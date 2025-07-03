document.addEventListener('DOMContentLoaded', function() {
  // Load Lottie animation
  const animation = lottie.loadAnimation({
    container: document.getElementById('lottie-animation'),
    renderer: 'svg',
    loop: true,
    autoplay: true,
    path: 'https://assets5.lottiefiles.com/packages/lf20_5tkzkblw.json' // AI assistant animation
  });

  // Elements
  const welcomeScreen = document.getElementById('welcome-screen');
  const chatScreen = document.getElementById('chat-screen');
  const chatMessages = document.getElementById('chat-messages');
  const userInput = document.getElementById('user-input');
  const sendBtn = document.getElementById('send-btn');
  const questionBtn = document.getElementById('question-btn');
  const quickOptions = document.querySelectorAll('.quick-option');
  const productCarousel = document.getElementById('product-carousel');
  const orderStatusModal = document.getElementById('order-status-modal');
  const closeOrderStatus = document.getElementById('close-order-status');
  const orderNumberInput = document.getElementById('order-number-input');
  const checkOrderBtn = document.getElementById('check-order-btn');
  const orderStatusResult = document.getElementById('order-status-result');
  
  let sessionId = generateSessionId();
  
  // Event listeners
  questionBtn.addEventListener('click', openChatScreen);
  sendBtn.addEventListener('click', sendMessage);
  userInput.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') sendMessage();
  });
  
  quickOptions.forEach(option => {
    option.addEventListener('click', function() {
      const optionType = this.getAttribute('data-option');
      handleQuickOption(optionType);
    });
  });
  
  closeOrderStatus.onclick = () => {
    orderStatusModal.style.display = 'none';
  };
  
  checkOrderBtn.onclick = async () => {
    const orderNum = orderNumberInput.value.trim();
    if (!orderNum) {
      orderStatusResult.innerHTML = '<span style="color:red">Please enter an order number.</span>';
      return;
    }
    orderStatusResult.innerHTML = 'Checking...';
    // Fetch order status from backend (PHP endpoint required)
    try {
      const res = await fetch(`../php/get_order_status.php?order_number=${encodeURIComponent(orderNum)}`);
      const data = await res.json();
      if (data && data.status) {
        orderStatusResult.innerHTML = `<b>Status:</b> ${data.status}<br><b>Updated:</b> ${data.updated_at}`;
      } else {
        orderStatusResult.innerHTML = '<span style="color:red">Order not found.</span>';
      }
    } catch (e) {
      orderStatusResult.innerHTML = '<span style="color:red">Unable to fetch order status.</span>';
    }
  };

  // Functions
  function generateSessionId() {
    return 'skye-' + Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);
  }
  
  function openChatScreen() {
    welcomeScreen.style.display = 'none';
    chatScreen.style.display = 'flex';
    addBotMessage("Hello! I'm your Skye Blinds assistant. How can I help you with your window blinds today?");
  }
  
  // --- New Elements for Enhanced Functionality ---
  // const productCarousel = document.getElementById('product-carousel');
  // const orderStatusModal = document.getElementById('order-status-modal');
  // const closeOrderStatus = document.getElementById('close-order-status');
  // const orderNumberInput = document.getElementById('order-number-input');
  // const checkOrderBtn = document.getElementById('check-order-btn');
  // const orderStatusResult = document.getElementById('order-status-result');

  // --- Product Recommendation Logic ---
  async function showProductRecommendations() {
    productCarousel.innerHTML = '';
    productCarousel.style.display = 'block';
    // Fetch product data
    let products = [];
    try {
      const res = await fetch('../shop/products.json');
      products = await res.json();
    } catch (e) {
      productCarousel.innerHTML = '<p>Unable to load products at this time.</p>';
      return;
    }
    // Build carousel/cards
    let cards = '';
    products.forEach((prod, idx) => {
      cards += `
        <div class="product-card-tile" data-idx="${idx}">
          <img src="${prod.image}" alt="${prod.name}" />
          <div class="prod-info">
            <h4>${prod.name}</h4>
            <p>${prod.description}</p>
            <span class="prod-price">₱${prod.price_php}</span>
          </div>
        </div>
      `;
    });
    productCarousel.innerHTML = `
      <div class="carousel-track">
        ${cards}
      </div>
      <button class="carousel-btn left">&#8592;</button>
      <button class="carousel-btn right">&#8594;</button>
    `;
    // Carousel logic
    let currentIdx = 0;
    const track = productCarousel.querySelector('.carousel-track');
    const tiles = productCarousel.querySelectorAll('.product-card-tile');
    const leftBtn = productCarousel.querySelector('.carousel-btn.left');
    const rightBtn = productCarousel.querySelector('.carousel-btn.right');
    function updateCarousel() {
      tiles.forEach((tile, idx) => {
        tile.style.display = (idx === currentIdx) ? 'block' : 'none';
      });
    }
    leftBtn.onclick = () => {
      currentIdx = (currentIdx - 1 + tiles.length) % tiles.length;
      updateCarousel();
    };
    rightBtn.onclick = () => {
      currentIdx = (currentIdx + 1) % tiles.length;
      updateCarousel();
    };
    updateCarousel();
  }

  // --- Order Status Logic ---
  function showOrderStatusModal() {
    orderStatusModal.style.display = 'block';
    orderStatusResult.innerHTML = '';
    orderNumberInput.value = '';
  }
  closeOrderStatus.onclick = () => {
    orderStatusModal.style.display = 'none';
  };
  checkOrderBtn.onclick = async () => {
    const orderNum = orderNumberInput.value.trim();
    if (!orderNum) {
      orderStatusResult.innerHTML = '<span style="color:red">Please enter an order number.</span>';
      return;
    }
    orderStatusResult.innerHTML = 'Checking...';
    // Fetch order status from backend (PHP endpoint required)
    try {
      const res = await fetch(`../php/get_order_status.php?order_number=${encodeURIComponent(orderNum)}`);
      const data = await res.json();
      if (data && data.status) {
        orderStatusResult.innerHTML = `<b>Status:</b> ${data.status}<br><b>Updated:</b> ${data.updated_at}`;
      } else {
        orderStatusResult.innerHTML = '<span style="color:red">Order not found.</span>';
      }
    } catch (e) {
      orderStatusResult.innerHTML = '<span style="color:red">Unable to fetch order status.</span>';
    }
  };

  // --- Enhance Quick Option Handling ---
  function handleQuickOption(optionType) {
    openChatScreen();
    productCarousel.style.display = 'none';
    orderStatusModal.style.display = 'none';
    switch(optionType) {
      case 'order-status':
        showOrderStatusModal();
        break;
      case 'product-recommendations':
        addBotMessage("Here are some product recommendations. Swipe to browse.");
        showProductRecommendations();
        break;
    }
  }
  
  function sendMessage() {
    const message = userInput.value.trim();
    if (!message) return;
    
    addUserMessage(message);
    userInput.value = '';
    toggleBotAnimation(true);
    
    // Show typing indicator
    const typingIndicator = document.createElement('div');
    typingIndicator.className = 'typing-indicator';
    typingIndicator.innerHTML = `
      <div class="typing-dot"></div>
      <div class="typing-dot"></div>
      <div class="typing-dot"></div>
    `;
    chatMessages.appendChild(typingIndicator);
    chatMessages.scrollTop = chatMessages.scrollHeight;
    
    // Simulate API call
    setTimeout(() => {
      // Remove typing indicator
      chatMessages.removeChild(typingIndicator);
      
      // Process message and get response
      processUserMessage(message);
      toggleBotAnimation(false);
    }, 1500);
  }
  
  function addUserMessage(text) {
    const messageDiv = document.createElement('div');
    messageDiv.className = 'message user-message';
    messageDiv.textContent = text;
    chatMessages.appendChild(messageDiv);
    chatMessages.scrollTop = chatMessages.scrollHeight;
  }
  
  function addBotMessage(text, options = {}) {
    const messageDiv = document.createElement('div');
    messageDiv.className = 'message bot-message';
    
    if (options.type === 'product') {
      messageDiv.innerHTML = `
        <p>${text}</p>
        <div class="product-card">
          <img src="${options.image || 'product-placeholder.jpg'}" alt="${options.title}">
          <div class="content">
            <h4>${options.title}</h4>
            <p>${options.description}</p>
            <p class="price">${options.price}</p>
          </div>
        </div>
      `;
    } else if (options.type === 'measurement') {
      messageDiv.innerHTML = `
        <p>${text}</p>
        <div class="measurement-guide">
          <h4>${options.title || 'Measurement Guide'}</h4>
          <p>${options.steps || 'Please provide your window measurements for an accurate estimate.'}</p>
        </div>
      `;
    } else if (options.type === 'order-status') {
      messageDiv.innerHTML = `
        <p>${text}</p>
        <div class="order-status">
          <h4>${options.title || 'Order Status'}</h4>
          <p>${options.details || 'Please provide your order number for more information.'}</p>
        </div>
      `;
    } else {
      messageDiv.textContent = text;
    }
    
    chatMessages.appendChild(messageDiv);
    chatMessages.scrollTop = chatMessages.scrollHeight;
  }
  
  function processUserMessage(message) {
    const lowerMessage = message.toLowerCase();
    
    // Simple response logic - in a real app, this would call your backend API
    if (lowerMessage.includes('hello') || lowerMessage.includes('hi')) {
      addBotMessage("Hello! I'm here to help you with all your window blind needs at Skye Interior Design. What are you looking for today?");
    } 
    else if (lowerMessage.includes('order') || lowerMessage.includes('status')) {
      addBotMessage("To check your order status, I'll need your order number. You can find it in your confirmation email.", {
        type: 'order-status',
        title: 'Order Lookup',
        details: 'Order #12345: Estimated delivery June 15'
      });
    }
    else if (lowerMessage.includes('roman blind') || lowerMessage.includes('roman shade')) {
      addBotMessage("Roman blinds are a great choice! Here's one of our popular options:", {
        type: 'product',
        title: 'Classic Linen Roman Blind',
        description: 'Elegant linen fabric with blackout lining option',
        price: 'From $120',
        image: 'roman-blind.jpg'
      });
    }
    else if (lowerMessage.includes('measure') || lowerMessage.includes('size')) {
      addBotMessage("Here's how to measure your windows for blinds:", {
        type: 'measurement',
        title: 'Window Measurement Guide',
        steps: '1. Use a metal tape measure for accuracy<br>2. Measure width at top, middle, and bottom<br>3. Measure height at left, center, and right<br>4. Use the smallest measurements'
      });
    }
    else if (lowerMessage.includes('recommend') || lowerMessage.includes('suggestion')) {
      addBotMessage("Based on your preferences, I recommend these options:", {
        type: 'product',
        title: 'Premium Blackout Roller Blind',
        description: 'Complete light control with thermal insulation',
        price: 'From $95',
        image: 'roller-blind.jpg'
      });
    }
    else {
      // Default response
      addBotMessage("I'm here to help with your window blind needs. You can ask me about product recommendations, measurements, or your order status. What would you like to know?");
    }
  }
  
  // Initialize with welcome message if needed
  // addBotMessage("Welcome to Skye Blinds! How can I assist you today?");
});
// Add this to your existing chatbot.js
let isBotActive = false;

function toggleBotAnimation(active) {
  const aiAvatar = document.querySelector('.ai-avatar');
  isBotActive = active;
  
  if (active) {
    aiAvatar.classList.add('bot-active');
  } else {
    aiAvatar.classList.remove('bot-active');
  }
}

// Modify your existing functions to trigger the animation
function sendMessage() {
  const message = userInput.value.trim();
  if (!message) return;
  
  addUserMessage(message);
  userInput.value = '';
  toggleBotAnimation(true);
  
  // Show typing indicator
  const typingIndicator = document.createElement('div');
  typingIndicator.className = 'typing-indicator';
  typingIndicator.innerHTML = `
    <div class="typing-dot"></div>
    <div class="typing-dot"></div>
    <div class="typing-dot"></div>
  `;
  chatMessages.appendChild(typingIndicator);
  chatMessages.scrollTop = chatMessages.scrollHeight;
  
  // Simulate API call
  setTimeout(() => {
    // Remove typing indicator
    chatMessages.removeChild(typingIndicator);
    
    // Process message and get response
    processUserMessage(message);
    toggleBotAnimation(false);
  }, 1500);
}

// Add animation when quick options are clicked
quickOptions.forEach(option => {
  option.addEventListener('click', function() {
    const optionType = this.getAttribute('data-option');
    toggleBotAnimation(true);
    setTimeout(() => {
      handleQuickOption(optionType);
      toggleBotAnimation(false);
    }, 1000);
  });
});

