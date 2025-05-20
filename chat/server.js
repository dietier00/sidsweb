const express = require('express');
const http = require('http');
const WebSocket = require('ws');
const mysql = require('mysql2/promise');
const cors = require('cors');
const bodyParser = require('body-parser');
const axios = require('axios');
const dotenv = require('dotenv');
const path = require('path');
const fs = require('fs');

dotenv.config();

const app = express();
const server = http.createServer(app);
const wss = new WebSocket.Server({ server });

// Database configuration
const dbConfig = {
    host: 'localhost',
    user: 'root',
    password: '',
    database: 'blinds_db'
};

// Function to get dashboard data
async function getDashboardData() {
    const connection = await mysql.createConnection(dbConfig);
    try {
        // Get pending orders count
        const [orders] = await connection.execute(
            'SELECT COUNT(*) as count FROM orders WHERE status = "pending"'
        );
        
        // Get unread messages count
        const [messages] = await connection.execute(
            'SELECT COUNT(*) as count FROM contact_messages WHERE status = "unread"'
        );
        
        // Get product statistics
        const [products] = await connection.execute(`
            SELECT 
                COUNT(*) as total_products,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_products,
                SUM(CASE WHEN stock < 10 THEN 1 ELSE 0 END) as low_stock_products,
                SUM(stock) as total_stock
            FROM products
        `);
        
        return {
            pendingOrders: orders[0].count,
            unreadMessages: messages[0].count,
            productStats: products[0]
        };
    } finally {
        await connection.end();
    }
}

// Broadcast to all connected clients
function broadcast(data) {
    wss.clients.forEach(client => {
        if (client.readyState === WebSocket.OPEN) {
            client.send(JSON.stringify(data));
        }
    });
}

// WebSocket connection handling
wss.on('connection', async function connection(ws) {
    console.log('Client connected to dashboard WebSocket');
    
    // Send initial data when client connects
    try {
        const data = await getDashboardData();
        ws.send(JSON.stringify({
            type: 'dashboard_update',
            data: data
        }));
    } catch (error) {
        console.error('Error sending initial data:', error);
    }
});

// Poll for websocket events
async function checkForUpdates() {
    const connection = await mysql.createConnection(dbConfig);
    try {
        const [events] = await connection.execute(
            'SELECT * FROM websocket_events WHERE processed = 0 ORDER BY created_at ASC'
        );

        for (const event of events) {
            const data = await getDashboardData();
            broadcast({
                type: event.event_type,
                data: data
            });

            // Mark event as processed
            await connection.execute(
                'UPDATE websocket_events SET processed = 1 WHERE id = ?',
                [event.id]
            );
        }
    } catch (error) {
        console.error('Error checking for updates:', error);
    } finally {
        await connection.end();
    }
}

// Check for updates every second
setInterval(checkForUpdates, 1000);

// Enable CORS
app.use(cors());
app.use(express.static(path.join(__dirname)));
app.use(bodyParser.json());

// Root route to serve chat.html
app.get('/', (req, res) => {
    res.sendFile(path.join(__dirname, 'chat.html'));
});

// JSON prompt for the chatbot

const companyinfos = JSON.parse(fs.readFileSync('companyinfos.json', 'utf8'));

// POST route for chatbot message
app.post('/message', async (req, res) => {
  const message = req.body.message;

  // Add context for the bot
  const contextPrompt = `You are a helpful customer service chatbot for Skye Blinds Interior Design Services.
  Below is the information about the company and its products:

  ${JSON.stringify(companyinfos, null, 2)}
  You help customers inquiries about the products names, price range, colors, materials, and services.
  Keep responses friendly but concise.

  Customer message: ${message}`;

  
  try {
    const geminiRes = await axios.post(
      'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent',
      {
        contents: [
          {
            role: 'user',
            parts: [
              {
                text: contextPrompt,
              },
            ],
          },
        ],
        generationConfig: {
          temperature: 0.7,
          topK: 1,
          topP: 1,
          maxOutputTokens: 4096,
          stopSequences: [],
        },
        safetySettings: [
          {
            category: 'HARM_CATEGORY_HARASSMENT',
            threshold: 'BLOCK_MEDIUM_AND_ABOVE',
          },
          {
            category: 'HARM_CATEGORY_HATE_SPEECH',
            threshold: 'BLOCK_MEDIUM_AND_ABOVE',
          },
          {
            category: 'HARM_CATEGORY_SEXUALLY_EXPLICIT',
            threshold: 'BLOCK_MEDIUM_AND_ABOVE',
          },
          {
            category: 'HARM_CATEGORY_DANGEROUS_CONTENT',
            threshold: 'BLOCK_MEDIUM_AND_ABOVE',
          },
        ],
      },
      {
        params: {
          key: process.env.GEMINI_API_KEY,
        },
      }
    );

    const reply =
      geminiRes.data?.candidates?.[0]?.content?.parts?.[0]?.text?.trim() ||
      "I'm not sure how to respond to that.";

    res.json({ reply });
  } catch (error) {
    console.error('Gemini API Error:', error.response?.data || error.message);
    res.status(500).send({
      reply: 'Gemini API Error: ' + (error.response?.data?.error?.message || error.message),
    });
  }
});

const PORT = process.env.PORT || 3001;
server.listen(PORT, () => {
    console.log(`WebSocket server running on port ${PORT}`);
});
