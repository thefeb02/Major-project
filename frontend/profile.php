<?php
require_once __DIR__ . '/../Backend/database.php';

// Ensure user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

$user = getCurrentUser();
$userId = $user['id'];

// Fetch User Profile details from DB to get the most updated info
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$userId]);
$userProfile = $stmt->fetch();

// Fetch Bookings
$bookingsStmt = $pdo->prepare('SELECT * FROM service_bookings WHERE user_id = ? ORDER BY created_at DESC');
$bookingsStmt->execute([$userId]);
$bookings = $bookingsStmt->fetchAll();

// Fetch Travel Plans
$plansStmt = $pdo->prepare('SELECT * FROM travel_plans WHERE user_id = ? ORDER BY created_at DESC');
$plansStmt->execute([$userId]);
$travelPlans = $plansStmt->fetchAll();

// Fetch Payments related to user's bookings
$paymentsStmt = $pdo->prepare('
    SELECT p.*, b.service_name 
    FROM payments p 
    JOIN service_bookings b ON p.booking_id = b.id 
    WHERE b.user_id = ? 
    ORDER BY p.payment_date DESC
');
$paymentsStmt->execute([$userId]);
$payments = $paymentsStmt->fetchAll();

// Determine avatar
$avatarUrl = '../img/default-avatar.png'; // Fallback
if (!empty($userProfile['profile_pic'])) {
    $avatarUrl = $userProfile['profile_pic'];
}

// Fallback site settings
$siteSettings = ['seo_title' => 'My Profile - Nepal Tour and Travel'];
try {
    $settingsStmt = $pdo->query('SELECT setting_key, setting_value FROM website_settings');
    $siteSettings = array_merge($siteSettings, $settingsStmt->fetchAll(PDO::FETCH_KEY_PAIR));
} catch (Throwable $e) {}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($siteSettings['seo_title']) ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="profile.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar" style="background: rgba(243, 240, 240, 0.8); position: fixed; top: 0; width: 100%; z-index: 1000;">
        <div class="nav-container">
            <a href="index.php" class="logo">
                <img src="../img/logo.png" alt="Logo" class="logo-icon" style="height: 40px;">
            </a>
            <ul class="nav-menu">
                <li><a href="index.php" class="nav-link">Home</a></li>
                <li><a href="places.php" class="nav-link">Places</a></li>
                
                <li>
                    <a href="profile.php" class="profile-direct-btn">
                        <img src="<?= htmlspecialchars($avatarUrl) ?>" alt="Avatar" class="profile-avatar" onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($userProfile['name']) ?>&background=2a5298&color=fff'">
                        <span><?= htmlspecialchars(explode(' ', $userProfile['name'])[0]) ?></span>
                    </a>
                </li>
                <li>
                    
                </li>
            </ul>
        </div>
    </nav>

    <!-- Dashboard Content -->
    <div class="dashboard-page">
        <div class="dashboard-container">
            
            <!-- User Header -->
            <div class="dashboard-header">
                <img src="<?= htmlspecialchars($avatarUrl) ?>" alt="Profile Picture" class="dashboard-avatar" onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($userProfile['name']) ?>&background=random&color=fff&size=128'">
                <div class="dashboard-user-info">
                    <h1><?= htmlspecialchars($userProfile['name']) ?></h1>
                    <p><i class="fas fa-envelope"></i> <?= htmlspecialchars($userProfile['email']) ?></p>
                    <p><i class="fas fa-calendar-alt"></i> Joined: <?= date('F j, Y', strtotime($userProfile['created_at'])) ?></p>
                </div>
            </div>

            <!-- Grid Content -->
            <div class="dashboard-grid">
                
                <!-- Bookings History -->
                <div class="dashboard-card">
                    <h2 class="card-title"><i class="fas fa-suitcase-rolling"></i> My Bookings</h2>
                    <?php if (empty($bookings)): ?>
                        <div class="empty-state">
                            <i class="fas fa-plane-slash"></i>
                            <p>You haven't booked any services yet.</p>
                        </div>
                    <?php else: ?>
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>Service</th>
                                    <th>Category</th>
                                    <th>Travel Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bookings as $booking): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($booking['service_name']) ?></td>
                                        <td><?= htmlspecialchars($booking['service_category']) ?></td>
                                        <td><?= date('M j, Y', strtotime($booking['travel_date'])) ?></td>
                                        <td>
                                            <span class="status-badge status-<?= strtolower($booking['status']) ?>">
                                                <?= ucfirst($booking['status']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>

                <!-- Custom Travel Plans -->
                <div class="dashboard-card">
                    <h2 class="card-title"><i class="fas fa-map-marked-alt"></i> Custom Travel Plans</h2>
                    <?php if (empty($travelPlans)): ?>
                        <div class="empty-state">
                            <i class="fas fa-map"></i>
                            <p>You haven't requested any custom travel plans.</p>
                        </div>
                    <?php else: ?>
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Destination</th>
                                    <th>Duration</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($travelPlans as $plan): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($plan['title']) ?></td>
                                        <td><?= htmlspecialchars($plan['destination']) ?></td>
                                        <td><?= date('M j, Y', strtotime($plan['start_date'])) ?> to <?= date('M j, Y', strtotime($plan['end_date'])) ?></td>
                                        <td>
                                            <span class="status-badge status-<?= strtolower($plan['status']) ?>">
                                                <?= ucfirst($plan['status']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>

                <!-- Payment History -->
                <div class="dashboard-card">
                    <h2 class="card-title"><i class="fas fa-credit-card"></i> Payment History</h2>
                    <?php if (empty($payments)): ?>
                        <div class="empty-state">
                            <i class="fas fa-receipt"></i>
                            <p>No payment records found.</p>
                        </div>
                    <?php else: ?>
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Booking</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($payments as $payment): ?>
                                    <tr>
                                        <td><?= date('M j, Y', strtotime($payment['payment_date'])) ?></td>
                                        <td><?= htmlspecialchars($payment['service_name']) ?></td>
                                        <td><?= htmlspecialchars($payment['type']) ?></td>
                                        <td>$<?= number_format($payment['amount'], 2) ?></td>
                                        <td>
                                            <span class="status-badge status-<?= strtolower($payment['status']) ?>">
                                                <?= ucfirst($payment['status']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>
</body>
</html>
