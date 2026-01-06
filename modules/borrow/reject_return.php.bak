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

/* ===== GET IDENTIFIERS FROM URL ===== */
$user_id    = isset($_GET['user_id']) ? (int) $_GET['user_id'] : 0;
$item_id    = isset($_GET['item_id']) ? (int) $_GET['item_id'] : 0;
$borrow_date = $_GET['borrow_date'] ?? '';

if ($user_id <= 0 || $item_id <= 0 || $borrow_date === '') {
    header("Location: return_requests.php");
    exit;
}

$error = '';

/* =========================================================
   STEP A: HANDLE POST (SAVE RETURN REJECTION)
========================================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $reject_reason = trim($_POST['reject_reason'] ?? '');

    if ($reject_reason === '') {
        $error = "Rejection reason is required.";
    } else {

        $admin_id = (int) $_SESSION['user_id'];

        $stmt = $conn->prepare("
            UPDATE borrow_transactions
            SET status = 'return_rejected',
                reject_reason = ?,
                rejected_by = ?,
                rejected_at = NOW()
            WHERE user_id = ?
              AND item_id = ?
              AND borrow_date = ?
              AND status = 'return_requested'
        ");
        $stmt->bind_param(
            "siiis",
            $reject_reason,
            $admin_id,
            $user_id,
            $item_id,
            $borrow_date
        );
        $stmt->execute();
        $stmt->close();

        header("Location: return_requests.php?rejected=1");
        exit;
    }
}

/* =========================================================
   STEP B: FETCH RETURN REQUEST DETAILS (GET)
========================================================= */
$stmt = $conn->prepare("
    SELECT 
        bt.quantity,
        bt.borrow_date,
        u.username AS requester_name,
        i.item_name
    FROM borrow_transactions bt
    JOIN users u ON u.user_id = bt.user_id
    JOIN items i ON i.item_id = bt.item_id
    WHERE bt.user_id = ?
      AND bt.item_id = ?
      AND bt.borrow_date = ?
      AND bt.status = 'return_requested'
    LIMIT 1
");
$stmt->bind_param("iis", $user_id, $item_id, $borrow_date);
$stmt->execute();
$returnRow = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$returnRow) {
    header("Location: return_requests.php");
    exit;
}

/* ===== LAYOUT TOP (ONLY AFTER ALL REDIRECTS) ===== */
include '../../partials/layout_top.php';
?>

<!-- ================= PAGE HEADER ================= -->
<section class="content-header">
  <h1 class="mb-2">Reject Return Request</h1>
  <p class="text-muted mb-0">
    Please provide a reason for rejecting this return request.
  </p>
</section>

<!-- ================= PAGE CONTENT ================= -->
<section class="content">

  <div class="card">
    <div class="card-body" style="max-width: 700px;">

      <div class="mb-3">
        <p><strong>User:</strong> <?= htmlspecialchars($returnRow['requester_name']) ?></p>
        <p><strong>Item:</strong> <?= htmlspecialchars($returnRow['item_name']) ?></p>
        <p><strong>Quantity:</strong> <?= (int)$returnRow['quantity'] ?></p>
        <p><strong>Borrow Date:</strong> <?= $returnRow['borrow_date'] ?></p>
      </div>

      <?php if (!empty($error)): ?>
        <div class="alert alert-danger">
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <form method="POST">

        <div class="form-group">
          <label for="reject_reason"><strong>Admin Rejection Reason</strong></label>
          <textarea
            name="reject_reason"
            id="reject_reason"
            class="form-control"
            rows="4"
            required
            placeholder="Example: Item not returned in good condition / quantity mismatch / inspection required"
          ><?= htmlspecialchars($_POST['reject_reason'] ?? '') ?></textarea>
        </div>

        <div class="mt-3">
          <button type="submit" class="btn btn-danger">
            <i class="fas fa-times"></i> Reject Return
          </button>
          <a href="return_requests.php" class="btn btn-secondary ml-2">
            Cancel
          </a>
        </div>

      </form>

    </div>
  </div>

</section>

<?php include '../../partials/layout_bottom.php'; ?>
