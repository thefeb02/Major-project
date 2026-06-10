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



$package_name = "";
$destination = "";
$price = "";
$duration = "";
$description = "";
$errors = [];
$success = "";

if ($conn && isset($_POST['save_package'])) {
    $package_name = trim($_POST['package_name'] ?? '');
    $destination = trim($_POST['destination'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $duration = trim($_POST['duration'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($package_name === '') {
        $errors[] = "Package name is required.";
    } elseif (!preg_match("/^[a-zA-Z0-9\s\-']+$/", $package_name)) {
        $errors[] = "Package name contains invalid characters.";
    }

    if ($destination === '') {
        $errors[] = "Destination is required.";
    }

    if ($price === '' || !is_numeric($price) || $price <= 0) {
        $errors[] = "Price must be a valid number greater than 0.";
    }

    if ($duration === '') {
        $errors[] = "Duration is required.";
    }

    if ($description === '') {
        $errors[] = "Description is required.";
    }

    if (empty($errors)) {
        $price = (float)$price;

        $stmt = $conn->prepare("INSERT INTO packages (package_name, destination, price, duration, description) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdss", $package_name, $destination, $price, $duration, $description);

        if ($stmt->execute()) {
            $success = "Package saved successfully.";
            $package_name = $destination = $price = $duration = $description = "";
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
  <title>Package Form</title>
  <style>
    body { font-family: Arial, sans-serif; background:#f4f7fb; padding:20px; }
    .card { max-width: 600px; margin:auto; background:#fff; padding:20px; border-radius:10px; box-shadow:0 2px 10px rgba(0,0,0,.08); }
    label { display:block; margin-top:12px; font-weight:bold; }
    input, textarea, button { width:100%; padding:10px; margin-top:6px; box-sizing:border-box; }
    textarea { resize: vertical; min-height: 120px; }
    button { background:#0b5ed7; color:#fff; border:0; cursor:pointer; margin-top:16px; }
    button:hover { background:#094db1; }
    .error { color:red; }
    .success { color:green; }
  </style>
</head>
<body>
<div class="card">
  <h2>Add Package</h2>

  <?php
  if ($success) echo "<p class='success'>$success</p>";
  if (!empty($errors)) {
      foreach ($errors as $error) {
          echo "<p class='error'>$error</p>";
      }
  }
  ?>

  <form method="post" action="" onsubmit="return validatePackageForm()">
    <label>Package Name</label>
    <input type="text" name="package_name" id="package_name" value="<?php echo htmlspecialchars($package_name); ?>">

    <label>Destination</label>
    <input type="text" name="destination" id="destination" value="<?php echo htmlspecialchars($destination); ?>">

    <label>Price</label>
    <input type="number" step="0.01" name="price" id="price" value="<?php echo htmlspecialchars($price); ?>" min="0.01">

    <label>Duration</label>
    <input type="text" name="duration" id="duration" value="<?php echo htmlspecialchars($duration); ?>">

    <label>Description</label>
    <textarea name="description" id="description"><?php echo htmlspecialchars($description); ?></textarea>

    <button type="submit" name="save_package">Save Package</button>
  </form>
</div>

<script>
function validatePackageForm() {
  const packageName = document.getElementById("package_name").value.trim();
  const destination = document.getElementById("destination").value.trim();
  const price = document.getElementById("price").value.trim();
  const duration = document.getElementById("duration").value.trim();
  const description = document.getElementById("description").value.trim();

  if (packageName === "") {
    alert("Please enter package name.");
    return false;
  }
  if (destination === "") {
    alert("Please enter destination.");
    return false;
  }
  if (price === "" || parseFloat(price) <= 0) {
    alert("Please enter a valid price.");
    return false;
  }
  if (duration === "") {
    alert("Please enter duration.");
    return false;
  }
  if (description === "") {
    alert("Please enter description.");
    return false;
  }
  return true;
}
</script>
</body>
</html>