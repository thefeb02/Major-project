<?php
require_once __DIR__ . '/../Backend/database.php';
require_once __DIR__ . '/../Backend/email_verification.php';
require_once __DIR__ . '/../Backend/config.php';

function resolveSafeRedirectTarget($value)
{
    $candidate = trim((string) ($value ?? ''));
    if ($candidate === '') {
        return 'index.php';
    }

    $candidate = str_replace('\\', '/', $candidate);
    if ($candidate === '' || strpos($candidate, '://') !== false || strpos($candidate, '/') === 0 || strpos($candidate, '..') !== false) {
        return 'index.php';
    }

    return $candidate;
}

if (isLoggedIn()) {
    $redirectPath = resolveSafeRedirectTarget($_GET['redirect'] ?? '');
    redirect($redirectPath);
}

$errors = [];
$email = '';
$redirectPath = resolveSafeRedirectTarget($_GET['redirect'] ?? '');
$loginMessage = trim((string) ($_GET['message'] ?? ''));
$googleLoginAvailable = googleOAuthIsConfigured();
if ($loginMessage !== '') {
    $errors[] = $loginMessage;
}

// show flash message (if any)
if (!empty($_SESSION['flash_message'])) {
    $errors[] = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'login') {
        $redirectPath = resolveSafeRedirectTarget($_POST['redirect'] ?? $_GET['redirect'] ?? '');
        // Store and look up email addresses consistently, regardless of the
        // capitalization a visitor uses when signing in.
        $email = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';

        $emailErrorMsg = '';
        if (!validateEmailProfessional($email, $emailErrorMsg)) {
            $errors[] = $emailErrorMsg;
        }
        if ($password === '') {
            $errors[] = 'Password is required.';
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare('SELECT id, name, email, password, role, is_verified FROM users WHERE email = ? LIMIT 1');
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                if (!(bool) $user['is_verified']) {
                    $errors[] = 'Please verify your email address before logging in.';
                } else {
                    $role = strtolower($user['role'] ?? 'user') === 'admin' ? 'admin' : 'user';

                    session_regenerate_id(true);
                    $_SESSION['user'] = [
                        'id' => $user['id'],
                        'name' => $user['name'],
                        'email' => $user['email'],
                        'role' => $role,
                    ];
                    redirect($role === 'admin' ? '../Backend/admin.php' : $redirectPath);
                }
            } else {
                $errors[] = 'Invalid email or password.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Nepal tours and Travel</title>
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
            <ul class="nav-menu">
                <li><a href="index.php#places" class="nav-link">Places</a></li>
            </ul>
        </div>
    </nav>

    <div class="auth-layout">
        <div class="auth-image-panel">
            <img src="../img/2.jpeg" alt="Nepal Tour and Travel">
            <div class="auth-image-copy">
                <div class="auth-image-brand">Nepal Tours</div>
                <p>Travel is the only purchase that enriches you in ways beyond material wealth.</p>
            </div>
        </div>

        <div class="auth-card auth-card-right">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="auth-form-panel" id="login-form">
                <div class="auth-head">
                    <h1>Welcome</h1>
                    <p>Login with your email</p>
                </div>

                <form action="login.php" method="POST" class="auth-form">
                    <input type="hidden" name="action" value="login">
                    <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirectPath) ?>">
                    <div class="input-group">
                        <label>Email Id</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-envelope"></i>
                            <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" placeholder="thisisab@mail.com" maxlength="190" autocomplete="email" required>
                        </div>
                    </div>

                    <div class="input-group">
                        <label>Password</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-lock"></i>
                            <input type="password" name="password" placeholder="•••••••••••••" autocomplete="current-password" required>
                        </div>
                    </div>

                   

                    <button type="submit" class="auth-submit">LOGIN</button>
                </form>

                <p class="auth-footer">Don't have an account? <a href="#" class="auth-toggle-link" onclick="toggleForm('signup'); return false;">Register Now</a></p>
            </div>

            <div class="auth-form-panel" id="signup-form" hidden>
                <div class="auth-head">
                    <h1>Register</h1>
                    <p>Create a new account</p>
                </div>

                <form action="signup.php" method="POST" class="auth-form">
                    <div class="input-group">
                        <label>Full Name</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-user"></i>
                            <input type="text" name="name" placeholder="Your name" required>
                        </div>
                    </div>

                    <div class="input-group">
                        <label>Email Id</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-envelope"></i>
                            <input type="email" name="email" placeholder="your@email.com" required>
                        </div>
                    </div>

                    <div class="input-group">
                        <label>Password</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-lock"></i>
                            <input type="password" name="password" placeholder="•••••••••••••" required>
                        </div>
                    </div>

                    <div class="input-group">
                        <label>Confirm Password</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-lock"></i>
                            <input type="password" name="confirm_password" placeholder="•••••••••••••" required>
                        </div>
                    </div>

                    <button type="submit" class="auth-submit">REGISTER</button>
                </form>

                <?php if ($googleLoginAvailable): ?>
                    <p class="auth-footer">
                        <a href="../Backend/google_login.php?redirect=<?= urlencode($redirectPath) ?>" class="auth-toggle-link">Register with Google</a>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function toggleForm(type) {
            const loginForm = document.getElementById('login-form');
            const signupForm = document.getElementById('signup-form');

            if (type === 'signup') {
                loginForm.hidden = true;
                signupForm.hidden = false;
            } else {
                loginForm.hidden = false;
                signupForm.hidden = true;
            }
        }
    </script>
</body>
</html>
