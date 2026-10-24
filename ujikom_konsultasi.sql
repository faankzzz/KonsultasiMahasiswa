-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 07, 2026 at 06:04 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ujikom_konsultasi`
--

-- --------------------------------------------------------

--
-- Table structure for table `ketersediaan`
--

CREATE TABLE `ketersediaan` (
  `id` int(11) NOT NULL,
  `dosen_id` int(11) NOT NULL,
  `hari` varchar(10) NOT NULL,
  `waktu_mulai` time NOT NULL,
  `waktu_selesai` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ketersediaan`
--

INSERT INTO `ketersediaan` (`id`, `dosen_id`, `hari`, `waktu_mulai`, `waktu_selesai`) VALUES
(3, 2, 'Selasa', '09:00:00', '12:00:00'),
(4, 2, 'Kamis', '13:00:00', '15:00:00'),
(5, 3, 'Senin', '10:00:00', '12:00:00'),
(6, 3, 'Jumat', '08:00:00', '11:00:00'),
(7, 1, 'Selasa', '07:00:00', '12:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `konsultasi`
--

CREATE TABLE `konsultasi` (
  `id` int(11) NOT NULL,
  `mahasiswa_id` int(11) NOT NULL,
  `dosen_id` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `waktu_mulai` time NOT NULL,
  `waktu_selesai` time NOT NULL,
  `topik` varchar(255) NOT NULL,
  `status` enum('menunggu','disetujui','ditolak') DEFAULT 'menunggu',
  `hasil_konsultasi` text DEFAULT NULL,
  `dibuat_pada` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `konsultasi`
--

INSERT INTO `konsultasi` (`id`, `mahasiswa_id`, `dosen_id`, `tanggal`, `waktu_mulai`, `waktu_selesai`, `topik`, `status`, `hasil_konsultasi`, `dibuat_pada`) VALUES
(3, 4, 3, '2026-05-07', '13:00:00', '13:30:00', 'Review Progress Tugas Akhir', 'ditolak', NULL, '2026-05-02 02:00:00'),
(5, 4, 2, '2026-04-20', '09:00:00', '09:30:00', 'Konsultasi Nilai Ujian', 'disetujui', 'nilai sudah bagus dan tidak perlu diubah', '2026-04-15 01:00:00'),
(7, 5, 2, '2026-05-07', '13:00:00', '13:30:00', 'pengajuan revisi bab 5', 'disetujui', 'selesai, acc bab 5', '2026-05-06 15:20:31'),
(9, 6, 2, '2026-05-19', '09:00:00', '09:30:00', 'pengajuan judul laporan akhir', 'disetujui', NULL, '2026-05-07 08:57:36'),
(10, 4, 2, '2026-05-21', '13:30:00', '14:00:00', 'pengajuan bab 5 laporan akhir', 'menunggu', NULL, '2026-05-07 08:58:49');

-- --------------------------------------------------------

--
-- Table structure for table `pengguna`
--

CREATE TABLE `pengguna` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `kata_sandi` varchar(255) NOT NULL,
  `peran` enum('mahasiswa','dosen','admin') NOT NULL,
  `nim` varchar(20) DEFAULT NULL,
  `nip` varchar(30) DEFAULT NULL,
  `jurusan` varchar(100) DEFAULT NULL,
  `dibuat_pada` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pengguna`
--

INSERT INTO `pengguna` (`id`, `nama`, `email`, `kata_sandi`, `peran`, `nim`, `nip`, `jurusan`, `dibuat_pada`) VALUES
(1, 'Dr. Indri Ariyanti, S.E., M.Si', 'ibuindri@gmail.com', '$2y$10$Hvn1Zi874BTmsKRXdxsfxOik6KQjsFPEhQyqFq/mXrVepGszqxLg2', 'dosen', NULL, '197306032008012012', 'Manajemen Informatika', '2026-05-06 14:15:17'),
(2, 'Trizaurah Armiani, S.Kom., M.Sc', 'ibutrie@gmail.com', '$2y$10$DhkEN4a9YSrODiTlvVTGB.8it4aKeByVRjQXyCzAJ0fr.yXHxkMI6', 'dosen', NULL, '199401222020122023', 'Manajemen Informatika', '2026-05-06 14:15:17'),
(3, 'Yulia Hapsari, M.Kom.', 'ibuyulia@gmail.com', '$2y$10$NCx5js0AYdx.nGNKSd6ZY.JLcOfLXSnsKy53zpeZu8epvRdhew3Ha', 'dosen', NULL, '199207242022032002', 'Manajemen Informatika', '2026-05-06 14:15:17'),
(4, 'M Farhan Pratama', 'pratamapaan@gmail.com', '$2y$10$yvK0l4mhifvm6vCraR2yWuY0PBD3RSfq6QqwQbx1B50hg7s/02Ym2', 'mahasiswa', '062330801582', NULL, 'Manajemen Informatika', '2026-05-06 14:15:17'),
(5, 'Muhammad Syukron', 'syukron@gmail.com', '$2y$10$RWn6h0sL.S31d0B7ERx14ehk7B6YDfA1BecGvIBuU64QPqjlaBwlK', 'mahasiswa', '062330801586', NULL, 'Manajemen Informatika', '2026-05-06 14:15:17'),
(6, 'Ikqi Nurpadilah', 'ikqi@gmail.com', '$2y$10$axs0HQcPIOIa348s3nvUh.6KbPg18DO06Ya.mxVOdCSpuBcz40QEm', 'mahasiswa', '062330801578', NULL, 'Manajemen Informatika', '2026-05-06 14:15:17'),
(7, 'Admin Akademik', 'admin@gmail.com', '$2y$10$mtG4sckOQqOZjsTB5tjaG.6o6ELXRTfOIx167XhJlYEvgT3X4IfRW', 'admin', NULL, NULL, NULL, '2026-05-06 14:15:17');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ketersediaan`
--
ALTER TABLE `ketersediaan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dosen_id` (`dosen_id`);

--
-- Indexes for table `konsultasi`
--
ALTER TABLE `konsultasi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mahasiswa_id` (`mahasiswa_id`),
  ADD KEY `dosen_id` (`dosen_id`);

--
-- Indexes for table `pengguna`
--
ALTER TABLE `pengguna`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ketersediaan`
--
ALTER TABLE `ketersediaan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `konsultasi`
--
ALTER TABLE `konsultasi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `pengguna`
--
ALTER TABLE `pengguna`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ketersediaan`
--
ALTER TABLE `ketersediaan`
  ADD CONSTRAINT `ketersediaan_ibfk_1` FOREIGN KEY (`dosen_id`) REFERENCES `pengguna` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `konsultasi`
--
ALTER TABLE `konsultasi`
  ADD CONSTRAINT `konsultasi_ibfk_1` FOREIGN KEY (`mahasiswa_id`) REFERENCES `pengguna` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `konsultasi_ibfk_2` FOREIGN KEY (`dosen_id`) REFERENCES `pengguna` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
