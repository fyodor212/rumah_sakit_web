<?php
require_once '../config/database.php';
require_once '../config/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = $_POST['booking_id'];
    $rating = $_POST['rating'];
    $komentar = $_POST['komentar'];
    
    try {
        $query = "UPDATE review SET rating = ?, komentar = ? WHERE booking_id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$rating, $komentar, $booking_id]);
        
        header('Location: ../index.php?page=patient/reviews&status=updated');
    } catch (PDOException $e) {
        header('Location: ../index.php?page=patient/reviews&status=error');
    }
} 