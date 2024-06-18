<?php
session_start();

// Check if the user is logged in as admin, teacher, or student
if (!isset($_SESSION['user_id']) || empty($_SESSION['role'])) {
    header('Location: ../auth/login.php');
    exit;
}

// Include necessary files
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../vendor/autoload.php'; // Include QR code library

use chillerlan\QRCode\{QRCode, QROptions};

// Fetch events that the student is enrolled in
$user_id = $_SESSION['user_id'];
$enrolled_events = getEnrolledEventsByStudentId($user_id);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['event_id'])) {
    $event_id = sanitizeInput($_POST['event_id']);

    // Fetch event details
    $sql = "SELECT event_name, event_date, event_location, event_start_time, teacher_id FROM events WHERE event_id = ?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $event_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $event_name, $event_date, $event_location, $event_start_time, $teacher_id);

        if (mysqli_stmt_fetch($stmt)) {
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
            $qr_content = "Teacher: " . htmlspecialchars($teacher_name) . "\n";
            $qr_content .= "Event Name: " . htmlspecialchars($event_name) . "\n";
            $qr_content .= "Event Date: " . htmlspecialchars($event_date) . "\n";
            $qr_content .= "Event Time: " . htmlspecialchars($event_start_time) . "\n";
            $qr_content .= "Location: " . htmlspecialchars($event_location);

            // Generate QR code for the event details
            $options = new QROptions([
                'outputType' => QRCode::OUTPUT_IMAGE_PNG,
                'eccLevel'   => QRCode::ECC_L,
                'imageBase64' => true, // Set to true for base64 image
            ]);
            $qrCode = new QRCode($options);
            $qr_image_base64 = $qrCode->render($qr_content);

            // Construct HTML for QR code and download link
            $qr_code_html = '<div class="qr-code-container">';
            $qr_code_html .= '<img src="' . htmlspecialchars($qr_image_base64) . '" alt="QR Code" class="img-fluid">';
            $qr_code_html .= '<a href="' . htmlspecialchars($qr_image_base64) . '" download="qrcode_' . $event_id . '.png">Download QR Code</a>';
            $qr_code_html .= '</div>';

            // Prepare response as JSON
            $response = [
                'success' => true,
                'qr_code_html' => $qr_code_html,
                'event_name' => htmlspecialchars($event_name),
            ];
            echo json_encode($response);
            exit; // Stop further execution after displaying QR code
        } else {
            echo json_encode(['success' => false, 'error' => 'Event not found.']);
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Database error.']);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Show QR Code</title>
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="../adminlte/css/adminlte.min.css">
    <!-- Additional CSS if needed -->

    <!-- Bootstrap CSS (for AdminLTE compatibility) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.0/css/bootstrap.min.css">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <!-- jQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        .qr-code-container {
            text-align: center;
            margin-top: 10px;
        }

        .qr-code-container img {
            max-width: 100%;
            height: auto;
        }

        .qr-code-container a {
            display: block;
            margin-top: 5px;
        }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
        <?php require_once 'includes/header.php'; ?>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0 text-dark">Show QR Code</h1>
                        </div>
                        <div class="col-sm-6">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb float-sm-right">
                                    <li class="breadcrumb-item"><a href="../admin/dashboard.php">Dashboard</a></li>
                                    <li class="breadcrumb-item"><a href="../admin/view_event.php">Events</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Show QR</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.content-header -->

            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">
                    <!-- Page content goes here -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Select Event</h3>
                                </div>
                                <div class="card-body">
                                    <form id="eventForm">
                                        <div class="form-group">
                                            <label for="eventSelect">Choose an event:</label>
                                            <select class="form-control" id="eventSelect" name="event_id">
                                                <?php foreach ($enrolled_events as $event): ?>
                                                    <option value="<?php echo htmlspecialchars($event['event_id']); ?>"><?php echo htmlspecialchars($event['event_name']); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <button type="submit" class="btn btn-primary">Generate QR Code</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <!-- /.content -->
        </div>
        <!-- /.content-wrapper -->
        <?php require_once 'includes/footer.php'; ?>
    </div>
    <!-- ./wrapper -->

    <script>
        $(document).ready(function() {
    // Form submission for generating QR code
    $('#eventForm').submit(function(e) {
        e.preventDefault();
        var event_id = $('#eventSelect').val();

        $.ajax({
            type: 'POST',
            url: '<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>',
            data: { event_id: event_id },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Display QR code and download link using SweetAlert2
                    Swal.fire({
                        title: 'QR Code for ' + response.event_name,
                        html: response.qr_code_html,
                        showCloseButton: true,
                        focusConfirm: false,
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.error,
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to generate QR code.',
                });
            }
        });
    });
});
</script>
