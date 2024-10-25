<?php
// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Log the logout action (optional)
if (isset($_SESSION['username'])) {
    error_log("User logged out: " . $_SESSION['username']);
}

// Clear all session variables
$_SESSION = array();

// Clear the session cookie
if (isset($_COOKIE[session_name()])) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 3600,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Clear specific cookies used in the login system
$cookies_to_clear = [
    'username',
    'remember',
    'user_id',
    'role'
];

foreach ($cookies_to_clear as $cookie) {
    if (isset($_COOKIE[$cookie])) {
        setcookie($cookie, '', time() - 3600, '/');
    }
}

// Destroy the session
session_destroy();

// Redirect to login page with a success message
header("Location: /EventOrganizingWannabe?logout=success");
exit();
?>