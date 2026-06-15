<?php
session_start();
include("config/db.php");

if (isset($_POST['register'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = md5($_POST['password']);
    $role = 'patient';

    // Insert user into database
    if ($conn->query("INSERT INTO users (name, email, password, role) VALUES ('$name', '$email', '$password', '$role')")) {
        $id = $conn->insert_id;
        $_SESSION['user_id'] = $id;
        $_SESSION['role'] = 'patient';
        header("Location: patient_profile.php");
        exit;
    } else {
        $error = "Email already exists";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Register | Smart Health</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

<style>
* { box-sizing: border-box; }
body {
    margin: 0;
    padding: 0;
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    background: linear-gradient(135deg, #6a11cb, #2575fc);
    font-family: 'Poppins', sans-serif;
    overflow: hidden;
}
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
@keyframes float { 0% { transform: translate(0,0);} 100% { transform: translate(-50px,-50px);} }

.container {
    position: relative;
    z-index: 1;
    animation: fadeSlideIn 1s ease both;
}
@keyframes fadeSlideIn {
    from { opacity: 0; transform: translateY(30px) scale(0.95); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}

.card {
    background: #fff;
    padding: 40px 30px;
    border-radius: 15px;
    box-shadow: 0 15px 40px rgba(0,0,0,0.3);
    text-align: center;
    transition: transform 0.4s ease, box-shadow 0.4s ease;
    perspective: 1000px;
    width: 380px;
}
.card:hover {
    transform: rotateY(6deg) rotateX(-6deg) scale(1.03);
    box-shadow: 0 25px 60px rgba(0,0,0,0.4);
}

h2 {
    color: #2575fc;
    margin-bottom: 25px;
    background: linear-gradient(45deg, #6a11cb, #2575fc);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

/* ===== INPUT GROUPS ===== */
.input-group {
    position: relative;
    margin-bottom: 20px;
    text-align: left;
}
label {
    display: block;
    margin-bottom: 6px;
    color: #333;
    font-weight: 600;
    font-size: 14px;
}

/* Animated gradient input borders */
.input-gradient {
    position: relative;
}
.input-gradient input {
    width: 100%;
    padding: 12px 40px 12px 12px;
    border-radius: 8px;
    outline: none;
    font-size: 14px;
    border: 2px solid transparent;
    background: #f9f9f9;
    transition: all 0.3s ease;
}
.input-gradient::before {
    content: '';
    position: absolute;
    top: -2px; left: -2px; right: -2px; bottom: -2px;
    border-radius: 10px;
    padding: 2px;
    background: linear-gradient(45deg, #6a11cb, #2575fc, #6a11cb, #2575fc);
    background-size: 400% 400%;
    z-index: -1;
    filter: blur(4px);
    opacity: 0;
    transition: opacity 0.3s;
    animation: gradientShift 8s ease infinite;
}
@keyframes gradientShift {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}
.input-gradient input:focus + label,
.input-gradient input:not(:placeholder-shown) + label {
    color: #2575fc;
}
.input-gradient input:focus {
    border-color: transparent;
}
.input-gradient input:focus ~ ::before,
.input-gradient input:focus::before {
    opacity: 1;
}
.input-gradient:focus-within::before {
    opacity: 1;
}

/* PASSWORD TOGGLE */
.eye-toggle {
    position: absolute;
    right: 12px;
    top: 38px;
    transform: translateY(-50%);
    cursor: pointer;
    color: #888;
    user-select: none;
    font-size: 18px;
    transition: 0.3s;
}
.eye-toggle:hover { color: #2575fc; }

/* BUTTON */
button {
    width: 100%;
    padding: 12px;
    background: linear-gradient(135deg, #6a11cb, #2575fc);
    border: none;
    border-radius: 8px;
    color: #fff;
    font-size: 16px;
    cursor: pointer;
    font-weight: 600;
    transition: 0.4s;
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}
button:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.3);
}

/* ERROR BOX */
.errors {
    background: #ffe5e5;
    color: #c0392b;
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 15px;
    animation: shake 0.5s;
}
@keyframes shake {
    0%,100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    50% { transform: translateX(5px); }
    75% { transform: translateX(-5px); }
}
p { margin-top: 10px; font-size: 14px; }
a { color: #6a11cb; text-decoration: none; }
a:hover { text-decoration: underline; }

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
    <div class="card">
        <h2>Register</h2>
        <?php if (!empty($error)) echo "<div class='errors'>$error</div>"; ?>

        <form method="POST">
            <div class="input-group input-gradient">
                <label>Name</label>
                <input type="text" name="name" required placeholder=" ">
            </div>

            <div class="input-group input-gradient">
                <label>Email</label>
                <input type="email" name="email" required placeholder=" ">
            </div>

            <div class="input-group input-gradient">
                <label>Password</label>
                <input type="password" id="password" name="password" required placeholder=" ">
                <span class="eye-toggle" onclick="togglePassword()">👁️</span>
            </div>

            <button type="submit" name="register">Register</button>
            <p>Already have an account? <a href="login.php">Login</a></p>
        </form>
    </div>
</div>

<script>
function togglePassword() {
    const passField = document.getElementById('password');
    const eye = document.querySelector('.eye-toggle');
    if (passField.type === 'password') {
        passField.type = 'text';
        eye.textContent = '🙈';
    } else {
        passField.type = 'password';
        eye.textContent = '👁️';
    }
}
</script>

</body>
</html>
