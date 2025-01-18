<?php
class ServiceController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getService() {
        try {
            if (empty($_GET['id'])) {
                throw new Exception('ID layanan tidak valid');
            }

            $service_id = (int)$_GET['id'];
            
            $query = "SELECT * FROM layanan WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$service_id]);
            $service = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$service) {
                throw new Exception('Layanan tidak ditemukan');
            }

            echo json_encode([
                'status' => 'success',
                'service' => $service
            ]);

        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function addService() {
        try {
            if (empty($_POST['nama']) || empty($_POST['deskripsi']) || empty($_POST['kategori'])) {
                throw new Exception('Nama, deskripsi, dan kategori harus diisi');
            }

            $query = "INSERT INTO layanan (nama, deskripsi, kategori, harga, status) 
                     VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($query);
            
            if (!$stmt->execute([
                $_POST['nama'],
                $_POST['deskripsi'],
                $_POST['kategori'],
                $_POST['harga'] ?? null,
                $_POST['status'] ?? 'tersedia'
            ])) {
                throw new Exception('Gagal menambahkan layanan');
            }

            echo json_encode([
                'status' => 'success',
                'message' => 'Layanan berhasil ditambahkan'
            ]);

        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function updateService() {
        try {
            if (empty($_POST['id']) || empty($_POST['nama']) || empty($_POST['deskripsi']) || empty($_POST['kategori'])) {
                throw new Exception('Semua field harus diisi');
            }

            $query = "UPDATE layanan 
                     SET nama = ?, 
                         deskripsi = ?, 
                         kategori = ?, 
                         harga = ?, 
                         status = ? 
                     WHERE id = ?";
            $stmt = $this->db->prepare($query);
            
            if (!$stmt->execute([
                $_POST['nama'],
                $_POST['deskripsi'],
                $_POST['kategori'],
                $_POST['harga'] ?? null,
                $_POST['status'],
                $_POST['id']
            ])) {
                throw new Exception('Gagal mengupdate layanan');
            }

            echo json_encode([
                'status' => 'success',
                'message' => 'Layanan berhasil diupdate'
            ]);

        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function deleteService() {
        try {
            if (empty($_POST['id'])) {
                throw new Exception('ID layanan tidak valid');
            }

            $query = "DELETE FROM layanan WHERE id = ?";
            $stmt = $this->db->prepare($query);
            
            if (!$stmt->execute([$_POST['id']])) {
                throw new Exception('Gagal menghapus layanan');
            }

            echo json_encode([
                'status' => 'success',
                'message' => 'Layanan berhasil dihapus'
            ]);

        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }
} 