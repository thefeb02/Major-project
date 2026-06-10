<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$conn = null;
$errors = [];

try {
    $conn = new mysqli("localhost", "root", "", "tour_travel_db");
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    // Don’t crash the page when DB is down.
    $errors[] = "Database connection failed: " . $e->getMessage();
    $conn = null;
}



$booking_id = "";
$cancel_date = "";
$reason = "";
$errors = [];
$success = "";

if (isset($_POST['save_cancellation'])) {
    $booking_id = trim($_POST['booking_id'] ?? '');
    $cancel_date = trim($_POST['cancel_date'] ?? '');
    $reason = trim($_POST['reason'] ?? '');

    if ($booking_id === '' || !ctype_digit($booking_id)) {
        $errors[] = "Invalid booking.";
    }

    if ($cancel_date === '') {
        $errors[] = "Cancel date is required.";
    } else {
        $today = date('Y-m-d');
        if ($cancel_date < $today) {
            $errors[] = "Cancel date cannot be in the past.";
        }
    }

    if ($reason === '') {
        $errors[] = "Reason is required.";
    } elseif (strlen($reason) < 5) {
        $errors[] = "Reason must be at least 5 characters.";
    }

    if (empty($errors)) {
        $booking_id = (int)$booking_id;

        $stmt = $conn->prepare("INSERT INTO cancellations (booking_id, cancel_date, reason) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $booking_id, $cancel_date, $reason);

        if ($stmt->execute()) {
            $success = "Cancellation saved successfully.";
            $booking_id = $cancel_date = $reason = "";
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
  <title>Cancellation Form</title>
  <style>
    body { font-family: Arial, sans-serif; background:#f4f7fb; padding:20px; }
    .card { max-width: 520px; margin:auto; background:#fff; padding:20px; border-radius:10px; box-shadow:0 2px 10px rgba(0,0,0,.08); }
    label { display:block; margin-top:12px; font-weight:bold; }
    input, textarea, button { width:100%; padding:10px; margin-top:6px; box-sizing:border-box; }
    textarea { resize: vertical; min-height: 100px; }
    button { background:#0b5ed7; color:#fff; border:0; cursor:pointer; margin-top:16px; }
    button:hover { background:#094db1; }
    .error { color:red; }
    .success { color:green; }
  </style>
</head>
<body>
<div class="card">
  <h2>Add Cancellation</h2>

  <?php
  if ($success) echo "<p class='success'>$success</p>";
  if (!empty($errors)) {
      foreach ($errors as $error) {
          echo "<p class='error'>$error</p>";
      }
  }
  ?>

  <form method="post" action="" onsubmit="return validateCancellationForm()">
    <label>Booking ID</label>
    <input type="number" name="booking_id" id="booking_id" value="<?php echo htmlspecialchars($booking_id); ?>" min="1">

    <label>Cancel Date</label>
    <input type="date" name="cancel_date" id="cancel_date" value="<?php echo htmlspecialchars($cancel_date); ?>" min="<?php echo date('Y-m-d'); ?>">

    <label>Reason</label>
    <textarea name="reason" id="reason"><?php echo htmlspecialchars($reason); ?></textarea>

    <button type="submit" name="save_cancellation">Save Cancellation</button>
  </form>
</div>

<script>
function validateCancellationForm() {
  const bookingId = document.getElementById("booking_id").value.trim();
  const cancelDate = document.getElementById("cancel_date").value.trim();
  const reason = document.getElementById("reason").value.trim();
  const today = new Date().toISOString().split("T")[0];

  if (bookingId === "") {
    alert("Please enter booking ID.");
    return false;
  }
  if (cancelDate === "") {
    alert("Please choose cancel date.");
    return false;
  }
  if (cancelDate < today) {
    alert("Cancel date cannot be in the past.");
    return false;
  }
  if (reason === "") {
    alert("Please enter reason.");
    return false;
  }
  if (reason.length < 5) {
    alert("Reason must be at least 5 characters.");
    return false;
  }
  return true;
}
</script>
</body>
</html>