<?php 
session_start();
require_once '../php/db_connect.php';

try {
    // Fetch all active products
    $sql = "SELECT * FROM products WHERE status = 'active' ORDER BY category, name";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error_message = "Database error occurred. Please try again later.";
    error_log("Database error in productsdetail.php: " . $e->getMessage());
}
?>
<!doctype html>
<html class="h-100" lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,shrink-to-fit=no">
    <meta name="description" content="Skye Interior Design Services - Product Details">
    <meta name="author" content="Skye Interior Design Services">
    <meta name="HandheldFriendly" content="true">
    <title>Our Products - Detailed View</title>
    <!-- CSS FILES -->
    <link rel="stylesheet" href="../css/theme.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@100;300;400;600;700&display=swap" rel="stylesheet">
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/bootstrap-icons.css" rel="stylesheet">
    <link href="../css/owl.carousel.min.css" rel="stylesheet">
    <link href="../css/card.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="../favicon/favicon.ico">
    <style>
      .product-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        margin-bottom: 2rem;
      }
      
      .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15) !important;
      }
      
      .product-image {
        height: 300px;
        object-fit: cover;
        width: 100%;
      }
      
      .category-section {
        margin-bottom: 3rem;
        padding-top: 2rem;
      }
      
      .category-title {
        position: relative;
        padding-bottom: 0.5rem;
        margin-bottom: 2rem;
        color: #333;
      }
      
      .category-title::after {
        content: '';
        position: absolute;
        left: 0;
        bottom: 0;
        width: 50px;
        height: 3px;
        background-color: #0d6efd;
      }
      
      .product-description {
        height: 4.5em;
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
      }
      
      .features-list {
        font-size: 0.9rem;
      }
      
      .stock-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        z-index: 1;
      }
    </style>
  </head>
  <body class="bg-gray-100" data-bs-spy="scroll" data-bs-target="#navScroll">
    <!-- Navbar -->
    <nav id="navScroll" class="navbar navbar-expand-lg navbar-light fixed-top" aria-label="Main navigation">
      <div class="container">
        <a class="navbar-brand pe-4 fs-4" href="../index.html">
          <img src="../favicon/favicon.ico" alt="Skye Logo" style="height:40px; width:auto; vertical-align:middle; margin-right:8px;">
          <span class="ms-1 fw-bolder">Skye Blinds Interior Design Services</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
          <ul class="navbar-nav me-auto mb-2 mb-lg-0">
            <li class="nav-item">
              <a class="nav-link" href="../index.html">Home</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="products.php">Products</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="../index.html#services">Services</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="../index.html#about">About</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="../index.html#contact">Contact</a>
            </li>
          </ul>
        </div>
      </div>
    </nav>

    <main style="padding-top:80px;">
      <!-- Products Display Section -->
      <section class="products-section py-5">
        <div class="container">
          <h1 class="text-center mb-5">Our Products</h1>
          
          <?php if (isset($error_message)): ?>
            <div class="alert alert-danger text-center">
              <?php echo htmlspecialchars($error_message); ?>
            </div>
          <?php else: ?>
            <?php
            // Group products by category
            $grouped_products = [];
            foreach ($products as $product) {
                $category = $product['category'];
                if (!isset($grouped_products[$category])) {
                    $grouped_products[$category] = [];
                }
                $grouped_products[$category][] = $product;
            }
            
            // Display products by category
            foreach ($grouped_products as $category => $category_products):
            ?>
            <div class="category-section">
              <h2 class="category-title"><?php echo htmlspecialchars($category); ?></h2>
              <div class="row">
                <?php foreach ($category_products as $product): ?>
                <div class="col-lg-6 col-md-6 mb-4">
                  <div class="product-card bg-white rounded shadow-sm h-100 position-relative">
                    <?php if($product['stock'] > 0): ?>
                      <span class="badge bg-success stock-badge">In Stock</span>
                    <?php else: ?>
                      <span class="badge bg-danger stock-badge">Out of Stock</span>
                    <?php endif; ?>
                    
                    <div class="row g-0">
                      <div class="col-md-6">
                        <img src="data:image/jpeg;base64,<?php echo base64_encode($product['images']); ?>"
                             class="product-image rounded-start"
                             alt="<?php echo htmlspecialchars($product['name']); ?>">
                      </div>
                      <div class="col-md-6">
                        <div class="card-body">
                          <h3 class="h5 mb-2"><?php echo htmlspecialchars($product['name']); ?></h3>
                          <p class="h6 text-primary mb-3">₱<?php echo number_format($product['price'], 2); ?></p>
                          <div class="product-description mb-3">
                            <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                          </div>
                          <div class="features-list">
                            <p class="mb-1"><i class="bi bi-check-circle-fill text-success me-2"></i>Category: <?php echo htmlspecialchars($product['category']); ?></p>
                            <?php if($product['stock'] > 0): ?>
                            <p class="mb-1"><i class="bi bi-check-circle-fill text-success me-2"></i>Available: <?php echo $product['stock']; ?> units</p>
                            <?php endif; ?>
                            <?php if(isset($product['material']) && !empty($product['material'])): ?>
                            <p class="mb-1"><i class="bi bi-check-circle-fill text-success me-2"></i>Material: <?php echo htmlspecialchars($product['material']); ?></p>
                            <?php endif; ?>
                            <?php if(isset($product['dimensions']) && !empty($product['dimensions'])): ?>
                            <p class="mb-1"><i class="bi bi-check-circle-fill text-success me-2"></i>Size: <?php echo htmlspecialchars($product['dimensions']); ?></p>
                            <?php endif; ?>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <?php endforeach; ?>
              </div>
            </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </section>
    </main>

    <!-- JS Dependencies -->
    <script src="../js/bootstrap.bundle.min.js"></script>
    <script src="../js/aos.js"></script>
    <script>
      AOS.init({ duration: 800 });
    </script>
    <script>
      // Navbar scroll shadow
      let scrollpos = window.scrollY;
      const header = document.querySelector(".navbar");
      const header_height = header.offsetHeight;
      const add_class_on_scroll = () => header.classList.add("scrolled", "shadow-sm");
      const remove_class_on_scroll = () => header.classList.remove("scrolled", "shadow-sm");
      window.addEventListener('scroll', function() {
        scrollpos = window.scrollY;
        if (scrollpos >= header_height) { add_class_on_scroll(); }
        else { remove_class_on_scroll(); }
      });
    </script>
  </body>
</html>