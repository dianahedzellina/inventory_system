<?php
session_start();

/* ===== LOGIN CHECK ===== */
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../auth/login.php");
    exit;
}

include '../../database.php';

$user_id = (int) $_SESSION['user_id'];

/* =====================================================
   HANDLE ACKNOWLEDGE REJECTED
===================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ack_rejected'])) {

    $borrow_id = (int)($_POST['borrow_id'] ?? 0);

    if ($borrow_id > 0) {
        $stmt = $conn->prepare("
            UPDATE borrow_transactions
            SET status = 'rejected_ack'
            WHERE borrow_id = ?
              AND user_id = ?
              AND status = 'rejected'
            LIMIT 1
        ");
        $stmt->bind_param("ii", $borrow_id, $user_id);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: list.php");
    exit;
}

/* =====================================================
   HANDLE REQUEST RETURN SUBMIT (WITH HANDOVER)
===================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_return'])) {

    $borrow_id   = (int) ($_POST['borrow_id'] ?? 0);
    $reason      = trim($_POST['reason'] ?? '');
    $handover_to = ($_POST['handover_to'] !== '') ? (int)$_POST['handover_to'] : null;

    if ($borrow_id > 0 && $reason !== '') {

        $stmt = $conn->prepare("
            UPDATE borrow_transactions
            SET status = 'return_requested',
                request_reason = ?,
                handover_to = ?
            WHERE borrow_id = ?
              AND user_id = ?
              AND status IN ('borrowed','return_rejected')
            LIMIT 1
        ");
        $stmt->bind_param("siii", $reason, $handover_to, $borrow_id, $user_id);
        $stmt->execute();
        $stmt->close();

        header("Location: list.php?requested=1");
        exit;
    }

    header("Location: list.php?error=1");
    exit;
}

/* ===== SHOW BACK BUTTON ===== */
$showBackButton = true;

/* ===== LAYOUT ===== */
include '../../partials/layout_top.php';

/* ===== FETCH BORROW RECORDS ===== */
$stmt = $conn->prepare("
    SELECT 
        b.borrow_id,
        b.quantity,
        b.borrow_date,
        b.return_date,
        b.status,
        i.item_id,
        i.item_name,
        i.model,
        i.serial_number,
        u.username
    FROM borrow_transactions b
    JOIN items i ON b.item_id = i.item_id
    JOIN users u ON b.user_id = u.user_id
    WHERE b.user_id = ?
    ORDER BY b.borrow_date DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

/* ===== FETCH STAFF FOR HANDOVER ===== */
$staffs = mysqli_query($conn, "
    SELECT user_id, username
    FROM users
    WHERE user_id != $user_id
    ORDER BY username ASC
");
?>

<section class="content-header">
  <h1 class="mb-2">My Borrowed Items</h1>
  <p class="text-muted mb-0">Track your borrow and return items.</p>
</section>

<section class="content">

<div class="card">
<div class="card-body table-responsive">

<table class="table table-bordered table-striped align-middle">
<thead>
<tr>
  <th>No</th>
  <th>Requester</th>
  <th>Item</th>
  <th>Model</th>
  <th>Serial No</th>
  <th>Qty</th>
  <th>Borrow Date</th>
  <th>Return Date</th>
  <th>Status</th>
  <th style="width:200px;">Action</th>
</tr>
</thead>

<tbody>
<?php if ($result->num_rows > 0): ?>
<?php $no=1; while ($row=$result->fetch_assoc()): ?>
<tr>

<td><?= $no++ ?></td>
<td><?= htmlspecialchars($row['username']) ?></td>
<td><?= htmlspecialchars($row['item_name']) ?></td>
<td><?= $row['model'] ?: '-' ?></td>
<td><?= $row['serial_number'] ?: '-' ?></td>
<td><?= (int)$row['quantity'] ?></td>
<td><?= date('d M Y', strtotime($row['borrow_date'])) ?></td>
<td><?= $row['return_date'] ? date('d M Y', strtotime($row['return_date'])) : '-' ?></td>

<td>
<?php
switch ($row['status']) {
    case 'pending':
        echo '<span class="badge badge-secondary">Pending Approval</span>';
        break;

    case 'borrowed':
        echo '<span class="badge badge-warning text-dark">Borrowed</span>';
        break;

    case 'return_requested':
        echo '<span class="badge badge-info text-dark">Return Requested</span>';
        break;

    case 'returned':
        echo '<span class="badge badge-success">Returned</span>';
        break;

    case 'rejected':
        echo '<span class="badge badge-danger">Rejected</span>';
        break;

    default:
        echo '<span class="badge badge-light">—</span>';
}
?>
</td>

<td>

<?php if (in_array($row['status'], ['borrowed','return_rejected'])): ?>

<button class="btn btn-sm btn-outline-warning btn-block"
        data-toggle="modal"
        data-target="#returnModal<?= $row['borrow_id'] ?>">
<i class="fas fa-undo"></i> Request Return
</button>

<?php elseif ($row['status'] === 'rejected'): ?>

<a href="add.php?item_id=<?= $row['item_id'] ?>"
   class="btn btn-sm btn-outline-primary btn-block mb-1">
<i class="fas fa-redo"></i> Re-request Borrow
</a>

<form method="post">
<input type="hidden" name="borrow_id" value="<?= $row['borrow_id'] ?>">
<button type="submit" name="ack_rejected"
        class="btn btn-sm btn-outline-secondary btn-block">
<i class="fas fa-check"></i> Okay
</button>
</form>

<?php else: ?>
<button class="btn btn-sm btn-light btn-block" disabled>—</button>
<?php endif; ?>

<?php if (in_array($row['status'], ['borrowed','return_rejected'])): ?>
<div class="modal fade" id="returnModal<?= $row['borrow_id'] ?>" tabindex="-1">
<div class="modal-dialog">
<div class="modal-content">

<form method="post">
<input type="hidden" name="borrow_id" value="<?= $row['borrow_id'] ?>">

<div class="modal-header">
<h5 class="modal-title">Request Return</h5>
<button type="button" class="close" data-dismiss="modal">&times;</button>
</div>

<div class="modal-body">

<div class="form-group">
<label>Reason for return *</label>
<textarea name="reason" class="form-control" required></textarea>
</div>

<div class="form-group">
<label>Handover to other staff (optional)</label>
<select name="handover_to" class="form-control">
<option value="">— No handover —</option>
<?php mysqli_data_seek($staffs, 0); ?>
<?php while ($s = mysqli_fetch_assoc($staffs)): ?>
<option value="<?= $s['user_id'] ?>"><?= htmlspecialchars($s['username']) ?></option>
<?php endwhile; ?>
</select>
</div>

</div>

<div class="modal-footer">
<button type="submit" name="request_return" class="btn btn-primary">
Submit Request
</button>
</div>

</form>

</div>
</div>
</div>
<?php endif; ?>

</td>

</tr>
<?php endwhile; else: ?>
<tr>
<td colspan="10" class="text-center text-muted">No borrow records found</td>
</tr>
<?php endif; ?>
</tbody>

</table>
</div>
</div>

</section>

<?php
$stmt->close();
include '../../partials/layout_bottom.php';
?>
