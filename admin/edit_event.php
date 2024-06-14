<?php
// Start session
session_start();

// Check if the user is logged in as a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header('Location: ../auth/login.php');
    exit;
}

// Include necessary files
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Initialize variables
$event_id = $event_name = $event_date = $event_location = '';
$event_name_err = $event_date_err = $event_location_err = '';
$error_message = '';

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate event ID
    if (empty(trim($_POST["event_id"]))) {
        echo "Invalid event ID.";
        exit();
    } else {
        $event_id = trim($_POST["event_id"]);
    }

    // Validate event name
    if (empty(trim($_POST["event_name"]))) {
        $event_name_err = "Please enter the event name.";
    } else {
        $event_name = trim($_POST["event_name"]);
    }

    // Validate event date
    if (empty(trim($_POST["event_date"]))) {
        $event_date_err = "Please enter the event date.";
    } else {
        $event_date = trim($_POST["event_date"]);
    }

    // Validate event location
    if (empty(trim($_POST["event_location"]))) {
        $event_location_err = "Please enter the event location.";
    } else {
        $event_location = trim($_POST["event_location"]);
    }

    // Check input errors before updating in database
    if (empty($event_name_err) && empty($event_date_err) && empty($event_location_err)) {
        // Prepare an update statement
        $sql = "UPDATE events SET event_name = ?, event_date = ?, event_location = ? WHERE event_id = ?";

        if ($stmt = mysqli_prepare($link, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "sssi", $param_event_name, $param_event_date, $param_event_location, $param_event_id);
            
            // Set parameters
            $param_event_name = $event_name;
            $param_event_date = $event_date;
            $param_event_location = $event_location;
            $param_event_id = $event_id;
            
            // Attempt to execute the prepared statement
            if (mysqli_stmt_execute($stmt)) {
                // Success message
                $success_message = "Event updated successfully.";
            } else {
                $error_message = "Oops! Something went wrong. Please try again later.";
            }
        }
        
        // Close statement
        mysqli_stmt_close($stmt);
    }
    
    // Close connection
    mysqli_close($link);
} else {
    // Retrieve event ID from query parameter if present
    if (isset($_GET['event_id'])) {
        $event_id = $_GET['event_id'];
        
        // Fetch event details from database
        $sql = "SELECT event_id, event_name, event_date, event_location FROM events WHERE event_id = ?";

        if ($stmt = mysqli_prepare($link, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "i", $param_event_id);
            
            // Set parameters
            $param_event_id = $event_id;
            
            // Attempt to execute the prepared statement
            if (mysqli_stmt_execute($stmt)) {
                // Store result
                mysqli_stmt_store_result($stmt);
                
                // Check if event ID exists, if yes then fetch the result
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    // Bind result variables
                    mysqli_stmt_bind_result($stmt, $event_id, $event_name, $event_date, $event_location);
                    mysqli_stmt_fetch($stmt);
                } else {
                    $error_message = "Event ID does not exist.";
                }
            } else {
                $error_message = "Oops! Something went wrong. Please try again later.";
            }
        }
        
        // Close statement
        mysqli_stmt_close($stmt);
        
        // Close connection
        mysqli_close($link);
    } else {
        $error_message = "Event ID parameter is missing.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Event</title>
    <!-- AdminLTE styles -->
    <link rel="stylesheet" href="../adminlte/css/adminlte.min.css">
    <!-- AdminLTE sidebar and control settings -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">

</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">

    <!-- Navbar -->
    <?php require_once 'includes/header.php'; ?>
    <!-- /.navbar -->
    <!-- Include AdminLTE Sidebar -->
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Edit Event</h1>
                    </div><!-- /.col -->
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">Edit Event</li>
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
                                    <input type="hidden" name="event_id" value="<?php echo $event_id; ?>">
                                    <div class="form-group">
                                        <label for="event_name">Event Name</label>
                                        <input type="text" class="form-control" id="event_name" name="event_name" value="<?php echo $event_name; ?>">
                                        <span class="text-danger"><?php echo $event_name_err; ?></span>
                                    </div>
                                    <div class="form-group">
                                        <label for="event_date">Event Date</label>
                                        <input type="datetime-local" class="form-control" id="event_date" name="event_date" value="<?php echo $event_date; ?>">
                                        <span class="text-danger"><?php echo $event_date_err; ?></span>
                                    </div>
                                    <div class="form-group">
                                        <label for="event_location">Event Location</label>
                                        <input type="text" class="form-control" id="event_location" name="event_location" value="<?php echo $event_location; ?>">
                                        <span class="text-danger"><?php echo $event_location_err; ?></span>
                                    </div>
                                </div>
                                <!-- /.card-body -->

                                <div class="card-footer">
                                    <button type="submit" class="btn btn-primary">Update Event</button>
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
