<?php
session_start();
require_once 'php/db_connect.php';

// Get cart count if user is logged in
$cartCount = 0;
if (isset($_SESSION['user_id'])) {
    $cartCountStmt = $pdo->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
    $cartCountStmt->execute([$_SESSION['user_id']]);
    $cartCount = $cartCountStmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
}
?>
<!DOCTYPE html>
<html class="h-100" lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,shrink-to-fit=no">
    <meta name="description" content="Skye Interior Design Services - Your trusted provider of quality window blinds and treatments">
    <meta name="author" content="Skye Interior Design Services">
    <meta name="HandheldFriendly" content="true">
    <title>Skye Blinds Interior Design Services</title>

    <!-- CSS FILES -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@48,400,1,0" />
    <link rel="stylesheet" href="chat/chatbot.css">
    <link rel="stylesheet" href="css/theme.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@100;300;400;600;700&display=swap" rel="stylesheet">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/bootstrap-icons.css" rel="stylesheet">
    <link href="css/owl.carousel.min.css" rel="stylesheet">
    <link href="css/card.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

    <!-- Favicon -->
      <link rel="icon" type="image/x-icon" href="favicon/favicon.ico">
      <link rel="apple-touch-icon" sizes="180x180" href="favicon/apple-touch-icon.png">
      <link rel="icon" type="image/png" sizes="192x192" href="favicon/android-chrome-192x192.png">
      <link rel="icon" type="image/png" sizes="512x512" href="favicon/android-chrome-512x512.png">
      <link rel="icon" type="image/png" sizes="32x32" href="favicon/favicon-32x32.png">
      <link rel="icon" type="image/png" sizes="16x16" href="favicon/favicon-16x16.png">
      <link rel="manifest" href="/site.webmanifest">


     <!-- CHATBOT -->
    


</head>
<body data-bs-spy="scroll" data-bs-target="#navScroll" style="background:#fff;">
  <nav id="navScroll" class="navbar navbar-expand-lg navbar-light fixed-top" tabindex="0">
        <div class="container">
          <a class="navbar-brand pe-4 fs-4" href="index.php">
            <img src="favicon/favicon.ico" alt="Skye Logo" style="height:40px; width:auto; vertical-align:middle; margin-right:8px;">
            <span class="ms-1 fw-bolder">Skye Blinds Interior Design Services</span>
          </a>
      
          <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
          </button>
          
          <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                  <a class="nav-link" href="#gallery">Gallery</a>
                </li>
                <!-- Products Dropdown Start -->
                <li class="nav-item dropdown">
                  <a class="nav-link dropdown-toggle" href="#products" id="productsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    Products
                  </a>
                  <ul class="dropdown-menu" aria-labelledby="productsDropdown">
                    <li><a class="dropdown-item" href="shop/productsdetail.php">Product Details</a></li>
                    <li><a class="dropdown-item" href="shop/products.php">Product Listing</a></li>
                  </ul>
                </li>
                <!-- Products Dropdown End -->
                <li class="nav-item">
                  <a class="nav-link" href="#services">Services</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="#about">About</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="users/contact.php">Contact</a>  
                </li>
              </ul>
              <!-- Add Profile Icon -->
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

    <main>
      <!-- Hero Section -->
      <div class="position-relative overflow-hidden w-100 bg-light" id="hero">
        <div class="container position-relative">
            <div class="col-12 col-lg-8 mt-0 h-100 position-absolute top-0 end-0 bg-cover" data-aos="fade-left" style="background-image: url(images/home/winblinds.webp);">
    
            </div>
          <div class="row">
            <div class="col-lg-7 py-vh-6 position-relative" data-aos="fade-right">
              <h1 class="display-4 fw-bold mt-5">Transform Your Windows with Style</h1>
              <p class="lead">Custom window treatments that perfectly blend style, functionality and comfort for your space.</p>
              <a href="#gallery" class="btn btn-dark btn-xl shadow me-3 rounded-0 my-5">See Gallery</a>
            </div>
          </div>
        </div>
      </div>

        <!-- Intro/yapping Section -->
        <div class="position-relative overflow-hidden w-100 bg-light" id="#intro">            <div class="container">
            
                <div class="row d-flex justify-content-between align-items-center">
                  
                <div class="col-lg-4">
                  <h3 class="py-5 border-top border-dark" data-aos="fade-left">
                    Our dedication shows in every project. We work tirelessly to deliver quality results and carefully select inspiring images to showcase our craftsmanship.
                  </h3>
                  <p data-aos="fade-left" data-aos-delay="200">
                    Through hard work and attention to detail, we ensure every client receives exceptional service and creative solutions.
                  </p>
                </div>
                </div>
                  </div>
            <div class="container">
              <div class="row d-flex justify-content-end">
                <div class="col-lg-8" data-aos="fade-down">
                  <h2 class="display-6">
                    Find out why homeowners and businesses trust us to turn their plain window interior design into a visually appealing design. Here are the top three reasons why <b>Skye Interior Design Services </b>is a favorite among our clients.
                  </h2>
                </div>
              </div>
              <div class="row d-flex align-items-center">
                <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="200">
                  <span class="h5 fw-lighter">01.</span>
                  <h3 class="py-5 border-top border-dark">Stylish Showroom & Creative Inspiration</h3>
                  <p>Step into our unique studio, set in a beautifully restored factory, and explore the latest trends in window design. Our space is designed to spark ideas and help you find the perfect look for your home or office.</p>
                  <a href="#" class="link-fancy">Learn more</a>
                </div>
                <div class="col-md-6 col-lg-4 py-vh-4 pb-0" data-aos="fade-up" data-aos-delay="400">
                  <span class="h5 fw-lighter">02.</span>
                  <h3 class="py-5 border-top border-dark">Fresh Perspective, Genuine Care</h3>
                  <p>We approach every project with curiosity and passion—always listening, never assuming. Our team is dedicated to finding creative solutions that fit your needs, ensuring you get results you’ll love.</p>
                  <a href="#" class="link-fancy">Learn more</a>
                </div>
                <div class="col-md-6 col-lg-4 py-vh-6 pb-0" data-aos="fade-up" data-aos-delay="600">
                  <span class="h5 fw-lighter">03.</span>
                  <h3 class="py-5 border-top border-dark">Personalized Service, Every Step</h3>
                  <p>From your first visit to final installation, we’re with you all the way. Our friendly experts make the process easy and enjoyable—so you can relax and watch your vision come to life.</p>
                  <a href="#" class="link-fancy">Learn more</a>
                </div>
              </div>
            </div>
          </div>


          <!-- Gallery Section -->
      <div class="position-relative overflow-hidden w-100 bg-light" id="gallery">
        <div class="container py-vh-5">
          <div class="row">
            <div class="col-12 text-center">
              <h2 class="display-6">Our Gallery</h2>
              <p class="lead">See our work in action</p>
            </div>
          </div>

          <div class="row g-4">
            <div class="col-md-4" data-aos="fade-up">
              <img src="images/home/blinds6.jpeg" class="img-fluid rounded shadow" alt="Installation">
            </div>
            <div class="col-md-4" data-aos="fade-up">
              <img src="images/home/blinds7.jpeg" class="img-fluid rounded shadow" alt="Installation">
            </div>
            <div class="col-md-4" data-aos="fade-up">
              <img src="images/home/blinds9.jpeg" class="img-fluid rounded shadow" alt="Installation">
            </div>
            <div class="col-md-4" data-aos="fade-up">
              <img src="images/home/blinds10.jpeg" class="img-fluid rounded shadow" alt="Installation">
            </div>
            <div class="col-md-4" data-aos="fade-up">
              <img src="images/home/blinds11.jpeg" class="img-fluid rounded shadow" alt="Installation">
            </div>
            <div class="col-md-4" data-aos="fade-up">
              <img src="images/home/blinds12.jpeg" class="img-fluid rounded shadow" alt="Installation">
            </div>
            <!-- Add more gallery images -->
          </div>

        </div>
      </div>

          <!-- Products Section -->
        <div class="py-vh-4 bg-gray-100 w-100 overflow-hidden" id="products">
        <div class="container">
            <div class="row">
            <div class="col-12 text-center">
                <h2 class="display-6">Our Products</h2>
                <p class="lead">Browse our selection of quality window treatments</p>
            </div>
            </div>
            <div class="d-flex flex-wrap justify-content-center gap-3 mt-4" style="row-gap:1.5rem;">
            <div class="product-tile-custom position-relative overflow-hidden" style="width:240px; border-radius:14px; background:#fff; box-shadow:0 2px 8px rgba(0,0,0,0.06);">
                <img src="images/home/rollerblinds.jpg" alt="Roller Blinds" style="width:100%; height:150px; object-fit:cover; border-radius:14px 14px 0 0;">
                <div class="p-3">
                <h6 class="mb-2 fw-bold">Roller Blinds</h6>
                <p class="mb-2 text-muted" style="font-size:0.95rem;">Modern and versatile window covering solution</p>
                <a href="shop/productsdetail.php" class="btn btn-primary btn-sm rounded-pill px-3">View Details</a>
                </div>
            </div>
            <div class="product-tile-custom position-relative overflow-hidden" style="width:240px; border-radius:14px; background:#fff; box-shadow:0 2px 8px rgba(0,0,0,0.06);">
                <img src="images/home/vertical_blinds.jpg" alt="Vertical Blinds" style="width:100%; height:150px; object-fit:cover; border-radius:14px 14px 0 0;">
                <div class="p-3">
                <h6 class="mb-2 fw-bold">Vertical Blinds</h6>
                <p class="mb-2 text-muted" style="font-size:0.95rem;">Ideal for large windows and sliding doors</p>
                <a href="shop/productsdetail.php" class="btn btn-primary btn-sm rounded-pill px-3">View Products</a>
                </div>
            </div>
            <div class="product-tile-custom position-relative overflow-hidden" style="width:240px; border-radius:14px; background:#fff; box-shadow:0 2px 8px rgba(0,0,0,0.06);">
                <img src="images/home/Roman-Blinds.jpg" alt="Roman Blinds" style="width:100%; height:150px; object-fit:cover; border-radius:14px 14px 0 0;">
                <div class="p-3">
                <h6 class="mb-2 fw-bold">Roman Blinds</h6>
                <p class="mb-2 text-muted" style="font-size:0.95rem;">Elegant and soft fabric window coverings</p>
                <a href="shop/productsdetail.php" class="btn btn-primary btn-sm rounded-pill px-3">View Details</a>
                </div>
            </div>
            </div>
            <div class="text-center mt-4">
            <a href="shop/productsdetail.php" class="btn btn-dark btn-xl">View All Products</a>
            </div>
        </div>
        </div>


      <!-- Update the Services Section -->
      <div class="position-relative overflow-hidden w-100 bg-light" id="services">   
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-12">
                  <br>
                    <i class="fas fa-tools fa-3x mb-4 text-primary"></i>
                    <h2 class="display-6">Our Services</h2>
                </div>
            </div>
            <div class="row d-flex align-items-stretch">
                <div class="col-md-6 col-lg-4 mb-4" data-aos="fade-up">
                    <div class="card h-90 shadow-sm hover-shadow">
                        <div class="card-body text-center p-4">
                            <img src="favicon/footer/measure.svg" alt="Measure" class="mb-3" style="width: 60px; height: 48px; opacity: 0.7;">
                            <h3 class="card-title h4 mb-3">Measurement Service</h3>
                            <p class="card-text mb-4">Professional measurement service for perfect fit. Our experts ensure precise measurements for your window treatments.</p>
                            <a href="shop/measurement.php" class="btn btn-primary btn-lg mt-auto">Measuring</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="card h-90 shadow-sm hover-shadow">
                        <div class="card-body text-center p-4">
                            <img src="favicon/footer/tools.svg" alt="Install" class="mb-3" style="width: 60px; height: 48px; opacity: 0.7;">
                            <h3 class="card-title h4 mb-3">Installation</h3>
                            <p class="card-text mb-4">Expert installation by trained professionals. We ensure your window treatments are perfectly installed.</p>
                            <a href="users/contact.php" class="btn btn-primary btn-lg mt-auto">Get Quote</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="card h-90 shadow-sm hover-shadow">
                        <div class="card-body text-center p-4">
                            <img src="favicon/footer/consu.svg" alt="Consultation" class="mb-3" style="width: 60px; height: 48px; opacity: 0.7;">
                            <h3 class="card-title h4 mb-3">Consultation</h3>
                            <p class="card-text mb-4">Free consultation for perfect window treatments. Our experts will help you choose the best solutions.</p>
                            <a href="users/contact.php" class="btn btn-primary btn-lg mt-auto">Book Now</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

      <!-- Pricing Guide Modal -->
        <div class="modal fade" id="pricingGuideModal" tabindex="-1" aria-labelledby="pricingGuideLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow-lg">
                <div class="modal-header bg-primary text-white rounded-top-4">
                <div class="d-flex align-items-center">
                    <span class="me-2 fs-3"><i class="bi bi-rulers"></i></span>
                    <h5 class="modal-title mb-0" id="pricingGuideLabel">Measurement Service Pricing Guide</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-5 py-4">
                <p class="mb-4 text-secondary">
                    <i class="bi bi-info-circle-fill text-primary me-1"></i>
                    Select your product and enter the required details to estimate the price.
                </p>
                <form id="pricingForm">
                    <div class="row g-3">
                    <div class="col-md-4">
                        <label for="productType" class="form-label fw-semibold">Product Type</label>
                        <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-window"></i></span>
                        <select class="form-select" id="productType" required>
                            <option value="">Choose...</option>
                            <option value="roller">Roller Blinds</option>
                            <option value="vertical">Vertical Blinds</option>
                            <option value="roman">Roman Blinds</option>
                        </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label for="length" class="form-label fw-semibold">Total Length (meters)</label>
                        <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-arrows-expand"></i></span>
                        <input type="number" class="form-control" id="length" min=".1" step=".01" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label for="quantity" class="form-label fw-semibold">Number of Products</label>
                        <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-123"></i></span>
                        <input type="number" class="form-control" id="quantity" min="1" required>
                        </div>
                    </div>
                    </div>
                    <div class="text-center mt-4">
                    <button type="submit" class="btn btn-primary btn-lg px-5 rounded-pill shadow">Calculate Price</button>
                    </div>
                </form>
                <div id="priceResult" class="mt-4"></div>
                </div>
            </div>
            </div>
        </div>

      
      <!-- About Section -->
      <div class="position-relative overflow-hidden w-100 bg-light" id="about">        <div class="container">
          <div class="row">
            <div class="col-lg-8">
              <h2 class="display-6">About Us</h2>
              <h3 class="py-5 border-top border-dark">Mission</h3>
              <p>To provide high-quality window treatments that enhance the beauty and functionality of any space while delivering exceptional customer service.</p>
              
              <h3 class="py-5 border-top border-dark">Vision</h3>
              <p>To be the leading provider of innovative window treatment solutions, known for quality, style, and customer satisfaction.</p>
              
              <h3 class="py-5 border-top border-dark">Goals</h3>
              <ul>
                <li>Deliver excellence in product quality and service</li>
                <li>Innovate in design and functionality</li>
                <li>Build lasting relationships with our customers</li>
                <li>Maintain environmental responsibility</li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <!-- ALIS MUNA 

      <div class="py-vh-4 bg-gray-100 w-100 overflow-hidden" id="contact">
            <div class="container">
            <div class="row">

              <div class="col-lg-6 mb-4 mb-lg-0">
                <h2 class="display-6 mb-4">Contact Us</h2>

                <div class="mb-4">
                    <h5 class="mb-3">Send us a message</h5>
                    <form id="contactForm" class="row g-4">
                    <div class="col-md-6">
                        <div class="form-floating">
                        <input type="text" class="form-control" id="fullName" placeholder="Your Name">
                        <label for="fullName">Full Name</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating">
                        <input type="email" class="form-control" id="emailAddress" placeholder="Email">
                        <label for="emailAddress">Email Address</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating">
                        <input type="tel" class="form-control" id="phone" placeholder="Phone">
                        <label for="phone">Phone Number</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-floating">
                        <textarea class="form-control" id="message" placeholder="Message" style="height: 160px"></textarea>
                        <label for="message">Message</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-warning btn-xl">Send Message</button>
                    </div>
                    </form>
                </div>

                <div class="d-flex align-items-center my-4">
                    <hr class="flex-grow-1" style="border-top: 2px solid #ccc;">
                    <span class="mx-3 text-muted fw-bold">OR</span>
                    <hr class="flex-grow-1" style="border-top: 2px solid #ccc;">
                </div>

                <div class="mb-3">
                    <h5 class="mb-3">Contact us directly</h5>
                    <div class="d-flex flex-column">
                        <div class="contact-item d-flex align-items-center mb-3">
                            <a href="https://www.facebook.com/skye.blinds.9" target="_blank" class="me-3" title="Facebook" style="display:inline-block; width:40px; height:40px;">
                                <img src="favicon/footer/facebook-svgrepo-com (1).svg" alt="Facebook" style="width:40px; height:40px;">
                            </a>
                            <span class="text-muted">Follow us on Facebook</span>
                        </div>
                        <div class="contact-item d-flex align-items-center mb-3">
                            <a href="viber://chat?number=09488736946" target="_blank" class="me-3" title="Viber" style="display:inline-block; width:40px; height:40px;">
                                <img src="favicon/footer/viber-svgrepo-com.svg" alt="Viber" style="width:40px; height:40px;">
                            </a>
                            <span class="text-muted">Chat with us on Viber: 09488736946</span>
                        </div>
                        <div class="contact-item d-flex align-items-center">
                            <span class="me-3" style="display:inline-block; width:40px; height:40px;">
                                <img src="favicon/footer/phone-svgrepo-com.svg" alt="Phone" style="width:40px; height:40px;">
                            </span>
                            <span class="text-muted">Call us: 09488736946</span>
                        </div>
                    </div>
                </div>
                </div>
                
                <div class="col-lg-6 d-flex align-items-center justify-content-center">
                    <div class="card shadow-lg border-0 w-100" style="min-height:420px; border-radius:18px;">
                    <div class="card-body p-0">
                        <div class="p-3 pb-0">
                        <h5 class="mb-3 fw-bold"><i class="bi bi-geo-alt-fill text-primary me-2"></i>Our Location</h5>
                        </div>
                        <iframe
                        src="https://www.google.com/maps?q=14.5191288,121.0598736&output=embed"
                        width="100%"
                        height="350"
                        style="border:0; border-radius:0 0 18px 18px; display:block;"
                        allowfullscreen=""
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        title="Our Location"></iframe>
                    </div>
                    </div>
                </div>
            </div>
            </div>
        </div>
            -->

        <!-- Chatbot Integration Start -->
 

        </main>

        <!-- Footer Section -->

    <!-- JavaScript Dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/emoji-mart@latest/dist/browser.js"></script>
    <script src="chatbot/chat.js"></script>
    <script src="js/bootstrap.bundle.min.js"></script>
    <script src="js/some.js"></script>
    <script src="js/aos.js"></script>
    <script>
      AOS.init({
        duration: 800
      });
    </script>
    <script>
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
    <!-- Initialize chatbot -->
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        // Initialize chatbot with user data if available
        const userData = {
          name: '<?php echo isset($_SESSION["fullname"]) ? $_SESSION["fullname"] : ""; ?>',
          email: '<?php echo isset($_SESSION["email"]) ? $_SESSION["email"] : ""; ?>'
        };
        
        if (window.initializeChatbot) {
          window.initializeChatbot(userData);
        }
      });
    </script>

    <style>
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
    </style>
    <script>
            document.addEventListener('DOMContentLoaded', function() {
               fetch('php/check_session.php')
                .then(response => response.json())
                .then(data => {
                    const profileContent = document.getElementById('profileContent');
                    if (data.loggedIn) {
                        profileContent.innerHTML = `
                            <li><span class="dropdown-item-text">Hi, ${data.fullname}</span></li>
                            <li><hr class="dropdown-divider"></li>
                            <li class="nav-item">
                              <a class="nav-link position-relative" href="shop/cart.php">
                                <i class="fas fa-shopping-cart"></i> Cart
                                <?php if ($cartCount > 0): ?>
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                        <?php echo $cartCount; ?>
                                        <span class="visually-hidden">items in cart</span>
                                    </span>
                                <?php endif; ?>
                              </a>
                            </li>
                            <li><a class="dropdown-item" href="users/profile.php"><i class="fas fa-user-circle"></i> My Profile</a></li>
                            <li><a class="dropdown-item" href="shop/orders.php"><i class="fas fa-shopping-bag"></i> My Orders</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="php/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                        `;
                    } else {
                        profileContent.innerHTML = `
                            <li><a class="dropdown-item" href="users/login.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                            <li><a class="dropdown-item" href="users/register.php"><i class="fas fa-user-plus"></i> Register</a></li>
                        `;
                    }
                })
                .catch(error => console.error('Error:', error));
        });
    </script>

</body>
</html>