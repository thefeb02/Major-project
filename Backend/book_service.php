<?php
require_once __DIR__ . '/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../frontend/index.php');
}

$category = trim($_POST['service_category'] ?? 'Tour');
$serviceName = trim($_POST['service_name'] ?? '');
$fullName = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$travelDate = $_POST['travel_date'] ?? '';
$travelers = max(1, min(50, (int)($_POST['travelers'] ?? 1)));
$message = trim($_POST['message'] ?? '');

if ($category === '' || $serviceName === '' || $fullName === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $phone === '' || !$travelDate || $travelDate < date('Y-m-d')) {
    http_response_code(422);
    exit('Please return to the booking form and complete all required fields with a future travel date.');
}

$message = trim('Travelers: ' . $travelers . ($message !== '' ? "\n" . $message : ''));
$user = getCurrentUser();

try {
    $stmt = $pdo->prepare('INSERT INTO service_bookings (user_id, service_category, service_name, full_name, email, phone, travel_date, message) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([$user['id'] ?? null, $category, $serviceName, $fullName, $email, $phone, $travelDate, $message]);
} catch (PDOException $e) {
    http_response_code(500);
    exit('We could not save your booking request. Please try again later.');
}

$referer = $_SERVER['HTTP_REFERER'] ?? '../frontend/index.php';
$separator = str_contains($referer, '?') ? '&' : '?';
header('Location: ' . $referer . $separator . 'booking=success');
exit;
