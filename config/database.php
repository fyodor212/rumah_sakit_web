<?php
// Konfigurasi Database
$host = 'localhost';
$dbname = 'rumah_sakit';
$username = 'root';
$password = '';

try {
    // Koneksi PDO
    $db = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    
    // Koneksi MySQLi
    $conn = new mysqli($host, $username, $password, $dbname);
    if ($conn->connect_error) {
        throw new Exception("Koneksi mysqli gagal: " . $conn->connect_error);
    }
    
    // Set charset
    $conn->set_charset("utf8mb4");
    
    // Debug koneksi
    error_log("Database connected successfully");
    
} catch (Exception $e) {
    error_log("Database connection error: " . $e->getMessage());
    die("Koneksi database gagal: " . $e->getMessage());
}

// Fungsi untuk mengecek tabel
function checkTableExists($tableName) {
    global $conn;
    $result = $conn->query("SHOW TABLES LIKE '$tableName'");
    return $result->num_rows > 0;
}

// Cek dan buat tabel users jika belum ada
if (!checkTableExists('users')) {
    $sql = "CREATE TABLE users (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        role ENUM('admin', 'dokter', 'pasien') NOT NULL DEFAULT 'pasien',
        status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_username (username),
        INDEX idx_email (email),
        INDEX idx_role (role)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    if ($conn->query($sql) === TRUE) {
        error_log("Tabel users berhasil dibuat");
        
        // Buat user admin default jika tabel baru dibuat
        $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
        $sql_admin = "INSERT INTO users (username, password, email, role, status) 
                     VALUES ('admin', '$admin_password', 'admin@klinik.com', 'admin', 'active')";
        if ($conn->query($sql_admin) === TRUE) {
            error_log("User admin default berhasil dibuat");
        } else {
            error_log("Error creating default admin: " . $conn->error);
        }
    } else {
        error_log("Error creating users table: " . $conn->error);
    }
}
?> 