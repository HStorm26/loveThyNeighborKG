/* phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Nov 08, 2025 at 08:09 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4*/

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT;
!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS;
!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION;
!40101 SET NAMES utf8mb4 ;*/

--
-- Database: `neighbordb`
--

-- --------------------------------------------------------

--
-- Table structure for table `dbevents`
--

CREATE TABLE `dbevents` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL,
  --`type` enum('Retreat','Normal') NOT NULL DEFAULT 'Normal',    -Brooke
  `date` char(10) NOT NULL,
  `startTime` char(5) NOT NULL,
  `endTime` char(5) NOT NULL,
  --`endDate` char(10) NOT NULL,   -Brooke
  `description` text NOT NULL,
  `capacity` int(11) NOT NULL,
  `location` text DEFAULT NULL,
  'archived' int(11) DEFAULT 0
  --`affiliation` int(11) DEFAULT NULL,   -Brooke
  --`branch` int(11) DEFAULT NULL,    -Brooke
  --`access` enum('Public','Private') NOT NULL DEFAULT 'Public',
  --`completed` enum('Y','N') NOT NULL DEFAULT 'N',
  --`series_id` varchar(32) DEFAULT NULL     -Brooke
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `dbevents` (`id`, `name`, `date`, `startTime`, `endTime`, `description`, `capacity`, `location`, `archivedd`) VALUES
(1, 'Test Event', '2026-4-10', '08:00', '20:30', 'I want cake', 12 ,'Home Depot', '0'), 
(2, 'Shoes and Clothes', '2026-4-13', '8:00', '20:30', 'I want water', 2, 'Basement', '1');
--
-- Dumping data for table `dbevents`
--

INSERT INTO `dbevents`

--
-- Indexes for dumped tables
--

--
-- Indexes for table `dbevents`
--
ALTER TABLE `dbevents`
  ADD PRIMARY KEY (`id`),
  --ADD KEY `series_id` (`series_id`); This must have been for the Animal Website BROOKE

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `dbevents`
--
ALTER TABLE `dbevents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=248;
COMMIT;
