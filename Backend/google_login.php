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

if (!googleOAuthIsConfigured()) {
    header('Location: ../frontend/login.php');
    exit;
}

// Bind the callback to this browser session. The requested page is stored
// server-side so it cannot be replaced by a crafted OAuth state value.
$state = bin2hex(random_bytes(32));
$_SESSION['google_oauth_state'] = $state;
$_SESSION['google_oauth_redirect'] = $redirectTarget;

$authUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
    'client_id' => GOOGLE_CLIENT_ID,
    'redirect_uri' => GOOGLE_REDIRECT_URI,
    'response_type' => 'code',
    'scope' => 'openid email profile',
    'state' => $state,
    'prompt' => 'select_account',
], '', '&', PHP_QUERY_RFC3986);

// Redirect to Google's OAuth Server
header('Location: ' . $authUrl);
exit;
