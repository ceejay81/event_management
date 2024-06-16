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

// Fetch student details from database
$student_id = $_SESSION['user_id'];
$student_data = getUserById($student_id);

// Query to fetch enrolled events for the student
$sql = "SELECT e.event_id, e.event_name, e.event_date, e.event_location
        FROM events e
        INNER JOIN enrolled_students es ON e.event_id = es.event_id
        WHERE es.student_id = ?";
        
$stmt = mysqli_prepare($link, $sql);
mysqli_stmt_bind_param($stmt, "i", $student_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Process $result further as needed
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrolled Events - Student Dashboard</title>
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="../adminlte/css/adminlte.min.css">
    <!-- Additional CSS if needed -->
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
                        <h1 class="m-0 text-dark">Enrolled Events</h1>
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
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">List of Enrolled Events</h3>
                            </div>
                            <!-- /.card-header -->
                            <div class="card-body">
                                <?php if (mysqli_num_rows($result) > 0) : ?>
                                    <ul class="list-unstyled">
                                        <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                                            <li class="media">
                                                <div class="media-body">
                                                    <h5 class="mt-0 mb-1"><?php echo htmlspecialchars($row['event_name']); ?></h5>
                                                    <p><strong>Date:</strong> <?php echo date('M d, Y', strtotime($row['event_date'])); ?></p>
                                                    <p><strong>Location:</strong> <?php echo htmlspecialchars($row['event_location']); ?></p>
                                                </div>
                                            </li>
                                        <?php endwhile; ?>
                                    </ul>
                                <?php else : ?>
                                    <p>No events enrolled.</p>
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

    <!-- Main Footer -->
    <?php require_once 'includes/footer.php'; ?>
</div>
<!-- ./wrapper -->

<!-- AdminLTE Script -->
<script src="../adminlte/plugins/jquery/jquery.min.js"></script>
<script src="../adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../adminlte/js/adminlte.min.js"></script>
<!-- Additional scripts or custom JS -->
<script src="../js/scripts.js"></script>
</body>
</html>
