<?php
session_start();
// Check if user is logged in AND has admin role
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: /EventOrganizingWannabe/index.php");
    exit();
}
require 'db.php';

// Fetch events from the database
$sql = "SELECT * FROM events";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="dashboard.css"> <!-- Your CSS file -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>        
    <title>Admin Dashboard</title>
    <style>
        .card {
            margin-bottom: 20px;
        }
        .card img {
            width: 100%; /* Make image fill the card */
            height: auto; /* Maintain aspect ratio */
        }
    </style>
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
        <h1>Admin Dashboard</h1>
        <h2>Current Events</h2>

        <div class="row">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <?php
                    // Calculate the number of registrants for each event
                    $event_id = $row['id'];
                    $reg_sql = "SELECT COUNT(*) AS total_registrations FROM registrations WHERE event_id = $event_id";
                    $reg_result = $conn->query($reg_sql);
                    $reg_count = $reg_result->fetch_assoc()['total_registrations'];
                    
                    // Determine the status of the event
                    $status = ($reg_count >= $row['max_participants']) ? 'CLOSED' : 'OPEN';
                    $status_color = ($status == 'CLOSED') ? 'red' : 'green';
                    ?>
                    <div class="col-md-4"> <!-- Card Column -->
                        <div class="card">
                            <img src="uploads/<?php echo htmlspecialchars($row['image']); ?>" alt="Event Image" class="card-img-top">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($row['event_name']); ?></h5>
                                <p class="card-text"><strong>Date:</strong> <?php echo htmlspecialchars($row['event_date']); ?></p>
                                <p class="card-text"><strong>Time:</strong> <?php echo htmlspecialchars($row['event_time']); ?></p>
                                <p class="card-text"><strong>Location:</strong> <?php echo htmlspecialchars($row['location']); ?></p>
                                <p class="card-text"><strong>Description:</strong> <?php echo htmlspecialchars($row['description']); ?></p>
                                <p class="card-text"><strong>Max Participants:</strong> <?php echo htmlspecialchars($row['max_participants']); ?></p>
                                <p class="card-text" style="color: <?php echo $status_color; ?>;">
                                    <strong>Status:</strong> <?php echo $status; ?>
                                </p>
                                <p class="card-text"><strong>Registrations:</strong> <?php echo $reg_count; ?></p>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-warning" role="alert">
                        No events found.
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$conn->close(); // Close the database connection
?>