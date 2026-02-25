-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 25, 2026 at 06:33 PM
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
-- Database: `test`
--

-- --------------------------------------------------------

--
-- Table structure for table `dbpersonstest`
--

CREATE TABLE `dbpersonstest` (
  `id` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `first_name` text NOT NULL,
  `last_name` text DEFAULT NULL,
  `phone_number` varchar(12) NOT NULL,
  `email` text DEFAULT NULL,
  `email_prefs` enum('true','false') DEFAULT NULL,
  `birthday` text DEFAULT NULL,
  `state` varchar(2) DEFAULT NULL,
  `city` text DEFAULT NULL,
  `street_address` text DEFAULT NULL,
  `zip_code` text DEFAULT NULL,
  `emergency_contact_name` text DEFAULT NULL,
  `emergency_contact_phone` varchar(12) DEFAULT NULL,
  `emergency_contact_relation` text DEFAULT NULL,
  `archived` tinyint(1) DEFAULT NULL,
  `password` text DEFAULT NULL,
  `contact_num` varchar(255) DEFAULT 'n/a',
  `contact_method` text DEFAULT NULL,
  `type` text DEFAULT NULL,
  `status` text DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `dbpersonstest`
--
ALTER TABLE `dbpersonstest`
  ADD PRIMARY KEY (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
