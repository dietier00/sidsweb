const chatBody = document.querySelector(".chat-body");
const messageInput = document.querySelector(".message-input");
const sendButton = document.querySelector("#send-message");
const chatToggler = document.querySelector("#chatbot-toggler");
const closeBtn = document.querySelector("#close-chatbot");

let userSession = {
  id: localStorage.getItem('chatSessionId') || Math.random().toString(36).substring(2, 15),
  name: localStorage.getItem('userName') || null
};

// Initialize chat and event listeners
function initializeChat() {
  localStorage.setItem('chatSessionId', userSession.id);
  
  // Initial greeting
  appendBotMessage({
    text: userSession.name 
      ? `Welcome back, ${userSession.name}! How can I help you today?`
      : "Hi there! 👋😁\nHow can I help you today?",
    showQuickActions: true
  });

  // Event listeners
  sendButton.addEventListener("click", handleOutgoingMessage);
  messageInput.addEventListener("keydown", (e) => {
    if (e.key === "Enter" && !e.shiftKey && messageInput.value.trim()) {
      e.preventDefault();
      handleOutgoingMessage(e);
    }
  });

  chatBody.addEventListener("click", (e) => {
    if (e.target.closest(".quick-action-btn")) {
      const action = e.target.closest(".quick-action-btn").dataset.action;
      handleQuickAction(action);
    }
    if (e.target.closest(".view-product-btn")) {
      const productId = e.target.closest(".view-product-btn").dataset.productId;
      window.location.href = `shop/productsdetail.php?id=${productId}`;
    }
  });

  chatToggler.addEventListener("click", () => {
    document.body.classList.toggle("show-chatbot");
    if (document.body.classList.contains("show-chatbot")) {
      chatBody.scrollTop = chatBody.scrollHeight;
    }
  });

  closeBtn.addEventListener("click", () => {
    document.body.classList.remove("show-chatbot");
  });
}

// Handle outgoing messages
async function handleOutgoingMessage(e) {
  e.preventDefault();
  const message = messageInput.value.trim();
  if (!message) return;

  // Clear input
  messageInput.value = "";
  messageInput.style.height = "44px";

  // Append user message
  appendUserMessage(message);

  // Show typing indicator
  const botMessageDiv = appendBotMessage({ 
    text: '<div class="thinking-indicator"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div>' 
  });

  try {
    const response = await fetch("http://localhost:3000/message", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ 
        message,
        sessionId: userSession.id,
        userName: userSession.name
      })
    });

    const data = await response.json();
    
    if (data.error) {
      throw new Error(data.error);
    }

    // Update bot message with response
    if (data.type === 'product_recommendation') {
      const productHtml = createProductCard(data.product);
      botMessageDiv.querySelector('.message-text').innerHTML = productHtml;
    } else {
      botMessageDiv.querySelector('.message-text').innerHTML = data.reply;
    }

    // Add quick actions if needed
    if (data.showQuickActions) {
      appendQuickActions(botMessageDiv);
    }

  } catch (error) {
    console.error("Chat error:", error);
    botMessageDiv.querySelector('.message-text').innerHTML = "Sorry, I'm having trouble connecting. Please try again.";
  }

  chatBody.scrollTop = chatBody.scrollHeight;
}

// Handle quick actions
function handleQuickAction(action) {
  let message = "";
  
  switch(action) {
    case "get-started":
      message = "I'd like to get started with choosing window treatments.";
      break;
    case "product-recommendation":
      message = "Can you recommend some products for me?";
      break;
    case "measurement":
      message = "I'd like to get a price estimate based on my window measurements.";
      break;
  }

  if (message) {
    messageInput.value = message;
    sendButton.click();
  }
}

// Create product card HTML
function createProductCard(product) {
  return `
    <div class="product-card">
      <img src="${product.image}" alt="${product.name}">
      <h3>${product.name}</h3>
      <p>${product.description}</p>
      <p class="price">${product.price_range}</p>
      <a href="shop/productsdetail.php?id=${product.id}" class="btn view-product-btn" data-product-id="${product.id}">View Product</a>
    </div>
  `;
}

// Append quick action buttons
function appendQuickActions(messageDiv) {
  const quickActions = document.createElement('div');
  quickActions.className = 'quick-actions';
  quickActions.innerHTML = `
    <button class="quick-action-btn" data-action="get-started">
      <span class="material-symbols-rounded">rocket_launch</span> Get Started
    </button>
    <button class="quick-action-btn" data-action="product-recommendation">
      <span class="material-symbols-rounded">format_list_bulleted</span> Product Recommendation
    </button>
    <button class="quick-action-btn" data-action="measurement">
      <span class="material-symbols-rounded">straighten</span> Get Price Estimate
    </button>
  `;
  messageDiv.querySelector('.message-text').appendChild(quickActions);
}

// Helper function to append user message
function appendUserMessage(text) {
  const messageDiv = document.createElement('div');
  messageDiv.className = 'message user-message';
  messageDiv.innerHTML = `<div class="message-text">${text}</div>`;
  chatBody.appendChild(messageDiv);
  chatBody.scrollTop = chatBody.scrollHeight;
}

// Helper function to append bot message
function appendBotMessage({ text, showQuickActions = false }) {
  const messageDiv = document.createElement('div');
  messageDiv.className = 'message bot-message';
  messageDiv.innerHTML = `
    <svg class="bot-avatar" xmlns="http://www.w3.org/2000/svg" width="35" height="35" viewBox="0 0 1024 1024">
      <path d="M738.3 287.6H285.7c-59 0-106.8 47.8-106.8 106.8v303.1c0 59 47.8 106.8 106.8 106.8h81.5v111.1c0 .7.8 1.1 1.4.7l166.9-110.6 41.8-.8h117.4l43.6-.4c59 0 106.8-47.8 106.8-106.8V394.5c0-59-47.8-106.9-106.8-106.9z"/>
    </svg>
    <div class="message-text">${text}</div>
  `;
  
  if (showQuickActions) {
    appendQuickActions(messageDiv);
  }
  
  chatBody.appendChild(messageDiv);
  chatBody.scrollTop = chatBody.scrollHeight;
  return messageDiv;
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', initializeChat);


