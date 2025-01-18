<?php
require_once 'config/database.php';
require_once 'config/functions.php';

// Cek apakah user adalah admin
if (!isAdmin()) {
    header('Location: index.php?page=auth/login');
    exit;
}

// Ambil ID booking dari URL
$bookingId = $_GET['id'] ?? null;
if (!$bookingId) {
    header('Location: index.php?page=admin/manage_booking');
    exit;
}

// Ambil data booking
try {
    $query = "SELECT b.*, p.nama as nama_pasien, p.no_rm, p.no_hp, p.alamat,
              d.nama as nama_dokter, d.spesialisasi, l.nama as nama_layanan, l.harga 
              FROM booking b 
              JOIN pasien p ON b.pasien_id = p.id 
              JOIN dokter d ON b.dokter_id = d.id 
              JOIN layanan l ON b.layanan_id = l.id 
              WHERE b.id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$bookingId]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$booking) {
        header('Location: index.php?page=admin/manage_booking');
        exit;
    }
} catch (PDOException $e) {
    $error = "Error: " . $e->getMessage();
}
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Detail Booking</h5>
                    <a href="?page=admin/manage_booking" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left me-2"></i>Kembali
                    </a>
                </div>
                <div class="card-body">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger">
                            <?= $error ?>
                        </div>
                    <?php endif; ?>

                    <div class="booking-details">
                        <!-- Status Badge -->
                        <div class="text-center mb-4">
                            <span class="badge bg-<?= getStatusColor($booking['status']) ?> status-badge">
                                <?= ucfirst($booking['status']) ?>
                            </span>
                        </div>

                        <!-- Informasi Booking -->
                        <div class="detail-section">
                            <h6 class="section-title">Informasi Booking</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Tanggal:</strong> <?= date('d/m/Y', strtotime($booking['tanggal'])) ?></p>
                                    <p><strong>Jam:</strong> <?= date('H:i', strtotime($booking['jam'])) ?></p>
                                    <p><strong>Layanan:</strong> <?= htmlspecialchars($booking['nama_layanan']) ?></p>
                                    <p><strong>Harga:</strong> Rp <?= number_format($booking['harga'], 0, ',', '.') ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>No. Booking:</strong> #<?= str_pad($booking['id'], 5, '0', STR_PAD_LEFT) ?></p>
                                    <p><strong>Dibuat:</strong> <?= date('d/m/Y H:i', strtotime($booking['created_at'])) ?></p>
                                    <p><strong>Catatan:</strong> <?= htmlspecialchars($booking['catatan'] ?? '-') ?></p>
                                </div>
                            </div>
                        </div>

                        <!-- Informasi Pasien -->
                        <div class="detail-section">
                            <h6 class="section-title">Informasi Pasien</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>No. RM:</strong> <?= htmlspecialchars($booking['no_rm']) ?></p>
                                    <p><strong>Nama:</strong> <?= htmlspecialchars($booking['nama_pasien']) ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>No. HP:</strong> <?= htmlspecialchars($booking['no_hp'] ?? '-') ?></p>
                                    <p><strong>Alamat:</strong> <?= htmlspecialchars($booking['alamat'] ?? '-') ?></p>
                                </div>
                            </div>
                        </div>

                        <!-- Informasi Dokter -->
                        <div class="detail-section">
                            <h6 class="section-title">Informasi Dokter</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Nama:</strong> <?= htmlspecialchars($booking['nama_dokter']) ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Spesialisasi:</strong> <?= htmlspecialchars($booking['spesialisasi']) ?></p>
                                </div>
                            </div>
                        </div>

                        <!-- Tombol Aksi -->
                        <div class="action-buttons text-center mt-4">
                            <button type="button" class="btn btn-warning me-2" 
                                    onclick="updateStatus(<?= $booking['id'] ?>)">
                                <i class="fas fa-edit me-2"></i>Update Status
                            </button>
                            <button type="button" class="btn btn-info" onclick="printBooking()">
                                <i class="fas fa-print me-2"></i>Cetak
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Update Status -->
<div class="modal fade" id="updateStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Status Booking</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="?page=admin/manage_booking">
                <div class="modal-body">
                    <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                    <input type="hidden" name="update_status" value="1">
                    
                    <div class="mb-3">
                        <label for="newStatus" class="form-label">Status Baru</label>
                        <select class="form-select" id="newStatus" name="new_status" required>
                            <option value="pending" <?= $booking['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="confirmed" <?= $booking['status'] == 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                            <option value="completed" <?= $booking['status'] == 'completed' ? 'selected' : '' ?>>Completed</option>
                            <option value="cancelled" <?= $booking['status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.card {
    border: none;
    box-shadow: 0 0 15px rgba(0,0,0,.05);
}

.card-header {
    background-color: #fff;
    border-bottom: 1px solid #eee;
    padding: 15px 20px;
}

.status-badge {
    font-size: 1rem;
    padding: 8px 16px;
}

.detail-section {
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #eee;
}

.section-title {
    color: #333;
    margin-bottom: 1rem;
    font-weight: 600;
}

.detail-section p {
    margin-bottom: 0.5rem;
}

.detail-section strong {
    color: #555;
}

.action-buttons .btn {
    padding: 0.5rem 1.5rem;
}

@media (max-width: 768px) {
    .card-header {
        flex-direction: column;
        gap: 1rem;
    }
    
    .card-header .btn {
        width: 100%;
    }
    
    .action-buttons .btn {
        width: 100%;
        margin-bottom: 0.5rem;
    }
}
</style>

<script>
function updateStatus(bookingId) {
    Swal.fire({
        title: 'Update Status Booking',
        text: 'Pilih status baru untuk booking ini',
        icon: 'question',
        input: 'select',
        inputOptions: {
            'pending': 'Pending',
            'confirmed': 'Dikonfirmasi',
            'completed': 'Selesai',
            'cancelled': 'Dibatalkan'
        },
        showCancelButton: true,
        confirmButtonText: 'Update',
        cancelButtonText: 'Batal',
        reverseButtons: true,
        inputValidator: (value) => {
            if (!value) {
                return 'Pilih status baru!';
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Kirim request AJAX untuk update status
            fetch('index.php?page=admin/handle_booking_status', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `booking_id=${bookingId}&status=${result.value}`
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
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    throw new Error(data.message || 'Terjadi kesalahan saat mengupdate status');
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

function printBooking() {
    window.print();
}
</script> 