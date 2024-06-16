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
$event_name = $event_date = $event_location = $event_start_time = '';
$event_name_err = $event_date_err = $event_location_err = $event_start_time_err = '';

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

    // Validate event start time
    if (empty(trim($_POST['event_start_time']))) {
        $event_start_time_err = 'Please enter the event start time.';
    } else {
        $event_start_time = trim($_POST['event_start_time']);
    }

    // Check input errors before inserting into database
    if (empty($event_name_err) && empty($event_date_err) && empty($event_location_err) && empty($event_start_time_err)) {
        // Prepare an insert statement
        $sql = 'INSERT INTO events (teacher_id, event_name, event_date, event_location, event_start_time) VALUES (?, ?, ?, ?, ?)';
        if ($stmt = mysqli_prepare($link, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, 'issss', $param_teacher_id, $param_event_name, $param_event_date, $param_event_location, $param_event_start_time);

            // Set parameters
            $param_teacher_id = $_SESSION['user_id'];
            $param_event_name = $event_name;
            $param_event_date = $event_date;
            $param_event_location = $event_location;
            $param_event_start_time = $event_start_time;

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
        } else {
            echo 'Error: ' . mysqli_error($link); // Display error message for debugging
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
    <!-- AdminLTE styles -->
    <link rel="stylesheet" href="../adminlte/css/adminlte.min.css">
    <!-- Bootstrap styles -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
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
                        <h1 class="m-0">Create Event</h1>
                    </div><!-- /.col -->
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">Create Event</li>
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
                        <!-- general form elements -->
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title">Event Details</h3>
                            </div>
                            <!-- /.card-header -->
                            <!-- form start -->
                            <form role="form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="event_name">Event Name</label>
                                        <input type="text" class="form-control <?php echo (!empty($event_name_err)) ? 'is-invalid' : ''; ?>" id="event_name" name="event_name" value="<?php echo htmlspecialchars($event_name); ?>">
                                        <span class="text-danger"><?php echo $event_name_err; ?></span>
                                    </div>
                                    <div class="form-group">
                                        <label for="event_date">Event Date</label>
                                        <input type="date" class="form-control <?php echo (!empty($event_date_err)) ? 'is-invalid' : ''; ?>" id="event_date" name="event_date" value="<?php echo htmlspecialchars($event_date); ?>">
                                        <span class="text-danger"><?php echo $event_date_err; ?></span>
                                    </div>

                                    <div class="form-group">
                                            <label>Event Start Time</label>
                                            <input type="time" name="event_start_time" class="form-control <?php echo (!empty($event_start_time_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $event_start_time; ?>">
                                            <span class="invalid-feedback"><?php echo $event_start_time_err;?></span>
                                        </div>
                                    <div class="form-group">
                                        <label for="event_location">Event Location</label>
                                        <input type="text" class="form-control <?php echo (!empty($event_location_err)) ? 'is-invalid' : ''; ?>" id="event_location" name="event_location" value="<?php echo htmlspecialchars($event_location); ?>">
                                        <span class="text-danger"><?php echo $event_location_err; ?></span>
                                    </div>
                                </div>
                                <!-- /.card-body -->

                                <div class="card-footer">
                                    <button type="submit" class="btn btn-primary">Create Event</button>
                                    <a href="dashboard.php" class="btn btn-default">Cancel</a>
                                </div>
                            </form>
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
</body>
</html>
a