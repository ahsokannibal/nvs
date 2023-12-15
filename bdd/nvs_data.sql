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
-- Création par défaut de la zone de respawn du camp neutre. Adapté à une carte de 200x200
--
insert into zone_respawn_camp (id_camp, x_min_zone, x_max_zone, y_min_zone, y_max_zone) VALUES (0, 190, 200, 0, 10);

--
-- Contenu de la table `action`
--

INSERT INTO `action` (`id_action`, `nom_action`, `nb_points`, `description_action`, `portee_action`, `perceptionMin_action`, `perceptionMax_action`, `pvMin_action`, `pvMax_action`, `nb_des_action`, `valeur_des_action`, `recupMin_action`, `recupMax_action`, `pmMin_action`, `pmMax_action`, `DefMin_action`, `DefMax_action`, `coutPa_action`, `nbreTourMin`, `nbreTourMax`, `coutOr_action`, `coutBois_action`, `coutFer_action`, `reflexive_action`, `cible_action`, `case_action`, `pnj_action`, `passif_action`) VALUES
(1, 'Sieste', 1, 'Permet de se reposer n\'importe ou et monter sa r&eacute;cup&eacute;ration pour le prochain tour - utilise la totalit&eacute; de ses PA', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0),
(4, 'Marche forc&eacute;e', 1, 'Permet de se d&eacute;passer afin de gagner un PM', 0, 0, 0, -10, -10, 0, 0, 0, 0, 1, 1, 0, 0, 4, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0),
(11, 'Soins', 1, 'Permet de se soigner ou de soigner une personne ayant des blessures', 1, 0, 0, 0, 0, 20, 6, 0, 0, 0, 0, 0, 0, 6, 0, 0, 0, 0, 0, 1, 1, 0, 0, 0),
(33, 'Construire - Barricade', 1, 'Permet de construire une barricade qui occupe une case', 1, 0, 0, 60, 250, 0, 0, 0, 0, 0, 0, 0, 0, 5, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0),
(38, 'Construire - Pont', 1, 'Permet de construire un pont sur une case d\'eau. Le pont ne peut se construire qu\'à proximit&eacute; d\'une case de terre ou d\'une autre case de pont', 1, 0, 0, 50, 200, 0, 0, 0, 0, 0, 0, 0, 0, 10, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0),
(43, 'Construire - Tour de guet', 1, 'Permet de construire une tour de visu, occupe une case et peut contenir un perso', 1, 0, 0, 50, 250, 0, 0, 0, 0, 0, 0, 0, 0, 10, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0),
(54, 'Construire - Hopital', 1, 'Permet de construire un hôpital', 1, 0, 0, 100, 1000, 0, 0, 0, 0, 0, 0, 0, 0, 10, 0, 0, 250, 0, 0, 0, 0, 1, 0, 0),
(59, 'Construire - Fortin', 1, 'Permet de construire un fortin. Les persos peuvent respawn dedans', 1, 0, 0, 300, 6000, 0, 0, 0, 0, 0, 0, 0, 0, 10, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0),
(64, 'Construire - Fort', 1, 'Permet de construire un fort. Les persos peuvent respawn dedans. Accessible seulement aux anims', 1, 0, 0, 5000, 10000, 0, 0, 0, 0, 0, 0, 0, 0, 10, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0),
(65, 'Entrainement', 1, 'Permet de s\'entrainer', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 10, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0),
(76, 'R&eacute;parer bâtiment', 1, 'Permet de r&eacute;parer un bâtiment dans lequel on se trouve ou à  port&eacute; de main (adjacent d\'une case)', 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 5, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0),
(87, 'Ami des animaux', 1, 'Permet d\'&eacute;viter de se faire attaquer par les pnjs', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1),
(104, 'Saboter', 1, 'permet de d&eacute;truire les routes et les ponts', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 10, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0),
(107, 'Marchandage', 1, 'Permet d\'avoir des prix sur les objets, armes et armures', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1),
(108, 'Marchandage', 2, 'Permet d\'avoir des prix sur les objets, armes et armures', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1),
(109, 'Marchandage', 3, 'Permet d\'avoir des prix sur les objets, armes et armures', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1),
(110, 'Deposer objet', 1, 'Action permettant de d&eacute;poser un objet à terre', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0),
(111, 'Ramasser objet', 1, 'Action permettant de ramasser des objets', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0),
(139, 'Donner objet', 1, 'Action permettant de donner un objet à un perso au Corps à corps', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0),
(140, 'Apaiser', 1, 'Permet de soigner les malus d\'une personne', 1, 0, 0, 0, 0, 2, 6, 0, 0, 0, 0, 0, 0, 6, 0, 0, 0, 0, 0, 1, 1, 0, 0, 0),
(145, 'Bousculer', 1, 'Permet de bouculer quelqu\'un', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 3, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(146, 'Construire - Gare', 1, 'Permet de construire une gare.', 1, 0, 0, 250, 5000, 0, 0, 0, 0, 0, 0, 0, 0, 10, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0),
(147, 'Construire - Rail', 1, 'Permet de construire une portion de rail.', 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 4, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0),
(148, 'Construire - Point stratégique', 1, 'Permet de construire un point stratégique.', 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 10, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0);

-- --------------------------------------------------------

--
-- Contenu de la table `action_as_batiment`
--

INSERT INTO `action_as_batiment` (`id_action`, `id_batiment`, `contenance`) VALUES
(33, 1, 0),
(38, 5, 0),
(43, 2, 3),
(54, 7, 50),
(59, 8, 50),
(64, 9, 100),
(146, 11, 50),
(148, 13, 1);


-- --------------------------------------------------------

--
-- Contenu de la table `arme`
--

INSERT INTO `arme` (`id_arme`, `nom_arme`, `porteeMin_arme`, `porteeMax_arme`, `coutPa_arme`, `coutOr_arme`, `additionMin_degats`, `additionMax_degats`, `multiplicateurMin_degats`, `multiplicateurMax_degats`, `degatMin_arme`, `degatMax_arme`, `valeur_des_arme`, `precision_arme`, `degatZone_arme`, `bonusPM_arme`, `poids_arme`, `pvMax_arme`, `description_arme`, `qualite_arme`, `main`, `image_arme`) VALUES
(1, 'Sabre', 1, 1, 4, 0, 0, 0, 0, 0, 20, 20, 6, 80, '0', 0, '2.0', 0, '', 0, 1, 'sabre.png'),
(2, 'Sabre lourd', 1, 1, 5, 250, 0, 0, 0, 0, 25, 25, 6, 80, '0', 0, '2.5', 0, '', 0, 1, 'sabre_lourd.png'),
(3, 'Cailloux', 1, 2, 3, 0, 0, 0, 0, 0, 5, 5, 6, 25, '0', 0, '0.0', 0, '', 0, 1, 'cailloux.png'),
(4, 'Pistolet', 1, 2, 3, 0, 0, 0, 0, 0, 15, 15, 6, 40, '0', 0, '1.5', 0, '', 0, 1, 'pistolet.png'),
(5, 'Pistolet Canon Long', 1, 2, 3, 100, 0, 0, 0, 0, 16, 16, 6, 60, '0', 0, '2.0', 0, '', 0, 1, 'pistolet_canon_long.png'),
(6, 'Baïonnette', 1, 1, 3, 0, 0, 0, 0, 0, 14, 14, 8, 70, '0', 0, '0.5', 0, '', 0, 1, 'baionnette.png'),
(7, 'Fusil', 1, 3, 5, 0, 0, 0, 0, 0, 20, 20, 6, 80, '0', 0, '1.6', 0, '', 0, 1, 'fusil.png'),
(8, 'Fusil Précision', 1, 4, 5, 250, 0, 0, 0, 0, 20, 20, 6, 90, '0', 0, '1.6', 0, '', 0, 1, 'fusil_precision.png'),
(9, 'Canines', 1, 1, 10, 0, 0, 0, 0, 0, 15, 15, 4, 90, '0', 0, '0.0', 0, '', 0, 1, 'morsure.png'),
(10, 'Seringue', 1, 1, 5, 50, 0, 0, 0, 0, 20, 20, 6, 90, '0', 0, '0.1', 0, 'Seringue pour soigner', 0, 1, 'seringue.png'),
(11, 'Bandages', 1, 1, 3, 50, 0, 0, 0, 0, 2, 2, 10, 35, '0', 0, '0.2', 0, 'Bandages permettant de récupérer des malus de defense', 0, 1, 'bandage.png'),
(12, 'Griffes', 1, 1, 10, 0, 0, 0, 0, 0, 15, 15, 4, 90, '0', 0, '0.0', 0, '', 0, 1, 'griffe.png'),
(13, 'Canon', 2, 6, 6, 0, 0, 0, 0, 0, 75, 75, 6, 65, '1', 0, '0.0', 0, 'Canon d\'artillerie, extrêmement dévastateur et efficace contre les bâtiments.', 0, 1, 'canon.png'),
(14, 'Gatling', 2, 5, 5, 0, 0, 0, 0, 0, 10, 10, 22, 75, '1', 0, '4.0', 0, 'Gatling, faucheuse moderne', 0, 1, 'gatling.png'),
(15, 'Magnum', '1', '2', '3', '150', '0', '0', '0', '0', '16', '16', '8', '40', '0', '0', '2.0', '0', '', '0', '1', 'magnum.png'),
(16, 'Couteau', '1', '1', '3', '10', '0', '0', '0', '0', '15', '15', '4', '35', '0', '0', '0.0', '0', '', '0', '1', 'couteau.png'),
(17, 'Canon double', '1', '3', '5', '225', '0', '0', '0', '0', '20', '20', '8', '75', '0', '0', '3.0', '0', '', '0', '1', 'canon_double.png'),
(18, 'Sabre normal', 1, 1, 4, 50, 0, 0, 0, 0, 20, 20, 6, 80, '0', 0, '2.0', 0, '', 0, 1, 'sabre.png'),
(19, 'Pistolet normal', 1, 2, 3, 50, 0, 0, 0, 0, 15, 15, 6, 40, '0', 0, '1.5', 0, '', 0, 1, 'pistolet.png'),
(20, 'Baïonnette normale', 1, 1, 3, 50, 0, 0, 0, 0, 14, 14, 8, 70, '0', 0, '0.5', 0, '', 0, 1, 'baionnette.png'),
(21, 'Fusil normal', 1, 3, 5, 50, 0, 0, 0, 0, 20, 20, 6, 80, '0', 0, '1.6', 0, '', 0, 1, 'fusil.png'),
(22, 'Canon normal', 2, 6, 6, 200, 0, 0, 0, 0, 75, 75, 6, 65, '1', 0, '0.0', 0, 'Canon d\'artillerie, extrêmement dévastateur et efficace contre les bâtiments.', 0, 1, 'canon.png'),
(23, 'Carabine Spencer', 1, 3, 5, 150, 0, 0, 0, 0, 20, 20, 6, 60, '0', 0, '4.0', 0, '', 0, 1, 'carabine_spencer.png'),
(24, 'Sabre léger', '1', '1', '4', '0', '0', '0', '0', '0', '16', '16', '6', '80', '0', '0', '0.0', '0', '', '0', '1', 'sabre_leger.png');


-- --------------------------------------------------------

--
-- Contenu de la table `arme_as_type_unite`
--

INSERT INTO `arme_as_type_unite` (`id_arme`, `id_type_unite`) VALUES
(1, 1),
(1, 2),
(2, 1),
(2, 2),
(3, 1),
(3, 2),
(3, 7),
(4, 1),
(4, 2),
(4, 7),
(5, 1),
(5, 2),
(5, 7),
(6, 3),
(7, 3),
(8, 3),
(10, 4),
(11, 4),
(9, 6),
(12, 6),
(13, 5),
(14, 8),
(15, 1),
(15, 2),
(15, 7),
(16, 1),
(16, 2),
(16, 7),
(17, 3),
(18, 1),
(18, 2),
(19, 1),
(19, 2),
(19, 7),
(20, 3),
(21, 3),
(22, 5),
(23, 1),
(23, 2),
(24, 7);

-- --------------------------------------------------------

--
-- Contenu de la table `objet_as_type_unite`
--

INSERT INTO `objet_as_type_unite` (`id_objet`, `id_type_unite`) VALUES
(5, 3),
(5, 4),
(5, 5),
(5, 8),
(6, 1),
(6, 2),
(6, 3),
(6, 4),
(6, 5),
(6, 8),
(6, 7),
(7, 1),
(7, 2),
(7, 3),
(7, 4),
(7, 5),
(7, 8),
(7, 7),
(8, 1),
(9, 1);
-- --------------------------------------------------------

--
-- Contenu de la table `batiment`
--

INSERT INTO `batiment` (`id_batiment`, `nom_batiment`, `pvMax_batiment`, `description`, `taille_batiment`) VALUES
(1, 'Barricade', 250, 'Une barricade permet de tenir des positions defensives', 1),
(2, 'Tour de guet', 250, 'Une tour de visu permet de mieux rep&eacute;rer le terrain et les positions ennemis', 1),
(5, 'Pont', 200, 'Un pont permet de traverser facilement des &eacute;tendues d\'eau', 1),
(6, 'Entrepot', 1000, 'Un entrepot permet de stocker et vendre du mat&eacute;riel', 1),
(7, 'Hopital', 750, 'Un hopital de campagne permet de soigner des blessés', 1),
(8, 'Fortin', 6000, 'Un fortin permet de prendre position sur une partie de la carte', 3),
(9, 'Fort', 10000, 'Un fort, à defendre coute que coute', 5),
(10, 'Pénitencier', 15000, 'La prison est un batiment ou sont enferm&eacute;s les criminels', 3),
(11, 'Gare', 5000, 'Une gare', 3),
(12, 'Train', 2500, 'Un train', 1),
(13, 'Point stratégique', 100000, 'Point stratégique dont le controle rapporte des points de victoire', 1);

-- --------------------------------------------------------

--
-- Contenu de la table `banque_as_compagnie`
--

INSERT INTO `banque_as_compagnie` (`id_compagnie`, `montant`) VALUES
('1', '0'),
('2', '0');

-- --------------------------------------------------------

--
-- Contenu de la table `banque_compagnie`
--

INSERT INTO `banque_compagnie` (`id_perso`, `montant`, `demande_emprunt`, `montant_emprunt`) VALUES
('1', '0', '0', '0'),
('2', '0', '0', '0');

-- --------------------------------------------------------

--
-- Contenu de la table `carte_time`
--

INSERT INTO `carte_time` (`timerefresh`) VALUES ('0');

--
-- Contenu de la table `compagnies`
--

INSERT INTO `compagnies` (`id_compagnie`, `nom_compagnie`, `image_compagnie`, `resume_compagnie`, `description_compagnie`, `id_clan`, `genie_civil`) VALUES
('1', 'Génie et Infrastructures Nordistes ', '', 'Compagnie GIN : : construction des infrastructures des nordistes', 'La compagnie du GIN a une double vocation.
Elle existe pour permettre la construction des nouveaux bâtiments nordistes qui assureront la conquête définitive des territoires gagnés ou nettoyés. Elle est aussi chargé de la surveillance des infrastructures existantes.
Ses membres sont formés à des techniques spécifiques de construction et de surveillance. ', '1', '1'),
('2', 'TIG-RES ', '', 'Compagnie TIG-RES : construction des infrastructures des confédérés', 'Réparation des anciens bâtiments et supervision de la construction des nouveaux.
Même et surtout en situation de crise quand les troupes du génie conventionnel seront dépassées et qu\'il faudra oeuvrer sous le feu de l\'ennemi. ', '2', '1');

-- --------------------------------------------------------

--
-- Contenu de la table `compagnie_as_contraintes`
--


INSERT INTO `compagnie_as_contraintes` (`id_compagnie`, `contrainte_type_perso`) VALUES
('1', '3'),
('2', '3');

-- --------------------------------------------------------

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
(62, 'Guerrier', 0, 1, 'Comp&eacute;tence permettant de d&eacute;bloquer les actions des comp&eacute;tences passives des guerriers', 50),
(63, 'Construire - Gare', 3, 1, 'Permet de construire des gares', 50),
(64, 'Construire - Rail', 1, 1, 'Permet de construire des rails', 50);

-- --------------------------------------------------------

--
-- Contenu de la table `competence_as_action`
--

INSERT INTO `competence_as_action` (`id_competence`, `id_action`) VALUES
(4, 1),
(6, 4),
(8, 10),
(12, 11),
(22, 33),
(23, 38),
(24, 43),
(27, 54),
(28, 59),
(29, 64),
(33, 76),
(38, 87),
(48, 104),
(50, 107),
(50, 108),
(50, 109),
(59, 140),
(63, 146),
(64, 147);

-- --------------------------------------------------------

--
-- Contenu de la table `config_jeu`
--

INSERT INTO `config_jeu` (`code_config`, `valeur_config`) VALUES
('disponible', '0');


INSERT INTO `decorations` (`id_decoration`, `description_decoration`, `camp_decoration`, `image_decoration`) VALUES
(1, 'Médaille de l\'honneur', 1, 'US-MOH-1862.png'),
(2, 'Croix de Guerre', 2, 'croix_guerre_sud.gif'),
(3, 'Croix de guerre', 1, 'croix_guerre_nord.png'),
(4, 'Croix de l\'honneur', 2, 'croix_honneur_sud.png'),
(5, 'Croix de l\'honneur exceptionnel', 2, 'croix_honneur_exceptionnelle_sud.png'),
(6, 'Étoile de la valeur argent', 2, 'etoile_valeur_argent_sud.png'),
(7, 'Étoile de la valeur Or', 2, 'etoile_valeur_or_sud.png'),
(8, 'Citation', 1, 'medaille_citation1_nord.png'),
(9, 'Médaille du dévouement', 1, 'medaille_devouement_nord.png'),
(10, 'médaille de la diplomatie', 1, 'medaille_diplomatie_nord.png'),
(11, 'Médaille de la magistrature', 1, 'medaille_magistrature_nord.png'),
(12, 'Médaille du mérite', 1, 'medaille_merite_nord.png'),
(13, 'Médaille du sauvetage', 1, 'medaille_sauvetage_nord.png'),
(14, 'Ordre du mérite', 2, 'ordre_merite_sud.png'),
(15, 'Purple Heart', 1, 'purple_heart_nord.png'),
(16, 'Médaille commémorative de Faifax Stone (Beta)', 1, 'beta.png'),
(17, 'Médaille commémorative de Faifax Stone (Beta)', 2, 'beta.png');

--
-- Contenu de la table `dossier`
--
INSERT INTO `dossier` ( `id_dossier` , `nom_dossier` )
VALUES (
'1', 'courant'
), (
'2', 'archive'
);

--
-- Contenu de la table `grades`
--

INSERT INTO `grades` (`id_grade`, `nom_grade`, `pc_grade`, `point_armee_grade`) VALUES
(2, 'Caporal', 10, 2),
(3, 'Sergent', 80, 4),
(4, '1er Sergent', 180, 6),
(5, 'Sergent d&apos;ordonnance', 300, 7),
(6, 'Quartier Maitre de Compagnie', 440, 8),
(7, 'Quartier Maitre de Régiment', 700, 9),
(8, 'Sergent Major', 950, 10),
(9, 'Sous-Lieutenant', 1350, 11),
(10, 'Lieutenant', 1800, 12),
(11, 'Capitaine', 2500, 13),
(12, 'Major', 3200, 14),
(13, 'Lieutenant-Colonel', 4000, 15),
(14, 'Colonel', 4800, 16),
(15, 'Général de Brigade', 6500, 17),
(16, 'Général de Division', 8200, 18),
(17, 'Général de Corps d&apos;armée', 11000, 19),
(18, 'Général des armées', 999999999, 100),
(1, 'Grouillot 2nd classe', 0, 0),
(101, 'Grouillot 1ere classe', 0, 0),
(102, 'Grouillot d\'élite', 0, 0);

-- --------------------------------------------------------
-- INSERTION DES 2 GENERAUX

--
-- Contenu de la table `joueur`
--
INSERT INTO `joueur` (`id_joueur`, `nom_joueur`, `email_joueur`, `mdp_joueur`, `age_joueur`, `pays_joueur`, `region_joueur`, `description_joueur`, `admin_perso`) VALUES
('1', NULL, 'admin1@example.com', '4fded1464736e77865df232cbcb4cd19', NULL, NULL, NULL, NULL, '1'),
('2', NULL, 'admin2@example.com', '4fded1464736e77865df232cbcb4cd19', NULL, NULL, NULL, NULL, '1');

--
-- Contenu de la table `news`
--
INSERT INTO `news` (`id_news`, `id_admin`, `date`, `contenu`) VALUES
(1, 1, '2022-02-06 00:00:00', 'Lancement de l\'Alpha communautaire !'),
(2, 1, '2020-01-06 00:00:00', 'Lancement de l\'Alpha !'),
(3, 1, '2020-02-01 00:00:00', 'Lancement de la Beta !');

--
-- Contenu de la table `objet`
--

INSERT INTO `objet` (`id_objet`, `nom_objet`, `portee_objet`, `bonusPerception_objet`, `bonusRecup_objet`, `bonusPv_objet`, `bonusPm_objet`, `bonusPrecisionCac_objet`, `bonusPrecisionDist_objet`, `bonusPA_objet`, `bonusDefense_objet`, `coutPa_objet`, `coutOr_objet`, `poids_objet`, `description_objet`, `contient_alcool`, `echangeable`, `deposable`, `type_objet`) VALUES
(1, 'Ticket de train', 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 3, '0.0', 'Un ticket de train permettant de monter dans un train pour aller vers une gare', 0, 0, 0, 'T'),
(2, 'Gourde d\'eau', 0, 0, 30, 0, 0, 0, 0, 0, 0, 1, 3, '0.5', 'Une gourde d\'eau bien fraiche permettant d\'augmenter sa récupération (Bonus Récup +30)', 0, 1, 1, 'N'),
(3, 'Whisky', 0, -3, 50, 0, 0, 0, 0, 0, 0, 1, 3, '0.5', 'Whisky, augmente la récupération mais attention aux effets secondaires ! (Bonus Récup +50, Bonus Perception -3)', 1, 1, 1, 'N'),
(4, 'Trousse de soin', 0, 0, 60, 0, 0, 0, 0, 0, 0, 1, 50, '2.0', 'Une trousse de soin, permet de récupérer plus rapidement de ses blessures (Bonus Récup +60)', 0, 1, 1, 'N'),
(5, 'Bottes légères', 0, 0, 0, 0, 1, 0, 0, 0, -5, 1, 50, '2.5', 'Des bottes légères pour monter plus rapidement au front mais dont la protection proposée est plus faible (Bonus PM +1, Bonus Defense -5)', 0, 1, 1, 'E'),
(6, 'Longue vue', 0, 2, 0, 0, 0, 0, 0, -1, 0, 1, 200, '0.2', 'Une longue vue permettant d\augmenter la perception de celui qui l\utilise (Bonus Perception +2, Bonus PA -1)', 0, 1, 1, 'E'),
(7, 'Lunette de visée', 0, 0, 0, 0, 0, 0, 15, 0, 0, 1, 200, '0.2', 'Une lunette de tir pour augmenter la précision des tirs à courte et longue distance (Coût attaque à distance +2, Bonus Précision à distance +15)', 0, 1, 1, 'E'),
(8, 'Etendard des armées du nord', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '9', "L'étendard de l'union", 0, 0, 0, 'E'),
(9, 'Etendard des armées du sud', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '9', "L'étendard des armées du sud", 0, 0, 0, 'E'),
(10, 'Orange', 0, 0, 60, 0, 0, 0, 0, 0, 0, 1, 9999, '0.0', 'Une orange fraîchement cueillie et pleine d\'énergie. Permet de récupérer plus rapidement de ses blessures (Bonus Récup +60)', 0, 0, 0, 'N');

INSERT INTO `objet` (`id_objet`, `nom_objet`, `portee_objet`, `bonusPerception_objet`, `bonusRecup_objet`, `bonusPv_objet`, `bonusPm_objet`, `bonusDefense_objet`, `bonusPrecisionCac_objet`, `bonusPrecisionDist_objet`, `bonusPA_objet`, `coutPa_objet`, `coutOr_objet`, `poids_objet`, `description_objet`, `type_objet`, `contient_alcool`, `echangeable`, `deposable`, `Perte_Proba`) VALUES (NULL, 'Pépite d\'or', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '3.5', 'Une pépite d\'or sacrément grosse!', 'RP', '0', '0', '1', '100') 

--
-- Contenu de la table `perso`
--
INSERT INTO `perso` (`id_perso`, `idJoueur_perso`, `nom_perso`, `type_perso`, `x_perso`, `y_perso`, `xp_perso`, `pi_perso`, `pc_perso`, `or_perso`, `pvMax_perso`, `pm_perso`, `pmMax_perso`, `pv_perso`, `perception_perso`, `recup_perso`, `pa_perso`, `paMax_perso`, `protec_perso`, `charge_perso`, `chargeMax_perso`, `bonusPerception_perso`, `bonusRecup_perso`, `bonusPM_perso`, `bonus_perso`, `image_perso`, `message_perso`, `bourre_perso`, `nb_kill`, `nb_mort`, `nb_pnj`, `dateCreation_perso`, `DLA_perso`, `description_perso`, `clan`, `a_gele`, `est_gele`, `date_gele`, `chef`, `bataillon`, `genie`) VALUES ('1', '1', 'Abraham Lincoln', '1', '165', '173', '0', '0', '999999999', '0', '7500', '100', '100', '7500', '10', '100', '100', '100', '20', '0', '50', '0', '0', '0', '0', 'cavalerie_nord.gif', 'Général des armées du Nord', '0', '0', '0', '0', NOW(), NOW(), 'Général des armées du Nord', '1', '0', '0', NULL, '1', 'Général de l\'Union', '1');
INSERT INTO `perso` (`id_perso`, `idJoueur_perso`, `nom_perso`, `type_perso`, `x_perso`, `y_perso`, `xp_perso`, `pi_perso`, `pc_perso`, `or_perso`, `pvMax_perso`, `pm_perso`, `pmMax_perso`, `pv_perso`, `perception_perso`, `recup_perso`, `pa_perso`, `paMax_perso`, `protec_perso`, `charge_perso`, `chargeMax_perso`, `bonusPerception_perso`, `bonusRecup_perso`, `bonusPM_perso`, `bonus_perso`, `image_perso`, `message_perso`, `bourre_perso`, `nb_kill`, `nb_mort`, `nb_pnj`, `dateCreation_perso`, `DLA_perso`, `description_perso`, `clan`, `a_gele`, `est_gele`, `date_gele`, `chef`, `bataillon`, `genie`) VALUES ('2', '2', 'Jefferson Davis', '1', '8', '7', '0', '0', '999999999', '0', '7500', '100', '100', '7500', '10', '100', '100', '100', '20', '0', '5', '0', '0', '0', '0', 'cavalerie_sud.gif', 'Général des armées du Sud', '0', '0', '0', '0', NOW(), NOW(), 'Général des armées du Sud', '2', '0', '0', NULL, '1', 'Général du Sud', '1');

--
-- Contenu de la table `perso_as_arme`
--
INSERT INTO `perso_as_arme` (`id_perso`, `id_arme`, `est_portee`) VALUES ('1', '1', '1');
INSERT INTO `perso_as_arme` (`id_perso`, `id_arme`, `est_portee`) VALUES ('1', '4', '1');
INSERT INTO `perso_as_arme` (`id_perso`, `id_arme`, `est_portee`) VALUES ('2', '1', '1');
INSERT INTO `perso_as_arme` (`id_perso`, `id_arme`, `est_portee`) VALUES ('2', '4', '1');

--
-- Contenu de la table `perso_as_grade`
--
INSERT INTO `perso_as_grade` (`id_perso`, `id_grade`) VALUES ('1', '18');
INSERT INTO `perso_as_grade` (`id_perso`, `id_grade`) VALUES ('2', '18');

--
-- Contenu de la table `perso_as_dossiers`
--
INSERT INTO `perso_as_dossiers` (`id_perso`, `id_dossier`) VALUES ('1', '1');
INSERT INTO `perso_as_dossiers` (`id_perso`, `id_dossier`) VALUES ('1', '2');
INSERT INTO `perso_as_dossiers` (`id_perso`, `id_dossier`) VALUES ('2', '1');
INSERT INTO `perso_as_dossiers` (`id_perso`, `id_dossier`) VALUES ('2', '2');

-- perso_as_competence
--

INSERT INTO `perso_as_competence` (`id_perso`, `id_competence`, `nb_points`) VALUES
('1', '4', '1'),
('1', '22', '1'),
('1', '23', '1'),
('1', '24', '1'),
('1', '27', '1'),
('1', '28', '1'),
('1', '29', '1'),
('1', '63', '1'),
('1', '64', '1'),
('2', '4', '1'),
('2', '22', '1'),
('2', '23', '1'),
('2', '24', '1'),
('2', '27', '1'),
('2', '28', '1'),
('2', '29', '1'),
('2', '63', '1'),
('2', '64', '1');

--
-- Contenu de la table `perso_in_compagnie`
--
INSERT INTO `perso_in_compagnie` (`id_perso`, `id_compagnie`, `poste_compagnie`, `attenteValidation_compagnie`) VALUES ('1', '1', '1', '0');
INSERT INTO `perso_in_compagnie` (`id_perso`, `id_compagnie`, `poste_compagnie`, `attenteValidation_compagnie`) VALUES ('2', '2', '1', '0');

--
-- Contenu de la table `perso_in_em`
--
INSERT INTO `perso_in_em` (`id_perso`, `camp_em`) VALUES
('1', '1'),
('2', '2');


-- --------------------------------------------------------
-- FIN INSERTION DES 2 GENERAUX

--
-- Contenu de la table `pnj`
--
INSERT INTO `pnj` (`id_pnj`, `nom_pnj`, `pvMax_pnj`, `degatMin_pnj`, `degatMax_pnj`, `pm_pnj`, `recup_pnj`, `protec_pnj`, `perception_pnj`, `precision_pnj`,`aggressivite_pnj`, `description_pnj`) VALUES ('1', 'Sangsue', '40', '8', '40', '4', '10', '0', '2', '80', '2', 'Petit animal suceur de sang, très gênant et particulièrement peu ragoutant, la sangsue se trouve dans le marais. Attention à leurs tares !');
INSERT INTO `pnj` (`id_pnj`, `nom_pnj`, `pvMax_pnj`, `degatMin_pnj`, `degatMax_pnj`, `pm_pnj`, `recup_pnj`, `protec_pnj`, `perception_pnj`, `precision_pnj`, `aggressivite_pnj`, `description_pnj`) VALUES ('2', 'Loup', '300', '20', '120', '8', '30', '0', '5', '70', '2', 'Chasseur des forêts, cousin éloigné du coyote, le loup n\'est plus à présenter.');
INSERT INTO `pnj` (`id_pnj`, `nom_pnj`, `pvMax_pnj`, `degatMin_pnj`, `degatMax_pnj`, `pm_pnj`, `recup_pnj`, `protec_pnj`, `perception_pnj`, `precision_pnj`, `aggressivite_pnj`, `description_pnj`) VALUES ('3', 'Crotale', '100', '20', '160', '5', '20', '0', '3', '70', '0', 'Les crotales sont des animaux qui peuvent s\'avérer très dangereux de par leurs terribles morsures infligeant des tares... Attention à na pas les sous estimer, tout comme les serpents !');
INSERT INTO `pnj` (`id_pnj`, `nom_pnj`, `pvMax_pnj`, `degatMin_pnj`, `degatMax_pnj`, `pm_pnj`, `recup_pnj`, `protec_pnj`, `perception_pnj`, `precision_pnj`, `aggressivite_pnj`, `description_pnj`) VALUES ('4', 'Caïman', '500', '30', '150', '5', '40', '20', '3', '70', '2', 'Redoutable prédateur des rivières, le caïman est synonyme d\'effroi pour beaucoup');
INSERT INTO `pnj` (`id_pnj`, `nom_pnj`, `pvMax_pnj`, `degatMin_pnj`, `degatMax_pnj`, `pm_pnj`, `recup_pnj`, `protec_pnj`, `perception_pnj`, `precision_pnj`, `aggressivite_pnj`, `description_pnj`) VALUES ('5', 'Bison', '1500', '48', '280', '5', '50', '20', '3', '70', '1', 'Noble herbivore des plaines, le bison n\'en demeure pas moins un animal extrêmement résistant. Attention à ne pas trop les énerver...');
INSERT INTO `pnj` (`id_pnj`, `nom_pnj`, `pvMax_pnj`, `degatMin_pnj`, `degatMax_pnj`, `pm_pnj`, `recup_pnj`, `protec_pnj`, `perception_pnj`, `precision_pnj`, `aggressivite_pnj`, `description_pnj`) VALUES ('6', 'Bison Blanc', '2000', '60', '300', '5', '60', '20', '3','80', '1', 'Bison très rare et encore plus résistant que le bison normal. Attention à ne pas trop les énerver...');
INSERT INTO `pnj` (`id_pnj`, `nom_pnj`, `pvMax_pnj`, `degatMin_pnj`, `degatMax_pnj`, `pm_pnj`, `recup_pnj`, `protec_pnj`, `perception_pnj`, `precision_pnj`, `aggressivite_pnj`, `description_pnj`) VALUES ('7', 'Scorpion', '40', '15', '120', '4', '10', '0', '2', '80', '0', 'Le scorpion se trouve essentiellement dans les régions désolées. Sa faiblesse n\'est que relative lorsque l\'on voit les effets de son venin... là encore, attention aux tares !');
INSERT INTO `pnj` (`id_pnj`, `nom_pnj`, `pvMax_pnj`, `degatMin_pnj`, `degatMax_pnj`, `pm_pnj`, `recup_pnj`, `protec_pnj`, `perception_pnj`, `precision_pnj`, `aggressivite_pnj`, `description_pnj`) VALUES ('8', 'Aigle', '100', '10', '40', '20', '2', '0', '6', '75', '0', 'Animal majestueux mais fragile, l\'aigle sera rencontré essentiellement dans les montagnes.');
INSERT INTO `pnj` (`id_pnj`, `nom_pnj`, `pvMax_pnj`, `degatMin_pnj`, `degatMax_pnj`, `pm_pnj`, `recup_pnj`, `protec_pnj`, `perception_pnj`, `precision_pnj`, `aggressivite_pnj`, `description_pnj`) VALUES ('9', 'Ours', '1000', '50', '240', '8', '50', '20', '3', '70', '1', 'Animal très robuste et que l\'on rencontre en forêt, l\'ours est capable de donner des coups de griffes meurtriers.');

-- --------------------------------------------------------

--
-- Contenu de la table `poste`
--

INSERT INTO `poste` VALUES (1, 'chef');
INSERT INTO `poste` VALUES (2, 'sous-chef');
INSERT INTO `poste` VALUES (3, 'tresorier');
INSERT INTO `poste` VALUES (4, 'recruteur');
INSERT INTO `poste` VALUES (5, 'diplomate');
INSERT INTO `poste` VALUES (10, 'membre');

-- --------------------------------------------------------

--
-- Contenu de la table `stats_camp_kill`
--

INSERT INTO `stats_camp_kill` (`id_camp`, `nb_kill`) VALUES ('1', '0'), ('2', '0');

--
-- Contenu de la table `stats_camp_pv`
--

INSERT INTO `stats_camp_pv` (`id_camp`, `points_victoire`) VALUES ('1', '0'), ('2', '0');

-- --------------------------------------------------------

--
-- Contenu de la table `type_unite`
--

INSERT INTO `type_unite` (`id_unite`, `nom_unite`, `description_unite`, `perception_unite`, `protection_unite`, `recup_unite`, `pv_unite`, `pa_unite`, `pm_unite`, `image_unite`, `cout_pg`) VALUES
(1, 'Chef', 'Chef d\'unité, cavalier', 5, 20, 40, 950, 10, 10, 'cavalerie', 0),
(2, 'Cavalerie lourde', 'La cavalerie lourde est sans doute l\'unité nordiste/sudiste courante la plus redoutée sur les champs de bataille. Comparables aux chef en tout point excpeté sa résistance, la cavalerie lourde peut faire de véritables saignées dans les rangs adverses notament grâce à leurs charges.', 5, 15, 30, 800, 10, 10, 'cavalerie', 4),
(3, 'Infanterie', 'Il s\'agit du plus courant des grouillots présents dans les armées nordistes et sudistes. Les infanteries représentent la masse de ces armées. Leur réputation n\'est pas toujours la meilleure et leur faible mobilité amène bien souvent des à priori négatifs à leur encontre. Pourtant les infanteries, lorsqu\'elles sont coordonnées et regroupées sont extrèmement redoutables et peuvent faire des ravages dans les rangs énnemis. Ce sont avant tout des unités de tir.', 4, 10, 30, 600, 10, 5, 'infanterie', 2),
(4, 'Soigneur', 'Les soigneurs sont des unités nordistes/sudistes non combattantes dont le rôle est uniquement de soigner les troupes parties sur le front. Plus rapides que des infanteries classiques, elles doivent malgré tout rester prudentes sur le front.', 4, 0, 30, 500, 10, 6, 'soigneur', 3),
(5, 'Artillerie', 'La plus puissante de toutes les unités combattantes. L\'artillerie est tout simplement extrèmement puissante, pouvant réduire en miette tout un bataillon en très peu de temps. Mais c\'est une unité extrèmement peu mobile et qui ne peut se battre au corps à corps et donc qui nécessite beaucoup d\'attention et de protection.', 6, 10, 30, 600, 10, 3, 'artillerie', 5),
(6, 'Toutou', 'Unité extrémement mobile, et bien que très fragile, le toutou sera le meilleur ami de votre bataillon afin d\étudier les positions ennemis. Ne peut effectuer que des attaques au Corps à corps', 5, 0, 20, 225, 10, 14, 'toutou', 1),
(7, 'Cavalerie légère', 'La cavalerie légère est typiquement utilisée pour la reconnaissance, le fourrageage et la poursuite d\'ennemis.', 5, 5, 60, 400, 10, 12, 'cavalerie_legere', 3),
(8, 'Gatling', 'Une gatling faucheuse d\'ames..', 5, 10, 30, 600, 10, 5, 'gatling', 4);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
