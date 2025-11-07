-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 10, 2025 at 01:09 PM
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
-- Database: `time_tracking_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `name`, `description`, `created_at`) VALUES
(1, 'Human Resources', 'Handles recruitment, employee relations, and benefits', '2025-08-22 00:11:12'),
(2, 'Information Technology', 'Manages technology infrastructure and support', '2025-08-22 00:11:12'),
(3, 'Marketing', 'Responsible for brand management and promotions', '2025-08-22 00:11:12'),
(4, 'Finance', 'Handles accounting, budgeting, and financial reporting', '2025-08-22 00:11:12'),
(5, 'Operations', 'Manages daily business activities and processes', '2025-08-22 00:11:12');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `department` varchar(50) DEFAULT NULL,
  `position` varchar(50) DEFAULT NULL,
  `hire_date` date DEFAULT NULL,
  `status` enum('active','inactive','on_leave') DEFAULT 'active',
  `password_hash` varchar(255) NOT NULL,
  `role` enum('employee','admin') DEFAULT 'employee',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `full_name`, `username`, `email`, `phone`, `department`, `position`, `hire_date`, `status`, `password_hash`, `role`, `created_at`, `updated_at`) VALUES
(1, 'John Smith', 'admin', 'admin@company.com', '(555) 123-4567', 'Information Technology', 'System Administrator', '2020-01-15', 'active', '$2y$10$Uf6Tg.yWgN5QZIz6E7bSBep26nkAS/CK59fn5i8cOuPtfJKsS3zGa', 'admin', '2025-08-22 05:41:12', '2025-08-24 08:14:31'),
(2, 'seema rekha', 'seema', 'sarah.j@company.com', '9696969686', 'Human Resources', 'Marketing Manager', '2020-01-15', 'active', '$2y$10$gvzMqng9Vh0qer2rM7.FCOk/uC8Qe7hbrZGX/Lgilufrtmne7eAKa', 'employee', '2025-08-22 05:41:12', '2025-09-05 13:18:55'),
(3, 'Michael Williams', 'manav', 'michael.w@company.com', '6585365478', 'Human Resources', 'Software Developer', '2020-03-22', 'active', '$2y$10$Uf6Tg.yWgN5QZIz6E7bSBep26nkAS/CK59fn5i8cOuPtfJKsS3zGa', 'employee', '2025-08-22 05:41:12', '2025-09-09 06:42:42'),
(4, 'Abhishek Sharma', 'abhishek', 'emily.b@company.com', '8585858585', 'Information Technology', '.Net Developer', '2020-05-10', 'active', '$2y$10$Uf6Tg.yWgN5QZIz6E7bSBep26nkAS/CK59fn5i8cOuPtfJKsS3zGa', 'employee', '2025-08-22 05:41:12', '2025-08-23 11:06:54'),
(5, 'David Jones', 'david', 'david.j@company.com', '(555) 456-7890', 'Finance', 'Financial Analyst', '2020-07-18', 'active', '$2y$10$Uf6Tg.yWgN5QZIz6E7bSBep26nkAS/CK59fn5i8cOuPtfJKsS3zGa', 'employee', '2025-08-22 05:41:12', '2025-08-23 07:52:43'),
(6, 'Jennifer Miller', 'rocky', 'jennifer.m@company.com', '(555) 567-8901', 'Operations', 'Operations Manager', '2020-09-05', 'active', '$2y$10$Uf6Tg.yWgN5QZIz6E7bSBep26nkAS/CK59fn5i8cOuPtfJKsS3zGa', 'employee', '2025-08-22 05:41:12', '2025-08-23 07:52:46'),
(9, 'lalit', 'pant', 'lalitpant@pant.com', '8978455632', 'Marketing', 'Business Executive', '2025-08-29', 'active', '$2y$10$FUBhLEttrxF01zkHIXNqvuWxf5v/LCWVlAHUW9Sqfzid01/QRMhf.', 'employee', '2025-08-29 05:38:42', '2025-08-29 05:38:42'),
(24, 'prashad', 'prashad', 'prashad@gmail.com', '9565645875', 'Information Technology', 'developer', '2025-09-04', 'active', '$2y$10$4EimVcQo35jlNfKGlkCuWeniO33RNsVZFgtF9IDAnAgvPl0t0oHdG', 'employee', '2025-09-04 07:20:19', '2025-09-04 07:20:19'),
(25, 'hardev', 'hardev', 'hardev@gmail.com', '9875641258', 'Information Technology', 'developer', '2025-09-04', 'active', '$2y$10$G1ZXj/1HZknkICJUgmuds.qQ3S7VN6l3SexaHdKog62Mp3rFQlwJa', 'employee', '2025-09-04 07:22:37', '2025-09-04 07:22:37');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `description`, `created_at`, `updated_at`) VALUES
(2, 'work_start_time', '10:30', 'Default work start time', '2025-08-22 05:41:12', '2025-09-07 17:17:22'),
(3, 'work_end_time', '19:30', 'Default work end time', '2025-08-22 05:41:12', '2025-09-06 09:55:18'),
(4, 'lunch_duration', '60', 'Default lunch duration in minutes', '2025-08-22 05:41:12', '2025-09-06 10:35:02'),
(5, 'late_threshold', '15', 'Minutes after start time considered late', '2025-08-22 05:41:12', '2025-09-06 10:40:14'),
(7, 'casual_leave', '10', 'Per Year Casual Leave', '2025-09-04 06:26:34', '2025-09-04 06:26:34'),
(8, 'sick_leave', '10', 'Per year sick leave', '2025-09-04 06:26:34', '2025-09-06 10:35:48'),
(9, 'half_day_time', '04:30', 'Working Time for Half day', '2025-09-06 12:24:10', '2025-09-07 17:22:07');

-- --------------------------------------------------------

--
-- Table structure for table `time_entries`
--

CREATE TABLE `time_entries` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `entry_type` set('punch_in','punch_out','lunch_start','lunch_end','half_day','holiday','sick_leave','casual_leave','regularization') DEFAULT NULL,
  `entry_time` datetime NOT NULL DEFAULT current_timestamp(),
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `time_entries`
--

INSERT INTO `time_entries` (`id`, `employee_id`, `entry_type`, `entry_time`, `notes`) VALUES
(32, 2, 'punch_in', '2025-08-22 17:27:07', 'punch_in'),
(33, 2, 'lunch_start', '2025-08-22 17:29:39', 'lunch_start'),
(34, 2, 'lunch_start', '2025-08-22 17:29:52', 'lunch_start'),
(35, 2, 'punch_out', '2025-08-22 17:29:55', 'punch_out'),
(36, 2, 'punch_out', '2025-08-22 17:29:58', 'punch_out'),
(37, 2, 'lunch_start', '2025-08-22 17:30:01', 'lunch_start'),
(38, 2, 'lunch_start', '2025-08-22 17:30:03', 'lunch_start'),
(39, 2, 'lunch_start', '2025-08-22 17:30:05', 'lunch_start'),
(40, 2, 'lunch_start', '2025-08-22 17:30:07', 'lunch_start'),
(41, 2, 'lunch_end', '2025-08-22 17:34:58', 'lunch_end'),
(42, 2, 'lunch_start', '2025-08-22 17:35:08', 'lunch_start'),
(43, 2, 'lunch_end', '2025-08-22 17:35:17', 'lunch_end'),
(44, 2, 'lunch_start', '2025-08-22 17:36:20', 'lunch_start'),
(45, 2, 'lunch_end', '2025-08-22 17:36:24', 'lunch_end'),
(46, 2, 'lunch_start', '2025-08-22 17:36:27', 'lunch_start'),
(47, 2, 'lunch_end', '2025-08-22 17:36:32', 'lunch_end'),
(48, 2, 'lunch_start', '2025-08-22 17:37:30', 'lunch_start'),
(49, 2, 'lunch_end', '2025-08-22 17:37:33', 'lunch_end'),
(50, 2, 'punch_out', '2025-08-22 17:41:12', 'punch_out'),
(51, 2, 'punch_in', '2025-08-22 17:41:16', 'punch_in'),
(52, 2, 'lunch_start', '2025-08-22 17:42:03', 'lunch_start'),
(53, 2, 'lunch_end', '2025-08-22 17:42:05', 'lunch_end'),
(54, 2, 'lunch_start', '2025-08-22 17:46:13', 'lunch_start'),
(55, 2, 'lunch_end', '2025-08-22 17:56:29', 'lunch_end'),
(56, 2, 'punch_out', '2025-08-22 19:39:55', 'punch_out'),
(57, 2, 'punch_in', '2025-08-23 10:40:36', 'punch_in'),
(58, 3, 'punch_in', '2025-08-23 13:23:05', 'punch_in'),
(59, 3, 'lunch_start', '2025-08-23 13:44:01', 'lunch_start'),
(60, 2, 'lunch_start', '2025-08-23 13:45:06', 'lunch_start'),
(61, 2, 'lunch_end', '2025-08-23 14:25:08', 'lunch_end'),
(62, 2, 'punch_out', '2025-08-23 16:02:12', 'punch_out'),
(63, 2, 'punch_in', '2025-08-23 16:02:25', 'punch_in'),
(64, 2, 'punch_out', '2025-08-23 16:34:44', 'punch_out'),
(65, 2, 'punch_in', '2025-08-23 16:34:49', 'punch_in'),
(66, 2, 'punch_out', '2025-08-23 18:47:50', 'punch_out'),
(67, 2, 'punch_in', '2025-08-23 18:47:55', 'punch_in'),
(68, 2, 'punch_out', '2025-08-23 21:17:24', 'punch_out'),
(69, 2, 'punch_in', '2025-08-24 12:53:20', 'punch_in'),
(70, 2, 'lunch_start', '2025-08-24 16:50:47', 'lunch_start'),
(71, 2, 'lunch_end', '2025-08-24 16:50:50', 'lunch_end'),
(72, 1, 'lunch_start', '2025-08-24 16:51:29', 'lunch_start'),
(73, 1, 'punch_in', '2025-08-24 16:51:45', 'punch_in'),
(74, 2, 'lunch_start', '2025-08-24 16:52:10', 'lunch_start'),
(75, 2, 'lunch_end', '2025-08-24 16:52:12', 'lunch_end'),
(76, 1, 'half_day', '2025-08-25 11:12:09', 'punch_in'),
(77, 1, 'punch_in', '2025-08-25 11:12:23', 'punch_in'),
(78, 1, 'punch_out', '2025-08-25 11:15:38', 'punch_out'),
(79, 1, 'punch_in', '2025-08-25 11:15:56', 'punch_in'),
(80, 1, 'punch_out', '2025-08-25 12:00:58', 'punch_out'),
(81, 1, 'punch_in', '2025-08-25 11:31:05', 'punch_in'),
(82, 1, 'punch_in', '2025-08-25 11:31:15', 'punch_in'),
(83, 1, 'punch_in', '2025-08-25 12:31:40', 'punch_in'),
(84, 1, 'lunch_start', '2025-08-25 13:23:03', 'lunch_start'),
(85, 1, 'lunch_end', '2025-08-25 13:23:12', 'lunch_end'),
(86, 2, '', '2025-08-25 14:59:50', 'lunch_start'),
(87, 2, 'punch_in', '2025-08-25 14:00:04', 'punch_in'),
(89, 2, 'lunch_end', '2025-08-25 15:09:01', 'lunch_end'),
(90, 1, 'lunch_start', '2025-08-25 15:10:48', 'lunch_start'),
(91, 1, 'lunch_end', '2025-08-25 15:10:50', 'lunch_end'),
(92, 1, 'punch_out', '2025-08-25 15:49:19', 'punch_out'),
(93, 1, 'punch_in', '2025-08-25 15:49:21', 'punch_in'),
(94, 1, 'punch_out', '2025-08-25 15:49:41', 'punch_out'),
(95, 1, 'punch_in', '2025-08-25 17:22:07', 'punch_in'),
(96, 1, 'punch_in', '2025-08-26 10:40:02', 'punch_in'),
(97, 1, 'lunch_start', '2025-08-26 11:44:19', 'lunch_start'),
(98, 1, 'lunch_end', '2025-08-26 11:44:22', 'lunch_end'),
(99, 1, 'lunch_start', '2025-08-26 11:44:41', 'lunch_start'),
(100, 1, 'lunch_end', '2025-08-26 11:45:05', 'lunch_end'),
(101, 1, 'lunch_start', '2025-08-26 11:47:16', 'lunch_start'),
(102, 1, 'lunch_end', '2025-08-26 11:47:24', 'lunch_end'),
(103, 1, 'lunch_start', '2025-08-26 11:59:22', 'lunch_start'),
(104, 1, 'lunch_end', '2025-08-26 12:00:25', 'lunch_end'),
(105, 1, 'lunch_start', '2025-08-26 12:02:40', 'lunch_start'),
(106, 1, 'lunch_end', '2025-08-26 12:07:42', 'lunch_end'),
(107, 2, 'punch_in', '2025-08-26 12:42:36', 'punch_in'),
(108, 1, 'lunch_start', '2025-08-26 13:13:05', 'lunch_start'),
(109, 1, 'lunch_end', '2025-08-26 13:56:06', 'lunch_end'),
(110, 3, 'punch_in', '2025-08-26 16:03:39', 'punch_in'),
(111, 2, 'lunch_start', '2025-08-26 17:16:20', 'lunch_start'),
(112, 2, 'lunch_end', '2025-08-26 17:41:13', 'lunch_end'),
(113, 1, 'lunch_start', '2025-08-26 17:47:55', 'lunch_start'),
(114, 1, 'lunch_end', '2025-08-26 17:48:00', 'lunch_end'),
(115, 4, 'punch_in', '2025-08-26 17:53:02', 'punch_in'),
(117, 1, 'punch_in', '2025-08-27 10:50:23', 'punch_in'),
(118, 1, 'punch_out', '2025-08-27 10:59:02', 'punch_out'),
(119, 1, 'punch_in', '2025-08-27 10:59:06', 'punch_in'),
(120, 1, 'punch_out', '2025-08-27 11:01:27', 'punch_out'),
(121, 2, 'punch_in', '2025-08-27 11:07:08', 'punch_in'),
(122, 2, 'lunch_start', '2025-08-27 15:24:59', 'lunch_start'),
(123, 2, 'lunch_end', '2025-08-27 15:25:13', 'lunch_end'),
(124, 2, 'lunch_start', '2025-08-27 15:26:19', 'lunch_start'),
(125, 2, 'lunch_end', '2025-08-27 15:26:25', 'lunch_end'),
(126, 1, 'punch_in', '2025-08-27 15:34:52', 'punch_in'),
(127, 2, 'lunch_start', '2025-08-27 21:45:07', 'lunch_start'),
(128, 2, 'lunch_end', '2025-08-27 21:45:09', 'lunch_end'),
(129, 2, 'lunch_start', '2025-08-27 22:04:04', 'lunch_start'),
(130, 2, 'lunch_end', '2025-08-27 22:04:07', 'lunch_end'),
(131, 2, 'lunch_start', '2025-08-27 22:04:12', 'lunch_start'),
(132, 2, 'lunch_end', '2025-08-27 22:04:14', 'lunch_end'),
(133, 2, 'lunch_start', '2025-08-27 22:04:22', 'lunch_start'),
(134, 2, 'lunch_end', '2025-08-27 22:04:24', 'lunch_end'),
(135, 2, 'lunch_start', '2025-08-27 23:24:46', 'lunch_start'),
(136, 2, 'lunch_end', '2025-08-27 23:24:49', 'lunch_end'),
(137, 2, 'punch_out', '2025-08-27 23:31:01', 'punch_out'),
(138, 2, 'punch_in', '2025-08-27 23:31:04', 'punch_in'),
(139, 2, 'punch_out', '2025-08-27 23:31:15', 'punch_out'),
(140, 3, 'punch_in', '2025-08-27 23:36:21', 'punch_in'),
(141, 3, 'punch_out', '2025-08-27 23:39:50', 'punch_out'),
(142, 3, 'punch_in', '2025-08-27 23:39:53', 'punch_in'),
(143, 3, 'lunch_start', '2025-08-27 23:39:56', 'lunch_start'),
(144, 3, 'lunch_end', '2025-08-27 23:40:00', 'lunch_end'),
(145, 3, 'lunch_start', '2025-08-27 23:41:10', 'lunch_start'),
(146, 3, 'lunch_end', '2025-08-27 23:41:15', 'lunch_end'),
(147, 2, 'punch_in', '2025-08-28 10:41:13', 'punch_in'),
(148, 3, 'punch_in', '2025-08-28 10:41:28', 'punch_in'),
(149, 4, 'punch_in', '2025-08-28 10:41:42', 'punch_in'),
(150, 4, 'lunch_start', '2025-08-28 10:57:47', 'lunch_start'),
(151, 4, 'lunch_end', '2025-08-28 10:57:49', 'lunch_end'),
(152, 5, 'punch_in', '2025-08-28 11:15:33', 'punch_in'),
(153, 2, 'lunch_start', '2025-08-28 11:39:50', 'lunch_start'),
(154, 2, 'lunch_end', '2025-08-28 11:39:54', 'lunch_end'),
(155, 2, 'lunch_start', '2025-08-28 15:25:09', 'lunch_start'),
(156, 2, 'lunch_end', '2025-08-28 15:25:11', 'lunch_end'),
(157, 2, 'lunch_start', '2025-08-28 17:55:17', 'lunch_start'),
(158, 2, 'lunch_end', '2025-08-28 17:55:19', 'lunch_end'),
(159, 2, 'lunch_start', '2025-08-28 18:57:42', 'lunch_start'),
(160, 2, 'lunch_end', '2025-08-28 18:57:45', 'lunch_end'),
(161, 2, 'punch_out', '2025-08-28 19:22:12', 'punch_out'),
(162, 2, 'punch_in', '2025-08-28 19:22:17', 'punch_in'),
(163, 2, 'punch_out', '2025-08-28 19:22:24', 'punch_out'),
(164, 2, 'punch_in', '2025-08-28 19:22:27', 'punch_in'),
(165, 2, 'lunch_start', '2025-08-28 19:23:58', 'lunch_start'),
(166, 2, 'lunch_end', '2025-08-28 19:26:17', 'lunch_end'),
(167, 2, 'punch_in', '2025-08-29 10:44:47', 'punch_in'),
(168, 3, 'punch_in', '2025-08-29 10:45:08', 'punch_in'),
(169, 4, 'punch_in', '2025-08-29 10:45:27', 'punch_in'),
(170, 5, 'punch_in', '2025-08-29 10:46:29', 'punch_in'),
(171, 6, 'punch_in', '2025-08-29 11:04:16', 'punch_in'),
(172, 6, 'lunch_start', '2025-08-29 11:04:19', 'lunch_start'),
(173, 6, 'lunch_end', '2025-08-29 11:04:21', 'lunch_end'),
(174, 6, 'punch_out', '2025-08-29 11:04:23', 'punch_out'),
(175, 6, 'punch_in', '2025-08-29 11:04:52', 'punch_in'),
(176, 6, 'lunch_start', '2025-08-29 11:04:54', 'lunch_start'),
(177, 6, 'lunch_end', '2025-08-29 11:04:56', 'lunch_end'),
(178, 6, 'punch_out', '2025-08-29 11:04:57', 'punch_out'),
(179, 6, 'punch_in', '2025-08-29 11:06:38', 'punch_in'),
(180, 6, 'lunch_start', '2025-08-29 11:06:40', 'lunch_start'),
(181, 6, 'lunch_end', '2025-08-29 11:06:41', 'lunch_end'),
(182, 6, 'punch_out', '2025-08-29 11:06:44', 'punch_out'),
(183, 9, 'punch_in', '2025-08-29 11:09:13', 'punch_in'),
(184, 2, 'lunch_start', '2025-08-29 12:33:10', 'lunch_start'),
(185, 2, 'lunch_end', '2025-08-29 12:37:24', 'lunch_end'),
(186, 2, 'lunch_start', '2025-08-29 12:53:47', 'lunch_start'),
(187, 2, 'lunch_end', '2025-08-29 12:53:49', 'lunch_end'),
(188, 2, 'lunch_start', '2025-08-29 12:54:12', 'lunch_start'),
(189, 2, 'lunch_end', '2025-08-29 12:54:14', 'lunch_end'),
(190, 2, 'lunch_start', '2025-08-29 12:54:32', 'lunch_start'),
(191, 2, 'lunch_end', '2025-08-29 12:54:34', 'lunch_end'),
(192, 2, 'lunch_start', '2025-08-29 12:57:07', 'lunch_start'),
(193, 2, 'lunch_end', '2025-08-29 12:58:39', 'lunch_end'),
(194, 2, 'lunch_start', '2025-08-29 12:59:02', 'lunch_start'),
(195, 2, 'lunch_end', '2025-08-29 13:09:14', 'lunch_end'),
(196, 3, 'lunch_start', '2025-08-29 13:14:26', 'lunch_start'),
(197, 3, 'lunch_end', '2025-08-29 13:16:06', 'lunch_end'),
(198, 3, 'lunch_start', '2025-08-29 13:16:50', 'lunch_start'),
(199, 3, 'lunch_end', '2025-08-29 13:19:29', 'lunch_end'),
(200, 3, 'lunch_start', '2025-08-29 13:19:42', 'lunch_start'),
(201, 3, 'lunch_end', '2025-08-29 13:20:26', 'lunch_end'),
(202, 3, 'lunch_start', '2025-08-29 15:09:48', 'lunch_start'),
(203, 3, 'lunch_end', '2025-08-29 15:09:52', 'lunch_end'),
(204, 5, 'lunch_start', '2025-08-29 15:41:33', 'lunch_start'),
(205, 5, 'lunch_end', '2025-08-29 15:41:59', 'lunch_end'),
(206, 2, 'lunch_start', '2025-08-29 18:41:46', 'lunch_start'),
(207, 2, 'lunch_end', '2025-08-29 18:41:51', 'lunch_end'),
(208, 2, 'punch_in', '2025-08-30 20:38:25', 'punch_in'),
(209, 2, 'lunch_start', '2025-08-30 21:08:12', 'lunch_start'),
(210, 2, 'lunch_end', '2025-08-30 21:08:19', 'lunch_end'),
(211, 3, 'punch_in', '2025-08-31 14:16:30', 'punch_in'),
(212, 2, 'punch_in', '2025-08-31 15:07:55', 'punch_in'),
(213, 3, 'punch_out', '2025-08-31 20:16:30', 'punch_out'),
(214, 2, 'punch_out', '2025-08-31 23:14:36', 'punch_out'),
(215, 2, 'punch_in', '2025-09-01 10:38:42', 'punch_in'),
(216, 3, 'punch_in', '2025-09-01 11:09:14', 'punch_in'),
(217, 3, 'lunch_start', '2025-09-01 11:35:20', 'lunch_start'),
(218, 3, 'lunch_end', '2025-09-01 11:35:21', 'lunch_end'),
(219, 3, 'lunch_start', '2025-09-01 15:28:54', 'lunch_start'),
(220, 3, 'lunch_end', '2025-09-01 15:28:56', 'lunch_end'),
(221, 3, 'lunch_start', '2025-09-01 18:51:38', 'lunch_start'),
(222, 3, 'lunch_end', '2025-09-01 18:51:40', 'lunch_end'),
(223, 5, 'punch_in', '2025-09-01 18:52:02', 'punch_in'),
(224, 5, 'lunch_start', '2025-09-01 18:52:07', 'lunch_start'),
(225, 5, 'lunch_end', '2025-09-01 18:52:09', 'lunch_end'),
(226, 3, 'punch_in', '2025-09-02 11:56:11', 'punch_in'),
(227, 3, 'lunch_start', '2025-09-02 19:25:17', 'lunch_start'),
(228, 3, 'lunch_end', '2025-09-02 19:25:20', 'lunch_end'),
(229, 2, 'punch_in', '2025-09-03 10:51:59', 'punch_in'),
(230, 3, 'punch_in', '2025-09-03 10:52:19', 'punch_in'),
(231, 5, 'punch_in', '2025-09-03 10:52:44', 'punch_in'),
(232, 4, 'punch_in', '2025-09-03 10:52:59', 'punch_in'),
(233, 2, 'sick_leave', '2025-09-04 11:24:52', 'leave'),
(234, 5, 'lunch_start', '2025-09-03 15:10:59', 'lunch_start'),
(235, 6, 'punch_in', '2025-09-03 15:59:38', 'punch_in'),
(236, 6, 'lunch_start', '2025-09-03 16:19:00', 'lunch_start'),
(237, 6, 'lunch_end', '2025-09-03 16:19:02', 'lunch_end'),
(238, 5, 'lunch_end', '2025-09-03 16:23:08', 'lunch_end'),
(332, 3, 'sick_leave', '2025-08-09 00:00:00', 'sick_leave'),
(333, 3, 'sick_leave', '2025-08-10 00:00:00', 'sick_leave'),
(334, 3, 'sick_leave', '2025-08-11 00:00:00', 'sick_leave'),
(335, 3, 'sick_leave', '2025-08-12 00:00:00', 'sick_leave'),
(336, 3, 'sick_leave', '2025-08-13 00:00:00', 'sick_leave'),
(337, 3, 'sick_leave', '2025-08-14 00:00:00', 'sick_leave'),
(338, 3, 'sick_leave', '2025-08-15 00:00:00', 'sick_leave'),
(339, 3, 'sick_leave', '2025-08-16 00:00:00', 'sick_leave'),
(340, 3, 'sick_leave', '2025-08-17 00:00:00', 'sick_leave'),
(341, 3, 'sick_leave', '2025-08-18 00:00:00', 'sick_leave'),
(342, 3, 'sick_leave', '2025-08-19 00:00:00', 'sick_leave'),
(343, 5, 'punch_out', '2025-09-03 19:30:03', 'punch_out'),
(344, 5, 'punch_in', '2025-09-03 19:30:50', 'punch_in'),
(345, 5, 'lunch_start', '2025-09-03 19:30:53', 'lunch_start'),
(346, 5, 'lunch_end', '2025-09-03 19:30:55', 'lunch_end'),
(347, 5, 'punch_out', '2025-09-03 19:30:57', 'punch_out'),
(348, 2, 'punch_in', '2025-09-04 11:03:50', 'punch_in'),
(349, 2, 'regularization', '2025-09-04 07:44:31', 'regularization'),
(350, 2, 'punch_in', '2025-09-04 11:16:32', 'punch_in'),
(351, 2, 'punch_in', '2025-09-04 11:16:41', 'punch_in'),
(352, 2, 'punch_in', '2025-09-04 11:16:44', 'punch_in'),
(353, 2, 'punch_in', '2025-09-04 11:16:53', 'punch_in'),
(354, 2, 'punch_in', '2025-09-04 11:16:55', 'punch_in'),
(355, 2, 'punch_in', '2025-09-04 11:17:01', 'punch_in'),
(356, 2, 'punch_in', '2025-09-04 11:27:37', 'punch_in'),
(357, 24, 'regularization', '2025-09-04 00:00:00', 'regularization'),
(358, 24, 'sick_leave', '2025-09-05 00:00:00', 'sick_leave'),
(359, 24, 'sick_leave', '2025-09-06 00:00:00', 'sick_leave'),
(360, 24, 'sick_leave', '2025-09-07 00:00:00', 'sick_leave'),
(361, 3, 'punch_in', '2025-09-05 12:56:56', 'punch_in'),
(362, 3, 'lunch_start', '2025-09-05 13:03:58', 'lunch_start'),
(363, 3, 'lunch_end', '2025-09-05 13:04:01', 'lunch_end'),
(364, 3, 'punch_out', '2025-09-05 13:04:04', 'punch_out'),
(365, 3, 'punch_in', '2025-09-05 13:04:06', 'punch_in'),
(366, 2, 'punch_in', '2025-09-05 13:14:18', 'punch_in'),
(367, 5, 'punch_in', '2025-09-05 15:19:18', 'punch_in'),
(368, 2, 'lunch_start', '2025-09-05 15:47:24', 'lunch_start'),
(369, 2, 'lunch_end', '2025-09-05 15:47:26', 'lunch_end'),
(379, 4, 'punch_in', '2025-09-05 17:39:45', 'punch_in'),
(416, 2, 'punch_in', '2025-09-06 11:42:39', 'punch_in'),
(417, 3, 'punch_in', '2025-09-06 16:39:33', 'punch_in'),
(418, 3, 'half_day', '2025-09-06 00:00:00', 'half_day'),
(419, 3, 'punch_out', '2025-09-06 17:41:43', 'punch_out'),
(420, 4, 'half_day', '2025-09-06 00:00:00', 'half_day'),
(421, 4, 'half_day', '2025-09-06 00:00:00', 'half_day'),
(422, 4, 'half_day', '2025-09-06 00:00:00', 'half_day'),
(423, 4, 'half_day', '2025-09-06 00:00:00', 'half_day'),
(424, 4, 'half_day', '2025-09-06 00:00:00', 'half_day'),
(425, 4, 'half_day', '2025-09-06 00:00:00', 'half_day'),
(426, 4, 'half_day', '2025-09-06 00:00:00', 'half_day'),
(427, 4, 'half_day', '2025-09-06 00:00:00', 'half_day'),
(428, 4, 'half_day', '2025-09-06 00:00:00', 'half_day'),
(435, 3, 'punch_in', '2025-09-07 17:00:31', 'punch_in'),
(438, 2, 'punch_in', '2025-09-07 20:29:10', 'punch_in'),
(439, 2, 'lunch_start', '2025-09-07 20:29:12', 'lunch_start'),
(441, 3, 'punch_out', '2025-09-07 21:37:44', 'punch_out'),
(442, 2, 'lunch_end', '2025-09-07 21:45:57', 'lunch_end'),
(443, 2, 'punch_out', '2025-09-07 21:46:13', 'punch_out'),
(444, 2, 'punch_in', '2025-09-07 15:00:15', 'punch_in'),
(447, 3, 'punch_in', '2025-09-08 15:00:53', 'punch_in'),
(448, 3, 'punch_out', '2025-09-08 19:49:58', 'punch_out'),
(449, 3, 'sick_leave', '2025-09-09 00:00:00', 'sick_leave'),
(450, 3, 'sick_leave', '2025-09-10 00:00:00', 'sick_leave'),
(451, 3, 'casual_leave', '2025-09-11 00:00:00', 'casual_leave'),
(479, 1, 'holiday', '2025-09-19 00:00:00', 'Holiday: sdfdss - sdfsddsffs'),
(480, 2, 'holiday', '2025-09-19 00:00:00', 'Holiday: sdfdss - sdfsddsffs'),
(481, 3, 'holiday', '2025-09-19 00:00:00', 'Holiday: sdfdss - sdfsddsffs'),
(482, 4, 'holiday', '2025-09-19 00:00:00', 'Holiday: sdfdss - sdfsddsffs'),
(483, 5, 'holiday', '2025-09-19 00:00:00', 'Holiday: sdfdss - sdfsddsffs'),
(484, 6, 'holiday', '2025-09-19 00:00:00', 'Holiday: sdfdss - sdfsddsffs'),
(485, 9, 'holiday', '2025-09-19 00:00:00', 'Holiday: sdfdss - sdfsddsffs'),
(486, 24, 'holiday', '2025-09-19 00:00:00', 'Holiday: sdfdss - sdfsddsffs'),
(487, 25, 'holiday', '2025-09-19 00:00:00', 'Holiday: sdfdss - sdfsddsffs'),
(623, 1, 'holiday', '2025-09-22 00:00:00', 'Holiday: sdfdsf - fdsfdsdfsf'),
(624, 2, 'holiday', '2025-09-22 00:00:00', 'Holiday: sdfdsf - fdsfdsdfsf'),
(625, 3, 'holiday', '2025-09-22 00:00:00', 'Holiday: sdfdsf - fdsfdsdfsf'),
(626, 4, 'holiday', '2025-09-22 00:00:00', 'Holiday: sdfdsf - fdsfdsdfsf'),
(627, 5, 'holiday', '2025-09-22 00:00:00', 'Holiday: sdfdsf - fdsfdsdfsf'),
(628, 6, 'holiday', '2025-09-22 00:00:00', 'Holiday: sdfdsf - fdsfdsdfsf'),
(629, 9, 'holiday', '2025-09-22 00:00:00', 'Holiday: sdfdsf - fdsfdsdfsf'),
(630, 24, 'holiday', '2025-09-22 00:00:00', 'Holiday: sdfdsf - fdsfdsdfsf'),
(631, 25, 'holiday', '2025-09-22 00:00:00', 'Holiday: sdfdsf - fdsfdsdfsf'),
(632, 1, 'holiday', '2025-09-23 00:00:00', 'Holiday: sdfdsf - fdsfdsdfsf'),
(633, 2, 'holiday', '2025-09-23 00:00:00', 'Holiday: sdfdsf - fdsfdsdfsf'),
(634, 3, 'holiday', '2025-09-23 00:00:00', 'Holiday: sdfdsf - fdsfdsdfsf'),
(635, 4, 'holiday', '2025-09-23 00:00:00', 'Holiday: sdfdsf - fdsfdsdfsf'),
(636, 5, 'holiday', '2025-09-23 00:00:00', 'Holiday: sdfdsf - fdsfdsdfsf'),
(637, 6, 'holiday', '2025-09-23 00:00:00', 'Holiday: sdfdsf - fdsfdsdfsf'),
(638, 9, 'holiday', '2025-09-23 00:00:00', 'Holiday: sdfdsf - fdsfdsdfsf'),
(639, 24, 'holiday', '2025-09-23 00:00:00', 'Holiday: sdfdsf - fdsfdsdfsf'),
(640, 25, 'holiday', '2025-09-23 00:00:00', 'Holiday: sdfdsf - fdsfdsdfsf'),
(641, 1, 'holiday', '2025-09-24 00:00:00', 'Holiday: sdfdsf - fdsfdsdfsf'),
(642, 2, 'holiday', '2025-09-24 00:00:00', 'Holiday: sdfdsf - fdsfdsdfsf'),
(643, 3, 'holiday', '2025-09-24 00:00:00', 'Holiday: sdfdsf - fdsfdsdfsf'),
(644, 4, 'holiday', '2025-09-24 00:00:00', 'Holiday: sdfdsf - fdsfdsdfsf'),
(645, 5, 'holiday', '2025-09-24 00:00:00', 'Holiday: sdfdsf - fdsfdsdfsf'),
(646, 6, 'holiday', '2025-09-24 00:00:00', 'Holiday: sdfdsf - fdsfdsdfsf'),
(647, 9, 'holiday', '2025-09-24 00:00:00', 'Holiday: sdfdsf - fdsfdsdfsf'),
(648, 24, 'holiday', '2025-09-24 00:00:00', 'Holiday: sdfdsf - fdsfdsdfsf'),
(649, 25, 'holiday', '2025-09-24 00:00:00', 'Holiday: sdfdsf - fdsfdsdfsf'),
(650, 1, 'holiday', '2025-09-25 00:00:00', 'Holiday: sdfdsf - fdsfdsdfsf'),
(651, 2, 'holiday', '2025-09-25 00:00:00', 'Holiday: sdfdsf - fdsfdsdfsf'),
(652, 3, 'holiday', '2025-09-25 00:00:00', 'Holiday: sdfdsf - fdsfdsdfsf'),
(653, 4, 'holiday', '2025-09-25 00:00:00', 'Holiday: sdfdsf - fdsfdsdfsf'),
(654, 5, 'holiday', '2025-09-25 00:00:00', 'Holiday: sdfdsf - fdsfdsdfsf'),
(655, 6, 'holiday', '2025-09-25 00:00:00', 'Holiday: sdfdsf - fdsfdsdfsf'),
(656, 9, 'holiday', '2025-09-25 00:00:00', 'Holiday: sdfdsf - fdsfdsdfsf'),
(657, 24, 'holiday', '2025-09-25 00:00:00', 'Holiday: sdfdsf - fdsfdsdfsf'),
(658, 25, 'holiday', '2025-09-25 00:00:00', 'Holiday: sdfdsf - fdsfdsdfsf');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `time_entries`
--
ALTER TABLE `time_entries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_employee_date` (`employee_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `time_entries`
--
ALTER TABLE `time_entries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=695;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `time_entries`
--
ALTER TABLE `time_entries`
  ADD CONSTRAINT `time_entries_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
