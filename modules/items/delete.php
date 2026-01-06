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

$id = $_GET['id'];

mysqli_query($conn, "DELETE FROM items WHERE item_id=$id");

header("Location: list.php");
exit;
