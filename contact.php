<?php
require_once __DIR__ . '/config/database.php';
$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us | Nepal Tour & Travel</title>
    <link rel="stylesheet" href="style.css?v=4">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="logo">
                <img src="img/logo.png?v=2" alt="Logo" class="logo-icon">
                <span class="logo-text">Nepal<span class="logo-subtitle">Tour & Travel</span></span>
            </a>
            <ul class="nav-menu">
                <li><a href="index.php" class="nav-link">Home</a></li>
                <li><a href="about.php" class="nav-link">About</a></li>
                <?php if ($user): ?>
                    <li><a href="logout.php" class="nav-link">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php" class="nav-link">Login</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
    <main class="travel-plan-page">
        <div class="travel-plan-shell">
            <section class="travel-plan-card">
                <h2>Contact Nepal Tour & Travel</h2>
                <p style="color:var(--text-light);margin-top:8px;">Reach out for bookings, custom itineraries, and trip support.</p>
                <div style="margin-top:16px;line-height:1.8;">
                    <p><strong>Email:</strong> info@nepalitourtravel.com</p>
                    <p><strong>Phone:</strong> +9779763658085</p>
                    <p><strong>Location:</strong> Butwal, Nepal</p>
                </div>
            </section>
        </div>
    </main>
</body>
</html>
