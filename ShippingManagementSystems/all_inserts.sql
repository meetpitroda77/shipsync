-- MySQL dump 10.13  Distrib 8.0.45, for Linux (x86_64)
--
-- Host: localhost    Database: shipsync
-- ------------------------------------------------------
-- Server version	8.0.45-0ubuntu0.24.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `addresses`
--


--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `failed_jobs`
--


--
-- Dumping data for table `invoices`
--


--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `migrations`
--


-- Dumping data for table `notifications`
--


--
-- Dumping data for table `packages`
--


--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `payments`
--



--
-- Dumping data for table `personal_access_tokens`
--


--
-- Dumping data for table `recipients`
--


--
-- Dumping data for table `reports`
--


--
-- Dumping data for table `sessions`
--


--
-- Dumping data for table `shipment_images`
--



--
-- Dumping data for table `shipment_logs`
--

LOCK TABLES `shipment_logs` WRITE;
/*!40000 ALTER TABLE `shipment_logs` DISABLE KEYS */;
INSERT INTO `shipment_logs` VALUES (358,105,'created','satyam park 1','Shipment created','2026-04-02 05:51:31','2026-04-02 05:51:31'),(359,105,'pending_assigned','N/A','Payment completed, shipment ready for assignment','2026-04-02 05:51:57','2026-04-02 05:51:57'),(366,105,'assigned',NULL,'Shipment is assigned','2026-04-02 07:13:15','2026-04-02 07:13:15'),(367,105,'picked_up',NULL,'Shipment is picked_up','2026-04-02 07:15:30','2026-04-02 07:15:30'),(380,109,'created','satyam park 1','Shipment created','2026-04-03 00:56:35','2026-04-03 00:56:35'),(381,109,'pending_assigned','N/A','Payment completed, shipment ready for assignment','2026-04-03 00:57:05','2026-04-03 00:57:05'),(382,109,'assigned',NULL,'Shipment is assigned','2026-04-03 00:58:32','2026-04-03 00:58:32'),(383,109,'picked_up',NULL,'Shipment is picked_up','2026-04-03 01:55:52','2026-04-03 01:55:52'),(384,109,'in_transit',NULL,'Shipment is in_transit','2026-04-03 01:56:01','2026-04-03 01:56:01'),(385,109,'out_for_delivery',NULL,'Shipment is out_for_delivery','2026-04-03 01:56:08','2026-04-03 01:56:08'),(386,109,'delivered',NULL,'Shipment is delivered','2026-04-03 01:56:28','2026-04-03 01:56:28'),(387,105,'in_transit',NULL,'Shipment is in_transit','2026-04-03 02:38:35','2026-04-03 02:38:35'),(388,105,'out_for_delivery',NULL,'Shipment is out_for_delivery','2026-04-03 02:38:41','2026-04-03 02:38:41'),(397,109,'out_for_delivery',NULL,'Shipment is out_for_delivery','2026-04-03 03:25:48','2026-04-03 03:25:48'),(398,109,'delivered',NULL,'Shipment is delivered','2026-04-03 03:25:58','2026-04-03 03:25:58'),(399,109,'out_for_delivery',NULL,'Shipment is out_for_delivery','2026-04-03 03:31:16','2026-04-03 03:31:16'),(400,109,'delivered',NULL,'Shipment is delivered','2026-04-03 03:31:34','2026-04-03 03:31:34'),(401,109,'out_for_delivery',NULL,'Shipment is out_for_delivery','2026-04-03 03:35:27','2026-04-03 03:35:27'),(402,109,'delivered',NULL,'Shipment is delivered','2026-04-03 03:35:36','2026-04-03 03:35:36'),(403,105,'delivered',NULL,'Shipment is delivered','2026-04-03 03:41:37','2026-04-03 03:41:37'),(409,110,'created','satyam park 1','Shipment created','2026-04-06 07:18:43','2026-04-06 07:18:43'),(410,110,'pending_assigned','N/A','Payment completed, shipment ready for assignment','2026-04-06 07:19:40','2026-04-06 07:19:40'),(411,110,'assigned',NULL,'Shipment is assigned','2026-04-06 07:28:30','2026-04-06 07:28:30'),(412,110,'picked_up',NULL,'Shipment is picked_up','2026-04-06 07:33:50','2026-04-06 07:33:50'),(413,110,'in_transit',NULL,'Shipment is in_transit','2026-04-06 07:34:10','2026-04-06 07:34:10'),(414,110,'out_for_delivery',NULL,'Shipment is out_for_delivery','2026-04-06 07:34:16','2026-04-06 07:34:16'),(415,110,'delivered',NULL,'Shipment is delivered','2026-04-06 07:46:05','2026-04-06 07:46:05'),(422,112,'created','satyam park 1','Shipment created','2026-04-07 08:32:25','2026-04-07 08:32:25'),(423,112,'pending_assigned','N/A','Payment completed, shipment ready for assignment','2026-04-07 08:32:43','2026-04-07 08:32:43'),(424,112,'assigned',NULL,'Shipment is assigned','2026-04-07 08:39:22','2026-04-07 08:39:22'),(425,112,'picked_up',NULL,'Shipment is picked_up','2026-04-07 08:40:36','2026-04-07 08:40:36'),(426,113,'created','satyam park 1','Shipment created','2026-04-07 09:12:05','2026-04-07 09:12:05'),(427,113,'pending_assigned','N/A','Payment completed, shipment ready for assignment','2026-04-07 09:12:25','2026-04-07 09:12:25'),(428,112,'delayed',NULL,'Shipment automatically marked as delayed due to past estimated delivery date','2026-04-07 10:15:00','2026-04-07 10:15:00'),(429,112,'picked_up',NULL,'Shipment is picked_up','2026-04-07 10:53:33','2026-04-07 10:53:33'),(430,110,'in_transit',NULL,'Shipment is in_transit','2026-04-07 10:53:59','2026-04-07 10:53:59'),(432,112,'delivered',NULL,'Shipment is delivered','2026-04-07 10:54:29','2026-04-07 10:54:29'),(433,113,'delayed',NULL,'Shipment delayed due to exceeding estimated delivery date','2026-04-07 11:04:00','2026-04-07 11:04:00'),(434,113,'assigned',NULL,'Shipment is assigned','2026-04-07 11:09:54','2026-04-07 11:09:54'),(435,113,'picked_up',NULL,'Shipment is picked_up','2026-04-07 11:10:54','2026-04-07 11:10:54'),(436,113,'in_transit',NULL,'Shipment is in_transit','2026-04-07 11:11:04','2026-04-07 11:11:04'),(437,113,'out_for_delivery',NULL,'Shipment is out_for_delivery','2026-04-07 11:11:12','2026-04-07 11:11:12'),(438,113,'delivered',NULL,'Shipment is delivered','2026-04-07 11:11:23','2026-04-07 11:11:23'),(439,114,'created','satyam park 1','Shipment created','2026-04-08 08:56:12','2026-04-08 08:56:12'),(440,114,'pending_assigned','N/A','Payment completed, shipment ready for assignment','2026-04-08 08:56:37','2026-04-08 08:56:37'),(441,114,'assigned',NULL,'Shipment is assigned','2026-04-08 09:06:28','2026-04-08 09:06:28'),(442,114,'picked_up',NULL,'Shipment is picked_up','2026-04-08 09:07:08','2026-04-08 09:07:08'),(443,114,'in_transit',NULL,'Shipment is in_transit','2026-04-08 09:07:20','2026-04-08 09:07:20'),(444,114,'out_for_delivery',NULL,'Shipment is out_for_delivery','2026-04-08 09:07:25','2026-04-08 09:07:25'),(445,114,'delivered',NULL,'Shipment is delivered','2026-04-08 09:07:41','2026-04-08 09:07:41'),(446,115,'created','satyam park 1','Shipment created','2026-04-08 12:10:39','2026-04-08 12:10:39'),(447,115,'pending_assigned','N/A','Payment completed, shipment ready for assignment','2026-04-08 12:11:02','2026-04-08 12:11:02'),(448,115,'assigned',NULL,'Shipment is assigned','2026-04-08 12:14:07','2026-04-08 12:14:07'),(449,115,'picked_up',NULL,'Shipment is picked_up','2026-04-08 12:15:28','2026-04-08 12:15:28'),(450,115,'delayed',NULL,'Shipment is delayed','2026-04-08 12:27:14','2026-04-08 12:27:14'),(451,115,'out_for_delivery',NULL,'Shipment is out_for_delivery','2026-04-08 12:30:35','2026-04-08 12:30:35'),(452,115,'in_transit',NULL,'Shipment is in_transit','2026-04-08 12:31:06','2026-04-08 12:31:06'),(453,115,'out_for_delivery',NULL,'Shipment is out_for_delivery','2026-04-08 12:31:22','2026-04-08 12:31:22'),(454,115,'delivered',NULL,'Shipment is delivered','2026-04-08 12:32:30','2026-04-08 12:32:30'),(455,115,'delayed',NULL,'Shipment is delayed','2026-04-08 12:36:13','2026-04-08 12:36:13'),(456,115,'out_for_delivery',NULL,'Shipment is out_for_delivery','2026-04-08 12:36:25','2026-04-08 12:36:25'),(457,115,'in_transit',NULL,'Shipment is in_transit','2026-04-08 12:36:40','2026-04-08 12:36:40'),(458,115,'delivered',NULL,'Shipment is delivered','2026-04-08 12:37:03','2026-04-08 12:37:03'),(459,116,'created','satyam park 1','Shipment created','2026-04-08 12:37:50','2026-04-08 12:37:50'),(460,116,'pending_assigned','N/A','Payment completed, shipment ready for assignment','2026-04-08 12:38:10','2026-04-08 12:38:10'),(461,116,'assigned',NULL,'Shipment is assigned','2026-04-08 12:41:38','2026-04-08 12:41:38'),(462,116,'picked_up',NULL,'Shipment is picked_up','2026-04-08 12:42:07','2026-04-08 12:42:07'),(463,116,'in_transit',NULL,'Shipment is in_transit','2026-04-08 12:54:20','2026-04-08 12:54:20'),(464,116,'out_for_delivery',NULL,'Shipment is out_for_delivery','2026-04-08 13:06:31','2026-04-08 13:06:31'),(465,116,'in_transit',NULL,'Shipment is in_transit','2026-04-08 13:17:52','2026-04-08 13:17:52'),(466,116,'out_for_delivery',NULL,'Shipment is out_for_delivery','2026-04-08 13:18:36','2026-04-08 13:18:36'),(467,116,'in_transit',NULL,'Shipment is in_transit','2026-04-08 13:19:30','2026-04-08 13:19:30'),(468,116,'out_for_delivery',NULL,'Shipment is out_for_delivery','2026-04-08 13:19:39','2026-04-08 13:19:39'),(469,116,'in_transit',NULL,'Shipment is in_transit','2026-04-08 13:27:39','2026-04-08 13:27:39'),(470,116,'out_for_delivery',NULL,'Shipment is out_for_delivery','2026-04-08 13:28:02','2026-04-08 13:28:02'),(471,116,'picked_up',NULL,'Shipment is picked_up','2026-04-08 13:28:24','2026-04-08 13:28:24'),(472,116,'in_transit',NULL,'Shipment is in_transit','2026-04-08 13:30:31','2026-04-08 13:30:31'),(473,116,'out_for_delivery',NULL,'Shipment is out_for_delivery','2026-04-08 13:38:01','2026-04-08 13:38:01'),(474,116,'in_transit',NULL,'Shipment is in_transit','2026-04-08 13:38:13','2026-04-08 13:38:13'),(475,116,'out_for_delivery',NULL,'Shipment is out_for_delivery','2026-04-08 13:38:29','2026-04-08 13:38:29'),(476,116,'picked_up',NULL,'Shipment is picked_up','2026-04-08 13:38:46','2026-04-08 13:38:46'),(477,116,'delivered',NULL,'Shipment is delivered','2026-04-08 13:39:58','2026-04-08 13:39:58'),(482,120,'created','satyam park 1','Shipment created','2026-04-09 04:37:20','2026-04-09 04:37:20'),(483,120,'pending_assigned','N/A','Payment completed, shipment ready for assignment','2026-04-09 04:37:38','2026-04-09 04:37:38'),(490,126,'created','satyam park 1','Shipment created','2026-04-09 05:17:01','2026-04-09 05:17:01'),(491,126,'pending_payment',NULL,'Shipment awaiting payment','2026-04-09 05:17:01','2026-04-09 05:17:01'),(492,126,'pending_assigned','N/A','Payment completed, shipment ready for assignment','2026-04-09 05:17:32','2026-04-09 05:17:32'),(514,137,'created','satyam park 1','Shipment created','2026-04-09 06:51:43','2026-04-09 06:51:43'),(515,137,'pending_payment','satyam park 1','Waiting for payment','2026-04-09 06:51:44','2026-04-09 06:51:44'),(529,143,'created','satyam park 1','Shipment created','2026-04-09 07:25:00','2026-04-09 07:25:00'),(530,143,'pending_payment','satyam park 1','Waiting for payment','2026-04-09 07:25:01','2026-04-09 07:25:01'),(531,143,'pending_assigned','N/A','Payment completed, shipment ready for assignment','2026-04-09 07:25:22','2026-04-09 07:25:22'),(532,144,'created','satyam park 1','Shipment created','2026-04-09 07:28:44','2026-04-09 07:28:44'),(533,144,'pending_payment','satyam park 1','Waiting for payment','2026-04-09 07:28:45','2026-04-09 07:28:45'),(534,144,'pending_assigned','N/A','Payment completed, shipment ready for assignment','2026-04-09 07:29:07','2026-04-09 07:29:07'),(541,120,'assigned',NULL,'Shipment is assigned','2026-04-09 07:43:25','2026-04-09 07:43:25'),(542,126,'assigned',NULL,'Shipment is assigned','2026-04-09 07:43:40','2026-04-09 07:43:40'),(543,126,'picked_up',NULL,'Shipment is picked_up','2026-04-09 07:45:33','2026-04-09 07:45:33'),(544,126,'in_transit',NULL,'Shipment is in_transit','2026-04-09 07:45:54','2026-04-09 07:45:54'),(545,126,'out_for_delivery',NULL,'Shipment is out_for_delivery','2026-04-09 07:46:15','2026-04-09 07:46:15'),(546,120,'picked_up',NULL,'Shipment is picked_up','2026-04-09 07:51:24','2026-04-09 07:51:24'),(547,120,'in_transit',NULL,'Shipment is in_transit','2026-04-09 07:58:00','2026-04-09 07:58:00'),(548,120,'out_for_delivery',NULL,'Shipment is out_for_delivery','2026-04-09 08:01:24','2026-04-09 08:01:24'),(549,120,'in_transit',NULL,'Shipment is in_transit','2026-04-09 08:01:59','2026-04-09 08:01:59'),(555,120,'out_for_delivery',NULL,'Shipment is out_for_delivery','2026-04-09 08:12:03','2026-04-09 08:12:03'),(556,126,'delivered',NULL,'Shipment is delivered','2026-04-09 08:12:25','2026-04-09 08:12:25'),(557,120,'delivered',NULL,'Shipment is delivered','2026-04-09 08:12:37','2026-04-09 08:12:37'),(588,137,'pending_payment','N/A','Payment failed, shipment pending payment','2026-04-09 12:31:48','2026-04-09 12:31:48'),(631,172,'created','avc','Shipment created','2026-04-10 11:41:32','2026-04-10 11:41:32'),(632,172,'pending_payment','avc','Waiting for payment','2026-04-10 11:41:33','2026-04-10 11:41:33'),(633,172,'pending_assigned','N/A','Payment completed, shipment ready for assignment','2026-04-10 11:41:51','2026-04-10 11:41:51'),(634,173,'created','satyam park 1','Shipment created','2026-04-13 07:45:17','2026-04-13 07:45:17'),(635,173,'pending_payment','satyam park 1','Waiting for payment','2026-04-13 07:45:18','2026-04-13 07:45:18'),(636,173,'pending_payment','N/A','Payment failed, shipment pending payment','2026-04-13 07:45:25','2026-04-13 07:45:25'),(637,173,'pending_assigned','N/A','Payment completed, shipment ready for assignment','2026-04-13 07:46:25','2026-04-13 07:46:25'),(638,173,'assigned',NULL,NULL,'2026-04-13 07:47:11','2026-04-13 07:47:11'),(639,173,'in_transit',NULL,NULL,'2026-04-13 07:47:53','2026-04-13 07:47:53'),(641,173,'failed_delivery',NULL,'mrt','2026-04-13 07:51:33','2026-04-13 07:57:35'),(642,173,'picked_up',NULL,'Picked up','2026-04-14 06:36:03','2026-04-14 06:36:03'),(643,173,'out_for_delivery',NULL,'Out for delivery','2026-04-14 06:36:26','2026-04-14 06:36:26'),(644,173,'delivered',NULL,'Delivered','2026-04-14 06:36:39','2026-04-14 06:36:39'),(651,176,'created','satyam park 1','Shipment created','2026-04-14 07:21:06','2026-04-14 07:21:06'),(652,176,'pending_payment','satyam park 1','Waiting for payment','2026-04-14 07:21:08','2026-04-14 07:21:08'),(653,176,'pending_assigned','N/A','Payment completed, shipment ready for assignment','2026-04-14 07:21:31','2026-04-14 07:21:31'),(654,177,'created','satyam park 1','Shipment created','2026-04-14 08:21:18','2026-04-14 08:21:18'),(655,177,'pending_payment','satyam park 1','Waiting for payment','2026-04-14 08:21:19','2026-04-14 08:21:19'),(656,177,'pending_assigned','N/A','Payment completed, shipment ready for assignment','2026-04-14 08:21:43','2026-04-14 08:21:43'),(657,178,'created','satyam park 1','Shipment created','2026-04-14 10:49:49','2026-04-14 10:49:49'),(658,178,'pending_payment','satyam park 1','Waiting for payment','2026-04-14 10:49:50','2026-04-14 10:49:50'),(659,178,'pending_assigned','N/A','Payment completed, shipment ready for assignment','2026-04-14 10:50:18','2026-04-14 10:50:18'),(660,179,'created','satyam park 1','Shipment created','2026-04-14 11:01:16','2026-04-14 11:01:16'),(661,179,'pending_payment','satyam park 1','Waiting for payment','2026-04-14 11:01:17','2026-04-14 11:01:17'),(662,179,'pending_assigned','N/A','Payment completed, shipment ready for assignment','2026-04-14 11:01:51','2026-04-14 11:01:51'),(663,179,'assigned',NULL,'Assigned','2026-04-14 12:00:50','2026-04-14 12:00:50'),(664,179,'picked_up',NULL,'Picked up','2026-04-14 12:10:42','2026-04-14 12:10:42'),(665,179,'in_transit',NULL,'In transit','2026-04-14 12:11:01','2026-04-14 12:11:01'),(666,179,'out_for_delivery',NULL,'Out for delivery','2026-04-14 12:11:14','2026-04-14 12:11:14'),(667,180,'created','satyam park 1','Shipment created','2026-04-15 05:22:18','2026-04-15 05:22:18'),(668,180,'pending_payment','satyam park 1','Waiting for payment','2026-04-15 05:22:19','2026-04-15 05:22:19'),(669,181,'created','satyam park 1','Shipment created','2026-04-15 05:42:17','2026-04-15 05:42:17'),(670,181,'pending_payment','satyam park 1','Waiting for payment','2026-04-15 05:42:18','2026-04-15 05:42:18'),(671,182,'created','satyam park 1','Shipment created','2026-04-15 05:47:40','2026-04-15 05:47:40'),(672,182,'pending_payment','satyam park 1','Waiting for payment','2026-04-15 05:47:41','2026-04-15 05:47:41'),(673,183,'created','satyam park 1','Shipment created','2026-04-15 07:11:41','2026-04-15 07:11:41'),(674,183,'pending_payment','satyam park 1','Waiting for payment','2026-04-15 07:11:42','2026-04-15 07:11:42'),(675,184,'created','satyam park 1','Shipment created','2026-04-15 08:16:08','2026-04-15 08:16:08'),(676,184,'pending_payment','satyam park 1','Waiting for payment','2026-04-15 08:16:09','2026-04-15 08:16:09'),(677,184,'pending_assigned','N/A','Payment completed, shipment ready for assignment','2026-04-15 08:16:30','2026-04-15 08:16:30'),(678,185,'created','satyam park 1','Shipment created','2026-04-15 08:32:29','2026-04-15 08:32:29'),(679,185,'pending_payment','satyam park 1','Waiting for payment','2026-04-15 08:32:30','2026-04-15 08:32:30'),(680,185,'pending_assigned','N/A','Payment completed, shipment ready for assignment','2026-04-15 08:32:48','2026-04-15 08:32:48'),(681,186,'created','satyam park 1','Shipment created','2026-04-15 08:52:28','2026-04-15 08:52:28'),(682,186,'pending_payment','satyam park 1','Waiting for payment','2026-04-15 08:52:29','2026-04-15 08:52:29'),(683,186,'pending_assigned','N/A','Payment completed, shipment ready for assignment','2026-04-15 08:52:51','2026-04-15 08:52:51'),(684,186,'assigned',NULL,'Assigned','2026-04-15 08:56:58','2026-04-15 08:56:58'),(685,185,'assigned',NULL,'Assigned','2026-04-15 09:02:40','2026-04-15 09:02:40'),(686,186,'out_for_delivery',NULL,'Out for delivery','2026-04-15 10:08:08','2026-04-15 10:08:08'),(687,185,'in_transit',NULL,'In transit','2026-04-15 11:02:31','2026-04-15 11:02:31'),(688,185,'out_for_delivery',NULL,'Out for delivery','2026-04-15 11:04:19','2026-04-15 11:04:19'),(689,186,'in_transit',NULL,'In transit','2026-04-15 11:06:07','2026-04-15 11:06:07'),(693,188,'created','satyam park 1','Shipment created','2026-04-15 11:47:59','2026-04-15 11:47:59'),(694,188,'pending_payment','satyam park 1','Waiting for payment','2026-04-15 11:47:59','2026-04-15 11:47:59'),(695,188,'pending_assigned','N/A','Payment completed, shipment ready for assignment','2026-04-15 11:48:21','2026-04-15 11:48:21'),(696,189,'created','satyam park 1','Shipment created','2026-04-15 12:00:14','2026-04-15 12:00:14'),(697,189,'pending_payment','satyam park 1','Waiting for payment','2026-04-15 12:00:15','2026-04-15 12:00:15'),(698,189,'pending_assigned','N/A','Payment completed, shipment ready for assignment','2026-04-15 12:00:35','2026-04-15 12:00:35'),(699,137,'delayed',NULL,'Shipment delayed due to exceeding estimated delivery date','2026-04-16 04:59:00','2026-04-16 04:59:00'),(700,143,'delayed',NULL,'Shipment delayed due to exceeding estimated delivery date','2026-04-16 04:59:00','2026-04-16 04:59:00'),(701,144,'delayed',NULL,'Shipment delayed due to exceeding estimated delivery date','2026-04-16 04:59:00','2026-04-16 04:59:00'),(702,172,'delayed',NULL,'Shipment delayed due to exceeding estimated delivery date','2026-04-16 04:59:00','2026-04-16 04:59:00'),(704,184,'assigned',NULL,'Assigned','2026-04-16 07:27:30','2026-04-16 07:27:30'),(706,178,'assigned',NULL,'Assigned','2026-04-16 08:45:41','2026-04-16 08:45:41'),(707,176,'assigned',NULL,'Assigned','2026-04-16 08:46:25','2026-04-16 08:46:25'),(709,177,'assigned',NULL,'Assigned','2026-04-16 08:49:46','2026-04-16 08:49:46'),(725,189,'assigned',NULL,'Assigned','2026-04-16 11:20:49','2026-04-16 11:20:49'),(736,188,'assigned',NULL,'Assigned','2026-04-16 12:37:15','2026-04-16 12:37:15'),(741,197,'created','satyam park 1','Shipment created','2026-04-16 13:22:25','2026-04-16 13:22:25'),(742,197,'pending_payment','satyam park 1','Waiting for payment','2026-04-16 13:22:26','2026-04-16 13:22:26'),(743,189,'picked_up',NULL,'Picked up','2026-04-17 04:43:55','2026-04-17 04:43:55');
/*!40000 ALTER TABLE `shipment_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `shipment_reports`
--

LOCK TABLES `shipment_reports` WRITE;
/*!40000 ALTER TABLE `shipment_reports` DISABLE KEYS */;
INSERT INTO `shipment_reports` VALUES (3,'2026-04-07',2,1308.62,2,0,2,2,2,1,2,0,2,0,'2026-04-07 12:51:00','2026-04-07 12:51:00'),(7,'2026-04-08',1,115.24,1,0,1,1,1,1,1,0,0,0,'2026-04-08 09:10:00','2026-04-08 09:10:00'),(8,'2026-04-14',0,0.00,0,0,0,0,0,0,0,0,0,0,'2026-04-14 06:00:00','2026-04-14 06:00:00'),(9,'2026-04-16',0,0.00,0,0,0,0,0,0,0,0,4,0,'2026-04-16 04:59:00','2026-04-16 04:59:00');
/*!40000 ALTER TABLE `shipment_reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `shipments`
--

LOCK TABLES `shipments` WRITE;
/*!40000 ALTER TABLE `shipments` DISABLE KEYS */;
INSERT INTO `shipments` VALUES (105,'TRK69ce513b058a6','Meet Pitroda','1234567890',1,'kathan chauhan','1234567890',50,'parcel','abc','standard','delivered','2026-04-07','2026-04-03',62,42,'2026-04-02 05:51:31','2026-04-03 03:41:37','dhl','surface'),(109,'TRK69cf5d9bcbf2a','Meet Pitroda','1234567890',1,'kathan chauhan','1234567890',50,'parcel','abc','standard','delivered','2026-04-08','2026-04-03',62,42,'2026-04-03 00:56:35','2026-04-03 03:35:37','dhl','surface'),(110,'TRK69d3ababc2eb5','Meet Pitroda','1234567890',1,'kathan chauhan','1234567890',50,'parcel','abv','standard','delivered','2026-04-11','2026-04-06',62,42,'2026-04-06 07:18:43','2026-04-07 10:54:15','dhl','air'),(112,'TRK69d4c119c9875','Meet Pitroda','1234567890',1,'kathan chauhan','1234567890',50,'document','ab','standard','delivered','2026-04-09','2026-04-07',62,42,'2026-04-07 08:32:25','2026-04-07 10:54:29','dhl','air'),(113,'TRK69d4ca65b3eb9','Meet Pitroda','1234567890',1,'kathan chauhan','1234567890',50,'parcel','abc','standard','delivered','2026-04-09','2026-04-07',62,42,'2026-04-07 09:12:05','2026-04-07 11:11:23','fedex','surface'),(114,'TRK69d6182c4556c','Meet Pitroda','1234567890',1,'kathan chauhan','1234567890',50,'parcel','abc','standard','delivered','2026-04-13','2026-04-08',62,42,'2026-04-08 08:56:12','2026-04-08 09:07:42','dhl','surface'),(115,'TRK69d645bf016e7','Meet Pitroda','1234567890',1,'kathan chauhan','1234567890',50,'box','abc','standard','delivered','2026-04-13','2026-04-08',62,42,'2026-04-08 12:10:39','2026-04-08 12:37:03','dhl','surface'),(116,'TRK69d64c1dda081','Meet Pitroda','1234567890',1,'kathan chauhan','1234567890',50,'document','abv','standard','delivered','2026-04-13','2026-04-08',62,42,'2026-04-08 12:37:49','2026-04-08 13:39:58','dhl','air'),(120,'TRK69d72cfff0a3d','Meet Pitroda','1234567890',1,'kathan chauhan','1234567890',50,'parcel','abc','standard','delivered','2026-04-14','2026-04-09',62,42,'2026-04-09 04:37:19','2026-04-09 08:12:37','dhl','air'),(126,'TRK69d7364d6db4b','Meet Pitroda','1234567890',1,'kathan chauhan','1234567890',50,'parcel','ab','standard','delivered','2026-04-14','2026-04-09',62,42,'2026-04-09 05:17:01','2026-04-09 08:12:25','fedex','air'),(137,'TRK69d74c7f3fb48','Meet Pitroda','1234567890',1,'kathan chauhan','1234567890',50,'parcel','ad','standard','delayed','2026-04-17',NULL,62,NULL,'2026-04-09 06:51:43','2026-04-16 04:59:00','dhl','air'),(143,'TRK69d7544c306ef','Meet Pitroda','1234567890',1,'kathan chauhan','1234567890',50,'parcel','ds','standard','delayed','2026-04-17',NULL,62,NULL,'2026-04-09 07:25:00','2026-04-16 04:59:00','fedex','surface'),(144,'TRK69d7552c4d428','Meet Pitroda','1234567890',1,'kathan chauhan','1234567890',50,'parcel','sa','standard','delayed','2026-04-17',NULL,62,NULL,'2026-04-09 07:28:44','2026-04-16 04:59:00','dhl','air'),(172,'TRK69d8e1ecc552e','admin','1234567890',4,'Meet Pitroda','1234567890',40,'parcel','fd','standard','delayed','2026-04-18',NULL,44,NULL,'2026-04-10 11:41:32','2026-04-16 04:59:00','dhl','surface'),(173,'TRK69dc9f0d45361','Meet Pitroda','1234567890',1,'kathan chauhan','1234567890',50,'pallet','abc','standard','delivered','2026-04-18','2026-04-14',62,42,'2026-04-13 07:45:17','2026-04-14 06:36:39','dhl','air'),(176,'TRK69ddeae2c193d','Meet Pitroda','1234567890',1,'kathan chauhan','1234567890',50,'parcel','abc','standard','assigned','2026-04-19',NULL,62,42,'2026-04-14 07:21:06','2026-04-16 08:46:25','dhl','surface'),(177,'TRK69ddf8feb28de','Meet Pitroda','1234567890',1,'kathan chauhan','1234567890',50,'parcel','ds','standard','assigned','2026-04-19',NULL,62,42,'2026-04-14 08:21:18','2026-04-16 08:49:45','dhl','surface'),(178,'TRK69de1bcdcceb8','Meet Pitroda','1234567890',1,'kathan chauhan','1234567890',50,'pallet','sa','standard','assigned','2026-04-19',NULL,62,42,'2026-04-14 10:49:49','2026-04-16 08:45:41','dhl','surface'),(179,'TRK69de1e7c2bb74','John Doe','1234567890',1,'kathan chauhan','1234567890',50,'box','Test shipment','express','in_transit','2026-04-20',NULL,62,42,'2026-04-14 11:01:16','2026-04-15 11:00:03','DHL','air'),(180,'TRK69df208a2c945','John Doe','1234567890',1,'kathan chauhan','1234567890',50,'box','Test shipment','express','pending_payment','2026-04-20',NULL,62,NULL,'2026-04-15 05:22:18','2026-04-15 05:22:19','DHL','air'),(181,'TRK69df2539ad09b','John Doe','1234567890',1,'kathan chauhan','1234567890',50,'box','Test shipment','express','pending_payment','2026-04-20',NULL,62,NULL,'2026-04-15 05:42:17','2026-04-15 05:42:18','DHL','air'),(182,'TRK69df267c47e9a','John Doe','1234567890',1,'kathan chauhan','1234567890',50,'box','Test shipment','express','pending_payment','2026-04-20',NULL,62,NULL,'2026-04-15 05:47:40','2026-04-15 05:47:41','DHL','air'),(183,'TRK69df3a2d4756d','John Doe','1234567890',1,'kathan chauhan','1234567890',50,'box','Test shipment','express','pending_payment','2026-04-20',NULL,62,NULL,'2026-04-15 07:11:41','2026-04-15 07:11:42','DHL','air'),(184,'TRK69df49487e9c0','Meet Pitroda','1234567890',1,'kathan chauhan','1234567890',50,'document','abv','standard','assigned','2026-04-20',NULL,62,42,'2026-04-15 08:16:08','2026-04-16 07:27:30','fedex','air'),(185,'TRK69df4d1d5c6fb','Meet Pitroda','1234567890',1,'kathan chauhan','1234567890',50,'parcel','add','standard','in_transit','2026-04-20',NULL,62,42,'2026-04-15 08:32:29','2026-04-15 11:11:29','dhl','air'),(186,'TRK69df51ccc1764','Meet Pitroda','1234567890',1,'kathan chauhan','1234567890',50,'parcel','ag','standard','out_for_delivery','2026-04-20',NULL,62,42,'2026-04-15 08:52:28','2026-04-15 11:07:22','fedex','air'),(188,'TRK69df7aef19362','Meet Pitroda','1234567890',1,'kathan chauhan','1234567890',50,'parcel','sw','standard','assigned','2026-04-20',NULL,62,42,'2026-04-15 11:47:59','2026-04-16 12:37:14','dhl','air'),(189,'TRK69df7dceaa4b4','Meet Pitroda','1234567890',1,'kathan chauhan','1234567890',50,'document','abc','standard','picked_up','2026-04-20',NULL,62,42,'2026-04-15 12:00:14','2026-04-17 04:43:55','dhl','surface'),(197,'TRK69e0e2913b8f2','meet pitroda','1234567890',1,'kathan chauhan','1234567890',50,'box','Test shipment','express','pending_payment','2026-04-20',NULL,62,NULL,'2026-04-16 13:22:25','2026-04-16 13:22:26','DHL','air');
/*!40000 ALTER TABLE `shipments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Meet Pitroda','mpitroda459@rku.ac.in','1234567890','2026-03-21 03:00:21','$2y$12$yQxBa3cj6S9/ZFIRPD0K4e3oY13L.CoCNSz3eTsd5FaDWp3LwG8rW','staff',NULL,'2026-03-21 02:59:52','2026-04-17 10:33:34',NULL),(42,'Meet Pitroda SDE','meet.p@techxperts.co.in','1234567890','2026-03-27 02:32:18','$2y$12$2iUvm7XYj7cYHRNqeZI.KOfoThUjlEtZsQmcMhl4vmXhuJrymFc.e','agent',NULL,'2026-03-27 02:31:23','2026-03-27 02:32:18',NULL),(43,'kathan','kathan123@gmail.com','1234567890','2026-03-27 08:03:13','$2y$12$2iUvm7XYj7cYHRNqeZI.KOfoThUjlEtZsQmcMhl4vmXhuJrymFc.e','customer',NULL,'2026-03-27 08:04:10','2026-03-30 01:21:41',NULL),(44,'admin','admin123@gmail.com','1234567890','2026-03-27 12:36:35','$2y$12$2iUvm7XYj7cYHRNqeZI.KOfoThUjlEtZsQmcMhl4vmXhuJrymFc.e','admin',NULL,'2026-03-27 12:33:57','2026-03-30 01:24:01',NULL),(62,'Meet Pitroda','pitrodameet021@gmail.com','1234567890','2026-04-02 07:22:17','$2y$12$lTuZxAwWSYts6Wg4SFRIm.lffy.5.liqG8l5Wbhk8wvEivp///58G','customer',NULL,'2026-03-30 03:27:28','2026-03-30 03:27:28',NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-04-18 11:50:34
