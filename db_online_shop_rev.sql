-- MySQL dump 10.13  Distrib 8.0.42, for Linux (x86_64)
--
-- Host: localhost    Database: db_online_shop
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
-- Table structure for table `id_direcciones_clientes`
--

DROP TABLE IF EXISTS `id_direcciones_clientes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `id_direcciones_clientes` (
  `id_direcciones_clientes` int NOT NULL AUTO_INCREMENT,
  `id_cliente` int NOT NULL,
  `id_direccion` int NOT NULL,
  PRIMARY KEY (`id_direcciones_clientes`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `id_direcciones_clientes`
--

LOCK TABLES `id_direcciones_clientes` WRITE;
/*!40000 ALTER TABLE `id_direcciones_clientes` DISABLE KEYS */;
/*!40000 ALTER TABLE `id_direcciones_clientes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_almacenes`
--

DROP TABLE IF EXISTS `tbl_almacenes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_almacenes` (
  `id_almacen` int NOT NULL AUTO_INCREMENT,
  `chr_nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_direccion` int DEFAULT NULL,
  `id_telefono` int DEFAULT NULL,
  `bit_activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_almacen`),
  KEY `index2` (`id_direccion`),
  KEY `index3` (`id_telefono`),
  CONSTRAINT `fk_tbl_almacenes_1` FOREIGN KEY (`id_direccion`) REFERENCES `tbl_direcciones` (`id_direccion`),
  CONSTRAINT `fk_tbl_almacenes_2` FOREIGN KEY (`id_telefono`) REFERENCES `tbl_telefonos` (`id_telefono`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_almacenes`
--

LOCK TABLES `tbl_almacenes` WRITE;
/*!40000 ALTER TABLE `tbl_almacenes` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_almacenes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_categorias`
--

DROP TABLE IF EXISTS `tbl_categorias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_categorias` (
  `id_categoria` int NOT NULL AUTO_INCREMENT,
  `chr_nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `chr_descripcion` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bit_activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_categoria`)
) ENGINE=InnoDB AUTO_INCREMENT=149 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_categorias`
--

LOCK TABLES `tbl_categorias` WRITE;
/*!40000 ALTER TABLE `tbl_categorias` DISABLE KEYS */;
INSERT INTO `tbl_categorias` VALUES (2,'Hardware',NULL,1),(3,'Software',NULL,1),(4,'Peripherals',NULL,1),(5,'Networking',NULL,1),(6,'Storage',NULL,1),(7,'Accessories',NULL,1),(8,'Components',NULL,1),(9,'Audio',NULL,1),(10,'Video',NULL,1),(11,'Power',NULL,1),(12,'Cooling',NULL,1),(13,'Cases',NULL,1),(14,'Monitors',NULL,1),(15,'Printers',NULL,1),(16,'Scanners',NULL,1),(17,'Tablets',NULL,1),(18,'Smartphones',NULL,1),(19,'Cameras',NULL,1),(20,'Wearables',NULL,1),(21,'Smart Home',NULL,1),(22,'Gaming',NULL,1),(23,'Office Supplies',NULL,1),(24,'Software Licenses',NULL,1),(25,'Services',NULL,1),(26,'Others',NULL,1),(88,'Computadoras',NULL,1),(89,'Ensamble',NULL,1),(90,'Computadoras Gaming',NULL,1),(91,'Almacenamiento Portatil',NULL,1),(92,'Almacenamiento',NULL,1),(93,'Electrónica',NULL,1),(94,'Accesorios para Electronica',NULL,1),(95,'Cables',NULL,1),(96,'Accesorios Gaming',NULL,1),(97,'Accesorios para Cómputo',NULL,1),(98,'Accesorios para Componentes',NULL,1),(99,'Adaptadores',NULL,1),(100,'Accesorios y Consumibles POS',NULL,1),(101,'Accesorios para Energía',NULL,1),(102,'Auriculares y Diademas',NULL,1),(103,'Domotica',NULL,1),(104,'Apple',NULL,1),(105,'Accesorios para Servidores',NULL,1),(106,'Tarjetas',NULL,1),(107,'Seguridad Inteligente',NULL,1),(108,'Perifericos para POS',NULL,1),(109,'Video Vigilancia',NULL,1),(110,'Baterías Banks',NULL,1),(111,'Respaldo y Regulación',NULL,1),(112,'Energía',NULL,1),(113,'Red Activa',NULL,1),(114,'Redes',NULL,1),(115,'Sistemas Contables',NULL,1),(116,'Sistema para puntos de venta',NULL,1),(117,'Consumibles',NULL,1),(118,'Credencialización',NULL,1),(119,'Red Pasiva ',NULL,1),(120,'Señalización Digital',NULL,1),(121,'Salud',NULL,1),(122,'Seguridad',NULL,1),(123,'Solucion para servidores',NULL,1),(124,'Accesorios para Impresión',NULL,1),(125,'Impresión',NULL,1),(126,'Digitalización de Imágenes',NULL,1),(127,'Teléfonos',NULL,1),(128,'Software POS',NULL,1),(129,'Modulos Supresores',NULL,1),(130,'Sistemas de Control',NULL,1),(131,'Energia Solar y Eolica',NULL,1),(132,'Software Administrativo',NULL,1),(133,'Servidores___',NULL,1),(134,'Workstations',NULL,1),(135,'ESD',NULL,1),(136,'Soluciones de Seguridad Inteligente',NULL,1),(137,'Conmutadores PBX',NULL,1),(138,'Comunicaciones',NULL,1),(139,'Conferencias',NULL,1),(140,'Soluciones de Telefonía para empresas',NULL,1),(141,'Línea Blanca',NULL,1),(142,'Tarjetas para Telefonía',NULL,1),(143,'Centro de Datos',NULL,1),(144,'Seguridad Electrónica',NULL,1),(145,'Telefonía y Video Vigilancia',NULL,1),(146,'Accesorios',NULL,1),(147,'Sistemas Operativos',NULL,1),(148,'Productividad',NULL,1);
/*!40000 ALTER TABLE `tbl_categorias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_clientes`
--

DROP TABLE IF EXISTS `tbl_clientes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_clientes` (
  `id_cliente` int NOT NULL AUTO_INCREMENT,
  `chr_nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `chr_apellido` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `chr_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `chr_telefono` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dt_fecha_registro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `bit_es_empresa` tinyint(1) NOT NULL DEFAULT '0',
  `chr_nombre_empresa` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `chr_RFC` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bit_activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_cliente`),
  UNIQUE KEY `chr_email_UNIQUE` (`chr_email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_clientes`
--

LOCK TABLES `tbl_clientes` WRITE;
/*!40000 ALTER TABLE `tbl_clientes` DISABLE KEYS */;
INSERT INTO `tbl_clientes` VALUES (1,'TestUser','TestLast','test@example.com','123456789','2025-04-14 04:44:14',1,'Mi empresita','XAXX010101ABC',1);
/*!40000 ALTER TABLE `tbl_clientes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_clientes_documentos`
--

DROP TABLE IF EXISTS `tbl_clientes_documentos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_clientes_documentos` (
  `id_cliente_documento` int NOT NULL AUTO_INCREMENT,
  `id_cliente` int NOT NULL,
  `id_documento` int NOT NULL,
  `dt_fecha_relacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_cliente_documento`),
  KEY `fk_tbl_clientes_documentos_clientes_idx` (`id_cliente`),
  KEY `fk_tbl_clientes_documentos_documentos_idx` (`id_documento`),
  CONSTRAINT `fk_tbl_clientes_documentos_clientes` FOREIGN KEY (`id_cliente`) REFERENCES `tbl_clientes` (`id_cliente`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_tbl_clientes_documentos_documentos` FOREIGN KEY (`id_documento`) REFERENCES `tbl_documentos` (`id_documento`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_clientes_documentos`
--

LOCK TABLES `tbl_clientes_documentos` WRITE;
/*!40000 ALTER TABLE `tbl_clientes_documentos` DISABLE KEYS */;
INSERT INTO `tbl_clientes_documentos` VALUES (1,1,1,'2025-04-16 09:10:07');
/*!40000 ALTER TABLE `tbl_clientes_documentos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_clientes_telefonos`
--

DROP TABLE IF EXISTS `tbl_clientes_telefonos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_clientes_telefonos` (
  `id_cliente_telefono` int NOT NULL AUTO_INCREMENT,
  `id_cliente` int NOT NULL,
  `id_numero_telefono` int NOT NULL,
  PRIMARY KEY (`id_cliente_telefono`),
  KEY `fk_tbl_clientes_telefonos_clientes_idx` (`id_cliente`),
  KEY `fk_tbl_clientes_telefonos_numero_telefono_idx` (`id_numero_telefono`),
  CONSTRAINT `fk_tbl_clientes_telefonos_clientes` FOREIGN KEY (`id_cliente`) REFERENCES `tbl_clientes` (`id_cliente`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_tbl_clientes_telefonos_numero_telefono` FOREIGN KEY (`id_numero_telefono`) REFERENCES `tbl_numero_telefono` (`id_numero_telefono`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_clientes_telefonos`
--

LOCK TABLES `tbl_clientes_telefonos` WRITE;
/*!40000 ALTER TABLE `tbl_clientes_telefonos` DISABLE KEYS */;
INSERT INTO `tbl_clientes_telefonos` VALUES (1,1,1);
/*!40000 ALTER TABLE `tbl_clientes_telefonos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_consumos_empleado`
--

DROP TABLE IF EXISTS `tbl_consumos_empleado`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_consumos_empleado` (
  `id_consumo_empleado` int NOT NULL AUTO_INCREMENT,
  `id_empleado_cliente` int NOT NULL,
  `id_orden` int NOT NULL,
  `dt_fecha_consumo` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fl_monto_consumido` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id_consumo_empleado`),
  KEY `fk_tbl_consumos_empleado_empleados_cliente_idx` (`id_empleado_cliente`),
  KEY `fk_tbl_consumos_empleado_ordenes_idx` (`id_orden`),
  CONSTRAINT `fk_tbl_consumos_empleado_empleados_cliente` FOREIGN KEY (`id_empleado_cliente`) REFERENCES `tbl_empleados_cliente` (`id_empleado_cliente`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_tbl_consumos_empleado_ordenes` FOREIGN KEY (`id_orden`) REFERENCES `tbl_ordenes` (`id_orden`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_consumos_empleado`
--

LOCK TABLES `tbl_consumos_empleado` WRITE;
/*!40000 ALTER TABLE `tbl_consumos_empleado` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_consumos_empleado` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_creditos_empleado`
--

DROP TABLE IF EXISTS `tbl_creditos_empleado`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_creditos_empleado` (
  `id_credito_empleado` int NOT NULL AUTO_INCREMENT,
  `id_empleado_cliente` int NOT NULL,
  `id_credito_empresa` int NOT NULL,
  `fl_monto_credito` decimal(10,2) NOT NULL DEFAULT '0.00',
  `bit_activo` tinyint(1) NOT NULL DEFAULT '1',
  `dt_fecha_creacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_credito_empleado`),
  UNIQUE KEY `unq_creditos_empleado_empleado` (`id_empleado_cliente`),
  KEY `fk_creditos_empleado_empleados_idx` (`id_empleado_cliente`),
  KEY `fk_creditos_empleado_creditos_empresa_idx` (`id_credito_empresa`),
  CONSTRAINT `fk_creditos_empleado_creditos_empresa` FOREIGN KEY (`id_credito_empresa`) REFERENCES `tbl_creditos_empresa` (`id_credito_empresa`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_creditos_empleado_empleados` FOREIGN KEY (`id_empleado_cliente`) REFERENCES `tbl_empleados_cliente` (`id_empleado_cliente`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_creditos_empleado`
--

LOCK TABLES `tbl_creditos_empleado` WRITE;
/*!40000 ALTER TABLE `tbl_creditos_empleado` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_creditos_empleado` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_creditos_empresa`
--

DROP TABLE IF EXISTS `tbl_creditos_empresa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_creditos_empresa` (
  `id_credito_empresa` int NOT NULL AUTO_INCREMENT,
  `id_cliente` int NOT NULL,
  `fl_monto_credito` decimal(10,2) NOT NULL DEFAULT '0.00',
  `bit_activo` tinyint(1) NOT NULL DEFAULT '1',
  `dt_fecha_creacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_credito_empresa`),
  KEY `fk_creditos_empresa_clientes_idx` (`id_cliente`),
  CONSTRAINT `fk_creditos_empresa_clientes` FOREIGN KEY (`id_cliente`) REFERENCES `tbl_clientes` (`id_cliente`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_creditos_empresa`
--

LOCK TABLES `tbl_creditos_empresa` WRITE;
/*!40000 ALTER TABLE `tbl_creditos_empresa` DISABLE KEYS */;
INSERT INTO `tbl_creditos_empresa` VALUES (1,1,250000.00,1,'2025-04-25 06:00:00'),(2,1,100000.00,1,'2025-04-25 07:12:46');
/*!40000 ALTER TABLE `tbl_creditos_empresa` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_cuentas_por_cobrar`
--

DROP TABLE IF EXISTS `tbl_cuentas_por_cobrar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_cuentas_por_cobrar` (
  `id_cuenta_por_cobrar` int NOT NULL AUTO_INCREMENT,
  `id_cliente` int NOT NULL,
  `id_orden` int NOT NULL,
  `dt_fecha_emision` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fl_monto_total` decimal(10,2) NOT NULL,
  `fl_monto_pendiente` decimal(10,2) NOT NULL,
  `dt_fecha_vencimiento` date DEFAULT NULL,
  `fl_tasa_recargo` decimal(5,2) DEFAULT '0.00',
  `chr_notas` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `bit_activa` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_cuenta_por_cobrar`),
  KEY `fk_tbl_cuentas_por_cobrar_clientes_idx` (`id_cliente`),
  KEY `fk_tbl_cuentas_por_cobrar_ordenes_idx` (`id_orden`),
  CONSTRAINT `fk_tbl_cuentas_por_cobrar_clientes` FOREIGN KEY (`id_cliente`) REFERENCES `tbl_clientes` (`id_cliente`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_tbl_cuentas_por_cobrar_ordenes` FOREIGN KEY (`id_orden`) REFERENCES `tbl_ordenes` (`id_orden`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_cuentas_por_cobrar`
--

LOCK TABLES `tbl_cuentas_por_cobrar` WRITE;
/*!40000 ALTER TABLE `tbl_cuentas_por_cobrar` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_cuentas_por_cobrar` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_definicion_combos`
--

DROP TABLE IF EXISTS `tbl_definicion_combos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_definicion_combos` (
  `id_definicion_combo` int NOT NULL AUTO_INCREMENT,
  `chr_nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `chr_descripcion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `fl_precio` decimal(10,2) NOT NULL DEFAULT '0.00',
  `bit_activo` tinyint(1) NOT NULL DEFAULT '1',
  `id_impuesto` int DEFAULT NULL,
  PRIMARY KEY (`id_definicion_combo`),
  KEY `fk_tbl_definicion_combos_impuestos_idx` (`id_impuesto`),
  CONSTRAINT `fk_tbl_definicion_combos_impuestos` FOREIGN KEY (`id_impuesto`) REFERENCES `tbl_impuestos` (`id_impuesto`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_definicion_combos`
--

LOCK TABLES `tbl_definicion_combos` WRITE;
/*!40000 ALTER TABLE `tbl_definicion_combos` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_definicion_combos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_devolucion_productos`
--

DROP TABLE IF EXISTS `tbl_devolucion_productos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_devolucion_productos` (
  `id_devolucion_producto` int NOT NULL AUTO_INCREMENT,
  `id_devolucion` int NOT NULL,
  `id_orden_producto` int NOT NULL,
  `int_cantidad` int NOT NULL,
  `fl_monto_reembolso` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id_devolucion_producto`),
  KEY `fk_tbl_devolucion_productos_devoluciones_idx` (`id_devolucion`),
  KEY `fk_tbl_devolucion_productos_orden_productos_idx` (`id_orden_producto`),
  CONSTRAINT `fk_tbl_devolucion_productos_devoluciones` FOREIGN KEY (`id_devolucion`) REFERENCES `tbl_devoluciones` (`id_devolucion`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_tbl_devolucion_productos_orden_productos` FOREIGN KEY (`id_orden_producto`) REFERENCES `tbl_orden_productos` (`id_orden_producto`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_devolucion_productos`
--

LOCK TABLES `tbl_devolucion_productos` WRITE;
/*!40000 ALTER TABLE `tbl_devolucion_productos` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_devolucion_productos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_devoluciones`
--

DROP TABLE IF EXISTS `tbl_devoluciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_devoluciones` (
  `id_devolucion` int NOT NULL AUTO_INCREMENT,
  `id_orden` int NOT NULL,
  `dt_fecha_devolucion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `chr_motivo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `chr_estado` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `fl_monto_reembolso` decimal(10,2) DEFAULT NULL,
  `id_transaccion_reembolso` int DEFAULT NULL,
  PRIMARY KEY (`id_devolucion`),
  KEY `fk_tbl_devoluciones_ordenes_idx` (`id_orden`),
  KEY `fk_tbl_devoluciones_transacciones_idx` (`id_transaccion_reembolso`),
  CONSTRAINT `fk_tbl_devoluciones_ordenes` FOREIGN KEY (`id_orden`) REFERENCES `tbl_ordenes` (`id_orden`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_tbl_devoluciones_transacciones` FOREIGN KEY (`id_transaccion_reembolso`) REFERENCES `tbl_transacciones_pago` (`id_transaccion`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_devoluciones`
--

LOCK TABLES `tbl_devoluciones` WRITE;
/*!40000 ALTER TABLE `tbl_devoluciones` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_devoluciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_direcciones`
--

DROP TABLE IF EXISTS `tbl_direcciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_direcciones` (
  `id_direccion` int NOT NULL AUTO_INCREMENT,
  `id_cliente` int NOT NULL,
  `chr_direccion` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_codigo_postal` bigint NOT NULL,
  `id_tipo_direccion` int NOT NULL DEFAULT '1',
  `id_colonia` bigint NOT NULL,
  `id_tipo_asentamiento` bigint NOT NULL DEFAULT '2435439259',
  `bit_default` tinyint(1) NOT NULL DEFAULT '0',
  `bit_activa` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_direccion`),
  KEY `fk_tbl_direcciones_clientes_idx` (`id_cliente`),
  KEY `index4` (`id_tipo_direccion`),
  KEY `index5` (`id_codigo_postal`),
  CONSTRAINT `fk_tbl_direcciones_1` FOREIGN KEY (`id_tipo_direccion`) REFERENCES `tbl_tipos_direcciones` (`id_tipos_direcciones`),
  CONSTRAINT `fk_tbl_direcciones_clientes` FOREIGN KEY (`id_cliente`) REFERENCES `tbl_clientes` (`id_cliente`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_direcciones`
--

LOCK TABLES `tbl_direcciones` WRITE;
/*!40000 ALTER TABLE `tbl_direcciones` DISABLE KEYS */;
INSERT INTO `tbl_direcciones` VALUES (1,1,'Calle Principal dir 2 100 Inter. 1',2873680545,4,433601300,2435439259,0,1),(2,1,'dir 2 calle 3 numero 4',2873680545,1,508625272,2435439259,1,1);
/*!40000 ALTER TABLE `tbl_direcciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_documentos`
--

DROP TABLE IF EXISTS `tbl_documentos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_documentos` (
  `id_documento` int NOT NULL AUTO_INCREMENT,
  `id_documento_tipo` int NOT NULL,
  `chr_nombre_archivo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `chr_ruta_archivo` varchar(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Path to the document file (e.g., on a server or cloud storage)',
  `chr_tipo_archivo` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'MIME type (e.g., application/pdf, image/jpeg)',
  `dt_fecha_subida` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `id_usuario_subida` int DEFAULT NULL,
  `chr_notas` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `bit_activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_documento`),
  KEY `fk_tbl_documentos_documentos_tipos_idx` (`id_documento_tipo`),
  KEY `fk_tbl_documentos_usuarios_idx` (`id_usuario_subida`),
  CONSTRAINT `fk_tbl_documentos_documentos_tipos` FOREIGN KEY (`id_documento_tipo`) REFERENCES `tbl_documentos_tipos` (`id_documento_tipo`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_tbl_documentos_usuarios` FOREIGN KEY (`id_usuario_subida`) REFERENCES `tbl_usuarios` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_documentos`
--

LOCK TABLES `tbl_documentos` WRITE;
/*!40000 ALTER TABLE `tbl_documentos` DISABLE KEYS */;
INSERT INTO `tbl_documentos` VALUES (1,3,'ab91eef95a4b974f3dcb32c497802f08.jpg','uploads/2025/04/16/cef134f3006772dc4853df87c13216ccfe8c979150effad3e143476dda4105cd','jpg','2025-04-16 09:10:07',1,'tuui pwwtuorewt uowri oru twei',1);
/*!40000 ALTER TABLE `tbl_documentos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_documentos_tipos`
--

DROP TABLE IF EXISTS `tbl_documentos_tipos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_documentos_tipos` (
  `id_documento_tipo` int NOT NULL AUTO_INCREMENT,
  `chr_nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `chr_descripcion` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bit_activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_documento_tipo`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_documentos_tipos`
--

LOCK TABLES `tbl_documentos_tipos` WRITE;
/*!40000 ALTER TABLE `tbl_documentos_tipos` DISABLE KEYS */;
INSERT INTO `tbl_documentos_tipos` VALUES (1,'Factura','Documento de factura',1),(2,'Contrato','Documento de contrato',1),(3,'Comprobante de pago','Documento que comprueba un pago',1),(4,'Identificación oficial','Documento de identificación oficial',1),(5,'Constancia Fiscal','Constancia fiscal actualizada para efectos de factucración',1);
/*!40000 ALTER TABLE `tbl_documentos_tipos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_empleados_cliente`
--

DROP TABLE IF EXISTS `tbl_empleados_cliente`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_empleados_cliente` (
  `id_empleado_cliente` int NOT NULL AUTO_INCREMENT,
  `id_cliente` int NOT NULL,
  `chr_nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `chr_apellido` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `chr_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `chr_telefono` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fl_limite_credito_individual` decimal(10,2) NOT NULL DEFAULT '0.00',
  `fl_credito_disponible` decimal(10,2) NOT NULL DEFAULT '0.00',
  `bit_activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_empleado_cliente`),
  UNIQUE KEY `chr_email_UNIQUE` (`chr_email`),
  KEY `fk_tbl_empleados_cliente_clientes_idx` (`id_cliente`),
  CONSTRAINT `fk_tbl_empleados_cliente_clientes` FOREIGN KEY (`id_cliente`) REFERENCES `tbl_clientes` (`id_cliente`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_empleados_cliente`
--

LOCK TABLES `tbl_empleados_cliente` WRITE;
/*!40000 ALTER TABLE `tbl_empleados_cliente` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_empleados_cliente` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_envios`
--

DROP TABLE IF EXISTS `tbl_envios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_envios` (
  `id_envio` int NOT NULL AUTO_INCREMENT,
  `chr_compania` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `chr_numero_seguimiento` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dt_fecha_envio` date DEFAULT NULL,
  `dt_fecha_entrega_estimada` date DEFAULT NULL,
  `fl_costo` decimal(10,2) DEFAULT NULL,
  `bit_entregado` tinyint(1) NOT NULL DEFAULT '0',
  `dt_fecha_entrega_real` date DEFAULT NULL,
  `chr_nombre_receptor` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id_envio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_envios`
--

LOCK TABLES `tbl_envios` WRITE;
/*!40000 ALTER TABLE `tbl_envios` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_envios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_estados_ordenes`
--

DROP TABLE IF EXISTS `tbl_estados_ordenes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_estados_ordenes` (
  `id_estado_orden` int NOT NULL AUTO_INCREMENT,
  `chr_nombre` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `chr_descripcion` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bit_activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_estado_orden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_estados_ordenes`
--

LOCK TABLES `tbl_estados_ordenes` WRITE;
/*!40000 ALTER TABLE `tbl_estados_ordenes` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_estados_ordenes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_formas_pago`
--

DROP TABLE IF EXISTS `tbl_formas_pago`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_formas_pago` (
  `id_forma_pago` int NOT NULL AUTO_INCREMENT,
  `chr_forma_pago` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fl_comision_extra` decimal(10,2) DEFAULT '0.00',
  `int_activo` int DEFAULT NULL,
  PRIMARY KEY (`id_forma_pago`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_formas_pago`
--

LOCK TABLES `tbl_formas_pago` WRITE;
/*!40000 ALTER TABLE `tbl_formas_pago` DISABLE KEYS */;
INSERT INTO `tbl_formas_pago` VALUES (1,'Efectivo',0.00,1),(2,'Transferencia',0.02,1),(3,'Tarjeta Déb/Créd',0.04,1);
/*!40000 ALTER TABLE `tbl_formas_pago` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_grupos_def`
--

DROP TABLE IF EXISTS `tbl_grupos_def`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_grupos_def` (
  `id_grupos_def` int NOT NULL AUTO_INCREMENT,
  `chr_nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `chr_descripcion` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bit_activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_grupos_def`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_grupos_def`
--

LOCK TABLES `tbl_grupos_def` WRITE;
/*!40000 ALTER TABLE `tbl_grupos_def` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_grupos_def` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_identificadores_tipos`
--

DROP TABLE IF EXISTS `tbl_identificadores_tipos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_identificadores_tipos` (
  `id_identificador_tipo` int NOT NULL AUTO_INCREMENT,
  `chr_nombre` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `chr_descripcion` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bit_activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_identificador_tipo`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_identificadores_tipos`
--

LOCK TABLES `tbl_identificadores_tipos` WRITE;
/*!40000 ALTER TABLE `tbl_identificadores_tipos` DISABLE KEYS */;
INSERT INTO `tbl_identificadores_tipos` VALUES (1,'SKU','Stock Keeping Unit',1),(2,'Serial Number','Manufacturer-assigned serial number',1),(3,'UPC','Universal Product Code',1),(4,'Part Number','Part Number',1);
/*!40000 ALTER TABLE `tbl_identificadores_tipos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_imagenes`
--

DROP TABLE IF EXISTS `tbl_imagenes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_imagenes` (
  `id_imagen` int NOT NULL AUTO_INCREMENT,
  `chr_nombre` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `chr_ruta` varchar(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `chr_alt_text` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bit_activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_imagen`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_imagenes`
--

LOCK TABLES `tbl_imagenes` WRITE;
/*!40000 ALTER TABLE `tbl_imagenes` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_imagenes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_impuestos`
--

DROP TABLE IF EXISTS `tbl_impuestos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_impuestos` (
  `id_impuesto` int NOT NULL AUTO_INCREMENT,
  `chr_nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `fl_tasa` decimal(5,2) NOT NULL,
  `chr_codigo` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bit_activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_impuesto`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_impuestos`
--

LOCK TABLES `tbl_impuestos` WRITE;
/*!40000 ALTER TABLE `tbl_impuestos` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_impuestos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_inventario`
--

DROP TABLE IF EXISTS `tbl_inventario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_inventario` (
  `id_inventario_id` int NOT NULL AUTO_INCREMENT,
  `id_insumo_id` int NOT NULL,
  `int_cantidad` int NOT NULL,
  `id_control_fecha` int DEFAULT NULL,
  PRIMARY KEY (`id_inventario_id`),
  KEY `id_insumo_id` (`id_insumo_id`),
  KEY `index3` (`id_control_fecha`),
  CONSTRAINT `fk_tbl_inventario_1` FOREIGN KEY (`id_control_fecha`) REFERENCES `tbl_control_fechas` (`id_control_fechas`),
  CONSTRAINT `tbl_inventario_ibfk_1` FOREIGN KEY (`id_insumo_id`) REFERENCES `tbl_insumos` (`id_insumo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_inventario`
--

LOCK TABLES `tbl_inventario` WRITE;
/*!40000 ALTER TABLE `tbl_inventario` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_inventario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_inventario_ubicaciones`
--

DROP TABLE IF EXISTS `tbl_inventario_ubicaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_inventario_ubicaciones` (
  `id_inventario_ubicacion` int NOT NULL AUTO_INCREMENT,
  `id_producto` int NOT NULL,
  `id_ubicacion` int NOT NULL,
  `int_cantidad` int NOT NULL DEFAULT '0',
  `id_almacen` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_inventario_ubicacion`),
  KEY `fk_tbl_inventario_ubicaciones_productos_idx` (`id_producto`),
  KEY `fk_tbl_inventario_ubicaciones_ubicaciones_idx` (`id_ubicacion`),
  KEY `index4` (`id_almacen`),
  CONSTRAINT `fk_tbl_inventario_ubicaciones_1` FOREIGN KEY (`id_almacen`) REFERENCES `tbl_almacenes` (`id_almacen`),
  CONSTRAINT `fk_tbl_inventario_ubicaciones_productos` FOREIGN KEY (`id_producto`) REFERENCES `tbl_productos` (`id_producto`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_tbl_inventario_ubicaciones_ubicaciones` FOREIGN KEY (`id_ubicacion`) REFERENCES `tbl_ubicaciones` (`id_ubicacion`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_inventario_ubicaciones`
--

LOCK TABLES `tbl_inventario_ubicaciones` WRITE;
/*!40000 ALTER TABLE `tbl_inventario_ubicaciones` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_inventario_ubicaciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_mermas`
--

DROP TABLE IF EXISTS `tbl_mermas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_mermas` (
  `id_merma` int NOT NULL AUTO_INCREMENT,
  `id_insumo` int NOT NULL,
  `int_cantidad` int DEFAULT NULL,
  `dt_control_fecha` int DEFAULT NULL,
  PRIMARY KEY (`id_merma`),
  KEY `index2` (`id_insumo`),
  KEY `index3` (`dt_control_fecha`),
  CONSTRAINT `fk_tbl_mermas_1` FOREIGN KEY (`id_insumo`) REFERENCES `tbl_insumos` (`id_insumo`),
  CONSTRAINT `fk_tbl_mermas_2` FOREIGN KEY (`dt_control_fecha`) REFERENCES `tbl_control_fechas` (`id_control_fechas`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_mermas`
--

LOCK TABLES `tbl_mermas` WRITE;
/*!40000 ALTER TABLE `tbl_mermas` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_mermas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_metodos_pago`
--

DROP TABLE IF EXISTS `tbl_metodos_pago`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_metodos_pago` (
  `id_metodo_pago` int NOT NULL AUTO_INCREMENT,
  `chr_nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `chr_descripcion` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bit_activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_metodo_pago`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_metodos_pago`
--

LOCK TABLES `tbl_metodos_pago` WRITE;
/*!40000 ALTER TABLE `tbl_metodos_pago` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_metodos_pago` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_numero_telefono`
--

DROP TABLE IF EXISTS `tbl_numero_telefono`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_numero_telefono` (
  `id_numero_telefono` int NOT NULL AUTO_INCREMENT,
  `chr_lada` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `chr_telefono` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_tipo_telefono` int DEFAULT NULL,
  `bit_activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_numero_telefono`),
  KEY `fk_tbl_numero_telefono_tipo_telefono_idx` (`id_tipo_telefono`),
  CONSTRAINT `fk_tbl_numero_telefono_tipo_telefono` FOREIGN KEY (`id_tipo_telefono`) REFERENCES `tbl_tipo_telefono` (`id_tipo_telefono`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_numero_telefono`
--

LOCK TABLES `tbl_numero_telefono` WRITE;
/*!40000 ALTER TABLE `tbl_numero_telefono` DISABLE KEYS */;
INSERT INTO `tbl_numero_telefono` VALUES (1,'951','1234567',1,1);
/*!40000 ALTER TABLE `tbl_numero_telefono` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_orden_productos`
--

DROP TABLE IF EXISTS `tbl_orden_productos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_orden_productos` (
  `id_orden_producto` int NOT NULL AUTO_INCREMENT,
  `id_orden` int NOT NULL,
  `id_producto` int NOT NULL,
  `id_precio` int NOT NULL,
  `int_cantidad` int unsigned NOT NULL,
  `fl_descuento` decimal(5,2) DEFAULT '0.00',
  PRIMARY KEY (`id_orden_producto`),
  KEY `fk_tbl_orden_productos_ordenes_idx` (`id_orden`),
  KEY `fk_tbl_orden_productos_productos_idx` (`id_producto`),
  KEY `fk_tbl_orden_productos_precios_productos_idx` (`id_precio`),
  CONSTRAINT `fk_tbl_orden_productos_impuestos` FOREIGN KEY (`id_precio`) REFERENCES `tbl_precios_productos` (`id_precio`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_tbl_orden_productos_ordenes` FOREIGN KEY (`id_orden`) REFERENCES `tbl_ordenes` (`id_orden`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_tbl_orden_productos_productos` FOREIGN KEY (`id_producto`) REFERENCES `tbl_productos` (`id_producto`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_orden_productos`
--

LOCK TABLES `tbl_orden_productos` WRITE;
/*!40000 ALTER TABLE `tbl_orden_productos` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_orden_productos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_ordenes`
--

DROP TABLE IF EXISTS `tbl_ordenes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_ordenes` (
  `id_orden` int NOT NULL AUTO_INCREMENT,
  `id_empleado_cliente` int NOT NULL,
  `id_cliente_empresa` int DEFAULT NULL,
  `dt_fecha_orden` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fl_subtotal` decimal(10,2) NOT NULL,
  `fl_total` decimal(10,2) NOT NULL,
  `id_estado_orden` int NOT NULL,
  `id_envio` int DEFAULT NULL,
  `chr_notas` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `chr_purchase_order_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_direccion_envio` int DEFAULT NULL,
  PRIMARY KEY (`id_orden`),
  KEY `fk_tbl_ordenes_empleados_cliente_idx` (`id_empleado_cliente`),
  KEY `fk_tbl_ordenes_clientes_empresa_idx` (`id_cliente_empresa`),
  KEY `fk_tbl_ordenes_envios_idx` (`id_envio`),
  KEY `fk_tbl_ordenes_estados_ordenes_idx` (`id_estado_orden`),
  CONSTRAINT `fk_tbl_ordenes_clientes_empresa` FOREIGN KEY (`id_cliente_empresa`) REFERENCES `tbl_clientes` (`id_cliente`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_tbl_ordenes_empleados_cliente` FOREIGN KEY (`id_empleado_cliente`) REFERENCES `tbl_empleados_cliente` (`id_empleado_cliente`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_tbl_ordenes_envios` FOREIGN KEY (`id_envio`) REFERENCES `tbl_envios` (`id_envio`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_tbl_ordenes_estados_ordenes` FOREIGN KEY (`id_estado_orden`) REFERENCES `tbl_estados_ordenes` (`id_estado_orden`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_ordenes`
--

LOCK TABLES `tbl_ordenes` WRITE;
/*!40000 ALTER TABLE `tbl_ordenes` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_ordenes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_ordenes_cerradas`
--

DROP TABLE IF EXISTS `tbl_ordenes_cerradas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_ordenes_cerradas` (
  `id_orden_cerrada` int NOT NULL AUTO_INCREMENT,
  `id_orden_id` int NOT NULL,
  `dt_horafecha_cierre_orden` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fl_total` decimal(10,2) NOT NULL,
  `bool_factura` tinyint(1) NOT NULL DEFAULT '0',
  `chr_referencia_notas` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `int_lvl_report` int NOT NULL DEFAULT '1',
  `id_ticket_IDNr` int NOT NULL,
  `id_control_fecha` int DEFAULT NULL,
  `int_forma_pago` int DEFAULT '1',
  PRIMARY KEY (`id_orden_cerrada`),
  KEY `id_orden_id` (`id_orden_id`),
  KEY `bsbfs` (`id_ticket_IDNr`) COMMENT 'fdsagr',
  KEY `index4` (`id_control_fecha`),
  KEY `index5` (`int_forma_pago`),
  CONSTRAINT `Cerrada_orden_ID` FOREIGN KEY (`id_orden_id`) REFERENCES `tbl_ordenes` (`id_orden`),
  CONSTRAINT `fk_tbl_ordenes_cerradas_1` FOREIGN KEY (`id_control_fecha`) REFERENCES `tbl_control_fechas` (`id_control_fechas`),
  CONSTRAINT `fk_tbl_ordenes_cerradas_2` FOREIGN KEY (`int_forma_pago`) REFERENCES `tbl_formas_pago` (`id_forma_pago`),
  CONSTRAINT `tbl_ordenes_cerradas_ibfk_1` FOREIGN KEY (`id_ticket_IDNr`) REFERENCES `tbl_ticket_cons` (`id_ticketNrConsecutivo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_ordenes_cerradas`
--

LOCK TABLES `tbl_ordenes_cerradas` WRITE;
/*!40000 ALTER TABLE `tbl_ordenes_cerradas` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_ordenes_cerradas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_ordenes_descuento`
--

DROP TABLE IF EXISTS `tbl_ordenes_descuento`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_ordenes_descuento` (
  `id_ordenes_descuento` int NOT NULL AUTO_INCREMENT,
  `id_orden_cerrada_id` int DEFAULT NULL,
  `fl_descuento_aplicado` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id_ordenes_descuento`),
  KEY `index2` (`id_orden_cerrada_id`),
  CONSTRAINT `fk_tbl_ordenes_descuento_1` FOREIGN KEY (`id_orden_cerrada_id`) REFERENCES `tbl_ordenes_cerradas` (`id_orden_cerrada`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_ordenes_descuento`
--

LOCK TABLES `tbl_ordenes_descuento` WRITE;
/*!40000 ALTER TABLE `tbl_ordenes_descuento` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_ordenes_descuento` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_pagos_a_plazos`
--

DROP TABLE IF EXISTS `tbl_pagos_a_plazos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_pagos_a_plazos` (
  `id_pago_a_plazos` int NOT NULL AUTO_INCREMENT,
  `id_cuenta_por_cobrar` int NOT NULL,
  `dt_fecha_pago` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fl_cantidad_pagada` decimal(10,2) NOT NULL,
  `fl_recargo_aplicado` decimal(10,2) DEFAULT '0.00',
  `int_metodo_pago` int DEFAULT NULL,
  `chr_referencia` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id_pago_a_plazos`),
  KEY `fk_tbl_pagos_a_plazos_cuentas_por_cobrar_idx` (`id_cuenta_por_cobrar`),
  KEY `index3` (`int_metodo_pago`),
  CONSTRAINT `fk_tbl_pagos_a_plazos_1` FOREIGN KEY (`int_metodo_pago`) REFERENCES `tbl_formas_pago` (`id_forma_pago`),
  CONSTRAINT `fk_tbl_pagos_a_plazos_cuentas_por_cobrar` FOREIGN KEY (`id_cuenta_por_cobrar`) REFERENCES `tbl_cuentas_por_cobrar` (`id_cuenta_por_cobrar`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_pagos_a_plazos`
--

LOCK TABLES `tbl_pagos_a_plazos` WRITE;
/*!40000 ALTER TABLE `tbl_pagos_a_plazos` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_pagos_a_plazos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_precios_productos`
--

DROP TABLE IF EXISTS `tbl_precios_productos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_precios_productos` (
  `id_precio` int NOT NULL AUTO_INCREMENT,
  `id_producto` int NOT NULL,
  `id_proveedor_producto` int DEFAULT NULL,
  `fl_precio` decimal(10,2) NOT NULL,
  `dt_fecha_inicio` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `dt_fecha_fin` timestamp NULL DEFAULT NULL,
  `id_impuesto` int DEFAULT NULL,
  PRIMARY KEY (`id_precio`),
  KEY `fk_tbl_precios_productos_productos_idx` (`id_producto`),
  KEY `fk_tbl_precios_productos_impuestos_idx` (`id_impuesto`),
  KEY `fk_tbl_precios_productos_proveedor_producto_idx` (`id_proveedor_producto`),
  CONSTRAINT `fk_tbl_precios_productos_impuestos` FOREIGN KEY (`id_impuesto`) REFERENCES `tbl_impuestos` (`id_impuesto`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_tbl_precios_productos_productos` FOREIGN KEY (`id_producto`) REFERENCES `tbl_productos` (`id_producto`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_tbl_precios_productos_proveedor_producto` FOREIGN KEY (`id_proveedor_producto`) REFERENCES `tbl_proveedor_producto` (`id_proveedor_producto`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10326 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_precios_productos`
--

LOCK TABLES `tbl_precios_productos` WRITE;
/*!40000 ALTER TABLE `tbl_precios_productos` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_precios_productos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_prods_variantes`
--

DROP TABLE IF EXISTS `tbl_prods_variantes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_prods_variantes` (
  `id_prods_variantes` int NOT NULL AUTO_INCREMENT,
  `id_producto` int NOT NULL,
  `id_variante` int NOT NULL,
  `int_activo` int DEFAULT '1',
  PRIMARY KEY (`id_prods_variantes`),
  KEY `dsff` (`id_producto`),
  KEY `id_variante` (`id_variante`),
  CONSTRAINT `tbl_prods_variantes_ibfk_1` FOREIGN KEY (`id_variante`) REFERENCES `tbl_variantes_platillos` (`id_variante_pl`),
  CONSTRAINT `tbl_prods_variantes_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `tbl_precios_productos` (`id_producto`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_prods_variantes`
--

LOCK TABLES `tbl_prods_variantes` WRITE;
/*!40000 ALTER TABLE `tbl_prods_variantes` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_prods_variantes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_prods_x_combo`
--

DROP TABLE IF EXISTS `tbl_prods_x_combo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_prods_x_combo` (
  `id_prod_x_combo` int NOT NULL AUTO_INCREMENT,
  `id_combo` int NOT NULL,
  `id_producto` int NOT NULL,
  `int_cantidad` int NOT NULL,
  `bol_activo` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_prod_x_combo`),
  KEY `index2` (`id_producto`),
  KEY `index3` (`id_combo`),
  CONSTRAINT `fk_tbl_prods_x_combo_1` FOREIGN KEY (`id_combo`) REFERENCES `tbl_productos` (`id_producto`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_tbl_prods_x_combo_2` FOREIGN KEY (`id_producto`) REFERENCES `tbl_productos` (`id_producto`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_prods_x_combo`
--

LOCK TABLES `tbl_prods_x_combo` WRITE;
/*!40000 ALTER TABLE `tbl_prods_x_combo` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_prods_x_combo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_prods_x_orden`
--

DROP TABLE IF EXISTS `tbl_prods_x_orden`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_prods_x_orden` (
  `id_prod_x_orden` int NOT NULL AUTO_INCREMENT,
  `int_orden_id` int NOT NULL,
  `int_producto_id` int NOT NULL,
  `int_cantidad` int NOT NULL,
  `bool_activo` int DEFAULT NULL,
  `dt_horafecha_pedido` timestamp NULL DEFAULT NULL,
  `bool_impreso` int DEFAULT '0',
  `int_tipo_precio` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_prod_x_orden`),
  KEY `int_producto_id` (`int_producto_id`),
  KEY `index4` (`int_tipo_precio`),
  KEY `int_orden_id` (`int_orden_id`),
  CONSTRAINT `fk_tbl_prods_x_orden_1` FOREIGN KEY (`int_tipo_precio`) REFERENCES `tbl_tipos_precios` (`id_tipo_precio`),
  CONSTRAINT `fk_tbl_prods_x_orden_2` FOREIGN KEY (`int_producto_id`) REFERENCES `tbl_productos` (`id_producto`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_prods_x_orden`
--

LOCK TABLES `tbl_prods_x_orden` WRITE;
/*!40000 ALTER TABLE `tbl_prods_x_orden` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_prods_x_orden` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_productos`
--

DROP TABLE IF EXISTS `tbl_productos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_productos` (
  `id_producto` int NOT NULL AUTO_INCREMENT,
  `chr_nombre_prod` varchar(1024) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `chr_desc_prod` varchar(256) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  `id_categoria` int NOT NULL,
  `int_activo` int NOT NULL,
  `fl_ordenar` float DEFAULT NULL,
  `id_grupo_def` int DEFAULT NULL,
  `id_unidad_medida` int DEFAULT NULL,
  `bit_es_combo` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_producto`),
  KEY `id_categoria` (`id_categoria`),
  KEY `index3` (`id_grupo_def`),
  KEY `fk_tbl_productos_unidades_medida_idx` (`id_unidad_medida`),
  CONSTRAINT `fk_tbl_productos_1` FOREIGN KEY (`id_grupo_def`) REFERENCES `tbl_grupos_def` (`id_grupos_def`),
  CONSTRAINT `fk_tbl_productos_unidades_medida` FOREIGN KEY (`id_unidad_medida`) REFERENCES `tbl_unidades_medida` (`id_unidad_medida`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `tbl_productos_ibfk_1` FOREIGN KEY (`id_categoria`) REFERENCES `tbl_categorias` (`id_categoria`)
) ENGINE=InnoDB AUTO_INCREMENT=10662 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_productos`
--

LOCK TABLES `tbl_productos` WRITE;
/*!40000 ALTER TABLE `tbl_productos` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_productos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_productos_especificaciones`
--

DROP TABLE IF EXISTS `tbl_productos_especificaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_productos_especificaciones` (
  `id_especificacion` int NOT NULL AUTO_INCREMENT,
  `id_producto` int NOT NULL,
  `chr_clave` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `chr_valor` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id_especificacion`),
  KEY `fk_productos_especificaciones_productos_idx` (`id_producto`),
  CONSTRAINT `fk_productos_especificaciones_productos` FOREIGN KEY (`id_producto`) REFERENCES `tbl_productos` (`id_producto`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=26029 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_productos_especificaciones`
--

LOCK TABLES `tbl_productos_especificaciones` WRITE;
/*!40000 ALTER TABLE `tbl_productos_especificaciones` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_productos_especificaciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_productos_identificadores`
--

DROP TABLE IF EXISTS `tbl_productos_identificadores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_productos_identificadores` (
  `id_producto_identificador` int NOT NULL AUTO_INCREMENT,
  `id_producto` int NOT NULL,
  `id_identificador_tipo` int NOT NULL,
  `chr_valor` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id_producto_identificador`),
  KEY `fk_tbl_productos_identificadores_productos_idx` (`id_producto`),
  KEY `fk_tbl_productos_identificadores_identificadores_tipos_idx` (`id_identificador_tipo`),
  CONSTRAINT `fk_tbl_productos_identificadores_identificadores_tipos` FOREIGN KEY (`id_identificador_tipo`) REFERENCES `tbl_identificadores_tipos` (`id_identificador_tipo`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_tbl_productos_identificadores_productos` FOREIGN KEY (`id_producto`) REFERENCES `tbl_productos` (`id_producto`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=30937 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_productos_identificadores`
--

LOCK TABLES `tbl_productos_identificadores` WRITE;
/*!40000 ALTER TABLE `tbl_productos_identificadores` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_productos_identificadores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_productos_imagenes`
--

DROP TABLE IF EXISTS `tbl_productos_imagenes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_productos_imagenes` (
  `id_producto_imagen` int NOT NULL AUTO_INCREMENT,
  `id_producto` int NOT NULL,
  `id_imagen` int NOT NULL,
  PRIMARY KEY (`id_producto_imagen`),
  KEY `fk_productos_imagenes_productos_idx` (`id_producto`),
  KEY `fk_productos_imagenes_imagenes_idx` (`id_imagen`),
  CONSTRAINT `fk_productos_imagenes_imagenes` FOREIGN KEY (`id_imagen`) REFERENCES `tbl_imagenes` (`id_imagen`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_productos_imagenes_productos` FOREIGN KEY (`id_producto`) REFERENCES `tbl_productos` (`id_producto`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_productos_imagenes`
--

LOCK TABLES `tbl_productos_imagenes` WRITE;
/*!40000 ALTER TABLE `tbl_productos_imagenes` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_productos_imagenes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_productos_promociones`
--

DROP TABLE IF EXISTS `tbl_productos_promociones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_productos_promociones` (
  `id_promocion` int NOT NULL AUTO_INCREMENT,
  `id_producto` int NOT NULL,
  `chr_promocion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id_promocion`),
  KEY `fk_productos_promociones_productos_idx` (`id_producto`),
  CONSTRAINT `fk_productos_promociones_productos` FOREIGN KEY (`id_producto`) REFERENCES `tbl_productos` (`id_producto`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2085 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_productos_promociones`
--

LOCK TABLES `tbl_productos_promociones` WRITE;
/*!40000 ALTER TABLE `tbl_productos_promociones` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_productos_promociones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_proveedor_producto`
--

DROP TABLE IF EXISTS `tbl_proveedor_producto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_proveedor_producto` (
  `id_proveedor_producto` int NOT NULL AUTO_INCREMENT,
  `id_proveedor` int NOT NULL,
  `id_producto` int NOT NULL,
  `bit_activo` tinyint(1) NOT NULL DEFAULT '1',
  `dt_fecha_asociacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_proveedor_producto`),
  UNIQUE KEY `unq_proveedor_producto` (`id_proveedor`,`id_producto`),
  KEY `fk_tbl_proveedor_producto_productos_idx` (`id_producto`),
  CONSTRAINT `fk_tbl_proveedor_producto_productos` FOREIGN KEY (`id_producto`) REFERENCES `tbl_productos` (`id_producto`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_tbl_proveedor_producto_proveedores` FOREIGN KEY (`id_proveedor`) REFERENCES `tbl_proveedores` (`id_proveedor`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_proveedor_producto`
--

LOCK TABLES `tbl_proveedor_producto` WRITE;
/*!40000 ALTER TABLE `tbl_proveedor_producto` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_proveedor_producto` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_proveedores`
--

DROP TABLE IF EXISTS `tbl_proveedores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_proveedores` (
  `id_proveedor` int NOT NULL AUTO_INCREMENT,
  `chr_nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `chr_tax_id` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `chr_telefono` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `chr_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_direccion` int DEFAULT NULL,
  `bit_activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_proveedor`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_proveedores`
--

LOCK TABLES `tbl_proveedores` WRITE;
/*!40000 ALTER TABLE `tbl_proveedores` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_proveedores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_puestos`
--

DROP TABLE IF EXISTS `tbl_puestos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_puestos` (
  `id_puesto` int NOT NULL AUTO_INCREMENT,
  `chr_puesto` varchar(24) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  `chr_desc_puesto` varchar(128) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  `bit_activo` int NOT NULL,
  PRIMARY KEY (`id_puesto`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_puestos`
--

LOCK TABLES `tbl_puestos` WRITE;
/*!40000 ALTER TABLE `tbl_puestos` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_puestos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_reportes`
--

DROP TABLE IF EXISTS `tbl_reportes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_reportes` (
  `id_reporte` int NOT NULL AUTO_INCREMENT,
  `dt_fecha_creacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `chr_reporte` text CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  `int_tipo_reporte_id` int NOT NULL,
  PRIMARY KEY (`id_reporte`),
  KEY `int_tipo_reporte_id` (`int_tipo_reporte_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_reportes`
--

LOCK TABLES `tbl_reportes` WRITE;
/*!40000 ALTER TABLE `tbl_reportes` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_reportes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_status`
--

DROP TABLE IF EXISTS `tbl_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_status` (
  `id_status` int NOT NULL AUTO_INCREMENT,
  `chr_status` varchar(32) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  PRIMARY KEY (`id_status`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_status`
--

LOCK TABLES `tbl_status` WRITE;
/*!40000 ALTER TABLE `tbl_status` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_telefonos`
--

DROP TABLE IF EXISTS `tbl_telefonos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_telefonos` (
  `id_telefono` int NOT NULL,
  `chr_lada` varchar(6) DEFAULT NULL,
  `chr_telefono` varchar(15) DEFAULT NULL,
  `int_tipo_telefono` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_telefono`),
  KEY `index2` (`int_tipo_telefono`),
  CONSTRAINT `fk_tbl_telefonos_1` FOREIGN KEY (`int_tipo_telefono`) REFERENCES `tbl_tipo_telefono` (`id_tipo_telefono`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_telefonos`
--

LOCK TABLES `tbl_telefonos` WRITE;
/*!40000 ALTER TABLE `tbl_telefonos` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_telefonos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_tipo_reporte`
--

DROP TABLE IF EXISTS `tbl_tipo_reporte`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_tipo_reporte` (
  `id_tipo_reporte` int NOT NULL AUTO_INCREMENT,
  `chr_tipo_reporte` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  `chr_desc_tipo_reporte` varchar(256) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  PRIMARY KEY (`id_tipo_reporte`),
  KEY `id_tipo_reporte` (`id_tipo_reporte`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_tipo_reporte`
--

LOCK TABLES `tbl_tipo_reporte` WRITE;
/*!40000 ALTER TABLE `tbl_tipo_reporte` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_tipo_reporte` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_tipo_telefono`
--

DROP TABLE IF EXISTS `tbl_tipo_telefono`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_tipo_telefono` (
  `id_tipo_telefono` int NOT NULL AUTO_INCREMENT,
  `chr_tipo_telefono` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id_tipo_telefono`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_tipo_telefono`
--

LOCK TABLES `tbl_tipo_telefono` WRITE;
/*!40000 ALTER TABLE `tbl_tipo_telefono` DISABLE KEYS */;
INSERT INTO `tbl_tipo_telefono` VALUES (1,'celular personal'),(2,'oficina fijo'),(3,'contacto de emergencia'),(4,'asistente');
/*!40000 ALTER TABLE `tbl_tipo_telefono` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_tipos_direcciones`
--

DROP TABLE IF EXISTS `tbl_tipos_direcciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_tipos_direcciones` (
  `id_tipos_direcciones` int NOT NULL AUTO_INCREMENT,
  `chr_tipo_direccion` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id_tipos_direcciones`),
  UNIQUE KEY `id_tipos_direcciones_UNIQUE` (`id_tipos_direcciones`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_tipos_direcciones`
--

LOCK TABLES `tbl_tipos_direcciones` WRITE;
/*!40000 ALTER TABLE `tbl_tipos_direcciones` DISABLE KEYS */;
INSERT INTO `tbl_tipos_direcciones` VALUES (1,'Dirección Fiscal'),(2,'Envios'),(3,'Oficina alternativa'),(4,'Bodega/Almacen'),(5,'Correo Postal');
/*!40000 ALTER TABLE `tbl_tipos_direcciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_tipos_precios`
--

DROP TABLE IF EXISTS `tbl_tipos_precios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_tipos_precios` (
  `id_tipo_precio` int NOT NULL AUTO_INCREMENT,
  `chr_nombre_precio` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci NOT NULL,
  `bit_activo` int DEFAULT '1',
  PRIMARY KEY (`id_tipo_precio`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_tipos_precios`
--

LOCK TABLES `tbl_tipos_precios` WRITE;
/*!40000 ALTER TABLE `tbl_tipos_precios` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_tipos_precios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_transacciones_pago`
--

DROP TABLE IF EXISTS `tbl_transacciones_pago`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_transacciones_pago` (
  `id_transaccion` int NOT NULL AUTO_INCREMENT,
  `id_orden` int NOT NULL,
  `id_metodo_pago` int NOT NULL,
  `fl_monto` decimal(10,2) NOT NULL,
  `dt_fecha_pago` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `chr_referencia` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `chr_estado` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id_transaccion`),
  KEY `fk_tbl_transacciones_pago_ordenes_idx` (`id_orden`),
  KEY `fk_tbl_transacciones_pago_metodos_pago_idx` (`id_metodo_pago`),
  CONSTRAINT `fk_tbl_transacciones_pago_metodos_pago` FOREIGN KEY (`id_metodo_pago`) REFERENCES `tbl_metodos_pago` (`id_metodo_pago`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_tbl_transacciones_pago_ordenes` FOREIGN KEY (`id_orden`) REFERENCES `tbl_ordenes` (`id_orden`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_transacciones_pago`
--

LOCK TABLES `tbl_transacciones_pago` WRITE;
/*!40000 ALTER TABLE `tbl_transacciones_pago` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_transacciones_pago` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_ubicaciones`
--

DROP TABLE IF EXISTS `tbl_ubicaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_ubicaciones` (
  `id_ubicacion` int NOT NULL AUTO_INCREMENT,
  `id_almacen` int NOT NULL,
  `chr_nombre` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `chr_descripcion` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id_ubicacion`),
  KEY `fk_tbl_ubicaciones_almacenes_idx` (`id_almacen`),
  CONSTRAINT `fk_tbl_ubicaciones_almacenes` FOREIGN KEY (`id_almacen`) REFERENCES `tbl_almacenes` (`id_almacen`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_ubicaciones`
--

LOCK TABLES `tbl_ubicaciones` WRITE;
/*!40000 ALTER TABLE `tbl_ubicaciones` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_ubicaciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_unidades_medida`
--

DROP TABLE IF EXISTS `tbl_unidades_medida`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_unidades_medida` (
  `id_unidad_medida` int NOT NULL AUTO_INCREMENT,
  `chr_nombre` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `chr_simbolo` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bit_activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_unidad_medida`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_unidades_medida`
--

LOCK TABLES `tbl_unidades_medida` WRITE;
/*!40000 ALTER TABLE `tbl_unidades_medida` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_unidades_medida` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tbl_usuarios`
--

DROP TABLE IF EXISTS `tbl_usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_usuarios` (
  `id_usuario` int NOT NULL AUTO_INCREMENT,
  `chr_login` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `chr_password` varchar(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `chr_salt` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `int_status` int NOT NULL,
  `bit_activo` tinyint(1) NOT NULL DEFAULT '1',
  `int_rol` int DEFAULT NULL,
  `id_cliente` int NOT NULL,
  PRIMARY KEY (`id_usuario`),
  KEY `index2` (`id_cliente`),
  CONSTRAINT `fk_tbl_usuarios_1` FOREIGN KEY (`id_cliente`) REFERENCES `tbl_clientes` (`id_cliente`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbl_usuarios`
--

LOCK TABLES `tbl_usuarios` WRITE;
/*!40000 ALTER TABLE `tbl_usuarios` DISABLE KEYS */;
INSERT INTO `tbl_usuarios` VALUES (1,'testuser','e5d21750f53a936ad6d6a4ac58ebd173238d29c06e82edc64b265deef5fb0de7','375d194d81f6f761e03aed35441abc70d3ec5c446bc84d3fa04b302febfd1c29',1,1,NULL,1);
/*!40000 ALTER TABLE `tbl_usuarios` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-04-25  1:17:16
