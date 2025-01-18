<?php
class PaymentController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function processPayment() {
        try {
            if (!isset($_POST['booking_id']) || !isset($_POST['metode_pembayaran'])) {
                throw new Exception("Data tidak lengkap");
            }

            $booking_id = $_POST['booking_id'];
            $metode = $_POST['metode_pembayaran'];

            // Ambil data booking dan harga layanan
            $query = "SELECT b.*, l.harga 
                     FROM booking b 
                     JOIN layanan l ON b.layanan_id = l.id 
                     WHERE b.id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$booking_id]);
            $booking = $stmt->fetch();

            if (!$booking) {
                throw new Exception("Booking tidak ditemukan");
            }

            // Cek apakah sudah ada tagihan
            $query = "SELECT id FROM tagihan WHERE booking_id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$booking_id]);
            if ($stmt->fetch()) {
                throw new Exception("Tagihan sudah ada");
            }

            // Begin transaction
            $this->db->beginTransaction();
            
            try {
                // Insert ke tabel tagihan
                $query = "INSERT INTO tagihan (
                            booking_id, nominal, tanggal, 
                            status, metode_pembayaran, created_at, updated_at
                        ) VALUES (?, ?, CURDATE(), 'Belum Lunas', ?, NOW(), NOW())";
                
                $stmt = $this->db->prepare($query);
                $result = $stmt->execute([
                    $booking_id,
                    $booking['harga'],
                    $metode
                ]);

                if (!$result) {
                    throw new Exception("Gagal membuat tagihan");
                }

                $this->db->commit();
                $_SESSION['message'] = "Pembayaran berhasil diproses";
                $_SESSION['message_type'] = 'success';
                header("Location: index.php?page=patient/payment_detail&booking_id=" . $booking_id);
                exit();

            } catch (Exception $e) {
                $this->db->rollBack();
                throw $e;
            }

        } catch (Exception $e) {
            $_SESSION['message'] = "Gagal memproses pembayaran: " . $e->getMessage();
            $_SESSION['message_type'] = 'danger';
            header("Location: index.php?page=patient/payment_detail&booking_id=" . $_POST['booking_id']);
            exit();
        }
    }

    public function uploadBuktiPembayaran() {
        try {
            if (!isset($_POST['tagihan_id']) || !isset($_FILES['bukti_pembayaran'])) {
                throw new Exception("Data tidak lengkap");
            }

            $tagihan_id = $_POST['tagihan_id'];
            $file = $_FILES['bukti_pembayaran'];

            // Validasi file
            $allowed = ['jpg', 'jpeg', 'png'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            if (!in_array($ext, $allowed)) {
                throw new Exception("Format file tidak didukung");
            }

            if ($file['size'] > 2000000) { // 2MB
                throw new Exception("Ukuran file terlalu besar (max 2MB)");
            }

            // Generate nama file unik
            $filename = uniqid() . '.' . $ext;
            $target = 'uploads/bukti_pembayaran/' . $filename;

            // Upload file
            if (!move_uploaded_file($file['tmp_name'], $target)) {
                throw new Exception("Gagal mengupload file");
            }

            // Update tagihan
            $query = "UPDATE tagihan SET 
                     bukti_pembayaran = ?,
                     status = 'Belum Lunas',
                     updated_at = NOW()
                     WHERE id = ?";
            
            $stmt = $this->db->prepare($query);
            if (!$stmt->execute([$target, $tagihan_id])) {
                unlink($target); // Hapus file jika update gagal
                throw new Exception("Gagal menyimpan bukti pembayaran");
            }

            $this->db->commit();
            $_SESSION['message'] = "Bukti pembayaran berhasil diupload";
            $_SESSION['message_type'] = 'success';
            header("Location: index.php?page=patient/payment_detail&booking_id=" . $_POST['booking_id']);
            exit();

        } catch (Exception $e) {
            $this->db->rollBack();
            if (isset($target) && file_exists($target)) {
                unlink($target);
            }
            throw $e;
        }
    }

    public function getPaymentDetail() {
        try {
            if (!isset($_GET['id'])) {
                throw new Exception('ID pembayaran tidak ditemukan');
            }

            $payment_id = (int)$_GET['id'];

            // Query untuk mengambil detail pembayaran
            $query = "SELECT p.*, 
                     b.tanggal as tanggal_booking,
                     b.jam as jam_booking,
                     pas.nama as nama_pasien,
                     pas.no_rm,
                     d.nama as nama_dokter,
                     d.spesialisasi,
                     l.nama as nama_layanan,
                     l.harga
                     FROM pembayaran p
                     JOIN booking b ON p.booking_id = b.id
                     JOIN pasien pas ON b.pasien_id = pas.id
                     JOIN dokter d ON b.dokter_id = d.id
                     JOIN layanan l ON b.layanan_id = l.id
                     WHERE p.id = ?";

            $stmt = $this->db->prepare($query);
            $stmt->execute([$payment_id]);
            $payment = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$payment) {
                throw new Exception('Data pembayaran tidak ditemukan');
            }

            // Format HTML untuk detail pembayaran
            $html = '
            <div class="payment-detail">
                <div class="mb-3">
                    <h6>Informasi Pasien</h6>
                    <p>Nama: ' . htmlspecialchars($payment['nama_pasien']) . '</p>
                    <p>No. RM: ' . htmlspecialchars($payment['no_rm']) . '</p>
                </div>
                
                <div class="mb-3">
                    <h6>Informasi Dokter</h6>
                    <p>Nama: ' . htmlspecialchars($payment['nama_dokter']) . '</p>
                    <p>Spesialisasi: ' . htmlspecialchars($payment['spesialisasi']) . '</p>
                </div>
                
                <div class="mb-3">
                    <h6>Informasi Layanan</h6>
                    <p>Layanan: ' . htmlspecialchars($payment['nama_layanan']) . '</p>
                    <p>Harga: ' . formatCurrency($payment['harga']) . '</p>
                </div>
                
                <div class="mb-3">
                    <h6>Informasi Pembayaran</h6>
                    <p>Jumlah: ' . formatCurrency($payment['jumlah']) . '</p>
                    <p>Metode: ' . ucfirst($payment['metode_pembayaran']) . '</p>
                    <p>Status: <span class="badge bg-' . $this->getStatusBadgeClass($payment['status']) . '">' 
                        . ucfirst($payment['status']) . '</span></p>
                    <p>Tanggal: ' . date('d/m/Y H:i', strtotime($payment['created_at'])) . '</p>
                </div>
            </div>';

            $this->jsonResponse('success', '', ['html' => $html]);

        } catch (Exception $e) {
            $this->jsonResponse('error', $e->getMessage());
        }
    }

    private function getStatusBadgeClass($status) {
        return match($status) {
            'pending' => 'warning',
            'success' => 'success',
            'failed' => 'danger',
            default => 'secondary'
        };
    }

    private function jsonResponse($status, $message = '', $data = []) {
        header('Content-Type: application/json');
        echo json_encode(array_merge(
            [
                'status' => $status,
                'message' => $message
            ],
            $data
        ));
        exit;
    }
} 