<?php
// =======================================
// SMART DOCTOR SYSTEM — EDIT_APPOINTMENT.PHP (Fixed)
// =======================================
session_start();
include("config/db.php");

// Only patients can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
    header("Location: login.php");
    exit;
}

$patient_id = $_SESSION['user_id'];

// Check if appointment ID is given
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid request: Appointment ID missing.");
}

$appointment_id = intval($_GET['id']);

// Fetch the appointment record (only if belongs to this patient)
$stmt = $conn->prepare("
    SELECT a.*, d.name AS doctor_name, d.specialization 
    FROM appointments a 
    JOIN doctors d ON a.doctor_id = d.id 
    WHERE a.id = ? AND a.patient_id = ?
");
$stmt->bind_param("ii", $appointment_id, $patient_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("❌ Error: Appointment not found or unauthorized access.");
}

$appointment = $result->fetch_assoc();
$message = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['appointment_date'];
    $time = $_POST['appointment_time'];
    $reason = trim($_POST['reason']);
    $status = $_POST['status'] ?? 'pending';

    if (empty($date) || empty($time)) {
        $message = "<div class='msg error'>⚠️ Please fill in all required fields.</div>";
    } else {
        $update = $conn->prepare("
            UPDATE appointments 
            SET appointment_date = ?, appointment_time = ?, reason = ?, status = ? 
            WHERE id = ? AND patient_id = ?
        ");
        $update->bind_param("ssssii", $date, $time, $reason, $status, $appointment_id, $patient_id);

        if ($update->execute()) {
            header("Location: patient_profile.php?updated=1");
            exit;
        } else {
            $message = "<div class='msg error'>❌ Failed to update appointment. Try again later.</div>";
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
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f4f6f9;
            margin: 0;
        }
        nav {
            background: #27ae60;
            padding: 12px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #fff;
        }
        nav ul { list-style: none; display: flex; gap: 20px; margin: 0; padding: 0; }
        nav a { color: #fff; text-decoration: none; font-weight: bold; }
        nav a:hover, nav a.active { text-decoration: underline; }

        .container { max-width: 700px; margin: 40px auto; background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        h2 { color: #2c3e50; margin-bottom: 20px; }
        form { display: flex; flex-direction: column; gap: 15px; }
        label { font-weight: 600; color: #2c3e50; }
        input, textarea, select {
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 15px;
            width: 100%;
        }
        textarea { resize: vertical; }
        button {
            background: #27ae60;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: 0.2s;
        }
        button:hover { background: #219150; }
        .msg {
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 15px;
            text-align: center;
        }
        .msg.success { background: #e9f9ee; color: #27ae60; border: 1px solid #27ae60; }
        .msg.error { background: #fdecea; color: #c0392b; border: 1px solid #e74c3c; }

        .back-link {
            display: inline-block;
            margin-top: 15px;
            text-decoration: none;
            color: #3498db;
            font-weight: bold;
        }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>

<nav>
    <div class="logo">🩺 Smart Doctor</div>
    <ul>
        <li><a href="patient_dashboard.php">Dashboard</a></li>
        <li><a href="book.php">Book Appointment</a></li>
        <li><a href="patient_profile.php">Profile</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</nav>

<div class="container">
    <h2>📝 Edit Appointment</h2>

    <?= $message ?>

    <form method="POST">
        <label>Doctor:</label>
        <input type="text" value="Dr. <?= htmlspecialchars($appointment['doctor_name']) ?> (<?= htmlspecialchars($appointment['specialization']) ?>)" disabled>

        <label for="appointment_date">Date:</label>
        <input type="date" id="appointment_date" name="appointment_date" value="<?= htmlspecialchars($appointment['appointment_date']) ?>" required>

        <label for="appointment_time">Time:</label>
        <input type="time" id="appointment_time" name="appointment_time" value="<?= htmlspecialchars($appointment['appointment_time']) ?>" required>

        <label for="reason">Reason (optional):</label>
        <textarea id="reason" name="reason" rows="3" placeholder="Describe your reason..."><?= htmlspecialchars($appointment['reason']) ?></textarea>

        <!--<label for="status">Status:</label>
        <select name="status" id="status" required>
            <option value="pending" <//?= $appointment['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
            <option value="approved" <//?= $appointment['status'] == 'approved' ? 'selected' : '' ?>>Approved</option>
            <option value="rescheduled" <//?= $appointment['status'] == 'rescheduled' ? 'selected' : '' ?>>Rescheduled</option>
            <option value="canceled" <//?= $appointment['status'] == 'canceled' ? 'selected' : '' ?>>Canceled</option>
        </select>-->

        <button type="submit">💾 Update Appointment</button>
    </form>

    <a href="patient_profile.php" class="back-link">← Back to Profile</a>
</div>

</body>
</html>
