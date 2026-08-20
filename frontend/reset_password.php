<?php
require_once __DIR__ . '/../Backend/database.php';

if (isLoggedIn()) {
    redirect('index.php');
}

$errors = [];
$success = '';
$token = trim($_GET['token'] ?? '');
$tokenRecord = null;

if ($token !== '') {
    $stmt = $pdo->prepare('SELECT ptr.id, ptr.user_id, ptr.expires_at, ptr.used_at, u.email FROM password_reset_tokens ptr JOIN users u ON u.id = ptr.user_id WHERE ptr.token = ? LIMIT 1');
    $stmt->execute([$token]);
    $tokenRecord = $stmt->fetch();

    if (!$tokenRecord || $tokenRecord['used_at'] !== null || strtotime($tokenRecord['expires_at']) < time()) {
        $errors[] = 'This reset link is invalid or has expired.';
        $tokenRecord = null;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = trim($_POST['token'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($token === '') {
        $errors[] = 'Missing password reset token.';
    }

    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long.';
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Password must contain at least one uppercase letter.';
    }
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'Password must contain at least one lowercase letter.';
    }
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Password must contain at least one number.';
    }
    if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
        $errors[] = 'Password must contain at least one special character.';
    }
    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare('SELECT id, user_id, expires_at, used_at FROM password_reset_tokens WHERE token = ? LIMIT 1');
        $stmt->execute([$token]);
        $record = $stmt->fetch();

        if (!$record || $record['used_at'] !== null || strtotime($record['expires_at']) < time()) {
            $errors[] = 'This reset link is invalid or has expired.';
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $pdo->prepare('UPDATE users SET password = ? WHERE id = ?')->execute([$passwordHash, $record['user_id']]);
            $pdo->prepare('UPDATE password_reset_tokens SET used_at = NOW() WHERE id = ?')->execute([$record['id']]);
            $_SESSION['flash_message'] = 'Password updated successfully. Please sign in with your new password.';
            redirect('login.php');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | Nepal Tour and Travel</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="logo">
                <img src="../img/logo.png?v=2" alt="Logo" class="logo-icon">
            </a>
        </div>
    </nav>

    <div class="auth-layout">
        <div class="auth-image-panel">
            <img src="../img/2.jpeg" alt="Nepal Tour and Travel">
            <div class="auth-image-copy">
                <div class="auth-image-brand">Secure Reset</div>
                <p>Choose a strong password that you can remember.</p>
            </div>
        </div>

        <div class="auth-card auth-card-right">
            <div class="auth-head">
                <h1>Create New Password</h1>
                <p>Set a new password for your account</p>
            </div>

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

            <?php if ($tokenRecord): ?>
                <form action="reset_password.php" method="POST" class="auth-form">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                    <div class="input-group">
                        <label>New Password</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-lock"></i>
                            <input type="password" name="password" required>
                        </div>
                    </div>
                    <div class="input-group">
                        <label>Confirm Password</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-lock"></i>
                            <input type="password" name="confirm_password" required>
                        </div>
                    </div>
                    <button type="submit" class="auth-submit">UPDATE PASSWORD</button>
                </form>
            <?php else: ?>
                <p class="auth-footer"><a href="forgot_password.php">Request a new reset link</a></p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
