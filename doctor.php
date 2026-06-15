<?php
session_start();
include("config/db.php");
$doctors = $conn->query("SELECT * FROM doctors");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Doctors | Smart Health</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<nav>
<div class="logo">🩺 Smart Health</div>
<ul>
<li><a href="patient_profile.php" class="active">Profile</a></li>
<li><a href="book.php">Book Appointment</a></li>
<li><a href="logout.php">Logout</a></li>
</ul>
</nav>
<div class="container">
<div class="card">
<h2>Available Doctors</h2>
<table class="table">
<tr><th>Name</th><th>Specialization</th></tr>
<?php while($row=$doctors->fetch_assoc()): ?>
<tr><td><?=htmlspecialchars($row['name'])?></td><td><?=htmlspecialchars($row['specialization'])?></td></tr>
<?php endwhile; ?>
</table>
</div>
</div>
</body>
</html>
