<?php
require_once 'config/database.php';
require_once 'config/functions.php';

// Cek akses admin
requireAdmin();

// Ambil ID dari query string
$id = $_GET['id'] ?? null;

if (!$id) {
    $_SESSION['message'] = "ID janji temu tidak valid.";
    $_SESSION['message_type'] = 'danger';
    header('Location: index.php?page=admin/bookings');
    exit;
}

try {
    // Query untuk mengambil detail booking
    $query = "SELECT b.*, 
              p.nama as nama_pasien,
              d.nama as nama_dokter,
              l.nama as nama_layanan,
              l.harga as harga_layanan
              FROM booking b
              JOIN pasien p ON b.pasien_id = p.id
              JOIN dokter d ON b.dokter_id = d.id
              JOIN layanan l ON b.layanan_id = l.id
              WHERE b.id = ?";
    
    $stmt = $db->prepare($query);
    $stmt->execute([$id]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$booking) {
        $_SESSION['message'] = "Janji temu tidak ditemukan.";
        $_SESSION['message_type'] = 'danger';
        header('Location: index.php?page=admin/bookings');
        exit;
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    $_SESSION['message'] = "Terjadi kesalahan saat mengambil data.";
    $_SESSION['message_type'] = 'danger';
}
?>

<div class="container-fluid p-4">
    <h2 class="mb-4">Detail Janji Temu</h2>
    
    <div class="card">
        <div class="card-body">
            <h5>Informasi Pasien</h5>
            <p><strong>Nama:</strong> <?= htmlspecialchars($booking['nama_pasien']) ?></p>
            <p><strong>Tanggal:</strong> <?= date('d/m/Y', strtotime($booking['tanggal'])) ?></p>
            <p><strong>Jam:</strong> <?= date('H:i', strtotime($booking['jam'])) ?></p>
            
            <h5 class="mt-4">Informasi Dokter</h5>
            <p><strong>Dokter:</strong> <?= htmlspecialchars($booking['nama_dokter']) ?></p>
            
            <h5 class="mt-4">Informasi Layanan</h5>
            <p><strong>Layanan:</strong> <?= htmlspecialchars($booking['nama_layanan']) ?></p>
            <p><strong>Harga:</strong> Rp <?= number_format($booking['harga_layanan'] ?? 0, 0, ',', '.') ?></p>
            
            <h5 class="mt-4">Status</h5>
            <span class="badge bg-<?= getStatusBadgeClass($booking['status']) ?>">
                <?= ucfirst($booking['status']) ?>
            </span>
        </div>
    </div>
    
    <div class="mt-4">
        <a href="index.php?page=admin/bookings" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
</div>

<style>
.card {
    border: none;
    border-radius: 10px;
    box-shadow: 0 0 15px rgba(0,0,0,.05);
}
</style> 