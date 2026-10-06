<?php
require_once __DIR__ . '/../Backend/database.php';

if (!isLoggedIn()) {
    redirect('login.php?redirect=bank_payment.php');
}

$bookingId = max(0, (int) ($_GET['booking_id'] ?? 0));
$amount = max(0, (float) ($_GET['amount'] ?? 0));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bank Transfer Payment | Nepal Tour & Travel</title>
    <link rel="stylesheet" href="style.css?v=4">
    <style>
        .bank-payment { max-width: 700px; margin: 120px auto 60px; padding: 32px; border-radius: 18px; background: #fff; box-shadow: 0 15px 45px rgba(15, 44, 106, .14); }
        .bank-payment h1 { color: #173f91; margin-top: 0; }
        .bank-payment dt { font-weight: 700; color: #173f91; margin-top: 14px; }
        .bank-payment dd { margin: 4px 0; font-size: 1.05rem; }
        .bank-payment .reference { padding: 12px; background: #eef4ff; border-radius: 8px; font-weight: 700; }
        .bank-payment .actions { display: flex; gap: 12px; flex-wrap: wrap; margin-top: 28px; }
    </style>
</head>
<body>
    <nav class="navbar"><div class="nav-container"><a href="index.php" class="logo"><img src="../img/logo.png" alt="Nepal Tour and Travel" class="logo-icon"></a></div></nav>
    <main class="bank-payment">
        <h1>Complete your bank transfer</h1>
        <p>Your booking request was saved. Use your own trusted bank app, mobile banking, or branch to transfer the amount below. This website never asks for your bank username or password.</p>
        <dl>
            <dt>Amount</dt><dd>NPR <?= htmlspecialchars(number_format($amount, 2)) ?></dd>
            <dt>Payment reference</dt><dd class="reference">NPTT-BOOKING-<?= htmlspecialchars((string) $bookingId) ?></dd>
            <dt>Bank account details</dt><dd>Please contact Nepal Tour & Travel to receive the verified bank name, account name, and account number before transferring.</dd>
        </dl>
        <p>After payment, share the payment reference and transfer receipt with our team so we can confirm your booking.</p>
        <div class="actions"><a class="cta-button" href="index.php?booking=success&payment=online&method=bank_transfer&amount=<?= urlencode((string) $amount) ?>">I will pay by bank transfer</a><a class="cta-button" href="index.php">Return to home</a></div>
    </main>
</body>
</html>
