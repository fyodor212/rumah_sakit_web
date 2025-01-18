<?php
require_once '../config/database.php';
require_once '../config/functions.php';

// Cek apakah user adalah admin
if (!isAdmin()) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

try {
    // Hitung total pesan yang belum dibaca
    $query = "SELECT COUNT(*) FROM kontak WHERE status = 'unread'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $unread = $stmt->fetchColumn();
    
    // Hitung pesan baru (misalnya dalam 5 menit terakhir)
    $query = "SELECT COUNT(*) FROM kontak 
              WHERE status = 'unread' 
              AND created_at >= NOW() - INTERVAL 5 MINUTE";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $new_messages = $stmt->fetchColumn();
    
    echo json_encode([
        'unread' => $unread,
        'new_messages' => $new_messages
    ]);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?> 