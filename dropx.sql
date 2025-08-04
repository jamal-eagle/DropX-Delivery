-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 09, 2025 at 02:39 PM
-- Server version: 10.4.27-MariaDB
-- PHP Version: 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dropx`
--

-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fullname`, `phone`, `password`, `location_text`, `latitude`, `longitude`, `user_type`, `is_active`, `fcm_token`, `remember_token`, `deleted_at`, `created_at`, `updated_at`, `is_verified`) VALUES
(1, 'Meghan Botsford', '0940310874', '$2y$12$.WCW/Tqi89lhsie8CMqavOuGkMjJGluTchx7sAkP0e3r1FaruCTFq', NULL, NULL, NULL, 'customer', 1, 'HbPDDdBs07jEvD6FMhTH', NULL, NULL, '2025-05-28 15:21:19', '2025-05-28 15:21:19', 0),
(2, 'Christop Thompson', '0903252365', '$2y$12$J74Ys5pDfFg23HLYHuMtUeKlOwUBl8.2e95oSNjTo0jNrrmu47bX.', NULL, NULL, NULL, 'customer', 1, 'WdMVoWkRj47W7qzwTEgi', NULL, NULL, '2025-05-28 15:21:19', '2025-05-28 15:21:19', 0),
(3, 'Madie Wintheiser', '0944256150', '$2y$12$RcEXbKes6gs/pVGsY.DNbOyzheTXbG9jTgh.UdvyKqt6kDaZ60Mr2', NULL, NULL, NULL, 'customer', 1, '0NVroJDz8p75ZrqzKsJ4', NULL, NULL, '2025-05-28 15:21:20', '2025-05-28 15:21:20', 0),
(4, 'Kendrick Terry', '0982504368', '$2y$12$zRXSXZkOmWX0v00ZWlE3k.oqg9Ud/LQzDW9NQsQYJotmnKBBL52WW', NULL, NULL, NULL, 'customer', 1, 'ouaLrohdriz05dDlR33f', NULL, NULL, '2025-05-28 15:21:20', '2025-05-28 15:21:20', 0),
(5, 'Dr. Merritt Grant', '0997312775', '$2y$12$XTwHIsIDVEqEENqSVBNIWuyMCaRoA7GQ0jBSnJjPHkmNHdR58sO2e', NULL, NULL, NULL, 'customer', 1, 'lNybKXrbwMl7w7iLqN2G', NULL, NULL, '2025-05-28 15:21:20', '2025-05-28 15:21:20', 0),
(6, 'Harmony Maggio', '0900782983', '$2y$12$i3PjOiZHzZNrGsKg48UyYOAoPb4ZVFp/.4jEwZN0T84pA96Fl/R7i', NULL, NULL, NULL, 'customer', 1, 'MlAmmAclCuv14jZYaJ7F', NULL, NULL, '2025-05-28 15:21:20', '2025-05-28 15:21:20', 0),
(7, 'Oran Mertz', '0969161167', '$2y$12$tnOXj/IJylIKtZWmEs5hYePO5bvn.CYwfPXvtZJ3vorDPcKLt4CEW', NULL, NULL, NULL, 'customer', 1, 'QMyxFFEV2H4pdrBvliOh', NULL, NULL, '2025-05-28 15:21:20', '2025-05-28 15:21:20', 0),
(8, 'Karley Koelpin', '0943674669', '$2y$12$Jtw/WO3sxAyH98UNFZwDz.NznFMb74lLlgWY70tyrL4AsuaB1Rxae', NULL, NULL, NULL, 'customer', 1, 'pc2vatg5Rc7gaPx1ZRhi', NULL, NULL, '2025-05-28 15:21:20', '2025-05-28 15:21:20', 0),
(9, 'Dr. Morton Kunze', '0939901627', '$2y$12$XnIUrlbDXk8DOcC9QMcMKOroEh/tFD/3fW9Lzg7.y/0M0lAHErna.', NULL, NULL, NULL, 'customer', 1, 'eyJU3Bso7GLDrFbQniiY', NULL, NULL, '2025-05-28 15:21:20', '2025-05-28 15:21:20', 0),
(10, 'Dr. Kathryn Auer Sr.', '0918123184', '$2y$12$TBMh2Io.5SryHpReg.pSuu7I/RX1UJmcIImh4eQ4ltTyYo/dUQ0U2', NULL, NULL, NULL, 'customer', 1, 'ieDnE20cyS78sZgPJroZ', NULL, NULL, '2025-05-28 15:21:20', '2025-05-28 15:21:20', 0),
(11, 'Mateo Lemke', '0973610687', '$2y$12$TfT5v5WmmS/je0m3/mdlCuFZmNGqdmeDcKSQ49ec9zp.hBbwGT7gK', NULL, '32.88888900', '36.04305600', 'restaurant', 1, 'W3anYuPFPl2n3ftu65aQ', NULL, NULL, '2025-05-28 15:21:24', '2025-05-28 15:21:24', 0),
(12, 'Mateo ', '0983705957', '$2y$12$Wfua19bPmOdbO3iZ4Pw0XemTVDvqAS93JO/3ibPjEW5ncquTDxQz2', NULL, NULL, NULL, 'restaurant', 1, 'pjBM9I79SQCLGFmFU0KZ', NULL, NULL, '2025-05-28 15:21:24', '2025-05-28 15:21:24', 0),
(13, 'Heather Pfannerstill', '0913545251', '$2y$12$8a9w8EoQ8pcX98AfGhqI3.r0AF3.J5ZSSTQeXzQySDHjye8FZc7ra', NULL, NULL, NULL, 'restaurant', 1, 'OjnqEpxyX6x70UFhKuy8', NULL, NULL, '2025-05-28 15:21:24', '2025-05-28 15:21:24', 0),
(14, 'Devon Kuvalis V', '0971001452', '$2y$12$NiwMF29FUzO2L3MwFPKKDOQftH/41zpoH8NZd9e27Cf5xQPbexCUS', NULL, NULL, NULL, 'restaurant', 1, 'OEevJifJpnhaIaQ2rK30', NULL, NULL, '2025-05-28 15:21:25', '2025-05-28 15:21:25', 0),
(15, 'Angela Shields Sr.', '0973195366', '$2y$12$CEr6wuH.YC5qDLrWzP/8wOEz2DS/Ey/P9N1aa/ef1HjZ9GISySYQS', NULL, NULL, NULL, 'restaurant', 1, 'zcBiDkfaRTSuOX5HKZ0h', NULL, NULL, '2025-05-28 15:21:25', '2025-05-28 15:21:25', 0),
(16, 'Isai O\'Hara', '0922289380', '$2y$12$dPMaQBUSp98oIuPwWJJ9oub0UPSTZOYp30XfxjlnVMf4GlZLDgn4.', NULL, NULL, NULL, 'driver', 1, 'PlxLV5gO6k1ueEQtC09k', NULL, NULL, '2025-05-28 15:21:27', '2025-05-28 15:21:27', 0),
(17, 'Petra Langosh II', '0904564756', '$2y$12$ZTnorGFpHuXYv.Cyfd8eDO277HSedZiDnsnxkLqtoHvhtXGkxsZx2', NULL, NULL, NULL, 'driver', 1, 'Z8vhTGnHK2CaDOachHSy', NULL, NULL, '2025-05-28 15:21:27', '2025-05-28 15:21:27', 0),
(18, 'Sandra Grimes', '0995396544', '$2y$12$7ILJ7mA0f3qlxnizQkXcqeb68qqcSPn/6s0O9KxhbyNuxpGO0HF5u', NULL, NULL, NULL, 'driver', 1, 'NHQmM7AAoR5c4hQR9AR7', NULL, NULL, '2025-05-28 15:21:27', '2025-05-28 15:21:27', 0),
(19, 'Sincere McClure', '0924676302', '$2y$12$VV0O8j76qwDZXVOl2tWNoePQfM9uAtBY/PPeY0yaU.RvSe29QCl96', NULL, NULL, NULL, 'driver', 1, 'guAB03m8GbFHRDk5rhLJ', NULL, NULL, '2025-05-28 15:21:28', '2025-05-28 15:21:28', 0),
(20, 'Miss Emely Armstrong DDS', '0960128923', '$2y$12$gKp8fs4QAo0NhCkle8Qoj.4m8dRXuZFQ9oJ8vfefY2vYKpNZN0DLq', NULL, NULL, NULL, 'driver', 1, 'pRCz6zisOXHxBHBfJTQ7', NULL, NULL, '2025-05-28 15:21:28', '2025-05-28 15:21:28', 0),
(21, 'Super Admin', '0999999999', '$2y$12$ipiSJBy5Kwv3ZCGPQsjD8O/Ptho6HW5R7t0BY15ICKJfLppKxDGgK', NULL, NULL, NULL, 'admin', 1, '1U4bXeUNBHE5z1d9SqfK', NULL, NULL, '2025-05-28 15:21:29', '2025-05-28 15:21:29', 0),
(22, 'baraa', '0995951652', '$2y$12$udJiMX8sVlkD8zucnx1CZ.85b3ATZPQNlh0qiYgEGy9xQKjD0Bfyu', NULL, NULL, NULL, 'customer', 1, NULL, NULL, NULL, '2025-05-28 15:22:23', '2025-05-28 15:22:23', 1),
(24, 'راما الشام', '0995951653', '$2y$12$7nGt6hPq3V..a783FcG2ieln6FWm4DdHfbH2J.pp61.6M18bHc2IG', NULL, '32.89360000', '36.02850000', 'restaurant', 1, NULL, NULL, NULL, '2025-05-29 16:10:43', '2025-05-29 16:10:43', 1),
(25, 'مهران الجهماني', '0995951654', '$2y$12$GweRB2Uee6dSB7pOMfTShe.R8QemDzgpIwJl7w3bD4e5CIcXMA6Rq', NULL, NULL, NULL, 'driver', 1, NULL, NULL, NULL, '2025-05-30 14:33:12', '2025-05-30 14:33:12', 1),
(26, 'جمال المرشد', '0995951655', '123123123', NULL, NULL, NULL, 'driver', 1, NULL, NULL, NULL, NULL, NULL, 1),
(27, 'براء الدرساني ', '0995951656', '123123123', NULL, NULL, NULL, 'driver', 1, NULL, NULL, NULL, NULL, NULL, 0),
(28, 'يحيى المرشد', '0995951657', '123123123', NULL, NULL, NULL, 'customer', 1, NULL, NULL, NULL, NULL, NULL, 0);

--
-- Dumping data for table `areas`
--

INSERT INTO `areas` (`id`, `city`, `neighborhood`, `created_at`, `updated_at`) VALUES
(1, 'Lake Rodgerview', 'Feeney Plaza', '2025-05-28 15:21:14', '2025-05-28 15:21:14'),
(2, 'East Serenachester', 'Kristoffer Rapids', '2025-05-28 15:21:15', '2025-05-28 15:21:15'),
(3, 'North Charity', 'Price Hollow', '2025-05-28 15:21:15', '2025-05-28 15:21:15'),
(4, 'Mooreshire', 'Turcotte Manors', '2025-05-28 15:21:15', '2025-05-28 15:21:15'),
(5, 'Port Jaquelinton', 'Wilbert Glen', '2025-05-28 15:21:15', '2025-05-28 15:21:15'),
(6, 'East Coty', 'Zemlak Knolls', '2025-05-28 15:21:16', '2025-05-28 15:21:16'),
(7, 'Rohanton', 'Willms Roads', '2025-05-28 15:21:16', '2025-05-28 15:21:16'),
(8, 'Lake Chaim', 'Dameon Shoals', '2025-05-28 15:21:16', '2025-05-28 15:21:16'),
(9, 'Spinkabury', 'Nigel Grove', '2025-05-28 15:21:16', '2025-05-28 15:21:16'),
(10, 'Carterhaven', 'Patrick Stream', '2025-05-28 15:21:16', '2025-05-28 15:21:16'),
(11, 'تسيل', 'iskan', '2025-05-28 15:22:23', '2025-05-28 15:22:23'),
(13, 'nawa', NULL, '2025-05-29 16:10:43', '2025-05-29 16:10:43'),
(14, 'jasm', NULL, '2025-06-01 08:30:31', '2025-06-01 08:30:31');

--
-- Dumping data for table `area_user`
--

INSERT INTO `area_user` (`id`, `user_id`, `area_id`, `created_at`, `updated_at`) VALUES
(1, 1, 10, '2025-05-28 15:21:21', '2025-05-28 15:21:21'),
(2, 2, 9, '2025-05-28 15:21:21', '2025-05-28 15:21:21'),
(3, 3, 3, '2025-05-28 15:21:21', '2025-05-28 15:21:21'),
(4, 4, 5, '2025-05-28 15:21:22', '2025-05-28 15:21:22'),
(5, 5, 4, '2025-05-28 15:21:22', '2025-05-28 15:21:22'),
(6, 6, 2, '2025-05-28 15:21:22', '2025-05-28 15:21:22'),
(7, 7, 7, '2025-05-28 15:21:22', '2025-05-28 15:21:22'),
(8, 8, 7, '2025-05-28 15:21:22', '2025-05-28 15:21:22'),
(9, 9, 1, '2025-05-28 15:21:23', '2025-05-28 15:21:23'),
(10, 10, 10, '2025-05-28 15:21:23', '2025-05-28 15:21:23'),
(11, 11, 6, '2025-05-28 15:21:25', '2025-05-28 15:21:25'),
(12, 11, 9, '2025-05-28 15:21:25', '2025-05-28 15:21:25'),
(13, 12, 2, '2025-05-28 15:21:25', '2025-05-28 15:21:25'),
(14, 13, 4, '2025-05-28 15:21:25', '2025-05-28 15:21:25'),
(15, 13, 8, '2025-05-28 15:21:25', '2025-05-28 15:21:25'),
(16, 14, 9, '2025-05-28 15:21:25', '2025-05-28 15:21:25'),
(17, 15, 8, '2025-05-28 15:21:25', '2025-05-28 15:21:25'),
(18, 16, 6, '2025-05-28 15:21:28', '2025-05-28 15:21:28'),
(19, 17, 10, '2025-05-28 15:21:28', '2025-05-28 15:21:28'),
(20, 18, 2, '2025-05-28 15:21:28', '2025-05-28 15:21:28'),
(21, 19, 2, '2025-05-28 15:21:29', '2025-05-28 15:21:29'),
(22, 20, 8, '2025-05-28 15:21:29', '2025-05-28 15:21:29'),
(23, 21, 8, '2025-05-28 15:21:29', '2025-05-28 15:21:29'),
(24, 22, 11, '2025-05-28 15:22:23', '2025-05-28 15:22:23'),
(25, 12, 9, NULL, NULL),
(27, 24, 13, '2025-05-29 16:10:43', '2025-05-29 16:10:43'),
(28, 25, 13, '2025-05-30 14:33:13', '2025-05-30 14:33:13'),
(29, 26, 13, NULL, NULL),
(30, 27, 14, NULL, NULL),
(31, 28, 14, NULL, NULL);

--
-- Dumping data for table `restaurants`
--

INSERT INTO `restaurants` (`id`, `user_id`, `image`, `description`, `working_hours_start`, `working_hours_end`, `status`, `created_at`, `updated_at`) VALUES
(1, 11, 'https://via.placeholder.com/640x480.png/000044?text=exercitationem', 'Facilis deleniti deleniti beatae sed aut.', '10:00:00', '22:00:00', 'open', '2025-05-28 15:21:30', '2025-05-28 15:21:30'),
(2, 12, 'https://via.placeholder.com/640x480.png/005533?text=voluptas', 'Distinctio nesciunt ut illum.', '10:00:00', '22:00:00', 'open', '2025-05-28 15:21:30', '2025-05-28 15:21:30'),
(3, 13, 'https://via.placeholder.com/640x480.png/00aa66?text=quam', 'Ipsum blanditiis molestias modi laborum assumenda.', '10:00:00', '22:00:00', 'open', '2025-05-28 15:21:30', '2025-05-28 15:21:30'),
(4, 14, 'https://via.placeholder.com/640x480.png/00aa00?text=dignissimos', 'Eum asperiores dolorem et itaque amet ex.', '10:00:00', '22:00:00', 'open', '2025-05-28 15:21:31', '2025-05-28 15:21:31'),
(5, 15, 'https://via.placeholder.com/640x480.png/00ff11?text=asperiores', 'Fugiat enim debitis molestiae recusandae ducimus eveniet aut.', '10:00:00', '22:00:00', 'open', '2025-05-28 15:21:31', '2025-05-28 15:21:31'),
(6, 24, 'jamalalmrshed', 'افضل خدمة بأفضل الاسعار والذ النكهات', '18:00:00', '23:00:00', 'open', NULL, '2025-05-29 16:51:57');


--
-- Dumping data for table `category_restaurant`
--

INSERT INTO `category_restaurant` (`id`, `category_id`, `restaurant_id`, `created_at`, `updated_at`) VALUES
(1, 1, 6, NULL, NULL),
(2, 2, 6, NULL, NULL);

--
-- Dumping data for table `delivery_settings`
--

INSERT INTO `delivery_settings` (`id`, `price_per_km`, `minimum_delivery_fee`, `created_at`, `updated_at`) VALUES
(1, '50000.00', 10000, NULL, NULL);

--
-- Dumping data for table `drivers`
--

INSERT INTO `drivers` (`id`, `user_id`, `vehicle_type`, `vehicle_number`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 16, 'car', '564-SCH', 1, '2025-05-28 15:21:31', '2025-05-28 15:21:31'),
(2, 17, 'car', '674-ACU', 1, '2025-05-28 15:21:32', '2025-05-28 15:21:32'),
(3, 18, 'car', '304-XPE', 1, '2025-05-28 15:21:32', '2025-05-28 15:21:32'),
(4, 19, 'car', '897-HPP', 1, '2025-05-28 15:21:32', '2025-05-28 15:21:32'),
(5, 20, 'car', '213-RDP', 1, '2025-05-28 15:21:32', '2025-05-28 15:21:32'),
(6, 25, 'سيارة', '123456', 1, NULL, NULL),
(7, 26, 'سيارة', '123456', 1, NULL, NULL),
(8, 27, 'سيارة', '12345', 1, NULL, NULL),
(9, 28, 'ماتور', '123456', 1, NULL, NULL);

--
-- Dumping data for table `driver_area_turns`
--

INSERT INTO `driver_area_turns` (`id`, `driver_id`, `turn_order`, `is_next`, `is_active`, `turn_assigned_at`, `last_assigned_at`, `created_at`, `updated_at`) VALUES
(1, 6, 1, 1, 1, '2025-06-01 11:59:16', NULL, NULL, '2025-06-01 11:59:16'),
(2, 7, 2, 0, 1, NULL, NULL, NULL, '2025-06-01 11:59:16'),
(3, 8, 1, 1, 1, '2025-06-01 08:43:40', NULL, NULL, '2025-06-01 08:43:40'),
(4, 9, 2, 0, 1, NULL, NULL, NULL, '2025-06-01 08:43:40');

--
-- Dumping data for table `driver_working_hours`
--

INSERT INTO `driver_working_hours` (`id`, `driver_id`, `day_of_week`, `start_time`, `end_time`, `created_at`, `updated_at`) VALUES
(1, 6, 'saturday', '08:00:00', '23:00:00', NULL, NULL),
(2, 6, 'Sunday', '08:00:00', '18:00:00', NULL, NULL),
(3, 7, 'Sunday', '08:00:00', '20:00:00', NULL, NULL),
(4, 8, 'Sunday', '08:00:00', '18:00:00', NULL, NULL),
(5, 9, 'Sunday', '08:00:00', '20:00:00', NULL, NULL);

--
-- Dumping data for table `meals`
--

INSERT INTO `meals` (`id`, `category_id`, `restaurant_id`, `name`, `original_price`, `is_available`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'dolores', '36.40', 1, '2025-05-28 15:21:35', '2025-05-28 15:21:35'),
(2, 1, 1, 'aliquid', '49.58', 1, '2025-05-28 15:21:35', '2025-05-28 15:21:35'),
(3, 1, 1, 'iste', '48.88', 1, '2025-05-28 15:21:36', '2025-05-28 15:21:36'),
(4, 1, 1, 'veritatis', '13.95', 1, '2025-05-28 15:21:36', '2025-05-28 15:21:36'),
(5, 1, 1, 'aliquam', '43.21', 1, '2025-05-28 15:21:36', '2025-05-28 15:21:36'),
(6, 1, 2, 'nobis', '42.55', 1, '2025-05-28 15:21:36', '2025-05-28 15:21:36'),
(7, 1, 2, 'illo', '27.37', 1, '2025-05-28 15:21:36', '2025-05-28 15:21:36'),
(8, 1, 2, 'illo', '18.96', 1, '2025-05-28 15:21:36', '2025-05-28 15:21:36'),
(9, 1, 2, 'suscipit', '11.78', 1, '2025-05-28 15:21:37', '2025-05-28 15:21:37'),
(10, 1, 2, 'eveniet', '15.65', 1, '2025-05-28 15:21:37', '2025-05-28 15:21:37'),
(11, 1, 3, 'tempora', '13.12', 1, '2025-05-28 15:21:37', '2025-05-28 15:21:37'),
(12, 1, 3, 'aut', '14.19', 1, '2025-05-28 15:21:37', '2025-05-28	 15:21:37'),
(13, 1, 3, 'et', '27.06', 1, '2025-05-28 15:21:37', '2025-05-28 15:21:37'),
(14, 1, 3, 'ut', '42.52', 1, '2025-05-28 15:21:37', '2025-05-28 15:21:37'),
(15, 1, 3, 'quaerat', '33.03', 1, '2025-05-28 15:21:37', '2025-05-28 15:21:37'),
(16, 1, 4, 'perferendis', '11.97', 1, '2025-05-28 15:21:37', '2025-05-28 15:21:37'),
(17, 1, 4, 'doloribus', '16.84', 1, '2025-05-28 15:21:38', '2025-05-28 15:21:38'),
(18, 1, 4, 'consequatur', '33.43', 1, '2025-05-28 15:21:38', '2025-05-28 15:21:38'),
(19, 1, 4, 'assumenda', '29.64', 1, '2025-05-28 15:21:38', '2025-05-28 15:21:38'),
(20, 1, 4, 'qui', '44.18', 1, '2025-05-28 15:21:38', '2025-05-28 15:21:38'),
(21, 1, 5, 'accusantium', '46.44', 1, '2025-05-28 15:21:38', '2025-05-28 15:21:38'),
(22, 1, 5, 'inventore', '41.61', 1, '2025-05-28 15:21:38', '2025-05-28 15:21:38'),
(23, 1, 5, 'non', '6.11', 1, '2025-05-28 15:21:39', '2025-05-28 15:21:39'),
(24, 1, 5, 'reprehenderit', '23.90', 1, '2025-05-28 15:21:39', '2025-05-28 15:21:39'),
(25, 1, 5, 'recusandae', '18.88', 1, '2025-05-28 15:21:39', '2025-05-28 15:21:39'),
(26, 1, 6, 'شاورما', '20000.00', 1, '2025-05-04 19:21:13', '2025-06-06 11:58:24'),
(27, 1, 6, 'شاورما دبل', '20000.00', 1, '2025-05-04 19:20:46', '2024-05-06 16:04:22');

--
-- Dumping data for table `image_for_meals`
--

INSERT INTO `image_for_meals` (`id`, `meal_id`, `image`, `description`, `created_at`, `updated_at`) VALUES
(1, 1, 'jamlZNcbv zbnm', 'cvhbjnkml,', NULL, NULL);



--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`migration`, `batch`) VALUES
('2025_05_26_113613_create_delivery_settings_table', 3),
('2014_10_12_000000_create_users_table', 4),
( '2014_10_12_100000_create_password_reset_tokens_table', 4),
( '2019_08_19_000000_create_failed_jobs_table', 4),
( '2019_12_14_000001_create_personal_access_tokens_table', 4),
( '2025_04_08_111905_create_restaurants_table', 4),
( '2025_04_08_112625_create_categories_table', 4),
('2025_04_08_112951_create_meals_table', 4),
('2025_04_08_113318_create_image_for_meals_table', 4),
('2025_04_08_113542_create_promo_codes_table', 4),
('2025_04_08_121210_create_drivers_table', 4),
('2025_04_08_124241_create_orders_table', 4),
('2025_04_08_124334_create_order_items_table', 4),
('2025_04_14_130809_create_areas_table', 4),
('2025_04_14_130929_create_area_user_table', 4),
('2025_04_19_095005_create_advertisements_table', 4),
('2025_05_05_174720_create_driver_area_turns_table', 4),
('2025_05_05_174903_create_driver_order_rejections_table', 4),
('2025_05_07_190008_add_turn_assigned_at_to_driver_area_turns_table', 4),
('2025_05_17_091833_create_restaurant_commissions_table', 4),
('2025_05_17_093607_create_restaurant_daily_reports_table', 4),
('2025_05_22_180714_add_is_verified_to_users_table', 4),
('2025_05_25_101346_create_user_promo_codes_table', 4),
('2025_05_26_104011_add_coordinates_to_orders_table', 4),
('2025_05_26_113939_create_delivery_settings_table', 4),
('2025_05_27_101523_create_restaurant_monthly_reports_table', 4),
('2025_05_30_141302_create_driver_area_turns_table', 5),
('2025_05_30_143619_create_driver_area_turns_table', 6),
('2025_05_31_102447_create_driver_working_hours_table', 7),
('2025_06_09_143439_create_category_restaurant_table', 8);

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `restaurant_id`, `driver_id`, `status`, `is_accepted`, `total_price`, `delivery_address`, `latitude`, `longitude`, `notes`, `delivery_fee`, `barcode`, `created_at`, `updated_at`) VALUES
(1, 22, 1, NULL, 'delivered', 1, '72.80', NULL, '32.89320200', '36.03941000', 'جرب التوصيل السريع', '29406.00', 'barcodes/order-ac2fbb13-6109-44d3-8d53-c21105e33a7c.png', '2025-05-28 17:19:04', '2025-05-28 17:19:04'),
(2, 22, 1, NULL, 'delivered', 1, '72.80', NULL, '32.89360000', '36.02850000', 'جرب التوصيل السريع', '72828.00', 'barcodes/order-8259fe8d-eb9e-4b3c-9c0a-5a83842b249a.png', '2025-05-28 17:23:43', '2025-05-28 17:23:43'),
(3, 22, 6, 6, 'preparing', 1, '50000.00', NULL, '36.04305600', '32.88888900', 'جرب التوصيل السريع', '22661933.00', 'barcodes/order-346465dc-c438-4c9e-8155-2b3aa6c0ac73.png', '2025-05-29 16:25:58', '2025-06-01 11:59:16'),
(4, 22, 6, 6, 'on_delivery', 1, '28000.00', NULL, '36.04305600', '32.88888900', 'جرب التوصيل السريع', '22661933.00', 'barcodes/order-cfffd12d-0388-4556-b9f2-fef2397599f7.png', '2025-05-29 16:36:53', '2025-06-01 11:37:00'),
(5, 22, 6, NULL, 'pending', 0, '40000.00', NULL, '36.04305600', '32.88888900', 'جرب التوصيل السريع', '22661933.00', 'barcodes/order-8f517217-e656-4a10-b719-e7b9b9385861.png', '2025-06-06 12:13:40', '2025-06-06 12:13:40'),
(6, 22, 6, NULL, 'pending', 0, '32000.00', NULL, '36.04305600', '32.88888900', 'جرب التوصيل السريع', '22661933.00', 'barcodes/order-0bb2766a-adda-4313-9eb0-d687b60b59c1.png', '2025-06-06 12:17:49', '2025-06-06 12:17:49');

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `meal_id`, `quantity`, `price`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 2, '36.40', '2025-05-28 17:19:04', '2025-05-28 17:19:04'),
(2, 2, 1, 2, '36.40', '2025-05-28 17:23:43', '2025-05-28 17:23:43'),
(3, 1, 2, 2, '20000.00', NULL, NULL),
(4, 3, 26, 2, '15000.00', '2025-05-29 16:25:58', '2025-05-29 16:25:58'),
(5, 3, 27, 1, '20000.00', '2025-05-29 16:25:58', '2025-05-29 16:25:58'),
(6, 4, 26, 1, '15000.00', '2025-05-29 16:36:53', '2025-05-29 16:36:53'),
(7, 4, 27, 1, '20000.00', '2025-05-29 16:36:53', '2025-05-29 16:36:53'),
(8, 5, 26, 1, '20000.00', '2025-06-06 12:13:40', '2025-06-06 12:13:40'),
(9, 5, 27, 1, '20000.00', '2025-06-06 12:13:40', '2025-06-06 12:13:40'),
(10, 6, 26, 1, '20000.00', '2025-06-06 12:17:49', '2025-06-06 12:17:49'),
(11, 6, 27, 1, '20000.00', '2025-06-06 12:17:49', '2025-06-06 12:17:49');

--
-- Dumping data for table `personal_access_tokens`
--

INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, 'App\\Models\\User', 22, 'auth_token', '5dd33adebe0b10c0b4bfe071a4258ef882d08b290fd13678d61239145ea270ce', '[\"*\"]', '2025-06-09 12:20:37', NULL, '2025-05-28 15:25:31', '2025-06-09 12:20:37'),
(2, 'App\\Models\\User', 24, 'auth_token', '4ccc555e6c61b1651861c57ec9c47e4d9499b50f912da9cdf94f10e86553a44e', '[\"*\"]', '2025-06-01 11:59:16', NULL, '2025-05-29 16:11:36', '2025-06-01 11:59:16'),
(3, 'App\\Models\\User', 25, 'auth_token', '85fc8f465f2acc5f2a10d75e0e2e13465c653957a3254cf86506a5219d629c50', '[\"*\"]', '2025-06-01 17:53:09', NULL, '2025-05-30 14:34:30', '2025-06-01 17:53:09'),
(4, 'App\\Models\\User', 24, 'auth_token', '192819f179c6655c7b61699e2b8412070938f1f40fa9e388ec7844891573ccf5', '[\"*\"]', '2025-06-06 11:58:23', NULL, '2025-06-06 11:35:29', '2025-06-06 11:58:23');

--
-- Dumping data for table `promo_codes`
--

INSERT INTO `promo_codes` (`id`, `code`, `discount_type`, `discount_value`, `min_order_value`, `max_uses`, `expiry_date`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'JAM12', 'percentage', '20.00', '0.00', 8, '2025-07-01 21:35:36', 1, NULL, NULL);


--
-- Dumping data for table `user_promo_codes`
--

INSERT INTO `user_promo_codes` (`id`, `user_id`, `order_id`, `promo_code_id`, `fcm_token`, `is_used`, `used_at`, `created_at`, `updated_at`) VALUES
(2, 22, 6, 1, NULL, 1, '2025-06-06 15:17:50', '2025-06-06 12:17:50', '2025-06-06 12:17:50');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
