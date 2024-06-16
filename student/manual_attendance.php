<?php
session_start();

// Check if user is logged in as student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../auth/login.php');
    exit;
}

// Include necessary files
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Initialize variables
$teacher_id = $event_id = '';
$teacher_id_err = $event_id_err = $submission_err = '';

// Process form data when form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate teacher ID
    if (empty(trim($_POST['teacher_id']))) {
        $teacher_id_err = 'Please enter the teacher ID.';
    } elseif (!is_numeric($_POST['teacher_id'])) {
        $teacher_id_err = 'Teacher ID must be a number.';
    } else {
        $teacher_id = trim($_POST['teacher_id']);
    }

    // Validate event ID
    if (empty(trim($_POST['event_id']))) {
        $event_id_err = 'Please enter the event ID.';
    } elseif (!is_numeric($_POST['event_id'])) {
        $event_id_err = 'Event ID must be a number.';
    } else {
        $event_id = trim($_POST['event_id']);
    }

    // Check input errors before inserting into database
    if (empty($teacher_id_err) && empty($event_id_err)) {
        // Prepare an insert statement
        $sql = 'INSERT INTO attendance (student_id, teacher_id, event_id, timestamp) VALUES (?, ?, ?, NOW())';
        if ($stmt = mysqli_prepare($link, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, 'iii', $param_student_id, $param_teacher_id, $param_event_id);

            // Set parameters
            $param_student_id = $_SESSION['user_id'];
            $param_teacher_id = $teacher_id;
            $param_event_id = $event_id;

            // Attempt to execute the prepared statement
            if (mysqli_stmt_execute($stmt)) {
                // Redirect to attendance page after successful submission
                header('location: view_attendance.php');
                exit;
            } else {
                $submission_err = 'Something went wrong. Please try again later.';
            }

            // Close statement
            mysqli_stmt_close($stmt);
        } else {
            $submission_err = 'Database error: ' . mysqli_error($link);
        }
    }

    // Close connection
    mysqli_close($link);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manual Attendance</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
<?php require_once 'includes/header.php'; ?>

    <div class="wrapper">
        <h2>Manual Attendance</h2>
        <p>Please enter the teacher ID and event ID to mark your attendance.</p>
        <?php if(!empty($submission_err)) echo '<div class="alert alert-danger">' . $submission_err . '</div>'; ?>
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
            <div class="form-group <?php echo (!empty($teacher_id_err)) ? 'has-error' : ''; ?>">
                <label>Teacher ID</label>
                <input type="text" name="teacher_id" class="form-control" value="<?php echo $teacher_id; ?>">
                <span class="help-block"><?php echo $teacher_id_err; ?></span>
            </div>
            <div class="form-group <?php echo (!empty($event_id_err)) ? 'has-error' : ''; ?>">
                <label>Event ID</label>
                <input type="text" name="event_id" class="form-control" value="<?php echo $event_id; ?>">
                <span class="help-block"><?php echo $event_id_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Submit Attendance">
                <a href="dashboard.php" class="btn btn-default">Cancel</a>
            </div>
        </form>
        <?php require_once 'includes/footer.php'; ?>

    </div>
</body>
</html>
