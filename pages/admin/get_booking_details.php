<?php
// Matikan semua output buffering dan bersihkan
while (ob_get_level()) {
    ob_end_clean();
}

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Set header untuk memastikan response adalah JSON
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// Pastikan tidak ada whitespace atau karakter lain sebelum JSON
try {
    require_once __DIR__ . '/../../../../config/database.php';
    require_once __DIR__ . '/../../../../config/functions.php';

    // Validasi ID booking
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        throw new Exception('ID booking tidak valid');
    }

    $booking_id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    if ($booking_id === false || $booking_id <= 0) {
        throw new Exception('ID booking tidak valid');
    }

    // Cek apakah user adalah admin
    if (!isAdmin()) {
        throw new Exception('Akses ditolak');
    }

    // Query untuk mengambil detail booking
    $query = "SELECT b.*, 
                     p.nama as nama_pasien,
                     p.no_hp,
                     p.no_rm,
                     p.alamat as alamat_pasien,
                     d.nama as nama_dokter,
                     d.spesialisasi,
                     d.id as dokter_id
              FROM booking b 
              LEFT JOIN pasien p ON b.pasien_id = p.id
              LEFT JOIN dokter d ON b.dokter_id = d.id 
              WHERE b.id = :booking_id";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':booking_id', $booking_id, PDO::PARAM_INT);
    $stmt->execute();
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$booking) {
        throw new Exception('Data booking tidak ditemukan');
    }

    // Format status
    $statusText = [
        'pending' => 'Menunggu',
        'confirmed' => 'Dikonfirmasi',
        'cancelled' => 'Dibatalkan',
        'completed' => 'Selesai'
    ];

    $statusClass = [
        'pending' => 'warning',
        'confirmed' => 'success',
        'cancelled' => 'danger',
        'completed' => 'info'
    ];

    // Generate HTML untuk detail booking
    $html = '<div class="modal-header">
        <h5 class="modal-title">Detail Booking #' . htmlspecialchars($booking['id']) . '</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body">
        <div class="table-responsive">
            <table class="table table-borderless">
                <tr>
                    <td class="fw-bold" style="width: 150px">No. Rekam Medis</td>
                    <td>: ' . htmlspecialchars($booking['no_rm'] ?? '-') . '</td>
                </tr>
                <tr>
                    <td class="fw-bold">Nama Pasien</td>
                    <td>: ' . htmlspecialchars($booking['nama_pasien'] ?? '-') . '</td>
                </tr>
                <tr>
                    <td class="fw-bold">No. HP</td>
                    <td>: ' . htmlspecialchars($booking['no_hp'] ?? '-') . '</td>
                </tr>
                <tr>
                    <td class="fw-bold">Alamat</td>
                    <td>: ' . htmlspecialchars($booking['alamat_pasien'] ?? '-') . '</td>
                </tr>
                <tr>
                    <td class="fw-bold">Dokter</td>
                    <td>: ' . htmlspecialchars($booking['nama_dokter'] ?? '-') . '</td>
                </tr>
                <tr>
                    <td class="fw-bold">Spesialisasi</td>
                    <td>: ' . htmlspecialchars($booking['spesialisasi'] ?? '-') . '</td>
                </tr>
                <tr>
                    <td class="fw-bold">Tanggal</td>
                    <td>: ' . (isset($booking['tanggal']) ? date('d/m/Y', strtotime($booking['tanggal'])) : '-') . '</td>
                </tr>
                <tr>
                    <td class="fw-bold">Jam</td>
                    <td>: ' . htmlspecialchars($booking['jam'] ?? '-') . '</td>
                </tr>
                <tr>
                    <td class="fw-bold">Keluhan</td>
                    <td>: ' . htmlspecialchars($booking['keluhan'] ?? '-') . '</td>
                </tr>
                <tr>
                    <td class="fw-bold">Status</td>
                    <td>: <span class="badge bg-' . ($statusClass[$booking['status']] ?? 'secondary') . '">' . 
                            ($statusText[$booking['status']] ?? 'Tidak Diketahui') . '</span></td>
                </tr>
                <tr>
                    <td class="fw-bold">Dibuat pada</td>
                    <td>: ' . (isset($booking['created_at']) ? date('d/m/Y H:i', strtotime($booking['created_at'])) : '-') . '</td>
                </tr>
            </table>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
        <a href="index.php?page=admin/view_booking&id=' . $booking['id'] . '" class="btn btn-primary">
            <i class="fas fa-eye"></i> Lihat Detail Lengkap
        </a>
    </div>';

    // Kirim response JSON
    die(json_encode([
        'status' => 'success',
        'html' => $html
    ]));

} catch (Exception $e) {
    die(json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]));
} 