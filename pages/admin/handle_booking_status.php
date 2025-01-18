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
    header_remove(); // Hapus semua header yang mungkin sudah terkirim
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    http_response_code($code);
    
    die(json_encode([
        'status' => $status,
        'message' => $message
    ], JSON_UNESCAPED_UNICODE));
}

try {
    // Load database dan functions
    require_once __DIR__ . '/../../../../config/database.php';
    require_once __DIR__ . '/../../../../config/functions.php';

    // Cek akses admin
    if (!isAdmin()) {
        sendJsonResponse('error', 'Anda tidak memiliki akses untuk melakukan ini.', 403);
    }

    // Cek method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendJsonResponse('error', 'Method tidak valid', 405);
    }

    // Validasi input
    if (!isset($_POST['booking_id']) || !is_numeric($_POST['booking_id'])) {
        sendJsonResponse('error', 'ID booking tidak valid', 400);
    }

    if (!isset($_POST['status']) || !in_array($_POST['status'], ['pending', 'confirmed', 'completed', 'cancelled'])) {
        sendJsonResponse('error', 'Status tidak valid', 400);
    }

    $booking_id = filter_var($_POST['booking_id'], FILTER_VALIDATE_INT);
    $status = $_POST['status'];

    // Mulai transaksi
    $db->beginTransaction();

    try {
        // Update status booking
        $query = "UPDATE booking SET status = :status WHERE id = :booking_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':booking_id', $booking_id);
        
        if (!$stmt->execute()) {
            throw new PDOException('Gagal mengupdate status booking');
        }

        if ($stmt->rowCount() === 0) {
            throw new Exception('Booking tidak ditemukan');
        }

        // Jika status confirmed, cek apakah ada tagihan
        if ($status === 'confirmed') {
            // Cek apakah sudah ada tagihan
            $checkQuery = "SELECT id FROM tagihan WHERE booking_id = :booking_id";
            $stmt = $db->prepare($checkQuery);
            $stmt->bindParam(':booking_id', $booking_id);
            $stmt->execute();
            
            if (!$stmt->fetch()) {
                // Ambil data booking untuk membuat tagihan
                $bookingQuery = "SELECT b.*, l.harga as nominal 
                               FROM booking b 
                               JOIN layanan l ON b.layanan_id = l.id 
                               WHERE b.id = :booking_id";
                $stmt = $db->prepare($bookingQuery);
                $stmt->bindParam(':booking_id', $booking_id);
                $stmt->execute();
                $booking = $stmt->fetch(PDO::FETCH_ASSOC);

                // Buat tagihan baru
                $insertQuery = "INSERT INTO tagihan (booking_id, nominal, tanggal, status, created_at) 
                              VALUES (:booking_id, :nominal, :tanggal, 'Belum Lunas', NOW())";
                $stmt = $db->prepare($insertQuery);
                $stmt->bindParam(':booking_id', $booking_id);
                $stmt->bindParam(':nominal', $booking['nominal']);
                $stmt->bindParam(':tanggal', $booking['tanggal']);
                
                if (!$stmt->execute()) {
                    throw new PDOException('Gagal membuat tagihan');
                }
            }
        }

        // Commit transaksi
        $db->commit();

        // Kirim response sukses
        sendJsonResponse('success', 'Status booking berhasil diupdate');

    } catch (Exception $e) {
        // Rollback transaksi jika ada error
        $db->rollBack();
        throw $e;
    }

} catch (PDOException $e) {
    error_log("Database error in handle_booking_status: " . $e->getMessage());
    sendJsonResponse('error', 'Terjadi kesalahan pada database', 500);
} catch (Exception $e) {
    error_log("Error in handle_booking_status: " . $e->getMessage());
    sendJsonResponse('error', $e->getMessage(), 400);
} 