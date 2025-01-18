<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

if (!isset($_POST['action'])) {
    $_SESSION['message'] = "Invalid request";
    $_SESSION['message_type'] = 'danger';
    header("Location: ../../index.php?page=profile");
    exit();
}

if ($_POST['action'] === 'update_profile') {
    try {
        $user_id = $_SESSION['user_id'];
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        
        // Validasi username dan email
        if (empty($username) || empty($email)) {
            throw new Exception("Username dan email harus diisi");
        }

        // Cek apakah username sudah digunakan (kecuali oleh user ini sendiri)
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $stmt->bind_param("si", $username, $user_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            throw new Exception("Username sudah digunakan");
        }

        // Cek apakah email sudah digunakan (kecuali oleh user ini sendiri)
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $user_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            throw new Exception("Email sudah digunakan");
        }

        // Upload avatar jika ada
        $avatar_path = null;
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['avatar'];
            
            // Validasi ukuran file (max 2MB)
            if ($file['size'] > 2 * 1024 * 1024) {
                throw new Exception("Ukuran file terlalu besar. Maksimal 2MB");
            }
            
            // Validasi tipe file
            $allowed_types = ['image/jpeg', 'image/png'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            if (!in_array($mime_type, $allowed_types)) {
                throw new Exception("Tipe file tidak didukung. Gunakan JPG atau PNG");
            }
            
            // Generate nama file unik
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $extension;
            
            // Buat direktori uploads jika belum ada
            $upload_dir = __DIR__ . '/../../public/uploads/avatars/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Pindahkan file
            $avatar_path = 'public/uploads/avatars/' . $filename;
            if (!move_uploaded_file($file['tmp_name'], $upload_dir . $filename)) {
                throw new Exception("Gagal mengupload file");
            }
        }

        // Update user data
        if (!empty($password)) {
            // Jika password diisi, update password juga
            if (strlen($password) < 6) {
                throw new Exception("Password minimal 6 karakter");
            }
            if ($password !== $_POST['confirm_password']) {
                throw new Exception("Konfirmasi password tidak sesuai");
            }
            
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            if ($avatar_path) {
                $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, password = ?, avatar = ? WHERE id = ?");
                $stmt->bind_param("ssssi", $username, $email, $hashed_password, $avatar_path, $user_id);
            } else {
                $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, password = ? WHERE id = ?");
                $stmt->bind_param("sssi", $username, $email, $hashed_password, $user_id);
            }
        } else {
            // Jika password kosong, update tanpa password
            if ($avatar_path) {
                $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, avatar = ? WHERE id = ?");
                $stmt->bind_param("sssi", $username, $email, $avatar_path, $user_id);
            } else {
                $stmt = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
                $stmt->bind_param("ssi", $username, $email, $user_id);
            }
        }

        if (!$stmt->execute()) {
            throw new Exception("Gagal mengupdate profil");
        }

        // Update session data
        $_SESSION['username'] = $username;
        if ($avatar_path) {
            $_SESSION['avatar'] = $avatar_path;
        }

        $_SESSION['message'] = "Profil berhasil diupdate";
        $_SESSION['message_type'] = 'success';
        
    } catch (Exception $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = 'danger';
    }
    
    header("Location: ../../index.php?page=profile");
    exit();
} 