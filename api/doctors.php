<?php
header('Content-Type: application/json');
require_once '../config/database.php';

try {
    $query = "SELECT d.id, d.nama, d.spesialisasi, d.pengalaman_tahun, d.deskripsi, 
              (SELECT COUNT(*) FROM booking b WHERE b.dokter_id = d.id AND b.status = 'completed') as total_pasien
              FROM dokter d 
              WHERE d.status = 'active'";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format response
    foreach ($doctors as &$doctor) {
        // Add jadwal dokter
        $jadwal_query = "SELECT hari, jam_mulai, jam_selesai 
                        FROM jadwal_dokter 
                        WHERE dokter_id = ? AND status = 'active'";
        $jadwal_stmt = $db->prepare($jadwal_query);
        $jadwal_stmt->execute([$doctor['id']]);
        $doctor['jadwal'] = $jadwal_stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    echo json_encode([
        'status' => 'success',
        'data' => $doctors
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Terjadi kesalahan pada server'
    ]);
} 