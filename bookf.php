<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$message = "";
$errors = [];

try {
    $conn = new mysqli("localhost", "root", "", "tour_travel_db");
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    // Graceful failure: show error, but do not crash the page.
    $errors[] = "Database connection failed: " . $e->getMessage();
    // Demo/fallback mode: allow page to load even without DB.
    $conn = null;
}

// If DB is not connected, we still render the page (no inserts will be attempted).

if ($conn && isset($_POST['save_booking'])) {
    $customer_id = trim($_POST['customer_id'] ?? '');
    $package_id  = trim($_POST['package_id'] ?? '');
    $booking_date = trim($_POST['booking_date'] ?? '');
    $status = trim($_POST['status'] ?? '');

    if ($customer_id === '' || !ctype_digit($customer_id)) $errors[] = "Invalid customer.";
    if ($package_id === '' || !ctype_digit($package_id)) $errors[] = "Invalid package.";
    if ($booking_date === '') $errors[] = "Booking date is required.";
    if (!in_array($status, ['pending', 'confirmed', 'cancelled'])) $errors[] = "Invalid status.";

    if (empty($errors)) {
        $customer_id = (int)$customer_id;
        $package_id = (int)$package_id;

        $stmt = $conn->prepare("INSERT INTO bookings (customer_id, package_id, booking_date, status) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $customer_id, $package_id, $booking_date, $status);

        if ($stmt->execute()) {
            $message = "Booking saved successfully.";
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
  <title>Single Page Booking</title>
  <style>
    body { font-family: Arial, sans-serif; background:#f4f7fb; margin:0; padding:20px; }
    .container { max-width:800px; margin:auto; }
    .card { background:#fff; padding:20px; border-radius:10px; box-shadow:0 2px 10px rgba(0,0,0,.08); }
    label { display:block; margin-top:12px; font-weight:bold; }
    input, select, button { width:100%; padding:10px; margin-top:6px; box-sizing:border-box; }
    button { background:#0b5ed7; color:#fff; border:0; cursor:pointer; margin-top:16px; }
    button:hover { background:#094db1; }
    .error { color:red; }
    .success { color:green; }
  </style>
</head>
<body>
<div class="container">
  <div class="card">
    <h2>Booking Form</h2>

    <?php
    if ($message) echo "<p class='success'>$message</p>";
    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo "<p class='error'>$error</p>";
        }
    }
    ?>

    <form method="post" action="" onsubmit="return validateBookingForm()">
      <label>Customer</label>
      <select name="customer_id" id="customer_id">
        <option value="">Select Customer</option>
      </select>

      <label>Package</label>
      <select name="package_id" id="package_id">
        <option value="">Select Package</option>
      </select>

      <label>Booking Date</label>
      <input type="date" name="booking_date" id="booking_date">

      <label>Status</label>
      <select name="status" id="status">
        <option value="">Select Status</option>
        <option value="pending">Pending</option>
        <option value="confirmed">Confirmed</option>
        <option value="cancelled">Cancelled</option>
      </select>

      <button type="submit" name="save_booking">Save Booking</button>
    </form>
  </div>
</div>

<script>
function validateBookingForm() {
  const customerId = document.getElementById("customer_id").value.trim();
  const packageId = document.getElementById("package_id").value.trim();
  const bookingDate = document.getElementById("booking_date").value.trim();
  const status = document.getElementById("status").value.trim();

  if (customerId === "") {
    alert("Please select a customer.");
    return false;
  }
  if (packageId === "") {
    alert("Please select a package.");
    return false;
  }
  if (bookingDate === "") {
    alert("Please choose booking date.");
    return false;
  }
  if (status === "") {
    alert("Please select status.");
    return false;
  }
  return true;
}
</script>
</body>
</html>