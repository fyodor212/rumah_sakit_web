<?php
require_once 'config/database.php';
require_once 'config/functions.php';

if (!isLoggedIn() || !isPasien()) {
    header('Location: index.php?page=auth/login');
    exit;
}

$booking_id = $_GET['id'] ?? 0;

try {
    // Ambil data pasien
    $query = "SELECT id FROM pasien WHERE user_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$_SESSION['user_id']]);
    $pasien = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$pasien) {
        throw new Exception("Data pasien tidak ditemukan");
    }

    // Pastikan booking milik pasien ini dan masih pending
    $query = "SELECT * FROM booking 
              WHERE id = ? AND pasien_id = ? AND status = 'pending'";
    $stmt = $db->prepare($query);
    $stmt->execute([$booking_id, $pasien['id']]);
    $booking = $stmt->fetch();

    if ($booking) {
        // Update status menjadi batal
        $query = "UPDATE booking SET status = 'batal' WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$booking_id]);

        $_SESSION['success'] = "Janji berhasil dibatalkan";
    } else {
        $_SESSION['error'] = "Janji tidak ditemukan atau tidak dapat dibatalkan";
    }
} catch (Exception $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
}

// Redirect kembali ke halaman janji
header('Location: index.php?page=patient/my_appointments');
exit; 