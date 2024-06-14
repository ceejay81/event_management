<?php
// Include necessary files
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Initialize variables
$full_name = $email = $password = $confirm_password = $role = '';
$full_name_err = $email_err = $password_err = $confirm_password_err = $role_err = '';

// Process form data when form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate full name
    if (empty(trim($_POST['full_name']))) {
        $full_name_err = 'Please enter your full name.';
    } else {
        $full_name = trim($_POST['full_name']);
    }

    // Validate email
    if (empty(trim($_POST['email']))) {
        $email_err = 'Please enter your email.';
    } else {
        $email = trim($_POST['email']);
        // Check if email already exists
        $sql = 'SELECT user_id FROM users WHERE email = ?';
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, 's', $param_email);
            $param_email = $email;
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    $email_err = 'This email is already taken.';
                }
            } else {
                echo 'Oops! Something went wrong. Please try again later.';
            }
            mysqli_stmt_close($stmt);
        }
    }

    // Validate password
    if (empty(trim($_POST['password']))) {
        $password_err = 'Please enter a password.';
    } elseif (strlen(trim($_POST['password'])) < 6) {
        $password_err = 'Password must have at least 6 characters.';
    } else {
        $password = trim($_POST['password']);
    }

    // Validate confirm password
    if (empty(trim($_POST['confirm_password']))) {
        $confirm_password_err = 'Please confirm password.';
    } else {
        $confirm_password = trim($_POST['confirm_password']);
        if ($password != $confirm_password) {
            $confirm_password_err = 'Password did not match.';
        }
    }

    // Validate role
    if (empty(trim($_POST['role']))) {
        $role_err = 'Please select your role.';
    } else {
        $role = trim($_POST['role']);
        if ($role === 'student') {
            $role_err = 'Only teachers can register students.';
        }
    }

    // Check input errors before inserting into database
    if (empty($full_name_err) && empty($email_err) && empty($password_err) && empty($confirm_password_err) && empty($role_err)) {
        // Prepare an insert statement
        $sql = 'INSERT INTO users (full_name, email, password_hash, role) VALUES (?, ?, ?, ?)';
        if ($stmt = mysqli_prepare($link, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, 'ssss', $param_full_name, $param_email, $param_password, $param_role);

            // Set parameters
            $param_full_name = $full_name;
            $param_email = $email;
            $param_password = password_hash($password, PASSWORD_DEFAULT); // Creates a password hash
            $param_role = $role;

            // Attempt to execute the prepared statement
            if (mysqli_stmt_execute($stmt)) {
                // Redirect to login page
                header('location: login.php');
                exit;
            } else {
                echo 'Something went wrong. Please try again later.';
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }

    // Close connection
    mysqli_close($link);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register</title>

    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="../adminlte/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="../adminlte/css/adminlte.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body class="hold-transition register-page">
<div class="register-box">
    <div class="card">
        <div class="card-body register-card-body">
            <p class="login-box-msg">Register a new membership</p>
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                <div class="input-group mb-3">
                    <input type="text" name="full_name" class="form-control" placeholder="Full name" value="<?php echo $full_name; ?>">
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-user"></span>
                        </div>
                    </div>
                </div>
                <span class="help-block"><?php echo $full_name_err; ?></span>
                <div class="input-group mb-3">
                    <input type="email" name="email" class="form-control" placeholder="Email" value="<?php echo $email; ?>">
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-envelope"></span>
                        </div>
                    </div>
                </div>
                <span class="help-block"><?php echo $email_err; ?></span>
                <div class="input-group mb-3">
                    <input type="password" name="password" class="form-control" placeholder="Password">
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-lock"></span>
                        </div>
                    </div>
                </div>
                <span class="help-block"><?php echo $password_err; ?></span>
                <div class="input-group mb-3">
                    <input type="password" name="confirm_password" class="form-control" placeholder="Retype password">
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-lock"></span>
                        </div>
                    </div>
                </div>
                <span class="help-block"><?php echo $confirm_password_err; ?></span>
                <div class="input-group mb-3">
                    <select name="role" class="form-control">
                        <option value="">Select Role</option>
                        <option value="teacher" <?php echo ($role == 'teacher') ? 'selected' : ''; ?>>Teacher</option>
                        <option value="student" <?php echo ($role == 'student') ? 'selected' : ''; ?>>Student</option>
                    </select>
                </div>
                <span class="help-block"><?php echo $role_err; ?></span>
                <div class="row">
                    <div class="col-8">
                        <button type="submit" class="btn btn-primary btn-block">Register</button>
                    </div>
                    <div class="col-4">
                        <a href="login.php" class="btn btn-link btn-block">Login</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- AdminLTE JavaScript -->
<script src="../adminlte/plugins/jquery/jquery.min.js"></script>
<script src="../adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../adminlte/js/adminlte.min.js"></script>
</body>
</html>
