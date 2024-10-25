<?php
session_start(); // Make sure to start the session

require 'db.php';

// Check if the user is not logged in or is not an admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: /EventOrganizingWannabe/index.php");
    exit();
}
// Get the event ID
if (isset($_GET['event_id'])) {
    $event_id = $_GET['event_id'];
    
    // Fetch the event details
    $event_sql = "SELECT event_name FROM events WHERE id = $event_id";
    $event_result = $conn->query($event_sql);
    $event = $event_result->fetch_assoc();

    // Fetch the list of registrants
    $sql = "SELECT users.username, users.email, registrations.registered_at 
            FROM registrations 
            JOIN users ON registrations.user_id = users.id 
            WHERE registrations.event_id = $event_id";
    $result = $conn->query($sql);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <title>Registrants for <?= htmlspecialchars($event['event_name']) ?></title>
</head>
<body>
    <div class="container">
        <h1>Registrants for <?= htmlspecialchars($event['event_name']) ?></h1>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Registered At</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['username']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= htmlspecialchars($row['registered_at']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3">No registrants found for this event.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Export to CSV button -->
        <a href="export_registrants.php?event_id=<?= $event_id ?>" class="btn btn-primary">Export to CSV</a>
    </div>
</body>
</html>

<?php $conn->close(); ?>