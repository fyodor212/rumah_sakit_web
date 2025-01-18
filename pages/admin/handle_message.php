<?php
// Matikan semua output buffering
while (ob_get_level()) {
    ob_end_clean();
}

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Set header untuk memastikan response adalah JSON
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

// Register error handler
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    error_log("PHP Error [$errno]: $errstr in $errfile on line $errline");
    return true;
});

try {
    session_start();
    require_once __DIR__ . '/../../../../config/database.php';
    require_once __DIR__ . '/../../../../config/functions.php';

    // Debug
    error_log("Message handler request received. POST data: " . print_r($_POST, true));
    error_log("Session data: " . print_r($_SESSION, true));

    // Cek apakah user adalah admin
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        error_log("Unauthorized access attempt. Session: " . print_r($_SESSION, true));
        throw new Exception('Akses ditolak');
    }

    // Cek request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        error_log("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
        throw new Exception('Method tidak valid');
    }

    // Validasi input
    if (!isset($_POST['action'])) {
        throw new Exception('Action tidak valid');
    }

    // Handle berbagai action
    switch ($_POST['action']) {
        case 'mark_as_read':
            if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
                throw new Exception('ID pesan tidak valid');
            }

            $message_id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
            if ($message_id === false || $message_id <= 0) {
                throw new Exception('ID pesan tidak valid');
            }

            // Update status pesan
            $update = $db->prepare("
                UPDATE kontak 
                SET status = 'read'
                WHERE id = ? 
                AND status = 'unread'
            ");

            if (!$update->execute([$message_id])) {
                $error = $update->errorInfo();
                error_log("Error updating message: " . print_r($error, true));
                throw new PDOException('Gagal memperbarui status pesan: ' . $error[2]);
            }

            if ($update->rowCount() === 0) {
                throw new Exception('Pesan tidak ditemukan atau sudah dibaca');
            }

            echo json_encode([
                'status' => 'success',
                'message' => 'Pesan telah ditandai sebagai dibaca'
            ]);
            break;

        case 'delete':
            if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
                throw new Exception('ID pesan tidak valid');
            }

            $message_id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
            if ($message_id === false || $message_id <= 0) {
                throw new Exception('ID pesan tidak valid');
            }

            // Hapus pesan
            $delete = $db->prepare("DELETE FROM kontak WHERE id = ?");
            if (!$delete->execute([$message_id])) {
                $error = $delete->errorInfo();
                error_log("Error deleting message: " . print_r($error, true));
                throw new PDOException('Gagal menghapus pesan: ' . $error[2]);
            }

            if ($delete->rowCount() === 0) {
                throw new Exception('Pesan tidak ditemukan');
            }

            echo json_encode([
                'status' => 'success',
                'message' => 'Pesan berhasil dihapus'
            ]);
            break;

        default:
            throw new Exception('Action tidak dikenal');
    }

} catch (PDOException $e) {
    error_log("Database error in handle_message: " . $e->getMessage());
    
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);

} catch (Exception $e) {
    error_log("General error in handle_message: " . $e->getMessage());
    
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
} 