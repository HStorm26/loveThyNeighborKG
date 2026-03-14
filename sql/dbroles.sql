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
-- Database: `dbkxnmd1xwle9j`
--
CREATE DATABASE IF NOT EXISTS `dbroles` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci;
USE `dbroles`;


-- --------------------------------------------------------

--
-- Table structure for table `dbroles`
--
CREATE TABLE IF NOT EXISTS `dbroles` (
  `role_id` int AUTO_INCREMENT PRIMARY KEY,
  `role` varchar(255),
  `role_description` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `person_roles`
--

CREATE TABLE IF NOT EXISTS `person_roles` (
  `person_id` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role_id` int NOT NULL,
  PRIMARY KEY (`person_id`, `role_id`),
  FOREIGN KEY (`person_id`) REFERENCES dbpersons(id),
  FOREIGN KEY (`role_id`) REFERENCES dbroles(role_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
