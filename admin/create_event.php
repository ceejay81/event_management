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
$event_name = $event_date = $event_location = '';
$event_name_err = $event_date_err = $event_location_err = '';

// Process form data when form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate event name
    if (empty(trim($_POST['event_name']))) {
        $event_name_err = 'Please enter the event name.';
    } else {
        $event_name = trim($_POST['event_name']);
    }

    // Validate event date
    if (empty(trim($_POST['event_date']))) {
        $event_date_err = 'Please enter the event date.';
    } else {
        $event_date = trim($_POST['event_date']);
    }

    // Validate event location
    if (empty(trim($_POST['event_location']))) {
        $event_location_err = 'Please enter the event location.';
    } else {
        $event_location = trim($_POST['event_location']);
    }

    // Check input errors before inserting into database
    if (empty($event_name_err) && empty($event_date_err) && empty($event_location_err)) {
        // Prepare an insert statement
        $sql = 'INSERT INTO events (teacher_id, event_name, event_date, event_location) VALUES (?, ?, ?, ?)';
        if ($stmt = mysqli_prepare($link, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, 'isss', $param_teacher_id, $param_event_name, $param_event_date, $param_event_location);

            // Set parameters
            $param_teacher_id = $_SESSION['user_id'];
            $param_event_name = $event_name;
            $param_event_date = $event_date;
            $param_event_location = $event_location;

            // Attempt to execute the prepared statement
            if (mysqli_stmt_execute($stmt)) {
                // Redirect to dashboard after successful event creation
                header('location: dashboard.php');
                exit;
            } else {
                echo 'Something went wrong. Please try again later.';
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
    <title>Create Event</title>
    <!-- Include AdminLTE CSS -->
    <link rel="stylesheet" href="../adminlte/css/adminlte.min.css">
    <!-- Include Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <!-- Include custom styles -->
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    <!-- Include AdminLTE Header -->
    <?php require_once 'includes/header.php'; ?>

    <!-- Include AdminLTE Sidebar -->

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">

        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Create Event</h1>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-6">
                        <!-- form start -->
                        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                            <div class="card card-primary">
                                <div class="card-header">
                                    <h3 class="card-title">Event Details</h3>
                                </div>
                                <div class="card-body">
                                    <!-- Event Name -->
                                    <div class="form-group">
                                        <label for="event_name">Event Name</label>
                                        <input type="text" name="event_name" id="event_name" class="form-control <?php echo (!empty($event_name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $event_name; ?>">
                                        <span class="invalid-feedback"><?php echo $event_name_err; ?></span>
                                    </div>
                                    <!-- Event Date -->
                                    <div class="form-group">
                                        <label for="event_date">Event Date</label>
                                        <input type="datetime-local" name="event_date" id="event_date" class="form-control <?php echo (!empty($event_date_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $event_date; ?>">
                                        <span class="invalid-feedback"><?php echo $event_date_err; ?></span>
                                    </div>
                                    <!-- Event Location -->
                                    <div class="form-group">
                                        <label for="event_location">Event Location</label>
                                        <input type="text" name="event_location" id="event_location" class="form-control <?php echo (!empty($event_location_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $event_location; ?>">
                                        <span class="invalid-feedback"><?php echo $event_location_err; ?></span>
                                    </div>
                                </div>
                                <!-- /.card-body -->
                                <div class="card-footer">
                                    <button type="submit" class="btn btn-primary">Create Event</button>
                                    <a href="dashboard.php" class="btn btn-default">Cancel</a>
                                </div>
                            </div>
                        </form>
                        <!-- /.form -->
                    </div>
                </div>
                <!-- /.row -->
            </div><!-- /.container-fluid -->
        </section>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->

    <!-- Include AdminLTE Footer -->
    <?php require_once 'includes/footer.php'; ?>

</div>
<!-- ./wrapper -->

<!-- Include AdminLTE JavaScript -->
</body>
</html>
