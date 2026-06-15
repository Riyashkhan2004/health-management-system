<?php
// =======================================
// DOCTOR LOGIN — LOGIN.PHP
// =======================================
session_start();
include("config/db.php");

// Redirect if already logged in
if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'doctor') {
    header("Location: doctor_dashboard.php");
    exit;
}

$error = "";

// Handle login
if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Fetch doctor from users table only
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND role='doctor'");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password']) || $user['password'] === md5($password)) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            header("Location: doctor_dashboard.php");
            exit;
        } else {
            $error = "Incorrect password. Please try again.";
        }
    } else {
        $error = "No doctor account found with that email.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Doctor Login | Smart Health</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
<style>
/* ===== GLOBAL ===== */
* { box-sizing: border-box; }
html, body {
    margin: 0;
    padding: 0;
    height: 100%;
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #27e6ffff, #35fafaff);
    display: flex;
    justify-content: center;
    align-items: center;
    overflow: hidden;
}

/* ===== PARTICLE EFFECT ===== */
body::before {
    content: '';
    position: absolute;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.05) 1%, transparent 1%) repeat;
    background-size: 50px 50px;
    animation: float 20s linear infinite;
    z-index: 0;
}
@keyframes float {
    0% { transform: translate(0,0); }
    100% { transform: translate(-50px,-50px); }
}

/* ===== CONTAINER ===== */
.container {
    position: relative;
    z-index: 1;
    width: 400px;
    background: #fff;
    padding: 50px 30px;
    border-radius: 15px;
    box-shadow: 0 15px 40px rgba(0,0,0,0.3);
    text-align: center;
    animation: fadeSlideIn 1s ease both;
}
@keyframes fadeSlideIn {
    from { opacity: 0; transform: translateY(30px) scale(0.98); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}

/* ===== LOGO ===== */
.logo {
    font-size: 32px;
    font-weight: 700;
    color: #27ae60;
    margin-bottom: 25px;
    animation: bounce 2s infinite;
}
@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}

/* ===== HEADINGS ===== */
h2 {
    color: #27ae60;
    margin-bottom: 25px;
    letter-spacing: 1px;
}

/* ===== INPUT GROUP ===== */
.input-group {
    position: relative;
    margin-bottom: 20px;
}

input[type="email"], 
.password-wrapper input {
    width: 100%;
    padding: 12px 40px 12px 12px;
    border-radius: 8px;
    border: 1px solid #ccc;
    outline: none;
    font-size: 14px;
    transition: all 0.3s ease;
    box-sizing: border-box;
    background: #fff;
}

/* Smooth glowing border */
input:focus {
    border-color: #27ae60;
    box-shadow: 0 0 8px rgba(39,174,96,0.4);
    animation: pulse 1s infinite alternate;
}
@keyframes pulse {
    from { box-shadow: 0 0 5px rgba(39,174,96,0.4); }
    to { box-shadow: 0 0 10px rgba(39,174,96,0.7); }
}

/* ===== FLOATING LABEL ===== */
label {
    position: absolute;
    left: 12px;
    top: 12px;
    color: #aaa;
    font-size: 14px;
    pointer-events: none;
    transition: all 0.3s ease;
    background: #fff;
    padding: 0 4px;
}
input:focus + label,
input:not(:placeholder-shown) + label {
    top: -8px;
    font-size: 12px;
    color: #27ae60;
}

/* ===== PASSWORD FIELD (STYLISH WRAPPER) ===== */
.password-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}
.password-wrapper input {
    width: 100%;
    font-family: 'Poppins', sans-serif;
    letter-spacing: 0.5px;
}
.password-wrapper .eye-toggle {
    position: absolute;
    right: 12px;
    cursor: pointer;
    color: #888;
    transition: color 0.3s;
    user-select: none;
}
.password-wrapper .eye-toggle:hover {
    color: #27ae60;
}

/* ===== BUTTON ===== */
button {
    width: 100%;
    padding: 14px;
    background: linear-gradient(135deg, #2ecc71, #27ae60);
    border: none;
    color: #fff;
    font-size: 16px;
    border-radius: 10px;
    cursor: pointer;
    font-weight: 600;
    transition: 0.4s;
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}
button:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.3);
}

/* ===== ERROR ===== */
.errors {
    background: #ffe5e5;
    color: #c0392b;
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 15px;
    text-align: left;
    animation: shake 0.5s;
}
@keyframes shake {
    0%,100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    50% { transform: translateX(5px); }
    75% { transform: translateX(-5px); }
}
/* ===== HOME BUTTON ===== */
.home-btn {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 10px 18px;
    background: linear-gradient(135deg, #6a11cb, #2575fc);
    color: #fff;
    font-weight: 600;
    border-radius: 8px;
    text-decoration: none;
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    transition: 0.3s ease;
    z-index: 2;
}
.home-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.3);
}
</style>
</head>
<body>
<!-- Add this inside the <body> before the .container -->
<a href="index.php" class="home-btn">🏠 Home</a>
<div class="container">
    <div class="logo">🩺 Smart Doctor</div>
    <h2>Doctor Login</h2>

    <?php if (!empty($error)): ?>
        <div class="errors"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" autocomplete="on">
        <div class="input-group">
            <input type="email" name="email" placeholder=" " required autocomplete="email">
            <label>Email</label>
        </div>

        <div class="input-group password-wrapper">
            <input type="password" id="password" name="password" placeholder=" " required autocomplete="current-password">
            <label>Password</label>
            <span class="eye-toggle" id="eye">👁️</span>
        </div>

        <button type="submit" name="login">Login</button>
    </form>
</div>

<script>
// ===== FIXED PASSWORD TOGGLE =====
// We now overlay a fake "text" mirror field to avoid losing CSS when toggling type
const passField = document.getElementById('password');
const eye = document.getElementById('eye');
let revealed = false;

eye.addEventListener('click', () => {
    revealed = !revealed;
    if (revealed) {
        passField.setAttribute('data-orig-type', passField.type);
        passField.type = 'text';
        eye.textContent = '🙈';
    } else {
        passField.type = 'password';
        eye.textContent = '👁️';
    }
    passField.focus();
});
</script>

</body>
</html>
