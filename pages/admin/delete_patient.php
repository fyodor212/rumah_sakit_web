<?php
require_once 'config/database.php';
require_once 'config/functions.php';

// Cek akses admin
requireAdmin();

// Ambil ID dari query string
$id = $_GET['id'] ?? null;

if (!$id) {
    $_SESSION['message'] = "ID pasien tidak valid.";
    $_SESSION['message_type'] = 'danger';
    header('Location: index.php?page=admin/manage_patients');
    exit;
}

try {
    // Hapus data pasien berdasarkan ID
    $query = "DELETE FROM pasien WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$id]);

    $_SESSION['message'] = "Pasien berhasil dihapus.";
    $_SESSION['message_type'] = 'success';
} catch (PDOException $e) {
    error_log($e->getMessage());
    $_SESSION['message'] = "Terjadi kesalahan saat menghapus pasien.";
    $_SESSION['message_type'] = 'danger';
}

// Redirect ke halaman manajemen pasien
header('Location: index.php?page=admin/manage_patients');
exit; 