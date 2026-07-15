<?php
require_once __DIR__ . '/config/database.php';

if (isLoggedIn()) {
    redirect('index.php');
}

$errors = [];
$email = '';

// show flash message (if any)
if (!empty($_SESSION['flash_message'])) {
    $errors[] = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid email address is required.';
    }
    if ($password === '') {
        $errors[] = 'Password is required.';
    }

    if (empty($errors)) {
<<<<<<< HEAD
        $stmt = $pdo->prepare('SELECT id, name, email, password FROM users WHERE email = ?');
=======
        $stmt = $pdo->prepare('SELECT id, name, email, password, role FROM users WHERE email = ?');
>>>>>>> 027836d59ed73f5f913fac73ae5c60eee70b9549
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $role = 'user';
            if (isset($user['role'])) {
                $role = $user['role'];
            } elseif (strtolower($user['email'] ?? '') === 'admin@nepaltravel.com') {
                $role = 'admin';
            }

            session_regenerate_id(true);
            $_SESSION['user'] = [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
<<<<<<< HEAD
                'role' => $role,
=======
                'role' => $user['role'] ?? 'user',
>>>>>>> 027836d59ed73f5f913fac73ae5c60eee70b9549
            ];
            redirect('index.php');
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

            <p class="auth-footer">New to Nepal Tour? <a href="signup.php">Create an account</a>.</p>
        </div>
    </div>
</body>
</html>
