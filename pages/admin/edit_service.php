<?php
require_once 'config/database.php';
require_once 'config/functions.php';

// Cek apakah user adalah admin
if (!isAdmin()) {
    header('Location: index.php?page=auth/login');
    exit;
}

// Ambil ID layanan dari URL
$serviceId = $_GET['id'] ?? null;
if (!$serviceId) {
    header('Location: index.php?page=admin/manage_services');
    exit;
}

// Ambil data layanan
try {
    $query = "SELECT * FROM layanan WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$serviceId]);
    $service = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$service) {
        header('Location: index.php?page=admin/manage_services');
        exit;
    }
} catch (PDOException $e) {
    $error = "Error: " . $e->getMessage();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $_POST['nama'] ?? '';
    $deskripsi = $_POST['deskripsi'] ?? '';
    $harga = $_POST['harga'] ?? 0;
    $status = $_POST['status'] ?? 'active';

    try {
        $query = "UPDATE layanan SET nama = ?, deskripsi = ?, harga = ?, status = ? WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$nama, $deskripsi, $harga, $status, $serviceId]);

        $_SESSION['success'] = "Layanan berhasil diperbarui!";
        header('Location: index.php?page=admin/manage_services');
        exit;
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
                    <h5 class="mb-0">Edit Layanan</h5>
                    <a href="?page=admin/manage_services" class="btn btn-secondary btn-sm">
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
                            <label for="nama" class="form-label">Nama Layanan</label>
                            <input type="text" class="form-control" id="nama" name="nama" 
                                   value="<?= htmlspecialchars($service['nama']) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="deskripsi" class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="deskripsi" name="deskripsi" rows="4" required
                            ><?= htmlspecialchars($service['deskripsi']) ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="harga" class="form-label">Harga (Rp)</label>
                            <input type="number" class="form-control" id="harga" name="harga" 
                                   value="<?= htmlspecialchars($service['harga']) ?>"
                                   min="0" step="1000" required>
                            <div class="form-text">Masukkan harga dalam Rupiah tanpa tanda pemisah</div>
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="active" <?= $service['status'] == 'active' ? 'selected' : '' ?>>
                                    Aktif
                                </option>
                                <option value="inactive" <?= $service['status'] == 'inactive' ? 'selected' : '' ?>>
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
// Format harga saat diketik
document.getElementById('harga').addEventListener('input', function(e) {
    let value = this.value.replace(/\D/g, '');
    this.value = value;
});
</script> 