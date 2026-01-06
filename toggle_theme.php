<?php
session_start();

/* Toggle theme */
$current = $_SESSION['theme'] ?? 'light';
$_SESSION['theme'] = ($current === 'dark') ? 'light' : 'dark';

/* Redirect back */
$redirect = $_SERVER['HTTP_REFERER'] ?? '/inventory_system/index.php';
header("Location: $redirect");
exit;
