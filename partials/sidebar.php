<!-- Menu Admin -->
<ul class="navbar-nav">
    <li class="nav-item">
        <a class="nav-link <?= $page === 'admin/dashboard' ? 'active' : '' ?>" 
           href="index.php?page=admin/dashboard">
            <i class="fas fa-tachometer-alt fa-fw me-2"></i>
            <span>Dashboard</span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link <?= $page === 'admin/manage_bookings' ? 'active' : '' ?>" 
           href="index.php?page=admin/manage_bookings">
            <i class="fas fa-calendar-check fa-fw me-2"></i>
            <span>Kelola Booking</span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link <?= $page === 'admin/manage_doctors' ? 'active' : '' ?>" 
           href="index.php?page=admin/manage_doctors">
            <i class="fas fa-user-md fa-fw me-2"></i>
            <span>Kelola Dokter</span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link <?= $page === 'admin/manage_patients' ? 'active' : '' ?>" 
           href="index.php?page=admin/manage_patients">
            <i class="fas fa-users fa-fw me-2"></i>
            <span>Kelola Pasien</span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link <?= $page === 'admin/manage_users' ? 'active' : '' ?>" 
           href="index.php?page=admin/manage_users">
            <i class="fas fa-user-shield fa-fw me-2"></i>
            <span>Kelola Users</span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link <?= $page === 'admin/manage_payments' ? 'active' : '' ?>" 
           href="index.php?page=admin/manage_payments">
            <i class="fas fa-money-bill-wave fa-fw me-2"></i>
            <span>Kelola Pembayaran</span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link <?= $page === 'admin/manage_messages' ? 'active' : '' ?>" 
           href="index.php?page=admin/manage_messages">
            <i class="fas fa-envelope fa-fw me-2"></i>
            <span>Kelola Pesan</span>
        </a>
    </li>

    <hr class="dropdown-divider">

    <li class="nav-item">
        <a class="nav-link <?= $page === 'admin/settings' ? 'active' : '' ?>" 
           href="index.php?page=admin/settings">
            <i class="fas fa-cog fa-fw me-2"></i>
            <span>Pengaturan Akun</span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link text-danger" href="index.php?page=logout" 
           onclick="return confirm('Apakah Anda yakin ingin keluar?')">
            <i class="fas fa-sign-out-alt fa-fw me-2"></i>
            <span>Keluar</span>
        </a>
    </li>
</ul> 