<?php
// Connect to DB and fetch reviews
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "blinds_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT text, avatar, name FROM reviews";
$result = $conn->query($sql);

$reviews = [];
if ($result && $result->num_rows > 0) {
  while($row = $result->fetch_assoc()) {
    $reviews[] = $row;
  }
}
$conn->close();

// Fetch Facebook reviews
$fb_page_id = 'YOUR_PAGE_ID';
$fb_access_token = 'YOUR_PAGE_ACCESS_TOKEN';
$fb_api_url = "https://graph.facebook.com/v19.0/$fb_page_id/ratings?access_token=$fb_access_token";

$fb_reviews = [];
$fb_response = @file_get_contents($fb_api_url);
if ($fb_response !== false) {
    $fb_data = json_decode($fb_response, true);
    if (isset($fb_data['data'])) {
        foreach ($fb_data['data'] as $fb_review) {
            $fb_reviews[] = [
                'text' => $fb_review['review_text'] ?? '',
                'avatar' => $fb_review['reviewer']['profile_pic'] ?? 'images/avatar/default.jpg',
                'name' => $fb_review['reviewer']['name'] ?? 'Facebook User',
            ];
        }
    }
}

// Merge local and Facebook reviews
$all_reviews = array_merge($reviews, $fb_reviews);
?>
<!doctype html>
<html class="h-100" lang="en">
  <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width,initial-scale=1,shrink-to-fit=no">
      <meta name="description" content="Skye Interior Design Services - Customer Reviews">
      <meta name="author" content="Skye Interior Design Services">
      <meta name="HandheldFriendly" content="true">
      <title>Customer Reviews | Skye Blinds Interior Design Services</title>

      <!-- CSS FILES -->
      <link rel="stylesheet" href="css/theme.min.css">
      <link rel="preconnect" href="https://fonts.googleapis.com">
      <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
      <link href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@100;300;400;600;700&display=swap" rel="stylesheet">
      <link href="css/bootstrap.min.css" rel="stylesheet">
      <link href="css/bootstrap-icons.css" rel="stylesheet">
      <link href="css/owl.carousel.min.css" rel="stylesheet">
      <link href="css/card.css" rel="stylesheet">
      <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
      <link rel="icon" type="image/x-icon" href="favicon/favicon.ico">
      <link rel="apple-touch-icon" sizes="180x180" href="favicon/apple-touch-icon.png">
      <link rel="icon" type="image/png" sizes="192x192" href="favicon/android-chrome-192x192.png">
      <link rel="icon" type="image/png" sizes="512x512" href="favicon/android-chrome-512x512.png">
      <link rel="icon" type="image/png" sizes="32x32" href="favicon/favicon-32x32.png">
      <link rel="icon" type="image/png" sizes="16x16" href="favicon/favicon-16x16.png">
      <link rel="manifest" href="/site.webmanifest">
  </head>
  <body data-bs-spy="scroll" data-bs-target="#navScroll">
    <nav id="navScroll" class="navbar navbar-expand-lg navbar-light fixed-top" tabindex="0">
        <div class="container">
          <a class="navbar-brand pe-4 fs-4" href="index.html">
            <img src="favicon/favicon.ico" alt="Skye Logo" style="height:40px; width:auto; vertical-align:middle; margin-right:8px;">
            <span class="ms-1 fw-bolder">Skye Blinds Interior Design Services</span>
          </a>
          <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
          </button>
          <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                  <a class="nav-link" href="index.html#gallery">Gallery</a>
                </li>
                <!-- Products Dropdown Start -->
                <li class="nav-item dropdown">
                  <a class="nav-link dropdown-toggle" href="index.html#products" id="productsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    Products
                  </a>
                  <ul class="dropdown-menu" aria-labelledby="productsDropdown">
                    <li><a class="dropdown-item" href="shop-detail.html">Product Details</a></li>
                    <li><a class="dropdown-item" href="shop-listing.html">Product Listing</a></li>
                  </ul>
                </li>
                <!-- Products Dropdown End -->
                <li class="nav-item">
                  <a class="nav-link" href="index.html#services">Services</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="index.html#about">About</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="index.html#contact">Contact</a>  
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="#">Wishlist</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="#">Cart</a>
                </li>
              </ul>
          </div>
        </div>
      </nav>

    <main>
      <!-- Reviews Section -->
      <div class="py-vh-5 w-100 overflow-hidden bg-light" id="reviews">
        <div class="container">
          <div class="row">
            <div class="col-12 text-center">
              <h2 class="display-6 mt-5">Customer <span class="text-muted">Reviews</span></h2>
              <p class="lead">See what our customers are saying about us</p>
            </div>
          </div>
          <div class="row">
            <div class="col-12">
              <div class="owl-carousel reviews-carousel mt-4">
                <?php foreach ($all_reviews as $review): ?>
                  <div class="reviews-thumb card border-0 shadow-sm p-4 mx-2" style="border-radius:18px; background:#fff;">
                    <div class="reviews-body mb-3">
                      <h4 class="fw-normal" style="font-size:1.15rem;">"<?= htmlspecialchars($review['text']) ?>"</h4>
                    </div>
                    <div class="reviews-bottom d-flex align-items-center">
                      <img src="<?= htmlspecialchars($review['avatar']) ?>" class="avatar-image img-fluid rounded-circle shadow-sm" alt="Customer" style="width:56px; height:56px; object-fit:cover;">
                      <p class="mb-0 ms-3 fw-bold" style="color:#222;"><?= htmlspecialchars($review['name']) ?></p>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- JavaScript Dependencies -->
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.bundle.min.js"></script>
    <script src="js/owl.carousel.min.js"></script>
    <script src="js/aos.js"></script>
    <script>
      AOS.init({ duration: 800 });
      $(document).ready(function(){
        $('.reviews-carousel').owlCarousel({
          loop:true,
          margin:24,
          nav:true,
          dots:true,
          responsive:{
            0:{ items:1 },
            600:{ items:2 },
            1000:{ items:3 }
          }
        });
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
    <script src="chatbot/app.js"></script>
  </body>
</html>