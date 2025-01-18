<?php
// Pastikan session dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../Helpers/helpers.php';
require_once __DIR__ . '/../../../../config/database.php';

// Cek akses dan AJAX request
if (!isAdmin() || !isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    http_response_code(403);
    exit(json_encode(['status' => 'error', 'message' => 'Akses tidak diizinkan']));
}

// Inisialisasi response
$response = ['status' => 'error', 'message' => ''];

try {
    // Validasi ID jadwal
    if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
        throw new Exception('ID jadwal tidak valid');
    }
    
    $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
    
    if ($id === false) {
        throw new Exception('ID jadwal harus berupa angka');
    }
    
    // Cek apakah jadwal ada
    $check = $db->prepare("SELECT id FROM jadwal_dokter WHERE id = ?");
    $check->execute([$id]);
    
    if ($check->rowCount() === 0) {
        throw new Exception('Jadwal tidak ditemukan');
    }
    
    // Hapus jadwal
    $query = "DELETE FROM jadwal_dokter WHERE id = ?";
    $stmt = $db->prepare($query);
    
    if ($stmt->execute([$id])) {
        $response = [
            'status' => 'success',
            'message' => 'Jadwal berhasil dihapus'
        ];
    } else {
        throw new Exception('Gagal menghapus jadwal');
    }
    
} catch (Exception $e) {
    $response = [
        'status' => 'error',
        'message' => $e->getMessage()
    ];
}

// Kirim response
header('Content-Type: application/json');
echo json_encode($response); 