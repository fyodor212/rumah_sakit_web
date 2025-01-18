<?php
require_once 'config/database.php';
require_once 'config/functions.php';

// Cek apakah user adalah admin
if (!isAdmin()) {
    header('Location: index.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $_POST['nama'] ?? '';
    $spesialisasi = $_POST['spesialisasi'] ?? '';
    $hari = implode(',', $_POST['hari'] ?? []); // Simpan hari sebagai string
    $jam_mulai = $_POST['jam_mulai'];
    $jam_selesai = $_POST['jam_selesai'];
    $status = $_POST['status'] ?? 'aktif';

    $errors = [];

    // Validasi input
    if (empty($nama)) $errors['nama'] = "Nama harus diisi";
    if (empty($spesialisasi)) $errors['spesialisasi'] = "Spesialisasi harus diisi";
    if (empty($_POST['hari'])) $errors['hari'] = "Hari praktik harus dipilih";
    if (empty($jam_mulai) || empty($jam_selesai)) $errors['jadwal'] = "Jadwal harus diisi";

    // Validasi jam
    if (strtotime($jam_mulai) >= strtotime($jam_selesai)) {
        $errors['jadwal'] = "Jam selesai harus lebih besar dari jam mulai";
    }
    
    // Validasi rentang waktu minimal 1 jam
    $diff = (strtotime($jam_selesai) - strtotime($jam_mulai)) / 3600;
    if ($diff < 1) {
        $errors['jadwal'] = "Durasi praktik minimal 1 jam";
    }

    if (empty($errors)) {
        try {
            $query = "INSERT INTO dokter (nama, spesialisasi, hari, jadwal, status, created_at) 
                      VALUES (?, ?, ?, ?, ?, NOW())";
            $stmt = $db->prepare($query);
            $stmt->execute([$nama, $spesialisasi, $hari, $jam_mulai . '-' . $jam_selesai, $status]);

            $_SESSION['success'] = "Dokter berhasil ditambahkan!";
            header('Location: index.php?page=admin/manage_doctor');
            exit;
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-user-md me-2"></i>Tambah Dokter Baru
                    </h5>
                    <a href="?page=admin/doctors" class="btn btn-light btn-sm">
                        <i class="fas fa-arrow-left me-1"></i>Kembali
                    </a>
                </div>
                <div class="card-body">
                    <form id="addDoctorForm" method="POST" class="needs-validation" novalidate>
                        <!-- Nama Dokter -->
                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama" name="nama" required maxlength="100">
                            <div class="invalid-feedback">
                                Nama dokter harus diisi
                            </div>
                        </div>

                        <!-- Spesialisasi -->
                        <div class="mb-3">
                            <label for="spesialisasi" class="form-label">Spesialisasi <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="spesialisasi" name="spesialisasi" required maxlength="100">
                            <div class="invalid-feedback">
                                Spesialisasi harus diisi
                            </div>
                        </div>

                        <!-- Hari Praktik -->
                        <div class="mb-3">
                            <label for="hari" class="form-label">Hari Praktik</label>
                            <select class="form-select" id="hari" name="hari[]" multiple>
                                <option value="Senin">Senin</option>
                                <option value="Selasa">Selasa</option>
                                <option value="Rabu">Rabu</option>
                                <option value="Kamis">Kamis</option>
                                <option value="Jumat">Jumat</option>
                                <option value="Sabtu">Sabtu</option>
                            </select>
                            <small class="text-muted">Tekan Ctrl untuk memilih lebih dari satu hari</small>
                        </div>

                        <!-- Jadwal -->
                        <div class="mb-3">
                            <label class="form-label">Jam Praktik</label>
                            <div class="row">
                                <div class="col-6">
                                    <input type="time" class="form-control" name="jam_mulai" id="jam_mulai">
                                    <small class="text-muted">Mulai</small>
                                </div>
                                <div class="col-6">
                                    <input type="time" class="form-control" name="jam_selesai" id="jam_selesai">
                                    <small class="text-muted">Selesai</small>
                                </div>
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status" id="status">
                                <option value="aktif">Aktif</option>
                                <option value="tidak aktif">Tidak Aktif</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Simpan
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('addDoctorForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Validasi form
    if (!this.checkValidity()) {
        e.stopPropagation();
        this.classList.add('was-validated');
        return;
    }
    
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
    
    fetch('index.php?page=admin/handle_add_doctor', {
        method: 'POST',
        body: new FormData(this),
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: data.message,
                showConfirmButton: true,
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '?page=admin/doctors';
                }
            });
        } else {
            throw new Error(data.message);
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: error.message || 'Terjadi kesalahan saat menambahkan dokter',
            confirmButtonColor: '#d33'
        });
    });
});

// Validasi jam praktik
document.getElementById('jam_mulai').addEventListener('change', validateJadwal);
document.getElementById('jam_selesai').addEventListener('change', validateJadwal);

function validateJadwal() {
    const jamMulai = document.getElementById('jam_mulai').value;
    const jamSelesai = document.getElementById('jam_selesai').value;
    
    if (jamMulai && jamSelesai) {
        if (jamMulai >= jamSelesai) {
            Swal.fire({
                icon: 'error',
                title: 'Jadwal Tidak Valid',
                text: 'Jam selesai harus lebih besar dari jam mulai'
            });
            document.getElementById('jam_selesai').value = '';
        }
    }
}
</script>

<style>
.form-label {
    font-weight: 500;
}

.form-select[multiple] {
    height: 120px;
}

.btn-light {
    background-color: #fff;
    border-color: #dee2e6;
}

.btn-light:hover {
    background-color: #f8f9fa;
    border-color: #dee2e6;
}

.text-danger {
    color: #dc3545;
}

.invalid-feedback {
    font-size: 80%;
}
</style> 