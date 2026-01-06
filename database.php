<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "inventory_system";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
	
	// ===== SYSTEM SETTINGS =====
	define('ADMIN_EMAIL', 'hedzelshaqir@gmail.com');

}
?>
