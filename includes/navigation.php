<?php
// Get the current page name
$current_page = basename($_SERVER['PHP_SELF']);

// Get cart count if user is logged in
$cartCount = 0;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM cart WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $cartCount = $stmt->fetchColumn();
}

if (isset($_SESSION['user_id'])) {
    $cartCountStmt = $pdo->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
    $cartCountStmt->execute([$_SESSION['user_id']]);
    $cartCount = $cartCountStmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
}
?>
<nav class="navbar navbar-expand-lg navbar-light fixed-top" tabindex="0">
    <div class="container">
        <a class="navbar-brand pe-4 fs-4" href="../index.php">
            <img src="../favicon/favicon.ico" alt="Skye Logo" style="height:40px; width:auto; vertical-align:middle; margin-right:8px;">
            <span class="ms-1 fw-bolder">Skye Blinds Interior Design Services</span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="../index.php#gallery">Gallery</a>
                </li>
                <!-- Products Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="productsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Products
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="productsDropdown">
                        <li><a class="dropdown-item" href="../shop/productsdetail.php">Product Details</a></li>
                        <li><a class="dropdown-item" href="../shop/products.php">Product Listing</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../index.php#services">Services</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../index.php#about">About</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../users/contact.php">Contact</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link position-relative <?= $current_page === 'cart.php' ? 'active' : '' ?>" href="../shop/cart.php">
                        <i class="fas fa-shopping-cart"></i> Cart
                        <?php if ($cartCount > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?php echo $cartCount; ?>
                                <span class="visually-hidden">items in cart</span>
                            </span>
                        <?php endif; ?>
                    </a>
                </li>
            </ul>
            <!-- Profile Icon -->
            <div class="nav-item dropdown">
                <a href="#" class="profile-icon" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-user"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-end profile-menu" aria-labelledby="profileDropdown">
                    <div id="profileContent">
                        <!-- Content will be populated by JavaScript -->
                    </div>
                </ul>
            </div>
        </div>
    </div>
</nav>

<style>
.navbar {
    background: #fff;
    transition: all 0.3s ease;
}
.navbar.scrolled {
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}
.profile-icon {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
    color: #0d6efd;
    text-decoration: none;
    margin-left: 15px;
    transition: all 0.3s ease;
}
.profile-icon:hover {
    background: #e2e6ea;
    color: #0a58ca;
}
.profile-menu {
    min-width: 200px;
}
.profile-menu .dropdown-item {
    padding: 8px 16px;
}
.profile-menu .dropdown-item i {
    margin-right: 8px;
}
.nav-link.active {
    font-weight: 600;
    color: #0d6efd !important;
}
</style>

<!-- Add Profile Menu JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    fetch('../php/check_session.php')
        .then(response => response.json())
        .then(data => {
            const profileContent = document.getElementById('profileContent');
            if (data.loggedIn) {
                profileContent.innerHTML = `
                    <li><span class="dropdown-item-text">Hi, ${data.fullname}</span></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="../users/profile.php"><i class="fas fa-user-circle"></i> My Profile</a></li>
                    <li><a class="dropdown-item" href="../shop/checkout.php"><i class="fas fa-shopping-bag"></i> My Orders</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="../php/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                `;
            } else {
                profileContent.innerHTML = `
                    <li><a class="dropdown-item" href="../users/login.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                    <li><a class="dropdown-item" href="../users/register.php"><i class="fas fa-user-plus"></i> Register</a></li>
                `;
            }
        })
        .catch(error => console.error('Error:', error));

    // Add scroll effect
    let scrollpos = window.scrollY;
    const header = document.querySelector(".navbar");
    const header_height = header.offsetHeight;

    const add_class_on_scroll = () => header.classList.add("scrolled");
    const remove_class_on_scroll = () => header.classList.remove("scrolled");

    window.addEventListener('scroll', function() {
        scrollpos = window.scrollY;
        if (scrollpos >= header_height) { add_class_on_scroll(); }
        else { remove_class_on_scroll(); }
    });
});
</script>