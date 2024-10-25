<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: /EventOrganizingWannabe/index.php");
    exit();
}
require 'db.php';

// Handle delete request
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $sql = "DELETE FROM events WHERE id = $delete_id";
    if ($conn->query($sql) === TRUE) {
        $message = "Event deleted successfully!";
    } else {
        $message = "Error deleting event: " . $conn->error;
    }

    echo "<script type='text/javascript'>alert('$message');</script>";
}

// Handle edit request
if (isset($_POST['edit_event'])) {
    $id = $_POST['id'];
    $event_name = $_POST['event_name'];
    $event_date = $_POST['event_date'];
    $event_time = $_POST['event_time'];
    $location = $_POST['location'];
    $description = $_POST['description'];
    $max_participants = $_POST['max_participants'];
    $image = $_FILES['image']['name'];
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($image);

    // Move uploaded file if a new one is uploaded
    if (!empty($_FILES['image']['name'])) {
        move_uploaded_file($_FILES['image']['tmp_name'], $target_file);
    } else {
        // If no new image, retain the existing one
        $image_query = $conn->query("SELECT image FROM events WHERE id = $id");
        $image_result = $image_query->fetch_assoc();
        $image = $image_result['image'];
    }

    $sql = "UPDATE events SET event_name='$event_name', event_date='$event_date', event_time='$event_time', location='$location', description='$description', max_participants='$max_participants', image='$image' WHERE id=$id";

    if ($conn->query($sql) === TRUE) {
        $message = "Event updated successfully!";
    } else {
        $message = "Error updating event: " . $conn->error;
    }

    echo "<script type='text/javascript'>alert('$message');</script>";
}

// Handle create request
if (isset($_POST['create_event'])) {
    $event_name = $_POST['event_name'];
    $event_date = $_POST['event_date'];
    $event_time = $_POST['event_time'];
    $location = $_POST['location'];
    $description = $_POST['description'];
    $max_participants = $_POST['max_participants'];
    $image = $_FILES['image']['name'];
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($image);

    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
        $sql = "INSERT INTO events (event_name, event_date, event_time, location, description, max_participants, image) VALUES ('$event_name', '$event_date', '$event_time', '$location', '$description', '$max_participants', '$image')";
        if ($conn->query($sql) === TRUE) {
            $message = "New event created successfully!";
        } else {
            $message = "Error creating event: " . $conn->error;
        }
    } else {
        $message = "Error uploading image.";
    }

    // Display the alert message
    echo "<script type='text/javascript'>alert('$message');</script>";
}

// Fetch events to display
$sql = "SELECT *, (SELECT COUNT(*) FROM registrations WHERE event_id = events.id) AS current_participants FROM events";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="dashboard.css"> <!-- Your CSS file -->
    <title>Event Management</title>
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
    <h1>Event Management</h1>

    <!-- Create Event Form -->
    <h3>Create New Event</h3>
    <form method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="event_name">Event Name:</label>
            <input type="text" class="form-control" name="event_name" required>
        </div>
        <div class="form-group">
            <label for="event_date">Event Date:</label>
            <input type="date" class="form-control" name="event_date" required>
        </div>
        <div class="form-group">
            <label for="event_time">Event Time:</label>
            <input type="time" class="form-control" name="event_time" required>
        </div>
        <div class="form-group">
            <label for="location">Location:</label>
            <input type="text" class="form-control" name="location" required>
        </div>
        <div class="form-group">
            <label for="description">Description:</label>
            <textarea class="form-control" name="description" required></textarea>
        </div>
        <div class="form-group">
            <label for="max_participants">Max Participants:</label>
            <input type="number" class="form-control" name="max_participants" required>
        </div>
        <div class="form-group">
            <label for="image">Upload Image:</label>
            <input type="file" class="form-control" name="image" required>
        </div>
        <button type="submit" name="create_event" class="btn btn-primary">Create Event</button>
    </form>

    <hr>

    <!-- Table to display events -->
    <h3>Current Events</h3>
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Event Name</th>
                <th>Date</th>
                <th>Time</th>
                <th>Location</th>
                <th>Description</th>
                <th>Max Participants</th>
                <th>Current Participants</th>
                <th>Status</th>
                <th>Image</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= $row['event_name'] ?></td>
                        <td><?= $row['event_date'] ?></td>
                        <td><?= $row['event_time'] ?></td>
                        <td><?= $row['location'] ?></td>
                        <td><?= $row['description'] ?></td>
                        <td><?= $row['max_participants'] ?></td>
                        <td><?= $row['current_participants'] ?></td>
                        <td style="color: <?= $row['current_participants'] >= $row['max_participants'] ? 'red' : 'green' ?>;">
                            <?= $row['current_participants'] >= $row['max_participants'] ? 'CLOSED' : 'OPEN' ?>
                        </td>
                        <td><img src="uploads/<?= $row['image'] ?>" alt="Event Image" style="width: 100px;"></td>
                        <td>
                        <button type="button" class="btn btn-warning" onclick="toggleEditForm(<?= $row['id'] ?>)">Edit</button>

                            <!-- Edit form, initially hidden -->
                        <form method="post" enctype="multipart/form-data" id="editForm_<?= $row['id'] ?>" style="display:none;">
                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                            <input type="text" name="event_name" value="<?= $row['event_name'] ?>" required>
                            <input type="date" name="event_date" value="<?= $row['event_date'] ?>" required>
                            <input type="time" name="event_time" value="<?= $row['event_time'] ?>" required>
                            <input type="text" name="location" value="<?= $row['location'] ?>" required>
                            <textarea name="description" required><?= $row['description'] ?></textarea>
                            <input type="number" name="max_participants" value="<?= $row['max_participants'] ?>" required>
                            <input type="file" name="image">
                            <button type="submit" name="edit_event" class="btn btn-success">Save</button>
                        </form>
                            <!-- Delete link -->
                            <a href="?delete_id=<?= $row['id'] ?>" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                            <a href="view_registrants.php?event_id=<?= $row['id'] ?>" class="btn btn-info">View Registrants</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="11">No events found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<script>
function toggleEditForm(id) {
    var form = document.getElementById('editForm_' + id);
    if (form.style.display === 'none') {
        form.style.display = 'block'; // Show form
    } else {
        form.style.display = 'none';  // Hide form
    }
}
</script>
</body>
</html>

<?php
$conn->close();
?>
