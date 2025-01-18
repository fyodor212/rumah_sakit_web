<?php
require_once 'config/database.php';
require_once 'config/functions.php';

// Cek apakah user adalah admin
if (!isAdmin()) {
    header('Location: index.php?page=auth/login');
    exit;
}

// Ambil ID jadwal dari URL
$scheduleId = $_GET['id'] ?? null;
if (!$scheduleId) {
    header('Location: index.php?page=admin/manage_schedules');
    exit;
}

// Array hari
$days = [
    1 => 'Senin',
    2 => 'Selasa',
    3 => 'Rabu',
    4 => 'Kamis',
    5 => 'Jumat',
    6 => 'Sabtu',
    7 => 'Minggu'
];

// Ambil data jadwal
try {
    $query = "SELECT j.*, d.nama as nama_dokter, d.spesialis 
              FROM jadwal_dokter j 
              JOIN dokter d ON j.dokter_id = d.id 
              WHERE j.id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$scheduleId]);
    $schedule = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$schedule) {
        header('Location: index.php?page=admin/manage_schedules');
        exit;
    }

    // Ambil data dokter untuk dropdown
    $query = "SELECT id, nama, spesialis FROM dokter WHERE status = 'active' ORDER BY nama ASC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error: " . $e->getMessage();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $dokter_id = $_POST['dokter_id'] ?? '';
    $hari = $_POST['hari'] ?? '';
    $jam_mulai = $_POST['jam_mulai'] ?? '';
    $jam_selesai = $_POST['jam_selesai'] ?? '';
    $kuota = $_POST['kuota'] ?? 0;
    $status = $_POST['status'] ?? 'active';

    // Validasi jadwal bentrok (kecuali dengan jadwal yang sedang diedit)
    try {
        $query = "SELECT COUNT(*) as total FROM jadwal_dokter 
                  WHERE dokter_id = ? AND hari = ? AND id != ? AND
                  ((jam_mulai BETWEEN ? AND ?) OR 
                   (jam_selesai BETWEEN ? AND ?) OR 
                   (jam_mulai <= ? AND jam_selesai >= ?))";
        $stmt = $db->prepare($query);
        $stmt->execute([$dokter_id, $hari, $scheduleId, $jam_mulai, $jam_selesai, 
                       $jam_mulai, $jam_selesai, $jam_mulai, $jam_selesai]);
        $exists = $stmt->fetch(PDO::FETCH_ASSOC)['total'] > 0;

        if ($exists) {
            $error = "Jadwal bentrok dengan jadwal yang sudah ada!";
        } else {
            // Update jadwal
            $query = "UPDATE jadwal_dokter 
                     SET dokter_id = ?, hari = ?, jam_mulai = ?, jam_selesai = ?, 
                         kuota = ?, status = ? 
                     WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$dokter_id, $hari, $jam_mulai, $jam_selesai, $kuota, $status, $scheduleId]);

            $_SESSION['success'] = "Jadwal berhasil diperbarui!";
            header('Location: index.php?page=admin/manage_schedules');
            exit;
        }
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Edit Jadwal</h5>
                    <a href="?page=admin/manage_schedules" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left me-2"></i>Kembali
                    </a>
                </div>
                <div class="card-body">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger">
                            <?= $error ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="dokter_id" class="form-label">Dokter</label>
                            <select class="form-select" id="dokter_id" name="dokter_id" required>
                                <option value="">Pilih Dokter</option>
                                <?php foreach ($doctors as $doctor): ?>
                                    <option value="<?= $doctor['id'] ?>" 
                                            <?= $schedule['dokter_id'] == $doctor['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($doctor['nama']) ?> - 
                                        <?= htmlspecialchars($doctor['spesialis']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="hari" class="form-label">Hari</label>
                            <select class="form-select" id="hari" name="hari" required>
                                <option value="">Pilih Hari</option>
                                <?php foreach ($days as $key => $day): ?>
                                    <option value="<?= $key ?>" 
                                            <?= $schedule['hari'] == $key ? 'selected' : '' ?>>
                                        <?= $day ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="jam_mulai" class="form-label">Jam Mulai</label>
                                <input type="time" class="form-control" id="jam_mulai" name="jam_mulai" 
                                       value="<?= date('H:i', strtotime($schedule['jam_mulai'])) ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="jam_selesai" class="form-label">Jam Selesai</label>
                                <input type="time" class="form-control" id="jam_selesai" name="jam_selesai" 
                                       value="<?= date('H:i', strtotime($schedule['jam_selesai'])) ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="kuota" class="form-label">Kuota Pasien</label>
                            <input type="number" class="form-control" id="kuota" name="kuota" 
                                   min="1" value="<?= $schedule['kuota'] ?>" required>
                            <div class="form-text">Jumlah maksimal pasien yang dapat dilayani</div>
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="active" <?= $schedule['status'] == 'active' ? 'selected' : '' ?>>
                                    Aktif
                                </option>
                                <option value="inactive" <?= $schedule['status'] == 'inactive' ? 'selected' : '' ?>>
                                    Tidak Aktif
                                </option>
                            </select>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Simpan Perubahan
                            </button>
                        </div>
                    </form>
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

.form-label {
    font-weight: 500;
    color: #333;
}

.form-control, .form-select {
    padding: 0.75rem 1rem;
    border-color: #e0e0e0;
}

.form-control:focus, .form-select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13,110,253,.25);
}

.form-text {
    color: #6c757d;
    font-size: 0.875rem;
}

.btn-primary {
    padding: 0.75rem 1.5rem;
    font-weight: 500;
}

.alert {
    border-radius: 8px;
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

<script>
// Validasi jam selesai harus lebih besar dari jam mulai
document.getElementById('jam_selesai').addEventListener('change', function() {
    var jamMulai = document.getElementById('jam_mulai').value;
    var jamSelesai = this.value;
    
    if (jamMulai && jamSelesai && jamSelesai <= jamMulai) {
        alert('Jam selesai harus lebih besar dari jam mulai');
        this.value = '';
    }
});
</script> 