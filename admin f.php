<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$conn = null;
try {
    $conn = new mysqli("localhost", "root", "", "tour_travel_db");
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    // Don’t fatal-crash the page when DB is down.
    $errors[] = "Database connection failed: " . $e->getMessage();
    $conn = null;
}


$admin_name = "";
$email = "";
$password = "";
$errors = [];
$success = "";

if (isset($_POST['save_admin'])) {
    $admin_name = trim($_POST['admin_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($admin_name === '') {
        $errors[] = "Admin name is required.";
    } elseif (!preg_match("/^[a-zA-Z ]+$/", $admin_name)) {
        $errors[] = "Admin name should contain only letters and spaces.";
    }

    if ($email === '') {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    if ($password === '') {
        $errors[] = "Password is required.";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
    }

    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO admins (admin_name, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $admin_name, $email, $hashed_password);

        if ($stmt->execute()) {
            $success = "Admin saved successfully.";
            $admin_name = "";
            $email = "";
            $password = "";
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
  <title>Admin Form</title>
  <style>
    body { font-family: Arial, sans-serif; background: #f4f7fb; padding: 20px; }
    .card { max-width: 500px; margin: auto; background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,.08); }
    label { display:block; margin-top: 12px; font-weight: bold; }
    input, button { width: 100%; padding: 10px; margin-top: 6px; box-sizing: border-box; }
    button { background:#0b5ed7; color:#fff; border:0; cursor:pointer; margin-top:16px; }
    button:hover { background:#094db1; }
    .error { color:red; }
    .success { color:green; }
  </style>
</head>
<body>
<div class="card">
  <h2>Add Admin</h2>

  <?php
  if ($success) echo "<p class='success'>$success</p>";
  if (!empty($errors)) {
      foreach ($errors as $error) {
          echo "<p class='error'>$error</p>";
      }
  }
  ?>

  <form method="post" action="" onsubmit="return validateAdminForm()">
    <label>Admin Name</label>
    <input type="text" name="admin_name" id="admin_name" value="<?php echo htmlspecialchars($admin_name); ?>">

    <label>Email</label>
    <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($email); ?>">

    <label>Password</label>
    <input type="password" name="password" id="password">

    <button type="submit" name="save_admin">Save Admin</button>
  </form>
</div>

<script>
function validateAdminForm() {
  const adminName = document.getElementById("admin_name").value.trim();
  const email = document.getElementById("email").value.trim();
  const password = document.getElementById("password").value.trim();

  if (adminName === "") {
    alert("Please enter admin name.");
    return false;
  }
  if (email === "") {
    alert("Please enter email.");
    return false;
  }
  if (password === "") {
    alert("Please enter password.");
    return false;
  }
  if (password.length < 6) {
    alert("Password must be at least 6 characters long.");
    return false;
  }
  return true;
}
</script>
</body>
</html>