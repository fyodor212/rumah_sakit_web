<?php
if (!isAdmin()) {
    header('Location: index.php');
    exit;
}

// Ambil ID dokter dari URL
$doctor_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

try {
    // Ambil data dokter
    $query = "SELECT * FROM dokter WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$doctor_id]);
    $doctor = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$doctor) {
        $_SESSION['error'] = "Data dokter tidak ditemukan";
        header('Location: index.php?page=admin/doctors');
        exit;
    }

    // Konversi string hari menjadi array
    $hari_praktik = array_map('trim', explode(',', $doctor['hari'] ?? ''));
    
    // Pisahkan jam mulai dan selesai
    $jadwal = explode('-', $doctor['jadwal'] ?? '');
    $jam_mulai = trim($jadwal[0] ?? '');
    $jam_selesai = trim($jadwal[1] ?? '');

} catch (PDOException $e) {
    error_log($e->getMessage());
    $_SESSION['error'] = "Terjadi kesalahan saat mengambil data";
    header('Location: index.php?page=admin/doctors');
    exit;
}

// Debug: Log data dokter
error_log("Data dokter: " . print_r($doctor, true));
error_log("Hari praktik: " . print_r($hari_praktik, true));
error_log("Jadwal: " . print_r(['mulai' => $jam_mulai, 'selesai' => $jam_selesai], true));
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-user-md me-2"></i>Edit Dokter
                    </h5>
                    <a href="?page=admin/manage_doctors" class="btn btn-light btn-sm">
                        <i class="fas fa-arrow-left me-1"></i>Kembali
                    </a>
                </div>
                <div class="card-body">
                    <form id="editDoctorForm" method="POST" class="needs-validation" novalidate>
                        <input type="hidden" name="doctor_id" value="<?= htmlspecialchars($doctor_id) ?>">
                        
                        <!-- Nama Dokter -->
                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama" name="nama" 
                                   value="<?= htmlspecialchars($doctor['nama']) ?>" 
                                   required maxlength="100">
                            <div class="invalid-feedback">
                                Nama dokter harus diisi
                            </div>
                        </div>

                        <!-- Spesialisasi -->
                        <div class="mb-3">
                            <label for="spesialisasi" class="form-label">Spesialisasi <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="spesialisasi" name="spesialisasi" 
                                   value="<?= htmlspecialchars($doctor['spesialisasi']) ?>" 
                                   required maxlength="100">
                            <div class="invalid-feedback">
                                Spesialisasi harus diisi
                            </div>
                        </div>

                        <!-- Hari Praktik -->
                        <div class="mb-3">
                            <label for="hari" class="form-label">Hari Praktik <span class="text-danger">*</span></label>
                            <select class="form-select" id="hari" name="hari[]" multiple required>
                                <option value="Senin" <?= in_array('Senin', $hari_praktik) ? 'selected' : '' ?>>Senin</option>
                                <option value="Selasa" <?= in_array('Selasa', $hari_praktik) ? 'selected' : '' ?>>Selasa</option>
                                <option value="Rabu" <?= in_array('Rabu', $hari_praktik) ? 'selected' : '' ?>>Rabu</option>
                                <option value="Kamis" <?= in_array('Kamis', $hari_praktik) ? 'selected' : '' ?>>Kamis</option>
                                <option value="Jumat" <?= in_array('Jumat', $hari_praktik) ? 'selected' : '' ?>>Jumat</option>
                                <option value="Sabtu" <?= in_array('Sabtu', $hari_praktik) ? 'selected' : '' ?>>Sabtu</option>
                            </select>
                            <div class="invalid-feedback">
                                Pilih minimal satu hari praktik
                            </div>
                            <small class="text-muted">Tekan Ctrl untuk memilih lebih dari satu hari</small>
                        </div>

                        <!-- Jadwal -->
                        <div class="mb-3">
                            <label class="form-label">Jam Praktik <span class="text-danger">*</span></label>
                            <div class="row">
                                <div class="col-6">
                                    <input type="time" class="form-control" name="jam_mulai" id="jam_mulai" 
                                           value="<?= htmlspecialchars($jam_mulai) ?>" required>
                                    <small class="text-muted">Mulai</small>
                                </div>
                                <div class="col-6">
                                    <input type="time" class="form-control" name="jam_selesai" id="jam_selesai" 
                                           value="<?= htmlspecialchars($jam_selesai) ?>" required>
                                    <small class="text-muted">Selesai</small>
                                </div>
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="mb-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select" name="status" id="status" required>
                                <option value="aktif" <?= $doctor['status'] === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                                <option value="tidak aktif" <?= $doctor['status'] === 'tidak aktif' ? 'selected' : '' ?>>Tidak Aktif</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Simpan Perubahan
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('editDoctorForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    // Validasi form
    if (!this.checkValidity()) {
        e.stopPropagation();
        this.classList.add('was-validated');
        return;
    }

    // Tampilkan loading
    const loadingAlert = Swal.fire({
        title: 'Memproses...',
        text: 'Mohon tunggu sebentar',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    try {
        // Kirim data
        const response = await fetch('index.php?page=admin/handle_edit_doctor', {
            method: 'POST',
            body: new FormData(this)
        });

        const data = await response.json();
        
        // Tutup loading
        await loadingAlert.close();
        
        if (data.status === 'success') {
            // Tampilkan pesan sukses
            await Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: data.message,
                timer: 1500,
                showConfirmButton: false
            });
            
            // Redirect ke halaman manage_doctors
            window.location.href = '?page=admin/manage_doctors';
        } else {
            // Tampilkan pesan error dari server
            await Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: data.message || 'Terjadi kesalahan saat menyimpan data',
                confirmButtonColor: '#d33'
            });
        }
    } catch (error) {
        // Tutup loading jika masih terbuka
        if (loadingAlert) {
            await loadingAlert.close();
        }
        
        // Tampilkan pesan error
        console.error('Error:', error);
        await Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: 'Terjadi kesalahan saat menghubungi server',
            confirmButtonColor: '#d33'
        });
    }
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