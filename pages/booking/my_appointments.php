<?php
require_once 'config/database.php';
require_once 'config/functions.php';

if (!isLoggedIn() || !isPasien()) {
    header('Location: index.php?page=auth/login');
    exit;
}

// Ambil data pasien
try {
    $query = "SELECT id FROM pasien WHERE user_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$_SESSION['user_id']]);
    $pasien = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$pasien) {
        throw new Exception("Data pasien tidak ditemukan");
    }
    
    // Ambil daftar janji
    $query = "SELECT b.*, d.nama as nama_dokter, d.spesialisasi,
              l.nama as nama_layanan, l.harga
              FROM booking b
              JOIN dokter d ON b.dokter_id = d.id
              JOIN layanan l ON b.layanan_id = l.id
              WHERE b.pasien_id = ?
              ORDER BY b.tanggal DESC, b.jam DESC";
    $stmt = $db->prepare($query);
    $stmt->execute([$pasien['id']]);
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Janji Temu Saya</h5>
                    <a href="?page=patient/book_appointment" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-2"></i>Buat Janji Baru
                    </a>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= $_SESSION['success'] ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php unset($_SESSION['success']); ?>
                    <?php endif; ?>

                    <?php if (!empty($appointments)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Jam</th>
                                        <th>Dokter</th>
                                        <th>Layanan</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($appointments as $apt): ?>
                                        <tr>
                                            <td><?= date('d/m/Y', strtotime($apt['tanggal'])) ?></td>
                                            <td><?= $apt['jam'] ?></td>
                                            <td>
                                                <?= htmlspecialchars($apt['nama_dokter']) ?><br>
                                                <small class="text-muted"><?= $apt['spesialisasi'] ?></small>
                                            </td>
                                            <td>
                                                <?= htmlspecialchars($apt['nama_layanan']) ?><br>
                                                <small class="text-muted">Rp <?= number_format($apt['harga'], 0, ',', '.') ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= getStatusColor($apt['status']) ?>">
                                                    <?= ucfirst($apt['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($apt['status'] == 'pending'): ?>
                                                    <button class="btn btn-danger btn-sm" 
                                                            onclick="cancelAppointment(<?= $apt['id'] ?>)">
                                                        <i class="fas fa-times me-1"></i>Batalkan
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-times text-muted mb-3" style="font-size: 48px;"></i>
                            <p class="mb-0">Belum ada janji temu</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function cancelAppointment(id) {
    if (confirm('Apakah Anda yakin ingin membatalkan janji ini?')) {
        window.location.href = `index.php?page=booking/cancel&id=${id}`;
    }
}
</script> 