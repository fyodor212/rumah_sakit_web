<?php
// Pastikan session dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../Helpers/helpers.php';

// Cek akses
if (!isAdmin()) {
    header('Location: index.php');
    exit;
}

// Filter tanggal
$start_date = $_GET['start_date'] ?? date('Y-m-d');
$end_date = $_GET['end_date'] ?? date('Y-m-d');
$status = $_GET['status'] ?? '';

// Query dasar
$query = "SELECT p.*, 
          b.no_antrian, b.tanggal as tanggal_booking, b.id as booking_id,
          ps.nama as nama_pasien, ps.no_rm,
          d.nama as nama_dokter,
          l.nama as nama_layanan
          FROM pembayaran p
          JOIN booking b ON p.booking_id = b.id
          JOIN pasien ps ON b.pasien_id = ps.id
          JOIN dokter d ON b.dokter_id = d.id
          JOIN layanan l ON b.layanan_id = l.id
          WHERE DATE(p.created_at) BETWEEN ? AND ?";
$params = [$start_date, $end_date];

// Tambah filter status jika ada
if (!empty($status)) {
    $query .= " AND p.status = ?";
    $params[] = $status;
}

$query .= " ORDER BY p.created_at DESC";

// Ambil data pembayaran
try {
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error: " . $e->getMessage();
}
?>

<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0">Kelola Pembayaran</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="index.php?page=admin/dashboard">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Kelola Pembayaran</li>
                        </ol>
                    </nav>
                </div>
                <button onclick="history.back()" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left me-2"></i>Kembali
                </button>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <form class="row g-3">
                <input type="hidden" name="page" value="admin/manage_payments">
                <div class="col-md-3">
                    <label class="form-label text-sm">Tanggal Mulai</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                        <input type="date" class="form-control" name="start_date" 
                               value="<?= $start_date ?>" max="<?= $end_date ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-sm">Tanggal Akhir</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                        <input type="date" class="form-control" name="end_date" 
                               value="<?= $end_date ?>" min="<?= $start_date ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-sm">Status</label>
                    <select class="form-select" name="status">
                        <option value="">Semua Status</option>
                        <option value="pending" <?= $status == 'pending' ? 'selected' : '' ?>>Menunggu Pembayaran</option>
                        <option value="success" <?= $status == 'success' ? 'selected' : '' ?>>Berhasil</option>
                        <option value="failed" <?= $status == 'failed' ? 'selected' : '' ?>>Gagal</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-2"></i>Filter Data
                    </button>
                </div>
            </form>
        </div>
        <div class="card-body p-0">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success m-3">
                    <?= $_SESSION['success'] ?>
                    <?php unset($_SESSION['success']) ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger m-3">
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <?php if (empty($payments)): ?>
                <div class="card">
                    <div class="card-body">
                        <?php 
                        require_once __DIR__ . '/../../components/no_data.php';
                        showNoData(
                            'Tidak ada data pembayaran untuk periode ini',
                            'file-invoice-dollar',
                            'bg-light rounded'
                        );
                        ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="border-0">No. Pembayaran</th>
                                <th class="border-0">Pasien</th>
                                <th class="border-0">Layanan</th>
                                <th class="border-0">Dokter</th>
                                <th class="border-0">Tanggal Booking</th>
                                <th class="border-0">Jumlah</th>
                                <th class="border-0">Status</th>
                                <th class="border-0">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payments as $payment): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-bold">INV-<?= str_pad($payment['booking_id'], 4, '0', STR_PAD_LEFT) ?></span>
                                            <small class="text-muted">RM: <?= $payment['no_rm'] ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span><?= htmlspecialchars($payment['nama_pasien']) ?></span>
                                            <small class="text-muted">No. Antrian: <?= $payment['no_antrian'] ?></small>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($payment['nama_layanan']) ?></td>
                                    <td><?= htmlspecialchars($payment['nama_dokter']) ?></td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span><?= formatDateIndo($payment['tanggal_booking']) ?></span>
                                            <small class="text-muted">Dibayar: <?= formatDateIndo($payment['created_at']) ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fw-bold"><?= formatCurrency($payment['jumlah']) ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= getPaymentStatusColor($payment['status']) ?> rounded-pill">
                                            <?= ucfirst($payment['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <!-- Tombol Lihat Detail -->
                                            <a href="index.php?page=admin/view_payment&id=<?= $payment['id'] ?>" 
                                               class="btn btn-info btn-sm" title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            <!-- Tombol Cetak -->
                                            <a href="index.php?page=admin/print_payment&id=<?= $payment['id'] ?>" 
                                               class="btn btn-secondary btn-sm" title="Cetak Kuitansi"
                                               target="_blank">
                                                <i class="fas fa-print"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.table th {
    font-weight: 600;
    white-space: nowrap;
}

.badge {
    font-weight: 500;
    padding: 0.5rem 0.75rem;
}

.form-select-sm {
    min-width: 150px;
}

.text-sm {
    font-size: 0.875rem;
}

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

.input-group-text {
    background-color: #fff;
}

.table td {
    padding: 1rem;
    vertical-align: middle;
}

.table-hover tbody tr:hover {
    background-color: #f8f9fa;
}

/* Style untuk tombol aksi */
.btn-sm {
    padding: 0.25rem 0.5rem;
    line-height: 1.5;
}

.btn-sm i {
    font-size: 0.875rem;
}

.btn-info {
    color: #fff;
    background-color: #0dcaf0;
    border-color: #0dcaf0;
}

.btn-info:hover {
    color: #fff;
    background-color: #31d2f2;
    border-color: #25cff2;
}

.btn-secondary {
    color: #fff;
    background-color: #6c757d;
    border-color: #6c757d;
}

.btn-secondary:hover {
    color: #fff;
    background-color: #5c636a;
    border-color: #565e64;
}

/* Tooltip style */
[title] {
    position: relative;
    cursor: pointer;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .form-select-sm {
        min-width: auto;
    }
    
    .d-flex.gap-2 {
        flex-direction: row;
        flex-wrap: wrap;
        gap: 0.5rem !important;
    }
    
    .btn-sm {
        padding: 0.375rem 0.75rem;
    }
}

.bg-light {
    background-color: #f8f9fa !important;
}

.rounded {
    border-radius: 0.5rem !important;
}

.text-muted {
    color: #6c757d !important;
}

.fa-file-invoice-dollar {
    opacity: 0.7;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const alertStatus = urlParams.get('alert_status');
    
    if (alertStatus === 'success') {
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: 'Data pembayaran berhasil diperbarui!',
            timer: 3000,
            showConfirmButton: false,
            toast: true,
            position: 'top-end'
        });
    } else if (alertStatus === 'error') {
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: 'Terjadi kesalahan saat memperbarui data pembayaran.',
            timer: 3000,
            showConfirmButton: false,
            toast: true,
            position: 'top-end'
        });
    }
});
</script>