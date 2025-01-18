<?php
// Cek session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek role admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <div class="row align-items-center">
                        <div class="col">
                            <h6 class="mb-0">Export Data</h6>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <!-- Export Pembayaran -->
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body text-center p-4">
                                    <div class="mb-3">
                                        <i class="fas fa-file-invoice-dollar text-primary" style="font-size: 3rem;"></i>
                                    </div>
                                    <h5 class="card-title mb-3">Data Pembayaran</h5>
                                    <p class="card-text text-muted mb-4">
                                        Export data pembayaran dalam format Excel (.xlsx)
                                    </p>
                                    <form action="index.php?page=admin/handle_export" method="POST">
                                        <input type="hidden" name="type" value="payments">
                                        <div class="row g-3">
                                            <div class="col-6">
                                                <div class="form-group">
                                                    <label class="form-label text-sm">Tanggal Mulai</label>
                                                    <input type="date" class="form-control" name="start_date" required>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="form-group">
                                                    <label class="form-label text-sm">Tanggal Akhir</label>
                                                    <input type="date" class="form-control" name="end_date" required>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-primary mt-3 w-100">
                                            <i class="fas fa-download me-2"></i>Export Data
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Export Booking -->
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body text-center p-4">
                                    <div class="mb-3">
                                        <i class="fas fa-calendar-check text-success" style="font-size: 3rem;"></i>
                                    </div>
                                    <h5 class="card-title mb-3">Data Booking</h5>
                                    <p class="card-text text-muted mb-4">
                                        Export data booking/janji temu dalam format Excel (.xlsx)
                                    </p>
                                    <form action="index.php?page=admin/handle_export" method="POST">
                                        <input type="hidden" name="type" value="bookings">
                                        <div class="row g-3">
                                            <div class="col-6">
                                                <div class="form-group">
                                                    <label class="form-label text-sm">Tanggal Mulai</label>
                                                    <input type="date" class="form-control" name="start_date" required>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="form-group">
                                                    <label class="form-label text-sm">Tanggal Akhir</label>
                                                    <input type="date" class="form-control" name="end_date" required>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-success mt-3 w-100">
                                            <i class="fas fa-download me-2"></i>Export Data
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Export Pasien -->
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body text-center p-4">
                                    <div class="mb-3">
                                        <i class="fas fa-users text-info" style="font-size: 3rem;"></i>
                                    </div>
                                    <h5 class="card-title mb-3">Data Pasien</h5>
                                    <p class="card-text text-muted mb-4">
                                        Export data pasien dalam format Excel (.xlsx)
                                    </p>
                                    <form action="index.php?page=admin/handle_export" method="POST">
                                        <input type="hidden" name="type" value="patients">
                                        <div class="form-group mb-3">
                                            <label class="form-label text-sm">Status Pasien</label>
                                            <select class="form-select" name="status">
                                                <option value="all">Semua Status</option>
                                                <option value="active">Aktif</option>
                                                <option value="inactive">Tidak Aktif</option>
                                            </select>
                                        </div>
                                        <button type="submit" class="btn btn-info mt-3 w-100">
                                            <i class="fas fa-download me-2"></i>Export Data
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    transition: transform 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-5px);
}

.text-sm {
    font-size: 0.875rem;
}

.form-control:focus,
.form-select:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.btn {
    font-weight: 500;
    padding: 0.5rem 1rem;
}

.btn-primary {
    background: linear-gradient(45deg, #0d6efd, #0a58ca);
    border: none;
}

.btn-success {
    background: linear-gradient(45deg, #198754, #157347);
    border: none;
}

.btn-info {
    background: linear-gradient(45deg, #0dcaf0, #0aa2c0);
    border: none;
    color: #fff;
}

.text-primary {
    color: #0d6efd !important;
}

.text-success {
    color: #198754 !important;
}

.text-info {
    color: #0dcaf0 !important;
}

@media (max-width: 768px) {
    .card-body {
        padding: 1.5rem !important;
    }
}
</style>

<script>
// Set max date untuk input tanggal
const today = new Date().toISOString().split('T')[0];
document.querySelectorAll('input[type="date"]').forEach(input => {
    input.max = today;
});

// Validasi tanggal
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function(e) {
        const startDate = this.querySelector('input[name="start_date"]');
        const endDate = this.querySelector('input[name="end_date"]');
        
        if (startDate && endDate) {
            if (startDate.value > endDate.value) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Tanggal mulai tidak boleh lebih besar dari tanggal akhir',
                    confirmButtonText: 'OK'
                });
            }
        }
    });
});
</script> 