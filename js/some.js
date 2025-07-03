document.addEventListener('DOMContentLoaded', function () {
  var dropdown = document.querySelector('.nav-item.dropdown');
  if (dropdown) {
    dropdown.addEventListener('mouseenter', function () {
      var menu = dropdown.querySelector('.dropdown-menu');
      dropdown.classList.add('show');
      menu.classList.add('show');
    });
    dropdown.addEventListener('mouseleave', function () {
      var menu = dropdown.querySelector('.dropdown-menu');
      dropdown.classList.remove('show');
      menu.classList.remove('show');
    });
  }
});  
  
document.getElementById('pricingForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const product = document.getElementById('productType').value;
    const length = parseFloat(document.getElementById('length').value);
    const quantity = parseInt(document.getElementById('quantity').value, 10);

    // Example pricing (adjust as needed)
    const prices = {
      roller: 500,
      vertical: 400,
      roman: 600
    };

    if (!product || !length || !quantity) {
      document.getElementById('priceResult').innerHTML = `<div class="alert alert-warning rounded-3">Please fill in all fields.</div>`;
      return;
    }

    const unitPrice = prices[product];
    const total = unitPrice * length * quantity;

    document.getElementById('priceResult').innerHTML = `
      <div class="card border-0 shadow-sm bg-success bg-opacity-10 text-success text-center mx-auto" style="max-width: 400px;">
        <div class="card-body">
          <div class="fs-2 mb-2"><i class="bi bi-cash-stack"></i></div>
          <div class="fw-bold fs-4">Estimated Price</div>
          <div class="display-6 fw-bold mb-2">₱${total.toLocaleString()}</div>
          <div class="small text-muted">* This is an estimate. Final pricing may vary.</div>
        </div>
      </div>
    `;
  });

// Chatbot initialization
window.initializeChatbot = function(userData) {
  const chatBody = document.querySelector('.chat-body');
  const userName = userData.name || localStorage.getItem('userName');
  
  if (userName) {
    const welcomeMessage = chatBody.querySelector('.message-text');
    welcomeMessage.innerHTML = `
      Hey ${userName}! 👋<br>
      How can I help you today?
      <div class="quick-actions">
        <button class="quick-action-btn" data-action="get-started">
          <span class="material-symbols-rounded">rocket_launch</span> Get Started
        </button>
        <button class="quick-action-btn" data-action="product-recommendation">
          <span class="material-symbols-rounded">format_list_bulleted</span> Product Recommendation
        </button>
        <button class="quick-action-btn" data-action="measurement">
          <span class="material-symbols-rounded">straighten</span> Get Price Estimate
        </button>
      </div>
    `;
  }

  // Initialize Material Icons
  document.querySelectorAll('.material-symbols-rounded').forEach(icon => {
    icon.style.fontFamily = 'Material Symbols Rounded';
  });

  // Add smooth animations
  document.querySelectorAll('.message').forEach(msg => {
    msg.style.opacity = '0';
    msg.style.transform = 'translateY(20px)';
    requestAnimationFrame(() => {
      msg.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
      msg.style.opacity = '1';
      msg.style.transform = 'translateY(0)';
    });
  });
};

// Handle quick action buttons
document.addEventListener('click', (e) => {
  if (e.target.closest('.quick-action-btn')) {
    const button = e.target.closest('.quick-action-btn');
    const action = button.dataset.action;
    
    switch(action) {
      case 'get-started':
        appendMessage("I'd like to get started with choosing blinds for my home.", 'user');
        break;
      case 'product-recommendation':
        appendMessage("Can you recommend some products for me?", 'user');
        break;
      case 'measurement':
        appendMessage("I'd like to get a price estimate based on my window measurements.", 'user');
        break;
    }
  }
});


