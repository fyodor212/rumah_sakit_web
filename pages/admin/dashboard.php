<?php
require_once 'config/database.php';
require_once 'config/functions.php';

// Cek akses admin
requireAdmin();

try {
    // Statistik total booking
    $bookingQuery = "SELECT 
        COUNT(*) as total_booking,
        COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_booking,
        COUNT(CASE WHEN status = 'confirmed' THEN 1 END) as confirmed_booking,
        COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_booking
        FROM booking";
    $bookingStats = $db->query($bookingQuery)->fetch(PDO::FETCH_ASSOC);

    // Total dokter aktif
    $doctorQuery = "SELECT COUNT(*) as total_dokter FROM dokter WHERE status = 'aktif'";
    $doctorStats = $db->query($doctorQuery)->fetch(PDO::FETCH_ASSOC);

    // Total pasien
    $patientQuery = "SELECT COUNT(*) as total_pasien FROM pasien";
    $patientStats = $db->query($patientQuery)->fetch(PDO::FETCH_ASSOC);

    // Statistik pembayaran
    $paymentStats = $db->query("
        SELECT 
            COUNT(*) as total_payments,
            SUM(CASE WHEN status = 'success' THEN jumlah ELSE 0 END) as total_paid,
            COUNT(CASE WHEN status = 'success' THEN 1 END) as count_paid,
            COUNT(CASE WHEN status = 'pending' THEN 1 END) as count_pending,
            COUNT(CASE WHEN status = 'failed' THEN 1 END) as count_failed
        FROM pembayaran
    ")->fetch(PDO::FETCH_ASSOC);

    // Pembayaran per bulan untuk grafik
    $monthlyPayments = $db->query("
        SELECT 
            DATE_FORMAT(tanggal_pembayaran, '%Y-%m') as bulan,
            SUM(jumlah) as total
        FROM pembayaran 
        WHERE status = 'success'
        AND tanggal_pembayaran >= DATE_SUB(CURRENT_DATE, INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(tanggal_pembayaran, '%Y-%m')
        ORDER BY bulan ASC
    ")->fetchAll(PDO::FETCH_ASSOC);

    // Debug
    error_log("Payment Stats: " . print_r($paymentStats, true));
    error_log("Monthly Payments: " . print_r($monthlyPayments, true));

    // Booking terbaru
    $recentBookings = $db->query("
        SELECT b.*, p.nama as nama_pasien, d.nama as nama_dokter 
        FROM booking b
        JOIN pasien p ON b.pasien_id = p.id
        JOIN dokter d ON b.dokter_id = d.id
        ORDER BY b.created_at DESC LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log($e->getMessage());
}
?>

<div class="container-fluid p-4">
    <!-- Tambahkan Chart.js di sini -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Dashboard Admin</h2>
        <div class="text-end">
            <div class="fs-6 text-muted"><?= date('l, d F Y') ?></div>
            <div class="fs-6">Selamat datang, <?= htmlspecialchars($_SESSION['nama'] ?? 'Admin') ?></div>
        </div>
    </div>

    <!-- Statistik Cards -->
    <div class="row g-4 mb-4">
        <!-- Total Booking -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-primary bg-opacity-10 p-3 rounded">
                                <i class="fas fa-calendar-check text-primary fa-2x"></i>
                            </div>
                        </div>
                        <div>
                            <h6 class="card-title mb-1">Total Booking</h6>
                            <h2 class="mb-0 fw-bold"><?= number_format($bookingStats['total_booking']) ?></h2>
                            <a href="index.php?page=admin/manage_bookings" class="btn btn-sm btn-primary mt-2">
                                <i class="fas fa-cog me-1"></i>Kelola
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Booking Pending -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-warning bg-opacity-10 p-3 rounded">
                                <i class="fas fa-clock text-warning fa-2x"></i>
                            </div>
                        </div>
                        <div>
                            <h6 class="card-title mb-1">Booking Pending</h6>
                            <h2 class="mb-0 fw-bold"><?= number_format($bookingStats['pending_booking']) ?></h2>
                            <a href="index.php?page=admin/manage_bookings" class="btn btn-sm btn-warning mt-2">
                                <i class="fas fa-cog me-1"></i>Kelola
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Dokter -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-success bg-opacity-10 p-3 rounded">
                                <i class="fas fa-user-md text-success fa-2x"></i>
                            </div>
                        </div>
                        <div>
                            <h6 class="card-title mb-1">Dokter Aktif</h6>
                            <h2 class="mb-0 fw-bold"><?= number_format($doctorStats['total_dokter']) ?></h2>
                            <a href="index.php?page=admin/manage_doctors" class="btn btn-sm btn-success mt-2">
                                <i class="fas fa-cog me-1"></i>Kelola
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Pasien -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-info bg-opacity-10 p-3 rounded">
                                <i class="fas fa-users text-info fa-2x"></i>
                            </div>
                        </div>
                        <div>
                            <h6 class="card-title mb-1">Total Pasien</h6>
                            <h2 class="mb-0 fw-bold"><?= number_format($patientStats['total_pasien']) ?></h2>
                            <a href="index.php?page=admin/manage_patients" class="btn btn-sm btn-info mt-2">
                                <i class="fas fa-cog me-1"></i>Kelola
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Users -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-danger bg-opacity-10 p-3 rounded">
                                <i class="fas fa-user-shield text-danger fa-2x"></i>
                            </div>
                        </div>
                        <div>
                            <h6 class="card-title mb-1">Kelola Users</h6>
                            <h2 class="mb-0 fw-bold">
                                <?php
                                $userQuery = "SELECT COUNT(*) as total_users FROM users";
                                $userStats = $db->query($userQuery)->fetch(PDO::FETCH_ASSOC);
                                echo number_format($userStats['total_users']);
                                ?>
                            </h2>
                            <a href="index.php?page=admin/manage_users" class="btn btn-sm btn-danger mt-2">
                                <i class="fas fa-cog me-1"></i>Kelola
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kelola Pembayaran -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-success bg-opacity-10 p-3 rounded">
                                <i class="fas fa-money-bill-wave text-success fa-2x"></i>
                            </div>
                        </div>
                        <div>
                            <h6 class="card-title mb-1">Total Pembayaran</h6>
                            <h2 class="mb-0 fw-bold">Rp <?= number_format($paymentStats['total_paid'] ?? 0, 0, ',', '.') ?></h2>
                            <div class="text-muted small mt-1">
                                <span class="text-success">
                                    <i class="fas fa-check-circle me-1"></i><?= number_format($paymentStats['count_paid'] ?? 0) ?> Sukses
                                </span>
                                <span class="ms-2 text-warning">
                                    <i class="fas fa-clock me-1"></i><?= number_format($paymentStats['count_pending'] ?? 0) ?> Pending
                                </span>
                                <?php if (($paymentStats['count_failed'] ?? 0) > 0): ?>
                                <span class="ms-2 text-danger">
                                    <i class="fas fa-times-circle me-1"></i><?= number_format($paymentStats['count_failed']) ?> Gagal
                                </span>
                                <?php endif; ?>
                            </div>
                            <a href="index.php?page=admin/manage_payments" class="btn btn-sm btn-success mt-2">
                                <i class="fas fa-cog me-1"></i>Kelola
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pesan Kontak -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-info bg-opacity-10 p-3 rounded">
                                <i class="fas fa-envelope text-info fa-2x"></i>
                            </div>
                        </div>
                        <div>
                            <h6 class="card-title mb-1">Pesan Kontak</h6>
                            <h2 class="mb-0 fw-bold">
                                <?php
                                $contactQuery = "SELECT COUNT(*) as total_messages FROM kontak";
                                $contactStats = $db->query($contactQuery)->fetch(PDO::FETCH_ASSOC);
                                echo number_format($contactStats['total_messages']);
                                ?>
                            </h2>
                            <a href="index.php?page=admin/manage_messages" class="btn btn-sm btn-info mt-2">
                                <i class="fas fa-envelope me-1"></i>Lihat Pesan
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Review Pasien -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-warning bg-opacity-10 p-3 rounded">
                                <i class="fas fa-star text-warning fa-2x"></i>
                            </div>
                        </div>
                        <div>
                            <h6 class="card-title mb-1">Review Pasien</h6>
                            <h2 class="mb-0 fw-bold">
                                <?php
                                $reviewQuery = "SELECT COUNT(*) as total_reviews FROM review";
                                $reviewStats = $db->query($reviewQuery)->fetch(PDO::FETCH_ASSOC);
                                echo number_format($reviewStats['total_reviews']);
                                ?>
                            </h2>
                            <a href="index.php?page=admin/manage_reviews" class="btn btn-sm btn-warning mt-2">
                                <i class="fas fa-star me-1"></i>Lihat Review
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Grafik dan Tabel -->
    <div class="row g-4 mb-4">
        <!-- Grafik Status Booking -->
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0">Statistik Status Booking</h5>
                </div>
                <div class="card-body">
                    <div style="height: 300px;">
                        <canvas id="bookingChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grafik Pembayaran -->
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0">Statistik Pembayaran 6 Bulan Terakhir</h5>
                </div>
                <div class="card-body">
                    <div style="height: 300px;">
                        <canvas id="paymentChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Booking Terbaru -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="card-title mb-0">Booking Terbaru</h5>
        </div>
        <div class="card-body p-0">
            <?php if (empty($recentBookings)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-calendar-times text-muted mb-3" style="font-size: 4rem;"></i>
                    <h6 class="text-muted">Belum ada booking</h6>
                </div>
            <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($recentBookings as $booking): ?>
                        <div class="list-group-item px-4 py-3">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1"><?= htmlspecialchars($booking['nama_pasien']) ?></h6>
                                    <div class="text-muted small">
                                        <i class="fas fa-user-md me-1"></i>
                                        <?= htmlspecialchars($booking['nama_dokter']) ?>
                                    </div>
                                    <div class="text-muted small">
                                        <i class="fas fa-calendar me-1"></i>
                                        <?= date('d/m/Y', strtotime($booking['tanggal'])) ?>
                                        <i class="fas fa-clock ms-2 me-1"></i>
                                        <?= date('H:i', strtotime($booking['jam'])) ?>
                                    </div>
                                </div>
                                <span class="badge bg-<?= getStatusBadgeClass($booking['status']) ?> rounded-pill">
                                    <?= ucfirst($booking['status']) ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Grafik Status Booking
    const bookingCtx = document.getElementById('bookingChart');
    if (bookingCtx) {
        new Chart(bookingCtx, {
            type: 'doughnut',
            data: {
                labels: ['Menunggu', 'Dikonfirmasi', 'Selesai'],
                datasets: [{
                    data: [
                        <?= $bookingStats['pending_booking'] ?? 0 ?>,
                        <?= $bookingStats['confirmed_booking'] ?? 0 ?>,
                        <?= $bookingStats['completed_booking'] ?? 0 ?>
                    ],
                    backgroundColor: [
                        '#ffc107', // warning
                        '#28a745', // success
                        '#17a2b8'  // info
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    // Grafik Pembayaran
    const paymentCtx = document.getElementById('paymentChart');
    if (paymentCtx) {
        new Chart(paymentCtx, {
            type: 'line',
            data: {
                labels: [
                    <?php 
                    if (!empty($monthlyPayments)) {
                        foreach ($monthlyPayments as $payment) {
                            $bulan = date('M Y', strtotime($payment['bulan'] . '-01'));
                            echo "'$bulan',";
                        }
                    } else {
                        // Jika tidak ada data, tampilkan 6 bulan terakhir
                        for ($i = 5; $i >= 0; $i--) {
                            echo "'" . date('M Y', strtotime("-$i month")) . "',";
                        }
                    }
                    ?>
                ],
                datasets: [{
                    label: 'Total Pembayaran',
                    data: [
                        <?php 
                        if (!empty($monthlyPayments)) {
                            foreach ($monthlyPayments as $payment) {
                                echo $payment['total'] . ',';
                            }
                        } else {
                            // Jika tidak ada data, isi dengan 0
                            for ($i = 0; $i < 6; $i++) {
                                echo "0,";
                            }
                        }
                        ?>
                    ],
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>

<style>
.card {
    transition: transform 0.2s;
}

.card:hover {
    transform: translateY(-5px);
}

.bg-opacity-10 {
    --bs-bg-opacity: 0.1;
}

.list-group-item:hover {
    background-color: #f8f9fa;
}

.badge {
    font-weight: 500;
}

@media (max-width: 576px) {
    .card-body {
        padding: 1rem;
    }
}
</style> 