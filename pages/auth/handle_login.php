<?php
session_start();
require_once __DIR__ . '/../../../../config/database.php';

// Debug mode
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Debug input
    error_log("Login attempt - Username: " . $_POST['username']);
    
    // Validasi input
    if (empty($_POST['username']) || empty($_POST['password'])) {
        throw new Exception('Username dan password harus diisi');
    }

    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Debug query
    error_log("Executing query for username: " . $username);
    
    // Query untuk mencari user
    $stmt = $conn->prepare("
        SELECT u.id, u.username, u.password, u.role, u.status, 
        CASE 
            WHEN u.role = 'pasien' THEN p.nama
            WHEN u.role = 'dokter' THEN d.nama
            ELSE u.username
        END as nama
        FROM users u
        LEFT JOIN pasien p ON u.id = p.user_id
        LEFT JOIN dokter d ON u.id = d.user_id
        WHERE u.username = ?
    ");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("s", $username);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Debug user data
    error_log("User data found: " . ($user ? "Yes" : "No"));
    if ($user) {
        error_log("User role: " . $user['role']);
        error_log("User nama: " . $user['nama']);
    }

    // Cek apakah user ditemukan dan password cocok
    if ($user && password_verify($password, $user['password'])) {
        // Cek status user
        if ($user['status'] !== 'active') {
            throw new Exception('Akun Anda tidak aktif. Silakan hubungi admin.');
        }

        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['nama'] = $user['nama'];

        error_log("Login successful - Role: " . $user['role']); // Debug log

        // Redirect berdasarkan role
        switch ($user['role']) {
            case 'admin':
                $_SESSION['message'] = 'Selamat datang, Admin!';
                $_SESSION['message_type'] = 'success';
                header('Location: index.php?page=admin/dashboard');
                break;
            case 'pasien':
                $_SESSION['message'] = 'Selamat datang kembali!';
                $_SESSION['message_type'] = 'success';
                header('Location: index.php?page=patient/dashboard');
                break;
            case 'dokter':
                $_SESSION['message'] = 'Selamat datang, Dokter!';
                $_SESSION['message_type'] = 'success';
                header('Location: index.php?page=doctor/dashboard');
                break;
            default:
                throw new Exception('Role tidak valid');
        }
        exit;
    } else {
        throw new Exception('Username atau password salah');
    }
} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    $_SESSION['message'] = $e->getMessage();
    $_SESSION['message_type'] = 'danger';
    header('Location: index.php?page=auth/login');
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
    exit;
} 