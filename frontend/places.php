<?php
require_once __DIR__ . '/../Backend/database.php';
$user = getCurrentUser();

// 1. Calculate Base URL dynamically for resources like CSS and images
$base_url = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
if (substr($base_url, -1) !== '/') {
    $base_url .= '/';
}

// 2. Load destinations data
$destinations_file = __DIR__ . '/destinations.json';
if (!file_exists($destinations_file)) {
    die("Data file not found.");
}
$data_json = file_get_contents($destinations_file);
$provinces_data = json_decode($data_json, true);

// 3. Resolve province parameter
$province_id = isset($_GET['province']) ? strtolower(trim($_GET['province'])) : '';
if (empty($province_id) || !isset($provinces_data['provinces'][$province_id])) {
    // Redirect to home if no valid province is selected
    header("Location: index.php#places");
    exit;
}

$province = $provinces_data['provinces'][$province_id];

// Filter destinations that belong to this province
$destinations = array_filter($provinces_data['destinations'], function($dest) use ($province_id) {
    return strtolower($dest['province_id']) === $province_id;
});

// 4. Resolve destination details parameter (by slug)
$destination_slug = isset($_GET['destination']) ? strtolower(trim($_GET['destination'])) : '';
$destination = null;

if (!empty($destination_slug)) {
    foreach ($provinces_data['destinations'] as $dest) {
        if (strtolower($dest['slug']) === $destination_slug) {
            $destination = $dest;
            break;
        }
    }
    // If destination slug was provided but not found, redirect to the province page
    if (!$destination) {
        header("Location: places/" . $province_id);
        exit;
    }
}

// 5. Define SEO-friendly Title and Meta Description
if ($destination) {
    $page_title = htmlspecialchars($destination['name']) . " - " . htmlspecialchars($province['name']) . " | Nepal Tour & Travel";
    $page_desc = htmlspecialchars(substr($destination['description'], 0, 150));
} else {
    $page_title = htmlspecialchars($province['name']) . " Tourist Destinations | Nepal Tour & Travel";
    $page_desc = htmlspecialchars($province['shortDescription']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <meta name="description" content="<?php echo $page_desc; ?>">
    
    <!-- Dynamic Base URL to ensure resources resolve perfectly on rewritten paths -->
    <base href="<?php echo htmlspecialchars($base_url); ?>">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;600;700&family=Noto+Sans+Devanagari:wght@400;700;900&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="style.css?v=4">
    <link rel="stylesheet" href="places.css?v=3">
</head>
<body>
    <!-- Navigation Bar (Identical to Homepage) -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="logo">
                <img src="../img/logo.png?v=2" alt="Logo" class="logo-icon">
                <span class="logo-text">
                    Nepal
                    <span class="logo-subtitle">Tour & Travel</span>
                </span>
            </a>
            <ul class="nav-menu">
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
            </ul>
            <div class="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </nav>

    <?php if ($destination): ?>
        <!-- ==================== DESTINATION DETAILS VIEW ==================== -->
        <!-- Breadcrumb Navigation -->
        <div class="breadcrumb-bar">
            <div class="container">
                <a href="index.php">Home</a> &gt; 
                <a href="index.php#places">Places</a> &gt; 
                <a href="places/<?php echo $province_id; ?>"><?php echo htmlspecialchars($province['name']); ?></a> &gt; 
                <span><?php echo htmlspecialchars($destination['name']); ?></span>
            </div>
        </div>

        <div class="details-container">
            <div class="container">
                <a href="places/<?php echo $province_id; ?>" class="back-link">
                    <i class="fa-solid fa-arrow-left"></i> Back to <?php echo htmlspecialchars($province['name']); ?>
                </a>

                <!-- 1. Hero Banner Section -->
                <div class="details-hero-banner">
                    <img src="<?php echo htmlspecialchars($destination['heroImage']); ?>" alt="<?php echo htmlspecialchars($destination['name']); ?>" loading="lazy">
                    <div class="details-hero-overlay">
                        <div class="details-hero-meta">
                            <?php 
                                $cats = explode(',', $destination['category']);
                                echo htmlspecialchars(strtoupper(trim($cats[0]))); 
                            ?> &bull; <?php echo htmlspecialchars($destination['difficulty']); ?>
                        </div>
                        <h1 class="details-hero-title"><?php echo htmlspecialchars($destination['name']); ?></h1>
                        <div class="details-hero-info">
                            <span><i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($destination['district']); ?>, <?php echo htmlspecialchars($destination['province']); ?></span>
                            <span><i class="fa-solid fa-star"></i> <?php echo number_format($destination['rating'], 1); ?> (45 reviews)</span>
                            <span><i class="fa-solid fa-clock"></i> <?php echo htmlspecialchars($destination['duration']); ?></span>
                        </div>
                    </div>
                </div>

                <!-- 2. Two-Column Split Layout -->
                <div class="details-grid">
                    <!-- Main Content (Left Column) -->
                    <div>
                        <!-- Description -->
                        <div class="details-card">
                            <h3>About the Destination</h3>
                            <p><?php echo nl2br(htmlspecialchars($destination['description'])); ?></p>
                        </div>

                        <!-- Highlights Category Matrix -->
                        <div class="details-card">
                            <h3>Highlights</h3>
                            <div class="highlights-grid-matrix">
                                <?php if (!empty($destination['highlights']['nature'])): ?>
                                <div class="highlight-matrix-item">
                                    <div class="matrix-icon"><i class="fa-solid fa-tree"></i></div>
                                    <div class="matrix-content">
                                        <h4>Nature</h4>
                                        <p><?php echo htmlspecialchars($destination['highlights']['nature']); ?></p>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php if (!empty($destination['highlights']['history'])): ?>
                                <div class="highlight-matrix-item">
                                    <div class="matrix-icon"><i class="fa-solid fa-monument"></i></div>
                                    <div class="matrix-content">
                                        <h4>History</h4>
                                        <p><?php echo htmlspecialchars($destination['highlights']['history']); ?></p>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php if (!empty($destination['highlights']['culture'])): ?>
                                <div class="highlight-matrix-item">
                                    <div class="matrix-icon"><i class="fa-solid fa-om"></i></div>
                                    <div class="matrix-content">
                                        <h4>Culture</h4>
                                        <p><?php echo htmlspecialchars($destination['highlights']['culture']); ?></p>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php if (!empty($destination['highlights']['adventure'])): ?>
                                <div class="highlight-matrix-item">
                                    <div class="matrix-icon"><i class="fa-solid fa-mountain"></i></div>
                                    <div class="matrix-content">
                                        <h4>Adventure</h4>
                                        <p><?php echo htmlspecialchars($destination['highlights']['adventure']); ?></p>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php if (!empty($destination['highlights']['wildlife'])): ?>
                                <div class="highlight-matrix-item">
                                    <div class="matrix-icon"><i class="fa-solid fa-paw"></i></div>
                                    <div class="matrix-content">
                                        <h4>Wildlife</h4>
                                        <p><?php echo htmlspecialchars($destination['highlights']['wildlife']); ?></p>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php if (!empty($destination['highlights']['photography'])): ?>
                                <div class="highlight-matrix-item">
                                    <div class="matrix-icon"><i class="fa-solid fa-camera-retro"></i></div>
                                    <div class="matrix-content">
                                        <h4>Photography Spots</h4>
                                        <p><?php echo htmlspecialchars($destination['highlights']['photography']); ?></p>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Gallery -->
                        <div class="details-card">
                            <h3>Photo Gallery</h3>
                            <div class="photo-gallery-grid">
                                <?php foreach ($destination['galleryImages'] as $gallery_img): ?>
                                    <div class="gallery-item">
                                        <img src="<?php echo htmlspecialchars($gallery_img); ?>" alt="Gallery Image" loading="lazy">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Travel Information Grid -->
                        <div class="details-card">
                            <h3>Travel Details</h3>
                            <div class="travel-info-matrix">
                                <div class="info-matrix-item">
                                    <i class="fa-regular fa-calendar-check matrix-main-icon"></i>
                                    <div>
                                        <h4>Best Time to Visit</h4>
                                        <p><?php echo htmlspecialchars($destination['travelInfo']['bestTime']); ?></p>
                                    </div>
                                </div>
                                <div class="info-matrix-item">
                                    <i class="fa-solid fa-bus-simple matrix-main-icon"></i>
                                    <div>
                                        <h4>Transportation</h4>
                                        <p><?php echo htmlspecialchars($destination['travelInfo']['transportation']); ?></p>
                                    </div>
                                </div>
                                <div class="info-matrix-item">
                                    <i class="fa-solid fa-wallet matrix-main-icon"></i>
                                    <div>
                                        <h4>Estimated Budget</h4>
                                        <p><?php echo htmlspecialchars($destination['travelInfo']['budget']); ?></p>
                                    </div>
                                </div>
                                <div class="info-matrix-item">
                                    <i class="fa-solid fa-hotel matrix-main-icon"></i>
                                    <div>
                                        <h4>Accommodation</h4>
                                        <p><?php echo htmlspecialchars($destination['travelInfo']['accommodation']); ?></p>
                                    </div>
                                </div>
                                <div class="info-matrix-item">
                                    <i class="fa-solid fa-utensils matrix-main-icon"></i>
                                    <div>
                                        <h4>Food Options</h4>
                                        <p><?php echo htmlspecialchars($destination['travelInfo']['food']); ?></p>
                                    </div>
                                </div>
                                <div class="info-matrix-item">
                                    <i class="fa-solid fa-ticket matrix-main-icon"></i>
                                    <div>
                                        <h4>Entry Fee</h4>
                                        <p><?php echo htmlspecialchars($destination['travelInfo']['entryFee']); ?></p>
                                    </div>
                                </div>
                                <div class="info-matrix-item">
                                    <i class="fa-solid fa-door-open matrix-main-icon"></i>
                                    <div>
                                        <h4>Opening Hours</h4>
                                        <p><?php echo htmlspecialchars($destination['travelInfo']['openingHours']); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Safety & Travel Tips -->
                        <div class="details-card">
                            <div style="display: grid; grid-template-columns: 1fr; gap: 2rem;">
                                <div>
                                    <h3><i class="fa-solid fa-shield-halved" style="color: var(--accent-orange); margin-right: 0.5rem;"></i> Safety Tips</h3>
                                    <ul class="tips-list">
                                        <li><?php echo htmlspecialchars($destination['travelInfo']['safetyTips']); ?></li>
                                    </ul>
                                </div>
                                <div>
                                    <h3><i class="fa-solid fa-lightbulb" style="color: var(--primary-blue); margin-right: 0.5rem;"></i> General Travel Tips</h3>
                                    <ul class="tips-list">
                                        <li><?php echo htmlspecialchars($destination['travelInfo']['travelTips']); ?></li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Google Maps Location -->
                        <div class="details-card">
                            <h3>Location on Google Maps</h3>
                            <div class="map-container">
                                <iframe src="<?php echo htmlspecialchars($destination['coordinates']); ?>" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar (Right Column) -->
                    <div>
                        <div class="sidebar-wrapper">
                            <!-- Quick Facts Card -->
                            <div class="sidebar-card">
                                <h3>Quick Facts</h3>
                                <div class="fact-item">
                                    <span class="fact-label">Primary Category</span>
                                    <span class="fact-value"><?php echo htmlspecialchars(ucfirst($cats[0])); ?></span>
                                </div>
                                <div class="fact-item">
                                    <span class="fact-label">District</span>
                                    <span class="fact-value"><?php echo htmlspecialchars($destination['district']); ?></span>
                                </div>
                                <div class="fact-item">
                                    <span class="fact-label">Difficulty</span>
                                    <span class="fact-value"><?php echo htmlspecialchars($destination['difficulty']); ?></span>
                                </div>
                                <div class="fact-item">
                                    <span class="fact-label">Ideal Duration</span>
                                    <span class="fact-value"><?php echo htmlspecialchars($destination['duration']); ?></span>
                                </div>
                                <div class="fact-item" style="border: none;">
                                    <span class="fact-label">Best Season</span>
                                    <span class="fact-value" style="font-size: 0.8rem; max-width: 150px; text-align: right; line-height: 1.3;">
                                        <?php echo htmlspecialchars($destination['bestSeason']); ?>
                                    </span>
                                </div>
                            </div>

                            <!-- CTA Grid Actions Section -->
                            <div class="sidebar-card action-cta-card">
                                <i class="fa-solid fa-compass-drafting" style="font-size: 3rem; color: var(--accent-orange); margin-bottom: 1rem;"></i>
                                <h3>Take Action</h3>
                                <p style="font-size: 0.95rem; margin-bottom: 1.5rem; color: var(--text-light);">
                                    Plan, customize, or book your dream trip to <?php echo htmlspecialchars($destination['name']); ?> today.
                                </p>
                                
                                <div class="cta-actions-grid">
                                    <!-- Plan Your Trip CTA -->
                                    <a href="travel_plan.php?destination=<?php echo urlencode($destination['name']); ?>&action=plan" class="cta-btn-action btn-plan">
                                        <i class="fa-solid fa-map-location-dot"></i> Plan Your Trip
                                    </a>
                                    <!-- Book a Tour CTA -->
                                    <a href="travel_plan.php?destination=<?php echo urlencode($destination['name']); ?>&action=book" class="cta-btn-action btn-book">
                                        <i class="fa-solid fa-suitcase-rolling"></i> Book a Tour
                                    </a>
                                    <!-- Save to Wishlist CTA -->
                                    <button onclick="alert('Saved to your wishlist!')" class="cta-btn-action btn-wishlist">
                                        <i class="fa-regular fa-heart"></i> Save to Wishlist
                                    </button>
                                </div>
                            </div>

                            <!-- Related Destinations (Nearby Attractions) -->
                            <div class="sidebar-card">
                                <h3>Nearby Attractions</h3>
                                <div class="related-list">
                                    <?php 
                                    $related_found = false;
                                    foreach ($provinces_data['destinations'] as $rel_dest) {
                                        if (in_array($rel_dest['slug'], $destination['nearbyAttractions'])) {
                                            $related_found = true;
                                            ?>
                                            <a href="places/<?php echo $rel_dest['province_id']; ?>/<?php echo $rel_dest['slug']; ?>" class="related-item">
                                                <div class="related-thumb">
                                                    <img src="<?php echo htmlspecialchars($rel_dest['heroImage']); ?>" alt="<?php echo htmlspecialchars($rel_dest['name']); ?>" loading="lazy">
                                                </div>
                                                <div class="related-info">
                                                    <span class="related-name"><?php echo htmlspecialchars($rel_dest['name']); ?></span>
                                                    <span class="related-location"><i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($rel_dest['district']); ?></span>
                                                </div>
                                            </a>
                                            <?php
                                        }
                                    }
                                    if (!$related_found) {
                                        // Fallback if array is empty
                                        $count = 0;
                                        foreach ($provinces_data['destinations'] as $rel_dest) {
                                            if ($rel_dest['slug'] !== $destination['slug'] && $count < 3) {
                                                ?>
                                                <a href="places/<?php echo $rel_dest['province_id']; ?>/<?php echo $rel_dest['slug']; ?>" class="related-item">
                                                    <div class="related-thumb">
                                                        <img src="<?php echo htmlspecialchars($rel_dest['heroImage']); ?>" alt="<?php echo htmlspecialchars($rel_dest['name']); ?>" loading="lazy">
                                                    </div>
                                                    <div class="related-info">
                                                        <span class="related-name"><?php echo htmlspecialchars($rel_dest['name']); ?></span>
                                                        <span class="related-location"><i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($rel_dest['district']); ?></span>
                                                    </div>
                                                </a>
                                                <?php
                                                $count++;
                                            }
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <?php else: ?>
        <!-- ==================== PROVINCE LISTING VIEW ==================== -->
        <!-- Hero Header -->
        <section class="province-hero" style="background-image: linear-gradient(135deg, rgba(30, 58, 138, 0.93), rgba(15, 23, 42, 0.88)), url('<?php echo htmlspecialchars($province['featuredImage']); ?>');">
            <div class="province-hero-content">
                <h1><?php echo htmlspecialchars($province['name']); ?></h1>
                <p><?php echo htmlspecialchars($province['shortDescription']); ?></p>
                <div class="province-stats">
                    <span><i class="fa-solid fa-map-location-dot"></i> <?php echo count($destinations); ?> Destinations</span>
                    <span><i class="fa-solid fa-compass"></i> Nepal Tourism Guide</span>
                </div>
            </div>
        </section>

        <!-- Destination Cards Listing (Matching "Our Trek Packages" styling) -->
        <section class="destinations-section">
            <div class="container">
                <div class="destinations-grid">
                    <?php foreach ($destinations as $dest): 
                        // Assign difficulty class
                        $diff_class = 'difficulty-moderate';
                        if (strtolower($dest['difficulty']) === 'easy') {
                            $diff_class = 'difficulty-easy';
                        } elseif (strtolower($dest['difficulty']) === 'hard') {
                            $diff_class = 'difficulty-hard';
                        }
                    ?>
                        <div class="destination-card">
                            <div class="dest-image-wrapper">
                                <img src="<?php echo htmlspecialchars($dest['heroImage']); ?>" alt="<?php echo htmlspecialchars($dest['name']); ?>" loading="lazy">
                                <!-- Duration Badge -->
                                <div class="badge-top-left">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:0.9rem;height:0.9rem;color:var(--accent-orange);margin-right:0.2rem;">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <polyline points="12 6 12 12 16 14"></polyline>
                                    </svg>
                                    <span><?php echo htmlspecialchars($dest['duration']); ?></span>
                                </div>
                                <!-- Category Badge -->
                                <div class="badge-top-right">
                                    <?php 
                                        $cats = explode(',', $dest['category']);
                                        echo htmlspecialchars(strtoupper(trim($cats[0]))); 
                                    ?>
                                </div>
                                <!-- Difficulty Badge -->
                                <div class="badge-difficulty <?php echo $diff_class; ?>">
                                    <?php echo htmlspecialchars($dest['difficulty']); ?>
                                </div>
                            </div>
                            
                            <div class="dest-content">
                                <div class="dest-meta-row">
                                    <div class="dest-rating">
                                        <i class="fa-solid fa-star star-icon"></i>
                                        <span><?php echo number_format($dest['rating'], 1); ?></span>
                                        <span class="reviews-count">(45 reviews)</span>
                                    </div>
                                    <div class="dest-location">
                                        <i class="fa-solid fa-location-dot"></i>
                                        <span><?php echo htmlspecialchars($dest['district']); ?></span>
                                    </div>
                                </div>
                                
                                <h3 class="dest-title"><?php echo htmlspecialchars($dest['name']); ?></h3>
                                <p class="dest-desc"><?php echo htmlspecialchars(substr($dest['description'], 0, 110)); ?>...</p>
                                
                                <div class="dest-footer-row">
                                    <div class="dest-info-pill">
                                        <span>Best Time</span>
                                        <?php 
                                            // Show a shortened version of season to fit card cleanly
                                            $parts = explode(',', $dest['bestSeason']);
                                            echo htmlspecialchars($parts[0]); 
                                        ?>
                                    </div>
                                    <a href="places/<?php echo $province_id; ?>/<?php echo strtolower($dest['slug']); ?>" class="btn-view-details">
                                        View Details <i class="fa-solid fa-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- Footer (Identical to Homepage) -->
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

    <!-- Back to Top Button and Mobile Menu Handler -->
    <button id="scrollToTop" class="scroll-to-top" style="display:none;"><i class="fa-solid fa-chevron-up"></i></button>
    <script>
        // Simple client-side scripts for scroll and nav menu toggle in Places pages
        document.addEventListener('DOMContentLoaded', function() {
            // Hamburger active toggle
            const hamburger = document.querySelector('.hamburger');
            const navMenu = document.querySelector('.nav-menu');
            if (hamburger && navMenu) {
                hamburger.addEventListener('click', function() {
                    hamburger.classList.toggle('active');
                    navMenu.classList.toggle('active');
                });
            }

            // Scroll to top display logic
            const scrollToTopBtn = document.getElementById('scrollToTop');
            window.addEventListener('scroll', function() {
                if (window.pageYOffset > 300) {
                    if (scrollToTopBtn) scrollToTopBtn.style.display = 'block';
                } else {
                    if (scrollToTopBtn) scrollToTopBtn.style.display = 'none';
                }
            });

            scrollToTopBtn?.addEventListener('click', function() {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>
</html>
