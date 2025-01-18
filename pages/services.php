<?php
require_once __DIR__ . '/../../../config/database.php';

// Ambil data kategori untuk filter
try {
    $query = "SELECT DISTINCT kategori FROM layanan WHERE status = 'tersedia' ORDER BY kategori ASC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $error = "Error: " . $e->getMessage();
}

// Filter berdasarkan kategori
$selectedCategory = $_GET['kategori'] ?? '';
$whereClause = $selectedCategory ? "AND kategori = ?" : "";

// Ambil data layanan
try {
    $query = "SELECT * FROM layanan WHERE status = 'tersedia' $whereClause ORDER BY nama ASC";
    $stmt = $db->prepare($query);
    if ($selectedCategory) {
        $stmt->execute([$selectedCategory]);
    } else {
        $stmt->execute();
    }
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error: " . $e->getMessage();
}
?>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2>Layanan Kami</h2>
            <p class="text-muted">Berbagai layanan kesehatan yang kami sediakan</p>
        </div>
        <div class="col-md-6">
            <form class="d-flex justify-content-end">
                <input type="hidden" name="page" value="services">
                <select class="form-select w-auto" name="kategori" onchange="this.form.submit()">
                    <option value="">Semua Kategori</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category ?>" <?= $selectedCategory == $category ? 'selected' : '' ?>>
                            <?= $category ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
    </div>

    <div class="row g-4">
        <?php foreach ($services as $service): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card service-card h-100">
                    <div class="card-body text-center p-4">
                        <div class="service-icon mb-3">
                            <i class="fas fa-stethoscope fa-3x text-primary"></i>
                        </div>
                        <h5 class="card-title"><?= htmlspecialchars($service['nama']) ?></h5>
                        <p class="text-primary mb-2"><?= htmlspecialchars($service['kategori']) ?></p>
                        
                        <?php if (!empty($service['deskripsi'])): ?>
                            <p class="mb-3"><?= htmlspecialchars($service['deskripsi']) ?></p>
                        <?php endif; ?>

                        <?php if (!empty($service['estimasi_waktu'])): ?>
                            <p class="mb-2">
                                <i class="fas fa-clock me-2"></i>
                                Estimasi: <?= $service['estimasi_waktu'] ?> menit
                            </p>
                        <?php endif; ?>

                        <div class="price mb-3">
                            <span class="h5 text-primary">
                                Rp <?= number_format($service['harga'], 0, ',', '.') ?>
                            </span>
                        </div>

                        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'pasien'): ?>
                            <a href="index.php?page=patient/book_appointment" class="btn btn-primary">
                                <i class="fas fa-calendar-plus"></i> Buat Janji
                            </a>
                        <?php elseif (!isset($_SESSION['user_id'])): ?>
                            <button class="btn btn-primary" onclick="confirmLogin()">
                                <i class="fas fa-calendar-plus"></i> Buat Janji
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (empty($services)): ?>
            <div class="col-12 text-center py-5">
                <i class="fas fa-stethoscope text-muted mb-3" style="font-size: 64px;"></i>
                <p class="lead mb-0">Tidak ada layanan yang ditemukan</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.service-card {
    transition: transform 0.3s ease;
    border: none;
    box-shadow: 0 0 15px rgba(0,0,0,.05);
    border-radius: 10px;
}

.service-card:hover {
    transform: translateY(-5px);
}

.service-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
}

.price {
    position: relative;
    display: inline-block;
}

.price::before {
    content: '';
    position: absolute;
    left: -10px;
    right: -10px;
    top: 50%;
    height: 8px;
    background: rgba(13, 110, 253, 0.1);
    z-index: 0;
    transform: translateY(-50%);
}

.price span {
    position: relative;
    z-index: 1;
    background: white;
    padding: 0 10px;
}
</style> 