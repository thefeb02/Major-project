

<?php
require_once __DIR__ . '/../Backend/database.php';
$user = getCurrentUser();
$siteSettings = ['site_name' => 'Nepal Tour and Travel', 'seo_title' => 'Nepal Tour and Travel - Discover the Magic of Nepal', 'homepage_hero' => 'Discover the Magic of Nepal'];
$websiteGallery = [];
$homepageSections = [];
$featuredPlaces = [];
try {
    $siteSettings = array_merge($siteSettings, $pdo->query('SELECT setting_key, setting_value FROM website_settings')->fetchAll(PDO::FETCH_KEY_PAIR));
    $websiteGallery = $pdo->query('SELECT title, image_url, category FROM gallery WHERE is_featured = 1 ORDER BY created_at DESC LIMIT 8')->fetchAll();
    $homepageSectionsRows = $pdo->query('SELECT section_key, title, subtitle, is_enabled, sort_order FROM homepage_sections ORDER BY sort_order, section_key')->fetchAll();
    foreach ($homepageSectionsRows as $row) {
        $homepageSections[$row['section_key']] = $row;
    }
    $featuredPlaces = $pdo->query('SELECT id, name, province, district, main_image FROM places WHERE status = "active" AND is_featured = 1 ORDER BY created_at DESC LIMIT 7')->fetchAll();
    if (!$featuredPlaces) {
        $featuredPlaces = $pdo->query('SELECT id, name, province, district, main_image FROM places WHERE status = "active" ORDER BY created_at DESC LIMIT 7')->fetchAll();
    }
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
    <meta name="description" content="<?= htmlspecialchars($siteSettings['seo_description'] ?? 'Nepal travel packages, places, and experiences.') ?>">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;600;700&family=Noto+Sans+Devanagari:wght@400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=4">
    <link rel="stylesheet" href="booking-form.css">
    <link rel="stylesheet" href="profile.css">
</head>
<body data-logged-in="<?= $user ? '1' : '0' ?>" data-user-name="<?= htmlspecialchars($user['name'] ?? '') ?>" data-user-email="<?= htmlspecialchars($user['email'] ?? '') ?>">
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="logo">
                <img src="<?= htmlspecialchars($siteSettings['logo_url'] ?: '../img/logo.png?v=2') ?>" alt="Logo" class="logo-icon">
               
            </a>
            <ul class="nav-menu">
                <li><a href="#places" class="nav-link">Places</a></li>
                <li><a href="#things" class="nav-link">Activities</a></li>
                <li><a href="<?= $user ? 'packages.php' : 'login.php?redirect=packages.php' ?>" class="nav-link">Packages</a></li>
               

                <li><a href="about.php" class="nav-link">About</a></li>
                <?php if ($user && isAdmin()): ?>
                    <li><a href="../Backend/admin.php" class="nav-link">Admin</a></li>
                <?php endif; ?>
                
                <?php if ($user): ?>
                    <?php 
                    $avatarUrl = '../img/default-avatar.png';
                    if (!empty($user['profile_pic'])) {
                        $avatarUrl = $user['profile_pic'];
                    }
                    ?>
                    <li>
                        <a href="profile.php" class="profile-direct-btn">
                            <img src="<?= htmlspecialchars($avatarUrl) ?>" alt="Avatar" class="profile-avatar" onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($user['name']) ?>&background=2a5298&color=fff'">
                            <span><?= htmlspecialchars(explode(' ', $user['name'])[0]) ?></span>
                        </a>
                    </li>
                    <li>
                        <a href="logout.php" class="logout-direct-btn">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </li>
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
    <?php if (!empty($homepageSections['stories']['is_enabled'])): ?>
    <!-- Latest Stories Section -->
    <section class="latest-stories" id="stories">
        <div class="container">
                        <h2 class="section-title"><?= htmlspecialchars($homepageSections['stories']['title'] ?? 'Latest Stories') ?></h2>
                    <b>  <p class="section-subtitle"><?= htmlspecialchars($homepageSections['stories']['subtitle'] ?? 'Discover inspiring travel stories and experiences from our community') ?></p></b>
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
    <?php endif; ?>

    <?php if (!empty($homepageSections['featured_places']['is_enabled'])): ?>
    <!-- Places to Go Section -->
    <section class="places" id="places">
        <div class="container">
            <h2 class="section-title"><?= htmlspecialchars($homepageSections['featured_places']['title'] ?? 'Places to Go') ?></h2>
            <p class="section-subtitle"><B><?= htmlspecialchars($homepageSections['featured_places']['subtitle'] ?? 'Explore the most stunning destinations across Nepal') ?></b></p>
            
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
                <?php foreach ($featuredPlaces as $place): ?>
                    <a href="places/<?= htmlspecialchars(strtolower(str_replace(' ', '-', $place['province']))) ?>" class="place-card" data-category="provinces">
                        <div class="place-image">
                            <img src="<?= htmlspecialchars($place['main_image'] ? (str_starts_with($place['main_image'], '../') ? $place['main_image'] : '../img/places/' . $place['main_image']) : '../img/1.jpeg') ?>" alt="<?= htmlspecialchars($place['name']) ?>">
                            <div class="place-overlay">
                                <h3><?= htmlspecialchars($place['name']) ?></h3>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>
  
    <?php if (!empty($homepageSections['activities']['is_enabled'])): ?>
    <!-- Things to Do Section -->
    <section class="things-to-do" id="things">
        <div class="container">
            <h2 class="section-title"><?= htmlspecialchars($homepageSections['activities']['title'] ?? 'Things to Do') ?></h2>
            <p class="section-subtitle"><b><?= htmlspecialchars($homepageSections['activities']['subtitle'] ?? 'Endless activities and experiences for every type of traveler') ?></b></p>
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
    <?php endif; ?>

    <!-- Footer -->
    

    <footer class="footer">
        <div class="container">
            <div class="footer-top">
                <a href="index.php" class="footer-brand">
                    <img src="<?= htmlspecialchars($siteSettings['logo_url'] ?: '../img/logo.png?v=3') ?>" alt="Nepal Tour & Travel Logo">
                    <div>
                        <h3><?= htmlspecialchars($siteSettings['site_name'] ?? 'Nepal Tour & Travel') ?></h3>
                        <span><?= htmlspecialchars($siteSettings['homepage_hero'] ?? 'Discover Nepal with comfort and confidence') ?></span>
                    </div>
                </a>

                <p class="footer-description">
                    <?= htmlspecialchars($siteSettings['footer_text'] ?? 'Curated tours, mountain adventures, cultural escapes, and trusted local guidance for an unforgettable Nepal experience.') ?>
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
                        <a href="<?= htmlspecialchars($siteSettings['facebook_url'] ?: '#') ?>" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="<?= htmlspecialchars($siteSettings['twitter_url'] ?: '#') ?>" title="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="<?= htmlspecialchars($siteSettings['instagram_url'] ?: '#') ?>" title="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="<?= htmlspecialchars($siteSettings['youtube_url'] ?: '#') ?>" title="YouTube"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>

                <div class="footer-section">
                    <h4>Contact Us</h4>
                    <p><i class="fas fa-envelope"></i> <?= htmlspecialchars($siteSettings['contact_email'] ?? 'info@nepalitourtravel.com') ?></p>
                    <p><i class="fas fa-phone"></i> <?= htmlspecialchars($siteSettings['contact_phone'] ?? '+977 9763658085') ?></p>
                    <p><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($siteSettings['address'] ?? 'Butwal, Nepal') ?></p>
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
