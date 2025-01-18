<?php
// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - Klinik</title>
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            min-height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .container {
            width: 100%;
            padding: 1rem;
        }

        .register-container {
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,.1);
        }

        .card-header {
            background: linear-gradient(135deg, #0d6efd, #0043a8);
            color: white;
            text-align: center;
            padding: 1rem;
            border-radius: 15px 15px 0 0 !important;
            border: none;
        }

        .logo-icon {
            font-size: 2rem;
            margin-bottom: 0.25rem;
        }

        .card-header h4 {
            margin: 0;
            font-size: 1.25rem;
        }

        .form-control {
            border-radius: 8px;
            padding: 0.75rem 1rem;
            border: 1px solid #dee2e6;
        }

        .form-control:focus {
            box-shadow: 0 0 0 0.2rem rgba(13,110,253,.15);
            border-color: #0d6efd;
        }

        .btn-primary {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            background: linear-gradient(135deg, #0d6efd, #0043a8);
            border: none;
            width: 100%;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #0043a8, #0d6efd);
            transform: translateY(-1px);
        }

        .back-link {
            color: #6c757d;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            margin-bottom: 1rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .back-link:hover {
            color: #0d6efd;
            transform: translateX(-3px);
        }

        .back-link i {
            margin-right: 0.5rem;
        }

        .login-link {
            text-align: center;
            margin-top: 1rem;
        }

        .login-link a {
            color: #0d6efd;
            text-decoration: none;
            font-weight: 500;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        .alert {
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .input-group-text {
            border-radius: 8px 0 0 8px;
            background: #f8f9fa;
        }

        .password-toggle {
            border-radius: 0 8px 8px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="register-container">
            <a href="index.php" class="back-link">
                <i class="fas fa-arrow-left"></i>
                Kembali ke Beranda
            </a>

            <div class="card">
                <div class="card-header">
                    <i class="fas fa-hospital-alt logo-icon"></i>
                    <h4 class="mb-0">Daftar Akun</h4>
                </div>
                <div class="card-body p-4">
                    <?php if (isset($_SESSION['message'])): ?>
                        <div class="alert alert-<?= $_SESSION['message_type'] ?> alert-dismissible fade show">
                            <?= $_SESSION['message'] ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php 
                        unset($_SESSION['message']);
                        unset($_SESSION['message_type']);
                        ?>
                    <?php endif; ?>

                    <form action="index.php?page=auth/handle_register" method="POST" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama Lengkap</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-user"></i>
                                </span>
                                <input type="text" class="form-control" id="nama" name="nama" required>
                                <div class="invalid-feedback">Nama lengkap harus diisi</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-user-circle"></i>
                                </span>
                                <input type="text" class="form-control" id="username" name="username" required>
                                <div class="invalid-feedback">Username harus diisi</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-envelope"></i>
                                </span>
                                <input type="email" class="form-control" id="email" name="email" required>
                                <div class="invalid-feedback">Email harus diisi dengan format yang benar</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="no_hp" class="form-label">Nomor HP</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-phone"></i>
                                </span>
                                <input type="tel" class="form-control" id="no_hp" name="no_hp" 
                                       pattern="[0-9]{10,15}" required>
                                <div class="invalid-feedback">Nomor HP harus diisi (10-15 digit)</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="alamat" class="form-label">Alamat</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-map-marker-alt"></i>
                                </span>
                                <textarea class="form-control" id="alamat" name="alamat" rows="2" required></textarea>
                                <div class="invalid-feedback">Alamat harus diisi</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <button class="btn btn-outline-secondary password-toggle" type="button">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <div class="invalid-feedback">Password harus diisi</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Konfirmasi Password</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                <button class="btn btn-outline-secondary password-toggle" type="button">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <div class="invalid-feedback">Konfirmasi password harus diisi</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="role" class="form-label">Daftar Sebagai</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-user-tag"></i>
                                </span>
                                <select class="form-select" id="role" name="role" required>
                                    <option value="">Pilih Role</option>
                                    <option value="patient">Pasien</option>
                                    <option value="doctor">Dokter</option>
                                </select>
                                <div class="invalid-feedback">Pilih role Anda</div>
                            </div>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                            <label class="form-check-label" for="terms">
                                Saya setuju dengan syarat dan ketentuan
                            </label>
                            <div class="invalid-feedback">
                                Anda harus menyetujui syarat dan ketentuan
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-user-plus me-2"></i>Daftar
                        </button>

                        <div class="login-link">
                            Sudah punya akun? 
                            <a href="index.php?page=auth/login">Login sekarang</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    // Form validation
    (function() {
        'use strict';
        var forms = document.querySelectorAll('.needs-validation');
        Array.prototype.slice.call(forms).forEach(function(form) {
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    })();

    // Toggle password visibility
    document.querySelectorAll('.password-toggle').forEach(function(button) {
        button.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const icon = this.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });

    // Password match validation
    document.getElementById('confirm_password').addEventListener('input', function() {
        const password = document.getElementById('password');
        if (this.value !== password.value) {
            this.setCustomValidity('Password tidak cocok');
        } else {
            this.setCustomValidity('');
        }
    });
    </script>
</body>
</html>