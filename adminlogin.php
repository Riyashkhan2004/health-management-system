<?php
session_start();
include("config/db.php");

// --- ADMIN LOGIN PROCESS ---
$error = "";

if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Hardcoded admin
    if ($email === "riyas@gmail.com" && $password === "riyas") {
        $_SESSION['user_id'] = 1;
        $_SESSION['role'] = 'admin';
        header("Location: admin_dashboard.php");
        exit;
    }

    // DB check
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND role = 'admin'");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password']) || $user['password'] === md5($password)) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            header("Location: admin_dashboard.php");
            exit;
        } else {
            $error = "Incorrect password. Please try again.";
        }
    } else {
        $error = "No admin account found with that email.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Login | Smart Health</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
<style>
/* ===== GLOBAL ===== */
* { box-sizing: border-box; margin:0; padding:0; }
body, html {
    height:100%;
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #6a11cb, #2575fc);
    display:flex;
    justify-content:center;
    align-items:center;
    overflow:hidden;
}

/* ===== PARTICLE / BACKGROUND EFFECT ===== */
body::before {
    content: '';
    position: absolute;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.05) 1%, transparent 1%) repeat;
    background-size: 50px 50px;
    animation: float 20s linear infinite;
    z-index:0;
}
@keyframes float {
    0% { transform: translate(0,0);}
    100% { transform: translate(-50px,-50px);}
}

/* ===== CARD ===== */
.container {
    position: relative;
    z-index: 1;
}
.card {
    background: #fff;
    width: 380px;
    padding: 40px 30px;
    border-radius: 15px;
    box-shadow: 0 15px 40px rgba(0,0,0,0.3);
    text-align: center;
    transform-style: preserve-3d;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    animation: slideFade 1s ease;
}
.card:hover {
    transform: rotateY(5deg) rotateX(3deg) scale(1.03);
    box-shadow: 0 20px 50px rgba(0,0,0,0.4);
}
@keyframes slideFade {
    from {opacity:0; transform: translateY(-30px);}
    to {opacity:1; transform: translateY(0);}
}

/* ===== HEADINGS ===== */
.logo {
    font-size: 32px;
    font-weight:700;
    color: #6a11cb;
    margin-bottom: 10px;
    display:inline-block;
    animation: bounce 2s infinite;
}
@keyframes bounce {
    0%,100%{transform:translateY(0);}
    50%{transform:translateY(-8px);}
}
h2 { color:#6a11cb; margin-bottom:20px; }

/* ===== INPUTS & FLOATING LABEL ===== */
.input-group { position: relative; margin-bottom:20px; }
input[type="email"], input[type="password"], input[type="text"] {
    width: 100%;
    padding:12px 40px 12px 12px;
    border-radius:8px;
    border:1px solid #ccc;
    outline:none;
    font-size:14px;
    background:#f9f9f9;
    transition: all 0.3s ease;
}
input[type="email"]:focus,
input[type="password"]:focus,
input[type="text"]:focus {
    border-color:#6a11cb;
    box-shadow: 0 0 10px rgba(106,17,203,0.3);
    background:#fff;
}
label {
    position: absolute;
    left:12px; top:12px;
    color:#aaa;
    font-size:14px;
    pointer-events:none;
    transition: all 0.3s ease;
    background:#fff; padding:0 4px;
}
input:focus + label,
input:not(:placeholder-shown) + label {
    top:-8px;
    font-size:12px;
    color:#6a11cb;
}

/* ===== EYE TOGGLE ===== */
.eye-toggle {
    position:absolute;
    right:10px;
    top:50%;
    transform:translateY(-50%);
    cursor:pointer;
    color:#888;
    font-size:16px;
    user-select:none;
    transition:0.3s;
}
.eye-toggle:hover { color:#6a11cb; }

/* ===== BUTTON ===== */
button {
    width:100%;
    padding:14px;
    background: linear-gradient(135deg, #6a11cb, #2575fc);
    border:none;
    color:#fff;
    font-size:16px;
    border-radius:10px;
    cursor:pointer;
    font-weight:600;
    transition: all 0.4s;
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}
button:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.3);
}

/* ===== ERROR BOX ===== */
.errors {
    background:#ffe5e5;
    color:#c0392b;
    padding:10px;
    border-radius:6px;
    margin-bottom:15px;
    animation: shake 0.5s;
}
@keyframes shake {
    0%{transform:translateX(0);}
    25%{transform:translateX(-5px);}
    50%{transform:translateX(5px);}
    75%{transform:translateX(-5px);}
    100%{transform:translateX(0);}
}

/* ===== LINKS ===== */
a { color:#6a11cb; text-decoration:none; }
a:hover { text-decoration:underline; }
</style>
</head>
<body>

<div class="container">
    <div class="card">
        <div class="logo">🛡️ Smart Admin</div>
        <h2>Admin Login</h2>

        <?php if(!empty($error)): ?>
            <div class="errors"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" autocomplete="on">
            <div class="input-group">
                <input type="email" name="email" placeholder=" " required autocomplete="email">
                <label>Email</label>
            </div>

            <div class="input-group">
                <input type="password" id="password" name="password" placeholder=" " required autocomplete="current-password">
                <label>Password</label>
                <span class="eye-toggle" onclick="togglePassword()">👁️</span>
            </div>

            <button type="submit" name="login">Login</button>
        </form>

        <p style="margin-top:15px;">
            <a href="login.php">← Back to Patient Login</a>
        </p>
    </div>
</div>

<script>
// ===== PASSWORD TOGGLE =====
function togglePassword() {
    const pass = document.getElementById('password');
    const start = pass.selectionStart;
    const end = pass.selectionEnd;
    pass.type = pass.type === 'password' ? 'text' : 'password';
    pass.setSelectionRange(start, end);
    pass.focus();
}
</script>
</body>
</html>
