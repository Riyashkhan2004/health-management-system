<?php
session_start();
include("config/db.php");

// Only patients can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'patient') {
    header("Location: login.php");
    exit;
}

$patient_id = $_SESSION['user_id'];
$msg = "";
$error = "";

// Current date and time
$currentDate = date('Y-m-d');
$currentTime = date('H:i');

// === Move past appointments automatically ===
$conn->query("
    UPDATE appointments 
    SET status='Past' 
    WHERE status='Pending'
      AND (appointment_date < '$currentDate' 
           OR (appointment_date = '$currentDate' AND appointment_time <= '$currentTime'))
");

// Handle booking form submission
if (isset($_POST['book'])) {
    $doctor_id = intval($_POST['doctor_id']);
    $appointment_date = $_POST['appointment_date'] ?? null;
    $appointment_time = $_POST['appointment_time'] ?? null;
    $reason = $conn->real_escape_string($_POST['reason']);

    // Validate working hours and past time
    if ($appointment_time < '10:00' || $appointment_time > '23:00') {
        $error = "⏰ Doctor is available only between 10:00 AM and 11:00 PM.";
    } elseif ($appointment_date == $currentDate && $appointment_time <= $currentTime) {
        $error = "⏰ Booking time for today has already passed. Please select a time tomorrow between 10:00 AM and 11:00 PM.";
    } else {
        // Validate doctor exists
        $doctor_check = $conn->query("SELECT name, specialization FROM doctors WHERE id=$doctor_id");
        if ($doctor_check->num_rows == 0) {
            $error = "❌ Selected doctor does not exist!";
        } else {
            $doctor = $doctor_check->fetch_assoc();
            $specialization = $doctor['specialization'];

            // Check if doctor is already booked at that time
            $check = $conn->query("
                SELECT * FROM appointments 
                WHERE doctor_id = $doctor_id 
                AND appointment_date = '$appointment_date' 
                AND appointment_time = '$appointment_time'
            ");

            if ($check->num_rows > 0) {
                // Doctor is busy — suggest another doctor or next available time
                $alt_doc = $conn->query("
                    SELECT id, name 
                    FROM doctors 
                    WHERE specialization = '{$specialization}' 
                    AND id != $doctor_id 
                    LIMIT 1
                ");

                if ($alt_doc->num_rows > 0) {
                    $alt = $alt_doc->fetch_assoc();
                    $error = "⚠️ Dr. {$doctor['name']} is already booked at this time.<br>
                    👉 You can book with <b>Dr. {$alt['name']} ({$specialization})</b> instead.";
                } else {
                    // No alternative doctor, suggest next time (30 mins later)
                    $next_time = date("H:i", strtotime("$appointment_time +30 minutes"));
                    if ($next_time > "23:00") $next_time = "10:00";
                    $error = "⚠️ Dr. {$doctor['name']} is busy at $appointment_time.<br>
                    🕒 Next available time: <b>$next_time</b>";
                }
            } else {
                // Slot is free — book it
                $sql = "INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, status, reason)
                        VALUES ($patient_id, $doctor_id, '$appointment_date', '$appointment_time', 'Pending', '$reason')";
                if ($conn->query($sql)) {
                    $_SESSION['flash_msg'] = [
                        'title' => 'Appointment Booked Successfully 🎉',
                        'text' => "You have an appointment with Dr. {$doctor['name']} ({$doctor['specialization']}) at $appointment_time.",
                        'type' => 'success'
                    ];
                    header("Location: patient_dashboard.php");
                    exit;
                } else {
                    $error = "❌ Failed to book appointment. Please try again.";
                }
            }
        }
    }
}

// Fetch all doctors
$doctors = $conn->query("SELECT id, name, specialization FROM doctors ORDER BY name ASC");
$doctor_list = [];
while ($d = $doctors->fetch_assoc()) {
    $doctor_list[] = $d;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Book Appointment | Smart Health</title>
<link rel="stylesheet" href="assets/css/style.css">
<style>
    body { font-family:'Poppins',sans-serif; background: #79e1fcff; margin:0; }
.container { max-width:700px; margin:50px auto; }
.card { background:#fff; padding:30px; border-radius:12px;  background: #b4e7f0ff; box-shadow:0 6px 20px rgba(0,0,0,0.1); }
h2 { margin-bottom:20px; color:#2c3e50; }
form input, form select, form textarea { width:95%; padding:12px; margin-bottom:15px; border-radius:8px; border:1px solid #ccc; }
form button { padding:12px 25px; border:none; border-radius:8px; background:#27ae60; color:#fff; font-weight:600; cursor:pointer; }
form button:hover { background:#2ecc71; }
.success { color:green; margin-bottom:15px; }
.error { color:red; margin-bottom:15px; }
.navbar { display:flex; justify-content:space-between; align-items:center; background:#27ae60; padding:10px 20px; color:#fff; border-radius:8px; }
.navbar a { color:#fff; text-decoration:none; margin-left:15px; font-weight:600; }
.navbar a:hover { text-decoration:underline; }
.note { font-size:13px; color:#888; margin-bottom:10px; }
</style>
</head>
<body>

<nav class="navbar">
    <div class="logo">🩺 Smart Doctor</div>
    <div>
        <a href="patient_dashboard.php">Dashboard</a>
        <a href="book.php" class="active">Book Appointment</a>
        <a href="patient_profile.php">Profile</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>

<div class="container">
    <div class="card">
        <h2>📅 Book an Appointment</h2>

        <?php if ($msg) echo "<div class='success'>$msg</div>"; ?>
        <?php if ($error) echo "<div class='error'>$error</div>"; ?>

        <form method="POST" id="bookingForm">
            <label>Select Doctor:</label>
            <select name="doctor_id" id="doctorSelect" required>
                <option value="">-- Select Doctor --</option>
                <?php foreach ($doctor_list as $doc): ?>
                    <option value="<?= $doc['id'] ?>" data-specialization="<?= htmlspecialchars(strtolower($doc['specialization'])) ?>">
                         <?= htmlspecialchars($doc['name']) ?> (<?= htmlspecialchars($doc['specialization']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="appointment_date">Appointment Date</label>
            <input type="date" name="appointment_date" id="appointment_date" required min="<?= date('Y-m-d') ?>">

            <label for="appointment_time">Appointment Time</label>
            <input type="time" name="appointment_time" id="appointment_time" required min="10:00" max="23:00">
            <p class="note">🕒 Available only between 10:00 AM – 11:00 PM</p>

            <label for="reason">Reason / Notes</label>
            <textarea name="reason" id="reason" rows="3" placeholder="Describe your symptoms or reason (e.g., fever, tooth ache, eye pain)" required></textarea>
            <p class="note">💡 Enter your symptom (like "fever" or "tooth ache") to get a doctor suggestion automatically.</p>

            <button type="submit" name="book">Book Appointment</button>
        </form>
    </div>
</div>

<footer style="text-align:center;margin-top:50px;padding:20px;background:#2c3e50;color:#fff;border-radius:12px;">
    <p>© <?= date('Y') ?> Smart Health System</p>
</footer>

<script>
// === Smart Doctor Suggestion System ===
const reasonInput = document.getElementById('reason');
const doctorSelect = document.getElementById('doctorSelect');

const symptomMap = {
    "fever": "general physician",
    "cold": "general physician",
    "cough": "general physician",
    "flu": "general physician",
    "head ache": "general physician",
    "tooth": "dentist",
    "gum": "dentist",
    "cavity": "dentist",
    "heart": "cardiologist",
    "chest": "cardiologist",
    "skin": "dermatologist",
    "rash": "dermatologist",
    "eye": "ophthalmologist",
    "vision": "ophthalmologist",
    "pain": "orthopedic",
    "bone": "orthopedic",
    "hand": "orthopedic",
    "leg": "orthopedic",
    "crack": "orthopedic",
    "stomach": "gastroenterologist",
    "diarrhea": "gastroenterologist",
    "gas": "gastroenterologist",
    "vomit": "gastroenterologist",
    "pregnancy": "gynecologist",
    "pregnant": "gynecologist",
    "period": "gynecologist"
};

// Show all doctors of the matched specialization
reasonInput.addEventListener('input', () => {
    const input = reasonInput.value.toLowerCase();
    let suggestedSpec = null;

    for (const [symptom, spec] of Object.entries(symptomMap)) {
        if (input.includes(symptom)) {
            suggestedSpec = spec;
            break;
        }
    }

    for (const option of doctorSelect.options) {
        if (!option.value) continue;
        const spec = option.dataset.specialization.toLowerCase();
        option.hidden = suggestedSpec ? !spec.includes(suggestedSpec) : false;
    }

    let firstVisible = Array.from(doctorSelect.options).find(opt => !opt.hidden && opt.value);
    doctorSelect.value = firstVisible ? firstVisible.value : "";
});

// Adjust min time dynamically based on selected date
const appointmentDateInput = document.getElementById('appointment_date');
const appointmentTimeInput = document.getElementById('appointment_time');

appointmentDateInput.addEventListener('change', () => {
    const today = new Date();
    const selectedDate = new Date(appointmentDateInput.value);

    if (selectedDate.toDateString() === today.toDateString()) {
        let hours = today.getHours();
        let minutes = today.getMinutes();
        minutes = minutes < 30 ? 30 : 0;
        if (minutes === 0) hours += 1;

        if (hours >= 23) {
            alert("⏰ Booking time for today has finished. Please select a time tomorrow between 10:00 AM and 11:00 PM.");
            const tomorrow = new Date(today);
            tomorrow.setDate(today.getDate() + 1);
            appointmentDateInput.value = tomorrow.toISOString().split('T')[0];
            appointmentTimeInput.min = "10:00";
            appointmentTimeInput.value = "10:00";
            return;
        }

        const minTime = ("0" + hours).slice(-2) + ":" + ("0" + minutes).slice(-2);
        appointmentTimeInput.min = minTime;
    } else {
        appointmentTimeInput.min = "10:00";
    }
});
</script>

</body>
</html>
