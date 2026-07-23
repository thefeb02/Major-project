<?php
/**
 * Redirects user to Google OAuth 2.0 Authorization Server
 */

require_once __DIR__ . '/config.php';

// Initialize Google Client
$client = new Google\Client();
$client->setClientId(GOOGLE_CLIENT_ID);
$client->setClientSecret(GOOGLE_CLIENT_SECRET);
$client->setRedirectUri(GOOGLE_REDIRECT_URI);
$client->addScope("email");
$client->addScope("profile");

// Generate auth URL
$authUrl = $client->createAuthUrl();

// Redirect to Google's OAuth Server
header('Location: ' . $authUrl);
exit;
