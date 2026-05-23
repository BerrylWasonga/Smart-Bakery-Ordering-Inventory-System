-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 21, 2026 at 11:23 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bakery_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_type` enum('admin','user') NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `action` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_type`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 'user', 1, 'register', 'New user registered', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-17 16:43:38'),
(2, 'admin', 1, 'login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-17 16:55:49'),
(3, 'admin', 1, 'product_update', 'Updated product #1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-17 17:12:30'),
(4, 'admin', 1, 'product_update', 'Updated product #2', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-17 17:14:17'),
(5, 'admin', 1, 'product_update', 'Updated product #3', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-17 17:15:32'),
(6, 'admin', 1, 'product_update', 'Updated product #4', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-17 17:18:18'),
(7, 'admin', 1, 'product_update', 'Updated product #5', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-17 17:20:53'),
(8, 'admin', 1, 'product_update', 'Updated product #6', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-17 17:22:17'),
(9, 'admin', 1, 'product_update', 'Updated product #7', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-17 17:22:58'),
(10, 'admin', 1, 'product_update', 'Updated product #8', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-17 17:23:47'),
(11, 'admin', 1, 'product_update', 'Updated product #9', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-17 17:24:55'),
(12, 'admin', 1, 'product_update', 'Updated product #10', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-17 17:25:55'),
(13, 'admin', 1, 'product_update', 'Updated product #11', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-17 17:26:55'),
(14, 'admin', 1, 'product_update', 'Updated product #12', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-17 17:28:12'),
(15, 'admin', 1, 'settings_update', 'Site settings updated', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-17 17:36:38'),
(16, 'admin', 1, 'settings_update', 'Site settings updated', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-17 17:37:13'),
(17, 'admin', 1, 'settings_update', 'Site settings updated', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-17 17:39:45'),
(18, 'admin', 1, 'settings_update', 'Site settings updated', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-17 18:51:24'),
(19, 'admin', 1, 'settings_update', 'Site settings updated', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-17 18:51:27'),
(20, 'admin', 1, 'settings_update', 'Site settings updated', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-17 18:52:05'),
(21, 'admin', 1, 'settings_update', 'Site settings updated', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-17 18:52:06'),
(22, 'admin', 1, 'settings_update', 'Site settings updated', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-17 18:52:08'),
(23, 'admin', 1, 'settings_update', 'Site settings updated', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-17 18:52:40'),
(24, 'admin', 1, 'settings_update', 'Site settings updated', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-17 18:53:34'),
(25, 'admin', 1, 'settings_update', 'Site settings updated', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-17 18:53:45'),
(26, 'admin', 1, 'settings_update', 'Site settings updated', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-17 18:53:53'),
(27, 'admin', 1, 'settings_update', 'Site settings updated', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-17 18:57:46'),
(28, 'admin', 1, 'settings_update', 'Site settings updated', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-17 18:57:49'),
(29, 'admin', 1, 'settings_update', 'Site settings updated', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-17 19:01:42'),
(30, 'admin', 1, 'settings_update', 'Site settings updated', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-17 19:02:29'),
(31, 'admin', 1, 'product_create', 'Created product Apple Pie', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 08:51:10'),
(32, 'admin', 1, 'product_update', 'Updated product #13', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 08:53:06'),
(33, 'admin', 1, 'product_create', 'Created product Meat pie', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 08:57:39'),
(34, 'admin', 1, 'settings_update', 'Site settings updated', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 09:03:41'),
(35, 'admin', 1, 'settings_update', 'Site settings updated', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 09:04:46'),
(36, 'admin', 1, 'login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-20 16:09:32'),
(37, 'admin', 1, 'product_update', 'Updated product #14', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-20 16:10:30'),
(38, 'admin', 1, 'product_update', 'Updated product #4', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-20 16:10:40'),
(39, 'admin', 1, 'login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-20 20:03:12'),
(40, 'admin', 1, 'login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-20 20:49:41'),
(41, 'admin', 1, 'logout', 'Admin logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-20 20:50:57'),
(42, 'admin', 1, 'login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-20 20:51:41'),
(43, 'admin', 1, 'settings_update', 'Site settings updated', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-20 20:56:23'),
(44, 'admin', 1, 'settings_update', 'Site settings updated', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-20 20:56:32'),
(45, 'user', 2, 'register', 'New user registered', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-20 20:58:09'),
(46, 'user', 2, 'order_placed', 'Order ORD-20260520-3A7A51 placed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-20 20:59:15'),
(47, 'admin', 1, 'login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-20 20:59:56'),
(48, 'admin', 1, 'order_status_update', 'Order #1 → delivered', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-20 21:00:29'),
(49, 'user', 2, 'login', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-20 21:01:26'),
(50, 'admin', 1, 'login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-20 21:13:09'),
(51, 'admin', 1, 'settings_update', 'Site settings updated', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-20 21:13:38'),
(52, 'user', 2, 'login', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-20 21:14:29'),
(53, 'user', 2, 'order_placed', 'Order ORD-20260521-AC2DCE placed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-20 21:15:54'),
(54, 'admin', 1, 'login', 'Admin logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-20 21:24:07'),
(55, 'admin', 1, 'order_status_update', 'Order #2 → delivered', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-20 21:31:42'),
(56, 'user', 2, 'login', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-20 21:31:57'),
(57, 'user', 2, 'login', 'User logged in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-20 21:34:32');

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('super_admin','admin','manager') DEFAULT 'admin',
  `avatar` varchar(255) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `name`, `email`, `password`, `role`, `avatar`, `last_login`, `status`, `created_at`) VALUES
(1, 'Super Admin', 'admin@bakery.com', '$2y$10$GQevaGlZeDBxaF3eHzuEdeNQgCs0LJANHWIZHf/fuomZz71KboUUO', 'super_admin', NULL, '2026-05-21 00:24:07', 1, '2026-05-17 16:18:04');

-- --------------------------------------------------------

--
-- Table structure for table `banners`
--

CREATE TABLE `banners` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(200) NOT NULL,
  `subtitle` varchar(300) DEFAULT NULL,
  `image` varchar(255) NOT NULL,
  `link_url` varchar(255) DEFAULT NULL,
  `button_text` varchar(50) DEFAULT NULL,
  `position` enum('hero','promo','sidebar') DEFAULT 'hero',
  `sort_order` int(11) DEFAULT 0,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `banners`
--

INSERT INTO `banners` (`id`, `title`, `subtitle`, `image`, `link_url`, `button_text`, `position`, `sort_order`, `status`, `created_at`) VALUES
(4, '', '', 'banners/6a0a12600b0f7_1779044960.jpg', '', '', 'hero', 0, 1, '2026-05-17 19:09:20'),
(5, '', '', 'banners/6a0a12dee91c8_1779045086.jpg', '', '', 'promo', 1, 1, '2026-05-17 19:11:26'),
(6, '', '', 'banners/6a0a12ed7bd63_1779045101.jpg', '', '', 'sidebar', 2, 1, '2026-05-17 19:11:41');

-- --------------------------------------------------------

--
-- Table structure for table `blog_posts`
--

CREATE TABLE `blog_posts` (
  `id` int(10) UNSIGNED NOT NULL,
  `admin_id` int(10) UNSIGNED NOT NULL,
  `category` varchar(100) DEFAULT 'General',
  `title` varchar(255) NOT NULL,
  `slug` varchar(280) NOT NULL,
  `excerpt` varchar(400) DEFAULT NULL,
  `body` longtext NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `tags` varchar(500) DEFAULT NULL,
  `meta_title` varchar(200) DEFAULT NULL,
  `meta_description` varchar(300) DEFAULT NULL,
  `views` int(11) DEFAULT 0,
  `status` enum('draft','published') DEFAULT 'draft',
  `published_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `session_id` varchar(100) DEFAULT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `product_variant_id` int(10) UNSIGNED DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `session_id`, `product_id`, `product_variant_id`, `quantity`, `added_at`) VALUES
(2, NULL, 'je53lldududsedsm940hkeimq4', 3, NULL, 1, '2026-05-20 20:56:51'),
(4, NULL, '2h6nbh9gmcfpmbatib21cnvali', 2, NULL, 2, '2026-05-20 21:09:08'),
(7, NULL, 'cc77ee7s48n0hroqkfgn2p3c89', 6, NULL, 7, '2026-05-20 21:34:19');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `parent_id` int(10) UNSIGNED DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `image`, `parent_id`, `sort_order`, `status`, `created_at`) VALUES
(1, 'Cakes', 'cakes', 'Beautiful custom and classic cakes', NULL, NULL, 1, 1, '2026-05-17 16:18:04'),
(2, 'Bread', 'bread', 'Freshly baked artisan breads', NULL, NULL, 2, 1, '2026-05-17 16:18:04'),
(3, 'Pastries', 'pastries', 'Buttery croissants, danishes and more', NULL, NULL, 3, 1, '2026-05-17 16:18:04'),
(4, 'Cookies', 'cookies', 'Handcrafted cookies and biscuits', NULL, NULL, 4, 1, '2026-05-17 16:18:04'),
(5, 'Cupcakes', 'cupcakes', 'Delightful individual cupcakes', NULL, NULL, 5, 1, '2026-05-17 16:18:04'),
(6, 'Donuts', 'donuts', 'Classic and gourmet donuts', NULL, NULL, 6, 1, '2026-05-17 16:18:04'),
(7, 'Pies & Tarts', 'pies-tarts', 'Sweet and savory pies', NULL, NULL, 7, 1, '2026-05-17 16:18:04'),
(8, 'Beverages', 'beverages', 'Hot and cold drinks', NULL, NULL, 8, 1, '2026-05-17 16:18:04'),
(9, 'Pies &amp; Tarts', 'pies-amp-tarts', 'Sweet and savory baked dishes filled with fruits, cream, meat, or flavored fillings.', '', NULL, 9, 1, '2026-05-19 08:45:22');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `subject` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `replied_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
--

CREATE TABLE `coupons` (
  `id` int(10) UNSIGNED NOT NULL,
  `code` varchar(50) NOT NULL,
  `type` enum('percentage','fixed') DEFAULT 'percentage',
  `value` decimal(10,2) NOT NULL,
  `min_order_amount` decimal(10,2) DEFAULT 0.00,
  `max_discount` decimal(10,2) DEFAULT NULL,
  `usage_limit` int(11) DEFAULT NULL,
  `used_count` int(11) DEFAULT 0,
  `per_user_limit` int(11) DEFAULT 1,
  `expires_at` datetime DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `coupons`
--

INSERT INTO `coupons` (`id`, `code`, `type`, `value`, `min_order_amount`, `max_discount`, `usage_limit`, `used_count`, `per_user_limit`, `expires_at`, `status`, `created_at`) VALUES
(1, 'WELCOME10', 'percentage', 10.00, 500.00, NULL, NULL, 0, 1, '2027-05-17 16:18:04', 1, '2026-05-17 16:18:04'),
(2, 'SAVE200', 'fixed', 200.00, 1500.00, NULL, NULL, 0, 1, '2026-11-17 16:18:04', 1, '2026-05-17 16:18:04'),
(3, 'BIRTHDAY20', 'percentage', 20.00, 1000.00, NULL, NULL, 0, 1, '2027-05-17 16:18:04', 1, '2026-05-17 16:18:04');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_logs`
--

CREATE TABLE `inventory_logs` (
  `id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `admin_id` int(10) UNSIGNED DEFAULT NULL,
  `action` enum('restock','sale','adjustment','return') NOT NULL,
  `quantity_change` int(11) NOT NULL,
  `quantity_before` int(11) NOT NULL,
  `quantity_after` int(11) NOT NULL,
  `note` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory_logs`
--

INSERT INTO `inventory_logs` (`id`, `product_id`, `admin_id`, `action`, `quantity_change`, `quantity_before`, `quantity_after`, `note`, `created_at`) VALUES
(1, 13, NULL, 'restock', 20, 0, 20, 'Initial stock', '2026-05-19 08:51:10'),
(2, 14, NULL, 'restock', 12, 0, 12, 'Initial stock', '2026-05-19 08:57:39'),
(3, 13, NULL, 'sale', -1, 20, 19, 'Order ORD-20260520-3A7A51', '2026-05-20 20:59:15'),
(4, 3, NULL, 'sale', -1, 8, 7, 'Order ORD-20260521-AC2DCE', '2026-05-20 21:15:54');

-- --------------------------------------------------------

--
-- Table structure for table `newsletter_subscribers`
--

CREATE TABLE `newsletter_subscribers` (
  `id` int(10) UNSIGNED NOT NULL,
  `email` varchar(150) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `subscribed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `type` varchar(50) NOT NULL,
  `title` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `link` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `type`, `title`, `message`, `is_read`, `link`, `created_at`) VALUES
(1, 2, 'order', 'Order Placed!', 'Your order ORD-20260520-3A7A51 has been placed successfully.', 0, 'http://localhost/bakery/pages/orders.php', '2026-05-20 20:59:15'),
(2, 2, 'order', 'Order Update', 'Your order ORD-20260520-3A7A51 is now: Delivered', 0, NULL, '2026-05-20 21:00:29'),
(3, 2, 'order', 'Order Placed!', 'Your order ORD-20260521-AC2DCE has been placed successfully.', 0, 'http://localhost/bakery/pages/orders.php', '2026-05-20 21:15:54'),
(4, 2, 'order', 'Order Update', 'Your order ORD-20260521-AC2DCE is now: Delivered', 0, NULL, '2026-05-20 21:31:42');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(10) UNSIGNED NOT NULL,
  `order_number` varchar(30) NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `coupon_id` int(10) UNSIGNED DEFAULT NULL,
  `billing_name` varchar(100) NOT NULL,
  `billing_email` varchar(150) NOT NULL,
  `billing_phone` varchar(20) NOT NULL,
  `billing_address` text NOT NULL,
  `billing_city` varchar(100) NOT NULL,
  `billing_state` varchar(100) NOT NULL,
  `billing_postal` varchar(20) NOT NULL,
  `billing_country` varchar(100) DEFAULT 'Kenya',
  `shipping_address` text DEFAULT NULL,
  `shipping_city` varchar(100) DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `shipping_cost` decimal(10,2) DEFAULT 0.00,
  `tax_amount` decimal(10,2) DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_method` enum('mpesa','stripe','paypal','cod') DEFAULT 'cod',
  `payment_status` enum('pending','paid','failed','refunded') DEFAULT 'pending',
  `payment_reference` varchar(255) DEFAULT NULL,
  `order_status` enum('pending','processing','ready','delivered','cancelled') DEFAULT 'pending',
  `order_notes` text DEFAULT NULL,
  `admin_notes` text DEFAULT NULL,
  `delivered_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_number`, `user_id`, `coupon_id`, `billing_name`, `billing_email`, `billing_phone`, `billing_address`, `billing_city`, `billing_state`, `billing_postal`, `billing_country`, `shipping_address`, `shipping_city`, `subtotal`, `discount_amount`, `shipping_cost`, `tax_amount`, `total_amount`, `payment_method`, `payment_status`, `payment_reference`, `order_status`, `order_notes`, `admin_notes`, `delivered_at`, `created_at`, `updated_at`) VALUES
(1, 'ORD-20260520-3A7A51', 2, NULL, 'Samson Odhiambo Juma', 'admin@bakery.com', '+254791430850', '00100 Kenya', 'Nairobi', 'Nairobi', '00100', 'Kenya', NULL, NULL, 2200.01, 0.00, 200.00, 176.00, 2576.01, 'cod', 'paid', NULL, 'delivered', '', '', '2026-05-21 00:00:29', '2026-05-20 20:59:15', '2026-05-20 21:00:29'),
(2, 'ORD-20260521-AC2DCE', 2, NULL, 'Samson Odhiambo Juma', 'admin@bakery.com', '+254791430850', '00100 Kenya', 'Nairobi', 'Nairobi', '00100', 'Kenya', NULL, NULL, 2800.00, 0.00, 200.00, 224.00, 3224.00, 'cod', 'paid', NULL, 'delivered', '', '', '2026-05-21 00:31:42', '2026-05-20 21:15:54', '2026-05-20 21:31:42');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `order_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED DEFAULT NULL,
  `product_variant_id` int(10) UNSIGNED DEFAULT NULL,
  `variant_name` varchar(100) DEFAULT NULL,
  `product_name` varchar(200) NOT NULL,
  `product_sku` varchar(80) NOT NULL,
  `product_image` varchar(255) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_variant_id`, `variant_name`, `product_name`, `product_sku`, `product_image`, `quantity`, `unit_price`, `total_price`) VALUES
(1, 1, 13, NULL, NULL, 'Apple Pie', '3EB9696E', 'products/6a0c247ec67d5_1779180670.jpg', 1, 2200.01, 2200.01),
(2, 2, 3, NULL, NULL, 'Red Velvet Royale', 'CAK-003', 'products/6a09f7b43785d_1779038132.jpg', 1, 2800.00, 2800.00);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(10) UNSIGNED NOT NULL,
  `category_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(200) NOT NULL,
  `slug` varchar(220) NOT NULL,
  `sku` varchar(80) NOT NULL,
  `description` text DEFAULT NULL,
  `short_description` varchar(500) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `discount_price` decimal(10,2) DEFAULT NULL,
  `stock_quantity` int(11) DEFAULT 0,
  `low_stock_threshold` int(11) DEFAULT 5,
  `weight` decimal(8,2) DEFAULT NULL COMMENT 'in grams',
  `thumbnail` varchar(255) DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `is_bestseller` tinyint(1) DEFAULT 0,
  `status` enum('active','inactive','out_of_stock') DEFAULT 'active',
  `meta_title` varchar(200) DEFAULT NULL,
  `meta_description` varchar(300) DEFAULT NULL,
  `views` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `slug`, `sku`, `description`, `short_description`, `price`, `discount_price`, `stock_quantity`, `low_stock_threshold`, `weight`, `thumbnail`, `is_featured`, `is_bestseller`, `status`, `meta_title`, `meta_description`, `views`, `created_at`, `updated_at`) VALUES
(1, 1, 'Chocolate Dream Cake', 'chocolate-dream-cake', 'CAK-001', 'Rich triple-layer chocolate cake with ganache frosting and chocolate shavings. Made with premium Belgian chocolate and fresh cream. Perfect for celebrations.', 'Triple-layer Belgian chocolate cake with ganache', 2800.00, 2400.00, 15, 5, NULL, 'products/6a09f6fec9c9e_1779037950.jpg', 1, 1, 'active', '', '', 4, '2026-05-17 16:18:04', '2026-05-20 20:34:37'),
(2, 1, 'Vanilla Celebration Cake', 'vanilla-celebration-cake', 'CAK-002', 'Classic vanilla sponge layered with fresh cream and seasonal fruits. Light, airy and perfect for any celebration.', 'Classic vanilla sponge with fresh cream', 2200.00, NULL, 12, 5, NULL, 'products/6a09f76924f94_1779038057.jpg', 1, 0, 'active', '', '', 1, '2026-05-17 16:18:04', '2026-05-20 21:09:00'),
(3, 1, 'Red Velvet Royale', 'red-velvet-royale', 'CAK-003', 'Stunning red velvet cake with cream cheese frosting. A crowd favorite with its vibrant color and subtle cocoa flavor.', 'Classic red velvet with cream cheese frosting', 3200.00, 2800.00, 7, 5, NULL, 'products/6a09f7b43785d_1779038132.jpg', 1, 1, 'active', '', '', 2, '2026-05-17 16:18:04', '2026-05-20 21:15:54'),
(4, 2, 'Sourdough Artisan Loaf', 'sourdough-artisan-loaf', 'BRD-001', 'Slow-fermented sourdough with crispy crust and chewy interior. Made from our 5-year old starter for complex flavor.', 'Slow-fermented sourdough with crispy crust', 450.00, NULL, 30, 5, NULL, 'products/6a09f85a93531_1779038298.jpg', 1, 1, 'active', '', '', 2, '2026-05-17 16:18:04', '2026-05-20 21:12:16'),
(5, 2, 'Honey Whole Wheat', 'honey-whole-wheat', 'BRD-002', 'Nutritious whole wheat bread sweetened with natural honey. Soft texture, great for everyday use.', 'Nutritious honey whole wheat bread', 380.00, 320.00, 25, 5, NULL, 'products/6a09f8f509eed_1779038453.jpg', 0, 0, 'active', '', '', 0, '2026-05-17 16:18:04', '2026-05-17 17:20:53'),
(6, 3, 'Butter Croissant', 'butter-croissant', 'PST-001', 'Flaky, buttery French croissant made with 72-hour laminated dough. Golden perfection in every bite.', '72-hour laminated butter croissant', 180.00, NULL, 40, 5, NULL, 'products/6a09f94904ce9_1779038537.jpg', 1, 1, 'active', '', '', 1, '2026-05-17 16:18:04', '2026-05-20 21:34:12'),
(7, 3, 'Almond Danish', 'almond-danish', 'PST-002', 'Delicate danish pastry filled with almond cream and topped with toasted almond flakes. A breakfast classic.', 'Almond cream filled danish pastry', 220.00, 180.00, 30, 5, NULL, 'products/6a09f972ab6da_1779038578.jpg', 0, 1, 'active', '', '', 0, '2026-05-17 16:18:04', '2026-05-17 17:22:58'),
(8, 4, 'Classic Chocolate Chip', 'classic-chocolate-chip', 'COK-001', 'Our signature recipe with premium dark chocolate chips, brown butter and a hint of sea salt. Crispy edges, soft center.', 'Brown butter chocolate chip cookies', 80.00, NULL, 100, 5, NULL, 'products/6a09f9a3d4db9_1779038627.jpg', 1, 1, 'active', '', '', 0, '2026-05-17 16:18:04', '2026-05-17 17:23:47'),
(9, 4, 'Red Velvet Cookies', 'red-velvet-cookies', 'COK-002', 'Chewy red velvet cookies with white chocolate chips. Vibrant, festive and absolutely delicious.', 'Chewy red velvet with white chocolate', 90.00, 75.00, 80, 5, NULL, 'products/6a09f9e7c7a3a_1779038695.jpg', 0, 0, 'active', '', '', 0, '2026-05-17 16:18:04', '2026-05-17 17:24:55'),
(10, 5, 'Vanilla Swirl Cupcakes', 'vanilla-swirl-cupcakes', 'CUP-001', 'Fluffy vanilla cupcakes topped with silky Swiss meringue buttercream in beautiful swirls. Available in boxes of 6 or 12.', 'Vanilla cupcakes with Swiss meringue buttercream', 150.00, NULL, 50, 5, NULL, 'products/6a09fa23b34d4_1779038755.jpg', 1, 0, 'active', '', '', 0, '2026-05-17 16:18:04', '2026-05-17 17:25:55'),
(11, 5, 'Salted Caramel Cupcakes', 'salted-caramel-cupcakes', 'CUP-002', 'Brown sugar cupcakes filled with salted caramel and topped with caramel buttercream. An irresistible combination.', 'Brown sugar cupcakes with salted caramel', 170.00, 140.00, 45, 5, NULL, 'products/6a09fa5f44b73_1779038815.jpg', 0, 1, 'active', '', '', 0, '2026-05-17 16:18:04', '2026-05-17 17:26:55'),
(12, 6, 'Glazed Classic Donut', 'glazed-classic-donut', 'DON-001', 'Light, airy yeast donut with our signature vanilla glaze. A timeless classic done perfectly.', 'Classic yeast donut with vanilla glaze', 120.00, NULL, 60, 5, NULL, 'products/6a09faac5d6d2_1779038892.jpg', 0, 1, 'active', '', '', 0, '2026-05-17 16:18:04', '2026-05-17 17:28:12'),
(13, 7, 'Apple Pie', 'apple-pie', '3EB9696E', '', 'Pastry filled with sweet apple mixture.', 2499.99, 2200.01, 19, 5, NULL, 'products/6a0c247ec67d5_1779180670.jpg', 1, 1, 'active', '', '', 1, '2026-05-19 08:51:10', '2026-05-20 20:59:15'),
(14, 7, 'Meat pie', 'meat-pie', 'C79359BD', '', 'A savory baked pastry filled with seasoned minced meat, vegetables, and spices, perfect as a snack or quick meal.', 800.00, 720.00, 12, 5, NULL, 'products/6a0c2603bf3e3_1779181059.jpg', 1, 1, 'active', '', '', 0, '2026-05-19 08:57:39', '2026-05-20 16:10:30');

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `alt_text` varchar(200) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `is_primary` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_variants`
--

CREATE TABLE `product_variants` (
  `id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `variant_name` varchar(100) NOT NULL COMMENT 'e.g. Small, Medium, Large, Special',
  `sku` varchar(50) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock_quantity` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_variants`
--

INSERT INTO `product_variants` (`id`, `product_id`, `variant_name`, `sku`, `price`, `stock_quantity`, `created_at`, `updated_at`) VALUES
(1, 14, 'Small', 'PIE-014-SM', 150.00, 20, '2026-05-20 23:07:35', '2026-05-20 23:07:35'),
(2, 14, 'Medium', 'PIE-014-MD', 250.00, 15, '2026-05-20 23:07:35', '2026-05-20 23:07:35'),
(3, 14, 'Large', 'PIE-014-LG', 400.00, 10, '2026-05-20 23:07:35', '2026-05-20 23:07:35'),
(4, 14, 'Special', 'PIE-014-SP', 500.00, 5, '2026-05-20 23:07:35', '2026-05-20 23:07:35'),
(5, 1, 'Small', 'CAK-001-SM', 1200.00, 10, '2026-05-20 23:07:35', '2026-05-20 23:07:35'),
(6, 1, 'Medium', 'CAK-001-MD', 2200.00, 8, '2026-05-20 23:07:35', '2026-05-20 23:07:35'),
(7, 1, 'Large', 'CAK-001-LG', 3500.00, 5, '2026-05-20 23:07:35', '2026-05-20 23:07:35'),
(8, 1, 'Special', 'CAK-001-SP', 4500.00, 3, '2026-05-20 23:07:35', '2026-05-20 23:07:35'),
(9, 4, 'Small', 'BRD-001-SM', 300.00, 15, '2026-05-20 23:07:35', '2026-05-20 23:07:35'),
(10, 4, 'Medium', 'BRD-001-MD', 500.00, 12, '2026-05-20 23:07:35', '2026-05-20 23:07:35'),
(11, 4, 'Large', 'BRD-001-LG', 800.00, 8, '2026-05-20 23:07:35', '2026-05-20 23:07:35'),
(12, 4, 'Special', 'BRD-001-SP', 1000.00, 4, '2026-05-20 23:07:35', '2026-05-20 23:07:35');

-- --------------------------------------------------------

--
-- Table structure for table `recently_viewed`
--

CREATE TABLE `recently_viewed` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `viewed_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `recently_viewed`
--

INSERT INTO `recently_viewed` (`id`, `user_id`, `product_id`, `viewed_at`) VALUES
(1, 2, 13, '2026-05-20 20:58:23'),
(2, 2, 3, '2026-05-20 21:15:12');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `rating` tinyint(4) NOT NULL CHECK (`rating` between 1 and 5),
  `title` varchar(200) DEFAULT NULL,
  `body` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `admin_reply` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(10) UNSIGNED NOT NULL,
  `key` varchar(100) NOT NULL,
  `value` longtext DEFAULT NULL,
  `type` enum('text','textarea','image','boolean','color','json') DEFAULT 'text',
  `group` varchar(50) DEFAULT 'general',
  `label` varchar(200) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `key`, `value`, `type`, `group`, `label`, `updated_at`) VALUES
(1, 'site_name', 'The Golden Crumb Bakery', 'text', 'general', 'Site Name', '2026-05-20 20:06:40'),
(2, 'site_tagline', 'Baked with Love, Served with Joy', 'text', 'general', 'Site Tagline', '2026-05-20 20:06:40'),
(3, 'site_email', 'hello@goldencrumb.com', 'text', 'general', 'Contact Email', '2026-05-20 20:06:40'),
(4, 'site_phone', '+254 700 123 456', 'text', 'general', 'Phone Number', '2026-05-20 20:06:40'),
(5, 'site_address', '123 Bakery Lane, Westlands, Nairobi', 'text', 'general', 'Address', '2026-05-20 20:06:40'),
(6, 'site_logo', 'banners/6a0e2402ca87d_1779311618.jpg', 'image', 'general', 'Logo', '2026-05-20 21:13:38'),
(7, 'currency', 'KES', 'text', 'general', 'Currency', '2026-05-20 20:06:40'),
(8, 'currency_symbol', 'KSh', 'text', 'general', 'Currency Symbol', '2026-05-20 20:06:40'),
(9, 'tax_rate', '8', 'text', 'general', 'Tax Rate (%)', '2026-05-20 20:56:32'),
(10, 'shipping_cost', '200', 'text', 'general', 'Standard Shipping Cost', '2026-05-20 20:06:40'),
(11, 'free_shipping_min', '3000', 'text', 'general', 'Free Shipping Minimum', '2026-05-20 20:06:40'),
(12, 'hero_title', 'Baked Fresh\nEvery Morning', 'textarea', 'homepage', 'Hero Title', '2026-05-20 20:06:40'),
(13, 'hero_subtitle', 'Artisan breads, dreamy cakes, and buttery pastries — crafted with love in our Nairobi bakery.', 'textarea', 'homepage', 'Hero Subtitle', '2026-05-20 20:06:40'),
(14, 'primary_color', '#8B4513', 'color', 'appearance', 'Primary Color', '2026-05-20 20:06:40'),
(15, 'accent_color', '#D2691E', 'color', 'appearance', 'Accent Color', '2026-05-20 20:06:40'),
(16, 'working_hours', 'Mon-Sat: 7AM-8PM | Sun: 8AM-6PM', 'text', 'contact', 'Working Hours', '2026-05-20 20:06:40'),
(17, 'facebook_url', 'https://facebook.com', 'text', 'social', 'Facebook URL', '2026-05-20 20:06:40'),
(18, 'instagram_url', 'https://instagram.com', 'text', 'social', 'Instagram URL', '2026-05-20 20:06:40'),
(19, 'twitter_url', 'https://twitter.com', 'text', 'social', 'Twitter URL', '2026-05-20 20:06:40'),
(20, 'mpesa_paybill', '247247', 'text', 'payment', 'M-Pesa Paybill', '2026-05-20 20:06:40'),
(21, 'whatsapp_number', '+254700123456', 'text', 'contact', 'WhatsApp Number', '2026-05-20 20:06:40'),
(22, 'maintenance_mode', '0', 'boolean', 'general', 'Maintenance Mode', '2026-05-20 20:06:40'),
(23, 'reviews_auto_approve', '0', 'boolean', 'general', 'Auto-approve Reviews', '2026-05-20 20:06:40'),
(24, 'meta_description', 'Nairobi\'s premium artisan bakery. Fresh sourdough, custom cakes, croissants & more. Order online for same-day delivery.', 'textarea', 'seo', 'Default Meta Description', '2026-05-20 20:06:40');

-- --------------------------------------------------------

--
-- Table structure for table `testimonials`
--

CREATE TABLE `testimonials` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `role` varchar(100) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `content` text NOT NULL,
  `rating` tinyint(4) DEFAULT 5,
  `status` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `testimonials`
--

INSERT INTO `testimonials` (`id`, `name`, `role`, `avatar`, `content`, `rating`, `status`, `sort_order`, `created_at`) VALUES
(1, 'Sarah Kamau', 'Regular Customer', 'avatars/6a0e26bba8c7c_1779312315.jpg', 'The chocolate cake from Crumbs &amp; Co is absolutely divine! I order for every family birthday. The quality is consistently amazing.', 5, 1, 1, '2026-05-17 16:18:04'),
(2, 'James Mwangi', 'Wedding Client', NULL, 'They made our wedding cake and it was breathtaking both in appearance and taste. Our guests could not stop talking about it!', 5, 1, 2, '2026-05-17 16:18:04'),
(3, 'Amina Hassan', 'Food Blogger', NULL, 'As someone who reviews bakeries professionally, I can say this is top-tier. The sourdough and croissants are world-class.', 5, 1, 3, '2026-05-17 16:18:04'),
(4, 'Peter Ochieng', 'Corporate Client', NULL, 'We order from here for all office events. Fast delivery, beautiful packaging and everything tastes incredible. Highly recommend!', 5, 1, 4, '2026-05-17 16:18:04');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `email_verified` tinyint(1) DEFAULT 0,
  `verification_token` varchar(255) DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expires` datetime DEFAULT NULL,
  `remember_token` varchar(255) DEFAULT NULL,
  `status` enum('active','banned','suspended') DEFAULT 'active',
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `phone`, `avatar`, `email_verified`, `verification_token`, `reset_token`, `reset_token_expires`, `remember_token`, `status`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'Blessing Mary', 'blesssing@gmail.com', '$2y$12$wpEkl/MZbFX8bYjNS7dYpOtI6MjGoKB7lP4nEqWsl9m8PMxws3oNS', '+254769165264', 'avatars/6a09f08d2319f_1779036301.jpg', 0, 'b6ab11a9790dc8fbebb5bcbd5bd2f9a22f52fa312c3fb0faae60e670954eb16e', NULL, NULL, NULL, 'active', NULL, '2026-05-17 16:43:38', '2026-05-17 16:45:01'),
(2, 'Samson Odhiambo Juma', 'admin@bakery.com', '$2y$12$gOwOoi5vLbHIj2gKwOx43OBoEAsso2Y/Bn1RY6kl/UowCydQ38Vau', '+254791430850', 'avatars/6a0e2503d6025_1779311875.jpg', 0, '9895cf400e966de0a13af9da5a6e5b619658700c8461521acd20528f89ce3c11', NULL, NULL, NULL, 'active', '2026-05-21 00:34:32', '2026-05-20 20:58:09', '2026-05-20 21:34:32');

-- --------------------------------------------------------

--
-- Table structure for table `user_addresses`
--

CREATE TABLE `user_addresses` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `label` varchar(50) DEFAULT 'Home',
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address_line1` varchar(255) NOT NULL,
  `address_line2` varchar(255) DEFAULT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(100) NOT NULL,
  `postal_code` varchar(20) NOT NULL,
  `country` varchar(100) DEFAULT 'Kenya',
  `is_default` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_type`,`user_id`);

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`);

--
-- Indexes for table `banners`
--
ALTER TABLE `banners`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `blog_posts`
--
ALTER TABLE `blog_posts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `admin_id` (`admin_id`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_session` (`session_id`),
  ADD KEY `fk_cart_variant` (`product_variant_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_parent` (`parent_id`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `idx_code` (`code`);

--
-- Indexes for table `inventory_logs`
--
ALTER TABLE `inventory_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product_id` (`product_id`);

--
-- Indexes for table `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `coupon_id` (`coupon_id`),
  ADD KEY `idx_order_number` (`order_number`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_status` (`order_status`),
  ADD KEY `idx_payment_status` (`payment_status`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `idx_order_id` (`order_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_sku` (`sku`),
  ADD KEY `idx_category` (`category_id`),
  ADD KEY `idx_featured` (`is_featured`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product_id` (`product_id`);

--
-- Indexes for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD KEY `fk_variant_product` (`product_id`);

--
-- Indexes for table `recently_viewed`
--
ALTER TABLE `recently_viewed`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_view` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_product_id` (`product_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `key` (`key`),
  ADD KEY `idx_key` (`key`),
  ADD KEY `idx_group` (`group`);

--
-- Indexes for table `testimonials`
--
ALTER TABLE `testimonials`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_wishlist` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `banners`
--
ALTER TABLE `banners`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `blog_posts`
--
ALTER TABLE `blog_posts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `inventory_logs`
--
ALTER TABLE `inventory_logs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `recently_viewed`
--
ALTER TABLE `recently_viewed`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `testimonials`
--
ALTER TABLE `testimonials`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user_addresses`
--
ALTER TABLE `user_addresses`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `blog_posts`
--
ALTER TABLE `blog_posts`
  ADD CONSTRAINT `blog_posts_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`);

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cart_variant` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `inventory_logs`
--
ALTER TABLE `inventory_logs`
  ADD CONSTRAINT `inventory_logs_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD CONSTRAINT `fk_variant_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `recently_viewed`
--
ALTER TABLE `recently_viewed`
  ADD CONSTRAINT `recently_viewed_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `recently_viewed_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD CONSTRAINT `user_addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
