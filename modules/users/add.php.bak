<?php
session_start();

/* ===== LOGIN + ADMIN CHECK ===== */
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../auth/login.php");
    exit;
}

if (($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../../index.php");
    exit;
}

include '../../database.php';

/* ===== PAGE SETTINGS ===== */
$showBackButton = true;
include '../../partials/layout_top.php';

$message = '';

/* ===== HANDLE SUBMIT ===== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username   = trim($_POST['username'] ?? '');
    $password   = $_POST['password'] ?? '';
    $role       = $_POST['role'] ?? 'staff';
    $department = $_POST['department'] ?? '';

    if ($username === '' || $password === '' || $department === '') {
        $message = '<div class="alert alert-danger">All fields are required ❌</div>';
    } else {

        /* CHECK DUPLICATE USERNAME */
        $check = $conn->prepare("
            SELECT user_id FROM users WHERE username = ? LIMIT 1
        ");
        $check->bind_param("s", $username);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $message = '<div class="alert alert-danger">Username already exists ❌</div>';
            $check->close();
        } else {

            $check->close();
            $hashed = password_hash($password, PASSWORD_DEFAULT);

            /* IMPORTANT FIXES:
               1. status = 'active' (lowercase)
               2. department values match users/list.php
            */
            $stmt = $conn->prepare("
                INSERT INTO users
                (username, password, role, department, status, created_at)
                VALUES (?, ?, ?, ?, 'active', NOW())
            ");
            $stmt->bind_param(
                "ssss",
                $username,
                $hashed,
                $role,
                $department
            );
            $stmt->execute();
            $stmt->close();

            $message = '<div class="alert alert-success">User added successfully ✅</div>';
        }
    }
}
?>

<section class="content-header">
  <h1 class="mb-1">Add User</h1>
  <p class="text-muted mb-0">Create a new staff or intern account.</p>
</section>

<section class="content">

<?= $message ?>

<div class="row">
  <div class="col-lg-12">

    <div class="card card-outline card-primary">
      <div class="card-body">

        <form method="post">

          <div class="row">
            <div class="col-md-6">
              <label class="font-weight-bold">Username *</label>
              <input type="text" name="username" class="form-control" required>
            </div>

            <div class="col-md-6">
              <label class="font-weight-bold">Password *</label>
              <input type="password" name="password" class="form-control" required>
            </div>
          </div>

          <div class="row mt-3">
            <div class="col-md-3">
              <label class="font-weight-bold">Role *</label>
              <select name="role" class="form-control" required>
                <option value="staff">Staff</option>
                <option value="intern">Intern</option>
              </select>
            </div>

            <div class="col-md-3">
              <label class="font-weight-bold">Department *</label>
              <select name="department" class="form-control" required>
                <option value="">— Select Department —</option>
                <option value="Operations">Operations</option>
                <option value="Network">Network</option>
                <option value="NOC">NOC</option>
                <option value="Finance">Finance</option>
                <option value="HR">HR</option>
                <option value="Management">Management</option>
              </select>
            </div>
          </div>

          <div class="text-right mt-3">
            <button class="btn btn-primary">
              <i class="fas fa-user-plus"></i> Add User
            </button>
          </div>

        </form>

      </div>
    </div>

  </div>
</div>

</section>

<?php include '../../partials/layout_bottom.php'; ?>
