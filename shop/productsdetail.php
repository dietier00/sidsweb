<?php 
include_once('../header.php');
// Load products from JSON file
$productsJson = file_get_contents('products.json'); 
$products = json_decode($productsJson, true);
?>
<body class="bg-gray-100" data-bs-spy="scroll" data-bs-target="#navScroll">
    <!-- Navbar -->
    <nav id="navScroll" class="navbar navbar-expand-lg navbar-light fixed-top" aria-label="Main navigation">
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
                <li class="nav-item dropdown">
                  <a class="nav-link dropdown-toggle" href="#products" id="productsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    Products
                  </a>
                  <ul class="dropdown-menu" aria-labelledby="productsDropdown">
                    <li><a class="dropdown-item" href="productsdetail.php">Product Details</a></li>
                    <li><a class="dropdown-item" href="products.php">Product Listing</a></li>
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
            </ul>
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

    <main style="padding-top:80px;">
        <!-- Header Section copied from prods.html -->
        <header class="site-header d-flex justify-content-center align-items-center mb-4" style="min-height:120px;">
            <div class="container">
                <div class="row">
                    <div class="col text-center" data-aos="fade-down">
                        <h1 class="display-6">Products Detail</h1>
                    </div>
                </div>
            </div>
        </header>
        
        <!-- Loop through products and output sections with alternating layout -->
        <?php $i = 0; foreach($products as $product): ?>
          <section class="shop-detail-section section-padding <?php echo ($i % 2 == 0) ? '' : 'section-bg'; ?>" data-aos="<?php echo ($i % 2 == 0) ? 'fade-right' : 'fade-left'; ?>">
            <div class="container">
              <div class="row align-items-center">
                <?php if($i % 2 == 0): ?>
                  <div class="col-lg-6 col-12 m-auto">
                    <div class="custom-block shop-detail-custom-block bg-white p-4 rounded shadow-sm">
                      <h3 class="mb-3"><?php echo htmlspecialchars($product['name']); ?></h3>
                      <p><?php echo htmlspecialchars($product['description']); ?></p>
                      <p class="h6 text-primary">₱<?php echo number_format($product['price_php'], 2); ?></p>
                    </div>
                  </div>
                  <div class="col-lg-6 col-12" data-aos="fade-left">
                    <div class="shop-image-wrap text-center">
                      <img src="<?php echo $product['image']; ?>" class="shop-image img-fluid rounded shadow" alt="<?php echo htmlspecialchars($product['name']); ?>">
                    </div>
                  </div>
                <?php else: ?>
                  <div class="col-lg-6 col-12" data-aos="fade-right">
                    <div class="shop-image-wrap text-center">
                      <img src="<?php echo $product['image']; ?>" class="shop-image img-fluid rounded shadow" alt="<?php echo htmlspecialchars($product['name']); ?>">
                    </div>
                  </div>
                  <div class="col-lg-6 col-12 m-auto">
                    <div class="custom-block shop-detail-custom-block bg-white p-4 rounded shadow-sm">
                      <h3 class="mb-3"><?php echo htmlspecialchars($product['name']); ?></h3>
                      <p><?php echo htmlspecialchars($product['description']); ?></p>
                      <p class="h6 text-primary">₱<?php echo number_format($product['price_php'], 2); ?></p>
                    </div>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </section>
        <?php $i++; endforeach; ?>
        
        <!-- Slideshow Section copied from prods.html -->
        <br> 
        <h1 class="text-center mb-4" data-aos="fade-up">Explore More Products</h1>

        <section class="shop-detail-section section-padding pb-0 mt-5" data-aos="fade-up">
            <div class="container">
                <div class="row">
                    <div class="col-lg-4 col-12 mb-4">
                        <div class="shop-thumb bg-white rounded shadow-sm h-100">
                            <div class="shop-image-wrap text-center">
                                <img src="../images/slideshow/vertical2.jpg" class="shop-image img-fluid rounded" alt="Vertical Blinds">
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-12 mb-4">
                        <div class="shop-thumb bg-white rounded shadow-sm h-100">
                            <div class="shop-image-wrap text-center">
                                <img src="../images/slideshow/panel.jpg" class="shop-image img-fluid rounded" alt="Panel Blinds">
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-12 mb-4">
                        <div class="shop-thumb bg-white rounded shadow-sm h-100">
                            <div class="shop-image-wrap text-center">
                                <img src="../images/slideshow/Crease-Combi-Blinds.jpg" class="shop-image img-fluid rounded" alt="Crease Combi Blinds">
                            </div>
                        </div>
                    </div>
                </div>
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
      // Navbar scroll shadow functionality
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