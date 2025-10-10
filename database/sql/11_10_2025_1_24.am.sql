-- MySQL dump 10.13  Distrib 8.0.43, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: nhatro_platform
-- ------------------------------------------------------
-- Server version	9.4.0

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
-- Table structure for table `amenities`
--

DROP TABLE IF EXISTS `amenities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `amenities` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `key_code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `deleted_by` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key_code` (`key_code`),
  KEY `amenities_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `amenities_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tiện ích';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amenities`
--

LOCK TABLES `amenities` WRITE;
/*!40000 ALTER TABLE `amenities` DISABLE KEYS */;
/*!40000 ALTER TABLE `amenities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `actor_id` bigint unsigned DEFAULT NULL,
  `action` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `entity_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `entity_id` bigint unsigned DEFAULT NULL,
  `before_json` json DEFAULT NULL,
  `after_json` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_audit_entity` (`entity_type`,`entity_id`),
  KEY `fk_audit_actor` (`actor_id`),
  CONSTRAINT `fk_audit_actor` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Audit log';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `booking_deposits`
--

DROP TABLE IF EXISTS `booking_deposits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `booking_deposits` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID đặt cọc',
  `unit_id` bigint unsigned NOT NULL COMMENT 'Phòng giữ chỗ',
  `tenant_user_id` bigint unsigned DEFAULT NULL COMMENT 'User nếu khách đã có tài khoản',
  `lead_id` bigint unsigned DEFAULT NULL COMMENT 'Lead nếu khách chưa có tài khoản',
  `amount` decimal(12,2) NOT NULL COMMENT 'Số tiền cọc',
  `invoice_id` bigint unsigned DEFAULT NULL COMMENT 'Hoá đơn cọc liên kết',
  `payment_status` enum('unpaid','paid','refunded','failed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'unpaid' COMMENT 'Trạng thái thanh toán',
  `hold_until` datetime DEFAULT NULL COMMENT 'Hết hạn giữ chỗ',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `deleted_by` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_bd_tenant` (`tenant_user_id`),
  KEY `fk_bd_lead` (`lead_id`),
  KEY `idx_deposits_unit` (`unit_id`),
  KEY `idx_deposits_status` (`payment_status`),
  KEY `idx_deposits_hold_until` (`hold_until`),
  KEY `fk_bd_invoice` (`invoice_id`),
  KEY `booking_deposits_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `booking_deposits_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_bd_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_bd_lead` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_bd_tenant` FOREIGN KEY (`tenant_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_bd_unit` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Đặt cọc giữ chỗ';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `booking_deposits`
--

LOCK TABLES `booking_deposits` WRITE;
/*!40000 ALTER TABLE `booking_deposits` DISABLE KEYS */;
/*!40000 ALTER TABLE `booking_deposits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
INSERT INTO `cache` VALUES ('laravel-cache-superadmin_dashboard_data','a:26:{s:18:\"totalOrganizations\";i:2;s:25:\"newOrganizationsThisMonth\";i:2;s:10:\"totalUsers\";i:7;s:17:\"newUsersThisMonth\";i:7;s:23:\"monthlyRecurringRevenue\";i:1000000;s:13:\"mrrGrowthRate\";i:0;s:9:\"churnRate\";i:0;s:21:\"averageRevenuePerUser\";d:142857.14285714287;s:21:\"customerLifetimeValue\";d:1714285.7142857146;s:23:\"customerAcquisitionCost\";i:5000000;s:11:\"ltvCacRatio\";d:0.3428571428571429;s:19:\"activeOrganizations\";i:2;s:21:\"inactiveOrganizations\";i:0;s:16:\"newOrganizations\";i:2;s:15:\"apiResponseTime\";i:166;s:12:\"systemUptime\";s:5:\"99.9%\";s:14:\"activeSessions\";i:1;s:12:\"pageLoadTime\";i:220;s:11:\"memoryUsage\";i:69;s:8:\"cpuUsage\";i:20;s:14:\"conversionRate\";i:32;s:18:\"openSupportTickets\";i:9;s:15:\"featureRequests\";i:16;s:20:\"customerSatisfaction\";i:5;s:16:\"recentActivities\";O:29:\"Illuminate\\Support\\Collection\":2:{s:8:\"\0*\0items\";a:0:{}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}s:16:\"topOrganizations\";O:39:\"Illuminate\\Database\\Eloquent\\Collection\":2:{s:8:\"\0*\0items\";a:2:{i:0;O:23:\"App\\Models\\Organization\":34:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:13:\"organizations\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:14:{s:2:\"id\";i:2;s:4:\"code\";N;s:4:\"name\";s:13:\"Tổ chức 2\";s:5:\"phone\";s:10:\"0901000000\";s:5:\"email\";s:16:\"info2@orgmain.vn\";s:8:\"tax_code\";N;s:7:\"address\";s:43:\"Phuong Son, Dong Van, Thanh Chuong, Nghe An\";s:6:\"status\";i:1;s:10:\"created_at\";s:19:\"2025-10-10 22:05:48\";s:10:\"updated_at\";s:19:\"2025-10-10 22:05:48\";s:10:\"deleted_at\";N;s:10:\"deleted_by\";N;s:11:\"users_count\";i:0;s:16:\"properties_count\";i:0;}s:11:\"\0*\0original\";a:12:{s:2:\"id\";i:2;s:4:\"code\";N;s:4:\"name\";s:13:\"Tổ chức 2\";s:5:\"phone\";s:10:\"0901000000\";s:5:\"email\";s:16:\"info2@orgmain.vn\";s:8:\"tax_code\";N;s:7:\"address\";s:43:\"Phuong Son, Dong Van, Thanh Chuong, Nghe An\";s:6:\"status\";i:1;s:10:\"created_at\";s:19:\"2025-10-10 22:05:48\";s:10:\"updated_at\";s:19:\"2025-10-10 22:05:48\";s:10:\"deleted_at\";N;s:10:\"deleted_by\";N;}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:2:{s:6:\"status\";s:7:\"boolean\";s:10:\"deleted_at\";s:8:\"datetime\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:2:{s:5:\"users\";O:39:\"Illuminate\\Database\\Eloquent\\Collection\":2:{s:8:\"\0*\0items\";a:0:{}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}s:10:\"properties\";O:39:\"Illuminate\\Database\\Eloquent\\Collection\":2:{s:8:\"\0*\0items\";a:0:{}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:8:{i:0;s:4:\"code\";i:1;s:4:\"name\";i:2;s:5:\"phone\";i:3;s:5:\"email\";i:4;s:8:\"tax_code\";i:5;s:7:\"address\";i:6;s:6:\"status\";i:7;s:10:\"deleted_by\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}s:16:\"\0*\0forceDeleting\";b:0;}i:1;O:23:\"App\\Models\\Organization\":34:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:13:\"organizations\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:14:{s:2:\"id\";i:1;s:4:\"code\";s:8:\"ORG_MAIN\";s:4:\"name\";s:25:\"Tổ chức mặc định\";s:5:\"phone\";s:10:\"0901000000\";s:5:\"email\";s:15:\"info@orgmain.vn\";s:8:\"tax_code\";N;s:7:\"address\";N;s:6:\"status\";i:1;s:10:\"created_at\";s:19:\"2025-10-08 20:28:45\";s:10:\"updated_at\";s:19:\"2025-10-08 20:28:45\";s:10:\"deleted_at\";N;s:10:\"deleted_by\";N;s:11:\"users_count\";i:5;s:16:\"properties_count\";i:5;}s:11:\"\0*\0original\";a:12:{s:2:\"id\";i:1;s:4:\"code\";s:8:\"ORG_MAIN\";s:4:\"name\";s:25:\"Tổ chức mặc định\";s:5:\"phone\";s:10:\"0901000000\";s:5:\"email\";s:15:\"info@orgmain.vn\";s:8:\"tax_code\";N;s:7:\"address\";N;s:6:\"status\";i:1;s:10:\"created_at\";s:19:\"2025-10-08 20:28:45\";s:10:\"updated_at\";s:19:\"2025-10-08 20:28:45\";s:10:\"deleted_at\";N;s:10:\"deleted_by\";N;}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:2:{s:6:\"status\";s:7:\"boolean\";s:10:\"deleted_at\";s:8:\"datetime\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:2:{s:5:\"users\";O:39:\"Illuminate\\Database\\Eloquent\\Collection\":2:{s:8:\"\0*\0items\";a:5:{i:0;O:15:\"App\\Models\\User\":36:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:5:\"users\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:13:{s:2:\"id\";i:3;s:5:\"email\";s:17:\"admin@example.com\";s:5:\"phone\";s:10:\"0901000001\";s:13:\"password_hash\";s:60:\"$2y$12$62IWJxDMrys848T8uj63nODgDgWCqROiZVyVBcpZb3KPIQw6Zn2nK\";s:9:\"full_name\";s:12:\"Admin System\";s:10:\"avatar_url\";N;s:6:\"status\";i:1;s:13:\"last_login_at\";N;s:10:\"created_at\";s:19:\"2025-10-09 03:19:06\";s:10:\"updated_at\";s:19:\"2025-10-09 03:46:48\";s:10:\"deleted_at\";N;s:10:\"deleted_by\";N;s:17:\"phone_verified_at\";N;}s:11:\"\0*\0original\";a:19:{s:2:\"id\";i:3;s:5:\"email\";s:17:\"admin@example.com\";s:5:\"phone\";s:10:\"0901000001\";s:13:\"password_hash\";s:60:\"$2y$12$62IWJxDMrys848T8uj63nODgDgWCqROiZVyVBcpZb3KPIQw6Zn2nK\";s:9:\"full_name\";s:12:\"Admin System\";s:10:\"avatar_url\";N;s:6:\"status\";i:1;s:13:\"last_login_at\";N;s:10:\"created_at\";s:19:\"2025-10-09 03:19:06\";s:10:\"updated_at\";s:19:\"2025-10-09 03:46:48\";s:10:\"deleted_at\";N;s:10:\"deleted_by\";N;s:17:\"phone_verified_at\";N;s:21:\"pivot_organization_id\";i:1;s:13:\"pivot_user_id\";i:3;s:13:\"pivot_role_id\";i:1;s:12:\"pivot_status\";s:6:\"active\";s:16:\"pivot_created_at\";s:19:\"2025-10-09 03:28:45\";s:16:\"pivot_updated_at\";s:19:\"2025-10-09 03:28:45\";}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:3:{s:13:\"last_login_at\";s:8:\"datetime\";s:6:\"status\";s:7:\"integer\";s:10:\"deleted_at\";s:8:\"datetime\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:1:{s:5:\"pivot\";O:44:\"Illuminate\\Database\\Eloquent\\Relations\\Pivot\":37:{s:13:\"\0*\0connection\";N;s:8:\"\0*\0table\";s:18:\"organization_users\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:0;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:6:{s:15:\"organization_id\";i:1;s:7:\"user_id\";i:3;s:7:\"role_id\";i:1;s:6:\"status\";s:6:\"active\";s:10:\"created_at\";s:19:\"2025-10-09 03:28:45\";s:10:\"updated_at\";s:19:\"2025-10-09 03:28:45\";}s:11:\"\0*\0original\";a:6:{s:15:\"organization_id\";i:1;s:7:\"user_id\";i:3;s:7:\"role_id\";i:1;s:6:\"status\";s:6:\"active\";s:10:\"created_at\";s:19:\"2025-10-09 03:28:45\";s:10:\"updated_at\";s:19:\"2025-10-09 03:28:45\";}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:0:{}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:0:{}s:10:\"\0*\0guarded\";a:0:{}s:11:\"pivotParent\";O:23:\"App\\Models\\Organization\":34:{s:13:\"\0*\0connection\";N;s:8:\"\0*\0table\";s:13:\"organizations\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:0;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:0:{}s:11:\"\0*\0original\";a:0:{}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:2:{s:6:\"status\";s:7:\"boolean\";s:10:\"deleted_at\";s:8:\"datetime\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:8:{i:0;s:4:\"code\";i:1;s:4:\"name\";i:2;s:5:\"phone\";i:3;s:5:\"email\";i:4;s:8:\"tax_code\";i:5;s:7:\"address\";i:6;s:6:\"status\";i:7;s:10:\"deleted_by\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}s:16:\"\0*\0forceDeleting\";b:0;}s:12:\"pivotRelated\";O:15:\"App\\Models\\User\":36:{s:13:\"\0*\0connection\";N;s:8:\"\0*\0table\";N;s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:0;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:0:{}s:11:\"\0*\0original\";a:0:{}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:3:{s:13:\"last_login_at\";s:8:\"datetime\";s:6:\"status\";s:7:\"integer\";s:10:\"deleted_at\";s:8:\"datetime\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:1:{i:0;s:13:\"password_hash\";}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:5:{i:0;s:9:\"full_name\";i:1;s:5:\"email\";i:2;s:13:\"password_hash\";i:3;s:6:\"status\";i:4;s:10:\"deleted_by\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}s:19:\"\0*\0authPasswordName\";s:8:\"password\";s:20:\"\0*\0rememberTokenName\";s:14:\"remember_token\";s:16:\"\0*\0forceDeleting\";b:0;}s:13:\"\0*\0foreignKey\";s:15:\"organization_id\";s:13:\"\0*\0relatedKey\";s:7:\"user_id\";}}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:1:{i:0;s:13:\"password_hash\";}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:5:{i:0;s:9:\"full_name\";i:1;s:5:\"email\";i:2;s:13:\"password_hash\";i:3;s:6:\"status\";i:4;s:10:\"deleted_by\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}s:19:\"\0*\0authPasswordName\";s:8:\"password\";s:20:\"\0*\0rememberTokenName\";s:14:\"remember_token\";s:16:\"\0*\0forceDeleting\";b:0;}i:1;O:15:\"App\\Models\\User\":36:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:5:\"users\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:13:{s:2:\"id\";i:4;s:5:\"email\";s:19:\"manager@example.com\";s:5:\"phone\";s:10:\"0902000002\";s:13:\"password_hash\";s:60:\"$2y$12$62IWJxDMrys848T8uj63nODgDgWCqROiZVyVBcpZb3KPIQw6Zn2nK\";s:9:\"full_name\";s:14:\"Manager Nguyen\";s:10:\"avatar_url\";N;s:6:\"status\";i:1;s:13:\"last_login_at\";N;s:10:\"created_at\";s:19:\"2025-10-09 03:19:06\";s:10:\"updated_at\";s:19:\"2025-10-09 03:46:48\";s:10:\"deleted_at\";N;s:10:\"deleted_by\";N;s:17:\"phone_verified_at\";N;}s:11:\"\0*\0original\";a:19:{s:2:\"id\";i:4;s:5:\"email\";s:19:\"manager@example.com\";s:5:\"phone\";s:10:\"0902000002\";s:13:\"password_hash\";s:60:\"$2y$12$62IWJxDMrys848T8uj63nODgDgWCqROiZVyVBcpZb3KPIQw6Zn2nK\";s:9:\"full_name\";s:14:\"Manager Nguyen\";s:10:\"avatar_url\";N;s:6:\"status\";i:1;s:13:\"last_login_at\";N;s:10:\"created_at\";s:19:\"2025-10-09 03:19:06\";s:10:\"updated_at\";s:19:\"2025-10-09 03:46:48\";s:10:\"deleted_at\";N;s:10:\"deleted_by\";N;s:17:\"phone_verified_at\";N;s:21:\"pivot_organization_id\";i:1;s:13:\"pivot_user_id\";i:4;s:13:\"pivot_role_id\";i:2;s:12:\"pivot_status\";s:6:\"active\";s:16:\"pivot_created_at\";s:19:\"2025-10-10 01:18:46\";s:16:\"pivot_updated_at\";s:19:\"2025-10-10 01:18:46\";}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:3:{s:13:\"last_login_at\";s:8:\"datetime\";s:6:\"status\";s:7:\"integer\";s:10:\"deleted_at\";s:8:\"datetime\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:1:{s:5:\"pivot\";O:44:\"Illuminate\\Database\\Eloquent\\Relations\\Pivot\":37:{s:13:\"\0*\0connection\";N;s:8:\"\0*\0table\";s:18:\"organization_users\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:0;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:6:{s:15:\"organization_id\";i:1;s:7:\"user_id\";i:4;s:7:\"role_id\";i:2;s:6:\"status\";s:6:\"active\";s:10:\"created_at\";s:19:\"2025-10-10 01:18:46\";s:10:\"updated_at\";s:19:\"2025-10-10 01:18:46\";}s:11:\"\0*\0original\";a:6:{s:15:\"organization_id\";i:1;s:7:\"user_id\";i:4;s:7:\"role_id\";i:2;s:6:\"status\";s:6:\"active\";s:10:\"created_at\";s:19:\"2025-10-10 01:18:46\";s:10:\"updated_at\";s:19:\"2025-10-10 01:18:46\";}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:0:{}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:0:{}s:10:\"\0*\0guarded\";a:0:{}s:11:\"pivotParent\";r:270;s:12:\"pivotRelated\";r:316;s:13:\"\0*\0foreignKey\";s:15:\"organization_id\";s:13:\"\0*\0relatedKey\";s:7:\"user_id\";}}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:1:{i:0;s:13:\"password_hash\";}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:5:{i:0;s:9:\"full_name\";i:1;s:5:\"email\";i:2;s:13:\"password_hash\";i:3;s:6:\"status\";i:4;s:10:\"deleted_by\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}s:19:\"\0*\0authPasswordName\";s:8:\"password\";s:20:\"\0*\0rememberTokenName\";s:14:\"remember_token\";s:16:\"\0*\0forceDeleting\";b:0;}i:2;O:15:\"App\\Models\\User\":36:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:5:\"users\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:13:{s:2:\"id\";i:5;s:5:\"email\";s:17:\"agent@example.com\";s:5:\"phone\";s:10:\"0903000003\";s:13:\"password_hash\";s:60:\"$2y$12$62IWJxDMrys848T8uj63nODgDgWCqROiZVyVBcpZb3KPIQw6Zn2nK\";s:9:\"full_name\";s:10:\"Agent Tran\";s:10:\"avatar_url\";N;s:6:\"status\";i:1;s:13:\"last_login_at\";N;s:10:\"created_at\";s:19:\"2025-10-09 03:19:06\";s:10:\"updated_at\";s:19:\"2025-10-09 03:46:48\";s:10:\"deleted_at\";N;s:10:\"deleted_by\";N;s:17:\"phone_verified_at\";N;}s:11:\"\0*\0original\";a:19:{s:2:\"id\";i:5;s:5:\"email\";s:17:\"agent@example.com\";s:5:\"phone\";s:10:\"0903000003\";s:13:\"password_hash\";s:60:\"$2y$12$62IWJxDMrys848T8uj63nODgDgWCqROiZVyVBcpZb3KPIQw6Zn2nK\";s:9:\"full_name\";s:10:\"Agent Tran\";s:10:\"avatar_url\";N;s:6:\"status\";i:1;s:13:\"last_login_at\";N;s:10:\"created_at\";s:19:\"2025-10-09 03:19:06\";s:10:\"updated_at\";s:19:\"2025-10-09 03:46:48\";s:10:\"deleted_at\";N;s:10:\"deleted_by\";N;s:17:\"phone_verified_at\";N;s:21:\"pivot_organization_id\";i:1;s:13:\"pivot_user_id\";i:5;s:13:\"pivot_role_id\";i:3;s:12:\"pivot_status\";s:6:\"active\";s:16:\"pivot_created_at\";s:19:\"2025-10-10 01:20:30\";s:16:\"pivot_updated_at\";s:19:\"2025-10-10 01:20:30\";}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:3:{s:13:\"last_login_at\";s:8:\"datetime\";s:6:\"status\";s:7:\"integer\";s:10:\"deleted_at\";s:8:\"datetime\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:1:{s:5:\"pivot\";O:44:\"Illuminate\\Database\\Eloquent\\Relations\\Pivot\":37:{s:13:\"\0*\0connection\";N;s:8:\"\0*\0table\";s:18:\"organization_users\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:0;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:6:{s:15:\"organization_id\";i:1;s:7:\"user_id\";i:5;s:7:\"role_id\";i:3;s:6:\"status\";s:6:\"active\";s:10:\"created_at\";s:19:\"2025-10-10 01:20:30\";s:10:\"updated_at\";s:19:\"2025-10-10 01:20:30\";}s:11:\"\0*\0original\";a:6:{s:15:\"organization_id\";i:1;s:7:\"user_id\";i:5;s:7:\"role_id\";i:3;s:6:\"status\";s:6:\"active\";s:10:\"created_at\";s:19:\"2025-10-10 01:20:30\";s:10:\"updated_at\";s:19:\"2025-10-10 01:20:30\";}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:0:{}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:0:{}s:10:\"\0*\0guarded\";a:0:{}s:11:\"pivotParent\";r:270;s:12:\"pivotRelated\";r:316;s:13:\"\0*\0foreignKey\";s:15:\"organization_id\";s:13:\"\0*\0relatedKey\";s:7:\"user_id\";}}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:1:{i:0;s:13:\"password_hash\";}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:5:{i:0;s:9:\"full_name\";i:1;s:5:\"email\";i:2;s:13:\"password_hash\";i:3;s:6:\"status\";i:4;s:10:\"deleted_by\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}s:19:\"\0*\0authPasswordName\";s:8:\"password\";s:20:\"\0*\0rememberTokenName\";s:14:\"remember_token\";s:16:\"\0*\0forceDeleting\";b:0;}i:3;O:15:\"App\\Models\\User\":36:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:5:\"users\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:13:{s:2:\"id\";i:6;s:5:\"email\";s:20:\"landlord@example.com\";s:5:\"phone\";s:10:\"0904000004\";s:13:\"password_hash\";s:60:\"$2y$12$62IWJxDMrys848T8uj63nODgDgWCqROiZVyVBcpZb3KPIQw6Zn2nK\";s:9:\"full_name\";s:11:\"Landlord Le\";s:10:\"avatar_url\";N;s:6:\"status\";i:1;s:13:\"last_login_at\";N;s:10:\"created_at\";s:19:\"2025-10-09 03:19:06\";s:10:\"updated_at\";s:19:\"2025-10-09 03:46:48\";s:10:\"deleted_at\";N;s:10:\"deleted_by\";N;s:17:\"phone_verified_at\";N;}s:11:\"\0*\0original\";a:19:{s:2:\"id\";i:6;s:5:\"email\";s:20:\"landlord@example.com\";s:5:\"phone\";s:10:\"0904000004\";s:13:\"password_hash\";s:60:\"$2y$12$62IWJxDMrys848T8uj63nODgDgWCqROiZVyVBcpZb3KPIQw6Zn2nK\";s:9:\"full_name\";s:11:\"Landlord Le\";s:10:\"avatar_url\";N;s:6:\"status\";i:1;s:13:\"last_login_at\";N;s:10:\"created_at\";s:19:\"2025-10-09 03:19:06\";s:10:\"updated_at\";s:19:\"2025-10-09 03:46:48\";s:10:\"deleted_at\";N;s:10:\"deleted_by\";N;s:17:\"phone_verified_at\";N;s:21:\"pivot_organization_id\";i:1;s:13:\"pivot_user_id\";i:6;s:13:\"pivot_role_id\";i:4;s:12:\"pivot_status\";s:6:\"active\";s:16:\"pivot_created_at\";s:19:\"2025-10-10 01:18:47\";s:16:\"pivot_updated_at\";s:19:\"2025-10-10 01:18:47\";}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:3:{s:13:\"last_login_at\";s:8:\"datetime\";s:6:\"status\";s:7:\"integer\";s:10:\"deleted_at\";s:8:\"datetime\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:1:{s:5:\"pivot\";O:44:\"Illuminate\\Database\\Eloquent\\Relations\\Pivot\":37:{s:13:\"\0*\0connection\";N;s:8:\"\0*\0table\";s:18:\"organization_users\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:0;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:6:{s:15:\"organization_id\";i:1;s:7:\"user_id\";i:6;s:7:\"role_id\";i:4;s:6:\"status\";s:6:\"active\";s:10:\"created_at\";s:19:\"2025-10-10 01:18:47\";s:10:\"updated_at\";s:19:\"2025-10-10 01:18:47\";}s:11:\"\0*\0original\";a:6:{s:15:\"organization_id\";i:1;s:7:\"user_id\";i:6;s:7:\"role_id\";i:4;s:6:\"status\";s:6:\"active\";s:10:\"created_at\";s:19:\"2025-10-10 01:18:47\";s:10:\"updated_at\";s:19:\"2025-10-10 01:18:47\";}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:0:{}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:0:{}s:10:\"\0*\0guarded\";a:0:{}s:11:\"pivotParent\";r:270;s:12:\"pivotRelated\";r:316;s:13:\"\0*\0foreignKey\";s:15:\"organization_id\";s:13:\"\0*\0relatedKey\";s:7:\"user_id\";}}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:1:{i:0;s:13:\"password_hash\";}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:5:{i:0;s:9:\"full_name\";i:1;s:5:\"email\";i:2;s:13:\"password_hash\";i:3;s:6:\"status\";i:4;s:10:\"deleted_by\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}s:19:\"\0*\0authPasswordName\";s:8:\"password\";s:20:\"\0*\0rememberTokenName\";s:14:\"remember_token\";s:16:\"\0*\0forceDeleting\";b:0;}i:4;O:15:\"App\\Models\\User\":36:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:5:\"users\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:13:{s:2:\"id\";i:7;s:5:\"email\";s:18:\"tenant@example.com\";s:5:\"phone\";s:10:\"0905000005\";s:13:\"password_hash\";s:60:\"$2y$12$62IWJxDMrys848T8uj63nODgDgWCqROiZVyVBcpZb3KPIQw6Zn2nK\";s:9:\"full_name\";s:12:\"Tenant Hoang\";s:10:\"avatar_url\";N;s:6:\"status\";i:1;s:13:\"last_login_at\";N;s:10:\"created_at\";s:19:\"2025-10-09 03:19:06\";s:10:\"updated_at\";s:19:\"2025-10-09 03:46:48\";s:10:\"deleted_at\";N;s:10:\"deleted_by\";N;s:17:\"phone_verified_at\";N;}s:11:\"\0*\0original\";a:19:{s:2:\"id\";i:7;s:5:\"email\";s:18:\"tenant@example.com\";s:5:\"phone\";s:10:\"0905000005\";s:13:\"password_hash\";s:60:\"$2y$12$62IWJxDMrys848T8uj63nODgDgWCqROiZVyVBcpZb3KPIQw6Zn2nK\";s:9:\"full_name\";s:12:\"Tenant Hoang\";s:10:\"avatar_url\";N;s:6:\"status\";i:1;s:13:\"last_login_at\";N;s:10:\"created_at\";s:19:\"2025-10-09 03:19:06\";s:10:\"updated_at\";s:19:\"2025-10-09 03:46:48\";s:10:\"deleted_at\";N;s:10:\"deleted_by\";N;s:17:\"phone_verified_at\";N;s:21:\"pivot_organization_id\";i:1;s:13:\"pivot_user_id\";i:7;s:13:\"pivot_role_id\";i:5;s:12:\"pivot_status\";s:6:\"active\";s:16:\"pivot_created_at\";s:19:\"2025-10-10 01:18:47\";s:16:\"pivot_updated_at\";s:19:\"2025-10-10 01:18:47\";}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:3:{s:13:\"last_login_at\";s:8:\"datetime\";s:6:\"status\";s:7:\"integer\";s:10:\"deleted_at\";s:8:\"datetime\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:1:{s:5:\"pivot\";O:44:\"Illuminate\\Database\\Eloquent\\Relations\\Pivot\":37:{s:13:\"\0*\0connection\";N;s:8:\"\0*\0table\";s:18:\"organization_users\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:0;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:6:{s:15:\"organization_id\";i:1;s:7:\"user_id\";i:7;s:7:\"role_id\";i:5;s:6:\"status\";s:6:\"active\";s:10:\"created_at\";s:19:\"2025-10-10 01:18:47\";s:10:\"updated_at\";s:19:\"2025-10-10 01:18:47\";}s:11:\"\0*\0original\";a:6:{s:15:\"organization_id\";i:1;s:7:\"user_id\";i:7;s:7:\"role_id\";i:5;s:6:\"status\";s:6:\"active\";s:10:\"created_at\";s:19:\"2025-10-10 01:18:47\";s:10:\"updated_at\";s:19:\"2025-10-10 01:18:47\";}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:0:{}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:0:{}s:10:\"\0*\0guarded\";a:0:{}s:11:\"pivotParent\";r:270;s:12:\"pivotRelated\";r:316;s:13:\"\0*\0foreignKey\";s:15:\"organization_id\";s:13:\"\0*\0relatedKey\";s:7:\"user_id\";}}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:1:{i:0;s:13:\"password_hash\";}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:5:{i:0;s:9:\"full_name\";i:1;s:5:\"email\";i:2;s:13:\"password_hash\";i:3;s:6:\"status\";i:4;s:10:\"deleted_by\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}s:19:\"\0*\0authPasswordName\";s:8:\"password\";s:20:\"\0*\0rememberTokenName\";s:14:\"remember_token\";s:16:\"\0*\0forceDeleting\";b:0;}}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}s:10:\"properties\";O:39:\"Illuminate\\Database\\Eloquent\\Collection\":2:{s:8:\"\0*\0items\";a:5:{i:0;O:19:\"App\\Models\\Property\":34:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:10:\"properties\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:15:{s:2:\"id\";i:5;s:15:\"organization_id\";i:1;s:8:\"owner_id\";i:6;s:16:\"property_type_id\";i:6;s:4:\"name\";s:22:\"Bất động sản La\";s:11:\"location_id\";i:5;s:16:\"location_id_2025\";N;s:11:\"description\";s:2:\"è\";s:12:\"total_floors\";i:6;s:11:\"total_rooms\";i:100;s:6:\"status\";i:1;s:10:\"created_at\";s:19:\"2025-10-09 18:15:12\";s:10:\"updated_at\";s:19:\"2025-10-10 02:17:27\";s:10:\"deleted_at\";N;s:10:\"deleted_by\";N;}s:11:\"\0*\0original\";a:15:{s:2:\"id\";i:5;s:15:\"organization_id\";i:1;s:8:\"owner_id\";i:6;s:16:\"property_type_id\";i:6;s:4:\"name\";s:22:\"Bất động sản La\";s:11:\"location_id\";i:5;s:16:\"location_id_2025\";N;s:11:\"description\";s:2:\"è\";s:12:\"total_floors\";i:6;s:11:\"total_rooms\";i:100;s:6:\"status\";i:1;s:10:\"created_at\";s:19:\"2025-10-09 18:15:12\";s:10:\"updated_at\";s:19:\"2025-10-10 02:17:27\";s:10:\"deleted_at\";N;s:10:\"deleted_by\";N;}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:4:{s:6:\"status\";s:7:\"integer\";s:12:\"total_floors\";s:7:\"integer\";s:11:\"total_rooms\";s:7:\"integer\";s:10:\"deleted_at\";s:8:\"datetime\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:11:{i:0;s:15:\"organization_id\";i:1;s:8:\"owner_id\";i:2;s:16:\"property_type_id\";i:3;s:4:\"name\";i:4;s:11:\"location_id\";i:5;s:16:\"location_id_2025\";i:6;s:11:\"description\";i:7;s:12:\"total_floors\";i:8;s:11:\"total_rooms\";i:9;s:6:\"status\";i:10;s:10:\"deleted_by\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}s:16:\"\0*\0forceDeleting\";b:0;}i:1;O:19:\"App\\Models\\Property\":34:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:10:\"properties\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:15:{s:2:\"id\";i:6;s:15:\"organization_id\";i:1;s:8:\"owner_id\";i:6;s:16:\"property_type_id\";N;s:4:\"name\";s:25:\"Bất động sản Ba Ka\";s:11:\"location_id\";i:6;s:16:\"location_id_2025\";i:5;s:11:\"description\";s:3:\"zXz\";s:12:\"total_floors\";i:6;s:11:\"total_rooms\";i:4;s:6:\"status\";i:1;s:10:\"created_at\";s:19:\"2025-10-09 22:29:09\";s:10:\"updated_at\";s:19:\"2025-10-10 02:17:25\";s:10:\"deleted_at\";N;s:10:\"deleted_by\";N;}s:11:\"\0*\0original\";a:15:{s:2:\"id\";i:6;s:15:\"organization_id\";i:1;s:8:\"owner_id\";i:6;s:16:\"property_type_id\";N;s:4:\"name\";s:25:\"Bất động sản Ba Ka\";s:11:\"location_id\";i:6;s:16:\"location_id_2025\";i:5;s:11:\"description\";s:3:\"zXz\";s:12:\"total_floors\";i:6;s:11:\"total_rooms\";i:4;s:6:\"status\";i:1;s:10:\"created_at\";s:19:\"2025-10-09 22:29:09\";s:10:\"updated_at\";s:19:\"2025-10-10 02:17:25\";s:10:\"deleted_at\";N;s:10:\"deleted_by\";N;}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:4:{s:6:\"status\";s:7:\"integer\";s:12:\"total_floors\";s:7:\"integer\";s:11:\"total_rooms\";s:7:\"integer\";s:10:\"deleted_at\";s:8:\"datetime\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:11:{i:0;s:15:\"organization_id\";i:1;s:8:\"owner_id\";i:2;s:16:\"property_type_id\";i:3;s:4:\"name\";i:4;s:11:\"location_id\";i:5;s:16:\"location_id_2025\";i:6;s:11:\"description\";i:7;s:12:\"total_floors\";i:8;s:11:\"total_rooms\";i:9;s:6:\"status\";i:10;s:10:\"deleted_by\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}s:16:\"\0*\0forceDeleting\";b:0;}i:2;O:19:\"App\\Models\\Property\":34:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:10:\"properties\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:15:{s:2:\"id\";i:7;s:15:\"organization_id\";i:1;s:8:\"owner_id\";i:6;s:16:\"property_type_id\";i:6;s:4:\"name\";s:24:\"Bất động sản Ba K\";s:11:\"location_id\";i:7;s:16:\"location_id_2025\";i:6;s:11:\"description\";s:7:\"gchbhcg\";s:12:\"total_floors\";i:6;s:11:\"total_rooms\";i:50;s:6:\"status\";i:1;s:10:\"created_at\";s:19:\"2025-10-10 01:10:21\";s:10:\"updated_at\";s:19:\"2025-10-10 02:17:23\";s:10:\"deleted_at\";N;s:10:\"deleted_by\";N;}s:11:\"\0*\0original\";a:15:{s:2:\"id\";i:7;s:15:\"organization_id\";i:1;s:8:\"owner_id\";i:6;s:16:\"property_type_id\";i:6;s:4:\"name\";s:24:\"Bất động sản Ba K\";s:11:\"location_id\";i:7;s:16:\"location_id_2025\";i:6;s:11:\"description\";s:7:\"gchbhcg\";s:12:\"total_floors\";i:6;s:11:\"total_rooms\";i:50;s:6:\"status\";i:1;s:10:\"created_at\";s:19:\"2025-10-10 01:10:21\";s:10:\"updated_at\";s:19:\"2025-10-10 02:17:23\";s:10:\"deleted_at\";N;s:10:\"deleted_by\";N;}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:4:{s:6:\"status\";s:7:\"integer\";s:12:\"total_floors\";s:7:\"integer\";s:11:\"total_rooms\";s:7:\"integer\";s:10:\"deleted_at\";s:8:\"datetime\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:11:{i:0;s:15:\"organization_id\";i:1;s:8:\"owner_id\";i:2;s:16:\"property_type_id\";i:3;s:4:\"name\";i:4;s:11:\"location_id\";i:5;s:16:\"location_id_2025\";i:6;s:11:\"description\";i:7;s:12:\"total_floors\";i:8;s:11:\"total_rooms\";i:9;s:6:\"status\";i:10;s:10:\"deleted_by\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}s:16:\"\0*\0forceDeleting\";b:0;}i:3;O:19:\"App\\Models\\Property\":34:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:10:\"properties\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:15:{s:2:\"id\";i:8;s:15:\"organization_id\";i:1;s:8:\"owner_id\";i:6;s:16:\"property_type_id\";i:6;s:4:\"name\";s:14:\"Nhà trọ ABC\";s:11:\"location_id\";i:2;s:16:\"location_id_2025\";N;s:11:\"description\";s:45:\"Nhà trọ sạch sẽ, gần trường học\";s:12:\"total_floors\";i:4;s:11:\"total_rooms\";i:20;s:6:\"status\";i:1;s:10:\"created_at\";s:19:\"2025-10-10 01:18:24\";s:10:\"updated_at\";s:19:\"2025-10-10 01:18:24\";s:10:\"deleted_at\";N;s:10:\"deleted_by\";N;}s:11:\"\0*\0original\";a:15:{s:2:\"id\";i:8;s:15:\"organization_id\";i:1;s:8:\"owner_id\";i:6;s:16:\"property_type_id\";i:6;s:4:\"name\";s:14:\"Nhà trọ ABC\";s:11:\"location_id\";i:2;s:16:\"location_id_2025\";N;s:11:\"description\";s:45:\"Nhà trọ sạch sẽ, gần trường học\";s:12:\"total_floors\";i:4;s:11:\"total_rooms\";i:20;s:6:\"status\";i:1;s:10:\"created_at\";s:19:\"2025-10-10 01:18:24\";s:10:\"updated_at\";s:19:\"2025-10-10 01:18:24\";s:10:\"deleted_at\";N;s:10:\"deleted_by\";N;}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:4:{s:6:\"status\";s:7:\"integer\";s:12:\"total_floors\";s:7:\"integer\";s:11:\"total_rooms\";s:7:\"integer\";s:10:\"deleted_at\";s:8:\"datetime\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:11:{i:0;s:15:\"organization_id\";i:1;s:8:\"owner_id\";i:2;s:16:\"property_type_id\";i:3;s:4:\"name\";i:4;s:11:\"location_id\";i:5;s:16:\"location_id_2025\";i:6;s:11:\"description\";i:7;s:12:\"total_floors\";i:8;s:11:\"total_rooms\";i:9;s:6:\"status\";i:10;s:10:\"deleted_by\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}s:16:\"\0*\0forceDeleting\";b:0;}i:4;O:19:\"App\\Models\\Property\":34:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:10:\"properties\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:15:{s:2:\"id\";i:9;s:15:\"organization_id\";i:1;s:8:\"owner_id\";i:6;s:16:\"property_type_id\";i:7;s:4:\"name\";s:18:\"Chung cư mini XYZ\";s:11:\"location_id\";i:3;s:16:\"location_id_2025\";N;s:11:\"description\";s:28:\"Chung cư mini hiện đại\";s:12:\"total_floors\";i:6;s:11:\"total_rooms\";i:30;s:6:\"status\";i:1;s:10:\"created_at\";s:19:\"2025-10-10 01:18:24\";s:10:\"updated_at\";s:19:\"2025-10-10 01:18:24\";s:10:\"deleted_at\";N;s:10:\"deleted_by\";N;}s:11:\"\0*\0original\";a:15:{s:2:\"id\";i:9;s:15:\"organization_id\";i:1;s:8:\"owner_id\";i:6;s:16:\"property_type_id\";i:7;s:4:\"name\";s:18:\"Chung cư mini XYZ\";s:11:\"location_id\";i:3;s:16:\"location_id_2025\";N;s:11:\"description\";s:28:\"Chung cư mini hiện đại\";s:12:\"total_floors\";i:6;s:11:\"total_rooms\";i:30;s:6:\"status\";i:1;s:10:\"created_at\";s:19:\"2025-10-10 01:18:24\";s:10:\"updated_at\";s:19:\"2025-10-10 01:18:24\";s:10:\"deleted_at\";N;s:10:\"deleted_by\";N;}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:4:{s:6:\"status\";s:7:\"integer\";s:12:\"total_floors\";s:7:\"integer\";s:11:\"total_rooms\";s:7:\"integer\";s:10:\"deleted_at\";s:8:\"datetime\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:11:{i:0;s:15:\"organization_id\";i:1;s:8:\"owner_id\";i:2;s:16:\"property_type_id\";i:3;s:4:\"name\";i:4;s:11:\"location_id\";i:5;s:16:\"location_id_2025\";i:6;s:11:\"description\";i:7;s:12:\"total_floors\";i:8;s:11:\"total_rooms\";i:9;s:6:\"status\";i:10;s:10:\"deleted_by\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}s:16:\"\0*\0forceDeleting\";b:0;}}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:8:{i:0;s:4:\"code\";i:1;s:4:\"name\";i:2;s:5:\"phone\";i:3;s:5:\"email\";i:4;s:8:\"tax_code\";i:5;s:7:\"address\";i:6;s:6:\"status\";i:7;s:10:\"deleted_by\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}s:16:\"\0*\0forceDeleting\";b:0;}}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}}',1760109125);
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `commission_event_splits`
--

DROP TABLE IF EXISTS `commission_event_splits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `commission_event_splits` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `event_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `role_key` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `percent_share` decimal(5,2) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `payroll_item_id` bigint unsigned DEFAULT NULL,
  `status` enum('pending','booked','paid','reversed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_event_user` (`event_id`,`user_id`),
  KEY `idx_ces_status` (`status`),
  KEY `fk_ces_user` (`user_id`),
  KEY `fk_ces_pitem` (`payroll_item_id`),
  CONSTRAINT `fk_ces_event` FOREIGN KEY (`event_id`) REFERENCES `commission_events` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ces_pitem` FOREIGN KEY (`payroll_item_id`) REFERENCES `payroll_items` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_ces_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Hoa hồng chia theo cá nhân';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `commission_event_splits`
--

LOCK TABLES `commission_event_splits` WRITE;
/*!40000 ALTER TABLE `commission_event_splits` DISABLE KEYS */;
/*!40000 ALTER TABLE `commission_event_splits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `commission_events`
--

DROP TABLE IF EXISTS `commission_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `commission_events` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `policy_id` bigint unsigned NOT NULL,
  `organization_id` bigint unsigned NOT NULL,
  `trigger_event` enum('deposit_paid','lease_signed','invoice_paid','viewing_done','listing_published') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ref_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ref_id` bigint unsigned NOT NULL,
  `lease_id` bigint unsigned DEFAULT NULL,
  `listing_id` bigint unsigned DEFAULT NULL,
  `unit_id` bigint unsigned DEFAULT NULL,
  `agent_id` bigint unsigned DEFAULT NULL,
  `occurred_at` datetime NOT NULL,
  `amount_base` decimal(12,2) NOT NULL,
  `commission_total` decimal(12,2) NOT NULL,
  `status` enum('pending','approved','paid','reversed','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `deleted_by` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_ce_org_time` (`organization_id`,`occurred_at`),
  KEY `idx_ce_status` (`status`),
  KEY `fk_ce_policy` (`policy_id`),
  KEY `fk_ce_lease` (`lease_id`),
  KEY `fk_ce_listing` (`listing_id`),
  KEY `fk_ce_unit` (`unit_id`),
  KEY `fk_ce_agent` (`agent_id`),
  KEY `commission_events_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `commission_events_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_ce_agent` FOREIGN KEY (`agent_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_ce_lease` FOREIGN KEY (`lease_id`) REFERENCES `leases` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_ce_listing` FOREIGN KEY (`listing_id`) REFERENCES `listings` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_ce_org` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ce_policy` FOREIGN KEY (`policy_id`) REFERENCES `commission_policies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ce_unit` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Sự kiện hoa hồng';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `commission_events`
--

LOCK TABLES `commission_events` WRITE;
/*!40000 ALTER TABLE `commission_events` DISABLE KEYS */;
/*!40000 ALTER TABLE `commission_events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `commission_policies`
--

DROP TABLE IF EXISTS `commission_policies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `commission_policies` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `organization_id` bigint unsigned NOT NULL,
  `code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `trigger_event` enum('deposit_paid','lease_signed','invoice_paid','viewing_done','listing_published') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `basis` enum('cash','accrual') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'cash',
  `calc_type` enum('percent','flat','tiered') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `percent_value` decimal(5,2) DEFAULT NULL,
  `flat_amount` decimal(12,2) DEFAULT NULL,
  `apply_limit_months` tinyint DEFAULT NULL,
  `min_amount` decimal(12,2) DEFAULT NULL,
  `cap_amount` decimal(12,2) DEFAULT NULL,
  `filters_json` json DEFAULT NULL,
  `active` tinyint DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `deleted_by` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `idx_cp_org_active` (`organization_id`,`active`),
  KEY `commission_policies_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `commission_policies_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_cp_org` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Chính sách hoa hồng';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `commission_policies`
--

LOCK TABLES `commission_policies` WRITE;
/*!40000 ALTER TABLE `commission_policies` DISABLE KEYS */;
INSERT INTO `commission_policies` VALUES (1,1,'COMM_LEASE_SIGNED','Hoa hồng ký hợp đồng','lease_signed','cash','percent',5.00,NULL,12,150000.00,5000000.00,NULL,1,'2025-10-09 21:42:14','2025-10-10 08:36:50',NULL,NULL),(2,1,'COMM_DEPOSIT_PAI','Hoa hồng thanh toán cọc','deposit_paid','cash','percent',2.00,NULL,1,100000.00,1000000.00,NULL,1,'2025-10-09 21:42:14','2025-10-10 08:30:37',NULL,NULL),(3,1,'COMM_VIEWING_DONE','Hoa hồng xem phòng','viewing_done','cash','flat',NULL,100000.00,1,50000.00,200000.00,NULL,1,'2025-10-09 21:42:14','2025-10-09 21:42:14',NULL,NULL),(4,1,'COMM_LISTING_PUBLISHED','Hoa hồng đăng tin','listing_published','cash','flat',NULL,50000.00,1,25000.00,100000.00,NULL,1,'2025-10-09 21:42:14','2025-10-09 21:42:14',NULL,NULL),(7,1,'COMM_DEPOSIT_PAIDD','acxcczxc','deposit_paid','cash','percent',5.00,NULL,1,50000.00,200000.00,NULL,1,'2025-10-10 08:33:06','2025-10-10 08:33:44','2025-10-10 08:33:44',4);
/*!40000 ALTER TABLE `commission_policies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `commission_policy_splits`
--

DROP TABLE IF EXISTS `commission_policy_splits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `commission_policy_splits` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `policy_id` bigint unsigned NOT NULL,
  `role_key` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `percent_share` decimal(5,2) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_policy_role` (`policy_id`,`role_key`),
  CONSTRAINT `fk_cps_policy` FOREIGN KEY (`policy_id`) REFERENCES `commission_policies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Phân chia hoa hồng';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `commission_policy_splits`
--

LOCK TABLES `commission_policy_splits` WRITE;
/*!40000 ALTER TABLE `commission_policy_splits` DISABLE KEYS */;
INSERT INTO `commission_policy_splits` VALUES (5,3,'agent',100.00),(6,4,'agent',100.00),(7,2,'agent',80.00),(8,2,'manager',20.00),(13,1,'agent',70.00),(14,1,'manager',30.00);
/*!40000 ALTER TABLE `commission_policy_splits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `documents`
--

DROP TABLE IF EXISTS `documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `documents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `owner_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `owner_id` bigint unsigned DEFAULT NULL,
  `file_url` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mime_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uploaded_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `deleted_by` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_documents_owner` (`owner_type`,`owner_id`),
  KEY `fk_docs_uploader` (`uploaded_by`),
  KEY `documents_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `documents_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_docs_uploader` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tài liệu/ảnh';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `documents`
--

LOCK TABLES `documents` WRITE;
/*!40000 ALTER TABLE `documents` DISABLE KEYS */;
/*!40000 ALTER TABLE `documents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `geo_countries`
--

DROP TABLE IF EXISTS `geo_countries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `geo_countries` (
  `code` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_local` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Quốc gia';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `geo_countries`
--

LOCK TABLES `geo_countries` WRITE;
/*!40000 ALTER TABLE `geo_countries` DISABLE KEYS */;
INSERT INTO `geo_countries` VALUES ('VN','Vietnam','Việt Nam','2025-10-08 17:06:26');
/*!40000 ALTER TABLE `geo_countries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `geo_districts`
--

DROP TABLE IF EXISTS `geo_districts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `geo_districts` (
  `code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `province_code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_local` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kind` enum('district','town','urban_district') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'district',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`code`),
  KEY `idx_gdist_province` (`province_code`),
  CONSTRAINT `fk_gdist_province` FOREIGN KEY (`province_code`) REFERENCES `geo_provinces` (`code`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Quận/Huyện/Thị xã';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `geo_districts`
--

LOCK TABLES `geo_districts` WRITE;
/*!40000 ALTER TABLE `geo_districts` DISABLE KEYS */;
INSERT INTO `geo_districts` VALUES ('DN-HAI','VN-DN','Hai Chau','Hải Châu','urban_district','2025-10-08 19:59:25'),('HCM-Q1','VN-HCM','District 1','Quận 1','urban_district','2025-10-08 19:59:25'),('HN-BD','VN-HN','Ba Dinh','Ba Đình','urban_district','2025-10-08 19:59:26'),('HN-CG','VN-HN','Cau Giay','Cầu Giấy','urban_district','2025-10-08 19:59:25'),('HN-DH','VN-HN','Dong Da','Đống Đa','urban_district','2025-10-08 19:59:26'),('HN-HAI','VN-HN','Hai Chau (DN placeholder)','','urban_district','2025-10-08 19:59:26'),('HN-HK','VN-HN','Hoan Kiem','Hoàn Kiếm','urban_district','2025-10-08 19:59:25'),('HN-HM','VN-HN','Hai Ba Trung','Hai Bà Trưng','urban_district','2025-10-08 19:59:26'),('HN-ND','VN-HN','Nam Tu Liem','Nam Từ Liêm','urban_district','2025-10-08 19:59:26'),('HN-TB','VN-HN','Tu Liem','Bắc Từ Liêm','urban_district','2025-10-08 19:59:26'),('HN-TX','VN-HN','Thanh Xuan','Thanh Xuân','urban_district','2025-10-08 19:59:26');
/*!40000 ALTER TABLE `geo_districts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `geo_provinces`
--

DROP TABLE IF EXISTS `geo_provinces`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `geo_provinces` (
  `code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `country_code` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_local` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kind` enum('province','city','municipality') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'province',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`code`),
  KEY `idx_gprov_country` (`country_code`),
  CONSTRAINT `fk_gprov_country` FOREIGN KEY (`country_code`) REFERENCES `geo_countries` (`code`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tỉnh/Thành phố';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `geo_provinces`
--

LOCK TABLES `geo_provinces` WRITE;
/*!40000 ALTER TABLE `geo_provinces` DISABLE KEYS */;
INSERT INTO `geo_provinces` VALUES ('VN-DN','VN','Da Nang','Đà Nẵng','city','2025-10-08 19:59:25'),('VN-HCM','VN','Ho Chi Minh City','TP. Hồ Chí Minh','city','2025-10-08 19:59:25'),('VN-HN','VN','Ha Noi','Hà Nội','city','2025-10-08 19:59:25');
/*!40000 ALTER TABLE `geo_provinces` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `geo_provinces_2025`
--

DROP TABLE IF EXISTS `geo_provinces_2025`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `geo_provinces_2025` (
  `code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `country_code` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_local` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kind` enum('province','city','municipality') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'province',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`code`),
  KEY `geo_provinces_2025_country_code_index` (`country_code`),
  CONSTRAINT `geo_provinces_2025_country_code_foreign` FOREIGN KEY (`country_code`) REFERENCES `geo_countries` (`code`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `geo_provinces_2025`
--

LOCK TABLES `geo_provinces_2025` WRITE;
/*!40000 ALTER TABLE `geo_provinces_2025` DISABLE KEYS */;
INSERT INTO `geo_provinces_2025` VALUES ('VN-HN','VN','Ha Noi','Hà Nội','city','2025-10-09 15:19:36');
/*!40000 ALTER TABLE `geo_provinces_2025` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `geo_streets`
--

DROP TABLE IF EXISTS `geo_streets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `geo_streets` (
  `code` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ward_code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_local` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`code`),
  KEY `idx_gstreet_ward` (`ward_code`),
  CONSTRAINT `geo_streets_ward_code_foreign` FOREIGN KEY (`ward_code`) REFERENCES `geo_wards` (`code`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `geo_streets`
--

LOCK TABLES `geo_streets` WRITE;
/*!40000 ALTER TABLE `geo_streets` DISABLE KEYS */;
INSERT INTO `geo_streets` VALUES ('CG-TRUNGHUA-01','CG-TRUNGHUA','Trung Hoa','Trung Hòa',NULL,NULL),('CG-YENHOA-01','CG-YENHOA','Yen Hoa','Yên Hòa',NULL,NULL),('HK-HANGBAI-01','HK-HANGBAI','Hang Bai','Hàng Bài',NULL,NULL),('HK-TRANGTIEN-01','HK-TRANGTIEN','Trang Tien','Tràng Tiền',NULL,NULL);
/*!40000 ALTER TABLE `geo_streets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `geo_wards`
--

DROP TABLE IF EXISTS `geo_wards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `geo_wards` (
  `code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `district_code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_local` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kind` enum('ward','commune','townlet') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'ward',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`code`),
  KEY `idx_gward_district` (`district_code`),
  CONSTRAINT `fk_gward_district` FOREIGN KEY (`district_code`) REFERENCES `geo_districts` (`code`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Phường/Xã/Thị trấn';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `geo_wards`
--

LOCK TABLES `geo_wards` WRITE;
/*!40000 ALTER TABLE `geo_wards` DISABLE KEYS */;
INSERT INTO `geo_wards` VALUES ('CG-DICHVONG','HN-CG','Dich Vong','Dịch Vọng','ward','2025-10-08 19:59:25'),('CG-DICHVONGHAU','HN-CG','Dich Vong Hau','Dịch Vọng Hậu','ward','2025-10-08 19:59:26'),('CG-MAIDICH','HN-CG','Mai Dich','Mai Dịch','ward','2025-10-08 19:59:26'),('CG-NGHIAO','HN-CG','Nghia Do','Nghĩa Đô','ward','2025-10-08 19:59:26'),('CG-NGHITAN','HN-CG','Nghia Tan','Nghĩa Tân','ward','2025-10-08 19:59:26'),('CG-QUANCHE','HN-CG','Quan Hoa','Quan Hoa','ward','2025-10-08 19:59:26'),('CG-TRUNGHUA','HN-CG','Trung Hoa','Trung Hòa','ward','2025-10-08 19:59:26'),('CG-YENHOA','HN-CG','Yen Hoa','Yên Hòa','ward','2025-10-08 19:59:26'),('HAI-THACHTHANG','DN-HAI','Thach Thang','Thạch Thang','ward','2025-10-08 19:59:25'),('HK-CHUONGDUONG','HN-HK','Chuong Duong','Chương Dương','ward','2025-10-08 19:59:26'),('HK-DONGXUAN','HN-HK','Dong Xuan','Đồng Xuân','ward','2025-10-08 19:59:26'),('HK-HANGBAC','HN-HK','Hang Bac','Hàng Bạc','ward','2025-10-08 19:59:26'),('HK-HANGBAI','HN-HK','Hang Bai','Hàng Bài','ward','2025-10-08 19:59:26'),('HK-HANGBUOM','HN-HK','Hang Buom','Hàng Buồm','ward','2025-10-08 19:59:26'),('HK-LYTHAITO','HN-HK','Ly Thai To','Lý Thái Tổ','ward','2025-10-08 19:59:26'),('HK-TRANGTIEN','HN-HK','Trang Tien','Tràng Tiền','ward','2025-10-08 19:59:25'),('HK-TRANPHU','HN-HK','Tran Phu','Trần Phú','ward','2025-10-08 19:59:26'),('HK-TRUNGLYET','HN-HK','Trung Liet','Trưng Liệt','ward','2025-10-08 19:59:26'),('Q1-BENTHANH','HCM-Q1','Ben Thanh','Bến Thành','ward','2025-10-08 19:59:25');
/*!40000 ALTER TABLE `geo_wards` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `geo_wards_2025`
--

DROP TABLE IF EXISTS `geo_wards_2025`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `geo_wards_2025` (
  `code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `district_code` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_local` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kind` enum('ward','commune','townlet') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ward',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`code`),
  KEY `geo_wards_2025_district_code_index` (`district_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `geo_wards_2025`
--

LOCK TABLES `geo_wards_2025` WRITE;
/*!40000 ALTER TABLE `geo_wards_2025` DISABLE KEYS */;
INSERT INTO `geo_wards_2025` VALUES ('HN-BD-P01','VN-HN','Phuc Xa','Phúc Xá','ward','2025-10-09 15:19:36'),('HN-BD-P02','VN-HN','Truc Bach','Trúc Bạch','ward','2025-10-09 15:19:36'),('HN-BD-P03','VN-HN','Giang Vo','Giảng Võ','ward','2025-10-09 15:19:36'),('HN-CG-P01','VN-HN','Dich Vong','Dịch Vọng','ward','2025-10-09 15:19:36'),('HN-CG-P02','VN-HN','Nghia Tan','Nghĩa Tân','ward','2025-10-09 15:19:36'),('HN-CG-P03','VN-HN','Yen Hoa','Yên Hòa','ward','2025-10-09 15:19:36'),('HN-HK-P01','VN-HN','Hang Bai','Hàng Bài','ward','2025-10-09 15:19:36'),('HN-HK-P02','VN-HN','Trang Tien','Tràng Tiền','ward','2025-10-09 15:19:36'),('HN-TX-P01','VN-HN','Thanh Xuan Bac','Thanh Xuân Bắc','ward','2025-10-09 15:19:36'),('HN-TX-P02','VN-HN','Khuong Trung','Khương Trung','ward','2025-10-09 15:19:36');
/*!40000 ALTER TABLE `geo_wards_2025` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invoice_items`
--

DROP TABLE IF EXISTS `invoice_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `invoice_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `invoice_id` bigint unsigned NOT NULL,
  `item_type` enum('rent','service','meter','deposit','other') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'other',
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quantity` decimal(12,3) DEFAULT '1.000',
  `unit_price` decimal(12,2) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `meta_json` json DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_invoice_items_invoice` (`invoice_id`),
  CONSTRAINT `fk_item_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Dòng hóa đơn';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoice_items`
--

LOCK TABLES `invoice_items` WRITE;
/*!40000 ALTER TABLE `invoice_items` DISABLE KEYS */;
INSERT INTO `invoice_items` VALUES (5,4,'rent','Tiền thuê phòng tháng 03/2025',1.000,4500000.00,4500000.00,NULL),(8,2,'rent','Tiền thuê phòng tháng 01/2025',1.000,5000000.01,5000000.01,NULL),(9,5,'rent','Tiền thuê phòng',1.000,5000000.00,5000000.00,NULL),(10,6,'rent','Tiền thuê phòng tháng 10/2025',1.000,3000000.00,3000000.00,NULL),(11,6,'service','Internet',1.000,100000.00,100000.00,NULL),(12,6,'service','Giữ xe',1.000,100000.00,100000.00,NULL),(13,7,'rent','Tiền thuê phòng',1.000,5000000.00,5000000.00,NULL),(14,7,'service','Điện',1.000,4000.00,4000.00,NULL),(15,7,'service','Nước',1.000,25000.00,25000.00,NULL),(16,3,'rent','Tiền thuê phòng tháng 02/2025',1.000,6000000.00,6000000.00,NULL),(17,3,'service','Dịch vụ điện nước',1.000,500000.00,500000.00,NULL);
/*!40000 ALTER TABLE `invoice_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invoices`
--

DROP TABLE IF EXISTS `invoices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `invoices` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `organization_id` bigint unsigned DEFAULT NULL,
  `lease_id` bigint unsigned DEFAULT NULL,
  `invoice_no` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `issue_date` date NOT NULL,
  `due_date` date NOT NULL,
  `status` enum('draft','issued','paid','overdue','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'draft',
  `subtotal` decimal(12,2) DEFAULT '0.00',
  `tax_amount` decimal(12,2) DEFAULT '0.00',
  `discount_amount` decimal(12,2) DEFAULT '0.00',
  `total_amount` decimal(12,2) DEFAULT '0.00',
  `currency` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'VND',
  `note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `deleted_by` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_no` (`invoice_no`),
  KEY `idx_invoices_lease_status` (`lease_id`,`status`),
  KEY `idx_invoices_due` (`due_date`),
  KEY `fk_inv_org` (`organization_id`),
  KEY `invoices_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `fk_inv_lease` FOREIGN KEY (`lease_id`) REFERENCES `leases` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_inv_org` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `invoices_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Hóa đơn';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoices`
--

LOCK TABLES `invoices` WRITE;
/*!40000 ALTER TABLE `invoices` DISABLE KEYS */;
INSERT INTO `invoices` VALUES (2,1,4,'INV20250011','2025-01-01','2025-01-31','paid',5000000.01,0.00,0.00,5000000.01,'VND','Hóa đơn tiền thuê tháng 1/2025','2025-10-09 20:22:52','2025-10-09 20:26:23',NULL,NULL),(3,1,6,'INV2025002','2025-02-01','2025-02-28','paid',6000000.00,0.00,100000.00,5900000.00,'VND','Hóa đơn tiền thuê tháng 2/2025','2025-10-09 20:22:52','2025-10-10 12:04:18',NULL,NULL),(4,1,7,'INV2025003','2025-03-01','2025-03-31','draft',4500000.00,0.00,0.00,4500000.00,'VND','Hóa đơn tiền thuê tháng 3/2025','2025-10-09 20:22:52','2025-10-09 20:22:52',NULL,NULL),(5,1,6,'INV202500111','2025-10-10','2025-11-09','draft',5000000.00,0.00,0.00,5000000.00,'VND',NULL,'2025-10-09 20:27:46','2025-10-09 20:27:46',NULL,NULL),(6,1,4,'AUTO20251010033316','2025-10-10','2025-11-09','draft',3200000.00,0.00,0.00,3200000.00,'VND','Hóa đơn tự động với dịch vụ từ hợp đồng','2025-10-09 20:33:16','2025-10-09 20:33:16',NULL,NULL),(7,1,6,NULL,'2025-10-10','2025-11-09','draft',5000000.00,0.00,0.00,5000000.00,'VND',NULL,'2025-10-09 20:34:49','2025-10-09 20:35:15','2025-10-09 20:35:15',4);
/*!40000 ALTER TABLE `invoices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `leads`
--

DROP TABLE IF EXISTS `leads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `leads` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID lead CRM',
  `source` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nguồn: web/zalo/fb/referral/...',
  `name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Tên khách tiềm năng',
  `phone` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'SĐT',
  `email` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Email',
  `desired_city` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Khu vực mong muốn',
  `budget_min` decimal(12,2) DEFAULT NULL COMMENT 'Ngân sách tối thiểu',
  `budget_max` decimal(12,2) DEFAULT NULL COMMENT 'Ngân sách tối đa',
  `note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Ghi chú',
  `status` enum('new','contacted','qualified','lost','converted') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'new' COMMENT 'Trạng thái CRM',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `deleted_by` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_leads_status_created` (`status`,`created_at`),
  KEY `idx_leads_phone` (`phone`),
  KEY `idx_leads_email` (`email`),
  KEY `leads_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `leads_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Lead';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `leads`
--

LOCK TABLES `leads` WRITE;
/*!40000 ALTER TABLE `leads` DISABLE KEYS */;
/*!40000 ALTER TABLE `leads` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lease_residents`
--

DROP TABLE IF EXISTS `lease_residents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lease_residents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `lease_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL COMMENT 'Nếu cư dân có tài khoản → liên kết để theo dõi hóa đơn/ticket',
  `name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_number` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `fk_resident_lease` (`lease_id`),
  KEY `idx_lease_residents_user` (`user_id`),
  CONSTRAINT `fk_resident_lease` FOREIGN KEY (`lease_id`) REFERENCES `leases` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_resident_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Cư dân kèm theo hợp đồng';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lease_residents`
--

LOCK TABLES `lease_residents` WRITE;
/*!40000 ALTER TABLE `lease_residents` DISABLE KEYS */;
/*!40000 ALTER TABLE `lease_residents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lease_services`
--

DROP TABLE IF EXISTS `lease_services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lease_services` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `lease_id` bigint unsigned NOT NULL,
  `service_id` bigint unsigned NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `meta_json` json DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_lease_service` (`lease_id`,`service_id`),
  KEY `fk_ls_service` (`service_id`),
  CONSTRAINT `fk_ls_lease` FOREIGN KEY (`lease_id`) REFERENCES `leases` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ls_service` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Dịch vụ áp cho hợp đồng';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lease_services`
--

LOCK TABLES `lease_services` WRITE;
/*!40000 ALTER TABLE `lease_services` DISABLE KEYS */;
INSERT INTO `lease_services` VALUES (3,4,3,100000.00,NULL),(4,4,4,100000.00,NULL),(5,5,1,1000.00,NULL),(8,7,1,3500.00,NULL),(9,7,2,25000.00,NULL),(10,8,1,3500.00,NULL),(11,8,2,25000.00,NULL),(12,3,1,5000.00,NULL),(13,3,3,100000.00,NULL),(17,6,1,4000.00,NULL),(18,6,2,25000.00,NULL);
/*!40000 ALTER TABLE `lease_services` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `leases`
--

DROP TABLE IF EXISTS `leases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `leases` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `organization_id` bigint unsigned DEFAULT NULL,
  `unit_id` bigint unsigned NOT NULL,
  `tenant_id` bigint unsigned NOT NULL,
  `agent_id` bigint unsigned DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `rent_amount` decimal(12,2) NOT NULL,
  `deposit_amount` decimal(12,2) DEFAULT '0.00',
  `billing_day` tinyint DEFAULT '1',
  `status` enum('draft','active','terminated','expired') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'draft',
  `contract_no` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `signed_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `deleted_by` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_leases_unit_status` (`unit_id`,`status`),
  KEY `idx_leases_tenant` (`tenant_id`),
  KEY `fk_lease_org` (`organization_id`),
  KEY `fk_lease_agent` (`agent_id`),
  KEY `leases_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `fk_lease_agent` FOREIGN KEY (`agent_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_lease_org` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_lease_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_lease_unit` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `leases_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Hợp đồng thuê';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `leases`
--

LOCK TABLES `leases` WRITE;
/*!40000 ALTER TABLE `leases` DISABLE KEYS */;
INSERT INTO `leases` VALUES (3,1,16,7,5,'2025-10-11','2025-10-31',2500000.00,2500000.00,5,'active','HD0001',NULL,'2025-10-09 18:43:58','2025-10-09 20:09:13','2025-10-09 20:09:13',4),(4,1,17,8,4,'2025-08-31','2026-09-05',3000000.00,3000000.00,5,'active','HD0002','2025-08-31 01:43:58','2025-10-09 18:43:58','2025-10-10 13:07:07','2025-10-10 13:07:07',4),(5,1,16,8,5,'2025-10-11','2025-10-30',30000000.00,1000.00,1,'draft','HD1001','2025-10-09 00:00:00','2025-10-09 19:31:24','2025-10-09 20:03:36','2025-10-09 20:03:36',4),(6,1,16,7,4,'2025-10-10','2025-10-25',5000000.00,10000000.00,1,'active','HD10011',NULL,'2025-10-09 20:00:56','2025-10-09 20:29:15',NULL,NULL),(7,1,17,7,4,'2025-02-01','2026-01-31',6000000.00,12000000.00,5,'active','HD1002','2025-02-01 14:30:00','2025-10-09 20:00:56','2025-10-09 20:00:56',NULL,NULL),(8,1,18,7,4,'2025-03-01','2026-02-28',4500000.00,9000000.00,10,'draft','HD1003',NULL,'2025-10-09 20:00:56','2025-10-09 20:11:33','2025-10-09 20:11:33',4);
/*!40000 ALTER TABLE `leases` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `listings`
--

DROP TABLE IF EXISTS `listings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `listings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `unit_id` bigint unsigned NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `price_display` decimal(12,2) DEFAULT NULL,
  `publish_status` enum('draft','published','archived') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'draft',
  `published_at` datetime DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `fk_listings_unit` (`unit_id`),
  KEY `fk_listings_user` (`created_by`),
  FULLTEXT KEY `ft_listings` (`title`,`description`),
  CONSTRAINT `fk_listings_unit` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_listings_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tin đăng';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `listings`
--

LOCK TABLES `listings` WRITE;
/*!40000 ALTER TABLE `listings` DISABLE KEYS */;
/*!40000 ALTER TABLE `listings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `locations`
--

DROP TABLE IF EXISTS `locations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `locations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `country_code` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'VN',
  `province_code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `district_code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ward_code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `street` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `district` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ward` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lat` decimal(10,7) DEFAULT NULL,
  `lng` decimal(10,7) DEFAULT NULL,
  `postal_code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `deleted_by` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_loc_province` (`province_code`),
  KEY `fk_loc_district` (`district_code`),
  KEY `fk_loc_ward` (`ward_code`),
  KEY `idx_loc_codes` (`country_code`,`province_code`,`district_code`,`ward_code`),
  KEY `locations_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `fk_loc_country` FOREIGN KEY (`country_code`) REFERENCES `geo_countries` (`code`) ON DELETE SET NULL,
  CONSTRAINT `fk_loc_district` FOREIGN KEY (`district_code`) REFERENCES `geo_districts` (`code`) ON DELETE SET NULL,
  CONSTRAINT `fk_loc_province` FOREIGN KEY (`province_code`) REFERENCES `geo_provinces` (`code`) ON DELETE SET NULL,
  CONSTRAINT `fk_loc_ward` FOREIGN KEY (`ward_code`) REFERENCES `geo_wards` (`code`) ON DELETE SET NULL,
  CONSTRAINT `locations_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Địa chỉ theo chuẩn geo codes';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `locations`
--

LOCK TABLES `locations` WRITE;
/*!40000 ALTER TABLE `locations` DISABLE KEYS */;
INSERT INTO `locations` VALUES (1,'VN','VN-HN','HN-CG','CG-NGHITAN','12, Dịch Vọng Hậu',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-09 01:59:52','2025-10-09 11:14:31','2025-10-09 11:14:31',4),(2,'VN','VN-HN','HN-HK','HK-HANGBAC','12, Hàng Bạc',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-09 09:13:50','2025-10-09 09:13:50',NULL,NULL),(3,'VN','VN-HN',NULL,NULL,'12, Dịch Vọng Hậu',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-09 09:24:55','2025-10-09 09:24:55',NULL,NULL),(4,'VN','VN-HN','HN-CG','CG-DICHVONGHAU','12, Dịch Vọng Hậu',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-10-09 09:33:26','2025-10-09 09:33:26',NULL,NULL),(5,'VN','VN-HN','HN-HK','HK-HANGBAI','12, Hàng bạc','Vietnam','Ha Noi','Hoan Kiem','Hang Bai',NULL,NULL,NULL,'2025-10-09 11:15:12','2025-10-09 11:15:12',NULL,NULL),(6,'VN','VN-HN','HN-CG','CG-DICHVONGHAU','12, Hàng Bạc','Vietnam','Ha Noi','Cau Giay','Dich Vong Hau',NULL,NULL,NULL,'2025-10-09 15:29:09','2025-10-09 15:29:09',NULL,NULL),(7,'VN','VN-HN','HN-CG','CG-DICHVONG','12, Hàng Bạc','Vietnam','Ha Noi','Cau Giay','Dich Vong',NULL,NULL,NULL,'2025-10-09 18:10:21','2025-10-09 18:10:21',NULL,NULL),(8,'VN','VN-HN','HN-CG','CG-NGHITAN','12, Dịch Vọng Hậu','Vietnam','Ha Noi','Cau Giay','Nghia Tan',NULL,NULL,NULL,'2025-10-09 18:18:24','2025-10-09 18:18:24',NULL,NULL),(9,'VN','VN-HN','HN-HK','HK-HANGBAC','25, Hàng Bạc','Vietnam','Ha Noi','Hoan Kiem','Hang Bac',NULL,NULL,NULL,'2025-10-09 18:18:24','2025-10-09 18:18:24',NULL,NULL);
/*!40000 ALTER TABLE `locations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `locations_2025`
--

DROP TABLE IF EXISTS `locations_2025`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `locations_2025` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `country_code` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'VN',
  `province_code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ward_code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `street` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ward` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lat` decimal(10,7) DEFAULT NULL,
  `lng` decimal(10,7) DEFAULT NULL,
  `postal_code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `deleted_by` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `locations_2025_province_code_index` (`province_code`),
  KEY `locations_2025_ward_code_index` (`ward_code`),
  KEY `locations_2025_country_code_province_code_ward_code_index` (`country_code`,`province_code`,`ward_code`),
  KEY `locations_2025_deleted_by_index` (`deleted_by`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `locations_2025`
--

LOCK TABLES `locations_2025` WRITE;
/*!40000 ALTER TABLE `locations_2025` DISABLE KEYS */;
INSERT INTO `locations_2025` VALUES (1,'VN','VN-HN','HN-CG-P01','12, Dịch Vọng','Vietnam','Ha Noi','Dich Vong',NULL,NULL,NULL,'2025-10-09 15:19:36','2025-10-09 15:19:36',NULL,NULL),(2,'VN','VN-HN','HN-HK-P01','25, Hàng Bài','Vietnam','Ha Noi','Hang Bai',NULL,NULL,NULL,'2025-10-09 15:19:36','2025-10-09 15:19:36',NULL,NULL),(3,'VN','VN-HN','HN-TX-P01','45, Thanh Xuân Bắc','Vietnam','Ha Noi','Thanh Xuan Bac',NULL,NULL,NULL,'2025-10-09 15:19:36','2025-10-09 15:19:36',NULL,NULL),(4,'VN','VN-HN','HN-BD-P03','88, Giảng Võ','Vietnam','Ha Noi','Giang Vo',NULL,NULL,NULL,'2025-10-09 15:19:36','2025-10-09 15:19:36',NULL,NULL),(5,'VN','VN-HN',NULL,NULL,'Vietnam','Ha Noi',NULL,NULL,NULL,NULL,'2025-10-09 15:29:09','2025-10-09 15:29:09',NULL,NULL),(6,'VN','VN-HN','HN-BD-P03','12, Hàng Bạc','Vietnam','Ha Noi','Giang Vo',NULL,NULL,NULL,'2025-10-09 18:10:21','2025-10-09 18:10:21',NULL,NULL);
/*!40000 ALTER TABLE `locations_2025` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `meter_readings`
--

DROP TABLE IF EXISTS `meter_readings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `meter_readings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `meter_id` bigint unsigned NOT NULL,
  `reading_date` date NOT NULL,
  `value` decimal(12,3) NOT NULL,
  `image_url` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `taken_by` bigint unsigned DEFAULT NULL,
  `note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_meter_date` (`meter_id`,`reading_date`),
  KEY `fk_mr_user` (`taken_by`),
  CONSTRAINT `fk_mr_meter` FOREIGN KEY (`meter_id`) REFERENCES `meters` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_mr_user` FOREIGN KEY (`taken_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Chỉ số công tơ';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `meter_readings`
--

LOCK TABLES `meter_readings` WRITE;
/*!40000 ALTER TABLE `meter_readings` DISABLE KEYS */;
/*!40000 ALTER TABLE `meter_readings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `meters`
--

DROP TABLE IF EXISTS `meters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `meters` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned DEFAULT NULL,
  `unit_id` bigint unsigned DEFAULT NULL,
  `service_id` bigint unsigned DEFAULT NULL,
  `serial_no` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `installed_at` date DEFAULT NULL,
  `status` tinyint DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `deleted_by` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_meter` (`unit_id`,`service_id`),
  KEY `fk_meter_property` (`property_id`),
  KEY `fk_meter_service` (`service_id`),
  KEY `meters_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `fk_meter_property` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_meter_service` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_meter_unit` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE CASCADE,
  CONSTRAINT `meters_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Đồng hồ/công tơ';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `meters`
--

LOCK TABLES `meters` WRITE;
/*!40000 ALTER TABLE `meters` DISABLE KEYS */;
/*!40000 ALTER TABLE `meters` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2025_10_08_171504_add_phone_to_users',1),(2,'2025_10_08_174513_create_sessions_table',2),(3,'2025_10_08_180000_create_properties_user_table',3),(4,'2025_10_08_181000_seed_vietnam_geo',4),(5,'2025_10_08_181200_create_geo_streets_table',4),(6,'2025_10_08_181300_seed_hanoi_full_geo',4),(7,'2025_10_08_201644_refactor_users_organization',5),(8,'2025_10_08_182000_create_property_types_table',6),(9,'2025_10_08_182100_add_property_type_to_properties',6),(10,'2025_10_08_182200_seed_property_types',6),(11,'2025_10_09_100000_add_soft_deletes_to_core_tables',7),(12,'2025_10_09_100100_add_soft_deletes_to_support_tables',7),(13,'2025_10_09_110000_add_total_rooms_to_properties',8),(14,'2025_10_09_170444_add_icon_to_property_types_table',9),(15,'2025_10_09_170534_add_status_to_property_types_table',10),(16,'2025_10_09_172449_remove_name_local_from_property_types_table',11),(17,'2025_10_09_215109_create_geo_provinces_2025_table',12),(18,'2025_10_09_215117_create_geo_wards_2025_table',12),(19,'2025_10_09_223719_edit_war2025',13),(20,'2025_10_10_161510_fix_payroll_tables_structure',14),(21,'2025_10_10_165453_add_currency_to_salary_advances_table',15),(22,'2025_10_10_164305_create_salary_advances_table',16),(23,'2025_10_10_170859_add_deleted_at_to_salary_advances_table',17),(24,'2025_10_10_170955_add_deleted_by_to_salary_advances_table',18),(27,'2025_10_10_192204_create_cache_table',19);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notification_channels`
--

DROP TABLE IF EXISTS `notification_channels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notification_channels` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `key_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `active` tinyint DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `key_code` (`key_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Kênh thông báo';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notification_channels`
--

LOCK TABLES `notification_channels` WRITE;
/*!40000 ALTER TABLE `notification_channels` DISABLE KEYS */;
/*!40000 ALTER TABLE `notification_channels` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `channel_id` bigint unsigned DEFAULT NULL,
  `to_user_id` bigint unsigned DEFAULT NULL,
  `subject` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` enum('queued','sent','failed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'queued',
  `error_msg` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `sent_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_notifications_status` (`status`,`created_at`),
  KEY `fk_ntf_channel` (`channel_id`),
  KEY `fk_ntf_user` (`to_user_id`),
  CONSTRAINT `fk_ntf_channel` FOREIGN KEY (`channel_id`) REFERENCES `notification_channels` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_ntf_user` FOREIGN KEY (`to_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Thông báo';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `organization_users`
--

DROP TABLE IF EXISTS `organization_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `organization_users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `organization_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  `status` enum('active','inactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_org_user_role` (`organization_id`,`user_id`,`role_id`),
  KEY `fk_ou_user` (`user_id`),
  KEY `fk_ou_role` (`role_id`),
  CONSTRAINT `fk_ou_org` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ou_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_ou_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Thành viên theo tổ chức & vai trò';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `organization_users`
--

LOCK TABLES `organization_users` WRITE;
/*!40000 ALTER TABLE `organization_users` DISABLE KEYS */;
INSERT INTO `organization_users` VALUES (1,1,3,1,'active','2025-10-08 20:28:45','2025-10-08 20:28:45'),(2,1,1,2,'active','2025-10-08 20:28:45','2025-10-08 20:28:45'),(3,1,4,2,'active','2025-10-09 18:18:46','2025-10-09 18:18:46'),(5,1,6,4,'active','2025-10-09 18:18:47','2025-10-09 18:18:47'),(6,1,7,5,'active','2025-10-09 18:18:47','2025-10-09 18:18:47'),(7,1,5,3,'active','2025-10-09 18:20:30','2025-10-09 18:20:30');
/*!40000 ALTER TABLE `organization_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `organizations`
--

DROP TABLE IF EXISTS `organizations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `organizations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tax_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `deleted_by` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `organizations_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `organizations_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tổ chức/đơn vị vận hành';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `organizations`
--

LOCK TABLES `organizations` WRITE;
/*!40000 ALTER TABLE `organizations` DISABLE KEYS */;
INSERT INTO `organizations` VALUES (1,'ORG_MAIN','Tổ chức mặc định','0901000000','info@orgmain.vn',NULL,NULL,1,'2025-10-08 13:28:45','2025-10-08 13:28:45',NULL,NULL),(2,NULL,'Tổ chức 22','0901000000','info2@orgmain.vn',NULL,'Phuong Son, Dong Van, Thanh Chuong, Nghe An',1,'2025-10-10 15:05:48','2025-10-10 15:07:37',NULL,NULL);
/*!40000 ALTER TABLE `organizations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payment_methods`
--

DROP TABLE IF EXISTS `payment_methods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payment_methods` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `key_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key_code` (`key_code`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Phương thức thanh toán';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment_methods`
--

LOCK TABLES `payment_methods` WRITE;
/*!40000 ALTER TABLE `payment_methods` DISABLE KEYS */;
INSERT INTO `payment_methods` VALUES (1,'cash','Tiền mặt'),(2,'bank_qr','Chuyển khoản/QR'),(3,'momo','MoMo'),(4,'zalopay','ZaloPay'),(5,'vnpay','VNPAY');
/*!40000 ALTER TABLE `payment_methods` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `invoice_id` bigint unsigned NOT NULL,
  `method_id` bigint unsigned DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `paid_at` datetime NOT NULL,
  `txn_ref` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pending','success','failed','refunded') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `payer_user_id` bigint unsigned DEFAULT NULL,
  `attachment_url` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `deleted_by` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_payments_invoice` (`invoice_id`),
  KEY `idx_payments_status` (`status`),
  KEY `fk_pay_method` (`method_id`),
  KEY `fk_pay_user` (`payer_user_id`),
  KEY `payments_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `fk_pay_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pay_method` FOREIGN KEY (`method_id`) REFERENCES `payment_methods` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_pay_user` FOREIGN KEY (`payer_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payments_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Thanh toán';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
INSERT INTO `payments` VALUES (1,2,1,5000000.00,'2025-01-01 10:00:00',NULL,'success',8,NULL,'Thanh toán đầy đủ','2025-10-09 20:22:52',NULL,NULL);
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payroll_cycles`
--

DROP TABLE IF EXISTS `payroll_cycles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payroll_cycles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `organization_id` bigint unsigned NOT NULL,
  `period_month` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('open','locked','paid') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `locked_at` timestamp NULL DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payroll_cycles_organization_id_period_month_index` (`organization_id`,`period_month`),
  CONSTRAINT `payroll_cycles_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payroll_cycles`
--

LOCK TABLES `payroll_cycles` WRITE;
/*!40000 ALTER TABLE `payroll_cycles` DISABLE KEYS */;
INSERT INTO `payroll_cycles` VALUES (1,1,'2025-10','locked','2025-10-10 09:39:08',NULL,'Test cycle','2025-10-10 09:17:10','2025-10-10 09:39:08');
/*!40000 ALTER TABLE `payroll_cycles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payroll_items`
--

DROP TABLE IF EXISTS `payroll_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payroll_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `payroll_cycle_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `item_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sign` int NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `ref_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ref_id` bigint unsigned DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payroll_items_user_id_foreign` (`user_id`),
  KEY `payroll_items_payroll_cycle_id_user_id_index` (`payroll_cycle_id`,`user_id`),
  CONSTRAINT `payroll_items_payroll_cycle_id_foreign` FOREIGN KEY (`payroll_cycle_id`) REFERENCES `payroll_cycles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payroll_items_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payroll_items`
--

LOCK TABLES `payroll_items` WRITE;
/*!40000 ALTER TABLE `payroll_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `payroll_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payroll_payslips`
--

DROP TABLE IF EXISTS `payroll_payslips`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payroll_payslips` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `payroll_cycle_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `gross_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `deduction_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `net_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `status` enum('pending','paid') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `paid_at` timestamp NULL DEFAULT NULL,
  `payment_method` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payroll_payslips_user_id_foreign` (`user_id`),
  KEY `payroll_payslips_payroll_cycle_id_user_id_index` (`payroll_cycle_id`,`user_id`),
  CONSTRAINT `payroll_payslips_payroll_cycle_id_foreign` FOREIGN KEY (`payroll_cycle_id`) REFERENCES `payroll_cycles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payroll_payslips_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payroll_payslips`
--

LOCK TABLES `payroll_payslips` WRITE;
/*!40000 ALTER TABLE `payroll_payslips` DISABLE KEYS */;
INSERT INTO `payroll_payslips` VALUES (1,1,3,1000000.00,0.00,1000000.00,'paid','2025-10-10 09:22:06',NULL,NULL,'2025-10-10 09:17:10','2025-10-10 09:22:06'),(2,1,4,14796210.00,0.00,14796210.00,'pending',NULL,NULL,NULL,'2025-10-10 09:21:30','2025-10-10 09:21:30'),(3,1,5,7833983.00,0.00,7833983.00,'pending',NULL,NULL,NULL,'2025-10-10 09:21:30','2025-10-10 09:21:30'),(4,1,6,16153758.00,0.00,16153758.00,'pending',NULL,NULL,NULL,'2025-10-10 09:21:30','2025-10-10 09:21:30'),(5,1,7,13426000.00,4000.00,13422000.00,'pending',NULL,NULL,NULL,'2025-10-10 09:21:31','2025-10-10 09:26:27');
/*!40000 ALTER TABLE `payroll_payslips` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `key_code` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key_code` (`key_code`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Quyền';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,'auth.signup','Đăng ký','2025-10-08 17:06:31','2025-10-08 17:06:31'),(2,'auth.signin','Đăng nhập','2025-10-08 17:06:31','2025-10-08 17:06:31'),(3,'profile.view','Xem hồ sơ','2025-10-08 17:06:31','2025-10-08 17:06:31'),(4,'profile.update','Cập nhật hồ sơ','2025-10-08 17:06:31','2025-10-08 17:06:31'),(5,'listing.create','Tạo tin','2025-10-08 17:06:31','2025-10-08 17:06:31'),(6,'listing.view','Xem tin','2025-10-08 17:06:31','2025-10-08 17:06:31'),(7,'lease.create','Tạo hợp đồng','2025-10-08 17:06:31','2025-10-08 17:06:31'),(8,'lease.view','Xem hợp đồng','2025-10-08 17:06:31','2025-10-08 17:06:31'),(9,'invoice.create','Tạo hóa đơn','2025-10-08 17:06:31','2025-10-08 17:06:31'),(10,'invoice.view','Xem hóa đơn','2025-10-08 17:06:31','2025-10-08 17:06:31'),(11,'payment.create','Ghi nhận thanh toán','2025-10-08 17:06:31','2025-10-08 17:06:31'),(12,'ticket.create','Tạo ticket','2025-10-08 17:06:31','2025-10-08 17:06:31'),(13,'ticket.view','Xem ticket','2025-10-08 17:06:31','2025-10-08 17:06:31');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `properties`
--

DROP TABLE IF EXISTS `properties`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `properties` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `organization_id` bigint unsigned DEFAULT NULL,
  `owner_id` bigint unsigned DEFAULT NULL,
  `property_type_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `location_id` bigint unsigned DEFAULT NULL,
  `location_id_2025` bigint unsigned DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `total_floors` int DEFAULT NULL,
  `total_rooms` int NOT NULL DEFAULT '0' COMMENT 'Tổng số phòng trong tòa nhà',
  `status` tinyint DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `deleted_by` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_properties_org` (`organization_id`),
  KEY `fk_properties_owner` (`owner_id`),
  KEY `fk_properties_location` (`location_id`),
  KEY `properties_property_type_id_index` (`property_type_id`),
  KEY `properties_deleted_by_foreign` (`deleted_by`),
  KEY `properties_total_rooms_index` (`total_rooms`),
  KEY `fk_properties_location2025` (`location_id_2025`),
  CONSTRAINT `fk_properties_location` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_properties_location2025` FOREIGN KEY (`location_id_2025`) REFERENCES `locations_2025` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_properties_org` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_properties_owner` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `properties_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `properties_property_type_id_foreign` FOREIGN KEY (`property_type_id`) REFERENCES `property_types` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tòa nhà/Tài sản';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `properties`
--

LOCK TABLES `properties` WRITE;
/*!40000 ALTER TABLE `properties` DISABLE KEYS */;
INSERT INTO `properties` VALUES (1,1,6,7,'Bất động sản Hà Hà',1,NULL,'Bất động sản',6,24,1,'2025-10-09 01:59:52','2025-10-09 19:17:41','2025-10-09 11:14:31',4),(2,1,6,2,'Bất động sản Ba Ba',2,NULL,'sđfs',6,0,1,'2025-10-09 09:13:50','2025-10-09 19:17:34','2025-10-09 09:10:10',NULL),(3,1,6,1,'Bất động sản La La',3,NULL,'adsas',6,30,0,'2025-10-09 09:24:55','2025-10-09 19:17:31','2025-10-09 09:25:18',NULL),(4,1,6,1,'Bất động sản La',4,NULL,'dfgv',6,500,1,'2025-10-09 09:33:26','2025-10-09 19:17:28','2025-10-09 09:34:18',4),(5,1,6,6,'Bất động sản La',5,NULL,'è',6,100,1,'2025-10-09 11:15:12','2025-10-09 19:17:27',NULL,NULL),(6,1,6,NULL,'Bất động sản Ba Ka',6,5,'zXz',6,4,1,'2025-10-09 15:29:09','2025-10-09 19:17:25',NULL,NULL),(7,1,6,6,'Bất động sản Ba K',7,6,'gchbhcg',6,50,1,'2025-10-09 18:10:21','2025-10-09 19:17:23',NULL,NULL),(8,1,6,6,'Nhà trọ ABC',2,NULL,'Nhà trọ sạch sẽ, gần trường học',4,20,1,'2025-10-09 18:18:24','2025-10-09 18:18:24',NULL,NULL),(9,1,6,7,'Chung cư mini XYZ',3,NULL,'Chung cư mini hiện đại',6,30,1,'2025-10-09 18:18:24','2025-10-09 18:18:24',NULL,NULL);
/*!40000 ALTER TABLE `properties` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `properties_user`
--

DROP TABLE IF EXISTS `properties_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `properties_user` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `role_key` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'agent',
  `assigned_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `deleted_by` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_property_user` (`property_id`,`user_id`),
  KEY `idx_property_user_user` (`user_id`),
  KEY `properties_user_updated_by_foreign` (`updated_by`),
  KEY `properties_user_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `properties_user_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `properties_user_property_id_foreign` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  CONSTRAINT `properties_user_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `properties_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `properties_user`
--

LOCK TABLES `properties_user` WRITE;
/*!40000 ALTER TABLE `properties_user` DISABLE KEYS */;
INSERT INTO `properties_user` VALUES (2,5,4,'manager','2025-10-09 18:18:47','2025-10-09 18:18:47','2025-10-09 18:18:47',NULL,NULL,NULL),(4,6,4,'manager','2025-10-09 18:18:47','2025-10-09 18:18:47','2025-10-09 18:18:47',NULL,NULL,NULL),(6,7,4,'manager','2025-10-09 18:18:47','2025-10-09 18:18:47','2025-10-09 18:18:47',NULL,NULL,NULL),(8,8,4,'manager','2025-10-09 18:18:47','2025-10-09 18:18:47','2025-10-09 18:18:47',NULL,NULL,NULL),(10,9,4,'manager','2025-10-09 18:18:47','2025-10-09 18:18:47','2025-10-09 18:18:47',NULL,NULL,NULL),(11,8,5,'agent','2025-10-09 18:20:30','2025-10-09 18:20:30','2025-10-09 18:20:30',4,NULL,NULL);
/*!40000 ALTER TABLE `properties_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `property_types`
--

DROP TABLE IF EXISTS `property_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `property_types` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `key_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `icon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint NOT NULL DEFAULT '1' COMMENT '1: Active, 0: Inactive',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `deleted_by` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `property_types_key_code_unique` (`key_code`),
  KEY `property_types_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `property_types_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `property_types`
--

LOCK TABLES `property_types` WRITE;
/*!40000 ALTER TABLE `property_types` DISABLE KEYS */;
INSERT INTO `property_types` VALUES (1,'phongtro','Updated Test Type',NULL,NULL,1,NULL,'2025-10-09 10:22:04','2025-10-09 10:22:04',NULL),(2,'chungcumini','Chung cư mini',NULL,NULL,1,NULL,'2025-10-09 10:27:17','2025-10-09 10:27:17',4),(3,'nhanguyencan','Nhà nguyên căn',NULL,NULL,1,NULL,'2025-10-09 10:27:22','2025-10-09 10:27:22',4),(4,'matbang','Mặt bằng',NULL,NULL,1,NULL,'2025-10-09 10:27:27','2025-10-09 10:27:27',4),(5,'chungcu','Chung cư',NULL,NULL,1,NULL,'2025-10-09 10:27:31','2025-10-09 10:27:31',4),(6,'phong_tro','Phòng trọ','Phòng trọ cho thuê ngắn hạn và dài hạn','fas fa-bed',1,'2025-10-09 10:06:05','2025-10-09 10:55:01',NULL,NULL),(7,'chung_cu_mini','Chung cư mini','Chung cư mini với diện tích nhỏ, phù hợp cho cá nhân','fas fa-building',1,'2025-10-09 10:06:05','2025-10-09 10:06:05',NULL,NULL),(8,'nha_nguyen_can','Nhà nguyên căn','Nhà nguyên căn cho thuê','fas fa-home',1,'2025-10-09 10:06:05','2025-10-09 10:23:27',NULL,NULL),(9,'mat_bang','Mặt bằng','Mặt bằng kinh doanh, văn phòng','fas fa-store',1,'2025-10-09 10:06:05','2025-10-09 10:06:05',NULL,NULL),(10,'chung_cu','Chung cư','Chung cư cao cấp','fas fa-city',1,'2025-10-09 10:06:05','2025-10-09 10:06:05',NULL,NULL),(11,'test_1760005272','Test Type','Test description',NULL,1,'2025-10-09 10:21:12','2025-10-09 10:27:11','2025-10-09 10:27:11',4),(12,'test_1760005324','Test Type','Test description',NULL,1,'2025-10-09 10:22:04','2025-10-09 10:27:04','2025-10-09 10:27:04',4),(13,'test_after_migration_1760005574','Updated Test After Migration','Test description',NULL,1,'2025-10-09 10:26:14','2025-10-09 10:26:14','2025-10-09 10:26:14',NULL),(14,'phong_troo','Phòng trọo',NULL,'fas fa-bed',1,'2025-10-09 10:49:55','2025-10-09 10:50:06','2025-10-09 10:50:06',4);
/*!40000 ALTER TABLE `property_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_permissions`
--

DROP TABLE IF EXISTS `role_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_permissions` (
  `role_id` bigint unsigned NOT NULL,
  `permission_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`permission_id`),
  KEY `fk_rp_perm` (`permission_id`),
  CONSTRAINT `fk_rp_perm` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_rp_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Gán quyền cho vai trò';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_permissions`
--

LOCK TABLES `role_permissions` WRITE;
/*!40000 ALTER TABLE `role_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `role_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `key_code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key_code` (`key_code`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Vai trò';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'admin','Quản trị hệ thống','2025-10-08 17:06:31','2025-10-08 17:06:31'),(2,'manager','Quản lý','2025-10-08 17:06:31','2025-10-08 17:06:31'),(3,'agent','CTV/Nhân viên','2025-10-08 17:06:31','2025-10-08 17:06:31'),(4,'landlord','Chủ trọ','2025-10-08 17:06:31','2025-10-08 17:06:31'),(5,'tenant','Người thuê','2025-10-08 17:06:31','2025-10-08 17:06:31');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `salary_advances`
--

DROP TABLE IF EXISTS `salary_advances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `salary_advances` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `organization_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `currency` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'VND',
  `advance_date` date NOT NULL,
  `expected_repayment_date` date NOT NULL,
  `reason` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','approved','rejected','repaid','partially_repaid') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `repaid_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `remaining_amount` decimal(15,2) NOT NULL,
  `repayment_method` enum('payroll_deduction','direct_payment','installment') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'payroll_deduction',
  `installment_months` int DEFAULT NULL,
  `monthly_deduction` decimal(15,2) DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `approved_by` bigint unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `rejected_by` bigint unsigned DEFAULT NULL,
  `rejected_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `deleted_by` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `salary_advances_approved_by_foreign` (`approved_by`),
  KEY `salary_advances_rejected_by_foreign` (`rejected_by`),
  KEY `salary_advances_organization_id_status_index` (`organization_id`,`status`),
  KEY `salary_advances_user_id_status_index` (`user_id`,`status`),
  KEY `salary_advances_advance_date_index` (`advance_date`),
  KEY `salary_advances_expected_repayment_date_index` (`expected_repayment_date`),
  KEY `salary_advances_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `salary_advances_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `salary_advances_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `salary_advances_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `salary_advances_rejected_by_foreign` FOREIGN KEY (`rejected_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `salary_advances_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `salary_advances`
--

LOCK TABLES `salary_advances` WRITE;
/*!40000 ALTER TABLE `salary_advances` DISABLE KEYS */;
INSERT INTO `salary_advances` VALUES (1,1,3,2437779.00,'VND','2025-09-29','2026-01-29','Ứng lương để chi trả học phí','partially_repaid',1503043.00,934736.00,'payroll_deduction',NULL,813000.00,'Dữ liệu mẫu từ seeder',NULL,NULL,NULL,NULL,NULL,'2025-10-10 09:56:12','2025-10-10 10:21:11',NULL,NULL),(2,1,3,2864870.00,'VND','2025-10-04','2025-12-04','Ứng lương để chi trả chi phí y tế','approved',0.00,2864870.00,'installment',3,NULL,'Dữ liệu mẫu từ seeder',5,'2025-10-07 09:56:12',NULL,NULL,NULL,'2025-10-10 09:56:12','2025-10-10 09:56:12',NULL,NULL),(3,1,4,1925123.00,'VND','2025-09-24','2026-01-24','Ứng lương để chi trả học phí','pending',0.00,1925123.00,'payroll_deduction',NULL,963000.00,'Dữ liệu mẫu từ seeder',NULL,NULL,NULL,NULL,NULL,'2025-10-10 09:56:12','2025-10-10 09:56:12',NULL,NULL),(4,1,4,3938078.00,'VND','2025-09-20','2025-10-20','Ứng lương để mua xe máy','rejected',0.00,3938078.00,'payroll_deduction',NULL,656000.00,'Dữ liệu mẫu từ seeder',NULL,NULL,3,'2025-09-22 09:56:12','Không đủ điều kiện ứng lương theo quy định công ty.','2025-10-10 09:56:12','2025-10-10 09:56:12',NULL,NULL),(5,1,4,1413682.00,'VND','2025-10-02','2026-01-02','Ứng lương để chi trả học phí','partially_repaid',690350.00,723332.00,'direct_payment',NULL,NULL,'Dữ liệu mẫu từ seeder',NULL,NULL,NULL,NULL,NULL,'2025-10-10 09:56:12','2025-10-10 09:56:12',NULL,NULL),(6,1,5,2784314.00,'VND','2025-10-05','2025-11-05','Ứng lương để chi trả nợ cũ','pending',0.00,2784314.00,'direct_payment',NULL,NULL,'Dữ liệu mẫu từ seeder',NULL,NULL,NULL,NULL,NULL,'2025-10-10 09:56:12','2025-10-10 09:56:12',NULL,NULL),(7,1,6,2818347.00,'VND','2025-09-12','2025-11-12','Ứng lương để chi trả nợ cũ','partially_repaid',1000000.00,1818347.00,'payroll_deduction',NULL,705000.00,'Dữ liệu mẫu từ seeder',7,'2025-09-13 09:56:12',NULL,NULL,NULL,'2025-10-10 09:56:12','2025-10-10 10:18:57',NULL,NULL),(8,1,6,3718863.00,'VND','2025-09-21','2025-11-21','Ứng lương để chi trả tiền thuê nhà','approved',0.00,3718863.00,'direct_payment',NULL,NULL,'Dữ liệu mẫu từ seeder',3,'2025-09-23 09:56:12',NULL,NULL,NULL,'2025-10-10 09:56:12','2025-10-10 09:56:12',NULL,NULL),(9,1,7,4328714.00,'VND','2025-09-25','2026-03-25','Ứng lương để chi trả học phí','approved',0.00,4328714.00,'payroll_deduction',NULL,2164000.00,'Dữ liệu mẫu từ seeder',5,'2025-09-26 09:56:12',NULL,NULL,NULL,'2025-10-10 09:56:12','2025-10-10 09:56:12',NULL,NULL),(10,1,7,4268019.00,'VND','2025-10-08','2026-02-08','Ứng lương để sửa chữa nhà cửa','partially_repaid',2025637.00,2242382.00,'payroll_deduction',NULL,2134000.00,'Dữ liệu mẫu từ seeder',NULL,NULL,NULL,NULL,NULL,'2025-10-10 09:56:12','2025-10-10 09:56:12',NULL,NULL),(11,1,7,3958945.00,'VND','2025-09-15','2025-12-15','Ứng lương để chi trả nợ cũ','pending',0.00,3958945.00,'installment',4,NULL,'Dữ liệu mẫu từ seeder',NULL,NULL,NULL,NULL,NULL,'2025-10-10 09:56:12','2025-10-10 09:56:12',NULL,NULL),(12,1,5,5000000.00,'VND','2025-10-10','2025-10-22','gdsfg','approved',0.00,5000000.00,'payroll_deduction',NULL,1000000.00,NULL,4,'2025-10-10 12:18:21',NULL,NULL,NULL,'2025-10-10 10:20:38','2025-10-10 12:18:21',NULL,NULL);
/*!40000 ALTER TABLE `salary_advances` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `salary_contracts`
--

DROP TABLE IF EXISTS `salary_contracts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `salary_contracts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `organization_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `base_salary` decimal(15,2) NOT NULL,
  `currency` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'VND',
  `pay_cycle` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'monthly',
  `pay_day` int NOT NULL DEFAULT '1',
  `allowances_json` json DEFAULT NULL,
  `kpi_target_json` json DEFAULT NULL,
  `effective_from` date NOT NULL,
  `effective_to` date DEFAULT NULL,
  `status` enum('active','inactive','terminated') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `deleted_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `salary_contracts_deleted_by_foreign` (`deleted_by`),
  KEY `salary_contracts_organization_id_status_index` (`organization_id`,`status`),
  KEY `salary_contracts_user_id_status_index` (`user_id`,`status`),
  CONSTRAINT `salary_contracts_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `salary_contracts_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `salary_contracts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `salary_contracts`
--

LOCK TABLES `salary_contracts` WRITE;
/*!40000 ALTER TABLE `salary_contracts` DISABLE KEYS */;
INSERT INTO `salary_contracts` VALUES (1,1,3,11976510.00,'VND','monthly',1,'{\"meal\": 301194, \"phone\": 172284, \"transport\": 602704}','{\"sales_target\": 17, \"commission_rate\": 6}','2025-04-10',NULL,'active',NULL,'2025-10-10 09:16:34','2025-10-10 11:28:32',NULL),(2,1,4,13601867.00,'VND','monthly',1,'{\"meal\": 360202, \"phone\": 226455, \"transport\": 607686}','{\"sales_target\": 28, \"commission_rate\": 8}','2025-04-10',NULL,'active',NULL,'2025-10-10 09:16:34','2025-10-10 09:16:34',NULL),(3,1,5,6493031.00,'VND','monthly',1,'{\"meal\": 548315, \"phone\": 110323, \"transport\": 682314}','{\"sales_target\": 49, \"commission_rate\": 2}','2025-04-10',NULL,'active',NULL,'2025-10-10 09:16:34','2025-10-10 09:16:34',NULL),(4,1,6,14404797.00,'VND','monthly',1,'{\"meal\": 653427, \"phone\": 180624, \"transport\": 914910}','{\"sales_target\": 16, \"commission_rate\": 5}','2025-04-10',NULL,'active',NULL,'2025-10-10 09:16:34','2025-10-10 09:16:34',NULL),(5,1,7,11621373.00,'VND','monthly',1,'{\"meal\": 688214, \"phone\": 200417, \"transport\": 916576}','{\"sales_target\": 24, \"commission_rate\": 8}','2025-04-10','2025-10-10','terminated',NULL,'2025-10-10 09:16:34','2025-10-10 11:10:35',NULL),(6,1,3,12000000.00,'VND','monthly',1,'{\"Phụ cấp xăng xe\": 300000, \"Phụ cấp ăn trưa\": 500000}','{\"Doanh số bán hàng\": 10000000}','2025-10-10',NULL,'inactive',NULL,'2025-10-10 11:18:00','2025-10-10 11:18:00','2025-10-10 11:18:00'),(7,1,3,12000000.00,'VND','monthly',1,'{\"Phụ cấp xăng xe\": 400000, \"Phụ cấp ăn trưa\": 600000, \"Phụ cấp điện thoại\": 200000}','{\"Doanh số bán hàng\": 10000000}','2025-10-10',NULL,'inactive',NULL,'2025-10-10 11:23:10','2025-10-10 11:23:10','2025-10-10 11:23:10'),(8,1,7,3000000.00,'VND','monthly',1,'{\"Phụ cấp xăng xe\": 300000, \"Phụ cấp ăn trưa\": 500000, \"Phụ cấp điện thoại\": 200000}','{\"Tỷ lệ hoa hồng\": 5, \"Doanh số bán hàng\": 10000000}','2025-10-10',NULL,'active',NULL,'2025-10-10 11:29:00','2025-10-10 11:29:00',NULL);
/*!40000 ALTER TABLE `salary_contracts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `services`
--

DROP TABLE IF EXISTS `services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `services` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `key_code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `pricing_type` enum('fixed','per_unit','tiered') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'fixed',
  `unit_label` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `deleted_by` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key_code` (`key_code`),
  KEY `services_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `services_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Danh mục dịch vụ';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `services`
--

LOCK TABLES `services` WRITE;
/*!40000 ALTER TABLE `services` DISABLE KEYS */;
INSERT INTO `services` VALUES (1,'electricity','Điện','per_unit','kWh',NULL,NULL,NULL,NULL,NULL),(2,'water','Nước','per_unit','m3',NULL,NULL,NULL,NULL,NULL),(3,'internet','Internet','fixed','month',NULL,NULL,NULL,NULL,NULL),(4,'parking','Giữ xe','fixed','slot',NULL,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `services` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('02B7ESimQ9WVAMIIkHd2WbN5yeVsIj78Di0SZQL8',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.6584','YTo0OntzOjY6Il90b2tlbiI7czo0MDoidUdZcmRNWXExNDYzV1V3YUhxb3hDNUFPV1lkMTExV292NjRkbWVNaSI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozODoiaHR0cDovLzEyNy4wLjAuMTo4MDAwL3N1cGVyYWRtaW4vdXNlcnMiO31zOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czoyNzoiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2xvZ2luIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1760114531),('1dd87WYihl1RKCL76gIBQMSnGmbrwhhNZeHHXTQA',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.6584','YTo0OntzOjY6Il90b2tlbiI7czo0MDoicW5VbGRMQ2x5ZFBqNWJlaDdFWTdweWRuVEhRenZZUGZZckdCVjgwRyI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czo0NToiaHR0cDovLzEyNy4wLjAuMTo4MDAwL3N1cGVyYWRtaW4vdXNlcnMvY3JlYXRlIjt9czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1760114541),('3OQ49HML2bu3DJNH5RmwSwJq99azwL6gkK7vFliM',3,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0','YTo4OntzOjY6Il90b2tlbiI7czo0MDoiV0I0c09Uc0Z4SVA0dXJodGNLaW1XbXFPRjh4eWRSZFp2d1lKbkdlWiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDA6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9zdXBlcmFkbWluL3VzZXJzLzQiO31zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aTozO3M6MTI6ImF1dGhfcm9sZV9pZCI7aToxO3M6MTM6ImF1dGhfcm9sZV9rZXkiO3M6NToiYWRtaW4iO3M6MjA6ImF1dGhfb3JnYW5pemF0aW9uX2lkIjtpOjE7czoyMjoiYXV0aF9vcmdhbml6YXRpb25fbmFtZSI7czoyNToiVOG7lSBjaOG7qWMgbeG6t2MgxJHhu4tuaCI7fQ==',1760114770),('6ayPdQSY9qCbCzTaIw7E9OaMKppsFXCxV6P2j3Zy',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.6584','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiZHhFMDVSRlU0bGtNWGFieGRrWDA4c2pkc2Q2MDU3Skl1aUkyY0xaNSI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czo0ODoiaHR0cDovLzEyNy4wLjAuMTo4MDAwL3N1cGVyYWRtaW4vb3JnYW5pemF0aW9ucy8xIjt9czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1760113576);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticket_logs`
--

DROP TABLE IF EXISTS `ticket_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ticket_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ticket_id` bigint unsigned NOT NULL,
  `actor_id` bigint unsigned DEFAULT NULL,
  `action` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `detail` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `cost_amount` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT 'Chi phí phát sinh (để có thể trừ vào cọc)',
  `cost_note` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Mô tả chi phí',
  `charge_to` enum('none','tenant_deposit','tenant_invoice','landlord') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'none' COMMENT 'Hướng hạch toán',
  `linked_invoice_id` bigint unsigned DEFAULT NULL COMMENT 'Hóa đơn liên quan (nếu charge_to=tenant_invoice)',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_tkl_actor` (`actor_id`),
  KEY `fk_tkl_invoice` (`linked_invoice_id`),
  KEY `idx_tkl_ticket_created` (`ticket_id`,`created_at`),
  KEY `idx_tkl_charge_to` (`charge_to`),
  CONSTRAINT `fk_tkl_actor` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_tkl_invoice` FOREIGN KEY (`linked_invoice_id`) REFERENCES `invoices` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_tkl_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Nhật ký ticket + chi phí';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticket_logs`
--

LOCK TABLES `ticket_logs` WRITE;
/*!40000 ALTER TABLE `ticket_logs` DISABLE KEYS */;
INSERT INTO `ticket_logs` VALUES (1,2,4,'created','Ticket được tạo mới',0.00,NULL,'none',NULL,'2025-10-09 20:45:06'),(2,3,4,'created','Ticket được tạo mới',0.00,NULL,'none',NULL,'2025-10-09 20:45:35'),(3,4,4,'created','Ticket được tạo mới',0.00,NULL,'none',NULL,'2025-10-09 20:45:35'),(4,4,4,'started','Bắt đầu xử lý ticket',0.00,NULL,'none',NULL,'2025-10-09 20:45:35'),(5,5,4,'created','Ticket được tạo mới',0.00,NULL,'none',NULL,'2025-10-09 20:45:35'),(6,5,4,'started','Bắt đầu xử lý ticket',0.00,NULL,'none',NULL,'2025-10-09 20:45:35'),(7,5,4,'resolved','Đã hoàn thành xử lý',50000.00,'Chi phí vật liệu','landlord',NULL,'2025-10-09 20:45:35'),(8,6,4,'created','Ticket được tạo mới',0.00,NULL,'none',NULL,'2025-10-09 20:45:35'),(9,7,4,'created','Ticket được tạo mới',0.00,NULL,'none',NULL,'2025-10-09 20:45:35'),(10,7,4,'started','Bắt đầu xử lý ticket',0.00,NULL,'none',NULL,'2025-10-09 20:45:35'),(11,7,4,'resolved','Đã hoàn thành xử lý',0.00,NULL,'none',NULL,'2025-10-09 20:45:35'),(12,7,4,'closed','Ticket đã được đóng',0.00,NULL,'none',NULL,'2025-10-09 20:45:35'),(13,8,4,'created','Ticket được tạo mới',0.00,NULL,'none',NULL,'2025-10-09 20:46:33'),(14,8,4,'updated','Cập nhật: Trạng thái: open → in_progress',0.00,NULL,'none',NULL,'2025-10-09 20:46:33'),(15,8,4,'test_action','Test log entry',100000.00,'Test cost','landlord',NULL,'2025-10-09 20:46:33'),(16,8,4,'updated','Cập nhật: Trạng thái: in_progress → resolved',0.00,NULL,'none',NULL,'2025-10-09 20:49:12'),(17,8,4,'test_format','Test log for format',0.00,NULL,'none',NULL,'2025-10-09 20:53:37'),(18,8,4,'test_format','Test log for format',0.00,NULL,'none',NULL,'2025-10-09 20:54:32'),(19,8,4,'final_test','Final test log',0.00,NULL,'none',NULL,'2025-10-09 20:57:37'),(20,3,4,'updated','Cập nhật: Trạng thái: open → in_progress',0.00,NULL,'none',NULL,'2025-10-09 21:04:35'),(21,9,4,'created','Ticket được tạo mới',0.00,NULL,'none',NULL,'2025-10-09 21:05:22'),(22,1,4,'updated','Cập nhật: Trạng thái: open → in_progress',0.00,NULL,'none',NULL,'2025-10-09 21:07:32'),(23,3,4,'Hoàn thành',NULL,6000.00,NULL,'tenant_deposit',NULL,'2025-10-09 21:11:04'),(24,6,4,'updated','Cập nhật: Trạng thái: open → in_progress',0.00,NULL,'none',NULL,'2025-10-09 21:21:05'),(25,3,4,'updated','Cập nhật: Trạng thái: in_progress → resolved',0.00,NULL,'none',NULL,'2025-10-09 21:22:44'),(26,8,4,'Hoàn thành',NULL,6000.00,NULL,'none',NULL,'2025-10-09 21:30:25');
/*!40000 ALTER TABLE `ticket_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tickets`
--

DROP TABLE IF EXISTS `tickets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tickets` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `organization_id` bigint unsigned DEFAULT NULL,
  `unit_id` bigint unsigned DEFAULT NULL,
  `lease_id` bigint unsigned DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `assigned_to` bigint unsigned DEFAULT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `priority` enum('low','medium','high','urgent') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'medium',
  `status` enum('open','in_progress','resolved','closed','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'open',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `deleted_by` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_tickets_status_priority` (`status`,`priority`),
  KEY `fk_tk_org` (`organization_id`),
  KEY `fk_tk_unit` (`unit_id`),
  KEY `fk_tk_lease` (`lease_id`),
  KEY `fk_tk_created` (`created_by`),
  KEY `fk_tk_assigned` (`assigned_to`),
  KEY `tickets_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `fk_tk_assigned` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_tk_created` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_tk_lease` FOREIGN KEY (`lease_id`) REFERENCES `leases` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_tk_org` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_tk_unit` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tickets_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Ticket bảo trì/sự cố';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tickets`
--

LOCK TABLES `tickets` WRITE;
/*!40000 ALTER TABLE `tickets` DISABLE KEYS */;
INSERT INTO `tickets` VALUES (1,1,16,4,4,3,'Sửa chữa điều hòa phòng 101','Điều hòa phòng 101 không hoạt động, cần kiểm tra và sửa chữa','high','in_progress','2025-10-09 20:44:37','2025-10-09 21:07:32',NULL,NULL),(2,1,16,4,4,3,'Sửa chữa điều hòa phòng 101','Điều hòa phòng 101 không hoạt động, cần kiểm tra và sửa chữa','high','in_progress','2025-10-09 20:45:06','2025-10-09 21:12:15',NULL,NULL),(3,1,16,4,4,3,'Sửa chữa điều hòa phòng 101','Điều hòa phòng 101 không hoạt động, cần kiểm tra và sửa chữa','high','resolved','2025-10-09 20:45:35','2025-10-09 21:22:44',NULL,NULL),(4,1,17,NULL,4,4,'Thay bóng đèn hành lang','Bóng đèn hành lang tầng 2 bị cháy, cần thay mới','medium','in_progress','2025-10-09 20:45:35','2025-10-09 20:45:35',NULL,NULL),(5,1,18,6,4,5,'Vệ sinh phòng sau khi khách trả phòng','Phòng 203 cần vệ sinh sau khi khách trả phòng','low','resolved','2025-10-09 20:45:35','2025-10-09 20:45:35',NULL,NULL),(6,1,16,7,4,NULL,'Sửa chữa vòi nước bị rò rỉ','Vòi nước phòng 105 bị rò rỉ, cần sửa chữa ngay','urgent','in_progress','2025-10-09 20:45:35','2025-10-09 21:21:05',NULL,NULL),(7,1,NULL,NULL,4,3,'Kiểm tra hệ thống điện','Kiểm tra định kỳ hệ thống điện toàn bộ tòa nhà','medium','closed','2025-10-09 20:45:35','2025-10-09 20:45:35',NULL,NULL),(8,1,NULL,NULL,4,NULL,'Updated Test Ticket','Updated description','high','resolved','2025-10-09 20:46:33','2025-10-09 20:49:12',NULL,NULL),(9,1,16,4,4,8,'Điều hoà','fdh','high','open','2025-10-09 21:05:22','2025-10-09 21:05:45','2025-10-09 21:05:45',4);
/*!40000 ALTER TABLE `tickets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `unit_amenities`
--

DROP TABLE IF EXISTS `unit_amenities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `unit_amenities` (
  `unit_id` bigint unsigned NOT NULL,
  `amenity_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`unit_id`,`amenity_id`),
  KEY `fk_ua_amen` (`amenity_id`),
  CONSTRAINT `fk_ua_amen` FOREIGN KEY (`amenity_id`) REFERENCES `amenities` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ua_unit` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tiện ích gắn cho phòng';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `unit_amenities`
--

LOCK TABLES `unit_amenities` WRITE;
/*!40000 ALTER TABLE `unit_amenities` DISABLE KEYS */;
/*!40000 ALTER TABLE `unit_amenities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `units`
--

DROP TABLE IF EXISTS `units`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `units` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `floor` int DEFAULT NULL,
  `area_m2` decimal(10,2) DEFAULT NULL,
  `unit_type` enum('room','apartment','dorm','shared') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'room',
  `base_rent` decimal(12,2) NOT NULL,
  `deposit_amount` decimal(12,2) DEFAULT '0.00',
  `max_occupancy` int DEFAULT '1',
  `status` enum('available','reserved','occupied','maintenance') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'available',
  `note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `deleted_by` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_property_code` (`property_id`,`code`),
  KEY `idx_units_status` (`status`),
  KEY `units_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `fk_units_property` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  CONSTRAINT `units_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Phòng/căn';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `units`
--

LOCK TABLES `units` WRITE;
/*!40000 ALTER TABLE `units` DISABLE KEYS */;
INSERT INTO `units` VALUES (16,5,'P101',1,30.00,'room',2500000.00,2500000.00,2,'available',NULL,'2025-10-09 18:43:58','2025-10-09 18:43:58',NULL,NULL),(17,5,'P202',2,35.00,'room',3000000.00,3000000.00,2,'available',NULL,'2025-10-09 18:43:58','2025-10-09 18:43:58',NULL,NULL),(18,5,'P303',3,40.00,'room',3500000.00,3500000.00,2,'available',NULL,'2025-10-09 18:43:58','2025-10-09 18:43:58',NULL,NULL),(19,6,'P101',1,30.00,'room',2500000.00,2500000.00,2,'available',NULL,'2025-10-09 18:43:58','2025-10-09 18:43:58',NULL,NULL),(20,6,'P202',2,35.00,'room',3000000.00,3000000.00,2,'available',NULL,'2025-10-09 18:43:58','2025-10-09 18:43:58',NULL,NULL),(21,6,'P303',3,40.00,'room',3500000.00,3500000.00,2,'available',NULL,'2025-10-09 18:43:58','2025-10-09 18:43:58',NULL,NULL),(22,7,'P101',1,30.00,'room',2500000.00,2500000.00,2,'available',NULL,'2025-10-09 18:43:58','2025-10-09 18:43:58',NULL,NULL),(23,7,'P202',2,35.00,'room',3000000.00,3000000.00,2,'available',NULL,'2025-10-09 18:43:58','2025-10-09 18:43:58',NULL,NULL),(24,7,'P303',3,40.00,'room',3500000.00,3500000.00,2,'available',NULL,'2025-10-09 18:43:58','2025-10-09 18:43:58',NULL,NULL),(25,8,'P101',1,30.00,'room',2500000.00,2500000.00,2,'available',NULL,'2025-10-09 18:43:58','2025-10-09 18:43:58',NULL,NULL),(26,8,'P202',2,35.00,'room',3000000.00,3000000.00,2,'available',NULL,'2025-10-09 18:43:58','2025-10-09 18:43:58',NULL,NULL),(27,8,'P303',3,40.00,'room',3500000.00,3500000.00,2,'available',NULL,'2025-10-09 18:43:58','2025-10-09 18:43:58',NULL,NULL),(28,9,'P101',1,30.00,'room',2500000.00,2500000.00,2,'available',NULL,'2025-10-09 18:43:58','2025-10-09 18:43:58',NULL,NULL),(29,9,'P202',2,35.00,'room',3000000.00,3000000.00,2,'available',NULL,'2025-10-09 18:43:58','2025-10-09 18:43:58',NULL,NULL),(30,9,'P303',3,40.00,'room',3500000.00,3500000.00,2,'available',NULL,'2025-10-09 18:43:58','2025-10-09 18:43:58',NULL,NULL);
/*!40000 ALTER TABLE `units` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_profiles`
--

DROP TABLE IF EXISTS `user_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_profiles` (
  `user_id` bigint unsigned NOT NULL,
  `dob` date DEFAULT NULL,
  `gender` enum('male','female','other') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'other',
  `id_number` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_issued_at` date DEFAULT NULL,
  `id_images` json DEFAULT NULL,
  `address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`user_id`),
  CONSTRAINT `fk_profiles_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Hồ sơ cơ bản người dùng';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_profiles`
--

LOCK TABLES `user_profiles` WRITE;
/*!40000 ALTER TABLE `user_profiles` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_profiles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_roles`
--

DROP TABLE IF EXISTS `user_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_roles` (
  `user_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`user_id`,`role_id`),
  KEY `fk_ur_role` (`role_id`),
  CONSTRAINT `fk_ur_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ur_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Vai trò toàn cục của user';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_roles`
--

LOCK TABLES `user_roles` WRITE;
/*!40000 ALTER TABLE `user_roles` DISABLE KEYS */;
INSERT INTO `user_roles` VALUES (3,1),(1,2),(4,2),(5,3),(6,4),(7,5),(8,5);
/*!40000 ALTER TABLE `user_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `full_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatar_url` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint NOT NULL DEFAULT '1',
  `last_login_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `deleted_by` bigint unsigned DEFAULT NULL,
  `phone_verified_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `phone` (`phone`),
  KEY `users_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `users_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Người dùng';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'lexuanthanhquan37@gmail.com',NULL,'$2y$12$62IWJxDMrys848T8uj63nODgDgWCqROiZVyVBcpZb3KPIQw6Zn2nK','Le quan',NULL,1,NULL,'2025-10-08 11:17:02','2025-10-09 11:50:26','2025-10-09 11:50:26',4,NULL),(3,'admin@example.com','0901000001','$2y$12$62IWJxDMrys848T8uj63nODgDgWCqROiZVyVBcpZb3KPIQw6Zn2nK','Admin System',NULL,1,NULL,'2025-10-08 20:19:06','2025-10-08 20:46:48',NULL,NULL,NULL),(4,'manager@example.com','0902000002','$2y$12$62IWJxDMrys848T8uj63nODgDgWCqROiZVyVBcpZb3KPIQw6Zn2nK','Manager Nguyen',NULL,1,NULL,'2025-10-08 20:19:06','2025-10-08 20:46:48',NULL,NULL,NULL),(5,'agent@example.com','0903000003','$2y$12$62IWJxDMrys848T8uj63nODgDgWCqROiZVyVBcpZb3KPIQw6Zn2nK','Agent Tran',NULL,1,NULL,'2025-10-08 20:19:06','2025-10-08 20:46:48',NULL,NULL,NULL),(6,'landlord@example.com','0904000004','$2y$12$62IWJxDMrys848T8uj63nODgDgWCqROiZVyVBcpZb3KPIQw6Zn2nK','Landlord Le',NULL,1,NULL,'2025-10-08 20:19:06','2025-10-08 20:46:48',NULL,NULL,NULL),(7,'tenant@example.com','0905000005','$2y$12$62IWJxDMrys848T8uj63nODgDgWCqROiZVyVBcpZb3KPIQw6Zn2nK','Tenant Hoang',NULL,1,NULL,'2025-10-08 20:19:06','2025-10-08 20:46:48',NULL,NULL,NULL),(8,'lexuanthanhquan@gmail.com',NULL,'$2y$12$eLRUMv/jrLuGHDbM8bdXNeoHyjQ3nFUFctxfSulr.dWnEJOROk7RW','Lê Quân',NULL,1,NULL,'2025-10-09 11:51:04','2025-10-09 11:51:04',NULL,NULL,NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `viewings`
--

DROP TABLE IF EXISTS `viewings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `viewings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID lịch xem phòng',
  `lead_id` bigint unsigned DEFAULT NULL COMMENT 'Lead nếu khách chưa có account',
  `listing_id` bigint unsigned DEFAULT NULL COMMENT 'Tin đăng xem',
  `agent_id` bigint unsigned DEFAULT NULL COMMENT 'CTV/Nhân viên phụ trách',
  `schedule_at` datetime NOT NULL COMMENT 'Thời điểm hẹn',
  `status` enum('requested','confirmed','done','no_show','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'requested' COMMENT 'Trạng thái',
  `result_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Kết quả buổi xem',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `deleted_by` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_view_lead` (`lead_id`),
  KEY `fk_view_listing` (`listing_id`),
  KEY `idx_viewings_status_time` (`status`,`schedule_at`),
  KEY `idx_viewings_agent_time` (`agent_id`,`schedule_at`),
  KEY `viewings_deleted_by_foreign` (`deleted_by`),
  CONSTRAINT `fk_view_agent` FOREIGN KEY (`agent_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_view_lead` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_view_listing` FOREIGN KEY (`listing_id`) REFERENCES `listings` (`id`) ON DELETE SET NULL,
  CONSTRAINT `viewings_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Lịch xem phòng';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `viewings`
--

LOCK TABLES `viewings` WRITE;
/*!40000 ALTER TABLE `viewings` DISABLE KEYS */;
/*!40000 ALTER TABLE `viewings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `webhooks`
--

DROP TABLE IF EXISTS `webhooks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `webhooks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `organization_id` bigint unsigned DEFAULT NULL,
  `event_key` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `target_url` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `secret` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `active` tinyint DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_wh_org` (`organization_id`),
  CONSTRAINT `fk_wh_org` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Webhook cấu hình';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `webhooks`
--

LOCK TABLES `webhooks` WRITE;
/*!40000 ALTER TABLE `webhooks` DISABLE KEYS */;
/*!40000 ALTER TABLE `webhooks` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-10-11  1:10:17
