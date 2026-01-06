<?php
session_start();

/* ===== LOGIN CHECK ===== */
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../auth/login.php");
    exit;
}

include '../../database.php';

/* ===== USER ===== */
$user_id = (int) $_SESSION['user_id'];

/* ===== GET BORROW ID (POST OR GET) ===== */
$borrow_id = 0;

if (isset($_POST['id'])) {
    $borrow_id = (int) $_POST['id'];
} elseif (isset($_GET['id'])) {
    $borrow_id = (int) $_GET['id'];
}

/* ===== BASIC VALIDATION ===== */
if ($borrow_id <= 0) {
    header("Location: list.php?error=invalid_id");
    exit;
}

/* =========================================================
   STEP 1: VERIFY BORROW RECORD BELONGS TO USER
           AND STATUS IS ALLOWED
========================================================= */
$stmt = $conn->prepare("
    SELECT item_id, status, borrow_date
    FROM borrow_transactions
    WHERE borrow_id = ?
      AND user_id = ?
      AND status IN ('borrowed', 'return_rejected')
    LIMIT 1
");
$stmt->bind_param("ii", $borrow_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

/* ===== IF NOT VALID, BLOCK ===== */
if ($result->num_rows === 0) {
    header("Location: list.php?error=not_allowed");
    exit;
}

$row = $result->fetch_assoc();

/* =========================================================
   STEP 2: UPDATE STATUS → return_requested
========================================================= */
$upd = $conn->prepare("
    UPDATE borrow_transactions
    SET status = 'return_requested'
    WHERE borrow_id = ?
      AND user_id = ?
      AND status IN ('borrowed', 'return_rejected')
    LIMIT 1
");
$upd->bind_param("ii", $borrow_id, $user_id);
$upd->execute();

/* ===== SAFETY CHECK ===== */
if ($upd->affected_rows <= 0) {
    $upd->close();
    header("Location: list.php?error=update_failed");
    exit;
}

$upd->close();

/* =========================================================
   STEP 3: ACTIVITY LOG (KEEPING YOUR LOGIC)
========================================================= */
$action = sprintf(
    "User requested return (Borrow ID: %d, Item ID: %d)",
    $borrow_id,
    $row['item_id']
);

$log = $conn->prepare("
    INSERT INTO activity_logs (user_id, action)
    VALUES (?, ?)
");
$log->bind_param("is", $user_id, $action);
$log->execute();
$log->close();

/* ===== REDIRECT BACK ===== */
header("Location: list.php?requested=1");
exit;
