<?php
// Pastikan session dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../Helpers/helpers.php';

// Fungsi format waktu jika belum ada
if (!function_exists('formatTime')) {
    function formatTime($time) {
        return date('H:i', strtotime($time));
    }
}

// Debug
error_log("Loading manage_booking.php");
error_log("Session data: " . print_r($_SESSION, true));
error_log("GET data: " . print_r($_GET, true));

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
$query = "SELECT b.*, 
          p.nama as nama_pasien, p.no_rm,
          d.nama as nama_dokter, d.spesialisasi,
          l.nama as nama_layanan, l.harga
          FROM booking b
          JOIN pasien p ON b.pasien_id = p.id
          JOIN dokter d ON b.dokter_id = d.id
          JOIN layanan l ON b.layanan_id = l.id
          WHERE b.tanggal BETWEEN ? AND ?";
$params = [$start_date, $end_date];

// Tambah filter status jika ada
if (!empty($status)) {
    $query .= " AND b.status = ?";
    $params[] = $status;
}

$query .= " ORDER BY b.tanggal ASC, b.jam ASC";

// Ambil data booking
try {
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error: " . $e->getMessage();
}

error_log("Request method: " . $_SERVER['REQUEST_METHOD']);
error_log("POST data: " . print_r($_POST, true));
error_log("GET data: " . print_r($_GET, true));
?>

<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0">Kelola Booking</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="index.php?page=admin/dashboard">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Kelola Booking</li>
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
                <input type="hidden" name="page" value="admin/manage_booking">
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
                        <option value="pending" <?= $status == 'pending' ? 'selected' : '' ?>>Menunggu Konfirmasi</option>
                        <option value="confirmed" <?= $status == 'confirmed' ? 'selected' : '' ?>>Terkonfirmasi</option>
                        <option value="completed" <?= $status == 'completed' ? 'selected' : '' ?>>Selesai</option>
                        <option value="cancelled" <?= $status == 'cancelled' ? 'selected' : '' ?>>Dibatalkan</option>
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

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-0">No. Antrian</th>
                            <th class="border-0">Pasien</th>
                            <th class="border-0">Dokter</th>
                            <th class="border-0">Layanan</th>
                            <th class="border-0">Jadwal</th>
                            <th class="border-0">Total Biaya</th>
                            <th class="border-0">Status</th>
                            <th class="border-0">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($bookings)): ?>
                            <?php foreach ($bookings as $booking): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-bold"><?= $booking['no_antrian'] ?></span>
                                            <small class="text-muted">RM: <?= $booking['no_rm'] ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span><?= htmlspecialchars($booking['nama_pasien']) ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span><?= htmlspecialchars($booking['nama_dokter']) ?></span>
                                            <small class="text-muted"><?= $booking['spesialisasi'] ?></small>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($booking['nama_layanan']) ?></td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span><?= formatDateIndo($booking['tanggal']) ?></span>
                                            <small class="text-muted"><?= formatTime($booking['jam']) ?> WIB</small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fw-bold"><?= formatCurrency($booking['total_biaya']) ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= getStatusColor($booking['status']) ?> rounded-pill">
                                            <?= ucfirst($booking['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <!-- Tombol Lihat Detail -->
                                            <a href="index.php?page=admin/view_booking&id=<?= $booking['id'] ?>" 
                                               class="btn btn-info btn-sm" title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            <!-- Tombol Cetak -->
                                            <a href="index.php?page=admin/print_booking&id=<?= $booking['id'] ?>" 
                                               class="btn btn-secondary btn-sm" title="Cetak Booking"
                                               target="_blank">
                                                <i class="fas fa-print"></i>
                                            </a>

                                            <!-- Form Update Status -->
                                            <form method="POST" class="d-inline" onsubmit="return confirmUpdate(event, this)">
                                                <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                                <input type="hidden" name="start_date" value="<?= htmlspecialchars($start_date) ?>">
                                                <input type="hidden" name="end_date" value="<?= htmlspecialchars($end_date) ?>">
                                                <input type="hidden" name="filter_status" value="<?= htmlspecialchars($status) ?>">
                                                
                                                <div class="d-flex gap-2">
                                                    <select name="status" class="form-select form-select-sm" 
                                                            <?= $booking['status'] == 'cancelled' ? 'disabled' : '' ?>>
                                                        <option value="pending" <?= $booking['status'] == 'pending' ? 'selected' : '' ?>>
                                                            Menunggu Konfirmasi
                                                        </option>
                                                        <option value="confirmed" <?= $booking['status'] == 'confirmed' ? 'selected' : '' ?>>
                                                            Terkonfirmasi
                                                        </option>
                                                        <option value="completed" <?= $booking['status'] == 'completed' ? 'selected' : '' ?>>
                                                            Selesai
                                                        </option>
                                                        <option value="cancelled" <?= $booking['status'] == 'cancelled' ? 'selected' : '' ?>>
                                                            Dibatalkan
                                                        </option>
                                                    </select>
                                                    <button type="submit" class="btn btn-primary btn-sm" 
                                                            <?= $booking['status'] == 'cancelled' ? 'disabled' : '' ?>
                                                            title="Update Status">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <img src="public/images/no-data.png" alt="No Data" style="width: 200px; opacity: 0.5;">
                                    <p class="text-muted mt-3">Tidak ada booking untuk periode ini</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
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
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Debug
    console.log('DOM loaded');
    console.log('URL parameters:', window.location.search);
    
    const urlParams = new URLSearchParams(window.location.search);
    const alertStatus = urlParams.get('alert_status');
    console.log('Alert status parameter:', alertStatus);
    
    if (alertStatus === 'success') {
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: 'Status booking berhasil diperbarui!',
            timer: 3000,
            showConfirmButton: false,
            toast: true,
            position: 'top-end'
        });
    } else if (alertStatus === 'error') {
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: 'Terjadi kesalahan saat memperbarui status booking.',
            timer: 3000,
            showConfirmButton: false,
            toast: true,
            position: 'top-end'
        });
    }
});

function confirmUpdate(event, form) {
    event.preventDefault();
    
    const statusSelect = form.querySelector('select[name="status"]');
    const selectedStatus = statusSelect.options[statusSelect.selectedIndex].text;
    
    Swal.fire({
        title: 'Konfirmasi Update',
        text: `Apakah Anda yakin ingin mengubah status booking menjadi "${selectedStatus}"?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Update',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            form.action = 'index.php?page=admin/handle_update_booking';
            form.submit();
        }
    });
    
    return false;
}
</script> 