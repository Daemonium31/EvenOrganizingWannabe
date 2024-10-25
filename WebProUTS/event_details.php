<?php
// Start session to track user login status
session_start();

// Database connection
$conn = new mysqli('localhost', 'lisc6834_sean', 'seanbswisnushakira', 'lisc6834_eventdb');


// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events</title>
    <link rel="stylesheet" href="dashboard.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container">
    <h1>Upcoming Events</h1>

    <!-- The events will be displayed here -->
    <?php
    // Fetch all event details
    $sql = "SELECT id, event_name, description, event_time, event_date FROM events";
    $result = $conn->query($sql);

    // Check if any events were found
    if ($result && $result->num_rows > 0) {
        // Loop through and display each event
        while ($row = $result->fetch_assoc()) {
            echo "<h2>" . htmlspecialchars($row['event_name']) . "</h2>";
            echo "<p><strong>Schedule:</strong> " . htmlspecialchars($row['event_time']) . "</p>";
            echo "<p><strong>Date:</strong> " . htmlspecialchars($row['event_date']) . "</p>";
            echo "<p><strong>Description:</strong> " . nl2br(htmlspecialchars($row['description'])) . "</p>";

            // Registration form for each event
            if (isset($_SESSION['user_id'])) { // Check if user is logged in
                echo '<form action="" method="post">';
                echo '<input type="hidden" name="event_id" value="' . htmlspecialchars($row['id']) . '">';
                echo '<button type="submit" name="register" class="btn btn-primary">Register</button>';
                echo '</form>';
            } else {
                echo '<p>Please log in to register for this event.</p>';
            }
            echo "<hr>"; // Line separator between events
        }
    } else {
        echo "<p>No events found.</p>";
    }

    // Handle registration submission
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
        $event_id = $_POST['event_id'];
        $user_id = $_SESSION['user_id']; // Assuming user ID is stored in session

        // Prepare and bind
        $stmt = $conn->prepare("INSERT INTO registrations (user_id, event_id, registered_at) VALUES (?, ?, NOW())");
        $stmt->bind_param("ii", $user_id, $event_id);

        // Execute the statement
        if ($stmt->execute()) {
            echo "<div class='alert alert-success'>Successfully registered for the event!</div>";
        } else {
            echo "<div class='alert alert-danger'>Error registering for the event: " . htmlspecialchars($stmt->error) . "</div>";
        }

        // Close the prepared statement
        $stmt->close();
    }

    // Close the connection
    $conn->close();
    ?>
</div>

<!-- Add JavaScript to refresh the page every 60 seconds -->
<script>
    setTimeout(function() {
        location.reload();
    }, 60000); // Refresh every 60 seconds
</script>

</body>
</html>