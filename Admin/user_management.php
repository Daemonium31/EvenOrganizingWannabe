<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: /EventOrganizingWannabe/login.php");
    exit();
}

// Database connection
require 'db.php';

// Handle deletion of user
if (isset($_POST['delete_user_id'])) {
    $id = intval($_POST['delete_user_id']);
    $sql = "DELETE FROM users WHERE id = $id";
    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('User deleted successfully!');</script>";
    } else {
        echo "<script>alert('Error deleting user: " . $conn->error . "');</script>";
    }
}

// Fetch users from the database
$sql = "SELECT * FROM users WHERE role != 'Admin'";
$result = $conn->query($sql);

// Function to fetch user's registered events
function getUserRegisteredEvents($user_id, $conn) {
    $sql = "SELECT e.event_name, e.event_date, e.event_time, e.location, e.description, e.max_participants, e.image
            FROM events e
            JOIN registrations r ON e.id = r.event_id
            WHERE r.user_id = $user_id";
    return $conn->query($sql);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <link rel="stylesheet" href="dashboard.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container">
    <h1>User Management</h1>
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Registered Events</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($user = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['id']); ?></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td>
                            <div class="d-flex flex-wrap">
                                <?php
                                $events = getUserRegisteredEvents($user['id'], $conn);
                                if ($events && $events->num_rows > 0):
                                    while ($event = $events->fetch_assoc()):
                                ?>
                                    <div class="card me-2 mb-2" style="width: 8rem;">
                                        <img src="uploads/<?php echo htmlspecialchars($event['image']); ?>" alt="Event Image" class="card-img-top" style="height: 80px; object-fit: cover;">
                                        <div class="card-body p-1 text-center">
                                            <h6 class="card-title" style="font-size: 0.9rem;"><?php echo htmlspecialchars($event['event_name']); ?></h6>
                                            <p class="card-text" style="font-size: 0.8rem;"><strong>Date:</strong> <?php echo htmlspecialchars(date('M d, Y', strtotime($event['event_date']))); ?></p>
                                            <p class="card-text" style="font-size: 0.8rem;"><strong>Location:</strong> <?php echo htmlspecialchars($event['location']); ?></p>
                                        </div>
                                    </div>
                                <?php endwhile; else: ?>
                                    <p>No events registered</p>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <form action="" method="POST">
                                <input type="hidden" name="delete_user_id" value="<?php echo htmlspecialchars($user['id']); ?>">
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this user?');">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5">No users found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>