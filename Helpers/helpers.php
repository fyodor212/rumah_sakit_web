<?php
// Include functions.php yang berisi semua fungsi utama
require_once __DIR__ . '/../../config/functions.php';

// Tambahkan fungsi yang belum ada di functions.php
if (!function_exists('formatDateIndo')) {
    function formatDateIndo($date) {
        if (empty($date)) return '-';
        
        $bulan = array (
            1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        );
        
        $tanggal = new DateTime($date);
        $tgl = $tanggal->format('j');
        $bln = $bulan[$tanggal->format('n')];
        $thn = $tanggal->format('Y');
        
        return "$tgl $bln $thn";
    }
}

// Fungsi-fungsi lain yang belum ada di functions.php bisa ditambahkan di sini dengan pengecekan function_exists 