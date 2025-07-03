<?php
include_once('../header.php');

// load measurement guide content
$measurement = file_get_contents('measure.json');
$measurementData = json_decode($measurement, true);

// Helper to format width/height as in the image
function format_dimension($value, $prev = null) {
    if ($prev === null) {
        return 'Up to ' . (int)$value . '&quot;';
    } else {
        return (int)$prev . '&quot; - ' . (int)$value . '&quot;';
    }
}

// Prepare rows for display (grouping by increasing width/height)
$rows = [];
$prevWidth = null;
$prevHeight = null;
foreach ($measurementData as $i => $row) {
    $width = $row['width'];
    $height = $row['height'];
    $price = $row['price'];
    $widthLabel = format_dimension($width, $prevWidth);
    $heightLabel = format_dimension($height, $prevHeight);
    $rows[] = [
        'width' => $widthLabel,
        'height' => $heightLabel,
        'price' => $price
    ];
    $prevWidth = $width;
    $prevHeight = $height;
}
?>

<body class="bg-light" data-bs-spy="scroll" data-bs-target="#navScroll">
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
    <div class="container mb-5">
        <div class="row justify-content-center g-4">
            <!-- How to Measure Card -->
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-sm rounded-4 h-100">
                    <div class="card-body">
                        <h2 class="fw-bold mb-4" style="font-size:1.5rem;"><span class="me-2" style="color:#6c757d;"><i class="bi bi-clipboard-check"></i></span>How to Measure</h2>
                        <div class="mb-3">
                            <strong>Step 1: Decide on Mount Type</strong><br>
                            <span class="fw-semibold">Inside Mount:</span> Fits within the window frame for a clean, built-in look. Requires precise measurements.<br>
                            <span class="fw-semibold">Outside Mount:</span> Hangs outside the frame, can make windows appear larger and offers better light control.
                        </div>
                        <div class="mb-3">
                            <strong>Step 2: Measure Width</strong><br>
                            For an <span class="fw-semibold">inside mount</span>, measure the exact width at the top, middle, and bottom of the window frame. Use the narrowest of the three measurements.<br>
                            For an <span class="fw-semibold">outside mount</span>, measure the width you want to cover. We recommend adding 3-4 inches to each side (6-8 inches total) for optimal coverage.
                        </div>
                        <div>
                            <strong>Step 3: Measure Height</strong><br>
                            For an <span class="fw-semibold">inside mount</span>, measure the height at the left, middle, and right. Use the tallest measurement.<br>
                            For an <span class="fw-semibold">outside mount</span>, measure the height from where you'll install the top of the bracket to where you want the bottom to hang (usually the window sill or floor).
                        </div>
                    </div>
                </div>
            </div>
            <!-- Base Pricing Chart Card -->
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-sm rounded-4 h-100">
                    <div class="card-body">
                        <h2 class="fw-bold mb-4" style="font-size:1.5rem;">Base Pricing Chart</h2>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Width</th>
                                        <th>Height</th>
                                        <th class="text-end">Starting Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($rows as $row): ?>
                                        <tr>
                                            <td><?= $row['width'] ?></td>
                                            <td><?= $row['height'] ?></td>
                                            <td class="text-end text-primary fw-bold">
                                                ₱<?= number_format($row['price'], 2) ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3 small text-muted">
                            * Prices are estimates for basic sheer curtains. Final price depends on fabric, style, and hardware.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
.card {
    border: none;
    border-radius: 1rem;
    background: #748873;
}
.table th, .table td {
    border-top: none;
    font-size: 1.08rem;
}
.table thead th {
    color: #888;
    font-weight: 600;
    background: #f8f9fa;
    border-bottom: 2px solid #eee;
}
.table tbody tr:not(:last-child) {
    border-bottom: 1px solid #eee;
}
.text-primary {
    color:rgb(255, 255, 255) !important;
}
@media (max-width: 991.98px) {
    .row.g-4 > [class^='col-'] {
        margin-bottom: 1.5rem;
    }
}
</style>

<script>
// Navbar scroll shadow (from index.html)
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