<?php
require 'config/database.php';

if (!isLoggedIn()) {
    header('Location: index.php?page=login');
    exit;
}

try {
    // Get user data
    $query = "SELECT u.*, p.* FROM users u 
              LEFT JOIN pasien p ON u.id = p.user_id 
              WHERE u.id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $db->beginTransaction();
        
        // Update user data
        $email = $_POST['email'];
        $password = !empty($_POST['password']) ? 
                   password_hash($_POST['password'], PASSWORD_DEFAULT) : 
                   $user['password'];
        
        $query = "UPDATE users SET email = ?, password = ? WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$email, $password, $_SESSION['user_id']]);
        
        // Update patient data if exists
        if ($user['role'] == 'pasien') {
            $nama = $_POST['nama'];
            $alamat = $_POST['alamat'];
            $no_hp = $_POST['no_hp'];
            
            $query = "UPDATE pasien SET nama = ?, alamat = ?, no_hp = ? 
                     WHERE user_id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$nama, $alamat, $no_hp, $_SESSION['user_id']]);
        }
        
        $db->commit();
        $_SESSION['success'] = "Profil berhasil diperbarui!";
        header('Location: index.php?page=profile');
        exit;
        
    }
} catch(Exception $e) {
    if (isset($db)) $db->rollBack();
    error_log("Error in profile: " . $e->getMessage());
    $_SESSION['error'] = "Terjadi kesalahan saat memperbarui profil";
}
?>

<main class="container">
    <div class="profile-container">
        <h2>Edit Profil</h2>

        <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?= $_SESSION['success'] ?>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?= $_SESSION['error'] ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <form method="POST" class="profile-form">
                <?php if($user['role'] == 'pasien'): ?>
                    <div class="form-group">
                        <label>No. RM</label>
                        <input type="text" value="<?= htmlspecialchars($user['no_rm']) ?>" 
                               class="form-control" readonly>
                    </div>

                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" name="nama" value="<?= htmlspecialchars($user['nama']) ?>" 
                               class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Alamat</label>
                        <textarea name="alamat" class="form-control" required><?= htmlspecialchars($user['alamat']) ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>No. HP</label>
                        <input type="tel" name="no_hp" value="<?= htmlspecialchars($user['no_hp']) ?>" 
                               class="form-control" required>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label>Username</label>
                    <input type="text" value="<?= htmlspecialchars($user['username']) ?>" 
                           class="form-control" readonly>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" 
                           class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Password Baru (kosongkan jika tidak ingin mengubah)</label>
                    <input type="password" name="password" class="form-control">
                </div>

                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </form>
        </div>
    </div>
</main> 