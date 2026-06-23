-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: 127.0.0.1    Database: sisintupt
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `administrativo`
--

DROP TABLE IF EXISTS `administrativo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `administrativo` (
  `IdAdministrativo` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `IdUsuario` int(10) unsigned NOT NULL,
  `Escuela` int(10) unsigned DEFAULT NULL,
  `Turno` enum('Mañana','Tarde','Noche','Completo') NOT NULL DEFAULT 'Completo',
  `Extension` varchar(10) DEFAULT NULL,
  `FechaIncorporacion` date NOT NULL,
  PRIMARY KEY (`IdAdministrativo`),
  UNIQUE KEY `administrativo_idusuario_unique` (`IdUsuario`),
  KEY `administrativo_escuela_foreign` (`Escuela`),
  CONSTRAINT `administrativo_escuela_foreign` FOREIGN KEY (`Escuela`) REFERENCES `escuela` (`IdEscuela`),
  CONSTRAINT `administrativo_idusuario_foreign` FOREIGN KEY (`IdUsuario`) REFERENCES `usuario` (`IdUsuario`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `administrativo`
--

LOCK TABLES `administrativo` WRITE;
/*!40000 ALTER TABLE `administrativo` DISABLE KEYS */;
INSERT INTO `administrativo` VALUES (1,15,NULL,'Completo','4440','2025-11-19'),(2,19,2,'Tarde','4455','2025-11-12'),(3,20,NULL,'Completo','44405','2025-11-26');
/*!40000 ALTER TABLE `administrativo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `auditoriareserva`
--

DROP TABLE IF EXISTS `auditoriareserva`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `auditoriareserva` (
  `IdAudit` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `IdReserva` int(10) unsigned NOT NULL,
  `EstadoAnterior` varchar(50) NOT NULL,
  `EstadoNuevo` varchar(50) NOT NULL,
  `FechaCambio` datetime NOT NULL DEFAULT current_timestamp(),
  `UsuarioCambio` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`IdAudit`),
  KEY `auditoriareserva_idreserva_foreign` (`IdReserva`),
  KEY `auditoriareserva_usuariocambio_foreign` (`UsuarioCambio`),
  CONSTRAINT `auditoriareserva_idreserva_foreign` FOREIGN KEY (`IdReserva`) REFERENCES `reserva` (`IdReserva`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `auditoriareserva_usuariocambio_foreign` FOREIGN KEY (`UsuarioCambio`) REFERENCES `usuario` (`IdUsuario`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `auditoriareserva`
--

LOCK TABLES `auditoriareserva` WRITE;
/*!40000 ALTER TABLE `auditoriareserva` DISABLE KEYS */;
/*!40000 ALTER TABLE `auditoriareserva` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bloqueshorarios`
--

DROP TABLE IF EXISTS `bloqueshorarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bloqueshorarios` (
  `IdBloque` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Orden` int(11) NOT NULL,
  `Nombre` varchar(50) NOT NULL,
  `HoraInicio` time NOT NULL,
  `HoraFinal` time NOT NULL,
  PRIMARY KEY (`IdBloque`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bloqueshorarios`
--

LOCK TABLES `bloqueshorarios` WRITE;
/*!40000 ALTER TABLE `bloqueshorarios` DISABLE KEYS */;
INSERT INTO `bloqueshorarios` VALUES (1,1,'B1','08:00:00','08:50:00'),(2,2,'B2','08:50:00','09:40:00'),(3,3,'B3','09:40:00','10:30:00'),(4,4,'B4','10:30:00','11:20:00'),(5,5,'B5','11:20:00','12:10:00'),(6,6,'B6','12:10:00','13:00:00'),(7,7,'B7','13:00:00','13:50:00'),(8,8,'B8','13:50:00','14:10:00'),(9,9,'B9','14:10:00','15:00:00'),(10,10,'B10','15:00:00','15:50:00'),(11,11,'B11','15:50:00','16:40:00'),(12,12,'B12','16:40:00','17:30:00'),(13,13,'B13','17:30:00','18:20:00'),(14,14,'B14','18:20:00','19:10:00'),(15,15,'B15','19:10:00','20:00:00'),(16,16,'B16','20:00:00','20:50:00'),(17,17,'B17','20:50:00','21:40:00');
/*!40000 ALTER TABLE `bloqueshorarios` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_crear_horarios AFTER INSERT ON bloqueshorarios FOR EACH ROW 
            BEGIN
                INSERT INTO horarios (espacio, bloque, diaSemana, ocupado)
                SELECT e.IdEspacio, NEW.IdBloque, d.dia, 0
                FROM espacio e
                CROSS JOIN (
                    SELECT 'Lunes' AS dia
                    UNION ALL SELECT 'Martes'
                    UNION ALL SELECT 'Miercoles'
                    UNION ALL SELECT 'Jueves'
                    UNION ALL SELECT 'Viernes'
                    UNION ALL SELECT 'Sabado'
                ) d;
            END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_eliminar_horarios_bloque AFTER DELETE ON bloqueshorarios FOR EACH ROW 
            BEGIN
                DELETE FROM horarios 
                WHERE bloque = OLD.IdBloque;
            END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
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
-- Table structure for table `cursos`
--

DROP TABLE IF EXISTS `cursos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cursos` (
  `IdCurso` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(100) NOT NULL,
  `Facultad` int(10) unsigned NOT NULL,
  `Escuela` int(10) unsigned NOT NULL,
  `Ciclo` varchar(5) NOT NULL,
  `Estado` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`IdCurso`),
  KEY `cursos_facultad_foreign` (`Facultad`),
  KEY `cursos_escuela_foreign` (`Escuela`),
  CONSTRAINT `cursos_escuela_foreign` FOREIGN KEY (`Escuela`) REFERENCES `escuela` (`IdEscuela`),
  CONSTRAINT `cursos_facultad_foreign` FOREIGN KEY (`Facultad`) REFERENCES `facultad` (`IdFacultad`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cursos`
--

LOCK TABLES `cursos` WRITE;
/*!40000 ALTER TABLE `cursos` DISABLE KEYS */;
INSERT INTO `cursos` VALUES (1,'POGRAMACION',1,2,'5',1),(2,'CIVIL',1,1,'2',1);
/*!40000 ALTER TABLE `cursos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `docente`
--

DROP TABLE IF EXISTS `docente`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `docente` (
  `IdDocente` bigint(20) NOT NULL,
  `IdUsuario` int(10) unsigned NOT NULL,
  `CodigoDocente` varchar(255) NOT NULL,
  `Escuela` int(10) unsigned DEFAULT NULL,
  `TipoContrato` enum('Tiempo Completo','Medio Tiempo','Contratado') NOT NULL,
  `Especialidad` varchar(100) DEFAULT NULL,
  `FechaIncorporacion` date NOT NULL,
  PRIMARY KEY (`IdDocente`),
  UNIQUE KEY `docente_idusuario_unique` (`IdUsuario`),
  UNIQUE KEY `docente_codigodocente_unique` (`CodigoDocente`),
  KEY `docente_escuela_foreign` (`Escuela`),
  CONSTRAINT `docente_escuela_foreign` FOREIGN KEY (`Escuela`) REFERENCES `escuela` (`IdEscuela`),
  CONSTRAINT `docente_idusuario_foreign` FOREIGN KEY (`IdUsuario`) REFERENCES `usuario` (`IdUsuario`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `docente`
--

LOCK TABLES `docente` WRITE;
/*!40000 ALTER TABLE `docente` DISABLE KEYS */;
INSERT INTO `docente` VALUES (1,16,'202307',2,'Tiempo Completo','nose','2025-11-09');
/*!40000 ALTER TABLE `docente` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `escuela`
--

DROP TABLE IF EXISTS `escuela`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `escuela` (
  `IdEscuela` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `IdFacultad` int(10) unsigned NOT NULL,
  `Nombre` varchar(255) NOT NULL,
  PRIMARY KEY (`IdEscuela`),
  KEY `escuela_idfacultad_foreign` (`IdFacultad`),
  CONSTRAINT `escuela_idfacultad_foreign` FOREIGN KEY (`IdFacultad`) REFERENCES `facultad` (`IdFacultad`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `escuela`
--

LOCK TABLES `escuela` WRITE;
/*!40000 ALTER TABLE `escuela` DISABLE KEYS */;
INSERT INTO `escuela` VALUES (1,1,'Ing. Civil'),(2,1,'Ing. de Sistemas'),(3,1,'Ing. Electronica'),(4,1,'Ing. Agroindustrial'),(5,1,'Ing. Ambiental'),(6,1,'Ing. Industrial'),(7,2,'Derecho'),(8,3,'Ciencias Contables y Financieras'),(9,3,'Economia y Microfinanzas'),(10,3,'Administracion'),(11,3,'Administracion Turistico-Hotel'),(12,3,'Administracion de Negocios Internacionales'),(13,4,'Educacion'),(14,4,'Ciencias de la Comunicacion'),(15,4,'Humanidades - Psicologia'),(16,5,'Medicina Humana'),(17,5,'Odontologia'),(18,5,'Tecnologia Medica'),(19,6,'Arquitectira');
/*!40000 ALTER TABLE `escuela` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `espacio`
--

DROP TABLE IF EXISTS `espacio`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `espacio` (
  `IdEspacio` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Codigo` varchar(20) NOT NULL,
  `Nombre` varchar(100) NOT NULL,
  `Tipo` enum('Laboratorio','Salon') NOT NULL DEFAULT 'Laboratorio',
  `Capacidad` int(11) NOT NULL,
  `Equipamiento` text DEFAULT NULL,
  `Escuela` int(10) unsigned NOT NULL,
  `Estado` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`IdEspacio`),
  UNIQUE KEY `espacio_codigo_unique` (`Codigo`),
  KEY `espacio_escuela_foreign` (`Escuela`),
  CONSTRAINT `espacio_escuela_foreign` FOREIGN KEY (`Escuela`) REFERENCES `escuela` (`IdEscuela`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `espacio`
--

LOCK TABLES `espacio` WRITE;
/*!40000 ALTER TABLE `espacio` DISABLE KEYS */;
INSERT INTO `espacio` VALUES (1,'P-302','LAB 02','Laboratorio',40,'1',2,1),(2,'S-630','LAB 15','Laboratorio',20,'A',1,1),(9,'P-035','G','Laboratorio',20,'Computadoras',2,1);
/*!40000 ALTER TABLE `espacio` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_crear_horarios_espacios AFTER INSERT ON espacio FOR EACH ROW 
            BEGIN
                INSERT INTO horarios (espacio, bloque, diaSemana, ocupado)
                SELECT NEW.IdEspacio, b.IdBloque, d.dia, 0
                FROM bloqueshorarios b
                CROSS JOIN (
                    SELECT 'Lunes' AS dia UNION SELECT 'Martes' UNION SELECT 'Miercoles'
                    UNION SELECT 'Jueves' UNION SELECT 'Viernes' UNION SELECT 'Sabado'
                ) d;
            END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_eliminar_horarios_espacio AFTER DELETE ON espacio FOR EACH ROW 
            BEGIN
                DELETE FROM horarios 
                WHERE espacio = OLD.IdEspacio;
            END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `estudiante`
--

DROP TABLE IF EXISTS `estudiante`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `estudiante` (
  `IdEstudiante` bigint(20) NOT NULL,
  `IdUsuario` int(10) unsigned NOT NULL,
  `Codigo` varchar(255) NOT NULL,
  `Escuela` int(10) unsigned NOT NULL,
  PRIMARY KEY (`IdEstudiante`),
  UNIQUE KEY `estudiante_idusuario_unique` (`IdUsuario`),
  UNIQUE KEY `estudiante_codigo_unique` (`Codigo`),
  KEY `estudiante_escuela_foreign` (`Escuela`),
  CONSTRAINT `estudiante_escuela_foreign` FOREIGN KEY (`Escuela`) REFERENCES `escuela` (`IdEscuela`),
  CONSTRAINT `estudiante_idusuario_foreign` FOREIGN KEY (`IdUsuario`) REFERENCES `usuario` (`IdUsuario`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `estudiante`
--

LOCK TABLES `estudiante` WRITE;
/*!40000 ALTER TABLE `estudiante` DISABLE KEYS */;
INSERT INTO `estudiante` VALUES (1,14,'2023088',2),(2,18,'2023076802',2),(3,19,'223075555',1);
/*!40000 ALTER TABLE `estudiante` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `facultad`
--

DROP TABLE IF EXISTS `facultad`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `facultad` (
  `IdFacultad` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(255) NOT NULL,
  `Abreviatura` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`IdFacultad`),
  UNIQUE KEY `facultad_nombre_unique` (`Nombre`),
  UNIQUE KEY `facultad_abreviatura_unique` (`Abreviatura`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `facultad`
--

LOCK TABLES `facultad` WRITE;
/*!40000 ALTER TABLE `facultad` DISABLE KEYS */;
INSERT INTO `facultad` VALUES (1,'Facultad de Ingeniería','FAING'),(2,'Facultad de Derecho y Ciencias Políticas','FADE'),(3,'Facultad de Ciencias Empresariales','FACEM'),(4,'Facultad de Educación, Ciencias de la Comunicación','FAEDCOH'),(5,'Facultad de Ciencias De la Salud','FACSA'),(6,'Facultad de Arquitectura y Urbanismo','FAU');
/*!40000 ALTER TABLE `facultad` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `horario_curso`
--

DROP TABLE IF EXISTS `horario_curso`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `horario_curso` (
  `IdHorarioCurso` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Curso` int(10) unsigned NOT NULL,
  `Docente` int(10) unsigned NOT NULL,
  `Espacio` int(10) unsigned NOT NULL,
  `Bloque` int(10) unsigned NOT NULL,
  `DiaSemana` enum('Lunes','Martes','Miercoles','Jueves','Viernes','Sabado') NOT NULL,
  `FechaInicio` date NOT NULL,
  `FechaFin` date NOT NULL,
  `Estado` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`IdHorarioCurso`),
  KEY `horario_curso_curso_foreign` (`Curso`),
  KEY `horario_curso_docente_foreign` (`Docente`),
  KEY `horario_curso_espacio_foreign` (`Espacio`),
  KEY `horario_curso_bloque_foreign` (`Bloque`),
  CONSTRAINT `horario_curso_bloque_foreign` FOREIGN KEY (`Bloque`) REFERENCES `bloqueshorarios` (`IdBloque`),
  CONSTRAINT `horario_curso_curso_foreign` FOREIGN KEY (`Curso`) REFERENCES `cursos` (`IdCurso`),
  CONSTRAINT `horario_curso_docente_foreign` FOREIGN KEY (`Docente`) REFERENCES `usuario` (`IdUsuario`),
  CONSTRAINT `horario_curso_espacio_foreign` FOREIGN KEY (`Espacio`) REFERENCES `espacio` (`IdEspacio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `horario_curso`
--

LOCK TABLES `horario_curso` WRITE;
/*!40000 ALTER TABLE `horario_curso` DISABLE KEYS */;
/*!40000 ALTER TABLE `horario_curso` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_bloquear_horario_curso_insert AFTER INSERT ON horario_curso FOR EACH ROW 
            BEGIN
                IF CURDATE() BETWEEN NEW.FechaInicio AND NEW.FechaFin AND NEW.Estado = 1 THEN
                    UPDATE horarios 
                    SET ocupado = 1 
                    WHERE espacio = NEW.Espacio 
                      AND bloque = NEW.Bloque 
                      AND diaSemana = NEW.DiaSemana;
                END IF;
            END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_bloquear_horario_curso_update AFTER UPDATE ON horario_curso FOR EACH ROW 
            BEGIN
                IF (OLD.Estado = 1 AND NEW.Estado = 0) OR 
                   (OLD.Estado = 1 AND (NEW.Espacio != OLD.Espacio OR NEW.Bloque != OLD.Bloque OR NEW.DiaSemana != OLD.DiaSemana)) OR
                   (OLD.Estado = 1 AND CURDATE() NOT BETWEEN NEW.FechaInicio AND NEW.FechaFin) THEN
                    
                    UPDATE horarios 
                    SET ocupado = 0 
                    WHERE espacio = OLD.Espacio 
                      AND bloque = OLD.Bloque 
                      AND diaSemana = OLD.DiaSemana;
                END IF;
                
                IF NEW.Estado = 1 AND CURDATE() BETWEEN NEW.FechaInicio AND NEW.FechaFin THEN
                    UPDATE horarios 
                    SET ocupado = 1 
                    WHERE espacio = NEW.Espacio 
                      AND bloque = NEW.Bloque 
                      AND diaSemana = NEW.DiaSemana;
                END IF;
            END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_liberar_horario_curso_delete AFTER DELETE ON horario_curso FOR EACH ROW 
            BEGIN
                UPDATE horarios 
                SET ocupado = 0 
                WHERE espacio = OLD.Espacio 
                  AND bloque = OLD.Bloque 
                  AND diaSemana = OLD.DiaSemana;
            END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `horarios`
--

DROP TABLE IF EXISTS `horarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `horarios` (
  `IdHorario` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `espacio` int(10) unsigned NOT NULL,
  `bloque` int(10) unsigned NOT NULL,
  `diaSemana` enum('Lunes','Martes','Miercoles','Jueves','Viernes','Sabado') NOT NULL,
  `ocupado` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`IdHorario`),
  KEY `horarios_espacio_foreign` (`espacio`),
  KEY `horarios_bloque_foreign` (`bloque`),
  CONSTRAINT `horarios_bloque_foreign` FOREIGN KEY (`bloque`) REFERENCES `bloqueshorarios` (`IdBloque`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `horarios_espacio_foreign` FOREIGN KEY (`espacio`) REFERENCES `espacio` (`IdEspacio`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=382 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `horarios`
--

LOCK TABLES `horarios` WRITE;
/*!40000 ALTER TABLE `horarios` DISABLE KEYS */;
INSERT INTO `horarios` VALUES (1,1,1,'Lunes',1),(2,1,1,'Martes',0),(3,1,1,'Miercoles',0),(4,1,1,'Jueves',0),(5,1,1,'Viernes',0),(6,1,1,'Sabado',0),(7,1,2,'Lunes',0),(8,1,2,'Martes',0),(9,1,2,'Miercoles',0),(10,1,2,'Jueves',0),(11,1,2,'Viernes',0),(12,1,2,'Sabado',0),(13,1,3,'Lunes',0),(14,1,3,'Martes',0),(15,1,3,'Miercoles',0),(16,1,3,'Jueves',0),(17,1,3,'Viernes',0),(18,1,3,'Sabado',0),(19,1,4,'Lunes',0),(20,1,4,'Martes',0),(21,1,4,'Miercoles',0),(22,1,4,'Jueves',0),(23,1,4,'Viernes',0),(24,1,4,'Sabado',0),(25,1,5,'Lunes',0),(26,1,5,'Martes',0),(27,1,5,'Miercoles',0),(28,1,5,'Jueves',0),(29,1,5,'Viernes',0),(30,1,5,'Sabado',0),(31,1,6,'Lunes',0),(32,1,6,'Martes',0),(33,1,6,'Miercoles',0),(34,1,6,'Jueves',0),(35,1,6,'Viernes',0),(36,1,6,'Sabado',0),(37,1,7,'Lunes',0),(38,1,7,'Martes',0),(39,1,7,'Miercoles',0),(40,1,7,'Jueves',0),(41,1,7,'Viernes',0),(42,1,7,'Sabado',0),(43,1,8,'Lunes',0),(44,1,8,'Martes',0),(45,1,8,'Miercoles',0),(46,1,8,'Jueves',0),(47,1,8,'Viernes',0),(48,1,8,'Sabado',0),(49,1,9,'Lunes',0),(50,1,9,'Martes',0),(51,1,9,'Miercoles',0),(52,1,9,'Jueves',0),(53,1,9,'Viernes',0),(54,1,9,'Sabado',0),(55,1,10,'Lunes',0),(56,1,10,'Martes',0),(57,1,10,'Miercoles',0),(58,1,10,'Jueves',0),(59,1,10,'Viernes',0),(60,1,10,'Sabado',0),(61,1,11,'Lunes',0),(62,1,11,'Martes',0),(63,1,11,'Miercoles',0),(64,1,11,'Jueves',0),(65,1,11,'Viernes',0),(66,1,11,'Sabado',0),(67,1,12,'Lunes',0),(68,1,12,'Martes',0),(69,1,12,'Miercoles',0),(70,1,12,'Jueves',0),(71,1,12,'Viernes',0),(72,1,12,'Sabado',0),(73,1,13,'Lunes',0),(74,1,13,'Martes',0),(75,1,13,'Miercoles',0),(76,1,13,'Jueves',0),(77,1,13,'Viernes',0),(78,1,13,'Sabado',0),(79,1,14,'Lunes',0),(80,1,14,'Martes',0),(81,1,14,'Miercoles',0),(82,1,14,'Jueves',0),(83,1,14,'Viernes',0),(84,1,14,'Sabado',0),(85,1,15,'Lunes',0),(86,1,15,'Martes',0),(87,1,15,'Miercoles',0),(88,1,15,'Jueves',0),(89,1,15,'Viernes',0),(90,1,15,'Sabado',0),(91,1,16,'Lunes',0),(92,1,16,'Martes',0),(93,1,16,'Miercoles',0),(94,1,16,'Jueves',0),(95,1,16,'Viernes',0),(96,1,16,'Sabado',0),(97,1,17,'Lunes',0),(98,1,17,'Martes',0),(99,1,17,'Miercoles',0),(100,1,17,'Jueves',0),(101,1,17,'Viernes',0),(102,1,17,'Sabado',0),(128,2,1,'Lunes',0),(129,2,1,'Martes',0),(130,2,1,'Miercoles',0),(131,2,1,'Jueves',0),(132,2,1,'Viernes',0),(133,2,1,'Sabado',0),(134,2,2,'Lunes',0),(135,2,2,'Martes',0),(136,2,2,'Miercoles',0),(137,2,2,'Jueves',0),(138,2,2,'Viernes',0),(139,2,2,'Sabado',0),(140,2,3,'Lunes',0),(141,2,3,'Martes',0),(142,2,3,'Miercoles',0),(143,2,3,'Jueves',0),(144,2,3,'Viernes',0),(145,2,3,'Sabado',0),(146,2,4,'Lunes',0),(147,2,4,'Martes',0),(148,2,4,'Miercoles',0),(149,2,4,'Jueves',0),(150,2,4,'Viernes',0),(151,2,4,'Sabado',0),(152,2,5,'Lunes',0),(153,2,5,'Martes',0),(154,2,5,'Miercoles',0),(155,2,5,'Jueves',0),(156,2,5,'Viernes',0),(157,2,5,'Sabado',0),(158,2,6,'Lunes',0),(159,2,6,'Martes',0),(160,2,6,'Miercoles',0),(161,2,6,'Jueves',0),(162,2,6,'Viernes',0),(163,2,6,'Sabado',0),(164,2,7,'Lunes',0),(165,2,7,'Martes',0),(166,2,7,'Miercoles',0),(167,2,7,'Jueves',0),(168,2,7,'Viernes',0),(169,2,7,'Sabado',0),(170,2,8,'Lunes',0),(171,2,8,'Martes',0),(172,2,8,'Miercoles',0),(173,2,8,'Jueves',0),(174,2,8,'Viernes',0),(175,2,8,'Sabado',0),(176,2,9,'Lunes',0),(177,2,9,'Martes',0),(178,2,9,'Miercoles',0),(179,2,9,'Jueves',0),(180,2,9,'Viernes',0),(181,2,9,'Sabado',0),(182,2,10,'Lunes',0),(183,2,10,'Martes',0),(184,2,10,'Miercoles',0),(185,2,10,'Jueves',0),(186,2,10,'Viernes',0),(187,2,10,'Sabado',0),(188,2,11,'Lunes',0),(189,2,11,'Martes',0),(190,2,11,'Miercoles',0),(191,2,11,'Jueves',0),(192,2,11,'Viernes',0),(193,2,11,'Sabado',0),(194,2,12,'Lunes',0),(195,2,12,'Martes',0),(196,2,12,'Miercoles',0),(197,2,12,'Jueves',0),(198,2,12,'Viernes',0),(199,2,12,'Sabado',0),(200,2,13,'Lunes',0),(201,2,13,'Martes',0),(202,2,13,'Miercoles',0),(203,2,13,'Jueves',0),(204,2,13,'Viernes',0),(205,2,13,'Sabado',0),(206,2,14,'Lunes',0),(207,2,14,'Martes',0),(208,2,14,'Miercoles',0),(209,2,14,'Jueves',0),(210,2,14,'Viernes',0),(211,2,14,'Sabado',0),(212,2,15,'Lunes',0),(213,2,15,'Martes',0),(214,2,15,'Miercoles',0),(215,2,15,'Jueves',0),(216,2,15,'Viernes',0),(217,2,15,'Sabado',0),(218,2,16,'Lunes',0),(219,2,16,'Martes',0),(220,2,16,'Miercoles',0),(221,2,16,'Jueves',0),(222,2,16,'Viernes',0),(223,2,16,'Sabado',0),(224,2,17,'Lunes',0),(225,2,17,'Martes',0),(226,2,17,'Miercoles',0),(227,2,17,'Jueves',0),(228,2,17,'Viernes',0),(229,2,17,'Sabado',0),(255,9,1,'Lunes',0),(256,9,1,'Martes',0),(257,9,1,'Miercoles',0),(258,9,1,'Jueves',0),(259,9,1,'Viernes',0),(260,9,1,'Sabado',0),(261,9,2,'Lunes',0),(262,9,2,'Martes',0),(263,9,2,'Miercoles',0),(264,9,2,'Jueves',0),(265,9,2,'Viernes',0),(266,9,2,'Sabado',0),(267,9,3,'Lunes',0),(268,9,3,'Martes',0),(269,9,3,'Miercoles',0),(270,9,3,'Jueves',0),(271,9,3,'Viernes',0),(272,9,3,'Sabado',0),(273,9,4,'Lunes',0),(274,9,4,'Martes',0),(275,9,4,'Miercoles',0),(276,9,4,'Jueves',0),(277,9,4,'Viernes',0),(278,9,4,'Sabado',0),(279,9,5,'Lunes',0),(280,9,5,'Martes',0),(281,9,5,'Miercoles',0),(282,9,5,'Jueves',0),(283,9,5,'Viernes',0),(284,9,5,'Sabado',0),(285,9,6,'Lunes',0),(286,9,6,'Martes',0),(287,9,6,'Miercoles',0),(288,9,6,'Jueves',0),(289,9,6,'Viernes',0),(290,9,6,'Sabado',0),(291,9,7,'Lunes',0),(292,9,7,'Martes',0),(293,9,7,'Miercoles',0),(294,9,7,'Jueves',0),(295,9,7,'Viernes',0),(296,9,7,'Sabado',0),(297,9,8,'Lunes',0),(298,9,8,'Martes',0),(299,9,8,'Miercoles',0),(300,9,8,'Jueves',0),(301,9,8,'Viernes',0),(302,9,8,'Sabado',0),(303,9,9,'Lunes',0),(304,9,9,'Martes',0),(305,9,9,'Miercoles',0),(306,9,9,'Jueves',0),(307,9,9,'Viernes',0),(308,9,9,'Sabado',0),(309,9,10,'Lunes',0),(310,9,10,'Martes',0),(311,9,10,'Miercoles',0),(312,9,10,'Jueves',0),(313,9,10,'Viernes',0),(314,9,10,'Sabado',0),(315,9,11,'Lunes',0),(316,9,11,'Martes',0),(317,9,11,'Miercoles',0),(318,9,11,'Jueves',0),(319,9,11,'Viernes',0),(320,9,11,'Sabado',0),(321,9,12,'Lunes',0),(322,9,12,'Martes',0),(323,9,12,'Miercoles',0),(324,9,12,'Jueves',0),(325,9,12,'Viernes',0),(326,9,12,'Sabado',0),(327,9,13,'Lunes',0),(328,9,13,'Martes',0),(329,9,13,'Miercoles',0),(330,9,13,'Jueves',0),(331,9,13,'Viernes',0),(332,9,13,'Sabado',0),(333,9,14,'Lunes',0),(334,9,14,'Martes',0),(335,9,14,'Miercoles',0),(336,9,14,'Jueves',0),(337,9,14,'Viernes',0),(338,9,14,'Sabado',0),(339,9,15,'Lunes',0),(340,9,15,'Martes',0),(341,9,15,'Miercoles',0),(342,9,15,'Jueves',0),(343,9,15,'Viernes',0),(344,9,15,'Sabado',0),(345,9,16,'Lunes',0),(346,9,16,'Martes',0),(347,9,16,'Miercoles',0),(348,9,16,'Jueves',0),(349,9,16,'Viernes',0),(350,9,16,'Sabado',0),(351,9,17,'Lunes',0),(352,9,17,'Martes',0),(353,9,17,'Miercoles',0),(354,9,17,'Jueves',0),(355,9,17,'Viernes',0),(356,9,17,'Sabado',0);
/*!40000 ALTER TABLE `horarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `incidencia`
--

DROP TABLE IF EXISTS `incidencia`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `incidencia` (
  `IdIncidencia` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Reserva` int(10) unsigned NOT NULL,
  `Descripcion` text NOT NULL,
  `FechaReporte` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`IdIncidencia`),
  KEY `incidencia_reserva_foreign` (`Reserva`),
  CONSTRAINT `incidencia_reserva_foreign` FOREIGN KEY (`Reserva`) REFERENCES `reserva` (`IdReserva`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `incidencia`
--

LOCK TABLES `incidencia` WRITE;
/*!40000 ALTER TABLE `incidencia` DISABLE KEYS */;
/*!40000 ALTER TABLE `incidencia` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2025_01_01_000001_create_rol_table',1),(5,'2025_01_01_000002_create_tipodocumento_table',1),(6,'2025_01_01_000003_create_facultad_table',1),(7,'2025_01_01_000004_create_escuela_table',1),(8,'2025_01_01_000005_create_usuario_table',1),(9,'2025_01_01_000006_create_usuario_auth_table',1),(10,'2025_01_01_000007_create_usuario_sesion_table',1),(11,'2025_01_01_000008_create_espacio_table',1),(12,'2025_01_01_000009_create_bloqueshorarios_table',1),(13,'2025_01_01_000010_create_horarios_table',1),(14,'2025_01_01_000011_create_cursos_table',1),(15,'2025_01_01_000012_create_horario_curso_table',1),(16,'2025_01_01_000013_create_reserva_table',1),(17,'2025_01_01_000014_create_reserva_gestion_table',1),(18,'2025_01_01_000015_create_auditoriareserva_table',1),(19,'2025_01_01_000016_create_incidencia_table',1),(20,'2025_01_01_000017_create_sancion_table',1),(21,'2025_01_01_000018_create_docente_table',1),(22,'2025_01_01_000019_create_estudiante_table',1),(23,'2025_01_01_000020_create_administrativo_table',1),(24,'2025_01_01_000021_create_triggers_procedures_events',1),(25,'2025_01_01_000023_fix_horario_trigger_collation',2);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reserva`
--

DROP TABLE IF EXISTS `reserva`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reserva` (
  `IdReserva` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `usuario` int(10) unsigned NOT NULL,
  `espacio` int(10) unsigned NOT NULL,
  `bloque` int(10) unsigned NOT NULL,
  `curso` int(10) unsigned NOT NULL,
  `fechaReserva` date NOT NULL,
  `fechaSolicitud` datetime NOT NULL DEFAULT current_timestamp(),
  `DescripcionUso` text DEFAULT NULL,
  `CantidadEstudiantes` int(11) NOT NULL DEFAULT 1,
  `Estado` enum('Pendiente','Aprobada','Rechazada','Cancelada') NOT NULL DEFAULT 'Pendiente',
  PRIMARY KEY (`IdReserva`),
  KEY `reserva_usuario_foreign` (`usuario`),
  KEY `reserva_espacio_foreign` (`espacio`),
  KEY `reserva_bloque_foreign` (`bloque`),
  KEY `reserva_curso_foreign` (`curso`),
  CONSTRAINT `reserva_bloque_foreign` FOREIGN KEY (`bloque`) REFERENCES `bloqueshorarios` (`IdBloque`),
  CONSTRAINT `reserva_curso_foreign` FOREIGN KEY (`curso`) REFERENCES `cursos` (`IdCurso`),
  CONSTRAINT `reserva_espacio_foreign` FOREIGN KEY (`espacio`) REFERENCES `espacio` (`IdEspacio`),
  CONSTRAINT `reserva_usuario_foreign` FOREIGN KEY (`usuario`) REFERENCES `usuario` (`IdUsuario`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reserva`
--

LOCK TABLES `reserva` WRITE;
/*!40000 ALTER TABLE `reserva` DISABLE KEYS */;
/*!40000 ALTER TABLE `reserva` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_verificar_sanciones_activas BEFORE INSERT ON reserva FOR EACH ROW 
            BEGIN
                DECLARE sancion_activa INT DEFAULT 0;
                
                SELECT COUNT(*) INTO sancion_activa
                FROM sancion 
                WHERE Usuario = NEW.usuario 
                AND Estado = 'Activa'
                AND CURDATE() BETWEEN FechaInicio AND FechaFin;
                
                IF sancion_activa > 0 THEN
                    SIGNAL SQLSTATE '45000' 
                    SET MESSAGE_TEXT = 'El usuario tiene una sanción activa y no puede realizar reservas';
                END IF;
            END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_auditoria_reserva AFTER UPDATE ON reserva FOR EACH ROW 
            BEGIN
                DECLARE usuario_gestion INT;
                
                IF OLD.Estado <> NEW.Estado THEN
                    SELECT rg.UsuarioGestion INTO usuario_gestion
                    FROM reserva_gestion rg
                    WHERE rg.IdReserva = NEW.IdReserva
                    ORDER BY rg.FechaGestion DESC
                    LIMIT 1;
                    
                    SET usuario_gestion = COALESCE(usuario_gestion, NEW.usuario);
                    
                    INSERT INTO auditoriareserva (IdReserva, EstadoAnterior, EstadoNuevo, UsuarioCambio)
                    VALUES (NEW.IdReserva, OLD.Estado, NEW.Estado, usuario_gestion);
                END IF;
            END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_actualizar_horario_update AFTER UPDATE ON reserva FOR EACH ROW
            BEGIN
                DECLARE dia ENUM('Lunes','Martes','Miercoles','Jueves','Viernes','Sabado') COLLATE utf8mb4_unicode_ci;

                SET dia = CASE DAYOFWEEK(NEW.fechaReserva)
                    WHEN 2 THEN 'Lunes'
                    WHEN 3 THEN 'Martes'
                    WHEN 4 THEN 'Miercoles'
                    WHEN 5 THEN 'Jueves'
                    WHEN 6 THEN 'Viernes'
                    WHEN 7 THEN 'Sabado'
                    ELSE 'Lunes'
                END;

                IF NEW.Estado = 'Aprobada' THEN
                    UPDATE horarios h
                    SET h.ocupado = 1
                    WHERE h.espacio = NEW.espacio
                      AND h.bloque = NEW.bloque
                      AND h.diaSemana = dia;
                ELSEIF NEW.Estado IN ('Rechazada','Cancelada') AND OLD.Estado = 'Aprobada' THEN
                    UPDATE horarios h
                    SET h.ocupado = 0
                    WHERE h.espacio = NEW.espacio
                      AND h.bloque = NEW.bloque
                      AND h.diaSemana = dia;
                END IF;
            END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `reserva_gestion`
--

DROP TABLE IF EXISTS `reserva_gestion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reserva_gestion` (
  `IdGestion` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `IdReserva` int(10) unsigned NOT NULL,
  `UsuarioGestion` int(10) unsigned NOT NULL COMMENT 'Admin que gestiona',
  `FechaGestion` datetime NOT NULL DEFAULT current_timestamp(),
  `Accion` enum('Aprobar','Rechazar') NOT NULL,
  `Motivo` text NOT NULL COMMENT 'Motivo de la acción',
  `Comentarios` text DEFAULT NULL COMMENT 'Comentarios adicionales',
  PRIMARY KEY (`IdGestion`),
  KEY `reserva_gestion_idreserva_foreign` (`IdReserva`),
  KEY `reserva_gestion_usuariogestion_foreign` (`UsuarioGestion`),
  CONSTRAINT `reserva_gestion_idreserva_foreign` FOREIGN KEY (`IdReserva`) REFERENCES `reserva` (`IdReserva`) ON DELETE CASCADE,
  CONSTRAINT `reserva_gestion_usuariogestion_foreign` FOREIGN KEY (`UsuarioGestion`) REFERENCES `usuario` (`IdUsuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reserva_gestion`
--

LOCK TABLES `reserva_gestion` WRITE;
/*!40000 ALTER TABLE `reserva_gestion` DISABLE KEYS */;
/*!40000 ALTER TABLE `reserva_gestion` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_sincronizar_estado_gestion AFTER INSERT ON reserva_gestion FOR EACH ROW 
            BEGIN
                DECLARE nuevo_estado VARCHAR(20);
                
                CASE NEW.Accion
                    WHEN 'Aprobar' THEN SET nuevo_estado = 'Aprobada';
                    WHEN 'Rechazar' THEN SET nuevo_estado = 'Rechazada';
                    ELSE SET nuevo_estado = 'Pendiente';
                END CASE;
                
                UPDATE reserva 
                SET Estado = nuevo_estado 
                WHERE IdReserva = NEW.IdReserva;
            END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `rol`
--

DROP TABLE IF EXISTS `rol`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rol` (
  `IdRol` int(11) NOT NULL,
  `Nombre` varchar(15) NOT NULL,
  PRIMARY KEY (`IdRol`),
  UNIQUE KEY `rol_nombre_unique` (`Nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rol`
--

LOCK TABLES `rol` WRITE;
/*!40000 ALTER TABLE `rol` DISABLE KEYS */;
INSERT INTO `rol` VALUES (3,'Administrador'),(2,'Estudiante'),(1,'Profesor'),(4,'Supervisor');
/*!40000 ALTER TABLE `rol` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sancion`
--

DROP TABLE IF EXISTS `sancion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sancion` (
  `IdSancion` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `Usuario` int(10) unsigned NOT NULL,
  `Motivo` text NOT NULL,
  `FechaInicio` date NOT NULL,
  `FechaFin` date NOT NULL,
  `Estado` enum('ACTIVA','CUMPLIDA') NOT NULL,
  `TipoUsuario` enum('DOCENTE','ESTUDIANTE') NOT NULL,
  PRIMARY KEY (`IdSancion`),
  KEY `sancion_usuario_foreign` (`Usuario`),
  CONSTRAINT `sancion_usuario_foreign` FOREIGN KEY (`Usuario`) REFERENCES `usuario` (`IdUsuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sancion`
--

LOCK TABLES `sancion` WRITE;
/*!40000 ALTER TABLE `sancion` DISABLE KEYS */;
/*!40000 ALTER TABLE `sancion` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_actualizar_estado_sancion BEFORE UPDATE ON sancion FOR EACH ROW 
            BEGIN
                IF CURDATE() > NEW.FechaFin AND NEW.Estado = 'Activa' THEN
                    SET NEW.Estado = 'Cumplida';
                END IF;
            END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
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
INSERT INTO `sessions` VALUES ('FAKynEkxq53st4eyRo2oCEuBFFUNjdVf20CxOvvu',NULL,'127.0.0.1','curl/8.19.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiMEszd2ViQlFQT2t5ckNkUmd1dEpGdEFMc1M1aHQ4cGdMZWVrdjgyWSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NTM6Imh0dHA6Ly8xMjcuMC4wLjE6ODEyNC9yZXNlcnZhcy9mb3JtdWxhcmlvP2VzY3VlbGFJZD0yIjtzOjU6InJvdXRlIjtOO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1782193980),('rWO0iR5qKNzEKsxGAQdgnQARYp92sSqKUBTqz0tK',NULL,'127.0.0.1','curl/8.19.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiOUpoaTk4eGY4ZW81WEc2Z0pLWEIyTm90V3J6WEIzT2VNQVdaeTRqWiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODEyNCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1782193519),('z1BtsVrUY7Yh05BoLd68QfyHiz7syWWgvRYf1ehQ',NULL,'127.0.0.1','curl/8.19.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiUFZKUzdaQkdiY0F2ZHdwRGg0RU5XWm53WTVqRWtaanpEZ2hCdjQ1MiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzA6Imh0dHA6Ly8xMjcuMC4wLjE6ODEyNC9lc3BhY2lvcyI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1782193519);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tipodocumento`
--

DROP TABLE IF EXISTS `tipodocumento`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tipodocumento` (
  `IdTipoDoc` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(50) NOT NULL,
  `Abreviatura` varchar(10) NOT NULL,
  PRIMARY KEY (`IdTipoDoc`),
  UNIQUE KEY `tipodocumento_nombre_unique` (`Nombre`),
  UNIQUE KEY `tipodocumento_abreviatura_unique` (`Abreviatura`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tipodocumento`
--

LOCK TABLES `tipodocumento` WRITE;
/*!40000 ALTER TABLE `tipodocumento` DISABLE KEYS */;
INSERT INTO `tipodocumento` VALUES (1,'Documento Nacional de Identidad','DNI'),(2,'Carnet de Extranjería','CE'),(3,'Pasaporte','PAS'),(4,'Permiso Temporal de Permanencia','PTP'),(5,'Cédula de Identidad','CI'),(6,'Registro Único de Contribuyente','RUC'),(7,'Partida de Nacimiento','PN'),(8,'Carnet de Refugiado','CR'),(9,'Documento de Identidad Extranjero','DIE'),(10,'Licencia de Conducir','LIC'),(11,'Carnet Universitario','CU'),(12,'Otro','OTRO');
/*!40000 ALTER TABLE `tipodocumento` ENABLE KEYS */;
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
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuario`
--

DROP TABLE IF EXISTS `usuario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usuario` (
  `IdUsuario` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(255) NOT NULL,
  `Apellido` varchar(255) NOT NULL,
  `TipoDoc` int(10) unsigned NOT NULL,
  `NumDoc` varchar(255) DEFAULT NULL,
  `Rol` int(11) NOT NULL,
  `Celular` varchar(11) DEFAULT NULL,
  `Genero` tinyint(1) DEFAULT NULL,
  `Estado` int(11) NOT NULL DEFAULT 1,
  `FechaRegistro` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`IdUsuario`),
  UNIQUE KEY `usuario_numdoc_unique` (`NumDoc`),
  KEY `usuario_rol_foreign` (`Rol`),
  KEY `usuario_tipodoc_foreign` (`TipoDoc`),
  CONSTRAINT `usuario_rol_foreign` FOREIGN KEY (`Rol`) REFERENCES `rol` (`IdRol`),
  CONSTRAINT `usuario_tipodoc_foreign` FOREIGN KEY (`TipoDoc`) REFERENCES `tipodocumento` (`IdTipoDoc`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuario`
--

LOCK TABLES `usuario` WRITE;
/*!40000 ALTER TABLE `usuario` DISABLE KEYS */;
INSERT INTO `usuario` VALUES (14,'IKER','SIERRA',1,'99887766',2,'987654321',1,1,'2025-11-09 12:11:58'),(15,'pablo','Ramirez',2,'872346812',3,'762394581',0,1,'2025-11-09 12:15:04'),(16,'dayan','quispe',4,'72489213412',1,'1236498124',1,1,'2025-11-09 15:28:53'),(17,'juan','perez',1,'1682346981',2,'2614894323',1,1,'2025-11-09 15:38:59'),(18,'Stevie','Marca',1,'72405382',2,'979739029',1,1,'2025-11-10 20:32:10'),(19,'Nicol','Carol',1,'72405385',4,'98888',1,1,'2025-11-10 20:57:55'),(20,'Stevie','Marca',1,'72405388',3,'979739029',NULL,1,'2025-11-26 19:25:03');
/*!40000 ALTER TABLE `usuario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuario_auth`
--

DROP TABLE IF EXISTS `usuario_auth`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usuario_auth` (
  `IdAuth` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `IdUsuario` int(10) unsigned NOT NULL,
  `CorreoU` varchar(30) NOT NULL,
  `Password` varchar(255) NOT NULL DEFAULT '',
  `UltimoLogin` datetime DEFAULT NULL,
  `SesionToken` varchar(255) DEFAULT NULL,
  `SesionExpira` datetime DEFAULT NULL,
  `SesionTipo` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`IdAuth`),
  UNIQUE KEY `usuario_auth_idusuario_unique` (`IdUsuario`),
  UNIQUE KEY `usuario_auth_correou_unique` (`CorreoU`),
  CONSTRAINT `usuario_auth_idusuario_foreign` FOREIGN KEY (`IdUsuario`) REFERENCES `usuario` (`IdUsuario`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuario_auth`
--

LOCK TABLES `usuario_auth` WRITE;
/*!40000 ALTER TABLE `usuario_auth` DISABLE KEYS */;
INSERT INTO `usuario_auth` VALUES (6,14,'ike@upt.pe','MTIzNDU2','2025-11-12 19:11:57',NULL,NULL,NULL),(7,15,'pa@upt.pe','MTIzNDU2','2025-11-27 11:59:07',NULL,NULL,NULL),(8,16,'dn@upt.pe','MTIzNDU2','2025-11-26 19:33:58',NULL,NULL,NULL),(9,17,'ju@upt.pe','MTIzNDU2',NULL,NULL,NULL,NULL),(10,18,'sm@upt.pe','MTIzNDU2','2025-11-27 11:59:57','f0feacd6-9e02-4978-87a9-139127faf431','2025-11-27 12:19:57','academic'),(11,19,'Nc@upt.pe','MTIzNDU2','2025-11-19 16:17:18',NULL,NULL,NULL),(12,20,'StevieMarca@upt.pe','MTIzNDU2','2025-11-26 19:25:19',NULL,NULL,NULL);
/*!40000 ALTER TABLE `usuario_auth` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuario_sesion`
--

DROP TABLE IF EXISTS `usuario_sesion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usuario_sesion` (
  `IdSesion` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `IdUsuario` int(10) unsigned NOT NULL,
  `Dispositivo` varchar(50) DEFAULT NULL,
  `IP` varchar(45) DEFAULT NULL,
  `Activa` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`IdSesion`),
  KEY `usuario_sesion_idusuario_foreign` (`IdUsuario`),
  CONSTRAINT `usuario_sesion_idusuario_foreign` FOREIGN KEY (`IdUsuario`) REFERENCES `usuario` (`IdUsuario`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuario_sesion`
--

LOCK TABLES `usuario_sesion` WRITE;
/*!40000 ALTER TABLE `usuario_sesion` DISABLE KEYS */;
/*!40000 ALTER TABLE `usuario_sesion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'sisintupt'
--
/*!50106 SET @save_time_zone= @@TIME_ZONE */ ;
/*!50106 DROP EVENT IF EXISTS `actualizar_sanciones_cumplidas` */;
DELIMITER ;;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;;
/*!50003 SET character_set_client  = utf8mb4 */ ;;
/*!50003 SET character_set_results = utf8mb4 */ ;;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;;
/*!50003 SET @saved_time_zone      = @@time_zone */ ;;
/*!50003 SET time_zone             = 'SYSTEM' */ ;;
/*!50106 CREATE*/ /*!50117 DEFINER=`root`@`localhost`*/ /*!50106 EVENT `actualizar_sanciones_cumplidas` ON SCHEDULE EVERY 1 DAY STARTS '2025-11-01 00:00:00' ON COMPLETION PRESERVE ENABLE DO BEGIN
                UPDATE sancion 
                SET Estado = 'Cumplida' 
                WHERE Estado = 'Activa' 
                AND FechaFin < CURDATE();
            END */ ;;
/*!50003 SET time_zone             = @saved_time_zone */ ;;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;;
/*!50003 SET character_set_client  = @saved_cs_client */ ;;
/*!50003 SET character_set_results = @saved_cs_results */ ;;
/*!50003 SET collation_connection  = @saved_col_connection */ ;;
/*!50106 DROP EVENT IF EXISTS `aplicar_horarios_fijos` */;;
DELIMITER ;;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;;
/*!50003 SET character_set_client  = utf8mb4 */ ;;
/*!50003 SET character_set_results = utf8mb4 */ ;;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;;
/*!50003 SET @saved_time_zone      = @@time_zone */ ;;
/*!50003 SET time_zone             = 'SYSTEM' */ ;;
/*!50106 CREATE*/ /*!50117 DEFINER=`root`@`localhost`*/ /*!50106 EVENT `aplicar_horarios_fijos` ON SCHEDULE EVERY 1 DAY STARTS '2025-11-01 00:00:00' ON COMPLETION PRESERVE ENABLE DO BEGIN
                UPDATE horarios h
                JOIN horario_curso hc ON h.espacio = hc.Espacio
                                       AND h.bloque = hc.Bloque
                                       AND h.diaSemana = hc.DiaSemana
                SET h.ocupado = 1
                WHERE CURDATE() BETWEEN hc.FechaInicio AND hc.FechaFin
                  AND hc.Estado = 1;
            END */ ;;
/*!50003 SET time_zone             = @saved_time_zone */ ;;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;;
/*!50003 SET character_set_client  = @saved_cs_client */ ;;
/*!50003 SET character_set_results = @saved_cs_results */ ;;
/*!50003 SET collation_connection  = @saved_col_connection */ ;;
/*!50106 DROP EVENT IF EXISTS `liberar_horarios_fijos` */;;
DELIMITER ;;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;;
/*!50003 SET character_set_client  = utf8mb4 */ ;;
/*!50003 SET character_set_results = utf8mb4 */ ;;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;;
/*!50003 SET @saved_time_zone      = @@time_zone */ ;;
/*!50003 SET time_zone             = 'SYSTEM' */ ;;
/*!50106 CREATE*/ /*!50117 DEFINER=`root`@`localhost`*/ /*!50106 EVENT `liberar_horarios_fijos` ON SCHEDULE EVERY 1 DAY STARTS '2025-11-01 00:00:00' ON COMPLETION PRESERVE ENABLE DO BEGIN
                UPDATE horario_curso
                SET Estado = 0
                WHERE FechaFin < CURDATE();
                
                UPDATE horarios h
                JOIN horario_curso hc ON h.espacio = hc.Espacio
                                       AND h.bloque = hc.Bloque
                                       AND h.diaSemana = hc.DiaSemana
                SET h.ocupado = 0
                WHERE hc.FechaFin < CURDATE()
                  AND h.ocupado = 1;
            END */ ;;
/*!50003 SET time_zone             = @saved_time_zone */ ;;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;;
/*!50003 SET character_set_client  = @saved_cs_client */ ;;
/*!50003 SET character_set_results = @saved_cs_results */ ;;
/*!50003 SET collation_connection  = @saved_col_connection */ ;;
/*!50106 DROP EVENT IF EXISTS `resetear_bloqueos` */;;
DELIMITER ;;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;;
/*!50003 SET character_set_client  = utf8mb4 */ ;;
/*!50003 SET character_set_results = utf8mb4 */ ;;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;;
/*!50003 SET @saved_time_zone      = @@time_zone */ ;;
/*!50003 SET time_zone             = 'SYSTEM' */ ;;
/*!50106 CREATE*/ /*!50117 DEFINER=`root`@`localhost`*/ /*!50106 EVENT `resetear_bloqueos` ON SCHEDULE EVERY 1 HOUR STARTS '2025-11-01 00:00:00' ON COMPLETION PRESERVE ENABLE DO BEGIN
                UPDATE usuario_auth 
                SET IntentosFallidos = 0, 
                    BloqueadoHasta = NULL 
                WHERE BloqueadoHasta IS NOT NULL 
                AND BloqueadoHasta < NOW();
                
                UPDATE usuario_auth 
                SET TokenRecuperacion = NULL, 
                    TokenExpiracion = NULL 
                WHERE TokenExpiracion IS NOT NULL 
                AND TokenExpiracion < NOW();
            END */ ;;
/*!50003 SET time_zone             = @saved_time_zone */ ;;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;;
/*!50003 SET character_set_client  = @saved_cs_client */ ;;
/*!50003 SET character_set_results = @saved_cs_results */ ;;
/*!50003 SET collation_connection  = @saved_col_connection */ ;;
/*!50106 DROP EVENT IF EXISTS `reset_horarios_domingo` */;;
DELIMITER ;;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;;
/*!50003 SET character_set_client  = utf8mb4 */ ;;
/*!50003 SET character_set_results = utf8mb4 */ ;;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;;
/*!50003 SET @saved_time_zone      = @@time_zone */ ;;
/*!50003 SET time_zone             = 'SYSTEM' */ ;;
/*!50106 CREATE*/ /*!50117 DEFINER=`root`@`localhost`*/ /*!50106 EVENT `reset_horarios_domingo` ON SCHEDULE EVERY 1 WEEK STARTS '2025-11-02 00:00:00' ON COMPLETION PRESERVE ENABLE DO BEGIN
                UPDATE horarios h
                JOIN reserva r ON h.espacio = r.espacio AND h.bloque = r.bloque
                SET h.ocupado = 0
                WHERE r.fechaReserva < CURDATE();
            END */ ;;
/*!50003 SET time_zone             = @saved_time_zone */ ;;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;;
/*!50003 SET character_set_client  = @saved_cs_client */ ;;
/*!50003 SET character_set_results = @saved_cs_results */ ;;
/*!50003 SET collation_connection  = @saved_col_connection */ ;;
DELIMITER ;
/*!50106 SET TIME_ZONE= @save_time_zone */ ;

--
-- Dumping routines for database 'sisintupt'
--
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `aprobar_reserva` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `aprobar_reserva`(IN p_IdReserva INT)
BEGIN
                DECLARE v_usuario INT;
                DECLARE v_espacio INT;
                DECLARE v_bloque INT;
                DECLARE v_fecha DATE;
                DECLARE v_rol INT;
                DECLARE v_motivo_rechazo VARCHAR(255);
                DECLARE v_existen_otros_estudiantes INT DEFAULT 0;

                SELECT r.usuario, r.espacio, r.bloque, r.fechaReserva, u.Rol 
                INTO v_usuario, v_espacio, v_bloque, v_fecha, v_rol
                FROM reserva r
                JOIN usuario u ON r.usuario = u.IdUsuario
                WHERE r.IdReserva = p_IdReserva;

                INSERT INTO reserva_gestion (IdReserva, UsuarioGestion, Accion, Motivo)
                VALUES (p_IdReserva, v_usuario, 'Aprobar', 'Reserva aprobada mediante procedimiento');

                IF v_rol = 1 THEN
                    SET v_motivo_rechazo = 'un docente reservo prioritario';
                    
                    INSERT INTO reserva_gestion (IdReserva, UsuarioGestion, Accion, Motivo)
                    SELECT r.IdReserva, v_usuario, 'Rechazar', v_motivo_rechazo
                    FROM reserva r
                    WHERE r.espacio = v_espacio
                      AND r.fechaReserva = v_fecha
                      AND r.bloque = v_bloque
                      AND r.Estado = 'Pendiente'
                      AND r.IdReserva != p_IdReserva;

                ELSEIF v_rol = 3 THEN
                    SET v_motivo_rechazo = 'prioridad auditoria';
                    
                    INSERT INTO reserva_gestion (IdReserva, UsuarioGestion, Accion, Motivo)
                    SELECT r.IdReserva, v_usuario, 'Rechazar', v_motivo_rechazo
                    FROM reserva r
                    WHERE r.espacio = v_espacio
                      AND r.fechaReserva = v_fecha
                      AND r.bloque = v_bloque
                      AND r.Estado = 'Pendiente'
                      AND r.IdReserva != p_IdReserva;

                ELSEIF v_rol = 2 THEN
                    SELECT COUNT(*) INTO v_existen_otros_estudiantes
                    FROM reserva r
                    JOIN usuario u ON r.usuario = u.IdUsuario
                    WHERE r.espacio = v_espacio
                      AND r.fechaReserva = v_fecha
                      AND r.bloque = v_bloque
                      AND r.Estado = 'Pendiente'
                      AND u.Rol = 2
                      AND r.IdReserva != p_IdReserva;

                    IF v_existen_otros_estudiantes > 0 THEN
                        SET v_motivo_rechazo = 'estudiante reservo primero';
                        
                        INSERT INTO reserva_gestion (IdReserva, UsuarioGestion, Accion, Motivo)
                        SELECT r.IdReserva, v_usuario, 'Rechazar', v_motivo_rechazo
                        FROM reserva r
                        JOIN usuario u ON r.usuario = u.IdUsuario
                        WHERE r.espacio = v_espacio
                          AND r.fechaReserva = v_fecha
                          AND r.bloque = v_bloque
                          AND r.Estado = 'Pendiente'
                          AND u.Rol = 2
                          AND r.IdReserva != p_IdReserva;
                    END IF;
                END IF;
            END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `crear_reserva_automatica` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `crear_reserva_automatica`(
                IN p_usuario INT,
                IN p_espacio INT,
                IN p_bloque INT,
                IN p_curso INT,
                IN p_fechaReserva DATE,
                IN p_descripcion TEXT,
                IN p_cantidadEstudiantes INT
            )
BEGIN
                DECLARE v_dia_semana VARCHAR(10) COLLATE utf8mb4_unicode_ci;
                DECLARE v_horario_ocupado INT DEFAULT 0;
                DECLARE v_reserva_existente INT DEFAULT 0;

                SET v_dia_semana = CASE DAYOFWEEK(p_fechaReserva)
                    WHEN 2 THEN 'Lunes'
                    WHEN 3 THEN 'Martes'
                    WHEN 4 THEN 'Miercoles'
                    WHEN 5 THEN 'Jueves'
                    WHEN 6 THEN 'Viernes'
                    WHEN 7 THEN 'Sabado'
                    ELSE 'Lunes'
                END;

                SELECT COUNT(*) INTO v_horario_ocupado
                FROM horarios
                WHERE espacio = p_espacio
                AND bloque = p_bloque
                AND diaSemana = v_dia_semana
                AND ocupado = 1;

                SELECT COUNT(*) INTO v_reserva_existente
                FROM reserva
                WHERE espacio = p_espacio
                AND bloque = p_bloque
                AND fechaReserva = p_fechaReserva
                AND Estado = 'Aprobada';

                IF v_horario_ocupado = 0 AND v_reserva_existente = 0 THEN
                    INSERT INTO reserva (usuario, espacio, bloque, curso, fechaReserva, DescripcionUso, CantidadEstudiantes, Estado)
                    VALUES (p_usuario, p_espacio, p_bloque, p_curso, p_fechaReserva, p_descripcion, p_cantidadEstudiantes, 'Pendiente');

                    SELECT LAST_INSERT_ID() AS IdReserva, 'Reserva creada exitosamente' AS Mensaje;
                ELSE
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'El horario seleccionado no está disponible';
                END IF;
            END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-06-23  8:07:25
