

<?php
require_once __DIR__ . '/../Backend/database.php';
$user = getCurrentUser();
$siteSettings = ['site_name' => 'AddNepalTour & Travel', 'seo_title' => 'Nepal Tour and Travel - Discover the Magic of Nepal', 'homepage_hero' => 'Discover the Magic of Nepal'];
$websiteGallery = [];
try {
    $siteSettings = array_merge($siteSettings, $pdo->query('SELECT setting_key, setting_value FROM website_settings')->fetchAll(PDO::FETCH_KEY_PAIR));
    $websiteGallery = $pdo->query('SELECT title, image_url, alt_text FROM gallery_images WHERE is_visible = 1 ORDER BY created_at DESC LIMIT 8')->fetchAll();
} catch (Throwable $e) {
    // The existing website stays available until the dashboard schema is imported.
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($siteSettings['seo_title']) ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;600;700&family=Noto+Sans+Devanagari:wght@400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=4">
    <link rel="stylesheet" href="booking-form.css">
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
                <li><a href="packages.php" class="nav-link">Packages</a></li>
               

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

    <section class="hero">
        <div class="nepali-text-container">
            <div class="nepali-text">NEPAL</div>
        </div>
        <div class="hero-content">
            <div class="hero-content">

    <div class="hero-badge">
      <h2><b> ⭐⭐⭐⭐⭐ Trusted by 5,000+ Travelers</b></h2>
    </div>

    <h1><?= htmlspecialchars($siteSettings['homepage_hero']) ?></h1>

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
                    <a class="story-image-link" href="media_detail.php?title=Trekking%20in%20the%20Himalayas&amp;desc=Discover%20the%20best%20trekking%20routes%20and%20prepare%20for%20your%20mountain%20adventure%20with%20expert%20tips.&amp;img=../img/3.jpeg&amp;alt=Trekking%20in%20the%20Himalayas&amp;topic=peaks" aria-label="View Trekking in the Himalayas details"><div class="story-image-wrapper">
                        <img src="../img/3.jpeg" alt="Story 1">
                        <span class="story-badge">Featured</span>
                    </div></a>
                    <div class="story-content">
                        <span class="story-date">May 15, 2026</span>
                        <h3>Trekking in the Himalayas</h3>
                        <p>Discover the best trekking routes and prepare for your mountain adventure with expert tips.</p>
                        <a href="#" class="read-more">Read More →</a>
                    </div>
                </article>
                <article class="story-card">
                    <a class="story-image-link" href="media_detail.php?title=Cultural%20Heritage%20Sites&amp;desc=Explore%20the%20ancient%20temples%20and%20cultural%20landmarks%20that%20define%20Nepal%27s%20rich%20history.&amp;img=../img/4.jpeg&amp;alt=Cultural%20Heritage%20Sites&amp;topic=heritage" aria-label="View Cultural Heritage Sites details"><div class="story-image-wrapper">
                        <img src="../img/4.jpeg" alt="Story 2">
                        <span class="story-badge">Popular</span>
                    </div></a>
                    <div class="story-content">
                        <span class="story-date">May 12, 2026</span>
                        <h3>Cultural Heritage Sites</h3>
                        <p>Explore the ancient temples and cultural landmarks that define Nepal's rich history.</p>
                        <a href="#" class="read-more">Read More →</a>
                    </div>
                </article>
                <article class="story-card">
                    <a class="story-image-link" href="media_detail.php?title=Adventure%20Activities&amp;desc=From%20paragliding%20to%20white-water%20rafting%2C%20find%20your%20next%20adrenaline-pumping%20experience.&amp;img=../img/5.jpeg&amp;alt=Adventure%20Activities&amp;topic=activity" aria-label="View Adventure Activities details"><div class="story-image-wrapper">
                        <img src="../img/5.jpeg" alt="Story 3">
                        <span class="story-badge">Trending</span>
                    </div></a>
                    <div class="story-content">
                        <span class="story-date">May 10, 2026</span>
                        <h3>Adventure Activities</h3>
                     <p>From paragliding to white-water rafting, find your next adrenaline-pumping experience.</p>
                        <a href="#" class="read-more">Read More →</a>
                    </div>
                </article>
           
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
           
    </section>
  
     <!-- Things to Do Section -->
    <section class="things-to-do" id="things">
        <div class="container">
            <h2 class="section-title">Things to Do</h2>
            <p class="section-subtitle"><b>Endless activities and experiences for every type of traveler</b></p>
            <div class="activities-grid">
                <a href="trekking.php" class="activity-link">
                    <article class="activity-card">
                        <div class="activity-image" style="background-image: url('https://www.andbeyond.com/wp-content/uploads/sites/5/trekking-annapurnas-nepal.jpg');"></div>
                        <div class="activity-body">
                            <span class="activity-tag">Adventure</span>
                            <h3>Trekking</h3>
                            <p>Explore scenic trails through mountains and valleys with breathtaking views.</p>
                        </div>
                    </article>
                </a>
                <a href="yoga.php" class="activity-link">
                    <article class="activity-card">
                        <div class="activity-image" style="background-image: url('https://wallpaperaccess.com/full/654400.jpg');"></div>
                        <div class="activity-body">
                            <span class="activity-tag">Wellness</span>
                            <h3>Meditation & Yoga</h3>
                            <p>Find inner peace in spiritual retreats and ashrams across the country.</p>
                        </div>
                    </article>
                </a>
                <a href="paragliding.php" class="activity-link">
                    <article class="activity-card">
                        <div class="activity-image" style="background-image: url('https://th.bing.com/th/id/R.779148d67bf705d4c90c65d79e7684bb?rik=otLE%2fHnp7NmJWQ&riu=http%3a%2f%2fhdqwalls.com%2fwallpapers%2fparagliding-wide.jpg&ehk=l4Tdcb3EapUNN3thR%2fzBmzRi6%2fYRcTu2RVCLXOt6mQo%3d&risl=&pid=ImgRaw&r=0');"></div>
                        <div class="activity-body">
                            <span class="activity-tag">Thrill</span>
                            <h3>Paragliding</h3>
                            <p>Experience the thrill of flying over beautiful landscapes and mountain peaks.</p>
                        </div>
                    </article>
                </a>
                <a href="photography.php" class="activity-link">
                    <article class="activity-card">
                        <div class="activity-image" style="background-image: url('https://images.unsplash.com/photo-1516483638261-f4dbaf036963?auto=format&fit=crop&w=900&q=80');"></div>
                        <div class="activity-body">
                            <span class="activity-tag">Creative</span>
                            <h3>Photography</h3>
                            <p>Capture stunning moments in nature and culture with professional guidance.</p>
                        </div>
                    </article>
                </a>
                <a href="culinary.php" class="activity-link">
                    <article class="activity-card">
                        <div class="activity-image" style="background-image: url('https://images.squarespace-cdn.com/content/v1/53ecd1bde4b0a6f9524254f8/1753609193026-HTNI4HYQ404GS83BTJWD/Savoring+Kathmandu-shankerhotel_com_np.png');"></div>
                        <div class="activity-body">
                            <span class="activity-tag">Taste</span>
                            <h3>Culinary Tours</h3>
                            <p>Taste authentic Nepali cuisine and local delicacies in traditional settings.</p>
                        </div>
                    </article>
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php if ($websiteGallery): ?>
        <section class="tour-packages-section" id="tour-packages" tabindex="-1" hidden>
            <div class="container">
                <div class="section-header package-section-header">
                    <h2>Tour Packages</h2>
                    <p>Choose from more than 100 destinations and find a package that fits your travel time.</p>
                </div>

                <div class="tour-filters" aria-label="Tour package filters">
                    <label>Destination
                        <select id="packageDestination"><option value="">All 100+ destinations</option></select>
                    </label>
                    <label>Tour category
                        <select id="packageCategory">
                            <option value="">All categories</option>
                            <option value="Adventure">Adventure</option><option value="Cultural">Cultural</option>
                            <option value="Family">Family</option><option value="Honeymoon">Honeymoon</option>
                            <option value="Pilgrimage">Pilgrimage</option><option value="Nature">Nature &amp; Wildlife</option>
                        </select>
                    </label>
                    <label>Duration
                        <select id="packageDuration">
                            <option value="">Any duration</option><option value="2">1–3 days</option>
                            <option value="5">4–7 days</option><option value="9">8–12 days</option><option value="14">13+ days</option>
                        </select>
                    </label>
                </div>
                <p id="selectedDestination" class="selected-destination" aria-live="polite">Showing packages for all destinations.</p>
                <div id="tourPackagesGrid" class="tour-packages-grid" aria-live="polite"></div>
                <div class="package-actions-bar"><button id="loadMorePackages" type="button" class="package-load-more">Show more packages</button></div>
            </div>
        </section>

        <section class="latest-stories" id="website-gallery">
            <div class="container">
                <h2 class="section-title">Website Gallery</h2>
                <p class="section-subtitle">Moments curated by <?= htmlspecialchars($siteSettings['site_name']) ?></p>
                <div class="stories-grid">
                    <?php foreach ($websiteGallery as $image): ?>
                        <article class="story-card">
                            <div class="story-image-wrapper"><img src="<?= htmlspecialchars($image['image_url']) ?>" alt="<?= htmlspecialchars($image['alt_text'] ?: $image['title']) ?>" loading="lazy"></div>
                            <div class="story-content"><h3><?= htmlspecialchars($image['title']) ?></h3></div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

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
    <script src="booking-form.js"></script>
</body>
</html>
