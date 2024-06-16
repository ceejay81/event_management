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
require_once '../includes/functions.php';

// Fetch student details from database
$user_id = $_SESSION['user_id'];
$student = getUserById($user_id);

// Check if student exists
if (!$student) {
    die('Student not found.'); // Handle error appropriately
}

// Fetch enrolled events for the student
$enrolled_events = getEnrolledEventsByStudentId($user_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="../adminlte/css/adminlte.min.css">
    <!-- Additional CSS if needed -->
    <link rel="stylesheet" href="../adminlte/css/adminlte.css">
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">

    <!-- Navbar -->
    <?php require_once 'includes/header.php'; ?>


    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content-header -->
        
        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <!-- Enrolled Events -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Enrolled Events</h3>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <ul class="list-group">
                            <?php if (!empty($enrolled_events)) : ?>
                                <?php foreach ($enrolled_events as $event) : ?>
                                    <li class="list-group-item">
                                        <h5><?php echo htmlspecialchars($event['event_name']); ?></h5>
                                        <p>Date: <?php echo htmlspecialchars($event['event_date']); ?></p>
                                        <p>Location: <?php echo htmlspecialchars($event['event_location']); ?></p>
                                        <a href="view_attendance.php?event_id=<?php echo $event['event_id']; ?>" class="btn btn-primary">View Attendance</a>
                                    </li>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <li class="list-group-item">No events found.</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->
            </div><!-- /.container-fluid -->
        </section>
        <!-- /.content -->
        <?php require_once 'includes/footer.php'; ?>
    </div>
    <!-- /.content-wrapper -->

    <!-- Main Footer -->
</div>
<!-- ./wrapper -->

<!-- AdminLTE Script -->
<!-- AdminLTE JS -->
<script src="../adminlte/plugins/jquery/jquery.min.js"></script>
<script src="../adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../adminlte/js/adminlte.min.js"></script>
<!-- Additional scripts or custom JS -->
<script src="../adminlte/js/custom.js"></script>

</body>
</html>
