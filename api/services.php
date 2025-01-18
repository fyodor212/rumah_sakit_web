<?php
header('Content-Type: application/json');
require_once '../config/database.php';

try {
    $query = "SELECT id, nama, deskripsi, harga, estimasi_waktu, kategori 
              FROM layanan 
              WHERE status = 'active'";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format response
    foreach ($services as &$service) {
        // Format harga
        $service['harga_formatted'] = 'Rp ' . number_format($service['harga'], 0, ',', '.');
        
        // Get dokter yang melayani
        $dokter_query = "SELECT d.id, d.nama, d.spesialisasi 
                        FROM dokter d 
                        JOIN dokter_layanan dl ON d.id = dl.dokter_id 
                        WHERE dl.layanan_id = ? AND d.status = 'active'";
        $dokter_stmt = $db->prepare($dokter_query);
        $dokter_stmt->execute([$service['id']]);
        $service['dokter'] = $dokter_stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    echo json_encode([
        'status' => 'success',
        'data' => $services
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Terjadi kesalahan pada server'
    ]);
} 