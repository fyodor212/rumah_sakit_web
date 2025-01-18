<?php include 'app/Views/templates/header.php'; ?>

<!-- Main Content -->
<div class="main-content">
    <!-- Hero Section -->
    <div class="hero-section position-relative">
        <div class="hero-overlay" style="background: linear-gradient(135deg, rgba(13, 110, 253, 0.9), rgba(0, 0, 0, 0.8)), url('public/images/hero-bg.jpg') no-repeat center center; background-size: cover; height: 100vh;">
            <div class="container h-100">
                <div class="row h-100 align-items-center">
                    <div class="col-lg-7">
                        <div class="text-white animate__animated animate__fadeIn">
                            <h1 class="display-4 fw-bold mb-4">Selamat Datang di<br>Klinik</h1>
                            <p class="lead mb-4 opacity-90">Kami menyediakan layanan kesehatan berkualitas dengan dokter-dokter terpercaya dan fasilitas modern untuk kesehatan Anda.</p>
                            <div class="d-flex gap-3">
                                <a href="index.php?page=auth/register" class="btn btn-light btn-lg px-4 shadow-sm">Daftar Sekarang</a>
                                <a href="index.php?page=auth/login" class="btn btn-outline-light btn-lg px-4">Masuk</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Wave Shape -->
        <div class="wave-shape">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320">
                <path fill="#ffffff" fill-opacity="1" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,112C672,96,768,96,864,112C960,128,1056,160,1152,160C1248,160,1344,128,1392,112L1440,96L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
            </svg>
        </div>
    </div>

    <!-- Features Section -->
    <div class="container py-5">
        <div class="row justify-content-center mb-5">
            <div class="col-lg-8 text-center">
                <h2 class="section-title fw-bold mb-3">Mengapa Memilih Kami?</h2>
                <p class="section-subtitle text-muted">Kami berkomitmen untuk memberikan pelayanan kesehatan terbaik dengan standar kualitas tertinggi.</p>
            </div>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card feature-card h-100 border-0 shadow-hover">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon-wrapper mb-3">
                            <div class="feature-icon-bg">
                                <i class="fas fa-user-md"></i>
                            </div>
                        </div>
                        <h4 class="card-title mb-3">Dokter Profesional</h4>
                        <p class="card-text text-muted">Tim dokter berpengalaman dan bersertifikasi siap memberikan pelayanan terbaik untuk Anda.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card feature-card h-100 border-0 shadow-hover">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon-wrapper mb-3">
                            <div class="feature-icon-bg">
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>
                        <h4 class="card-title mb-3">Layanan 24 Jam</h4>
                        <p class="card-text text-muted">Siap melayani kebutuhan kesehatan Anda kapanpun dengan pelayanan yang responsif.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card feature-card h-100 border-0 shadow-hover">
                    <div class="card-body text-center p-4">
                        <div class="feature-icon-wrapper mb-3">
                            <div class="feature-icon-bg">
                                <i class="fas fa-hospital"></i>
                            </div>
                        </div>
                        <h4 class="card-title mb-3">Fasilitas Modern</h4>
                        <p class="card-text text-muted">Dilengkapi dengan peralatan medis modern untuk menunjang pelayanan kesehatan optimal.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Services Section -->
    <div class="section-bg py-5">
        <div class="container">
            <div class="row justify-content-center mb-5">
                <div class="col-lg-8 text-center">
                    <h2 class="section-title fw-bold mb-3">Layanan Kami</h2>
                    <p class="section-subtitle text-muted">Berbagai layanan kesehatan yang kami sediakan untuk memenuhi kebutuhan Anda.</p>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-md-6 col-lg-4">
                    <div class="card service-card border-0 shadow-hover">
                        <div class="card-body p-4">
                            <div class="service-icon mb-3">
                                <i class="fas fa-stethoscope text-primary"></i>
                            </div>
                            <h5 class="card-title mb-3">Konsultasi Umum</h5>
                            <p class="card-text text-muted">Layanan konsultasi kesehatan umum dengan dokter berpengalaman.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="card service-card border-0 shadow-hover">
                        <div class="card-body p-4">
                            <div class="service-icon mb-3">
                                <i class="fas fa-flask text-primary"></i>
                            </div>
                            <h5 class="card-title mb-3">Pemeriksaan Laboratorium</h5>
                            <p class="card-text text-muted">Fasilitas laboratorium lengkap untuk berbagai jenis pemeriksaan.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="card service-card border-0 shadow-hover">
                        <div class="card-body p-4">
                            <div class="service-icon mb-3">
                                <i class="fas fa-ambulance text-primary"></i>
                            </div>
                            <h5 class="card-title mb-3">Layanan Gawat Darurat</h5>
                            <p class="card-text text-muted">Penanganan cepat untuk kasus gawat darurat 24 jam.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CTA Section -->
    <div class="cta-section py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card cta-card border-0 shadow text-center p-5">
                        <h2 class="fw-bold mb-4">Siap Untuk Mendaftar?</h2>
                        <p class="lead mb-4">Bergabunglah dengan kami dan dapatkan pelayanan kesehatan terbaik untuk Anda dan keluarga.</p>
                        <div class="d-flex justify-content-center gap-3">
                            <a href="index.php?page=auth/register" class="btn btn-primary btn-lg px-5 shadow-sm">Daftar Sekarang</a>
                            <a href="index.php?page=auth/login" class="btn btn-outline-primary btn-lg px-5">Masuk</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.hero-section {
    margin-top: -80px; /* Adjust based on your navbar height */
}

.wave-shape {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    overflow: hidden;
    line-height: 0;
}

.feature-icon-bg {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #e3f2fd, #bbdefb);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    transition: all 0.3s ease;
}

.feature-icon-bg i {
    font-size: 2rem;
    color: #0d6efd;
}

.shadow-hover {
    transition: all 0.3s ease;
}

.shadow-hover:hover {
    transform: translateY(-5px);
    box-shadow: 0 1rem 3rem rgba(0,0,0,.175)!important;
}

.service-icon i {
    font-size: 2.5rem;
}

.section-bg {
    background-color: #f8f9fa;
}

.section-title {
    position: relative;
    padding-bottom: 15px;
}

.section-title:after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 50px;
    height: 3px;
    background: #0d6efd;
}

.cta-section {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.cta-card {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
}

.feature-card:hover .feature-icon-bg {
    background: linear-gradient(135deg, #0d6efd, #0043a8);
}

.feature-card:hover .feature-icon-bg i {
    color: #ffffff;
}

.service-card {
    overflow: hidden;
}

.service-card::before {
    content: '';
    position: absolute;
    top: -100%;
    left: 0;
    width: 100%;
    height: 3px;
    background: #0d6efd;
    transition: all 0.3s ease;
}

.service-card:hover::before {
    top: 0;
}
</style>

<?php include 'app/Views/templates/footer.php'; ?> 