<?php
// Pastikan user sudah login dan memiliki role admin
if (!isLoggedIn() || !isAdmin()) {
    header('Location: index.php?page=auth/login');
    exit;
}

// Ambil data user yang sedang login
try {
    $query = "SELECT * FROM users WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception("Data user tidak ditemukan.");
    }

} catch (Exception $e) {
    error_log("Error fetching user data: " . $e->getMessage());
    $_SESSION['message'] = "Terjadi kesalahan saat mengambil data user.";
    $_SESSION['message_type'] = "danger";
    header('Location: index.php?page=admin/dashboard');
    exit;
}
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-cog me-2"></i>Pengaturan Akun
                    </h5>
                </div>
                <div class="card-body">
                    <form id="settingsForm" action="index.php?page=admin/handle_settings" method="POST" class="needs-validation" novalidate>
                        <!-- Username -->
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" 
                                   value="<?= htmlspecialchars($user['username']) ?>" required>
                            <div class="invalid-feedback">
                                Username tidak boleh kosong
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?= htmlspecialchars($user['email']) ?>" required>
                            <div class="invalid-feedback">
                                Email tidak valid
                            </div>
                        </div>

                        <hr>

                        <!-- Password Lama -->
                        <div class="mb-3">
                            <label for="old_password" class="form-label">Password Lama</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="old_password" name="old_password">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('old_password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <small class="text-muted">Kosongkan jika tidak ingin mengubah password</small>
                        </div>

                        <!-- Password Baru -->
                        <div class="mb-3">
                            <label for="new_password" class="form-label">Password Baru</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="new_password" name="new_password">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('new_password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <small class="text-muted">Minimal 8 karakter</small>
                        </div>

                        <!-- Konfirmasi Password -->
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Konfirmasi Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirm_password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const icon = input.nextElementSibling.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Form validation
(function () {
    'use strict'
    const form = document.getElementById('settingsForm')
    
    form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) {
            event.preventDefault()
            event.stopPropagation()
        }
        
        // Password validation
        const newPassword = document.getElementById('new_password')
        const confirmPassword = document.getElementById('confirm_password')
        
        if (newPassword.value || confirmPassword.value) {
            if (!document.getElementById('old_password').value) {
                event.preventDefault()
                showAlert('Error', 'Password lama harus diisi untuk mengubah password', 'error')
                return
            }
            
            if (newPassword.value.length < 8) {
                event.preventDefault()
                showAlert('Error', 'Password baru minimal 8 karakter', 'error')
                return
            }
            
            if (newPassword.value !== confirmPassword.value) {
                event.preventDefault()
                showAlert('Error', 'Konfirmasi password tidak cocok', 'error')
                return
            }
        }
        
        form.classList.add('was-validated')
    }, false)
})()
</script> 