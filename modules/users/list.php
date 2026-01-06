<?php
session_start();

/* ===== LOGIN CHECK ===== */
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../auth/login.php");
    exit;
}

/* ===== ADMIN ONLY ===== */
if (($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../../index.php");
    exit;
}

include '../../database.php';

/* =========================
   APPROVE / REJECT USER
========================= */
if (isset($_GET['action'], $_GET['id']) && ctype_digit($_GET['id'])) {

    $user_id = (int) $_GET['id'];
    $action  = $_GET['action'];

    if ($action === 'approve') {
        $stmt = $conn->prepare("
            UPDATE users
            SET status = 'active'
            WHERE user_id = ?
            LIMIT 1
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();

    } elseif ($action === 'reject') {
        $stmt = $conn->prepare("
            UPDATE users
            SET status = 'rejected'
            WHERE user_id = ?
            LIMIT 1
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: list.php");
    exit;
}

/* =========================
   UPDATE DEPARTMENT (POST)
   ✅ ACTIVE STAFF ONLY
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_department'])) {

    $target_user_id = (int) $_POST['user_id'];
    $department     = trim($_POST['department']);

    $check = $conn->prepare("
        SELECT role, status FROM users WHERE user_id = ? LIMIT 1
    ");
    $check->bind_param("i", $target_user_id);
    $check->execute();
    $check->bind_result($role, $status);
    $check->fetch();
    $check->close();

    if (strtolower($role) === 'staff' && strtolower($status) === 'active') {
        $stmt = $conn->prepare("
            UPDATE users SET department = ? WHERE user_id = ?
        ");
        $stmt->bind_param("si", $department, $target_user_id);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: list.php");
    exit;
}

/* ===== FETCH USERS ===== */
$users = mysqli_query($conn, "
    SELECT 
        user_id,
        username,
        role,
        department,
        status,
        created_at
    FROM users
    ORDER BY created_at DESC
");

/* ===== DEPARTMENT OPTIONS ===== */
$departments = [
    'Operations',
    'Network',
    'NOC',
    'Finance',
    'HR',
    'Management'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>User List</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link rel="stylesheet" href="../../admin/plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="../../admin/dist/css/adminlte.min.css">
</head>

<body class="hold-transition sidebar-mini">
<div class="wrapper">

  <!-- NAVBAR -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#">
          <i class="fas fa-bars"></i>
        </a>
      </li>
    </ul>
  </nav>

  <!-- SIDEBAR -->
  <?php
    $showBackButton = true;
    include '../../partials/sidebar.php';
  ?>

  <!-- CONTENT -->
  <div class="content-wrapper p-4">

    <section class="content-header">
      <h1 class="mb-3">User List</h1>
    </section>

    <section class="content">

      <div class="card">
        <div class="card-body table-responsive">

          <table class="table table-bordered table-striped">
            <thead>
              <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Role</th>
                <th>Department</th>
                <th>Status</th>
                <th>Created At</th>
              </tr>
            </thead>
            <tbody>

            <?php if ($users && mysqli_num_rows($users) > 0): ?>
              <?php while ($u = mysqli_fetch_assoc($users)): ?>
                <tr>

                  <td><?= (int)$u['user_id'] ?></td>
                  <td><?= htmlspecialchars($u['username']) ?></td>

                  <!-- ROLE -->
                  <td>
                    <span class="badge badge-<?= strtolower($u['role']) === 'admin' ? 'danger' : 'secondary' ?>">
                      <?= ucfirst($u['role']) ?>
                    </span>
                  </td>

                  <!-- DEPARTMENT -->
                  <td>
                    <?php if (strtolower($u['role']) !== 'staff'): ?>

                      <span class="text-muted">—</span>

                    <?php elseif ($u['status'] !== 'active'): ?>

                      <span class="text-muted">—</span>

                    <?php else: ?>

                      <strong><?= htmlspecialchars($u['department'] ?: '—') ?></strong>

                      <form method="post" class="form-inline mt-1">
                        <input type="hidden" name="user_id" value="<?= (int)$u['user_id'] ?>">

                        <select name="department"
                                class="form-control form-control-sm mr-2"
                                required>
                          <?php foreach ($departments as $dept): ?>
                            <option value="<?= $dept ?>"
                              <?= $u['department'] === $dept ? 'selected' : '' ?>>
                              <?= $dept ?>
                            </option>
                          <?php endforeach; ?>
                        </select>

                        <button type="submit"
                                name="set_department"
                                class="btn btn-sm btn-primary">
                          Save
                        </button>
                      </form>

                    <?php endif; ?>
                  </td>

                  <!-- STATUS -->
                  <td>
                    <?php if ($u['status'] === 'active'): ?>
                      <span class="badge badge-success">Active</span>
                    <?php elseif ($u['status'] === 'pending'): ?>
                      <span class="badge badge-warning">Pending</span><br>
                      <a href="list.php?action=approve&id=<?= (int)$u['user_id'] ?>"
                         class="btn btn-sm btn-success mt-1">Approve</a>
                      <a href="list.php?action=reject&id=<?= (int)$u['user_id'] ?>"
                         class="btn btn-sm btn-danger mt-1">Reject</a>
                    <?php else: ?>
                      <span class="badge badge-danger">Rejected</span>
                    <?php endif; ?>
                  </td>

                  <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>

                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="6" class="text-center text-muted">No users found</td>
              </tr>
            <?php endif; ?>

            </tbody>
          </table>

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
