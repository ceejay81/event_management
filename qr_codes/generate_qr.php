<?php
// Start session
session_start();

// Check if the user is logged in as admin or teacher
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'teacher')) {
    header('Location: ../auth/login.php');
    exit;
}

// Include necessary files
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../vendor/autoload.php'; // Include QR code library
require_once '../includes/functions.php';
use chillerlan\QRCode\{QRCode, QROptions};

// Initialize variables
$events = [];

// Fetch events from the database
$sql = "SELECT event_id, event_name, event_date FROM events";
$result = mysqli_query($link, $sql);

if ($result) {
    // Fetch all rows as an associative array
    $events = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_free_result($result);
} else {
    die("Error fetching events: " . mysqli_error($link));
}

// Handle form submission
$error_message = '';
$success_message = '';
$qrCodeFile = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_id = isset($_POST['event_id']) ? $_POST['event_id'] : '';

    // Validate event ID
    if (empty($event_id)) {
        $error_message = "Please select an event.";
    } else {
        // Fetch event details
        $sql = "SELECT event_name, event_date, event_location, event_start_time, teacher_id FROM events WHERE event_id = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $event_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $event_name, $event_date, $event_location, $event_start_time, $teacher_id);
            mysqli_stmt_fetch($stmt);
            mysqli_stmt_close($stmt);

            // Fetch teacher name
            $sql_teacher = "SELECT full_name FROM users WHERE user_id = ?";
            if ($stmt_teacher = mysqli_prepare($link, $sql_teacher)) {
                mysqli_stmt_bind_param($stmt_teacher, "i", $teacher_id);
                mysqli_stmt_execute($stmt_teacher);
                mysqli_stmt_bind_result($stmt_teacher, $teacher_name);
                mysqli_stmt_fetch($stmt_teacher);
                mysqli_stmt_close($stmt_teacher);
            } else {
                die("Error fetching teacher details: " . mysqli_error($link));
            }

            // Generate QR code content
            $qr_content = "Teacher: " . $teacher_name . "\n";
            $qr_content .= "Event Name: " . $event_name . "\n";
            $qr_content .= "Event Date: " . $event_date . "\n";
            $qr_content .= "Event time: " . $event_start_time . "\n";
            $qr_content .= "Location: " . $event_location;

            // Generate QR code
            $options = new QROptions([
                'outputType' => QRCode::OUTPUT_IMAGE_PNG,
                'eccLevel'   => QRCode::ECC_L,
                'imageBase64' => false, // set to true if you need base64 images instead of files
            ]);

            $qrCode = new QRCode($options);

            // Adjust the path where the QR code image will be saved
            $qrCodeFile = '../images/event_' . $event_id . '.png';

            $qrCode->render($qr_content, $qrCodeFile);

            // Save QR code details to the database
            $sql_save = "INSERT INTO qr_codes (event_id, qr_code, qr_content) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE qr_code = ?, qr_content = ?";
            if ($stmt_save = mysqli_prepare($link, $sql_save)) {
                mysqli_stmt_bind_param($stmt_save, "issss", $event_id, $qrCodeFile, $qr_content, $qrCodeFile, $qr_content);
                if (mysqli_stmt_execute($stmt_save)) {
                    $success_message = "QR code generated successfully!";
                } else {
                    $error_message = "Failed to save QR code details: " . mysqli_error($link);
                }
                mysqli_stmt_close($stmt_save);
            } else {
                $error_message = "Error preparing save statement: " . mysqli_error($link);
            }
        } else {
            die("Error preparing query: " . mysqli_error($link));
        }
    }
}

// Close the database connection
mysqli_close($link);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Generate QR Code</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.1.0/css/adminlte.min.css">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    <!-- Header -->
    <?php require_once 'includes/header.php'; ?>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <!-- Content Header -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Generate QR Code</h1>
                    </div>
                </div>
            </div>
        </section>

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-6">
                        <!-- Form -->
                        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" id="qrForm">
                            <div class="card card-primary">
                                <div class="card-header">
                                    <h3 class="card-title">Select Event</h3>
                                </div>
                                <div class="card-body">
                                    <!-- Event Selection -->
                                    <div class="form-group">
                                        <label for="event_id">Event</label>
                                        <select class="form-control" name="event_id" id="event_id" required>
                                            <option value="">Select an event</option>
                                            <?php foreach ($events as $event): ?>
                                                <option value="<?php echo htmlspecialchars($event['event_id']); ?>"><?php echo htmlspecialchars($event['event_name'] . ' - ' . $event['event_date']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="invalid-feedback">Please select an event.</div>
                                    </div>   
                                </div>
                                <!-- Card Footer -->
                                <div class="card-footer">
                                    <button type="submit" class="btn btn-primary" id="generateButton">Generate QR Code</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->

    <!-- Footer -->
    <?php require_once 'includes/footer.php'; ?>
</div>
<!-- ./wrapper -->

<!-- Include jQuery -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<!-- Bootstrap JS -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.1.0/js/adminlte.min.js"></script>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php if (!empty($qrCodeFile)): ?>
    <script>
        $(document).ready(function() {
            Swal.fire({
                title: 'Your QR Code',
                imageUrl: '<?php echo $qrCodeFile; ?>',
                imageWidth: 600,
                imageHeight: 400,
                imageAlt: 'Generated QR Code',
            });
        });
    </script>
<?php endif; ?>
</body>
</html>
