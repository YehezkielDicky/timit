-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 01 Nov 2025 pada 07.15
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `timit`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `barang`
--

CREATE TABLE `barang` (
  `id_barang` int(11) NOT NULL,
  `nama_barang` varchar(30) NOT NULL,
  `qty` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `barang`
--

INSERT INTO `barang` (`id_barang`, `nama_barang`, `qty`) VALUES
(1, 'SSD_ADATA 256GB', 22),
(2, 'SSD Falcon 256GB', 30),
(3, 'SSD Falcon 512GB', 15),
(4, 'PSU enlight 300w', 12);

-- --------------------------------------------------------

--
-- Struktur dari tabel `d_trans`
--

CREATE TABLE `d_trans` (
  `id_dtrans` int(11) NOT NULL,
  `id_trans` int(11) NOT NULL,
  `id_barang` int(11) NOT NULL,
  `qty` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `d_trans`
--

INSERT INTO `d_trans` (`id_dtrans`, `id_trans`, `id_barang`, `qty`) VALUES
(2, 1, 1, 1),
(3, 2, 1, 2),
(4, 3, 1, 3),
(5, 4, 1, 1),
(6, 5, 1, 12),
(8, 6, 4, 12);

-- --------------------------------------------------------

--
-- Struktur dari tabel `h_trans`
--

CREATE TABLE `h_trans` (
  `id_trans` int(11) NOT NULL,
  `no_surat` varchar(50) NOT NULL,
  `jenis` enum('masuk','keluar') NOT NULL,
  `tanggal` date NOT NULL,
  `id_unit` int(11) DEFAULT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `tanda_terima` varchar(255) DEFAULT NULL,
  `berita_acara` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `h_trans`
--

INSERT INTO `h_trans` (`id_trans`, `no_surat`, `jenis`, `tanggal`, `id_unit`, `keterangan`, `tanda_terima`, `berita_acara`) VALUES
(1, '01/WM_IT/SK/VIII/2025', 'keluar', '2025-10-31', 1, 'Untuk admisi', NULL, NULL),
(2, '02/WM_IT/SK/VIII/2025', 'keluar', '2025-10-31', 1, 'keluar di admii', NULL, NULL),
(3, '03/WM_IT/SK/VIII/2025', 'keluar', '2025-10-31', 1, 'smeed', NULL, NULL),
(4, '04/WM_IT/SK/VIII/2025', 'keluar', '2025-10-31', 1, 'adadasda', NULL, NULL),
(5, '01/WM_IT/SM/VIII/2025', 'masuk', '2025-10-31', NULL, 'MASUK CUK', NULL, NULL),
(6, '02/WM_IT/SM/VIII/2025', 'masuk', '2025-11-01', NULL, 'adadasd', 'transaksi/tanda_terima/yYaLPXxIH5AXMHKa9P1ksnxqHpHSoGO92jeP3IaR.pdf', 'transaksi/berita_acara/HSeh0K3FnQqZmkXi1XvVt2hIfUCo7c46RKt0HSJ8.pdf');

-- --------------------------------------------------------

--
-- Struktur dari tabel `peminjaman_barang`
--

CREATE TABLE `peminjaman_barang` (
  `id_peminjaman` int(11) NOT NULL,
  `id_barang` int(11) NOT NULL,
  `id_users` int(11) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `tanggal` datetime NOT NULL,
  `tanggal_kembali` datetime DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `keterangan` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `peminjaman_barang`
--

INSERT INTO `peminjaman_barang` (`id_peminjaman`, `id_barang`, `id_users`, `jumlah`, `tanggal`, `tanggal_kembali`, `status`, `keterangan`) VALUES
(1, 1, 1, 1, '2025-11-01 00:00:00', '2025-11-01 00:00:00', 'dikembalikan', 'Untuk admisi'),
(2, 1, 1, 1, '2025-11-01 00:00:00', '2025-11-01 00:00:00', 'dikembalikan', 'Untuk admisi');

-- --------------------------------------------------------

--
-- Struktur dari tabel `unit_kerja`
--

CREATE TABLE `unit_kerja` (
  `id_unit` int(11) NOT NULL,
  `unit_kerja` varchar(20) NOT NULL,
  `lokasi` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `unit_kerja`
--

INSERT INTO `unit_kerja` (`id_unit`, `unit_kerja`, `lokasi`) VALUES
(1, 'LPKS', 'Dinoyo'),
(2, 'Perpustakaan', 'Dinoyo'),
(3, 'Perpustakaan', 'Kalijudan'),
(4, 'Perpustakaan', 'Pakuwon'),
(5, 'Farmasi', 'Pakuwon'),
(6, 'Kedokteran', 'Pakuwon'),
(7, 'Psikologi', 'Pakuwon'),
(8, 'Keperawatan', 'Pakuwon'),
(9, 'Filsafat', 'Pakuwon'),
(10, 'BAAK', 'Pakuwon'),
(11, 'BAAK', 'Dinoyo'),
(12, 'BAU', 'Dinoyo'),
(13, 'FIKOM', 'Dinoyo'),
(14, 'Rumah Tangga', 'Kalijudan');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `nama_user` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `username` varchar(10) NOT NULL,
  `role` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `photo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id_user`, `nama_user`, `password`, `username`, `role`, `status`, `photo`) VALUES
(1, 'Yehezkiel Dicky', '$2y$12$CWXA8KDMnlzLFcocYS/Pr.hOyOs0y5I8HoDJDxehX3Z5jEOxHt2zK', '003251384', 'admin', 'active', 'avatars/Hy9dJeXuNCwWAH7crqeWOX1UtIkx1VllDIJOA6aI.jpg'),
(2, 'vicky', '$2y$12$bVcOwJtyuyEwMGisv7Q3WefZvgSdDOuULKsqzpWeBBnKa60AIQQMa', 'vicky', 'staff', 'active', NULL),
(3, 'Wahyu Setiawan', '$2y$12$1zgZw1nj6GtefS0gLu8N6exSXu6FDB659rer8DJHyy//ydYnFBPcC', '003221300', 'staff', 'active', NULL),
(4, 'Yoga Pratama', '$2y$12$WPbj1YjpYg2cZtyarVrCNufeJ9vlA3elDM3DwY3vz7HF.2qAs358u', '003251385', 'staff', 'active', NULL),
(5, 'Jovan', '$2y$12$i1Tqd2UHCAQCfchyZ80a0.7UNnwwIf3TsQRoi1B7W9LYAVvUM/tyy', 'jovan', 'staff', 'active', NULL),
(6, 'Vincent', '$2y$12$/YngtaTJCi2zF9y1L.BjCe5cBRMTk5CaLubf9K21l4/fF/ySqk9Da', 'vincent', 'staff', 'active', NULL),
(7, 'Sumarno', '$2y$12$G7pqIpj79IPFPfZjPM1PcuFwGyMnqTdn9nvz3NGM9c4sONkokTKB.', '003000478', 'koordinator', 'active', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `user_logs`
--

CREATE TABLE `user_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `activity` varchar(255) NOT NULL,
  `module` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `user_logs`
--

INSERT INTO `user_logs` (`id`, `user_id`, `activity`, `module`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 'View', 'Barang', 'Lihat daftar barang', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-31 21:58:41'),
(2, 1, 'View', 'Barang', 'Buka form create', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-31 21:58:58'),
(3, 1, 'View', 'Barang', 'Lihat daftar barang', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-31 21:59:28'),
(4, 1, 'View', 'Barang', 'Lihat daftar barang', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-31 22:04:51'),
(5, 1, 'View', 'Barang', 'Buka form create', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-31 22:04:53'),
(6, 1, 'Create', 'Barang', 'Tambah: SSD Falcon 256GB (id: 2)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-31 22:06:09'),
(7, 1, 'View', 'Barang', 'Lihat daftar barang', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-31 22:06:09'),
(8, 1, 'View', 'Barang', 'Lihat daftar barang', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-31 22:32:38'),
(9, 1, 'View', 'Barang', 'Buka form create', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-31 22:32:42'),
(10, 1, 'Create', 'Barang', 'Tambah: SSD Falcon 512GB (id: 3)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-31 22:32:56'),
(11, 1, 'View', 'Barang', 'Lihat daftar barang', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-31 22:32:57'),
(12, 1, 'View', 'Barang', 'Lihat daftar barang', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-31 22:38:56'),
(13, 1, 'View', 'Barang', 'Lihat daftar barang', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-31 22:43:51'),
(14, 1, 'View', 'Barang', 'Lihat daftar barang', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-31 22:45:11'),
(15, 1, 'View', 'Barang', 'Buka form create', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-31 22:45:12'),
(16, 1, 'Create', 'Barang', 'Tambah: PSU enlight 300w (id: 4)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-31 22:45:30'),
(17, 1, 'View', 'Barang', 'Lihat daftar barang', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-31 22:45:31'),
(18, 1, 'Create', 'Transaksi', 'NoSurat: 02/WM_IT/SM/VIII/2025, Jenis: masuk, Items: 4', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-31 22:46:00'),
(19, 1, 'View', 'Barang', 'Lihat daftar barang', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-31 22:46:02'),
(20, 1, 'Update', 'Transaksi', 'Update NoSurat: 02/WM_IT/SM/VIII/2025, Files: TT=transaksi/tanda_terima/yYaLPXxIH5AXMHKa9P1ksnxqHpHSoGO92jeP3IaR.pdf, BA=transaksi/berita_acara/HSeh0K3FnQqZmkXi1XvVt2hIfUCo7c46RKt0HSJ8.pdf', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-31 23:08:34'),
(21, 1, 'View', 'Barang', 'Lihat daftar barang', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-31 23:14:25');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `barang`
--
ALTER TABLE `barang`
  ADD PRIMARY KEY (`id_barang`);

--
-- Indeks untuk tabel `d_trans`
--
ALTER TABLE `d_trans`
  ADD PRIMARY KEY (`id_dtrans`),
  ADD KEY `idx_trans` (`id_trans`),
  ADD KEY `idx_barang` (`id_barang`);

--
-- Indeks untuk tabel `h_trans`
--
ALTER TABLE `h_trans`
  ADD PRIMARY KEY (`id_trans`),
  ADD UNIQUE KEY `uq_surat_jenis` (`no_surat`,`jenis`),
  ADD KEY `idx_tgl` (`tanggal`),
  ADD KEY `fk_hunit` (`id_unit`);

--
-- Indeks untuk tabel `peminjaman_barang`
--
ALTER TABLE `peminjaman_barang`
  ADD PRIMARY KEY (`id_peminjaman`),
  ADD KEY `fk_peminjaman_barang` (`id_barang`),
  ADD KEY `fk_peminjaman_user` (`id_users`);

--
-- Indeks untuk tabel `unit_kerja`
--
ALTER TABLE `unit_kerja`
  ADD PRIMARY KEY (`id_unit`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`);

--
-- Indeks untuk tabel `user_logs`
--
ALTER TABLE `user_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `barang`
--
ALTER TABLE `barang`
  MODIFY `id_barang` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `d_trans`
--
ALTER TABLE `d_trans`
  MODIFY `id_dtrans` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `h_trans`
--
ALTER TABLE `h_trans`
  MODIFY `id_trans` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `peminjaman_barang`
--
ALTER TABLE `peminjaman_barang`
  MODIFY `id_peminjaman` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `unit_kerja`
--
ALTER TABLE `unit_kerja`
  MODIFY `id_unit` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `user_logs`
--
ALTER TABLE `user_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `d_trans`
--
ALTER TABLE `d_trans`
  ADD CONSTRAINT `fk_db` FOREIGN KEY (`id_barang`) REFERENCES `barang` (`id_barang`),
  ADD CONSTRAINT `fk_dh` FOREIGN KEY (`id_trans`) REFERENCES `h_trans` (`id_trans`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `h_trans`
--
ALTER TABLE `h_trans`
  ADD CONSTRAINT `fk_hunit` FOREIGN KEY (`id_unit`) REFERENCES `unit_kerja` (`id_unit`);

--
-- Ketidakleluasaan untuk tabel `peminjaman_barang`
--
ALTER TABLE `peminjaman_barang`
  ADD CONSTRAINT `fk_peminjaman_barang` FOREIGN KEY (`id_barang`) REFERENCES `barang` (`id_barang`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_peminjaman_user` FOREIGN KEY (`id_users`) REFERENCES `users` (`id_user`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `user_logs`
--
ALTER TABLE `user_logs`
  ADD CONSTRAINT `user_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id_user`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
