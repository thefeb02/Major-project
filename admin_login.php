<?php
require_once __DIR__ . '/config/database.php';

if (isAdmin()) {
    redirect('admin.php');
}

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid admin email address is required.';
    }
    if ($password === '') {
        $errors[] = 'Password is required.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare('SELECT id, name, email, password, role FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password']) && (($user['role'] ?? '') === 'admin' || strtolower($user['email']) === 'admin@nepaltravel.com')) {
            session_regenerate_id(true);
            $_SESSION['user'] = [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role'] ?? 'admin',
            ];
            redirect('admin.php');
        }

        $errors[] = 'Invalid admin email or password.';
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Nepal Tour and Travel</title>
    <link rel="stylesheet" href="style.css?v=5">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card admin-auth-card">
            <h1>Admin Login</h1>
            <p class="auth-subtitle">Sign in to manage website data, users, plans, and requests.</p>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="admin_login.php" method="post" class="auth-form">
                <label>
                    <span>Admin Email</span>
                    <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
                </label>
                <label>
                    <span>Password</span>
                    <input type="password" name="password" required>
                </label>
                <button type="submit" class="auth-submit">Start Dashboard</button>
            </form>

            <p class="auth-footer"><a href="index.php">Back to website</a></p>
        </div>
    </div>
</body>
</html>
