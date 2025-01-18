<?php
require_once '../config/database.php';
require_once '../config/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = $_POST['booking_id'];
    $rating = $_POST['rating'];
    $komentar = $_POST['komentar'];
    
    try {
        $query = "INSERT INTO review (booking_id, rating, komentar) VALUES (?, ?, ?)";
        $stmt = $db->prepare($query);
        $stmt->execute([$booking_id, $rating, $komentar]);
        
        header('Location: ../index.php?page=patient/reviews&status=success');
    } catch (PDOException $e) {
        header('Location: ../index.php?page=patient/reviews&status=error');
    }
} 