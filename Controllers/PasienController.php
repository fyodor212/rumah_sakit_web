<?php

class PasienController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function index() {
        // Logika untuk menampilkan halaman pasien
        require_once __DIR__ . '/../Views/pages/patient/dashboard.php';
    }

    public function detail() {
        // Logika untuk menampilkan detail pasien
        require_once __DIR__ . '/../Views/pages/patient/profile_detail.php';
    }

    public function handleUpdatePatient() {
        try {
            if (empty($_POST['patient_id'])) {
                throw new Exception('Data tidak lengkap');
            }

            $patient_id = $_POST['patient_id'];
            $nama = $_POST['nama'];
            $no_hp = $_POST['no_hp'] ?? null;
            $alamat = $_POST['alamat'] ?? null;

            $query = "UPDATE pasien SET 
                     nama = ?, 
                     no_hp = ?,
                     alamat = ?
                     WHERE id = ?";
                     
            $stmt = $this->db->prepare($query);
            
            if (!$stmt->execute([$nama, $no_hp, $alamat, $patient_id])) {
                throw new Exception('Gagal mengupdate data pasien');
            }

            $_SESSION['message'] = "Profil berhasil diperbarui";
            $_SESSION['message_type'] = 'success';
            
            // Redirect ke detail profil
            header("Location: index.php?page=patient/profile_detail");
            exit();

        } catch (Exception $e) {
            $_SESSION['message'] = $e->getMessage();
            $_SESSION['message_type'] = 'danger';
            header("Location: index.php?page=patient/profile");
            exit();
        }
    }
} 