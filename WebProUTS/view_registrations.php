<?php
session_start(); // Start the session to store user information

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /EventOrganizingWannabe/index.php"); // Redirect to login if not logged in
    exit();
}

// Get user_id from session
$user_id = $_SESSION['user_id']; // Assuming user_id is stored in the session

// Database connection
$conn = new mysqli('localhost', 'lisc6834_sean', 'seanbswisnushakira', 'lisc6834_eventdb');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch the list of registered events for the user
$sql = "SELECT e.event_name, e.description, e.event_time, e.event_date, r.id AS registration_id 
        FROM registrations as r 
        JOIN events e ON r.event_id = e.id 
        WHERE r.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if any registrations were found
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<h2>" . htmlspecialchars($row['event_name']) . "</h2>";
        echo "<p><strong>Schedule:</strong> " . htmlspecialchars($row['event_time']) . "</p>";
        echo "<p><strong>Date:</strong> " . htmlspecialchars($row['event_date']) . "</p>";
        echo "<p><strong>Description:</strong> " . nl2br(htmlspecialchars($row['description'])) . "</p>";
        
        // Cancel registration button
        echo "<form action='cancel_registration.php' method='POST'>";
        echo "<input type='hidden' name='registration_id' value='" . htmlspecialchars($row['registration_id']) . "'>";
        echo "<button type='submit' class='btn btn-danger' onclick='return confirm(\"Are you sure you want to cancel this registration?\")'>Cancel Registration</button>";
        echo "</form>";
        echo "<hr>"; // Separator between events
    }
} else {
    echo "<p>No registered events found.</p>";
}

// Close the statement and connection
$stmt->close();
$conn->close();
?>