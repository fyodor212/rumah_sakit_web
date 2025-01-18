<?php
require_once 'config/database.php';

try {
    // Cek apakah admin sudah ada
    $query = "SELECT COUNT(*) FROM users WHERE username = 'admin'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $count = $stmt->fetchColumn();

    if ($count == 0) {
        // Buat password yang dienkripsi
        $password = 'admin123'; // Password default: admin123
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert admin
        $query = "INSERT INTO users (username, email, password, role) 
                  VALUES ('admin', 'admin@admin.com', ?, 'admin')";
        $stmt = $db->prepare($query);
        $stmt->execute([$hashed_password]);

        echo "Admin berhasil dibuat!<br>";
        echo "Username: admin<br>";
        echo "Password: admin123";
    } else {
        echo "Admin sudah ada!";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 