<?php
/**
 * Redirects user to Google OAuth 2.0 Authorization Server
 */

require_once __DIR__ . '/config.php';

$redirectTarget = trim($_GET['redirect'] ?? '');
$allowedRedirects = ['index.php', 'packages.php', 'profile.php', 'about.php', 'travel_plan.php'];
if ($redirectTarget !== '' && !in_array($redirectTarget, $allowedRedirects, true)) {
    $redirectTarget = '';
}

if (GOOGLE_CLIENT_ID === 'your-google-client-id.apps.googleusercontent.com' || GOOGLE_CLIENT_SECRET === 'your-google-client-secret') {
    $_SESSION['flash_message'] = 'Google OAuth is not configured yet. Please set real GOOGLE_CLIENT_ID and GOOGLE_CLIENT_SECRET values in your environment.';
    header('Location: ../frontend/login.php');
    exit;
}

// Initialize Google Client
$client = new Google\Client();
$client->setClientId(GOOGLE_CLIENT_ID);
$client->setClientSecret(GOOGLE_CLIENT_SECRET);
$client->setRedirectUri(GOOGLE_REDIRECT_URI);
$client->addScope("email");
$client->addScope("profile");

// Generate auth URL
$authUrl = $client->createAuthUrl();
if ($redirectTarget !== '') {
    $authUrl .= '&state=' . urlencode($redirectTarget);
}

// Redirect to Google's OAuth Server
header('Location: ' . $authUrl);
exit;
