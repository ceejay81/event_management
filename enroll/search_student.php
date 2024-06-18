<?php
session_start();

// Include necessary files
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the search term from the POST request
    $search_term = isset($_POST['search_term']) ? sanitizeInput($_POST['search_term']) : '';

    // Initialize response array
    $response = [];

    if (!empty($search_term)) {
        // Prepare SQL statement to search for the student by name
        $sql = 'SELECT user_id, full_name FROM users WHERE full_name LIKE ? AND role = "student"';
        
        if ($stmt = mysqli_prepare($link, $sql)) {
            // Bind the search term parameter
            $param_term = '%' . $search_term . '%';
            mysqli_stmt_bind_param($stmt, 's', $param_term);

            // Execute the statement
            if (mysqli_stmt_execute($stmt)) {
                // Store result
                mysqli_stmt_store_result($stmt);

                // Check if any students were found
                if (mysqli_stmt_num_rows($stmt) > 0) {
                    // Bind result variables
                    mysqli_stmt_bind_result($stmt, $user_id, $full_name);

                    // Fetch and store students in the response array
                    while (mysqli_stmt_fetch($stmt)) {
                        $response[] = [
                            'user_id' => $user_id,
                            'full_name' => $full_name
                        ];
                    }
                } else {
                    // No students found
                    $response['error'] = true;
                    $response['message'] = 'No students found.';
                }
            } else {
                // Query execution failed
                $response['error'] = true;
                $response['message'] = 'Database query failed.';
            }

            // Close the statement
            mysqli_stmt_close($stmt);
        } else {
            // SQL statement preparation failed
            $response['error'] = true;
            $response['message'] = 'Failed to prepare the database query.';
        }
    } else {
        // Search term is empty
        $response['error'] = true;
        $response['message'] = 'Search term is required.';
    }

    // Close the database connection
    mysqli_close($link);

    // Return response in JSON format
    echo json_encode($response);
} else {
    // Invalid request method
    http_response_code(405);
    echo json_encode(['error' => true, 'message' => 'Invalid request method.']);
}
?>
