-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: May 27, 2026 at 08:00 PM
-- Server version: 11.8.6-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u504065335_to_quran`
--

-- --------------------------------------------------------

--
-- Table structure for table `audio_lessons`
--

CREATE TABLE `audio_lessons` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `file` varchar(255) DEFAULT NULL,
  `order` int(11) DEFAULT NULL,
  `unit_id` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audio_lessons`
--

INSERT INTO `audio_lessons` (`id`, `title`, `type`, `file`, `order`, `unit_id`, `active`, `created_at`, `updated_at`) VALUES
(1, 'R 0.1', 'audio', 'public/course/Elementary-L1/unit1/R 0.1.mp3', 1, 1, 1, '2023-01-15 13:48:42', '2023-01-15 13:48:42'),
(2, 'R 0.10', 'audio', 'public/course/Elementary-L1/unit1/R 0.10.mp3', 10, 1, 1, '2023-01-15 13:48:42', '2023-01-15 13:48:42'),
(3, 'R 0.11', 'audio', 'public/course/Elementary-L1/unit1/R 0.11.mp3', 11, 1, 1, '2023-01-15 13:48:42', '2023-01-15 13:48:42'),
(4, 'R 0.2', 'audio', 'public/course/Elementary-L1/unit1/R 0.2.mp3', 2, 1, 1, '2023-01-15 13:48:42', '2023-01-15 13:48:42'),
(5, 'R 0.3', 'audio', 'public/course/Elementary-L1/unit1/R 0.3.mp3', 3, 1, 1, '2023-01-15 13:48:42', '2023-01-15 13:48:42'),
(6, 'R 0.4', 'audio', 'public/course/Elementary-L1/unit1/R 0.4.mp3', 4, 1, 1, '2023-01-15 13:48:42', '2023-01-15 13:48:42'),
(7, 'R 0.5', 'audio', 'public/course/Elementary-L1/unit1/R 0.5.mp3', 5, 1, 1, '2023-01-15 13:48:42', '2023-01-15 13:48:42'),
(8, 'R 0.6', 'audio', 'public/course/Elementary-L1/unit1/R 0.6.mp3', 6, 1, 1, '2023-01-15 13:48:42', '2023-01-15 13:48:42'),
(9, 'R 0.7', 'audio', 'public/course/Elementary-L1/unit1/R 0.7.mp3', 7, 1, 1, '2023-01-15 13:48:42', '2023-01-15 13:48:42'),
(10, 'R 0.8', 'audio', 'public/course/Elementary-L1/unit1/R 0.8.mp3', 8, 1, 1, '2023-01-15 13:48:42', '2023-01-15 13:48:42'),
(11, 'R 0.9', 'audio', 'public/course/Elementary-L1/unit1/R 0.9.mp3', 9, 1, 1, '2023-01-15 13:48:42', '2023-01-15 13:48:42'),
(12, 'R 1.1', 'audio', 'public/course/Elementary-L1/unit1/R 1.1.mp3', 12, 1, 1, '2023-01-15 13:48:42', '2023-01-15 13:48:42'),
(13, 'R 1.10', 'audio', 'public/course/Elementary-L1/unit1/R 1.10.mp3', 21, 1, 1, '2023-01-15 13:48:42', '2023-01-15 13:48:42'),
(14, 'R 1.11', 'audio', 'public/course/Elementary-L1/unit1/R 1.11.mp3', 22, 1, 1, '2023-01-15 13:48:42', '2023-01-15 13:48:42'),
(15, 'R 1.12', 'audio', 'public/course/Elementary-L1/unit1/R 1.12.mp3', 23, 1, 1, '2023-01-15 13:48:42', '2023-01-15 13:48:42'),
(16, 'R 1.13', 'audio', 'public/course/Elementary-L1/unit1/R 1.13.mp3', 24, 1, 1, '2023-01-15 13:48:42', '2023-01-15 13:48:42'),
(17, 'R 1.14', 'audio', 'public/course/Elementary-L1/unit1/R 1.14.mp3', 25, 1, 1, '2023-01-15 13:48:42', '2023-01-15 13:48:42'),
(18, 'R 1.15', 'audio', 'public/course/Elementary-L1/unit1/R 1.15.mp3', 26, 1, 1, '2023-01-15 13:48:42', '2023-01-15 13:48:42'),
(19, 'R 1.16', 'audio', 'public/course/Elementary-L1/unit1/R 1.16.mp3', 27, 1, 1, '2023-01-15 13:48:42', '2023-01-15 13:48:42'),
(20, 'R 1.17', 'audio', 'public/course/Elementary-L1/unit1/R 1.17.mp3', 28, 1, 1, '2023-01-15 13:48:42', '2023-01-15 13:48:42'),
(21, 'R 1.18', 'audio', 'public/course/Elementary-L1/unit1/R 1.18.mp3', 29, 1, 1, '2023-01-15 13:48:42', '2023-01-15 13:48:42'),
(22, 'R 1.19', 'audio', 'public/course/Elementary-L1/unit1/R 1.19.mp3', 30, 1, 1, '2023-01-15 13:48:42', '2023-01-15 13:48:42'),
(23, 'R 1.2', 'audio', 'public/course/Elementary-L1/unit1/R 1.2.mp3', 13, 1, 1, '2023-01-15 13:48:42', '2023-01-15 13:48:42'),
(24, 'R 1.20', 'audio', 'public/course/Elementary-L1/unit1/R 1.20.mp3', 31, 1, 1, '2023-01-15 13:48:42', '2023-01-15 13:48:42'),
(25, 'R 1.21', 'audio', 'public/course/Elementary-L1/unit1/R 1.21.mp3', 32, 1, 1, '2023-01-15 13:48:42', '2023-01-15 13:48:42'),
(26, 'R 1.22', 'audio', 'public/course/Elementary-L1/unit1/R 1.22.mp3', 33, 1, 1, '2023-01-15 13:48:42', '2023-01-15 13:48:42'),
(27, 'R 1.3', 'audio', 'public/course/Elementary-L1/unit1/R 1.3.mp3', 14, 1, 1, '2023-01-15 13:48:42', '2023-01-15 13:48:42'),
(28, 'R 1.4', 'audio', 'public/course/Elementary-L1/unit1/R 1.4.mp3', 15, 1, 1, '2023-01-15 13:48:42', '2023-01-15 13:48:42'),
(29, 'R 1.5', 'audio', 'public/course/Elementary-L1/unit1/R 1.5.mp3', 16, 1, 1, '2023-01-15 13:48:42', '2023-01-15 13:48:42'),
(30, 'R 1.6', 'audio', 'public/course/Elementary-L1/unit1/R 1.6.mp3', 17, 1, 1, '2023-01-15 13:48:42', '2023-01-15 13:48:42'),
(31, 'R 1.7', 'audio', 'public/course/Elementary-L1/unit1/R 1.7.mp3', 18, 1, 1, '2023-01-15 13:48:42', '2023-01-15 13:48:42'),
(32, 'R 1.8', 'audio', 'public/course/Elementary-L1/unit1/R 1.8.mp3', 19, 1, 1, '2023-01-15 13:48:42', '2023-01-15 13:48:42'),
(33, 'R 1.9', 'audio', 'public/course/Elementary-L1/unit1/R 1.9.mp3', 20, 1, 1, '2023-01-15 13:48:42', '2023-01-15 13:48:42'),
(34, 'R 2.1', 'audio', 'public/course/Elementary-L1/unit2/R 2.1.mp3', 1, 2, 1, '2023-01-15 14:03:22', '2023-01-15 14:03:22'),
(35, 'R 2.10', 'audio', 'public/course/Elementary-L1/unit2/R 2.10.mp3', 10, 2, 1, '2023-01-15 14:03:22', '2023-01-15 14:03:22'),
(36, 'R 2.11', 'audio', 'public/course/Elementary-L1/unit2/R 2.11.mp3', 11, 2, 1, '2023-01-15 14:03:22', '2023-01-15 14:03:22'),
(37, 'R 2.12', 'audio', 'public/course/Elementary-L1/unit2/R 2.12.mp3', 12, 2, 1, '2023-01-15 14:03:22', '2023-01-15 14:03:22'),
(38, 'R 2.13', 'audio', 'public/course/Elementary-L1/unit2/R 2.13.mp3', 13, 2, 1, '2023-01-15 14:03:22', '2023-01-15 14:03:22'),
(39, 'R 2.14', 'audio', 'public/course/Elementary-L1/unit2/R 2.14.mp3', 14, 2, 1, '2023-01-15 14:03:22', '2023-01-15 14:03:22'),
(40, 'R 2.15', 'audio', 'public/course/Elementary-L1/unit2/R 2.15.mp3', 15, 2, 1, '2023-01-15 14:03:22', '2023-01-15 14:03:22'),
(41, 'R 2.16', 'audio', 'public/course/Elementary-L1/unit2/R 2.16.mp3', 16, 2, 1, '2023-01-15 14:03:22', '2023-01-15 14:03:22'),
(42, 'R 2.2', 'audio', 'public/course/Elementary-L1/unit2/R 2.2.mp3', 2, 2, 1, '2023-01-15 14:03:22', '2023-01-15 14:03:22'),
(43, 'R 2.3', 'audio', 'public/course/Elementary-L1/unit2/R 2.3.mp3', 3, 2, 1, '2023-01-15 14:03:22', '2023-01-15 14:03:22'),
(44, 'R 2.4', 'audio', 'public/course/Elementary-L1/unit2/R 2.4.mp3', 4, 2, 1, '2023-01-15 14:03:22', '2023-01-15 14:03:22'),
(45, 'R 2.5', 'audio', 'public/course/Elementary-L1/unit2/R 2.5.mp3', 5, 2, 1, '2023-01-15 14:03:22', '2023-01-15 14:03:22'),
(46, 'R 2.6', 'audio', 'public/course/Elementary-L1/unit2/R 2.6.mp3', 6, 2, 1, '2023-01-15 14:03:22', '2023-01-15 14:03:22'),
(47, 'R 2.7', 'audio', 'public/course/Elementary-L1/unit2/R 2.7.mp3', 7, 2, 1, '2023-01-15 14:03:22', '2023-01-15 14:03:22'),
(48, 'R 2.8', 'audio', 'public/course/Elementary-L1/unit2/R 2.8.mp3', 8, 2, 1, '2023-01-15 14:03:22', '2023-01-15 14:03:22'),
(49, 'R 2.9', 'audio', 'public/course/Elementary-L1/unit2/R 2.9.mp3', 9, 2, 1, '2023-01-15 14:03:22', '2023-01-15 14:03:22'),
(50, 'R 3.1', 'audio', 'public/course/Elementary-L1/unit3/R 3.1.mp3', 1, 3, 1, '2023-01-15 14:04:04', '2023-01-15 14:04:04'),
(51, 'R 3.10', 'audio', 'public/course/Elementary-L1/unit3/R 3.10.mp3', 10, 3, 1, '2023-01-15 14:04:04', '2023-01-15 14:04:04'),
(52, 'R 3.11', 'audio', 'public/course/Elementary-L1/unit3/R 3.11.mp3', 11, 3, 1, '2023-01-15 14:04:04', '2023-01-15 14:04:04'),
(53, 'R 3.12', 'audio', 'public/course/Elementary-L1/unit3/R 3.12.mp3', 12, 3, 1, '2023-01-15 14:04:04', '2023-01-15 14:04:04'),
(54, 'R 3.13', 'audio', 'public/course/Elementary-L1/unit3/R 3.13.mp3', 13, 3, 1, '2023-01-15 14:04:04', '2023-01-15 14:04:04'),
(55, 'R 3.14', 'audio', 'public/course/Elementary-L1/unit3/R 3.14.mp3', 14, 3, 1, '2023-01-15 14:04:04', '2023-01-15 14:04:04'),
(56, 'R 3.15', 'audio', 'public/course/Elementary-L1/unit3/R 3.15.mp3', 15, 3, 1, '2023-01-15 14:04:04', '2023-01-15 14:04:04'),
(57, 'R 3.16', 'audio', 'public/course/Elementary-L1/unit3/R 3.16.mp3', 16, 3, 1, '2023-01-15 14:04:04', '2023-01-15 14:04:04'),
(58, 'R 3.17', 'audio', 'public/course/Elementary-L1/unit3/R 3.17.mp3', 17, 3, 1, '2023-01-15 14:04:04', '2023-01-15 14:04:04'),
(59, 'R 3.2', 'audio', 'public/course/Elementary-L1/unit3/R 3.2.mp3', 2, 3, 1, '2023-01-15 14:04:04', '2023-01-15 14:04:04'),
(60, 'R 3.3', 'audio', 'public/course/Elementary-L1/unit3/R 3.3.mp3', 3, 3, 1, '2023-01-15 14:04:04', '2023-01-15 14:04:04'),
(61, 'R 3.4', 'audio', 'public/course/Elementary-L1/unit3/R 3.4.mp3', 4, 3, 1, '2023-01-15 14:04:04', '2023-01-15 14:04:04'),
(62, 'R 3.5', 'audio', 'public/course/Elementary-L1/unit3/R 3.5.mp3', 5, 3, 1, '2023-01-15 14:04:04', '2023-01-15 14:04:04'),
(63, 'R 3.6', 'audio', 'public/course/Elementary-L1/unit3/R 3.6.mp3', 6, 3, 1, '2023-01-15 14:04:04', '2023-01-15 14:04:04'),
(64, 'R 3.7', 'audio', 'public/course/Elementary-L1/unit3/R 3.7.mp3', 7, 3, 1, '2023-01-15 14:04:04', '2023-01-15 14:04:04'),
(65, 'R 3.8', 'audio', 'public/course/Elementary-L1/unit3/R 3.8.mp3', 8, 3, 1, '2023-01-15 14:04:04', '2023-01-15 14:04:04'),
(66, 'R 3.9', 'audio', 'public/course/Elementary-L1/unit3/R 3.9.mp3', 9, 3, 1, '2023-01-15 14:04:04', '2023-01-15 14:04:04'),
(67, 'R 4.1', 'audio', 'public/course/Elementary-L1/unit4/R 4.1.mp3', 1, 4, 1, '2023-01-15 14:06:19', '2023-01-15 14:06:19'),
(68, 'R 4.10', 'audio', 'public/course/Elementary-L1/unit4/R 4.10.mp3', 10, 4, 1, '2023-01-15 14:06:19', '2023-01-15 14:06:19'),
(69, 'R 4.11', 'audio', 'public/course/Elementary-L1/unit4/R 4.11.mp3', 11, 4, 1, '2023-01-15 14:06:19', '2023-01-15 14:06:19'),
(70, 'R 4.12', 'audio', 'public/course/Elementary-L1/unit4/R 4.12.mp3', 12, 4, 1, '2023-01-15 14:06:19', '2023-01-15 14:06:19'),
(71, 'R 4.13', 'audio', 'public/course/Elementary-L1/unit4/R 4.13.mp3', 13, 4, 1, '2023-01-15 14:06:19', '2023-01-15 14:06:19'),
(72, 'R 4.14', 'audio', 'public/course/Elementary-L1/unit4/R 4.14.mp3', 14, 4, 1, '2023-01-15 14:06:19', '2023-01-15 14:06:19'),
(73, 'R 4.15', 'audio', 'public/course/Elementary-L1/unit4/R 4.15.mp3', 15, 4, 1, '2023-01-15 14:06:19', '2023-01-15 14:06:19'),
(74, 'R 4.2', 'audio', 'public/course/Elementary-L1/unit4/R 4.2.mp3', 2, 4, 1, '2023-01-15 14:06:19', '2023-01-15 14:06:19'),
(75, 'R 4.3', 'audio', 'public/course/Elementary-L1/unit4/R 4.3.mp3', 3, 4, 1, '2023-01-15 14:06:19', '2023-01-15 14:06:19'),
(76, 'R 4.4', 'audio', 'public/course/Elementary-L1/unit4/R 4.4.mp3', 4, 4, 1, '2023-01-15 14:06:19', '2023-01-15 14:06:19'),
(77, 'R 4.5', 'audio', 'public/course/Elementary-L1/unit4/R 4.5.mp3', 5, 4, 1, '2023-01-15 14:06:19', '2023-01-15 14:06:19'),
(78, 'R 4.6', 'audio', 'public/course/Elementary-L1/unit4/R 4.6.mp3', 6, 4, 1, '2023-01-15 14:06:19', '2023-01-15 14:06:19'),
(79, 'R 4.7', 'audio', 'public/course/Elementary-L1/unit4/R 4.7.mp3', 7, 4, 1, '2023-01-15 14:06:19', '2023-01-15 14:06:19'),
(80, 'R 4.8', 'audio', 'public/course/Elementary-L1/unit4/R 4.8.mp3', 8, 4, 1, '2023-01-15 14:06:19', '2023-01-15 14:06:19'),
(81, 'R 4.9', 'audio', 'public/course/Elementary-L1/unit4/R 4.9.mp3', 9, 4, 1, '2023-01-15 14:06:19', '2023-01-15 14:06:19'),
(82, 'R 5.1', 'audio', 'public/course/Elementary-L2/Unit 5/R 5.1.mp3', 1, 5, 1, '2023-01-15 14:10:11', '2023-01-15 14:10:11'),
(83, 'R 5.10', 'audio', 'public/course/Elementary-L2/Unit 5/R 5.10.mp3', 10, 5, 1, '2023-01-15 14:10:11', '2023-01-15 14:10:11'),
(84, 'R 5.2', 'audio', 'public/course/Elementary-L2/Unit 5/R 5.2.mp3', 2, 5, 1, '2023-01-15 14:10:11', '2023-01-15 14:10:11'),
(85, 'R 5.3', 'audio', 'public/course/Elementary-L2/Unit 5/R 5.3.mp3', 3, 5, 1, '2023-01-15 14:10:11', '2023-01-15 14:10:11'),
(86, 'R 5.4', 'audio', 'public/course/Elementary-L2/Unit 5/R 5.4.mp3', 4, 5, 1, '2023-01-15 14:10:11', '2023-01-15 14:10:11'),
(87, 'R 5.5', 'audio', 'public/course/Elementary-L2/Unit 5/R 5.5.mp3', 5, 5, 1, '2023-01-15 14:10:11', '2023-01-15 14:10:11'),
(88, 'R 5.6', 'audio', 'public/course/Elementary-L2/Unit 5/R 5.6.mp3', 6, 5, 1, '2023-01-15 14:10:11', '2023-01-15 14:10:11'),
(89, 'R 5.7', 'audio', 'public/course/Elementary-L2/Unit 5/R 5.7.mp3', 7, 5, 1, '2023-01-15 14:10:11', '2023-01-15 14:10:11'),
(90, 'R 5.8', 'audio', 'public/course/Elementary-L2/Unit 5/R 5.8.mp3', 8, 5, 1, '2023-01-15 14:10:11', '2023-01-15 14:10:11'),
(91, 'R 5.9', 'audio', 'public/course/Elementary-L2/Unit 5/R 5.9.mp3', 9, 5, 1, '2023-01-15 14:10:11', '2023-01-15 14:10:11'),
(92, 'R 6.1', 'audio', 'public/course/Elementary-L2/Unit 6/R 6.1.mp3', 1, 6, 1, '2023-01-15 14:11:30', '2023-01-15 14:11:30'),
(93, 'R 6.10', 'audio', 'public/course/Elementary-L2/Unit 6/R 6.10.mp3', 10, 6, 1, '2023-01-15 14:11:30', '2023-01-15 14:11:30'),
(94, 'R 6.11', 'audio', 'public/course/Elementary-L2/Unit 6/R 6.11.mp3', 11, 6, 1, '2023-01-15 14:11:30', '2023-01-15 14:11:30'),
(95, 'R 6.12 Song', 'audio', 'public/course/Elementary-L2/Unit 6/R 6.12 Song.mp3', 12, 6, 1, '2023-01-15 14:11:30', '2023-01-15 14:11:30'),
(96, 'R 6.2', 'audio', 'public/course/Elementary-L2/Unit 6/R 6.2.mp3', 2, 6, 1, '2023-01-15 14:11:30', '2023-01-15 14:11:30'),
(97, 'R 6.3', 'audio', 'public/course/Elementary-L2/Unit 6/R 6.3.mp3', 3, 6, 1, '2023-01-15 14:11:30', '2023-01-15 14:11:30'),
(98, 'R 6.4', 'audio', 'public/course/Elementary-L2/Unit 6/R 6.4.mp3', 4, 6, 1, '2023-01-15 14:11:30', '2023-01-15 14:11:30'),
(99, 'R 6.5', 'audio', 'public/course/Elementary-L2/Unit 6/R 6.5.mp3', 5, 6, 1, '2023-01-15 14:11:30', '2023-01-15 14:11:30'),
(100, 'R 6.6', 'audio', 'public/course/Elementary-L2/Unit 6/R 6.6.mp3', 6, 6, 1, '2023-01-15 14:11:30', '2023-01-15 14:11:30'),
(101, 'R 6.7', 'audio', 'public/course/Elementary-L2/Unit 6/R 6.7.mp3', 7, 6, 1, '2023-01-15 14:11:30', '2023-01-15 14:11:30'),
(102, 'R 6.8', 'audio', 'public/course/Elementary-L2/Unit 6/R 6.8.mp3', 8, 6, 1, '2023-01-15 14:11:30', '2023-01-15 14:11:30'),
(103, 'R 6.9', 'audio', 'public/course/Elementary-L2/Unit 6/R 6.9.mp3', 9, 6, 1, '2023-01-15 14:11:30', '2023-01-15 14:11:30'),
(104, 'R 7.1', 'audio', 'public/course/Elementary-L2/Unit 7/R 7.1.mp3', 1, 7, 1, '2023-01-15 14:11:41', '2023-01-15 14:11:41'),
(105, 'R 7.10', 'audio', 'public/course/Elementary-L2/Unit 7/R 7.10.mp3', 10, 7, 1, '2023-01-15 14:11:41', '2023-01-15 14:11:41'),
(106, 'R 7.11', 'audio', 'public/course/Elementary-L2/Unit 7/R 7.11.mp3', 11, 7, 1, '2023-01-15 14:11:41', '2023-01-15 14:11:41'),
(107, 'R 7.12', 'audio', 'public/course/Elementary-L2/Unit 7/R 7.12.mp3', 12, 7, 1, '2023-01-15 14:11:41', '2023-01-15 14:11:41'),
(108, 'R 7.2', 'audio', 'public/course/Elementary-L2/Unit 7/R 7.2.mp3', 2, 7, 1, '2023-01-15 14:11:41', '2023-01-15 14:11:41'),
(109, 'R 7.3', 'audio', 'public/course/Elementary-L2/Unit 7/R 7.3.mp3', 3, 7, 1, '2023-01-15 14:11:41', '2023-01-15 14:11:41'),
(110, 'R 7.4', 'audio', 'public/course/Elementary-L2/Unit 7/R 7.4.mp3', 4, 7, 1, '2023-01-15 14:11:41', '2023-01-15 14:11:41'),
(111, 'R 7.5', 'audio', 'public/course/Elementary-L2/Unit 7/R 7.5.mp3', 5, 7, 1, '2023-01-15 14:11:41', '2023-01-15 14:11:41'),
(112, 'R 7.6', 'audio', 'public/course/Elementary-L2/Unit 7/R 7.6.mp3', 6, 7, 1, '2023-01-15 14:11:41', '2023-01-15 14:11:41'),
(113, 'R 7.7', 'audio', 'public/course/Elementary-L2/Unit 7/R 7.7.mp3', 7, 7, 1, '2023-01-15 14:11:41', '2023-01-15 14:11:41'),
(114, 'R 7.8', 'audio', 'public/course/Elementary-L2/Unit 7/R 7.8.mp3', 8, 7, 1, '2023-01-15 14:11:41', '2023-01-15 14:11:41'),
(115, 'R 7.9', 'audio', 'public/course/Elementary-L2/Unit 7/R 7.9.mp3', 9, 7, 1, '2023-01-15 14:11:41', '2023-01-15 14:11:41'),
(116, 'R 8.1', 'audio', 'public/course/Elementary-L2/Unit 8/R 8.1.mp3', 1, 8, 1, '2023-01-15 14:11:54', '2023-01-15 14:11:54'),
(117, 'R 8.10 Song', 'audio', 'public/course/Elementary-L2/Unit 8/R 8.10 Song.mp3', 10, 8, 1, '2023-01-15 14:11:54', '2023-01-15 14:11:54'),
(118, 'R 8.2', 'audio', 'public/course/Elementary-L2/Unit 8/R 8.2.mp3', 2, 8, 1, '2023-01-15 14:11:54', '2023-01-15 14:11:54'),
(119, 'R 8.3', 'audio', 'public/course/Elementary-L2/Unit 8/R 8.3.mp3', 3, 8, 1, '2023-01-15 14:11:54', '2023-01-15 14:11:54'),
(120, 'R 8.4', 'audio', 'public/course/Elementary-L2/Unit 8/R 8.4.mp3', 4, 8, 1, '2023-01-15 14:11:54', '2023-01-15 14:11:54'),
(121, 'R 8.5', 'audio', 'public/course/Elementary-L2/Unit 8/R 8.5.mp3', 5, 8, 1, '2023-01-15 14:11:54', '2023-01-15 14:11:54'),
(122, 'R 8.6', 'audio', 'public/course/Elementary-L2/Unit 8/R 8.6.mp3', 6, 8, 1, '2023-01-15 14:11:54', '2023-01-15 14:11:54'),
(123, 'R 8.7', 'audio', 'public/course/Elementary-L2/Unit 8/R 8.7.mp3', 7, 8, 1, '2023-01-15 14:11:54', '2023-01-15 14:11:54'),
(124, 'R 8.8', 'audio', 'public/course/Elementary-L2/Unit 8/R 8.8.mp3', 8, 8, 1, '2023-01-15 14:11:54', '2023-01-15 14:11:54'),
(125, 'R 8.9', 'audio', 'public/course/Elementary-L2/Unit 8/R 8.9.mp3', 9, 8, 1, '2023-01-15 14:11:54', '2023-01-15 14:11:54'),
(126, 'R 9.1', 'audio', 'public/course/Elementary-L3/Unit 9/R 9.1.mp3', 1, 9, 1, '2023-01-15 14:54:09', '2023-01-15 14:54:09'),
(127, 'R 9.10', 'audio', 'public/course/Elementary-L3/Unit 9/R 9.10.mp3', 10, 9, 1, '2023-01-15 14:54:09', '2023-01-15 14:54:09'),
(128, 'R 9.11', 'audio', 'public/course/Elementary-L3/Unit 9/R 9.11.mp3', 11, 9, 1, '2023-01-15 14:54:09', '2023-01-15 14:54:09'),
(129, 'R 9.12', 'audio', 'public/course/Elementary-L3/Unit 9/R 9.12.mp3', 12, 9, 1, '2023-01-15 14:54:09', '2023-01-15 14:54:09'),
(130, 'R 9.13', 'audio', 'public/course/Elementary-L3/Unit 9/R 9.13.mp3', 13, 9, 1, '2023-01-15 14:54:09', '2023-01-15 14:54:09'),
(131, 'R 9.14', 'audio', 'public/course/Elementary-L3/Unit 9/R 9.14.mp3', 14, 9, 1, '2023-01-15 14:54:09', '2023-01-15 14:54:09'),
(132, 'R 9.15', 'audio', 'public/course/Elementary-L3/Unit 9/R 9.15.mp3', 15, 9, 1, '2023-01-15 14:54:09', '2023-01-15 14:54:09'),
(133, 'R 9.16', 'audio', 'public/course/Elementary-L3/Unit 9/R 9.16.mp3', 16, 9, 1, '2023-01-15 14:54:09', '2023-01-15 14:54:09'),
(134, 'R 9.17', 'audio', 'public/course/Elementary-L3/Unit 9/R 9.17.mp3', 17, 9, 1, '2023-01-15 14:54:09', '2023-01-15 14:54:09'),
(135, 'R 9.18 Song', 'audio', 'public/course/Elementary-L3/Unit 9/R 9.18 Song.mp3', 18, 9, 1, '2023-01-15 14:54:09', '2023-01-15 14:54:09'),
(136, 'R 9.2', 'audio', 'public/course/Elementary-L3/Unit 9/R 9.2.mp3', 2, 9, 1, '2023-01-15 14:54:09', '2023-01-15 14:54:09'),
(137, 'R 9.3', 'audio', 'public/course/Elementary-L3/Unit 9/R 9.3.mp3', 3, 9, 1, '2023-01-15 14:54:09', '2023-01-15 14:54:09'),
(138, 'R 9.4', 'audio', 'public/course/Elementary-L3/Unit 9/R 9.4.mp3', 4, 9, 1, '2023-01-15 14:54:09', '2023-01-15 14:54:09'),
(139, 'R 9.5', 'audio', 'public/course/Elementary-L3/Unit 9/R 9.5.mp3', 5, 9, 1, '2023-01-15 14:54:09', '2023-01-15 14:54:09'),
(140, 'R 9.6', 'audio', 'public/course/Elementary-L3/Unit 9/R 9.6.mp3', 6, 9, 1, '2023-01-15 14:54:09', '2023-01-15 14:54:09'),
(141, 'R 9.7', 'audio', 'public/course/Elementary-L3/Unit 9/R 9.7.mp3', 7, 9, 1, '2023-01-15 14:54:09', '2023-01-15 14:54:09'),
(142, 'R 9.8', 'audio', 'public/course/Elementary-L3/Unit 9/R 9.8.mp3', 8, 9, 1, '2023-01-15 14:54:09', '2023-01-15 14:54:09'),
(143, 'R 9.9', 'audio', 'public/course/Elementary-L3/Unit 9/R 9.9.mp3', 9, 9, 1, '2023-01-15 14:54:09', '2023-01-15 14:54:09'),
(144, 'R 10.1', 'audio', 'public/course/Elementary-L3/Unit 10/R 10.1.mp3', 1, 10, 1, '2023-01-15 14:54:20', '2023-01-15 14:54:20'),
(145, 'R 10.10', 'audio', 'public/course/Elementary-L3/Unit 10/R 10.10.mp3', 10, 10, 1, '2023-01-15 14:54:20', '2023-01-15 14:54:20'),
(146, 'R 10.11', 'audio', 'public/course/Elementary-L3/Unit 10/R 10.11.mp3', 11, 10, 1, '2023-01-15 14:54:20', '2023-01-15 14:54:20'),
(147, 'R 10.2', 'audio', 'public/course/Elementary-L3/Unit 10/R 10.2.mp3', 2, 10, 1, '2023-01-15 14:54:20', '2023-01-15 14:54:20'),
(148, 'R 10.3', 'audio', 'public/course/Elementary-L3/Unit 10/R 10.3.mp3', 3, 10, 1, '2023-01-15 14:54:20', '2023-01-15 14:54:20'),
(149, 'R 10.4', 'audio', 'public/course/Elementary-L3/Unit 10/R 10.4.mp3', 4, 10, 1, '2023-01-15 14:54:20', '2023-01-15 14:54:20'),
(150, 'R 10.5', 'audio', 'public/course/Elementary-L3/Unit 10/R 10.5.mp3', 5, 10, 1, '2023-01-15 14:54:20', '2023-01-15 14:54:20'),
(151, 'R 10.6', 'audio', 'public/course/Elementary-L3/Unit 10/R 10.6.mp3', 6, 10, 1, '2023-01-15 14:54:20', '2023-01-15 14:54:20'),
(152, 'R 10.7', 'audio', 'public/course/Elementary-L3/Unit 10/R 10.7.mp3', 7, 10, 1, '2023-01-15 14:54:20', '2023-01-15 14:54:20'),
(153, 'R 10.8', 'audio', 'public/course/Elementary-L3/Unit 10/R 10.8.mp3', 8, 10, 1, '2023-01-15 14:54:20', '2023-01-15 14:54:20'),
(154, 'R 10.9', 'audio', 'public/course/Elementary-L3/Unit 10/R 10.9.mp3', 9, 10, 1, '2023-01-15 14:54:20', '2023-01-15 14:54:20'),
(155, 'R 11.1', 'audio', 'public/course/Elementary-L3/Unit 11/R 11.1.mp3', 1, 11, 1, '2023-01-15 14:54:30', '2023-01-15 14:54:30'),
(156, 'R 11.10', 'audio', 'public/course/Elementary-L3/Unit 11/R 11.10.mp3', 10, 11, 1, '2023-01-15 14:54:30', '2023-01-15 14:54:30'),
(157, 'R 11.11', 'audio', 'public/course/Elementary-L3/Unit 11/R 11.11.mp3', 11, 11, 1, '2023-01-15 14:54:30', '2023-01-15 14:54:30'),
(158, 'R 11.12', 'audio', 'public/course/Elementary-L3/Unit 11/R 11.12.mp3', 12, 11, 1, '2023-01-15 14:54:30', '2023-01-15 14:54:30'),
(159, 'R 11.13', 'audio', 'public/course/Elementary-L3/Unit 11/R 11.13.mp3', 13, 11, 1, '2023-01-15 14:54:30', '2023-01-15 14:54:30'),
(160, 'R 11.2', 'audio', 'public/course/Elementary-L3/Unit 11/R 11.2.mp3', 2, 11, 1, '2023-01-15 14:54:30', '2023-01-15 14:54:30'),
(161, 'R 11.3', 'audio', 'public/course/Elementary-L3/Unit 11/R 11.3.mp3', 3, 11, 1, '2023-01-15 14:54:30', '2023-01-15 14:54:30'),
(162, 'R 11.4', 'audio', 'public/course/Elementary-L3/Unit 11/R 11.4.mp3', 4, 11, 1, '2023-01-15 14:54:30', '2023-01-15 14:54:30'),
(163, 'R 11.5', 'audio', 'public/course/Elementary-L3/Unit 11/R 11.5.mp3', 5, 11, 1, '2023-01-15 14:54:30', '2023-01-15 14:54:30'),
(164, 'R 11.6', 'audio', 'public/course/Elementary-L3/Unit 11/R 11.6.mp3', 6, 11, 1, '2023-01-15 14:54:30', '2023-01-15 14:54:30'),
(165, 'R 11.7', 'audio', 'public/course/Elementary-L3/Unit 11/R 11.7.mp3', 7, 11, 1, '2023-01-15 14:54:30', '2023-01-15 14:54:30'),
(166, 'R 11.8', 'audio', 'public/course/Elementary-L3/Unit 11/R 11.8.mp3', 8, 11, 1, '2023-01-15 14:54:30', '2023-01-15 14:54:30'),
(167, 'R 11.9', 'audio', 'public/course/Elementary-L3/Unit 11/R 11.9.mp3', 9, 11, 1, '2023-01-15 14:54:30', '2023-01-15 14:54:30'),
(168, 'R 12.1', 'audio', 'public/course/Elementary-L3/Unit 12/R 12.1.mp3', 1, 12, 1, '2023-01-15 14:54:42', '2023-01-15 14:54:42'),
(169, 'R 12.10', 'audio', 'public/course/Elementary-L3/Unit 12/R 12.10.mp3', 10, 12, 1, '2023-01-15 14:54:42', '2023-01-15 14:54:42'),
(170, 'R 12.11', 'audio', 'public/course/Elementary-L3/Unit 12/R 12.11.mp3', 11, 12, 1, '2023-01-15 14:54:42', '2023-01-15 14:54:42'),
(171, 'R 12.12', 'audio', 'public/course/Elementary-L3/Unit 12/R 12.12.mp3', 12, 12, 1, '2023-01-15 14:54:42', '2023-01-15 14:54:42'),
(172, 'R 12.13', 'audio', 'public/course/Elementary-L3/Unit 12/R 12.13.mp3', 13, 12, 1, '2023-01-15 14:54:42', '2023-01-15 14:54:42'),
(173, 'R 12.2', 'audio', 'public/course/Elementary-L3/Unit 12/R 12.2.mp3', 2, 12, 1, '2023-01-15 14:54:42', '2023-01-15 14:54:42'),
(174, 'R 12.3', 'audio', 'public/course/Elementary-L3/Unit 12/R 12.3.mp3', 3, 12, 1, '2023-01-15 14:54:42', '2023-01-15 14:54:42'),
(175, 'R 12.4', 'audio', 'public/course/Elementary-L3/Unit 12/R 12.4.mp3', 4, 12, 1, '2023-01-15 14:54:42', '2023-01-15 14:54:42'),
(176, 'R 12.5', 'audio', 'public/course/Elementary-L3/Unit 12/R 12.5.mp3', 5, 12, 1, '2023-01-15 14:54:42', '2023-01-15 14:54:42'),
(177, 'R 12.6', 'audio', 'public/course/Elementary-L3/Unit 12/R 12.6.mp3', 6, 12, 1, '2023-01-15 14:54:42', '2023-01-15 14:54:42'),
(178, 'R 12.7', 'audio', 'public/course/Elementary-L3/Unit 12/R 12.7.mp3', 7, 12, 1, '2023-01-15 14:54:42', '2023-01-15 14:54:42'),
(179, 'R 12.8', 'audio', 'public/course/Elementary-L3/Unit 12/R 12.8.mp3', 8, 12, 1, '2023-01-15 14:54:42', '2023-01-15 14:54:42'),
(180, 'R 12.9', 'audio', 'public/course/Elementary-L3/Unit 12/R 12.9.mp3', 9, 12, 1, '2023-01-15 14:54:42', '2023-01-15 14:54:42'),
(181, 'R1.1', 'audio', 'public/course/Pre-Intermediate L1/Unit 1/R1.1.mp3', 1, 13, 1, '2023-01-15 14:59:21', '2023-01-15 14:59:21'),
(182, 'R1.10', 'audio', 'public/course/Pre-Intermediate L1/Unit 1/R1.10.mp3', 10, 13, 1, '2023-01-15 14:59:21', '2023-01-15 14:59:21'),
(183, 'R1.11', 'audio', 'public/course/Pre-Intermediate L1/Unit 1/R1.11.mp3', 11, 13, 1, '2023-01-15 14:59:21', '2023-01-15 14:59:21'),
(184, 'R1.12', 'audio', 'public/course/Pre-Intermediate L1/Unit 1/R1.12.mp3', 12, 13, 1, '2023-01-15 14:59:21', '2023-01-15 14:59:21'),
(185, 'R1.2', 'audio', 'public/course/Pre-Intermediate L1/Unit 1/R1.2.mp3', 2, 13, 1, '2023-01-15 14:59:21', '2023-01-15 14:59:21'),
(186, 'R1.3', 'audio', 'public/course/Pre-Intermediate L1/Unit 1/R1.3.mp3', 3, 13, 1, '2023-01-15 14:59:21', '2023-01-15 14:59:21'),
(187, 'R1.4', 'audio', 'public/course/Pre-Intermediate L1/Unit 1/R1.4.mp3', 4, 13, 1, '2023-01-15 14:59:21', '2023-01-15 14:59:21'),
(188, 'R1.5', 'audio', 'public/course/Pre-Intermediate L1/Unit 1/R1.5.mp3', 5, 13, 1, '2023-01-15 14:59:21', '2023-01-15 14:59:21'),
(189, 'R1.6', 'audio', 'public/course/Pre-Intermediate L1/Unit 1/R1.6.mp3', 6, 13, 1, '2023-01-15 14:59:21', '2023-01-15 14:59:21'),
(190, 'R1.7', 'audio', 'public/course/Pre-Intermediate L1/Unit 1/R1.7.mp3', 7, 13, 1, '2023-01-15 14:59:21', '2023-01-15 14:59:21'),
(191, 'R1.8', 'audio', 'public/course/Pre-Intermediate L1/Unit 1/R1.8.mp3', 8, 13, 1, '2023-01-15 14:59:21', '2023-01-15 14:59:21'),
(192, 'R1.9', 'audio', 'public/course/Pre-Intermediate L1/Unit 1/R1.9.mp3', 9, 13, 1, '2023-01-15 14:59:21', '2023-01-15 14:59:21'),
(193, 'R2.1', 'audio', 'public/course/Pre-Intermediate L1/Unit 2/R2.1.mp3', 1, 14, 1, '2023-01-15 15:00:09', '2023-01-15 15:00:09'),
(194, 'R2.10', 'audio', 'public/course/Pre-Intermediate L1/Unit 2/R2.10.mp3', 10, 14, 1, '2023-01-15 15:00:09', '2023-01-15 15:00:09'),
(195, 'R2.11', 'audio', 'public/course/Pre-Intermediate L1/Unit 2/R2.11.mp3', 11, 14, 1, '2023-01-15 15:00:09', '2023-01-15 15:00:09'),
(196, 'R2.12', 'audio', 'public/course/Pre-Intermediate L1/Unit 2/R2.12.mp3', 12, 14, 1, '2023-01-15 15:00:09', '2023-01-15 15:00:09'),
(197, 'R2.13', 'audio', 'public/course/Pre-Intermediate L1/Unit 2/R2.13.mp3', 13, 14, 1, '2023-01-15 15:00:09', '2023-01-15 15:00:09'),
(198, 'R2.14', 'audio', 'public/course/Pre-Intermediate L1/Unit 2/R2.14.mp3', 14, 14, 1, '2023-01-15 15:00:09', '2023-01-15 15:00:09'),
(199, 'R2.2', 'audio', 'public/course/Pre-Intermediate L1/Unit 2/R2.2.mp3', 2, 14, 1, '2023-01-15 15:00:09', '2023-01-15 15:00:09'),
(200, 'R2.3', 'audio', 'public/course/Pre-Intermediate L1/Unit 2/R2.3.mp3', 3, 14, 1, '2023-01-15 15:00:09', '2023-01-15 15:00:09'),
(201, 'R2.4', 'audio', 'public/course/Pre-Intermediate L1/Unit 2/R2.4.mp3', 4, 14, 1, '2023-01-15 15:00:09', '2023-01-15 15:00:09'),
(202, 'R2.5', 'audio', 'public/course/Pre-Intermediate L1/Unit 2/R2.5.mp3', 5, 14, 1, '2023-01-15 15:00:09', '2023-01-15 15:00:09'),
(203, 'R2.6', 'audio', 'public/course/Pre-Intermediate L1/Unit 2/R2.6.mp3', 6, 14, 1, '2023-01-15 15:00:09', '2023-01-15 15:00:09'),
(204, 'R2.7', 'audio', 'public/course/Pre-Intermediate L1/Unit 2/R2.7.mp3', 7, 14, 1, '2023-01-15 15:00:09', '2023-01-15 15:00:09'),
(205, 'R2.8', 'audio', 'public/course/Pre-Intermediate L1/Unit 2/R2.8.mp3', 8, 14, 1, '2023-01-15 15:00:09', '2023-01-15 15:00:09'),
(206, 'R2.9', 'audio', 'public/course/Pre-Intermediate L1/Unit 2/R2.9.mp3', 9, 14, 1, '2023-01-15 15:00:09', '2023-01-15 15:00:09'),
(207, 'R3.1', 'audio', 'public/course/Pre-Intermediate L1/Unit 3/R3.1.mp3', 1, 15, 1, '2023-01-15 15:00:39', '2023-01-15 15:00:39'),
(208, 'R3.2', 'audio', 'public/course/Pre-Intermediate L1/Unit 3/R3.2.mp3', 2, 15, 1, '2023-01-15 15:00:39', '2023-01-15 15:00:39'),
(209, 'R3.3', 'audio', 'public/course/Pre-Intermediate L1/Unit 3/R3.3.mp3', 3, 15, 1, '2023-01-15 15:00:39', '2023-01-15 15:00:39'),
(210, 'R3.4', 'audio', 'public/course/Pre-Intermediate L1/Unit 3/R3.4.mp3', 4, 15, 1, '2023-01-15 15:00:39', '2023-01-15 15:00:39'),
(211, 'R3.5', 'audio', 'public/course/Pre-Intermediate L1/Unit 3/R3.5.mp3', 5, 15, 1, '2023-01-15 15:00:39', '2023-01-15 15:00:39'),
(212, 'R3.6', 'audio', 'public/course/Pre-Intermediate L1/Unit 3/R3.6.mp3', 6, 15, 1, '2023-01-15 15:00:39', '2023-01-15 15:00:39'),
(213, 'R3.7', 'audio', 'public/course/Pre-Intermediate L1/Unit 3/R3.7.mp3', 7, 15, 1, '2023-01-15 15:00:39', '2023-01-15 15:00:39'),
(214, 'R3.8', 'audio', 'public/course/Pre-Intermediate L1/Unit 3/R3.8.mp3', 8, 15, 1, '2023-01-15 15:00:39', '2023-01-15 15:00:39'),
(215, 'R4.1', 'audio', 'public/course/Pre-Intermediate L1/Unit 4/R4.1.mp3', 1, 16, 1, '2023-01-15 15:00:53', '2023-01-15 15:00:53'),
(216, 'R4.10', 'audio', 'public/course/Pre-Intermediate L1/Unit 4/R4.10.mp3', 10, 16, 1, '2023-01-15 15:00:53', '2023-01-15 15:00:53'),
(217, 'R4.11', 'audio', 'public/course/Pre-Intermediate L1/Unit 4/R4.11.mp3', 11, 16, 1, '2023-01-15 15:00:53', '2023-01-15 15:00:53'),
(218, 'R4.2', 'audio', 'public/course/Pre-Intermediate L1/Unit 4/R4.2.mp3', 2, 16, 1, '2023-01-15 15:00:53', '2023-01-15 15:00:53'),
(219, 'R4.3', 'audio', 'public/course/Pre-Intermediate L1/Unit 4/R4.3.mp3', 3, 16, 1, '2023-01-15 15:00:53', '2023-01-15 15:00:53'),
(220, 'R4.4', 'audio', 'public/course/Pre-Intermediate L1/Unit 4/R4.4.mp3', 4, 16, 1, '2023-01-15 15:00:53', '2023-01-15 15:00:53'),
(221, 'R4.5', 'audio', 'public/course/Pre-Intermediate L1/Unit 4/R4.5.mp3', 5, 16, 1, '2023-01-15 15:00:53', '2023-01-15 15:00:53'),
(222, 'R4.6', 'audio', 'public/course/Pre-Intermediate L1/Unit 4/R4.6.mp3', 6, 16, 1, '2023-01-15 15:00:53', '2023-01-15 15:00:53'),
(223, 'R4.7', 'audio', 'public/course/Pre-Intermediate L1/Unit 4/R4.7.mp3', 7, 16, 1, '2023-01-15 15:00:53', '2023-01-15 15:00:53'),
(224, 'R4.8', 'audio', 'public/course/Pre-Intermediate L1/Unit 4/R4.8.mp3', 8, 16, 1, '2023-01-15 15:00:53', '2023-01-15 15:00:53'),
(225, 'R4.9', 'audio', 'public/course/Pre-Intermediate L1/Unit 4/R4.9.mp3', 9, 16, 1, '2023-01-15 15:00:53', '2023-01-15 15:00:53'),
(226, 'R5.1', 'audio', 'public/course/Pre-Intermediate L2/Unit 5/R5.1.mp3', 1, 17, 1, '2023-01-15 15:05:49', '2023-01-15 15:05:49'),
(227, 'R5.10', 'audio', 'public/course/Pre-Intermediate L2/Unit 5/R5.10.mp3', 10, 17, 1, '2023-01-15 15:05:49', '2023-01-15 15:05:49'),
(228, 'R5.11', 'audio', 'public/course/Pre-Intermediate L2/Unit 5/R5.11.mp3', 11, 17, 1, '2023-01-15 15:05:49', '2023-01-15 15:05:49'),
(229, 'R5.12', 'audio', 'public/course/Pre-Intermediate L2/Unit 5/R5.12.mp3', 12, 17, 1, '2023-01-15 15:05:49', '2023-01-15 15:05:49'),
(230, 'R5.13', 'audio', 'public/course/Pre-Intermediate L2/Unit 5/R5.13.mp3', 13, 17, 1, '2023-01-15 15:05:49', '2023-01-15 15:05:49'),
(231, 'R5.14', 'audio', 'public/course/Pre-Intermediate L2/Unit 5/R5.14.mp3', 14, 17, 1, '2023-01-15 15:05:49', '2023-01-15 15:05:49'),
(232, 'R5.15', 'audio', 'public/course/Pre-Intermediate L2/Unit 5/R5.15.mp3', 15, 17, 1, '2023-01-15 15:05:49', '2023-01-15 15:05:49'),
(233, 'R5.2', 'audio', 'public/course/Pre-Intermediate L2/Unit 5/R5.2.mp3', 2, 17, 1, '2023-01-15 15:05:49', '2023-01-15 15:05:49'),
(234, 'R5.3', 'audio', 'public/course/Pre-Intermediate L2/Unit 5/R5.3.mp3', 3, 17, 1, '2023-01-15 15:05:49', '2023-01-15 15:05:49'),
(235, 'R5.4', 'audio', 'public/course/Pre-Intermediate L2/Unit 5/R5.4.mp3', 4, 17, 1, '2023-01-15 15:05:49', '2023-01-15 15:05:49'),
(236, 'R5.5', 'audio', 'public/course/Pre-Intermediate L2/Unit 5/R5.5.mp3', 5, 17, 1, '2023-01-15 15:05:49', '2023-01-15 15:05:49'),
(237, 'R5.6', 'audio', 'public/course/Pre-Intermediate L2/Unit 5/R5.6.mp3', 6, 17, 1, '2023-01-15 15:05:49', '2023-01-15 15:05:49'),
(238, 'R5.7', 'audio', 'public/course/Pre-Intermediate L2/Unit 5/R5.7.mp3', 7, 17, 1, '2023-01-15 15:05:49', '2023-01-15 15:05:49'),
(239, 'R5.8', 'audio', 'public/course/Pre-Intermediate L2/Unit 5/R5.8.mp3', 8, 17, 1, '2023-01-15 15:05:49', '2023-01-15 15:05:49'),
(240, 'R5.9', 'audio', 'public/course/Pre-Intermediate L2/Unit 5/R5.9.mp3', 9, 17, 1, '2023-01-15 15:05:49', '2023-01-15 15:05:49'),
(241, 'R6.1', 'audio', 'public/course/Pre-Intermediate L2/Unit 6/R6.1.mp3', 1, 18, 1, '2023-01-15 15:06:02', '2023-01-15 15:06:02'),
(242, 'R6.10', 'audio', 'public/course/Pre-Intermediate L2/Unit 6/R6.10.mp3', 10, 18, 1, '2023-01-15 15:06:02', '2023-01-15 15:06:02'),
(243, 'R6.2', 'audio', 'public/course/Pre-Intermediate L2/Unit 6/R6.2.mp3', 2, 18, 1, '2023-01-15 15:06:02', '2023-01-15 15:06:02'),
(244, 'R6.3', 'audio', 'public/course/Pre-Intermediate L2/Unit 6/R6.3.mp3', 3, 18, 1, '2023-01-15 15:06:02', '2023-01-15 15:06:02'),
(245, 'R6.4', 'audio', 'public/course/Pre-Intermediate L2/Unit 6/R6.4.mp3', 4, 18, 1, '2023-01-15 15:06:02', '2023-01-15 15:06:02'),
(246, 'R6.5', 'audio', 'public/course/Pre-Intermediate L2/Unit 6/R6.5.mp3', 5, 18, 1, '2023-01-15 15:06:02', '2023-01-15 15:06:02'),
(247, 'R6.6', 'audio', 'public/course/Pre-Intermediate L2/Unit 6/R6.6.mp3', 6, 18, 1, '2023-01-15 15:06:02', '2023-01-15 15:06:02'),
(248, 'R6.7', 'audio', 'public/course/Pre-Intermediate L2/Unit 6/R6.7.mp3', 7, 18, 1, '2023-01-15 15:06:02', '2023-01-15 15:06:02'),
(249, 'R6.8', 'audio', 'public/course/Pre-Intermediate L2/Unit 6/R6.8.mp3', 8, 18, 1, '2023-01-15 15:06:02', '2023-01-15 15:06:02'),
(250, 'R6.9', 'audio', 'public/course/Pre-Intermediate L2/Unit 6/R6.9.mp3', 9, 18, 1, '2023-01-15 15:06:02', '2023-01-15 15:06:02'),
(251, 'R7.1', 'audio', 'public/course/Pre-Intermediate L2/Unit 7/R7.1.mp3', 1, 19, 1, '2023-01-15 15:06:15', '2023-01-15 15:06:15'),
(252, 'R7.10', 'audio', 'public/course/Pre-Intermediate L2/Unit 7/R7.10.mp3', 10, 19, 1, '2023-01-15 15:06:15', '2023-01-15 15:06:15'),
(253, 'R7.2', 'audio', 'public/course/Pre-Intermediate L2/Unit 7/R7.2.mp3', 2, 19, 1, '2023-01-15 15:06:15', '2023-01-15 15:06:15'),
(254, 'R7.3', 'audio', 'public/course/Pre-Intermediate L2/Unit 7/R7.3.mp3', 3, 19, 1, '2023-01-15 15:06:15', '2023-01-15 15:06:15'),
(255, 'R7.4', 'audio', 'public/course/Pre-Intermediate L2/Unit 7/R7.4.mp3', 4, 19, 1, '2023-01-15 15:06:15', '2023-01-15 15:06:15'),
(256, 'R7.5', 'audio', 'public/course/Pre-Intermediate L2/Unit 7/R7.5.mp3', 5, 19, 1, '2023-01-15 15:06:15', '2023-01-15 15:06:15'),
(257, 'R7.6', 'audio', 'public/course/Pre-Intermediate L2/Unit 7/R7.6.mp3', 6, 19, 1, '2023-01-15 15:06:15', '2023-01-15 15:06:15'),
(258, 'R7.7', 'audio', 'public/course/Pre-Intermediate L2/Unit 7/R7.7.mp3', 7, 19, 1, '2023-01-15 15:06:15', '2023-01-15 15:06:15'),
(259, 'R7.8', 'audio', 'public/course/Pre-Intermediate L2/Unit 7/R7.8.mp3', 8, 19, 1, '2023-01-15 15:06:15', '2023-01-15 15:06:15'),
(260, 'R7.9', 'audio', 'public/course/Pre-Intermediate L2/Unit 7/R7.9.mp3', 9, 19, 1, '2023-01-15 15:06:15', '2023-01-15 15:06:15'),
(261, 'R8.1', 'audio', 'public/course/Pre-Intermediate L2/Unit 8/R8.1.mp3', 1, 20, 1, '2023-01-15 15:06:29', '2023-01-15 15:06:29'),
(262, 'R8.10', 'audio', 'public/course/Pre-Intermediate L2/Unit 8/R8.10.mp3', 10, 20, 1, '2023-01-15 15:06:29', '2023-01-15 15:06:29'),
(263, 'R8.2', 'audio', 'public/course/Pre-Intermediate L2/Unit 8/R8.2.mp3', 2, 20, 1, '2023-01-15 15:06:29', '2023-01-15 15:06:29'),
(264, 'R8.3', 'audio', 'public/course/Pre-Intermediate L2/Unit 8/R8.3.mp3', 3, 20, 1, '2023-01-15 15:06:29', '2023-01-15 15:06:29'),
(265, 'R8.4', 'audio', 'public/course/Pre-Intermediate L2/Unit 8/R8.4.mp3', 4, 20, 1, '2023-01-15 15:06:29', '2023-01-15 15:06:29'),
(266, 'R8.5', 'audio', 'public/course/Pre-Intermediate L2/Unit 8/R8.5.mp3', 5, 20, 1, '2023-01-15 15:06:29', '2023-01-15 15:06:29'),
(267, 'R8.6', 'audio', 'public/course/Pre-Intermediate L2/Unit 8/R8.6.mp3', 6, 20, 1, '2023-01-15 15:06:29', '2023-01-15 15:06:29'),
(268, 'R8.7', 'audio', 'public/course/Pre-Intermediate L2/Unit 8/R8.7.mp3', 7, 20, 1, '2023-01-15 15:06:29', '2023-01-15 15:06:29'),
(269, 'R8.8', 'audio', 'public/course/Pre-Intermediate L2/Unit 8/R8.8.mp3', 8, 20, 1, '2023-01-15 15:06:29', '2023-01-15 15:06:29'),
(270, 'R8.9', 'audio', 'public/course/Pre-Intermediate L2/Unit 8/R8.9.mp3', 9, 20, 1, '2023-01-15 15:06:29', '2023-01-15 15:06:29'),
(271, 'R9.1', 'audio', 'public/course/Pre-Intermediate L3/Unit 9/R9.1.mp3', 1, 21, 1, '2023-01-15 15:09:31', '2023-01-15 15:09:31'),
(272, 'R9.10', 'audio', 'public/course/Pre-Intermediate L3/Unit 9/R9.10.mp3', 10, 21, 1, '2023-01-15 15:09:31', '2023-01-15 15:09:31'),
(273, 'R9.11', 'audio', 'public/course/Pre-Intermediate L3/Unit 9/R9.11.mp3', 11, 21, 1, '2023-01-15 15:09:31', '2023-01-15 15:09:31'),
(274, 'R9.2', 'audio', 'public/course/Pre-Intermediate L3/Unit 9/R9.2.mp3', 2, 21, 1, '2023-01-15 15:09:31', '2023-01-15 15:09:31'),
(275, 'R9.3', 'audio', 'public/course/Pre-Intermediate L3/Unit 9/R9.3.mp3', 3, 21, 1, '2023-01-15 15:09:31', '2023-01-15 15:09:31'),
(276, 'R9.4', 'audio', 'public/course/Pre-Intermediate L3/Unit 9/R9.4.mp3', 4, 21, 1, '2023-01-15 15:09:31', '2023-01-15 15:09:31'),
(277, 'R9.5', 'audio', 'public/course/Pre-Intermediate L3/Unit 9/R9.5.mp3', 5, 21, 1, '2023-01-15 15:09:31', '2023-01-15 15:09:31'),
(278, 'R9.6', 'audio', 'public/course/Pre-Intermediate L3/Unit 9/R9.6.mp3', 6, 21, 1, '2023-01-15 15:09:31', '2023-01-15 15:09:31'),
(279, 'R9.7', 'audio', 'public/course/Pre-Intermediate L3/Unit 9/R9.7.mp3', 7, 21, 1, '2023-01-15 15:09:31', '2023-01-15 15:09:31'),
(280, 'R9.8', 'audio', 'public/course/Pre-Intermediate L3/Unit 9/R9.8.mp3', 8, 21, 1, '2023-01-15 15:09:31', '2023-01-15 15:09:31'),
(281, 'R9.9', 'audio', 'public/course/Pre-Intermediate L3/Unit 9/R9.9.mp3', 9, 21, 1, '2023-01-15 15:09:31', '2023-01-15 15:09:31'),
(282, 'R10.1', 'audio', 'public/course/Pre-Intermediate L3/Unit 10/R10.1.mp3', 1, 22, 1, '2023-01-15 15:09:42', '2023-01-15 15:09:42'),
(283, 'R10.2', 'audio', 'public/course/Pre-Intermediate L3/Unit 10/R10.2.mp3', 2, 22, 1, '2023-01-15 15:09:42', '2023-01-15 15:09:42'),
(284, 'R10.3', 'audio', 'public/course/Pre-Intermediate L3/Unit 10/R10.3.mp3', 3, 22, 1, '2023-01-15 15:09:42', '2023-01-15 15:09:42'),
(285, 'R10.4', 'audio', 'public/course/Pre-Intermediate L3/Unit 10/R10.4.mp3', 4, 22, 1, '2023-01-15 15:09:42', '2023-01-15 15:09:42'),
(286, 'R10.5', 'audio', 'public/course/Pre-Intermediate L3/Unit 10/R10.5.mp3', 5, 22, 1, '2023-01-15 15:09:42', '2023-01-15 15:09:42'),
(287, 'R10.6', 'audio', 'public/course/Pre-Intermediate L3/Unit 10/R10.6.mp3', 6, 22, 1, '2023-01-15 15:09:42', '2023-01-15 15:09:42'),
(288, 'R10.7', 'audio', 'public/course/Pre-Intermediate L3/Unit 10/R10.7.mp3', 7, 22, 1, '2023-01-15 15:09:42', '2023-01-15 15:09:42'),
(289, 'R10.8', 'audio', 'public/course/Pre-Intermediate L3/Unit 10/R10.8.mp3', 8, 22, 1, '2023-01-15 15:09:42', '2023-01-15 15:09:42'),
(290, 'R11.1', 'audio', 'public/course/Pre-Intermediate L3/Unit 11/R11.1.mp3', 1, 23, 1, '2023-01-15 15:10:11', '2023-01-15 15:10:11'),
(291, 'R11.10', 'audio', 'public/course/Pre-Intermediate L3/Unit 11/R11.10.mp3', 10, 23, 1, '2023-01-15 15:10:11', '2023-01-15 15:10:11'),
(292, 'R11.11', 'audio', 'public/course/Pre-Intermediate L3/Unit 11/R11.11.mp3', 11, 23, 1, '2023-01-15 15:10:11', '2023-01-15 15:10:11'),
(293, 'R11.2', 'audio', 'public/course/Pre-Intermediate L3/Unit 11/R11.2.mp3', 2, 23, 1, '2023-01-15 15:10:11', '2023-01-15 15:10:11'),
(294, 'R11.3', 'audio', 'public/course/Pre-Intermediate L3/Unit 11/R11.3.mp3', 3, 23, 1, '2023-01-15 15:10:11', '2023-01-15 15:10:11'),
(295, 'R11.4', 'audio', 'public/course/Pre-Intermediate L3/Unit 11/R11.4.mp3', 4, 23, 1, '2023-01-15 15:10:11', '2023-01-15 15:10:11'),
(296, 'R11.5', 'audio', 'public/course/Pre-Intermediate L3/Unit 11/R11.5.mp3', 5, 23, 1, '2023-01-15 15:10:11', '2023-01-15 15:10:11'),
(297, 'R11.6', 'audio', 'public/course/Pre-Intermediate L3/Unit 11/R11.6.mp3', 6, 23, 1, '2023-01-15 15:10:11', '2023-01-15 15:10:11'),
(298, 'R11.7', 'audio', 'public/course/Pre-Intermediate L3/Unit 11/R11.7.mp3', 7, 23, 1, '2023-01-15 15:10:11', '2023-01-15 15:10:11'),
(299, 'R11.8', 'audio', 'public/course/Pre-Intermediate L3/Unit 11/R11.8.mp3', 8, 23, 1, '2023-01-15 15:10:11', '2023-01-15 15:10:11'),
(300, 'R11.9', 'audio', 'public/course/Pre-Intermediate L3/Unit 11/R11.9.mp3', 9, 23, 1, '2023-01-15 15:10:11', '2023-01-15 15:10:11'),
(301, 'R12.1', 'audio', 'public/course/Pre-Intermediate L3/Unit 12/R12.1.mp3', 1, 24, 1, '2023-01-15 15:10:22', '2023-01-15 15:10:22'),
(302, 'R12.2', 'audio', 'public/course/Pre-Intermediate L3/Unit 12/R12.2.mp3', 2, 24, 1, '2023-01-15 15:10:22', '2023-01-15 15:10:22'),
(303, 'R12.3', 'audio', 'public/course/Pre-Intermediate L3/Unit 12/R12.3.mp3', 3, 24, 1, '2023-01-15 15:10:22', '2023-01-15 15:10:22'),
(304, 'R12.4', 'audio', 'public/course/Pre-Intermediate L3/Unit 12/R12.4.mp3', 4, 24, 1, '2023-01-15 15:10:22', '2023-01-15 15:10:22'),
(305, 'R12.5', 'audio', 'public/course/Pre-Intermediate L3/Unit 12/R12.5.mp3', 5, 24, 1, '2023-01-15 15:10:22', '2023-01-15 15:10:22'),
(306, 'R12.6', 'audio', 'public/course/Pre-Intermediate L3/Unit 12/R12.6.mp3', 6, 24, 1, '2023-01-15 15:10:22', '2023-01-15 15:10:22'),
(307, 'R12.7', 'audio', 'public/course/Pre-Intermediate L3/Unit 12/R12.7.mp3', 7, 24, 1, '2023-01-15 15:10:22', '2023-01-15 15:10:22'),
(308, 'R 1.1', 'audio', 'public/course/Intermediate L1/Unit 1/R 1.1.mp4', 1, 25, 1, '2023-01-15 15:17:12', '2023-01-15 15:17:12'),
(309, 'R 1.10', 'audio', 'public/course/Intermediate L1/Unit 1/R 1.10.mp4', 10, 25, 1, '2023-01-15 15:17:12', '2023-01-15 15:17:12'),
(310, 'R 1.2', 'audio', 'public/course/Intermediate L1/Unit 1/R 1.2.mp4', 2, 25, 1, '2023-01-15 15:17:12', '2023-01-15 15:17:12'),
(311, 'R 1.3', 'audio', 'public/course/Intermediate L1/Unit 1/R 1.3.mp4', 3, 25, 1, '2023-01-15 15:17:12', '2023-01-15 15:17:12'),
(312, 'R 1.4', 'audio', 'public/course/Intermediate L1/Unit 1/R 1.4.mp4', 4, 25, 1, '2023-01-15 15:17:12', '2023-01-15 15:17:12'),
(313, 'R 1.5', 'audio', 'public/course/Intermediate L1/Unit 1/R 1.5.mp4', 5, 25, 1, '2023-01-15 15:17:12', '2023-01-15 15:17:12'),
(314, 'R 1.6', 'audio', 'public/course/Intermediate L1/Unit 1/R 1.6.mp4', 6, 25, 1, '2023-01-15 15:17:12', '2023-01-15 15:17:12'),
(315, 'R 1.7', 'audio', 'public/course/Intermediate L1/Unit 1/R 1.7.mp4', 7, 25, 1, '2023-01-15 15:17:12', '2023-01-15 15:17:12'),
(316, 'R 1.8', 'audio', 'public/course/Intermediate L1/Unit 1/R 1.8.mp4', 8, 25, 1, '2023-01-15 15:17:12', '2023-01-15 15:17:12'),
(317, 'R 1.9', 'audio', 'public/course/Intermediate L1/Unit 1/R 1.9.mp4', 9, 25, 1, '2023-01-15 15:17:12', '2023-01-15 15:17:12'),
(318, 'R 2.1', 'audio', 'public/course/Intermediate L1/Unit 2/R 2.1.mp4', 1, 26, 1, '2023-01-15 15:17:35', '2023-01-15 15:17:35'),
(319, 'R 2.10', 'audio', 'public/course/Intermediate L1/Unit 2/R 2.10.mp4', 10, 26, 1, '2023-01-15 15:17:35', '2023-01-15 15:17:35'),
(320, 'R 2.11 song', 'audio', 'public/course/Intermediate L1/Unit 2/R 2.11 song.mp4', 11, 26, 1, '2023-01-15 15:17:35', '2023-01-15 15:17:35'),
(321, 'R 2.2', 'audio', 'public/course/Intermediate L1/Unit 2/R 2.2.mp4', 2, 26, 1, '2023-01-15 15:17:35', '2023-01-15 15:17:35'),
(322, 'R 2.3', 'audio', 'public/course/Intermediate L1/Unit 2/R 2.3.mp4', 3, 26, 1, '2023-01-15 15:17:35', '2023-01-15 15:17:35'),
(323, 'R 2.4', 'audio', 'public/course/Intermediate L1/Unit 2/R 2.4.mp4', 4, 26, 1, '2023-01-15 15:17:35', '2023-01-15 15:17:35'),
(324, 'R 2.5', 'audio', 'public/course/Intermediate L1/Unit 2/R 2.5.mp4', 5, 26, 1, '2023-01-15 15:17:35', '2023-01-15 15:17:35'),
(325, 'R 2.6', 'audio', 'public/course/Intermediate L1/Unit 2/R 2.6.mp4', 6, 26, 1, '2023-01-15 15:17:35', '2023-01-15 15:17:35'),
(326, 'R 2.7', 'audio', 'public/course/Intermediate L1/Unit 2/R 2.7.mp4', 7, 26, 1, '2023-01-15 15:17:35', '2023-01-15 15:17:35'),
(327, 'R 2.8', 'audio', 'public/course/Intermediate L1/Unit 2/R 2.8.mp4', 8, 26, 1, '2023-01-15 15:17:35', '2023-01-15 15:17:35'),
(328, 'R 2.9', 'audio', 'public/course/Intermediate L1/Unit 2/R 2.9.mp4', 9, 26, 1, '2023-01-15 15:17:35', '2023-01-15 15:17:35'),
(329, 'R 3.1', 'audio', 'public/course/Intermediate L1/Unit 3/R 3.1.mp4', 1, 27, 1, '2023-01-15 15:17:46', '2023-01-15 15:17:46'),
(330, 'R 3.10', 'audio', 'public/course/Intermediate L1/Unit 3/R 3.10.mp4', 10, 27, 1, '2023-01-15 15:17:46', '2023-01-15 15:17:46'),
(331, 'R 3.2', 'audio', 'public/course/Intermediate L1/Unit 3/R 3.2.mp4', 2, 27, 1, '2023-01-15 15:17:46', '2023-01-15 15:17:46'),
(332, 'R 3.3', 'audio', 'public/course/Intermediate L1/Unit 3/R 3.3.mp4', 3, 27, 1, '2023-01-15 15:17:46', '2023-01-15 15:17:46'),
(333, 'R 3.4', 'audio', 'public/course/Intermediate L1/Unit 3/R 3.4.mp4', 4, 27, 1, '2023-01-15 15:17:46', '2023-01-15 15:17:46'),
(334, 'R 3.5', 'audio', 'public/course/Intermediate L1/Unit 3/R 3.5.mp4', 5, 27, 1, '2023-01-15 15:17:46', '2023-01-15 15:17:46'),
(335, 'R 3.6', 'audio', 'public/course/Intermediate L1/Unit 3/R 3.6.mp4', 6, 27, 1, '2023-01-15 15:17:46', '2023-01-15 15:17:46'),
(336, 'R 3.7', 'audio', 'public/course/Intermediate L1/Unit 3/R 3.7.mp4', 7, 27, 1, '2023-01-15 15:17:46', '2023-01-15 15:17:46'),
(337, 'R 3.8', 'audio', 'public/course/Intermediate L1/Unit 3/R 3.8.mp4', 8, 27, 1, '2023-01-15 15:17:46', '2023-01-15 15:17:46'),
(338, 'R 3.9', 'audio', 'public/course/Intermediate L1/Unit 3/R 3.9.mp4', 9, 27, 1, '2023-01-15 15:17:46', '2023-01-15 15:17:46'),
(339, 'R 4.1', 'audio', 'public/course/Intermediate L1/Unit 4/R 4.1.mp4', 1, 28, 1, '2023-01-15 15:18:00', '2023-01-15 15:18:00'),
(340, 'R 4.10', 'audio', 'public/course/Intermediate L1/Unit 4/R 4.10.mp4', 10, 28, 1, '2023-01-15 15:18:01', '2023-01-15 15:18:01'),
(341, 'R 4.11', 'audio', 'public/course/Intermediate L1/Unit 4/R 4.11.mp4', 11, 28, 1, '2023-01-15 15:18:01', '2023-01-15 15:18:01'),
(342, 'R 4.12', 'audio', 'public/course/Intermediate L1/Unit 4/R 4.12.mp4', 12, 28, 1, '2023-01-15 15:18:01', '2023-01-15 15:18:01'),
(343, 'R 4.13', 'audio', 'public/course/Intermediate L1/Unit 4/R 4.13.mp4', 13, 28, 1, '2023-01-15 15:18:01', '2023-01-15 15:18:01'),
(344, 'R 4.14', 'audio', 'public/course/Intermediate L1/Unit 4/R 4.14.mp4', 14, 28, 1, '2023-01-15 15:18:01', '2023-01-15 15:18:01'),
(345, 'R 4.2', 'audio', 'public/course/Intermediate L1/Unit 4/R 4.2.mp4', 2, 28, 1, '2023-01-15 15:18:01', '2023-01-15 15:18:01'),
(346, 'R 4.3', 'audio', 'public/course/Intermediate L1/Unit 4/R 4.3.mp4', 3, 28, 1, '2023-01-15 15:18:01', '2023-01-15 15:18:01'),
(347, 'R 4.4', 'audio', 'public/course/Intermediate L1/Unit 4/R 4.4.mp4', 4, 28, 1, '2023-01-15 15:18:01', '2023-01-15 15:18:01'),
(348, 'R 4.5', 'audio', 'public/course/Intermediate L1/Unit 4/R 4.5.mp4', 5, 28, 1, '2023-01-15 15:18:01', '2023-01-15 15:18:01'),
(349, 'R 4.6', 'audio', 'public/course/Intermediate L1/Unit 4/R 4.6.mp4', 6, 28, 1, '2023-01-15 15:18:01', '2023-01-15 15:18:01'),
(350, 'R 4.7', 'audio', 'public/course/Intermediate L1/Unit 4/R 4.7.mp4', 7, 28, 1, '2023-01-15 15:18:01', '2023-01-15 15:18:01'),
(351, 'R 4.8', 'audio', 'public/course/Intermediate L1/Unit 4/R 4.8.mp4', 8, 28, 1, '2023-01-15 15:18:01', '2023-01-15 15:18:01'),
(352, 'R 4.9', 'audio', 'public/course/Intermediate L1/Unit 4/R 4.9.mp4', 9, 28, 1, '2023-01-15 15:18:01', '2023-01-15 15:18:01'),
(353, 'R 5.1', 'audio', 'public/course/Intermediate L2/Unit 5/R 5.1.mp4', 1, 29, 1, '2023-01-15 15:18:55', '2023-01-15 15:18:55'),
(354, 'R 5.10 song', 'audio', 'public/course/Intermediate L2/Unit 5/R 5.10 song.mp4', 10, 29, 1, '2023-01-15 15:18:55', '2023-01-15 15:18:55'),
(355, 'R 5.2', 'audio', 'public/course/Intermediate L2/Unit 5/R 5.2.mp4', 2, 29, 1, '2023-01-15 15:18:55', '2023-01-15 15:18:55'),
(356, 'R 5.3', 'audio', 'public/course/Intermediate L2/Unit 5/R 5.3.mp4', 3, 29, 1, '2023-01-15 15:18:55', '2023-01-15 15:18:55'),
(357, 'R 5.4', 'audio', 'public/course/Intermediate L2/Unit 5/R 5.4.mp4', 4, 29, 1, '2023-01-15 15:18:55', '2023-01-15 15:18:55'),
(358, 'R 5.5', 'audio', 'public/course/Intermediate L2/Unit 5/R 5.5.mp4', 5, 29, 1, '2023-01-15 15:18:55', '2023-01-15 15:18:55'),
(359, 'R 5.6', 'audio', 'public/course/Intermediate L2/Unit 5/R 5.6.mp4', 6, 29, 1, '2023-01-15 15:18:55', '2023-01-15 15:18:55'),
(360, 'R 5.7', 'audio', 'public/course/Intermediate L2/Unit 5/R 5.7.mp4', 7, 29, 1, '2023-01-15 15:18:55', '2023-01-15 15:18:55'),
(361, 'R 5.8', 'audio', 'public/course/Intermediate L2/Unit 5/R 5.8.mp4', 8, 29, 1, '2023-01-15 15:18:55', '2023-01-15 15:18:55'),
(362, 'R 5.9', 'audio', 'public/course/Intermediate L2/Unit 5/R 5.9.mp4', 9, 29, 1, '2023-01-15 15:18:55', '2023-01-15 15:18:55'),
(363, 'R 6.1', 'audio', 'public/course/Intermediate L2/Unit 6/R 6.1.mp4', 1, 30, 1, '2023-01-15 15:19:06', '2023-01-15 15:19:06'),
(364, 'R 6.10', 'audio', 'public/course/Intermediate L2/Unit 6/R 6.10.mp4', 10, 30, 1, '2023-01-15 15:19:06', '2023-01-15 15:19:06'),
(365, 'R 6.2', 'audio', 'public/course/Intermediate L2/Unit 6/R 6.2.mp4', 2, 30, 1, '2023-01-15 15:19:06', '2023-01-15 15:19:06'),
(366, 'R 6.3', 'audio', 'public/course/Intermediate L2/Unit 6/R 6.3.mp4', 3, 30, 1, '2023-01-15 15:19:06', '2023-01-15 15:19:06'),
(367, 'R 6.4', 'audio', 'public/course/Intermediate L2/Unit 6/R 6.4.mp4', 4, 30, 1, '2023-01-15 15:19:06', '2023-01-15 15:19:06'),
(368, 'R 6.5', 'audio', 'public/course/Intermediate L2/Unit 6/R 6.5.mp4', 5, 30, 1, '2023-01-15 15:19:06', '2023-01-15 15:19:06'),
(369, 'R 6.6', 'audio', 'public/course/Intermediate L2/Unit 6/R 6.6.mp4', 6, 30, 1, '2023-01-15 15:19:06', '2023-01-15 15:19:06'),
(370, 'R 6.7', 'audio', 'public/course/Intermediate L2/Unit 6/R 6.7.mp4', 7, 30, 1, '2023-01-15 15:19:06', '2023-01-15 15:19:06'),
(371, 'R 6.8', 'audio', 'public/course/Intermediate L2/Unit 6/R 6.8.mp4', 8, 30, 1, '2023-01-15 15:19:06', '2023-01-15 15:19:06'),
(372, 'R 6.9', 'audio', 'public/course/Intermediate L2/Unit 6/R 6.9.mp4', 9, 30, 1, '2023-01-15 15:19:06', '2023-01-15 15:19:06'),
(373, 'R 7.1', 'audio', 'public/course/Intermediate L2/Unit 7/R 7.1.mp4', 1, 31, 1, '2023-01-15 15:19:17', '2023-01-15 15:19:17'),
(374, 'R 7.10', 'audio', 'public/course/Intermediate L2/Unit 7/R 7.10.mp4', 10, 31, 1, '2023-01-15 15:19:17', '2023-01-15 15:19:17'),
(375, 'R 7.11', 'audio', 'public/course/Intermediate L2/Unit 7/R 7.11.mp4', 11, 31, 1, '2023-01-15 15:19:17', '2023-01-15 15:19:17'),
(376, 'R 7.12', 'audio', 'public/course/Intermediate L2/Unit 7/R 7.12.mp4', 12, 31, 1, '2023-01-15 15:19:17', '2023-01-15 15:19:17'),
(377, 'R 7.13', 'audio', 'public/course/Intermediate L2/Unit 7/R 7.13.mp4', 13, 31, 1, '2023-01-15 15:19:17', '2023-01-15 15:19:17'),
(378, 'R 7.2', 'audio', 'public/course/Intermediate L2/Unit 7/R 7.2.mp4', 2, 31, 1, '2023-01-15 15:19:17', '2023-01-15 15:19:17'),
(379, 'R 7.3', 'audio', 'public/course/Intermediate L2/Unit 7/R 7.3.mp4', 3, 31, 1, '2023-01-15 15:19:17', '2023-01-15 15:19:17'),
(380, 'R 7.4', 'audio', 'public/course/Intermediate L2/Unit 7/R 7.4.mp4', 4, 31, 1, '2023-01-15 15:19:17', '2023-01-15 15:19:17'),
(381, 'R 7.5', 'audio', 'public/course/Intermediate L2/Unit 7/R 7.5.mp4', 5, 31, 1, '2023-01-15 15:19:17', '2023-01-15 15:19:17'),
(382, 'R 7.6', 'audio', 'public/course/Intermediate L2/Unit 7/R 7.6.mp4', 6, 31, 1, '2023-01-15 15:19:17', '2023-01-15 15:19:17'),
(383, 'R 7.7', 'audio', 'public/course/Intermediate L2/Unit 7/R 7.7.mp4', 7, 31, 1, '2023-01-15 15:19:17', '2023-01-15 15:19:17'),
(384, 'R 7.8', 'audio', 'public/course/Intermediate L2/Unit 7/R 7.8.mp4', 8, 31, 1, '2023-01-15 15:19:17', '2023-01-15 15:19:17'),
(385, 'R 7.9', 'audio', 'public/course/Intermediate L2/Unit 7/R 7.9.mp4', 9, 31, 1, '2023-01-15 15:19:17', '2023-01-15 15:19:17');
INSERT INTO `audio_lessons` (`id`, `title`, `type`, `file`, `order`, `unit_id`, `active`, `created_at`, `updated_at`) VALUES
(386, 'R 8.1', 'audio', 'public/course/Intermediate L2/Unit 8/R 8.1.mp4', 1, 32, 1, '2023-01-15 15:19:28', '2023-01-15 15:19:28'),
(387, 'R 8.2', 'audio', 'public/course/Intermediate L2/Unit 8/R 8.2.mp4', 2, 32, 1, '2023-01-15 15:19:28', '2023-01-15 15:19:28'),
(388, 'R 8.3', 'audio', 'public/course/Intermediate L2/Unit 8/R 8.3.mp4', 3, 32, 1, '2023-01-15 15:19:28', '2023-01-15 15:19:28'),
(389, 'R 8.4', 'audio', 'public/course/Intermediate L2/Unit 8/R 8.4.mp4', 4, 32, 1, '2023-01-15 15:19:28', '2023-01-15 15:19:28'),
(390, 'R 8.5', 'audio', 'public/course/Intermediate L2/Unit 8/R 8.5.mp4', 5, 32, 1, '2023-01-15 15:19:28', '2023-01-15 15:19:28'),
(391, 'R 8.6', 'audio', 'public/course/Intermediate L2/Unit 8/R 8.6.mp4', 6, 32, 1, '2023-01-15 15:19:28', '2023-01-15 15:19:28'),
(392, 'R 8.7', 'audio', 'public/course/Intermediate L2/Unit 8/R 8.7.mp4', 7, 32, 1, '2023-01-15 15:19:28', '2023-01-15 15:19:28'),
(393, 'R 8.8', 'audio', 'public/course/Intermediate L2/Unit 8/R 8.8.mp4', 8, 32, 1, '2023-01-15 15:19:28', '2023-01-15 15:19:28'),
(394, 'R 8.9', 'audio', 'public/course/Intermediate L2/Unit 8/R 8.9.mp4', 9, 32, 1, '2023-01-15 15:19:28', '2023-01-15 15:19:28'),
(395, 'R 9.1', 'audio', 'public/course/Intermediate L3/Unit 9/R 9.1.mp4', 1, 33, 1, '2023-01-15 15:20:39', '2023-01-15 15:20:39'),
(396, 'R 9.10', 'audio', 'public/course/Intermediate L3/Unit 9/R 9.10.mp4', 10, 33, 1, '2023-01-15 15:20:39', '2023-01-15 15:20:39'),
(397, 'R 9.11', 'audio', 'public/course/Intermediate L3/Unit 9/R 9.11.mp4', 11, 33, 1, '2023-01-15 15:20:39', '2023-01-15 15:20:39'),
(398, 'R 9.12', 'audio', 'public/course/Intermediate L3/Unit 9/R 9.12.mp4', 12, 33, 1, '2023-01-15 15:20:39', '2023-01-15 15:20:39'),
(399, 'R 9.2', 'audio', 'public/course/Intermediate L3/Unit 9/R 9.2.mp4', 2, 33, 1, '2023-01-15 15:20:39', '2023-01-15 15:20:39'),
(400, 'R 9.3', 'audio', 'public/course/Intermediate L3/Unit 9/R 9.3.mp4', 3, 33, 1, '2023-01-15 15:20:39', '2023-01-15 15:20:39'),
(401, 'R 9.4', 'audio', 'public/course/Intermediate L3/Unit 9/R 9.4.mp4', 4, 33, 1, '2023-01-15 15:20:39', '2023-01-15 15:20:39'),
(402, 'R 9.5', 'audio', 'public/course/Intermediate L3/Unit 9/R 9.5.mp4', 5, 33, 1, '2023-01-15 15:20:39', '2023-01-15 15:20:39'),
(403, 'R 9.6', 'audio', 'public/course/Intermediate L3/Unit 9/R 9.6.mp4', 6, 33, 1, '2023-01-15 15:20:39', '2023-01-15 15:20:39'),
(404, 'R 9.7', 'audio', 'public/course/Intermediate L3/Unit 9/R 9.7.mp4', 7, 33, 1, '2023-01-15 15:20:39', '2023-01-15 15:20:39'),
(405, 'R 9.8', 'audio', 'public/course/Intermediate L3/Unit 9/R 9.8.mp4', 8, 33, 1, '2023-01-15 15:20:39', '2023-01-15 15:20:39'),
(406, 'R 9.9', 'audio', 'public/course/Intermediate L3/Unit 9/R 9.9.mp4', 9, 33, 1, '2023-01-15 15:20:39', '2023-01-15 15:20:39'),
(407, 'R 10.1', 'audio', 'public/course/Intermediate L3/Unit 10/R 10.1.mp4', 1, 34, 1, '2023-01-15 15:20:50', '2023-01-15 15:20:50'),
(408, 'R 10.10', 'audio', 'public/course/Intermediate L3/Unit 10/R 10.10.mp4', 10, 34, 1, '2023-01-15 15:20:50', '2023-01-15 15:20:50'),
(409, 'R 10.2', 'audio', 'public/course/Intermediate L3/Unit 10/R 10.2.mp4', 2, 34, 1, '2023-01-15 15:20:50', '2023-01-15 15:20:50'),
(410, 'R 10.3', 'audio', 'public/course/Intermediate L3/Unit 10/R 10.3.mp4', 3, 34, 1, '2023-01-15 15:20:50', '2023-01-15 15:20:50'),
(411, 'R 10.4', 'audio', 'public/course/Intermediate L3/Unit 10/R 10.4.mp4', 4, 34, 1, '2023-01-15 15:20:50', '2023-01-15 15:20:50'),
(412, 'R 10.5', 'audio', 'public/course/Intermediate L3/Unit 10/R 10.5.mp4', 5, 34, 1, '2023-01-15 15:20:50', '2023-01-15 15:20:50'),
(413, 'R 10.6', 'audio', 'public/course/Intermediate L3/Unit 10/R 10.6.mp4', 6, 34, 1, '2023-01-15 15:20:50', '2023-01-15 15:20:50'),
(414, 'R 10.7', 'audio', 'public/course/Intermediate L3/Unit 10/R 10.7.mp4', 7, 34, 1, '2023-01-15 15:20:50', '2023-01-15 15:20:50'),
(415, 'R 10.8', 'audio', 'public/course/Intermediate L3/Unit 10/R 10.8.mp4', 8, 34, 1, '2023-01-15 15:20:50', '2023-01-15 15:20:50'),
(416, 'R 10.9', 'audio', 'public/course/Intermediate L3/Unit 10/R 10.9.mp4', 9, 34, 1, '2023-01-15 15:20:50', '2023-01-15 15:20:50'),
(417, 'R 11.1', 'audio', 'public/course/Intermediate L3/Unit 11/R 11.1.mp4', 1, 35, 1, '2023-01-15 15:21:01', '2023-01-15 15:21:01'),
(418, 'R 11.10', 'audio', 'public/course/Intermediate L3/Unit 11/R 11.10.mp4', 10, 35, 1, '2023-01-15 15:21:01', '2023-01-15 15:21:01'),
(419, 'R 11.11', 'audio', 'public/course/Intermediate L3/Unit 11/R 11.11.mp4', 11, 35, 1, '2023-01-15 15:21:01', '2023-01-15 15:21:01'),
(420, 'R 11.12', 'audio', 'public/course/Intermediate L3/Unit 11/R 11.12.mp4', 12, 35, 1, '2023-01-15 15:21:01', '2023-01-15 15:21:01'),
(421, 'R 11.13', 'audio', 'public/course/Intermediate L3/Unit 11/R 11.13.mp4', 13, 35, 1, '2023-01-15 15:21:01', '2023-01-15 15:21:01'),
(422, 'R 11.14', 'audio', 'public/course/Intermediate L3/Unit 11/R 11.14.mp4', 14, 35, 1, '2023-01-15 15:21:01', '2023-01-15 15:21:01'),
(423, 'R 11.2', 'audio', 'public/course/Intermediate L3/Unit 11/R 11.2.mp4', 2, 35, 1, '2023-01-15 15:21:01', '2023-01-15 15:21:01'),
(424, 'R 11.3', 'audio', 'public/course/Intermediate L3/Unit 11/R 11.3.mp4', 3, 35, 1, '2023-01-15 15:21:01', '2023-01-15 15:21:01'),
(425, 'R 11.4', 'audio', 'public/course/Intermediate L3/Unit 11/R 11.4.mp4', 4, 35, 1, '2023-01-15 15:21:01', '2023-01-15 15:21:01'),
(426, 'R 11.5', 'audio', 'public/course/Intermediate L3/Unit 11/R 11.5.mp4', 5, 35, 1, '2023-01-15 15:21:01', '2023-01-15 15:21:01'),
(427, 'R 11.6', 'audio', 'public/course/Intermediate L3/Unit 11/R 11.6.mp4', 6, 35, 1, '2023-01-15 15:21:01', '2023-01-15 15:21:01'),
(428, 'R 11.7', 'audio', 'public/course/Intermediate L3/Unit 11/R 11.7.mp4', 7, 35, 1, '2023-01-15 15:21:01', '2023-01-15 15:21:01'),
(429, 'R 11.8', 'audio', 'public/course/Intermediate L3/Unit 11/R 11.8.mp4', 8, 35, 1, '2023-01-15 15:21:01', '2023-01-15 15:21:01'),
(430, 'R 11.9', 'audio', 'public/course/Intermediate L3/Unit 11/R 11.9.mp4', 9, 35, 1, '2023-01-15 15:21:01', '2023-01-15 15:21:01'),
(431, 'R 12.1', 'audio', 'public/course/Intermediate L3/Unit 12/R 12.1.mp4', 1, 36, 1, '2023-01-15 15:21:12', '2023-01-15 15:21:12'),
(432, 'R 12.2', 'audio', 'public/course/Intermediate L3/Unit 12/R 12.2.mp4', 2, 36, 1, '2023-01-15 15:21:12', '2023-01-15 15:21:12'),
(433, 'R 12.3', 'audio', 'public/course/Intermediate L3/Unit 12/R 12.3.mp4', 3, 36, 1, '2023-01-15 15:21:12', '2023-01-15 15:21:12'),
(434, 'R 12.4', 'audio', 'public/course/Intermediate L3/Unit 12/R 12.4.mp4', 4, 36, 1, '2023-01-15 15:21:12', '2023-01-15 15:21:12'),
(435, 'R 12.5', 'audio', 'public/course/Intermediate L3/Unit 12/R 12.5.mp4', 5, 36, 1, '2023-01-15 15:21:12', '2023-01-15 15:21:12'),
(436, 'R 12.6', 'audio', 'public/course/Intermediate L3/Unit 12/R 12.6.mp4', 6, 36, 1, '2023-01-15 15:21:12', '2023-01-15 15:21:12'),
(437, 'R 12.7', 'audio', 'public/course/Intermediate L3/Unit 12/R 12.7.mp4', 7, 36, 1, '2023-01-15 15:21:12', '2023-01-15 15:21:12'),
(438, 'R 12.8', 'audio', 'public/course/Intermediate L3/Unit 12/R 12.8.mp4', 8, 36, 1, '2023-01-15 15:21:12', '2023-01-15 15:21:12'),
(439, 'R 1.1', 'audio', 'public/course/Upper-Intermediate L1/Unit 1/R 1.1.mp4', 1, 37, 1, '2023-01-15 15:25:17', '2023-01-15 15:25:17'),
(440, 'R 1.10', 'audio', 'public/course/Upper-Intermediate L1/Unit 1/R 1.10.mp4', 10, 37, 1, '2023-01-15 15:25:17', '2023-01-15 15:25:17'),
(441, 'R 1.2', 'audio', 'public/course/Upper-Intermediate L1/Unit 1/R 1.2.mp4', 2, 37, 1, '2023-01-15 15:25:17', '2023-01-15 15:25:17'),
(442, 'R 1.3', 'audio', 'public/course/Upper-Intermediate L1/Unit 1/R 1.3.mp4', 3, 37, 1, '2023-01-15 15:25:17', '2023-01-15 15:25:17'),
(443, 'R 1.4', 'audio', 'public/course/Upper-Intermediate L1/Unit 1/R 1.4.mp4', 4, 37, 1, '2023-01-15 15:25:17', '2023-01-15 15:25:17'),
(444, 'R 1.5', 'audio', 'public/course/Upper-Intermediate L1/Unit 1/R 1.5.mp4', 5, 37, 1, '2023-01-15 15:25:17', '2023-01-15 15:25:17'),
(445, 'R 1.6', 'audio', 'public/course/Upper-Intermediate L1/Unit 1/R 1.6.mp4', 6, 37, 1, '2023-01-15 15:25:17', '2023-01-15 15:25:17'),
(446, 'R 1.7', 'audio', 'public/course/Upper-Intermediate L1/Unit 1/R 1.7.mp4', 7, 37, 1, '2023-01-15 15:25:17', '2023-01-15 15:25:17'),
(447, 'R 1.8', 'audio', 'public/course/Upper-Intermediate L1/Unit 1/R 1.8.mp4', 8, 37, 1, '2023-01-15 15:25:17', '2023-01-15 15:25:17'),
(448, 'R 1.9', 'audio', 'public/course/Upper-Intermediate L1/Unit 1/R 1.9.mp4', 9, 37, 1, '2023-01-15 15:25:17', '2023-01-15 15:25:17'),
(449, 'R 2.1', 'audio', 'public/course/Upper-Intermediate L1/Unit 2/R 2.1.mp4', 1, 38, 1, '2023-01-15 15:25:28', '2023-01-15 15:25:28'),
(450, 'R 2.2', 'audio', 'public/course/Upper-Intermediate L1/Unit 2/R 2.2.mp4', 2, 38, 1, '2023-01-15 15:25:28', '2023-01-15 15:25:28'),
(451, 'R 2.3', 'audio', 'public/course/Upper-Intermediate L1/Unit 2/R 2.3.mp4', 3, 38, 1, '2023-01-15 15:25:28', '2023-01-15 15:25:28'),
(452, 'R 2.4', 'audio', 'public/course/Upper-Intermediate L1/Unit 2/R 2.4.mp4', 4, 38, 1, '2023-01-15 15:25:28', '2023-01-15 15:25:28'),
(453, 'R 2.5', 'audio', 'public/course/Upper-Intermediate L1/Unit 2/R 2.5.mp4', 5, 38, 1, '2023-01-15 15:25:28', '2023-01-15 15:25:28'),
(454, 'R 2.6', 'audio', 'public/course/Upper-Intermediate L1/Unit 2/R 2.6.mp4', 6, 38, 1, '2023-01-15 15:25:28', '2023-01-15 15:25:28'),
(455, 'R 2.7', 'audio', 'public/course/Upper-Intermediate L1/Unit 2/R 2.7.mp4', 7, 38, 1, '2023-01-15 15:25:28', '2023-01-15 15:25:28'),
(456, 'R 2.8', 'audio', 'public/course/Upper-Intermediate L1/Unit 2/R 2.8.mp4', 8, 38, 1, '2023-01-15 15:25:28', '2023-01-15 15:25:28'),
(457, 'R 3.1', 'audio', 'public/course/Upper-Intermediate L1/Unit 3/R 3.1.mp4', 1, 39, 1, '2023-01-15 15:25:39', '2023-01-15 15:25:39'),
(458, 'R 3.2', 'audio', 'public/course/Upper-Intermediate L1/Unit 3/R 3.2.mp4', 2, 39, 1, '2023-01-15 15:25:39', '2023-01-15 15:25:39'),
(459, 'R 3.3', 'audio', 'public/course/Upper-Intermediate L1/Unit 3/R 3.3.mp4', 3, 39, 1, '2023-01-15 15:25:39', '2023-01-15 15:25:39'),
(460, 'R 3.4', 'audio', 'public/course/Upper-Intermediate L1/Unit 3/R 3.4.mp4', 4, 39, 1, '2023-01-15 15:25:39', '2023-01-15 15:25:39'),
(461, 'R 3.5', 'audio', 'public/course/Upper-Intermediate L1/Unit 3/R 3.5.mp4', 5, 39, 1, '2023-01-15 15:25:39', '2023-01-15 15:25:39'),
(462, 'R 3.6', 'audio', 'public/course/Upper-Intermediate L1/Unit 3/R 3.6.mp4', 6, 39, 1, '2023-01-15 15:25:39', '2023-01-15 15:25:39'),
(463, 'R 3.7', 'audio', 'public/course/Upper-Intermediate L1/Unit 3/R 3.7.mp4', 7, 39, 1, '2023-01-15 15:25:39', '2023-01-15 15:25:39'),
(464, 'R 3.8', 'audio', 'public/course/Upper-Intermediate L1/Unit 3/R 3.8.mp4', 8, 39, 1, '2023-01-15 15:25:39', '2023-01-15 15:25:39'),
(465, 'R 3.9', 'audio', 'public/course/Upper-Intermediate L1/Unit 3/R 3.9.mp4', 9, 39, 1, '2023-01-15 15:25:39', '2023-01-15 15:25:39'),
(466, 'R 4.1', 'audio', 'public/course/Upper-Intermediate L1/Unit 4/R 4.1.mp4', 1, 40, 1, '2023-01-15 15:25:50', '2023-01-15 15:25:50'),
(467, 'R 4.2', 'audio', 'public/course/Upper-Intermediate L1/Unit 4/R 4.2.mp4', 2, 40, 1, '2023-01-15 15:25:50', '2023-01-15 15:25:50'),
(468, 'R 4.3', 'audio', 'public/course/Upper-Intermediate L1/Unit 4/R 4.3.mp4', 3, 40, 1, '2023-01-15 15:25:50', '2023-01-15 15:25:50'),
(469, 'R 4.4', 'audio', 'public/course/Upper-Intermediate L1/Unit 4/R 4.4.mp4', 4, 40, 1, '2023-01-15 15:25:50', '2023-01-15 15:25:50'),
(470, 'R 4.5', 'audio', 'public/course/Upper-Intermediate L1/Unit 4/R 4.5.mp4', 5, 40, 1, '2023-01-15 15:25:50', '2023-01-15 15:25:50'),
(471, 'R 4.6', 'audio', 'public/course/Upper-Intermediate L1/Unit 4/R 4.6.mp4', 6, 40, 1, '2023-01-15 15:25:50', '2023-01-15 15:25:50'),
(472, 'R 4.7', 'audio', 'public/course/Upper-Intermediate L1/Unit 4/R 4.7.mp4', 7, 40, 1, '2023-01-15 15:25:50', '2023-01-15 15:25:50'),
(473, 'R 4.8', 'audio', 'public/course/Upper-Intermediate L1/Unit 4/R 4.8.mp4', 8, 40, 1, '2023-01-15 15:25:50', '2023-01-15 15:25:50'),
(474, 'R 5.1', 'audio', 'public/course/Upper-Intermediate L2/Unit 5/R 5.1.mp4', 1, 41, 1, '2023-01-15 15:26:45', '2023-01-15 15:26:45'),
(475, 'R 5.2', 'audio', 'public/course/Upper-Intermediate L2/Unit 5/R 5.2.mp4', 2, 41, 1, '2023-01-15 15:26:45', '2023-01-15 15:26:45'),
(476, 'R 5.3', 'audio', 'public/course/Upper-Intermediate L2/Unit 5/R 5.3.mp4', 3, 41, 1, '2023-01-15 15:26:45', '2023-01-15 15:26:45'),
(477, 'R 5.4', 'audio', 'public/course/Upper-Intermediate L2/Unit 5/R 5.4.mp4', 4, 41, 1, '2023-01-15 15:26:45', '2023-01-15 15:26:45'),
(478, 'R 5.5', 'audio', 'public/course/Upper-Intermediate L2/Unit 5/R 5.5.mp4', 5, 41, 1, '2023-01-15 15:26:45', '2023-01-15 15:26:45'),
(479, 'R 5.6', 'audio', 'public/course/Upper-Intermediate L2/Unit 5/R 5.6.mp4', 6, 41, 1, '2023-01-15 15:26:45', '2023-01-15 15:26:45'),
(480, 'R 5.7', 'audio', 'public/course/Upper-Intermediate L2/Unit 5/R 5.7.mp4', 7, 41, 1, '2023-01-15 15:26:45', '2023-01-15 15:26:45'),
(481, 'R 5.8', 'audio', 'public/course/Upper-Intermediate L2/Unit 5/R 5.8.mp4', 8, 41, 1, '2023-01-15 15:26:45', '2023-01-15 15:26:45'),
(482, 'R 6.1', 'audio', 'public/course/Upper-Intermediate L2/Unit 6/R 6.1.mp4', 1, 42, 1, '2023-01-15 15:26:58', '2023-01-15 15:26:58'),
(483, 'R 6.2', 'audio', 'public/course/Upper-Intermediate L2/Unit 6/R 6.2.mp4', 2, 42, 1, '2023-01-15 15:26:58', '2023-01-15 15:26:58'),
(484, 'R 6.3', 'audio', 'public/course/Upper-Intermediate L2/Unit 6/R 6.3.mp4', 3, 42, 1, '2023-01-15 15:26:58', '2023-01-15 15:26:58'),
(485, 'R 6.4', 'audio', 'public/course/Upper-Intermediate L2/Unit 6/R 6.4.mp4', 4, 42, 1, '2023-01-15 15:26:58', '2023-01-15 15:26:58'),
(486, 'R 6.5', 'audio', 'public/course/Upper-Intermediate L2/Unit 6/R 6.5.mp4', 5, 42, 1, '2023-01-15 15:26:58', '2023-01-15 15:26:58'),
(487, 'R 6.6', 'audio', 'public/course/Upper-Intermediate L2/Unit 6/R 6.6.mp4', 6, 42, 1, '2023-01-15 15:26:58', '2023-01-15 15:26:58'),
(488, 'R 6.7', 'audio', 'public/course/Upper-Intermediate L2/Unit 6/R 6.7.mp4', 7, 42, 1, '2023-01-15 15:26:58', '2023-01-15 15:26:58'),
(489, 'R 6.8', 'audio', 'public/course/Upper-Intermediate L2/Unit 6/R 6.8.mp4', 8, 42, 1, '2023-01-15 15:26:58', '2023-01-15 15:26:58'),
(490, 'R 6.9', 'audio', 'public/course/Upper-Intermediate L2/Unit 6/R 6.9.mp4', 9, 42, 1, '2023-01-15 15:26:58', '2023-01-15 15:26:58'),
(491, 'R 7.1', 'audio', 'public/course/Upper-Intermediate L2/Unit 7/R 7.1.mp4', 1, 43, 1, '2023-01-15 15:27:10', '2023-01-15 15:27:10'),
(492, 'R 7.2', 'audio', 'public/course/Upper-Intermediate L2/Unit 7/R 7.2.mp4', 2, 43, 1, '2023-01-15 15:27:10', '2023-01-15 15:27:10'),
(493, 'R 7.3.1', 'audio', 'public/course/Upper-Intermediate L2/Unit 7/R 7.3.1.mp4', 3, 43, 1, '2023-01-15 15:27:10', '2023-01-15 15:27:10'),
(494, 'R 7.3.2', 'audio', 'public/course/Upper-Intermediate L2/Unit 7/R 7.3.2.mp4', 3, 43, 1, '2023-01-15 15:27:10', '2023-01-15 15:27:10'),
(495, 'R 7.3.3', 'audio', 'public/course/Upper-Intermediate L2/Unit 7/R 7.3.3.mp4', 3, 43, 1, '2023-01-15 15:27:10', '2023-01-15 15:27:10'),
(496, 'R 7.4', 'audio', 'public/course/Upper-Intermediate L2/Unit 7/R 7.4.mp4', 4, 43, 1, '2023-01-15 15:27:10', '2023-01-15 15:27:10'),
(497, 'R 7.5', 'audio', 'public/course/Upper-Intermediate L2/Unit 7/R 7.5.mp4', 5, 43, 1, '2023-01-15 15:27:10', '2023-01-15 15:27:10'),
(498, 'R 8.1', 'audio', 'public/course/Upper-Intermediate L2/Unit 8/R 8.1.mp4', 1, 44, 1, '2023-01-15 15:27:24', '2023-01-15 15:27:24'),
(499, 'R 8.2', 'audio', 'public/course/Upper-Intermediate L2/Unit 8/R 8.2.mp4', 2, 44, 1, '2023-01-15 15:27:24', '2023-01-15 15:27:24'),
(500, 'R 8.3', 'audio', 'public/course/Upper-Intermediate L2/Unit 8/R 8.3.mp4', 3, 44, 1, '2023-01-15 15:27:24', '2023-01-15 15:27:24'),
(501, 'R 8.4', 'audio', 'public/course/Upper-Intermediate L2/Unit 8/R 8.4.mp4', 4, 44, 1, '2023-01-15 15:27:24', '2023-01-15 15:27:24'),
(502, 'R 8.5', 'audio', 'public/course/Upper-Intermediate L2/Unit 8/R 8.5.mp4', 5, 44, 1, '2023-01-15 15:27:24', '2023-01-15 15:27:24'),
(503, 'R 8.6', 'audio', 'public/course/Upper-Intermediate L2/Unit 8/R 8.6.mp4', 6, 44, 1, '2023-01-15 15:27:24', '2023-01-15 15:27:24'),
(504, 'R 8.7', 'audio', 'public/course/Upper-Intermediate L2/Unit 8/R 8.7.mp4', 7, 44, 1, '2023-01-15 15:27:24', '2023-01-15 15:27:24'),
(505, 'R 8.8', 'audio', 'public/course/Upper-Intermediate L2/Unit 8/R 8.8.mp4', 8, 44, 1, '2023-01-15 15:27:24', '2023-01-15 15:27:24'),
(506, 'R 8.9', 'audio', 'public/course/Upper-Intermediate L2/Unit 8/R 8.9.mp4', 9, 44, 1, '2023-01-15 15:27:24', '2023-01-15 15:27:24'),
(507, 'R 9.1', 'audio', 'public/course/Upper-Intermediate L3/Unit 9/R 9.1.mp4', 1, 45, 1, '2023-01-15 15:28:05', '2023-01-15 15:28:05'),
(508, 'R 9.2', 'audio', 'public/course/Upper-Intermediate L3/Unit 9/R 9.2.mp4', 2, 45, 1, '2023-01-15 15:28:05', '2023-01-15 15:28:05'),
(509, 'R 9.3', 'audio', 'public/course/Upper-Intermediate L3/Unit 9/R 9.3.mp4', 3, 45, 1, '2023-01-15 15:28:05', '2023-01-15 15:28:05'),
(510, 'R 9.4', 'audio', 'public/course/Upper-Intermediate L3/Unit 9/R 9.4.mp4', 4, 45, 1, '2023-01-15 15:28:05', '2023-01-15 15:28:05'),
(511, 'R 9.5', 'audio', 'public/course/Upper-Intermediate L3/Unit 9/R 9.5.mp4', 5, 45, 1, '2023-01-15 15:28:05', '2023-01-15 15:28:05'),
(512, 'R 10.1', 'audio', 'public/course/Upper-Intermediate L3/Unit 10/R 10.1.mp4', 1, 46, 1, '2023-01-15 15:28:16', '2023-01-15 15:28:16'),
(513, 'R 10.10', 'audio', 'public/course/Upper-Intermediate L3/Unit 10/R 10.10.mp4', 10, 46, 1, '2023-01-15 15:28:16', '2023-01-15 15:28:16'),
(514, 'R 10.11', 'audio', 'public/course/Upper-Intermediate L3/Unit 10/R 10.11.mp4', 11, 46, 1, '2023-01-15 15:28:16', '2023-01-15 15:28:16'),
(515, 'R 10.2', 'audio', 'public/course/Upper-Intermediate L3/Unit 10/R 10.2.mp4', 2, 46, 1, '2023-01-15 15:28:16', '2023-01-15 15:28:16'),
(516, 'R 10.3', 'audio', 'public/course/Upper-Intermediate L3/Unit 10/R 10.3.mp4', 3, 46, 1, '2023-01-15 15:28:16', '2023-01-15 15:28:16'),
(517, 'R 10.4', 'audio', 'public/course/Upper-Intermediate L3/Unit 10/R 10.4.mp4', 4, 46, 1, '2023-01-15 15:28:16', '2023-01-15 15:28:16'),
(518, 'R 10.5', 'audio', 'public/course/Upper-Intermediate L3/Unit 10/R 10.5.mp4', 5, 46, 1, '2023-01-15 15:28:16', '2023-01-15 15:28:16'),
(519, 'R 10.6', 'audio', 'public/course/Upper-Intermediate L3/Unit 10/R 10.6.mp4', 6, 46, 1, '2023-01-15 15:28:16', '2023-01-15 15:28:16'),
(520, 'R 10.7', 'audio', 'public/course/Upper-Intermediate L3/Unit 10/R 10.7.mp4', 7, 46, 1, '2023-01-15 15:28:16', '2023-01-15 15:28:16'),
(521, 'R 10.8', 'audio', 'public/course/Upper-Intermediate L3/Unit 10/R 10.8.mp4', 8, 46, 1, '2023-01-15 15:28:16', '2023-01-15 15:28:16'),
(522, 'R 10.9', 'audio', 'public/course/Upper-Intermediate L3/Unit 10/R 10.9.mp4', 9, 46, 1, '2023-01-15 15:28:16', '2023-01-15 15:28:16'),
(523, 'R 11.1', 'audio', 'public/course/Upper-Intermediate L3/Unit 11/R 11.1.mp4', 1, 47, 1, '2023-01-15 15:28:28', '2023-01-15 15:28:28'),
(524, 'R 11.2', 'audio', 'public/course/Upper-Intermediate L3/Unit 11/R 11.2.mp4', 2, 47, 1, '2023-01-15 15:28:28', '2023-01-15 15:28:28'),
(525, 'R 11.3', 'audio', 'public/course/Upper-Intermediate L3/Unit 11/R 11.3.mp4', 3, 47, 1, '2023-01-15 15:28:28', '2023-01-15 15:28:28'),
(526, 'R 11.4', 'audio', 'public/course/Upper-Intermediate L3/Unit 11/R 11.4.mp4', 4, 47, 1, '2023-01-15 15:28:28', '2023-01-15 15:28:28'),
(527, 'R 11.5', 'audio', 'public/course/Upper-Intermediate L3/Unit 11/R 11.5.mp4', 5, 47, 1, '2023-01-15 15:28:28', '2023-01-15 15:28:28'),
(528, 'R 11.6', 'audio', 'public/course/Upper-Intermediate L3/Unit 11/R 11.6.mp4', 6, 47, 1, '2023-01-15 15:28:28', '2023-01-15 15:28:28'),
(529, 'R 11.7', 'audio', 'public/course/Upper-Intermediate L3/Unit 11/R 11.7.mp4', 7, 47, 1, '2023-01-15 15:28:28', '2023-01-15 15:28:28'),
(530, 'R 11.8', 'audio', 'public/course/Upper-Intermediate L3/Unit 11/R 11.8.mp4', 8, 47, 1, '2023-01-15 15:28:28', '2023-01-15 15:28:28'),
(531, 'R 11.9', 'audio', 'public/course/Upper-Intermediate L3/Unit 11/R 11.9.mp4', 9, 47, 1, '2023-01-15 15:28:28', '2023-01-15 15:28:28'),
(532, 'R 12.1', 'audio', 'public/course/Upper-Intermediate L3/Unit 12/R 12.1.mp4', 1, 48, 1, '2023-01-15 15:28:42', '2023-01-15 15:28:42'),
(533, 'R 12.2', 'audio', 'public/course/Upper-Intermediate L3/Unit 12/R 12.2.mp4', 2, 48, 1, '2023-01-15 15:28:42', '2023-01-15 15:28:42'),
(534, 'R 12.3', 'audio', 'public/course/Upper-Intermediate L3/Unit 12/R 12.3.mp4', 3, 48, 1, '2023-01-15 15:28:42', '2023-01-15 15:28:42'),
(535, 'R 12.4', 'audio', 'public/course/Upper-Intermediate L3/Unit 12/R 12.4.mp4', 4, 48, 1, '2023-01-15 15:28:42', '2023-01-15 15:28:42'),
(536, 'R 12.5', 'audio', 'public/course/Upper-Intermediate L3/Unit 12/R 12.5.mp4', 5, 48, 1, '2023-01-15 15:28:42', '2023-01-15 15:28:42'),
(537, 'R 12.6', 'audio', 'public/course/Upper-Intermediate L3/Unit 12/R 12.6.mp4', 6, 48, 1, '2023-01-15 15:28:42', '2023-01-15 15:28:42'),
(538, 'R 12.7', 'audio', 'public/course/Upper-Intermediate L3/Unit 12/R 12.7.mp4', 7, 48, 1, '2023-01-15 15:28:42', '2023-01-15 15:28:42'),
(539, 'R 12.8', 'audio', 'public/course/Upper-Intermediate L3/Unit 12/R 12.8.mp4', 8, 48, 1, '2023-01-15 15:28:42', '2023-01-15 15:28:42');

-- --------------------------------------------------------

--
-- Table structure for table `audio_units`
--

CREATE TABLE `audio_units` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `order` int(11) DEFAULT NULL,
  `level_id` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audio_units`
--

INSERT INTO `audio_units` (`id`, `title`, `order`, `level_id`, `active`, `created_at`, `updated_at`) VALUES
(1, 'Unit 1', 1, 1, 1, NULL, NULL),
(2, 'Unit 2', 2, 1, 1, NULL, NULL),
(3, 'Unit 3', 3, 1, 1, NULL, NULL),
(4, 'Unit 4', 4, 1, 1, NULL, NULL),
(5, 'Unit 5', 1, 2, 1, NULL, NULL),
(6, 'Unit 6', 2, 2, 1, NULL, NULL),
(7, 'Unit 7', 3, 2, 1, NULL, NULL),
(8, 'Unit 8', 4, 2, 1, NULL, NULL),
(9, 'Unit 9', 1, 3, 1, NULL, NULL),
(10, 'Unit 10', 2, 3, 1, NULL, NULL),
(11, 'Unit 11', 3, 3, 1, NULL, NULL),
(12, 'Unit 12', 4, 3, 1, NULL, NULL),
(13, 'Unit 1', 1, 4, 1, NULL, NULL),
(14, 'Unit 2', 2, 4, 1, NULL, NULL),
(15, 'Unit 3', 3, 4, 1, NULL, NULL),
(16, 'Unit 4', 4, 4, 1, NULL, NULL),
(17, 'Unit 5', 1, 5, 1, NULL, NULL),
(18, 'Unit 6', 2, 5, 1, NULL, NULL),
(19, 'Unit 7', 3, 5, 1, NULL, NULL),
(20, 'Unit 8', 4, 5, 1, NULL, NULL),
(21, 'Unit 9', 1, 6, 1, NULL, NULL),
(22, 'Unit 10', 2, 6, 1, NULL, NULL),
(23, 'Unit 11', 3, 6, 1, NULL, NULL),
(24, 'Unit 12', 4, 6, 1, NULL, NULL),
(25, 'Unit 1', 1, 7, 1, NULL, NULL),
(26, 'Unit 2', 2, 7, 1, NULL, NULL),
(27, 'Unit 3', 3, 7, 1, NULL, NULL),
(28, 'Unit 4', 4, 7, 1, NULL, NULL),
(29, 'Unit 5', 1, 8, 1, NULL, NULL),
(30, 'Unit 6', 2, 8, 1, NULL, NULL),
(31, 'Unit 7', 3, 8, 1, NULL, NULL),
(32, 'Unit 8', 4, 8, 1, NULL, NULL),
(33, 'Unit 9', 1, 9, 1, NULL, NULL),
(34, 'Unit 10', 2, 9, 1, NULL, NULL),
(35, 'Unit 11', 3, 9, 1, NULL, NULL),
(36, 'Unit 12', 4, 9, 1, NULL, NULL),
(37, 'Unit 1', 1, 10, 1, NULL, NULL),
(38, 'Unit 2', 2, 10, 1, NULL, NULL),
(39, 'Unit 3', 3, 10, 1, NULL, NULL),
(40, 'Unit 4', 4, 10, 1, NULL, NULL),
(41, 'Unit 5', 1, 11, 1, NULL, NULL),
(42, 'Unit 6', 2, 11, 1, NULL, NULL),
(43, 'Unit 7', 3, 11, 1, NULL, NULL),
(44, 'Unit 8', 4, 11, 1, NULL, NULL),
(45, 'Unit 9', 1, 12, 1, NULL, NULL),
(46, 'Unit 10', 2, 12, 1, NULL, NULL),
(47, 'Unit 11', 3, 12, 1, NULL, NULL),
(48, 'Unit 12', 4, 12, 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` bigint(20) NOT NULL,
  `parent_name` varchar(255) NOT NULL,
  `parent_email` varchar(255) NOT NULL,
  `type` enum('IntSchool','English','Quran') DEFAULT NULL,
  `parent_phone` varchar(255) NOT NULL,
  `parent_id` int(10) DEFAULT NULL,
  `student_id` int(10) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `child_name` varchar(255) DEFAULT NULL,
  `child_age` int(11) DEFAULT NULL,
  `child_grade` int(10) DEFAULT NULL,
  `current_school` varchar(255) DEFAULT NULL,
  `school_system` enum('IB','American','British','Egyptian','Other') DEFAULT NULL,
  `primary_challenges` text DEFAULT NULL,
  `service_interest` varchar(255) DEFAULT NULL,
  `contact_method` enum('email','phone','both') NOT NULL DEFAULT 'email',
  `preferred_date` date DEFAULT NULL,
  `preferred_time` varchar(255) DEFAULT NULL,
  `consultation_time` varchar(255) DEFAULT NULL,
  `consultation_type` enum('online','in-person') DEFAULT NULL,
  `consultation_date` date DEFAULT NULL,
  `follow_up_date` datetime DEFAULT NULL,
  `main_concerns` text DEFAULT NULL,
  `how_heard` enum('google-search','social-media','friend-referral','school-recommendation') DEFAULT NULL,
  `status` enum('pending','confirmed','followup','cancelled','fit','unfit') NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `booking_reference` text DEFAULT NULL,
  `terms` tinyint(4) NOT NULL DEFAULT 0,
  `teacher_notes` text DEFAULT NULL,
  `transfer` tinyint(1) NOT NULL DEFAULT 0,
  `meeting_link` longtext DEFAULT NULL,
  `meeting_address` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `parent_name`, `parent_email`, `type`, `parent_phone`, `parent_id`, `student_id`, `country`, `child_name`, `child_age`, `child_grade`, `current_school`, `school_system`, `primary_challenges`, `service_interest`, `contact_method`, `preferred_date`, `preferred_time`, `consultation_time`, `consultation_type`, `consultation_date`, `follow_up_date`, `main_concerns`, `how_heard`, `status`, `notes`, `booking_reference`, `terms`, `teacher_notes`, `transfer`, `meeting_link`, `meeting_address`, `created_at`, `updated_at`) VALUES
(1, 'Osama Elazab', 'osama.elazab22@gmail.com', 'Quran', '+201146004550', NULL, NULL, 'us', 'doha', 10, NULL, NULL, NULL, NULL, 'My Deen Journey (Parenting System)', 'email', '2026-01-08', 'morning', NULL, NULL, NULL, NULL, 'hy', NULL, 'pending', NULL, 'W14-3E98D56D', 1, NULL, 0, NULL, NULL, '2026-01-07 18:14:34', '2026-01-07 18:14:34'),
(2, 'Osama Elazab', 'osama.elazab22@gmail.com', 'Quran', '+201146004550', NULL, NULL, 'us', 'Mustafa', 10, NULL, NULL, NULL, NULL, 'Quran Memorization', 'email', '2026-01-15', 'afternoon', NULL, NULL, NULL, NULL, NULL, NULL, 'pending', NULL, 'W14-956D0D6A', 1, NULL, 0, NULL, NULL, '2026-01-13 17:18:17', '2026-01-13 17:18:17'),
(3, 'Osama Elazab', 'osama.elazab22@gmail.com', 'Quran', '+201146004550', NULL, NULL, 'us', 'Baraa', 11, NULL, NULL, NULL, NULL, 'My Deen Journey (Parenting System)', 'email', '2026-01-17', 'afternoon', NULL, NULL, NULL, NULL, 'My Deen Journey is a simple, child-friendly system designed to support parents in building consistent Islamic habits at home. You can track Salah, Adhkar, and good manners.\r\n\r\nParents can gently organize daily expectations, agree on meaningful rewards, and apply fair consequences — all within a clear, faith-centered framework rooted in Islamic philosophy.', NULL, 'pending', NULL, 'W14-28DCF9C2', 1, NULL, 0, NULL, NULL, '2026-01-15 19:45:20', '2026-01-15 19:45:20');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `page_title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `alies` varchar(255) DEFAULT NULL,
  `subject_id` int(11) UNSIGNED NOT NULL,
  `active` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `title`, `page_title`, `description`, `alies`, `subject_id`, `active`, `created_at`, `updated_at`) VALUES
(1, 'AA SL', 'Analysis & Approaches SL Questionbank', 'Exam questions, filtered by topic, sub-topic and difficulty\r\n', 'aa-sl', 5, 1, NULL, NULL),
(2, 'AA HL', 'Analysis & Approaches HL Questionbank\r\n', 'Exam questions, filtered by topic, sub-topic and difficulty', 'aa-hl', 5, 1, NULL, NULL),
(3, 'Grade 7', '', '', 'subject/language-literature/grade7', 1, 1, NULL, NULL),
(4, 'Grade 8', '', '', 'subject/language-literature/grade8', 1, 1, NULL, NULL),
(5, 'Grade 9', '', '', 'subject/language-literature/grade9', 1, 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `contact_us`
--

CREATE TABLE `contact_us` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` longtext NOT NULL,
  `email` varchar(255) NOT NULL,
  `reference` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `contact_us`
--

INSERT INTO `contact_us` (`id`, `name`, `subject`, `message`, `email`, `reference`, `created_at`, `updated_at`) VALUES
(1, 'ftyjghu', 'tyutyu', 'tyutyutyutyu', 'asmayyyyazakaria009@gmail.com', NULL, '2023-11-27 21:20:14', '2023-11-27 21:20:14'),
(2, 'Osama', 'Cc', 'Cc', 'osama.elazab22@gmail.com', NULL, '2024-03-11 18:48:50', '2024-03-11 18:48:50'),
(3, 'fdsf', 'sdfs', 'sdfsdf', 'asmaazakddaria009@gmail.com', NULL, '2024-03-11 23:43:08', '2024-03-11 23:43:08'),
(4, 'gg', 'currency inquiry', 'fgf', 'smosala@gmail.com', NULL, '2024-06-02 15:03:58', '2024-06-02 15:03:58'),
(5, 'Osama', 'My Deen Journey Support', 'ko', 'osama.elazab22@gmail.com', 'CNT-0C51B435', '2026-01-07 15:50:29', '2026-01-07 15:50:29'),
(6, 'Osama', 'General Inquiry', 'mj', 'osama.elazab22@gmail.com', 'CNT-58F7D377', '2026-01-07 16:10:55', '2026-01-07 16:10:55'),
(7, 'Osama', 'Quran Classes Inquiry', 'hello', 'osama.elazab22@gmail.com', 'CNT-CCA77CB6', '2026-01-07 16:41:46', '2026-01-07 16:41:46'),
(8, 'Osama', 'Billing And Payments', 'تع', 'osama.elazab22@gmail.com', 'CNT-EF0E5FFF', '2026-01-07 16:50:56', '2026-01-07 16:50:56'),
(9, 'TeacherUsama', 'General Inquiry', 'test', 'osama.elazab22@gmail.com', 'CNT-F49508B1', '2026-01-13 17:22:17', '2026-01-13 17:22:17'),
(10, 'Osama Elazab', 'Quran Classes Inquiry', 'tessssssssssssssssst', 'osama.elazab22@gmail.com', 'CNT-A3496727', '2026-01-13 18:08:52', '2026-01-13 18:08:52'),
(11, 'Osama Elazab', 'Quran Classes Inquiry', 'yeeeeeeeeeeeeeeeeee', 'osama.elazab22@gmail.com', 'CNT-6B1C27AD', '2026-01-13 19:02:09', '2026-01-13 19:02:09'),
(12, 'Osama Elazab', 'My Deen Journey Support', 'mm', 'osama.elazab22@gmail.com', 'CNT-A895BBCC', '2026-01-13 19:18:33', '2026-01-13 19:18:33'),
(13, 'Week14', 'My Deen Journey Support', 'm', 'osama.elazab22@gmail.com', 'CNT-B06C57A5', '2026-01-13 19:20:38', '2026-01-13 19:20:38'),
(14, 'Osama Elazab', 'General Inquiry', 'oo', 'osama.elazab22@gmail.com', 'CNT-3FD56518', '2026-01-15 11:48:13', '2026-01-15 11:48:13'),
(15, 'Osama Elazab', 'Quran Classes Inquiry', 'We will review your message carefully.\r\nWe’ll reply within 24 hours (often sooner).\r\nIf needed, we’ll recommend the best next step (trial class / assessment / program path).\r\nTo help us respond faster, please reply with your child’s age and current level (beginner / reads Quran / memorizing).', 'osama.elazab22@gmail.com', 'CNT-83F1DED9', '2026-01-15 12:06:23', '2026-01-15 12:06:23'),
(16, 'Osama Elazab', 'Quran Classes Inquiry', 'My Deen Journey is a simple, child-friendly system designed to support parents in building consistent Islamic habits at home. You can track Salah, Adhkar, and good manners.\r\n\r\nParents can gently organize daily expectations, agree on meaningful rewards, and apply fair consequences — all within a clear, faith-centered framework rooted in Islamic philosophy.', 'osama.elazab22@gmail.com', 'CNT-B0961E07', '2026-01-15 12:18:17', '2026-01-15 12:18:17'),
(17, 'Osama Elazab', 'General Inquiry', 'My Deen Journey is a simple, child-friendly system designed to support parents in building consistent Islamic habits at home. You can track Salah, Adhkar, and good manners.\r\n\r\nParents can gently organize daily expectations, agree on meaningful rewards, and apply fair consequences — all within a clear, faith-centered framework rooted in Islamic philosophy.', 'osama.elazab22@gmail.com', 'CNT-B38A2B09', '2026-01-15 12:19:04', '2026-01-15 12:19:04'),
(18, 'Osama Elazab', 'Quran Classes Inquiry', 'My Deen Journey is a simple, child-friendly system designed to support parents in building consistent Islamic habits at home. You can track Salah, Adhkar, and good manners.\r\n\r\nParents can gently organize daily expectations, agree on meaningful rewards, and apply fair consequences — all within a clear, faith-centered framework rooted in Islamic philosophy.', 'osama.elazab22@gmail.com', 'CNT-F0CB54BB', '2026-01-15 17:08:28', '2026-01-15 17:08:28'),
(19, 'Osama Elazab', 'Billing And Payments', 'My Deen Journey is a simple, child-friendly system designed to support parents in building consistent Islamic habits at home. You can track Salah, Adhkar, and good manners.\r\n\r\nParents can gently organize daily expectations, agree on meaningful rewards, and apply fair consequences — all within a clear, faith-centered framework rooted in Islamic philosophy.', 'osama.elazab22@gmail.com', 'CNT-2B91D45B', '2026-01-15 17:24:09', '2026-01-15 17:24:09');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `code` varchar(255) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `name`, `slug`, `parent_id`, `category_id`, `code`, `active`, `created_at`, `updated_at`) VALUES
(1, 'Kids Courses', 'kids-courses', 0, NULL, '01', 1, NULL, NULL),
(2, 'Adults Courses', 'adults-courses', 0, NULL, '02', 1, NULL, NULL),
(3, 'International Schools', 'international-schools', 0, NULL, '03', 0, NULL, NULL),
(7, 'General English Conversation', 'general-english-conversation', 2, NULL, '07', 0, NULL, NULL),
(8, 'Business English', 'business-english', 2, NULL, '08', 0, NULL, NULL),
(9, 'Advanced Conversation', 'advanced-conversation', 2, NULL, '09', 0, NULL, NULL),
(10, 'G6', 'g6', 3, NULL, '010', 0, NULL, NULL),
(11, 'G7', 'g7', 3, NULL, '011', 0, NULL, NULL),
(12, 'G8', 'g8', 3, NULL, '012', 0, NULL, NULL),
(13, 'G9', 'g9', 3, NULL, '013', 0, NULL, NULL),
(14, 'G10', 'g10', 3, NULL, '014', 0, NULL, NULL),
(15, 'G11', 'g11', 3, NULL, '015', 0, NULL, NULL),
(16, 'G12', 'g12', 3, NULL, '016', 0, NULL, NULL),
(43, 'ITLTS Preparation', 'itlts-preparation', 2, NULL, '017', 0, NULL, NULL),
(44, 'TOEFLibt Preparation', 'toeflibt-preparation', 2, NULL, '018', 0, NULL, NULL),
(45, 'Teacher Training\n', 'teacher-training', 2, NULL, '019', 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `courses_old`
--

CREATE TABLE `courses_old` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `code` varchar(255) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `courses_old`
--

INSERT INTO `courses_old` (`id`, `name`, `slug`, `parent_id`, `category_id`, `code`, `active`, `created_at`, `updated_at`) VALUES
(1, 'Kids Courses', 'kids-courses', 0, NULL, '01', 1, NULL, NULL),
(2, 'Adults Courses', 'adults-courses', 0, NULL, '02', 1, NULL, NULL),
(3, 'International Schools', 'international-schools', 0, NULL, '03', 1, NULL, NULL),
(7, 'General English Conversation', 'general-english-conversation', 2, NULL, '07', 1, NULL, NULL),
(8, 'Business English', 'business-english', 2, NULL, '08', 1, NULL, NULL),
(9, 'Advanced Conversation', 'advanced-conversation', 2, NULL, '09', 1, NULL, NULL),
(10, 'G6', 'g6', 3, NULL, '010', 1, NULL, NULL),
(11, 'G7', 'g7', 3, NULL, '011', 1, NULL, NULL),
(12, 'G8', 'g8', 3, NULL, '012', 1, NULL, NULL),
(13, 'G9', 'g9', 3, NULL, '013', 1, NULL, NULL),
(14, 'G10', 'g10', 3, NULL, '014', 1, NULL, NULL),
(15, 'G11', 'g11', 3, NULL, '015', 1, NULL, NULL),
(16, 'G12', 'g12', 3, NULL, '016', 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `course_translations`
--

CREATE TABLE `course_translations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `course_id` int(11) NOT NULL,
  `lang` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `course_translations`
--

INSERT INTO `course_translations` (`id`, `title`, `slug`, `course_id`, `lang`, `created_at`, `updated_at`) VALUES
(1, 'Kids Courses', 'kids-courses', 1, 'en', NULL, NULL),
(2, 'Adults Courses', 'adults-courses', 2, 'en', NULL, NULL),
(3, 'International Schools', 'international-schools', 3, 'en', NULL, NULL),
(4, 'كورسات الأطفال', 'كورسات-الأطفال', 1, 'ar', NULL, NULL),
(5, 'كورسات الكبار', 'كورسات-الكبار', 2, 'ar', NULL, NULL),
(6, 'المدارس الدولية', 'المدارس-الدولية', 3, 'ar', NULL, NULL),
(7, 'General English Conversation', 'general-english-conversation', 7, 'en', NULL, NULL),
(8, 'Business English', 'business-english', 8, 'en', NULL, NULL),
(9, 'Advanced Conversation', 'advanced-conversation', 9, 'en', NULL, NULL),
(10, 'G6', 'g6', 10, 'en', NULL, NULL),
(11, 'G7', 'g7', 11, 'en', NULL, NULL),
(12, 'G8', 'g8', 12, 'en', NULL, NULL),
(13, 'G9', 'g9', 13, 'en', NULL, NULL),
(14, 'G10', 'g10', 14, 'en', NULL, NULL),
(15, 'G11', 'g11', 15, 'en', NULL, NULL),
(16, 'G12', 'g12', 16, 'en', NULL, NULL),
(17, 'الصف6', 'الصف6', 10, 'ar', NULL, NULL),
(18, 'الصف7', 'الصف7', 11, 'ar', NULL, NULL),
(19, 'الصف8', 'الصف8', 12, 'ar', NULL, NULL),
(20, 'الصف9', 'الصف9', 13, 'ar', NULL, NULL),
(21, 'الصف10', 'الصف10', 14, 'ar', NULL, NULL),
(22, 'الصف11', 'الصف11', 15, 'ar', NULL, NULL),
(23, 'الصف12', 'الصف12', 16, 'ar', NULL, NULL),
(24, 'محادثة الإنجليزية العامة', 'محادثةالإنجليزية-العامة', 7, 'ar', NULL, NULL),
(25, 'الإنجليزية للأعمال', 'الإنجليزية-للأعمال', 8, 'ar', NULL, NULL),
(26, 'كورس المحادثة المتقدم', 'كورس-المحادثة-المتقدم', 9, 'ar', NULL, NULL),
(43, 'IELTS Preparation', 'ielts-preparation', 43, 'en', NULL, NULL),
(44, 'تحضير IELTS', 'ielts-تحضير', 43, 'ar', NULL, NULL),
(45, 'TOEFLibt Preparation', 'toeflibt-preparation', 44, 'en', NULL, NULL),
(46, 'تحضير TOEFLibt', 'toeflibt-تحضير', 44, 'ar', NULL, NULL),
(47, 'Teacher Training', 'teacher-training', 45, 'en', NULL, NULL),
(48, 'تدريب المدرسين', 'تدريب-المدرسين', 45, 'ar', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `course_translations_old`
--

CREATE TABLE `course_translations_old` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `course_id` int(11) NOT NULL,
  `lang` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `course_translations_old`
--

INSERT INTO `course_translations_old` (`id`, `title`, `slug`, `course_id`, `lang`, `created_at`, `updated_at`) VALUES
(1, 'Kids Courses', 'kids-courses', 1, 'en', NULL, NULL),
(2, 'Adults Courses', 'adults-courses', 2, 'en', NULL, NULL),
(3, 'International Schools', 'international-schools', 3, 'en', NULL, NULL),
(4, 'كورسات الأطفال', 'كورسات-الأطفال', 1, 'ar', NULL, NULL),
(5, 'كورسات الكبار', 'كورسات-الكبار', 2, 'ar', NULL, NULL),
(6, 'المدارس الدولية', 'المدارس-الدولية', 3, 'ar', NULL, NULL),
(7, 'General English Conversation', 'general-english-conversation', 7, 'en', NULL, NULL),
(8, 'Business English', 'business-english', 8, 'en', NULL, NULL),
(9, 'Advanced Conversation', 'advanced-conversation', 9, 'en', NULL, NULL),
(10, 'G6', 'g6', 10, 'en', NULL, NULL),
(11, 'G7', 'g7', 11, 'en', NULL, NULL),
(12, 'G8', 'g8', 12, 'en', NULL, NULL),
(13, 'G9', 'g9', 13, 'en', NULL, NULL),
(14, 'G10', 'g10', 14, 'en', NULL, NULL),
(15, 'G11', 'g11', 15, 'en', NULL, NULL),
(16, 'G12', 'g12', 16, 'en', NULL, NULL),
(17, 'الصف6', 'الصف6', 10, 'ar', NULL, NULL),
(18, 'الصف7', 'الصف7', 11, 'ar', NULL, NULL),
(19, 'الصف8', 'الصف8', 12, 'ar', NULL, NULL),
(20, 'الصف9', 'الصف9', 13, 'ar', NULL, NULL),
(21, 'الصف10', 'الصف10', 14, 'ar', NULL, NULL),
(22, 'الصف11', 'الصف11', 15, 'ar', NULL, NULL),
(23, 'الصف12', 'الصف12', 16, 'ar', NULL, NULL),
(24, 'محادثة الإنجليزية العامة', 'محادثةالإنجليزية-العامة', 7, 'ar', NULL, NULL),
(25, 'الإنجليزية للأعمال', 'الإنجليزية-للأعمال', 8, 'ar', NULL, NULL),
(26, 'كورس المحادثة المتقدم', 'كورس-المحادثة-المتقدم', 9, 'ar', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `alies` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `title`, `alies`, `image`, `active`, `created_at`, `updated_at`) VALUES
(1, 'PYP', 'e-books/pyp', 'front/images/2995459-1.png', 1, NULL, NULL),
(2, 'MYP', 'e-books/myp', 'front/images/3429417.png', 1, NULL, NULL),
(3, 'DP', 'e-books/dp', 'front/images/3429328.png', 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `user_name` varchar(255) DEFAULT NULL,
  `password` mediumtext DEFAULT NULL,
  `decr_password` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `meeting` mediumtext DEFAULT NULL,
  `active` int(10) NOT NULL,
  `login` int(10) NOT NULL DEFAULT 0,
  `level_id` varchar(255) NOT NULL,
  `gender` varchar(255) DEFAULT NULL,
  `age` varchar(255) DEFAULT NULL,
  `birthday` date DEFAULT NULL,
  `department` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `first_name`, `last_name`, `user_name`, `password`, `decr_password`, `email`, `phone`, `meeting`, `active`, `login`, `level_id`, `gender`, `age`, `birthday`, `department`, `created_at`, `updated_at`) VALUES
(1, 'Usama Azb', NULL, 'Osama_st01', 'eyJpdiI6InAvL3V6VHN3ODc2cjRGVFpwSDZnSHc9PSIsInZhbHVlIjoiRm9sZWM4MnlJSllsblMrNnJPR01xNk9XdTUwRlNIRm13Um01L2FKRmV0Zz0iLCJtYWMiOiJlOWZkMWE5MTZjNDJjYmM1ZmI1ZDg3ZDhmYTljZWYwYTE0MGU4ZTBkYTdiZDQ5YzVmOTFlYjU3YzZjYjRhMWY3In0=', 'Osama_852', 'osama.elazab22@gmail.com', '01146004550', 'نادي 15 مايو', 2, 0, '0', 'male', '35', NULL, NULL, '2022-08-14 18:22:14', '2022-08-14 18:22:14'),
(2, 'Nancy', NULL, 'nancy_St04', 'eyJpdiI6ImtBNTF2Ym5vc3NTem1KRU9KVW5ud0E9PSIsInZhbHVlIjoiZzJxc2xVdEtobnl3MktMRU1GenFWZz09IiwibWFjIjoiOGZkNjk0NGU3MDUxN2I1OTQyMmVjZjU3NTgyNjNlMmYxNjIzODA4YjRjZmMxNDYxYjMyODkwOGZiYWFjZTc1NiJ9', '0pDgrWcL', NULL, NULL, NULL, 0, 0, '', NULL, NULL, NULL, NULL, '2022-08-25 21:19:04', '2022-08-25 21:19:04'),
(3, '', NULL, '', '$2y$10$IQR7XC8MSR0QZD6KqNXbFOWt88w5vmhFy6lg5YZq9ymrbTGgoVNae', 'Heba_80579', NULL, NULL, NULL, 2, 0, '', NULL, NULL, NULL, NULL, '2022-08-25 21:19:04', '2022-08-25 21:19:04'),
(4, 'Heba Basiouny', NULL, 'hbasiouny83@gmail.com', 'eyJpdiI6IlNDemlkaTkwbksybDQvRGdFaUtENXc9PSIsInZhbHVlIjoiNjBaWHAzeWxHdlFRNlFXRGpiRksxdz09IiwibWFjIjoiYjNmN2U1ZWExZThkODYyZDEyZmUzNWU4YzQxZjQ2ZGE4N2JhNzkzZGFjMTQxN2JlN2Y5ODJjYWI3MTk2NmRiMiJ9', 'iYv5puvg', NULL, NULL, NULL, 2, 0, '', NULL, NULL, NULL, NULL, '2022-08-25 21:19:04', '2022-08-25 21:19:04'),
(5, '', NULL, 'User_301', 'eyJpdiI6IlAxUW1LaFdXMGNsMEJPWGZMVzVHUHc9PSIsInZhbHVlIjoidE5RTTEvbFhxdDRaekMwRG4xeG5Udz09IiwibWFjIjoiYzhiMTRiMTIzZTQ0Y2E1NDQyZmJmYzQzMjljNTcyYWE2OThlMGFiZGI5ZTZjNmM5YjlmYmRmZTdhODJkZDQwOSJ9', 'Bv5dWTHC', NULL, NULL, NULL, 0, 0, '', NULL, NULL, NULL, NULL, '2022-08-25 21:19:04', '2022-08-25 21:19:04'),
(6, '', NULL, 'User_353', 'eyJpdiI6IngyVm5nTmxsV2ROT3FHMExzdExsN2c9PSIsInZhbHVlIjoickRoOElxZ3B0bUh1MVI2bVd0TTZBQT09IiwibWFjIjoiZjQzYTc0YzIxMTk0YTI3OTM5ZjE0N2VkYmQxYmM5MWMzZWMwOGJkMDVjMWNjOGQwY2MyNjAwYjg3YzZlNDJjZiJ9', 'cZo0L3oh', NULL, NULL, NULL, 0, 0, '', NULL, NULL, NULL, NULL, '2022-08-25 21:19:04', '2022-08-25 21:19:04'),
(7, '', NULL, 'User_438', 'eyJpdiI6ImxUQWFONkU4bGhnb0ZuWXRjOHVaeWc9PSIsInZhbHVlIjoiN0NxVEh3UzBnSUlrUmJSLytzaW1tQT09IiwibWFjIjoiNzFlNmMxNDc4MWM1NjllYmIxNmNjNjVhMWQ3MmE3MzI0NjZmNzMyODg0OWVmYTc1ZGRlZjA0OWU4N2ZlOGVkMCJ9', 'alTnC8Gk', NULL, NULL, NULL, 0, 0, '', NULL, NULL, NULL, NULL, '2022-08-25 21:19:04', '2022-08-25 21:19:04'),
(8, '', NULL, 'User_402', 'eyJpdiI6Ik8vdjh0cExwbElLUUUxWmt4WWU2aVE9PSIsInZhbHVlIjoiUm1uVkVkb0RMbjN2alNncytjbFBndz09IiwibWFjIjoiYzU2MGYwNzdiMmE5ZmMyMTQ1M2Y2YWEzMDZmMTZmOWVjZDBlNmRiYmNiYzFiOTU2NGJjYTk1M2Q1MmM4YjU5OCJ9', '2Nlk3bV8', NULL, NULL, NULL, 0, 0, '', NULL, NULL, NULL, NULL, '2022-08-25 21:19:04', '2022-08-25 21:19:04'),
(9, '', NULL, 'User_400', 'eyJpdiI6ImlMY3kvS0NaMmJ6ZDcwYzlhRjZHeWc9PSIsInZhbHVlIjoiYm5NeEROVHdYWnVrTmNsRmprVEx2Zz09IiwibWFjIjoiZWEzN2YxYTYxZmNhY2NmNzZkZDc1Y2U1NDJiYTk0MWRlYjcxZDk0ODQ5MWQ0YTY5NDlmNTU2NmNkZTdmZjA3OSJ9', 'C8PeVRAm', NULL, NULL, NULL, 0, 0, '', NULL, NULL, NULL, NULL, '2022-08-25 21:19:04', '2022-08-25 21:19:04'),
(10, '', NULL, 'User_352', 'eyJpdiI6ImNWcnVtb0dKNEVNcTNyQnhFMGJEZVE9PSIsInZhbHVlIjoibnR5anZOM0NHZGcvSE5mWTRoMm1FUT09IiwibWFjIjoiMjQ0ZWJkNTI0ZjUzNmQwYzgyYzc3OTM1YTAxYzU5OTA1ZTliNWFjM2UyZTA3Njc2ZjZjMWY5ZmZmNGY2OTQ5OSJ9', 'YDcsFEcV', NULL, NULL, NULL, 0, 0, '', NULL, NULL, NULL, NULL, '2022-08-25 21:19:04', '2022-08-25 21:19:04'),
(11, '', NULL, 'User_484', 'eyJpdiI6Ik5KR1dTVi8rR0szVThGVW8yQ3NKbEE9PSIsInZhbHVlIjoib0RUNTRqa3dzU0tzZS8zMjEwd21ydz09IiwibWFjIjoiNjUzZDhkYmY1OTkyZjVjYmZlZmE2MDk5Y2RjMDVhMjc2ZTZhZjRmNDE1Y2NlZTBjOWViNWI4YjM3NjczNzM3YSJ9', 'qWizcY4P', NULL, NULL, NULL, 0, 0, '', NULL, NULL, NULL, NULL, '2022-08-25 21:19:04', '2022-08-25 21:19:04'),
(12, '', NULL, 'User_323', 'eyJpdiI6InNoTkdtNVhTT09FN1dGeXR3ZStVQ1E9PSIsInZhbHVlIjoiTFVvcjlXaGRNdDFSaFpobEpnQ2Z6QT09IiwibWFjIjoiMzk0NmNiZDM0ZmI5MzNlY2E4ZTc2YWNmMmY5NGVjYzhlMzlkZDNjNDlkNWUwOTgzMDkwNmVjNWQ2ZDZiNDhmZCJ9', 'gHhwLxHw', NULL, NULL, NULL, 0, 0, '', NULL, NULL, NULL, NULL, '2022-08-25 21:19:04', '2022-08-25 21:19:04'),
(13, '', NULL, 'User_328', 'eyJpdiI6ImF1b1NGMVA0WjFwREM2M2xXMjF4RVE9PSIsInZhbHVlIjoiM0JXOFF5amcvZEFvaXl4NHVIclVFdz09IiwibWFjIjoiY2NmYjcyYmY4Y2JlODlhZDlkNTk1YzVhYjdkZGE4NjJiNGYyNTA0OGUwMmU1YTYzZjAxMzNiMTRkODQ5ODBmYiJ9', 'XI0nSZzE', NULL, NULL, NULL, 0, 0, '', NULL, NULL, NULL, NULL, '2022-08-25 21:19:04', '2022-08-25 21:19:04'),
(14, '', NULL, 'User_446', 'eyJpdiI6ImZ2aWRjanZOdktyTkJEM2VYZnJYUFE9PSIsInZhbHVlIjoiZzJZN1ZOVGtoa2pyZWtieWhMMkRoZz09IiwibWFjIjoiMWI0NDRkMDhlYjhiNDBlZjY0MTNmMDZhZTAxMWVjZGNhYmU2ZGMxMTlkZTlkNTZmMTAxNTQ1MTk4OThlMDgyOCJ9', 'YvQmo4pE', NULL, NULL, NULL, 0, 0, '', NULL, NULL, NULL, NULL, '2022-08-25 21:19:04', '2022-08-25 21:19:04'),
(15, '', NULL, 'User_438', 'eyJpdiI6InpZVmhITUkyR1ZNVldyZFNvN0VNYVE9PSIsInZhbHVlIjoiLzdseCtucmhjdGFhR04yeHpVamJLdz09IiwibWFjIjoiYmY0ODUwYTgyZTk5ZWI3Y2Q4YzEyZWVmMGY3YTkyYWVlMmQyNDVlZmYyNzc1MDdlNjVhNDI3YzhmMDBiZTA0YSJ9', 'NvhbtAjI', NULL, NULL, NULL, 0, 0, '', NULL, NULL, NULL, NULL, '2022-08-25 21:19:04', '2022-08-25 21:19:04'),
(16, '', NULL, 'User_319', 'eyJpdiI6InhWK2JlRmVzNThqbHpWcTVwS21nT3c9PSIsInZhbHVlIjoiVjQwK2d0N05xZlNQWU9maCtTUnk0QT09IiwibWFjIjoiNzVjODQ2ODk0NGYxMWJjNWMyMjU1ODhhMTMwNDRmZDA4NGMwMDY4ZmEwYmI2YzIzNzQ1NGU5MjY1OTc3YTIyOCJ9', 'TOch6PL4', NULL, NULL, NULL, 0, 0, '', NULL, NULL, NULL, NULL, '2022-08-25 21:19:04', '2022-08-25 21:19:04'),
(17, '', NULL, 'User_458', 'eyJpdiI6InZsT0J0RkZZcjF6Y1lUQnpua0NRL2c9PSIsInZhbHVlIjoiUm9FRmVFR09naVNYNndzcE1xYnloZz09IiwibWFjIjoiMTViZDY1ZDM2ZDU0MzQyOWU0NjRiOWY2NDcwMzNlYzUyNzM4YzYwOTk4M2NmZmY1ZjgzYjBjZjE4NGJkNGM3OSJ9', '0WbpiNyx', NULL, NULL, NULL, 0, 0, '', NULL, NULL, NULL, NULL, '2022-08-25 21:19:04', '2022-08-25 21:19:04'),
(18, '', NULL, 'User_492', 'eyJpdiI6InJYdklTdjM0a05DV0hEK0dMNEswSFE9PSIsInZhbHVlIjoiSzRFWTUzb3hwVUMxZFJaVVNTK2svQT09IiwibWFjIjoiZTkxY2MzYzgxYzFmOGNiOGM3ODhiMTFhMDgyZmY4Y2FlYWYxNDg4Yjc1OTU4OWQ5ZWQ2ODQ2NzRjMzJjODU5MCJ9', 'aCDpMO6E', NULL, NULL, NULL, 0, 0, '', NULL, NULL, NULL, NULL, '2022-08-25 21:19:04', '2022-08-25 21:19:04'),
(19, '', NULL, 'User_362', 'eyJpdiI6Ik5nUUlsRU9KMlU5NW03clEzWmNwdWc9PSIsInZhbHVlIjoiOHJTajNOTForVG1iRkx3R0E3bzJIdz09IiwibWFjIjoiN2EyOTc0MGRlYjdkZGFmMGY0MTQzNTg3ZWVjMjcwYTYzZWY2NjEwNjZiOTcwMzMzNTY4YjgxODQxZDJjYzMzZCJ9', 'VHcfMLzc', NULL, NULL, NULL, 0, 0, '', NULL, NULL, NULL, NULL, '2022-08-25 21:19:04', '2022-08-25 21:19:04'),
(20, '', NULL, 'User_465', 'eyJpdiI6InFlN3djQnlsUFBwQjA0Y0lIaXExVEE9PSIsInZhbHVlIjoiQ242OWZlKzZqYmQ5V3hmYW1nWjZ0UT09IiwibWFjIjoiYzIwODJhMjYwY2QyOWUyNDQ0OWFmYWFhNDEzOGUzMGFhM2RhODM2YWRjY2Y1MTFmYzEwM2Y0MTkxYTVlMDAwNCJ9', 'XIYT8GEd', NULL, NULL, NULL, 0, 0, '', NULL, NULL, NULL, NULL, '2022-08-25 21:19:04', '2022-08-25 21:19:04'),
(21, '', NULL, 'User_447', 'eyJpdiI6ImZOczNDbG1lWnVwOGZBdXIrSGgyekE9PSIsInZhbHVlIjoicUk1My9VQkxET0U2enk1dlNabFFOZz09IiwibWFjIjoiYmZiMTkwZjM4Y2I1NTk4YzA3OGYwZjdjZWI2Yjc0Yjc3Y2FmNzJiYzk5YWY1OWQzNmM3ZmJkNzdjNzI4NGQ5OSJ9', 'qdmsJ16N', NULL, NULL, NULL, 0, 0, '', NULL, NULL, NULL, NULL, '2022-08-25 21:19:04', '2022-08-25 21:19:04'),
(22, '', NULL, 'User_379', 'eyJpdiI6IjI1VmxDbmhkY0lpZzNXMkVCcmp1NUE9PSIsInZhbHVlIjoiL20yMEZGNU9tRDFVMUM4SnVOc3BOdz09IiwibWFjIjoiOTZjNDQ3NTRkZGJiYTAxMWRmMTE2NGJiMDQxNjFiY2E5M2M2YmVjZmQ3YWVlNGI5OWU3NTJhZmE3YjYwODBjYyJ9', 'Fi8xJbPK', NULL, NULL, NULL, 0, 0, '', NULL, NULL, NULL, NULL, '2022-08-25 21:19:04', '2022-08-25 21:19:04'),
(23, '', NULL, 'User_478', 'eyJpdiI6IjRQNUx5L0xUaTJBbnBvdWRjd0U5aWc9PSIsInZhbHVlIjoiZ1pZb2ZhL0FkK2dHRlJiSERpUjVXQT09IiwibWFjIjoiN2Q3ZTJkYTc4NTdhZDkyYzA2OGM2ZTZmNmNlZTNjN2U0YTQ2ZTA0N2IzZTJmZmQ2OTQwMGFmZGNlNjE4YWJhYiJ9', 'CBDgcyWy', NULL, NULL, NULL, 0, 0, '', NULL, NULL, NULL, NULL, '2022-08-25 21:19:04', '2022-08-25 21:19:04'),
(24, '', NULL, 'User_396', 'eyJpdiI6Ikp5SFh3YTJuUjVqR2gxL1hDUmZGdXc9PSIsInZhbHVlIjoiZDNEcFdXRzd6RVZWclJ3YVNBVm5nZz09IiwibWFjIjoiMWNhYjU3ZjRjMzlkNzU5MGRjYTkyYTNjODIwMWI4NGExNmFiODAwYjY0NjY0OTE2Njg1NjlhODEzMThlMjgzOSJ9', 'aoBP7Smb', NULL, NULL, NULL, 0, 0, '', NULL, NULL, NULL, NULL, '2022-08-25 21:19:04', '2022-08-25 21:19:04'),
(25, '', NULL, 'User_392', 'eyJpdiI6InJyQXk3MlkrcUtIVE1CU1g0NmdkaXc9PSIsInZhbHVlIjoiWEVURnlWVTE4eisxaVp1Y25oWGtEZz09IiwibWFjIjoiOWQ1MzlmNDkxZWFkMWJmMjJmODMwNmFlZDEwZTZhYzA5Y2Y5Mjg3Y2NjM2Y1Mjg2ZDY2YzYxMGMwMGE0ZDk0YiJ9', 'BQYGcFZV', NULL, NULL, NULL, 0, 0, '', NULL, NULL, NULL, NULL, '2022-08-25 21:19:04', '2022-08-25 21:19:04'),
(26, '', NULL, 'User_413', 'eyJpdiI6IjBIR3VFR1Z2YUxKV2NiVmtrSlhrWUE9PSIsInZhbHVlIjoiZGFYTHJZZzdvN2ZEQUVTV1RuS1ZtQT09IiwibWFjIjoiZTIzNGJlMjg2MjEzOThmOTIxNzE4MTEzMjE0ZDhlYjUyZjFlNDQyZjc2ZWFmMzkyODcwZTI1MGUyNjk4YWFhNSJ9', 'eOazK8R4', NULL, NULL, NULL, 0, 0, '', NULL, NULL, NULL, NULL, '2022-08-25 21:19:04', '2022-08-25 21:19:04'),
(27, '', NULL, 'User_434', 'eyJpdiI6Ikt5WllWaTlhVkNnRUlRY2ZRMUlRWFE9PSIsInZhbHVlIjoiQUUvLzMzYzdOd1UvdXpOOEt0MTBwdz09IiwibWFjIjoiYmNjNTY1YTg4NGM0MjNjZWVkMDU1OGIwNTEwZmVhZTY3OWFlYWIzNmY5ZTQ3MzhlNDRlNWE2MjdjNDBiYjdhNiJ9', 'geGUYZBc', NULL, NULL, NULL, 0, 0, '', NULL, NULL, NULL, NULL, '2022-08-25 21:19:04', '2022-08-25 21:19:04'),
(28, '', NULL, 'User_381', 'eyJpdiI6InlxTFhsY1cyM3ZNMVYyQ1A3NmY4alE9PSIsInZhbHVlIjoiam55SUp2NnJqRXR2NGJXMFhTSkVtZz09IiwibWFjIjoiNmE4MTA2ZTllMTIwNDk3M2VmZTgwZDgzNjZiNmI3MDgxNDQ0NWMxNjNmYTc1ZWVlZTBiODU2NDEzZWUwMTllYiJ9', 'CJaSddkF', NULL, NULL, NULL, 0, 0, '', NULL, NULL, NULL, NULL, '2022-08-25 21:19:04', '2022-08-25 21:19:04'),
(29, '', NULL, 'User_425', 'eyJpdiI6ImY5aW1YZlJ1ZW5GZjVJYkUycThMYlE9PSIsInZhbHVlIjoiMnhtSHoycysvakNGVXdSclBuK3hzUT09IiwibWFjIjoiOGJmYmVkYjA2NjdmYWYzZWI0NjM4YmM1ZTFlMmM1MWE5OTQ5ZTU3Y2E3NmM5NDNlNTU1ZWY4M2I4YmQ0Y2Q3MyJ9', 'mFVZrXnK', NULL, NULL, NULL, 0, 0, '', NULL, NULL, NULL, NULL, '2022-08-25 21:19:04', '2022-08-25 21:19:04'),
(30, '', NULL, 'User_394', 'eyJpdiI6ImNHTEIveXFZZVN3YXBQdi9pMGFvQmc9PSIsInZhbHVlIjoiN0xYcFc2U1lWNjNlZnFRQ0l5NTJ2dz09IiwibWFjIjoiZDAzNWJiZTU2ZGU1NzQ4ZGU1Yzg1OThhNGE1M2E0MThjZjE1ZTJmODcxMzIxM2E2ZDU5N2I2OGY2MjkxNDhhZiJ9', 'ta0UzmQS', NULL, NULL, NULL, 0, 0, '', NULL, NULL, NULL, NULL, '2022-08-25 21:19:04', '2022-08-25 21:19:04'),
(31, '', NULL, 'User_373', 'eyJpdiI6Ijc3Qy9nVTRhSGZzTVdFMXNud0gvMlE9PSIsInZhbHVlIjoiNDJDYmY1b3pteFVYbnZiZ2k3Znk3QT09IiwibWFjIjoiZTVkZTk2Njk2ZDgxNDE4Mjg3NTJhY2JhZTc4N2Y1OTBmNjExMzE3MGNlN2FkYWUzOThiNzhiMmU3OGUyMTcwMCJ9', '3FOOQaCY', NULL, NULL, NULL, 0, 0, '', NULL, NULL, NULL, NULL, '2022-08-25 21:19:04', '2022-08-25 21:19:04');

-- --------------------------------------------------------

--
-- Table structure for table `employees_course`
--

CREATE TABLE `employees_course` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `user_name` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `decr_password` varchar(255) DEFAULT NULL,
  `active` int(10) NOT NULL,
  `login` int(10) NOT NULL DEFAULT 0,
  `level_id` varchar(255) NOT NULL,
  `gender` varchar(255) DEFAULT NULL,
  `age` varchar(255) DEFAULT NULL,
  `birthday` date DEFAULT NULL,
  `department` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employees_course`
--

INSERT INTO `employees_course` (`id`, `first_name`, `last_name`, `user_name`, `password`, `decr_password`, `active`, `login`, `level_id`, `gender`, `age`, `birthday`, `department`, `created_at`, `updated_at`) VALUES
(1, 'Hassan', '', 'st.hassan', 'eyJpdiI6IkoyTDF2TEFvYkJvVHE2T2ExQzkyL3c9PSIsInZhbHVlIjoiVzY1LzlIVUV5TU03b3VHVHpsY3JCQT09IiwibWFjIjoiYWE3OTViY2Q3Y2Y3ZTM0NTljNjUwMDhlMzQ4Y2Q4MzE3YWY0NzZkMDFmZmU3Y2FmY2Q5ZmY4ZGFhYTBlMGJmMiJ9', 'DetCcIW!', 0, 0, '', 'male', '46', '1974-05-15', 'General Manager', '2021-02-07 03:15:21', '2022-06-09 02:04:26'),
(2, 'Jehad', '', 'jehad', 'eyJpdiI6InE4MjkrLzA5NmVMenVZQXV1RXkxWXc9PSIsInZhbHVlIjoiMUE0ZUU1a21qUDhSZ1phRlhIc3JkQT09IiwibWFjIjoiYWQ3OGVlODA1MThmYzg2ODc2ZjU2YWE2MDg3ZGExZjQ2YTRmMTFmOWVmOThlNjFmYzExNWNhZDU0ZjZiNmY2NSJ9', 'o79WKBrZ', 2, 0, '0', 'female', '31', '1989-07-26', 'Office Administrator', '2021-02-07 03:15:21', '2021-02-07 03:15:21'),
(3, 'Abdulaziz', '', 'abdulaziz', 'eyJpdiI6InY2aDNBTEttZjd0dE54QTVXT1RBdEE9PSIsInZhbHVlIjoiQzh1TUQvdmEzelJKTDhRa00raWhPQT09IiwibWFjIjoiMjYxMWI3ZjNiOWIxNzhjMmVmMTFiMTE3NTZlMDQ2ZmJlZDhlNWIwNDY2N2M0YjJmNTM4OGZmYTc1MTc2YTE0NiJ9', 'ihT&CZye', 0, 0, '1,4', 'male', '39', '1981-10-28', 'Sales Manager', '2021-02-07 03:15:21', '2021-02-07 03:15:21'),
(4, 'Fidaa', '', 'fidaa', 'eyJpdiI6IldaTnBmQ1Qxeko3NmFjUDdDbTNqSVE9PSIsInZhbHVlIjoiemE3QXdrNzJHRlZMQXF5ZWJQT0REdz09IiwibWFjIjoiZDY3MTIzYWEyMTk2YzNkMDAxOTAxZGE4MTBhOWRlMzQ3N2EzMTI2ZTEwYjUwOTYwMzliZjYzNzlmZGZjNjNhMSJ9', '3Ng&ipQ4', 0, 0, '0', 'female', '28', '1992-08-15', 'Sales Assistant', '2021-02-07 03:15:21', '2021-02-07 03:15:21'),
(5, 'Fatima', '', 'fatima_moh', 'eyJpdiI6IlZXYjJtS3ZtYVVRbE1DYWpYUjNKTHc9PSIsInZhbHVlIjoiN3VXZTNoMWVWc3VITGt3UlhlT3RQZz09IiwibWFjIjoiNDc3YWZiMzZkNWFkYjdmNzRiYTk3NWZkYmQyOWRiNWQ1NTdmZGFmN2VjYTlmN2MzMDkzNTk2OTQ2MzBmNWJlMCJ9', 'zyWIhA#R', 0, 0, '0', 'male', '39', '1981-08-16', 'Sales Department', '2021-02-07 03:15:21', '2021-02-07 03:15:21'),
(6, 'Nisma', '', 'nisma_huseen', 'eyJpdiI6Inlyb2ZRTDg3ajliZFJHV1FYSHJJYmc9PSIsInZhbHVlIjoiamJXYmJ1UEgzV3lqdzlMblJZNy9aQT09IiwibWFjIjoiZTYzM2VkZjA3ZWY3OGQ0OTY2N2I0YTE5ODJjODUxZmY3YmU4YTM4ODQ1NGY1NjY0YTg1ZjRmODYxMzE2MTAwMCJ9', 'GyWq@d5t', 0, 0, '0', 'male', '41', '1979-12-21', 'Sales Department', '2021-02-07 03:15:21', '2021-02-07 03:15:21'),
(7, 'Shimaa', '', 'shimaa_maher', 'eyJpdiI6Ik9qclBZTEhmVHJWcVRCcVZKc2h1Unc9PSIsInZhbHVlIjoiR2hMZmJDUnM4Y05nSlVVV0FwSzhIUT09IiwibWFjIjoiNmJjMjA4Zjc5ZDljMjA4N2E5MmE2NDUxZjk1NGVlODdiZTQ4NjJhYmY3ZjVmY2RkZjcyMjE0ZGNiYmZkNTk2MCJ9', 'Dvbs8_0S', 0, 0, '0', 'male', '36', '1984-05-25', 'Sales Department', '2021-02-07 03:15:21', '2021-02-07 03:15:21'),
(8, 'Khaled', '', 'khaled_moh', 'eyJpdiI6Inlob1RjVkVVMHlrWU0wTmd4cUJ3WUE9PSIsInZhbHVlIjoiTGVMbDBWbG5jMzhPclpsSU9CUkc0Zz09IiwibWFjIjoiZWE5NDJjYTUzNjA1OTgwNDJmNjdkOGMwNjYyNDk2YWM0YmQ1MjQzNjczODEyNGJiNDI5NmJiM2YzMzE3YTFlZiJ9', 'eg32ZczC', 0, 0, '0', 'male', '39', '1981-10-18', 'Sales Department', '2021-02-07 03:15:21', '2021-02-07 03:15:21'),
(9, 'Somaya', '', 'somaya_eid', 'eyJpdiI6Ii8rdFdINU1LcXZqVkM0ckJvVGZ6d0E9PSIsInZhbHVlIjoiOGFXSEhFbkw3dEJGQ05KaWJFZjI5QT09IiwibWFjIjoiN2RiYzllMDA3NDQ1OGZlMDkwMGIwZDFhMTE3M2UzYTNjZDZhYTFjNzk0MTc3NjQ1YzEwY2YxZDVjMDc4ZTM2ZSJ9', 'w2_qjnf1', 0, 0, '1', 'male', '28', '1992-08-15', 'Sales Department', '2021-02-07 03:15:21', '2022-06-10 19:00:15'),
(10, 'Hasnaa', '', 'hasnaa_mah', 'eyJpdiI6Ilo4NGJsNi9oV0tjQWRtMVJZYU53RlE9PSIsInZhbHVlIjoiakdDUEdUbGJYNEowWldFd1VoYVY4UT09IiwibWFjIjoiMTBjNWFkMTE1NTVhMzczZDc5MTc1NjAwYmVlNGVkOTgyYWU4Njg5YzcwZjExOTRlMzY0MmM2Y2E5MDExOWFjNiJ9', '@ZCwj0uI', 0, 0, '0', 'male', '42', '1978-03-08', 'Sales Department', '2021-02-07 03:15:21', '2021-02-07 03:15:21'),
(11, 'Mohammad', '', 'mohammad_ali', 'eyJpdiI6Ijl4amRkQUZEbFZqSWtDaFJ1ZStwOHc9PSIsInZhbHVlIjoib25mOHZWN253ODR5NmtickNVd004QT09IiwibWFjIjoiZTM0MWZlNTdhNzI0N2E2N2UxNTBjNGNhZWVlMmVlYTJhODQzNjFhMTZlN2U5YWU1ZTIxZTJlZjE1ODYwMzI0ZSJ9', 'Rys9qjSG', 0, 0, '0', 'male', '32', '1988-06-21', 'Sales Department', '2021-02-07 03:15:21', '2021-02-07 03:15:21'),
(12, 'Samah', '', 'samah_saleh', 'eyJpdiI6Ik9ZTUM2VHFRMDh6T0Y2a1FwSEZjL2c9PSIsInZhbHVlIjoiUWMzTE1iQXdNdkpIbGJsVG03bU93dz09IiwibWFjIjoiNTA1YmVhNTllYWVhOGUyM2I4OTY2ZWY1NDI2NDc4NTlhNGUxNjIwNWE5MmE4NmEwYTJkODAxODdhM2FlODhjMyJ9', 's543DhfC', 0, 0, '1', 'male', '35', '1985-10-26', 'Sales Department', '2021-02-07 03:15:21', '2022-05-29 02:12:20'),
(13, 'Neema', '', 'neema_ali', 'eyJpdiI6ImtLRkRSbzNwR3k0UkMwVGVmWHZBeFE9PSIsInZhbHVlIjoiVlpnS1UyYnhBNDMwYTNCYVlTY0wydz09IiwibWFjIjoiZDQ5YmYxMjIzZjc0OTZlN2RiOTgwYWM1MDZiOThhMmMxNjU3ZTRjNTE5YjZiNjUwYjllM2JmNTQ5YTQ1YTBjNyJ9', '0dGUk7If', 0, 0, '0', 'male', '45', '1975-11-18', 'Sales Department- Marketing', '2021-02-07 03:15:21', '2021-02-07 03:15:21'),
(14, 'Hala', '', 'hala_ahmed', 'eyJpdiI6IkxNN0NXVDlXandEZFFXOGtablkxb2c9PSIsInZhbHVlIjoiVnVQQ0pQR1RJTTlkRGJKZXNoUXpBUT09IiwibWFjIjoiMjE0MDk0MWE1ZjhhNDM3NTgyMmRlZjJjYTg0MTc0NjYwMDNkODllYWExMjgwN2EzMjhlMDIwZjI2MDExNzMxMyJ9', 'kRgbKv&6', 0, 0, '1', 'male', '47', '1972-02-25', 'Finance & HR Manger', '2021-02-07 03:15:21', '2022-05-28 23:47:00'),
(15, 'Afaaf', '', 'afaaf_sher', 'eyJpdiI6IkhBQm0vRWFHMmFQOU01UWpJck41SGc9PSIsInZhbHVlIjoiR2lVMDVadXJZaStwZUd6bCtxRklxdz09IiwibWFjIjoiYTQyNDEwNzY0NDQ4YzAzNjc2ZThiODE5ZjFhOWZiMmNkN2YzZDgyMjFhNjA0ZTJjYzhlZWM1NTZiZDIzOWEyZSJ9', 'bLfndOM5', 0, 0, '0', 'male', '31', '1989-02-22', 'Finance Department', '2021-02-07 03:15:21', '2021-02-07 03:15:21'),
(16, 'Hoda', '', 'hoda_sawy', 'eyJpdiI6IjJid3h0T2FVaVZVT3diMmZLRmtkblE9PSIsInZhbHVlIjoiUjhvU3c3aWhWOHJXSFo1eG5tQWUwZz09IiwibWFjIjoiMTEzNGI0OThjMTU5YmQ2NWQ5MzhiZjJiYWM4YjMxMjEzZjg3YmMxMTRjMWUyZjg0ZGFhNmQ1NmJmMzc2ZTU5MyJ9', 'DpmHAdyt', 0, 0, '0', 'male', '35', '1985-06-01', 'Finance Department', '2021-02-07 03:15:21', '2021-02-07 03:15:21'),
(17, 'Shaimaa', '', 'shimaa_galal', 'eyJpdiI6ImxqSzNtd1lyY1k1MlJ5c3pULzkzWHc9PSIsInZhbHVlIjoiY25oT01JQzZOT3ZBOE9TOERoeVo4Zz09IiwibWFjIjoiMjAyODI0NzQxNDc0ZjI1ODExM2JkY2QxNjNkYmY1ZjRhYWJhMTNlNTk3YjhlZjAyOWUzMDdkNmE5Y2MzNDlmZiJ9', 'KwIqFM&x', 0, 0, '1', 'male', '24', '1996-08-26', 'Collectrors', '2021-02-07 03:15:21', '2021-02-07 03:15:21'),
(18, 'Heba', '', 'heba_abd', 'eyJpdiI6IlR3VGNETVkwbmdjaTdvSHplemNmR0E9PSIsInZhbHVlIjoiQTJ0NW5sK0lUY1dMNWdKUEhUM2JQZz09IiwibWFjIjoiMmMzZWU0YTZiMDA3M2MzYzQwYWQ3NDBhNzY3NDUxMGM0NDIyNjAxNWQ3ZmE3YTc2ZjcyODg0NTgzZWJhODFjMiJ9', 'GqK46cfI', 0, 0, '0', 'male', '51', '1969-06-20', 'Collectrors', '2021-02-07 03:15:21', '2021-02-07 03:15:21'),
(19, 'Hala', '', 'hala_mah', 'eyJpdiI6ImtzSWJUODJ5NENJMWdoQ2hoYVk2aFE9PSIsInZhbHVlIjoiSVV1RWllUTJ4V2VxT3d6RU41M1RJUT09IiwibWFjIjoiZWY5ZjIxNDkzOTAyMDAwNTM1NGQ2ODczNmNmZjk1YTBmMGI1NjI2MmIyM2Q3ZTIyODJjOTZhYTRiNzEzOWYwZiJ9', 'FWJ0KU6X', 0, 0, '0', 'male', '32', '1988-04-12', 'Collectrorst', '2021-02-07 03:15:21', '2021-02-07 03:15:21'),
(20, 'Rasha', '', 'rasha_halem', 'eyJpdiI6Imx5VkVqTko2WXRmSnAwelJQdjhqelE9PSIsInZhbHVlIjoieFJ5TFZWRnlnV1BJekpJMEJxZ0dwUT09IiwibWFjIjoiOWUwMGIzODY3MzUzOTBkOTc4NzU4OGIwNzQ0ZjVjZTJiZGEyOTIyMTMyNWMzYWFjNjAxYTlhYzhkNjJmN2E0MiJ9', 'H19IpAUt', 0, 0, '1', 'male', '50', '1970-03-04', 'Supply Chain Manager', '2021-02-07 03:15:21', '2022-06-18 01:04:53'),
(21, 'Amal', '', 'amal_salah', 'eyJpdiI6Iit2Uitha05IQmQ3blhmVC9WY1BjZFE9PSIsInZhbHVlIjoiTE5EdzNIQzZ3ZDB5ZGFtcDFNS1RwQT09IiwibWFjIjoiNDUxOGMzNjRkMWUyMWJjZjZiZmRmMjdmYjJmNzQzZjcwZmRkOTczYTg4NTY4ZGUwYTMyZGU4ZGQyZWNiNGM3ZiJ9', 'i7125clp', 0, 0, '0', 'male', '32', '1988-10-01', 'Supply Chain Department', '2021-02-07 03:15:21', '2021-02-07 03:15:21'),
(22, 'Norhan', '', 'norhan_moh\r\n', 'eyJpdiI6IkE4bUZLdFB4K3hDUGRsVUdKMitpU2c9PSIsInZhbHVlIjoiQTdsOWMwSVcxdm95SW1RN2hCOVpzZz09IiwibWFjIjoiYjZkYjE4NjA0YjkyNGE5MGQxOTliZTRhOTZmNTAxMWIyOWIxYThiZTMyOTliZWE4MTJkZGQ4YjczOTYxMjIyYyJ9', 'PYM&NSfI', 0, 0, '0', 'male', '31', '1989-12-05', 'Supply Chain Department', '2021-02-07 03:15:21', '2021-02-07 03:15:21'),
(23, 'Ahmed', 'Ewais', 'ahmed_ewais', 'eyJpdiI6IlYyUnRtNThCK2YzMHRoR01tSDRTNkE9PSIsInZhbHVlIjoid2FqZWEyZWVQY1lJQlFEaHdQc2pEUT09IiwibWFjIjoiMjI5YzNkNmZlMjI2MWU3YjMwY2U0YTUwYmI2MWI4NjMyZGQ0MjM4YzNhMzNmMTFiNjk2NDkyZjhjNzkzMTNhNCJ9', 'vS3B!Mka', 0, 0, '0', 'male', '26', '1994-03-08', 'Supply Chain Department', '2021-02-07 03:15:21', '2021-02-07 03:15:21'),
(24, 'Walaa', '', 'walaa_abd', 'eyJpdiI6Ii94V3RqRC9jL2tkSXdwMU9oZ1FXWEE9PSIsInZhbHVlIjoidGpJTGZLUHh2Y2FuU3BTSjBQQlRIZz09IiwibWFjIjoiODVmOGQ0YTNkYjMxMTNlNDFhNDk1MmJiMjdhYjk4MTFmMWNjZGJmMzM0ODg5NjNiMWQ5MDFmNWFlZTg0OTBmNCJ9', 'zjwqdU5T', 0, 0, '0', 'male', '40', '1980-11-09', 'Supply Chain Department', '2021-02-07 03:15:21', '2021-02-07 03:15:21'),
(25, 'Faten', '', 'faten_moh', 'eyJpdiI6IklFYllCNzNuUHlEQ1pWYjU5VUZld1E9PSIsInZhbHVlIjoiTzdPSFlrQXEzZnZYeU5aTlhHUUZqdz09IiwibWFjIjoiNWE3MjA0MjViZjQxY2IyY2I3Zjg0ZTAxNzMwYzI3Y2NjZTM4MGQ0ZGJjODY4NWY0MDNmZTA4OWVmYTY0OTRhYiJ9', 'zZ3SHlcm', 0, 0, '0', 'male', '35', '1985-08-25', 'Supply Chain Department', '2021-02-07 03:15:21', '2021-02-07 03:15:21'),
(26, 'Mohamed', '', 'moh_khleel', 'eyJpdiI6ImhndFhYY3hvNm5vNWx1THBQZlpFK2c9PSIsInZhbHVlIjoiME5uclF5M0lvRlV2SmtLUHBnV1VXUT09IiwibWFjIjoiZGQ0N2FmODBlZjA0MWMwOTFlNGY3M2YwYzNlNWU4YjJhMzQ3ODFhZDA2NTUwYTJjZDA4NGJkODJkNDAxZjIyZCJ9', 'RImLrtq3', 0, 0, '0', 'male', '54', '1966-09-24', 'Service Manager', '2021-02-07 03:15:21', '2021-02-07 03:15:21'),
(27, 'Doha', '', 'doha_nasr', 'eyJpdiI6ImZ0R2h4aVFNTWk4QjFtRE1SOFMvU2c9PSIsInZhbHVlIjoidTByb01tY1FTWmhmOSswQTltdDZadz09IiwibWFjIjoiNTA1OTMzOGJkYWU5NGM2OWIzYjY2YzNiNDlkMDRlMDRjNDEzMDYzM2E1Mjk3YjlkMDgyYjRmODY5NWUxZGFmMiJ9', 'c8@dZC&w', 0, 0, '1', 'female', '31', '1989-10-18', 'Service  Assistant', '2021-02-07 03:15:21', '2022-06-08 01:47:06'),
(28, 'Sara', '', 'sara_samir', 'eyJpdiI6InpIVmdFVmlodVF5d0dob3RNTy9TQmc9PSIsInZhbHVlIjoiQ1Rsd2dGVVlGM2FsZm5JaGhBV1Nydz09IiwibWFjIjoiNTE4Y2VlNTZhNzUzNWIyMWMyMzViMzRhZTAzODZmMmQzMGZhODQyY2QzZDU1MDgzZDA2NDkzOWM0NDNmMjAyMyJ9', 'ChUHieJK', 0, 0, '1', 'male', '40', '1980-05-27', 'Service - Customer Support Team', '2021-02-07 03:15:21', '2022-05-30 02:22:31'),
(29, 'Hossam', '', 'hosam_zky', 'eyJpdiI6IjRrV3lQcTVuRVlYMzhtTUVXNUtrdkE9PSIsInZhbHVlIjoiU1p4eGZjN1ZRejBOVklobGZLVGw0Zz09IiwibWFjIjoiMTIzNjQ0M2U2YWU2YTg5YjE5YTlkZDBhMWY2ZDYyNjQ0MTg1NjAzNmFiMTg5NWJiNjVhMWJlYjkxZmJhMjU0ZiJ9', '7H4&oNrQ', 0, 0, '0', 'male', '29', '1991-06-03', 'Service - Customer Support Teamt', '2021-02-07 03:15:21', '2021-02-07 03:15:21'),
(30, 'Magda', '', 'magda_abd', 'eyJpdiI6InBoeXJXU1YxSFpYbTM2KzFCYWRUK3c9PSIsInZhbHVlIjoiZTh2cS83dE1BY2d1UUh5bkd4RjVIZz09IiwibWFjIjoiZjJlYzRlNjk3NTFlZTcxYTFmOTNmY2Q4ZDEyMWUwOTEzMjdiZDZhMDIxZWNlYjM3NjdlN2FlZGZmNTk5ZGFmZCJ9', '&hZYUOTp', 0, 0, '0', 'male', '28', '1992-03-27', 'Service - Customer Support Team', '2021-02-07 03:15:21', '2021-02-07 03:15:21'),
(31, 'Rowda', '', 'rowda_akrm', 'eyJpdiI6Im16bWVpUWdhRDA4Z1JyUjRoZ0RpTFE9PSIsInZhbHVlIjoiY211WGMrei9FdDRscHZic0x4Zmlidz09IiwibWFjIjoiNWQxYThmMDE3N2Y5MTE0ZGY2MWQ4ODM5MGM0OWMxNmFlZjliY2Y2OTEyMDRlMjMxYmEzNzFkNDRlMGNjYTdhOCJ9', 'FBGoq8fR', 0, 0, '0', 'male', '30', '1990-06-01', 'Service - Customer Support Team', '2021-02-07 03:15:21', '2021-02-07 03:15:21'),
(32, 'Hager', '', 'harger_moh', 'eyJpdiI6IjVjb0g0eW1QTmFwUDNjUXRGNXdNemc9PSIsInZhbHVlIjoiY2lpMVhhMFB3bVVLYi9Ta25oclNIQT09IiwibWFjIjoiODgzNzE4NDhmNzBmOTQ5ODZmZjQyNzYwMzFlZjkwODBkM2U4NjZmNTdjYzcxZGFmM2U3YzlkNGE3NGIyMjMyOSJ9', 'SXK7VwvF', 0, 0, '1', 'male', '29', '1991-10-01', 'Service - Customer Support Team', '2021-02-07 03:15:21', '2022-05-27 19:04:14'),
(33, 'Aya', '', 'aya_sayed', 'eyJpdiI6IlJONUkvRkhHRTB4aklhNVVmWVpiRkE9PSIsInZhbHVlIjoidGRYQVplYWNWZU1yQUxGam9LVXF5dz09IiwibWFjIjoiNDY4Y2U4MTVkZjg0YmZiOWRmMGFlNTg1ZWFhMjk5M2Y0MDk0MmE0YWJmYjI1ZGFjY2MxZGE2MzJhZjU0MjBiZCJ9', '_umcze5i', 0, 0, '0', 'male', '31', '1989-03-03', 'Service - Customer Support Team', '2021-02-07 03:15:21', '2021-02-07 03:15:21'),
(34, 'Alaa', '', 'alaa_ahm', 'eyJpdiI6Ii8xdjRTQXNtUi9MWWJMWXpuSFpZcGc9PSIsInZhbHVlIjoiZ05UWVY4VS96TjdDMWVUTmlBQVVBdz09IiwibWFjIjoiZmJiYTU4OTI5NDI4OGQ1YzZkZjIwN2YxMDJiZjkzZjMyZmUzMzU2OGJjZDY1MGFlOGQ2NGNmY2ZkODZkNDU4OCJ9', 'SwaOgzNy', 0, 0, '0', 'male', '37', '1983-02-23', 'Service - Customer Support Team', '2021-02-07 03:15:21', '2021-02-07 03:15:21'),
(35, 'Mokhtar', '', 'mokhtar_moh', 'eyJpdiI6IjlYMTVoWmRiSkRXZU5EOEhZMTAwMEE9PSIsInZhbHVlIjoiTUNLZTV2N1lySkRPVTZnRkNjdzc1UT09IiwibWFjIjoiYTk5ZDYwY2QxZTA3ZWQ1OWU3YjdjYTA3OGE5ZTNiNmMxMTY5ZjAwMDc1NDQ5ZGMxYzA5ZmNmMjRmOWU5ZTRmZiJ9', 'I#Q_uioT', 0, 0, '1', 'male', '33', '1987-06-06', 'IT Administrator Department', '2021-02-07 03:15:21', '2022-06-18 01:07:14'),
(36, 'Afaaf', '', 'afaaf_younis', 'eyJpdiI6IjhuaDhuK0FzYW5DS3pubkMrd0ZheFE9PSIsInZhbHVlIjoiaEI5cExDWklFbi9DTXhOSVNJS1Rpdz09IiwibWFjIjoiZjVjODE5YjI2ZmI5ZjBkZDc3YmU3OGE2YjcxZWE1YTVhNzBkMjQxNTE4NjMxYjIxMzk0ZTE2MjlhNTQyNTYxYiJ9', 'kgYu@S1E', 0, 0, '0', 'male', '34', '1986-08-28', 'Office Boy', '2021-02-07 03:15:21', '2021-02-07 03:15:21'),
(37, 'Rania', '', 'rania_bnd', 'eyJpdiI6ImdqRE1sNU5WeS9raTFhNWs2aWFUM1E9PSIsInZhbHVlIjoid045MWxxek1pZmoyZFA0NlhLdTRpdz09IiwibWFjIjoiNTcyMjY5ZmM0ZGRmZDdkMTUzM2YwZjQwNTNhZDgwNzNlYWM5N2RiMjZhN2UzMjhjMDIwZGZlYjc1MWQ5MzYxZCJ9', 'PAzGgEy_', 0, 0, '0', 'male', '35', '1985-11-19', 'Office Boy', '2021-02-07 03:15:21', '2021-02-07 03:15:21'),
(38, 'Esraa', NULL, 'esraa_salah', 'eyJpdiI6ImlJNXV1L3dzeEdHditZRlRZbVRCV3c9PSIsInZhbHVlIjoiNlpXRWZXS0pyK2RhQVpuMmJ0YmRWUT09IiwibWFjIjoiN2FiODI0YzJlNWY1NzA1NmQ5OGY4NWE0OTkzNGNiZDAxYzJkOWNhNTcwNjQwY2NlYjRkMGJhY2UyZWFmYTZjMSJ9', 'eyHVDdwK', 0, 0, '0', NULL, NULL, NULL, NULL, '2022-05-18 02:50:15', '2022-05-18 02:50:15'),
(39, 'Yousif', NULL, 'yousif_hamed', 'eyJpdiI6Img5ZnhwQTFBU2UwV1c2c1BHcmIwWlE9PSIsInZhbHVlIjoiSTNyVnkrN0ZZTndXQjVmT1JJWVZiQT09IiwibWFjIjoiOTc2Y2EyN2EyMTIwYTBkMTQ3ZDZkOThhNTZjZDE0Y2NlZDVhZTk0MWIyNDM2NThlNWIzNWMzNzc5M2M0NjI0YSJ9', 'CcqHwWPF', 0, 0, '0', NULL, NULL, NULL, NULL, '2022-05-18 02:50:15', '2022-05-18 02:50:15'),
(40, 'Esraa', NULL, 'esraa_lwizy', 'eyJpdiI6ImZNb3J0RVJFbjljZnpHQTFrcTl4aEE9PSIsInZhbHVlIjoiUW4vWjUxalJEdU90N0EyY1E5ZVJEdz09IiwibWFjIjoiZmQ2M2RmYzZmMDg4MWVlMmZhMDQ0MTI2OTdmNzNiMDU3NjJjMWQ0MmE3YjY5NjJkYjk3MzU0ZmZkM2Y4ZDZjNCJ9', 'bMCKvZdD', 0, 0, '0', NULL, NULL, NULL, NULL, '2022-05-18 02:50:15', '2022-05-18 02:50:15'),
(41, 'Esraa', NULL, 'esraa_magdy', 'eyJpdiI6IjNQSWpIYTNKbVlQZ1dXZTMwd2oxMXc9PSIsInZhbHVlIjoiSjRmYzZxNTBPYkhsUlpQRVJsVmxNZz09IiwibWFjIjoiYTc2ZWRmZmUzODI5N2YyZGQ5YWM0ODY1M2M3YmQ0MjQ2YjNiZTlmZTI3MjI0YmQwY2E0N2M4ZTk1ZDA1N2UwZiJ9', 'rLFKmpSw', 0, 0, '0', NULL, NULL, NULL, NULL, '2022-05-18 02:50:15', '2022-05-18 02:50:15'),
(42, 'Youmna', NULL, 'yomna_ibr', 'eyJpdiI6IlYzdE1xRDJqY0NLRkZCU2VqbjlNRkE9PSIsInZhbHVlIjoiVWFDS3paRXllMkw5SUVBNGlCcTFldz09IiwibWFjIjoiODI5NGYxNTkwZWI5NjNlMzkzMThhYWFmOWM3ODNjMGIzM2Q5YjY2MmRhMTkxYzhmNGIwNTk1MDU4ZmJjMWU0NSJ9', 'kqnGbifn', 0, 0, '0', NULL, NULL, NULL, NULL, '2022-05-18 02:50:15', '2022-05-18 02:50:15'),
(43, 'Madeeha', NULL, 'madeha_abd', 'eyJpdiI6IlJqUHFwVXA1Sll0ekpYNUsycVJnZEE9PSIsInZhbHVlIjoiZlM5bFhtSTFPRkpaM3FZNHd0cmEvQT09IiwibWFjIjoiMGY1NDViOWJmNTlmZGY3ODhmNGNjZGVhYWFmYmQ5MDk1YmU0M2ExNjBhYzdiMTFhYTcyYTFiY2Y1ZjJmMTdjNiJ9', 'bUuszcgb', 0, 0, '0', NULL, NULL, NULL, NULL, '2022-05-18 02:50:15', '2022-05-18 02:50:15'),
(44, 'Fatima', NULL, 'fatima_zinhom', 'eyJpdiI6InlmdVlPMGQrOXY1SE1nRDdGWFg2eFE9PSIsInZhbHVlIjoiZVNqclBhYVFTZ2cyajVLMG85T3Q1dz09IiwibWFjIjoiZDE2NWE1NGE2YmQwY2VmN2U2ZmE1YzRkYTkyOWY4MmRmZmVkNDFhMTJlODkxZjQ2M2MyNzhhZmJmOTg4MjI4ZSJ9', 're5JN7xR', 0, 0, '0', NULL, NULL, NULL, NULL, '2022-05-18 02:50:15', '2022-05-30 01:37:51'),
(45, 'Mona', NULL, 'mona_soli', 'eyJpdiI6IlVLZGNSL3RyNHZzY2R3Z0NMZUp5Nmc9PSIsInZhbHVlIjoiT2pZZDRMbnhEd2lyVmlpdE96Y2szQT09IiwibWFjIjoiMjdkOWUxNGEwNDZjMGU5OWYwMjQ2YWNjMjIxYTQ1MWE5NmUxMGEyYzIzMDUxOTU3OWU2MTdhNzFiMDQyODdkYyJ9', 'bkYCRtz7', 0, 0, '0', NULL, NULL, NULL, NULL, '2022-05-18 02:50:15', '2022-05-18 02:50:15'),
(46, 'Aysha', NULL, 'aysha_salah', 'eyJpdiI6Ilc0SlpGMjZUaS9sWGhSZktiSXV4TWc9PSIsInZhbHVlIjoiOUd4MCtVYThySXFwYkYwa3g2ZS9VUT09IiwibWFjIjoiNWU1MjZjNjk0NGIxNzYwNDI4NzAyZjE4YjcwMDUyYjI1ZTg1MDYzOTlhMTg2MzVjNTFmOThjMjc3ZDIxY2JlYiJ9', 'xyDVutxL', 0, 0, '1', NULL, NULL, NULL, NULL, '2022-05-18 02:50:15', '2022-05-27 18:42:24'),
(47, 'Abdulrahman', NULL, 'abdul_shahin', 'eyJpdiI6InVRSk0zOE1BVUxteXJjRFdhdGdkVUE9PSIsInZhbHVlIjoibVBPc0ZrcmZ0MEkvbDV6TmtIVXBVZz09IiwibWFjIjoiN2NkZTUxODc2NTE2NDIyZWNiNGMyYTlkOTZhNjBhOTgxMjI5MDU1MDUzOGI0MDVlNDQ0YWUxN2RlNzY2OWFkNSJ9', 'dQoUCEJ2', 0, 0, '1', NULL, NULL, NULL, NULL, '2022-05-18 02:50:15', '2022-05-27 19:07:55'),
(48, 'Amira', NULL, 'amira_abd', 'eyJpdiI6IjFwaXluL3lRaDdNSHdCTktJRXZYaWc9PSIsInZhbHVlIjoiTFBGbmF6RTVNYTZCcHBTeFNjVzFrdz09IiwibWFjIjoiMWUwNDY5MTc4MzFiYWE1M2I3YzNlYjUxMjVmYmEyMDdjZjU5ZDc3Y2VlODI4NDE2OTkxN2I5ZTU5YjYzYTA0MCJ9', '4g6kdkf3', 0, 0, '1', NULL, NULL, NULL, NULL, '2022-05-18 02:50:15', '2022-05-31 00:40:26'),
(49, 'Abdullah', NULL, 'abdullah', 'eyJpdiI6IlY2TzBYMUY4STlMOHV1eGlzbVhUbGc9PSIsInZhbHVlIjoieFN2MU9tbHY4YlgyRWlLQXhWRzZaZz09IiwibWFjIjoiMjM3YTViMTllNGU5MTY2NjM0M2RkMGUwODRjYWYyNTMzZTZhNTg5YWRlZjhmMTg2NzdkYjVjYWY4MzNhMzFiOSJ9', 'Tw5zzI8F', 0, 0, '1,4', NULL, NULL, NULL, NULL, '2022-05-18 02:50:15', '2022-06-12 21:30:24'),
(50, 'Badr', NULL, 'badr', 'eyJpdiI6Im9TTDJRY2FUTHhWb2xzdUlZc3ZFQ0E9PSIsInZhbHVlIjoiQlJ3WWplRmFhanhuODlxc3ROZGYyUT09IiwibWFjIjoiMTliZWY5ZDU5MjY5Y2FiOGU5ZjU2NTcxODhlMzI3OTA4ZDZlM2M0MThlZDQ5NjdiOGQzNDllMjlmZmM3NWMyMSJ9', 'fwmfkqaj', 0, 0, '1,4', NULL, NULL, NULL, NULL, '2022-05-18 02:50:15', '2022-06-09 05:06:24'),
(51, 'Abdulazeez_2', NULL, 'abdulazeez_2', 'eyJpdiI6IlVkVVJ1ZlFUY3NzWGpsRjI1Q2x4bGc9PSIsInZhbHVlIjoiSEY0VTVQUFRNQzlMSUFSWDVwTEQwdz09IiwibWFjIjoiMGIzMGRiMzViMzY0MGM1NDljOWMwODc0Y2E5YzdiNjdlODVlMmI0ODI4NTdmNzA5NTE5NTUyN2VhZDEzN2RiOSJ9', 'HaLMC0Ef', 0, 0, '1,4', NULL, NULL, NULL, NULL, '2022-05-18 02:50:15', '2022-06-21 06:01:20'),
(52, 'Abdullah', NULL, 'abdullah_2', 'eyJpdiI6InJHdEdQUE8xNFlaMXdMQ3Y2SG8wcWc9PSIsInZhbHVlIjoiMUY2dWhVTUEyYnN1Z3JKU0hIUCtnQT09IiwibWFjIjoiYmIyY2ZhMWE4NGEzMWU4MWQ4ZDYxNDg4MDBmYjliNjRiZDZiOTdiOTJjMTNiNDNlZjgyNjFmNzMyZTIwOTE0YyJ9', '4wjf9Fi7', 0, 0, '1,4', NULL, NULL, NULL, NULL, '2022-05-18 02:50:15', '2022-06-12 20:22:16'),
(53, 'Amr', 'Ayman', 'amr_ayman', 'eyJpdiI6Inp1dnpnejlxeHRwY3pORk5LeEJmVVE9PSIsInZhbHVlIjoienRPWE9XaUVwRGVqQ1NudVVVV0NqUT09IiwibWFjIjoiZTY4NDIxNTQ1OTcxNjA1MjY3MDYwZTVmMjRlNWZlMjE2MzllNzE4ZjJlZDQzZWVmMTU3ZGQ3YzMwNGUxYmIzMiJ9', 'MzcI5Kt3', 0, 0, '0', NULL, NULL, NULL, NULL, '2022-05-18 02:50:15', '2022-05-18 02:50:15'),
(54, 'new', NULL, NULL, 'eyJpdiI6InNoSEpmVTVYdXFPRm91ZE9nY0xseFE9PSIsInZhbHVlIjoiZWpEdmdmVXo1NHlJWHJ3cFdRV3ByZz09IiwibWFjIjoiNzA2NjBlNDE5NGUwMWZkMTkzNGY1N2ViMzEzZWI2ODkxZThkNzE5NGZlY2VmZjZiYWI5Y2Q1ZDE0ZTc2NjFjZSJ9', 'AuM4y5PL', 0, 0, '0', NULL, NULL, NULL, NULL, '2022-05-18 02:50:15', '2022-05-18 02:50:15'),
(55, 'new', NULL, NULL, 'eyJpdiI6IlIyTkE2QVhTbTFDS0Q3clQzSHd0V1E9PSIsInZhbHVlIjoibXlwRGxxQko3ZkhDd3AwKzdMQU5jZz09IiwibWFjIjoiZmRmZjFjZDRlZTk2ZWQ0YzE2NWVlYWI4ZjdmZGE3MjA0MWRkZmNhZjNmZWEyODFiNTI3YzNlODAwYjU5MDI5OSJ9', '6YJDYZBQ', 0, 0, '0', NULL, NULL, NULL, NULL, '2022-05-18 02:50:15', '2022-05-18 02:50:15'),
(56, 'new', NULL, NULL, 'eyJpdiI6Iml3SmVSSEIrMXpRNFZBbG5iQXFvV2c9PSIsInZhbHVlIjoicUw2M3BQTHRhWXZCd1g3Mm5VaFNFQT09IiwibWFjIjoiMjYyODgxYzA1MjNjMGQ2MmEzY2ZjYmY0OGVjNGZjNzg5OWFmZmRlNmJhODUyMWZiNmUwMTQzNTMyMzg1YmEzNCJ9', 'akxJdfQr', 0, 0, '0', NULL, NULL, NULL, NULL, '2022-05-18 02:50:15', '2022-05-18 02:50:15'),
(57, 'new', NULL, NULL, 'eyJpdiI6ImNvSlBuU2dRY0RaOUQyby9CeFh5UGc9PSIsInZhbHVlIjoiTmJJbW9CajVTNzFpN3oxVDc0T1REdz09IiwibWFjIjoiOGQwOWM4OGZjZjRiODJkYzBiMDFmYzcyZDUxZDc1MDc5OWI0ZGFhNjYwOTNmMjQ4YTUzZjNlZWNiYjE1NDI3ZiJ9', '2UggZxqc', 0, 0, '0', NULL, NULL, NULL, NULL, '2022-05-18 02:50:15', '2022-05-18 02:50:15'),
(58, 'new', NULL, NULL, 'eyJpdiI6IkxmMEl6RWVlcFNzZnVydUVEdFhMR3c9PSIsInZhbHVlIjoicVhRYUp3NTczNXlXZ1dORSttbkwrQT09IiwibWFjIjoiYjU2OGJhM2YzOGU3ZGFlZGVjYmUzNzM4YTBmYjQ3OWU4ZWFhMzdmZWMwMTAzOTI4ZjZmZjQ2MTMzYTMyZTQ1OCJ9', '55tuyRZZ', 0, 0, '0', NULL, NULL, NULL, NULL, '2022-05-18 02:50:15', '2022-05-18 02:50:15'),
(59, 'new', NULL, NULL, 'eyJpdiI6ImpER1dCVERlRWlPMHNmVHh5TjFrR0E9PSIsInZhbHVlIjoiUTVTaUcyWTdJeEYzUjdQSXY4Yko0Zz09IiwibWFjIjoiNDkyMmY5YmFiNmU1YmMxYjVmYWExZDVkOWRjMTEzZGZjNjljOTIwY2JlOWY0M2VkMGRkYzI2NTIzZjRlNzMwZCJ9', 'tS46PVoa', 0, 0, '0', NULL, NULL, NULL, NULL, '2022-05-18 02:50:15', '2022-05-18 02:50:15'),
(60, 'new', NULL, NULL, 'eyJpdiI6Imc4REhxVTVjSEFrd0p4YTJGSUREZmc9PSIsInZhbHVlIjoiQ3R3ek4xcXp6enhWcDRjRkFwcTRpZz09IiwibWFjIjoiYjgyYjkyODc3NzkwYjI5ZTg4ZjI2MjI1ZTA3YWE4NmY0OGY3MDM3ZWM2MGNkYTUwMzE1YTYyYjUyZDE1OWNhMiJ9', 'kfaFELEv', 0, 0, '0', NULL, NULL, NULL, NULL, '2022-05-18 02:50:15', '2022-05-18 02:50:15'),
(61, 'new', NULL, NULL, 'eyJpdiI6ImhCeFhnSGwveU40MnByTWxVVGdKV0E9PSIsInZhbHVlIjoiZ3JTUVF0QjdpQUJubWt3OTlXM0s3dz09IiwibWFjIjoiMDM5MzYyY2VjNTFhN2ZmNjQ3NWQxMjI5ZWJmMDAzZDFlNWM2N2JlZjM5NTk1ODc1OGFhYTYwNzdjNGQ0NGI0NiJ9', 'Unj0jFKU', 0, 0, '0', NULL, NULL, NULL, NULL, '2022-05-18 02:50:15', '2022-05-18 02:50:15'),
(62, 'new', NULL, NULL, 'eyJpdiI6ImRiN3V1YkxXNklZNjdNN29sYlVmdVE9PSIsInZhbHVlIjoiMUM0aUtVc281dlZ4VmxZVCtpVUVCdz09IiwibWFjIjoiNmI0NzRmMGQyNDVmYWQ1NTVhYzUxMTRlMDEzMWFiYWRiNjUwMzc3MWIyNGM0MTk2NmNiM2FjNWU3NDZhZGQxNyJ9', 'o6Y6QPcj', 0, 0, '0', NULL, NULL, NULL, NULL, '2022-05-18 02:50:15', '2022-05-18 02:50:15'),
(63, 'new', NULL, NULL, 'eyJpdiI6InV4bGhuVGc2azZ0MWlzUGZ3MjZxNFE9PSIsInZhbHVlIjoicUw3Q0hXMktlejVjRWxQYmp3TnJXQT09IiwibWFjIjoiMzJiMDI2M2VkZThkMTVkZjFjYmRmOWIzZDc5OGU4NTQ5NjE2NGVkNjZkNDY3YWExZDg2MTRkZGNlNmMzOTFhNiJ9', 'AMyUmFZh', 0, 0, '0', NULL, NULL, NULL, NULL, '2022-05-18 02:50:15', '2022-05-18 02:50:15'),
(64, 'new', NULL, NULL, 'eyJpdiI6IjdxZ0l3eVBlakFOT3JvMDJjYTY5WWc9PSIsInZhbHVlIjoiWTVFWXZ0QmlPN0FJYmFoNTJrcjE3UT09IiwibWFjIjoiZTY3NDZiNDM3YTVhMDZmYjI3OTAyY2U0YTBjYWYyMTQ4YTVmYTcwMzc2MDZmZjI5NDVhMDE2YTE3Y2QzZWZmMSJ9', 'fLau9z2L', 0, 0, '0', NULL, NULL, NULL, NULL, '2022-05-18 02:50:15', '2022-05-18 02:50:15'),
(65, 'new', NULL, NULL, 'eyJpdiI6IkRtb2UvNGtNZVJhbzNLeEdnQWkyZWc9PSIsInZhbHVlIjoiTkR6L3l3dTN1Z3pqMXA4VFJ0YWpBZz09IiwibWFjIjoiNGRkNjE0MWE4ODBlNzU3NDkxOTdiYzM1NzdjYWVkYWRiMmE4YjU3Y2IyYjJjMjY4MWU5ODkwYWE5MWQ4YWM2ZiJ9', '2zilYPNg', 0, 0, '0', NULL, NULL, NULL, NULL, '2022-05-18 02:50:15', '2022-05-18 02:50:15'),
(66, 'new', NULL, NULL, 'eyJpdiI6InhValRoSE9wSjFyOXEyZHVScTA5eXc9PSIsInZhbHVlIjoiWmNMWFp4ZHIwRm55dllSSU5KamhnQT09IiwibWFjIjoiMTRhMGQ3MzM2MTM4YzJlNjg2ZGIwMWEzYTlkODUxYzZhMGJjNTc2N2MyNTEwMjA1ODZjZDIyZmNjNTdkNjlhNiJ9', 'nL7oLqQe', 0, 0, '0', NULL, NULL, NULL, NULL, '2022-05-18 02:50:15', '2022-05-18 02:50:15'),
(67, 'new', NULL, NULL, 'eyJpdiI6ImoyT295TzFOcTJ0SXRzeWc3elpVcHc9PSIsInZhbHVlIjoiSUpPTFlveHUyWExSSlNHa21tdzhPQT09IiwibWFjIjoiZGVjODg1ZTBjZjA2OWY1Yzc2OTc2NjhkZTZhMjM1NjY4YjEwY2Q1M2U3OTMxYjRhMGJhMWM4MDBjYTc1Y2M1YSJ9', '6KMSkyiH', 0, 0, '0', NULL, NULL, NULL, NULL, '2022-05-18 02:50:15', '2022-05-18 02:50:15');

-- --------------------------------------------------------

--
-- Table structure for table `employees_old`
--

CREATE TABLE `employees_old` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `user_name` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `decr_password` varchar(255) DEFAULT NULL,
  `gender` varchar(255) DEFAULT NULL,
  `age` varchar(255) DEFAULT NULL,
  `birthday` date DEFAULT NULL,
  `department` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employees_old`
--

INSERT INTO `employees_old` (`id`, `first_name`, `last_name`, `user_name`, `password`, `decr_password`, `gender`, `age`, `birthday`, `department`, `created_at`, `updated_at`) VALUES
(1, 'Ahmed', 'Shawky', 'a.shawky', 'eyJpdiI6IkoyTDF2TEFvYkJvVHE2T2ExQzkyL3c9PSIsInZhbHVlIjoiVzY1LzlIVUV5TU03b3VHVHpsY3JCQT09IiwibWFjIjoiYWE3OTViY2Q3Y2Y3ZTM0NTljNjUwMDhlMzQ4Y2Q4MzE3YWY0NzZkMDFmZmU3Y2FmY2Q5ZmY4ZGFhYTBlMGJmMiJ9', 'DetCcIW!', 'male', '46', '1974-05-15', 'General Manager', '2021-02-07 03:15:21', '2021-02-07 03:15:21'),
(2, 'Engy', 'Taha', 'e.taha', 'eyJpdiI6InE4MjkrLzA5NmVMenVZQXV1RXkxWXc9PSIsInZhbHVlIjoiMUE0ZUU1a21qUDhSZ1phRlhIc3JkQT09IiwibWFjIjoiYWQ3OGVlODA1MThmYzg2ODc2ZjU2YWE2MDg3ZGExZjQ2YTRmMTFmOWVmOThlNjFmYzExNWNhZDU0ZjZiNmY2NSJ9', 'o79WKBrZ', 'female', '31', '1989-07-26', 'Office Administrator', '2021-02-07 03:15:21', '2021-02-07 03:15:21'),
(3, 'Ahmed', 'Hussein', 'a.hussein', 'eyJpdiI6InY2aDNBTEttZjd0dE54QTVXT1RBdEE9PSIsInZhbHVlIjoiQzh1TUQvdmEzelJKTDhRa00raWhPQT09IiwibWFjIjoiMjYxMWI3ZjNiOWIxNzhjMmVmMTFiMTE3NTZlMDQ2ZmJlZDhlNWIwNDY2N2M0YjJmNTM4OGZmYTc1MTc2YTE0NiJ9', 'ihT&CZye', 'male', '39', '1981-10-28', 'Sales Manager', '2021-02-07 03:15:21', '2021-02-07 03:15:21'),
(4, 'Kholod', 'Gamal', 'k.gamal', 'eyJpdiI6IldaTnBmQ1Qxeko3NmFjUDdDbTNqSVE9PSIsInZhbHVlIjoiemE3QXdrNzJHRlZMQXF5ZWJQT0REdz09IiwibWFjIjoiZDY3MTIzYWEyMTk2YzNkMDAxOTAxZGE4MTBhOWRlMzQ3N2EzMTI2ZTEwYjUwOTYwMzliZjYzNzlmZGZjNjNhMSJ9', '3Ng&ipQ4', 'female', '28', '1992-08-15', 'Sales Assistant', '2021-02-07 03:15:21', '2021-02-07 03:15:21'),
(5, 'Ahmed', 'Eissa', 'a.eissa', 'eyJpdiI6IlZXYjJtS3ZtYVVRbE1DYWpYUjNKTHc9PSIsInZhbHVlIjoiN3VXZTNoMWVWc3VITGt3UlhlT3RQZz09IiwibWFjIjoiNDc3YWZiMzZkNWFkYjdmNzRiYTk3NWZkYmQyOWRiNWQ1NTdmZGFmN2VjYTlmN2MzMDkzNTk2OTQ2MzBmNWJlMCJ9', 'zyWIhA#R', 'male', '39', '1981-08-16', 'Sales Department', '2021-02-07 03:15:21', '2021-02-07 03:15:21'),
(6, 'Hazem', 'Algohary', 'h.algohary', 'eyJpdiI6Inlyb2ZRTDg3ajliZFJHV1FYSHJJYmc9PSIsInZhbHVlIjoiamJXYmJ1UEgzV3lqdzlMblJZNy9aQT09IiwibWFjIjoiZTYzM2VkZjA3ZWY3OGQ0OTY2N2I0YTE5ODJjODUxZmY3YmU4YTM4ODQ1NGY1NjY0YTg1ZjRmODYxMzE2MTAwMCJ9', 'GyWq@d5t', 'male', '41', '1979-12-21', 'Sales Department', '2021-02-07 03:15:21', '2021-02-07 03:15:21'),
(7, 'Mohamed', 'Nabil', 'm.nabil', 'eyJpdiI6Ik9qclBZTEhmVHJWcVRCcVZKc2h1Unc9PSIsInZhbHVlIjoiR2hMZmJDUnM4Y05nSlVVV0FwSzhIUT09IiwibWFjIjoiNmJjMjA4Zjc5ZDljMjA4N2E5MmE2NDUxZjk1NGVlODdiZTQ4NjJhYmY3ZjVmY2RkZjcyMjE0ZGNiYmZkNTk2MCJ9', 'Dvbs8_0S', 'male', '36', '1984-05-25', 'Sales Department', '2021-02-07 03:15:21', '2021-02-07 03:15:21'),
(8, 'Ahmed', 'Abdelfattah', 'a.Abdelfattah', 'eyJpdiI6Inlob1RjVkVVMHlrWU0wTmd4cUJ3WUE9PSIsInZhbHVlIjoiTGVMbDBWbG5jMzhPclpsSU9CUkc0Zz09IiwibWFjIjoiZWE5NDJjYTUzNjA1OTgwNDJmNjdkOGMwNjYyNDk2YWM0YmQ1MjQzNjczODEyNGJiNDI5NmJiM2YzMzE3YTFlZiJ9', 'eg32ZczC', 'male', '39', '1981-10-18', 'Sales Department', '2021-02-07 03:15:21', '2021-02-07 03:15:21'),
(9, 'Mahmoud', 'Salamony', 'm.salamony', 'eyJpdiI6Ii8rdFdINU1LcXZqVkM0ckJvVGZ6d0E9PSIsInZhbHVlIjoiOGFXSEhFbkw3dEJGQ05KaWJFZjI5QT09IiwibWFjIjoiN2RiYzllMDA3NDQ1OGZlMDkwMGIwZDFhMTE3M2UzYTNjZDZhYTFjNzk0MTc3NjQ1YzEwY2YxZDVjMDc4ZTM2ZSJ9', 'w2_qjnf1', 'male', '28', '1992-08-15', 'Sales Department', '2021-02-07 03:15:21', '2021-02-07 03:15:21'),
(10, 'Mostafa', 'Morsi', 'm.morsi', 'eyJpdiI6Ilo4NGJsNi9oV0tjQWRtMVJZYU53RlE9PSIsInZhbHVlIjoiakdDUEdUbGJYNEowWldFd1VoYVY4UT09IiwibWFjIjoiMTBjNWFkMTE1NTVhMzczZDc5MTc1NjAwYmVlNGVkOTgyYWU4Njg5YzcwZjExOTRlMzY0MmM2Y2E5MDExOWFjNiJ9', '@ZCwj0uI', 'male', '42', '1978-03-08', 'Sales Department', '2021-02-07 03:15:21', '2021-02-07 03:15:21'),
(11, 'Maisara', 'Mostafa', 'm.mostafa', 'eyJpdiI6Ijl4amRkQUZEbFZqSWtDaFJ1ZStwOHc9PSIsInZhbHVlIjoib25mOHZWN253ODR5NmtickNVd004QT09IiwibWFjIjoiZTM0MWZlNTdhNzI0N2E2N2UxNTBjNGNhZWVlMmVlYTJhODQzNjFhMTZlN2U5YWU1ZTIxZTJlZjE1ODYwMzI0ZSJ9', 'Rys9qjSG', 'male', '32', '1988-06-21', 'Sales Department', '2021-02-07 03:15:21', '2021-02-07 03:15:21'),
(12, 'Mina', 'Harris', 'm.harris', 'eyJpdiI6Ik9ZTUM2VHFRMDh6T0Y2a1FwSEZjL2c9PSIsInZhbHVlIjoiUWMzTE1iQXdNdkpIbGJsVG03bU93dz09IiwibWFjIjoiNTA1YmVhNTllYWVhOGUyM2I4OTY2ZWY1NDI2NDc4NTlhNGUxNjIwNWE5MmE4NmEwYTJkODAxODdhM2FlODhjMyJ9', 's543DhfC', 'male', '35', '1985-10-26', 'Sales Department', '2021-02-07 03:15:21', '2021-02-07 03:15:21'),
(13, 'Islam', 'El Nahriry', 'm.elnahriry', 'eyJpdiI6ImtLRkRSbzNwR3k0UkMwVGVmWHZBeFE9PSIsInZhbHVlIjoiVlpnS1UyYnhBNDMwYTNCYVlTY0wydz09IiwibWFjIjoiZDQ5YmYxMjIzZjc0OTZlN2RiOTgwYWM1MDZiOThhMmMxNjU3ZTRjNTE5YjZiNjUwYjllM2JmNTQ5YTQ1YTBjNyJ9', '0dGUk7If', 'male', '45', '1975-11-18', 'Sales Department- Marketing', '2021-02-07 03:15:21', '2021-02-07 03:15:21'),
(14, 'Akram', 'El Zaiaty', 'a.elzaiaty', 'eyJpdiI6IkxNN0NXVDlXandEZFFXOGtablkxb2c9PSIsInZhbHVlIjoiVnVQQ0pQR1RJTTlkRGJKZXNoUXpBUT09IiwibWFjIjoiMjE0MDk0MWE1ZjhhNDM3NTgyMmRlZjJjYTg0MTc0NjYwMDNkODllYWExMjgwN2EzMjhlMDIwZjI2MDExNzMxMyJ9', 'kRgbKv&6', 'male', '47', '1972-02-25', 'Finance & HR Manger', '2021-02-07 03:15:21', '2021-02-07 03:15:21'),
(15, 'Ahmed', 'Hosny', 'a.hosny', 'eyJpdiI6IkhBQm0vRWFHMmFQOU01UWpJck41SGc9PSIsInZhbHVlIjoiR2lVMDVadXJZaStwZUd6bCtxRklxdz09IiwibWFjIjoiYTQyNDEwNzY0NDQ4YzAzNjc2ZThiODE5ZjFhOWZiMmNkN2YzZDgyMjFhNjA0ZTJjYzhlZWM1NTZiZDIzOWEyZSJ9', 'bLfndOM5', 'male', '31', '1989-02-22', 'Finance Department', '2021-02-07 03:15:21', '2021-02-07 03:15:21'),
(16, 'Mahmoud', 'Shehata', 'm.shehata', 'eyJpdiI6IjJid3h0T2FVaVZVT3diMmZLRmtkblE9PSIsInZhbHVlIjoiUjhvU3c3aWhWOHJXSFo1eG5tQWUwZz09IiwibWFjIjoiMTEzNGI0OThjMTU5YmQ2NWQ5MzhiZjJiYWM4YjMxMjEzZjg3YmMxMTRjMWUyZjg0ZGFhNmQ1NmJmMzc2ZTU5MyJ9', 'DpmHAdyt', 'male', '35', '1985-06-01', 'Finance Department', '2021-02-07 03:15:21', '2021-02-07 03:15:21'),
(17, 'Ibrahim', 'Eid', 'i.eid', 'eyJpdiI6ImxqSzNtd1lyY1k1MlJ5c3pULzkzWHc9PSIsInZhbHVlIjoiY25oT01JQzZOT3ZBOE9TOERoeVo4Zz09IiwibWFjIjoiMjAyODI0NzQxNDc0ZjI1ODExM2JkY2QxNjNkYmY1ZjRhYWJhMTNlNTk3YjhlZjAyOWUzMDdkNmE5Y2MzNDlmZiJ9', 'KwIqFM&x', 'male', '24', '1996-08-26', 'Collectrors', '2021-02-07 03:15:21', '2021-02-07 03:15:21'),
(18, 'Mohamed', 'Hassouna', 'm.hassouna', 'eyJpdiI6IlR3VGNETVkwbmdjaTdvSHplemNmR0E9PSIsInZhbHVlIjoiQTJ0NW5sK0lUY1dMNWdKUEhUM2JQZz09IiwibWFjIjoiMmMzZWU0YTZiMDA3M2MzYzQwYWQ3NDBhNzY3NDUxMGM0NDIyNjAxNWQ3ZmE3YTc2ZjcyODg0NTgzZWJhODFjMiJ9', 'GqK46cfI', 'male', '51', '1969-06-20', 'Collectrors', '2021-02-07 03:15:21', '2021-02-07 03:15:21'),
(19, 'Ahmed', 'Rafaat', 'a.rafaat', 'eyJpdiI6ImtzSWJUODJ5NENJMWdoQ2hoYVk2aFE9PSIsInZhbHVlIjoiSVV1RWllUTJ4V2VxT3d6RU41M1RJUT09IiwibWFjIjoiZWY5ZjIxNDkzOTAyMDAwNTM1NGQ2ODczNmNmZjk1YTBmMGI1NjI2MmIyM2Q3ZTIyODJjOTZhYTRiNzEzOWYwZiJ9', 'FWJ0KU6X', 'male', '32', '1988-04-12', 'Collectrorst', '2021-02-07 03:15:21', '2021-02-07 03:15:21'),
(20, 'Mohamed', 'Badr', 'm.badr', 'eyJpdiI6Imx5VkVqTko2WXRmSnAwelJQdjhqelE9PSIsInZhbHVlIjoieFJ5TFZWRnlnV1BJekpJMEJxZ0dwUT09IiwibWFjIjoiOWUwMGIzODY3MzUzOTBkOTc4NzU4OGIwNzQ0ZjVjZTJiZGEyOTIyMTMyNWMzYWFjNjAxYTlhYzhkNjJmN2E0MiJ9', 'H19IpAUt', 'male', '50', '1970-03-04', 'Supply Chain Manager', '2021-02-07 03:15:21', '2021-02-07 03:15:21'),
(21, 'Ahmed', 'Saad', 'a.saad', 'eyJpdiI6Iit2Uitha05IQmQ3blhmVC9WY1BjZFE9PSIsInZhbHVlIjoiTE5EdzNIQzZ3ZDB5ZGFtcDFNS1RwQT09IiwibWFjIjoiNDUxOGMzNjRkMWUyMWJjZjZiZmRmMjdmYjJmNzQzZjcwZmRkOTczYTg4NTY4ZGUwYTMyZGU4ZGQyZWNiNGM3ZiJ9', 'i7125clp', 'male', '32', '1988-10-01', 'Supply Chain Department', '2021-02-07 03:15:21', '2021-02-07 03:15:21'),
(22, 'Ola', 'Breik', 'o.breik', 'eyJpdiI6IkE4bUZLdFB4K3hDUGRsVUdKMitpU2c9PSIsInZhbHVlIjoiQTdsOWMwSVcxdm95SW1RN2hCOVpzZz09IiwibWFjIjoiYjZkYjE4NjA0YjkyNGE5MGQxOTliZTRhOTZmNTAxMWIyOWIxYThiZTMyOTliZWE4MTJkZGQ4YjczOTYxMjIyYyJ9', 'PYM&NSfI', 'male', '31', '1989-12-05', 'Supply Chain Department', '2021-02-07 03:15:21', '2021-02-07 03:15:21'),
(23, 'David', 'Magdy', 'd.magdy', 'eyJpdiI6IlYyUnRtNThCK2YzMHRoR01tSDRTNkE9PSIsInZhbHVlIjoid2FqZWEyZWVQY1lJQlFEaHdQc2pEUT09IiwibWFjIjoiMjI5YzNkNmZlMjI2MWU3YjMwY2U0YTUwYmI2MWI4NjMyZGQ0MjM4YzNhMzNmMTFiNjk2NDkyZjhjNzkzMTNhNCJ9', 'vS3B!Mka', 'male', '26', '1994-03-08', 'Supply Chain Department', '2021-02-07 03:15:21', '2021-02-07 03:15:21'),
(24, 'Haitham', 'Said', 'h.said', 'eyJpdiI6Ii94V3RqRC9jL2tkSXdwMU9oZ1FXWEE9PSIsInZhbHVlIjoidGpJTGZLUHh2Y2FuU3BTSjBQQlRIZz09IiwibWFjIjoiODVmOGQ0YTNkYjMxMTNlNDFhNDk1MmJiMjdhYjk4MTFmMWNjZGJmMzM0ODg5NjNiMWQ5MDFmNWFlZTg0OTBmNCJ9', 'zjwqdU5T', 'male', '40', '1980-11-09', 'Supply Chain Department', '2021-02-07 03:15:21', '2021-02-07 03:15:21'),
(25, 'Ramy', 'Hassan', 'r.hassan', 'eyJpdiI6IklFYllCNzNuUHlEQ1pWYjU5VUZld1E9PSIsInZhbHVlIjoiTzdPSFlrQXEzZnZYeU5aTlhHUUZqdz09IiwibWFjIjoiNWE3MjA0MjViZjQxY2IyY2I3Zjg0ZTAxNzMwYzI3Y2NjZTM4MGQ0ZGJjODY4NWY0MDNmZTA4OWVmYTY0OTRhYiJ9', 'zZ3SHlcm', 'male', '35', '1985-08-25', 'Supply Chain Department', '2021-02-07 03:15:21', '2021-02-07 03:15:21'),
(26, 'Khaled', 'Sarhan', 'k.sarhan', 'eyJpdiI6ImhndFhYY3hvNm5vNWx1THBQZlpFK2c9PSIsInZhbHVlIjoiME5uclF5M0lvRlV2SmtLUHBnV1VXUT09IiwibWFjIjoiZGQ0N2FmODBlZjA0MWMwOTFlNGY3M2YwYzNlNWU4YjJhMzQ3ODFhZDA2NTUwYTJjZDA4NGJkODJkNDAxZjIyZCJ9', 'RImLrtq3', 'male', '54', '1966-09-24', 'Service Manager', '2021-02-07 03:15:21', '2021-02-07 03:15:21'),
(27, 'Noura', 'Osama', 'm.osama', 'eyJpdiI6ImZ0R2h4aVFNTWk4QjFtRE1SOFMvU2c9PSIsInZhbHVlIjoidTByb01tY1FTWmhmOSswQTltdDZadz09IiwibWFjIjoiNTA1OTMzOGJkYWU5NGM2OWIzYjY2YzNiNDlkMDRlMDRjNDEzMDYzM2E1Mjk3YjlkMDgyYjRmODY5NWUxZGFmMiJ9', 'c8@dZC&w', 'female', '31', '1989-10-18', 'Service  Assistant', '2021-02-07 03:15:21', '2021-02-07 03:15:21'),
(28, 'Yehia', 'Khallaf', 'y.khallaf', 'eyJpdiI6InpIVmdFVmlodVF5d0dob3RNTy9TQmc9PSIsInZhbHVlIjoiQ1Rsd2dGVVlGM2FsZm5JaGhBV1Nydz09IiwibWFjIjoiNTE4Y2VlNTZhNzUzNWIyMWMyMzViMzRhZTAzODZmMmQzMGZhODQyY2QzZDU1MDgzZDA2NDkzOWM0NDNmMjAyMyJ9', 'ChUHieJK', 'male', '40', '1980-05-27', 'Service - Customer Support Team', '2021-02-07 03:15:21', '2021-02-07 03:15:21'),
(29, 'Ahmed', 'Saleh', 'a.saleh', 'eyJpdiI6IjRrV3lQcTVuRVlYMzhtTUVXNUtrdkE9PSIsInZhbHVlIjoiU1p4eGZjN1ZRejBOVklobGZLVGw0Zz09IiwibWFjIjoiMTIzNjQ0M2U2YWU2YTg5YjE5YTlkZDBhMWY2ZDYyNjQ0MTg1NjAzNmFiMTg5NWJiNjVhMWJlYjkxZmJhMjU0ZiJ9', '7H4&oNrQ', 'male', '29', '1991-06-03', 'Service - Customer Support Teamt', '2021-02-07 03:15:21', '2021-02-07 03:15:21'),
(30, 'Mohamed', 'Gamal', 'm.gamal', 'eyJpdiI6InBoeXJXU1YxSFpYbTM2KzFCYWRUK3c9PSIsInZhbHVlIjoiZTh2cS83dE1BY2d1UUh5bkd4RjVIZz09IiwibWFjIjoiZjJlYzRlNjk3NTFlZTcxYTFmOTNmY2Q4ZDEyMWUwOTEzMjdiZDZhMDIxZWNlYjM3NjdlN2FlZGZmNTk5ZGFmZCJ9', '&hZYUOTp', 'male', '28', '1992-03-27', 'Service - Customer Support Team', '2021-02-07 03:15:21', '2021-02-07 03:15:21'),
(31, 'Mohammed Ramadan', 'Eldewak', 'm.eldewak', 'eyJpdiI6Im16bWVpUWdhRDA4Z1JyUjRoZ0RpTFE9PSIsInZhbHVlIjoiY211WGMrei9FdDRscHZic0x4Zmlidz09IiwibWFjIjoiNWQxYThmMDE3N2Y5MTE0ZGY2MWQ4ODM5MGM0OWMxNmFlZjliY2Y2OTEyMDRlMjMxYmEzNzFkNDRlMGNjYTdhOCJ9', 'FBGoq8fR', 'male', '30', '1990-06-01', 'Service - Customer Support Team', '2021-02-07 03:15:21', '2021-02-07 03:15:21'),
(32, 'Mahmoud', 'Alawi', 'm.alawi', 'eyJpdiI6IjVjb0g0eW1QTmFwUDNjUXRGNXdNemc9PSIsInZhbHVlIjoiY2lpMVhhMFB3bVVLYi9Ta25oclNIQT09IiwibWFjIjoiODgzNzE4NDhmNzBmOTQ5ODZmZjQyNzYwMzFlZjkwODBkM2U4NjZmNTdjYzcxZGFmM2U3YzlkNGE3NGIyMjMyOSJ9', 'SXK7VwvF', 'male', '29', '1991-10-01', 'Service - Customer Support Team', '2021-02-07 03:15:21', '2021-02-07 03:15:21'),
(33, 'Akram', 'Elnagar', 'a.elnagar', 'eyJpdiI6IlJONUkvRkhHRTB4aklhNVVmWVpiRkE9PSIsInZhbHVlIjoidGRYQVplYWNWZU1yQUxGam9LVXF5dz09IiwibWFjIjoiNDY4Y2U4MTVkZjg0YmZiOWRmMGFlNTg1ZWFhMjk5M2Y0MDk0MmE0YWJmYjI1ZGFjY2MxZGE2MzJhZjU0MjBiZCJ9', '_umcze5i', 'male', '31', '1989-03-03', 'Service - Customer Support Team', '2021-02-07 03:15:21', '2021-02-07 03:15:21'),
(34, 'Mohamed', 'Abdelrazek', 'm.abdelrazek', 'eyJpdiI6Ii8xdjRTQXNtUi9MWWJMWXpuSFpZcGc9PSIsInZhbHVlIjoiZ05UWVY4VS96TjdDMWVUTmlBQVVBdz09IiwibWFjIjoiZmJiYTU4OTI5NDI4OGQ1YzZkZjIwN2YxMDJiZjkzZjMyZmUzMzU2OGJjZDY1MGFlOGQ2NGNmY2ZkODZkNDU4OCJ9', 'SwaOgzNy', 'male', '37', '1983-02-23', 'Service - Customer Support Team', '2021-02-07 03:15:21', '2021-02-07 03:15:21'),
(35, 'Ahmed', 'Mehanna', 'a.mehanna', 'eyJpdiI6IjlYMTVoWmRiSkRXZU5EOEhZMTAwMEE9PSIsInZhbHVlIjoiTUNLZTV2N1lySkRPVTZnRkNjdzc1UT09IiwibWFjIjoiYTk5ZDYwY2QxZTA3ZWQ1OWU3YjdjYTA3OGE5ZTNiNmMxMTY5ZjAwMDc1NDQ5ZGMxYzA5ZmNmMjRmOWU5ZTRmZiJ9', 'I#Q_uioT', 'male', '33', '1987-06-06', 'IT Administrator Department', '2021-02-07 03:15:21', '2021-02-07 03:15:21'),
(36, 'Mohamed', 'Riad', 'm.riad', 'eyJpdiI6IjhuaDhuK0FzYW5DS3pubkMrd0ZheFE9PSIsInZhbHVlIjoiaEI5cExDWklFbi9DTXhOSVNJS1Rpdz09IiwibWFjIjoiZjVjODE5YjI2ZmI5ZjBkZDc3YmU3OGE2YjcxZWE1YTVhNzBkMjQxNTE4NjMxYjIxMzk0ZTE2MjlhNTQyNTYxYiJ9', 'kgYu@S1E', 'male', '34', '1986-08-28', 'Office Boy', '2021-02-07 03:15:21', '2021-02-07 03:15:21'),
(37, 'Mahmoud', 'Kheir', 'm.Kheir', 'eyJpdiI6ImdqRE1sNU5WeS9raTFhNWs2aWFUM1E9PSIsInZhbHVlIjoid045MWxxek1pZmoyZFA0NlhLdTRpdz09IiwibWFjIjoiNTcyMjY5ZmM0ZGRmZDdkMTUzM2YwZjQwNTNhZDgwNzNlYWM5N2RiMjZhN2UzMjhjMDIwZGZlYjc1MWQ5MzYxZCJ9', 'PAzGgEy_', 'male', '35', '1985-11-19', 'Office Boy', '2021-02-07 03:15:21', '2021-02-07 03:15:21');

-- --------------------------------------------------------

--
-- Table structure for table `employee_quizzes_old`
--

CREATE TABLE `employee_quizzes_old` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `quiz_id` int(11) DEFAULT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `emp_answer` text DEFAULT NULL,
  `correct_answer` varchar(255) DEFAULT NULL,
  `check_answer` varchar(255) DEFAULT NULL,
  `emp_score` int(11) DEFAULT NULL,
  `score` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employee_quizzes_old`
--

INSERT INTO `employee_quizzes_old` (`id`, `quiz_id`, `employee_id`, `emp_answer`, `correct_answer`, `check_answer`, `emp_score`, `score`, `created_at`, `updated_at`) VALUES
(1, 1, 15, '[\"What\"]', '[\"What\"]', 'correct', 1, 1, '2021-02-15 21:40:11', '2021-02-15 21:40:11'),
(2, 2, 15, '[\"They\'re\"]', '[\"They\'re\"]', 'correct', 1, 1, '2021-02-15 21:40:11', '2021-02-15 21:40:11'),
(3, 3, 15, '[\"It\"]', '[\"It\"]', 'correct', 1, 1, '2021-02-15 21:40:11', '2021-02-15 21:40:11'),
(4, 5, 15, '[\"you spell\"]', '[\"do you spell\"]', 'incorrect', 0, 1, '2021-02-15 21:40:11', '2021-02-15 21:40:11'),
(5, 6, 15, '[\"a\"]', '[\"--\"]', 'incorrect', 0, 1, '2021-02-15 21:40:11', '2021-02-15 21:40:11'),
(6, 7, 15, '[\"Is Mr. Mike a teacher?\"]', '[\"Is Mr. Mike a teacher?\"]', 'correct', 1, 1, '2021-02-15 21:40:11', '2021-02-15 21:40:11'),
(7, 8, 15, '[\"your father\'s job\"]', '[\"your father\'s job\"]', 'correct', 1, 1, '2021-02-15 21:40:11', '2021-02-15 21:40:11'),
(8, 9, 15, '[\"doesn\'t have\"]', '[\"doesn\'t have\"]', 'correct', 1, 1, '2021-02-15 21:40:11', '2021-02-15 21:40:11'),
(9, 10, 15, '[\"working\"]', '[\"works\"]', 'incorrect', 0, 1, '2021-02-15 21:40:11', '2021-02-15 21:40:11'),
(10, 11, 15, '[\"We do not often go to the cinema.\"]', '[\"We do not often go to the cinema.\"]', 'correct', 1, 1, '2021-02-15 21:40:11', '2021-02-15 21:40:11'),
(11, 12, 15, '[\"do\"]', '[\"do\"]', 'correct', 1, 1, '2021-02-15 21:40:11', '2021-02-15 21:40:11'),
(12, 14, 15, '[\"ever\"]', '[\"ever\"]', 'correct', 1, 1, '2021-02-15 21:40:11', '2021-02-15 21:40:11'),
(13, 18, 15, '[\"taller\"]', '[\"taller\"]', 'correct', 1, 1, '2021-02-15 21:40:11', '2021-02-15 21:40:11'),
(14, 19, 15, '[\"most\"]', '[\"most\"]', 'correct', 1, 1, '2021-02-15 21:40:11', '2021-02-15 21:40:11'),
(15, 23, 15, '[\"more friendly\"]', '[\"friendlier\"]', 'incorrect', 0, 1, '2021-02-15 21:40:11', '2021-02-15 21:40:11'),
(16, 28, 15, '[\"in\"]', '[\"on\"]', 'incorrect', 0, 1, '2021-02-15 21:40:11', '2021-02-15 21:40:11'),
(17, 29, 15, '[\"for\"]', '[\"for\"]', 'correct', 1, 1, '2021-02-15 21:40:11', '2021-02-15 21:40:11'),
(18, 30, 15, 'where sally\'s grandmother lives?', '[]', '', 0, 1, '2021-02-15 21:40:11', '2021-02-15 21:40:11'),
(19, 31, 15, 'how many children Tom Has ?', '[]', '', 0, 1, '2021-02-15 21:40:11', '2021-02-15 21:40:11'),
(20, 32, 15, 'when do you get up ?', '[]', '', 0, 1, '2021-02-15 21:40:11', '2021-02-15 21:40:11'),
(21, 33, 15, '[\"I didn\'t go to the cinema\\u00a0 last night.\\u00a0\"]', '[\"I didn\'t go to the cinema\\u00a0 last night.\\u00a0\"]', 'correct', 1, 1, '2021-02-15 21:40:11', '2021-02-15 21:40:11'),
(22, 34, 15, '[\"They don\'t have become very rich.\"]', '[\"They haven\'t become very rich.\"]', 'incorrect', 0, 1, '2021-02-15 21:40:11', '2021-02-15 21:40:11'),
(23, 35, 15, '[\"I don\'t have a big window in my room.\"]', '[\"I don\'t have a big window in my room.\"]', 'correct', 1, 1, '2021-02-15 21:40:11', '2021-02-15 21:40:11'),
(24, 36, 15, '[\"My mom doesn\'t have perfect cooking skills.\"]', '[\"My mom doesn\'t have perfect cooking skills.\"]', 'correct', 1, 1, '2021-02-15 21:40:11', '2021-02-15 21:40:11'),
(25, 37, 15, '[\"ill\",\"quiet\",\"difficult\",\"beautiful\",\"crowded\",\"intelligent\",\"rich\",\"friendly\",\"interesting\"]', '[\"ill\",\"quiet\",\"difficult\",\"beautiful\",\"crowded\",\"intelligent\",\"rich\",\"friendly\",\"interesting\"]', '[\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\"]', 9, 9, '2021-02-15 21:40:11', '2021-02-15 21:40:11'),
(26, 38, 15, '[\"arrived\",\"want\",\"do\",\"am not understanding\",\"are talking\",\"had meet\",\"came\",\"does\",\"need\'s\",\"is ringing\"]', '[\"arrived\",\"want\",\"have been\",\"don\'t understand\",\"talk\",\"met\",\"came\",\"is doing\",\"needs\",\"is ringing\"]', '[\"correct\",\"correct\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"correct\",\"incorrect\",\"incorrect\",\"correct\"]', 0, 10, '2021-02-15 21:40:11', '2021-02-15 21:40:11'),
(27, 42, 15, '[\"24\",\"13:20\",\"18:45\",\"11\",\"5:15\",\"8:20\",\"259\"]', '[\"24\",\"13.20\",\"18.45\",\"11\",\"5.15\",\"8.20\",\"259\"]', '[\"correct\",\"incorrect\",\"incorrect\",\"correct\",\"incorrect\",\"incorrect\",\"correct\"]', 0, 7, '2021-02-15 21:40:11', '2021-02-15 21:40:11'),
(28, 48, 15, '[\"speaking\",\"this is\",\"really sorry\",\"have to\",\"Oh, dear\",\"did she\",\"don\'t worry\",\"could you help me\",\"shall I\",\"that would be\"]', '[\"speaking\",\"this is\",\"really sorry\",\"have to\",\"Oh, dear\",\"did she\",\"don\'t worry\",\"could you help me\",\"shall I\",\"that would be\"]', '[\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\"]', 10, 10, '2021-02-15 21:40:11', '2021-02-15 21:40:11'),
(29, 49, 15, '[\"will accept\"]', '[\"would accept\"]', 'incorrect', 0, 3, '2021-02-15 21:40:11', '2021-02-15 21:40:11'),
(30, 50, 15, '[\"will pass\"]', '[\"will pass\"]', 'correct', 3, 3, '2021-02-15 21:40:11', '2021-02-15 21:40:11'),
(31, 51, 15, '[\"seeing\"]', '[\"seeing\"]', 'correct', 3, 3, '2021-02-15 21:40:11', '2021-02-15 21:40:11'),
(32, 52, 15, '[\"want -- next\"]', '[\"wanted -- the following\"]', 'incorrect', 0, 3, '2021-02-15 21:40:11', '2021-02-15 21:40:11'),
(33, 53, 15, '[\"which\"]', '[\"which\"]', 'correct', 3, 3, '2021-02-15 21:40:11', '2021-02-15 21:40:11'),
(34, 54, 15, '[\"where\"]', '[\"where\"]', 'correct', 3, 3, '2021-02-15 21:40:11', '2021-02-15 21:40:11'),
(35, 55, 15, '[\"have been fired\"]', '[\"were fired\"]', 'incorrect', 0, 3, '2021-02-15 21:40:11', '2021-02-15 21:40:11'),
(36, 56, 15, '[\"give up\"]', '[\"give up\"]', 'correct', 3, 3, '2021-02-15 21:40:11', '2021-02-15 21:40:11'),
(37, 57, 15, '[\"go out with\"]', '[\"put up with\"]', 'incorrect', 0, 3, '2021-02-15 21:40:11', '2021-02-15 21:40:11'),
(38, 58, 15, '[\"impolite\"]', '[\"impolite\"]', 'correct', 3, 3, '2021-02-15 21:40:11', '2021-02-15 21:40:11'),
(39, 59, 15, '[\"niece\"]', '[\"niece\"]', 'correct', 3, 3, '2021-02-15 21:40:11', '2021-02-15 21:40:11'),
(40, 60, 15, '[\"mother-in-law\"]', '[\"mother-in-law\"]', 'correct', 3, 3, '2021-02-15 21:40:11', '2021-02-15 21:40:11'),
(41, 61, 15, '[\"17\"]', '[\"17\"]', 'correct', 2, 2, '2021-02-15 21:40:11', '2021-02-15 21:40:11'),
(42, 62, 15, '[\"20\"]', '[\"20\"]', 'correct', 2, 2, '2021-02-15 21:40:11', '2021-02-15 21:40:11'),
(43, 63, 15, '[\"$20\"]', '[\"$20\"]', 'correct', 2, 2, '2021-02-15 21:40:11', '2021-02-15 21:40:11'),
(44, 64, 15, '[\"10\"]', '[\"10\"]', 'correct', 2, 2, '2021-02-15 21:40:11', '2021-02-15 21:40:11'),
(45, 65, 15, '[\"in the water tank\"]', '[\"on his body\"]', 'incorrect', 0, 2, '2021-02-15 21:40:11', '2021-02-15 21:40:11'),
(46, 66, 15, '[\"stomach problems\"]', '[\"stomach problems\"]', 'correct', 2, 2, '2021-02-15 21:40:11', '2021-02-15 21:40:11'),
(47, 67, 15, '[\"Although\"]', '[\"Although\"]', 'correct', 4, 4, '2021-02-15 21:40:11', '2021-02-15 21:40:11'),
(48, 68, 15, '[\"Although being tired\",\"Despite being tired\"]', '[\"Despite being tired\"]', 'incorrect', 0, 4, '2021-02-15 21:40:11', '2021-02-15 21:40:11'),
(49, 69, 15, '[\"am used to working\"]', '[\"am used to working\"]', 'correct', 4, 4, '2021-02-15 21:40:11', '2021-02-15 21:40:11'),
(50, 71, 15, '[\"have forgotten\"]', '[\"had forgotten\"]', 'incorrect', 0, 4, '2021-02-15 21:40:11', '2021-02-15 21:40:11'),
(51, 72, 15, '[\"keep in touch with\"]', '[\"keep in touch with\"]', 'correct', 4, 4, '2021-02-15 21:40:11', '2021-02-15 21:40:11'),
(52, 73, 15, '[\"splitted\"]', '[\"split\"]', 'incorrect', 0, 4, '2021-02-15 21:40:11', '2021-02-15 21:40:11'),
(53, 74, 15, '[\"don\'t\"]', '[\"not to\"]', 'incorrect', 0, 4, '2021-02-15 21:40:11', '2021-02-15 21:40:11'),
(54, 75, 15, '[\"care\",\"enjoyable\",\"disappointment\"]', '[\"care\",\"enjoyable\",\"disappointment\"]', '[\"correct\",\"correct\",\"correct\"]', 6, 6, '2021-02-15 21:40:11', '2021-02-15 21:40:11'),
(55, 76, 15, '[\"in Australia\"]', '[\"at a party\"]', 'incorrect', 0, 4, '2021-02-15 21:40:11', '2021-02-15 21:40:11'),
(56, 77, 15, '[\"lost her passport\"]', '[\"lost her passport\"]', 'correct', 4, 4, '2021-02-15 21:40:11', '2021-02-15 21:40:11'),
(57, 78, 15, '[\"lost her job\"]', '[\"lost her job\"]', 'correct', 4, 4, '2021-02-15 21:40:11', '2021-02-15 21:40:11'),
(58, 79, 15, '[\"the country\"]', '[\"the country\"]', 'correct', 4, 4, '2021-02-15 21:40:11', '2021-02-15 21:40:11'),
(59, 80, 15, '[\"an aggressive\"]', '[\"an aggressive\"]', 'correct', 4, 4, '2021-02-15 21:40:11', '2021-02-15 21:40:11'),
(60, 81, 15, '[\"some boxing gloves\"]', '[\"a bike\"]', 'incorrect', 0, 4, '2021-02-15 21:40:11', '2021-02-15 21:40:11'),
(61, 82, 15, '[\"looting\",\"robbing\",\"burgl\",\"vandalise\"]', '[\"looting\",\"robbery\",\"burgle\",\"vandalize\"]', '[\"correct\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 8, '2021-02-15 21:40:11', '2021-02-15 21:40:11'),
(62, 83, 15, '[\"politicist\",\"economical\",\"environmental\",\"polluterment\"]', '[\"politician\",\"economic\",\"environmental\",\"pollution\"]', '[\"incorrect\",\"incorrect\",\"correct\",\"incorrect\"]', 0, 8, '2021-02-15 21:40:11', '2021-02-15 21:40:11'),
(63, 84, 15, '[\"give someone a job\"]', '[\"fire someone form a job\"]', 'incorrect', 0, 4, '2021-02-15 21:40:11', '2021-02-15 21:40:11'),
(64, 85, 15, '[\"strong\"]', '[]', 'incorrect', 0, 4, '2021-02-15 21:40:11', '2021-02-15 21:40:11'),
(65, 86, 15, '[\"have a rough voice\"]', '[\"have a difficulty in speaking\"]', 'incorrect', 0, 4, '2021-02-15 21:40:11', '2021-02-15 21:40:11'),
(66, 87, 15, '[\"gripping\",\"scary\",\"overrated\",\"predictable\",\"memorable\",\"hilarious\"]', '[\"memorable\",\"scary\",\"overrated\",\"predictable\",\"gripping\",\"hilarious\"]', '[\"incorrect\",\"correct\",\"correct\",\"correct\",\"incorrect\",\"correct\"]', 0, 12, '2021-02-15 21:40:11', '2021-02-15 21:40:11'),
(67, 90, 15, '[\"attack\",\"Sued\",\"spread\",\"crisis\",\"hostage\",\"released\",\"invaded\",\"troops\",\"flee\",\"damages\",\"outcry\"]', '[\"sued\",\"damages\",\"spread\",\"crisis\",\"hostage\",\"released\",\"invaded\",\"troops\",\"flee\",\"attack\",\"outcry\"]', '[\"incorrect\",\"incorrect\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"incorrect\",\"correct\"]', 0, 30, '2021-02-15 21:40:11', '2021-02-15 21:40:11'),
(68, 91, 15, '[\"True\"]', '[\"False\"]', 'incorrect', 0, 5, '2021-02-15 21:40:11', '2021-02-15 21:40:11'),
(69, 92, 15, '[\"True\"]', '[\"True\"]', 'correct', 5, 5, '2021-02-15 21:40:11', '2021-02-15 21:40:11'),
(70, 93, 15, '[\"False\"]', '[\"False\"]', 'correct', 5, 5, '2021-02-15 21:40:11', '2021-02-15 21:40:11'),
(71, 94, 15, '[\"False\"]', '[\"True\"]', 'incorrect', 0, 5, '2021-02-15 21:40:11', '2021-02-15 21:40:11'),
(72, 95, 15, '[\"three times\"]', '[\"three times\"]', 'correct', 5, 5, '2021-02-15 21:40:11', '2021-02-15 21:40:11'),
(73, 96, 15, 'Dear sir , \r\nHope all is well , \r\n\r\nAfter my viewing on the company\'s policy , please find below the strengths , the weaknesses  and how can we turn  the weaknesses into strengths , \r\n\r\n* Strengths \r\n- issuing the invoices when we receive  the order from customer side then review with sales offer then confirm the prices with the commercial \r\n- issuing the products from stock after confirm the batches by mail from our third party \r\n- finance team involve at each transaction because he who begin the cycle and ending it \r\n\r\n* Weaknesses\r\n- Employee Exp have to be more electronic than now and use new software like Concure system \r\n- the sales team have to be more organized than now \r\n\r\nThanks &BR, \r\nAhmed', '[]', '', 0, 20, '2021-02-15 21:40:11', '2021-02-15 21:40:11'),
(74, 1, 37, '[\"What\"]', '[\"What\"]', 'correct', 1, 1, '2021-02-16 15:50:31', '2021-02-16 15:50:31'),
(75, 2, 37, '[\"I\'m\"]', '[\"They\'re\"]', 'incorrect', 0, 1, '2021-02-16 15:50:31', '2021-02-16 15:50:31'),
(76, 3, 37, '[\"It\"]', '[\"It\"]', 'correct', 1, 1, '2021-02-16 15:50:31', '2021-02-16 15:50:31'),
(77, 5, 37, '[\"spell\"]', '[\"do you spell\"]', 'incorrect', 0, 1, '2021-02-16 15:50:31', '2021-02-16 15:50:31'),
(78, 6, 37, '[\"an\"]', '[\"--\"]', 'incorrect', 0, 1, '2021-02-16 15:50:31', '2021-02-16 15:50:31'),
(79, 7, 37, '[\"Is Mr. Mike a teacher?\"]', '[\"Is Mr. Mike a teacher?\"]', 'correct', 1, 1, '2021-02-16 15:50:31', '2021-02-16 15:50:31'),
(80, 8, 37, '[\"your father job\'s\"]', '[\"your father\'s job\"]', 'incorrect', 0, 1, '2021-02-16 15:50:31', '2021-02-16 15:50:31'),
(81, 10, 37, '[\"is work\"]', '[\"works\"]', 'incorrect', 0, 1, '2021-02-16 15:50:31', '2021-02-16 15:50:31'),
(82, 12, 37, '[\"have\"]', '[\"do\"]', 'incorrect', 0, 1, '2021-02-16 15:50:31', '2021-02-16 15:50:31'),
(83, 14, 37, '[\"never\"]', '[\"ever\"]', 'incorrect', 0, 1, '2021-02-16 15:50:31', '2021-02-16 15:50:31'),
(84, 18, 37, '[\"taller\"]', '[\"taller\"]', 'correct', 1, 1, '2021-02-16 15:50:31', '2021-02-16 15:50:31'),
(85, 19, 37, '[\"more\"]', '[\"most\"]', 'incorrect', 0, 1, '2021-02-16 15:50:31', '2021-02-16 15:50:31'),
(86, 23, 37, '[\"more friendly\"]', '[\"friendlier\"]', 'incorrect', 0, 1, '2021-02-16 15:50:31', '2021-02-16 15:50:31'),
(87, 28, 37, '[\"in\"]', '[\"on\"]', 'incorrect', 0, 1, '2021-02-16 15:50:31', '2021-02-16 15:50:31'),
(88, 29, 37, '[\"to\"]', '[\"for\"]', 'incorrect', 0, 1, '2021-02-16 15:50:31', '2021-02-16 15:50:31'),
(89, 33, 37, '[\"I didn\'t go to the cinema\\u00a0 last night.\\u00a0\"]', '[\"I didn\'t go to the cinema\\u00a0 last night.\\u00a0\"]', 'correct', 1, 1, '2021-02-16 15:50:31', '2021-02-16 15:50:31'),
(90, 35, 37, '[\"I haven\'t a big window in my room.\"]', '[\"I don\'t have a big window in my room.\"]', 'incorrect', 0, 1, '2021-02-16 15:50:31', '2021-02-16 15:50:31'),
(91, 36, 37, '[\"My mom hasn\'t perfect cooking skills.\"]', '[\"My mom doesn\'t have perfect cooking skills.\"]', 'incorrect', 0, 1, '2021-02-16 15:50:31', '2021-02-16 15:50:31'),
(92, 37, 37, '[null,null,null,null,null,null,null,null,null]', '[\"ill\",\"quiet\",\"difficult\",\"beautiful\",\"crowded\",\"intelligent\",\"rich\",\"friendly\",\"interesting\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 9, '2021-02-16 15:50:31', '2021-02-16 15:50:31'),
(93, 38, 37, '[null,null,null,null,null,null,null,null,null,null]', '[\"arrived\",\"want\",\"have been\",\"don\'t understand\",\"talk\",\"met\",\"came\",\"is doing\",\"needs\",\"is ringing\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 10, '2021-02-16 15:50:31', '2021-02-16 15:50:31'),
(94, 42, 37, '[null,null,null,null,null,null,null]', '[\"24\",\"13.20\",\"18.45\",\"11\",\"5.15\",\"8.20\",\"259\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 7, '2021-02-16 15:50:31', '2021-02-16 15:50:31'),
(95, 48, 37, '[\"really sorry\",\"have to\",null,null,null,null,null,null,null,null]', '[\"speaking\",\"this is\",\"really sorry\",\"have to\",\"Oh, dear\",\"did she\",\"don\'t worry\",\"could you help me\",\"shall I\",\"that would be\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 10, '2021-02-16 15:50:31', '2021-02-16 15:50:31'),
(96, 51, 37, '[\"see\"]', '[\"seeing\"]', 'incorrect', 0, 3, '2021-02-16 15:50:31', '2021-02-16 15:50:31'),
(97, 52, 37, '[\"want -- next\"]', '[\"wanted -- the following\"]', 'incorrect', 0, 3, '2021-02-16 15:50:31', '2021-02-16 15:50:31'),
(98, 53, 37, '[\"who\"]', '[\"which\"]', 'incorrect', 0, 3, '2021-02-16 15:50:31', '2021-02-16 15:50:31'),
(99, 54, 37, '[\"where\"]', '[\"where\"]', 'correct', 3, 3, '2021-02-16 15:50:31', '2021-02-16 15:50:31'),
(100, 56, 37, '[\"give up\"]', '[\"give up\"]', 'correct', 3, 3, '2021-02-16 15:50:31', '2021-02-16 15:50:31'),
(101, 61, 37, '[\"17\"]', '[\"17\"]', 'correct', 2, 2, '2021-02-16 15:50:31', '2021-02-16 15:50:31'),
(102, 63, 37, '[\"$20\"]', '[\"$20\"]', 'correct', 2, 2, '2021-02-16 15:50:31', '2021-02-16 15:50:31'),
(103, 67, 37, '[\"However\"]', '[\"Although\"]', 'incorrect', 0, 4, '2021-02-16 15:50:31', '2021-02-16 15:50:31'),
(104, 75, 37, '[null,null,null]', '[\"care\",\"enjoyable\",\"disappointment\"]', '[\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 6, '2021-02-16 15:50:31', '2021-02-16 15:50:31'),
(105, 76, 37, '[\"in Australia\"]', '[\"at a party\"]', 'incorrect', 0, 4, '2021-02-16 15:50:31', '2021-02-16 15:50:31'),
(106, 79, 37, '[\"the country\"]', '[\"the country\"]', 'correct', 4, 4, '2021-02-16 15:50:31', '2021-02-16 15:50:31'),
(107, 82, 37, '[null,null,null,null]', '[\"looting\",\"robbery\",\"burgle\",\"vandalize\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 8, '2021-02-16 15:50:31', '2021-02-16 15:50:31'),
(108, 83, 37, '[null,null,null,null]', '[\"politician\",\"economic\",\"environmental\",\"pollution\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 8, '2021-02-16 15:50:31', '2021-02-16 15:50:31'),
(109, 87, 37, '[null,null,null,null,null,null]', '[\"memorable\",\"scary\",\"overrated\",\"predictable\",\"gripping\",\"hilarious\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 12, '2021-02-16 15:50:31', '2021-02-16 15:50:31'),
(110, 90, 37, '[null,null,null,null,null,null,null,null,null,null,null]', '[\"sued\",\"damages\",\"spread\",\"crisis\",\"hostage\",\"released\",\"invaded\",\"troops\",\"flee\",\"attack\",\"outcry\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 30, '2021-02-16 15:50:31', '2021-02-16 15:50:31'),
(111, 1, 36, '[\"What\"]', '[\"What\"]', 'correct', 1, 1, '2021-02-16 16:29:42', '2021-02-16 16:29:42'),
(112, 2, 36, '[\"They\'re\"]', '[\"They\'re\"]', 'correct', 1, 1, '2021-02-16 16:29:42', '2021-02-16 16:29:42'),
(113, 3, 36, '[\"It\"]', '[\"It\"]', 'correct', 1, 1, '2021-02-16 16:29:42', '2021-02-16 16:29:42'),
(114, 5, 36, '[\"spell\"]', '[\"do you spell\"]', 'incorrect', 0, 1, '2021-02-16 16:29:42', '2021-02-16 16:29:42'),
(115, 6, 36, '[\"a\"]', '[\"--\"]', 'incorrect', 0, 1, '2021-02-16 16:29:42', '2021-02-16 16:29:42'),
(116, 8, 36, '[\"your father job\'s\"]', '[\"your father\'s job\"]', 'incorrect', 0, 1, '2021-02-16 16:29:42', '2021-02-16 16:29:42'),
(117, 9, 36, '[\"doesn\'t have\"]', '[\"doesn\'t have\"]', 'correct', 1, 1, '2021-02-16 16:29:42', '2021-02-16 16:29:42'),
(118, 12, 36, '[\"have\"]', '[\"do\"]', 'incorrect', 0, 1, '2021-02-16 16:29:42', '2021-02-16 16:29:42'),
(119, 18, 36, '[\"tallest\"]', '[\"taller\"]', 'incorrect', 0, 1, '2021-02-16 16:29:42', '2021-02-16 16:29:42'),
(120, 19, 36, '[\"more\"]', '[\"most\"]', 'incorrect', 0, 1, '2021-02-16 16:29:42', '2021-02-16 16:29:42'),
(121, 29, 36, '[\"to\"]', '[\"for\"]', 'incorrect', 0, 1, '2021-02-16 16:29:42', '2021-02-16 16:29:42'),
(122, 31, 36, 'how mane children.', '[]', '', 0, 1, '2021-02-16 16:29:42', '2021-02-16 16:29:42'),
(123, 33, 36, '[\"I not went to the cinema\\u00a0 last night.\\u00a0\"]', '[\"I didn\'t go to the cinema\\u00a0 last night.\\u00a0\"]', 'incorrect', 0, 1, '2021-02-16 16:29:42', '2021-02-16 16:29:42'),
(124, 35, 36, '[\"I haven\'t a big window in my room.\"]', '[\"I don\'t have a big window in my room.\"]', 'incorrect', 0, 1, '2021-02-16 16:29:42', '2021-02-16 16:29:42'),
(125, 37, 36, '[null,null,null,null,null,null,null,null,null]', '[\"ill\",\"quiet\",\"difficult\",\"beautiful\",\"crowded\",\"intelligent\",\"rich\",\"friendly\",\"interesting\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 9, '2021-02-16 16:29:42', '2021-02-16 16:29:42'),
(126, 38, 36, '[null,null,null,null,null,null,null,null,null,null]', '[\"arrived\",\"want\",\"have been\",\"don\'t understand\",\"talk\",\"met\",\"came\",\"is doing\",\"needs\",\"is ringing\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 10, '2021-02-16 16:29:42', '2021-02-16 16:29:42'),
(127, 42, 36, '[null,null,null,null,null,null,null]', '[\"24\",\"13.20\",\"18.45\",\"11\",\"5.15\",\"8.20\",\"259\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 7, '2021-02-16 16:29:42', '2021-02-16 16:29:42'),
(128, 48, 36, '[null,null,null,null,null,null,null,null,null,null]', '[\"speaking\",\"this is\",\"really sorry\",\"have to\",\"Oh, dear\",\"did she\",\"don\'t worry\",\"could you help me\",\"shall I\",\"that would be\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 10, '2021-02-16 16:29:42', '2021-02-16 16:29:42'),
(129, 67, 36, '[\"However\"]', '[\"Although\"]', 'incorrect', 0, 4, '2021-02-16 16:29:42', '2021-02-16 16:29:42'),
(130, 68, 36, '[\"Despite being tired\"]', '[\"Despite being tired\"]', 'correct', 4, 4, '2021-02-16 16:29:42', '2021-02-16 16:29:42'),
(131, 69, 36, '[\"am used to working\"]', '[\"am used to working\"]', 'correct', 4, 4, '2021-02-16 16:29:42', '2021-02-16 16:29:42'),
(132, 75, 36, '[null,null,null]', '[\"care\",\"enjoyable\",\"disappointment\"]', '[\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 6, '2021-02-16 16:29:42', '2021-02-16 16:29:42'),
(133, 82, 36, '[null,null,null,null]', '[\"looting\",\"robbery\",\"burgle\",\"vandalize\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 8, '2021-02-16 16:29:42', '2021-02-16 16:29:42'),
(134, 83, 36, '[null,null,null,null]', '[\"politician\",\"economic\",\"environmental\",\"pollution\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 8, '2021-02-16 16:29:42', '2021-02-16 16:29:42'),
(135, 87, 36, '[null,null,null,null,null,null]', '[\"memorable\",\"scary\",\"overrated\",\"predictable\",\"gripping\",\"hilarious\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 12, '2021-02-16 16:29:42', '2021-02-16 16:29:42'),
(136, 90, 36, '[null,null,null,null,null,null,null,null,null,null,null]', '[\"sued\",\"damages\",\"spread\",\"crisis\",\"hostage\",\"released\",\"invaded\",\"troops\",\"flee\",\"attack\",\"outcry\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 30, '2021-02-16 16:29:42', '2021-02-16 16:29:42'),
(137, 1, 27, '[\"What\"]', '[\"What\"]', 'correct', 1, 1, '2021-02-16 16:41:57', '2021-02-16 16:41:57'),
(138, 2, 27, '[\"They\'re\"]', '[\"They\'re\"]', 'correct', 1, 1, '2021-02-16 16:41:57', '2021-02-16 16:41:57'),
(139, 3, 27, '[\"It\"]', '[\"It\"]', 'correct', 1, 1, '2021-02-16 16:41:57', '2021-02-16 16:41:57'),
(140, 5, 27, '[\"do you spell\"]', '[\"do you spell\"]', 'correct', 1, 1, '2021-02-16 16:41:57', '2021-02-16 16:41:57'),
(141, 6, 27, '[\"one\"]', '[\"--\"]', 'incorrect', 0, 1, '2021-02-16 16:41:57', '2021-02-16 16:41:57'),
(142, 7, 27, '[\"Is Mr. Mike a teacher?\"]', '[\"Is Mr. Mike a teacher?\"]', 'correct', 1, 1, '2021-02-16 16:41:57', '2021-02-16 16:41:57'),
(143, 8, 27, '[\"your father\'s job\"]', '[\"your father\'s job\"]', 'correct', 1, 1, '2021-02-16 16:41:57', '2021-02-16 16:41:57'),
(144, 9, 27, '[\"doesn\'t have\"]', '[\"doesn\'t have\"]', 'correct', 1, 1, '2021-02-16 16:41:57', '2021-02-16 16:41:57'),
(145, 10, 27, '[\"works\"]', '[\"works\"]', 'correct', 1, 1, '2021-02-16 16:41:57', '2021-02-16 16:41:57'),
(146, 11, 27, '[\"We do not often go to the cinema.\"]', '[\"We do not often go to the cinema.\"]', 'correct', 1, 1, '2021-02-16 16:41:57', '2021-02-16 16:41:57'),
(147, 12, 27, '[\"do\"]', '[\"do\"]', 'correct', 1, 1, '2021-02-16 16:41:57', '2021-02-16 16:41:57'),
(148, 14, 27, '[\"ever\"]', '[\"ever\"]', 'correct', 1, 1, '2021-02-16 16:41:57', '2021-02-16 16:41:57'),
(149, 18, 27, '[\"taller\"]', '[\"taller\"]', 'correct', 1, 1, '2021-02-16 16:41:57', '2021-02-16 16:41:57'),
(150, 19, 27, '[\"most\"]', '[\"most\"]', 'correct', 1, 1, '2021-02-16 16:41:57', '2021-02-16 16:41:57'),
(151, 23, 27, '[\"more friendly\"]', '[\"friendlier\"]', 'incorrect', 0, 1, '2021-02-16 16:41:57', '2021-02-16 16:41:57'),
(152, 28, 27, '[\"on\"]', '[\"on\"]', 'correct', 1, 1, '2021-02-16 16:41:57', '2021-02-16 16:41:57'),
(153, 29, 27, '[\"for\"]', '[\"for\"]', 'correct', 1, 1, '2021-02-16 16:41:57', '2021-02-16 16:41:57'),
(154, 30, 27, 'where does sally\'s grandmother live?', '[]', '', 0, 1, '2021-02-16 16:41:57', '2021-02-16 16:41:57'),
(155, 31, 27, 'how many children did Tom has?\r\nhow many children do tom has?', '[]', '', 0, 1, '2021-02-16 16:41:57', '2021-02-16 16:41:57'),
(156, 32, 27, 'when do they get up ?\r\nat what time do they get up ?', '[]', '', 0, 1, '2021-02-16 16:41:57', '2021-02-16 16:41:57'),
(157, 33, 27, '[\"I didn\'t go to the cinema\\u00a0 last night.\\u00a0\"]', '[\"I didn\'t go to the cinema\\u00a0 last night.\\u00a0\"]', 'correct', 1, 1, '2021-02-16 16:41:57', '2021-02-16 16:41:57'),
(158, 34, 27, '[\"They haven\'t become very rich.\"]', '[\"They haven\'t become very rich.\"]', 'correct', 1, 1, '2021-02-16 16:41:57', '2021-02-16 16:41:57'),
(159, 35, 27, '[\"I don\'t have a big window in my room.\"]', '[\"I don\'t have a big window in my room.\"]', 'correct', 1, 1, '2021-02-16 16:41:57', '2021-02-16 16:41:57'),
(160, 36, 27, '[\"My mom doesn\'t have perfect cooking skills.\"]', '[\"My mom doesn\'t have perfect cooking skills.\"]', 'correct', 1, 1, '2021-02-16 16:41:57', '2021-02-16 16:41:57'),
(161, 37, 27, '[\"ill\",\"quiet\",\"difficult\",\"beautiful\",\"crowded\",\"intelligent\",\"rich\",\"friendly\",\"interesting\"]', '[\"ill\",\"quiet\",\"difficult\",\"beautiful\",\"crowded\",\"intelligent\",\"rich\",\"friendly\",\"interesting\"]', '[\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\"]', 9, 9, '2021-02-16 16:41:57', '2021-02-16 16:41:57'),
(162, 38, 27, '[\"arrived\",\"will want\",\"have you ever been\",\"don\'t understand\",\"are talking\",\"have met\",\"came\",\"is doing\",\"needs\",\"is ringing\"]', '[\"arrived\",\"want\",\"have been\",\"don\'t understand\",\"talk\",\"met\",\"came\",\"is doing\",\"needs\",\"is ringing\"]', '[\"correct\",\"incorrect\",\"incorrect\",\"correct\",\"incorrect\",\"incorrect\",\"correct\",\"correct\",\"correct\",\"correct\"]', 0, 10, '2021-02-16 16:41:57', '2021-02-16 16:41:57'),
(163, 42, 27, '[\"24th\",\"13:20\",\"18:45\",\"11th\",\"5:15 AM\",\"8:20 AM\",\"259 pounds\"]', '[\"24\",\"13.20\",\"18.45\",\"11\",\"5.15\",\"8.20\",\"259\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 7, '2021-02-16 16:41:57', '2021-02-16 16:41:57'),
(164, 48, 27, '[\"Speaking\",\"this is\",\"really sorry\",\"have to\",\"Oh, Dear\",\"Did She\",\"don\'t worry\",\"could you help me\",\"shall I\",\"that would be\"]', '[\"speaking\",\"this is\",\"really sorry\",\"have to\",\"Oh, dear\",\"did she\",\"don\'t worry\",\"could you help me\",\"shall I\",\"that would be\"]', '[\"incorrect\",\"correct\",\"correct\",\"correct\",\"incorrect\",\"incorrect\",\"correct\",\"correct\",\"correct\",\"correct\"]', 0, 10, '2021-02-16 16:41:57', '2021-02-16 16:41:57'),
(165, 49, 27, '[\"would accept\"]', '[\"would accept\"]', 'correct', 3, 3, '2021-02-16 16:41:57', '2021-02-16 16:41:57'),
(166, 50, 27, '[\"will pass\"]', '[\"will pass\"]', 'correct', 3, 3, '2021-02-16 16:41:57', '2021-02-16 16:41:57'),
(167, 51, 27, '[\"seeing\"]', '[\"seeing\"]', 'correct', 3, 3, '2021-02-16 16:41:57', '2021-02-16 16:41:57'),
(168, 52, 27, '[\"wants -- next\"]', '[\"wanted -- the following\"]', 'incorrect', 0, 3, '2021-02-16 16:41:57', '2021-02-16 16:41:57'),
(169, 53, 27, '[\"which\"]', '[\"which\"]', 'correct', 3, 3, '2021-02-16 16:41:57', '2021-02-16 16:41:57'),
(170, 54, 27, '[\"where\"]', '[\"where\"]', 'correct', 3, 3, '2021-02-16 16:41:57', '2021-02-16 16:41:57'),
(171, 55, 27, '[\"have been fired\"]', '[\"were fired\"]', 'incorrect', 0, 3, '2021-02-16 16:41:57', '2021-02-16 16:41:57'),
(172, 56, 27, '[\"give up\"]', '[\"give up\"]', 'correct', 3, 3, '2021-02-16 16:41:57', '2021-02-16 16:41:57'),
(173, 57, 27, '[\"put up with\"]', '[\"put up with\"]', 'correct', 3, 3, '2021-02-16 16:41:57', '2021-02-16 16:41:57'),
(174, 58, 27, '[\"impolite\"]', '[\"impolite\"]', 'correct', 3, 3, '2021-02-16 16:41:57', '2021-02-16 16:41:57'),
(175, 59, 27, '[\"niece\"]', '[\"niece\"]', 'correct', 3, 3, '2021-02-16 16:41:57', '2021-02-16 16:41:57'),
(176, 60, 27, '[\"mother-in-law\"]', '[\"mother-in-law\"]', 'correct', 3, 3, '2021-02-16 16:41:57', '2021-02-16 16:41:57'),
(177, 61, 27, '[\"17\"]', '[\"17\"]', 'correct', 2, 2, '2021-02-16 16:41:57', '2021-02-16 16:41:57'),
(178, 62, 27, '[\"20\"]', '[\"20\"]', 'correct', 2, 2, '2021-02-16 16:41:57', '2021-02-16 16:41:57'),
(179, 63, 27, '[\"$20\"]', '[\"$20\"]', 'correct', 2, 2, '2021-02-16 16:41:57', '2021-02-16 16:41:57'),
(180, 64, 27, '[\"10\"]', '[\"10\"]', 'correct', 2, 2, '2021-02-16 16:41:57', '2021-02-16 16:41:57'),
(181, 65, 27, '[\"on his body\"]', '[\"on his body\"]', 'correct', 2, 2, '2021-02-16 16:41:57', '2021-02-16 16:41:57'),
(182, 66, 27, '[\"stomach problems\"]', '[\"stomach problems\"]', 'correct', 2, 2, '2021-02-16 16:41:57', '2021-02-16 16:41:57'),
(183, 67, 27, '[\"Although\"]', '[\"Although\"]', 'correct', 4, 4, '2021-02-16 16:41:57', '2021-02-16 16:41:57'),
(184, 68, 27, '[\"Despite being tired\"]', '[\"Despite being tired\"]', 'correct', 4, 4, '2021-02-16 16:41:57', '2021-02-16 16:41:57'),
(185, 69, 27, '[\"used to work\"]', '[\"am used to working\"]', 'incorrect', 0, 4, '2021-02-16 16:41:57', '2021-02-16 16:41:57'),
(186, 71, 27, '[\"forgot\"]', '[\"had forgotten\"]', 'incorrect', 0, 4, '2021-02-16 16:41:57', '2021-02-16 16:41:57'),
(187, 72, 27, '[\"keep in touch with\"]', '[\"keep in touch with\"]', 'correct', 4, 4, '2021-02-16 16:41:57', '2021-02-16 16:41:57'),
(188, 73, 27, '[\"have splitted\"]', '[\"split\"]', 'incorrect', 0, 4, '2021-02-16 16:41:57', '2021-02-16 16:41:57'),
(189, 74, 27, '[\"not to\"]', '[\"not to\"]', 'correct', 4, 4, '2021-02-16 16:41:57', '2021-02-16 16:41:57'),
(190, 75, 27, '[\"care \\/ caring\",\"enjoyable\",\"disappointment\"]', '[\"care\",\"enjoyable\",\"disappointment\"]', '[\"incorrect\",\"correct\",\"correct\"]', 0, 6, '2021-02-16 16:41:57', '2021-02-16 16:41:57'),
(191, 76, 27, '[\"at a party\"]', '[\"at a party\"]', 'correct', 4, 4, '2021-02-16 16:41:57', '2021-02-16 16:41:57'),
(192, 77, 27, '[\"lost her passport\"]', '[\"lost her passport\"]', 'correct', 4, 4, '2021-02-16 16:41:57', '2021-02-16 16:41:57'),
(193, 78, 27, '[\"lost her job\"]', '[\"lost her job\"]', 'correct', 4, 4, '2021-02-16 16:41:57', '2021-02-16 16:41:57'),
(194, 79, 27, '[\"the country\"]', '[\"the country\"]', 'correct', 4, 4, '2021-02-16 16:41:57', '2021-02-16 16:41:57'),
(195, 80, 27, '[\"an aggressive\"]', '[\"an aggressive\"]', 'correct', 4, 4, '2021-02-16 16:41:57', '2021-02-16 16:41:57'),
(196, 81, 27, '[\"some boxing gloves\"]', '[\"a bike\"]', 'incorrect', 0, 4, '2021-02-16 16:41:57', '2021-02-16 16:41:57'),
(197, 82, 27, '[\"loot\",\"robbery\",\"burglarize\\/burgle\",\"vandalized\"]', '[\"looting\",\"robbery\",\"burgle\",\"vandalize\"]', '[\"incorrect\",\"correct\",\"incorrect\",\"incorrect\"]', 0, 8, '2021-02-16 16:41:57', '2021-02-16 16:41:57'),
(198, 83, 27, '[\"politician\",\"economical\",\"Environmental\",\"pollutant\"]', '[\"politician\",\"economic\",\"environmental\",\"pollution\"]', '[\"correct\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 8, '2021-02-16 16:41:57', '2021-02-16 16:41:57'),
(199, 84, 27, '[\"fire someone form a job\"]', '[\"fire someone form a job\"]', 'correct', 4, 4, '2021-02-16 16:41:57', '2021-02-16 16:41:57'),
(200, 85, 27, '[\"a lot of money\"]', '[]', 'incorrect', 0, 4, '2021-02-16 16:41:57', '2021-02-16 16:41:57'),
(201, 86, 27, '[\"have a difficulty in speaking\"]', '[\"have a difficulty in speaking\"]', 'correct', 4, 4, '2021-02-16 16:41:57', '2021-02-16 16:41:57'),
(202, 87, 27, '[\"memorable\",\"scary\",\"overrated\",\"predictable\",\"gripping\",\"hilarious\"]', '[\"memorable\",\"scary\",\"overrated\",\"predictable\",\"gripping\",\"hilarious\"]', '[\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\"]', 12, 12, '2021-02-16 16:41:57', '2021-02-16 16:41:57'),
(203, 90, 27, '[\"damages\",\"sued\",\"spread\",\"crisis\",\"hostage\",\"released\",\"invaded\",\"troops\",\"flee\",\"attack\",\"outcry\"]', '[\"sued\",\"damages\",\"spread\",\"crisis\",\"hostage\",\"released\",\"invaded\",\"troops\",\"flee\",\"attack\",\"outcry\"]', '[\"incorrect\",\"incorrect\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\"]', 0, 30, '2021-02-16 16:41:57', '2021-02-16 16:41:57'),
(204, 91, 27, '[\"False\"]', '[\"False\"]', 'correct', 5, 5, '2021-02-16 16:41:57', '2021-02-16 16:41:57'),
(205, 92, 27, '[\"False\"]', '[\"True\"]', 'incorrect', 0, 5, '2021-02-16 16:41:57', '2021-02-16 16:41:57'),
(206, 93, 27, '[\"False\"]', '[\"False\"]', 'correct', 5, 5, '2021-02-16 16:41:57', '2021-02-16 16:41:57'),
(207, 94, 27, '[\"True\"]', '[\"True\"]', 'correct', 5, 5, '2021-02-16 16:41:57', '2021-02-16 16:41:57'),
(208, 95, 27, '[\"three times\"]', '[\"three times\"]', 'correct', 5, 5, '2021-02-16 16:41:57', '2021-02-16 16:41:57'),
(209, 96, 27, 'Dear Mr. John,\r\n\r\nI am sending this email regards the overview of our company\'s policy,\r\n\r\nFirst of all we should mention the amazing effort and support you always provide us with in every step in our career path in the place, second atmosphere whole managers provide us with in every detail, I would like only mention the we can have some adjustments in working conditions like the working hours can be more flexible up to 15 minutes maximum late, also the working place needs more space and rooms for healthy outcome, it would be perfect having a break area in the place, also management shall insist on sticking to the dress code rules, meeting deadline rules, cleanliness of the place, and organizing work flow.\r\n\r\nThank you for allowing me to express my point of view and to share my thoughts with the management, I will always do my best for our company.\r\n\r\nRegards,\r\nNora Osama,\r\nService Assistant', '[]', '', 0, 20, '2021-02-16 16:41:57', '2021-02-16 16:41:57'),
(222, 1, 35, '[\"What\"]', '[\"What\"]', 'correct', 1, 1, '2021-02-16 19:25:20', '2021-02-16 19:25:20'),
(223, 2, 35, '[\"They\'re\"]', '[\"They\'re\"]', 'correct', 1, 1, '2021-02-16 19:25:20', '2021-02-16 19:25:20'),
(224, 3, 35, '[\"It\"]', '[\"It\"]', 'correct', 1, 1, '2021-02-16 19:25:20', '2021-02-16 19:25:20'),
(225, 5, 35, '[\"do you spell\"]', '[\"do you spell\"]', 'correct', 1, 1, '2021-02-16 19:25:20', '2021-02-16 19:25:20'),
(226, 6, 35, '[\"--\"]', '[\"--\"]', 'correct', 1, 1, '2021-02-16 19:25:20', '2021-02-16 19:25:20'),
(227, 7, 35, '[\"Is Mr. Mike a teacher?\"]', '[\"Is Mr. Mike a teacher?\"]', 'correct', 1, 1, '2021-02-16 19:25:20', '2021-02-16 19:25:20'),
(228, 8, 35, '[\"your father\'s job\"]', '[\"your father\'s job\"]', 'correct', 1, 1, '2021-02-16 19:25:20', '2021-02-16 19:25:20'),
(229, 9, 35, '[\"doesn\'t have\"]', '[\"doesn\'t have\"]', 'correct', 1, 1, '2021-02-16 19:25:20', '2021-02-16 19:25:20'),
(230, 10, 35, '[\"works\"]', '[\"works\"]', 'correct', 1, 1, '2021-02-16 19:25:20', '2021-02-16 19:25:20'),
(231, 11, 35, '[\"We do not often go to the cinema.\"]', '[\"We do not often go to the cinema.\"]', 'correct', 1, 1, '2021-02-16 19:25:20', '2021-02-16 19:25:20'),
(232, 12, 35, '[\"do\"]', '[\"do\"]', 'correct', 1, 1, '2021-02-16 19:25:20', '2021-02-16 19:25:20'),
(233, 14, 35, '[\"ever\"]', '[\"ever\"]', 'correct', 1, 1, '2021-02-16 19:25:20', '2021-02-16 19:25:20'),
(234, 18, 35, '[\"taller\"]', '[\"taller\"]', 'correct', 1, 1, '2021-02-16 19:25:20', '2021-02-16 19:25:20'),
(235, 19, 35, '[\"most\"]', '[\"most\"]', 'correct', 1, 1, '2021-02-16 19:25:20', '2021-02-16 19:25:20'),
(236, 23, 35, '[\"more friendly\"]', '[\"friendlier\"]', 'incorrect', 0, 1, '2021-02-16 19:25:20', '2021-02-16 19:25:20'),
(237, 28, 35, '[\"on\"]', '[\"on\"]', 'correct', 1, 1, '2021-02-16 19:25:20', '2021-02-16 19:25:20'),
(238, 29, 35, '[\"for\"]', '[\"for\"]', 'correct', 1, 1, '2021-02-16 19:25:20', '2021-02-16 19:25:20'),
(239, 30, 35, 'Where does Sally\'s grandmother live?', '[]', '', 0, 1, '2021-02-16 19:25:20', '2021-02-16 19:25:20'),
(240, 31, 35, 'How many children does Tom have?', '[]', '', 0, 1, '2021-02-16 19:25:20', '2021-02-16 19:25:20'),
(241, 32, 35, 'When do they get up every morning?', '[]', '', 0, 1, '2021-02-16 19:25:20', '2021-02-16 19:25:20'),
(242, 33, 35, '[\"I didn\'t go to the cinema\\u00a0 last night.\\u00a0\"]', '[\"I didn\'t go to the cinema\\u00a0 last night.\\u00a0\"]', 'correct', 1, 1, '2021-02-16 19:25:20', '2021-02-16 19:25:20'),
(243, 34, 35, '[\"They haven\'t become very rich.\"]', '[\"They haven\'t become very rich.\"]', 'correct', 1, 1, '2021-02-16 19:25:20', '2021-02-16 19:25:20'),
(244, 35, 35, '[\"I don\'t have a big window in my room.\"]', '[\"I don\'t have a big window in my room.\"]', 'correct', 1, 1, '2021-02-16 19:25:20', '2021-02-16 19:25:20'),
(245, 36, 35, '[\"My mom doesn\'t have perfect cooking skills.\"]', '[\"My mom doesn\'t have perfect cooking skills.\"]', 'correct', 1, 1, '2021-02-16 19:25:20', '2021-02-16 19:25:20'),
(246, 37, 35, '[\"ill\",\"quite\",\"difficult\",\"beautiful\",\"crowded\",\"intelligent\",\"rich\",\"friendly\",\"interesting\"]', '[\"ill\",\"quiet\",\"difficult\",\"beautiful\",\"crowded\",\"intelligent\",\"rich\",\"friendly\",\"interesting\"]', '[\"correct\",\"incorrect\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\"]', 0, 9, '2021-02-16 19:25:20', '2021-02-16 19:25:20'),
(247, 38, 35, '[\"arrived\",\"want\",\"Have, gone\",\"don\'t understand\",\"talk\",\"met\",\"came\",\"is doing\",\"needs\",\"is ringing\"]', '[\"arrived\",\"want\",\"have been\",\"don\'t understand\",\"talk\",\"met\",\"came\",\"is doing\",\"needs\",\"is ringing\"]', '[\"correct\",\"correct\",\"incorrect\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\"]', 0, 10, '2021-02-16 19:25:20', '2021-02-16 19:25:20'),
(248, 42, 35, '[\"24\",\"13.20\",\"18.45\",\"11\",\"5.15\",\"8.20\",\"259\"]', '[\"24\",\"13.20\",\"18.45\",\"11\",\"5.15\",\"8.20\",\"259\"]', '[\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\"]', 7, 7, '2021-02-16 19:25:20', '2021-02-16 19:25:20'),
(249, 48, 35, '[\"speaking\",\"this is\",\"really sorry\",\"have to\",\"Oh, dear\",\"did she\",\"don\'t worry\",\"could you help me\",\"shall I\",\"that would be\"]', '[\"speaking\",\"this is\",\"really sorry\",\"have to\",\"Oh, dear\",\"did she\",\"don\'t worry\",\"could you help me\",\"shall I\",\"that would be\"]', '[\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\"]', 10, 10, '2021-02-16 19:25:20', '2021-02-16 19:25:20'),
(250, 49, 35, '[\"will accept\"]', '[\"would accept\"]', 'incorrect', 0, 3, '2021-02-16 19:25:20', '2021-02-16 19:25:20'),
(251, 50, 35, '[\"will pass\"]', '[\"will pass\"]', 'correct', 3, 3, '2021-02-16 19:25:20', '2021-02-16 19:25:20'),
(252, 51, 35, '[\"see\"]', '[\"seeing\"]', 'incorrect', 0, 3, '2021-02-16 19:25:20', '2021-02-16 19:25:20'),
(253, 52, 35, '[\"wants -- next\"]', '[\"wanted -- the following\"]', 'incorrect', 0, 3, '2021-02-16 19:25:20', '2021-02-16 19:25:20'),
(254, 53, 35, '[\"which\"]', '[\"which\"]', 'correct', 3, 3, '2021-02-16 19:25:20', '2021-02-16 19:25:20'),
(255, 54, 35, '[\"where\"]', '[\"where\"]', 'correct', 3, 3, '2021-02-16 19:25:20', '2021-02-16 19:25:20'),
(256, 55, 35, '[\"were fired\"]', '[\"were fired\"]', 'correct', 3, 3, '2021-02-16 19:25:20', '2021-02-16 19:25:20'),
(257, 56, 35, '[\"give up\"]', '[\"give up\"]', 'correct', 3, 3, '2021-02-16 19:25:20', '2021-02-16 19:25:20'),
(258, 57, 35, '[\"put up with\"]', '[\"put up with\"]', 'correct', 3, 3, '2021-02-16 19:25:20', '2021-02-16 19:25:20'),
(259, 58, 35, '[\"impolite\"]', '[\"impolite\"]', 'correct', 3, 3, '2021-02-16 19:25:20', '2021-02-16 19:25:20'),
(260, 59, 35, '[\"niece\"]', '[\"niece\"]', 'correct', 3, 3, '2021-02-16 19:25:20', '2021-02-16 19:25:20'),
(261, 60, 35, '[\"mother-in-law\"]', '[\"mother-in-law\"]', 'correct', 3, 3, '2021-02-16 19:25:21', '2021-02-16 19:25:21'),
(262, 61, 35, '[\"17\"]', '[\"17\"]', 'correct', 2, 2, '2021-02-16 19:25:21', '2021-02-16 19:25:21'),
(263, 62, 35, '[\"20\"]', '[\"20\"]', 'correct', 2, 2, '2021-02-16 19:25:21', '2021-02-16 19:25:21'),
(264, 63, 35, '[\"$20\"]', '[\"$20\"]', 'correct', 2, 2, '2021-02-16 19:25:21', '2021-02-16 19:25:21'),
(265, 64, 35, '[\"10\"]', '[\"10\"]', 'correct', 2, 2, '2021-02-16 19:25:21', '2021-02-16 19:25:21'),
(266, 65, 35, '[\"on his body\"]', '[\"on his body\"]', 'correct', 2, 2, '2021-02-16 19:25:21', '2021-02-16 19:25:21'),
(267, 66, 35, '[\"stomach problems\"]', '[\"stomach problems\"]', 'correct', 2, 2, '2021-02-16 19:25:21', '2021-02-16 19:25:21'),
(268, 67, 35, '[\"Although\"]', '[\"Although\"]', 'correct', 4, 4, '2021-02-16 19:25:21', '2021-02-16 19:25:21'),
(269, 68, 35, '[\"Although being tired\"]', '[\"Despite being tired\"]', 'incorrect', 0, 4, '2021-02-16 19:25:21', '2021-02-16 19:25:21'),
(270, 69, 35, '[\"am used to work\"]', '[\"am used to working\"]', 'incorrect', 0, 4, '2021-02-16 19:25:21', '2021-02-16 19:25:21'),
(271, 71, 35, '[\"have forgotten\"]', '[\"had forgotten\"]', 'incorrect', 0, 4, '2021-02-16 19:25:21', '2021-02-16 19:25:21'),
(272, 72, 35, '[\"keep in touch with\"]', '[\"keep in touch with\"]', 'correct', 4, 4, '2021-02-16 19:25:21', '2021-02-16 19:25:21'),
(273, 73, 35, '[\"splitted\"]', '[\"split\"]', 'incorrect', 0, 4, '2021-02-16 19:25:21', '2021-02-16 19:25:21'),
(274, 74, 35, '[\"not to\"]', '[\"not to\"]', 'correct', 4, 4, '2021-02-16 19:25:21', '2021-02-16 19:25:21'),
(275, 75, 35, '[\"caring\",\"enjoyably\",\"disappointment\"]', '[\"care\",\"enjoyable\",\"disappointment\"]', '[\"incorrect\",\"incorrect\",\"correct\"]', 0, 6, '2021-02-16 19:25:21', '2021-02-16 19:25:21'),
(276, 76, 35, '[\"at a party\"]', '[\"at a party\"]', 'correct', 4, 4, '2021-02-16 19:25:21', '2021-02-16 19:25:21'),
(277, 77, 35, '[\"lost her passport\"]', '[\"lost her passport\"]', 'correct', 4, 4, '2021-02-16 19:25:21', '2021-02-16 19:25:21'),
(278, 78, 35, '[\"lost her job\"]', '[\"lost her job\"]', 'correct', 4, 4, '2021-02-16 19:25:21', '2021-02-16 19:25:21'),
(279, 79, 35, '[\"the country\"]', '[\"the country\"]', 'correct', 4, 4, '2021-02-16 19:25:21', '2021-02-16 19:25:21'),
(280, 80, 35, '[\"an aggressive\"]', '[\"an aggressive\"]', 'correct', 4, 4, '2021-02-16 19:25:21', '2021-02-16 19:25:21'),
(281, 81, 35, '[\"some boxing gloves\"]', '[\"a bike\"]', 'incorrect', 0, 4, '2021-02-16 19:25:21', '2021-02-16 19:25:21'),
(282, 82, 35, '[null,\"robbery\",\"burgle\",null]', '[\"looting\",\"robbery\",\"burgle\",\"vandalize\"]', '[\"incorrect\",\"correct\",\"correct\",\"incorrect\"]', 0, 8, '2021-02-16 19:25:21', '2021-02-16 19:25:21'),
(283, 83, 35, '[\"politician\",\"economical\",\"enviromental\",null]', '[\"politician\",\"economic\",\"environmental\",\"pollution\"]', '[\"correct\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 8, '2021-02-16 19:25:21', '2021-02-16 19:25:21'),
(284, 84, 35, '[\"fire someone form a job\"]', '[\"fire someone form a job\"]', 'correct', 4, 4, '2021-02-16 19:25:21', '2021-02-16 19:25:21'),
(285, 85, 35, '[\"a lot of money\"]', '[]', 'incorrect', 0, 4, '2021-02-16 19:25:21', '2021-02-16 19:25:21'),
(286, 86, 35, '[\"have a difficulty in speaking\"]', '[\"have a difficulty in speaking\"]', 'correct', 4, 4, '2021-02-16 19:25:21', '2021-02-16 19:25:21'),
(287, 87, 35, '[\"memorable\",\"scary\",\"overrated\",\"predictable\",\"gripping\",\"hilarious\"]', '[\"memorable\",\"scary\",\"overrated\",\"predictable\",\"gripping\",\"hilarious\"]', '[\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\"]', 12, 12, '2021-02-16 19:25:21', '2021-02-16 19:25:21'),
(288, 90, 35, '[null,null,\"spread\",null,null,\"released\",\"invaded\",\"troops\",null,null,null]', '[\"sued\",\"damages\",\"spread\",\"crisis\",\"hostage\",\"released\",\"invaded\",\"troops\",\"flee\",\"attack\",\"outcry\"]', '[\"incorrect\",\"incorrect\",\"correct\",\"incorrect\",\"incorrect\",\"correct\",\"correct\",\"correct\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 30, '2021-02-16 19:25:21', '2021-02-16 19:25:21'),
(289, 91, 35, '[\"False\"]', '[\"False\"]', 'correct', 5, 5, '2021-02-16 19:25:21', '2021-02-16 19:25:21'),
(290, 92, 35, '[\"True\"]', '[\"True\"]', 'correct', 5, 5, '2021-02-16 19:25:21', '2021-02-16 19:25:21'),
(291, 93, 35, '[\"False\"]', '[\"False\"]', 'correct', 5, 5, '2021-02-16 19:25:21', '2021-02-16 19:25:21'),
(292, 94, 35, '[\"True\"]', '[\"True\"]', 'correct', 5, 5, '2021-02-16 19:25:21', '2021-02-16 19:25:21'),
(293, 95, 35, '[\"three times\"]', '[\"three times\"]', 'correct', 5, 5, '2021-02-16 19:25:21', '2021-02-16 19:25:21'),
(294, 96, 35, 'Dear John,\r\n\r\nI am writing you this email to let you know my opinion about our company\'s policy & working conditions.\r\nFirst of all I think your company has a very good working environment. I can see that the company is keen about employees self development, its a very good step that they offered an English course to enhance our English level . Also it was a good idea when they got us gym offers for free so we can practice some sports which improves our body & mental health.\r\nCollogues are very friendly & they are always willing to help & collaborate for the sake of the company.\r\nAlso at Corona peak the idea of working from home made us less worried about our health & families while working efficiently.\r\nOn the other side I see we can improve our internal communication by developing an internal news letter so all employees can easily see the success & new projects coming to our company.\r\nAlso I wish one day that our company gives us more flexible working hours.\r\n\r\nThanks', '[]', '', 0, 20, '2021-02-16 19:25:21', '2021-02-16 19:25:21'),
(295, 1, 4, '[\"What\"]', '[\"What\"]', 'correct', 1, 1, '2021-02-16 19:48:44', '2021-02-16 19:48:44'),
(296, 2, 4, '[\"They\'re\"]', '[\"They\'re\"]', 'correct', 1, 1, '2021-02-16 19:48:44', '2021-02-16 19:48:44'),
(297, 3, 4, '[\"It\"]', '[\"It\"]', 'correct', 1, 1, '2021-02-16 19:48:44', '2021-02-16 19:48:44'),
(298, 5, 4, '[\"do you spell\"]', '[\"do you spell\"]', 'correct', 1, 1, '2021-02-16 19:48:44', '2021-02-16 19:48:44'),
(299, 6, 4, '[\"--\"]', '[\"--\"]', 'correct', 1, 1, '2021-02-16 19:48:44', '2021-02-16 19:48:44'),
(300, 7, 4, '[\"Is Mr. Mike a teacher?\"]', '[\"Is Mr. Mike a teacher?\"]', 'correct', 1, 1, '2021-02-16 19:48:44', '2021-02-16 19:48:44'),
(301, 8, 4, '[\"your father\'s job\"]', '[\"your father\'s job\"]', 'correct', 1, 1, '2021-02-16 19:48:44', '2021-02-16 19:48:44'),
(302, 9, 4, '[\"hasn\'t\"]', '[\"doesn\'t have\"]', 'incorrect', 0, 1, '2021-02-16 19:48:44', '2021-02-16 19:48:44'),
(303, 10, 4, '[\"works\"]', '[\"works\"]', 'correct', 1, 1, '2021-02-16 19:48:44', '2021-02-16 19:48:44'),
(304, 11, 4, '[\"We often do not go to the cinema.\"]', '[\"We do not often go to the cinema.\"]', 'incorrect', 0, 1, '2021-02-16 19:48:44', '2021-02-16 19:48:44'),
(305, 12, 4, '[\"do\"]', '[\"do\"]', 'correct', 1, 1, '2021-02-16 19:48:44', '2021-02-16 19:48:44'),
(306, 14, 4, '[\"ever\"]', '[\"ever\"]', 'correct', 1, 1, '2021-02-16 19:48:44', '2021-02-16 19:48:44'),
(307, 18, 4, '[\"taller\"]', '[\"taller\"]', 'correct', 1, 1, '2021-02-16 19:48:44', '2021-02-16 19:48:44'),
(308, 19, 4, '[\"most\"]', '[\"most\"]', 'correct', 1, 1, '2021-02-16 19:48:44', '2021-02-16 19:48:44'),
(309, 23, 4, '[\"more friendly\"]', '[\"friendlier\"]', 'incorrect', 0, 1, '2021-02-16 19:48:44', '2021-02-16 19:48:44'),
(310, 28, 4, '[\"on\"]', '[\"on\"]', 'correct', 1, 1, '2021-02-16 19:48:44', '2021-02-16 19:48:44'),
(311, 29, 4, '[\"for\"]', '[\"for\"]', 'correct', 1, 1, '2021-02-16 19:48:44', '2021-02-16 19:48:44'),
(312, 30, 4, 'Where does Sally\'s grandmother live?', '[]', '', 0, 1, '2021-02-16 19:48:44', '2021-02-16 19:48:44'),
(313, 31, 4, 'How Many Children did tom Have ?', '[]', '', 0, 1, '2021-02-16 19:48:44', '2021-02-16 19:48:44'),
(314, 32, 4, 'When do they get up every morning?', '[]', '', 0, 1, '2021-02-16 19:48:44', '2021-02-16 19:48:44'),
(315, 33, 4, '[\"I didn\'t go to the cinema\\u00a0 last night.\\u00a0\"]', '[\"I didn\'t go to the cinema\\u00a0 last night.\\u00a0\"]', 'correct', 1, 1, '2021-02-16 19:48:44', '2021-02-16 19:48:44'),
(316, 34, 4, '[\"They don\'t have become very rich.\"]', '[\"They haven\'t become very rich.\"]', 'incorrect', 0, 1, '2021-02-16 19:48:44', '2021-02-16 19:48:44'),
(317, 35, 4, '[\"I don\'t have a big window in my room.\"]', '[\"I don\'t have a big window in my room.\"]', 'correct', 1, 1, '2021-02-16 19:48:44', '2021-02-16 19:48:44'),
(318, 36, 4, '[\"My mom doesn\'t have perfect cooking skills.\"]', '[\"My mom doesn\'t have perfect cooking skills.\"]', 'correct', 1, 1, '2021-02-16 19:48:44', '2021-02-16 19:48:44'),
(319, 37, 4, '[\"ill\",\"quiet\",\"difficult\",\"beautiful\",\"crowded\",\"intelligent\",\"rich\",\"Frindlly\",null]', '[\"ill\",\"quiet\",\"difficult\",\"beautiful\",\"crowded\",\"intelligent\",\"rich\",\"friendly\",\"interesting\"]', '[\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"incorrect\",\"incorrect\"]', 0, 9, '2021-02-16 19:48:44', '2021-02-16 19:48:44'),
(320, 38, 4, '[null,null,null,null,null,null,null,null,null,null]', '[\"arrived\",\"want\",\"have been\",\"don\'t understand\",\"talk\",\"met\",\"came\",\"is doing\",\"needs\",\"is ringing\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 10, '2021-02-16 19:48:44', '2021-02-16 19:48:44'),
(321, 42, 4, '[null,null,null,null,null,null,null]', '[\"24\",\"13.20\",\"18.45\",\"11\",\"5.15\",\"8.20\",\"259\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 7, '2021-02-16 19:48:44', '2021-02-16 19:48:44');
INSERT INTO `employee_quizzes_old` (`id`, `quiz_id`, `employee_id`, `emp_answer`, `correct_answer`, `check_answer`, `emp_score`, `score`, `created_at`, `updated_at`) VALUES
(322, 48, 4, '[null,null,null,null,null,null,null,null,null,null]', '[\"speaking\",\"this is\",\"really sorry\",\"have to\",\"Oh, dear\",\"did she\",\"don\'t worry\",\"could you help me\",\"shall I\",\"that would be\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 10, '2021-02-16 19:48:44', '2021-02-16 19:48:44'),
(323, 75, 4, '[null,null,null]', '[\"care\",\"enjoyable\",\"disappointment\"]', '[\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 6, '2021-02-16 19:48:44', '2021-02-16 19:48:44'),
(324, 82, 4, '[null,null,null,null]', '[\"looting\",\"robbery\",\"burgle\",\"vandalize\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 8, '2021-02-16 19:48:44', '2021-02-16 19:48:44'),
(325, 83, 4, '[null,null,null,null]', '[\"politician\",\"economic\",\"environmental\",\"pollution\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 8, '2021-02-16 19:48:44', '2021-02-16 19:48:44'),
(326, 87, 4, '[null,null,null,null,null,null]', '[\"memorable\",\"scary\",\"overrated\",\"predictable\",\"gripping\",\"hilarious\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 12, '2021-02-16 19:48:44', '2021-02-16 19:48:44'),
(327, 90, 4, '[null,null,null,null,null,null,null,null,null,null,null]', '[\"sued\",\"damages\",\"spread\",\"crisis\",\"hostage\",\"released\",\"invaded\",\"troops\",\"flee\",\"attack\",\"outcry\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 30, '2021-02-16 19:48:44', '2021-02-16 19:48:44'),
(328, 1, 2, '[\"What\"]', '[\"What\"]', 'correct', 1, 1, '2021-02-16 20:37:18', '2021-02-16 20:37:18'),
(329, 2, 2, '[\"They\'re\"]', '[\"They\'re\"]', 'correct', 1, 1, '2021-02-16 20:37:18', '2021-02-16 20:37:18'),
(330, 3, 2, '[\"It\"]', '[\"It\"]', 'correct', 1, 1, '2021-02-16 20:37:18', '2021-02-16 20:37:18'),
(331, 5, 2, '[\"do you spell\"]', '[\"do you spell\"]', 'correct', 1, 1, '2021-02-16 20:37:18', '2021-02-16 20:37:18'),
(332, 6, 2, '[\"one\"]', '[\"--\"]', 'incorrect', 0, 1, '2021-02-16 20:37:18', '2021-02-16 20:37:18'),
(333, 7, 2, '[\"Is Mr. Mike a teacher?\"]', '[\"Is Mr. Mike a teacher?\"]', 'correct', 1, 1, '2021-02-16 20:37:18', '2021-02-16 20:37:18'),
(334, 8, 2, '[\"your father\'s job\"]', '[\"your father\'s job\"]', 'correct', 1, 1, '2021-02-16 20:37:18', '2021-02-16 20:37:18'),
(335, 9, 2, '[\"doesn\'t have\"]', '[\"doesn\'t have\"]', 'correct', 1, 1, '2021-02-16 20:37:18', '2021-02-16 20:37:18'),
(336, 10, 2, '[\"works\"]', '[\"works\"]', 'correct', 1, 1, '2021-02-16 20:37:18', '2021-02-16 20:37:18'),
(337, 11, 2, '[\"We do not often go to the cinema.\"]', '[\"We do not often go to the cinema.\"]', 'correct', 1, 1, '2021-02-16 20:37:18', '2021-02-16 20:37:18'),
(338, 12, 2, '[\"make\"]', '[\"do\"]', 'incorrect', 0, 1, '2021-02-16 20:37:18', '2021-02-16 20:37:18'),
(339, 14, 2, '[\"sometimes\"]', '[\"ever\"]', 'incorrect', 0, 1, '2021-02-16 20:37:18', '2021-02-16 20:37:18'),
(340, 18, 2, '[\"taller\"]', '[\"taller\"]', 'correct', 1, 1, '2021-02-16 20:37:18', '2021-02-16 20:37:18'),
(341, 19, 2, '[\"most\"]', '[\"most\"]', 'correct', 1, 1, '2021-02-16 20:37:18', '2021-02-16 20:37:18'),
(342, 23, 2, '[\"more friendly\"]', '[\"friendlier\"]', 'incorrect', 0, 1, '2021-02-16 20:37:18', '2021-02-16 20:37:18'),
(343, 28, 2, '[\"on\"]', '[\"on\"]', 'correct', 1, 1, '2021-02-16 20:37:18', '2021-02-16 20:37:18'),
(344, 29, 2, '[\"for\"]', '[\"for\"]', 'correct', 1, 1, '2021-02-16 20:37:18', '2021-02-16 20:37:18'),
(345, 30, 2, 'Where does Sally\'s grandmother live?', '[]', '', 0, 1, '2021-02-16 20:37:18', '2021-02-16 20:37:18'),
(346, 31, 2, 'How many children did Tom has?', '[]', '', 0, 1, '2021-02-16 20:37:18', '2021-02-16 20:37:18'),
(347, 32, 2, 'When do they get up every morning?', '[]', '', 0, 1, '2021-02-16 20:37:18', '2021-02-16 20:37:18'),
(348, 33, 2, '[\"I didn\'t go to the cinema\\u00a0 last night.\\u00a0\"]', '[\"I didn\'t go to the cinema\\u00a0 last night.\\u00a0\"]', 'correct', 1, 1, '2021-02-16 20:37:18', '2021-02-16 20:37:18'),
(349, 34, 2, '[\"They haven\'t become very rich.\"]', '[\"They haven\'t become very rich.\"]', 'correct', 1, 1, '2021-02-16 20:37:18', '2021-02-16 20:37:18'),
(350, 35, 2, '[\"I don\'t have a big window in my room.\"]', '[\"I don\'t have a big window in my room.\"]', 'correct', 1, 1, '2021-02-16 20:37:18', '2021-02-16 20:37:18'),
(351, 36, 2, '[\"My mom doesn\'t have perfect cooking skills.\"]', '[\"My mom doesn\'t have perfect cooking skills.\"]', 'correct', 1, 1, '2021-02-16 20:37:18', '2021-02-16 20:37:18'),
(352, 37, 2, '[\"ill\",\"quiet\",\"difficult\",\"beautiful\",\"crowded\",\"intelligent\",\"rich\",\"friendly\",\"interesting\"]', '[\"ill\",\"quiet\",\"difficult\",\"beautiful\",\"crowded\",\"intelligent\",\"rich\",\"friendly\",\"interesting\"]', '[\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\"]', 9, 9, '2021-02-16 20:37:18', '2021-02-16 20:37:18'),
(353, 38, 2, '[\"will arrive\",\"want\",\"Have - gone\",\"don\'t understand\",\"talk\",\"have met\",\"came\",\"will make\",\"will need\",\"is ringing\"]', '[\"arrived\",\"want\",\"have been\",\"don\'t understand\",\"talk\",\"met\",\"came\",\"is doing\",\"needs\",\"is ringing\"]', '[\"incorrect\",\"correct\",\"incorrect\",\"correct\",\"correct\",\"incorrect\",\"correct\",\"incorrect\",\"incorrect\",\"correct\"]', 0, 10, '2021-02-16 20:37:18', '2021-02-16 20:37:18'),
(354, 42, 2, '[\"24 february\",\"13:20 - 24 February\",\"18:45 - 22 February\",\"11 march\",\"5:15 - sunday 11 March\",\"8:20 am\",\"259 pounds including taxes\"]', '[\"24\",\"13.20\",\"18.45\",\"11\",\"5.15\",\"8.20\",\"259\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 7, '2021-02-16 20:37:18', '2021-02-16 20:37:18'),
(355, 48, 2, '[\"speaking\",\"this is\",\"really sorry\",\"have to\",\"oh, dear\",\"Did she?\",\"don\'t worry\",\"could you help me\",\"shall I\",\"this is\"]', '[\"speaking\",\"this is\",\"really sorry\",\"have to\",\"Oh, dear\",\"did she\",\"don\'t worry\",\"could you help me\",\"shall I\",\"that would be\"]', '[\"correct\",\"correct\",\"correct\",\"correct\",\"incorrect\",\"incorrect\",\"correct\",\"correct\",\"correct\",\"incorrect\"]', 0, 10, '2021-02-16 20:37:18', '2021-02-16 20:37:18'),
(356, 49, 2, '[\"will accept\"]', '[\"would accept\"]', 'incorrect', 0, 3, '2021-02-16 20:37:18', '2021-02-16 20:37:18'),
(357, 50, 2, '[\"will pass\"]', '[\"will pass\"]', 'correct', 3, 3, '2021-02-16 20:37:18', '2021-02-16 20:37:18'),
(358, 51, 2, '[\"seeing\"]', '[\"seeing\"]', 'correct', 3, 3, '2021-02-16 20:37:18', '2021-02-16 20:37:18'),
(359, 52, 2, '[\"wants -- next\"]', '[\"wanted -- the following\"]', 'incorrect', 0, 3, '2021-02-16 20:37:18', '2021-02-16 20:37:18'),
(360, 53, 2, '[\"which\"]', '[\"which\"]', 'correct', 3, 3, '2021-02-16 20:37:18', '2021-02-16 20:37:18'),
(361, 54, 2, '[\"where\"]', '[\"where\"]', 'correct', 3, 3, '2021-02-16 20:37:18', '2021-02-16 20:37:18'),
(362, 55, 2, '[\"have been fired\"]', '[\"were fired\"]', 'incorrect', 0, 3, '2021-02-16 20:37:18', '2021-02-16 20:37:18'),
(363, 56, 2, '[\"give up\"]', '[\"give up\"]', 'correct', 3, 3, '2021-02-16 20:37:18', '2021-02-16 20:37:18'),
(364, 57, 2, '[\"go out with\"]', '[\"put up with\"]', 'incorrect', 0, 3, '2021-02-16 20:37:18', '2021-02-16 20:37:18'),
(365, 58, 2, '[\"impolite\"]', '[\"impolite\"]', 'correct', 3, 3, '2021-02-16 20:37:18', '2021-02-16 20:37:18'),
(366, 59, 2, '[\"niece\"]', '[\"niece\"]', 'correct', 3, 3, '2021-02-16 20:37:18', '2021-02-16 20:37:18'),
(367, 60, 2, '[\"mother-in-law\"]', '[\"mother-in-law\"]', 'correct', 3, 3, '2021-02-16 20:37:18', '2021-02-16 20:37:18'),
(368, 61, 2, '[\"17\"]', '[\"17\"]', 'correct', 2, 2, '2021-02-16 20:37:18', '2021-02-16 20:37:18'),
(369, 62, 2, '[\"20\"]', '[\"20\"]', 'correct', 2, 2, '2021-02-16 20:37:18', '2021-02-16 20:37:18'),
(370, 63, 2, '[\"$20\"]', '[\"$20\"]', 'correct', 2, 2, '2021-02-16 20:37:18', '2021-02-16 20:37:18'),
(371, 64, 2, '[\"10\"]', '[\"10\"]', 'correct', 2, 2, '2021-02-16 20:37:18', '2021-02-16 20:37:18'),
(372, 65, 2, '[\"in the water tank\"]', '[\"on his body\"]', 'incorrect', 0, 2, '2021-02-16 20:37:18', '2021-02-16 20:37:18'),
(373, 66, 2, '[\"stomach problems\"]', '[\"stomach problems\"]', 'correct', 2, 2, '2021-02-16 20:37:18', '2021-02-16 20:37:18'),
(374, 67, 2, '[\"Although\"]', '[\"Although\"]', 'correct', 4, 4, '2021-02-16 20:37:18', '2021-02-16 20:37:18'),
(375, 68, 2, '[\"However being tired\"]', '[\"Despite being tired\"]', 'incorrect', 0, 4, '2021-02-16 20:37:18', '2021-02-16 20:37:18'),
(376, 69, 2, '[\"used to work\"]', '[\"am used to working\"]', 'incorrect', 0, 4, '2021-02-16 20:37:18', '2021-02-16 20:37:18'),
(377, 71, 2, '[\"had forgotten\"]', '[\"had forgotten\"]', 'correct', 4, 4, '2021-02-16 20:37:18', '2021-02-16 20:37:18'),
(378, 72, 2, '[\"keep in touch with\"]', '[\"keep in touch with\"]', 'correct', 4, 4, '2021-02-16 20:37:18', '2021-02-16 20:37:18'),
(379, 73, 2, '[\"splitted\"]', '[\"split\"]', 'incorrect', 0, 4, '2021-02-16 20:37:18', '2021-02-16 20:37:18'),
(380, 74, 2, '[\"not to\"]', '[\"not to\"]', 'correct', 4, 4, '2021-02-16 20:37:18', '2021-02-16 20:37:18'),
(381, 75, 2, '[\"caring\",\"enjoyable\",\"disappointment\"]', '[\"care\",\"enjoyable\",\"disappointment\"]', '[\"incorrect\",\"correct\",\"correct\"]', 0, 6, '2021-02-16 20:37:18', '2021-02-16 20:37:18'),
(382, 76, 2, '[\"at a party\"]', '[\"at a party\"]', 'correct', 4, 4, '2021-02-16 20:37:18', '2021-02-16 20:37:18'),
(383, 77, 2, '[\"lost her passport\"]', '[\"lost her passport\"]', 'correct', 4, 4, '2021-02-16 20:37:18', '2021-02-16 20:37:18'),
(384, 78, 2, '[\"got into financial trouble\",\"lost her job\"]', '[\"lost her job\"]', 'incorrect', 0, 4, '2021-02-16 20:37:18', '2021-02-16 20:37:18'),
(385, 79, 2, '[\"London\",\"the country\"]', '[\"the country\"]', 'incorrect', 0, 4, '2021-02-16 20:37:18', '2021-02-16 20:37:18'),
(386, 80, 2, '[\"an aggressive\"]', '[\"an aggressive\"]', 'correct', 4, 4, '2021-02-16 20:37:18', '2021-02-16 20:37:18'),
(387, 81, 2, '[\"some boxing gloves\"]', '[\"a bike\"]', 'incorrect', 0, 4, '2021-02-16 20:37:18', '2021-02-16 20:37:18'),
(388, 82, 2, '[\"looting\",\"rob\",\"burgle\",\"vandal\"]', '[\"looting\",\"robbery\",\"burgle\",\"vandalize\"]', '[\"correct\",\"incorrect\",\"correct\",\"incorrect\"]', 0, 8, '2021-02-16 20:37:18', '2021-02-16 20:37:18'),
(389, 83, 2, '[\"politic\",\"economical\",\"environmental\",\"pollution\"]', '[\"politician\",\"economic\",\"environmental\",\"pollution\"]', '[\"incorrect\",\"incorrect\",\"correct\",\"correct\"]', 0, 8, '2021-02-16 20:37:18', '2021-02-16 20:37:18'),
(390, 84, 2, '[\"give someone a job\"]', '[\"fire someone form a job\"]', 'incorrect', 0, 4, '2021-02-16 20:37:18', '2021-02-16 20:37:18'),
(391, 85, 2, '[\"strong\"]', '[]', 'incorrect', 0, 4, '2021-02-16 20:37:18', '2021-02-16 20:37:18'),
(392, 86, 2, '[\"have a rough voice\"]', '[\"have a difficulty in speaking\"]', 'incorrect', 0, 4, '2021-02-16 20:37:18', '2021-02-16 20:37:18'),
(393, 87, 2, '[\"memorable\",\"scary\",\"overrated\",\"predictable\",\"gripping\",\"hilarious\"]', '[\"memorable\",\"scary\",\"overrated\",\"predictable\",\"gripping\",\"hilarious\"]', '[\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\"]', 12, 12, '2021-02-16 20:37:18', '2021-02-16 20:37:18'),
(394, 90, 2, '[\"outcry\",\"sued\",\"spread\",\"crisis\",\"hostage\",\"released\",\"invaded\",\"troops\",\"flee\",\"damages\",\"attack\"]', '[\"sued\",\"damages\",\"spread\",\"crisis\",\"hostage\",\"released\",\"invaded\",\"troops\",\"flee\",\"attack\",\"outcry\"]', '[\"incorrect\",\"incorrect\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"incorrect\",\"incorrect\"]', 0, 30, '2021-02-16 20:37:18', '2021-02-16 20:37:18'),
(395, 91, 2, '[\"True\"]', '[\"False\"]', 'incorrect', 0, 5, '2021-02-16 20:37:18', '2021-02-16 20:37:18'),
(396, 92, 2, '[\"False\"]', '[\"True\"]', 'incorrect', 0, 5, '2021-02-16 20:37:18', '2021-02-16 20:37:18'),
(397, 93, 2, '[\"False\"]', '[\"False\"]', 'correct', 5, 5, '2021-02-16 20:37:18', '2021-02-16 20:37:18'),
(398, 94, 2, '[\"True\"]', '[\"True\"]', 'correct', 5, 5, '2021-02-16 20:37:18', '2021-02-16 20:37:18'),
(399, 95, 2, '[\"three times\"]', '[\"three times\"]', 'correct', 5, 5, '2021-02-16 20:37:18', '2021-02-16 20:37:18'),
(400, 96, 2, 'Dear Dr. Ahmed,\r\nI would like to share my opinion with you about the company\'s policy and the working conditions.\r\nFirst of all, I would like to say that the policy of the company is not very strict and not hard and this is the most comfortable thing in the company.\r\n we have flexible working hours and if the employees are late, there is no any deductions happen, another issue I like in the company that the manager is very friendly and I can take his opinion in work and any problems I have.\r\nAlso the company always make trips for all the staff to change the mood from negative to positive as this helps us to work better at work.\r\nMy opinion is to keep it on and always encourage the staff to be better and positive.\r\nFinally, I would like to thank the managers at the company, as they are always by our side and improve us in everything.\r\n Thanks in advance', '[]', '', 0, 20, '2021-02-16 20:37:18', '2021-02-16 20:37:18'),
(401, 1, 22, '[\"What\"]', '[\"What\"]', 'correct', 1, 1, '2021-02-16 21:01:22', '2021-02-16 21:01:22'),
(402, 2, 22, '[\"We\'re\"]', '[\"They\'re\"]', 'incorrect', 0, 1, '2021-02-16 21:01:22', '2021-02-16 21:01:22'),
(403, 3, 22, '[\"It\"]', '[\"It\"]', 'correct', 1, 1, '2021-02-16 21:01:22', '2021-02-16 21:01:22'),
(404, 5, 22, '[\"do you spell\"]', '[\"do you spell\"]', 'correct', 1, 1, '2021-02-16 21:01:22', '2021-02-16 21:01:22'),
(405, 6, 22, '[\"one\"]', '[\"--\"]', 'incorrect', 0, 1, '2021-02-16 21:01:22', '2021-02-16 21:01:22'),
(406, 7, 22, '[\"Is Mr. Mike a teacher?\"]', '[\"Is Mr. Mike a teacher?\"]', 'correct', 1, 1, '2021-02-16 21:01:22', '2021-02-16 21:01:22'),
(407, 8, 22, '[\"your father\'s job\"]', '[\"your father\'s job\"]', 'correct', 1, 1, '2021-02-16 21:01:22', '2021-02-16 21:01:22'),
(408, 9, 22, '[\"doesn\'t have\"]', '[\"doesn\'t have\"]', 'correct', 1, 1, '2021-02-16 21:01:22', '2021-02-16 21:01:22'),
(409, 10, 22, '[\"works\"]', '[\"works\"]', 'correct', 1, 1, '2021-02-16 21:01:22', '2021-02-16 21:01:22'),
(410, 11, 22, '[\"We do not often go to the cinema.\"]', '[\"We do not often go to the cinema.\"]', 'correct', 1, 1, '2021-02-16 21:01:22', '2021-02-16 21:01:22'),
(411, 12, 22, '[\"do\"]', '[\"do\"]', 'correct', 1, 1, '2021-02-16 21:01:22', '2021-02-16 21:01:22'),
(412, 14, 22, '[\"usually\"]', '[\"ever\"]', 'incorrect', 0, 1, '2021-02-16 21:01:22', '2021-02-16 21:01:22'),
(413, 18, 22, '[\"taller\"]', '[\"taller\"]', 'correct', 1, 1, '2021-02-16 21:01:22', '2021-02-16 21:01:22'),
(414, 19, 22, '[\"most\"]', '[\"most\"]', 'correct', 1, 1, '2021-02-16 21:01:22', '2021-02-16 21:01:22'),
(415, 23, 22, '[\"more friendly\"]', '[\"friendlier\"]', 'incorrect', 0, 1, '2021-02-16 21:01:22', '2021-02-16 21:01:22'),
(416, 28, 22, '[\"on\"]', '[\"on\"]', 'correct', 1, 1, '2021-02-16 21:01:22', '2021-02-16 21:01:22'),
(417, 29, 22, '[\"for\"]', '[\"for\"]', 'correct', 1, 1, '2021-02-16 21:01:22', '2021-02-16 21:01:22'),
(418, 30, 22, 'where is Sally\'s grandmother lives in ?', '[]', '', 0, 1, '2021-02-16 21:01:22', '2021-02-16 21:01:22'),
(419, 31, 22, 'how old are tom ?', '[]', '', 0, 1, '2021-02-16 21:01:22', '2021-02-16 21:01:22'),
(420, 32, 22, 'when they  are getting up  every morning?', '[]', '', 0, 1, '2021-02-16 21:01:22', '2021-02-16 21:01:22'),
(421, 33, 22, '[\"I didn\'t go to the cinema\\u00a0 last night.\\u00a0\"]', '[\"I didn\'t go to the cinema\\u00a0 last night.\\u00a0\"]', 'correct', 1, 1, '2021-02-16 21:01:22', '2021-02-16 21:01:22'),
(422, 34, 22, '[\"They haven\'t become very rich.\"]', '[\"They haven\'t become very rich.\"]', 'correct', 1, 1, '2021-02-16 21:01:22', '2021-02-16 21:01:22'),
(423, 35, 22, '[\"I don\'t have a big window in my room.\"]', '[\"I don\'t have a big window in my room.\"]', 'correct', 1, 1, '2021-02-16 21:01:22', '2021-02-16 21:01:22'),
(424, 36, 22, '[\"My mom doesn\'t have perfect cooking skills.\"]', '[\"My mom doesn\'t have perfect cooking skills.\"]', 'correct', 1, 1, '2021-02-16 21:01:22', '2021-02-16 21:01:22'),
(425, 37, 22, '[\"ill\",\"quiet\",\"difficult\",\"beautiful\",\"crowded\",\"intelligent\",\"rich\",\"friendly\",\"interesting\"]', '[\"ill\",\"quiet\",\"difficult\",\"beautiful\",\"crowded\",\"intelligent\",\"rich\",\"friendly\",\"interesting\"]', '[\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\"]', 9, 9, '2021-02-16 21:01:22', '2021-02-16 21:01:22'),
(426, 38, 22, '[\"was arrived\",\"want\",\"have-------------go\",\"am not understanding\",\"are talking\",\"met\",\"came\",\"is doing\",\"need\",\"is ringing\"]', '[\"arrived\",\"want\",\"have been\",\"don\'t understand\",\"talk\",\"met\",\"came\",\"is doing\",\"needs\",\"is ringing\"]', '[\"incorrect\",\"correct\",\"incorrect\",\"incorrect\",\"incorrect\",\"correct\",\"correct\",\"correct\",\"incorrect\",\"correct\"]', 0, 10, '2021-02-16 21:01:22', '2021-02-16 21:01:22'),
(427, 42, 22, '[\"24th\",\"13:20\",\"18:45\",\"11th\",\"05:15\",\"08:20\",\"259\"]', '[\"24\",\"13.20\",\"18.45\",\"11\",\"5.15\",\"8.20\",\"259\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"correct\"]', 0, 7, '2021-02-16 21:01:22', '2021-02-16 21:01:22'),
(428, 48, 22, '[\"speaking\",\"this is\",\"really sorry\",\"have to\",\"Oh, dear\",\"did she\",\"don\'t worry\",\"could you help me\",\"shall I\",\"that would be\"]', '[\"speaking\",\"this is\",\"really sorry\",\"have to\",\"Oh, dear\",\"did she\",\"don\'t worry\",\"could you help me\",\"shall I\",\"that would be\"]', '[\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\"]', 10, 10, '2021-02-16 21:01:22', '2021-02-16 21:01:22'),
(429, 49, 22, '[\"will accept\"]', '[\"would accept\"]', 'incorrect', 0, 3, '2021-02-16 21:01:22', '2021-02-16 21:01:22'),
(430, 50, 22, '[\"will pass\"]', '[\"will pass\"]', 'correct', 3, 3, '2021-02-16 21:01:22', '2021-02-16 21:01:22'),
(431, 51, 22, '[\"see\"]', '[\"seeing\"]', 'incorrect', 0, 3, '2021-02-16 21:01:22', '2021-02-16 21:01:22'),
(432, 52, 22, '[\"wants -- next\"]', '[\"wanted -- the following\"]', 'incorrect', 0, 3, '2021-02-16 21:01:22', '2021-02-16 21:01:22'),
(433, 53, 22, '[\"which\"]', '[\"which\"]', 'correct', 3, 3, '2021-02-16 21:01:22', '2021-02-16 21:01:22'),
(434, 54, 22, '[\"where\"]', '[\"where\"]', 'correct', 3, 3, '2021-02-16 21:01:22', '2021-02-16 21:01:22'),
(435, 55, 22, '[\"were fired\"]', '[\"were fired\"]', 'correct', 3, 3, '2021-02-16 21:01:22', '2021-02-16 21:01:22'),
(436, 56, 22, '[\"give up\"]', '[\"give up\"]', 'correct', 3, 3, '2021-02-16 21:01:22', '2021-02-16 21:01:22'),
(437, 57, 22, '[\"put up with\"]', '[\"put up with\"]', 'correct', 3, 3, '2021-02-16 21:01:22', '2021-02-16 21:01:22'),
(438, 58, 22, '[\"impolite\"]', '[\"impolite\"]', 'correct', 3, 3, '2021-02-16 21:01:22', '2021-02-16 21:01:22'),
(439, 59, 22, '[\"niece\"]', '[\"niece\"]', 'correct', 3, 3, '2021-02-16 21:01:22', '2021-02-16 21:01:22'),
(440, 60, 22, '[\"mother-in-law\"]', '[\"mother-in-law\"]', 'correct', 3, 3, '2021-02-16 21:01:22', '2021-02-16 21:01:22'),
(441, 61, 22, '[\"17\"]', '[\"17\"]', 'correct', 2, 2, '2021-02-16 21:01:22', '2021-02-16 21:01:22'),
(442, 62, 22, '[\"20\"]', '[\"20\"]', 'correct', 2, 2, '2021-02-16 21:01:22', '2021-02-16 21:01:22'),
(443, 63, 22, '[\"$20\"]', '[\"$20\"]', 'correct', 2, 2, '2021-02-16 21:01:22', '2021-02-16 21:01:22'),
(444, 64, 22, '[\"10\"]', '[\"10\"]', 'correct', 2, 2, '2021-02-16 21:01:22', '2021-02-16 21:01:22'),
(445, 65, 22, '[\"on his body\"]', '[\"on his body\"]', 'correct', 2, 2, '2021-02-16 21:01:22', '2021-02-16 21:01:22'),
(446, 66, 22, '[\"stomach problems\"]', '[\"stomach problems\"]', 'correct', 2, 2, '2021-02-16 21:01:22', '2021-02-16 21:01:22'),
(447, 67, 22, '[\"Although\"]', '[\"Although\"]', 'correct', 4, 4, '2021-02-16 21:01:22', '2021-02-16 21:01:22'),
(448, 68, 22, '[\"Despite being tired\"]', '[\"Despite being tired\"]', 'correct', 4, 4, '2021-02-16 21:01:22', '2021-02-16 21:01:22'),
(449, 69, 22, '[\"used to work\"]', '[\"am used to working\"]', 'incorrect', 0, 4, '2021-02-16 21:01:22', '2021-02-16 21:01:22'),
(450, 71, 22, '[\"forgot\"]', '[\"had forgotten\"]', 'incorrect', 0, 4, '2021-02-16 21:01:22', '2021-02-16 21:01:22'),
(451, 72, 22, '[\"keep in touch with\"]', '[\"keep in touch with\"]', 'correct', 4, 4, '2021-02-16 21:01:22', '2021-02-16 21:01:22'),
(452, 73, 22, '[\"splitted\"]', '[\"split\"]', 'incorrect', 0, 4, '2021-02-16 21:01:22', '2021-02-16 21:01:22'),
(453, 74, 22, '[\"not to\"]', '[\"not to\"]', 'correct', 4, 4, '2021-02-16 21:01:22', '2021-02-16 21:01:22'),
(454, 75, 22, '[\"care\",\"enjoy\",\"disappointment\"]', '[\"care\",\"enjoyable\",\"disappointment\"]', '[\"correct\",\"incorrect\",\"correct\"]', 0, 6, '2021-02-16 21:01:22', '2021-02-16 21:01:22'),
(455, 76, 22, '[\"at a party\"]', '[\"at a party\"]', 'correct', 4, 4, '2021-02-16 21:01:22', '2021-02-16 21:01:22'),
(456, 77, 22, '[\"lost her passport\"]', '[\"lost her passport\"]', 'correct', 4, 4, '2021-02-16 21:01:22', '2021-02-16 21:01:22'),
(457, 78, 22, '[\"lost her job\"]', '[\"lost her job\"]', 'correct', 4, 4, '2021-02-16 21:01:22', '2021-02-16 21:01:22'),
(458, 79, 22, '[\"the country\"]', '[\"the country\"]', 'correct', 4, 4, '2021-02-16 21:01:22', '2021-02-16 21:01:22'),
(459, 80, 22, '[\"an aggressive\"]', '[\"an aggressive\"]', 'correct', 4, 4, '2021-02-16 21:01:22', '2021-02-16 21:01:22'),
(460, 81, 22, '[\"some boxing gloves\"]', '[\"a bike\"]', 'incorrect', 0, 4, '2021-02-16 21:01:22', '2021-02-16 21:01:22'),
(461, 82, 22, '[\"loot\",\"rob\",null,null]', '[\"looting\",\"robbery\",\"burgle\",\"vandalize\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 8, '2021-02-16 21:01:22', '2021-02-16 21:01:22'),
(462, 83, 22, '[\"politicain\",\"economical\",\"environmental\",\"pollut\"]', '[\"politician\",\"economic\",\"environmental\",\"pollution\"]', '[\"incorrect\",\"incorrect\",\"correct\",\"incorrect\"]', 0, 8, '2021-02-16 21:01:22', '2021-02-16 21:01:22'),
(463, 84, 22, '[\"give someone a job\"]', '[\"fire someone form a job\"]', 'incorrect', 0, 4, '2021-02-16 21:01:22', '2021-02-16 21:01:22'),
(464, 85, 22, '[\"strong\"]', '[]', 'incorrect', 0, 4, '2021-02-16 21:01:22', '2021-02-16 21:01:22'),
(465, 86, 22, '[\"have a rough voice\"]', '[\"have a difficulty in speaking\"]', 'incorrect', 0, 4, '2021-02-16 21:01:22', '2021-02-16 21:01:22'),
(466, 87, 22, '[\"memorable\",\"scary\",\"overrated\",\"predictable\",\"gripping\",\"hilarious\"]', '[\"memorable\",\"scary\",\"overrated\",\"predictable\",\"gripping\",\"hilarious\"]', '[\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\"]', 12, 12, '2021-02-16 21:01:22', '2021-02-16 21:01:22'),
(467, 90, 22, '[\"sued\",\"damages\",\"spread\",\"crisis\",\"hostage\",\"flee\",\"attack\",\"troops\",\"outcry\",\"invaded\",\"released\"]', '[\"sued\",\"damages\",\"spread\",\"crisis\",\"hostage\",\"released\",\"invaded\",\"troops\",\"flee\",\"attack\",\"outcry\"]', '[\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"incorrect\",\"incorrect\",\"correct\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 30, '2021-02-16 21:01:22', '2021-02-16 21:01:22'),
(468, 91, 22, '[\"False\"]', '[\"False\"]', 'correct', 5, 5, '2021-02-16 21:01:22', '2021-02-16 21:01:22'),
(469, 92, 22, '[\"True\"]', '[\"True\"]', 'correct', 5, 5, '2021-02-16 21:01:22', '2021-02-16 21:01:22'),
(470, 93, 22, '[\"True\"]', '[\"False\"]', 'incorrect', 0, 5, '2021-02-16 21:01:22', '2021-02-16 21:01:22'),
(471, 94, 22, '[\"True\"]', '[\"True\"]', 'correct', 5, 5, '2021-02-16 21:01:22', '2021-02-16 21:01:22'),
(472, 95, 22, '[\"three times\"]', '[\"three times\"]', 'correct', 5, 5, '2021-02-16 21:01:22', '2021-02-16 21:01:22'),
(473, 96, 22, 'Dear Manager,\r\n\r\nAccording to your request  about my pinion on the company\'s policy , please review the below points:\r\n1) We have a friendly work environment.\r\n2) Encourage the employees to give the best.\r\n\r\nBest Regards.', '[]', '', 0, 20, '2021-02-16 21:01:22', '2021-02-16 21:01:22'),
(474, 1, 23, '[\"What\"]', '[\"What\"]', 'correct', 1, 1, '2021-02-16 21:07:10', '2021-02-16 21:07:10'),
(475, 2, 23, '[\"They\'re\"]', '[\"They\'re\"]', 'correct', 1, 1, '2021-02-16 21:07:10', '2021-02-16 21:07:10'),
(476, 3, 23, '[\"It\"]', '[\"It\"]', 'correct', 1, 1, '2021-02-16 21:07:10', '2021-02-16 21:07:10'),
(477, 5, 23, '[\"do you spell\"]', '[\"do you spell\"]', 'correct', 1, 1, '2021-02-16 21:07:10', '2021-02-16 21:07:10'),
(478, 6, 23, '[\"one\"]', '[\"--\"]', 'incorrect', 0, 1, '2021-02-16 21:07:10', '2021-02-16 21:07:10'),
(479, 7, 23, '[\"Is Mr. Mike a teacher?\"]', '[\"Is Mr. Mike a teacher?\"]', 'correct', 1, 1, '2021-02-16 21:07:10', '2021-02-16 21:07:10'),
(480, 8, 23, '[\"your father\'s job\"]', '[\"your father\'s job\"]', 'correct', 1, 1, '2021-02-16 21:07:10', '2021-02-16 21:07:10'),
(481, 9, 23, '[\"doesn\'t have\"]', '[\"doesn\'t have\"]', 'correct', 1, 1, '2021-02-16 21:07:10', '2021-02-16 21:07:10'),
(482, 10, 23, '[\"works\"]', '[\"works\"]', 'correct', 1, 1, '2021-02-16 21:07:10', '2021-02-16 21:07:10'),
(483, 11, 23, '[\"We do not often go to the cinema.\"]', '[\"We do not often go to the cinema.\"]', 'correct', 1, 1, '2021-02-16 21:07:10', '2021-02-16 21:07:10'),
(484, 12, 23, '[\"do\"]', '[\"do\"]', 'correct', 1, 1, '2021-02-16 21:07:10', '2021-02-16 21:07:10'),
(485, 14, 23, '[\"sometimes\"]', '[\"ever\"]', 'incorrect', 0, 1, '2021-02-16 21:07:10', '2021-02-16 21:07:10'),
(486, 18, 23, '[\"taller\"]', '[\"taller\"]', 'correct', 1, 1, '2021-02-16 21:07:10', '2021-02-16 21:07:10'),
(487, 19, 23, '[\"most\"]', '[\"most\"]', 'correct', 1, 1, '2021-02-16 21:07:10', '2021-02-16 21:07:10'),
(488, 23, 23, '[\"more friendly\"]', '[\"friendlier\"]', 'incorrect', 0, 1, '2021-02-16 21:07:10', '2021-02-16 21:07:10'),
(489, 28, 23, '[\"on\"]', '[\"on\"]', 'correct', 1, 1, '2021-02-16 21:07:10', '2021-02-16 21:07:10'),
(490, 29, 23, '[\"for\"]', '[\"for\"]', 'correct', 1, 1, '2021-02-16 21:07:10', '2021-02-16 21:07:10'),
(491, 30, 23, 'Where sally\'s grandmother lives ?', '[]', '', 0, 1, '2021-02-16 21:07:10', '2021-02-16 21:07:10'),
(492, 31, 23, 'How many children does Tom had ?', '[]', '', 0, 1, '2021-02-16 21:07:10', '2021-02-16 21:07:10'),
(493, 32, 23, 'When they get up every morning ?', '[]', '', 0, 1, '2021-02-16 21:07:10', '2021-02-16 21:07:10'),
(494, 33, 23, '[\"I didn\'t go to the cinema\\u00a0 last night.\\u00a0\"]', '[\"I didn\'t go to the cinema\\u00a0 last night.\\u00a0\"]', 'correct', 1, 1, '2021-02-16 21:07:10', '2021-02-16 21:07:10'),
(495, 34, 23, '[\"They haven\'t become very rich.\"]', '[\"They haven\'t become very rich.\"]', 'correct', 1, 1, '2021-02-16 21:07:10', '2021-02-16 21:07:10'),
(496, 35, 23, '[\"I don\'t have a big window in my room.\"]', '[\"I don\'t have a big window in my room.\"]', 'correct', 1, 1, '2021-02-16 21:07:10', '2021-02-16 21:07:10'),
(497, 36, 23, '[\"My mom doesn\'t have perfect cooking skills.\"]', '[\"My mom doesn\'t have perfect cooking skills.\"]', 'correct', 1, 1, '2021-02-16 21:07:10', '2021-02-16 21:07:10'),
(498, 37, 23, '[\"ill\",\"queit\",\"difficult\",\"beautiful\",\"crowded\",\"intelligent\",\"rich\",\"friendly\",\"interesting\"]', '[\"ill\",\"quiet\",\"difficult\",\"beautiful\",\"crowded\",\"intelligent\",\"rich\",\"friendly\",\"interesting\"]', '[\"correct\",\"incorrect\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\"]', 0, 9, '2021-02-16 21:07:10', '2021-02-16 21:07:10'),
(499, 38, 23, '[\"arrived\",\"want\",\"have , went\",\"cannot understand\",\"are talking\",\"met\",\"came\",\"is doing\",\"needs\",\"is ringing\"]', '[\"arrived\",\"want\",\"have been\",\"don\'t understand\",\"talk\",\"met\",\"came\",\"is doing\",\"needs\",\"is ringing\"]', '[\"correct\",\"correct\",\"incorrect\",\"incorrect\",\"incorrect\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\"]', 0, 10, '2021-02-16 21:07:10', '2021-02-16 21:07:10'),
(500, 42, 23, '[\"24th\",\"13:20\",\"18:45\",\"11th\",\"5:15\",\"08:20\",\"259\"]', '[\"24\",\"13.20\",\"18.45\",\"11\",\"5.15\",\"8.20\",\"259\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"correct\"]', 0, 7, '2021-02-16 21:07:10', '2021-02-16 21:07:10'),
(501, 48, 23, '[\"speaking\",\"this is\",\"really sorry\",\"have to\",\"Oh, dear\",\"really sorry\",\"don\'t worry\",\"could you help me\",\"shall I\",\"that would be\"]', '[\"speaking\",\"this is\",\"really sorry\",\"have to\",\"Oh, dear\",\"did she\",\"don\'t worry\",\"could you help me\",\"shall I\",\"that would be\"]', '[\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"incorrect\",\"correct\",\"correct\",\"correct\",\"correct\"]', 0, 10, '2021-02-16 21:07:10', '2021-02-16 21:07:10'),
(502, 49, 23, '[\"will accept\"]', '[\"would accept\"]', 'incorrect', 0, 3, '2021-02-16 21:07:10', '2021-02-16 21:07:10'),
(503, 50, 23, '[\"would pass\"]', '[\"will pass\"]', 'incorrect', 0, 3, '2021-02-16 21:07:10', '2021-02-16 21:07:10'),
(504, 51, 23, '[\"see\"]', '[\"seeing\"]', 'incorrect', 0, 3, '2021-02-16 21:07:10', '2021-02-16 21:07:10'),
(505, 52, 23, '[\"wants -- next\"]', '[\"wanted -- the following\"]', 'incorrect', 0, 3, '2021-02-16 21:07:10', '2021-02-16 21:07:10'),
(506, 53, 23, '[\"which\"]', '[\"which\"]', 'correct', 3, 3, '2021-02-16 21:07:10', '2021-02-16 21:07:10'),
(507, 54, 23, '[\"where\"]', '[\"where\"]', 'correct', 3, 3, '2021-02-16 21:07:10', '2021-02-16 21:07:10'),
(508, 55, 23, '[\"were fired\"]', '[\"were fired\"]', 'correct', 3, 3, '2021-02-16 21:07:10', '2021-02-16 21:07:10'),
(509, 56, 23, '[\"give up\"]', '[\"give up\"]', 'correct', 3, 3, '2021-02-16 21:07:10', '2021-02-16 21:07:10'),
(510, 57, 23, '[\"put up with\"]', '[\"put up with\"]', 'correct', 3, 3, '2021-02-16 21:07:10', '2021-02-16 21:07:10'),
(511, 58, 23, '[\"impolite\"]', '[\"impolite\"]', 'correct', 3, 3, '2021-02-16 21:07:10', '2021-02-16 21:07:10'),
(512, 59, 23, '[\"niece\"]', '[\"niece\"]', 'correct', 3, 3, '2021-02-16 21:07:10', '2021-02-16 21:07:10'),
(513, 60, 23, '[\"mother-in-law\"]', '[\"mother-in-law\"]', 'correct', 3, 3, '2021-02-16 21:07:10', '2021-02-16 21:07:10'),
(514, 61, 23, '[\"17\"]', '[\"17\"]', 'correct', 2, 2, '2021-02-16 21:07:10', '2021-02-16 21:07:10'),
(515, 62, 23, '[\"20\"]', '[\"20\"]', 'correct', 2, 2, '2021-02-16 21:07:10', '2021-02-16 21:07:10'),
(516, 63, 23, '[\"$20\"]', '[\"$20\"]', 'correct', 2, 2, '2021-02-16 21:07:10', '2021-02-16 21:07:10'),
(517, 64, 23, '[\"10\"]', '[\"10\"]', 'correct', 2, 2, '2021-02-16 21:07:10', '2021-02-16 21:07:10'),
(518, 65, 23, '[\"on his body\"]', '[\"on his body\"]', 'correct', 2, 2, '2021-02-16 21:07:10', '2021-02-16 21:07:10'),
(519, 66, 23, '[\"stomach problems\"]', '[\"stomach problems\"]', 'correct', 2, 2, '2021-02-16 21:07:10', '2021-02-16 21:07:10'),
(520, 67, 23, '[\"Although\"]', '[\"Although\"]', 'correct', 4, 4, '2021-02-16 21:07:10', '2021-02-16 21:07:10'),
(521, 68, 23, '[\"Despite being tired\"]', '[\"Despite being tired\"]', 'correct', 4, 4, '2021-02-16 21:07:10', '2021-02-16 21:07:10'),
(522, 69, 23, '[\"used to work\"]', '[\"am used to working\"]', 'incorrect', 0, 4, '2021-02-16 21:07:10', '2021-02-16 21:07:10'),
(523, 71, 23, '[\"forgot\"]', '[\"had forgotten\"]', 'incorrect', 0, 4, '2021-02-16 21:07:10', '2021-02-16 21:07:10'),
(524, 72, 23, '[\"keep in touch with\"]', '[\"keep in touch with\"]', 'correct', 4, 4, '2021-02-16 21:07:10', '2021-02-16 21:07:10'),
(525, 73, 23, '[\"splitted\"]', '[\"split\"]', 'incorrect', 0, 4, '2021-02-16 21:07:10', '2021-02-16 21:07:10'),
(526, 74, 23, '[\"not to\"]', '[\"not to\"]', 'correct', 4, 4, '2021-02-16 21:07:10', '2021-02-16 21:07:10'),
(527, 75, 23, '[\"Caring\",\"enjoy\",\"disappointment\"]', '[\"care\",\"enjoyable\",\"disappointment\"]', '[\"incorrect\",\"incorrect\",\"correct\"]', 0, 6, '2021-02-16 21:07:10', '2021-02-16 21:07:10'),
(528, 76, 23, '[\"at a party\"]', '[\"at a party\"]', 'correct', 4, 4, '2021-02-16 21:07:10', '2021-02-16 21:07:10'),
(529, 77, 23, '[\"lost her passport\"]', '[\"lost her passport\"]', 'correct', 4, 4, '2021-02-16 21:07:10', '2021-02-16 21:07:10'),
(530, 78, 23, '[\"lost her job\"]', '[\"lost her job\"]', 'correct', 4, 4, '2021-02-16 21:07:10', '2021-02-16 21:07:10'),
(531, 79, 23, '[\"the country\"]', '[\"the country\"]', 'correct', 4, 4, '2021-02-16 21:07:10', '2021-02-16 21:07:10'),
(532, 80, 23, '[\"an aggressive\"]', '[\"an aggressive\"]', 'correct', 4, 4, '2021-02-16 21:07:10', '2021-02-16 21:07:10'),
(533, 81, 23, '[\"a bike\"]', '[\"a bike\"]', 'correct', 4, 4, '2021-02-16 21:07:10', '2021-02-16 21:07:10'),
(534, 82, 23, '[\"looting\",\"robbery\",\"burgle\",\"vandalize\"]', '[\"looting\",\"robbery\",\"burgle\",\"vandalize\"]', '[\"correct\",\"correct\",\"correct\",\"correct\"]', 8, 8, '2021-02-16 21:07:10', '2021-02-16 21:07:10'),
(535, 83, 23, '[\"Politic\",\"Economical\",\"Environmental\",\"Pollution\"]', '[\"politician\",\"economic\",\"environmental\",\"pollution\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 8, '2021-02-16 21:07:10', '2021-02-16 21:07:10'),
(536, 84, 23, '[\"fire someone form a job\"]', '[\"fire someone form a job\"]', 'correct', 4, 4, '2021-02-16 21:07:10', '2021-02-16 21:07:10'),
(537, 85, 23, '[\"a lot of money\"]', '[]', 'incorrect', 0, 4, '2021-02-16 21:07:10', '2021-02-16 21:07:10'),
(538, 86, 23, '[\"have a rough voice\"]', '[\"have a difficulty in speaking\"]', 'incorrect', 0, 4, '2021-02-16 21:07:10', '2021-02-16 21:07:10'),
(539, 87, 23, '[\"memorable\",\"scary\",\"overrated\",\"predictable\",\"gripping\",\"hilarious\"]', '[\"memorable\",\"scary\",\"overrated\",\"predictable\",\"gripping\",\"hilarious\"]', '[\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\"]', 12, 12, '2021-02-16 21:07:10', '2021-02-16 21:07:10'),
(540, 90, 23, '[\"released\",\"damages\",\"spread\",\"attack\",\"hostage\",\"sued\",\"invaded\",\"Troops\",\"flee\",\"crisis\",\"outcry\"]', '[\"sued\",\"damages\",\"spread\",\"crisis\",\"hostage\",\"released\",\"invaded\",\"troops\",\"flee\",\"attack\",\"outcry\"]', '[\"incorrect\",\"correct\",\"correct\",\"incorrect\",\"correct\",\"incorrect\",\"correct\",\"incorrect\",\"correct\",\"incorrect\",\"correct\"]', 0, 30, '2021-02-16 21:07:10', '2021-02-16 21:07:10'),
(541, 91, 23, '[\"True\"]', '[\"False\"]', 'incorrect', 0, 5, '2021-02-16 21:07:10', '2021-02-16 21:07:10'),
(542, 92, 23, '[\"False\"]', '[\"True\"]', 'incorrect', 0, 5, '2021-02-16 21:07:10', '2021-02-16 21:07:10'),
(543, 93, 23, '[\"True\"]', '[\"False\"]', 'incorrect', 0, 5, '2021-02-16 21:07:10', '2021-02-16 21:07:10'),
(544, 94, 23, '[\"True\"]', '[\"True\"]', 'correct', 5, 5, '2021-02-16 21:07:10', '2021-02-16 21:07:10'),
(545, 95, 23, '[\"three times\"]', '[\"three times\"]', 'correct', 5, 5, '2021-02-16 21:07:10', '2021-02-16 21:07:10'),
(546, 96, 23, 'Dear ... ,\r\n\r\nI am writing to express my opinion on our company policy and the current working conditions.\r\n\r\nFirst of all, in my opinion, i highly respect and follow the policy that our company impose.\r\ni think it makes everyone uncomfortable as the doors are always open and many people step into the company and they are not part of the company.\r\n\r\nAlso, in regard to the current working conditions, i feel it\'s a bit stressful so i recommend to go out for lunch next thursday\r\n\r\nYours sincerely,\r\n\r\nDavid', '[]', '', 0, 20, '2021-02-16 21:07:10', '2021-02-16 21:07:10'),
(547, 1, 26, '[\"What\"]', '[\"What\"]', 'correct', 1, 1, '2021-02-17 20:53:52', '2021-02-17 20:53:52'),
(548, 2, 26, '[\"They\'re\"]', '[\"They\'re\"]', 'correct', 1, 1, '2021-02-17 20:53:52', '2021-02-17 20:53:52'),
(549, 3, 26, '[\"It\"]', '[\"It\"]', 'correct', 1, 1, '2021-02-17 20:53:52', '2021-02-17 20:53:52'),
(550, 5, 26, '[\"do you spell\"]', '[\"do you spell\"]', 'correct', 1, 1, '2021-02-17 20:53:52', '2021-02-17 20:53:52'),
(551, 6, 26, '[\"a\"]', '[\"--\"]', 'incorrect', 0, 1, '2021-02-17 20:53:52', '2021-02-17 20:53:52'),
(552, 7, 26, '[\"Is Mr. Mike a teacher?\"]', '[\"Is Mr. Mike a teacher?\"]', 'correct', 1, 1, '2021-02-17 20:53:52', '2021-02-17 20:53:52'),
(553, 8, 26, '[\"your father\'s job\"]', '[\"your father\'s job\"]', 'correct', 1, 1, '2021-02-17 20:53:52', '2021-02-17 20:53:52'),
(554, 9, 26, '[\"doesn\'t has\"]', '[\"doesn\'t have\"]', 'incorrect', 0, 1, '2021-02-17 20:53:52', '2021-02-17 20:53:52'),
(555, 10, 26, '[\"works\"]', '[\"works\"]', 'correct', 1, 1, '2021-02-17 20:53:52', '2021-02-17 20:53:52'),
(556, 11, 26, '[\"We do not go often to the cinema.\"]', '[\"We do not often go to the cinema.\"]', 'incorrect', 0, 1, '2021-02-17 20:53:52', '2021-02-17 20:53:52'),
(557, 12, 26, '[\"do\"]', '[\"do\"]', 'correct', 1, 1, '2021-02-17 20:53:52', '2021-02-17 20:53:52'),
(558, 14, 26, '[\"sometimes\"]', '[\"ever\"]', 'incorrect', 0, 1, '2021-02-17 20:53:52', '2021-02-17 20:53:52'),
(559, 18, 26, '[\"taller\"]', '[\"taller\"]', 'correct', 1, 1, '2021-02-17 20:53:52', '2021-02-17 20:53:52'),
(560, 19, 26, '[\"most\"]', '[\"most\"]', 'correct', 1, 1, '2021-02-17 20:53:52', '2021-02-17 20:53:52'),
(561, 23, 26, '[\"more friendly\"]', '[\"friendlier\"]', 'incorrect', 0, 1, '2021-02-17 20:53:52', '2021-02-17 20:53:52'),
(562, 28, 26, '[\"in\"]', '[\"on\"]', 'incorrect', 0, 1, '2021-02-17 20:53:52', '2021-02-17 20:53:52'),
(563, 29, 26, '[\"for\"]', '[\"for\"]', 'correct', 1, 1, '2021-02-17 20:53:52', '2021-02-17 20:53:52'),
(564, 30, 26, 'where does Sally\'s grandmother live?', '[]', '', 0, 1, '2021-02-17 20:53:52', '2021-02-17 20:53:52'),
(565, 31, 26, 'How many children did Tom have?', '[]', '', 0, 1, '2021-02-17 20:53:52', '2021-02-17 20:53:52'),
(566, 32, 26, 'what time do they get up every morning?', '[]', '', 0, 1, '2021-02-17 20:53:52', '2021-02-17 20:53:52'),
(567, 33, 26, '[\"I didn\'t go to the cinema\\u00a0 last night.\\u00a0\"]', '[\"I didn\'t go to the cinema\\u00a0 last night.\\u00a0\"]', 'correct', 1, 1, '2021-02-17 20:53:52', '2021-02-17 20:53:52'),
(568, 34, 26, '[\"They don\'t have become very rich.\"]', '[\"They haven\'t become very rich.\"]', 'incorrect', 0, 1, '2021-02-17 20:53:52', '2021-02-17 20:53:52'),
(569, 35, 26, '[\"I don\'t have a big window in my room.\"]', '[\"I don\'t have a big window in my room.\"]', 'correct', 1, 1, '2021-02-17 20:53:52', '2021-02-17 20:53:52'),
(570, 36, 26, '[\"My mom doesn\'t have perfect cooking skills.\"]', '[\"My mom doesn\'t have perfect cooking skills.\"]', 'correct', 1, 1, '2021-02-17 20:53:52', '2021-02-17 20:53:52'),
(571, 37, 26, '[\"ill\",\"quiet\",\"difficult\",\"quiet\",\"crowded\",\"intelligent\",\"rich\",\"friendly\",\"beautiful\"]', '[\"ill\",\"quiet\",\"difficult\",\"beautiful\",\"crowded\",\"intelligent\",\"rich\",\"friendly\",\"interesting\"]', '[\"correct\",\"correct\",\"correct\",\"incorrect\",\"correct\",\"correct\",\"correct\",\"correct\",\"incorrect\"]', 0, 9, '2021-02-17 20:53:52', '2021-02-17 20:53:52'),
(572, 38, 26, '[\"have arrived\",\"want\",\"have you ever gone\",\"did not understand\",\"are talking\",\"met\",\"came\",\"is doing\",\"needs\",\"is ringing\"]', '[\"arrived\",\"want\",\"have been\",\"don\'t understand\",\"talk\",\"met\",\"came\",\"is doing\",\"needs\",\"is ringing\"]', '[\"incorrect\",\"correct\",\"incorrect\",\"incorrect\",\"incorrect\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\"]', 0, 10, '2021-02-17 20:53:52', '2021-02-17 20:53:52'),
(573, 42, 26, '[\"24th\",\"at 13:20\",\"at 18:45\",\"11th\",\"at 5:15\",\"at 8:20\",\"259\"]', '[\"24\",\"13.20\",\"18.45\",\"11\",\"5.15\",\"8.20\",\"259\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"correct\"]', 0, 7, '2021-02-17 20:53:52', '2021-02-17 20:53:52'),
(574, 48, 26, '[\"speaking\",\"this is\",null,\"have to\",\"Oh, dear\",\"really sorry\",\"don\'t worry\",\"could you help me\",\"shall I\",\"that would be\"]', '[\"speaking\",\"this is\",\"really sorry\",\"have to\",\"Oh, dear\",\"did she\",\"don\'t worry\",\"could you help me\",\"shall I\",\"that would be\"]', '[\"correct\",\"correct\",\"incorrect\",\"correct\",\"correct\",\"incorrect\",\"correct\",\"correct\",\"correct\",\"correct\"]', 0, 10, '2021-02-17 20:53:52', '2021-02-17 20:53:52'),
(575, 49, 26, '[\"would accept\"]', '[\"would accept\"]', 'correct', 3, 3, '2021-02-17 20:53:52', '2021-02-17 20:53:52'),
(576, 50, 26, '[\"will pass\"]', '[\"will pass\"]', 'correct', 3, 3, '2021-02-17 20:53:52', '2021-02-17 20:53:52'),
(577, 51, 26, '[\"seeing\"]', '[\"seeing\"]', 'correct', 3, 3, '2021-02-17 20:53:52', '2021-02-17 20:53:52'),
(578, 52, 26, '[\"wants -- next\"]', '[\"wanted -- the following\"]', 'incorrect', 0, 3, '2021-02-17 20:53:52', '2021-02-17 20:53:52'),
(579, 53, 26, '[\"which\"]', '[\"which\"]', 'correct', 3, 3, '2021-02-17 20:53:52', '2021-02-17 20:53:52'),
(580, 54, 26, '[\"where\"]', '[\"where\"]', 'correct', 3, 3, '2021-02-17 20:53:52', '2021-02-17 20:53:52'),
(581, 55, 26, '[\"have been fired\"]', '[\"were fired\"]', 'incorrect', 0, 3, '2021-02-17 20:53:52', '2021-02-17 20:53:52'),
(582, 56, 26, '[\"give up\"]', '[\"give up\"]', 'correct', 3, 3, '2021-02-17 20:53:52', '2021-02-17 20:53:52'),
(583, 57, 26, '[\"put up with\"]', '[\"put up with\"]', 'correct', 3, 3, '2021-02-17 20:53:52', '2021-02-17 20:53:52'),
(584, 58, 26, '[\"impolite\"]', '[\"impolite\"]', 'correct', 3, 3, '2021-02-17 20:53:52', '2021-02-17 20:53:52'),
(585, 59, 26, '[\"niece\"]', '[\"niece\"]', 'correct', 3, 3, '2021-02-17 20:53:52', '2021-02-17 20:53:52'),
(586, 60, 26, '[\"mother-in-law\"]', '[\"mother-in-law\"]', 'correct', 3, 3, '2021-02-17 20:53:52', '2021-02-17 20:53:52'),
(587, 61, 26, '[\"17\"]', '[\"17\"]', 'correct', 2, 2, '2021-02-17 20:53:52', '2021-02-17 20:53:52'),
(588, 62, 26, '[\"20\"]', '[\"20\"]', 'correct', 2, 2, '2021-02-17 20:53:52', '2021-02-17 20:53:52'),
(589, 63, 26, '[\"$20\"]', '[\"$20\"]', 'correct', 2, 2, '2021-02-17 20:53:52', '2021-02-17 20:53:52'),
(590, 64, 26, '[\"10\"]', '[\"10\"]', 'correct', 2, 2, '2021-02-17 20:53:52', '2021-02-17 20:53:52'),
(591, 65, 26, '[\"on his body\"]', '[\"on his body\"]', 'correct', 2, 2, '2021-02-17 20:53:52', '2021-02-17 20:53:52'),
(592, 66, 26, '[\"stomach problems\"]', '[\"stomach problems\"]', 'correct', 2, 2, '2021-02-17 20:53:52', '2021-02-17 20:53:52'),
(593, 67, 26, '[\"Although\"]', '[\"Although\"]', 'correct', 4, 4, '2021-02-17 20:53:52', '2021-02-17 20:53:52'),
(594, 68, 26, '[\"Although being tired\"]', '[\"Despite being tired\"]', 'incorrect', 0, 4, '2021-02-17 20:53:52', '2021-02-17 20:53:52'),
(595, 69, 26, '[\"used to work\"]', '[\"am used to working\"]', 'incorrect', 0, 4, '2021-02-17 20:53:52', '2021-02-17 20:53:52'),
(596, 71, 26, '[\"have forgotten\"]', '[\"had forgotten\"]', 'incorrect', 0, 4, '2021-02-17 20:53:52', '2021-02-17 20:53:52'),
(597, 72, 26, '[\"keep in touch with\"]', '[\"keep in touch with\"]', 'correct', 4, 4, '2021-02-17 20:53:52', '2021-02-17 20:53:52'),
(598, 73, 26, '[\"have splitted\"]', '[\"split\"]', 'incorrect', 0, 4, '2021-02-17 20:53:52', '2021-02-17 20:53:52'),
(599, 74, 26, '[\"don\'t\"]', '[\"not to\"]', 'incorrect', 0, 4, '2021-02-17 20:53:52', '2021-02-17 20:53:52'),
(600, 75, 26, '[\"care\",\"enjoyable\",\"disappointment\"]', '[\"care\",\"enjoyable\",\"disappointment\"]', '[\"correct\",\"correct\",\"correct\"]', 6, 6, '2021-02-17 20:53:52', '2021-02-17 20:53:52'),
(601, 76, 26, '[\"at a party\"]', '[\"at a party\"]', 'correct', 4, 4, '2021-02-17 20:53:52', '2021-02-17 20:53:52'),
(602, 77, 26, '[\"lost her passport\"]', '[\"lost her passport\"]', 'correct', 4, 4, '2021-02-17 20:53:52', '2021-02-17 20:53:52'),
(603, 78, 26, '[\"lost her job\"]', '[\"lost her job\"]', 'correct', 4, 4, '2021-02-17 20:53:52', '2021-02-17 20:53:52'),
(604, 79, 26, '[\"the country\"]', '[\"the country\"]', 'correct', 4, 4, '2021-02-17 20:53:52', '2021-02-17 20:53:52'),
(605, 80, 26, '[\"an aggressive\"]', '[\"an aggressive\"]', 'correct', 4, 4, '2021-02-17 20:53:52', '2021-02-17 20:53:52'),
(606, 81, 26, '[\"a bike\"]', '[\"a bike\"]', 'correct', 4, 4, '2021-02-17 20:53:52', '2021-02-17 20:53:52'),
(607, 82, 26, '[\"looting\",\"robbery\",\"burgle\",\"vandalize\"]', '[\"looting\",\"robbery\",\"burgle\",\"vandalize\"]', '[\"correct\",\"correct\",\"correct\",\"correct\"]', 8, 8, '2021-02-17 20:53:52', '2021-02-17 20:53:52'),
(608, 83, 26, '[\"politician\",\"economical\",\"environmental\",\"polluter\"]', '[\"politician\",\"economic\",\"environmental\",\"pollution\"]', '[\"correct\",\"incorrect\",\"correct\",\"incorrect\"]', 0, 8, '2021-02-17 20:53:52', '2021-02-17 20:53:52'),
(609, 84, 26, '[\"fire someone form a job\"]', '[\"fire someone form a job\"]', 'correct', 4, 4, '2021-02-17 20:53:52', '2021-02-17 20:53:52'),
(610, 85, 26, '[\"a lot of money\"]', '[]', 'incorrect', 0, 4, '2021-02-17 20:53:52', '2021-02-17 20:53:52'),
(611, 86, 26, '[\"have a difficulty in speaking\"]', '[\"have a difficulty in speaking\"]', 'correct', 4, 4, '2021-02-17 20:53:52', '2021-02-17 20:53:52'),
(612, 87, 26, '[\"memorable\",\"scary\",\"overrated\",\"predictable\",\"gripping\",\"hilarious\"]', '[\"memorable\",\"scary\",\"overrated\",\"predictable\",\"gripping\",\"hilarious\"]', '[\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\"]', 12, 12, '2021-02-17 20:53:52', '2021-02-17 20:53:52'),
(613, 90, 26, '[null,null,null,null,null,\"released\",null,null,null,null,null]', '[\"sued\",\"damages\",\"spread\",\"crisis\",\"hostage\",\"released\",\"invaded\",\"troops\",\"flee\",\"attack\",\"outcry\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"correct\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 30, '2021-02-17 20:53:52', '2021-02-17 20:53:52'),
(614, 91, 26, '[\"False\"]', '[\"False\"]', 'correct', 5, 5, '2021-02-17 20:53:52', '2021-02-17 20:53:52'),
(615, 92, 26, '[\"False\"]', '[\"True\"]', 'incorrect', 0, 5, '2021-02-17 20:53:52', '2021-02-17 20:53:52'),
(616, 93, 26, '[\"True\"]', '[\"False\"]', 'incorrect', 0, 5, '2021-02-17 20:53:52', '2021-02-17 20:53:52'),
(617, 94, 26, '[\"True\"]', '[\"True\"]', 'correct', 5, 5, '2021-02-17 20:53:52', '2021-02-17 20:53:52'),
(618, 95, 26, '[\"three times\"]', '[\"three times\"]', 'correct', 5, 5, '2021-02-17 20:53:52', '2021-02-17 20:53:52'),
(619, 96, 26, 'Dear Sir,\r\nI would like to inform you that recently the company\'s policy has been changed and the new one has many points we have to discuss in the upcoming meeting to get some modification again to be matched with our yearly plan and give some flexibility to our SOP\'s.\r\nthank you for your understanding and looking forward meeting you soon', '[]', '', 0, 20, '2021-02-17 20:53:52', '2021-02-17 20:53:52'),
(620, 1, 21, '[\"What\"]', '[\"What\"]', 'correct', 1, 1, '2021-02-17 22:33:35', '2021-02-17 22:33:35'),
(621, 2, 21, '[\"They\'re\"]', '[\"They\'re\"]', 'correct', 1, 1, '2021-02-17 22:33:35', '2021-02-17 22:33:35'),
(622, 3, 21, '[\"It\"]', '[\"It\"]', 'correct', 1, 1, '2021-02-17 22:33:35', '2021-02-17 22:33:35'),
(623, 5, 21, '[\"do you spell\"]', '[\"do you spell\"]', 'correct', 1, 1, '2021-02-17 22:33:35', '2021-02-17 22:33:35'),
(624, 6, 21, '[\"--\"]', '[\"--\"]', 'correct', 1, 1, '2021-02-17 22:33:35', '2021-02-17 22:33:35'),
(625, 7, 21, '[\"Is Mr. Mike a teacher?\"]', '[\"Is Mr. Mike a teacher?\"]', 'correct', 1, 1, '2021-02-17 22:33:35', '2021-02-17 22:33:35'),
(626, 8, 21, '[\"your father\'s job\"]', '[\"your father\'s job\"]', 'correct', 1, 1, '2021-02-17 22:33:35', '2021-02-17 22:33:35'),
(627, 9, 21, '[\"doesn\'t have\"]', '[\"doesn\'t have\"]', 'correct', 1, 1, '2021-02-17 22:33:35', '2021-02-17 22:33:35'),
(628, 10, 21, '[\"works\"]', '[\"works\"]', 'correct', 1, 1, '2021-02-17 22:33:36', '2021-02-17 22:33:36'),
(629, 11, 21, '[\"We do not often go to the cinema.\"]', '[\"We do not often go to the cinema.\"]', 'correct', 1, 1, '2021-02-17 22:33:36', '2021-02-17 22:33:36'),
(630, 12, 21, '[\"make\"]', '[\"do\"]', 'incorrect', 0, 1, '2021-02-17 22:33:36', '2021-02-17 22:33:36'),
(631, 14, 21, '[\"ever\"]', '[\"ever\"]', 'correct', 1, 1, '2021-02-17 22:33:36', '2021-02-17 22:33:36'),
(632, 18, 21, '[\"taller\"]', '[\"taller\"]', 'correct', 1, 1, '2021-02-17 22:33:36', '2021-02-17 22:33:36'),
(633, 19, 21, '[\"most\"]', '[\"most\"]', 'correct', 1, 1, '2021-02-17 22:33:36', '2021-02-17 22:33:36'),
(634, 23, 21, '[\"more friendly\"]', '[\"friendlier\"]', 'incorrect', 0, 1, '2021-02-17 22:33:36', '2021-02-17 22:33:36'),
(635, 28, 21, '[\"on\"]', '[\"on\"]', 'correct', 1, 1, '2021-02-17 22:33:36', '2021-02-17 22:33:36'),
(636, 29, 21, '[\"for\"]', '[\"for\"]', 'correct', 1, 1, '2021-02-17 22:33:36', '2021-02-17 22:33:36'),
(637, 30, 21, 'Where do Sally\'s grandmother lives?', '[]', '', 0, 1, '2021-02-17 22:33:36', '2021-02-17 22:33:36'),
(638, 31, 21, 'How many children did tom have?', '[]', '', 0, 1, '2021-02-17 22:33:36', '2021-02-17 22:33:36'),
(639, 32, 21, 'when do they get up every morning?', '[]', '', 0, 1, '2021-02-17 22:33:36', '2021-02-17 22:33:36'),
(640, 33, 21, '[\"I didn\'t go to the cinema\\u00a0 last night.\\u00a0\"]', '[\"I didn\'t go to the cinema\\u00a0 last night.\\u00a0\"]', 'correct', 1, 1, '2021-02-17 22:33:36', '2021-02-17 22:33:36'),
(641, 34, 21, '[\"They haven\'t become very rich.\"]', '[\"They haven\'t become very rich.\"]', 'correct', 1, 1, '2021-02-17 22:33:36', '2021-02-17 22:33:36'),
(642, 35, 21, '[\"I don\'t have a big window in my room.\"]', '[\"I don\'t have a big window in my room.\"]', 'correct', 1, 1, '2021-02-17 22:33:36', '2021-02-17 22:33:36'),
(643, 36, 21, '[\"My mom doesn\'t have perfect cooking skills.\"]', '[\"My mom doesn\'t have perfect cooking skills.\"]', 'correct', 1, 1, '2021-02-17 22:33:36', '2021-02-17 22:33:36');
INSERT INTO `employee_quizzes_old` (`id`, `quiz_id`, `employee_id`, `emp_answer`, `correct_answer`, `check_answer`, `emp_score`, `score`, `created_at`, `updated_at`) VALUES
(644, 37, 21, '[\"ill\",\"quiet\",\"difficult\",\"beautiful\",\"crowded\",\"intelligent\",\"rich\",\"friendly\",\"interesting\"]', '[\"ill\",\"quiet\",\"difficult\",\"beautiful\",\"crowded\",\"intelligent\",\"rich\",\"friendly\",\"interesting\"]', '[\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\"]', 9, 9, '2021-02-17 22:33:36', '2021-02-17 22:33:36'),
(645, 38, 21, '[\"Have arrived\",\"want\",\"have gone\",\"Didn\'t understand\",\"are talking\",\"have met\",\"come\",\"is doing\",\"needs\",\"is ringing\"]', '[\"arrived\",\"want\",\"have been\",\"don\'t understand\",\"talk\",\"met\",\"came\",\"is doing\",\"needs\",\"is ringing\"]', '[\"incorrect\",\"correct\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"correct\",\"correct\",\"correct\"]', 0, 10, '2021-02-17 22:33:36', '2021-02-17 22:33:36'),
(646, 42, 21, '[\"24\",\"13:20\",\"18:45\",\"11\",\"5:15\",\"08:20\",\"259\"]', '[\"24\",\"13.20\",\"18.45\",\"11\",\"5.15\",\"8.20\",\"259\"]', '[\"correct\",\"incorrect\",\"incorrect\",\"correct\",\"incorrect\",\"incorrect\",\"correct\"]', 0, 7, '2021-02-17 22:33:36', '2021-02-17 22:33:36'),
(647, 48, 21, '[\"speaking\",\"this is\",\"really sorry\",\"have to\",\"Oh, dear\",\"did she\",\"this is\",\"could you help me\",\"shall I\",\"that would be\"]', '[\"speaking\",\"this is\",\"really sorry\",\"have to\",\"Oh, dear\",\"did she\",\"don\'t worry\",\"could you help me\",\"shall I\",\"that would be\"]', '[\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"incorrect\",\"correct\",\"correct\",\"correct\"]', 0, 10, '2021-02-17 22:33:36', '2021-02-17 22:33:36'),
(648, 49, 21, '[\"would accept\"]', '[\"would accept\"]', 'correct', 3, 3, '2021-02-17 22:33:36', '2021-02-17 22:33:36'),
(649, 50, 21, '[\"will pass\"]', '[\"will pass\"]', 'correct', 3, 3, '2021-02-17 22:33:36', '2021-02-17 22:33:36'),
(650, 51, 21, '[\"see\"]', '[\"seeing\"]', 'incorrect', 0, 3, '2021-02-17 22:33:36', '2021-02-17 22:33:36'),
(651, 52, 21, '[\"wants -- next\"]', '[\"wanted -- the following\"]', 'incorrect', 0, 3, '2021-02-17 22:33:36', '2021-02-17 22:33:36'),
(652, 53, 21, '[\"which\"]', '[\"which\"]', 'correct', 3, 3, '2021-02-17 22:33:36', '2021-02-17 22:33:36'),
(653, 54, 21, '[\"where\"]', '[\"where\"]', 'correct', 3, 3, '2021-02-17 22:33:36', '2021-02-17 22:33:36'),
(654, 55, 21, '[\"were fired\"]', '[\"were fired\"]', 'correct', 3, 3, '2021-02-17 22:33:36', '2021-02-17 22:33:36'),
(655, 56, 21, '[\"give up\"]', '[\"give up\"]', 'correct', 3, 3, '2021-02-17 22:33:36', '2021-02-17 22:33:36'),
(656, 57, 21, '[\"put up with\"]', '[\"put up with\"]', 'correct', 3, 3, '2021-02-17 22:33:36', '2021-02-17 22:33:36'),
(657, 58, 21, '[\"impolite\"]', '[\"impolite\"]', 'correct', 3, 3, '2021-02-17 22:33:36', '2021-02-17 22:33:36'),
(658, 59, 21, '[\"nephew\"]', '[\"niece\"]', 'incorrect', 0, 3, '2021-02-17 22:33:36', '2021-02-17 22:33:36'),
(659, 60, 21, '[\"mother-in-law\"]', '[\"mother-in-law\"]', 'correct', 3, 3, '2021-02-17 22:33:36', '2021-02-17 22:33:36'),
(660, 61, 21, '[\"17\"]', '[\"17\"]', 'correct', 2, 2, '2021-02-17 22:33:36', '2021-02-17 22:33:36'),
(661, 62, 21, '[\"20\"]', '[\"20\"]', 'correct', 2, 2, '2021-02-17 22:33:36', '2021-02-17 22:33:36'),
(662, 63, 21, '[\"$20\"]', '[\"$20\"]', 'correct', 2, 2, '2021-02-17 22:33:36', '2021-02-17 22:33:36'),
(663, 64, 21, '[\"10\"]', '[\"10\"]', 'correct', 2, 2, '2021-02-17 22:33:36', '2021-02-17 22:33:36'),
(664, 65, 21, '[\"on his body\"]', '[\"on his body\"]', 'correct', 2, 2, '2021-02-17 22:33:36', '2021-02-17 22:33:36'),
(665, 66, 21, '[\"stomach problems\"]', '[\"stomach problems\"]', 'correct', 2, 2, '2021-02-17 22:33:36', '2021-02-17 22:33:36'),
(666, 67, 21, '[\"Although\"]', '[\"Although\"]', 'correct', 4, 4, '2021-02-17 22:33:36', '2021-02-17 22:33:36'),
(667, 68, 21, '[\"Despite being tired\"]', '[\"Despite being tired\"]', 'correct', 4, 4, '2021-02-17 22:33:36', '2021-02-17 22:33:36'),
(668, 69, 21, '[\"used to work\"]', '[\"am used to working\"]', 'incorrect', 0, 4, '2021-02-17 22:33:36', '2021-02-17 22:33:36'),
(669, 71, 21, '[\"had forgotten\"]', '[\"had forgotten\"]', 'correct', 4, 4, '2021-02-17 22:33:36', '2021-02-17 22:33:36'),
(670, 72, 21, '[\"keep in touch with\"]', '[\"keep in touch with\"]', 'correct', 4, 4, '2021-02-17 22:33:36', '2021-02-17 22:33:36'),
(671, 73, 21, '[\"have splitted\"]', '[\"split\"]', 'incorrect', 0, 4, '2021-02-17 22:33:36', '2021-02-17 22:33:36'),
(672, 74, 21, '[\"not to\"]', '[\"not to\"]', 'correct', 4, 4, '2021-02-17 22:33:36', '2021-02-17 22:33:36'),
(673, 75, 21, '[\"Careful\",\"enjoyable\",\"disappointed\"]', '[\"care\",\"enjoyable\",\"disappointment\"]', '[\"incorrect\",\"correct\",\"incorrect\"]', 0, 6, '2021-02-17 22:33:36', '2021-02-17 22:33:36'),
(674, 76, 21, '[\"at a party\"]', '[\"at a party\"]', 'correct', 4, 4, '2021-02-17 22:33:36', '2021-02-17 22:33:36'),
(675, 77, 21, '[\"lost her passport\"]', '[\"lost her passport\"]', 'correct', 4, 4, '2021-02-17 22:33:36', '2021-02-17 22:33:36'),
(676, 78, 21, '[\"lost her job\"]', '[\"lost her job\"]', 'correct', 4, 4, '2021-02-17 22:33:36', '2021-02-17 22:33:36'),
(677, 79, 21, '[\"the country\"]', '[\"the country\"]', 'correct', 4, 4, '2021-02-17 22:33:36', '2021-02-17 22:33:36'),
(678, 80, 21, '[\"an aggressive\"]', '[\"an aggressive\"]', 'correct', 4, 4, '2021-02-17 22:33:36', '2021-02-17 22:33:36'),
(679, 81, 21, '[\"a bike\"]', '[\"a bike\"]', 'correct', 4, 4, '2021-02-17 22:33:36', '2021-02-17 22:33:36'),
(680, 82, 21, '[\"looterry\",\"robbery\",\"burglar\",\"vandal\"]', '[\"looting\",\"robbery\",\"burgle\",\"vandalize\"]', '[\"incorrect\",\"correct\",\"incorrect\",\"incorrect\"]', 0, 8, '2021-02-17 22:33:36', '2021-02-17 22:33:36'),
(681, 83, 21, '[\"politician\",\"economic\",\"environmentally\",\"pollution\"]', '[\"politician\",\"economic\",\"environmental\",\"pollution\"]', '[\"correct\",\"correct\",\"incorrect\",\"correct\"]', 0, 8, '2021-02-17 22:33:36', '2021-02-17 22:33:36'),
(682, 84, 21, '[\"fire someone form a job\"]', '[\"fire someone form a job\"]', 'correct', 4, 4, '2021-02-17 22:33:36', '2021-02-17 22:33:36'),
(683, 85, 21, '[\"a lot of money\"]', '[]', 'incorrect', 0, 4, '2021-02-17 22:33:36', '2021-02-17 22:33:36'),
(684, 86, 21, '[\"have a difficulty in speaking\"]', '[\"have a difficulty in speaking\"]', 'correct', 4, 4, '2021-02-17 22:33:36', '2021-02-17 22:33:36'),
(685, 87, 21, '[\"memorable\",\"scary\",\"overrated\",\"predictable\",\"gripping\",\"hilarious\"]', '[\"memorable\",\"scary\",\"overrated\",\"predictable\",\"gripping\",\"hilarious\"]', '[\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\"]', 12, 12, '2021-02-17 22:33:36', '2021-02-17 22:33:36'),
(686, 90, 21, '[\"sued\",\"damages\",\"spread\",\"crisis\",\"hostage\",\"released\",\"invaded\",\"troops\",\"flee\",\"attack\",\"outcry\"]', '[\"sued\",\"damages\",\"spread\",\"crisis\",\"hostage\",\"released\",\"invaded\",\"troops\",\"flee\",\"attack\",\"outcry\"]', '[\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\"]', 30, 30, '2021-02-17 22:33:36', '2021-02-17 22:33:36'),
(687, 91, 21, '[\"False\"]', '[\"False\"]', 'correct', 5, 5, '2021-02-17 22:33:36', '2021-02-17 22:33:36'),
(688, 92, 21, '[\"False\"]', '[\"True\"]', 'incorrect', 0, 5, '2021-02-17 22:33:36', '2021-02-17 22:33:36'),
(689, 93, 21, '[\"False\"]', '[\"False\"]', 'correct', 5, 5, '2021-02-17 22:33:36', '2021-02-17 22:33:36'),
(690, 94, 21, '[\"True\"]', '[\"True\"]', 'correct', 5, 5, '2021-02-17 22:33:36', '2021-02-17 22:33:36'),
(691, 95, 21, '[\"three times\"]', '[\"three times\"]', 'correct', 5, 5, '2021-02-17 22:33:36', '2021-02-17 22:33:36'),
(692, 96, 21, 'Dear Sir,\r\n\r\nGreetings.\r\n\r\nThis is to explain my point of view about the company\'s policy as a part from the company.\r\nFirst thing is working hours I think we can make a survey about is it long or suitable for all the employees and the result of this survey will help to make a decision about the working hours , And about the working condition I think we have a very good environment to create and innovate how to make a very interesting workflows to make it easier and simple. \r\n\r\nwe can have a free discussion for all the employees to collect the most suitable idea to make the employees feel like that they are the owners of this company.\r\n\r\nMany thanks for giving me this chance to express my opinion. It is really appreciated.\r\n\r\nRegards,\r\n\r\nAhmed', '[]', '', 0, 20, '2021-02-17 22:33:36', '2021-02-17 22:33:36'),
(795, 1, 28, '[\"What\"]', '[\"What\"]', 'correct', 1, 1, '2021-02-18 14:39:36', '2021-02-18 14:39:36'),
(796, 2, 28, '[\"They\'re\"]', '[\"They\'re\"]', 'correct', 1, 1, '2021-02-18 14:39:36', '2021-02-18 14:39:36'),
(797, 3, 28, '[\"It\"]', '[\"It\"]', 'correct', 1, 1, '2021-02-18 14:39:36', '2021-02-18 14:39:36'),
(798, 5, 28, '[\"do you spell\"]', '[\"do you spell\"]', 'correct', 1, 1, '2021-02-18 14:39:36', '2021-02-18 14:39:36'),
(799, 6, 28, '[\"--\"]', '[\"--\"]', 'correct', 1, 1, '2021-02-18 14:39:36', '2021-02-18 14:39:36'),
(800, 7, 28, '[\"Is Mr. Mike a teacher?\"]', '[\"Is Mr. Mike a teacher?\"]', 'correct', 1, 1, '2021-02-18 14:39:36', '2021-02-18 14:39:36'),
(801, 8, 28, '[\"your father\'s job\"]', '[\"your father\'s job\"]', 'correct', 1, 1, '2021-02-18 14:39:36', '2021-02-18 14:39:36'),
(802, 9, 28, '[\"hasn\'t\"]', '[\"doesn\'t have\"]', 'incorrect', 0, 1, '2021-02-18 14:39:36', '2021-02-18 14:39:36'),
(803, 10, 28, '[\"works\"]', '[\"works\"]', 'correct', 1, 1, '2021-02-18 14:39:36', '2021-02-18 14:39:36'),
(804, 11, 28, '[\"We do not go often to the cinema.\"]', '[\"We do not often go to the cinema.\"]', 'incorrect', 0, 1, '2021-02-18 14:39:36', '2021-02-18 14:39:36'),
(805, 12, 28, '[\"do\"]', '[\"do\"]', 'correct', 1, 1, '2021-02-18 14:39:36', '2021-02-18 14:39:36'),
(806, 14, 28, '[\"sometimes\"]', '[\"ever\"]', 'incorrect', 0, 1, '2021-02-18 14:39:36', '2021-02-18 14:39:36'),
(807, 18, 28, '[\"taller\"]', '[\"taller\"]', 'correct', 1, 1, '2021-02-18 14:39:36', '2021-02-18 14:39:36'),
(808, 19, 28, '[\"most\"]', '[\"most\"]', 'correct', 1, 1, '2021-02-18 14:39:36', '2021-02-18 14:39:36'),
(809, 23, 28, '[\"more friendly\"]', '[\"friendlier\"]', 'incorrect', 0, 1, '2021-02-18 14:39:36', '2021-02-18 14:39:36'),
(810, 28, 28, '[\"on\"]', '[\"on\"]', 'correct', 1, 1, '2021-02-18 14:39:36', '2021-02-18 14:39:36'),
(811, 29, 28, '[\"to\"]', '[\"for\"]', 'incorrect', 0, 1, '2021-02-18 14:39:36', '2021-02-18 14:39:36'),
(812, 30, 28, 'where does Sally\'s grandmother live?', '[]', '', 0, 1, '2021-02-18 14:39:36', '2021-02-18 14:39:36'),
(813, 31, 28, 'How many children had Tom?', '[]', '', 0, 1, '2021-02-18 14:39:36', '2021-02-18 14:39:36'),
(814, 32, 28, 'what time do they get up ?', '[]', '', 0, 1, '2021-02-18 14:39:36', '2021-02-18 14:39:36'),
(815, 33, 28, '[\"I didn\'t go to the cinema\\u00a0 last night.\\u00a0\"]', '[\"I didn\'t go to the cinema\\u00a0 last night.\\u00a0\"]', 'correct', 1, 1, '2021-02-18 14:39:36', '2021-02-18 14:39:36'),
(816, 34, 28, '[\"They haven\'t become very rich.\"]', '[\"They haven\'t become very rich.\"]', 'correct', 1, 1, '2021-02-18 14:39:36', '2021-02-18 14:39:36'),
(817, 35, 28, '[\"I don\'t have a big window in my room.\"]', '[\"I don\'t have a big window in my room.\"]', 'correct', 1, 1, '2021-02-18 14:39:36', '2021-02-18 14:39:36'),
(818, 36, 28, '[\"My mom doesn\'t have perfect cooking skills.\"]', '[\"My mom doesn\'t have perfect cooking skills.\"]', 'correct', 1, 1, '2021-02-18 14:39:36', '2021-02-18 14:39:36'),
(819, 37, 28, '[\"ill\",\"quit\",\"difficult\",\"beautiful\",\"crowded\",\"intelligent\",\"rich\",\"friendly\",\"interesting\"]', '[\"ill\",\"quiet\",\"difficult\",\"beautiful\",\"crowded\",\"intelligent\",\"rich\",\"friendly\",\"interesting\"]', '[\"correct\",\"incorrect\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\"]', 0, 9, '2021-02-18 14:39:36', '2021-02-18 14:39:36'),
(820, 38, 28, '[\"arrived\",\"want\",\"Do \\/ go\",\"don\'t understand\",\"are talking\",\"met\",\"came\",\"is doing\",\"needs\",\"is ringing\"]', '[\"arrived\",\"want\",\"have been\",\"don\'t understand\",\"talk\",\"met\",\"came\",\"is doing\",\"needs\",\"is ringing\"]', '[\"correct\",\"correct\",\"incorrect\",\"correct\",\"incorrect\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\"]', 0, 10, '2021-02-18 14:39:36', '2021-02-18 14:39:36'),
(821, 42, 28, '[\"Saturday 24\",\"13:20\",\"18:45\",\"Sunday 11\",\"5:15\",\"8:20\",\"259\"]', '[\"24\",\"13.20\",\"18.45\",\"11\",\"5.15\",\"8.20\",\"259\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"correct\"]', 0, 7, '2021-02-18 14:39:36', '2021-02-18 14:39:36'),
(822, 48, 28, '[\"speaking\",\"this is\",\"really sorry\",\"have to\",\"Oh, dear\",\"did she\",\"don\'t worry\",\"could you help me\",\"shall I\",\"that would be\"]', '[\"speaking\",\"this is\",\"really sorry\",\"have to\",\"Oh, dear\",\"did she\",\"don\'t worry\",\"could you help me\",\"shall I\",\"that would be\"]', '[\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\"]', 10, 10, '2021-02-18 14:39:36', '2021-02-18 14:39:36'),
(823, 49, 28, '[\"would accept\"]', '[\"would accept\"]', 'correct', 3, 3, '2021-02-18 14:39:36', '2021-02-18 14:39:36'),
(824, 50, 28, '[\"will pass\"]', '[\"will pass\"]', 'correct', 3, 3, '2021-02-18 14:39:36', '2021-02-18 14:39:36'),
(825, 51, 28, '[\"see\"]', '[\"seeing\"]', 'incorrect', 0, 3, '2021-02-18 14:39:36', '2021-02-18 14:39:36'),
(826, 52, 28, '[\"wants -- next\"]', '[\"wanted -- the following\"]', 'incorrect', 0, 3, '2021-02-18 14:39:36', '2021-02-18 14:39:36'),
(827, 53, 28, '[\"which\"]', '[\"which\"]', 'correct', 3, 3, '2021-02-18 14:39:36', '2021-02-18 14:39:36'),
(828, 54, 28, '[\"where\"]', '[\"where\"]', 'correct', 3, 3, '2021-02-18 14:39:36', '2021-02-18 14:39:36'),
(829, 55, 28, '[\"were fired\"]', '[\"were fired\"]', 'correct', 3, 3, '2021-02-18 14:39:36', '2021-02-18 14:39:36'),
(830, 56, 28, '[\"give up\"]', '[\"give up\"]', 'correct', 3, 3, '2021-02-18 14:39:36', '2021-02-18 14:39:36'),
(831, 57, 28, '[\"put up with\"]', '[\"put up with\"]', 'correct', 3, 3, '2021-02-18 14:39:36', '2021-02-18 14:39:36'),
(832, 58, 28, '[\"impolite\"]', '[\"impolite\"]', 'correct', 3, 3, '2021-02-18 14:39:36', '2021-02-18 14:39:36'),
(833, 59, 28, '[\"niece\"]', '[\"niece\"]', 'correct', 3, 3, '2021-02-18 14:39:36', '2021-02-18 14:39:36'),
(834, 60, 28, '[\"mother-in-law\"]', '[\"mother-in-law\"]', 'correct', 3, 3, '2021-02-18 14:39:36', '2021-02-18 14:39:36'),
(835, 61, 28, '[\"17\"]', '[\"17\"]', 'correct', 2, 2, '2021-02-18 14:39:36', '2021-02-18 14:39:36'),
(836, 62, 28, '[\"20\"]', '[\"20\"]', 'correct', 2, 2, '2021-02-18 14:39:36', '2021-02-18 14:39:36'),
(837, 63, 28, '[\"$20\"]', '[\"$20\"]', 'correct', 2, 2, '2021-02-18 14:39:36', '2021-02-18 14:39:36'),
(838, 64, 28, '[\"10\"]', '[\"10\"]', 'correct', 2, 2, '2021-02-18 14:39:36', '2021-02-18 14:39:36'),
(839, 65, 28, '[\"on his body\"]', '[\"on his body\"]', 'correct', 2, 2, '2021-02-18 14:39:36', '2021-02-18 14:39:36'),
(840, 66, 28, '[\"stomach problems\"]', '[\"stomach problems\"]', 'correct', 2, 2, '2021-02-18 14:39:36', '2021-02-18 14:39:36'),
(841, 67, 28, '[\"Although\"]', '[\"Although\"]', 'correct', 4, 4, '2021-02-18 14:39:36', '2021-02-18 14:39:36'),
(842, 68, 28, '[\"Although being tired\"]', '[\"Despite being tired\"]', 'incorrect', 0, 4, '2021-02-18 14:39:36', '2021-02-18 14:39:36'),
(843, 69, 28, '[\"am used to work\"]', '[\"am used to working\"]', 'incorrect', 0, 4, '2021-02-18 14:39:36', '2021-02-18 14:39:36'),
(844, 71, 28, '[\"have forgotten\"]', '[\"had forgotten\"]', 'incorrect', 0, 4, '2021-02-18 14:39:36', '2021-02-18 14:39:36'),
(845, 72, 28, '[\"keep in touch with\"]', '[\"keep in touch with\"]', 'correct', 4, 4, '2021-02-18 14:39:36', '2021-02-18 14:39:36'),
(846, 73, 28, '[\"splitted\"]', '[\"split\"]', 'incorrect', 0, 4, '2021-02-18 14:39:36', '2021-02-18 14:39:36'),
(847, 74, 28, '[\"don\'t\"]', '[\"not to\"]', 'incorrect', 0, 4, '2021-02-18 14:39:36', '2021-02-18 14:39:36'),
(848, 75, 28, '[\"Care\",\"enjoyable\",\"disappointment\"]', '[\"care\",\"enjoyable\",\"disappointment\"]', '[\"incorrect\",\"correct\",\"correct\"]', 0, 6, '2021-02-18 14:39:36', '2021-02-18 14:39:36'),
(849, 76, 28, '[\"at a party\"]', '[\"at a party\"]', 'correct', 4, 4, '2021-02-18 14:39:36', '2021-02-18 14:39:36'),
(850, 77, 28, '[\"lost her passport\"]', '[\"lost her passport\"]', 'correct', 4, 4, '2021-02-18 14:39:36', '2021-02-18 14:39:36'),
(851, 78, 28, '[\"lost her job\"]', '[\"lost her job\"]', 'correct', 4, 4, '2021-02-18 14:39:36', '2021-02-18 14:39:36'),
(852, 79, 28, '[\"the country\"]', '[\"the country\"]', 'correct', 4, 4, '2021-02-18 14:39:36', '2021-02-18 14:39:36'),
(853, 80, 28, '[\"an aggressive\"]', '[\"an aggressive\"]', 'correct', 4, 4, '2021-02-18 14:39:36', '2021-02-18 14:39:36'),
(854, 81, 28, '[\"some boxing gloves\"]', '[\"a bike\"]', 'incorrect', 0, 4, '2021-02-18 14:39:36', '2021-02-18 14:39:36'),
(855, 82, 28, '[\"loot\",\"robbery\",\"burgle\",\"vadalize\"]', '[\"looting\",\"robbery\",\"burgle\",\"vandalize\"]', '[\"incorrect\",\"correct\",\"correct\",\"incorrect\"]', 0, 8, '2021-02-18 14:39:36', '2021-02-18 14:39:36'),
(856, 83, 28, '[\"political\",\"economical\",\"Environmental\",\"pollution\"]', '[\"politician\",\"economic\",\"environmental\",\"pollution\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"correct\"]', 0, 8, '2021-02-18 14:39:36', '2021-02-18 14:39:36'),
(857, 84, 28, '[\"fire someone form a job\"]', '[\"fire someone form a job\"]', 'correct', 4, 4, '2021-02-18 14:39:36', '2021-02-18 14:39:36'),
(858, 85, 28, '[\"a lot of money\"]', '[]', 'incorrect', 0, 4, '2021-02-18 14:39:36', '2021-02-18 14:39:36'),
(859, 86, 28, '[\"have a difficulty in speaking\"]', '[\"have a difficulty in speaking\"]', 'correct', 4, 4, '2021-02-18 14:39:36', '2021-02-18 14:39:36'),
(860, 87, 28, '[\"overrated\",\"scary\",\"gripping\",\"predictable\",\"memorable\",\"hilarious\"]', '[\"memorable\",\"scary\",\"overrated\",\"predictable\",\"gripping\",\"hilarious\"]', '[\"incorrect\",\"correct\",\"incorrect\",\"correct\",\"incorrect\",\"correct\"]', 0, 12, '2021-02-18 14:39:36', '2021-02-18 14:39:36'),
(861, 90, 28, '[\"sued\",null,\"spread\",\"crisis\",\"outcry\",\"released\",\"invaded\",\"troops\",\"flee\",\"hostage\",null]', '[\"sued\",\"damages\",\"spread\",\"crisis\",\"hostage\",\"released\",\"invaded\",\"troops\",\"flee\",\"attack\",\"outcry\"]', '[\"correct\",\"incorrect\",\"correct\",\"correct\",\"incorrect\",\"correct\",\"correct\",\"correct\",\"correct\",\"incorrect\",\"incorrect\"]', 0, 30, '2021-02-18 14:39:36', '2021-02-18 14:39:36'),
(862, 91, 28, '[\"True\"]', '[\"False\"]', 'incorrect', 0, 5, '2021-02-18 14:39:36', '2021-02-18 14:39:36'),
(863, 92, 28, '[\"True\"]', '[\"True\"]', 'correct', 5, 5, '2021-02-18 14:39:36', '2021-02-18 14:39:36'),
(864, 93, 28, '[\"False\"]', '[\"False\"]', 'correct', 5, 5, '2021-02-18 14:39:36', '2021-02-18 14:39:36'),
(865, 94, 28, '[\"True\"]', '[\"True\"]', 'correct', 5, 5, '2021-02-18 14:39:36', '2021-02-18 14:39:36'),
(866, 95, 28, '[\"three times\"]', '[\"three times\"]', 'correct', 5, 5, '2021-02-18 14:39:36', '2021-02-18 14:39:36'),
(867, 96, 28, 'Dear Sir,\r\nregarding our discussion about company\'s policy and working condition, I think we have perfect structure with a successful team, we only need to initiate a new department for logistic to support our coverage around more areas, also we should establish new branches in remote area\r\nbest regards', '[]', '', 0, 20, '2021-02-18 14:39:36', '2021-02-18 14:39:36'),
(868, 1, 1, '[\"What\"]', '[\"What\"]', 'correct', 1, 1, '2021-02-18 15:04:02', '2021-02-18 15:04:02'),
(869, 2, 1, '[\"They\'re\"]', '[\"They\'re\"]', 'correct', 1, 1, '2021-02-18 15:04:02', '2021-02-18 15:04:02'),
(870, 3, 1, '[\"It\"]', '[\"It\"]', 'correct', 1, 1, '2021-02-18 15:04:02', '2021-02-18 15:04:02'),
(871, 5, 1, '[\"do you spell\"]', '[\"do you spell\"]', 'correct', 1, 1, '2021-02-18 15:04:02', '2021-02-18 15:04:02'),
(872, 6, 1, '[\"--\"]', '[\"--\"]', 'correct', 1, 1, '2021-02-18 15:04:02', '2021-02-18 15:04:02'),
(873, 7, 1, '[\"Is Mr. Mike a teacher?\"]', '[\"Is Mr. Mike a teacher?\"]', 'correct', 1, 1, '2021-02-18 15:04:02', '2021-02-18 15:04:02'),
(874, 8, 1, '[\"your father\'s job\"]', '[\"your father\'s job\"]', 'correct', 1, 1, '2021-02-18 15:04:02', '2021-02-18 15:04:02'),
(875, 9, 1, '[\"doesn\'t have\"]', '[\"doesn\'t have\"]', 'correct', 1, 1, '2021-02-18 15:04:02', '2021-02-18 15:04:02'),
(876, 10, 1, '[\"works\"]', '[\"works\"]', 'correct', 1, 1, '2021-02-18 15:04:02', '2021-02-18 15:04:02'),
(877, 11, 1, '[\"We do not often go to the cinema.\"]', '[\"We do not often go to the cinema.\"]', 'correct', 1, 1, '2021-02-18 15:04:02', '2021-02-18 15:04:02'),
(878, 12, 1, '[\"do\"]', '[\"do\"]', 'correct', 1, 1, '2021-02-18 15:04:02', '2021-02-18 15:04:02'),
(879, 14, 1, '[\"sometimes\"]', '[\"ever\"]', 'incorrect', 0, 1, '2021-02-18 15:04:02', '2021-02-18 15:04:02'),
(880, 18, 1, '[\"taller\"]', '[\"taller\"]', 'correct', 1, 1, '2021-02-18 15:04:02', '2021-02-18 15:04:02'),
(881, 19, 1, '[\"most\"]', '[\"most\"]', 'correct', 1, 1, '2021-02-18 15:04:02', '2021-02-18 15:04:02'),
(882, 23, 1, '[\"more friendly\"]', '[\"friendlier\"]', 'incorrect', 0, 1, '2021-02-18 15:04:02', '2021-02-18 15:04:02'),
(883, 28, 1, '[\"on\"]', '[\"on\"]', 'correct', 1, 1, '2021-02-18 15:04:02', '2021-02-18 15:04:02'),
(884, 29, 1, '[\"for\"]', '[\"for\"]', 'correct', 1, 1, '2021-02-18 15:04:02', '2021-02-18 15:04:02'),
(885, 30, 1, 'Where does Sally\'s grandmother live?', '[]', '', 0, 1, '2021-02-18 15:04:02', '2021-02-18 15:04:02'),
(886, 31, 1, 'How many children didTom have?', '[]', '', 0, 1, '2021-02-18 15:04:02', '2021-02-18 15:04:02'),
(887, 32, 1, 'when you walk up every day?', '[]', '', 0, 1, '2021-02-18 15:04:02', '2021-02-18 15:04:02'),
(888, 33, 1, '[\"I didn\'t go to the cinema\\u00a0 last night.\\u00a0\"]', '[\"I didn\'t go to the cinema\\u00a0 last night.\\u00a0\"]', 'correct', 1, 1, '2021-02-18 15:04:02', '2021-02-18 15:04:02'),
(889, 34, 1, '[\"They haven\'t become very rich.\"]', '[\"They haven\'t become very rich.\"]', 'correct', 1, 1, '2021-02-18 15:04:02', '2021-02-18 15:04:02'),
(890, 35, 1, '[\"I don\'t have a big window in my room.\"]', '[\"I don\'t have a big window in my room.\"]', 'correct', 1, 1, '2021-02-18 15:04:02', '2021-02-18 15:04:02'),
(891, 36, 1, '[\"My mom doesn\'t have perfect cooking skills.\"]', '[\"My mom doesn\'t have perfect cooking skills.\"]', 'correct', 1, 1, '2021-02-18 15:04:02', '2021-02-18 15:04:02'),
(892, 37, 1, '[\"ill\",\"quiet\",\"difficult\",\"beautiful\",\"crowded\",\"intelligent\",\"friendly\",\"quiet\",\"interesting\"]', '[\"ill\",\"quiet\",\"difficult\",\"beautiful\",\"crowded\",\"intelligent\",\"rich\",\"friendly\",\"interesting\"]', '[\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"incorrect\",\"incorrect\",\"correct\"]', 0, 9, '2021-02-18 15:04:02', '2021-02-18 15:04:02'),
(893, 38, 1, '[\"arrived\",\"wanted\",null,null,null,null,null,null,null,null]', '[\"arrived\",\"want\",\"have been\",\"don\'t understand\",\"talk\",\"met\",\"came\",\"is doing\",\"needs\",\"is ringing\"]', '[\"correct\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 10, '2021-02-18 15:04:02', '2021-02-18 15:04:02'),
(894, 42, 1, '[\"24th\",\"13:20\",\"18:45\",\"11st\",\"05:15 am\",\"08:20 am\",\"259\"]', '[\"24\",\"13.20\",\"18.45\",\"11\",\"5.15\",\"8.20\",\"259\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"correct\"]', 0, 7, '2021-02-18 15:04:02', '2021-02-18 15:04:02'),
(895, 48, 1, '[\"speaking\",\"this is\",null,\"have to\",\"Oh, dear\",\"really sorry\",\"don\'t worry\",\"could you help me\",\"shall I\",\"that would be\"]', '[\"speaking\",\"this is\",\"really sorry\",\"have to\",\"Oh, dear\",\"did she\",\"don\'t worry\",\"could you help me\",\"shall I\",\"that would be\"]', '[\"correct\",\"correct\",\"incorrect\",\"correct\",\"correct\",\"incorrect\",\"correct\",\"correct\",\"correct\",\"correct\"]', 0, 10, '2021-02-18 15:04:02', '2021-02-18 15:04:02'),
(896, 49, 1, '[\"will accept\"]', '[\"would accept\"]', 'incorrect', 0, 3, '2021-02-18 15:04:02', '2021-02-18 15:04:02'),
(897, 50, 1, '[\"would have passed\"]', '[\"will pass\"]', 'incorrect', 0, 3, '2021-02-18 15:04:02', '2021-02-18 15:04:02'),
(898, 51, 1, '[\"see\"]', '[\"seeing\"]', 'incorrect', 0, 3, '2021-02-18 15:04:02', '2021-02-18 15:04:02'),
(899, 52, 1, '[\"wants -- next\"]', '[\"wanted -- the following\"]', 'incorrect', 0, 3, '2021-02-18 15:04:02', '2021-02-18 15:04:02'),
(900, 53, 1, '[\"which\"]', '[\"which\"]', 'correct', 3, 3, '2021-02-18 15:04:02', '2021-02-18 15:04:02'),
(901, 54, 1, '[\"where\"]', '[\"where\"]', 'correct', 3, 3, '2021-02-18 15:04:02', '2021-02-18 15:04:02'),
(902, 55, 1, '[\"were fired\",\"have been fired\"]', '[\"were fired\"]', 'incorrect', 0, 3, '2021-02-18 15:04:02', '2021-02-18 15:04:02'),
(903, 56, 1, '[\"give up\"]', '[\"give up\"]', 'correct', 3, 3, '2021-02-18 15:04:02', '2021-02-18 15:04:02'),
(904, 57, 1, '[\"put up with\"]', '[\"put up with\"]', 'correct', 3, 3, '2021-02-18 15:04:02', '2021-02-18 15:04:02'),
(905, 58, 1, '[\"impolite\"]', '[\"impolite\"]', 'correct', 3, 3, '2021-02-18 15:04:02', '2021-02-18 15:04:02'),
(906, 59, 1, '[\"niece\"]', '[\"niece\"]', 'correct', 3, 3, '2021-02-18 15:04:02', '2021-02-18 15:04:02'),
(907, 60, 1, '[\"mother-in-law\"]', '[\"mother-in-law\"]', 'correct', 3, 3, '2021-02-18 15:04:02', '2021-02-18 15:04:02'),
(908, 61, 1, '[\"17\"]', '[\"17\"]', 'correct', 2, 2, '2021-02-18 15:04:02', '2021-02-18 15:04:02'),
(909, 62, 1, '[\"20\"]', '[\"20\"]', 'correct', 2, 2, '2021-02-18 15:04:02', '2021-02-18 15:04:02'),
(910, 63, 1, '[\"$20\"]', '[\"$20\"]', 'correct', 2, 2, '2021-02-18 15:04:02', '2021-02-18 15:04:02'),
(911, 64, 1, '[\"10\"]', '[\"10\"]', 'correct', 2, 2, '2021-02-18 15:04:02', '2021-02-18 15:04:02'),
(912, 65, 1, '[\"on his body\"]', '[\"on his body\"]', 'correct', 2, 2, '2021-02-18 15:04:02', '2021-02-18 15:04:02'),
(913, 66, 1, '[\"stomach problems\"]', '[\"stomach problems\"]', 'correct', 2, 2, '2021-02-18 15:04:02', '2021-02-18 15:04:02'),
(914, 67, 1, '[\"Although\"]', '[\"Although\"]', 'correct', 4, 4, '2021-02-18 15:04:02', '2021-02-18 15:04:02'),
(915, 68, 1, '[\"Although being tired\"]', '[\"Despite being tired\"]', 'incorrect', 0, 4, '2021-02-18 15:04:02', '2021-02-18 15:04:02'),
(916, 69, 1, '[\"used to work\"]', '[\"am used to working\"]', 'incorrect', 0, 4, '2021-02-18 15:04:02', '2021-02-18 15:04:02'),
(917, 71, 1, '[\"had forgotten\"]', '[\"had forgotten\"]', 'correct', 4, 4, '2021-02-18 15:04:02', '2021-02-18 15:04:02'),
(918, 72, 1, '[\"keep in touch with\"]', '[\"keep in touch with\"]', 'correct', 4, 4, '2021-02-18 15:04:02', '2021-02-18 15:04:02'),
(919, 73, 1, '[\"splitted\"]', '[\"split\"]', 'incorrect', 0, 4, '2021-02-18 15:04:02', '2021-02-18 15:04:02'),
(920, 74, 1, '[\"not to\"]', '[\"not to\"]', 'correct', 4, 4, '2021-02-18 15:04:02', '2021-02-18 15:04:02'),
(921, 75, 1, '[\"care\",\"enjoable\",\"disappointment\"]', '[\"care\",\"enjoyable\",\"disappointment\"]', '[\"correct\",\"incorrect\",\"correct\"]', 0, 6, '2021-02-18 15:04:02', '2021-02-18 15:04:02'),
(922, 76, 1, '[\"at a party\"]', '[\"at a party\"]', 'correct', 4, 4, '2021-02-18 15:04:02', '2021-02-18 15:04:02'),
(923, 77, 1, '[\"lost her passport\"]', '[\"lost her passport\"]', 'correct', 4, 4, '2021-02-18 15:04:02', '2021-02-18 15:04:02'),
(924, 78, 1, '[\"lost her job\"]', '[\"lost her job\"]', 'correct', 4, 4, '2021-02-18 15:04:02', '2021-02-18 15:04:02'),
(925, 79, 1, '[\"the country\"]', '[\"the country\"]', 'correct', 4, 4, '2021-02-18 15:04:02', '2021-02-18 15:04:02'),
(926, 80, 1, '[\"a hard-working\"]', '[\"an aggressive\"]', 'incorrect', 0, 4, '2021-02-18 15:04:02', '2021-02-18 15:04:02'),
(927, 81, 1, '[\"some boxing gloves\"]', '[\"a bike\"]', 'incorrect', 0, 4, '2021-02-18 15:04:02', '2021-02-18 15:04:02'),
(928, 82, 1, '[\"loot\",\"robbering\",\"burglar\",\"vandaling\"]', '[\"looting\",\"robbery\",\"burgle\",\"vandalize\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 8, '2021-02-18 15:04:02', '2021-02-18 15:04:02'),
(929, 83, 1, '[\"politician\",\"economical\",\"environmental\",\"polluty\"]', '[\"politician\",\"economic\",\"environmental\",\"pollution\"]', '[\"correct\",\"incorrect\",\"correct\",\"incorrect\"]', 0, 8, '2021-02-18 15:04:02', '2021-02-18 15:04:02'),
(930, 84, 1, '[\"give someone a job\"]', '[\"fire someone form a job\"]', 'incorrect', 0, 4, '2021-02-18 15:04:02', '2021-02-18 15:04:02'),
(931, 85, 1, '[\"strong\"]', '[]', 'incorrect', 0, 4, '2021-02-18 15:04:02', '2021-02-18 15:04:02'),
(932, 86, 1, '[\"have a difficulty in speaking\"]', '[\"have a difficulty in speaking\"]', 'correct', 4, 4, '2021-02-18 15:04:02', '2021-02-18 15:04:02'),
(933, 87, 1, '[\"memorable\",\"scary\",\"overrated\",\"predictabl\",\"gripping\",\"hilarious\"]', '[\"memorable\",\"scary\",\"overrated\",\"predictable\",\"gripping\",\"hilarious\"]', '[\"correct\",\"correct\",\"correct\",\"incorrect\",\"correct\",\"correct\"]', 0, 12, '2021-02-18 15:04:02', '2021-02-18 15:04:02'),
(934, 90, 1, '[null,\"sued\",\"spread\",\"damages\",null,null,\"attack\",\"troops\",\"flee\",null,null]', '[\"sued\",\"damages\",\"spread\",\"crisis\",\"hostage\",\"released\",\"invaded\",\"troops\",\"flee\",\"attack\",\"outcry\"]', '[\"incorrect\",\"incorrect\",\"correct\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"correct\",\"correct\",\"incorrect\",\"incorrect\"]', 0, 30, '2021-02-18 15:04:02', '2021-02-18 15:04:02'),
(935, 91, 1, '[\"True\"]', '[\"False\"]', 'incorrect', 0, 5, '2021-02-18 15:04:02', '2021-02-18 15:04:02'),
(936, 92, 1, '[\"False\"]', '[\"True\"]', 'incorrect', 0, 5, '2021-02-18 15:04:02', '2021-02-18 15:04:02'),
(937, 93, 1, '[\"True\"]', '[\"False\"]', 'incorrect', 0, 5, '2021-02-18 15:04:02', '2021-02-18 15:04:02'),
(938, 94, 1, '[\"True\"]', '[\"True\"]', 'correct', 5, 5, '2021-02-18 15:04:02', '2021-02-18 15:04:02'),
(939, 95, 1, '[\"three times\"]', '[\"three times\"]', 'correct', 5, 5, '2021-02-18 15:04:02', '2021-02-18 15:04:02'),
(940, 96, 1, 'Dear Manager:\r\nThank you so much for giving me the opportunity to tell you my opinion on our company policy and working conditions. i can summarize the feedback into 2 main aspects:\r\nfirst: we have a great working environment that based on commitment and integrity, and while saying that, i appreciate the assertiveness and fairness of the company\'s reactions to all people.\r\nsecond: the area we need to improve is that we need to improve the development of self-learning programs and reward the active people for doing that\r\nthanks and best regards\r\nAhmed', '[]', '', 0, 20, '2021-02-18 15:04:02', '2021-02-18 15:04:02'),
(941, 1, 17, '[\"What\"]', '[\"What\"]', 'correct', 1, 1, '2021-02-18 16:12:30', '2021-02-18 16:12:30'),
(942, 2, 17, '[\"They\'re\"]', '[\"They\'re\"]', 'correct', 1, 1, '2021-02-18 16:12:30', '2021-02-18 16:12:30'),
(943, 3, 17, '[\"It\"]', '[\"It\"]', 'correct', 1, 1, '2021-02-18 16:12:30', '2021-02-18 16:12:30'),
(944, 5, 17, '[\"are you spell\"]', '[\"do you spell\"]', 'incorrect', 0, 1, '2021-02-18 16:12:30', '2021-02-18 16:12:30'),
(945, 6, 17, '[\"one\"]', '[\"--\"]', 'incorrect', 0, 1, '2021-02-18 16:12:30', '2021-02-18 16:12:30'),
(946, 7, 17, '[\"Is Mr. Mike a teacher?\"]', '[\"Is Mr. Mike a teacher?\"]', 'correct', 1, 1, '2021-02-18 16:12:30', '2021-02-18 16:12:30'),
(947, 8, 17, '[\"your father job\"]', '[\"your father\'s job\"]', 'incorrect', 0, 1, '2021-02-18 16:12:30', '2021-02-18 16:12:30'),
(948, 9, 17, '[\"doesn\'t have\"]', '[\"doesn\'t have\"]', 'correct', 1, 1, '2021-02-18 16:12:30', '2021-02-18 16:12:30'),
(949, 10, 17, '[\"is work\"]', '[\"works\"]', 'incorrect', 0, 1, '2021-02-18 16:12:30', '2021-02-18 16:12:30'),
(950, 11, 17, '[\"We do not often go to the cinema.\"]', '[\"We do not often go to the cinema.\"]', 'correct', 1, 1, '2021-02-18 16:12:30', '2021-02-18 16:12:30'),
(951, 12, 17, '[\"make\"]', '[\"do\"]', 'incorrect', 0, 1, '2021-02-18 16:12:30', '2021-02-18 16:12:30'),
(952, 14, 17, '[\"usually\"]', '[\"ever\"]', 'incorrect', 0, 1, '2021-02-18 16:12:30', '2021-02-18 16:12:30'),
(953, 18, 17, '[\"tallest\"]', '[\"taller\"]', 'incorrect', 0, 1, '2021-02-18 16:12:30', '2021-02-18 16:12:30'),
(954, 19, 17, '[\"most\"]', '[\"most\"]', 'correct', 1, 1, '2021-02-18 16:12:30', '2021-02-18 16:12:30'),
(955, 23, 17, '[\"more friendly\"]', '[\"friendlier\"]', 'incorrect', 0, 1, '2021-02-18 16:12:30', '2021-02-18 16:12:30'),
(956, 28, 17, '[\"on\"]', '[\"on\"]', 'correct', 1, 1, '2021-02-18 16:12:30', '2021-02-18 16:12:30'),
(957, 29, 17, '[\"to\"]', '[\"for\"]', 'incorrect', 0, 1, '2021-02-18 16:12:30', '2021-02-18 16:12:30'),
(958, 30, 17, 'where is your grandmother live /', '[]', '', 0, 1, '2021-02-18 16:12:30', '2021-02-18 16:12:30'),
(959, 31, 17, 'how many his children/', '[]', '', 0, 1, '2021-02-18 16:12:30', '2021-02-18 16:12:30'),
(960, 32, 17, 'when are they wake up/', '[]', '', 0, 1, '2021-02-18 16:12:30', '2021-02-18 16:12:30'),
(961, 33, 17, '[\"I don\'t went to the cinema\\u00a0 last night.\\u00a0\"]', '[\"I didn\'t go to the cinema\\u00a0 last night.\\u00a0\"]', 'incorrect', 0, 1, '2021-02-18 16:12:30', '2021-02-18 16:12:30'),
(962, 34, 17, '[\"They haven\'t become very rich.\"]', '[\"They haven\'t become very rich.\"]', 'correct', 1, 1, '2021-02-18 16:12:30', '2021-02-18 16:12:30'),
(963, 35, 17, '[\"I haven\'t a big window in my room.\"]', '[\"I don\'t have a big window in my room.\"]', 'incorrect', 0, 1, '2021-02-18 16:12:30', '2021-02-18 16:12:30'),
(964, 36, 17, '[\"My mom doesn\'t have perfect cooking skills.\"]', '[\"My mom doesn\'t have perfect cooking skills.\"]', 'correct', 1, 1, '2021-02-18 16:12:30', '2021-02-18 16:12:30'),
(965, 37, 17, '[\"ill\",\"interesting\",\"difficult\",\"beautiful\",\"crowded\",\"intelliugent\",\"frendly\",\"beautiful\",\"quiet\"]', '[\"ill\",\"quiet\",\"difficult\",\"beautiful\",\"crowded\",\"intelligent\",\"rich\",\"friendly\",\"interesting\"]', '[\"correct\",\"incorrect\",\"correct\",\"correct\",\"correct\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 9, '2021-02-18 16:12:30', '2021-02-18 16:12:30'),
(966, 38, 17, '[null,null,null,null,null,null,null,null,null,null]', '[\"arrived\",\"want\",\"have been\",\"don\'t understand\",\"talk\",\"met\",\"came\",\"is doing\",\"needs\",\"is ringing\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 10, '2021-02-18 16:12:30', '2021-02-18 16:12:30'),
(967, 42, 17, '[null,null,\"8;45\",\"11\",null,\"at 8;20\",\"259\"]', '[\"24\",\"13.20\",\"18.45\",\"11\",\"5.15\",\"8.20\",\"259\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"correct\",\"incorrect\",\"incorrect\",\"correct\"]', 0, 7, '2021-02-18 16:12:30', '2021-02-18 16:12:30'),
(968, 48, 17, '[null,null,null,null,null,null,null,null,null,null]', '[\"speaking\",\"this is\",\"really sorry\",\"have to\",\"Oh, dear\",\"did she\",\"don\'t worry\",\"could you help me\",\"shall I\",\"that would be\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 10, '2021-02-18 16:12:30', '2021-02-18 16:12:30'),
(969, 49, 17, '[\"will accept\"]', '[\"would accept\"]', 'incorrect', 0, 3, '2021-02-18 16:12:30', '2021-02-18 16:12:30'),
(970, 50, 17, '[\"will pass\"]', '[\"will pass\"]', 'correct', 3, 3, '2021-02-18 16:12:30', '2021-02-18 16:12:30'),
(971, 51, 17, '[\"see\"]', '[\"seeing\"]', 'incorrect', 0, 3, '2021-02-18 16:12:30', '2021-02-18 16:12:30'),
(972, 52, 17, '[\"want -- next\"]', '[\"wanted -- the following\"]', 'incorrect', 0, 3, '2021-02-18 16:12:30', '2021-02-18 16:12:30'),
(973, 53, 17, '[\"which\"]', '[\"which\"]', 'correct', 3, 3, '2021-02-18 16:12:30', '2021-02-18 16:12:30'),
(974, 54, 17, '[\"which\"]', '[\"where\"]', 'incorrect', 0, 3, '2021-02-18 16:12:30', '2021-02-18 16:12:30'),
(975, 55, 17, '[\"were fired\"]', '[\"were fired\"]', 'correct', 3, 3, '2021-02-18 16:12:30', '2021-02-18 16:12:30'),
(976, 56, 17, '[\"give away\"]', '[\"give up\"]', 'incorrect', 0, 3, '2021-02-18 16:12:30', '2021-02-18 16:12:30'),
(977, 57, 17, '[\"go out with\"]', '[\"put up with\"]', 'incorrect', 0, 3, '2021-02-18 16:12:30', '2021-02-18 16:12:30'),
(978, 58, 17, '[\"unpolite\"]', '[\"impolite\"]', 'incorrect', 0, 3, '2021-02-18 16:12:30', '2021-02-18 16:12:30'),
(979, 59, 17, '[\"niece\"]', '[\"niece\"]', 'correct', 3, 3, '2021-02-18 16:12:30', '2021-02-18 16:12:30'),
(980, 75, 17, '[null,null,null]', '[\"care\",\"enjoyable\",\"disappointment\"]', '[\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 6, '2021-02-18 16:12:30', '2021-02-18 16:12:30'),
(981, 82, 17, '[null,null,null,null]', '[\"looting\",\"robbery\",\"burgle\",\"vandalize\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 8, '2021-02-18 16:12:30', '2021-02-18 16:12:30'),
(982, 83, 17, '[null,null,null,null]', '[\"politician\",\"economic\",\"environmental\",\"pollution\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 8, '2021-02-18 16:12:30', '2021-02-18 16:12:30'),
(983, 87, 17, '[null,null,null,null,null,null]', '[\"memorable\",\"scary\",\"overrated\",\"predictable\",\"gripping\",\"hilarious\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 12, '2021-02-18 16:12:30', '2021-02-18 16:12:30'),
(984, 90, 17, '[null,null,null,null,null,null,null,null,null,null,null]', '[\"sued\",\"damages\",\"spread\",\"crisis\",\"hostage\",\"released\",\"invaded\",\"troops\",\"flee\",\"attack\",\"outcry\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 30, '2021-02-18 16:12:30', '2021-02-18 16:12:30'),
(985, 1, 12, '[\"What\"]', '[\"What\"]', 'correct', 1, 1, '2021-02-18 16:21:52', '2021-02-18 16:21:52'),
(986, 2, 12, '[\"They\'re\"]', '[\"They\'re\"]', 'correct', 1, 1, '2021-02-18 16:21:52', '2021-02-18 16:21:52'),
(987, 3, 12, '[\"It\"]', '[\"It\"]', 'correct', 1, 1, '2021-02-18 16:21:52', '2021-02-18 16:21:52'),
(988, 5, 12, '[\"do you spell\"]', '[\"do you spell\"]', 'correct', 1, 1, '2021-02-18 16:21:52', '2021-02-18 16:21:52'),
(989, 6, 12, '[\"one\"]', '[\"--\"]', 'incorrect', 0, 1, '2021-02-18 16:21:52', '2021-02-18 16:21:52'),
(990, 7, 12, '[\"Is Mr. Mike a teacher?\"]', '[\"Is Mr. Mike a teacher?\"]', 'correct', 1, 1, '2021-02-18 16:21:52', '2021-02-18 16:21:52'),
(991, 8, 12, '[\"your father\'s job\"]', '[\"your father\'s job\"]', 'correct', 1, 1, '2021-02-18 16:21:52', '2021-02-18 16:21:52'),
(992, 9, 12, '[\"doesn\'t has\"]', '[\"doesn\'t have\"]', 'incorrect', 0, 1, '2021-02-18 16:21:52', '2021-02-18 16:21:52'),
(993, 10, 12, '[\"works\"]', '[\"works\"]', 'correct', 1, 1, '2021-02-18 16:21:52', '2021-02-18 16:21:52'),
(994, 11, 12, '[\"We often do not go to the cinema.\"]', '[\"We do not often go to the cinema.\"]', 'incorrect', 0, 1, '2021-02-18 16:21:52', '2021-02-18 16:21:52'),
(995, 12, 12, '[\"make\"]', '[\"do\"]', 'incorrect', 0, 1, '2021-02-18 16:21:52', '2021-02-18 16:21:52'),
(996, 14, 12, '[\"ever\"]', '[\"ever\"]', 'correct', 1, 1, '2021-02-18 16:21:52', '2021-02-18 16:21:52'),
(997, 18, 12, '[\"taller\"]', '[\"taller\"]', 'correct', 1, 1, '2021-02-18 16:21:52', '2021-02-18 16:21:52'),
(998, 19, 12, '[\"most\"]', '[\"most\"]', 'correct', 1, 1, '2021-02-18 16:21:52', '2021-02-18 16:21:52'),
(999, 23, 12, '[\"more friendly\"]', '[\"friendlier\"]', 'incorrect', 0, 1, '2021-02-18 16:21:52', '2021-02-18 16:21:52'),
(1000, 28, 12, '[\"on\"]', '[\"on\"]', 'correct', 1, 1, '2021-02-18 16:21:52', '2021-02-18 16:21:52'),
(1001, 29, 12, '[\"for\"]', '[\"for\"]', 'correct', 1, 1, '2021-02-18 16:21:52', '2021-02-18 16:21:52'),
(1002, 30, 12, 'where does sally\'s grandmother live ?', '[]', '', 0, 1, '2021-02-18 16:21:52', '2021-02-18 16:21:52'),
(1003, 31, 12, 'How many children did tom have ?', '[]', '', 0, 1, '2021-02-18 16:21:52', '2021-02-18 16:21:52'),
(1004, 32, 12, 'What time do they  get up every morning?', '[]', '', 0, 1, '2021-02-18 16:21:52', '2021-02-18 16:21:52'),
(1005, 33, 12, '[\"I didn\'t go to the cinema\\u00a0 last night.\\u00a0\"]', '[\"I didn\'t go to the cinema\\u00a0 last night.\\u00a0\"]', 'correct', 1, 1, '2021-02-18 16:21:52', '2021-02-18 16:21:52'),
(1006, 34, 12, '[\"They haven\'t become very rich.\"]', '[\"They haven\'t become very rich.\"]', 'correct', 1, 1, '2021-02-18 16:21:52', '2021-02-18 16:21:52'),
(1007, 35, 12, '[\"I don\'t have a big window in my room.\"]', '[\"I don\'t have a big window in my room.\"]', 'correct', 1, 1, '2021-02-18 16:21:52', '2021-02-18 16:21:52'),
(1008, 36, 12, '[\"My mom doesn\'t have perfect cooking skills.\"]', '[\"My mom doesn\'t have perfect cooking skills.\"]', 'correct', 1, 1, '2021-02-18 16:21:52', '2021-02-18 16:21:52'),
(1009, 37, 12, '[\"ill\",\"crowded\",\"difficult\",\"beautiful\",\"quiet\",\"intelligent\",\"rich\",\"friendly\",\"interesting\"]', '[\"ill\",\"quiet\",\"difficult\",\"beautiful\",\"crowded\",\"intelligent\",\"rich\",\"friendly\",\"interesting\"]', '[\"correct\",\"incorrect\",\"correct\",\"correct\",\"incorrect\",\"correct\",\"correct\",\"correct\",\"correct\"]', 0, 9, '2021-02-18 16:21:52', '2021-02-18 16:21:52'),
(1010, 38, 12, '[\"arrived\",\"have wanted\",\"Did - go\",\"haven\'t understood\",\"talk\",\"met\",\"came\",\"has done\",\"need\",\"is ringing\"]', '[\"arrived\",\"want\",\"have been\",\"don\'t understand\",\"talk\",\"met\",\"came\",\"is doing\",\"needs\",\"is ringing\"]', '[\"correct\",\"incorrect\",\"incorrect\",\"incorrect\",\"correct\",\"correct\",\"correct\",\"incorrect\",\"incorrect\",\"correct\"]', 0, 10, '2021-02-18 16:21:52', '2021-02-18 16:21:52'),
(1011, 42, 12, '[\"Saturday, 24th\",\"13:20\",\"18:45\",\"11th\",\"5:15\",\"8:20\",\"259\"]', '[\"24\",\"13.20\",\"18.45\",\"11\",\"5.15\",\"8.20\",\"259\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"correct\"]', 0, 7, '2021-02-18 16:21:52', '2021-02-18 16:21:52'),
(1012, 48, 12, '[\"speaking\",\"this is\",\"really sorry\",\"have to\",\"Oh, dear\",\"did she\",\"don\'t worry\",\"could you help me\",\"shall I\",\"that would be\"]', '[\"speaking\",\"this is\",\"really sorry\",\"have to\",\"Oh, dear\",\"did she\",\"don\'t worry\",\"could you help me\",\"shall I\",\"that would be\"]', '[\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\"]', 10, 10, '2021-02-18 16:21:52', '2021-02-18 16:21:52'),
(1013, 49, 12, '[\"will accept\"]', '[\"would accept\"]', 'incorrect', 0, 3, '2021-02-18 16:21:52', '2021-02-18 16:21:52'),
(1014, 50, 12, '[\"would have passed\"]', '[\"will pass\"]', 'incorrect', 0, 3, '2021-02-18 16:21:52', '2021-02-18 16:21:52'),
(1015, 51, 12, '[\"see\"]', '[\"seeing\"]', 'incorrect', 0, 3, '2021-02-18 16:21:52', '2021-02-18 16:21:52'),
(1016, 52, 12, '[\"wants -- next\"]', '[\"wanted -- the following\"]', 'incorrect', 0, 3, '2021-02-18 16:21:52', '2021-02-18 16:21:52'),
(1017, 53, 12, '[\"which\"]', '[\"which\"]', 'correct', 3, 3, '2021-02-18 16:21:52', '2021-02-18 16:21:52'),
(1018, 54, 12, '[\"where\"]', '[\"where\"]', 'correct', 3, 3, '2021-02-18 16:21:52', '2021-02-18 16:21:52'),
(1019, 55, 12, '[\"were fired\"]', '[\"were fired\"]', 'correct', 3, 3, '2021-02-18 16:21:52', '2021-02-18 16:21:52'),
(1020, 56, 12, '[\"give up\"]', '[\"give up\"]', 'correct', 3, 3, '2021-02-18 16:21:52', '2021-02-18 16:21:52'),
(1021, 57, 12, '[\"put up with\"]', '[\"put up with\"]', 'correct', 3, 3, '2021-02-18 16:21:52', '2021-02-18 16:21:52'),
(1022, 58, 12, '[\"impolite\"]', '[\"impolite\"]', 'correct', 3, 3, '2021-02-18 16:21:52', '2021-02-18 16:21:52'),
(1023, 59, 12, '[\"nephew\"]', '[\"niece\"]', 'incorrect', 0, 3, '2021-02-18 16:21:52', '2021-02-18 16:21:52'),
(1024, 60, 12, '[\"mother-in-law\"]', '[\"mother-in-law\"]', 'correct', 3, 3, '2021-02-18 16:21:52', '2021-02-18 16:21:52'),
(1025, 61, 12, '[\"17\"]', '[\"17\"]', 'correct', 2, 2, '2021-02-18 16:21:52', '2021-02-18 16:21:52'),
(1026, 62, 12, '[\"20\"]', '[\"20\"]', 'correct', 2, 2, '2021-02-18 16:21:52', '2021-02-18 16:21:52'),
(1027, 63, 12, '[\"$20\"]', '[\"$20\"]', 'correct', 2, 2, '2021-02-18 16:21:52', '2021-02-18 16:21:52'),
(1028, 64, 12, '[\"10\"]', '[\"10\"]', 'correct', 2, 2, '2021-02-18 16:21:52', '2021-02-18 16:21:52'),
(1029, 65, 12, '[\"on his body\"]', '[\"on his body\"]', 'correct', 2, 2, '2021-02-18 16:21:52', '2021-02-18 16:21:52'),
(1030, 66, 12, '[\"stomach problems\"]', '[\"stomach problems\"]', 'correct', 2, 2, '2021-02-18 16:21:52', '2021-02-18 16:21:52'),
(1031, 67, 12, '[\"Although\"]', '[\"Although\"]', 'correct', 4, 4, '2021-02-18 16:21:52', '2021-02-18 16:21:52'),
(1032, 68, 12, '[\"However being tired\"]', '[\"Despite being tired\"]', 'incorrect', 0, 4, '2021-02-18 16:21:52', '2021-02-18 16:21:52'),
(1033, 69, 12, '[\"used to work\"]', '[\"am used to working\"]', 'incorrect', 0, 4, '2021-02-18 16:21:52', '2021-02-18 16:21:52'),
(1034, 71, 12, '[\"forgot\"]', '[\"had forgotten\"]', 'incorrect', 0, 4, '2021-02-18 16:21:52', '2021-02-18 16:21:52'),
(1035, 72, 12, '[\"keep in touch with\"]', '[\"keep in touch with\"]', 'correct', 4, 4, '2021-02-18 16:21:52', '2021-02-18 16:21:52'),
(1036, 73, 12, '[\"splitted\"]', '[\"split\"]', 'incorrect', 0, 4, '2021-02-18 16:21:52', '2021-02-18 16:21:52'),
(1037, 74, 12, '[\"don\'t\"]', '[\"not to\"]', 'incorrect', 0, 4, '2021-02-18 16:21:52', '2021-02-18 16:21:52'),
(1038, 75, 12, '[\"care\",\"enjoyness\",\"disappointment\"]', '[\"care\",\"enjoyable\",\"disappointment\"]', '[\"correct\",\"incorrect\",\"correct\"]', 0, 6, '2021-02-18 16:21:52', '2021-02-18 16:21:52'),
(1039, 76, 12, '[\"at a party\"]', '[\"at a party\"]', 'correct', 4, 4, '2021-02-18 16:21:52', '2021-02-18 16:21:52'),
(1040, 77, 12, '[\"lost her passport\"]', '[\"lost her passport\"]', 'correct', 4, 4, '2021-02-18 16:21:52', '2021-02-18 16:21:52'),
(1041, 78, 12, '[\"lost her job\"]', '[\"lost her job\"]', 'correct', 4, 4, '2021-02-18 16:21:52', '2021-02-18 16:21:52'),
(1042, 79, 12, '[\"the country\"]', '[\"the country\"]', 'correct', 4, 4, '2021-02-18 16:21:52', '2021-02-18 16:21:52'),
(1043, 80, 12, '[\"an aggressive\"]', '[\"an aggressive\"]', 'correct', 4, 4, '2021-02-18 16:21:53', '2021-02-18 16:21:53'),
(1044, 81, 12, '[\"some boxing gloves\"]', '[\"a bike\"]', 'incorrect', 0, 4, '2021-02-18 16:21:53', '2021-02-18 16:21:53'),
(1045, 82, 12, '[\"loots\",\"robbery\",\"burgee\",\"vandal\"]', '[\"looting\",\"robbery\",\"burgle\",\"vandalize\"]', '[\"incorrect\",\"correct\",\"incorrect\",\"incorrect\"]', 0, 8, '2021-02-18 16:21:53', '2021-02-18 16:21:53'),
(1046, 83, 12, '[\"politest\",\"economical\",\"environmental\",\"polluted\"]', '[\"politician\",\"economic\",\"environmental\",\"pollution\"]', '[\"incorrect\",\"incorrect\",\"correct\",\"incorrect\"]', 0, 8, '2021-02-18 16:21:53', '2021-02-18 16:21:53'),
(1047, 84, 12, '[\"fire someone form a job\"]', '[\"fire someone form a job\"]', 'correct', 4, 4, '2021-02-18 16:21:53', '2021-02-18 16:21:53'),
(1048, 85, 12, '[\"a lot of money\"]', '[]', 'incorrect', 0, 4, '2021-02-18 16:21:53', '2021-02-18 16:21:53'),
(1049, 86, 12, '[\"have a difficulty in speaking\"]', '[\"have a difficulty in speaking\"]', 'correct', 4, 4, '2021-02-18 16:21:53', '2021-02-18 16:21:53'),
(1050, 87, 12, '[\"memorable\",\"scary\",\"gripping\",\"predictable\",\"overrated\",\"hilarious\"]', '[\"memorable\",\"scary\",\"overrated\",\"predictable\",\"gripping\",\"hilarious\"]', '[\"correct\",\"correct\",\"incorrect\",\"correct\",\"incorrect\",\"correct\"]', 0, 12, '2021-02-18 16:21:53', '2021-02-18 16:21:53'),
(1051, 90, 12, '[\"hostage\",\"flee\",\"sued\",\"spread\",\"crisis\",\"invaded\",\"released\",\"troops\",\"outcry\",null,null]', '[\"sued\",\"damages\",\"spread\",\"crisis\",\"hostage\",\"released\",\"invaded\",\"troops\",\"flee\",\"attack\",\"outcry\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"correct\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 30, '2021-02-18 16:21:53', '2021-02-18 16:21:53'),
(1052, 91, 12, '[\"True\"]', '[\"False\"]', 'incorrect', 0, 5, '2021-02-18 16:21:53', '2021-02-18 16:21:53'),
(1053, 92, 12, '[\"True\"]', '[\"True\"]', 'correct', 5, 5, '2021-02-18 16:21:53', '2021-02-18 16:21:53'),
(1054, 93, 12, '[\"False\"]', '[\"False\"]', 'correct', 5, 5, '2021-02-18 16:21:53', '2021-02-18 16:21:53'),
(1055, 94, 12, '[\"False\"]', '[\"True\"]', 'incorrect', 0, 5, '2021-02-18 16:21:53', '2021-02-18 16:21:53'),
(1056, 95, 12, '[\"three times\"]', '[\"three times\"]', 'correct', 5, 5, '2021-02-18 16:21:53', '2021-02-18 16:21:53'),
(1057, 1, 13, '[\"What\"]', '[\"What\"]', 'correct', 1, 1, '2021-02-18 17:49:46', '2021-02-18 17:49:46'),
(1058, 2, 13, '[\"They\'re\"]', '[\"They\'re\"]', 'correct', 1, 1, '2021-02-18 17:49:46', '2021-02-18 17:49:46'),
(1059, 3, 13, '[\"It\"]', '[\"It\"]', 'correct', 1, 1, '2021-02-18 17:49:46', '2021-02-18 17:49:46'),
(1060, 5, 13, '[\"do you spell\"]', '[\"do you spell\"]', 'correct', 1, 1, '2021-02-18 17:49:46', '2021-02-18 17:49:46'),
(1061, 6, 13, '[\"a\"]', '[\"--\"]', 'incorrect', 0, 1, '2021-02-18 17:49:46', '2021-02-18 17:49:46'),
(1062, 7, 13, '[\"Is Mr. Mike a teacher?\"]', '[\"Is Mr. Mike a teacher?\"]', 'correct', 1, 1, '2021-02-18 17:49:46', '2021-02-18 17:49:46'),
(1063, 8, 13, '[\"your father\'s job\"]', '[\"your father\'s job\"]', 'correct', 1, 1, '2021-02-18 17:49:46', '2021-02-18 17:49:46'),
(1064, 9, 13, '[\"hasn\'t\"]', '[\"doesn\'t have\"]', 'incorrect', 0, 1, '2021-02-18 17:49:46', '2021-02-18 17:49:46'),
(1065, 10, 13, '[\"works\"]', '[\"works\"]', 'correct', 1, 1, '2021-02-18 17:49:46', '2021-02-18 17:49:46'),
(1066, 11, 13, '[\"We not often go to the cinema.\"]', '[\"We do not often go to the cinema.\"]', 'incorrect', 0, 1, '2021-02-18 17:49:46', '2021-02-18 17:49:46');
INSERT INTO `employee_quizzes_old` (`id`, `quiz_id`, `employee_id`, `emp_answer`, `correct_answer`, `check_answer`, `emp_score`, `score`, `created_at`, `updated_at`) VALUES
(1067, 12, 13, '[\"do\"]', '[\"do\"]', 'correct', 1, 1, '2021-02-18 17:49:46', '2021-02-18 17:49:46'),
(1068, 14, 13, '[\"sometimes\"]', '[\"ever\"]', 'incorrect', 0, 1, '2021-02-18 17:49:46', '2021-02-18 17:49:46'),
(1069, 18, 13, '[\"taller\"]', '[\"taller\"]', 'correct', 1, 1, '2021-02-18 17:49:46', '2021-02-18 17:49:46'),
(1070, 19, 13, '[\"most\"]', '[\"most\"]', 'correct', 1, 1, '2021-02-18 17:49:46', '2021-02-18 17:49:46'),
(1071, 23, 13, '[\"friendlier\"]', '[\"friendlier\"]', 'correct', 1, 1, '2021-02-18 17:49:46', '2021-02-18 17:49:46'),
(1072, 28, 13, '[\"in\"]', '[\"on\"]', 'incorrect', 0, 1, '2021-02-18 17:49:46', '2021-02-18 17:49:46'),
(1073, 29, 13, '[\"for\"]', '[\"for\"]', 'correct', 1, 1, '2021-02-18 17:49:46', '2021-02-18 17:49:46'),
(1074, 33, 13, '[\"I don\'t went to the cinema\\u00a0 last night.\\u00a0\"]', '[\"I didn\'t go to the cinema\\u00a0 last night.\\u00a0\"]', 'incorrect', 0, 1, '2021-02-18 17:49:46', '2021-02-18 17:49:46'),
(1075, 34, 13, '[\"They haven\'t become very rich.\"]', '[\"They haven\'t become very rich.\"]', 'correct', 1, 1, '2021-02-18 17:49:46', '2021-02-18 17:49:46'),
(1076, 35, 13, '[\"I don\'t have a big window in my room.\"]', '[\"I don\'t have a big window in my room.\"]', 'correct', 1, 1, '2021-02-18 17:49:46', '2021-02-18 17:49:46'),
(1077, 36, 13, '[\"My mom doesn\'t  has perfect cooking skills.\"]', '[\"My mom doesn\'t have perfect cooking skills.\"]', 'incorrect', 0, 1, '2021-02-18 17:49:46', '2021-02-18 17:49:46'),
(1078, 37, 13, '[\"ill\",\"crowded\",\"difficult\",\"beautiful\",\"crowded\",\"intelligent\",\"rich\",null,\"interesting\"]', '[\"ill\",\"quiet\",\"difficult\",\"beautiful\",\"crowded\",\"intelligent\",\"rich\",\"friendly\",\"interesting\"]', '[\"correct\",\"incorrect\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"incorrect\",\"correct\"]', 0, 9, '2021-02-18 17:49:46', '2021-02-18 17:49:46'),
(1079, 38, 13, '[\"arraived\",\"want\",\"going\",\"not understand\",\"is talting\",\"meet\",\"comes\",\"is doing\",\"needs\",\"is ringing\"]', '[\"arrived\",\"want\",\"have been\",\"don\'t understand\",\"talk\",\"met\",\"came\",\"is doing\",\"needs\",\"is ringing\"]', '[\"incorrect\",\"correct\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"correct\",\"correct\",\"correct\"]', 0, 10, '2021-02-18 17:49:46', '2021-02-18 17:49:46'),
(1080, 42, 13, '[\"24.2\",\"13.20\",\"18.55\",\"11.3\",\"5.15\",\"8.20\",\"259\"]', '[\"24\",\"13.20\",\"18.45\",\"11\",\"5.15\",\"8.20\",\"259\"]', '[\"incorrect\",\"correct\",\"incorrect\",\"incorrect\",\"correct\",\"correct\",\"correct\"]', 0, 7, '2021-02-18 17:49:46', '2021-02-18 17:49:46'),
(1081, 48, 13, '[\"this is\",null,null,null,null,null,null,null,null,null]', '[\"speaking\",\"this is\",\"really sorry\",\"have to\",\"Oh, dear\",\"did she\",\"don\'t worry\",\"could you help me\",\"shall I\",\"that would be\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 10, '2021-02-18 17:49:46', '2021-02-18 17:49:46'),
(1082, 49, 13, '[\"will accept\"]', '[\"would accept\"]', 'incorrect', 0, 3, '2021-02-18 17:49:46', '2021-02-18 17:49:46'),
(1083, 50, 13, '[\"will pass\"]', '[\"will pass\"]', 'correct', 3, 3, '2021-02-18 17:49:46', '2021-02-18 17:49:46'),
(1084, 51, 13, '[\"seeing\"]', '[\"seeing\"]', 'correct', 3, 3, '2021-02-18 17:49:46', '2021-02-18 17:49:46'),
(1085, 52, 13, '[\"wants -- next\"]', '[\"wanted -- the following\"]', 'incorrect', 0, 3, '2021-02-18 17:49:46', '2021-02-18 17:49:46'),
(1086, 53, 13, '[\"which\"]', '[\"which\"]', 'correct', 3, 3, '2021-02-18 17:49:46', '2021-02-18 17:49:46'),
(1087, 54, 13, '[\"which\"]', '[\"where\"]', 'incorrect', 0, 3, '2021-02-18 17:49:46', '2021-02-18 17:49:46'),
(1088, 55, 13, '[\"are fired\"]', '[\"were fired\"]', 'incorrect', 0, 3, '2021-02-18 17:49:46', '2021-02-18 17:49:46'),
(1089, 56, 13, '[\"give up\"]', '[\"give up\"]', 'correct', 3, 3, '2021-02-18 17:49:46', '2021-02-18 17:49:46'),
(1090, 57, 13, '[\"put up with\"]', '[\"put up with\"]', 'correct', 3, 3, '2021-02-18 17:49:46', '2021-02-18 17:49:46'),
(1091, 58, 13, '[\"impolite\"]', '[\"impolite\"]', 'correct', 3, 3, '2021-02-18 17:49:46', '2021-02-18 17:49:46'),
(1092, 59, 13, '[\"niece\"]', '[\"niece\"]', 'correct', 3, 3, '2021-02-18 17:49:46', '2021-02-18 17:49:46'),
(1093, 60, 13, '[\"mother-in-law\"]', '[\"mother-in-law\"]', 'correct', 3, 3, '2021-02-18 17:49:46', '2021-02-18 17:49:46'),
(1094, 61, 13, '[\"17\"]', '[\"17\"]', 'correct', 2, 2, '2021-02-18 17:49:46', '2021-02-18 17:49:46'),
(1095, 62, 13, '[\"20\"]', '[\"20\"]', 'correct', 2, 2, '2021-02-18 17:49:46', '2021-02-18 17:49:46'),
(1096, 63, 13, '[\"$20\"]', '[\"$20\"]', 'correct', 2, 2, '2021-02-18 17:49:46', '2021-02-18 17:49:46'),
(1097, 64, 13, '[\"10\"]', '[\"10\"]', 'correct', 2, 2, '2021-02-18 17:49:46', '2021-02-18 17:49:46'),
(1098, 65, 13, '[\"in the water tank\"]', '[\"on his body\"]', 'incorrect', 0, 2, '2021-02-18 17:49:46', '2021-02-18 17:49:46'),
(1099, 66, 13, '[\"stomach problems\"]', '[\"stomach problems\"]', 'correct', 2, 2, '2021-02-18 17:49:46', '2021-02-18 17:49:46'),
(1100, 67, 13, '[\"Although\"]', '[\"Although\"]', 'correct', 4, 4, '2021-02-18 17:49:46', '2021-02-18 17:49:46'),
(1101, 68, 13, '[\"Despite being tired\"]', '[\"Despite being tired\"]', 'correct', 4, 4, '2021-02-18 17:49:46', '2021-02-18 17:49:46'),
(1102, 69, 13, '[\"am used to work\"]', '[\"am used to working\"]', 'incorrect', 0, 4, '2021-02-18 17:49:46', '2021-02-18 17:49:46'),
(1103, 71, 13, '[\"have forgotten\"]', '[\"had forgotten\"]', 'incorrect', 0, 4, '2021-02-18 17:49:46', '2021-02-18 17:49:46'),
(1104, 72, 13, '[\"keep in touch with\"]', '[\"keep in touch with\"]', 'correct', 4, 4, '2021-02-18 17:49:46', '2021-02-18 17:49:46'),
(1105, 73, 13, '[\"have splitted\"]', '[\"split\"]', 'incorrect', 0, 4, '2021-02-18 17:49:46', '2021-02-18 17:49:46'),
(1106, 74, 13, '[\"don\'t\"]', '[\"not to\"]', 'incorrect', 0, 4, '2021-02-18 17:49:46', '2021-02-18 17:49:46'),
(1107, 75, 13, '[\"care\",\"enjoyment\",\"disappointment\"]', '[\"care\",\"enjoyable\",\"disappointment\"]', '[\"correct\",\"incorrect\",\"correct\"]', 0, 6, '2021-02-18 17:49:46', '2021-02-18 17:49:46'),
(1108, 76, 13, '[\"in Australia\"]', '[\"at a party\"]', 'incorrect', 0, 4, '2021-02-18 17:49:46', '2021-02-18 17:49:46'),
(1109, 77, 13, '[\"lost her passport\"]', '[\"lost her passport\"]', 'correct', 4, 4, '2021-02-18 17:49:46', '2021-02-18 17:49:46'),
(1110, 78, 13, '[\"lost her job\"]', '[\"lost her job\"]', 'correct', 4, 4, '2021-02-18 17:49:46', '2021-02-18 17:49:46'),
(1111, 79, 13, '[\"London\"]', '[\"the country\"]', 'incorrect', 0, 4, '2021-02-18 17:49:46', '2021-02-18 17:49:46'),
(1112, 80, 13, '[\"an aggressive\"]', '[\"an aggressive\"]', 'correct', 4, 4, '2021-02-18 17:49:46', '2021-02-18 17:49:46'),
(1113, 81, 13, '[\"some boxing gloves\"]', '[\"a bike\"]', 'incorrect', 0, 4, '2021-02-18 17:49:46', '2021-02-18 17:49:46'),
(1114, 82, 13, '[null,null,null,null]', '[\"looting\",\"robbery\",\"burgle\",\"vandalize\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 8, '2021-02-18 17:49:47', '2021-02-18 17:49:47'),
(1115, 83, 13, '[null,null,null,null]', '[\"politician\",\"economic\",\"environmental\",\"pollution\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 8, '2021-02-18 17:49:47', '2021-02-18 17:49:47'),
(1116, 87, 13, '[null,null,null,null,null,null]', '[\"memorable\",\"scary\",\"overrated\",\"predictable\",\"gripping\",\"hilarious\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 12, '2021-02-18 17:49:47', '2021-02-18 17:49:47'),
(1117, 90, 13, '[null,null,null,null,null,null,null,null,null,null,null]', '[\"sued\",\"damages\",\"spread\",\"crisis\",\"hostage\",\"released\",\"invaded\",\"troops\",\"flee\",\"attack\",\"outcry\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 30, '2021-02-18 17:49:47', '2021-02-18 17:49:47'),
(1118, 91, 13, '[\"True\"]', '[\"False\"]', 'incorrect', 0, 5, '2021-02-18 17:49:47', '2021-02-18 17:49:47'),
(1119, 92, 13, '[\"False\"]', '[\"True\"]', 'incorrect', 0, 5, '2021-02-18 17:49:47', '2021-02-18 17:49:47'),
(1120, 93, 13, '[\"False\"]', '[\"False\"]', 'correct', 5, 5, '2021-02-18 17:49:47', '2021-02-18 17:49:47'),
(1121, 94, 13, '[\"True\"]', '[\"True\"]', 'correct', 5, 5, '2021-02-18 17:49:47', '2021-02-18 17:49:47'),
(1122, 95, 13, '[\"three times\"]', '[\"three times\"]', 'correct', 5, 5, '2021-02-18 17:49:47', '2021-02-18 17:49:47'),
(1123, 96, 13, 'I think Sysmex is very good company that I loved to work in it \r\nit from my pleasure that I joined Sysmex\r\nI think the time is late bout I thank god to give me last chance to joined Sysmex', '[]', '', 0, 20, '2021-02-18 17:49:47', '2021-02-18 17:49:47'),
(1124, 1, 16, '[\"What\"]', '[\"What\"]', 'correct', 1, 1, '2021-02-18 17:51:13', '2021-02-18 17:51:13'),
(1125, 2, 16, '[\"They\'re\"]', '[\"They\'re\"]', 'correct', 1, 1, '2021-02-18 17:51:13', '2021-02-18 17:51:13'),
(1126, 3, 16, '[\"It\"]', '[\"It\"]', 'correct', 1, 1, '2021-02-18 17:51:13', '2021-02-18 17:51:13'),
(1127, 5, 16, '[\"do you spell\"]', '[\"do you spell\"]', 'correct', 1, 1, '2021-02-18 17:51:13', '2021-02-18 17:51:13'),
(1128, 6, 16, '[\"a\"]', '[\"--\"]', 'incorrect', 0, 1, '2021-02-18 17:51:13', '2021-02-18 17:51:13'),
(1129, 7, 16, '[\"Is Mr. Mike a teacher?\"]', '[\"Is Mr. Mike a teacher?\"]', 'correct', 1, 1, '2021-02-18 17:51:13', '2021-02-18 17:51:13'),
(1130, 8, 16, '[\"your father\'s job\"]', '[\"your father\'s job\"]', 'correct', 1, 1, '2021-02-18 17:51:13', '2021-02-18 17:51:13'),
(1131, 9, 16, '[\"doesn\'t have\"]', '[\"doesn\'t have\"]', 'correct', 1, 1, '2021-02-18 17:51:13', '2021-02-18 17:51:13'),
(1132, 10, 16, '[\"works\"]', '[\"works\"]', 'correct', 1, 1, '2021-02-18 17:51:13', '2021-02-18 17:51:13'),
(1133, 11, 16, '[\"We often do not go to the cinema.\"]', '[\"We do not often go to the cinema.\"]', 'incorrect', 0, 1, '2021-02-18 17:51:13', '2021-02-18 17:51:13'),
(1134, 12, 16, '[\"do\"]', '[\"do\"]', 'correct', 1, 1, '2021-02-18 17:51:13', '2021-02-18 17:51:13'),
(1135, 14, 16, '[\"sometimes\"]', '[\"ever\"]', 'incorrect', 0, 1, '2021-02-18 17:51:13', '2021-02-18 17:51:13'),
(1136, 18, 16, '[\"taller\"]', '[\"taller\"]', 'correct', 1, 1, '2021-02-18 17:51:13', '2021-02-18 17:51:13'),
(1137, 19, 16, '[\"most\"]', '[\"most\"]', 'correct', 1, 1, '2021-02-18 17:51:13', '2021-02-18 17:51:13'),
(1138, 23, 16, '[\"more friendly\"]', '[\"friendlier\"]', 'incorrect', 0, 1, '2021-02-18 17:51:13', '2021-02-18 17:51:13'),
(1139, 28, 16, '[\"on\"]', '[\"on\"]', 'correct', 1, 1, '2021-02-18 17:51:13', '2021-02-18 17:51:13'),
(1140, 29, 16, '[\"for\"]', '[\"for\"]', 'correct', 1, 1, '2021-02-18 17:51:13', '2021-02-18 17:51:13'),
(1141, 30, 16, 'where\'s your mother live?', '[]', '', 0, 1, '2021-02-18 17:51:13', '2021-02-18 17:51:13'),
(1142, 31, 16, 'how many children does tom have?', '[]', '', 0, 1, '2021-02-18 17:51:13', '2021-02-18 17:51:13'),
(1143, 32, 16, 'what\'s the clock do you get up ?', '[]', '', 0, 1, '2021-02-18 17:51:13', '2021-02-18 17:51:13'),
(1144, 33, 16, '[\"I don\'t went to the cinema\\u00a0 last night.\\u00a0\"]', '[\"I didn\'t go to the cinema\\u00a0 last night.\\u00a0\"]', 'incorrect', 0, 1, '2021-02-18 17:51:13', '2021-02-18 17:51:13'),
(1145, 34, 16, '[\"They haven\'t become very rich.\",\"They don\'t have become very rich.\"]', '[\"They haven\'t become very rich.\"]', 'incorrect', 0, 1, '2021-02-18 17:51:13', '2021-02-18 17:51:13'),
(1146, 35, 16, '[\"I don\'t have a big window in my room.\",\"I haven\'t a big window in my room.\"]', '[\"I don\'t have a big window in my room.\"]', 'incorrect', 0, 1, '2021-02-18 17:51:13', '2021-02-18 17:51:13'),
(1147, 36, 16, '[\"My mom doesn\'t have perfect cooking skills.\",\"My mom hasn\'t perfect cooking skills.\"]', '[\"My mom doesn\'t have perfect cooking skills.\"]', 'incorrect', 0, 1, '2021-02-18 17:51:13', '2021-02-18 17:51:13'),
(1148, 37, 16, '[\"ill\",\"interesting\",\"difficult\",\"beautiful\",\"crowded\",\"intelligent\",\"rich\",\"friendly\",\"quiet\"]', '[\"ill\",\"quiet\",\"difficult\",\"beautiful\",\"crowded\",\"intelligent\",\"rich\",\"friendly\",\"interesting\"]', '[\"correct\",\"incorrect\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"incorrect\"]', 0, 9, '2021-02-18 17:51:13', '2021-02-18 17:51:13'),
(1149, 38, 16, '[\"arrived\",\"want\",\"do    -   go\",\"didn\'t understand\",\"talk\",\"met\",\"came\",\"is doing\",\"is needing\",\"is ringing\"]', '[\"arrived\",\"want\",\"have been\",\"don\'t understand\",\"talk\",\"met\",\"came\",\"is doing\",\"needs\",\"is ringing\"]', '[\"correct\",\"correct\",\"incorrect\",\"incorrect\",\"correct\",\"correct\",\"correct\",\"correct\",\"incorrect\",\"correct\"]', 0, 10, '2021-02-18 17:51:13', '2021-02-18 17:51:13'),
(1150, 42, 16, '[\"24\",\"13:20\",\"18:45\",\"11\",\"5:15\",\"8:20\",\"259\"]', '[\"24\",\"13.20\",\"18.45\",\"11\",\"5.15\",\"8.20\",\"259\"]', '[\"correct\",\"incorrect\",\"incorrect\",\"correct\",\"incorrect\",\"incorrect\",\"correct\"]', 0, 7, '2021-02-18 17:51:13', '2021-02-18 17:51:13'),
(1151, 48, 16, '[\"speaking\",\"that would be\",\"realy sorry\",\"have to\",\"oh , dear\",\"shall i\",\"don\'t worry\",\"could you help\",\"did she\",\"this is\"]', '[\"speaking\",\"this is\",\"really sorry\",\"have to\",\"Oh, dear\",\"did she\",\"don\'t worry\",\"could you help me\",\"shall I\",\"that would be\"]', '[\"correct\",\"incorrect\",\"incorrect\",\"correct\",\"incorrect\",\"incorrect\",\"correct\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 10, '2021-02-18 17:51:13', '2021-02-18 17:51:13'),
(1152, 49, 16, '[\"will accept\"]', '[\"would accept\"]', 'incorrect', 0, 3, '2021-02-18 17:51:13', '2021-02-18 17:51:13'),
(1153, 50, 16, '[\"will pass\",\"would have passed\"]', '[\"will pass\"]', 'incorrect', 0, 3, '2021-02-18 17:51:13', '2021-02-18 17:51:13'),
(1154, 51, 16, '[\"see\"]', '[\"seeing\"]', 'incorrect', 0, 3, '2021-02-18 17:51:13', '2021-02-18 17:51:13'),
(1155, 52, 16, '[\"want -- next\"]', '[\"wanted -- the following\"]', 'incorrect', 0, 3, '2021-02-18 17:51:13', '2021-02-18 17:51:13'),
(1156, 53, 16, '[\"which\"]', '[\"which\"]', 'correct', 3, 3, '2021-02-18 17:51:13', '2021-02-18 17:51:13'),
(1157, 54, 16, '[\"where\"]', '[\"where\"]', 'correct', 3, 3, '2021-02-18 17:51:13', '2021-02-18 17:51:13'),
(1158, 55, 16, '[\"were fired\"]', '[\"were fired\"]', 'correct', 3, 3, '2021-02-18 17:51:13', '2021-02-18 17:51:13'),
(1159, 56, 16, '[\"give away\"]', '[\"give up\"]', 'incorrect', 0, 3, '2021-02-18 17:51:13', '2021-02-18 17:51:13'),
(1160, 57, 16, '[\"put up with\"]', '[\"put up with\"]', 'correct', 3, 3, '2021-02-18 17:51:13', '2021-02-18 17:51:13'),
(1161, 58, 16, '[\"impolite\"]', '[\"impolite\"]', 'correct', 3, 3, '2021-02-18 17:51:13', '2021-02-18 17:51:13'),
(1162, 59, 16, '[\"niece\"]', '[\"niece\"]', 'correct', 3, 3, '2021-02-18 17:51:13', '2021-02-18 17:51:13'),
(1163, 60, 16, '[\"stepmother\"]', '[\"mother-in-law\"]', 'incorrect', 0, 3, '2021-02-18 17:51:13', '2021-02-18 17:51:13'),
(1164, 61, 16, '[\"17\"]', '[\"17\"]', 'correct', 2, 2, '2021-02-18 17:51:13', '2021-02-18 17:51:13'),
(1165, 62, 16, '[\"20\"]', '[\"20\"]', 'correct', 2, 2, '2021-02-18 17:51:13', '2021-02-18 17:51:13'),
(1166, 63, 16, '[\"$20\"]', '[\"$20\"]', 'correct', 2, 2, '2021-02-18 17:51:13', '2021-02-18 17:51:13'),
(1167, 64, 16, '[\"10\"]', '[\"10\"]', 'correct', 2, 2, '2021-02-18 17:51:13', '2021-02-18 17:51:13'),
(1168, 65, 16, '[\"in the water tank\"]', '[\"on his body\"]', 'incorrect', 0, 2, '2021-02-18 17:51:13', '2021-02-18 17:51:13'),
(1169, 66, 16, '[\"stomach problems\"]', '[\"stomach problems\"]', 'correct', 2, 2, '2021-02-18 17:51:13', '2021-02-18 17:51:13'),
(1170, 67, 16, '[\"However\"]', '[\"Although\"]', 'incorrect', 0, 4, '2021-02-18 17:51:13', '2021-02-18 17:51:13'),
(1171, 68, 16, '[\"However being tired\"]', '[\"Despite being tired\"]', 'incorrect', 0, 4, '2021-02-18 17:51:13', '2021-02-18 17:51:13'),
(1172, 69, 16, '[\"am used to working\"]', '[\"am used to working\"]', 'correct', 4, 4, '2021-02-18 17:51:13', '2021-02-18 17:51:13'),
(1173, 71, 16, '[\"had forgotten\"]', '[\"had forgotten\"]', 'correct', 4, 4, '2021-02-18 17:51:13', '2021-02-18 17:51:13'),
(1174, 72, 16, '[\"keep in touch with\"]', '[\"keep in touch with\"]', 'correct', 4, 4, '2021-02-18 17:51:13', '2021-02-18 17:51:13'),
(1175, 73, 16, '[\"splitted\"]', '[\"split\"]', 'incorrect', 0, 4, '2021-02-18 17:51:13', '2021-02-18 17:51:13'),
(1176, 74, 16, '[\"don\'t\"]', '[\"not to\"]', 'incorrect', 0, 4, '2021-02-18 17:51:13', '2021-02-18 17:51:13'),
(1177, 75, 16, '[\"care\",\"enjoyful\",\"disappointment\"]', '[\"care\",\"enjoyable\",\"disappointment\"]', '[\"correct\",\"incorrect\",\"correct\"]', 0, 6, '2021-02-18 17:51:13', '2021-02-18 17:51:13'),
(1178, 76, 16, '[\"at a party\"]', '[\"at a party\"]', 'correct', 4, 4, '2021-02-18 17:51:13', '2021-02-18 17:51:13'),
(1179, 77, 16, '[\"lost her passport\"]', '[\"lost her passport\"]', 'correct', 4, 4, '2021-02-18 17:51:13', '2021-02-18 17:51:13'),
(1180, 78, 16, '[\"got into financial trouble\",\"lost her job\"]', '[\"lost her job\"]', 'incorrect', 0, 4, '2021-02-18 17:51:13', '2021-02-18 17:51:13'),
(1181, 79, 16, '[\"the country\"]', '[\"the country\"]', 'correct', 4, 4, '2021-02-18 17:51:13', '2021-02-18 17:51:13'),
(1182, 80, 16, '[\"an aggressive\",\"a hard-working\"]', '[\"an aggressive\"]', 'incorrect', 0, 4, '2021-02-18 17:51:13', '2021-02-18 17:51:13'),
(1183, 81, 16, '[\"a bike\"]', '[\"a bike\"]', 'correct', 4, 4, '2021-02-18 17:51:13', '2021-02-18 17:51:13'),
(1184, 82, 16, '[\"loot\",\"rob\",\"burglar\",\"vandal\"]', '[\"looting\",\"robbery\",\"burgle\",\"vandalize\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 8, '2021-02-18 17:51:13', '2021-02-18 17:51:13'),
(1185, 83, 16, '[\"politician\",\"economist\",\"enviromental\",\"pullutment\"]', '[\"politician\",\"economic\",\"environmental\",\"pollution\"]', '[\"correct\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 8, '2021-02-18 17:51:13', '2021-02-18 17:51:13'),
(1186, 84, 16, '[\"fire someone form a job\"]', '[\"fire someone form a job\"]', 'correct', 4, 4, '2021-02-18 17:51:13', '2021-02-18 17:51:13'),
(1187, 85, 16, '[\"strong\"]', '[]', 'incorrect', 0, 4, '2021-02-18 17:51:13', '2021-02-18 17:51:13'),
(1188, 86, 16, '[\"have a difficulty in speaking\"]', '[\"have a difficulty in speaking\"]', 'correct', 4, 4, '2021-02-18 17:51:13', '2021-02-18 17:51:13'),
(1189, 87, 16, '[null,\"scary\",\"predictable\",null,null,null]', '[\"memorable\",\"scary\",\"overrated\",\"predictable\",\"gripping\",\"hilarious\"]', '[\"incorrect\",\"correct\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 12, '2021-02-18 17:51:13', '2021-02-18 17:51:13'),
(1190, 90, 16, '[null,null,null,null,null,null,null,null,null,null,null]', '[\"sued\",\"damages\",\"spread\",\"crisis\",\"hostage\",\"released\",\"invaded\",\"troops\",\"flee\",\"attack\",\"outcry\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 30, '2021-02-18 17:51:13', '2021-02-18 17:51:13'),
(1191, 91, 16, '[\"True\"]', '[\"False\"]', 'incorrect', 0, 5, '2021-02-18 17:51:13', '2021-02-18 17:51:13'),
(1192, 92, 16, '[\"False\"]', '[\"True\"]', 'incorrect', 0, 5, '2021-02-18 17:51:13', '2021-02-18 17:51:13'),
(1193, 93, 16, '[\"False\"]', '[\"False\"]', 'correct', 5, 5, '2021-02-18 17:51:13', '2021-02-18 17:51:13'),
(1194, 94, 16, '[\"True\"]', '[\"True\"]', 'correct', 5, 5, '2021-02-18 17:51:13', '2021-02-18 17:51:13'),
(1195, 95, 16, '[\"four times\"]', '[\"three times\"]', 'incorrect', 0, 5, '2021-02-18 17:51:13', '2021-02-18 17:51:13'),
(1196, 96, 16, 'Dear sir,\r\nhope you are well\r\nappreciate your effort  for improving work environment and improving company\'s policy and working condition .\r\ni  suggest to improving policy to be written to be clear for all employees and do regular meeting with employees to ensure polices .     \r\nBR,\r\nthanks,', '[]', '', 0, 20, '2021-02-18 17:51:13', '2021-02-18 17:51:13'),
(1197, 1, 33, '[\"What\"]', '[\"What\"]', 'correct', 1, 1, '2021-02-18 21:05:14', '2021-02-18 21:05:14'),
(1198, 2, 33, '[\"They\'re\"]', '[\"They\'re\"]', 'correct', 1, 1, '2021-02-18 21:05:14', '2021-02-18 21:05:14'),
(1199, 3, 33, '[\"It\"]', '[\"It\"]', 'correct', 1, 1, '2021-02-18 21:05:14', '2021-02-18 21:05:14'),
(1200, 5, 33, '[\"do you spell\"]', '[\"do you spell\"]', 'correct', 1, 1, '2021-02-18 21:05:14', '2021-02-18 21:05:14'),
(1201, 6, 33, '[\"a\"]', '[\"--\"]', 'incorrect', 0, 1, '2021-02-18 21:05:14', '2021-02-18 21:05:14'),
(1202, 7, 33, '[\"Is Mr. Mike a teacher?\"]', '[\"Is Mr. Mike a teacher?\"]', 'correct', 1, 1, '2021-02-18 21:05:14', '2021-02-18 21:05:14'),
(1203, 8, 33, '[\"your father\'s job\"]', '[\"your father\'s job\"]', 'correct', 1, 1, '2021-02-18 21:05:14', '2021-02-18 21:05:14'),
(1204, 9, 33, '[\"hasn\'t\"]', '[\"doesn\'t have\"]', 'incorrect', 0, 1, '2021-02-18 21:05:14', '2021-02-18 21:05:14'),
(1205, 10, 33, '[\"works\"]', '[\"works\"]', 'correct', 1, 1, '2021-02-18 21:05:14', '2021-02-18 21:05:14'),
(1206, 11, 33, '[\"We do not often go to the cinema.\"]', '[\"We do not often go to the cinema.\"]', 'correct', 1, 1, '2021-02-18 21:05:14', '2021-02-18 21:05:14'),
(1207, 12, 33, '[\"do\"]', '[\"do\"]', 'correct', 1, 1, '2021-02-18 21:05:14', '2021-02-18 21:05:14'),
(1208, 14, 33, '[\"sometimes\"]', '[\"ever\"]', 'incorrect', 0, 1, '2021-02-18 21:05:14', '2021-02-18 21:05:14'),
(1209, 18, 33, '[\"taller\"]', '[\"taller\"]', 'correct', 1, 1, '2021-02-18 21:05:14', '2021-02-18 21:05:14'),
(1210, 19, 33, '[\"most\"]', '[\"most\"]', 'correct', 1, 1, '2021-02-18 21:05:14', '2021-02-18 21:05:14'),
(1211, 23, 33, '[\"more friendly\"]', '[\"friendlier\"]', 'incorrect', 0, 1, '2021-02-18 21:05:14', '2021-02-18 21:05:14'),
(1212, 28, 33, '[\"on\"]', '[\"on\"]', 'correct', 1, 1, '2021-02-18 21:05:14', '2021-02-18 21:05:14'),
(1213, 29, 33, '[\"for\"]', '[\"for\"]', 'correct', 1, 1, '2021-02-18 21:05:14', '2021-02-18 21:05:14'),
(1214, 30, 33, 'where is sally\'s grand mother living ?', '[]', '', 0, 1, '2021-02-18 21:05:14', '2021-02-18 21:05:14'),
(1215, 31, 33, 'how many children had Tom ?', '[]', '', 0, 1, '2021-02-18 21:05:14', '2021-02-18 21:05:14'),
(1216, 32, 33, 'when do them get up every morning ?', '[]', '', 0, 1, '2021-02-18 21:05:14', '2021-02-18 21:05:14'),
(1217, 33, 33, '[\"I didn\'t go to the cinema\\u00a0 last night.\\u00a0\"]', '[\"I didn\'t go to the cinema\\u00a0 last night.\\u00a0\"]', 'correct', 1, 1, '2021-02-18 21:05:14', '2021-02-18 21:05:14'),
(1218, 34, 33, '[\"They haven\'t become very rich.\"]', '[\"They haven\'t become very rich.\"]', 'correct', 1, 1, '2021-02-18 21:05:14', '2021-02-18 21:05:14'),
(1219, 35, 33, '[\"I don\'t have a big window in my room.\"]', '[\"I don\'t have a big window in my room.\"]', 'correct', 1, 1, '2021-02-18 21:05:14', '2021-02-18 21:05:14'),
(1220, 36, 33, '[\"My mom doesn\'t have perfect cooking skills.\"]', '[\"My mom doesn\'t have perfect cooking skills.\"]', 'correct', 1, 1, '2021-02-18 21:05:14', '2021-02-18 21:05:14'),
(1221, 37, 33, '[\"ill\",\"quiet\",\"difficult\",\"beautiful\",\"crowded\",\"intelligent\",\"rich\",\"friendly\",\"interesting\"]', '[\"ill\",\"quiet\",\"difficult\",\"beautiful\",\"crowded\",\"intelligent\",\"rich\",\"friendly\",\"interesting\"]', '[\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\"]', 9, 9, '2021-02-18 21:05:14', '2021-02-18 21:05:14'),
(1222, 38, 33, '[\"arrived\",\"wanted\",\"have   been\",\"do\",\"are talking\",\"had met\",\"came\",\"does\",\"doesn\'t\",\"is ringing\"]', '[\"arrived\",\"want\",\"have been\",\"don\'t understand\",\"talk\",\"met\",\"came\",\"is doing\",\"needs\",\"is ringing\"]', '[\"correct\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"correct\",\"incorrect\",\"incorrect\",\"correct\"]', 0, 10, '2021-02-18 21:05:14', '2021-02-18 21:05:14'),
(1223, 42, 33, '[\"24\",\"13:20\",\"18:45\",\"11\",\"5:15\",\"8:20\",\"259\"]', '[\"24\",\"13.20\",\"18.45\",\"11\",\"5.15\",\"8.20\",\"259\"]', '[\"correct\",\"incorrect\",\"incorrect\",\"correct\",\"incorrect\",\"incorrect\",\"correct\"]', 0, 7, '2021-02-18 21:05:14', '2021-02-18 21:05:14'),
(1224, 48, 33, '[\"speaking\",\"this is\",\"really sorry\",\"have to\",\"Oh, dear\",\"did she\",\"don\'t worry\",\"could you help me\",\"shall I\",\"that would be\"]', '[\"speaking\",\"this is\",\"really sorry\",\"have to\",\"Oh, dear\",\"did she\",\"don\'t worry\",\"could you help me\",\"shall I\",\"that would be\"]', '[\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\"]', 10, 10, '2021-02-18 21:05:14', '2021-02-18 21:05:14'),
(1225, 49, 33, '[\"will accept\"]', '[\"would accept\"]', 'incorrect', 0, 3, '2021-02-18 21:05:14', '2021-02-18 21:05:14'),
(1226, 50, 33, '[\"will pass\"]', '[\"will pass\"]', 'correct', 3, 3, '2021-02-18 21:05:14', '2021-02-18 21:05:14'),
(1227, 51, 33, '[\"see\"]', '[\"seeing\"]', 'incorrect', 0, 3, '2021-02-18 21:05:14', '2021-02-18 21:05:14'),
(1228, 52, 33, '[\"wants -- next\"]', '[\"wanted -- the following\"]', 'incorrect', 0, 3, '2021-02-18 21:05:14', '2021-02-18 21:05:14'),
(1229, 53, 33, '[\"which\"]', '[\"which\"]', 'correct', 3, 3, '2021-02-18 21:05:14', '2021-02-18 21:05:14'),
(1230, 54, 33, '[\"where\"]', '[\"where\"]', 'correct', 3, 3, '2021-02-18 21:05:14', '2021-02-18 21:05:14'),
(1231, 55, 33, '[\"have been fired\"]', '[\"were fired\"]', 'incorrect', 0, 3, '2021-02-18 21:05:14', '2021-02-18 21:05:14'),
(1232, 56, 33, '[\"give up\"]', '[\"give up\"]', 'correct', 3, 3, '2021-02-18 21:05:14', '2021-02-18 21:05:14'),
(1233, 57, 33, '[\"go out with\"]', '[\"put up with\"]', 'incorrect', 0, 3, '2021-02-18 21:05:14', '2021-02-18 21:05:14'),
(1234, 58, 33, '[\"impolite\"]', '[\"impolite\"]', 'correct', 3, 3, '2021-02-18 21:05:14', '2021-02-18 21:05:14'),
(1235, 59, 33, '[\"nephew\"]', '[\"niece\"]', 'incorrect', 0, 3, '2021-02-18 21:05:14', '2021-02-18 21:05:14'),
(1236, 60, 33, '[\"mother-in-law\"]', '[\"mother-in-law\"]', 'correct', 3, 3, '2021-02-18 21:05:14', '2021-02-18 21:05:14'),
(1237, 61, 33, '[\"17\"]', '[\"17\"]', 'correct', 2, 2, '2021-02-18 21:05:14', '2021-02-18 21:05:14'),
(1238, 62, 33, '[\"20\"]', '[\"20\"]', 'correct', 2, 2, '2021-02-18 21:05:14', '2021-02-18 21:05:14'),
(1239, 63, 33, '[\"$20\"]', '[\"$20\"]', 'correct', 2, 2, '2021-02-18 21:05:14', '2021-02-18 21:05:14'),
(1240, 64, 33, '[\"2\"]', '[\"10\"]', 'incorrect', 0, 2, '2021-02-18 21:05:14', '2021-02-18 21:05:14'),
(1241, 65, 33, '[\"in the water tank\"]', '[\"on his body\"]', 'incorrect', 0, 2, '2021-02-18 21:05:14', '2021-02-18 21:05:14'),
(1242, 66, 33, '[\"stomach problems\"]', '[\"stomach problems\"]', 'correct', 2, 2, '2021-02-18 21:05:14', '2021-02-18 21:05:14'),
(1243, 67, 33, '[\"Although\"]', '[\"Although\"]', 'correct', 4, 4, '2021-02-18 21:05:14', '2021-02-18 21:05:14'),
(1244, 68, 33, '[\"However being tired\"]', '[\"Despite being tired\"]', 'incorrect', 0, 4, '2021-02-18 21:05:14', '2021-02-18 21:05:14'),
(1245, 69, 33, '[\"am used to working\"]', '[\"am used to working\"]', 'correct', 4, 4, '2021-02-18 21:05:14', '2021-02-18 21:05:14'),
(1246, 71, 33, '[\"had forgotten\"]', '[\"had forgotten\"]', 'correct', 4, 4, '2021-02-18 21:05:14', '2021-02-18 21:05:14'),
(1247, 72, 33, '[\"keep touch with\"]', '[\"keep in touch with\"]', 'incorrect', 0, 4, '2021-02-18 21:05:14', '2021-02-18 21:05:14'),
(1248, 73, 33, '[\"have splitted\"]', '[\"split\"]', 'incorrect', 0, 4, '2021-02-18 21:05:14', '2021-02-18 21:05:14'),
(1249, 74, 33, '[\"don\'t\"]', '[\"not to\"]', 'incorrect', 0, 4, '2021-02-18 21:05:14', '2021-02-18 21:05:14'),
(1250, 75, 33, '[\"care\",\"boring\",\"disappointment\"]', '[\"care\",\"enjoyable\",\"disappointment\"]', '[\"correct\",\"incorrect\",\"correct\"]', 0, 6, '2021-02-18 21:05:14', '2021-02-18 21:05:14'),
(1251, 76, 33, '[\"at a party\"]', '[\"at a party\"]', 'correct', 4, 4, '2021-02-18 21:05:14', '2021-02-18 21:05:14'),
(1252, 77, 33, '[\"missed her flight\",\"lost her passport\"]', '[\"lost her passport\"]', 'incorrect', 0, 4, '2021-02-18 21:05:14', '2021-02-18 21:05:14'),
(1253, 78, 33, '[\"got into financial trouble\",\"lost her job\"]', '[\"lost her job\"]', 'incorrect', 0, 4, '2021-02-18 21:05:14', '2021-02-18 21:05:14'),
(1254, 79, 33, '[\"London\"]', '[\"the country\"]', 'incorrect', 0, 4, '2021-02-18 21:05:14', '2021-02-18 21:05:14'),
(1255, 80, 33, '[\"an aggressive\"]', '[\"an aggressive\"]', 'correct', 4, 4, '2021-02-18 21:05:14', '2021-02-18 21:05:14'),
(1256, 81, 33, '[\"a bike\"]', '[\"a bike\"]', 'correct', 4, 4, '2021-02-18 21:05:14', '2021-02-18 21:05:14'),
(1257, 82, 33, '[\"looty\",\"robbery\",\"burgle\",\"vandalize\"]', '[\"looting\",\"robbery\",\"burgle\",\"vandalize\"]', '[\"incorrect\",\"correct\",\"correct\",\"correct\"]', 0, 8, '2021-02-18 21:05:14', '2021-02-18 21:05:14'),
(1258, 83, 33, '[\"politician\",\"economical\",\"environmental\",\"pollution\"]', '[\"politician\",\"economic\",\"environmental\",\"pollution\"]', '[\"correct\",\"incorrect\",\"correct\",\"correct\"]', 0, 8, '2021-02-18 21:05:14', '2021-02-18 21:05:14'),
(1259, 84, 33, '[\"fire someone form a job\"]', '[\"fire someone form a job\"]', 'correct', 4, 4, '2021-02-18 21:05:14', '2021-02-18 21:05:14'),
(1260, 85, 33, '[\"strong\"]', '[]', 'incorrect', 0, 4, '2021-02-18 21:05:14', '2021-02-18 21:05:14'),
(1261, 86, 33, '[\"have a rough voice\"]', '[\"have a difficulty in speaking\"]', 'incorrect', 0, 4, '2021-02-18 21:05:14', '2021-02-18 21:05:14'),
(1262, 87, 33, '[\"memorable\",\"scary\",\"overrated\",\"predictable\",\"gripping\",\"hilarious\"]', '[\"memorable\",\"scary\",\"overrated\",\"predictable\",\"gripping\",\"hilarious\"]', '[\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\"]', 12, 12, '2021-02-18 21:05:14', '2021-02-18 21:05:14'),
(1263, 90, 33, '[null,null,\"spread\",null,null,null,null,null,null,null,null]', '[\"sued\",\"damages\",\"spread\",\"crisis\",\"hostage\",\"released\",\"invaded\",\"troops\",\"flee\",\"attack\",\"outcry\"]', '[\"incorrect\",\"incorrect\",\"correct\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 30, '2021-02-18 21:05:14', '2021-02-18 21:05:14'),
(1264, 91, 33, '[\"True\"]', '[\"False\"]', 'incorrect', 0, 5, '2021-02-18 21:05:14', '2021-02-18 21:05:14'),
(1265, 92, 33, '[\"False\"]', '[\"True\"]', 'incorrect', 0, 5, '2021-02-18 21:05:14', '2021-02-18 21:05:14'),
(1266, 93, 33, '[\"True\"]', '[\"False\"]', 'incorrect', 0, 5, '2021-02-18 21:05:14', '2021-02-18 21:05:14'),
(1267, 94, 33, '[\"True\"]', '[\"True\"]', 'correct', 5, 5, '2021-02-18 21:05:14', '2021-02-18 21:05:14'),
(1268, 95, 33, '[\"three times\"]', '[\"three times\"]', 'correct', 5, 5, '2021-02-18 21:05:14', '2021-02-18 21:05:14'),
(1269, 1, 25, '[\"What\"]', '[\"What\"]', 'correct', 1, 1, '2021-02-18 21:12:25', '2021-02-18 21:12:25'),
(1270, 2, 25, '[\"They\'re\"]', '[\"They\'re\"]', 'correct', 1, 1, '2021-02-18 21:12:25', '2021-02-18 21:12:25'),
(1271, 3, 25, '[\"It\"]', '[\"It\"]', 'correct', 1, 1, '2021-02-18 21:12:25', '2021-02-18 21:12:25'),
(1272, 5, 25, '[\"do you spell\"]', '[\"do you spell\"]', 'correct', 1, 1, '2021-02-18 21:12:25', '2021-02-18 21:12:25'),
(1273, 6, 25, '[\"one\"]', '[\"--\"]', 'incorrect', 0, 1, '2021-02-18 21:12:25', '2021-02-18 21:12:25'),
(1274, 7, 25, '[\"Is Mr. Mike a teacher?\"]', '[\"Is Mr. Mike a teacher?\"]', 'correct', 1, 1, '2021-02-18 21:12:25', '2021-02-18 21:12:25'),
(1275, 8, 25, '[\"your father job\"]', '[\"your father\'s job\"]', 'incorrect', 0, 1, '2021-02-18 21:12:25', '2021-02-18 21:12:25'),
(1276, 9, 25, '[\"don\'t have\"]', '[\"doesn\'t have\"]', 'incorrect', 0, 1, '2021-02-18 21:12:25', '2021-02-18 21:12:25'),
(1277, 10, 25, '[\"is work\"]', '[\"works\"]', 'incorrect', 0, 1, '2021-02-18 21:12:25', '2021-02-18 21:12:25'),
(1278, 11, 25, '[\"We do not often go to the cinema.\"]', '[\"We do not often go to the cinema.\"]', 'correct', 1, 1, '2021-02-18 21:12:25', '2021-02-18 21:12:25'),
(1279, 12, 25, '[\"do\"]', '[\"do\"]', 'correct', 1, 1, '2021-02-18 21:12:25', '2021-02-18 21:12:25'),
(1280, 14, 25, '[\"ever\"]', '[\"ever\"]', 'correct', 1, 1, '2021-02-18 21:12:25', '2021-02-18 21:12:25'),
(1281, 18, 25, '[\"tallest\"]', '[\"taller\"]', 'incorrect', 0, 1, '2021-02-18 21:12:25', '2021-02-18 21:12:25'),
(1282, 19, 25, '[\"most\"]', '[\"most\"]', 'correct', 1, 1, '2021-02-18 21:12:25', '2021-02-18 21:12:25'),
(1283, 23, 25, '[\"more friendly\"]', '[\"friendlier\"]', 'incorrect', 0, 1, '2021-02-18 21:12:25', '2021-02-18 21:12:25'),
(1284, 28, 25, '[\"on\"]', '[\"on\"]', 'correct', 1, 1, '2021-02-18 21:12:25', '2021-02-18 21:12:25'),
(1285, 29, 25, '[\"for\"]', '[\"for\"]', 'correct', 1, 1, '2021-02-18 21:12:25', '2021-02-18 21:12:25'),
(1286, 30, 25, 'where is Sally\'s grandmother lives ?', '[]', '', 0, 1, '2021-02-18 21:12:25', '2021-02-18 21:12:25'),
(1287, 31, 25, 'how many children tom you  have  ?', '[]', '', 0, 1, '2021-02-18 21:12:25', '2021-02-18 21:12:25'),
(1288, 32, 25, 'when do you are get up every morning ?', '[]', '', 0, 1, '2021-02-18 21:12:25', '2021-02-18 21:12:25'),
(1289, 33, 25, '[\"I didn\'t go to the cinema\\u00a0 last night.\\u00a0\"]', '[\"I didn\'t go to the cinema\\u00a0 last night.\\u00a0\"]', 'correct', 1, 1, '2021-02-18 21:12:25', '2021-02-18 21:12:25'),
(1290, 34, 25, '[\"They don\'t have become very rich.\"]', '[\"They haven\'t become very rich.\"]', 'incorrect', 0, 1, '2021-02-18 21:12:25', '2021-02-18 21:12:25'),
(1291, 35, 25, '[\"I haven\'t a big window in my room.\"]', '[\"I don\'t have a big window in my room.\"]', 'incorrect', 0, 1, '2021-02-18 21:12:25', '2021-02-18 21:12:25'),
(1292, 36, 25, '[\"My mom doesn\'t have perfect cooking skills.\"]', '[\"My mom doesn\'t have perfect cooking skills.\"]', 'correct', 1, 1, '2021-02-18 21:12:25', '2021-02-18 21:12:25'),
(1293, 37, 25, '[\"ill\",\"quiet\",\"difficult\",\"beautiful\",\"crowded\",\"intelligent\",\"rich\",\"friendly\",\"interesting\"]', '[\"ill\",\"quiet\",\"difficult\",\"beautiful\",\"crowded\",\"intelligent\",\"rich\",\"friendly\",\"interesting\"]', '[\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\"]', 9, 9, '2021-02-18 21:12:25', '2021-02-18 21:12:25'),
(1294, 38, 25, '[\"am arrived\",\"want\",\"did , went\",\"not understandare\",\"are talking\",\"met\",\"came\",\"did\",\"needed\",\"is ringing\"]', '[\"arrived\",\"want\",\"have been\",\"don\'t understand\",\"talk\",\"met\",\"came\",\"is doing\",\"needs\",\"is ringing\"]', '[\"incorrect\",\"correct\",\"incorrect\",\"incorrect\",\"incorrect\",\"correct\",\"correct\",\"incorrect\",\"incorrect\",\"correct\"]', 0, 10, '2021-02-18 21:12:25', '2021-02-18 21:12:25'),
(1295, 42, 25, '[\"24\",\"13:20\",\"18:45\",\"11\",\"5:50\",\"8:12\",\"259\"]', '[\"24\",\"13.20\",\"18.45\",\"11\",\"5.15\",\"8.20\",\"259\"]', '[\"correct\",\"incorrect\",\"incorrect\",\"correct\",\"incorrect\",\"incorrect\",\"correct\"]', 0, 7, '2021-02-18 21:12:25', '2021-02-18 21:12:25'),
(1296, 48, 25, '[\"speaking\",\"this is\",\"really sorry\",\"have to\",\"Oh, dear\",\"did she\",\"don\'t worry\",\"could you help me\",\"shall I\",\"that would be\"]', '[\"speaking\",\"this is\",\"really sorry\",\"have to\",\"Oh, dear\",\"did she\",\"don\'t worry\",\"could you help me\",\"shall I\",\"that would be\"]', '[\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\"]', 10, 10, '2021-02-18 21:12:25', '2021-02-18 21:12:25'),
(1297, 49, 25, '[\"will accept\"]', '[\"would accept\"]', 'incorrect', 0, 3, '2021-02-18 21:12:25', '2021-02-18 21:12:25'),
(1298, 50, 25, '[\"will pass\"]', '[\"will pass\"]', 'correct', 3, 3, '2021-02-18 21:12:25', '2021-02-18 21:12:25'),
(1299, 51, 25, '[\"see\"]', '[\"seeing\"]', 'incorrect', 0, 3, '2021-02-18 21:12:25', '2021-02-18 21:12:25'),
(1300, 52, 25, '[\"wants -- next\"]', '[\"wanted -- the following\"]', 'incorrect', 0, 3, '2021-02-18 21:12:25', '2021-02-18 21:12:25'),
(1301, 53, 25, '[\"who\"]', '[\"which\"]', 'incorrect', 0, 3, '2021-02-18 21:12:25', '2021-02-18 21:12:25'),
(1302, 54, 25, '[\"where\"]', '[\"where\"]', 'correct', 3, 3, '2021-02-18 21:12:25', '2021-02-18 21:12:25'),
(1303, 55, 25, '[\"are fired\"]', '[\"were fired\"]', 'incorrect', 0, 3, '2021-02-18 21:12:25', '2021-02-18 21:12:25'),
(1304, 56, 25, '[\"give up\"]', '[\"give up\"]', 'correct', 3, 3, '2021-02-18 21:12:25', '2021-02-18 21:12:25'),
(1305, 57, 25, '[\"go out with\"]', '[\"put up with\"]', 'incorrect', 0, 3, '2021-02-18 21:12:25', '2021-02-18 21:12:25'),
(1306, 58, 25, '[\"unpolite\"]', '[\"impolite\"]', 'incorrect', 0, 3, '2021-02-18 21:12:25', '2021-02-18 21:12:25'),
(1307, 59, 25, '[\"niece\"]', '[\"niece\"]', 'correct', 3, 3, '2021-02-18 21:12:25', '2021-02-18 21:12:25'),
(1308, 60, 25, '[\"mother-in-law\"]', '[\"mother-in-law\"]', 'correct', 3, 3, '2021-02-18 21:12:25', '2021-02-18 21:12:25'),
(1309, 61, 25, '[\"17\"]', '[\"17\"]', 'correct', 2, 2, '2021-02-18 21:12:25', '2021-02-18 21:12:25'),
(1310, 62, 25, '[\"20\"]', '[\"20\"]', 'correct', 2, 2, '2021-02-18 21:12:25', '2021-02-18 21:12:25'),
(1311, 63, 25, '[\"$20\"]', '[\"$20\"]', 'correct', 2, 2, '2021-02-18 21:12:25', '2021-02-18 21:12:25'),
(1312, 64, 25, '[\"10\"]', '[\"10\"]', 'correct', 2, 2, '2021-02-18 21:12:25', '2021-02-18 21:12:25'),
(1313, 65, 25, '[\"in the water tank\"]', '[\"on his body\"]', 'incorrect', 0, 2, '2021-02-18 21:12:25', '2021-02-18 21:12:25'),
(1314, 66, 25, '[\"stomach problems\"]', '[\"stomach problems\"]', 'correct', 2, 2, '2021-02-18 21:12:25', '2021-02-18 21:12:25'),
(1315, 67, 25, '[\"Although\"]', '[\"Although\"]', 'correct', 4, 4, '2021-02-18 21:12:25', '2021-02-18 21:12:25'),
(1316, 68, 25, '[\"However being tired\"]', '[\"Despite being tired\"]', 'incorrect', 0, 4, '2021-02-18 21:12:25', '2021-02-18 21:12:25'),
(1317, 69, 25, '[\"used to work\"]', '[\"am used to working\"]', 'incorrect', 0, 4, '2021-02-18 21:12:25', '2021-02-18 21:12:25'),
(1318, 71, 25, '[\"forgot\"]', '[\"had forgotten\"]', 'incorrect', 0, 4, '2021-02-18 21:12:25', '2021-02-18 21:12:25'),
(1319, 72, 25, '[\"keep touching\"]', '[\"keep in touch with\"]', 'incorrect', 0, 4, '2021-02-18 21:12:25', '2021-02-18 21:12:25'),
(1320, 73, 25, '[\"splitted\"]', '[\"split\"]', 'incorrect', 0, 4, '2021-02-18 21:12:25', '2021-02-18 21:12:25'),
(1321, 74, 25, '[\"don\'t\"]', '[\"not to\"]', 'incorrect', 0, 4, '2021-02-18 21:12:25', '2021-02-18 21:12:25'),
(1322, 75, 25, '[\"care\",null,\"disappoint\"]', '[\"care\",\"enjoyable\",\"disappointment\"]', '[\"correct\",\"incorrect\",\"incorrect\"]', 0, 6, '2021-02-18 21:12:25', '2021-02-18 21:12:25'),
(1323, 76, 25, '[\"at a party\"]', '[\"at a party\"]', 'correct', 4, 4, '2021-02-18 21:12:25', '2021-02-18 21:12:25'),
(1324, 77, 25, '[\"lost her passport\"]', '[\"lost her passport\"]', 'correct', 4, 4, '2021-02-18 21:12:25', '2021-02-18 21:12:25'),
(1325, 78, 25, '[\"got into financial trouble\"]', '[\"lost her job\"]', 'incorrect', 0, 4, '2021-02-18 21:12:25', '2021-02-18 21:12:25'),
(1326, 79, 25, '[\"the country\"]', '[\"the country\"]', 'correct', 4, 4, '2021-02-18 21:12:25', '2021-02-18 21:12:25'),
(1327, 80, 25, '[\"an aggressive\"]', '[\"an aggressive\"]', 'correct', 4, 4, '2021-02-18 21:12:25', '2021-02-18 21:12:25'),
(1328, 81, 25, '[\"some boxing gloves\"]', '[\"a bike\"]', 'incorrect', 0, 4, '2021-02-18 21:12:25', '2021-02-18 21:12:25'),
(1329, 82, 25, '[null,null,null,null]', '[\"looting\",\"robbery\",\"burgle\",\"vandalize\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 8, '2021-02-18 21:12:25', '2021-02-18 21:12:25'),
(1330, 83, 25, '[null,null,null,null]', '[\"politician\",\"economic\",\"environmental\",\"pollution\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 8, '2021-02-18 21:12:25', '2021-02-18 21:12:25'),
(1331, 84, 25, '[\"fire someone form a job\"]', '[\"fire someone form a job\"]', 'correct', 4, 4, '2021-02-18 21:12:25', '2021-02-18 21:12:25'),
(1332, 85, 25, '[\"strong\"]', '[]', 'incorrect', 0, 4, '2021-02-18 21:12:25', '2021-02-18 21:12:25'),
(1333, 86, 25, '[\"have a rough voice\"]', '[\"have a difficulty in speaking\"]', 'incorrect', 0, 4, '2021-02-18 21:12:25', '2021-02-18 21:12:25'),
(1334, 87, 25, '[\"memorable\",\"scary\",\"overrated\",\"predictable\",\"gripping\",\"hilarious\"]', '[\"memorable\",\"scary\",\"overrated\",\"predictable\",\"gripping\",\"hilarious\"]', '[\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\"]', 12, 12, '2021-02-18 21:12:25', '2021-02-18 21:12:25'),
(1335, 90, 25, '[null,null,null,null,null,null,null,null,null,null,null]', '[\"sued\",\"damages\",\"spread\",\"crisis\",\"hostage\",\"released\",\"invaded\",\"troops\",\"flee\",\"attack\",\"outcry\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 30, '2021-02-18 21:12:25', '2021-02-18 21:12:25'),
(1336, 91, 25, '[\"False\"]', '[\"False\"]', 'correct', 5, 5, '2021-02-18 21:12:25', '2021-02-18 21:12:25'),
(1337, 92, 25, '[\"True\"]', '[\"True\"]', 'correct', 5, 5, '2021-02-18 21:12:25', '2021-02-18 21:12:25'),
(1338, 93, 25, '[\"False\"]', '[\"False\"]', 'correct', 5, 5, '2021-02-18 21:12:25', '2021-02-18 21:12:25'),
(1339, 94, 25, '[\"True\"]', '[\"True\"]', 'correct', 5, 5, '2021-02-18 21:12:25', '2021-02-18 21:12:25'),
(1340, 95, 25, '[\"three times\"]', '[\"three times\"]', 'correct', 5, 5, '2021-02-18 21:12:25', '2021-02-18 21:12:25'),
(1341, 96, 25, 'Dear Dr,\r\nI want to thanks you for all things you do to improve all in company  and make all employs happy for working  in company and iam  one form them so i want say  thanks you fo you join for us in gym and we want go all for us on same day one time in week  for more friendly', '[]', '', 0, 20, '2021-02-18 21:12:25', '2021-02-18 21:12:25'),
(1342, 1, 8, '[\"What\"]', '[\"What\"]', 'correct', 1, 1, '2021-02-19 04:19:22', '2021-02-19 04:19:22'),
(1343, 2, 8, '[\"They\'re\"]', '[\"They\'re\"]', 'correct', 1, 1, '2021-02-19 04:19:22', '2021-02-19 04:19:22'),
(1344, 3, 8, '[\"It\"]', '[\"It\"]', 'correct', 1, 1, '2021-02-19 04:19:22', '2021-02-19 04:19:22'),
(1345, 5, 8, '[\"do you spell\"]', '[\"do you spell\"]', 'correct', 1, 1, '2021-02-19 04:19:22', '2021-02-19 04:19:22'),
(1346, 6, 8, '[\"a\"]', '[\"--\"]', 'incorrect', 0, 1, '2021-02-19 04:19:22', '2021-02-19 04:19:22'),
(1347, 7, 8, '[\"Is Mr. Mike a teacher?\"]', '[\"Is Mr. Mike a teacher?\"]', 'correct', 1, 1, '2021-02-19 04:19:22', '2021-02-19 04:19:22'),
(1348, 8, 8, '[\"your father\'s job\"]', '[\"your father\'s job\"]', 'correct', 1, 1, '2021-02-19 04:19:22', '2021-02-19 04:19:22'),
(1349, 9, 8, '[\"doesn\'t have\"]', '[\"doesn\'t have\"]', 'correct', 1, 1, '2021-02-19 04:19:22', '2021-02-19 04:19:22'),
(1350, 10, 8, '[\"works\"]', '[\"works\"]', 'correct', 1, 1, '2021-02-19 04:19:22', '2021-02-19 04:19:22'),
(1351, 11, 8, '[\"We do not often go to the cinema.\"]', '[\"We do not often go to the cinema.\"]', 'correct', 1, 1, '2021-02-19 04:19:22', '2021-02-19 04:19:22'),
(1352, 12, 8, '[\"do\"]', '[\"do\"]', 'correct', 1, 1, '2021-02-19 04:19:22', '2021-02-19 04:19:22'),
(1353, 14, 8, '[\"ever\"]', '[\"ever\"]', 'correct', 1, 1, '2021-02-19 04:19:22', '2021-02-19 04:19:22'),
(1354, 18, 8, '[\"tallest\"]', '[\"taller\"]', 'incorrect', 0, 1, '2021-02-19 04:19:22', '2021-02-19 04:19:22'),
(1355, 19, 8, '[\"most\"]', '[\"most\"]', 'correct', 1, 1, '2021-02-19 04:19:22', '2021-02-19 04:19:22'),
(1356, 23, 8, '[\"more friendly\"]', '[\"friendlier\"]', 'incorrect', 0, 1, '2021-02-19 04:19:22', '2021-02-19 04:19:22'),
(1357, 28, 8, '[\"on\"]', '[\"on\"]', 'correct', 1, 1, '2021-02-19 04:19:22', '2021-02-19 04:19:22'),
(1358, 29, 8, '[\"for\"]', '[\"for\"]', 'correct', 1, 1, '2021-02-19 04:19:22', '2021-02-19 04:19:22'),
(1359, 30, 8, 'where does sally\'S grand mother live?', '[]', '', 0, 1, '2021-02-19 04:19:22', '2021-02-19 04:19:22'),
(1360, 31, 8, 'How many children  did tom have ?', '[]', '', 0, 1, '2021-02-19 04:19:22', '2021-02-19 04:19:22'),
(1361, 32, 8, 'what time clock do they get up every morning?', '[]', '', 0, 1, '2021-02-19 04:19:22', '2021-02-19 04:19:22'),
(1362, 33, 8, '[\"I didn\'t go to the cinema\\u00a0 last night.\\u00a0\"]', '[\"I didn\'t go to the cinema\\u00a0 last night.\\u00a0\"]', 'correct', 1, 1, '2021-02-19 04:19:22', '2021-02-19 04:19:22'),
(1363, 34, 8, '[\"They haven\'t become very rich.\"]', '[\"They haven\'t become very rich.\"]', 'correct', 1, 1, '2021-02-19 04:19:22', '2021-02-19 04:19:22'),
(1364, 35, 8, '[\"I don\'t have a big window in my room.\"]', '[\"I don\'t have a big window in my room.\"]', 'correct', 1, 1, '2021-02-19 04:19:22', '2021-02-19 04:19:22'),
(1365, 36, 8, '[\"My mom doesn\'t have perfect cooking skills.\"]', '[\"My mom doesn\'t have perfect cooking skills.\"]', 'correct', 1, 1, '2021-02-19 04:19:22', '2021-02-19 04:19:22'),
(1366, 37, 8, '[\"ill\",\"crowded\",\"difficult\",\"beautiful\",\"quiet\",\"intelligent\",\"rich\",\"friendly\",\"interesting\"]', '[\"ill\",\"quiet\",\"difficult\",\"beautiful\",\"crowded\",\"intelligent\",\"rich\",\"friendly\",\"interesting\"]', '[\"correct\",\"incorrect\",\"correct\",\"correct\",\"incorrect\",\"correct\",\"correct\",\"correct\",\"correct\"]', 0, 9, '2021-02-19 04:19:22', '2021-02-19 04:19:22'),
(1367, 38, 8, '[\"arrived\",\"want\",\"do \\/ go\",\"do not understand\",\"talk\",\"met\",\"came\",\"is doing\",\"needs\",\"is ringing\"]', '[\"arrived\",\"want\",\"have been\",\"don\'t understand\",\"talk\",\"met\",\"came\",\"is doing\",\"needs\",\"is ringing\"]', '[\"correct\",\"correct\",\"incorrect\",\"incorrect\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\"]', 0, 10, '2021-02-19 04:19:22', '2021-02-19 04:19:22'),
(1368, 42, 8, '[\"24\",\"13.20\",\"8.45\",\"11\",\"5.15\",\"8.20\",\"259\"]', '[\"24\",\"13.20\",\"18.45\",\"11\",\"5.15\",\"8.20\",\"259\"]', '[\"correct\",\"correct\",\"incorrect\",\"correct\",\"correct\",\"correct\",\"correct\"]', 0, 7, '2021-02-19 04:19:22', '2021-02-19 04:19:22'),
(1369, 48, 8, '[\"speaking\",\"did she\",\"that would be\",\"have to\",\"Oh, dear\",\"really sorry\",\"don\'t worry\",\"could you help me\",\"shall I\",\"this is\"]', '[\"speaking\",\"this is\",\"really sorry\",\"have to\",\"Oh, dear\",\"did she\",\"don\'t worry\",\"could you help me\",\"shall I\",\"that would be\"]', '[\"correct\",\"incorrect\",\"incorrect\",\"correct\",\"correct\",\"incorrect\",\"correct\",\"correct\",\"correct\",\"incorrect\"]', 0, 10, '2021-02-19 04:19:22', '2021-02-19 04:19:22'),
(1370, 49, 8, '[\"would accept\"]', '[\"would accept\"]', 'correct', 3, 3, '2021-02-19 04:19:22', '2021-02-19 04:19:22'),
(1371, 50, 8, '[\"will pass\"]', '[\"will pass\"]', 'correct', 3, 3, '2021-02-19 04:19:22', '2021-02-19 04:19:22'),
(1372, 51, 8, '[\"seeing\"]', '[\"seeing\"]', 'correct', 3, 3, '2021-02-19 04:19:22', '2021-02-19 04:19:22'),
(1373, 52, 8, '[\"wanted -- the following\"]', '[\"wanted -- the following\"]', 'correct', 3, 3, '2021-02-19 04:19:22', '2021-02-19 04:19:22'),
(1374, 53, 8, '[\"which\"]', '[\"which\"]', 'correct', 3, 3, '2021-02-19 04:19:22', '2021-02-19 04:19:22'),
(1375, 54, 8, '[\"where\"]', '[\"where\"]', 'correct', 3, 3, '2021-02-19 04:19:22', '2021-02-19 04:19:22'),
(1376, 55, 8, '[\"fired\"]', '[\"were fired\"]', 'incorrect', 0, 3, '2021-02-19 04:19:22', '2021-02-19 04:19:22'),
(1377, 56, 8, '[\"give up\"]', '[\"give up\"]', 'correct', 3, 3, '2021-02-19 04:19:22', '2021-02-19 04:19:22'),
(1378, 57, 8, '[\"go out with\"]', '[\"put up with\"]', 'incorrect', 0, 3, '2021-02-19 04:19:22', '2021-02-19 04:19:22'),
(1379, 58, 8, '[\"impolite\"]', '[\"impolite\"]', 'correct', 3, 3, '2021-02-19 04:19:22', '2021-02-19 04:19:22'),
(1380, 59, 8, '[\"niece\"]', '[\"niece\"]', 'correct', 3, 3, '2021-02-19 04:19:22', '2021-02-19 04:19:22'),
(1381, 60, 8, '[\"mother-in-law\"]', '[\"mother-in-law\"]', 'correct', 3, 3, '2021-02-19 04:19:22', '2021-02-19 04:19:22'),
(1382, 61, 8, '[\"17\"]', '[\"17\"]', 'correct', 2, 2, '2021-02-19 04:19:22', '2021-02-19 04:19:22'),
(1383, 62, 8, '[\"20\"]', '[\"20\"]', 'correct', 2, 2, '2021-02-19 04:19:22', '2021-02-19 04:19:22'),
(1384, 63, 8, '[\"$20\"]', '[\"$20\"]', 'correct', 2, 2, '2021-02-19 04:19:22', '2021-02-19 04:19:22'),
(1385, 64, 8, '[\"10\"]', '[\"10\"]', 'correct', 2, 2, '2021-02-19 04:19:22', '2021-02-19 04:19:22'),
(1386, 65, 8, '[\"in the water tank\"]', '[\"on his body\"]', 'incorrect', 0, 2, '2021-02-19 04:19:22', '2021-02-19 04:19:22'),
(1387, 66, 8, '[\"stomach problems\"]', '[\"stomach problems\"]', 'correct', 2, 2, '2021-02-19 04:19:22', '2021-02-19 04:19:22'),
(1388, 67, 8, '[\"Although\"]', '[\"Although\"]', 'correct', 4, 4, '2021-02-19 04:19:22', '2021-02-19 04:19:22'),
(1389, 68, 8, '[\"Despite being tired\"]', '[\"Despite being tired\"]', 'correct', 4, 4, '2021-02-19 04:19:22', '2021-02-19 04:19:22'),
(1390, 69, 8, '[\"am used to working\"]', '[\"am used to working\"]', 'correct', 4, 4, '2021-02-19 04:19:22', '2021-02-19 04:19:22'),
(1391, 71, 8, '[\"had forgotten\"]', '[\"had forgotten\"]', 'correct', 4, 4, '2021-02-19 04:19:22', '2021-02-19 04:19:22'),
(1392, 72, 8, '[\"keep in touch with\"]', '[\"keep in touch with\"]', 'correct', 4, 4, '2021-02-19 04:19:22', '2021-02-19 04:19:22'),
(1393, 73, 8, '[\"split\"]', '[\"split\"]', 'correct', 4, 4, '2021-02-19 04:19:22', '2021-02-19 04:19:22'),
(1394, 74, 8, '[\"didn\'t\"]', '[\"not to\"]', 'incorrect', 0, 4, '2021-02-19 04:19:22', '2021-02-19 04:19:22'),
(1395, 75, 8, '[\"care\",\"dis enjoyable\",\"appointment\"]', '[\"care\",\"enjoyable\",\"disappointment\"]', '[\"correct\",\"incorrect\",\"incorrect\"]', 0, 6, '2021-02-19 04:19:22', '2021-02-19 04:19:22'),
(1396, 76, 8, '[\"in Australia\"]', '[\"at a party\"]', 'incorrect', 0, 4, '2021-02-19 04:19:22', '2021-02-19 04:19:22'),
(1397, 77, 8, '[\"lost her passport\"]', '[\"lost her passport\"]', 'correct', 4, 4, '2021-02-19 04:19:22', '2021-02-19 04:19:22');
INSERT INTO `employee_quizzes_old` (`id`, `quiz_id`, `employee_id`, `emp_answer`, `correct_answer`, `check_answer`, `emp_score`, `score`, `created_at`, `updated_at`) VALUES
(1398, 78, 8, '[\"lost her job\"]', '[\"lost her job\"]', 'correct', 4, 4, '2021-02-19 04:19:22', '2021-02-19 04:19:22'),
(1399, 79, 8, '[\"the country\"]', '[\"the country\"]', 'correct', 4, 4, '2021-02-19 04:19:22', '2021-02-19 04:19:22'),
(1400, 80, 8, '[\"an aggressive\"]', '[\"an aggressive\"]', 'correct', 4, 4, '2021-02-19 04:19:22', '2021-02-19 04:19:22'),
(1401, 81, 8, '[\"some boxing gloves\"]', '[\"a bike\"]', 'incorrect', 0, 4, '2021-02-19 04:19:22', '2021-02-19 04:19:22'),
(1402, 82, 8, '[\"loot\",\"rob\",\"burgle\",\"vandalise\"]', '[\"looting\",\"robbery\",\"burgle\",\"vandalize\"]', '[\"incorrect\",\"incorrect\",\"correct\",\"incorrect\"]', 0, 8, '2021-02-19 04:19:22', '2021-02-19 04:19:22'),
(1403, 83, 8, '[\"politicaly\",\"economics\",\"environmental\",\"pollution\"]', '[\"politician\",\"economic\",\"environmental\",\"pollution\"]', '[\"incorrect\",\"incorrect\",\"correct\",\"correct\"]', 0, 8, '2021-02-19 04:19:22', '2021-02-19 04:19:22'),
(1404, 84, 8, '[\"give someone a job\"]', '[\"fire someone form a job\"]', 'incorrect', 0, 4, '2021-02-19 04:19:22', '2021-02-19 04:19:22'),
(1405, 85, 8, '[\"strong\"]', '[\"a lot of money\"]', 'incorrect', 0, 4, '2021-02-19 04:19:22', '2021-02-19 04:19:22'),
(1406, 86, 8, '[\"have a rough voice\"]', '[\"have a difficulty in speaking\"]', 'incorrect', 0, 4, '2021-02-19 04:19:22', '2021-02-19 04:19:22'),
(1407, 87, 8, '[\"memorable\",\"scary\",\"overrated\",\"predictable\",\"gripping\",\"hilarious\"]', '[\"memorable\",\"scary\",\"overrated\",\"predictable\",\"gripping\",\"hilarious\"]', '[\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\"]', 12, 12, '2021-02-19 04:19:22', '2021-02-19 04:19:22'),
(1408, 90, 8, '[\"attack\",\"sued\",\"spread\",\"crisis\",\"hostage\",\"released\",\"invaded\",\"troops\",\"flee\",\"damages\",\"outcry\"]', '[\"sued\",\"damages\",\"spread\",\"crisis\",\"hostage\",\"released\",\"invaded\",\"troops\",\"flee\",\"attack\",\"outcry\"]', '[\"incorrect\",\"incorrect\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"incorrect\",\"correct\"]', 0, 30, '2021-02-19 04:19:22', '2021-02-19 04:19:22'),
(1409, 91, 8, '[\"True\"]', '[\"False\"]', 'incorrect', 0, 5, '2021-02-19 04:19:22', '2021-02-19 04:19:22'),
(1410, 92, 8, '[\"True\"]', '[\"True\"]', 'correct', 5, 5, '2021-02-19 04:19:22', '2021-02-19 04:19:22'),
(1411, 93, 8, '[\"False\"]', '[\"False\"]', 'correct', 5, 5, '2021-02-19 04:19:22', '2021-02-19 04:19:22'),
(1412, 94, 8, '[\"True\"]', '[\"True\"]', 'correct', 5, 5, '2021-02-19 04:19:22', '2021-02-19 04:19:22'),
(1413, 95, 8, '[\"three times\"]', '[\"three times\"]', 'correct', 5, 5, '2021-02-19 04:19:22', '2021-02-19 04:19:22'),
(1414, 96, 8, 'Dear my Manager,\r\nI am  proud be a member  of  our company ,we must respect  our company\'s  policy  and working condition which keep Employees rights as medical insurance , cooperation between workers and development of personal skills and having respect  between all employees .\r\n\r\nThanks \r\nAhmed Abd el Fattah', '[]', '', 0, 20, '2021-02-19 04:19:22', '2021-02-19 04:19:22'),
(1415, 1, 7, '[\"What\"]', '[\"What\"]', 'correct', 1, 1, '2021-02-19 14:44:55', '2021-02-19 14:44:55'),
(1416, 2, 7, '[\"They\'re\"]', '[\"They\'re\"]', 'correct', 1, 1, '2021-02-19 14:44:55', '2021-02-19 14:44:55'),
(1417, 3, 7, '[\"It\"]', '[\"It\"]', 'correct', 1, 1, '2021-02-19 14:44:55', '2021-02-19 14:44:55'),
(1418, 5, 7, '[\"do you spell\"]', '[\"do you spell\"]', 'correct', 1, 1, '2021-02-19 14:44:55', '2021-02-19 14:44:55'),
(1419, 6, 7, '[\"a\"]', '[\"--\"]', 'incorrect', 0, 1, '2021-02-19 14:44:55', '2021-02-19 14:44:55'),
(1420, 7, 7, '[\"Is Mr. Mike a teacher?\"]', '[\"Is Mr. Mike a teacher?\"]', 'correct', 1, 1, '2021-02-19 14:44:55', '2021-02-19 14:44:55'),
(1421, 8, 7, '[\"your father\'s job\"]', '[\"your father\'s job\"]', 'correct', 1, 1, '2021-02-19 14:44:55', '2021-02-19 14:44:55'),
(1422, 9, 7, '[\"doesn\'t has\"]', '[\"doesn\'t have\"]', 'incorrect', 0, 1, '2021-02-19 14:44:55', '2021-02-19 14:44:55'),
(1423, 10, 7, '[\"working\"]', '[\"works\"]', 'incorrect', 0, 1, '2021-02-19 14:44:55', '2021-02-19 14:44:55'),
(1424, 11, 7, '[\"We often do not go to the cinema.\"]', '[\"We do not often go to the cinema.\"]', 'incorrect', 0, 1, '2021-02-19 14:44:55', '2021-02-19 14:44:55'),
(1425, 12, 7, '[\"do\"]', '[\"do\"]', 'correct', 1, 1, '2021-02-19 14:44:55', '2021-02-19 14:44:55'),
(1426, 14, 7, '[\"never\"]', '[\"ever\"]', 'incorrect', 0, 1, '2021-02-19 14:44:55', '2021-02-19 14:44:55'),
(1427, 18, 7, '[\"tallest\"]', '[\"taller\"]', 'incorrect', 0, 1, '2021-02-19 14:44:55', '2021-02-19 14:44:55'),
(1428, 19, 7, '[\"more\"]', '[\"most\"]', 'incorrect', 0, 1, '2021-02-19 14:44:55', '2021-02-19 14:44:55'),
(1429, 23, 7, '[\"friendlier\"]', '[\"friendlier\"]', 'correct', 1, 1, '2021-02-19 14:44:55', '2021-02-19 14:44:55'),
(1430, 28, 7, '[\"on\"]', '[\"on\"]', 'correct', 1, 1, '2021-02-19 14:44:55', '2021-02-19 14:44:55'),
(1431, 29, 7, '[\"for\"]', '[\"for\"]', 'correct', 1, 1, '2021-02-19 14:44:55', '2021-02-19 14:44:55'),
(1432, 30, 7, 'where Sally\'s grandmother lives?', '[]', '', 0, 1, '2021-02-19 14:44:55', '2021-02-19 14:44:55'),
(1433, 31, 7, 'How many children\'s do you had?', '[]', '', 0, 1, '2021-02-19 14:44:55', '2021-02-19 14:44:55'),
(1434, 32, 7, 'what is the time of your get up every day?', '[]', '', 0, 1, '2021-02-19 14:44:55', '2021-02-19 14:44:55'),
(1435, 33, 7, '[\"I didn\'t go to the cinema\\u00a0 last night.\\u00a0\"]', '[\"I didn\'t go to the cinema\\u00a0 last night.\\u00a0\"]', 'correct', 1, 1, '2021-02-19 14:44:55', '2021-02-19 14:44:55'),
(1436, 34, 7, '[\"They have become not very rich.\"]', '[\"They haven\'t become very rich.\"]', 'incorrect', 0, 1, '2021-02-19 14:44:56', '2021-02-19 14:44:56'),
(1437, 35, 7, '[\"I don\'t have a big window in my room.\"]', '[\"I don\'t have a big window in my room.\"]', 'correct', 1, 1, '2021-02-19 14:44:56', '2021-02-19 14:44:56'),
(1438, 36, 7, '[\"My mom doesn\'t  has perfect cooking skills.\"]', '[\"My mom doesn\'t have perfect cooking skills.\"]', 'incorrect', 0, 1, '2021-02-19 14:44:56', '2021-02-19 14:44:56'),
(1439, 37, 7, '[\"ill\",\"quiet\",\"difficult\",\"beautiful\",\"crowded\",\"intelligent\",\"rich\",\"friendly\",\"interesting\"]', '[\"ill\",\"quiet\",\"difficult\",\"beautiful\",\"crowded\",\"intelligent\",\"rich\",\"friendly\",\"interesting\"]', '[\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\"]', 9, 9, '2021-02-19 14:44:56', '2021-02-19 14:44:56'),
(1440, 38, 7, '[\"was arrived\",\"wants\",\"why do you ever went to London?\",\"did not understand\",\"took\",\"met\",\"came\",\"did\",\"needs\",\"rings\"]', '[\"arrived\",\"want\",\"have been\",\"don\'t understand\",\"talk\",\"met\",\"came\",\"is doing\",\"needs\",\"is ringing\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"correct\",\"correct\",\"incorrect\",\"correct\",\"incorrect\"]', 0, 10, '2021-02-19 14:44:56', '2021-02-19 14:44:56'),
(1441, 42, 7, '[\"24 Feb\",\"13:20\",\"19:45\",\"11 March\",\"05:15\",\"08:20\",\"258\"]', '[\"24\",\"13.20\",\"18.45\",\"11\",\"5.15\",\"8.20\",\"259\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 7, '2021-02-19 14:44:56', '2021-02-19 14:44:56'),
(1442, 48, 7, '[\"speaking\",\"this is dear\",\"really sorry\",\"have to\",\"did she\",\"that would be\",\"don\'t worry\",\"could you help me\",\"shall I\",\"Oh\"]', '[\"speaking\",\"this is\",\"really sorry\",\"have to\",\"Oh, dear\",\"did she\",\"don\'t worry\",\"could you help me\",\"shall I\",\"that would be\"]', '[\"correct\",\"incorrect\",\"correct\",\"correct\",\"incorrect\",\"incorrect\",\"correct\",\"correct\",\"correct\",\"incorrect\"]', 0, 10, '2021-02-19 14:44:56', '2021-02-19 14:44:56'),
(1443, 49, 7, '[\"would accept\"]', '[\"would accept\"]', 'correct', 3, 3, '2021-02-19 14:44:56', '2021-02-19 14:44:56'),
(1444, 50, 7, '[\"will pass\"]', '[\"will pass\"]', 'correct', 3, 3, '2021-02-19 14:44:56', '2021-02-19 14:44:56'),
(1445, 51, 7, '[\"seeing\"]', '[\"seeing\"]', 'correct', 3, 3, '2021-02-19 14:44:56', '2021-02-19 14:44:56'),
(1446, 52, 7, '[\"wants -- next\"]', '[\"wanted -- the following\"]', 'incorrect', 0, 3, '2021-02-19 14:44:56', '2021-02-19 14:44:56'),
(1447, 53, 7, '[\"which\"]', '[\"which\"]', 'correct', 3, 3, '2021-02-19 14:44:56', '2021-02-19 14:44:56'),
(1448, 54, 7, '[\"which\"]', '[\"where\"]', 'incorrect', 0, 3, '2021-02-19 14:44:56', '2021-02-19 14:44:56'),
(1449, 55, 7, '[\"have been fired\"]', '[\"were fired\"]', 'incorrect', 0, 3, '2021-02-19 14:44:56', '2021-02-19 14:44:56'),
(1450, 56, 7, '[\"give up\"]', '[\"give up\"]', 'correct', 3, 3, '2021-02-19 14:44:56', '2021-02-19 14:44:56'),
(1451, 57, 7, '[\"put up with\"]', '[\"put up with\"]', 'correct', 3, 3, '2021-02-19 14:44:56', '2021-02-19 14:44:56'),
(1452, 58, 7, '[\"impolite\"]', '[\"impolite\"]', 'correct', 3, 3, '2021-02-19 14:44:56', '2021-02-19 14:44:56'),
(1453, 59, 7, '[\"nephew\"]', '[\"niece\"]', 'incorrect', 0, 3, '2021-02-19 14:44:56', '2021-02-19 14:44:56'),
(1454, 60, 7, '[\"mother-in-law\"]', '[\"mother-in-law\"]', 'correct', 3, 3, '2021-02-19 14:44:56', '2021-02-19 14:44:56'),
(1455, 61, 7, '[\"17\"]', '[\"17\"]', 'correct', 2, 2, '2021-02-19 14:44:56', '2021-02-19 14:44:56'),
(1456, 62, 7, '[\"12\"]', '[\"20\"]', 'incorrect', 0, 2, '2021-02-19 14:44:56', '2021-02-19 14:44:56'),
(1457, 63, 7, '[\"$20\"]', '[\"$20\"]', 'correct', 2, 2, '2021-02-19 14:44:56', '2021-02-19 14:44:56'),
(1458, 64, 7, '[\"10\"]', '[\"10\"]', 'correct', 2, 2, '2021-02-19 14:44:56', '2021-02-19 14:44:56'),
(1459, 65, 7, '[\"in the water tank\"]', '[\"on his body\"]', 'incorrect', 0, 2, '2021-02-19 14:44:56', '2021-02-19 14:44:56'),
(1460, 66, 7, '[\"stomach problems\"]', '[\"stomach problems\"]', 'correct', 2, 2, '2021-02-19 14:44:56', '2021-02-19 14:44:56'),
(1461, 67, 7, '[\"Although\"]', '[\"Although\"]', 'correct', 4, 4, '2021-02-19 14:44:56', '2021-02-19 14:44:56'),
(1462, 68, 7, '[\"Despite being tired\"]', '[\"Despite being tired\"]', 'correct', 4, 4, '2021-02-19 14:44:56', '2021-02-19 14:44:56'),
(1463, 69, 7, '[\"am used to working\"]', '[\"am used to working\"]', 'correct', 4, 4, '2021-02-19 14:44:56', '2021-02-19 14:44:56'),
(1464, 71, 7, '[\"forgot\"]', '[\"had forgotten\"]', 'incorrect', 0, 4, '2021-02-19 14:44:56', '2021-02-19 14:44:56'),
(1465, 72, 7, '[\"keep in touch with\"]', '[\"keep in touch with\"]', 'correct', 4, 4, '2021-02-19 14:44:56', '2021-02-19 14:44:56'),
(1466, 73, 7, '[\"splitted\"]', '[\"split\"]', 'incorrect', 0, 4, '2021-02-19 14:44:56', '2021-02-19 14:44:56'),
(1467, 74, 7, '[\"don\'t\"]', '[\"not to\"]', 'incorrect', 0, 4, '2021-02-19 14:44:56', '2021-02-19 14:44:56'),
(1468, 75, 7, '[\"care\",\"enjoyable\",\"disappointment\"]', '[\"care\",\"enjoyable\",\"disappointment\"]', '[\"correct\",\"correct\",\"correct\"]', 6, 6, '2021-02-19 14:44:56', '2021-02-19 14:44:56'),
(1469, 76, 7, '[\"at a party\"]', '[\"at a party\"]', 'correct', 4, 4, '2021-02-19 14:44:56', '2021-02-19 14:44:56'),
(1470, 77, 7, '[\"lost her passport\"]', '[\"lost her passport\"]', 'correct', 4, 4, '2021-02-19 14:44:56', '2021-02-19 14:44:56'),
(1471, 78, 7, '[\"got into financial trouble\"]', '[\"lost her job\"]', 'incorrect', 0, 4, '2021-02-19 14:44:56', '2021-02-19 14:44:56'),
(1472, 79, 7, '[\"the country\"]', '[\"the country\"]', 'correct', 4, 4, '2021-02-19 14:44:56', '2021-02-19 14:44:56'),
(1473, 80, 7, '[\"an aggressive\"]', '[\"an aggressive\"]', 'correct', 4, 4, '2021-02-19 14:44:56', '2021-02-19 14:44:56'),
(1474, 81, 7, '[\"some boxing gloves\"]', '[\"a bike\"]', 'incorrect', 0, 4, '2021-02-19 14:44:56', '2021-02-19 14:44:56'),
(1475, 82, 7, '[\"looter\",\"robber\",\"Burgle\",\"vandalize\"]', '[\"looting\",\"robbery\",\"burgle\",\"vandalize\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"correct\"]', 0, 8, '2021-02-19 14:44:56', '2021-02-19 14:44:56'),
(1476, 83, 7, '[\"policy\",\"economical\",\"environmental\",\"polluting\"]', '[\"politician\",\"economic\",\"environmental\",\"pollution\"]', '[\"incorrect\",\"incorrect\",\"correct\",\"incorrect\"]', 0, 8, '2021-02-19 14:44:56', '2021-02-19 14:44:56'),
(1477, 84, 7, '[\"fire someone form a job\"]', '[\"fire someone form a job\"]', 'correct', 4, 4, '2021-02-19 14:44:56', '2021-02-19 14:44:56'),
(1478, 85, 7, '[\"a lot of money\"]', '[\"a lot of money\"]', 'correct', 4, 4, '2021-02-19 14:44:56', '2021-02-19 14:44:56'),
(1479, 86, 7, '[\"have a difficulty in speaking\"]', '[\"have a difficulty in speaking\"]', 'correct', 4, 4, '2021-02-19 14:44:56', '2021-02-19 14:44:56'),
(1480, 87, 7, '[\"predictable\",\"memorable\",\"overrated\",\"scary\",\"gripping\",\"hilarious\"]', '[\"memorable\",\"scary\",\"overrated\",\"predictable\",\"gripping\",\"hilarious\"]', '[\"incorrect\",\"incorrect\",\"correct\",\"incorrect\",\"correct\",\"correct\"]', 0, 12, '2021-02-19 14:44:56', '2021-02-19 14:44:56'),
(1481, 90, 7, '[\"invaded\",\"sued\",\"spread\",\"damages\",\"hostage\",\"attack\",\"released\",\"troops\",\"flee\",\"sued\",\"outcry\"]', '[\"sued\",\"damages\",\"spread\",\"crisis\",\"hostage\",\"released\",\"invaded\",\"troops\",\"flee\",\"attack\",\"outcry\"]', '[\"incorrect\",\"incorrect\",\"correct\",\"incorrect\",\"correct\",\"incorrect\",\"incorrect\",\"correct\",\"correct\",\"incorrect\",\"correct\"]', 0, 30, '2021-02-19 14:44:56', '2021-02-19 14:44:56'),
(1482, 91, 7, '[\"False\"]', '[\"False\"]', 'correct', 5, 5, '2021-02-19 14:44:56', '2021-02-19 14:44:56'),
(1483, 92, 7, '[\"True\"]', '[\"True\"]', 'correct', 5, 5, '2021-02-19 14:44:56', '2021-02-19 14:44:56'),
(1484, 93, 7, '[\"False\"]', '[\"False\"]', 'correct', 5, 5, '2021-02-19 14:44:56', '2021-02-19 14:44:56'),
(1485, 94, 7, '[\"True\"]', '[\"True\"]', 'correct', 5, 5, '2021-02-19 14:44:56', '2021-02-19 14:44:56'),
(1486, 95, 7, '[\"three times\"]', '[\"three times\"]', 'correct', 5, 5, '2021-02-19 14:44:56', '2021-02-19 14:44:56'),
(1487, 96, 7, 'Dear Mr. Aly\r\nI am happy to be one of the team of organization and I would you like to discuss one issue with you about our company\'s policy which is over time for extra job some employs ask me to talk about it and I need to discus this point to make our employs working happy and get more benefit from extra  work to achieve our goals before ending year and finally I wish more success for our company', '[]', '', 0, 20, '2021-02-19 14:44:56', '2021-02-19 14:44:56'),
(1488, 1, 5, '[\"What\"]', '[\"What\"]', 'correct', 1, 1, '2021-02-20 03:03:03', '2021-02-20 03:03:03'),
(1489, 2, 5, '[\"They\'re\"]', '[\"They\'re\"]', 'correct', 1, 1, '2021-02-20 03:03:03', '2021-02-20 03:03:03'),
(1490, 3, 5, '[\"It\"]', '[\"It\"]', 'correct', 1, 1, '2021-02-20 03:03:03', '2021-02-20 03:03:03'),
(1491, 5, 5, '[\"do you spell\"]', '[\"do you spell\"]', 'correct', 1, 1, '2021-02-20 03:03:03', '2021-02-20 03:03:03'),
(1492, 6, 5, '[\"a\"]', '[\"--\"]', 'incorrect', 0, 1, '2021-02-20 03:03:03', '2021-02-20 03:03:03'),
(1493, 7, 5, '[\"Is Mr. Mike a teacher?\"]', '[\"Is Mr. Mike a teacher?\"]', 'correct', 1, 1, '2021-02-20 03:03:03', '2021-02-20 03:03:03'),
(1494, 8, 5, '[\"your father\'s job\"]', '[\"your father\'s job\"]', 'correct', 1, 1, '2021-02-20 03:03:03', '2021-02-20 03:03:03'),
(1495, 9, 5, '[\"doesn\'t have\"]', '[\"doesn\'t have\"]', 'correct', 1, 1, '2021-02-20 03:03:03', '2021-02-20 03:03:03'),
(1496, 10, 5, '[\"works\"]', '[\"works\"]', 'correct', 1, 1, '2021-02-20 03:03:03', '2021-02-20 03:03:03'),
(1497, 11, 5, '[\"We do not go often to the cinema.\"]', '[\"We do not often go to the cinema.\"]', 'incorrect', 0, 1, '2021-02-20 03:03:03', '2021-02-20 03:03:03'),
(1498, 12, 5, '[\"make\"]', '[\"do\"]', 'incorrect', 0, 1, '2021-02-20 03:03:03', '2021-02-20 03:03:03'),
(1499, 14, 5, '[\"sometimes\"]', '[\"ever\"]', 'incorrect', 0, 1, '2021-02-20 03:03:03', '2021-02-20 03:03:03'),
(1500, 18, 5, '[\"taller\"]', '[\"taller\"]', 'correct', 1, 1, '2021-02-20 03:03:03', '2021-02-20 03:03:03'),
(1501, 19, 5, '[\"most\"]', '[\"most\"]', 'correct', 1, 1, '2021-02-20 03:03:03', '2021-02-20 03:03:03'),
(1502, 23, 5, '[\"more friendly\"]', '[\"friendlier\"]', 'incorrect', 0, 1, '2021-02-20 03:03:03', '2021-02-20 03:03:03'),
(1503, 28, 5, '[\"on\"]', '[\"on\"]', 'correct', 1, 1, '2021-02-20 03:03:03', '2021-02-20 03:03:03'),
(1504, 29, 5, '[\"for\"]', '[\"for\"]', 'correct', 1, 1, '2021-02-20 03:03:03', '2021-02-20 03:03:03'),
(1505, 30, 5, 'where is sally,s grand mother live?', '[]', '', 0, 1, '2021-02-20 03:03:03', '2021-02-20 03:03:03'),
(1506, 31, 5, 'how many childrens did tom have?', '[]', '', 0, 1, '2021-02-20 03:03:03', '2021-02-20 03:03:03'),
(1507, 32, 5, 'which clock they get up every morning?', '[]', '', 0, 1, '2021-02-20 03:03:03', '2021-02-20 03:03:03'),
(1508, 33, 5, '[\"I didn\'t go to the cinema\\u00a0 last night.\\u00a0\"]', '[\"I didn\'t go to the cinema\\u00a0 last night.\\u00a0\"]', 'correct', 1, 1, '2021-02-20 03:03:03', '2021-02-20 03:03:03'),
(1509, 34, 5, '[\"They haven\'t become very rich.\"]', '[\"They haven\'t become very rich.\"]', 'correct', 1, 1, '2021-02-20 03:03:03', '2021-02-20 03:03:03'),
(1510, 35, 5, '[\"I don\'t have a big window in my room.\"]', '[\"I don\'t have a big window in my room.\"]', 'correct', 1, 1, '2021-02-20 03:03:03', '2021-02-20 03:03:03'),
(1511, 36, 5, '[\"My mom doesn\'t have perfect cooking skills.\"]', '[\"My mom doesn\'t have perfect cooking skills.\"]', 'correct', 1, 1, '2021-02-20 03:03:03', '2021-02-20 03:03:03'),
(1512, 37, 5, '[\"ill\",\"quiet\",\"difficult\",\"beautiful\",\"crowded\",\"intelligent\",\"rich\",\"friendly\",\"interestinga\"]', '[\"ill\",\"quiet\",\"difficult\",\"beautiful\",\"crowded\",\"intelligent\",\"rich\",\"friendly\",\"interesting\"]', '[\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"incorrect\"]', 0, 9, '2021-02-20 03:03:03', '2021-02-20 03:03:03'),
(1513, 38, 5, '[\"arrived\",\"wanted\",\"had  you ever went\",\"don,t understand\",\"are talking\",\"met\",\"came\",\"is doing\",\"need\",\"is ringing\"]', '[\"arrived\",\"want\",\"have been\",\"don\'t understand\",\"talk\",\"met\",\"came\",\"is doing\",\"needs\",\"is ringing\"]', '[\"correct\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"correct\",\"correct\",\"correct\",\"incorrect\",\"correct\"]', 0, 10, '2021-02-20 03:03:03', '2021-02-20 03:03:03'),
(1514, 42, 5, '[\"24\",\"13:20\",\"18:45\",\"11\",\"5:15\",\"8:20\",\"259\"]', '[\"24\",\"13.20\",\"18.45\",\"11\",\"5.15\",\"8.20\",\"259\"]', '[\"correct\",\"incorrect\",\"incorrect\",\"correct\",\"incorrect\",\"incorrect\",\"correct\"]', 0, 7, '2021-02-20 03:03:03', '2021-02-20 03:03:03'),
(1515, 48, 5, '[\"this is\",\"that would be\",\"speaking\",\"have to\",\"oh dear\",\"did she\",\"dont worry\",\"could you help me\",\"shall i\",\"this is\"]', '[\"speaking\",\"this is\",\"really sorry\",\"have to\",\"Oh, dear\",\"did she\",\"don\'t worry\",\"could you help me\",\"shall I\",\"that would be\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"correct\",\"incorrect\",\"correct\",\"incorrect\",\"correct\",\"incorrect\",\"incorrect\"]', 0, 10, '2021-02-20 03:03:03', '2021-02-20 03:03:03'),
(1516, 49, 5, '[\"would accept\"]', '[\"would accept\"]', 'correct', 3, 3, '2021-02-20 03:03:03', '2021-02-20 03:03:03'),
(1517, 50, 5, '[\"will pass\"]', '[\"will pass\"]', 'correct', 3, 3, '2021-02-20 03:03:03', '2021-02-20 03:03:03'),
(1518, 51, 5, '[\"see\"]', '[\"seeing\"]', 'incorrect', 0, 3, '2021-02-20 03:03:03', '2021-02-20 03:03:03'),
(1519, 52, 5, '[\"want -- next\"]', '[\"wanted -- the following\"]', 'incorrect', 0, 3, '2021-02-20 03:03:03', '2021-02-20 03:03:03'),
(1520, 53, 5, '[\"which\"]', '[\"which\"]', 'correct', 3, 3, '2021-02-20 03:03:03', '2021-02-20 03:03:03'),
(1521, 54, 5, '[\"where\"]', '[\"where\"]', 'correct', 3, 3, '2021-02-20 03:03:03', '2021-02-20 03:03:03'),
(1522, 55, 5, '[\"were fired\"]', '[\"were fired\"]', 'correct', 3, 3, '2021-02-20 03:03:03', '2021-02-20 03:03:03'),
(1523, 56, 5, '[\"give up\"]', '[\"give up\"]', 'correct', 3, 3, '2021-02-20 03:03:03', '2021-02-20 03:03:03'),
(1524, 57, 5, '[\"put up with\"]', '[\"put up with\"]', 'correct', 3, 3, '2021-02-20 03:03:03', '2021-02-20 03:03:03'),
(1525, 58, 5, '[\"impolite\"]', '[\"impolite\"]', 'correct', 3, 3, '2021-02-20 03:03:03', '2021-02-20 03:03:03'),
(1526, 59, 5, '[\"niece\"]', '[\"niece\"]', 'correct', 3, 3, '2021-02-20 03:03:03', '2021-02-20 03:03:03'),
(1527, 60, 5, '[\"mother-in-law\"]', '[\"mother-in-law\"]', 'correct', 3, 3, '2021-02-20 03:03:04', '2021-02-20 03:03:04'),
(1528, 61, 5, '[\"17\"]', '[\"17\"]', 'correct', 2, 2, '2021-02-20 03:03:04', '2021-02-20 03:03:04'),
(1529, 62, 5, '[\"20\"]', '[\"20\"]', 'correct', 2, 2, '2021-02-20 03:03:04', '2021-02-20 03:03:04'),
(1530, 63, 5, '[\"$20\"]', '[\"$20\"]', 'correct', 2, 2, '2021-02-20 03:03:04', '2021-02-20 03:03:04'),
(1531, 64, 5, '[\"10\"]', '[\"10\"]', 'correct', 2, 2, '2021-02-20 03:03:04', '2021-02-20 03:03:04'),
(1532, 66, 5, '[\"stomach problems\"]', '[\"stomach problems\"]', 'correct', 2, 2, '2021-02-20 03:03:04', '2021-02-20 03:03:04'),
(1533, 67, 5, '[\"However\"]', '[\"Although\"]', 'incorrect', 0, 4, '2021-02-20 03:03:04', '2021-02-20 03:03:04'),
(1534, 68, 5, '[\"Despite being tired\"]', '[\"Despite being tired\"]', 'correct', 4, 4, '2021-02-20 03:03:04', '2021-02-20 03:03:04'),
(1535, 69, 5, '[\"used to work\"]', '[\"am used to working\"]', 'incorrect', 0, 4, '2021-02-20 03:03:04', '2021-02-20 03:03:04'),
(1536, 71, 5, '[\"forgot\"]', '[\"had forgotten\"]', 'incorrect', 0, 4, '2021-02-20 03:03:04', '2021-02-20 03:03:04'),
(1537, 72, 5, '[\"keep in touch with\"]', '[\"keep in touch with\"]', 'correct', 4, 4, '2021-02-20 03:03:04', '2021-02-20 03:03:04'),
(1538, 73, 5, '[\"have splitted\"]', '[\"split\"]', 'incorrect', 0, 4, '2021-02-20 03:03:04', '2021-02-20 03:03:04'),
(1539, 74, 5, '[\"not to\"]', '[\"not to\"]', 'correct', 4, 4, '2021-02-20 03:03:04', '2021-02-20 03:03:04'),
(1540, 75, 5, '[\"caring\",\"enjoyable\",\"disappoitig\"]', '[\"care\",\"enjoyable\",\"disappointment\"]', '[\"incorrect\",\"correct\",\"incorrect\"]', 0, 6, '2021-02-20 03:03:04', '2021-02-20 03:03:04'),
(1541, 76, 5, '[\"at a party\"]', '[\"at a party\"]', 'correct', 4, 4, '2021-02-20 03:03:04', '2021-02-20 03:03:04'),
(1542, 77, 5, '[\"lost her passport\"]', '[\"lost her passport\"]', 'correct', 4, 4, '2021-02-20 03:03:04', '2021-02-20 03:03:04'),
(1543, 78, 5, '[\"got into financial trouble\"]', '[\"lost her job\"]', 'incorrect', 0, 4, '2021-02-20 03:03:04', '2021-02-20 03:03:04'),
(1544, 79, 5, '[\"the country\"]', '[\"the country\"]', 'correct', 4, 4, '2021-02-20 03:03:04', '2021-02-20 03:03:04'),
(1545, 80, 5, '[\"an aggressive\"]', '[\"an aggressive\"]', 'correct', 4, 4, '2021-02-20 03:03:04', '2021-02-20 03:03:04'),
(1546, 81, 5, '[\"a bike\"]', '[\"a bike\"]', 'correct', 4, 4, '2021-02-20 03:03:04', '2021-02-20 03:03:04'),
(1547, 82, 5, '[\"loot\",\"rober\",\"burg\",\"vand\"]', '[\"looting\",\"robbery\",\"burgle\",\"vandalize\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 8, '2021-02-20 03:03:04', '2021-02-20 03:03:04'),
(1548, 83, 5, '[\"politican\",\"econimical\",\"enviromental\",\"pollution\"]', '[\"politician\",\"economic\",\"environmental\",\"pollution\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"correct\"]', 0, 8, '2021-02-20 03:03:04', '2021-02-20 03:03:04'),
(1549, 84, 5, '[\"give someone a job\"]', '[\"fire someone form a job\"]', 'incorrect', 0, 4, '2021-02-20 03:03:04', '2021-02-20 03:03:04'),
(1550, 85, 5, '[\"strong\"]', '[\"a lot of money\"]', 'incorrect', 0, 4, '2021-02-20 03:03:04', '2021-02-20 03:03:04'),
(1551, 86, 5, '[\"have a difficulty in speaking\"]', '[\"have a difficulty in speaking\"]', 'correct', 4, 4, '2021-02-20 03:03:04', '2021-02-20 03:03:04'),
(1552, 87, 5, '[\"predictable\",\"scary\",\"hilarious\",\"overrated\",\"memorable\",\"gripping\"]', '[\"memorable\",\"scary\",\"overrated\",\"predictable\",\"gripping\",\"hilarious\"]', '[\"incorrect\",\"correct\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 12, '2021-02-20 03:03:04', '2021-02-20 03:03:04'),
(1553, 90, 5, '[\"invaded\",\"outcry\",\"spread\",\"crisis\",\"damage\",\"realesed\",\"sued\",null,\"flee\",null,null]', '[\"sued\",\"damages\",\"spread\",\"crisis\",\"hostage\",\"released\",\"invaded\",\"troops\",\"flee\",\"attack\",\"outcry\"]', '[\"incorrect\",\"incorrect\",\"correct\",\"correct\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"correct\",\"incorrect\",\"incorrect\"]', 0, 30, '2021-02-20 03:03:04', '2021-02-20 03:03:04'),
(1554, 91, 5, '[\"True\"]', '[\"False\"]', 'incorrect', 0, 5, '2021-02-20 03:03:04', '2021-02-20 03:03:04'),
(1555, 92, 5, '[\"False\"]', '[\"True\"]', 'incorrect', 0, 5, '2021-02-20 03:03:04', '2021-02-20 03:03:04'),
(1556, 93, 5, '[\"True\"]', '[\"False\"]', 'incorrect', 0, 5, '2021-02-20 03:03:04', '2021-02-20 03:03:04'),
(1557, 94, 5, '[\"True\"]', '[\"True\"]', 'correct', 5, 5, '2021-02-20 03:03:04', '2021-02-20 03:03:04'),
(1558, 95, 5, '[\"three times\"]', '[\"three times\"]', 'correct', 5, 5, '2021-02-20 03:03:04', '2021-02-20 03:03:04'),
(1559, 96, 5, 'Dear:\r\nkindly i send you my opinion about the company policy it was great  company and the working condition are healthy, i have some advice to how improve the company as spread the spirit of teamwork between the employs and make a strategy for increasing the profit of the company.\r\nAhmed Essa', '[]', '', 0, 20, '2021-02-20 03:03:04', '2021-02-20 03:03:04'),
(1560, 1, 19, '[\"What\"]', '[\"What\"]', 'correct', 1, 1, '2021-02-20 18:46:57', '2021-02-20 18:46:57'),
(1561, 2, 19, '[\"They\'re\"]', '[\"They\'re\"]', 'correct', 1, 1, '2021-02-20 18:46:57', '2021-02-20 18:46:57'),
(1562, 3, 19, '[\"It\"]', '[\"It\"]', 'correct', 1, 1, '2021-02-20 18:46:57', '2021-02-20 18:46:57'),
(1563, 5, 19, '[\"do you spell\"]', '[\"do you spell\"]', 'correct', 1, 1, '2021-02-20 18:46:57', '2021-02-20 18:46:57'),
(1564, 6, 19, '[\"one\"]', '[\"--\"]', 'incorrect', 0, 1, '2021-02-20 18:46:57', '2021-02-20 18:46:57'),
(1565, 7, 19, '[\"Is Mr. Mike a teacher?\"]', '[\"Is Mr. Mike a teacher?\"]', 'correct', 1, 1, '2021-02-20 18:46:57', '2021-02-20 18:46:57'),
(1566, 8, 19, '[\"your father\'s job\"]', '[\"your father\'s job\"]', 'correct', 1, 1, '2021-02-20 18:46:57', '2021-02-20 18:46:57'),
(1567, 9, 19, '[\"don\'t have\"]', '[\"doesn\'t have\"]', 'incorrect', 0, 1, '2021-02-20 18:46:57', '2021-02-20 18:46:57'),
(1568, 10, 19, '[\"works\"]', '[\"works\"]', 'correct', 1, 1, '2021-02-20 18:46:57', '2021-02-20 18:46:57'),
(1569, 11, 19, '[\"We often do not go to the cinema.\"]', '[\"We do not often go to the cinema.\"]', 'incorrect', 0, 1, '2021-02-20 18:46:57', '2021-02-20 18:46:57'),
(1570, 12, 19, '[\"do\"]', '[\"do\"]', 'correct', 1, 1, '2021-02-20 18:46:57', '2021-02-20 18:46:57'),
(1571, 14, 19, '[\"ever\"]', '[\"ever\"]', 'correct', 1, 1, '2021-02-20 18:46:57', '2021-02-20 18:46:57'),
(1572, 18, 19, '[\"taller\"]', '[\"taller\"]', 'correct', 1, 1, '2021-02-20 18:46:57', '2021-02-20 18:46:57'),
(1573, 19, 19, '[\"most\"]', '[\"most\"]', 'correct', 1, 1, '2021-02-20 18:46:57', '2021-02-20 18:46:57'),
(1574, 23, 19, '[\"more friendly\"]', '[\"friendlier\"]', 'incorrect', 0, 1, '2021-02-20 18:46:57', '2021-02-20 18:46:57'),
(1575, 28, 19, '[\"on\"]', '[\"on\"]', 'correct', 1, 1, '2021-02-20 18:46:57', '2021-02-20 18:46:57'),
(1576, 29, 19, '[\"for\"]', '[\"for\"]', 'correct', 1, 1, '2021-02-20 18:46:57', '2021-02-20 18:46:57'),
(1577, 30, 19, 'where is your grandmother lives sally ?', '[]', '', 0, 1, '2021-02-20 18:46:57', '2021-02-20 18:46:57'),
(1578, 31, 19, 'how many children tom have ?', '[]', '', 0, 1, '2021-02-20 18:46:57', '2021-02-20 18:46:57'),
(1579, 32, 19, 'when do they get up every morning ?', '[]', '', 0, 1, '2021-02-20 18:46:57', '2021-02-20 18:46:57'),
(1580, 33, 19, '[\"I didn\'t go to the cinema\\u00a0 last night.\\u00a0\"]', '[\"I didn\'t go to the cinema\\u00a0 last night.\\u00a0\"]', 'correct', 1, 1, '2021-02-20 18:46:57', '2021-02-20 18:46:57'),
(1581, 34, 19, '[\"They haven\'t become very rich.\"]', '[\"They haven\'t become very rich.\"]', 'correct', 1, 1, '2021-02-20 18:46:57', '2021-02-20 18:46:57'),
(1582, 35, 19, '[\"I don\'t have a big window in my room.\"]', '[\"I don\'t have a big window in my room.\"]', 'correct', 1, 1, '2021-02-20 18:46:57', '2021-02-20 18:46:57'),
(1583, 36, 19, '[\"My mom don\'t has perfect cooking skills.\"]', '[\"My mom doesn\'t have perfect cooking skills.\"]', 'incorrect', 0, 1, '2021-02-20 18:46:57', '2021-02-20 18:46:57'),
(1584, 37, 19, '[\"ill\",\"quiet\",\"difficult\",\"beautiful\",\"crowded\",\"intelligent\",\"rich\",\"friendly\",\"interesting\"]', '[\"ill\",\"quiet\",\"difficult\",\"beautiful\",\"crowded\",\"intelligent\",\"rich\",\"friendly\",\"interesting\"]', '[\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\"]', 9, 9, '2021-02-20 18:46:57', '2021-02-20 18:46:57'),
(1585, 38, 19, '[\"arrived\",\"want\",\"went\",\"didn\'t understood\",\"talked\",\"meet\",\"came\",\"is doing\",\"needed\",\"rang\"]', '[\"arrived\",\"want\",\"have been\",\"don\'t understand\",\"talk\",\"met\",\"came\",\"is doing\",\"needs\",\"is ringing\"]', '[\"correct\",\"correct\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"correct\",\"correct\",\"incorrect\",\"incorrect\"]', 0, 10, '2021-02-20 18:46:57', '2021-02-20 18:46:57'),
(1586, 42, 19, '[\"24\",\"13.20\",\"18.45\",\"11\",\"5.15\",\"08.20\",\"259\"]', '[\"24\",\"13.20\",\"18.45\",\"11\",\"5.15\",\"8.20\",\"259\"]', '[\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\"]', 7, 7, '2021-02-20 18:46:57', '2021-02-20 18:46:57'),
(1587, 48, 19, '[\"speaking\",\"this is\",\"really sorry\",\"have to\",\"Oh, dear\",\"did she\",\"don\'t worry\",\"could you help me\",\"shall I\",\"that would be\"]', '[\"speaking\",\"this is\",\"really sorry\",\"have to\",\"Oh, dear\",\"did she\",\"don\'t worry\",\"could you help me\",\"shall I\",\"that would be\"]', '[\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\"]', 10, 10, '2021-02-20 18:46:57', '2021-02-20 18:46:57'),
(1588, 49, 19, '[\"will accept\"]', '[\"would accept\"]', 'incorrect', 0, 3, '2021-02-20 18:46:57', '2021-02-20 18:46:57'),
(1589, 50, 19, '[\"will pass\"]', '[\"will pass\"]', 'correct', 3, 3, '2021-02-20 18:46:57', '2021-02-20 18:46:57'),
(1590, 51, 19, '[\"seeing\"]', '[\"seeing\"]', 'correct', 3, 3, '2021-02-20 18:46:57', '2021-02-20 18:46:57'),
(1591, 52, 19, '[\"wants -- next\"]', '[\"wanted -- the following\"]', 'incorrect', 0, 3, '2021-02-20 18:46:57', '2021-02-20 18:46:57'),
(1592, 53, 19, '[\"which\"]', '[\"which\"]', 'correct', 3, 3, '2021-02-20 18:46:57', '2021-02-20 18:46:57'),
(1593, 54, 19, '[\"where\"]', '[\"where\"]', 'correct', 3, 3, '2021-02-20 18:46:57', '2021-02-20 18:46:57'),
(1594, 55, 19, '[\"were fired\"]', '[\"were fired\"]', 'correct', 3, 3, '2021-02-20 18:46:57', '2021-02-20 18:46:57'),
(1595, 56, 19, '[\"give up\"]', '[\"give up\"]', 'correct', 3, 3, '2021-02-20 18:46:57', '2021-02-20 18:46:57'),
(1596, 57, 19, '[\"put up with\"]', '[\"put up with\"]', 'correct', 3, 3, '2021-02-20 18:46:57', '2021-02-20 18:46:57'),
(1597, 58, 19, '[\"impolite\"]', '[\"impolite\"]', 'correct', 3, 3, '2021-02-20 18:46:57', '2021-02-20 18:46:57'),
(1598, 59, 19, '[\"nephew\"]', '[\"niece\"]', 'incorrect', 0, 3, '2021-02-20 18:46:57', '2021-02-20 18:46:57'),
(1599, 60, 19, '[\"mother-in-law\"]', '[\"mother-in-law\"]', 'correct', 3, 3, '2021-02-20 18:46:57', '2021-02-20 18:46:57'),
(1600, 61, 19, '[\"17\"]', '[\"17\"]', 'correct', 2, 2, '2021-02-20 18:46:57', '2021-02-20 18:46:57'),
(1601, 62, 19, '[\"20\"]', '[\"20\"]', 'correct', 2, 2, '2021-02-20 18:46:57', '2021-02-20 18:46:57'),
(1602, 63, 19, '[\"$20\"]', '[\"$20\"]', 'correct', 2, 2, '2021-02-20 18:46:57', '2021-02-20 18:46:57'),
(1603, 64, 19, '[\"10\"]', '[\"10\"]', 'correct', 2, 2, '2021-02-20 18:46:57', '2021-02-20 18:46:57'),
(1604, 65, 19, '[\"on his body\"]', '[\"on his body\"]', 'correct', 2, 2, '2021-02-20 18:46:57', '2021-02-20 18:46:57'),
(1605, 66, 19, '[\"stomach problems\"]', '[\"stomach problems\"]', 'correct', 2, 2, '2021-02-20 18:46:57', '2021-02-20 18:46:57'),
(1606, 67, 19, '[\"Although\"]', '[\"Although\"]', 'correct', 4, 4, '2021-02-20 18:46:57', '2021-02-20 18:46:57'),
(1607, 68, 19, '[\"Despite being tired\"]', '[\"Despite being tired\"]', 'correct', 4, 4, '2021-02-20 18:46:57', '2021-02-20 18:46:57'),
(1608, 69, 19, '[\"used to work\"]', '[\"am used to working\"]', 'incorrect', 0, 4, '2021-02-20 18:46:57', '2021-02-20 18:46:57'),
(1609, 71, 19, '[\"forgot\"]', '[\"had forgotten\"]', 'incorrect', 0, 4, '2021-02-20 18:46:57', '2021-02-20 18:46:57'),
(1610, 72, 19, '[\"keep in touch with\"]', '[\"keep in touch with\"]', 'correct', 4, 4, '2021-02-20 18:46:57', '2021-02-20 18:46:57'),
(1611, 73, 19, '[\"split\"]', '[\"split\"]', 'correct', 4, 4, '2021-02-20 18:46:57', '2021-02-20 18:46:57'),
(1612, 74, 19, '[\"not to\"]', '[\"not to\"]', 'correct', 4, 4, '2021-02-20 18:46:57', '2021-02-20 18:46:57'),
(1613, 75, 19, '[\"care\",\"enjoyable\",\"disappointment\"]', '[\"care\",\"enjoyable\",\"disappointment\"]', '[\"correct\",\"correct\",\"correct\"]', 6, 6, '2021-02-20 18:46:57', '2021-02-20 18:46:57'),
(1614, 76, 19, '[\"at a party\"]', '[\"at a party\"]', 'correct', 4, 4, '2021-02-20 18:46:57', '2021-02-20 18:46:57'),
(1615, 77, 19, '[\"lost her passport\"]', '[\"lost her passport\"]', 'correct', 4, 4, '2021-02-20 18:46:57', '2021-02-20 18:46:57'),
(1616, 78, 19, '[\"lost her job\"]', '[\"lost her job\"]', 'correct', 4, 4, '2021-02-20 18:46:57', '2021-02-20 18:46:57'),
(1617, 79, 19, '[\"the country\"]', '[\"the country\"]', 'correct', 4, 4, '2021-02-20 18:46:57', '2021-02-20 18:46:57'),
(1618, 80, 19, '[\"an aggressive\"]', '[\"an aggressive\"]', 'correct', 4, 4, '2021-02-20 18:46:57', '2021-02-20 18:46:57'),
(1619, 81, 19, '[\"a bike\"]', '[\"a bike\"]', 'correct', 4, 4, '2021-02-20 18:46:57', '2021-02-20 18:46:57'),
(1620, 82, 19, '[null,null,null,null]', '[\"looting\",\"robbery\",\"burgle\",\"vandalize\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 8, '2021-02-20 18:46:57', '2021-02-20 18:46:57'),
(1621, 83, 19, '[\"politician\",\"economic\",\"environmental\",\"pollution\"]', '[\"politician\",\"economic\",\"environmental\",\"pollution\"]', '[\"correct\",\"correct\",\"correct\",\"correct\"]', 8, 8, '2021-02-20 18:46:57', '2021-02-20 18:46:57'),
(1622, 84, 19, '[\"give someone a job\"]', '[\"fire someone form a job\"]', 'incorrect', 0, 4, '2021-02-20 18:46:57', '2021-02-20 18:46:57'),
(1623, 85, 19, '[\"strong\"]', '[\"a lot of money\"]', 'incorrect', 0, 4, '2021-02-20 18:46:57', '2021-02-20 18:46:57'),
(1624, 86, 19, '[\"have a rough voice\"]', '[\"have a difficulty in speaking\"]', 'incorrect', 0, 4, '2021-02-20 18:46:57', '2021-02-20 18:46:57'),
(1625, 87, 19, '[\"memorable\",\"scary\",\"overrated\",\"predictable\",\"gripping\",\"hilarious\"]', '[\"memorable\",\"scary\",\"overrated\",\"predictable\",\"gripping\",\"hilarious\"]', '[\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\"]', 12, 12, '2021-02-20 18:46:57', '2021-02-20 18:46:57'),
(1626, 90, 19, '[\"sued\",\"damages\",\"spread\",\"crisis\",\"hostage\",\"released\",\"invaded\",\"troops\",\"flee\",\"attack\",\"outcry\"]', '[\"sued\",\"damages\",\"spread\",\"crisis\",\"hostage\",\"released\",\"invaded\",\"troops\",\"flee\",\"attack\",\"outcry\"]', '[\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\"]', 30, 30, '2021-02-20 18:46:57', '2021-02-20 18:46:57'),
(1627, 91, 19, '[\"False\"]', '[\"False\"]', 'correct', 5, 5, '2021-02-20 18:46:57', '2021-02-20 18:46:57'),
(1628, 92, 19, '[\"True\"]', '[\"True\"]', 'correct', 5, 5, '2021-02-20 18:46:57', '2021-02-20 18:46:57'),
(1629, 93, 19, '[\"False\"]', '[\"False\"]', 'correct', 5, 5, '2021-02-20 18:46:57', '2021-02-20 18:46:57'),
(1630, 94, 19, '[\"True\"]', '[\"True\"]', 'correct', 5, 5, '2021-02-20 18:46:57', '2021-02-20 18:46:57'),
(1631, 95, 19, '[\"three times\"]', '[\"three times\"]', 'correct', 5, 5, '2021-02-20 18:46:57', '2021-02-20 18:46:57'),
(1632, 1, 6, '[\"What\"]', '[\"What\"]', 'correct', 1, 1, '2021-02-20 20:44:16', '2021-02-20 20:44:16'),
(1633, 2, 6, '[\"They\'re\"]', '[\"They\'re\"]', 'correct', 1, 1, '2021-02-20 20:44:16', '2021-02-20 20:44:16'),
(1634, 3, 6, '[\"It\"]', '[\"It\"]', 'correct', 1, 1, '2021-02-20 20:44:16', '2021-02-20 20:44:16'),
(1635, 5, 6, '[\"do you spell\"]', '[\"do you spell\"]', 'correct', 1, 1, '2021-02-20 20:44:16', '2021-02-20 20:44:16'),
(1636, 6, 6, '[\"one\"]', '[\"--\"]', 'incorrect', 0, 1, '2021-02-20 20:44:16', '2021-02-20 20:44:16'),
(1637, 7, 6, '[\"Is Mr. Mike a teacher?\"]', '[\"Is Mr. Mike a teacher?\"]', 'correct', 1, 1, '2021-02-20 20:44:16', '2021-02-20 20:44:16'),
(1638, 8, 6, '[\"your father\'s job\"]', '[\"your father\'s job\"]', 'correct', 1, 1, '2021-02-20 20:44:16', '2021-02-20 20:44:16'),
(1639, 9, 6, '[\"doesn\'t have\"]', '[\"doesn\'t have\"]', 'correct', 1, 1, '2021-02-20 20:44:16', '2021-02-20 20:44:16'),
(1640, 10, 6, '[\"works\"]', '[\"works\"]', 'correct', 1, 1, '2021-02-20 20:44:16', '2021-02-20 20:44:16'),
(1641, 11, 6, '[\"We often do not go to the cinema.\"]', '[\"We do not often go to the cinema.\"]', 'incorrect', 0, 1, '2021-02-20 20:44:16', '2021-02-20 20:44:16'),
(1642, 12, 6, '[\"do\"]', '[\"do\"]', 'correct', 1, 1, '2021-02-20 20:44:16', '2021-02-20 20:44:16'),
(1643, 14, 6, '[\"ever\"]', '[\"ever\"]', 'correct', 1, 1, '2021-02-20 20:44:16', '2021-02-20 20:44:16'),
(1644, 18, 6, '[\"tallest\"]', '[\"taller\"]', 'incorrect', 0, 1, '2021-02-20 20:44:16', '2021-02-20 20:44:16'),
(1645, 19, 6, '[\"more\"]', '[\"most\"]', 'incorrect', 0, 1, '2021-02-20 20:44:16', '2021-02-20 20:44:16'),
(1646, 23, 6, '[\"more friendly\"]', '[\"friendlier\"]', 'incorrect', 0, 1, '2021-02-20 20:44:16', '2021-02-20 20:44:16'),
(1647, 28, 6, '[\"on\"]', '[\"on\"]', 'correct', 1, 1, '2021-02-20 20:44:16', '2021-02-20 20:44:16'),
(1648, 29, 6, '[\"to\"]', '[\"for\"]', 'incorrect', 0, 1, '2021-02-20 20:44:16', '2021-02-20 20:44:16'),
(1649, 30, 6, 'Where does Sally\'s grandmother live?', '[]', '', 0, 1, '2021-02-20 20:44:16', '2021-02-20 20:44:16'),
(1650, 31, 6, 'How many children did Tom have?', '[]', '', 0, 1, '2021-02-20 20:44:16', '2021-02-20 20:44:16'),
(1651, 32, 6, 'When do they get up every morning?', '[]', '', 0, 1, '2021-02-20 20:44:16', '2021-02-20 20:44:16'),
(1652, 33, 6, '[\"I didn\'t go to the cinema\\u00a0 last night.\\u00a0\"]', '[\"I didn\'t go to the cinema\\u00a0 last night.\\u00a0\"]', 'correct', 1, 1, '2021-02-20 20:44:16', '2021-02-20 20:44:16'),
(1653, 34, 6, '[\"They haven\'t become very rich.\"]', '[\"They haven\'t become very rich.\"]', 'correct', 1, 1, '2021-02-20 20:44:16', '2021-02-20 20:44:16'),
(1654, 35, 6, '[\"I don\'t have a big window in my room.\"]', '[\"I don\'t have a big window in my room.\"]', 'correct', 1, 1, '2021-02-20 20:44:16', '2021-02-20 20:44:16'),
(1655, 36, 6, '[\"My mom doesn\'t have perfect cooking skills.\"]', '[\"My mom doesn\'t have perfect cooking skills.\"]', 'correct', 1, 1, '2021-02-20 20:44:16', '2021-02-20 20:44:16'),
(1656, 37, 6, '[\"ill\",\"quiet\",\"difficult\",\"beautiful\",\"crowded\",\"intelligent\",\"rich\",\"friendly\",\"interesting\"]', '[\"ill\",\"quiet\",\"difficult\",\"beautiful\",\"crowded\",\"intelligent\",\"rich\",\"friendly\",\"interesting\"]', '[\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\"]', 9, 9, '2021-02-20 20:44:16', '2021-02-20 20:44:16'),
(1657, 38, 6, '[\"have arrived\",\"have wanted\",\"have gone\",\"don\'t understand\",\"are talking\",\"met\",\"came\",\"has done\",\"need\",\"is ringing\"]', '[\"arrived\",\"want\",\"have been\",\"don\'t understand\",\"talk\",\"met\",\"came\",\"is doing\",\"needs\",\"is ringing\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"correct\",\"incorrect\",\"correct\",\"correct\",\"incorrect\",\"incorrect\",\"correct\"]', 0, 10, '2021-02-20 20:44:16', '2021-02-20 20:44:16'),
(1658, 42, 6, '[\"24th\",\"13:20\",\"18:45\",\"11th\",\"5:15\",\"8:20\",\"259\"]', '[\"24\",\"13.20\",\"18.45\",\"11\",\"5.15\",\"8.20\",\"259\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"correct\"]', 0, 7, '2021-02-20 20:44:16', '2021-02-20 20:44:16'),
(1659, 48, 6, '[\"speaking\",\"this is\",\"really sorry\",\"have to\",\"oh dear\",\"did she\",\"don\'t worry\",\"could you help me\",\"shall I\",\"that would be\"]', '[\"speaking\",\"this is\",\"really sorry\",\"have to\",\"Oh, dear\",\"did she\",\"don\'t worry\",\"could you help me\",\"shall I\",\"that would be\"]', '[\"correct\",\"correct\",\"correct\",\"correct\",\"incorrect\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\"]', 0, 10, '2021-02-20 20:44:16', '2021-02-20 20:44:16'),
(1660, 49, 6, '[\"would accept\"]', '[\"would accept\"]', 'correct', 3, 3, '2021-02-20 20:44:16', '2021-02-20 20:44:16'),
(1661, 50, 6, '[\"will pass\"]', '[\"will pass\"]', 'correct', 3, 3, '2021-02-20 20:44:16', '2021-02-20 20:44:16'),
(1662, 51, 6, '[\"see\"]', '[\"seeing\"]', 'incorrect', 0, 3, '2021-02-20 20:44:16', '2021-02-20 20:44:16'),
(1663, 52, 6, '[\"wants -- next\"]', '[\"wanted -- the following\"]', 'incorrect', 0, 3, '2021-02-20 20:44:16', '2021-02-20 20:44:16'),
(1664, 53, 6, '[\"which\"]', '[\"which\"]', 'correct', 3, 3, '2021-02-20 20:44:16', '2021-02-20 20:44:16'),
(1665, 54, 6, '[\"where\"]', '[\"where\"]', 'correct', 3, 3, '2021-02-20 20:44:16', '2021-02-20 20:44:16'),
(1666, 55, 6, '[\"have been fired\"]', '[\"were fired\"]', 'incorrect', 0, 3, '2021-02-20 20:44:16', '2021-02-20 20:44:16'),
(1667, 56, 6, '[\"give away\"]', '[\"give up\"]', 'incorrect', 0, 3, '2021-02-20 20:44:16', '2021-02-20 20:44:16'),
(1668, 57, 6, '[\"put up with\"]', '[\"put up with\"]', 'correct', 3, 3, '2021-02-20 20:44:16', '2021-02-20 20:44:16'),
(1669, 58, 6, '[\"impolite\"]', '[\"impolite\"]', 'correct', 3, 3, '2021-02-20 20:44:16', '2021-02-20 20:44:16'),
(1670, 59, 6, '[\"niece\"]', '[\"niece\"]', 'correct', 3, 3, '2021-02-20 20:44:16', '2021-02-20 20:44:16'),
(1671, 60, 6, '[\"mother-in-law\"]', '[\"mother-in-law\"]', 'correct', 3, 3, '2021-02-20 20:44:16', '2021-02-20 20:44:16'),
(1672, 67, 6, '[\"Although\"]', '[\"Although\"]', 'correct', 4, 4, '2021-02-20 20:44:16', '2021-02-20 20:44:16'),
(1673, 68, 6, '[\"Despite being tired\"]', '[\"Despite being tired\"]', 'correct', 4, 4, '2021-02-20 20:44:16', '2021-02-20 20:44:16'),
(1674, 69, 6, '[\"am used to work\"]', '[\"am used to working\"]', 'incorrect', 0, 4, '2021-02-20 20:44:16', '2021-02-20 20:44:16'),
(1675, 71, 6, '[\"have forgotten\"]', '[\"had forgotten\"]', 'incorrect', 0, 4, '2021-02-20 20:44:16', '2021-02-20 20:44:16'),
(1676, 72, 6, '[\"keep in touch with\"]', '[\"keep in touch with\"]', 'correct', 4, 4, '2021-02-20 20:44:16', '2021-02-20 20:44:16'),
(1677, 73, 6, '[\"splitted\"]', '[\"split\"]', 'incorrect', 0, 4, '2021-02-20 20:44:16', '2021-02-20 20:44:16'),
(1678, 74, 6, '[\"don\'t\"]', '[\"not to\"]', 'incorrect', 0, 4, '2021-02-20 20:44:16', '2021-02-20 20:44:16'),
(1679, 75, 6, '[\"careful\",\"enjoyable\",null]', '[\"care\",\"enjoyable\",\"disappointment\"]', '[\"incorrect\",\"correct\",\"incorrect\"]', 0, 6, '2021-02-20 20:44:16', '2021-02-20 20:44:16'),
(1680, 82, 6, '[null,null,null,null]', '[\"looting\",\"robbery\",\"burgle\",\"vandalize\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 8, '2021-02-20 20:44:16', '2021-02-20 20:44:16'),
(1681, 83, 6, '[null,null,null,null]', '[\"politician\",\"economic\",\"environmental\",\"pollution\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 8, '2021-02-20 20:44:16', '2021-02-20 20:44:16'),
(1682, 87, 6, '[null,null,null,null,null,null]', '[\"memorable\",\"scary\",\"overrated\",\"predictable\",\"gripping\",\"hilarious\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 12, '2021-02-20 20:44:16', '2021-02-20 20:44:16'),
(1683, 90, 6, '[null,null,null,null,null,null,null,null,null,null,null]', '[\"sued\",\"damages\",\"spread\",\"crisis\",\"hostage\",\"released\",\"invaded\",\"troops\",\"flee\",\"attack\",\"outcry\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 30, '2021-02-20 20:44:16', '2021-02-20 20:44:16'),
(1684, 1, 31, '[\"What\"]', '[\"What\"]', 'correct', 1, 1, '2021-02-21 05:05:49', '2021-02-21 05:05:49'),
(1685, 2, 31, '[\"They\'re\"]', '[\"They\'re\"]', 'correct', 1, 1, '2021-02-21 05:05:49', '2021-02-21 05:05:49'),
(1686, 3, 31, '[\"It\"]', '[\"It\"]', 'correct', 1, 1, '2021-02-21 05:05:49', '2021-02-21 05:05:49'),
(1687, 5, 31, '[\"do you spell\"]', '[\"do you spell\"]', 'correct', 1, 1, '2021-02-21 05:05:49', '2021-02-21 05:05:49'),
(1688, 6, 31, '[\"one\"]', '[\"--\"]', 'incorrect', 0, 1, '2021-02-21 05:05:49', '2021-02-21 05:05:49'),
(1689, 7, 31, '[\"Is Mr. Mike a teacher?\"]', '[\"Is Mr. Mike a teacher?\"]', 'correct', 1, 1, '2021-02-21 05:05:49', '2021-02-21 05:05:49'),
(1690, 8, 31, '[\"your father\'s job\"]', '[\"your father\'s job\"]', 'correct', 1, 1, '2021-02-21 05:05:49', '2021-02-21 05:05:49'),
(1691, 9, 31, '[\"doesn\'t have\"]', '[\"doesn\'t have\"]', 'correct', 1, 1, '2021-02-21 05:05:49', '2021-02-21 05:05:49'),
(1692, 10, 31, '[\"works\"]', '[\"works\"]', 'correct', 1, 1, '2021-02-21 05:05:49', '2021-02-21 05:05:49'),
(1693, 11, 31, '[\"We do not often go to the cinema.\"]', '[\"We do not often go to the cinema.\"]', 'correct', 1, 1, '2021-02-21 05:05:49', '2021-02-21 05:05:49'),
(1694, 12, 31, '[\"do\"]', '[\"do\"]', 'correct', 1, 1, '2021-02-21 05:05:49', '2021-02-21 05:05:49'),
(1695, 14, 31, '[\"ever\"]', '[\"ever\"]', 'correct', 1, 1, '2021-02-21 05:05:49', '2021-02-21 05:05:49'),
(1696, 18, 31, '[\"taller\"]', '[\"taller\"]', 'correct', 1, 1, '2021-02-21 05:05:49', '2021-02-21 05:05:49'),
(1697, 19, 31, '[\"most\"]', '[\"most\"]', 'correct', 1, 1, '2021-02-21 05:05:49', '2021-02-21 05:05:49'),
(1698, 23, 31, '[\"more friendly\"]', '[\"friendlier\"]', 'incorrect', 0, 1, '2021-02-21 05:05:49', '2021-02-21 05:05:49'),
(1699, 28, 31, '[\"on\"]', '[\"on\"]', 'correct', 1, 1, '2021-02-21 05:05:49', '2021-02-21 05:05:49'),
(1700, 29, 31, '[\"for\"]', '[\"for\"]', 'correct', 1, 1, '2021-02-21 05:05:49', '2021-02-21 05:05:49'),
(1701, 30, 31, 'where does sally\'s grandmother live?', '[]', '', 0, 1, '2021-02-21 05:05:49', '2021-02-21 05:05:49'),
(1702, 31, 31, 'How many children does tom have?', '[]', '', 0, 1, '2021-02-21 05:05:49', '2021-02-21 05:05:49'),
(1703, 32, 31, 'when do they get up every morning?', '[]', '', 0, 1, '2021-02-21 05:05:49', '2021-02-21 05:05:49'),
(1704, 33, 31, '[\"I didn\'t go to the cinema\\u00a0 last night.\\u00a0\"]', '[\"I didn\'t go to the cinema\\u00a0 last night.\\u00a0\"]', 'correct', 1, 1, '2021-02-21 05:05:49', '2021-02-21 05:05:49'),
(1705, 34, 31, '[\"They haven\'t become very rich.\"]', '[\"They haven\'t become very rich.\"]', 'correct', 1, 1, '2021-02-21 05:05:49', '2021-02-21 05:05:49'),
(1706, 35, 31, '[\"I don\'t have a big window in my room.\"]', '[\"I don\'t have a big window in my room.\"]', 'correct', 1, 1, '2021-02-21 05:05:49', '2021-02-21 05:05:49'),
(1707, 36, 31, '[\"My mom doesn\'t have perfect cooking skills.\"]', '[\"My mom doesn\'t have perfect cooking skills.\"]', 'correct', 1, 1, '2021-02-21 05:05:49', '2021-02-21 05:05:49'),
(1708, 37, 31, '[\"ill\",\"quiet\",\"difficult\",\"beautiful\",\"crowded\",\"intelligent\",\"rich\",\"friendly\",\"interesting\"]', '[\"ill\",\"quiet\",\"difficult\",\"beautiful\",\"crowded\",\"intelligent\",\"rich\",\"friendly\",\"interesting\"]', '[\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\"]', 9, 9, '2021-02-21 05:05:49', '2021-02-21 05:05:49'),
(1709, 38, 31, '[\"have arrived\",\"want\",\"have gone\",\"don\'t understand\",\"talk\",\"met\",\"came\",\"is doing\",\"needs\",\"is ringing\"]', '[\"arrived\",\"want\",\"have been\",\"don\'t understand\",\"talk\",\"met\",\"came\",\"is doing\",\"needs\",\"is ringing\"]', '[\"incorrect\",\"correct\",\"incorrect\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\"]', 0, 10, '2021-02-21 05:05:49', '2021-02-21 05:05:49'),
(1710, 42, 31, '[\"24\",\"13:20\",\"18:45\",\"11\",\"5:15\",\"8:20\",\"259\"]', '[\"24\",\"13.20\",\"18.45\",\"11\",\"5.15\",\"8.20\",\"259\"]', '[\"correct\",\"incorrect\",\"incorrect\",\"correct\",\"incorrect\",\"incorrect\",\"correct\"]', 0, 7, '2021-02-21 05:05:49', '2021-02-21 05:05:49'),
(1711, 48, 31, '[\"speaking\",\"this is\",\"really sorry\",\"have to go\",\"oh dear\",\"did she\",\"don\'t worry\",\"can you help me\",\"shall i\",\"the would be\"]', '[\"speaking\",\"this is\",\"really sorry\",\"have to\",\"Oh, dear\",\"did she\",\"don\'t worry\",\"could you help me\",\"shall I\",\"that would be\"]', '[\"correct\",\"correct\",\"correct\",\"incorrect\",\"incorrect\",\"correct\",\"correct\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 10, '2021-02-21 05:05:49', '2021-02-21 05:05:49'),
(1712, 49, 31, '[\"would accept\"]', '[\"would accept\"]', 'correct', 3, 3, '2021-02-21 05:05:49', '2021-02-21 05:05:49'),
(1713, 50, 31, '[\"will pass\"]', '[\"will pass\"]', 'correct', 3, 3, '2021-02-21 05:05:49', '2021-02-21 05:05:49'),
(1714, 51, 31, '[\"seeing\"]', '[\"seeing\"]', 'correct', 3, 3, '2021-02-21 05:05:49', '2021-02-21 05:05:49'),
(1715, 52, 31, '[\"wants -- next\"]', '[\"wanted -- the following\"]', 'incorrect', 0, 3, '2021-02-21 05:05:49', '2021-02-21 05:05:49'),
(1716, 53, 31, '[\"which\"]', '[\"which\"]', 'correct', 3, 3, '2021-02-21 05:05:49', '2021-02-21 05:05:49'),
(1717, 54, 31, '[\"where\"]', '[\"where\"]', 'correct', 3, 3, '2021-02-21 05:05:49', '2021-02-21 05:05:49'),
(1718, 55, 31, '[\"were fired\"]', '[\"were fired\"]', 'correct', 3, 3, '2021-02-21 05:05:49', '2021-02-21 05:05:49'),
(1719, 56, 31, '[\"give up\"]', '[\"give up\"]', 'correct', 3, 3, '2021-02-21 05:05:49', '2021-02-21 05:05:49');
INSERT INTO `employee_quizzes_old` (`id`, `quiz_id`, `employee_id`, `emp_answer`, `correct_answer`, `check_answer`, `emp_score`, `score`, `created_at`, `updated_at`) VALUES
(1720, 57, 31, '[\"go out with\"]', '[\"put up with\"]', 'incorrect', 0, 3, '2021-02-21 05:05:49', '2021-02-21 05:05:49'),
(1721, 58, 31, '[\"impolite\"]', '[\"impolite\"]', 'correct', 3, 3, '2021-02-21 05:05:49', '2021-02-21 05:05:49'),
(1722, 59, 31, '[\"niece\"]', '[\"niece\"]', 'correct', 3, 3, '2021-02-21 05:05:49', '2021-02-21 05:05:49'),
(1723, 60, 31, '[\"mother-in-law\"]', '[\"mother-in-law\"]', 'correct', 3, 3, '2021-02-21 05:05:49', '2021-02-21 05:05:49'),
(1724, 61, 31, '[\"17\"]', '[\"17\"]', 'correct', 2, 2, '2021-02-21 05:05:49', '2021-02-21 05:05:49'),
(1725, 62, 31, '[\"20\"]', '[\"20\"]', 'correct', 2, 2, '2021-02-21 05:05:49', '2021-02-21 05:05:49'),
(1726, 63, 31, '[\"$20\"]', '[\"$20\"]', 'correct', 2, 2, '2021-02-21 05:05:49', '2021-02-21 05:05:49'),
(1727, 64, 31, '[\"10\"]', '[\"10\"]', 'correct', 2, 2, '2021-02-21 05:05:49', '2021-02-21 05:05:49'),
(1728, 65, 31, '[\"on his body\"]', '[\"on his body\"]', 'correct', 2, 2, '2021-02-21 05:05:50', '2021-02-21 05:05:50'),
(1729, 66, 31, '[\"stomach problems\"]', '[\"stomach problems\"]', 'correct', 2, 2, '2021-02-21 05:05:50', '2021-02-21 05:05:50'),
(1730, 67, 31, '[\"Although\"]', '[\"Although\"]', 'correct', 4, 4, '2021-02-21 05:05:50', '2021-02-21 05:05:50'),
(1731, 68, 31, '[\"Despite being tired\"]', '[\"Despite being tired\"]', 'correct', 4, 4, '2021-02-21 05:05:50', '2021-02-21 05:05:50'),
(1732, 69, 31, '[\"used to work\"]', '[\"am used to working\"]', 'incorrect', 0, 4, '2021-02-21 05:05:50', '2021-02-21 05:05:50'),
(1733, 71, 31, '[\"have forgotten\"]', '[\"had forgotten\"]', 'incorrect', 0, 4, '2021-02-21 05:05:50', '2021-02-21 05:05:50'),
(1734, 72, 31, '[\"keep in touch with\"]', '[\"keep in touch with\"]', 'correct', 4, 4, '2021-02-21 05:05:50', '2021-02-21 05:05:50'),
(1735, 73, 31, '[\"have splitted\"]', '[\"split\"]', 'incorrect', 0, 4, '2021-02-21 05:05:50', '2021-02-21 05:05:50'),
(1736, 74, 31, '[\"not to\"]', '[\"not to\"]', 'correct', 4, 4, '2021-02-21 05:05:50', '2021-02-21 05:05:50'),
(1737, 75, 31, '[\"care\",\"enjoyable\",\"disappointment\"]', '[\"care\",\"enjoyable\",\"disappointment\"]', '[\"correct\",\"correct\",\"correct\"]', 6, 6, '2021-02-21 05:05:50', '2021-02-21 05:05:50'),
(1738, 76, 31, '[\"at a party\"]', '[\"at a party\"]', 'correct', 4, 4, '2021-02-21 05:05:50', '2021-02-21 05:05:50'),
(1739, 77, 31, '[\"lost her passport\"]', '[\"lost her passport\"]', 'correct', 4, 4, '2021-02-21 05:05:50', '2021-02-21 05:05:50'),
(1740, 78, 31, '[\"lost her job\"]', '[\"lost her job\"]', 'correct', 4, 4, '2021-02-21 05:05:50', '2021-02-21 05:05:50'),
(1741, 79, 31, '[\"the country\"]', '[\"the country\"]', 'correct', 4, 4, '2021-02-21 05:05:50', '2021-02-21 05:05:50'),
(1742, 80, 31, '[\"an aggressive\"]', '[\"an aggressive\"]', 'correct', 4, 4, '2021-02-21 05:05:50', '2021-02-21 05:05:50'),
(1743, 81, 31, '[\"some boxing gloves\"]', '[\"a bike\"]', 'incorrect', 0, 4, '2021-02-21 05:05:50', '2021-02-21 05:05:50'),
(1744, 82, 31, '[\"loot\",\"robbery\",\"burgle\",\"vandalise\"]', '[\"looting\",\"robbery\",\"burgle\",\"vandalize\"]', '[\"incorrect\",\"correct\",\"correct\",\"incorrect\"]', 0, 8, '2021-02-21 05:05:50', '2021-02-21 05:05:50'),
(1745, 83, 31, '[\"polite\",\"economical\",\"enviromental\",\"pollution\"]', '[\"politician\",\"economic\",\"environmental\",\"pollution\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"correct\"]', 0, 8, '2021-02-21 05:05:50', '2021-02-21 05:05:50'),
(1746, 84, 31, '[\"fire someone form a job\"]', '[\"fire someone form a job\"]', 'correct', 4, 4, '2021-02-21 05:05:50', '2021-02-21 05:05:50'),
(1747, 85, 31, '[\"a lot of money\"]', '[\"a lot of money\"]', 'correct', 4, 4, '2021-02-21 05:05:50', '2021-02-21 05:05:50'),
(1748, 86, 31, '[\"have a rough voice\"]', '[\"have a difficulty in speaking\"]', 'incorrect', 0, 4, '2021-02-21 05:05:50', '2021-02-21 05:05:50'),
(1749, 87, 31, '[\"memorable\",\"scary\",\"overrated\",\"predictable\",\"gripping\",\"hilarious\"]', '[\"memorable\",\"scary\",\"overrated\",\"predictable\",\"gripping\",\"hilarious\"]', '[\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\"]', 12, 12, '2021-02-21 05:05:50', '2021-02-21 05:05:50'),
(1750, 90, 31, '[\"sued\",\"damages\",\"spread\",\"crisis\",\"hostage\",\"released\",\"invaded\",\"troops\",\"flee\",\"attack\",\"outcry\"]', '[\"sued\",\"damages\",\"spread\",\"crisis\",\"hostage\",\"released\",\"invaded\",\"troops\",\"flee\",\"attack\",\"outcry\"]', '[\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\"]', 30, 30, '2021-02-21 05:05:50', '2021-02-21 05:05:50'),
(1751, 91, 31, '[\"False\"]', '[\"False\"]', 'correct', 5, 5, '2021-02-21 05:05:50', '2021-02-21 05:05:50'),
(1752, 92, 31, '[\"True\"]', '[\"True\"]', 'correct', 5, 5, '2021-02-21 05:05:50', '2021-02-21 05:05:50'),
(1753, 93, 31, '[\"False\"]', '[\"False\"]', 'correct', 5, 5, '2021-02-21 05:05:50', '2021-02-21 05:05:50'),
(1754, 94, 31, '[\"True\"]', '[\"True\"]', 'correct', 5, 5, '2021-02-21 05:05:50', '2021-02-21 05:05:50'),
(1755, 95, 31, '[\"three times\"]', '[\"three times\"]', 'correct', 5, 5, '2021-02-21 05:05:50', '2021-02-21 05:05:50'),
(1756, 96, 31, 'Dear Sir,\r\nI\'m writing this email to express my opinion about company\'s policy and working\r\nconditions.\r\nI believe that the main problem is not in the company\'s policy as much as how it applied on the employees.\r\nTo begin with Sick Leaves are listed in the company\'s policy but it is very difficult and needs massive paper work to be \r\naccepted, although it is accepted in other companies just by doctor\'s signature. As a consequence, most employees seek\r\nto take sick leaves from there holidays to avoid this paper work which leaves us with limited holidays negatively affecting our working environment.\r\nRegarding working conditions, in my view I totally agree that managers are doing their best to improve working conditions\r\non daily bases. Not only by giving daily objectives to improve team work ,but also by mixing the groups from different departments as well.\r\nAll things considered I hope my opinion reaches clearly and would be taken in consideration.\r\n\r\nYours sincerely,\r\nEng. Mohamed El Dewak', '[]', '', 0, 20, '2021-02-21 05:05:50', '2021-02-21 05:05:50'),
(1757, 1, 11, '[\"What\"]', '[\"What\"]', 'correct', 1, 1, '2021-02-22 05:04:35', '2021-02-22 05:04:35'),
(1758, 2, 11, '[\"They\'re\"]', '[\"They\'re\"]', 'correct', 1, 1, '2021-02-22 05:04:35', '2021-02-22 05:04:35'),
(1759, 3, 11, '[\"It\"]', '[\"It\"]', 'correct', 1, 1, '2021-02-22 05:04:35', '2021-02-22 05:04:35'),
(1760, 5, 11, '[\"do you spell\"]', '[\"do you spell\"]', 'correct', 1, 1, '2021-02-22 05:04:35', '2021-02-22 05:04:35'),
(1761, 6, 11, '[\"a\"]', '[\"--\"]', 'incorrect', 0, 1, '2021-02-22 05:04:35', '2021-02-22 05:04:35'),
(1762, 7, 11, '[\"Is Mr. Mike a teacher?\"]', '[\"Is Mr. Mike a teacher?\"]', 'correct', 1, 1, '2021-02-22 05:04:35', '2021-02-22 05:04:35'),
(1763, 8, 11, '[\"your father\'s job\"]', '[\"your father\'s job\"]', 'correct', 1, 1, '2021-02-22 05:04:35', '2021-02-22 05:04:35'),
(1764, 9, 11, '[\"doesn\'t have\"]', '[\"doesn\'t have\"]', 'correct', 1, 1, '2021-02-22 05:04:35', '2021-02-22 05:04:35'),
(1765, 10, 11, '[\"works\"]', '[\"works\"]', 'correct', 1, 1, '2021-02-22 05:04:35', '2021-02-22 05:04:35'),
(1766, 11, 11, '[\"We not often go to the cinema.\"]', '[\"We do not often go to the cinema.\"]', 'incorrect', 0, 1, '2021-02-22 05:04:35', '2021-02-22 05:04:35'),
(1767, 12, 11, '[\"go\"]', '[\"do\"]', 'incorrect', 0, 1, '2021-02-22 05:04:35', '2021-02-22 05:04:35'),
(1768, 14, 11, '[\"sometimes\"]', '[\"ever\"]', 'incorrect', 0, 1, '2021-02-22 05:04:35', '2021-02-22 05:04:35'),
(1769, 18, 11, '[\"tallest\"]', '[\"taller\"]', 'incorrect', 0, 1, '2021-02-22 05:04:35', '2021-02-22 05:04:35'),
(1770, 19, 11, '[\"most\"]', '[\"most\"]', 'correct', 1, 1, '2021-02-22 05:04:35', '2021-02-22 05:04:35'),
(1771, 23, 11, '[\"more friendly\"]', '[\"friendlier\"]', 'incorrect', 0, 1, '2021-02-22 05:04:35', '2021-02-22 05:04:35'),
(1772, 28, 11, '[\"on\"]', '[\"on\"]', 'correct', 1, 1, '2021-02-22 05:04:35', '2021-02-22 05:04:35'),
(1773, 29, 11, '[\"for\"]', '[\"for\"]', 'correct', 1, 1, '2021-02-22 05:04:35', '2021-02-22 05:04:35'),
(1774, 30, 11, 'Where does sally\'s grandmother live?', '[]', '', 0, 1, '2021-02-22 05:04:35', '2021-02-22 05:04:35'),
(1775, 31, 11, 'How many children did Tom have?', '[]', '', 0, 1, '2021-02-22 05:04:35', '2021-02-22 05:04:35'),
(1776, 32, 11, 'When do they get up?', '[]', '', 0, 1, '2021-02-22 05:04:35', '2021-02-22 05:04:35'),
(1777, 33, 11, '[\"I didn\'t go to the cinema\\u00a0 last night.\\u00a0\"]', '[\"I didn\'t go to the cinema\\u00a0 last night.\\u00a0\"]', 'correct', 1, 1, '2021-02-22 05:04:35', '2021-02-22 05:04:35'),
(1778, 34, 11, '[\"They don\'t have become very rich.\"]', '[\"They haven\'t become very rich.\"]', 'incorrect', 0, 1, '2021-02-22 05:04:35', '2021-02-22 05:04:35'),
(1779, 35, 11, '[\"I haven\'t a big window in my room.\"]', '[\"I don\'t have a big window in my room.\"]', 'incorrect', 0, 1, '2021-02-22 05:04:35', '2021-02-22 05:04:35'),
(1780, 36, 11, '[\"My mom doesn\'t have perfect cooking skills.\"]', '[\"My mom doesn\'t have perfect cooking skills.\"]', 'correct', 1, 1, '2021-02-22 05:04:35', '2021-02-22 05:04:35'),
(1781, 37, 11, '[\"ill\",\"quiet\",\"difficult\",\"interesting\",\"crowded\",\"intelligent\",\"rich\",\"friendly\",\"beautiful\"]', '[\"ill\",\"quiet\",\"difficult\",\"beautiful\",\"crowded\",\"intelligent\",\"rich\",\"friendly\",\"interesting\"]', '[\"correct\",\"correct\",\"correct\",\"incorrect\",\"correct\",\"correct\",\"correct\",\"correct\",\"incorrect\"]', 0, 9, '2021-02-22 05:04:35', '2021-02-22 05:04:35'),
(1782, 38, 11, '[\"arrived\",\"want\",\"Have , gone\",\"didn\'t understand\",\"talk\",\"met\",\"were coming\",\"is doing\",\"need\",\"is ringing\"]', '[\"arrived\",\"want\",\"have been\",\"don\'t understand\",\"talk\",\"met\",\"came\",\"is doing\",\"needs\",\"is ringing\"]', '[\"correct\",\"correct\",\"incorrect\",\"incorrect\",\"correct\",\"correct\",\"incorrect\",\"correct\",\"incorrect\",\"correct\"]', 0, 10, '2021-02-22 05:04:35', '2021-02-22 05:04:35'),
(1783, 42, 11, '[\"24\",\"airport\",\"18:45\",\"11 of\",\"13:20\",\"8:20\",\"259\"]', '[\"24\",\"13.20\",\"18.45\",\"11\",\"5.15\",\"8.20\",\"259\"]', '[\"correct\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"correct\"]', 0, 7, '2021-02-22 05:04:35', '2021-02-22 05:04:35'),
(1784, 48, 11, '[null,\"this is\",\"speaking\",\"have to\",\"really sorry\",\"Oh, dear\",\"don\'t worry\",\"could you help me\",\"Shall I\",\"that would be\"]', '[\"speaking\",\"this is\",\"really sorry\",\"have to\",\"Oh, dear\",\"did she\",\"don\'t worry\",\"could you help me\",\"shall I\",\"that would be\"]', '[\"incorrect\",\"correct\",\"incorrect\",\"correct\",\"incorrect\",\"incorrect\",\"correct\",\"correct\",\"incorrect\",\"correct\"]', 0, 10, '2021-02-22 05:04:35', '2021-02-22 05:04:35'),
(1785, 49, 11, '[\"would accept\"]', '[\"would accept\"]', 'correct', 3, 3, '2021-02-22 05:04:35', '2021-02-22 05:04:35'),
(1786, 50, 11, '[\"will pass\"]', '[\"will pass\"]', 'correct', 3, 3, '2021-02-22 05:04:35', '2021-02-22 05:04:35'),
(1787, 51, 11, '[\"seeing\"]', '[\"seeing\"]', 'correct', 3, 3, '2021-02-22 05:04:35', '2021-02-22 05:04:35'),
(1788, 52, 11, '[\"wants -- next\"]', '[\"wanted -- the following\"]', 'incorrect', 0, 3, '2021-02-22 05:04:35', '2021-02-22 05:04:35'),
(1789, 53, 11, '[\"which\"]', '[\"which\"]', 'correct', 3, 3, '2021-02-22 05:04:35', '2021-02-22 05:04:35'),
(1790, 54, 11, '[\"where\"]', '[\"where\"]', 'correct', 3, 3, '2021-02-22 05:04:35', '2021-02-22 05:04:35'),
(1791, 55, 11, '[\"fired\"]', '[\"were fired\"]', 'incorrect', 0, 3, '2021-02-22 05:04:35', '2021-02-22 05:04:35'),
(1792, 56, 11, '[\"give away\"]', '[\"give up\"]', 'incorrect', 0, 3, '2021-02-22 05:04:35', '2021-02-22 05:04:35'),
(1793, 57, 11, '[\"put up with\"]', '[\"put up with\"]', 'correct', 3, 3, '2021-02-22 05:04:35', '2021-02-22 05:04:35'),
(1794, 58, 11, '[\"impolite\"]', '[\"impolite\"]', 'correct', 3, 3, '2021-02-22 05:04:35', '2021-02-22 05:04:35'),
(1795, 59, 11, '[\"niece\"]', '[\"niece\"]', 'correct', 3, 3, '2021-02-22 05:04:35', '2021-02-22 05:04:35'),
(1796, 60, 11, '[\"mother-in-law\"]', '[\"mother-in-law\"]', 'correct', 3, 3, '2021-02-22 05:04:35', '2021-02-22 05:04:35'),
(1797, 61, 11, '[\"17\"]', '[\"17\"]', 'correct', 2, 2, '2021-02-22 05:04:35', '2021-02-22 05:04:35'),
(1798, 62, 11, '[\"20\"]', '[\"20\"]', 'correct', 2, 2, '2021-02-22 05:04:35', '2021-02-22 05:04:35'),
(1799, 63, 11, '[\"$20\"]', '[\"$20\"]', 'correct', 2, 2, '2021-02-22 05:04:35', '2021-02-22 05:04:35'),
(1800, 64, 11, '[\"10\"]', '[\"10\"]', 'correct', 2, 2, '2021-02-22 05:04:35', '2021-02-22 05:04:35'),
(1801, 65, 11, '[\"in the water tank\"]', '[\"on his body\"]', 'incorrect', 0, 2, '2021-02-22 05:04:35', '2021-02-22 05:04:35'),
(1802, 66, 11, '[\"stomach problems\"]', '[\"stomach problems\"]', 'correct', 2, 2, '2021-02-22 05:04:35', '2021-02-22 05:04:35'),
(1803, 67, 11, '[\"Although\"]', '[\"Although\"]', 'correct', 4, 4, '2021-02-22 05:04:35', '2021-02-22 05:04:35'),
(1804, 68, 11, '[\"Despite being tired\"]', '[\"Despite being tired\"]', 'correct', 4, 4, '2021-02-22 05:04:35', '2021-02-22 05:04:35'),
(1805, 69, 11, '[\"am used to working\"]', '[\"am used to working\"]', 'correct', 4, 4, '2021-02-22 05:04:35', '2021-02-22 05:04:35'),
(1806, 71, 11, '[\"had forgotten\"]', '[\"had forgotten\"]', 'correct', 4, 4, '2021-02-22 05:04:35', '2021-02-22 05:04:35'),
(1807, 72, 11, '[\"keep in touch with\"]', '[\"keep in touch with\"]', 'correct', 4, 4, '2021-02-22 05:04:35', '2021-02-22 05:04:35'),
(1808, 73, 11, '[\"splitted\"]', '[\"split\"]', 'incorrect', 0, 4, '2021-02-22 05:04:35', '2021-02-22 05:04:35'),
(1809, 74, 11, '[\"didn\'t\"]', '[\"not to\"]', 'incorrect', 0, 4, '2021-02-22 05:04:35', '2021-02-22 05:04:35'),
(1810, 75, 11, '[\"care\",\"enjoyable\",\"disappointment\"]', '[\"care\",\"enjoyable\",\"disappointment\"]', '[\"correct\",\"correct\",\"correct\"]', 6, 6, '2021-02-22 05:04:35', '2021-02-22 05:04:35'),
(1811, 76, 11, '[\"at a party\"]', '[\"at a party\"]', 'correct', 4, 4, '2021-02-22 05:04:35', '2021-02-22 05:04:35'),
(1812, 77, 11, '[\"lost her passport\"]', '[\"lost her passport\"]', 'correct', 4, 4, '2021-02-22 05:04:35', '2021-02-22 05:04:35'),
(1813, 78, 11, '[\"got into financial trouble\"]', '[\"lost her job\"]', 'incorrect', 0, 4, '2021-02-22 05:04:35', '2021-02-22 05:04:35'),
(1814, 79, 11, '[\"the country\"]', '[\"the country\"]', 'correct', 4, 4, '2021-02-22 05:04:35', '2021-02-22 05:04:35'),
(1815, 80, 11, '[\"a hard-working\"]', '[\"an aggressive\"]', 'incorrect', 0, 4, '2021-02-22 05:04:35', '2021-02-22 05:04:35'),
(1816, 81, 11, '[\"some boxing gloves\"]', '[\"a bike\"]', 'incorrect', 0, 4, '2021-02-22 05:04:35', '2021-02-22 05:04:35'),
(1817, 82, 11, '[\"loot\",\"robbery\",\"burglarize\",\"vandalise\"]', '[\"looting\",\"robbery\",\"burgle\",\"vandalize\"]', '[\"incorrect\",\"correct\",\"incorrect\",\"incorrect\"]', 0, 8, '2021-02-22 05:04:35', '2021-02-22 05:04:35'),
(1818, 83, 11, '[null,null,null,null]', '[\"politician\",\"economic\",\"environmental\",\"pollution\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 8, '2021-02-22 05:04:35', '2021-02-22 05:04:35'),
(1819, 84, 11, '[\"give someone a job\"]', '[\"fire someone form a job\"]', 'incorrect', 0, 4, '2021-02-22 05:04:35', '2021-02-22 05:04:35'),
(1820, 85, 11, '[\"a lot of money\"]', '[\"a lot of money\"]', 'correct', 4, 4, '2021-02-22 05:04:35', '2021-02-22 05:04:35'),
(1821, 86, 11, '[\"have a difficulty in speaking\"]', '[\"have a difficulty in speaking\"]', 'correct', 4, 4, '2021-02-22 05:04:35', '2021-02-22 05:04:35'),
(1822, 87, 11, '[\"memorable\",\"scary\",\"overrated\",\"predictable\",\"gripping\",\"hiarious\"]', '[\"memorable\",\"scary\",\"overrated\",\"predictable\",\"gripping\",\"hilarious\"]', '[\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"incorrect\"]', 0, 12, '2021-02-22 05:04:35', '2021-02-22 05:04:35'),
(1823, 90, 11, '[null,null,null,null,null,null,null,null,null,null,null]', '[\"sued\",\"damages\",\"spread\",\"crisis\",\"hostage\",\"released\",\"invaded\",\"troops\",\"flee\",\"attack\",\"outcry\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 30, '2021-02-22 05:04:35', '2021-02-22 05:04:35'),
(1824, 91, 11, '[\"False\"]', '[\"False\"]', 'correct', 5, 5, '2021-02-22 05:04:35', '2021-02-22 05:04:35'),
(1825, 92, 11, '[\"True\"]', '[\"True\"]', 'correct', 5, 5, '2021-02-22 05:04:35', '2021-02-22 05:04:35'),
(1826, 93, 11, '[\"True\"]', '[\"False\"]', 'incorrect', 0, 5, '2021-02-22 05:04:35', '2021-02-22 05:04:35'),
(1827, 94, 11, '[\"True\"]', '[\"True\"]', 'correct', 5, 5, '2021-02-22 05:04:35', '2021-02-22 05:04:35'),
(1828, 95, 11, '[\"three times\"]', '[\"three times\"]', 'correct', 5, 5, '2021-02-22 05:04:35', '2021-02-22 05:04:35'),
(1829, 1, 9, '[\"What\"]', '[\"What\"]', 'correct', 1, 1, '2021-02-22 08:08:09', '2021-02-22 08:08:09'),
(1830, 2, 9, '[\"They\'re\"]', '[\"They\'re\"]', 'correct', 1, 1, '2021-02-22 08:08:09', '2021-02-22 08:08:09'),
(1831, 3, 9, '[\"It\"]', '[\"It\"]', 'correct', 1, 1, '2021-02-22 08:08:09', '2021-02-22 08:08:09'),
(1832, 5, 9, '[\"do you spell\"]', '[\"do you spell\"]', 'correct', 1, 1, '2021-02-22 08:08:09', '2021-02-22 08:08:09'),
(1833, 6, 9, '[\"--\"]', '[\"--\"]', 'correct', 1, 1, '2021-02-22 08:08:09', '2021-02-22 08:08:09'),
(1834, 7, 9, '[\"Is Mr. Mike a teacher?\"]', '[\"Is Mr. Mike a teacher?\"]', 'correct', 1, 1, '2021-02-22 08:08:09', '2021-02-22 08:08:09'),
(1835, 8, 9, '[\"your father\'s job\"]', '[\"your father\'s job\"]', 'correct', 1, 1, '2021-02-22 08:08:09', '2021-02-22 08:08:09'),
(1836, 9, 9, '[\"doesn\'t have\"]', '[\"doesn\'t have\"]', 'correct', 1, 1, '2021-02-22 08:08:09', '2021-02-22 08:08:09'),
(1837, 10, 9, '[\"works\"]', '[\"works\"]', 'correct', 1, 1, '2021-02-22 08:08:09', '2021-02-22 08:08:09'),
(1838, 11, 9, '[\"We do not often go to the cinema.\"]', '[\"We do not often go to the cinema.\"]', 'correct', 1, 1, '2021-02-22 08:08:09', '2021-02-22 08:08:09'),
(1839, 12, 9, '[\"do\"]', '[\"do\"]', 'correct', 1, 1, '2021-02-22 08:08:09', '2021-02-22 08:08:09'),
(1840, 14, 9, '[\"never\"]', '[\"ever\"]', 'incorrect', 0, 1, '2021-02-22 08:08:09', '2021-02-22 08:08:09'),
(1841, 18, 9, '[\"taller\"]', '[\"taller\"]', 'correct', 1, 1, '2021-02-22 08:08:09', '2021-02-22 08:08:09'),
(1842, 19, 9, '[\"most\"]', '[\"most\"]', 'correct', 1, 1, '2021-02-22 08:08:09', '2021-02-22 08:08:09'),
(1843, 23, 9, '[\"more friendly\"]', '[\"friendlier\"]', 'incorrect', 0, 1, '2021-02-22 08:08:09', '2021-02-22 08:08:09'),
(1844, 28, 9, '[\"on\"]', '[\"on\"]', 'correct', 1, 1, '2021-02-22 08:08:09', '2021-02-22 08:08:09'),
(1845, 29, 9, '[\"for\"]', '[\"for\"]', 'correct', 1, 1, '2021-02-22 08:08:09', '2021-02-22 08:08:09'),
(1846, 30, 9, 'where sally\'s grandmother lives ?', '[]', '', 0, 1, '2021-02-22 08:08:09', '2021-02-22 08:08:09'),
(1847, 31, 9, 'how many children does Tom have ?', '[]', '', 0, 1, '2021-02-22 08:08:09', '2021-02-22 08:08:09'),
(1848, 32, 9, 'when they are used to get up ?', '[]', '', 0, 1, '2021-02-22 08:08:09', '2021-02-22 08:08:09'),
(1849, 33, 9, '[\"I didn\'t go to the cinema\\u00a0 last night.\\u00a0\"]', '[\"I didn\'t go to the cinema\\u00a0 last night.\\u00a0\"]', 'correct', 1, 1, '2021-02-22 08:08:09', '2021-02-22 08:08:09'),
(1850, 34, 9, '[\"They haven\'t become very rich.\"]', '[\"They haven\'t become very rich.\"]', 'correct', 1, 1, '2021-02-22 08:08:09', '2021-02-22 08:08:09'),
(1851, 35, 9, '[\"I don\'t have a big window in my room.\"]', '[\"I don\'t have a big window in my room.\"]', 'correct', 1, 1, '2021-02-22 08:08:09', '2021-02-22 08:08:09'),
(1852, 36, 9, '[\"My mom doesn\'t have perfect cooking skills.\"]', '[\"My mom doesn\'t have perfect cooking skills.\"]', 'correct', 1, 1, '2021-02-22 08:08:09', '2021-02-22 08:08:09'),
(1853, 37, 9, '[\"ill\",\"quiet\",\"difficult\",\"beutiful\",\"crowded\",\"intelligent\",\"rich\",\"friendly\",\"interesting\"]', '[\"ill\",\"quiet\",\"difficult\",\"beautiful\",\"crowded\",\"intelligent\",\"rich\",\"friendly\",\"interesting\"]', '[\"correct\",\"correct\",\"correct\",\"incorrect\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\"]', 0, 9, '2021-02-22 08:08:09', '2021-02-22 08:08:09'),
(1854, 38, 9, '[\"arrived\",\"want\",\"have , went\",\"am not understanding\",\"are talking\",\"have met\",\"came\",\"did\",\"needed\",\"is ringing\"]', '[\"arrived\",\"want\",\"have been\",\"don\'t understand\",\"talk\",\"met\",\"came\",\"is doing\",\"needs\",\"is ringing\"]', '[\"correct\",\"correct\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"correct\",\"incorrect\",\"incorrect\",\"correct\"]', 0, 10, '2021-02-22 08:08:09', '2021-02-22 08:08:09'),
(1855, 42, 9, '[\"24th\",\"13:20 pm\",\"18:45 pm\",\"11th of\",\"5:15 am\",\"8:20 am\",\"259 pounds\"]', '[\"24\",\"13.20\",\"18.45\",\"11\",\"5.15\",\"8.20\",\"259\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 7, '2021-02-22 08:08:09', '2021-02-22 08:08:09'),
(1856, 48, 9, '[\"speaking\",\"this is\",\"really sorry\",\"have to\",\"oh dear\",\"did she\",\"don\'t worry\",\"could you help me\",\"shall i\",\"that would be\"]', '[\"speaking\",\"this is\",\"really sorry\",\"have to\",\"Oh, dear\",\"did she\",\"don\'t worry\",\"could you help me\",\"shall I\",\"that would be\"]', '[\"correct\",\"correct\",\"correct\",\"correct\",\"incorrect\",\"correct\",\"correct\",\"correct\",\"incorrect\",\"correct\"]', 0, 10, '2021-02-22 08:08:09', '2021-02-22 08:08:09'),
(1857, 49, 9, '[\"will accept\"]', '[\"would accept\"]', 'incorrect', 0, 3, '2021-02-22 08:08:09', '2021-02-22 08:08:09'),
(1858, 50, 9, '[\"will pass\"]', '[\"will pass\"]', 'correct', 3, 3, '2021-02-22 08:08:09', '2021-02-22 08:08:09'),
(1859, 51, 9, '[\"see\"]', '[\"seeing\"]', 'incorrect', 0, 3, '2021-02-22 08:08:09', '2021-02-22 08:08:09'),
(1860, 52, 9, '[\"want -- next\"]', '[\"wanted -- the following\"]', 'incorrect', 0, 3, '2021-02-22 08:08:09', '2021-02-22 08:08:09'),
(1861, 53, 9, '[\"which\"]', '[\"which\"]', 'correct', 3, 3, '2021-02-22 08:08:09', '2021-02-22 08:08:09'),
(1862, 54, 9, '[\"where\"]', '[\"where\"]', 'correct', 3, 3, '2021-02-22 08:08:09', '2021-02-22 08:08:09'),
(1863, 55, 9, '[\"have been fired\"]', '[\"were fired\"]', 'incorrect', 0, 3, '2021-02-22 08:08:09', '2021-02-22 08:08:09'),
(1864, 56, 9, '[\"give up\"]', '[\"give up\"]', 'correct', 3, 3, '2021-02-22 08:08:09', '2021-02-22 08:08:09'),
(1865, 57, 9, '[\"put up with\"]', '[\"put up with\"]', 'correct', 3, 3, '2021-02-22 08:08:09', '2021-02-22 08:08:09'),
(1866, 58, 9, '[\"impolite\"]', '[\"impolite\"]', 'correct', 3, 3, '2021-02-22 08:08:09', '2021-02-22 08:08:09'),
(1867, 59, 9, '[\"niece\"]', '[\"niece\"]', 'correct', 3, 3, '2021-02-22 08:08:09', '2021-02-22 08:08:09'),
(1868, 60, 9, '[\"mother-in-law\"]', '[\"mother-in-law\"]', 'correct', 3, 3, '2021-02-22 08:08:09', '2021-02-22 08:08:09'),
(1869, 61, 9, '[\"17\"]', '[\"17\"]', 'correct', 2, 2, '2021-02-22 08:08:09', '2021-02-22 08:08:09'),
(1870, 62, 9, '[\"20\"]', '[\"20\"]', 'correct', 2, 2, '2021-02-22 08:08:09', '2021-02-22 08:08:09'),
(1871, 63, 9, '[\"$20\"]', '[\"$20\"]', 'correct', 2, 2, '2021-02-22 08:08:09', '2021-02-22 08:08:09'),
(1872, 64, 9, '[\"10\"]', '[\"10\"]', 'correct', 2, 2, '2021-02-22 08:08:09', '2021-02-22 08:08:09'),
(1873, 65, 9, '[\"on his body\"]', '[\"on his body\"]', 'correct', 2, 2, '2021-02-22 08:08:09', '2021-02-22 08:08:09'),
(1874, 66, 9, '[\"stomach problems\"]', '[\"stomach problems\"]', 'correct', 2, 2, '2021-02-22 08:08:10', '2021-02-22 08:08:10'),
(1875, 67, 9, '[\"Although\"]', '[\"Although\"]', 'correct', 4, 4, '2021-02-22 08:08:10', '2021-02-22 08:08:10'),
(1876, 68, 9, '[\"Despite being tired\"]', '[\"Despite being tired\"]', 'correct', 4, 4, '2021-02-22 08:08:10', '2021-02-22 08:08:10'),
(1877, 69, 9, '[\"used to work\"]', '[\"am used to working\"]', 'incorrect', 0, 4, '2021-02-22 08:08:10', '2021-02-22 08:08:10'),
(1878, 71, 9, '[\"have forgotten\"]', '[\"had forgotten\"]', 'incorrect', 0, 4, '2021-02-22 08:08:10', '2021-02-22 08:08:10'),
(1879, 72, 9, '[\"keep in touch with\"]', '[\"keep in touch with\"]', 'correct', 4, 4, '2021-02-22 08:08:10', '2021-02-22 08:08:10'),
(1880, 73, 9, '[\"have splitted\"]', '[\"split\"]', 'incorrect', 0, 4, '2021-02-22 08:08:10', '2021-02-22 08:08:10'),
(1881, 74, 9, '[\"not to\"]', '[\"not to\"]', 'correct', 4, 4, '2021-02-22 08:08:10', '2021-02-22 08:08:10'),
(1882, 75, 9, '[\"careness\",\"enjoyfull\",\"disappointing\"]', '[\"care\",\"enjoyable\",\"disappointment\"]', '[\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 6, '2021-02-22 08:08:10', '2021-02-22 08:08:10'),
(1883, 76, 9, '[\"at a party\"]', '[\"at a party\"]', 'correct', 4, 4, '2021-02-22 08:08:10', '2021-02-22 08:08:10'),
(1884, 77, 9, '[\"lost her passport\"]', '[\"lost her passport\"]', 'correct', 4, 4, '2021-02-22 08:08:10', '2021-02-22 08:08:10'),
(1885, 78, 9, '[\"got into financial trouble\",\"lost her job\"]', '[\"lost her job\"]', 'incorrect', 0, 4, '2021-02-22 08:08:10', '2021-02-22 08:08:10'),
(1886, 79, 9, '[\"the country\"]', '[\"the country\"]', 'correct', 4, 4, '2021-02-22 08:08:10', '2021-02-22 08:08:10'),
(1887, 80, 9, '[\"an aggressive\"]', '[\"an aggressive\"]', 'correct', 4, 4, '2021-02-22 08:08:10', '2021-02-22 08:08:10'),
(1888, 81, 9, '[\"a bike\"]', '[\"a bike\"]', 'correct', 4, 4, '2021-02-22 08:08:10', '2021-02-22 08:08:10'),
(1889, 82, 9, '[\"loting\",\"robbery\",\"burglarize\",\"vandalize\"]', '[\"looting\",\"robbery\",\"burgle\",\"vandalize\"]', '[\"incorrect\",\"correct\",\"incorrect\",\"correct\"]', 0, 8, '2021-02-22 08:08:10', '2021-02-22 08:08:10'),
(1890, 83, 9, '[\"politician\",\"economical\",\"environmental\",\"polluting\"]', '[\"politician\",\"economic\",\"environmental\",\"pollution\"]', '[\"correct\",\"incorrect\",\"correct\",\"incorrect\"]', 0, 8, '2021-02-22 08:08:10', '2021-02-22 08:08:10'),
(1891, 84, 9, '[\"give someone a job\"]', '[\"fire someone form a job\"]', 'incorrect', 0, 4, '2021-02-22 08:08:10', '2021-02-22 08:08:10'),
(1892, 85, 9, '[\"strong\"]', '[\"a lot of money\"]', 'incorrect', 0, 4, '2021-02-22 08:08:10', '2021-02-22 08:08:10'),
(1893, 86, 9, '[\"have a difficulty in speaking\"]', '[\"have a difficulty in speaking\"]', 'correct', 4, 4, '2021-02-22 08:08:10', '2021-02-22 08:08:10'),
(1894, 87, 9, '[\"memorable\",\"scary\",\"overrated\",\"predictable\",\"gripping\",\"hilarious\"]', '[\"memorable\",\"scary\",\"overrated\",\"predictable\",\"gripping\",\"hilarious\"]', '[\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\"]', 12, 12, '2021-02-22 08:08:10', '2021-02-22 08:08:10'),
(1895, 90, 9, '[\"attack\",\"sued\",\"spreed\",\"crisis\",\"hostage\",\"released\",\"invaded\",\"troops\",\"flee\",\"damages\",\"outcry\"]', '[\"sued\",\"damages\",\"spread\",\"crisis\",\"hostage\",\"released\",\"invaded\",\"troops\",\"flee\",\"attack\",\"outcry\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"incorrect\",\"correct\"]', 0, 30, '2021-02-22 08:08:10', '2021-02-22 08:08:10'),
(1896, 91, 9, '[\"True\"]', '[\"False\"]', 'incorrect', 0, 5, '2021-02-22 08:08:10', '2021-02-22 08:08:10'),
(1897, 92, 9, '[\"False\"]', '[\"True\"]', 'incorrect', 0, 5, '2021-02-22 08:08:10', '2021-02-22 08:08:10'),
(1898, 93, 9, '[\"True\"]', '[\"False\"]', 'incorrect', 0, 5, '2021-02-22 08:08:10', '2021-02-22 08:08:10'),
(1899, 94, 9, '[\"True\"]', '[\"True\"]', 'correct', 5, 5, '2021-02-22 08:08:10', '2021-02-22 08:08:10'),
(1900, 95, 9, '[\"three times\"]', '[\"three times\"]', 'correct', 5, 5, '2021-02-22 08:08:10', '2021-02-22 08:08:10'),
(1901, 96, 9, 'Dear, Manager \r\n\r\nI need to thank you firstly for your continuous support, and I want to share my opinion about the company\'s policy that I am interested at , and I found that our working condition is too healthy and collaborative \r\nand I have an advice to improve the connection tools between the departments . \r\nand focusing on the remote area suffering sometimes from miscommunication and late response . \r\n\r\nI appreciate everything you are doing to improve and develop our business and I am learning new things everyday literally  . \r\n\r\nMany Thanks and best regards', '[]', '', 0, 20, '2021-02-22 08:08:10', '2021-02-22 08:08:10'),
(1902, 1, 18, '[\"What\"]', '[\"What\"]', 'correct', 1, 1, '2021-02-22 16:53:19', '2021-02-22 16:53:19'),
(1903, 2, 18, '[\"They\'re\"]', '[\"They\'re\"]', 'correct', 1, 1, '2021-02-22 16:53:19', '2021-02-22 16:53:19'),
(1904, 3, 18, '[\"It\"]', '[\"It\"]', 'correct', 1, 1, '2021-02-22 16:53:19', '2021-02-22 16:53:19'),
(1905, 5, 18, '[\"are you spell\"]', '[\"do you spell\"]', 'incorrect', 0, 1, '2021-02-22 16:53:19', '2021-02-22 16:53:19'),
(1906, 6, 18, '[\"one\"]', '[\"--\"]', 'incorrect', 0, 1, '2021-02-22 16:53:19', '2021-02-22 16:53:19'),
(1907, 7, 18, '[\"Is this your phone?\"]', '[\"Is Mr. Mike a teacher?\"]', 'incorrect', 0, 1, '2021-02-22 16:53:19', '2021-02-22 16:53:19'),
(1908, 8, 18, '[\"job your father\"]', '[\"your father\'s job\"]', 'incorrect', 0, 1, '2021-02-22 16:53:19', '2021-02-22 16:53:19'),
(1909, 9, 18, '[\"doesn\'t have\"]', '[\"doesn\'t have\"]', 'correct', 1, 1, '2021-02-22 16:53:19', '2021-02-22 16:53:19'),
(1910, 10, 18, '[\"working\"]', '[\"works\"]', 'incorrect', 0, 1, '2021-02-22 16:53:19', '2021-02-22 16:53:19'),
(1911, 11, 18, '[\"We do not often go to the cinema.\"]', '[\"We do not often go to the cinema.\"]', 'correct', 1, 1, '2021-02-22 16:53:19', '2021-02-22 16:53:19'),
(1912, 12, 18, '[\"go\"]', '[\"do\"]', 'incorrect', 0, 1, '2021-02-22 16:53:19', '2021-02-22 16:53:19'),
(1913, 14, 18, '[\"sometimes\"]', '[\"ever\"]', 'incorrect', 0, 1, '2021-02-22 16:53:19', '2021-02-22 16:53:19'),
(1914, 18, 18, '[\"tallest\"]', '[\"taller\"]', 'incorrect', 0, 1, '2021-02-22 16:53:19', '2021-02-22 16:53:19'),
(1915, 19, 18, '[\"more\"]', '[\"most\"]', 'incorrect', 0, 1, '2021-02-22 16:53:19', '2021-02-22 16:53:19'),
(1916, 23, 18, '[\"friendlier\"]', '[\"friendlier\"]', 'correct', 1, 1, '2021-02-22 16:53:19', '2021-02-22 16:53:19'),
(1917, 28, 18, '[\"in\"]', '[\"on\"]', 'incorrect', 0, 1, '2021-02-22 16:53:19', '2021-02-22 16:53:19'),
(1918, 29, 18, '[\"for\"]', '[\"for\"]', 'correct', 1, 1, '2021-02-22 16:53:19', '2021-02-22 16:53:19'),
(1919, 30, 18, 'where grandmother Sally lives?', '[]', '', 0, 1, '2021-02-22 16:53:19', '2021-02-22 16:53:19'),
(1920, 31, 18, 'how mony tom children ?', '[]', '', 0, 1, '2021-02-22 16:53:19', '2021-02-22 16:53:19'),
(1921, 32, 18, 'when they morning ?', '[]', '', 0, 1, '2021-02-22 16:53:19', '2021-02-22 16:53:19'),
(1922, 33, 18, '[\"I don\'t went to the cinema\\u00a0 last night.\\u00a0\"]', '[\"I didn\'t go to the cinema\\u00a0 last night.\\u00a0\"]', 'incorrect', 0, 1, '2021-02-22 16:53:19', '2021-02-22 16:53:19'),
(1923, 34, 18, '[\"They haven\'t become very rich.\"]', '[\"They haven\'t become very rich.\"]', 'correct', 1, 1, '2021-02-22 16:53:19', '2021-02-22 16:53:19'),
(1924, 35, 18, '[\"I don\'t have a big window in my room.\"]', '[\"I don\'t have a big window in my room.\"]', 'correct', 1, 1, '2021-02-22 16:53:19', '2021-02-22 16:53:19'),
(1925, 36, 18, '[\"My mom doesn\'t  has perfect cooking skills.\"]', '[\"My mom doesn\'t have perfect cooking skills.\"]', 'incorrect', 0, 1, '2021-02-22 16:53:19', '2021-02-22 16:53:19'),
(1926, 37, 18, '[null,null,null,null,null,null,null,null,null]', '[\"ill\",\"quiet\",\"difficult\",\"beautiful\",\"crowded\",\"intelligent\",\"rich\",\"friendly\",\"interesting\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 9, '2021-02-22 16:53:19', '2021-02-22 16:53:19'),
(1927, 38, 18, '[null,null,null,null,null,null,null,null,null,null]', '[\"arrived\",\"want\",\"have been\",\"don\'t understand\",\"talk\",\"met\",\"came\",\"is doing\",\"needs\",\"is ringing\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 10, '2021-02-22 16:53:19', '2021-02-22 16:53:19'),
(1928, 42, 18, '[null,null,null,null,null,null,null]', '[\"24\",\"13.20\",\"18.45\",\"11\",\"5.15\",\"8.20\",\"259\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 7, '2021-02-22 16:53:19', '2021-02-22 16:53:19'),
(1929, 48, 18, '[null,null,null,null,null,null,null,null,null,null]', '[\"speaking\",\"this is\",\"really sorry\",\"have to\",\"Oh, dear\",\"did she\",\"don\'t worry\",\"could you help me\",\"shall I\",\"that would be\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 10, '2021-02-22 16:53:19', '2021-02-22 16:53:19'),
(1930, 75, 18, '[null,null,null]', '[\"care\",\"enjoyable\",\"disappointment\"]', '[\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 6, '2021-02-22 16:53:19', '2021-02-22 16:53:19'),
(1931, 82, 18, '[null,null,null,null]', '[\"looting\",\"robbery\",\"burgle\",\"vandalize\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 8, '2021-02-22 16:53:19', '2021-02-22 16:53:19'),
(1932, 83, 18, '[null,null,null,null]', '[\"politician\",\"economic\",\"environmental\",\"pollution\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 8, '2021-02-22 16:53:19', '2021-02-22 16:53:19'),
(1933, 87, 18, '[null,null,null,null,null,null]', '[\"memorable\",\"scary\",\"overrated\",\"predictable\",\"gripping\",\"hilarious\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 12, '2021-02-22 16:53:19', '2021-02-22 16:53:19'),
(1934, 90, 18, '[null,null,null,null,null,null,null,null,null,null,null]', '[\"sued\",\"damages\",\"spread\",\"crisis\",\"hostage\",\"released\",\"invaded\",\"troops\",\"flee\",\"attack\",\"outcry\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 30, '2021-02-22 16:53:19', '2021-02-22 16:53:19'),
(1935, 1, 30, '[\"What\"]', '[\"What\"]', 'correct', 1, 1, '2021-02-25 04:26:22', '2021-02-25 04:26:22'),
(1936, 2, 30, '[\"They\'re\"]', '[\"They\'re\"]', 'correct', 1, 1, '2021-02-25 04:26:22', '2021-02-25 04:26:22'),
(1937, 3, 30, '[\"It\"]', '[\"It\"]', 'correct', 1, 1, '2021-02-25 04:26:22', '2021-02-25 04:26:22'),
(1938, 5, 30, '[\"do you spell\"]', '[\"do you spell\"]', 'correct', 1, 1, '2021-02-25 04:26:22', '2021-02-25 04:26:22'),
(1939, 6, 30, '[\"a\"]', '[\"--\"]', 'incorrect', 0, 1, '2021-02-25 04:26:22', '2021-02-25 04:26:22'),
(1940, 7, 30, '[\"Is Mr. Mike a teacher?\"]', '[\"Is Mr. Mike a teacher?\"]', 'correct', 1, 1, '2021-02-25 04:26:22', '2021-02-25 04:26:22'),
(1941, 8, 30, '[\"your father\'s job\"]', '[\"your father\'s job\"]', 'correct', 1, 1, '2021-02-25 04:26:22', '2021-02-25 04:26:22'),
(1942, 9, 30, '[\"doesn\'t have\"]', '[\"doesn\'t have\"]', 'correct', 1, 1, '2021-02-25 04:26:22', '2021-02-25 04:26:22'),
(1943, 10, 30, '[\"works\"]', '[\"works\"]', 'correct', 1, 1, '2021-02-25 04:26:22', '2021-02-25 04:26:22'),
(1944, 11, 30, '[\"We do not often go to the cinema.\"]', '[\"We do not often go to the cinema.\"]', 'correct', 1, 1, '2021-02-25 04:26:22', '2021-02-25 04:26:22'),
(1945, 12, 30, '[\"do\"]', '[\"do\"]', 'correct', 1, 1, '2021-02-25 04:26:22', '2021-02-25 04:26:22'),
(1946, 14, 30, '[\"ever\"]', '[\"ever\"]', 'correct', 1, 1, '2021-02-25 04:26:22', '2021-02-25 04:26:22'),
(1947, 18, 30, '[\"taller\"]', '[\"taller\"]', 'correct', 1, 1, '2021-02-25 04:26:22', '2021-02-25 04:26:22'),
(1948, 19, 30, '[\"most\"]', '[\"most\"]', 'correct', 1, 1, '2021-02-25 04:26:22', '2021-02-25 04:26:22'),
(1949, 23, 30, '[\"more friendly\"]', '[\"friendlier\"]', 'incorrect', 0, 1, '2021-02-25 04:26:22', '2021-02-25 04:26:22'),
(1950, 28, 30, '[\"on\"]', '[\"on\"]', 'correct', 1, 1, '2021-02-25 04:26:22', '2021-02-25 04:26:22'),
(1951, 29, 30, '[\"for\"]', '[\"for\"]', 'correct', 1, 1, '2021-02-25 04:26:22', '2021-02-25 04:26:22'),
(1952, 30, 30, 'Where does Sally\'s grandmother lives?', '[]', '', 0, 1, '2021-02-25 04:26:22', '2021-02-25 04:26:22'),
(1953, 31, 30, 'How many children does tom have ?', '[]', '', 0, 1, '2021-02-25 04:26:22', '2021-02-25 04:26:22'),
(1954, 32, 30, 'When they get up?', '[]', '', 0, 1, '2021-02-25 04:26:22', '2021-02-25 04:26:22'),
(1955, 33, 30, '[\"I didn\'t go to the cinema\\u00a0 last night.\\u00a0\"]', '[\"I didn\'t go to the cinema\\u00a0 last night.\\u00a0\"]', 'correct', 1, 1, '2021-02-25 04:26:22', '2021-02-25 04:26:22'),
(1956, 34, 30, '[\"They haven\'t become very rich.\"]', '[\"They haven\'t become very rich.\"]', 'correct', 1, 1, '2021-02-25 04:26:22', '2021-02-25 04:26:22'),
(1957, 35, 30, '[\"I don\'t have a big window in my room.\"]', '[\"I don\'t have a big window in my room.\"]', 'correct', 1, 1, '2021-02-25 04:26:22', '2021-02-25 04:26:22'),
(1958, 36, 30, '[\"My mom doesn\'t have perfect cooking skills.\"]', '[\"My mom doesn\'t have perfect cooking skills.\"]', 'correct', 1, 1, '2021-02-25 04:26:22', '2021-02-25 04:26:22'),
(1959, 37, 30, '[\"ill\",\"quiet\",\"difficult\",\"beautiful\",\"crowded\",\"intelligent\",\"rich\",\"friendly\",\"interesting\"]', '[\"ill\",\"quiet\",\"difficult\",\"beautiful\",\"crowded\",\"intelligent\",\"rich\",\"friendly\",\"interesting\"]', '[\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\"]', 9, 9, '2021-02-25 04:26:22', '2021-02-25 04:26:22'),
(1960, 38, 30, '[\"arrived\",\"wanted\",\"Did , go\",\"did not understand\",\"were talking\",\"met\",\"came\",\"is doing\",\"need\",\"rings\"]', '[\"arrived\",\"want\",\"have been\",\"don\'t understand\",\"talk\",\"met\",\"came\",\"is doing\",\"needs\",\"is ringing\"]', '[\"correct\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"correct\",\"correct\",\"correct\",\"incorrect\",\"incorrect\"]', 0, 10, '2021-02-25 04:26:22', '2021-02-25 04:26:22'),
(1961, 42, 30, '[\"24th\",\"13:20\",\"18:45\",\"11\",\"05:15\",\"08:20\",\"250\"]', '[\"24\",\"13.20\",\"18.45\",\"11\",\"5.15\",\"8.20\",\"259\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"correct\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 7, '2021-02-25 04:26:22', '2021-02-25 04:26:22'),
(1962, 48, 30, '[\"speaking\",\"this is\",\"really sorry\",\"have to\",\"Oh, dear\",\"did she\",\"don\'t worry\",\"could you help me\",\"shall I\",\"that would be\"]', '[\"speaking\",\"this is\",\"really sorry\",\"have to\",\"Oh, dear\",\"did she\",\"don\'t worry\",\"could you help me\",\"shall I\",\"that would be\"]', '[\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\"]', 10, 10, '2021-02-25 04:26:22', '2021-02-25 04:26:22'),
(1963, 49, 30, '[\"would accept\"]', '[\"would accept\"]', 'correct', 3, 3, '2021-02-25 04:26:22', '2021-02-25 04:26:22'),
(1964, 50, 30, '[\"will pass\"]', '[\"will pass\"]', 'correct', 3, 3, '2021-02-25 04:26:22', '2021-02-25 04:26:22'),
(1965, 51, 30, '[\"see\"]', '[\"seeing\"]', 'incorrect', 0, 3, '2021-02-25 04:26:22', '2021-02-25 04:26:22'),
(1966, 52, 30, '[\"wants -- next\"]', '[\"wanted -- the following\"]', 'incorrect', 0, 3, '2021-02-25 04:26:22', '2021-02-25 04:26:22'),
(1967, 53, 30, '[\"which\"]', '[\"which\"]', 'correct', 3, 3, '2021-02-25 04:26:22', '2021-02-25 04:26:22'),
(1968, 54, 30, '[\"where\"]', '[\"where\"]', 'correct', 3, 3, '2021-02-25 04:26:22', '2021-02-25 04:26:22'),
(1969, 55, 30, '[\"were fired\"]', '[\"were fired\"]', 'correct', 3, 3, '2021-02-25 04:26:22', '2021-02-25 04:26:22'),
(1970, 56, 30, '[\"give up\"]', '[\"give up\"]', 'correct', 3, 3, '2021-02-25 04:26:22', '2021-02-25 04:26:22'),
(1971, 57, 30, '[\"put up with\"]', '[\"put up with\"]', 'correct', 3, 3, '2021-02-25 04:26:22', '2021-02-25 04:26:22'),
(1972, 58, 30, '[\"impolite\"]', '[\"impolite\"]', 'correct', 3, 3, '2021-02-25 04:26:22', '2021-02-25 04:26:22'),
(1973, 59, 30, '[\"niece\"]', '[\"niece\"]', 'correct', 3, 3, '2021-02-25 04:26:22', '2021-02-25 04:26:22'),
(1974, 60, 30, '[\"mother-in-law\"]', '[\"mother-in-law\"]', 'correct', 3, 3, '2021-02-25 04:26:22', '2021-02-25 04:26:22'),
(1975, 61, 30, '[\"17\"]', '[\"17\"]', 'correct', 2, 2, '2021-02-25 04:26:22', '2021-02-25 04:26:22'),
(1976, 62, 30, '[\"20\"]', '[\"20\"]', 'correct', 2, 2, '2021-02-25 04:26:22', '2021-02-25 04:26:22'),
(1977, 63, 30, '[\"$20\"]', '[\"$20\"]', 'correct', 2, 2, '2021-02-25 04:26:22', '2021-02-25 04:26:22'),
(1978, 64, 30, '[\"10\"]', '[\"10\"]', 'correct', 2, 2, '2021-02-25 04:26:22', '2021-02-25 04:26:22'),
(1979, 65, 30, '[\"on his body\"]', '[\"on his body\"]', 'correct', 2, 2, '2021-02-25 04:26:22', '2021-02-25 04:26:22'),
(1980, 66, 30, '[\"stomach problems\"]', '[\"stomach problems\"]', 'correct', 2, 2, '2021-02-25 04:26:22', '2021-02-25 04:26:22'),
(1981, 67, 30, '[\"Although\"]', '[\"Although\"]', 'correct', 4, 4, '2021-02-25 04:26:22', '2021-02-25 04:26:22'),
(1982, 68, 30, '[\"Despite being tired\"]', '[\"Despite being tired\"]', 'correct', 4, 4, '2021-02-25 04:26:22', '2021-02-25 04:26:22'),
(1983, 69, 30, '[\"am used to work\"]', '[\"am used to working\"]', 'incorrect', 0, 4, '2021-02-25 04:26:22', '2021-02-25 04:26:22'),
(1984, 71, 30, '[\"forgot\"]', '[\"had forgotten\"]', 'incorrect', 0, 4, '2021-02-25 04:26:22', '2021-02-25 04:26:22'),
(1985, 72, 30, '[\"keep in touch with\"]', '[\"keep in touch with\"]', 'correct', 4, 4, '2021-02-25 04:26:22', '2021-02-25 04:26:22'),
(1986, 73, 30, '[\"splitted\"]', '[\"split\"]', 'incorrect', 0, 4, '2021-02-25 04:26:22', '2021-02-25 04:26:22'),
(1987, 74, 30, '[\"not to\"]', '[\"not to\"]', 'correct', 4, 4, '2021-02-25 04:26:22', '2021-02-25 04:26:22'),
(1988, 75, 30, '[\"care\",\"enjoyable\",\"disappointment\"]', '[\"care\",\"enjoyable\",\"disappointment\"]', '[\"correct\",\"correct\",\"correct\"]', 6, 6, '2021-02-25 04:26:22', '2021-02-25 04:26:22'),
(1989, 76, 30, '[\"at a party\"]', '[\"at a party\"]', 'correct', 4, 4, '2021-02-25 04:26:22', '2021-02-25 04:26:22'),
(1990, 77, 30, '[\"lost her passport\"]', '[\"lost her passport\"]', 'correct', 4, 4, '2021-02-25 04:26:22', '2021-02-25 04:26:22'),
(1991, 78, 30, '[\"lost her job\"]', '[\"lost her job\"]', 'correct', 4, 4, '2021-02-25 04:26:22', '2021-02-25 04:26:22'),
(1992, 79, 30, '[\"the country\"]', '[\"the country\"]', 'correct', 4, 4, '2021-02-25 04:26:22', '2021-02-25 04:26:22'),
(1993, 80, 30, '[\"an aggressive\"]', '[\"an aggressive\"]', 'correct', 4, 4, '2021-02-25 04:26:22', '2021-02-25 04:26:22'),
(1994, 81, 30, '[\"some boxing gloves\"]', '[\"a bike\"]', 'incorrect', 0, 4, '2021-02-25 04:26:22', '2021-02-25 04:26:22'),
(1995, 82, 30, '[null,\"robbery\",\"burgle\",null]', '[\"looting\",\"robbery\",\"burgle\",\"vandalize\"]', '[\"incorrect\",\"correct\",\"correct\",\"incorrect\"]', 0, 8, '2021-02-25 04:26:22', '2021-02-25 04:26:22'),
(1996, 83, 30, '[\"politician\",\"economical\",\"environmental\",\"pollution\"]', '[\"politician\",\"economic\",\"environmental\",\"pollution\"]', '[\"correct\",\"incorrect\",\"correct\",\"correct\"]', 0, 8, '2021-02-25 04:26:22', '2021-02-25 04:26:22'),
(1997, 84, 30, '[\"fire someone form a job\"]', '[\"fire someone form a job\"]', 'correct', 4, 4, '2021-02-25 04:26:22', '2021-02-25 04:26:22'),
(1998, 85, 30, '[\"a lot of money\"]', '[\"a lot of money\"]', 'correct', 4, 4, '2021-02-25 04:26:22', '2021-02-25 04:26:22'),
(1999, 86, 30, '[\"have a difficulty in speaking\"]', '[\"have a difficulty in speaking\"]', 'correct', 4, 4, '2021-02-25 04:26:22', '2021-02-25 04:26:22'),
(2000, 87, 30, '[\"memorable\",\"scary\",\"overrated\",\"predictable\",\"gripping\",\"hilarious\"]', '[\"memorable\",\"scary\",\"overrated\",\"predictable\",\"gripping\",\"hilarious\"]', '[\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\"]', 12, 12, '2021-02-25 04:26:22', '2021-02-25 04:26:22'),
(2001, 90, 30, '[\"sued\",null,\"spread\",\"crisis\",\"hostage\",\"released\",\"invaded\",\"troops\",\"flee\",\"damages\",\"outcry\"]', '[\"sued\",\"damages\",\"spread\",\"crisis\",\"hostage\",\"released\",\"invaded\",\"troops\",\"flee\",\"attack\",\"outcry\"]', '[\"correct\",\"incorrect\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"incorrect\",\"correct\"]', 0, 30, '2021-02-25 04:26:22', '2021-02-25 04:26:22'),
(2002, 91, 30, '[\"False\"]', '[\"False\"]', 'correct', 5, 5, '2021-02-25 04:26:22', '2021-02-25 04:26:22'),
(2003, 92, 30, '[\"False\"]', '[\"True\"]', 'incorrect', 0, 5, '2021-02-25 04:26:23', '2021-02-25 04:26:23'),
(2004, 93, 30, '[\"True\"]', '[\"False\"]', 'incorrect', 0, 5, '2021-02-25 04:26:23', '2021-02-25 04:26:23'),
(2005, 94, 30, '[\"True\"]', '[\"True\"]', 'correct', 5, 5, '2021-02-25 04:26:23', '2021-02-25 04:26:23'),
(2006, 95, 30, '[\"three times\"]', '[\"three times\"]', 'correct', 5, 5, '2021-02-25 04:26:23', '2021-02-25 04:26:23'),
(2007, 96, 30, 'Dear Sir, \r\n\r\nHope this E-Mail find you well.\r\nRegarding to the mentioned topics:\r\n1.	Company Policy:\r\nCompany make balance between Intranational and Egyptian law policy to keep work forward and remove any barriers that restrict our workflow for good work environment.\r\nAdvantages:\r\n•	Company respects our privacy. \r\n•	Sick leaves, Vacation following Egyptian law.\r\n•	Medical insurance for each employee.\r\nDisadvantage:\r\n•	As Field service engineer we have to be available 24/7 \r\n2. Company environment:\r\n•	We work as one team.\r\n•	Regularly meeting to clear our vision and plan our future together.\r\n•	Manager listen to their employees and give them opportunity to show their work   without any reservation, to maintain \r\n        healthy environment.\r\n•	company organize multiple outings for employees to clear their minds and take rest to be able to perform a better job.\r\n\r\nSincerely,\r\nMohamed', '[]', '', 0, 20, '2021-02-25 04:26:23', '2021-02-25 04:26:23'),
(2008, 1, 34, '[\"What\"]', '[\"What\"]', 'correct', 1, 1, '2021-02-25 21:52:41', '2021-02-25 21:52:41'),
(2009, 2, 34, '[\"They\'re\"]', '[\"They\'re\"]', 'correct', 1, 1, '2021-02-25 21:52:41', '2021-02-25 21:52:41'),
(2010, 3, 34, '[\"It\"]', '[\"It\"]', 'correct', 1, 1, '2021-02-25 21:52:41', '2021-02-25 21:52:41'),
(2011, 5, 34, '[\"do you spell\"]', '[\"do you spell\"]', 'correct', 1, 1, '2021-02-25 21:52:41', '2021-02-25 21:52:41'),
(2012, 6, 34, '[\"one\"]', '[\"--\"]', 'incorrect', 0, 1, '2021-02-25 21:52:41', '2021-02-25 21:52:41'),
(2013, 7, 34, '[\"Is Mr. Mike a teacher?\"]', '[\"Is Mr. Mike a teacher?\"]', 'correct', 1, 1, '2021-02-25 21:52:41', '2021-02-25 21:52:41'),
(2014, 8, 34, '[\"your father job\",\"your father\'s job\"]', '[\"your father\'s job\"]', 'incorrect', 0, 1, '2021-02-25 21:52:41', '2021-02-25 21:52:41'),
(2015, 9, 34, '[\"doesn\'t have\"]', '[\"doesn\'t have\"]', 'correct', 1, 1, '2021-02-25 21:52:42', '2021-02-25 21:52:42'),
(2016, 10, 34, '[\"works\"]', '[\"works\"]', 'correct', 1, 1, '2021-02-25 21:52:42', '2021-02-25 21:52:42'),
(2017, 11, 34, '[\"We do not go often to the cinema.\"]', '[\"We do not often go to the cinema.\"]', 'incorrect', 0, 1, '2021-02-25 21:52:42', '2021-02-25 21:52:42'),
(2018, 12, 34, '[\"do\",\"make\"]', '[\"do\"]', 'incorrect', 0, 1, '2021-02-25 21:52:42', '2021-02-25 21:52:42'),
(2019, 14, 34, '[\"sometimes\"]', '[\"ever\"]', 'incorrect', 0, 1, '2021-02-25 21:52:42', '2021-02-25 21:52:42'),
(2020, 18, 34, '[\"taller\"]', '[\"taller\"]', 'correct', 1, 1, '2021-02-25 21:52:42', '2021-02-25 21:52:42'),
(2021, 19, 34, '[\"most\"]', '[\"most\"]', 'correct', 1, 1, '2021-02-25 21:52:43', '2021-02-25 21:52:43'),
(2022, 23, 34, '[\"more friendly\"]', '[\"friendlier\"]', 'incorrect', 0, 1, '2021-02-25 21:52:43', '2021-02-25 21:52:43'),
(2023, 28, 34, '[\"in\"]', '[\"on\"]', 'incorrect', 0, 1, '2021-02-25 21:52:43', '2021-02-25 21:52:43'),
(2024, 29, 34, '[\"for\"]', '[\"for\"]', 'correct', 1, 1, '2021-02-25 21:52:43', '2021-02-25 21:52:43'),
(2025, 30, 34, 'where do sally\'s grandmother live ?', '[]', '', 0, 1, '2021-02-25 21:52:43', '2021-02-25 21:52:43'),
(2026, 31, 34, 'how many children tom had ?', '[]', '', 0, 1, '2021-02-25 21:52:43', '2021-02-25 21:52:43'),
(2027, 32, 34, 'when are they getting up every morning ?', '[]', '', 0, 1, '2021-02-25 21:52:44', '2021-02-25 21:52:44'),
(2028, 33, 34, '[\"I didn\'t go to the cinema\\u00a0 last night.\\u00a0\"]', '[\"I didn\'t go to the cinema\\u00a0 last night.\\u00a0\"]', 'correct', 1, 1, '2021-02-25 21:52:44', '2021-02-25 21:52:44'),
(2029, 34, 34, '[\"They haven\'t become very rich.\"]', '[\"They haven\'t become very rich.\"]', 'correct', 1, 1, '2021-02-25 21:52:44', '2021-02-25 21:52:44'),
(2030, 35, 34, '[\"I don\'t have a big window in my room.\"]', '[\"I don\'t have a big window in my room.\"]', 'correct', 1, 1, '2021-02-25 21:52:44', '2021-02-25 21:52:44'),
(2031, 36, 34, '[\"My mom doesn\'t have perfect cooking skills.\",\"My mom hasn\'t perfect cooking skills.\"]', '[\"My mom doesn\'t have perfect cooking skills.\"]', 'incorrect', 0, 1, '2021-02-25 21:52:44', '2021-02-25 21:52:44'),
(2032, 37, 34, '[\"ill\",\"quiet\",\"difficult\",\"interesting\",\"crowded\",\"intelligent\",\"rich\",\"beautiful\",\"friendly\"]', '[\"ill\",\"quiet\",\"difficult\",\"beautiful\",\"crowded\",\"intelligent\",\"rich\",\"friendly\",\"interesting\"]', '[\"correct\",\"correct\",\"correct\",\"incorrect\",\"correct\",\"correct\",\"correct\",\"incorrect\",\"incorrect\"]', 0, 9, '2021-02-25 21:52:45', '2021-02-25 21:52:45'),
(2033, 38, 34, '[\"arrived\",\"want to\",\"did you go\",\"did not understand\",\"are talking\",\"met\",\"came\",\"have done\",\"was needing to\",\"is ringing\"]', '[\"arrived\",\"want\",\"have been\",\"don\'t understand\",\"talk\",\"met\",\"came\",\"is doing\",\"needs\",\"is ringing\"]', '[\"correct\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"correct\",\"correct\",\"incorrect\",\"incorrect\",\"correct\"]', 0, 10, '2021-02-25 21:52:45', '2021-02-25 21:52:45');
INSERT INTO `employee_quizzes_old` (`id`, `quiz_id`, `employee_id`, `emp_answer`, `correct_answer`, `check_answer`, `emp_score`, `score`, `created_at`, `updated_at`) VALUES
(2034, 42, 34, '[\"24 feb\",\"13.20 (24 feb )\",\"18.45\",\"11 march\",\"5.15 AM\",\"8.20 AM\",\"259 $\"]', '[\"24\",\"13.20\",\"18.45\",\"11\",\"5.15\",\"8.20\",\"259\"]', '[\"incorrect\",\"incorrect\",\"correct\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 7, '2021-02-25 21:52:45', '2021-02-25 21:52:45'),
(2035, 48, 34, '[\"speaking\",\"this is\",\"really sorry\",\"have to\",\"Oh, dear\",\"did she\",\"don\'t worry\",\"could you help me\",\"shall I\",\"that would be\"]', '[\"speaking\",\"this is\",\"really sorry\",\"have to\",\"Oh, dear\",\"did she\",\"don\'t worry\",\"could you help me\",\"shall I\",\"that would be\"]', '[\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\"]', 10, 10, '2021-02-25 21:52:45', '2021-02-25 21:52:45'),
(2036, 49, 34, '[\"will accept\"]', '[\"would accept\"]', 'incorrect', 0, 3, '2021-02-25 21:52:46', '2021-02-25 21:52:46'),
(2037, 50, 34, '[\"would pass\"]', '[\"will pass\"]', 'incorrect', 0, 3, '2021-02-25 21:52:46', '2021-02-25 21:52:46'),
(2038, 51, 34, '[\"see\"]', '[\"seeing\"]', 'incorrect', 0, 3, '2021-02-25 21:52:46', '2021-02-25 21:52:46'),
(2039, 52, 34, '[\"wants -- next\"]', '[\"wanted -- the following\"]', 'incorrect', 0, 3, '2021-02-25 21:52:46', '2021-02-25 21:52:46'),
(2040, 53, 34, '[\"which\"]', '[\"which\"]', 'correct', 3, 3, '2021-02-25 21:52:46', '2021-02-25 21:52:46'),
(2041, 54, 34, '[\"where\"]', '[\"where\"]', 'correct', 3, 3, '2021-02-25 21:52:46', '2021-02-25 21:52:46'),
(2042, 55, 34, '[\"were fired\"]', '[\"were fired\"]', 'correct', 3, 3, '2021-02-25 21:52:46', '2021-02-25 21:52:46'),
(2043, 56, 34, '[\"give up\"]', '[\"give up\"]', 'correct', 3, 3, '2021-02-25 21:52:46', '2021-02-25 21:52:46'),
(2044, 57, 34, '[\"put up with\"]', '[\"put up with\"]', 'correct', 3, 3, '2021-02-25 21:52:46', '2021-02-25 21:52:46'),
(2045, 58, 34, '[\"unpolite\"]', '[\"impolite\"]', 'incorrect', 0, 3, '2021-02-25 21:52:46', '2021-02-25 21:52:46'),
(2046, 59, 34, '[\"nephew\"]', '[\"niece\"]', 'incorrect', 0, 3, '2021-02-25 21:52:46', '2021-02-25 21:52:46'),
(2047, 60, 34, '[\"mother-in-law\"]', '[\"mother-in-law\"]', 'correct', 3, 3, '2021-02-25 21:52:47', '2021-02-25 21:52:47'),
(2048, 61, 34, '[\"17\"]', '[\"17\"]', 'correct', 2, 2, '2021-02-25 21:52:47', '2021-02-25 21:52:47'),
(2049, 62, 34, '[\"20\"]', '[\"20\"]', 'correct', 2, 2, '2021-02-25 21:52:47', '2021-02-25 21:52:47'),
(2050, 63, 34, '[\"$20\"]', '[\"$20\"]', 'correct', 2, 2, '2021-02-25 21:52:47', '2021-02-25 21:52:47'),
(2051, 64, 34, '[\"10\"]', '[\"10\"]', 'correct', 2, 2, '2021-02-25 21:52:47', '2021-02-25 21:52:47'),
(2052, 65, 34, '[\"in the water tank\"]', '[\"on his body\"]', 'incorrect', 0, 2, '2021-02-25 21:52:47', '2021-02-25 21:52:47'),
(2053, 66, 34, '[\"stomach problems\"]', '[\"stomach problems\"]', 'correct', 2, 2, '2021-02-25 21:52:47', '2021-02-25 21:52:47'),
(2054, 67, 34, '[\"Although\"]', '[\"Although\"]', 'correct', 4, 4, '2021-02-25 21:52:48', '2021-02-25 21:52:48'),
(2055, 68, 34, '[\"Although being tired\"]', '[\"Despite being tired\"]', 'incorrect', 0, 4, '2021-02-25 21:52:48', '2021-02-25 21:52:48'),
(2056, 69, 34, '[\"used to work\"]', '[\"am used to working\"]', 'incorrect', 0, 4, '2021-02-25 21:52:48', '2021-02-25 21:52:48'),
(2057, 71, 34, '[\"forgot\"]', '[\"had forgotten\"]', 'incorrect', 0, 4, '2021-02-25 21:52:48', '2021-02-25 21:52:48'),
(2058, 72, 34, '[\"keep in touch with\"]', '[\"keep in touch with\"]', 'correct', 4, 4, '2021-02-25 21:52:48', '2021-02-25 21:52:48'),
(2059, 73, 34, '[\"have splitted\"]', '[\"split\"]', 'incorrect', 0, 4, '2021-02-25 21:52:49', '2021-02-25 21:52:49'),
(2060, 74, 34, '[\"not to\"]', '[\"not to\"]', 'correct', 4, 4, '2021-02-25 21:52:49', '2021-02-25 21:52:49'),
(2061, 75, 34, '[\"CARE DEGREE\",\"ENJOYFUL\",\"DISAPPOINTEMENT\"]', '[\"care\",\"enjoyable\",\"disappointment\"]', '[\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 6, '2021-02-25 21:52:49', '2021-02-25 21:52:49'),
(2062, 76, 34, '[\"at a party\"]', '[\"at a party\"]', 'correct', 4, 4, '2021-02-25 21:52:49', '2021-02-25 21:52:49'),
(2063, 77, 34, '[\"missed her flight\",\"lost her passport\"]', '[\"lost her passport\"]', 'incorrect', 0, 4, '2021-02-25 21:52:49', '2021-02-25 21:52:49'),
(2064, 78, 34, '[\"lost her job\"]', '[\"lost her job\"]', 'correct', 4, 4, '2021-02-25 21:52:49', '2021-02-25 21:52:49'),
(2065, 79, 34, '[\"the country\"]', '[\"the country\"]', 'correct', 4, 4, '2021-02-25 21:52:49', '2021-02-25 21:52:49'),
(2066, 80, 34, '[\"an aggressive\"]', '[\"an aggressive\"]', 'correct', 4, 4, '2021-02-25 21:52:50', '2021-02-25 21:52:50'),
(2067, 81, 34, '[\"some boxing gloves\"]', '[\"a bike\"]', 'incorrect', 0, 4, '2021-02-25 21:52:50', '2021-02-25 21:52:50'),
(2068, 82, 34, '[\"LOOTARY\",\"ROBBING\",\"BURGLE\",\"VAND\"]', '[\"looting\",\"robbery\",\"burgle\",\"vandalize\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 8, '2021-02-25 21:52:50', '2021-02-25 21:52:50'),
(2069, 83, 34, '[\"POLITICALIST\",\"ECONOMICAL\",\"ENVIRONMENTAL\",\"POLLUTION\"]', '[\"politician\",\"economic\",\"environmental\",\"pollution\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 8, '2021-02-25 21:52:50', '2021-02-25 21:52:50'),
(2070, 84, 34, '[\"fire someone form a job\"]', '[\"fire someone form a job\"]', 'correct', 4, 4, '2021-02-25 21:52:50', '2021-02-25 21:52:50'),
(2071, 85, 34, '[\"strong\"]', '[\"a lot of money\"]', 'incorrect', 0, 4, '2021-02-25 21:52:50', '2021-02-25 21:52:50'),
(2072, 86, 34, '[\"have a difficulty in speaking\"]', '[\"have a difficulty in speaking\"]', 'correct', 4, 4, '2021-02-25 21:52:50', '2021-02-25 21:52:50'),
(2073, 87, 34, '[\"scary\",\"hilarious\",\"memorable\",\"predictable\",\"overrated\",\"gripping\"]', '[\"memorable\",\"scary\",\"overrated\",\"predictable\",\"gripping\",\"hilarious\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"correct\",\"incorrect\",\"incorrect\"]', 0, 12, '2021-02-25 21:52:50', '2021-02-25 21:52:50'),
(2074, 90, 34, '[\"Sued\",\"hostage\",\"spread\",\"released\",\"crisis\",\"released\",\"invaded\",\"troops\",\"outcry\",\"damages\",\"attack\"]', '[\"sued\",\"damages\",\"spread\",\"crisis\",\"hostage\",\"released\",\"invaded\",\"troops\",\"flee\",\"attack\",\"outcry\"]', '[\"incorrect\",\"incorrect\",\"correct\",\"incorrect\",\"incorrect\",\"correct\",\"correct\",\"correct\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 30, '2021-02-25 21:52:50', '2021-02-25 21:52:50'),
(2075, 91, 34, '[\"True\"]', '[\"False\"]', 'incorrect', 0, 5, '2021-02-25 21:52:51', '2021-02-25 21:52:51'),
(2076, 92, 34, '[\"True\"]', '[\"True\"]', 'correct', 5, 5, '2021-02-25 21:52:51', '2021-02-25 21:52:51'),
(2077, 93, 34, '[\"True\"]', '[\"False\"]', 'incorrect', 0, 5, '2021-02-25 21:52:51', '2021-02-25 21:52:51'),
(2078, 94, 34, '[\"False\"]', '[\"True\"]', 'incorrect', 0, 5, '2021-02-25 21:52:51', '2021-02-25 21:52:51'),
(2079, 95, 34, '[\"three times\"]', '[\"three times\"]', 'correct', 5, 5, '2021-02-25 21:52:51', '2021-02-25 21:52:51'),
(2080, 96, 34, 'dear eng khalid \r\nI hope that mail finding you well .\r\nactually I write that mail to discuss some issues in my job as a service engineer . \r\nfirst issue : working duty : if we did it by achievement not by time it will be better .\r\nsecond issue : spare parts request need a mandatory revision  .\r\npls approve my request , don\'t hesitate to ask any detailed questions .\r\nthanks and best regards', '[]', '', 0, 20, '2021-02-25 21:52:51', '2021-02-25 21:52:51'),
(2081, 1, 32, '[\"What\"]', '[\"What\"]', 'correct', 1, 1, '2021-02-26 21:37:30', '2021-02-26 21:37:30'),
(2082, 2, 32, '[\"They\'re\"]', '[\"They\'re\"]', 'correct', 1, 1, '2021-02-26 21:37:30', '2021-02-26 21:37:30'),
(2083, 3, 32, '[\"It\"]', '[\"It\"]', 'correct', 1, 1, '2021-02-26 21:37:30', '2021-02-26 21:37:30'),
(2084, 5, 32, '[\"do you spell\"]', '[\"do you spell\"]', 'correct', 1, 1, '2021-02-26 21:37:30', '2021-02-26 21:37:30'),
(2085, 6, 32, '[\"--\"]', '[\"--\"]', 'correct', 1, 1, '2021-02-26 21:37:30', '2021-02-26 21:37:30'),
(2086, 7, 32, '[\"Is Mr. Mike a teacher?\"]', '[\"Is Mr. Mike a teacher?\"]', 'correct', 1, 1, '2021-02-26 21:37:30', '2021-02-26 21:37:30'),
(2087, 8, 32, '[\"your father\'s job\"]', '[\"your father\'s job\"]', 'correct', 1, 1, '2021-02-26 21:37:30', '2021-02-26 21:37:30'),
(2088, 9, 32, '[\"doesn\'t have\"]', '[\"doesn\'t have\"]', 'correct', 1, 1, '2021-02-26 21:37:30', '2021-02-26 21:37:30'),
(2089, 10, 32, '[\"works\"]', '[\"works\"]', 'correct', 1, 1, '2021-02-26 21:37:30', '2021-02-26 21:37:30'),
(2090, 11, 32, '[\"We do not go often to the cinema.\"]', '[\"We do not often go to the cinema.\"]', 'incorrect', 0, 1, '2021-02-26 21:37:30', '2021-02-26 21:37:30'),
(2091, 12, 32, '[\"do\"]', '[\"do\"]', 'correct', 1, 1, '2021-02-26 21:37:30', '2021-02-26 21:37:30'),
(2092, 14, 32, '[\"ever\"]', '[\"ever\"]', 'correct', 1, 1, '2021-02-26 21:37:30', '2021-02-26 21:37:30'),
(2093, 18, 32, '[\"taller\"]', '[\"taller\"]', 'correct', 1, 1, '2021-02-26 21:37:30', '2021-02-26 21:37:30'),
(2094, 19, 32, '[\"most\"]', '[\"most\"]', 'correct', 1, 1, '2021-02-26 21:37:30', '2021-02-26 21:37:30'),
(2095, 23, 32, '[\"more friendly\"]', '[\"friendlier\"]', 'incorrect', 0, 1, '2021-02-26 21:37:31', '2021-02-26 21:37:31'),
(2096, 28, 32, '[\"on\"]', '[\"on\"]', 'correct', 1, 1, '2021-02-26 21:37:31', '2021-02-26 21:37:31'),
(2097, 29, 32, '[\"for\"]', '[\"for\"]', 'correct', 1, 1, '2021-02-26 21:37:31', '2021-02-26 21:37:31'),
(2098, 30, 32, 'Where does sally\'s grandmother lives?', '[]', '', 0, 1, '2021-02-26 21:37:31', '2021-02-26 21:37:31'),
(2099, 31, 32, 'how many children does Tom has?', '[]', '', 0, 1, '2021-02-26 21:37:31', '2021-02-26 21:37:31'),
(2100, 32, 32, 'when they get up every morning?', '[]', '', 0, 1, '2021-02-26 21:37:31', '2021-02-26 21:37:31'),
(2101, 33, 32, '[\"I didn\'t go to the cinema\\u00a0 last night.\\u00a0\"]', '[\"I didn\'t go to the cinema\\u00a0 last night.\\u00a0\"]', 'correct', 1, 1, '2021-02-26 21:37:31', '2021-02-26 21:37:31'),
(2102, 34, 32, '[\"They haven\'t become very rich.\"]', '[\"They haven\'t become very rich.\"]', 'correct', 1, 1, '2021-02-26 21:37:31', '2021-02-26 21:37:31'),
(2103, 35, 32, '[\"I don\'t have a big window in my room.\"]', '[\"I don\'t have a big window in my room.\"]', 'correct', 1, 1, '2021-02-26 21:37:31', '2021-02-26 21:37:31'),
(2104, 36, 32, '[\"My mom doesn\'t have perfect cooking skills.\"]', '[\"My mom doesn\'t have perfect cooking skills.\"]', 'correct', 1, 1, '2021-02-26 21:37:31', '2021-02-26 21:37:31'),
(2105, 37, 32, '[\"ill\",\"quiet\",\"difficult\",\"beautiful\",\"crowded\",\"intelligent\",\"rich\",\"friendly\",\"interesting\"]', '[\"ill\",\"quiet\",\"difficult\",\"beautiful\",\"crowded\",\"intelligent\",\"rich\",\"friendly\",\"interesting\"]', '[\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\"]', 9, 9, '2021-02-26 21:37:31', '2021-02-26 21:37:31'),
(2106, 38, 32, '[\"arrived\",\"want\",\"have went\",\"don\'t understand\",\"talk\",\"have met\",\"came\",\"is doing\",\"is doing\",\"is ringing\"]', '[\"arrived\",\"want\",\"have been\",\"don\'t understand\",\"talk\",\"met\",\"came\",\"is doing\",\"needs\",\"is ringing\"]', '[\"correct\",\"correct\",\"incorrect\",\"correct\",\"correct\",\"incorrect\",\"correct\",\"correct\",\"incorrect\",\"correct\"]', 0, 10, '2021-02-26 21:37:31', '2021-02-26 21:37:31'),
(2107, 42, 32, '[\"24\",\"13:20\",\"18:45\",\"11\",\"5:15\",\"08:20\",\"259\"]', '[\"24\",\"13.20\",\"18.45\",\"11\",\"5.15\",\"8.20\",\"259\"]', '[\"correct\",\"incorrect\",\"incorrect\",\"correct\",\"incorrect\",\"incorrect\",\"correct\"]', 0, 7, '2021-02-26 21:37:31', '2021-02-26 21:37:31'),
(2108, 48, 32, '[\"speaking\",\"this is\",\"really sorry\",\"have to\",\"Oh, dear\",\"did she\",\"don\'t worry\",\"could you help me\",\"shall I\",\"that would be\"]', '[\"speaking\",\"this is\",\"really sorry\",\"have to\",\"Oh, dear\",\"did she\",\"don\'t worry\",\"could you help me\",\"shall I\",\"that would be\"]', '[\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\"]', 10, 10, '2021-02-26 21:37:31', '2021-02-26 21:37:31'),
(2109, 49, 32, '[\"would accept\"]', '[\"would accept\"]', 'correct', 3, 3, '2021-02-26 21:37:31', '2021-02-26 21:37:31'),
(2110, 50, 32, '[\"will pass\"]', '[\"will pass\"]', 'correct', 3, 3, '2021-02-26 21:37:31', '2021-02-26 21:37:31'),
(2111, 51, 32, '[\"see\"]', '[\"seeing\"]', 'incorrect', 0, 3, '2021-02-26 21:37:31', '2021-02-26 21:37:31'),
(2112, 52, 32, '[\"wants -- next\"]', '[\"wanted -- the following\"]', 'incorrect', 0, 3, '2021-02-26 21:37:31', '2021-02-26 21:37:31'),
(2113, 53, 32, '[\"which\"]', '[\"which\"]', 'correct', 3, 3, '2021-02-26 21:37:31', '2021-02-26 21:37:31'),
(2114, 54, 32, '[\"where\"]', '[\"where\"]', 'correct', 3, 3, '2021-02-26 21:37:31', '2021-02-26 21:37:31'),
(2115, 55, 32, '[\"were fired\"]', '[\"were fired\"]', 'correct', 3, 3, '2021-02-26 21:37:31', '2021-02-26 21:37:31'),
(2116, 56, 32, '[\"give away\"]', '[\"give up\"]', 'incorrect', 0, 3, '2021-02-26 21:37:31', '2021-02-26 21:37:31'),
(2117, 57, 32, '[\"put up with\"]', '[\"put up with\"]', 'correct', 3, 3, '2021-02-26 21:37:31', '2021-02-26 21:37:31'),
(2118, 58, 32, '[\"impolite\"]', '[\"impolite\"]', 'correct', 3, 3, '2021-02-26 21:37:31', '2021-02-26 21:37:31'),
(2119, 59, 32, '[\"nephew\"]', '[\"niece\"]', 'incorrect', 0, 3, '2021-02-26 21:37:31', '2021-02-26 21:37:31'),
(2120, 60, 32, '[\"mother-in-law\"]', '[\"mother-in-law\"]', 'correct', 3, 3, '2021-02-26 21:37:31', '2021-02-26 21:37:31'),
(2121, 61, 32, '[\"17\"]', '[\"17\"]', 'correct', 2, 2, '2021-02-26 21:37:31', '2021-02-26 21:37:31'),
(2122, 62, 32, '[\"20\"]', '[\"20\"]', 'correct', 2, 2, '2021-02-26 21:37:31', '2021-02-26 21:37:31'),
(2123, 63, 32, '[\"$20\"]', '[\"$20\"]', 'correct', 2, 2, '2021-02-26 21:37:31', '2021-02-26 21:37:31'),
(2124, 64, 32, '[\"10\"]', '[\"10\"]', 'correct', 2, 2, '2021-02-26 21:37:31', '2021-02-26 21:37:31'),
(2125, 65, 32, '[\"on his body\"]', '[\"on his body\"]', 'correct', 2, 2, '2021-02-26 21:37:31', '2021-02-26 21:37:31'),
(2126, 66, 32, '[\"stomach problems\"]', '[\"stomach problems\"]', 'correct', 2, 2, '2021-02-26 21:37:31', '2021-02-26 21:37:31'),
(2127, 67, 32, '[\"However\"]', '[\"Although\"]', 'incorrect', 0, 4, '2021-02-26 21:37:31', '2021-02-26 21:37:31'),
(2128, 68, 32, '[\"Despite being tired\"]', '[\"Despite being tired\"]', 'correct', 4, 4, '2021-02-26 21:37:31', '2021-02-26 21:37:31'),
(2129, 69, 32, '[\"used to work\"]', '[\"am used to working\"]', 'incorrect', 0, 4, '2021-02-26 21:37:31', '2021-02-26 21:37:31'),
(2130, 71, 32, '[\"have forgotten\"]', '[\"had forgotten\"]', 'incorrect', 0, 4, '2021-02-26 21:37:31', '2021-02-26 21:37:31'),
(2131, 72, 32, '[\"keep in touch with\"]', '[\"keep in touch with\"]', 'correct', 4, 4, '2021-02-26 21:37:31', '2021-02-26 21:37:31'),
(2132, 73, 32, '[\"split\"]', '[\"split\"]', 'correct', 4, 4, '2021-02-26 21:37:31', '2021-02-26 21:37:31'),
(2133, 74, 32, '[\"not to\"]', '[\"not to\"]', 'correct', 4, 4, '2021-02-26 21:37:31', '2021-02-26 21:37:31'),
(2134, 75, 32, '[\"Care\",\"dislike\",\"disappointment\"]', '[\"care\",\"enjoyable\",\"disappointment\"]', '[\"incorrect\",\"incorrect\",\"correct\"]', 0, 6, '2021-02-26 21:37:31', '2021-02-26 21:37:31'),
(2135, 76, 32, '[\"at a party\"]', '[\"at a party\"]', 'correct', 4, 4, '2021-02-26 21:37:31', '2021-02-26 21:37:31'),
(2136, 77, 32, '[\"lost her passport\"]', '[\"lost her passport\"]', 'correct', 4, 4, '2021-02-26 21:37:31', '2021-02-26 21:37:31'),
(2137, 78, 32, '[\"lost her job\"]', '[\"lost her job\"]', 'correct', 4, 4, '2021-02-26 21:37:31', '2021-02-26 21:37:31'),
(2138, 79, 32, '[\"the country\"]', '[\"the country\"]', 'correct', 4, 4, '2021-02-26 21:37:31', '2021-02-26 21:37:31'),
(2139, 80, 32, '[\"an aggressive\"]', '[\"an aggressive\"]', 'correct', 4, 4, '2021-02-26 21:37:31', '2021-02-26 21:37:31'),
(2140, 81, 32, '[\"a bike\"]', '[\"a bike\"]', 'correct', 4, 4, '2021-02-26 21:37:31', '2021-02-26 21:37:31'),
(2141, 82, 32, '[\"loot\",\"robbery\",\"Burglarize\",\"vandalize\"]', '[\"looting\",\"robbery\",\"burgle\",\"vandalize\"]', '[\"incorrect\",\"correct\",\"incorrect\",\"correct\"]', 0, 8, '2021-02-26 21:37:31', '2021-02-26 21:37:31'),
(2142, 83, 32, '[\"politician\",\"economical\",\"environmental\",\"pollution\"]', '[\"politician\",\"economic\",\"environmental\",\"pollution\"]', '[\"correct\",\"incorrect\",\"correct\",\"correct\"]', 0, 8, '2021-02-26 21:37:31', '2021-02-26 21:37:31'),
(2143, 84, 32, '[\"fire someone form a job\"]', '[\"fire someone form a job\"]', 'correct', 4, 4, '2021-02-26 21:37:31', '2021-02-26 21:37:31'),
(2144, 85, 32, '[\"a lot of money\"]', '[\"a lot of money\"]', 'correct', 4, 4, '2021-02-26 21:37:31', '2021-02-26 21:37:31'),
(2145, 86, 32, '[\"have a difficulty in speaking\"]', '[\"have a difficulty in speaking\"]', 'correct', 4, 4, '2021-02-26 21:37:31', '2021-02-26 21:37:31'),
(2146, 87, 32, '[\"memorable\",\"scary\",\"overrated\",\"predictable\",\"gripping\",\"hilarious\"]', '[\"memorable\",\"scary\",\"overrated\",\"predictable\",\"gripping\",\"hilarious\"]', '[\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\"]', 12, 12, '2021-02-26 21:37:31', '2021-02-26 21:37:31'),
(2147, 90, 32, '[\"sued\",\"damages\",\"spread\",\"crisis\",\"hostage\",\"released\",\"invaded\",\"troops\",\"flee\",\"attack\",\"outcry\"]', '[\"sued\",\"damages\",\"spread\",\"crisis\",\"hostage\",\"released\",\"invaded\",\"troops\",\"flee\",\"attack\",\"outcry\"]', '[\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\"]', 30, 30, '2021-02-26 21:37:31', '2021-02-26 21:37:31'),
(2148, 91, 32, '[\"False\"]', '[\"False\"]', 'correct', 5, 5, '2021-02-26 21:37:31', '2021-02-26 21:37:31'),
(2149, 92, 32, '[\"True\"]', '[\"True\"]', 'correct', 5, 5, '2021-02-26 21:37:31', '2021-02-26 21:37:31'),
(2150, 93, 32, '[\"True\"]', '[\"False\"]', 'incorrect', 0, 5, '2021-02-26 21:37:31', '2021-02-26 21:37:31'),
(2151, 94, 32, '[\"True\"]', '[\"True\"]', 'correct', 5, 5, '2021-02-26 21:37:31', '2021-02-26 21:37:31'),
(2152, 95, 32, '[\"three times\"]', '[\"three times\"]', 'correct', 5, 5, '2021-02-26 21:37:31', '2021-02-26 21:37:31'),
(2153, 96, 32, 'Dear Manager,\r\nHope you are well,\r\nOur company\'s policies have many advantages like workplace health and safety and employee complaint policies but, others policies need to be reviewed. The working conditions somehow are tough as late night work without overtime, the leave policy need some review and working hours need to be reduced as it is too much.\r\n\r\nI am sure you will take these notes into consideration.\r\n\r\nBest Regards,\r\nMMM', '[]', '', 0, 20, '2021-02-26 21:37:31', '2021-02-26 21:37:31'),
(2154, 1, 29, '[\"What\"]', '[\"What\"]', 'correct', 1, 1, '2021-02-27 17:15:39', '2021-02-27 17:15:39'),
(2155, 2, 29, '[\"They\'re\"]', '[\"They\'re\"]', 'correct', 1, 1, '2021-02-27 17:15:39', '2021-02-27 17:15:39'),
(2156, 3, 29, '[\"It\"]', '[\"It\"]', 'correct', 1, 1, '2021-02-27 17:15:39', '2021-02-27 17:15:39'),
(2157, 5, 29, '[\"do you spell\"]', '[\"do you spell\"]', 'correct', 1, 1, '2021-02-27 17:15:39', '2021-02-27 17:15:39'),
(2158, 6, 29, '[\"a\"]', '[\"--\"]', 'incorrect', 0, 1, '2021-02-27 17:15:39', '2021-02-27 17:15:39'),
(2159, 7, 29, '[\"Is Mr. Mike a teacher?\"]', '[\"Is Mr. Mike a teacher?\"]', 'correct', 1, 1, '2021-02-27 17:15:39', '2021-02-27 17:15:39'),
(2160, 8, 29, '[\"your father\'s job\"]', '[\"your father\'s job\"]', 'correct', 1, 1, '2021-02-27 17:15:39', '2021-02-27 17:15:39'),
(2161, 9, 29, '[\"doesn\'t have\"]', '[\"doesn\'t have\"]', 'correct', 1, 1, '2021-02-27 17:15:39', '2021-02-27 17:15:39'),
(2162, 10, 29, '[\"works\"]', '[\"works\"]', 'correct', 1, 1, '2021-02-27 17:15:39', '2021-02-27 17:15:39'),
(2163, 11, 29, '[\"We do not often go to the cinema.\"]', '[\"We do not often go to the cinema.\"]', 'correct', 1, 1, '2021-02-27 17:15:39', '2021-02-27 17:15:39'),
(2164, 12, 29, '[\"do\"]', '[\"do\"]', 'correct', 1, 1, '2021-02-27 17:15:39', '2021-02-27 17:15:39'),
(2165, 14, 29, '[\"ever\"]', '[\"ever\"]', 'correct', 1, 1, '2021-02-27 17:15:39', '2021-02-27 17:15:39'),
(2166, 18, 29, '[\"tallest\"]', '[\"taller\"]', 'incorrect', 0, 1, '2021-02-27 17:15:39', '2021-02-27 17:15:39'),
(2167, 19, 29, '[\"most\"]', '[\"most\"]', 'correct', 1, 1, '2021-02-27 17:15:39', '2021-02-27 17:15:39'),
(2168, 23, 29, '[\"more friendly\"]', '[\"friendlier\"]', 'incorrect', 0, 1, '2021-02-27 17:15:39', '2021-02-27 17:15:39'),
(2169, 28, 29, '[\"on\"]', '[\"on\"]', 'correct', 1, 1, '2021-02-27 17:15:39', '2021-02-27 17:15:39'),
(2170, 29, 29, '[\"for\"]', '[\"for\"]', 'correct', 1, 1, '2021-02-27 17:15:39', '2021-02-27 17:15:39'),
(2171, 30, 29, 'Where does Sally\'s grandmother live?', '[]', '', 0, 1, '2021-02-27 17:15:39', '2021-02-27 17:15:39'),
(2172, 31, 29, 'How many children did Tom have?', '[]', '', 0, 1, '2021-02-27 17:15:39', '2021-02-27 17:15:39'),
(2173, 32, 29, 'When do they get up every morning?\r\nWhat time do they get up every morning?', '[]', '', 0, 1, '2021-02-27 17:15:39', '2021-02-27 17:15:39'),
(2174, 33, 29, '[\"I didn\'t go to the cinema\\u00a0 last night.\\u00a0\"]', '[\"I didn\'t go to the cinema\\u00a0 last night.\\u00a0\"]', 'correct', 1, 1, '2021-02-27 17:15:39', '2021-02-27 17:15:39'),
(2175, 34, 29, '[\"They haven\'t become very rich.\"]', '[\"They haven\'t become very rich.\"]', 'correct', 1, 1, '2021-02-27 17:15:39', '2021-02-27 17:15:39'),
(2176, 35, 29, '[\"I don\'t have a big window in my room.\"]', '[\"I don\'t have a big window in my room.\"]', 'correct', 1, 1, '2021-02-27 17:15:39', '2021-02-27 17:15:39'),
(2177, 36, 29, '[\"My mom doesn\'t have perfect cooking skills.\"]', '[\"My mom doesn\'t have perfect cooking skills.\"]', 'correct', 1, 1, '2021-02-27 17:15:39', '2021-02-27 17:15:39'),
(2178, 37, 29, '[\"ill\",\"quiet\",\"difficult\",\"beautiful\",\"crowded\",\"intelligent\",\"rich\",\"friendly\",\"interesting\"]', '[\"ill\",\"quiet\",\"difficult\",\"beautiful\",\"crowded\",\"intelligent\",\"rich\",\"friendly\",\"interesting\"]', '[\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\"]', 9, 9, '2021-02-27 17:15:39', '2021-02-27 17:15:39'),
(2179, 38, 29, '[\"arrived\",\"am going\",\"Have gone\",\"don\'t\",\"are talking\",\"met\",\"came\",\"has done\",\"needs\",\"is ringing\"]', '[\"arrived\",\"want\",\"have been\",\"don\'t understand\",\"talk\",\"met\",\"came\",\"is doing\",\"needs\",\"is ringing\"]', '[\"correct\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"correct\",\"correct\",\"incorrect\",\"correct\",\"correct\"]', 0, 10, '2021-02-27 17:15:39', '2021-02-27 17:15:39'),
(2180, 42, 29, '[\"24\",\"13:20\",\"18:45\",\"11\",\"05:15\",\"08:20\",\"259\"]', '[\"24\",\"13.20\",\"18.45\",\"11\",\"5.15\",\"8.20\",\"259\"]', '[\"correct\",\"incorrect\",\"incorrect\",\"correct\",\"incorrect\",\"incorrect\",\"correct\"]', 0, 7, '2021-02-27 17:15:39', '2021-02-27 17:15:39'),
(2181, 48, 29, '[\"speaking\",\"this is\",\"really sorry\",\"have to\",\"oh, dear\",\"did she\",\"don\'t worry\",\"could you help me\",\"shall I\",\"that would be\"]', '[\"speaking\",\"this is\",\"really sorry\",\"have to\",\"Oh, dear\",\"did she\",\"don\'t worry\",\"could you help me\",\"shall I\",\"that would be\"]', '[\"correct\",\"correct\",\"correct\",\"correct\",\"incorrect\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\"]', 0, 10, '2021-02-27 17:15:39', '2021-02-27 17:15:39'),
(2182, 49, 29, '[\"would accept\"]', '[\"would accept\"]', 'correct', 3, 3, '2021-02-27 17:15:39', '2021-02-27 17:15:39'),
(2183, 50, 29, '[\"will pass\"]', '[\"will pass\"]', 'correct', 3, 3, '2021-02-27 17:15:39', '2021-02-27 17:15:39'),
(2184, 51, 29, '[\"see\"]', '[\"seeing\"]', 'incorrect', 0, 3, '2021-02-27 17:15:39', '2021-02-27 17:15:39'),
(2185, 52, 29, '[\"wants -- next\"]', '[\"wanted -- the following\"]', 'incorrect', 0, 3, '2021-02-27 17:15:39', '2021-02-27 17:15:39'),
(2186, 53, 29, '[\"which\"]', '[\"which\"]', 'correct', 3, 3, '2021-02-27 17:15:39', '2021-02-27 17:15:39'),
(2187, 54, 29, '[\"where\"]', '[\"where\"]', 'correct', 3, 3, '2021-02-27 17:15:39', '2021-02-27 17:15:39'),
(2188, 55, 29, '[\"have been fired\"]', '[\"were fired\"]', 'incorrect', 0, 3, '2021-02-27 17:15:39', '2021-02-27 17:15:39'),
(2189, 56, 29, '[\"give up\"]', '[\"give up\"]', 'correct', 3, 3, '2021-02-27 17:15:39', '2021-02-27 17:15:39'),
(2190, 57, 29, '[\"put up with\"]', '[\"put up with\"]', 'correct', 3, 3, '2021-02-27 17:15:39', '2021-02-27 17:15:39'),
(2191, 58, 29, '[\"impolite\"]', '[\"impolite\"]', 'correct', 3, 3, '2021-02-27 17:15:39', '2021-02-27 17:15:39'),
(2192, 59, 29, '[\"niece\"]', '[\"niece\"]', 'correct', 3, 3, '2021-02-27 17:15:39', '2021-02-27 17:15:39'),
(2193, 60, 29, '[\"mother-in-law\"]', '[\"mother-in-law\"]', 'correct', 3, 3, '2021-02-27 17:15:39', '2021-02-27 17:15:39'),
(2194, 61, 29, '[\"17\"]', '[\"17\"]', 'correct', 2, 2, '2021-02-27 17:15:39', '2021-02-27 17:15:39'),
(2195, 62, 29, '[\"20\"]', '[\"20\"]', 'correct', 2, 2, '2021-02-27 17:15:39', '2021-02-27 17:15:39'),
(2196, 63, 29, '[\"$20\"]', '[\"$20\"]', 'correct', 2, 2, '2021-02-27 17:15:39', '2021-02-27 17:15:39'),
(2197, 64, 29, '[\"10\"]', '[\"10\"]', 'correct', 2, 2, '2021-02-27 17:15:39', '2021-02-27 17:15:39'),
(2198, 65, 29, '[\"in the water tank\"]', '[\"on his body\"]', 'incorrect', 0, 2, '2021-02-27 17:15:39', '2021-02-27 17:15:39'),
(2199, 66, 29, '[\"stomach problems\"]', '[\"stomach problems\"]', 'correct', 2, 2, '2021-02-27 17:15:39', '2021-02-27 17:15:39'),
(2200, 67, 29, '[\"Although\"]', '[\"Although\"]', 'correct', 4, 4, '2021-02-27 17:15:39', '2021-02-27 17:15:39'),
(2201, 68, 29, '[\"Despite being tired\"]', '[\"Despite being tired\"]', 'correct', 4, 4, '2021-02-27 17:15:39', '2021-02-27 17:15:39'),
(2202, 69, 29, '[\"am used to working\"]', '[\"am used to working\"]', 'correct', 4, 4, '2021-02-27 17:15:39', '2021-02-27 17:15:39'),
(2203, 71, 29, '[\"have forgotten\"]', '[\"had forgotten\"]', 'incorrect', 0, 4, '2021-02-27 17:15:39', '2021-02-27 17:15:39'),
(2204, 72, 29, '[\"keep in touch with\"]', '[\"keep in touch with\"]', 'correct', 4, 4, '2021-02-27 17:15:39', '2021-02-27 17:15:39'),
(2205, 73, 29, '[\"split\"]', '[\"split\"]', 'correct', 4, 4, '2021-02-27 17:15:39', '2021-02-27 17:15:39'),
(2206, 74, 29, '[\"not to\"]', '[\"not to\"]', 'correct', 4, 4, '2021-02-27 17:15:39', '2021-02-27 17:15:39'),
(2207, 75, 29, '[\"caution\",\"enjoyable\",\"disappointment\"]', '[\"care\",\"enjoyable\",\"disappointment\"]', '[\"incorrect\",\"correct\",\"correct\"]', 0, 6, '2021-02-27 17:15:39', '2021-02-27 17:15:39'),
(2208, 76, 29, '[\"at a party\"]', '[\"at a party\"]', 'correct', 4, 4, '2021-02-27 17:15:39', '2021-02-27 17:15:39'),
(2209, 77, 29, '[\"lost her passport\"]', '[\"lost her passport\"]', 'correct', 4, 4, '2021-02-27 17:15:39', '2021-02-27 17:15:39'),
(2210, 78, 29, '[\"got into financial trouble\",\"lost her job\"]', '[\"lost her job\"]', 'incorrect', 0, 4, '2021-02-27 17:15:39', '2021-02-27 17:15:39'),
(2211, 79, 29, '[\"the country\"]', '[\"the country\"]', 'correct', 4, 4, '2021-02-27 17:15:39', '2021-02-27 17:15:39'),
(2212, 80, 29, '[\"an aggressive\"]', '[\"an aggressive\"]', 'correct', 4, 4, '2021-02-27 17:15:39', '2021-02-27 17:15:39'),
(2213, 81, 29, '[\"some boxing gloves\"]', '[\"a bike\"]', 'incorrect', 0, 4, '2021-02-27 17:15:39', '2021-02-27 17:15:39'),
(2214, 82, 29, '[\"loot\",\"robbery\",\"burglary\",\"ruin\\/ vandal\"]', '[\"looting\",\"robbery\",\"burgle\",\"vandalize\"]', '[\"incorrect\",\"correct\",\"incorrect\",\"incorrect\"]', 0, 8, '2021-02-27 17:15:39', '2021-02-27 17:15:39'),
(2215, 83, 29, '[\"politician\",\"economical\",\"enviromental\",\"pollution\"]', '[\"politician\",\"economic\",\"environmental\",\"pollution\"]', '[\"correct\",\"incorrect\",\"incorrect\",\"correct\"]', 0, 8, '2021-02-27 17:15:39', '2021-02-27 17:15:39'),
(2216, 84, 29, '[\"fire someone form a job\"]', '[\"fire someone form a job\"]', 'correct', 4, 4, '2021-02-27 17:15:39', '2021-02-27 17:15:39'),
(2217, 85, 29, '[\"a lot of money\"]', '[\"a lot of money\"]', 'correct', 4, 4, '2021-02-27 17:15:39', '2021-02-27 17:15:39'),
(2218, 86, 29, '[\"have a rough voice\"]', '[\"have a difficulty in speaking\"]', 'incorrect', 0, 4, '2021-02-27 17:15:39', '2021-02-27 17:15:39'),
(2219, 87, 29, '[\"memorable\",\"scary\",\"overrated\",\"predictable\",\"gripping\",\"hilarious\"]', '[\"memorable\",\"scary\",\"overrated\",\"predictable\",\"gripping\",\"hilarious\"]', '[\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\"]', 12, 12, '2021-02-27 17:15:39', '2021-02-27 17:15:39'),
(2220, 90, 29, '[null,null,null,null,\"hostage\",\"released\",\"troops\",\"invaded\",null,null,null]', '[\"sued\",\"damages\",\"spread\",\"crisis\",\"hostage\",\"released\",\"invaded\",\"troops\",\"flee\",\"attack\",\"outcry\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"correct\",\"correct\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 30, '2021-02-27 17:15:39', '2021-02-27 17:15:39'),
(2221, 91, 29, '[\"False\"]', '[\"False\"]', 'correct', 5, 5, '2021-02-27 17:15:39', '2021-02-27 17:15:39'),
(2222, 92, 29, '[\"True\"]', '[\"True\"]', 'correct', 5, 5, '2021-02-27 17:15:39', '2021-02-27 17:15:39'),
(2223, 93, 29, '[\"True\"]', '[\"False\"]', 'incorrect', 0, 5, '2021-02-27 17:15:39', '2021-02-27 17:15:39'),
(2224, 94, 29, '[\"True\"]', '[\"True\"]', 'correct', 5, 5, '2021-02-27 17:15:39', '2021-02-27 17:15:39'),
(2225, 95, 29, '[\"two times\"]', '[\"three times\"]', 'incorrect', 0, 5, '2021-02-27 17:15:39', '2021-02-27 17:15:39'),
(2226, 1, 10, '[\"What\"]', '[\"What\"]', 'correct', 1, 1, '2021-02-28 16:09:53', '2021-02-28 16:09:53'),
(2227, 2, 10, '[\"They\'re\"]', '[\"They\'re\"]', 'correct', 1, 1, '2021-02-28 16:09:53', '2021-02-28 16:09:53'),
(2228, 3, 10, '[\"It\"]', '[\"It\"]', 'correct', 1, 1, '2021-02-28 16:09:53', '2021-02-28 16:09:53'),
(2229, 5, 10, '[\"are you spell\"]', '[\"do you spell\"]', 'incorrect', 0, 1, '2021-02-28 16:09:53', '2021-02-28 16:09:53'),
(2230, 6, 10, '[\"a\"]', '[\"--\"]', 'incorrect', 0, 1, '2021-02-28 16:09:53', '2021-02-28 16:09:53'),
(2231, 7, 10, '[\"Is Mr. Mike a teacher?\"]', '[\"Is Mr. Mike a teacher?\"]', 'correct', 1, 1, '2021-02-28 16:09:53', '2021-02-28 16:09:53'),
(2232, 8, 10, '[\"your father\'s job\"]', '[\"your father\'s job\"]', 'correct', 1, 1, '2021-02-28 16:09:53', '2021-02-28 16:09:53'),
(2233, 9, 10, '[\"doesn\'t has\"]', '[\"doesn\'t have\"]', 'incorrect', 0, 1, '2021-02-28 16:09:53', '2021-02-28 16:09:53'),
(2234, 10, 10, '[\"works\"]', '[\"works\"]', 'correct', 1, 1, '2021-02-28 16:09:53', '2021-02-28 16:09:53'),
(2235, 11, 10, '[\"We often do not go to the cinema.\"]', '[\"We do not often go to the cinema.\"]', 'incorrect', 0, 1, '2021-02-28 16:09:53', '2021-02-28 16:09:53'),
(2236, 12, 10, '[\"make\"]', '[\"do\"]', 'incorrect', 0, 1, '2021-02-28 16:09:53', '2021-02-28 16:09:53'),
(2237, 14, 10, '[\"ever\"]', '[\"ever\"]', 'correct', 1, 1, '2021-02-28 16:09:53', '2021-02-28 16:09:53'),
(2238, 18, 10, '[\"tallest\"]', '[\"taller\"]', 'incorrect', 0, 1, '2021-02-28 16:09:53', '2021-02-28 16:09:53'),
(2239, 19, 10, '[\"most\"]', '[\"most\"]', 'correct', 1, 1, '2021-02-28 16:09:53', '2021-02-28 16:09:53'),
(2240, 23, 10, '[\"more friendly\"]', '[\"friendlier\"]', 'incorrect', 0, 1, '2021-02-28 16:09:53', '2021-02-28 16:09:53'),
(2241, 28, 10, '[\"in\"]', '[\"on\"]', 'incorrect', 0, 1, '2021-02-28 16:09:53', '2021-02-28 16:09:53'),
(2242, 29, 10, '[\"for\"]', '[\"for\"]', 'correct', 1, 1, '2021-02-28 16:09:53', '2021-02-28 16:09:53'),
(2243, 33, 10, '[\"I don\'t went to the cinema\\u00a0 last night.\\u00a0\"]', '[\"I didn\'t go to the cinema\\u00a0 last night.\\u00a0\"]', 'incorrect', 0, 1, '2021-02-28 16:09:53', '2021-02-28 16:09:53'),
(2244, 34, 10, '[\"They don\'t have become very rich.\"]', '[\"They haven\'t become very rich.\"]', 'incorrect', 0, 1, '2021-02-28 16:09:53', '2021-02-28 16:09:53'),
(2245, 35, 10, '[\"I don\'t have a big window in my room.\"]', '[\"I don\'t have a big window in my room.\"]', 'correct', 1, 1, '2021-02-28 16:09:53', '2021-02-28 16:09:53'),
(2246, 36, 10, '[\"My mom hasn\'t perfect cooking skills.\"]', '[\"My mom doesn\'t have perfect cooking skills.\"]', 'incorrect', 0, 1, '2021-02-28 16:09:53', '2021-02-28 16:09:53'),
(2247, 37, 10, '[null,null,null,null,null,null,null,null,null]', '[\"ill\",\"quiet\",\"difficult\",\"beautiful\",\"crowded\",\"intelligent\",\"rich\",\"friendly\",\"interesting\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 9, '2021-02-28 16:09:53', '2021-02-28 16:09:53'),
(2248, 38, 10, '[null,null,null,null,null,null,null,null,null,null]', '[\"arrived\",\"want\",\"have been\",\"don\'t understand\",\"talk\",\"met\",\"came\",\"is doing\",\"needs\",\"is ringing\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 10, '2021-02-28 16:09:53', '2021-02-28 16:09:53'),
(2249, 42, 10, '[null,null,null,null,null,null,null]', '[\"24\",\"13.20\",\"18.45\",\"11\",\"5.15\",\"8.20\",\"259\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 7, '2021-02-28 16:09:54', '2021-02-28 16:09:54'),
(2250, 48, 10, '[null,null,null,null,null,null,null,null,null,null]', '[\"speaking\",\"this is\",\"really sorry\",\"have to\",\"Oh, dear\",\"did she\",\"don\'t worry\",\"could you help me\",\"shall I\",\"that would be\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 10, '2021-02-28 16:09:54', '2021-02-28 16:09:54'),
(2251, 49, 10, '[\"will accept\"]', '[\"would accept\"]', 'incorrect', 0, 3, '2021-02-28 16:09:54', '2021-02-28 16:09:54'),
(2252, 50, 10, '[\"pass\"]', '[\"will pass\"]', 'incorrect', 0, 3, '2021-02-28 16:09:54', '2021-02-28 16:09:54'),
(2253, 51, 10, '[\"see\"]', '[\"seeing\"]', 'incorrect', 0, 3, '2021-02-28 16:09:54', '2021-02-28 16:09:54'),
(2254, 53, 10, '[\"which\"]', '[\"which\"]', 'correct', 3, 3, '2021-02-28 16:09:54', '2021-02-28 16:09:54'),
(2255, 54, 10, '[\"where\"]', '[\"where\"]', 'correct', 3, 3, '2021-02-28 16:09:54', '2021-02-28 16:09:54'),
(2256, 55, 10, '[\"fired\"]', '[\"were fired\"]', 'incorrect', 0, 3, '2021-02-28 16:09:54', '2021-02-28 16:09:54'),
(2257, 56, 10, '[\"give up\"]', '[\"give up\"]', 'correct', 3, 3, '2021-02-28 16:09:54', '2021-02-28 16:09:54'),
(2258, 57, 10, '[\"go out with\"]', '[\"put up with\"]', 'incorrect', 0, 3, '2021-02-28 16:09:54', '2021-02-28 16:09:54'),
(2259, 58, 10, '[\"unpolite\"]', '[\"impolite\"]', 'incorrect', 0, 3, '2021-02-28 16:09:54', '2021-02-28 16:09:54'),
(2260, 67, 10, '[\"However\"]', '[\"Although\"]', 'incorrect', 0, 4, '2021-02-28 16:09:54', '2021-02-28 16:09:54'),
(2261, 68, 10, '[\"Although being tired\"]', '[\"Despite being tired\"]', 'incorrect', 0, 4, '2021-02-28 16:09:54', '2021-02-28 16:09:54'),
(2262, 69, 10, '[\"am used to work\"]', '[\"am used to working\"]', 'incorrect', 0, 4, '2021-02-28 16:09:54', '2021-02-28 16:09:54'),
(2263, 71, 10, '[\"forgot\"]', '[\"had forgotten\"]', 'incorrect', 0, 4, '2021-02-28 16:09:54', '2021-02-28 16:09:54'),
(2264, 72, 10, '[\"keep in touch with\"]', '[\"keep in touch with\"]', 'correct', 4, 4, '2021-02-28 16:09:54', '2021-02-28 16:09:54'),
(2265, 73, 10, '[\"splitted\"]', '[\"split\"]', 'incorrect', 0, 4, '2021-02-28 16:09:54', '2021-02-28 16:09:54'),
(2266, 74, 10, '[\"don\'t\"]', '[\"not to\"]', 'incorrect', 0, 4, '2021-02-28 16:09:54', '2021-02-28 16:09:54'),
(2267, 75, 10, '[\"care\",\"enjoyment\",\"disappoint\"]', '[\"care\",\"enjoyable\",\"disappointment\"]', '[\"correct\",\"incorrect\",\"incorrect\"]', 0, 6, '2021-02-28 16:09:54', '2021-02-28 16:09:54'),
(2268, 76, 10, '[\"in Australia\"]', '[\"at a party\"]', 'incorrect', 0, 4, '2021-02-28 16:09:54', '2021-02-28 16:09:54'),
(2269, 77, 10, '[\"lost her passport\"]', '[\"lost her passport\"]', 'correct', 4, 4, '2021-02-28 16:09:54', '2021-02-28 16:09:54'),
(2270, 78, 10, '[\"got into financial trouble\"]', '[\"lost her job\"]', 'incorrect', 0, 4, '2021-02-28 16:09:54', '2021-02-28 16:09:54'),
(2271, 79, 10, '[\"the country\"]', '[\"the country\"]', 'correct', 4, 4, '2021-02-28 16:09:54', '2021-02-28 16:09:54'),
(2272, 80, 10, '[\"an aggressive\"]', '[\"an aggressive\"]', 'correct', 4, 4, '2021-02-28 16:09:54', '2021-02-28 16:09:54'),
(2273, 81, 10, '[\"some boxing gloves\"]', '[\"a bike\"]', 'incorrect', 0, 4, '2021-02-28 16:09:54', '2021-02-28 16:09:54'),
(2274, 82, 10, '[null,null,\"burglarize\",\"vadalize\"]', '[\"looting\",\"robbery\",\"burgle\",\"vandalize\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 8, '2021-02-28 16:09:54', '2021-02-28 16:09:54'),
(2275, 83, 10, '[\"political\",\"economical\",\"environmental\",\"polluter\"]', '[\"politician\",\"economic\",\"environmental\",\"pollution\"]', '[\"incorrect\",\"incorrect\",\"correct\",\"incorrect\"]', 0, 8, '2021-02-28 16:09:54', '2021-02-28 16:09:54'),
(2276, 84, 10, '[\"fire someone form a job\"]', '[\"fire someone form a job\"]', 'correct', 4, 4, '2021-02-28 16:09:54', '2021-02-28 16:09:54'),
(2277, 85, 10, '[\"a lot of money\"]', '[\"a lot of money\"]', 'correct', 4, 4, '2021-02-28 16:09:54', '2021-02-28 16:09:54'),
(2278, 86, 10, '[\"have a difficulty in speaking\"]', '[\"have a difficulty in speaking\"]', 'correct', 4, 4, '2021-02-28 16:09:54', '2021-02-28 16:09:54'),
(2279, 87, 10, '[\"memorable\",\"overrated\",\"predictable\",\"gripping\",\"scary\",\"hilarious\"]', '[\"memorable\",\"scary\",\"overrated\",\"predictable\",\"gripping\",\"hilarious\"]', '[\"correct\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"correct\"]', 0, 12, '2021-02-28 16:09:54', '2021-02-28 16:09:54'),
(2280, 90, 10, '[\"released\",\"outcry\",\"spread\",\"damages\",\"crisis\",\"attack\",\"invaded\",\"flee\",null,\"troops\",null]', '[\"sued\",\"damages\",\"spread\",\"crisis\",\"hostage\",\"released\",\"invaded\",\"troops\",\"flee\",\"attack\",\"outcry\"]', '[\"incorrect\",\"incorrect\",\"correct\",\"incorrect\",\"incorrect\",\"incorrect\",\"correct\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 30, '2021-02-28 16:09:54', '2021-02-28 16:09:54'),
(2281, 91, 10, '[\"False\"]', '[\"False\"]', 'correct', 5, 5, '2021-02-28 16:09:54', '2021-02-28 16:09:54'),
(2282, 92, 10, '[\"True\"]', '[\"True\"]', 'correct', 5, 5, '2021-02-28 16:09:54', '2021-02-28 16:09:54'),
(2283, 93, 10, '[\"False\"]', '[\"False\"]', 'correct', 5, 5, '2021-02-28 16:09:54', '2021-02-28 16:09:54'),
(2284, 94, 10, '[\"True\"]', '[\"True\"]', 'correct', 5, 5, '2021-02-28 16:09:54', '2021-02-28 16:09:54'),
(2285, 95, 10, '[\"three times\"]', '[\"three times\"]', 'correct', 5, 5, '2021-02-28 16:09:54', '2021-02-28 16:09:54'),
(2286, 1, 20, '[\"What\"]', '[\"What\"]', 'correct', 1, 1, '2021-02-28 16:57:33', '2021-02-28 16:57:33'),
(2287, 2, 20, '[\"They\'re\"]', '[\"They\'re\"]', 'correct', 1, 1, '2021-02-28 16:57:33', '2021-02-28 16:57:33'),
(2288, 3, 20, '[\"It\"]', '[\"It\"]', 'correct', 1, 1, '2021-02-28 16:57:33', '2021-02-28 16:57:33'),
(2289, 5, 20, '[\"do you spell\"]', '[\"do you spell\"]', 'correct', 1, 1, '2021-02-28 16:57:33', '2021-02-28 16:57:33'),
(2290, 6, 20, '[\"--\"]', '[\"--\"]', 'correct', 1, 1, '2021-02-28 16:57:33', '2021-02-28 16:57:33'),
(2291, 7, 20, '[\"Is Mr. Mike a teacher?\"]', '[\"Is Mr. Mike a teacher?\"]', 'correct', 1, 1, '2021-02-28 16:57:33', '2021-02-28 16:57:33'),
(2292, 8, 20, '[\"your father\'s job\"]', '[\"your father\'s job\"]', 'correct', 1, 1, '2021-02-28 16:57:33', '2021-02-28 16:57:33'),
(2293, 9, 20, '[\"doesn\'t have\"]', '[\"doesn\'t have\"]', 'correct', 1, 1, '2021-02-28 16:57:33', '2021-02-28 16:57:33'),
(2294, 10, 20, '[\"works\"]', '[\"works\"]', 'correct', 1, 1, '2021-02-28 16:57:33', '2021-02-28 16:57:33'),
(2295, 11, 20, '[\"We do not go often to the cinema.\"]', '[\"We do not often go to the cinema.\"]', 'incorrect', 0, 1, '2021-02-28 16:57:33', '2021-02-28 16:57:33'),
(2296, 12, 20, '[\"do\"]', '[\"do\"]', 'correct', 1, 1, '2021-02-28 16:57:33', '2021-02-28 16:57:33'),
(2297, 14, 20, '[\"sometimes\"]', '[\"ever\"]', 'incorrect', 0, 1, '2021-02-28 16:57:33', '2021-02-28 16:57:33'),
(2298, 18, 20, '[\"taller\"]', '[\"taller\"]', 'correct', 1, 1, '2021-02-28 16:57:33', '2021-02-28 16:57:33'),
(2299, 19, 20, '[\"most\"]', '[\"most\"]', 'correct', 1, 1, '2021-02-28 16:57:33', '2021-02-28 16:57:33'),
(2300, 23, 20, '[\"more friendly\"]', '[\"friendlier\"]', 'incorrect', 0, 1, '2021-02-28 16:57:33', '2021-02-28 16:57:33'),
(2301, 28, 20, '[\"on\"]', '[\"on\"]', 'correct', 1, 1, '2021-02-28 16:57:33', '2021-02-28 16:57:33'),
(2302, 29, 20, '[\"for\"]', '[\"for\"]', 'correct', 1, 1, '2021-02-28 16:57:33', '2021-02-28 16:57:33'),
(2303, 30, 20, 'where does Sally\'s grandmother live?', '[]', '', 0, 1, '2021-02-28 16:57:33', '2021-02-28 16:57:33'),
(2304, 31, 20, 'how many children did tom have?', '[]', '', 0, 1, '2021-02-28 16:57:33', '2021-02-28 16:57:33'),
(2305, 32, 20, 'what time do they get up every morning?', '[]', '', 0, 1, '2021-02-28 16:57:33', '2021-02-28 16:57:33'),
(2306, 33, 20, '[\"I didn\'t go to the cinema\\u00a0 last night.\\u00a0\"]', '[\"I didn\'t go to the cinema\\u00a0 last night.\\u00a0\"]', 'correct', 1, 1, '2021-02-28 16:57:33', '2021-02-28 16:57:33'),
(2307, 34, 20, '[\"They haven\'t become very rich.\"]', '[\"They haven\'t become very rich.\"]', 'correct', 1, 1, '2021-02-28 16:57:33', '2021-02-28 16:57:33'),
(2308, 35, 20, '[\"I don\'t have a big window in my room.\"]', '[\"I don\'t have a big window in my room.\"]', 'correct', 1, 1, '2021-02-28 16:57:33', '2021-02-28 16:57:33'),
(2309, 36, 20, '[\"My mom doesn\'t have perfect cooking skills.\"]', '[\"My mom doesn\'t have perfect cooking skills.\"]', 'correct', 1, 1, '2021-02-28 16:57:33', '2021-02-28 16:57:33'),
(2310, 37, 20, '[\"ill\",\"quiet\",\"difficult\",\"beautiful\",\"crowded\",\"intelligent\",\"rich\",\"friendly\",\"interesting\"]', '[\"ill\",\"quiet\",\"difficult\",\"beautiful\",\"crowded\",\"intelligent\",\"rich\",\"friendly\",\"interesting\"]', '[\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\"]', 9, 9, '2021-02-28 16:57:33', '2021-02-28 16:57:33'),
(2311, 38, 20, '[\"arrived\",\"want\",\"have you ever gone\",\"do not understand\",\"are talking\",\"met\",\"came\",\"is doing\",\"needs\",\"is ringing\"]', '[\"arrived\",\"want\",\"have been\",\"don\'t understand\",\"talk\",\"met\",\"came\",\"is doing\",\"needs\",\"is ringing\"]', '[\"correct\",\"correct\",\"incorrect\",\"incorrect\",\"incorrect\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\"]', 0, 10, '2021-02-28 16:57:33', '2021-02-28 16:57:33'),
(2312, 42, 20, '[\"24 February Saturday\",\"13:20\",\"18:45\",\"Sunday 11March\",\"5:15 AM\",\"08: 20 AM\",\"259 pounds\"]', '[\"24\",\"13.20\",\"18.45\",\"11\",\"5.15\",\"8.20\",\"259\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 7, '2021-02-28 16:57:33', '2021-02-28 16:57:33'),
(2313, 48, 20, '[\"speaking\",\"this is\",\"really sorry\",\"have to go\",\"Oh, dear\",\"did she have injury?\",\"don\'t worry\",\"could you help me\",\"shall I\",\"that would be\"]', '[\"speaking\",\"this is\",\"really sorry\",\"have to\",\"Oh, dear\",\"did she\",\"don\'t worry\",\"could you help me\",\"shall I\",\"that would be\"]', '[\"correct\",\"correct\",\"correct\",\"incorrect\",\"correct\",\"incorrect\",\"correct\",\"correct\",\"correct\",\"correct\"]', 0, 10, '2021-02-28 16:57:33', '2021-02-28 16:57:33'),
(2314, 49, 20, '[\"would have accept\"]', '[\"would accept\"]', 'incorrect', 0, 3, '2021-02-28 16:57:33', '2021-02-28 16:57:33'),
(2315, 50, 20, '[\"will pass\"]', '[\"will pass\"]', 'correct', 3, 3, '2021-02-28 16:57:33', '2021-02-28 16:57:33'),
(2316, 51, 20, '[\"seeing\"]', '[\"seeing\"]', 'correct', 3, 3, '2021-02-28 16:57:33', '2021-02-28 16:57:33'),
(2317, 52, 20, '[\"wants -- next\"]', '[\"wanted -- the following\"]', 'incorrect', 0, 3, '2021-02-28 16:57:33', '2021-02-28 16:57:33'),
(2318, 53, 20, '[\"which\"]', '[\"which\"]', 'correct', 3, 3, '2021-02-28 16:57:33', '2021-02-28 16:57:33'),
(2319, 54, 20, '[\"where\"]', '[\"where\"]', 'correct', 3, 3, '2021-02-28 16:57:33', '2021-02-28 16:57:33'),
(2320, 55, 20, '[\"have been fired\"]', '[\"were fired\"]', 'incorrect', 0, 3, '2021-02-28 16:57:33', '2021-02-28 16:57:33'),
(2321, 56, 20, '[\"give up\"]', '[\"give up\"]', 'correct', 3, 3, '2021-02-28 16:57:33', '2021-02-28 16:57:33'),
(2322, 57, 20, '[\"put up with\"]', '[\"put up with\"]', 'correct', 3, 3, '2021-02-28 16:57:33', '2021-02-28 16:57:33'),
(2323, 58, 20, '[\"impolite\"]', '[\"impolite\"]', 'correct', 3, 3, '2021-02-28 16:57:33', '2021-02-28 16:57:33'),
(2324, 59, 20, '[\"niece\"]', '[\"niece\"]', 'correct', 3, 3, '2021-02-28 16:57:33', '2021-02-28 16:57:33'),
(2325, 60, 20, '[\"mother-in-law\"]', '[\"mother-in-law\"]', 'correct', 3, 3, '2021-02-28 16:57:33', '2021-02-28 16:57:33'),
(2326, 61, 20, '[\"17\"]', '[\"17\"]', 'correct', 2, 2, '2021-02-28 16:57:33', '2021-02-28 16:57:33'),
(2327, 62, 20, '[\"20\"]', '[\"20\"]', 'correct', 2, 2, '2021-02-28 16:57:33', '2021-02-28 16:57:33'),
(2328, 63, 20, '[\"$20\"]', '[\"$20\"]', 'correct', 2, 2, '2021-02-28 16:57:33', '2021-02-28 16:57:33'),
(2329, 64, 20, '[\"10\"]', '[\"10\"]', 'correct', 2, 2, '2021-02-28 16:57:34', '2021-02-28 16:57:34'),
(2330, 65, 20, '[\"on his body\"]', '[\"on his body\"]', 'correct', 2, 2, '2021-02-28 16:57:34', '2021-02-28 16:57:34'),
(2331, 66, 20, '[\"stomach problems\"]', '[\"stomach problems\"]', 'correct', 2, 2, '2021-02-28 16:57:34', '2021-02-28 16:57:34'),
(2332, 67, 20, '[\"Although\"]', '[\"Although\"]', 'correct', 4, 4, '2021-02-28 16:57:34', '2021-02-28 16:57:34'),
(2333, 68, 20, '[\"Although being tired\"]', '[\"Despite being tired\"]', 'incorrect', 0, 4, '2021-02-28 16:57:34', '2021-02-28 16:57:34'),
(2334, 69, 20, '[\"am used to working\"]', '[\"am used to working\"]', 'correct', 4, 4, '2021-02-28 16:57:34', '2021-02-28 16:57:34'),
(2335, 71, 20, '[\"had forgotten\"]', '[\"had forgotten\"]', 'correct', 4, 4, '2021-02-28 16:57:34', '2021-02-28 16:57:34'),
(2336, 72, 20, '[\"keep in touch with\"]', '[\"keep in touch with\"]', 'correct', 4, 4, '2021-02-28 16:57:34', '2021-02-28 16:57:34'),
(2337, 73, 20, '[\"have splitted\"]', '[\"split\"]', 'incorrect', 0, 4, '2021-02-28 16:57:34', '2021-02-28 16:57:34'),
(2338, 74, 20, '[\"not to\"]', '[\"not to\"]', 'correct', 4, 4, '2021-02-28 16:57:34', '2021-02-28 16:57:34'),
(2339, 75, 20, '[\"care\",\"enjoyable\",\"disappointment\"]', '[\"care\",\"enjoyable\",\"disappointment\"]', '[\"correct\",\"correct\",\"correct\"]', 6, 6, '2021-02-28 16:57:34', '2021-02-28 16:57:34'),
(2340, 76, 20, '[\"at a party\"]', '[\"at a party\"]', 'correct', 4, 4, '2021-02-28 16:57:34', '2021-02-28 16:57:34'),
(2341, 77, 20, '[\"lost her passport\"]', '[\"lost her passport\"]', 'correct', 4, 4, '2021-02-28 16:57:34', '2021-02-28 16:57:34'),
(2342, 78, 20, '[\"lost her job\"]', '[\"lost her job\"]', 'correct', 4, 4, '2021-02-28 16:57:34', '2021-02-28 16:57:34'),
(2343, 79, 20, '[\"the country\"]', '[\"the country\"]', 'correct', 4, 4, '2021-02-28 16:57:34', '2021-02-28 16:57:34'),
(2344, 80, 20, '[\"an aggressive\"]', '[\"an aggressive\"]', 'correct', 4, 4, '2021-02-28 16:57:34', '2021-02-28 16:57:34'),
(2345, 81, 20, '[\"a bike\"]', '[\"a bike\"]', 'correct', 4, 4, '2021-02-28 16:57:34', '2021-02-28 16:57:34'),
(2346, 82, 20, '[\"loot\",\"robbery\",\"burgle\",\"vandalize\"]', '[\"looting\",\"robbery\",\"burgle\",\"vandalize\"]', '[\"incorrect\",\"correct\",\"correct\",\"correct\"]', 0, 8, '2021-02-28 16:57:34', '2021-02-28 16:57:34'),
(2347, 83, 20, '[\"politician\",\"economical\",\"environmental\",\"pollution\"]', '[\"politician\",\"economic\",\"environmental\",\"pollution\"]', '[\"correct\",\"incorrect\",\"correct\",\"correct\"]', 0, 8, '2021-02-28 16:57:34', '2021-02-28 16:57:34'),
(2348, 84, 20, '[\"fire someone form a job\"]', '[\"fire someone form a job\"]', 'correct', 4, 4, '2021-02-28 16:57:34', '2021-02-28 16:57:34'),
(2349, 85, 20, '[\"a lot of money\"]', '[\"a lot of money\"]', 'correct', 4, 4, '2021-02-28 16:57:34', '2021-02-28 16:57:34'),
(2350, 86, 20, '[\"have a difficulty in speaking\"]', '[\"have a difficulty in speaking\"]', 'correct', 4, 4, '2021-02-28 16:57:34', '2021-02-28 16:57:34'),
(2351, 87, 20, '[\"memorable\",\"scary\",\"overrated\",\"predictable\",\"gripping\",\"hilarious\"]', '[\"memorable\",\"scary\",\"overrated\",\"predictable\",\"gripping\",\"hilarious\"]', '[\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\"]', 12, 12, '2021-02-28 16:57:34', '2021-02-28 16:57:34'),
(2352, 90, 20, '[\"sued\",null,\"spread\",\"crisis\",\"hostage\",\"released\",\"invaded\",\"troops\",\"flee\",\"attack\",null]', '[\"sued\",\"damages\",\"spread\",\"crisis\",\"hostage\",\"released\",\"invaded\",\"troops\",\"flee\",\"attack\",\"outcry\"]', '[\"correct\",\"incorrect\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"incorrect\"]', 0, 30, '2021-02-28 16:57:34', '2021-02-28 16:57:34'),
(2353, 91, 20, '[\"False\"]', '[\"False\"]', 'correct', 5, 5, '2021-02-28 16:57:34', '2021-02-28 16:57:34'),
(2354, 92, 20, '[\"False\"]', '[\"True\"]', 'incorrect', 0, 5, '2021-02-28 16:57:34', '2021-02-28 16:57:34'),
(2355, 93, 20, '[\"True\"]', '[\"False\"]', 'incorrect', 0, 5, '2021-02-28 16:57:34', '2021-02-28 16:57:34'),
(2356, 94, 20, '[\"True\"]', '[\"True\"]', 'correct', 5, 5, '2021-02-28 16:57:34', '2021-02-28 16:57:34'),
(2357, 95, 20, '[\"three times\"]', '[\"three times\"]', 'correct', 5, 5, '2021-02-28 16:57:34', '2021-02-28 16:57:34');
INSERT INTO `employee_quizzes_old` (`id`, `quiz_id`, `employee_id`, `emp_answer`, `correct_answer`, `check_answer`, `emp_score`, `score`, `created_at`, `updated_at`) VALUES
(2358, 96, 20, 'Dear Sir,\r\n\r\nRegarding company policy and working conditions, kindly find my opinion below :\r\n\r\nBenefits :\r\nIt focuses on  discipline .\r\nIt encourages commitment and taking responsibility.\r\nIt encourages sharing and discussing ideas.\r\nIt helps employees to work as a team.\r\nIt support employees to develop their skills by trainings and workshops.\r\n\r\nDrawbacks :\r\nTaking responsibility some times leads to mistakes but on the other hand employees are learning from their mistakes.\r\nFor sharing and discussing ideas we have many meetings ( meetings should be less ).\r\n\r\nI hope  my opinion will be perifacial for the company.\r\n\r\nthanks and best regards', '[]', '', 0, 20, '2021-02-28 16:57:34', '2021-02-28 16:57:34'),
(2359, 1, 24, '[\"What\"]', '[\"What\"]', 'correct', 1, 1, '2021-03-01 02:50:45', '2021-03-01 02:50:45'),
(2360, 2, 24, '[\"They\'re\"]', '[\"They\'re\"]', 'correct', 1, 1, '2021-03-01 02:50:45', '2021-03-01 02:50:45'),
(2361, 3, 24, '[\"It\"]', '[\"It\"]', 'correct', 1, 1, '2021-03-01 02:50:45', '2021-03-01 02:50:45'),
(2362, 5, 24, '[\"do you spell\"]', '[\"do you spell\"]', 'correct', 1, 1, '2021-03-01 02:50:45', '2021-03-01 02:50:45'),
(2363, 6, 24, '[\"a\"]', '[\"--\"]', 'incorrect', 0, 1, '2021-03-01 02:50:45', '2021-03-01 02:50:45'),
(2364, 7, 24, '[\"Is Mr. Mike a teacher?\"]', '[\"Is Mr. Mike a teacher?\"]', 'correct', 1, 1, '2021-03-01 02:50:45', '2021-03-01 02:50:45'),
(2365, 8, 24, '[\"your father\'s job\"]', '[\"your father\'s job\"]', 'correct', 1, 1, '2021-03-01 02:50:45', '2021-03-01 02:50:45'),
(2366, 9, 24, '[\"doesn\'t have\"]', '[\"doesn\'t have\"]', 'correct', 1, 1, '2021-03-01 02:50:45', '2021-03-01 02:50:45'),
(2367, 10, 24, '[\"works\"]', '[\"works\"]', 'correct', 1, 1, '2021-03-01 02:50:45', '2021-03-01 02:50:45'),
(2368, 11, 24, '[\"We often do not go to the cinema.\"]', '[\"We do not often go to the cinema.\"]', 'incorrect', 0, 1, '2021-03-01 02:50:45', '2021-03-01 02:50:45'),
(2369, 12, 24, '[\"do\"]', '[\"do\"]', 'correct', 1, 1, '2021-03-01 02:50:45', '2021-03-01 02:50:45'),
(2370, 14, 24, '[\"sometimes\"]', '[\"ever\"]', 'incorrect', 0, 1, '2021-03-01 02:50:45', '2021-03-01 02:50:45'),
(2371, 18, 24, '[\"taller\"]', '[\"taller\"]', 'correct', 1, 1, '2021-03-01 02:50:45', '2021-03-01 02:50:45'),
(2372, 19, 24, '[\"more\"]', '[\"most\"]', 'incorrect', 0, 1, '2021-03-01 02:50:45', '2021-03-01 02:50:45'),
(2373, 23, 24, '[\"more friendly\"]', '[\"friendlier\"]', 'incorrect', 0, 1, '2021-03-01 02:50:45', '2021-03-01 02:50:45'),
(2374, 28, 24, '[\"on\"]', '[\"on\"]', 'correct', 1, 1, '2021-03-01 02:50:45', '2021-03-01 02:50:45'),
(2375, 29, 24, '[\"for\"]', '[\"for\"]', 'correct', 1, 1, '2021-03-01 02:50:45', '2021-03-01 02:50:45'),
(2376, 30, 24, 'Where does Sally\'s grandmother live ?', '[]', '', 0, 1, '2021-03-01 02:50:45', '2021-03-01 02:50:45'),
(2377, 31, 24, 'How many children does Tom had?', '[]', '', 0, 1, '2021-03-01 02:50:45', '2021-03-01 02:50:45'),
(2378, 32, 24, '?when do they weak up', '[]', '', 0, 1, '2021-03-01 02:50:45', '2021-03-01 02:50:45'),
(2379, 33, 24, '[\"I didn\'t go to the cinema\\u00a0 last night.\\u00a0\"]', '[\"I didn\'t go to the cinema\\u00a0 last night.\\u00a0\"]', 'correct', 1, 1, '2021-03-01 02:50:45', '2021-03-01 02:50:45'),
(2380, 34, 24, '[\"They don\'t have become very rich.\"]', '[\"They haven\'t become very rich.\"]', 'incorrect', 0, 1, '2021-03-01 02:50:45', '2021-03-01 02:50:45'),
(2381, 35, 24, '[\"I don\'t have a big window in my room.\"]', '[\"I don\'t have a big window in my room.\"]', 'correct', 1, 1, '2021-03-01 02:50:45', '2021-03-01 02:50:45'),
(2382, 36, 24, '[\"My mom doesn\'t  has perfect cooking skills.\"]', '[\"My mom doesn\'t have perfect cooking skills.\"]', 'incorrect', 0, 1, '2021-03-01 02:50:45', '2021-03-01 02:50:45'),
(2383, 37, 24, '[\"ill\",\"quiet\",\"difficult\",\"interesting\",\"crowded\",\"intelligent\",\"rich\",\"beautiful\",\"friendly\"]', '[\"ill\",\"quiet\",\"difficult\",\"beautiful\",\"crowded\",\"intelligent\",\"rich\",\"friendly\",\"interesting\"]', '[\"correct\",\"correct\",\"correct\",\"incorrect\",\"correct\",\"correct\",\"correct\",\"incorrect\",\"incorrect\"]', 0, 9, '2021-03-01 02:50:45', '2021-03-01 02:50:45'),
(2384, 38, 24, '[\"Arrived\",\"wanted\",\"did \\/ go\",\"did\",null,null,null,null,null,null]', '[\"arrived\",\"want\",\"have been\",\"don\'t understand\",\"talk\",\"met\",\"came\",\"is doing\",\"needs\",\"is ringing\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 10, '2021-03-01 02:50:45', '2021-03-01 02:50:45'),
(2385, 42, 24, '[null,null,null,null,null,null,null]', '[\"24\",\"13.20\",\"18.45\",\"11\",\"5.15\",\"8.20\",\"259\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 7, '2021-03-01 02:50:45', '2021-03-01 02:50:45'),
(2386, 48, 24, '[null,null,null,null,null,null,null,null,null,null]', '[\"speaking\",\"this is\",\"really sorry\",\"have to\",\"Oh, dear\",\"did she\",\"don\'t worry\",\"could you help me\",\"shall I\",\"that would be\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 10, '2021-03-01 02:50:45', '2021-03-01 02:50:45'),
(2387, 75, 24, '[null,null,null]', '[\"care\",\"enjoyable\",\"disappointment\"]', '[\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 6, '2021-03-01 02:50:45', '2021-03-01 02:50:45'),
(2388, 82, 24, '[null,null,null,null]', '[\"looting\",\"robbery\",\"burgle\",\"vandalize\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 8, '2021-03-01 02:50:45', '2021-03-01 02:50:45'),
(2389, 83, 24, '[null,null,null,null]', '[\"politician\",\"economic\",\"environmental\",\"pollution\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 8, '2021-03-01 02:50:45', '2021-03-01 02:50:45'),
(2390, 87, 24, '[null,null,null,null,null,null]', '[\"memorable\",\"scary\",\"overrated\",\"predictable\",\"gripping\",\"hilarious\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 12, '2021-03-01 02:50:45', '2021-03-01 02:50:45'),
(2391, 90, 24, '[null,null,null,null,null,null,null,null,null,null,null]', '[\"sued\",\"damages\",\"spread\",\"crisis\",\"hostage\",\"released\",\"invaded\",\"troops\",\"flee\",\"attack\",\"outcry\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 30, '2021-03-01 02:50:45', '2021-03-01 02:50:45'),
(2392, 1, 14, '[\"What\"]', '[\"What\"]', 'correct', 1, 1, '2021-03-02 00:48:27', '2021-03-02 00:48:27'),
(2393, 2, 14, '[\"She\'s\"]', '[\"They\'re\"]', 'incorrect', 0, 1, '2021-03-02 00:48:27', '2021-03-02 00:48:27'),
(2394, 3, 14, '[\"It\"]', '[\"It\"]', 'correct', 1, 1, '2021-03-02 00:48:27', '2021-03-02 00:48:27'),
(2395, 5, 14, '[\"do you spell\"]', '[\"do you spell\"]', 'correct', 1, 1, '2021-03-02 00:48:27', '2021-03-02 00:48:27'),
(2396, 6, 14, '[\"a\"]', '[\"--\"]', 'incorrect', 0, 1, '2021-03-02 00:48:27', '2021-03-02 00:48:27'),
(2397, 7, 14, '[\"Is Mr. Mike a teacher?\"]', '[\"Is Mr. Mike a teacher?\"]', 'correct', 1, 1, '2021-03-02 00:48:27', '2021-03-02 00:48:27'),
(2398, 8, 14, '[\"your father job\"]', '[\"your father\'s job\"]', 'incorrect', 0, 1, '2021-03-02 00:48:27', '2021-03-02 00:48:27'),
(2399, 9, 14, '[\"doesn\'t has\"]', '[\"doesn\'t have\"]', 'incorrect', 0, 1, '2021-03-02 00:48:27', '2021-03-02 00:48:27'),
(2400, 10, 14, '[\"works\"]', '[\"works\"]', 'correct', 1, 1, '2021-03-02 00:48:27', '2021-03-02 00:48:27'),
(2401, 11, 14, '[\"We often do not go to the cinema.\"]', '[\"We do not often go to the cinema.\"]', 'incorrect', 0, 1, '2021-03-02 00:48:27', '2021-03-02 00:48:27'),
(2402, 12, 14, '[\"do\"]', '[\"do\"]', 'correct', 1, 1, '2021-03-02 00:48:27', '2021-03-02 00:48:27'),
(2403, 14, 14, '[\"sometimes\"]', '[\"ever\"]', 'incorrect', 0, 1, '2021-03-02 00:48:27', '2021-03-02 00:48:27'),
(2404, 18, 14, '[\"taller\"]', '[\"taller\"]', 'correct', 1, 1, '2021-03-02 00:48:27', '2021-03-02 00:48:27'),
(2405, 19, 14, '[\"most\"]', '[\"most\"]', 'correct', 1, 1, '2021-03-02 00:48:27', '2021-03-02 00:48:27'),
(2406, 23, 14, '[\"friendlier\"]', '[\"friendlier\"]', 'correct', 1, 1, '2021-03-02 00:48:27', '2021-03-02 00:48:27'),
(2407, 28, 14, '[\"in\"]', '[\"on\"]', 'incorrect', 0, 1, '2021-03-02 00:48:27', '2021-03-02 00:48:27'),
(2408, 29, 14, '[\"for\"]', '[\"for\"]', 'correct', 1, 1, '2021-03-02 00:48:27', '2021-03-02 00:48:27'),
(2409, 30, 14, 'where is Sally grandmother lives in ?', '[]', '', 0, 1, '2021-03-02 00:48:27', '2021-03-02 00:48:27'),
(2410, 31, 14, 'How many children  Tom have ?', '[]', '', 0, 1, '2021-03-02 00:48:27', '2021-03-02 00:48:27'),
(2411, 32, 14, 'when do they wake up every morning ?', '[]', '', 0, 1, '2021-03-02 00:48:27', '2021-03-02 00:48:27'),
(2412, 33, 14, '[\"I didn\'t go to the cinema\\u00a0 last night.\\u00a0\"]', '[\"I didn\'t go to the cinema\\u00a0 last night.\\u00a0\"]', 'correct', 1, 1, '2021-03-02 00:48:28', '2021-03-02 00:48:28'),
(2413, 34, 14, '[\"They haven\'t become very rich.\"]', '[\"They haven\'t become very rich.\"]', 'correct', 1, 1, '2021-03-02 00:48:28', '2021-03-02 00:48:28'),
(2414, 35, 14, '[\"I don\'t have a big window in my room.\"]', '[\"I don\'t have a big window in my room.\"]', 'correct', 1, 1, '2021-03-02 00:48:28', '2021-03-02 00:48:28'),
(2415, 36, 14, '[\"My mom doesn\'t  has perfect cooking skills.\"]', '[\"My mom doesn\'t have perfect cooking skills.\"]', 'incorrect', 0, 1, '2021-03-02 00:48:28', '2021-03-02 00:48:28'),
(2416, 37, 14, '[\"ill\",\"quiet\",\"difficult\",\"beautiful\",\"crowded\",\"intelligent\",\"rich\",\"friendly\",\"interesting\"]', '[\"ill\",\"quiet\",\"difficult\",\"beautiful\",\"crowded\",\"intelligent\",\"rich\",\"friendly\",\"interesting\"]', '[\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\"]', 9, 9, '2021-03-02 00:48:28', '2021-03-02 00:48:28'),
(2417, 38, 14, '[\"arrived\",\"wanted\",\"have you ever been\",\"i am not understanding\",\"are talking\",\"met\",\"came\",\"was doing\",\"needs\",\"is ringing\"]', '[\"arrived\",\"want\",\"have been\",\"don\'t understand\",\"talk\",\"met\",\"came\",\"is doing\",\"needs\",\"is ringing\"]', '[\"correct\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"correct\",\"correct\",\"incorrect\",\"correct\",\"correct\"]', 0, 10, '2021-03-02 00:48:28', '2021-03-02 00:48:28'),
(2418, 42, 14, '[\"24\",\"13.2\",\"18.45\",\"11\",\"5.15\",\"8.2\",\"259\"]', '[\"24\",\"13.20\",\"18.45\",\"11\",\"5.15\",\"8.20\",\"259\"]', '[\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\"]', 7, 7, '2021-03-02 00:48:28', '2021-03-02 00:48:28'),
(2419, 48, 14, '[\"speaking\",\"this is\",\"really sorry\",\"have to\",\"Oh, dear\",\"really sorry\",\"don\'t worry\",\"could you help me\",\"shall I\",\"that would be\"]', '[\"speaking\",\"this is\",\"really sorry\",\"have to\",\"Oh, dear\",\"did she\",\"don\'t worry\",\"could you help me\",\"shall I\",\"that would be\"]', '[\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"incorrect\",\"correct\",\"correct\",\"correct\",\"correct\"]', 0, 10, '2021-03-02 00:48:28', '2021-03-02 00:48:28'),
(2420, 49, 14, '[\"would accept\"]', '[\"would accept\"]', 'correct', 3, 3, '2021-03-02 00:48:28', '2021-03-02 00:48:28'),
(2421, 50, 14, '[\"will pass\"]', '[\"will pass\"]', 'correct', 3, 3, '2021-03-02 00:48:28', '2021-03-02 00:48:28'),
(2422, 51, 14, '[\"see\"]', '[\"seeing\"]', 'incorrect', 0, 3, '2021-03-02 00:48:28', '2021-03-02 00:48:28'),
(2423, 52, 14, '[\"wants -- next\"]', '[\"wanted -- the following\"]', 'incorrect', 0, 3, '2021-03-02 00:48:28', '2021-03-02 00:48:28'),
(2424, 53, 14, '[\"which\"]', '[\"which\"]', 'correct', 3, 3, '2021-03-02 00:48:28', '2021-03-02 00:48:28'),
(2425, 54, 14, '[\"where\"]', '[\"where\"]', 'correct', 3, 3, '2021-03-02 00:48:28', '2021-03-02 00:48:28'),
(2426, 55, 14, '[\"have been fired\"]', '[\"were fired\"]', 'incorrect', 0, 3, '2021-03-02 00:48:28', '2021-03-02 00:48:28'),
(2427, 56, 14, '[\"give up\"]', '[\"give up\"]', 'correct', 3, 3, '2021-03-02 00:48:28', '2021-03-02 00:48:28'),
(2428, 57, 14, '[\"go out with\"]', '[\"put up with\"]', 'incorrect', 0, 3, '2021-03-02 00:48:28', '2021-03-02 00:48:28'),
(2429, 58, 14, '[\"unpolite\"]', '[\"impolite\"]', 'incorrect', 0, 3, '2021-03-02 00:48:28', '2021-03-02 00:48:28'),
(2430, 59, 14, '[\"nephew\"]', '[\"niece\"]', 'incorrect', 0, 3, '2021-03-02 00:48:28', '2021-03-02 00:48:28'),
(2431, 60, 14, '[\"mother-in-law\"]', '[\"mother-in-law\"]', 'correct', 3, 3, '2021-03-02 00:48:28', '2021-03-02 00:48:28'),
(2432, 61, 14, '[\"17\"]', '[\"17\"]', 'correct', 2, 2, '2021-03-02 00:48:28', '2021-03-02 00:48:28'),
(2433, 62, 14, '[\"20\"]', '[\"20\"]', 'correct', 2, 2, '2021-03-02 00:48:28', '2021-03-02 00:48:28'),
(2434, 63, 14, '[\"$20\"]', '[\"$20\"]', 'correct', 2, 2, '2021-03-02 00:48:28', '2021-03-02 00:48:28'),
(2435, 64, 14, '[\"10\"]', '[\"10\"]', 'correct', 2, 2, '2021-03-02 00:48:28', '2021-03-02 00:48:28'),
(2436, 65, 14, '[\"in the water tank\"]', '[\"on his body\"]', 'incorrect', 0, 2, '2021-03-02 00:48:28', '2021-03-02 00:48:28'),
(2437, 66, 14, '[\"stomach problems\"]', '[\"stomach problems\"]', 'correct', 2, 2, '2021-03-02 00:48:28', '2021-03-02 00:48:28'),
(2438, 67, 14, '[\"Although\"]', '[\"Although\"]', 'correct', 4, 4, '2021-03-02 00:48:28', '2021-03-02 00:48:28'),
(2439, 68, 14, '[\"Despite being tired\"]', '[\"Despite being tired\"]', 'correct', 4, 4, '2021-03-02 00:48:28', '2021-03-02 00:48:28'),
(2440, 69, 14, '[\"used to work\"]', '[\"am used to working\"]', 'incorrect', 0, 4, '2021-03-02 00:48:28', '2021-03-02 00:48:28'),
(2441, 71, 14, '[\"forgot\"]', '[\"had forgotten\"]', 'incorrect', 0, 4, '2021-03-02 00:48:28', '2021-03-02 00:48:28'),
(2442, 72, 14, '[\"keep in touch with\"]', '[\"keep in touch with\"]', 'correct', 4, 4, '2021-03-02 00:48:28', '2021-03-02 00:48:28'),
(2443, 73, 14, '[\"have splitted\"]', '[\"split\"]', 'incorrect', 0, 4, '2021-03-02 00:48:28', '2021-03-02 00:48:28'),
(2444, 74, 14, '[\"not to\"]', '[\"not to\"]', 'correct', 4, 4, '2021-03-02 00:48:28', '2021-03-02 00:48:28'),
(2445, 75, 14, '[\"caring\",\"interesting\",\"disappointment\"]', '[\"care\",\"enjoyable\",\"disappointment\"]', '[\"incorrect\",\"incorrect\",\"correct\"]', 0, 6, '2021-03-02 00:48:28', '2021-03-02 00:48:28'),
(2446, 76, 14, '[\"at a party\"]', '[\"at a party\"]', 'correct', 4, 4, '2021-03-02 00:48:28', '2021-03-02 00:48:28'),
(2447, 77, 14, '[\"lost her passport\"]', '[\"lost her passport\"]', 'correct', 4, 4, '2021-03-02 00:48:28', '2021-03-02 00:48:28'),
(2448, 78, 14, '[\"lost her job\"]', '[\"lost her job\"]', 'correct', 4, 4, '2021-03-02 00:48:28', '2021-03-02 00:48:28'),
(2449, 79, 14, '[\"London\"]', '[\"the country\"]', 'incorrect', 0, 4, '2021-03-02 00:48:28', '2021-03-02 00:48:28'),
(2450, 80, 14, '[\"an aggressive\"]', '[\"an aggressive\"]', 'correct', 4, 4, '2021-03-02 00:48:28', '2021-03-02 00:48:28'),
(2451, 81, 14, '[\"some boxing gloves\"]', '[\"a bike\"]', 'incorrect', 0, 4, '2021-03-02 00:48:28', '2021-03-02 00:48:28'),
(2452, 82, 14, '[\"loot\",\"rob\",\"thief\",null]', '[\"looting\",\"robbery\",\"burgle\",\"vandalize\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 8, '2021-03-02 00:48:28', '2021-03-02 00:48:28'),
(2453, 83, 14, '[\"politician\",\"economical\",\"environmental\",\"pollution\"]', '[\"politician\",\"economic\",\"environmental\",\"pollution\"]', '[\"correct\",\"incorrect\",\"correct\",\"correct\"]', 0, 8, '2021-03-02 00:48:28', '2021-03-02 00:48:28'),
(2454, 84, 14, '[\"give someone a job\"]', '[\"fire someone form a job\"]', 'incorrect', 0, 4, '2021-03-02 00:48:28', '2021-03-02 00:48:28'),
(2455, 85, 14, '[\"strong\"]', '[\"a lot of money\"]', 'incorrect', 0, 4, '2021-03-02 00:48:28', '2021-03-02 00:48:28'),
(2456, 86, 14, '[\"have a difficulty in speaking\"]', '[\"have a difficulty in speaking\"]', 'correct', 4, 4, '2021-03-02 00:48:28', '2021-03-02 00:48:28'),
(2457, 87, 14, '[\"memorable\",\"scary\",\"overrated\",\"predictable\",\"gripping\",\"hilarious\"]', '[\"memorable\",\"scary\",\"overrated\",\"predictable\",\"gripping\",\"hilarious\"]', '[\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\"]', 12, 12, '2021-03-02 00:48:28', '2021-03-02 00:48:28'),
(2458, 90, 14, '[null,null,null,\"spread\",null,null,null,null,null,null,null]', '[\"sued\",\"damages\",\"spread\",\"crisis\",\"hostage\",\"released\",\"invaded\",\"troops\",\"flee\",\"attack\",\"outcry\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 30, '2021-03-02 00:48:28', '2021-03-02 00:48:28'),
(2459, 91, 14, '[\"True\"]', '[\"False\"]', 'incorrect', 0, 5, '2021-03-02 00:48:28', '2021-03-02 00:48:28'),
(2460, 92, 14, '[\"True\"]', '[\"True\"]', 'correct', 5, 5, '2021-03-02 00:48:28', '2021-03-02 00:48:28'),
(2461, 93, 14, '[\"False\"]', '[\"False\"]', 'correct', 5, 5, '2021-03-02 00:48:28', '2021-03-02 00:48:28'),
(2462, 94, 14, '[\"True\"]', '[\"True\"]', 'correct', 5, 5, '2021-03-02 00:48:28', '2021-03-02 00:48:28'),
(2463, 95, 14, '[\"three times\"]', '[\"three times\"]', 'correct', 5, 5, '2021-03-02 00:48:28', '2021-03-02 00:48:28'),
(2464, 96, 14, 'Dear Shawky,\r\nI hope you are doing well\r\nrefer to the b.m topic , first of all  I would like to say i am really proud to work at Sysmex  for many reasons , but also please accept my suggestions to improve the work environment for healthy life  such as :\r\n1- regular (monthly or biweekly meeting ) within same dep. and with other deps.\r\n2- speak up quarterly meetings  to allow all employees express about their thoughts \r\n3- other points i do prefer to discuss it with you FTF \r\n\r\nB.R\r\nAkram', '[]', '', 0, 20, '2021-03-02 00:48:28', '2021-03-02 00:48:28');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `grades`
--

CREATE TABLE `grades` (
  `id` int(11) NOT NULL,
  `title_ar` varchar(255) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `active` int(11) NOT NULL DEFAULT 1,
  `sort` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `grades`
--

INSERT INTO `grades` (`id`, `title_ar`, `title`, `active`, `sort`) VALUES
(1, '', 'G6', 1, 6),
(2, '', 'G7', 1, 7),
(3, '', 'G8', 1, 8),
(4, '', 'G9', 1, 9),
(5, '', 'G10', 1, 10),
(6, '', 'G11', 1, 11),
(7, '', 'G12', 1, 12),
(8, NULL, 'G1', 1, 1),
(9, NULL, 'G2', 1, 2),
(10, NULL, 'G3', 1, 3),
(11, NULL, 'G4', 1, 4),
(12, NULL, 'G5', 1, 5);

-- --------------------------------------------------------

--
-- Table structure for table `lessons`
--

CREATE TABLE `lessons` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `unit_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `alies` varchar(255) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lessons`
--

INSERT INTO `lessons` (`id`, `unit_id`, `title`, `alies`, `active`, `created_at`, `updated_at`) VALUES
(1, 1, 'Lesson1: Rogue Wave', 'grade7/unit/rogue-wave/get-ready', 1, NULL, NULL),
(2, 1, 'Lesson2: The Flight of Icarus', 'grade7/unit/lesson2--the-flight-of-icarus/read', 1, NULL, '2023-04-14 03:48:38'),
(3, 1, 'Lesson3: Icarus\'s Flight', 'grade7/unit/icarus-flight/get-ready', 1, NULL, NULL),
(4, 1, 'Lesson4: Women in Aviation', 'grade7/unit/women-in-aviation/get-ready', 1, NULL, NULL),
(5, 1, 'Lesson5: Thank You, M\'am/ A Police Stop Changed This Teenager\'s Life', 'grade7/unit/thank-you-mam/get-ready', 1, NULL, NULL),
(6, 1, 'Lesson6: A Police Stop Changed This Teenager\'s Life', 'grade7/unit/police-teenager/get-ready', 1, NULL, NULL),
(10, 2, 'Lesson 1: The Brave Little Toaster', 'grade8/unit/brave-little-toaster/get-ready', 1, NULL, NULL),
(11, 2, 'Lesson 2: Are Bionic Superhumans on the Horizon?', 'grade8/unit/bionic-superhumans/get-ready', 1, NULL, NULL),
(12, 2, 'Lesson 3: Interflora', 'grade8/unit/Interflora/get-ready', 1, NULL, NULL),
(13, 2, 'Lesson 4: The Automation Paradox/Heads Up, Humans', 'grade8/unit/automation-paradox/get-ready', 1, NULL, NULL),
(14, 2, 'Lesson 5: Heads Up, Humans', 'grade8/unit/Heads-up-humans/get-ready', 1, NULL, NULL),
(15, 3, 'Lesson 1: A Quilt of a Country', 'grade9/unit/quilt-of-country/get-ready', 1, NULL, NULL),
(47, 4, 'Lesson1:Test Lesson', 'grade7/unit/lesson1-test-lesson/read', 1, '2023-04-10 00:10:42', '2023-04-12 02:46:04'),
(48, 4, 'Lesson2', 'grade7/unit/lesson2/read', 1, '2023-04-10 00:10:42', '2023-04-10 00:10:42'),
(49, 4, 'Lesson3', 'grade7/unit/lesson3/read', 1, '2023-04-10 00:10:42', '2023-04-10 00:10:42'),
(50, 4, 'Lesson4', 'grade7/unit/lesson4/read', 1, '2023-04-10 00:10:42', '2023-04-10 00:10:42'),
(51, 4, 'Lesson5', 'grade7/unit/lesson5/read', 1, '2023-04-10 00:10:42', '2023-04-10 00:10:42'),
(52, 4, 'Lesson6', 'grade7/unit/lesson6/read', 1, '2023-04-10 00:10:42', '2023-04-10 00:10:42'),
(53, 5, 'Lesson1', 'grade7/unit/lesson1/read', 1, '2023-04-10 00:10:42', '2023-04-10 00:10:42'),
(54, 5, 'Lesson2', 'grade7/unit/lesson2/read', 1, '2023-04-10 00:10:42', '2023-04-10 00:10:42'),
(55, 5, 'Lesson3', 'grade7/unit/lesson3/read', 1, '2023-04-10 00:10:42', '2023-04-10 00:10:42'),
(56, 5, 'Lesson4', 'grade7/unit/lesson4/read', 1, '2023-04-10 00:10:42', '2023-04-10 00:10:42'),
(57, 5, 'Lesson5', 'grade7/unit/lesson5/read', 1, '2023-04-10 00:10:42', '2023-04-10 00:10:42'),
(58, 5, 'Lesson6', 'grade7/unit/lesson6/read', 1, '2023-04-10 00:10:42', '2023-04-10 00:10:42'),
(59, 6, 'Lesson1', 'grade7/unit/lesson1/read', 1, '2023-04-10 00:10:42', '2023-04-10 00:10:42'),
(60, 6, 'Lesson2', 'grade7/unit/lesson2/read', 1, '2023-04-10 00:10:42', '2023-04-10 00:10:42'),
(61, 6, 'Lesson3', 'grade7/unit/lesson3/read', 1, '2023-04-10 00:10:42', '2023-04-10 00:10:42'),
(62, 6, 'Lesson4', 'grade7/unit/lesson4/read', 1, '2023-04-10 00:10:42', '2023-04-10 00:10:42'),
(63, 6, 'Lesson5', 'grade7/unit/lesson5/read', 1, '2023-04-10 00:10:42', '2023-04-10 00:10:42'),
(64, 6, 'Lesson6', 'grade7/unit/lesson6/read', 1, '2023-04-10 00:10:42', '2023-04-10 00:10:42'),
(65, 7, 'Lesson1', 'grade7/unit/lesson1/read', 1, '2023-04-10 00:10:42', '2023-04-10 00:10:42'),
(66, 7, 'Lesson2', 'grade7/unit/lesson2/read', 1, '2023-04-10 00:10:42', '2023-04-10 00:10:42'),
(67, 7, 'Lesson3', 'grade7/unit/lesson3/read', 1, '2023-04-10 00:10:42', '2023-04-10 00:10:42'),
(68, 7, 'Lesson4', 'grade7/unit/lesson4/read', 1, '2023-04-10 00:10:42', '2023-04-10 00:10:42'),
(69, 7, 'Lesson5', 'grade7/unit/lesson5/read', 1, '2023-04-10 00:10:42', '2023-04-10 00:10:42'),
(70, 7, 'Lesson6', 'grade7/unit/lesson6/read', 1, '2023-04-10 00:10:42', '2023-04-10 00:10:42'),
(71, 8, 'Lesson1', 'grade7/unit/lesson1/read', 1, '2023-04-10 00:10:42', '2023-04-10 00:10:42'),
(72, 8, 'Lesson2', 'grade7/unit/lesson2/read', 1, '2023-04-10 00:10:42', '2023-04-10 00:10:42'),
(73, 8, 'Lesson3', 'grade7/unit/lesson3/read', 1, '2023-04-10 00:10:42', '2023-04-10 00:10:42'),
(74, 8, 'Lesson4', 'grade7/unit/lesson4/read', 1, '2023-04-10 00:10:42', '2023-04-10 00:10:42'),
(75, 8, 'Lesson5', 'grade7/unit/lesson5/read', 1, '2023-04-10 00:10:42', '2023-04-10 00:10:42'),
(76, 8, 'Lesson6', 'grade7/unit/lesson6/read', 1, '2023-04-10 00:10:42', '2023-04-10 00:10:42'),
(77, 9, 'Lesson1', 'grade7/unit/lesson1/read', 1, '2023-04-10 00:11:45', '2023-04-10 00:11:45'),
(78, 9, 'Lesson2', 'grade7/unit/lesson2/read', 1, '2023-04-10 00:11:45', '2023-04-10 00:11:45'),
(79, 9, 'Lesson3', 'grade7/unit/lesson3/read', 1, '2023-04-10 00:11:45', '2023-04-10 00:11:45'),
(80, 9, 'Lesson4', 'grade7/unit/lesson4/read', 1, '2023-04-10 00:11:45', '2023-04-10 00:11:45'),
(81, 9, 'Lesson5', 'grade7/unit/lesson5/read', 1, '2023-04-10 00:11:45', '2023-04-10 00:11:45'),
(82, 9, 'Lesson6', 'grade7/unit/lesson6/read', 1, '2023-04-10 00:11:45', '2023-04-10 00:11:45'),
(83, 10, 'Lesson1', 'grade7/unit/lesson1/read', 1, '2023-04-10 00:11:45', '2023-04-10 00:11:45'),
(84, 10, 'Lesson2', 'grade7/unit/lesson2/read', 1, '2023-04-10 00:11:45', '2023-04-10 00:11:45'),
(85, 10, 'Lesson3', 'grade7/unit/lesson3/read', 1, '2023-04-10 00:11:45', '2023-04-10 00:11:45'),
(86, 10, 'Lesson4', 'grade7/unit/lesson4/read', 1, '2023-04-10 00:11:45', '2023-04-10 00:11:45'),
(87, 10, 'Lesson5', 'grade7/unit/lesson5/read', 1, '2023-04-10 00:11:45', '2023-04-10 00:11:45'),
(88, 10, 'Lesson6', 'grade7/unit/lesson6/read', 1, '2023-04-10 00:11:45', '2023-04-10 00:11:45'),
(89, 11, 'Lesson1', 'grade7/unit/lesson1/read', 1, '2023-04-10 00:11:45', '2023-04-10 00:11:45'),
(90, 11, 'Lesson2', 'grade7/unit/lesson2/read', 1, '2023-04-10 00:11:45', '2023-04-10 00:11:45'),
(91, 11, 'Lesson3', 'grade7/unit/lesson3/read', 1, '2023-04-10 00:11:45', '2023-04-10 00:11:45'),
(92, 11, 'Lesson4', 'grade7/unit/lesson4/read', 1, '2023-04-10 00:11:45', '2023-04-10 00:11:45'),
(93, 11, 'Lesson5', 'grade7/unit/lesson5/read', 1, '2023-04-10 00:11:45', '2023-04-10 00:11:45'),
(94, 11, 'Lesson6', 'grade7/unit/lesson6/read', 1, '2023-04-10 00:11:45', '2023-04-10 00:11:45'),
(95, 12, 'Lesson1', 'grade7/unit/lesson1/read', 1, '2023-04-10 00:11:45', '2023-04-10 00:11:45'),
(96, 12, 'Lesson2', 'grade7/unit/lesson2/read', 1, '2023-04-10 00:11:45', '2023-04-10 00:11:45'),
(97, 12, 'Lesson3', 'grade7/unit/lesson3/read', 1, '2023-04-10 00:11:45', '2023-04-10 00:11:45'),
(98, 12, 'Lesson4', 'grade7/unit/lesson4/read', 1, '2023-04-10 00:11:45', '2023-04-10 00:11:45'),
(99, 12, 'Lesson5', 'grade7/unit/lesson5/read', 1, '2023-04-10 00:11:45', '2023-04-10 00:11:45'),
(100, 12, 'Lesson6', 'grade7/unit/lesson6/read', 1, '2023-04-10 00:11:45', '2023-04-10 00:11:45'),
(101, 13, 'Lesson1', 'grade7/unit/lesson1/read', 1, '2023-04-10 00:11:45', '2023-04-10 00:11:45'),
(102, 13, 'Lesson2', 'grade7/unit/lesson2/read', 1, '2023-04-10 00:11:45', '2023-04-10 00:11:45'),
(103, 13, 'Lesson3', 'grade7/unit/lesson3/read', 1, '2023-04-10 00:11:45', '2023-04-10 00:11:45'),
(104, 13, 'Lesson4', 'grade7/unit/lesson4/read', 1, '2023-04-10 00:11:45', '2023-04-10 00:11:45'),
(105, 13, 'Lesson5', 'grade7/unit/lesson5/read', 1, '2023-04-10 00:11:45', '2023-04-10 00:11:45'),
(106, 13, 'Lesson6', 'grade7/unit/lesson6/read', 1, '2023-04-10 00:11:45', '2023-04-10 00:11:45'),
(107, 14, 'Lesson1', 'grade7/unit/lesson1/read', 1, '2023-04-10 00:12:12', '2023-04-10 00:12:12'),
(108, 14, 'Lesson2', 'grade7/unit/lesson2/read', 1, '2023-04-10 00:12:12', '2023-04-10 00:12:12'),
(109, 14, 'Lesson3', 'grade7/unit/lesson3/read', 1, '2023-04-10 00:12:12', '2023-04-10 00:12:12'),
(110, 14, 'Lesson4', 'grade7/unit/lesson4/read', 1, '2023-04-10 00:12:12', '2023-04-10 00:12:12'),
(111, 14, 'Lesson5', 'grade7/unit/lesson5/read', 1, '2023-04-10 00:12:12', '2023-04-10 00:12:12'),
(112, 14, 'Lesson6', 'grade7/unit/lesson6/read', 1, '2023-04-10 00:12:12', '2023-04-10 00:12:12'),
(113, 15, 'Lesson1', 'grade7/unit/lesson1/read', 1, '2023-04-10 00:12:12', '2023-04-10 00:12:12'),
(114, 15, 'Lesson2', 'grade7/unit/lesson2/read', 1, '2023-04-10 00:12:12', '2023-04-10 00:12:12'),
(115, 15, 'Lesson3', 'grade7/unit/lesson3/read', 1, '2023-04-10 00:12:12', '2023-04-10 00:12:12'),
(116, 15, 'Lesson4', 'grade7/unit/lesson4/read', 1, '2023-04-10 00:12:12', '2023-04-10 00:12:12'),
(117, 15, 'Lesson5', 'grade7/unit/lesson5/read', 1, '2023-04-10 00:12:12', '2023-04-10 00:12:12'),
(118, 15, 'Lesson6', 'grade7/unit/lesson6/read', 1, '2023-04-10 00:12:12', '2023-04-10 00:12:12'),
(119, 16, 'Lesson1', 'grade7/unit/lesson1/read', 1, '2023-04-10 00:12:12', '2023-04-10 00:12:12'),
(120, 16, 'Lesson2', 'grade7/unit/lesson2/read', 1, '2023-04-10 00:12:12', '2023-04-10 00:12:12'),
(121, 16, 'Lesson3', 'grade7/unit/lesson3/read', 1, '2023-04-10 00:12:12', '2023-04-10 00:12:12'),
(122, 16, 'Lesson4', 'grade7/unit/lesson4/read', 1, '2023-04-10 00:12:12', '2023-04-10 00:12:12'),
(123, 16, 'Lesson5', 'grade7/unit/lesson5/read', 1, '2023-04-10 00:12:12', '2023-04-10 00:12:12'),
(124, 16, 'Lesson6', 'grade7/unit/lesson6/read', 1, '2023-04-10 00:12:12', '2023-04-10 00:12:12'),
(125, 17, 'Lesson1', 'grade7/unit/lesson1/read', 1, '2023-04-10 00:12:12', '2023-04-10 00:12:12'),
(126, 17, 'Lesson2', 'grade7/unit/lesson2/read', 1, '2023-04-10 00:12:12', '2023-04-10 00:12:12'),
(127, 17, 'Lesson3', 'grade7/unit/lesson3/read', 1, '2023-04-10 00:12:12', '2023-04-10 00:12:12'),
(128, 17, 'Lesson4', 'grade7/unit/lesson4/read', 1, '2023-04-10 00:12:12', '2023-04-10 00:12:12'),
(129, 17, 'Lesson5', 'grade7/unit/lesson5/read', 1, '2023-04-10 00:12:12', '2023-04-10 00:12:12'),
(130, 17, 'Lesson6', 'grade7/unit/lesson6/read', 1, '2023-04-10 00:12:12', '2023-04-10 00:12:12'),
(131, 18, 'Lesson1', 'grade7/unit/lesson1/read', 1, '2023-04-10 00:12:12', '2023-04-10 00:12:12'),
(132, 18, 'Lesson2', 'grade7/unit/lesson2/read', 1, '2023-04-10 00:12:12', '2023-04-10 00:12:12'),
(133, 18, 'Lesson3', 'grade7/unit/lesson3/read', 1, '2023-04-10 00:12:12', '2023-04-10 00:12:12'),
(134, 18, 'Lesson4', 'grade7/unit/lesson4/read', 1, '2023-04-10 00:12:12', '2023-04-10 00:12:12'),
(135, 18, 'Lesson5', 'grade7/unit/lesson5/read', 1, '2023-04-10 00:12:12', '2023-04-10 00:12:12'),
(136, 18, 'Lesson6', 'grade7/unit/lesson6/read', 1, '2023-04-10 00:12:12', '2023-04-10 00:12:12'),
(137, 3, 'Lesson2', 'grade9/unit/lesson2/read', 1, '2023-04-10 20:14:03', '2023-04-10 20:14:03'),
(138, 3, 'Lesson3', 'grade9/unit/lesson3/read', 1, '2023-04-10 20:14:03', '2023-04-10 20:14:03'),
(139, 3, 'Lesson4', 'grade9/unit/lesson4/read', 1, '2023-04-10 20:14:03', '2023-04-10 20:14:03'),
(140, 3, 'Lesson5', 'grade9/unit/lesson5/read', 1, '2023-04-10 20:14:03', '2023-04-10 20:14:03'),
(141, 3, 'Lesson6', 'grade9/unit/lesson6/read', 1, '2023-04-10 20:14:03', '2023-04-10 20:14:03');

-- --------------------------------------------------------

--
-- Table structure for table `lessons_old`
--

CREATE TABLE `lessons_old` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `file` varchar(255) DEFAULT NULL,
  `order` int(11) DEFAULT NULL,
  `unit_id` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lessons_old`
--

INSERT INTO `lessons_old` (`id`, `title`, `type`, `file`, `order`, `unit_id`, `active`, `created_at`, `updated_at`) VALUES
(1, 'R 0.1', 'audio', 'public/course/level1/unit1/R 0.1.mp3', 1, 1, 1, '2022-05-25 00:41:08', '2022-05-25 00:41:08'),
(2, 'R 0.10', 'audio', 'public/course/level1/unit1/R 0.10.mp3', 10, 1, 1, '2022-05-25 00:41:08', '2022-05-25 00:41:08'),
(3, 'R 0.11', 'audio', 'public/course/level1/unit1/R 0.11.mp3', 11, 1, 1, '2022-05-25 00:41:08', '2022-05-25 00:41:08'),
(4, 'R 0.2', 'audio', 'public/course/level1/unit1/R 0.2.mp3', 2, 1, 1, '2022-05-25 00:41:08', '2022-05-25 00:41:08'),
(5, 'R 0.3', 'audio', 'public/course/level1/unit1/R 0.3.mp3', 3, 1, 1, '2022-05-25 00:41:09', '2022-05-25 00:41:09'),
(6, 'R 0.4', 'audio', 'public/course/level1/unit1/R 0.4.mp3', 4, 1, 1, '2022-05-25 00:41:09', '2022-05-25 00:41:09'),
(7, 'R 0.5', 'audio', 'public/course/level1/unit1/R 0.5.mp3', 5, 1, 1, '2022-05-25 00:41:09', '2022-05-25 00:41:09'),
(8, 'R 0.6', 'audio', 'public/course/level1/unit1/R 0.6.mp3', 6, 1, 1, '2022-05-25 00:41:09', '2022-05-25 00:41:09'),
(9, 'R 0.7', 'audio', 'public/course/level1/unit1/R 0.7.mp3', 7, 1, 1, '2022-05-25 00:41:09', '2022-05-25 00:41:09'),
(10, 'R 0.8', 'audio', 'public/course/level1/unit1/R 0.8.mp3', 8, 1, 1, '2022-05-25 00:41:09', '2022-05-25 00:41:09'),
(11, 'R 0.9', 'audio', 'public/course/level1/unit1/R 0.9.mp3', 9, 1, 1, '2022-05-25 00:41:09', '2022-05-25 00:41:09'),
(12, 'R 1.1', 'audio', 'public/course/level1/unit1/R 1.1.mp3', 12, 1, 1, '2022-05-25 00:41:09', '2022-05-25 00:41:09'),
(13, 'R 1.10', 'audio', 'public/course/level1/unit1/R 1.10.mp3', 21, 1, 1, '2022-05-25 00:41:09', '2022-05-25 00:41:09'),
(14, 'R 1.11', 'audio', 'public/course/level1/unit1/R 1.11.mp3', 22, 1, 1, '2022-05-25 00:41:09', '2022-05-25 00:41:09'),
(15, 'R 1.12', 'audio', 'public/course/level1/unit1/R 1.12.mp3', 23, 1, 1, '2022-05-25 00:41:09', '2022-05-25 00:41:09'),
(16, 'R 1.13', 'audio', 'public/course/level1/unit1/R 1.13.mp3', 24, 1, 1, '2022-05-25 00:41:09', '2022-05-25 00:41:09'),
(17, 'R 1.14', 'audio', 'public/course/level1/unit1/R 1.14.mp3', 25, 1, 1, '2022-05-25 00:41:09', '2022-05-25 00:41:09'),
(18, 'R 1.15', 'audio', 'public/course/level1/unit1/R 1.15.mp3', 26, 1, 1, '2022-05-25 00:41:09', '2022-05-25 00:41:09'),
(19, 'R 1.16', 'audio', 'public/course/level1/unit1/R 1.16.mp3', 27, 1, 1, '2022-05-25 00:41:09', '2022-05-25 00:41:09'),
(20, 'R 1.17', 'audio', 'public/course/level1/unit1/R 1.17.mp3', 28, 1, 1, '2022-05-25 00:41:09', '2022-05-25 00:41:09'),
(21, 'R 1.18', 'audio', 'public/course/level1/unit1/R 1.18.mp3', 29, 1, 1, '2022-05-25 00:41:09', '2022-05-25 00:41:09'),
(22, 'R 1.19', 'audio', 'public/course/level1/unit1/R 1.19.mp3', 30, 1, 1, '2022-05-25 00:41:09', '2022-05-25 00:41:09'),
(23, 'R 1.2', 'audio', 'public/course/level1/unit1/R 1.2.mp3', 13, 1, 1, '2022-05-25 00:41:09', '2022-05-25 00:41:09'),
(24, 'R 1.20', 'audio', 'public/course/level1/unit1/R 1.20.mp3', 31, 1, 1, '2022-05-25 00:41:09', '2022-05-25 00:41:09'),
(25, 'R 1.21', 'audio', 'public/course/level1/unit1/R 1.21.mp3', 32, 1, 1, '2022-05-25 00:41:09', '2022-05-25 00:41:09'),
(26, 'R 1.22', 'audio', 'public/course/level1/unit1/R 1.22.mp3', 33, 1, 1, '2022-05-25 00:41:09', '2022-05-25 00:41:09'),
(27, 'R 1.3', 'audio', 'public/course/level1/unit1/R 1.3.mp3', 14, 1, 1, '2022-05-25 00:41:09', '2022-05-25 00:41:09'),
(28, 'R 1.4', 'audio', 'public/course/level1/unit1/R 1.4.mp3', 15, 1, 1, '2022-05-25 00:41:09', '2022-05-25 00:41:09'),
(29, 'R 1.5', 'audio', 'public/course/level1/unit1/R 1.5.mp3', 16, 1, 1, '2022-05-25 00:41:09', '2022-05-25 00:41:09'),
(30, 'R 1.6', 'audio', 'public/course/level1/unit1/R 1.6.mp3', 17, 1, 1, '2022-05-25 00:41:09', '2022-05-25 00:41:09'),
(31, 'R 1.7', 'audio', 'public/course/level1/unit1/R 1.7.mp3', 18, 1, 1, '2022-05-25 00:41:09', '2022-05-25 00:41:09'),
(32, 'R 1.8', 'audio', 'public/course/level1/unit1/R 1.8.mp3', 19, 1, 1, '2022-05-25 00:41:09', '2022-05-25 00:41:09'),
(33, 'R 1.9', 'audio', 'public/course/level1/unit1/R 1.9.mp3', 20, 1, 1, '2022-05-25 00:41:09', '2022-05-25 00:41:09'),
(34, 'R 2.1', 'audio', 'public/course/level1/unit2/R 2.1.mp3', 1, 2, 1, '2022-05-25 00:41:09', '2022-05-25 00:41:09'),
(35, 'R 2.10', 'audio', 'public/course/level1/unit2/R 2.10.mp3', 10, 2, 1, '2022-05-25 00:41:09', '2022-05-25 00:41:09'),
(36, 'R 2.11', 'audio', 'public/course/level1/unit2/R 2.11.mp3', 11, 2, 1, '2022-05-25 00:41:09', '2022-05-25 00:41:09'),
(37, 'R 2.12', 'audio', 'public/course/level1/unit2/R 2.12.mp3', 12, 2, 1, '2022-05-25 00:41:09', '2022-05-25 00:41:09'),
(38, 'R 2.13', 'audio', 'public/course/level1/unit2/R 2.13.mp3', 13, 2, 1, '2022-05-25 00:41:09', '2022-05-25 00:41:09'),
(39, 'R 2.14', 'audio', 'public/course/level1/unit2/R 2.14.mp3', 14, 2, 1, '2022-05-25 00:41:09', '2022-05-25 00:41:09'),
(40, 'R 2.15', 'audio', 'public/course/level1/unit2/R 2.15.mp3', 15, 2, 1, '2022-05-25 00:41:09', '2022-05-25 00:41:09'),
(41, 'R 2.16', 'audio', 'public/course/level1/unit2/R 2.16.mp3', 16, 2, 1, '2022-05-25 00:41:09', '2022-05-25 00:41:09'),
(42, 'R 2.2', 'audio', 'public/course/level1/unit2/R 2.2.mp3', 2, 2, 1, '2022-05-25 00:41:09', '2022-05-25 00:41:09'),
(43, 'R 2.3', 'audio', 'public/course/level1/unit2/R 2.3.mp3', 3, 2, 1, '2022-05-25 00:41:09', '2022-05-25 00:41:09'),
(44, 'R 2.4', 'audio', 'public/course/level1/unit2/R 2.4.mp3', 4, 2, 1, '2022-05-25 00:41:09', '2022-05-25 00:41:09'),
(45, 'R 2.5', 'audio', 'public/course/level1/unit2/R 2.5.mp3', 5, 2, 1, '2022-05-25 00:41:09', '2022-05-25 00:41:09'),
(46, 'R 2.6', 'audio', 'public/course/level1/unit2/R 2.6.mp3', 6, 2, 1, '2022-05-25 00:41:09', '2022-05-25 00:41:09'),
(47, 'R 2.7', 'audio', 'public/course/level1/unit2/R 2.7.mp3', 7, 2, 1, '2022-05-25 00:41:09', '2022-05-25 00:41:09'),
(48, 'R 2.8', 'audio', 'public/course/level1/unit2/R 2.8.mp3', 8, 2, 1, '2022-05-25 00:41:09', '2022-05-25 00:41:09'),
(49, 'R 2.9', 'audio', 'public/course/level1/unit2/R 2.9.mp3', 9, 2, 1, '2022-05-25 00:41:09', '2022-05-25 00:41:09'),
(50, 'R 3.1', 'audio', 'public/course/level1/unit3/R 3.1.mp3', 1, 3, 1, '2022-05-25 00:42:04', '2022-05-25 00:42:04'),
(51, 'R 3.10', 'audio', 'public/course/level1/unit3/R 3.10.mp3', 10, 3, 1, '2022-05-25 00:42:05', '2022-05-25 00:42:05'),
(52, 'R 3.11', 'audio', 'public/course/level1/unit3/R 3.11.mp3', 11, 3, 1, '2022-05-25 00:42:05', '2022-05-25 00:42:05'),
(53, 'R 3.12', 'audio', 'public/course/level1/unit3/R 3.12.mp3', 12, 3, 1, '2022-05-25 00:42:05', '2022-05-25 00:42:05'),
(54, 'R 3.13', 'audio', 'public/course/level1/unit3/R 3.13.mp3', 13, 3, 1, '2022-05-25 00:42:05', '2022-05-25 00:42:05'),
(55, 'R 3.14', 'audio', 'public/course/level1/unit3/R 3.14.mp3', 14, 3, 1, '2022-05-25 00:42:05', '2022-05-25 00:42:05'),
(56, 'R 3.15', 'audio', 'public/course/level1/unit3/R 3.15.mp3', 15, 3, 1, '2022-05-25 00:42:05', '2022-05-25 00:42:05'),
(57, 'R 3.16', 'audio', 'public/course/level1/unit3/R 3.16.mp3', 16, 3, 1, '2022-05-25 00:42:05', '2022-05-25 00:42:05'),
(58, 'R 3.17', 'audio', 'public/course/level1/unit3/R 3.17.mp3', 17, 3, 1, '2022-05-25 00:42:05', '2022-05-25 00:42:05'),
(59, 'R 3.2', 'audio', 'public/course/level1/unit3/R 3.2.mp3', 2, 3, 1, '2022-05-25 00:42:05', '2022-05-25 00:42:05'),
(60, 'R 3.3', 'audio', 'public/course/level1/unit3/R 3.3.mp3', 3, 3, 1, '2022-05-25 00:42:05', '2022-05-25 00:42:05'),
(61, 'R 3.4', 'audio', 'public/course/level1/unit3/R 3.4.mp3', 4, 3, 1, '2022-05-25 00:42:05', '2022-05-25 00:42:05'),
(62, 'R 3.5', 'audio', 'public/course/level1/unit3/R 3.5.mp3', 5, 3, 1, '2022-05-25 00:42:05', '2022-05-25 00:42:05'),
(63, 'R 3.6', 'audio', 'public/course/level1/unit3/R 3.6.mp3', 6, 3, 1, '2022-05-25 00:42:05', '2022-05-25 00:42:05'),
(64, 'R 3.7', 'audio', 'public/course/level1/unit3/R 3.7.mp3', 7, 3, 1, '2022-05-25 00:42:05', '2022-05-25 00:42:05'),
(65, 'R 3.8', 'audio', 'public/course/level1/unit3/R 3.8.mp3', 8, 3, 1, '2022-05-25 00:42:05', '2022-05-25 00:42:05'),
(66, 'R 3.9', 'audio', 'public/course/level1/unit3/R 3.9.mp3', 9, 3, 1, '2022-05-25 00:42:05', '2022-05-25 00:42:05'),
(67, 'R 4.1', 'audio', 'public/course/level1/unit4/R 4.1.mp3', 1, 4, 1, '2022-05-25 00:42:30', '2022-05-25 00:42:30'),
(68, 'R 4.10', 'audio', 'public/course/level1/unit4/R 4.10.mp3', 10, 4, 1, '2022-05-25 00:42:30', '2022-05-25 00:42:30'),
(69, 'R 4.11', 'audio', 'public/course/level1/unit4/R 4.11.mp3', 11, 4, 1, '2022-05-25 00:42:30', '2022-05-25 00:42:30'),
(70, 'R 4.12', 'audio', 'public/course/level1/unit4/R 4.12.mp3', 12, 4, 1, '2022-05-25 00:42:30', '2022-05-25 00:42:30'),
(71, 'R 4.13', 'audio', 'public/course/level1/unit4/R 4.13.mp3', 13, 4, 1, '2022-05-25 00:42:30', '2022-05-25 00:42:30'),
(72, 'R 4.14', 'audio', 'public/course/level1/unit4/R 4.14.mp3', 14, 4, 1, '2022-05-25 00:42:30', '2022-05-25 00:42:30'),
(73, 'R 4.15', 'audio', 'public/course/level1/unit4/R 4.15.mp3', 15, 4, 1, '2022-05-25 00:42:30', '2022-05-25 00:42:30'),
(74, 'R 4.2', 'audio', 'public/course/level1/unit4/R 4.2.mp3', 2, 4, 1, '2022-05-25 00:42:30', '2022-05-25 00:42:30'),
(75, 'R 4.3', 'audio', 'public/course/level1/unit4/R 4.3.mp3', 3, 4, 1, '2022-05-25 00:42:30', '2022-05-25 00:42:30'),
(76, 'R 4.4', 'audio', 'public/course/level1/unit4/R 4.4.mp3', 4, 4, 1, '2022-05-25 00:42:30', '2022-05-25 00:42:30'),
(77, 'R 4.5', 'audio', 'public/course/level1/unit4/R 4.5.mp3', 5, 4, 1, '2022-05-25 00:42:30', '2022-05-25 00:42:30'),
(78, 'R 4.6', 'audio', 'public/course/level1/unit4/R 4.6.mp3', 6, 4, 1, '2022-05-25 00:42:30', '2022-05-25 00:42:30'),
(79, 'R 4.7', 'audio', 'public/course/level1/unit4/R 4.7.mp3', 7, 4, 1, '2022-05-25 00:42:30', '2022-05-25 00:42:30'),
(80, 'R 4.8', 'audio', 'public/course/level1/unit4/R 4.8.mp3', 8, 4, 1, '2022-05-25 00:42:30', '2022-05-25 00:42:30'),
(81, 'R 4.9', 'audio', 'public/course/level1/unit4/R 4.9.mp3', 9, 4, 1, '2022-05-25 00:42:30', '2022-05-25 00:42:30'),
(82, 'R1.1', 'audio', 'public/course/level4/unit1/R1.1.mp3', 1, 5, 1, '2022-06-08 21:09:33', '2022-06-08 21:09:33'),
(83, 'R1.10', 'audio', 'public/course/level4/unit1/R1.10.mp3', 10, 5, 1, '2022-06-08 21:09:33', '2022-06-08 21:09:33'),
(84, 'R1.11', 'audio', 'public/course/level4/unit1/R1.11.mp3', 11, 5, 1, '2022-06-08 21:09:33', '2022-06-08 21:09:33'),
(85, 'R1.12', 'audio', 'public/course/level4/unit1/R1.12.mp3', 12, 5, 1, '2022-06-08 21:09:33', '2022-06-08 21:09:33'),
(86, 'R1.2', 'audio', 'public/course/level4/unit1/R1.2.mp3', 2, 5, 1, '2022-06-08 21:09:33', '2022-06-08 21:09:33'),
(87, 'R1.3', 'audio', 'public/course/level4/unit1/R1.3.mp3', 3, 5, 1, '2022-06-08 21:09:33', '2022-06-08 21:09:33'),
(88, 'R1.4', 'audio', 'public/course/level4/unit1/R1.4.mp3', 4, 5, 1, '2022-06-08 21:09:33', '2022-06-08 21:09:33'),
(89, 'R1.5', 'audio', 'public/course/level4/unit1/R1.5.mp3', 5, 5, 1, '2022-06-08 21:09:33', '2022-06-08 21:09:33'),
(90, 'R1.6', 'audio', 'public/course/level4/unit1/R1.6.mp3', 6, 5, 1, '2022-06-08 21:09:33', '2022-06-08 21:09:33'),
(91, 'R1.7', 'audio', 'public/course/level4/unit1/R1.7.mp3', 7, 5, 1, '2022-06-08 21:09:33', '2022-06-08 21:09:33'),
(92, 'R1.8', 'audio', 'public/course/level4/unit1/R1.8.mp3', 8, 5, 1, '2022-06-08 21:09:33', '2022-06-08 21:09:33'),
(93, 'R1.9', 'audio', 'public/course/level4/unit1/R1.9.mp3', 9, 5, 1, '2022-06-08 21:09:33', '2022-06-08 21:09:33'),
(94, 'R2.1', 'audio', 'public/course/level4/unit2/R2.1.mp3', 1, 6, 1, '2022-06-08 21:21:16', '2022-06-08 21:21:16'),
(95, 'R2.10', 'audio', 'public/course/level4/unit2/R2.10.mp3', 10, 6, 1, '2022-06-08 21:21:16', '2022-06-08 21:21:16'),
(96, 'R2.11', 'audio', 'public/course/level4/unit2/R2.11.mp3', 11, 6, 1, '2022-06-08 21:21:16', '2022-06-08 21:21:16'),
(97, 'R2.12', 'audio', 'public/course/level4/unit2/R2.12.mp3', 12, 6, 1, '2022-06-08 21:21:16', '2022-06-08 21:21:16'),
(98, 'R2.13', 'audio', 'public/course/level4/unit2/R2.13.mp3', 13, 6, 1, '2022-06-08 21:21:16', '2022-06-08 21:21:16'),
(99, 'R2.14', 'audio', 'public/course/level4/unit2/R2.14.mp3', 14, 6, 1, '2022-06-08 21:21:16', '2022-06-08 21:21:16'),
(100, 'R2.2', 'audio', 'public/course/level4/unit2/R2.2.mp3', 2, 6, 1, '2022-06-08 21:21:16', '2022-06-08 21:21:16'),
(101, 'R2.3', 'audio', 'public/course/level4/unit2/R2.3.mp3', 3, 6, 1, '2022-06-08 21:21:16', '2022-06-08 21:21:16'),
(102, 'R2.4', 'audio', 'public/course/level4/unit2/R2.4.mp3', 4, 6, 1, '2022-06-08 21:21:16', '2022-06-08 21:21:16'),
(103, 'R2.5', 'audio', 'public/course/level4/unit2/R2.5.mp3', 5, 6, 1, '2022-06-08 21:21:16', '2022-06-08 21:21:16'),
(104, 'R2.6', 'audio', 'public/course/level4/unit2/R2.6.mp3', 6, 6, 1, '2022-06-08 21:21:16', '2022-06-08 21:21:16'),
(105, 'R2.7', 'audio', 'public/course/level4/unit2/R2.7.mp3', 7, 6, 1, '2022-06-08 21:21:16', '2022-06-08 21:21:16'),
(106, 'R2.8', 'audio', 'public/course/level4/unit2/R2.8.mp3', 8, 6, 1, '2022-06-08 21:21:16', '2022-06-08 21:21:16'),
(107, 'R2.9', 'audio', 'public/course/level4/unit2/R2.9.mp3', 9, 6, 1, '2022-06-08 21:21:16', '2022-06-08 21:21:16'),
(108, 'R3.1', 'audio', 'public/course/level4/unit3/R3.1.mp3', 1, 7, 1, '2022-06-08 21:21:43', '2022-06-08 21:21:43'),
(109, 'R3.2', 'audio', 'public/course/level4/unit3/R3.2.mp3', 2, 7, 1, '2022-06-08 21:21:43', '2022-06-08 21:21:43'),
(110, 'R3.3', 'audio', 'public/course/level4/unit3/R3.3.mp3', 3, 7, 1, '2022-06-08 21:21:43', '2022-06-08 21:21:43'),
(111, 'R3.4', 'audio', 'public/course/level4/unit3/R3.4.mp3', 4, 7, 1, '2022-06-08 21:21:43', '2022-06-08 21:21:43'),
(112, 'R3.5', 'audio', 'public/course/level4/unit3/R3.5.mp3', 5, 7, 1, '2022-06-08 21:21:43', '2022-06-08 21:21:43'),
(113, 'R3.6', 'audio', 'public/course/level4/unit3/R3.6.mp3', 6, 7, 1, '2022-06-08 21:21:43', '2022-06-08 21:21:43'),
(114, 'R3.7', 'audio', 'public/course/level4/unit3/R3.7.mp3', 7, 7, 1, '2022-06-08 21:21:43', '2022-06-08 21:21:43'),
(115, 'R3.8', 'audio', 'public/course/level4/unit3/R3.8.mp3', 8, 7, 1, '2022-06-08 21:21:43', '2022-06-08 21:21:43'),
(116, 'R4.1', 'audio', 'public/course/level4/unit4/R4.1.mp3', 1, 8, 1, '2022-06-08 21:22:10', '2022-06-08 21:22:10'),
(117, 'R4.10', 'audio', 'public/course/level4/unit4/R4.10.mp3', 10, 8, 1, '2022-06-08 21:22:10', '2022-06-08 21:22:10'),
(118, 'R4.11', 'audio', 'public/course/level4/unit4/R4.11.mp3', 11, 8, 1, '2022-06-08 21:22:10', '2022-06-08 21:22:10'),
(119, 'R4.2', 'audio', 'public/course/level4/unit4/R4.2.mp3', 2, 8, 1, '2022-06-08 21:22:10', '2022-06-08 21:22:10'),
(120, 'R4.3', 'audio', 'public/course/level4/unit4/R4.3.mp3', 3, 8, 1, '2022-06-08 21:22:10', '2022-06-08 21:22:10'),
(121, 'R4.4', 'audio', 'public/course/level4/unit4/R4.4.mp3', 4, 8, 1, '2022-06-08 21:22:10', '2022-06-08 21:22:10'),
(122, 'R4.5', 'audio', 'public/course/level4/unit4/R4.5.mp3', 5, 8, 1, '2022-06-08 21:22:10', '2022-06-08 21:22:10'),
(123, 'R4.6', 'audio', 'public/course/level4/unit4/R4.6.mp3', 6, 8, 1, '2022-06-08 21:22:10', '2022-06-08 21:22:10'),
(124, 'R4.7', 'audio', 'public/course/level4/unit4/R4.7.mp3', 7, 8, 1, '2022-06-08 21:22:10', '2022-06-08 21:22:10'),
(125, 'R4.8', 'audio', 'public/course/level4/unit4/R4.8.mp3', 8, 8, 1, '2022-06-08 21:22:10', '2022-06-08 21:22:10'),
(126, 'R4.9', 'audio', 'public/course/level4/unit4/R4.9.mp3', 9, 8, 1, '2022-06-08 21:22:10', '2022-06-08 21:22:10');

-- --------------------------------------------------------

--
-- Table structure for table `lesson_descriptions`
--

CREATE TABLE `lesson_descriptions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `lesson_id` int(10) UNSIGNED NOT NULL,
  `alies` varchar(255) DEFAULT NULL,
  `lesson_type_id` int(10) UNSIGNED DEFAULT NULL,
  `hyper_link` mediumtext DEFAULT NULL,
  `audio` mediumtext DEFAULT NULL,
  `text` longtext DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lesson_descriptions`
--

INSERT INTO `lesson_descriptions` (`id`, `lesson_id`, `alies`, `lesson_type_id`, `hyper_link`, `audio`, `text`, `active`, `created_at`, `updated_at`) VALUES
(1, 1, 'grade7/unit/rogue-wave/get-ready', 1, 'https://cdn2.trunity.org/literature/into_lit/g7/student/epub/hmh_ngl20na_g7u1_sete_en_student/OPS/s9ml/cards/07le_03_ese_roguewave_gr.xhtml', NULL, NULL, 1, NULL, NULL),
(2, 1, 'grade7/unit/rogue-wave/read', 2, 'https://cdn2.trunity.org/literature/into_lit/g7/student/epub/hmh_ngl20na_g7u1_sete_en_student/OPS/s9ml/cards/07_ese_roguewave_01_en_us.xhtml', NULL, NULL, 1, NULL, NULL),
(3, 1, 'grade7/unit/rogue-wave/check-your-understanding', 3, 'https://cdn2.trunity.org/literature/into_lit/g7/student/epub/hmh_ngl20na_g7u1_sete_en_student/OPS/s9ml/cards/07le_03_ese_roguewave_cyu.xhtml', NULL, NULL, 1, NULL, NULL),
(4, 1, 'grade7/unit/rogue-wave/respond-analyze-the-text', 4, 'https://cdn2.trunity.org/literature/into_lit/g7/student/epub/hmh_ngl20na_g7u1_sete_en_student/OPS/s9ml/cards/07le_03_ese_roguewave_rpa.xhtml', NULL, NULL, 1, NULL, NULL),
(5, 1, 'grade7/unit/rogue-wave/respond-vocabulary', 5, 'https://cdn2.trunity.org/literature/into_lit/g7/student/epub/hmh_ngl20na_g7u1_sete_en_student/OPS/s9ml/cards/07le_03_ese_roguewave_rpv.xhtml', NULL, NULL, 1, NULL, NULL),
(6, 1, 'grade7/unit/rogue-wave/respond-language-conventions', 6, 'https://cdn2.trunity.org/literature/into_lit/g7/student/epub/hmh_ngl20na_g7u1_sete_en_student/OPS/s9ml/cards/07le_03_ese_roguewave_rplc.xhtml', NULL, NULL, 1, NULL, NULL),
(7, 1, 'grade7/unit/rogue-wave/close-read-screencast-1', 7, 'https://cdn2.trunity.org/literature/common/literature_video_player/V_FLLIT_0081a.mp4', NULL, NULL, 1, NULL, NULL),
(8, 1, 'grade7/unit/rogue-wave/close-read-screencast-2', 8, 'https://cdn2.trunity.org/literature/common/literature_video_player/V_FLLIT_0081b.mp4', NULL, NULL, 1, NULL, NULL),
(9, 1, 'grade7/unit/rogue-wave/text-in-focus-video', 10, 'https://cdn2.trunity.org/literature/common/literature_video_player/7li_tif_01_16c.mp4', NULL, NULL, 1, NULL, NULL),
(10, 2, 'grade7/unit/the-flight-of-icarus/get-ready', 1, 'https://cdn2.trunity.org/literature/into_lit/g7/student/epub/hmh_ngl20na_g7u1_sete_en_student/OPS/s9ml/cards/07le_03_ese_flightoficarus_gr.xhtml', NULL, NULL, 1, NULL, NULL),
(11, 2, 'grade7/unit/lesson2--the-flight-of-icarus/read', 2, 'https://cdn2.trunity.org/literature/into_lit/g7/student/epub/hmh_ngl20na_g7u1_sete_en_student/OPS/s9ml/cards/07_ese_flightoficarus_01_en_us.xhtml', NULL, NULL, 1, NULL, '2023-04-14 03:48:38'),
(12, 2, 'grade7/unit/the-flight-of-icarus/check-your-understanding', 3, 'https://cdn2.trunity.org/literature/into_lit/g7/student/epub/hmh_ngl20na_g7u1_sete_en_student/OPS/s9ml/cards/07le_03_ese_flightoficarus_cyu.xhtml', NULL, NULL, 1, NULL, NULL),
(13, 2, 'grade7/unit/the-flight-of-icarus/respond-analyze-the-text', 4, 'https://cdn2.trunity.org/literature/into_lit/g7/student/epub/hmh_ngl20na_g7u1_sete_en_student/OPS/s9ml/cards/07le_03_ese_flightoficarus_rpa.xhtml', NULL, NULL, 1, NULL, NULL),
(14, 2, 'grade7/unit/the-flight-of-icarus/respond-vocabulary', 5, 'https://cdn2.trunity.org/literature/into_lit/g7/student/epub/hmh_ngl20na_g7u1_sete_en_student/OPS/s9ml/cards/07le_03_ese_flightoficarus_rpv.xhtml', NULL, NULL, 1, NULL, NULL),
(15, 2, 'grade7/unit/the-flight-of-icarus/respond-language-conventions', 6, 'https://cdn2.trunity.org/literature/into_lit/g7/student/epub/hmh_ngl20na_g7u1_sete_en_student/OPS/s9ml/cards/07le_03_ese_flightoficarus_rplc.xhtml', NULL, NULL, 1, NULL, NULL),
(16, 2, 'grade7/unit/the-flight-of-icarus/close-read-screencast-1', 7, 'https://cdn2.trunity.org/literature/common/literature_video_player/V_FLLIT_0082.mp4', NULL, NULL, 1, NULL, NULL),
(17, 2, 'grade7/unit/the-flight-of-icarus/text-in-focus-video', 10, 'https://cdn2.trunity.org/literature/common/literature_video_player/7li_tif_01_17c.mp4', NULL, NULL, 1, NULL, NULL),
(18, 3, 'grade7/unit/icarus-flight/get-ready', 1, 'https://cdn2.trunity.org/literature/into_lit/g7/student/epub/hmh_ngl20na_g7u1_sete_en_student/OPS/s9ml/cards/07le_03_ese_icarussflight_gr.xhtml', NULL, NULL, 1, NULL, NULL),
(19, 3, 'grade7/unit/icarus-flight/read', 2, 'https://cdn2.trunity.org/literature/into_lit/g7/student/epub/hmh_ngl20na_g7u1_sete_en_student/OPS/s9ml/cards/07_ese_icarussflight_01_en_us.xhtml', NULL, NULL, 1, NULL, NULL),
(20, 3, 'grade7/unit/icarus-flight/check-your-understanding', 3, 'https://cdn2.trunity.org/literature/into_lit/g7/student/epub/hmh_ngl20na_g7u1_sete_en_student/OPS/s9ml/cards/07le_03_ese_icarussflight_cyu.xhtml', NULL, NULL, 1, NULL, NULL),
(21, 3, 'grade7/unit/icarus-flight/respond-analyze-the-text', 4, 'https://cdn2.trunity.org/literature/into_lit/g7/student/epub/hmh_ngl20na_g7u1_sete_en_student/OPS/s9ml/cards/07le_03_ese_icarussflight_rpa.xhtml', NULL, NULL, 1, NULL, NULL),
(22, 4, 'grade7/unit/women-in-aviation/get-ready', 1, 'https://cdn2.trunity.org/literature/into_lit/g7/student/epub/hmh_ngl20na_g7u1_sete_en_student/OPS/s9ml/cards/07le_03_ese_womeninaviation_gr.xhtml', NULL, NULL, 1, NULL, NULL),
(23, 4, 'grade7/unit/women-in-aviation/read', 2, 'https://cdn2.trunity.org/literature/into_lit/g7/student/epub/hmh_ngl20na_g7u1_sete_en_student/OPS/s9ml/cards/07_ese_womeninaviation_01_en_us.xhtml', NULL, NULL, 1, NULL, NULL),
(24, 4, 'grade7/unit/women-in-aviation/check-your-understanding', 3, 'https://cdn2.trunity.org/literature/into_lit/g7/student/epub/hmh_ngl20na_g7u1_sete_en_student/OPS/s9ml/cards/07le_03_ese_womeninaviation_cyu.xhtml', NULL, NULL, 1, NULL, NULL),
(25, 4, 'grade7/unit/women-in-aviation/respond-analyze-the-text', 4, 'https://cdn2.trunity.org/literature/into_lit/g7/student/epub/hmh_ngl20na_g7u1_sete_en_student/OPS/s9ml/cards/07le_03_ese_womeninaviation_rpa.xhtml', NULL, NULL, 1, NULL, NULL),
(26, 4, 'grade7/unit/women-in-aviation/respond-vocabulary', 5, 'https://cdn2.trunity.org/literature/into_lit/g7/student/epub/hmh_ngl20na_g7u1_sete_en_student/OPS/s9ml/cards/07le_03_ese_womeninaviation_rpv.xhtml', NULL, NULL, 1, NULL, NULL),
(27, 4, 'grade7/unit/women-in-aviation/respond-language-conventions', 6, 'https://cdn2.trunity.org/literature/into_lit/g7/student/epub/hmh_ngl20na_g7u1_sete_en_student/OPS/s9ml/cards/07le_03_ese_takingaction_ir.xhtml', NULL, NULL, 1, NULL, NULL),
(28, 5, 'grade7/unit/thank-you-mam/get-ready', 1, 'https://cdn2.trunity.org/literature/into_lit/g7/student/epub/hmh_ngl20na_g7u1_sete_en_student/OPS/s9ml/cards/07le_03_ese_thankyoumam_gr.xhtml', NULL, NULL, 1, NULL, NULL),
(29, 5, 'grade7/unit/thank-you-mam/read', 2, 'https://cdn2.trunity.org/literature/into_lit/g7/student/epub/hmh_ngl20na_g7u1_sete_en_student/OPS/s9ml/cards/07le_03_ese_thankyoumam_rd.xhtml', NULL, NULL, 1, NULL, NULL),
(30, 5, 'grade7/unit/thank-you-mam/check-your-understanding', 3, 'https://cdn2.trunity.org/literature/into_lit/g7/student/epub/hmh_ngl20na_g7u1_sete_en_student/OPS/s9ml/cards/07le_03_ese_thankyoumam_cyu.xhtml', NULL, NULL, 1, NULL, NULL),
(31, 5, 'grade7/unit/thank-you-mam/respond-analyze-the-text', 4, 'https://cdn2.trunity.org/literature/into_lit/g7/student/epub/hmh_ngl20na_g7u1_sete_en_student/OPS/s9ml/cards/07le_03_ese_thankyoumam_rpa.xhtml', NULL, NULL, 1, NULL, NULL),
(32, 5, 'grade7/unit/thank-you-mam/respond-vocabulary', 5, 'https://cdn2.trunity.org/literature/into_lit/g7/student/epub/hmh_ngl20na_g7u1_sete_en_student/OPS/s9ml/cards/07le_03_ese_thankyoumam_rpv.xhtml', NULL, NULL, 1, NULL, NULL),
(33, 5, 'grade7/unit/thank-you-mam/respond-language-conventions', 6, 'https://cdn2.trunity.org/literature/into_lit/g7/student/epub/hmh_ngl20na_g7u1_sete_en_student/OPS/s9ml/cards/07le_03_ese_thankyoumam_rplc.xhtml', NULL, NULL, 1, NULL, NULL),
(34, 6, 'grade7/unit/police-teenager/get-ready', 1, 'https://cdn2.trunity.org/literature/into_lit/g7/student/epub/hmh_ngl20na_g7u1_sete_en_student/OPS/s9ml/cards/07le_03_ese_policestopchang_gr.xhtml', NULL, NULL, 1, NULL, NULL),
(35, 6, 'grade7/unit/police-teenager/read', 2, 'https://cdn2.trunity.org/literature/into_lit/g7/student/epub/hmh_ngl20na_g7u1_sete_en_student/OPS/s9ml/cards/07le_03_ese_policestopchang_rd.xhtml', NULL, NULL, 1, NULL, NULL),
(36, 6, 'grade7/unit/police-teenager/check-your-understanding', 3, 'https://cdn2.trunity.org/literature/into_lit/g7/student/epub/hmh_ngl20na_g7u1_sete_en_student/OPS/s9ml/cards/07le_03_ese_policestopchang_cyu.xhtml', NULL, NULL, 1, NULL, NULL),
(37, 6, 'grade7/unit/police-teenager/respond-analyze-the-text', 4, 'https://cdn2.trunity.org/literature/into_lit/g7/student/epub/hmh_ngl20na_g7u1_sete_en_student/OPS/s9ml/cards/07le_03_ese_policestopchang_rpa.xhtml', NULL, NULL, 1, NULL, NULL),
(38, 6, 'grade7/unit/police-teenager/respond-vocabulary', 5, 'https://cdn2.trunity.org/literature/into_lit/g7/student/epub/hmh_ngl20na_g7u1_sete_en_student/OPS/s9ml/cards/07le_03_ese_policestopchang_rpv.xhtml', NULL, NULL, 1, NULL, NULL),
(39, 6, 'grade7/unit/police-teenager/respond-language-conventions', 6, 'https://cdn2.trunity.org/literature/into_lit/g7/student/epub/hmh_ngl20na_g7u1_sete_en_student/OPS/s9ml/cards/07le_03_ese_policestopchang_rplc.xhtml', NULL, NULL, 1, NULL, NULL),
(40, 6, 'grade7/unit/police-teenager/collaborate-and-compare', 11, 'https://cdn2.trunity.org/literature/into_lit/g7/student/epub/hmh_ngl20na_g7u1_sete_en_student/OPS/s9ml/cards/07le_03_ese_thankyoupolicstop_cc.xhtml', NULL, NULL, 1, NULL, NULL),
(41, 10, 'grade8/unit/brave-little-toaster/get-ready', 1, 'https://cdn2.trunity.org/literature/into_lit/g8/student/epub/hmh_ngl20na_g08u1_sete_en_student/OPS/s9ml/cards/08le_04_ese_bravelittltoast_gr.xhtml', NULL, NULL, 1, NULL, NULL),
(42, 10, 'grade8/unit/brave-little-toaster/read', 2, '', NULL, NULL, 1, NULL, NULL),
(43, 11, 'grade8/unit/bionic-superhumans/get-ready', 1, 'https://cdn2.trunity.org/literature/into_lit/g8/student/epub/hmh_ngl20na_g08u1_sete_en_student/OPS/s9ml/cards/08le_04_ese_bionsuperhuman_gr.xhtml', NULL, NULL, 1, NULL, NULL),
(44, 11, 'grade8/unit/bionic-superhumans/read', 2, 'https://cdn2.trunity.org/literature/into_lit/g8/student/epub/hmh_ngl20na_g08u1_sete_en_student/OPS/s9ml/cards/08le_04_ese_bionsuperhuman_rd.xhtml', NULL, NULL, 1, NULL, NULL),
(45, 11, 'grade8/unit/bionic-superhumans/check-your-understanding', 3, 'https://cdn2.trunity.org/literature/into_lit/g8/student/epub/hmh_ngl20na_g08u1_sete_en_student/OPS/s9ml/cards/08le_04_ese_bionsuperhuman_cyu.xhtml', NULL, NULL, 1, NULL, NULL),
(46, 11, 'grade8/unit/bionic-superhumans/respond-analyze-the-text', 4, 'https://cdn2.trunity.org/literature/into_lit/g8/student/epub/hmh_ngl20na_g08u1_sete_en_student/OPS/s9ml/cards/08le_04_ese_bionsuperhuman_rpa.xhtml', NULL, NULL, 1, NULL, NULL),
(47, 11, 'grade8/unit/bionic-superhumans/respond-vocabulary', 5, 'https://cdn2.trunity.org/literature/into_lit/g8/student/epub/hmh_ngl20na_g08u1_sete_en_student/OPS/s9ml/cards/08le_04_ese_bionsuperhuman_rpv.xhtml', NULL, NULL, 1, NULL, NULL),
(48, 11, 'grade8/unit/bionic-superhumans/respond-language-conventions', 6, 'https://cdn2.trunity.org/literature/into_lit/g8/student/epub/hmh_ngl20na_g08u1_sete_en_student/OPS/s9ml/cards/08le_04_ese_bionsuperhuman_rplc.xhtml', NULL, NULL, 1, NULL, NULL),
(49, 11, 'grade8/unit/bionic-superhumans/text-in-focus-video', 10, 'https://cdn2.trunity.org/literature/common/literature_video_player/ngl_tif_bionic.mp4', NULL, NULL, 1, NULL, NULL),
(50, 15, 'grade9/unit/quilt-of-country/get-ready', 1, 'https://cdn2.trunity.org/literature/into_lit/g9/student/epub/hmh_ngl20na_g9u1_sete_en_student/OPS/s9ml/cards/09le_04_ese_quiltcountry_gr.xhtml', NULL, NULL, 1, NULL, NULL),
(51, 15, 'grade9/unit/brave-little-toaster/read', 2, 'https://cdn2.trunity.org/literature/into_lit/g9/student/epub/hmh_ngl20na_g9u1_sete_en_student/OPS/s9ml/cards/09_ese_quiltcountry_04_en_us.xhtml', NULL, NULL, 1, NULL, NULL),
(52, 15, 'grade9/unit/quilt-of-country/check-your-understanding', 3, 'https://cdn2.trunity.org/literature/into_lit/g9/student/epub/hmh_ngl20na_g9u1_sete_en_student/OPS/s9ml/cards/09le_04_ese_quiltcountry_cyu.xhtml', NULL, NULL, 1, NULL, NULL),
(53, 15, 'grade9/unit/quilt-of-country/respond-analyze-the-text', 4, 'https://cdn2.trunity.org/literature/into_lit/g9/student/epub/hmh_ngl20na_g9u1_sete_en_student/OPS/s9ml/cards/09le_04_ese_quiltcountry_rpa.xhtml', NULL, NULL, 1, NULL, NULL),
(54, 15, 'grade9/unit/quilt-of-country/respond-vocabulary', 5, 'https://cdn2.trunity.org/literature/into_lit/g9/student/epub/hmh_ngl20na_g9u1_sete_en_student/OPS/s9ml/cards/09le_04_ese_quiltcountry_rpv.xhtml', NULL, NULL, 1, NULL, NULL),
(55, 47, 'grade7/unit/lesson1-test-lesson/read', 2, 'https://cdn.hfghfgh', 'admin/uploads/audios/mixkit-little-birds-singing-in-the-trees-17-1681253148.wav', '<p>fghfghfg tyftyty ytfffyftfy ftytf fty</p>', 1, '2023-04-10 00:10:42', '2023-04-13 22:14:30'),
(56, 48, 'grade7/unit/lesson2/read', 2, 'https://quran.com/1', NULL, NULL, 1, '2023-04-10 00:10:42', '2023-04-16 20:07:59'),
(57, 49, 'grade7/unit/lesson3/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:10:42', '2023-04-10 00:10:42'),
(58, 50, 'grade7/unit/lesson4/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:10:42', '2023-04-10 00:10:42'),
(59, 51, 'grade7/unit/lesson5/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:10:42', '2023-04-10 00:10:42'),
(60, 52, 'grade7/unit/lesson6/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:10:42', '2023-04-10 00:10:42'),
(61, 53, 'grade7/unit/lesson1/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:10:42', '2023-04-10 00:10:42'),
(62, 54, 'grade7/unit/lesson2/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:10:42', '2023-04-10 00:10:42'),
(63, 55, 'grade7/unit/lesson3/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:10:42', '2023-04-10 00:10:42'),
(64, 56, 'grade7/unit/lesson4/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:10:42', '2023-04-10 00:10:42'),
(65, 57, 'grade7/unit/lesson5/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:10:42', '2023-04-10 00:10:42'),
(66, 58, 'grade7/unit/lesson6/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:10:42', '2023-04-10 00:10:42'),
(67, 59, 'grade7/unit/lesson1/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:10:42', '2023-04-10 00:10:42'),
(68, 60, 'grade7/unit/lesson2/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:10:42', '2023-04-10 00:10:42'),
(69, 61, 'grade7/unit/lesson3/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:10:42', '2023-04-10 00:10:42'),
(70, 62, 'grade7/unit/lesson4/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:10:42', '2023-04-10 00:10:42'),
(71, 63, 'grade7/unit/lesson5/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:10:42', '2023-04-10 00:10:42'),
(72, 64, 'grade7/unit/lesson6/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:10:42', '2023-04-10 00:10:42'),
(73, 65, 'grade7/unit/lesson1/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:10:42', '2023-04-10 00:10:42'),
(74, 66, 'grade7/unit/lesson2/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:10:42', '2023-04-10 00:10:42'),
(75, 67, 'grade7/unit/lesson3/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:10:42', '2023-04-10 00:10:42'),
(76, 68, 'grade7/unit/lesson4/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:10:42', '2023-04-10 00:10:42'),
(77, 69, 'grade7/unit/lesson5/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:10:42', '2023-04-10 00:10:42'),
(78, 70, 'grade7/unit/lesson6/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:10:42', '2023-04-10 00:10:42'),
(79, 71, 'grade7/unit/lesson1/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:10:42', '2023-04-10 00:10:42'),
(80, 72, 'grade7/unit/lesson2/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:10:42', '2023-04-10 00:10:42'),
(81, 73, 'grade7/unit/lesson3/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:10:42', '2023-04-10 00:10:42'),
(82, 74, 'grade7/unit/lesson4/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:10:42', '2023-04-10 00:10:42'),
(83, 75, 'grade7/unit/lesson5/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:10:42', '2023-04-10 00:10:42'),
(84, 76, 'grade7/unit/lesson6/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:10:42', '2023-04-10 00:10:42'),
(85, 77, 'grade7/unit/lesson1/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:11:45', '2023-04-10 00:11:45'),
(86, 78, 'grade7/unit/lesson2/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:11:45', '2023-04-10 00:11:45'),
(87, 79, 'grade7/unit/lesson3/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:11:45', '2023-04-10 00:11:45'),
(88, 80, 'grade7/unit/lesson4/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:11:45', '2023-04-10 00:11:45'),
(89, 81, 'grade7/unit/lesson5/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:11:45', '2023-04-10 00:11:45'),
(90, 82, 'grade7/unit/lesson6/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:11:45', '2023-04-10 00:11:45'),
(91, 83, 'grade7/unit/lesson1/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:11:45', '2023-04-10 00:11:45'),
(92, 84, 'grade7/unit/lesson2/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:11:45', '2023-04-10 00:11:45'),
(93, 85, 'grade7/unit/lesson3/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:11:45', '2023-04-10 00:11:45'),
(94, 86, 'grade7/unit/lesson4/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:11:45', '2023-04-10 00:11:45'),
(95, 87, 'grade7/unit/lesson5/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:11:45', '2023-04-10 00:11:45'),
(96, 88, 'grade7/unit/lesson6/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:11:45', '2023-04-10 00:11:45'),
(97, 89, 'grade7/unit/lesson1/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:11:45', '2023-04-10 00:11:45'),
(98, 90, 'grade7/unit/lesson2/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:11:45', '2023-04-10 00:11:45'),
(99, 91, 'grade7/unit/lesson3/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:11:45', '2023-04-10 00:11:45'),
(100, 92, 'grade7/unit/lesson4/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:11:45', '2023-04-10 00:11:45'),
(101, 93, 'grade7/unit/lesson5/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:11:45', '2023-04-10 00:11:45'),
(102, 94, 'grade7/unit/lesson6/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:11:45', '2023-04-10 00:11:45'),
(103, 95, 'grade7/unit/lesson1/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:11:45', '2023-04-10 00:11:45'),
(104, 96, 'grade7/unit/lesson2/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:11:45', '2023-04-10 00:11:45'),
(105, 97, 'grade7/unit/lesson3/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:11:45', '2023-04-10 00:11:45'),
(106, 98, 'grade7/unit/lesson4/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:11:45', '2023-04-10 00:11:45'),
(107, 99, 'grade7/unit/lesson5/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:11:45', '2023-04-10 00:11:45'),
(108, 100, 'grade7/unit/lesson6/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:11:45', '2023-04-10 00:11:45'),
(109, 101, 'grade7/unit/lesson1/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:11:45', '2023-04-10 00:11:45'),
(110, 102, 'grade7/unit/lesson2/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:11:45', '2023-04-10 00:11:45'),
(111, 103, 'grade7/unit/lesson3/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:11:45', '2023-04-10 00:11:45'),
(112, 104, 'grade7/unit/lesson4/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:11:45', '2023-04-10 00:11:45'),
(113, 105, 'grade7/unit/lesson5/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:11:45', '2023-04-10 00:11:45'),
(114, 106, 'grade7/unit/lesson6/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:11:45', '2023-04-10 00:11:45'),
(115, 107, 'grade7/unit/lesson1/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:12:12', '2023-04-10 00:12:12'),
(116, 108, 'grade7/unit/lesson2/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:12:12', '2023-04-10 00:12:12'),
(117, 109, 'grade7/unit/lesson3/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:12:12', '2023-04-10 00:12:12'),
(118, 110, 'grade7/unit/lesson4/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:12:12', '2023-04-10 00:12:12'),
(119, 111, 'grade7/unit/lesson5/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:12:12', '2023-04-10 00:12:12'),
(120, 112, 'grade7/unit/lesson6/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:12:12', '2023-04-10 00:12:12'),
(121, 113, 'grade7/unit/lesson1/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:12:12', '2023-04-10 00:12:12'),
(122, 114, 'grade7/unit/lesson2/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:12:12', '2023-04-10 00:12:12'),
(123, 115, 'grade7/unit/lesson3/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:12:12', '2023-04-10 00:12:12'),
(124, 116, 'grade7/unit/lesson4/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:12:12', '2023-04-10 00:12:12'),
(125, 117, 'grade7/unit/lesson5/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:12:12', '2023-04-10 00:12:12'),
(126, 118, 'grade7/unit/lesson6/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:12:12', '2023-04-10 00:12:12'),
(127, 119, 'grade7/unit/lesson1/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:12:12', '2023-04-10 00:12:12'),
(128, 120, 'grade7/unit/lesson2/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:12:12', '2023-04-10 00:12:12'),
(129, 121, 'grade7/unit/lesson3/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:12:12', '2023-04-10 00:12:12'),
(130, 122, 'grade7/unit/lesson4/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:12:12', '2023-04-10 00:12:12'),
(131, 123, 'grade7/unit/lesson5/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:12:12', '2023-04-10 00:12:12'),
(132, 124, 'grade7/unit/lesson6/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:12:12', '2023-04-10 00:12:12'),
(133, 125, 'grade7/unit/lesson1/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:12:12', '2023-04-10 00:12:12'),
(134, 126, 'grade7/unit/lesson2/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:12:12', '2023-04-10 00:12:12'),
(135, 127, 'grade7/unit/lesson3/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:12:12', '2023-04-10 00:12:12'),
(136, 128, 'grade7/unit/lesson4/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:12:12', '2023-04-10 00:12:12'),
(137, 129, 'grade7/unit/lesson5/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:12:12', '2023-04-10 00:12:12'),
(138, 130, 'grade7/unit/lesson6/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:12:12', '2023-04-10 00:12:12'),
(139, 131, 'grade7/unit/lesson1/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:12:12', '2023-04-10 00:12:12'),
(140, 132, 'grade7/unit/lesson2/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:12:12', '2023-04-10 00:12:12'),
(141, 133, 'grade7/unit/lesson3/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:12:12', '2023-04-10 00:12:12'),
(142, 134, 'grade7/unit/lesson4/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:12:12', '2023-04-10 00:12:12'),
(143, 135, 'grade7/unit/lesson5/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:12:12', '2023-04-10 00:12:12'),
(144, 136, 'grade7/unit/lesson6/read', 2, NULL, NULL, NULL, 1, '2023-04-10 00:12:12', '2023-04-10 00:12:12'),
(145, 137, 'grade9/unit/lesson2/read', 2, NULL, NULL, NULL, 1, '2023-04-10 20:14:03', '2023-04-10 20:14:03'),
(146, 138, 'grade9/unit/lesson3/read', 2, NULL, NULL, NULL, 1, '2023-04-10 20:14:03', '2023-04-10 20:14:03'),
(147, 139, 'grade9/unit/lesson4/read', 2, NULL, NULL, NULL, 1, '2023-04-10 20:14:03', '2023-04-10 20:14:03'),
(148, 140, 'grade9/unit/lesson5/read', 2, NULL, NULL, NULL, 1, '2023-04-10 20:14:03', '2023-04-10 20:14:03'),
(149, 141, 'grade9/unit/lesson6/read', 2, NULL, NULL, NULL, 1, '2023-04-10 20:14:03', '2023-04-10 20:14:03');

-- --------------------------------------------------------

--
-- Table structure for table `lesson_types`
--

CREATE TABLE `lesson_types` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lesson_types`
--

INSERT INTO `lesson_types` (`id`, `title`, `created_at`, `updated_at`) VALUES
(1, 'Get Ready', NULL, NULL),
(2, 'Read', NULL, NULL),
(3, 'Check Your Understanding', NULL, NULL),
(4, 'Respond: Analyze the text', NULL, NULL),
(5, 'Respond: Vocabulary', NULL, NULL),
(6, 'Respond: Language Conventions', NULL, NULL),
(7, 'Close Read Screencast 1', NULL, NULL),
(8, 'Close Read Screencast 2', NULL, NULL),
(9, 'Close Read Screencast 3', NULL, NULL),
(10, 'Text in Focus Video', NULL, NULL),
(11, 'Collaborate and Compare', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `levels`
--

CREATE TABLE `levels` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `active` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `levels`
--

INSERT INTO `levels` (`id`, `title`, `active`, `created_at`, `updated_at`) VALUES
(1, 'Elementary L1', 1, NULL, NULL),
(2, 'Elementary L2', 1, NULL, NULL),
(3, 'Elementary L3', 1, NULL, NULL),
(4, 'Pre-Intermediate L1', 1, NULL, NULL),
(5, 'Pre-Intermediate L2', 1, NULL, NULL),
(6, 'Pre-Intermediate L3', 1, NULL, NULL),
(7, 'Intermediate L1', 1, NULL, NULL),
(8, 'Intermediate L2', 1, NULL, NULL),
(9, 'Intermediate L3', 1, NULL, NULL),
(10, 'Upper-Intermediate L1', 1, NULL, NULL),
(11, 'Upper-Intermediate L2', 1, NULL, NULL),
(12, 'Upper-Intermediate L3', 1, NULL, NULL),
(13, 'Upper-Intermediate L4', 1, NULL, NULL),
(14, 'Upper-Intermediate L5', 1, NULL, NULL),
(15, 'Advanced C1 L1', 1, NULL, NULL),
(16, 'Advanced C1 L2', 1, NULL, NULL),
(17, 'Advanced C1 L3', 1, NULL, NULL),
(18, 'Advanced C1 L4', 1, NULL, NULL),
(19, 'Advanced C1 L5', 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `listening_books`
--

CREATE TABLE `listening_books` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `author` varchar(255) DEFAULT NULL,
  `active` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `listening_chapters`
--

CREATE TABLE `listening_chapters` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `listen_book_id` int(11) NOT NULL,
  `iframe_link` varchar(255) DEFAULT NULL,
  `aduio` varchar(255) DEFAULT NULL,
  `text` longtext DEFAULT NULL
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
(2, '2014_10_12_100000_create_password_resets_table', 1),
(3, '2019_08_19_000000_create_failed_jobs_table', 1),
(4, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(6, '2022_08_15_120542_laratrust_setup_tables', 2),
(7, '2022_08_13_124621_create_employees_table', 3),
(12, '2014_10_12_000000_create_users_table', 5),
(21, '2022_08_15_140405_create_students_table', 9),
(23, '2022_08_15_154930_create_courses_table', 11),
(26, '2022_08_19_093700_create_course_translations_table', 12);

-- --------------------------------------------------------

--
-- Table structure for table `organization`
--

CREATE TABLE `organization` (
  `id` int(11) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `promocode` varchar(255) NOT NULL,
  `active` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `organization`
--

INSERT INTO `organization` (`id`, `title`, `promocode`, `active`) VALUES
(1, 'Osama', '4free', 1),
(2, 'admin_55', 'english55', 0),
(3, 'admin_60', 'english60', 0),
(4, 'admin_65', 'english65', 0),
(5, 'admin_70', 'english70', 0),
(6, 'admin_75', 'english75', 0),
(7, 'helwan_55', 'helwan55', 0),
(8, '15may_60', '15may60', 0),
(9, '15MayClub', '15mayclub', 1),
(10, 'Free Celta', 'free_celta', 1);

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `display_name` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permission_role`
--

CREATE TABLE `permission_role` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permission_user`
--

CREATE TABLE `permission_user` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `user_type` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quizzes`
--

CREATE TABLE `quizzes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `question_type` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `option_text` varchar(255) NOT NULL,
  `correct_answer` text NOT NULL,
  `hint` text DEFAULT NULL,
  `correct_massage` text DEFAULT NULL,
  `incorrect_massage` text DEFAULT NULL,
  `score` int(11) DEFAULT NULL,
  `order` int(11) DEFAULT NULL,
  `section_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `quizzes`
--

INSERT INTO `quizzes` (`id`, `question_type`, `slug`, `description`, `option_text`, `correct_answer`, `hint`, `correct_massage`, `incorrect_massage`, `score`, `order`, `section_id`, `created_at`, `updated_at`) VALUES
(1, 'Single Or Multi Choice', 'questions/82669', '<p><span style=\"text-decoration: underline; font-family: georgia, palatino, serif; font-size: 14pt;\"><span style=\"color: #000000; text-decoration: underline;\">Choose the correct answer.</span></span></p>\n<p><span style=\"font-size: 14pt; color: #34495e; font-family: georgia, palatino, serif;\">1- ____\'s your name? </span></p>\n<p><span style=\"font-size: 14pt; color: #34495e; font-family: georgia, palatino, serif;\">&nbsp; &nbsp; My name is Rita.</span></p>', '[\"How\",\"Who\",\"What\",\"When\"]', '[\"What\"]', NULL, NULL, NULL, 1, 2, 1, '2021-02-06 22:36:40', '2021-02-09 07:01:55'),
(2, 'Single Or Multi Choice', 'questions/50484', '<p><span style=\"font-family: georgia, palatino, serif; font-size: 14pt; color: #34495e;\">2- This is Chris and his brother, Luke. ____ my friends.</span></p>', '[\"I\'m\",\"They\'re\",\"We\'re\",\"She\'s\"]', '[\"They\'re\"]', NULL, NULL, NULL, 1, 2, 1, '2021-02-06 22:44:01', '2021-02-06 22:57:36'),
(3, 'Single Or Multi Choice', 'questions/28299', '<p><span style=\"font-family: georgia, palatino, serif; font-size: 14pt; color: #34495e;\">3- I\'m from Milan. ____ is in Italy.</span></p>', '[\"He\",\"She\",\"It\",\"They\"]', '[\"It\"]', NULL, NULL, NULL, 1, 2, 1, '2021-02-06 22:46:18', '2021-02-06 22:46:18'),
(5, 'Single Or Multi Choice', 'questions/61895', '<p><span style=\"font-family: georgia, palatino, serif; font-size: 14pt; color: #34495e;\">4- Excuse me, how ____ your last name? S-A-R-A-H</span></p>', '[\"do you spell\",\"are you spell\",\"spell\",\"you spell\"]', '[\"do you spell\"]', NULL, NULL, NULL, 1, 2, 1, '2021-02-06 22:48:57', '2021-02-16 21:38:50'),
(6, 'Single Or Multi Choice', 'questions/27825', '<p><span style=\"font-family: georgia, palatino, serif; font-size: 14pt; color: #2f346d;\">5- I need just one sandwich. Give me _____ bread and one piece of burger.</span></p>', '[\"one\",\"--\",\"an\",\"a\"]', '[\"--\"]', NULL, NULL, NULL, 1, 2, 1, '2021-02-06 22:55:39', '2021-02-07 22:07:42'),
(7, 'Single Or Multi Choice', 'questions/78941', '<p><span style=\"font-family: georgia, palatino, serif; font-size: 14pt; color: #171b72;\">6- _____________? No, he isn\'t.</span></p>', '[\"Are they teachers?\",\"Are you from Italy?\",\"Is Mr. Mike a teacher?\",\"Is this your phone?\"]', '[\"Is Mr. Mike a teacher?\"]', NULL, NULL, NULL, 1, 2, 1, '2021-02-06 22:59:43', '2021-02-07 22:08:06'),
(8, 'Single Or Multi Choice', 'questions/93086', '<p><span style=\"font-family: georgia, palatino, serif; font-size: 14pt; color: #171b72;\">7- What is ___________?</span></p>\n<p><span style=\"font-family: georgia, palatino, serif; font-size: 14pt; color: #171b72;\">&nbsp; &nbsp; He is an engineer.</span></p>', '[\"job your father\",\"your father job\",\"your father job\'s\",\"your father\'s job\"]', '[\"your father\'s job\"]', NULL, NULL, NULL, 1, 2, 1, '2021-02-06 23:02:33', '2021-02-07 22:08:21'),
(9, 'Single Or Multi Choice', 'questions/25472', '<p><span style=\"color: #171b72; font-family: georgia, palatino, serif;\"><span style=\"font-size: 18.6667px;\">8- She ___________ a dog. &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;</span></span></p>', '[\"hasn\'t\",\"doesn\'t have\",\"doesn\'t has\",\"don\'t have\"]', '[\"doesn\'t have\"]', NULL, NULL, NULL, 1, 2, 1, '2021-02-06 23:03:46', '2021-02-07 22:08:35'),
(10, 'Single Or Multi Choice', 'questions/92413', '<p><span style=\"color: #171b72; font-family: georgia, palatino, serif;\"><span style=\"font-size: 18.6667px;\">9- Stephen ____ in our company.</span></span></p>', '[\"works\",\"work\",\"is work\",\"working\"]', '[\"works\"]', NULL, NULL, NULL, 1, 2, 1, '2021-02-06 23:04:31', '2021-02-07 22:08:50'),
(11, 'Single Or Multi Choice', 'questions/45253', '<p><span style=\"color: #171b72; font-family: georgia, palatino, serif;\"><span style=\"font-size: 18.6667px;\">10- Choose the correct sentence from the following:</span></span></p>', '[\"We not often go to the cinema.\",\"We often do not go to the cinema.\",\"We do not go often to the cinema.\",\"We do not often go to the cinema.\"]', '[\"We do not often go to the cinema.\"]', NULL, NULL, NULL, 1, 2, 1, '2021-02-06 23:06:37', '2021-02-09 06:25:04'),
(12, 'Single Or Multi Choice', 'questions/24030', '<p><span style=\"font-family: georgia, palatino, serif; font-size: 14pt; color: #171b72;\">11- We usually _______ the shopping in a supermarket.</span></p>', '[\"do\",\"make\",\"go\",\"have\"]', '[\"do\"]', NULL, NULL, NULL, 1, 2, 1, '2021-02-06 23:09:20', '2021-02-07 22:09:22'),
(14, 'Single Or Multi Choice', 'questions/15844', '<p><span style=\"font-family: georgia, palatino, serif; font-size: 14pt; color: #171b72;\">12-&nbsp;They hardly ____ visit us.</span></p>', '[\"ever\",\"sometimes\",\"never\",\"usually\"]', '[\"ever\"]', NULL, NULL, NULL, 1, 2, 1, '2021-02-06 23:11:30', '2021-02-07 22:09:33'),
(18, 'Single Or Multi Choice', 'questions/9852', '<p><span style=\"text-decoration: underline; font-family: georgia, palatino, serif; color: #000000;\"><span style=\"font-size: 14pt;\">Choose the correct answers:</span></span></p>\n<p><span style=\"font-size: 14pt; color: #34495e; font-family: georgia, palatino, serif;\">1- I\'m quite tall, but my brother\'s ________</span></p>', '[\"taller\",\"tallest\"]', '[\"taller\"]', NULL, NULL, NULL, 1, NULL, 2, '2021-02-07 18:52:47', '2021-02-09 07:55:43'),
(19, 'Single Or Multi Choice', 'questions/28353', '<p><span style=\"font-size: 14pt; color: #34495e; font-family: georgia, palatino, serif;\">2- Who\'s the ______intelligent person in the class?</span></p>', '[\"more\",\"most\"]', '[\"most\"]', NULL, NULL, NULL, 1, NULL, 2, '2021-02-07 18:56:22', '2021-02-07 18:56:22'),
(23, 'Single Or Multi Choice', 'questions/22044', '<p><span style=\"font-size: 14pt; color: #34495e; font-family: georgia, palatino, serif;\">3- Is your husband ____ than you?</span></p>', '[\"friendlier\",\"more friendly\"]', '[\"friendlier\"]', NULL, NULL, NULL, 1, NULL, 2, '2021-02-07 19:04:35', '2021-02-09 06:34:37'),
(28, 'Single Or Multi Choice', 'questions/10189', '<p><span style=\"font-family: georgia, palatino, serif; font-size: 14pt; color: #171b72;\">4- Johann and Sudan went ______ holiday last week.</span></p>', '[\"in\",\"on\"]', '[\"on\"]', NULL, NULL, NULL, 1, NULL, 2, '2021-02-07 22:02:53', '2021-02-09 05:27:52'),
(29, 'Single Or Multi Choice', 'questions/94905', '<p><span style=\"font-family: georgia, palatino, serif; font-size: 14pt; color: #171b72;\">5- You can go _____ long walks in the mountains.</span></p>', '[\"for\",\"to\"]', '[\"for\"]', NULL, NULL, NULL, 1, NULL, 2, '2021-02-07 22:05:09', '2021-02-09 05:28:03'),
(30, 'Free Answer', 'questions/78531', '<p><span style=\"text-decoration: underline; color: #000000;\"><span style=\"font-family: georgia, palatino, serif; font-size: 14pt;\">Write a question to ask about the words in <strong>bold.</strong></span></span></p>\n<p><span style=\"font-family: georgia, palatino, serif; font-size: 14pt; color: #2f346d;\">Example: I was born <strong>in Sydney</strong>.&nbsp; &nbsp; =&gt; <span style=\"color: #e67e23;\">Where were you born?</span></span></p>\n<p>&nbsp;</p>\n<p><span style=\"font-family: georgia, palatino, serif; font-size: 14pt; color: #2f346d;\">1- Sally\'s grandmother lives <strong>in Spain</strong>.</span></p>', '[]', '[]', NULL, NULL, NULL, 1, NULL, 2, '2021-02-07 22:16:07', '2021-02-09 06:55:22'),
(31, 'Free Answer', 'questions/49702', '<p><span style=\"font-family: georgia, palatino, serif; font-size: 14pt; color: #171b72;\">2- Tom had <strong>seven</strong> children.</span></p>', '[]', '[]', NULL, NULL, NULL, 1, NULL, 2, '2021-02-07 22:32:21', '2021-02-07 22:32:21'),
(32, 'Free Answer', 'questions/67497', '<p><span style=\"color: #171b72; font-family: georgia, palatino, serif;\"><span style=\"font-size: 18.6667px;\">3- They get up<strong> at 8:30 </strong>every morning.</span></span></p>', '[]', '[]', NULL, NULL, NULL, 1, NULL, 2, '2021-02-07 22:33:16', '2021-02-07 22:33:16'),
(33, 'Single Or Multi Choice', 'questions/33652', '<p><span style=\"text-decoration: underline; color: #000000;\"><span style=\"font-family: georgia, palatino, serif;\"><span style=\"font-size: 18.6667px;\">Choose the correct negative form:</span></span></span></p>\n<p><span style=\"color: #171b72; font-family: georgia, palatino, serif;\"><span style=\"font-size: 18.6667px;\">1- I went to the cinema&nbsp; last night.&nbsp;</span></span></p>', '[\"I don\'t went to the cinema\\u00a0 last night.\\u00a0\",\"I didn\'t go to the cinema\\u00a0 last night.\\u00a0\",\"I not went to the cinema\\u00a0 last night.\\u00a0\"]', '[\"I didn\'t go to the cinema\\u00a0 last night.\\u00a0\"]', NULL, NULL, NULL, 1, NULL, 2, '2021-02-07 22:36:26', '2021-02-09 06:55:08'),
(34, 'Single Or Multi Choice', 'questions/71164', '<p><span style=\"font-family: georgia, palatino, serif; font-size: 14pt; color: #2f346d; background-color: #ffffff;\">2- They have become very rich.</span></p>', '[\"They haven\'t become very rich.\",\"They don\'t have become very rich.\",\"They have become not very rich.\"]', '[\"They haven\'t become very rich.\"]', NULL, NULL, NULL, 1, NULL, 2, '2021-02-07 22:37:12', '2021-02-09 06:04:26'),
(35, 'Single Or Multi Choice', 'questions/46822', '<p><span style=\"font-family: georgia, palatino, serif; font-size: 14pt; color: #2f346d; background-color: #ffffff;\">3- I have a big window in my room.</span></p>', '[\"I don\'t have a big window in my room.\",\"I haven\'t a big window in my room.\"]', '[\"I don\'t have a big window in my room.\"]', NULL, NULL, NULL, 1, NULL, 2, '2021-02-07 22:38:01', '2021-02-09 06:05:36'),
(36, 'Single Or Multi Choice', 'questions/56167', '<p><span style=\"font-family: georgia, palatino, serif; font-size: 14pt; color: #2f346d; background-color: #ffffff;\">4- My mom has perfect cooking skills.</span></p>', '[\"My mom don\'t has perfect cooking skills.\",\"My mom doesn\'t  has perfect cooking skills.\",\"My mom doesn\'t have perfect cooking skills.\",\"My mom hasn\'t perfect cooking skills.\"]', '[\"My mom doesn\'t have perfect cooking skills.\"]', NULL, NULL, NULL, 1, NULL, 2, '2021-02-07 22:38:27', '2021-02-09 06:08:54'),
(37, 'Fill In Blank', 'questions/64467', '<p><span style=\"text-decoration: underline; color: #000000;\"><span style=\"font-family: georgia, palatino, serif; font-size: 14pt;\">Fill in the gaps with the words in the box.</span></span></p>\n<table style=\"border-collapse: collapse; width: 100%;\" border=\"1\">\n<tbody>\n<tr>\n<td style=\"width: 98.5336%;\"><span style=\"font-family: georgia, palatino, serif; font-size: 14pt; color: #171b72;\"><span style=\"font-size: 14pt; font-family: georgia, palatino, serif; color: #ba372a;\">&nbsp; ill&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;beautiful&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; quiet&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; rich&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; crowded&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span><span style=\"font-size: 14pt; font-family: georgia, palatino, serif; color: #ba372a;\">interesting&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</span><span style=\"font-size: 14pt; font-family: georgia, palatino, serif; color: #ba372a;\">difficult&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; friendly&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; intelligent</span></span></td>\n</tr>\n</tbody>\n</table>\n<p><span style=\"font-family: georgia, palatino, serif; font-size: 14pt; color: #171b72;\"><span style=\"font-size: 12pt;\">Saturday 12 August&nbsp;</span></span></p>\n<p><span style=\"font-family: georgia, palatino, serif; font-size: 12pt; color: #171b72;\">Sally didn\'t come to see me today. She was (1) .................. and she stayed in bed for the day.</span></p>\n<p><span style=\"font-family: georgia, palatino, serif; font-size: 12pt; color: #171b72;\">The house was very (2) ................... because my two noisy little brothers were away.</span></p>\n<p><span style=\"font-family: georgia, palatino, serif; font-size: 12pt; color: #171b72;\">I studied English in the morning but I didn\'t finish my homework. It was too (3) ..................!</span></p>\n<p><span style=\"font-family: georgia, palatino, serif; font-size: 12pt; color: #171b72;\">In the afternoon, I went to the beach. It was a (4) .................. day, but the beach was very</span></p>\n<p><span style=\"font-family: georgia, palatino, serif; font-size: 12pt; color: #171b72;\">(5) ..................., so I didn\'t stay. Then I met Miranda, a student from my English class.</span></p>\n<p><span style=\"font-family: georgia, palatino, serif; font-size: 12pt; color: #171b72;\">She is very (6) ..................... She is always first in our class. She is also from a very (7) .............</span></p>\n<p><span style=\"font-family: georgia, palatino, serif; font-size: 12pt; color: #171b72;\">family. Her father is a famous musician. But she is really (8) ................... She always says&nbsp;</span></p>\n<p><span style=\"font-family: georgia, palatino, serif; font-size: 12pt; color: #171b72;\">hello to me. We went to a Chinese restaurant for dinner and had a very (9) ....................</span></p>\n<p><span style=\"font-family: georgia, palatino, serif; font-size: 12pt; color: #171b72;\">conversation about her father\'s job. So it was a really nice day in the end.</span></p>', '[\"[\\\"ill\\\"]\",\"[\\\"quiet\\\"]\",\"[\\\"difficult\\\"]\",\"[\\\"beautiful\\\"]\",\"[\\\"crowded\\\"]\",\"[\\\"intelligent\\\"]\",\"[\\\"rich\\\"]\",\"[\\\"friendly\\\"]\",\"[\\\"interesting\\\"]\"]', '[[[\"ill\"],[\"quiet\"],[\"difficult\"],[\"beautiful\"],[\"crowded\"],[\"intelligent\"],[\"rich\"],[\"friendly\"],[\"interesting\"]]]', NULL, NULL, NULL, 9, NULL, 2, '2021-02-07 22:52:58', '2022-06-13 07:51:57'),
(38, 'Fill In Blank', 'questions/31329', '<p><span style=\"text-decoration: underline; color: #000000;\"><span style=\"font-family: georgia, palatino, serif; font-size: 14pt;\">Read Julia\'s Email. Put the verbs in brackets in the Present Simple, Present Contunuous, Past Simple, or Present Perfect.</span></span></p>\n<p><span style=\"font-family: georgia, palatino, serif; font-size: 12pt; color: #2f346d;\">Hi Roberto,</span></p>\n<p><span style=\"font-family: georgia, palatino, serif; font-size: 12pt; color: #2f346d;\">How are you? I hope you are OK. At the moment I (0) <em><span style=\"color: #e03e2d;\">am sitting</span></em> (sit) in an Internet cafe.&nbsp;</span></p>\n<p><span style=\"font-family: georgia, palatino, serif; font-size: 12pt; color: #2f346d;\">I (1) .................... (arrive) in London two days ago. and I (2) .................... (want) to do an </span></p>\n<p><span style=\"font-family: georgia, palatino, serif; font-size: 12pt; color: #2f346d;\">English course for a month. (3) ....................... you ever ...................... (go) to London?</span></p>\n<p><span style=\"font-family: georgia, palatino, serif; font-size: 12pt; color: #2f346d;\">It\'s an amazing city, but I (4) ...................... (not understand) the people very well.&nbsp;</span></p>\n<p><span style=\"font-family: georgia, palatino, serif; font-size: 12pt; color: #2f346d;\">The problem is they (5) ....................... (talk) very fast. I\'m here with one of my cousins,</span></p>\n<p><span style=\"font-family: georgia, palatino, serif; font-size: 12pt; color: #2f346d;\">Javier. You (6) ...................... (meet) him last year when you (7) &nbsp;...................... (come) to my </span></p>\n<p><span style=\"font-size: 12pt;\"><span style=\"font-family: georgia, palatino, serif; color: #2f346d;\">house for a barbecue. </span><span style=\"font-family: georgia, palatino, serif; color: #2f346d;\">He (8) ...................... (do) some shopping in Oxford Street at the moment </span></span></p>\n<p><span style=\"font-size: 12pt;\"><span style=\"font-family: georgia, palatino, serif; color: #2f346d;\">because he (9) ...................... (need) to buy&nbsp;</span><span style=\"font-family: georgia, palatino, serif; color: #2f346d;\">a new coat. Oh, my phone (10) ...................... (ring)</span></span></p>\n<p><span style=\"font-family: georgia, palatino, serif; font-size: 12pt; color: #2f346d;\">I will write again soon.</span></p>\n<p><span style=\"font-family: georgia, palatino, serif; font-size: 12pt; color: #2f346d;\">Love,</span></p>\n<p><span style=\"font-family: georgia, palatino, serif; font-size: 12pt; color: #2f346d;\">Julia.</span></p>', '[\"[\\\"arrived\\\"]\",\"[\\\"want\\\"]\",\"[\\\"have been\\\"]\",\"[\\\"don\'t understand\\\"]\",\"[\\\"talk\\\"]\",\"[\\\"met\\\"]\",\"[\\\"came\\\"]\",\"[\\\"is doing\\\"]\",\"[\\\"needs\\\"]\",\"[\\\"is ringing\\\"]\"]', '[[[\"arrived\"],[\"want\"],[\"have been\"],[\"don\'t understand\"],[\"talk\"],[\"met\"],[\"came\"],[\"is doing\"],[\"needs\"],[\"is ringing\"]]]', NULL, NULL, NULL, 10, NULL, 2, '2021-02-07 23:08:06', '2021-02-09 06:54:32'),
(42, 'Fill In Blank', 'questions/84469', '<p><span style=\"color: #000000;\"><span style=\"text-decoration: underline; font-family: georgia, palatino, serif;\"><span style=\"font-size: 14pt;\">Listen to Joe\'s phone call Call-a-Flight . </span></span><span style=\"text-decoration: underline; font-family: georgia, palatino, serif;\"><span style=\"font-size: 14pt;\">Fill in gaps 1-7 in his notes.</span></span></span></p>\n<p>&nbsp;</p>\n<p><audio controls=\"controls\">\n  <source src=\"https://test.webdative.com/public/audios/quizzes/2021-02/1-1El.mp3\" type=\"audio/mpeg\" />\n  Your browser does not support the audio tag.</audio></p>\n<table style=\"border-collapse: collapse; width: 43.4678%; height: 302px;\" border=\"1\">\n<tbody>\n<tr>\n<td style=\"width: 100%;\">\n<p><span style=\"font-family: \'comic sans ms\', sans-serif;\"><em>&nbsp; </em></span><span style=\"text-decoration: underline; font-family: \'comic sans ms\', sans-serif;\"><em>Bosten Trip&nbsp;</em></span></p>\n<p><span style=\"font-family: \'comic sans ms\', sans-serif;\"><strong>&nbsp; Leaving </strong>Saturday (1)................. February</span></p>\n<p><span style=\"font-family: \'comic sans ms\', sans-serif;\">&nbsp; &nbsp;depart London Heathrow (2)................</span></p>\n<p><span style=\"font-family: \'comic sans ms\', sans-serif;\">&nbsp; &nbsp;arrive Bosten (3).............</span></p>\n<p><span style=\"font-family: \'comic sans ms\', sans-serif;\"><strong>&nbsp; Return flight</strong> Sunday (4)...................March</span></p>\n<p><span style=\"font-family: \'comic sans ms\', sans-serif;\">&nbsp; &nbsp;depart Bosten (5)..........................</span></p>\n<p><span style=\"font-family: \'comic sans ms\', sans-serif;\">&nbsp; &nbsp;arrive London (6).....................</span></p>\n<p><span style=\"font-family: \'comic sans ms\', sans-serif;\">&nbsp; &nbsp;price (7) ................ pounds</span></p>\n</td>\n</tr>\n</tbody>\n</table>\n<p>&nbsp;</p>', '[\"[\\\"24\\\"]\",\"[\\\"13.20\\\"]\",\"[\\\"18.45\\\"]\",\"[\\\"11\\\"]\",\"[\\\"5.15\\\"]\",\"[\\\"8.20\\\"]\",\"[\\\"259\\\"]\"]', '[[[\"24\"],[\"13.20\"],[\"18.45\"],[\"11\"],[\"5.15\"],[\"8.20\"],[\"259\"]]]', NULL, NULL, NULL, 7, NULL, 2, '2021-02-08 01:29:53', '2021-02-09 06:54:18'),
(48, 'Fill In Blank', 'questions/6101', '<p><span style=\"text-decoration: underline; color: #000000;\"><span style=\"font-family: georgia, palatino, serif; font-size: 14pt;\">Fill in the gaps in the conversation with the words and phrases in the box.</span></span></p>\n<table style=\"border-collapse: collapse; width: 100%;\" border=\"1\">\n<tbody>\n<tr>\n<td style=\"width: 98.5336%;\">\n<p><span style=\"color: #e03e2d;\"><span style=\"font-family: georgia, palatino, serif; font-size: 14pt;\">&nbsp; this is&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;really sorry&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;shall I &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</span><span style=\"font-family: georgia, palatino, serif; font-size: 14pt;\">could you help me&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;did she&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;Oh, dear &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;</span></span><span style=\"color: #e03e2d;\"><span style=\"font-family: georgia, palatino, serif; font-size: 14pt;\"> have to&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;don\'t worry &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;speaking&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </span><span style=\"font-family: georgia, palatino, serif; font-size: 14pt;\">that would be</span></span></p>\n</td>\n</tr>\n</tbody>\n</table>\n<p><span style=\"font-family: georgia, palatino, serif; font-size: 12pt; color: #2f346d;\"><strong>Tom&nbsp; &nbsp; </strong>Tom Radford (1) .............................</span></p>\n<p><span style=\"font-family: georgia, palatino, serif; font-size: 12pt; color: #2f346d;\"><strong>Ann&nbsp; &nbsp; &nbsp;</strong>Hello, (2) ............................. Ann Jones.</span></p>\n<p><span style=\"font-family: georgia, palatino, serif; font-size: 12pt; color: #2f346d;\">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;I am (3) ............................., but I can\'t come to dinner</span></p>\n<p><span style=\"font-family: georgia, palatino, serif; font-size: 12pt; color: #2f346d;\">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; with you tomorrow. I (4) ............................. go to see my</span></p>\n<p><span style=\"font-family: georgia, palatino, serif; font-size: 12pt; color: #2f346d;\">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;mother in hospital.</span></p>\n<p><span style=\"font-family: georgia, palatino, serif; font-size: 12pt; color: #2f346d;\"><strong>Tom&nbsp; &nbsp; &nbsp;</strong>(5) .............................! What happened?</span></p>\n<p><span style=\"font-family: georgia, palatino, serif; font-size: 12pt; color: #2f346d;\"><strong>Ann&nbsp; &nbsp; &nbsp; </strong>She had a car accident yesterday.</span></p>\n<p><span style=\"font-family: georgia, palatino, serif; font-size: 12pt; color: #2f346d;\"><strong>Tom&nbsp; &nbsp; &nbsp; </strong>(6) .............................? That\'s terrible.&nbsp;</span></p>\n<p><span style=\"font-family: georgia, palatino, serif; font-size: 12pt; color: #2f346d;\"><strong>Ann&nbsp; &nbsp; &nbsp; </strong>Luckily, she is not badly injured.</span></p>\n<p><span style=\"font-family: georgia, palatino, serif; font-size: 12pt; color: #2f346d;\"><strong>Tom&nbsp; &nbsp; &nbsp; </strong>Good. Anyway, (7) ............................. about dinner. We can</span></p>\n<p><span style=\"font-family: georgia, palatino, serif; font-size: 12pt; color: #2f346d;\">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;do it another time.</span></p>\n<p><span style=\"font-family: georgia, palatino, serif; font-size: 12pt; color: #2f346d;\"><strong>Ann</strong>&nbsp; &nbsp; &nbsp;Great. Tom, (8) ............................. with something?</span></p>\n<p><span style=\"font-family: georgia, palatino, serif; font-size: 12pt; color: #2f346d;\"><strong>Tom&nbsp; &nbsp; &nbsp; </strong>Yes, of course. What is it?</span></p>\n<p><span style=\"font-family: georgia, palatino, serif; font-size: 12pt; color: #2f346d;\"><strong>Ann</strong>&nbsp; &nbsp; &nbsp; I am going to stay at my mother\'s house for ten days&nbsp;</span></p>\n<p><span style=\"font-family: georgia, palatino, serif; font-size: 12pt; color: #2f346d;\">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;and I can\'t take my cat with me.</span></p>\n<p><span style=\"font-family: georgia, palatino, serif; font-size: 12pt; color: #2f346d;\"><strong>Tom&nbsp; &nbsp; &nbsp; </strong>(9) ............................. look after it?</span></p>\n<p><span style=\"font-family: georgia, palatino, serif; font-size: 12pt; color: #2f346d;\"><strong>Ann&nbsp; &nbsp; &nbsp; </strong>Oh, Tom. (10) ............................. great. Thanks</span></p>\n<p>&nbsp;</p>', '[\"[\\\"speaking\\\"]\",\"[\\\"this is\\\"]\",\"[\\\"really sorry\\\"]\",\"[\\\"have to\\\"]\",\"[\\\"Oh, dear\\\"]\",\"[\\\"did she\\\"]\",\"[\\\"don\'t worry\\\"]\",\"[\\\"could you help me\\\"]\",\"[\\\"shall I\\\"]\",\"[\\\"that would be\\\"]\"]', '[[[\"speaking\"],[\"this is\"],[\"really sorry\"],[\"have to\"],[\"Oh, dear\"],[\"did she\"],[\"don\'t worry\"],[\"could you help me\"],[\"shall I\"],[\"that would be\"]]]', NULL, NULL, NULL, 10, NULL, 3, '2021-02-08 03:22:44', '2022-06-13 08:12:32'),
(49, 'Single Or Multi Choice', 'questions/93566', '<p><span style=\"text-decoration: underline; color: #000000;\"><span style=\"font-family: georgia, palatino, serif; font-size: 14pt;\">Choose the correct answer.</span></span></p>\n<p><span style=\"font-family: georgia, palatino, serif; font-size: 14pt; color: #2f346d;\">1- If he offered me a job, I __________ it.</span></p>', '[\"will accept\",\"accept\",\"would accept\",\"would have accept\"]', '[\"would accept\"]', NULL, NULL, NULL, 3, NULL, 3, '2021-02-08 03:26:57', '2021-02-09 06:53:49'),
(50, 'Single Or Multi Choice', 'questions/5963', '<p><span style=\"background-color: #ffffff;\"><span style=\"color: #2f346d; font-family: georgia, palatino, serif;\"><span style=\"font-size: 18.6667px; background-color: #ffffff;\">2- If he studies harder, he ________ his exam easily.</span></span></span></p>', '[\"will pass\",\"pass\",\"would pass\",\"would have passed\"]', '[\"will pass\"]', NULL, NULL, NULL, 3, NULL, 3, '2021-02-08 03:28:40', '2021-02-08 03:28:40'),
(51, 'Single Or Multi Choice', 'questions/89952', '<p><span style=\"background-color: #ffffff;\"><span style=\"color: #2f346d; font-family: georgia, palatino, serif;\"><span style=\"font-size: 18.6667px; background-color: #ffffff;\">3- I am looking forward to ________ you soon.</span></span></span></p>', '[\"seeing\",\"seen\",\"see\",\"saw\"]', '[\"seeing\"]', NULL, NULL, NULL, 3, NULL, 3, '2021-02-08 03:29:42', '2021-02-08 03:29:42'),
(52, 'Single Or Multi Choice', 'questions/51617', '<p><span style=\"background-color: #ffffff;\"><span style=\"color: #2f346d; font-family: georgia, palatino, serif;\"><span style=\"font-size: 18.6667px; background-color: #ffffff;\">4- John told me that he __________ to travel ________ week.</span></span></span></p>', '[\"wants -- next\",\"wanted -- the following\",\"want -- next\",\"wanted -- next\"]', '[\"wanted -- the following\"]', NULL, NULL, NULL, 3, NULL, 3, '2021-02-08 03:31:56', '2021-02-08 03:31:56'),
(53, 'Single Or Multi Choice', 'questions/53028', '<p><span style=\"background-color: #ffffff;\"><span style=\"color: #2f346d; font-family: georgia, palatino, serif;\"><span style=\"font-size: 18.6667px; background-color: #ffffff;\">5- That is the book ________ I gave you.</span></span></span></p>', '[\"which\",\"who\"]', '[\"which\"]', NULL, NULL, NULL, 3, NULL, 3, '2021-02-08 03:32:56', '2021-02-08 03:32:56'),
(54, 'Single Or Multi Choice', 'questions/41580', '<p><span style=\"background-color: #ffffff;\"><span style=\"color: #2f346d; font-family: georgia, palatino, serif;\"><span style=\"font-size: 18.6667px; background-color: #ffffff;\">6- Do you remember the market _______ you bought some vegetables?</span></span></span></p>', '[\"which\",\"where\"]', '[\"where\"]', NULL, NULL, NULL, 3, NULL, 3, '2021-02-08 03:34:27', '2021-02-08 03:34:27'),
(55, 'Single Or Multi Choice', 'questions/41568', '<p><span style=\"background-color: #ffffff;\"><span style=\"color: #2f346d; font-family: georgia, palatino, serif;\"><span style=\"font-size: 18.6667px; background-color: #ffffff;\">7-&nbsp; Many people ________ from our company last year.</span></span></span></p>', '[\"fired\",\"are fired\",\"were fired\",\"have been fired\"]', '[\"were fired\"]', NULL, NULL, NULL, 3, NULL, 3, '2021-02-08 03:36:16', '2021-02-08 03:36:16'),
(56, 'Single Or Multi Choice', 'questions/21134', '<p><span style=\"background-color: #ffffff;\"><span style=\"color: #2f346d; font-family: georgia, palatino, serif;\"><span style=\"font-size: 18.6667px; background-color: #ffffff;\">8-&nbsp; Why don\'t you ________ smoking?</span></span></span></p>', '[\"give up\",\"give away\"]', '[\"give up\"]', NULL, NULL, NULL, 3, NULL, 3, '2021-02-08 03:37:22', '2021-02-09 06:57:27'),
(57, 'Single Or Multi Choice', 'questions/75737', '<p><span style=\"background-color: #ffffff;\"><span style=\"color: #2f346d; font-family: georgia, palatino, serif;\"><span style=\"font-size: 18.6667px; background-color: #ffffff;\">9- We have to __________ our neighbor\'s noise because they have&nbsp; party.</span></span></span></p>', '[\"put up with\",\"go out with\"]', '[\"put up with\"]', NULL, NULL, NULL, 3, NULL, 3, '2021-02-08 03:38:24', '2021-02-09 06:57:35'),
(58, 'Single Or Multi Choice', 'questions/89534', '<p><span style=\"background-color: #ffffff;\"><span style=\"color: #2f346d; font-family: georgia, palatino, serif;\"><span style=\"font-size: 18.6667px; background-color: #ffffff;\">10- The opposite of polite is _______</span></span></span></p>', '[\"impolite\",\"unpolite\"]', '[\"impolite\"]', NULL, NULL, NULL, 3, NULL, 3, '2021-02-08 03:39:43', '2021-02-09 06:57:49'),
(59, 'Single Or Multi Choice', 'questions/58452', '<p><span style=\"color: #2f346d; font-family: georgia, palatino, serif;\"><span style=\"font-size: 18.6667px;\">11- The daughter of your brother is your ________</span></span></p>', '[\"niece\",\"nephew\"]', '[\"niece\"]', NULL, NULL, NULL, 3, NULL, 3, '2021-02-08 03:40:33', '2021-02-09 06:57:59'),
(60, 'Single Or Multi Choice', 'questions/90182', '<p><span style=\"background-color: #ffffff;\"><span style=\"color: #2f346d; font-family: georgia, palatino, serif;\"><span style=\"font-size: 18.6667px; background-color: #ffffff;\">11- The mother of your husband is your ________</span></span></span></p>', '[\"mother-in-law\",\"stepmother\"]', '[\"mother-in-law\"]', NULL, NULL, NULL, 3, NULL, 3, '2021-02-08 03:41:30', '2021-02-09 06:58:14'),
(61, 'Single Or Multi Choice', 'questions/84214', '<p><span style=\"text-decoration: underline; color: #000000;\"><span style=\"font-family: georgia, palatino, serif; font-size: 14pt;\">Listen to the recording and choose the correct answer.</span></span></p>\n<p>&nbsp;</p>\n<p><audio controls=\"controls\">\n  <source src=\"https://test.webdative.com/public/audios/quizzes/2021-02/2.pr.mp3\" type=\"audio/mpeg\" />\n  Your browser does not support the audio tag.</audio></p>\n<p><span style=\"color: #2f346d; font-family: georgia, palatino, serif; font-size: 14pt;\">1- He started working as a magician when he was ________</span></p>', '[\"17\",\"27\"]', '[\"17\"]', NULL, NULL, NULL, 2, NULL, 3, '2021-02-08 03:48:27', '2021-02-09 06:53:12'),
(62, 'Single Or Multi Choice', 'questions/5355', '<p><span style=\"color: #2f346d; font-family: georgia, palatino, serif; font-size: 14pt;\">2- He used to do _______ shows a day.</span></p>', '[\"12\",\"20\"]', '[\"20\"]', NULL, NULL, NULL, 2, NULL, 3, '2021-02-08 03:49:02', '2021-02-08 03:49:02'),
(63, 'Single Or Multi Choice', 'questions/20549', '<p><span style=\"color: #2f346d; font-family: georgia, palatino, serif; font-size: 14pt;\">3- He tried to sell his magic secrets for _______</span></p>', '[\"$20\",\"$2000\"]', '[\"$20\"]', NULL, NULL, NULL, 2, NULL, 3, '2021-02-08 03:49:41', '2021-02-08 03:52:04'),
(64, 'Single Or Multi Choice', 'questions/51318', '<p><span style=\"color: #2f346d; font-family: georgia, palatino, serif; font-size: 14pt;\">4- He practiced opening handcuffs for ______ hours a day.</span></p>', '[\"2\",\"10\"]', '[\"10\"]', NULL, NULL, NULL, 2, NULL, 3, '2021-02-08 03:50:17', '2021-02-08 03:52:12'),
(65, 'Single Or Multi Choice', 'questions/5449', '<p><span style=\"color: #2f346d; font-family: georgia, palatino, serif; font-size: 14pt;\">5- He used to hide pieces of wire ___________</span></p>', '[\"in the water tank\",\"on his body\"]', '[\"on his body\"]', NULL, NULL, NULL, 2, NULL, 3, '2021-02-08 03:51:14', '2021-02-08 03:51:14'),
(66, 'Single Or Multi Choice', 'questions/8397', '<p><span style=\"color: #2f346d; font-family: georgia, palatino, serif; font-size: 14pt;\">6- He died because of ________</span></p>', '[\"stomach problems\",\"an accident\"]', '[\"stomach problems\"]', NULL, NULL, NULL, 2, NULL, 3, '2021-02-08 03:51:51', '2021-02-08 03:51:51'),
(67, 'Single Or Multi Choice', 'questions/33721', '<p><span style=\"text-decoration: underline; color: #000000;\"><span style=\"font-family: georgia, palatino, serif; font-size: 14pt;\">Choose the correct answer.</span></span></p>\n<p><span style=\"color: #2f346d; font-family: georgia, palatino, serif; font-size: 14pt;\">1- ___________ I was exhausted, I went to the party.</span></p>', '[\"Although\",\"However\"]', '[\"Although\"]', NULL, NULL, NULL, 4, NULL, 4, '2021-02-08 03:57:45', '2021-02-09 06:52:55'),
(68, 'Single Or Multi Choice', 'questions/71750', '<p><span style=\"font-family: georgia, palatino, serif; font-size: 14pt; color: #2f346d;\">2- ________________, I couldn\'t sleep.</span></p>', '[\"Although being tired\",\"However being tired\",\"Despite being tired\"]', '[\"Despite being tired\"]', NULL, NULL, NULL, 4, NULL, 4, '2021-02-08 04:05:59', '2021-02-09 06:30:17'),
(69, 'Single Or Multi Choice', 'questions/90452', '<p><span style=\"font-family: georgia, palatino, serif; font-size: 14pt; color: #2f346d;\">3- I ________________ hard since I was a student until now.</span></p>', '[\"am used to working\",\"am used to work\",\"used to work\"]', '[\"am used to working\"]', NULL, NULL, NULL, 4, NULL, 4, '2021-02-08 04:09:31', '2021-02-08 04:09:31'),
(71, 'Single Or Multi Choice', 'questions/73093', '<p><span style=\"font-family: georgia, palatino, serif; font-size: 14pt; color: #2f346d;\">4- When I left home, I realized that I __________ my keys inside.</span></p>', '[\"had forgotten\",\"forgot\",\"have forgotten\"]', '[\"had forgotten\"]', NULL, NULL, NULL, 4, NULL, 4, '2021-02-08 04:11:14', '2021-02-08 04:11:14'),
(72, 'Single Or Multi Choice', 'questions/46312', '<p><span style=\"font-family: georgia, palatino, serif; font-size: 14pt; color: #2f346d;\">5- I\'d like to _____________ my old friends.</span></p>', '[\"keep touch with\",\"keep in touch with\",\"keep touching\"]', '[\"keep in touch with\"]', NULL, NULL, NULL, 4, NULL, 4, '2021-02-08 04:13:20', '2021-02-09 06:30:51'),
(73, 'Single Or Multi Choice', 'questions/33068', '<p><span style=\"font-family: georgia, palatino, serif; font-size: 14pt; color: #2f346d;\">6- My parents _________ up two years ago.</span></p>', '[\"splitted\",\"have splitted\",\"split\"]', '[\"split\"]', NULL, NULL, NULL, 4, NULL, 4, '2021-02-08 04:14:40', '2021-02-08 04:14:40'),
(74, 'Single Or Multi Choice', 'questions/2718', '<p><span style=\"font-family: georgia, palatino, serif; font-size: 14pt; color: #2f346d;\">7- My manager told me __________ go there.</span></p>', '[\"not to\",\"don\'t\",\"didn\'t\"]', '[\"not to\"]', NULL, NULL, NULL, 4, NULL, 4, '2021-02-08 04:16:04', '2021-02-09 06:31:30'),
(75, 'Fill In Blank', 'questions/9950', '<p><span style=\"text-decoration: underline; color: #000000;\"><span style=\"font-family: georgia, palatino, serif; font-size: 14pt;\">8- Complete the table in the gaps below it.</span></span></p>\n<table style=\"border-collapse: collapse; width: 85.7193%; height: 85px;\" border=\"1\">\n<tbody>\n<tr style=\"height: 17px;\">\n<td style=\"width: 30.0772%; height: 17px;\"><span style=\"color: #e03e2d;\"><strong>&nbsp;verb</strong></span></td>\n<td style=\"width: 31.297%; height: 17px;\"><span style=\"color: #e03e2d;\"><strong>&nbsp;noun</strong></span></td>\n<td style=\"width: 34.6279%; height: 17px;\"><span style=\"color: #e03e2d;\"><strong>&nbsp;adjective</strong></span></td>\n</tr>\n<tr style=\"height: 17px;\">\n<td style=\"width: 30.0772%; height: 17px;\">&nbsp;attract</td>\n<td style=\"width: 31.297%; height: 17px;\">&nbsp;attraction</td>\n<td style=\"width: 34.6279%; height: 17px;\">&nbsp;attractive</td>\n</tr>\n<tr style=\"height: 17px;\">\n<td style=\"width: 30.0772%; height: 17px;\">&nbsp;care</td>\n<td style=\"width: 31.297%; height: 17px;\">&nbsp;(1) ..............</td>\n<td style=\"width: 34.6279%; height: 17px;\">&nbsp;careful</td>\n</tr>\n<tr style=\"height: 17px;\">\n<td style=\"width: 30.0772%; height: 17px;\">&nbsp;enjoy</td>\n<td style=\"width: 31.297%; height: 17px;\">&nbsp;enjoyment</td>\n<td style=\"width: 34.6279%; height: 17px;\">&nbsp;(2) ..............</td>\n</tr>\n<tr style=\"height: 17px;\">\n<td style=\"width: 30.0772%; height: 17px;\">&nbsp;disappoint&nbsp;</td>\n<td style=\"width: 31.297%; height: 17px;\">&nbsp;(3) ..............</td>\n<td style=\"width: 34.6279%; height: 17px;\">&nbsp;disappointed</td>\n</tr>\n</tbody>\n</table>\n<p>&nbsp;</p>', '[\"[\\\"care\\\"]\",\"[\\\"enjoyable\\\"]\",\"[\\\"disappointment\\\"]\"]', '[[[\"care\"],[\"enjoyable\"],[\"disappointment\"]]]', NULL, NULL, NULL, 6, NULL, 4, '2021-02-08 04:21:07', '2022-06-13 08:18:34'),
(76, 'Single Or Multi Choice', 'questions/24883', '<p><span style=\"color: #000000;\"><span style=\"text-decoration: underline;\"><span style=\"font-family: georgia, palatino, serif; font-size: 14pt;\">Listen to Sandy , Miranda and Barry talking about important moments<strong>&nbsp;</strong>in their lives and </span></span><span style=\"text-decoration: underline;\"><span style=\"font-family: georgia, palatino, serif; font-size: 14pt;\">choose the correct answer.</span></span></span></p>\n<p><audio controls=\"controls\">\n  <source src=\"https://test.webdative.com/public/audios/quizzes/2021-02/3-INt.mp3\" type=\"audio/mpeg\" />\n  Your browser does not support the audio tag.</audio></p>\n<p><span style=\"font-family: georgia, palatino, serif; font-size: 14pt; color: #171b72;\">1- Sandy met his wife _________.</span></p>', '[\"in Australia\",\"at a party\"]', '[\"at a party\"]', NULL, NULL, NULL, 4, NULL, 5, '2021-02-08 04:37:37', '2021-02-09 06:19:47'),
(77, 'Single Or Multi Choice', 'questions/300', '<p><span style=\"font-family: georgia, palatino, serif; font-size: 14pt; color: #2f346d;\">2- Paula stayed in England because she had ___________.</span></p>', '[\"missed her flight\",\"lost her passport\"]', '[\"lost her passport\"]', NULL, NULL, NULL, 4, NULL, 5, '2021-02-08 04:39:40', '2021-02-08 05:56:37'),
(78, 'Single Or Multi Choice', 'questions/1599', '<p><span style=\"font-family: georgia, palatino, serif; font-size: 14pt; color: #2f346d;\">3- Miranda _____________________.</span></p>', '[\"got into financial trouble\",\"lost her job\"]', '[\"lost her job\"]', NULL, NULL, NULL, 4, NULL, 5, '2021-02-08 04:40:28', '2021-02-08 05:56:44'),
(79, 'Single Or Multi Choice', 'questions/53843', '<p><span style=\"font-family: georgia, palatino, serif; font-size: 14pt; color: #2f346d;\">4- Miranda now lives in ______________.</span></p>', '[\"London\",\"the country\"]', '[\"the country\"]', NULL, NULL, NULL, 4, NULL, 5, '2021-02-08 04:41:06', '2021-02-08 05:57:28'),
(80, 'Single Or Multi Choice', 'questions/27913', '<p><span style=\"font-family: georgia, palatino, serif; font-size: 14pt; color: #2f346d;\">5- Barry was ______________ teenager.</span></p>', '[\"an aggressive\",\"a hard-working\"]', '[\"an aggressive\"]', NULL, NULL, NULL, 4, NULL, 5, '2021-02-08 04:41:58', '2021-02-08 05:57:34'),
(81, 'Single Or Multi Choice', 'questions/79119', '<p><span style=\"font-family: georgia, palatino, serif; font-size: 14pt; color: #2f346d;\">6- Barry asked for _________________ for his birthday.</span></p>', '[\"some boxing gloves\",\"a bike\"]', '[\"a bike\"]', NULL, NULL, NULL, 4, NULL, 5, '2021-02-08 04:42:40', '2021-02-08 05:57:40'),
(82, 'Fill In Blank', 'questions/31941', '<p><span style=\"text-decoration: underline; color: #000000;\"><span style=\"font-family: georgia, palatino, serif; font-size: 14pt;\">&nbsp;Complete the table in the gaps below it.</span></span></p>\n<table style=\"border-collapse: collapse; width: 80.2639%; height: 85px;\" border=\"1\">\n<tbody>\n<tr style=\"height: 17px;\">\n<td style=\"width: 30.5684%; height: 17px;\"><span style=\"color: #e03e2d;\"><strong>&nbsp;Crime (n.)</strong></span></td>\n<td style=\"width: 33.9637%; height: 17px;\"><span style=\"color: #e03e2d;\"><strong>&nbsp;Criminal (adj)</strong></span></td>\n<td style=\"width: 31.5294%; height: 17px; text-align: left;\"><span style=\"color: #e03e2d;\"><strong>&nbsp;Verb</strong></span></td>\n</tr>\n<tr style=\"height: 17px;\">\n<td style=\"width: 30.5684%; height: 17px;\"><span style=\"color: #e03e2d;\"><strong>&nbsp;</strong></span>(1) .............</td>\n<td style=\"width: 33.9637%; height: 17px;\"><span style=\"color: #e03e2d;\"><strong>&nbsp;</strong></span>looter&nbsp;</td>\n<td style=\"width: 31.5294%; height: 17px;\"><span style=\"color: #e03e2d;\"><strong>&nbsp;</strong></span>loot</td>\n</tr>\n<tr style=\"height: 17px;\">\n<td style=\"width: 30.5684%; height: 17px;\"><span style=\"color: #e03e2d;\"><strong>&nbsp;</strong></span>(2) .............</td>\n<td style=\"width: 33.9637%; height: 17px;\"><span style=\"color: #e03e2d;\"><strong>&nbsp;</strong></span>robber</td>\n<td style=\"width: 31.5294%; height: 17px;\"><span style=\"color: #e03e2d;\"><strong>&nbsp;</strong></span>rob</td>\n</tr>\n<tr style=\"height: 17px;\">\n<td style=\"width: 30.5684%; height: 17px;\"><span style=\"color: #e03e2d;\"><strong>&nbsp;</strong></span>burglary</td>\n<td style=\"width: 33.9637%; height: 17px;\"><span style=\"color: #e03e2d;\"><strong>&nbsp;</strong></span>burglar</td>\n<td style=\"width: 31.5294%; height: 17px;\"><span style=\"color: #e03e2d;\"><strong>&nbsp;</strong></span>(3) .............</td>\n</tr>\n<tr style=\"height: 17px;\">\n<td style=\"width: 30.5684%; height: 17px;\"><span style=\"color: #e03e2d;\"><strong>&nbsp;</strong></span>vadalism</td>\n<td style=\"width: 33.9637%; height: 17px;\"><span style=\"color: #e03e2d;\"><strong>&nbsp;</strong></span>vandal</td>\n<td style=\"width: 31.5294%; height: 17px;\"><span style=\"color: #e03e2d;\"><strong>&nbsp;</strong></span>(4) ..............</td>\n</tr>\n</tbody>\n</table>\n<p>&nbsp;</p>', '[\"[\\\"looting\\\"]\",\"[\\\"robbery\\\"]\",\"[\\\"burgle\\\"]\",\"[\\\"vandalize\\\"]\"]', '[[[\"looting\"],[\"robbery\"],[\"burgle\"],[\"vandalize\"]]]', NULL, NULL, NULL, 8, NULL, 5, '2021-02-08 04:49:15', '2022-06-13 06:24:50'),
(84, 'Single Or Multi Choice', 'questions/18570', '<p><span style=\"text-decoration: underline; color: #000000;\"><span style=\"font-family: georgia, palatino, serif; font-size: 14pt;\">Which choice gives the same meaning of the following phrases:</span></span></p>\n<p><span style=\"font-family: georgia, palatino, serif; font-size: 12pt; color: #2f346d;\">1- give someone the ax.</span></p>', '[\"fire someone form a job\",\"give someone a job\"]', '[\"fire someone form a job\"]', NULL, NULL, NULL, 4, NULL, 5, '2021-02-08 04:57:27', '2021-02-09 06:51:21'),
(85, 'Single Or Multi Choice', 'questions/85851', '<p><span style=\"font-family: georgia, palatino, serif; font-size: 12pt; color: #2f346d;\">2- an arm and a leg</span></p>', '[\"a lot of money\",\"strong\"]', '[\"a lot of money\"]', NULL, NULL, NULL, 4, NULL, 5, '2021-02-08 04:57:58', '2021-02-18 22:23:34'),
(86, 'Single Or Multi Choice', 'questions/578', '<p><span style=\"font-family: georgia, palatino, serif; font-size: 12pt; color: #2f346d;\">3- have a frog in <span style=\"font-size: 16px; font-variant: normal; font-weight: 400; text-decoration: none; text-indent: 0px;\">your</span> throat</span></p>', '[\"have a rough voice\",\"have a difficulty in speaking\"]', '[\"have a difficulty in speaking\"]', NULL, NULL, NULL, 4, NULL, 5, '2021-02-08 04:58:37', '2021-02-09 05:59:40'),
(90, 'Fill In Blank', 'questions/45874', '<p><span style=\"text-decoration: underline; color: #000000;\"><span style=\"font-family: georgia, palatino, serif; font-size: 14pt;\">Complete the radio news with words from the box.</span></span></p>\n<table style=\"border-collapse: collapse; width: 100%;\" border=\"1\">\n<tbody>\n<tr>\n<td style=\"width: 98.5336%;\">\n<p style=\"text-align: left;\"><span style=\"font-size: 14pt; color: #e03e2d;\">&nbsp; &nbsp; &nbsp;sued&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; crisis&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;outcry&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;hostage&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;flee&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;damages&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;invaded&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;attack&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;released&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;spread&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;troops</span></p>\n</td>\n</tr>\n</tbody>\n</table>\n<p style=\"text-align: left;\"><span style=\"color: #2f346d; font-family: georgia, palatino, serif; font-size: 12pt;\">- TV soap opera Sally Woodham has successfully (1) ..................... the daily news for saying that she was&nbsp;</span></p>\n<p><span style=\"color: #2f346d; font-family: georgia, palatino, serif; font-size: 12pt;\">about to divorce her husband. The paper has been ordered to pay her $250,000 (2).....................</span></p>\n<p><span style=\"color: #2f346d; font-family: georgia, palatino, serif; font-size: 12pt;\">- The cold weather is causing the new \'super-flu\' virus to (3)..................... faster than expected.&nbsp;</span></p>\n<p><span style=\"color: #2f346d; font-family: georgia, palatino, serif; font-size: 12pt;\">A spokesperson has called it the biggest health (4) ..................... for many years. </span></p>\n<p><span style=\"color: #2f346d; font-family: georgia, palatino, serif; font-size: 12pt;\">- The (5) ..................... </span><span style=\"color: #2f346d; font-family: georgia, palatino, serif; font-size: 12pt;\">who was talking during a bank robbery in Leeds last night has been (6) ..................... safely. </span></p>\n<p><span style=\"color: #2f346d; font-family: georgia, palatino, serif; font-size: 12pt;\">Police&nbsp;</span><span style=\"color: #2f346d; font-family: georgia, palatino, serif; font-size: 12pt;\">have arrested the robbers.</span></p>\n<p><span style=\"color: #2f346d; font-family: georgia, palatino, serif; font-size: 12pt;\">- The Pacific state of Temalu has been (7) ..................... by hundreds of (8) .....................from the nearby island of Manaka.</span></p>\n<p><span style=\"color: #2f346d; font-family: georgia, palatino, serif; font-size: 12pt;\">Many people have decided to (9) ..................... into the mountains for safety.</span></p>\n<p><span style=\"color: #2f346d; font-family: georgia, palatino, serif; font-size: 12pt;\">- 89-year old Jan Biggs was mugged un the village of Firewall last night. The (10) ..................... has cost an (11) .....................</span></p>\n<p><span style=\"color: #2f346d; font-family: georgia, palatino, serif; font-size: 12pt;\"> among </span><span style=\"color: #2f346d; font-family: georgia, palatino, serif; font-size: 12pt;\">local residents, who are demanding better policing.</span></p>', '[\"[\\\"sued\\\"]\",\"[\\\"damages\\\"]\",\"[\\\"spread\\\"]\",\"[\\\"crisis\\\"]\",\"[\\\"hostage\\\"]\",\"[\\\"released\\\"]\",\"[\\\"invaded\\\"]\",\"[\\\"troops\\\"]\",\"[\\\"flee\\\"]\",\"[\\\"attack\\\"]\",\"[\\\"outcry\\\"]\"]', '[[[\"sued\"],[\"damages\"],[\"spread\"],[\"crisis\"],[\"hostage\"],[\"released\"],[\"invaded\"],[\"troops\"],[\"flee\"],[\"attack\"],[\"outcry\"]]]', NULL, NULL, NULL, 30, NULL, 5, '2021-02-08 05:26:11', '2022-06-13 08:13:40'),
(91, 'True Or False', 'questions/1178', '<p><span style=\"text-decoration: underline;\"><span style=\"color: #000000; font-family: georgia, palatino, serif; text-decoration: underline;\"><span style=\"font-size: 18.6667px;\">Listen to the record and answer the questions.</span></span></span></p>\n<p><audio controls=\"controls\">\n  <source src=\"https://test.webdative.com/public/audios/quizzes/2021-02/4-U.mp3\" type=\"audio/mpeg\" />\n  Your browser does not support the audio tag.</audio></p>\n<p><span style=\"color: #2f346d; font-family: georgia, palatino, serif; font-size: 12pt;\">A) Polly thinks women should stop working.</span></p>', '[\"True\",\"False\"]', '[\"False\"]', NULL, NULL, NULL, 5, NULL, 5, '2021-02-08 05:30:25', '2021-02-09 06:28:03'),
(92, 'True Or False', 'questions/16538', '<p><span style=\"font-size: 12pt;\"><span style=\"color: #171b72; font-family: georgia, palatino, serif;\">B) Naomi didn\'t read <strong><em>Why Men Lie and Women Cry?</em></strong></span></span></p>', '[\"True\",\"False\"]', '[\"True\"]', NULL, NULL, NULL, 5, NULL, 5, '2021-02-08 05:31:33', '2021-02-08 05:58:48'),
(93, 'True Or False', 'questions/68384', '<p><span style=\"color: #171b72; font-family: georgia, palatino, serif;\"><span style=\"font-size: 16px;\">C) Matt think that <strong><em>Why Men Lie and Women Cry?</em></strong> is really bad.</span></span></p>', '[\"True\",\"False\"]', '[\"False\"]', NULL, NULL, NULL, 5, NULL, 5, '2021-02-08 05:32:30', '2021-02-08 05:58:55'),
(94, 'True Or False', 'questions/87111', '<p><span style=\"color: #171b72; font-family: georgia, palatino, serif;\"><span style=\"font-size: 16px;\">D) Women talk about problems to find sympathy more than solutions.</span></span></p>', '[\"True\",\"False\"]', '[\"True\"]', NULL, NULL, NULL, 5, NULL, 5, '2021-02-08 05:33:31', '2021-02-08 05:59:00'),
(95, 'Single Or Multi Choice', 'questions/27997', '<p><span style=\"color: #171b72; font-family: georgia, palatino, serif;\"><span style=\"font-size: 16px;\">E) Women use ________ more words in a day than a man.</span></span></p>', '[\"two times\",\"three times\",\"four times\"]', '[\"three times\"]', NULL, NULL, NULL, 5, NULL, 5, '2021-02-08 05:34:53', '2021-02-08 05:59:06');
INSERT INTO `quizzes` (`id`, `question_type`, `slug`, `description`, `option_text`, `correct_answer`, `hint`, `correct_massage`, `incorrect_massage`, `score`, `order`, `section_id`, `created_at`, `updated_at`) VALUES
(96, 'Free Answer', 'questions/61638', '<p><span style=\"font-family: georgia, palatino, serif; font-size: 14pt;\">Imagine you are an employee at a company and you have concerns about a company policy or working condition.</span></p>\n<p><span style=\"text-decoration: underline;\"><span style=\"font-family: georgia, palatino, serif; font-size: 12pt; color: #e03e2d; text-decoration: underline;\">Write an e-mail to your manager using formal language and proper punctuation. You can discuss the following:</span></span></p>\n<ul>\n<li>\n<p><span style=\"font-size: 12pt; color: #236fa1;\"><span style=\"font-family: georgia, palatino, serif;\">You can discuss the</span><span style=\"font-family: georgia, palatino, serif;\"> benefits and drawbacks of the company\'s policy and the working conditions.</span></span></p>\n</li>\n<li>\n<p><span style=\"font-size: 12pt; color: #236fa1;\"><span style=\"font-family: georgia, palatino, serif;\">An introduction explaining who you are and what policy or working condition you are concerned about</span></span></p>\n</li>\n<li>\n<p><span style=\"font-size: 12pt; color: #236fa1;\"><span style=\"font-family: georgia, palatino, serif;\">A clear description of your concerns, including any potential issues or problems this policy or condition could cause.</span></span></p>\n</li>\n<li>\n<p><span style=\"font-size: 12pt; color: #236fa1;\"><span style=\"font-family: georgia, palatino, serif;\">Any suggestions or ideas you have for improving the policy or working condition.</span></span></p>\n</li>\n<li>\n<p><span style=\"font-size: 12pt; color: #236fa1;\"><span style=\"font-family: georgia, palatino, serif;\">A request for a meeting or discussion with your manager to further discuss your concerns.</span></span></p>\n</li>\n</ul>', '[]', '[]', NULL, NULL, NULL, 20, NULL, 6, '2021-02-08 05:49:50', '2023-04-07 23:06:15'),
(99, 'Record', 'questions/81979', '<p><span style=\"font-size: 14pt; color: #34495e; font-family: georgia, palatino, serif;\">Speak for&nbsp;<strong>at least 1 minute </strong>about yourself: your family, your job, your daily routine, your future plans, and anything else you want to add.</span></p>\n<p><span style=\"color: #e03e2d;\">Note: You can only record one time. It\'s recommended to take some notes before starting so you can fill the 1 minute. After you are done recording, please click on the save button and wait for a moment until it\'s done.</span></p>', '[]', '[[]]', NULL, NULL, NULL, 30, NULL, 6, '2022-06-12 00:02:35', '2023-04-07 23:06:50'),
(100, 'Record', 'questions/37179', '<p>&nbsp;</p>\n<p><span style=\"font-size: 14pt; color: #34495e; font-family: georgia, palatino, serif;\">Speak for <strong>at least 1 minute</strong> about a movie you watched before, Or a nice vacation or a day-out you had with friends or family.</span></p>\n<p><span style=\"color: #e03e2d;\">Note: You can only record one time. It\'s recommended to take some notes before starting so you can fill the 1 minute. After you are done recording, please click on the save button and wait for a moment until it\'s done.</span></p>', '[]', '[[]]', NULL, NULL, NULL, 30, NULL, 6, '2022-06-12 00:03:55', '2023-04-07 23:06:58');

-- --------------------------------------------------------

--
-- Table structure for table `quran_courses`
--

CREATE TABLE `quran_courses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `code` varchar(255) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `quran_courses`
--

INSERT INTO `quran_courses` (`id`, `name`, `slug`, `parent_id`, `category_id`, `code`, `active`, `created_at`, `updated_at`) VALUES
(1, 'Quran for Beginners', 'quran-for-beginners\r\n', 0, NULL, NULL, 1, NULL, NULL),
(2, 'Quran Memorization with Tajweed', 'quran-memorization-with-tajweed', 0, NULL, NULL, 1, NULL, NULL),
(3, 'The Arabic Language', 'the-arabic-language', 0, NULL, NULL, 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `quran_course_translations`
--

CREATE TABLE `quran_course_translations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `course_id` int(11) NOT NULL,
  `lang` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `quran_course_translations`
--

INSERT INTO `quran_course_translations` (`id`, `title`, `slug`, `course_id`, `lang`, `created_at`, `updated_at`) VALUES
(1, 'Quran for Beginners', 'quran-for-beginners\r\n', 1, 'en', NULL, NULL),
(2, 'Quran Memorization with Tajweed', 'quran-memorization-with-tajweed', 2, 'en', NULL, NULL),
(3, 'The Arabic Language', 'the-arabic-language', 3, 'en', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `display_name` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `display_name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin', 'admin', NULL, NULL),
(2, 'student', 'student', 'student', NULL, NULL),
(3, 'course', 'course', 'course', NULL, NULL),
(4, 'org', 'org', 'org', NULL, NULL),
(5, 'course_student', 'course_student', 'course_student', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `role_user`
--

CREATE TABLE `role_user` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `user_type` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_user`
--

INSERT INTO `role_user` (`role_id`, `user_id`, `user_type`) VALUES
(1, 9, 'App\\Models\\User'),
(2, 4, 'App\\Models\\User'),
(2, 5, 'App\\Models\\User'),
(2, 6, 'App\\Models\\User'),
(2, 42, 'App\\Models\\User'),
(2, 43, 'App\\Models\\User'),
(2, 44, 'App\\Models\\User'),
(2, 45, 'App\\Models\\User'),
(2, 46, 'App\\Models\\User'),
(2, 47, 'App\\Models\\User'),
(2, 48, 'App\\Models\\User'),
(2, 49, 'App\\Models\\User'),
(2, 50, 'App\\Models\\User'),
(2, 51, 'App\\Models\\User'),
(2, 52, 'App\\Models\\User'),
(2, 53, 'App\\Models\\User'),
(2, 54, 'App\\Models\\User'),
(2, 55, 'App\\Models\\User'),
(2, 56, 'App\\Models\\User'),
(2, 57, 'App\\Models\\User'),
(2, 58, 'App\\Models\\User'),
(2, 59, 'App\\Models\\User'),
(2, 60, 'App\\Models\\User'),
(2, 61, 'App\\Models\\User'),
(2, 62, 'App\\Models\\User'),
(2, 63, 'App\\Models\\User'),
(2, 64, 'App\\Models\\User'),
(2, 65, 'App\\Models\\User'),
(2, 66, 'App\\Models\\User'),
(2, 67, 'App\\Models\\User'),
(2, 68, 'App\\Models\\User'),
(2, 69, 'App\\Models\\User'),
(2, 70, 'App\\Models\\User'),
(2, 71, 'App\\Models\\User'),
(2, 72, 'App\\Models\\User'),
(2, 73, 'App\\Models\\User'),
(2, 74, 'App\\Models\\User'),
(2, 75, 'App\\Models\\User'),
(2, 76, 'App\\Models\\User'),
(2, 77, 'App\\Models\\User'),
(2, 78, 'App\\Models\\User'),
(2, 79, 'App\\Models\\User'),
(2, 80, 'App\\Models\\User'),
(2, 81, 'App\\Models\\User'),
(2, 82, 'App\\Models\\User'),
(2, 83, 'App\\Models\\User'),
(4, 15, 'App\\Models\\User'),
(4, 16, 'App\\Models\\User'),
(4, 17, 'App\\Models\\User'),
(4, 18, 'App\\Models\\User'),
(4, 19, 'App\\Models\\User'),
(4, 20, 'App\\Models\\User'),
(4, 21, 'App\\Models\\User'),
(4, 31, 'App\\Models\\User');

-- --------------------------------------------------------

--
-- Table structure for table `sections`
--

CREATE TABLE `sections` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `sec_desc` text DEFAULT NULL,
  `audio` varchar(255) DEFAULT NULL,
  `video` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sections`
--

INSERT INTO `sections` (`id`, `title`, `sec_desc`, `audio`, `video`, `created_at`, `updated_at`) VALUES
(1, 'Step One', NULL, NULL, NULL, '2021-02-06 22:32:10', '2021-02-06 22:32:10'),
(2, 'Step Two', NULL, NULL, NULL, '2021-02-07 18:35:07', '2021-02-07 18:35:07'),
(3, 'Step Three', NULL, NULL, NULL, '2021-02-08 02:47:42', '2021-02-08 02:47:42'),
(4, 'Step Four', NULL, NULL, NULL, '2021-02-08 03:55:43', '2021-02-08 03:55:43'),
(5, 'Step Five', NULL, NULL, NULL, '2021-02-08 04:43:24', '2021-02-08 04:43:24'),
(6, 'Step Six', NULL, NULL, NULL, '2021-02-08 05:43:59', '2021-02-08 05:43:59');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `promocode` varchar(255) DEFAULT NULL,
  `parent_first_name` varchar(255) DEFAULT NULL,
  `parent_last_name` varchar(255) DEFAULT NULL,
  `parent_email` text DEFAULT NULL,
  `parent_phone` varchar(255) DEFAULT NULL,
  `stu_first_name` varchar(255) DEFAULT NULL,
  `stu_last_name` varchar(255) DEFAULT NULL,
  `stu_email` text DEFAULT NULL,
  `stu_phone` varchar(255) DEFAULT NULL,
  `user_name` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `decr_password` varchar(255) DEFAULT NULL,
  `gender` varchar(255) DEFAULT NULL,
  `age` varchar(255) DEFAULT NULL,
  `code` varchar(255) DEFAULT NULL,
  `school_name` varchar(255) DEFAULT NULL,
  `active` int(11) DEFAULT 0,
  `course_active` int(11) NOT NULL DEFAULT 0,
  `paid` int(11) NOT NULL DEFAULT 0,
  `level_id` varchar(255) DEFAULT NULL,
  `course_id` int(11) DEFAULT NULL,
  `sub_course_id` int(11) DEFAULT NULL,
  `quran_course_id` int(11) DEFAULT NULL,
  `grade_id` int(11) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `class_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `promocode`, `parent_first_name`, `parent_last_name`, `parent_email`, `parent_phone`, `stu_first_name`, `stu_last_name`, `stu_email`, `stu_phone`, `user_name`, `password`, `decr_password`, `gender`, `age`, `code`, `school_name`, `active`, `course_active`, `paid`, `level_id`, `course_id`, `sub_course_id`, `quran_course_id`, `grade_id`, `category_id`, `class_id`, `created_at`, `updated_at`) VALUES
(56, NULL, NULL, NULL, NULL, NULL, 'Fathya', 'Sheikh', 'amiko00@hotmail.com', '07452701177', 'fathya_St056', '$2y$10$W5KA9gtWmYHb0Z6KKgzGw.6MEqwZ5TnRoeMy8N.Hoow2zB9ATP0N6', 'Fathya_33735', 'female', '22', 'St056', NULL, 0, 0, 0, NULL, 2, NULL, 1, NULL, NULL, NULL, '2023-07-19 22:46:46', '2023-07-19 22:46:46'),
(57, NULL, NULL, NULL, NULL, NULL, 'youssef', 'Oumar', 'youssefsokhna12@gmail.com', '+22227022541', 'youssef_St057', '$2y$10$XC2qWDxV15eiM.yOqePkr.cJRMlrYOW0wZDEFhQQiF/SrNauK//CC', 'Youssef_44414', 'male', '25', 'St057', NULL, 0, 0, 0, NULL, 2, NULL, 2, NULL, NULL, NULL, '2023-08-13 20:18:39', '2023-08-13 20:18:39'),
(58, NULL, NULL, NULL, NULL, NULL, 'Gorgui', 'Diallo', 'gorguisdiallo@gmail.com', '+221762997878', 'gorgui_St058', '$2y$10$rrfUBvitDRofFa9uvCuIL.5JmZ0IG3dqW1w0.nGw.p/f.uyUCaHmC', 'Gorgui_67351', 'male', '58', 'St058', NULL, 0, 0, 0, NULL, 2, NULL, 2, NULL, NULL, NULL, '2023-08-26 02:07:48', '2023-08-26 02:07:48'),
(59, NULL, 'Osama', 'Cc', 'osama.elazab22@gmail.com', '010', 'Ah', 'Ah', NULL, NULL, 'ah_St059', '$2y$10$pNv1Uz8sOWmGN5992gaWteoFq0eN9A6v8mVvpWBjPbPwraxJeWZBW', 'Ah_10014', 'male', '6', 'St059', NULL, 0, 0, 0, NULL, 1, NULL, 1, NULL, NULL, NULL, '2024-03-11 18:49:38', '2024-03-11 18:49:38'),
(60, NULL, NULL, NULL, NULL, NULL, 'Asmaa', 'Za', 'asmaazakaria009@gmail.com', '01185236987', 'asmaa_St060', '$2y$10$./vCv56GtU.O03YrJ8KfG.9ldGv.uAXMDkv994DR5Y1vjOcdvNKHe', 'Asmaa_83981', 'female', '23', 'St060', NULL, 0, 0, 0, NULL, 2, NULL, 1, NULL, NULL, NULL, '2024-03-11 20:23:50', '2024-03-11 20:23:50'),
(61, NULL, NULL, NULL, NULL, NULL, 'Mostafa', 'Ahmed', 'admin@teacherusama.com', '01258746325', 'mostafa_St061', '$2y$10$iQVBJxzff0261nD5RqRnA.YgBm.DYRNxhrJHW1LTTUL9.Usqq0NPW', 'Mostafa_99588', 'male', '23', 'St061', NULL, 0, 0, 0, NULL, 2, NULL, 1, NULL, NULL, NULL, '2024-03-11 23:25:38', '2024-03-11 23:25:38'),
(62, NULL, NULL, NULL, NULL, NULL, 'Mostafa', 'Ahmed', 'osama.elaza232b22@gmail.com', '01258746328', 'mostafa_St062', '$2y$10$m5GJTG8m/YODirszGDz.5eNQ9i.9TbzA6o3Wa24v1PF8FbQ/LuwjO', 'Mostafa_9434', 'male', '19', 'St062', NULL, 0, 0, 0, NULL, 2, NULL, 2, NULL, NULL, NULL, '2024-03-11 23:30:17', '2024-03-11 23:30:17'),
(63, NULL, NULL, NULL, NULL, NULL, 'asdas', 'asd', 'oasssama.elazab22@gmail.com', '01254789652', NULL, NULL, NULL, 'female', '23', NULL, NULL, 0, 0, 0, NULL, 2, NULL, 1, NULL, NULL, NULL, '2024-03-11 23:46:11', '2024-03-11 23:46:11'),
(64, NULL, NULL, NULL, NULL, NULL, 'nnn', 'rtrt', 'aasmaa@gmail.com', '01258746358', 'nnn_St064', '$2y$10$00LW7cXFJGipF/yYPUd36.yaTcDlB92eFMOkmUzI2cczsgTRlbVJS', 'Nnn_92041', 'female', '29', 'St064', NULL, 0, 0, 0, NULL, 2, NULL, 1, NULL, NULL, NULL, '2024-03-12 11:46:25', '2024-03-12 11:46:25'),
(65, NULL, NULL, NULL, NULL, NULL, 'uyit', 'trdf', 'asmaase@gmail.com', '01258746321', 'uyit_St065', '$2y$10$M24aaVFimKXlPZnJ2QaKn.jGupfQEVEK1dzqgnxeUwjlhTHfrOupa', 'Uyit_52243', 'female', '19', 'St065', NULL, 0, 0, 0, NULL, 2, NULL, 1, NULL, NULL, NULL, '2024-03-12 11:48:48', '2024-03-12 11:48:48'),
(66, NULL, 'bdb', 'ddd', 'ww@yahoo.com', 'eddd', 'we', 'ee', NULL, NULL, 'we_St066', '$2y$10$Anw/YmqDt5qZ2Me6yhbYGusJ4WFUvzY3z9y3wllYg.YNyPCnKFRkK', 'We_96405', 'female', '20', 'St066', NULL, 0, 0, 0, NULL, 1, NULL, 1, NULL, NULL, NULL, '2024-06-02 15:06:26', '2024-06-02 15:06:26'),
(67, NULL, 'Shabnam', 'Bibi', 'shabnambibi19820@gmail.com', '07812192747', 'Khadija', 'Ahmed', NULL, NULL, 'khadija_St067', '$2y$10$PD9PA.xbRrfPIVItY3IAee1aEKrWr03xNDd.0cL36Vx1Y7gbW2Z4S', 'Khadija_74255', 'female', '12', 'St067', NULL, 0, 0, 0, NULL, 1, NULL, 1, NULL, NULL, NULL, '2024-07-11 11:14:47', '2024-07-11 11:14:47'),
(68, NULL, 'Shabnam', 'Bibi', 'shabnambibi19820@gmail.com', '07812192747', 'Fatima', 'Ahmed', NULL, NULL, 'fatima_St068', '$2y$10$QlwkaCzAottub4YSf2SLEO0BJ0XkYhPpT.A0s5N7Uaff2M3rHtWJm', 'Fatima_65550', 'female', '8', 'St068', NULL, 0, 0, 0, NULL, 1, NULL, 1, NULL, NULL, NULL, '2024-07-11 11:14:47', '2024-07-11 11:14:48'),
(69, NULL, NULL, NULL, NULL, NULL, 'Danny', 'Hill', 'wigandill89@gmail.com', '07738417450', 'danny_St069', '$2y$10$FX2x6ZGl976Yc1D7xShxRuErhlyDu7LD3itG2tSgiUhZKsPzNQHWC', 'Danny_58815', 'male', '30', 'St069', NULL, 0, 0, 0, NULL, 2, NULL, 1, NULL, NULL, NULL, '2025-01-24 20:10:49', '2025-01-24 20:10:49'),
(70, NULL, NULL, NULL, NULL, NULL, 'md ehsan', 'zaman', 'ehsanz.official@gmail.com', '07476267843', 'md_St070', '$2y$10$yZy5q9qzrU43TkDYVaIow.qfJQ46D4EPk2.R7DiIfCWCA.4Mit7OW', 'Md_66420', 'male', '23', 'St070', NULL, 0, 0, 0, NULL, 2, NULL, 1, NULL, NULL, NULL, '2025-04-30 08:30:46', '2025-04-30 08:30:46'),
(71, NULL, NULL, NULL, NULL, NULL, 'Richard', 'Arnold', 'wastecontrol@hotmail.com', '07912139478', 'richard_St071', '$2y$10$4JrmOHdmehehHcs7YcmVOe9.Zaf791L3pl1ZCwjHG4Z.LXdYjmQZm', 'Richard_26503', 'male', '40', 'St071', NULL, 0, 0, 0, NULL, 2, NULL, 1, NULL, NULL, NULL, '2025-05-01 12:34:19', '2025-05-01 12:34:19'),
(72, NULL, NULL, NULL, NULL, NULL, 'luca', 'campoli', 'patricgary420@gmail.com', '4168462941', 'luca_St072', '$2y$10$5PCbR4BmLVkiIo5nwHpsd.zeO/zNZa.QEKKWxwvDRqewbwf1YLznC', 'Luca_69762', 'male', '18', 'St072', NULL, 0, 0, 0, NULL, 2, NULL, 3, NULL, NULL, NULL, '2025-11-04 06:01:49', '2025-11-04 06:01:49'),
(73, NULL, NULL, NULL, NULL, NULL, 'Sunny', 'Patel', 'sunniness0526@gmail.com', '2819359870', 'sunny_St073', '$2y$10$TNm/tf0t3DX0ezpd9MNLO.bsb4dJIrm7XLAsVf.gfGa8KLvfzf3nO', 'Sunny_3620', 'male', '36', 'St073', NULL, 0, 0, 0, NULL, 2, NULL, 2, NULL, NULL, NULL, '2026-01-21 17:19:17', '2026-01-21 17:19:17');

-- --------------------------------------------------------

--
-- Table structure for table `student_quizzes`
--

CREATE TABLE `student_quizzes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `quiz_id` int(11) DEFAULT NULL,
  `student_id` int(11) DEFAULT NULL,
  `emp_answer` text DEFAULT NULL,
  `correct_answer` varchar(255) DEFAULT NULL,
  `check_answer` varchar(255) DEFAULT NULL,
  `emp_score` int(11) DEFAULT NULL,
  `score` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_quizzes`
--

INSERT INTO `student_quizzes` (`id`, `quiz_id`, `student_id`, `emp_answer`, `correct_answer`, `check_answer`, `emp_score`, `score`, `created_at`, `updated_at`) VALUES
(1, 18, 4, '[\"taller\"]', '[\"taller\"]', 'correct', 1, 1, '2023-01-08 12:06:13', '2023-01-08 12:06:13'),
(2, 19, 4, '[\"most\"]', '[\"most\"]', 'correct', 1, 1, '2023-01-08 12:06:13', '2023-01-08 12:06:13'),
(3, 23, 4, '[\"friendlier\"]', '[\"friendlier\"]', 'correct', 1, 1, '2023-01-08 12:06:13', '2023-01-08 12:06:13'),
(4, 28, 4, '[\"on\"]', '[\"on\"]', 'correct', 1, 1, '2023-01-08 12:06:13', '2023-01-08 12:06:13'),
(5, 29, 4, '[\"for\"]', '[\"for\"]', 'correct', 1, 1, '2023-01-08 12:06:13', '2023-01-08 12:06:13'),
(6, 30, 4, 'Does sally\'s grandmother live in Spain?', '[]', '', 0, 1, '2023-01-08 12:06:13', '2023-01-08 12:06:13'),
(7, 31, 4, 'How many children had Tom?', '[]', '', 0, 1, '2023-01-08 12:06:13', '2023-01-08 12:06:13'),
(8, 32, 4, 'Which time they get up every morning ?', '[]', '', 0, 1, '2023-01-08 12:06:13', '2023-01-08 12:06:13'),
(9, 33, 4, '[\"I didn\'t go to the cinema\\u00a0 last night.\\u00a0\"]', '[\"I didn\'t go to the cinema\\u00a0 last night.\\u00a0\"]', 'correct', 1, 1, '2023-01-08 12:06:13', '2023-01-08 12:06:13'),
(10, 34, 4, '[\"They haven\'t become very rich.\"]', '[\"They haven\'t become very rich.\"]', 'correct', 1, 1, '2023-01-08 12:06:13', '2023-01-08 12:06:13'),
(11, 35, 4, '[\"I don\'t have a big window in my room.\"]', '[\"I don\'t have a big window in my room.\"]', 'correct', 1, 1, '2023-01-08 12:06:13', '2023-01-08 12:06:13'),
(12, 36, 4, '[\"My mom hasn\'t perfect cooking skills.\"]', '[\"My mom doesn\'t have perfect cooking skills.\"]', 'incorrect', 0, 1, '2023-01-08 12:06:13', '2023-01-08 12:06:13'),
(13, 37, 4, '[\"ill\",\"quite\",\"difficult\",\"beautiful\",\"crowded\",\"intelligent\",\"rich\",\"friendly\",\"interesting\"]', '[\"ill\",\"quiet\",\"difficult\",\"beautiful\",\"crowded\",\"intelligent\",\"rich\",\"friendly\",\"interesting\"]', '[\"correct\",\"incorrect\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\"]', 0, 9, '2023-01-08 12:06:13', '2023-01-08 12:06:13'),
(14, 38, 4, '[\"Arrived\",\"want\",\"have you ever    went\",\"didn\'t understand\",\"are talking\",\"met\",\"came\",\"is doing\",\"needs\",\"is ringing\"]', '[\"arrived\",\"want\",\"have been\",\"don\'t understand\",\"talk\",\"met\",\"came\",\"is doing\",\"needs\",\"is ringing\"]', '[\"incorrect\",\"correct\",\"incorrect\",\"incorrect\",\"incorrect\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\"]', 0, 10, '2023-01-08 12:06:13', '2023-01-08 12:06:13'),
(15, 42, 4, '[\"24th of Feb\",\"13:20 24th of feb\",\"18: 45\",\"11 march\",\"5:15  sunday\",\"8:20 am\",\"259 pounds including the Taxes\"]', '[\"24\",\"13.20\",\"18.45\",\"11\",\"5.15\",\"8.20\",\"259\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 7, '2023-01-08 12:06:13', '2023-01-08 12:06:13'),
(16, 48, 4, '[\"speaking\",\"this is\",\"really sorry\",\"have to\",\"did she\",\"Oh, dear\",\"don\'t worry\",\"could you help me\",\"shall I\",\"that would be\"]', '[\"speaking\",\"this is\",\"really sorry\",\"have to\",\"Oh, dear\",\"did she\",\"don\'t worry\",\"could you help me\",\"shall I\",\"that would be\"]', '[\"correct\",\"correct\",\"correct\",\"correct\",\"incorrect\",\"incorrect\",\"correct\",\"correct\",\"correct\",\"correct\"]', 0, 10, '2023-01-08 12:06:13', '2023-01-08 12:06:13'),
(17, 49, 4, '[\"would accept\"]', '[\"would accept\"]', 'correct', 3, 3, '2023-01-08 12:06:13', '2023-01-08 12:06:13'),
(18, 50, 4, '[\"will pass\"]', '[\"will pass\"]', 'correct', 3, 3, '2023-01-08 12:06:13', '2023-01-08 12:06:13'),
(19, 51, 4, '[\"seeing\"]', '[\"seeing\"]', 'correct', 3, 3, '2023-01-08 12:06:13', '2023-01-08 12:06:13'),
(20, 52, 4, '[\"wanted -- next\"]', '[\"wanted -- the following\"]', 'incorrect', 0, 3, '2023-01-08 12:06:13', '2023-01-08 12:06:13'),
(21, 53, 4, '[\"which\"]', '[\"which\"]', 'correct', 3, 3, '2023-01-08 12:06:13', '2023-01-08 12:06:13'),
(22, 54, 4, '[\"where\"]', '[\"where\"]', 'correct', 3, 3, '2023-01-08 12:06:13', '2023-01-08 12:06:13'),
(23, 55, 4, '[\"were fired\"]', '[\"were fired\"]', 'correct', 3, 3, '2023-01-08 12:06:13', '2023-01-08 12:06:13'),
(24, 56, 4, '[\"give up\"]', '[\"give up\"]', 'correct', 3, 3, '2023-01-08 12:06:13', '2023-01-08 12:06:13'),
(25, 57, 4, '[\"go out with\"]', '[\"put up with\"]', 'incorrect', 0, 3, '2023-01-08 12:06:13', '2023-01-08 12:06:13'),
(26, 58, 4, '[\"impolite\"]', '[\"impolite\"]', 'correct', 3, 3, '2023-01-08 12:06:13', '2023-01-08 12:06:13'),
(27, 59, 4, '[\"niece\"]', '[\"niece\"]', 'correct', 3, 3, '2023-01-08 12:06:13', '2023-01-08 12:06:13'),
(28, 60, 4, '[\"stepmother\"]', '[\"mother-in-law\"]', 'incorrect', 0, 3, '2023-01-08 12:06:13', '2023-01-08 12:06:13'),
(29, 61, 4, '[\"17\"]', '[\"17\"]', 'correct', 2, 2, '2023-01-08 12:06:13', '2023-01-08 12:06:13'),
(30, 62, 4, '[\"20\"]', '[\"20\"]', 'correct', 2, 2, '2023-01-08 12:06:13', '2023-01-08 12:06:13'),
(31, 63, 4, '[\"$20\"]', '[\"$20\"]', 'correct', 2, 2, '2023-01-08 12:06:13', '2023-01-08 12:06:13'),
(32, 64, 4, '[\"10\"]', '[\"10\"]', 'correct', 2, 2, '2023-01-08 12:06:13', '2023-01-08 12:06:13'),
(33, 65, 4, '[\"on his body\"]', '[\"on his body\"]', 'correct', 2, 2, '2023-01-08 12:06:13', '2023-01-08 12:06:13'),
(34, 66, 4, '[\"stomach problems\"]', '[\"stomach problems\"]', 'correct', 2, 2, '2023-01-08 12:06:13', '2023-01-08 12:06:13'),
(35, 67, 4, '[\"Although\"]', '[\"Although\"]', 'correct', 4, 4, '2023-01-08 12:06:13', '2023-01-08 12:06:13'),
(36, 68, 4, '[\"Despite being tired\"]', '[\"Despite being tired\"]', 'correct', 4, 4, '2023-01-08 12:06:13', '2023-01-08 12:06:13'),
(37, 69, 4, '[\"used to work\"]', '[\"am used to working\"]', 'incorrect', 0, 4, '2023-01-08 12:06:13', '2023-01-08 12:06:13'),
(38, 71, 4, '[\"have forgotten\"]', '[\"had forgotten\"]', 'incorrect', 0, 4, '2023-01-08 12:06:13', '2023-01-08 12:06:13'),
(39, 72, 4, '[\"keep in touch with\"]', '[\"keep in touch with\"]', 'correct', 4, 4, '2023-01-08 12:06:13', '2023-01-08 12:06:13'),
(40, 73, 4, '[\"have splitted\"]', '[\"split\"]', 'incorrect', 0, 4, '2023-01-08 12:06:13', '2023-01-08 12:06:13'),
(41, 74, 4, '[\"not to\"]', '[\"not to\"]', 'correct', 4, 4, '2023-01-08 12:06:13', '2023-01-08 12:06:13'),
(42, 75, 4, '[\"caring\",\"Enjoyable\",\"Disappointment\"]', '[\"care\",\"enjoyable\",\"disappointment\"]', '[\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 6, '2023-01-08 12:06:13', '2023-01-08 12:06:13'),
(43, 76, 4, '[\"at a party\"]', '[\"at a party\"]', 'correct', 4, 4, '2023-01-08 12:06:13', '2023-01-08 12:06:13'),
(44, 77, 4, '[\"lost her passport\"]', '[\"lost her passport\"]', 'correct', 4, 4, '2023-01-08 12:06:13', '2023-01-08 12:06:13'),
(45, 78, 4, '[\"lost her job\"]', '[\"lost her job\"]', 'correct', 4, 4, '2023-01-08 12:06:13', '2023-01-08 12:06:13'),
(46, 79, 4, '[\"the country\"]', '[\"the country\"]', 'correct', 4, 4, '2023-01-08 12:06:13', '2023-01-08 12:06:13'),
(47, 80, 4, '[\"an aggressive\"]', '[\"an aggressive\"]', 'correct', 4, 4, '2023-01-08 12:06:13', '2023-01-08 12:06:13'),
(48, 81, 4, '[\"some boxing gloves\"]', '[\"a bike\"]', 'incorrect', 0, 4, '2023-01-08 12:06:13', '2023-01-08 12:06:13'),
(49, 82, 4, '[\"Looting\",\"Robbery\",\"Burgle\",\"vandal\"]', '[\"looting\",\"robbery\",\"burgle\",\"vandalize\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 8, '2023-01-08 12:06:13', '2023-01-08 12:06:13'),
(50, 83, 4, '[\"politician\",\"Economic\",\"Environmental\",\"Pollution\"]', '[\"politician\",\"economic\",\"environmental\",\"pollution\"]', '[\"correct\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 8, '2023-01-08 12:06:13', '2023-01-08 12:06:13'),
(51, 84, 4, '[\"give someone a job\"]', '[\"fire someone form a job\"]', 'incorrect', 0, 4, '2023-01-08 12:06:13', '2023-01-08 12:06:13'),
(52, 85, 4, '[\"a lot of money\"]', '[\"a lot of money\"]', 'correct', 4, 4, '2023-01-08 12:06:13', '2023-01-08 12:06:13'),
(53, 86, 4, '[\"have a rough voice\"]', '[\"have a difficulty in speaking\"]', 'incorrect', 0, 4, '2023-01-08 12:06:13', '2023-01-08 12:06:13'),
(54, 87, 4, '[\"memorable\",\"scary\",\"gripping\",\"predictable\",\"overrated\",\"hilarious\"]', '[\"memorable\",\"scary\",\"overrated\",\"predictable\",\"gripping\",\"hilarious\"]', '[\"correct\",\"correct\",\"incorrect\",\"correct\",\"incorrect\",\"correct\"]', 0, 12, '2023-01-08 12:06:13', '2023-01-08 12:06:13'),
(55, 90, 4, '[\"released\",null,\"spread\",null,null,\"sued\",\"invaded\",null,null,\"crisis\",\"damages\"]', '[\"sued\",\"damages\",\"spread\",\"crisis\",\"hostage\",\"released\",\"invaded\",\"troops\",\"flee\",\"attack\",\"outcry\"]', '[\"incorrect\",\"incorrect\",\"correct\",\"incorrect\",\"incorrect\",\"incorrect\",\"correct\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 30, '2023-01-08 12:06:13', '2023-01-08 12:06:13'),
(56, 91, 4, '[\"True\"]', '[\"False\"]', 'incorrect', 0, 5, '2023-01-08 12:06:13', '2023-01-08 12:06:13'),
(57, 92, 4, '[\"True\"]', '[\"True\"]', 'correct', 5, 5, '2023-01-08 12:06:13', '2023-01-08 12:06:13'),
(58, 93, 4, '[\"False\"]', '[\"False\"]', 'correct', 5, 5, '2023-01-08 12:06:13', '2023-01-08 12:06:13'),
(59, 94, 4, '[\"True\"]', '[\"True\"]', 'correct', 5, 5, '2023-01-08 12:06:13', '2023-01-08 12:06:13'),
(60, 95, 4, '[\"three times\"]', '[\"three times\"]', 'correct', 5, 5, '2023-01-08 12:06:13', '2023-01-08 12:06:13'),
(61, 96, 4, 'Hello Dear,\r\n\r\nHope all is good,\r\n\r\ni am writing this email to express my opinion towards the company\'s policy, i am pretty much accepting some of it however, i do have some few concerns about others terms so i would like to discuss it with you in our one to one meeting by next week.\r\n\r\nThanks \r\nMy name', '[]', '', 0, 20, '2023-01-08 12:06:13', '2023-01-08 12:06:13'),
(62, 1, 4, '[\"What\"]', '[\"What\"]', 'correct', 1, 1, '2023-01-08 12:06:13', '2023-01-08 12:06:13'),
(63, 2, 4, '[\"They\'re\"]', '[\"They\'re\"]', 'correct', 1, 1, '2023-01-08 12:06:13', '2023-01-08 12:06:13'),
(64, 3, 4, '[\"It\"]', '[\"It\"]', 'correct', 1, 1, '2023-01-08 12:06:13', '2023-01-08 12:06:13'),
(65, 5, 4, '[\"do you spell\"]', '[\"do you spell\"]', 'correct', 1, 1, '2023-01-08 12:06:13', '2023-01-08 12:06:13'),
(66, 6, 4, '[\"a\"]', '[\"--\"]', 'incorrect', 0, 1, '2023-01-08 12:06:13', '2023-01-08 12:06:13'),
(67, 7, 4, '[\"Is Mr. Mike a teacher?\"]', '[\"Is Mr. Mike a teacher?\"]', 'correct', 1, 1, '2023-01-08 12:06:13', '2023-01-08 12:06:13'),
(68, 8, 4, '[\"your father job\"]', '[\"your father\'s job\"]', 'incorrect', 0, 1, '2023-01-08 12:06:13', '2023-01-08 12:06:13'),
(69, 9, 4, '[\"hasn\'t\"]', '[\"doesn\'t have\"]', 'incorrect', 0, 1, '2023-01-08 12:06:13', '2023-01-08 12:06:13'),
(70, 10, 4, '[\"works\"]', '[\"works\"]', 'correct', 1, 1, '2023-01-08 12:06:13', '2023-01-08 12:06:13'),
(71, 11, 4, '[\"We do not go often to the cinema.\"]', '[\"We do not often go to the cinema.\"]', 'incorrect', 0, 1, '2023-01-08 12:06:13', '2023-01-08 12:06:13'),
(72, 12, 4, '[\"do\"]', '[\"do\"]', 'correct', 1, 1, '2023-01-08 12:06:13', '2023-01-08 12:06:13'),
(73, 14, 4, '[\"sometimes\"]', '[\"ever\"]', 'incorrect', 0, 1, '2023-01-08 12:06:13', '2023-01-08 12:06:13'),
(74, 18, 1, '[\"tallest\"]', '[\"taller\"]', 'incorrect', 0, 1, '2023-01-09 11:47:07', '2023-01-09 11:47:07'),
(75, 19, 1, '[\"most\"]', '[\"most\"]', 'correct', 1, 1, '2023-01-09 11:47:07', '2023-01-09 11:47:07'),
(76, 23, 1, '[\"more friendly\"]', '[\"friendlier\"]', 'incorrect', 0, 1, '2023-01-09 11:47:07', '2023-01-09 11:47:07'),
(77, 28, 1, '[\"in\"]', '[\"on\"]', 'incorrect', 0, 1, '2023-01-09 11:47:07', '2023-01-09 11:47:07'),
(78, 30, 1, 'xvxcvxcv', '[]', '', 0, 1, '2023-01-09 11:47:07', '2023-01-09 11:47:07'),
(79, 31, 1, 'xcvxcvxcv', '[]', '', 0, 1, '2023-01-09 11:47:07', '2023-01-09 11:47:07'),
(80, 32, 1, 'xcvxcv', '[]', '', 0, 1, '2023-01-09 11:47:07', '2023-01-09 11:47:07'),
(81, 33, 1, '[\"I don\'t went to the cinema\\u00a0 last night.\\u00a0\"]', '[\"I didn\'t go to the cinema\\u00a0 last night.\\u00a0\"]', 'incorrect', 0, 1, '2023-01-09 11:47:07', '2023-01-09 11:47:07'),
(82, 37, 1, '[\"xcvxcv\",\"xcvxcv\",\"xcvx\",\"xcvxcv\",null,null,null,null,null]', '[\"ill\",\"quiet\",\"difficult\",\"beautiful\",\"crowded\",\"intelligent\",\"rich\",\"friendly\",\"interesting\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 9, '2023-01-09 11:47:07', '2023-01-09 11:47:07'),
(83, 38, 1, '[null,\"xcv\",null,null,null,null,null,null,null,null]', '[\"arrived\",\"want\",\"have been\",\"don\'t understand\",\"talk\",\"met\",\"came\",\"is doing\",\"needs\",\"is ringing\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 10, '2023-01-09 11:47:07', '2023-01-09 11:47:07'),
(84, 42, 1, '[null,null,null,null,null,null,null]', '[\"24\",\"13.20\",\"18.45\",\"11\",\"5.15\",\"8.20\",\"259\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 7, '2023-01-09 11:47:07', '2023-01-09 11:47:07'),
(85, 48, 1, '[null,null,null,null,null,null,null,null,null,null]', '[\"speaking\",\"this is\",\"really sorry\",\"have to\",\"Oh, dear\",\"did she\",\"don\'t worry\",\"could you help me\",\"shall I\",\"that would be\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 10, '2023-01-09 11:47:07', '2023-01-09 11:47:07'),
(86, 75, 1, '[null,null,null]', '[\"care\",\"enjoyable\",\"disappointment\"]', '[\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 6, '2023-01-09 11:47:07', '2023-01-09 11:47:07'),
(87, 82, 1, '[null,null,null,null]', '[\"looting\",\"robbery\",\"burgle\",\"vandalize\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 8, '2023-01-09 11:47:07', '2023-01-09 11:47:07'),
(88, 83, 1, '[null,null,null,null]', '[\"politician\",\"economic\",\"environmental\",\"pollution\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 8, '2023-01-09 11:47:07', '2023-01-09 11:47:07'),
(89, 87, 1, '[null,null,null,null,null,null]', '[\"memorable\",\"scary\",\"overrated\",\"predictable\",\"gripping\",\"hilarious\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 12, '2023-01-09 11:47:07', '2023-01-09 11:47:07'),
(90, 90, 1, '[null,null,null,null,null,null,null,null,null,null,null]', '[\"sued\",\"damages\",\"spread\",\"crisis\",\"hostage\",\"released\",\"invaded\",\"troops\",\"flee\",\"attack\",\"outcry\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 30, '2023-01-09 11:47:07', '2023-01-09 11:47:07'),
(91, 1, 1, '[\"How\"]', '[\"What\"]', 'incorrect', 0, 1, '2023-01-09 11:47:07', '2023-01-09 11:47:07'),
(92, 2, 1, '[\"We\'re\"]', '[\"They\'re\"]', 'incorrect', 0, 1, '2023-01-09 11:47:07', '2023-01-09 11:47:07'),
(93, 3, 1, '[\"They\"]', '[\"It\"]', 'incorrect', 1, 1, '2023-01-09 11:47:07', '2023-01-10 08:07:27'),
(94, 5, 1, '[\"are you spell\"]', '[\"do you spell\"]', 'incorrect', 0, 1, '2023-01-09 11:47:07', '2023-01-09 11:47:07'),
(95, 96, 1, '', '[]', '', 20, 20, '2023-01-10 08:03:42', '2023-01-10 08:03:46'),
(96, 76, 1, '', '[\"at a party\"]', '', 3, 4, '2023-01-10 08:04:08', '2023-01-10 08:04:08'),
(97, 37, 24, '[null,null,null,null,null,null,null,null,null]', '[\"ill\",\"quiet\",\"difficult\",\"beautiful\",\"crowded\",\"intelligent\",\"rich\",\"friendly\",\"interesting\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 9, '2023-01-25 15:09:06', '2023-01-25 15:09:06'),
(98, 38, 24, '[null,null,null,null,null,null,null,null,null,null]', '[\"arrived\",\"want\",\"have been\",\"don\'t understand\",\"talk\",\"met\",\"came\",\"is doing\",\"needs\",\"is ringing\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 10, '2023-01-25 15:09:06', '2023-01-25 15:09:06'),
(99, 42, 24, '[null,null,null,null,null,null,null]', '[\"24\",\"13.20\",\"18.45\",\"11\",\"5.15\",\"8.20\",\"259\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 7, '2023-01-25 15:09:06', '2023-01-25 15:09:06'),
(100, 48, 24, '[null,null,null,null,null,null,null,null,null,null]', '[\"speaking\",\"this is\",\"really sorry\",\"have to\",\"Oh, dear\",\"did she\",\"don\'t worry\",\"could you help me\",\"shall I\",\"that would be\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 10, '2023-01-25 15:09:06', '2023-01-25 15:09:06'),
(101, 75, 24, '[null,null,null]', '[\"care\",\"enjoyable\",\"disappointment\"]', '[\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 6, '2023-01-25 15:09:06', '2023-01-25 15:09:06'),
(102, 82, 24, '[null,null,null,null]', '[\"looting\",\"robbery\",\"burgle\",\"vandalize\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 8, '2023-01-25 15:09:06', '2023-01-25 15:09:06'),
(103, 83, 24, '[null,null,null,null]', '[\"politician\",\"economic\",\"environmental\",\"pollution\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 8, '2023-01-25 15:09:06', '2023-01-25 15:09:06'),
(104, 87, 24, '[null,null,null,null,null,null]', '[\"memorable\",\"scary\",\"overrated\",\"predictable\",\"gripping\",\"hilarious\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 12, '2023-01-25 15:09:06', '2023-01-25 15:09:06'),
(105, 90, 24, '[null,null,null,null,null,null,null,null,null,null,null]', '[\"sued\",\"damages\",\"spread\",\"crisis\",\"hostage\",\"released\",\"invaded\",\"troops\",\"flee\",\"attack\",\"outcry\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 30, '2023-01-25 15:09:06', '2023-01-25 15:09:06'),
(106, 99, 24, 'record/2023-01-25T15_1674659333.wav', '[[]]', '', NULL, NULL, '2023-01-25 15:09:06', '2023-01-25 15:09:06'),
(107, 100, 24, 'record/2023-01-25T15_1674659334.wav', '[[]]', '', NULL, NULL, '2023-01-25 15:09:06', '2023-01-25 15:09:06'),
(108, 37, 25, '[null,null,null,null,null,null,null,null,null]', '[\"ill\",\"quiet\",\"difficult\",\"beautiful\",\"crowded\",\"intelligent\",\"rich\",\"friendly\",\"interesting\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 9, '2023-01-25 15:28:08', '2023-01-25 15:28:08'),
(109, 38, 25, '[null,null,null,null,null,null,null,null,null,null]', '[\"arrived\",\"want\",\"have been\",\"don\'t understand\",\"talk\",\"met\",\"came\",\"is doing\",\"needs\",\"is ringing\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 10, '2023-01-25 15:28:08', '2023-01-25 15:28:08'),
(110, 42, 25, '[null,null,null,null,null,null,null]', '[\"24\",\"13.20\",\"18.45\",\"11\",\"5.15\",\"8.20\",\"259\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 7, '2023-01-25 15:28:08', '2023-01-25 15:28:08'),
(111, 48, 25, '[null,null,null,null,null,null,null,null,null,null]', '[\"speaking\",\"this is\",\"really sorry\",\"have to\",\"Oh, dear\",\"did she\",\"don\'t worry\",\"could you help me\",\"shall I\",\"that would be\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 10, '2023-01-25 15:28:08', '2023-01-25 15:28:08'),
(112, 75, 25, '[null,null,null]', '[\"care\",\"enjoyable\",\"disappointment\"]', '[\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 6, '2023-01-25 15:28:08', '2023-01-25 15:28:08'),
(113, 82, 25, '[null,null,null,null]', '[\"looting\",\"robbery\",\"burgle\",\"vandalize\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 8, '2023-01-25 15:28:08', '2023-01-25 15:28:08'),
(114, 83, 25, '[null,null,null,null]', '[\"politician\",\"economic\",\"environmental\",\"pollution\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 8, '2023-01-25 15:28:08', '2023-01-25 15:28:08'),
(115, 87, 25, '[null,null,null,null,null,null]', '[\"memorable\",\"scary\",\"overrated\",\"predictable\",\"gripping\",\"hilarious\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 12, '2023-01-25 15:28:08', '2023-01-25 15:28:08'),
(116, 90, 25, '[null,null,null,null,null,null,null,null,null,null,null]', '[\"sued\",\"damages\",\"spread\",\"crisis\",\"hostage\",\"released\",\"invaded\",\"troops\",\"flee\",\"attack\",\"outcry\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 30, '2023-01-25 15:28:08', '2023-01-25 15:28:08'),
(117, 96, 25, 'asdasd', '[]', '', 0, 20, '2023-01-25 15:28:08', '2023-01-25 15:28:08'),
(118, 18, 36, '[\"tallest\"]', '[\"taller\"]', 'incorrect', 0, 1, '2023-03-13 06:00:05', '2023-03-13 13:58:25'),
(119, 19, 36, '[\"most\"]', '[\"most\"]', 'correct', 1, 1, '2023-03-13 06:00:05', '2023-03-13 06:00:05'),
(120, 23, 36, '[\"more friendly\"]', '[\"friendlier\"]', 'incorrect', 0, 1, '2023-03-13 06:00:05', '2023-03-13 06:00:05'),
(121, 28, 36, '[\"in\"]', '[\"on\"]', 'incorrect', 0, 1, '2023-03-13 06:00:05', '2023-03-13 06:00:05'),
(122, 29, 36, '[\"to\"]', '[\"for\"]', 'incorrect', 0, 1, '2023-03-13 06:00:05', '2023-03-13 06:00:05'),
(123, 30, 36, 'where does sally\'s grandmother live ?', '[]', '', 1, 1, '2023-03-13 06:00:05', '2023-03-13 13:58:20'),
(124, 31, 36, 'How many children does Tom have?', '[]', '', 1, 1, '2023-03-13 06:00:05', '2023-03-13 13:58:32'),
(125, 32, 36, 'When do they get up?', '[]', '', 1, 1, '2023-03-13 06:00:05', '2023-03-13 13:58:36'),
(126, 33, 36, '[\"I didn\'t go to the cinema\\u00a0 last night.\\u00a0\"]', '[\"I didn\'t go to the cinema\\u00a0 last night.\\u00a0\"]', 'correct', 1, 1, '2023-03-13 06:00:05', '2023-03-13 06:00:05'),
(127, 34, 36, '[\"They don\'t have become very rich.\"]', '[\"They haven\'t become very rich.\"]', 'incorrect', 0, 1, '2023-03-13 06:00:05', '2023-03-13 06:00:05'),
(128, 35, 36, '[\"I don\'t have a big window in my room.\"]', '[\"I don\'t have a big window in my room.\"]', 'correct', 1, 1, '2023-03-13 06:00:05', '2023-03-13 06:00:05'),
(129, 36, 36, '[\"My mom doesn\'t have perfect cooking skills.\"]', '[\"My mom doesn\'t have perfect cooking skills.\"]', 'correct', 1, 1, '2023-03-13 06:00:05', '2023-03-13 06:00:05'),
(130, 37, 36, '[\"ill\",\"quiet\",\"difficult\",\"interesting\",\"crowded\",\"intelligent\",\"rich\",\"friendly\",\"beautiful\"]', '[\"ill\",\"quiet\",\"difficult\",\"beautiful\",\"crowded\",\"intelligent\",\"rich\",\"friendly\",\"interesting\"]', '[\"correct\",\"correct\",\"correct\",\"incorrect\",\"correct\",\"correct\",\"correct\",\"correct\",\"incorrect\"]', 7, 9, '2023-03-13 06:00:05', '2023-03-13 13:59:12'),
(131, 38, 36, '[\"arrived\",\"want\",\"have \\/go\",\"don\'t understand\",\"are talking\",\"have met\",\"came\",\"is doing\",\"has need\",\"is ringing\"]', '[\"arrived\",\"want\",\"have been\",\"don\'t understand\",\"talk\",\"met\",\"came\",\"is doing\",\"needs\",\"is ringing\"]', '[\"correct\",\"correct\",\"incorrect\",\"correct\",\"incorrect\",\"incorrect\",\"correct\",\"correct\",\"incorrect\",\"correct\"]', 6, 10, '2023-03-13 06:00:05', '2023-03-13 13:59:53'),
(132, 42, 36, '[\"24\",\"13.20\",\"18.45\",\"11th march\",\"5.15\",\"8.20\",\"259\"]', '[\"24\",\"13.20\",\"18.45\",\"11\",\"5.15\",\"8.20\",\"259\"]', '[\"correct\",\"correct\",\"correct\",\"incorrect\",\"correct\",\"correct\",\"correct\"]', 7, 7, '2023-03-13 06:00:05', '2023-03-13 14:00:05'),
(133, 48, 36, '[\"speaking\",\"this is\",\"really sorry\",\"have to\",\"Oh, dear\",\"did she\",\"don\'t worry\",\"could you help me\",\"shall I\",\"that would be\"]', '[\"speaking\",\"this is\",\"really sorry\",\"have to\",\"Oh, dear\",\"did she\",\"don\'t worry\",\"could you help me\",\"shall I\",\"that would be\"]', '[\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\",\"correct\"]', 10, 10, '2023-03-13 06:00:05', '2023-03-13 06:00:05'),
(134, 49, 36, '[\"will accept\"]', '[\"would accept\"]', 'incorrect', 0, 3, '2023-03-13 06:00:05', '2023-03-13 06:00:05'),
(135, 50, 36, '[\"would have passed\"]', '[\"will pass\"]', 'incorrect', 0, 3, '2023-03-13 06:00:05', '2023-03-13 06:00:05'),
(136, 51, 36, '[\"see\"]', '[\"seeing\"]', 'incorrect', 0, 3, '2023-03-13 06:00:05', '2023-03-13 06:00:05'),
(137, 52, 36, '[\"wants -- next\"]', '[\"wanted -- the following\"]', 'incorrect', 0, 3, '2023-03-13 06:00:05', '2023-03-13 06:00:05'),
(138, 53, 36, '[\"which\"]', '[\"which\"]', 'correct', 3, 3, '2023-03-13 06:00:05', '2023-03-13 06:00:05'),
(139, 54, 36, '[\"where\"]', '[\"where\"]', 'correct', 3, 3, '2023-03-13 06:00:05', '2023-03-13 06:00:05'),
(140, 55, 36, '[\"have been fired\"]', '[\"were fired\"]', 'incorrect', 0, 3, '2023-03-13 06:00:05', '2023-03-13 06:00:05'),
(141, 56, 36, '[\"give up\"]', '[\"give up\"]', 'correct', 3, 3, '2023-03-13 06:00:05', '2023-03-13 06:00:05'),
(142, 57, 36, '[\"put up with\"]', '[\"put up with\"]', 'correct', 3, 3, '2023-03-13 06:00:05', '2023-03-13 06:00:05'),
(143, 58, 36, '[\"impolite\"]', '[\"impolite\"]', 'correct', 3, 3, '2023-03-13 06:00:05', '2023-03-13 06:00:05'),
(144, 59, 36, '[\"niece\"]', '[\"niece\"]', 'correct', 3, 3, '2023-03-13 06:00:05', '2023-03-13 06:00:05'),
(145, 60, 36, '[\"mother-in-law\"]', '[\"mother-in-law\"]', 'correct', 3, 3, '2023-03-13 06:00:05', '2023-03-13 06:00:05'),
(146, 61, 36, '[\"17\"]', '[\"17\"]', 'correct', 2, 2, '2023-03-13 06:00:05', '2023-03-13 06:00:05'),
(147, 62, 36, '[\"20\"]', '[\"20\"]', 'correct', 2, 2, '2023-03-13 06:00:05', '2023-03-13 06:00:05'),
(148, 63, 36, '[\"$20\"]', '[\"$20\"]', 'correct', 2, 2, '2023-03-13 06:00:05', '2023-03-13 06:00:05'),
(149, 64, 36, '[\"10\"]', '[\"10\"]', 'correct', 2, 2, '2023-03-13 06:00:05', '2023-03-13 06:00:05'),
(150, 65, 36, '[\"in the water tank\"]', '[\"on his body\"]', 'incorrect', 0, 2, '2023-03-13 06:00:05', '2023-03-13 06:00:05'),
(151, 66, 36, '[\"stomach problems\"]', '[\"stomach problems\"]', 'correct', 2, 2, '2023-03-13 06:00:05', '2023-03-13 06:00:05'),
(152, 67, 36, '[\"Although\"]', '[\"Although\"]', 'correct', 4, 4, '2023-03-13 06:00:05', '2023-03-13 06:00:05'),
(153, 68, 36, '[\"Despite being tired\"]', '[\"Despite being tired\"]', 'correct', 4, 4, '2023-03-13 06:00:05', '2023-03-13 06:00:05'),
(154, 69, 36, '[\"used to work\"]', '[\"am used to working\"]', 'incorrect', 0, 4, '2023-03-13 06:00:05', '2023-03-13 06:00:05'),
(155, 71, 36, '[\"have forgotten\"]', '[\"had forgotten\"]', 'incorrect', 0, 4, '2023-03-13 06:00:05', '2023-03-13 06:00:05'),
(156, 72, 36, '[\"keep in touch with\"]', '[\"keep in touch with\"]', 'correct', 4, 4, '2023-03-13 06:00:05', '2023-03-13 06:00:05'),
(157, 73, 36, '[\"have splitted\"]', '[\"split\"]', 'incorrect', 0, 4, '2023-03-13 06:00:05', '2023-03-13 06:00:05'),
(158, 74, 36, '[\"don\'t\"]', '[\"not to\"]', 'incorrect', 0, 4, '2023-03-13 06:00:05', '2023-03-13 06:00:05'),
(159, 75, 36, '[\"care\",\"enjoying\",\"disappoint\"]', '[\"care\",\"enjoyable\",\"disappointment\"]', '[\"correct\",\"incorrect\",\"incorrect\"]', 2, 6, '2023-03-13 06:00:05', '2023-03-13 14:01:01'),
(160, 76, 36, '[\"at a party\"]', '[\"at a party\"]', 'correct', 4, 4, '2023-03-13 06:00:05', '2023-03-13 06:00:05'),
(161, 77, 36, '[\"lost her passport\"]', '[\"lost her passport\"]', 'correct', 4, 4, '2023-03-13 06:00:05', '2023-03-13 06:00:05'),
(162, 78, 36, '[\"lost her job\"]', '[\"lost her job\"]', 'correct', 4, 4, '2023-03-13 06:00:05', '2023-03-13 06:00:05'),
(163, 79, 36, '[\"the country\"]', '[\"the country\"]', 'correct', 4, 4, '2023-03-13 06:00:05', '2023-03-13 06:00:05'),
(164, 80, 36, '[\"an aggressive\"]', '[\"an aggressive\"]', 'correct', 4, 4, '2023-03-13 06:00:05', '2023-03-13 06:00:05'),
(165, 81, 36, '[\"a bike\"]', '[\"a bike\"]', 'correct', 4, 4, '2023-03-13 06:00:05', '2023-03-13 06:00:05'),
(166, 82, 36, '[\"looting\",\"robbing\",\"burgle\",\"vandalize\"]', '[\"looting\",\"robbery\",\"burgle\",\"vandalize\"]', '[\"correct\",\"incorrect\",\"correct\",\"correct\"]', 6, 8, '2023-03-13 06:00:05', '2023-03-13 14:01:18'),
(167, 83, 36, '[\"politician\",\"economic\",\"environmental\",\"pollutes\"]', '[\"politician\",\"economic\",\"environmental\",\"pollution\"]', '[\"correct\",\"correct\",\"correct\",\"incorrect\"]', 6, 8, '2023-03-13 06:00:05', '2023-03-13 14:01:25'),
(168, 84, 36, '[\"fire someone form a job\"]', '[\"fire someone form a job\"]', 'correct', 4, 4, '2023-03-13 06:00:05', '2023-03-13 06:00:05'),
(169, 85, 36, '[\"strong\"]', '[\"a lot of money\"]', 'incorrect', 0, 4, '2023-03-13 06:00:05', '2023-03-13 06:00:05'),
(170, 86, 36, '[\"have a difficulty in speaking\"]', '[\"have a difficulty in speaking\"]', 'correct', 4, 4, '2023-03-13 06:00:05', '2023-03-13 06:00:05'),
(171, 87, 36, '[\"memorable\",\"scary\",\"overrated\",\"hilarious\",\"predictable\",\"gripping\"]', '[\"memorable\",\"scary\",\"overrated\",\"predictable\",\"gripping\",\"hilarious\"]', '[\"correct\",\"correct\",\"correct\",\"incorrect\",\"incorrect\",\"incorrect\"]', 6, 12, '2023-03-13 06:00:05', '2023-03-13 14:01:55'),
(172, 90, 36, '[\"hostage\",\"troops\",null,\"attack\",null,null,null,null,null,null,null]', '[\"sued\",\"damages\",\"spread\",\"crisis\",\"hostage\",\"released\",\"invaded\",\"troops\",\"flee\",\"attack\",\"outcry\"]', '[\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\",\"incorrect\"]', 0, 30, '2023-03-13 06:00:05', '2023-03-13 06:00:05'),
(173, 91, 36, '[\"True\"]', '[\"False\"]', 'incorrect', 0, 5, '2023-03-13 06:00:05', '2023-03-13 06:00:05'),
(174, 92, 36, '[\"False\"]', '[\"True\"]', 'incorrect', 0, 5, '2023-03-13 06:00:05', '2023-03-13 06:00:05'),
(175, 93, 36, '[\"False\"]', '[\"False\"]', 'correct', 5, 5, '2023-03-13 06:00:05', '2023-03-13 06:00:05'),
(176, 94, 36, '[\"True\"]', '[\"True\"]', 'correct', 5, 5, '2023-03-13 06:00:05', '2023-03-13 06:00:05'),
(177, 95, 36, '[\"three times\"]', '[\"three times\"]', 'correct', 5, 5, '2023-03-13 06:00:05', '2023-03-13 06:00:05'),
(178, 96, 36, 'Good Morning, Mr.Ahmed,\r\nI hope everything is going well \r\nI would like to highlight some important points to improve our company \r\nI need to arrange the working hours to avoid workload \r\nalso, I offered to make some events which make the employees more engaged', '[]', '', 0, 20, '2023-03-13 06:00:05', '2023-03-13 06:00:05'),
(179, 99, 36, 'record/2023-03-13T05_1678687140.wav', '[[]]', '', NULL, NULL, '2023-03-13 06:00:05', '2023-03-13 06:00:05'),
(180, 1, 36, '[\"What\"]', '[\"What\"]', 'correct', 1, 1, '2023-03-13 06:00:05', '2023-03-13 06:00:05'),
(181, 2, 36, '[\"They\'re\"]', '[\"They\'re\"]', 'correct', 1, 1, '2023-03-13 06:00:05', '2023-03-13 06:00:05'),
(182, 3, 36, '[\"It\"]', '[\"It\"]', 'correct', 1, 1, '2023-03-13 06:00:05', '2023-03-13 06:00:05'),
(183, 5, 36, '[\"do you spell\"]', '[\"do you spell\"]', 'correct', 1, 1, '2023-03-13 06:00:05', '2023-03-13 06:00:05'),
(184, 6, 36, '[\"a\"]', '[\"--\"]', 'incorrect', 0, 1, '2023-03-13 06:00:05', '2023-03-13 06:00:05'),
(185, 7, 36, '[\"Is this your phone?\"]', '[\"Is Mr. Mike a teacher?\"]', 'incorrect', 0, 1, '2023-03-13 06:00:05', '2023-03-13 06:00:05'),
(186, 8, 36, '[\"your father\'s job\"]', '[\"your father\'s job\"]', 'correct', 1, 1, '2023-03-13 06:00:05', '2023-03-13 06:00:05'),
(187, 9, 36, '[\"doesn\'t have\"]', '[\"doesn\'t have\"]', 'correct', 1, 1, '2023-03-13 06:00:05', '2023-03-13 06:00:05'),
(188, 10, 36, '[\"works\"]', '[\"works\"]', 'correct', 1, 1, '2023-03-13 06:00:05', '2023-03-13 06:00:05'),
(189, 11, 36, '[\"We do not go often to the cinema.\"]', '[\"We do not often go to the cinema.\"]', 'incorrect', 0, 1, '2023-03-13 06:00:05', '2023-03-13 06:00:05'),
(190, 12, 36, '[\"go\"]', '[\"do\"]', 'incorrect', 0, 1, '2023-03-13 06:00:05', '2023-03-13 06:00:05'),
(191, 14, 36, '[\"sometimes\"]', '[\"ever\"]', 'incorrect', 0, 1, '2023-03-13 06:00:05', '2023-03-13 06:00:05');

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `alies` varchar(255) DEFAULT NULL,
  `active` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `title`, `alies`, `active`, `created_at`, `updated_at`) VALUES
(1, 'Language and Literature', 'subject/language-literature', 1, NULL, NULL),
(2, 'Language Acquisition', NULL, 1, NULL, NULL),
(3, 'Individuals and Societies', NULL, 1, NULL, NULL),
(4, 'Sciences', NULL, 1, NULL, NULL),
(5, 'Mathematics', 'subject/math/questionbank', 1, NULL, NULL),
(6, 'Arts', NULL, 1, NULL, NULL),
(7, 'Design', NULL, 1, NULL, NULL),
(8, 'Physical and Health Education', NULL, 1, NULL, NULL),
(9, 'MYP Projects', NULL, 1, NULL, NULL),
(10, 'Interdisciplinary Teaching and Learning', NULL, 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `surahs`
--

CREATE TABLE `surahs` (
  `id` int(3) NOT NULL,
  `number` int(3) DEFAULT NULL,
  `name_ar` varchar(14) DEFAULT NULL,
  `name_en` varchar(14) DEFAULT NULL,
  `iframe_link` varchar(255) DEFAULT NULL,
  `name_en_translation` varchar(26) DEFAULT NULL,
  `type_en` varchar(7) DEFAULT NULL,
  `name_ru` varchar(15) DEFAULT NULL,
  `name_ru_translation` varchar(33) DEFAULT NULL,
  `type_ru` varchar(15) DEFAULT NULL,
  `video` longtext DEFAULT NULL,
  `num_ayas` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `surahs`
--

INSERT INTO `surahs` (`id`, `number`, `name_ar`, `name_en`, `iframe_link`, `name_en_translation`, `type_en`, `name_ru`, `name_ru_translation`, `type_ru`, `video`, `num_ayas`) VALUES
(1, 1, 'سورة الفاتحة', 'Al-Faatiha', 'https://quran.com/1', 'The Opening', 'Meccan', 'Аль-Фатиха', 'Открывающая Коран', 'мекканская сура', '', 7),
(2, 2, 'سورة البقرة', 'Al-Baqara', 'https://quran.com/2', 'The Cow', 'Medinan', 'Аль-Бакара', 'Корова', 'мединская сура', '', NULL),
(3, 3, 'سورة آل عمران', 'Aal-i-Imraan', 'https://quran.com/3', 'The Family of Imraan', 'Medinan', 'Алю Имран', 'Семейство Имрана', 'мединская сура', '', NULL),
(4, 4, 'سورة النساء', 'An-Nisaa', 'https://quran.com/4', 'The Women', 'Medinan', 'Ан-Ниса', 'Женщины', 'мединская сура', '', NULL),
(5, 5, 'سورة المائدة', 'Al-Maaida', 'https://quran.com/5', 'The Table', 'Medinan', 'Аль-Маида', 'Трапеза', 'мединская сура', '', NULL),
(6, 6, 'سورة الأنعام', 'Al-An\'aam', 'https://quran.com/6', 'The Cattle', 'Meccan', 'Аль-Анам', 'Скот', 'мекканская сура', '', NULL),
(7, 7, 'سورة الأعراف', 'Al-A\'raaf', 'https://quran.com/7', 'The Heights', 'Meccan', 'Аль-Араф', 'Преграды', 'мекканская сура', '', NULL),
(8, 8, 'سورة الأنفال', 'Al-Anfaal', 'https://quran.com/8', 'The Spoils of War', 'Medinan', 'Аль-Анфаль', 'Трофеи', 'мединская сура', '', NULL),
(9, 9, 'سورة التوبة', 'At-Tawba', 'https://quran.com/9', 'The Repentance', 'Medinan', 'Ат-Тауба', 'Покаяние', 'мединская сура', '', NULL),
(10, 10, 'سورة يونس', 'Yunus', 'https://quran.com/10', 'Jonas', 'Meccan', 'Йунус', 'Йунус', 'мекканская сура', '', NULL),
(11, 11, 'سورة هود', 'Hud', 'https://quran.com/11', 'Hud', 'Meccan', 'Худ', 'Худ', 'мекканская сура', '', NULL),
(12, 12, 'سورة يوسف', 'Yusuf', 'https://quran.com/12', 'Joseph', 'Meccan', 'Йусуф', 'Йусуф', 'мекканская сура', '', NULL),
(13, 13, 'سورة الرعد', 'Ar-Ra\'d', 'https://quran.com/13', 'The Thunder', 'Medinan', 'Ар-Раад', 'Гром', 'мединская сура', '', NULL),
(14, 14, 'سورة ابراهيم', 'Ibrahim', 'https://quran.com/14', 'Abraham', 'Meccan', 'Ибрахим', 'Ибрахим', 'мекканская сура', '', NULL),
(15, 15, 'سورة الحجر', 'Al-Hijr', 'https://quran.com/15', 'The Rock', 'Meccan', 'Аль-Хиджр', 'Аль-Хиджр', 'мекканская сура', '', NULL),
(16, 16, 'سورة النحل', 'An-Nahl', 'https://quran.com/16', 'The Bee', 'Meccan', 'Ан-Нахль', 'Пчёлы', 'мекканская сура', '', NULL),
(17, 17, 'سورة الإسراء', 'Al-Israa', 'https://quran.com/17', 'The Night Journey', 'Meccan', 'Аль-Исра', 'Ночной перенос', 'мекканская сура', '', NULL),
(18, 18, 'سورة الكهف', 'Al-Kahf', 'https://quran.com/18', 'The Cave', 'Meccan', 'Аль-Кахф', 'Пещера', 'мекканская сура', '', NULL),
(19, 19, 'سورة مريم', 'Maryam', 'https://quran.com/19', 'Mary', 'Meccan', 'Марьям', 'Марьям', 'мекканская сура', '', NULL),
(20, 20, 'سورة طه', 'Taa-Haa', 'https://quran.com/20', 'Taa-Haa', 'Meccan', 'Та Ха', 'Та Ха', 'мекканская сура', '', NULL),
(21, 21, 'سورة الأنبياء', 'Al-Anbiyaa', 'https://quran.com/21', 'The Prophets', 'Meccan', 'Аль-Анбийа', 'Пророки', 'мекканская сура', '', NULL),
(22, 22, 'سورة الحج', 'Al-Hajj', 'https://quran.com/22', 'The Pilgrimage', 'Medinan', 'Аль-Хаджж', 'Паломничество', 'мединская сура', '', NULL),
(23, 23, 'سورة المؤمنون', 'Al-Muminoon', 'https://quran.com/23', 'The Believers', 'Meccan', 'Аль-Муминун', 'Верующие', 'мекканская сура', '', NULL),
(24, 24, 'سورة النور', 'An-Noor', 'https://quran.com/24', 'The Light', 'Medinan', 'Ан-Нур', 'Свет', 'мединская сура', '', NULL),
(25, 25, 'سورة الفرقان', 'Al-Furqaan', 'https://quran.com/25', 'The Criterion', 'Meccan', 'Аль-Фуркан', 'Различение', 'мекканская сура', '', NULL),
(26, 26, 'سورة الشعراء', 'Ash-Shu\'araa', 'https://quran.com/26', 'The Poets', 'Meccan', 'Аш-Шуара', 'Поэты', 'мекканская сура', '', NULL),
(27, 27, 'سورة النمل', 'An-Naml', 'https://quran.com/27', 'The Ant', 'Meccan', 'Ан-Намль', 'Муравьи', 'мекканская сура', '', NULL),
(28, 28, 'سورة القصص', 'Al-Qasas', 'https://quran.com/28', 'The Stories', 'Meccan', 'Аль-Касас', 'Рассказы', 'мекканская сура', '', NULL),
(29, 29, 'سورة العنكبوت', 'Al-Ankaboot', 'https://quran.com/29', 'The Spider', 'Meccan', 'Аль-Анкабут', 'Паук', 'мекканская сура', '', NULL),
(30, 30, 'سورة الروم', 'Ar-Room', 'https://quran.com/30', 'The Romans', 'Meccan', 'Ар-Рум', 'Римляне', 'мекканская сура', '', NULL),
(31, 31, 'سورة لقمان', 'Luqman', 'https://quran.com/31', 'Luqman', 'Meccan', 'Лукман', 'Лукман', 'мекканская сура', '', NULL),
(32, 32, 'سورة السجدة', 'As-Sajda', 'https://quran.com/32', 'The Prostration', 'Meccan', 'Ас-Саджда', 'Земной поклон', 'мекканская сура', '', NULL),
(33, 33, 'سورة الأحزاب', 'Al-Ahzaab', 'https://quran.com/33', 'The Clans', 'Medinan', 'Аль-Ахзаб', 'Союзники,Сонмы', 'мединская сура', '', NULL),
(34, 34, 'سورة سبإ', 'Saba', 'https://quran.com/34', 'Sheba', 'Meccan', 'Саба', 'Саба', 'мекканская сура', '', NULL),
(35, 35, 'سورة فاطر', 'Faatir', 'https://quran.com/35', 'The Originator', 'Meccan', 'Фатыр', 'Творец', 'мекканская сура', '', NULL),
(36, 36, 'سورة يس', 'Yaseen', 'https://quran.com/36', 'Yaseen', 'Meccan', 'Йа Син', 'Йа Син', 'мекканская сура', '', NULL),
(37, 37, 'سورة الصافات', 'As-Saaffaat', 'https://quran.com/37', 'Those drawn up in Ranks', 'Meccan', 'Ас-Саффат', 'Выстроившиеся в ряды', 'мекканская сура', '', NULL),
(38, 38, 'سورة ص', 'Saad', 'https://quran.com/38', 'The letter Saad', 'Meccan', 'Сад', 'Буква Сад ', 'мекканская сура', '', NULL),
(39, 39, 'سورة الزمر', 'Az-Zumar', 'https://quran.com/39', 'The Groups', 'Meccan', 'Аз-Зумар', 'Толпы', 'мекканская сура', '', NULL),
(40, 40, 'سورة غافر', 'Ghafir', 'https://quran.com/40', 'The Forgiver', 'Meccan', 'Гафир', 'Прощающий', 'мекканская сура', '', NULL),
(41, 41, 'سورة فصلت', 'Fussilat', 'https://quran.com/41', 'Explained in detail', 'Meccan', 'Фуссылат', 'Разъяснены', 'мекканская сура', '', NULL),
(42, 42, 'سورة الشورى', 'Ash-Shura', 'https://quran.com/42', 'Consultation', 'Meccan', 'Аш-Шура', 'Совет', 'мекканская сура', '', NULL),
(43, 43, 'سورة الزخرف', 'Az-Zukhruf', 'https://quran.com/43', 'Ornaments of gold', 'Meccan', 'Аз-Зухруф', 'Украшения', 'мекканская сура', '', NULL),
(44, 44, 'سورة الدخان', 'Ad-Dukhaan', 'https://quran.com/44', 'The Smoke', 'Meccan', 'Ад-Духан', 'Дым', 'мекканская сура', '', NULL),
(45, 45, 'سورة الجاثية', 'Al-Jaathiya', 'https://quran.com/45', 'Crouching', 'Meccan', 'Аль-Джасийа', 'Коленопреклонённые', 'мекканская сура', '', NULL),
(46, 46, 'سورة الأحقاف', 'Al-Ahqaf', 'https://quran.com/46', 'The Dunes', 'Meccan', 'Аль-Ахкаф', 'Пески', 'мекканская сура', '', NULL),
(47, 47, 'سورة محمد', 'Muhammad', 'https://quran.com/47', 'Muhammad', 'Medinan', 'Мухаммад', 'Мухаммад', 'мединская сура', '', NULL),
(48, 48, 'سورة الفتح', 'Al-Fath', 'https://quran.com/48', 'The Victory', 'Medinan', 'Аль-Фатх', 'Победа', 'мединская сура', '', NULL),
(49, 49, 'سورة الحجرات', 'Al-Hujuraat', 'https://quran.com/49', 'The Inner Apartments', 'Medinan', 'Аль-Худжурат', 'Комнаты', 'мединская сура', '', NULL),
(50, 50, 'سورة ق', 'Qaaf', 'https://quran.com/50', 'The letter Qaaf', 'Meccan', 'Каф', 'Буква Каф', 'мекканская сура', '', NULL),
(51, 51, 'سورة الذاريات', 'Adh-Dhaariyat', 'https://quran.com/51', 'The Winnowing Winds', 'Meccan', 'Аз-Зарийат', 'Рассеивающие', 'мекканская сура', '', NULL),
(52, 52, 'سورة الطور', 'At-Tur', 'https://quran.com/52', 'The Mount', 'Meccan', 'Ат-Тур ', 'Гора', 'мекканская сура', '', NULL),
(53, 53, 'سورة النجم', 'An-Najm', 'https://quran.com/53', 'The Star', 'Meccan', 'Ан-Наджм', ' Звезда', 'мекканская сура', '', NULL),
(54, 54, 'سورة القمر', 'Al-Qamar', 'https://quran.com/54', 'The Moon', 'Meccan', 'Аль-Камар ', 'Месяц', 'мекканская сура', '', NULL),
(55, 55, 'سورة الرحمن', 'Ar-Rahmaan', 'https://quran.com/55', 'The Beneficent', 'Medinan', 'Ар-Рахман', 'Милостивый', 'мединская сура', '', NULL),
(56, 56, 'سورة الواقعة', 'Al-Waaqia', 'https://quran.com/56', 'The Inevitable', 'Meccan', 'Аль-Вакиа', 'Событие', 'мекканская сура', '', NULL),
(57, 57, 'سورة الحديد', 'Al-Hadid', 'https://quran.com/57', 'The Iron', 'Medinan', 'Аль-Хадид ', 'Железо', 'мединская сура', '', NULL),
(58, 58, 'سورة المجادلة', 'Al-Mujaadila', 'https://quran.com/58', 'The Pleading Woman', 'Medinan', 'Аль-Муджадила ', 'Препирающаяся', 'мединская сура', '', NULL),
(59, 59, 'سورة الحشر', 'Al-Hashr', 'https://quran.com/59', 'The Exile', 'Medinan', 'Аль-Хашр', 'Собрание', 'мединская сура', '', NULL),
(60, 60, 'سورة الممتحنة', 'Al-Mumtahana', 'https://quran.com/60', 'She that is to be examined', 'Medinan', 'Аль-Мумтахана', 'Испытуемая', 'мединская сура', '', NULL),
(61, 61, 'سورة الصف', 'As-Saff', 'https://quran.com/61', 'The Ranks', 'Medinan', 'Ас-Сафф', ' Ряды', 'мединская сура', '', NULL),
(62, 62, 'سورة الجمعة', 'Al-Jumu\'a', 'https://quran.com/62', 'Friday', 'Medinan', 'Аль-Джумуа ', 'Пятница ( день пятничной молитвы)', 'мединская сура', '', NULL),
(63, 63, 'سورة المنافقون', 'Al-Munaafiqoon', 'https://quran.com/63', 'The Hypocrites', 'Medinan', 'Аль-Мунафикун', 'Лицемеры', 'мединская сура', '', NULL),
(64, 64, 'سورة التغابن', 'At-Taghaabun', 'https://quran.com/64', 'Mutual Disillusion', 'Medinan', 'Ат-Тагабун ', 'взаимное обманывание', 'мединская сура', '', NULL),
(65, 65, 'سورة الطلاق', 'At-Talaaq', 'https://quran.com/65', 'Divorce', 'Medinan', 'Ат-Талак', 'Развод', 'мединская сура', '', NULL),
(66, 66, 'سورة التحريم', 'At-Tahrim', 'https://quran.com/66', 'The Prohibition', 'Medinan', 'Ат-Тахрим', 'Запрещение', 'мединская сура', '', NULL),
(67, 67, 'سورة الملك', 'Al-Mulk', 'https://quran.com/67', 'The Sovereignty', 'Meccan', 'Аль-Мульк', 'Власть', 'мекканская сура', '', NULL),
(68, 68, 'سورة القلم', 'Al-Qalam', 'https://quran.com/68', 'The Pen', 'Meccan', 'Аль-Калам', 'Письменная трость', 'мекканская сура', '', NULL),
(69, 69, 'سورة الحاقة', 'Al-Haaqqa', 'https://quran.com/69', 'The Reality', 'Meccan', 'Аль-Хакка', ' Неминуемое', 'мекканская сура', '', NULL),
(70, 70, 'سورة المعارج', 'Al-Ma\'aarij', 'https://quran.com/70', 'The Ascending Stairways', 'Meccan', 'Аль-Мааридж ', 'Ступени', 'мекканская сура', '', NULL),
(71, 71, 'سورة نوح', 'Nooh', 'https://quran.com/71', 'Noah', 'Meccan', 'Нух ', 'Нух ', 'мекканская сура', '', NULL),
(72, 72, 'سورة الجن', 'Al-Jinn', 'https://quran.com/72', 'The Jinn', 'Meccan', 'Аль-Джинн', 'Джинны', 'мекканская сура', '', NULL),
(73, 73, 'سورة المزمل', 'Al-Muzzammil', 'https://quran.com/73', 'The Enshrouded One', 'Meccan', 'Аль-Муззаммиль ', 'Закутавшийся', 'мекканская сура', '', NULL),
(74, 74, 'سورة المدثر', 'Al-Muddaththir', 'https://quran.com/74', 'The Cloaked One', 'Meccan', 'Аль-Муддассир', 'Завернувшийся', 'мекканская сура', '', NULL),
(75, 75, 'سورة القيامة', 'Al-Qiyaama', 'https://quran.com/75', 'The Resurrection', 'Meccan', 'Аль-Кийама', 'Воскресение', 'мекканская сура', '', NULL),
(76, 76, 'سورة الانسان', 'Al-Insaan', 'https://quran.com/76', 'Man', 'Medinan', 'Аль-Инсан', 'Человек', 'мединская сура', '', NULL),
(77, 77, 'سورة المرسلات', 'Al-Mursalaat', 'https://quran.com/77', 'The Emissaries', 'Meccan', 'Аль-Мурсалят ', 'Посылаемые', 'мекканская сура', '', NULL),
(78, 78, 'سورة النبإ', 'An-Naba', 'https://quran.com/78', 'The Announcement', 'Meccan', 'Ан-Наба ', 'Весть', 'мекканская сура', '', NULL),
(79, 79, 'سورة النازعات', 'An-Naazi\'aat', 'https://quran.com/79', 'Those who drag forth', 'Meccan', 'Ан-Назиат', 'Вырывающие', 'мекканская сура', '', NULL),
(80, 80, 'سورة عبس', 'Abasa', 'https://quran.com/80', 'He frowned', 'Meccan', 'Абаса', 'Нахмурился', 'мекканская сура', '', NULL),
(81, 81, 'سورة التكوير', 'At-Takwir', 'https://quran.com/81', 'The Overthrowing', 'Meccan', 'Ат-Таквир', 'Скручивание', 'мекканская сура', '', NULL),
(82, 82, 'سورة الإنفطار', 'Al-Infitaar', 'https://quran.com/82', 'The Cleaving', 'Meccan', 'Аль-Инфитар', 'Раскалывание', 'мекканская сура', '', NULL),
(83, 83, 'سورة المطففين', 'Al-Mutaffifin', 'https://quran.com/83', 'Defrauding', 'Meccan', 'Аль-Мутаффифин', 'Обвешивающие', 'мекканская сура', '', NULL),
(84, 84, 'سورة الإنشقاق', 'Al-Inshiqaaq', 'https://quran.com/84', 'The Splitting Open', 'Meccan', 'Аль-Иншикак ', 'Разверзнется', 'мекканская сура', '', NULL),
(85, 85, 'سورة البروج', 'Al-Burooj', 'https://quran.com/85', 'The Constellations', 'Meccan', 'Аль-Бурудж', 'Созвездия', 'мекканская сура', '', NULL),
(86, 86, 'سورة الطارق', 'At-Taariq', 'https://quran.com/86', 'The Morning Star', 'Meccan', 'Ат-Тарик', 'Ночной путник', 'мекканская сура', '', NULL),
(87, 87, 'سورة الأعلى', 'Al-A\'laa', 'https://quran.com/87', 'The Most High', 'Meccan', 'Аль-Аля', 'Высочайший', 'мекканская сура', '', NULL),
(88, 88, 'سورة الغاشية', 'Al-Ghaashiya', 'https://quran.com/88', 'The Overwhelming', 'Meccan', 'Аль-Гашийа', 'Покрывающее', 'мекканская сура', '', NULL),
(89, 89, 'سورة الفجر', 'Al-Fajr', 'https://quran.com/89', 'The Dawn', 'Meccan', 'Аль-Фаджр ', 'Заря', 'мекканская сура', '', NULL),
(90, 90, 'سورة البلد', 'Al-Balad', 'https://quran.com/90', 'The City', 'Meccan', 'Аль-Балад ', 'Город', 'мекканская сура', '', NULL),
(91, 91, 'سورة الشمس', 'Ash-Shams', 'https://quran.com/91', 'The Sun', 'Meccan', 'Аш-Шамс ', 'Солнце', 'мекканская сура', '', NULL),
(92, 92, 'سورة الليل', 'Al-Lail', 'https://quran.com/92', 'The Night', 'Meccan', 'Аль-Лайл', 'Ночь', 'мекканская сура', '', NULL),
(93, 93, 'سورة الضحى', 'Ad-Dhuhaa', 'https://quran.com/93', 'The Morning Hours', 'Meccan', 'Ад-Духа', 'Утро', 'мекканская сура', '', 11),
(94, 94, 'سورة الشرح', 'Ash-Sharh', 'https://quran.com/94', 'The Consolation', 'Meccan', 'Аш-Шарх', 'Раскрытие', 'мекканская сура', '', 8),
(95, 95, 'سورة التين', 'At-Tin', 'https://quran.com/95', 'The Fig', 'Meccan', 'Ат-Тин', 'Смоковница', 'мекканская сура', '', 8),
(96, 96, 'سورة العلق', 'Al-Alaq', 'https://quran.com/96', 'The Clot', 'Meccan', 'Аль-Алак ', 'Сгусток', 'мекканская сура', '', 19),
(97, 97, 'سورة القدر', 'Al-Qadr', 'https://quran.com/97', 'The Power, Fate', 'Meccan', 'Аль-Кадр', 'Предопределение', 'мекканская сура', '', 5),
(98, 98, 'سورة البينة', 'Al-Bayyina', 'https://quran.com/98', 'The Evidence', 'Medinan', 'Аль-Баййина', 'Ясное знамение', 'мединская сура', '', 8),
(99, 99, 'سورة الزلزلة', 'Az-Zalzala', 'https://quran.com/99', 'The Earthquake', 'Medinan', 'Аз-Залзала', 'Землетрясение', 'мединская сура', '', 8),
(100, 100, 'سورة العاديات', 'Al-Aadiyaat', 'https://quran.com/100', 'The Chargers', 'Meccan', 'Аль-Адийат ', 'Скачущие', 'мекканская сура', '', 11),
(101, 101, 'سورة القارعة', 'Al-Qaari\'a', 'https://quran.com/101', 'The Calamity', 'Meccan', 'Аль-Кариа', ' Великое бедствие', 'мекканская сура', '', 11),
(102, 102, 'سورة التكاثر', 'At-Takaathur', 'https://quran.com/102', 'Competition', 'Meccan', 'Ат-Такасур', 'Приумножение', 'мекканская сура', '', 8),
(103, 103, 'سورة العصر', 'Al-Asr', 'https://quran.com/103', 'The Declining Day, Epoch', 'Meccan', 'Аль-Аср', 'предвечернее время', 'мекканская сура', '', 3),
(104, 104, 'سورة الهمزة', 'Al-Humaza', 'https://quran.com/104', 'The Traducer', 'Meccan', 'Аль-Хумаза ', 'Хулитель', 'мекканская сура', '', 9),
(105, 105, 'سورة الفيل', 'Al-Fil', 'https://quran.com/105', 'The Elephant', 'Meccan', 'Аль-Филь', 'Слон', 'мекканская сура', '', 5),
(106, 106, 'سورة قريش', 'Quraish', 'https://quran.com/106', 'Quraysh', 'Meccan', 'Курейш ', 'Курейшиты', 'мекканская сура', '', 4),
(107, 107, 'سورة الماعون', 'Al-Maa\'un', 'https://quran.com/107', 'Almsgiving', 'Meccan', 'Аль-Маун', 'Милостыня', 'мекканская сура', '', 7),
(108, 108, 'سورة الكوثر', 'Al-Kawthar', 'https://quran.com/108', 'Abundance', 'Meccan', 'Аль-Каусар', 'Изобилие', 'мекканская сура', '', 3),
(109, 109, 'سورة الكافرون', 'Al-Kaafiroon', 'https://quran.com/109', 'The Disbelievers', 'Meccan', 'Аль-Кафирун', 'Неверующие', 'мекканская сура', '', 6),
(110, 110, 'سورة النصر', 'An-Nasr', 'https://quran.com/110', 'Divine Support', 'Medinan', 'Ан-Наср', 'Помощь', 'мединская сура', '', 3),
(111, 111, 'سورة المسد', 'Al-Masad', 'https://quran.com/111', 'The Palm Fibre', 'Meccan', 'Аль-Масад', 'Пальмовые волокна', 'мекканская сура', '', 5),
(112, 112, 'سورة الإخلاص', 'Al-Ikhlaas', 'https://quran.com/112', 'Sincerity', 'Meccan', 'Аль-Ихлас', 'Очищение веры', 'мекканская сура', '', 4),
(113, 113, 'سورة الفلق', 'Al-Falaq', 'https://quran.com/113', 'The Dawn', 'Meccan', 'Аль-Фалак ', 'Рассвет', 'мекканская сура', '', 5),
(114, 114, 'سورة الناس', 'An-Naas', 'https://quran.com/114', 'Mankind', 'Meccan', 'Ан-Нас', 'Люди', 'мекканская сура', '', 6);

-- --------------------------------------------------------

--
-- Table structure for table `surahs_old`
--

CREATE TABLE `surahs_old` (
  `id` int(10) UNSIGNED NOT NULL,
  `number` int(11) NOT NULL,
  `name_ar` varchar(255) DEFAULT NULL,
  `name_en` varchar(255) DEFAULT NULL,
  `iframe_link` mediumtext DEFAULT NULL,
  `name_en_translation` varchar(255) DEFAULT NULL,
  `type_en` varchar(255) DEFAULT NULL,
  `name_ru` varchar(255) DEFAULT NULL,
  `name_ru_translation` varchar(255) DEFAULT NULL,
  `type_ru` varchar(255) DEFAULT NULL,
  `num_ayas` int(11) DEFAULT NULL,
  `video` mediumtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `surahs_old`
--

INSERT INTO `surahs_old` (`id`, `number`, `name_ar`, `name_en`, `iframe_link`, `name_en_translation`, `type_en`, `name_ru`, `name_ru_translation`, `type_ru`, `num_ayas`, `video`, `created_at`, `updated_at`) VALUES
(1, 1, 'سورة الفاتحة', 'Al-Faatiha', 'https://quran.com/1', 'The Opening', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:06:54', '2018-06-07 06:06:54'),
(2, 2, 'سورة البقرة', 'Al-Baqara', 'https://quran.com/2', 'The Cow', 'Medinan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:06:54', '2018-06-07 06:06:54'),
(3, 3, 'سورة آل عمران', 'Aal-i-Imraan', 'https://quran.com/3', 'The Family of Imraan', 'Medinan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:06:55', '2018-06-07 06:06:55'),
(4, 4, 'سورة النساء', 'An-Nisaa', 'https://quran.com/4', 'The Women', 'Medinan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:06:55', '2018-06-07 06:06:55'),
(5, 5, 'سورة المائدة', 'Al-Maaida', 'https://quran.com/5', 'The Table', 'Medinan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:06:55', '2018-06-07 06:06:55'),
(6, 6, 'سورة الأنعام', 'Al-An\'aam', 'https://quran.com/6', 'The Cattle', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:06:56', '2018-06-07 06:06:56'),
(7, 7, 'سورة الأعراف', 'Al-A\'raaf', 'https://quran.com/7', 'The Heights', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:06:56', '2018-06-07 06:06:56'),
(8, 8, 'سورة الأنفال', 'Al-Anfaal', 'https://quran.com/8', 'The Spoils of War', 'Medinan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:06:57', '2018-06-07 06:06:57'),
(9, 9, 'سورة التوبة', 'At-Tawba', 'https://quran.com/9', 'The Repentance', 'Medinan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:06:57', '2018-06-07 06:06:57'),
(10, 10, 'سورة يونس', 'Yunus', 'https://quran.com/10', 'Jonas', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:06:57', '2018-06-07 06:06:57'),
(11, 11, 'سورة هود', 'Hud', 'https://quran.com/11', 'Hud', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:06:58', '2018-06-07 06:06:58'),
(12, 12, 'سورة يوسف', 'Yusuf', 'https://quran.com/12', 'Joseph', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:06:58', '2018-06-07 06:06:58'),
(13, 13, 'سورة الرعد', 'Ar-Ra\'d', 'https://quran.com/13', 'The Thunder', 'Medinan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:06:59', '2018-06-07 06:06:59'),
(14, 14, 'سورة ابراهيم', 'Ibrahim', 'https://quran.com/14', 'Abraham', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:06:59', '2018-06-07 06:06:59'),
(15, 15, 'سورة الحجر', 'Al-Hijr', 'https://quran.com/15', 'The Rock', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:06:59', '2018-06-07 06:06:59'),
(16, 16, 'سورة النحل', 'An-Nahl', 'https://quran.com/16', 'The Bee', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:06:59', '2018-06-07 06:06:59'),
(17, 17, 'سورة الإسراء', 'Al-Israa', 'https://quran.com/17', 'The Night Journey', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:00', '2018-06-07 06:07:00'),
(18, 18, 'سورة الكهف', 'Al-Kahf', 'https://quran.com/18', 'The Cave', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:00', '2018-06-07 06:07:00'),
(19, 19, 'سورة مريم', 'Maryam', 'https://quran.com/19', 'Mary', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:01', '2018-06-07 06:07:01'),
(20, 20, 'سورة طه', 'Taa-Haa', 'https://quran.com/20', 'Taa-Haa', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:01', '2018-06-07 06:07:01'),
(21, 21, 'سورة الأنبياء', 'Al-Anbiyaa', 'https://quran.com/21', 'The Prophets', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:02', '2018-06-07 06:07:02'),
(22, 22, 'سورة الحج', 'Al-Hajj', 'https://quran.com/22', 'The Pilgrimage', 'Medinan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:02', '2018-06-07 06:07:02'),
(23, 23, 'سورة المؤمنون', 'Al-Muminoon', 'https://quran.com/23', 'The Believers', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:02', '2018-06-07 06:07:02'),
(24, 24, 'سورة النور', 'An-Noor', 'https://quran.com/24', 'The Light', 'Medinan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:03', '2018-06-07 06:07:03'),
(25, 25, 'سورة الفرقان', 'Al-Furqaan', 'https://quran.com/25', 'The Criterion', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:03', '2018-06-07 06:07:03'),
(26, 26, 'سورة الشعراء', 'Ash-Shu\'araa', 'https://quran.com/26', 'The Poets', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:04', '2018-06-07 06:07:04'),
(27, 27, 'سورة النمل', 'An-Naml', 'https://quran.com/27', 'The Ant', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:05', '2018-06-07 06:07:05'),
(28, 28, 'سورة القصص', 'Al-Qasas', 'https://quran.com/28', 'The Stories', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:05', '2018-06-07 06:07:05'),
(29, 29, 'سورة العنكبوت', 'Al-Ankaboot', 'https://quran.com/29', 'The Spider', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:06', '2018-06-07 06:07:06'),
(30, 30, 'سورة الروم', 'Ar-Room', 'https://quran.com/30', 'The Romans', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:06', '2018-06-07 06:07:06'),
(31, 31, 'سورة لقمان', 'Luqman', 'https://quran.com/31', 'Luqman', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:06', '2018-06-07 06:07:06'),
(32, 32, 'سورة السجدة', 'As-Sajda', 'https://quran.com/32', 'The Prostration', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:06', '2018-06-07 06:07:06'),
(33, 33, 'سورة الأحزاب', 'Al-Ahzaab', 'https://quran.com/33', 'The Clans', 'Medinan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:07', '2018-06-07 06:07:07'),
(34, 34, 'سورة سبإ', 'Saba', 'https://quran.com/34', 'Sheba', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:07', '2018-06-07 06:07:07'),
(35, 35, 'سورة فاطر', 'Faatir', 'https://quran.com/35', 'The Originator', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:07', '2018-06-07 06:07:07'),
(36, 36, 'سورة يس', 'Yaseen', 'https://quran.com/36', 'Yaseen', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:07', '2018-06-07 06:07:07'),
(37, 37, 'سورة الصافات', 'As-Saaffaat', 'https://quran.com/37', 'Those drawn up in Ranks', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:08', '2018-06-07 06:07:08'),
(38, 38, 'سورة ص', 'Saad', 'https://quran.com/38', 'The letter Saad', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:09', '2018-06-07 06:07:09'),
(39, 39, 'سورة الزمر', 'Az-Zumar', 'https://quran.com/39', 'The Groups', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:09', '2018-06-07 06:07:09'),
(40, 40, 'سورة غافر', 'Ghafir', 'https://quran.com/40', 'The Forgiver', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:10', '2018-06-07 06:07:10'),
(41, 41, 'سورة فصلت', 'Fussilat', 'https://quran.com/41', 'Explained in detail', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:10', '2018-06-07 06:07:10'),
(42, 42, 'سورة الشورى', 'Ash-Shura', 'https://quran.com/42', 'Consultation', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:11', '2018-06-07 06:07:11'),
(43, 43, 'سورة الزخرف', 'Az-Zukhruf', 'https://quran.com/43', 'Ornaments of gold', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:11', '2018-06-07 06:07:11'),
(44, 44, 'سورة الدخان', 'Ad-Dukhaan', 'https://quran.com/44', 'The Smoke', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:12', '2018-06-07 06:07:12'),
(45, 45, 'سورة الجاثية', 'Al-Jaathiya', 'https://quran.com/45', 'Crouching', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:12', '2018-06-07 06:07:12'),
(46, 46, 'سورة الأحقاف', 'Al-Ahqaf', 'https://quran.com/46', 'The Dunes', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:12', '2018-06-07 06:07:12'),
(47, 47, 'سورة محمد', 'Muhammad', 'https://quran.com/47', 'Muhammad', 'Medinan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:12', '2018-06-07 06:07:12'),
(48, 48, 'سورة الفتح', 'Al-Fath', 'https://quran.com/48', 'The Victory', 'Medinan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:13', '2018-06-07 06:07:13'),
(49, 49, 'سورة الحجرات', 'Al-Hujuraat', 'https://quran.com/49', 'The Inner Apartments', 'Medinan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:13', '2018-06-07 06:07:13'),
(50, 50, 'سورة ق', 'Qaaf', 'https://quran.com/50', 'The letter Qaaf', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:13', '2018-06-07 06:07:13'),
(51, 51, 'سورة الذاريات', 'Adh-Dhaariyat', 'https://quran.com/51', 'The Winnowing Winds', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:13', '2018-06-07 06:07:13'),
(52, 52, 'سورة الطور', 'At-Tur', 'https://quran.com/52', 'The Mount', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:13', '2018-06-07 06:07:13'),
(53, 53, 'سورة النجم', 'An-Najm', 'https://quran.com/53', 'The Star', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:14', '2018-06-07 06:07:14'),
(54, 54, 'سورة القمر', 'Al-Qamar', 'https://quran.com/54', 'The Moon', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:14', '2018-06-07 06:07:14'),
(55, 55, 'سورة الرحمن', 'Ar-Rahmaan', 'https://quran.com/55', 'The Beneficent', 'Medinan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:15', '2018-06-07 06:07:15'),
(56, 56, 'سورة الواقعة', 'Al-Waaqia', 'https://quran.com/56', 'The Inevitable', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:15', '2018-06-07 06:07:15'),
(57, 57, 'سورة الحديد', 'Al-Hadid', 'https://quran.com/57', 'The Iron', 'Medinan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:16', '2018-06-07 06:07:16'),
(58, 58, 'سورة المجادلة', 'Al-Mujaadila', 'https://quran.com/58', 'The Pleading Woman', 'Medinan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:16', '2018-06-07 06:07:16'),
(59, 59, 'سورة الحشر', 'Al-Hashr', 'https://quran.com/59', 'The Exile', 'Medinan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:16', '2018-06-07 06:07:16'),
(60, 60, 'سورة الممتحنة', 'Al-Mumtahana', 'https://quran.com/60', 'She that is to be examined', 'Medinan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:16', '2018-06-07 06:07:16'),
(61, 61, 'سورة الصف', 'As-Saff', 'https://quran.com/61', 'The Ranks', 'Medinan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:16', '2018-06-07 06:07:16'),
(62, 62, 'سورة الجمعة', 'Al-Jumu\'a', 'https://quran.com/62', 'Friday', 'Medinan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:16', '2018-06-07 06:07:16'),
(63, 63, 'سورة المنافقون', 'Al-Munaafiqoon', 'https://quran.com/63', 'The Hypocrites', 'Medinan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:16', '2018-06-07 06:07:16'),
(64, 64, 'سورة التغابن', 'At-Taghaabun', 'https://quran.com/64', 'Mutual Disillusion', 'Medinan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:16', '2018-06-07 06:07:16'),
(65, 65, 'سورة الطلاق', 'At-Talaaq', 'https://quran.com/65', 'Divorce', 'Medinan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:17', '2018-06-07 06:07:17'),
(66, 66, 'سورة التحريم', 'At-Tahrim', 'https://quran.com/66', 'The Prohibition', 'Medinan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:17', '2018-06-07 06:07:17'),
(67, 67, 'سورة الملك', 'Al-Mulk', 'https://quran.com/67', 'The Sovereignty', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:17', '2018-06-07 06:07:17'),
(68, 68, 'سورة القلم', 'Al-Qalam', 'https://quran.com/68', 'The Pen', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:17', '2018-06-07 06:07:17'),
(69, 69, 'سورة الحاقة', 'Al-Haaqqa', 'https://quran.com/69', 'The Reality', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:17', '2018-06-07 06:07:17'),
(70, 70, 'سورة المعارج', 'Al-Ma\'aarij', 'https://quran.com/70', 'The Ascending Stairways', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:18', '2018-06-07 06:07:18'),
(71, 71, 'سورة نوح', 'Nooh', 'https://quran.com/71', 'Noah', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:18', '2018-06-07 06:07:18'),
(72, 72, 'سورة الجن', 'Al-Jinn', 'https://quran.com/72', 'The Jinn', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:18', '2018-06-07 06:07:18'),
(73, 73, 'سورة المزمل', 'Al-Muzzammil', 'https://quran.com/73', 'The Enshrouded One', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:18', '2018-06-07 06:07:18'),
(74, 74, 'سورة المدثر', 'Al-Muddaththir', 'https://quran.com/74', 'The Cloaked One', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:18', '2018-06-07 06:07:18'),
(75, 75, 'سورة القيامة', 'Al-Qiyaama', 'https://quran.com/75', 'The Resurrection', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:19', '2018-06-07 06:07:19'),
(76, 76, 'سورة الانسان', 'Al-Insaan', 'https://quran.com/76', 'Man', 'Medinan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:19', '2018-06-07 06:07:19'),
(77, 77, 'سورة المرسلات', 'Al-Mursalaat', 'https://quran.com/77', 'The Emissaries', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:19', '2018-06-07 06:07:19'),
(78, 78, 'سورة النبإ', 'An-Naba', 'https://quran.com/78', 'The Announcement', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:20', '2018-06-07 06:07:20'),
(79, 79, 'سورة النازعات', 'An-Naazi\'aat', 'https://quran.com/79', 'Those who drag forth', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:20', '2018-06-07 06:07:20'),
(80, 80, 'سورة عبس', 'Abasa', 'https://quran.com/80', 'He frowned', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:20', '2018-06-07 06:07:20'),
(81, 81, 'سورة التكوير', 'At-Takwir', 'https://quran.com/81', 'The Overthrowing', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:21', '2018-06-07 06:07:21'),
(82, 82, 'سورة الإنفطار', 'Al-Infitaar', 'https://quran.com/82', 'The Cleaving', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:21', '2018-06-07 06:07:21'),
(83, 83, 'سورة المطففين', 'Al-Mutaffifin', 'https://quran.com/83', 'Defrauding', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:21', '2018-06-07 06:07:21'),
(84, 84, 'سورة الإنشقاق', 'Al-Inshiqaaq', 'https://quran.com/84', 'The Splitting Open', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:21', '2018-06-07 06:07:21'),
(85, 85, 'سورة البروج', 'Al-Burooj', 'https://quran.com/85', 'The Constellations', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:21', '2018-06-07 06:07:21'),
(86, 86, 'سورة الطارق', 'At-Taariq', 'https://quran.com/86', 'The Morning Star', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:22', '2018-06-07 06:07:22'),
(87, 87, 'سورة الأعلى', 'Al-A\'laa', 'https://quran.com/87', 'The Most High', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:22', '2018-06-07 06:07:22'),
(88, 88, 'سورة الغاشية', 'Al-Ghaashiya', 'https://quran.com/88', 'The Overwhelming', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:22', '2018-06-07 06:07:22'),
(89, 89, 'سورة الفجر', 'Al-Fajr', 'https://quran.com/89', 'The Dawn', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:22', '2018-06-07 06:07:22'),
(90, 90, 'سورة البلد', 'Al-Balad', 'https://quran.com/90', 'The City', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:22', '2018-06-07 06:07:22'),
(91, 91, 'سورة الشمس', 'Ash-Shams', 'https://quran.com/91', 'The Sun', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:22', '2018-06-07 06:07:22'),
(92, 92, 'سورة الليل', 'Al-Lail', 'https://quran.com/92', 'The Night', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:23', '2018-06-07 06:07:23'),
(93, 93, 'سورة الضحى', 'Ad-Dhuhaa', 'https://quran.com/93', 'The Morning Hours', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:23', '2018-06-07 06:07:23'),
(94, 94, 'سورة الشرح', 'Ash-Sharh', 'https://quran.com/94', 'The Consolation', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:23', '2018-06-07 06:07:23'),
(95, 95, 'سورة التين', 'At-Tin', 'https://quran.com/95', 'The Fig', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:23', '2018-06-07 06:07:23'),
(96, 96, 'سورة العلق', 'Al-Alaq', 'https://quran.com/96', 'The Clot', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:23', '2018-06-07 06:07:23'),
(97, 97, 'سورة القدر', 'Al-Qadr', 'https://quran.com/97', 'The Power, Fate', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:23', '2018-06-07 06:07:23'),
(98, 98, 'سورة البينة', 'Al-Bayyina', 'https://quran.com/98', 'The Evidence', 'Medinan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:23', '2018-06-07 06:07:23'),
(99, 99, 'سورة الزلزلة', 'Az-Zalzala', 'https://quran.com/99', 'The Earthquake', 'Medinan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:23', '2018-06-07 06:07:23'),
(100, 100, 'سورة العاديات', 'Al-Aadiyaat', 'https://quran.com/100', 'The Chargers', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:23', '2018-06-07 06:07:23'),
(101, 101, 'سورة القارعة', 'Al-Qaari\'a', 'https://quran.com/101', 'The Calamity', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:23', '2018-06-07 06:07:23'),
(102, 102, 'سورة التكاثر', 'At-Takaathur', 'https://quran.com/102', 'Competition', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:23', '2018-06-07 06:07:23'),
(103, 103, 'سورة العصر', 'Al-Asr', 'https://quran.com/103', 'The Declining Day, Epoch', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:24', '2018-06-07 06:07:24'),
(104, 104, 'سورة الهمزة', 'Al-Humaza', 'https://quran.com/104', 'The Traducer', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:24', '2018-06-07 06:07:24'),
(105, 105, 'سورة الفيل', 'Al-Fil', 'https://quran.com/105', 'The Elephant', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:24', '2018-06-07 06:07:24'),
(106, 106, 'سورة قريش', 'Quraish', 'https://quran.com/106', 'Quraysh', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:24', '2018-06-07 06:07:24'),
(107, 107, 'سورة الماعون', 'Al-Maa\'un', 'https://quran.com/107', 'Almsgiving', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:24', '2018-06-07 06:07:24'),
(108, 108, 'سورة الكوثر', 'Al-Kawthar', 'https://quran.com/108', 'Abundance', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:24', '2018-06-07 06:07:24'),
(109, 109, 'سورة الكافرون', 'Al-Kaafiroon', 'https://quran.com/109', 'The Disbelievers', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:24', '2018-06-07 06:07:24'),
(110, 110, 'سورة النصر', 'An-Nasr', 'https://quran.com/110', 'Divine Support', 'Medinan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:24', '2018-06-07 06:07:24'),
(111, 111, 'سورة المسد', 'Al-Masad', 'https://quran.com/111', 'The Palm Fibre', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:24', '2018-06-07 06:07:24'),
(112, 112, 'سورة الإخلاص', 'Al-Ikhlaas', 'https://quran.com/112', 'Sincerity', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:24', '2018-06-07 06:07:24'),
(113, 113, 'سورة الفلق', 'Al-Falaq', 'https://quran.com/113', 'The Dawn', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:24', '2018-06-07 06:07:24'),
(114, 114, 'سورة الناس', 'An-Naas', 'https://quran.com/114', 'Mankind', 'Meccan', NULL, NULL, NULL, NULL, NULL, '2018-06-07 06:07:24', '2018-06-07 06:07:24');

-- --------------------------------------------------------

--
-- Table structure for table `surh_videos`
--

CREATE TABLE `surh_videos` (
  `id` int(11) NOT NULL,
  `surh_id` int(11) NOT NULL,
  `video_link` mediumtext NOT NULL,
  `img_ar` mediumtext DEFAULT NULL,
  `img_en` mediumtext DEFAULT NULL,
  `img_ru` mediumtext DEFAULT NULL,
  `from_aya` int(11) NOT NULL,
  `to_aya` int(11) NOT NULL,
  `sort` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `surh_videos`
--

INSERT INTO `surh_videos` (`id`, `surh_id`, `video_link`, `img_ar`, `img_en`, `img_ru`, `from_aya`, `to_aya`, `sort`, `created_at`, `updated_at`) VALUES
(2, 1, 'https://www.youtube.com/embed/ZRKWcTbYwT4', 'public/uploads/surhs/images/ar/1- Surah Al-Fatihah.png', 'public/uploads/surhs/images/en/1- Surah Al-Fatihah.png', 'public/uploads/surhs/images/ru/1- Surah Al-Fatihah.png', 1, 3, 1, '2023-05-16 12:31:09', '2023-05-16 12:31:09'),
(5, 1, 'https://www.youtube.com/embed/xRPhXuDH6Ho', '', 'public/uploads/surhs/images/en/1- Surah Al-Fatihah 4 to 6_Moment.jpg', '', 4, 6, 2, '2023-05-16 12:31:28', '2023-05-16 12:31:28'),
(6, 1, 'https://www.youtube.com/embed/6e7LrJR5pGQ', '', 'public/uploads/surhs/images/en/1- Surah Al-Fatihah 7_Moment.jpg', '', 7, 7, 3, '2023-05-17 15:40:57', '2023-05-17 15:40:57'),
(7, 1, 'https://www.youtube.com/embed/rxOvQIPiNLU', '', 'public/uploads/surhs/images/en/1- Surah Al-Fatihah 1 to 7_Moment.jpg', '', 1, 7, 4, '2023-05-17 15:40:50', '2023-05-17 15:40:50'),
(9, 112, 'https://www.youtube.com/embed/0V6wPdSuUuE', '', 'public/uploads/surhs/images/en/112- Surah Al-Ikhlas_Moment.jpg', '', 1, 4, 1, '2023-05-16 12:35:42', '2023-05-16 12:35:42'),
(10, 113, 'https://www.youtube.com/embed/X9UHC9rcgz0', '', 'public/uploads/surhs/images/en/113- Surah Al-Falaq 1 to 3_Moment.jpg', '', 1, 3, 1, '2023-05-18 12:20:47', '2023-05-18 12:20:47'),
(11, 113, 'https://www.youtube.com/embed/sO1mwTJIOl8', '', 'public/uploads/surhs/images/en/113- Surah Al-Falaq 4 to 5_Moment.jpg', '', 4, 5, 2, '2023-05-18 12:21:12', '2023-05-18 12:21:12'),
(12, 113, 'https://www.youtube.com/embed/MTPkkrC2FSg', '', 'public/uploads/surhs/images/en/113- Surah Al-Falaq 1 to 5_Moment.jpg', '', 1, 5, 3, '2023-05-18 13:04:20', '2023-05-18 13:04:20'),
(13, 114, 'https://www.youtube.com/embed/AQhYb-SZPpc', '', 'public/uploads/surhs/images/en/114- Surah An-Nas 1 to 3_Moment.jpg', '', 1, 3, 1, '2023-05-18 13:05:10', '2023-05-18 13:05:10'),
(14, 114, 'https://www.youtube.com/embed/MOvwzu9I66U', '', 'public/uploads/surhs/images/en/114- Surah An-Nas 4 to 6_Moment.jpg', '', 4, 6, 2, '2023-05-18 13:05:45', '2023-05-18 13:05:45'),
(15, 114, 'https://www.youtube.com/embed/yPSUqmb42GU', '', 'public/uploads/surhs/images/en/114-  Surah An-Nas 1 to 6_Moment.jpg', '', 1, 6, 3, '2023-05-18 13:06:12', '2023-05-18 13:06:12'),
(16, 111, 'https://www.youtube.com/embed/DpeQzSsyWtA', '', 'public/uploads/surhs/images/en/111- Surah Al-Masad 1 to 3_Moment.jpg', '', 1, 3, 1, '2023-05-18 13:06:55', '2023-05-18 13:06:55'),
(17, 111, 'https://www.youtube.com/embed/CatsXQjAFps', '', 'public/uploads/surhs/images/en/111- Surah Al-Masad 4 to 5_Moment.jpg', '', 4, 5, 2, '2023-05-18 13:07:20', '2023-05-18 13:07:20'),
(18, 111, 'https://www.youtube.com/embed/gLDCUHUBJnM', '', 'public/uploads/surhs/images/en/111- Surah Al-Masad 1 to 5_Moment.jpg', '', 1, 5, 3, '2023-05-18 13:07:40', '2023-05-18 13:07:40'),
(22, 109, 'https://www.youtube.com/embed/XiO4-y2QouA', '', 'public/uploads/surhs/images/en/109- Surah Al-Kafirun 1 to 2_Moment.jpg', '', 1, 2, 1, '2023-05-19 16:05:22', '2023-05-19 16:05:22'),
(23, 109, 'https://www.youtube.com/embed/lK_vKRFnQF4', '', 'public/uploads/surhs/images/en/109- Surah Al-Kafirun 3 to 4_Moment.jpg', '', 3, 4, 2, '2023-05-19 16:05:48', '2023-05-19 16:05:48'),
(24, 109, 'https://www.youtube.com/embed/BvVIn6alAe0', '', 'public/uploads/surhs/images/en/109- Surah Al-Kafirun 5 to 6_Moment.jpg', '', 5, 6, 4, '2023-06-18 13:27:54', '2023-06-18 13:27:54'),
(25, 109, 'https://www.youtube.com/embed/N18x8L242Ss', '', 'public/uploads/surhs/images/en/109- Surah Al-Kafirun 1 to 6_Moment.jpg', '', 1, 6, 4, '2023-05-19 16:06:36', '2023-05-19 16:06:36'),
(26, 108, 'https://www.youtube.com/embed/dl8s0ugXJEM', '', 'public/uploads/surhs/images/en/108- Surah Al-Kawthar 1 to 3_Moment.jpg', '', 1, 3, 1, '2023-05-19 16:07:02', '2023-05-19 16:07:02'),
(27, 107, 'https://www.youtube.com/embed/LW85dro-v6E', '', 'public/uploads/surhs/images/en/107- Surah Al-Ma\'un 1 to 2_Moment.jpg', '', 1, 2, 1, '2023-05-19 16:07:36', '2023-05-19 16:07:36'),
(28, 107, 'https://www.youtube.com/embed/yNtRbS7wdjY', '', 'public/uploads/surhs/images/en/107- Surah Al-Ma\'un 3 to 5_Moment.jpg', '', 3, 5, 2, '2023-05-19 16:08:02', '2023-05-19 16:08:02'),
(29, 107, 'https://www.youtube.com/embed/e90ici28rQk', '', 'public/uploads/surhs/images/en/107- Surah Al-Ma\'un 6 to 7_Moment.jpg', '', 6, 7, 3, '2023-05-20 09:13:51', '2023-05-20 09:13:51'),
(30, 107, 'https://www.youtube.com/embed/0WciWcHMiUc', '', 'public/uploads/surhs/images/en/107- Surah Al-Ma\'un 1 to 7_Moment.jpg', '', 1, 7, 4, '2023-05-20 09:32:24', '2023-05-20 09:32:24'),
(31, 110, 'https://www.youtube.com/embed/77IZISxxkwU', '', 'public/uploads/surhs/images/en/110- Surah An-Nasr 1 to 2_Moment.jpg', '', 1, 2, 1, '2023-05-20 09:34:33', '2023-05-20 09:34:33'),
(32, 110, 'https://www.youtube.com/embed/twVeYJj6mKE', '', 'public/uploads/surhs/images/en/110- Surah An-Nasr 3_Moment.jpg', '', 3, 3, 2, '2023-05-20 09:34:51', '2023-05-20 09:34:51'),
(33, 110, 'https://www.youtube.com/embed/hWxOzFI_cQE', '', 'public/uploads/surhs/images/en/110- Surah An-Nasr 1 to 3_Moment.jpg', '', 1, 3, 3, '2023-05-31 15:06:39', '2023-05-31 15:06:39'),
(34, 106, 'https://www.youtube.com/embed/k7UhcDi8leY', '', 'public/uploads/surhs/images/en/106- Surah Quraysh 1 to 2_Moment.jpg', '', 1, 2, 1, '2023-05-20 09:37:12', '2023-05-20 09:37:12'),
(35, 106, 'https://www.youtube.com/embed/0ToX7hxJ12E', '', 'public/uploads/surhs/images/en/106- Surah Quraysh 3 to 4_Moment.jpg', '', 3, 4, 2, '2023-05-20 09:37:35', '2023-05-20 09:37:35'),
(36, 106, 'https://www.youtube.com/embed/AaUSiA6I5TA', '', 'public/uploads/surhs/images/en/106- Surah Quraysh 1 to 4_Moment.jpg', '', 1, 4, 3, '2023-05-20 09:38:03', '2023-05-20 09:38:03'),
(39, 105, 'https://www.youtube.com/embed/WRoD_puhZOQ', '', 'public/uploads/surhs/images/en/105- Surah Al-Fil 1 to 2_Moment-1684588489.jpg', '', 1, 2, 1, '2023-05-20 13:14:49', '2023-05-20 13:14:49'),
(40, 105, 'https://www.youtube.com/embed/Iu_HeT6ItWo', '', 'public/uploads/surhs/images/en/105- Surah Al-Fil 3 to 4_Moment-1684588517.jpg', '', 3, 4, 2, '2023-05-20 13:15:17', '2023-05-20 13:15:17'),
(41, 105, 'https://www.youtube.com/embed/zyb7G6ZHhoQ', '', 'public/uploads/surhs/images/en/105- Surah Al-Fil 5_Moment-1684588543.jpg', '', 5, 5, 3, '2023-05-20 13:15:43', '2023-05-20 13:15:43'),
(42, 105, 'https://www.youtube.com/embed/g4r5sq3-ekY', '', 'public/uploads/surhs/images/en/105- Surah Al-Fil 1 to 5_Moment-1684588569.jpg', '', 1, 5, 4, '2023-05-20 13:16:09', '2023-05-20 13:16:09'),
(43, 104, 'https://www.youtube.com/embed/YY1PA-JABZ0', '', 'public/uploads/surhs/images/en/104- Surah Al-Humazah 1 to 3_Moment-1686065359.jpg', '', 1, 3, 1, '2023-06-06 15:29:19', '2023-06-06 15:29:19'),
(44, 104, 'https://www.youtube.com/embed/xEEFxoWt5vk', '', 'public/uploads/surhs/images/en/104- Surah Al-Humazah 4 to 6_Moment-1686065463.jpg', '', 4, 6, 2, '2023-06-06 15:31:03', '2023-06-06 15:31:03'),
(45, 104, 'https://www.youtube.com/embed/xDpCfTESofk', 'public/uploads/surhs/images/ar/104- Surah Al-Humazah 7 to 9_Moment-1686065570.jpg', 'public/uploads/surhs/images/en/104- Surah Al-Humazah 7 to 9_Moment1-1686066893.jpg', '', 7, 9, 3, '2023-06-06 15:54:53', '2023-06-06 15:54:53'),
(46, 103, 'https://www.youtube.com/embed/FCD1tS-eGAY', '', 'public/uploads/surhs/images/en/Al-Asr 1 to 2-1687088511.jpg', '', 1, 2, 1, '2023-06-18 11:41:51', '2023-06-18 11:41:51'),
(47, 109, 'https://www.youtube.com/embed/REKbQWbnFCw', '', 'public/uploads/surhs/images/en/109- Surah Al-Kafirun 1 to 4_Moment-1687094845.jpg', '', 1, 4, 3, '2023-06-18 13:27:25', '2023-06-18 13:27:25'),
(48, 107, 'https://www.youtube.com/embed/e-5Xs-Tjji4', '', 'public/uploads/surhs/images/en/107- Surah Al-Ma\'un 1 to 5_Moment-1687099009.jpg', '', 1, 5, 2, '2023-06-18 14:36:49', '2023-06-18 14:36:49'),
(49, 104, 'https://www.youtube.com/embed/AI6bPbLmvQQ', '', 'public/uploads/surhs/images/en/104- Surah Al-Humazah 1 to 6_Moment-1687099914.jpg', '', 1, 6, 2, '2023-06-18 14:51:54', '2023-06-18 14:51:54'),
(50, 104, 'https://www.youtube.com/embed/Zcpo5SevPLM', '', 'public/uploads/surhs/images/en/104- Surah Al-Humazah 1 to 9_Moment-1687101150.jpg', '', 1, 9, 5, '2023-06-18 15:12:30', '2023-06-18 15:12:30'),
(51, 103, 'https://www.youtube.com/embed/4ASDJPw9LGA', '', 'public/uploads/surhs/images/en/103- Surah Al-Asr 3_Moment-1687101803.jpg', '', 3, 3, 2, '2023-06-18 15:23:23', '2023-06-18 15:23:23'),
(52, 103, 'https://www.youtube.com/embed/8-y3yuyBUI4', '', 'public/uploads/surhs/images/en/103- Surah Al-Asr 1 to 3_Moment-1687102043.jpg', '', 1, 3, 3, '2023-06-18 15:27:23', '2023-06-18 15:27:23'),
(53, 102, 'https://www.youtube.com/embed/HudsDhMcUvo', '', 'public/uploads/surhs/images/en/102- Surah At-Takathur  1 to 2_Moment-1687109744.jpg', '', 1, 2, 1, '2023-06-18 17:35:44', '2023-06-18 17:35:44'),
(54, 102, 'https://www.youtube.com/embed/mvWyJYQOTUA', '', 'public/uploads/surhs/images/en/102- Surah At-Takathur  3 to 4_Moment-1687109841.jpg', '', 3, 4, 2, '2023-06-18 17:37:21', '2023-06-18 17:37:21'),
(55, 102, 'https://www.youtube.com/embed/SX1hD9MPfKQ', '', 'public/uploads/surhs/images/en/102- Surah At-Takathur  1 to 4_Moment-1687109938.jpg', '', 1, 4, 3, '2023-06-18 17:38:58', '2023-06-18 17:38:58'),
(56, 102, 'https://www.youtube.com/embed/XH0Vc6r_UpE', '', 'public/uploads/surhs/images/en/102- Surah At-Takathur  5 to 6_Moment-1687110011.jpg', '', 5, 6, 4, '2023-06-18 17:40:11', '2023-06-18 17:40:11'),
(57, 102, 'https://www.youtube.com/embed/3a8gL5vTix4', '', 'public/uploads/surhs/images/en/102- Surah At-Takathur  1 to 6_Moment-1687110102.jpg', '', 1, 6, 5, '2023-06-18 17:41:42', '2023-06-18 17:41:42'),
(58, 102, 'https://www.youtube.com/embed/WGqcELIa_PU', '', 'public/uploads/surhs/images/en/102- Surah At-Takathur 7 to 8_Moment-1687110207.jpg', '', 7, 8, 6, '2023-06-18 17:43:27', '2023-06-18 17:43:27'),
(59, 102, 'https://www.youtube.com/embed/l2qJMjIzfI8', '', 'public/uploads/surhs/images/en/102- Surah At-Takathur 1 to 8_Moment-1687110898.jpg', '', 1, 8, 7, '2023-06-18 17:54:58', '2023-06-18 17:54:58'),
(60, 101, 'https://youtube.com/embed/Qk6ISimZUAU', '', 'public/uploads/surhs/images/en/101- Surah Al-Qari\'ah 1 to 3_Moment-1690379084.jpg', '', 1, 3, 1, '2023-07-26 14:43:55', '2023-07-26 14:43:55'),
(61, 101, 'https://youtube.com/embed/suGKK7UojSw', '', 'public/uploads/surhs/images/en/101- Surah Al-Qari\'ah 4 to 5_Moment-1690380281.jpg', '', 4, 5, 2, '2023-07-26 14:42:34', '2023-07-26 14:42:34'),
(62, 101, 'https://youtube.com/embed/Nl9tmuyQQU8', '', 'public/uploads/surhs/images/en/101- Surah Al-Qari\'ah 1 to 5_Moment-1690380439.jpg', '', 1, 5, 3, '2023-07-26 14:42:40', '2023-07-26 14:42:40'),
(63, 101, 'https://youtube.com/embed/5qUQjr21QqM', '', 'public/uploads/surhs/images/en/101- Surah Al-Qari\'ah 6 to 7_Moment-1690381316.jpg', '', 6, 7, 4, '2023-07-26 14:42:28', '2023-07-26 14:42:28'),
(64, 101, 'https://youtube.com/embed/H8z8Plbg0DI', '', 'public/uploads/surhs/images/en/101- Surah Al-Qari\'ah 1 to 7_Moment-1690381547.jpg', '', 1, 7, 5, '2023-07-26 14:42:57', '2023-07-26 14:42:57'),
(65, 101, 'https://youtube.com/embed/7ujD11mVKsw', '', 'public/uploads/surhs/images/en/101- Surah Al-Qari\'ah 8 to 9_Moment-1690381803.jpg', '', 8, 9, 6, '2023-07-26 14:43:04', '2023-07-26 14:43:04'),
(66, 101, 'https://youtube.com/embed/VzKb0cS7AtM', '', 'public/uploads/surhs/images/en/101- Surah Al-Qari\'ah 1 to 9_Moment-1690382066.jpg', '', 1, 9, 7, '2023-07-26 14:43:10', '2023-07-26 14:43:10'),
(67, 101, 'https://youtube.com/embed/Trv1ZBAixXQ', '', 'public/uploads/surhs/images/en/101- Surah Al-Qari\'ah 10 to 11_Moment-1690382272.jpg', '', 10, 11, 8, '2023-07-26 14:44:08', '2023-07-26 14:44:08'),
(68, 101, 'https://youtube.com/embed/LbAqb_FEcNI', '', 'public/uploads/surhs/images/en/101- Surah Al-Qari\'ah 1 to 11_Moment-1690382358.jpg', '', 1, 11, 9, '2023-07-26 14:44:20', '2023-07-26 14:44:20'),
(69, 100, 'https://youtube.com/embed//WvOhnQwAK84', '', 'public/uploads/surhs/images/en/100- Surah Al-\'Adiyat 1 to 3_Moment-1690445676.jpg', '', 1, 3, 1, '2023-07-27 08:14:36', '2023-07-27 08:14:36'),
(70, 100, 'https://youtube.com/embed/pWvnK9mKXbg', '', 'public/uploads/surhs/images/en/100- Surah Al-\'Adiyat 4 to 5_Moment-1690445754.jpg', '', 4, 5, 2, '2023-07-27 08:15:54', '2023-07-27 08:15:54'),
(71, 100, 'https://youtube.com/embed//2hwChOwRls4', '', 'public/uploads/surhs/images/en/100- Surah Al-\'Adiyat 1 to 5_Moment-1690445802.jpg', '', 1, 5, 3, '2023-07-27 08:27:36', '2023-07-27 08:27:36'),
(72, 100, 'https://youtube.com/embed//uQkmgrUMqg0', '', 'public/uploads/surhs/images/en/100- Surah Al-\'Adiyat 6 to 7_Moment-1690445859.jpg', '', 6, 7, 4, '2023-07-27 08:27:47', '2023-07-27 08:27:47'),
(73, 100, 'https://youtube.com/embed//QNFuLawgxCc', '', 'public/uploads/surhs/images/en/100- Surah Al-\'Adiyat 1 to 7_Moment-1690446335.jpg', '', 1, 7, 5, '2023-07-27 08:27:53', '2023-07-27 08:27:53'),
(74, 100, 'https://youtube.com/embed//VUsVZHVe5S8', '', 'public/uploads/surhs/images/en/100- Surah Al-\'Adiyat 8 to 9_Moment-1690446397.jpg', '', 8, 9, 6, '2023-07-27 08:27:59', '2023-07-27 08:27:59'),
(75, 100, 'https://youtube.com/embed/4-lphiYTDEA', '', 'public/uploads/surhs/images/en/100- Surah Al-\'Adiyat 1 to 9_Moment-1690446444.jpg', '', 1, 9, 7, '2023-07-27 08:27:24', '2023-07-27 08:27:24'),
(76, 100, 'https://youtube.com/embed/py5eVPqsZT4', '', 'public/uploads/surhs/images/en/100- Surah Al-\'Adiyat 10 to 11_Moment-1690446536.jpg', '', 10, 11, 8, '2023-07-27 08:28:56', '2023-07-27 08:28:56'),
(77, 100, 'https://youtube.com/embed//HTjcD2-qkwY', '', 'public/uploads/surhs/images/en/100- Surah Al-\'Adiyat 1 to 11_Moment-1690446584.jpg', '', 1, 11, 9, '2023-07-27 08:29:44', '2023-07-27 08:29:44'),
(78, 99, 'https://youtube.com/embed/WcvounSov0Q', '', 'public/uploads/surhs/images/en/99- Surah Az-Zalzalah 1 to 2_Moment-1690446833.jpg', '', 1, 2, 1, '2023-07-27 08:33:53', '2023-07-27 08:33:53'),
(79, 99, 'https://youtube.com/embed/NrWFtVaVaIU', '', 'public/uploads/surhs/images/en/99- Surah Az-Zalzalah 3 to 4_Moment-1690446894.jpg', '', 3, 4, 2, '2023-07-27 08:34:54', '2023-07-27 08:34:54'),
(80, 99, 'https://youtube.com/embed/c8dl8yyhwtg', '', 'public/uploads/surhs/images/en/99- Surah Az-Zalzalah 1 to 4_Moment-1690447011.jpg', '', 1, 4, 3, '2023-07-27 08:36:51', '2023-07-27 08:36:51'),
(81, 99, 'https://youtube.com/embed/2EGrGB8o3Jc', '', 'public/uploads/surhs/images/en/99- Surah Az-Zalzalah 5 to 6_Moment-1690447097.jpg', '', 5, 6, 4, '2023-07-27 08:38:17', '2023-07-27 08:38:17'),
(82, 99, 'https://youtube.com/embed//9F5erCQfRJ8', '', 'public/uploads/surhs/images/en/99- Surah Az-Zalzalah 1 to 6_Moment-1690447328.jpg', '', 1, 6, 5, '2023-07-27 08:42:08', '2023-07-27 08:42:08'),
(83, 99, 'https://youtube.com/embed/LMZrsH-mRP4', '', 'public/uploads/surhs/images/en/99- Surah Az-Zalzalah 7 to 8_Moment-1690447387.jpg', '', 7, 8, 6, '2023-07-27 08:43:07', '2023-07-27 08:43:07'),
(84, 99, 'https://youtube.com/embed/_nCAsakSmz4', '', 'public/uploads/surhs/images/en/99- Surah Az-Zalzalah 1 to 8_Moment-1690447436.jpg', '', 1, 8, 7, '2023-07-27 08:43:56', '2023-07-27 08:43:56'),
(85, 98, 'https://youtube.com/embed/xr_wZsIYKus', '', 'public/uploads/surhs/images/en/98- Surah Al-Bayyinah 1_Moment-1690464478.jpg', '', 1, 1, 1, '2023-07-27 13:27:58', '2023-07-27 13:27:58'),
(86, 98, 'https://youtube.com/embed/MlEp6_ss9Y8', '', 'public/uploads/surhs/images/en/98- Surah Al-Bayyinah 2 to 3_Moment-1690464619.jpg', '', 2, 3, 2, '2023-07-27 13:30:19', '2023-07-27 13:30:19'),
(87, 98, 'https://youtube.com/embed/UTVbXp8x9f0', '', 'public/uploads/surhs/images/en/98- Surah Al-Bayyinah 1 to 3_Moment-1690464674.jpg', '', 1, 3, 3, '2023-07-27 13:31:14', '2023-07-27 13:31:14'),
(88, 98, 'https://youtube.com/embed/BSavFZdaTTc', '', 'public/uploads/surhs/images/en/98- Surah Al-Bayyinah 4_Moment-1690464739.jpg', '', 4, 4, 4, '2023-07-27 13:32:19', '2023-07-27 13:32:19'),
(89, 98, 'https://youtube.com/embed/tm9WF3AScX0', '', 'public/uploads/surhs/images/en/98- Surah Al-Bayyinah 1 to 4_Moment-1690464801.jpg', '', 1, 4, 5, '2023-07-27 13:33:21', '2023-07-27 13:33:21'),
(90, 98, 'https://youtube.com/embed/Aux9AV8U3qA', '', 'public/uploads/surhs/images/en/98- Surah Al-Bayyinah 5_Moment-1690465065.jpg', '', 5, 5, 6, '2023-07-27 13:37:45', '2023-07-27 13:37:45'),
(91, 98, 'https://youtube.com/embed/XJPDFlJ50pM', '', 'public/uploads/surhs/images/en/98- Surah Al-Bayyinah 1 to 5_Moment-1690465123.jpg', '', 1, 5, 7, '2023-07-27 13:38:43', '2023-07-27 13:38:43'),
(92, 98, 'https://youtube.com/embed/rlohTeyiot8', '', 'public/uploads/surhs/images/en/98- Surah Al-Bayyinah 6_Moment-1690465171.jpg', '', 6, 6, 8, '2023-07-27 13:39:31', '2023-07-27 13:39:31'),
(93, 98, 'https://youtube.com/embed/0AMDkD0siLM', '', 'public/uploads/surhs/images/en/98- Surah Al-Bayyinah 1 to 6_Moment-1690465218.jpg', '', 1, 6, 9, '2023-07-27 13:40:18', '2023-07-27 13:40:18'),
(94, 98, 'https://youtube.com/embed/ArenY50u_Lk', '', 'public/uploads/surhs/images/en/98- Surah Al-Bayyinah 7_Moment-1690465302.jpg', '', 7, 7, 10, '2023-07-27 13:41:42', '2023-07-27 13:41:42'),
(95, 98, 'https://youtube.com/embed/CJrkLTRKtow', '', 'public/uploads/surhs/images/en/98- Surah Al-Bayyinah 1 to 7_Moment-1690465349.jpg', '', 1, 7, 11, '2023-07-27 13:42:30', '2023-07-27 13:42:30'),
(96, 98, 'https://youtube.com/embed/zRE-FdVoDyM', '', 'public/uploads/surhs/images/en/98- Surah Al-Bayyinah 8_Moment-1690465424.jpg', '', 8, 8, 12, '2023-07-27 13:43:44', '2023-07-27 13:43:44'),
(97, 98, 'https://youtube.com/embed/fzCPZWCat30', '', 'public/uploads/surhs/images/en/98- Surah Al-Bayyinah 1 to 8_Moment-1690465469.jpg', '', 1, 8, 13, '2023-07-27 13:44:29', '2023-07-27 13:44:29'),
(98, 97, 'https://youtube.com/embed/M0mkG0DWw4A', '', 'public/uploads/surhs/images/en/97- Surah Al-Qadr 1-2_Moment-1692702652.jpg', '', 1, 2, 1, '2023-08-22 11:10:52', '2023-08-22 11:10:52'),
(99, 97, 'https://youtube.com/embed/EdmoRHo4S_A', '', 'public/uploads/surhs/images/en/97- Surah Al-Qadr 3_Moment-1692702691.jpg', '', 3, 3, 2, '2023-08-22 11:11:31', '2023-08-22 11:11:31'),
(100, 97, 'https://youtube.com/embed/IccmY7WgGbY', '', 'public/uploads/surhs/images/en/97- Surah Al-Qadr 4_Moment-1692702727.jpg', '', 4, 4, 3, '2023-08-22 11:12:07', '2023-08-22 11:12:07'),
(101, 97, 'https://youtube.com/embed/NMh0NxVCAmY', '', 'public/uploads/surhs/images/en/97- Surah Al-Qadr 5_Moment-1692702756.jpg', '', 5, 5, 4, '2023-08-22 11:12:36', '2023-08-22 11:12:36'),
(102, 97, 'https://youtube.com/embed/s3dBSIGmCms', '', 'public/uploads/surhs/images/en/97- Surah Al-Qadr 1 to 5_Moment-1692702804.jpg', '', 1, 5, 5, '2023-08-22 11:13:24', '2023-08-22 11:13:24'),
(103, 96, 'https://youtube.com/embed/o6ZtqCc8I3M', '', 'public/uploads/surhs/images/en/96- Surah Al-Alaq 1 to 2_Moment-1692702855.jpg', '', 1, 2, 1, '2023-08-22 11:14:15', '2023-08-22 11:14:15'),
(104, 96, 'https://youtube.com/embed/IBOT-utvy3w', '', 'public/uploads/surhs/images/en/96- Surah Al-Alaq 3 to 4_Moment-1692702879.jpg', '', 3, 4, 2, '2023-08-22 11:14:39', '2023-08-22 11:14:39'),
(105, 96, 'https://youtube.com/embed/NaomuQtz9AY', '', 'public/uploads/surhs/images/en/96- Surah Al-Alaq 5 to 6_Moment-1692702908.jpg', '', 5, 6, 3, '2023-08-22 11:15:08', '2023-08-22 11:15:08'),
(106, 96, 'https://youtube.com/embed/fX5s0u875dE', '', 'public/uploads/surhs/images/en/96- Surah Al-Alaq 7 to 8_Moment-1692702937.jpg', '', 7, 8, 4, '2023-08-22 11:15:37', '2023-08-22 11:15:37'),
(107, 96, 'https://youtube.com/embed/zMQUXQXYkck', '', 'public/uploads/surhs/images/en/96- Surah Al-Alaq 9 to 10_Moment-1692702965.jpg', '', 9, 10, 5, '2023-08-22 11:16:05', '2023-08-22 11:16:05'),
(108, 96, 'https://youtube.com/embed/b6f0d32YonQ', '', 'public/uploads/surhs/images/en/96- Surah Al-Alaq 1 to 6_Moment-1692703015.jpg', '', 1, 6, 3, '2023-08-22 11:17:29', '2023-08-22 11:17:29'),
(109, 96, 'https://youtube.com/embed/R22KqtKfAKs', '', 'public/uploads/surhs/images/en/96- Surah Al-Alaq 11 to 12_Moment-1692703102.jpg', '', 11, 12, 7, '2023-08-22 11:18:22', '2023-08-22 11:18:22'),
(110, 96, 'https://youtube.com/embed/RkJgJx7rQGo', '', 'public/uploads/surhs/images/en/96- Surah Al-Alaq 1 to 12_Moment-1692703130.jpg', '', 1, 12, 8, '2023-08-22 11:18:50', '2023-08-22 11:18:50'),
(111, 96, 'https://youtube.com/embed/usIan7FdpuI', '', 'public/uploads/surhs/images/en/96- Surah Al-Alaq 13 to 14_Moment-1692703317.jpg', '', 13, 14, 9, '2023-08-22 11:21:57', '2023-08-22 11:21:57'),
(112, 96, 'https://youtube.com/embed/jLOV_w3aSZU', '', 'public/uploads/surhs/images/en/96- Surah Al-Alaq 15 to 16_Moment-1692703400.jpg', '', 15, 16, 10, '2023-08-22 11:23:20', '2023-08-22 11:23:20'),
(113, 96, 'https://youtube.com/embed/GcDCTL6HCs8', '', 'public/uploads/surhs/images/en/96- Surah Al-Alaq 17 to 19_Moment-1692703483.jpg', '', 17, 19, 11, '2023-08-22 11:24:43', '2023-08-22 11:24:43'),
(114, 96, 'https://youtube.com/embed/k7J8HtL8Q5A', '', 'public/uploads/surhs/images/en/96- Surah Al-Alaq 13 to 19_Moment-1692703550.jpg', '', 13, 19, 12, '2023-08-22 11:25:50', '2023-08-22 11:25:50'),
(115, 96, 'https://youtube.com/embed/tWR3jdZj4YM', '', 'public/uploads/surhs/images/en/96- Surah Al-Alaq 1 to 19_Moment-1692703619.jpg', '', 1, 19, 13, '2023-08-22 11:26:59', '2023-08-22 11:26:59');

-- --------------------------------------------------------

--
-- Table structure for table `units`
--

CREATE TABLE `units` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `cat_id` int(10) UNSIGNED NOT NULL,
  `video` text DEFAULT NULL,
  `alies` varchar(255) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `units`
--

INSERT INTO `units` (`id`, `title`, `cat_id`, `video`, `alies`, `active`, `created_at`, `updated_at`) VALUES
(1, 'Unit 1: Taking Action', 3, 'https://cdn2.trunity.org/literature/common/literature_video_player/ngl_sts_boldactions.mp4', NULL, 0, NULL, '2023-04-14 01:48:32'),
(2, 'Unit1: Gadgets and Glitches', 4, 'https://cdn2.trunity.org/literature/common/literature_video_player/ngl_sts_gadgets.mp4', NULL, 0, NULL, NULL),
(3, 'Unit 1: Finding Common Ground', 5, '', NULL, 0, NULL, NULL),
(4, 'Unit 2:', 3, NULL, NULL, 1, '2023-04-09 23:42:13', '2023-04-14 01:48:56'),
(5, 'Unit3', 3, NULL, NULL, 1, '2023-04-09 23:42:13', '2023-04-09 23:42:13'),
(6, 'Unit4', 3, NULL, NULL, 1, '2023-04-09 23:42:13', '2023-04-09 23:42:13'),
(7, 'Unit5', 3, NULL, NULL, 1, '2023-04-09 23:42:13', '2023-04-09 23:42:13'),
(8, 'Unit6', 3, NULL, NULL, 1, '2023-04-09 23:42:13', '2023-04-09 23:42:13'),
(9, 'Unit2', 4, NULL, NULL, 1, '2023-04-09 23:42:38', '2023-04-09 23:42:38'),
(10, 'Unit3', 4, NULL, NULL, 1, '2023-04-09 23:42:38', '2023-04-09 23:42:38'),
(11, 'Unit4', 4, NULL, NULL, 1, '2023-04-09 23:42:38', '2023-04-09 23:42:38'),
(12, 'Unit5', 4, NULL, NULL, 1, '2023-04-09 23:42:38', '2023-04-09 23:42:38'),
(13, 'Unit6', 4, NULL, NULL, 1, '2023-04-09 23:42:38', '2023-04-09 23:42:38'),
(14, 'Unit2', 5, NULL, NULL, 1, '2023-04-09 23:42:58', '2023-04-09 23:42:58'),
(15, 'Unit3', 5, NULL, NULL, 1, '2023-04-09 23:42:58', '2023-04-09 23:42:58'),
(16, 'Unit4', 5, NULL, NULL, 1, '2023-04-09 23:42:58', '2023-04-09 23:42:58'),
(17, 'Unit5', 5, NULL, NULL, 1, '2023-04-09 23:42:58', '2023-04-09 23:42:58'),
(18, 'Unit6', 5, NULL, NULL, 1, '2023-04-09 23:42:58', '2023-04-09 23:42:58');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `pivot_id` int(11) DEFAULT NULL,
  `model_name` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `decr_password` varchar(255) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `pivot_id`, `model_name`, `email`, `email_verified_at`, `password`, `decr_password`, `remember_token`, `created_at`, `updated_at`) VALUES
(9, 'osama', 1, 'App\\Models\\User', 'osama.elazab2222@gmail.com', NULL, '$2y$10$h1mAye7w3jjC15hROGOTyeCcI07sNwxK24ILyjsr8mVXsx9ISCYCi', 'Osama_51448', NULL, '2022-08-21 13:09:36', '2022-08-21 13:09:36'),
(67, 'fathya_St056', 56, 'App\\Models\\Student', 'amiko00@hotmail.com', NULL, '$2y$10$W5KA9gtWmYHb0Z6KKgzGw.6MEqwZ5TnRoeMy8N.Hoow2zB9ATP0N6', 'Fathya_33735', NULL, '2023-07-19 22:46:46', '2023-07-19 22:46:46'),
(68, 'youssef_St057', 57, 'App\\Models\\Student', 'youssefsokhna12@gmail.com', NULL, '$2y$10$XC2qWDxV15eiM.yOqePkr.cJRMlrYOW0wZDEFhQQiF/SrNauK//CC', 'Youssef_44414', NULL, '2023-08-13 20:18:39', '2023-08-13 20:18:39'),
(69, 'gorgui_St058', 58, 'App\\Models\\Student', 'gorguisdiallo@gmail.com', NULL, '$2y$10$rrfUBvitDRofFa9uvCuIL.5JmZ0IG3dqW1w0.nGw.p/f.uyUCaHmC', 'Gorgui_67351', NULL, '2023-08-26 02:07:48', '2023-08-26 02:07:48'),
(70, 'ah_St059', 59, 'App\\Models\\Student', 'ah_St059@gmail.com', NULL, '$2y$10$pNv1Uz8sOWmGN5992gaWteoFq0eN9A6v8mVvpWBjPbPwraxJeWZBW', 'Ah_10014', NULL, '2024-03-11 18:49:38', '2024-03-11 18:49:38'),
(71, 'asmaa_St060', 60, 'App\\Models\\Student', 'asmaazakaria009@gmail.com', NULL, '$2y$10$./vCv56GtU.O03YrJ8KfG.9ldGv.uAXMDkv994DR5Y1vjOcdvNKHe', 'Asmaa_83981', NULL, '2024-03-11 20:23:50', '2024-03-11 20:23:50'),
(72, 'mostafa_St061', 61, 'App\\Models\\Student', 'admin@teacherusama.com', NULL, '$2y$10$iQVBJxzff0261nD5RqRnA.YgBm.DYRNxhrJHW1LTTUL9.Usqq0NPW', 'Mostafa_99588', NULL, '2024-03-11 23:25:38', '2024-03-11 23:25:38'),
(73, 'mostafa_St062', 62, 'App\\Models\\Student', 'osama.elaza232b22@gmail.com', NULL, '$2y$10$m5GJTG8m/YODirszGDz.5eNQ9i.9TbzA6o3Wa24v1PF8FbQ/LuwjO', 'Mostafa_9434', NULL, '2024-03-11 23:30:17', '2024-03-11 23:30:17'),
(74, 'nnn_St064', 64, 'App\\Models\\Student', 'aasmaa@gmail.com', NULL, '$2y$10$00LW7cXFJGipF/yYPUd36.yaTcDlB92eFMOkmUzI2cczsgTRlbVJS', 'Nnn_92041', NULL, '2024-03-12 11:46:25', '2024-03-12 11:46:25'),
(75, 'uyit_St065', 65, 'App\\Models\\Student', 'asmaase@gmail.com', NULL, '$2y$10$M24aaVFimKXlPZnJ2QaKn.jGupfQEVEK1dzqgnxeUwjlhTHfrOupa', 'Uyit_52243', NULL, '2024-03-12 11:48:48', '2024-03-12 11:48:48'),
(76, 'we_St066', 66, 'App\\Models\\Student', 'we_St066@gmail.com', NULL, '$2y$10$Anw/YmqDt5qZ2Me6yhbYGusJ4WFUvzY3z9y3wllYg.YNyPCnKFRkK', 'We_96405', NULL, '2024-06-02 15:06:26', '2024-06-02 15:06:26'),
(77, 'khadija_St067', 67, 'App\\Models\\Student', 'khadija_St067@gmail.com', NULL, '$2y$10$PD9PA.xbRrfPIVItY3IAee1aEKrWr03xNDd.0cL36Vx1Y7gbW2Z4S', 'Khadija_74255', NULL, '2024-07-11 11:14:47', '2024-07-11 11:14:47'),
(78, 'fatima_St068', 68, 'App\\Models\\Student', 'fatima_St068@gmail.com', NULL, '$2y$10$QlwkaCzAottub4YSf2SLEO0BJ0XkYhPpT.A0s5N7Uaff2M3rHtWJm', 'Fatima_65550', NULL, '2024-07-11 11:14:48', '2024-07-11 11:14:48'),
(79, 'danny_St069', 69, 'App\\Models\\Student', 'wigandill89@gmail.com', NULL, '$2y$10$FX2x6ZGl976Yc1D7xShxRuErhlyDu7LD3itG2tSgiUhZKsPzNQHWC', 'Danny_58815', NULL, '2025-01-24 20:10:49', '2025-01-24 20:10:49'),
(80, 'md_St070', 70, 'App\\Models\\Student', 'ehsanz.official@gmail.com', NULL, '$2y$10$yZy5q9qzrU43TkDYVaIow.qfJQ46D4EPk2.R7DiIfCWCA.4Mit7OW', 'Md_66420', NULL, '2025-04-30 08:30:46', '2025-04-30 08:30:46'),
(81, 'richard_St071', 71, 'App\\Models\\Student', 'wastecontrol@hotmail.com', NULL, '$2y$10$4JrmOHdmehehHcs7YcmVOe9.Zaf791L3pl1ZCwjHG4Z.LXdYjmQZm', 'Richard_26503', NULL, '2025-05-01 12:34:19', '2025-05-01 12:34:19'),
(82, 'luca_St072', 72, 'App\\Models\\Student', 'patricgary420@gmail.com', NULL, '$2y$10$5PCbR4BmLVkiIo5nwHpsd.zeO/zNZa.QEKKWxwvDRqewbwf1YLznC', 'Luca_69762', NULL, '2025-11-04 06:01:49', '2025-11-04 06:01:49'),
(83, 'sunny_St073', 73, 'App\\Models\\Student', 'sunniness0526@gmail.com', NULL, '$2y$10$TNm/tf0t3DX0ezpd9MNLO.bsb4dJIrm7XLAsVf.gfGa8KLvfzf3nO', 'Sunny_3620', NULL, '2026-01-21 17:19:17', '2026-01-21 17:19:17');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audio_lessons`
--
ALTER TABLE `audio_lessons`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `audio_units`
--
ALTER TABLE `audio_units`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contact_us`
--
ALTER TABLE `contact_us`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `courses_old`
--
ALTER TABLE `courses_old`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `course_translations`
--
ALTER TABLE `course_translations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `course_translations_old`
--
ALTER TABLE `course_translations_old`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `employees_course`
--
ALTER TABLE `employees_course`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `employees_old`
--
ALTER TABLE `employees_old`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `employee_quizzes_old`
--
ALTER TABLE `employee_quizzes_old`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `grades`
--
ALTER TABLE `grades`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lessons`
--
ALTER TABLE `lessons`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lessons_old`
--
ALTER TABLE `lessons_old`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lesson_descriptions`
--
ALTER TABLE `lesson_descriptions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lesson_types`
--
ALTER TABLE `lesson_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `levels`
--
ALTER TABLE `levels`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `listening_books`
--
ALTER TABLE `listening_books`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `listening_chapters`
--
ALTER TABLE `listening_chapters`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `organization`
--
ALTER TABLE `organization`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_unique` (`name`);

--
-- Indexes for table `permission_role`
--
ALTER TABLE `permission_role`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `permission_role_role_id_foreign` (`role_id`);

--
-- Indexes for table `permission_user`
--
ALTER TABLE `permission_user`
  ADD PRIMARY KEY (`user_id`,`permission_id`,`user_type`),
  ADD KEY `permission_user_permission_id_foreign` (`permission_id`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `quran_courses`
--
ALTER TABLE `quran_courses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `quran_course_translations`
--
ALTER TABLE `quran_course_translations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_unique` (`name`);

--
-- Indexes for table `role_user`
--
ALTER TABLE `role_user`
  ADD PRIMARY KEY (`user_id`,`role_id`,`user_type`),
  ADD KEY `role_user_role_id_foreign` (`role_id`);

--
-- Indexes for table `sections`
--
ALTER TABLE `sections`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `student_quizzes`
--
ALTER TABLE `student_quizzes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `surahs`
--
ALTER TABLE `surahs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `surahs_old`
--
ALTER TABLE `surahs_old`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `surh_videos`
--
ALTER TABLE `surh_videos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `units`
--
ALTER TABLE `units`
  ADD PRIMARY KEY (`id`);

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
-- AUTO_INCREMENT for table `audio_lessons`
--
ALTER TABLE `audio_lessons`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=540;

--
-- AUTO_INCREMENT for table `audio_units`
--
ALTER TABLE `audio_units`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `contact_us`
--
ALTER TABLE `contact_us`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `courses_old`
--
ALTER TABLE `courses_old`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `course_translations`
--
ALTER TABLE `course_translations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `course_translations_old`
--
ALTER TABLE `course_translations_old`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `employees_course`
--
ALTER TABLE `employees_course`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT for table `employees_old`
--
ALTER TABLE `employees_old`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grades`
--
ALTER TABLE `grades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `lessons`
--
ALTER TABLE `lessons`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=142;

--
-- AUTO_INCREMENT for table `lesson_descriptions`
--
ALTER TABLE `lesson_descriptions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=150;

--
-- AUTO_INCREMENT for table `lesson_types`
--
ALTER TABLE `lesson_types`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `levels`
--
ALTER TABLE `levels`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `listening_books`
--
ALTER TABLE `listening_books`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `listening_chapters`
--
ALTER TABLE `listening_chapters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `organization`
--
ALTER TABLE `organization`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quran_courses`
--
ALTER TABLE `quran_courses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `quran_course_translations`
--
ALTER TABLE `quran_course_translations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- AUTO_INCREMENT for table `student_quizzes`
--
ALTER TABLE `student_quizzes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=192;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `surahs`
--
ALTER TABLE `surahs`
  MODIFY `id` int(3) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=115;

--
-- AUTO_INCREMENT for table `surahs_old`
--
ALTER TABLE `surahs_old`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=115;

--
-- AUTO_INCREMENT for table `surh_videos`
--
ALTER TABLE `surh_videos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=116;

--
-- AUTO_INCREMENT for table `units`
--
ALTER TABLE `units`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=84;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `permission_role`
--
ALTER TABLE `permission_role`
  ADD CONSTRAINT `permission_role_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `permission_role_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `permission_user`
--
ALTER TABLE `permission_user`
  ADD CONSTRAINT `permission_user_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `role_user`
--
ALTER TABLE `role_user`
  ADD CONSTRAINT `role_user_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
