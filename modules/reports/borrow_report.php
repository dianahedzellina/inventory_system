<?php
session_start();

/* ===== ADMIN CHECK ===== */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit;
}

include '../../database.php';

/* ===== SHOW BACK BUTTON ===== */
$showBackButton = true;

/* ===== LAYOUT TOP ===== */
include '../../partials/layout_top.php';

/* ===== FILTER INPUT ===== */
$from_date = $_GET['from_date'] ?? '';
$to_date   = $_GET['to_date'] ?? '';
$user_id   = $_GET['user_id'] ?? '';
$item_id   = $_GET['item_id'] ?? '';

$where = [];

if ($from_date !== '') {
    $where[] = "b.borrow_date >= '" . mysqli_real_escape_string($conn, $from_date) . "'";
}

if ($to_date !== '') {
    $where[] = "b.borrow_date <= '" . mysqli_real_escape_string($conn, $to_date) . "'";
}

if ($user_id !== '') {
    $where[] = "b.user_id = " . (int)$user_id;
}

if ($item_id !== '') {
    $where[] = "b.item_id = " . (int)$item_id;
}

$where_sql = '';
if (!empty($where)) {
    $where_sql = 'WHERE ' . implode(' AND ', $where);
}

/* ===== MAIN REPORT QUERY (WITH DEPARTMENT) ===== */
$sql = "
    SELECT 
        u.username,
        u.department,
        i.item_name,
        i.model,
        i.serial_number,
        b.quantity,
        b.borrow_date,
        b.return_date,
        b.status
    FROM borrow_transactions b
    JOIN users u ON u.user_id = b.user_id
    JOIN items i ON i.item_id = b.item_id
    $where_sql
    ORDER BY b.borrow_date DESC
";

$result = mysqli_query($conn, $sql);

/* ===== SAFETY CHECK ===== */
if ($result === false) {
    echo '<div class="alert alert-danger">';
    echo '<strong>SQL Error:</strong> ' . htmlspecialchars(mysqli_error($conn));
    echo '</div>';
    include '../../partials/layout_bottom.php';
    exit;
}

/* ===== SUMMARY ===== */
$total_records  = mysqli_num_rows($result);
$total_quantity = 0;

mysqli_data_seek($result, 0);
while ($r = mysqli_fetch_assoc($result)) {
    $total_quantity += (int)$r['quantity'];
}
mysqli_data_seek($result, 0);
?>

<!-- ================= PAGE HEADER ================= -->
<section class="content-header">
  <h1 class="mb-2">Borrow Report</h1>
  <p class="text-muted mb-0">Summary of borrow activities</p>
</section>

<section class="content">

  <!-- SUMMARY CARDS -->
  <div class="row mb-3">
    <div class="col-md-3">
      <div class="small-box bg-info">
        <div class="inner">
          <h3><?= $total_records ?></h3>
          <p>Total Borrow Records</p>
        </div>
      </div>
    </div>

    <div class="col-md-3">
      <div class="small-box bg-success">
        <div class="inner">
          <h3><?= $total_quantity ?></h3>
          <p>Total Quantity Borrowed</p>
        </div>
      </div>
    </div>
  </div>

  <!-- TABLE -->
  <div class="card">
    <div class="card-body table-responsive">
      <table class="table table-bordered table-striped">
        <thead>
          <tr>
            <th>No</th>
            <th>User</th>
            <th>Department</th>
            <th>Item</th>
            <th>Model</th>
            <th>Serial No.</th>
            <th>Quantity</th>
            <th>Borrow Date</th>
            <th>Return Date</th>
            <th>Status</th>
          </tr>
        </thead>

        <tbody>
        <?php if ($total_records > 0): ?>
          <?php $no = 1; while ($row = mysqli_fetch_assoc($result)): ?>
            <tr>
              <td><?= $no++ ?></td>

              <td><?= htmlspecialchars($row['username']) ?></td>

              <td>
                <?= $row['department']
                    ? htmlspecialchars($row['department'])
                    : '<span class="text-muted">—</span>' ?>
              </td>

              <td><strong><?= htmlspecialchars($row['item_name']) ?></strong></td>

              <td>
                <?= $row['model']
                    ? htmlspecialchars($row['model'])
                    : '<span class="text-muted">—</span>' ?>
              </td>

              <td>
                <?= $row['serial_number']
                    ? htmlspecialchars($row['serial_number'])
                    : '<span class="text-muted">—</span>' ?>
              </td>

              <td><?= (int)$row['quantity'] ?></td>

              <td><?= date('d M Y', strtotime($row['borrow_date'])) ?></td>

              <td>
                <?= $row['return_date']
                    ? date('d M Y', strtotime($row['return_date']))
                    : '-' ?>
              </td>

              <td>
                <span class="badge badge-secondary">
                  <?= htmlspecialchars(ucfirst($row['status'])) ?>
                </span>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="10" class="text-center text-muted">
              No records found
            </td>
          </tr>
        <?php endif; ?>
        </tbody>

      </table>
    </div>
  </div>

</section>

<?php include '../../partials/layout_bottom.php'; ?>
