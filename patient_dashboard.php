<?php
session_start();
include("config/db.php");

// Flash Message Popup
if (isset($_SESSION['flash_msg'])) {
    $flash = $_SESSION['flash_msg'];
    unset($_SESSION['flash_msg']);
    echo "
    <div id='flashBox' class='flash-box {$flash['type']}'>
        <h3>{$flash['title']}</h3>
        <p>{$flash['text']}</p>
        <button onclick=\"document.getElementById('flashBox').classList.add('fadeOut')\">OK</button>
    </div>
    <style>
        @keyframes popupIn { from { opacity:0; transform:translate(-50%,-50%) scale(0.8); } to { opacity:1; transform:translate(-50%,-50%) scale(1); } }
        @keyframes fadeOut { from { opacity:1; transform:scale(1); } to { opacity:0; transform:scale(0.9); } }
        .flash-box { position:fixed; top:50%; left:50%; transform:translate(-50%, -50%) scale(1); background:#fff; border-radius:20px; box-shadow:0 10px 40px rgba(0,0,0,0.2); padding:25px 30px; width:380px; text-align:center; z-index:9999; animation:popupIn 0.4s ease forwards; }
        .flash-box.fadeOut { animation:fadeOut 0.3s forwards; }
        .flash-box.success { border-top:6px solid #27ae60; }
        .flash-box.error { border-top:6px solid #e74c3c; }
        .flash-box h3 { margin-bottom:10px; color:#2c3e50; font-size:22px; }
        .flash-box p { color:#555; margin-bottom:20px; font-size:15px; }
        .flash-box button { background:linear-gradient(135deg,#2ecc71,#27ae60); color:#fff; border:none; padding:10px 25px; border-radius:8px; font-weight:600; cursor:pointer; transition:0.3s; }
        .flash-box button:hover { transform:scale(1.05); }
    </style>
    ";
}

// Only patients can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
    header("Location: login.php");
    exit;
}

$patient_id = $_SESSION['user_id'];

// Fetch patient info
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ? AND role = 'patient'");
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    echo "Error: Patient record not found.";
    exit;
}
$patient = $result->fetch_assoc();

// Upcoming appointments count
$upcoming_count = $conn->query("
    SELECT COUNT(*) AS total FROM appointments 
    WHERE patient_id = $patient_id AND appointment_date >= CURDATE()
")->fetch_assoc()['total'] ?? 0;

// Past appointments count
$past_count = $conn->query("
    SELECT COUNT(*) AS total FROM appointments 
    WHERE patient_id = $patient_id AND appointment_date < CURDATE()
")->fetch_assoc()['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Patient Dashboard | Smart Doctor</title>
<link rel="stylesheet" href="assets/css/style.css">
<style>
/* ===== GLOBAL ===== */
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap');
body {
    font-family: 'Poppins', sans-serif;
    margin:0; background: linear-gradient(135deg,#e3fdfd,#ffe6fa);
    min-height:100vh; overflow-x:hidden; animation:fadeInPage 0.8s ease forwards;
}
@keyframes fadeInPage { from{opacity:0;} to{opacity:1;} }

/* ===== NAVBAR ===== */
.navbar {
    display:flex; justify-content:space-between; align-items:center;
    background:linear-gradient(90deg,#2ecc71,#27ae60);
    padding:15px 30px; color:#fff; box-shadow:0 5px 15px rgba(0,0,0,0.1);
}
.navbar .logo { font-size:26px; font-weight:700; }
.navbar a { color:#fff; text-decoration:none; margin-left:20px; font-weight:600; transition:0.3s; }
.navbar a:hover { color:#f1c40f; text-shadow:0 0 10px rgba(241,196,15,0.7); }

/* ===== DASHBOARD ===== */
.container { max-width:1000px; margin:50px auto; padding:20px; text-align:center; }
h1 { color:#2c3e50; font-size:32px; font-weight:700; margin-bottom:40px; animation:slideIn 1s ease; }
@keyframes slideIn { from{transform:translateY(-20px);opacity:0;} to{transform:translateY(0);opacity:1;} }

/* ===== CARDS ===== */
.dashboard-container { display:flex; flex-wrap:wrap; gap:25px; justify-content:center; }
.card { flex:1; min-width:240px; background:#fff; padding:30px 20px; border-radius:16px; box-shadow:0 10px 25px rgba(0,0,0,0.1); text-align:center; transition:all 0.3s ease; position:relative; overflow:hidden; cursor:pointer; }
.card:hover { transform:translateY(-10px) scale(1.03); box-shadow:0 20px 40px rgba(0,0,0,0.15); }
.card::before { content:''; position:absolute; top:-50%; left:-50%; width:200%; height:200%; background:radial-gradient(circle,rgba(255,255,255,0.1) 10%,transparent 11%); background-size:10px 10px; opacity:0; transition:0.5s; }
.card:hover::before { opacity:1; }

.card h2 { font-size:38px; margin-bottom:15px; color:#2c3e50; animation:popIn 1s ease forwards; }
.card p { color:#555; font-size:16px; font-weight:500; }
@keyframes popIn { from{transform:scale(0.8);opacity:0;} to{transform:scale(1);opacity:1;} }

/* ===== CARD COLORS ===== */
.card:nth-child(1) { background: linear-gradient(135deg,#a8edea,#fed6e3); }
.card:nth-child(2) { background: linear-gradient(135deg,#f6d365,#fda085); }
.card:nth-child(3) { background: linear-gradient(135deg,#c3cfe2,#c7e9fb); }

/* ===== ANIMATED ICONS ===== */
.card h2 { display:flex; justify-content:center; align-items:center; gap:10px; }
.card .icon { display:inline-block; animation:float 2s ease-in-out infinite; font-size:40px; }
@keyframes float { 0%,100%{transform:translateY(0);} 50%{transform:translateY(-10px);} }

/* ===== FOOTER ===== */
footer { text-align:center; margin-top:60px; padding:20px; background:#2c3e50; color:#fff; border-radius:12px 12px 0 0; font-weight:500; }

/* ===== CLICKABLE CARD EFFECT ===== */
.card-link { text-decoration:none; color:inherit; }
</style>
</head>
<body>

<nav class="navbar">
    <div class="logo">🩺 Smart Doctor</div>
    <div>
        <a href="patient_dashboard.php">Dashboard</a>
        <a href="book.php">Book Appointment</a>
        <a href="patient_profile.php">Profile</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>

<div class="container">
    <h1>Welcome, <?= htmlspecialchars($patient['name'] ?? 'Patient') ?>!</h1>

    <div class="dashboard-container">
        <!--<a href="patient_dashboard.php" class="card-link">-->
            <div class="card">
                <h2><span class="icon">📅</span><?= $upcoming_count ?></h2>
                <p>Upcoming Appointments</p>
            </div>
        <!--<a href="patient_dashboard.php" class="card-link">-->
            <div class="card">
                <h2><span class="icon">📖</span><?= $past_count ?></h2>
                <p>Past Appointments</p>
            </div>
        
        <a href="patient_profile.php" class="card-link">
            <div class="card">
                <h2><span class="icon">👤</span></h2>
                <p>View Profile</p>
            </div>
        </a>
    </div>
</div>

<footer>
    <p>© <?= date('Y') ?> Smart Doctor Appointment & Health Record System</p>
</footer>

</body>
</html>
