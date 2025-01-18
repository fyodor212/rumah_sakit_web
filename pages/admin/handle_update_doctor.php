<?php
// Session sudah di-start di index.php
require_once 'config/database.php';
require_once 'app/Helpers/helpers.php';

if (!isAdmin()) {
    header('Location: index.php');
    exit;
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Method tidak valid");
    }

    // Debug
    error_log("POST Data: " . print_r($_POST, true));

    // Ambil dan validasi input
    $doctor_id = filter_input(INPUT_POST, 'doctor_id', FILTER_VALIDATE_INT);
    if (!$doctor_id) {
        throw new Exception("ID dokter tidak valid");
    }

    $new_status = $_POST['status'] ?? '';
    if (!in_array($new_status, ['aktif', 'tidak aktif'])) {
        throw new Exception("Status tidak valid");
    }

    $filter_spesialisasi = $_POST['filter_spesialisasi'] ?? '';
    $filter_status = $_POST['filter_status'] ?? '';

    // Cek apakah dokter ada
    $check_query = "SELECT id FROM dokter WHERE id = ?";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->execute([$doctor_id]);
    
    if (!$check_stmt->fetch()) {
        throw new Exception("Tidak ada dokter dengan ID: $doctor_id");
    }

    // Update status
    $update_query = "UPDATE dokter SET status = ? WHERE id = ?";
    $update_stmt = $db->prepare($update_query);
    
    if (!$update_stmt->execute([$new_status, $doctor_id])) {
        throw new Exception("Gagal mengupdate status dokter");
    }

    // Redirect dengan success
    $redirect_url = 'index.php?page=admin/manage_doctors&alert_status=success';
    if (!empty($filter_spesialisasi)) {
        $redirect_url .= '&spesialisasi=' . urlencode($filter_spesialisasi);
    }
    if (!empty($filter_status)) {
        $redirect_url .= '&status=' . urlencode($filter_status);
    }
    
    header('Location: ' . $redirect_url);
    exit;

} catch (Exception $e) {
    error_log("Error in handle_update_doctor.php: " . $e->getMessage());
    header('Location: index.php?page=admin/manage_doctors&alert_status=error&message=' . urlencode($e->getMessage()));
    exit;
} 