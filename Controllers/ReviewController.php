<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

class ReviewController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function submitReview() {
        try {
            if (!isset($_POST['booking_id']) || !isset($_POST['rating'])) {
                throw new Exception("Data tidak lengkap");
            }

            $booking_id = $_POST['booking_id'];
            $rating = (int)$_POST['rating'];
            $komentar = $_POST['komentar'] ?? null;

            // Validasi rating
            if ($rating < 1 || $rating > 5) {
                throw new Exception("Rating harus antara 1-5");
            }

            // Validasi booking
            $query = "SELECT b.* FROM booking b 
                     JOIN pasien p ON b.pasien_id = p.id 
                     WHERE b.id = ? AND p.user_id = ? AND b.status = 'completed'";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$booking_id, $_SESSION['user_id']]);
            
            if (!$stmt->fetch()) {
                throw new Exception("Data janji temu tidak valid");
            }

            // Cek apakah sudah pernah review
            $query = "SELECT id FROM review WHERE booking_id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$booking_id]);
            
            if ($stmt->fetch()) {
                throw new Exception("Anda sudah memberikan review untuk janji temu ini");
            }

            // Insert review
            $query = "INSERT INTO review (booking_id, rating, komentar, created_at) 
                     VALUES (?, ?, ?, NOW())";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$booking_id, $rating, $komentar]);

            $_SESSION['message'] = "Review berhasil dikirim";
            $_SESSION['message_type'] = 'success';

            // Redirect ke daftar janji
            header("Location: index.php?page=patient/my_appointments");
            exit();

        } catch (Exception $e) {
            $_SESSION['message'] = "Gagal mengirim review: " . $e->getMessage();
            $_SESSION['message_type'] = 'danger';
            header("Location: index.php?page=patient/my_appointments");
            exit();
        }
    }
} 