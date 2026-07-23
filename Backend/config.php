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

// Autoload Composer dependencies
require_once __DIR__ . '/../vendor/autoload.php';

// Reuse existing database connection
require_once __DIR__ . '/database.php';

// Google Client Configuration Constants
// IMPORTANT: Please fill in your Google Client ID and Secret obtained from Google Cloud Console
define('GOOGLE_CLIENT_ID', '475309162996-261l3i771e87b4b57144p2c8f31074i0.apps.googleusercontent.com'); // Client ID
define('GOOGLE_CLIENT_SECRET', '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'); // Client Secret

// Dynamically construct Redirect URI
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
define('GOOGLE_REDIRECT_URI', $protocol . '://' . $host . '/Major-project/Backend/callback.php');
