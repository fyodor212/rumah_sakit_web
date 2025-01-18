<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load helpers dan middleware
require_once 'app/Helpers/helpers.php';
require_once 'app/Middleware/auth.php';
require_once 'config/database.php';

// Update page routing
$page = $_GET['page'] ?? 'home';

// Map pages to their actual paths
$page_map = [
    'home' => 'public/home',
    'login' => 'auth/login',
    'register' => 'auth/register',
    'logout' => 'auth/logout',
    'profile' => 'auth/profile',
    'dokter' => 'public/dokter',
    'layanan' => 'public/layanan',
    'booking' => 'booking/booking',
    'payment' => 'booking/payment',
    'review' => 'booking/review',
    'riwayat' => 'public/riwayat',
    'error' => 'public/error',
    // Admin routes
    'admin/dashboard' => 'admin/dashboard',
    'admin/manage_doctor' => 'admin/manage_doctor',
    'admin/manage_booking' => 'admin/manage_booking',
    'admin/manage_payment' => 'admin/manage_payment',
    'admin/manage_data' => 'admin/manage_data'
];

// Get mapped page path
$page_path = $page_map[$page] ?? $page;

// Redirect ke dashboard yang sesuai
if ($page === 'dashboard') {
    if (isAdmin()) {
        $page_path = 'admin/dashboard';
    } else {
        $page_path = 'public/dashboard';
    }
}

// Cek akses halaman
if (!in_array($page_path, $public_pages)) {
    requireLogin();
}

if (in_array($page_path, $admin_pages)) {
    requireAdmin();
}

try {
    // Include header
    require_once 'app/Views/partials/header.php';

    // Check if file exists and include
    $file = "app/Views/pages/$page_path.php";
    if (file_exists($file)) {
        require_once $file;
    } else {
        $_SESSION['error'] = "Halaman tidak ditemukan";
        require_once 'app/Views/pages/public/error.php';
    }

    // Load footer
    require 'app/Views/partials/footer.php';
    
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    $_SESSION['error'] = "Terjadi kesalahan sistem";
    include 'app/Views/pages/public/error.php';
}
?>