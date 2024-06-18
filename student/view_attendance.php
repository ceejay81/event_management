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

// Fetch student details from session
$student_id = $_SESSION['user_id'];

// Fetch events for the logged-in student
$events_result = getStudentEvents($link, $student_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Attendance</title>
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="../adminlte/css/adminlte.min.css">
    <!-- Custom Styles -->
    <style>
        .badge {
            padding: 5px 8px;
            font-size: 12px;
        }
        .badge-success {
            background-color: #28a745;
        }
        .badge-danger {
            background-color: #dc3545;
        }
        .loading-spinner {
            display: none;
            text-align: center;
            padding: 20px;
        }
        .spinner-border {
            width: 3rem;
            height: 3rem;
        }
    </style>
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
                                <ul class="nav nav-tabs" id="eventTabs" role="tablist">
                                    <?php
                                    $first = true;
                                    while ($event = mysqli_fetch_assoc($events_result)) {
                                        $event_id = $event['event_id'];
                                        $event_name = htmlspecialchars($event['event_name']);
                                        $active_class = $first ? 'active' : '';
                                        echo '<li class="nav-item">';
                                        echo '<a class="nav-link ' . $active_class . '" id="tab' . $event_id . '" data-toggle="tab" href="#pane' . $event_id . '" role="tab" aria-controls="pane' . $event_id . '" aria-selected="true">' . $event_name . '</a>';
                                        echo '</li>';
                                        $first = false;
                                    }
                                    ?>
                                </ul>
                                <div class="tab-content" id="eventTabsContent">
                                    <?php
                                    mysqli_data_seek($events_result, 0); // Reset result pointer
                                    $first = true;
                                    while ($event = mysqli_fetch_assoc($events_result)) {
                                        $event_id = $event['event_id'];
                                        $active_class = $first ? 'show active' : '';
                                        echo '<div class="tab-pane fade ' . $active_class . '" id="pane' . $event_id . '" role="tabpanel" aria-labelledby="tab' . $event_id . '">';
                                        echo '<h4>' . htmlspecialchars($event['event_name']) . '</h4>';
                                        echo '<div class="loading-spinner"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></div>';
                                        echo '<div class="table-responsive" data-loaded="false">';
                                        echo '<h5>Attendance Status</h5>';
                                        echo '<table class="table table-bordered">';
                                        echo '<thead><tr><th>Student Name</th><th>Status</th></tr></thead><tbody>';

                                        // Fetch attendance data for this event
                                        $attendance_result = getEventAttendance($link, $event_id);
                                        $present_found = false;
                                        $absent_found = false;

                                        while ($row = mysqli_fetch_assoc($attendance_result)) {
                                            echo '<tr>';
                                            echo '<td>' . htmlspecialchars($row['student_name']) . '</td>';
                                            if ($row['attendance_time']) {
                                                $present_found = true;
                                                echo '<td><span class="badge badge-success">Present</span></td>';
                                            } else {
                                                $absent_found = true;
                                                echo '<td><span class="badge badge-danger">Absent</span></td>';
                                            }
                                            echo '</tr>';
                                        }

                                        if (!$present_found && !$absent_found) {
                                            echo '<tr><td colspan="2">No attendance records found.</td></tr>';
                                        }

                                        echo '</tbody></table>';
                                        echo '</div>'; // Close table-responsive div
                                        echo '</div>'; // Close tab-pane div
                                        $first = false;
                                    }
                                    ?>
                                </div>
                                <!-- /.tab-content -->
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

</div>
<!-- ./wrapper -->

<!-- AdminLTE JavaScript -->
<script src="../adminlte/plugins/jquery/jquery.min.js"></script>
<script src="../adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../adminlte/js/adminlte.min.js"></script>
<!-- Custom Script -->
<script>
$(document).ready(function() {
    $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
        var target = $(e.target).attr("href");
        var $tabContent = $(target);
        var $spinner = $tabContent.find('.loading-spinner');
        var $tableResponsive = $tabContent.find('.table-responsive');

        if ($tableResponsive.data('loaded') !== 'true') {
            $spinner.show();
            // Simulate data loading process
            setTimeout(function() {
                $spinner.hide();
                $tableResponsive.data('loaded', 'true');
                // Add any additional data loading logic here
            }, 1000); // Simulated delay for loading
        }
    });
});
</script>

</body>
</html>
