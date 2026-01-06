<?php
// sidebar.php
// REQUIRE: session_start() already called
// OPTIONAL: $showBackButton
// OPTIONAL: $conn

$username = $_SESSION['username'] ?? 'User';
$role     = $_SESSION['role'] ?? 'staff';

/* ===== THEME HANDLING ===== */
$theme  = $_SESSION['theme'] ?? 'light';
$isDark = ($theme === 'dark');

/*
 AdminLTE sidebar classes:
 - Dark mode  → sidebar-dark-primary
 - Light mode → sidebar-light-primary
*/
$sidebarClass = $isDark ? 'sidebar-dark-primary' : 'sidebar-light-primary';

/* ===== BADGE COUNTERS (ADMIN ONLY) ===== */
$pending_borrow_requests = 0;
$pending_return_requests = 0;
$pending_user_requests   = 0;

if ($role === 'admin' && isset($conn)) {

    $row = mysqli_fetch_assoc(mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total FROM borrow_transactions WHERE status='pending'"
    ));
    $pending_borrow_requests = (int)($row['total'] ?? 0);

    $row = mysqli_fetch_assoc(mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total FROM borrow_transactions WHERE status='return_requested'"
    ));
    $pending_return_requests = (int)($row['total'] ?? 0);

    $row = mysqli_fetch_assoc(mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total FROM users WHERE status='pending'"
    ));
    $pending_user_requests = (int)($row['total'] ?? 0);
}
?>

<!-- ================= SIDEBAR ================= -->
<aside class="main-sidebar <?= $sidebarClass ?> elevation-4">

  <!-- BRAND -->
  <a href="/inventory_system/index.php"
     class="brand-link <?= $isDark ? 'text-white' : 'text-dark' ?>">
    <span class="brand-text font-weight-semibold">
      Inventory System
    </span>
  </a>

  <div class="sidebar">

    <!-- USER PANEL -->
    <div class="user-panel mt-3 pb-3 mb-3 d-flex align-items-center">
      <div class="image">
        <i class="fas fa-user-circle fa-2x <?= $isDark ? 'text-secondary' : 'text-primary' ?>"></i>
      </div>
      <div class="info">
        <span class="d-block font-weight-bold <?= $isDark ? 'text-white' : 'text-dark' ?>">
          <?= htmlspecialchars($username) ?>
        </span>
        <small class="<?= $isDark ? 'text-muted' : 'text-secondary' ?>">
          <?= ucfirst(htmlspecialchars($role)) ?>
        </small>
      </div>
    </div>

    <!-- BACK BUTTON -->
    <?php if (!empty($showBackButton)): ?>
      <div class="px-3 mb-2">
        <a href="javascript:history.back()"
           class="btn btn-block btn-sm <?= $isDark ? 'btn-secondary' : 'btn-outline-secondary' ?>">
          <i class="fas fa-arrow-left"></i> Back
        </a>
      </div>
    <?php endif; ?>

    <!-- MENU -->
    <nav class="mt-2">
      <ul class="nav nav-pills nav-sidebar flex-column"
          data-widget="treeview"
          role="menu"
          data-accordion="false">

        <!-- DASHBOARD -->
        <li class="nav-item">
          <a href="/inventory_system/index.php" class="nav-link">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>Dashboard</p>
          </a>
        </li>

        <!-- ================= BORROW ================= -->
        <li class="nav-item has-treeview">
          <a href="#" class="nav-link">
            <i class="nav-icon fas fa-hand-holding"></i>
            <p>
              Borrow
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>

          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="/inventory_system/modules/borrow/add.php" class="nav-link">
                <p>Borrow Item</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="/inventory_system/modules/borrow/list.php" class="nav-link">
                <p>My Borrowed Items</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="/inventory_system/modules/borrow/history.php" class="nav-link">
                <p>Borrow History</p>
              </a>
            </li>
          </ul>
        </li>

        <!-- ================= ADMIN ================= -->
        <?php if ($role === 'admin'): ?>

          <!-- USERS -->
          <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-users"></i>
              <p>
                Users
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="/inventory_system/modules/users/add.php" class="nav-link">
                  <p>Add User</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="/inventory_system/modules/users/list.php" class="nav-link">
                  <p>
                    User Requests
                    <?php if ($pending_user_requests > 0): ?>
                      <span class="right badge bg-navy"><?= $pending_user_requests ?></span>
                    <?php endif; ?>
                  </p>
                </a>
              </li>
            </ul>
          </li>

          <!-- INVENTORY -->
          <li class="nav-item has-treeview">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-box"></i>
              <p>
                Inventory
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="/inventory_system/modules/items/add.php" class="nav-link">
                  <p>Add Item</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="/inventory_system/modules/items/list.php" class="nav-link">
                  <p>Item List</p>
                </a>
              </li>
            </ul>
          </li>

          <!-- BORROW REQUESTS -->
          <li class="nav-item">
            <a href="/inventory_system/modules/borrow/borrow_requests.php" class="nav-link">
              <i class="nav-icon fas fa-inbox"></i>
              <p>
                Borrow Requests
                <?php if ($pending_borrow_requests > 0): ?>
                  <span class="right badge bg-orange"><?= $pending_borrow_requests ?></span>
                <?php endif; ?>
              </p>
            </a>
          </li>

          <!-- RETURN REQUESTS -->
          <li class="nav-item">
            <a href="/inventory_system/modules/borrow/return_requests.php" class="nav-link">
              <i class="nav-icon fas fa-undo"></i>
              <p>
                Return Requests
                <?php if ($pending_return_requests > 0): ?>
                  <span class="right badge bg-maroon"><?= $pending_return_requests ?></span>
                <?php endif; ?>
              </p>
            </a>
          </li>

          <!-- REPORTS -->
          <li class="nav-item">
            <a href="/inventory_system/modules/reports/borrow_report.php" class="nav-link">
              <i class="nav-icon fas fa-file-alt"></i>
              <p>Reports</p>
            </a>
          </li>

          <!-- ACTIVITY LOGS -->
          <li class="nav-item">
            <a href="/inventory_system/modules/logs/list.php" class="nav-link">
              <i class="nav-icon fas fa-clipboard-list"></i>
              <p>Activity Logs</p>
            </a>
          </li>

        <?php endif; ?>

        <!-- LOGOUT -->
        <li class="nav-item">
          <a href="/inventory_system/auth/logout.php" class="nav-link">
            <i class="nav-icon fas fa-sign-out-alt"></i>
            <p>Logout</p>
          </a>
        </li>

      </ul>
    </nav>
  </div>
</aside>
