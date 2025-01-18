<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get patient ID first
        $get_patient = $conn->prepare("SELECT id FROM pasien WHERE user_id = ?");
        $get_patient->bind_param("i", $_SESSION['user_id']);
        $get_patient->execute();
        $patient_result = $get_patient->get_result();
        
        if ($patient_result->num_rows > 0) {
            $patient = $patient_result->fetch_assoc();
            $patient_id = $patient['id'];

            // Validasi input
            if (empty($_POST['layanan_id']) || empty($_POST['dokter_id']) || 
                empty($_POST['tanggal']) || empty($_POST['jam']) || empty($_POST['keluhan'])) {
                throw new Exception("Semua field harus diisi");
            }

                // Generate nomor antrian
            $check_antrian = $conn->prepare("
                SELECT COUNT(*) as total 
                FROM booking 
                WHERE dokter_id = ? AND tanggal = ? AND jam = ?
            ");
            $check_antrian->bind_param("iss", $_POST['dokter_id'], $_POST['tanggal'], $_POST['jam']);
            $check_antrian->execute();
            $result = $check_antrian->get_result();
            $antrian = $result->fetch_assoc()['total'] + 1;

            // Get harga layanan
            $get_harga = $conn->prepare("SELECT harga FROM layanan WHERE id = ?");
            $get_harga->bind_param("i", $_POST['layanan_id']);
            $get_harga->execute();
            $harga_result = $get_harga->get_result();
            $harga = $harga_result->fetch_assoc()['harga'];

            // Biaya admin (contoh: 10% dari harga layanan)
            $biaya_admin = $harga * 0.1;
            $total_biaya = $harga + $biaya_admin;

            // Insert booking
            $stmt = $conn->prepare("
                INSERT INTO booking (
                    pasien_id, dokter_id, layanan_id, keluhan, 
                    tanggal, jam, status, no_antrian, total_biaya, 
                    created_at
                ) VALUES (?, ?, ?, ?, ?, ?, 'pending', ?, ?, NOW())
            ");

            $stmt->bind_param(
                "iiisssid",
                $patient_id,
                    $_POST['dokter_id'],
                    $_POST['layanan_id'],
                    $_POST['keluhan'],
                    $_POST['tanggal'],
                    $_POST['jam'],
                $antrian,
                $total_biaya
            );

            if ($stmt->execute()) {
                $_SESSION['message'] = "Janji temu berhasil dibuat! Nomor antrian Anda: " . $antrian;
                $_SESSION['message_type'] = 'success';
                header("Location: ../../index.php?page=patient/my_appointments");
                exit();
            } else {
                throw new Exception("Gagal menyimpan janji temu");
            }
        } else {
            throw new Exception("Data pasien tidak ditemukan");
        }

        } catch (Exception $e) {
        error_log("Error in booking: " . $e->getMessage());
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = 'danger';
        header("Location: ../../index.php?page=patient/book_appointment");
            exit();
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'cancel') {
        try {
            if (!isset($_GET['id'])) {
                throw new Exception("ID janji temu tidak valid");
            }

        $stmt = $conn->prepare("
            UPDATE booking 
            SET status = 'cancelled', 
                updated_at = NOW() 
            WHERE id = ? AND pasien_id = (
                SELECT id FROM pasien WHERE user_id = ?
            ) AND status = 'pending'
        ");

        $stmt->bind_param("ii", $_GET['id'], $_SESSION['user_id']);
        
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $_SESSION['message'] = "Janji temu berhasil dibatalkan";
            $_SESSION['message_type'] = 'success';
        } else {
                    throw new Exception("Gagal membatalkan janji temu");
                }

            } catch (Exception $e) {
        error_log("Error in cancel booking: " . $e->getMessage());
        $_SESSION['message'] = "Error: " . $e->getMessage();
            $_SESSION['message_type'] = 'danger';
    }

    header("Location: ../../index.php?page=patient/my_appointments");
    exit();
}
?> 