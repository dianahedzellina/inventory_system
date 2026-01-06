<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../auth/login.php");
    exit;
}

include '../../database.php';

if (!isset($_GET['id'])) {
    header("Location: list.php");
    exit;
}

$id = (int) $_GET['id'];

// Fetch category
$result = mysqli_query($conn, "SELECT * FROM categories WHERE category_id = $id");
$category = mysqli_fetch_assoc($result);

if (!$category) {
    die("Category not found");
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $category_name = trim($_POST['category_name']);

    if ($category_name === "") {
        $message = "Category name cannot be empty.";
    } else {
        $sql = "UPDATE categories 
                SET category_name = '$category_name' 
                WHERE category_id = $id";

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
  <title>Edit Category</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link rel="stylesheet" href="../../admin/plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="../../admin/dist/css/adminlte.min.css">
</head>

<body class="hold-transition sidebar-mini">
<div class="wrapper">

  <div class="content-wrapper p-4">
    <section class="content-header">
      <h1>Edit Category</h1>
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
              <input type="text" name="category_name"
                     class="form-control"
                     value="<?= htmlspecialchars($category['category_name']) ?>"
                     required>
            </div>

            <button type="submit" class="btn btn-primary">
              <i class="fas fa-save"></i> Update
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
