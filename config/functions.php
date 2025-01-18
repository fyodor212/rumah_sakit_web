<?php
// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fungsi-fungsi autentikasi
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    $is_admin = isset($_SESSION['user_id']) && isset($_SESSION['role']) && strtolower($_SESSION['role']) === 'admin';
    error_log("isAdmin() check - User ID: " . ($_SESSION['user_id'] ?? 'not set') . ", Role: " . ($_SESSION['role'] ?? 'not set') . ", Result: " . ($is_admin ? 'true' : 'false'));
    return $is_admin;
}

function isPasien() {
    $is_pasien = isset($_SESSION['user_id']) && isset($_SESSION['role']) && strtolower($_SESSION['role']) === 'pasien';
    error_log("isPasien() check - User ID: " . ($_SESSION['user_id'] ?? 'not set') . ", Role: " . ($_SESSION['role'] ?? 'not set') . ", Result: " . ($is_pasien ? 'true' : 'false'));
    return $is_pasien;
}

/**
 * Memeriksa apakah user yang login adalah pasien
 */
function isPatient() {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'pasien';
}

/**
 * Mendapatkan ID pasien berdasarkan user_id
 */
function getPatientIdByUserId($userId) {
    global $conn;
    try {
        $stmt = $conn->prepare("SELECT id FROM pasien WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['id'] : null;
    } catch (PDOException $e) {
        error_log("Error getting patient ID: " . $e->getMessage());
        return null;
    }
}

// Fungsi middleware
function requireAdmin() {
    if (!isAdmin()) {
        if (!headers_sent()) {
            $_SESSION['message'] = "Anda tidak memiliki akses ke halaman tersebut";
            $_SESSION['message_type'] = 'danger';
            header('Location: index.php');
            exit();
        } else {
            echo "<script>
                alert('Anda tidak memiliki akses ke halaman tersebut');
                window.location.href = 'index.php';
            </script>";
            exit();
        }
    }
}

function requireLogin() {
    if (!isLoggedIn()) {
        if (!headers_sent()) {
            $_SESSION['message'] = "Silakan login terlebih dahulu";
            $_SESSION['message_type'] = 'warning';
            header("Location: index.php?page=auth/login");
            exit();
        } else {
            echo "<script>
                alert('Silakan login terlebih dahulu');
                window.location.href = 'index.php?page=auth/login';
            </script>";
            exit();
        }
    }
}

function redirectIfNotLoggedIn() {
    if (!isLoggedIn()) {
        $_SESSION['message'] = "Silakan login terlebih dahulu";
        $_SESSION['message_type'] = 'warning';
        header("Location: index.php?page=auth/login");
        exit();
    }
}

// Fungsi validasi session
function validateSession() {
    if (isLoggedIn()) {
        try {
            global $db;
            $query = "SELECT id, status FROM users WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user || $user['status'] !== 'active') {
                session_destroy();
                header('Location: index.php?page=auth/login&error=invalid_session');
                exit;
            }
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
        }
    }
}

// Fungsi untuk admin panel
function getAdminPageTitle($page) {
    $titles = [
        'admin/dashboard' => 'Dashboard',
        'admin/doctors' => 'Kelola Dokter',
        'admin/add_doctor' => 'Tambah Dokter',
        'admin/edit_doctor' => 'Edit Dokter',
        'admin/manage_doctors' => 'Manajemen Dokter',
        // ... (titles lainnya tetap sama)
    ];
    return $titles[$page] ?? 'Admin Panel';
}

function getAdminMenuIcon($page) {
    $icons = [
        'dashboard' => 'tachometer-alt',
        'doctors' => 'user-md',
        'patients' => 'procedures',
        'services' => 'stethoscope',
        'bookings' => 'calendar-check',
        'payments' => 'money-bill-wave',
        'messages' => 'envelope',
        'users' => 'users',
        'profile' => 'user-shield',
        'settings' => 'cog'
    ];
    
    $section = explode('/', $page)[1] ?? '';
    return $icons[$section] ?? 'circle';
}

// Fungsi untuk status dan badge
function getStatusBadgeClass($status) {
    switch ($status) {
        case 'pending':
            return 'bg-warning text-dark';
        case 'confirmed':
            return 'bg-success';
        case 'completed':
            return 'bg-info';
        case 'cancelled':
            return 'bg-danger';
        default:
            return 'bg-secondary';
    }
}

function getStatusText($status) {
    switch ($status) {
        case 'pending':
            return 'Menunggu';
        case 'confirmed':
            return 'Dikonfirmasi';
        case 'completed':
            return 'Selesai';
        case 'cancelled':
            return 'Dibatalkan';
        default:
            return 'Tidak Diketahui';
    }
}

function getPaymentStatusColor($status) {
    return match(strtolower($status)) {
        'success' => 'success',
        'pending' => 'warning',
        'failed' => 'danger',
        default => 'secondary'
    };
}

// Fungsi format currency
function formatCurrency($amount) {
    $amount = floatval($amount);
    return 'Rp ' . number_format($amount, 2, ',', '.');
}

function formatRupiah($angka) {
    $angka = floatval($angka);
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

function getPageTitle($page) {
    $titles = [
        'home' => 'Beranda',
        'doctors' => 'Dokter',
        'services' => 'Layanan',
        'reviews' => 'Review Pasien',
        'contact' => 'Kontak',
        
        'patient/doctors' => 'Daftar Dokter',
        'patient/services' => 'Layanan Tersedia',
        'patient/reviews' => 'Review Saya',
        
        // ... tambahkan judul lainnya
    ];
    
    return $titles[$page] ?? 'RS Sehat';
}

/**
 * Format waktu dari format 24 jam ke format yang lebih mudah dibaca
 * @param string $time Waktu dalam format H:i:s atau H:i
 * @return string Waktu dalam format H:i
 */
function formatTime($time) {
    if (empty($time)) return '-';
    return date('H:i', strtotime($time));
}

function getBadgeColor($role) {
    switch(strtolower($role)) {
        case 'admin':
            return 'danger';
        case 'dokter':
            return 'success';
        case 'pasien':
            return 'primary';
        default:
            return 'secondary';
    }
}

/**
 * Redirect ke halaman tertentu dengan pesan
 */
function redirectWith($page, $message, $type = 'info') {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
    header("Location: index.php?page=" . $page);
    exit;
} 