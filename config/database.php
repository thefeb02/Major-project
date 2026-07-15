<?php
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
session_name('nepal_tour_session');
session_start();

// Database credentials
define('DB_HOST', 'localhost');
define('DB_PORTS', [3307, 3306]);
define('DB_NAME', 'tour_travel_db');
define('DB_USER', 'tour_user');
define('DB_PASS', 'tour_pass_2026');

$pdo = null;
$lastDatabaseError = null;

foreach (DB_PORTS as $port) {
    try {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';port=' . $port . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
        break;
    } catch (PDOException $e) {
        $lastDatabaseError = $e;
    }
}

if (!$pdo) {
    http_response_code(500);
    echo 'Database connection failed. Tried MySQL ports: ' . htmlspecialchars(implode(', ', DB_PORTS));
    if ($lastDatabaseError) {
        echo '<br>Error: ' . htmlspecialchars($lastDatabaseError->getMessage());
    }
    exit;
}

function redirect($url)
{
    header('Location: ' . $url);
    exit;
}

function isLoggedIn()
{
    return !empty($_SESSION['user']);
}

function getCurrentUser()
{
    return $_SESSION['user'] ?? null;
}

function isAdmin()
{
    $user = getCurrentUser();
    if (!$user) {
        return false;
    }

    if (!empty($user['role']) && strtolower($user['role']) === 'admin') {
        return true;
    }

    return strtolower($user['email'] ?? '') === 'admin@nepaltravel.com';
}
