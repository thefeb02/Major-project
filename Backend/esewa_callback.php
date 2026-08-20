<?php
require_once __DIR__ . '/database.php';

$status = $_GET['status'] ?? 'failure';
$redirectBase = '../frontend/index.php'; // Default fallback

if ($status === 'success' && isset($_GET['data'])) {
    $dataStr = base64_decode($_GET['data']);
    $data = json_decode($dataStr, true);

    if ($data && isset($data['transaction_uuid'], $data['total_amount'], $data['product_code'])) {
        $transaction_uuid = $data['transaction_uuid'];
        $total_amount = str_replace(',', '', $data['total_amount']);
        $product_code = $data['product_code'];

        // Extract booking ID
        $parts = explode('-', $transaction_uuid);
        $bookingId = (int) $parts[0];

        // Verify with eSewa Status API
        $verifyUrl = "https://rc.esewa.com.np/api/epay/transaction/status/?product_code=" . urlencode($product_code) . 
                     "&total_amount=" . urlencode($total_amount) . 
                     "&transaction_uuid=" . urlencode($transaction_uuid);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $verifyUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $responseData = json_decode($response, true);
            if (isset($responseData['status']) && $responseData['status'] === 'COMPLETE') {
                // Payment verified, update database
                try {
                    $stmt = $pdo->prepare('UPDATE payments SET status = ? WHERE booking_id = ?');
                    $stmt->execute(['Completed', $bookingId]);
                    
                    // Redirect to frontend with success message
                    header('Location: ' . $redirectBase . '?booking=success&payment=online&method=esewa&amount=' . urlencode($total_amount));
                    exit;
                } catch (PDOException $e) {
                    // Database error
                    header('Location: ' . $redirectBase . '?payment_error=db_error');
                    exit;
                }
            }
        }
    }
}

// If we reach here, it's a failure or invalid data
header('Location: ' . $redirectBase . '?payment_error=esewa_failed');
exit;
