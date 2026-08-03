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
$paymentMethodInput = strtolower(trim($_POST['payment_method'] ?? 'pay_later'));
$paymentReference = trim($_POST['payment_reference'] ?? '');
$amount = max(0, (float)($_POST['amount'] ?? 100));

$paymentMethodMap = [
    'esewa' => 'esewa',
    'khalti' => 'khalti',
    'bank_transfer' => 'bank_transfer',
    'online_now' => 'bank_transfer',
    'pay_later' => 'pay_later',
];

$paymentMethod = $paymentMethodMap[$paymentMethodInput] ?? 'pay_later';
$needsOnlinePayment = $paymentMethod !== 'pay_later';
$paymentLabel = match ($paymentMethod) {
    'esewa' => 'eSewa',
    'khalti' => 'Khalti',
    'bank_transfer' => 'Bank Transfer',
    default => 'Pay Later',
};

if ($category === '' || $serviceName === '' || $fullName === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $phone === '' || !$travelDate || $travelDate < date('Y-m-d')) {
    http_response_code(422);
    exit('Please return to the booking form and complete all required fields with a future travel date.');
}

$message = trim('Travelers: ' . $travelers . ($message !== '' ? "\n" . $message : ''));
if ($needsOnlinePayment) {
    $message .= ($message !== '' ? "\n" : '') . 'Payment method: ' . $paymentLabel;
    if ($paymentReference !== '') {
        $message .= ' | Reference: ' . $paymentReference;
    }
}
$user = getCurrentUser();

try {
    $stmt = $pdo->prepare('INSERT INTO service_bookings (user_id, service_category, service_name, full_name, email, phone, travel_date, message) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([$user['id'] ?? null, $category, $serviceName, $fullName, $email, $phone, $travelDate, $message]);
    $bookingId = (int) $pdo->lastInsertId();

    $paymentStatus = 'Pending';
    $paymentStmt = $pdo->prepare('INSERT INTO payments (booking_id, customer_name, type, amount, status, payment_date) VALUES (?, ?, ?, ?, ?, CURDATE())');
    $paymentStmt->execute([$bookingId, $fullName, $paymentLabel, $amount, $paymentStatus]);
} catch (PDOException $e) {
    http_response_code(500);
    exit('We could not save your booking request. Please try again later.');
}

$referer = $_SERVER['HTTP_REFERER'] ?? '../frontend/index.php';
$separator = str_contains($referer, '?') ? '&' : '?';
$paymentParam = $needsOnlinePayment ? '&payment=online' : '&payment=later';
$methodParam = '&method=' . rawurlencode($paymentMethod);
$amountParam = '&amount=' . rawurlencode((string) $amount);
header('Location: ' . $referer . $separator . 'booking=success' . $paymentParam . $methodParam . $amountParam);
exit;
