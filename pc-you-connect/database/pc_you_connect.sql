-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 08, 2026 at 03:04 PM
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
-- Database: `pc_you_connect`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(80) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES
(1, 2, 'login', 'Student logged in', '::1', '2026-10-08 07:35:41'),
(2, 2, 'logout', 'Campus User signed out', '::1', '2026-10-08 07:35:44'),
(3, 1, 'login', 'Admin logged in', '::1', '2026-10-08 07:35:51'),
(4, 1, 'logout', 'Admin User signed out', '::1', '2026-10-08 08:00:14'),
(5, 2, 'login', 'Student logged in', '::1', '2026-10-08 08:00:19'),
(6, 2, 'logout', 'Campus User signed out', '::1', '2026-10-08 08:00:22'),
(7, 1, 'login', 'Admin logged in', '::1', '2026-10-08 08:00:28'),
(8, 1, 'logout', 'Admin User signed out', '::1', '2026-10-08 08:14:23'),
(9, 2, 'login', 'Student logged in', '::1', '2026-10-08 08:14:27'),
(10, 2, 'logout', 'Campus User signed out', '::1', '2026-10-08 08:14:39'),
(11, 1, 'login', 'Admin logged in', '::1', '2026-10-08 08:14:45'),
(12, 1, 'logout', 'Admin User signed out', '::1', '2026-10-08 08:49:21'),
(13, 2, 'login', 'Student logged in', '::1', '2026-10-08 08:49:30'),
(14, 2, 'logout', 'Campus User signed out', '::1', '2026-10-08 08:55:41'),
(15, 1, 'login', 'Admin logged in', '::1', '2026-10-08 08:55:46'),
(16, 1, 'ann_delete', '#1', '::1', '2026-10-08 08:55:51'),
(17, 1, 'ann_delete', '#4', '::1', '2026-10-08 08:56:22'),
(18, 1, 'logout', 'Admin User signed out', '::1', '2026-10-08 08:56:28'),
(19, 2, 'login', 'Student logged in', '::1', '2026-10-08 08:56:34'),
(20, 2, 'logout', 'Campus User signed out', '::1', '2026-10-08 08:56:43'),
(21, 1, 'login', 'Admin logged in', '::1', '2026-10-08 08:56:49'),
(22, 1, 'logout', 'Admin User signed out', '::1', '2026-10-08 09:08:04'),
(23, 1, 'login', 'Admin logged in', '::1', '2026-10-08 09:08:13'),
(24, 1, 'logout', 'Admin User signed out', '::1', '2026-10-08 09:12:03'),
(25, 2, 'login', 'Student logged in', '::1', '2026-10-08 09:12:11'),
(26, 2, 'logout', 'Campus User signed out', '::1', '2026-10-08 09:13:05'),
(27, 1, 'login', 'Admin logged in', '::1', '2026-10-08 09:13:12'),
(28, 1, 'logout', 'Admin User signed out', '::1', '2026-10-08 10:10:18'),
(29, 1, 'login', 'Admin logged in', '::1', '2026-10-08 10:10:55'),
(30, 1, 'logout', 'Admin User signed out', '::1', '2026-10-08 10:10:56'),
(31, 1, 'login', 'Admin logged in', '::1', '2026-10-08 10:24:54'),
(32, 1, 'logout', 'Admin User signed out', '::1', '2026-10-08 10:24:57'),
(33, 1, 'login', 'Admin logged in', '::1', '2026-10-08 11:20:35'),
(34, 1, 'login', 'Admin logged in', '::1', '2026-10-08 12:03:24'),
(35, 1, 'logout', 'Admin User signed out', '::1', '2026-10-08 12:06:28'),
(36, 2, 'login', 'Student logged in', '::1', '2026-10-08 12:06:34'),
(37, 2, 'logout', 'Campus User signed out', '::1', '2026-10-08 12:06:45'),
(38, 1, 'login', 'Admin logged in', '::1', '2026-10-08 12:26:05'),
(39, 1, 'logout', 'Admin User signed out', '::1', '2026-10-08 12:27:44'),
(40, 1, 'login', 'Admin logged in', '::1', '2026-10-08 12:28:22'),
(41, 1, 'logout', 'Admin User signed out', '::1', '2026-10-08 12:32:24'),
(42, 1, 'login', 'Admin logged in', '::1', '2026-10-08 12:33:27'),
(43, 1, 'logout', 'Admin User signed out', '::1', '2026-10-08 12:41:06'),
(44, 1, 'login', 'Admin logged in', '::1', '2026-10-08 12:41:15');

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `author_id` int(11) DEFAULT NULL,
  `author_label` varchar(100) DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `content` text NOT NULL,
  `category` enum('facilities','lost_found') NOT NULL DEFAULT 'facilities',
  `event_date` date DEFAULT NULL,
  `priority` enum('normal','high') NOT NULL DEFAULT 'normal',
  `status` enum('published','archived') NOT NULL DEFAULT 'published',
  `views` int(11) NOT NULL DEFAULT 0,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `author_id`, `author_label`, `title`, `content`, `category`, `event_date`, `priority`, `status`, `views`, `image`, `created_at`, `updated_at`) VALUES
(2, NULL, 'Student Affairs', 'Claim Period for Found Items Ends This Friday', 'All unclaimed items currently held at the Student Affairs Office will only be kept until Friday. Please bring a valid school ID and proof of ownership to claim your item.', 'lost_found', NULL, 'normal', 'published', 99, NULL, '2026-10-08 02:21:13', '2026-10-08 12:04:42'),
(3, NULL, 'Facilities Office', '7th Floor Conference Room Closed for Maintenance (Oct 12–14)', 'The 7th Floor Conference Room is temporarily closed for maintenance from October 12 to 14. Reservations resume October 15.', 'facilities', NULL, 'normal', 'published', 76, NULL, '2026-10-07 07:21:13', '2026-10-08 08:52:13'),
(5, NULL, 'Student Affairs', 'Claim Period for Found Items Ends This Friday', 'All unclaimed items currently held at the Student Affairs Office will only be kept until Friday. Please bring a valid school ID and proof of ownership to claim your item.', 'lost_found', NULL, 'normal', 'published', 54, NULL, '2026-10-06 07:21:13', '2026-10-08 08:52:13'),
(6, NULL, 'Facilities Office', 'Gymnasium Reservations Now Open for Intramurals 2026', 'Team captains may now book the Gymnasium for Intramurals practice sessions. Slots are limited to two hours per team per week and can be reserved through the campus portal. Bring your approval letter on the first session.', 'facilities', '2026-10-18', 'high', 'published', 87, NULL, '2026-10-08 09:11:43', '2026-10-08 12:11:43'),
(7, NULL, 'Student Affairs', 'Found: Set of Keys Near the AVR', 'A keychain with three keys and a lanyard was found outside the Audio Visual Room this morning. If it is yours, submit a claim with proof of ownership through the Lost and Found page of the portal.', 'lost_found', NULL, 'normal', 'published', 54, NULL, '2026-10-08 07:11:43', '2026-10-08 12:11:43'),
(8, NULL, 'Facilities Office', 'AVR Projector Upgrade Completed', 'The Audio Visual Room now has a new laser projector and wireless presentation system. Reservations proceed as normal — no additional setup time is needed for presentations.', 'facilities', NULL, 'normal', 'published', 41, NULL, '2026-10-07 12:11:43', '2026-10-08 12:11:43'),
(9, NULL, 'Admin Office', 'Reminder: Claim Period for Unclaimed Items Ends Friday', 'All unclaimed found items are held at the Student Affairs Office until Friday, 4:00 PM. Bring a valid school ID and be ready to describe the item. Unclaimed items are then turned over to the general lost and found bin.', 'lost_found', NULL, 'high', 'published', 66, NULL, '2026-10-06 12:11:43', '2026-10-08 12:11:43'),
(10, NULL, 'Facilities Office', 'Basketball Court (Elementary) Closed October 20 for Resurfacing', 'The Elementary basketball court will be closed for the whole day on October 20 for court resurfacing. The JHS court remains available. Affected reservation holders have been notified.', 'facilities', '2026-10-20', 'normal', 'published', 35, NULL, '2026-10-05 12:11:43', '2026-10-08 12:11:43');

-- --------------------------------------------------------

--
-- Table structure for table `claim_requests`
--

CREATE TABLE `claim_requests` (
  `id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `proof_details` text NOT NULL,
  `claimant_contact` varchar(80) DEFAULT NULL,
  `attachments` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `admin_remarks` varchar(255) DEFAULT NULL,
  `decided_by` int(11) DEFAULT NULL,
  `decided_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `facilities`
--

CREATE TABLE `facilities` (
  `id` int(11) NOT NULL,
  `name` varchar(120) NOT NULL,
  `description` text DEFAULT NULL,
  `location` varchar(150) DEFAULT NULL,
  `status` enum('available','maintenance','unavailable') NOT NULL DEFAULT 'available',
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `facilities`
--

INSERT INTO `facilities` (`id`, `name`, `description`, `location`, `status`, `image`, `created_at`) VALUES
(1, 'Audio Visual Room (AVR)', 'Air-conditioned AVR for film screenings, seminars, talks, and student organization events.', 'Main Building, Ground Floor', 'available', 'facility-avr.png', '2026-10-08 07:21:13'),
(2, 'Gymnasium', 'Indoor gymnasium for sports fests, assemblies, PE classes, and large campus events.', 'Sports Complex', 'available', 'facility-gym.png', '2026-10-08 07:21:13'),
(3, 'Basketball Court (Elementary)', 'Covered basketball court for the Elementary Department. Open for PE classes and practice sessions.', 'Elementary Department Grounds', 'available', 'facility-court-elem.png', '2026-10-08 07:21:13'),
(4, 'Basketball Court (JHS)', 'Covered basketball court for the Junior High School Department. Open for PE classes and practice sessions.', 'JHS Department Grounds', 'available', 'facility-court-jhs.png', '2026-10-08 07:21:13'),
(5, '7th Floor Conference Room', 'Executive conference room for meetings, defenses, and official university gatherings.', 'Main Building, 7th Floor', 'available', 'facility-7th-floor.png', '2026-10-08 07:21:13');

-- --------------------------------------------------------

--
-- Table structure for table `facility_reservations`
--

CREATE TABLE `facility_reservations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `facility_id` int(11) NOT NULL,
  `purpose` varchar(160) NOT NULL,
  `res_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `notes` text DEFAULT NULL,
  `attachments` text DEFAULT NULL,
  `status` enum('pending','approved','rejected','cancelled','completed') NOT NULL DEFAULT 'pending',
  `remarks` varchar(255) DEFAULT NULL,
  `decided_by` int(11) DEFAULT NULL,
  `decided_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `facility_reservations`
--

INSERT INTO `facility_reservations` (`id`, `user_id`, `facility_id`, `purpose`, `res_date`, `start_time`, `end_time`, `notes`, `attachments`, `status`, `remarks`, `decided_by`, `decided_at`, `created_at`) VALUES
(1, 2, 5, 'Student Council General Assembly', '2026-10-22', '09:00:00', '12:00:00', NULL, NULL, 'approved', NULL, 1, '2026-10-08 15:33:29', '2026-10-08 07:33:29');

-- --------------------------------------------------------

--
-- Table structure for table `lost_found_items`
--

CREATE TABLE `lost_found_items` (
  `id` int(11) NOT NULL,
  `reporter_id` int(11) DEFAULT NULL,
  `report_type` enum('found','lost') NOT NULL DEFAULT 'found',
  `item_name` varchar(140) NOT NULL,
  `category` enum('electronics','id_card','accessories','bags','keys','documents','clothing','books','other') NOT NULL DEFAULT 'other',
  `description` text DEFAULT NULL,
  `location_found` varchar(160) DEFAULT NULL,
  `date_found` date NOT NULL,
  `status` enum('unclaimed','claimed','turned_in','searching','reunited') NOT NULL DEFAULT 'unclaimed',
  `claimed_by` varchar(140) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lost_found_items`
--

INSERT INTO `lost_found_items` (`id`, `reporter_id`, `report_type`, `item_name`, `category`, `description`, `location_found`, `date_found`, `status`, `claimed_by`, `image`, `created_at`, `updated_at`) VALUES
(1, NULL, 'found', 'Silver Watch', 'accessories', 'Silver analog wrist watch with leather strap.', 'Found near women\'s comfort room', '2026-10-08', 'unclaimed', NULL, NULL, '2026-10-08 04:21:13', '2026-10-08 07:21:13'),
(2, NULL, 'found', 'Student ID Card', 'id_card', 'Student ID turned over to the office by a fellow student.', 'Found at NAB building hallway', '2026-10-06', 'unclaimed', NULL, NULL, '2026-10-06 07:21:13', '2026-10-08 07:21:13'),
(3, NULL, 'found', 'Black Backpack', 'bags', 'Black backpack containing notebooks and a water bottle.', 'Left at JHS library', '2026-10-07', 'unclaimed', NULL, NULL, '2026-10-07 07:21:13', '2026-10-08 07:21:13'),
(4, NULL, 'found', 'Samsung Phone', 'electronics', 'Black Samsung smartphone, locked, in a clear case.', 'Found at Room 311', '2026-10-08', 'unclaimed', NULL, NULL, '2026-10-08 03:21:13', '2026-10-08 07:21:13'),
(5, NULL, 'found', 'Keychain with Keys', 'keys', 'Keychain with three keys and a carabiner.', 'Found near gymnasium entrance', '2026-10-08', 'claimed', NULL, NULL, '2026-10-08 02:21:13', '2026-10-08 07:21:13'),
(6, NULL, 'found', 'Eyeglasses', 'accessories', 'Rectangular eyeglasses in a black hard case.', 'Left at Computer Laboratory 9', '2026-10-05', 'claimed', NULL, NULL, '2026-10-05 07:21:13', '2026-10-08 07:21:13'),
(7, NULL, 'found', 'ATM Card', 'id_card', 'Bank ATM card. Turned in to the treasury office.', 'Found at treasury', '2026-10-08', 'turned_in', NULL, NULL, '2026-10-08 01:21:13', '2026-10-08 07:21:13');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('reservation','lost_found','announcement','system') NOT NULL DEFAULT 'system',
  `title` varchar(160) NOT NULL,
  `message` text DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `type`, `title`, `message`, `link`, `is_read`, `created_at`) VALUES
(1, 2, 'reservation', 'Reservation Approved', 'Your reservation for the 7th Floor Conference Room has been approved.', NULL, 1, '2026-10-08 07:33:29');

-- --------------------------------------------------------

--
-- Table structure for table `reservation_status_history`
--

CREATE TABLE `reservation_status_history` (
  `id` int(11) NOT NULL,
  `reservation_id` int(11) NOT NULL,
  `status` varchar(30) NOT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `changed_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reservation_status_history`
--

INSERT INTO `reservation_status_history` (`id`, `reservation_id`, `status`, `remarks`, `changed_by`, `created_at`) VALUES
(1, 1, 'pending', 'Reservation submitted', 2, '2026-10-08 07:33:29'),
(2, 1, 'approved', 'Approved by admin', 1, '2026-10-08 07:33:29');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `role` enum('student','faculty','staff','admin') NOT NULL DEFAULT 'student',
  `user_id` varchar(50) NOT NULL,
  `first_name` varchar(60) NOT NULL,
  `last_name` varchar(60) NOT NULL,
  `email` varchar(140) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `settings` text DEFAULT NULL,
  `remember_token` varchar(64) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `role`, `user_id`, `first_name`, `last_name`, `email`, `password`, `avatar`, `status`, `settings`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin', 'Admin', 'User', 'admin@pcu.edu.ph', '$2y$10$irFnL7T7IZ9MKHu6S8Bmd.RXbVBQjhOtGojF/n4LpeBGePgC/OcX6', NULL, 'active', NULL, NULL, '2026-10-08 07:33:29', '2026-10-08 07:33:29'),
(2, 'student', 'user', 'Campus', 'User', 'user@student.pcu.edu.ph', '$2y$10$irFnL7T7IZ9MKHu6S8Bmd.RXbVBQjhOtGojF/n4LpeBGePgC/OcX6', NULL, 'active', NULL, NULL, '2026-10-08 07:33:29', '2026-10-08 07:33:29');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `author_id` (`author_id`);

--
-- Indexes for table `claim_requests`
--
ALTER TABLE `claim_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `decided_by` (`decided_by`);

--
-- Indexes for table `facilities`
--
ALTER TABLE `facilities`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `facility_reservations`
--
ALTER TABLE `facility_reservations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `facility_id` (`facility_id`),
  ADD KEY `decided_by` (`decided_by`);

--
-- Indexes for table `lost_found_items`
--
ALTER TABLE `lost_found_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reporter_id` (`reporter_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `reservation_status_history`
--
ALTER TABLE `reservation_status_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reservation_id` (`reservation_id`),
  ADD KEY `changed_by` (`changed_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `claim_requests`
--
ALTER TABLE `claim_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `facilities`
--
ALTER TABLE `facilities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `facility_reservations`
--
ALTER TABLE `facility_reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `lost_found_items`
--
ALTER TABLE `lost_found_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `reservation_status_history`
--
ALTER TABLE `reservation_status_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `claim_requests`
--
ALTER TABLE `claim_requests`
  ADD CONSTRAINT `claim_requests_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `lost_found_items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `claim_requests_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `claim_requests_ibfk_3` FOREIGN KEY (`decided_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `facility_reservations`
--
ALTER TABLE `facility_reservations`
  ADD CONSTRAINT `facility_reservations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `facility_reservations_ibfk_2` FOREIGN KEY (`facility_id`) REFERENCES `facilities` (`id`),
  ADD CONSTRAINT `facility_reservations_ibfk_3` FOREIGN KEY (`decided_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `lost_found_items`
--
ALTER TABLE `lost_found_items`
  ADD CONSTRAINT `lost_found_items_ibfk_1` FOREIGN KEY (`reporter_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reservation_status_history`
--
ALTER TABLE `reservation_status_history`
  ADD CONSTRAINT `reservation_status_history_ibfk_1` FOREIGN KEY (`reservation_id`) REFERENCES `facility_reservations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reservation_status_history_ibfk_2` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
