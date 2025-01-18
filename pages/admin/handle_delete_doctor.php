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
    // Set header
    header_remove(); // Hapus semua header yang mungkin sudah terkirim
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    http_response_code($code);
    
    // Kirim response
    die(json_encode([
        'status' => $status,
        'message' => $message
    ], JSON_UNESCAPED_UNICODE));
}

try {
    // Load database dan functions
    require_once __DIR__ . '/../../../../config/database.php';
    require_once __DIR__ . '/../../../../config/functions.php';

    // Cek session dan role (gunakan fungsi dari functions.php)
    if (!isAdmin()) {
        sendJsonResponse('error', 'Anda tidak memiliki akses untuk melakukan ini.', 403);
    }

    // Cek method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendJsonResponse('error', 'Method tidak valid', 405);
    }

    // Validasi input
    if (!isset($_POST['doctor_id']) || !is_numeric($_POST['doctor_id'])) {
        sendJsonResponse('error', 'ID dokter tidak valid', 400);
    }

    $doctor_id = filter_var($_POST['doctor_id'], FILTER_VALIDATE_INT);
    if ($doctor_id === false || $doctor_id <= 0) {
        sendJsonResponse('error', 'ID dokter tidak valid', 400);
    }

    // Mulai transaksi
    $db->beginTransaction();

    try {
        // Cek apakah dokter ada
        $check = $db->prepare("SELECT id, nama, user_id FROM dokter WHERE id = ?");
        if (!$check->execute([$doctor_id])) {
            throw new PDOException("Gagal mengecek data dokter");
        }
        
        $doctor = $check->fetch(PDO::FETCH_ASSOC);
        if (!$doctor) {
            throw new Exception('Data dokter tidak ditemukan');
        }

        // Cek booking aktif
        $booking = $db->prepare("SELECT COUNT(*) as total FROM booking WHERE dokter_id = ? AND status IN ('pending', 'confirmed')");
        if (!$booking->execute([$doctor_id])) {
            throw new PDOException("Gagal mengecek booking aktif");
        }
        
        $bookingCount = $booking->fetch(PDO::FETCH_ASSOC)['total'];
        if ($bookingCount > 0) {
            throw new Exception("Dokter masih memiliki {$bookingCount} booking aktif");
        }

        // Hapus dokter
        $delete = $db->prepare("DELETE FROM dokter WHERE id = ?");
        if (!$delete->execute([$doctor_id])) {
            throw new PDOException('Gagal menghapus data dokter');
        }

        // Hapus user account jika ada
        if ($doctor['user_id']) {
            $delete_user = $db->prepare("DELETE FROM users WHERE id = ?");
            if (!$delete_user->execute([$doctor['user_id']])) {
                throw new PDOException('Gagal menghapus akun user dokter');
            }
        }

        // Commit transaksi
        $db->commit();
        
        sendJsonResponse('success', 'Data dokter berhasil dihapus');

    } catch (Exception $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        throw $e;
    }

} catch (PDOException $e) {
    sendJsonResponse('error', 'Terjadi kesalahan pada database', 500);

} catch (Exception $e) {
    sendJsonResponse('error', $e->getMessage(), 400);
}
?> 