-- database.sql
CREATE DATABASE IF NOT EXISTS smart_health DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE smart_health;


-- users table (patients, doctors, admin)
CREATE TABLE users (
id INT AUTO_INCREMENT PRIMARY KEY,
name VARCHAR(150) NOT NULL,
email VARCHAR(150) NOT NULL UNIQUE,
password VARCHAR(255) NOT NULL,
role ENUM('patient','doctor','admin') NOT NULL DEFAULT 'patient',
specialty VARCHAR(150) DEFAULT NULL,
phone VARCHAR(30) DEFAULT NULL,
bio TEXT DEFAULT NULL,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;


CREATE TABLE appointments (
id INT AUTO_INCREMENT PRIMARY KEY,
patient_id INT NOT NULL,
doctor_id INT NOT NULL,
appt_date DATE NOT NULL,
appt_time TIME NOT NULL,
type ENUM('in_person','online') DEFAULT 'in_person',
status ENUM('pending','confirmed','cancelled','completed') DEFAULT 'pending',
notes TEXT,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (patient_id) REFERENCES users(id) ON DELETE CASCADE,
FOREIGN KEY (doctor_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;


CREATE TABLE prescriptions (
id INT AUTO_INCREMENT PRIMARY KEY,
appointment_id INT NOT NULL,
doctor_id INT NOT NULL,
patient_id INT NOT NULL,
content TEXT NOT NULL,
file VARCHAR(255) DEFAULT NULL,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE
) ENGINE=InnoDB;


CREATE TABLE health_metrics (
id INT AUTO_INCREMENT PRIMARY KEY,
patient_id INT NOT NULL,
metric_name VARCHAR(100) NOT NULL,
metric_value VARCHAR(100) NOT NULL,
recorded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (patient_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;


-- sample admin
INSERT INTO users (name, email, password, role) VALUES
('Riyas','riyas@gmail.com', '" . md5('riyas786') . "', 'admin');


-- Note: passwords here are placeholders; the app uses password_hash() when registering.