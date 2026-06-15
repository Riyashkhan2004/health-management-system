<?php
// =======================================
// SMART DOCTOR SYSTEM — PATIENT_PROFILE.PHP
// =======================================
session_start();
include("config/db.php");

// Only patients can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
    header("Location: login.php");
    exit;
}

$patient_id = $_SESSION['user_id'];

// Fetch patient info
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "Error: Patient record not found.";
    exit;
}

$patient = $result->fetch_assoc();

// Upcoming appointments
$upcoming = $conn->query("
    SELECT a.*, d.name AS doctor_name, d.specialization
    FROM appointments a
    JOIN doctors d ON a.doctor_id = d.id
    WHERE a.patient_id = $patient_id AND a.appointment_date >= CURDATE()
    ORDER BY a.appointment_date ASC, a.appointment_time ASC
");

// Past appointments
$past = $conn->query("
    SELECT a.*, d.name AS doctor_name, d.specialization
    FROM appointments a
    JOIN doctors d ON a.doctor_id = d.id
    WHERE a.patient_id = $patient_id AND a.appointment_date < CURDATE()
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Patient Profile | Smart Doctor</title>
<link rel="stylesheet" href="assets/css/style.css">
<style>
/* ===== GLOBAL STYLES ===== */
body {
    font-family: 'Poppins', sans-serif;
    margin: 0;
    background: linear-gradient(135deg,#e3fdfd,#ffe6fa);
    animation: fadeInPage 0.8s ease forwards;
}
@keyframes fadeInPage { from{opacity:0;} to{opacity:1;} }

nav {
    background: linear-gradient(90deg,#2ecc71,#27ae60);
    padding: 14px 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    color: #fff;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    position: sticky;
    top: 0;
    z-index: 100;
}
nav ul { list-style:none; display:flex; gap:25px; margin:0; padding:0; }
nav a { color:#fff; text-decoration:none; font-weight:600; position: relative; transition:0.3s; }
nav a.active::after, nav a:hover::after {
    content: '';
    position: absolute;
    width: 100%;
    height: 2px;
    background: #f1c40f;
    bottom: -4px;
    left: 0;
    border-radius: 2px;
    animation: slideLine 0.3s ease forwards;
}
@keyframes slideLine { from {width:0;} to{width:100%;} }

/* ===== CONTAINER & CARDS ===== */
.container { max-width:1100px; margin:40px auto; padding:20px; }
.card {
    background:#fff;
    padding:25px 30px;
    border-radius:16px;
    box-shadow:0 8px 25px rgba(0,0,0,0.1);
    margin-bottom:30px;
    transition: 0.3s all;
}
.card:hover { transform:translateY(-5px); box-shadow:0 15px 30px rgba(0,0,0,0.15); }

.profile-card { display:flex; justify-content: space-between; align-items: center; flex-wrap: wrap; }
.profile-info h2 { margin:0 0 10px; color:#2c3e50; font-size:28px; animation: fadeSlideIn 1s ease forwards; }
.profile-info p { margin:5px 0; color:#555; }

/* ===== PROFILE IMAGE ===== */
.profile-card img {
    width:140px;
    border-radius:50%;
    box-shadow:0 8px 25px rgba(0,0,0,0.2);
    transition: transform 0.3s;
}
.profile-card img:hover { transform: scale(1.05) rotate(-2deg); }

/* ===== TABLES ===== */
table { width:100%; border-collapse: collapse; margin-top:20px; animation: fadeSlideIn 0.8s ease forwards; }
th, td { padding:12px 15px; text-align:center; border-bottom:1px solid #ddd; vertical-align: middle; }
th { background: linear-gradient(135deg,#2ecc71,#27ae60); color:#fff; font-weight:600; }
tr:hover { background: rgba(241,196,15,0.1); transform:scale(1.01); transition:0.2s; border-radius:6px; }
td .doctor-message { background:#f0f8ff; border-left:4px solid #3498db; border-radius:6px; padding:10px; font-size:14px; text-align:left; }

/* ===== STATUS COLORS ===== */
.status-approved { color: #27ae60; font-weight:700; }
.status-pending { color: #e67e22; font-weight:700; }
.status-canceled { color:#e74c3c; font-weight:700; }
.status-completed { color:#16a085; font-weight:700; }

.row-approved { background-color: #eafaf1; }
.row-pending { background-color: #fff8e5; }
.row-canceled { background-color: #fdecea; }
.row-completed { background-color: #e9f7ef; }

/* ===== ACTION BUTTONS ===== */
.action-buttons { display:flex; gap:8px; justify-content:center; align-items:center; }
.btn-edit, .btn-delete {
    padding:8px 16px;
    border-radius:8px;
    font-weight:600;
    font-size:14px;
    cursor:pointer;
    border:none;
    transition:0.3s;
}
.btn-edit { background: linear-gradient(135deg,#3498db,#2980b9); color:#fff; }
.btn-edit:hover { transform: translateY(-3px) scale(1.05); box-shadow:0 6px 20px rgba(0,0,0,0.2); }
.btn-delete { background: linear-gradient(135deg,#e74c3c,#c0392b); color:#fff; }
.btn-delete:hover { transform: translateY(-3px) scale(1.05); box-shadow:0 6px 20px rgba(0,0,0,0.2); }

/* ===== MODALS ===== */
.modal-overlay { position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.4); display:none; align-items:center; justify-content:center; z-index:999; }
.modal-box { background:#fff; padding:25px 30px; border-radius:12px; box-shadow:0 10px 25px rgba(0,0,0,0.3); width:380px; animation: popupFade 0.3s ease forwards; }
.modal-box h3 { margin:0 0 10px; color:#2c3e50; font-weight:700; }
.modal-box p { color:#555; margin:10px 0 20px; }
.modal-buttons { display:flex; justify-content:center; gap:12px; }
.btn-confirm { background: linear-gradient(135deg,#e74c3c,#c0392b); color:#fff; border:none; padding:10px 18px; border-radius:8px; cursor:pointer; font-weight:700; transition:0.3s; }
.btn-confirm:hover { transform: scale(1.05); box-shadow:0 6px 20px rgba(0,0,0,0.2); }
.btn-cancel { background: #bdc3c7; color:#2c3e50; border:none; padding:10px 18px; border-radius:8px; cursor:pointer; font-weight:700; transition:0.3s; }
.btn-cancel:hover { background:#95a5a6; transform: scale(1.05); }

/* ===== ANIMATIONS ===== */
@keyframes fadeSlideIn { from {opacity:0; transform:translateY(20px);} to {opacity:1; transform:translateY(0);} }
@keyframes popupFade { from {transform:scale(0.9); opacity:0;} to {transform:scale(1); opacity:1;} }
</style>

</head>
<body>

<nav>
    <div class="logo">🩺 Smart Doctor</div>
    <ul>
        <li><a href="patient_dashboard.php">Dashboard</a></li>
        <li><a href="book.php">Book Appointment</a></li>
        <li><a href="patient_profile.php" class="active">Profile</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</nav>

<div class="container">
    <div class="card profile-card">
        <div class="profile-info">
            <h2>👤 <?= htmlspecialchars($patient['name'] ?? 'N/A') ?></h2>
            <p><strong>Email:</strong> <?= htmlspecialchars($patient['email'] ?? 'N/A') ?></p>
            <p><strong>Patient ID:</strong> <?= $patient['id'] ?? 'N/A' ?></p>
        </div>
        <div>
            <img src="assets/img/patient.png" alt="Profile" style="width:120px;border-radius:50%;box-shadow:0 6px 20px rgba(0,0,0,0.1)">
        </div>
    </div>

    <!-- Upcoming Appointments -->
    <div class="appointments card">
        <h2>📅 Upcoming Appointments</h2>
        <table>
            <tr>
                <th>Doctor</th>
                <th>Specialization</th>
                <th>Date</th>
                <th>Time</th>
                <th>Status & Message</th>
                <th>Actions</th>
            </tr>
            <?php if ($upcoming->num_rows > 0): ?>
                <?php while ($row = $upcoming->fetch_assoc()): ?>
                    <?php
                        $status_raw = strtolower(trim($row['status'] ?? ''));
                        if (in_array($status_raw, ['approved', 'accept', 'accepted'])) {
                            $status_key = 'approved';
                        } elseif (in_array($status_raw, ['canceled', 'cancelled', 'rejected'])) {
                            $status_key = 'canceled';
                        } elseif ($status_raw === 'completed') {
                            $status_key = 'completed';
                        } else {
                            $status_key = 'pending';
                        }
                    ?>
                    <tr class="row-<?= $status_key ?>">
                        <td><?= htmlspecialchars($row['doctor_name']) ?></td>
                        <td><?= htmlspecialchars($row['specialization']) ?></td>
                        <td><?= htmlspecialchars($row['appointment_date']) ?></td>
                        <td><?= htmlspecialchars($row['appointment_time']) ?></td>
                        <td>
                            <span class="status-<?= $status_key ?>"><?= ucfirst($row['status']) ?></span>
                            <?php if (!empty($row['doctor_message'])): ?>
                                <div class="doctor-message">
                                    <strong>Message from Doctor:</strong><br>
                                    <?= nl2br(htmlspecialchars($row['doctor_message'])) ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <?php if ($status_raw === 'completed'): ?>
                                    <button class="btn-edit" onclick="openReviewModal(<?= $row['id'] ?>, '<?= htmlspecialchars($row['doctor_name']) ?>')">⭐ Review</button>
                                <?php else: ?>
                                    <a href="edit_appointment.php?id=<?= $row['id'] ?>" class="btn-edit">📝 Edit</a>
                                <?php endif; ?>
                                <a href="#" class="btn-delete" onclick="confirmDelete('delete_appointment.php?id=<?= $row['id'] ?>'); return false;">❌ Delete</a>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6">No upcoming appointments.</td></tr>
            <?php endif; ?>
        </table>
    </div>

    <!-- Past Appointments -->
    <div class="appointments card">
        <h2>📖 Past Appointments</h2>
        <table>
            <tr>
                <th>Doctor</th>
                <th>Specialization</th>
                <th>Date</th>
                <th>Time</th>
                <th>Status & Message</th>
                <th>Actions</th>
            </tr>
            <?php if ($past->num_rows > 0): ?>
                <?php while ($row = $past->fetch_assoc()): ?>
                    <?php
                        $status_raw = strtolower(trim($row['status'] ?? ''));
                        if (in_array($status_raw, ['approved', 'accept', 'accepted'])) {
                            $status_key = 'approved';
                        } elseif (in_array($status_raw, ['canceled', 'cancelled', 'rejected'])) {
                            $status_key = 'canceled';
                        } elseif ($status_raw === 'completed') {
                            $status_key = 'completed';
                        } else {
                            $status_key = 'pending';
                        }
                    ?>
                    <tr class="row-<?= $status_key ?>">
                        <td><?= htmlspecialchars($row['doctor_name']) ?></td>
                        <td><?= htmlspecialchars($row['specialization']) ?></td>
                        <td><?= htmlspecialchars($row['appointment_date']) ?></td>
                        <td><?= htmlspecialchars($row['appointment_time']) ?></td>
                        <td>
                            <span class="status-<?= $status_key ?>"><?= ucfirst($row['status']) ?></span>
                            <?php if (!empty($row['doctor_message'])): ?>
                                <div class="doctor-message">
                                    <strong>Message from Doctor:</strong><br>
                                    <?= nl2br(htmlspecialchars($row['doctor_message'])) ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <?php if ($status_raw === 'completed'): ?>
                                    <button class="btn-edit" onclick="openReviewModal(<?= $row['id'] ?>, '<?= htmlspecialchars($row['doctor_name']) ?>')">⭐ Review</button>
                                <?php else: ?>
                                    <a href="edit_appointment.php?id=<?= $row['id'] ?>" class="btn-edit">📝 Edit</a>
                                <?php endif; ?>
                                <a href="#" class="btn-delete" onclick="confirmDelete('delete_appointment.php?id=<?= $row['id'] ?>'); return false;">❌ Delete</a>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6">No past appointments.</td></tr>
            <?php endif; ?>
        </table>
    </div>
</div>

<!-- DELETE CONFIRMATION MODAL -->
<div id="confirmModal" class="modal-overlay">
  <div class="modal-box">
      <h3>❗ Confirm Deletion</h3>
      <p>Are you sure you want to delete this appointment?</p>
      <div class="modal-buttons">
          <button id="confirmYes" class="btn-confirm">Yes, Delete</button>
          <button id="confirmNo" class="btn-cancel">Cancel</button>
      </div>
  </div>
</div>

<!-- REVIEW MODAL -->
<div id="reviewModal" class="modal-overlay">
  <div class="modal-box">
      <h3>⭐ Review <span id="doctorName"></span></h3>
      <form id="reviewForm" action="submit_review.php" method="POST">
          <input type="hidden" name="appointment_id" id="reviewAppointmentId">
          <div style="margin-bottom:10px;">
              <label>Rating:</label>
              <select name="rating" required>
                  <option value="">Select</option>
                  <option value="1">1 ⭐</option>
                  <option value="2">2 ⭐</option>
                  <option value="3">3 ⭐</option>
                  <option value="4">4 ⭐</option>
                  <option value="5">5 ⭐</option>
              </select>
          </div>
          <div style="margin-bottom:15px;">
              <label>Feedback:</label><br>
              <textarea name="feedback" rows="4" style="width:100%;border-radius:6px;padding:8px;" placeholder="Write your feedback..." required></textarea>
          </div>
          <div class="modal-buttons">
              <button type="submit" class="btn-confirm">Submit Review</button>
              <button type="button" class="btn-cancel" onclick="closeReviewModal()">Cancel</button>
          </div>
      </form>
  </div>
</div>

<script>
let deleteUrl = '';

function confirmDelete(url) {
    deleteUrl = url;
    document.getElementById('confirmModal').style.display = 'flex';
}

document.getElementById('confirmYes').addEventListener('click', function() {
    window.location.href = deleteUrl;
});
document.getElementById('confirmNo').addEventListener('click', function() {
    document.getElementById('confirmModal').style.display = 'none';
});

function openReviewModal(appointmentId, doctorName) {
    document.getElementById('reviewAppointmentId').value = appointmentId;
    document.getElementById('doctorName').textContent = doctorName;
    document.getElementById('reviewModal').style.display = 'flex';
}

function closeReviewModal() {
    document.getElementById('reviewModal').style.display = 'none';
}
</script>

</body>
</html>
