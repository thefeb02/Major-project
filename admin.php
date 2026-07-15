<?php
require_once __DIR__ . '/config/database.php';

if (!isAdmin()) {
    redirect('admin_login.php');
}

function h($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function columnExists(PDO $pdo, $table, $column)
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?'
    );
    $stmt->execute([$table, $column]);
    return (int)$stmt->fetchColumn() > 0;
}

function addColumnIfMissing(PDO $pdo, $table, $column, $definition)
{
    if (!columnExists($pdo, $table, $column)) {
        $pdo->exec("ALTER TABLE `$table` ADD COLUMN `$column` $definition");
    }
}

function tableExists(PDO $pdo, $table)
{
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?');
    $stmt->execute([$table]);
    return (int)$stmt->fetchColumn() > 0;
}

function normalizeDate($value)
{
    return trim($value) === '' ? null : trim($value);
}

$setupErrors = [];
try {
    addColumnIfMissing($pdo, 'users', 'admin_status', "ENUM('active','inactive') NOT NULL DEFAULT 'active'");
    addColumnIfMissing($pdo, 'users', 'archived_at', 'DATETIME NULL');
    addColumnIfMissing($pdo, 'travel_plans', 'status', "ENUM('pending','approved','rejected','confirmed','cancelled') NOT NULL DEFAULT 'pending'");
    addColumnIfMissing($pdo, 'travel_plans', 'archived_at', 'DATETIME NULL');
    if (tableExists($pdo, 'service_bookings')) {
        addColumnIfMissing($pdo, 'service_bookings', 'status', "ENUM('pending','approved','rejected','confirmed','cancelled') NOT NULL DEFAULT 'pending'");
        addColumnIfMissing($pdo, 'service_bookings', 'archived_at', 'DATETIME NULL');
    }
} catch (PDOException $e) {
    $setupErrors[] = 'Some admin status columns could not be prepared: ' . $e->getMessage();
}

$resources = [
    'users' => [
        'title' => 'Users',
        'table' => 'users',
        'search' => ['name', 'email', 'role', 'admin_status'],
        'sorts' => ['id', 'name', 'email', 'role', 'admin_status', 'created_at'],
        'list' => ['id', 'name', 'email', 'role', 'admin_status', 'created_at', 'archived_at'],
    ],
    'travel_plans' => [
        'title' => 'Travel Plans',
        'table' => 'travel_plans',
        'search' => ['title', 'destination', 'notes', 'status'],
        'sorts' => ['id', 'title', 'destination', 'start_date', 'end_date', 'travelers', 'status', 'created_at'],
        'list' => ['id', 'user_id', 'title', 'destination', 'start_date', 'end_date', 'travelers', 'status', 'created_at', 'archived_at'],
    ],
    'service_bookings' => [
        'title' => 'Requests',
        'table' => 'service_bookings',
        'search' => ['service_category', 'service_name', 'full_name', 'email', 'phone', 'status'],
        'sorts' => ['id', 'service_category', 'service_name', 'full_name', 'travel_date', 'status', 'created_at'],
        'list' => ['id', 'user_id', 'service_category', 'service_name', 'full_name', 'email', 'phone', 'travel_date', 'status', 'created_at', 'archived_at'],
    ],
];

$resource = $_GET['resource'] ?? 'users';
if (!isset($resources[$resource])) {
    $resource = 'users';
}
$config = $resources[$resource];
$table = $config['table'];
$errors = $setupErrors;
$success = '';

if ($resource === 'service_bookings' && !tableExists($pdo, 'service_bookings')) {
    $errors[] = 'The service_bookings table is not available yet. Import database.sql first.';
}

if (isset($_GET['export']) && $_GET['export'] === 'csv' && empty($errors)) {
    $stmt = $pdo->query('SELECT * FROM `' . $table . '` ORDER BY id DESC');
    $rows = $stmt->fetchAll();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $table . '-export.csv"');
    $out = fopen('php://output', 'w');
    if (!empty($rows)) {
        fputcsv($out, array_keys($rows[0]));
        foreach ($rows as $row) {
            fputcsv($out, $row);
        }
    } else {
        fputcsv($out, $config['list']);
    }
    fclose($out);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($errors)) {
    $mode = $_POST['mode'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    try {
        if ($mode === 'save_user') {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $role = $_POST['role'] === 'admin' ? 'admin' : 'user';
            $adminStatus = $_POST['admin_status'] === 'inactive' ? 'inactive' : 'active';
            $password = $_POST['password'] ?? '';

            if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Name and a valid email are required.';
            } elseif ($id > 0) {
                if ($password !== '') {
                    $stmt = $pdo->prepare('UPDATE users SET name = ?, email = ?, role = ?, admin_status = ?, password = ? WHERE id = ?');
                    $stmt->execute([$name, $email, $role, $adminStatus, password_hash($password, PASSWORD_DEFAULT), $id]);
                } else {
                    $stmt = $pdo->prepare('UPDATE users SET name = ?, email = ?, role = ?, admin_status = ? WHERE id = ?');
                    $stmt->execute([$name, $email, $role, $adminStatus, $id]);
                }
                $success = 'User updated.';
            } else {
                if (strlen($password) < 6) {
                    $errors[] = 'Password must be at least 6 characters for new users.';
                } else {
                    $stmt = $pdo->prepare('INSERT INTO users (name, email, password, role, admin_status, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
                    $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), $role, $adminStatus]);
                    $success = 'User added.';
                }
            }
        }

        if ($mode === 'save_plan') {
            $userId = (int)($_POST['user_id'] ?? 0);
            $title = trim($_POST['title'] ?? '');
            $destination = trim($_POST['destination'] ?? '');
            $startDate = normalizeDate($_POST['start_date'] ?? '');
            $endDate = normalizeDate($_POST['end_date'] ?? '');
            $travelers = max(1, (int)($_POST['travelers'] ?? 1));
            $notes = trim($_POST['notes'] ?? '');
            $status = in_array($_POST['status'] ?? 'pending', ['pending', 'approved', 'rejected', 'confirmed', 'cancelled'], true) ? $_POST['status'] : 'pending';

            if ($userId < 1 || $title === '' || $destination === '' || !$startDate || !$endDate) {
                $errors[] = 'User, title, destination, and dates are required.';
            } elseif ($id > 0) {
                $stmt = $pdo->prepare('UPDATE travel_plans SET user_id = ?, title = ?, destination = ?, start_date = ?, end_date = ?, travelers = ?, notes = ?, status = ? WHERE id = ?');
                $stmt->execute([$userId, $title, $destination, $startDate, $endDate, $travelers, $notes, $status, $id]);
                $success = 'Travel plan updated.';
            } else {
                $stmt = $pdo->prepare('INSERT INTO travel_plans (user_id, title, destination, start_date, end_date, travelers, notes, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
                $stmt->execute([$userId, $title, $destination, $startDate, $endDate, $travelers, $notes, $status]);
                $success = 'Travel plan added.';
            }
        }

        if ($mode === 'save_booking') {
            $userId = (int)($_POST['user_id'] ?? 0);
            $userId = $userId > 0 ? $userId : null;
            $category = trim($_POST['service_category'] ?? '');
            $service = trim($_POST['service_name'] ?? '');
            $fullName = trim($_POST['full_name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $travelDate = normalizeDate($_POST['travel_date'] ?? '');
            $message = trim($_POST['message'] ?? '');
            $status = in_array($_POST['status'] ?? 'pending', ['pending', 'approved', 'rejected', 'confirmed', 'cancelled'], true) ? $_POST['status'] : 'pending';

            if ($category === '' || $service === '' || $fullName === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $phone === '' || !$travelDate) {
                $errors[] = 'Category, service, customer details, and travel date are required.';
            } elseif ($id > 0) {
                $stmt = $pdo->prepare('UPDATE service_bookings SET user_id = ?, service_category = ?, service_name = ?, full_name = ?, email = ?, phone = ?, travel_date = ?, message = ?, status = ? WHERE id = ?');
                $stmt->execute([$userId, $category, $service, $fullName, $email, $phone, $travelDate, $message, $status, $id]);
                $success = 'Request updated.';
            } else {
                $stmt = $pdo->prepare('INSERT INTO service_bookings (user_id, service_category, service_name, full_name, email, phone, travel_date, message, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
                $stmt->execute([$userId, $category, $service, $fullName, $email, $phone, $travelDate, $message, $status]);
                $success = 'Request added.';
            }
        }

        if ($mode === 'import_csv' && isset($_FILES['csv_file']) && is_uploaded_file($_FILES['csv_file']['tmp_name'])) {
            $handle = fopen($_FILES['csv_file']['tmp_name'], 'r');
            $header = fgetcsv($handle);
            $count = 0;
            if ($resource === 'travel_plans' && $header) {
                while (($row = fgetcsv($handle)) !== false) {
                    $data = array_combine($header, $row);
                    if (!$data || empty($data['user_id']) || empty($data['title']) || empty($data['destination'])) {
                        continue;
                    }
                    $stmt = $pdo->prepare('INSERT INTO travel_plans (user_id, title, destination, start_date, end_date, travelers, notes, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
                    $stmt->execute([
                        (int)$data['user_id'],
                        $data['title'],
                        $data['destination'],
                        $data['start_date'] ?? date('Y-m-d'),
                        $data['end_date'] ?? date('Y-m-d'),
                        max(1, (int)($data['travelers'] ?? 1)),
                        $data['notes'] ?? '',
                        $data['status'] ?? 'pending',
                    ]);
                    $count++;
                }
            }
            if ($handle) {
                fclose($handle);
            }
            $success = $count . ' travel plans imported.';
        }
    } catch (PDOException $e) {
        $errors[] = 'Admin action failed: ' . $e->getMessage();
    }
}

$action = $_GET['action'] ?? '';
$id = (int)($_GET['id'] ?? 0);
if ($id > 0 && empty($errors)) {
    try {
        if ($action === 'delete' || $action === 'remove') {
            $stmt = $pdo->prepare('DELETE FROM `' . $table . '` WHERE id = ?');
            $stmt->execute([$id]);
            $success = ucfirst($resource) . ' deleted.';
        } elseif ($action === 'archive') {
            $stmt = $pdo->prepare('UPDATE `' . $table . '` SET archived_at = NOW() WHERE id = ?');
            $stmt->execute([$id]);
            $success = 'Record archived.';
        } elseif ($action === 'restore') {
            $stmt = $pdo->prepare('UPDATE `' . $table . '` SET archived_at = NULL WHERE id = ?');
            $stmt->execute([$id]);
            $success = 'Record restored.';
        } elseif ($resource === 'users' && in_array($action, ['activate', 'deactivate'], true)) {
            $stmt = $pdo->prepare('UPDATE users SET admin_status = ? WHERE id = ?');
            $stmt->execute([$action === 'activate' ? 'active' : 'inactive', $id]);
            $success = 'User ' . ($action === 'activate' ? 'activated.' : 'deactivated.');
        } elseif ($resource !== 'users' && in_array($action, ['approve', 'reject', 'confirm', 'cancel'], true)) {
            $map = ['approve' => 'approved', 'reject' => 'rejected', 'confirm' => 'confirmed', 'cancel' => 'cancelled'];
            $stmt = $pdo->prepare('UPDATE `' . $table . '` SET status = ? WHERE id = ?');
            $stmt->execute([$map[$action], $id]);
            $success = 'Record ' . $map[$action] . '.';
        }
    } catch (PDOException $e) {
        $errors[] = 'Admin action failed: ' . $e->getMessage();
    }
}

$editRow = null;
if (($_GET['edit'] ?? '') !== '' && empty($errors)) {
    $stmt = $pdo->prepare('SELECT * FROM `' . $table . '` WHERE id = ?');
    $stmt->execute([(int)$_GET['edit']]);
    $editRow = $stmt->fetch();
}

$q = trim($_GET['q'] ?? '');
$status = trim($_GET['status'] ?? '');
$sort = in_array($_GET['sort'] ?? '', $config['sorts'], true) ? $_GET['sort'] : 'id';
$dir = strtolower($_GET['dir'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';
$where = [];
$params = [];

if ($q !== '') {
    $parts = [];
    foreach ($config['search'] as $column) {
        $parts[] = "`$column` LIKE ?";
        $params[] = '%' . $q . '%';
    }
    $where[] = '(' . implode(' OR ', $parts) . ')';
}

if ($status !== '') {
    if ($resource === 'users') {
        $where[] = 'admin_status = ?';
    } else {
        $where[] = 'status = ?';
    }
    $params[] = $status;
}

$sql = 'SELECT * FROM `' . $table . '`';
if (!empty($where)) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY `' . $sort . '` ' . $dir . ' LIMIT 200';

$rows = [];
$counts = ['users' => 0, 'travel_plans' => 0, 'service_bookings' => 0];
try {
    foreach ($counts as $key => $value) {
        if (tableExists($pdo, $resources[$key]['table'])) {
            $counts[$key] = (int)$pdo->query('SELECT COUNT(*) FROM `' . $resources[$key]['table'] . '`')->fetchColumn();
        }
    }
    if (empty($errors)) {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
    }
} catch (PDOException $e) {
    $errors[] = 'Could not load dashboard data: ' . $e->getMessage();
}

$userOptions = [];
try {
    $userOptions = $pdo->query('SELECT id, name, email FROM users ORDER BY name ASC')->fetchAll();
} catch (PDOException $e) {
    $userOptions = [];
}

$actionNames = ['Add', 'Edit', 'Update', 'Delete', 'Remove', 'View', 'Search', 'Filter', 'Sort', 'Approve', 'Reject', 'Confirm', 'Cancel', 'Activate', 'Deactivate', 'Save', 'Reset', 'Export', 'Import', 'Download', 'Upload', 'Print', 'Archive', 'Restore', 'Refresh'];
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Nepal Tour and Travel</title>
    <link rel="stylesheet" href="style.css?v=5">
</head>
<body class="admin-page">
    <nav class="navbar admin-navbar">
        <div class="nav-container">
            <a href="index.php" class="logo">
                <img src="img/logo.png?v=2" alt="Logo" class="logo-icon">
                <span class="logo-text">Nepal<span class="logo-subtitle">Admin</span></span>
            </a>
            <ul class="nav-menu">
                <li><a href="index.php" class="nav-link">Website</a></li>
                <li><a href="admin.php" class="nav-link">Dashboard</a></li>
                <li><a href="logout.php" class="nav-link">Logout</a></li>
            </ul>
        </div>
    </nav>

    <main class="admin-shell">
        <section class="admin-hero">
            <div>
                <p class="admin-kicker">Professional Control Panel</p>
                <h1>Admin Dashboard</h1>
                <p>Manage users, travel plans, requests, statuses, archives, imports, and exports from one connected system.</p>
            </div>
            <div class="admin-stats">
                <div><strong><?= (int)$counts['users'] ?></strong><span>Users</span></div>
                <div><strong><?= (int)$counts['travel_plans'] ?></strong><span>Plans</span></div>
                <div><strong><?= (int)$counts['service_bookings'] ?></strong><span>Requests</span></div>
            </div>
        </section>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= h($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= h($success) ?></div>
        <?php endif; ?>

        <div class="admin-action-cloud" aria-label="Supported admin actions">
            <?php foreach ($actionNames as $name): ?>
                <span><?= h($name) ?></span>
            <?php endforeach; ?>
        </div>

        <div class="admin-layout">
            <aside class="admin-sidebar">
                <?php foreach ($resources as $key => $item): ?>
                    <a class="<?= $resource === $key ? 'active' : '' ?>" href="admin.php?resource=<?= h($key) ?>"><?= h($item['title']) ?></a>
                <?php endforeach; ?>
                <a href="admin.php?resource=<?= h($resource) ?>&export=csv">Export / Download CSV</a>
                <a href="admin.php?resource=<?= h($resource) ?>" onclick="window.print();return false;">Print</a>
                <a href="admin.php?resource=<?= h($resource) ?>">Refresh</a>
            </aside>

            <section class="admin-panel">
                <div class="admin-panel-header">
                    <div>
                        <h2><?= h($config['title']) ?></h2>
                        <p>Add, edit, update, view, search, filter, sort, archive, restore, and delete records.</p>
                    </div>
                    <a class="btn-action btn-secondary-action" href="admin.php?resource=<?= h($resource) ?>">Reset</a>
                </div>

                <form class="admin-filters" method="get">
                    <input type="hidden" name="resource" value="<?= h($resource) ?>">
                    <input type="search" name="q" placeholder="Search..." value="<?= h($q) ?>">
                    <select name="status">
                        <option value="">All status</option>
                        <?php $statusOptions = $resource === 'users' ? ['active', 'inactive'] : ['pending', 'approved', 'rejected', 'confirmed', 'cancelled']; ?>
                        <?php foreach ($statusOptions as $option): ?>
                            <option value="<?= h($option) ?>" <?= $status === $option ? 'selected' : '' ?>><?= h(ucfirst($option)) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="sort">
                        <?php foreach ($config['sorts'] as $option): ?>
                            <option value="<?= h($option) ?>" <?= $sort === $option ? 'selected' : '' ?>>Sort: <?= h($option) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="dir">
                        <option value="desc" <?= $dir === 'DESC' ? 'selected' : '' ?>>Descending</option>
                        <option value="asc" <?= $dir === 'ASC' ? 'selected' : '' ?>>Ascending</option>
                    </select>
                    <button type="submit" class="btn-action btn-primary-action">Search / Filter / Sort</button>
                </form>

                <div class="admin-grid">
                    <section class="admin-form-card">
                        <h3><?= $editRow ? 'Edit / Update' : 'Add New' ?> <?= h($config['title']) ?></h3>
                        <?php if ($resource === 'users'): ?>
                            <form method="post" class="travel-plan-form">
                                <input type="hidden" name="mode" value="save_user">
                                <input type="hidden" name="id" value="<?= (int)($editRow['id'] ?? 0) ?>">
                                <label>Name<input type="text" name="name" value="<?= h($editRow['name'] ?? '') ?>" required></label>
                                <label>Email<input type="email" name="email" value="<?= h($editRow['email'] ?? '') ?>" required></label>
                                <label>Password<input type="password" name="password" placeholder="<?= $editRow ? 'Leave blank to keep current password' : 'Minimum 6 characters' ?>"></label>
                                <div class="form-row">
                                    <label>Role<select name="role"><option value="user" <?= (($editRow['role'] ?? '') !== 'admin') ? 'selected' : '' ?>>User</option><option value="admin" <?= (($editRow['role'] ?? '') === 'admin') ? 'selected' : '' ?>>Admin</option></select></label>
                                    <label>Status<select name="admin_status"><option value="active" <?= (($editRow['admin_status'] ?? 'active') === 'active') ? 'selected' : '' ?>>Active</option><option value="inactive" <?= (($editRow['admin_status'] ?? '') === 'inactive') ? 'selected' : '' ?>>Inactive</option></select></label>
                                </div>
                                <div class="form-actions"><button class="btn-action btn-primary-action" type="submit">Save</button><a class="btn-action btn-secondary-action" href="admin.php?resource=users">Cancel / Reset</a></div>
                            </form>
                        <?php elseif ($resource === 'travel_plans'): ?>
                            <form method="post" class="travel-plan-form">
                                <input type="hidden" name="mode" value="save_plan">
                                <input type="hidden" name="id" value="<?= (int)($editRow['id'] ?? 0) ?>">
                                <label>User<select name="user_id" required><option value="">Choose user</option><?php foreach ($userOptions as $option): ?><option value="<?= (int)$option['id'] ?>" <?= (int)($editRow['user_id'] ?? 0) === (int)$option['id'] ? 'selected' : '' ?>><?= h($option['name'] . ' - ' . $option['email']) ?></option><?php endforeach; ?></select></label>
                                <div class="form-row"><label>Title<input type="text" name="title" value="<?= h($editRow['title'] ?? '') ?>" required></label><label>Destination<input type="text" name="destination" value="<?= h($editRow['destination'] ?? '') ?>" required></label></div>
                                <div class="form-row"><label>Start Date<input type="date" name="start_date" value="<?= h($editRow['start_date'] ?? '') ?>" required></label><label>End Date<input type="date" name="end_date" value="<?= h($editRow['end_date'] ?? '') ?>" required></label></div>
                                <div class="form-row"><label>Travelers<input type="number" min="1" name="travelers" value="<?= h($editRow['travelers'] ?? 1) ?>" required></label><label>Status<select name="status"><?php foreach (['pending', 'approved', 'rejected', 'confirmed', 'cancelled'] as $option): ?><option value="<?= h($option) ?>" <?= (($editRow['status'] ?? 'pending') === $option) ? 'selected' : '' ?>><?= h(ucfirst($option)) ?></option><?php endforeach; ?></select></label></div>
                                <label>Notes<textarea name="notes"><?= h($editRow['notes'] ?? '') ?></textarea></label>
                                <div class="form-actions"><button class="btn-action btn-primary-action" type="submit">Save</button><a class="btn-action btn-secondary-action" href="admin.php?resource=travel_plans">Cancel / Reset</a></div>
                            </form>
                        <?php else: ?>
                            <form method="post" class="travel-plan-form">
                                <input type="hidden" name="mode" value="save_booking">
                                <input type="hidden" name="id" value="<?= (int)($editRow['id'] ?? 0) ?>">
                                <label>User<select name="user_id"><option value="">Guest / no account</option><?php foreach ($userOptions as $option): ?><option value="<?= (int)$option['id'] ?>" <?= (int)($editRow['user_id'] ?? 0) === (int)$option['id'] ? 'selected' : '' ?>><?= h($option['name'] . ' - ' . $option['email']) ?></option><?php endforeach; ?></select></label>
                                <div class="form-row"><label>Category<input type="text" name="service_category" value="<?= h($editRow['service_category'] ?? '') ?>" required></label><label>Service Name<input type="text" name="service_name" value="<?= h($editRow['service_name'] ?? '') ?>" required></label></div>
                                <div class="form-row"><label>Full Name<input type="text" name="full_name" value="<?= h($editRow['full_name'] ?? '') ?>" required></label><label>Email<input type="email" name="email" value="<?= h($editRow['email'] ?? '') ?>" required></label></div>
                                <div class="form-row"><label>Phone<input type="text" name="phone" value="<?= h($editRow['phone'] ?? '') ?>" required></label><label>Travel Date<input type="date" name="travel_date" value="<?= h($editRow['travel_date'] ?? '') ?>" required></label></div>
                                <label>Status<select name="status"><?php foreach (['pending', 'approved', 'rejected', 'confirmed', 'cancelled'] as $option): ?><option value="<?= h($option) ?>" <?= (($editRow['status'] ?? 'pending') === $option) ? 'selected' : '' ?>><?= h(ucfirst($option)) ?></option><?php endforeach; ?></select></label>
                                <label>Message<textarea name="message"><?= h($editRow['message'] ?? '') ?></textarea></label>
                                <div class="form-actions"><button class="btn-action btn-primary-action" type="submit">Save</button><a class="btn-action btn-secondary-action" href="admin.php?resource=service_bookings">Cancel / Reset</a></div>
                            </form>
                        <?php endif; ?>

                        <?php if ($resource === 'travel_plans'): ?>
                            <form method="post" enctype="multipart/form-data" class="admin-import-form">
                                <input type="hidden" name="mode" value="import_csv">
                                <label>Import / Upload CSV<input type="file" name="csv_file" accept=".csv" required></label>
                                <button class="btn-action btn-secondary-action" type="submit">Import / Upload</button>
                            </form>
                        <?php endif; ?>
                    </section>

                    <section class="admin-table-card">
                        <h3>View Records</h3>
                        <div class="admin-table-wrap">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <?php foreach ($config['list'] as $column): ?>
                                            <th><?= h($column) ?></th>
                                        <?php endforeach; ?>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($rows)): ?>
                                        <tr><td colspan="<?= count($config['list']) + 1 ?>">No records found.</td></tr>
                                    <?php endif; ?>
                                    <?php foreach ($rows as $row): ?>
                                        <tr>
                                            <?php foreach ($config['list'] as $column): ?>
                                                <td><?= h($row[$column] ?? '') ?></td>
                                            <?php endforeach; ?>
                                            <td class="admin-row-actions">
                                                <a href="admin.php?resource=<?= h($resource) ?>&edit=<?= (int)$row['id'] ?>">Edit</a>
                                                <?php if ($resource === 'users'): ?>
                                                    <a href="admin.php?resource=<?= h($resource) ?>&action=activate&id=<?= (int)$row['id'] ?>">Activate</a>
                                                    <a href="admin.php?resource=<?= h($resource) ?>&action=deactivate&id=<?= (int)$row['id'] ?>">Deactivate</a>
                                                <?php else: ?>
                                                    <a href="admin.php?resource=<?= h($resource) ?>&action=approve&id=<?= (int)$row['id'] ?>">Approve</a>
                                                    <a href="admin.php?resource=<?= h($resource) ?>&action=reject&id=<?= (int)$row['id'] ?>">Reject</a>
                                                    <a href="admin.php?resource=<?= h($resource) ?>&action=confirm&id=<?= (int)$row['id'] ?>">Confirm</a>
                                                    <a href="admin.php?resource=<?= h($resource) ?>&action=cancel&id=<?= (int)$row['id'] ?>">Cancel</a>
                                                <?php endif; ?>
                                                <?php if (!empty($row['archived_at'])): ?>
                                                    <a href="admin.php?resource=<?= h($resource) ?>&action=restore&id=<?= (int)$row['id'] ?>">Restore</a>
                                                <?php else: ?>
                                                    <a href="admin.php?resource=<?= h($resource) ?>&action=archive&id=<?= (int)$row['id'] ?>">Archive</a>
                                                <?php endif; ?>
                                                <a class="danger" href="admin.php?resource=<?= h($resource) ?>&action=delete&id=<?= (int)$row['id'] ?>" onclick="return confirm('Delete this record permanently?');">Delete</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>
            </section>
        </div>
    </main>
</body>
</html>
