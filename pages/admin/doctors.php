<?php
if (!isAdmin()) {
    header('Location: index.php');
    exit;
}

try {
    // Ambil daftar unik spesialisasi untuk filter
    $query_spesialisasi = "SELECT DISTINCT spesialisasi FROM dokter ORDER BY spesialisasi";
    $stmt_spesialisasi = $db->prepare($query_spesialisasi);
    $stmt_spesialisasi->execute();
    $spesialisasi_list = $stmt_spesialisasi->fetchAll(PDO::FETCH_COLUMN);

    // Query untuk data dokter
    $query = "SELECT * FROM dokter ORDER BY created_at DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log($e->getMessage());
    $_SESSION['error'] = "Terjadi kesalahan saat mengambil data";
}
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-user-md me-2"></i>Daftar Dokter
                    </h5>
                    <a href="?page=admin/add_doctor" class="btn btn-light btn-sm">
                        <i class="fas fa-plus me-1"></i>Tambah Dokter
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

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= $_SESSION['error'] ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>

                    <!-- Filter Spesialisasi -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="filterSpesialisasi" class="form-label">Filter Spesialisasi</label>
                            <select class="form-select" id="filterSpesialisasi">
                                <option value="">Semua Spesialisasi</option>
                                <?php foreach($spesialisasi_list as $spesialisasi): ?>
                                    <option value="<?= htmlspecialchars($spesialisasi) ?>">
                                        <?= htmlspecialchars($spesialisasi) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover" id="doctorsTable">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama</th>
                                    <th>Spesialisasi</th>
                                    <th>Hari Praktik</th>
                                    <th>Jam Praktik</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($doctors)): ?>
                                    <?php foreach($doctors as $index => $doctor): ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td><?= htmlspecialchars($doctor['nama']) ?></td>
                                            <td><?= htmlspecialchars($doctor['spesialisasi']) ?></td>
                                            <td><?= htmlspecialchars($doctor['hari'] ?? '-') ?></td>
                                            <td><?= htmlspecialchars($doctor['jadwal'] ?? '-') ?></td>
                                            <td>
                                                <span class="badge bg-<?= $doctor['status'] === 'aktif' ? 'success' : 'danger' ?>">
                                                    <?= ucfirst($doctor['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="?page=admin/edit_doctor&id=<?= $doctor['id'] ?>" 
                                                   class="btn btn-warning btn-sm">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button class="btn btn-danger btn-sm" 
                                                        onclick="deleteDoctor(<?= $doctor['id'] ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <i class="fas fa-user-md text-muted mb-3" style="font-size: 48px;"></i>
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
    </div>
</div>

<script>
// Inisialisasi DataTable
let doctorsTable;

$(document).ready(function() {
    doctorsTable = $('#doctorsTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
        }
    });

    // Event listener untuk filter spesialisasi
    $('#filterSpesialisasi').on('change', function() {
        let selectedSpesialisasi = $(this).val();
        
        // Reset pencarian sebelumnya
        doctorsTable.column(2).search('').draw();
        
        if (selectedSpesialisasi) {
            // Terapkan filter baru
            doctorsTable.column(2).search(selectedSpesialisasi, true, false).draw();
        }
    });
});

function deleteDoctor(id) {
    Swal.fire({
        title: 'Hapus Dokter?',
        text: 'Data yang dihapus tidak dapat dikembalikan',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Tampilkan loading
            Swal.fire({
                title: 'Memproses...',
                text: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            const formData = new FormData();
            formData.append('doctor_id', id);
            
            fetch('index.php?page=admin/handle_delete_doctor', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: data.message,
                        showConfirmButton: true,
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#28a745'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Redirect ke halaman list dokter
                            window.location.href = 'index.php?page=admin/doctors';
                        }
                    });
                } else {
                    throw new Error(data.message || 'Terjadi kesalahan saat menghapus dokter');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: error.message || 'Terjadi kesalahan saat menghapus dokter',
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'OK'
                });
            });
        }
    });
}
</script>

<style>
.badge {
    font-size: 0.875rem;
    padding: 0.5rem 0.75rem;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
}

.table > :not(caption) > * > * {
    padding: 0.75rem;
}
</style> 