<?php
session_start();

/* ===== FORCE NO CACHE ===== */
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

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

/* ===== FETCH ITEMS ===== */
$result = mysqli_query($conn, "
    SELECT 
        i.item_id,
        i.item_name,
        i.model,
        i.serial_number,
        i.quantity,
        i.location,
        i.created_at,
        c.category_name
    FROM items i
    LEFT JOIN categories c
        ON i.category_id = c.category_id
    ORDER BY i.created_at DESC
");
?>

<!-- ================= PAGE HEADER ================= -->
<section class="content-header">
  <h1 class="mb-2">Inventory Items</h1>

  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item">
        <a href="/inventory_system/index.php">Dashboard</a>
      </li>
      <li class="breadcrumb-item">Inventory</li>
      <li class="breadcrumb-item active">Item List</li>
    </ol>
  </nav>
</section>

<!-- ================= PAGE CONTENT ================= -->
<section class="content">

  <div class="card">
    <div class="card-body table-responsive">

      <table class="table table-bordered table-striped">
        <thead>
          <tr>
            <th>ID</th>
            <th>Item Name</th>
            <th>Model</th>
            <th>Serial No.</th>
            <th>Category</th>
            <th>Quantity</th>
            <th>Location</th>
            <th>Created</th>
            <th>Action</th>
          </tr>
        </thead>

        <tbody>
        <?php if ($result && mysqli_num_rows($result) > 0): ?>
          <?php $no = 1; ?>
          <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr>

              <!-- ROW NUMBER (NOT DB ID) -->
              <td><?= $no++ ?></td>

              <td><strong><?= htmlspecialchars($row['item_name']) ?></strong></td>

              <td><?= $row['model'] ? htmlspecialchars($row['model']) : '<span class="text-muted">—</span>' ?></td>

              <td><?= $row['serial_number'] ? htmlspecialchars($row['serial_number']) : '<span class="text-muted">—</span>' ?></td>

              <td><?= htmlspecialchars($row['category_name'] ?? '—') ?></td>

              <td><?= (int)$row['quantity'] ?></td>

              <td><?= htmlspecialchars($row['location']) ?></td>

              <td>
                <?= $row['created_at']
                    ? date('d M Y', strtotime($row['created_at']))
                    : '-' ?>
              </td>

              <td>
                <a href="edit.php?id=<?= (int)$row['item_id'] ?>"
                   class="btn btn-sm btn-warning">
                  <i class="fas fa-edit"></i> Edit
                </a>

                <a href="delete.php?id=<?= (int)$row['item_id'] ?>"
                   class="btn btn-sm btn-danger"
                   onclick="return confirm('Are you sure you want to delete this item?');">
                  <i class="fas fa-trash"></i> Delete
                </a>
              </td>

            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="9" class="text-center text-muted">
              No items found
            </td>
          </tr>
        <?php endif; ?>
        </tbody>

      </table>

    </div>
  </div>

</section>

<?php include '../../partials/layout_bottom.php'; ?>
