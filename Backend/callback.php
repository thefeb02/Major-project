<?php
/**
 * Callback handler for Google OAuth redirect
 */

require_once __DIR__ . '/config.php';

// Check if authorization code is provided
if (isset($_GET['code'])) {
    try {
        // Initialize Google Client
        $client = new Google\Client();
        $client->setClientId(GOOGLE_CLIENT_ID);
        $client->setClientSecret(GOOGLE_CLIENT_SECRET);
        $client->setRedirectUri(GOOGLE_REDIRECT_URI);

        // Exchange code for Access Token
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        
        if (isset($token['error'])) {
            throw new Exception("Error fetching access token: " . $token['error_description']);
        }

        $client->setAccessToken($token['access_token']);

        // Get user profile information from Google
        $googleService = new Google\Service\Oauth2($client);
        $googleUser = $googleService->userinfo->get();

        $googleId = $googleUser->id;
        $email = $googleUser->email;
        $name = $googleUser->name;
        $picture = $googleUser->picture; // URL of Google profile picture

        if (empty($email)) {
            throw new Exception("Could not retrieve email from Google Account.");
        }

        // 1. Search database by google_id or email
        $stmt = $pdo->prepare('SELECT id, name, email, role, is_verified FROM users WHERE google_id = ? OR email = ?');
        $stmt->execute([$googleId, $email]);
        $user = $stmt->fetch();

        if ($user) {
            // User exists
            $userId = $user['id'];
            $userRole = $user['role'];
            $userName = $user['name'];

            // Update user record with Google ID, profile picture, and mark verified if not already
            $updateStmt = $pdo->prepare('UPDATE users SET google_id = ?, profile_pic = ?, is_verified = 1 WHERE id = ?');
            $updateStmt->execute([$googleId, $picture, $userId]);
        } else {
            // User does not exist, automatically register them
            $userRole = 'user';
            $userName = $name;
            
            // Generate a random secure password hash for OAuth users
            $randomPassword = bin2hex(random_bytes(16));
            $passwordHash = password_hash($randomPassword, PASSWORD_DEFAULT);

            $insertStmt = $pdo->prepare('INSERT INTO users (name, email, google_id, password, profile_pic, role, is_verified, created_at) VALUES (?, ?, ?, ?, ?, ?, 1, NOW())');
            $insertStmt->execute([$name, $email, $googleId, $passwordHash, $picture, $userRole]);
            $userId = $pdo->lastInsertId();
        }

        // 2. Establish secure session
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => $userId,
            'name' => $userName,
            'email' => $email,
            'role' => $userRole,
            'profile_pic' => $picture
        ];

        // 3. Redirect to frontend/index.php
        redirect('../frontend/index.php');

    } catch (Exception $e) {
        $_SESSION['flash_message'] = "Google login failed: " . htmlspecialchars($e->getMessage());
        redirect('../frontend/login.php');
    }
} else {
    redirect('../frontend/login.php');
}
