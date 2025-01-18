<?php
// Pastikan tidak ada output sebelum header dan session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inisialisasi variabel unread
$unread = 0;

// Jika user sudah login, hitung pesan yang belum dibaca
if (isLoggedIn()) {
    try {
        $query = "SELECT COUNT(*) FROM pesan WHERE penerima_id = ? AND status = 'unread'";
        $stmt = $db->prepare($query);
        $stmt->execute([$_SESSION['user_id']]);
        $unread = $stmt->fetchColumn();
    } catch (Exception $e) {
        error_log("Error counting unread messages: " . $e->getMessage());
        // Tetap gunakan nilai default 0 jika terjadi error
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Klinik</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-bootstrap-4/bootstrap-4.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="public/css/layout.css" rel="stylesheet">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- DataTables -->
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script>
    function checkNewMessages() {
        fetch('ajax/check_messages.php')
            .then(response => response.json())
            .then(data => {
                if (data.unread > 0) {
                    // Update badge count
                    document.getElementById('message-badge').textContent = data.unread;
                    document.getElementById('message-badge').style.display = 'inline';
                    
                    // Tampilkan notifikasi jika ada pesan baru
                    if (data.new_messages > 0) {
                        showNotification('Pesan Baru', `Anda memiliki ${data.new_messages} pesan baru`);
                    }
                } else {
                    document.getElementById('message-badge').style.display = 'none';
                }
            });
    }

    function showNotification(title, message) {
        if (!("Notification" in window)) {
            return;
        }

        if (Notification.permission === "granted") {
            new Notification(title, { body: message });
        } else if (Notification.permission !== "denied") {
            Notification.requestPermission().then(permission => {
                if (permission === "granted") {
                    new Notification(title, { body: message });
                }
            });
        }
    }

    // Cek pesan baru setiap 30 detik
    if (document.querySelector('[data-role="admin"]')) {
        setInterval(checkNewMessages, 30000);
        checkNewMessages(); // Cek pertama kali
    }
    </script>
    <script>
    function showAlert(title, text, icon) {
        Swal.fire({
            title: title,
            text: text,
            icon: icon, // success, error, warning, info, question
            confirmButtonColor: '#0d6efd'
        });
    }

    function showConfirm(title, text, callback) {
        Swal.fire({
            title: title,
            text: text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#0d6efd',
            cancelButtonColor: '#dc3545',
            confirmButtonText: 'Ya',
            cancelButtonText: 'Tidak'
        }).then((result) => {
            if (result.isConfirmed) {
                callback();
            }
        });
    }
    </script>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-hospital-alt text-primary me-2"></i>
                Klinik
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link <?= $page == 'doctors' ? 'active' : '' ?>" 
                           href="index.php?page=doctors">
                            <i class="fas fa-user-md me-1"></i>Dokter
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $page == 'services' ? 'active' : '' ?>" 
                           href="index.php?page=services">
                            <i class="fas fa-stethoscope me-1"></i>Layanan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $page == 'reviews' ? 'active' : '' ?>" 
                           href="index.php?page=reviews">
                            <i class="fas fa-star me-1"></i>Review Pasien
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $page == 'contact' ? 'active' : '' ?>" 
                           href="index.php?page=contact">
                            <i class="fas fa-envelope me-1"></i>Kontak
                        </a>
                    </li>
                </ul>
                
                <?php if (isLoggedIn()): ?>
                    <?php if (isAdmin()): ?>
                        <!-- Menu Admin -->
                        <div class="dropdown">
                            <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-shield me-2"></i><?= $_SESSION['username'] ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <!-- Dashboard -->
                                <li><a class="dropdown-item" href="index.php?page=admin/dashboard">
                                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                                </a></li>
                                
                                <li><hr class="dropdown-divider"></li>
                                
                                <!-- Manajemen Pengguna -->
                                <li class="dropdown-submenu">
                                    <a class="dropdown-item d-flex justify-content-between align-items-center" href="#">
                                        <span><i class="fas fa-users me-2"></i>Manajemen Pengguna</span>
                                        <i class="fas fa-chevron-right ms-2"></i>
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="index.php?page=admin/manage_users">
                                            <i class="fas fa-users-cog me-2"></i>Kelola Users
                                        </a></li>
                                        <li><a class="dropdown-item" href="index.php?page=admin/manage_doctor">
                                            <i class="fas fa-user-md me-2"></i>Kelola Dokter
                                        </a></li>
                                        <li><a class="dropdown-item" href="index.php?page=admin/manage_patients">
                                            <i class="fas fa-procedures me-2"></i>Kelola Pasien
                                        </a></li>
                                    </ul>
                                </li>
                                
                                <!-- Manajemen Layanan -->
                                <li class="dropdown-submenu">
                                    <a class="dropdown-item d-flex justify-content-between align-items-center" href="#">
                                        <span><i class="fas fa-clipboard-list me-2"></i>Manajemen Layanan</span>
                                        <i class="fas fa-chevron-right ms-2"></i>
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="index.php?page=admin/manage_services">
                                            <i class="fas fa-stethoscope me-2"></i>Kelola Layanan
                                        </a></li>
                                        <li><a class="dropdown-item" href="index.php?page=admin/manage_booking">
                                            <i class="fas fa-calendar-check me-2"></i>Kelola Booking
                                        </a></li>
                                    </ul>
                                </li>
                                
                                <!-- Manajemen Keuangan -->
                                <li><a class="dropdown-item" href="index.php?page=admin/manage_payments">
                                    <i class="fas fa-money-bill-wave me-2"></i>Kelola Pembayaran
                                </a></li>
                                
                                <!-- Pesan -->
                                <li><a class="dropdown-item d-flex justify-content-between align-items-center" 
                                       href="index.php?page=admin/manage_messages">
                                    <span><i class="fas fa-envelope me-2"></i>Kelola Pesan</span>
                                    <?php if ($unread > 0): ?>
                                        <span class="badge bg-danger rounded-pill"><?= $unread ?></span>
                                    <?php endif; ?>
                                </a></li>
                                
                                <li><hr class="dropdown-divider"></li>
                                
                                <!-- Pengaturan & Logout -->
                                <li><a class="dropdown-item" href="index.php?page=admin/settings">
                                    <i class="fas fa-cog me-2"></i>Pengaturan Akun
                                </a></li>
                                <li><a class="dropdown-item" href="index.php?page=auth/logout">
                                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                                </a></li>
                            </ul>
            </div>
                    <?php elseif (isPasien()): ?>
                        <!-- Menu Pasien -->
                        <div class="dropdown">
                            <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-2"></i><?= $_SESSION['username'] ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <!-- Dashboard -->
                                <li><a class="dropdown-item" href="?page=patient/dashboard">
                                    <i class="fas fa-home me-2"></i>Dashboard
                                </a></li>
                                <li><a class="dropdown-item" href="?page=patient/my_appointments">
                                    <i class="fas fa-calendar-check me-2"></i>Janji Saya
                                </a></li>
                                <li><a class="dropdown-item" href="?page=patient/payment_history">
                                    <i class="fas fa-receipt me-2"></i>Riwayat Pembayaran
                                </a></li>
                                
                                <li><hr class="dropdown-divider"></li>
                                
                                <!-- Booking & Janji -->
                                <li class="dropdown-submenu">
                                    <a class="dropdown-item d-flex justify-content-between align-items-center" href="#">
                                        <span><i class="fas fa-calendar-alt me-2"></i>Booking & Janji</span>
                                        <i class="fas fa-chevron-right ms-2"></i>
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="?page=patient/book_appointment">
                                            <i class="fas fa-calendar-plus me-2"></i>Buat Janji
                                        </a></li>
                                        <li><a class="dropdown-item" href="?page=patient/my_appointments">
                                            <i class="fas fa-calendar-check me-2"></i>Janji Saya
                                        </a></li>
                                        <li><a class="dropdown-item" href="?page=patient/appointment_history">
                                            <i class="fas fa-history me-2"></i>Riwayat Janji
                                        </a></li>
                                    </ul>
                                </li>
                                
                                <!-- Rekam Medis -->
                                <li><a class="dropdown-item" href="?page=patient/medical_records">
                                    <i class="fas fa-notes-medical me-2"></i>Rekam Medis
                                </a></li>
                                
                                <!-- Pembayaran -->
                                <li class="dropdown-submenu">
                                    <a class="dropdown-item d-flex justify-content-between align-items-center" href="#">
                                        <span><i class="fas fa-money-bill-wave me-2"></i>Pembayaran</span>
                                        <i class="fas fa-chevron-right ms-2"></i>
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="?page=patient/payments">
                                            <i class="fas fa-file-invoice-dollar me-2"></i>Tagihan
                                        </a></li>
                                        <li><a class="dropdown-item" href="?page=patient/payment_history">
                                            <i class="fas fa-history me-2"></i>Riwayat Pembayaran
                                        </a></li>
                                    </ul>
                                </li>
                                
                                <!-- Review -->
                                <li><a class="dropdown-item" href="?page=patient/reviews">
                                    <i class="fas fa-star me-2"></i>Review & Penilaian
                                </a></li>
                                
                                <li><hr class="dropdown-divider"></li>
                                
                                <!-- Pengaturan & Logout -->
                                <li><a class="dropdown-item" href="?page=user/settings">
                                    <i class="fas fa-cog me-2"></i>Pengaturan Akun
                                </a></li>
                                <li><a class="dropdown-item" href="?page=auth/logout">
                                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                                </a></li>
                            </ul>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="navbar-nav">
                        <a href="?page=auth/login" class="btn btn-outline-primary me-2">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </a>
                        <a href="?page=auth/register" class="btn btn-primary">
                            <i class="fas fa-user-plus me-2"></i>Daftar
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        </nav>

    <!-- Main Content -->
    <main class="main-content">
        <?php if (isset($_SESSION['message'])): ?>
            <div class="container mt-3">
                <div class="alert alert-<?= $_SESSION['message_type'] ?? 'info' ?> alert-dismissible fade show" role="alert">
                    <?= $_SESSION['message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
            <?php 
            // Hapus pesan setelah ditampilkan
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
            ?>
        <?php endif; ?>

    <style>
    /* Styling untuk dropdown menu */
    .dropdown-menu {
        padding: 0.5rem 0;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    .dropdown-item {
        padding: 0.5rem 1rem;
    }

    /* Styling untuk submenu */
    .dropdown-submenu {
        position: relative;
    }

    .dropdown-submenu > .dropdown-menu {
        top: 0;
        left: 100%;
        margin-top: -0.5rem;
        display: none;
    }

    .dropdown-submenu:hover > .dropdown-menu {
        display: block;
    }

    /* Responsive styling */
    @media (max-width: 768px) {
        .dropdown-submenu > .dropdown-menu {
            position: static;
            margin-left: 1rem;
            margin-right: 1rem;
            box-shadow: none;
            border: none;
            background: #f8f9fa;
        }
        
        .dropdown-submenu > a::after {
            transform: rotate(90deg);
        }
    }

    /* Hover effects */
    .dropdown-item:hover {
        background-color: #f8f9fa;
    }

    .dropdown-item.active, 
    .dropdown-item:active {
        background-color: #0d6efd;
        color: white;
    }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle submenu on mobile
        const submenuTriggers = document.querySelectorAll('.dropdown-submenu > a');
        
        submenuTriggers.forEach(trigger => {
            trigger.addEventListener('click', function(e) {
                if (window.innerWidth <= 768) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const submenu = this.nextElementSibling;
                    const isVisible = submenu.style.display === 'block';
                    
                    // Hide all other submenus
                    document.querySelectorAll('.dropdown-submenu > .dropdown-menu').forEach(menu => {
                        menu.style.display = 'none';
                    });
                    
                    // Toggle current submenu
                    submenu.style.display = isVisible ? 'none' : 'block';
                }
            });
        });
        
        // Close submenus when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.dropdown-submenu')) {
                document.querySelectorAll('.dropdown-submenu > .dropdown-menu').forEach(menu => {
                    menu.style.display = '';
                });
            }
        });
    });
    </script>

    <script>
    $(document).ready(function() {
        // Cek parameter logout
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('logout') === 'success') {
            Swal.fire({
                icon: 'success',
                title: 'Logout Berhasil!',
                text: 'Anda telah berhasil keluar dari akun.',
                timer: 3000,
                showConfirmButton: false
            });
        }
    });
    </script>

    <script>
    $(document).ready(function() {
        // Cek parameter logout
        const urlParams = new URLSearchParams(window.location.search);
        console.log("Checking logout parameter:", urlParams.get('logout'));
        if (urlParams.get('logout') === 'success') {
            Swal.fire({
                icon: 'success',
                title: 'Logout Berhasil!',
                text: 'Anda telah berhasil keluar dari akun.',
                timer: 3000,
                showConfirmButton: false
            });
        }
    });
    </script>