<?php
// =======================================
// SMART DOCTOR SYSTEM — ADMIN_DASHBOARD.PHP
// =======================================
session_start();
include("config/db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: adminlogin.php");
    exit;
}

$totalDoctors = $conn->query("SELECT COUNT(*) as cnt FROM doctors")->fetch_assoc()['cnt'];
$totalPatients = $conn->query("SELECT COUNT(*) as cnt FROM patients")->fetch_assoc()['cnt'];
$totalAppointments = $conn->query("SELECT COUNT(*) as cnt FROM appointments")->fetch_assoc()['cnt'];
$totalPending = $conn->query("SELECT COUNT(*) as cnt FROM appointments WHERE status='Pending'")->fetch_assoc()['cnt'];
$totalApproved = $conn->query("SELECT COUNT(*) as cnt FROM appointments WHERE status='Approved'")->fetch_assoc()['cnt'];
$totalCompleted = $conn->query("SELECT COUNT(*) as cnt FROM appointments WHERE status='Completed'")->fetch_assoc()['cnt'];
$totalCancelled = $conn->query("SELECT COUNT(*) as cnt FROM appointments WHERE status='Cancelled'")->fetch_assoc()['cnt'];

$latestDoctors = $conn->query("SELECT * FROM doctors ORDER BY id DESC LIMIT 5");
$latestPatients = $conn->query("SELECT * FROM users WHERE role = 'patient' ORDER BY id DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard | Smart Doctor</title>
<link rel="stylesheet" href="assets/css/style.css">
<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap');
    body {
        font-family: 'Poppins', sans-serif;
        margin: 0;
        background: linear-gradient(120deg,#74ebd5,#ACB6E5);
        background-size: 400% 400%;
        animation: gradientBG 15s ease infinite;
        color:#2c3e50;
    }

    @keyframes gradientBG {
        0% {background-position:0% 50%;}
        50% {background-position:100% 50%;}
        100% {background-position:0% 50%;}
    }

    nav {
        display:flex;
        justify-content:space-between;
        align-items:center;
        padding:15px 30px;
        background: rgba(0,0,0,0.6);
        color:white;
        box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        position: sticky;
        top:0;
        z-index:100;
    }
    nav .logo { font-weight:700; font-size:22px; letter-spacing:1px; }
    nav ul { list-style:none; display:flex; gap:20px; }
    nav ul li a { color:white; text-decoration:none; font-weight:500; transition:0.3s; }
    nav ul li a.active, nav ul li a:hover { color:#ffec59; text-shadow: 1px 1px 5px #000; }

    .container { max-width:1300px; margin:40px auto; padding:0 20px; }
    h2 { text-align:center; margin-bottom:30px; color:#fff; text-shadow:1px 1px 5px rgba(0,0,0,0.5); }

/* Small futuristic stats card */
.stats {
    display:flex;
    flex-wrap:wrap;
    gap:15px;
    justify-content:center;
}

.small-card {
    flex:0 0 150px;
    height:120px;
    display:flex;
    flex-direction:column;
    justify-content:center;
    align-items:center;
    border-radius:15px;
    color:white;
    font-weight:600;
    text-align:center;
    cursor:pointer;
    position:relative;
    overflow:hidden;
    transition: transform 0.6s, box-shadow 0.6s;
    box-shadow: 0 6px 20px rgba(0,0,0,0.3);
}

/* Neon glow animation */
.small-card::before {
    content:'';
    position:absolute;
    top:-50%;
    left:-50%;
    width:200%;
    height:200%;
    background: radial-gradient(circle at center, rgba(255,255,255,0.2), transparent 70%);
    animation: glowMove 4s linear infinite;
}

@keyframes glowMove {
    0% { transform: rotate(0deg) translateX(0); }
    50% { transform: rotate(180deg) translateX(5px); }
    100% { transform: rotate(360deg) translateX(0); }
}

/* Hover effects */
.small-card:hover {
    transform: scale(1.15) rotateZ(-2deg);
    box-shadow: 0 15px 40px rgba(0,0,0,0.6), 0 0 15px rgba(255,255,255,0.3);
}

/* Text animations */
.small-card h3 {
    font-size:14px;
    margin-bottom:5px;
    letter-spacing:1px;
    text-shadow: 0 0 5px rgba(0,0,0,0.3);
}

.small-card p {
    font-size:24px;
    font-weight:700;
    animation:bounce 1.2s infinite alternate;
    text-shadow: 0 0 8px rgba(255,255,255,0.5);
}

@keyframes bounce {
    0% { transform: translateY(0); }
    50% { transform: translateY(-6px); }
    100% { transform: translateY(0); }
}

/* Unique gradients for each card */
.gradient-doctors { background: linear-gradient(135deg,#1abc9c,#16a085); }
.gradient-patients { background: linear-gradient(135deg,#3498db,#2980b9); }
.gradient-total { background: linear-gradient(135deg,#f39c12,#e67e22); }
.gradient-pending { background: linear-gradient(135deg,#e74c3c,#c0392b); }
.gradient-approved { background: linear-gradient(135deg,#9b59b6,#8e44ad); }
.gradient-completed { background: linear-gradient(135deg,#00b894,#55efc4); }
.gradient-cancelled { background: linear-gradient(135deg,#fd79a8,#d63031); }

    .tables { display:grid; grid-template-columns:1fr 1fr; gap:25px; margin-top:40px; }
    .tables .card {
        padding:20px;
        border-radius:15px;
        background:rgba(255,255,255,0.95);
        box-shadow:0 8px 20px rgba(0,0,0,0.1);
        overflow-x:auto;
        transform: translateY(30px);
        opacity:0;
        animation: fadeInUp 0.8s forwards;
    }
    .tables .card:nth-child(1){ animation-delay:0.2s; }
    .tables .card:nth-child(2){ animation-delay:0.4s; }

    @keyframes fadeInUp {
        to { transform: translateY(0); opacity:1; }
    }

    table { width:100%; border-collapse:collapse; margin-top:10px; }
    table th, table td { padding:12px; text-align:left; border-bottom:1px solid #ddd; transition:0.3s; }
    table th { background:#f1f1f1; }
    table tr:hover { background:#e0f7fa; transform: translateX(5px); }

    footer { text-align:center; margin:50px 0 20px; font-size:15px; color:#fff; text-shadow:1px 1px 5px rgba(0,0,0,0.5); }

</style>
</head>
<body>

<nav>
    <div class="logo">🩺 Smart Doctor</div>
    <ul>
        <li><a href="admin_dashboard.php" class="active">Dashboard</a></li>
        <li><a href="manage_doctors.php">Doctors</a></li>
        <li><a href="manage_patients.php">Patients</a></li>
        <li><a href="appointment.php">Appointments</a></li>
        <li><a href="reviews.php">Reviews</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</nav>

<div class="container">
    <h2>📊 Admin Dashboard</h2>

    <div class="stats">
    <a href="manage_doctors.php" class="card small-card gradient-doctors">
        <h3>Doctors</h3>
        <p class="count" data-count="<?= $totalDoctors ?>">0</p>
    </a>
    <a href="manage_patients.php" class="card small-card gradient-patients">
        <h3>Patients</h3>
        <p class="count" data-count="<?= $totalPatients ?>">0</p>
    </a>
    <a href="appointment.php" class="card small-card gradient-total">
        <h3>Total Appointments</h3>
        <p class="count" data-count="<?= $totalAppointments ?>">0</p>
    </a>
    <a href="appointment.php?status=Pending" class="card small-card gradient-pending">
        <h3>Pending</h3>
        <p class="count" data-count="<?= $totalPending ?>">0</p>
    </a>
    <a href="appointment.php?status=Approved" class="card small-card gradient-approved">
        <h3>Approved</h3>
        <p class="count" data-count="<?= $totalApproved ?>">0</p>
    </a>
    <a href="appointment.php?status=Completed" class="card small-card gradient-completed">
        <h3>Completed</h3>
        <p class="count" data-count="<?= $totalCompleted ?>">0</p>
    </a>
    <a href="appointment.php?status=Cancelled" class="card small-card gradient-cancelled">
        <h3>Cancelled</h3>
        <p class="count" data-count="<?= $totalCancelled ?>">0</p>
    </a>
</div>

    <div class="tables">
        <div class="card">
            <h3>🩺 Latest Doctors</h3>
            <table>
                <tr><th>ID</th><th>Name</th><th>Specialization</th><th>Email</th></tr>
                <?php if ($latestDoctors->num_rows > 0): ?>
                    <?php while ($row = $latestDoctors->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= htmlspecialchars($row['specialization']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4">No doctors found.</td></tr>
                <?php endif; ?>
            </table>
        </div>

        <div class="card">
            <h3>👤 Latest Patients</h3>
            <table>
                <tr><th>ID</th><th>Name</th><th>Email</th></tr>
                <?php if ($latestPatients->num_rows > 0): ?>
                    <?php while ($row = $latestPatients->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="3">No patients found.</td></tr>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>

<footer>
    <p>© <?= date('Y') ?> Smart Doctor Appointment & Health Record System</p>
</footer>

<script>
// Animate count-up numbers
const counters = document.querySelectorAll('.count');
counters.forEach(counter => {
    const updateCount = () => {
        const target = +counter.getAttribute('data-count');
        const count = +counter.innerText;
        const increment = target / 100;
        if(count < target) {
            counter.innerText = Math.ceil(count + increment);
            requestAnimationFrame(updateCount);
        } else {
            counter.innerText = target;
        }
    };
    updateCount();
});
</script>
</body>
</html>
