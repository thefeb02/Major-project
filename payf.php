<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$conn = null;
$errors = [];

try {
    $conn = new mysqli("localhost", "root", "", "tour_travel_db");
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    $errors[] = "Database connection failed: " . $e->getMessage();
    $conn = null;
}



$booking_id = "";
$amount = "";
$payment_date = "";
$method = "";
$errors = [];
$success = "";

if ($conn && isset($_POST['save_payment'])) {
    $booking_id = trim($_POST['booking_id'] ?? '');
    $amount = trim($_POST['amount'] ?? '');
    $payment_date = trim($_POST['payment_date'] ?? '');
    $method = trim($_POST['method'] ?? '');

    if ($booking_id === '' || !ctype_digit($booking_id)) {
        $errors[] = "Invalid booking.";
    }

    if ($amount === '' || !is_numeric($amount) || $amount <= 0) {
        $errors[] = "Amount must be a valid number greater than 0.";
    }

    if ($payment_date === '') {
        $errors[] = "Payment date is required.";
    } else {
        $today = date('Y-m-d');
        if ($payment_date < $today) {
            $errors[] = "Payment date cannot be in the past.";
        }
    }

    $allowed_methods = ['cash', 'card', 'bank', 'esewa', 'khalti'];
    if ($method === '' || !in_array($method, $allowed_methods)) {
        $errors[] = "Please select a valid payment method.";
    }

    if (empty($errors)) {
        $booking_id = (int)$booking_id;
        $amount = (float)$amount;

        $stmt = $conn->prepare("INSERT INTO payments (booking_id, amount, payment_date, method) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("idss", $booking_id, $amount, $payment_date, $method);

        if ($stmt->execute()) {
            $success = "Payment saved successfully.";
            $booking_id = $amount = $payment_date = $method = "";
        } else {
            $errors[] = "Database error: " . $stmt->error;
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Payment Form</title>
  <style>
    body { font-family: Arial, sans-serif; background:#f4f7fb; padding:20px; }
    .card { max-width: 520px; margin:auto; background:#fff; padding:20px; border-radius:10px; box-shadow:0 2px 10px rgba(0,0,0,.08); }
    label { display:block; margin-top:12px; font-weight:bold; }
    input, select, button { width:100%; padding:10px; margin-top:6px; box-sizing:border-box; }
    button { background:#0b5ed7; color:#fff; border:0; cursor:pointer; margin-top:16px; }
    button:hover { background:#094db1; }
    .error { color:red; }
    .success { color:green; }
  </style>
</head>
<body>
<div class="card">
  <h2>Add Payment</h2>

  <?php
  if ($success) echo "<p class='success'>$success</p>";
  if (!empty($errors)) {
      foreach ($errors as $error) {
          echo "<p class='error'>$error</p>";
      }
  }
  ?>

  <form method="post" action="" onsubmit="return validatePaymentForm()">
    <label>Booking ID</label>
    <input type="number" name="booking_id" id="booking_id" value="<?php echo htmlspecialchars($booking_id); ?>" min="1">

    <label>Amount</label>
    <input type="number" step="0.01" name="amount" id="amount" value="<?php echo htmlspecialchars($amount); ?>" min="0.01">

    <label>Payment Date</label>
    <input type="date" name="payment_date" id="payment_date" value="<?php echo htmlspecialchars($payment_date); ?>" min="<?php echo date('Y-m-d'); ?>">

    <label>Method</label>
    <select name="method" id="method">
      <option value="">Select Method</option>
      <option value="cash" <?php if ($method === 'cash') echo 'selected'; ?>>Cash</option>
      <option value="card" <?php if ($method === 'card') echo 'selected'; ?>>Card</option>
      <option value="bank" <?php if ($method === 'bank') echo 'selected'; ?>>Bank</option>
      <option value="esewa" <?php if ($method === 'esewa') echo 'selected'; ?>>Esewa</option>
      <option value="khalti" <?php if ($method === 'khalti') echo 'selected'; ?>>Khalti</option>
    </select>

    <button type="submit" name="save_payment">Save Payment</button>
  </form>
</div>

<script>
function validatePaymentForm() {
  const bookingId = document.getElementById("booking_id").value.trim();
  const amount = document.getElementById("amount").value.trim();
  const paymentDate = document.getElementById("payment_date").value.trim();
  const method = document.getElementById("method").value.trim();
  const today = new Date().toISOString().split("T")[0];

  if (bookingId === "") {
    alert("Please enter booking ID.");
    return false;
  }
  if (amount === "" || parseFloat(amount) <= 0) {
    alert("Please enter a valid amount.");
    return false;
  }
  if (paymentDate === "") {
    alert("Please choose payment date.");
    return false;
  }
  if (paymentDate < today) {
    alert("Payment date cannot be in the past.");
    return false;
  }
  if (method === "") {
    alert("Please select payment method.");
    return false;
  }
  return true;
}
</script>
</body>
</html>