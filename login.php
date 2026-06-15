<?php
session_start();
include("config/db.php");

// --- LOGIN PROCESS ---
$error = "";

if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Fetch user from DB
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // ❌ Only allow patients
        if ($user['role'] !== 'patient') {
            $error = "Access denied. Only patients can log in here.";
        }
        // ✅ Password check (supports hashed + md5)
        elseif (password_verify($password, $user['password']) || $user['password'] === md5($password)) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            header("Location: patient_profile.php");
            exit;
        } else {
            $error = "Incorrect password. Please try again.";
        }
    } else {
        $error = "No patient account found with that email.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Patient Login | Smart Health</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
<style>
/* ===== GLOBAL ===== */
* { box-sizing: border-box; }
body, html {
    margin: 0;
    padding: 0;
    height: 100%;
    font-family: 'Poppins', sans-serif;
    display: flex;
    justify-content: center;
    align-items: center;
    overflow: hidden;
    background: linear-gradient(135deg, #27ae60, #2ecc71);
    position: relative;
}

/* ===== PARTICLE BACKGROUND ===== */
body::before {
    content: '';
    position: absolute;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.05) 1%, transparent 1%) repeat;
    background-size: 50px 50px;
    animation: float 25s linear infinite;
    z-index: 0;
}
@keyframes float {
    0% { transform: translate(0,0); }
    100% { transform: translate(-50px,-50px); }
}

/* ===== CARD ===== */
.container { position: relative; z-index: 1; }
.card {
    background: #fff;
    padding: 40px 30px;
    border-radius: 15px;
    box-shadow: 0 15px 40px rgba(0,0,0,0.2);
    width: 380px;
    text-align: center;
    animation: fadeIn 1s ease;
}
@keyframes fadeIn {
    from { opacity:0; transform:translateY(-20px);}
    to {opacity:1; transform:translateY(0);}
}

/* ===== TITLE ===== */
h2 {
    color: #27ae60;
    margin-bottom: 25px;
    font-weight: 700;
}

/* ===== INPUTS ===== */
.input-group {
    position: relative;
    margin-bottom: 22px;
}
.input-wrapper {
    position: relative;
    width: 100%;
}
input[type="email"], input[type="password"], input[type="text"] {
    width: 100%;
    padding: 12px 40px 12px 12px;
    border-radius: 8px;
    border: 1px solid #ccc;
    background: #f9f9f9;
    font-size: 14px;
    transition: all 0.3s ease;
    box-sizing: border-box;
    appearance: none;
}
input:focus {
    border-color: #27ae60;
    background: #fff;
    box-shadow: 0 0 8px rgba(39,174,96,0.3);
}

/* ===== FLOATING LABEL ===== */
label {
    position: absolute;
    top: 12px;
    left: 12px;
    color: #aaa;
    font-size: 14px;
    pointer-events: none;
    transition: all 0.3s ease;
    background: transparent;
    padding: 0 4px;
}
input:focus + label,
input:not(:placeholder-shown) + label {
    top: -8px;
    left: 10px;
    font-size: 12px;
    color: #27ae60;
    background: #fff;
}

/* ===== EYE TOGGLE ===== */
.eye-toggle {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    color: #888;
    font-size: 16px;
    transition: 0.3s;
}
.eye-toggle:hover { color: #27ae60; }

/* ===== PASSWORD FIELD STYLING ===== */
.password-box {
    background: linear-gradient(135deg, #e8f8f5, #ffffff);
    border-radius: 10px;
    padding: 3px;
    transition: all 0.3s ease;
}
.password-box:focus-within {
    box-shadow: 0 0 10px rgba(39,174,96,0.3);
}

/* ===== BUTTON ===== */
button {
    width: 100%;
    padding: 12px;
    border: none;
    background: linear-gradient(135deg, #2ecc71, #27ae60);
    color: #fff;
    border-radius: 10px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: 0.3s ease;
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}
button:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.3);
}

/* ===== ERROR MESSAGE ===== */
.errors {
    background: #ffe5e5;
    color: #c0392b;
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 15px;
    text-align: left;
    animation: shake 0.4s;
}
@keyframes shake {
    25% { transform: translateX(-4px); }
    50% { transform: translateX(4px); }
    75% { transform: translateX(-4px); }
}

/* ===== LINKS ===== */
a { color: #27ae60; text-decoration: none; }
a:hover { text-decoration: underline; }
/* ===== HOME BUTTON ===== */
.home-btn {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 10px 18px;
    background: linear-gradient(135deg, #54e937ff, #6ae79eff);
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
    <div class="card">
        <h2>🔐 Patient Login</h2>

        <?php if (!empty($error)): ?>
            <div class="errors"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" autocomplete="on">
            <div class="input-group">
                <input type="email" name="email" id="email" placeholder=" " required autocomplete="email">
                <label for="email">Email</label>
            </div>

            <div class="input-group password-box">
                <div class="input-wrapper">
                    <input type="password" id="password" name="password" placeholder=" " required autocomplete="current-password">
                    <label for="password">Password</label>
                    <span class="eye-toggle" onclick="togglePassword(this)">👁️</span>
                </div>
            </div>

            <button type="submit" name="login">Login</button>
        </form>

        <p style="margin-top: 15px;">Don’t have an account? <a href="register.php">Register here</a></p>
    </div>
</div>

<script>
// ===== PASSWORD TOGGLE (no shrink fix) =====
function togglePassword(icon) {
    const input = icon.parentElement.querySelector('input');
    const wasPassword = input.type === "password";
    
    // Clone trick preserves style
    const clone = input.cloneNode(true);
    clone.type = wasPassword ? "text" : "password";
    clone.value = input.value;
    input.parentNode.replaceChild(clone, input);
    
    // Rebind the icon again
    icon.onclick = () => togglePassword(icon);
}
</script>
</body>
</html>
