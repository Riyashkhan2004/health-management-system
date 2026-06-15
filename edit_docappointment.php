<?php
session_start();
include("config/db.php");

// Only doctors can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'doctor') {
    header("Location: logdoctor.php");
    exit;
}

$doctor_id = $_SESSION['user_id'];
$error = "";
$msg = "";

// Get appointment ID
if (!isset($_GET['id'])) {
    header("Location: appointment.php");
    exit;
}

$appointment_id = intval($_GET['id']);

// Fetch appointment details
$stmt = $conn->prepare("SELECT a.*, u.name AS patient_name FROM appointments a LEFT JOIN users u ON a.patient_id = u.id WHERE a.id = ? AND a.doctor_id = ?");
$stmt->bind_param("ii", $appointment_id, $doctor_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Appointment not found or you don't have permission to edit it.");
}

$appointment = $result->fetch_assoc();

// Handle form submission
if (isset($_POST['update'])) {
    $new_time = $_POST['appointment_time'];
    $new_date = $_POST['appointment_date'];
    $doctor_message = $conn->real_escape_string($_POST['doctor_message']);

    // Validate time
    if ($new_time < '10:00' || $new_time > '23:00') {
        $error = "⏰ Doctor is available only between 10:00 AM and 11:00 PM.";
    } else {
        // Check if doctor is busy at that time
        $check = $conn->prepare("SELECT * FROM appointments WHERE doctor_id=? AND appointment_date=? AND appointment_time=? AND id != ?");
        $check->bind_param("issi", $doctor_id, $new_date, $new_time, $appointment_id);
        $check->execute();
        $check_result = $check->get_result();

        if ($check_result->num_rows > 0) {
            $error = "⚠️ You already have another appointment at this time.";
        } else {
            // Update appointment with message
            $update = $conn->prepare("UPDATE appointments SET appointment_date=?, appointment_time=?, doctor_message=? WHERE id=?");
            $update->bind_param("sssi", $new_date, $new_time, $doctor_message, $appointment_id);

            if ($update->execute()) {
                $msg = "✅ Appointment updated successfully. Patient will see your message when they login.";
            } else {
                $error = "❌ Failed to update appointment. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Appointment | Smart Doctor</title>
<link rel="stylesheet" href="assets/css/style.css">
<style>
body { font-family: Arial, sans-serif; background:#f9f9f9; margin:0; padding:0; }
.container { max-width:600px; margin:50px auto; }
.card { background:#fff; padding:30px; border-radius:12px; box-shadow:0 6px 20px rgba(0,0,0,0.1); }
h2 { margin-bottom:20px; color:#2c3e50; }
form input, form textarea { width:100%; padding:12px; margin-bottom:15px; border-radius:8px; border:1px solid #ccc; }
form button { padding:12px 25px; border:none; border-radius:8px; background:#27ae60; color:#fff; font-weight:600; cursor:pointer; }
form button:hover { background:#2ecc71; }
.success { color:green; margin-bottom:15px; }
.error { color:red; margin-bottom:15px; }
.navbar { display:flex; justify-content:space-between; align-items:center; background:#27ae60; padding:10px 20px; color:#fff; border-radius:8px; }
.navbar a { color:#fff; text-decoration:none; margin-left:15px; font-weight:600; }
.navbar a:hover { text-decoration:underline; }
</style>
</head>
<body>

<nav class="navbar">
    <div class="logo">🩺 Smart Doctor</div>
    <div>
        <a href="doctor_dashboard.php">Dashboard</a>
        <a href="appointment.php">Appointments</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>

<div class="container">
    <div class="card">
        <h2>Edit Appointment</h2>

        <?php if ($error) echo "<div class='error'>$error</div>"; ?>
        <?php if ($msg) echo "<div class='success'>$msg</div>"; ?>

        <form method="POST">
            <label>Patient Name</label>
            <input type="text" value="<?= htmlspecialchars($appointment['patient_name']) ?>" disabled>

            <label>Appointment Date</label>
            <input type="date" name="appointment_date" value="<?= htmlspecialchars($appointment['appointment_date']) ?>" required min="<?= date('Y-m-d') ?>">

            <label>Appointment Time</label>
            <input type="time" name="appointment_time" value="<?= htmlspecialchars($appointment['appointment_time']) ?>" required min="10:00" max="23:00">

            <label>Message to Patient</label>
            <textarea name="doctor_message" rows="4" placeholder="Write a message for the patient (e.g., new time, instructions)"><?= htmlspecialchars($appointment['doctor_message'] ?? '') ?></textarea>

            <button type="submit" name="update">Update Appointment</button>
        </form>
    </div>
</div>

</body>
</html>
