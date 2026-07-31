<?php
require_once __DIR__ . '/../Backend/database.php';
require_once __DIR__ . '/../Backend/email_verification.php';

if (isLoggedIn()) {
    redirect('index.php');
}

$errors = [];
$success = '';
$name = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
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
            $token = bin2hex(random_bytes(32));
            
            $stmt = $pdo->prepare('INSERT INTO users (name, email, password, verification_token, is_verified, created_at) VALUES (?, ?, ?, ?, 0, NOW())');
            $stmt->execute([$name, $email, $passwordHash, $token]);

            sendVerificationEmailLocal($email, $token);

            $success = 'Registration successful. A verification link has been sent to your email. (Developers: Check Backend/verification_emails.log)';
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
    <div class="auth-container">
        <div class="auth-card">
            <h1>Create an Account</h1>
            <p class="auth-subtitle">Securely sign up and start exploring Nepal travel packages.</p>

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
                <label>
                    <span>Name</span>
                    <input type="text" name="name" value="<?= htmlspecialchars($name) ?>" required>
                </label>
                <label>
                    <span>Email</span>
                    <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
                </label>
                <label>
                    <span>Password</span>
                    <input type="password" name="password" required>
                </label>
                <label>
                    <span>Confirm Password</span>
                    <input type="password" name="confirm_password" required>
                </label>
                <button type="submit" class="auth-submit">Sign Up</button>
            </form>

            <p class="auth-footer">Already have an account? <a href="login.php">Log in here</a>.</p>
        </div>
    </div>
    <script>
    document.querySelector('.auth-form').addEventListener('submit', function(e) {
        const emailInput = document.querySelector('input[name="email"]');
        const email = emailInput.value.trim();
        let errorMsg = '';

        if (email === '') {
            errorMsg = 'Please enter a valid email address.';
        } else if (email.indexOf(' ') !== -1) {
            errorMsg = 'Please enter a valid email address.';
        } else {
            const parts = email.split('@');
            if (parts.length !== 2) {
                errorMsg = 'Please enter a valid email address.';
            } else {
                const local = parts[0];
                const domain = parts[1];
                if (local === '' || domain === '') {
                    errorMsg = 'Please enter a valid email address.';
                } else if (email.includes('..')) {
                    errorMsg = 'Please enter a valid email address.';
                } else if (local.startsWith('.') || local.endsWith('.')) {
                    errorMsg = 'Please enter a valid email address.';
                } else if (domain.startsWith('.') || domain.endsWith('.') || domain.startsWith('-') || domain.endsWith('-')) {
                    errorMsg = 'Please enter a valid email address.';
                } else {
                    const domainParts = domain.split('.');
                    if (domainParts.length < 2) {
                        errorMsg = 'Please enter a valid email address.';
                    } else {
                        const tld = domainParts[domainParts.length - 1];
                        const invalidTlds = ['coom', 'comm', 'commmmm', 'coo', 'cm', 'om'];
                        if (invalidTlds.includes(tld.toLowerCase())) {
                            errorMsg = 'Please enter a valid email address.';
                        } else if (tld.length < 2 || tld.length > 6 || !/^[a-zA-Z]{2,6}$/.test(tld)) {
                            errorMsg = 'Please enter a valid email address.';
                        }
                    }
                }
            }
        }

        if (errorMsg !== '') {
            e.preventDefault();
            let alertBox = document.querySelector('.alert-error');
            if (!alertBox) {
                alertBox = document.createElement('div');
                alertBox.className = 'alert alert-error';
                const card = document.querySelector('.auth-card');
                const subtitle = document.querySelector('.auth-subtitle');
                card.insertBefore(alertBox, subtitle.nextSibling);
            }
            alertBox.innerHTML = '<ul><li>' + errorMsg + '</li></ul>';
            window.scrollTo(0, 0);
        }
    });
    </script>
</body>
</html>
