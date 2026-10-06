<?php
/**
 * Callback handler for Google OAuth redirect
 */

require_once __DIR__ . '/config.php';

function googleOAuthRequest(string $url, array $fields = []): array
{
    $curl = curl_init($url);
    $options = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_HTTPHEADER => ['Accept: application/json'],
    ];

    if ($fields) {
        $options[CURLOPT_POST] = true;
        $options[CURLOPT_POSTFIELDS] = http_build_query($fields, '', '&', PHP_QUERY_RFC3986);
        $options[CURLOPT_HTTPHEADER] = ['Accept: application/json', 'Content-Type: application/x-www-form-urlencoded'];
    }

    curl_setopt_array($curl, $options);
    $response = curl_exec($curl);
    $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $error = curl_error($curl);
    curl_close($curl);

    if ($response === false || $status < 200 || $status >= 300) {
        throw new RuntimeException('Google could not complete the sign-in request' . ($error ? ': ' . $error : '.') );
    }

    $data = json_decode($response, true);
    if (!is_array($data)) {
        throw new RuntimeException('Google returned an invalid sign-in response.');
    }

    return $data;
}

if (!googleOAuthIsConfigured()) {
    $_SESSION['flash_message'] = 'Google login is not configured yet.';
    redirect('../frontend/login.php');
}

$state = (string) ($_GET['state'] ?? '');
$expectedState = (string) ($_SESSION['google_oauth_state'] ?? '');
$redirectTarget = (string) ($_SESSION['google_oauth_redirect'] ?? '');
unset($_SESSION['google_oauth_state'], $_SESSION['google_oauth_redirect']);

if ($expectedState === '' || !hash_equals($expectedState, $state)) {
    $_SESSION['flash_message'] = 'Google login request expired or was invalid. Please try again.';
    redirect('../frontend/login.php');
}

// Check if authorization code is provided
if (isset($_GET['code'])) {
    try {
        $token = googleOAuthRequest('https://oauth2.googleapis.com/token', [
            'code' => (string) $_GET['code'],
            'client_id' => GOOGLE_CLIENT_ID,
            'client_secret' => GOOGLE_CLIENT_SECRET,
            'redirect_uri' => GOOGLE_REDIRECT_URI,
            'grant_type' => 'authorization_code',
        ]);

        if (empty($token['access_token'])) {
            throw new RuntimeException('Google did not provide an access token.');
        }

        $googleUser = googleOAuthRequest('https://www.googleapis.com/oauth2/v3/userinfo?access_token=' . rawurlencode($token['access_token']));
        $googleId = (string) ($googleUser['sub'] ?? '');
        $email = strtolower(trim((string) ($googleUser['email'] ?? '')));
        $name = trim((string) ($googleUser['name'] ?? ''));
        $picture = (string) ($googleUser['picture'] ?? '');

        if ($googleId === '' || $email === '' || empty($googleUser['email_verified'])) {
            throw new RuntimeException('Google did not return a verified email address.');
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

        // 3. Redirect to the requested frontend page
        $destination = $redirectTarget !== '' ? '../frontend/' . $redirectTarget : '../frontend/index.php';
        redirect($destination);

    } catch (Throwable $e) {
        $_SESSION['flash_message'] = 'Google login failed. Please try again.';
        redirect('../frontend/login.php');
    }
} else {
    redirect('../frontend/login.php');
}
