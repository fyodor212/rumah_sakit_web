<?php
// Pastikan session dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../Helpers/helpers.php';
require_once __DIR__ . '/../../../../config/database.php';

// Cek akses
if (!isAdmin()) {
    header('Location: index.php');
    exit;
}

// Ambil ID pembayaran
$payment_id = $_GET['id'] ?? null;

if (!$payment_id) {
    $_SESSION['error'] = 'ID Pembayaran tidak valid';
    header('Location: index.php?page=admin/manage_payments');
    exit;
}

// Query untuk mengambil detail pembayaran
$query = "SELECT p.*, 
          b.no_antrian, b.tanggal as tanggal_booking, b.jam, b.id as booking_id,
          ps.nama as nama_pasien, ps.no_rm, ps.no_hp, ps.alamat,
          d.nama as nama_dokter,
          l.nama as nama_layanan, l.harga as harga_layanan
          FROM pembayaran p
          JOIN booking b ON p.booking_id = b.id
          JOIN pasien ps ON b.pasien_id = ps.id
          JOIN dokter d ON b.dokter_id = d.id
          JOIN layanan l ON b.layanan_id = l.id
          WHERE p.id = ?";

try {
    $stmt = $db->prepare($query);
    $stmt->execute([$payment_id]);
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$payment) {
        $_SESSION['error'] = 'Data pembayaran tidak ditemukan';
        header('Location: index.php?page=admin/manage_payments');
        exit;
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
    header('Location: index.php?page=admin/manage_payments');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kuitansi Pembayaran - INV-<?= str_pad($payment['booking_id'], 4, '0', STR_PAD_LEFT) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            padding: 20px;
        }
        .kuitansi {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #000;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .info-label {
            font-weight: bold;
            min-width: 150px;
        }
        .amount {
            font-size: 1.2em;
            font-weight: bold;
            color: #0d6efd;
        }
        .footer {
            margin-top: 50px;
            text-align: right;
        }
        .status-badge {
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: 500;
            display: inline-block;
        }
        @media print {
            .no-print {
                display: none;
            }
            body {
                padding: 0;
            }
            .kuitansi {
                border: none;
            }
        }
    </style>
</head>
<body>
    <div class="kuitansi">
        <div class="header">
            <h2 class="mb-1">KLINIK GIGI ALKINDI</h2>
            <p class="mb-0">Jl. Raya Alkindi No. 123, Jakarta</p>
            <p class="mb-0">Telp: (021) 123456</p>
        </div>

        <h4 class="text-center mb-4">KUITANSI PEMBAYARAN</h4>

        <div class="info-row">
            <div>
                <div class="info-label">No. Kuitansi</div>
                <div>INV-<?= str_pad($payment['booking_id'], 4, '0', STR_PAD_LEFT) ?></div>
            </div>
            <div>
                <div class="info-label">Tanggal</div>
                <div><?= formatDateIndo($payment['created_at']) ?></div>
            </div>
        </div>

        <div class="info-row mt-4">
            <div>
                <div class="info-label">Telah Terima Dari</div>
                <div><?= htmlspecialchars($payment['nama_pasien']) ?></div>
                <div class="text-muted small">No. RM: <?= $payment['no_rm'] ?></div>
            </div>
            <div>
                <div class="info-label">Status</div>
                <div class="status-badge bg-<?= getPaymentStatusColor($payment['status']) ?> text-white">
                    <?= ucfirst($payment['status']) ?>
                </div>
            </div>
        </div>

        <div class="mt-4">
            <div class="info-label">Untuk Pembayaran</div>
            <div>Layanan <?= htmlspecialchars($payment['nama_layanan']) ?></div>
            <div class="text-muted small">
                Dokter: <?= htmlspecialchars($payment['nama_dokter']) ?><br>
                Tanggal Booking: <?= formatDateIndo($payment['tanggal_booking']) ?><br>
                Jam: <?= formatTime($payment['jam']) ?>
            </div>
        </div>

        <div class="mt-4">
            <div class="info-label">Jumlah Pembayaran</div>
            <div class="amount"><?= formatCurrency($payment['jumlah']) ?></div>
        </div>

        <div class="footer">
            <p>Jakarta, <?= formatDateIndo(date('Y-m-d')) ?></p>
            <br><br><br>
            <p>_____________________</p>
            <p>Petugas Administrasi</p>
        </div>
    </div>

    <div class="text-center mt-4 no-print">
        <button onclick="window.print()" class="btn btn-primary">
            <i class="fas fa-print me-2"></i>Cetak Kuitansi
        </button>
        <button onclick="window.close()" class="btn btn-outline-secondary ms-2">
            <i class="fas fa-times me-2"></i>Tutup
        </button>
    </div>

    <script src="https://kit.fontawesome.com/your-font-awesome-kit.js"></script>
    <script>
        // Auto print on page load
        window.onload = function() {
            if (!window.location.search.includes('noprint')) {
                window.print();
            }
        }
    </script>
</body>
</html> 