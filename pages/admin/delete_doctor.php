<?php
require_once 'config/database.php';
require_once 'config/functions.php';

// Cek apakah user adalah admin
if (!isAdmin()) {
    header('Location: index.php?page=auth/login');
    exit;
}

// Hapus dokter berdasarkan ID
if (isset($_GET['id'])) {
    $doctorId = $_GET['id'];
    try {
        $query = "DELETE FROM dokter WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$doctorId]);

        $_SESSION['success'] = "Dokter berhasil dihapus!";
        header('Location: index.php?page=admin/manage_doctor');
        exit;
    } catch (PDOException $e) {
        $error = "Error deleting doctor: " . $e->getMessage();
    }
}
?>

<main>
    <section class="page-header bg-light">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center py-5">
                    <h1>Hapus Dokter</h1>
                    <p class="lead">Dokter berhasil dihapus!</p>
                </div>
            </div>
        </div>
    </section>
</main>

<style>
.page-header {
    background: linear-gradient(rgba(255,255,255,.9), rgba(255,255,255,.9)), 
                url('public/images/hospital-bg.jpg') center/cover;
}
</style> 