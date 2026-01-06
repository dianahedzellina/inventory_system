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

/* ===== GET BORROW ID ===== */
$borrow_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($borrow_id <= 0) {
    header("Location: borrow_requests.php");
    exit;
}

/* ===== GET BORROW REQUEST (MUST BE PENDING) ===== */
$stmt = $conn->prepare("
    SELECT b.item_id, b.quantity, u.username
    FROM borrow_transactions b
    JOIN users u ON b.user_id = u.user_id
    WHERE b.borrow_id = ? AND b.status = 'pending'
    LIMIT 1
");
$stmt->bind_param("i", $borrow_id);
$stmt->execute();
$result = $stmt->get_result();
$borrow = $result->fetch_assoc();
$stmt->close();

if (!$borrow) {
    // Already approved / rejected / invalid
    header("Location: borrow_requests.php");
    exit;
}

$item_id   = (int) $borrow['item_id'];
$qty       = (int) $borrow['quantity'];
$requester = $borrow['username']; // ✅ borrower name

/* ===== CHECK ITEM STOCK ===== */
$stmt = $conn->prepare("
    SELECT quantity
    FROM items
    WHERE item_id = ?
    LIMIT 1
");
$stmt->bind_param("i", $item_id);
$stmt->execute();
$result = $stmt->get_result();
$item = $result->fetch_assoc();
$stmt->close();

if (!$item || $item['quantity'] < $qty) {
    // Not enough stock
    header("Location: borrow_requests.php?error=stock");
    exit;
}

/* ===== APPROVE BORROW (STATUS -> borrowed) ===== */
$stmt = $conn->prepare("
    UPDATE borrow_transactions
    SET status = 'borrowed'
    WHERE borrow_id = ?
");
$stmt->bind_param("i", $borrow_id);
$stmt->execute();
$stmt->close();

/* ===== REDUCE ITEM STOCK ===== */
$stmt = $conn->prepare("
    UPDATE items
    SET quantity = quantity - ?
    WHERE item_id = ?
");
$stmt->bind_param("ii", $qty, $item_id);
$stmt->execute();
$stmt->close();

/* ===== ACTIVITY LOG (UPDATED) ===== */
$admin_id = (int) $_SESSION['user_id'];
$action = "Approved borrow request (Requester: {$requester})";

$stmt = $conn->prepare("
    INSERT INTO activity_logs (user_id, action)
    VALUES (?, ?)
");
$stmt->bind_param("is", $admin_id, $action);
$stmt->execute();
$stmt->close();

/* ===== REDIRECT BACK ===== */
header("Location: borrow_requests.php?approved=1");
exit;
