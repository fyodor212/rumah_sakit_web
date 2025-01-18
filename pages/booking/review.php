<?php
require 'config/database.php';

if (!isLoggedIn()) {
    header('Location: index.php?page=login');
    exit;
}

$booking_id = $_GET['booking_id'] ?? null;

try {
    // Verify booking belongs to user
    $query = "SELECT b.*, d.nama as nama_dokter, l.nama as nama_layanan
              FROM booking b
              JOIN dokter d ON b.dokter_id = d.id
              JOIN layanan l ON b.layanan_id = l.id
              JOIN pasien p ON b.pasien_id = p.id
              WHERE b.id = ? AND p.user_id = ? AND b.status = 'completed'";
    $stmt = $db->prepare($query);
    $stmt->execute([$booking_id, $_SESSION['user_id']]);
    $booking = $stmt->fetch();

    if (!$booking) {
        throw new Exception("Booking tidak ditemukan atau tidak dapat direview");
    }

    // Handle review submission
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $rating = $_POST['rating'];
        $komentar = $_POST['komentar'];

        $query = "INSERT INTO review (booking_id, rating, komentar) VALUES (?, ?, ?)";
        $stmt = $db->prepare($query);
        
        if ($stmt->execute([$booking_id, $rating, $komentar])) {
            $_SESSION['success'] = "Review berhasil dikirim!";
            header('Location: index.php?page=riwayat');
            exit;
        } else {
            throw new Exception("Gagal menyimpan review");
        }
    }
} catch(Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header('Location: index.php?page=riwayat');
    exit;
}
?>

<main class="container">
    <div class="review-form-container">
        <h2>Beri Review</h2>
        
        <div class="booking-details card">
            <h3>Detail Kunjungan</h3>
            <p><strong>Dokter:</strong> <?= htmlspecialchars($booking['nama_dokter']) ?></p>
            <p><strong>Layanan:</strong> <?= htmlspecialchars($booking['nama_layanan']) ?></p>
            <p><strong>Tanggal:</strong> <?= date('d M Y', strtotime($booking['tanggal'])) ?></p>
        </div>

        <div class="card">
            <form method="POST" class="review-form">
                <div class="form-group">
                    <label>Rating</label>
                    <div class="rating">
                        <?php for($i = 5; $i >= 1; $i--): ?>
                            <input type="radio" name="rating" value="<?= $i ?>" id="star<?= $i ?>" required>
                            <label for="star<?= $i ?>"><i class="fas fa-star"></i></label>
                        <?php endfor; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label>Komentar</label>
                    <textarea name="komentar" class="form-control" rows="4" required></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Kirim Review</button>
            </form>
        </div>
    </div>
</main> 