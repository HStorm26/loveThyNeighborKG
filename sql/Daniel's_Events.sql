-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Nov 08, 2025 at 08:09 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

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

-- Delete the old dbevents

DROP TABLE IF Exists `dbevents`; -- this is important -Daniel

--
-- Table structure for table `dbevents`
--


CREATE TABLE `dbevents` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL,
  `date` char(10) NOT NULL,
  `startTime` char(5) NOT NULL,
  `endTime` char(5) NOT NULL,
  `description` text NOT NULL,
  `capacity` int(11) NOT NULL,
  `location` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `dbevents` (`id`, `name`, `date`, `startTime`, `endTime`, `description`, `capacity`, `location`) VALUES -- we left the feilds that were commented out
(1, 'Make this work', '2026-3-30', '08:00', '20:30', 'I want cake', 12 ,'my house'), 
(2, 'Brooke wants this to work', '2026-3-29', '8:00', '20:30', 'I want water', 2, 'the basement');


--
-- Indexes for table `dbevents`
--
ALTER TABLE `dbevents`
  ADD PRIMARY KEY (`id`);
 

-- AUTO_INCREMENT for table `dbevents`
--
ALTER TABLE `dbevents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=248;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
