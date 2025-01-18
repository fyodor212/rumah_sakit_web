<?php
require_once 'config/database.php';
require_once 'config/functions.php';

// Cek akses admin
requireAdmin();

try {
    // Query untuk mengambil semua janji temu
    $query = "SELECT b.*, 
              p.nama as nama_pasien,
              d.nama as nama_dokter,
              l.nama as nama_layanan,
              l.harga as harga_layanan
              FROM booking b
              JOIN pasien p ON b.pasien_id = p.id
              JOIN dokter d ON b.dokter_id = d.id
              JOIN layanan l ON b.layanan_id = l.id
              ORDER BY b.tanggal DESC, b.created_at DESC";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log($e->getMessage());
    $_SESSION['message'] = "Terjadi kesalahan saat mengambil data";
    $_SESSION['message_type'] = 'danger';
}
?>

<div class="container-fluid p-4">
    <!-- Page Title -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Manajemen Janji Temu</h2>
        <div>
            <button class="btn btn-outline-primary me-2" onclick="window.print()">
                <i class="fas fa-print me-2"></i>Cetak Laporan
            </button>
            <a href="index.php?page=admin/export_bookings" class="btn btn-primary">
                <i class="fas fa-download me-2"></i>Export Data
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?= $_SESSION['message_type'] ?> alert-dismissible fade show">
            <?= $_SESSION['message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php 
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
        ?>
    <?php endif; ?>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form class="row g-3" id="filterForm">
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select class="form-select" id="filterStatus">
                        <option value="">Semua Status</option>
                        <option value="pending">Pending</option>
                        <option value="confirmed">Dikonfirmasi</option>
                        <option value="completed">Selesai</option>
                        <option value="cancelled">Dibatalkan</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal</label>
                    <input type="date" class="form-control" id="filterDate">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Dokter</label>
                    <select class="form-select" id="filterDoctor">
                        <option value="">Semua Dokter</option>
                        <!-- Options will be populated by JS -->
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Layanan</label>
                    <select class="form-select" id="filterService">
                        <option value="">Semua Layanan</option>
                        <!-- Options will be populated by JS -->
                    </select>
                </div>
            </form>
        </div>
    </div>

    <!-- Bookings Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="bookingsTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tanggal</th>
                            <th>Jam</th>
                            <th>Pasien</th>
                            <th>Dokter</th>
                            <th>Layanan</th>
                            <th>Harga</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td>#<?= $booking['id'] ?></td>
                                <td><?= date('d/m/Y', strtotime($booking['tanggal'])) ?></td>
                                <td><?= date('H:i', strtotime($booking['jam'])) ?></td>
                                <td><?= htmlspecialchars($booking['nama_pasien']) ?></td>
                                <td><?= htmlspecialchars($booking['nama_dokter']) ?></td>
                                <td><?= htmlspecialchars($booking['nama_layanan']) ?></td>
                                <td>Rp <?= number_format($booking['harga_layanan'], 0, ',', '.') ?></td>
                                <td>
                                    <span class="badge bg-<?= getStatusBadgeClass($booking['status']) ?>">
                                        <?= ucfirst($booking['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="index.php?page=admin/booking_detail&id=<?= $booking['id'] ?>" 
                                           class="btn btn-sm btn-info text-white" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-sm btn-success" 
                                                onclick="updateStatus(<?= $booking['id'] ?>, 'confirmed')"
                                                title="Konfirmasi">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button type="button" 
                                                class="btn btn-sm btn-danger"
                                                onclick="updateStatus(<?= $booking['id'] ?>, 'cancelled')"
                                                title="Batalkan">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    border: none;
    border-radius: 10px;
    box-shadow: 0 0 15px rgba(0,0,0,.05);
}

.table > :not(caption) > * > * {
    padding: 1rem;
}

.badge {
    padding: 0.5rem 0.75rem;
    font-weight: 500;
}

.btn-group .btn {
    padding: 0.25rem 0.5rem;
}

.btn-group .btn i {
    width: 16px;
    text-align: center;
}

.form-label {
    font-weight: 500;
    color: #6c757d;
}

@media print {
    .btn, .alert {
        display: none;
    }
}
</style>

<script>
// Filter functionality
document.querySelectorAll('#filterForm select, #filterForm input').forEach(element => {
    element.addEventListener('change', filterTable);
});

function filterTable() {
    const status = document.getElementById('filterStatus').value.toLowerCase();
    const date = document.getElementById('filterDate').value;
    const doctor = document.getElementById('filterDoctor').value.toLowerCase();
    const service = document.getElementById('filterService').value.toLowerCase();

    document.querySelectorAll('#bookingsTable tbody tr').forEach(row => {
        let show = true;

        // Filter by status
        if (status && !row.querySelector('td:nth-child(8)').textContent.toLowerCase().includes(status)) {
            show = false;
        }

        // Filter by date
        if (date) {
            const rowDate = new Date(row.querySelector('td:nth-child(2)').textContent.split('/').reverse().join('-'));
            const filterDate = new Date(date);
            if (rowDate.toDateString() !== filterDate.toDateString()) {
                show = false;
            }
        }

        // Filter by doctor
        if (doctor && !row.querySelector('td:nth-child(5)').textContent.toLowerCase().includes(doctor)) {
            show = false;
        }

        // Filter by service
        if (service && !row.querySelector('td:nth-child(6)').textContent.toLowerCase().includes(service)) {
            show = false;
        }

        row.style.display = show ? '' : 'none';
    });
}

// Update booking status
function updateStatus(id, status) {
    Swal.fire({
        title: 'Konfirmasi',
        text: `Apakah Anda yakin ingin mengubah status janji temu ini menjadi ${status}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya',
        cancelButtonText: 'Tidak'
    }).then((result) => {
        if (result.isConfirmed) {
            // Send AJAX request to update status
            fetch('api/update_booking_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id: id,
                    status: status
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire(
                        'Berhasil!',
                        'Status janji temu telah diperbarui.',
                        'success'
                    ).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire(
                        'Gagal!',
                        'Terjadi kesalahan saat memperbarui status.',
                        'error'
                    );
                }
            });
        }
    });
}
</script> 