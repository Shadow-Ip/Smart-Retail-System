-- MySQL dump 10.13  Distrib 8.0.44, for Win64 (x86_64)
--
-- Host: localhost    Database: smart_retail_db
-- ------------------------------------------------------
-- Server version	8.0.36

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `cart_items`
--

DROP TABLE IF EXISTS `cart_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cart_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `customer_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `added_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE CASCADE,
  CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=67 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cart_items`
--

LOCK TABLES `cart_items` WRITE;
/*!40000 ALTER TABLE `cart_items` DISABLE KEYS */;
INSERT INTO `cart_items` VALUES (60,2,9,1,'2025-11-14 07:52:31'),(61,2,14,1,'2025-11-14 09:15:39');
/*!40000 ALTER TABLE `cart_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `customers` (
  `customer_id` int NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(10) DEFAULT NULL,
  `address` text,
  `registration_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`customer_id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_customers_email` (`email`),
  KEY `idx_customers_name` (`full_name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customers`
--

LOCK TABLES `customers` WRITE;
/*!40000 ALTER TABLE `customers` DISABLE KEYS */;
INSERT INTO `customers` VALUES (1,'Jake Zulu','maslow@gmail.com','$2y$10$hYZ8O4HFbczwaXbGfpL92ef01SkFDZdfSG7FQjoBfY4Ddo8MJbap.','0755555555','125 Germiston','2025-11-12 16:37:24'),(2,'John Van ZamBuk','zambuck@gmail.com','$2y$10$UdIztqxZyHY10Pdgn2gnWezntNr4K1n1WdR/sVd1AfPDIf1ytXmaq','0725254545','Block E, Building 7, Centurion Gate Business Park, 124 Akkerboom St, Road, Centurion, 0157','2025-11-12 21:07:41'),(3,'Peter Wals','peter24@gmail.com','$2y$10$P20jU.EzkDKr85BmjovLMevDKgFxtEh8e/up2n.syQ6Oa4Jbfk/q6','0725144545','124 Main Street','2025-11-14 11:18:43');
/*!40000 ALTER TABLE `customers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_activity_log`
--

DROP TABLE IF EXISTS `order_activity_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_activity_log` (
  `log_id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `associate_id` int NOT NULL,
  `action` varchar(255) NOT NULL,
  `action_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_id`),
  KEY `order_id` (`order_id`),
  KEY `associate_id` (`associate_id`),
  CONSTRAINT `order_activity_log_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  CONSTRAINT `order_activity_log_ibfk_2` FOREIGN KEY (`associate_id`) REFERENCES `sales_associate` (`associate_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_activity_log`
--

LOCK TABLES `order_activity_log` WRITE;
/*!40000 ALTER TABLE `order_activity_log` DISABLE KEYS */;
INSERT INTO `order_activity_log` VALUES (1,6,1,'Status changed to Processing','2025-11-13 17:51:42'),(2,5,1,'Status changed to Shipped','2025-11-13 17:56:10'),(3,4,1,'Status changed to Delivered','2025-11-14 11:35:33'),(4,6,1,'Status changed to Delivered','2025-11-14 11:42:02');
/*!40000 ALTER TABLE `order_activity_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_items` (
  `order_item_id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) GENERATED ALWAYS AS ((`quantity` * `unit_price`)) STORED,
  PRIMARY KEY (`order_item_id`),
  KEY `product_id` (`product_id`),
  KEY `idx_order_items_order_product` (`order_id`,`product_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `order_items_chk_1` CHECK ((`quantity` > 0)),
  CONSTRAINT `order_items_chk_2` CHECK ((`unit_price` >= 0))
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
INSERT INTO `order_items` (`order_item_id`, `order_id`, `product_id`, `quantity`, `unit_price`) VALUES (4,4,3,1,599.00),(5,5,8,1,740.00),(6,5,2,3,1499.00),(7,5,1,2,999.00),(8,6,7,2,6499.00),(9,6,8,1,740.00),(10,6,6,1,7999.00),(11,6,3,1,599.00),(12,6,1,1,999.00),(13,6,2,5,1499.00),(14,7,12,1,5999.00);
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders` (
  `order_id` int NOT NULL AUTO_INCREMENT,
  `customer_id` int NOT NULL,
  `order_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `total_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `status` enum('Pending','Processing','Shipped','Delivered','Cancelled') DEFAULT 'Pending',
  PRIMARY KEY (`order_id`),
  KEY `idx_orders_customer_id` (`customer_id`),
  KEY `idx_orders_status` (`status`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES (4,2,'2025-11-13 12:57:58',674.00,'Delivered'),(5,2,'2025-11-13 13:30:41',7235.00,'Shipped'),(6,2,'2025-11-13 13:47:54',30830.00,'Delivered'),(7,3,'2025-11-14 11:22:47',5999.00,'Pending');
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payments` (
  `payment_id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `payment_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('Credit Card','EFT','Cash') DEFAULT 'Credit Card',
  `payment_status` enum('Pending','Completed','Failed') DEFAULT 'Pending',
  `transaction_ref` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`payment_id`),
  KEY `idx_payments_order_id` (`order_id`),
  KEY `idx_payments_date` (`payment_date`),
  KEY `idx_payments_status` (`payment_status`),
  CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `payments_chk_1` CHECK ((`amount` >= 0))
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
INSERT INTO `payments` VALUES (1,4,'2025-11-13 12:57:58',674.00,'Cash','Completed','TXN6915D5D62AEB3'),(2,5,'2025-11-13 13:30:41',7235.00,'Credit Card','Completed','TXN6915DD81D6936'),(3,6,'2025-11-13 13:47:54',30830.00,'EFT','Completed','TXN6915E18A357DB'),(4,7,'2025-11-14 11:22:47',5999.00,'Cash','Completed','TXN69171107CEDE6');
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `product_id` int NOT NULL AUTO_INCREMENT,
  `product_name` varchar(120) NOT NULL,
  `description` text,
  `price` decimal(10,2) NOT NULL,
  `stock_quantity` int NOT NULL DEFAULT '0',
  `category` varchar(80) DEFAULT NULL,
  `date_added` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`product_id`),
  KEY `idx_products_name` (`product_name`),
  KEY `idx_products_category` (`category`),
  KEY `idx_products_price` (`price`),
  CONSTRAINT `products_chk_1` CHECK ((`price` >= 0))
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,'Wireless Headphones','Comfortable over-ear with 20h battery',999.00,54,'Electronics','2025-11-12 17:06:16','2025-11-14 11:36:56'),(2,'Smartwatch Pro','Fitness tracking & messages on your wrist',1499.00,2,'Wearables','2025-11-12 17:10:02','2025-11-14 08:31:04'),(3,'Portable Speaker','Compact with rich bass',599.00,10,'Audio','2025-11-12 17:14:25','2025-11-12 17:14:25'),(4,'Coffee Maker','Rich pump espresso coffee machine',3189.00,4,'Appliances','2025-11-12 17:16:59','2025-11-14 11:36:20'),(5,'Graphics Card','Arktek AMD Radeon RX580 8GB GDDR5 256-bit HDMI / DVI / DPx3',2628.00,1,'Computer Video','2025-11-12 17:19:00','2025-11-13 16:49:24'),(6,'Gaming Monitor','Samsung Gaming Monitor - 34\" Curved Monitor Odyssey G5 Ultra WQHD',7999.00,8,'Monitors','2025-11-12 17:26:05','2025-11-14 07:19:37'),(7,'Laptop','HP Notebooks 8GB-256GB Perfect for Office starter work in styles',6499.00,12,'Computers','2025-11-12 17:28:37','2025-11-12 17:28:37'),(8,'Christmas Tree','1.8m Pine Needle Artificial Christmas Tree. Easy to assemble',740.00,122,'Decorations','2025-11-12 17:30:05','2025-11-12 17:30:05'),(9,'DVI-D Converter','Connects DVI-D  desktops to VGA displays, 1080P Adapter Cable 25pin (24+1)',106.00,2,'Cable Adapters','2025-11-13 18:23:47','2025-11-14 07:42:56'),(10,'Grinder','BLACK+DECKER - 710W Small Angle Grinder 115mm. Compact design.',329.00,15,'DIY Power Tools','2025-11-14 08:06:58','2025-11-14 08:07:59'),(11,'Air Fryer','Introducing the XL Defy Air Fryer boasting 7.6L capacity',1356.00,26,'','2025-11-14 08:17:48','2025-11-14 08:17:48'),(12,'Bottom Freezer Fridge','Hisense 222L Bottom Freezer Fridge with Water Dispenser -Titanium Inox',5999.00,18,'Fridges & Freezers','2025-11-14 08:29:51','2025-11-14 08:29:51'),(13,'Macallan Whiskey','The Macallan Single Malt 18 Year Old Sherry Oak',6929.00,4,'Whiskey, Gin & Spirits','2025-11-14 09:00:07','2025-11-14 09:00:07'),(14,'Firstwatch - Whisky','First watch is 100% genuine Canadian Whisky distilled, blended and matured in Canada.',259.00,17,'Whiskey, Gin & Spirits','2025-11-14 09:13:04','2025-11-14 09:27:20'),(15,'MSI PRO H610M-E Intel Motherboard','Features DDR5 memory support | Supports 12th/13th Gen Intel Core processors for LGA 1700 socket',2889.00,1,'Motherboards','2025-11-14 11:38:51','2025-11-14 11:38:51'),(16,'Washing Machine','Defy 6kg DAW392 Grey Front Loader Washing Machine A+++',3999.00,0,'Washing & Drying','2025-11-14 11:40:19','2025-11-14 11:40:19');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sales_associate`
--

DROP TABLE IF EXISTS `sales_associate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sales_associate` (
  `associate_id` int NOT NULL AUTO_INCREMENT,
  `associate_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`associate_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sales_associate`
--

LOCK TABLES `sales_associate` WRITE;
/*!40000 ALTER TABLE `sales_associate` DISABLE KEYS */;
INSERT INTO `sales_associate` VALUES (1,'Oscar Dyantyi','oscarDN@srs.co.za','$2y$10$MNYD4QL49nW8tMY8kgfqs.X8em0wE80ALzkjq8j8uFEJYxd3ahRyu','2025-11-13 16:32:52');
/*!40000 ALTER TABLE `sales_associate` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-11-14 14:06:40
