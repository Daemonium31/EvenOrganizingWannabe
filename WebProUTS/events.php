<?php
session_start();
include 'db.php';

$query = "SELECT * FROM events WHERE status = 'open' ORDER BY event_date ASC";
$stmt = $conn->prepare($query);
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    $events = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    die("Error in preparing the query: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Available Events</title>
</head>
<body>
    <h2>Available Events</h2>
    <ul>
        <?php foreach ($events as $event): ?>
            <li>
                <a href="event_details.php?id=<?php echo $event['id']; ?>">
                    <?php echo htmlspecialchars($event['event_name']); ?> - <?php echo htmlspecialchars($event['event_date']); ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</body>
</html>
