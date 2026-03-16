-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Mar 16, 2026 at 06:00 PM
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

--
-- Table structure for table `dbroleevents`
--

CREATE TABLE `dbroleevents` (
  `eventID` int(11) NOT NULL,
  `roleID` int(11) NOT NULL,
  `capacity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `dbroleevents`
--
ALTER TABLE `dbroleevents`
  ADD PRIMARY KEY (`eventID`,`roleID`),
  ADD KEY `roleID` (`roleID`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `dbroleevents`
--
ALTER TABLE `dbroleevents`
  ADD CONSTRAINT `dbroleevents_ibfk_1` FOREIGN KEY (`eventID`) REFERENCES `dbevents` (`id`),
  ADD CONSTRAINT `dbroleevents_ibfk_2` FOREIGN KEY (`roleID`) REFERENCES `dbroles` (`role_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
