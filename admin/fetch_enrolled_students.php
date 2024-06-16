<?php
// fetch_enrolled_students.php

// Include necessary files
require_once '../includes/config.php';
require_once '../includes/db.php';

// Check if event_id is provided via GET
if (isset($_GET['event_id'])) {
    $event_id = intval($_GET['event_id']);

    // Query to fetch enrolled students for the specified event
    $sql = "SELECT u.full_name, IFNULL(a.attendance_id, 0) AS present
            FROM users u
            LEFT JOIN enrolled_students es ON u.user_id = es.student_id
            LEFT JOIN attendance a ON u.user_id = a.student_id AND a.event_id = es.event_id
            WHERE es.event_id = ?";
    
    $stmt = mysqli_prepare($link, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $event_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        // Fetch all rows as an associative array
        $enrolled_students = mysqli_fetch_all($result, MYSQLI_ASSOC);

        mysqli_stmt_close($stmt);
    } else {
        // Handle SQL error
        http_response_code(500);
        echo json_encode(['error' => 'Failed to prepare statement: ' . mysqli_error($link)]);
        exit;
    }

    // Return JSON response
    echo json_encode($enrolled_students);
} else {
    // No event_id provided
    http_response_code(400);
    echo json_encode(['error' => 'Event ID is required']);
    exit;
}

mysqli_close($link);
?>
