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
    // Validasi ID layanan
    if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
        throw new Exception('ID layanan tidak valid');
    }
    
    $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
    
    if ($id === false) {
        throw new Exception('ID layanan harus berupa angka');
    }
    
    // Cek apakah layanan ada
    $check = $db->prepare("SELECT id FROM layanan WHERE id = ?");
    $check->execute([$id]);
    
    if ($check->rowCount() === 0) {
        throw new Exception('Layanan tidak ditemukan');
    }
    
    // Hapus layanan
    $query = "DELETE FROM layanan WHERE id = ?";
    $stmt = $db->prepare($query);
    
    if ($stmt->execute([$id])) {
        $response = [
            'status' => 'success',
            'message' => 'Layanan berhasil dihapus'
        ];
    } else {
        throw new Exception('Gagal menghapus layanan');
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