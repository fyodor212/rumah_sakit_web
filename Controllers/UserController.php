<?php
class UserController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function updateStatus() {
        try {
            if (empty($_POST['user_id']) || empty($_POST['status'])) {
                throw new Exception('Data tidak lengkap');
            }

            $user_id = (int)$_POST['user_id'];
            $status = $_POST['status'];

            // Validasi status
            if (!in_array($status, ['active', 'inactive'])) {
                throw new Exception('Status tidak valid');
            }

            $query = "UPDATE users SET status = ? WHERE id = ?";
            $stmt = $this->db->prepare($query);
            
            if (!$stmt->execute([$status, $user_id])) {
                throw new Exception('Gagal mengupdate status user');
            }

            echo json_encode([
                'status' => 'success',
                'message' => 'Status user berhasil diupdate'
            ]);

        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function deleteUser() {
        try {
            if (empty($_POST['user_id'])) {
                throw new Exception('ID user tidak valid');
            }

            $user_id = (int)$_POST['user_id'];

            // Cek apakah user yang akan dihapus bukan admin
            $check_query = "SELECT role FROM users WHERE id = ?";
            $check_stmt = $this->db->prepare($check_query);
            $check_stmt->execute([$user_id]);
            $user = $check_stmt->fetch();

            if ($user['role'] === 'admin') {
                throw new Exception('Tidak dapat menghapus akun admin');
            }

            // Mulai transaksi
            $this->db->beginTransaction();

            // Hapus data dari tabel terkait
            $tables = ['pasien', 'dokter', 'users'];
            
            foreach ($tables as $table) {
                if ($table === 'users') {
                    $query = "DELETE FROM $table WHERE id = ?";
                } else {
                    $query = "DELETE FROM $table WHERE user_id = ?";
                }
                
                $stmt = $this->db->prepare($query);
                $stmt->execute([$user_id]);
            }

            // Commit transaksi
            $this->db->commit();

            echo json_encode([
                'status' => 'success',
                'message' => 'User berhasil dihapus'
            ]);

        } catch (Exception $e) {
            // Rollback jika terjadi error
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function updateRole() {
        try {
            if (empty($_POST['user_id']) || empty($_POST['role'])) {
                throw new Exception('Data tidak lengkap');
            }

            $user_id = (int)$_POST['user_id'];
            $new_role = $_POST['role'];

            // Validasi role
            if (!in_array($new_role, ['admin', 'pasien'])) {
                throw new Exception('Role tidak valid');
            }

            // Cek apakah user ada
            $check_query = "SELECT role FROM users WHERE id = ?";
            $check_stmt = $this->db->prepare($check_query);
            $check_stmt->execute([$user_id]);
            $user = $check_stmt->fetch();

            if (!$user) {
                throw new Exception('User tidak ditemukan');
            }

            // Mulai transaksi
            $this->db->beginTransaction();

            // Update role di tabel users
            $query = "UPDATE users SET role = ? WHERE id = ?";
            $stmt = $this->db->prepare($query);
            
            if (!$stmt->execute([$new_role, $user_id])) {
                throw new Exception('Gagal mengupdate role user');
            }

            // Hapus data dari tabel role lama
            $tables = ['pasien', 'dokter'];
            foreach ($tables as $table) {
                $query = "DELETE FROM $table WHERE user_id = ?";
                $stmt = $this->db->prepare($query);
                $stmt->execute([$user_id]);
            }

            // Buat data baru di tabel sesuai role
            if ($new_role === 'pasien') {
                // Generate nomor RM untuk pasien baru
                $no_rm = $this->generateNoRM();
                
                $query = "INSERT INTO pasien (user_id, nama, no_rm) 
                         SELECT id, username, ? FROM users WHERE id = ?";
                $stmt = $this->db->prepare($query);
                if (!$stmt->execute([$no_rm, $user_id])) {
                    throw new Exception('Gagal membuat data pasien');
                }
            }

            // Commit transaksi
            $this->db->commit();

            echo json_encode([
                'status' => 'success',
                'message' => 'Role user berhasil diupdate'
            ]);

        } catch (Exception $e) {
            // Rollback jika terjadi error
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    private function generateNoRM() {
        try {
            // Ambil tahun sekarang untuk prefix
            $year = date('Y');
            
            // Query untuk mendapatkan nomor urut terakhir
            $query = "SELECT MAX(CAST(SUBSTRING(no_rm, 6) AS UNSIGNED)) as last_number 
                     FROM pasien 
                     WHERE no_rm LIKE ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$year . '%']);
            $result = $stmt->fetch();
            
            $lastNumber = $result['last_number'] ?? 0;
            $nextNumber = $lastNumber + 1;
            
            // Format: YYYY-XXXXX (contoh: 2024-00001)
            return sprintf("%d-%05d", $year, $nextNumber);
        } catch (Exception $e) {
            error_log('Error generating RM number: ' . $e->getMessage());
            throw new Exception('Gagal generate nomor RM');
        }
    }

    public function updateSettings() {
        try {
            // Debug
            error_log('POST data: ' . print_r($_POST, true));
            error_log('Session data: ' . print_r($_SESSION, true));

            // Validasi email
            if (empty($_POST['email'])) {
                throw new Exception('Email harus diisi');
            }

            $this->db->beginTransaction();

            // Update email di tabel users
            $query = "UPDATE users SET email = ? WHERE id = ?";
            $stmt = $this->db->prepare($query);
            if (!$stmt->execute([$_POST['email'], $_SESSION['user_id']])) {
                throw new Exception('Gagal mengupdate email');
            }

            // Update data sesuai role
            if ($_SESSION['role'] !== 'admin') {
                // Validasi nama dan no_hp hanya untuk non-admin
                if (empty($_POST['nama']) || empty($_POST['no_hp'])) {
                    throw new Exception('Nama dan No. HP harus diisi');
                }

                if ($_SESSION['role'] === 'pasien') {
                    $query = "UPDATE pasien SET nama = ?, no_hp = ? WHERE user_id = ?";
                } else if ($_SESSION['role'] === 'dokter') {
                    $query = "UPDATE dokter SET nama = ?, no_hp = ? WHERE user_id = ?";
                }

                $stmt = $this->db->prepare($query);
                if (!$stmt->execute([$_POST['nama'], $_POST['no_hp'], $_SESSION['user_id']])) {
                    throw new Exception('Gagal mengupdate profil');
                }
            }

            // Update password jika diisi
            if (!empty($_POST['old_password']) && !empty($_POST['new_password'])) {
                // Verifikasi password lama
                $query = "SELECT password FROM users WHERE id = ?";
                $stmt = $this->db->prepare($query);
                $stmt->execute([$_SESSION['user_id']]);
                $user = $stmt->fetch();

                if (!password_verify($_POST['old_password'], $user['password'])) {
                    throw new Exception('Password lama tidak sesuai');
                }

                // Update password baru
                $hashedPassword = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
                $query = "UPDATE users SET password = ? WHERE id = ?";
                $stmt = $this->db->prepare($query);
                
                if (!$stmt->execute([$hashedPassword, $_SESSION['user_id']])) {
                    throw new Exception('Gagal mengupdate password');
                }
            }

            $this->db->commit();

            echo json_encode([
                'status' => 'success',
                'message' => 'Pengaturan berhasil diupdate'
            ]);

        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            
            error_log('Settings update error: ' . $e->getMessage());
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }
} 