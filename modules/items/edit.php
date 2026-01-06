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

$id = $_GET['id'];

// Fetch existing item
$result = mysqli_query($conn, "SELECT * FROM items WHERE item_id=$id");
$item = mysqli_fetch_assoc($result);

if (!$item) {
    die("Item not found");
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $item_name = $_POST['item_name'];
    $category  = $_POST['category'];
    $quantity  = $_POST['quantity'];
    $location  = $_POST['location'];

    $sql = "UPDATE items 
            SET item_name='$item_name',
                category='$category',
                quantity='$quantity',
                location='$location'
            WHERE item_id=$id";

    if (mysqli_query($conn, $sql)) {
        $message = "Item updated successfully ✅";
    } else {
        $message = "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Edit Item</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link rel="stylesheet" href="../../admin/plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="../../admin/dist/css/adminlte.min.css">
</head>

<body class="hold-transition sidebar-mini">
<div class="wrapper">

  <div class="content-wrapper p-4">
    <section class="content-header">
      <h1>Edit Item</h1>
    </section>

    <section class="content">

      <?php if ($message): ?>
        <div class="alert alert-info"><?= $message ?></div>
      <?php endif; ?>

      <div class="card">
        <div class="card-body">

          <form method="post">
            <div class="form-group">
              <label>Item Name</label>
              <input type="text" name="item_name" class="form-control"
                     value="<?= htmlspecialchars($item['item_name']) ?>" required>
            </div>

            <div class="form-group">
              <label>Category</label>
              <input type="text" name="category" class="form-control"
                     value="<?= htmlspecialchars($item['category']) ?>">
            </div>

            <div class="form-group">
              <label>Quantity</label>
              <input type="number" name="quantity" class="form-control"
                     value="<?= $item['quantity'] ?>" required>
            </div>

            <div class="form-group">
              <label>Location</label>
              <input type="text" name="location" class="form-control"
                     value="<?= htmlspecialchars($item['location']) ?>">
            </div>

            <button type="submit" class="btn btn-primary">
              <i class="fas fa-save"></i> Update Item
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
