<?php
require_once __DIR__ . '/../Backend/database.php';
$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nepal Culinary Tours</title>
    <link rel="stylesheet" href="trekking.css">
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
           <a href="index.php" class="logo">
                <img src="../img/logo.png?v=2" alt="Logo" class="logo-icon">
                <span class="logo-text">
                    Nepal
                    <span class="logo-subtitle">Tour & Travel</span>
                </span>
            </a>
            <div class="nav-links">
                <a href="Major-project/culinary.php">Packages</a>

                <button class="btn-book">Book Now</button>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div id="app">
        <!-- Packages Page -->
        <div id="packagesPage" class="page active">
            <section class="packages-section">
                <div class="container">
                    <h2 class="section-title">Our Culinary Packages</h2>
                    <p class="section-subtitle">Discover the authentic flavors of the Himalayas</p>

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
    <script src="culinary.js"></script>
</body>

</html>
