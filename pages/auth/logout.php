<?php
// Pastikan session sudah dimulai
session_start();

// Simpan username untuk pesan
$username = $_SESSION['username'] ?? '';

// Hapus semua data session
$_SESSION = array();

// Hapus cookie session jika ada
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Hancurkan session
session_destroy();

// Hapus cookie remember me jika ada
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

// Mulai session baru untuk pesan logout
session_start();
$_SESSION['message'] = "Anda telah berhasil logout.";
$_SESSION['message_type'] = "success";

// Redirect ke halaman login
header("Location: index.php?page=auth/login");
exit;
?> 