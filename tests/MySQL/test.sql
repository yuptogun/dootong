-- MySQL dump 10.13  Distrib 5.5.62, for Win64 (AMD64)
--
-- Host: localhost    Database: test
-- ------------------------------------------------------
-- Server version	5.5.5-10.5.3-MariaDB

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
-- Table structure for table `happening_attributes`
--

DROP TABLE IF EXISTS `happening_attributes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `happening_attributes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `happening_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `attribute_key` varchar(255) NOT NULL,
  `attribute_value` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `happening_attributes_happening_id_IDX` (`happening_id`,`attribute_key`) USING BTREE,
  KEY `happening_attributes_FK_user` (`user_id`),
  CONSTRAINT `happening_attributes_FK_happening` FOREIGN KEY (`happening_id`) REFERENCES `happenings` (`id`),
  CONSTRAINT `happening_attributes_FK_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `happening_attributes`
--

LOCK TABLES `happening_attributes` WRITE;
/*!40000 ALTER TABLE `happening_attributes` DISABLE KEYS */;
INSERT INTO `happening_attributes` VALUES (1,1,1,'name','created first happening data','2021-09-20 10:18:55',NULL),(2,2,2,'name','lost something','2021-09-20 10:20:14',NULL),(3,3,2,'name','win a lotto','2021-09-20 10:27:34',NULL);
/*!40000 ALTER TABLE `happening_attributes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `happenings`
--

DROP TABLE IF EXISTS `happenings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `happenings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `happenings_FK` (`user_id`),
  CONSTRAINT `happenings_FK` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `happenings`
--

LOCK TABLES `happenings` WRITE;
/*!40000 ALTER TABLE `happenings` DISABLE KEYS */;
INSERT INTO `happenings` VALUES (1,1,'2021-09-20 10:18:26',NULL,NULL),(2,2,'2021-09-20 10:18:26',NULL,NULL),(3,2,'2021-09-20 10:27:23',NULL,NULL);
/*!40000 ALTER TABLE `happenings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_happenings`
--

DROP TABLE IF EXISTS `user_happenings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_happenings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `happening_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_happenings_FK_user` (`user_id`),
  KEY `user_happenings_FK_happening` (`happening_id`),
  CONSTRAINT `user_happenings_FK_happening` FOREIGN KEY (`happening_id`) REFERENCES `happenings` (`id`),
  CONSTRAINT `user_happenings_FK_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_happenings`
--

LOCK TABLES `user_happenings` WRITE;
/*!40000 ALTER TABLE `user_happenings` DISABLE KEYS */;
INSERT INTO `user_happenings` VALUES (1,1,1,'2021-09-20 10:19:19',NULL,NULL),(2,1,2,'2021-09-20 10:20:31',NULL,NULL),(3,2,2,'2021-09-20 10:20:36',NULL,NULL),(4,1,2,'2021-09-20 10:25:22',NULL,NULL);
/*!40000 ALTER TABLE `user_happenings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'user1','user1@happened.link','$2y$10$O5T1a14I0nrgj2SOIBvwbeOtJUuomFF/dx1vWXKe/AQYDcm/aT/yq','2021-08-17 05:23:10',NULL,NULL),(2,'user2','user2@happened.link','$2y$10$o1FPn04kftkVWMT1wrQj.OSvtccVeK5CpLfpJgqCgtT00sWJ86/y6','2021-08-18 09:11:20','2021-08-18 09:14:34','2021-08-18 09:14:40');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'test'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2021-09-20 19:39:03
