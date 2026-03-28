-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Mar 27, 2026 at 04:06 PM
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
-- Database: `neighbordb`
--

-- --------------------------------------------------------

--
-- Table structure for table `person_roles`
--

CREATE TABLE `person_roles` (
  `person_id` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `role_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `person_roles`
--

INSERT INTO `person_roles` (`person_id`, `role_id`, `event_id`) VALUES
('email', 1, 2),
('email', 1, 248),
('email', 2, 248);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `person_roles`
--
ALTER TABLE `person_roles`
  ADD PRIMARY KEY (`person_id`,`role_id`,`event_id`),
  ADD KEY `role_id` (`role_id`),
  ADD KEY `person_roles_ibfk_3` (`event_id`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `person_roles`
--
ALTER TABLE `person_roles`
  ADD CONSTRAINT `person_roles_ibfk_1` FOREIGN KEY (`person_id`) REFERENCES `dbpersons` (`id`),
  ADD CONSTRAINT `person_roles_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `dbroles` (`role_id`),
  ADD CONSTRAINT `person_roles_ibfk_3` FOREIGN KEY (`event_id`) REFERENCES `dbevents` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
