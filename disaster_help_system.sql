SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


DROP DATABASE IF EXISTS disaster_help_system;
CREATE DATABASE disaster_help_system;
USE disaster_help_system;

CREATE TABLE `complaints` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `category` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `latitude` varchar(50) DEFAULT NULL,
  `longitude` varchar(50) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `status` enum('Pending','In Progress','Resolved') DEFAULT 'Pending',
  `assigned_to` int(11) DEFAULT NULL,
  `proof_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `complaints`
--

INSERT INTO `complaints` (`id`, `user_id`, `category`, `description`, `photo_path`, `latitude`, `longitude`, `address`, `status`, `assigned_to`, `proof_path`, `created_at`) VALUES
(1, 3, 'Roads & Potholes', 'ISSUE: Potholes \nDETAILS: significant safety hazards causing numerous accidents, injuries, and fatalities globally, especially in India, leading to vehicle damage and legal battles for compensation', 'uploads/1765893679_potholes.jpg', '15.830307986455471', '74.49829101562501', NULL, 'Pending', NULL, 'uploads/1766409191_proof_1000153357.jpg', '2025-12-16 14:01:19'),
(2, 4, 'Street Lights', 'ISSUE: Brocket street light \nDETAILS: often caused by electrical faults, aging components (like LED drivers/circuit boards), or poor installation/quality', 'uploads/1765893808_broken_light.jpg', '16.15558982828776', '74.48730468750001', NULL, 'Pending', NULL, 'uploads/1765893932_proof_resolved_light.jpg', '2025-12-16 14:03:28'),
(3, 15, 'Water Supply', 'ISSUE: Bottle daag neer kaali aaget \nDETAILS: ನೀರು ಬರುತ್ತಿಲ್ಲಾ', 'uploads/1766467979_17664679320011422929596833053168.jpg', '15.857116902879135', '74.50549762023029', NULL, 'In Progress', 8, 'uploads/1766468086_proof_image.jpg', '2025-12-23 05:32:59');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','citizen','helper') DEFAULT 'citizen',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `password`, `role`, `created_at`) VALUES
(1, 'System Admin', 'admin@gmail.com', '1234567890', '$2y$10$2zF2KASf.PUdizLfagqLteLc/ByvFyT5go/rozZezkt0nYtZf73.6', 'admin', '2025-12-16 13:56:48'),
(3, 'sumith k', 'sumith@gmail.com', '8660919746', '$2y$10$3ISJ2zTff5sjYJL5j9YkmOkHhsyO890/ftbNnVyN48bJe0DQsUJBS', 'citizen', '2025-12-16 13:59:05'),
(4, 'santosh', 'santosh@gmail.com', '7894561230', '$2y$10$dzn/bSRuL82Mpz4UdmBIOe9RS3Qkj/oZCxBb7ZdTuAokFjcrYK/xq', 'citizen', '2025-12-16 14:01:54'),
(6, 'System Admin', 'admin@bcir.com', '0000000000', '$2y$10$YourHashHereOrUsePHPToGenerate', 'admin', '2025-12-23 03:37:10'),
(8, 'Municipal Corporation – Engineering / Roads Division', 'road@gmail.com', '8660919746', '$2y$10$be2//HaTcECF6j4VZULIg.o4aif.7Ur142gJKhnL6kpafBzyBoy42', 'helper', '2025-12-23 05:01:02'),
(9, 'Municipal Corporation – Electrical Department', 'Elect@gmail.com', '8660919746', '$2y$10$okiBJiRCS7EHvCASGbVhI.OmuY6DKBE.glfDbLzghihPCGKWevRtq', 'helper', '2025-12-23 05:02:39'),
(10, 'Municipal Corporation – Water Works Department', 'water@gmail.com', '8660919746', '$2y$10$w58c3E6d5KrKlCXeJrq9sODRtDWWKPi7PEtG.7gfiNJJb/8.gepwe', 'helper', '2025-12-23 05:03:14'),
(11, 'Municipal Corporation – Sanitation / Solid Waste Management', 'waste@gmail.com', '8660919746', '$2y$10$9fe1oylLdGIVOX1byzBuMuOUr6sffFDjlOEBpYOEmb4GuVgEFK1Dm', 'helper', '2025-12-23 05:03:53'),
(12, 'Municipal Corporation – Drainage / Sewerage Department', 'drainage@gmail.com', '8660919746', '$2y$10$EEy5RetR627j4EgsyOzMROOZJk6owu5X8AhqbqFWQECdvA02WVth2', 'helper', '2025-12-23 05:05:41'),
(13, 'Municipal Corporation – Parks & Gardens Department', 'park@gmail.com', '8660919746', '$2y$10$5/EcJMhP0fXakT8g60PV3.KGxDQCI4IhAgEgFLORvQdd.l6.ckof2', 'helper', '2025-12-23 05:06:17'),
(14, 'Municipal Corporation – Transport / Enforcement Wing', 'transport@gmail.com', '8660919746', '$2y$10$f6Jok1LANd8mQXXcZ3IvUu9Xuv7Gn7yml6nP2JJ/4vLN61riQhx/C', 'helper', '2025-12-23 05:06:53'),
(15, 'Suchitra ', 'suchitra@1426', '8123570794', '$2y$10$YCmlo6ZTkK4mmPYZs3ManeSAdvSl937ZAZaeRPDAB8CyWcn1NcgS6', 'citizen', '2025-12-23 05:27:58');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `complaints`
--
ALTER TABLE `complaints`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_user` (`user_id`),
  ADD KEY `fk_assigned_to` (`assigned_to`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `complaints`
--
ALTER TABLE `complaints`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `complaints`
--
ALTER TABLE `complaints`
  ADD CONSTRAINT `fk_assigned_to` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
