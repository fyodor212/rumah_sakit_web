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

    // Cek akses
    if (!isAdmin()) {
        sendJsonResponse('error', 'Anda tidak memiliki akses untuk melakukan ini.', 403);
    }

    // Cek method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendJsonResponse('error', 'Method tidak valid', 405);
    }

    // Validasi input
    if (!isset($_POST['review_id']) || !is_numeric($_POST['review_id'])) {
        sendJsonResponse('error', 'ID review tidak valid', 400);
    }

    $review_id = filter_var($_POST['review_id'], FILTER_VALIDATE_INT);
    if ($review_id === false || $review_id <= 0) {
        sendJsonResponse('error', 'ID review tidak valid', 400);
    }

    // Update status review
    $query = "UPDATE review SET status = 'approved' WHERE id = ?";
    $stmt = $db->prepare($query);
    
    if (!$stmt->execute([$review_id])) {
        throw new PDOException('Gagal mengupdate status review');
    }

    if ($stmt->rowCount() === 0) {
        sendJsonResponse('error', 'Review tidak ditemukan', 404);
    }

    sendJsonResponse('success', 'Review berhasil disetujui');

} catch (PDOException $e) {
    sendJsonResponse('error', 'Terjadi kesalahan pada database', 500);
} catch (Exception $e) {
    sendJsonResponse('error', $e->getMessage(), 400);
} 