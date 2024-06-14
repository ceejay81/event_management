<?php
require_once 'db.php'; // Assuming db.php contains your database connection

// Function to generate a random ID
function generateRandomID($length = 6) {
    return substr(str_shuffle("0123456789"), 0, $length);
}

// Function to sanitize input data
function sanitizeInput($data) {
    global $link;
    return mysqli_real_escape_string($link, trim($data));
}

// Function to redirect to another page
function redirectTo($location) {
    header("Location: $location");
    exit;
}

// Function to display error message
function showError($message) {
    echo '<div class="alert alert-danger">' . $message . '</div>';
}

// Function to get user details by user_id
function getUserById($user_id) {
    global $link;
    $sql = "SELECT full_name, email FROM users WHERE user_id = ?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_store_result($stmt);
            if (mysqli_stmt_num_rows($stmt) == 1) {
                mysqli_stmt_bind_result($stmt, $full_name, $email);
                mysqli_stmt_fetch($stmt);
                return ['full_name' => $full_name, 'email' => $email];
            } else {
                return null; // User not found
            }
        } else {
            echo 'Oops! Something went wrong. Please try again later.';
        }
        mysqli_stmt_close($stmt);
    } else {
        echo 'Oops! Something went wrong. Please try again later.';
    }
    return null; // Return null if user not found or other error
}

// Function to get all events
function getAllEvents() {
    global $link;
    $sql = "SELECT * FROM events";
    $result = mysqli_query($link, $sql);
    if ($result) {
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    } else {
        echo 'Oops! Something went wrong fetching events. Please try again later.';
        return []; // Return an empty array if the query fails
    }
}

// Function to get enrolled events by student_id
function getEnrolledEventsByStudentId($student_id) {
    global $link;
    $sql = "SELECT e.event_id, e.event_name, e.event_date, e.event_location
            FROM enrolled_students es
            INNER JOIN events e ON es.event_id = e.event_id
            WHERE es.student_id = ?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $student_id);
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            if ($result) {
                return mysqli_fetch_all($result, MYSQLI_ASSOC);
            } else {
                echo 'Oops! Something went wrong fetching enrolled events. Please try again later.';
                return []; // Return an empty array if no results
            }
        } else {
            echo 'Oops! Something went wrong executing query. Please try again later.';
        }
        mysqli_stmt_close($stmt);
    } else {
        echo 'Oops! Something went wrong preparing statement. Please try again later.';
    }
    return []; // Return an empty array if query fails
}

// Function to check if a student is enrolled in a given event
function isStudentEnrolled($student_id, $event_id) {
    global $link; // Use the global database connection

    $sql = 'SELECT * FROM enrolled_students WHERE student_id = ? AND event_id = ?';
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, 'ii', $student_id, $event_id);
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_store_result($stmt);
            if (mysqli_stmt_num_rows($stmt) > 0) {
                return true; // Student is already enrolled
            } else {
                return false; // Student is not enrolled
            }
        } else {
            echo 'Oops! Something went wrong executing query. Please try again later.';
        }
        mysqli_stmt_close($stmt);
    } else {
        echo 'Oops! Something went wrong preparing statement. Please try again later.';
    }
    return false; // Default return value
}

// Function to enroll a student in an event
function enrollStudent($student_id, $event_id) {
    global $link; // Use the global database connection

    $sql = 'INSERT INTO enrolled_students (student_id, event_id) VALUES (?, ?)';
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, 'ii', $student_id, $event_id);
        if (mysqli_stmt_execute($stmt)) {
            return true; // Enrollment successful
        } else {
            echo 'Oops! Something went wrong enrolling student. Please try again later.';
        }
        mysqli_stmt_close($stmt);
    } else {
        echo 'Oops! Something went wrong preparing statement. Please try again later.';
    }
    return false; // Default return value
}
// Function to mark attendance
function markAttendance($user_id, $event_id) {
    global $link;

    // Check if attendance record already exists
    $sql_check = "SELECT * FROM attendance WHERE user_id = ? AND event_id = ?";
    $stmt_check = mysqli_prepare($link, $sql_check);
    mysqli_stmt_bind_param($stmt_check, "ii", $user_id, $event_id);
    mysqli_stmt_execute($stmt_check);
    mysqli_stmt_store_result($stmt_check);

    if (mysqli_stmt_num_rows($stmt_check) > 0) {
        // Attendance already marked
        return "Attendance already marked.";
    } else {
        // Insert attendance record
        $sql_insert = "INSERT INTO attendance (user_id, event_id, attendance_time) VALUES (?, ?, NOW())";
        $stmt_insert = mysqli_prepare($link, $sql_insert);
        mysqli_stmt_bind_param($stmt_insert, "ii", $user_id, $event_id);
        if (mysqli_stmt_execute($stmt_insert)) {
            return "Attendance marked successfully.";
        } else {
            return "Failed to mark attendance.";
        }
    }

    mysqli_stmt_close($stmt_check);
    mysqli_stmt_close($stmt_insert);
}

// Function to save QR code details (image path and content) to the database
function saveQrCode($event_id, $qr_image_path, $qr_content) {
    global $link; // Assuming $link is your MySQL database connection

    // Check if the QR code entry already exists for the event ID
    $sql_check = "SELECT qr_code_id FROM qr_codes WHERE event_id = ?";
    $stmt_check = mysqli_prepare($link, $sql_check);
    mysqli_stmt_bind_param($stmt_check, "i", $event_id);
    mysqli_stmt_execute($stmt_check);
    mysqli_stmt_store_result($stmt_check);

    if (mysqli_stmt_num_rows($stmt_check) > 0) {
        // Update existing QR code entry
        $sql_update = "UPDATE qr_codes SET qr_code = ?, qr_content = ? WHERE event_id = ?";
        $stmt_update = mysqli_prepare($link, $sql_update);
        mysqli_stmt_bind_param($stmt_update, "ssi", $qr_image_path, $qr_content, $event_id);
        $result = mysqli_stmt_execute($stmt_update);
    } else {
        // Insert new QR code entry
        $sql_insert = "INSERT INTO qr_codes (event_id, qr_code, qr_content) VALUES (?, ?, ?)";
        $stmt_insert = mysqli_prepare($link, $sql_insert);
        mysqli_stmt_bind_param($stmt_insert, "iss", $event_id, $qr_image_path, $qr_content);
        $result = mysqli_stmt_execute($stmt_insert);
    }

    mysqli_stmt_close($stmt_check);
    if (isset($stmt_update)) {
        mysqli_stmt_close($stmt_update);
    }
    if (isset($stmt_insert)) {
        mysqli_stmt_close($stmt_insert);
    }

    return $result;
}


// Function to handle QR code scan
function handleQRCodeScan($qr_content) {
    global $link;

    // Decode JSON content
    $decoded_content = json_decode($qr_content, true);

    // Extract event ID and teacher ID from QR code content
    $event_id = $decoded_content['event_id'];
    $teacher_id = $decoded_content['teacher_id'];

    // Query database to get event details
    $sql = "SELECT event_name, event_date FROM events WHERE event_id = ?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $event_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $event_name, $event_date);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        // Check if event details were fetched
        if (!empty($event_name) && !empty($event_date)) {
            // Record attendance or do any other action here
            $attendance_message = "Attendance marked for Event: $event_name on Date: $event_date";
            return $attendance_message;
        } else {
            return "Event not found or details could not be fetched.";
        }
    } else {
        return "Error: Could not prepare SQL query.";
    }
}
?>
