<?php
require_once 'config/database.php';
require_once 'config/functions.php';

// Cek apakah user adalah admin
if (!isAdmin()) {
    header('Location: index.php?page=auth/login');
    exit;
}

// Handle hapus dokter
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_id'])) {
    $doctor_id = $_POST['delete_id'];
    
    try {
        // Cek apakah dokter memiliki booking aktif
        $query = "SELECT COUNT(*) FROM booking 
                 WHERE dokter_id = ? 
                 AND status IN ('pending', 'confirmed')";
        $stmt = $db->prepare($query);
        $stmt->execute([$doctor_id]);
        $active_bookings = $stmt->fetchColumn();

        if ($active_bookings > 0) {
            $_SESSION['error'] = "Dokter tidak dapat dihapus karena masih memiliki booking aktif!";
        } else {
            // Hapus data dokter
            $query = "DELETE FROM dokter WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$doctor_id]);

            $_SESSION['success'] = "Data dokter berhasil dihapus!";
        }
        header('Location: index.php?page=admin/manage_doctor');
        exit;
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Handle pencarian
$search = $_GET['search'] ?? '';
$searchWhere = '';
$params = [];

if (!empty($search)) {
    $searchWhere = "WHERE d.nama LIKE ? OR d.spesialisasi LIKE ?";
    $params = ["%$search%", "%$search%"];
}

// Ambil data dokter
try {
    $query = "SELECT d.*, u.email, u.username,
              (SELECT COUNT(*) FROM booking b WHERE b.dokter_id = d.id) as total_pasien
              FROM dokter d
              LEFT JOIN users u ON d.user_id = u.id
              $searchWhere
              ORDER BY d.created_at DESC";
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error: " . $e->getMessage();
}

// Daftar hari
$days = [
    'Senin',
    'Selasa',
    'Rabu',
    'Kamis',
    'Jumat',
    'Sabtu',
    'Minggu'
];

// Daftar jadwal
$schedules = [
    '08:00-12:00' => 'Pagi (08:00 - 12:00)',
    '13:00-17:00' => 'Siang (13:00 - 17:00)',
    '18:00-21:00' => 'Malam (18:00 - 21:00)'
];

// Fungsi untuk mendapatkan nama hari
function getNamaHari($hari) {
    $daftarHari = [
        '1' => 'Senin',
        '2' => 'Selasa', 
        '3' => 'Rabu',
        '4' => 'Kamis',
        '5' => 'Jumat',
        '6' => 'Sabtu',
        '7' => 'Minggu'
    ];
    return $daftarHari[$hari] ?? $hari;
}
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <button onclick="history.back()" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </button>
    </div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Kelola Data Dokter</h5>
            <div class="d-flex gap-2">
                <form class="d-flex">
                    <input type="hidden" name="page" value="admin/manage_doctor">
                    <input type="search" name="search" class="form-control me-2" 
                           placeholder="Cari nama/spesialisasi..." value="<?= htmlspecialchars($search) ?>">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
                <a href="?page=admin/add_doctor" class="btn btn-primary">
                    <i class="fas fa-plus-circle me-2"></i>Tambah Dokter
                </a>
            </div>
        </div>
        <div class="card-body">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?= $_SESSION['success'] ?>
                    <?php unset($_SESSION['success']) ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?= $_SESSION['error'] ?>
                    <?php unset($_SESSION['error']) ?>
                </div>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nama & Spesialisasi</th>
                            <th>Hari Praktik</th>
                            <th>Jadwal</th>
                            <th>Status</th>
                            <th>Total Pasien</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($doctors as $doctor): ?>
                            <tr>
                                <td>
                                    <div><?= htmlspecialchars($doctor['nama']) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($doctor['spesialisasi']) ?></small>
                                    <?php if ($doctor['email']): ?>
                                        <div class="small text-muted">
                                            <i class="fas fa-envelope me-1"></i><?= htmlspecialchars($doctor['email']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php 
                                    $hari_praktik = explode(',', $doctor['hari'] ?? '');
                                    foreach ($hari_praktik as $hari): 
                                        $namaHari = getNamaHari(trim($hari));
                                    ?>
                                        <span class="badge bg-info me-1"><?= $namaHari ?></span>
                                    <?php endforeach; ?>
                                </td>
                                <td><?= htmlspecialchars($doctor['jadwal']) ?></td>
                                <td>
                                    <span class="badge bg-<?= $doctor['status'] == 'aktif' ? 'success' : 'danger' ?>">
                                        <?= ucfirst($doctor['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-primary">
                                        <?= $doctor['total_pasien'] ?> pasien
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="?page=admin/edit_doctor&id=<?= $doctor['id'] ?>" 
                                           class="btn btn-info btn-sm" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST" class="d-inline" 
                                              onsubmit="return confirm('Yakin ingin menghapus dokter ini?')">
                                            <input type="hidden" name="delete_id" value="<?= $doctor['id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <?php if (empty($doctors)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <i class="fas fa-user-md text-muted mb-3" style="font-size: 40px;"></i>
                                    <p class="mb-0">Belum ada data dokter</p>
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
    background-color: #f8f9fa;
}

.badge {
    font-weight: 500;
    padding: 0.5rem 0.75rem;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
}

.table td {
    vertical-align: middle;
}
</style> 