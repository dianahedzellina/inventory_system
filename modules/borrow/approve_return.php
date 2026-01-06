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

/* ===== GET IDENTIFIERS FROM URL ===== */
$user_id     = isset($_GET['user_id']) ? (int) $_GET['user_id'] : 0;
$item_id     = isset($_GET['item_id']) ? (int) $_GET['item_id'] : 0;
$borrow_date = $_GET['borrow_date'] ?? '';

if ($user_id <= 0 || $item_id <= 0 || $borrow_date === '') {
    header("Location: return_requests.php");
    exit;
}

/* =========================================================
   STEP 1: FETCH BORROW RECORD (MUST BE return_requested)
========================================================= */
$stmt = $conn->prepare("
    SELECT quantity
    FROM borrow_transactions
    WHERE user_id = ?
      AND item_id = ?
      AND borrow_date = ?
      AND status = 'return_requested'
    LIMIT 1
");
$stmt->bind_param("iis", $user_id, $item_id, $borrow_date);
$stmt->execute();
$borrow = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$borrow) {
    header("Location: return_requests.php");
    exit;
}

$qty = (int) $borrow['quantity'];

/* =========================================================
   STEP 2: UPDATE BORROW STATUS → returned
========================================================= */
$stmt = $conn->prepare("
    UPDATE borrow_transactions
    SET status = 'returned',
        return_date = NOW()
    WHERE user_id = ?
      AND item_id = ?
      AND borrow_date = ?
      AND status = 'return_requested'
");
$stmt->bind_param("iis", $user_id, $item_id, $borrow_date);
$stmt->execute();
$stmt->close();

/* =========================================================
   STEP 3: RESTORE ITEM STOCK
========================================================= */
$stmt = $conn->prepare("
    UPDATE items
    SET quantity = quantity + ?
    WHERE item_id = ?
");
$stmt->bind_param("ii", $qty, $item_id);
$stmt->execute();
$stmt->close();

/* =========================================================
   STEP 4: ACTIVITY LOG
========================================================= */
$admin_id = (int) $_SESSION['user_id'];
$action = "Approved return request (User ID: $user_id, Item ID: $item_id, Borrowed at: $borrow_date)";

$stmt = $conn->prepare("
    INSERT INTO activity_logs (user_id, action)
    VALUES (?, ?)
");
$stmt->bind_param("is", $admin_id, $action);
$stmt->execute();
$stmt->close();

/* ===== REDIRECT ===== */
header("Location: return_requests.php?approved=1");
exit;
