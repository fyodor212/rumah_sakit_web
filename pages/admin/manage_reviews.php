<?php
require_once 'config/database.php';
require_once 'config/functions.php';

// Cek akses admin
requireAdmin();

try {
    // Debug: Cek struktur tabel review
    $tableQuery = "DESCRIBE review";
    $tableStructure = $db->query($tableQuery)->fetchAll(PDO::FETCH_ASSOC);
    error_log("Review table structure: " . print_r($tableStructure, true));

    // Ambil semua review dengan JOIN yang benar melalui booking
    $query = "SELECT r.*, 
              b.id as booking_id,
              p.nama as nama_pasien, 
              d.nama as nama_dokter,
              r.komentar as review,
              r.rating,
              CASE 
                  WHEN r.rating >= 4 THEN 'approved'
                  ELSE 'pending'
              END as status
              FROM review r 
              JOIN booking b ON r.booking_id = b.id
              JOIN pasien p ON b.pasien_id = p.id 
              JOIN dokter d ON b.dokter_id = d.id 
              ORDER BY r.created_at DESC";
    $reviews = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);

    if (empty($reviews)) {
        $reviews = [];
    }
} catch (PDOException $e) {
    error_log("Error in manage_reviews.php: " . $e->getMessage());
    $error = "Terjadi kesalahan saat mengambil data review: " . $e->getMessage();
    $reviews = [];
}
?>

<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Kelola Review</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="index.php?page=admin/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item active">Review</li>
            </ol>
        </nav>
    </div>

    <?php if (isset($error)): ?>
    <div class="alert alert-danger">
        <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php if (empty($reviews)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-star text-muted mb-3" style="font-size: 4rem;"></i>
                    <h6 class="text-muted">Belum ada review</h6>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover datatable">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Pasien</th>
                                <th>Dokter</th>
                                <th>Rating</th>
                                <th>Review</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reviews as $index => $review): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= htmlspecialchars($review['nama_pasien']) ?></td>
                                <td><?= htmlspecialchars($review['nama_dokter']) ?></td>
                                <td>
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star <?= $i <= $review['rating'] ? 'text-warning' : 'text-muted' ?>"></i>
                                    <?php endfor; ?>
                                </td>
                                <td>
                                    <?php 
                                    $review_text = htmlspecialchars($review['komentar']);
                                    echo strlen($review_text) > 50 ? substr($review_text, 0, 50) . '...' : $review_text;
                                    ?>
                                    <button type="button" class="btn btn-link btn-sm p-0 ms-2" 
                                            onclick="viewReview(<?= htmlspecialchars(json_encode($review)) ?>)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($review['created_at'])) ?></td>
                                <td>
                                    <span class="badge bg-<?= $review['status'] == 'approved' ? 'success' : 'warning' ?>">
                                        <?= ucfirst($review['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($review['status'] != 'approved'): ?>
                                    <button type="button" class="btn btn-sm btn-success me-1" 
                                            onclick="approveReview(<?= $review['id'] ?>)">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <?php endif; ?>
                                    <button type="button" class="btn btn-sm btn-danger" 
                                            onclick="deleteReview(<?= $review['id'] ?>, '<?= htmlspecialchars($review['nama_pasien']) ?>')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal View Review -->
<div class="modal fade" id="viewReviewModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Review</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="fw-bold">Pasien:</label>
                    <div id="modalPasien"></div>
                </div>
                <div class="mb-3">
                    <label class="fw-bold">Dokter:</label>
                    <div id="modalDokter"></div>
                </div>
                <div class="mb-3">
                    <label class="fw-bold">Rating:</label>
                    <div id="modalRating"></div>
                </div>
                <div class="mb-3">
                    <label class="fw-bold">Review:</label>
                    <div id="modalReview"></div>
                </div>
                <div>
                    <label class="fw-bold">Dikirim pada:</label>
                    <div id="modalCreatedAt"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
function viewReview(review) {
    document.getElementById('modalPasien').textContent = review.nama_pasien;
    document.getElementById('modalDokter').textContent = review.nama_dokter;
    
    // Generate star rating
    let ratingHtml = '';
    for (let i = 1; i <= 5; i++) {
        ratingHtml += `<i class="fas fa-star ${i <= review.rating ? 'text-warning' : 'text-muted'}"></i>`;
    }
    document.getElementById('modalRating').innerHTML = ratingHtml;
    
    document.getElementById('modalReview').textContent = review.komentar;
    document.getElementById('modalCreatedAt').textContent = new Date(review.created_at).toLocaleString('id-ID');
    
    new bootstrap.Modal(document.getElementById('viewReviewModal')).show();
}

function approveReview(reviewId) {
    Swal.fire({
        title: 'Setujui Review?',
        text: 'Review yang disetujui akan ditampilkan di halaman publik',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('index.php?page=admin/handle_approve_review', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `review_id=${reviewId}`
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

function deleteReview(reviewId, namaPasien) {
    Swal.fire({
        title: 'Hapus Review',
        html: `Yakin ingin menghapus review dari <strong>${namaPasien}</strong>?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#dc3545',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('index.php?page=admin/handle_delete_review', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `review_id=${reviewId}`
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