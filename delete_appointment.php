<?php
// =======================================
// SMART DOCTOR SYSTEM — DELETE_APPOINTMENT.PHP
// =======================================
session_start();
include("config/db.php");

// Only allow logged-in patients
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
    header("Location: login.php");
    exit;
}

$patient_id = $_SESSION['user_id'];

// Validate appointment ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: patient_profile.php?error=invalid_id");
    exit;
}

$appointment_id = intval($_GET['id']);

// Check if appointment belongs to the logged-in patient
$stmt = $conn->prepare("SELECT id FROM appointments WHERE id = ? AND patient_id = ?");
$stmt->bind_param("ii", $appointment_id, $patient_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Appointment doesn't belong to this user or doesn't exist
    header("Location: patient_profile.php?error=unauthorized");
    exit;
}

// Proceed to delete
$delete_stmt = $conn->prepare("DELETE FROM appointments WHERE id = ? AND patient_id = ?");
$delete_stmt->bind_param("ii", $appointment_id, $patient_id);

if ($delete_stmt->execute()) {
    header("Location: patient_profile.php?success=appointment_deleted");
    exit;
} else {
    header("Location: patient_profile.php?error=delete_failed");
    exit;
}
?>
