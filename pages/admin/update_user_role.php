<?php
require_once 'config/database.php';
require_once 'config/functions.php';

// Cek apakah request adalah AJAX
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    die(json_encode(['status' => 'error', 'message' => 'Direct access not permitted']));
}

// Cek apakah user adalah admin
if (!isAdmin()) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

// Cek apakah ada user_id dan role
if (!isset($_POST['user_id']) || !isset($_POST['role'])) {
    echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap']);
    exit;
}

$userId = $_POST['user_id'];
$newRole = $_POST['role'];

// Validasi role
$validRoles = ['admin', 'dokter', 'pasien'];
if (!in_array($newRole, $validRoles)) {
    echo json_encode(['status' => 'error', 'message' => 'Role tidak valid']);
    exit;
}

try {
    // Mulai transaksi
    $db->beginTransaction();
    
    // Cek apakah user ada
    $query = "SELECT role, username FROM users WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        throw new Exception('User tidak ditemukan');
    }
    
    // Update role user
    $query = "UPDATE users SET role = ? WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$newRole, $userId]);
    
    // Jika role diubah ke dokter, buat entry di tabel dokter
    if ($newRole === 'dokter') {
        // Hapus data dari tabel pasien jika ada
        $query = "DELETE FROM pasien WHERE user_id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$userId]);
        
        // Cek apakah sudah ada di tabel dokter
        $query = "SELECT id FROM dokter WHERE user_id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$userId]);
        
        if (!$stmt->fetch()) {
            // Buat entry baru di tabel dokter
            $query = "INSERT INTO dokter (user_id, nama, status) VALUES (?, ?, 'aktif')";
            $stmt = $db->prepare($query);
            $stmt->execute([$userId, $user['username']]);
        }
    }
    
    // Jika role diubah ke pasien, buat entry di tabel pasien
    if ($newRole === 'pasien') {
        // Hapus data dari tabel dokter jika ada
        $query = "DELETE FROM dokter WHERE user_id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$userId]);
        
        // Generate nomor rekam medis
        $query = "SELECT MAX(CAST(SUBSTRING(no_rm, 3) AS UNSIGNED)) as last_number FROM pasien";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $lastNumber = $result['last_number'] ?? 0;
        $newNumber = $lastNumber + 1;
        $no_rm = 'RM' . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
        
        // Cek apakah sudah ada di tabel pasien
        $query = "SELECT id FROM pasien WHERE user_id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$userId]);
        
        if (!$stmt->fetch()) {
            // Buat entry baru di tabel pasien
            $query = "INSERT INTO pasien (user_id, nama, no_rm, status) VALUES (?, ?, ?, 'active')";
            $stmt = $db->prepare($query);
            $stmt->execute([$userId, $user['username'], $no_rm]);
        }
    }
    
    // Commit transaksi
    $db->commit();
    
    echo json_encode(['status' => 'success', 'message' => 'Role berhasil diupdate']);
} catch (Exception $e) {
    // Rollback jika terjadi error
    $db->rollBack();
    error_log($e->getMessage());
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?> 