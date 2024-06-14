<?php
session_start();

// Check if user is logged in as admin or teacher
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'teacher')) {
    header('Location: ../auth/login.php');
    exit;
}

// Include necessary files
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Initialize variables for new student registration
$student_id = $event_id = $full_name = $email = '';
$student_id_err = $event_id_err = $full_name_err = $email_err = '';

// Process form data when form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if new student registration is requested
    if (isset($_POST['new_student'])) {
        // Validate full name
        if (empty(trim($_POST['full_name']))) {
            $full_name_err = 'Please enter student\'s full name.';
        } else {
            $full_name = trim($_POST['full_name']);
        }

        // Validate email
        if (empty(trim($_POST['email']))) {
            $email_err = 'Please enter student\'s email.';
        } else {
            $email = trim($_POST['email']);
            // Check if email already exists
            $sql = 'SELECT user_id FROM users WHERE email = ?';
            if ($stmt = mysqli_prepare($link, $sql)) {
                mysqli_stmt_bind_param($stmt, 's', $param_email);
                $param_email = $email;
                if (mysqli_stmt_execute($stmt)) {
                    mysqli_stmt_store_result($stmt);
                    if (mysqli_stmt_num_rows($stmt) > 0) {
                        $email_err = 'This email is already taken.';
                    }
                } else {
                    echo 'Oops! Something went wrong. Please try again later.';
                }
                mysqli_stmt_close($stmt);
            }
        }

        // Validate password
        if (empty(trim($_POST['password']))) {
            $password_err = 'Please enter a password.';
        } else {
            $password = trim($_POST['password']);
        }

        // Check input errors before inserting into database
        if (empty($full_name_err) && empty($email_err) && empty($password_err)) {
            // Hash the password for secure storage
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            // Define the user role (adjust as needed)
            $role = 'student'; // Example role

            // Insert into the database with prepared statement
            $sql = 'INSERT INTO users (full_name, email, password_hash, role) VALUES (?, ?, ?, ?)';
            if ($stmt = mysqli_prepare($link, $sql)) {
                // Bind parameters to the statement
                mysqli_stmt_bind_param($stmt, 'ssss', $full_name, $email, $password_hash, $role);

                // Execute the statement
                if (mysqli_stmt_execute($stmt)) {
                    $student_id = mysqli_insert_id($link); // Get the newly inserted student ID
                    $success_message = 'Student registered successfully!';
                    // Clear form fields after successful registration
                    $full_name = $email = '';
                } else {
                    $error_message = 'Failed to register student. Please try again.';
                }

                // Close the prepared statement
                mysqli_stmt_close($stmt);
            } else {
                $error_message = 'Database error. Please try again later.';
            }
        }
    } else {
        // Validate student ID
        if (empty(trim($_POST['student_id']))) {
            $student_id_err = 'Please enter student ID.';
        } else {
            $student_id = trim($_POST['student_id']);
        }

        // Validate event ID
        if (empty(trim($_POST['event_id']))) {
            $event_id_err = 'Please select an event.';
        } else {
            $event_id = trim($_POST['event_id']);
        }

        // Check input errors before enrolling student in event
        if (empty($student_id_err) && empty($event_id_err)) {
            // Check if student exists
            $student_data = getUserById($student_id);
            if (!$student_data) {
                $student_id_err = 'Student not found. Please enter a valid student ID or register a new student.';
            } else {
                // Check if student is already enrolled in the event
                if (isStudentEnrolled($student_id, $event_id)) {
                    $student_id_err = 'Student is already enrolled in this event.';
                } else {
                    // Enroll student in event
                    if (enrollStudent($student_id, $event_id)) {
                        $success_message = 'Student enrolled successfully!';
                        // Clear form fields after successful enrollment
                        $student_id = $event_id = '';
                    } else {
                        $error_message = 'Failed to enroll student. Please try again.';
                    }
                }
            }
        }
    }
}

// Fetch events for dropdown
$events = getAllEvents(); // Assuming getAllEvents() retrieves a list of events
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Enroll Student</title>
    <link rel="stylesheet" href="../adminlte/css/adminlte.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.1.0/css/adminlte.min.css">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    <!-- Navbar -->
    <?php require_once 'includes/header.php'; ?>

    <!-- Main Sidebar Container -->
    
    <div class="container-fluid">
    <ul class="nav nav-tabs" id="myTab" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="enroll-tab" data-toggle="tab" href="#enroll" role="tab" aria-controls="enroll" aria-selected="true">Enroll Existing Student</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="register-tab" data-toggle="tab" href="#register" role="tab" aria-controls="register" aria-selected="false">Register New Student</a>
        </li>
    </ul>
    <div class="tab-content" id="myTabContent">
        <div class="tab-pane fade show active" id="enroll" role="tabpanel" aria-labelledby="enroll-tab">
            <!-- Enroll Existing Student Form -->
            <div class="content-wrapper">
                <section class="content">
                <div class="container-fluid" style="margin-left: 0; padding-top: 0;">
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-user-edit"></i> Enroll Existing Student</h3>
                            </div>
                            <form role="form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="student_id"><i class="fas fa-id-card"></i> Student ID:</label>
                                        <input type="text" class="form-control" id="student_id" name="student_id" placeholder="Enter Student ID" value="<?php echo $student_id; ?>">                                        <span class="text-danger"><?php echo $student_id_err; ?></span>
                                    </div>
                                    <div class="form-group">
                                        <label for="event_id"><i class="fas fa-calendar-alt"></i> Select Event:</label>
                                        <select class="form-control" id="event_id" name="event_id">
                                            <option value="">Select Event</option>
                                            <?php foreach ($events as $event): ?>
                                                <option value="<?php echo $event['event_id']; ?>" <?php echo ($event_id == $event['event_id']) ? 'selected' : ''; ?>>
                                                    <?php echo $event['event_name']; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <span class="text-danger"><?php echo $event_id_err; ?></span>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Enroll Student</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        <div class="tab-pane fade" id="register" role="tabpanel" aria-labelledby="register-tab">
            <!-- Register New Student Form -->
            <div class="content-wrapper">
                <section class="content">
                <div class="container-fluid" style="margin-left: 0; padding-top: 0;">
                        <div class="card card-secondary">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-user-plus"></i> Register New Student</h3>
                            </div>
                            <form role="form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                                <div class="card-body">
                                    <input type="hidden" name="new_student" value="1">
                                    <div class="form-group">
                                        <label for="full_name"><i class="fas fa-user"></i> Full Name:</label>
                                        <input type="text" class="form-control" id="full_name" name="full_name" placeholder="Enter Full Name" value="<?php echo $full_name; ?>">
                                        <span class="text-danger"><?php echo $full_name_err; ?></span>
                                    </div>
                                    <div class="form-group">
                                        <label for="email"><i class="fas fa-envelope"></i> Email address:</label>
                                        <input type="email" class="form-control" id="email" name="email" placeholder="@gmail.com" value="<?php echo $email; ?>">
                                        <span class="text-danger"><?php echo $email_err; ?></span>
                                    </div>
                                    <div class="form-group">
                                        <label for="password"><i class="fas fa-lock"></i> Password:</label>
                                        <input type="password" class="form-control" id="password" name="password" placeholder="Enter Password" required>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Register Student</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>


    <!-- Footer -->
    <?php require_once '../admin/includes/footer.php'; ?>
</div>
<!-- ./wrapper -->

<!-- jQuery -->
<script src="../adminlte/plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="../adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="../adminlte/js/adminlte.min.js"></script>
</body>
</html>
