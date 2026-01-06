<?php
session_start();

/* ===== ADMIN CHECK ===== */
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../auth/login.php");
    exit;
}

if (($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../../index.php");
    exit;
}

include '../../database.php';

/* ===== SHOW BACK BUTTON ===== */
$showBackButton = true;

/* ===== LAYOUT TOP ===== */
include '../../partials/layout_top.php';

/* ===== FETCH PENDING BORROW REQUESTS (WITH DEPARTMENT) ===== */
$stmt = $conn->prepare("
    SELECT 
        b.borrow_id,
        b.quantity,
        b.request_reason,
        b.borrow_date,

        i.item_name,
        i.model,
        i.serial_number,
        i.quantity AS available_stock,

        u.username,
        u.department

    FROM borrow_transactions b
    JOIN items i ON b.item_id = i.item_id
    JOIN users u ON b.user_id = u.user_id

    WHERE b.status = 'pending'
    ORDER BY b.borrow_date ASC
");
$stmt->execute();
$result = $stmt->get_result();
?>

<!-- ================= PAGE HEADER ================= -->
<section class="content-header">
  <h1 class="mb-2">Borrow Requests</h1>
  <p class="text-muted mb-0">
    Review and approve or reject staff borrow requests.
  </p>
</section>

<!-- ================= PAGE CONTENT ================= -->
<section class="content">

  <div class="card">
    <div class="card-body table-responsive">

      <table class="table table-bordered table-striped">
        <thead>
          <tr>
            <th>#</th>
            <th>Requester</th>
            <th>Department</th>
            <th>Item</th>
            <th>Model</th>
            <th>Serial No.</th>
            <th>Requested Qty</th>
            <th>Available</th>
            <th>Staff Reason</th>
            <th style="width: 180px;">Action</th>
          </tr>
        </thead>

        <tbody>
        <?php if ($result->num_rows > 0): ?>
          <?php $no = 1; while ($row = $result->fetch_assoc()): ?>
            <tr>

              <td><?= $no++ ?></td>

              <!-- REQUESTER -->
              <td>
                <strong><?= htmlspecialchars($row['username']) ?></strong>
              </td>

              <!-- DEPARTMENT -->
              <td>
                <?php if (!empty($row['department'])): ?>
                  <span class="badge badge-info">
                    <?= htmlspecialchars($row['department']) ?>
                  </span>
                <?php else: ?>
                  <span class="text-muted">Not Set</span>
                <?php endif; ?>
              </td>

              <!-- ITEM -->
              <td><?= htmlspecialchars($row['item_name']) ?></td>

              <!-- MODEL -->
              <td>
                <?= $row['model']
                    ? htmlspecialchars($row['model'])
                    : '<span class="text-muted">—</span>' ?>
              </td>

              <!-- SERIAL NUMBER -->
              <td>
                <?= $row['serial_number']
                    ? htmlspecialchars($row['serial_number'])
                    : '<span class="text-muted">—</span>' ?>
              </td>

              <!-- REQUESTED QTY -->
              <td><?= (int) $row['quantity'] ?></td>

              <!-- AVAILABLE STOCK -->
              <td>
                <span class="badge badge-info">
                  <?= (int) $row['available_stock'] ?>
                </span>
              </td>

              <!-- STAFF REASON -->
              <td style="max-width: 280px;">
                <?= nl2br(htmlspecialchars($row['request_reason'])) ?>
              </td>

              <!-- ACTION -->
              <td>

                <!-- APPROVE -->
                <a href="/inventory_system/modules/borrow/approve_borrow.php?id=<?= $row['borrow_id'] ?>"
                   class="btn btn-sm btn-success mb-1"
                   onclick="return confirm('Approve this borrow request?');">
                  <i class="fas fa-check"></i> Approve
                </a>

                <!-- REJECT -->
                <a href="/inventory_system/modules/borrow/reject_borrow.php?id=<?= $row['borrow_id'] ?>"
                   class="btn btn-sm btn-danger">
                  <i class="fas fa-times"></i> Reject
                </a>

              </td>

            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="10" class="text-center text-muted">
              No pending borrow requests
            </td>
          </tr>
        <?php endif; ?>
        </tbody>

      </table>

    </div>
  </div>

</section>

<?php
$stmt->close();

/* ===== LAYOUT BOTTOM ===== */
include '../../partials/layout_bottom.php';
