<?php
require_once __DIR__ . '/../Backend/database.php';
require_once __DIR__ . '/../Backend/email_verification.php';

$redirectTarget = trim($_GET['redirect'] ?? '');
$allowedRedirects = ['index.php', 'packages.php', 'profile.php', 'about.php', 'travel_plan.php'];

if (isLoggedIn()) {
    if ($redirectTarget !== '' && in_array($redirectTarget, $allowedRedirects, true)) {
        redirect($redirectTarget);
    }
    redirect('index.php');
}

$errors = [];
$email = '';

// show flash message (if any)
if (!empty($_SESSION['flash_message'])) {
    $errors[] = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
}

$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($requestMethod === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $emailErrorMsg = '';
    if (!validateEmailProfessional($email, $emailErrorMsg)) {
        $errors[] = $emailErrorMsg;
    }
    if ($password === '') {
        $errors[] = 'Password is required.';
    }

    if (empty($errors)) {
        // Older installations do not have the email-verification columns yet.
        // Fall back to the original user fields so they can still log in.
        try {
            $stmt = $pdo->prepare('SELECT id, name, email, password, is_verified FROM users WHERE email = ?');
            $stmt->execute([$email]);
            $user = $stmt->fetch();
        } catch (PDOException $e) {
            if ($e->getCode() !== '42S22') {
                throw $e;
            }

            $stmt = $pdo->prepare('SELECT id, name, email, password FROM users WHERE email = ?');
            $stmt->execute([$email]);
            $user = $stmt->fetch();
        }
        // Only the designated administrator may use the direct dashboard login.
        // Every other email must match a registered user record and its password.
        $isDirectAdminLogin = strtolower($email) === 'admin@nepaltravel.com'
            && hash_equals('Admin@123', $password);

        if (($user && password_verify($password, $user['password'])) || $isDirectAdminLogin) {
            if (!$isDirectAdminLogin && isset($user['is_verified']) && (int)$user['is_verified'] === 0) {
                $errors[] = 'Please verify your email address before logging in.';
            } else {
                $role = 'user';
                if (strtolower($user['email'] ?? '') === 'admin@nepaltravel.com') {
                    $role = 'admin';
                }

                if ($isDirectAdminLogin) {
                    $role = 'admin';
                    $user = $user ?: [
                        'id' => 0,
                        'name' => 'Admin',
                        'email' => $email,
                        'role' => 'admin',
                    ];
                }

                session_regenerate_id(true);
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'role' => $role,
                ];
                $redirectTarget = trim($_POST['redirect'] ?? ($_GET['redirect'] ?? ''));
                $allowedRedirects = ['index.php', 'packages.php', 'profile.php', 'about.php', 'travel_plan.php'];
                if ($redirectTarget !== '' && in_array($redirectTarget, $allowedRedirects, true)) {
                    redirect($redirectTarget);
                }
                redirect($role === 'admin' ? '../Backend/admin.php' : 'index.php');
            }
        }

        $errors[] = 'Invalid email or password.';
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Nepal Tour and Travel</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <h1>Welcome Back</h1>
            <p class="auth-subtitle">Log in to manage your travel planning securely.</p>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="login.php" method="post" class="auth-form">
                <?php $redirectValue = trim($_GET['redirect'] ?? ''); ?>
                <?php if ($redirectValue !== ''): ?>
                    <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirectValue) ?>">
                <?php endif; ?>
                <label>
                    <span>Email</span>
                    <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
                </label>
                <label>
                    <span>Password</span>
                    <input type="password" name="password" required>
                </label>
                <button type="submit" class="auth-submit">Log In</button>
            </form>

            <div class="auth-divider"><span>OR</span></div>
            <a href="../Backend/google_login.php?redirect=<?= urlencode(trim($_GET['redirect'] ?? '')) ?>" class="google-btn">
                <img src="https://developers.google.com/static/identity/images/g-logo.png" alt="Google Logo" style="width: 18px; height: 18px; margin-right: 10px;">
                Continue with Google
            </a>

            <p class="auth-footer">New to Nepal Tour? <a href="signup.php">Create an account</a>.</p>
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
