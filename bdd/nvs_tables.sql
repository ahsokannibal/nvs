-- phpMyAdmin SQL Dump
-- version 4.6.4
-- https://www.phpmyadmin.net/
--
-- Client :  127.0.0.1
-- Généré le :  Sam 16 Novembre 2019 à 19:00
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


CREATE TABLE IF NOT EXISTS `acces_log` (
  `id_acces` int(11) NOT NULL AUTO_INCREMENT,
  `date_acces` datetime NOT NULL,
  `id_perso` int(11) NOT NULL,
  `page` varchar(255) NOT NULL,
  PRIMARY KEY (`id_acces`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Table `Camp`
--

CREATE TABLE `camp` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `desc` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `color` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Structure de la table `action`
--

CREATE TABLE `action` (
  `id_action` int(11) NOT NULL,
  `nom_action` varchar(60) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `nb_points` int(11) NOT NULL DEFAULT '1',
  `description_action` text COLLATE utf8_unicode_ci,
  `portee_action` int(11) NOT NULL DEFAULT '0',
  `perceptionMin_action` int(11) NOT NULL DEFAULT '0',
  `perceptionMax_action` int(11) NOT NULL DEFAULT '0',
  `pvMin_action` int(11) NOT NULL DEFAULT '0',
  `pvMax_action` int(11) NOT NULL DEFAULT '0',
  `nb_des_action` int(11) NOT NULL DEFAULT '0',
  `valeur_des_action` int(11) NOT NULL DEFAULT '0',
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `action_as_batiment`
--

CREATE TABLE `action_as_batiment` (
  `id_action` int(11) NOT NULL,
  `id_batiment` int(11) NOT NULL,
  `contenance` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `alerte_anim`
--

CREATE TABLE `alerte_anim` ( 
	`id_alerte` INT NOT NULL AUTO_INCREMENT , 
	`type_alerte` INT NOT NULL , 
	`id_perso` INT NOT NULL , 
	`raison_alerte` TEXT NOT NULL ,
	`date_alerte` DATETIME NOT NULL , 
	PRIMARY KEY (`id_alerte`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; 

-- --------------------------------------------------------

--
-- Structure de la table `anim_capture`
--

CREATE TABLE `anim_capture` ( 
	`id` INT NOT NULL AUTO_INCREMENT , 
	`id_perso` INT NOT NULL , 
	`id_perso_capture` INT NOT NULL , 
	`titre` VARCHAR(255) NOT NULL ,
	`message` TEXT NOT NULL , 
	`date_capture` DATETIME NOT NULL , 
	`statut` INT NOT NULL DEFAULT '0' ,
	PRIMARY KEY (`id`)
) ENGINE = MyISAM; 

-- --------------------------------------------------------

--
-- Structure de la table `anim_question`
--

CREATE TABLE `anim_question` ( 
	`id` INT NOT NULL AUTO_INCREMENT ,
	`date_question` DATETIME NOT NULL ,
	`id_perso` INT NOT NULL , 
	`titre` TEXT NOT NULL , 
	`question` TEXT NOT NULL ,
	`id_camp` INT NOT NULL , 
	`status` INT NOT NULL DEFAULT '0' ,
	`id_parent` INT DEFAULT NULL ,
	PRIMARY KEY (`id`)
) ENGINE = MyISAM; 

-- --------------------------------------------------------

--
-- Structure de la tables `anti_zerk`
--

CREATE TABLE `anti_zerk` ( 
	`id_perso` INT NOT NULL, 
	`date_derniere_attaque` DATETIME NOT NULL,
	`date_nouveau_tour` DATETIME NOT NULL
) ENGINE = MyISAM;

CREATE TABLE IF NOT EXISTS `anti_zerk_banque_compagnie` (
  `id_anti_zerk` int(11) NOT NULL AUTO_INCREMENT,
  `id_perso` int(11) NOT NULL,
  `date_dernier_retrait` datetime NOT NULL,
  PRIMARY KEY (`id_anti_zerk`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

--
-- Structure de la table `arme`
--

CREATE TABLE `arme` (
  `id_arme` int(11) NOT NULL,
  `nom_arme` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `porteeMin_arme` int(11) NOT NULL DEFAULT '0',
  `porteeMax_arme` tinyint(4) NOT NULL DEFAULT '0',
  `coutPa_arme` int(11) NOT NULL DEFAULT '0',
  `coutOr_arme` int(11) NOT NULL DEFAULT '0',
  `additionMin_degats` tinyint(4) NOT NULL DEFAULT '0',
  `additionMax_degats` tinyint(4) NOT NULL DEFAULT '0',
  `multiplicateurMin_degats` double NOT NULL DEFAULT '0',
  `multiplicateurMax_degats` double NOT NULL DEFAULT '0',
  `degatMin_arme` int(11) NOT NULL DEFAULT '0',
  `degatMax_arme` int(11) NOT NULL DEFAULT '0',
  `valeur_des_arme` int(11) NOT NULL DEFAULT '0',
  `precision_arme` int(11) NOT NULL DEFAULT '0',
  `degatZone_arme` enum('0','1') NOT NULL DEFAULT '0',
  `bonusPM_arme` int(11) NOT NULL DEFAULT '0',
  `poids_arme` decimal(10,1) NOT NULL DEFAULT '0.0',
  `pvMax_arme` int(11) NOT NULL DEFAULT '0',
  `description_arme` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `qualite_arme` tinyint(4) NOT NULL DEFAULT '6',
  `main` tinyint(4) NOT NULL DEFAULT '1',
  `image_arme` varchar(100) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `arme_as_type_unite`
--

CREATE TABLE `arme_as_type_unite` (
  `id_arme` int(11) NOT NULL,
  `id_type_unite` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `objet_as_type_unite`
--

CREATE TABLE `objet_as_type_unite` (
  `id_objet` int(11) NOT NULL,
  `id_type_unite` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `armure`
--

CREATE TABLE `armure` (
  `id_armure` int(11) NOT NULL,
  `nom_armure` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `coutOr_armure` int(11) NOT NULL DEFAULT '0',
  `corps_armure` int(11) NOT NULL DEFAULT '0',
  `bonusDefense_armure` int(11) NOT NULL DEFAULT '0',
  `bonusDesDefense_armure` int(11) NOT NULL DEFAULT '0',
  `bonusRecup_armure` int(11) NOT NULL DEFAULT '0',
  `bonusAttaque_armure` int(11) NOT NULL DEFAULT '0',
  `bonusPm_armure` int(11) NOT NULL DEFAULT '0',
  `bonusPv_armure` int(11) NOT NULL DEFAULT '0',
  `BonusCharge_armure` int(11) NOT NULL DEFAULT '0',
  `BonusPerception_armure` int(11) NOT NULL DEFAULT '0',
  `kit` enum('0','1') NOT NULL DEFAULT '0',
  `id_kit` int(11) DEFAULT NULL,
  `poids_armure` decimal(10,1) NOT NULL DEFAULT '0.0',
  `pvMax_armure` int(11) NOT NULL DEFAULT '0',
  `description_armure` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `qualite_armure` tinyint(4) NOT NULL DEFAULT '6',
  `image_armure` varchar(200) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `batiment`
--

CREATE TABLE `batiment` (
  `id_batiment` int(11) NOT NULL,
  `nom_batiment` varchar(125) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `pvMax_batiment` int(11) NOT NULL DEFAULT '20',
  `description` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `taille_batiment` int(11) NOT NULL DEFAULT '1',
  `capturable` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `banque_as_compagnie`
-- 

CREATE TABLE `banque_as_compagnie` (
  `id_compagnie` int(11) NOT NULL default '0',
  `montant` int(11) NOT NULL default '0'
);

-- --------------------------------------------------------

-- 
-- Structure de la table `banque_compagnie`
-- 

CREATE TABLE `banque_compagnie` (
  `id_perso` int(11) NOT NULL default '0',
  `montant` int(11) NOT NULL default '0',
  `demande_emprunt` int(11) NOT NULL default '0',
  `montant_emprunt` int(11) NOT NULL default '0'
);

-- --------------------------------------------------------


CREATE TABLE `banque_log` ( 
	`id_log` INT NOT NULL AUTO_INCREMENT, 
	`date_log` DATETIME NOT NULL , 
	`id_compagnie` INT NOT NULL , 
	`id_perso` INT NOT NULL , 
	`montant_transfert` INT NOT NULL , 
	`montant_final` INT NOT NULL,
	PRIMARY KEY (`id_log`)
) ENGINE = MyISAM;


--
-- Structure de la table `carte`
--

CREATE TABLE `carte` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_carte` int(10) unsigned NOT NULL,
  `x_carte` int(11) NOT NULL DEFAULT '0',
  `y_carte` int(11) NOT NULL DEFAULT '0',
  `occupee_carte` enum('0','1') NOT NULL DEFAULT '0',
  `fond_carte` varchar(20) NOT NULL DEFAULT '',
  `idPerso_carte` int(11) DEFAULT '0',
  `type_perso` varchar(255) NOT NULL DEFAULT 'undefined',
  `image_carte` varchar(100) DEFAULT NULL,
  `save_info_carte` varchar(255) DEFAULT NULL,
  `vue_nord` tinyint(1) DEFAULT '0',
  `vue_sud` tinyint(1) DEFAULT '0',
  `vue_nord_date` datetime DEFAULT NULL,
  `vue_sud_date` datetime DEFAULT NULL,
  `coordonnees` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Structure de la table `carte2`
--

CREATE TABLE `carte2` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_carte` int(10) unsigned NOT NULL,
  `x_carte` int(11) NOT NULL DEFAULT '0',
  `y_carte` int(11) NOT NULL DEFAULT '0',
  `occupee_carte` enum('0','1') NOT NULL DEFAULT '0',
  `fond_carte` varchar(20) NOT NULL DEFAULT '',
  `idPerso_carte` int(11) DEFAULT '0',
  `type_perso` varchar(255) NOT NULL DEFAULT 'undefined',
  `image_carte` varchar(100) DEFAULT NULL,
  `save_info_carte` varchar(255) DEFAULT NULL,
  `vue_nord` tinyint(1) DEFAULT '0',
  `vue_sud` tinyint(1) DEFAULT '0',
  `vue_nord_date` datetime DEFAULT NULL,
  `vue_sud_date` datetime DEFAULT NULL,
  `coordonnees` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Structure de la table `carte3`
--

CREATE TABLE `carte3` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_carte` int(10) unsigned NOT NULL,
  `x_carte` int(11) NOT NULL DEFAULT '0',
  `y_carte` int(11) NOT NULL DEFAULT '0',
  `occupee_carte` enum('0','1') NOT NULL DEFAULT '0',
  `fond_carte` varchar(20) NOT NULL DEFAULT '',
  `idPerso_carte` int(11) DEFAULT '0',
  `type_perso` varchar(255) NOT NULL DEFAULT 'undefined',
  `image_carte` varchar(100) DEFAULT NULL,
  `save_info_carte` varchar(255) DEFAULT NULL,
  `vue_nord` tinyint(1) DEFAULT '0',
  `vue_sud` tinyint(1) DEFAULT '0',
  `vue_nord_date` datetime DEFAULT NULL,
  `vue_sud_date` datetime DEFAULT NULL,
  `coordonnees` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`id`)
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
-- Structure de la table `choix_carte_suivante`
--

CREATE TABLE `choix_carte_suivante` ( 
	`id_choix` INT NOT NULL AUTO_INCREMENT, 
	`id_camp` INT NOT NULL , 
	`carte` VARCHAR(255) NOT NULL , 
	`date_choix` DATETIME NOT NULL ,
	PRIMARY KEY (`id_choix`)
) ENGINE = MyISAM;

-- --------------------------------------------------------

--
-- Structure de la table `compagnies`
--

CREATE TABLE `compagnies` (
  `id_compagnie` int(11) NOT NULL,
  `nom_compagnie` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `image_compagnie` varchar(255) NOT NULL DEFAULT '0',
  `resume_compagnie` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `description_compagnie` longtext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `id_clan` tinyint(4) NOT NULL,
  `genie_civil` tinyint(1) NOT NULL DEFAULT '0',
  `id_parent` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `compagnie_as_contraintes`
--

CREATE TABLE `compagnie_as_contraintes` (
  `id_compagnie` int(11) NOT NULL,
  `contrainte_type_perso` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `compagnie_demande_anim`
--

CREATE TABLE `compagnie_demande_anim` ( 
	`id_compagnie` INT NOT NULL , 
	`type_demande` INT NOT NULL , 
	`info_demande` TEXT NOT NULL 
) ENGINE = MyISAM; 

-- --------------------------------------------------------

--
-- Structure de la table `competence`
--

CREATE TABLE `competence` (
  `id_competence` int(11) NOT NULL,
  `nom_competence` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `niveau_competence` int(11) NOT NULL DEFAULT '0',
  `nbPoints_competence` int(11) NOT NULL DEFAULT '0',
  `description_competence` text CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `cout_competence` int(11) NOT NULL DEFAULT '50'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `competence_as_action`
--

CREATE TABLE `competence_as_action` (
  `id_competence` int(11) NOT NULL,
  `id_action` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

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

-- --------------------------------------------------------

--
-- Structure de la table `config_jeu`
--

CREATE TABLE IF NOT EXISTS `config_jeu` (
  `code_config` varchar(255) NOT NULL,
  `valeur_config` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `contact`
--

CREATE TABLE `contact` (
  `id_contact` int(11) NOT NULL,
  `nom_contact` varchar(50) NOT NULL DEFAULT 'amis',
  `contacts` text DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `cv`
--

CREATE TABLE `cv` (
  `ID_cv` int(11) NOT NULL,
  `IDActeur_cv` int(11) NOT NULL DEFAULT '0',
  `nomActeur_cv` varchar(100) NOT NULL DEFAULT '',
  `gradeActeur_cv` varchar(255) DEFAULT NULL,
  `IDCible_cv` int(11) DEFAULT NULL,
  `nomCible_cv` varchar(100) DEFAULT NULL,
  `gradeCible_cv` varchar(255) DEFAULT NULL,
  `date_cv` datetime NOT NULL,
  `special` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `declaration_babysitte`
--

CREATE TABLE `declaration_babysitte` (
	`id_declaration` int(11) NOT NULL,
	`id_perso` INT NOT NULL , 
	`id_baby` INT NOT NULL , 
	`date_debut` DATETIME NOT NULL , 
	`date_fin` DATETIME NOT NULL 
) ENGINE = MyISAM;

-- --------------------------------------------------------

--
-- Structure de la table `declaration_multi`
--

CREATE TABLE `declaration_multi` ( 
	`id_declaration` INT NOT NULL AUTO_INCREMENT , 
	`id_perso` INT NOT NULL , 
	`id_multi` INT NOT NULL , 
	`situation` TEXT NOT NULL , 
	`date_declaration` DATETIME DEFAULT NULL ,
	PRIMARY KEY (`id_declaration`)
) ENGINE = MyISAM; 

-- --------------------------------------------------------

--
-- Structure de la table `decorations`
--

CREATE TABLE `decorations` ( 
	`id_decoration` INT NOT NULL AUTO_INCREMENT , 
	`description_decoration` TEXT NULL , 
	`camp_decoration` INT NOT NULL ,
	`image_decoration` TEXT NOT NULL ,
	PRIMARY KEY (`id_decoration`)
) ENGINE = MyISAM; 

-- --------------------------------------------------------

--
-- Structure de la table `dernier_tombe`
--

CREATE TABLE `dernier_tombe` ( 
	`date_capture` DATETIME NOT NULL , 
	`id_perso_capture` INT NOT NULL ,
	`camp_perso_capture` tinyint(4) NOT NULL,
	`id_perso_captureur` INT NOT NULL,
	`camp_perso_captureur` tinyint(4) NOT NULL
) ENGINE = MyISAM;

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
-- Structure de la table `em_creer_compagnie`
--

CREATE TABLE `em_creer_compagnie` (
  `id_em_creer_compagnie` int(11) NOT NULL,
  `id_perso` int(11) NOT NULL,
  `nom_compagnie` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `description_compagnie` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `camp` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `em_position_infra_carte_suivante`
--

CREATE TABLE IF NOT EXISTS `em_position_infra_carte_suivante` (
  `id_infra_carte` int(11) NOT NULL AUTO_INCREMENT,
  `id_camp` int(11) NOT NULL,
  `carte` varchar(255) NOT NULL,
  `id_batiment` int(11) NOT NULL,
  `position_x` int(11) NOT NULL,
  `position_y` int(11) NOT NULL,
  PRIMARY KEY (`id_infra_carte`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `em_vote_choix_carte`
--

CREATE TABLE `em_vote_choix_carte` ( 
	`id_vote` INT NOT NULL AUTO_INCREMENT, 
	`id_em_perso` INT NOT NULL , 
	`vote` VARCHAR(255) NOT NULL , 
	`date_vote` DATETIME NOT NULL , 
	PRIMARY KEY (`id_vote`)
) ENGINE = MyISAM; 

-- --------------------------------------------------------

--
-- Structure de la table `em_vote_creer_compagnie`
--

CREATE TABLE `em_vote_creer_compagnie` (
  `id_em_creer_compagnie` int(11) NOT NULL,
  `id_em_perso` int(11) NOT NULL,
  `vote` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `evenement`
--

CREATE TABLE `evenement` (
  `ID_evenement` int(11) NOT NULL,
  `IDActeur_evenement` int(11) NOT NULL DEFAULT '0',
  `nomActeur_evenement` varchar(100) NOT NULL DEFAULT '',
  `phrase_evenement` varchar(250) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `IDCible_evenement` int(11) DEFAULT NULL,
  `nomCible_evenement` varchar(100) DEFAULT NULL,
  `effet_evenement` varchar(250) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `date_evenement` datetime NOT NULL,
  `special` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `grades`
--

CREATE TABLE `grades` (
  `id_grade` int(11) NOT NULL,
  `nom_grade` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `pc_grade` int(11) NOT NULL,
  `point_armee_grade` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Structure de la table `histobanque_compagnie`
-- 

CREATE TABLE `histobanque_compagnie` (
  `id_histo` int(11) NOT NULL default '0',
  `id_compagnie` int(11) NOT NULL default '0',
  `id_perso` int(11) NOT NULL default '0',
  `operation` int(11) NOT NULL default '0',
  `montant` int(11) NOT NULL default '0',
  `date_operation` datetime DEFAULT NULL,
  `is_auteur` TINYINT(1) NOT NULL DEFAULT '1',
  `id_dest` INT DEFAULT NULL
);

-- --------------------------------------------------------

-- 
-- Structure de la table `histobanque_compagnie`
-- 

CREATE TABLE `histo_stats_camp_pv` ( 
	`date_pvict` DATETIME NOT NULL , 
	`id_camp` INT NOT NULL , 
	`gain_pvict` INT NOT NULL , 
	`texte` TEXT NOT NULL
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- 
-- Structure de la table `historique_punitions`
-- 

CREATE TABLE `historique_punitions` ( 
	`id` INT NOT NULL AUTO_INCREMENT , 
	`date_punition` DATETIME NOT NULL , 
	`id_perso_puni` INT NOT NULL , 
	`id_perso_anim` INT NOT NULL , 
	`description_punition` TEXT NOT NULL , 
	PRIMARY KEY (`id`)
) ENGINE = MyISAM; 

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
  `camp_origine_instance` int(11) DEFAULT NULL,
  `contenance_instance` int(11) DEFAULT '0',
  `terrain_instance` varchar(50) DEFAULT '1.gif'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `instance_batiment_canon`
--

CREATE TABLE `instance_batiment_canon` (
  `id_instance_canon` int(11) NOT NULL,
  `id_instance_bat` int(11) NOT NULL,
  `x_canon` int(11) NOT NULL,
  `y_canon` int(11) NOT NULL,
  `camp_canon` int(11) NOT NULL,
  `date_activation` datetime DEFAULT NULL
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
  `y_i` int(11) DEFAULT '0',
  `bonus_i` int(11) DEFAULT '0',
  `cycle_mvt` int(11) DEFAULT '0'
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
  `dossier_img` varchar(10) DEFAULT 'v1',
  `admin_perso` enum('0','1') NOT NULL DEFAULT '0',
  `animateur` enum('0','1') NOT NULL DEFAULT '0',
  `redacteur` INT NOT NULL DEFAULT '0',
  `mail_mp` INT NOT NULL DEFAULT '0',
  `valid_case` INT NOT NULL DEFAULT '0',
  `afficher_rosace` INT NOT NULL DEFAULT '1',
  `bousculade_deplacement` INT NOT NULL DEFAULT '1',
  `pendu` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `joueur_as_ip`
--

CREATE TABLE `joueur_as_ip` (
  `id_joueur` int(11) NOT NULL DEFAULT '0',
  `ip_joueur` varchar(100) NOT NULL DEFAULT '000.000.000.000',
  `date_premier_releve` datetime NOT NULL,
  `date_dernier_releve` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `user_ok_logins`
--

CREATE TABLE `user_ok_logins` (
  `id_joueur` int(11) NOT NULL DEFAULT '0',
  `ip_joueur` varchar(100) NOT NULL DEFAULT '000.000.000.000',
  `time` datetime NOT NULL,
  `user_agent` varchar(100) NOT NULL DEFAULT '',
  `cookie_val` varchar(300) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Structure de la table `whitelist_triche`
--

CREATE TABLE `whitelist_triche` (
  `id` INT NOT NULL AUTO_INCREMENT , 
  `id_joueur` int(11) NOT NULL DEFAULT '0',
   PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `liaisons_gare`
--

CREATE TABLE `liaisons_gare` (
  `id_gare1` int(11) NOT NULL,
  `id_gare2` int(11) NOT NULL,
  `id_train` int(11) NULL,
  `direction` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `liens_activation`
--

CREATE TABLE `liens_activation` (
  `id_lien` VARCHAR(250) NOT NULL,
  `date_fin` datetime NOT NULL,
  `mail` VARCHAR(250) NOT NULL,
  `data` TEXT NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `log`
--

CREATE TABLE `log` ( 
	`id_log` INT NOT NULL AUTO_INCREMENT , 
	`date_log` DATETIME NOT NULL , 
	`id_perso` INT NOT NULL , 
	`type_action` VARCHAR(255) NOT NULL,
	`id_arme` INT NULL,
	`degats` INT NULL,
	`pourcentage` INT NULL,
	`message_log` TEXT NOT NULL , 
	PRIMARY KEY (`id_log`)
) ENGINE = MyISAM; 

CREATE TABLE `log_respawn` ( 
	`id_log_respawn` INT NOT NULL AUTO_INCREMENT , 
	`id_perso` INT NOT NULL , 
	`date_respawn` DATETIME NOT NULL , 
	`texte_respawn` TEXT NOT NULL , 
	PRIMARY KEY (`id_log_respawn`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `log_pendaison` (
  `id_pendaison` int(11) NOT NULL AUTO_INCREMENT,
  `date_pendaison` datetime NOT NULL,
  `nom_perso` varchar(255) NOT NULL,
  `id_perso` int(11) NOT NULL,
  `raison_pendaison` text NOT NULL,
  PRIMARY KEY (`id_pendaison`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Structure de la table `message`
--

CREATE TABLE `message` (
  `id_message` int(11) NOT NULL,
  `id_expediteur` int(11) NULL,
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
  `annonce` int(11) NOT NULL DEFAULT '0',
  `supprime_message` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `missions`
--
CREATE TABLE IF NOT EXISTS `missions` (
  `id_mission` int(11) NOT NULL AUTO_INCREMENT,
  `nom_mission` varchar(255) NOT NULL,
  `texte_mission` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `nombre_participant` INT(11) NULL DEFAULT NULL,
  `recompense_thune` int(11) NOT NULL DEFAULT '0',
  `recompense_xp` int(11) NOT NULL DEFAULT '0',
  `recompense_pc` int(11) NOT NULL DEFAULT '0',
  `recompense_pvict` int(11) NOT NULL DEFAULT '0',
  `date_debut_mission` datetime DEFAULT NULL,
  `date_fin_mission` datetime DEFAULT NULL,
  `camp_mission` INT NOT NULL,
  `objectif_atteint` INT NULL,
  PRIMARY KEY (`id_mission`)
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

--
-- Structure de la table `objet`
--

CREATE TABLE `objet` (
  `id_objet` int(11) NOT NULL,
  `nom_objet` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `portee_objet` int(11) NOT NULL DEFAULT '0',
  `bonusPerception_objet` int(11) NOT NULL DEFAULT '0',
  `bonusRecup_objet` int(11) NOT NULL DEFAULT '0',
  `bonusPv_objet` int(11) NOT NULL DEFAULT '0',
  `bonusPm_objet` int(11) NOT NULL DEFAULT '0',
  `bonusDefense_objet` int(11) NOT NULL DEFAULT '0',
  `bonusPrecisionCac_objet` int(11) NOT NULL DEFAULT '0',
  `bonusPrecisionDist_objet` int(11) NOT NULL DEFAULT '0',
  `bonusPA_objet` int(11) NOT NULL DEFAULT '0',
  `coutPa_objet` int(11) NOT NULL DEFAULT '0',
  `coutOr_objet` int(11) NOT NULL DEFAULT '0',
  `poids_objet` decimal(10,1) NOT NULL DEFAULT '0.0',
  `description_objet` text NOT NULL,
  `type_objet` varchar(3) NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `objet_in_carte`
--

CREATE TABLE `objet_in_carte` (
  `type_objet` tinyint(4) NOT NULL DEFAULT '1',
  `id_objet` int(11) NOT NULL DEFAULT '0',
  `nb_objet` int(11) NOT NULL DEFAULT '0',
  `x_carte` int(11) NOT NULL DEFAULT '-1',
  `y_carte` int(11) NOT NULL DEFAULT '-1'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `perso`
--

CREATE TABLE `perso` (
  `id_perso` int(11) NOT NULL,
  `idJoueur_perso` int(11) NOT NULL,
  `nom_perso` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `type_perso` int(11) NOT NULL DEFAULT '1',
  `x_perso` int(11) NOT NULL DEFAULT '0',
  `y_perso` int(11) NOT NULL DEFAULT '0',
  `xp_perso` int(11) NOT NULL DEFAULT '0',
  `pi_perso` int(11) NOT NULL DEFAULT '0',
  `pc_perso` int(11) NOT NULL DEFAULT '0',
  `or_perso` int(11) NOT NULL DEFAULT '0',
  `pvMax_perso` int(11) NOT NULL DEFAULT '0',
  `pm_perso` int(11) NOT NULL DEFAULT '5',
  `pmMax_perso` int(11) NOT NULL DEFAULT '5',
  `pv_perso` int(11) NOT NULL DEFAULT '0',
  `perception_perso` int(11) NOT NULL DEFAULT '0',
  `recup_perso` int(11) NOT NULL DEFAULT '0',
  `pa_perso` int(11) NOT NULL DEFAULT '0',
  `paMax_perso` int(11) NOT NULL DEFAULT '10',
  `protec_perso` int(11) NOT NULL DEFAULT '0',
  `charge_perso` DECIMAL(10,3) NOT NULL DEFAULT '0.000',
  `chargeMax_perso` int(11) NOT NULL DEFAULT '10',
  `bonusPerception_perso` int(11) NOT NULL DEFAULT '0',
  `bonusRecup_perso` int(11) NOT NULL DEFAULT '0',
  `bonusPM_perso` int(11) NOT NULL DEFAULT '0',
  `bonusPA_perso` int(11) NOT NULL DEFAULT '0',
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
  `a_gele` tinyint(1) NOT NULL DEFAULT '0',
  `est_gele` tinyint(1) NOT NULL DEFAULT '0',
  `date_gele` datetime DEFAULT NULL,
  `chef` tinyint(1) NOT NULL DEFAULT '0',
  `bataillon` varchar(250) NOT NULL DEFAULT '',
  `convalescence` INT NOT NULL DEFAULT '0',
  `genie` INT NOT NULL DEFAULT '0',
  `gain_xp_tour` INT NOT NULL DEFAULT '0',
  `est_renvoye` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `perso_as_arme`
--

CREATE TABLE `perso_as_arme` (
  `id_perso` int(11) NOT NULL DEFAULT '0',
  `id_arme` int(11) NOT NULL DEFAULT '0',
  `est_portee` enum('0','1') NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `perso_as_armure`
--

CREATE TABLE `perso_as_armure` (
  `id_perso` int(11) NOT NULL DEFAULT '0',
  `id_armure` int(11) NOT NULL DEFAULT '0',
  `est_portee` enum('0','1') NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

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
-- Structure de la table `perso_as_decoration`
--

CREATE TABLE `perso_as_decoration` ( 
	`id` INT NOT NULL AUTO_INCREMENT , 
	`id_perso` INT NOT NULL , 
	`id_decoration` INT NOT NULL , 
	`date_decoration` DATETIME NOT NULL ,
	`raison_decoration` TEXT NULL , 
	PRIMARY KEY (`id`)
) ENGINE = MyISAM; 

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
-- Structure de la table `perso_as_grade`
--

CREATE TABLE `perso_as_grade` (
  `id_perso` int(11) NOT NULL,
  `id_grade` int(11) NOT NULL
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
  `id_perso` int(11) NOT NULL DEFAULT '0',
  `id_objet` int(11) NOT NULL DEFAULT '0',
  `capacite_objet` varchar(250) DEFAULT NULL,
  `equip_objet` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `perso_as_respawn`
--

CREATE TABLE `perso_as_respawn` (
  `id_perso` int(11) NOT NULL,
  `id_bat` int(11) NOT NULL,
  `id_instance_bat` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `perso_bagne`
--

CREATE TABLE `perso_bagne` ( 
	`id` INT NOT NULL AUTO_INCREMENT , 
	`id_perso` INT NOT NULL , 
	`date_debut` DATETIME NOT NULL , 
	`duree` INT NULL , PRIMARY KEY (`id`)
) ENGINE = MyISAM; 

-- --------------------------------------------------------

--
-- Structure de la table `perso_demande_anim`
--

CREATE TABLE `perso_demande_anim` ( 
	`id_perso` INT NOT NULL , 
	`type_demande` INT NOT NULL , 
	`info_demande` TEXT NOT NULL 
) ENGINE = MyISAM; 

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
-- Structure de la table `perso_in_compagnie`
--

CREATE TABLE `perso_in_compagnie` (
  `id_perso` int(11) NOT NULL DEFAULT '0',
  `id_compagnie` int(11) NOT NULL DEFAULT '0',
  `poste_compagnie` int(11) NOT NULL DEFAULT '0',
  `attenteValidation_compagnie` int(11) NOT NULL DEFAULT '1'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `perso_in_em`
--
CREATE TABLE `perso_in_em` (
  `id_perso` int(11) NOT NULL,
  `camp_em` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `perso_in_mission`
--
CREATE TABLE IF NOT EXISTS `perso_in_mission` (
  `id_perso` int(11) NOT NULL,
  `id_mission` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `perso_in_train`
--

CREATE TABLE `perso_in_train` (
  `id_train` int(11) NOT NULL,
  `id_perso` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `pnj`
--

CREATE TABLE `pnj` (
  `id_pnj` int(11) NOT NULL,
  `nom_pnj` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `pvMax_pnj` int(11) NOT NULL DEFAULT '0',
  `degatMin_pnj` int(11) NOT NULL DEFAULT '0',
  `degatMax_pnj` int(11) NOT NULL DEFAULT '0',
  `pm_pnj` int(11) NOT NULL DEFAULT '0',
  `recup_pnj` int(11) NOT NULL DEFAULT '0',
  `protec_pnj` int(11) NOT NULL DEFAULT '0',
  `perception_pnj` int(11) NOT NULL DEFAULT '0',
  `precision_pnj` int(11) NOT NULL DEFAULT '70',
  `aggressivite_pnj` int(11) NOT NULL DEFAULT '0',
  `description_pnj` text CHARACTER SET utf8 COLLATE utf8_unicode_ci
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
-- Structure de la table `poste`
--

CREATE TABLE `poste` (
  `id_poste` int(11) NOT NULL default '0',
  `nom_poste` varchar(25) NOT NULL default ''
);

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
-- Structure de la table `stats_camp_kill`
--

CREATE TABLE `stats_camp_kill` (
  `id_camp` tinyint(4) NOT NULL DEFAULT '0',
  `nb_kill` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `stats_camp_pv`
--

CREATE TABLE `stats_camp_pv` (
  `id_camp` int(11) NOT NULL,
  `points_victoire` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `tentative_triche`
--
CREATE TABLE `tentative_triche` ( 
	`id_tentative` INT NOT NULL AUTO_INCREMENT , 
	`id_perso` INT NULL , 
	`texte_tentative` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL , 
	PRIMARY KEY (`id_tentative`)
) ENGINE = MyISAM;

-- --------------------------------------------------------

--
-- Structure de la table `train_compteur_blocage`
--

CREATE TABLE IF NOT EXISTS `train_compteur_blocage` (
  `id_train` int(11) NOT NULL,
  `compteur` int(11) NOT NULL DEFAULT '0',
  `date_debut_blocage` datetime NOT NULL
) ENGINE=MyISAM;

--
-- Structure de la table `train_last_dep`
--

CREATE TABLE `train_last_dep` ( 
	`id_train` INT NOT NULL , 
	`x_last_dep` INT NOT NULL , 
	`y_last_dep` INT NOT NULL 
) ENGINE = MyISAM; 

-- --------------------------------------------------------

--
-- Structure de la table `type_unite`
--

CREATE TABLE `type_unite` (
  `id_unite` int(11) NOT NULL,
  `nom_unite` varchar(250) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `description_unite` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `perception_unite` int(11) NOT NULL,
  `protection_unite` int(11) NOT NULL,
  `recup_unite` int(11) NOT NULL,
  `pv_unite` int(11) NOT NULL,
  `pa_unite` int(11) NOT NULL,
  `pm_unite` int(11) NOT NULL,
  `image_unite` VARCHAR(255) NULL,
  `cout_pg` int(11) NOT NULL,
  `capturer_bat` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `user_failed_logins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `ip_address` int(11) unsigned DEFAULT NULL,
  `attempted_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;

--
-- Structure de la table `zones`
--

CREATE TABLE `zones` (
  `id_zone` int(11) NOT NULL,
  `xMin_zone` int(11) NOT NULL DEFAULT '0',
  `xMax_zone` int(11) NOT NULL DEFAULT '0',
  `yMin_zone` int(11) NOT NULL DEFAULT '0',
  `yMax_zone` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `zone_respawn_camp`
--

CREATE TABLE IF NOT EXISTS `zone_respawn_camp` (
  `id_zone` int(11) NOT NULL DEFAULT '0',
  `id_camp` int(11) NOT NULL,
  `x_min_zone` int(11) NOT NULL,
  `x_max_zone` int(11) NOT NULL,
  `y_min_zone` int(11) NOT NULL,
  `y_max_zone` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Structure de la table `log_action_animation`
--

CREATE TABLE IF NOT EXISTS `log_action_animation` (
  `id` int(11) NOT NULL DEFAULT '0',
  `date_acces` DATETIME NOT NULL,
  `id_perso` int(11) NOT NULL,
  `page` varchar(50) NOT NULL,
  `action` varchar(50) NOT NULL,
  `texte` varchar(50) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table permettant de gérer les ordres données à la compagnie
--
CREATE TABLE `compagnie_ordre` (
  `id_compagnie` int(11) NOT NULL,
  `ordre` varchar(2000) NOT NULL
);

--
-- Index pour les tables exportées
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
-- Index pour la table `arme`
--
ALTER TABLE `arme`
  ADD PRIMARY KEY (`id_arme`);

--
-- Index pour la table `armure`
--
ALTER TABLE `armure`
  ADD PRIMARY KEY (`id_armure`);

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
-- Index pour la table `compagnies`
--
ALTER TABLE `compagnies`
  ADD PRIMARY KEY (`id_compagnie`);
  
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
-- Index pour la table `declaration_babysitte`
--  
ALTER TABLE `declaration_babysitte`
  ADD PRIMARY KEY (`id_declaration`);

--
-- Index pour la table `em_creer_compagnie`
--
ALTER TABLE `em_creer_compagnie`
  ADD PRIMARY KEY (`id_em_creer_compagnie`);  
  
--
-- Index pour la table `evenement`
--
ALTER TABLE `evenement`
  ADD PRIMARY KEY (`ID_evenement`);

--
-- Index pour la table `grades`
--
ALTER TABLE `grades`
  ADD PRIMARY KEY (`id_grade`); 
  
--
-- Index pour la table `histobanque_compagnie`
--
ALTER TABLE `histobanque_compagnie`
  ADD PRIMARY KEY (`id_histo`);
  
--
-- Index pour la table `instance_batiment`
--
ALTER TABLE `instance_batiment`
  ADD PRIMARY KEY (`id_instanceBat`);
  
--
-- Index pour la table `instance_batiment_canon`
--
ALTER TABLE `instance_batiment_canon`
  ADD PRIMARY KEY (`id_instance_canon`);

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
-- Index pour la table `liens_activation`
--
ALTER TABLE `liens_activation`
  ADD UNIQUE KEY `unique_id_lien` (`id_lien`);  
  
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
-- Index pour la table `objet`
--
ALTER TABLE `objet`
  ADD PRIMARY KEY (`id_objet`);

--
-- Index pour la table `perso`
--
ALTER TABLE `perso`
  ADD PRIMARY KEY (`id_perso`);

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
-- Index pour la table `perso_as_grade`
--
ALTER TABLE `perso_as_grade`
  ADD PRIMARY KEY (`id_perso`,`id_grade`); 

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
-- Index pour la table `type_unite`
--
ALTER TABLE `type_unite`
  ADD KEY `id_unite` (`id_unite`);

--
-- Index pour la table `zones`
--
ALTER TABLE `zones`
  ADD PRIMARY KEY (`id_zone`);

--
-- AUTO_INCREMENT pour les tables exportées
--

--
-- AUTO_INCREMENT pour la table `action`
--
ALTER TABLE `action`
  MODIFY `id_action` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `arme`
--
ALTER TABLE `arme`
  MODIFY `id_arme` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `armure`
--
ALTER TABLE `armure`
  MODIFY `id_armure` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `batiment`
--
ALTER TABLE `batiment`
  MODIFY `id_batiment` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `compagnies`
--
ALTER TABLE `compagnies`
  MODIFY `id_compagnie` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `competence`
--
ALTER TABLE `competence`
  MODIFY `id_competence` int(11) NOT NULL AUTO_INCREMENT;
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
-- AUTO_INCREMENT pour la table `declaration_babysitte`
--
ALTER TABLE `declaration_babysitte`
  MODIFY `id_declaration` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `dossier`
--
ALTER TABLE `dossier`
  MODIFY `id_dossier` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `em_creer_compagnie`
--
ALTER TABLE `em_creer_compagnie`
  MODIFY `id_em_creer_compagnie` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `evenement`
--
ALTER TABLE `evenement`
  MODIFY `ID_evenement` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `grades`
--
ALTER TABLE `grades`
  MODIFY `id_grade` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `histobanque_compagnie`
--
ALTER TABLE `histobanque_compagnie`
  MODIFY `id_histo` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `instance_batiment`
--
ALTER TABLE `instance_batiment`
  MODIFY `id_instanceBat` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50000;
--
-- AUTO_INCREMENT pour la table `instance_batiment_canon`
--
ALTER TABLE `instance_batiment_canon`
  MODIFY `id_instance_canon` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `instance_pnj`
--
ALTER TABLE `instance_pnj`
  MODIFY `idInstance_pnj` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=200000;
--
-- AUTO_INCREMENT pour la table `joueur`
--
ALTER TABLE `joueur`
  MODIFY `id_joueur` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `message`
--
ALTER TABLE `message`
  MODIFY `id_message` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `news`
--
ALTER TABLE `news`
  MODIFY `id_news` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `objet`
--
ALTER TABLE `objet`
  MODIFY `id_objet` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `perso`
--
ALTER TABLE `perso`
  MODIFY `id_perso` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;
--
-- AUTO_INCREMENT pour la table `pnj`
--
ALTER TABLE `pnj`
  MODIFY `id_pnj` int(11) NOT NULL AUTO_INCREMENT;
  
  --
-- AUTO_INCREMENT pour la table `zones`
--
ALTER TABLE `zones`
  MODIFY `id_zone` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `compagnies`
--
ALTER TABLE `compagnies`
  MODIFY `id_compagnie` int(11) NOT NULL AUTO_INCREMENT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

