<?php
session_start();

// Check if the user is logged in as a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../auth/login.php');
    exit;
}

// Include necessary files
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../vendor/autoload.php'; // Include QR code library

use Zxing\QrReader;

// Handle QR code scanning or upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if QR content is posted (from camera scan)
    if (isset($_POST['qr_content'])) {
        $qr_content = $_POST['qr_content'];
    } elseif (isset($_FILES['qr_code'])) { // Check if QR code file is uploaded
        // Validate uploaded file
        if ($_FILES['qr_code']['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($_FILES['qr_code']['tmp_name'])) {
            $response = ['success' => false, 'error' => 'Invalid QR code upload.'];
            echo json_encode($response);
            exit;
        }

        $qr_code_file = $_FILES['qr_code']['tmp_name'];

        // Initialize QR code reader
        try {
            $qrcode = new QrReader($qr_code_file);
            $qr_content = $qrcode->text(); // Retrieve decoded QR code content
        } catch (\Exception $e) {
            $response = ['success' => false, 'error' => 'Failed to process QR code: ' . $e->getMessage()];
            echo json_encode($response);
            exit;
        }
    } else {
        $response = ['success' => false, 'error' => 'No QR code data received.'];
        echo json_encode($response);
        exit;
    }

    // Process QR code content if decoding successful
    if ($qr_content) {
        // Look up the QR code in the database to find associated event
        $sql_qr_lookup = "SELECT * FROM qr_codes WHERE qr_content = ?";
        if ($stmt_qr_lookup = mysqli_prepare($link, $sql_qr_lookup)) {
            mysqli_stmt_bind_param($stmt_qr_lookup, "s", $qr_content);
            mysqli_stmt_execute($stmt_qr_lookup);
            $result_qr = mysqli_stmt_get_result($stmt_qr_lookup);
            if ($row_qr = mysqli_fetch_assoc($result_qr)) {
                $event_id = $row_qr['event_id'];
                mysqli_stmt_close($stmt_qr_lookup);

                // Check if the student is enrolled in the event
                $student_id = $_SESSION['user_id'];
                $sql_enrollment_check = "SELECT enrollment_id FROM enrolled_students WHERE student_id = ? AND event_id = ?";
                if ($stmt_enrollment_check = mysqli_prepare($link, $sql_enrollment_check)) {
                    mysqli_stmt_bind_param($stmt_enrollment_check, "ii", $student_id, $event_id);
                    mysqli_stmt_execute($stmt_enrollment_check);
                    mysqli_stmt_store_result($stmt_enrollment_check);
                    if (mysqli_stmt_num_rows($stmt_enrollment_check) > 0) {
                        // Mark attendance for the student in the event
                        $sql_mark_attendance = "INSERT INTO attendance (student_id, event_id) VALUES (?, ?)";
                        if ($stmt_mark_attendance = mysqli_prepare($link, $sql_mark_attendance)) {
                            mysqli_stmt_bind_param($stmt_mark_attendance, "ii", $student_id, $event_id);
                            if (mysqli_stmt_execute($stmt_mark_attendance)) {
                                $response = ['success' => true, 'message' => 'Attendance marked successfully.', 'event_details' => $row_qr];
                            } else {
                                $response = ['success' => false, 'error' => 'Failed to mark attendance.'];
                            }
                            mysqli_stmt_close($stmt_mark_attendance);
                        } else {
                            $response = ['success' => false, 'error' => 'Database error marking attendance.'];
                        }
                    } else {
                        $response = ['success' => false, 'error' => 'You are not enrolled in this event.'];
                    }
                    mysqli_stmt_close($stmt_enrollment_check);
                } else {
                    $response = ['success' => false, 'error' => 'Database error checking enrollment.'];
                }
            } else {
                $response = ['success' => false, 'error' => 'QR code not found in database.'];
            }
        } else {
            $response = ['success' => false, 'error' => 'Database error looking up QR code.'];
        }
    } else {
        $response = ['success' => false, 'error' => 'Invalid QR code content.'];
    }

    echo json_encode($response);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scan QR Code</title>
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="../adminlte/css/adminlte.min.css">
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
    <!-- html5-qrcode -->
    <script src="https://unpkg.com/html5-qrcode/minified/html5-qrcode.min.js"></script>
    <!-- jsQR -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jsQR/1.0.0/jsQR.min.js"></script>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
        <!-- Header -->
        <?php require_once 'includes/header.php'; ?>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0 text-dark">Scan QR Code</h1>
                        </div>
                        <div class="col-sm-6">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb float-sm-right">
                                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Scan QR Code</li>
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
                    <div class="row">
                        <div class="col-md-6 offset-md-3">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Upload or Scan QR Code</h3>
                                </div>
                                <div class="card-body">
                                    <!-- QR Code upload form -->
                                    <form id="qrForm" enctype="multipart/form-data">
                                        <div class="form-group">
                                            <label for="qrCode">Choose a QR Code:</label>
                                            <input type="file" class="form-control" id="qrCode" name="qr_code" accept="image/*">
                                        </div>
                                        <button type="submit" class="btn btn-primary">Upload QR Code</button>
                                    </form>
                                    
                                    <!-- Button to start QR code scan -->
                                    <div class="mt-3 text-center">
                                        <button id="startScan" class="btn btn-success">Scan QR Code with Camera</button>
                                    </div>

                                    <!-- QR reader container -->
                                    <div id="qr-reader" style="width:100%; margin-top: 20px;"></div>
                                </div>
                            </div>
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

    <!-- JavaScript -->
    <script>
    $(document).ready(function() {
        // Function to handle QR code decoding using jsQR
        function decodeQRCodeFromImage(imageData) {
            const code = jsQR(imageData.data, imageData.width, imageData.height, {
                inversionAttempts: "dontInvert",
            });

            if (code) {
                return code.data;
            }
            return null;
        }

        // Function to start QR code scanning using html5-qrcode
    function startQRCodeScanner() {
        const html5QrCode = new Html5Qrcode("qr-reader");

        html5QrCode.start(
            { facingMode: "environment" }, // Use environment-facing camera
            {
                fps: 10,
                qrbox: { width: 250, height: 250 }
            },
            async (decodedText, decodedResult) => {
                try {
                    html5QrCode.stop(); // Stop scanning
                    const qrContent = decodeQRCodeFromImage(decodedResult);
                    if (qrContent) {
                        console.log('QR Code content:', qrContent);

                        // Send the decoded text to the PHP server for processing
                        const response = await $.ajax({
                            type: 'POST',
                            url: 'scan_qr.php', // Adjusted URL for self-reference
                            data: { qr_content: qrContent },
                            dataType: 'json'
                        });

                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: response.message,
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.error,
                            });
                        }
                    } else {
                        throw new Error('Failed to decode QR code.');
                    }
                } catch (error) {
                    console.error('QR code processing error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to process QR code.',
                    });
                }
            },
            (errorMessage) => {
                console.error('QR code scanning error:', errorMessage);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to scan QR code.',
                });
            }
        ).catch(err => {
            console.error("Unable to start scanning", err);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Unable to start scanning QR code.',
            });
        });
    }

    // Event listener for starting QR code scan with camera button
    $('#startScan').click(function() {
        startQRCodeScanner();
    });

    // Form submission for QR code upload and verification
    $('#qrForm').submit(function(e) {
        e.preventDefault();
        var formData = new FormData(this);

        $.ajax({
            type: 'POST',
            url: 'scan_qr.php', // Adjusted URL for self-reference
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message,
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
                    text: 'Failed to process QR code.',
                });
            }
        });
    });
});
</script>
</body>
</html>
