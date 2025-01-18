<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Klinik - Pelayanan Kesehatan Terpercaya</title>
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    
    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #6c757d;
            --success-color: #198754;
            --info-color: #0dcaf0;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --light-color: #f8f9fa;
            --dark-color: #212529;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar {
            padding: 1rem 0;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.95);
        }

        .navbar.scrolled {
            padding: 0.5rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .navbar-brand i {
            font-size: 2rem;
            margin-right: 0.5rem;
            background: linear-gradient(135deg, var(--primary-color), #0043a8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .navbar-brand span {
            background: linear-gradient(135deg, var(--primary-color), #0043a8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .nav-link {
            color: var(--dark-color);
            font-weight: 500;
            padding: 0.5rem 1rem;
            transition: all 0.3s ease;
            position: relative;
        }

        .nav-link:hover {
            color: var(--primary-color);
        }

        .nav-link.active {
            color: var(--primary-color);
        }

        .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 1rem;
            right: 1rem;
            height: 2px;
            background: var(--primary-color);
        }

        .btn-nav {
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), #0043a8);
            border: none;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #0043a8, var(--primary-color));
            transform: translateY(-2px);
        }

        .dropdown-menu {
            display: none;
            position: absolute;
            right: 0;
            left: auto;
            min-width: 200px;
            padding: 0.5rem 0;
            margin: 0.125rem 0 0;
            background-color: #fff;
            background-clip: padding-box;
            border: 1px solid rgba(0,0,0,.15);
            border-radius: 0.25rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        .dropdown-menu.show {
            display: block;
        }

        .dropdown-toggle::after {
            display: inline-block;
            margin-left: 0.255em;
            vertical-align: 0.255em;
            content: "";
            border-top: 0.3em solid;
            border-right: 0.3em solid transparent;
            border-bottom: 0;
            border-left: 0.3em solid transparent;
        }

        .user-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--primary-color), #0043a8);
            color: white;
            font-size: 1.2rem;
            margin-right: 0.5rem;
        }

        .btn-appointment {
            background: linear-gradient(135deg, var(--success-color), #156c43);
            color: white;
            border: none;
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-appointment:hover {
            background: linear-gradient(135deg, #156c43, var(--success-color));
            transform: translateY(-2px);
            color: white;
        }

        .btn-appointment i {
            margin-right: 0.5rem;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-hospital-alt"></i>
                <span>KLINIK</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?page=doctors">Dokter</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?page=services">Layanan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?page=contact">Kontak</a>
                    </li>
                    
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if ($_SESSION['role'] === 'pasien'): ?>
                            <li class="nav-item ms-2">
                                <a href="index.php?page=patient/book_appointment" class="btn btn-appointment">
                                    <i class="fas fa-calendar-plus"></i> Buat Janji
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php if ($_SESSION['role'] === 'admin'): ?>
                            <!-- Menu Admin -->
                            <li class="nav-item">
                                <a class="nav-link" href="index.php?page=admin/dashboard">
                                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="index.php?page=admin/manage_doctors">
                                    <i class="fas fa-user-md me-2"></i>Dokter
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="index.php?page=admin/manage_patients">
                                    <i class="fas fa-users me-2"></i>Pasien
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="index.php?page=admin/manage_appointments">
                                    <i class="fas fa-calendar-check me-2"></i>Janji Temu
                                </a>
                            </li>
                        <?php elseif ($_SESSION['role'] === 'dokter'): ?>
                            <!-- Menu Dokter -->
                            <li class="nav-item">
                                <a class="nav-link" href="index.php?page=doctor/dashboard">
                                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="index.php?page=doctor/appointments">
                                    <i class="fas fa-calendar-check me-2"></i>Janji Temu
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="index.php?page=doctor/patients">
                                    <i class="fas fa-users me-2"></i>Pasien
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="index.php?page=doctor/schedule">
                                    <i class="fas fa-clock me-2"></i>Jadwal
                                </a>
                            </li>
                        <?php elseif ($_SESSION['role'] === 'pasien'): ?>
                            <!-- Menu Pasien -->
                            <li class="nav-item">
                                <a class="nav-link" href="index.php?page=patient/dashboard">
                                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="index.php?page=patient/my_appointments">
                                    <i class="fas fa-calendar-check me-2"></i>Janji Temu
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="index.php?page=patient/medical_records">
                                    <i class="fas fa-notes-medical me-2"></i>Rekam Medis
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <!-- User Dropdown -->
                        <li class="nav-item dropdown ms-3">
                            <button class="btn nav-link dropdown-toggle d-flex align-items-center" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <div class="user-avatar me-2">
                                    <?php if (isset($_SESSION['avatar']) && !empty($_SESSION['avatar'])): ?>
                                        <img src="<?php echo $_SESSION['avatar']; ?>" alt="Avatar" class="rounded-circle" width="35" height="35">
                                    <?php else: ?>
                                        <i class="fas fa-user"></i>
                                    <?php endif; ?>
                                </div>
                                <span><?php echo $_SESSION['username']; ?></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li>
                                    <a class="dropdown-item" href="index.php?page=profile">
                                        <i class="fas fa-user me-2"></i>Profil
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="index.php?page=settings">
                                        <i class="fas fa-cog me-2"></i>Pengaturan Akun
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger" href="#" onclick="confirmLogout(event)">
                                        <i class="fas fa-sign-out-alt me-2"></i>Keluar
                                    </a>
                                </li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item ms-2">
                            <button class="btn btn-appointment" onclick="confirmLogin()">
                                <i class="fas fa-calendar-plus"></i> Buat Janji
                            </button>
                        </li>
                        <li class="nav-item ms-2">
                            <a href="index.php?page=auth/login" class="btn btn-primary">
                                <i class="fas fa-sign-in-alt me-2"></i>Masuk
                            </a>
                        </li>
                        <li class="nav-item ms-2">
                            <a href="index.php?page=auth/register" class="btn btn-outline-primary">
                                <i class="fas fa-user-plus me-2"></i>Daftar
        </a>
    </li>
<?php endif; ?> 
                </ul>
            </div>
        </div>
    </nav>

    <!-- Login Confirmation Modal -->
    <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="loginModalLabel">Login Diperlukan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Anda harus login terlebih dahulu untuk membuat janji temu.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <a href="index.php?page=auth/login" class="btn btn-primary">Login Sekarang</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Logout Confirmation Modal -->
    <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="logoutModalLabel">Konfirmasi Logout</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin keluar?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <a href="index.php?page=auth/logout" class="btn btn-danger">Ya, Keluar</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Notification Container -->
    <?php if (isset($_SESSION['message'])): ?>
        <div class="notification-container position-fixed top-0 end-0 p-3" style="z-index: 1100;">
            <div class="toast show bg-<?php echo $_SESSION['message_type']; ?> text-white" role="alert">
                <div class="toast-header">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong class="me-auto">Notifikasi</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">
                    <?php 
                    echo $_SESSION['message'];
                    unset($_SESSION['message']);
                    unset($_SESSION['message_type']);
                    ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Main Content Container -->
    <div class="content-wrapper" style="margin-top: 76px;">

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inisialisasi semua dropdown
        var dropdowns = document.querySelectorAll('.dropdown-toggle');
        dropdowns.forEach(function(dropdown) {
            new bootstrap.Dropdown(dropdown);
        });

        // Inisialisasi semua modal
        var modals = document.querySelectorAll('.modal');
        modals.forEach(function(modal) {
            new bootstrap.Modal(modal);
        });

        // Active menu highlight
        var currentPage = window.location.search.split('page=')[1] || '';
        var navLinks = document.querySelectorAll('.nav-link');
        navLinks.forEach(function(link) {
            var href = link.getAttribute('href');
            if (href && href.includes(currentPage)) {
                link.classList.add('active');
            }
        });

        // Auto-hide notifications after 5 seconds
        var toasts = document.querySelectorAll('.toast');
        toasts.forEach(function(toast) {
            setTimeout(function() {
                var bsToast = new bootstrap.Toast(toast);
                bsToast.hide();
            }, 5000);
        });

        // Inisialisasi dropdown secara manual
        const dropdownToggle = document.getElementById('userDropdown');
        const dropdownMenu = dropdownToggle.nextElementSibling;
        
        dropdownToggle.addEventListener('click', function(e) {
            e.preventDefault();
            dropdownMenu.classList.toggle('show');
        });

        // Menutup dropdown saat mengklik di luar
        document.addEventListener('click', function(e) {
            if (!dropdownToggle.contains(e.target)) {
                dropdownMenu.classList.remove('show');
            }
        });

        // Menutup dropdown saat menekan tombol Escape
        document.addEventListener('keyup', function(e) {
            if (e.key === 'Escape') {
                dropdownMenu.classList.remove('show');
            }
        });
    });

    // Navbar scroll effect
    window.addEventListener('scroll', function() {
        var navbar = document.querySelector('.navbar');
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });

    // Fungsi untuk konfirmasi login
    function confirmLogin() {
        var loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
        loginModal.show();
    }

    // Fungsi untuk konfirmasi logout
    function confirmLogout(event) {
        event.preventDefault();
        var logoutModal = new bootstrap.Modal(document.getElementById('logoutModal'));
        logoutModal.show();
    }
    </script> 