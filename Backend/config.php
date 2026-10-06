<?php
/**
 * Google OAuth 2.0 Configuration
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    session_name('nepal_tour_session');
    session_start();
}

// Reuse existing database connection
require_once __DIR__ . '/database.php';

// Google Client Configuration Constants
// Set these values in your environment or in your local web server config.
// Example on Windows PowerShell:
// $env:GOOGLE_CLIENT_ID="your-client-id.apps.googleusercontent.com"
// $env:GOOGLE_CLIENT_SECRET="your-client-secret"
// $env:GOOGLE_REDIRECT_URI="http://localhost/tour%20and%20travelling/Major-project/Backend/callback.php"

function buildGoogleRedirectUri(): string
{
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptPath = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? $_SERVER['PHP_SELF'] ?? '/tour and travelling/Major-project/Backend/google_login.php');
    $scriptDir = rtrim(dirname($scriptPath), '/');

    if (preg_match('#/Backend$#', $scriptDir) === 1) {
        return $protocol . '://' . $host . $scriptDir . '/callback.php';
    }

    return $protocol . '://' . $host . '/tour and travelling/Major-project/Backend/callback.php';
}

$clientId = getenv('GOOGLE_CLIENT_ID') ?: 'your-google-client-id.apps.googleusercontent.com';
$clientSecret = getenv('GOOGLE_CLIENT_SECRET') ?: 'your-google-client-secret';

define('GOOGLE_CLIENT_ID', $clientId);
define('GOOGLE_CLIENT_SECRET', $clientSecret);
define('GOOGLE_REDIRECT_URI', getenv('GOOGLE_REDIRECT_URI') ?: buildGoogleRedirectUri());

function googleOAuthIsConfigured(): bool
{
    return GOOGLE_CLIENT_ID !== ''
        && GOOGLE_CLIENT_SECRET !== ''
        && stripos(GOOGLE_CLIENT_ID, 'your-google-client') === false
        && stripos(GOOGLE_CLIENT_SECRET, 'your-google-client') === false;
}
