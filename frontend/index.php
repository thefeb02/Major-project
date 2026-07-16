

<?php
require_once __DIR__ . '/../Backend/database.php';
$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nepal Tour and Travel - Discover the Magic of Nepal</title>
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
                <li><a href="#festivals" class="nav-link">Festivals</a></li>

                <li><a href="about.php" class="nav-link">About</a></li>
                <li><a href="admin.php" class="nav-link admin-nav-link">Admin</a></li>
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

    <section class="hero">
        <div class="nepali-text-container">
            <div class="nepali-text">NEPAL</div>
        </div>
        <div class="hero-content">
            <div class="hero-content">

    <div class="hero-badge">
      <h2><b> ⭐⭐⭐⭐⭐ Trusted by 5,000+ Travelers</b></h2>
    </div>

    <h1>Discover the Magic of Nepal</h1>

    <p>
        Explore breathtaking mountains, ancient temples,
        vibrant festivals and unforgettable adventures
        across the Himalayas.
    </p>

    <div class="hero-buttons">
        <a href="#places" class="cta-button">
            Explore Destinations
        </a>

       
        </a>
    </div>

    <div class="hero-features">
        <span>✔ Local Guides</span>
        <span>✔ Best Price</span>
        <span>✔ Safe Travel</span>
    </div>

</div>
        </div>
    </section>
    <!-- Latest Stories Section -->
    <section class="latest-stories" id="stories">
        <div class="container">
            <h2 class="section-title">Latest Stories</h2>
          <b>  <p class="section-subtitle">Discover inspiring travel stories and experiences from our community</p></b>
            <div class="stories-grid">
                <article class="story-card">
                    <div class="story-image-wrapper">
                        <img src="../img/3.jpeg" alt="Story 1">
                        <span class="story-badge">Featured</span>
                    </div>
                    <div class="story-content">
                        <span class="story-date">May 15, 2026</span>
                        <h3>Trekking in the Himalayas</h3>
                        <p>Discover the best trekking routes and prepare for your mountain adventure with expert tips.</p>
                        <a href="#" class="read-more">Read More →</a>
                    </div>
                </article>
                <article class="story-card">
                    <div class="story-image-wrapper">
                        <img src="../img/4.jpeg" alt="Story 2">
                        <span class="story-badge">Popular</span>
                    </div>
                    <div class="story-content">
                        <span class="story-date">May 12, 2026</span>
                        <h3>Cultural Heritage Sites</h3>
                        <p>Explore the ancient temples and cultural landmarks that define Nepal's rich history.</p>
                        <a href="#" class="read-more">Read More →</a>
                    </div>
                </article>
                <article class="story-card">
                    <div class="story-image-wrapper">
                        <img src="../img/5.jpeg" alt="Story 3">
                        <span class="story-badge">Trending</span>
                    </div>
                    <div class="story-content">
                        <span class="story-date">May 10, 2026</span>
                        <h3>Adventure Activities</h3>
                     <p>From paragliding to white-water rafting, find your next adrenaline-pumping experience.</p>
                        <a href="#" class="read-more">Read More →</a>
                    </div>
                </article>
            </div>
            <button class="view-all-btn">View All Stories</button>
        </div>
    </section>

    <!-- Places to Go Section -->
    <section class="places" id="places">
        <div class="container">
            <h2 class="section-title">Places to Go</h2>
            <p class="section-subtitle"><B>Explore the most stunning destinations across Nepal</b></p>
            
            <!-- Category Filter Buttons -->
            <div class="places-categories">
                <button class="category-btn active" data-category="provinces">Provinces</button>
                <button class="category-btn" data-category="heritage">World Heritage (UNESCO)</button>
                <button class="category-btn" data-category="protected">Protected Area</button>
                <button class="category-btn" data-category="cities">Cities and Towns</button>
                <button class="category-btn" data-category="peaks">Eight Thousanders</button>
                <button class="category-btn" data-category="pilgrimage">Pilgrimage Sites</button>
                <button class="category-btn" data-category="hills">Mid Hills</button>
            </div>
            
            <!-- Places Grid -->
            <div class="places-grid" id="places-grid">
                <!-- Provinces Category -->
             
                     <a href="places/koshi" class="place-card" data-category="provinces">
                    <div class="place-image">
                        <img src="https://i.pinimg.com/736x/38/7a/21/387a21d7763974798937d09cecf7418f.jpg" alt="Koshi">
                        <div class="place-overlay">
                            <h3>Koshi</h3>
                        </div>
                    </div>
                </a>
                <a href="places/madhesh" class="place-card" data-category="provinces">
                    <div class="place-image">
                        <img src="https://chinarinepal.com/wp-content/uploads/2022/03/JANAKI-MANDIR-1024x386.png" alt="Madhesh">
                        <div class="place-overlay">
                            <h3>Madhesh</h3>
                        </div>
                    </div>
                </a>
                <a href="places/bagmati" class="place-card" data-category="provinces">
                    <div class="place-image">
                        <img src="https://i.pinimg.com/1200x/be/a3/e5/bea3e57e5abd2abb4d3ada67e7c200dc.jpg" alt="Bagmati">
                        <div class="place-overlay">
                            <h3>Bagmati</h3>
                        </div>
                    </div>
                </a>
                <a href="places/gandaki" class="place-card" data-category="provinces">
                    <div class="place-image">
                        <img src="https://i.pinimg.com/736x/56/3e/91/563e9142137cad48487a45b9bba77d62.jpg" alt="Gandaki">
                        <div class="place-overlay">
                            <h3>Gandaki</h3>
                        </div>
                    </div>
                </a>
                <a href="places/lumbini" class="place-card" data-category="provinces">
                    <div class="place-image">
                        <img src="https://i.pinimg.com/1200x/b3/83/c2/b383c22c9aa6854d763a9cbbbe1daed9.jpg" alt="Lumbini">
                        <div class="place-overlay">
                            <h3>Lumbini</h3>
                        </div>
                    </div>
                </a>
                <a href="places/karnali" class="place-card" data-category="provinces">
                    <div class="place-image">
                        <img src="https://i.pinimg.com/736x/4e/ba/4a/4eba4a5546a3525b36808a0f35aecf15.jpg" alt="Karnali">
                        <div class="place-overlay">
                            <h3>Karnali</h3>
                        </div>
                    </div>
                </a>
                <a href="places/sudurpashchim" class="place-card" data-category="provinces">
                    <div class="place-image">
                        <img src="https://i.pinimg.com/736x/f2/8f/12/f28f121913883f59a3fa9e466f56ee7e.jpg" alt="Sudurpashchim">
                        <div class="place-overlay">
                            <h3>Sudurpashchim</h3>
                        </div>
                    </div>
                </a>
            </div>
                        <button class="view-all-btn" id="places-view-all-btn">View All</button>
        </div>
    </section>
    <!-- Things to Do Section -->
    <section class="things-to-do" id="things">
        <div class="container">
            <h2 class="section-title">Things to Do</h2>
            <p class="section-subtitle"><b>Endless activities and experiences for every type of traveler</b></p>
             <div class="activities-grid"><a href="trekking.php" > 
                <div class="activity-card">
                    <div class="activity-icon">🥾</div>
               <h3>Trekking</h3>
                    <p>Explore scenic trails through mountains and valleys with breathtaking views.</p>
                </div></a>
                <a href="yoga.php" >
                <div class="activity-card">
                    <div class="activity-icon">🧘</div>
                    <h3>Meditation & Yoga</h3>
                    <p>Find inner peace in spiritual retreats and ashrams across the country.</p>
                </div></a>
                 <a href="paragliding.php" >
                <div class="activity-card">
                    <div class="activity-icon">🪂</div>
                    <h3>Paragliding</h3>
                    <p>Experience the thrill of flying over beautiful landscapes and mountain peaks.</p>
                </div></a>
                 <a href="photography.php" >
                <div class="activity-card">
                    <div class="activity-icon">📸</div>
                    <h3>Photography</h3>
                    <p>Capture stunning moments in nature and culture with professional guidance.</p>
                </div></a>
                 <a href="culinary.php" >
                <div class="activity-card">
                    <div class="activity-icon">🍽️</div>
                    <h3>Culinary Tours</h3>
                    <p>Taste authentic Nepali cuisine and local delicacies in traditional settings.</p>
                </div></a>
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
