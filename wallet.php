<?php
session_start();
include("config/db.php");

// Only patients can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle adding money
if (isset($_POST['add_money'])) {
    $amount = floatval($_POST['amount']);
    if ($amount > 0) {
        // Update wallet balance
        $conn->query("UPDATE wallets SET balance = balance + $amount WHERE user_id = $user_id");

        // Optional: insert transaction history
        $conn->query("INSERT INTO wallet_transactions (user_id, amount, type, description, created_at) 
                      VALUES ($user_id, $amount, 'credit', 'Wallet top-up', NOW())");

        $msg = "₹$amount added to your wallet successfully!";
    } else {
        $error = "Enter a valid amount.";
    }
}

// Fetch current balance
$wallet = $conn->query("SELECT balance FROM wallets WHERE user_id = $user_id");
$balance = $wallet->num_rows ? $wallet->fetch_assoc()['balance'] : '0.00';

// Fetch transaction history (optional)
$transactions = $conn->query("SELECT * FROM wallet_transactions WHERE user_id = $user_id ORDER BY created_at DESC");

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Wallet | Smart Doctor</title>
<link rel="stylesheet" href="assets/css/style.css">
<style>
.container { max-width:800px; margin:40px auto; }
.card { background:#fff; padding:20px; border-radius:12px; box-shadow:0 6px 20px rgba(0,0,0,0.1); margin-bottom:30px; }
h2 { color:#2c3e50; margin-bottom:20px; }
.balance { font-size:28px; font-weight:700; color:#27ae60; }
form input { padding:10px; width:200px; border:1px solid #ccc; border-radius:8px; margin-right:10px; }
form button { padding:10px 20px; border:none; border-radius:8px; background:#27ae60; color:#fff; cursor:pointer; font-weight:600; }
form button:hover { background:#2ecc71; }
table { width:100%; border-collapse:collapse; margin-top:20px; }
table th, table td { padding:12px; text-align:left; border-bottom:1px solid #ddd; }
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
        <a href="patient_dashboard.php">Dashboard</a>
        <a href="patient_profile.php">Profile</a>
        <a href="book.php">Book Appointment</a>
        <a href="wallet.php">Wallet</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>

<div class="container">
    <div class="card">
        <h2>💰 Wallet Balance</h2>
        <div class="balance">₹ <?= number_format($balance,2) ?></div>

        <?php if(isset($msg)) echo "<div class='success'>$msg</div>"; ?>
        <?php if(isset($error)) echo "<div class='error'>$error</div>"; ?>

        <form method="POST">
            <input type="number" name="amount" step="0.01" placeholder="Enter amount to add" required>
            <button type="submit" name="add_money">Add Money</button>
        </form>
    </div>

    <div class="card">
        <h2>💳 Transaction History</h2>
        <?php if($transactions->num_rows > 0): ?>
        <table>
            <tr>
                <th>Amount</th>
                <th>Type</th>
                <th>Description</th>
                <th>Date</th>
            </tr>
            <?php while($row = $transactions->fetch_assoc()): ?>
            <tr>
                <td>₹ <?= number_format($row['amount'],2) ?></td>
                <td><?= ucfirst($row['type']) ?></td>
                <td><?= htmlspecialchars($row['description']) ?></td>
                <td><?= $row['created_at'] ?></td>
            </tr>
            <?php endwhile; ?>
        </table>
        <?php else: ?>
            <p>No transactions yet.</p>
        <?php endif; ?>
    </div>
</div>

<footer style="text-align:center;margin-top:50px;padding:20px;background:#2c3e50;color:#fff;border-radius:12px;">
    <p>© <?= date('Y') ?> Smart Doctor Appointment & Health Record System</p>
</footer>

</body>
</html>
