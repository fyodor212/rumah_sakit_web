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
    // Debug: Log semua POST data
    error_log("POST data: " . print_r($_POST, true));
    
    // Validasi ID dokter
    if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
        throw new Exception('ID dokter tidak valid');
    }
    
    $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
    
    if ($id === false) {
        throw new Exception('ID dokter harus berupa angka');
    }
    
    // Validasi input
    $hari = isset($_POST['hari']) ? trim($_POST['hari']) : '';
    $jadwal = isset($_POST['jadwal']) ? trim($_POST['jadwal']) : '';
    $status = isset($_POST['status']) ? trim($_POST['status']) : 'aktif';
    
    // Debug: Log nilai input setelah trim
    error_log("Hari: " . $hari);
    error_log("Jadwal: " . $jadwal);
    error_log("Status: " . $status);
    
    // Validasi status
    if (!in_array($status, ['aktif', 'tidak_aktif'])) {
        error_log("Status tidak valid: " . $status);
        throw new Exception('Status tidak valid');
    }
    
    // Validasi format jadwal (jika diisi)
    if (!empty($jadwal)) {
        error_log("Validasi format jadwal untuk: " . $jadwal);
        
        if (!preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]-([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $jadwal)) {
            error_log("Format jadwal tidak valid");
            throw new Exception('Format jadwal tidak valid (gunakan format JJ:MM-JJ:MM)');
        }
        
        // Validasi jam mulai < jam selesai
        list($start, $end) = explode('-', $jadwal);
        if (strtotime($start) >= strtotime($end)) {
            error_log("Jam mulai ($start) lebih besar atau sama dengan jam selesai ($end)");
            throw new Exception('Jam mulai harus lebih kecil dari jam selesai');
        }
    }
    
    // Cek apakah dokter ada
    $check = $db->prepare("SELECT id FROM dokter WHERE id = ?");
    $check->execute([$id]);
    
    if ($check->rowCount() === 0) {
        throw new Exception('Dokter tidak ditemukan');
    }
    
    // Debug: Log query dan parameter
    $query = "UPDATE dokter SET hari = ?, jadwal = ?, status = ? WHERE id = ?";
    error_log("Query: " . $query);
    error_log("Parameters: " . print_r([$hari, $jadwal, $status, $id], true));
    
    // Update jadwal dokter
    $stmt = $db->prepare($query);
    
    if ($stmt->execute([$hari, $jadwal, $status, $id])) {
        $response = [
            'status' => 'success',
            'message' => 'Jadwal dokter berhasil diperbarui'
        ];
    } else {
        throw new Exception('Gagal memperbarui jadwal dokter');
    }
    
} catch (Exception $e) {
    error_log("Error in update_doctor_schedule: " . $e->getMessage());
    $response = [
        'status' => 'error',
        'message' => $e->getMessage()
    ];
}

// Kirim response
header('Content-Type: application/json');
echo json_encode($response); 