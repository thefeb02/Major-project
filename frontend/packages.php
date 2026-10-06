<?php
require_once __DIR__ . '/../Backend/database.php';
$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tour Packages | Nepal Tour & Travel</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=7">
    <link rel="stylesheet" href="booking-form.css">
    <link rel="stylesheet" href="profile.css">
</head>
<body data-booking-category="Tour package" data-logged-in="<?= $user ? '1' : '0' ?>" data-user-name="<?= htmlspecialchars($user['name'] ?? '') ?>" data-user-email="<?= htmlspecialchars($user['email'] ?? '') ?>">
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="logo"><img src="../img/logo.png?v=2" alt="Nepal Tour & Travel" class="logo-icon"></a>
            <ul class="nav-menu">
                <li><a href="index.php#places" class="nav-link">Places</a></li>
                <li><a href="index.php#things" class="nav-link">Activities</a></li>
                <li><a href="packages.php" class="nav-link active">Packages</a></li>
                <li><a href="about.php" class="nav-link">About</a></li>
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
            <div class="hamburger" role="button" tabindex="0" aria-label="Open menu"><span></span><span></span><span></span></div>
        </div>
    </nav>

    <main>
        <section class="tour-packages-section packages-page" id="tour-packages" tabindex="-1">
            <div class="container">
                <div class="section-header package-section-header">
                    <h1>Tour Packages</h1>
                    <p>Choose from more than 100 real Nepal travel packages and find one that fits your travel time.</p>
                </div>
                <div class="tour-filters" aria-label="Tour package filters">
                    <label>Destination<select id="packageDestination"><option value="">All destinations</option></select></label>
                    <label>Tour package<select id="packageCategory"><option value="">Choose a tour package</option><option value="Adventure">Adventure</option><option value="Cultural">Cultural</option><option value="Family">Family</option><option value="Honeymoon">Honeymoon</option><option value="Pilgrimage">Pilgrimage</option><option value="Nature">Nature &amp; Wildlife</option></select></label>
                    <label>Duration<select id="packageDuration"><option value="">Any duration</option><option value="2">1–3 days</option><option value="5">4–7 days</option><option value="9">8–12 days</option><option value="14">13+ days</option></select></label>
                </div>
                <p id="selectedDestination" class="selected-destination" aria-live="polite">Showing packages for all destinations.</p>
                <div id="tourPackagesGrid" class="tour-packages-grid" aria-live="polite"></div>
                <div class="package-actions-bar"><button id="loadMorePackages" type="button" class="package-load-more">Show more packages</button></div>
            </div>
        </section>
    </main>

    <script src="booking-form.js"></script>
    <script src="tour-packages.js?v=2"></script>
    <script>document.querySelector('.hamburger')?.addEventListener('click', () => document.querySelector('.nav-menu')?.classList.toggle('active'));</script>
</body>
</html>
