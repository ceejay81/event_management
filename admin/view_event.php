<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

// Include necessary files
require_once '../includes/config.php';
require_once '../includes/db.php';

// Initialize an empty array for events
$events = [];

// Fetch events from the database
$sql = "SELECT event_id, event_name, event_date, event_location FROM events";
if ($result = mysqli_query($link, $sql)) {
    while ($row = mysqli_fetch_assoc($result)) {
        $events[] = $row;
    }
    mysqli_free_result($result);
}

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
        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <div class="container">
                                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="view_event.php">Events</a></li>
                        <li class="breadcrumb-item"><a href="../qr_codes/show_qr.php">Show QR</a></li>
                        <li class="breadcrumb-item"><a href="view_attendance.php">View Attendance</a></li>
                        <li class="breadcrumb-item active" aria-current="page">events</li>
                    </ol>
                            <ul class="event-list">
                                <?php if (!empty($events)) : ?>
                                    <?php foreach ($events as $event): ?>
                                        <li class="event-item">
                                            <h3 class="event-title"><?php echo htmlspecialchars($event['event_name']); ?></h3>
                                            <ul class="event-details">
                                                <li>Date: <?php echo htmlspecialchars($event['event_date']); ?></li>
                                                <li>Location: <?php echo htmlspecialchars($event['event_location']); ?></li>
                                                <li><a href="edit_event.php?event_id=<?php echo $event['event_id']; ?>">Edit Event</a></li>
                                                <li><a href="../qr_codes/generate_qr.php?event_id=<?php echo $event['event_id']; ?>">Generate QR Code</a></li>
                                            </ul>
                                        </li>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <li>No events found.</li>
                                <?php endif; ?>
                            </ul>
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
a