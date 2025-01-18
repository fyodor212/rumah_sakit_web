<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Menggunakan path absolut dari root project
require_once __DIR__ . '/../../../../config/database.php';
require_once __DIR__ . '/../../../../config/functions.php';

// Redirect jika sudah login
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Validasi method request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['message'] = 'Metode request tidak valid';
    $_SESSION['message_type'] = 'danger';
    header('Location: index.php?page=auth/register');
    exit;
}

// Validasi input
$required_fields = ['nama', 'username', 'email', 'password', 'confirm_password', 'no_hp', 'alamat', 'role'];
$input = [];
$errors = [];

foreach ($required_fields as $field) {
    if ($field === 'password' || $field === 'confirm_password') {
        $input[$field] = trim($_POST[$field] ?? '');
    } else {
        $input[$field] = htmlspecialchars(trim($_POST[$field] ?? ''));
    }
    
    if (empty($input[$field])) {
        $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' harus diisi';
    }
}

// Validasi terms
if (!isset($_POST['terms'])) {
    $errors[] = 'Anda harus menyetujui syarat dan ketentuan';
}

// Validasi password match
if ($input['password'] !== $input['confirm_password']) {
    $errors[] = 'Password tidak cocok';
}

// Validasi email
if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Format email tidak valid';
}

// Validasi nomor HP (minimal 10 digit, maksimal 15 digit)
if (!preg_match('/^[0-9]{10,15}$/', $input['no_hp'])) {
    $errors[] = 'Format nomor HP tidak valid';
}

// Validasi role
if (!in_array($input['role'], ['patient', 'doctor'])) {
    $errors[] = 'Role tidak valid';
}

// Jika ada error, kembali ke form
if (!empty($errors)) {
    $_SESSION['message'] = implode('<br>', $errors);
    $_SESSION['message_type'] = 'danger';
    header('Location: index.php?page=auth/register');
    exit;
}

try {
    // Mulai transaksi
    $db->beginTransaction();

    // Cek username sudah digunakan atau belum
    $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$input['username']]);
    if ($stmt->fetch()) {
        throw new Exception('Username sudah digunakan');
    }

    // Cek email sudah digunakan atau belum
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$input['email']]);
    if ($stmt->fetch()) {
        throw new Exception('Email sudah digunakan');
    }

    // Hash password
    $hashed_password = password_hash($input['password'], PASSWORD_DEFAULT);

    // Insert user baru
    $stmt = $db->prepare("
        INSERT INTO users (username, email, password, role, status, created_at) 
        VALUES (?, ?, ?, ?, 'active', CURRENT_TIMESTAMP)
    ");
    
    $role = ($input['role'] === 'patient') ? 'pasien' : 'dokter';
    
    $stmt->execute([
        $input['username'],
        $input['email'],
        $hashed_password,
        $role
    ]);

    // Ambil id user yang baru dibuat
    $user_id = $db->lastInsertId();

    // Insert ke tabel sesuai role
    if ($role === 'pasien') {
        // Generate nomor rekam medis
        $stmt = $db->query("SELECT MAX(CAST(SUBSTRING(no_rm, 3) AS UNSIGNED)) as last_number FROM pasien");
        $result = $stmt->fetch();
        $last_number = $result['last_number'] ?? 0;
        $new_number = $last_number + 1;
        $no_rm = 'RM' . str_pad($new_number, 6, '0', STR_PAD_LEFT);

        $stmt = $db->prepare("
            INSERT INTO pasien (user_id, nama, no_hp, alamat, no_rm, created_at) 
            VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP)
        ");
        $stmt->execute([
            $user_id,
            $input['nama'],
            $input['no_hp'],
            $input['alamat'],
            $no_rm
        ]);
    } else {
        $stmt = $db->prepare("
            INSERT INTO dokter (user_id, nama, no_hp, alamat, status, created_at) 
            VALUES (?, ?, ?, ?, 'inactive', CURRENT_TIMESTAMP)
        ");
        $stmt->execute([
            $user_id,
            $input['nama'],
            $input['no_hp'],
            $input['alamat']
        ]);
    }

    // Commit transaksi
    $db->commit();

    // Set session
    $_SESSION['user_id'] = $user_id;
    $_SESSION['username'] = $input['username'];
    $_SESSION['role'] = $role;
    $_SESSION['nama'] = $input['nama'];

    // Redirect ke dashboard sesuai role
    $_SESSION['message'] = 'Registrasi berhasil! Selamat datang ' . $input['nama'];
    $_SESSION['message_type'] = 'success';
    
    if ($role === 'pasien') {
        header('Location: index.php?page=patient/dashboard');
    } else {
        $_SESSION['message'] = 'Registrasi berhasil! Akun Anda akan diaktifkan setelah diverifikasi admin.';
        header('Location: index.php?page=auth/login');
    }
    exit;

} catch (Exception $e) {
    // Rollback transaksi jika ada error
    $db->rollBack();
    
    error_log("Register error: " . $e->getMessage());
    $_SESSION['message'] = $e->getMessage();
    $_SESSION['message_type'] = 'danger';
    header('Location: index.php?page=auth/register');
    exit;
} 