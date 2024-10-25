<?php 
session_start();
require 'db.php'; // Koneksi ke database

// Proses Penyimpanan Registrasi Event Baru
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id']; // Assuming 'user_id' is stored in the session after login
    $event_id = $_POST['event_id'];

    // Siapkan dan jalankan query untuk menyimpan registrasi event
    $stmt = $conn->prepare("INSERT INTO registrations (user_id, event_id, registered_at) VALUES (?, ?, NOW())");
    $stmt->bind_param("ii", $user_id, $event_id);
    $stmt->execute();

    // Redirect ke halaman yang sama agar tidak submit ulang
    header("Location: /EventOrganizingWannabe/WebProUTS/events.php");
    exit;
}

// Proses Cancel Registrasi Event (Hapus berdasarkan ID registrasi)
if (isset($_GET['cancel_id'])) {
    $cancel_id = $_GET['cancel_id'];
    $stmt = $conn->prepare("DELETE FROM registrations WHERE id = ?");
    $stmt->bind_param("i", $cancel_id);
    $stmt->execute();

    // Redirect untuk memperbarui daftar event
    header("Location: events.php");
    exit;
}

// Ambil semua registrasi yang sudah dilakukan user
$user_id = $_SESSION['username'];
$stmt = $conn->prepare("SELECT e.event_name, e.event_date, e.event_time, e.location, e.description, r.id AS registration_id 
                        FROM registrations r
                        JOIN events e ON r.event_id = e.id
                        WHERE r.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$registrations = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="dashboard.css">
    <title>Daftar Event Terdaftar</title>
</head>
<body>
    <h1>Daftar Event yang Terdaftar</h1>

    <!-- Form untuk Register Event Baru -->
    <form method="POST" action="events.php">
        <select name="event_id" required>
            <?php
            // Fetch available events from the database
            $event_result = $conn->query("SELECT id, event_name FROM events");
            while ($event = $event_result->fetch_assoc()) {
                echo "<option value='" . $event['id'] . "'>" . htmlspecialchars($event['event_name']) . "</option>";
            }
            ?>
        </select>
        <button type="submit">Register Event</button>
    </form>

    <hr>

    <!-- Tabel Daftar Registrasi Event -->
    <table border="1">
        <tr>
            <th>Nama Event</th>
            <th>Tanggal</th>
            <th>Waktu</th>
            <th>Lokasi</th>
            <th>Deskripsi</th>
            <th>Aksi</th>
        </tr>
        <?php foreach ($registrations as $registration): ?>
            <tr>
                <td><?php echo htmlspecialchars($registration['event_name']); ?></td>
                <td><?php echo htmlspecialchars($registration['event_date']); ?></td>
                <td><?php echo htmlspecialchars($registration['event_time']); ?></td>
                <td><?php echo htmlspecialchars($registration['location']); ?></td>
                <td><?php echo htmlspecialchars($registration['description']); ?></td>
                <td>
                    <a href="event_details.php?id=<?php echo $registration['registration_id']; ?>">View Details</a> | 
                    <a href="?cancel_id=<?php echo $registration['registration_id']; ?>" 
                       onclick="return confirm('Apakah Anda yakin ingin membatalkan registrasi event ini?');">Cancel</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>