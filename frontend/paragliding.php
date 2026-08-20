
<?php
require_once __DIR__ . '/../Backend/database.php';
$user = getCurrentUser();
?> 
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nepal Paragliding  Packages</title>
    <link rel="stylesheet" href="paragliding.css">
    <link rel="stylesheet" href="booking-form.css">
    <link rel="stylesheet" href="profile.css">
</head>

<body data-booking-category="Paragliding" data-logged-in="<?= $user ? '1' : '0' ?>" data-user-name="<?= htmlspecialchars($user['name'] ?? '') ?>" data-user-email="<?= htmlspecialchars($user['email'] ?? '') ?>">

   <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
           <a href="index.php" class="logo">
                <img src="../img/logo.png" alt="Logo" class="logo-icon">
                
            </a>
            <div class="nav-links">
                <a href="paragliding.php">Packages</a>
                <?php if ($user): ?>
                    <?php 
                    $avatarUrl = '../img/default-avatar.png';
                    if (!empty($user['profile_pic'])) {
                        $avatarUrl = $user['profile_pic'];
                    }
                    ?>
                    <a href="profile.php" class="profile-direct-btn">
                        <img src="<?= htmlspecialchars($avatarUrl) ?>" alt="Avatar" class="profile-avatar" onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($user['name']) ?>&background=2a5298&color=fff'">
                        <span><?= htmlspecialchars(explode(' ', $user['name'])[0]) ?></span>
                    </a>
                    <a href="logout.php" class="logout-direct-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a href="signup.php">Signup</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <!-- Main Content -->
    <div id="app">
        <!-- Packages Page -->
        <div id="packagesPage" class="page active">
            <section class="packages-section">
                <div class="container">
                    <h2 class="section-title">Our Paragliding  Packages</h2>
                    <p class="section-subtitle">Soar above the Himalayas and experience Nepal from the sky</p>

                    <div class="packages-grid" id="packagesGrid">
                        <!-- Packages will be inserted here by JavaScript -->
                    </div>
                </div>
            </section>
        </div>

        <!-- Details Page -->
        <div id="detailsPage" class="page">
            <section class="details-section">
                <div class="container">
                    <a href="#" id="backBtn" class="back-link">← Back to packages</a>

                    <div id="detailsContent">
                        <!-- Details will be inserted here by JavaScript -->
                    </div>
                </div>
            </section>
        </div>
    </div>
    <script src="booking-form.js"></script>
    <script src="paragliding.js"></script>
  
</body>

</html>
