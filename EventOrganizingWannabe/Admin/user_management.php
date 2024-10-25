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
    $sql = "SELECT e.event_name, e.event_date, e.image
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
    <link rel="stylesheet" href="dashboard.css"> <!-- Your CSS file -->
    <title>User Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script> 
</head>
<body>
<nav class="navbar navbar-expand-lg">
    <div class="container-fluid">
        <a class="navbar-brand" href="admin_dashboard.php">Admin Dashboard</a>
        
        <button class="navbar-toggler" type="button" 
                data-bs-toggle="collapse" 
                data-bs-target="#navbarNav" 
                aria-controls="navbarNav" 
                aria-expanded="false" 
                aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'admin_dashboard.php' ? 'active' : ''; ?>" 
                       href="admin_dashboard.php">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'event_management.php' ? 'active' : ''; ?>" 
                       href="event_management.php">Event Management</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'user_management.php' ? 'active' : ''; ?>" 
                       href="user_management.php">User Management</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php" onclick="return confirm('Are you sure you want to log out?');">Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

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
            <?php while ($user = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $user['id']; ?></td>
                <td><?php echo htmlspecialchars($user['username']); ?></td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
                <td>
                    <div class="d-flex flex-wrap">
                        <?php
                        $events = getUserRegisteredEvents($user['id'], $conn);
                        if ($events->num_rows > 0):
                            while ($event = $events->fetch_assoc()):
                        ?>
                            <div class="card me-2 mb-2" style="width: 8rem;">
                                <img src="<?php echo htmlspecialchars($event['image']); ?>" class="card-img-top" alt="Event Image" style="height: 80px; object-fit: cover;">
                                <div class="card-body p-1 text-center">
                                    <p class="card-title" style="font-size: 0.9rem;"><?php echo htmlspecialchars($event['event_name']); ?></p>
                                    <p class="card-text" style="font-size: 0.8rem;"><?php echo date('M d, Y', strtotime($event['event_date'])); ?></p>
                                </div>
                            </div>
                        <?php endwhile; else: ?>
                            <p>No events registered</p>
                        <?php endif; ?>
                    </div>
                </td>
                <td>
                    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal" data-user-id="<?php echo $user['id']; ?>">Delete</button>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this user? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <form method="POST">
                    <input type="hidden" name="delete_user_id" id="delete_user_id" value="">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const deleteModal = document.getElementById('deleteModal');
    deleteModal.addEventListener('show.bs.modal', event => {
        const button = event.relatedTarget; // Button that triggered the modal
        const userId = button.getAttribute('data-user-id'); // Extract info from data-* attributes
        const modalInput = deleteModal.querySelector('#delete_user_id');
        modalInput.value = userId; // Update the modal's content
    });
</script>
</body>
</html>

<?php
$conn->close();
?>