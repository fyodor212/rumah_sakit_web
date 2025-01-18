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
    // Validasi ID
    if (empty($_GET['id'])) {
        throw new Exception('ID layanan tidak valid');
    }

    $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    if (!$id) {
        throw new Exception('ID layanan tidak valid');
    }

    // Query untuk mengambil detail layanan
    $query = "SELECT * FROM layanan WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$id]);
    $service = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$service) {
        throw new Exception('Layanan tidak ditemukan');
    }

    $response = [
        'status' => 'success',
        'service' => $service
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