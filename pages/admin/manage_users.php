<?php
require_once 'config/database.php';
require_once 'config/functions.php';

// Cek apakah user adalah admin
if (!isAdmin()) {
    header('Location: index.php');
    exit;
}

try {
    // Ubah query untuk menampilkan semua user termasuk admin
    $query = "SELECT u.*, 
              CASE 
                WHEN p.id IS NOT NULL THEN 'pasien'
                WHEN d.id IS NOT NULL THEN 'dokter'
                ELSE u.role 
              END as actual_role,
              COALESCE(p.nama, d.nama, u.username) as nama_lengkap
              FROM users u
              LEFT JOIN pasien p ON u.id = p.user_id
              LEFT JOIN dokter d ON u.id = d.user_id
              ORDER BY u.created_at DESC";
              
    $stmt = $db->prepare($query);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                        <i class="fas fa-users me-2"></i>Kelola Users
                    </h5>
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

                    <div class="table-responsive">
                        <table class="table table-hover" id="usersTable">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Nama Lengkap</th>
                                    <th>Role</th>
                                    <th>Terdaftar</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($users)): ?>
                                    <?php foreach($users as $index => $user): ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td><?= htmlspecialchars($user['username']) ?></td>
                                            <td><?= htmlspecialchars($user['email']) ?></td>
                                            <td><?= htmlspecialchars($user['nama_lengkap']) ?></td>
                                            <td>
                                                <span class="badge bg-<?= getBadgeColor($user['actual_role']) ?>">
                                                    <?= ucfirst($user['actual_role']) ?>
                                                </span>
                                            </td>
                                            <td><?= date('d/m/Y H:i', strtotime($user['created_at'])) ?></td>
                                            <td>
                                                <?php if ($user['role'] !== 'admin' || $_SESSION['user_id'] != $user['id']): ?>
                                                    <button type="button" 
                                                            class="btn btn-warning btn-sm"
                                                            onclick="editRole(<?= $user['id'] ?>, '<?= $user['actual_role'] ?>')">
                                                        <i class="fas fa-user-edit me-1"></i>Role
                                                    </button>
                                                    <button type="button" 
                                                            class="btn btn-danger btn-sm"
                                                            onclick="deleteUser(<?= $user['id'] ?>)">
                                                        <i class="fas fa-trash me-1"></i>Hapus
                                                    </button>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Current Admin</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center">Tidak ada data user</td>
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

<div class="modal fade" id="editRoleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Role User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editRoleForm">
                    <input type="hidden" id="userId" name="user_id">
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select class="form-select" id="userRole" name="role" required>
                            <option value="admin">Admin</option>
                            <option value="pasien">Pasien</option>
                            <option value="dokter">Dokter</option>
                        </select>
                        <div class="form-text text-warning" id="adminWarning" style="display: none;">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            Perhatian: User dengan role admin akan memiliki akses penuh ke sistem
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="updateRole()">Simpan</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#usersTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
        }
    });
});

function deleteUser(userId) {
    Swal.fire({
        title: 'Konfirmasi Hapus',
        text: 'Apakah Anda yakin ingin menghapus user ini?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('index.php?page=admin/delete_user', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `user_id=${userId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'User berhasil dihapus',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    throw new Error(data.message);
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: error.message || 'Terjadi kesalahan saat menghapus user'
                });
            });
        }
    });
}

function editRole(userId, currentRole) {
    document.getElementById('userId').value = userId;
    document.getElementById('userRole').value = currentRole;
    
    // Tampilkan warning jika role saat ini adalah admin
    const warning = document.getElementById('adminWarning');
    warning.style.display = currentRole === 'admin' ? 'block' : 'none';
    
    const modal = new bootstrap.Modal(document.getElementById('editRoleModal'));
    modal.show();
}

function updateRole() {
    const form = document.getElementById('editRoleForm');
    const formData = new FormData(form);
    const userId = formData.get('user_id');
    const newRole = formData.get('role');

    // Validasi client-side
    if (!userId || !newRole) {
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'Data tidak lengkap'
        });
        return;
    }

    // Konfirmasi khusus untuk role admin
    let confirmMessage = 'Apakah Anda yakin ingin mengubah role user ini?';
    if (newRole === 'admin') {
        confirmMessage = 'PERHATIAN: Anda akan memberikan akses admin penuh kepada user ini. Lanjutkan?';
    }
    
    Swal.fire({
        title: 'Konfirmasi Update Role',
        text: confirmMessage,
        icon: newRole === 'admin' ? 'warning' : 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Update',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('index.php?page=admin/update_user_role', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `user_id=${userId}&role=${newRole}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Role user berhasil diupdate',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    throw new Error(data.message);
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: error.message || 'Terjadi kesalahan saat mengupdate role'
                });
            });
        }
    });
}
</script>

<style>
.table th {
    background-color: #f8f9fa;
}

.badge {
    font-weight: 500;
    padding: 0.5rem 0.75rem;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    margin: 0.125rem;
}

#usersTable td {
    vertical-align: middle;
}
</style> 