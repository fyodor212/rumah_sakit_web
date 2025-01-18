<?php
// Pastikan session dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../Helpers/helpers.php';
require_once __DIR__ . '/../../../../config/database.php';

// Cek akses
if (!isAdmin()) {
    header('Location: index.php');
    exit;
}

// Tambah kolom status jika belum ada
try {
    $checkColumn = $db->query("SHOW COLUMNS FROM layanan LIKE 'status'");
    if ($checkColumn->rowCount() == 0) {
        $db->exec("ALTER TABLE layanan ADD COLUMN status ENUM('tersedia', 'tidak_tersedia') DEFAULT 'tersedia'");
    }
} catch (PDOException $e) {
    error_log("Error checking/adding status column: " . $e->getMessage());
}

// Query untuk mengambil semua layanan
$query = "SELECT * FROM layanan ORDER BY created_at DESC";

try {
    $stmt = $db->query($query);
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error: " . $e->getMessage();
}
?>

<div class="container-fluid px-4 py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-list-alt me-2"></i>Kelola Layanan
                    </h5>
                    <button type="button" class="btn btn-light" onclick="showAddModal()">
                        <i class="fas fa-plus me-2"></i>Tambah Layanan
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="servicesTable">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Layanan</th>
                                    <th>Deskripsi</th>
                                    <th>Icon</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($services)): ?>
                                    <?php foreach($services as $index => $service): ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td><?= htmlspecialchars($service['nama']) ?></td>
                                            <td><?= htmlspecialchars($service['deskripsi']) ?></td>
                                            <td>
                                                <?php
                                                $icon = !empty($service['icon']) ? $service['icon'] : 'fas fa-stethoscope';
                                                ?>
                                                <i class="<?= htmlspecialchars($icon) ?> fa-2x text-primary"></i>
                                            </td>
                                            <td>
                                                <span class="badge <?= $service['status'] === 'tersedia' ? 'bg-success' : 'bg-danger' ?>"><?= ucfirst(str_replace('_', ' ', $service['status'])) ?></span>
                                            </td>
                                            <td>
                                                <button type="button" 
                                                        class="btn btn-info btn-sm text-white"
                                                        onclick="editService(<?= $service['id'] ?>)">
                                                    <i class="fas fa-edit me-1"></i>Edit
                                                </button>
                                                <button type="button" 
                                                        class="btn btn-danger btn-sm"
                                                        onclick="deleteService(<?= $service['id'] ?>)">
                                                    <i class="fas fa-trash me-1"></i>Hapus
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-3">
                                            <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                                            <p class="text-muted mb-0">Belum ada data layanan</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah/Edit Layanan -->
<div class="modal fade" id="serviceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="serviceModalTitle">Tambah Layanan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="serviceForm">
                    <input type="hidden" id="serviceId" name="id">
                    <div class="mb-3">
                        <label class="form-label">Nama Layanan</label>
                        <input type="text" class="form-control" id="serviceName" name="nama" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="serviceDesc" name="deskripsi" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kategori</label>
                        <input type="text" class="form-control" id="serviceCategory" name="kategori" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Harga</label>
                        <input type="number" class="form-control" id="servicePrice" name="harga" min="0" step="1000">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" id="serviceStatus" name="status" required>
                            <option value="tersedia">Tersedia</option>
                            <option value="tidak_tersedia">Tidak Tersedia</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="saveService()">Simpan</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#servicesTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
        }
    });
});

function showAddModal() {
    $('#serviceModalTitle').text('Tambah Layanan');
    $('#serviceForm')[0].reset();
    $('#serviceId').val('');
    new bootstrap.Modal(document.getElementById('serviceModal')).show();
}

function editService(id) {
    fetch(`index.php?page=admin/get_service&id=${id}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            $('#serviceModalTitle').text('Edit Layanan');
            $('#serviceId').val(data.service.id);
            $('#serviceName').val(data.service.nama);
            $('#serviceDesc').val(data.service.deskripsi);
            $('#serviceIcon').val(data.service.icon);
            $('#serviceStatus').val(data.service.status);
            new bootstrap.Modal(document.getElementById('serviceModal')).show();
        } else {
            throw new Error(data.message);
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: error.message
        });
    });
}

function saveService() {
    const formData = new FormData(document.getElementById('serviceForm'));
    const isEdit = formData.get('id') !== '';

    fetch(`index.php?page=admin/${isEdit ? 'update' : 'add'}_service`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: data.message,
                timer: 1500,
                showConfirmButton: false
            }).then(() => location.reload());
        } else {
            throw new Error(data.message);
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: error.message
        });
    });
}

function deleteService(id) {
    Swal.fire({
        title: 'Hapus Layanan',
        text: 'Apakah Anda yakin ingin menghapus layanan ini?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('index.php?page=admin/delete_service', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: data.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => location.reload());
                } else {
                    throw new Error(data.message);
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: error.message
                });
            });
        }
    });
}
</script>

<style>
.badge {
    font-weight: 500;
    padding: 0.5rem 0.75rem;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    margin: 0.125rem;
}

#servicesTable td {
    vertical-align: middle;
}

.form-text {
    font-size: 0.875rem;
}
</style> 