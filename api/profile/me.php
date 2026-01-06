<?php
header("Content-Type: application/json");
include '../../database.php';

$user_id = (int)($_GET['user_id'] ?? 0);

if ($user_id <= 0) {
    http_response_code(400);
    echo json_encode(["error" => "Missing or invalid user_id"]);
    exit;
}

/* SAFELY PREPARE */
$sql = "
    SELECT user_id, username, role, status
    FROM users
    WHERE user_id = ?
    LIMIT 1
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    http_response_code(500);
    echo json_encode([
        "error" => "SQL prepare failed",
        "details" => $conn->error
    ]);
    exit;
}

$stmt->bind_param("i", $user_id);
$stmt->execute();

$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    http_response_code(404);
    echo json_encode(["error" => "User not found"]);
    exit;
}

echo json_encode($user);
