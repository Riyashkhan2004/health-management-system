<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Smart Health System</title>
<link rel="stylesheet" href="assets/css/style.css">
<style>
/* ===== GLOBAL STYLES ===== */
body {
    font-family: 'Poppins', sans-serif;
    margin: 0;
    background: #9aa3faff; /* soft medical blue */
    color: #2c3e50;
}
.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

/* ===== HERO SECTION ===== */
.hero {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 60px 20px;
    background: linear-gradient(135deg, #00c6ff, #0072ff); /* medical blue gradient */
    color: #fff;
    flex-wrap: wrap;
    border-radius: 16px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}
.hero-text { flex: 1; min-width: 300px; }
.hero-text h1 {
    font-size: 48px;
    margin-bottom: 20px;
}
.hero-text p {
    font-size: 18px;
    margin-bottom: 30px;
    line-height: 1.6;
}
.hero-buttons button {
    padding: 15px 30px;
    margin-right: 15px;
    border: none;
    border-radius: 10px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: 0.3s;
}
.btn-login {
    background: #ffffffcc;
    color: #0072ff;
    backdrop-filter: blur(5px);
}
.btn-login:hover {
    background: #ffffff;
    transform: translateY(-2px);
}
.btn-register {
    background: #0072ffcc;
    color: #fff;
    backdrop-filter: blur(5px);
}
.btn-register:hover {
    background: #005bb5;
    transform: translateY(-2px);
}

.hero-image { flex: 1; min-width: 300px; text-align: center; }
.hero-image img {
    width: 100%;
    max-width: 400px;
    border-radius: 16px;
    box-shadow: 0 12px 35px rgba(0, 0, 0, 0.15);
}

/* ===== FEATURES SECTION ===== */
.features {
    display: flex;
    justify-content: space-around;
    flex-wrap: wrap;
    margin-top: 50px;
    gap: 20px;
}
.feature-card {
    flex: 1;
    min-width: 250px;
    background: linear-gradient(145deg, #6cdbd2ff, #e0f7f7); /* subtle medical gradient */
    margin: 10px;
    padding: 25px 20px;
    border-radius: 16px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
    text-align: center;
    transition: 0.4s;
}
.feature-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 35px rgba(0, 0, 0, 0.12);
}
.feature-card h3 { margin-bottom: 10px; color: #0072ff; }
.feature-card p { color: #555; line-height: 1.6; }
.feature-card button {
    margin-top: 15px;
    background: #00c6ffcc;
    color: #fff;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: 0.3s;
}
.feature-card button:hover {
    background: #0072ff;
    transform: translateY(-2px);
}

/* ===== FOOTER ===== */
footer {
    text-align: center;
    margin-top: 50px;
    padding: 25px 20px;
    background: linear-gradient(135deg, #00c6ff, #0072ff); /* matching footer gradient */
    color: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
    .hero {
        flex-direction: column;
        text-align: center;
    }
    .hero-buttons { justify-content: center; }
}
/* ===== GLOBAL ANIMATION SETTINGS ===== */
* {
    transition: all 0.4s ease-in-out;
}

/* ===== HERO ANIMATIONS ===== */
.hero-text h1 {
    opacity: 0;
    transform: translateY(-20px);
    animation: fadeSlideIn 1s forwards 0.3s;
}
.hero-text p {
    opacity: 0;
    transform: translateY(-10px);
    animation: fadeSlideIn 1s forwards 0.6s;
}
.hero-buttons button {
    opacity: 0;
    transform: translateY(10px);
    animation: fadeSlideIn 1s forwards 0.9s;
}

.hero-image img {
    opacity: 0;
    transform: scale(0.9);
    animation: imageZoomIn 1s forwards 1s;
}

/* ===== FEATURES ANIMATION ===== */
.feature-card {
    opacity: 0;
    transform: translateY(30px);
    animation: cardFadeUp 0.8s forwards;
}
.feature-card:nth-child(1) { animation-delay: 0.3s; }
.feature-card:nth-child(2) { animation-delay: 0.5s; }
.feature-card:nth-child(3) { animation-delay: 0.7s; }
.feature-card:nth-child(4) { animation-delay: 0.9s; }

/* ===== KEYFRAMES ===== */
@keyframes fadeSlideIn {
    to { opacity: 1; transform: translateY(0); }
}
@keyframes imageZoomIn {
    to { opacity: 1; transform: scale(1); }
}
@keyframes cardFadeUp {
    to { opacity: 1; transform: translateY(0); }
}

/* ===== HOVER EFFECTS (enhanced) ===== */
.feature-card:hover {
    transform: translateY(-10px) scale(1.03);
    box-shadow: 0 15px 40px rgba(0,0,0,0.15);
}
.btn-login:hover, .btn-register:hover, .feature-card button:hover {
    transform: translateY(-3px) scale(1.05);
}
</style>
</head>
<body>
<div class="container">

<!-- HERO SECTION -->
<div class="hero">
    <div class="hero-text">
        <h1>Welcome to Smart Health System</h1>
        <p>Smart Health System is an online platform for managing doctor appointments, patient records, and hospital operations efficiently. Patients can book appointments, track health records, and communicate with doctors seamlessly. Doctors can manage appointments, patient data, and provide timely health services. Admins oversee the system and ensure smooth operations.</p>
        <div class="hero-buttons">
            <button class="btn-login" onclick="window.location.href='login.php'">Login</button>
            <button class="btn-register" onclick="window.location.href='register.php'">Register</button>
        </div>
    </div>
    <div class="hero-image">
        <img src="assets/img/healthcare.jpg" alt="Smart Health">
    </div>
</div>

<!-- FEATURES SECTION -->
<div class="features">
    <div class="feature-card">
        <h3>📅 Online Appointments</h3>
        <p>Book appointments with doctors anytime, anywhere. Avoid long queues and save your time.</p>
        <button class="btn-login" onclick="window.location.href='redirect.php?target=book.php'">Book Now</button>
    </div>
    <div class="feature-card">
        <h3>🩺 Doctor Management</h3>
        <p>Doctors can manage patient appointments, track health records, and update patient progress.</p>
        <button class="btn-login" onclick="window.location.href='logdoctor.php?redirect=doctor_dashboard.php'">Doctor</button>
    </div>
    <div class="feature-card">
        <h3>👤 Patient Profiles</h3>
        <p>Patients have personal dashboards to manage their appointments, profile, and health records securely.</p>
        <button class="btn-login" onclick="window.location.href='redirect.php?target=patient_dashboard.php'">Patient</button>
    </div>
    <div class="feature-card">
        <h3>📊 Admin Dashboard</h3>
        <p>Admins can oversee doctors, patients, and appointments to ensure smooth operation of the system.</p>
        <button class="btn-login" onclick="window.location.href='adminlogin.php?redirect=admin_dashboard.php'">Admin</button>
    </div>
</div>
<footer>
    <p>© <?= date('Y') ?> Smart Health System. All Rights Reserved.</p>
</footer>

</div>
</body>
</html>
