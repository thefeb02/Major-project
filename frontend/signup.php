<?php
require_once __DIR__ . '/../Backend/database.php';
require_once __DIR__ . '/../Backend/email_verification.php';
require_once __DIR__ . '/../Backend/config.php';

$googleLoginAvailable = googleOAuthIsConfigured();

if (isLoggedIn()) {
    redirect('index.php');
}

$errors = [];
$success = '';
$name = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($name === '') {
        $errors[] = 'Name is required.';
    }
    $emailErrorMsg = '';
    if (!validateEmailProfessional($email, $emailErrorMsg)) {
        $errors[] = $emailErrorMsg;
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
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $errors[] = 'Email is already registered. Please log in instead.';
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $verificationToken = bin2hex(random_bytes(32));

            $stmt = $pdo->prepare('INSERT INTO users (name, email, password, is_verified, verification_token, created_at) VALUES (?, ?, ?, 0, ?, NOW())');
            $stmt->execute([$name, $email, $passwordHash, $verificationToken]);
            sendVerificationEmailLocal($email, $verificationToken);

            $success = 'Registration successful. Please check your email and verify your account before logging in.';
            $name = '';
            $email = '';
        }
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up | Nepal Tour and Travel</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">
    <div class="auth-layout">
        <div class="auth-image-panel">
            <img src="../img/2.jpeg" alt="Travelista Tours">
            <div class="auth-image-copy">
                <div class="auth-image-brand">Travelista Tours</div>
                <p>Travel is the only purchase that enriches you in ways beyond material wealth.</p>
            </div>
        </div>

        <div class="auth-card auth-card-right">
            <div class="auth-head">
                <h1>Register</h1>
                <p>Create your account</p>
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

            <form action="signup.php" method="post" class="auth-form">
                <div class="input-group">
                    <label>Name</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-user"></i>
                        <input type="text" name="name" value="<?= htmlspecialchars($name) ?>" minlength="2" maxlength="120" autocomplete="name" required>
                    </div>
                </div>
                <div class="input-group">
                    <label>Email Id</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-envelope"></i>
                        <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" maxlength="190" autocomplete="email" required>
                    </div>
                </div>
                <div class="input-group">
                    <label>Password</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" name="password" minlength="8" autocomplete="new-password" required>
                    </div>
                </div>
                <div class="input-group">
                    <label>Confirm Password</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" name="confirm_password" minlength="8" autocomplete="new-password" required>
                    </div>
                </div>
                <button type="submit" class="auth-submit">SIGN UP</button>
            </form>

            <?php if ($googleLoginAvailable): ?>
                <p class="auth-footer"><a href="../Backend/google_login.php" class="auth-toggle-link">Register with Google</a></p>
            <?php endif; ?>

        </div>
    </div>
</body>
</html>
