<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: /EventOrganizingWannabe/index.php');
    exit;
}

if (isset($_GET['id'])) {
    $event_id = $_GET['id'];
    $user_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("DELETE FROM registrations WHERE user_id = ? AND event_id = ?");
    $stmt->execute([$user_id, $event_id]);
    $_SESSION['message'] = "You have successfully canceled your registration.";
}

header('Location: /EventOrganizingWannabe/WebProUTS/profile.php');
exit;
?>
