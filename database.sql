-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Waktu pembuatan: 18 Jan 2025 pada 09.27
-- Versi server: 10.4.28-MariaDB
-- Versi PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `rumah_sakit`
--
CREATE DATABASE IF NOT EXISTS `rumah_sakit` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `rumah_sakit`;

-- --------------------------------------------------------

--
-- Struktur dari tabel `booking`
--

CREATE TABLE `booking` (
  `id` int(11) NOT NULL,
  `pasien_id` int(11) DEFAULT NULL,
  `dokter_id` int(11) DEFAULT NULL,
  `layanan_id` int(11) DEFAULT NULL,
  `keluhan` text DEFAULT NULL,
  `tanggal` date NOT NULL,
  `jam` time NOT NULL,
  `status` enum('pending','confirmed','completed','cancelled','rejected') DEFAULT 'pending',
  `no_antrian` varchar(10) DEFAULT NULL,
  `total_biaya` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `booking`
--

INSERT INTO `booking` (`id`, `pasien_id`, `dokter_id`, `layanan_id`, `keluhan`, `tanggal`, `jam`, `status`, `no_antrian`, `total_biaya`, `created_at`, `updated_at`) VALUES
(1, NULL, 1, 1, NULL, '2024-01-20', '09:00:00', 'confirmed', 'A001', 50000.00, '2025-01-04 18:47:15', '2025-01-05 12:01:13'),
(2, 2, 2, 2, NULL, '2024-01-20', '14:00:00', 'confirmed', 'A002', 150000.00, '2025-01-04 18:47:15', '2025-01-05 12:20:21'),
(3, NULL, 3, 3, NULL, '2024-01-21', '10:00:00', 'confirmed', 'A003', 200000.00, '2025-01-04 18:47:15', '2025-01-05 12:01:13'),
(4, NULL, 4, 4, NULL, '2024-01-21', '19:00:00', 'confirmed', 'A004', 300000.00, '2025-01-04 18:47:15', '2025-01-05 12:01:13'),
(5, NULL, 5, 5, NULL, '2024-01-22', '15:00:00', 'confirmed', 'A005', 150000.00, '2025-01-04 18:47:15', '2025-01-07 18:55:05'),
(7, 7, 8, 2, NULL, '2025-01-08', '09:00:00', 'confirmed', NULL, 150000.00, '2025-01-04 21:16:43', '2025-01-09 16:20:45'),
(8, 7, 3, 7, NULL, '2025-01-16', '10:00:00', 'cancelled', NULL, 150000.00, '2025-01-04 21:17:02', '2025-01-14 14:46:02'),
(9, 7, 8, 8, NULL, '2025-01-09', '10:00:00', 'cancelled', NULL, 200000.00, '2025-01-04 21:19:59', '2025-01-09 16:20:45'),
(10, 7, 6, 2, NULL, '2025-01-06', '08:00:00', 'completed', NULL, 150000.00, '2025-01-05 10:08:59', '2025-01-09 16:20:45'),
(11, 7, 1, 3, NULL, '2025-01-08', '10:00:00', 'confirmed', NULL, 200000.00, '2025-01-05 11:21:06', '2025-01-09 16:20:45'),
(12, 7, 6, 8, NULL, '2025-01-07', '09:00:00', 'completed', NULL, 200000.00, '2025-01-05 11:21:31', '2025-01-09 16:20:45'),
(13, 7, 1, 8, NULL, '2025-01-15', '09:00:00', 'cancelled', NULL, 200000.00, '2025-01-05 11:24:24', '2025-01-09 16:20:45'),
(14, 7, 1, 8, NULL, '2025-01-15', '09:00:00', 'cancelled', NULL, 200000.00, '2025-01-05 11:24:29', '2025-01-09 16:20:45'),
(15, 7, 3, 8, '', '2025-01-06', '08:00:00', 'confirmed', NULL, 200000.00, '2025-01-05 11:25:56', '2025-01-09 16:20:45'),
(16, 7, 3, 2, 'fd', '2025-01-07', '09:00:00', 'completed', NULL, 150000.00, '2025-01-05 11:27:45', '2025-01-09 16:20:45'),
(17, 7, 1, 3, 'm', '2025-01-07', '09:00:00', 'completed', NULL, 200000.00, '2025-01-05 11:50:15', '2025-01-09 16:20:45'),
(23, 7, 6, 6, 'uhug', '2025-01-20', '13:30:00', 'cancelled', '1', 55000.00, '2025-01-17 20:31:55', '2025-01-17 20:35:57'),
(24, 7, 1, 1, 'sakitt', '2025-01-20', '13:30:00', 'cancelled', '1', 55000.00, '2025-01-17 20:36:23', '2025-01-17 20:39:07'),
(25, 7, 1, 1, 'sakitt', '2025-01-20', '13:30:00', 'cancelled', '2', 55000.00, '2025-01-17 20:36:27', '2025-01-17 20:39:10'),
(26, 7, 1, 1, 'sakitt', '2025-01-20', '13:30:00', 'cancelled', '3', 55000.00, '2025-01-17 20:39:03', '2025-01-17 20:39:12'),
(27, 7, 22, 6, 'dss', '2025-01-20', '13:30:00', 'cancelled', '1', 55000.00, '2025-01-18 07:29:59', '2025-01-18 07:30:05'),
(28, 7, 6, 1, 'ccsc', '2025-01-21', '16:00:00', 'pending', '1', 55000.00, '2025-01-18 07:54:29', '2025-01-18 07:54:29');

-- --------------------------------------------------------

--
-- Struktur dari tabel `dokter`
--

CREATE TABLE `dokter` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `nama` varchar(100) NOT NULL,
  `spesialisasi` varchar(100) NOT NULL,
  `hari` varchar(255) DEFAULT NULL,
  `jadwal` varchar(255) DEFAULT NULL,
  `status` enum('aktif','tidak aktif') DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `dokter`
--

INSERT INTO `dokter` (`id`, `user_id`, `nama`, `spesialisasi`, `hari`, `jadwal`, `status`, `created_at`) VALUES
(1, NULL, 'Dr. Ahmad Susanto', 'Dokter Umum', '', '12:45 - 14:15', 'aktif', '2025-01-04 18:47:15'),
(2, NULL, 'Dr. Siti Rahayu', 'Dokter Gigi', 'Senin,Rabu,Jumat', '13:00-17:00', 'aktif', '2025-01-04 18:47:15'),
(3, NULL, 'Dr. Budi Santoso', 'Dokter Anak', '', '08:00 - 12:00', 'aktif', '2025-01-04 18:47:15'),
(4, NULL, 'Dr. Maya Indah', 'Dokter Kandungan', 'Rabu,Jumat', '18:00-21:00', 'aktif', '2025-01-04 18:47:15'),
(5, NULL, 'Dr. Joko', 'Dokter THT', 'Senin,Kamis', '13:00-17:00', 'aktif', '2025-01-04 18:47:15'),
(6, NULL, 'Dr. Ahmad Susanto', 'Dokter Umum', 'Senin,Selasa,Rabu', '08:00-12:00', 'aktif', '2025-01-04 18:47:40'),
(8, NULL, 'Dr. Budi Santoso', 'Dokter Anak', 'Selasa,Kamis,Sabtu', '08:00-12:00', 'aktif', '2025-01-04 18:47:40'),
(22, NULL, 'Dr.Dwi Farhan', 'Jantung', '', '12:00 - 15:00', 'aktif', '2025-01-08 13:56:31');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kontak`
--

CREATE TABLE `kontak` (
  `id` int(11) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subjek` varchar(200) NOT NULL,
  `pesan` text NOT NULL,
  `status` enum('unread','read') DEFAULT 'unread',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `layanan`
--

CREATE TABLE `layanan` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `kategori` varchar(50) DEFAULT NULL,
  `harga` decimal(10,2) DEFAULT NULL,
  `status` enum('tersedia','tidak_tersedia') DEFAULT 'tersedia',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `layanan`
--

INSERT INTO `layanan` (`id`, `nama`, `deskripsi`, `kategori`, `harga`, `status`, `created_at`) VALUES
(1, 'Pemeriksaan Umum', 'Pemeriksaan kesehatan umum', 'Pemeriksaan Umum', 50000.00, 'tersedia', '2025-01-04 18:47:15'),
(2, 'Pemeriksaan Gigi', 'Pemeriksaan dan perawatan gigi', 'Spesialis', 150000.00, 'tersedia', '2025-01-04 18:47:15'),
(3, 'Konsultasi Anak', 'Konsultasi kesehatan anak', 'Spesialis', 200000.00, 'tersedia', '2025-01-04 18:47:15'),
(4, 'USG Kandungan', 'Pemeriksaan kandungan dengan USG', 'Spesialis', 300000.00, 'tersedia', '2025-01-04 18:47:15'),
(5, 'Pemeriksaan THT', 'Pemeriksaan telinga, hidung, tenggorokan', 'Spesialis', 150000.00, 'tersedia', '2025-01-04 18:47:15'),
(6, 'Pemeriksaan Umum', 'Pemeriksaan kesehatan umum', 'Pemeriksaan Umum', 50000.00, 'tersedia', '2025-01-04 18:47:40'),
(7, 'Pemeriksaan Gigi', 'Pemeriksaan dan perawatan gigi', 'Spesialis', 150000.00, 'tersedia', '2025-01-04 18:47:40'),
(8, 'Konsultasi Anak', 'Konsultasi kesehatan anak', 'Spesialis', 200000.00, 'tersedia', '2025-01-04 18:47:40'),
(9, 'USG Kandungan', 'Pemeriksaan kandungan dengan USG', 'Spesialis', 300000.00, 'tersedia', '2025-01-04 18:47:40'),
(10, 'Pemeriksaan THT', 'Pemeriksaan telinga, hidung, tenggorokan', 'Spesialis', 150000.00, 'tersedia', '2025-01-04 18:47:40');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pasien`
--

CREATE TABLE `pasien` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `no_rm` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `jenis_kelamin` enum('L','P') DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `no_hp` varchar(15) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `no_rekam_medis` varchar(20) DEFAULT NULL,
  `nik` varchar(16) DEFAULT NULL,
  `tempat_lahir` varchar(100) DEFAULT NULL,
  `golongan_darah` varchar(3) DEFAULT NULL,
  `riwayat_alergi` text DEFAULT NULL,
  `riwayat_penyakit` text DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pasien`
--

INSERT INTO `pasien` (`id`, `user_id`, `no_rm`, `nama`, `tanggal_lahir`, `jenis_kelamin`, `alamat`, `no_hp`, `status`, `created_at`, `no_rekam_medis`, `nik`, `tempat_lahir`, `golongan_darah`, `riwayat_alergi`, `riwayat_penyakit`, `foto`) VALUES
(2, 2, 'RM202401002', 'Dewi Lestari', '1988-08-20', 'P', 'Jl. Sudirman No. 45, Jakarta', '082345678901', 'active', '2025-01-04 18:47:15', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(6, 13, 'RM202501/001', 'rizky', '2024-05-14', 'L', 'payakumbuh', '32323', 'active', '2025-01-04 19:31:19', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(7, 14, 'RM202501/002', 'rizkyhamzary', '2024-12-26', 'L', 'payakumbuh', '212243jhj', 'active', '2025-01-04 20:00:44', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(14, 20, 'RM202401003', 'rizky', NULL, NULL, 'payakuumbuh', '01021212121', 'active', '2025-01-18 07:02:10', NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `pembayaran`
--

CREATE TABLE `pembayaran` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `jumlah` decimal(10,2) NOT NULL,
  `metode_pembayaran` varchar(50) DEFAULT NULL,
  `status` enum('pending','success','failed') DEFAULT 'pending',
  `tanggal_pembayaran` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pembayaran`
--

INSERT INTO `pembayaran` (`id`, `booking_id`, `jumlah`, `metode_pembayaran`, `status`, `tanggal_pembayaran`, `created_at`) VALUES
(1, 1, 50000.00, 'cash', 'success', '2024-01-20 02:30:00', '2025-01-04 18:47:15'),
(2, 2, 150000.00, 'transfer', 'success', '2025-01-07 18:31:18', '2025-01-04 18:47:15'),
(3, 3, 200000.00, 'cash', 'success', '2025-01-04 19:01:47', '2025-01-04 18:47:15'),
(4, 4, 300000.00, 'transfer', 'success', '2024-01-21 12:30:00', '2025-01-04 18:47:15'),
(5, 5, 150000.00, 'transfer', 'success', '2025-01-07 18:55:05', '2025-01-04 18:47:15');

-- --------------------------------------------------------

--
-- Struktur dari tabel `rekam_medis`
--

CREATE TABLE `rekam_medis` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `diagnosa` text NOT NULL,
  `tindakan` text NOT NULL,
  `resep` text DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `review`
--

CREATE TABLE `review` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `rating` int(11) DEFAULT NULL,
  `komentar` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `review`
--

INSERT INTO `review` (`id`, `booking_id`, `rating`, `komentar`, `created_at`) VALUES
(1, 1, 5, 'Pelayanan sangat baik dan dokter ramah', '2025-01-04 18:47:57'),
(2, 2, 4, 'Cukup puas dengan pelayanannya', '2025-01-04 18:47:57'),
(3, 3, 5, 'Dokter sangat ahli dan ramah terhadap anak-anak', '2025-01-04 18:47:57'),
(4, 4, 4, 'Fasilitas lengkap dan bersih', '2025-01-04 18:47:57'),
(5, 5, 3, 'Pelayanan baik tapi antrian cukup lama', '2025-01-04 18:47:57'),
(6, 10, 5, 'mantapp', '2025-01-05 10:12:23');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tagihan`
--

CREATE TABLE `tagihan` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `nominal` decimal(10,2) NOT NULL,
  `tanggal` date NOT NULL,
  `status` enum('Belum Lunas','Lunas') DEFAULT 'Belum Lunas',
  `metode_pembayaran` varchar(50) DEFAULT NULL,
  `bukti_pembayaran` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('admin','dokter','pasien') NOT NULL DEFAULT 'pasien',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `role`, `status`, `created_at`) VALUES
(2, 'dewi456', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'dewi@email.com', 'pasien', 'active', '2025-01-04 18:47:15'),
(3, 'rudi789', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'rudi@email.com', 'pasien', 'active', '2025-01-04 18:47:15'),
(5, 'doni654', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'doni@email.com', 'admin', 'active', '2025-01-04 18:47:15'),
(13, 'user', '$2y$10$dfwK0t8pSDOCNW/IAkAELe1KVVhgfboZ96gucy1OFQyUsU9Jo350a', 'r@gmail.com', 'admin', 'active', '2025-01-04 19:31:19'),
(14, 'rizkyhamzary', '$2y$10$JZc/pOqwKVEITbsW5Xaa1eJxyupuY1nx8cGCHaaPVQ/B.ML/Q0fiy', 'rizky@gmail.com', 'pasien', 'active', '2025-01-04 20:00:44'),
(16, 'admin', 'admin123', 'admin@example.com', 'admin', 'active', '2025-01-07 16:13:37'),
(18, 'wew', '$2y$10$WWLbPRqa6UeEGd5/670AAuq8qV8pPgUMJaHFC5uTXdn3S0wnr/ybu', 'wewe@gmail.com', 'pasien', 'active', '2025-01-07 20:08:01'),
(20, 'rizky', '$2y$10$s0mNtw6ie8WLkw8eYXTj.ecinqmr9HydOY37iQXDXyWLEb9XhHSMe', 'hhh@gmail.com', 'pasien', 'active', '2025-01-18 07:02:10');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `booking`
--
ALTER TABLE `booking`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pasien_id` (`pasien_id`),
  ADD KEY `dokter_id` (`dokter_id`),
  ADD KEY `layanan_id` (`layanan_id`),
  ADD KEY `idx_tanggal` (`tanggal`),
  ADD KEY `idx_status` (`status`);

--
-- Indeks untuk tabel `dokter`
--
ALTER TABLE `dokter`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_spesialisasi` (`spesialisasi`);

--
-- Indeks untuk tabel `kontak`
--
ALTER TABLE `kontak`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `layanan`
--
ALTER TABLE `layanan`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `pasien`
--
ALTER TABLE `pasien`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `no_rm` (`no_rm`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_no_rm` (`no_rm`);

--
-- Indeks untuk tabel `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created` (`created_at`),
  ADD KEY `idx_booking_id` (`booking_id`);

--
-- Indeks untuk tabel `rekam_medis`
--
ALTER TABLE `rekam_medis`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indeks untuk tabel `review`
--
ALTER TABLE `review`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indeks untuk tabel `tagihan`
--
ALTER TABLE `tagihan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_role` (`role`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `booking`
--
ALTER TABLE `booking`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT untuk tabel `dokter`
--
ALTER TABLE `dokter`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT untuk tabel `kontak`
--
ALTER TABLE `kontak`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT untuk tabel `layanan`
--
ALTER TABLE `layanan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT untuk tabel `pasien`
--
ALTER TABLE `pasien`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT untuk tabel `pembayaran`
--
ALTER TABLE `pembayaran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `rekam_medis`
--
ALTER TABLE `rekam_medis`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `review`
--
ALTER TABLE `review`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `tagihan`
--
ALTER TABLE `tagihan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `booking`
--
ALTER TABLE `booking`
  ADD CONSTRAINT `booking_ibfk_1` FOREIGN KEY (`pasien_id`) REFERENCES `pasien` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `booking_ibfk_2` FOREIGN KEY (`dokter_id`) REFERENCES `dokter` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `booking_ibfk_3` FOREIGN KEY (`layanan_id`) REFERENCES `layanan` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `dokter`
--
ALTER TABLE `dokter`
  ADD CONSTRAINT `dokter_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `pasien`
--
ALTER TABLE `pasien`
  ADD CONSTRAINT `pasien_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD CONSTRAINT `fk_booking_payment` FOREIGN KEY (`booking_id`) REFERENCES `booking` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `pembayaran_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `booking` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `rekam_medis`
--
ALTER TABLE `rekam_medis`
  ADD CONSTRAINT `rekam_medis_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `booking` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `review`
--
ALTER TABLE `review`
  ADD CONSTRAINT `review_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `booking` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `tagihan`
--
ALTER TABLE `tagihan`
  ADD CONSTRAINT `tagihan_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `booking` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
