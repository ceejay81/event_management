<?php
session_start();

// Check if the user is logged in as a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../auth/login.php');
    exit;
}

// Include necessary files
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../vendor/autoload.php'; // Include QR code library

use chillerlan\QRCode\QRCodeReader;

// Handle QR code scanning or upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['qr_code'])) {
        // Validate uploaded file
        if ($_FILES['qr_code']['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($_FILES['qr_code']['tmp_name'])) {
            $response = ['success' => false, 'error' => 'Invalid QR code upload.'];
            echo json_encode($response);
            exit;
        }

        $qr_code_file = $_FILES['qr_code']['tmp_name'];

        // Initialize QR code reader
        $QRCodeReader = new QRCodeReader();

        // Attempt to decode QR code from uploaded file
        try {
            $qr_content = $QRCodeReader->decode(file_get_contents($qr_code_file));
        } catch (\Exception $e) {
            $response = ['success' => false, 'error' => 'Failed to process QR code: ' . $e->getMessage()];
            echo json_encode($response);
            exit;
        }

        // Process QR code content if decoding successful
        if ($qr_content) {
            // Extract event details from the QR code content
            $qr_lines = explode("\n", $qr_content);
            $event_details = [];
            foreach ($qr_lines as $line) {
                $line_parts = explode(": ", $line);
                if (count($line_parts) === 2) {
                    $key = trim($line_parts[0]);
                    $value = trim($line_parts[1]);
                    $event_details[$key] = $value;
                }
            }

            // Verify event details and perform necessary actions
            $event_name = isset($event_details['Event Name']) ? $event_details['Event Name'] : '';

            // Fetch event ID based on event name
            $sql_event = "SELECT event_id FROM events WHERE event_name = ?";
            if ($stmt_event = mysqli_prepare($link, $sql_event)) {
                mysqli_stmt_bind_param($stmt_event, "s", $event_name);
                mysqli_stmt_execute($stmt_event);
                mysqli_stmt_bind_result($stmt_event, $event_id);
                if (mysqli_stmt_fetch($stmt_event)) {
                    mysqli_stmt_close($stmt_event);

                    // Check if the student is enrolled in the event
                    $student_id = $_SESSION['user_id'];
                    $sql_enrollment = "SELECT enrollment_id FROM enrolled_students WHERE student_id = ? AND event_id = ?";
                    if ($stmt_enrollment = mysqli_prepare($link, $sql_enrollment)) {
                        mysqli_stmt_bind_param($stmt_enrollment, "ii", $student_id, $event_id);
                        mysqli_stmt_execute($stmt_enrollment);
                        mysqli_stmt_store_result($stmt_enrollment);
                        if (mysqli_stmt_num_rows($stmt_enrollment) > 0) {
                            // Mark attendance for the student in the event
                            $sql_attendance = "INSERT INTO attendance (student_id, event_id) VALUES (?, ?)";
                            if ($stmt_attendance = mysqli_prepare($link, $sql_attendance)) {
                                mysqli_stmt_bind_param($stmt_attendance, "ii", $student_id, $event_id);
                                if (mysqli_stmt_execute($stmt_attendance)) {
                                    $response = ['success' => true, 'message' => 'Attendance marked successfully.'];
                                } else {
                                    $response = ['success' => false, 'error' => 'Failed to mark attendance.'];
                                }
                                mysqli_stmt_close($stmt_attendance);
                            } else {
                                $response = ['success' => false, 'error' => 'Database error.'];
                            }
                        } else {
                            $response = ['success' => false, 'error' => 'You are not enrolled in this event.'];
                        }
                        mysqli_stmt_close($stmt_enrollment);
                    } else {
                        $response = ['success' => false, 'error' => 'Database error.'];
                    }
                } else {
                    $response = ['success' => false, 'error' => 'Event not found.'];
                }
            } else {
                $response = ['success' => false, 'error' => 'Database error.'];
            }
        } else {
            $response = ['success' => false, 'error' => 'Invalid QR code.'];
        }
    } else {
        $response = ['success' => false, 'error' => 'No QR code uploaded.'];
    }

    echo json_encode($response);
    exit;
} else {
    // Redirect or handle GET requests appropriately
    header('Location: ../student/dashboard.php');
    exit;
}
?>
