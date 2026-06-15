<?php
session_start();

// Get target page
$target = isset($_GET['target']) ? $_GET['target'] : 'index.php';

// If user not logged in or not patient, redirect to login with redirect param
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
    header("Location: login.php?redirect=$target");
    exit;
}

// User is patient, redirect to target
header("Location: $target");
exit;
?>
