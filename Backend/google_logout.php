<?php
/**
 * Sign out user from session and Google OAuth context
 */

require_once __DIR__ . '/config.php';

// Clear session variables
$_SESSION = [];

// Destroy session cookies
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy session
session_destroy();

// Redirect to login page
redirect('../frontend/login.php');
