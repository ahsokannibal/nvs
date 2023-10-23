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

--
-- Structure de la table `acces_log`
--

CREATE TABLE IF NOT EXISTS `acces_log` (
  `id_acces` int(11) NOT NULL AUTO_INCREMENT,
  `date_acces` datetime NOT NULL,
  `id_perso` int(11) NOT NULL,
  `page` varchar(255) NOT NULL,
  PRIMARY KEY (`id_acces`),
  KEY `index_acces_log` (`date_acces`,`id_perso`),
  KEY `index_verifPage` (`id_perso`,`date_acces`,`page`),
  KEY `index_log2` (`id_perso`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

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
  `passif_action` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_action`),
  KEY `index_actionActionOK` (`id_action`,`nb_points`),
  KEY `index_nomActionPassif` (`id_action`,`nom_action`,`passif_action`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- AUTO_INCREMENT pour la table `action`
--
ALTER TABLE `action`
  MODIFY `id_action` int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- Structure de la table `action_as_batiment`
--

CREATE TABLE `action_as_batiment` (
  `id_action` int(11) NOT NULL,
  `id_batiment` int(11) NOT NULL,
  `contenance` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_action`,`id_batiment`)
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
  PRIMARY KEY (`id_alerte`),
  KEY `index_typeAlerte` (`type_alerte`,`date_alerte`,`id_perso`)
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
  PRIMARY KEY (`id`),
  KEY `index_statut_anim` (`id`,`statut`),
  KEY `index_statut1` (`statut`),
  KEY `index_dateCapture` (`id_perso_capture`,`date_capture`)
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
  PRIMARY KEY (`id`),
  KEY `index_animId_Id` (`id`,`id_perso`),
  KEY `index_idParentQuestion` (`id`,`id_parent`),
  KEY `index_persoCampIdStatut` (`id`,`id_perso`,`id_camp`,`status`),
  KEY `index_camp_statut` (`id_camp`,`status`)
) ENGINE = MyISAM;

-- --------------------------------------------------------

--
-- Structure de la tables `anti_zerk`
--

CREATE TABLE `anti_zerk` (
	`id_perso` INT NOT NULL,
	`date_derniere_attaque` DATETIME NOT NULL,
	`date_nouveau_tour` DATETIME NOT NULL,
  KEY `index_gestion_antiZerk` (`id_perso`)
) ENGINE = MyISAM;

CREATE TABLE IF NOT EXISTS `anti_zerk_banque_compagnie` (
  `id_anti_zerk` int(11) NOT NULL AUTO_INCREMENT,
  `id_perso` int(11) NOT NULL,
  `date_dernier_retrait` datetime NOT NULL,
  PRIMARY KEY (`id_anti_zerk`),
  KEY `index_antiZerkPerso` (`id_perso`)
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
  `image_arme` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id_arme`),
  KEY `index_qualiteArme` (`porteeMin_arme`,`porteeMax_arme`,`qualite_arme`),
  KEY `index_porteemax1` (`porteeMax_arme`,`qualite_arme`),
  KEY `index_minMax_or` (`porteeMin_arme`,`porteeMax_arme`,`coutOr_arme`),
  KEY `index_proteeMax_or` (`porteeMax_arme`,`coutOr_arme`),
  KEY `index_armeportee1` (`id_arme`,`porteeMax_arme`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- AUTO_INCREMENT pour la table `arme`
--
ALTER TABLE `arme`
  MODIFY `id_arme` int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- Structure de la table `arme_as_type_unite`
--

CREATE TABLE `arme_as_type_unite` (
  `id_arme` int(11) NOT NULL,
  `id_type_unite` int(11) NOT NULL,
  KEY `index_nom_unite` (`id_arme`,`id_type_unite`)
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
  `image_armure` varchar(200) NOT NULL DEFAULT '',
  PRIMARY KEY (`id_armure`),
  KEY `index_portarmure` (`id_armure`,`corps_armure`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- AUTO_INCREMENT pour la table `armure`
--
ALTER TABLE `armure`
  MODIFY `id_armure` int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- Structure de la table `banque_as_compagnie`
--

CREATE TABLE `banque_as_compagnie` (
  `id_compagnie` int(11) NOT NULL default '0',
  `montant` int(11) NOT NULL default '0',
  KEY `index_banque_compagnie` (`id_compagnie`)
);

-- --------------------------------------------------------

--
-- Structure de la table `banque_compagnie`
--

CREATE TABLE `banque_compagnie` (
  `id_perso` int(11) NOT NULL default '0',
  `montant` int(11) NOT NULL default '0',
  `demande_emprunt` int(11) NOT NULL default '0',
  `montant_emprunt` int(11) NOT NULL default '0',
  KEY `index_supp_banque_compa` (`id_perso`),
  KEY `index_demandeemprunt` (`id_perso`,`demande_emprunt`),
  KEY `index_montantbanque` (`montant`)
);

-- --------------------------------------------------------

--
-- Structure de la table `banque_log`
--

CREATE TABLE `banque_log` (
	`id_log` INT NOT NULL AUTO_INCREMENT,
	`date_log` DATETIME NOT NULL ,
	`id_compagnie` INT NOT NULL ,
	`id_perso` INT NOT NULL ,
	`montant_transfert` INT NOT NULL ,
	`montant_final` INT NOT NULL,
  PRIMARY KEY (`id_log`),
  KEY `index_supp_banqueLog` (`id_compagnie`),
  KEY `index_veriflogBanque` (`id_log`,`id_compagnie`)
) ENGINE = MyISAM;


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
  `capturable` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_batiment`),
  KEY `index_taille_bat` (`taille_batiment`,`id_batiment`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- AUTO_INCREMENT pour la table `batiment`
--
ALTER TABLE `batiment`
  MODIFY `id_batiment` int(11) NOT NULL AUTO_INCREMENT;


-- --------------------------------------------------------

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


-- --------------------------------------------------------

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
  PRIMARY KEY (`id`),
  UNIQUE KEY `x_carte` (`x_carte`,`y_carte`),
  UNIQUE KEY `index_anim_creer_batiment` (`x_carte`,`y_carte`,`fond_carte`,`coordonnees`,`idPerso_carte`),
  KEY `index_carte_FCarte` (`x_carte`,`y_carte`,`fond_carte`),
  KEY `index_carte_fCarte1` (`occupee_carte`,`x_carte`,`y_carte`,`idPerso_carte`),
  KEY `index_carte_prox_bat` (`x_carte`,`y_carte`,`idPerso_carte`),
  KEY `index_bat_rapat_carte` (`idPerso_carte`),
  KEY `index_rail_autour` (`fond_carte`,`x_carte`,`y_carte`,`coordonnees`),
  KEY `index_coordo_carteGeneral` (`coordonnees`,`vue_nord_date`) USING BTREE,
  KEY `index_vue_visible_nord` (`coordonnees`,`vue_nord_date`,`vue_nord`),
  KEY `index_carte_deja_vue` (`vue_nord`),
  KEY `index_verifFondCarte` (`fond_carte`),
  KEY `index_sauvCarte` (`save_info_carte`),
  KEY `index_persoXY_occupee` (`x_carte`,`y_carte`,`fond_carte`,`occupee_carte`,`idPerso_carte`),
  KEY `index_carteSave1` (`x_carte`,`y_carte`,`fond_carte`,`save_info_carte`),
  KEY `index_carte_YX1` (`x_carte`,`y_carte`,`fond_carte`,`idPerso_carte`),
  KEY `index_carte_XY_pont` (`x_carte`,`y_carte`,`fond_carte`,`save_info_carte`,`coordonnees`),
  KEY `index_rechercherail` (`vue_nord`,`fond_carte`),
  KEY `index_imageCarte` (`x_carte`,`image_carte`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

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

-- --------------------------------------------------------

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
-- Structure de la table `carte_historique`
--

CREATE TABLE `carte_historique` (
 `id_carte_historique` int(11) NOT NULL AUTO_INCREMENT,
 `clan` int(11) NOT NULL,
 `carte_date` date NOT NULL,
 `carte_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`carte_json`)),
 PRIMARY KEY (`id_carte_historique`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

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

ALTER TABLE `choix_carte_suivante`
  ADD PRIMARY KEY (`id_choix`),
  ADD KEY `index_choixCarte` (`id_camp`);

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

ALTER TABLE `compagnies`
  ADD PRIMARY KEY (`id_compagnie`),
  ADD KEY `index_AllCompagnie1` (`id_compagnie`,`id_clan`),
  ADD KEY `index_compagnieParent` (`id_parent`),
  ADD KEY `index_clan_parent` (`id_clan`,`id_parent`),
  ADD KEY `index_compagnie_parent_clan` (`id_compagnie`,`id_clan`,`id_parent`),
  ADD KEY `index_par_nomcompagnie` (`nom_compagnie`),
  ADD KEY `index_compagnieCLan` (`id_clan`),
  ADD KEY `index_geniecompagnie` (`id_compagnie`,`id_clan`,`genie_civil`),
  ADD KEY `index_sigenie` (`id_compagnie`,`genie_civil`),
  ADD KEY `index_idcompParent` (`id_compagnie`,`id_parent`);


  --
  -- AUTO_INCREMENT pour la table `compagnies`
  --
  ALTER TABLE `compagnies`
    MODIFY `id_compagnie` int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- Structure de la table `compagnie_as_contraintes`
--

CREATE TABLE `compagnie_as_contraintes` (
  `id_compagnie` int(11) NOT NULL,
  `contrainte_type_perso` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

ALTER TABLE `compagnie_as_contraintes`
  ADD KEY `index_allcontrainte` (`id_compagnie`,`contrainte_type_perso`);

-- --------------------------------------------------------

--
-- Structure de la table `compagnie_demande_anim`
--

CREATE TABLE `compagnie_demande_anim` (
	`id_compagnie` INT NOT NULL ,
	`type_demande` INT NOT NULL ,
	`info_demande` TEXT NOT NULL
) ENGINE = MyISAM;

ALTER TABLE `compagnie_demande_anim`
  ADD UNIQUE KEY `index_supp_demande_comp` (`id_compagnie`),
  ADD KEY `index_demande_Compagnie` (`id_compagnie`,`type_demande`);


-- --------------------------------------------------------

  --
  -- Table permettant de gérer les ordres données à la compagnie
  --
  CREATE TABLE `compagnie_ordre` (
    `id_compagnie` int(11) NOT NULL,
    `ordre` varchar(2000) NOT NULL
  );

  ALTER TABLE `compagnie_ordre`
    ADD UNIQUE KEY `index_compagnie_ordre` (`id_compagnie`);

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

ALTER TABLE `competence`
  ADD PRIMARY KEY (`id_competence`);

  --
  -- AUTO_INCREMENT pour la table `competence`
  --
  ALTER TABLE `competence`
    MODIFY `id_competence` int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- Structure de la table `competence_as_action`
--

CREATE TABLE `competence_as_action` (
  `id_competence` int(11) NOT NULL,
  `id_action` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

ALTER TABLE `competence_as_action`
  ADD PRIMARY KEY (`id_competence`,`id_action`);

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

ALTER TABLE `competence_as_competence`
  ADD PRIMARY KEY (`id_competence`,`id_competence_accessible`);
-- --------------------------------------------------------

--
-- Structure de la table `config_jeu`
--

CREATE TABLE IF NOT EXISTS `config_jeu` (
  `code_config` varchar(255) NOT NULL,
  `valeur_config` varchar(255) NOT NULL,
  `msg` TEXT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

ALTER TABLE `config_jeu`
  ADD KEY `index_config` (`code_config`);

-- --------------------------------------------------------

--
-- Structure de la table `contact`
--

CREATE TABLE `contact` (
  `id_contact` int(11) NOT NULL,
  `nom_contact` varchar(50) NOT NULL DEFAULT 'amis',
  `contacts` text DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

ALTER TABLE `contact`
  ADD PRIMARY KEY (`id_contact`),
  ADD KEY `index_nomContact` (`id_contact`,`nom_contact`);

  --
  -- AUTO_INCREMENT pour la table `contact`
  --
  ALTER TABLE `contact`
    MODIFY `id_contact` int(11) NOT NULL AUTO_INCREMENT;

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

ALTER TABLE `cv`
  ADD PRIMARY KEY (`ID_cv`),
  ADD KEY `index_cv_date` (`IDActeur_cv`,`special`,`date_cv`),
  ADD KEY `index_nb_kill` (`special`,`IDActeur_cv`,`IDCible_cv`,`date_cv`),
  ADD KEY `index_nomcvperso` (`IDCible_cv`,`IDActeur_cv`,`nomCible_cv`);

  --
  -- AUTO_INCREMENT pour la table `cv`
  --
  ALTER TABLE `cv`
    MODIFY `ID_cv` int(11) NOT NULL AUTO_INCREMENT;

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

ALTER TABLE `declaration_babysitte`
  ADD PRIMARY KEY (`id_declaration`),
  ADD KEY `index_baby` (`date_debut`,`date_fin`),
  ADD KEY `index_estbaby` (`id_baby`,`date_debut`,`date_fin`),
  ADD KEY `index_babyPerso` (`id_perso`,`id_baby`,`date_debut`,`date_fin`),
  ADD KEY `index_babyactif` (`date_debut`,`date_fin`,`id_perso`),
  ADD KEY `index_babypasse` (`id_perso`,`date_fin`);

  --
  -- AUTO_INCREMENT pour la table `declaration_babysitte`
  --
  ALTER TABLE `declaration_babysitte`
    MODIFY `id_declaration` int(11) NOT NULL AUTO_INCREMENT;

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

ALTER TABLE `declaration_multi`
  ADD PRIMARY KEY (`id_declaration`),
  ADD KEY `index_declarMulti` (`id_perso`),
  ADD KEY `index_multiactif` (`id_perso`,`id_multi`);

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

ALTER TABLE `decorations`
  ADD PRIMARY KEY (`id_decoration`),
  ADD KEY `index_camp_deco` (`camp_decoration`);

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

ALTER TABLE `dernier_tombe`
  ADD KEY `index_capturecamp` (`camp_perso_capture`,`camp_perso_captureur`),
  ADD KEY `index_derniercap1` (`date_capture`);

-- --------------------------------------------------------

--
-- Structure de la table `dossier`
--

CREATE TABLE `dossier` (
  `id_dossier` int(11) NOT NULL,
  `nom_dossier` varchar(100) NOT NULL DEFAULT 'sans_nom'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

ALTER TABLE `dossier`
  ADD PRIMARY KEY (`id_dossier`);

  --
  -- AUTO_INCREMENT pour la table `dossier`
  --
  ALTER TABLE `dossier`
    MODIFY `id_dossier` int(11) NOT NULL AUTO_INCREMENT;

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

ALTER TABLE `em_creer_compagnie`
  ADD PRIMARY KEY (`id_em_creer_compagnie`),
  ADD KEY `index_emPerso` (`id_perso`),
  ADD KEY `index_emcompagnie` (`camp`);

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

ALTER TABLE `em_position_infra_carte_suivante`
  ADD PRIMARY KEY (`id_infra_carte`),
  ADD KEY `index_em_batiment` (`carte`,`id_batiment`),
  ADD KEY `index_ChoixCartesuivante` (`id_batiment`,`id_camp`,`carte`);

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

ALTER TABLE `em_vote_choix_carte`
  ADD PRIMARY KEY (`id_vote`);

-- --------------------------------------------------------

--
-- Structure de la table `em_vote_creer_compagnie`
--

CREATE TABLE `em_vote_creer_compagnie` (
  `id_em_creer_compagnie` int(11) NOT NULL,
  `id_em_perso` int(11) NOT NULL,
  `vote` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

ALTER TABLE `em_vote_creer_compagnie`
  ADD KEY `index_creercompa` (`id_em_creer_compagnie`,`id_em_perso`),
  ADD KEY `index_emCompa1` (`id_em_creer_compagnie`);

  --
  -- AUTO_INCREMENT pour la table `em_creer_compagnie`
  --
  ALTER TABLE `em_creer_compagnie`
    MODIFY `id_em_creer_compagnie` int(11) NOT NULL AUTO_INCREMENT;

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

ALTER TABLE `evenement`
  ADD PRIMARY KEY (`ID_evenement`),
  ADD KEY `index_acteurEvenement` (`IDActeur_evenement`),
  ADD KEY `index_evenement1` (`IDCible_evenement`,`IDActeur_evenement`,`date_evenement`,`ID_evenement`),
  ADD KEY `index_evenement2` (`IDActeur_evenement`,`IDCible_evenement`,`ID_evenement`);

  --
  -- AUTO_INCREMENT pour la table `evenement`
  --
  ALTER TABLE `evenement`
    MODIFY `ID_evenement` int(11) NOT NULL AUTO_INCREMENT;


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

ALTER TABLE `grades`
  ADD PRIMARY KEY (`id_grade`),
  ADD UNIQUE KEY `index_verif_grade` (`id_grade`,`pc_grade`),
  ADD KEY `index_stat_grade3` (`id_grade`,`nom_grade`,`pc_grade`);

  --
  -- AUTO_INCREMENT pour la table `grades`
  --
  ALTER TABLE `grades`
    MODIFY `id_grade` int(11) NOT NULL AUTO_INCREMENT;

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

ALTER TABLE `histobanque_compagnie`
  ADD PRIMARY KEY (`id_histo`),
  ADD KEY `Index_dette_perso` (`id_perso`,`id_compagnie`),
  ADD KEY `index_supp_histobanque` (`id_perso`),
  ADD KEY `index_supp_histo_banque` (`id_compagnie`),
  ADD KEY `index_banqueOp` (`id_perso`,`id_compagnie`,`operation`),
  ADD KEY `index_idComphisto` (`id_histo`,`id_compagnie`);

  --
  -- AUTO_INCREMENT pour la table `histobanque_compagnie`
  --
  ALTER TABLE `histobanque_compagnie`
    MODIFY `id_histo` int(11) NOT NULL AUTO_INCREMENT;

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

ALTER TABLE `historique_punitions`
  ADD PRIMARY KEY (`id`);

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

ALTER TABLE `histo_stats_camp_pv`
  ADD KEY `index_histocamp` (`id_camp`,`date_pvict`);
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

--
ALTER TABLE `instance_batiment`
  ADD PRIMARY KEY (`id_instanceBat`),
  ADD KEY `index_instance_bat_prox_bat` (`id_batiment`,`id_instanceBat`),
  ADD KEY `index_bat_rapat_instanceBat` (`id_instanceBat`,`camp_instance`,`pv_instance`,`pvMax_instance`,`x_instance`,`y_instance`),
  ADD KEY `index_bat_rapat_instanceBat1` (`id_batiment`,`camp_instance`),
  ADD KEY `index_action_train` (`id_batiment`),
  ADD KEY `index_instBat_carteGeneral` (`x_instance`,`y_instance`,`id_batiment`,`camp_instance`),
  ADD KEY `index_perso_bat_general` (`id_instanceBat`,`x_instance`,`y_instance`),
  ADD KEY `index_degradation_bat` (`id_batiment`,`x_instance`,`y_instance`),
  ADD KEY `index_est_batiment_camp` (`id_instanceBat`,`contenance_instance`,`id_batiment`,`camp_instance`),
  ADD KEY `index_camp2` (`id_instanceBat`,`id_batiment`,`camp_instance`),
  ADD KEY `index_idBat_camp1` (`id_instanceBat`,`camp_instance`),
  ADD KEY `index_taillebatiment` (`x_instance`,`y_instance`,`pv_instance`,`camp_instance`,`id_batiment`),
  ADD KEY `index_recherchegare` (`pv_instance`,`id_batiment`,`camp_instance`),
  ADD KEY `index_instanceXY` (`x_instance`,`y_instance`);

  --
  -- AUTO_INCREMENT pour la table `instance_batiment`
  --
  ALTER TABLE `instance_batiment`
    MODIFY `id_instanceBat` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50000;


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

--
ALTER TABLE `instance_batiment_canon`
  ADD PRIMARY KEY (`id_instance_canon`),
  ADD KEY `index_canon_instanceBat` (`id_instance_bat`,`date_activation`),
  ADD KEY `index_canon_instancebat1` (`id_instance_canon`,`date_activation`),
  ADD KEY `index_instanceCanon1` (`id_instance_bat`);

  --
  -- AUTO_INCREMENT pour la table `instance_batiment_canon`
  --
  ALTER TABLE `instance_batiment_canon`
    MODIFY `id_instance_canon` int(11) NOT NULL AUTO_INCREMENT;



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

ALTER TABLE `instance_pnj`
  ADD PRIMARY KEY (`idInstance_pnj`),
  ADD KEY `index_cron_pnj_nbPNJ` (`id_pnj`),
  ADD KEY `index_cron_action_pnj` (`deplace_i`,`idInstance_pnj`),
  ADD KEY `index_cron_action_pnj_instancepnj` (`id_pnj`,`idInstance_pnj`);

  --
  -- AUTO_INCREMENT pour la table `instance_pnj`
  --
  ALTER TABLE `instance_pnj`
    MODIFY `idInstance_pnj` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=200000;


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

ALTER TABLE `joueur`
  ADD PRIMARY KEY (`id_joueur`),
  ADD UNIQUE KEY `index_id_mail` (`id_joueur`,`email_joueur`),
  ADD UNIQUE KEY `id_perso` (`nom_joueur`),
  ADD KEY `index_verif_mailJoueur` (`email_joueur`),
  ADD KEY `index_est_pendu` (`nom_joueur`,`pendu`),
  ADD KEY `index_estAnim` (`id_joueur`,`animateur`),
  ADD KEY `index_estRedacteur` (`id_joueur`,`redacteur`),
  ADD KEY `index_EstAdmin` (`id_joueur`,`admin_perso`),
  ADD KEY `index_mpd_id` (`id_joueur`,`mdp_joueur`);

  --
  -- AUTO_INCREMENT pour la table `joueur`
  --
  ALTER TABLE `joueur`
    MODIFY `id_joueur` int(11) NOT NULL AUTO_INCREMENT;

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

ALTER TABLE `joueur_as_ip`
  ADD KEY `index_supp_joueurIP` (`id_joueur`),
  ADD KEY `index_ip_joueur` (`id_joueur`,`ip_joueur`,`date_premier_releve`,`date_dernier_releve`);



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

ALTER TABLE `liaisons_gare`
  ADD KEY `index_liaisonTrain` (`id_train`),
  ADD KEY `index_liaisongare1gare2` (`id_gare1`,`id_gare2`),
  ADD KEY `index_gare1` (`id_gare1`);

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

ALTER TABLE `liens_activation`
  ADD UNIQUE KEY `unique_id_lien` (`id_lien`);

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

ALTER TABLE `log`
  ADD PRIMARY KEY (`id_log`),
  ADD KEY `index1` (`id_log`,`id_perso`);

  -- --------------------------------------------------------

  --
  -- Structure de la table `log_action_animation`
  --

  CREATE TABLE IF NOT EXISTS `log_action_animation` (
    `id` int(11) NOT NULL DEFAULT '0',
    `date_acces` DATETIME NOT NULL,
    `id_perso` int(11) NOT NULL,
    `page` varchar(50) NOT NULL,
    `action` varchar(50) NOT NULL,
    `texte` varchar(250) NOT NULL
  ) ENGINE=MyISAM DEFAULT CHARSET=latin1;

  ALTER TABLE `log_action_animation`
    ADD KEY `index_pardateacces` (`date_acces`);


    -- --------------------------------------------------------

    --
    -- Structure de la table `log_pendaison`
    --

    CREATE TABLE IF NOT EXISTS `log_pendaison` (
      `id_pendaison` int(11) NOT NULL AUTO_INCREMENT,
      `date_pendaison` datetime NOT NULL,
      `nom_perso` varchar(255) NOT NULL,
      `id_perso` int(11) NOT NULL,
      `raison_pendaison` text NOT NULL,
      PRIMARY KEY (`id_pendaison`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

    ALTER TABLE `log_pendaison`
      ADD PRIMARY KEY (`id_pendaison`);

-- --------------------------------------------------------

--
-- Structure de la table `log_respawn`
--

CREATE TABLE `log_respawn` (
	`id_log_respawn` INT NOT NULL AUTO_INCREMENT ,
	`id_perso` INT NOT NULL ,
	`date_respawn` DATETIME NOT NULL ,
	`texte_respawn` TEXT NOT NULL ,
	PRIMARY KEY (`id_log_respawn`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `log_respawn`
  ADD PRIMARY KEY (`id_log_respawn`),
  ADD KEY `index_log_perso` (`id_perso`);



-- --------------------------------------------------------

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

ALTER TABLE `message`
  ADD PRIMARY KEY (`id_message`),
  ADD KEY `index_idExpedieur` (`id_expediteur`) USING BTREE,
  ADD KEY `index_expediteur_qui` (`id_message`,`id_expediteur`),
  ADD KEY `index_dateMessage` (`id_message`,`id_expediteur`,`date_message`),
  ADD KEY `index_message3` (`id_message`,`date_message`);

  --
  -- AUTO_INCREMENT pour la table `message`
  --
  ALTER TABLE `message`
    MODIFY `id_message` int(11) NOT NULL AUTO_INCREMENT;

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

ALTER TABLE `message_perso`
  ADD PRIMARY KEY (`id_message`,`id_perso`),
  ADD KEY `index_message_P` (`id_message`,`id_perso`),
  ADD KEY `index_messagePerso1` (`id_perso`),
  ADD KEY `index_messagesupp` (`id_message`,`lu_message`,`supprime_message`),
  ADD KEY `index_messagelu` (`id_perso`,`lu_message`,`supprime_message`),
  ADD KEY `index_persomessage1` (`id_message`,`id_perso`,`lu_message`),
  ADD KEY `index_idMess` (`id_message`),
  ADD KEY `index_messagedossier` (`id_perso`,`id_dossier`,`supprime_message`,`id_message`);

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

ALTER TABLE `missions`
  ADD PRIMARY KEY (`id_mission`),
  ADD KEY `index_camp_id` (`id_mission`,`camp_mission`),
  ADD KEY `index_nomCamp` (`camp_mission`,`nom_mission`),
  ADD KEY `index_dateDebut_fin_mission` (`date_debut_mission`,`date_fin_mission`,`objectif_atteint`,`camp_mission`),
  ADD KEY `index_dateDebutMission` (`date_debut_mission`,`camp_mission`),
  ADD KEY `index_dateFinmission` (`date_fin_mission`,`camp_mission`,`objectif_atteint`),
  ADD KEY `index_missioncamp1` (`date_debut_mission`,`date_fin_mission`,`camp_mission`);

-- --------------------------------------------------------

--
-- Structure de la table `nb_online`
--

CREATE TABLE `nb_online` (
  `ip` varchar(15) NOT NULL DEFAULT '',
  `time` bigint(16) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

ALTER TABLE `nb_online`
  ADD KEY `index_enLigne` (`ip`),
  ADD KEY `index_tempsLigne` (`time`);

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

ALTER TABLE `news`
  ADD PRIMARY KEY (`id_news`);

  --
  -- AUTO_INCREMENT pour la table `news`
  --
  ALTER TABLE `news`
    MODIFY `id_news` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;



-- --------------------------------------------------------

--
-- Structure de la table `objet`
--

CREATE TABLE `objet` (
  `id_objet` int(11) NOT NULL,
  `nom_objet` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `description_objet` text NOT NULL,
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
  `contient_alcool`	tinyint [0] NOT NULL DEFAULT '0',
  `echangeable`	tinyint [0] NOT NULL DEFAULT '1',
  `deposable` tinyint [0] NOT NULL DEFAULT '1',
  `type_objet` varchar(3) NOT NULL DEFAULT 'N'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

ALTER TABLE `objet`
  ADD PRIMARY KEY (`id_objet`),
  ADD UNIQUE KEY `index_echangeableTypeObj` (`id_objet`,`type_objet`,`echangeable`),
  ADD KEY `index_typeObjet` (`type_objet`),
  ADD KEY `index_verif_typeObjet` (`id_objet`,`type_objet`),
  ADD KEY `index_ordreOr` (`id_objet`,`type_objet`,`coutOr_objet`),
  ADD KEY `index_echangeable` (`id_objet`,`echangeable`),
  ADD KEY `index_typeObjEchange` (`type_objet`,`echangeable`);

  --
  -- AUTO_INCREMENT pour la table `objet`
  --
  ALTER TABLE `objet`
    MODIFY `id_objet` int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- Structure de la table `objet_as_type_unite`
--

CREATE TABLE `objet_as_type_unite` (
  `id_objet` int(11) NOT NULL,
  `id_type_unite` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

ALTER TABLE `objet_as_type_unite`
  ADD KEY `index_test_idobjtyp` (`id_objet`,`id_type_unite`);
COMMIT;

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

ALTER TABLE `objet_in_carte`
  ADD UNIQUE KEY `INDEX_OBJinCarte_est_capturee` (`type_objet`,`id_objet`,`y_carte`,`x_carte`),
  ADD KEY `index_objet_in_carte` (`x_carte`,`y_carte`),
  ADD KEY `index_carteObJXY` (`x_carte`,`id_objet`);

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


ALTER TABLE `perso`
  ADD PRIMARY KEY (`id_perso`),
  ADD UNIQUE KEY `index_chef_estdsCompagnie` (`id_perso`,`idJoueur_perso`,`chef`),
  ADD KEY `index_perso_joueur` (`id_perso`,`idJoueur_perso`),
  ADD KEY `index_perso_Fcarte1` (`id_perso`,`x_perso`,`y_perso`,`pv_perso`),
  ADD KEY `index_perso_visu` (`x_perso`,`y_perso`,`idJoueur_perso`,`est_gele`,`type_perso`,`pv_perso`),
  ADD KEY `index_perso_Visu_camp` (`x_perso`,`y_perso`,`idJoueur_perso`,`clan`,`est_gele`,`type_perso`,`pv_perso`),
  ADD KEY `index_perso_prox_bat_perso` (`x_perso`,`y_perso`,`id_perso`),
  ADD KEY `index_nb_ennemi_siege_batiment` (`id_perso`,`clan`),
  ADD KEY `index_bat_rapat_perso` (`idJoueur_perso`,`chef`),
  ADD KEY `index_perso_gele` (`est_gele`,`date_gele`),
  ADD KEY `index_dateDLA_gele` (`DLA_perso`,`est_gele`,`id_perso`),
  ADD KEY `Index_joueurgele_mail` (`idJoueur_perso`,`est_gele`,`date_gele`,`id_perso`),
  ADD KEY `index_supp_perso` (`idJoueur_perso`),
  ADD KEY `index_stat1` (`id_perso`,`type_perso`),
  ADD KEY `index_all_player` (`est_gele`,`est_renvoye`,`clan`,`id_perso`,`DLA_perso`),
  ADD KEY `index_all_grouillot` (`est_gele`,`est_renvoye`,`id_perso`,`type_perso`,`DLA_perso`),
  ADD KEY `index_All_grade2` (`id_perso`,`est_renvoye`,`est_gele`,`type_perso`,`clan`,`DLA_perso`),
  ADD KEY `index_AllCompagnie2` (`id_perso`,`DLA_perso`),
  ADD KEY `Index_nbperso_actif` (`est_gele`),
  ADD KEY `index_perso_inactif` (`chef`,`est_gele`,`clan`),
  ADD KEY `index_vérifie_nomPerso` (`nom_perso`),
  ADD KEY `index_Perso_pendu` (`idJoueur_perso`,`nom_perso`,`chef`),
  ADD KEY `index_redacAnim_verif` (`idJoueur_perso`,`clan`,`chef`),
  ADD KEY `index_idPersoNom` (`id_perso`,`nom_perso`),
  ADD KEY `index_id_chef_perso` (`id_perso`,`chef`),
  ADD KEY `index_perso_clan_chef` (`chef`,`clan`),
  ADD KEY `index_chefUnique` (`chef`),
  ADD KEY `index_typeUnitePerso` (`type_perso`,`idJoueur_perso`,`chef`),
  ADD KEY `index_listPersoJoueur` (`id_perso`,`idJoueur_perso`,`est_renvoye`,`type_perso`),
  ADD KEY `index_classement` (`id_perso`,`type_perso`,`nb_kill`),
  ADD KEY `index_perso_renvoyer` (`clan`,`est_gele`,`est_renvoye`),
  ADD KEY `index_classement_perso_Clan` (`id_perso`,`clan`,`type_perso`),
  ADD KEY `index_Clan1` (`clan`),
  ADD KEY `index_idPerso_type` (`id_perso`,`type_perso`),
  ADD KEY `index_geniePerso` (`id_perso`,`genie`,`x_perso`,`y_perso`,`est_gele`) USING BTREE,
  ADD KEY `index_persoClan_position` (`clan`,`type_perso`,`x_perso`,`y_perso`,`est_gele`),
  ADD KEY `index_persoPosition` (`x_perso`,`y_perso`,`pv_perso`,`clan`,`est_gele`),
  ADD KEY `index_recrut` (`type_perso`,`idJoueur_perso`,`est_renvoye`),
  ADD KEY `index_indrenvoie` (`id_perso`,`idJoueur_perso`,`est_renvoye`);

  --
  -- AUTO_INCREMENT pour la table `perso`
  --
  ALTER TABLE `perso`
    MODIFY `id_perso` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

-- --------------------------------------------------------

--
-- Structure de la table `perso_as_arme`
--

CREATE TABLE `perso_as_arme` (
  `id_perso` int(11) NOT NULL DEFAULT '0',
  `id_arme` int(11) NOT NULL DEFAULT '0',
  `est_portee` enum('0','1') NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

ALTER TABLE `perso_as_arme`
  ADD KEY `index_arme_equipe` (`id_perso`,`est_portee`),
  ADD KEY `INDEX_cible_capturee` (`id_perso`,`id_arme`,`est_portee`),
  ADD KEY `index_supp_arme` (`id_perso`),
  ADD KEY `index_arme1` (`id_perso`,`id_arme`),
  ADD KEY `index_armePortee1` (`id_arme`,`est_portee`);

-- --------------------------------------------------------

--
-- Structure de la table `perso_as_armure`
--

CREATE TABLE `perso_as_armure` (
  `id_perso` int(11) NOT NULL DEFAULT '0',
  `id_armure` int(11) NOT NULL DEFAULT '0',
  `est_portee` enum('0','1') NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

ALTER TABLE `perso_as_armure`
  ADD KEY `index_supp_armure` (`id_perso`),
  ADD KEY `index_AllArmure` (`id_perso`,`id_armure`,`est_portee`),
  ADD KEY `index_idArmurePerso` (`id_perso`,`id_armure`);

-- --------------------------------------------------------

--
-- Structure de la table `perso_as_competence`
--

CREATE TABLE `perso_as_competence` (
  `id_perso` int(11) NOT NULL,
  `id_competence` int(11) NOT NULL,
  `nb_points` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

ALTER TABLE `perso_as_competence`
  ADD PRIMARY KEY (`id_perso`,`id_competence`),
  ADD KEY `index_supp_competence` (`id_perso`),
  ADD KEY `index_actionOK` (`id_competence`,`nb_points`),
  ADD KEY `index_allpersoasCompetence` (`id_perso`,`id_competence`,`nb_points`);

-- --------------------------------------------------------

--
-- Structure de la table `perso_as_contact`
--

CREATE TABLE `perso_as_contact` (
  `id_perso` int(11) NOT NULL DEFAULT '0',
  `id_contact` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

ALTER TABLE `perso_as_contact`
  ADD PRIMARY KEY (`id_perso`,`id_contact`),
  ADD KEY `index_supp_contact` (`id_perso`),
  ADD KEY `index_messagecontact` (`id_contact`);
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

ALTER TABLE `perso_as_decoration`
  ADD PRIMARY KEY (`id`),
  ADD KEY `index_supp_deco` (`id_perso`),
  ADD KEY `index_recup_decoration` (`id_decoration`,`id_perso`,`date_decoration`);

-- --------------------------------------------------------

--
-- Structure de la table `perso_as_dossiers`
--

CREATE TABLE `perso_as_dossiers` (
  `id_perso` int(11) NOT NULL,
  `id_dossier` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

ALTER TABLE `perso_as_dossiers`
  ADD KEY `index_supp_dossiers` (`id_perso`),
  ADD KEY `index_dossierPerso` (`id_perso`,`id_dossier`);

-- --------------------------------------------------------

--
-- Structure de la table `perso_as_entrainement`
--

CREATE TABLE `perso_as_entrainement` (
  `id_perso` int(11) NOT NULL,
  `niveau_entrainement` int(11) NOT NULL,
  `nb` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

ALTER TABLE `perso_as_entrainement`
  ADD PRIMARY KEY (`id_perso`),
  ADD KEY `index_niveauentrainement` (`id_perso`,`niveau_entrainement`);

-- --------------------------------------------------------

--
-- Structure de la table `perso_as_grade`
--

CREATE TABLE `perso_as_grade` (
  `id_perso` int(11) NOT NULL,
  `id_grade` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

ALTER TABLE `perso_as_grade`
  ADD PRIMARY KEY (`id_perso`,`id_grade`),
  ADD KEY `INDEX_Perso` (`id_perso`);

-- --------------------------------------------------------

--
-- Structure de la table `perso_as_killpnj`
--

CREATE TABLE `perso_as_killpnj` (
  `id_perso` int(11) NOT NULL DEFAULT '0',
  `id_pnj` int(11) NOT NULL DEFAULT '0',
  `nb_pnj` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

ALTER TABLE `perso_as_killpnj`
  ADD PRIMARY KEY (`id_perso`,`id_pnj`),
  ADD KEY `index_supp_killpnj` (`id_perso`),
  ADD KEY `index_nbpnj` (`id_pnj`,`nb_pnj`);

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

ALTER TABLE `perso_as_objet`
  ADD KEY `index_bat_rapat_persoasobj` (`id_objet`,`id_perso`,`equip_objet`),
  ADD KEY `index_etendard_joueur` (`id_perso`,`id_objet`),
  ADD KEY `index_recupPerso_gare` (`id_perso`,`capacite_objet`),
  ADD KEY `index_sup_ticket` (`id_perso`,`id_objet`,`capacite_objet`),
  ADD KEY `index_supp_objet` (`id_perso`),
  ADD KEY `index_persoObjet1` (`id_objet`);

-- --------------------------------------------------------

--
-- Structure de la table `perso_as_respawn`
--

CREATE TABLE `perso_as_respawn` (
  `id_perso` int(11) NOT NULL,
  `id_bat` int(11) NOT NULL,
  `id_instance_bat` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

ALTER TABLE `perso_as_respawn`
  ADD KEY `index_bat_ratrap` (`id_instance_bat`,`id_perso`,`id_bat`) USING BTREE,
  ADD KEY `index_supp_respawn` (`id_perso`),
  ADD KEY `index_instanceBat` (`id_instance_bat`),
  ADD KEY `index_perso_respawn1` (`id_perso`,`id_bat`);

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

ALTER TABLE `perso_bagne`
  ADD PRIMARY KEY (`id`),
  ADD KEY `index_perso_banni` (`id_perso`,`date_debut`),
  ADD KEY `index_delete_bagne1` (`id_perso`);

-- --------------------------------------------------------

--
-- Structure de la table `perso_demande_anim`
--

CREATE TABLE `perso_demande_anim` (
	`id_perso` INT NOT NULL ,
	`type_demande` INT NOT NULL ,
	`info_demande` TEXT NOT NULL
) ENGINE = MyISAM;

ALTER TABLE `perso_demande_anim`
  ADD KEY `index_typePerso_demande` (`id_perso`,`type_demande`);

-- --------------------------------------------------------

--
-- Structure de la table `perso_in_batiment`
--

CREATE TABLE `perso_in_batiment` (
  `id_perso` int(11) NOT NULL DEFAULT '0',
  `id_instanceBat` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

ALTER TABLE `perso_in_batiment`
  ADD PRIMARY KEY (`id_perso`,`id_instanceBat`),
  ADD KEY `index_perso_bat_inBat` (`id_perso`),
  ADD KEY `index_batvide` (`id_instanceBat`);

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

ALTER TABLE `perso_in_compagnie`
  ADD UNIQUE KEY `index_verifAttenteCompa` (`id_perso`,`attenteValidation_compagnie`),
  ADD KEY `index_perso_compagnie` (`id_perso`),
  ADD KEY `index_carte_general` (`id_perso`,`id_compagnie`),
  ADD KEY `index_AllCompagnie` (`id_perso`,`id_compagnie`,`attenteValidation_compagnie`),
  ADD KEY `index_perso_poste` (`id_perso`,`poste_compagnie`,`id_compagnie`),
  ADD KEY `index_recupPersoCompagnie` (`id_compagnie`),
  ADD KEY `index_est_chef` (`id_perso`,`poste_compagnie`),
  ADD KEY `index_compaAttente` (`id_compagnie`,`attenteValidation_compagnie`),
  ADD KEY `index_allCompagnie1` (`id_perso`,`id_compagnie`,`poste_compagnie`,`attenteValidation_compagnie`),
  ADD KEY `index_rechercheposte1` (`poste_compagnie`);

-- --------------------------------------------------------

--
-- Structure de la table `perso_in_em`
--
CREATE TABLE `perso_in_em` (
  `id_perso` int(11) NOT NULL,
  `camp_em` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

ALTER TABLE `perso_in_em`
  ADD KEY `index_supp_persoEM` (`id_perso`),
  ADD KEY `index_campem` (`camp_em`),
  ADD KEY `index_emall` (`id_perso`,`camp_em`);

-- --------------------------------------------------------

--
-- Structure de la table `perso_in_mission`
--
CREATE TABLE IF NOT EXISTS `perso_in_mission` (
  `id_perso` int(11) NOT NULL,
  `id_mission` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

ALTER TABLE `perso_in_mission`
  ADD KEY `index_persoMission` (`id_perso`),
  ADD KEY `index_perso_mission` (`id_perso`,`id_mission`),
  ADD KEY `index_persomission1` (`id_mission`);

-- --------------------------------------------------------

--
-- Structure de la table `perso_in_train`
--

CREATE TABLE `perso_in_train` (
  `id_train` int(11) NOT NULL,
  `id_perso` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

ALTER TABLE `perso_in_train`
  ADD KEY `index_perso_intrain_persointrain` (`id_perso`),
  ADD KEY `index_persointrain_instancetrain` (`id_perso`,`id_train`),
  ADD KEY `index_train1` (`id_train`);

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

ALTER TABLE `pnj`
  ADD PRIMARY KEY (`id_pnj`);

  --
  -- AUTO_INCREMENT pour la table `pnj`
  --
  ALTER TABLE `pnj`
    MODIFY `id_pnj` int(11) NOT NULL AUTO_INCREMENT;

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

ALTER TABLE `pnj_in_zone`
  ADD PRIMARY KEY (`id_pnj`,`id_zone`),
  ADD KEY `index_cron_pnj_pnjzone` (`id_pnj`,`id_zone`);

-- --------------------------------------------------------

--
-- Structure de la table `poste`
--

CREATE TABLE `poste` (
  `id_poste` int(11) NOT NULL default '0',
  `nom_poste` varchar(25) NOT NULL default ''
);

ALTER TABLE `poste`
  ADD KEY `index_verif_poste` (`id_poste`);

-- --------------------------------------------------------

--
-- Structure de la table `ressources_entrepot`
--

CREATE TABLE `ressources_entrepot` (
  `id_entrepot` int(11) NOT NULL DEFAULT '0',
  `id_ressource` int(11) NOT NULL DEFAULT '0',
  `nb_ressource` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

ALTER TABLE `ressources_entrepot`
  ADD UNIQUE KEY `id_entrepot` (`id_entrepot`,`id_ressource`),
  ADD KEY `index_entrepot` (`id_entrepot`);

-- --------------------------------------------------------

--
-- Structure de la table `stats_camp_kill`
--

CREATE TABLE `stats_camp_kill` (
  `id_camp` tinyint(4) NOT NULL DEFAULT '0',
  `nb_kill` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

ALTER TABLE `stats_camp_kill`
  ADD KEY `Index_stat_est_capturee` (`id_camp`);

-- --------------------------------------------------------

--
-- Structure de la table `stats_camp_pv`
--

CREATE TABLE `stats_camp_pv` (
  `id_camp` int(11) NOT NULL,
  `points_victoire` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

ALTER TABLE `stats_camp_pv`
  ADD KEY `index_action_PV` (`id_camp`);

-- --------------------------------------------------------

--
-- Structure de la table `tentative_triche`
--
CREATE TABLE `tentative_triche` (
	`id_tentative` INT NOT NULL AUTO_INCREMENT ,
	`date` DATETIME DEFAULT NOW(),
	`id_perso` INT NULL ,
	`texte_tentative` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
	PRIMARY KEY (`id_tentative`)
) ENGINE = MyISAM;

ALTER TABLE `tentative_triche`
  ADD PRIMARY KEY (`id_tentative`);

-- --------------------------------------------------------

--
-- Structure de la table `train_compteur_blocage`
--

CREATE TABLE IF NOT EXISTS `train_compteur_blocage` (
  `id_train` int(11) NOT NULL,
  `compteur` int(11) NOT NULL DEFAULT '0',
  `date_debut_blocage` datetime NOT NULL
) ENGINE=MyISAM;

ALTER TABLE `train_compteur_blocage`
  ADD KEY `index_compteur_train` (`id_train`) USING BTREE;


-- --------------------------------------------------------

--
-- Structure de la table `train_last_dep`
--

CREATE TABLE `train_last_dep` (
	`id_train` INT NOT NULL ,
	`x_last_dep` INT NOT NULL ,
	`y_last_dep` INT NOT NULL
) ENGINE = MyISAM;

ALTER TABLE `train_last_dep`
  ADD KEY `index_train_derniereDepart` (`id_train`);

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

ALTER TABLE `type_unite`
  ADD KEY `id_unite` (`id_unite`),
  ADD KEY `index_all_grouillot1` (`id_unite`,`nom_unite`);

-- --------------------------------------------------------

--
-- Structure de la table `user_failed_logins`
--

CREATE TABLE IF NOT EXISTS `user_failed_logins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `ip_address` int(11) unsigned DEFAULT NULL,
  `attempted_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;


ALTER TABLE `user_failed_logins`
  ADD PRIMARY KEY (`id`),
  ADD KEY `Index_veriflog_attente` (`attempted_at`);

-- --------------------------------------------------------

--
-- Structure de la table `user_ok_logins`
--

CREATE TABLE `user_ok_logins` (
  `id_joueur` int(11) NOT NULL DEFAULT '0',
  `ip_joueur` varchar(100) NOT NULL DEFAULT '000.000.000.000',
  `time` datetime NOT NULL,
  `user_agent` varchar(255) NOT NULL DEFAULT '',
  `cookie_val` varchar(300) NOT NULL DEFAULT '',
  `est_acquitte` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

ALTER TABLE `user_ok_logins`
  ADD KEY `index_veriftriche` (`id_joueur`,`ip_joueur`,`time`,`est_acquitte`),
  ADD KEY `index_valeur_cookie` (`cookie_val`),
  ADD KEY `index_idjoueur_acquitte_cookie` (`id_joueur`,`cookie_val`,`est_acquitte`),
  ADD KEY `index_cookie_id` (`id_joueur`,`cookie_val`),
  ADD KEY `index_time_ipID` (`id_joueur`,`ip_joueur`,`time`),
  ADD KEY `index_cookie_time_id` (`id_joueur`,`cookie_val`,`time`);

-- --------------------------------------------------------

--
-- Structure de la table `whitelist_triche`
--

CREATE TABLE `whitelist_triche` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `id_joueur` int(11) NOT NULL DEFAULT '0',
   PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

ALTER TABLE `whitelist_triche`
  ADD PRIMARY KEY (`id`);

-- --------------------------------------------------------

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

ALTER TABLE `zones`
  ADD PRIMARY KEY (`id_zone`),
  ADD KEY `index_cron_pnj_zone` (`id_zone`);
  --
-- AUTO_INCREMENT pour la table `zones`
--
ALTER TABLE `zones`
  MODIFY `id_zone` int(11) NOT NULL AUTO_INCREMENT;

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


ALTER TABLE `zone_respawn_camp`
  ADD KEY `index_zone_respawn` (`id_camp`);
