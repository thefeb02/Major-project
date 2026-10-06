<?php
require_once __DIR__ . '/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../frontend/index.php');
}

$category = trim($_POST['service_category'] ?? 'Tour');
$serviceName = trim($_POST['service_name'] ?? '');
$requestedPackageId = trim($_POST['package_id'] ?? '');
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

if (!isLoggedIn()) {
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    $redirectPath = 'index.php';
    if ($referer !== '') {
        $parsedReferer = parse_url($referer);
        if (!empty($parsedReferer['path'])) {
            $redirectPath = basename($parsedReferer['path']);
            if ($redirectPath === '') {
                $redirectPath = 'index.php';
            }
            if (!empty($parsedReferer['query'])) {
                $redirectPath .= '?' . $parsedReferer['query'];
            }
        }
    }

    header('Location: ../frontend/login.php?redirect=' . rawurlencode($redirectPath));
    exit;
}

if ($category === '' || $serviceName === '' || $fullName === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $phone === '' || !$travelDate || $travelDate < date('Y-m-d')) {
    http_response_code(422);
    exit('Please return to the booking form and complete all required fields with a future travel date.');
}

$serviceName = mb_substr($serviceName, 0, 190);
$category = mb_substr($category, 0, 100);
$paymentReference = mb_substr($paymentReference, 0, 100);
$message = trim('Travelers: ' . $travelers . ($message !== '' ? "\n" . $message : ''));
$message .= ($message !== '' ? "\n" : '') . 'Payment method: ' . $paymentLabel;
if ($paymentReference !== '') {
    $message .= ' | Reference: ' . $paymentReference;
}
$user = getCurrentUser();

try {
    $packageId = null;
    if ($requestedPackageId !== '') {
        if (!ctype_digit($requestedPackageId) || (int) $requestedPackageId < 1) {
            http_response_code(422);
            exit('The selected package is invalid. Please choose a package and try again.');
        }
        $pStmt = $pdo->prepare("SELECT p.id, p.title, c.name AS category FROM packages p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ? AND p.status = 'active'");
        $pStmt->execute([(int) $requestedPackageId]);
        $selectedPackage = $pStmt->fetch(PDO::FETCH_ASSOC);
        if (!$selectedPackage) {
            http_response_code(422);
            exit('This package is no longer available. Please choose another package.');
        }
        $packageId = (int) $selectedPackage['id'];
        $serviceName = $selectedPackage['title'];
        if (!empty($selectedPackage['category'])) {
            $category = $selectedPackage['category'];
        }
    } else {
        $pStmt = $pdo->prepare("SELECT p.id, p.title, c.name AS category FROM packages p LEFT JOIN categories c ON p.category_id = c.id WHERE p.title = ? AND p.status = 'active' LIMIT 1");
        $pStmt->execute([$serviceName]);
        $selectedPackage = $pStmt->fetch(PDO::FETCH_ASSOC);
        if ($selectedPackage) {
            $packageId = (int) $selectedPackage['id'];
            $serviceName = $selectedPackage['title'];
            if (!empty($selectedPackage['category'])) {
                $category = $selectedPackage['category'];
            }
        }
    }

    $stmt = $pdo->prepare('INSERT INTO bookings (full_name, email, phone, package_id, service_name, service_category, travelers, travel_date, message) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([$fullName, $email, $phone, $packageId, $serviceName, $category, $travelers, $travelDate, $message]);
    $bookingId = (int) $pdo->lastInsertId();

    try {
        $paymentStatus = 'Pending';
        $paymentStmt = $pdo->prepare('INSERT INTO payments (booking_id, amount, status, payment_method) VALUES (?, ?, ?, ?)');
        $paymentStmt->execute([$bookingId, $amount, $paymentStatus, $paymentMethod]);
    } catch (PDOException $paymentError) {
        error_log('Payment tracking insert failed for booking ' . $bookingId . ': ' . $paymentError->getMessage());
    }
} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        session_destroy();
        header('Location: ../frontend/login.php?message=' . urlencode('Your session is invalid. Please log in again.'));
        exit;
    }
    http_response_code(500);
    error_log('Booking save failed: ' . $e->getMessage());
    exit('We could not save your booking request. Please try again later.');
}

if ($paymentMethod === 'esewa') {
    $transaction_uuid = $bookingId . '-' . time();
    $product_code = 'EPAYTEST';
    $secret_key = '8gBm/:&EnhH.1/q';
    
    $messageToSign = "total_amount=$amount,transaction_uuid=$transaction_uuid,product_code=$product_code";
    $signature = base64_encode(hash_hmac('sha256', $messageToSign, $secret_key, true));

    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $base_dir = dirname($_SERVER['PHP_SELF']); // e.g. /Major-project/Backend
    $success_url = $protocol . '://' . $host . $base_dir . '/esewa_callback.php?status=success';
    $failure_url = $protocol . '://' . $host . $base_dir . '/esewa_callback.php?status=failure';

    // Render an auto-submitting form
    echo '<!DOCTYPE html>
<html>
<head>
    <title>Redirecting to eSewa...</title>
</head>
<body onload="document.forms[\'esewa_form\'].submit();">
    <h2>Redirecting to eSewa... Please wait.</h2>
    <form id="esewa_form" action="https://rc-epay.esewa.com.np/api/epay/main/v2/form" method="POST">
        <input type="hidden" name="amount" value="' . htmlspecialchars($amount) . '">
        <input type="hidden" name="tax_amount" value="0">
        <input type="hidden" name="total_amount" value="' . htmlspecialchars($amount) . '">
        <input type="hidden" name="transaction_uuid" value="' . htmlspecialchars($transaction_uuid) . '">
        <input type="hidden" name="product_code" value="' . htmlspecialchars($product_code) . '">
        <input type="hidden" name="product_delivery_charge" value="0">
        <input type="hidden" name="product_service_charge" value="0">
        <input type="hidden" name="success_url" value="' . htmlspecialchars($success_url) . '">
        <input type="hidden" name="failure_url" value="' . htmlspecialchars($failure_url) . '">
        <input type="hidden" name="signed_field_names" value="total_amount,transaction_uuid,product_code">
        <input type="hidden" name="signature" value="' . htmlspecialchars($signature) . '">
    </form>
</body>
</html>';
    exit;
}

if ($paymentMethod === 'bank_transfer') {
    header('Location: ../frontend/bank_payment.php?booking_id=' . rawurlencode((string) $bookingId) . '&amount=' . rawurlencode((string) $amount));
    exit;
}

$referer = $_SERVER['HTTP_REFERER'] ?? '../frontend/index.php';
$separator = str_contains($referer, '?') ? '&' : '?';
$paymentParam = $needsOnlinePayment ? '&payment=online' : '&payment=later';
$methodParam = '&method=' . rawurlencode($paymentMethod);
$amountParam = '&amount=' . rawurlencode((string) $amount);
header('Location: ' . $referer . $separator . 'booking=success' . $paymentParam . $methodParam . $amountParam);
exit;
