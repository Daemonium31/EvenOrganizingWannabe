<?php
// Start a session
session_start();

// Database connection
$conn = new mysqli('localhost', 'lisc6834_sean', 'seanbswisnushakira', 'lisc6834_eventdb');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch events from the database
$sql = "SELECT id, event_date, location FROM events WHERE event_date >= NOW() ORDER BY event_date ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Events</title>
    <link rel="stylesheet" href="dashboard.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Available Events</h1>
        <ul class="list-group">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <li class="list-group-item">
                        <a href="event_details.php?id=<?php echo $row['id']; ?>">
                            <?php echo htmlspecialchars($row['location']); ?>
                        </a>
                        <span class="float-end"><?php echo date('Y-m-d H:i', strtotime($row['event_date'])); ?></span>
                    </li>
                <?php endwhile; ?>
            <?php else: ?>
                <li class="list-group-item">No upcoming events available.</li>
            <?php endif; ?>
        </ul>
    </div>
</body>
</html>

<?php
$conn->close();
?>
