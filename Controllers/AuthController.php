<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

class AuthController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function handleLogin() {
        try {
            // Debug
            error_log("Login attempt - POST data: " . print_r($_POST, true));
            
            if (!isset($_POST['username']) || !isset($_POST['password'])) {
                throw new Exception("Username dan password harus diisi");
            }

            $username = $_POST['username'];
            $password = $_POST['password'];

            // Debug
            error_log("Checking user: " . $username);

            // Cek user di database
            $query = "SELECT * FROM users WHERE username = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user || !password_verify($password, $user['password'])) {
                throw new Exception("Username atau password salah");
            }

            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Redirect berdasarkan role
            if ($user['role'] === 'admin') {
                $_SESSION['message'] = "Selamat datang, Admin!";
                $_SESSION['message_type'] = 'success';
                header("Location: ../index.php?page=admin/dashboard");
                exit();
            } elseif ($user['role'] === 'dokter') {
                $_SESSION['message'] = "Selamat datang, Dokter!";
                $_SESSION['message_type'] = 'success';
                header("Location: ../index.php?page=doctor/dashboard");
                exit();
            } elseif ($user['role'] === 'pasien') {
                $_SESSION['message'] = "Selamat datang!";
                $_SESSION['message_type'] = 'success';
                header("Location: ../index.php?page=patient/dashboard");
                exit();
            }

        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            $_SESSION['message'] = $e->getMessage();
            $_SESSION['message_type'] = 'danger';
            header("Location: ?page=auth/login");
            exit();
        }
    }

    public function handleLogout() {
        // Hapus semua data session
        session_unset();
        session_destroy();

        // Redirect ke halaman login
        header("Location: ?page=auth/login");
        exit();
    }

    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }

        $query = "SELECT * FROM users WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function checkRole($role) {
        if (!$this->isLoggedIn()) {
            return false;
        }
        return $_SESSION['role'] === $role;
    }
}