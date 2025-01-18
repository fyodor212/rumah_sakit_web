<?php
require_once 'config/database.php';
require_once 'config/functions.php';

// Cek akses admin
requireAdmin();

try {
    // Query untuk mengambil data booking dengan data pasien dan dokter
    $query = "SELECT b.*, 
                     p.nama as nama_pasien, 
                     p.no_hp,
                     d.nama as nama_dokter,
                     d.spesialisasi
              FROM booking b 
              LEFT JOIN pasien p ON b.pasien_id = p.id
              LEFT JOIN dokter d ON b.dokter_id = d.id 
              ORDER BY b.tanggal DESC, b.created_at DESC";
    
    // Debug query
    error_log("SQL Query: " . $query);
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Debug hasil query
    error_log("Jumlah data: " . count($bookings));
    if (!empty($bookings)) {
        error_log("Kolom yang tersedia: " . implode(", ", array_keys($bookings[0])));
        error_log("Data booking pertama: " . print_r($bookings[0], true));
    } else {
        error_log("Tidak ada data booking");
    }
    
} catch (PDOException $e) {
    error_log("Error fetching bookings: " . $e->getMessage());
    $_SESSION['message'] = "Terjadi kesalahan saat mengambil data booking.";
    $_SESSION['message_type'] = 'danger';
    $bookings = [];
}
?>

<div class="container-fluid p-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center bg-white p-3 rounded shadow-sm">
                <div>
                    <h4 class="mb-0">Kelola Data Booking</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 mt-2">
                            <li class="breadcrumb-item"><a href="index.php?page=admin/dashboard" class="text-decoration-none">Dashboard</a></li>
                            <li class="breadcrumb-item active">Kelola Booking</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?= $_SESSION['message_type'] ?> alert-dismissible fade show shadow-sm">
            <?= $_SESSION['message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php 
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
        ?>
    <?php endif; ?>

    <!-- Tabel Data -->
    <div class="card shadow">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="bookingsTable" class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="px-4 py-3" style="width: 50px">No</th>
                            <th class="px-4 py-3">Nama Pasien</th>
                            <th class="px-4 py-3">Dokter</th>
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-4 py-3">Jam</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-center" style="width: 120px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($bookings)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4">Tidak ada data booking</td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($bookings as $index => $booking): ?>
                                <tr>
                                    <td class="px-4"><?= $index + 1 ?></td>
                                    <td class="px-4">
                                        <div class="fw-semibold text-dark"><?= htmlspecialchars($booking['nama_pasien'] ?? '-') ?></div>
                                        <div class="small text-muted"><?= htmlspecialchars($booking['no_hp'] ?? '-') ?></div>
                                    </td>
                                    <td class="px-4"><?= htmlspecialchars($booking['nama_dokter'] ?? '-') ?></td>
                                    <td class="px-4"><?= isset($booking['tanggal']) ? date('d/m/Y', strtotime($booking['tanggal'])) : '-' ?></td>
                                    <td class="px-4"><?= htmlspecialchars($booking['jam'] ?? '-') ?></td>
                                    <td class="px-4 text-center">
                                        <?php
                                        $statusClass = [
                                            'pending' => 'warning',
                                            'confirmed' => 'success',
                                            'cancelled' => 'danger',
                                            'completed' => 'info'
                                        ];
                                        $statusText = [
                                            'pending' => 'Menunggu',
                                            'confirmed' => 'Dikonfirmasi',
                                            'cancelled' => 'Dibatalkan',
                                            'completed' => 'Selesai'
                                        ];
                                        $status = $booking['status'] ?? 'pending';
                                        ?>
                                        <span class="badge bg-<?= $statusClass[$status] ?> rounded-pill">
                                            <?= $statusText[$status] ?>
                                        </span>
                                    </td>
                                    <td class="px-4 text-center">
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-outline-primary view-booking" 
                                                    data-id="<?= $booking['id'] ?>"
                                                    title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <?php if (($booking['status'] ?? '') === 'pending'): ?>
                                                <button type="button" class="btn btn-sm btn-outline-success confirm-booking" 
                                                        data-id="<?= $booking['id'] ?>"
                                                        title="Konfirmasi">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger cancel-booking" 
                                                        data-id="<?= $booking['id'] ?>"
                                                        title="Batalkan">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail Booking -->
<div class="modal fade" id="viewBookingModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title"><i class="fas fa-info-circle me-2 text-primary"></i>Detail Booking</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="bookingDetails">
                <!-- Data akan diisi melalui AJAX -->
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Inisialisasi DataTable
    const table = $('#bookingsTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
        },
        pageLength: 10,
        order: [[3, 'desc'], [4, 'asc']],
        columnDefs: [
            { orderable: false, targets: [6] }
        ]
    });

    // Handle lihat detail
    $('.view-booking').click(function() {
        const id = $(this).data('id');
        
        // Debug
        console.log('Viewing booking details for ID:', id);
        
        // Tampilkan loading
        $('#bookingDetails').html('<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><div class="mt-2">Memuat data...</div></div>');
        $('#viewBookingModal').modal('show');

        // Ambil detail booking
        $.ajax({
            url: 'index.php',
            method: 'GET',
            data: { 
                page: 'admin/get_booking_details',
                id: id 
            },
            dataType: 'json',
            success: function(response) {
                console.log('Response received:', response);
                if (response.status === 'success') {
                    $('#bookingDetails').html(response.html);
                } else {
                    $('#bookingDetails').html('<div class="alert alert-danger">' + (response.message || 'Terjadi kesalahan saat memuat data. Silakan coba lagi.') + '</div>');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', {
                    status: status,
                    error: error,
                    response: xhr.responseText
                });
                
                let errorMessage = 'Terjadi kesalahan saat memuat data. Silakan coba lagi.';
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.message) {
                        errorMessage = response.message;
                    }
                } catch (e) {
                    console.error('Error parsing response:', e);
                }
                
                $('#bookingDetails').html('<div class="alert alert-danger">' + errorMessage + '</div>');
            }
        });
    });

    // Handle konfirmasi booking
    $('.confirm-booking').click(function() {
        const id = $(this).data('id');
        
        Swal.fire({
            title: 'Konfirmasi Booking',
            text: 'Yakin ingin mengkonfirmasi booking ini?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#28a745'
        }).then((result) => {
            if (result.isConfirmed) {
                // Tampilkan loading
                Swal.fire({
                    title: 'Memproses...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Kirim request
                $.post('index.php?page=admin/handle_booking_status', {
                    booking_id: id,
                    status: 'confirmed'
                }, function(response) {
                    if (response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: response.message
                        });
                    }
                });
            }
        });
    });

    // Handle batalkan booking
    $('.cancel-booking').click(function() {
        const id = $(this).data('id');
        
        Swal.fire({
            title: 'Batalkan Booking',
            text: 'Yakin ingin membatalkan booking ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya',
            cancelButtonText: 'Tidak',
            confirmButtonColor: '#dc3545'
        }).then((result) => {
            if (result.isConfirmed) {
                // Tampilkan loading
                Swal.fire({
                    title: 'Memproses...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Kirim request
                $.post('index.php?page=admin/handle_booking_status', {
                    booking_id: id,
                    status: 'cancelled'
                }, function(response) {
                    if (response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: response.message
                        });
                    }
                });
            }
        });
    });
});
</script>

<style>
.table {
    vertical-align: middle;
}

.table th {
    font-weight: 600;
    background-color: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
}

.table td {
    vertical-align: middle;
}

.btn-group {
    gap: 0.25rem;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

.badge {
    font-weight: 500;
    padding: 0.5em 1em;
}

.breadcrumb-item + .breadcrumb-item::before {
    color: #6c757d;
}

.breadcrumb-item a {
    color: #6c757d;
}

.breadcrumb-item.active {
    color: #344767;
}

.modal-content {
    border: none;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.small {
    font-size: 0.875rem;
}

.text-muted {
    color: #6c757d !important;
}

.fw-semibold {
    font-weight: 600;
}
</style> 