<?php
if (!isAdmin()) {
    header('Location: index.php');
    exit;
}

// Ambil ID pasien dari URL
$patient_id = $_GET['id'] ?? null;
if (!$patient_id) {
    $_SESSION['error'] = "ID Pasien tidak valid";
    header('Location: index.php?page=admin/manage_patients');
    exit;
}

try {
    // Query untuk mengambil data pasien
    $query = "SELECT p.*, u.email 
              FROM pasien p 
              LEFT JOIN users u ON p.user_id = u.id 
              WHERE p.id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$patient_id]);
    $patient = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$patient) {
        throw new Exception("Data pasien tidak ditemukan");
    }
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header('Location: index.php?page=admin/manage_patients');
    exit;
}
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-user-edit me-2"></i>Edit Data Pasien
                    </h5>
                    <a href="?page=admin/manage_patients" class="btn btn-light btn-sm">
                        <i class="fas fa-arrow-left me-1"></i>Kembali
                    </a>
                </div>
                <div class="card-body">
                    <form id="editPatientForm" method="POST" action="index.php?page=admin/handle_update_patient" class="needs-validation" novalidate>
                        <input type="hidden" name="patient_id" value="<?= htmlspecialchars($patient_id) ?>">
                        
                        <!-- No RM -->
                        <div class="mb-3">
                            <label class="form-label">Nomor RM</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($patient['no_rm']) ?>" readonly>
                        </div>

                        <!-- Nama -->
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" name="nama" 
                                   value="<?= htmlspecialchars($patient['nama']) ?>" required>
                            <div class="invalid-feedback">Nama harus diisi</div>
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" value="<?= htmlspecialchars($patient['email'] ?? '') ?>" readonly>
                        </div>

                        <!-- No HP -->
                        <div class="mb-3">
                            <label class="form-label">No. HP</label>
                            <input type="text" class="form-control" name="no_hp" 
                                   value="<?= htmlspecialchars($patient['no_hp'] ?? '') ?>">
                        </div>

                        <!-- Alamat -->
                        <div class="mb-3">
                            <label class="form-label">Alamat</label>
                            <textarea class="form-control" name="alamat" rows="3"><?= htmlspecialchars($patient['alamat'] ?? '') ?></textarea>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Simpan Perubahan
                            </button>
                            <a href="index.php?page=admin/manage_patients" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Kembali
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Form validation
document.getElementById('editPatientForm').addEventListener('submit', function(e) {
    if (!this.checkValidity()) {
        e.preventDefault();
        e.stopPropagation();
    }
    this.classList.add('was-validated');
});
</script>

<style>
.card {
    border: none;
}

.form-control:read-only {
    background-color: #f8f9fa;
}

.btn-light {
    background-color: #fff;
    border-color: #dee2e6;
}

.btn-light:hover {
    background-color: #f8f9fa;
}
</style> 