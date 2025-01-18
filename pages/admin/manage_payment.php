<?php
require 'config/database.php';

if (!isAdmin()) {
    header('Location: index.php?page=login');
    exit;
}

// Handle payment status updates
if (isset($_POST['update_status'])) {
    try {
        $payment_id = $_POST['payment_id'];
        $new_status = $_POST['status'];
        
        $db->beginTransaction();
        
        // Update payment status
        $query = "UPDATE pembayaran SET status = ?, tanggal_pembayaran = NOW() WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$new_status, $payment_id]);
        
        // If payment successful, update booking status
        if ($new_status == 'success') {
            $query = "UPDATE booking b 
                     JOIN pembayaran p ON b.id = p.booking_id 
                     SET b.status = 'confirmed' 
                     WHERE p.id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$payment_id]);
        }
        
        $db->commit();
        $_SESSION['success'] = "Status pembayaran berhasil diupdate!";
        
    } catch(PDOException $e) {
        $db->rollBack();
        error_log("Error updating payment: " . $e->getMessage());
        $_SESSION['error'] = "Gagal mengupdate status pembayaran";
    }
}

// Get payments with filter
$status_filter = $_GET['status'] ?? 'all';
$date_filter = $_GET['date'] ?? date('Y-m-d');

try {
    $where_clause = "";
    $params = [];
    
    if ($status_filter !== 'all') {
        $where_clause .= " AND p.status = ?";
        $params[] = $status_filter;
    }
    
    if ($date_filter) {
        $where_clause .= " AND DATE(p.created_at) = ?";
        $params[] = $date_filter;
    }

    $query = "SELECT p.*, b.tanggal as booking_date, b.jam,
              ps.nama as nama_pasien, ps.no_rm,
              d.nama as nama_dokter, l.nama as nama_layanan
              FROM pembayaran p
              JOIN booking b ON p.booking_id = b.id
              JOIN pasien ps ON b.pasien_id = ps.id
              JOIN dokter d ON b.dokter_id = d.id
              JOIN layanan l ON b.layanan_id = l.id
              WHERE 1=1" . $where_clause . "
              ORDER BY p.created_at DESC";
              
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $payments = $stmt->fetchAll();
    
} catch(PDOException $e) {
    error_log("Error fetching payments: " . $e->getMessage());
    $payments = [];
}

// Calculate total payments
$total_pending = 0;
$total_success = 0;
foreach($payments as $payment) {
    if ($payment['status'] == 'success') {
        $total_success += $payment['jumlah'];
    } elseif ($payment['status'] == 'pending') {
        $total_pending += $payment['jumlah'];
    }
}
?>

<div class="page-container admin-page">
    <div class="page-header">
        <h1>Kelola Pembayaran</h1>
        <p>Manajemen data pembayaran</p>
    </div>

    <div class="content-card">
        <!-- Filter Section -->
        <div class="filter-section mb-4">
            <form method="GET" class="row g-3">
                <input type="hidden" name="page" value="admin/manage_payment">
                
                <div class="col-md-4">
                    <label class="form-label">Filter Tanggal</label>
                    <input type="date" name="date" class="form-control" 
                           value="<?= htmlspecialchars($date_filter) ?>">
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control">
                        <option value="all" <?= $status_filter == 'all' ? 'selected' : '' ?>>Semua</option>
                        <option value="pending" <?= $status_filter == 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="success" <?= $status_filter == 'success' ? 'selected' : '' ?>>Success</option>
                        <option value="failed" <?= $status_filter == 'failed' ? 'selected' : '' ?>>Failed</option>
                    </select>
                </div>
                
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Filter</button>
                </div>
            </form>
        </div>

        <!-- Payment Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title">Total Pembayaran Sukses</h5>
                        <h3 class="card-text">Rp <?= number_format($total_success, 0, ',', '.') ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-warning">
                    <div class="card-body">
                        <h5 class="card-title">Total Pembayaran Pending</h5>
                        <h3 class="card-text">Rp <?= number_format($total_pending, 0, ',', '.') ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Table -->
        <div class="table-container">
            <table class="content-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Invoice</th>
                        <th>Pasien</th>
                        <th>Layanan</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($payments)): ?>
                        <tr>
                            <td colspan="8" class="text-center">Tidak ada data pembayaran</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($payments as $index => $payment): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= date('d/m/Y', strtotime($payment['created_at'])) ?></td>
                                <td><?= htmlspecialchars($payment['invoice_number']) ?></td>
                                <td><?= htmlspecialchars($payment['nama_pasien']) ?></td>
                                <td><?= htmlspecialchars($payment['nama_layanan']) ?></td>
                                <td>Rp <?= number_format($payment['jumlah'], 0, ',', '.') ?></td>
                                <td>
                                    <span class="badge bg-<?= getStatusColor($payment['status']) ?>">
                                        <?= ucfirst($payment['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if($payment['status'] == 'pending'): ?>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="payment_id" value="<?= $payment['id'] ?>">
                                            <select name="status" class="form-select form-select-sm" 
                                                    onchange="this.form.submit()" style="width: auto; display: inline-block;">
                                                <option value="pending" selected>Pending</option>
                                                <option value="success">Success</option>
                                                <option value="failed">Failed</option>
                                            </select>
                                            <input type="hidden" name="update_status" value="1">
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div> 