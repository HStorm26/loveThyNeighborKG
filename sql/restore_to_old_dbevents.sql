DROP table if Exists `dbevents`;
CREATE TABLE `dbevents` (
  `id` int NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('Retreat','Normal') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Normal',
  `startDate` char(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `startTime` char(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `endTime` char(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `endDate` char(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `capacity` int NOT NULL,
  `location` text COLLATE utf8mb4_unicode_ci,
  `affiliation` int DEFAULT NULL,
  `branch` int DEFAULT NULL,
  `access` enum('Public','Private') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Public',
  `completed` enum('Y','N') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  `series_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;