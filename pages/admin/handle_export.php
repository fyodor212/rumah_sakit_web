<?php
// Cek session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek role admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

// Validasi method request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['message'] = 'Metode request tidak valid';
    $_SESSION['message_type'] = 'danger';
    header('Location: index.php?page=admin/export');
    exit;
}

require_once __DIR__ . '/../../../../config/database.php';
require_once __DIR__ . '/../../../../config/functions.php';

// Ambil parameter
$type = $_POST['type'] ?? '';
$start_date = $_POST['start_date'] ?? '';
$end_date = $_POST['end_date'] ?? '';
$status = $_POST['status'] ?? 'all';

// Validasi parameter
if (empty($type)) {
    $_SESSION['message'] = 'Tipe data tidak valid';
    $_SESSION['message_type'] = 'danger';
    header('Location: index.php?page=admin/export');
    exit;
}

try {
    $data = [];
    $filename = '';

    switch ($type) {
        case 'payments':
            if (empty($start_date) || empty($end_date)) {
                throw new Exception('Tanggal harus diisi');
            }

            $query = "
                SELECT 
                    p.id,
                    p.booking_id,
                    p.jumlah,
                    p.status,
                    p.created_at,
                    b.no_antrian,
                    b.tanggal_booking,
                    ps.nama as nama_pasien,
                    ps.no_rm,
                    d.nama as nama_dokter,
                    l.nama as nama_layanan
                FROM payments p
                JOIN bookings b ON p.booking_id = b.id
                JOIN patients ps ON b.patient_id = ps.id
                JOIN doctors d ON b.doctor_id = d.id
                JOIN layanan l ON b.layanan_id = l.id
                WHERE DATE(p.created_at) BETWEEN ? AND ?
                ORDER BY p.created_at DESC
            ";
            
            $stmt = $db->prepare($query);
            $stmt->execute([$start_date, $end_date]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $filename = "Data_Pembayaran_{$start_date}_sd_{$end_date}";
            break;

        case 'bookings':
            if (empty($start_date) || empty($end_date)) {
                throw new Exception('Tanggal harus diisi');
            }

            $query = "
                SELECT 
                    b.id,
                    b.no_antrian,
                    b.tanggal_booking,
                    b.jam_booking,
                    b.status,
                    b.created_at,
                    ps.nama as nama_pasien,
                    ps.no_rm,
                    d.nama as nama_dokter,
                    l.nama as nama_layanan
                FROM bookings b
                JOIN patients ps ON b.patient_id = ps.id
                JOIN doctors d ON b.doctor_id = d.id
                JOIN layanan l ON b.layanan_id = l.id
                WHERE DATE(b.tanggal_booking) BETWEEN ? AND ?
                ORDER BY b.tanggal_booking DESC, b.jam_booking ASC
            ";
            
            $stmt = $db->prepare($query);
            $stmt->execute([$start_date, $end_date]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $filename = "Data_Booking_{$start_date}_sd_{$end_date}";
            break;

        case 'patients':
            $query = "
                SELECT 
                    p.*,
                    u.email,
                    u.username,
                    u.created_at as tanggal_daftar
                FROM patients p
                JOIN users u ON p.user_id = u.id
            ";
            
            if ($status !== 'all') {
                $query .= " WHERE p.status = ?";
                $stmt = $db->prepare($query);
                $stmt->execute([$status]);
            } else {
                $stmt = $db->prepare($query);
                $stmt->execute();
            }
            
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $filename = "Data_Pasien_" . date('Y-m-d');
            break;

        default:
            throw new Exception('Tipe data tidak valid');
    }

    // Jika data kosong
    if (empty($data)) {
        throw new Exception('Tidak ada data untuk di-export');
    }

    // Set header untuk download Excel
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="' . $filename . '.xls"');

    // Output data
    $output = '';
    
    // Header
    $output .= implode("\t", array_keys($data[0])) . "\n";
    
    // Data
    foreach ($data as $row) {
        $output .= implode("\t", array_map(function($value) {
            // Handle nilai null
            if ($value === null) return '';
            // Hapus tab dan newline
            return str_replace(["\t", "\n", "\r"], ' ', $value);
        }, $row)) . "\n";
    }

    echo $output;
    exit;

} catch (Exception $e) {
    $_SESSION['message'] = $e->getMessage();
    $_SESSION['message_type'] = 'danger';
    header('Location: index.php?page=admin/export');
    exit;
} 