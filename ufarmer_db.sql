-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jul 20, 2025 at 07:09 PM
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
-- Database: `ufarmer_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `farmers`
--

CREATE TABLE `farmers` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `farm_name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `location` varchar(200) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `verification_documents` varchar(255) DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `rating` decimal(3,2) DEFAULT 0.00,
  `total_reviews` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `farmers`
--

INSERT INTO `farmers` (`id`, `user_id`, `farm_name`, `description`, `location`, `phone`, `profile_image`, `verification_documents`, `is_verified`, `rating`, `total_reviews`, `created_at`) VALUES
(6, 10, 'Green Valley Organic Farm', 'Family-owned organic farm specializing in seasonal vegetables and herbs.', 'Sonoma County, California', '(555) 123-4567', 'farmers/farmer-organic-male.jpg', NULL, 1, 4.80, 47, '2025-07-06 07:27:29'),
(7, 11, 'Sunshine Acres', 'Certified organic farm growing a wide variety of fruits and vegetables.', 'Napa Valley, California', '(555) 234-5678', 'farmers/farmer-female-greenhouse.jpg', NULL, 1, 4.90, 63, '2025-07-06 07:27:29'),
(8, 12, 'Harvest Moon Farm', 'Small-scale diversified farm focusing on Asian vegetables and herbs.', 'Central Valley, California', '(555) 345-6789', 'farmers/farmer-elderly-wise.jpg', NULL, 1, 4.70, 29, '2025-07-06 07:27:29'),
(9, 13, 'Meadowbrook Dairy', 'Grass-fed dairy farm producing fresh milk, cheese, and yogurt.', 'Petaluma, California', '(555) 456-7890', 'farmers/farmer-dairy-specialist.jpg', NULL, 1, 4.60, 34, '2025-07-06 07:27:29'),
(10, 14, 'Golden Harvest Orchards', 'Boutique orchard specializing in heritage apples and stone fruits.', 'Santa Rosa, California', '(555) 567-8901', 'farmers/farmer-orchard-keeper.jpg', NULL, 1, 4.50, 18, '2025-07-06 07:27:29'),
(11, 16, 'J Farmies\' Lawn', 'We grow free range chickens', 'Spring District, Chattisgarh', '9898938989', 'farmers/hen-farmer.jpg', NULL, 0, 0.00, 0, '2025-07-06 15:35:00'),
(12, 17, 'Tested Farm', 'I sell Dairy Farm Produce', 'Toqing City, Kurrapuram', '7759766767', 'default-farmer.jpg', NULL, 0, 0.00, 0, '2025-07-06 15:49:48'),
(13, 19, 'Swastika&#039;s Farm', 'I sell mutton and chicken', 'Kolkata, WB', '1231231234', 'farmers/6873e4dfa892a.jpeg', NULL, 0, 0.00, 0, '2025-07-13 16:54:55');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `farmer_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','preparing','ready','delivered','cancelled') DEFAULT 'pending',
  `delivery_address` text DEFAULT NULL,
  `delivery_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `customer_id`, `farmer_id`, `total_amount`, `status`, `delivery_address`, `delivery_date`, `notes`, `created_at`, `updated_at`) VALUES
(2, 6, 6, 25.50, 'pending', '123 Test Street', NULL, 'Test order', '2025-07-06 17:17:05', '2025-07-06 18:16:40'),
(3, 6, 6, 9.98, 'pending', '456 Real Address\\nTest City, State 12345', NULL, 'Full checkout test', '2025-07-06 17:18:43', '2025-07-06 18:16:40'),
(46, 7, 6, 8.48, 'pending', 'j', NULL, NULL, '2025-07-06 17:42:44', '2025-07-06 18:16:40'),
(47, 7, 6, 8.48, 'pending', 'j', NULL, NULL, '2025-07-06 17:42:44', '2025-07-06 18:16:40'),
(48, 7, 6, 4.24, 'pending', 'ppuuuiuiuiu', NULL, NULL, '2025-07-06 17:56:31', '2025-07-06 18:16:40'),
(49, 7, 6, 4.24, 'pending', ',k', NULL, NULL, '2025-07-06 18:10:39', '2025-07-06 18:16:40'),
(50, 7, 6, 2.49, 'pending', '015', NULL, NULL, '2025-07-06 18:23:38', '2025-07-06 18:23:38'),
(51, 7, 6, 6.73, 'confirmed', 'h', NULL, NULL, '2025-07-06 18:29:21', '2025-07-06 18:29:21'),
(52, 7, 11, 50.00, 'confirmed', 'u', NULL, NULL, '2025-07-06 18:30:24', '2025-07-06 18:30:24'),
(53, 7, 7, 3.99, 'confirmed', 'aaaa', NULL, NULL, '2025-07-07 17:12:25', '2025-07-07 17:12:25'),
(54, 7, 6, 3.59, 'confirmed', '234567890oiujhghgfdertyghb', NULL, 'qwertyuil,mnb v', '2025-07-09 03:50:00', '2025-07-09 03:50:00'),
(55, 7, 6, 3.59, 'confirmed', 'fr', NULL, NULL, '2025-07-09 03:50:48', '2025-07-09 03:50:48'),
(56, 7, 13, 50.00, 'confirmed', 'no', NULL, NULL, '2025-07-13 17:59:41', '2025-07-13 17:59:41'),
(57, 7, 13, 50.00, 'confirmed', 'budge budge 71/2 220007', NULL, 'please handover nicely', '2025-07-17 03:53:16', '2025-07-17 03:53:16');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(1, 3, 21, 2, 4.99),
(44, 46, 21, 2, 4.24),
(45, 47, 21, 2, 4.24),
(46, 48, 21, 1, 4.24),
(47, 49, 21, 1, 4.24),
(48, 50, 22, 1, 2.49),
(49, 51, 21, 1, 4.24),
(50, 51, 22, 1, 2.49),
(51, 52, 41, 1, 50.00),
(52, 53, 27, 1, 3.99),
(53, 54, 23, 1, 3.59),
(54, 55, 23, 1, 3.59),
(55, 56, 43, 1, 50.00),
(56, 57, 43, 1, 50.00);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `farmer_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `unit` varchar(20) NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `seasonal_availability` varchar(100) DEFAULT NULL,
  `stock_quantity` int(11) DEFAULT 0,
  `image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `discount_percentage` decimal(5,2) DEFAULT 0.00,
  `discounted_price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `farmer_id`, `name`, `description`, `price`, `unit`, `category`, `seasonal_availability`, `stock_quantity`, `image`, `is_active`, `created_at`, `discount_percentage`, `discounted_price`) VALUES
(21, 6, 'Organic Tomatoes', 'Fresh organic tomatoes from Green Valley Organic Farm', 4.99, 'lb', 'vegetables', NULL, 41, 'vegetables/tomatoes-organic.jpg', 1, '2025-07-06 07:27:29', 15.00, 4.24),
(22, 6, 'Fresh Basil', 'Fresh fresh basil from Green Valley Organic Farm', 2.49, 'bunch', 'herbs', NULL, 28, 'herbs/basil-fresh.jpg', 1, '2025-07-06 07:27:29', 0.00, NULL),
(23, 6, 'Baby Spinach', 'Fresh baby spinach from Green Valley Organic Farm', 3.99, 'bag', 'vegetables', NULL, 23, 'vegetables/spinach-baby.jpg', 1, '2025-07-06 07:27:29', 10.00, 3.59),
(24, 6, 'Organic Carrots', 'Fresh organic carrots from Green Valley Organic Farm', 2.99, 'lb', 'vegetables', NULL, 40, 'vegetables/carrots-organic.jpg', 1, '2025-07-06 07:27:29', 0.00, NULL),
(25, 7, 'Fresh Strawberries', 'Fresh fresh strawberries from Sunshine Acres', 5.99, 'pint', 'fruits', NULL, 20, 'fruits/strawberries-premium.jpg', 1, '2025-07-06 07:27:29', 20.00, 4.79),
(26, 7, 'Mixed Greens', 'Fresh mixed greens from Sunshine Acres', 4.49, 'bag', 'vegetables', NULL, 35, 'vegetables/leafy-greens.jpg', 1, '2025-07-06 07:27:29', 0.00, NULL),
(27, 7, 'Cherry Tomatoes', 'Fresh cherry tomatoes from Sunshine Acres', 3.99, 'pint', 'vegetables', NULL, 44, 'vegetables/tomatoes-fresh.jpg', 1, '2025-07-06 07:27:29', 0.00, NULL),
(28, 7, 'Bell Peppers', 'Fresh bell peppers from Sunshine Acres', 1.99, 'each', 'vegetables', NULL, 60, 'vegetables/peppers-bell.jpg', 1, '2025-07-06 07:27:29', 0.00, NULL),
(29, 8, 'Bok Choy', 'Fresh bok choy from Harvest Moon Farm', 2.99, 'bunch', 'vegetables', NULL, 25, 'vegetables/bok-choy.jpg', 1, '2025-07-06 07:27:29', 0.00, NULL),
(30, 8, 'Daikon Radish', 'Fresh daikon radish from Harvest Moon Farm', 1.99, 'each', 'vegetables', NULL, 30, 'vegetables/vegetables-mixed.jpg', 1, '2025-07-06 07:27:29', 0.00, NULL),
(31, 8, 'Napa Cabbage', 'Fresh napa cabbage from Harvest Moon Farm', 3.49, 'head', 'vegetables', NULL, 20, 'vegetables/cabbage-green.jpg', 1, '2025-07-06 07:27:29', 0.00, NULL),
(32, 8, 'Shiitake Mushrooms', 'Fresh shiitake mushrooms from Harvest Moon Farm', 6.99, 'lb', 'vegetables', NULL, 15, 'vegetables/vegetables-mixed.jpg', 1, '2025-07-06 07:27:29', 0.00, NULL),
(33, 9, 'Fresh Whole Milk', 'Fresh fresh whole milk from Meadowbrook Dairy', 4.99, 'gallon', 'dairy', NULL, 30, 'dairy/milk-whole.jpg', 1, '2025-07-06 07:27:29', 0.00, NULL),
(34, 9, 'Artisan Cheese', 'Fresh artisan cheese from Meadowbrook Dairy', 8.99, 'wheel', 'dairy', NULL, 12, 'dairy/cheese-farmhouse.jpg', 1, '2025-07-06 07:27:29', 0.00, NULL),
(35, 9, 'Greek Yogurt', 'Fresh greek yogurt from Meadowbrook Dairy', 3.99, 'container', 'dairy', NULL, 40, 'dairy/yogurt-greek.jpg', 1, '2025-07-06 07:27:29', 0.00, NULL),
(36, 9, 'Farm Butter', 'Fresh farm butter from Meadowbrook Dairy', 5.49, 'lb', 'dairy', NULL, 25, 'dairy/butter-homemade.jpg', 1, '2025-07-06 07:27:29', 0.00, NULL),
(37, 10, 'Honeycrisp Apples', 'Fresh honeycrisp apples from Golden Harvest Orchards', 3.99, 'lb', 'fruits', NULL, 100, 'fruits/apples-honeycrisp.jpg', 1, '2025-07-06 07:27:29', 0.00, NULL),
(38, 10, 'Fresh Peaches', 'Fresh fresh peaches from Golden Harvest Orchards', 4.49, 'lb', 'fruits', NULL, 50, 'fruits/peaches-fresh.jpg', 1, '2025-07-06 07:27:29', 0.00, NULL),
(39, 10, 'Pears', 'Fresh pears from Golden Harvest Orchards', 3.49, 'lb', 'fruits', NULL, 75, 'fruits/pears-golden.jpg', 1, '2025-07-06 07:27:29', 0.00, NULL),
(40, 10, 'Plums', 'Fresh plums from Golden Harvest Orchards', 4.99, 'lb', 'fruits', NULL, 30, 'fruits/plums-purple.jpg', 1, '2025-07-06 07:27:29', 0.00, NULL),
(41, 11, 'Organic Carrots', 'Fresh Carrots from Freedom Avenue', 50.00, 'kg', 'vegetables', 'Year-Round', 39, 'vegetables/carrots-organic.jpg', 1, '2025-07-06 16:25:25', 0.00, NULL),
(42, 13, 'Ripe Mangoes', 'Ripe mangoes from my garden', 40.00, 'kg', 'fruits', 'Monsoon', 300, NULL, 1, '2025-07-13 17:35:14', 0.00, NULL),
(43, 13, 'Fully Ripe Mango', 'Totally Ripe mango', 50.00, 'g', 'fruits', 'Monsoon', 49998, 'products/6873ef8c12db3.jpg', 1, '2025-07-13 17:40:28', 0.00, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `farmer_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `user_type` enum('customer','farmer') DEFAULT 'customer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `wallet_balance` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `user_type`, `created_at`, `updated_at`, `wallet_balance`) VALUES
(5, 'Robert Wilson', 'rob@willowcreek.com', '$2y$10$kR/x/SbgmOTHkqtr1Gub6Ob3nABvanKNsPFhstQIDBEMKP9p5h1.q', 'farmer', '2025-07-06 06:50:19', '2025-07-06 07:23:49', 50.00),
(6, 'Demo Customer', 'customer@demo.com', '$2y$10$RA.otanfGAqYYc1ourFLQOqhGl3kWNqSlTKUZDY2qsEBU5MCcHvCe', 'customer', '2025-07-06 06:50:19', '2025-07-06 07:23:49', 500.00),
(7, 'John Doe', 'john@doe.com', '$2y$10$PWvDnAzuaAsiXoyjb60JvucoFmLbK1fs13b4c56Lu7BFNYgkG9IXW', 'customer', '2025-07-06 06:58:40', '2025-07-13 18:00:46', 650.00),
(10, 'John Smith', 'john@greenvalley.com', '$2y$10$1XGMdV1UYFaMvat1EB2gUuyOYBAXPsyfFWDXJkfDbx5m..xpx/qna', 'farmer', '2025-07-06 07:27:29', '2025-07-06 07:27:29', 50.00),
(11, 'Maria Rodriguez', 'maria@sunshineacres.com', '$2y$10$mQegIP9flRs0CL3Iywg47Ogp7tapW3eOpoCFYnyKnE93Vpx1emd3u', 'farmer', '2025-07-06 07:27:29', '2025-07-06 07:27:29', 50.00),
(12, 'David Chen', 'david@harvestmoon.com', '$2y$10$Qq4/TdufFuOF/dL/.rv54uyvOopDBRs6RQV.uXOW/byiEsSClrA8i', 'farmer', '2025-07-06 07:27:29', '2025-07-06 07:27:29', 50.00),
(13, 'Sarah Johnson', 'sarah@meadowbrook.com', '$2y$10$g2LpeSrrFiXaGqDOdPwXM.1cxzKzH32eEG9kFmFbeDnIyB/Y6o28S', 'farmer', '2025-07-06 07:27:29', '2025-07-06 07:27:29', 50.00),
(14, 'Robert Green', 'robert@goldenharvest.com', '$2y$10$uO.pxKz6ZpcqBPNQIrFIv.CseL6YrDN00VQUia5Tf3th65IAWfp/O', 'farmer', '2025-07-06 07:27:29', '2025-07-06 07:27:29', 50.00),
(15, 'Demo Customer', 'demo@example.com', '$2y$10$BwYNHOGW679HOQPa5bDdc.26tWOpLtlBfcS66mWnNBLJKnJvkPNlS', 'customer', '2025-07-06 07:27:29', '2025-07-06 07:27:29', 500.00),
(16, 'John Farm', 'j@f.com', '$2y$10$ktO6jnR9zGlZQxOyIq0A7Om8OXQ2x3MBTZmPD2hb67jAGXumfiXfS', 'farmer', '2025-07-06 15:25:50', '2025-07-06 15:25:50', 0.00),
(17, 'Little Yong', 'L@y.com', '$2y$10$Y9Pgf1okSfXHItY4ExVb4Oc8WF7Ge9iY5MBXZd9OrzbimSOmOnd..', 'farmer', '2025-07-06 15:48:13', '2025-07-06 15:48:13', 0.00),
(18, 'swastika', 'swastika@gmail.com', '$2y$10$ANH1lxckPRMv0ZbtHlZ8ueAHLQnSkUGYgXuz66GkkmO1f661.xI6W', 'customer', '2025-07-09 03:51:47', '2025-07-09 03:51:47', 0.00),
(19, 'Swastika', 'swastika1@gmail.com', '$2y$10$/jyw2n0QIZU9iPNkvcLize9Sta2aM5eFY6i9LNtcPZ57w1LwBbFyi', 'farmer', '2025-07-13 16:52:47', '2025-07-13 17:27:09', 500.00);

-- --------------------------------------------------------

--
-- Table structure for table `wallet_transactions`
--

CREATE TABLE `wallet_transactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('credit','debit') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `reference_type` enum('initial','purchase','sale','refund','recharge') DEFAULT 'purchase',
  `reference_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wallet_transactions`
--

INSERT INTO `wallet_transactions` (`id`, `user_id`, `type`, `amount`, `description`, `reference_type`, `reference_id`, `created_at`) VALUES
(1, 6, 'credit', 500.00, 'Initial wallet balance for testing', 'initial', NULL, '2025-07-06 17:07:14'),
(2, 7, 'credit', 1000.00, 'Initial wallet balance for testing', 'initial', NULL, '2025-07-06 17:07:14'),
(3, 6, 'debit', 9.98, 'Order #3 - Organic Tomatoes', 'purchase', 3, '2025-07-06 17:18:43'),
(4, 10, 'credit', 9.98, 'Sale from Order #3', 'sale', 3, '2025-07-06 17:18:43'),
(5, 7, 'debit', 8.48, 'Order #46 - Farm products purchase', 'purchase', 46, '2025-07-06 17:42:44'),
(6, 10, 'credit', 8.48, 'Sale from Order #46', 'sale', 46, '2025-07-06 17:42:44'),
(7, 7, 'debit', 8.48, 'Order #47 - Farm products purchase', 'purchase', 47, '2025-07-06 17:42:44'),
(8, 10, 'credit', 8.48, 'Sale from Order #47', 'sale', 47, '2025-07-06 17:42:44'),
(9, 7, 'debit', 4.24, 'Order #48 - Farm products purchase', 'purchase', 48, '2025-07-06 17:56:32'),
(10, 10, 'credit', 4.24, 'Sale from Order #48', 'sale', 48, '2025-07-06 17:56:32'),
(11, 7, 'debit', 4.24, 'Order #49 - Farm products purchase', 'purchase', 49, '2025-07-06 18:10:39'),
(12, 10, 'credit', 4.24, 'Sale from Order #49', 'sale', 49, '2025-07-06 18:10:39'),
(31, 7, 'debit', 2.49, 'Order #50 - Farm products purchase', 'purchase', 50, '2025-07-06 18:23:38'),
(32, 10, 'credit', 2.49, 'Sale from Order #50', 'sale', 50, '2025-07-06 18:23:38'),
(33, 7, 'debit', 6.73, 'Order #51 - Farm products purchase', 'purchase', 51, '2025-07-06 18:29:21'),
(34, 10, 'credit', 6.73, 'Sale from Order #51', 'sale', 51, '2025-07-06 18:29:21'),
(35, 7, 'debit', 50.00, 'Order #52 - Farm products purchase', 'purchase', 52, '2025-07-06 18:30:24'),
(36, 16, 'credit', 50.00, 'Sale from Order #52', 'sale', 52, '2025-07-06 18:30:24'),
(37, 7, 'debit', 3.99, 'Order #53 - Farm products purchase', 'purchase', 53, '2025-07-07 17:12:25'),
(38, 11, 'credit', 3.99, 'Sale from Order #53', 'sale', 53, '2025-07-07 17:12:25'),
(39, 7, 'debit', 3.59, 'Order #54 - Farm products purchase', 'purchase', 54, '2025-07-09 03:50:00'),
(40, 10, 'credit', 3.59, 'Sale from Order #54', 'sale', 54, '2025-07-09 03:50:00'),
(41, 7, 'debit', 3.59, 'Order #55 - Farm products purchase', 'purchase', 55, '2025-07-09 03:50:48'),
(42, 10, 'credit', 3.59, 'Sale from Order #55', 'sale', 55, '2025-07-09 03:50:48'),
(43, 7, 'debit', 50.00, 'Order #56 - Farm products purchase', 'purchase', 56, '2025-07-13 17:59:41'),
(44, 19, 'credit', 50.00, 'Sale from Order #56', 'sale', 56, '2025-07-13 17:59:41'),
(45, 7, 'credit', 150.00, 'Wallet recharge - ₹12,450', 'recharge', NULL, '2025-07-13 18:00:46'),
(46, 7, 'debit', 50.00, 'Order #57 - Farm products purchase', 'purchase', 57, '2025-07-17 03:53:16'),
(47, 19, 'credit', 50.00, 'Sale from Order #57', 'sale', 57, '2025-07-17 03:53:16');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `farmers`
--
ALTER TABLE `farmers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `farmer_id` (`farmer_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `farmer_id` (`farmer_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `farmer_id` (`farmer_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `wallet_transactions`
--
ALTER TABLE `wallet_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `farmers`
--
ALTER TABLE `farmers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `wallet_transactions`
--
ALTER TABLE `wallet_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `farmers`
--
ALTER TABLE `farmers`
  ADD CONSTRAINT `farmers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`farmer_id`) REFERENCES `farmers` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`farmer_id`) REFERENCES `farmers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`farmer_id`) REFERENCES `farmers` (`id`),
  ADD CONSTRAINT `reviews_ibfk_3` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);

--
-- Constraints for table `wallet_transactions`
--
ALTER TABLE `wallet_transactions`
  ADD CONSTRAINT `wallet_transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
