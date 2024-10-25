<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: /EventOrganizingWannabe/index.php");
    exit();
}
require 'db.php';

// Get the event ID
if (isset($_GET['event_id'])) {
    $event_id = $_GET['event_id'];

    // Fetch the list of registrants
    $sql = "SELECT users.username, users.email, registrations.registered_at 
            FROM registrations 
            JOIN users ON registrations.user_id = users.id 
            WHERE registrations.event_id = $event_id";
    $result = $conn->query($sql);

    // Set headers for the CSV file
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename="registrants_event_' . $event_id . '.csv"');

    // Open PHP output stream as a file
    $output = fopen('php://output', 'w');

    // Write the column headers
    fputcsv($output, ['Username', 'Email', 'Registered At']);

    // Write the registrants' data
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            fputcsv($output, [$row['username'], $row['email'], $row['registered_at']]);
        }
    }

    // Close the output stream
    fclose($output);
    exit(); // Exit after finishing the CSV download
}
$conn->close();
?>