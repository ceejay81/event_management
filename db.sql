-- Create event_management database if not exists
CREATE DATABASE IF NOT EXISTS system;

-- Switch to event_management database
USE system;

-- Users Table
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'teacher', 'student') NOT NULL
);

-- Events Table
CREATE TABLE events (
    event_id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT NOT NULL,
    event_name VARCHAR(255) NOT NULL,
    event_date DATE NOT NULL,
    event_location VARCHAR(255) NOT NULL,
    FOREIGN KEY (teacher_id) REFERENCES users(user_id)
);

-- Attendance Table
CREATE TABLE attendance (
    attendance_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    event_id INT NOT NULL,
    attendance_time DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(user_id),
    FOREIGN KEY (event_id) REFERENCES events(event_id),
    UNIQUE KEY unique_attendance (student_id, event_id)
);

-- Enrolled Students Table
CREATE TABLE enrolled_students (
    enrollment_id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    student_id INT NOT NULL,
    FOREIGN KEY (event_id) REFERENCES events(event_id),
    FOREIGN KEY (student_id) REFERENCES users(user_id),
    UNIQUE KEY unique_enrollment (event_id, student_id)
);

-- QR Codes Table
CREATE TABLE qr_codes (
    qr_code_id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    qr_code TEXT NOT NULL,
    qr_content TEXT NOT NULL, -- New column for QR code content
    FOREIGN KEY (event_id) REFERENCES events(event_id),
    UNIQUE KEY unique_qr_code (event_id)
);
