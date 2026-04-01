drop table if exists `dbpersonhours`;
CREATE TABLE `dbpersonhours` (
  `personID` varchar(256) NOT NULL,
  `eventID` int(11) NOT NULL,
  `roleID` int(11) NOT NULL,
  `start_time` timestamp NULL DEFAULT NULL,
  `end_time` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`personID`,`eventID`,`roleID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


--
-- Indexes for table `dbpersonhours`
--
ALTER TABLE `dbpersonhours`
  ADD KEY `FkpersonID2` (`personID`),
  ADD KEY `FKeventID3` (`eventID`),
  ADD KEY `FKroleID4` (`roleID`);