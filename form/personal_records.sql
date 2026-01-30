-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 30, 2026 at 06:52 PM
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
-- Database: `personal_records`
--

-- --------------------------------------------------------

--
-- Table structure for table `dependents`
--

CREATE TABLE `dependents` (
  `id` int(11) NOT NULL,
  `personal_data_id` int(11) NOT NULL,
  `type` enum('spouse','child','beneficiary') NOT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `suffix` varchar(20) DEFAULT NULL,
  `relationship` varchar(50) DEFAULT NULL,
  `dob` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dependents`
--

INSERT INTO `dependents` (`id`, `personal_data_id`, `type`, `last_name`, `first_name`, `middle_name`, `suffix`, `relationship`, `dob`) VALUES
(1, 4, 'spouse', 'Santos', 'Maria', 'Luna', NULL, 'Spouse', '1987-07-22'),
(2, 4, 'child', 'Santos', 'Pedro', 'Dela Cruz', 'III', 'Child', '2010-05-15'),
(3, 4, 'child', 'Santos', 'Ana', 'Luna', NULL, 'Child', '2012-09-30'),
(4, 6, 'beneficiary', 'Reyes', 'Miguel', 'Santos', NULL, 'Nephew', '1990-03-15'),
(5, 6, 'beneficiary', 'Lopez', 'Carmen', 'Dela Cruz', NULL, 'Sister', '1965-11-20'),
(6, 7, 'child', 'Garcia', 'Jose', 'Cruz', NULL, 'Child', '2002-01-10'),
(7, 7, 'child', 'Garcia', 'Isabella', 'Cruz', NULL, 'Child', '2005-08-22'),
(8, 7, 'child', 'Garcia', 'Sofia', 'Cruz', NULL, 'Child', '2008-03-14'),
(9, 10, 'spouse', 'Ramos', 'Carlos', 'Dela Cruz', NULL, 'Spouse', '1980-09-25'),
(10, 11, 'beneficiary', 'Mendoza', 'Roberto', 'Santos', 'Jr.', 'Son', '1980-05-20'),
(11, 11, 'beneficiary', 'Mendoza', 'Patricia', 'Santos', NULL, 'Daughter', '1982-08-15'),
(12, 11, 'beneficiary', 'Salazar', 'Teresa', 'Mendoza', NULL, 'Sister', '1960-03-10'),
(13, 13, 'child', 'Aguilar', 'Fernando', 'Dela Rosa', 'Jr.', 'Child', '1975-03-10');

-- --------------------------------------------------------

--
-- Table structure for table `personal_data`
--

CREATE TABLE `personal_data` (
  `id` int(11) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `suffix` varchar(20) DEFAULT NULL,
  `dob` date NOT NULL,
  `sex` enum('Male','Female') NOT NULL,
  `civil_status` varchar(50) NOT NULL,
  `civil_status_other` varchar(100) DEFAULT NULL,
  `tin` varchar(50) DEFAULT NULL,
  `nationality` varchar(100) NOT NULL,
  `religion` varchar(100) DEFAULT NULL,
  `pob_city_municipality` varchar(100) NOT NULL,
  `pob_province` varchar(100) DEFAULT NULL,
  `pob_country` varchar(100) DEFAULT 'Philippines',
  `birth_same_as_home` tinyint(1) DEFAULT 0,
  `home_address` text DEFAULT NULL,
  `mobile` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telephone` varchar(50) DEFAULT NULL,
  `father_last_name` varchar(100) DEFAULT NULL,
  `father_first_name` varchar(100) DEFAULT NULL,
  `father_middle_name` varchar(100) DEFAULT NULL,
  `father_suffix` varchar(20) DEFAULT NULL,
  `father_dob` date DEFAULT NULL,
  `mother_last_name` varchar(100) DEFAULT NULL,
  `mother_first_name` varchar(100) DEFAULT NULL,
  `mother_middle_name` varchar(100) DEFAULT NULL,
  `mother_suffix` varchar(20) DEFAULT NULL,
  `mother_dob` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `personal_data`
--

INSERT INTO `personal_data` (`id`, `last_name`, `first_name`, `middle_name`, `suffix`, `dob`, `sex`, `civil_status`, `civil_status_other`, `tin`, `nationality`, `religion`, `pob_city_municipality`, `pob_province`, `pob_country`, `birth_same_as_home`, `home_address`, `mobile`, `email`, `telephone`, `father_last_name`, `father_first_name`, `father_middle_name`, `father_suffix`, `father_dob`, `mother_last_name`, `mother_first_name`, `mother_middle_name`, `mother_suffix`, `mother_dob`, `created_at`) VALUES
(4, 'Santos', 'Juan', 'Dela Cruz', 'Jr.', '1985-03-15', 'Male', 'Married', NULL, '123-456-789-000', 'Filipino', 'Roman Catholic', 'Makati City', 'Metro Manila', 'Philippines', 0, '123 Ayala Ave, Makati City, Metro Manila, Philippines', '09171234567', 'juan.santos@email.com', '+63-2-8123456', 'Santos', 'Pedro', 'Reyes', 'Sr.', '1955-08-20', 'Reyes', 'Maria', 'Dela Cruz', NULL, '1958-11-10', '2026-01-30 17:43:25'),
(5, 'Cruz', 'Anna', 'Marie', NULL, '1992-12-25', 'Female', 'Single', NULL, NULL, 'Filipino', 'Iglesia ni Cristo', 'Quezon City', 'Metro Manila', 'Philippines', 1, 'Quezon City, Metro Manila, Philippines', '09189876543', 'anna.cruz@email.com', NULL, 'Cruz', 'Roberto', 'Magsaysay', NULL, '1965-04-12', 'Magsaysay', 'Elena', 'Santos', NULL, '1968-09-05', '2026-01-30 17:43:25'),
(6, 'Reyes', 'Roberto', 'Mendoza', NULL, '1960-07-08', 'Male', 'Widowed', NULL, '789-123-456-000', 'Filipino', 'Roman Catholic', 'Cebu City', 'Cebu', 'Philippines', 0, '456 Mango St, Cebu City, Cebu, Philippines', '09209876543', 'roberto.reyes@email.com', '+63-32-1234567', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-30 17:43:25'),
(7, 'Garcia', 'Maria', 'Cecilia', NULL, '1975-11-30', 'Female', 'Legally Separated', NULL, '456-789-123-000', 'Filipino', 'Roman Catholic', 'Davao City', 'Davao del Sur', 'Philippines', 0, '789 Durian St, Davao City, Davao del Sur, Philippines', '09351234567', 'maria.garcia@email.com', NULL, 'Garcia', 'Antonio', 'Rodriguez', NULL, '1945-02-28', 'Rodriguez', 'Carmen', 'Mendoza', NULL, '1948-06-15', '2026-01-30 17:43:25'),
(8, 'Lim', 'David', 'Chua', NULL, '1988-05-20', 'Male', 'Others', 'Annulled', '321-654-987-000', 'Filipino-Chinese', 'Buddhist', 'Mandaluyong City', 'Metro Manila', 'Philippines', 0, '321 Pine St, Mandaluyong City, Metro Manila, Philippines', '09451234567', 'david.lim@email.com', '+63-2-8987654', 'Lim', 'Wong', 'Chua', 'Sr.', '1958-09-12', 'Chua', 'Lily', 'Tan', NULL, '1962-03-08', '2026-01-30 17:43:25'),
(9, 'Torres', 'Sarah', 'Jane', NULL, '1998-09-14', 'Female', 'Single', NULL, NULL, 'Filipino', NULL, 'Pasig City', 'Metro Manila', 'Philippines', 1, 'Pasig City, Metro Manila, Philippines', '09567890123', 'sarah.torres@email.com', NULL, 'Torres', 'Michael', 'Johnson', NULL, '1970-12-05', 'Johnson', 'Susan', 'Reyes', NULL, '1972-04-18', '2026-01-30 17:43:25'),
(10, 'Ramos', 'Elena', 'Villanueva', NULL, '1982-04-03', 'Female', 'Married', NULL, '654-321-987-000', 'Filipino', 'Born Again Christian', 'Taguig City', 'Metro Manila', 'Philippines', 0, '654 BGC Ave, Taguig City, Metro Manila, Philippines', '09198765432', 'elena.ramos@email.com', '+63-2-8555123', 'Villanueva', 'Fernando', 'Garcia', NULL, '1952-11-22', 'Garcia', 'Luzviminda', 'Santos', NULL, '1955-07-14', '2026-01-30 17:43:25'),
(11, 'Mendoza', 'Alberto', 'Santos', NULL, '1955-12-12', 'Male', 'Widowed', NULL, '987-654-321-000', 'Filipino', 'Roman Catholic', 'Caloocan City', 'Metro Manila', 'Philippines', 0, '987 Kalye St, Caloocan City, Metro Manila, Philippines', '09223456789', 'alberto.mendoza@email.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-30 17:43:25'),
(12, 'Tan', 'Michelle', 'Wong', NULL, '1990-06-18', 'Female', 'Single', NULL, NULL, 'Filipino', 'Roman Catholic', 'Los Angeles', 'California', 'USA', 0, '45 Visayan St, Cebu City, Cebu, Philippines', '09678901234', 'michelle.tan@email.com', NULL, 'Tan', 'Richard', 'Wong', NULL, '1960-01-15', 'Wong', 'Jennifer', 'Lee', NULL, '1963-04-22', '2026-01-30 17:43:25'),
(13, 'Aguilar', 'Fernando', 'Dela Rosa', 'Sr.', '1945-01-01', 'Male', 'Widowed', NULL, '111-222-333-000', 'Filipino', 'Roman Catholic', 'Manila City', 'Metro Manila', 'Philippines', 0, '111 Rizal Ave, Manila City, Metro Manila, Philippines', '09111111111', 'fernando.aguilar@email.com', '+63-2-7111111', 'Aguilar', 'Jose', 'Dela Rosa', NULL, '1915-12-12', 'Dela Rosa', 'Consuelo', 'Garcia', NULL, '1920-05-20', '2026-01-30 17:43:25');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `dependents`
--
ALTER TABLE `dependents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `personal_data_id` (`personal_data_id`);

--
-- Indexes for table `personal_data`
--
ALTER TABLE `personal_data`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `dependents`
--
ALTER TABLE `dependents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `personal_data`
--
ALTER TABLE `personal_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `dependents`
--
ALTER TABLE `dependents`
  ADD CONSTRAINT `dependents_ibfk_1` FOREIGN KEY (`personal_data_id`) REFERENCES `personal_data` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
