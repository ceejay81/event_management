<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

// Include necessary files
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Fetch user details from database (assuming function getUserById() exists)
$user_id = $_SESSION['user_id'];
$user_data = getUserById($user_id);

// Update user profile if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Process form submission if needed (for admin profile update, if applicable)
    if ($_SESSION['role'] === 'admin') {
        // Example of updating full name and email
        $new_full_name = sanitizeInput($_POST['new_full_name']);
        $new_email = sanitizeInput($_POST['new_email']);

        // Update user profile in the database
        $sql = "UPDATE users SET full_name = ?, email = ? WHERE user_id = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "ssi", $new_full_name, $new_email, $user_id);
            if (mysqli_stmt_execute($stmt)) {
                // Update successful
                $user_data['full_name'] = $new_full_name; // Update local data for display
                $user_data['email'] = $new_email;
            } else {
                // Error updating profile
                echo "Error updating profile: " . mysqli_error($link);
            }
            mysqli_stmt_close($stmt);
        } else {
            echo "SQL Error: " . mysqli_error($link);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Profile</title>
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="../adminlte/css/adminlte.min.css">
    <!-- Custom Styles -->
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">

    <?php require_once 'includes/header.php'; ?>

    <!-- Main content -->
    <div class="content-wrapper">
        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Profile</h3>
                            </div>
                            <!-- /.card-header -->
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Full Name:</label>
                                    <span><?php echo htmlspecialchars($user_data['full_name']); ?></span>
                                </div>
                                <div class="form-group">
                                    <label>Email:</label>
                                    <span><?php echo htmlspecialchars($user_data['email']); ?></span>
                                </div>
                                <div class="form-group">
                                    <label>Role:</label>
                                    <span><?php echo htmlspecialchars($_SESSION['role']); ?></span>
                                </div>

                                <?php if ($_SESSION['role'] === 'admin') : ?>
                                    <!-- Form for admin profile update -->
                                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                                        <div class="form-group">
                                            <label>New Full Name:</label>
                                            <input type="text" name="new_full_name" class="form-control" value="<?php echo htmlspecialchars($user_data['full_name']); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label>New Email:</label>
                                            <input type="email" name="new_email" class="form-control" value="<?php echo htmlspecialchars($user_data['email']); ?>">
                                        </div>
                                        <div class="form-group">
                                            <input type="submit" class="btn btn-primary" value="Update Profile">
                                        </div>
                                    </form>
                                <?php endif; ?>
                            </div>
                            <!-- /.card-body -->
                        </div>
                        <!-- /.card -->
                    </div>
                    <!-- /.col -->
                </div>
                <!-- /.row -->
            </div>
            <!-- /.container-fluid -->
        </section>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->

    <?php require_once 'includes/footer.php'; ?>

</div>
<!-- ./wrapper -->

<!-- AdminLTE JavaScript -->
<script src="../adminlte/plugins/jquery/jquery.min.js"></script>
<script src="../adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../adminlte/js/adminlte.min.js"></script>
<!-- Custom Script -->
<script src="../js/scripts.js"></script>

</body>
</html>
