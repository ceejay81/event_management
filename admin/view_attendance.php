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
$event_id = $_GET['event_id'] ?? null;
$event_name = '';

// Fetch event details if event_id is provided
if ($event_id) {
    $sql_event = "SELECT event_name FROM events WHERE event_id = ?";
    $stmt_event = mysqli_prepare($link, $sql_event);
    if ($stmt_event) {
        mysqli_stmt_bind_param($stmt_event, 'i', $event_id);
        mysqli_stmt_execute($stmt_event);
        mysqli_stmt_bind_result($stmt_event, $event_name);
        mysqli_stmt_fetch($stmt_event);
        mysqli_stmt_close($stmt_event);
    } else {
        handle_error('Failed to fetch event details: ' . mysqli_error($link));
    }

    // Fetch enrolled students for the event
    $enrolled_students = [];
    $sql_enrolled_students = "SELECT u.user_id, u.full_name, a.attendance_time
                              FROM users u
                              LEFT JOIN attendance a ON u.user_id = a.student_id AND a.event_id = ?
                              LEFT JOIN enrolled_students es ON u.user_id = es.student_id
                              WHERE es.event_id = ?";
    $stmt_enrolled_students = mysqli_prepare($link, $sql_enrolled_students);
    if ($stmt_enrolled_students) {
        mysqli_stmt_bind_param($stmt_enrolled_students, 'ii', $event_id, $event_id);
        mysqli_stmt_execute($stmt_enrolled_students);
        $result_enrolled_students = mysqli_stmt_get_result($stmt_enrolled_students);
        if ($result_enrolled_students) {
            while ($row = mysqli_fetch_assoc($result_enrolled_students)) {
                $enrolled_students[] = $row;
            }
        } else {
            handle_error('Failed to fetch enrolled students: ' . mysqli_error($link));
        }
        mysqli_stmt_close($stmt_enrolled_students);
    } else {
        handle_error('Failed to prepare statement for enrolled students: ' . mysqli_error($link));
    }
}

// Fetch all events for event dropdown
$events = getAllEvents();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Attendance</title>
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="../adminlte/css/adminlte.min.css">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Custom Styles -->
    <style>
        .card-header {
            background-color: #007bff;
            color: #fff;
        }
        .nav-tabs .nav-link {
            color: #495057;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-bottom-color: transparent;
            border-radius: 0;
        }
        .nav-tabs .nav-link.active {
            color: #007bff;
            background-color: #fff;
            border-color: #dee2e6 #dee2e6 #fff;
            border-bottom-color: transparent;
        }
        .tab-content {
            border: 1px solid #dee2e6;
            border-top: none;
            padding: 15px;
        }
        .breadcrumb {
            background-color: #f8f9fa;
            padding: 0.75rem 1rem;
            margin-bottom: 1rem;
            list-style: none;
            border-radius: 0.25rem;
        }
        .breadcrumb-item {
            display: inline-block;
            margin-right: 0.5rem;
        }
        .breadcrumb-item+.breadcrumb-item::before {
            display: inline-block;
            padding-right: 0.5rem;
            color: #6c757d;
            content: '/';
        }
        .breadcrumb-item.active {
            color: #6c757d;
        }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    <!-- Header -->
    <?php require_once 'includes/header.php'; ?>

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="view_event.php">Events</a></li>
            <li class="breadcrumb-item active" aria-current="page">View Attendance</li>
        </ol>
    </nav>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <!-- Main row -->
                <div class="row">
                    <!-- Left col -->
                    <section class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Attendance for Event: <?php echo htmlspecialchars($event_name); ?></h3>
                            </div>
                            <!-- /.card-header -->
                            <div class="card-body">
                                <div class="tab-content">
                                    <!-- Enrolled Students for Event Tab -->
                                    <div class="tab-pane fade show active" id="enrolled-students-event" role="tabpanel" aria-labelledby="enrolled-students-event-tab">
                                        <h4>Enrolled Students for Event</h4>
                                        <!-- Event selection dropdown -->
                                        <div class="form-group">
                                            <label for="event-dropdown">Select Event:</label>
                                            <select class="form-control" id="event-dropdown">
                                                <option value="">Select an event...</option>
                                                <?php foreach ($events as $event): ?>
                                                    <option value="<?php echo $event['event_id']; ?>" <?php echo ($event['event_id'] == $event_id) ? 'selected' : ''; ?>><?php echo htmlspecialchars($event['event_name']); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <!-- Enrolled students list -->
                                        <ul id="enrolled-students-list">
                                            <?php if (!empty($enrolled_students)): ?>
                                                <?php foreach ($enrolled_students as $student): ?>
                                                    <li><?php echo htmlspecialchars($student['full_name']); ?> - <?php echo ($student['attendance_time']) ? date('Y-m-d H:i:s', strtotime($student['attendance_time'])) : 'Not marked'; ?></li>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <li>No enrolled students found for this event.</li>
                                            <?php endif; ?>
                                        </ul>
                                        <!-- Alert for no enrolled students -->
                                        <div class="alert alert-warning mt-3 <?php echo (empty($enrolled_students)) ? '' : 'd-none'; ?>" id="no-enrolled-students-alert" role="alert">
                                            No enrolled students found for selected event.
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /.card-body -->
                        </div>
                        <!-- /.card -->
                    </section>
                    <!-- /.Left col -->
                </div>
                <!-- /.row (main row) -->
            </div><!-- /.container-fluid -->
        </section>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->

    <!-- Main Footer -->
    <?php require_once 'includes/footer.php'; ?>
</div>
<!-- ./wrapper -->

<!-- Bootstrap and AdminLTE Scripts -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="../adminlte/js/adminlte.min.js"></script>
<!-- JavaScript for event selection and enrolled students -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const eventDropdown = document.getElementById('event-dropdown');
        const enrolledStudentsList = document.getElementById('enrolled-students-list');
        const noEnrolledStudentsAlert = document.getElementById('no-enrolled-students-alert');

        eventDropdown.addEventListener('change', function() {
            const selectedEventId = eventDropdown.value;
            if (selectedEventId) {
                fetchEnrolledStudents(selectedEventId);
            } else {
                enrolledStudentsList.innerHTML = ''; // Clear list if no event selected
                noEnrolledStudentsAlert.classList.add('d-none');
            }
        });

        function fetchEnrolledStudents(eventId) {
            fetch(`fetch_enrolled_students.php?event_id=${eventId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.length > 0) {
                        displayEnrolledStudents(data);
                        noEnrolledStudentsAlert.classList.add('d-none'); // Hide alert if students found
                    } else {
                        enrolledStudentsList.innerHTML = ''; // Clear list if no students found
                        noEnrolledStudentsAlert.classList.remove('d-none'); // Show alert for no students
                    }
                })
                .catch(error => console.error('Error fetching enrolled students:', error));
        }

        function displayEnrolledStudents(students) {
            enrolledStudentsList.innerHTML = ''; // Clear previous list
            students.forEach(student => {
                const listItem = document.createElement('li');
                listItem.textContent = `${student.full_name} - ${student.attendance_time ? formatDate(student.attendance_time) : 'Not marked'}`;
                enrolledStudentsList.appendChild(listItem);
            });
        }

        function formatDate(dateTimeString) {
            const options = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' };
            return new Date(dateTimeString).toLocaleDateString('en-US', options);
        }
    });
</script>
