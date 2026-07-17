

<?php
require_once __DIR__ . '/../Backend/database.php';
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
    <link rel="stylesheet" href="style.css?v=4">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="logo">
                <img src="../img/logo.png?v=2" alt="Logo" class="logo-icon">
               
            </a>
            <ul class="nav-menu">
                <li><a href="#places" class="nav-link">Places</a></li>
                <li><a href="#things" class="nav-link">Activities</a></li>
              

                <li><a href="about.php" class="nav-link">About</a></li>
                
                <?php if ($user): ?>
                    <li><a href="logout.php" class="nav-link">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php" class="nav-link">Login</a></li>
                    <li><a href="signup.php" class="nav-link">Signup</a></li>
                <?php endif; ?>
                
            </ul>
            <div class="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </nav>


    <section class="about-page" id="about">
        <div class="container">
            <div class="about-hero">
                <div class="about-hero-copy">
                    
                    <h1>About Nepal Tour & Travel</h1>
                    <p>
                        Nepal Tour & Travel is a modern tourism brand focused on helping travelers discover Nepal through authentic experiences, trusted hospitality, and carefully curated adventures. We combine local insight with smooth planning so every journey feels meaningful and stress-free.
                    </p>
                    <div class="about-pill-row">
                        <span class="about-pill">Trusted Local Guides</span>
                        <span class="about-pill">Safe & Seamless Travel</span>
                        <span class="about-pill">Cultural & Scenic Experiences</span>
                    </div>
                </div>

                <div class="about-hero-card">
                    <div class="about-visual">
                        <img src="../img/5.jpeg" alt="Nepal travel experience">
                        <div class="about-floating-badge">
                            <span>5000+</span>
                            <small>travellers guided each year</small>
                        </div>
                    </div>
                    <div class="about-card-content">
                        <h3>Why travelers choose us</h3>
                        <ul class="about-list">
                            <li><i class="fa-solid fa-check"></i> Personalized trip planning for every traveler.</li>
                            <li><i class="fa-solid fa-check"></i> Strong local connections, premium service, and expert guidance.</li>
                            <li><i class="fa-solid fa-check"></i> Promotion of sustainable tourism and responsible travel.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="about-grid">
                <article class="info-card">
                    <h3>Our Story</h3>
                    <p>
                        We began with a simple vision: to present Nepal not just as a destination, but as a story of mountain air, heritage temples, and unforgettable memories.
                    </p>
                </article>
                <article class="info-card">
                    <h3>Our Mission</h3>
                    <p>
                        To make every visit to Nepal more enriching, more comfortable, and more memorable by creating travel experiences rooted in quality, trust, and culture.
                    </p>
                </article>
            </div>

            <div class="about-stats">
                <div class="stat">
                    <h4>7</h4>
                    <p>Provinces to Explore</p>
                </div>
                <div class="stat">
                    <h4>5000+</h4>
                    <p>Happy Travelers</p>
                </div>
                <div class="stat">
                    <h4>24/7</h4>
                    <p>Travel Support</p>
                </div>
            </div>

            <div class="about-values">
                <article class="value-card">
                    <i class="fa-solid fa-handshake"></i>
                    <h3>Trust</h3>
                    <p>Reliable planning, transparent communication, and service that travelers can count on.</p>
                </article>
                <article class="value-card">
                    <i class="fa-solid fa-mountain-sun"></i>
                    <h3>Experience</h3>
                    <p>From mountain adventures to heritage walks, every itinerary is designed to feel special.</p>
                </article>
                <article class="value-card">
                    <i class="fa-solid fa-leaf"></i>
                    <h3>Sustainability</h3>
                    <p>We believe travel should support communities, preserve culture, and protect nature.</p>
                </article>
            </div>
        </div>
    </section>

   <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-top">
                <a href="index.php" class="footer-brand">
                    <img src="../img/logo.png?v=3" alt="Nepal Tour & Travel Logo">
                    <div>
                        <h3>Nepal Tour & Travel</h3>
                        <span>Discover Nepal with comfort and confidence</span>
                    </div>
                </a>

                <p class="footer-description">
                    Curated tours, mountain adventures, cultural escapes, and trusted local guidance for an unforgettable Nepal experience.
                </p>

                <div class="footer-badges" aria-label="Highlights">
                    <span>24/7 Support</span>
                    <span>Local Experts</span>
                    <span>Trusted Guides</span>
                </div>
            </div>

            <div class="footer-content">
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="index.php#places">Places</a></li>
                        <li><a href="index.php#things">Activities</a></li>
                        <li><a href="about.php">About Us</a></li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h4>Follow Us</h4>
                    <p class="footer-copy">
                        Stay connected for travel inspiration, updates, and destination highlights.
                    </p>
                    <div class="social-links">
                        <a href="#" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" title="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" title="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" title="YouTube"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>

                <div class="footer-section">
                    <h4>Contact Us</h4>
                    <p><i class="fas fa-envelope"></i> info@nepalitourtravel.com</p>
                    <p><i class="fas fa-phone"></i> +977 9763658085</p>
                    <p><i class="fas fa-map-marker-alt"></i> Butwal, Nepal</p>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; 2026 Nepal Tour and Travel. All rights reserved.</p>
                <p><a href="#">Privacy Policy</a> <span>•</span> <a href="#">Terms of Service</a></p>
            </div>
        </div>
    </footer>

    <button id="scrollToTop" class="scroll-to-top" style="display:none;"><i class="fa-solid fa-chevron-up"></i></button>
    <script src="script.js?v=<?php echo time(); ?>"></script>
</body>
</html>
