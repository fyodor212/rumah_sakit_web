<?php
require_once 'config/database.php';
require_once 'config/functions.php';

// Cek akses admin
requireAdmin();

// Ambil ID dari POST data
$id = $_POST['patient_id'] ?? null;

// Debug log
error_log("Received patient_id: " . print_r($id, true));

if (!$id || !is_numeric($id)) {
    $_SESSION['message'] = "ID pasien tidak valid.";
    $_SESSION['message_type'] = 'danger';
    error_log("Invalid patient ID: " . print_r($id, true));
    header('Location: index.php?page=admin/manage_patients');
    exit;
}

try {
    // Validasi apakah pasien ada
    $checkQuery = "SELECT id FROM pasien WHERE id = ?";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->execute([$id]);
    
    if ($checkStmt->rowCount() === 0) {
        $_SESSION['message'] = "Pasien tidak ditemukan.";
        $_SESSION['message_type'] = 'danger';
        error_log("Patient not found with ID: " . $id);
        header('Location: index.php?page=admin/manage_patients');
        exit;
    }

    // Proses pembaruan data
    $nama = trim($_POST['nama'] ?? '');
    $no_hp = trim($_POST['no_hp'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');

    // Debug log
    error_log("Updating patient data: " . print_r($_POST, true));

    // Update data pasien
    $updateQuery = "UPDATE pasien SET nama = ?, no_hp = ?, alamat = ? WHERE id = ?";
    $updateStmt = $db->prepare($updateQuery);
    $updateStmt->execute([$nama, $no_hp, $alamat, $id]);

    $_SESSION['message'] = "Data pasien berhasil diperbarui.";
    $_SESSION['message_type'] = 'success';
    header('Location: index.php?page=admin/manage_patients');
    exit;

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $_SESSION['message'] = "Terjadi kesalahan saat memperbarui data pasien.";
    $_SESSION['message_type'] = 'danger';
    header('Location: index.php?page=admin/manage_patients');
    exit;
}
?>

<div class="container-fluid p-4">
    <h2 class="mb-4">Edit Pasien</h2>
    
    <form method="POST">
        <div class="mb-3">
            <label for="nama" class="form-label">Nama</label>
            <input type="text" class="form-control" id="nama" name="nama" value="<?= htmlspecialchars($patient['nama']) ?>" required>
        </div>
        <div class="mb-3">
            <label for="tanggal_lahir" class="form-label">Tanggal Lahir</label>
            <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir" value="<?= $patient['tanggal_lahir'] ?>" required>
        </div>
        <div class="mb-3">
            <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
            <select class="form-select" id="jenis_kelamin" name="jenis_kelamin" required>
                <option value="L" <?= $patient['jenis_kelamin'] === 'L' ? 'selected' : '' ?>>Laki-laki</option>
                <option value="P" <?= $patient['jenis_kelamin'] === 'P' ? 'selected' : '' ?>>Perempuan</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select class="form-select" id="status" name="status" required>
                <option value="active" <?= $patient['status'] === 'active' ? 'selected' : '' ?>>Aktif</option>
                <option value="inactive" <?= $patient['status'] === 'inactive' ? 'selected' : '' ?>>Tidak Aktif</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        <a href="index.php?page=admin/manage_patients" class="btn btn-secondary">Batal</a>
    </form>
</div> 