require('dotenv').config();

const express = require('express');
const cors = require('cors');
const bodyParser = require('body-parser');
const dotenv = require('dotenv');
const axios = require('axios');
const fs = require('fs');
const path = require('path');
const mysql = require('mysql2/promise');

dotenv.config();

const app = express();
const PORT = process.env.PORT || 3000;

app.use(cors());
app.use(bodyParser.json());

// Load company info
let companyinfos = {};
try {
  const data = fs.readFileSync(path.join(__dirname, 'companyinfos.json'), 'utf8');
  companyinfos = JSON.parse(data);
} catch (err) {
  console.error('Error loading company info:', err.message);
}

// MySQL connection pool
const pool = mysql.createPool({
  host: process.env.DB_HOST,
  user: process.env.DB_USER,
  password: process.env.DB_PASSWORD,
  database: process.env.DB_NAME,
  waitForConnections: true,
  connectionLimit: 10,
  queueLimit: 0
});

// Enhanced price estimation function
const calculatePriceEstimate = (productName, width, height) => {
  const product = companyinfos.products.find(p => 
    p.name.toLowerCase().includes(productName.toLowerCase())
  );
  
  if (!product) return null;
  
  const priceRange = product.price_range.match(/\d+/g);
  if (!priceRange || priceRange.length < 2) return null;
  
  const minPrice = parseInt(priceRange[0]);
  const maxPrice = parseInt(priceRange[1]);
  
  // Calculate area in square meters
  const areaInSqMeters = (width * height) / 1550;
  
  // Calculate price range
  const minEstimate = Math.round(areaInSqMeters * minPrice);
  const maxEstimate = Math.round(areaInSqMeters * maxPrice);
  
  return {
    product: product.name,
    estimatedPrice: `₱${minEstimate.toLocaleString()} - ₱${maxEstimate.toLocaleString()}`,
    priceRange: product.price_range,
    description: product.description,
    area: areaInSqMeters.toFixed(2)
  };
};

// Product recommendation function
const getProductRecommendations = (preferences = {}) => {
  let products = [...companyinfos.products];
  
  if (preferences.privacy) {
    products = products.filter(p => 
      p.description.toLowerCase().includes('privacy') || 
      p.name.toLowerCase().includes('blackout')
    );
  }
  
  if (preferences.budget) {
    const maxBudget = parseInt(preferences.budget);
    products = products.filter(p => {
      const maxPrice = parseInt(p.price_range.match(/\d+/g)[1]);
      return maxPrice <= maxBudget;
    });
  }
  
  // Sort by relevance or popularity (can be enhanced based on actual usage data)
  return products.slice(0, 3).map(p => ({
    name: p.name,
    description: p.description,
    price_range: p.price_range,
    image: `/images/products/${p.name.toLowerCase().replace(/\s+/g, '-')}.jpg`
  }));
};

const getProductSummary = () => companyinfos.products.map(p => `- ${p.name}: ${p.description} (${p.price_range})`).join('\n');
const getServicesSummary = () => companyinfos.services.map(s => `- ${s.name}: ${s.description}`).join('\n');

const basePrompt = `You are 'Interior Design Expert especially in Window Blinds', a professional, concise, and highly knowledgeable window blinds consultant for Skye Blinds Interior Design Services.
You will always remember the customer's preferences and past interactions to provide personalized recommendations.
You will not ask repetitive questions or request information already provided.
You will always provide a summary of the products and services offered by Skye Blinds.

Product Info:\n${getProductSummary()}\n\nServices:\n${getServicesSummary()}`;

const loadMemory = async (sessionId) => {
  const [rows] = await pool.query('SELECT * FROM user_memory WHERE session_id = ?', [sessionId]);
  return rows[0] || null;
};

const saveMemory = async (sessionId, updates) => {
  const fields = ['name', 'room', 'goal', 'budget', 'window_width', 'window_height', 'product_interest', 'color_preference', 'mount_type'];
  const placeholders = fields.map(f => '?').join(', ');
  const values = fields.map(f => updates[f] || null);
  await pool.query(`REPLACE INTO user_memory (session_id, ${fields.join(', ')}) VALUES (?, ${placeholders})`, [sessionId, ...values]);
};

const detectIfComplex = async (message) => {
  try {
    const response = await axios.post('https://openrouter.ai/api/v1/chat/completions', {
      model: "mistralai/mixtral-8x7b-instruct",
      messages: [
        { role: "system", content: "If the user message needs deep logic, personalization, or reasoning, respond YES. Otherwise, respond NO." },
        { role: "user", content: message }
      ],
      temperature: 0.2,
      max_tokens: 256
    }, {
      headers: {
        'Authorization': `Bearer ${process.env.OPENROUTER_API_KEY}`,
        'Content-Type': 'application/json'
      }
    });
    return response.data.choices[0].message.content.toLowerCase().includes("yes");
  } catch (err) {
    console.warn("Intent detection failed:", err.response?.data || err.message || err);
    return false;
  }
};

const askGemini = async (history) => {
  try {
    const response = await axios.post(`https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=AIzaSyBQkAdxwNJweBJRyygYeTglttaT4TVFTCw`, {
      contents: history,
      generationConfig: { temperature: 0.4, maxOutputTokens: 800 }
    }, {
      params: { key: process.env.GEMINI_API_KEY }
    });
    return response.data.candidates[0].content.parts[0].text;
  } catch (err) {
    console.error("Gemini error:", err.message);
    return null;
  }
};

const askGPT = async (history) => {
  try {
    const formatted = history.map(h => ({ role: h.role === 'user' ? 'user' : 'assistant', content: h.parts[0].text }));
    const response = await axios.post('https://openrouter.ai/api/v1/chat/completions', {
      model: "mistralai/mixtral-8x7b-instruct",
      messages: formatted,
      temperature: 0.3
    }, {
      headers: {
        Authorization: `Bearer ${process.env.OPENROUTER_API_KEY}`,
        'Content-Type': 'application/json'
      }
    });
    return response.data.choices[0].message.content;
  } catch (err) {
    console.error("GPT error:", err.message);
    return null;
  }
};

app.post('/message', async (req, res) => {
  const { message, sessionId, userName } = req.body;
  if (!message) return res.status(400).json({ error: true, reply: "No message provided." });
  
  const id = sessionId || Math.random().toString(36).substring(2, 16);
  let memory = await loadMemory(id) || {};
  
  // Handle measurement requests
  const measurementMatch = message.match(/(\d+)\s*(?:inches|inch|\")\s*(?:x|by)\s*(\d+)/i);
  if (measurementMatch && memory.product_interest) {
    const width = parseInt(measurementMatch[1]);
    const height = parseInt(measurementMatch[2]);
    const estimate = calculatePriceEstimate(memory.product_interest, width, height);
    
    if (estimate) {
      return res.json({
        error: false,
        type: 'measurement',
        reply: `Based on your window size (${width}\" x ${height}\") and chosen product (${estimate.product}), here's your estimate:\n\nArea: ${estimate.area} sq. meters\nEstimated Price Range: ${estimate.estimatedPrice}\n\nWould you like to proceed with getting a detailed quote?`
      });
    }
  }
  
  // Handle product recommendations
  if (message.toLowerCase().includes('recommend') || message.toLowerCase().includes('suggestion')) {
    const recommendations = getProductRecommendations(memory);
    return res.json({
      error: false,
      type: 'product_recommendation',
      recommendations,
      reply: 'Here are some products I think you might like:'
    });
  }

  // Handle measurement guide request
  if (message.toLowerCase().includes('measure') || message.toLowerCase().includes('size')) {
    const guide = companyinfos.measurement;
    return res.json({
      error: false,
      type: 'measurement_guide',
      reply: `Here's how to measure your windows:\n\n${guide.inside_mount.description}\n\n${guide.inside_mount.steps.join('\n')}\n\nOnce you have the measurements, let me know and I'll provide a price estimate.`
    });
  }

  const memoryContext = `\nCustomer Info:\n- Name: ${memory.name || "N/A"}\n- Room: ${memory.room || "N/A"}\n- Goal: ${memory.goal || "N/A"}\n- Budget: ${memory.budget || "N/A"}\n- Window Size: ${memory.window_width || "?"}in x ${memory.window_height || "?"}in\n- Product Interest: ${memory.product_interest || "N/A"}`;

  const history = [
    { role: "user", parts: [{ text: basePrompt + memoryContext }] },
    { role: "user", parts: [{ text: message }] }
  ];

  const useGPT = await detectIfComplex(message);
  let reply = useGPT ? await askGPT(history) : await askGemini(history);
  if (!reply) reply = "Sorry, something went wrong. Please try again later.";

  // Memory Extraction (use your own NLP or regex or GPT call here)
  const updateFields = {};
  const lower = message.toLowerCase();
  if (lower.includes("living room")) updateFields.room = "living room";
  if (lower.includes("privacy")) updateFields.goal = "privacy";
  if (lower.includes("roman") || lower.includes("roller")) updateFields.product_interest = lower.includes("roman") ? "roman blinds" : "roller blinds";
  const budgetMatch = lower.match(/\b(\d{4,6})\b/);
  if (budgetMatch) updateFields.budget = parseInt(budgetMatch[1]);
  const sizeMatch = lower.match(/(\d{2,3})\s*(inches|inch|\")\s*(x|by)\s*(\d{2,3})/);
  if (sizeMatch) {
    updateFields.window_width = parseInt(sizeMatch[1]);
    updateFields.window_height = parseInt(sizeMatch[4]);
  }

  await saveMemory(id, { ...memory, ...updateFields });

  res.json({ error: false, reply, sessionId: id });
});

app.listen(PORT, () => {
  console.log(`✅ Dual-engine chatbot server running at http://localhost:${PORT}`);
});
