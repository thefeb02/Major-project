<?php
require_once __DIR__ . '/../Backend/database.php';
require_once __DIR__ . '/../Backend/email_verification.php';

if (isLoggedIn()) {
    redirect('index.php');
}

$errors = [];
$success = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $emailErrorMsg = '';
    if (!validateEmailProfessional($email, $emailErrorMsg)) {
        $errors[] = $emailErrorMsg;
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? AND archived_at IS NULL');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));
            $insertStmt = $pdo->prepare('INSERT INTO password_reset_tokens (user_id, token, expires_at) VALUES (?, ?, ?)');
            $insertStmt->execute([$user['id'], $token, $expiresAt]);
            sendPasswordResetEmailLocal($email, $token);
        }

        $success = 'If an account exists for this email, we have sent reset instructions.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | Nepal Tour and Travel</title>
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
                <div class="auth-image-brand">Reset Access</div>
                <p>We will send a secure link so you can choose a new password.</p>
            </div>
        </div>

        <div class="auth-card auth-card-right">
            <div class="auth-head">
                <h1>Forgot Password</h1>
                <p>Enter your email to receive a reset link</p>
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

            <form action="forgot_password.php" method="POST" class="auth-form">
                <div class="input-group">
                    <label>Email address</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-envelope"></i>
                        <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" placeholder="your@email.com" required>
                    </div>
                </div>

                <button type="submit" class="auth-submit">SEND RESET LINK</button>
            </form>

            <p class="auth-footer"><a href="login.php">Back to login</a></p>
        </div>
    </div>
</body>
</html>
