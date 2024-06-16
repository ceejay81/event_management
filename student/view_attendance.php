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

// Query to fetch attendance records for the student
$sql = "SELECT e.event_name, e.event_date, a.attendance_time
        FROM events e
        INNER JOIN attendance a ON e.event_id = a.event_id
        WHERE a.student_id = ?";
        
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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>View Attendance</title>
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="../adminlte/css/adminlte.min.css">
    <!-- Custom Styles -->
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">

    <?php require_once 'includes/header.php'; ?>

    <!-- Main content -->
    <div class="content-wrapper">
        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Attendance Records</h3>
                            </div>
                            <!-- /.card-header -->
                            <div class="card-body">
                                <?php if (mysqli_num_rows($result) > 0) : ?>
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Event Name</th>
                                                <th>Date</th>
                                                <th>Attendance Time</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($row['event_name']); ?></td>
                                                    <td><?php echo date('M d, Y', strtotime($row['event_date'])); ?></td>
                                                    <td><?php echo date('M d, Y H:i:s', strtotime($row['attendance_time'])); ?></td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                <?php else : ?>
                                    <p>No attendance records found.</p>
                                <?php endif; ?>
                            </div>
                            <!-- /.card-body -->
                        </div>
                        <!-- /.card -->
                    </div>
                    <!-- /.col -->
                </div>
                <!-- /.row -->
            </div>
            <!-- /.container-fluid -->
        </section>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->

    <?php require_once 'includes/footer.php'; ?>

</div>
<!-- ./wrapper -->

<!-- AdminLTE JavaScript -->
<script src="../adminlte/plugins/jquery/jquery.min.js"></script>
<script src="../adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../adminlte/js/adminlte.min.js"></script>
<!-- Custom Script -->
<script src="../js/scripts.js"></script>

</body>
</html>
