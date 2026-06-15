<?php
// =======================================
// SMART DOCTOR SYSTEM — DOCTOR_DASHBOARD.PHP
// =======================================
session_start();
include("config/db.php");

// Only doctors can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'doctor') {
    header("Location: logdoctor.php");
    exit;
}

$doctor_id = $_SESSION['user_id'];

// Fetch doctor's profile info
$doctor = $conn->query("SELECT * FROM doctors WHERE id=$doctor_id")->fetch_assoc();

// Fetch today's appointments
$today = date('Y-m-d');
$appointments = $conn->query("
    SELECT a.*, p.name AS patient_name 
    FROM appointments a 
    JOIN users p ON a.patient_id = p.id 
    WHERE a.doctor_id=$doctor_id AND a.appointment_date='$today' 
    ORDER BY a.appointment_time ASC
");


// Fetch statistics
$totalPending = $conn->query("SELECT COUNT(*) as cnt FROM appointments WHERE doctor_id=$doctor_id AND status='Pending'")->fetch_assoc()['cnt'];
$totalApproved = $conn->query("SELECT COUNT(*) as cnt FROM appointments WHERE doctor_id=$doctor_id AND status='Approved'")->fetch_assoc()['cnt'];
$totalCompleted = $conn->query("SELECT COUNT(*) as cnt FROM appointments WHERE doctor_id=$doctor_id AND status='Completed'")->fetch_assoc()['cnt'];
$totalCancelled = $conn->query("SELECT COUNT(*) as cnt FROM appointments WHERE doctor_id=$doctor_id AND status='Cancelled'")->fetch_assoc()['cnt'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Doctor Dashboard | Smart Doctor</title>
<link rel="stylesheet" href="assets/css/style.css">
<style>
/* --- Fonts --- */
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@500;700&family=Roboto:wght@400;500&display=swap');

body {
    font-family: 'Roboto', sans-serif;
    background: linear-gradient(to bottom, #f0f4f7, #d9e2ec);
    margin: 0;
    padding: 0;
}
nav {
    background: #2c3e50;
    padding: 10px 20px;
    color: #fff;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
nav .logo { font-weight: bold; font-size: 1.5em; font-family: 'Montserrat', sans-serif; }
nav ul { list-style: none; margin: 0; padding: 0; display: flex; gap: 15px; }
nav ul li a { color: #fff; text-decoration: none; font-weight: 600; transition: 0.3s; }
nav ul li a.active, nav ul li a:hover { text-decoration: underline; color: #1abc9c; }

.container { max-width: 1200px; margin: 20px auto; padding: 0 20px; animation: fadeInUp 1s ease forwards; }

/* --- Profile Card --- */
.profile-card {
    background: linear-gradient(135deg, #a8edea, #fed6e3);
    padding: 20px;
    border-radius: 16px;
    box-shadow: 0 12px 25px rgba(0,0,0,0.2);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    animation: fadeInUp 1s ease forwards;
}
.profile-info h2 { margin:0 0 10px; color:#2c3e50; font-family: 'Montserrat', sans-serif; }
.profile-info p { margin:3px 0; color:#333; }

/* --- Stats Cards --- */
.stats { display:flex; gap:20px; flex-wrap:wrap; margin-top:20px; }
.stats .card {
    flex:1;
    min-width:150px;
    text-align:center;
    padding:20px;
    border-radius: 16px;
    position: relative;
    overflow: hidden;
    cursor: default;
    transition: transform 0.3s, box-shadow 0.3s;
    color: #fff;
    font-weight: 600;
}
.stats .card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0,0,0,0.3); }

/* Gradient backgrounds for stats */
.stats .pending { background: linear-gradient(135deg, #f093fb, #f5576c); }
.stats .approved { background: linear-gradient(135deg, #43e97b, #38f9d7); }
.stats .completed { background: linear-gradient(135deg, #667eea, #764ba2); }
.stats .cancelled { background: linear-gradient(135deg, #f7971e, #ffd200); }

.stats .card h3 { margin-bottom:10px; font-family: 'Montserrat', sans-serif; font-size: 18px; }
.stats .card p { font-size: 30px; margin:0; animation: bounceNumber 1.5s ease forwards; }

/* Bounce animation for numbers */
@keyframes bounceNumber {
    0% { transform: scale(0); opacity:0; }
    50% { transform: scale(1.2); opacity:1; }
    100% { transform: scale(1); }
}

/* --- Appointments Table --- */
.appointments.card {
    margin-top:30px;
    padding:20px;
    border-radius:16px;
    background:#fff;
    box-shadow:0 12px 25px rgba(0,0,0,0.15);
    animation: fadeInUp 1s ease forwards;
}
.appointments h2 { margin-top:0; color:#2c3e50; font-family: 'Montserrat', sans-serif; }

.table { width:100%; border-collapse: collapse; margin-top:15px; }
.table th, .table td { padding:12px; border-bottom:1px solid #ddd; text-align:left; transition: background 0.3s; }
.table th { background:#1abc9c; color:#fff; }
.table tr:hover { background:#e0f7fa; }

/* Status badges with animation */
.status {
    font-weight:600;
    padding:6px 12px;
    border-radius:12px;
    color:#fff;
    display:inline-block;
    animation: pulse 1.5s infinite;
}
.status.Pending { background: #e67e22; }
.status.Approved { background: #2ecc71; }
.status.Completed { background: #3498db; }
.status.Cancelled { background: #e74c3c; }

/* Pulse animation for status badges */
@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

/* --- Fade in animation --- */
@keyframes fadeInUp {
    from { opacity:0; transform: translateY(20px); }
    to { opacity:1; transform: translateY(0); }
}

footer {
    text-align:center;
    margin-top:50px;
    padding:20px;
    background:#2c3e50;
    color:#fff;
    border-radius:12px;
    font-family: 'Montserrat', sans-serif;
}
</style>
</head>
<body>

<nav>
    <div class="logo">🩺 Smart Doctor</div>
    <ul>
        <li><a href="doctor_dashboard.php" class="active">Dashboard</a></li>
        <li><a href="appointment.php">Appointments</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</nav>

<div class="container">

    <!-- Profile Card -->
    <div class="card profile-card">
        <div class="profile-info">
            <h2><?= htmlspecialchars($doctor['name']) ?></h2>
            <p><strong>Specialization:</strong> <?= htmlspecialchars($doctor['specialization']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($doctor['email']) ?></p>
        </div>
        <div style="margin-left:20px;">
            <img src="assets/img/doctor.png" alt="Doctor" style="width:120px;border-radius:50%;box-shadow:0 10px 25px rgba(0,0,0,0.2)">
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats">
        <div class="card pending">
            <h3>Pending</h3>
            <p><?= $totalPending ?></p>
        </div>
        <div class="card approved">
            <h3>Approved</h3>
            <p><?= $totalApproved ?></p>
        </div>
        <div class="card completed">
            <h3>Completed</h3>
            <p><?= $totalCompleted ?></p>
        </div>
        <div class="card cancelled">
            <h3>Cancelled</h3>
            <p><?= $totalCancelled ?></p>
        </div>
    </div>

    <!-- Today's Appointments -->
    <div class="appointments card">
        <h2>📅 Today's Appointments (<?= $today ?>)</h2>
        <table class="table">
            <tr>
                <th>Patient</th>
                <th>Time</th>
                <th>Reason</th>
                <th>Status</th>
            </tr>
            <?php if ($appointments->num_rows > 0): ?>
                <?php while ($row = $appointments->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['patient_name']) ?></td>
                        <td><?= htmlspecialchars($row['appointment_time']) ?></td>
                        <td><?= htmlspecialchars($row['reason']) ?></td>
                        <td><span class="status <?= $row['status'] ?>"><?= $row['status'] ?></span></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="4">No appointments scheduled for today.</td></tr>
            <?php endif; ?>
        </table>
    </div>
</div>

<footer>
    <p>© <?= date('Y') ?> Smart Doctor Appointment & Health Record System</p>
</footer>

</body>
</html>
