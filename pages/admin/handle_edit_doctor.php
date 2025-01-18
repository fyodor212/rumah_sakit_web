<?php
// Matikan output buffering
while (ob_get_level()) {
    ob_end_clean();
}

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Set header untuk response JSON
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

try {
    require_once __DIR__ . '/../../../../config/database.php';
    require_once __DIR__ . '/../../../../config/functions.php';

    // Debug log
    error_log("POST data received: " . print_r($_POST, true));

    // Cek akses admin
    if (!isAdmin()) {
        throw new Exception('Akses ditolak');
    }

    // Validasi input
    if (!isset($_POST['doctor_id']) || !is_numeric($_POST['doctor_id'])) {
        throw new Exception('ID dokter tidak valid');
    }

    // Ambil dan validasi data
    $doctor_id = filter_var($_POST['doctor_id'], FILTER_VALIDATE_INT);
    $nama = trim($_POST['nama'] ?? '');
    $spesialisasi = trim($_POST['spesialisasi'] ?? '');
    $hari = isset($_POST['hari']) ? (is_array($_POST['hari']) ? implode(', ', $_POST['hari']) : $_POST['hari']) : '';
    $jam_mulai = trim($_POST['jam_mulai'] ?? '');
    $jam_selesai = trim($_POST['jam_selesai'] ?? '');
    $status = $_POST['status'] ?? 'aktif';

    // Validasi data wajib
    if (empty($nama) || empty($spesialisasi)) {
        throw new Exception('Nama dan spesialisasi harus diisi');
    }

    // Format jadwal
    $jadwal = '';
    if (!empty($jam_mulai) && !empty($jam_selesai)) {
        $jadwal = $jam_mulai . ' - ' . $jam_selesai;
    }

    // Debug log
    error_log("Processing doctor data - ID: $doctor_id, Nama: $nama, Spesialisasi: $spesialisasi");

    // Update data dokter
    $query = "UPDATE dokter 
              SET nama = :nama,
                  spesialisasi = :spesialisasi,
                  hari = :hari,
                  jadwal = :jadwal,
                  status = :status
              WHERE id = :doctor_id";

    try {
        $stmt = $db->prepare($query);
        $params = [
            ':nama' => $nama,
            ':spesialisasi' => $spesialisasi,
            ':hari' => $hari,
            ':jadwal' => $jadwal,
            ':status' => $status,
            ':doctor_id' => $doctor_id
        ];

        if (!$stmt->execute($params)) {
            throw new Exception('Gagal menyimpan data dokter');
        }

        // Debug log
        error_log("Doctor data updated successfully");

        // Kirim response sukses
        die(json_encode([
            'status' => 'success',
            'message' => 'Data dokter berhasil diperbarui'
        ]));
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        throw new Exception('Terjadi kesalahan database');
    }

} catch (Exception $e) {
    error_log("Error in handle_edit_doctor: " . $e->getMessage());
    http_response_code(400);
    die(json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]));
} 