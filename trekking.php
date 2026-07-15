
<?php
require_once __DIR__ . '/config/database.php';
$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nepal Trekking Packages</title>
    <link rel="stylesheet" href="trekking.css">
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
           <a href="index.php" class="logo">
                <img src="img/logo.png?v=2" alt="Logo" class="logo-icon">
                <span class="logo-text">
                    Nepal
                    <span class="logo-subtitle">Tour & Travel</span>
                </span>
            </a>
            <div class="nav-links">
                <a href="trekking.php">Packages</a>

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
                    <h2 class="section-title">Our Trek Packages</h2>
                    <p class="section-subtitle">Choose from our carefully curated trekking experiences</p>

                    <div class="packages-grid" id="packagesGrid">
                        <!-- Packages will be inserted here by JavaScript -->
                    </div>
                </div>
            </section>
        </div>

        <!-- Trek Details Page -->
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
    <script src="trekking.js"></script>
<script defer src="https://static.cloudflareinsights.com/beacon.min.js/v4513226cdae34746b4dedf0b4dfa099e1781791509496" integrity="sha512-ZE9pZaUXND66v380QUtch/5sE9tPFh2zg45pR2PB0CVkCtOREv2AJKkSidISWkysEuQ0EH8faUU5du78bx87UQ==" data-cf-beacon='{"version":"2024.11.0","token":"499e684b7b1043878977050a0a606794","r":1,"server_timing":{"name":{"cfCacheStatus":true,"cfEdge":true,"cfExtPri":true,"cfL4":true,"cfOrigin":true,"cfSpeedBrain":true},"location_startswith":null}}' crossorigin="anonymous"></script>
</body>

</html>
