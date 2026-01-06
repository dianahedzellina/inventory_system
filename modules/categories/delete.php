<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../auth/login.php");
    exit;
}

include '../../database.php';

if (!isset($_GET['id'])) {
    header("Location: list.php");
    exit;
}

$id = (int) $_GET['id'];

// Check if category is used by any item
$check = mysqli_query($conn, "
    SELECT COUNT(*) AS total 
    FROM items 
    WHERE category_id = $id
");
$row = mysqli_fetch_assoc($check);

if ($row['total'] > 0) {
    // Category in use — do NOT delete
    header("Location: list.php?error=used");
    exit;
}

// Safe to delete
mysqli_query($conn, "DELETE FROM categories WHERE category_id = $id");

header("Location: list.php");
exit;
