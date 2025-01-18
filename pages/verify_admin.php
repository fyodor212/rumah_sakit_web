<?php
require 'config/database.php';

try {
    // Cek data admin yang ada
    $stmt = $db->prepare("SELECT * FROM users WHERE username = 'admin'");
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<h2>Admin Data Verification</h2>";
    
    if ($admin) {
        echo "<pre>";
        echo "ID: " . $admin['id'] . "\n";
        echo "Username: " . $admin['username'] . "\n";
        echo "Email: " . $admin['email'] . "\n";
        echo "Role: " . $admin['role'] . "\n";
        echo "</pre>";
        
        // Test password verification
        $testPassword = 'admin123';
        if (password_verify($testPassword, $admin['password'])) {
            echo "<p style='color: green'>Password verification successful!</p>";
        } else {
            echo "<p style='color: red'>Password verification failed!</p>";
            
            // Create new admin with correct password
            echo "<h3>Creating new admin account...</h3>";
            
            // Delete existing admin
            $db->exec("DELETE FROM users WHERE username = 'admin'");
            
            // Create new admin
            $password = password_hash($testPassword, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, ?)");
            $stmt->execute(['admin', $password, 'admin@rssehat.com', 'admin']);
            
            echo "<p style='color: green'>New admin account created!</p>";
            echo "<p>Please try logging in with:</p>";
            echo "<pre>";
            echo "Username: admin\n";
            echo "Password: admin123\n";
            echo "</pre>";
        }
    } else {
        echo "<p style='color: red'>No admin account found!</p>";
    }
    
} catch(PDOException $e) {
    echo "<p style='color: red'>Error: " . $e->getMessage() . "</p>";
}
?>

<p><a href="index.php?page=login">Go to Login</a></p> 