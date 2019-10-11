-- MySQL dump 10.13  Distrib 5.7.24, for Linux (x86_64)
--
-- Host: 127.0.0.1    Database: laravel-shop
-- ------------------------------------------------------
-- Server version	5.7.24-0ubuntu0.18.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `admin_menu`
--

LOCK TABLES `admin_menu` WRITE;
/*!40000 ALTER TABLE `admin_menu` DISABLE KEYS */;
INSERT INTO `admin_menu` VALUES (1,0,1,'首页','fa-bar-chart','/',NULL,NULL,'2018-12-13 23:56:30'),(2,0,9,'系统管理','fa-tasks',NULL,NULL,NULL,'2019-10-10 13:20:17'),(3,2,10,'管理员','fa-users','auth/users',NULL,NULL,'2019-10-10 13:20:17'),(4,2,11,'角色','fa-user','auth/roles',NULL,NULL,'2019-10-10 13:20:17'),(5,2,12,'权限','fa-ban','auth/permissions',NULL,NULL,'2019-10-10 13:20:17'),(6,2,13,'菜单','fa-bars','auth/menu',NULL,NULL,'2019-10-10 13:20:17'),(7,2,14,'操作日志','fa-history','auth/logs',NULL,NULL,'2019-10-10 13:20:17'),(8,0,2,'用户管理','fa-users','/users',NULL,'2018-12-14 00:23:57','2018-12-14 00:25:13'),(10,0,3,'商品管理','fa-cubes','/products',NULL,'2018-12-15 01:13:34','2018-12-15 01:13:46'),(11,0,6,'订单管理','fa-clone','/orders',NULL,'2019-09-24 19:10:02','2019-10-10 13:20:17'),(12,0,7,'优惠券管理','fa-creative-commons','/coupon_codes',NULL,'2019-09-26 23:51:29','2019-10-10 13:20:17'),(13,0,8,'类目管理','fa-columns','/categories',NULL,'2019-10-08 16:45:30','2019-10-10 13:20:17'),(14,10,4,'众筹商品管理','fa-cube','/crowdfunding_products',NULL,'2019-10-09 00:33:20','2019-10-10 13:20:17'),(15,10,5,'普通商品','fa-cube','/products',NULL,'2019-10-10 13:20:12','2019-10-10 13:20:17'),(16,10,0,'秒杀商品','fa-bars','/seckill_products',NULL,'2019-10-11 18:00:34','2019-10-11 18:00:34');
/*!40000 ALTER TABLE `admin_menu` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `admin_permissions`
--

LOCK TABLES `admin_permissions` WRITE;
/*!40000 ALTER TABLE `admin_permissions` DISABLE KEYS */;
INSERT INTO `admin_permissions` VALUES (1,'All permission','*','','*',NULL,NULL),(2,'Dashboard','dashboard','GET','/',NULL,NULL),(3,'Login','auth.login','','/auth/login\r\n/auth/logout',NULL,NULL),(4,'User setting','auth.setting','GET,PUT','/auth/setting',NULL,NULL),(5,'Auth management','auth.management','','/auth/roles\r\n/auth/permissions\r\n/auth/menu\r\n/auth/logs',NULL,NULL),(6,'用户管理','users','','/users*','2018-12-14 00:46:12','2018-12-14 00:46:12'),(7,'商品管理','products','','/products*','2019-09-28 23:27:18','2019-09-28 23:27:18'),(8,'订单管理','orders','','/orders*','2019-09-28 23:28:09','2019-09-28 23:28:09'),(9,'优惠券管理','couponCodes','','/coupon_codes*','2019-09-28 23:29:56','2019-09-28 23:29:56');
/*!40000 ALTER TABLE `admin_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `admin_role_menu`
--

LOCK TABLES `admin_role_menu` WRITE;
/*!40000 ALTER TABLE `admin_role_menu` DISABLE KEYS */;
INSERT INTO `admin_role_menu` VALUES (1,2,NULL,NULL);
/*!40000 ALTER TABLE `admin_role_menu` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `admin_role_permissions`
--

LOCK TABLES `admin_role_permissions` WRITE;
/*!40000 ALTER TABLE `admin_role_permissions` DISABLE KEYS */;
INSERT INTO `admin_role_permissions` VALUES (1,1,NULL,NULL),(2,2,NULL,NULL),(2,3,NULL,NULL),(2,4,NULL,NULL),(2,6,NULL,NULL),(2,7,NULL,NULL),(2,8,NULL,NULL),(2,9,NULL,NULL);
/*!40000 ALTER TABLE `admin_role_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `admin_role_users`
--

LOCK TABLES `admin_role_users` WRITE;
/*!40000 ALTER TABLE `admin_role_users` DISABLE KEYS */;
INSERT INTO `admin_role_users` VALUES (1,1,NULL,NULL),(2,2,NULL,NULL);
/*!40000 ALTER TABLE `admin_role_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `admin_roles`
--

LOCK TABLES `admin_roles` WRITE;
/*!40000 ALTER TABLE `admin_roles` DISABLE KEYS */;
INSERT INTO `admin_roles` VALUES (1,'Administrator','administrator','2018-12-13 18:12:09','2018-12-13 18:12:09'),(2,'运营','operator','2018-12-14 00:49:55','2018-12-14 00:49:55');
/*!40000 ALTER TABLE `admin_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `admin_user_permissions`
--

LOCK TABLES `admin_user_permissions` WRITE;
/*!40000 ALTER TABLE `admin_user_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `admin_user_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `admin_users`
--

LOCK TABLES `admin_users` WRITE;
/*!40000 ALTER TABLE `admin_users` DISABLE KEYS */;
INSERT INTO `admin_users` VALUES (1,'admin','$2y$10$4.hxukoMtWwYAtIDM53ciOFYVuWejYwCBnFWudIEbXEGrKIRP.rpW','Administrator',NULL,'HQM9LIty5yHUD4fpERIrsFQc41Ao1ed3vKsxQW8Ox9OdG1Hbq1ZMHjXq0KK2','2018-12-13 18:12:09','2018-12-13 18:12:09'),(2,'operator','$2y$10$Cijv5ZzNqAuJrUNP2nIcS.xVe5S1FlvHKOokNCUGSi2Mnf4hStouq','运营',NULL,'hNwajHAADm9A5EdVLNgipOzL63rrplTh1cM92qsUFbgeeSSh5RRYlZYX83am','2018-12-14 00:52:26','2018-12-14 00:52:26');
/*!40000 ALTER TABLE `admin_users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2019-10-11 16:31:22
