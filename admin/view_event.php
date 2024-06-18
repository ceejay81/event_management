<?php
session_start();

// Check if the user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header('Location: ../auth/login.php');
    exit;
}

// Include necessary files
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Initialize message variable
$message = '';

// Check if an action is specified and the action is delete
if (isset($_GET['event_id']) && is_numeric($_GET['event_id']) && isset($_GET['action']) && $_GET['action'] == 'delete') {
    $event_id = $_GET['event_id'];

    // Attempt to delete the event
    $success = deleteEvent($event_id);

    if ($success) {
        // Redirect to the events list or display a success message
        header('Location: view_event.php');
        exit;
    } else {
        // Handle deletion failure
        $message = 'Failed to delete the event.';
    }
}

// Fetch events from the database using the getAllEvents function
$events = getAllEvents();

// Close the database connection
mysqli_close($link);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Events</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
<?php require_once 'includes/header.php'; ?>
<div class="container">
    <h1>View Events</h1>
    <?php if (!empty($message)): ?>
        <div class="alert alert-warning"><?php echo $message; ?></div>
    <?php endif; ?>
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="container">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="view_event.php">Events</a></li>
                            <li class="breadcrumb-item"><a href="../qr_codes/generate_qr.php">Show QR</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Events</li>
                        </ol>
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Events</h3>
                            </div>
                            <div class="card-body p-0">
                                <ul class="nav nav-pills flex-column">
                                    <?php if (!empty($events)) : ?>
                                        <?php foreach ($events as $event): ?>
                                            <li class="nav-item active">
                                                <a href="#" class="nav-link">
                                                    <i class="far fa-calendar-alt"></i> <?php echo htmlspecialchars($event['event_name']); ?> (ID: <?php echo htmlspecialchars($event['event_id']); ?>)
                                                    <span class="badge bg-primary float-right"><?php echo htmlspecialchars($event['event_date']); ?> at <?php echo htmlspecialchars($event['event_start_time']); ?></span>
                                                </a>
                                                <div style="padding-left:30px;">
                                                    <a href="edit_event.php?event_id=<?php echo $event['event_id']; ?>" class="btn btn-primary btn-sm">Edit Event</a>
                                                    <a href="../qr_codes/generate_qr.php?event_id=<?php echo $event['event_id']; ?>" class="btn btn-success btn-sm">Generate QR Code</a>
                                                    <a href="view_event.php?event_id=<?php echo $event['event_id']; ?>&action=delete" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this event?');">Delete Event</a>
                                                </div>
                                            </li>
                                        <?php endforeach; ?>
                                    <?php else : ?>
                                        <li class="nav-item">
                                            <a href="#" class="nav-link">
                                                No events found.
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.row -->
        </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
</div>
<?php require_once 'includes/footer.php'; ?>
</body>
</html>
