-- phpMyAdmin SQL Dump
-- version 4.6.4
-- https://www.phpmyadmin.net/
--
-- Client :  127.0.0.1
-- G&eacute;n&eacute;r&eacute; le :  Ven 15 Novembre 2019 à 22:06
-- Version du serveur :  5.7.14
-- Version de PHP :  5.6.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de donn&eacute;es :  `nvs`
--

-- --------------------------------------------------------

--
-- Structure de la table `action`
--

CREATE TABLE `action` (
  `id_action` int(11) NOT NULL,
  `nom_action` varchar(25) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `nb_points` int(11) NOT NULL DEFAULT '1',
  `description_action` text CHARACTER SET latin1 COLLATE latin1_general_ci,
  `portee_action` int(11) NOT NULL DEFAULT '0',
  `perceptionMin_action` int(11) NOT NULL DEFAULT '0',
  `perceptionMax_action` int(11) NOT NULL DEFAULT '0',
  `pvMin_action` int(11) NOT NULL DEFAULT '0',
  `pvMax_action` int(11) NOT NULL DEFAULT '0',
  `recupMin_action` int(11) NOT NULL DEFAULT '0',
  `recupMax_action` int(11) NOT NULL DEFAULT '0',
  `pmMin_action` int(11) NOT NULL DEFAULT '0',
  `pmMax_action` int(11) NOT NULL DEFAULT '0',
  `DefMin_action` int(11) NOT NULL DEFAULT '0',
  `DefMax_action` int(11) NOT NULL DEFAULT '0',
  `coutPa_action` int(11) NOT NULL DEFAULT '0',
  `nbreTourMin` int(11) NOT NULL DEFAULT '0',
  `nbreTourMax` int(11) NOT NULL DEFAULT '0',
  `coutOr_action` int(11) NOT NULL DEFAULT '0',
  `coutBois_action` int(11) NOT NULL DEFAULT '0',
  `coutFer_action` int(11) NOT NULL DEFAULT '0',
  `reflexive_action` int(11) NOT NULL DEFAULT '0',
  `cible_action` int(11) NOT NULL DEFAULT '0',
  `case_action` int(11) NOT NULL DEFAULT '0',
  `pnj_action` int(11) NOT NULL DEFAULT '0',
  `passif_action` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Contenu de la table `action`
--

INSERT INTO `action` (`id_action`, `nom_action`, `nb_points`, `description_action`, `portee_action`, `perceptionMin_action`, `perceptionMax_action`, `pvMin_action`, `pvMax_action`, `recupMin_action`, `recupMax_action`, `pmMin_action`, `pmMax_action`, `DefMin_action`, `DefMax_action`, `coutPa_action`, `nbreTourMin`, `nbreTourMax`, `coutOr_action`, `coutBois_action`, `coutFer_action`, `reflexive_action`, `cible_action`, `case_action`, `pnj_action`, `passif_action`) VALUES
(1, 'dormir', 1, 'Permet de se reposer n\'importe ou et monter sa r&eacute;cup&eacute;ration pour le prochain tour - utilise la totalit&eacute; de ses PA', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0),
(2, 'dormir', 2, 'Permet de se reposer n\'importe ou et monter sa r&eacute;cup&eacute;ration pour le prochain tour - utilise la totalit&eacute; de ses PA', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0),
(3, 'dormir', 3, 'Permet de se reposer n\'importe ou et monter sa r&eacute;cup&eacute;ration pour le prochain tour - utilise la totalit&eacute; de ses PA', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0),
(4, 'Marche forc&eacute;e', 1, 'Permet de se d&eacute;passer afin de gagner un PM', 0, 0, 0, -8, -8, 0, 0, 1, 1, 0, 0, 3, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0),
(5, 'Marche forc&eacute;e', 2, 'Permet de se d&eacute;passer afin de gagner un PM', 0, 0, 0, -6, -6, 0, 0, 1, 1, 0, 0, 3, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0),
(6, 'Marche forc&eacute;e', 3, 'Permet de se d&eacute;passer afin de gagner un PM', 0, 0, 0, -4, -4, 0, 0, 1, 1, 0, 0, 3, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0),
(7, 'Courir', 1, 'Permet de courir et de gagner des PM pendant 1 tour en consommant tout ses PA - permet de fuir...', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0),
(8, 'Courir', 2, 'Permet de courir et de gagner des PM pendant 1 tour en consommant tout ses PA - permet de fuir...', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0),
(9, 'Courir', 3, 'Permet de courir et de gagner des PM pendant 1 tour en consommant tout ses PA - permet de fuir...', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0),
(10, 'Sauter', 1, 'Permet de sauter par dessus un autre perso. Utilise 1PM + cout PM case arriv&eacute;e', 0, 0, 0, 0, 0, 0, 0, -1, -1, 0, 0, 4, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0),
(11, 'Premiers soins', 1, 'Permet de se soigner ou de soigner une personne ayant des blessures l&eacute;gères (jusqu\'à 10% de PV en moins)', 1, 0, 0, 1, 3, 0, 0, 0, 0, 0, 0, 6, 0, 0, 0, 0, 0, 1, 1, 0, 0, 0),
(12, 'Premiers soins', 2, 'Permet de se soigner ou de soigner une personne ayant des blessures l&eacute;gères (jusqu\'à 10% de PV en moins)', 1, 0, 0, 1, 5, 0, 0, 0, 0, 0, 0, 6, 0, 0, 0, 0, 0, 1, 1, 0, 0, 0),
(13, 'Premiers soins', 3, 'Permet de se soigner ou de soigner une personne ayant des blessures l&eacute;gères (jusqu\'à 10% de PV en moins)', 1, 0, 0, 1, 8, 0, 0, 0, 0, 0, 0, 6, 0, 0, 0, 0, 0, 1, 1, 0, 0, 0),
(14, 'Soins avanc&eacute;s', 1, 'Permet de se soigner ou de soigner une personne ayant des blessures un peu plus graves (jusqu\'à  25% de PV en moins)', 1, 0, 0, 1, 5, 0, 0, 0, 0, 0, 0, 6, 0, 0, 0, 0, 0, 1, 1, 0, 0, 0),
(15, 'Soins avanc&eacute;s', 2, 'Permet de se soigner ou de soigner une personne ayant des blessures un peu plus graves (jusqu\'à  25% de PV en moins)', 1, 0, 0, 1, 8, 0, 0, 0, 0, 0, 0, 6, 0, 0, 0, 0, 0, 1, 1, 0, 0, 0),
(16, 'Soins avanc&eacute;s', 3, 'Permet de se soigner ou de soigner une personne ayant des blessures un peu plus graves (jusqu\'à  25% de PV en moins)', 1, 0, 0, 1, 12, 0, 0, 0, 0, 0, 0, 6, 0, 0, 0, 0, 0, 1, 1, 0, 0, 0),
(17, 'Soins v&eacute;t&eacute;rinaire', 1, 'Permet de soigner un de ses animaux de compagnie', 1, 0, 0, 1, 3, 0, 0, 0, 0, 0, 0, 6, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0),
(18, 'Soins v&eacute;t&eacute;rinaire', 2, 'Permet de soigner un de ses animaux de compagnie', 1, 0, 0, 1, 3, 0, 0, 0, 0, 0, 0, 6, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0),
(19, 'Soins v&eacute;t&eacute;rinaire', 3, 'Permet de soigner un de ses animaux de compagnie', 1, 0, 0, 1, 5, 0, 0, 0, 0, 0, 0, 6, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0),
(20, 'Soins v&eacute;t&eacute;rinaire', 4, 'Permet de soigner un de ses animaux de compagnie', 1, 0, 0, 1, 8, 0, 0, 0, 0, 0, 0, 6, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0),
(21, 'Soins v&eacute;t&eacute;rinaire', 5, 'Permet de soigner un de ses animaux de compagnie', 1, 0, 0, 1, 12, 0, 0, 0, 0, 0, 0, 6, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0),
(22, 'Chirurgie', 1, 'Permet de se soigner ou de soigner une personne ayant des blessures graves (jusqu\'à 50% de pv en moins)', 1, 0, 0, 1, 8, 0, 0, 0, 0, 0, 0, 6, 0, 0, 0, 0, 0, 1, 1, 0, 0, 0),
(23, 'Chirurgie', 2, 'Permet de se soigner ou de soigner une personne ayant des blessures graves (jusqu\'à 50% de pv en moins)', 1, 0, 0, 1, 12, 0, 0, 0, 0, 0, 0, 6, 0, 0, 0, 0, 0, 1, 1, 0, 0, 0),
(24, 'Chirurgie', 3, 'Permet de se soigner ou de soigner une personne ayant des blessures graves (jusqu\'à 50% de pv en moins)', 1, 0, 0, 1, 15, 0, 0, 0, 0, 0, 0, 6, 0, 0, 0, 0, 0, 1, 1, 0, 0, 0),
(25, 'Chirurgie de guerre', 1, 'Permet de se soigner ou de soigner une personne ayant des blessures très graves (jusqu\'à 99% de pv en moins)', 1, 0, 0, 1, 8, 0, 0, 0, 0, 0, 0, 7, 0, 0, 0, 0, 0, 1, 1, 0, 0, 0),
(26, 'Chirurgie de guerre', 2, 'Permet de se soigner ou de soigner une personne ayant des blessures très graves (jusqu\'à 99% de pv en moins)', 1, 0, 0, 1, 12, 0, 0, 0, 0, 0, 0, 7, 0, 0, 0, 0, 0, 1, 1, 0, 0, 0),
(27, 'Chirurgie de guerre', 3, 'Permet de se soigner ou de soigner une personne ayant des blessures très graves (jusqu\'à 99% de pv en moins)', 1, 0, 0, 1, 15, 0, 0, 0, 0, 0, 0, 7, 0, 0, 0, 0, 0, 1, 1, 0, 0, 0),
(28, 'Couper du bois', 1, 'Permet de couper du bois. Fais disparaitre la forêt qu\'on d&eacute;coupe', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 7, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0),
(29, 'Couper du bois', 2, 'Permet de couper du bois. Fais disparaitre la forêt qu\'on d&eacute;coupe', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 7, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0),
(30, 'Couper du bois', 3, 'Permet de couper du bois. Fais disparaitre la forêt qu\'on d&eacute;coupe', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 7, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0),
(31, 'Miner la montagne', 1, 'Permet de miner une montagne à la recherche de fer', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 10, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0),
(32, 'Construire - route', 1, 'Permet de construire une route. la route ne peut être construite adjacente à un QG (le fortin est consid&eacute;r&eacute; comme un QG), un hôpital, un entrepôt ou Ã  une autre route', 1, 0, 0, 10, 10, 0, 0, 0, 0, 0, 0, 5, 0, 0, 1, 0, 0, 0, 0, 1, 0, 0),
(33, 'Construire - barricade', 1, 'Permet de construire une barricade qui occupe une case', 1, 0, 0, 20, 20, 0, 0, 0, 0, 0, 0, 10, 0, 0, 5, 1, 0, 0, 0, 1, 0, 0),
(34, 'Construire - barricade', 2, 'Permet de construire une barricade qui occupe une case', 1, 0, 0, 40, 40, 0, 0, 0, 0, 0, 0, 10, 0, 0, 5, 1, 0, 0, 0, 1, 0, 0),
(35, 'Construire - barricade', 3, 'Permet de construire une barricade qui occupe une case', 1, 0, 0, 50, 50, 0, 0, 0, 0, 0, 0, 10, 0, 0, 5, 1, 0, 0, 0, 1, 0, 0),
(36, 'Construire - barricade', 4, 'Permet de construire une barricade qui occupe une case', 1, 0, 0, 60, 60, 0, 0, 0, 0, 0, 0, 10, 0, 0, 5, 1, 0, 0, 0, 1, 0, 0),
(37, 'Construire - barricade', 5, 'Permet de construire une barricade qui occupe une case', 1, 0, 0, 60, 100, 0, 0, 0, 0, 0, 0, 10, 0, 0, 5, 1, 0, 0, 0, 1, 0, 0),
(38, 'Construire - pont', 1, 'Permet de construire un pont sur une case d\'eau. Le pont ne peut se construire qu\'à proximit&eacute; d\'une case de terre ou d\'une autre case de pont', 1, 0, 0, 10, 10, 0, 0, 0, 0, 0, 0, 10, 0, 0, 10, 1, 0, 0, 0, 1, 0, 0),
(39, 'Construire - pont', 2, 'Permet de construire un pont sur une case d\'eau. Le pont ne peut se construire qu\'à proximit&eacute; d\'une case de terre ou d\'une autre case de pont', 1, 0, 0, 20, 20, 0, 0, 0, 0, 0, 0, 10, 0, 0, 10, 1, 0, 0, 0, 1, 0, 0),
(40, 'Construire - pont', 3, 'Permet de construire un pont sur une case d\'eau. Le pont ne peut se construire qu\'à proximit&eacute; d\'une case de terre ou d\'une autre case de pont', 1, 0, 0, 30, 30, 0, 0, 0, 0, 0, 0, 10, 0, 0, 10, 1, 0, 0, 0, 1, 0, 0),
(41, 'Construire - pont', 4, 'Permet de construire un pont sur une case d\'eau. Le pont ne peut se construire qu\'à proximit&eacute; d\'une case de terre ou d\'une autre case de pont', 1, 0, 0, 40, 40, 0, 0, 0, 0, 0, 0, 10, 0, 0, 10, 1, 0, 0, 0, 1, 0, 0),
(42, 'Construire - pont', 5, 'Permet de construire un pont sur une case d\'eau. Le pont ne peut se construire qu\'à proximit&eacute; d\'une case de terre ou d\'une autre case de pont', 1, 0, 0, 40, 60, 0, 0, 0, 0, 0, 0, 10, 0, 0, 10, 1, 0, 0, 0, 1, 0, 0),
(43, 'Construire - tour de visu', 1, 'Permet de construire une tour de visu, occupe une case et peut contenir un perso', 1, 0, 0, 20, 20, 0, 0, 0, 0, 0, 0, 12, 0, 0, 40, 2, 0, 0, 0, 1, 0, 0),
(44, 'Construire - tour de visu', 2, 'Permet de construire une tour de visu, occupe une case et peut contenir un perso', 1, 0, 0, 40, 40, 0, 0, 0, 0, 0, 0, 12, 0, 0, 40, 2, 0, 0, 0, 1, 0, 0),
(45, 'Construire - tour de visu', 3, 'Permet de construire une tour de visu, occupe une case et peut contenir un perso', 1, 0, 0, 50, 50, 0, 0, 0, 0, 0, 0, 12, 0, 0, 40, 2, 0, 0, 0, 1, 0, 0),
(46, 'Construire - tour de visu', 4, 'Permet de construire une tour de visu, occupe une case et peut contenir un perso', 1, 0, 0, 50, 50, 0, 0, 0, 0, 0, 0, 12, 0, 0, 40, 2, 0, 0, 0, 1, 0, 0),
(47, 'Construire - tour de visu', 5, 'Permet de construire une tour de visu, occupe une case et peut contenir un perso', 1, 0, 0, 50, 50, 0, 0, 0, 0, 0, 0, 12, 0, 0, 40, 2, 0, 0, 0, 1, 0, 0),
(48, 'Construire - tour de gard', 1, 'Permet de construire une tour de garde, occupe une case et peut contenir un perso. Le perso peut attaquer depuis la tour avec une arme de distance. Donne un bonus de +2 en perception', 1, 0, 0, 50, 50, 0, 0, 0, 0, 0, 0, 12, 0, 0, 50, 2, 0, 0, 0, 1, 0, 0),
(49, 'Construire - entrepot d\'a', 1, 'Permet de construire un entrepôt d\'arme, occupe une case et les persos à proximit&eacute; peuvent acheter objets, armes et armures', 1, 0, 0, 100, 100, 0, 0, 0, 0, 0, 0, 12, 0, 0, 200, 4, 0, 0, 0, 1, 0, 0),
(50, 'Construire - entrepot d\'a', 2, 'Permet de construire un entrepôt d\'arme, occupe une case et les persos à proximit&eacute; peuvent acheter objets, armes et armures', 1, 0, 0, 100, 100, 0, 0, 0, 0, 0, 0, 12, 0, 0, 200, 4, 0, 0, 0, 1, 0, 0),
(51, 'Construire - entrepot d\'a', 3, 'Permet de construire un entrepôt d\'arme, occupe une case et les persos à proximit&eacute; peuvent acheter objets, armes et armures', 1, 0, 0, 100, 100, 0, 0, 0, 0, 0, 0, 12, 0, 0, 200, 4, 0, 0, 0, 1, 0, 0),
(52, 'Construire - entrepot d\'a', 4, 'Permet de construire un entrepôt d\'arme, occupe une case et les persos à proximit&eacute; peuvent acheter objets, armes et armures', 1, 0, 0, 100, 100, 0, 0, 0, 0, 0, 0, 12, 0, 0, 200, 4, 0, 0, 0, 1, 0, 0),
(53, 'Construire - entrepot d\'a', 5, 'Permet de construire un entrepôt d\'arme, occupe une case et les persos à proximit&eacute; peuvent acheter objets, armes et armures', 1, 0, 0, 100, 100, 0, 0, 0, 0, 0, 0, 12, 0, 0, 200, 4, 0, 0, 0, 1, 0, 0),
(54, 'Construire - hopital', 1, 'Permet de construire un hôpital', 1, 0, 0, 200, 200, 0, 0, 0, 0, 0, 0, 14, 0, 0, 500, 5, 0, 0, 0, 1, 0, 0),
(55, 'Construire - hopital', 2, 'Permet de construire un hôpital', 1, 0, 0, 200, 200, 0, 0, 0, 0, 0, 0, 14, 0, 0, 500, 5, 0, 0, 0, 1, 0, 0),
(56, 'Construire - hopital', 3, 'Permet de construire un hôpital', 1, 0, 0, 200, 200, 0, 0, 0, 0, 0, 0, 14, 0, 0, 500, 5, 0, 0, 0, 1, 0, 0),
(57, 'Construire - hopital', 4, 'Permet de construire un hopital', 1, 0, 0, 200, 200, 0, 0, 0, 0, 0, 0, 14, 0, 0, 500, 5, 0, 0, 0, 1, 0, 0),
(58, 'Construire - hopital', 5, 'Permet de construire un hopital', 1, 0, 0, 200, 200, 0, 0, 0, 0, 0, 0, 14, 0, 0, 500, 5, 0, 0, 0, 1, 0, 0),
(59, 'Construire - fortin', 1, 'Permet de construire un fortin. Les persos peuvent respawn dedans', 1, 0, 0, 400, 400, 0, 0, 0, 0, 0, 0, 15, 0, 0, 1000, 10, 0, 0, 0, 1, 0, 0),
(60, 'Construire - fortin', 2, 'Permet de construire un fortin. Les persos peuvent respawn dedans', 1, 0, 0, 400, 400, 0, 0, 0, 0, 0, 0, 15, 0, 0, 1000, 10, 0, 0, 0, 1, 0, 0),
(61, 'Construire - fortin', 3, 'Permet de construire un fortin. Les persos peuvent respawn dedans', 1, 0, 0, 400, 400, 0, 0, 0, 0, 0, 0, 15, 0, 0, 1000, 10, 0, 0, 0, 1, 0, 0),
(62, 'Construire - fortin', 4, 'Permet de construire un fortin. Les persos peuvent respawn dedans', 1, 0, 0, 400, 400, 0, 0, 0, 0, 0, 0, 15, 0, 0, 1000, 10, 0, 0, 0, 1, 0, 0),
(63, 'Construire - fortin', 5, 'Permet de construire un fortin. Les persos peuvent respawn dedans', 1, 0, 0, 400, 400, 0, 0, 0, 0, 0, 0, 15, 0, 0, 1000, 10, 0, 0, 0, 1, 0, 0),
(64, 'Construire - fort', 1, 'Permet de construire un fort. Les persos peuvent respawn dedans. Accessible seulement aux anims', 1, 0, 0, 1000, 1000, 0, 0, 0, 0, 0, 0, 10, 0, 0, 0, 10, 0, 0, 0, 1, 0, 0),
(65, 'Entrainement', 1, 'Permet de s\'entrainer', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 10, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0),
(66, 'Chance', 1, 'Am&eacute;liore le taux de r&eacute;ussite sur toutes les actions utilisant un pourcentage (dont l\\\'entrainement)', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1),
(67, 'Chance', 2, 'Am&eacute;liore le taux de r&eacute;ussite sur toutes les actions utilisant un pourcentage (dont l\\\'entrainement)', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1),
(68, 'Chance', 3, 'Am&eacute;liore le taux de r&eacute;ussite sur toutes les actions utilisant un pourcentage (dont l\\\'entrainement)', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1),
(69, 'Chance', 4, 'Am&eacute;liore le taux de r&eacute;ussite sur toutes les actions utilisant un pourcentage (dont l\\\'entrainement)', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1),
(70, 'Chance', 5, 'Am&eacute;liore le taux de r&eacute;ussite sur toutes les actions utilisant un pourcentage (dont l\\\'entrainement)', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1),
(71, 'Chance', 6, 'Am&eacute;liore le taux de r&eacute;ussite sur toutes les actions utilisant un pourcentage (dont l\\\'entrainement)', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1),
(72, 'Chance', 7, 'Am&eacute;liore le taux de r&eacute;ussite sur toutes les actions utilisant un pourcentage (dont l\\\'entrainement)', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1),
(73, 'Chance', 8, 'Am&eacute;liore le taux de r&eacute;ussite sur toutes les actions utilisant un pourcentage (dont l\\\'entrainement)', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1),
(74, 'Chance', 9, 'Am&eacute;liore le taux de r&eacute;ussite sur toutes les actions utilisant un pourcentage (dont l\\\'entrainement)', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1),
(75, 'Chance', 10, 'Am&eacute;liore le taux de r&eacute;ussite sur toutes les actions utilisant un pourcentage (dont l\\\'entrainement)', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1),
(76, 'R&eacute;parer bâtiment', 1, 'Permet de r&eacute;parer un bâtiment dans lequel on se trouve ou à  port&eacute; de main (adjacent d\'une case)', 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 6, 0, 0, 2, 0, 0, 0, 0, 1, 0, 0),
(77, 'R&eacute;parer bâtiment', 2, 'Permet de r&eacute;parer un bâtiment dans lequel on se trouve ou à  port&eacute; de main (adjacent d\'une case)', 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 6, 0, 0, 2, 0, 0, 0, 0, 1, 0, 0),
(78, 'R&eacute;parer bâtiment', 3, 'Permet de r&eacute;parer un bâtiment dans lequel on se trouve ou à  port&eacute; de main (adjacent d\'une case)', 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 6, 0, 0, 2, 0, 0, 0, 0, 1, 0, 0),
(79, 'R&eacute;parer bâtiment', 4, 'Permet de r&eacute;parer un bâtiment dans lequel on se trouve ou à  port&eacute; de main (adjacent d\'une case)', 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 6, 0, 0, 2, 0, 0, 0, 0, 1, 0, 0),
(80, 'Upgrade bâtiment', 1, 'Permet d\'upgrader un bâtiment dans lequel on se trouve ou à port&eacute; de main (adjacent d\'une case)', 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 10, 0, 0, 2, 0, 0, 0, 0, 1, 0, 0),
(81, 'Upgrade bâtiment', 2, 'Permet d\'upgrader un bâtiment dans lequel on se trouve ou à port&eacute; de main (adjacent d\'une case)', 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 10, 0, 0, 2, 0, 0, 0, 0, 1, 0, 0),
(82, 'Upgrade bâtiment', 3, 'Permet d\'upgrader un bâtiment dans lequel on se trouve ou à port&eacute; de main (adjacent d\'une case)', 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 10, 0, 0, 2, 0, 0, 0, 0, 1, 0, 0),
(83, 'Upgrade bâtiment Expert', 1, 'Permet d\'upgrader un bâtiment jusqu\'au niveau max qu\'on possède dans lequel on se trouve ou à port&eacute; de main (adjacent d\'une case)', 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 10, 0, 0, 1, 0, 0, 0, 0, 1, 0, 0),
(84, 'Upgrade bâtiment Expert', 2, 'Permet d\'upgrader un bâtiment jusqu\'au niveau max qu\'on possède dans lequel on se trouve ou à port&eacute; de main (adjacent d\'une case)', 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 10, 0, 0, 1, 0, 0, 0, 0, 1, 0, 0),
(85, 'Upgrade bâtiment Expert', 3, 'Permet d\'upgrader un bâtiment jusqu\'au niveau max qu\'on possède dans lequel on se trouve ou à port&eacute; de main (adjacent d\'une case)', 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 10, 0, 0, 1, 0, 0, 0, 0, 1, 0, 0),
(86, 'Planter arbre', 1, 'Permet de planter des arbustes sur une case. Au bout d\'une semaine, la forêt apparait', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 8, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0),
(87, 'Ami des animaux', 1, 'Permet d\'&eacute;viter de se faire attaquer par les pnjs', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1),
(88, 'Ami des animaux', 2, 'Permet d\'&eacute;viter de se faire attaquer par les pnj.', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1),
(89, 'Ami des animaux', 3, 'Permet d\'&eacute;viter de se faire attaquer par les pnj.', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1),
(90, 'M&eacute;langer ingr&eacute;dients', 1, 'Permet de m&eacute;langer 2 ingr&eacute;dients afin d\'en obtenir un nouveau... ou pas', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 3, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0),
(91, 'M&eacute;langer ingr&eacute;dients', 2, 'Permet de m&eacute;langer 2 ingr&eacute;dients afin d\'en obtenir un nouveau... ou pas', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 3, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0),
(92, 'M&eacute;langer ingr&eacute;dients', 3, 'Permet de m&eacute;langer 2 ingr&eacute;dients afin d\'en obtenir un nouveau... ou pas', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 3, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0),
(93, 'Alchimie avanc&eacute;e', 1, 'Augmente le taux de r&eacute;ussite des m&eacute;langes', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1),
(94, 'Alchimie avanc&eacute;e', 2, 'Augmente le taux de r&eacute;ussite des m&eacute;langes', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1),
(95, 'Alchimie avanc&eacute;e', 3, 'Augmente le taux de r&eacute;ussite des m&eacute;langes', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1),
(96, 'Danser', 1, 'Ajoute un &eacute;v&eacute;nement de danse dans ses &eacute;v&eacute;nements', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 2, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0),
(97, 'Danser', 2, 'Ajoute un &eacute;v&eacute;nement de danse dans ses &eacute;v&eacute;nements', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 2, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0),
(98, 'Chanter', 1, 'Ajoute un &eacute;v&eacute;nement de chant dans ses &eacute;v&eacute;nements', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 2, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0),
(99, 'Chanter', 2, 'Ajoute un &eacute;v&eacute;nement de chant dans ses &eacute;v&eacute;nements', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 2, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0),
(100, 'Peindre', 1, 'Ajoute un &eacute;v&eacute;nement de peinture dans ses &eacute;v&eacute;nements', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 2, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0),
(101, 'Peindre', 2, 'Ajoute un &eacute;v&eacute;nement de peinture dans ses &eacute;v&eacute;nements', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 2, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0),
(102, 'Sculpter', 1, 'Ajoute un &eacute;v&eacute;nement de sculpture dans ses &eacute;v&eacute;nements', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 2, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0),
(103, 'Sculpter', 2, 'Ajoute un &eacute;v&eacute;nement de sculpture dans ses &eacute;v&eacute;nements', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 2, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0),
(104, 'Saboter', 1, 'permet de d&eacute;truire les routes et les ponts', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 10, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0),
(105, 'Saboter', 2, 'permet de d&eacute;truire les routes et les ponts', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 10, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0),
(106, 'Saboter', 3, 'permet de d&eacute;truire les routes et les ponts', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 10, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0),
(107, 'Marchandage', 1, 'Permet d\'avoir des prix sur les objets, armes et armures', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1),
(108, 'Marchandage', 2, 'Permet d\'avoir des prix sur les objets, armes et armures', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1),
(109, 'Marchandage', 3, 'Permet d\'avoir des prix sur les objets, armes et armures', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1),
(110, 'Deposer objet', 1, 'Action permettant de d&eacute;poser un objet à terre', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0),
(111, 'Ramasser objet', 1, 'Action permettant de ramasser des objets', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0),
(112, 'Nettoyer arme', 1, 'Il est recommander d\'entretenir ses armes r&eacute;gulièrement. Permet de redonner quelques pv à son arme en la nettoyant.', 0, 0, 0, 1, 3, 0, 0, 0, 0, 0, 0, 4, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0),
(113, 'Nettoyer arme', 2, 'Il est recommander d\'entretenir ses armes r&eacute;gulièrement. Permet de redonner quelques pv à son arme en la nettoyant.', 0, 0, 0, 2, 5, 0, 0, 0, 0, 0, 0, 4, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0),
(114, 'Nettoyer arme', 3, 'Il est recommander d\'entretenir ses armes r&eacute;gulièrement. Permet de redonner quelques pv à son arme en la nettoyant.', 0, 0, 0, 3, 8, 0, 0, 0, 0, 0, 0, 4, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0),
(115, 'R&eacute;parer arme', 1, 'A force de manipuler des armes, vous avez appris comment les d&eacute;monter et les r&eacute;parer. Permet de redonner beaucoup de pv à son arme.', 0, 0, 0, 5, 10, 0, 0, 0, 0, 0, 0, 6, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0),
(116, 'R&eacute;parer arme', 2, 'A force de manipuler des armes, vous avez appris comment les d&eacute;monter et les r&eacute;parer. Permet de redonner beaucoup de pv à son arme.', 0, 0, 0, 5, 10, 0, 0, 0, 0, 0, 0, 6, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0),
(117, 'R&eacute;parer arme', 3, 'A force de manipuler des armes, vous avez appris comment les d&eacute;monter et les r&eacute;parer. Permet de redonner beaucoup de pv à son arme.', 0, 0, 0, 8, 15, 0, 0, 0, 0, 0, 0, 6, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0),
(118, 'R&eacute;parer arme', 4, 'A force de manipuler des armes, vous avez appris comment les d&eacute;monter et les r&eacute;parer. Permet de redonner beaucoup de pv à son arme.', 0, 0, 0, 8, 15, 0, 0, 0, 0, 0, 0, 6, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0),
(119, 'R&eacute;parer arme', 5, 'A force de manipuler des armes, vous avez appris comment les d&eacute;monter et les r&eacute;parer. Permet de redonner beaucoup de pv à son arme.', 0, 0, 0, 10, 20, 0, 0, 0, 0, 0, 0, 6, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0),
(120, 'R&eacute;parer arme', 6, 'A force de manipuler des armes, vous avez appris comment les d&eacute;monter et les r&eacute;parer. Permet de redonner beaucoup de pv à son arme.', 0, 0, 0, 10, 20, 0, 0, 0, 0, 0, 0, 6, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0),
(121, 'Nettoyer armure', 1, 'Il est recommand&eacute; d\'entretenir r&eacute;gulièrement ses armures. Permet de redonner quelques pv à ses armures.', 0, 0, 0, 1, 3, 0, 0, 0, 0, 0, 0, 4, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0),
(122, 'Nettoyer armure', 2, 'Il est recommand&eacute; d\'entretenir r&eacute;gulièrement ses armures. Permet de redonner quelques pv à ses armures.', 0, 0, 0, 2, 5, 0, 0, 0, 0, 0, 0, 4, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0),
(123, 'Nettoyer armure', 3, 'Il est recommand&eacute; d\'entretenir r&eacute;gulièrement ses armures. Permet de redonner quelques pv à ses armures.', 0, 0, 0, 3, 8, 0, 0, 0, 0, 0, 0, 4, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0),
(124, 'R&eacute;parer armure', 1, 'A force de rafistoler vos armures, vous avez appris comment le faire efficacement. permet de redonner beaucoup de pv à ses armures.', 0, 0, 0, 5, 10, 0, 0, 0, 0, 0, 0, 6, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0),
(125, 'R&eacute;parer armure', 2, 'A force de rafistoler vos armures, vous avez appris comment le faire efficacement. permet de redonner beaucoup de pv à ses armures.', 0, 0, 0, 5, 10, 0, 0, 0, 0, 0, 0, 6, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0),
(126, 'R&eacute;parer armure', 3, 'A force de rafistoler vos armures, vous avez appris comment le faire efficacement. permet de redonner beaucoup de pv à ses armures.', 0, 0, 0, 8, 15, 0, 0, 0, 0, 0, 0, 6, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0),
(127, 'R&eacute;parer armure', 4, 'A force de rafistoler vos armures, vous avez appris comment le faire efficacement. permet de redonner beaucoup de pv à ses armures.', 0, 0, 0, 8, 15, 0, 0, 0, 0, 0, 0, 6, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0),
(128, 'R&eacute;parer armure', 5, 'A force de rafistoler vos armures, vous avez appris comment le faire efficacement. permet de redonner beaucoup de pv à ses armures.', 0, 0, 0, 10, 20, 0, 0, 0, 0, 0, 0, 6, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0),
(129, 'R&eacute;parer armure', 6, 'A force de rafistoler vos armures, vous avez appris comment le faire efficacement. permet de redonner beaucoup de pv à ses armures.', 0, 0, 0, 10, 20, 0, 0, 0, 0, 0, 0, 6, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0),
(130, 'Defense d\'armure', 1, 'Votre connaissance des armures vous permet d\'utiliser au mieux leurs capacit&eacute;s d&eacute;fensives. Augmente le pourcentage de chance d\'utiliser totalement son bonus d\'armure lors d\'un combat.', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1),
(131, 'Defense d\'armure', 2, 'Votre connaissance des armures vous permet d\'utiliser au mieux leurs capacit&eacute;s d&eacute;fensives. Augmente le pourcentage de chance d\'utiliser totalement son bonus d\'armure lors d\'un combat.', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1),
(132, 'Defense d\'armure', 3, 'Votre connaissance des armures vous permet d\'utiliser au mieux leurs capacit&eacute;s d&eacute;fensives. Augmente le pourcentage de chance d\'utiliser totalement son bonus d\'armure lors d\'un combat.', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1),
(133, 'Defense d\'armure', 4, 'Votre connaissance des armures vous permet d\'utiliser au mieux leurs capacit&eacute;s d&eacute;fensives. Augmente le pourcentage de chance d\'utiliser totalement son bonus d\'armure lors d\'un combat.', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1),
(134, 'Defense d\'armure', 5, 'Votre connaissance des armures vous permet d\'utiliser au mieux leurs capacit&eacute;s d&eacute;fensives. Augmente le pourcentage de chance d\'utiliser totalement son bonus d\'armure lors d\'un combat.', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1),
(135, 'Defense d\'armure', 6, 'Votre connaissance des armures vous permet d\'utiliser au mieux leurs capacit&eacute;s d&eacute;fensives. Augmente le pourcentage de chance d\'utiliser totalement son bonus d\'armure lors d\'un combat.', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1),
(136, 'Defense d\'armure', 7, 'Votre connaissance des armures vous permet d\'utiliser au mieux leurs capacit&eacute;s d&eacute;fensives. Augmente le pourcentage de chance d\'utiliser totalement son bonus d\'armure lors d\'un combat.', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1),
(137, 'Defense d\'armure', 8, 'Votre connaissance des armures vous permet d\'utiliser au mieux leurs capacit&eacute;s d&eacute;fensives. Augmente le pourcentage de chance d\'utiliser totalement son bonus d\'armure lors d\'un combat.', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1),
(138, 'Nudiste inv&eacute;t&eacute;r&eacute;', 1, 'A force de vous balader nu, vous avez appris à recevoir des coup nu. Votre malus de d&eacute;fense du à votre nudit&eacute; disparait.', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1),
(139, 'Donner objet', 1, 'Action permettant de donner un objet à un perso au Corps à corps', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0),
(140, 'Apaiser', 1, 'Permet de soigner les malus d\'une personne', 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 6, 0, 0, 0, 0, 0, 1, 1, 0, 0, 0),
(141, 'Apaiser', 2, 'Permet de soigner les malus d\'une personne', 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 6, 0, 0, 0, 0, 0, 1, 1, 0, 0, 0),
(142, 'Apaiser', 3, 'Permet de soigner les malus d\'une personne', 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 6, 0, 0, 0, 0, 0, 1, 1, 0, 0, 0),
(143, 'Port armes lourdes', 1, 'Permet de baisser les malus dues au port des armes lourdes', 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1),
(144, 'Port armures lourdes', 1, 'Permet de baisser les malus dues au port des armures lourdes', 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1);

-- --------------------------------------------------------

--
-- Structure de la table `action_as_batiment`
--

CREATE TABLE `action_as_batiment` (
  `id_action` int(11) NOT NULL,
  `id_batiment` int(11) NOT NULL,
  `contenance` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Contenu de la table `action_as_batiment`
--

INSERT INTO `action_as_batiment` (`id_action`, `id_batiment`, `contenance`) VALUES
(32, 4, 0),
(33, 1, 0),
(34, 1, 0),
(35, 1, 0),
(36, 1, 0),
(37, 1, 0),
(38, 5, 0),
(39, 5, 0),
(40, 5, 0),
(41, 5, 0),
(42, 5, 0),
(43, 2, 1),
(44, 2, 1),
(45, 2, 1),
(46, 2, 1),
(47, 2, 1),
(48, 3, 1),
(49, 6, 50),
(50, 6, 50),
(51, 6, 50),
(52, 6, 50),
(53, 6, 50),
(54, 7, 5),
(55, 7, 10),
(56, 7, 10),
(57, 7, 15),
(58, 7, 20),
(59, 8, 5),
(60, 8, 10),
(61, 8, 15),
(62, 8, 20),
(63, 8, 25),
(64, 9, 50);

-- --------------------------------------------------------

-- 
-- Structure de la table `arme`
-- 

CREATE TABLE `arme` (
  `id_arme` int(11) NOT NULL auto_increment,
  `nom_arme` varchar(50) NOT NULL default '',
  `porteeMin_arme` int(11) NOT NULL default '0',
  `porteeMax_arme` tinyint(4) NOT NULL default '0',
  `coutPa_arme` int(11) NOT NULL default '0',
  `coutOr_arme` int(11) NOT NULL default '0',
  `additionMin_degats` tinyint(4) NOT NULL default '0',
  `additionMax_degats` tinyint(4) NOT NULL default '0',
  `multiplicateurMin_degats` double NOT NULL default '0',
  `multiplicateurMax_degats` double NOT NULL default '0',
  `degatMin_arme` int(11) NOT NULL default '0',
  `degatMax_arme` int(11) NOT NULL default '0',
  `degatZone_arme` enum('0','1') NOT NULL default '0',
  `bonusPM_arme` int(11) NOT NULL default '0',
  `poids_arme` decimal(10,1) NOT NULL default '0.0',
  `pvMax_arme` int(11) NOT NULL default '0',
  `description_arme` text NOT NULL,
  `qualite_arme` tinyint(4) NOT NULL default '6',
  `main` tinyint(4) NOT NULL default '1',
  `image_arme` varchar(100) default NULL,
  PRIMARY KEY  (`id_arme`)
);

-- --------------------------------------------------------

-- 
-- Structure de la table `armure`
-- 

CREATE TABLE `armure` (
  `id_armure` int(11) NOT NULL auto_increment,
  `nom_armure` varchar(50) NOT NULL default '',
  `coutOr_armure` int(11) NOT NULL default '0',
  `corps_armure` int(11) NOT NULL default '0',
  `bonusDefense_armure` int(11) NOT NULL default '0',
  `bonusDesDefense_armure` int(11) NOT NULL default '0',
  `bonusRecup_armure` int(11) NOT NULL default '0',
  `bonusAttaque_armure` int(11) NOT NULL default '0',
  `bonusPm_armure` int(11) NOT NULL default '0',
  `bonusPv_armure` int(11) NOT NULL default '0',
  `BonusCharge_armure` int(11) NOT NULL default '0',
  `BonusPerception_armure` int(11) NOT NULL default '0',
  `kit` enum('0','1') NOT NULL default '0',
  `id_kit` int(11) default NULL,
  `poids_armure` decimal(10,1) NOT NULL default '0.0',
  `pvMax_armure` int(11) NOT NULL default '0',
  `description_armure` text NOT NULL,
  `qualite_armure` tinyint(4) NOT NULL default '6',
  `image_armure` varchar(200) NOT NULL default '',
  PRIMARY KEY  (`id_armure`)
);

-- --------------------------------------------------------

--
-- Structure de la table `batiment`
--

CREATE TABLE `batiment` (
  `id_batiment` int(11) NOT NULL,
  `nom_batiment` varchar(100) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `pvMax_batiment` int(11) NOT NULL DEFAULT '20',
  `description` text CHARACTER SET latin1 COLLATE latin1_general_ci
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Contenu de la table `batiment`
--

INSERT INTO `batiment` (`id_batiment`, `nom_batiment`, `pvMax_batiment`, `description`) VALUES
(1, 'barricade', 100, 'Une barricade permet de tenir des positions defensives'),
(2, 'tour de visu', 50, 'Une tour de visu permet de mieux rep&eacute;rer le terrain et les positions ennemis'),
(3, 'tour de garde', 50, 'Une tour de garde permet de monter la defense sur une position haute permettant de voir l\'ennemi s\'approcher et ainsi de l\'abattre avant qu\'il n\'atteigne sa position'),
(4, 'route', 10, 'Une route permet de se d&eacute;placer plus facilement sur les terrains difficiles'),
(5, 'pont', 60, 'Un pont permet de traverser facilement des &eacute;tendues d\'eau'),
(6, 'entrepot', 100, 'Un entrepot permet de stocker et vendre du mat&eacute;riel'),
(7, 'hopital', 200, 'Un hopital de campagne permet de soigner des blessers'),
(8, 'fortin', 400, 'Un fortin permet de prendre position sur une partie de la carte'),
(9, 'fort', 1000, 'Un fort, à defendre coute que coute'),
(10, 'prison', 500, 'La prison est un batiment ou sont enferm&eacute;s les criminels');

-- --------------------------------------------------------

--
-- Structure de la table `carte`
--

CREATE TABLE `carte` (
  `x_carte` int(11) NOT NULL DEFAULT '0',
  `y_carte` int(11) NOT NULL DEFAULT '0',
  `occupee_carte` enum('0','1') NOT NULL DEFAULT '0',
  `fond_carte` varchar(20) NOT NULL DEFAULT '',
  `idPerso_carte` int(11) DEFAULT '0',
  `image_carte` varchar(100) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `carte_time`
--

CREATE TABLE `carte_time` (
  `timerefresh` varchar(20) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `competence`
--

CREATE TABLE `competence` (
  `id_competence` int(11) NOT NULL,
  `nom_competence` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `niveau_competence` int(11) NOT NULL DEFAULT '0',
  `nbPoints_competence` int(11) NOT NULL DEFAULT '0',
  `description_competence` text CHARACTER SET latin1 COLLATE latin1_general_ci,
  `cout_competence` int(11) NOT NULL DEFAULT '50'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Contenu de la table `competence`
--

INSERT INTO `competence` (`id_competence`, `nom_competence`, `niveau_competence`, `nbPoints_competence`, `description_competence`, `cout_competence`) VALUES
(1, 'Bon buveur', 0, 1, 'Permet d\'avoir accès aux comp&eacute;tences de bon buveur', 25),
(2, 'Endurance a l\'alcool', 1, 1, 'Enlève les malus li&eacute;s à la consommation d\'alcool', 25),
(3, 'Grand dormeur', 0, 1, 'Permet d\'avoir accès aux comp&eacute;tences de grand dormeur', 25),
(4, 'Dormir', 1, 3, 'Permet de se reposer n\'importe ou et monter sa r&eacute;cup&eacute;ration pour le prochain tour - utilise la totalit&eacute; de ses PA.', 25),
(5, 'Athlète', 0, 1, 'Permet d\'avoir accès aux comp&eacute;tences d\'athlète', 15),
(6, 'Marche forc&eacute;e', 1, 3, 'Permet de se d&eacute;passer afin de gagner un PM', 15),
(7, 'Courir', 1, 3, 'Permet de courir et de gagner des PM pendant 1 tour en consommant tout ses PA - permet de fuir...', 30),
(8, 'Sauter', 2, 1, 'Permet de sauter par dessus un autre perso. Utilise 1PM + cout PM case arriv&eacute;e. Coute 4PA', 50),
(11, 'Medecine', 0, 1, 'Permet d\'avoir accès aux comp&eacute;tences de m&eacute;decine', 20),
(12, 'Premiers soins', 1, 3, 'Permet de se soigner ou de soigner une personne ayant des blessures l&eacute;g&eacute;res (jusqu\'à 10% de PV en moins)', 20),
(13, 'Soins avanc&eacute;s', 2, 3, 'Permet de se soigner ou de soigner une personne ayant des blessures un peu plus graves (jusqu\'à 25% de PV en moins)', 25),
(14, 'Soins v&eacute;t&eacute;rinaire', 3, 5, 'Permet de soigner un de ses animaux de compagnie', 30),
(15, 'Chirurgie', 3, 3, 'Permet de se soigner ou de soigner une personne ayant des blessures graves (jusqu\'à 50% de PV en moins)', 40),
(16, 'Chirurgie de guerre', 4, 3, 'Permet de se soigner ou de soigner une personne ayant des blessures très graves (jusqu\'à 99% de PV en moins)', 50),
(17, 'Minage', 0, 1, 'Permet d\'avoir accès aux comp&eacute;tences de minage', 20),
(18, 'Couper du bois', 1, 3, 'Permet de couper du bois. Fais disparaitre la forêt qu\'on d&eacute;coupe', 25),
(19, 'Miner la montagne', 1, 1, 'Permet de miner une montagne à la recherche de fer', 40),
(20, 'Construction', 0, 1, 'Permet d\'avoir accès aux comp&eacute;tences de construction', 50),
(21, 'Construire - Route', 1, 1, 'Permet de construire une route. La route ne peut être construite adjacente à un QG (le fortin est consid&eacute;r&eacute; comme un QG), un hôpital, un entrepôt ou à une autre route', 20),
(22, 'Construire - Barricade', 1, 5, 'Permet de construire une barricade qui occupe une case', 20),
(23, 'Construire - Pont', 2, 5, 'Permet de construire un pont sur une case d\'eau. Le pont ne peut se construire qu\'à  proximit&eacute; d\'une case de terre ou d\'une autre case de pont', 25),
(24, 'Construire - Tour de visu', 3, 5, 'Permet de construire une tour de visu, occupe une case et peut contenir un perso', 30),
(25, 'Construire - Tour de gard', 4, 1, 'Permet de construire une tour de garde, occupe une case et peut contenir un perso. Le perso peut attaquer depuis la tour avec une arme de distance. +2 en perception', 50),
(26, 'Construire - Entrepot d\'armes', 4, 5, 'Permet de construire un entrepôt d\'arme, occupe une case et les persos à proximit&eacute; peuvent acheter objets, armes et armures', 50),
(27, 'Construire - Hopital', 4, 5, 'Permet de construire un hôpital', 60),
(28, 'Construire - Fortin', 5, 5, 'Permet de construire un fortin. Les persos peuvent respwan dedans', 100),
(29, 'Construire - Fort', 6, 1, 'Permet de construire un fort. Les persos peuvent respwan dedans. Accessible seulement aux anims', 0),
(30, 'Chanceux', 0, 1, 'Permet d\'avoir accès aux comp&eacute;tences de Chanceux', 60),
(31, 'Chance', 1, 10, 'Permet d\'avoir plus de chance', 10),
(32, 'Genie Civil', 0, 1, 'Permet d\'avoir accès aux comp&eacute;tences de G&eacute;nie Civil', 30),
(33, 'R&eacute;parer bâtiment', 1, 4, 'Permet de r&eacute;parer un bâtiment', 20),
(34, 'Upgrade bâtiment', 1, 3, 'Permet d\'upgader un bâtiment vers son niveau suivant', 20),
(35, 'Upgrade bâtiment Expert', 2, 3, 'Permet d\'upgrader un bâtiment directement vers son dernier niveau', 20),
(36, 'Amoureux de la nature', 0, 1, 'Permet d\'avoir accès aux comp&eacute;tences d\'amoureux de la nature', 10),
(37, 'Planter arbre', 1, 1, 'Permet de planter des arbres', 10),
(38, 'Ami des animaux', 1, 3, NULL, 10),
(39, 'Alchimiste', 0, 1, 'Permet d\'avoir accès aux comp&eacute;tences d\'Alchimiste', 30),
(40, 'M&eacute;langer ingr&eacute;dients', 1, 3, NULL, 25),
(41, 'Alchimie avanc&eacute;e', 2, 3, NULL, 10),
(42, 'Artiste', 0, 1, NULL, 10),
(43, 'Danser', 1, 2, NULL, 5),
(44, 'Chanter', 1, 2, NULL, 5),
(45, 'Peindre', 1, 2, NULL, 5),
(46, 'Sculpter', 1, 2, NULL, 5),
(47, 'Saboteur', 0, 1, 'Permet d\'avoir accès aux comp&eacute;tences de Saboteur', 35),
(48, 'Saboter', 1, 3, 'Permet de d&eacute;truire certaines installations comme les ponts et les routes.', 15),
(49, 'Marchand', 0, 1, NULL, 25),
(50, 'Marchandage', 1, 3, NULL, 15),
(51, 'Passionn&eacute;e d\'armes', 0, 1, 'Comp&eacute;tence permettant de d&eacute;bloquer les actions des passionn&eacute;s d\'armes', 50),
(52, 'Nettoyer arme', 1, 3, 'Il est recommander d\'entretenir ses armes r&eacute;gulièrement. Permet de redonner quelques pv à son arme en la nettoyant.', 20),
(53, 'R&eacute;parer arme', 2, 6, 'A force de manipuler des armes, vous avez appris comment les d&eacute;monter et les r&eacute;parer. Permet de redonner beaucoup de pv à son arme.', 30),
(54, 'Passionn&eacute; d\'armures', 0, 1, 'Permet de d&eacute;bloquer les actions des passionn&eacute;s d\'armures', 50),
(55, 'Netoyer armure', 1, 3, 'Il est recommand&eacute; d\'entretenir r&eacute;gulièrement ses armures. Permet de redonner quelques pv à ses armures.', 20),
(56, 'R&eacute;parer armure', 2, 6, 'A force de rafistoler vos armures, vous avez appris comment le faire efficacement. permet de redonner beaucoup de pv à ses armures.', 30),
(57, 'Defense d\'armure', 1, 8, 'Votre connaissance des armures vous permet d\'utiliser au mieux leurs capacit&eacute;s d&eacute;fensives. Augmente le pourcentage de chance d\'utiliser totalement son bonus d\'armure lors d\'un combat.', 10),
(58, 'Nudiste inv&eacute;t&eacute;r&eacute;', 1, 1, 'A force de vous balader nu en pleine nature, vous avez appris à recevoir des coup nu. Votre malus de d&eacute;fense du à votre nudit&eacute; disparait.', 25),
(59, 'Apaiser', 3, 3, 'Permet de soigner les malus d\'une personne', 26),
(60, 'Port armes lourdes', 1, 1, 'Permet de baisser les malus dues au port des armes lourdes', 20),
(61, 'Port armures lourdes', 1, 1, 'Permet de baisser les malus dues au port des armures lourdes', 36),
(62, 'Guerrier', 0, 1, 'Comp&eacute;tence permettant de d&eacute;bloquer les actions des comp&eacute;tences passives des guerriers', 50);

-- --------------------------------------------------------

--
-- Structure de la table `competence_as_action`
--

CREATE TABLE `competence_as_action` (
  `id_competence` int(11) NOT NULL,
  `id_action` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Contenu de la table `competence_as_action`
--

INSERT INTO `competence_as_action` (`id_competence`, `id_action`) VALUES
(4, 1),
(4, 2),
(4, 3),
(6, 4),
(6, 5),
(6, 6),
(7, 7),
(7, 8),
(7, 9),
(8, 10),
(12, 11),
(12, 12),
(12, 13),
(13, 14),
(13, 15),
(13, 16),
(14, 17),
(14, 18),
(14, 19),
(14, 20),
(14, 21),
(15, 22),
(15, 23),
(15, 24),
(16, 25),
(16, 26),
(16, 27),
(18, 28),
(18, 29),
(18, 30),
(19, 31),
(21, 32),
(22, 33),
(22, 34),
(22, 35),
(22, 36),
(22, 37),
(23, 38),
(23, 39),
(23, 40),
(23, 41),
(23, 42),
(24, 43),
(24, 44),
(24, 45),
(24, 46),
(24, 47),
(25, 48),
(26, 49),
(26, 50),
(26, 51),
(26, 52),
(26, 53),
(27, 54),
(27, 55),
(27, 56),
(27, 57),
(27, 58),
(28, 59),
(28, 60),
(28, 61),
(28, 62),
(28, 63),
(29, 64),
(31, 66),
(31, 67),
(31, 68),
(31, 69),
(31, 70),
(31, 71),
(31, 72),
(31, 73),
(31, 74),
(31, 75),
(33, 76),
(33, 77),
(33, 78),
(33, 79),
(34, 80),
(34, 81),
(34, 82),
(35, 83),
(35, 84),
(35, 85),
(37, 86),
(38, 87),
(38, 88),
(38, 89),
(40, 90),
(40, 91),
(40, 92),
(41, 93),
(41, 94),
(41, 95),
(43, 96),
(43, 97),
(44, 98),
(44, 99),
(45, 100),
(45, 101),
(46, 102),
(46, 103),
(48, 104),
(48, 105),
(48, 106),
(50, 107),
(50, 108),
(50, 109),
(52, 112),
(52, 113),
(52, 114),
(53, 115),
(53, 116),
(53, 117),
(53, 118),
(53, 119),
(53, 120),
(55, 121),
(55, 122),
(55, 123),
(56, 124),
(56, 125),
(56, 126),
(56, 127),
(56, 128),
(56, 129),
(57, 130),
(57, 131),
(57, 132),
(57, 133),
(57, 134),
(57, 135),
(57, 136),
(57, 137),
(58, 138),
(59, 140),
(59, 141),
(59, 142),
(60, 143),
(61, 144);

-- --------------------------------------------------------

--
-- Structure de la table `competence_as_competence`
--

CREATE TABLE `competence_as_competence` (
  `id_competence` int(11) NOT NULL DEFAULT '0',
  `id_competence_accessible` int(11) NOT NULL DEFAULT '0',
  `nb_points` int(11) NOT NULL DEFAULT '1',
  `besoin_multiple` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Contenu de la table `competence_as_competence`
--

INSERT INTO `competence_as_competence` (`id_competence`, `id_competence_accessible`, `nb_points`, `besoin_multiple`) VALUES
(1, 2, 1, 0),
(3, 4, 1, 0),
(5, 6, 1, 0),
(5, 7, 1, 0),
(7, 8, 1, 0),
(9, 10, 1, 0),
(10, 26, 1, 1),
(11, 12, 1, 0),
(12, 13, 1, 0),
(13, 14, 1, 0),
(13, 15, 1, 0),
(15, 16, 1, 0),
(15, 27, 1, 1),
(17, 18, 1, 0),
(18, 19, 1, 0),
(20, 21, 1, 0),
(20, 22, 1, 0),
(22, 23, 1, 0),
(22, 24, 1, 0),
(24, 25, 2, 0),
(24, 26, 1, 1),
(24, 27, 1, 1),
(26, 28, 1, 0),
(30, 31, 1, 0),
(32, 33, 1, 0),
(32, 34, 1, 0),
(34, 35, 1, 0),
(36, 37, 1, 0),
(36, 38, 1, 0),
(39, 40, 1, 0),
(40, 41, 1, 0),
(42, 43, 1, 0),
(42, 44, 1, 0),
(42, 45, 1, 0),
(42, 46, 1, 0),
(47, 48, 1, 0),
(49, 50, 1, 0),
(51, 52, 1, 0),
(52, 53, 1, 0),
(54, 55, 1, 0),
(54, 57, 1, 0),
(55, 56, 1, 0),
(36, 58, 1, 0),
(13, 59, 1, 0),
(62, 60, 1, 0),
(62, 61, 1, 0);

-- --------------------------------------------------------

--
-- Structure de la table `config_jeu`
--

CREATE TABLE `config_jeu` (
  `disponible` int(11) NOT NULL DEFAULT '1'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Contenu de la table `config_jeu`
--

INSERT INTO `config_jeu` (`disponible`) VALUES
(1);

-- --------------------------------------------------------

--
-- Structure de la table `contact`
--

CREATE TABLE `contact` (
  `id_contact` int(11) NOT NULL,
  `nom_contact` varchar(50) NOT NULL DEFAULT 'amis',
  `contacts` varchar(250) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `cv`
--

CREATE TABLE `cv` (
  `ID_cv` int(11) NOT NULL,
  `IDActeur_cv` int(11) NOT NULL DEFAULT '0',
  `nomActeur_cv` varchar(100) NOT NULL DEFAULT '',
  `IDCible_cv` int(11) DEFAULT NULL,
  `nomCible_cv` varchar(100) DEFAULT NULL,
  `date_cv` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `dossier`
--

CREATE TABLE `dossier` (
  `id_dossier` int(11) NOT NULL,
  `nom_dossier` varchar(100) NOT NULL DEFAULT 'sans_nom'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `evenement`
--

CREATE TABLE `evenement` (
  `ID_evenement` int(11) NOT NULL,
  `IDActeur_evenement` int(11) NOT NULL DEFAULT '0',
  `nomActeur_evenement` varchar(100) NOT NULL DEFAULT '',
  `phrase_evenement` varchar(250) NOT NULL,
  `IDCible_evenement` int(11) DEFAULT NULL,
  `nomCible_evenement` varchar(100) DEFAULT NULL,
  `effet_evenement` varchar(250) DEFAULT NULL,
  `date_evenement` datetime NOT NULL,
  `special` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `instance_batiment`
--

CREATE TABLE `instance_batiment` (
  `id_instanceBat` int(11) NOT NULL,
  `niveau_instance` tinyint(1) NOT NULL DEFAULT '1',
  `id_batiment` int(11) NOT NULL DEFAULT '1',
  `nom_instance` varchar(100) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `pv_instance` int(11) NOT NULL DEFAULT '20',
  `pvMax_instance` int(11) NOT NULL DEFAULT '20',
  `x_instance` int(11) DEFAULT NULL,
  `y_instance` int(11) DEFAULT NULL,
  `camp_instance` int(11) DEFAULT '4',
  `contenance_instance` int(11) DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `instance_pnj`
--

CREATE TABLE `instance_pnj` (
  `idInstance_pnj` int(11) NOT NULL,
  `id_pnj` int(11) NOT NULL DEFAULT '0',
  `pv_i` int(11) NOT NULL DEFAULT '0',
  `pm_i` int(11) NOT NULL DEFAULT '0',
  `deplace_i` enum('0','1') NOT NULL DEFAULT '0',
  `dernierAttaquant_i` int(11) NOT NULL DEFAULT '0',
  `x_i` int(11) DEFAULT '0',
  `y_i` int(11) DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `joueur`
--

CREATE TABLE `joueur` (
  `id_joueur` int(11) NOT NULL,
  `nom_joueur` varchar(100) DEFAULT NULL,
  `email_joueur` varchar(100) NOT NULL DEFAULT '',
  `mdp_joueur` varchar(100) NOT NULL DEFAULT '',
  `age_joueur` int(11) DEFAULT NULL,
  `pays_joueur` varchar(100) DEFAULT NULL,
  `region_joueur` varchar(100) DEFAULT NULL,
  `description_joueur` text,
  `mail_info` tinyint(1) NOT NULL DEFAULT '0',
  `admin_perso` enum('0','1') NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `joueur_as_ip`
--

CREATE TABLE `joueur_as_ip` (
  `id_joueur` int(11) NOT NULL DEFAULT '0',
  `ip_joueur` varchar(100) NOT NULL DEFAULT '000.000.000.000'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `message`
--

CREATE TABLE `message` (
  `id_message` int(11) NOT NULL,
  `expediteur_message` text NOT NULL,
  `date_message` datetime NOT NULL,
  `contenu_message` longtext NOT NULL,
  `objet_message` text
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `message_perso`
--

CREATE TABLE `message_perso` (
  `id_message` int(11) NOT NULL DEFAULT '0',
  `id_perso` int(11) NOT NULL DEFAULT '0',
  `id_dossier` int(11) NOT NULL DEFAULT '0',
  `lu_message` int(11) NOT NULL DEFAULT '0',
  `supprime_message` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `nb_online`
--

CREATE TABLE `nb_online` (
  `ip` varchar(15) NOT NULL DEFAULT '',
  `time` bigint(16) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `news`
--

CREATE TABLE `news` (
  `id_news` int(10) UNSIGNED NOT NULL,
  `id_admin` int(11) NOT NULL DEFAULT '0',
  `date` datetime NOT NULL,
  `contenu` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

CREATE TABLE `objet` (
  `id_objet` int(11) NOT NULL auto_increment,
  `nom_objet` varchar(50) NOT NULL default '',
  `portee_objet` int(11) NOT NULL default '0',
  `bonusPerception_objet` int(11) NOT NULL default '0',
  `bonusRecup_objet` int(11) NOT NULL default '0',
  `bonusPv_objet` int(11) NOT NULL default '0',
  `bonusPm_objet` int(11) NOT NULL default '0',
  `coutPa_objet` int(11) NOT NULL default '0',
  `coutOr_objet` int(11) NOT NULL default '0',
  `poids_objet` decimal(10,1) NOT NULL default '0.0',
  `description_objet` text NOT NULL,
  `type_objet` varchar(3) NOT NULL default 'N',
  PRIMARY KEY  (`id_objet`)
);

-- --------------------------------------------------------

--
-- Structure de la table `objet_in_carte`
--

CREATE TABLE `objet_in_carte` (
  `type_objet` tinyint(4) NOT NULL DEFAULT '1',
  `id_objet` int(11) NOT NULL DEFAULT '0',
  `nb_objet` int(11) NOT NULL DEFAULT '0',
  `x_carte` int(11) NOT NULL DEFAULT '-1',
  `y_carte` int(11) NOT NULL DEFAULT '-1',
  `pv_objet` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `perso`
--

CREATE TABLE `perso` (
  `id_perso` int(11) NOT NULL,
  `idJoueur_perso` int(11) NOT NULL DEFAULT '0',
  `nom_perso` varchar(50) NOT NULL DEFAULT '',
  `type_perso` int(11) NOT NULL DEFAULT '1',
  `x_perso` int(11) NOT NULL DEFAULT '0',
  `y_perso` int(11) NOT NULL DEFAULT '0',
  `xp_perso` int(11) NOT NULL DEFAULT '0',
  `pi_perso` int(11) NOT NULL DEFAULT '0',
  `pc_perso` int(11) NOT NULL DEFAULT '0',
  `deAttaque_perso` int(11) NOT NULL DEFAULT '1',
  `deDefense_perso` int(11) NOT NULL DEFAULT '1',
  `changementDe_perso` enum('0','1') NOT NULL DEFAULT '0',
  `or_perso` int(11) NOT NULL DEFAULT '20',
  `pvMax_perso` int(11) NOT NULL DEFAULT '0',
  `pm_perso` int(11) NOT NULL DEFAULT '5',
  `pmMax_perso` int(11) NOT NULL DEFAULT '5',
  `pv_perso` int(11) NOT NULL DEFAULT '0',
  `perception_perso` int(11) NOT NULL DEFAULT '0',
  `recup_perso` int(11) NOT NULL DEFAULT '0',
  `pa_perso` int(11) NOT NULL DEFAULT '0',
  `paMax_perso` int(11) NOT NULL DEFAULT '10',
  `charge_perso` int(11) NOT NULL DEFAULT '0',
  `chargeMax_perso` int(11) NOT NULL DEFAULT '5',
  `niveau_perso` int(11) NOT NULL DEFAULT '1',
  `bonusPerception_perso` int(11) NOT NULL DEFAULT '0',
  `bonusRecup_perso` int(11) NOT NULL DEFAULT '0',
  `bonusPM_perso` int(11) NOT NULL DEFAULT '0',
  `bonus_perso` int(11) NOT NULL DEFAULT '0',
  `image_perso` varchar(200) NOT NULL DEFAULT '',
  `message_perso` text NOT NULL,
  `bourre_perso` int(11) NOT NULL DEFAULT '0',
  `nb_kill` int(11) NOT NULL DEFAULT '0',
  `nb_mort` int(11) NOT NULL DEFAULT '0',
  `nb_pnj` int(11) NOT NULL DEFAULT '0',
  `dateCreation_perso` datetime DEFAULT NULL,
  `DLA_perso` datetime DEFAULT NULL,
  `description_perso` longtext,
  `clan` tinyint(4) NOT NULL,
  `arene` enum('0','1') NOT NULL DEFAULT '0',
  `a_gele` tinyint(1) NOT NULL DEFAULT '0',
  `est_gele` tinyint(1) NOT NULL DEFAULT '0',
  `date_gele` datetime DEFAULT NULL,
  `mainPrincipale_perso` tinyint(4) NOT NULL DEFAULT '1'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `perso_as_arme`
-- 

CREATE TABLE `perso_as_arme` (
  `id_perso` int(11) NOT NULL default '0',
  `id_arme` int(11) NOT NULL default '0',
  `est_portee` enum('0','1') NOT NULL default '0',
  `pv_arme` int(11) NOT NULL default '0',
  `mains` tinyint(4) NOT NULL default '1'
);

-- --------------------------------------------------------

-- 
-- Structure de la table `perso_as_armure`
-- 

CREATE TABLE `perso_as_armure` (
  `id_perso` int(11) NOT NULL default '0',
  `id_armure` int(11) NOT NULL default '0',
  `est_portee` enum('0','1') NOT NULL default '0',
  `corps_armure` int(11) default NULL,
  `pv_armure` int(11) NOT NULL default '0'
);

-- --------------------------------------------------------


--
-- Structure de la table `perso_as_competence`
--

CREATE TABLE `perso_as_competence` (
  `id_perso` int(11) NOT NULL,
  `id_competence` int(11) NOT NULL,
  `nb_points` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `perso_as_contact`
--

CREATE TABLE `perso_as_contact` (
  `id_perso` int(11) NOT NULL DEFAULT '0',
  `id_contact` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `perso_as_dossiers`
--

CREATE TABLE `perso_as_dossiers` (
  `id_perso` int(11) NOT NULL,
  `id_dossier` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `perso_as_entrainement`
--

CREATE TABLE `perso_as_entrainement` (
  `id_perso` int(11) NOT NULL,
  `niveau_entrainement` int(11) NOT NULL,
  `nb` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `perso_as_killpnj`
--

CREATE TABLE `perso_as_killpnj` (
  `id_perso` int(11) NOT NULL DEFAULT '0',
  `id_pnj` int(11) NOT NULL DEFAULT '0',
  `nb_pnj` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `perso_as_objet`
-- 

CREATE TABLE `perso_as_objet` (
  `id_perso` int(11) NOT NULL default '0',
  `id_objet` int(11) NOT NULL default '0'
);

-- --------------------------------------------------------

--
-- Structure de la table `perso_in_batiment`
--

CREATE TABLE `perso_in_batiment` (
  `id_perso` int(11) NOT NULL DEFAULT '0',
  `id_instanceBat` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `perso_in_section`
--

CREATE TABLE `perso_in_section` (
  `id_perso` int(11) NOT NULL DEFAULT '0',
  `id_section` int(11) NOT NULL DEFAULT '0',
  `poste_section` int(11) NOT NULL DEFAULT '0',
  `attenteValidation_section` int(11) NOT NULL DEFAULT '1'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `pnj`
--

CREATE TABLE `pnj` (
  `id_pnj` int(11) NOT NULL,
  `nom_pnj` varchar(50) NOT NULL DEFAULT '',
  `pvMax_pnj` int(11) NOT NULL DEFAULT '0',
  `degatMin_pnj` int(11) NOT NULL DEFAULT '0',
  `degatMax_pnj` int(11) NOT NULL DEFAULT '0',
  `pm_pnj` int(11) NOT NULL DEFAULT '0',
  `recup_pnj` int(11) NOT NULL DEFAULT '0',
  `perception_pnj` int(11) NOT NULL DEFAULT '0',
  `aggressivite_pnj` int(11) NOT NULL DEFAULT '0',
  `de_pnj` int(11) NOT NULL DEFAULT '0',
  `description_pnj` text
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `pnj_as_objet`
--

CREATE TABLE `pnj_as_objet` (
  `idInstance_pnj` int(11) NOT NULL DEFAULT '0',
  `id_objet` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `pnj_in_zone`
--

CREATE TABLE `pnj_in_zone` (
  `id_pnj` int(11) NOT NULL DEFAULT '0',
  `id_zone` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `ressources_entrepot`
--

CREATE TABLE `ressources_entrepot` (
  `id_entrepot` int(11) NOT NULL DEFAULT '0',
  `id_ressource` int(11) NOT NULL DEFAULT '0',
  `nb_ressource` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `sections`
--

CREATE TABLE `sections` (
  `id_section` int(11) NOT NULL,
  `nom_section` varchar(25) NOT NULL DEFAULT '0',
  `image_section` varchar(255) NOT NULL DEFAULT '0',
  `resume_section` text NOT NULL,
  `description_section` longtext NOT NULL,
  `id_clan` tinyint(4) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `stats_camp_kill`
--

CREATE TABLE `stats_camp_kill` (
  `id_camp` tinyint(4) NOT NULL DEFAULT '0',
  `nb_kill` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `zones`
--

CREATE TABLE `zones` (
  `id_zone` int(11) NOT NULL DEFAULT '0',
  `xMin_zone` int(11) NOT NULL DEFAULT '0',
  `xMax_zone` int(11) NOT NULL DEFAULT '0',
  `yMin_zone` int(11) NOT NULL DEFAULT '0',
  `yMax_zone` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Index pour les tables export&eacute;es
--

--
-- Index pour la table `action`
--
ALTER TABLE `action`
  ADD PRIMARY KEY (`id_action`);

--
-- Index pour la table `action_as_batiment`
--
ALTER TABLE `action_as_batiment`
  ADD PRIMARY KEY (`id_action`,`id_batiment`);

--
-- Index pour la table `batiment`
--
ALTER TABLE `batiment`
  ADD PRIMARY KEY (`id_batiment`);

--
-- Index pour la table `carte`
--
ALTER TABLE `carte`
  ADD UNIQUE KEY `x_carte` (`x_carte`,`y_carte`);

--
-- Index pour la table `competence`
--
ALTER TABLE `competence`
  ADD PRIMARY KEY (`id_competence`);

--
-- Index pour la table `competence_as_action`
--
ALTER TABLE `competence_as_action`
  ADD PRIMARY KEY (`id_competence`,`id_action`);

--
-- Index pour la table `competence_as_competence`
--
ALTER TABLE `competence_as_competence`
  ADD PRIMARY KEY (`id_competence`,`id_competence_accessible`);

--
-- Index pour la table `contact`
--
ALTER TABLE `contact`
  ADD PRIMARY KEY (`id_contact`);

--
-- Index pour la table `cv`
--
ALTER TABLE `cv`
  ADD PRIMARY KEY (`ID_cv`);

--
-- Index pour la table `dossier`
--
ALTER TABLE `dossier`
  ADD PRIMARY KEY (`id_dossier`);

--
-- Index pour la table `evenement`
--
ALTER TABLE `evenement`
  ADD PRIMARY KEY (`ID_evenement`);

--
-- Index pour la table `instance_batiment`
--
ALTER TABLE `instance_batiment`
  ADD PRIMARY KEY (`id_instanceBat`);

--
-- Index pour la table `instance_pnj`
--
ALTER TABLE `instance_pnj`
  ADD PRIMARY KEY (`idInstance_pnj`);

--
-- Index pour la table `joueur`
--
ALTER TABLE `joueur`
  ADD PRIMARY KEY (`id_joueur`),
  ADD UNIQUE KEY `id_perso` (`nom_joueur`);

--
-- Index pour la table `message`
--
ALTER TABLE `message`
  ADD PRIMARY KEY (`id_message`);

--
-- Index pour la table `message_perso`
--
ALTER TABLE `message_perso`
  ADD PRIMARY KEY (`id_message`,`id_perso`);

--
-- Index pour la table `news`
--
ALTER TABLE `news`
  ADD PRIMARY KEY (`id_news`);

--
-- Index pour la table `perso`
--
ALTER TABLE `perso`
  ADD PRIMARY KEY (`id_perso`),
  ADD UNIQUE KEY `id_joueur` (`idJoueur_perso`);

--
-- Index pour la table `perso_as_competence`
--
ALTER TABLE `perso_as_competence`
  ADD PRIMARY KEY (`id_perso`,`id_competence`);

--
-- Index pour la table `perso_as_contact`
--
ALTER TABLE `perso_as_contact`
  ADD PRIMARY KEY (`id_perso`,`id_contact`);

--
-- Index pour la table `perso_as_entrainement`
--
ALTER TABLE `perso_as_entrainement`
  ADD PRIMARY KEY (`id_perso`);

--
-- Index pour la table `perso_as_killpnj`
--
ALTER TABLE `perso_as_killpnj`
  ADD PRIMARY KEY (`id_perso`,`id_pnj`);

--
-- Index pour la table `perso_in_batiment`
--
ALTER TABLE `perso_in_batiment`
  ADD PRIMARY KEY (`id_perso`,`id_instanceBat`);

--
-- Index pour la table `pnj`
--
ALTER TABLE `pnj`
  ADD PRIMARY KEY (`id_pnj`);

--
-- Index pour la table `pnj_in_zone`
--
ALTER TABLE `pnj_in_zone`
  ADD PRIMARY KEY (`id_pnj`,`id_zone`);

--
-- Index pour la table `ressources_entrepot`
--
ALTER TABLE `ressources_entrepot`
  ADD UNIQUE KEY `id_entrepot` (`id_entrepot`,`id_ressource`);

--
-- Index pour la table `sections`
--
ALTER TABLE `sections`
  ADD PRIMARY KEY (`id_section`),
  ADD UNIQUE KEY `nom_section` (`nom_section`);

--
-- Index pour la table `zones`
--
ALTER TABLE `zones`
  ADD PRIMARY KEY (`id_zone`);

--
-- AUTO_INCREMENT pour les tables export&eacute;es
--

--
-- AUTO_INCREMENT pour la table `action`
--
ALTER TABLE `action`
  MODIFY `id_action` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=145;
--
-- AUTO_INCREMENT pour la table `batiment`
--
ALTER TABLE `batiment`
  MODIFY `id_batiment` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
--
-- AUTO_INCREMENT pour la table `competence`
--
ALTER TABLE `competence`
  MODIFY `id_competence` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;
--
-- AUTO_INCREMENT pour la table `contact`
--
ALTER TABLE `contact`
  MODIFY `id_contact` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `cv`
--
ALTER TABLE `cv`
  MODIFY `ID_cv` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `dossier`
--
ALTER TABLE `dossier`
  MODIFY `id_dossier` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT pour la table `evenement`
--
ALTER TABLE `evenement`
  MODIFY `ID_evenement` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `instance_batiment`
--
ALTER TABLE `instance_batiment`
  MODIFY `id_instanceBat` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50000;
--
-- AUTO_INCREMENT pour la table `instance_pnj`
--
ALTER TABLE `instance_pnj`
  MODIFY `idInstance_pnj` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `joueur`
--
ALTER TABLE `joueur`
  MODIFY `id_joueur` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
--
-- AUTO_INCREMENT pour la table `message`
--
ALTER TABLE `message`
  MODIFY `id_message` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT pour la table `news`
--
ALTER TABLE `news`
  MODIFY `id_news` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `perso`
--
ALTER TABLE `perso`
  MODIFY `id_perso` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT pour la table `pnj`
--
ALTER TABLE `pnj`
  MODIFY `id_pnj` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `sections`
--
ALTER TABLE `sections`
  MODIFY `id_section` int(11) NOT NULL AUTO_INCREMENT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
