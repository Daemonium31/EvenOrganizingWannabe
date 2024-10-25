<?php
// Start a session
session_start();

// Initialize variables
$username = "";
$password = "";
$error = "";

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve username and password from the form
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    // Check if the remember me checkbox is set
    $remember = isset($_POST['remember']);

    // Add reCAPTCHA verification
    $recaptcha_secret = '6Lcf22oqAAAAALQdMGvEquSTADMpQDMYdlE3xXTQ';
    $recaptcha_response = $_POST['g-recaptcha-response'];

    // Verify the reCAPTCHA response
    $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
    $recaptcha = file_get_contents($recaptcha_url . '?secret=' . $recaptcha_secret . '&response=' . $recaptcha_response);
    $recaptcha = json_decode($recaptcha);

    if ($recaptcha->success) {
        // reCAPTCHA is successful, proceed with login
        require 'db.php';

        // First, handle the special AdminUser case
        if ($username === 'AdminUser' && $password === '123') {
            $_SESSION['username'] = $username;
            $_SESSION['role'] = 'admin';
            
            if ($remember) {
                setcookie("username", $username, time() + 3600, "/", "", true, true);
            }
            
            // Add debug logging
            error_log("Admin user logged in successfully. Role: " . $_SESSION['role']);
            
            header("Location: /EventOrganizingWannabe/Admin/admin_dashboard.php");
            exit();
        }

        // Prepare and bind for regular login
        $stmt = $conn->prepare("SELECT id, password, role FROM users WHERE username = ?");
        if (!$stmt) {
            error_log("Prepare failed: " . $conn->error);
            $error = "Database error occurred.";
        } else {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                
                // Verify the password
                if (password_verify($password, $user['password'])) {
                    // Set session variables
                    $_SESSION['username'] = $username;
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['role'] = $user['role'];
                    
                    // Add debug logging
                    error_log("User logged in successfully. Username: $username, Role: " . $user['role']);

                    // Handle remember me
                    if ($remember) {
                        setcookie("username", $username, time() + 3600, "/", "", true, true);
                    } else {
                        setcookie("username", "", time() - 3600, "/");
                    }

                    // Explicit role check for redirection
                    if (strtolower($user['role']) === 'admin') {
                        error_log("Redirecting admin to admin dashboard");
                        header("Location: /EventOrganizingWannabe/Admin/admin_dashboard.php");
                        exit();
                    } else {
                        error_log("Redirecting regular user to user dashboard");
                        header("Location: user_dashboard.php");
                        exit();
                    }
                } else {
                    $error = "Invalid password.";
                    error_log("Failed login attempt - invalid password for user: $username");
                }
            } else {
                $error = "Invalid username.";
                error_log("Failed login attempt - invalid username: $username");
            }
            $stmt->close();
        }
        $conn->close();
    } else {
        $error = "reCAPTCHA verification failed.";
        error_log("reCAPTCHA verification failed for user: $username");
    }
}

// Add this at the end to check session variables
if (isset($_SESSION['role'])) {
    error_log("Current session role: " . $_SESSION['role']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    <div class="wrapper">
        <form action="" method="POST">
            <h1>Login</h1>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <div class="input-box">
                <input type="text" name="username" placeholder="Username" value="<?php echo htmlspecialchars($username); ?>" required>
                <i class="bx bx-user"></i>
            </div>
            <div class="input-box">
                <input type="password" name="password" placeholder="Password" required>
                <i class='bx bxs-lock-alt'></i>
            </div>
            <div class="remember-forget">
                <label><input type="checkbox" name="remember" <?php if (isset($_COOKIE["username"])) echo "checked"; ?>> Remember me</label>
                <a href="forgotpassword.php">Forgot Password?</a>
            </div>
            <!-- Add reCAPTCHA widget -->
            <div class="g-recaptcha" data-sitekey="6Lcf22oqAAAAAJG5oDwHsKjKYvRQuql1O-QKYmGc"></div> <!-- Replace with your actual site key -->
            <button type="submit" class="btn">Login</button>
            <div class="register-link">
                <p>Don't have an account? <a href="register.php">Register</a></p>
            </div>
        </form>
    </div>

    <!-- Load the reCAPTCHA v2 API script -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</body>
</html>