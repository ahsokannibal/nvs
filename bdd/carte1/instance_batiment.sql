-- phpMyAdmin SQL Dump
-- version 4.6.4
-- https://www.phpmyadmin.net/
--
-- Client :  127.0.0.1
-- Généré le :  Lun 23 Décembre 2019 à 18:27
-- Version du serveur :  5.7.14
-- Version de PHP :  5.6.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données :  `nvs`
--

-- --------------------------------------------------------

--
-- Contenu de la table `instance_batiment`
--

INSERT INTO `instance_batiment` (`id_instanceBat`, `niveau_instance`, `id_batiment`, `nom_instance`, `pv_instance`, `pvMax_instance`, `x_instance`, `y_instance`, `camp_instance`, `contenance_instance`) VALUES
(50006, 1, 8, 'Vincible', 6000, 6000, 13, 51, 2, 50),
(50005, 1, 9, 'Midable', 10000, 10000, 165, 173, 1, 100),
(50004, 1, 9, 'Iche', 10000, 10000, 8, 7, 2, 100),
(50007, 1, 11, 'Nement', 5000, 5000, 33, 55, 2, 50),
(50008, 1, 11, 'Deuxtrois', 5000, 5000, 65, 37, 2, 50),
(50009, 1, 11, 'Atoi', 6000, 6000, 119, 54, 2, 50),
(50010, 1, 8, 'Tamare', 6000, 6000, 104, 63, 2, 50),
(50011, 1, 8, 'Trouvable', 6000, 6000, 158, 6, 2, 50),
(50012, 1, 11, 'Niture', 6000, 6000, 170, 33, 2, 50),
(50013, 1, 8, 'Berbe', 6000, 6000, 64, 11, 2, 50),
(50014, 1, 8, 'Tin et Milou', 6000, 6000, 198, 158, 1, 50),
(50015, 1, 11, 'Age', 6000, 6000, 176, 139, 1, 50),
(50016, 1, 11, 'Haine', 6000, 6000, 120, 164, 1, 50),
(50017, 1, 8, 'Flammable', 6000, 6000, 116, 188, 1, 50),
(50018, 1, 11, 'Field', 6000, 6000, 76, 151, 1, 50),
(50019, 1, 8, 'Traitable', 6000, 6000, 73, 127, 1, 50),
(50020, 1, 11, 'Anti', 6000, 6000, 33, 168, 1, 50),
(50021, 1, 8, 'Terdit', 6000, 6000, 43, 193, 1, 50),
(50025, 1, 5, '', 750, 750, 21, 7, 2, 0),
(50028, 1, 12, NULL, 2500, 2500, 117, 54, 2, 50),
(50029, 1, 12, NULL, 2500, 2500, 119, 52, 2, 50),
(50030, 1, 12, NULL, 2500, 2500, 65, 35, 2, 50),
(50031, 1, 12, NULL, 2500, 2500, 78, 151, 1, 50),
(50032, 1, 12, NULL, 2500, 2500, 74, 151, 1, 50),
(50033, 1, 12, NULL, 2500, 2500, 122, 164, 1, 50);

--
-- Contenu de la table `instance_batiment_canon`
--

INSERT INTO `instance_batiment_canon` (`id_instance_canon`, `id_instance_bat`, `x_canon`, `y_canon`, `camp_canon`, `date_activation`) VALUES
(1, 50014, 197, 157, 1, NULL),
(2, 50014, 197, 159, 1, NULL),
(3, 50014, 199, 157, 1, NULL),
(4, 50014, 199, 159, 1, NULL),
(5, 50010, 103, 62, 2, NULL),
(6, 50010, 103, 64, 2, NULL),
(7, 50010, 105, 62, 2, NULL),
(8, 50010, 105, 64, 2, NULL),
(9, 50006, 12, 50, 2, NULL),
(10, 50006, 12, 52, 2, NULL),
(11, 50006, 14, 50, 2, NULL),
(12, 50006, 14, 52, 2, NULL),
(13, 50013, 63, 10, 2, NULL),
(14, 50013, 63, 12, 2, NULL),
(15, 50013, 65, 10, 2, NULL),
(16, 50013, 65, 12, 2, NULL),
(17, 50011, 157, 5, 2, NULL),
(18, 50011, 157, 7, 2, NULL),
(19, 50011, 159, 5, 2, NULL),
(20, 50011, 159, 7, 2, NULL),
(21, 50017, 115, 187, 1, NULL),
(22, 50017, 115, 189, 1, NULL),
(23, 50017, 117, 187, 1, NULL),
(24, 50017, 117, 189, 1, NULL),
(25, 50019, 72, 126, 1, NULL),
(26, 50019, 72, 128, 1, NULL),
(27, 50019, 74, 126, 1, NULL),
(28, 50019, 74, 128, 1, NULL),
(29, 50021, 42, 192, 1, NULL),
(30, 50021, 42, 194, 1, NULL),
(31, 50021, 44, 192, 1, NULL),
(32, 50021, 44, 194, 1, NULL),
(33, 50004, 6, 9, 2, NULL),
(34, 50004, 6, 7, 2, NULL),
(35, 50004, 6, 5, 2, NULL),
(36, 50004, 10, 9, 2, NULL),
(37, 50004, 10, 7, 2, NULL),
(38, 50004, 10, 5, 2, NULL),
(39, 50005, 163, 175, 1, NULL),
(40, 50005, 163, 173, 1, NULL),
(41, 50005, 163, 171, 1, NULL),
(42, 50005, 167, 175, 1, NULL),
(43, 50005, 167, 173, 1, NULL),
(44, 50005, 167, 171, 1, NULL);

--
-- Contenu de la table `liaisons_gare`
--

INSERT INTO `liaisons_gare` (`id_gare1`, `id_gare2`, `id_train`, `direction`) VALUES
(50007, 50008, 50030, 50007),
(50008, 50009, 50028, 50008),
(50009, 50012, 50029, 50012),
(50015, 50016, 50033, 50015),
(50016, 50018, 50031, 50016),
(50018, 50020, 50032, 50020);

--
-- AUTO_INCREMENT pour la table `instance_batiment`
--
ALTER TABLE `instance_batiment`
  MODIFY `id_instanceBat` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50022;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
