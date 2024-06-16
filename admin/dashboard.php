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
$students = [];
$events = getAllEvents();
$enrolled_students = [];

// Fetch enrolled students
$sql_students = "SELECT user_id, full_name FROM users WHERE role = 'student'";
$result_students = mysqli_query($link, $sql_students);
if ($result_students) {
    while ($row = mysqli_fetch_assoc($result_students)) {
        $students[] = $row;
    }
} else {
    handle_error('Failed to fetch students: ' . mysqli_error($link));
}

// Fetch latest event details for the logged-in teacher
$teacher_id = $_SESSION['user_id'];
$sql_event = "SELECT event_id, event_name, event_date, event_location FROM events WHERE teacher_id = ? ORDER BY event_date DESC LIMIT 1";
$stmt_event = mysqli_prepare($link, $sql_event);
if ($stmt_event) {
    mysqli_stmt_bind_param($stmt_event, 'i', $teacher_id);
    mysqli_stmt_execute($stmt_event);
    mysqli_stmt_bind_result($stmt_event, $event_id, $event_name, $event_date, $event_location);
    mysqli_stmt_fetch($stmt_event);
    mysqli_stmt_close($stmt_event);
} else {
    handle_error('Failed to fetch latest event: ' . mysqli_error($link));
}

// Fetch enrolled students for the latest event
$sql_enrolled_students = "SELECT u.user_id, u.full_name, IFNULL(a.attendance_id, 0) AS present
                          FROM users u
                          LEFT JOIN enrolled_students es ON u.user_id = es.student_id
                          LEFT JOIN attendance a ON u.user_id = a.student_id AND a.event_id = es.event_id
                          WHERE es.event_id = ?";
$stmt_enrolled_students = mysqli_prepare($link, $sql_enrolled_students);
if ($stmt_enrolled_students) {
    mysqli_stmt_bind_param($stmt_enrolled_students, 'i', $event_id);
    mysqli_stmt_execute($stmt_enrolled_students);
    $result_enrolled_students = mysqli_stmt_get_result($stmt_enrolled_students);
    if ($result_enrolled_students) {
        while ($row = mysqli_fetch_assoc($result_enrolled_students)) {
            $enrolled_students[] = $row;
        }
    } else {
        handle_error('Failed to fetch enrolled students for the event: ' . mysqli_error($link));
    }
    mysqli_stmt_close($stmt_enrolled_students);
} else {
    handle_error('Failed to prepare statement for enrolled students: ' . mysqli_error($link));
}

// Handle form submission to mark attendance
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = validate_input($_POST['student_id']);
    $event_id = validate_input($_POST['event_id']);

    // Check if the event ID is valid
    $valid_event = false;
    foreach ($events as $event) {
        if ($event['event_id'] == $event_id) {
            $valid_event = true;
            break;
        }
    }

    // Check if the student ID is valid
    $valid_student = false;
    foreach ($students as $student) {
        if ($student['user_id'] == $student_id) {
            $valid_student = true;
            break;
        }
    }

    // Perform validation and processing (e.g., update attendance)
    if ($valid_event && $valid_student) {
        $success = mark_Attendance($student_id, $event_id);

        if ($success === true) {
            header('Location: dashboard.php');
            exit;
        } else {
            handle_error('Failed to mark attendance.');
        }
    } else {
        $success_message = "Attendance marked successfully for student ID: $student_id and event ID: $event_id";
        $_SESSION['success_message'] = $success_message; // Set session variable
        $show_alert = true;
    }
}

// Close database connection
mysqli_close($link);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="../adminlte/css/adminlte.min.css">
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
            <li class="breadcrumb-item"><a href="../qr_codes/show_qr.php">Show QR</a></li>
            <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
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
                                <!-- Ensure $_SESSION['full_name'] and $_SESSION['user_id'] are accessible here -->
                                <h3 class="card-title">Welcome Teacher <?php echo $_SESSION['full_name']; ?> (ID: <?php echo $_SESSION['user_id']; ?>)</h3>
                            </div>
                            <!-- /.card-header -->
                            <div class="card-body">
                                <!-- Tab navigation -->
                                <ul class="nav nav-tabs" id="dashboardTabs" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" id="enrolled-students-tab" data-toggle="tab" href="#enrolled-students" role="tab" aria-controls="enrolled-students" aria-selected="true">Enrolled Students</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="event-details-tab" data-toggle="tab" href="#event-details" role="tab" aria-controls="event-details" aria-selected="false">Event Details</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="enrolled-students-event-tab" data-toggle="tab" href="#enrolled-students-event" role="tab" aria-controls="enrolled-students-event" aria-selected="false">Enrolled Students for Event</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="update-attendance-tab" data-toggle="tab" href="#update-attendance" role="tab" aria-controls="update-attendance" aria-selected="false">Update Attendance</a>
                                    </li>
                                </ul>

                                <!-- Tab content -->
                                <div class="tab-content mt-3" id="dashboardTabsContent">
                                    <!-- Enrolled Students Tab -->
                                    <div class="tab-pane fade show active" id="enrolled-students" role="tabpanel" aria-labelledby="enrolled-students-tab">
                                        <h4>Enrolled Students</h4>
                                        <ul>
                                            <?php foreach ($students as $student): ?>
                                                <li><?php echo $student['full_name']; ?> (ID: <?php echo $student['user_id']; ?>)</li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>

                                    <!-- Event Details Tab -->
                                    <div class="tab-pane fade" id="event-details" role="tabpanel" aria-labelledby="event-details-tab">
                                        <h4>Event Details</h4>
                                        <!-- Search input -->
                                        <div class="form-group">
                                            <input type="text" class="form-control" id="event-search" placeholder="Search by event name...">
                                        </div>
                                        <!-- List of events -->
                                        <ul id="event-list">
                                            <?php foreach ($events as $event): ?>
                                                <li><?php echo htmlspecialchars($event['event_name']); ?> - <?php echo $event['event_date']; ?> <?php echo $event['event_start_time']?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>

                                    <!-- Enrolled Students for Event Tab -->
                                    <div class="tab-pane fade" id="enrolled-students-event" role="tabpanel" aria-labelledby="enrolled-students-event-tab">
                                        <h4>Enrolled Students for Event</h4>
                                        <!-- Event selection dropdown -->
                                        <div class="form-group">
                                            <label for="event-dropdown">Select Event:</label>
                                            <select class="form-control" id="event-dropdown">
                                                <option value="">Select an event...</option>
                                                <?php foreach ($events as $event): ?>
                                                    <option value="<?php echo $event['event_id']; ?>"><?php echo htmlspecialchars($event['event_name']); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <!-- Enrolled students list -->
                                        <ul id="enrolled-students-list">
                                            <!-- Students will be populated dynamically -->
                                        </ul>
                                        <!-- Alert for no enrolled students -->
                                        <div class="alert alert-warning mt-3 d-none" id="no-enrolled-students-alert" role="alert">
                                            No enrolled students found for selected event.
                                        </div>
                                    </div>

                                    <!-- Update Attendance Tab -->
                    <div class="tab-pane fade" id="update-attendance" role="tabpanel" aria-labelledby="update-attendance-tab">
                        <h4>Update Attendance</h4>
                        <form id="mark-attendance-form" action="mark_attendance.php" method="POST">
                            <!-- Student ID and Event ID input fields -->
                            <div class="form-group">
                                <label for="student-id">Student ID:</label>
                                <input type="text" class="form-control" id="student-id" name="student_id" list="student-list" required>
                                <datalist id="student-list">
                                    <!-- Options dynamically populated -->
                                    <?php foreach ($students as $student): ?>
                                        <option value="<?php echo $student['user_id']; ?>"><?php echo $student['full_name']; ?></option>
                                    <?php endforeach; ?>
                                </datalist>
                            </div>
                            <!-- Event ID input -->
                            <div class="form-group">
                                <label for="event-id">Event ID:</label>
                                <input type="text" class="form-control" id="event-id" name="event_id" list="event-list" required>
                                <datalist id="event-list">
                                    <!-- Options dynamically populated -->
                                    <?php foreach ($events as $event): ?>
                                        <option value="<?php echo $event['event_id']; ?>"><?php echo $event['event_name']; ?></option>
                                    <?php endforeach; ?>
                                </datalist>
                            </div>
                            <!-- Submit button -->
                            <button type="submit" class="btn btn-primary">Mark Attendance</button>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Display success message if set -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        <?php if (isset($_SESSION['success_message'])): ?>
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '<?php echo $_SESSION['success_message']; ?>',
                confirmButtonText: 'OK'
            });
            <?php unset($_SESSION['success_message']); ?> // Clear session variable after displaying
        <?php endif; ?>
    });
</script>
<?php require_once 'includes/footer.php'; ?>
<div>
<!-- Bootstrap, Popper.js, and AdminLTE Scripts -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="../adminlte/js/adminlte.min.js"></script>

<!-- JavaScript for live search and attendance -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Live search for Student ID input
    const studentIdInput = document.getElementById('student-id');
    const studentList = document.getElementById('student-list');
    const students = <?php echo json_encode($students); ?>; // Assuming $students is an array of student data

    studentIdInput.addEventListener('input', function() {
        const searchTerm = studentIdInput.value.toLowerCase();
        const filteredStudents = students.filter(student => student.user_id.toString().includes(searchTerm));

        renderStudentList(filteredStudents);
    });

    function renderStudentList(students) {
        studentList.innerHTML = '';
        students.forEach(student => {
            const option = document.createElement('option');
            option.value = student.user_id;
            studentList.appendChild(option);
        });
    }

    // Live search for Event ID input
    const eventIdInput = document.getElementById('event-id');
    const eventList = document.getElementById('event-list');
    const events = <?php echo json_encode($events); ?>; // Assuming $events is an array of event data

    eventIdInput.addEventListener('input', function() {
        const searchTerm = eventIdInput.value.trim().toLowerCase();
        const filteredEvents = events.filter(event => event.event_name.toLowerCase().includes(searchTerm));

        renderEventList(filteredEvents);
    });

    function renderEventList(events) {
        eventList.innerHTML = '';
        events.forEach(event => {
            const option = document.createElement('option');
            option.value = event.event_id;
            option.textContent = `${event.event_name} - ${event.event_date}`;
            eventList.appendChild(option);
        });
    }
});
</script>

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
            listItem.textContent = `${student.full_name} - ${student.present ? 'Present' : 'Absent'}`;
            enrolledStudentsList.appendChild(listItem);
        });
    }
});
</script>

<!-- JavaScript for live search on Student ID -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const studentSearchInput = document.getElementById('student-id');
    const studentList = document.getElementById('student-list');
    const students = <?php echo json_encode($students); ?>; // Assuming $students is an array of student data

    studentSearchInput.addEventListener('input', function() {
        const searchTerm = studentSearchInput.value.trim().toLowerCase();
        const filteredStudents = students.filter(student => student.user_id.toString().includes(searchTerm) || student.full_name.toLowerCase().includes(searchTerm));

        renderStudentList(filteredStudents);
    });

    function renderStudentList(students) {
        studentList.innerHTML = '';
        students.forEach(student => {
            const option = document.createElement('option');
            option.value = student.user_id;
            option.textContent = student.full_name;
            studentList.appendChild(option);
        });
    }
});
</script>
<!-- Add this script section after your main content -->
</body>
</html>

