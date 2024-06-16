<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="../adminlte/css/adminlte.min.css">
    <!-- Additional CSS if needed -->
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.1.0/css/adminlte.min.css">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- AdminLTE JS -->
    <!-- Additional scripts or custom JS -->
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">

    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a>
            </li>
        </ul>

        <!-- Right navbar links -->
        <ul class="navbar-nav ml-auto">
            <!-- Logout Link -->
            <li class="nav-item">
                <a class="nav-link" href="../auth/logout.php">Logout</a>
            </li>
        </ul>
    </nav>
    <!-- /.navbar -->

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <!-- Brand Logo -->
        <a href="#" class="brand-link">
            <span class="brand-text font-weight-light">Student Dashboard</span>
        </a>

        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Sidebar Menu -->
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                    <!-- Dashboard -->
                    <li class="nav-item">
                        <a href="dashboard.php" class="nav-link">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                    <!-- Enrolled Events -->
                    <li class="nav-item">
                        <a href="view_enrolled_events.php" class="nav-link">
                            <i class="nav-icon fas fa-calendar-check"></i>
                            <p>Enrolled Events</p>
                        </a>
                    </li>
                    <!-- View Attendance -->
                    <li class="nav-item">
                        <a href="view_attendance.php" class="nav-link">
                            <i class="nav-icon fas fa-clipboard-list"></i>
                            <p>View Attendance</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="manual_attendance.php" class="nav-link">
                            <i class="nav-icon fas fa-clipboard-list"></i>
                            <p>Manual Attendance</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="show_qr.php" class="nav-link">
                            <i class="nav-icon fas fa-clipboard-list"></i>
                            <p>QR Attendance</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="scan_qr.php" class="nav-link">
                            <i class="nav-icon fas fa-clipboard-list"></i>
                            <p>Scan QR </p>
                        </a>
                    </li>
                    <!-- Profile -->
                    <li class="nav-item">
                        <a href="profile.php" class="nav-link">
                            <i class="nav-icon fas fa-user"></i>
                            <p>Profile</p>
                        </a>
                    </li>
                </ul>
            </nav>
            <!-- /.sidebar-menu -->
        </div>
        <!-- /.sidebar -->
    </aside>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-dark">Dashboard</h1>
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content-header -->
