-- MySQL Administrator dump 1.4
--
-- ------------------------------------------------------
-- Server version	5.7.18


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;


--
-- Create schema `svelte-app1`
--

-- CREATE DATABASE IF NOT EXISTS `svelte-app1`;
-- USE `svelte-app1`;


--
-- Definition of table `support`
--

DROP TABLE IF EXISTS `support`;
CREATE TABLE `support` (
  `id_support` int(11) NOT NULL AUTO_INCREMENT,
  `support` varchar(30) NOT NULL,
  PRIMARY KEY (`id_support`),
  UNIQUE KEY `support_UNIQUE` (`support`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `support`
--

/*!40000 ALTER TABLE `support` DISABLE KEYS */;
INSERT INTO `support` (`id_support`,`support`) VALUES 
 (3,'Blue-ray'),
 (1,'CDROM'),
 (2,'DVD');
/*!40000 ALTER TABLE `support` ENABLE KEYS */;


--
-- Definition of table `theme`
--

DROP TABLE IF EXISTS `theme`;
CREATE TABLE `theme` (
  `id_theme` int(11) NOT NULL AUTO_INCREMENT,
  `theme` varchar(30) NOT NULL,
  PRIMARY KEY (`id_theme`),
  UNIQUE KEY `theme_UNIQUE` (`theme`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `theme`
--

/*!40000 ALTER TABLE `theme` DISABLE KEYS */;
INSERT INTO `theme` (`id_theme`,`theme`) VALUES 
 (7,'Animación'),
 (2,'Aventura'),
 (1,'Ciencia Ficción'),
 (6,'Crimen'),
 (4,'Drama'),
 (3,'Fantasía'),
 (5,'Terror');
/*!40000 ALTER TABLE `theme` ENABLE KEYS */;


--
-- Definition of table `movie`
--

DROP TABLE IF EXISTS `movie`;
CREATE TABLE `movie` (
  `id_movie` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(40) NOT NULL,
  `price` decimal(7,2) NOT NULL,
  `startDate` date NOT NULL,
  `rating` int(11) NOT NULL,
  `theme_id` int(11) NOT NULL,
  `support_id` int(11) NOT NULL,
  PRIMARY KEY (`id_movie`),
  UNIQUE KEY `name_UNIQUE` (`name`),
  KEY `fk_movie_theme_idx` (`theme_id`),
  KEY `fk_movie_support1_idx` (`support_id`),
  CONSTRAINT `fk_movie_support1` FOREIGN KEY (`support_id`) REFERENCES `support` (`id_support`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `fk_movie_theme` FOREIGN KEY (`theme_id`) REFERENCES `theme` (`id_theme`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `movie`
--

/*!40000 ALTER TABLE `movie` DISABLE KEYS */;
INSERT INTO `movie` (`id_movie`,`name`,`price`,`startDate`,`rating`,`theme_id`,`support_id`) VALUES 
 (1,'Alien: El Octavo Pasajero','18.75','1979-05-25',2,1,3),
 (2,'Regreso al Futuro','12.00','1985-07-03',5,2,2),
 (3,'Origen','25.50','2010-07-16',5,1,3),
 (4,'El Señor de los Anillos','22.00','2001-12-19',5,3,2),
 (5,'Interestellar','28.99','2014-11-07',4,1,3),
 (6,'Matrix','9.50','1999-03-31',5,1,2),
 (7,'Parásitos','15.00','2019-05-30',5,4,2),
 (8,'Psicosis','8.00','1960-06-16',5,5,1),
 (9,'Pulp Fiction','11.20','1994-10-14',4,6,3),
 (14,'ET','5.25','2025-07-01',1,1,1);
/*!40000 ALTER TABLE `movie` ENABLE KEYS */;



/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
