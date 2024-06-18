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
    echo '<div class="alert alert-danger">' . htmlspecialchars($message) . '</div>';
}
function validate_input($input) {
    // Perform basic sanitization
    global $link; // Assuming $link is your database connection object

    // Use mysqli_real_escape_string or other suitable sanitization methods
    $clean_input = mysqli_real_escape_string($link, trim($input));

    return $clean_input;
}
// Function to update user's password
function updateUserPassword($user_id, $new_password_hash) {
    global $link;
    
    $sql = "UPDATE users SET password_hash = ? WHERE user_id = ?";
    
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "si", $new_password_hash, $user_id);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $result;
    } else {
        return false;
    }
}

// Function to update user's full name
function updateUserName($user_id, $full_name) {
    global $link;
    
    $sql = "UPDATE users SET full_name = ? WHERE user_id = ?";
    
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "si", $full_name, $user_id);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $result;
    } else {
        return false;
    }
}

// Function to retrieve user details by user ID
function getUserById($user_id) {
    global $link;
    
    $sql = "SELECT user_id, full_name, email, password_hash FROM users WHERE user_id = ?";
    
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_store_result($stmt);
            
            if (mysqli_stmt_num_rows($stmt) == 1) {
                mysqli_stmt_bind_result($stmt, $user_id, $full_name, $email, $password_hash);
                mysqli_stmt_fetch($stmt);
                $user = [
                    'user_id' => $user_id,
                    'full_name' => $full_name,
                    'email' => $email,
                    'password_hash' => $password_hash
                ];
                mysqli_stmt_close($stmt);
                return $user;
            } else {
                mysqli_stmt_close($stmt);
                return null;
            }
        } else {
            mysqli_stmt_close($stmt);
            return null;
        }
    } else {
        return null;
    }
}

// Function to check if email exists
function emailExists($email) {
    global $link;
    
    $sql = "SELECT user_id FROM users WHERE email = ?";
    
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $email);
        
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_store_result($stmt);
            $result = mysqli_stmt_num_rows($stmt) > 0;
            mysqli_stmt_close($stmt);
            return $result;
        } else {
            mysqli_stmt_close($stmt);
            return false;
        }
    } else {
        return false;
    }
}

// Function to verify password
function verifyPassword($user_id, $password) {
    global $link;
    
    $sql = "SELECT password_hash FROM users WHERE user_id = ?";
    
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_store_result($stmt);
            
            if (mysqli_stmt_num_rows($stmt) == 1) {
                mysqli_stmt_bind_result($stmt, $password_hash);
                mysqli_stmt_fetch($stmt);
                mysqli_stmt_close($stmt);
                return password_verify($password, $password_hash);
            } else {
                mysqli_stmt_close($stmt);
                return false;
            }
        } else {
            mysqli_stmt_close($stmt);
            return false;
        }
    } else {
        return false;
    }
}

// Function to get all events
function getAllEvents() {
    global $link;
    $sql = "SELECT * FROM events";
    $result = mysqli_query($link, $sql);
    if ($result) {
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    } else {
        showError('Oops! Something went wrong fetching events. Please try again later.');
        return []; // Return an empty array if the query fails
    }
}

// Function to get enrolled events for a teacher
function getEnrolledEventsForTeacher($teacher_id) {
    global $link;
    $sql = "SELECT e.event_id, e.event_name, e.event_date, e.event_location
            FROM events e
            WHERE e.teacher_id = ?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $teacher_id);
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            if ($result) {
                return mysqli_fetch_all($result, MYSQLI_ASSOC);
            }
        }
        mysqli_stmt_close($stmt);
    }
    showError('Oops! Something went wrong fetching enrolled events. Please try again later.');
    return []; // Return an empty array if query fails
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
            }
        }
        mysqli_stmt_close($stmt);
    }
    showError('Oops! Something went wrong fetching enrolled events. Please try again later.');
    return []; // Return an empty array if query fails
}

// Function to check if a student is enrolled in a given event
function isStudentEnrolled($student_id, $event_id) {
    global $link;
    $sql = 'SELECT * FROM enrolled_students WHERE student_id = ? AND event_id = ?';
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, 'ii', $student_id, $event_id);
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_store_result($stmt);
            $is_enrolled = mysqli_stmt_num_rows($stmt) > 0;
            mysqli_stmt_close($stmt);
            return $is_enrolled;
        }
        mysqli_stmt_close($stmt);
    }
    showError('Oops! Something went wrong executing query. Please try again later.');
    return false; // Default return value
}
// functions.php or db.php

// Function to delete an event and its associated enrolled students
function deleteEvent($event_id) {
    global $link; // Assuming $link is your database connection
    
    // Step 1: Delete enrolled students
    $success = deleteEnrolledStudents($event_id);
    
    if (!$success) {
        return false; // Return false if deletion of enrolled students fails
    }
    
    // Step 2: Delete the event
    $sql = "DELETE FROM events WHERE event_id = ?";
    $stmt = mysqli_prepare($link, $sql);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $event_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        return true; // Return true if event deletion was successful
    } else {
        handle_error('Failed to prepare statement for event deletion: ' . mysqli_error($link));
        return false; // Return false on failure
    }
}

// Function to delete enrolled students associated with an event
function deleteEnrolledStudents($event_id) {
    global $link; // Assuming $link is your database connection
    
    $sql = "DELETE FROM enrolled_students WHERE event_id = ?";
    $stmt = mysqli_prepare($link, $sql);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $event_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        return true; // Return true if enrolled students deletion was successful
    } else {
        handle_error('Failed to prepare statement for enrolled students deletion: ' . mysqli_error($link));
        return false; // Return false on failure
    }
}
function getAllStudents() {
    global $link; // Assuming $link is your MySQLi connection object

    $students = [];
    $sql = "SELECT user_id, full_name FROM users WHERE role = 'student'";
    $result = mysqli_query($link, $sql);

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $students[] = $row;
        }
        mysqli_free_result($result);
    } else {
        handle_error('Failed to fetch students: ' . mysqli_error($link));
    }

    return $students;
}
// Function to enroll a student in an event
function enrollStudent($student_id, $event_id) {
    global $link;
    $sql = 'INSERT INTO enrolled_students (student_id, event_id) VALUES (?, ?)';
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, 'ii', $student_id, $event_id);
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            return true; // Enrollment successful
        }
        mysqli_stmt_close($stmt);
    }
    showError('Oops! Something went wrong enrolling student. Please try again later.');
    return false; // Default return value
}

// Function to mark attendance
function mark_Attendance($student_id, $event_id) {
    global $link;

    // Check if attendance is already marked
    $sql_check = "SELECT * FROM attendance WHERE student_id = ? AND event_id = ?";
    $stmt_check = mysqli_prepare($link, $sql_check);
    if (!$stmt_check) {
        handle_error('Prepare statement failed: ' . mysqli_error($link));
        return false;
    }

    mysqli_stmt_bind_param($stmt_check, "ii", $student_id, $event_id);
    if (!mysqli_stmt_execute($stmt_check)) {
        handle_error('Execute statement failed: ' . mysqli_stmt_error($stmt_check));
        mysqli_stmt_close($stmt_check);
        return false;
    }

    mysqli_stmt_store_result($stmt_check);
    if (mysqli_stmt_num_rows($stmt_check) > 0) {
        mysqli_stmt_close($stmt_check);
        return true; // Attendance already marked
    }

    mysqli_stmt_close($stmt_check);

    // Insert attendance record
    $sql_insert = "INSERT INTO attendance (student_id, event_id, attendance_time) VALUES (?, ?, NOW())";
    $stmt_insert = mysqli_prepare($link, $sql_insert);
    if (!$stmt_insert) {
        handle_error('Prepare statement failed: ' . mysqli_error($link));
        return false;
    }

    mysqli_stmt_bind_param($stmt_insert, "ii", $student_id, $event_id);
    if (!mysqli_stmt_execute($stmt_insert)) {
        handle_error('Execute statement failed: ' . mysqli_stmt_error($stmt_insert));
        mysqli_stmt_close($stmt_insert);
        return false;
    }

    mysqli_stmt_close($stmt_insert);
    return true; // Attendance marked successfully
}


// Function to save QR code details (image path and content) to the database
function saveQrCode($event_id, $qr_image_path, $qr_content) {
    global $link;

    $sql_check = "SELECT qr_code_id FROM qr_codes WHERE event_id = ?";
    if ($stmt_check = mysqli_prepare($link, $sql_check)) {
        mysqli_stmt_bind_param($stmt_check, "i", $event_id);
        mysqli_stmt_execute($stmt_check);
        mysqli_stmt_store_result($stmt_check);

        if (mysqli_stmt_num_rows($stmt_check) > 0) {
            $sql_update = "UPDATE qr_codes SET qr_code = ?, qr_content = ? WHERE event_id = ?";
            if ($stmt_update = mysqli_prepare($link, $sql_update)) {
                mysqli_stmt_bind_param($stmt_update, "ssi", $qr_image_path, $qr_content, $event_id);
                $result = mysqli_stmt_execute($stmt_update);
                mysqli_stmt_close($stmt_update);
                mysqli_stmt_close($stmt_check);
                return $result;
            }
        } else {
            $sql_insert = "INSERT INTO qr_codes (event_id, qr_code, qr_content) VALUES (?, ?, ?)";
            if ($stmt_insert = mysqli_prepare($link, $sql_insert)) {
                mysqli_stmt_bind_param($stmt_insert, "iss", $event_id, $qr_image_path, $qr_content);
                $result = mysqli_stmt_execute($stmt_insert);
                mysqli_stmt_close($stmt_insert);
                mysqli_stmt_close($stmt_check);
                return $result;
            }
        }
        mysqli_stmt_close($stmt_check);
    }
    return false;
}

// Function to handle QR code scan and mark attendance
function handleQRCodeScan($qr_content) {
    global $link;

    // Decode the QR content
    $decoded_content = json_decode($qr_content, true);

    // Extract event_id and student_id from the decoded content
    $event_id = $decoded_content['event_id'];
    $student_id = $decoded_content['student_id'];

    // Verify the event exists
    $sql_event = "SELECT event_name, event_date FROM events WHERE event_id = ?";
    if ($stmt_event = mysqli_prepare($link, $sql_event)) {
        mysqli_stmt_bind_param($stmt_event, "i", $event_id);
        if (mysqli_stmt_execute($stmt_event)) {
            mysqli_stmt_bind_result($stmt_event, $event_name, $event_date);
            if (mysqli_stmt_fetch($stmt_event)) {
                // Event found
                mysqli_stmt_close($stmt_event);

                // Verify the student is enrolled in the event
                if (isStudentEnrolled($student_id, $event_id)) {
                    // Check if attendance is already marked
                    $sql_check = "SELECT * FROM attendance WHERE student_id = ? AND event_id = ?";
                    if ($stmt_check = mysqli_prepare($link, $sql_check)) {
                        mysqli_stmt_bind_param($stmt_check, "ii", $student_id, $event_id);
                        if (mysqli_stmt_execute($stmt_check)) {
                            mysqli_stmt_store_result($stmt_check);
                            if (mysqli_stmt_num_rows($stmt_check) > 0) {
                                // Attendance already marked
                                mysqli_stmt_close($stmt_check);
                                return "Attendance already marked for Event: $event_name on Date: $event_date";
                            }
                            mysqli_stmt_close($stmt_check);

                            // Mark the attendance
                            $sql_insert = "INSERT INTO attendance (student_id, event_id, attendance_time) VALUES (?, ?, NOW())";
                            if ($stmt_insert = mysqli_prepare($link, $sql_insert)) {
                                mysqli_stmt_bind_param($stmt_insert, "ii", $student_id, $event_id);
                                if (mysqli_stmt_execute($stmt_insert)) {
                                    mysqli_stmt_close($stmt_insert);
                                    return "Attendance marked successfully for Event: $event_name on Date: $event_date";
                                }
                                mysqli_stmt_close($stmt_insert);
                                return "Error: Failed to mark attendance.";
                            }
                            return "Error: Could not prepare SQL query to mark attendance.";
                        }
                        mysqli_stmt_close($stmt_check);
                    }
                    return "Error: Could not prepare SQL query to check attendance.";
                }
                return "Error: Student is not enrolled in this event.";
            }
            mysqli_stmt_close($stmt_event);
            return "Error: Event not found.";
        }
        mysqli_stmt_close($stmt_event);
        return "Error: Could not prepare SQL query to verify event.";
    }
    return "Error: Could not prepare SQL query.";
}
function getStudentEvents($link, $student_id) {
    $sql = "SELECT e.event_id, e.event_name, e.event_date, e.event_start_time, e.event_location
            FROM events e
            INNER JOIN enrolled_students es ON e.event_id = es.event_id
            WHERE es.student_id = ?
            ORDER BY e.event_date";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "i", $student_id);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}

function getEventAttendance($link, $event_id) {
    $sql = "SELECT u.full_name AS student_name, a.attendance_time
            FROM enrolled_students es
            LEFT JOIN attendance a ON es.event_id = a.event_id AND es.student_id = a.student_id
            LEFT JOIN users u ON es.student_id = u.user_id
            WHERE es.event_id = ?";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "i", $event_id);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}

?>