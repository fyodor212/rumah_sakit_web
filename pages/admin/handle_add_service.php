<?php
// Pastikan session dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/database.php';
require_once 'config/functions.php';

// Cek akses admin
if (!isAdmin()) {
    http_response_code(403);
    exit(json_encode(['status' => 'error', 'message' => 'Akses ditolak']));
}

// Set header untuk response JSON
header('Content-Type: application/json');

try {
    // Debug: Log semua POST data
    error_log("POST data: " . print_r($_POST, true));

    // Validasi input
    $nama = trim($_POST['nama'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $kategori = trim($_POST['kategori'] ?? '');
    $harga = filter_var($_POST['harga'] ?? 0, FILTER_VALIDATE_FLOAT);
    $status = trim($_POST['status'] ?? 'tersedia');

    // Validasi field yang required
    if (empty($nama)) {
        throw new Exception('Nama layanan harus diisi');
    }

    // Validasi panjang input
    if (strlen($nama) > 100) {
        throw new Exception('Nama layanan maksimal 100 karakter');
    }
    if (!empty($kategori) && strlen($kategori) > 50) {
        throw new Exception('Kategori maksimal 50 karakter');
    }

    // Validasi harga
    if ($harga === false || $harga < 0) {
        throw new Exception('Harga tidak valid');
    }

    // Validasi status
    if (!in_array($status, ['tersedia', 'tidak_tersedia'])) {
        $status = 'tersedia'; // Set default jika tidak valid
    }

    // Debug: Log data yang akan disimpan
    error_log("Data yang akan disimpan: " . print_r([
        'nama' => $nama,
        'deskripsi' => $deskripsi,
        'kategori' => $kategori,
        'harga' => $harga,
        'status' => $status
    ], true));

    // Simpan ke database
    $query = "INSERT INTO layanan (nama, deskripsi, kategori, harga, status) VALUES (?, ?, ?, ?, ?)";
    $stmt = $db->prepare($query);
    
    if ($stmt->execute([$nama, $deskripsi, $kategori, $harga, $status])) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Layanan berhasil ditambahkan'
        ]);
    } else {
        throw new Exception('Gagal menyimpan data layanan');
    }

} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Terjadi kesalahan database: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
} 