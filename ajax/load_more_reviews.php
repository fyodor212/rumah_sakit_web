<?php
require_once '../config/database.php';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

try {
    $query = "SELECT 
                r.rating,
                r.komentar,
                r.created_at as review_date,
                d.nama as nama_dokter,
                d.spesialisasi,
                l.nama as nama_layanan,
                p.nama as nama_pasien
              FROM review r
              JOIN booking b ON r.booking_id = b.id
              JOIN dokter d ON b.dokter_id = d.id
              JOIN layanan l ON b.layanan_id = l.id
              JOIN pasien p ON b.pasien_id = p.id
              ORDER BY r.created_at DESC
              LIMIT ? OFFSET ?";
              
    $stmt = $db->prepare($query);
    $stmt->execute([$limit, $offset]);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach($reviews as $review) {
        ?>
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h6 class="card-subtitle text-muted mb-1">
                                <?= htmlspecialchars($review['nama_pasien']) ?>
                            </h6>
                            <div class="text-warning">
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star <?= $i <= $review['rating'] ? 'text-warning' : 'text-muted' ?>"></i>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <small class="text-muted">
                            <?= date('d/m/Y', strtotime($review['review_date'])) ?>
                        </small>
                    </div>

                    <p class="card-text">
                        <em>"<?= htmlspecialchars($review['komentar']) ?>"</em>
                    </p>

                    <div class="mt-3 pt-3 border-top">
                        <small class="text-muted">
                            <i class="fas fa-user-md me-1"></i>
                            Dokter: <?= htmlspecialchars($review['nama_dokter']) ?>
                            (<?= $review['spesialisasi'] ?>)
                        </small>
                        <br>
                        <small class="text-muted">
                            <i class="fas fa-stethoscope me-1"></i>
                            Layanan: <?= htmlspecialchars($review['nama_layanan']) ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 