-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Apr 29, 2026 at 06:20 PM
-- Server version: 8.4.6-6
-- PHP Version: 8.2.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `neighbordb`
--

-- --------------------------------------------------------

--
-- Table structure for table `dbeventpersons`
--

CREATE TABLE `dbeventpersons` (
  `id` int NOT NULL,
  `eventID` int NOT NULL,
  `userID` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `attended` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dbevents`
--

CREATE TABLE `dbevents` (
  `id` int NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` char(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `startTime` char(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `endTime` char(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `capacity` int NOT NULL,
  `location` text COLLATE utf8mb4_unicode_ci,
  `archived` tinyint DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dbmessages`
--

CREATE TABLE `dbmessages` (
  `id` int NOT NULL,
  `senderID` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `recipientID` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `time` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `wasRead` tinyint(1) NOT NULL DEFAULT '0',
  `prioritylevel` tinyint NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dbpasswordreset`
--

CREATE TABLE `dbpasswordreset` (
  `id` int NOT NULL,
  `person_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token_hash` char(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dbpersonhours`
--

CREATE TABLE `dbpersonhours` (
  `personID` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `eventID` int NOT NULL,
  `roleID` int NOT NULL,
  `start_time` timestamp NULL DEFAULT NULL,
  `end_time` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dbpersons`
--

CREATE TABLE `dbpersons` (
  `id` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
  `first_name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` text COLLATE utf8mb4_unicode_ci,
  `phone_number` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` text COLLATE utf8mb4_unicode_ci,
  `email_prefs` enum('true','false') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `birthday` text COLLATE utf8mb4_unicode_ci,
  `t-shirt_size` text COLLATE utf8mb4_unicode_ci,
  `state` varchar(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` text COLLATE utf8mb4_unicode_ci,
  `street_address` text COLLATE utf8mb4_unicode_ci,
  `zip_code` text COLLATE utf8mb4_unicode_ci,
  `emergency_contact_first_name` text COLLATE utf8mb4_unicode_ci,
  `emergency_contact_phone` varchar(16) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emergency_contact_relation` text COLLATE utf8mb4_unicode_ci,
  `archived` tinyint(1) DEFAULT NULL,
  `password` text COLLATE utf8mb4_unicode_ci,
  `contact_num` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'n/a',
  `contact_method` text COLLATE utf8mb4_unicode_ci,
  `type` text COLLATE utf8mb4_unicode_ci,
  `status` text COLLATE utf8mb4_unicode_ci,
  `photo_release` tinyint(1) DEFAULT '0',
  `community_service` tinyint(1) DEFAULT '0',
  `notes` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dbroleevents`
--

CREATE TABLE `dbroleevents` (
  `eventID` int NOT NULL,
  `roleID` int NOT NULL,
  `capacity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dbroles`
--

CREATE TABLE `dbroles` (
  `role_id` int NOT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role_description` text COLLATE utf8mb4_unicode_ci,
  `shift_group` enum('Early','Main','Late') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Main'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `dbroles`
--

INSERT INTO `dbroles` (`role_id`, `role`, `role_description`, `shift_group`) VALUES
(1, 'Truck Unloading', 'Show up during the time slot', 'Main'),
(2, 'Pickup', 'Show up during the time slot', 'Main'),
(3, 'Sorting', 'Show up during the time slot', 'Main'),
(4, 'Distribution', 'Show up during the time slot', 'Main'),
(5, 'Setup', 'Arrive 30 minutes early', 'Early'),
(6, 'Cleanup', 'Stay 30 minutes afterwards', 'Late');

-- --------------------------------------------------------

--
-- Table structure for table `dbscheduledemails`
--

CREATE TABLE `dbscheduledemails` (
  `id` int NOT NULL,
  `userID` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
  `recipientID` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `scheduledSend` date NOT NULL,
  `sent` tinyint(1) DEFAULT '0',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `person_roles`
--

CREATE TABLE `person_roles` (
  `person_id` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `role_id` int NOT NULL,
  `event_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `dbeventpersons`
--
ALTER TABLE `dbeventpersons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FKeventID` (`eventID`),
  ADD KEY `FKpersonID` (`userID`);

--
-- Indexes for table `dbevents`
--
ALTER TABLE `dbevents`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `dbmessages`
--
ALTER TABLE `dbmessages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `dbpasswordreset`
--
ALTER TABLE `dbpasswordreset`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_person_id` (`person_id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_token_hash` (`token_hash`);

--
-- Indexes for table `dbpersonhours`
--
ALTER TABLE `dbpersonhours`
  ADD PRIMARY KEY (`personID`,`eventID`,`roleID`),
  ADD KEY `FkpersonID2` (`personID`),
  ADD KEY `FKeventID3` (`eventID`),
  ADD KEY `FKroleID4` (`roleID`);

--
-- Indexes for table `dbpersons`
--
ALTER TABLE `dbpersons`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `dbroleevents`
--
ALTER TABLE `dbroleevents`
  ADD PRIMARY KEY (`eventID`,`roleID`);

--
-- Indexes for table `dbroles`
--
ALTER TABLE `dbroles`
  ADD PRIMARY KEY (`role_id`);

--
-- Indexes for table `dbscheduledemails`
--
ALTER TABLE `dbscheduledemails`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `person_roles`
--
ALTER TABLE `person_roles`
  ADD PRIMARY KEY (`person_id`,`role_id`,`event_id`),
  ADD KEY `role_id` (`role_id`),
  ADD KEY `person_roles_ibfk_3` (`event_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `dbeventpersons`
--
ALTER TABLE `dbeventpersons`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=88;

--
-- AUTO_INCREMENT for table `dbevents`
--
ALTER TABLE `dbevents`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `dbmessages`
--
ALTER TABLE `dbmessages`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=567;

--
-- AUTO_INCREMENT for table `dbpasswordreset`
--
ALTER TABLE `dbpasswordreset`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `dbroles`
--
ALTER TABLE `dbroles`
  MODIFY `role_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2147483648;

--
-- AUTO_INCREMENT for table `dbscheduledemails`
--
ALTER TABLE `dbscheduledemails`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
