<?php
include '../database.php';

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if ($username === "" || $password === "") {
        $error = "All fields are required.";
    } else {

        // Check if username already exists
        $stmt = $conn->prepare(
            "SELECT user_id FROM users WHERE username = ? LIMIT 1"
        );
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Username already exists.";
            $stmt->close();
        } else {
            $stmt->close();

            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insert as PENDING staff user
            $stmt = $conn->prepare(
                "INSERT INTO users (username, password, role, status)
                 VALUES (?, ?, 'staff', 'pending')"
            );
            $stmt->bind_param("ss", $username, $hashedPassword);
            $stmt->execute();
            $stmt->close();

            $message = "Request submitted. Please wait for admin approval.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Request Access | Inventory System</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link rel="stylesheet" href="../admin/plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="../admin/dist/css/adminlte.min.css">
</head>

<body class="hold-transition login-page">
<div class="login-box">

  <div class="card">
    <div class="card-body login-card-body">
      <p class="login-box-msg">Request Access</p>

      <?php if ($error): ?>
        <p class="text-danger text-center"><?= htmlspecialchars($error) ?></p>
      <?php endif; ?>

      <?php if ($message): ?>
        <p class="text-success text-center"><?= htmlspecialchars($message) ?></p>
      <?php endif; ?>

      <form method="post">
        <div class="input-group mb-3">
          <input type="text" name="username" class="form-control" placeholder="Username" required>
        </div>

        <div class="input-group mb-3">
          <input type="password" name="password" class="form-control" placeholder="Password" required>
        </div>

        <button type="submit" class="btn btn-primary btn-block">
          Submit Request
        </button>
      </form>

      <p class="mt-3 text-center">
        <a href="login.php">Back to login</a>
      </p>

    </div>
  </div>

</div>
</body>
</html>
