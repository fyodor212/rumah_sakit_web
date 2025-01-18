<?php
// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if not logged in as patient
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pasien') {
    $_SESSION['message'] = 'Anda harus login sebagai pasien untuk mengakses halaman ini';
    $_SESSION['message_type'] = 'warning';
    header('Location: index.php?page=auth/login');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pasien - Klinik</title>
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    
    <!-- JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    
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
            background-color: var(--light-color);
            padding-top: 76px;
        }

        .navbar {
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,.1);
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            font-weight: 700;
            color: var(--primary-color);
        }

        .navbar-brand i {
            font-size: 1.5rem;
            margin-right: 0.5rem;
        }

        .nav-link {
            font-weight: 500;
            padding: 0.5rem 1rem;
            color: var(--dark-color);
        }

        .nav-link:hover {
            color: var(--primary-color);
        }

        .nav-link.active {
            color: var(--primary-color);
            position: relative;
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

        .content {
            padding: 1.5rem;
        }

        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,.1);
            margin-bottom: 1rem;
        }

        .card-header {
            background-color: white;
            border-bottom: 1px solid rgba(0,0,0,.125);
            padding: 1rem;
        }

        .card-body {
            padding: 1rem;
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
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link <?= $page === 'patient/dashboard' ? 'active' : '' ?>" href="index.php?page=patient/dashboard">
                            <i class="fas fa-home"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $page === 'patient/book_appointment' ? 'active' : '' ?>" href="index.php?page=patient/book_appointment">
                            <i class="fas fa-calendar-plus"></i> Buat Janji
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $page === 'patient/my_appointments' ? 'active' : '' ?>" href="index.php?page=patient/my_appointments">
                            <i class="fas fa-calendar-check"></i> Janji Temu Saya
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $page === 'patient/medical_records' ? 'active' : '' ?>" href="index.php?page=patient/medical_records">
                            <i class="fas fa-notes-medical"></i> Rekam Medis
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-2"></i><?php echo htmlspecialchars($_SESSION['nama'] ?? $_SESSION['username']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="index.php?page=patient/profile">
                                    <i class="fas fa-user me-2"></i>Profil
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
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main content -->
    <div class="container">
        <div class="content"> 