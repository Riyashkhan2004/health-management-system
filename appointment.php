<?php
// =======================================
// SMART DOCTOR SYSTEM — APPOINTMENT.PHP
// =======================================
session_start();
include("config/db.php");

// ✅ Allow only logged-in users
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['patient', 'doctor', 'admin'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// ✅ Include PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

// ===================== HANDLE ACTIONS =====================

if (isset($_GET['action'], $_GET['id'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];
    $newStatus = '';

    if ($action === 'approve') {
        $newStatus = 'Approved';

        // Fetch patient email & doctor info
        $stmt = $conn->prepare("SELECT a.patient_id, a.appointment_date, a.appointment_time, u.email AS patient_email FROM appointments a LEFT JOIN users u ON a.patient_id = u.id WHERE a.id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $patient_email = $row['patient_email'];
        $appointment_date = $row['appointment_date'];
        $appointment_time = $row['appointment_time'];

        $stmt = $conn->prepare("SELECT name FROM doctors WHERE id=?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $doctor = $stmt->get_result()->fetch_assoc();
        $doctor_name = $doctor['name'];

        // Send email to patient
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'riyashkhan454@gmail.com';
            $mail->Password = 'esolovrtmchtgggg'; // app password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('riyashkhan454@gmail.com', "$doctor_name via Smart Doctor System");
            $mail->addAddress($patient_email);
            $mail->isHTML(true);
            $mail->Subject = "Appointment Approved";
            $mail->Body = "
                <h3>Your appointment has been approved by $doctor_name</h3>
                <p>✅ Your appointment is confirmed.</p>
                <p>Appointment Details:<br>
                   Date: $appointment_date<br>
                   Time: $appointment_time</p>
                <p>Sent via Smart Doctor System</p>
            ";
            $mail->send();
        } catch (Exception $e) {
            // Optional: log error
        }

    } elseif ($action === 'cancel') {
        $newStatus = 'Cancelled';
    } elseif ($action === 'complete') {
        $newStatus = 'Completed';
    } elseif ($action === 'remove') {
        $stmt = $conn->prepare("DELETE FROM appointments WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        header("Location: appointment.php?msg=Removed");
        exit;
    }

    if (!empty($newStatus)) {
        $stmt = $conn->prepare("UPDATE appointments SET status=? WHERE id=?");
        $stmt->bind_param("si", $newStatus, $id);
        $stmt->execute();
        header("Location: appointment.php?msg=$newStatus");
        exit;
    }
}

// ===================== HANDLE MESSAGE + EDIT =====================
if (isset($_POST['send_message']) && $role == 'doctor') {
    $patient_email = $_POST['patient_email'];
    $subject = $_POST['subject'];
    $message = nl2br($_POST['message']);
    $appointment_id = $_POST['appointment_id'];
    $new_date = $_POST['new_date'];
    $new_time = $_POST['new_time'];

    // Update appointment time/date if provided
    if (!empty($new_date) && !empty($new_time)) {
        $stmt = $conn->prepare("UPDATE appointments SET appointment_date=?, appointment_time=? WHERE id=?");
        $stmt->bind_param("ssi", $new_date, $new_time, $appointment_id);
        $stmt->execute();
    }

    // Get doctor details
    $stmt = $conn->prepare("SELECT name FROM doctors WHERE id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $doctor = $stmt->get_result()->fetch_assoc();
    $doctor_name = $doctor['name'];

    // Send email
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'riyashkhan454@gmail.com';
        $mail->Password = 'esolovrtmchtgggg'; // app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('riyashkhan454@gmail.com', "$doctor_name via Smart Doctor System");
        $mail->addAddress($patient_email);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = "
            <h3>Message from  $doctor_name</h3>
            <p>$message</p>
            <hr>
            <p><strong>Appointment Updated:</strong><br>
            Date: $new_date<br>
            Time: $new_time</p>
            <p>Sent via Smart Doctor System</p>
        ";
        $mail->send();
        echo "<script>alert('✅ Message sent & appointment updated successfully!');</script>";
    } catch (Exception $e) {
        echo "<script>alert('❌ Message could not be sent. Error: {$mail->ErrorInfo}');</script>";
    }
}

// ===================== FETCH APPOINTMENTS =====================
if ($role == 'doctor') {
    $sql = "
        SELECT a.*, u.name AS patient_name, u.email AS patient_email
        FROM appointments a
        LEFT JOIN users u ON a.patient_id = u.id
        WHERE a.doctor_id = ?
        ORDER BY a.appointment_date DESC, a.appointment_time DESC
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
} elseif ($role == 'patient') {
    $sql = "
        SELECT a.*, u.name AS doctor_name, u.specialization
        FROM appointments a
        LEFT JOIN users u ON a.doctor_id = u.id
        WHERE a.patient_id = ?
        ORDER BY a.appointment_date DESC, a.appointment_time DESC
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
} else {
    $sql = "
        SELECT a.*, p.name AS patient_name, d.name AS doctor_name
        FROM appointments a
        LEFT JOIN users p ON a.patient_id = p.id
        LEFT JOIN users d ON a.doctor_id = d.id
        ORDER BY a.appointment_date DESC, a.appointment_time DESC
    ";
    $stmt = $conn->prepare($sql);
}
$stmt->execute();
$appointments = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Appointments | Smart Doctor</title>
<style>
body { font-family:'Poppins',sans-serif; background:#f4f6f8; margin:0; }
nav { display:flex; justify-content:space-between; align-items:center; padding:15px 30px; background:linear-gradient(45deg,#2980b9,#3498db); color:white; }
nav .logo { font-weight:700; font-size:22px; }
nav ul { list-style:none; display:flex; gap:20px; }
nav ul li a { color:white; text-decoration:none; }
nav ul li a.active, nav ul li a:hover { color:#ffec59; }
.container { max-width:1200px; margin:20px auto; padding:0 20px; }
.card { background:white; padding:20px; border-radius:12px; box-shadow:0 5px 20px rgba(0,0,0,0.1); }

.table { width:100%; border-collapse:collapse; margin-top:20px; }
.table th, .table td { padding:12px; border-bottom:1px solid #ddd; text-align:left; }
.table th { background:#27ae60; color:white; }

.btn { padding:8px 14px; border-radius:6px; color:white; text-decoration:none; font-weight:bold; margin:2px; display:inline-block; cursor:pointer; }
.btn-success { background:#2ecc71; }
.btn-danger { background:#e74c3c; }
.btn-edit { background:#3498db; }
.btn-remove { background:#7f8c8d; }
.btn-disabled { opacity:0.6; pointer-events:none; }

.status-Pending { color:#f39c12; font-weight:bold; }
.status-Approved { color:#27ae60; font-weight:bold; }
.status-Cancelled { color:#e74c3c; font-weight:bold; }
.status-Completed { color:#16a085; font-weight:bold; }

footer { text-align:center; margin-top:50px; padding:20px; background:#3498db; color:white; }

/* Modal */
.modal {
  display:none; position:fixed; z-index:1000; padding-top:80px;
  left:0; top:0; width:100%; height:100%; background-color:rgba(0,0,0,0.6);
}
.modal-content {
  background:#fff; margin:auto; padding:20px; border-radius:10px;
  width:420px; animation:fadeIn .3s ease;
}
@keyframes fadeIn { from {opacity:0;} to {opacity:1;} }
.close { color:red; float:right; font-size:25px; font-weight:bold; cursor:pointer; }
</style>
</head>
<body>

<nav>
  <div class="logo">🩺 Smart Doctor</div>
  <ul>
    <?php if ($role=='doctor'): ?><li><a href="doctor_dashboard.php">Dashboard</a></li>
    <?php elseif ($role=='admin'): ?><li><a href="admin_dashboard.php">Dashboard</a></li>
    <?php else: ?><li><a href="patient_dashboard.php">Dashboard</a></li><?php endif; ?>
    <li><a href="appointment.php" class="active">Appointments</a></li>
    <li><a href="logout.php">Logout</a></li>
  </ul>
</nav>

<div class="container">
<div class="card">
<h2>🗓️ Appointment Management</h2>
<p>Manage, message, and reschedule appointments.</p>

<table class="table">
<tr>
  <?php if (in_array($role, ['admin','patient'])): ?><th>Doctor</th><?php endif; ?>
  <?php if (in_array($role, ['admin','doctor'])): ?><th>Patient</th><?php endif; ?>
  <th>Date</th><th>Time</th><th>Reason</th><th>Status</th><th>Action</th>
</tr>

<?php if ($appointments->num_rows>0): while($row=$appointments->fetch_assoc()): ?>
<tr>
  <?php if (in_array($role, ['admin','patient'])): ?>
    <td> <?= htmlspecialchars($row['doctor_name']??'Unknown') ?></td>
  <?php endif; ?>
  <?php if (in_array($role, ['admin','doctor'])): ?>
    <td><?= htmlspecialchars($row['patient_name']??'Unknown') ?></td>
  <?php endif; ?>
  <td><?= htmlspecialchars($row['appointment_date']) ?></td>
  <td><?= htmlspecialchars($row['appointment_time']) ?></td>
  <td><?= htmlspecialchars($row['reason']) ?></td>
  <td class="status-<?= htmlspecialchars($row['status']) ?>"><?= htmlspecialchars($row['status']) ?></td>
  <td>
  <?php
    $appointmentDateTime = new DateTime($row['appointment_date'].' '.$row['appointment_time']);
    $now = new DateTime();
    $isPast = $appointmentDateTime < $now;

    if($role=='doctor'):
        if($row['status']=='Pending'):
            $cls = $isPast ? 'btn btn-success btn-disabled' : 'btn btn-success';
            $attr = $isPast ? 'onclick="return false;" title="Past Time"' : '';
            echo "<a href='appointment.php?action=approve&id={$row['id']}' class='$cls' $attr>Approve</a>";
            echo "<a href='appointment.php?action=cancel&id={$row['id']}' class='btn btn-danger'>Cancel</a>";
        elseif($row['status']=='Approved'):
            if($isPast) {
                echo "<a href='appointment.php?action=complete&id={$row['id']}' class='btn btn-success'>Complete</a>";
            } else {
                echo "<button class='btn btn-success btn-disabled' title='Can complete only after appointment time'>Complete</button>";
            }
        elseif($row['status']=='Completed'):
            echo "<span style='color:green;font-weight:bold;'>✔ Completed</span>";
        endif;
  ?>
  <button class="btn btn-edit" 
    onclick="openModal('<?= htmlspecialchars($row['patient_email']) ?>',
                       <?= $row['id'] ?>,
                      '<?= $row['appointment_date'] ?>',
                      '<?= $row['appointment_time'] ?>')">
    📧 Message / Edit
  </button>
  <a href="appointment.php?action=remove&id=<?= $row['id'] ?>" class="btn btn-remove">Remove</a>
  <?php elseif($role=='patient'): ?>
    <a href="appointment.php?action=cancel&id=<?= $row['id'] ?>" class="btn btn-danger">Cancel</a>
  <?php else: ?>
    <a href="appointment.php?action=remove&id=<?= $row['id'] ?>" class="btn btn-remove">Remove</a>
  <?php endif; ?>
  </td>
</tr>
<?php endwhile; else: ?>
<tr><td colspan="7">No appointments found.</td></tr>
<?php endif; ?>
</table>
</div>
</div>

<!-- Modal -->
<div id="messageModal" class="modal">
  <div class="modal-content">
    <span class="close" onclick="document.getElementById('messageModal').style.display='none'">&times;</span>
    <h3>📧 Message / Edit Appointment</h3>
    <form method="POST">
      <input type="hidden" id="patient_email" name="patient_email">
      <input type="hidden" id="appointment_id" name="appointment_id">

      <label>Subject:</label><br>
      <input type="text" name="subject" required style="width:100%;padding:6px;"><br><br>

      <label>Message:</label><br>
      <textarea name="message" style="width:100%;height:100px;padding:6px;" placeholder="Write your message here..."></textarea><br><br>

      <label>Reschedule Date:</label><br>
      <input type="date" id="new_date" name="new_date" style="width:100%;padding:6px;"><br><br>

      <label>Reschedule Time:</label><br>
      <input type="time" id="new_time" name="new_time" style="width:100%;padding:6px;"><br><br>

      <button type="submit" name="send_message" class="btn btn-success">Send & Update</button>
    </form>
  </div>
</div>

<footer><p>© <?= date('Y') ?> Smart Doctor Appointment System</p></footer>

<script>
function openModal(email,id,date,time){
  document.getElementById('patient_email').value=email;
  document.getElementById('appointment_id').value=id;
  document.getElementById('new_date').value=date;
  document.getElementById('new_time').value=time;
  document.getElementById('messageModal').style.display='block';
}
</script>
</body>
</html>
