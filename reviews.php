<?php
// =======================================
// SMART DOCTOR SYSTEM — ADMIN REVIEWS PAGE
// =======================================
session_start();
include("config/db.php");

// Only admin can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: adminlogin.php");
    exit;
}

// Fetch reviews with doctor & patient names
$query = "
    SELECT r.*, d.name AS doctor_name, u.name AS patient_name
    FROM reviews r
    JOIN appointments a ON r.appointment_id = a.id
    JOIN doctors d ON a.doctor_id = d.id
    JOIN users u ON r.patient_id = u.id
    ORDER BY r.created_at DESC
";
$reviews = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Reviews | Smart Doctor</title>
<link rel="stylesheet" href="assets/css/style.css">
<style>
/* Fonts */
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@500;700&family=Roboto:wght@400;500&display=swap');

body {
    font-family: 'Roboto', sans-serif;
    background: #f4f6f8;
    margin: 0;
    color: #2c3e50;
}

/* Navigation */
nav {
    display:flex; justify-content:space-between; align-items:center;
    padding:15px 30px; background: linear-gradient(45deg,#2980b9,#3498db); color:white;
}
nav .logo { font-weight:700; font-size:22px; font-family:'Montserrat',sans-serif; }
nav ul { list-style:none; display:flex; gap:20px; }
nav ul li a { color:white; text-decoration:none; font-weight:500; transition: all 0.3s ease; }
nav ul li a.active, nav ul li a:hover { color:#ffec59; text-shadow: 1px 1px 5px #000; }

/* Container */
.container { max-width: 1000px; margin:40px auto; padding:20px; }
h2 { font-family:'Montserrat',sans-serif; font-weight:700; color:#34495e; margin-bottom:20px; }

/* Table */
table { width:100%; border-collapse: collapse; box-shadow: 0 8px 25px rgba(0,0,0,0.1); border-radius:12px; overflow:hidden; }
th, td { padding:12px 15px; text-align:center; transition: all 0.3s ease; }
th { background: linear-gradient(45deg,#27ae60,#2ecc71); color:#fff; font-family:'Montserrat',sans-serif; }
tr { background:#fff; }
tr:nth-child(even) { background:#f9f9f9; }
tr:hover { background:#dff9fb; transform: scale(1.01); box-shadow: 0 4px 20px rgba(0,0,0,0.08); }

/* Star rating */
.star {
    font-size: 18px;
    display: inline-block;
    color: #f1c40f;
    transition: transform 0.3s, color 0.3s;
}
.star:hover { transform: scale(1.3); color: #f39c12; }

/* Animations */
@keyframes fadeIn { from {opacity:0; transform:translateY(15px);} to {opacity:1; transform:translateY(0);} }
tr { animation: fadeIn 0.5s ease; }

/* Footer */
footer { text-align: center; margin-top: 50px; padding: 20px; background: linear-gradient(45deg,#2980b9,#3498db); color: #fff; border-radius: 12px; }

/* Scrollable table on smaller screens */
@media(max-width:768px){
    table { display:block; overflow-x:auto; }
}
</style>
</head>
<body>

<nav>
    <div class="logo">🩺 Smart Doctor</div>
    <ul>
        <li><a href="admin_dashboard.php">Dashboard</a></li>
        <li><a href="manage_doctors.php">Doctors</a></li>
        <li><a href="manage_patients.php">Patients</a></li>
        <li><a href="appointment.php">Appointments</a></li>
        <li><a href="reviews.php" class="active">Reviews</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</nav>

<div class="container">
    <h2>⭐ Patient Reviews for Doctors</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Doctor</th>
            <th>Patient</th>
            <th>Rating</th>
            <th>Feedback</th>
            <th>Submitted At</th>
        </tr>
        <?php if ($reviews->num_rows > 0): ?>
            <?php while ($row = $reviews->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['doctor_name']) ?></td>
                    <td><?= htmlspecialchars($row['patient_name']) ?></td>
                    <td>
                        <?php
                        // Show stars
                        for ($i = 1; $i <= 5; $i++) {
                            echo $i <= $row['rating'] ? '<span class="star">★</span>' : '<span class="star">☆</span>';
                        }
                        ?>
                    </td>
                    <td><?= nl2br(htmlspecialchars($row['feedback'])) ?></td>
                    <td><?= $row['created_at'] ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="6">No reviews found.</td></tr>
        <?php endif; ?>
    </table>
</div>

</body>
</html>
