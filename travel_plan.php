<?php
require_once __DIR__ . '/config/database.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$user = getCurrentUser();
$errors = [];
$success = '';
$tableReady = true;
$editingId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$trip = null;
$formValues = [
    'title' => '',
    'destination' => '',
    'start_date' => '',
    'end_date' => '',
    'travelers' => 1,
    'notes' => '',
];

if ($editingId > 0) {
    try {
        $stmt = $pdo->prepare('SELECT id, title, destination, start_date, end_date, travelers, notes FROM travel_plans WHERE id = ? AND user_id = ?');
        $stmt->execute([$editingId, $user['id']]);
        $trip = $stmt->fetch();
    } catch (PDOException $e) {
        $tableReady = false;
        $errors[] = 'Travel plans storage is not available yet. Please refresh after the database setup completes.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mode = $_POST['mode'] ?? 'create';
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $title = trim($_POST['title'] ?? '');
    $destination = trim($_POST['destination'] ?? '');
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    $travelers = (int)($_POST['travelers'] ?? 1);
    $notes = trim($_POST['notes'] ?? '');

    $formValues = [
        'title' => $title,
        'destination' => $destination,
        'start_date' => $start_date,
        'end_date' => $end_date,
        'travelers' => $travelers,
        'notes' => $notes,
    ];

    if ($title === '' || $destination === '' || $start_date === '' || $end_date === '') {
        $errors[] = 'Please fill in the trip title, destination, and dates.';
    }
    if ($travelers < 1) {
        $errors[] = 'Traveler count must be at least 1.';
    }
    if ($start_date && $end_date && $end_date < $start_date) {
        $errors[] = 'End date cannot be earlier than the start date.';
    }

    if (empty($errors)) {
        try {
            if ($mode === 'edit' && $id > 0) {
                $stmt = $pdo->prepare('UPDATE travel_plans SET title = ?, destination = ?, start_date = ?, end_date = ?, travelers = ?, notes = ? WHERE id = ? AND user_id = ?');
                $stmt->execute([$title, $destination, $start_date, $end_date, $travelers, $notes, $id, $user['id']]);
                $success = 'Travel plan updated successfully.';
                $trip = null;
                $editingId = 0;
            } else {
                $stmt = $pdo->prepare('INSERT INTO travel_plans (user_id, title, destination, start_date, end_date, travelers, notes) VALUES (?, ?, ?, ?, ?, ?, ?)');
                $stmt->execute([$user['id'], $title, $destination, $start_date, $end_date, $travelers, $notes]);
                $success = 'Travel plan created successfully.';
            }
            if (empty($errors)) {
                header('Location: travel_plan.php');
                exit;
            }
        } catch (PDOException $e) {
            $tableReady = false;
            $errors[] = 'Travel plans storage is not available yet. Please refresh after the database setup completes.';
        }
    }
}

if (isset($_GET['delete']) && (int)$_GET['delete'] > 0) {
    try {
        $stmt = $pdo->prepare('DELETE FROM travel_plans WHERE id = ? AND user_id = ?');
        $stmt->execute([(int)$_GET['delete'], $user['id']]);
        $success = 'Travel plan removed.';
    } catch (PDOException $e) {
        $tableReady = false;
        $errors[] = 'Travel plans storage is not available yet. Please refresh after the database setup completes.';
    }
}

try {
    $stmt = $pdo->prepare('SELECT id, title, destination, start_date, end_date, travelers, notes FROM travel_plans WHERE user_id = ? ORDER BY start_date ASC, created_at DESC');
    $stmt->execute([$user['id']]);
    $plans = $stmt->fetchAll();
} catch (PDOException $e) {
    $tableReady = false;
    $plans = [];
    $errors[] = 'Travel plans storage is not available yet. Please refresh after the database setup completes.';
}
// --- Load service reference data so travel_plan.php can manage listings too ---
$flightRows = [];
$hotelRows = [];
$busRows = [];
$rentalRows = [];
$mallRows = [];
try {
    $stmt = $pdo->query('SELECT airline, flight_code, route, departure_time, price, status FROM flights ORDER BY id DESC');
    $flightRows = $stmt->fetchAll();
} catch (PDOException $e) {
    $flightRows = [];
}

try {
    $stmt = $pdo->query('SELECT id, name, location, description, phone, website, image_path FROM hotels ORDER BY id DESC');
    $hotelRows = $stmt->fetchAll();
} catch (PDOException $e) {
    $hotelRows = [];
}

try {
    $stmt = $pdo->query('SELECT id, name, route, duration, price, phone, link, image_path FROM buses ORDER BY id DESC');
    $busRows = $stmt->fetchAll();
} catch (PDOException $e) {
    $busRows = [];
}

try {
    $stmt = $pdo->query('SELECT id, name, category, location, price_per_day, phone, link, image_path FROM rentals ORDER BY id DESC');
    $rentalRows = $stmt->fetchAll();
} catch (PDOException $e) {
    $rentalRows = [];
}

try {
    $stmt = $pdo->query('SELECT id, name, location, phone, website, image_path, description FROM malls ORDER BY id DESC');
    $mallRows = $stmt->fetchAll();
} catch (PDOException $e) {
    $mallRows = [];
}

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Travel Plans | Nepal Tour & Travel</title>
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
                <li><a href="travel_plan.php" class="nav-link">Travel Plans</a></li>
                <li><a href="about.php" class="nav-link">About</a></li>
                <li><a href="logout.php" class="nav-link">Logout</a></li>
            </ul>
        </div>
    </nav>

    <main class="travel-plan-page">
        <div class="travel-plan-shell">
            <section class="travel-plan-card">
                <h2 style="margin-bottom:8px;">Manage Your Travel Plan</h2>
                <p style="color:var(--text-light);">Create, update, and keep track of your Nepal trip itinerary in one place.</p>
            </section>

            <?php if (!$tableReady): ?>
                <div class="alert alert-error">
                    The travel plans database table is being initialized. Please refresh this page in a moment.
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <div class="travel-plan-grid">
                <section class="travel-plan-card travel-plan-form">
                    <h3 style="margin-bottom:16px;"><?= $trip ? 'Edit Travel Plan' : 'Create New Travel Plan' ?></h3>
                    <form method="post">
                        <input type="hidden" name="mode" value="<?= $trip ? 'edit' : 'create' ?>">
                        <?php if ($trip): ?>
                            <input type="hidden" name="id" value="<?= (int)$trip['id'] ?>">
                        <?php endif; ?>
                        <div class="form-row">
                            <label>
                                Sources
                                <input type="text" name="title" value="<?= htmlspecialchars($trip['title'] ?? $formValues['title']) ?>" required>
                            </label>
                            <label>
                                Destination
                                <input type="text" name="destination" value="<?= htmlspecialchars($trip['destination'] ?? $formValues['destination']) ?>" required>
                            </label>
                        </div>
                        <div class="form-row">
                            <label>
                                Start Date
                                <input type="date" name="start_date" value="<?= htmlspecialchars($trip['start_date'] ?? $formValues['start_date']) ?>" required>
                            </label>
                            <label>
                                End Date
                                <input type="date" name="end_date" value="<?= htmlspecialchars($trip['end_date'] ?? $formValues['end_date']) ?>" required>
                            </label>
                        </div>
                        <div class="form-row">
                            <label>
                                Travelers
                                <input type="number" name="travelers" min="1" value="<?= (int)($trip['travelers'] ?? $formValues['travelers']) ?>" required>
                            </label>
                        </div>
                        <label>
                            Notes
                            <textarea name="notes" placeholder="Add hotels, activities, or reminders..."><?= htmlspecialchars($trip['notes'] ?? $formValues['notes']) ?></textarea>
                        </label>
                        <div class="form-actions">
                            <button type="submit" class="btn-action btn-primary-action">Save Plan</button>
                            <?php if ($trip): ?>
                                <a class="btn-action btn-secondary-action" href="travel_plan.php" style="text-decoration:none;">Cancel Edit</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </section>

                <section class="travel-plan-card">
                    <h3 style="margin-bottom:16px;">Your Saved Plans</h3>
                    <?php if (empty($plans)): ?>
                        <div class="empty-state">No travel plans yet. Create your first itinerary to get started.</div>
                    <?php else: ?>
                        <div class="plan-list">
                            <?php foreach ($plans as $plan): ?>
                                <article class="plan-card">
                                    <h3>Sources: <?= htmlspecialchars($plan['title']) ?></h3>
                                    <p><strong>Destination:</strong> <?= htmlspecialchars($plan['destination']) ?></p>
                                    <p><strong>Dates:</strong> <?= htmlspecialchars($plan['start_date']) ?> to <?= htmlspecialchars($plan['end_date']) ?></p>
                                    <p><strong>Travelers:</strong> <?= (int)$plan['travelers'] ?></p>
                                    <?php if (!empty($plan['notes'])): ?>
                                        <p><strong>Notes:</strong> <?= htmlspecialchars($plan['notes']) ?></p>
                                    <?php endif; ?>
                                    <div class="meta">
                                        <a href="travel_plan.php?edit=<?= (int)$plan['id'] ?>" class="btn-action btn-secondary-action" style="text-decoration:none;">Edit</a>
                                        <a href="travel_plan.php?delete=<?= (int)$plan['id'] ?>" class="btn-action btn-danger-action" style="text-decoration:none;" onclick="return confirm('Delete this travel plan?');">Delete</a>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>
            </div>
        </div>
    </main>

    <script>
        window.flightServiceData = <?= json_encode($flightRows, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP) ?>;
        window.hotelsData = <?= json_encode($hotelRows, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP) ?>;
        window.busesData = <?= json_encode($busRows, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP) ?>;
        window.rentalsData = <?= json_encode($rentalRows, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP) ?>;
        window.mallsData = <?= json_encode($mallRows, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP) ?>;
    </script>
    <script src="script.js?v=4"></script>
</body>
</html>
