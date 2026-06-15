<?php
// =======================================
// SMART DOCTOR SYSTEM — SUBMIT_REVIEW.PHP
// =======================================
session_start();
include("config/db.php");

// Ensure only patients can submit reviews
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
    header("Location: login.php");
    exit;
}

$patient_id = $_SESSION['user_id'];

// Check POST data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appointment_id = intval($_POST['appointment_id']);
    $rating = intval($_POST['rating']);
    $feedback = trim($_POST['feedback']);

    // ✅ Validate rating
    if ($rating < 1 || $rating > 5) {
        die("Invalid rating value.");
    }

    // ✅ Check if appointment exists and belongs to this patient
    $check = $conn->prepare("
        SELECT id FROM appointments 
        WHERE id = ? AND patient_id = ?
    ");
    $check->bind_param("ii", $appointment_id, $patient_id);
    $check->execute();
    $res = $check->get_result();

    if ($res->num_rows === 0) {
        die("Error: Invalid appointment or access denied.");
    }

    // ✅ Check if review already exists for this appointment
    $checkReview = $conn->prepare("SELECT id FROM reviews WHERE appointment_id = ?");
    $checkReview->bind_param("i", $appointment_id);
    $checkReview->execute();
    $existing = $checkReview->get_result();

    if ($existing->num_rows > 0) {
        die("You have already submitted a review for this appointment.");
    }

    // ✅ Insert review (no deleting appointment — this fixes your foreign key crash)
    $stmt = $conn->prepare("
        INSERT INTO reviews (appointment_id, patient_id, rating, feedback, created_at)
        VALUES (?, ?, ?, ?, NOW())
    ");
    $stmt->bind_param("iiis", $appointment_id, $patient_id, $rating, $feedback);

    if ($stmt->execute()) {
        echo "<script>alert('✅ Review submitted successfully!'); window.location.href='patient_profile.php';</script>";
    } else {
        echo "❌ Error saving review: " . $conn->error;
    }

    $stmt->close();
}
?>
