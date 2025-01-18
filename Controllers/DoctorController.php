<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';

if (!isset($_GET['action'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Action tidak ditemukan']);
    exit;
}

$action = $_GET['action'];

if ($action === 'get_schedule') {
    try {
        if (!isset($_GET['dokter_id']) || !isset($_GET['tanggal'])) {
            throw new Exception('Parameter tidak lengkap');
        }

        $dokter_id = $_GET['dokter_id'];
        $tanggal = $_GET['tanggal'];

            // Debug
        error_log("Request jadwal untuk dokter ID: " . $dokter_id . " tanggal: " . $tanggal);

        // Ambil jadwal dokter dengan semua kolom untuk debugging
        $query = "SELECT * FROM dokter WHERE id = ? AND status = 'aktif'";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $dokter_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $dokter = $result->fetch_assoc();
        $stmt->close();

        if (!$dokter) {
            throw new Exception('Dokter tidak ditemukan atau tidak aktif');
        }
        
        error_log("Data dokter lengkap: " . print_r($dokter, true));

        // Validasi format hari
        if (empty($dokter['hari'])) {
            throw new Exception('Dokter belum memiliki jadwal hari praktik');
        }

        // Parse hari praktik
        $hari_praktik = array_map('trim', explode(',', $dokter['hari']));
        error_log("Hari praktik: " . print_r($hari_praktik, true));
        
        // Konversi tanggal ke hari (1-7)
        $timestamp = strtotime($tanggal);
        if ($timestamp === false) {
            throw new Exception('Format tanggal tidak valid');
        }
        $hari = date('N', $timestamp); // 1 (Senin) sampai 7 (Minggu)
        error_log("Hari yang dipilih: " . $hari);
        
        // Cek apakah dokter praktik di hari yang dipilih
        if (!in_array((string)$hari, $hari_praktik)) {
            error_log("Dokter tidak praktik di hari ini");
            echo json_encode([
                'success' => true,
                'data' => [],
                'message' => 'Dokter tidak praktik di hari ini'
            ]);
            exit;
        }

        // Validasi format jadwal
        if (empty($dokter['jadwal'])) {
            throw new Exception('Dokter belum memiliki jadwal jam praktik');
        }

        // Parse jam praktik
        $jadwal_parts = explode('-', $dokter['jadwal']);
        if (count($jadwal_parts) !== 2) {
            throw new Exception('Format jadwal dokter tidak valid');
        }
        
        $jam_mulai = trim($jadwal_parts[0]);
        $jam_selesai = trim($jadwal_parts[1]);
        
        error_log("Jam praktik: " . $jam_mulai . " - " . $jam_selesai);
        
        // Validasi format jam
        if (!preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $jam_mulai) || 
            !preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $jam_selesai)) {
            throw new Exception('Format jam praktik tidak valid');
        }
        
        // Generate slot waktu per 30 menit
        $slots = [];
        $current = strtotime($tanggal . ' ' . $jam_mulai);
        $end = strtotime($tanggal . ' ' . $jam_selesai);
        
        if ($current === false || $end === false) {
            throw new Exception('Gagal mengkonversi jam praktik');
        }
        
        error_log("Generating slots dari " . date('Y-m-d H:i', $current) . " sampai " . date('Y-m-d H:i', $end));
        
        while ($current < $end) {
            $jam = date('H:i', $current);
            error_log("Checking slot: " . $jam);

            // Jika hari ini, hanya tampilkan slot yang belum lewat
            if ($tanggal === date('Y-m-d') && $jam <= date('H:i')) {
                error_log("Skip slot " . $jam . " (sudah lewat)");
                $current = strtotime('+30 minutes', $current);
                continue;
            }

            // Cek apakah slot sudah dibooking
            $query = "SELECT COUNT(*) as total FROM booking 
                     WHERE dokter_id = ? AND tanggal = ? AND jam = ? 
                     AND status IN ('pending', 'confirmed')";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("iss", $dokter_id, $tanggal, $jam);
            $stmt->execute();
            $result = $stmt->get_result();
            $booked = $result->fetch_assoc()['total'];
            $stmt->close();

            error_log("Slot " . $jam . " - Booked: " . $booked);

            // Asumsikan maksimal 2 pasien per slot
            if ($booked < 2) {
                $slots[] = ['jam' => $jam];
                error_log("Added slot: " . $jam);
            }

            $current = strtotime('+30 minutes', $current);
        }
        
        error_log("Total slots yang tersedia: " . count($slots));

            echo json_encode([
            'success' => true,
            'data' => $slots,
            'debug' => [
                'dokter_id' => $dokter_id,
                'tanggal' => $tanggal,
                'hari' => $hari,
                'hari_praktik' => $hari_praktik,
                'jam_mulai' => $jam_mulai,
                'jam_selesai' => $jam_selesai,
                'total_slots' => count($slots)
            ]
            ]);

        } catch (Exception $e) {
        error_log("Error in get_schedule: " . $e->getMessage());
        http_response_code(500);
            echo json_encode([
            'success' => false,
                'message' => $e->getMessage()
            ]);
        }
} else {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Action tidak valid'
    ]);
} 