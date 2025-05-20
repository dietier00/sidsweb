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


