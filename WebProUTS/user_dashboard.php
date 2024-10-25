<?php
// Start a session
session_start();

// Redirect to login if user is not logged in
if (!isset($_SESSION['username'])) {
    header("Location: /EventOrganizingWannabe/index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Management System</title>
    <link rel="stylesheet" href="dashboard.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container text-center mt-5">
        <h1>Welcome to the Event Management System</h1>
        <p class="lead">Manage your events efficiently!</p>
        <div class="mt-4">
            <h2>Hello, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
            <a href="/EventOrganizingWannabe/WebProUTS/available_events.php" class="btn btn-secondary">View Available Events</a>
            <a href="/EventOrganizingWannabe/WebProUTS/profile.php" class="btn btn-info">Profile</a>
            <a href="/EventOrganizingWannabe/WebProUTS/logout.php" class="btn btn-danger">Logout</a>
        </div>
    </div>
</body>
</html>
