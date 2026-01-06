<?php
session_start();

/* ===== LOGIN + ADMIN CHECK ===== */
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../auth/login.php");
    exit;
}

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit;
}

include '../../database.php';

/* ===== SHOW BACK BUTTON ===== */
$showBackButton = true;

/* ===== LAYOUT TOP ===== */
include '../../partials/layout_top.php';

/* ===== FETCH CATEGORIES ===== */
$categories = mysqli_query(
    $conn,
    "SELECT * FROM categories ORDER BY category_name ASC"
);

$message = "";

/* ===== HANDLE FORM SUBMIT ===== */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $item_name     = trim($_POST['item_name']);
    $model         = trim($_POST['model']);
    $serial_number = trim($_POST['serial_number']);
    $quantity      = (int) $_POST['quantity'];
    $location      = trim($_POST['location']);

    $use_new_cat   = isset($_POST['use_new_category']);
    $category_id   = (int) ($_POST['category_id'] ?? 0);
    $new_category  = trim($_POST['new_category'] ?? '');

    if ($item_name === "" || $quantity < 0) {
        $message = "Please fill in all required fields ❌";
    } else {

        /* ===== CREATE NEW CATEGORY IF REQUESTED ===== */
        if ($use_new_cat && $new_category !== "") {

            $check = $conn->prepare("
                SELECT category_id FROM categories WHERE category_name = ?
            ");
            $check->bind_param("s", $new_category);
            $check->execute();
            $res = $check->get_result();

            if ($res->num_rows > 0) {
                $category_id = $res->fetch_assoc()['category_id'];
            } else {
                $insertCat = $conn->prepare("
                    INSERT INTO categories (category_name)
                    VALUES (?)
                ");
                $insertCat->bind_param("s", $new_category);
                $insertCat->execute();
                $category_id = $insertCat->insert_id;
                $insertCat->close();
            }

            $check->close();
        }

        if ($category_id <= 0) {
            $message = "Please select or create a category ❌";
        } else {

            /* ===== INSERT ITEM (UPDATED) ===== */
            $stmt = $conn->prepare("
                INSERT INTO items 
                (item_name, model, serial_number, category_id, quantity, location)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param(
                "sssiss",
                $item_name,
                $model,
                $serial_number,
                $category_id,
                $quantity,
                $location
            );

            if ($stmt->execute()) {
                $message = "Item added successfully ✅";
            } else {
                $message = "Error adding item ❌";
            }

            $stmt->close();
        }
    }
}
?>

<!-- ================= PAGE HEADER ================= -->
<section class="content-header">
  <h1 class="mb-2">Add Inventory Item</h1>
  <p class="text-muted mb-0">Add a new item into the inventory system.</p>
</section>

<!-- ================= PAGE CONTENT ================= -->
<section class="content">

  <?php if ($message): ?>
    <div class="alert alert-info">
      <?= htmlspecialchars($message) ?>
    </div>
  <?php endif; ?>

  <div class="card">
    <div class="card-body">

      <form method="post">

        <div class="form-group">
          <label>Item Name</label>
          <input type="text" name="item_name" class="form-control" required>
        </div>

        <div class="form-group">
          <label>Model</label>
          <input type="text"
                 name="model"
                 class="form-control"
                 placeholder="e.g. Cisco Nexus 3064-X">
        </div>

        <div class="form-group">
          <label>Serial Number</label>
          <input type="text"
                 name="serial_number"
                 class="form-control"
                 placeholder="e.g. FOC1234ABC">
        </div>

        <div class="form-group">
          <label>Category</label>
          <select name="category_id" class="form-control">
            <option value="">-- Select Category --</option>
            <?php while ($cat = mysqli_fetch_assoc($categories)): ?>
              <option value="<?= $cat['category_id'] ?>">
                <?= htmlspecialchars($cat['category_name']) ?>
              </option>
            <?php endwhile; ?>
          </select>
        </div>

        <div class="form-group">
          <div class="form-check">
            <input type="checkbox"
                   class="form-check-input"
                   id="useNewCategory"
                   name="use_new_category"
                   onclick="toggleNewCategory()">
            <label class="form-check-label" for="useNewCategory">
              Add new category
            </label>
          </div>
        </div>

        <div class="form-group d-none" id="newCategoryBox">
          <label>New Category Name</label>
          <input type="text"
                 name="new_category"
                 class="form-control"
                 placeholder="e.g. Network Equipment">
        </div>

        <div class="form-group">
          <label>Quantity</label>
          <input type="number" name="quantity" class="form-control" min="0" required>
        </div>

        <div class="form-group">
          <label>Location</label>
          <input type="text"
                 name="location"
                 class="form-control"
                 placeholder="e.g. Rack A / Store Room">
        </div>

        <button type="submit" class="btn btn-primary">
          <i class="fas fa-save"></i> Save Item
        </button>

      </form>

    </div>
  </div>

</section>

<script>
function toggleNewCategory() {
  document.getElementById('newCategoryBox')
          .classList.toggle('d-none');
}
</script>

<?php include '../../partials/layout_bottom.php'; ?>
