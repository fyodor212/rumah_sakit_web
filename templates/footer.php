    </div> <!-- End of content-wrapper -->

    <!-- Footer -->
    <footer class="footer bg-dark text-light py-5 mt-5">
        <div class="container">
            <div class="row g-4">
                <!-- Klinik Info -->
                <div class="col-lg-4">
                    <div class="footer-brand mb-4">
                        <i class="fas fa-hospital-alt"></i>
                        <span class="ms-2">KLINIK</span>
                    </div>
                    <p class="mb-4">Memberikan pelayanan kesehatan terbaik dengan dokter-dokter profesional dan fasilitas modern untuk kesehatan Anda dan keluarga.</p>
                    <div class="social-links">
                        <a href="#" class="text-light me-3 social-icon"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-light me-3 social-icon"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-light me-3 social-icon"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-light social-icon"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="col-lg-2">
                    <h5 class="footer-title mb-4">Menu Utama</h5>
                    <ul class="list-unstyled footer-links">
                        <li class="mb-2">
                            <a href="index.php" class="text-light text-decoration-none footer-link">
                                <i class="fas fa-chevron-right me-2"></i>Beranda
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="index.php?page=doctors" class="text-light text-decoration-none footer-link">
                                <i class="fas fa-chevron-right me-2"></i>Dokter
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="index.php?page=services" class="text-light text-decoration-none footer-link">
                                <i class="fas fa-chevron-right me-2"></i>Layanan
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="index.php?page=contact" class="text-light text-decoration-none footer-link">
                                <i class="fas fa-chevron-right me-2"></i>Kontak
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Services -->
                <div class="col-lg-3">
                    <h5 class="footer-title mb-4">Layanan Kami</h5>
                    <ul class="list-unstyled footer-links">
                        <li class="mb-2">
                            <a href="#" class="text-light text-decoration-none footer-link">
                                <i class="fas fa-chevron-right me-2"></i>Konsultasi Umum
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="#" class="text-light text-decoration-none footer-link">
                                <i class="fas fa-chevron-right me-2"></i>Pemeriksaan Laboratorium
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="#" class="text-light text-decoration-none footer-link">
                                <i class="fas fa-chevron-right me-2"></i>Layanan Gawat Darurat
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="#" class="text-light text-decoration-none footer-link">
                                <i class="fas fa-chevron-right me-2"></i>Medical Check-up
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Contact Info -->
                <div class="col-lg-3">
                    <h5 class="footer-title mb-4">Informasi Kontak</h5>
                    <ul class="list-unstyled footer-contact">
                        <li class="mb-3">
                            <i class="fas fa-map-marker-alt me-2 contact-icon"></i>
                            Jl. Contoh No. 123, Kota, Indonesia
                        </li>
                        <li class="mb-3">
                            <i class="fas fa-phone-alt me-2 contact-icon"></i>
                            (021) 1234-5678
                        </li>
                        <li class="mb-3">
                            <i class="fas fa-envelope me-2 contact-icon"></i>
                            info@klinikabc.com
                        </li>
                        <li class="mb-3">
                            <i class="fas fa-clock me-2 contact-icon"></i>
                            Senin - Minggu: 24 Jam
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Copyright -->
            <div class="border-top border-secondary pt-4 mt-4">
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-0">&copy; 2024 Klinik. All rights reserved.</p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <a href="#" class="text-light text-decoration-none me-3 footer-link">Kebijakan Privasi</a>
                        <a href="#" class="text-light text-decoration-none footer-link">Syarat & Ketentuan</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <style>
    .footer {
        position: relative;
        background: linear-gradient(135deg, #212529, #343a40);
    }

    .footer-brand {
        font-size: 1.8rem;
        font-weight: 700;
        color: white;
    }

    .footer-brand i {
        font-size: 2rem;
        background: linear-gradient(135deg, #fff, #ccc);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .footer-title {
        position: relative;
        padding-bottom: 10px;
    }

    .footer-title::after {
        content: '';
        position: absolute;
        left: 0;
        bottom: 0;
        width: 50px;
        height: 2px;
        background: var(--primary-color);
    }

    .footer-link {
        transition: all 0.3s ease;
        opacity: 0.8;
    }

    .footer-link:hover {
        opacity: 1;
        padding-left: 10px;
        color: var(--primary-color) !important;
    }

    .social-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 35px;
        height: 35px;
        border-radius: 50%;
        background: rgba(255,255,255,0.1);
        transition: all 0.3s ease;
    }

    .social-icon:hover {
        background: var(--primary-color);
        transform: translateY(-3px);
    }

    .contact-icon {
        color: var(--primary-color);
    }

    .footer-contact li {
        display: flex;
        align-items: center;
    }
    </style>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Custom Scripts -->
    <script>
        // Navbar Scroll Effect
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Auto-hide notifications
        window.addEventListener('DOMContentLoaded', (event) => {
            const toasts = document.querySelectorAll('.toast');
            toasts.forEach(toast => {
                setTimeout(() => {
                    toast.classList.remove('show');
                }, 5000);
            });
        });

        // Active Link Highlight
        document.addEventListener('DOMContentLoaded', function() {
            const currentLocation = location.href;
            const navLinks = document.querySelectorAll('.nav-link');
            navLinks.forEach(link => {
                if (link.href === currentLocation) {
                    link.classList.add('active');
                }
            });
        });
    </script>
</body>
</html> 