<?php
session_start();

/* ===== LOGIN CHECK ===== */
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../auth/login.php");
    exit;
}

/* ===== ADMIN ONLY ===== */
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit;
}

include '../../database.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role     = $_POST['role'];

    if ($username === "" || $password === "" || $role === "") {
        $message = "All fields are required.";
    } else {

        /* Check if username exists */
        $stmt = $conn->prepare(
            "SELECT user_id FROM users WHERE username = ? LIMIT 1"
        );
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $message = "Username already exists ❌";
            $stmt->close();
        } else {
            $stmt->close();

            /* Hash password */
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            /* Insert new user */
            $stmt = $conn->prepare(
                "INSERT INTO users (username, password, role)
                 VALUES (?, ?, ?)"
            );
            $stmt->bind_param("sss", $username, $hashedPassword, $role);
            $stmt->execute();
            $stmt->close();

            $message = "User created successfully ✅";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Add User | Inventory System</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link rel="stylesheet" href="../../admin/plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="../../admin/dist/css/adminlte.min.css">
</head>

<body class="hold-transition sidebar-mini">
<div class="wrapper">

  <div class="content-wrapper p-4">
    <section class="content-header">
      <h1 class="mb-3">Add User</h1>
    </section>

    <section class="content">

      <?php if ($message): ?>
        <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
      <?php endif; ?>

      <div class="card">
        <div class="card-body">

          <form method="post">
            <div class="form-group">
              <label>Username</label>
              <input type="text" name="username" class="form-control" required>
            </div>

            <div class="form-group">
              <label>Password</label>
              <input type="password" name="password" class="form-control" required>
            </div>

            <div class="form-group">
              <label>Role</label>
              <select name="role" class="form-control" required>
                <option value="">-- Select Role --</option>
                <option value="staff">Staff</option>
                <option value="admin">Admin</option>
              </select>
            </div>

            <button type="submit" class="btn btn-primary">
              <i class="fas fa-user-plus"></i> Create User
            </button>

          </form>

        </div>
      </div>

    </section>
  </div>

</div>

<script src="../../admin/plugins/jquery/jquery.min.js"></script>
<script src="../../admin/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../../admin/dist/js/adminlte.min.js"></script>
</body>
</html>
