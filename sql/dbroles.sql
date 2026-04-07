-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Feb 09, 2026 at 11:09 AM
-- Server version: 8.4.5-5
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

use neighbordb;


-- --------------------------------------------------------

--
-- Table structure for table `dbroles`
--
CREATE TABLE IF NOT EXISTS `dbroles` (
  `role_id` int AUTO_INCREMENT PRIMARY KEY,
  `role` varchar(255),
  `role_description` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `dbroles` (`role_id`, `role`, `role_description`) VALUES
(1, 'Truck Unloader', 'Show up during the time slot'),
(2, 'Pickup', 'Show up during the time slot'),
(3, 'Sorting', 'Show up during the time slot'), 
(4, 'Distribution', 'Show up during the time slot'),
(5, 'Setup', 'Arrive 30 minutes early'), 
(6, 'Cleanup', 'Stay 30 minutes afterwards');

-- --------------------------------------------------------