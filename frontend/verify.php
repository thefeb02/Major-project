<?php
require_once __DIR__ . '/../Backend/database.php';

$token = $_GET['token'] ?? '';

if (empty($token)) {
    $_SESSION['flash_message'] = 'Invalid or missing verification token.';
    redirect('login.php');
}

// Search for user with this token
$stmt = $pdo->prepare('SELECT id, name FROM users WHERE verification_token = ? AND is_verified = 0');
$stmt->execute([$token]);
$user = $stmt->fetch();

if ($user) {
    // Activate the user
    $updateStmt = $pdo->prepare('UPDATE users SET is_verified = 1, verification_token = NULL WHERE id = ?');
    $updateStmt->execute([$user['id']]);

    $_SESSION['flash_message'] = 'Your account has been successfully verified! You can now log in.';
} else {
    $_SESSION['flash_message'] = 'The verification link is invalid, expired, or the account is already verified.';
}

redirect('login.php');
