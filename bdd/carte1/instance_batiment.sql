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
(50006, 1, 8, '', 6000, 6000, 13, 51, 2, 50),
(50005, 1, 9, '', 10000, 10000, 165, 173, 1, 100),
(50004, 1, 9, '', 10000, 10000, 8, 7, 2, 100),
(50007, 1, 11, '', 5000, 5000, 33, 55, 2, 50),
(50008, 1, 11, '', 5000, 5000, 65, 37, 2, 50),
(50009, 1, 11, '', 4065, 6000, 119, 54, 2, 50),
(50010, 1, 8, '', 2614, 6000, 104, 63, 2, 50),
(50011, 1, 8, '', 4056, 6000, 158, 6, 2, 50),
(50012, 1, 11, '', 5599, 6000, 170, 33, 2, 50),
(50013, 1, 8, '', 4476, 6000, 64, 11, 2, 50),
(50014, 1, 8, '', 4869, 6000, 198, 158, 1, 50),
(50015, 1, 11, '', 4575, 6000, 176, 139, 1, 50),
(50016, 1, 11, '', 1073, 6000, 120, 164, 1, 50),
(50017, 1, 8, '', 3367, 6000, 116, 188, 1, 50),
(50018, 1, 11, '', 2791, 6000, 76, 151, 1, 50),
(50019, 1, 8, '', 2330, 6000, 73, 127, 1, 50),
(50020, 1, 11, '', 3238, 6000, 33, 168, 1, 50),
(50021, 1, 8, '', 3406, 6000, 43, 193, 1, 50);

--
-- Contenu de la table `liaisons_gare`
--

INSERT INTO `liaisons_gare` (`id_gare1`, `id_gare2`) VALUES
(50007, 50008),
(50008, 50009),
(50009, 50012),
(50015, 50016),
(50016, 50018),
(50018, 50020);

--
-- AUTO_INCREMENT pour la table `instance_batiment`
--
ALTER TABLE `instance_batiment`
  MODIFY `id_instanceBat` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50022;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
