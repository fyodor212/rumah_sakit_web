<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once '../app/Helpers/helpers.php';
    require_once '../config/database.php';

    session_start();

    if (!isAdmin()) {
        throw new Exception("Akses ditolak: Bukan admin");
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Method tidak valid");
    }

    $doctor_id = $_POST['doctor_id'] ?? '';
    $new_status = $_POST['status'] ?? '';
    $filter_spesialisasi = $_POST['filter_spesialisasi'] ?? '';
    $filter_status = $_POST['filter_status'] ?? '';

    // Debug
    error_log("POST Data: " . print_r($_POST, true));

    if (empty($doctor_id) || empty($new_status)) {
        throw new Exception("Data tidak lengkap: doctor_id=$doctor_id, status=$new_status");
    }

    // Konversi status ke format yang sesuai dengan database
    $db_status = ($new_status === 'active') ? 'aktif' : 'tidak aktif';

    $query = "UPDATE dokter SET status = ? WHERE id = ?";
    $stmt = $db->prepare($query);
    
    error_log("Executing query: $query with params: " . json_encode([$db_status, $doctor_id]));
    
    if (!$stmt->execute([$db_status, $doctor_id])) {
        throw new Exception("Query gagal: " . implode(", ", $stmt->errorInfo()));
    }

    if ($stmt->rowCount() === 0) {
        throw new Exception("Tidak ada dokter dengan ID: $doctor_id");
    }

    // Redirect dengan success
    $redirect_url = '../index.php?page=admin/manage_doctors&alert_status=success';
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
    error_log("Stack trace: " . $e->getTraceAsString());
    
    header('Location: ../index.php?page=admin/manage_doctors&alert_status=error&message=' . urlencode($e->getMessage()));
    exit;
} 