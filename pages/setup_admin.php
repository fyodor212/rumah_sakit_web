<?php
require 'config/database.php';

try {
    // 1. Cek apakah tabel users ada
    $tableExists = $db->query("SHOW TABLES LIKE 'users'")->rowCount() > 0;
    if (!$tableExists) {
        echo "Tabel users tidak ditemukan!<br>";
        exit;
    }

    // 2. Hapus admin lama jika ada
    $db->exec("DELETE FROM users WHERE username = 'admin'");
    
    // 3. Buat admin baru
    $sql = "INSERT INTO users (username, password, email, role) VALUES (:username, :password, :email, :role)";
    $stmt = $db->prepare($sql);
    
    $data = [
        ':username' => 'admin',
        ':password' => password_hash('admin123', PASSWORD_DEFAULT),
        ':email' => 'admin@rssehat.com',
        ':role' => 'admin'
    ];
    
    if ($stmt->execute($data)) {
        echo "<h3>Admin berhasil dibuat!</h3>";
        echo "Username: admin<br>";
        echo "Password: admin123<br>";
        
        // 4. Verifikasi data admin
        $admin = $db->query("SELECT * FROM users WHERE username = 'admin'")->fetch();
        if ($admin) {
            echo "<br>Verifikasi Data Admin:<br>";
            echo "ID: " . $admin['id'] . "<br>";
            echo "Role: " . $admin['role'] . "<br>";
            echo "<br><a href='index.php?page=login'>Login Sekarang</a>";
        }
    } else {
        echo "Gagal membuat admin!";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
} 