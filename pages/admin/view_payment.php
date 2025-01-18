<?php
// Pastikan session dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../Helpers/helpers.php';
require_once __DIR__ . '/../../../../config/database.php';

// Cek akses
if (!isAdmin()) {
    header('Location: index.php');
    exit;
}

// Ambil ID pembayaran
$payment_id = $_GET['id'] ?? null;

if (!$payment_id) {
    $_SESSION['error'] = 'ID Pembayaran tidak valid';
    header('Location: index.php?page=admin/manage_payments');
    exit;
}

// Query untuk mengambil detail pembayaran
$query = "SELECT p.*, 
          b.no_antrian, b.tanggal as tanggal_booking, b.jam, b.id as booking_id,
          ps.nama as nama_pasien, ps.no_rm, ps.no_hp, ps.alamat,
          d.nama as nama_dokter,
          l.nama as nama_layanan, l.harga as harga_layanan
          FROM pembayaran p
          JOIN booking b ON p.booking_id = b.id
          JOIN pasien ps ON b.pasien_id = ps.id
          JOIN dokter d ON b.dokter_id = d.id
          JOIN layanan l ON b.layanan_id = l.id
          WHERE p.id = ?";

try {
    $stmt = $db->prepare($query);
    $stmt->execute([$payment_id]);
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$payment) {
        $_SESSION['error'] = 'Data pembayaran tidak ditemukan';
        header('Location: index.php?page=admin/manage_payments');
        exit;
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
    header('Location: index.php?page=admin/manage_payments');
    exit;
}
?>

<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0">Detail Pembayaran</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="index.php?page=admin/dashboard">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="index.php?page=admin/manage_payments">Kelola Pembayaran</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Detail Pembayaran</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex gap-2">
                    <button onclick="history.back()" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-2"></i>Kembali
                    </button>
                    <a href="index.php?page=admin/print_payment&id=<?= $payment['id'] ?>" 
                       class="btn btn-primary" target="_blank">
                        <i class="fas fa-print me-2"></i>Cetak Kuitansi
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Informasi Pembayaran -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-file-invoice me-2 text-primary"></i>
                        Informasi Pembayaran
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="text-muted mb-1">No. Pembayaran</label>
                            <p class="fw-bold mb-0">INV-<?= str_pad($payment['booking_id'], 4, '0', STR_PAD_LEFT) ?></p>
                        </div>
                        <div class="col-6">
                            <label class="text-muted mb-1">Status</label>
                            <br>
                            <span class="badge bg-<?= getPaymentStatusColor($payment['status']) ?> rounded-pill">
                                <?= ucfirst($payment['status']) ?>
                            </span>
                        </div>
                        <div class="col-6">
                            <label class="text-muted mb-1">Tanggal Pembayaran</label>
                            <p class="mb-0"><?= formatDateIndo($payment['created_at']) ?></p>
                        </div>
                        <div class="col-6">
                            <label class="text-muted mb-1">Jumlah</label>
                            <p class="fw-bold text-primary mb-0"><?= formatCurrency($payment['jumlah']) ?></p>
                        </div>
                        <div class="col-12">
                            <label class="text-muted mb-1">Metode Pembayaran</label>
                            <p class="mb-0"><?= ucfirst($payment['metode_pembayaran'] ?? 'Tunai') ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Informasi Pasien -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user me-2 text-primary"></i>
                        Informasi Pasien
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="text-muted mb-1">Nama Pasien</label>
                            <p class="fw-bold mb-0"><?= htmlspecialchars($payment['nama_pasien']) ?></p>
                        </div>
                        <div class="col-6">
                            <label class="text-muted mb-1">No. Rekam Medis</label>
                            <p class="mb-0"><?= $payment['no_rm'] ?></p>
                        </div>
                        <div class="col-6">
                            <label class="text-muted mb-1">No. HP</label>
                            <p class="mb-0"><?= $payment['no_hp'] ?></p>
                        </div>
                        <div class="col-12">
                            <label class="text-muted mb-1">Alamat</label>
                            <p class="mb-0"><?= htmlspecialchars($payment['alamat']) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Informasi Booking -->
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-calendar-check me-2 text-primary"></i>
                        Detail Booking
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="text-muted mb-1">No. Antrian</label>
                            <p class="fw-bold mb-0"><?= $payment['no_antrian'] ?></p>
                        </div>
                        <div class="col-md-3">
                            <label class="text-muted mb-1">Tanggal Booking</label>
                            <p class="mb-0"><?= formatDateIndo($payment['tanggal_booking']) ?></p>
                        </div>
                        <div class="col-md-3">
                            <label class="text-muted mb-1">Jam</label>
                            <p class="mb-0"><?= formatTime($payment['jam']) ?></p>
                        </div>
                        <div class="col-md-3">
                            <label class="text-muted mb-1">Dokter</label>
                            <p class="mb-0"><?= htmlspecialchars($payment['nama_dokter']) ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted mb-1">Layanan</label>
                            <p class="mb-0"><?= htmlspecialchars($payment['nama_layanan']) ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted mb-1">Harga Layanan</label>
                            <p class="mb-0"><?= formatCurrency($payment['harga_layanan']) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.breadcrumb {
    font-size: 0.875rem;
}

.breadcrumb a {
    color: #6c757d;
    text-decoration: none;
}

.breadcrumb a:hover {
    color: #0d6efd;
}

.card {
    border: none;
    margin-bottom: 1.5rem;
}

.card-header {
    border-bottom: 1px solid rgba(0,0,0,.125);
}

.card-title {
    color: #344767;
    font-size: 1rem;
}

.text-muted {
    font-size: 0.875rem;
}

.badge {
    font-weight: 500;
    padding: 0.5rem 0.75rem;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .d-flex.gap-2 {
        flex-direction: column;
        gap: 0.5rem !important;
    }
    
    .btn {
        width: 100%;
    }
}
</style> 