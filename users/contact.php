<?php
require_once('../php/db_connect.php');
require_once('../php/mailer_config.php');

// Set timezone to Manila time
date_default_timezone_set('Asia/Manila');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    // Validate all fields are filled
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = "All fields are required. Please fill in all the information.";
    } 
    // Validate email format
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    }
    else {
        try {
            // Insert into database
            $sql = "INSERT INTO contact_messages (name, email, subject, message, created_at) VALUES (?, ?, ?, ?, NOW())";
            $stmt = $pdo->prepare($sql);
            
            if ($stmt->execute([$name, $email, $subject, $message])) {
                // Prepare email content with professional formatting
                $email_subject = "New Contact Form Submission: " . htmlspecialchars($subject);
                
                // Create formatted message body with proper HTML structure and Manila time
                $email_message = "
                    <table style='width: 100%; border-collapse: collapse;'>
                        <tr>
                            <td style='padding: 10px; border-bottom: 1px solid #eee;'><strong>Sender Name:</strong></td>
                            <td style='padding: 10px; border-bottom: 1px solid #eee;'>" . htmlspecialchars($name) . "</td>
                        </tr>
                        <tr>
                            <td style='padding: 10px; border-bottom: 1px solid #eee;'><strong>Email Address:</strong></td>
                            <td style='padding: 10px; border-bottom: 1px solid #eee;'>" . htmlspecialchars($email) . "</td>
                        </tr>
                        <tr>
                            <td style='padding: 10px; border-bottom: 1px solid #eee;'><strong>Subject:</strong></td>
                            <td style='padding: 10px; border-bottom: 1px solid #eee;'>" . htmlspecialchars($subject) . "</td>
                        </tr>
                        <tr>
                            <td style='padding: 10px; border-bottom: 1px solid #eee;'><strong>Message:</strong></td>
                            <td style='padding: 10px; border-bottom: 1px solid #eee; white-space: pre-wrap;'>" . htmlspecialchars($message) . "</td>
                        </tr>
                        <tr>
                            <td style='padding: 10px;'><strong>Submitted:</strong></td>
                            <td style='padding: 10px;'>" . date('F j, Y g:i A') . "</td>
                        </tr>
                    </table>";
                
                if(sendMail('dyterljfederiz@gmail.com', $email_subject, $email_message, $email, $name)) {
                    $success = "Message sent successfully! We'll get back to you soon.";
                } else {
                    $success = "Message saved successfully! We'll get back to you soon.";
                }
            } else {
                $error = "Sorry, there was an error sending your message.";
            }
        } catch (Exception $e) {
            error_log($e->getMessage());
            $error = "Sorry, there was an error sending your message.";
        }
    }
}
?>
<!DOCTYPE html>
<html class="h-100" lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,shrink-to-fit=no">
    <meta name="description" content="Contact Skye Interior Design Services - Get in touch with us for your window treatment needs">
    <meta name="author" content="Skye Interior Design Services">
    <meta name="HandheldFriendly" content="true">
    <title>Contact</title>

    <!-- CSS FILES -->
    <link rel="stylesheet" href="../chatbot/style.css" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0&family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@48,400,1,0" />
    <link rel="stylesheet" href="../css/theme.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@100;300;400;600;700&display=swap" rel="stylesheet">
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/bootstrap-icons.css" rel="stylesheet">
    <link href="../css/owl.carousel.min.css" rel="stylesheet">
    <link href="../css/card.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../favicon/favicon.ico">
    <link rel="apple-touch-icon" sizes="180x180" href="../favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="192x192" href="../favicon/android-chrome-192x192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="../favicon/android-chrome-512x512.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../favicon/favicon-16x16.png">
    <link rel="manifest" href="../site.webmanifest">

    <!-- Add custom animations CSS -->
    <style>
        /* Modern animations and transitions */
        .fade-in-up {
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.6s ease-out;
        }
        
        .fade-in-up.active {
            opacity: 1;
            transform: translateY(0);
        }

        .contact-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .contact-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
        }

        .social-icon {
            transition: all 0.3s ease;
        }

        .social-icon:hover {
            transform: scale(1.15);
        }

        .form-control {
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
            transform: translateY(-2px);
        }

        .btn-send {
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .btn-send:before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.6s ease, height 0.6s ease;
        }

        .btn-send:hover:before {
            width: 300px;
            height: 300px;
        }

        .map-container {
            position: relative;
            overflow: hidden;
            border-radius: 18px;
            transition: transform 0.3s ease;
        }

        .map-container:hover {
            transform: scale(1.01);
        }

        /* Modern loading animation */
        .loading-dots {
            display: inline-flex;
            align-items: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .loading-dots.active {
            opacity: 1;
        }

        .loading-dots span {
            width: 6px;
            height: 6px;
            margin: 0 2px;
            background: currentColor;
            border-radius: 50%;
            animation: dots 1s infinite;
        }

        .loading-dots span:nth-child(2) { animation-delay: 0.2s; }
        .loading-dots span:nth-child(3) { animation-delay: 0.4s; }

        @keyframes dots {
            0%, 100% { transform: scale(0.5); opacity: 0.5; }
            50% { transform: scale(1); opacity: 1; }
        }

        /* Custom form styles */
        .form-floating > label {
            transition: all 0.3s ease;
        }

        .form-floating > .form-control:focus ~ label {
            transform: translateY(-1.5rem) scale(0.85);
            color: #0d6efd;
        }

        .contact-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 40px;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
        }

        .contact-header {
            text-align: left;
            margin-bottom: 30px;
        }

        .contact-header h2 {
            font-size: 24px;
            color: #333;
            margin-bottom: 8px;
        }

        .contact-header p {
            color: #666;
            font-size: 15px;
        }

        .contact-form .form-control {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 16px;
            font-size: 15px;
            transition: all 0.3s ease;
        }

        .contact-form .form-control:focus {
            border-color:rgb(157, 204, 162);
            box-shadow: 0 0 0 3px rgba(77, 127, 82, 0.1);
        }

        .contact-form textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }

        .btn-send {
            background:rgb(204, 211, 112);
            color: white;
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-send:hover {
            background:rgb(81, 92, 17);
            transform: translateY(-2px);
        }

        .contact-info {
            margin-top: 40px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 12px;
        }

        .contact-info-item {
            display: flex;
            align-items: center;
            margin-bottom: 16px;
            color: #555;
        }

        .contact-info-item:last-child {
            margin-bottom: 0;
        }

        .contact-info-item img {
            width: 20px;
            height: 20px;
            margin-right: 12px;
            opacity: 0.7;
        }

        .map-container {
            margin-top: 30px;
            border-radius: 12px;
            overflow: hidden;
        }

        .map-container iframe {
            width: 100%;
            height: 250px;
            border: none;
        }

        @media (max-width: 768px) {
            .contact-container {
                padding: 24px;
                margin: 16px;
            }
        }
    </style>

</head>
<body data-bs-spy="scroll" data-bs-target="#navScroll">
    <nav id="navScroll" class="navbar navbar-expand-lg navbar-light fixed-top">
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
                        <a class="nav-link" href="../index.html#gallery">Gallery</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#products" id="productsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" onKeyDown="handleKeyPress(event)">
                            Products
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="productsDropdown">
                            <li><a class="dropdown-item" href="../shop/productsdetail.php">Product Details</a></li>
                            <li><a class="dropdown-item" href="../shop/products.php">Product Listing</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../index.html#services">Services</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../index.html#about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="contact.php">Contact</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../shop/cart.php">Cart</a>
                    </li>
                </ul>
                <!-- Add Profile Icon -->
                <div class="nav-item dropdown">
                    <button class="profile-icon" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false" onKeyDown="handleKeyPress(event)">
                        <i class="fas fa-user"></i>
                    </button>
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
        <div class="w-100 overflow-hidden position-relative bg-light" id="top" style="padding-top: 120px;">
            <div class="container py-5">
                <div class="contact-container">
                    <div class="contact-header">
                        <h2 class="mb-2">Contact Us</h2>
                        <p class="text-muted">Got any question? Feel free to message us and we will get in touch with you right away.</p>
                        <?php if (isset($success)): ?>
                            <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                                <?php echo htmlspecialchars($success); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                                <?php echo htmlspecialchars($error); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                    </div>

                    <form class="contact-form" id="contactForm" method="post">
                        <div class="row g-4">
                            <div class="col-12">
                                <input type="text" class="form-control" name="name" placeholder="Name" required style="background: #fff;">
                            </div>
                            <div class="col-12">
                                <input type="email" class="form-control" name="email" placeholder="Email@email.com" required style="background: #fff;">
                            </div>
                            <div class="col-12">
                                <input type="text" class="form-control" name="subject" placeholder="Subject" required style="background: #fff;">
                            </div>
                            <div class="col-12">
                                <textarea class="form-control" name="message" placeholder="Message" rows="4" required style="background: #fff;"></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-send w-50" style="background-color:rgb(211, 175, 75);">
                                    Send
                                    <div class="loading-dots">
                                        <span></span>
                                        <span></span>
                                        <span></span>
                                    </div>
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="contact-info">
                        <div class="contact-info-item">
                            <img src="../favicon/footer/fb2.svg" alt="Phone">
                            <a href="https://www.facebook.com/skye.blinds.9">Skye Window Blinds</a>
                        </div>
                        <div class="contact-info-item">
                            <img src="../favicon/footer/viber-svgrepo-com.svg" alt="Phone">
                            <a href="viber://chat?number=09488736946">09488736946</a>
                        </div>
                        <div class="contact-info-item">
                            <img src="../favicon/footer/gmail-svgrepo-com.svg" alt="Email">
                            <a href="mailto:dyterljfederiz@gmail.com">dyterljfederiz@gmail.com</a>
                        </div>
                        <div class="contact-info-item">
                            <img src="../favicon/footer/gmaps.svg" alt="Location">
                            <span>Santol extension, North Signal Village, Taguig City</span>
                        </div>
                    </div>

                    <div class="map-container">
                        <iframe
                            src="https://www.google.com/maps?q=14.5191288,121.0598736&output=embed"
                            width="100%"
                            height="300"
                            style="border:0;"
                            allowfullscreen=""
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            title="Our Location">
                        </iframe>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chatbot Integration -->
        <button id="chatbot-toggler">
            <span class="material-symbols-rounded">mode_comment</span>
            <span class="material-symbols-rounded">close</span>
        </button>
        <div class="chatbot-popup">
            <!-- Chatbot Header -->
            <div class="chat-header">
                <div class="header-info">
                    <img class="chatbot-logo" src="../chatbot/bot.png" alt="Chatbot Logo" width="35" height="35" style="background:#fff; border-radius:50%; padding:6px;" />
                    <h2 class="logo-text">Kye</h2>
                </div>
                <button id="close-chatbot" class="material-symbols-rounded">keyboard_arrow_down</button>
            </div>
            <!-- Chatbot Body -->
            <div class="chat-body">
                <div class="message bot-message">
                    <svg class="bot-avatar" xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 1024 1024">
                        <path d="M738.3 287.6H285.7c-59 0-106.8 47.8-106.8 106.8v303.1c0 59 47.8 106.8 106.8 106.8h81.5v111.1c0 .7.8 1.1 1.4.7l166.9-110.6 41.8-.8h117.4l43.6-.4c59 0 106.8-47.8 106.8-106.8V394.5c0-59-47.8-106.9-106.8-106.9zM351.7 448.2c0-29.5 23.9-53.5 53.5-53.5s53.5 23.9 53.5 53.5-23.9 53.5-53.5 53.5-53.5-23.9-53.5-53.5zm157.9 267.1c-67.8 0-123.8-47.5-132.3-109h264.6c-8.6 61.5-64.5 109-132.3 109zm110-213.7c-29.5 0-53.5-23.9-53.5-53.5s23.9-53.5 53.5-53.5 53.5 23.9 53.5 53.5-23.9 53.5-53.5 53.5z"/>
                    </svg>
                    <div class="message-text">Hey there<br />How can I help you today?</div>
                </div>
            </div>
            <!-- Chatbot Footer -->
            <div class="chat-footer">
                <form action="#" class="chat-form">
                    <textarea placeholder="Message..." class="message-input" required></textarea>
                    <div class="chat-controls">
                        <button type="button" id="emoji-picker" class="material-symbols-outlined">sentiment_satisfied</button>
                        <div class="file-upload-wrapper">
                            <input type="file" accept="image/*" id="file-input" hidden />
                            <img src="#" alt="Selected file preview" />
                            <button type="button" id="file-upload" class="material-symbols-rounded">attach_file</button>
                            <button type="button" id="file-cancel" class="material-symbols-rounded">close</button>
                        </div>
                        <button type="submit" id="send-message" class="material-symbols-rounded">arrow_upward</button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <!-- JavaScript Dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/emoji-mart@latest/dist/browser.js"></script>
    <script src="../chatbot/chat.js"></script>
    <script src="../js/bootstrap.bundle.min.js"></script>
    <script src="../js/some.js"></script>
    <script src="../js/aos.js"></script>
    <script>
        AOS.init({
            duration: 800
        });
    </script>
    <script>
        // Animation on scroll
        document.addEventListener('DOMContentLoaded', function() {
            const fadeElements = document.querySelectorAll('.fade-in-up');
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('active');
                    }
                });
            }, {
                threshold: 0.1
            });

            fadeElements.forEach(element => {
                observer.observe(element);
            });

            // Form submission animation
            const form = document.getElementById('contactForm');
            const submitBtn = form.querySelector('button[type="submit"]');
            const loadingDots = submitBtn.querySelector('.loading-dots');

            form.addEventListener('submit', function(e) {
                loadingDots.classList.add('active');
                submitBtn.disabled = true;

                // The form will now submit normally via POST
                // The loading animation will be hidden when the page reloads
            });
        });

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
    <script src="../chatbot/app.js"></script>

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
        .navbar {
            transition: all 0.3s ease;
        }
        .navbar.scrolled {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(10px);
        }
        /* Add additional styling for contact page */
        #top {
            background-color: #f8f9fa;
        }
    </style>
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
                            <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user-circle"></i> My Profile</a></li>
                            <li><a class="dropdown-item" href="../shop/orders.php"><i class="fas fa-shopping-bag"></i> My Orders</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../php/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                        `;
                    } else {
                        profileContent.innerHTML = `
                            <li><a class="dropdown-item" href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                            <li><a class="dropdown-item" href="register.php"><i class="fas fa-user-plus"></i> Register</a></li>
                        `;
                    }
                })
                .catch(error => console.error('Error:', error));
        });
    </script>
    <script>
        function handleKeyPress(event) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                event.target.click();
            }
        }
    </script>
</body>
</html>