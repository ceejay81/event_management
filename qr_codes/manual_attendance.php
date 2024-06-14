<?php
// Start session
session_start();

// Check if the user is logged in as student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../auth/login.php');
    exit;
}

// Include necessary files
require_once '../includes/config.php';
require_once '../includes/db.php';

// Initialize variables
$teacher_id = $event_id = '';
$teacher_id_err = $event_id_err = $submission_err = '';

// Process form data when form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate teacher ID
    if (empty(trim($_POST['teacher_id']))) {
        $teacher_id_err = 'Please enter the teacher ID.';
    } else {
        $teacher_id = trim($_POST['teacher_id']);
    }

    // Validate event ID
    if (empty(trim($_POST['event_id']))) {
        $event_id_err = 'Please enter the event ID.';
    } else {
        $event_id = trim($_POST['event_id']);
    }

    // Check input errors before inserting into database
    if (empty($teacher_id_err) && empty($event_id_err)) {
        // Prepare an insert statement
        $sql = 'INSERT INTO attendance (student_id, teacher_id, event_id, timestamp) VALUES (?, ?, ?, NOW())';
        if ($stmt = mysqli_prepare($link, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, 'iii', $_SESSION['user_id'], $teacher_id, $event_id);

            // Attempt to execute the prepared statement
            if (mysqli_stmt_execute($stmt)) {
                // Redirect to attendance page after successful submission
                header('location: ../student/view_attendance.php');
                exit;
            } else {
                $submission_err = 'Something went wrong. Please try again later.';
            }

            // Close statement
            mysqli_stmt_close($stmt);
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manual Attendance - AdminLTE</title>
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="../adminlte/css/adminlte.min.css">
    <!-- Additional CSS if needed -->
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a>
            </li>
        </ul>

        <!-- Right navbar links -->
        <ul class="navbar-nav ml-auto">
            <!-- Logout -->
            <li class="nav-item">
                <a class="nav-link" href="../auth/logout.php">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </li>
        </ul>
    </nav>
    <!-- /.navbar -->

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <!-- Brand Logo -->
        <a href="#" class="brand-link">
            <span class="brand-text font-weight-light">AdminLTE 3</span>
        </a>

        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Sidebar Menu -->
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                    <!-- Add your sidebar items here -->
                    <li class="nav-item">
                        <a href="../student/dashboard.php" class="nav-link">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../student/view_attendance.php" class="nav-link">
                            <i class="nav-icon fas fa-clipboard-list"></i>
                            <p>View Attendance</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../student/profile.php" class="nav-link">
                            <i class="nav-icon fas fa-user"></i>
                            <p>Profile</p>
                        </a>
                    </li>
                </ul>
            </nav>
            <!-- /.sidebar-menu -->
        </div>
        <!-- /.sidebar -->
    </aside>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">

        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-dark">Manual Attendance</h1>
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content-header -->

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <!-- Page content goes here -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Enter Details</h3>
                            </div>
                            <div class="card-body">
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
                                        <a href="../student/dashboard.php" class="btn btn-default">Cancel</a>
                                    </div>
                                </form>
                                <?php if(!empty($submission_err)): ?>
                                    <div class="alert alert-danger"><?php echo $submission_err; ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->

    <!-- Control Sidebar -->
    <aside class="control-sidebar control-sidebar-dark">
        <!-- Control sidebar content goes here -->
    </aside>
    <!-- /.control-sidebar -->

    <!-- Main Footer -->
    <footer class="main-footer">
        <!-- To the right -->
        <div class="float-right d-none d-sm-inline">
            Powered by <strong>JUSTINE BALASA ESPIRITU</strong>
        </div>
        <!-- Default to the left -->
        <strong>&copy; 2024 Event Management</strong> All rights reserved.
    </footer>
</div>
<!-- ./wrapper -->

<!-- AdminLTE Script -->
<!-- jQuery -->
<script src="../adminlte/plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap -->
<script src="../adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE -->
<script src="../adminlte/js/adminlte.min.js"></script>

<!-- Additional scripts or custom JS -->
<script src="../js/scripts.js"></script>
</body>
</html>
