<?php
// =======================================
// SMART DOCTOR SYSTEM — MANAGE_PATIENTS.PHP
// =======================================
session_start();
include("config/db.php");

// --- ACCESS CONTROL ---
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php?redirect=manage_patients.php");
    exit;
}

// --- DELETE PATIENT ---
if (isset($_GET['delete'])) {
    $patient_id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'patient'");
    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    header("Location: manage_patients.php?msg=deleted");
    exit;
}

// --- ADD PATIENT ---
if (isset($_POST['add_patient'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'patient')");
    $stmt->bind_param("sss", $name, $email, $password);
    $stmt->execute();

    header("Location: manage_patients.php?msg=added");
    exit;
}

// --- UPDATE PATIENT ---
if (isset($_POST['update_patient'])) {
    $id = intval($_POST['patient_id']);
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);

    $stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ? AND role = 'patient'");
    $stmt->bind_param("ssi", $name, $email, $id);
    $stmt->execute();

    header("Location: manage_patients.php?msg=updated");
    exit;
}

// --- FETCH ALL PATIENTS ---
$patients = $conn->query("SELECT * FROM users WHERE role = 'patient' ORDER BY id DESC");

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Patients | Smart Doctor</title>
<link rel="stylesheet" href="assets/css/style.css">
<!-- Add Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&family=Montserrat:wght@500;700&display=swap" rel="stylesheet">

<style>
/* Fonts */
body {
    font-family: 'Roboto', sans-serif;
    background: #f4f6f8;
    color: #2c3e50;
    margin: 0;
    transition: all 0.3s ease;
}

/* Container & header */
.container { max-width: 1100px; margin: 40px auto; }
h2 { margin-bottom: 20px; color: #2c3e50; font-family: 'Montserrat', sans-serif; text-align:center; }

/* Card styling */
.card { 
    padding: 20px; 
    background: #fff; 
    border-radius: 12px; 
    box-shadow: 0 8px 25px rgba(0,0,0,0.1); 
    margin-bottom: 20px; 
    transition: transform 0.3s, box-shadow 0.3s;
}
.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 30px rgba(0,0,0,0.2);
}

/* Table styling */
.table { width: 100%; border-collapse: collapse; }
.table th, .table td { padding: 12px; text-align: left; transition: all 0.3s ease; font-family: 'Roboto', sans-serif; }
.table th { background: linear-gradient(45deg,#27ae60,#2ecc71); color: #fff; text-transform: uppercase; letter-spacing: 1px; }
.table tr:nth-child(even) { background: #f3f3f3; }
.table tr:hover { background: #d1f0d1; transform: scale(1.02); }

/* Buttons */
.btn { 
    padding: 8px 14px; 
    border: none; 
    border-radius: 8px; 
    cursor: pointer; 
    font-size: 14px; 
    font-weight:600; 
    transition: all 0.3s ease; 
    font-family: 'Montserrat', sans-serif;
}
.btn-add { 
    background: linear-gradient(45deg,#27ae60,#2ecc71); 
    color: #fff; 
    box-shadow: 0 5px 15px rgba(39,174,96,0.4);
}
.btn-add:hover { 
    transform: scale(1.05); 
    box-shadow: 0 10px 25px rgba(39,174,96,0.6);
}
.btn-edit { 
    background: linear-gradient(45deg,#3498db,#2980b9); 
    color: #fff; 
    box-shadow: 0 5px 15px rgba(52,152,219,0.4);
}
.btn-edit:hover { 
    transform: scale(1.05); 
    box-shadow: 0 10px 25px rgba(52,152,219,0.6);
}
.btn-delete { 
    background: linear-gradient(45deg,#e74c3c,#c0392b); 
    color: #fff; 
    box-shadow: 0 5px 15px rgba(231,76,60,0.4);
}
.btn-delete:hover { 
    transform: scale(1.05); 
    box-shadow: 0 10px 25px rgba(231,76,60,0.6);
}

/* Form inputs */
form input {
    width: 100%; 
    padding: 12px; 
    margin-bottom: 12px;
    border: 1px solid #ccc; 
    border-radius: 8px; 
    transition: all 0.3s ease;
}
form input:focus {
    border-color: #3498db;
    box-shadow: 0 0 8px rgba(52,152,219,0.3);
    outline: none;
}

/* Modal */
.modal {
    display: none; 
    position: fixed; 
    top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0,0,0,0.6); 
    justify-content: center; 
    align-items: center;
    animation: fadeIn 0.5s forwards;
}
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.modal-content {
    background: #fff; 
    padding: 25px; 
    border-radius: 12px; 
    width: 400px; 
    position: relative;
    transform: scale(0.7);
    animation: scaleIn 0.4s forwards;
}
@keyframes scaleIn {
    to { transform: scale(1); }
}

.modal-content h3 { margin-bottom: 20px; color: #2c3e50; font-family: 'Montserrat', sans-serif; text-align:center; }
.close-btn {
    position: absolute; right: 12px; top: 12px; font-size: 22px; cursor: pointer; color: #333;
    transition: transform 0.3s ease, color 0.3s ease;
}
.close-btn:hover { transform: rotate(90deg); color: #e74c3c; }

/* Navigation */
nav {
    display:flex; justify-content:space-between; align-items:center;
    padding:15px 30px; background: linear-gradient(45deg,#3498db,#2980b9); color:white;
}
nav .logo { font-weight:700; font-size:22px; font-family: 'Montserrat', sans-serif; }
nav ul { list-style:none; display:flex; gap:20px; }
nav ul li a { color:white; text-decoration:none; font-weight:500; transition: all 0.3s ease; font-family: 'Roboto', sans-serif; }
nav ul li a.active, nav ul li a:hover { color:#ffec59; text-shadow: 1px 1px 5px #000; }
</style>
<script>
function openAddModal() {
    document.getElementById('addModal').style.display = 'flex';
}
function openEditModal(id, name, email) {
    document.getElementById('editModal').style.display = 'flex';
    document.getElementById('patient_id').value = id;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_email').value = email;
}
function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}
</script>
</head>
<body>

<nav>
    <div class="logo">🩺 Smart Doctor</div>
    <ul>
        <li><a href="admin_dashboard.php">Dashboard</a></li>
        <li><a href="manage_doctors.php">Doctors</a></li>
        <li><a href="manage_patients.php" class="active">Patients</a></li>
        <li><a href="appointment.php">Appointments</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</nav>

<div class="container">
    <h2>👤 Manage Patients</h2>

    <button class="btn btn-add" onclick="openAddModal()">➕ Add Patient</button>

    <div class="card">
        <table class="table">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Actions</th>
            </tr>
            <?php if ($patients->num_rows > 0): ?>
                <?php while ($row = $patients->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td>
                            <button class="btn btn-edit" onclick="openEditModal('<?= $row['id'] ?>', '<?= htmlspecialchars($row['name'], ENT_QUOTES) ?>', '<?= htmlspecialchars($row['email'], ENT_QUOTES) ?>')">Edit</button>
                            <a href="?delete=<?= $row['id'] ?>" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this patient?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="4">No patients found.</td></tr>
            <?php endif; ?>
        </table>
    </div>
</div>

<!-- Add Patient Modal -->
<div class="modal" id="addModal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal('addModal')">&times;</span>
        <h3>➕ Add New Patient</h3>
        <form method="POST">
            <input type="text" name="name" placeholder="Patient Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="add_patient" class="btn btn-add">Add Patient</button>
        </form>
    </div>
</div>

<!-- Edit Patient Modal -->
<div class="modal" id="editModal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal('editModal')">&times;</span>
        <h3>✏️ Edit Patient</h3>
        <form method="POST">
            <input type="hidden" name="patient_id" id="patient_id">
            <input type="text" name="name" id="edit_name" placeholder="Patient Name" required>
            <input type="email" name="email" id="edit_email" placeholder="Email" required>
            <button type="submit" name="update_patient" class="btn btn-edit">Update Patient</button>
        </form>
    </div>
</div>

</body>
</html>
