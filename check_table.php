<?php
require_once 'config/database.php';

try {
    $query = "SHOW COLUMNS FROM dokter WHERE Field = 'status'";
    $stmt = $db->query($query);
    $column = $stmt->fetch(PDO::FETCH_ASSOC);
    var_dump($column);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 