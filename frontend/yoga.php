
<?php
require_once __DIR__ . '/../Backend/database.php';
$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meditation & Yoga in Nepal</title>
    <link rel="stylesheet" href="yoga.css">
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
           <a href="index.html" class="logo">
                <img src="../img/logo.png?v=2" alt="Logo" class="logo-icon">
                <span class="logo-text">
                    Nepal
                    <span class="logo-subtitle">Tour & Travel</span>
                </span>
            </a>
            <div class="nav-links">
                <a href="Major-project/yoga.php" class="active-link">Yoga</a>  
                 <a href="index.php">Home</a> 
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div id="app">
        <!-- Packages Page -->
        <div id="packagesPage" class="page active">
            <section class="packages-section">
                <div class="container">
                    <h2 class="section-title">Meditation & Yoga Retreats</h2>
                    <p class="section-subtitle">Discover inner peace in the heart of the Himalayas</p>

                    <div class="packages-grid" id="packagesGrid">
                        <!-- Content will be inserted here by JavaScript -->
                    </div>
                </div>
            </section>
        </div>

        <!-- Details Page -->
        <div id="detailsPage" class="page">
            <section class="details-section">
                <div class="container">
                    <a href="#" id="backBtn" class="back-link">← Back to retreats</a>

                    <div id="detailsContent">
                        <!-- Details will be inserted here by JavaScript -->
                    </div>
                </div>
            </section>
        </div>
    </div>
    <script src="yoga.js"></script>
</body>

</html>
