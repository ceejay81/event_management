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
                                    <form id="qrForm" enctype="multipart/form-data">
                                        <div class="form-group">
                                            <label for="qrCode">Choose a QR Code:</label>
                                            <input type="file" class="form-control" id="qrCode" name="qr_code" accept="image/*">
                                        </div>
                                        <button type="submit" class="btn btn-primary">Upload QR Code</button>
                                    </form>
                                    <div class="mt-3 text-center">
                                        <button id="startScan" class="btn btn-success">Scan QR Code with Camera</button>
                                    </div>
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
        <?php require_once 'includes/footer.php'; ?>
    </div>
    <!-- ./wrapper -->

    <script>
    $(document).ready(function() {
        // Form submission for QR code upload and verification
        $('#qrForm').submit(function(e) {
            e.preventDefault();
            var formData = new FormData(this);

            $.ajax({
                type: 'POST',
                url: 'student/verify_attendance.php',
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

        // Initialize QR code scanner
        const html5QrCode = new Html5Qrcode("qr-reader");

        $('#startScan').click(function() {
            html5QrCode.start(
                { facingMode: "environment" },
                {
                    fps: 10,
                    qrbox: { width: 250, height: 250 }
                },
                (decodedText, decodedResult) => {
                    // Handle the decoded result here
                    html5QrCode.stop().then(() => {
                        $.ajax({
                            type: 'POST',
                            url: 'student/verify_attendance.php',
                            data: { qr_content: decodedText },
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
                    }).catch(err => {
                        console.error("Failed to stop camera", err);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to stop camera.',
                        });
                    });
                },
                (errorMessage) => {
                    // Handle scan error
                    console.log(errorMessage);
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
        });
    });
    </script>
</body>
</html>
