-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 06, 2025 at 09:46 AM
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
-- Database: `ccsmf`
--

-- --------------------------------------------------------

--
-- Table structure for table `smf_transactions`
--

CREATE TABLE `smf_transactions` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `student_name` varchar(120) NOT NULL,
  `student_identifier` varchar(50) NOT NULL,
  `program` enum('BSBA','BSIS','BMMA','BSA','BSTM','BSED','BEED','BCAED') DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `status` enum('pending','under_review','approved','rejected','updated') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `smf_transactions`
--

INSERT INTO `smf_transactions` (`id`, `user_id`, `student_name`, `student_identifier`, `program`, `amount`, `photo_path`, `status`, `created_at`) VALUES
(1, 4, 'Josh', 'IS002T22', NULL, 100.00, 'uploads/smf_20251006_072908_b17a707c.jpg', 'pending', '2025-10-06 05:29:08'),
(2, 4, 'Josh', 'IS002T22', NULL, 100.00, 'uploads/smf_20251006_072910_9fd5e82b.jpg', 'pending', '2025-10-06 05:29:10'),
(3, 4, 'Josh', 'IS002T22', NULL, 100.00, 'uploads/smf_20251006_072911_39455e2b.jpg', 'pending', '2025-10-06 05:29:11'),
(4, 4, 'Josh', 'IS002T22', NULL, 100.00, 'uploads/smf_20251006_072911_28f43ea1.jpg', 'pending', '2025-10-06 05:29:11'),
(5, 4, 'Josh', 'IS002T22', NULL, 100.00, 'uploads/smf_20251006_072911_6e294257.jpg', 'pending', '2025-10-06 05:29:11'),
(6, 4, 'Josh', 'IS002T22', NULL, 100.00, 'uploads/smf_20251006_072911_36d4193d.jpg', 'pending', '2025-10-06 05:29:11'),
(7, 4, 'Josh', 'IS002T22', NULL, 100.00, 'uploads/smf_20251006_072912_6249dc3f.jpg', 'pending', '2025-10-06 05:29:12'),
(8, 4, 'Josh', 'IS002T22', NULL, 100.00, 'uploads/smf_20251006_072912_f31688cc.jpg', 'pending', '2025-10-06 05:29:12'),
(9, 4, 'Josh', 'IS002T22', NULL, 100.00, 'uploads/smf_20251006_072913_308f8242.jpg', 'approved', '2025-10-06 05:29:13'),
(10, 4, 'Josh', 'IS002T22', NULL, 100.00, 'uploads/smf_20251006_072913_2b9b9a13.jpg', 'approved', '2025-10-06 05:29:13'),
(11, 4, 'Fitz', 'IS002T23', NULL, 50.00, 'uploads/smf_20251006_073111_89eef152.jpg', 'rejected', '2025-10-06 05:31:11'),
(12, 4, 'Les', '2023-CC-000100', 'BSIS', 1000.00, 'uploads/smf_20251006_074916_ea898d3d.png', 'approved', '2025-10-06 05:49:16'),
(13, 14, 'Fitzgerald Andres', '2023-CC-000117', 'BSIS', 50.00, 'uploads/smf_20251006_083734_8c768775.jpg', 'approved', '2025-10-06 06:37:34');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(120) NOT NULL,
  `email` varchar(190) NOT NULL,
  `role` enum('student','admin','ccsc') NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `role`, `password_hash`, `created_at`) VALUES
(1, 'Alice Admin', 'admin@example.com', 'admin', '$2y$12$KYSsK1iiELgUMhpWm3X9Yu3zWskQuPpBkpEV5/ozuIFxoLC2jMzx6', '2025-10-06 05:11:29'),
(2, 'Sam Student', 'student@example.com', 'student', '$2y$12$KYSsK1iiELgUMhpWm3X9Yu3zWskQuPpBkpEV5/ozuIFxoLC2jMzx6', '2025-10-06 05:11:29'),
(3, 'Chris CCSC', 'ccsc@example.com', 'ccsc', '$2y$12$KYSsK1iiELgUMhpWm3X9Yu3zWskQuPpBkpEV5/ozuIFxoLC2jMzx6', '2025-10-06 05:11:29'),
(4, 'Josh McDowell Trapal', 'joshmcdowelltrapal@gmail.com', 'student', '$2y$10$77iW18Eg0oXgKiGMvq3bUuuEyApl0941itaWW7T17TNBRRCTTn3Q.', '2025-10-06 05:21:17'),
(14, 'Fitzgerald Andres', 'ftzndrs@gmail.com', 'student', '$2y$10$a87l7XuQuKnF0kA/1rCX3eOi3BNUmty4uzdsPAD3XmQkh/zk.pubC', '2025-10-06 06:36:47'),
(15, 'Fitz', 'fitzgerald@gmail.com', 'ccsc', '$2y$10$dnJJfBow1HBXlvA4FC0SJO/AS4ntr8MeL5ykrEfjkLqwM4lo/FJiW', '2025-10-06 07:24:17');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `smf_transactions`
--
ALTER TABLE `smf_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_smf_user` (`user_id`);

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
-- AUTO_INCREMENT for table `smf_transactions`
--
ALTER TABLE `smf_transactions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `smf_transactions`
--
ALTER TABLE `smf_transactions`
  ADD CONSTRAINT `fk_smf_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
