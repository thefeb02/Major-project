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



$name = "";
$email = "";
$phone_number = "";
$address = "";
$errors = [];
$success = "";

if ($conn && isset($_POST['save_customer'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone_number = trim($_POST['phone_number'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if ($name === '') {
        $errors[] = "Name is required.";
    } elseif (!preg_match("/^[a-zA-Z ]+$/", $name)) {
        $errors[] = "Name should contain only letters and spaces.";
    }

    if ($email === '') {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    if ($phone_number === '') {
        $errors[] = "Phone number is required.";
    } elseif (!preg_match("/^[0-9+\-\s]{7,20}$/", $phone_number)) {
        $errors[] = "Invalid phone number.";
    }

    if ($address === '') {
        $errors[] = "Address is required.";
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO customers (name, email, phone_number, address) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $phone_number, $address);

        if ($stmt->execute()) {
            $success = "Customer saved successfully.";
            $name = $email = $phone_number = $address = "";
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
  <title>Customer Form</title>
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
  <h2>Add Customer</h2>

  <?php
  if ($success) echo "<p class='success'>$success</p>";
  if (!empty($errors)) {
      foreach ($errors as $error) {
          echo "<p class='error'>$error</p>";
      }
  }
  ?>

  <form method="post" action="" onsubmit="return validateCustomerForm()">
    <label>Name</label>
    <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($name); ?>">

    <label>Email</label>
    <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($email); ?>">

    <label>Phone Number</label>
    <input type="text" name="phone_number" id="phone_number" value="<?php echo htmlspecialchars($phone_number); ?>">

    <label>Address</label>
    <textarea name="address" id="address"><?php echo htmlspecialchars($address); ?></textarea>

    <button type="submit" name="save_customer">Save Customer</button>
  </form>
</div>

<script>
function validateCustomerForm() {
  const name = document.getElementById("name").value.trim();
  const email = document.getElementById("email").value.trim();
  const phone = document.getElementById("phone_number").value.trim();
  const address = document.getElementById("address").value.trim();

  if (name === "") {
    alert("Please enter name.");
    return false;
  }
  if (email === "") {
    alert("Please enter email.");
    return false;
  }
  if (phone === "") {
    alert("Please enter phone number.");
    return false;
  }
  if (address === "") {
    alert("Please enter address.");
    return false;
  }
  return true;
}
</script>
</body>
</html>