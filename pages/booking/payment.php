<?php
require 'config/database.php';

if (!isLoggedIn()) {
    header('Location: index.php?page=login');
    exit;
}

$booking_id = $_GET['booking_id'] ?? null;

try {
    // Get booking details
    $query = "SELECT b.*, d.nama as nama_dokter, l.nama as nama_layanan, l.harga,
              p.nama as nama_pasien, p.no_rm
              FROM booking b
              JOIN dokter d ON b.dokter_id = d.id
              JOIN layanan l ON b.layanan_id = l.id
              JOIN pasien p ON b.pasien_id = p.id
              WHERE b.id = ? AND p.user_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$booking_id, $_SESSION['user_id']]);
    $booking = $stmt->fetch();

    if (!$booking) {
        throw new Exception("Booking tidak ditemukan");
    }

    // Handle payment submission
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $metode = $_POST['metode_pembayaran'];
        
        $db->beginTransaction();
        
        try {
            // Create payment record
            $query = "INSERT INTO pembayaran (booking_id, jumlah, metode_pembayaran, status) 
                     VALUES (?, ?, ?, 'pending')";
            $stmt = $db->prepare($query);
            $stmt->execute([$booking_id, $booking['harga'], $metode]);
            
            // Update booking status
            $query = "UPDATE booking SET status = 'confirmed' WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$booking_id]);
            
            $db->commit();
            
            // Redirect to payment gateway
            $payment_link = createPaymentLink($booking_id, $booking['harga']);
            header("Location: $payment_link");
            exit;
            
        } catch(Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }
} catch(Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header('Location: index.php?page=riwayat');
    exit;
}
?>

<main class="container">
    <div class="payment-container">
        <h2>Pembayaran</h2>

        <div class="card payment-details">
            <h3>Detail Booking</h3>
            <div class="detail-grid">
                <div class="detail-item">
                    <span class="label">No. Booking:</span>
                    <span class="value"><?= generateBookingNumber() ?></span>
                </div>
                <div class="detail-item">
                    <span class="label">Pasien:</span>
                    <span class="value"><?= htmlspecialchars($booking['nama_pasien']) ?></span>
                </div>
                <div class="detail-item">
                    <span class="label">No. RM:</span>
                    <span class="value"><?= htmlspecialchars($booking['no_rm']) ?></span>
                </div>
                <div class="detail-item">
                    <span class="label">Dokter:</span>
                    <span class="value"><?= htmlspecialchars($booking['nama_dokter']) ?></span>
                </div>
                <div class="detail-item">
                    <span class="label">Layanan:</span>
                    <span class="value"><?= htmlspecialchars($booking['nama_layanan']) ?></span>
                </div>
                <div class="detail-item">
                    <span class="label">Tanggal:</span>
                    <span class="value"><?= date('d M Y', strtotime($booking['tanggal'])) ?></span>
                </div>
                <div class="detail-item">
                    <span class="label">Jam:</span>
                    <span class="value"><?= date('H:i', strtotime($booking['jam'])) ?> WIB</span>
                </div>
                <div class="detail-item total">
                    <span class="label">Total Pembayaran:</span>
                    <span class="value">Rp <?= number_format($booking['harga'], 0, ',', '.') ?></span>
                </div>
            </div>
        </div>

        <div class="card">
            <form method="POST" class="payment-form">
                <div class="form-group">
                    <label>Metode Pembayaran</label>
                    <select name="metode_pembayaran" class="form-control" required>
                        <option value="">Pilih Metode Pembayaran</option>
                        <option value="transfer">Transfer Bank</option>
                        <option value="va">Virtual Account</option>
                        <option value="ewallet">E-Wallet</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Lanjutkan ke Pembayaran</button>
            </form>
        </div>
    </div>
</main> 