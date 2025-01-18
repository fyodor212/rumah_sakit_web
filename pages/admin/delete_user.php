<?php
require_once 'config/database.php';
require_once 'config/functions.php';

// Cek apakah request adalah AJAX
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    die('Direct access not permitted');
}

// Cek apakah user adalah admin
if (!isAdmin()) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

// Cek apakah ada user_id
if (!isset($_POST['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User ID tidak ditemukan']);
    exit;
}

$userId = $_POST['user_id'];

try {
    // Mulai transaksi
    $db->beginTransaction();
    
    // Cek apakah user yang akan dihapus bukan admin
    $query = "SELECT role FROM users WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user['role'] === 'admin') {
        throw new Exception('Tidak dapat menghapus user admin');
    }
    
    // Hapus data dari tabel terkait
    $tables = ['pasien', 'dokter'];
    foreach ($tables as $table) {
        $query = "DELETE FROM $table WHERE user_id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$userId]);
    }
    
    // Hapus user
    $query = "DELETE FROM users WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$userId]);
    
    // Commit transaksi
    $db->commit();
    
    echo json_encode(['status' => 'success', 'message' => 'User berhasil dihapus']);
} catch (Exception $e) {
    // Rollback jika terjadi error
    $db->rollBack();
    error_log($e->getMessage());
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?> 