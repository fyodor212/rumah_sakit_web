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

// Query untuk mengambil semua dokter
$query = "SELECT * FROM dokter ORDER BY nama ASC";

try {
    $stmt = $db->query($query);
    $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching doctors: " . $e->getMessage());
    $doctors = [];
}
?>

<div class="container-fluid px-4 py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-alt me-2"></i>Kelola Jadwal Dokter
                    </h5>
                    <button type="button" class="btn btn-light" onclick="showAddModal()">
                            <i class="fas fa-plus me-2"></i>Tambah Jadwal
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="doctorsTable">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama</th>
                                    <th>Spesialisasi</th>
                                    <th>Hari Praktik</th>
                                    <th>Jadwal</th>
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
                                            <td><?= $doctor['hari'] ? htmlspecialchars($doctor['hari']) : '-' ?></td>
                                            <td><?= $doctor['jadwal'] ? htmlspecialchars($doctor['jadwal']) : '-' ?></td>
                                            <td>
                                                <span class="badge <?= $doctor['status'] === 'aktif' ? 'bg-success' : 'bg-danger' ?>">
                                                    <?= ucfirst(str_replace('_', ' ', $doctor['status'])) ?>
                                            </span>
                                        </td>
                                        <td>
                                                <button type="button" 
                                                        class="btn btn-info btn-sm text-white"
                                                        onclick="editDoctor(<?= $doctor['id'] ?>)">
                                                    <i class="fas fa-edit me-1"></i>Edit
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-3">
                                            <img src="assets/images/no-data.png" alt="No Data" class="img-fluid mb-2" style="max-width: 200px;">
                                            <p class="text-muted mb-0">Belum ada data dokter</p>
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

<!-- Modal Edit Jadwal -->
<div class="modal fade" id="doctorModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="doctorModalTitle">Edit Jadwal Dokter</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="doctorForm">
                    <input type="hidden" id="doctorId" name="id">
                    <div class="mb-3">
                        <label class="form-label">Nama</label>
                        <input type="text" class="form-control" id="doctorName" name="nama" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Spesialisasi</label>
                        <input type="text" class="form-control" id="doctorSpecialization" name="spesialisasi" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Hari Praktik</label>
                        <select class="form-select" id="doctorDays" name="hari" multiple>
                            <option value="Senin">Senin</option>
                            <option value="Selasa">Selasa</option>
                            <option value="Rabu">Rabu</option>
                            <option value="Kamis">Kamis</option>
                            <option value="Jumat">Jumat</option>
                            <option value="Sabtu">Sabtu</option>
                            <option value="Minggu">Minggu</option>
                        </select>
                        <div class="form-text">Tekan Ctrl (Windows) atau Command (Mac) untuk memilih beberapa hari</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jadwal</label>
                        <input type="text" class="form-control" id="doctorSchedule" name="jadwal" 
                               placeholder="Contoh: 08:00-16:00">
                        <div class="form-text">Format: JJ:MM-JJ:MM (24 jam)</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" id="doctorStatus" name="status" required>
                            <option value="aktif">Aktif</option>
                            <option value="tidak_aktif">Tidak Aktif</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="saveDoctor()">Simpan</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#doctorsTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
        },
        columnDefs: [
            {
                targets: [0, -1],
                orderable: false
            }
        ],
        order: [[1, 'asc']] // Urutkan berdasarkan nama
    });
});

function showAddModal() {
    window.location.href = 'index.php?page=admin/add_doctor';
}

function editDoctor(id) {
    fetch(`index.php?page=admin/get_doctor&id=${id}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            $('#doctorModalTitle').text('Edit Jadwal Dokter');
            $('#doctorId').val(data.doctor.id);
            $('#doctorName').val(data.doctor.nama);
            $('#doctorSpecialization').val(data.doctor.spesialisasi);
            
            // Handle hari (multiple select)
            if (data.doctor.hari) {
                const selectedDays = data.doctor.hari.split(',').map(day => day.trim());
                $('#doctorDays').val(selectedDays);
            } else {
                $('#doctorDays').val([]);
            }
            
            $('#doctorSchedule').val(data.doctor.jadwal);
            $('#doctorStatus').val(data.doctor.status);
            
            new bootstrap.Modal(document.getElementById('doctorModal')).show();
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

function saveDoctor() {
    // Validasi input sebelum mengirim
    const form = document.getElementById('doctorForm');
    const formData = new FormData(form);
    
    // Validasi hari
    const selectedDays = Array.from($('#doctorDays').val() || []);
    if (selectedDays.length === 0) {
        Swal.fire({
            icon: 'error',
            title: 'Validasi Gagal',
            text: 'Pilih minimal satu hari praktik'
        });
        return;
    }
    
    // Format hari dengan koma dan spasi
    formData.set('hari', selectedDays.join(', '));
    
    // Debug: Log data yang akan dikirim
    console.log('Data yang akan dikirim:');
    for (let pair of formData.entries()) {
        console.log(pair[0] + ': ' + pair[1]);
    }
    
    // Kirim data ke server
    fetch('index.php?page=admin/update_doctor_schedule', {
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
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: error.message || 'Terjadi kesalahan saat memperbarui jadwal'
        });
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

#doctorsTable {
    width: 100% !important;
}

#doctorsTable td {
    vertical-align: middle;
}

#doctorsTable th {
    background-color: #f8f9fa;
    font-weight: 600;
}

.form-text {
    font-size: 0.875rem;
    color: #6c757d;
}

.dataTables_wrapper .dataTables_length,
.dataTables_wrapper .dataTables_filter {
    margin-bottom: 1rem;
}

.dataTables_wrapper .dataTables_info,
.dataTables_wrapper .dataTables_paginate {
    margin-top: 1rem;
}

/* Style for multiple select */
select[multiple] {
    height: auto;
    min-height: 100px;
}
</style> 