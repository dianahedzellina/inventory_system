<?php
header('Content-Type: application/json');
require_once '../../database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Username and password are required'
    ]);
    exit;
}

$stmt = $conn->prepare(
    "SELECT user_id, username, password, role, status
     FROM users
     WHERE username = ?
     LIMIT 1"
);

$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user || !password_verify($password, $user['password'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid username or password'
    ]);
    exit;
}

if ($user['status'] !== 'active') {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Account not active'
    ]);
    exit;
}

echo json_encode([
    'success' => true,
    'user' => [
        'id'       => (int)$user['user_id'],
        'username' => $user['username'],
        'role'     => $user['role']
    ]
]);
