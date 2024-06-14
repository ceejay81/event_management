<?php
// Start session
session_start();

// Check if the user is logged in as admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header('Location: ../auth/login.php');
    exit;
}

// Include necessary files
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Fetch admin details from database
$user_id = $_SESSION['user_id'];
$user = getUserById($user_id);
// Check if user exists
if (!$user) {
    die('User not found.'); // Handle error appropriately
}

// Initialize variables
$full_name = $user['full_name'];
$email = $user['email'];

// Initialize variables for password update
$current_password = $new_password = $confirm_password = '';
$current_password_err = $new_password_err = $confirm_password_err = '';

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
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
        $sql = "SELECT password_hash FROM users WHERE user_id = ?";
        
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $param_user_id);
            $param_user_id = $user_id;
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    mysqli_stmt_bind_result($stmt, $hashed_password);
                    if (mysqli_stmt_fetch($stmt)) {
                        if (password_verify($current_password, $hashed_password)) {
                            // Password is correct, update password
                            $sql = "UPDATE users SET password_hash = ? WHERE user_id = ?";
                            if ($stmt = mysqli_prepare($link, $sql)) {
                                $param_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                                mysqli_stmt_bind_param($stmt, "si", $param_password_hash, $param_user_id);
                                $param_user_id = $user_id;
                                if (mysqli_stmt_execute($stmt)) {
                                    // Password updated successfully
                                    header("location: profile.php");
                                    exit();
                                } else {
                                    echo "Oops! Something went wrong. Please try again later.";
                                }
                                mysqli_stmt_close($stmt);
                            }
                        } else {
                            $current_password_err = "Invalid password.";
                        }
                    }
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }
            mysqli_stmt_close($stmt);
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
<body>
    <div class="wrapper">
        <?php require_once 'includes/header.php'; ?>
        <div class="content-wrapper">
            <section class="content-header">
                <h1>Profile</h1>
            </section>

            <section class="content">
                <div class="row">
                    <div class="col-md-6">
                        <div class="box box-primary">
                            <div class="box-header with-border">
                                <h3 class="box-title">Update Password</h3>
                            </div>
                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                                <div class="box-body">
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
                                </div>
                                <div class="box-footer">
                                    <input type="submit" class="btn btn-primary" value="Update Password">
                                    <a href="dashboard.php" class="btn btn-default">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="box box-primary">
                            <div class="box-header with-border">
                                <h3 class="box-title">Profile Information</h3>
                            </div>
                            <div class="box-body">
                                <div class="form-group">
                                    <label>Full Name</label>
                                    <p class="form-control-static"><?php echo htmlspecialchars($full_name); ?></p>
                                </div>
                                <div class="form-group">
                                    <label>Email</label>
                                    <p class="form-control-static"><?php echo htmlspecialchars($email); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <?php require_once 'includes/footer.php'; ?>
    </div>
</body>
</html>
