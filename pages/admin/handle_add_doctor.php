<?php
// Matikan error reporting untuk output
error_reporting(0);
ini_set('display_errors', 0);

// Matikan semua output buffering
while (ob_get_level()) {
    ob_end_clean();
}

// Function untuk mengirim response JSON
function sendJsonResponse($status, $message, $code = 200) {
    header_remove(); // Hapus semua header yang mungkin sudah terkirim
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    http_response_code($code);
    
    die(json_encode([
        'status' => $status,
        'message' => $message
    ], JSON_UNESCAPED_UNICODE));
}

try {
    // Load database dan functions
    require_once __DIR__ . '/../../../../config/database.php';
    require_once __DIR__ . '/../../../../config/functions.php';

    // Cek akses
    if (!isAdmin()) {
        sendJsonResponse('error', 'Anda tidak memiliki akses untuk melakukan ini.', 403);
    }

    // Validasi input
    $nama = trim($_POST['nama'] ?? '');
    $spesialisasi = trim($_POST['spesialisasi'] ?? '');
    $hari = isset($_POST['hari']) ? (array)$_POST['hari'] : [];
    $jam_mulai = trim($_POST['jam_mulai'] ?? '');
    $jam_selesai = trim($_POST['jam_selesai'] ?? '');
    $status = trim($_POST['status'] ?? 'aktif');

    // Validasi field yang required
    if (empty($nama)) {
        sendJsonResponse('error', 'Nama dokter harus diisi', 400);
    }
    if (empty($spesialisasi)) {
        sendJsonResponse('error', 'Spesialisasi harus diisi', 400);
    }
    if (empty($hari)) {
        sendJsonResponse('error', 'Pilih minimal satu hari praktik', 400);
    }
    if (empty($jam_mulai) || empty($jam_selesai)) {
        sendJsonResponse('error', 'Jam praktik harus diisi', 400);
    }

    // Validasi format jam
    if (strtotime($jam_mulai) >= strtotime($jam_selesai)) {
        sendJsonResponse('error', 'Jam selesai harus lebih besar dari jam mulai', 400);
    }

    // Validasi status
    if (!in_array($status, ['aktif', 'tidak aktif'])) {
        $status = 'aktif';
    }

    // Format hari praktik menjadi string
    $hari_praktik = implode(', ', $hari);
    
    // Format jadwal
    $jadwal = $jam_mulai . ' - ' . $jam_selesai;

    // Simpan ke database
    $query = "INSERT INTO dokter (nama, spesialisasi, hari, jadwal, status, created_at) 
              VALUES (?, ?, ?, ?, ?, NOW())";
    $stmt = $db->prepare($query);
    
    if (!$stmt->execute([$nama, $spesialisasi, $hari_praktik, $jadwal, $status])) {
        throw new PDOException('Gagal menyimpan data dokter');
    }

    sendJsonResponse('success', 'Dokter berhasil ditambahkan');

} catch (PDOException $e) {
    sendJsonResponse('error', 'Terjadi kesalahan pada database', 500);
} catch (Exception $e) {
    sendJsonResponse('error', $e->getMessage(), 400);
} 