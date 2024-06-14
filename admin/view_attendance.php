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
$event_id = $event_name = '';
$attendance_records = [];

// Retrieve event ID from query parameter if present
if (isset($_GET['event_id'])) {
    $event_id = $_GET['event_id'];

    // Fetch event details from database
    $sql = "SELECT event_id, event_name FROM events WHERE event_id = ?";

    if ($stmt = mysqli_prepare($link, $sql)) {
        // Bind variables to the prepared statement as parameters
        mysqli_stmt_bind_param($stmt, "i", $param_event_id);

        // Set parameters
        $param_event_id = $event_id;

        // Attempt to execute the prepared statement
        if (mysqli_stmt_execute($stmt)) {
            // Store result
            mysqli_stmt_store_result($stmt);

            // Check if event ID exists, if yes then fetch the result
            if (mysqli_stmt_num_rows($stmt) == 1) {
                // Bind result variables
                mysqli_stmt_bind_result($stmt, $event_id, $event_name);
                mysqli_stmt_fetch($stmt);
            } else {
                // Event ID doesn't exist, redirect to dashboard
                echo "Event ID not found.";
                exit(); // Ensure script stops execution
            }
        } else {
            echo "Oops! Something went wrong. Please try again later.";
        }
    } else {
        echo "SQL Error: " . mysqli_error($link); // Check SQL error
    }

    // Close statement
    mysqli_stmt_close($stmt);

    // Fetch attendance records for the event
    $sql_attendance = "SELECT a.attendance_id, a.student_id, s.student_name, a.attendance_status
                       FROM attendance a
                       INNER JOIN students s ON a.student_id = s.student_id
                       WHERE a.event_id = ?";

    if ($stmt_attendance = mysqli_prepare($link, $sql_attendance)) {
        // Bind variables to the prepared statement as parameters
        mysqli_stmt_bind_param($stmt_attendance, "i", $param_event_id_attendance);

        // Set parameters
        $param_event_id_attendance = $event_id;

        // Attempt to execute the prepared statement
        if (mysqli_stmt_execute($stmt_attendance)) {
            // Store result
            mysqli_stmt_store_result($stmt_attendance);

            // Check if attendance records exist
            if (mysqli_stmt_num_rows($stmt_attendance) > 0) {
                // Bind result variables
                mysqli_stmt_bind_result($stmt_attendance, $attendance_id, $student_id, $student_name, $attendance_status);

                // Fetch and store attendance records
                while (mysqli_stmt_fetch($stmt_attendance)) {
                    $attendance_records[] = [
                        'attendance_id' => $attendance_id,
                        'student_id' => $student_id,
                        'student_name' => $student_name,
                        'attendance_status' => $attendance_status
                    ];
                }
            } else {
                // No attendance records found
                $no_records_message = "No attendance records found for this event.";
            }
        } else {
            echo "Oops! Something went wrong. Please try again later.";
        }
    } else {
        echo "SQL Error: " . mysqli_error($link); // Check SQL error
    }

    // Close statement
    mysqli_stmt_close($stmt_attendance);

    // Close connection
    mysqli_close($link);
} else {
    // Event ID parameter is missing, redirect to dashboard
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Attendance</title>
    <!-- AdminLTE styles -->
    <link rel="stylesheet" href="../adminlte/css/adminlte.min.css">
    <!-- AdminLTE sidebar and control settings -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">

    <!-- Navbar -->
    <?php require_once 'includes/header.php'; ?>
    <!-- /.navbar -->

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">View Attendance</h1>
                    </div><!-- /.col -->
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="create_event.php">Events</a></li>
                            <li class="breadcrumb-item active">View Attendance</li>
                        </ol>
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content-header -->

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Attendance for <?php echo $event_name; ?></h3>
                            </div>
                            <!-- /.card-header -->
                            <div class="card-body">
                                <?php if (!empty($attendance_records)) : ?>
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Student ID</th>
                                                <th>Student Name</th>
                                                <th>Attendance Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($attendance_records as $attendance) : ?>
                                                <tr>
                                                    <td><?php echo $attendance['student_id']; ?></td>
                                                    <td><?php echo $attendance['student_name']; ?></td>
                                                    <td><?php echo $attendance['attendance_status']; ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php else : ?>
                                    <p><?php echo isset($no_records_message) ? $no_records_message : 'No attendance records found.'; ?></p>
                                <?php endif; ?>
                            </div>
                            <!-- /.card-body -->
                        </div>
                        <!-- /.card -->
                    </div>
                    <!-- /.col -->
                </div>
                <!-- /.row -->
            </div><!-- /.container-fluid -->
        </section>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->

    <!-- Control Sidebar -->
    <!-- /.control-sidebar -->

    <!-- Footer -->
    <?php require_once 'includes/footer.php'; ?>
    <!-- /.footer -->
</div>
<!-- ./wrapper -->

<!-- AdminLTE scripts -->
<script src="../adminlte/js/adminlte.min.js"></script>
</
