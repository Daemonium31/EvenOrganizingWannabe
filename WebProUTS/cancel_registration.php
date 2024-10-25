<?php
session_start(); // Mulai sesi untuk menyimpan informasi pengguna

// Ambil user_id dari sesi
$user_id = $_SESSION['user_id']; // Sesuaikan dengan sistem login

// Database connection
$conn = new mysqli('localhost', 'lisc6834_sean', 'seanbswisnushakira', 'lisc6834_eventdb');


// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Cek apakah form dikirim
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $registration_id = intval($_POST['registration_id']);

    // Hapus registrasi dari tabel registrations
    $sql = "DELETE FROM registrations WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $registration_id, $user_id);
    if ($stmt->execute()) {
        echo "Registration canceled successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}

$conn->close();
?>
