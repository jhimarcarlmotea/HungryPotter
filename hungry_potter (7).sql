-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 29, 2025 at 04:51 AM
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
-- Database: `hungry_potter`
--

-- --------------------------------------------------------

--
-- Table structure for table `fooditems`
--

CREATE TABLE `fooditems` (
  `foodId` int(11) NOT NULL,
  `foodName` varchar(251) NOT NULL,
  `image_path` varchar(251) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `category` varchar(251) NOT NULL,
  `availability` enum('Available','Not Available','','') NOT NULL,
  `description` text NOT NULL,
  `bestSeller` enum('Yes','No','','') NOT NULL DEFAULT 'No'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fooditems`
--

INSERT INTO `fooditems` (`foodId`, `foodName`, `image_path`, `price`, `quantity`, `category`, `availability`, `description`, `bestSeller`) VALUES
(1, 'Plain Rice', 'uploads/food/1747299449_Rice.png', 12.00, 50, 'Beverages & Extra', 'Available', '', 'No'),
(2, 'Garlic Rice', 'uploads/food/1747299440_Garlic.png', 15.00, 50, 'Beverages & Extra', 'Available', '', 'No'),
(3, 'Barkada Sisig Good for 2-3 pax', 'uploads/food/1747301004_Barkada Sisig.png', 239.00, 50, 'Hungry Hooray!', 'Available', 'Crispy pork sisig, perfect for sharing with the whole crew, served with calamansi, chili, and soy sauce', 'No'),
(4, 'Bacon Silog', 'uploads/food/1747301091_BaconSilog.png', 75.00, 50, 'Silog Meals', 'Available', 'crispy, savory bacon, garlic fried rice, and a sunny-side-up egg', 'No'),
(5, 'Tapsilog', 'uploads/food/1747301579_Tapsilog.png', 85.00, 50, 'Silog Meals', 'Available', 'tender beef tapa, garlic fried rice, and a perfectly cooked sunny-side-up egg.', 'Yes'),
(6, 'Porksilog', 'uploads/food/1747301943_PorkSilog.png', 85.00, 50, 'Silog Meals', 'Available', ' juicy pork slices, paired with garlic fried rice and a sunny-side-up egg', 'No'),
(7, 'Chicksilog', 'uploads/food/1747301986_ChickSilog.png', 85.00, 50, 'Silog Meals', 'Available', 'crispy fried chicken, garlic fried rice, and a sunny-side-up egg.', 'No'),
(8, 'Sisigsilog', 'uploads/food/1747302292_SisigSilog.png', 85.00, 50, 'Silog Meals', 'Available', 'sizzling pork sisig, garlic fried rice, and a sunny-side-up egg', 'Yes'),
(9, 'Hungariansilog', 'uploads/food/1747302329_HungarianSilog.png', 85.00, 50, 'Silog Meals', 'Available', ' smoky and flavorful Hungarian sausage, garlic fried rice, and a sunny-side-up egg', 'No'),
(10, 'Bangsilog', 'uploads/food/1747302363_BangSilog.png', 85.00, 0, 'Silog Meals', 'Not Available', 'crispy fried bangus, garlic fried rice, and a sunny-side-up egg, served with a side of tangy vinegar and fresh tomatoes', 'Yes'),
(11, 'Bagnetsilog', 'uploads/food/1747302394_BagnetSilog.png', 85.00, 50, 'Silog Meals', 'Available', 'crispy and crunchy bagnet, garlic fried rice, and a sunny-side-up egg, served with fresh tomatoes', 'No'),
(12, 'Wingsilog', 'uploads/food/1747302425_WingSilog.png', 85.00, 50, 'Silog Meals', 'Available', 'crispy chicken wings, garlic fried rice, and a sunny-side-up egg, served with a side of creamy sauce', 'No'),
(13, 'Spamsilog', 'uploads/food/1747302458_SpamSilog.png', 80.00, 50, 'Silog Meals', 'Available', 'flavorful spam slices, garlic fried rice, and a sunny-side-up egg', 'Yes'),
(14, 'Embosilog', 'uploads/food/1747302487_EmboSilog.png', 80.00, 34, 'Silog Meals', 'Available', 'flavorful embotido slices, garlic fried rice, and a sunny-side-up egg, serve with a side of creamy sauce', 'Yes'),
(15, 'Tosilog', 'uploads/food/1747302526_Tosilog.png', 75.00, 50, 'Silog Meals', 'Available', 'sweet and savory marinated tocino, garlic fried rice, and a sunny-side-up egg', 'Yes'),
(16, 'Cornsilog', 'uploads/food/1747302669_CornSilog.png', 80.00, 50, 'Silog Meals', 'Available', 'corned beef, garlic fried rice, and a sunny-side-up egg, served with a side of fresh tomatoes', 'No'),
(17, 'Shangsilog', 'uploads/food/1747302723_ShangSilog.png', 80.00, 50, 'Silog Meals', 'Available', 'savory and juicy Shanghai rolls, garlic fried rice, and a sunny-side-up egg, served with a side of sweet ketchup sauce', 'No'),
(18, 'Hotsilog', 'uploads/food/1747302751_HotSilog.png', 70.00, 50, 'Silog Meals', 'Available', 'plump and juicy hotdogs, garlic fried rice, and a sunny-side-up egg', 'No'),
(19, 'Longsilog', 'uploads/food/1747302799_LongSilog.png', 70.00, 50, 'Silog Meals', 'Available', 'sweet and savory longganisa, garlic fried rice, and a sunny-side-up egg, served with a side of spiced vinegar', 'No'),
(20, 'Beshy Sisig Good for 2 pax', 'uploads/food/1747302899_Beshy Sisig.png', 159.00, 20, 'Hungry Hooray!', 'Available', 'Enjoy our Beshy Sisig a sizzling platter of crispy and savory pork sisig, perfect for sharing, served with a side of calamansi and chili', 'Yes'),
(21, 'Chicken Inasal', 'uploads/food/1747303050_Chicken Inasal.png', 99.00, 50, 'Hungry Meals & Set Meals', 'Available', ' tender, smoky, and perfectly grilled, served with a generous portion of steamed rice', 'No'),
(22, 'Set A', 'uploads/food/1747303083_Set A.png', 220.00, 50, 'Hungry Meals & Set Meals', 'Available', '3pcs Chicken Wings, Bagnet, Bangus', 'No'),
(23, 'Set B', 'uploads/food/1747303111_Set B.png', 220.00, 50, 'Hungry Meals & Set Meals', 'Available', '3pcs Chicken Wings, Chicken Fillet, Porkchop', 'No'),
(24, 'Egg', 'uploads/food/1747303295_Egg.png', 17.00, 50, 'Beverages & Extra', 'Available', '', 'No'),
(25, 'Chicken', 'uploads/food/1747303336_chicken.png', 17.00, 50, 'Beverages & Extra', 'Available', '', 'No'),
(26, 'Pork', 'uploads/food/1747303378_pork.png', 17.00, 50, 'Beverages & Extra', 'Available', '', 'No'),
(27, 'Tapa', 'uploads/food/1747303407_tapa.png', 65.00, 50, 'Beverages & Extra', 'Available', '', 'No'),
(28, 'Sisig', 'uploads/food/1747303426_sisig.png', 65.00, 50, 'Beverages & Extra', 'Available', '', 'No'),
(29, 'Spam', 'uploads/food/1747303447_spam.png', 60.00, 50, 'Beverages & Extra', 'Available', '', 'No'),
(30, 'Bangus', 'uploads/food/1747303467_bangus.png', 60.00, 50, 'Beverages & Extra', 'Available', '', 'No'),
(31, 'Hotdog', 'uploads/food/1747303494_hotdog.png', 60.00, 50, 'Beverages & Extra', 'Available', '', 'No'),
(32, 'Longganisa', 'uploads/food/1747303516_longganisa.png', 55.00, 50, 'Beverages & Extra', 'Available', '', 'No'),
(33, 'Embotido', 'uploads/food/1747303538_embotido.png', 55.00, 50, 'Beverages & Extra', 'Available', '', 'No'),
(34, 'Tocino', 'uploads/food/1747303558_tocino.png', 55.00, 50, 'Beverages & Extra', 'Available', '', 'No'),
(35, 'Bacon', 'uploads/food/1747303927_bacon.png', 55.00, 42, 'Beverages & Extra', 'Available', '', 'No'),
(36, 'Coke', 'uploads/food/1748172578_coke.png', 75.00, 50, 'Beverages & Extra', 'Available', '', 'No'),
(37, 'Sprite', 'uploads/food/1748172649_download (2).png', 75.00, 50, 'Beverages & Extra', 'Available', '', 'No'),
(38, 'Royal', 'uploads/food/1748172672_Royal1.png', 75.00, 50, 'Beverages & Extra', 'Available', '', 'No');

-- --------------------------------------------------------

--
-- Table structure for table `gallery_images`
--

CREATE TABLE `gallery_images` (
  `id` int(11) NOT NULL,
  `image_path` varchar(1500) NOT NULL,
  `title` varchar(100) NOT NULL,
  `category` enum('current','last_month','','') NOT NULL,
  `display_order` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gallery_images`
--

INSERT INTO `gallery_images` (`id`, `image_path`, `title`, `category`, `display_order`, `created_at`, `updated_at`) VALUES
(1, 'https://c.animaapp.com/iD6ZGSdb/img/image-47@2x.png', 'Food Image 1', 'current', 1, '2025-05-19 13:03:26', '2025-05-19 13:03:26'),
(2, 'https://c.animaapp.com/iD6ZGSdb/img/image-48@2x.png', 'Food Image 2', 'current', 2, '2025-05-19 13:03:26', '2025-05-19 13:03:26'),
(3, 'https://c.animaapp.com/iD6ZGSdb/img/image-49@2x.png', 'Food Image 3', 'current', 3, '2025-05-19 13:03:26', '2025-05-19 13:03:26'),
(4, 'https://c.animaapp.com/iD6ZGSdb/img/image-50@2x.png', 'Food Image 4', 'current', 4, '2025-05-19 13:03:26', '2025-05-19 13:03:26'),
(5, 'https://c.animaapp.com/iD6ZGSdb/img/image-51@2x.png', 'Food Image 5', 'current', 5, '2025-05-19 13:03:26', '2025-05-19 13:03:26'),
(6, 'https://c.animaapp.com/iD6ZGSdb/img/image-52@2x.png', 'Food Image 6', 'current', 6, '2025-05-19 13:03:26', '2025-05-19 13:03:26'),
(7, 'https://c.animaapp.com/iD6ZGSdb/img/image-53@2x.png', 'Food Image 7', 'current', 7, '2025-05-19 13:03:26', '2025-05-19 13:03:26'),
(8, 'https://c.animaapp.com/iD6ZGSdb/img/image-54@2x.png', 'Food Image 8', 'current', 8, '2025-05-19 13:03:26', '2025-05-19 13:03:26'),
(9, 'https://c.animaapp.com/iD6ZGSdb/img/image-55@2x.png', 'Food Image 9', 'current', 9, '2025-05-19 13:03:26', '2025-05-19 13:03:26'),
(10, 'https://c.animaapp.com/iD6ZGSdb/img/image-56@2x.png', 'Food Image 10', 'current', 10, '2025-05-19 13:03:26', '2025-05-19 13:03:26'),
(11, 'https://c.animaapp.com/iD6ZGSdb/img/image-57@2x.png', 'Food Image 11', 'current', 11, '2025-05-19 13:03:26', '2025-05-19 13:03:26'),
(12, 'https://c.animaapp.com/iD6ZGSdb/img/image-58@2x.png', 'Food Image 12', 'current', 12, '2025-05-19 13:03:26', '2025-05-19 13:03:26'),
(13, 'https://c.animaapp.com/iD6ZGSdb/img/image-47@2x.png', 'Last Month 1', 'last_month', 1, '2025-05-19 13:03:26', '2025-05-19 13:03:26'),
(14, 'https://c.animaapp.com/iD6ZGSdb/img/image-48@2x.png', 'Last Month 2', 'last_month', 2, '2025-05-19 13:03:26', '2025-05-19 13:03:26'),
(15, 'https://c.animaapp.com/iD6ZGSdb/img/image-49@2x.png', 'Last Month 3', 'last_month', 3, '2025-05-19 13:03:26', '2025-05-19 13:03:26'),
(16, 'https://c.animaapp.com/iD6ZGSdb/img/image-50@2x.png', 'Last Month 4', 'last_month', 4, '2025-05-19 13:03:26', '2025-05-19 13:03:26'),
(17, 'https://c.animaapp.com/iD6ZGSdb/img/image-51@2x.png', 'Last Month 5', 'last_month', 5, '2025-05-19 13:03:26', '2025-05-19 13:03:26'),
(18, 'https://c.animaapp.com/iD6ZGSdb/img/image-54@2x.png', 'Last Month 6', 'last_month', 6, '2025-05-19 13:03:26', '2025-05-19 13:03:26');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_number` varchar(20) NOT NULL,
  `payment_method` enum('cod','gcash','paymaya') NOT NULL DEFAULT 'cod',
  `payment_number` varchar(15) DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `delivery_fee` decimal(10,2) NOT NULL DEFAULT 38.00,
  `promo_discount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `order_total` decimal(10,2) NOT NULL,
  `status` enum('Pending','Confirmed','Preparing','Out for Delivery','Delivered','Cancelled') DEFAULT 'Pending',
  `order_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`order_data`)),
  `notes` text DEFAULT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `order_number`, `payment_method`, `payment_number`, `subtotal`, `delivery_fee`, `promo_discount`, `order_total`, `status`, `order_data`, `notes`, `order_date`, `updated_at`) VALUES
(1, 1, 'HP202505251210', 'cod', '', 85.00, 38.00, 0.00, 123.00, 'Pending', '{\"items\":[{\"foodId\":12,\"foodName\":\"Wingsilog\",\"price\":85,\"quantity\":1,\"image_path\":\"uploads\\/food\\/1747302425_WingSilog.png\",\"description\":\"crispy chicken wings, garlic fried rice, and a sunny-side-up egg, served with a side of creamy sauce\"}],\"customer\":{\"name\":\"Jhimar Carl Motea\",\"phone\":\"09982832054\",\"email\":\"jhimarcarlmotea23@gmail.com\",\"address\":\"152\"},\"payment_method\":\"cod\",\"payment_number\":\"\",\"subtotal\":85,\"delivery_fee\":38,\"promo_discount\":0,\"total\":123}', NULL, '2025-05-25 11:03:16', '2025-05-25 11:03:16'),
(2, 1, 'HP202505258049', 'cod', '', 170.00, 38.00, 0.00, 208.00, 'Pending', '{\"items\":[{\"foodId\":12,\"foodName\":\"Wingsilog\",\"price\":85,\"quantity\":2,\"image_path\":\"uploads\\/food\\/1747302425_WingSilog.png\",\"description\":\"crispy chicken wings, garlic fried rice, and a sunny-side-up egg, served with a side of creamy sauce\"}],\"customer\":{\"name\":\"Jhimar Carl Motea\",\"phone\":\"09982832054\",\"email\":\"jhimarcarlmotea23@gmail.com\",\"address\":\"152\"},\"payment_method\":\"cod\",\"payment_number\":\"\",\"subtotal\":170,\"delivery_fee\":38,\"promo_discount\":0,\"total\":208}', NULL, '2025-05-25 11:37:12', '2025-05-25 11:37:12'),
(3, 1, 'HP202505256098', 'cod', '', 245.00, 38.00, 0.00, 283.00, 'Delivered', '{\"items\":[{\"foodId\":12,\"foodName\":\"Wingsilog\",\"price\":85,\"quantity\":2,\"image_path\":\"uploads\\/food\\/1747302425_WingSilog.png\",\"description\":\"crispy chicken wings, garlic fried rice, and a sunny-side-up egg, served with a side of creamy sauce\"},{\"foodId\":15,\"foodName\":\"Tosilog\",\"price\":75,\"quantity\":1,\"image_path\":\"uploads\\/food\\/1747302526_Tosilog.png\",\"description\":\"sweet and savory marinated tocino, garlic fried rice, and a sunny-side-up egg\"}],\"customer\":{\"name\":\"Jhimar Carl Motea\",\"phone\":\"09982832054\",\"email\":\"jhimarcarlmotea23@gmail.com\",\"address\":\"152\"},\"payment_method\":\"cod\",\"payment_number\":\"\",\"subtotal\":245,\"delivery_fee\":38,\"promo_discount\":0,\"total\":283}', NULL, '2025-05-25 11:57:58', '2025-05-25 13:40:11'),
(4, 1, 'HP202505251277', 'cod', '', 255.00, 38.00, 0.00, 293.00, 'Pending', '{\"items\":[{\"foodId\":12,\"foodName\":\"Wingsilog\",\"price\":85,\"quantity\":3,\"image_path\":\"uploads\\/food\\/1747302425_WingSilog.png\",\"description\":\"crispy chicken wings, garlic fried rice, and a sunny-side-up egg, served with a side of creamy sauce\"}],\"customer\":{\"name\":\"Jhimar Carl Motea\",\"phone\":\"09982832054\",\"email\":\"jhimarcarlmotea23@gmail.com\",\"address\":\"152\"},\"payment_method\":\"cod\",\"payment_number\":\"\",\"subtotal\":255,\"delivery_fee\":38,\"promo_discount\":0,\"total\":293,\"promotions_applied\":{\"free_cokes\":0}}', NULL, '2025-05-25 12:16:51', '2025-05-25 12:16:51'),
(5, 1, 'HP202505259915', 'cod', '', 160.00, 38.00, 0.00, 198.00, 'Pending', '{\"items\":[{\"foodId\":12,\"foodName\":\"Wingsilog\",\"price\":85,\"quantity\":1,\"image_path\":\"uploads\\/food\\/1747302425_WingSilog.png\",\"description\":\"crispy chicken wings, garlic fried rice, and a sunny-side-up egg, served with a side of creamy sauce\"},{\"foodId\":15,\"foodName\":\"Tosilog\",\"price\":75,\"quantity\":1,\"image_path\":\"uploads\\/food\\/1747302526_Tosilog.png\",\"description\":\"sweet and savory marinated tocino, garlic fried rice, and a sunny-side-up egg\"}],\"customer\":{\"name\":\"Jhimar Carl Motea\",\"phone\":\"09982832054\",\"email\":\"jhimarcarlmotea23@gmail.com\",\"address\":\"152\"},\"payment_method\":\"cod\",\"payment_number\":\"\",\"subtotal\":160,\"delivery_fee\":38,\"promo_discount\":0,\"total\":198}', NULL, '2025-05-25 13:36:38', '2025-05-25 13:36:38'),
(6, 5, 'HP202505266721', 'cod', '', 170.00, 38.00, 0.00, 208.00, 'Pending', '{\"items\":[{\"foodId\":12,\"foodName\":\"Wingsilog\",\"price\":85,\"quantity\":2,\"image_path\":\"uploads\\/food\\/1747302425_WingSilog.png\",\"description\":\"crispy chicken wings, garlic fried rice, and a sunny-side-up egg, served with a side of creamy sauce\"}],\"customer\":{\"name\":\"Kou Motea\",\"phone\":\"09982832054\",\"email\":\"gyuleeseung@gmail.com\",\"address\":\"152\"},\"payment_method\":\"cod\",\"payment_number\":\"\",\"subtotal\":170,\"delivery_fee\":38,\"promo_discount\":0,\"total\":208}', NULL, '2025-05-26 01:33:14', '2025-05-26 01:33:14'),
(7, 5, 'HP202505267717', 'cod', '', 0.00, 38.00, 0.00, 38.00, 'Pending', '{\"items\":[],\"customer\":{\"name\":\"Kou Motea\",\"phone\":\"09982832054\",\"email\":\"gyuleeseung@gmail.com\",\"address\":\"152\"},\"payment_method\":\"cod\",\"payment_number\":\"\",\"subtotal\":0,\"delivery_fee\":38,\"promo_discount\":0,\"total\":38}', NULL, '2025-05-26 01:33:34', '2025-05-26 01:33:34'),
(8, 1, 'HP202505261582', 'cod', '', 75.00, 38.00, 0.00, 113.00, 'Delivered', '{\"items\":[{\"foodId\":15,\"foodName\":\"Tosilog\",\"price\":75,\"quantity\":1,\"image_path\":\"uploads\\/food\\/1747302526_Tosilog.png\",\"description\":\"sweet and savory marinated tocino, garlic fried rice, and a sunny-side-up egg\"}],\"customer\":{\"name\":\"Jhimar Carl Motea\",\"phone\":\"09982832054\",\"email\":\"jhimarcarlmotea23@gmail.com\",\"address\":\"152\"},\"payment_method\":\"cod\",\"payment_number\":\"\",\"subtotal\":75,\"delivery_fee\":38,\"promo_discount\":0,\"total\":113}', NULL, '2025-05-26 02:00:01', '2025-05-26 09:15:02'),
(9, 1, 'HP202505265156', 'cod', '', 170.00, 38.00, 0.00, 208.00, 'Pending', '{\"items\":[{\"foodId\":12,\"foodName\":\"Wingsilog\",\"price\":85,\"quantity\":2,\"image_path\":\"uploads\\/food\\/1747302425_WingSilog.png\",\"description\":\"crispy chicken wings, garlic fried rice, and a sunny-side-up egg, served with a side of creamy sauce\"}],\"customer\":{\"name\":\"Jhimar Carl Motea\",\"phone\":\"09982832054\",\"email\":\"jhimarcarlmotea23@gmail.com\",\"address\":\"152\"},\"payment_method\":\"cod\",\"payment_number\":\"\",\"subtotal\":170,\"delivery_fee\":38,\"promo_discount\":0,\"total\":208}', NULL, '2025-05-26 09:30:38', '2025-05-26 09:30:38'),
(10, 1, 'HP202505269837', 'paymaya', '', 85.00, 38.00, 0.00, 123.00, 'Pending', '{\"items\":[{\"foodId\":12,\"foodName\":\"Wingsilog\",\"price\":85,\"quantity\":1,\"image_path\":\"uploads\\/food\\/1747302425_WingSilog.png\",\"description\":\"crispy chicken wings, garlic fried rice, and a sunny-side-up egg, served with a side of creamy sauce\"}],\"customer\":{\"name\":\"Jhimar Carl Motea\",\"phone\":\"09982832054\",\"email\":\"jhimarcarlmotea23@gmail.com\",\"address\":\"152\"},\"payment_method\":\"paymaya\",\"payment_number\":\"\",\"subtotal\":85,\"delivery_fee\":38,\"promo_discount\":0,\"total\":123}', NULL, '2025-05-26 10:22:51', '2025-05-26 10:22:51'),
(11, 1, 'HP202505267630', 'paymaya', '', 0.00, 38.00, 0.00, 38.00, 'Pending', '{\"items\":[],\"customer\":{\"name\":\"Jhimar Carl Motea\",\"phone\":\"09982832054\",\"email\":\"jhimarcarlmotea23@gmail.com\",\"address\":\"152\"},\"payment_method\":\"paymaya\",\"payment_number\":\"\",\"subtotal\":0,\"delivery_fee\":38,\"promo_discount\":0,\"total\":38}', NULL, '2025-05-26 10:23:31', '2025-05-26 10:23:31'),
(12, 1, 'HP202505262891', 'paymaya', '', 0.00, 38.00, 0.00, 38.00, 'Pending', '{\"items\":[],\"customer\":{\"name\":\"Jhimar Carl Motea\",\"phone\":\"09982832054\",\"email\":\"jhimarcarlmotea23@gmail.com\",\"address\":\"152\"},\"payment_method\":\"paymaya\",\"payment_number\":\"\",\"subtotal\":0,\"delivery_fee\":38,\"promo_discount\":0,\"total\":38}', NULL, '2025-05-26 10:23:57', '2025-05-26 10:23:57'),
(13, 1, 'HP202505263352', 'cod', '', 170.00, 38.00, 0.00, 208.00, 'Pending', '{\"items\":[{\"foodId\":12,\"foodName\":\"Wingsilog\",\"price\":85,\"quantity\":2,\"image_path\":\"uploads\\/food\\/1747302425_WingSilog.png\",\"description\":\"crispy chicken wings, garlic fried rice, and a sunny-side-up egg, served with a side of creamy sauce\"}],\"customer\":{\"name\":\"Jhimar Carl Motea\",\"phone\":\"09982832054\",\"email\":\"jhimarcarlmotea23@gmail.com\",\"address\":\"152\"},\"payment_method\":\"cod\",\"payment_number\":\"\",\"subtotal\":170,\"delivery_fee\":38,\"promo_discount\":0,\"total\":208}', NULL, '2025-05-26 10:31:43', '2025-05-26 10:31:43'),
(14, 1, 'HP202505265549', 'cod', '', 85.00, 38.00, 0.00, 123.00, 'Pending', '{\"items\":[{\"foodId\":5,\"foodName\":\"Tapsilog\",\"price\":85,\"quantity\":1,\"image_path\":\"uploads\\/food\\/1747301579_Tapsilog.png\",\"description\":\"tender beef tapa, garlic fried rice, and a perfectly cooked sunny-side-up egg.\"}],\"customer\":{\"name\":\"Jhimar Carl Motea\",\"phone\":\"09982832054\",\"email\":\"jhimarcarlmotea23@gmail.com\",\"address\":\"152\"},\"payment_method\":\"cod\",\"payment_number\":\"\",\"subtotal\":85,\"delivery_fee\":38,\"promo_discount\":0,\"total\":123}', NULL, '2025-05-26 10:37:03', '2025-05-26 10:37:03'),
(15, 1, 'HP202505268458', 'cod', '', 85.00, 38.00, 0.00, 123.00, 'Pending', '{\"items\":[{\"foodId\":12,\"foodName\":\"Wingsilog\",\"price\":85,\"quantity\":1,\"image_path\":\"uploads\\/food\\/1747302425_WingSilog.png\",\"description\":\"crispy chicken wings, garlic fried rice, and a sunny-side-up egg, served with a side of creamy sauce\"}],\"customer\":{\"name\":\"Jhimar Carl Motea\",\"phone\":\"09982832054\",\"email\":\"jhimarcarlmotea23@gmail.com\",\"address\":\"152\"},\"payment_method\":\"cod\",\"payment_number\":\"\",\"subtotal\":85,\"delivery_fee\":38,\"promo_discount\":0,\"total\":123}', NULL, '2025-05-26 10:58:11', '2025-05-26 10:58:11'),
(16, 1, 'HP202505267438', 'cod', '', 85.00, 38.00, 0.00, 123.00, 'Pending', '{\"items\":[{\"foodId\":12,\"foodName\":\"Wingsilog\",\"price\":85,\"quantity\":1,\"image_path\":\"uploads\\/food\\/1747302425_WingSilog.png\",\"description\":\"crispy chicken wings, garlic fried rice, and a sunny-side-up egg, served with a side of creamy sauce\"}],\"customer\":{\"name\":\"Jhimar Carl Motea\",\"phone\":\"09982832054\",\"email\":\"jhimarcarlmotea23@gmail.com\",\"address\":\"152\"},\"payment_method\":\"cod\",\"payment_number\":\"\",\"subtotal\":85,\"delivery_fee\":38,\"promo_discount\":0,\"total\":123}', NULL, '2025-05-26 10:58:43', '2025-05-26 10:58:43'),
(17, 1, 'HP202505265728', 'cod', '', 85.00, 38.00, 0.00, 123.00, 'Pending', '{\"items\":[{\"foodId\":12,\"foodName\":\"Wingsilog\",\"price\":85,\"quantity\":1,\"image_path\":\"uploads\\/food\\/1747302425_WingSilog.png\",\"description\":\"crispy chicken wings, garlic fried rice, and a sunny-side-up egg, served with a side of creamy sauce\"}],\"customer\":{\"name\":\"Jhimar Carl Motea\",\"phone\":\"09982832054\",\"email\":\"jhimarcarlmotea23@gmail.com\",\"address\":\"152\"},\"payment_method\":\"cod\",\"payment_number\":\"\",\"subtotal\":85,\"delivery_fee\":38,\"promo_discount\":0,\"total\":123}', NULL, '2025-05-26 10:59:07', '2025-05-26 10:59:07'),
(18, 1, 'HP202505264011', 'cod', '', 170.00, 38.00, 0.00, 208.00, 'Pending', '{\"items\":[{\"foodId\":12,\"foodName\":\"Wingsilog\",\"price\":85,\"quantity\":2,\"image_path\":\"uploads\\/food\\/1747302425_WingSilog.png\",\"description\":\"crispy chicken wings, garlic fried rice, and a sunny-side-up egg, served with a side of creamy sauce\"}],\"customer\":{\"name\":\"Jhimar Carl Motea\",\"phone\":\"09982832054\",\"email\":\"jhimarcarlmotea23@gmail.com\",\"address\":\"152\"},\"payment_method\":\"cod\",\"payment_number\":\"\",\"subtotal\":170,\"delivery_fee\":38,\"promo_discount\":0,\"total\":208}', NULL, '2025-05-26 11:06:58', '2025-05-26 11:06:58'),
(19, 1, 'HP202505272662', 'cod', '', 820.00, 38.00, 0.00, 858.00, 'Pending', '{\"items\":[{\"foodId\":12,\"foodName\":\"Wingsilog\",\"price\":85,\"quantity\":5,\"image_path\":\"uploads\\/food\\/1747302425_WingSilog.png\",\"description\":\"crispy chicken wings, garlic fried rice, and a sunny-side-up egg, served with a side of creamy sauce\"},{\"foodId\":5,\"foodName\":\"Tapsilog\",\"price\":85,\"quantity\":1,\"image_path\":\"uploads\\/food\\/1747301579_Tapsilog.png\",\"description\":\"tender beef tapa, garlic fried rice, and a perfectly cooked sunny-side-up egg.\"},{\"foodId\":13,\"foodName\":\"Spamsilog\",\"price\":80,\"quantity\":1,\"image_path\":\"uploads\\/food\\/1747302458_SpamSilog.png\",\"description\":\"flavorful spam slices, garlic fried rice, and a sunny-side-up egg\"},{\"foodId\":8,\"foodName\":\"Sisigsilog\",\"price\":85,\"quantity\":1,\"image_path\":\"uploads\\/food\\/1747302292_SisigSilog.png\",\"description\":\"sizzling pork sisig, garlic fried rice, and a sunny-side-up egg\"},{\"foodId\":15,\"foodName\":\"Tosilog\",\"price\":75,\"quantity\":1,\"image_path\":\"uploads\\/food\\/1747302526_Tosilog.png\",\"description\":\"sweet and savory marinated tocino, garlic fried rice, and a sunny-side-up egg\"},{\"foodId\":19,\"foodName\":\"Longsilog\",\"price\":70,\"quantity\":1,\"image_path\":\"uploads\\/food\\/1747302799_LongSilog.png\",\"description\":\"sweet and savory longganisa, garlic fried rice, and a sunny-side-up egg, served with a side of spiced vinegar\"}],\"customer\":{\"name\":\"Jhimar Carl Motea\",\"phone\":\"09982832054\",\"email\":\"jhimarcarlmotea23@gmail.com\",\"address\":\"152\"},\"payment_method\":\"cod\",\"payment_number\":\"\",\"subtotal\":820,\"delivery_fee\":38,\"promo_discount\":0,\"total\":858}', NULL, '2025-05-27 08:20:42', '2025-05-27 08:20:42'),
(20, 1, 'HP202505275163', 'cod', '', 425.00, 38.00, 0.00, 463.00, 'Pending', '{\"items\":[{\"foodId\":12,\"foodName\":\"Wingsilog\",\"price\":85,\"quantity\":5,\"image_path\":\"uploads\\/food\\/1747302425_WingSilog.png\",\"description\":\"crispy chicken wings, garlic fried rice, and a sunny-side-up egg, served with a side of creamy sauce\"},{\"id\":36,\"foodName\":\"Coke 12oz (FREE - Taposilog Promo)\",\"price\":0,\"original_price\":75,\"quantity\":1,\"image_path\":\"coke.png\",\"category\":\"Beverages & Extra\",\"is_free_promo\":true,\"promo_sets\":1}],\"customer\":{\"name\":\"Jhimar Carl Motea\",\"phone\":\"09982832054\",\"email\":\"jhimarcarlmotea23@gmail.com\",\"address\":\"152\"},\"payment_method\":\"cod\",\"payment_number\":\"\",\"subtotal\":425,\"delivery_fee\":38,\"promo_discount\":0,\"total\":463,\"promotions_applied\":{\"free_cokes\":1,\"free_cokes_value\":75,\"taposilog_count\":6}}', NULL, '2025-05-27 08:33:41', '2025-05-27 08:33:41'),
(21, 1, 'HP202505277875', 'cod', '', 170.00, 38.00, 0.00, 208.00, 'Pending', '{\"items\":[{\"foodId\":12,\"foodName\":\"Wingsilog\",\"price\":85,\"quantity\":2,\"image_path\":\"uploads\\/food\\/1747302425_WingSilog.png\",\"description\":\"crispy chicken wings, garlic fried rice, and a sunny-side-up egg, served with a side of creamy sauce\"}],\"customer\":{\"name\":\"Jhimar Carl Motea\",\"phone\":\"09982832054\",\"email\":\"jhimarcarlmotea23@gmail.com\",\"address\":\"152\"},\"payment_method\":\"cod\",\"payment_number\":\"\",\"subtotal\":170,\"delivery_fee\":38,\"promo_discount\":0,\"total\":208,\"promotions_applied\":{\"free_cokes\":0,\"free_cokes_value\":0,\"taposilog_count\":2}}', NULL, '2025-05-27 08:34:40', '2025-05-27 08:34:40'),
(22, 1, 'HP202505279971', 'cod', '', 425.00, 38.00, 0.00, 463.00, 'Pending', '{\"items\":[{\"foodId\":12,\"foodName\":\"Wingsilog\",\"price\":85,\"quantity\":5,\"image_path\":\"uploads\\/food\\/1747302425_WingSilog.png\",\"description\":\"crispy chicken wings, garlic fried rice, and a sunny-side-up egg, served with a side of creamy sauce\"},{\"id\":36,\"foodName\":\"Coke 12oz (FREE - Taposilog Promo)\",\"price\":0,\"original_price\":75,\"quantity\":1,\"image_path\":\"coke.png\",\"category\":\"Beverages & Extra\",\"is_free_promo\":true,\"promo_sets\":1}],\"customer\":{\"name\":\"Jhimar Carl Motea\",\"phone\":\"09982832054\",\"email\":\"jhimarcarlmotea23@gmail.com\",\"address\":\"152\"},\"payment_method\":\"cod\",\"payment_number\":\"\",\"subtotal\":425,\"delivery_fee\":38,\"promo_discount\":0,\"total\":463,\"promotions_applied\":{\"free_cokes\":1,\"free_cokes_value\":75,\"taposilog_count\":6}}', NULL, '2025-05-27 08:37:52', '2025-05-27 08:37:52'),
(23, 1, 'HP202505277749', 'cod', '', 425.00, 38.00, 0.00, 463.00, 'Pending', '{\"items\":[{\"foodId\":12,\"foodName\":\"Wingsilog\",\"price\":85,\"quantity\":5,\"image_path\":\"uploads\\/food\\/1747302425_WingSilog.png\",\"description\":\"crispy chicken wings, garlic fried rice, and a sunny-side-up egg, served with a side of creamy sauce\"},{\"id\":36,\"foodName\":\"Coke 1.5 L (FREE - Taposilog Promo)\",\"price\":0,\"original_price\":75,\"quantity\":1,\"image_path\":\"coke.png\",\"category\":\"Beverages & Extra\",\"is_free_promo\":true,\"promo_sets\":1}],\"customer\":{\"name\":\"Jhimar Carl Motea\",\"phone\":\"09982832054\",\"email\":\"jhimarcarlmotea23@gmail.com\",\"address\":\"152\"},\"payment_method\":\"cod\",\"payment_number\":\"\",\"subtotal\":425,\"delivery_fee\":38,\"promo_discount\":0,\"total\":463,\"promotions_applied\":{\"free_cokes\":1,\"free_cokes_value\":75,\"taposilog_count\":6}}', NULL, '2025-05-27 08:38:40', '2025-05-27 08:38:40'),
(24, 1, 'HP202505274360', 'cod', '', 660.00, 38.00, 0.00, 698.00, 'Pending', '{\"items\":[{\"foodId\":30,\"foodName\":\"Bangus\",\"price\":60,\"quantity\":11,\"image_path\":\"uploads\\/food\\/1747303467_bangus.png\",\"description\":\"\"}],\"customer\":{\"name\":\"Jhimar Carl Motea\",\"phone\":\"09982832054\",\"email\":\"jhimarcarlmotea23@gmail.com\",\"address\":\"152\"},\"payment_method\":\"cod\",\"payment_number\":\"\",\"subtotal\":660,\"delivery_fee\":38,\"promo_discount\":0,\"total\":698,\"promotions_applied\":{\"free_cokes\":0,\"free_cokes_value\":0,\"taposilog_count\":0}}', NULL, '2025-05-27 11:21:11', '2025-05-27 11:21:11'),
(27, 1, 'HP202505274624', 'cod', '', 300.00, 38.00, 0.00, 338.00, 'Pending', '{\"items\":[{\"foodId\":30,\"foodName\":\"Bangus\",\"price\":60,\"quantity\":5,\"image_path\":\"uploads\\/food\\/1747303467_bangus.png\",\"description\":\"\"}],\"customer\":{\"name\":\"Jhimar Carl Motea\",\"phone\":\"09982832054\",\"email\":\"jhimarcarlmotea23@gmail.com\",\"address\":\"152\"},\"payment_method\":\"cod\",\"payment_number\":\"\",\"subtotal\":300,\"delivery_fee\":38,\"promo_discount\":0,\"total\":338,\"promotions_applied\":{\"free_cokes\":0,\"free_cokes_value\":0,\"taposilog_count\":0}}', NULL, '2025-05-27 11:36:49', '2025-05-27 11:36:49'),
(28, 1, 'HP202505275395', 'cod', '', 170.00, 38.00, 0.00, 208.00, 'Pending', '{\"items\":[{\"foodId\":25,\"foodName\":\"Chicken\",\"price\":17,\"quantity\":10,\"image_path\":\"uploads\\/food\\/1747303336_chicken.png\",\"description\":\"\"}],\"customer\":{\"name\":\"Jhimar Carl Motea\",\"phone\":\"09982832054\",\"email\":\"jhimarcarlmotea23@gmail.com\",\"address\":\"152\"},\"payment_method\":\"cod\",\"payment_number\":\"\",\"subtotal\":170,\"delivery_fee\":38,\"promo_discount\":0,\"total\":208,\"promotions_applied\":{\"free_cokes\":0,\"free_cokes_value\":0,\"taposilog_count\":0}}', NULL, '2025-05-27 11:37:30', '2025-05-27 11:37:30'),
(29, 1, 'HP202505279752', 'cod', '', 425.00, 38.00, 0.00, 463.00, 'Pending', '{\"items\":[{\"foodId\":12,\"foodName\":\"Wingsilog\",\"price\":85,\"quantity\":5,\"image_path\":\"uploads\\/food\\/1747302425_WingSilog.png\",\"description\":\"crispy chicken wings, garlic fried rice, and a sunny-side-up egg, served with a side of creamy sauce\"},{\"foodId\":36,\"foodName\":\"Coke 1.5 L (FREE - Taposilog Promo)\",\"price\":0,\"original_price\":75,\"quantity\":1,\"image_path\":\"coke.png\",\"category\":\"Beverages & Extra\",\"is_free_promo\":true,\"promo_sets\":1}],\"customer\":{\"name\":\"Jhimar Carl Motea\",\"phone\":\"09982832054\",\"email\":\"jhimarcarlmotea23@gmail.com\",\"address\":\"152\"},\"payment_method\":\"cod\",\"payment_number\":\"\",\"subtotal\":425,\"delivery_fee\":38,\"promo_discount\":0,\"total\":463,\"promotions_applied\":{\"free_cokes\":1,\"free_cokes_value\":75,\"taposilog_count\":6}}', NULL, '2025-05-27 11:45:45', '2025-05-27 11:45:45'),
(30, 1, 'HP202505273885', 'cod', '', 425.00, 38.00, 0.00, 463.00, 'Pending', '{\"items\":[{\"foodId\":12,\"foodName\":\"Wingsilog\",\"price\":85,\"quantity\":5,\"image_path\":\"uploads\\/food\\/1747302425_WingSilog.png\",\"description\":\"crispy chicken wings, garlic fried rice, and a sunny-side-up egg, served with a side of creamy sauce\"},{\"foodId\":36,\"foodName\":\"Coke 1.5 L (FREE - Taposilog Promo)\",\"price\":0,\"original_price\":75,\"quantity\":1,\"image_path\":\"coke.png\",\"category\":\"Beverages & Extra\",\"is_free_promo\":true,\"promo_sets\":1}],\"customer\":{\"name\":\"Jhimar Carl Motea\",\"phone\":\"09982832054\",\"email\":\"jhimarcarlmotea23@gmail.com\",\"address\":\"152\"},\"payment_method\":\"cod\",\"payment_number\":\"\",\"subtotal\":425,\"delivery_fee\":38,\"promo_discount\":0,\"total\":463,\"promotions_applied\":{\"free_cokes\":1,\"free_cokes_value\":75,\"taposilog_count\":6}}', NULL, '2025-05-27 11:51:16', '2025-05-27 11:51:16'),
(31, 1, 'HP202505274266', 'cod', '', 360.00, 38.00, 0.00, 398.00, 'Pending', '{\"items\":[{\"foodId\":30,\"foodName\":\"Bangus\",\"price\":60,\"quantity\":6,\"image_path\":\"uploads\\/food\\/1747303467_bangus.png\",\"description\":\"\"}],\"customer\":{\"name\":\"Jhimar Carl Motea\",\"phone\":\"09982832054\",\"email\":\"jhimarcarlmotea23@gmail.com\",\"address\":\"152\"},\"payment_method\":\"cod\",\"payment_number\":\"\",\"subtotal\":360,\"delivery_fee\":38,\"promo_discount\":0,\"total\":398,\"promotions_applied\":{\"free_cokes\":0,\"free_cokes_value\":0,\"taposilog_count\":0}}', NULL, '2025-05-27 11:52:48', '2025-05-27 11:52:48'),
(32, 1, 'HP202505277221', 'cod', '', 85.00, 38.00, 0.00, 123.00, 'Pending', '{\"items\":[{\"foodId\":12,\"foodName\":\"Wingsilog\",\"price\":85,\"quantity\":1,\"image_path\":\"uploads\\/food\\/1747302425_WingSilog.png\",\"description\":\"crispy chicken wings, garlic fried rice, and a sunny-side-up egg, served with a side of creamy sauce\"}],\"customer\":{\"name\":\"Jhimar Carl Motea\",\"phone\":\"09982832054\",\"email\":\"jhimarcarlmotea23@gmail.com\",\"address\":\"152\"},\"payment_method\":\"cod\",\"payment_number\":\"\",\"subtotal\":85,\"delivery_fee\":38,\"promo_discount\":0,\"total\":123,\"promotions_applied\":{\"free_cokes\":0,\"free_cokes_value\":0,\"taposilog_count\":1}}', NULL, '2025-05-27 11:59:01', '2025-05-27 11:59:01'),
(33, 1, 'HP202505273133', 'cod', '', 0.00, 38.00, 0.00, 38.00, 'Pending', '{\"items\":[],\"customer\":{\"name\":\"Jhimar Carl Motea\",\"phone\":\"09982832054\",\"email\":\"jhimarcarlmotea23@gmail.com\",\"address\":\"152\"},\"payment_method\":\"cod\",\"payment_number\":\"\",\"subtotal\":0,\"delivery_fee\":38,\"promo_discount\":0,\"total\":38,\"promotions_applied\":{\"free_cokes\":0,\"free_cokes_value\":0,\"taposilog_count\":0}}', NULL, '2025-05-27 11:59:05', '2025-05-27 11:59:05'),
(34, 1, 'HP202505276615', 'cod', '', 170.00, 38.00, 0.00, 208.00, 'Pending', '{\"items\":[{\"foodId\":25,\"foodName\":\"Chicken\",\"price\":17,\"quantity\":10,\"image_path\":\"uploads\\/food\\/1747303336_chicken.png\",\"description\":\"\"}],\"customer\":{\"name\":\"Jhimar Carl Motea\",\"phone\":\"09982832054\",\"email\":\"jhimarcarlmotea23@gmail.com\",\"address\":\"152\"},\"payment_method\":\"cod\",\"payment_number\":\"\",\"subtotal\":170,\"delivery_fee\":38,\"promo_discount\":0,\"total\":208,\"promotions_applied\":{\"free_cokes\":0,\"free_cokes_value\":0,\"taposilog_count\":0}}', NULL, '2025-05-27 12:58:17', '2025-05-27 12:58:17'),
(35, 1, 'HP202505273238', 'cod', '', 375.00, 38.00, 0.00, 413.00, 'Pending', '{\"items\":[{\"foodId\":4,\"foodName\":\"Bacon Silog\",\"price\":75,\"quantity\":5,\"image_path\":\"uploads\\/food\\/1747301091_BaconSilog.png\",\"description\":\"crispy, savory bacon, garlic fried rice, and a sunny-side-up egg\\\\r\\\\n\"},{\"foodId\":36,\"foodName\":\"Coke 1.5 L (FREE - Taposilog Promo)\",\"price\":0,\"original_price\":75,\"quantity\":1,\"image_path\":\"coke.png\",\"category\":\"Beverages & Extra\",\"is_free_promo\":true,\"promo_sets\":1}],\"customer\":{\"name\":\"Jhimar Carl Motea\",\"phone\":\"09982832054\",\"email\":\"jhimarcarlmotea23@gmail.com\",\"address\":\"152\"},\"payment_method\":\"cod\",\"payment_number\":\"\",\"subtotal\":375,\"delivery_fee\":38,\"promo_discount\":0,\"total\":413,\"promotions_applied\":{\"free_cokes\":1,\"free_cokes_value\":75,\"taposilog_count\":6}}', NULL, '2025-05-27 13:48:06', '2025-05-27 13:48:06'),
(36, 2, 'HP202505282068', 'cod', '', 440.00, 38.00, 0.00, 478.00, 'Pending', '{\"items\":[{\"foodId\":35,\"foodName\":\"Bacon\",\"price\":55,\"quantity\":8,\"image_path\":\"uploads\\/food\\/1747303927_bacon.png\",\"description\":\"\"}],\"customer\":{\"name\":\"Jhimar Motea\",\"phone\":\"09982832054\",\"email\":\"jhimar.motea23@gmail.com\",\"address\":\"152\"},\"payment_method\":\"cod\",\"payment_number\":\"\",\"subtotal\":440,\"delivery_fee\":38,\"promo_discount\":0,\"total\":478,\"promotions_applied\":{\"free_cokes\":0,\"free_cokes_value\":0,\"taposilog_count\":0}}', NULL, '2025-05-28 02:04:01', '2025-05-28 02:04:01'),
(37, 1, 'HP202505287302', 'cod', '', 800.00, 38.00, 0.00, 838.00, 'Pending', '{\"items\":[{\"foodId\":14,\"foodName\":\"Embosilog\",\"price\":80,\"quantity\":10,\"image_path\":\"uploads\\/food\\/1747302487_EmboSilog.png\",\"description\":\"flavorful embotido slices, garlic fried rice, and a sunny-side-up egg, serve with a side of creamy sauce\"},{\"foodId\":36,\"foodName\":\"Coke 1.5 L (FREE - Taposilog Promo)\",\"price\":0,\"original_price\":75,\"quantity\":2,\"image_path\":\"coke.png\",\"category\":\"Beverages & Extra\",\"is_free_promo\":true,\"promo_sets\":2}],\"customer\":{\"name\":\"Jhimar Carl Motea\",\"phone\":\"09982832054\",\"email\":\"jhimarcarlmotea23@gmail.com\",\"address\":\"152 Tandang Sora St\"},\"payment_method\":\"cod\",\"payment_number\":\"\",\"subtotal\":800,\"delivery_fee\":38,\"promo_discount\":0,\"total\":838,\"promotions_applied\":{\"free_cokes\":2,\"free_cokes_value\":150,\"taposilog_count\":12}}', NULL, '2025-05-28 08:38:44', '2025-05-28 08:38:44'),
(38, 1, 'HP202505289580', 'cod', '', 0.00, 38.00, 0.00, 38.00, 'Pending', '{\"items\":[],\"customer\":{\"name\":\"Jhimar Carl Motea\",\"phone\":\"09982832054\",\"email\":\"jhimarcarlmotea23@gmail.com\",\"address\":\"152 Tandang Sora St\"},\"payment_method\":\"cod\",\"payment_number\":\"\",\"subtotal\":0,\"delivery_fee\":38,\"promo_discount\":0,\"total\":38,\"promotions_applied\":{\"free_cokes\":0,\"free_cokes_value\":0,\"taposilog_count\":0}}', NULL, '2025-05-28 08:38:47', '2025-05-28 08:38:47'),
(39, 1, 'HP202505289403', 'cod', '', 595.00, 38.00, 0.00, 633.00, 'Pending', '{\"items\":[{\"foodId\":10,\"foodName\":\"Bangsilog\",\"price\":85,\"quantity\":7,\"image_path\":\"uploads\\/food\\/1747302363_BangSilog.png\",\"description\":\"crispy fried bangus, garlic fried rice, and a sunny-side-up egg, served with a side of tangy vinegar and fresh tomatoes\\\\\\\\r\\\\\\\\n\"},{\"foodId\":36,\"foodName\":\"Coke 1.5 L (FREE - Taposilog Promo)\",\"price\":0,\"original_price\":75,\"quantity\":1,\"image_path\":\"coke.png\",\"category\":\"Beverages & Extra\",\"is_free_promo\":true,\"promo_sets\":1}],\"customer\":{\"name\":\"Jhimar Carl Motea\",\"phone\":\"09982832054\",\"email\":\"jhimarcarlmotea23@gmail.com\",\"address\":\"152 hahahaha\"},\"payment_method\":\"cod\",\"payment_number\":\"\",\"subtotal\":595,\"delivery_fee\":38,\"promo_discount\":0,\"total\":633,\"promotions_applied\":{\"free_cokes\":1,\"free_cokes_value\":75,\"taposilog_count\":8}}', NULL, '2025-05-28 08:39:40', '2025-05-28 08:39:40'),
(40, 1, 'HP202505288866', 'cod', '', 477.00, 38.00, 0.00, 515.00, 'Pending', '{\"items\":[{\"foodId\":20,\"foodName\":\"Beshy Sisig Good for 2 pax\",\"price\":159,\"quantity\":3,\"image_path\":\"uploads\\/food\\/1747302899_Beshy Sisig.png\",\"description\":\"Enjoy our Beshy Sisig a sizzling platter of crispy and savory pork sisig, perfect for sharing, served with a side of calamansi and chili\"}],\"customer\":{\"name\":\"\",\"phone\":\"\",\"email\":\"\",\"address\":\"152 qqqqq\"},\"payment_method\":\"cod\",\"payment_number\":\"\",\"subtotal\":477,\"delivery_fee\":38,\"promo_discount\":0,\"total\":515,\"promotions_applied\":{\"free_cokes\":0,\"free_cokes_value\":0,\"taposilog_count\":0}}', NULL, '2025-05-28 08:43:25', '2025-05-28 08:43:25'),
(41, 1, 'HP202505284224', 'cod', '', 477.00, 38.00, 0.00, 515.00, 'Pending', '{\"items\":[{\"foodId\":20,\"foodName\":\"Beshy Sisig Good for 2 pax\",\"price\":159,\"quantity\":3,\"image_path\":\"uploads\\/food\\/1747302899_Beshy Sisig.png\",\"description\":\"Enjoy our Beshy Sisig a sizzling platter of crispy and savory pork sisig, perfect for sharing, served with a side of calamansi and chili\"}],\"customer\":{\"name\":\"\",\"phone\":\"\",\"email\":\"\",\"address\":\"152 aaaaa\"},\"payment_method\":\"cod\",\"payment_number\":\"\",\"subtotal\":477,\"delivery_fee\":38,\"promo_discount\":0,\"total\":515,\"promotions_applied\":{\"free_cokes\":0,\"free_cokes_value\":0,\"taposilog_count\":0}}', NULL, '2025-05-28 08:43:51', '2025-05-28 08:43:51'),
(42, 1, 'HP202505287000', 'cod', '', 425.00, 38.00, 0.00, 463.00, 'Pending', '{\"items\":[{\"foodId\":10,\"foodName\":\"Bangsilog\",\"price\":85,\"quantity\":5,\"image_path\":\"uploads\\/food\\/1747302363_BangSilog.png\",\"description\":\"crispy fried bangus, garlic fried rice, and a sunny-side-up egg, served with a side of tangy vinegar and fresh tomatoes\\\\\\\\r\\\\\\\\n\"},{\"foodId\":36,\"foodName\":\"Coke 1.5 L (FREE - Taposilog Promo)\",\"price\":0,\"original_price\":75,\"quantity\":1,\"image_path\":\"coke.png\",\"category\":\"Beverages & Extra\",\"is_free_promo\":true,\"promo_sets\":1}],\"customer\":{\"name\":\"\",\"phone\":\"\",\"email\":\"\",\"address\":\"152 aaaaa\"},\"payment_method\":\"cod\",\"payment_number\":\"\",\"subtotal\":425,\"delivery_fee\":38,\"promo_discount\":0,\"total\":463,\"promotions_applied\":{\"free_cokes\":1,\"free_cokes_value\":75,\"taposilog_count\":6}}', NULL, '2025-05-28 08:46:14', '2025-05-28 08:46:14'),
(43, 1, 'HP202505288563', 'cod', '', 0.00, 38.00, 0.00, 38.00, 'Pending', '{\"items\":[],\"customer\":{\"name\":\"\",\"phone\":\"\",\"email\":\"\",\"address\":\"152aaa\"},\"payment_method\":\"cod\",\"payment_number\":\"\",\"subtotal\":0,\"delivery_fee\":38,\"promo_discount\":0,\"total\":38,\"promotions_applied\":{\"free_cokes\":0,\"free_cokes_value\":0,\"taposilog_count\":0}}', NULL, '2025-05-28 08:46:38', '2025-05-28 08:46:38'),
(44, 1, 'HP202505285723', 'cod', '', 425.00, 38.00, 0.00, 463.00, 'Pending', '{\"items\":[{\"foodId\":10,\"foodName\":\"Bangsilog\",\"price\":85,\"quantity\":5,\"image_path\":\"uploads\\/food\\/1747302363_BangSilog.png\",\"description\":\"crispy fried bangus, garlic fried rice, and a sunny-side-up egg, served with a side of tangy vinegar and fresh tomatoes\\\\\\\\r\\\\\\\\n\"},{\"foodId\":36,\"foodName\":\"Coke 1.5 L (FREE - Taposilog Promo)\",\"price\":0,\"original_price\":75,\"quantity\":1,\"image_path\":\"coke.png\",\"category\":\"Beverages & Extra\",\"is_free_promo\":true,\"promo_sets\":1}],\"customer\":{\"name\":\"\",\"phone\":\"\",\"email\":\"\",\"address\":\"152 sdfgh\"},\"payment_method\":\"cod\",\"payment_number\":\"\",\"subtotal\":425,\"delivery_fee\":38,\"promo_discount\":0,\"total\":463,\"promotions_applied\":{\"free_cokes\":1,\"free_cokes_value\":75,\"taposilog_count\":6}}', NULL, '2025-05-28 08:49:54', '2025-05-28 08:49:54'),
(45, 1, 'HP202505289329', 'cod', '', 340.00, 38.00, 0.00, 378.00, 'Pending', '{\"items\":[{\"foodId\":10,\"foodName\":\"Bangsilog\",\"price\":85,\"quantity\":4,\"image_path\":\"uploads\\/food\\/1747302363_BangSilog.png\",\"description\":\"crispy fried bangus, garlic fried rice, and a sunny-side-up egg, served with a side of tangy vinegar and fresh tomatoes\\\\\\\\r\\\\\\\\n\"}],\"customer\":{\"name\":\"\",\"phone\":\"\",\"email\":\"\",\"address\":\"152 abs\"},\"payment_method\":\"cod\",\"payment_number\":\"\",\"subtotal\":340,\"delivery_fee\":38,\"promo_discount\":0,\"total\":378,\"promotions_applied\":{\"free_cokes\":0,\"free_cokes_value\":0,\"taposilog_count\":4}}', NULL, '2025-05-28 08:55:10', '2025-05-28 08:55:10'),
(46, 1, 'HP202505285513', 'cod', '', 510.00, 38.00, 0.00, 548.00, 'Pending', '{\"items\":[{\"foodId\":10,\"foodName\":\"Bangsilog\",\"price\":85,\"quantity\":6,\"image_path\":\"uploads\\/food\\/1747302363_BangSilog.png\",\"description\":\"crispy fried bangus, garlic fried rice, and a sunny-side-up egg, served with a side of tangy vinegar and fresh tomatoes\\\\\\\\r\\\\\\\\n\"},{\"foodId\":36,\"foodName\":\"Coke 1.5 L (FREE - Taposilog Promo)\",\"price\":0,\"original_price\":75,\"quantity\":1,\"image_path\":\"coke.png\",\"category\":\"Beverages & Extra\",\"is_free_promo\":true,\"promo_sets\":1}],\"customer\":{\"name\":\"Jhimar Carl Motea\",\"phone\":\"09982832054\",\"email\":\"jhimarcarlmotea23@gmail.com\",\"address\":\"152\"},\"payment_method\":\"cod\",\"payment_number\":\"\",\"subtotal\":510,\"delivery_fee\":38,\"promo_discount\":0,\"total\":548,\"promotions_applied\":{\"free_cokes\":1,\"free_cokes_value\":75,\"taposilog_count\":7}}', NULL, '2025-05-28 08:56:10', '2025-05-28 08:56:10'),
(47, 1, 'HP202505286461', 'cod', '', 954.00, 38.00, 0.00, 992.00, 'Pending', '{\"items\":[{\"foodId\":20,\"foodName\":\"Beshy Sisig Good for 2 pax\",\"price\":159,\"quantity\":6,\"image_path\":\"uploads\\/food\\/1747302899_Beshy Sisig.png\",\"description\":\"Enjoy our Beshy Sisig a sizzling platter of crispy and savory pork sisig, perfect for sharing, served with a side of calamansi and chili\"}],\"customer\":{\"name\":\"\",\"phone\":\"\",\"email\":\"\",\"address\":\"152 aaaa\"},\"payment_method\":\"cod\",\"payment_number\":\"\",\"subtotal\":954,\"delivery_fee\":38,\"promo_discount\":0,\"total\":992,\"promotions_applied\":{\"free_cokes\":0,\"free_cokes_value\":0,\"taposilog_count\":0}}', NULL, '2025-05-28 08:56:52', '2025-05-28 08:56:52'),
(48, 1, 'HP202505281767', 'cod', '', 340.00, 38.00, 0.00, 378.00, 'Pending', '{\"items\":[{\"foodId\":10,\"foodName\":\"Bangsilog\",\"price\":85,\"quantity\":4,\"image_path\":\"uploads\\/food\\/1747302363_BangSilog.png\",\"description\":\"crispy fried bangus, garlic fried rice, and a sunny-side-up egg, served with a side of tangy vinegar and fresh tomatoes\\\\\\\\r\\\\\\\\n\"}],\"customer\":{\"name\":\"\",\"phone\":\"\",\"email\":\"\",\"address\":\"152 aaaaa\"},\"payment_method\":\"cod\",\"payment_number\":\"\",\"subtotal\":340,\"delivery_fee\":38,\"promo_discount\":0,\"total\":378,\"promotions_applied\":{\"free_cokes\":0,\"free_cokes_value\":0,\"taposilog_count\":4}}', NULL, '2025-05-28 09:00:28', '2025-05-28 09:00:28'),
(49, 1, 'HP202505285984', 'cod', '', 425.00, 38.00, 0.00, 463.00, 'Pending', '{\"items\":[{\"foodId\":10,\"foodName\":\"Bangsilog\",\"price\":85,\"quantity\":5,\"image_path\":\"uploads\\/food\\/1747302363_BangSilog.png\",\"description\":\"crispy fried bangus, garlic fried rice, and a sunny-side-up egg, served with a side of tangy vinegar and fresh tomatoes\\\\\\\\r\\\\\\\\n\"},{\"foodId\":36,\"foodName\":\"Coke 1.5 L (FREE - Taposilog Promo)\",\"price\":0,\"original_price\":75,\"quantity\":1,\"image_path\":\"coke.png\",\"category\":\"Beverages & Extra\",\"is_free_promo\":true,\"promo_sets\":1}],\"customer\":{\"name\":\"\",\"phone\":\"\",\"email\":\"\",\"address\":\"152 qqqqq\"},\"payment_method\":\"cod\",\"payment_number\":\"\",\"subtotal\":425,\"delivery_fee\":38,\"promo_discount\":0,\"total\":463,\"promotions_applied\":{\"free_cokes\":1,\"free_cokes_value\":75,\"taposilog_count\":6}}', NULL, '2025-05-28 09:03:46', '2025-05-28 09:03:46'),
(50, 1, 'HP202505289416', 'cod', '', 510.00, 38.00, 0.00, 548.00, 'Pending', '{\"items\":[{\"foodId\":10,\"foodName\":\"Bangsilog\",\"price\":85,\"quantity\":6,\"image_path\":\"uploads\\/food\\/1747302363_BangSilog.png\",\"description\":\"crispy fried bangus, garlic fried rice, and a sunny-side-up egg, served with a side of tangy vinegar and fresh tomatoes\\\\\\\\r\\\\\\\\n\"},{\"foodId\":36,\"foodName\":\"Coke 1.5 L (FREE - Taposilog Promo)\",\"price\":0,\"original_price\":75,\"quantity\":1,\"image_path\":\"coke.png\",\"category\":\"Beverages & Extra\",\"is_free_promo\":true,\"promo_sets\":1}],\"customer\":{\"name\":\"Jhimar Carl Motea\",\"phone\":\"09982832054\",\"email\":\"jhimarcarlmotea23@gmail.com\",\"address\":\"152\"},\"payment_method\":\"cod\",\"payment_number\":\"\",\"subtotal\":510,\"delivery_fee\":38,\"promo_discount\":0,\"total\":548,\"promotions_applied\":{\"free_cokes\":1,\"free_cokes_value\":75,\"taposilog_count\":7}}', NULL, '2025-05-28 09:04:04', '2025-05-28 09:04:04'),
(51, 1, 'HP202505283246', 'cod', '', 255.00, 38.00, 0.00, 293.00, 'Pending', '{\"items\":[{\"foodId\":10,\"foodName\":\"Bangsilog\",\"price\":85,\"quantity\":3,\"image_path\":\"uploads\\/food\\/1747302363_BangSilog.png\",\"description\":\"crispy fried bangus, garlic fried rice, and a sunny-side-up egg, served with a side of tangy vinegar and fresh tomatoes\\\\\\\\r\\\\\\\\n\"}],\"customer\":{\"name\":\"\",\"phone\":\"\",\"email\":\"\",\"address\":\"152aaa\"},\"payment_method\":\"cod\",\"payment_number\":\"\",\"subtotal\":255,\"delivery_fee\":38,\"promo_discount\":0,\"total\":293,\"promotions_applied\":{\"free_cokes\":0,\"free_cokes_value\":0,\"taposilog_count\":3}}', NULL, '2025-05-28 09:06:07', '2025-05-28 09:06:07'),
(52, 1, 'HP202505289966', 'cod', '', 425.00, 38.00, 0.00, 463.00, 'Pending', '{\"items\":[{\"foodId\":10,\"foodName\":\"Bangsilog\",\"price\":85,\"quantity\":5,\"image_path\":\"uploads\\/food\\/1747302363_BangSilog.png\",\"description\":\"crispy fried bangus, garlic fried rice, and a sunny-side-up egg, served with a side of tangy vinegar and fresh tomatoes\\\\\\\\r\\\\\\\\n\"},{\"foodId\":36,\"foodName\":\"Coke 1.5 L (FREE - Taposilog Promo)\",\"price\":0,\"original_price\":75,\"quantity\":1,\"image_path\":\"coke.png\",\"category\":\"Beverages & Extra\",\"is_free_promo\":true,\"promo_sets\":1}],\"customer\":{\"name\":\"\",\"phone\":\"\",\"email\":\"\",\"address\":\"152 aaaa\"},\"payment_method\":\"cod\",\"payment_number\":\"\",\"subtotal\":425,\"delivery_fee\":38,\"promo_discount\":0,\"total\":463,\"promotions_applied\":{\"free_cokes\":1,\"free_cokes_value\":75,\"taposilog_count\":6}}', NULL, '2025-05-28 09:17:56', '2025-05-28 09:17:56'),
(53, 1, 'HP202505288848', 'cod', '', 0.00, 38.00, 0.00, 38.00, 'Pending', '{\"items\":[],\"customer\":{\"name\":\"\",\"phone\":\"\",\"email\":\"\",\"address\":\"152gghgg\"},\"payment_method\":\"cod\",\"payment_number\":\"\",\"subtotal\":0,\"delivery_fee\":38,\"promo_discount\":0,\"total\":38,\"promotions_applied\":{\"free_cokes\":0,\"free_cokes_value\":0,\"taposilog_count\":0}}', NULL, '2025-05-28 09:24:49', '2025-05-28 09:24:49'),
(54, 1, 'HP202505285920', 'cod', '', 1113.00, 38.00, 0.00, 1151.00, 'Pending', '{\"items\":[{\"foodId\":20,\"foodName\":\"Beshy Sisig Good for 2 pax\",\"price\":159,\"quantity\":7,\"image_path\":\"uploads\\/food\\/1747302899_Beshy Sisig.png\",\"description\":\"Enjoy our Beshy Sisig a sizzling platter of crispy and savory pork sisig, perfect for sharing, served with a side of calamansi and chili\"}],\"customer\":{\"name\":\"Jhimar Carl Motea\",\"phone\":\"09982832054\",\"email\":\"jhimarcarlmotea23@gmail.com\",\"address\":\"152 abs\"},\"payment_method\":\"cod\",\"payment_number\":\"\",\"subtotal\":1113,\"delivery_fee\":38,\"promo_discount\":0,\"total\":1151,\"promotions_applied\":{\"free_cokes\":0,\"free_cokes_value\":0,\"taposilog_count\":0}}', NULL, '2025-05-28 10:47:39', '2025-05-28 10:47:39'),
(55, 1, 'HP202505287795', 'cod', '', 480.00, 38.00, 0.00, 518.00, 'Pending', '{\"items\":[{\"foodId\":14,\"foodName\":\"Embosilog\",\"price\":80,\"quantity\":6,\"image_path\":\"uploads\\/food\\/1747302487_EmboSilog.png\",\"description\":\"flavorful embotido slices, garlic fried rice, and a sunny-side-up egg, serve with a side of creamy sauce\"},{\"foodId\":36,\"foodName\":\"Coke 1.5 L (FREE - Taposilog Promo)\",\"price\":0,\"original_price\":75,\"quantity\":1,\"image_path\":\"coke.png\",\"category\":\"Beverages & Extra\",\"is_free_promo\":true,\"promo_sets\":1}],\"customer\":{\"name\":\"Jhimar Carl Motea\",\"phone\":\"09982832054\",\"email\":\"jhimarcarlmotea23@gmail.com\",\"address\":\"152 abanamsad\"},\"payment_method\":\"cod\",\"payment_number\":\"\",\"subtotal\":480,\"delivery_fee\":38,\"promo_discount\":0,\"total\":518,\"promotions_applied\":{\"free_cokes\":1,\"free_cokes_value\":75,\"taposilog_count\":7}}', NULL, '2025-05-28 10:57:41', '2025-05-28 10:57:41'),
(56, 1, 'HP202505289674', 'cod', '', 795.00, 38.00, 0.00, 833.00, 'Pending', '{\"items\":[{\"foodId\":20,\"foodName\":\"Beshy Sisig Good for 2 pax\",\"price\":159,\"quantity\":5,\"image_path\":\"uploads\\/food\\/1747302899_Beshy Sisig.png\",\"description\":\"Enjoy our Beshy Sisig a sizzling platter of crispy and savory pork sisig, perfect for sharing, served with a side of calamansi and chili\"}],\"customer\":{\"name\":\"Jhimar Carl Motea\",\"phone\":\"09982832054\",\"email\":\"jhimarcarlmotea23@gmail.com\",\"address\":\"152 abs\"},\"payment_method\":\"cod\",\"payment_number\":\"\",\"subtotal\":795,\"delivery_fee\":38,\"promo_discount\":0,\"total\":833,\"promotions_applied\":{\"free_cokes\":0,\"free_cokes_value\":0,\"taposilog_count\":0}}', NULL, '2025-05-28 11:04:32', '2025-05-28 11:04:32'),
(57, 1, 'HP202505282540', 'cod', '', 954.00, 38.00, 0.00, 992.00, 'Pending', '{\"items\":[{\"foodId\":20,\"foodName\":\"Beshy Sisig Good for 2 pax\",\"price\":159,\"quantity\":6,\"image_path\":\"uploads\\/food\\/1747302899_Beshy Sisig.png\",\"description\":\"Enjoy our Beshy Sisig a sizzling platter of crispy and savory pork sisig, perfect for sharing, served with a side of calamansi and chili\"}],\"customer\":{\"name\":\"Jhimar Carl Motea\",\"phone\":\"09982832054\",\"email\":\"jhimarcarlmotea23@gmail.com\",\"address\":\"152 tang ina\"},\"payment_method\":\"cod\",\"payment_number\":\"\",\"subtotal\":954,\"delivery_fee\":38,\"promo_discount\":0,\"total\":992,\"promotions_applied\":{\"free_cokes\":0,\"free_cokes_value\":0,\"taposilog_count\":0}}', NULL, '2025-05-28 11:08:57', '2025-05-28 11:08:57'),
(58, 2, 'HP202505288778', 'cod', '', 440.00, 38.00, 0.00, 478.00, 'Pending', '{\"items\":[{\"foodId\":35,\"foodName\":\"Bacon\",\"price\":55,\"quantity\":8,\"image_path\":\"uploads\\/food\\/1747303927_bacon.png\",\"description\":\"\"}],\"customer\":{\"name\":\"Jhimar Motea\",\"phone\":\"09982832054\",\"email\":\"jhimar.motea23@gmail.com\",\"address\":\"152 naeedit ka ba?\"},\"payment_method\":\"cod\",\"payment_number\":\"\",\"subtotal\":440,\"delivery_fee\":38,\"promo_discount\":0,\"total\":478,\"promotions_applied\":{\"free_cokes\":0,\"free_cokes_value\":0,\"taposilog_count\":0}}', NULL, '2025-05-28 12:39:44', '2025-05-28 12:39:44');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `food_name` varchar(100) NOT NULL,
  `food_price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_free_promo` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `food_name`, `food_price`, `quantity`, `subtotal`, `image_path`, `created_at`, `is_free_promo`) VALUES
(1, 1, 'Wingsilog', 85.00, 1, 85.00, 'uploads/food/1747302425_WingSilog.png', '2025-05-25 11:03:16', 0),
(2, 2, 'Wingsilog', 85.00, 2, 170.00, 'uploads/food/1747302425_WingSilog.png', '2025-05-25 11:37:12', 0),
(3, 3, 'Wingsilog', 85.00, 2, 170.00, 'uploads/food/1747302425_WingSilog.png', '2025-05-25 11:57:58', 0),
(4, 3, 'Tosilog', 75.00, 1, 75.00, 'uploads/food/1747302526_Tosilog.png', '2025-05-25 11:57:58', 0),
(5, 5, 'Wingsilog', 85.00, 1, 85.00, 'uploads/food/1747302425_WingSilog.png', '2025-05-25 13:36:38', 0),
(6, 5, 'Tosilog', 75.00, 1, 75.00, 'uploads/food/1747302526_Tosilog.png', '2025-05-25 13:36:38', 0),
(7, 6, 'Wingsilog', 85.00, 2, 170.00, 'uploads/food/1747302425_WingSilog.png', '2025-05-26 01:33:14', 0),
(8, 8, 'Tosilog', 75.00, 1, 75.00, 'uploads/food/1747302526_Tosilog.png', '2025-05-26 02:00:01', 0),
(9, 9, 'Wingsilog', 85.00, 2, 170.00, 'uploads/food/1747302425_WingSilog.png', '2025-05-26 09:30:38', 0),
(10, 10, 'Wingsilog', 85.00, 1, 85.00, 'uploads/food/1747302425_WingSilog.png', '2025-05-26 10:22:51', 0),
(11, 13, 'Wingsilog', 85.00, 2, 170.00, 'uploads/food/1747302425_WingSilog.png', '2025-05-26 10:31:43', 0),
(12, 14, 'Tapsilog', 85.00, 1, 85.00, 'uploads/food/1747301579_Tapsilog.png', '2025-05-26 10:37:03', 0),
(13, 15, 'Wingsilog', 85.00, 1, 85.00, 'uploads/food/1747302425_WingSilog.png', '2025-05-26 10:58:11', 0),
(14, 16, 'Wingsilog', 85.00, 1, 85.00, 'uploads/food/1747302425_WingSilog.png', '2025-05-26 10:58:43', 0),
(15, 17, 'Wingsilog', 85.00, 1, 85.00, 'uploads/food/1747302425_WingSilog.png', '2025-05-26 10:59:07', 0),
(16, 18, 'Wingsilog', 85.00, 2, 170.00, 'uploads/food/1747302425_WingSilog.png', '2025-05-26 11:06:58', 0),
(17, 19, 'Wingsilog', 85.00, 5, 425.00, 'uploads/food/1747302425_WingSilog.png', '2025-05-27 08:20:42', 0),
(18, 19, 'Tapsilog', 85.00, 1, 85.00, 'uploads/food/1747301579_Tapsilog.png', '2025-05-27 08:20:42', 0),
(19, 19, 'Spamsilog', 80.00, 1, 80.00, 'uploads/food/1747302458_SpamSilog.png', '2025-05-27 08:20:42', 0),
(20, 19, 'Sisigsilog', 85.00, 1, 85.00, 'uploads/food/1747302292_SisigSilog.png', '2025-05-27 08:20:42', 0),
(21, 19, 'Tosilog', 75.00, 1, 75.00, 'uploads/food/1747302526_Tosilog.png', '2025-05-27 08:20:42', 0),
(22, 19, 'Longsilog', 70.00, 1, 70.00, 'uploads/food/1747302799_LongSilog.png', '2025-05-27 08:20:42', 0),
(23, 20, 'Wingsilog', 85.00, 5, 425.00, 'uploads/food/1747302425_WingSilog.png', '2025-05-27 08:33:41', 0),
(24, 20, 'Coke 12oz (FREE - Taposilog Promo)', 0.00, 1, 0.00, 'coke.png', '2025-05-27 08:33:41', 0),
(25, 21, 'Wingsilog', 85.00, 2, 170.00, 'uploads/food/1747302425_WingSilog.png', '2025-05-27 08:34:40', 0),
(26, 22, 'Wingsilog', 85.00, 5, 425.00, 'uploads/food/1747302425_WingSilog.png', '2025-05-27 08:37:52', 0),
(27, 22, 'Coke 12oz (FREE - Taposilog Promo)', 0.00, 1, 0.00, 'coke.png', '2025-05-27 08:37:52', 0),
(28, 23, 'Wingsilog', 85.00, 5, 425.00, 'uploads/food/1747302425_WingSilog.png', '2025-05-27 08:38:40', 0),
(29, 23, 'Coke 1.5 L (FREE - Taposilog Promo)', 0.00, 1, 0.00, 'coke.png', '2025-05-27 08:38:40', 0),
(30, 24, 'Bangus', 60.00, 11, 660.00, 'uploads/food/1747303467_bangus.png', '2025-05-27 11:21:11', 0),
(31, 27, 'Bangus', 60.00, 5, 300.00, 'uploads/food/1747303467_bangus.png', '2025-05-27 05:36:49', 0),
(32, 28, 'Chicken', 17.00, 10, 170.00, 'uploads/food/1747303336_chicken.png', '2025-05-27 05:37:30', 0),
(33, 29, 'Wingsilog', 85.00, 5, 425.00, 'uploads/food/1747302425_WingSilog.png', '2025-05-27 05:45:45', 0),
(34, 29, 'Coke 1.5 L (FREE - Taposilog Promo)', 0.00, 1, 0.00, 'coke.png', '2025-05-27 05:45:45', 1),
(35, 30, 'Wingsilog', 85.00, 5, 425.00, 'uploads/food/1747302425_WingSilog.png', '2025-05-27 05:51:16', 0),
(36, 30, 'Coke 1.5 L (FREE - Taposilog Promo)', 0.00, 1, 0.00, 'coke.png', '2025-05-27 05:51:16', 1),
(37, 31, 'Bangus', 60.00, 6, 360.00, 'uploads/food/1747303467_bangus.png', '2025-05-27 05:52:48', 0),
(38, 32, 'Wingsilog', 85.00, 1, 85.00, 'uploads/food/1747302425_WingSilog.png', '2025-05-27 05:59:01', 0),
(39, 34, 'Chicken', 17.00, 10, 170.00, 'uploads/food/1747303336_chicken.png', '2025-05-27 06:58:17', 0),
(40, 35, 'Bacon Silog', 75.00, 5, 375.00, 'uploads/food/1747301091_BaconSilog.png', '2025-05-27 07:48:06', 0),
(41, 35, 'Coke 1.5 L (FREE - Taposilog Promo)', 0.00, 1, 0.00, 'coke.png', '2025-05-27 07:48:06', 1),
(42, 36, 'Bacon', 55.00, 8, 440.00, 'uploads/food/1747303927_bacon.png', '2025-05-27 20:04:01', 0),
(43, 37, 'Embosilog', 80.00, 10, 800.00, 'uploads/food/1747302487_EmboSilog.png', '2025-05-28 02:38:44', 0),
(44, 37, 'Coke 1.5 L (FREE - Taposilog Promo)', 0.00, 2, 0.00, 'coke.png', '2025-05-28 02:38:44', 1),
(45, 39, 'Bangsilog', 85.00, 7, 595.00, 'uploads/food/1747302363_BangSilog.png', '2025-05-28 02:39:40', 0),
(46, 39, 'Coke 1.5 L (FREE - Taposilog Promo)', 0.00, 1, 0.00, 'coke.png', '2025-05-28 02:39:40', 1),
(47, 40, 'Beshy Sisig Good for 2 pax', 159.00, 3, 477.00, 'uploads/food/1747302899_Beshy Sisig.png', '2025-05-28 02:43:25', 0),
(48, 41, 'Beshy Sisig Good for 2 pax', 159.00, 3, 477.00, 'uploads/food/1747302899_Beshy Sisig.png', '2025-05-28 02:43:51', 0),
(49, 42, 'Bangsilog', 85.00, 5, 425.00, 'uploads/food/1747302363_BangSilog.png', '2025-05-28 02:46:14', 0),
(50, 42, 'Coke 1.5 L (FREE - Taposilog Promo)', 0.00, 1, 0.00, 'coke.png', '2025-05-28 02:46:14', 1),
(51, 44, 'Bangsilog', 85.00, 5, 425.00, 'uploads/food/1747302363_BangSilog.png', '2025-05-28 02:49:54', 0),
(52, 44, 'Coke 1.5 L (FREE - Taposilog Promo)', 0.00, 1, 0.00, 'coke.png', '2025-05-28 02:49:54', 1),
(53, 45, 'Bangsilog', 85.00, 4, 340.00, 'uploads/food/1747302363_BangSilog.png', '2025-05-28 02:55:10', 0),
(54, 46, 'Bangsilog', 85.00, 6, 510.00, 'uploads/food/1747302363_BangSilog.png', '2025-05-28 02:56:10', 0),
(55, 46, 'Coke 1.5 L (FREE - Taposilog Promo)', 0.00, 1, 0.00, 'coke.png', '2025-05-28 02:56:10', 1),
(56, 47, 'Beshy Sisig Good for 2 pax', 159.00, 6, 954.00, 'uploads/food/1747302899_Beshy Sisig.png', '2025-05-28 02:56:52', 0),
(57, 48, 'Bangsilog', 85.00, 4, 340.00, 'uploads/food/1747302363_BangSilog.png', '2025-05-28 03:00:28', 0),
(58, 49, 'Bangsilog', 85.00, 5, 425.00, 'uploads/food/1747302363_BangSilog.png', '2025-05-28 03:03:46', 0),
(59, 49, 'Coke 1.5 L (FREE - Taposilog Promo)', 0.00, 1, 0.00, 'coke.png', '2025-05-28 03:03:46', 1),
(60, 50, 'Bangsilog', 85.00, 6, 510.00, 'uploads/food/1747302363_BangSilog.png', '2025-05-28 03:04:04', 0),
(61, 50, 'Coke 1.5 L (FREE - Taposilog Promo)', 0.00, 1, 0.00, 'coke.png', '2025-05-28 03:04:04', 1),
(62, 51, 'Bangsilog', 85.00, 3, 255.00, 'uploads/food/1747302363_BangSilog.png', '2025-05-28 03:06:07', 0),
(63, 52, 'Bangsilog', 85.00, 5, 425.00, 'uploads/food/1747302363_BangSilog.png', '2025-05-28 03:17:56', 0),
(64, 52, 'Coke 1.5 L (FREE - Taposilog Promo)', 0.00, 1, 0.00, 'coke.png', '2025-05-28 03:17:56', 1),
(65, 54, 'Beshy Sisig Good for 2 pax', 159.00, 7, 1113.00, 'uploads/food/1747302899_Beshy Sisig.png', '2025-05-28 04:47:39', 0),
(66, 55, 'Embosilog', 80.00, 6, 480.00, 'uploads/food/1747302487_EmboSilog.png', '2025-05-28 04:57:41', 0),
(67, 55, 'Coke 1.5 L (FREE - Taposilog Promo)', 0.00, 1, 0.00, 'coke.png', '2025-05-28 04:57:41', 1),
(68, 56, 'Beshy Sisig Good for 2 pax', 159.00, 5, 795.00, 'uploads/food/1747302899_Beshy Sisig.png', '2025-05-28 05:04:32', 0),
(69, 57, 'Beshy Sisig Good for 2 pax', 159.00, 6, 954.00, 'uploads/food/1747302899_Beshy Sisig.png', '2025-05-28 05:08:57', 0),
(70, 58, 'Bacon', 55.00, 8, 440.00, 'uploads/food/1747303927_bacon.png', '2025-05-28 06:39:44', 0);

-- --------------------------------------------------------

--
-- Table structure for table `order_status_history`
--

CREATE TABLE `order_status_history` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `status` varchar(50) NOT NULL,
  `notes` text DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_status_history`
--

INSERT INTO `order_status_history` (`id`, `order_id`, `status`, `notes`, `updated_by`, `created_at`, `updated_at`) VALUES
(4, 5, 'Pending', 'Order placed successfully', 1, '2025-05-25 13:36:38', '2025-05-26 01:49:35'),
(5, 3, 'Cancelled', '', 1, '2025-05-25 13:39:33', '2025-05-26 01:49:35'),
(6, 3, 'Delivered', '', 1, '2025-05-25 13:39:40', '2025-05-26 01:49:35'),
(7, 3, 'Delivered', '', 1, '2025-05-25 13:40:11', '2025-05-26 01:49:35'),
(8, 6, 'Pending', 'Order placed successfully', 5, '2025-05-26 01:33:14', '2025-05-26 01:49:35'),
(9, 7, 'Pending', 'Order placed successfully', 5, '2025-05-26 01:33:34', '2025-05-26 01:49:35'),
(10, 8, 'Pending', 'Order placed successfully', 1, '2025-05-26 02:00:01', '2025-05-26 02:00:01'),
(11, 8, 'Delivered', '', 1, '2025-05-26 09:15:02', '2025-05-26 09:15:02'),
(12, 9, 'Pending', 'Order placed successfully', 1, '2025-05-26 09:30:38', '2025-05-26 09:30:38'),
(13, 10, 'Pending', 'Order placed successfully', 1, '2025-05-26 10:22:51', '2025-05-26 10:22:51'),
(14, 11, 'Pending', 'Order placed successfully', 1, '2025-05-26 10:23:31', '2025-05-26 10:23:31'),
(15, 12, 'Pending', 'Order placed successfully', 1, '2025-05-26 10:23:57', '2025-05-26 10:23:57'),
(16, 13, 'Pending', 'Order placed successfully', 1, '2025-05-26 10:31:43', '2025-05-26 10:31:43'),
(17, 14, 'Pending', 'Order placed successfully', 1, '2025-05-26 10:37:03', '2025-05-26 10:37:03'),
(18, 15, 'Pending', 'Order placed successfully', 1, '2025-05-26 10:58:11', '2025-05-26 10:58:11'),
(19, 16, 'Pending', 'Order placed successfully', 1, '2025-05-26 10:58:43', '2025-05-26 10:58:43'),
(20, 17, 'Pending', 'Order placed successfully', 1, '2025-05-26 10:59:07', '2025-05-26 10:59:07'),
(21, 18, 'Pending', 'Order placed successfully', 1, '2025-05-26 11:06:58', '2025-05-26 11:06:58'),
(22, 19, 'Pending', 'Order placed successfully', 1, '2025-05-27 08:20:42', '2025-05-27 08:20:42'),
(23, 20, 'Pending', 'Order placed successfully', 1, '2025-05-27 08:33:41', '2025-05-27 08:33:41'),
(24, 21, 'Pending', 'Order placed successfully', 1, '2025-05-27 08:34:40', '2025-05-27 08:34:40'),
(25, 22, 'Pending', 'Order placed successfully', 1, '2025-05-27 08:37:52', '2025-05-27 08:37:52'),
(26, 23, 'Pending', 'Order placed successfully', 1, '2025-05-27 08:38:40', '2025-05-27 08:38:40'),
(27, 24, 'Pending', 'Order placed successfully', 1, '2025-05-27 11:21:11', '2025-05-27 11:21:11'),
(28, 27, 'Pending', 'Order placed successfully', 1, '2025-05-27 11:36:49', '2025-05-27 11:36:49'),
(29, 28, 'Pending', 'Order placed successfully', 1, '2025-05-27 11:37:30', '2025-05-27 11:37:30'),
(30, 29, 'Pending', 'Order placed successfully', 1, '2025-05-27 11:45:45', '2025-05-27 11:45:45'),
(31, 30, 'Pending', 'Order placed successfully', 1, '2025-05-27 11:51:16', '2025-05-27 11:51:16'),
(32, 31, 'Pending', 'Order placed successfully', 1, '2025-05-27 11:52:48', '2025-05-27 11:52:48'),
(33, 32, 'Pending', 'Order placed successfully', 1, '2025-05-27 11:59:01', '2025-05-27 11:59:01'),
(34, 33, 'Pending', 'Order placed successfully', 1, '2025-05-27 11:59:05', '2025-05-27 11:59:05'),
(35, 34, 'Pending', 'Order placed successfully', 1, '2025-05-27 12:58:17', '2025-05-27 12:58:17'),
(36, 35, 'Pending', 'Order placed successfully', 1, '2025-05-27 13:48:06', '2025-05-27 13:48:06'),
(37, 36, 'Pending', 'Order placed successfully', 2, '2025-05-28 02:04:01', '2025-05-28 02:04:01'),
(38, 37, 'Pending', 'Order placed successfully', 1, '2025-05-28 08:38:44', '2025-05-28 08:38:44'),
(39, 38, 'Pending', 'Order placed successfully', 1, '2025-05-28 08:38:47', '2025-05-28 08:38:47'),
(40, 39, 'Pending', 'Order placed successfully', 1, '2025-05-28 08:39:40', '2025-05-28 08:39:40'),
(41, 40, 'Pending', 'Order placed successfully', 1, '2025-05-28 08:43:25', '2025-05-28 08:43:25'),
(42, 41, 'Pending', 'Order placed successfully', 1, '2025-05-28 08:43:51', '2025-05-28 08:43:51'),
(43, 42, 'Pending', 'Order placed successfully', 1, '2025-05-28 08:46:14', '2025-05-28 08:46:14'),
(44, 43, 'Pending', 'Order placed successfully', 1, '2025-05-28 08:46:38', '2025-05-28 08:46:38'),
(45, 44, 'Pending', 'Order placed successfully', 1, '2025-05-28 08:49:54', '2025-05-28 08:49:54'),
(46, 45, 'Pending', 'Order placed successfully', 1, '2025-05-28 08:55:10', '2025-05-28 08:55:10'),
(47, 46, 'Pending', 'Order placed successfully', 1, '2025-05-28 08:56:10', '2025-05-28 08:56:10'),
(48, 47, 'Pending', 'Order placed successfully', 1, '2025-05-28 08:56:52', '2025-05-28 08:56:52'),
(49, 48, 'Pending', 'Order placed successfully', 1, '2025-05-28 09:00:28', '2025-05-28 09:00:28'),
(50, 49, 'Pending', 'Order placed successfully', 1, '2025-05-28 09:03:46', '2025-05-28 09:03:46'),
(51, 50, 'Pending', 'Order placed successfully', 1, '2025-05-28 09:04:04', '2025-05-28 09:04:04'),
(52, 51, 'Pending', 'Order placed successfully', 1, '2025-05-28 09:06:07', '2025-05-28 09:06:07'),
(53, 52, 'Pending', 'Order placed successfully', 1, '2025-05-28 09:17:56', '2025-05-28 09:17:56'),
(54, 53, 'Pending', 'Order placed successfully', 1, '2025-05-28 09:24:49', '2025-05-28 09:24:49'),
(55, 54, 'Pending', 'Order placed successfully', 1, '2025-05-28 10:47:39', '2025-05-28 10:47:39'),
(56, 55, 'Pending', 'Order placed successfully', 1, '2025-05-28 10:57:41', '2025-05-28 10:57:41'),
(57, 56, 'Pending', 'Order placed successfully', 1, '2025-05-28 11:04:32', '2025-05-28 11:04:32'),
(58, 57, 'Pending', 'Order placed successfully', 1, '2025-05-28 11:08:57', '2025-05-28 11:08:57'),
(59, 58, 'Pending', 'Order placed successfully', 2, '2025-05-28 12:39:44', '2025-05-28 12:39:44');

-- --------------------------------------------------------

--
-- Table structure for table `return_requests`
--

CREATE TABLE `return_requests` (
  `return_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `return_number` varchar(50) NOT NULL,
  `return_type` enum('refund','exchange') NOT NULL,
  `return_reason` text NOT NULL,
  `return_amount` decimal(10,2) NOT NULL,
  `return_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`return_data`)),
  `status` enum('Pending','Approved','Rejected','Processing','Completed') DEFAULT 'Pending',
  `request_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `return_requests`
--

INSERT INTO `return_requests` (`return_id`, `order_id`, `return_number`, `return_type`, `return_reason`, `return_amount`, `return_data`, `status`, `request_date`, `updated_at`) VALUES
(1, 35, 'RET202505272744', 'exchange', 'Late Delivery', 375.00, '{\"items\": [{\"id\": 40, \"order_id\": 35, \"food_name\": \"Bacon Silog\", \"food_price\": \"75.00\", \"quantity\": 5, \"subtotal\": \"375.00\", \"image_path\": \"uploads\\/food\\/1747301091_BaconSilog.png\", \"created_at\": \"2025-05-27 15:48:06\", \"is_free_promo\": 0}], \"reason\": \"Late Delivery\", \"type\": \"exchange\", \"notes\": \"\", \"return_amount\": 375, \"admin_notes\": \"sge sge mag tapsilog ka nalang\"}', 'Completed', '2025-05-27 13:48:34', '2025-05-27 14:12:32'),
(2, 36, 'RET202505287664', 'refund', 'Item Not as Described', 440.00, '{\"items\":[{\"id\":42,\"order_id\":36,\"food_name\":\"Bacon\",\"food_price\":\"55.00\",\"quantity\":8,\"subtotal\":\"440.00\",\"image_path\":\"uploads\\/food\\/1747303927_bacon.png\",\"created_at\":\"2025-05-28 04:04:01\",\"is_free_promo\":0}],\"reason\":\"Item Not as Described\",\"type\":\"refund\",\"notes\":\"\",\"return_amount\":440}', 'Completed', '2025-05-28 02:04:29', '2025-05-28 02:07:17'),
(3, 27, 'RET202505289974', 'refund', 'Wrong Item Delivered', 300.00, '{\"items\":[{\"id\":31,\"order_id\":27,\"food_name\":\"Bangus\",\"food_price\":\"60.00\",\"quantity\":5,\"subtotal\":\"300.00\",\"image_path\":\"uploads\\/food\\/1747303467_bangus.png\",\"created_at\":\"2025-05-27 13:36:49\",\"is_free_promo\":0}],\"reason\":\"Wrong Item Delivered\",\"type\":\"refund\",\"notes\":\"\",\"return_amount\":300}', 'Pending', '2025-05-28 02:32:18', '2025-05-28 02:32:18');

-- --------------------------------------------------------

--
-- Table structure for table `sign_up`
--

CREATE TABLE `sign_up` (
  `userId` int(11) NOT NULL,
  `firstName` varchar(251) NOT NULL,
  `lastName` varchar(251) NOT NULL,
  `address` varchar(251) NOT NULL,
  `phoneNumber` varchar(20) NOT NULL,
  `email` varchar(251) NOT NULL,
  `password` varchar(251) NOT NULL,
  `reset_code` varchar(6) DEFAULT NULL,
  `role` varchar(51) NOT NULL DEFAULT 'User'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sign_up`
--

INSERT INTO `sign_up` (`userId`, `firstName`, `lastName`, `address`, `phoneNumber`, `email`, `password`, `reset_code`, `role`) VALUES
(1, 'Jhimar Carl', 'Motea', '152', '09982832054', 'jhimarcarlmotea23@gmail.com', '$2y$10$VpAOaE.B6MpV/ktLF/zZ6.xRdgvOMiNqFAwJ4XDJdi.Hq2lofBzdy', NULL, 'Admin'),
(2, 'Jhimar', 'Motea', '152', '09982832054', 'jhimar.motea23@gmail.com', '$2y$10$Q/uvWxzWinQFmZbkU3i92O7/8pU.V5aGh91bNeVb7ViM2lCSo4NZS', '399171', 'User'),
(4, 'Mark Cyrus', 'Mendoza', '155 - X 19th Avenue East Rembo Taguig CIty', '09949870370', 'markcyrus2004@gmail.com', '$2y$10$pdoB6UJ8rhOsCrUsr2tqhOQCGGdpgYK.HimPlsYg7FwScR.OSXmm2', NULL, 'User'),
(5, 'Kou', 'Motea', '152', '09982832054', 'gyuleeseung@gmail.com', '$2y$10$0Yzxtbnj2oEBWfnPaQw6SulKT/I45ujg1YNefj7VW5fXKjkahbEU.', NULL, 'User'),
(6, 'JC', 'Motea', 'asd', '0982832054', 'carlmotea@gmail.com', '$2y$10$yIdxgh9ccx0Xhz4W2fP8SOeSe3Kk47al4T/bvtvruqVEanTbUg6sK', NULL, 'User');

-- --------------------------------------------------------

--
-- Table structure for table `user_carts`
--

CREATE TABLE `user_carts` (
  `cart_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `cart_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`cart_data`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_carts`
--

INSERT INTO `user_carts` (`cart_id`, `user_id`, `cart_data`, `created_at`, `updated_at`) VALUES
(3, 4, '[{\"foodId\":12,\"foodName\":\"Wingsilog\",\"price\":85,\"quantity\":1,\"image_path\":\"uploads\\/food\\/1747302425_WingSilog.png\",\"description\":\"crispy chicken wings, garlic fried rice, and a sunny-side-up egg, served with a side of creamy sauce\"},{\"foodId\":5,\"foodName\":\"Tapsilog\",\"price\":85,\"quantity\":1,\"image_path\":\"uploads\\/food\\/1747301579_Tapsilog.png\",\"description\":\"tender beef tapa, garlic fried rice, and a perfectly cooked sunny-side-up egg.\"}]', '2025-05-15 06:26:07', '2025-05-19 04:49:11'),
(60, 1, '[]', '2025-05-28 12:20:24', '2025-05-28 12:53:52'),
(61, 2, '[{\"foodId\":8,\"foodName\":\"Sisigsilog\",\"price\":85,\"quantity\":7,\"image_path\":\"uploads\\/food\\/1747302292_SisigSilog.png\",\"description\":\"sizzling pork sisig, garlic fried rice, and a sunny-side-up egg\"}]', '2025-05-28 12:40:36', '2025-05-28 12:40:36');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `fooditems`
--
ALTER TABLE `fooditems`
  ADD PRIMARY KEY (`foodId`);

--
-- Indexes for table `gallery_images`
--
ALTER TABLE `gallery_images`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `idx_user_orders` (`user_id`),
  ADD KEY `idx_order_status` (`status`),
  ADD KEY `idx_order_date` (`order_date`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_items` (`order_id`);

--
-- Indexes for table `order_status_history`
--
ALTER TABLE `order_status_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_status_history` (`order_id`);

--
-- Indexes for table `return_requests`
--
ALTER TABLE `return_requests`
  ADD PRIMARY KEY (`return_id`),
  ADD UNIQUE KEY `return_number` (`return_number`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `sign_up`
--
ALTER TABLE `sign_up`
  ADD PRIMARY KEY (`userId`);

--
-- Indexes for table `user_carts`
--
ALTER TABLE `user_carts`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `fk_user_cart_user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `fooditems`
--
ALTER TABLE `fooditems`
  MODIFY `foodId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `gallery_images`
--
ALTER TABLE `gallery_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT for table `order_status_history`
--
ALTER TABLE `order_status_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `return_requests`
--
ALTER TABLE `return_requests`
  MODIFY `return_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `sign_up`
--
ALTER TABLE `sign_up`
  MODIFY `userId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `user_carts`
--
ALTER TABLE `user_carts`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `sign_up` (`userId`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE;

--
-- Constraints for table `order_status_history`
--
ALTER TABLE `order_status_history`
  ADD CONSTRAINT `order_status_history_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE;

--
-- Constraints for table `return_requests`
--
ALTER TABLE `return_requests`
  ADD CONSTRAINT `return_requests_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`);

--
-- Constraints for table `user_carts`
--
ALTER TABLE `user_carts`
  ADD CONSTRAINT `fk_user_cart_user_id` FOREIGN KEY (`user_id`) REFERENCES `sign_up` (`userId`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
