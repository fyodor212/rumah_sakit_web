<?php
require_once 'config/database.php';
require_once 'config/functions.php';

// Cek apakah user adalah admin
if (!isAdmin()) {
    header('Location: index.php?page=auth/login');
    exit;
}

// Ambil data admin
try {
    $query = "SELECT * FROM users WHERE id = ? AND role = 'admin'";
    $stmt = $db->prepare($query);
    $stmt->execute([$_SESSION['user_id']]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin) {
        header('Location: index.php?page=auth/login');
        exit;
    }
} catch (PDOException $e) {
    $error = "Error: " . $e->getMessage();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    try {
        // Validasi password saat ini
        if (!empty($current_password)) {
            if (!password_verify($current_password, $admin['password'])) {
                $error = "Password saat ini tidak sesuai!";
            } elseif ($new_password !== $confirm_password) {
                $error = "Password baru dan konfirmasi password tidak cocok!";
            } else {
                // Update dengan password baru
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $query = "UPDATE users SET username = ?, email = ?, password = ? WHERE id = ?";
                $stmt = $db->prepare($query);
                $stmt->execute([$username, $email, $hashed_password, $_SESSION['user_id']]);
            }
        } else {
            // Update tanpa password
            $query = "UPDATE users SET username = ?, email = ? WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$username, $email, $_SESSION['user_id']]);
        }

        if (!isset($error)) {
            $_SESSION['success'] = "Profil berhasil diperbarui!";
            header('Location: index.php?page=admin/profile');
            exit;
        }
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Profil Admin</h5>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= $_SESSION['success'] ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php unset($_SESSION['success']); ?>
                    <?php endif; ?>

                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger">
                            <?= $error ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" 
                                   value="<?= htmlspecialchars($admin['username']) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?= htmlspecialchars($admin['email']) ?>" required>
                        </div>

                        <hr class="my-4">
                        <h6 class="mb-3">Ubah Password</h6>

                        <div class="mb-3">
                            <label for="current_password" class="form-label">Password Saat Ini</label>
                            <input type="password" class="form-control" id="current_password" 
                                   name="current_password">
                            <div class="form-text">Kosongkan jika tidak ingin mengubah password</div>
                        </div>

                        <div class="mb-3">
                            <label for="new_password" class="form-label">Password Baru</label>
                            <input type="password" class="form-control" id="new_password" 
                                   name="new_password">
                        </div>

                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Konfirmasi Password Baru</label>
                            <input type="password" class="form-control" id="confirm_password" 
                                   name="confirm_password">
                        </div>

                        <div class="d-grid">
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

<style>
.card {
    border: none;
    box-shadow: 0 0 15px rgba(0,0,0,.05);
}

.card-header {
    background-color: #fff;
    border-bottom: 1px solid #eee;
    padding: 15px 20px;
}

.form-label {
    font-weight: 500;
    color: #333;
}

.form-control {
    padding: 0.75rem 1rem;
    border-color: #e0e0e0;
}

.form-control:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13,110,253,.25);
}

.form-text {
    color: #6c757d;
    font-size: 0.875rem;
}

.btn-primary {
    padding: 0.75rem 1.5rem;
    font-weight: 500;
}

.alert {
    border-radius: 8px;
}

hr {
    opacity: 0.15;
}

h6 {
    color: #333;
    font-weight: 600;
}
</style>

<script>
// Validasi password baru dan konfirmasi harus diisi keduanya
document.getElementById('new_password').addEventListener('input', validatePassword);
document.getElementById('confirm_password').addEventListener('input', validatePassword);

function validatePassword() {
    var newPass = document.getElementById('new_password').value;
    var confirmPass = document.getElementById('confirm_password').value;
    
    if (newPass || confirmPass) {
        document.getElementById('current_password').required = true;
    } else {
        document.getElementById('current_password').required = false;
    }
}
</script> 