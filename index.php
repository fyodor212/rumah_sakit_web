<?php
// Mulai output buffering
ob_start();

// Mulai session
session_start();

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load file konfigurasi dan helper
require_once 'config/database.php';
require_once 'config/functions.php';
require_once 'app/Helpers/helpers.php';

// Debug logging untuk session
error_log("Session data: " . print_r($_SESSION, true));

// Redirect berdasarkan role jika mengakses root URL atau home
$current_page = $_GET['page'] ?? '';

if (isLoggedIn()) {
    // Hanya redirect jika mengakses root URL atau home
    if (empty($current_page) || $current_page === 'home') {
        if (isAdmin()) {
            header('Location: index.php?page=admin/dashboard');
            exit;
        } elseif (isPasien()) {
            header('Location: index.php?page=patient/dashboard');
            exit;
        } elseif (isDokter()) {
            header('Location: index.php?page=doctor/dashboard');
            exit;
        }
    }
}

// Definisikan halaman yang bisa diakses publik
$public_pages = [
    'home',
    'auth/login',
    'auth/register',
    'auth/handle_login',
    'auth/handle_register',
    'auth/logout',
    'doctors',
    'services',
    'contact',
    'reviews',
    '404'
];

// Definisikan halaman yang memerlukan akses admin
$admin_pages = [
    // Dashboard & Profile
    'admin/dashboard',
    'admin/profile',
    'admin/settings',
    'admin/handle_settings',
    
    // Dokter
    'admin/doctors',
    'admin/manage_doctors',
    'admin/add_doctor',
    'admin/edit_doctor',
    'admin/delete_doctor',
    'admin/handle_add_doctor',
    'admin/handle_edit_doctor',
    'admin/handle_delete_doctor',
    'admin/handle_update_doctor',
    'admin/get_doctor',
    
    // Jadwal
    'admin/manage_schedules',
    'admin/edit_schedule',
    'admin/delete_schedule',
    'admin/update_doctor_schedule',
    
    // Layanan
    'admin/manage_services',
    'admin/add_service',
    'admin/edit_service',
    'admin/delete_service',
    'admin/handle_add_service',
    'admin/update_service',
    'admin/get_service',
    
    // Booking/Janji Temu
    'admin/manage_bookings',
    'admin/bookings',
    'admin/booking_detail',
    'admin/handle_booking_status',
    'admin/get_booking_details',
    'admin/view_booking',
    
    // Pembayaran
    'admin/manage_payments',
    'admin/manage_payment',
    'admin/view_payment',
    'admin/print_payment',
    
    // Pasien
    'admin/manage_patients',
    'admin/edit_patient',
    'admin/delete_patient',
    'admin/handle_update_patient',
    'admin/view_patient',
    
    // Users
    'admin/manage_users',
    'admin/delete_user',
    'admin/update_user_role',
    
    // Pesan
    'admin/manage_messages',
    'admin/handle_message',
    
    // Export & Data
    'admin/export',
    'admin/handle_export',
    'admin/manage_data'
];

// Definisikan halaman yang memerlukan akses pasien
$patient_pages = [
    'patient/dashboard',
    'patient/book_appointment',
    'patient/my_appointments',
    'patient/medical_records',
    'patient/profile',
    'patient/payments',
    'patient/get_bill_details',
    'patient/process_payment',
    'patient/handle_payment'
];

// Ambil halaman yang diminta
$page = $current_page ?: 'home';

// Inisialisasi $page_file terlebih dahulu
$base_path = realpath(dirname(__FILE__));

// Tentukan path file berdasarkan tipe halaman
if (str_starts_with($page, 'admin/')) {
    $page_file = $base_path . "/app/Views/pages/admin/" . basename($page) . ".php";
    error_log("[DEBUG] Admin page: " . basename($page));
} elseif (str_starts_with($page, 'patient/')) {
    $page_file = $base_path . "/app/Views/pages/patient/" . basename($page) . ".php";
    error_log("[DEBUG] Patient page: " . basename($page));
} elseif (str_starts_with($page, 'doctor/')) {
    $page_file = $base_path . "/app/Views/pages/doctor/" . basename($page) . ".php";
    error_log("[DEBUG] Doctor page: " . basename($page));
} else {
    $page_file = $base_path . "/app/Views/pages/{$page}.php";
    error_log("[DEBUG] Public page: " . $page);
}

// Debug logging
error_log("[DEBUG] Full requested page: " . $page);
error_log("[DEBUG] Base name: " . basename($page));
error_log("[DEBUG] Full page file path: " . $page_file);
error_log("[DEBUG] File exists check: " . (file_exists($page_file) ? 'yes' : 'no'));
error_log("[DEBUG] Directory listing:");
error_log(shell_exec("ls -la " . dirname($page_file)));

// Jika halaman tidak ditemukan, tampilkan 404
if (!file_exists($page_file)) {
    error_log("File not found: " . $page_file);
    $page = '404';
    $page_file = $base_path . "/app/Views/pages/404.php";
}

// Cek akses halaman
if (!in_array($page, $public_pages)) {
    error_log("Checking access for page: $page");
    error_log("Is logged in: " . (isLoggedIn() ? 'yes' : 'no'));
    error_log("Is admin: " . (isAdmin() ? 'yes' : 'no'));
    
    // Redirect ke login jika belum login
    if (!isLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        $_SESSION['message'] = "Silakan login terlebih dahulu";
        $_SESSION['message_type'] = 'warning';
        header('Location: index.php?page=auth/login');
        exit;
    }
    
    // Cek akses admin
    if (str_starts_with($page, 'admin/')) {
        if (!isAdmin()) {
            error_log("Access denied: Non-admin user trying to access admin page");
            $_SESSION['message'] = "Anda tidak memiliki akses ke halaman tersebut";
            $_SESSION['message_type'] = 'danger';
            header('Location: index.php');
            exit;
        }
    }
    
    // Cek akses pasien
    if (str_starts_with($page, 'patient/')) {
        if (!isPasien()) {
            error_log("Access denied: Non-patient user trying to access patient page");
            $_SESSION['message'] = "Anda tidak memiliki akses ke halaman tersebut";
            $_SESSION['message_type'] = 'danger';
            header('Location: index.php');
            exit;
        }
    }
}

try {
    // Tentukan template berdasarkan tipe halaman
    if (str_starts_with($page, 'admin/')) {
        // Load template admin
        require_once $base_path . "/app/Views/templates/admin/header.php";
        require_once $page_file;
        require_once $base_path . "/app/Views/templates/admin/footer.php";
    } elseif (str_starts_with($page, 'patient/')) {
        // Load template patient
        require_once $base_path . "/app/Views/templates/patient/header.php";
        require_once $page_file;
        require_once $base_path . "/app/Views/templates/patient/footer.php";
    } elseif (str_starts_with($page, 'auth/') || str_contains($page, 'handle_') || str_contains($page, 'get_')) {
        // Load halaman tanpa template untuk auth dan ajax
        require_once $page_file;
    } else {
        // Load template default untuk halaman publik
        require_once $base_path . "/app/Views/templates/header.php";
        require_once $page_file;
        require_once $base_path . "/app/Views/templates/footer.php";
    }
} catch (Exception $e) {
    // Log error
    error_log("Error in index.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    // Tampilkan pesan error
    $_SESSION['error'] = "Terjadi kesalahan sistem";
    require_once $base_path . "/app/Views/pages/404.php";
}

// Flush output buffer at the end
ob_end_flush();
?> 