<?php
// Pastikan session dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../Helpers/helpers.php';
require_once __DIR__ . '/../../../../config/database.php';

// Cek akses
if (!isAdmin()) {
    header('Location: index.php');
    exit;
}

// Inisialisasi response
$response = [
    'status' => 'error',
    'message' => 'Terjadi kesalahan'
];

try {
    // Validasi input
    if (empty($_POST['id']) || empty($_POST['nama']) || empty($_POST['deskripsi']) || empty($_POST['kategori']) || !isset($_POST['harga'])) {
        throw new Exception('Semua field harus diisi');
    }

    // Sanitasi input
    $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
    $nama = htmlspecialchars(trim($_POST['nama']));
    $deskripsi = htmlspecialchars(trim($_POST['deskripsi']));
    $kategori = htmlspecialchars(trim($_POST['kategori']));
    $harga = filter_var($_POST['harga'], FILTER_VALIDATE_FLOAT);
    $status = $_POST['status'] ?? 'tersedia';

    // Validasi status
    if (!in_array($status, ['tersedia', 'tidak_tersedia'])) {
        $status = 'tersedia'; // Default value jika tidak valid
    }

    // Query untuk update layanan
    $query = "UPDATE layanan SET nama = ?, deskripsi = ?, kategori = ?, harga = ?, status = ? WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$nama, $deskripsi, $kategori, $harga, $status, $id]);

    $response = [
        'status' => 'success',
        'message' => 'Layanan berhasil diperbarui'
    ];

} catch (Exception $e) {
    $response = [
        'status' => 'error',
        'message' => $e->getMessage()
    ];
}

// Kirim response
header('Content-Type: application/json');
echo json_encode($response); 