<?php
require_once 'config/database.php';
require_once 'config/functions.php';

// Cek akses admin
requireAdmin();

try {
    // Query untuk mengambil semua pasien
    $query = "SELECT id, nama, tanggal_lahir, jenis_kelamin, status FROM pasien ORDER BY nama ASC";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log($e->getMessage());
    $_SESSION['message'] = "Terjadi kesalahan saat mengambil data pasien.";
    $_SESSION['message_type'] = 'danger';
}
?>

<div class="container-fluid p-4">
    <h2 class="mb-4">Manajemen Pasien</h2>
    
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama</th>
                            <th>Tanggal Lahir</th>
                            <th>Jenis Kelamin</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($patients as $patient): ?>
                            <tr>
                                <td>#<?= $patient['id'] ?></td>
                                <td><?= htmlspecialchars($patient['nama'] ?? 'N/A') ?></td>
                                <td><?= $patient['tanggal_lahir'] ? date('d/m/Y', strtotime($patient['tanggal_lahir'])) : 'N/A' ?></td>
                                <td><?= htmlspecialchars($patient['jenis_kelamin'] ?? 'N/A') ?></td>
                                <td>
                                    <span class="badge bg-<?= getStatusBadgeClass($patient['status']) ?>">
                                        <?= ucfirst($patient['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="index.php?page=admin/edit_patient&id=<?= $patient['id'] ?>" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="index.php?page=admin/delete_patient&id=<?= $patient['id'] ?>" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i> Hapus
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div> 