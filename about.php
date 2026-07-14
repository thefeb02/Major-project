<?php
require_once __DIR__ . '/config/database.php';
$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Nepal Tour & Travel</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;600;700&family=Noto+Sans+Devanagari:wght@400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=3">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="logo">
                <img src="img/logo.png?v=2" alt="Logo" class="logo-icon">
                <span class="logo-text">
                    Nepal
                    <span class="logo-subtitle">Tour & Travel</span>
                </span>
            </a>
            <ul class="nav-menu">
                <li><a href="index.php" class="nav-link">Home</a></li>
                <li><a href="index.php#places" class="nav-link">Places</a></li>
                <li><a href="index.php#things" class="nav-link">Activities</a></li>
                <li><a href="index.php#festivals" class="nav-link">Festivals</a></li>
                <li><a href="about.php" class="nav-link">About</a></li>
                <li><a href="admin.php" class="nav-link admin-nav-link">Admin</a></li>
                <?php if ($user): ?>
                    <li><a href="logout.php" class="nav-link">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php" class="nav-link">Login</a></li>
                    <li><a href="signup.php" class="nav-link">Signup</a></li>
                <?php endif; ?>
                <li><a href="#" class="nav-link search-btn">🔍</a></li>
            </ul>
        </div>
    </nav>

   <section class="about" id="about">
    <div class="container">
        <h2 class="section-title">About Us</h2>

        <div class="about-content">
            <p>
                <strong>
                   <p>
    <strong>
        Nepal Tour and Travel is a leading tourism organization dedicated to promoting Nepal as a world-class travel destination. We work tirelessly to enhance the tourism experience and support the sustainable development of tourism in Nepal, creating unforgettable experiences for visitors from around the world.
    </strong>
</p>
                </strong>
            </p>

            <!-- Stats Bar -->
            <div class="stats-bar">
                <div class="stat-item">
                    <i class="fas fa-map-marked-alt"></i>
                    <div class="stat-number">75+</div>
                    <div class="stat-label">Tour Destinations</div>
                </div>

                <div class="stat-item">
                    <i class="fas fa-users"></i>
                    <div class="stat-number">5K+</div>
                    <div class="stat-label">Happy Travelers</div>
                </div>

                <div class="stat-item">
                    <i class="fas fa-hotel"></i>
                    <div class="stat-number">150+</div>
                    <div class="stat-label">Hotels & Resorts</div>
                </div>
            </div>
        </div>
    </div>
</section>
    <footer class="footer">
    <div class="container">

        <!-- Footer Top -->
        <div class="footer-top">
            <a href="index.php" class="footer-logo">
                <img src="img/logo.png" alt="Nepal Tour & Travel Logo">
                <h3>Nepal Tour & Travel</h3>
            </a>

            <p class="footer-description">
                Explore the beauty, culture, and adventure of Nepal with us.
                Discover breathtaking destinations and create unforgettable memories.
            </p>
        </div>

        <!-- Footer Content -->
        <div class="footer-content">

            <!-- Quick Links -->
            <div class="footer-section">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="index.php#places">Places</a></li>
                    <li><a href="index.php#things">Activities</a></li>
                    <li><a href="index.php#festivals">Festivals</a></li>
                    <li><a href="about.php">About Us</a></li>
                </ul>
            </div>

            <!-- Follow Us -->
            <div class="footer-section">
                <h4>Follow Us</h4>

                <div class="social-links">
                    <a href="#" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" title="Twitter"><i class="fab fa-twitter"></i></a>
                    <a href="#" title="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" title="YouTube"><i class="fab fa-youtube"></i></a>
                </div>
            </div>

            <!-- Contact -->
            <div class="footer-section">
                <h4>Contact Us</h4>

                <p><i class="fas fa-envelope"></i> info@nepalitourtravel.com</p>

                <p><i class="fas fa-phone"></i> +977 9763658085</p>

                <p><i class="fas fa-map-marker-alt"></i> Butwal, Nepal</p>
            </div>

        </div>

        <!-- Footer Bottom -->
        <div class="footer-bottom">
             <p>&copy; 2026 Nepal Tour and Travel. All rights reserved. | Privacy Policy | Terms of Service</p>
            
        </div>

    </div>
</footer>

    <button id="scrollToTop" class="scroll-to-top" style="display:none;"><i class="fa-solid fa-chevron-up"></i></button>
    <script src="script.js"></script>
</body>
</html>
