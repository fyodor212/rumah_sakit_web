<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

if (!isset($_POST['action'])) {
    $_SESSION['message'] = "Invalid request";
    $_SESSION['message_type'] = 'danger';
    header("Location: ../../index.php");
    exit();
}

$action = $_POST['action'];

if ($action === 'create') {
    try {
        // Validasi input
        if (empty($_POST['layanan_id']) || empty($_POST['dokter_id']) || 
            empty($_POST['tanggal']) || empty($_POST['jam']) || empty($_POST['keluhan'])) {
            throw new Exception("Semua field harus diisi");
        }

        // Ambil data dari form
        $layanan_id = $_POST['layanan_id'];
        $dokter_id = $_POST['dokter_id'];
        $tanggal = $_POST['tanggal'];
        $jam = $_POST['jam'];
        $keluhan = $_POST['keluhan'];
        $pasien_id = $_SESSION['user_id'];

        // Generate nomor antrian
        $query = "SELECT COUNT(*) as total FROM booking WHERE dokter_id = ? AND tanggal = ? AND jam = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iss", $dokter_id, $tanggal, $jam);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $no_antrian = $row['total'] + 1;

        // Ambil harga layanan
        $query = "SELECT harga FROM layanan WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $layanan_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $layanan = $result->fetch_assoc();
        $total_biaya = $layanan['harga'] + 10000; // Biaya layanan + admin

        // Insert booking
        $query = "INSERT INTO booking (pasien_id, dokter_id, layanan_id, keluhan, tanggal, jam, status, no_antrian, total_biaya) 
                 VALUES (?, ?, ?, ?, ?, ?, 'pending', ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iiisssis", $pasien_id, $dokter_id, $layanan_id, $keluhan, $tanggal, $jam, $no_antrian, $total_biaya);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Janji temu berhasil dibuat! Nomor antrian Anda: " . $no_antrian;
            $_SESSION['message_type'] = 'success';
            header("Location: ../../index.php?page=patient/my_appointments");
            exit();
        } else {
            throw new Exception("Gagal membuat janji temu");
        }

    } catch (Exception $e) {
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = 'danger';
        header("Location: ../../index.php?page=patient/book_appointment");
        exit();
    }
} 