<?php
header("Content-Type: application/json");
include '../../database.php';

$user_id = (int)($_GET['user_id'] ?? 0);
if ($user_id <= 0) {
    http_response_code(400);
    echo json_encode(["error" => "Missing user_id"]);
    exit;
}

$stmt = $conn->prepare("
    SELECT b.borrow_id, b.borrow_date, b.return_date, b.status,
           i.item_name, i.model, i.serial_number
    FROM borrow_transactions b
    JOIN items i ON i.item_id = b.item_id
    WHERE b.user_id = ?
    ORDER BY b.borrow_date DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();

$out = [];
while ($r = $res->fetch_assoc()) $out[] = $r;

echo json_encode($out);
