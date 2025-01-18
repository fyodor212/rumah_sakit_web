<?php
require 'config/database.php';

if (!isLoggedIn()) {
    header('Location: index.php?page=login');
    exit;
}

// Handle booking process
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_dokter = $_POST['dokter_id'];
    $id_pasien = $_SESSION['user_id'];
    $tanggal_pemesanan = $_POST['tanggal'];
    $layanan_id = $_POST['layanan_id'];
    $status = 'pending';

    try {
        // Cek apakah id_pasien ada di tabel pasien
        $checkQuery = "SELECT * FROM pasien WHERE user_id = :id_pasien";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->execute(['id_pasien' => $id_pasien]);

        if ($checkStmt->rowCount() === 0) {
            throw new Exception("Data pasien tidak ditemukan");
        }

        // Insert booking
        $query = "INSERT INTO bookings (id_dokter, id_pasien, tanggal_pemesanan, layanan_id, status) 
                 VALUES (:id_dokter, :id_pasien, :tanggal_pemesanan, :layanan_id, :status)";
        $stmt = $db->prepare($query);
        
        $stmt->execute([
            'id_dokter' => $id_dokter,
            'id_pasien' => $id_pasien,
            'tanggal_pemesanan' => $tanggal_pemesanan,
            'layanan_id' => $layanan_id,
            'status' => $status
        ]);

        $_SESSION['success'] = "Booking berhasil dibuat!";
        header('Location: index.php?page=payment&booking_id=' . $db->lastInsertId());
        exit;

    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
}

// Handle cancel booking
if (isset($_GET['action']) && $_GET['action'] == 'cancel' && isset($_GET['id'])) {
    try {
        $booking_id = $_GET['id'];
        
        // Verify booking belongs to user
        $query = "SELECT * FROM bookings WHERE id = ? AND id_pasien = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$booking_id, $_SESSION['user_id']]);
        $booking = $stmt->fetch();
        
        if (!$booking) {
            throw new Exception("Booking tidak ditemukan");
        }
        
        if ($booking['status'] == 'completed') {
            throw new Exception("Tidak dapat membatalkan booking yang sudah selesai");
        }
        
        // Update booking status
        $query = "UPDATE bookings SET status = 'cancelled' WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$booking_id]);
        
        $_SESSION['success'] = "Booking berhasil dibatalkan";
        header('Location: index.php?page=riwayat');
        exit;
        
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header('Location: index.php?page=riwayat');
        exit;
    }
}

// Get doctors list
$query = "SELECT * FROM dokter ORDER BY nama ASC";
$doctors = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);

// Get services list
$query = "SELECT * FROM layanan ORDER BY nama ASC";
$layanan = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="page-container">
    <div class="page-header">
        <h1>Buat Janji</h1>
        <p>Silakan isi form booking</p>
    </div>

    <div class="content-card">
        <div class="form-container">
            <form method="POST">
                <div class="form-group">
                    <label>Pilih Dokter</label>
                    <select name="dokter_id" class="form-control" required>
                        <option value="">Pilih Dokter</option>
                        <?php foreach($doctors as $doctor): ?>
                            <option value="<?= $doctor['id'] ?>">
                                <?= htmlspecialchars($doctor['nama']) ?> - 
                                <?= htmlspecialchars($doctor['spesialisasi']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Pilih Layanan</label>
                    <select name="layanan_id" class="form-control" required>
                        <option value="">Pilih Layanan</option>
                        <?php foreach($layanan as $service): ?>
                            <option value="<?= $service['id'] ?>">
                                <?= htmlspecialchars($service['nama']) ?> - 
                                Rp <?= number_format($service['harga'], 0, ',', '.') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Tanggal Kunjungan</label>
                    <input type="date" name="tanggal" class="form-control" 
                           min="<?= date('Y-m-d') ?>" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Buat Janji</button>
            </form>
        </div>
    </div>
</div> 