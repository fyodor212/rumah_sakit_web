<?php
function requireAdmin() {
    if (!isAdmin()) {
        $_SESSION['message'] = "Anda tidak memiliki akses ke halaman tersebut";
        $_SESSION['message_type'] = 'danger';
        header('Location: index.php');
        exit();
    }
} 