-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 08, 2025 at 05:08 AM
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
-- Database: `mcc`
--

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

CREATE TABLE `accounts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `age` int(11) NOT NULL,
  `address` varchar(255) NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `borrows`
--

CREATE TABLE `borrows` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `room_item_id` bigint(20) UNSIGNED NOT NULL,
  `borrower_name` varchar(255) NOT NULL,
  `borrow_date` date NOT NULL,
  `return_date` date DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'Borrowed',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `room_title` varchar(255) NOT NULL,
  `device_category` varchar(255) NOT NULL,
  `device_type` varchar(255) DEFAULT NULL,
  `brand` varchar(255) DEFAULT NULL,
  `model` varchar(255) DEFAULT NULL,
  `serial_number` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('Usable','Unusable') NOT NULL DEFAULT 'Usable',
  `barcode` varchar(255) NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `maintenance_items`
--

CREATE TABLE `maintenance_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `item_id` bigint(20) UNSIGNED NOT NULL,
  `status` enum('Broken','Fixing','Fixed') NOT NULL DEFAULT 'Broken',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `maintenance_notes`
--

CREATE TABLE `maintenance_notes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `fullset_id` varchar(255) NOT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2025_06_20_135918_create_accounts_table', 1),
(2, '2025_06_20_140436_create_sessions_table', 1),
(3, '2025_06_21_140119_create_categories_and_room_items_tables', 1),
(4, '2025_06_21_155831_create_users_table', 2),
(5, '2025_06_24_074251_create_categories_table', 3),
(6, '2025_06_24_145540_add_status_to_room_items_table', 4),
(7, '2025_06_24_150507_create_borrows_table', 5),
(8, '2025_06_25_051658_add_is_approved_to_users_table', 6),
(10, '2025_06_25_060917_add_is_approved_to_users_table', 7),
(11, '2025_06_28_160557_add_device_type_to_room_items_table', 8),
(12, '2025_06_28_162737_add_device_type_to_room_items_table', 9),
(13, '2025_06_29_141918_create_categories_table', 10),
(14, '2025_07_01_093852_add_full_set_columns_to_room_items_table', 11),
(22, '2025_07_01_103207_add_brand_and_model_to_room_items_table', 12),
(23, '2025_07_06_091405_make_barcode_nullable_in_room_items_table', 13),
(24, '2025_07_06_091040_make_barcode_nullable_in_roomitems_table', 14),
(28, '2025_07_08_064520_add_fullset_columns_to_room_items_table', 15),
(29, '2025_07_08_064914_add_is_full_item_to_room_items_table', 16),
(30, '2025_07_08_070858_create_items_table', 16),
(31, '2025_08_25_055917_create_maintenance_notes_table', 17);

-- --------------------------------------------------------

--
-- Table structure for table `room_items`
--

CREATE TABLE `room_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `room_title` varchar(255) NOT NULL,
  `device_category` varchar(255) NOT NULL,
  `device_type` varchar(255) DEFAULT NULL,
  `brand` varchar(255) DEFAULT NULL,
  `model` varchar(255) DEFAULT NULL,
  `serial_number` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `is_full_set_item` tinyint(1) NOT NULL DEFAULT 0,
  `full_set_id` varchar(255) DEFAULT NULL,
  `barcode` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `room_items`
--

INSERT INTO `room_items` (`id`, `photo`, `room_title`, `device_category`, `device_type`, `brand`, `model`, `serial_number`, `description`, `status`, `is_full_set_item`, `full_set_id`, `barcode`, `created_at`, `updated_at`, `notes`) VALUES
(616, 'public/photos/6zR4xvqXCe7OkQZz5IhzX9MucJ9wtY8fAlPYboEn.jpg', 'ComLab 5', 'System Unit', 'Computer Units', 'DELL', 'XRM12', 'IDVB4UW', 'lerss', 'Usable', 0, 'FS-RZJYTM6U', 'CL5-PC001-SU-001', '2025-08-24 23:01:04', '2025-08-24 23:01:04', NULL),
(617, 'public/photos/6zR4xvqXCe7OkQZz5IhzX9MucJ9wtY8fAlPYboEn.jpg', 'ComLab 5', 'Monitor', 'Peripherals', 'DELL', 'XRM12', 'SD60KL4', 'lerss', 'Usable', 0, 'FS-RZJYTM6U', 'CL5-PC001-M-001', '2025-08-24 23:01:04', '2025-08-24 23:01:04', NULL),
(618, 'public/photos/6zR4xvqXCe7OkQZz5IhzX9MucJ9wtY8fAlPYboEn.jpg', 'ComLab 5', 'Keyboard', 'Peripherals', 'DELL', 'XRM12', '1RHPDQU', 'lerss', 'Usable', 0, 'FS-RZJYTM6U', 'CL5-PC001-K-001', '2025-08-24 23:01:04', '2025-08-24 23:01:04', NULL),
(619, 'public/photos/6zR4xvqXCe7OkQZz5IhzX9MucJ9wtY8fAlPYboEn.jpg', 'ComLab 5', 'Mouse', 'Peripherals', 'DELL', 'XRM12', '9RDX7IY', 'lerss', 'Usable', 0, 'FS-RZJYTM6U', 'CL5-PC001-MS-001', '2025-08-24 23:01:04', '2025-08-24 23:01:04', NULL),
(621, 'public/photos/6zR4xvqXCe7OkQZz5IhzX9MucJ9wtY8fAlPYboEn.jpg', 'ComLab 5', 'SSD', 'Computer Units', 'DELL', 'XRM12', 'PKBDM61', 'lerss', 'Usable', 0, 'FS-RZJYTM6U', 'CL5-PC001-SSD-001', '2025-08-24 23:01:04', '2025-08-24 23:01:04', NULL),
(622, 'public/photos/6zR4xvqXCe7OkQZz5IhzX9MucJ9wtY8fAlPYboEn.jpg', 'ComLab 5', 'Motherboard', 'Computer Units', 'DELL', 'XRM12', '69LDOCO', 'lerss', 'Usable', 0, 'FS-RZJYTM6U', 'CL5-PC001-MB-001', '2025-08-24 23:01:04', '2025-08-24 23:01:04', NULL),
(623, 'public/photos/6zR4xvqXCe7OkQZz5IhzX9MucJ9wtY8fAlPYboEn.jpg', 'ComLab 5', 'Graphic Card', 'Computer Units', 'DELL', 'XRM12', 'F8Q58BL', 'lerss', 'Usable', 0, 'FS-RZJYTM6U', 'CL5-PC001-GPU-001', '2025-08-24 23:01:04', '2025-08-24 23:01:04', NULL),
(624, 'public/photos/6zR4xvqXCe7OkQZz5IhzX9MucJ9wtY8fAlPYboEn.jpg', 'ComLab 5', 'RAM', 'Computer Units', 'DELL', 'XRM12', '5SE761N', 'lerss', 'Usable', 0, 'FS-RZJYTM6U', 'CL5-PC001-RAM-001', '2025-08-24 23:01:04', '2025-08-24 23:01:04', NULL),
(625, 'public/photos/UeCLjv8NcMMOYcUYIQ5xCs7fgFLterf1mY5kgkgY.jpg', 'ComLab 5', 'System Unit', 'Computer Units', 'DELL', 'XRM12', 'AA9GPH4', 'lerss', 'Usable', 0, 'FS-DBOHYMOF', 'CL5-PC002-SU-001', '2025-08-24 23:02:16', '2025-08-24 23:02:16', NULL),
(626, 'public/photos/UeCLjv8NcMMOYcUYIQ5xCs7fgFLterf1mY5kgkgY.jpg', 'ComLab 5', 'Monitor', 'Peripherals', 'DELL', 'XRM12', 'PWBB10L', 'lerss', 'Usable', 0, 'FS-DBOHYMOF', 'CL5-PC002-M-001', '2025-08-24 23:02:16', '2025-08-24 23:02:16', NULL),
(627, 'public/photos/UeCLjv8NcMMOYcUYIQ5xCs7fgFLterf1mY5kgkgY.jpg', 'ComLab 5', 'Keyboard', 'Peripherals', 'DELL', 'XRM12', 'LNC1FOD', 'lerss', 'Usable', 0, 'FS-DBOHYMOF', 'CL5-PC002-K-001', '2025-08-24 23:02:16', '2025-08-24 23:02:16', NULL),
(628, 'public/photos/UeCLjv8NcMMOYcUYIQ5xCs7fgFLterf1mY5kgkgY.jpg', 'ComLab 5', 'Mouse', 'Peripherals', 'DELL', 'XRM12', '81MBAR3', 'lerss', 'Usable', 0, 'FS-DBOHYMOF', 'CL5-PC002-MS-001', '2025-08-24 23:02:16', '2025-08-24 23:02:16', NULL),
(630, 'public/photos/UeCLjv8NcMMOYcUYIQ5xCs7fgFLterf1mY5kgkgY.jpg', 'ComLab 5', 'SSD', 'Computer Units', 'DELL', 'XRM12', '34V2UBC', 'lerss', 'Usable', 0, 'FS-DBOHYMOF', 'CL5-PC002-SSD-001', '2025-08-24 23:02:16', '2025-08-24 23:02:16', NULL),
(631, 'public/photos/UeCLjv8NcMMOYcUYIQ5xCs7fgFLterf1mY5kgkgY.jpg', 'ComLab 5', 'Motherboard', 'Computer Units', 'DELL', 'XRM12', 'LUH3BC7', 'lerss', 'Usable', 0, 'FS-DBOHYMOF', 'CL5-PC002-MB-001', '2025-08-24 23:02:16', '2025-08-24 23:02:16', NULL),
(632, 'public/photos/UeCLjv8NcMMOYcUYIQ5xCs7fgFLterf1mY5kgkgY.jpg', 'ComLab 5', 'Graphic Card', 'Computer Units', 'DELL', 'XRM12', 'PDQPG97', 'lerss', 'Usable', 0, 'FS-DBOHYMOF', 'CL5-PC002-GPU-001', '2025-08-24 23:02:16', '2025-08-24 23:02:16', NULL),
(633, 'public/photos/UeCLjv8NcMMOYcUYIQ5xCs7fgFLterf1mY5kgkgY.jpg', 'ComLab 5', 'RAM', 'Computer Units', 'DELL', 'XRM12', '7TJ4N2V', 'lerss', 'Usable', 0, 'FS-DBOHYMOF', 'CL5-PC002-RAM-001', '2025-08-24 23:02:16', '2025-08-24 23:02:16', NULL),
(634, 'public/photos/PnI4S0rYM3yyflQ4o6EyhebYs74o2sKMLmTSM4xx.jpg', 'ComLab 5', 'System Unit', 'Computer Units', 'DELL', 'XRM12', 'EE3TYXK', 'lerss', 'Usable', 0, 'FS-MXW8TZZK', 'CL5-PC003-SU-001', '2025-08-24 23:03:56', '2025-08-24 23:03:56', NULL),
(635, 'public/photos/PnI4S0rYM3yyflQ4o6EyhebYs74o2sKMLmTSM4xx.jpg', 'ComLab 5', 'Monitor', 'Peripherals', 'DELL', 'XRM12', 'KVLEQG9', 'lerss', 'Usable', 0, 'FS-MXW8TZZK', 'CL5-PC003-M-001', '2025-08-24 23:03:56', '2025-08-24 23:03:56', NULL),
(636, 'public/photos/PnI4S0rYM3yyflQ4o6EyhebYs74o2sKMLmTSM4xx.jpg', 'ComLab 5', 'Keyboard', 'Peripherals', 'DELL', 'XRM12', 'SOTIBEI', 'lerss', 'Usable', 0, 'FS-MXW8TZZK', 'CL5-PC003-K-001', '2025-08-24 23:03:56', '2025-08-24 23:03:56', NULL),
(637, 'public/photos/PnI4S0rYM3yyflQ4o6EyhebYs74o2sKMLmTSM4xx.jpg', 'ComLab 5', 'Mouse', 'Peripherals', 'DELL', 'XRM12', '1PM2EEM', 'lerss', 'Usable', 0, 'FS-MXW8TZZK', 'CL5-PC003-MS-001', '2025-08-24 23:03:56', '2025-08-24 23:03:56', NULL),
(639, 'public/photos/PnI4S0rYM3yyflQ4o6EyhebYs74o2sKMLmTSM4xx.jpg', 'ComLab 5', 'SSD', 'Computer Units', 'DELL', 'XRM12', 'O3WR6SJ', 'lerss', 'Usable', 0, 'FS-MXW8TZZK', 'CL5-PC003-SSD-001', '2025-08-24 23:03:56', '2025-08-24 23:03:56', NULL),
(640, 'public/photos/PnI4S0rYM3yyflQ4o6EyhebYs74o2sKMLmTSM4xx.jpg', 'ComLab 5', 'Motherboard', 'Computer Units', 'DELL', 'XRM12', 'S30FFFI', 'lerss', 'Usable', 0, 'FS-MXW8TZZK', 'CL5-PC003-MB-001', '2025-08-24 23:03:56', '2025-08-24 23:03:56', NULL),
(641, 'public/photos/PnI4S0rYM3yyflQ4o6EyhebYs74o2sKMLmTSM4xx.jpg', 'ComLab 5', 'Graphic Card', 'Computer Units', 'DELL', 'XRM12', 'S0KAA5F', 'lerss', 'Usable', 0, 'FS-MXW8TZZK', 'CL5-PC003-GPU-001', '2025-08-24 23:03:56', '2025-08-24 23:03:56', NULL),
(642, 'public/photos/PnI4S0rYM3yyflQ4o6EyhebYs74o2sKMLmTSM4xx.jpg', 'ComLab 5', 'RAM', 'Computer Units', 'DELL', 'XRM12', '4Q5OKDM', 'lerss', 'Usable', 0, 'FS-MXW8TZZK', 'CL5-PC003-RAM-001', '2025-08-24 23:03:56', '2025-08-24 23:03:56', NULL),
(643, 'public/photos/a6e0QX5KiVNU1VPvUWAKuAdusEQQAD5ZlM1pT2nH.jpg', 'ComLab 5', 'System Unit', 'Computer Units', 'DELL', 'XRM12', 'BPNPGQJ', 'lerss', 'Usable', 0, 'FS-NNXKYRCX', 'CL5-PC004-SU-001', '2025-08-24 23:05:07', '2025-08-24 23:05:07', NULL),
(644, 'public/photos/a6e0QX5KiVNU1VPvUWAKuAdusEQQAD5ZlM1pT2nH.jpg', 'ComLab 5', 'Monitor', 'Peripherals', 'DELL', 'XRM12', 'VGUTWCC', 'lerss', 'Usable', 0, 'FS-NNXKYRCX', 'CL5-PC004-M-001', '2025-08-24 23:05:07', '2025-08-24 23:05:07', NULL),
(645, 'public/photos/a6e0QX5KiVNU1VPvUWAKuAdusEQQAD5ZlM1pT2nH.jpg', 'ComLab 5', 'Keyboard', 'Peripherals', 'DELL', 'XRM12', '7PJ6UYG', 'lerss', 'Usable', 0, 'FS-NNXKYRCX', 'CL5-PC004-K-001', '2025-08-24 23:05:07', '2025-08-24 23:05:07', NULL),
(646, 'public/photos/a6e0QX5KiVNU1VPvUWAKuAdusEQQAD5ZlM1pT2nH.jpg', 'ComLab 5', 'Mouse', 'Peripherals', 'DELL', 'XRM12', 'MY0DGYG', 'lerss', 'Usable', 0, 'FS-NNXKYRCX', 'CL5-PC004-MS-001', '2025-08-24 23:05:07', '2025-08-24 23:05:07', NULL),
(648, 'public/photos/a6e0QX5KiVNU1VPvUWAKuAdusEQQAD5ZlM1pT2nH.jpg', 'ComLab 5', 'SSD', 'Computer Units', 'DELL', 'XRM12', 'SCW9SSZ', 'lerss', 'Usable', 0, 'FS-NNXKYRCX', 'CL5-PC004-SSD-001', '2025-08-24 23:05:07', '2025-08-24 23:05:07', NULL),
(649, 'public/photos/a6e0QX5KiVNU1VPvUWAKuAdusEQQAD5ZlM1pT2nH.jpg', 'ComLab 5', 'Motherboard', 'Computer Units', 'DELL', 'XRM12', 'BW19BSH', 'lerss', 'Usable', 0, 'FS-NNXKYRCX', 'CL5-PC004-MB-001', '2025-08-24 23:05:07', '2025-08-24 23:05:07', NULL),
(650, 'public/photos/a6e0QX5KiVNU1VPvUWAKuAdusEQQAD5ZlM1pT2nH.jpg', 'ComLab 5', 'Graphic Card', 'Computer Units', 'DELL', 'XRM12', '3V1OEGX', 'lerss', 'Usable', 0, 'FS-NNXKYRCX', 'CL5-PC004-GPU-001', '2025-08-24 23:05:07', '2025-08-24 23:05:07', NULL),
(651, 'public/photos/a6e0QX5KiVNU1VPvUWAKuAdusEQQAD5ZlM1pT2nH.jpg', 'ComLab 5', 'RAM', 'Computer Units', 'DELL', 'XRM12', '5IPHL6A', 'lerss', 'Usable', 0, 'FS-NNXKYRCX', 'CL5-PC004-RAM-001', '2025-08-24 23:05:07', '2025-08-24 23:05:07', NULL),
(652, 'public/photos/e53cwWYiiKSXF1pxN7VSnGEfuoe0fjKYPdrz8pUm.jpg', 'ComLab 5', 'System Unit', 'Computer Units', 'DELL', 'XRM12', 'P1ZI0U8', 'lerss', 'Usable', 0, 'FS-MO0SLAJS', 'CL5-PC005-SU-001', '2025-08-24 23:05:42', '2025-08-24 23:08:55', NULL),
(653, 'public/photos/3j2yuotoYwI5zfQ41vnZnGsaSEdMk0o7IGWzT7nX.jpg', 'ComLab 5', 'Monitor', 'Peripherals', 'DELL', 'XRM12', '8INS5CR', 'lerss', 'Usable', 0, 'FS-MO0SLAJS', 'CL5-PC005-M-001', '2025-08-24 23:05:42', '2025-08-24 23:05:42', NULL),
(654, 'public/photos/3j2yuotoYwI5zfQ41vnZnGsaSEdMk0o7IGWzT7nX.jpg', 'ComLab 5', 'Keyboard', 'Peripherals', 'DELL', 'XRM12', 'YK8SGUQ', 'lerss', 'Usable', 0, 'FS-MO0SLAJS', 'CL5-PC005-K-001', '2025-08-24 23:05:42', '2025-08-24 23:05:42', NULL),
(655, 'public/photos/3j2yuotoYwI5zfQ41vnZnGsaSEdMk0o7IGWzT7nX.jpg', 'ComLab 5', 'Mouse', 'Peripherals', 'DELL', 'XRM12', '29LKRD3', 'lerss', 'Usable', 0, 'FS-MO0SLAJS', 'CL5-PC005-MS-001', '2025-08-24 23:05:42', '2025-08-24 23:05:42', NULL),
(657, 'public/photos/3j2yuotoYwI5zfQ41vnZnGsaSEdMk0o7IGWzT7nX.jpg', 'ComLab 5', 'SSD', 'Computer Units', 'DELL', 'XRM12', 'Z0GVS1I', 'lerss', 'Usable', 0, 'FS-MO0SLAJS', 'CL5-PC005-SSD-001', '2025-08-24 23:05:42', '2025-08-24 23:05:42', NULL),
(658, 'public/photos/3j2yuotoYwI5zfQ41vnZnGsaSEdMk0o7IGWzT7nX.jpg', 'ComLab 5', 'Motherboard', 'Computer Units', 'DELL', 'XRM12', '719K374', 'lerss', 'Usable', 0, 'FS-MO0SLAJS', 'CL5-PC005-MB-001', '2025-08-24 23:05:42', '2025-08-24 23:05:42', NULL),
(659, 'public/photos/3j2yuotoYwI5zfQ41vnZnGsaSEdMk0o7IGWzT7nX.jpg', 'ComLab 5', 'Graphic Card', 'Computer Units', 'DELL', 'XRM12', 'JL1ZRWK', 'lerss', 'Usable', 0, 'FS-MO0SLAJS', 'CL5-PC005-GPU-001', '2025-08-24 23:05:42', '2025-08-24 23:05:42', NULL),
(660, 'public/photos/3j2yuotoYwI5zfQ41vnZnGsaSEdMk0o7IGWzT7nX.jpg', 'ComLab 5', 'RAM', 'Computer Units', 'DELL', 'XRM12', 'EGN8KXY', 'lerss', 'Usable', 0, 'FS-MO0SLAJS', 'CL5-PC005-RAM-001', '2025-08-24 23:05:42', '2025-08-24 23:05:42', NULL),
(661, NULL, 'ComLab 5', 'System Unit', 'Computer Units', 'DELL', 'XRM12', '0MOHQI7', 'lerss', 'Usable', 0, 'FS-JCSF10QK', 'CL5-PC006-SU-001', '2025-08-24 23:12:17', '2025-08-24 23:12:17', NULL),
(662, NULL, 'ComLab 5', 'Monitor', 'Peripherals', 'DELL', 'XRM12', '7GX18XU', 'lerss', 'Usable', 0, 'FS-JCSF10QK', 'CL5-PC006-M-001', '2025-08-24 23:12:17', '2025-08-24 23:12:17', NULL),
(663, NULL, 'ComLab 5', 'Keyboard', 'Peripherals', 'DELL', 'XRM12', 'BITYG34', 'lerss', 'Usable', 0, 'FS-JCSF10QK', 'CL5-PC006-K-001', '2025-08-24 23:12:17', '2025-08-24 23:12:17', NULL),
(664, NULL, 'ComLab 5', 'Mouse', 'Peripherals', 'DELL', 'XRM12', 'TAS8JG5', 'lerss', 'Usable', 0, 'FS-JCSF10QK', 'CL5-PC006-MS-001', '2025-08-24 23:12:17', '2025-08-24 23:12:17', NULL),
(665, NULL, 'ComLab 5', 'Power Supply Unit', 'Computer Units', 'DELL', 'XRM12', '9CMP97M', 'lerss', 'Usable', 0, 'FS-JCSF10QK', 'CL5-PC006-PSU-001', '2025-08-24 23:12:17', '2025-08-24 23:12:17', NULL),
(666, NULL, 'ComLab 5', 'SSD', 'Computer Units', 'DELL', 'XRM12', 'QLBHI7P', 'lerss', 'Usable', 0, 'FS-JCSF10QK', 'CL5-PC006-SSD-001', '2025-08-24 23:12:17', '2025-08-24 23:12:17', NULL),
(667, NULL, 'ComLab 5', 'Motherboard', 'Computer Units', 'DELL', 'XRM12', 'WHNI6AM', 'lerss', 'Usable', 0, 'FS-JCSF10QK', 'CL5-PC006-MB-001', '2025-08-24 23:12:17', '2025-08-24 23:12:17', NULL),
(668, NULL, 'ComLab 5', 'Graphic Card', 'Computer Units', 'DELL', 'XRM12', 'FF4A4DX', 'lerss', 'Usable', 0, 'FS-JCSF10QK', 'CL5-PC006-GPU-001', '2025-08-24 23:12:17', '2025-08-24 23:12:17', NULL),
(669, NULL, 'ComLab 5', 'RAM', 'Computer Units', 'DELL', 'XRM12', 'WQVUHUP', 'lerss', 'Usable', 0, 'FS-JCSF10QK', 'CL5-PC006-RAM-001', '2025-08-24 23:12:17', '2025-08-24 23:12:17', NULL),
(670, NULL, 'ComLab 5', 'System Unit', 'Computer Units', 'DELL', 'XRM12', 'S04FV8W', 'lerss', 'Usable', 0, 'FS-2J5JCKPN', 'CL5-PC007-SU-001', '2025-08-24 23:12:43', '2025-08-24 23:12:43', NULL),
(671, NULL, 'ComLab 5', 'Monitor', 'Peripherals', 'DELL', 'XRM12', '2OG4U73', 'lerss', 'Usable', 0, 'FS-2J5JCKPN', 'CL5-PC007-M-001', '2025-08-24 23:12:43', '2025-08-24 23:12:43', NULL),
(672, NULL, 'ComLab 5', 'Keyboard', 'Peripherals', 'DELL', 'XRM12', 'SD1QTZL', 'lerss', 'Usable', 0, 'FS-2J5JCKPN', 'CL5-PC007-K-001', '2025-08-24 23:12:43', '2025-08-24 23:12:43', NULL),
(673, NULL, 'ComLab 5', 'Mouse', 'Peripherals', 'DELL', 'XRM12', 'GR07H1Z', 'lerss', 'Usable', 0, 'FS-2J5JCKPN', 'CL5-PC007-MS-001', '2025-08-24 23:12:43', '2025-08-24 23:12:43', NULL),
(674, NULL, 'ComLab 5', 'Power Supply Unit', 'Computer Units', 'DELL', 'XRM12', 'GT0DNEY', 'lerss', 'Usable', 0, 'FS-2J5JCKPN', 'CL5-PC007-PSU-001', '2025-08-24 23:12:43', '2025-08-24 23:12:43', NULL),
(675, NULL, 'ComLab 5', 'SSD', 'Computer Units', 'DELL', 'XRM12', 'GZR4SGO', 'lerss', 'Usable', 0, 'FS-2J5JCKPN', 'CL5-PC007-SSD-001', '2025-08-24 23:12:43', '2025-08-24 23:12:43', NULL),
(676, NULL, 'ComLab 5', 'Motherboard', 'Computer Units', 'DELL', 'XRM12', 'EWKAZCQ', 'lerss', 'Usable', 0, 'FS-2J5JCKPN', 'CL5-PC007-MB-001', '2025-08-24 23:12:43', '2025-08-24 23:12:43', NULL),
(677, NULL, 'ComLab 5', 'Graphic Card', 'Computer Units', 'DELL', 'XRM12', 'AJJ9NK7', 'lerss', 'Usable', 0, 'FS-2J5JCKPN', 'CL5-PC007-GPU-001', '2025-08-24 23:12:43', '2025-08-24 23:12:43', NULL),
(678, NULL, 'ComLab 5', 'RAM', 'Computer Units', 'DELL', 'XRM12', '8GV3U8T', 'lerss', 'Usable', 0, 'FS-2J5JCKPN', 'CL5-PC007-RAM-001', '2025-08-24 23:12:43', '2025-08-24 23:12:43', NULL),
(679, NULL, 'ComLab 5', 'System Unit', 'Computer Units', 'DELL', 'XRM12', '3CQ1NQ8', 'lerss', 'Usable', 0, 'FS-EHIQFK7N', 'CL5-PC008-SU-001', '2025-08-24 23:13:11', '2025-08-24 23:13:11', NULL),
(680, NULL, 'ComLab 5', 'Monitor', 'Peripherals', 'DELL', 'XRM12', 'JPHQXY8', 'lerss', 'Usable', 0, 'FS-EHIQFK7N', 'CL5-PC008-M-001', '2025-08-24 23:13:11', '2025-08-24 23:13:11', NULL),
(681, NULL, 'ComLab 5', 'Keyboard', 'Peripherals', 'DELL', 'XRM12', 'HP6MJWP', 'lerss', 'Usable', 0, 'FS-EHIQFK7N', 'CL5-PC008-K-001', '2025-08-24 23:13:11', '2025-08-24 23:13:11', NULL),
(682, NULL, 'ComLab 5', 'Mouse', 'Peripherals', 'DELL', 'XRM12', '0WRYXQN', 'lerss', 'Usable', 0, 'FS-EHIQFK7N', 'CL5-PC008-MS-001', '2025-08-24 23:13:11', '2025-08-24 23:13:11', NULL),
(683, NULL, 'ComLab 5', 'Power Supply Unit', 'Computer Units', 'DELL', 'XRM12', 'MFNMXH2', 'lerss', 'Usable', 0, 'FS-EHIQFK7N', 'CL5-PC008-PSU-001', '2025-08-24 23:13:11', '2025-08-24 23:13:11', NULL),
(684, NULL, 'ComLab 5', 'SSD', 'Computer Units', 'DELL', 'XRM12', 'J4C7EGU', 'lerss', 'Usable', 0, 'FS-EHIQFK7N', 'CL5-PC008-SSD-001', '2025-08-24 23:13:11', '2025-08-24 23:13:11', NULL),
(685, NULL, 'ComLab 5', 'Motherboard', 'Computer Units', 'DELL', 'XRM12', 'QLP20IJ', 'lerss', 'Usable', 0, 'FS-EHIQFK7N', 'CL5-PC008-MB-001', '2025-08-24 23:13:11', '2025-08-24 23:13:11', NULL),
(686, NULL, 'ComLab 5', 'Graphic Card', 'Computer Units', 'DELL', 'XRM12', 'E1I3LEN', 'lerss', 'Usable', 0, 'FS-EHIQFK7N', 'CL5-PC008-GPU-001', '2025-08-24 23:13:11', '2025-08-24 23:13:11', NULL),
(687, NULL, 'ComLab 5', 'RAM', 'Computer Units', 'DELL', 'XRM12', 'CCFWHJC', 'lerss', 'Usable', 0, 'FS-EHIQFK7N', 'CL5-PC008-RAM-001', '2025-08-24 23:13:11', '2025-08-24 23:13:11', NULL),
(688, NULL, 'ComLab 5', 'System Unit', 'Computer Units', 'DELL', 'XRM12', 'VP69MEB', 'lerss', 'Usable', 0, 'FS-7IYULSAY', 'CL5-PC009-SU-001', '2025-08-24 23:14:42', '2025-08-24 23:14:42', NULL),
(689, NULL, 'ComLab 5', 'Monitor', 'Peripherals', 'DELL', 'XRM12', 'JVCP7W5', 'lerss', 'Usable', 0, 'FS-7IYULSAY', 'CL5-PC009-M-001', '2025-08-24 23:14:42', '2025-08-24 23:14:42', NULL),
(690, NULL, 'ComLab 5', 'Keyboard', 'Peripherals', 'DELL', 'XRM12', 'G23B04K', 'lerss', 'Usable', 0, 'FS-7IYULSAY', 'CL5-PC009-K-001', '2025-08-24 23:14:42', '2025-08-24 23:14:42', NULL),
(691, NULL, 'ComLab 5', 'Mouse', 'Peripherals', 'DELL', 'XRM12', '1Q8WSES', 'lerss', 'Usable', 0, 'FS-7IYULSAY', 'CL5-PC009-MS-001', '2025-08-24 23:14:42', '2025-08-24 23:14:42', NULL),
(692, NULL, 'ComLab 5', 'Power Supply Unit', 'Computer Units', 'DELL', 'XRM12', '5VT2WRM', 'lerss', 'Usable', 0, 'FS-7IYULSAY', 'CL5-PC009-PSU-001', '2025-08-24 23:14:42', '2025-08-24 23:14:42', NULL),
(693, NULL, 'ComLab 5', 'SSD', 'Computer Units', 'DELL', 'XRM12', 'QQT4GGE', 'lerss', 'Usable', 0, 'FS-7IYULSAY', 'CL5-PC009-SSD-001', '2025-08-24 23:14:42', '2025-08-24 23:14:42', NULL),
(694, NULL, 'ComLab 5', 'Motherboard', 'Computer Units', 'DELL', 'XRM12', '7HEVKNG', 'lerss', 'Usable', 0, 'FS-7IYULSAY', 'CL5-PC009-MB-001', '2025-08-24 23:14:42', '2025-08-24 23:14:42', NULL),
(695, NULL, 'ComLab 5', 'Graphic Card', 'Computer Units', 'DELL', 'XRM12', '3EHMCUE', 'lerss', 'Usable', 0, 'FS-7IYULSAY', 'CL5-PC009-GPU-001', '2025-08-24 23:14:42', '2025-08-24 23:14:42', NULL),
(696, NULL, 'ComLab 5', 'RAM', 'Computer Units', 'DELL', 'XRM12', 'Q12X5J5', 'lerss', 'Usable', 0, 'FS-7IYULSAY', 'CL5-PC009-RAM-001', '2025-08-24 23:14:42', '2025-08-24 23:14:42', NULL),
(698, 'public/photos/ipkviYKKp5YA6Bc4YryfJ70lsTTtHIRntuqCjNYq.jpg', 'Server', 'Monitor', 'Peripherals', 'DELL', 'XRM12', '0AR86C5', 'ls', 'Usable', 0, 'FS-Y3KCNDYR', 'SRV-PC001-M-001', '2025-08-25 01:50:29', '2025-08-25 01:50:29', NULL),
(699, 'public/photos/ipkviYKKp5YA6Bc4YryfJ70lsTTtHIRntuqCjNYq.jpg', 'Server', 'Keyboard', 'Peripherals', 'DELL', 'XRM12', 'MGIR511', 'ls', 'Usable', 0, 'FS-Y3KCNDYR', 'SRV-PC001-K-001', '2025-08-25 01:50:29', '2025-08-25 01:50:29', NULL),
(700, 'public/photos/ipkviYKKp5YA6Bc4YryfJ70lsTTtHIRntuqCjNYq.jpg', 'Server', 'Mouse', 'Peripherals', 'DELL', 'XRM12', 'WCERA12', 'ls', 'Usable', 0, 'FS-Y3KCNDYR', 'SRV-PC001-MS-001', '2025-08-25 01:50:29', '2025-08-25 01:50:29', NULL),
(701, 'public/photos/ipkviYKKp5YA6Bc4YryfJ70lsTTtHIRntuqCjNYq.jpg', 'Server', 'Power Supply Unit', 'Computer Units', 'DELL', 'XRM12', '2P3K3NU', 'ls', 'Usable', 0, 'FS-Y3KCNDYR', 'SRV-PC001-PSU-001', '2025-08-25 01:50:29', '2025-08-25 01:50:29', NULL),
(702, 'public/photos/ipkviYKKp5YA6Bc4YryfJ70lsTTtHIRntuqCjNYq.jpg', 'Server', 'SSD', 'Computer Units', 'DELL', 'XRM12', '73SBWIA', 'ls', 'Usable', 0, 'FS-Y3KCNDYR', 'SRV-PC001-SSD-001', '2025-08-25 01:50:29', '2025-08-25 01:50:29', NULL),
(703, 'public/photos/ipkviYKKp5YA6Bc4YryfJ70lsTTtHIRntuqCjNYq.jpg', 'Server', 'Motherboard', 'Computer Units', 'DELL', 'XRM12', 'GIJB0WA', 'ls', 'Usable', 0, 'FS-Y3KCNDYR', 'SRV-PC001-MB-001', '2025-08-25 01:50:29', '2025-08-25 01:50:29', NULL),
(704, 'public/photos/ipkviYKKp5YA6Bc4YryfJ70lsTTtHIRntuqCjNYq.jpg', 'Server', 'Graphic Card', 'Computer Units', 'DELL', 'XRM12', '7ZF20FD', 'ls', 'Usable', 0, 'FS-Y3KCNDYR', 'SRV-PC001-GPU-001', '2025-08-25 01:50:29', '2025-08-25 01:50:29', NULL),
(705, 'public/photos/ipkviYKKp5YA6Bc4YryfJ70lsTTtHIRntuqCjNYq.jpg', 'Server', 'RAM', 'Computer Units', 'DELL', 'XRM12', '41EYXRU', 'ls', 'Usable', 0, 'FS-Y3KCNDYR', 'SRV-PC001-RAM-001', '2025-08-25 01:50:29', '2025-08-25 01:50:29', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('9k2Tqs9QNyEopsoCWjBbUuthOynSb9Vd6aaKq14k', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36 Edg/139.0.0.0', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiN0JqbHRMV3hydTRsYnJNMW84Rm9ETzRQRUpzUUZvTnNVMno1dVpoWSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzQ6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9zY2FuLWJhcmNvZGUiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=', 1756810170),
('dAmnX9A6vnPOqzCpfnkiBgbqZHmeS5w9ucY3aEx0', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36 Edg/139.0.0.0', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiMzB1RDFCaWpTdkRibVdSdG9Gc2MwRW53em5QcmtiVnBiNHJ0R0xCeSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDI6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9yb29tLWl0ZW1zLzYyNC9waG90byI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7fQ==', 1756482561);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_approved` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `photo`, `created_at`, `updated_at`, `is_approved`) VALUES
(1, 'sdfasdf', 'a@gmail.com', '$2y$12$WLDaPgXc39rNjqTFx4qVMO8BBHe3qV/A6B7dVsmbLLcBQCWtpe.nu', '1750522123.jpg', '2025-06-21 08:08:44', '2025-06-24 22:40:08', 1),
(4, 'Sheshan Baho Bilat', 'bahobilat@gmail.com', '$2y$12$c4oqc/KHzYDlqjtHaJCHmOD8A5ov1O44eM2by4TnBuqhyNbt/dUey', '1750830418.jpg', '2025-06-24 21:46:58', '2025-06-24 22:39:52', 1),
(5, 'Mango Graham Ice Cream', 'k@gmail.com', '$2y$12$MgstnHiVgqywue6QMRySye8u1V7VGvvRtzukrFEP9moJ1J/aswmSC', '1751267759.jpg', '2025-06-29 23:16:00', '2025-07-14 20:53:09', 1),
(6, 'annial aja ss', 'dfasdf@sdfasd', '$2y$12$m7YVwbD93mC.UjTQvUeeaO8zCkElwA2Now7jDfXz3NKqY2a2Q2QF.', '1752556081.jpg', '2025-07-14 21:08:02', '2025-07-14 21:08:02', 0),
(7, 'KHAMYR B. ARAÑO', 'director@madridejos.edu.ph', '$2y$12$9pSsodggcOy4/rwS.tVRr.HtqohFTvABTT7Z417ZniHQw4GoEG0Z.', 'director_default.jpg', '2025-08-25 04:38:23', '2025-08-25 04:38:23', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `accounts_email_unique` (`email`);

--
-- Indexes for table `borrows`
--
ALTER TABLE `borrows`
  ADD PRIMARY KEY (`id`),
  ADD KEY `borrows_room_item_id_foreign` (`room_item_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `items_barcode_unique` (`barcode`),
  ADD KEY `items_room_title_device_category_index` (`room_title`,`device_category`),
  ADD KEY `items_room_title_device_category_serial_number_index` (`room_title`,`device_category`,`serial_number`),
  ADD KEY `items_barcode_index` (`barcode`),
  ADD KEY `items_status_index` (`status`);

--
-- Indexes for table `maintenance_items`
--
ALTER TABLE `maintenance_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `maintenance_notes`
--
ALTER TABLE `maintenance_notes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `maintenance_notes_fullset_id_unique` (`fullset_id`),
  ADD KEY `maintenance_notes_fullset_id_index` (`fullset_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `room_items`
--
ALTER TABLE `room_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `room_items_barcode_unique` (`barcode`),
  ADD KEY `room_items_full_set_id_index` (`full_set_id`),
  ADD KEY `room_items_is_full_set_item_full_set_id_index` (`is_full_set_item`,`full_set_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accounts`
--
ALTER TABLE `accounts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `borrows`
--
ALTER TABLE `borrows`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `maintenance_items`
--
ALTER TABLE `maintenance_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `maintenance_notes`
--
ALTER TABLE `maintenance_notes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `room_items`
--
ALTER TABLE `room_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=706;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `borrows`
--
ALTER TABLE `borrows`
  ADD CONSTRAINT `borrows_room_item_id_foreign` FOREIGN KEY (`room_item_id`) REFERENCES `room_items` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
