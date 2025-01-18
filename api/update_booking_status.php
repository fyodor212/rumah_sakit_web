<?php
require_once '../config/database.php';
require_once '../config/functions.php';

// Cek apakah permintaan adalah POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari permintaan
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'];
    $status = $data['status'];

    // Validasi input
    if (!in_array($status, ['confirmed', 'cancelled'])) {
        echo json_encode(['success' => false, 'message' => 'Status tidak valid']);
        exit;
    }

    try {
        // Update status booking
        $query = "UPDATE booking SET status = ? WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$status, $id]);

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        error_log($e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan saat memperbarui status']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Metode tidak diizinkan']);
} 