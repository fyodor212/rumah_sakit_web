<?php
// Debug
echo "Loading routes...<br>";

// Definisikan routes menggunakan Router class kita sendiri
$router->get('/', function() {
    echo "Executing home route<br>";
    require_once __DIR__ . '/../app/Views/main.php';
});

$router->get('/login', function() {
    echo "Executing login route<br>";
    require_once __DIR__ . '/../app/Views/login.php';
});

// Route untuk pasien
$router->get('/pasien', function() {
    requireLogin();
    $pasienController = new \App\Http\Controllers\PasienController();
    $pasienController->index();
});

// Route untuk detail pasien
$router->get('/pasien/detail', function() {
    requireLogin();
    $pasienController = new \App\Http\Controllers\PasienController();
    $pasienController->detail();
});

// Route untuk mengubah data pasien
$router->post('/pasien/update', function() {
    requireLogin();
    $pasienController = new \App\Http\Controllers\PasienController();
    $pasienController->handleUpdatePatient();
});

// Route untuk dokter
$router->get('/dokter', function() {
    requireLogin();
    $dokterController = new \App\Http\Controllers\DokterController();
    $dokterController->index();
});

// Route untuk layanan
$router->get('/layanan', function() {
    requireLogin();
    $layananController = new \App\Http\Controllers\LayananController();
    $layananController->index();
});

// Route untuk riwayat booking
$router->get('/riwayat-booking', function() {
    requireLogin();
    $bookingController = new \App\Http\Controllers\BookingController();
    $bookingController->getRiwayatBooking();
});

// Route untuk admin dashboard
$router->get('/admin/dashboard', function() {
    requireAdmin();
    require_once __DIR__ . '/../app/Views/pages/admin/dashboard.php';
}); 