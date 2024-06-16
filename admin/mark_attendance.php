<?php
// Start session
session_start();

// Check if the user is logged in as teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header('Location: ../auth/login.php');
    exit;
}

// Include necessary files
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Initialize variables
$students = [];
$events = getAllEvents();
$enrolled_students = [];

// Fetch enrolled students (assuming you have a function getAllStudents() defined)
$students = getAllStudents();

// Fetch latest event details for the logged-in teacher
$teacher_id = $_SESSION['user_id'];
$sql_event = "SELECT event_id, event_name, event_date, event_location FROM events WHERE teacher_id = ? ORDER BY event_date DESC LIMIT 1";
$stmt_event = mysqli_prepare($link, $sql_event);
if ($stmt_event) {
    mysqli_stmt_bind_param($stmt_event, 'i', $teacher_id);
    mysqli_stmt_execute($stmt_event);
    mysqli_stmt_bind_result($stmt_event, $event_id, $event_name, $event_date, $event_location);
    mysqli_stmt_fetch($stmt_event);
    mysqli_stmt_close($stmt_event);
} else {
    handle_error('Failed to fetch latest event: ' . mysqli_error($link));
}

// Fetch enrolled students for the latest event
$sql_enrolled_students = "SELECT u.user_id, u.full_name, IFNULL(a.attendance_id, 0) AS present
                          FROM users u
                          LEFT JOIN enrolled_students es ON u.user_id = es.student_id
                          LEFT JOIN attendance a ON u.user_id = a.student_id AND a.event_id = es.event_id
                          WHERE es.event_id = ?";
$stmt_enrolled_students = mysqli_prepare($link, $sql_enrolled_students);
if ($stmt_enrolled_students) {
    mysqli_stmt_bind_param($stmt_enrolled_students, 'i', $event_id);
    mysqli_stmt_execute($stmt_enrolled_students);
    $result_enrolled_students = mysqli_stmt_get_result($stmt_enrolled_students);
    if ($result_enrolled_students) {
        while ($row = mysqli_fetch_assoc($result_enrolled_students)) {
            $enrolled_students[] = $row;
        }
    } else {
        handle_error('Failed to fetch enrolled students for the event: ' . mysqli_error($link));
    }
    mysqli_stmt_close($stmt_enrolled_students);
} else {
    handle_error('Failed to prepare statement for enrolled students: ' . mysqli_error($link));
}

// Handle form submission to mark attendance
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = validate_input($_POST['student_id']);
    $event_id = validate_input($_POST['event_id']);

    // Check if the event ID is valid
    $valid_event = false;
    foreach ($events as $event) {
        if ($event['event_id'] == $event_id) {
            $valid_event = true;
            break;
        }
    }

    // Check if the student ID is valid
    $valid_student = false;
    foreach ($students as $student) {
        if ($student['user_id'] == $student_id) {
            $valid_student = true;
            break;
        }
    }

    // Perform validation and processing (e.g., update attendance)
    if ($valid_event && $valid_student) {
        $success = mark_Attendance($student_id, $event_id);

        if ($success === true) {
            // Set success message in session
            $success_message = "Attendance marked successfully for student ID: $student_id and event ID: $event_id";
            $_SESSION['success_message'] = $success_message;
            header('Location: dashboard.php'); // Redirect to dashboard
            exit;
        } else {
            handle_error('Failed to mark attendance.');
        }
    } else {
        $show_alert = true; // Set flag to show alert
    }
}

// Close database connection
mysqli_close($link);
?>
