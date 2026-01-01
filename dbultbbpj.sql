-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.0.30 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.1.0.6537
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for dbultbbpj
DROP DATABASE IF EXISTS `dbultbbpj`;
CREATE DATABASE IF NOT EXISTS `dbultbbpj` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `dbultbbpj`;

-- Dumping structure for table dbultbbpj.cache
DROP TABLE IF EXISTS `cache`;
CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table dbultbbpj.cache: ~2 rows (approximately)
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
	('laravel_cache_admin@ultbbpj.com|127.0.0.1', 'i:2;', 1750083917),
	('laravel_cache_admin@ultbbpj.com|127.0.0.1:timer', 'i:1750083917;', 1750083917);

-- Dumping structure for table dbultbbpj.cache_locks
DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table dbultbbpj.cache_locks: ~0 rows (approximately)

-- Dumping structure for table dbultbbpj.failed_jobs
DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table dbultbbpj.failed_jobs: ~0 rows (approximately)

-- Dumping structure for table dbultbbpj.jobs
DROP TABLE IF EXISTS `jobs`;
CREATE TABLE IF NOT EXISTS `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table dbultbbpj.jobs: ~0 rows (approximately)

-- Dumping structure for table dbultbbpj.job_batches
DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE IF NOT EXISTS `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table dbultbbpj.job_batches: ~0 rows (approximately)

-- Dumping structure for table dbultbbpj.migrations
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table dbultbbpj.migrations: ~16 rows (approximately)
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
	(1, '0001_01_01_000000_create_users_table', 1),
	(2, '0001_01_01_000001_create_cache_table', 1),
	(3, '0001_01_01_000002_create_jobs_table', 1),
	(4, '2025_06_05_121534_create_permohonans_table', 1),
	(5, '2025_06_05_143722_add_demographics_to_permohonans_table', 2),
	(6, '2025_06_05_170737_revert_layanan_dibutuhkan_to_string_in_permohonans_table', 3),
	(7, '2025_06_07_132423_add_is_admin_to_users_table', 4),
	(8, '2025_06_08_050053_add_status_and_registration_to_permohonans_table', 5),
	(9, '2025_06_08_053334_create_status_histories_table', 6),
	(10, '2025_06_13_103954_add_role_to_users_table', 7),
	(11, '2025_06_16_124619_add_profile_photo_path_to_users_table', 8),
	(12, '2025_06_16_131018_add_activity_timestamps_to_users_table', 9),
	(13, '2025_06_16_203910_add_activity_timestamps_to_users_table', 10),
	(14, '2025_06_16_213951_add_nip_to_users_table', 11),
	(17, '2025_07_28_083401_create_pengaduans_table', 12),
	(19, '2025_06_08_053334_create_status_pengaduan_table', 13);

-- Dumping structure for table dbultbbpj.password_reset_tokens
DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table dbultbbpj.password_reset_tokens: ~0 rows (approximately)

-- Dumping structure for table dbultbbpj.pengaduans
DROP TABLE IF EXISTS `pengaduans`;
CREATE TABLE IF NOT EXISTS `pengaduans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nama_lengkap` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nomor_ponsel` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `profesi` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `instansi` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `isi_aduan` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `path_bukti_aduan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('Diajukan','Diproses','Selesai') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Diajukan',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table dbultbbpj.pengaduans: ~2 rows (approximately)
INSERT INTO `pengaduans` (`id`, `nama_lengkap`, `nomor_ponsel`, `email`, `profesi`, `instansi`, `isi_aduan`, `path_bukti_aduan`, `status`, `created_at`, `updated_at`) VALUES
	(1, 'Prabowo', '081215000306', 'mail.bowo.me@gmail.com', 'dosen', 'Balai Bahasa Jambi', 'yeyeeyetestts', 'bukti-aduan/pAXzjhB9kYTGt92q0V1QG97AOy9MqWJd6YQMDuIz.png', 'Selesai', '2025-07-29 06:36:14', '2025-07-29 07:05:41'),
	(2, 'Alisha Salsabila Putri', '081215000306', 'mail.bowo.me@gmail.com', 'Mahasiswa', 'Balai Bahasa Jambi', 'iyaya', NULL, 'Diajukan', '2025-07-30 08:44:18', '2025-07-30 08:44:18');

-- Dumping structure for table dbultbbpj.permohonans
DROP TABLE IF EXISTS `permohonans`;
CREATE TABLE IF NOT EXISTS `permohonans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `no_registrasi` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama_lengkap` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `instansi` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nomor_ponsel` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jenis_kelamin` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pendidikan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `profesi` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `layanan_dibutuhkan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `isi_permohonan` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Diajukan',
  `path_surat_permohonan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `path_berkas_permohonan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permohonans_no_registrasi_unique` (`no_registrasi`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table dbultbbpj.permohonans: ~7 rows (approximately)
INSERT INTO `permohonans` (`id`, `no_registrasi`, `nama_lengkap`, `instansi`, `email`, `nomor_ponsel`, `jenis_kelamin`, `pendidikan`, `profesi`, `layanan_dibutuhkan`, `isi_permohonan`, `status`, `path_surat_permohonan`, `path_berkas_permohonan`, `created_at`, `updated_at`) VALUES
	(23, 'ULT-2025061214352888', 'Lawaris', 'Balai Bahasa Jambi', 'lawaris@mail.com', '085212345678', 'Laki-laki', 'SMA', 'Sopir', 'Saksi Ahli (Bahasa dan Hukum)', 'penyuntingan BAP ke polisi', 'Diproses', 'surat/Ec1kvwxvy65Y3iM8cCAWf84V4XGMMjz00UkZ20v5.pdf', 'berkas/QV8jQ6xU5CBK9zAZdVjJMqJ1V2bdh0lUlmOf8c6h.pdf', '2025-06-12 07:35:28', '2025-06-16 02:15:19'),
	(24, 'ULT-2025061714473810', 'Jalaludin', 'UIN Jambi', 'udin67662@gmail.com', '0858409904168', 'Laki-laki', 'S1', 'Mahasiswa', 'Praktik Kerja Lapangan (Pemagangan)', 'Pemagangan', 'Diajukan', 'surat/tqtPKs5Cxe5Ru5iOOTGJB2qhLPlmxikp9r2uTx2V.pdf', 'berkas/0n3VxMR1N8pj96IGAIQv85EMvKuB6JpRvWyHIPct.pdf', '2025-06-17 07:47:38', '2025-06-17 07:47:38'),
	(25, 'ULT-2025062413241493', 'Neti Puji Rahayu', 'Balai Bahasa Jambi', 'neti@mail.com', '081212345678', 'Perempuan', 'S1', 'PNS', 'UKBI', 'Pendampingan ukbi', 'Ditolak', 'surat/X3IVijVu5FxrVUITg7ut4FvmkeDTquvOKQRA8V04.pdf', 'berkas/pWbjCQJLQLvTWkQkeADLkVBGyqIKzqmYZ5ylbDdc.pdf', '2025-06-24 06:24:14', '2025-06-24 06:54:50'),
	(26, 'ULT-2025062414560644', 'Wesa Ostika', 'UIN Jambi', 'wesa@mail.com', '081212345678', 'Perempuan', 'S1', 'PNS', 'Penerjemahan Tulis', 'Penerjemahan aksara kuno', 'Diajukan', 'surat/Uad3TClJC2w9Tx58myA1r7aQZDoiTuOSO7T6L7aB.pdf', 'berkas/5tL1tILtew5Md2Eo6EPkU58hNflrqw3dCeznVLrt.pdf', '2025-06-24 07:56:06', '2025-06-24 07:56:06'),
	(27, 'ULT-2025062415282499', 'Wessa', 'BBPJ', 'wesa@mail.com', '081212345678', 'Perempuan', 'S1', 'PNS', 'Penerjemahan Tulis', 'PERMOHONAN PENERJEMAHAN TULIS', 'Diajukan', 'surat/7LBMC8wjUfLr1GVZWPHHAIiMJmV4vXrblwghpVI5.pdf', 'berkas/DaAv1OgLPXjYOGdnTwfToxPQDUCLrApVOrcKbrtR.pdf', '2025-06-24 08:28:24', '2025-06-24 08:28:24'),
	(28, 'ULT-2025070410223357', 'Wilya Ajah', 'UIN Jambi', 'wilya@mail.com', '081212345678', 'Perempuan', 'SMA', 'Mahasiswa', 'Kunjungan Edukasi', 'Kunjungan Edukasi ke Balai', 'Diproses', 'surat/CjYdqmsB0rBCMo4teuzmtCjGSFu32z9N3COhFaUV.pdf', 'berkas/G3mTjZVUz2CofnSm9zRPxydhMRRdYHe8cX3cMAre.pdf', '2025-07-04 03:22:33', '2025-07-04 03:24:35'),
	(29, 'ULT-2025073013291045', 'Ofren', 'Universitas Jambi', 'ofrendiala25@gmail.com', '082125461169', 'Laki-laki', 'SMA', 'Mahasiswa', 'UKBI', 'pengen tes', 'Diajukan', 'surat/z3wK2EDh4s05JkMGnorX1SUmGj5gV5HCbeJE1hXO.pdf', 'berkas/Qj7BWmf5Eb4EWFo51jAcXrKXqbm1iAcjqkFVQnRj.pdf', '2025-07-30 06:29:10', '2025-07-30 06:29:10');

-- Dumping structure for table dbultbbpj.sessions
DROP TABLE IF EXISTS `sessions`;
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table dbultbbpj.sessions: ~2 rows (approximately)
INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
	('DGLgCFDS4kbOn4YQoZSIksNA52qCOfF1AMurwP38', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoic2hvN2lLRmJucWVlYXdzREFKSDdhUFpBaFNrTFZ5aFBLeUdUM2t2aSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NjI6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9zdGFuZGFyLXBlbGF5YW5hbi9wcmFrdGlrLWtlcmphLWxhcGFuZ2FuIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1755509797),
	('GPm7Y4LtwHtu172dfwXce5ERRn8JzIXqj4pq9tyl', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiOG9QUzJDaFVWMElSU291YzA5RE5MOUNscnVpMHA5aHd5Vjk3RFlXMSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjA6Imh0dHA6Ly91bHRlYmJwai50ZXN0Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1755509797);

-- Dumping structure for table dbultbbpj.status_histories
DROP TABLE IF EXISTS `status_histories`;
CREATE TABLE IF NOT EXISTS `status_histories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `permohonan_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `keterangan` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `status_histories_permohonan_id_foreign` (`permohonan_id`),
  KEY `status_histories_user_id_foreign` (`user_id`),
  CONSTRAINT `status_histories_permohonan_id_foreign` FOREIGN KEY (`permohonan_id`) REFERENCES `permohonans` (`id`) ON DELETE CASCADE,
  CONSTRAINT `status_histories_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table dbultbbpj.status_histories: ~8 rows (approximately)
INSERT INTO `status_histories` (`id`, `permohonan_id`, `user_id`, `status`, `keterangan`, `created_at`, `updated_at`) VALUES
	(33, 23, 1, 'Diproses', 'Permohonan layanan telah didisposisikan ke bagian Tim Penyuntingan untuk diproses lebih lanjut.', '2025-06-12 07:41:22', '2025-06-12 07:41:22'),
	(34, 23, 1, 'Diproses', 'Disposisi ke: Persuratan.\n\nPermohonan layanan telah didisposisikan ke bagian Persuratan untuk diproses lebih lanjut.', '2025-06-12 07:51:54', '2025-06-12 07:51:54'),
	(35, 23, 1, 'Selesai', 'Permohonan layanan telah selesai diproses.', '2025-06-12 08:16:43', '2025-06-12 08:16:43'),
	(38, 23, 1, 'Diproses', 'Permohonan layanan telah didisposisikan ke bagian Tim Penerjemahan untuk ditindaklanjuti.', '2025-06-16 02:15:19', '2025-06-16 02:15:19'),
	(40, 25, 1, 'Diproses', 'Permohonan layanan telah didisposisikan ke bagian Persuratan untuk ditindaklanjuti.', '2025-06-24 06:40:14', '2025-06-24 06:40:14'),
	(41, 25, 1, 'Diproses', 'Permohonan layanan telah didisposisikan ke bagian Tim UKBI untuk ditindaklanjuti.', '2025-06-24 06:42:20', '2025-06-24 06:42:20'),
	(42, 25, 1, 'Ditolak', 'Dokumen kurang lengkap. tolong dilengkapi', '2025-06-24 06:54:50', '2025-06-24 06:54:50'),
	(43, 28, 1, 'Diproses', 'Permohonan layanan telah didisposisikan ke bagian Persuratan untuk ditindaklanjuti.', '2025-07-04 03:24:35', '2025-07-04 03:24:35');

-- Dumping structure for table dbultbbpj.status_pengaduans
DROP TABLE IF EXISTS `status_pengaduans`;
CREATE TABLE IF NOT EXISTS `status_pengaduans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `pengaduan_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `keterangan` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `status_pengaduans_pengaduan_id_foreign` (`pengaduan_id`),
  KEY `status_pengaduans_user_id_foreign` (`user_id`),
  CONSTRAINT `status_pengaduans_pengaduan_id_foreign` FOREIGN KEY (`pengaduan_id`) REFERENCES `pengaduans` (`id`) ON DELETE CASCADE,
  CONSTRAINT `status_pengaduans_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table dbultbbpj.status_pengaduans: ~2 rows (approximately)
INSERT INTO `status_pengaduans` (`id`, `pengaduan_id`, `user_id`, `status`, `keterangan`, `created_at`, `updated_at`) VALUES
	(1, 1, 1, 'Selesai', 'tes', '2025-07-29 07:05:41', '2025-07-29 07:05:41'),
	(2, 1, 1, 'Selesai', 'tes', '2025-07-29 07:06:29', '2025-07-29 07:06:29');

-- Dumping structure for table dbultbbpj.users
DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nip` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `profile_photo_path` varchar(2048) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'petugas',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `last_activity_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_nip_unique` (`nip`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table dbultbbpj.users: ~3 rows (approximately)
INSERT INTO `users` (`id`, `name`, `nip`, `email`, `profile_photo_path`, `role`, `email_verified_at`, `password`, `remember_token`, `last_login_at`, `last_activity_at`, `created_at`, `updated_at`) VALUES
	(1, 'bgbowo', '12345678910', 'admin@ultebbpj.com', NULL, 'admin', NULL, '$2y$12$OMH.5o/PtSlQGVpRzwwEGuipXmlGZAzfYcwXU1SjBwYjQd.ce.D9u', 'kfkm7VAhr4wX0UAE9Yzl5cfG7M7WmeJcMpyUQFk8IFftJE4Em0azThv9goIM', '2025-07-30 08:24:59', '2025-07-30 08:46:39', '2025-06-07 06:23:28', '2025-07-30 08:46:39'),
	(2, 'bowobg', NULL, 'bowo@ultebbpj.com', NULL, 'petugas', NULL, '$2y$12$mCT2Uh5ExZmz3gLuTIT2meR95FEvM3n67P7/IrR2ZpANn3WbEIhu.', NULL, '2025-06-16 13:48:00', '2025-06-16 15:01:07', '2025-06-15 21:20:39', '2025-06-16 15:01:07'),
	(3, 'Wesa ostika', '1988123456789', 'wesa@mail.com', NULL, 'petugas', NULL, '$2y$12$TCyE4diBmMpHR/9fyTn1GOpYHvpvAZUPJ8Qp9apPKIOOyLrta7Pyy', NULL, NULL, NULL, '2025-06-16 15:01:42', '2025-06-17 06:55:36');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
