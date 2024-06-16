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

// Fetch teacher details from the database
$user_id = $_SESSION['user_id'];
$user = getUserById($user_id);

// Check if user exists
if (!$user) {
    die('User not found.');
}

// Initialize variables
$full_name = $user['full_name'];
$email = $user['email'];

// Initialize variables for password update
$current_password = $new_password = $confirm_password = '';
$current_password_err = $new_password_err = $confirm_password_err = '';
$full_name_err = '';

// Process form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Process password update
    if (isset($_POST['password_submit'])) {
        // Validate current password
        if (empty(trim($_POST["current_password"]))) {
            $current_password_err = "Please enter your current password.";
        } else {
            $current_password = trim($_POST["current_password"]);
        }

        // Validate new password
        if (empty(trim($_POST["new_password"]))) {
            $new_password_err = "Please enter a new password.";
        } elseif (strlen(trim($_POST["new_password"])) < 6) {
            $new_password_err = "Password must have at least 6 characters.";
        } else {
            $new_password = trim($_POST["new_password"]);
        }

        // Validate confirm password
        if (empty(trim($_POST["confirm_password"]))) {
            $confirm_password_err = "Please confirm the password.";
        } else {
            $confirm_password = trim($_POST["confirm_password"]);
            if (empty($new_password_err) && ($new_password != $confirm_password)) {
                $confirm_password_err = "Password did not match.";
            }
        }

        // Check input errors before updating the database
        if (empty($current_password_err) && empty($new_password_err) && empty($confirm_password_err)) {
            // Verify current password
            if (password_verify($current_password, $user['password_hash'])) {
                // Password is correct, update password
                $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                if (updateUserPassword($user_id, $new_password_hash)) {
                    // Password updated successfully
                    header("location: profile.php");
                    exit();
                } else {
                    echo "Failed to update password. Please try again later.";
                }
            } else {
                $current_password_err = "Invalid password.";
            }
        }
    }

    // Process full name update
    if (isset($_POST['name_submit'])) {
        // Validate full name
        if (empty(trim($_POST["full_name"]))) {
            $full_name_err = "Please enter your full name.";
        } else {
            $full_name = trim($_POST["full_name"]);
        }

        // Check input errors before updating the database
        if (empty($full_name_err)) {
            if (updateUserName($user_id, $full_name)) {
                // Full name updated successfully
                header("location: profile.php");
                exit();
            } else {
                echo "Failed to update full name. Please try again later.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile</title>
    <!-- Include AdminLTE CSS -->
    <link rel="stylesheet" href="../adminlte/css/adminlte.min.css">
    <!-- Include Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <!-- Include custom styles -->
</head>
<body class="hold-transition sidebar-mini">
    <div class="wrapper">
        <?php require_once 'includes/header.php'; ?>
        <div class="content-wrapper">
            <section class="content-header">
                <h1>Profile</h1>
            </section>

            <section class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header p-2">
                                    <ul class="nav nav-pills">
                                        <li class="nav-item"><a class="nav-link active" href="#profile_tab" data-toggle="pill">Profile Information</a></li>
                                        <li class="nav-item"><a class="nav-link" href="#password_tab" data-toggle="pill">Update Password</a></li>
                                        <li class="nav-item"><a class="nav-link" href="#name_tab" data-toggle="pill">Update Full Name</a></li>
                                    </ul>
                                </div>
                                <div class="card-body">
                                    <div class="tab-content">
                                        <!-- Profile Information Tab -->
                                        <div class="tab-pane active" id="profile_tab">
                                            <div class="form-group">
                                                <label>Full Name</label>
                                                <p class="form-control-static"><?php echo htmlspecialchars($full_name); ?></p>
                                            </div>
                                            <div class="form-group">
                                                <label>Email</label>
                                                <p class="form-control-static"><?php echo htmlspecialchars($email); ?></p>
                                            </div>
                                        </div>

                                        <!-- Password Tab -->
                                        <div class="tab-pane" id="password_tab">
                                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                                                <div class="form-group <?php echo (!empty($current_password_err)) ? 'has-error' : ''; ?>">
                                                    <label>Current Password</label>
                                                    <input type="password" name="current_password" class="form-control" value="<?php echo $current_password; ?>">
                                                    <span class="help-block"><?php echo $current_password_err; ?></span>
                                                </div>
                                                <div class="form-group <?php echo (!empty($new_password_err)) ? 'has-error' : ''; ?>">
                                                    <label>New Password</label>
                                                    <input type="password" name="new_password" class="form-control" value="<?php echo $new_password; ?>">
                                                    <span class="help-block"><?php echo $new_password_err; ?></span>
                                                </div>
                                                <div class="form-group <?php echo (!empty($confirm_password_err)) ? 'has-error' : ''; ?>">
                                                    <label>Confirm Password</label>
                                                    <input type="password" name="confirm_password" class="form-control" value="<?php echo $confirm_password; ?>">
                                                    <span class="help-block"><?php echo $confirm_password_err; ?></span>
                                                </div>
                                                <div class="box-footer">
                                                    <input type="submit" class="btn btn-primary" name="password_submit" value="Update Password">
                                                </div>
                                            </form>
                                        </div>

                                        <!-- Full Name Tab -->
                                        <div class="tab-pane" id="name_tab">
                                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                                                <div class="form-group <?php echo (!empty($full_name_err)) ? 'has-error' : ''; ?>">
                                                    <label>Full Name</label>
                                                    <input type="text" name="full_name" class="form-control" value="<?php echo $full_name; ?>">
                                                    <span class="help-block"><?php echo $full_name_err; ?></span>
                                                </div>
                                                <div class="box-footer">
                                                    <input type="submit" class="btn btn-primary" name="name_submit" value="Update Full Name">
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <?php require_once 'includes/footer.php'; ?>
    </div>
    <!-- AdminLTE App -->
    <script src="../adminlte/js/adminlte.min.js"></script>
    <!-- Include jQuery -->
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <!-- Include Bootstrap JS -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>
