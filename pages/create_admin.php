<?php
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    try {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $query = "INSERT INTO users (username, email, password, role, created_at) 
                 VALUES (?, ?, ?, 'admin', NOW())";
        $stmt = $db->prepare($query);
        $stmt->execute([$username, $email, $hashed_password]);
        
        echo "Admin berhasil dibuat!";
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?> 