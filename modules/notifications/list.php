<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../auth/login.php");
    exit;
}

include '../../database.php';

$showBackButton = true;
include '../../partials/layout_top.php';

$user_id = (int)$_SESSION['user_id'];

/* Try read notifications table (if exists) */
$notifications = [];
$hasTable = true;

$q = $conn->query("SHOW TABLES LIKE 'notifications'");
if (!$q || $q->num_rows === 0) {
    $hasTable = false;
} else {
    $stmt = $conn->prepare("
        SELECT notification_id, title, message, is_read, created_at
        FROM notifications
        WHERE user_id = ?
        ORDER BY created_at DESC
        LIMIT 50
    ");
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $notifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
}
?>

<section class="content-header">
  <h1 class="mb-1">Notifications</h1>
  <p class="text-muted mb-0">Latest updates about your borrow requests</p>
</section>

<section class="content">
  <div class="card">
    <div class="card-body">

      <?php if (!$hasTable): ?>
        <div class="alert alert-secondary mb-0">
          <i class="fas fa-bell mr-1"></i>
          Notifications system is not enabled yet (no <code>notifications</code> table found).
        </div>

      <?php elseif (empty($notifications)): ?>
        <div class="alert alert-info mb-0">
          <i class="fas fa-info-circle mr-1"></i>
          No notifications yet.
        </div>

      <?php else: ?>
        <div class="list-group">
          <?php foreach ($notifications as $n): ?>
            <div class="list-group-item">
              <div class="d-flex justify-content-between">
                <div>
                  <div class="font-weight-bold">
                    <?= htmlspecialchars($n['title'] ?? 'Notification') ?>
                    <?php if ((int)($n['is_read'] ?? 0) === 0): ?>
                      <span class="badge badge-warning ml-2">New</span>
                    <?php endif; ?>
                  </div>
                  <div class="text-muted">
                    <?= nl2br(htmlspecialchars($n['message'] ?? '')) ?>
                  </div>
                </div>
                <small class="text-muted">
                  <?= htmlspecialchars($n['created_at'] ?? '') ?>
                </small>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

    </div>
  </div>
</section>

<?php include '../../partials/layout_bottom.php'; ?>
