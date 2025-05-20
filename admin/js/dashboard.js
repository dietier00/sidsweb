// Dashboard refresh functionality
let refreshInterval;
const REFRESH_INTERVAL = 30000; // 30 seconds

// Initialize WebSocket connection
function initializeWebSocket() {
    const ws = new WebSocket('ws://localhost:3001');

    ws.onopen = function() {
        console.log('Connected to dashboard WebSocket');
    };

    ws.onmessage = function(event) {
        const data = JSON.parse(event.data);
        
        if (data.type === 'dashboard_update' || 
            data.type === 'order_update' || 
            data.type === 'message_update' || 
            data.type === 'product_update') {
            
            // Update purchase orders count
            document.querySelector('.purchase-orders h3').textContent = data.data.pendingOrders;
            
            // Update messages count and notification
            const messagesCard = document.querySelector('.messages');
            const messageCount = data.data.unreadMessages;
            messagesCard.querySelector('h3').textContent = messageCount;
            const notificationDot = messagesCard.querySelector('.notification-dot');
            
            if (messageCount > 0) {
                notificationDot.classList.remove('d-none');
                // Add pulse animation
                notificationDot.style.animation = 'none';
                notificationDot.offsetHeight; // Trigger reflow
                notificationDot.style.animation = 'pulse 2s infinite';
            } else {
                notificationDot.classList.add('d-none');
            }

            // Update product statistics
            document.querySelector('.total-products .value').textContent = data.data.productStats.total_products;
            document.querySelector('.active-products .value').textContent = data.data.productStats.active_products;
            document.querySelector('.low-stock .value').textContent = data.data.productStats.low_stock_products;
            document.querySelector('.total-stock .value').textContent = data.data.productStats.total_stock;

            // Show toast notification for updates
            const toast = new bootstrap.Toast(document.getElementById('refreshToast'));
            toast.show();
        }
    };

    ws.onclose = function() {
        console.log('WebSocket connection closed. Attempting to reconnect...');
        setTimeout(initializeWebSocket, 5000); // Try to reconnect after 5 seconds
    };

    return ws;
}

// WebSocket connection
const ws = new WebSocket('ws://localhost:3001');

ws.onopen = function() {
    console.log('Connected to dashboard websocket');
};

ws.onmessage = function(event) {
    const data = JSON.parse(event.data);
    
    if (data.type === 'dashboard_update' || data.type === 'order_update' || 
        data.type === 'message_update' || data.type === 'product_update') {
        // Update metrics
        document.querySelector('.purchase-orders h3').textContent = data.data.pendingOrders;
        
        // Update messages and notification dot
        const messagesCard = document.querySelector('.messages');
        const messageCount = data.data.unreadMessages;
        messagesCard.querySelector('h3').textContent = messageCount;
        const notificationDot = messagesCard.querySelector('.notification-dot');
        if (messageCount > 0) {
            notificationDot.classList.remove('d-none');
        } else {
            notificationDot.classList.add('d-none');
        }

        // Update product statistics
        document.querySelector('.total-products .value').textContent = data.data.productStats.total_products;
        document.querySelector('.active-products .value').textContent = data.data.productStats.active_products;
        document.querySelector('.low-stock .value').textContent = data.data.productStats.low_stock_products;
        document.querySelector('.total-stock .value').textContent = data.data.productStats.total_stock;
    }
};

ws.onclose = function() {
    console.log('Dashboard websocket connection closed');
    // Attempt to reconnect after 5 seconds
    setTimeout(() => {
        window.location.reload();
    }, 5000);
};

function startAutoRefresh() {
    refreshDashboard();
    refreshInterval = setInterval(refreshDashboard, REFRESH_INTERVAL);
}

function stopAutoRefresh() {
    clearInterval(refreshInterval);
}

// Toggle auto-refresh
document.getElementById('autoRefreshToggle').addEventListener('change', function() {
    if (this.checked) {
        startAutoRefresh();
    } else {
        stopAutoRefresh();
    }
});

function refreshDashboard() {
    fetch('../admin/dashboard/get_dashboard_data.php')
        .then(response => response.json())
        .then(data => {
            updateMetrics(data);
            updateProductStats(data.productStats);
            updateRecentTransactions(data.recentTransactions);
            updateOrderTimeline(data.orderTimeline);
            updateTopProducts(data.topProducts);
            showRefreshToast();
        })
        .catch(error => console.error('Error:', error));
}

function updateMetrics(data) {
    // Update weekly sales
    document.querySelector('.weekly-sales h3').textContent = '₱' + numberWithCommas(data.weeklySales.total);
    const salesGrowthText = data.weeklySales.growth > 0 ? `+${data.weeklySales.growth}%` : `${data.weeklySales.growth}%`;
    document.querySelector('.weekly-sales .growth-text').textContent = salesGrowthText;
    
    // Update weekly profit
    document.querySelector('.weekly-profit h3').textContent = '₱' + numberWithCommas(data.weeklySales.profit);
    const profitGrowthText = data.weeklySales.profit_growth > 0 ? `+${data.weeklySales.profit_growth}%` : `${data.weeklySales.profit_growth}%`;
    document.querySelector('.weekly-profit .profit-growth-text').textContent = profitGrowthText;
    
    // Update new users
    document.querySelector('.new-users h3').textContent = data.newUsers;
    
    // Update pending orders
    document.querySelector('.purchase-orders h3').textContent = data.pendingOrders;
    
    // Update unread messages and notification dot
    const messagesCard = document.querySelector('.messages');
    const messageCount = data.unreadMessages;
    messagesCard.querySelector('h3').textContent = messageCount;
    const notificationDot = messagesCard.querySelector('.notification-dot');
    if (messageCount > 0) {
        notificationDot.classList.remove('d-none');
    } else {
        notificationDot.classList.add('d-none');
    }

    // Update product summary cards
    document.querySelector('.total-products .value').textContent = data.productSummary.total_products;
    document.querySelector('.active-products .value').textContent = data.productSummary.active_products;
    document.querySelector('.low-stock .value').textContent = data.productSummary.low_stock_products;
    document.querySelector('.total-stock .value').textContent = data.productSummary.total_stock;
}

function updateProductStats(stats) {
    const ctx = document.getElementById('productsChart').getContext('2d');
    
    // Destroy existing chart if it exists
    if (window.productChart instanceof Chart) {
        window.productChart.destroy();
    }

    window.productChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: stats.labels,
            datasets: [{
                label: 'Active Products',
                data: stats.status.map(s => s.active),
                backgroundColor: 'rgba(40, 167, 69, 0.5)',
                borderColor: 'rgb(40, 167, 69)',
                borderWidth: 1
            }, {
                label: 'Inactive Products',
                data: stats.status.map(s => s.inactive),
                backgroundColor: 'rgba(220, 53, 69, 0.5)',
                borderColor: 'rgb(220, 53, 69)',
                borderWidth: 1
            }, {
                label: 'Stock Level',
                data: stats.stock,
                type: 'line',
                borderColor: 'rgb(13, 110, 253)',
                borderWidth: 2,
                fill: false,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Number of Products'
                    }
                },
                y1: {
                    beginAtZero: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Stock Level'
                    },
                    grid: {
                        drawOnChartArea: false
                    }
                }
            }
        }
    });
}

function updateRecentTransactions(transactions) {
    const container = document.querySelector('.transaction-list');
    container.innerHTML = '';

    transactions.forEach(transaction => {
        const item = document.createElement('div');
        item.className = 'transaction-item';
        item.innerHTML = `
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-1">Order #${transaction.order_number}</h6>
                    <p class="mb-0 text-muted">${transaction.customer_name}</p>
                    <small class="text-muted">${formatDate(transaction.created_at)}</small>
                </div>
                <div class="text-end">
                    <h6 class="mb-1">₱${numberWithCommas(transaction.total_amount)}</h6>
                    <span class="badge bg-${getStatusBadgeClass(transaction.status)}">${transaction.status}</span>
                </div>
            </div>
        `;
        container.appendChild(item);
    });
}

function updateOrderTimeline(timeline) {
    const container = document.querySelector('.timeline');
    container.innerHTML = '';

    timeline.forEach(order => {
        const item = document.createElement('div');
        item.className = 'timeline-item';
        item.innerHTML = `
            <div class="d-flex justify-content-between">
                <div>
                    <h6 class="mb-1">Order #${order.order_number}</h6>
                    <p class="mb-0">${order.customer_name}</p>
                    <p class="mb-0"><small class="text-muted">Products: ${order.products}</small></p>
                    <small class="text-muted">${formatDate(order.created_at)}</small>
                </div>
                <div class="text-end">
                    <span class="badge bg-${getStatusBadgeClass(order.status)}">${order.status}</span>
                    <div class="mt-1">
                        <small class="text-muted">Revenue: ₱${numberWithCommas(order.total_amount)}</small>
                        <br>
                        <small class="text-success">Profit: ₱${numberWithCommas(order.order_profit)}</small>
                    </div>
                </div>
            </div>
        `;
        container.appendChild(item);
    });
}

function updateTopProducts(products) {
    const tbody = document.querySelector('.product-performance tbody');
    tbody.innerHTML = '';

    products.forEach(product => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>
                <div class="d-flex align-items-center">
                    <img src="data:image/jpeg;base64,${product.images}" alt="${product.name}" class="product-image me-2">
                    <div>
                        ${product.name}
                        <br>
                        <small class="text-muted">Units: ${product.units_sold}</small>
                    </div>
                </div>
            </td>
            <td class="text-center">
                <div>${product.total_sales}</div>
                <small class="text-success">₱${numberWithCommas(product.total_profit)}</small>
            </td>
            <td class="text-end">
                <div>₱${numberWithCommas(product.total_revenue)}</div>
                <small class="text-muted">Avg. profit: ₱${numberWithCommas(parseFloat(product.avg_profit_per_sale).toFixed(2))}</small>
            </td>
            <td><span class="text-success">+${calculateGrowth(product.total_revenue)}%</span></td>
        `;
        tbody.appendChild(row);
    });
}

// Helper functions
function numberWithCommas(x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

function formatDate(dateString) {
    const options = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' };
    return new Date(dateString).toLocaleDateString('en-US', options);
}

function getStatusBadgeClass(status) {
    const statusClasses = {
        'pending': 'warning',
        'processing': 'info',
        'shipped': 'primary',
        'delivered': 'success',
        'cancelled': 'danger',
        'completed': 'success'
    };
    return statusClasses[status] || 'secondary';
}

function calculateGrowth(value) {
    // Placeholder for growth calculation
    return Math.floor(Math.random() * 20) + 1; // Random number between 1-20 for demo
}

function showRefreshToast() {
    const toast = new bootstrap.Toast(document.getElementById('refreshToast'));
    toast.show();
}

// Initialize the dashboard
document.addEventListener('DOMContentLoaded', function() {
    // Initialize WebSocket connection
    const ws = initializeWebSocket();

    // Start auto-refresh for other dashboard components
    startAutoRefresh();
});