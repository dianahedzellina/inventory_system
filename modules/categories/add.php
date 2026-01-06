<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../auth/login.php");
    exit;
}

include '../../database.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $category_name = trim($_POST['category_name']);

    if ($category_name == "") {
        $message = "Category name cannot be empty.";
    } else {
        $sql = "INSERT INTO categories (category_name) VALUES ('$category_name')";
        if (mysqli_query($conn, $sql)) {
            header("Location: list.php");
            exit;
        } else {
            $message = "Error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Add Category</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link rel="stylesheet" href="../../admin/plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="../../admin/dist/css/adminlte.min.css">
</head>

<body class="hold-transition sidebar-mini">
<div class="wrapper">

  <div class="content-wrapper p-4">
    <section class="content-header">
      <h1>Add Category</h1>
    </section>

    <section class="content">

      <?php if ($message): ?>
        <div class="alert alert-danger"><?= $message ?></div>
      <?php endif; ?>

      <div class="card">
        <div class="card-body">

          <form method="post">
            <div class="form-group">
              <label>Category Name</label>
              <input type="text" name="category_name" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary">
              <i class="fas fa-save"></i> Save
            </button>

            <a href="list.php" class="btn btn-secondary">Back</a>
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
