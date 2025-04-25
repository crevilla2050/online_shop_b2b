-- MySQL dump 10.13  Distrib 8.0.42, for Linux (x86_64)
--
-- Host: localhost    Database: db_codigos_postales
-- ------------------------------------------------------
-- Server version	8.0.42

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
-- Table structure for table `catalog_postal_codes`
--

DROP TABLE IF EXISTS `catalog_postal_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `catalog_postal_codes` (
  `id` binary(16) NOT NULL,
  `state_id` binary(16) DEFAULT NULL,
  `municipality_id` binary(16) DEFAULT NULL,
  `settlement_id` binary(16) DEFAULT NULL,
  `city_id` binary(16) DEFAULT NULL,
  `postal_code_id` binary(16) DEFAULT NULL,
  `zone_id` binary(16) DEFAULT NULL,
  `settlement_type_id` binary(16) DEFAULT NULL,
  `datetime_id` binary(16) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_catalog_postal_codes_1_idx` (`city_id`),
  KEY `fk_catalog_postal_codes_2_idx` (`municipality_id`),
  KEY `fk_catalog_postal_codes_3_idx` (`postal_code_id`),
  KEY `fk_catalog_postal_codes_4_idx` (`state_id`),
  KEY `fk_catalog_postal_codes_6_idx` (`settlement_id`),
  KEY `fk_catalog_postal_codes_6_idx1` (`zone_id`),
  KEY `fk_catalog_postal_codes_7_idx` (`settlement_type_id`),
  KEY `fk_catalog_postal_codes_8_idx` (`datetime_id`),
  CONSTRAINT `fk_catalog_postal_codes_1` FOREIGN KEY (`city_id`) REFERENCES `city` (`id`),
  CONSTRAINT `fk_catalog_postal_codes_2` FOREIGN KEY (`municipality_id`) REFERENCES `municipality` (`id`),
  CONSTRAINT `fk_catalog_postal_codes_3` FOREIGN KEY (`postal_code_id`) REFERENCES `postal_code` (`id`),
  CONSTRAINT `fk_catalog_postal_codes_4` FOREIGN KEY (`state_id`) REFERENCES `state` (`id`),
  CONSTRAINT `fk_catalog_postal_codes_5` FOREIGN KEY (`settlement_id`) REFERENCES `settlement` (`id`),
  CONSTRAINT `fk_catalog_postal_codes_6` FOREIGN KEY (`zone_id`) REFERENCES `zone` (`id`),
  CONSTRAINT `fk_catalog_postal_codes_7` FOREIGN KEY (`settlement_type_id`) REFERENCES `settlement_type` (`id`),
  CONSTRAINT `fk_catalog_postal_codes_8` FOREIGN KEY (`datetime_id`) REFERENCES `datetime_at` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `city`
--

DROP TABLE IF EXISTS `city`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `city` (
  `id` binary(16) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `datetime_at`
--

DROP TABLE IF EXISTS `datetime_at`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `datetime_at` (
  `id` binary(16) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `municipality`
--

DROP TABLE IF EXISTS `municipality`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `municipality` (
  `id` binary(16) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `postal_code`
--

DROP TABLE IF EXISTS `postal_code`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `postal_code` (
  `id` binary(16) NOT NULL,
  `code` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary view structure for view `postal_codes_view`
--

DROP TABLE IF EXISTS `postal_codes_view`;
/*!50001 DROP VIEW IF EXISTS `postal_codes_view`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `postal_codes_view` AS SELECT 
 1 AS `estado`,
 1 AS `municipio`,
 1 AS `id_ciudad`,
 1 AS `ciudad`,
 1 AS `colonia`,
 1 AS `id_colonia`,
 1 AS `codigo_postal`*/;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `settlement`
--

DROP TABLE IF EXISTS `settlement`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `settlement` (
  `id` binary(16) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `settlement_type`
--

DROP TABLE IF EXISTS `settlement_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `settlement_type` (
  `id` binary(16) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `state`
--

DROP TABLE IF EXISTS `state`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `state` (
  `id` binary(16) NOT NULL,
  `name` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `states`
--

DROP TABLE IF EXISTS `states`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `states` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `zone`
--

DROP TABLE IF EXISTS `zone`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `zone` (
  `id` binary(16) NOT NULL,
  `name` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Final view structure for view `postal_codes_view`
--

/*!50001 DROP VIEW IF EXISTS `postal_codes_view`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `postal_codes_view` AS select `s`.`name` AS `estado`,`m`.`name` AS `municipio`,`c`.`id` AS `id_ciudad`,`c`.`name` AS `ciudad`,`se`.`name` AS `colonia`,`se`.`id` AS `id_colonia`,`pc`.`code` AS `codigo_postal` from (((((`catalog_postal_codes` `cp` join `state` `s` on((`s`.`id` = `cp`.`state_id`))) join `municipality` `m` on((`m`.`id` = `cp`.`municipality_id`))) join `city` `c` on((`c`.`id` = `cp`.`city_id`))) join `settlement` `se` on((`se`.`id` = `cp`.`settlement_id`))) join `postal_code` `pc` on((`pc`.`id` = `cp`.`postal_code_id`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-04-24 22:33:57
