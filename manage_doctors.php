<?php
// =======================================
// SMART DOCTOR SYSTEM — MANAGE_DOCTORS.PHP
// =======================================
session_start();
include("config/db.php");

// --- ACCESS CONTROL ---
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php?redirect=manage_doctors.php");
    exit;
}

// --- DELETE DOCTOR ---
if (isset($_GET['delete'])) {
    $doctor_id = intval($_GET['delete']);
    $conn->begin_transaction();
    try {
        // Delete from both doctors and users
        $stmt = $conn->prepare("DELETE FROM doctors WHERE id = ?");
        $stmt->bind_param("i", $doctor_id);
        $stmt->execute();

        $stmt2 = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt2->bind_param("i", $doctor_id);
        $stmt2->execute();

        $conn->commit();
        header("Location: manage_doctors.php?msg=deleted");
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        die("Error deleting doctor: " . $e->getMessage());
    }
}

// --- ADD NEW DOCTOR ---
if (isset($_POST['add_doctor'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $specialization = trim($_POST['specialization']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $conn->begin_transaction();
    try {
        // 1️⃣ Insert into users first to get the ID
        $stmtUser = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'doctor')");
        $stmtUser->bind_param("sss", $name, $email, $password);
        $stmtUser->execute();
        $user_id = $conn->insert_id; // same ID for both

        // 2️⃣ Insert into doctors using that same ID
        $stmtDoctor = $conn->prepare("INSERT INTO doctors (id, name, email, specialization) VALUES (?, ?, ?, ?)");
        $stmtDoctor->bind_param("isss", $user_id, $name, $email, $specialization);
        $stmtDoctor->execute();

        $conn->commit();
        header("Location: manage_doctors.php?msg=added");
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        die("Error adding doctor: " . $e->getMessage());
    }
}

// --- UPDATE DOCTOR ---
if (isset($_POST['update_doctor'])) {
    $id = intval($_POST['doctor_id']);
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $specialization = trim($_POST['specialization']);

    $conn->begin_transaction();
    try {
        // Update doctors
        $stmt = $conn->prepare("UPDATE doctors SET name = ?, email = ?, specialization = ? WHERE id = ?");
        $stmt->bind_param("sssi", $name, $email, $specialization, $id);
        $stmt->execute();

        // Update users
        $stmt2 = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ? AND role = 'doctor'");
        $stmt2->bind_param("ssi", $name, $email, $id);
        $stmt2->execute();

        $conn->commit();
        header("Location: manage_doctors.php?msg=updated");
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        die("Error updating doctor: " . $e->getMessage());
    }
}

// --- FETCH ALL DOCTORS ---
$doctors = $conn->query("SELECT * FROM doctors ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Doctors | Smart Doctor</title>
<link rel="stylesheet" href="assets/css/style.css">
<style>
<style>
/* Container & card */
.container { max-width: 1100px; margin: 40px auto; }
h2 { margin-bottom: 20px; color: #2c3e50; text-align:center; }

/* Card styling */
.card { 
    padding: 20px; 
    background: #fff; 
    border-radius: 12px; 
    box-shadow: 0 8px 20px rgba(0,0,0,0.1); 
    margin-bottom: 20px; 
    transition: transform 0.3s, box-shadow 0.3s;
}
.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 30px rgba(0,0,0,0.2);
}

/* Table styling */
.table { width: 100%; border-collapse: collapse; }
.table th, .table td { padding: 12px; text-align: left; transition: all 0.3s ease; }
.table th { background: linear-gradient(45deg,#27ae60,#2ecc71); color: #fff; }
.table tr:nth-child(even) { background: #f3f3f3; }
.table tr:hover { background: #d1f0d1; transform: scale(1.02); }

/* Buttons */
.btn { padding: 8px 14px; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight:600; transition: all 0.3s ease; }
.btn-add { background: linear-gradient(45deg,#27ae60,#2ecc71); color: #fff; }
.btn-add:hover { transform: scale(1.05); box-shadow: 0 6px 15px rgba(39,174,96,0.4); }
.btn-edit { background: linear-gradient(45deg,#3498db,#2980b9); color: #fff; }
.btn-edit:hover { transform: scale(1.05); box-shadow: 0 6px 15px rgba(52,152,219,0.4); }
.btn-delete { background: linear-gradient(45deg,#e74c3c,#c0392b); color: #fff; }
.btn-delete:hover { transform: scale(1.05); box-shadow: 0 6px 15px rgba(231,76,60,0.4); }

/* Form inputs */
form input, form select {
    width: 100%; padding: 10px; margin-bottom: 12px;
    border: 1px solid #ccc; border-radius: 6px;
    transition: all 0.3s ease;
}
form input:focus, form select:focus {
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

.modal-content h3 { margin-bottom: 20px; color: #2c3e50; text-align:center; }
.close-btn {
    position: absolute; right: 12px; top: 12px; font-size: 20px; cursor: pointer; color: #333;
    transition: transform 0.3s ease, color 0.3s ease;
}
.close-btn:hover { transform: rotate(90deg); color: #e74c3c; }

/* Navigation */
nav {
    display:flex; justify-content:space-between; align-items:center;
    padding:15px 30px; background: #2980b9; color:white;
}
nav .logo { font-weight:700; font-size:22px; }
nav ul { list-style:none; display:flex; gap:20px; }
nav ul li a { color:white; text-decoration:none; font-weight:500; transition: all 0.3s ease; }
nav ul li a.active, nav ul li a:hover { color:#ffec59; text-shadow: 1px 1px 5px #000; }
</style>
<script>
function openAddModal() {
    document.getElementById('addModal').style.display = 'flex';
}
function openEditModal(id, name, email, specialization) {
    document.getElementById('editModal').style.display = 'flex';
    document.getElementById('doctor_id').value = id;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_email').value = email;
    document.getElementById('edit_specialization').value = specialization;
    document.getElementById('original_email').value = email;
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
        <li><a href="manage_doctors.php" class="active">Doctors</a></li>
        <li><a href="manage_patients.php">Patients</a></li>
        <li><a href="appointment.php">Appointments</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</nav>

<div class="container">
    <h2>🩺 Manage Doctors</h2>

    <button class="btn btn-add" onclick="openAddModal()">➕ Add Doctor</button>

    <div class="card">
        <table class="table">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Specialization</th>
                <th>Actions</th>
            </tr>
            <?php if ($doctors->num_rows > 0): ?>
                <?php while ($row = $doctors->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= htmlspecialchars($row['specialization']) ?></td>
                        <td>
                            <button class="btn btn-edit" onclick="openEditModal('<?= $row['id'] ?>', '<?= htmlspecialchars($row['name'], ENT_QUOTES) ?>', '<?= htmlspecialchars($row['email'], ENT_QUOTES) ?>', '<?= htmlspecialchars($row['specialization'], ENT_QUOTES) ?>')">Edit</button>
                            <a href="?delete=<?= $row['id'] ?>" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this doctor?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="5">No doctors found.</td></tr>
            <?php endif; ?>
        </table>
    </div>
</div>

<!-- Add Doctor Modal -->
<div class="modal" id="addModal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal('addModal')">&times;</span>
        <h3>➕ Add New Doctor</h3>
        <form method="POST">
            <input type="text" name="name" placeholder="Doctor Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="text" name="specialization" placeholder="Specialization" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="add_doctor" class="btn btn-add">Add Doctor</button>
        </form>
    </div>
</div>

<!-- Edit Doctor Modal -->
<div class="modal" id="editModal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal('editModal')">&times;</span>
        <h3>✏️ Edit Doctor</h3>
        <form method="POST">
            <input type="hidden" name="doctor_id" id="doctor_id">
            <input type="hidden" name="original_email" id="original_email">
            <input type="text" name="name" id="edit_name" placeholder="Doctor Name" required>
            <input type="email" name="email" id="edit_email" placeholder="Email" required>
            <input type="text" name="specialization" id="edit_specialization" placeholder="Specialization" required>
            <button type="submit" name="update_doctor" class="btn btn-edit">Update Doctor</button>
        </form>
    </div>
</div>

</body>
</html>
