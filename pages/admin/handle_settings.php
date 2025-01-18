<?php
// Pastikan user sudah login dan memiliki role admin
if (!isLoggedIn() || !isAdmin()) {
    header('Location: index.php?page=auth/login');
    exit;
}

// Pastikan request method adalah POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['message'] = "Metode request tidak valid.";
    $_SESSION['message_type'] = "danger";
    header('Location: index.php?page=admin/settings');
    exit;
}

try {
    // Validasi input
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $old_password = $_POST['old_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validasi field yang wajib diisi
    if (empty($username) || empty($email)) {
        throw new Exception("Username dan email wajib diisi.");
    }

    // Validasi format email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Format email tidak valid.");
    }

    // Mulai transaksi
    $db->beginTransaction();

    // Cek apakah username sudah digunakan (kecuali oleh user ini sendiri)
    $stmt = $db->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
    $stmt->execute([$username, $_SESSION['user_id']]);
    if ($stmt->fetch()) {
        throw new Exception("Username sudah digunakan.");
    }

    // Cek apakah email sudah digunakan (kecuali oleh user ini sendiri)
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$email, $_SESSION['user_id']]);
    if ($stmt->fetch()) {
        throw new Exception("Email sudah digunakan.");
    }

    // Update data di tabel users
    $query = "UPDATE users SET username = ?, email = ? WHERE id = ?";
    $params = [$username, $email, $_SESSION['user_id']];

    // Jika ada perubahan password
    if (!empty($new_password)) {
        // Validasi password lama
        $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $current_password = $stmt->fetchColumn();

        if (!password_verify($old_password, $current_password)) {
            throw new Exception("Password lama tidak sesuai.");
        }

        // Validasi password baru
        if (strlen($new_password) < 8) {
            throw new Exception("Password baru minimal 8 karakter.");
        }

        if ($new_password !== $confirm_password) {
            throw new Exception("Konfirmasi password tidak cocok.");
        }

        // Hash password baru
        $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
        $query = "UPDATE users SET username = ?, email = ?, password = ? WHERE id = ?";
        $params = [$username, $email, $password_hash, $_SESSION['user_id']];
    }

    // Eksekusi query update users
    $stmt = $db->prepare($query);
    $stmt->execute($params);

    // Commit transaksi
    $db->commit();

    // Update session data
    $_SESSION['username'] = $username;

    $_SESSION['message'] = "Pengaturan berhasil disimpan.";
    $_SESSION['message_type'] = "success";

} catch (Exception $e) {
    // Rollback transaksi jika terjadi error
    if ($db->inTransaction()) {
        $db->rollBack();
    }

    error_log("Error in handle_settings.php: " . $e->getMessage());
    $_SESSION['message'] = $e->getMessage();
    $_SESSION['message_type'] = "danger";
}

// Redirect kembali ke halaman settings
header('Location: index.php?page=admin/settings');
exit; 