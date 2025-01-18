<?php
require_once 'config/database.php';
require_once 'config/functions.php';

// Cek apakah user adalah admin
if (!isAdmin()) {
    header('Location: index.php?page=auth/login');
    exit;
}

// Ambil ID pasien dari URL
$patientId = $_GET['id'] ?? null;
if (!$patientId) {
    header('Location: index.php?page=admin/manage_patients');
    exit;
}

// Ambil data pasien
try {
    $query = "SELECT p.*, u.email, u.username 
              FROM pasien p 
              JOIN users u ON p.user_id = u.id 
              WHERE p.id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$patientId]);
    $patient = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$patient) {
        header('Location: index.php?page=admin/manage_patients');
        exit;
    }

    // Ambil riwayat booking pasien
    $query = "SELECT b.*, d.nama as nama_dokter, l.nama as nama_layanan 
              FROM booking b 
              JOIN dokter d ON b.dokter_id = d.id 
              JOIN layanan l ON b.layanan_id = l.id 
              WHERE b.pasien_id = ? 
              ORDER BY b.tanggal DESC";
    $stmt = $db->prepare($query);
    $stmt->execute([$patientId]);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error: " . $e->getMessage();
}
?>

<div class="container mt-4">
    <div class="row">
        <!-- Profile Card -->
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Detail Pasien</h5>
                    <a href="?page=admin/manage_patients" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left me-2"></i>Kembali
                    </a>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <i class="fas fa-user-circle fa-4x text-primary"></i>
                    </div>
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>No. RM</strong></td>
                            <td>: <?= htmlspecialchars($patient['no_rm']) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Nama</strong></td>
                            <td>: <?= htmlspecialchars($patient['nama']) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Email</strong></td>
                            <td>: <?= htmlspecialchars($patient['email']) ?></td>
                        </tr>
                        <tr>
                            <td><strong>Username</strong></td>
                            <td>: <?= htmlspecialchars($patient['username']) ?></td>
                        </tr>
                        <tr>
                            <td><strong>No. HP</strong></td>
                            <td>: <?= htmlspecialchars($patient['no_hp'] ?? '-') ?></td>
                        </tr>
                        <tr>
                            <td><strong>Alamat</strong></td>
                            <td>: <?= htmlspecialchars($patient['alamat'] ?? '-') ?></td>
                        </tr>
                        <tr>
                            <td><strong>Status</strong></td>
                            <td>
                                <span class="badge bg-<?= $patient['status'] == 'active' ? 'success' : 'danger' ?>">
                                    <?= ucfirst($patient['status'] ?? 'active') ?>
                                </span>
                            </td>
                        </tr>
                    </table>
                    <div class="d-grid gap-2 mt-3">
                        <a href="?page=admin/edit_patient&id=<?= $patient['id'] ?>" class="btn btn-warning">
                            <i class="fas fa-edit me-2"></i>Edit Data
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Booking History -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Riwayat Booking</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($bookings)): ?>
                        <p class="text-center text-muted my-4">Belum ada riwayat booking</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Dokter</th>
                                        <th>Layanan</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($bookings as $booking): ?>
                                        <tr>
                                            <td><?= date('d/m/Y', strtotime($booking['tanggal'])) ?></td>
                                            <td><?= htmlspecialchars($booking['nama_dokter']) ?></td>
                                            <td><?= htmlspecialchars($booking['nama_layanan']) ?></td>
                                            <td>
                                                <span class="badge bg-<?= getStatusColor($booking['status']) ?>">
                                                    <?= ucfirst($booking['status']) ?>
                                                </span>
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
    </div>
</div>

<style>
.card {
    border: none;
    box-shadow: 0 0 15px rgba(0,0,0,.05);
}

.card-header {
    background-color: #fff;
    border-bottom: 1px solid #eee;
    padding: 15px 20px;
}

.table th {
    font-weight: 600;
    color: #333;
}

.table td {
    vertical-align: middle;
}

.badge {
    padding: 6px 12px;
    font-weight: 500;
}

.fas {
    color: #0d6efd;
}

@media (max-width: 768px) {
    .card-header {
        flex-direction: column;
        gap: 1rem;
    }
    
    .card-header .btn {
        width: 100%;
    }
}
</style> 