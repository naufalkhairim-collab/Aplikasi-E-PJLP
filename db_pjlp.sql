-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 12, 2026 at 09:01 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_pjlp`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbl_absensi`
--

CREATE TABLE `tbl_absensi` (
  `id_absen` int(11) NOT NULL,
  `id_kontrak` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `status_kehadiran` enum('Hadir','Alpa','Izin','Sakit','Cuti','Dinas Luar') NOT NULL,
  `id_pengajuan_cuti` int(11) DEFAULT NULL,
  `jam_masuk` time DEFAULT NULL,
  `foto_masuk` varchar(255) DEFAULT NULL,
  `lat_masuk` varchar(50) DEFAULT NULL,
  `long_masuk` varchar(50) DEFAULT NULL,
  `status_masuk` enum('Tepat Waktu','Terlambat') DEFAULT NULL,
  `jam_pulang` time DEFAULT NULL,
  `foto_pulang` varchar(255) DEFAULT NULL,
  `lat_pulang` varchar(50) DEFAULT NULL,
  `long_pulang` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_dpa`
--

CREATE TABLE `tbl_dpa` (
  `id_dpa` int(11) NOT NULL,
  `id_unit_kerja` int(11) NOT NULL,
  `tahun_anggaran` year(4) NOT NULL,
  `nomor_dpa` varchar(150) NOT NULL,
  `tgl_dpa` date NOT NULL,
  `total_anggaran_dpa` decimal(20,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_dpa`
--

INSERT INTO `tbl_dpa` (`id_dpa`, `id_unit_kerja`, `tahun_anggaran`, `nomor_dpa`, `tgl_dpa`, `total_anggaran_dpa`) VALUES
(1, 1, '2025', 'DPA/001/SETWAN-UMUM/2025', '2025-01-02', 1500000000.00),
(2, 2, '2025', 'DPA/002/SETWAN-SIDANG/2025', '2025-01-02', 800000000.00);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_dpa_detail`
--

CREATE TABLE `tbl_dpa_detail` (
  `id_dpa_detail` int(11) NOT NULL,
  `id_dpa` int(11) NOT NULL,
  `id_rekening` int(11) NOT NULL,
  `pagu_anggaran_rekening` decimal(20,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_dpa_detail`
--

INSERT INTO `tbl_dpa_detail` (`id_dpa_detail`, `id_dpa`, `id_rekening`, `pagu_anggaran_rekening`) VALUES
(1, 1, 2, 600000000.00),
(2, 1, 3, 500000000.00),
(3, 1, 4, 400000000.00),
(4, 2, 1, 800000000.00);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_gaji_pjlp`
--

CREATE TABLE `tbl_gaji_pjlp` (
  `id_gaji_pjlp` int(11) NOT NULL,
  `id_kontrak` int(11) NOT NULL,
  `gaji_pokok` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_gaji_pjlp`
--

INSERT INTO `tbl_gaji_pjlp` (`id_gaji_pjlp`, `id_kontrak`, `gaji_pokok`) VALUES
(1, 1, 9999999999.99);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_jadwal_kerja`
--

CREATE TABLE `tbl_jadwal_kerja` (
  `id_jadwal` int(11) NOT NULL,
  `nama_jadwal` varchar(100) NOT NULL,
  `jam_masuk` time NOT NULL,
  `jam_pulang` time NOT NULL,
  `toleransi_masuk_menit` int(3) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_jadwal_kerja`
--

INSERT INTO `tbl_jadwal_kerja` (`id_jadwal`, `nama_jadwal`, `jam_masuk`, `jam_pulang`, `toleransi_masuk_menit`) VALUES
(1, 'Reguler (Senin - Kamis)', '08:00:00', '16:30:00', 30),
(2, 'Reguler (Jumat)', '08:00:00', '11:30:00', 30),
(3, 'Piket Satpol PP / Security', '07:00:00', '19:00:00', 15);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_kinerja`
--

CREATE TABLE `tbl_kinerja` (
  `id_kinerja` int(11) NOT NULL,
  `id_kontrak` int(11) NOT NULL,
  `id_user_penilai` int(11) NOT NULL,
  `bulan` int(2) NOT NULL,
  `tahun` year(4) NOT NULL,
  `skor_kinerja` int(3) NOT NULL,
  `catatan_evaluasi` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_komponen_gaji`
--

CREATE TABLE `tbl_komponen_gaji` (
  `id_komponen` int(11) NOT NULL,
  `nama_komponen` varchar(150) NOT NULL,
  `tipe` enum('tunjangan','potongan') NOT NULL,
  `nominal_default` decimal(12,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_komponen_gaji`
--

INSERT INTO `tbl_komponen_gaji` (`id_komponen`, `nama_komponen`, `tipe`, `nominal_default`) VALUES
(1, 'Tunjangan Makan', 'tunjangan', 50000.00),
(2, 'Tunjangan Transport', 'tunjangan', 25000.00),
(3, 'Uang Lembur', 'tunjangan', 20000.00),
(4, 'Tunjangan Hari Raya (THR)', 'tunjangan', 0.00),
(5, 'BPJS Kesehatan (1%)', 'potongan', 0.00),
(6, 'BPJS Ketenagakerjaan (2%)', 'potongan', 0.00),
(7, 'PPh 21', 'potongan', 0.00),
(8, 'Keterlambatan', 'potongan', 250000000.00),
(9, 'Simpanan Koperasi', 'potongan', 100000.00);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_kontrak`
--

CREATE TABLE `tbl_kontrak` (
  `id_kontrak` int(11) NOT NULL,
  `id_pjlp` int(11) NOT NULL,
  `id_unit_kerja` int(11) NOT NULL,
  `id_dpa_detail` int(11) NOT NULL,
  `nomor_kontrak` varchar(150) NOT NULL,
  `tgl_mulai_kontrak` date NOT NULL,
  `tgl_selesai_kontrak` date NOT NULL,
  `status_kontrak` enum('Aktif','Berakhir') NOT NULL DEFAULT 'Aktif',
  `file_kontrak` varchar(255) DEFAULT NULL COMMENT 'File PDF/Gambar Scan Kontrak'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_kontrak`
--

INSERT INTO `tbl_kontrak` (`id_kontrak`, `id_pjlp`, `id_unit_kerja`, `id_dpa_detail`, `nomor_kontrak`, `tgl_mulai_kontrak`, `tgl_selesai_kontrak`, `status_kontrak`, `file_kontrak`) VALUES
(1, 2, 1, 4, 'SPK/2025/001', '2024-02-17', '2026-02-17', 'Aktif', 'KONTRAK_1770918364_698e11dc261f7.png');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_kriteria_penilaian`
--

CREATE TABLE `tbl_kriteria_penilaian` (
  `id_kriteria` int(11) NOT NULL,
  `nama_kriteria` varchar(150) NOT NULL,
  `bobot_persen` int(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_kriteria_penilaian`
--

INSERT INTO `tbl_kriteria_penilaian` (`id_kriteria`, `nama_kriteria`, `bobot_persen`) VALUES
(1, 'Disiplin & Kehadiran', 30),
(2, 'Kualitas Hasil Kerja', 30),
(3, 'Kerjasama Tim (Teamwork)', 20),
(4, 'Inisiatif & Perilaku', 20);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_kuota_cuti_pjlp`
--

CREATE TABLE `tbl_kuota_cuti_pjlp` (
  `id_kuota` int(11) NOT NULL,
  `id_pjlp` int(11) NOT NULL,
  `id_master_cuti` int(11) NOT NULL,
  `tahun` year(4) NOT NULL,
  `kuota_awal` int(3) NOT NULL,
  `kuota_terpakai` int(3) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_kuota_cuti_pjlp`
--

INSERT INTO `tbl_kuota_cuti_pjlp` (`id_kuota`, `id_pjlp`, `id_master_cuti`, `tahun`, `kuota_awal`, `kuota_terpakai`) VALUES
(1, 2, 1, '2025', 12, 0),
(2, 2, 3, '2025', 3, 0);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_lokasi_kantor`
--

CREATE TABLE `tbl_lokasi_kantor` (
  `id_lokasi` int(11) NOT NULL,
  `nama_lokasi` varchar(150) NOT NULL,
  `latitude` varchar(50) NOT NULL,
  `longitude` varchar(50) NOT NULL,
  `radius_meter` int(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_lokasi_kantor`
--

INSERT INTO `tbl_lokasi_kantor` (`id_lokasi`, `nama_lokasi`, `latitude`, `longitude`, `radius_meter`) VALUES
(1, 'DPRD Provinsi Kalimantan Selatan', '-3.3151450447170703', '114.59139370587063', 100);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_master_cuti`
--

CREATE TABLE `tbl_master_cuti` (
  `id_master_cuti` int(11) NOT NULL,
  `kode_cuti` varchar(20) NOT NULL,
  `nama_cuti` varchar(100) NOT NULL,
  `tipe_cuti` enum('Cuti','Izin','Sakit','Dinas Luar') NOT NULL,
  `memerlukan_file` tinyint(1) NOT NULL DEFAULT 0,
  `mengurangi_kuota` tinyint(1) NOT NULL DEFAULT 0,
  `potongan_gaji_persen` decimal(5,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_master_cuti`
--

INSERT INTO `tbl_master_cuti` (`id_master_cuti`, `kode_cuti`, `nama_cuti`, `tipe_cuti`, `memerlukan_file`, `mengurangi_kuota`, `potongan_gaji_persen`) VALUES
(1, 'CT', 'Cuti Tahunan', 'Cuti', 0, 1, 0.00),
(2, 'CB', 'Cuti Bersalin / Melahirkan', 'Cuti', 1, 0, 0.00),
(3, 'CAP', 'Cuti Alasan Penting', 'Cuti', 1, 1, 0.00),
(4, 'SKT', 'Izin Sakit (Surat Dokter)', 'Sakit', 1, 0, 0.00),
(5, 'IZN', 'Izin Terlambat / Pulang Cepat', 'Izin', 0, 0, 1.50),
(6, 'DL', 'Dinas Luar / Perjalanan Dinas', 'Dinas Luar', 1, 0, 0.00),
(7, 'ALPA', 'Tanpa Keterangan', '', 0, 0, 5.00);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_master_rekening`
--

CREATE TABLE `tbl_master_rekening` (
  `id_rekening` int(11) NOT NULL,
  `kode_rekening` varchar(100) NOT NULL,
  `nama_rekening` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_master_rekening`
--

INSERT INTO `tbl_master_rekening` (`id_rekening`, `kode_rekening`, `nama_rekening`) VALUES
(1, '5.1.02.02.01.0012', 'Belanja Jasa Tenaga Administrasi'),
(2, '5.1.02.02.01.0013', 'Belanja Jasa Tenaga Kebersihan (Cleaning Service)'),
(3, '5.1.02.02.01.0014', 'Belanja Jasa Tenaga Keamanan (Security)'),
(4, '5.1.02.02.01.0015', 'Belanja Jasa Tenaga Supir (Driver)'),
(5, '5.1.02.02.01.0016', 'Belanja Jasa Tenaga Teknisi / IT Support'),
(6, '5.1.02.02.01.0026', 'Belanja Jasa Tenaga Publikasi dan Dokumentasi');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_notifikasi`
--

CREATE TABLE `tbl_notifikasi` (
  `id_notifikasi` int(11) NOT NULL,
  `id_user_penerima` int(11) DEFAULT NULL COMMENT 'Jika NULL, notif untuk admin',
  `judul_notif` varchar(100) NOT NULL,
  `isi_notif` text NOT NULL,
  `link_url` varchar(255) DEFAULT NULL COMMENT 'Link ke halaman terkait',
  `status_baca` enum('Belum','Sudah') DEFAULT 'Belum',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_payroll`
--

CREATE TABLE `tbl_payroll` (
  `id_payroll` int(11) NOT NULL,
  `id_kontrak` int(11) NOT NULL,
  `id_dpa_detail` int(11) NOT NULL,
  `bulan` int(2) NOT NULL,
  `tahun` year(4) NOT NULL,
  `gaji_pokok` decimal(12,2) NOT NULL,
  `total_tunjangan` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_potongan` decimal(12,2) NOT NULL DEFAULT 0.00,
  `gaji_diterima` decimal(12,2) NOT NULL,
  `status_bayar` enum('Proses','Lunas') NOT NULL DEFAULT 'Proses',
  `tgl_dibuat` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_payroll_detail`
--

CREATE TABLE `tbl_payroll_detail` (
  `id_payroll_detail` int(11) NOT NULL,
  `id_payroll` int(11) NOT NULL,
  `id_komponen_gaji` int(11) NOT NULL,
  `nama_komponen` varchar(150) NOT NULL,
  `tipe` enum('tunjangan','potongan') NOT NULL,
  `jumlah` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_pengajuan_cuti`
--

CREATE TABLE `tbl_pengajuan_cuti` (
  `id_pengajuan` int(11) NOT NULL,
  `id_pjlp` int(11) NOT NULL,
  `id_master_cuti` int(11) NOT NULL,
  `tgl_diajukan` datetime NOT NULL,
  `tgl_mulai` date NOT NULL,
  `tgl_selesai` date NOT NULL,
  `jumlah_hari` int(3) NOT NULL,
  `keterangan_pjlp` text DEFAULT NULL,
  `file_pendukung` varchar(255) DEFAULT NULL,
  `status_pengajuan` enum('Diajukan','Disetujui','Ditolak') NOT NULL DEFAULT 'Diajukan',
  `id_user_penyetuju` int(11) DEFAULT NULL,
  `tgl_disetujui` datetime DEFAULT NULL,
  `catatan_atasan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_pengaturan`
--

CREATE TABLE `tbl_pengaturan` (
  `id_pengaturan` tinyint(1) NOT NULL DEFAULT 1,
  `nama_aplikasi` varchar(100) NOT NULL,
  `nama_instansi` varchar(255) NOT NULL,
  `nama_pimpinan` varchar(150) DEFAULT NULL,
  `nip_pimpinan` varchar(30) DEFAULT NULL,
  `alamat_instansi` text DEFAULT NULL,
  `telp_instansi` varchar(20) DEFAULT NULL,
  `email_instansi` varchar(100) DEFAULT NULL,
  `logo_instansi` varchar(255) DEFAULT NULL,
  `background_login` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_pengaturan`
--

INSERT INTO `tbl_pengaturan` (`id_pengaturan`, `nama_aplikasi`, `nama_instansi`, `nama_pimpinan`, `nip_pimpinan`, `alamat_instansi`, `telp_instansi`, `email_instansi`, `logo_instansi`, `background_login`) VALUES
(1, 'E-PJLP Setwan Kalsel', 'Sekretariat DPRD Provinsi Kalimantan Selatan', 'MUHAMMAD NAUFAL KHAIRI', '312343345', 'Jl. Lambung Mangkurat No.18, Kertak Baru Ilir, Kec. Banjarmasin Tengah, Kota Banjarmasin, Kalimantan Selatan 70111', '(0511) 3353456', 'setwan@kalselprov.go.id', 'logo_1765806526.png', 'bg_1765806610.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_penilaian_detail`
--

CREATE TABLE `tbl_penilaian_detail` (
  `id_detail` int(11) NOT NULL,
  `id_penilaian` int(11) NOT NULL,
  `id_kriteria` int(11) NOT NULL,
  `id_penilai` int(11) NOT NULL COMMENT 'ID User atau ID PJLP',
  `tipe_penilai` enum('admin','pimpinan','keuangan') NOT NULL,
  `nilai_input` int(3) NOT NULL COMMENT 'Skala 1-100'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_penilaian_detail`
--

INSERT INTO `tbl_penilaian_detail` (`id_detail`, `id_penilaian`, `id_kriteria`, `id_penilai`, `tipe_penilai`, `nilai_input`) VALUES
(1, 1, 1, 1, 'admin', 90),
(2, 1, 2, 1, 'admin', 90),
(3, 1, 3, 1, 'admin', 90),
(4, 1, 4, 1, 'admin', 90),
(5, 1, 1, 3, 'keuangan', 80),
(6, 1, 2, 3, 'keuangan', 80),
(7, 1, 3, 3, 'keuangan', 80),
(8, 1, 4, 3, 'keuangan', 80),
(9, 1, 1, 2, 'pimpinan', 70),
(10, 1, 2, 2, 'pimpinan', 90),
(11, 1, 3, 2, 'pimpinan', 80),
(12, 1, 4, 2, 'pimpinan', 77);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_penilaian_header`
--

CREATE TABLE `tbl_penilaian_header` (
  `id_penilaian` int(11) NOT NULL,
  `id_kontrak` int(11) NOT NULL,
  `bulan` int(2) NOT NULL,
  `tahun` year(4) NOT NULL,
  `nilai_akhir` decimal(5,2) DEFAULT 0.00,
  `status_finalisasi` enum('Draft','Final') DEFAULT 'Draft'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_penilaian_header`
--

INSERT INTO `tbl_penilaian_header` (`id_penilaian`, `id_kontrak`, `bulan`, `tahun`, `nilai_akhir`, `status_finalisasi`) VALUES
(1, 1, 2, '2026', 82.63, 'Draft');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_pjlp`
--

CREATE TABLE `tbl_pjlp` (
  `id_pjlp` int(11) NOT NULL,
  `nik` varchar(16) NOT NULL,
  `nama_lengkap` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `tempat_lahir` varchar(100) DEFAULT NULL,
  `tgl_lahir` date DEFAULT NULL,
  `jenis_kelamin` enum('L','P') NOT NULL,
  `alamat` text DEFAULT NULL,
  `no_hp` varchar(15) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `foto` varchar(255) DEFAULT 'default-user.png',
  `nib_nomor` varchar(100) NOT NULL,
  `nib_tgl_terbit` date DEFAULT NULL,
  `file_ktp` varchar(255) DEFAULT NULL,
  `file_nib` varchar(255) DEFAULT NULL,
  `status_pjlp` enum('Aktif','Non-Aktif') NOT NULL DEFAULT 'Aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_pjlp`
--

INSERT INTO `tbl_pjlp` (`id_pjlp`, `nik`, `nama_lengkap`, `password`, `tempat_lahir`, `tgl_lahir`, `jenis_kelamin`, `alamat`, `no_hp`, `email`, `foto`, `nib_nomor`, `nib_tgl_terbit`, `file_ktp`, `file_nib`, `status_pjlp`) VALUES
(2, '2323232131231231', 'Suteno', 'cfb5272df036d0fb55a353c795319518', 'Banjarmasin', '1992-01-01', 'L', 'Jl. Cemara Raya', '087348273468', 'suteno@gmail.com', 'profile_pjlp_2_1765826814.jpg', '12893123', '2020-12-15', 'ktp_2323232131231231_1765807715.jpg', 'nib_2323232131231231_1765807715.jpg', 'Aktif');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_template_surat`
--

CREATE TABLE `tbl_template_surat` (
  `id_template` int(11) NOT NULL,
  `kode_template` varchar(50) NOT NULL,
  `nama_dokumen` varchar(255) NOT NULL,
  `isi_template` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_tugas`
--

CREATE TABLE `tbl_tugas` (
  `id_tugas` int(11) NOT NULL,
  `id_user_pemberi` int(11) NOT NULL,
  `id_pjlp_penerima` int(11) NOT NULL,
  `judul_tugas` varchar(255) NOT NULL,
  `deskripsi_tugas` text DEFAULT NULL,
  `tgl_beri` datetime NOT NULL,
  `deadline` datetime DEFAULT NULL,
  `status_tugas` enum('Baru','Dikerjakan','Selesai','Ditinjau') NOT NULL DEFAULT 'Baru'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_unit_kerja`
--

CREATE TABLE `tbl_unit_kerja` (
  `id_unit_kerja` int(11) NOT NULL,
  `nama_unit_kerja` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_unit_kerja`
--

INSERT INTO `tbl_unit_kerja` (`id_unit_kerja`, `nama_unit_kerja`) VALUES
(1, 'Bagian Umum dan Kepegawaian'),
(2, 'Bagian Persidangan dan Perundang-undangan'),
(3, 'Bagian Keuangan'),
(4, 'Bagian Fasilitasi Penganggaran dan Pengawasan'),
(5, 'Sub Bagian Tata Usaha dan Rumah Tangga'),
(6, 'Sub Bagian Protokol dan Kehumasan');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_users`
--

CREATE TABLE `tbl_users` (
  `id_user` int(11) NOT NULL,
  `nama_lengkap` varchar(150) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `level` enum('admin','pimpinan','keuangan') NOT NULL,
  `foto` varchar(255) DEFAULT 'default-user.png',
  `status_aktif` enum('1','0') DEFAULT '1',
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_users`
--

INSERT INTO `tbl_users` (`id_user`, `nama_lengkap`, `email`, `no_hp`, `username`, `password`, `level`, `foto`, `status_aktif`, `last_login`, `created_at`) VALUES
(1, 'Administrator Utama', 'admin@instansi.go.id', '08562387332', 'admin', '21232f297a57a5a743894a0e4a801fc3', 'admin', 'profile_admin_1_1765808353.jpg', '1', '2026-02-13 01:45:15', '2025-12-15 12:50:24'),
(2, 'Pimpinan', 'pimpinan@gmail.com', '087782736827', 'pimpinan', '90973652b88fe07d05a4304f0a945de8', 'pimpinan', 'user_1765825930.jpg', '1', '2026-02-13 02:52:46', '2025-12-15 19:12:10'),
(3, 'Keuangan', 'keuangan@gmail.com', '081128736273', 'keuangan', 'a4151d4b2856ec63368a7c784b1f0a6e', 'keuangan', 'user_1765826861.jpg', '1', '2026-02-13 02:46:50', '2025-12-15 19:27:41');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbl_absensi`
--
ALTER TABLE `tbl_absensi`
  ADD PRIMARY KEY (`id_absen`),
  ADD UNIQUE KEY `id_kontrak` (`id_kontrak`,`tanggal`),
  ADD KEY `id_pengajuan_cuti` (`id_pengajuan_cuti`);

--
-- Indexes for table `tbl_dpa`
--
ALTER TABLE `tbl_dpa`
  ADD PRIMARY KEY (`id_dpa`),
  ADD KEY `id_unit_kerja` (`id_unit_kerja`);

--
-- Indexes for table `tbl_dpa_detail`
--
ALTER TABLE `tbl_dpa_detail`
  ADD PRIMARY KEY (`id_dpa_detail`),
  ADD KEY `id_dpa` (`id_dpa`),
  ADD KEY `id_rekening` (`id_rekening`);

--
-- Indexes for table `tbl_gaji_pjlp`
--
ALTER TABLE `tbl_gaji_pjlp`
  ADD PRIMARY KEY (`id_gaji_pjlp`),
  ADD UNIQUE KEY `id_kontrak` (`id_kontrak`);

--
-- Indexes for table `tbl_jadwal_kerja`
--
ALTER TABLE `tbl_jadwal_kerja`
  ADD PRIMARY KEY (`id_jadwal`);

--
-- Indexes for table `tbl_kinerja`
--
ALTER TABLE `tbl_kinerja`
  ADD PRIMARY KEY (`id_kinerja`),
  ADD KEY `id_kontrak` (`id_kontrak`),
  ADD KEY `id_user_penilai` (`id_user_penilai`);

--
-- Indexes for table `tbl_komponen_gaji`
--
ALTER TABLE `tbl_komponen_gaji`
  ADD PRIMARY KEY (`id_komponen`);

--
-- Indexes for table `tbl_kontrak`
--
ALTER TABLE `tbl_kontrak`
  ADD PRIMARY KEY (`id_kontrak`),
  ADD KEY `id_pjlp` (`id_pjlp`),
  ADD KEY `id_unit_kerja` (`id_unit_kerja`),
  ADD KEY `id_dpa_detail` (`id_dpa_detail`);

--
-- Indexes for table `tbl_kriteria_penilaian`
--
ALTER TABLE `tbl_kriteria_penilaian`
  ADD PRIMARY KEY (`id_kriteria`);

--
-- Indexes for table `tbl_kuota_cuti_pjlp`
--
ALTER TABLE `tbl_kuota_cuti_pjlp`
  ADD PRIMARY KEY (`id_kuota`),
  ADD KEY `id_pjlp` (`id_pjlp`),
  ADD KEY `id_master_cuti` (`id_master_cuti`);

--
-- Indexes for table `tbl_lokasi_kantor`
--
ALTER TABLE `tbl_lokasi_kantor`
  ADD PRIMARY KEY (`id_lokasi`);

--
-- Indexes for table `tbl_master_cuti`
--
ALTER TABLE `tbl_master_cuti`
  ADD PRIMARY KEY (`id_master_cuti`),
  ADD UNIQUE KEY `kode_cuti` (`kode_cuti`);

--
-- Indexes for table `tbl_master_rekening`
--
ALTER TABLE `tbl_master_rekening`
  ADD PRIMARY KEY (`id_rekening`),
  ADD UNIQUE KEY `kode_rekening` (`kode_rekening`);

--
-- Indexes for table `tbl_notifikasi`
--
ALTER TABLE `tbl_notifikasi`
  ADD PRIMARY KEY (`id_notifikasi`);

--
-- Indexes for table `tbl_payroll`
--
ALTER TABLE `tbl_payroll`
  ADD PRIMARY KEY (`id_payroll`),
  ADD UNIQUE KEY `id_kontrak` (`id_kontrak`,`bulan`,`tahun`),
  ADD KEY `id_dpa_detail` (`id_dpa_detail`);

--
-- Indexes for table `tbl_payroll_detail`
--
ALTER TABLE `tbl_payroll_detail`
  ADD PRIMARY KEY (`id_payroll_detail`),
  ADD KEY `id_payroll` (`id_payroll`),
  ADD KEY `id_komponen_gaji` (`id_komponen_gaji`);

--
-- Indexes for table `tbl_pengajuan_cuti`
--
ALTER TABLE `tbl_pengajuan_cuti`
  ADD PRIMARY KEY (`id_pengajuan`),
  ADD KEY `id_pjlp` (`id_pjlp`),
  ADD KEY `id_master_cuti` (`id_master_cuti`),
  ADD KEY `id_user_penyetuju` (`id_user_penyetuju`);

--
-- Indexes for table `tbl_pengaturan`
--
ALTER TABLE `tbl_pengaturan`
  ADD PRIMARY KEY (`id_pengaturan`);

--
-- Indexes for table `tbl_penilaian_detail`
--
ALTER TABLE `tbl_penilaian_detail`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `id_penilaian` (`id_penilaian`);

--
-- Indexes for table `tbl_penilaian_header`
--
ALTER TABLE `tbl_penilaian_header`
  ADD PRIMARY KEY (`id_penilaian`),
  ADD KEY `id_kontrak` (`id_kontrak`);

--
-- Indexes for table `tbl_pjlp`
--
ALTER TABLE `tbl_pjlp`
  ADD PRIMARY KEY (`id_pjlp`),
  ADD UNIQUE KEY `nik` (`nik`),
  ADD UNIQUE KEY `nib_nomor` (`nib_nomor`);

--
-- Indexes for table `tbl_template_surat`
--
ALTER TABLE `tbl_template_surat`
  ADD PRIMARY KEY (`id_template`),
  ADD UNIQUE KEY `kode_template` (`kode_template`);

--
-- Indexes for table `tbl_tugas`
--
ALTER TABLE `tbl_tugas`
  ADD PRIMARY KEY (`id_tugas`),
  ADD KEY `id_user_pemberi` (`id_user_pemberi`),
  ADD KEY `id_pjlp_penerima` (`id_pjlp_penerima`);

--
-- Indexes for table `tbl_unit_kerja`
--
ALTER TABLE `tbl_unit_kerja`
  ADD PRIMARY KEY (`id_unit_kerja`);

--
-- Indexes for table `tbl_users`
--
ALTER TABLE `tbl_users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_absensi`
--
ALTER TABLE `tbl_absensi`
  MODIFY `id_absen` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tbl_dpa`
--
ALTER TABLE `tbl_dpa`
  MODIFY `id_dpa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbl_dpa_detail`
--
ALTER TABLE `tbl_dpa_detail`
  MODIFY `id_dpa_detail` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tbl_gaji_pjlp`
--
ALTER TABLE `tbl_gaji_pjlp`
  MODIFY `id_gaji_pjlp` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tbl_jadwal_kerja`
--
ALTER TABLE `tbl_jadwal_kerja`
  MODIFY `id_jadwal` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tbl_kinerja`
--
ALTER TABLE `tbl_kinerja`
  MODIFY `id_kinerja` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tbl_komponen_gaji`
--
ALTER TABLE `tbl_komponen_gaji`
  MODIFY `id_komponen` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `tbl_kontrak`
--
ALTER TABLE `tbl_kontrak`
  MODIFY `id_kontrak` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tbl_kriteria_penilaian`
--
ALTER TABLE `tbl_kriteria_penilaian`
  MODIFY `id_kriteria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tbl_kuota_cuti_pjlp`
--
ALTER TABLE `tbl_kuota_cuti_pjlp`
  MODIFY `id_kuota` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbl_lokasi_kantor`
--
ALTER TABLE `tbl_lokasi_kantor`
  MODIFY `id_lokasi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tbl_master_cuti`
--
ALTER TABLE `tbl_master_cuti`
  MODIFY `id_master_cuti` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tbl_master_rekening`
--
ALTER TABLE `tbl_master_rekening`
  MODIFY `id_rekening` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tbl_notifikasi`
--
ALTER TABLE `tbl_notifikasi`
  MODIFY `id_notifikasi` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_payroll`
--
ALTER TABLE `tbl_payroll`
  MODIFY `id_payroll` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tbl_payroll_detail`
--
ALTER TABLE `tbl_payroll_detail`
  MODIFY `id_payroll_detail` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tbl_pengajuan_cuti`
--
ALTER TABLE `tbl_pengajuan_cuti`
  MODIFY `id_pengajuan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tbl_penilaian_detail`
--
ALTER TABLE `tbl_penilaian_detail`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `tbl_penilaian_header`
--
ALTER TABLE `tbl_penilaian_header`
  MODIFY `id_penilaian` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tbl_pjlp`
--
ALTER TABLE `tbl_pjlp`
  MODIFY `id_pjlp` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbl_template_surat`
--
ALTER TABLE `tbl_template_surat`
  MODIFY `id_template` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_tugas`
--
ALTER TABLE `tbl_tugas`
  MODIFY `id_tugas` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tbl_unit_kerja`
--
ALTER TABLE `tbl_unit_kerja`
  MODIFY `id_unit_kerja` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tbl_users`
--
ALTER TABLE `tbl_users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tbl_absensi`
--
ALTER TABLE `tbl_absensi`
  ADD CONSTRAINT `tbl_absensi_ibfk_1` FOREIGN KEY (`id_kontrak`) REFERENCES `tbl_kontrak` (`id_kontrak`) ON DELETE CASCADE,
  ADD CONSTRAINT `tbl_absensi_ibfk_2` FOREIGN KEY (`id_pengajuan_cuti`) REFERENCES `tbl_pengajuan_cuti` (`id_pengajuan`) ON DELETE SET NULL;

--
-- Constraints for table `tbl_dpa`
--
ALTER TABLE `tbl_dpa`
  ADD CONSTRAINT `tbl_dpa_ibfk_1` FOREIGN KEY (`id_unit_kerja`) REFERENCES `tbl_unit_kerja` (`id_unit_kerja`);

--
-- Constraints for table `tbl_dpa_detail`
--
ALTER TABLE `tbl_dpa_detail`
  ADD CONSTRAINT `tbl_dpa_detail_ibfk_1` FOREIGN KEY (`id_dpa`) REFERENCES `tbl_dpa` (`id_dpa`) ON DELETE CASCADE,
  ADD CONSTRAINT `tbl_dpa_detail_ibfk_2` FOREIGN KEY (`id_rekening`) REFERENCES `tbl_master_rekening` (`id_rekening`);

--
-- Constraints for table `tbl_gaji_pjlp`
--
ALTER TABLE `tbl_gaji_pjlp`
  ADD CONSTRAINT `tbl_gaji_pjlp_ibfk_1` FOREIGN KEY (`id_kontrak`) REFERENCES `tbl_kontrak` (`id_kontrak`) ON DELETE CASCADE;

--
-- Constraints for table `tbl_kinerja`
--
ALTER TABLE `tbl_kinerja`
  ADD CONSTRAINT `tbl_kinerja_ibfk_1` FOREIGN KEY (`id_kontrak`) REFERENCES `tbl_kontrak` (`id_kontrak`) ON DELETE CASCADE,
  ADD CONSTRAINT `tbl_kinerja_ibfk_2` FOREIGN KEY (`id_user_penilai`) REFERENCES `tbl_users` (`id_user`);

--
-- Constraints for table `tbl_kontrak`
--
ALTER TABLE `tbl_kontrak`
  ADD CONSTRAINT `tbl_kontrak_ibfk_1` FOREIGN KEY (`id_pjlp`) REFERENCES `tbl_pjlp` (`id_pjlp`),
  ADD CONSTRAINT `tbl_kontrak_ibfk_2` FOREIGN KEY (`id_unit_kerja`) REFERENCES `tbl_unit_kerja` (`id_unit_kerja`),
  ADD CONSTRAINT `tbl_kontrak_ibfk_3` FOREIGN KEY (`id_dpa_detail`) REFERENCES `tbl_dpa_detail` (`id_dpa_detail`);

--
-- Constraints for table `tbl_kuota_cuti_pjlp`
--
ALTER TABLE `tbl_kuota_cuti_pjlp`
  ADD CONSTRAINT `tbl_kuota_cuti_pjlp_ibfk_1` FOREIGN KEY (`id_pjlp`) REFERENCES `tbl_pjlp` (`id_pjlp`) ON DELETE CASCADE,
  ADD CONSTRAINT `tbl_kuota_cuti_pjlp_ibfk_2` FOREIGN KEY (`id_master_cuti`) REFERENCES `tbl_master_cuti` (`id_master_cuti`);

--
-- Constraints for table `tbl_payroll`
--
ALTER TABLE `tbl_payroll`
  ADD CONSTRAINT `tbl_payroll_ibfk_1` FOREIGN KEY (`id_kontrak`) REFERENCES `tbl_kontrak` (`id_kontrak`),
  ADD CONSTRAINT `tbl_payroll_ibfk_2` FOREIGN KEY (`id_dpa_detail`) REFERENCES `tbl_dpa_detail` (`id_dpa_detail`);

--
-- Constraints for table `tbl_payroll_detail`
--
ALTER TABLE `tbl_payroll_detail`
  ADD CONSTRAINT `tbl_payroll_detail_ibfk_1` FOREIGN KEY (`id_payroll`) REFERENCES `tbl_payroll` (`id_payroll`) ON DELETE CASCADE,
  ADD CONSTRAINT `tbl_payroll_detail_ibfk_2` FOREIGN KEY (`id_komponen_gaji`) REFERENCES `tbl_komponen_gaji` (`id_komponen`);

--
-- Constraints for table `tbl_pengajuan_cuti`
--
ALTER TABLE `tbl_pengajuan_cuti`
  ADD CONSTRAINT `tbl_pengajuan_cuti_ibfk_1` FOREIGN KEY (`id_pjlp`) REFERENCES `tbl_pjlp` (`id_pjlp`),
  ADD CONSTRAINT `tbl_pengajuan_cuti_ibfk_2` FOREIGN KEY (`id_master_cuti`) REFERENCES `tbl_master_cuti` (`id_master_cuti`),
  ADD CONSTRAINT `tbl_pengajuan_cuti_ibfk_3` FOREIGN KEY (`id_user_penyetuju`) REFERENCES `tbl_users` (`id_user`) ON DELETE SET NULL;

--
-- Constraints for table `tbl_penilaian_detail`
--
ALTER TABLE `tbl_penilaian_detail`
  ADD CONSTRAINT `fk_detail_header` FOREIGN KEY (`id_penilaian`) REFERENCES `tbl_penilaian_header` (`id_penilaian`) ON DELETE CASCADE;

--
-- Constraints for table `tbl_penilaian_header`
--
ALTER TABLE `tbl_penilaian_header`
  ADD CONSTRAINT `fk_penilaian_kontrak` FOREIGN KEY (`id_kontrak`) REFERENCES `tbl_kontrak` (`id_kontrak`) ON DELETE CASCADE;

--
-- Constraints for table `tbl_tugas`
--
ALTER TABLE `tbl_tugas`
  ADD CONSTRAINT `tbl_tugas_ibfk_1` FOREIGN KEY (`id_user_pemberi`) REFERENCES `tbl_users` (`id_user`),
  ADD CONSTRAINT `tbl_tugas_ibfk_2` FOREIGN KEY (`id_pjlp_penerima`) REFERENCES `tbl_pjlp` (`id_pjlp`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
