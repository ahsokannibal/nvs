<?php
session_start();
require_once("../fonctions.php");

$mysqli = db_connexion();

include ('../nb_online.php');

// recupération config jeu
$sql = "SELECT disponible FROM config_jeu";
$res = $mysqli->query($sql);
$t_dispo = $res->fetch_assoc();

$dispo = $t_dispo["disponible"];

if($dispo){

	if (@$_SESSION["id_perso"]) {
		
		//recuperation des varaibles de sessions
		$id = $_SESSION["id_perso"];
		
		$sql = "SELECT pv_perso FROM perso WHERE id_perso='$id'";
		$res = $mysqli->query($sql);
		$tpv = $res->fetch_assoc();
		
		$testpv = $tpv['pv_perso'];
		
		if ($testpv <= 0) {
			echo "<font color=red>Vous êtes mort...</font>";
		}
		else {
			
			$sql = "SELECT idJoueur_perso, nom_perso, pc_perso, pa_perso, chef, clan, bataillon, point_armee_grade, grades.id_grade FROM perso, perso_as_grade, grades 
					WHERE perso.id_perso = perso_as_grade.id_perso
					AND perso_as_grade.id_grade = grades.id_grade 
					AND perso.id_perso ='$id'";
			$res = $mysqli->query($sql);
			$tab = $res->fetch_assoc();
			
			$id_joueur	= $tab["idJoueur_perso"];
			$nom_perso	= $tab["nom_perso"];
			$pc 		= $tab["pc_perso"];
			$pa_perso	= $tab["pa_perso"];
			$chef 		= $tab["chef"];
			$clan		= $tab["clan"];
			$bataillon	= $tab["bataillon"];
			$pg			= $tab["point_armee_grade"];
			$id_grade	= $tab["id_grade"];
			
			if ($clan == 1) {
				$camp = "nord";
				$couleur_clan_perso = "blue";
			} else if ($clan == 2) {
				$camp = "sud";
				$couleur_clan_perso = "red";
			} else {
				// ???
				$camp = "nord";
				$couleur_clan_perso = "blue";
			}
			
			// Seul le chef peut recruter des grouillots
			if ($chef) {
			
				// Récupération du batiment dans lequel se trouve le perso 
				$sql = "SELECT id_instanceBat FROM perso_in_batiment WHERE id_perso='$id'";
				$res = $mysqli->query($sql);
				$tab = $res->fetch_assoc();
				
				$id_instance_bat = $tab["id_instanceBat"];		
			
			
	?>
<html>
	<head>
		<title>Nord VS Sud</title>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			<meta http-equiv="Content-Language" content="fr" />
		<link rel="stylesheet" type="text/css" media="screen" href="onglet.css" title="Version 1" />
	</head>
	
	<body>
		<div id="header">
			<ul>
				<li><a href="profil.php">Profil</a></li>
				<li><a href="ameliorer.php">Améliorer son perso</a></li>
				<?php
				if($chef) {
					echo "<li id=\"current\"><a href=\"#\">Recruter des grouillots</a></li>";
					echo "<li><a href=\"gestion_grouillot.php\">Gérer ses grouillots</a></li>";
				}
				?>
				<li><a href="equipement.php">Equiper son perso</a></li>
				<li><a href="compte.php">Gérer son Compte</a></li>
			</ul>
		</div>
	
		<br /><br /><center><h1>Recrutement des grouillots</h1></center>
		
		<div align=center><input type="button" value="Fermer cette fenêtre" onclick="window.close()"></div>
		<br />
<?php
				if ($id_instance_bat == NULL) {
					
					echo "<center><font color='red'>Vous ne pouvez recruter des grouillots que depuis un Fort ou un Fortin</font></center>";
					
				} else {
					
					// Récupération des informations sur la batiment dans lequel on se trouve 
					$sql = "SELECT pv_instance, pvMax_instance, id_batiment, x_instance, y_instance FROM instance_batiment WHERE id_instanceBat = '$id_instance_bat'";
					$res = $mysqli->query($sql);
					$tab = $res->fetch_assoc();
					
					$id_batiment_instance 	= $tab["id_batiment"];
					$pv_instance			= $tab["pv_instance"];
					$pv_max_instance		= $tab["pvMax_instance"];
					$x_instance				= $tab["x_instance"];
					$y_instance				= $tab["y_instance"];
					
					// Fort ou Fortin
					if ($id_batiment_instance != 8 && $id_batiment_instance != 9) {
						
						echo "<center><font color='red'>Vous ne pouvez recruter des grouillots que depuis un Fort ou un Fortin</font></center>";
						
					} else {
						
						// Calcul pourcentage pv du batiment 
						$pourc_pv_instance = ($pv_instance / $pv_max_instance) * 100;
						
						if ($pourc_pv_instance < 90) {
							
							// Il reste moins de 90% des pv du batiment => siege
							echo "<center><font color='red'>Ce batiment est considéré en état de siege, il ne sera pas possible de recruter des grouillots tant que ses PV ne seront pas suffisamment remontés</font></center><br />";
							echo "<center>PV actuel : $pv_instance / $pv_max_instance</center>";
							
						} else {
					
							// Cavalerie Lourde
							if (isset($_POST["2"])) {
								
								// Besoin de 3PA pour recruter
								if ($pa_perso >= 3) {
									
									// Calculer PG déjà utilisés par le joueur
									$pg_utilise = 0;
									
									$sql = "SELECT type_perso FROM perso WHERE idJoueur_perso='$id_joueur'";
									$res = $mysqli->query($sql);
									while ($tab = $res->fetch_assoc()) {
										
										$type_perso_joueur = $tab["type_perso"];
										
										$sql_u = "SELECT cout_pg FROM type_unite WHERE id_unite='$type_perso_joueur'";
										$res_u = $mysqli->query($sql_u);
										$t_u = $res_u->fetch_assoc();
										
										$pg_utilise += $t_u["cout_pg"];
										
									}
									
									// Calcul PG restant au joueur
									$pg_restant = $pg - $pg_utilise;
									
									// Récupérer coût PG unite 
									$sql = "SELECT cout_pg, nom_unite, perception_unite, protection_unite, recup_unite, pv_unite, pa_unite, pm_unite, image_unite FROM type_unite WHERE id_unite='2'";
									$res = $mysqli->query($sql);
									$tab = $res->fetch_assoc();
									
									$cout_pg_recrutement = $tab["cout_pg"];
									
									// Verifier si possibilité de recruter
									if ($pg_restant >= $cout_pg_recrutement) {
										
										// MAJ des PA du chef 
										$pa_perso = $pa_perso - 3;
										$sql = "UPDATE perso SET pa_perso=pa_perso-3 WHERE id_perso='$id'";
										$mysqli->query($sql);
										
										// Recupération caracs de base du perso 
										$nom_unite 			= $tab["nom_unite"];
										$perception_unite 	= $tab["perception_unite"];
										$protection_unite 	= $tab["protection_unite"];
										$recup_unite 		= $tab["recup_unite"];
										$pv_unite 			= $tab["pv_unite"];
										$pa_unite 			= $tab["pa_unite"];
										$pm_unite 			= $tab["pm_unite"];
										$image_unite		= $tab["image_unite"];
										
										$image_perso_cree 	= $image_unite."_".$camp.".gif";
										$nom_perso_cree		= $nom_perso."_junior";
										
										// Calcul DLA
										$date = time();
										$dla = $date + DUREE_TOUR;
										
										$bataillon = addslashes($bataillon);
										
										// Créer nouveau Perso et la placer dans ce même batiment
										$sql = "INSERT INTO perso (IDJoueur_perso, type_perso, nom_perso, x_perso, y_perso, pvMax_perso, pv_perso, pm_perso, pmMax_perso, perception_perso, recup_perso, protec_perso, pa_perso, image_perso, dateCreation_perso, DLA_perso, clan, message_perso, chef, bataillon) 
												VALUES ('$id_joueur', '2', '$nom_perso_cree', '$x_instance', '$y_instance', '$pv_unite', '$pv_unite', '$pm_unite', '$pm_unite', '$perception_unite', '$recup_unite', '$protection_unite', '$pa_unite', '$image_perso_cree', NOW(), FROM_UNIXTIME($dla), $clan, '', 0, '$bataillon')";
										$mysqli->query($sql);
										
										// Récupération de l'id du perso créé 
										$sql = "SELECT MAX(id_perso) as id_perso_cree FROM perso WHERE IDJoueur_perso='$id_joueur'";
										$res = $mysqli->query($sql);
										$tab = $res->fetch_assoc();
										
										$id_perso_cree = $tab["id_perso_cree"];
										
										// Insertion perso dans batiment 
										$sql = "INSERT INTO perso_in_batiment VALUES ('$id_perso_cree','$id_instance_bat')";
										$mysqli->query($sql);
										
										//------- Messagerie
										// dossier courant
										$sql_i = "INSERT INTO perso_as_dossiers VALUES ('$id_perso_cree','1')";
										$mysqli->query($sql_i);
										
										// dossier archives
										$sql_i = "INSERT INTO perso_as_dossiers VALUES ('$id_perso_cree','2')";
										$mysqli->query($sql_i);
										
										// grade Grouillot = 2nd classe
										$sql_i = "INSERT INTO perso_as_grade VALUES ('$id_perso_cree','1')";
										$mysqli->query($sql_i);
										
										//------- Ajout des armes à la cavalerie
										// Arme Cac : sabre
										$sql = "INSERT INTO perso_as_arme (id_perso, id_arme, est_portee) VALUES ('$id_perso_cree','1','1')";
										$mysqli->query($sql);
										
										// Arme distance : pistolet 
										$sql = "INSERT INTO perso_as_arme (id_perso, id_arme, est_portee) VALUES ('$id_perso_cree','4','1')";
										$mysqli->query($sql);
										
										// Insertion competence sieste
										$sql = "INSERT INTO perso_as_competence (id_perso, id_competence, nb_points) VALUES ('$id_perso_cree','4','1')";
										$mysqli->query($sql);
										
										// Evenement grouillot rejoint bataillon
										$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) 
												VALUES ($id_perso_cree,'<font color=$couleur_clan_perso>$nom_perso_cree</font>',' a rejoint le bataillon $bataillon',NULL,'','',NOW(),'0')";
										$mysqli->query($sql);
										
										echo "<center><font color=blue>Vous venez de recruter une $nom_unite</font></center>";
										
									} else {
										echo "<center><font color=red>Vous n'avez pas assez de point de grouillot pour pouvoir recruter cette unité. Il vous reste $pg_restant points de grouillot</font></center>";
									}
								} else {
									echo "<center><font color=red>Vous n'avez pas assez de point d'action pour recruter un grouillot, il vous reste $pa_perso points d'action</font></center>";
								}
							}
							
							// Infanterie
							if (isset($_POST["3"])) {
								
								// Besoin de 3PA pour recruter
								if ($pa_perso >= 3) {
									
									// Calculer PG déjà utilisés par le joueur
									$pg_utilise = 0;
									
									$sql = "SELECT type_perso FROM perso WHERE idJoueur_perso='$id_joueur'";
									$res = $mysqli->query($sql);
									while ($tab = $res->fetch_assoc()) {
										
										$type_perso_joueur = $tab["type_perso"];
										
										$sql_u = "SELECT cout_pg FROM type_unite WHERE id_unite='$type_perso_joueur'";
										$res_u = $mysqli->query($sql_u);
										$t_u = $res_u->fetch_assoc();
										
										$pg_utilise += $t_u["cout_pg"];
										
									}
									
									// Calcul PG restant au joueur
									$pg_restant = $pg - $pg_utilise;
									
									// Récupérer coût PG unite 
									$sql = "SELECT cout_pg, nom_unite, perception_unite, protection_unite, recup_unite, pv_unite, pa_unite, pm_unite, image_unite FROM type_unite WHERE id_unite='3'";
									$res = $mysqli->query($sql);
									$tab = $res->fetch_assoc();
									
									$cout_pg_recrutement = $tab["cout_pg"];
									
									// Verifier si possibilité de recruter
									if ($pg_restant >= $cout_pg_recrutement) {
										
										// MAJ des PA du chef 
										$pa_perso = $pa_perso - 3;
										$sql = "UPDATE perso SET pa_perso=pa_perso-3 WHERE id_perso='$id'";
										$mysqli->query($sql);
										
										// Recupération caracs de base du perso 
										$nom_unite 			= $tab["nom_unite"];
										$perception_unite 	= $tab["perception_unite"];
										$protection_unite 	= $tab["protection_unite"];
										$recup_unite 		= $tab["recup_unite"];
										$pv_unite 			= $tab["pv_unite"];
										$pa_unite 			= $tab["pa_unite"];
										$pm_unite 			= $tab["pm_unite"];
										$image_unite		= $tab["image_unite"];
										
										$image_perso_cree 	= $image_unite."_".$camp.".gif";
										$nom_perso_cree		= $nom_perso."_junior";
										
										// Calcul DLA
										$date = time();
										$dla = $date + DUREE_TOUR;
										
										$bataillon = addslashes($bataillon);
										
										// Créer nouveau Perso et la placer dans ce même batiment
										$sql = "INSERT INTO perso (IDJoueur_perso, type_perso, nom_perso, x_perso, y_perso, pvMax_perso, pv_perso, pm_perso, pmMax_perso, perception_perso, recup_perso, protec_perso, pa_perso, image_perso, dateCreation_perso, DLA_perso, clan, message_perso, chef, bataillon) 
												VALUES ('$id_joueur', '3', '$nom_perso_cree', '$x_instance', '$y_instance', '$pv_unite', '$pv_unite', '$pm_unite', '$pm_unite', '$perception_unite', '$recup_unite', '$protection_unite', '$pa_unite', '$image_perso_cree', NOW(), FROM_UNIXTIME($dla), $clan, '', 0, '$bataillon')";
										$mysqli->query($sql);
										
										// Récupération de l'id du perso créé 
										$sql = "SELECT MAX(id_perso) as id_perso_cree FROM perso WHERE IDJoueur_perso='$id_joueur'";
										$res = $mysqli->query($sql);
										$tab = $res->fetch_assoc();
										
										$id_perso_cree = $tab["id_perso_cree"];
										
										// Insertion perso dans batiment 
										$sql = "INSERT INTO perso_in_batiment VALUES ('$id_perso_cree','$id_instance_bat')";
										$mysqli->query($sql);
										
										//------- Messagerie
										// dossier courant
										$sql_i = "INSERT INTO perso_as_dossiers VALUES ('$id_perso_cree','1')";
										$mysqli->query($sql_i);
										
										// dossier archives
										$sql_i = "INSERT INTO perso_as_dossiers VALUES ('$id_perso_cree','2')";
										$mysqli->query($sql_i);
										
										// grade Grouillot = 2nd classe
										$sql_i = "INSERT INTO perso_as_grade VALUES ('$id_perso_cree','1')";
										$mysqli->query($sql_i);
										
										//------- Ajout des armes à l'infanterie
										// Arme Cac : baillonette
										$sql = "INSERT INTO perso_as_arme (id_perso, id_arme, est_portee) VALUES ('$id_perso_cree','6','1')";
										$mysqli->query($sql);
										
										// Arme distance : fusil 
										$sql = "INSERT INTO perso_as_arme (id_perso, id_arme, est_portee) VALUES ('$id_perso_cree','7','1')";
										$mysqli->query($sql);
										
										//------- Competences
										// Insertion competence construction barricades
										$sql_c = "INSERT INTO perso_as_competence (id_perso, id_competence, nb_points) VALUES ('$id_perso_cree','22','1')";
										$mysqli->query($sql_c);
										
										// Insertion competence marche forcée
										$sql_c = "INSERT INTO perso_as_competence (id_perso, id_competence, nb_points) VALUES ('$id_perso_cree','6','1')";
										$mysqli->query($sql_c);
										
										// Insertion competence sieste
										$sql = "INSERT INTO perso_as_competence (id_perso, id_competence, nb_points) VALUES ('$id_perso_cree','4','1')";
										$mysqli->query($sql);
										
										// Evenement grouillot rejoint bataillon
										$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) 
												VALUES ($id_perso_cree,'<font color=$couleur_clan_perso>$nom_perso_cree</font>',' a rejoint le bataillon $bataillon',NULL,'','',NOW(),'0')";
										$mysqli->query($sql);
										
										echo "<center><font color=blue>Vous venez de recruter une $nom_unite</font></center>";
										
									} else {
										echo "<center><font color=red>Vous n'avez pas assez de point de grouillot pour pouvoir recruter cette unité. Il vous reste $pg_restant points de grouillot</font></center>";
									}
								} else {
									echo "<center><font color=red>Vous n'avez pas assez de point d'action pour recruter un grouillot, il vous reste $pa_perso points d'action</font></center>";
								}
								
							}
							
							// Soigneur
							if (isset($_POST["4"])) {
								
								// Besoin de 3PA pour recruter
								if ($pa_perso >= 3) {
									
									// Calculer PG déjà utilisés par le joueur
									$pg_utilise = 0;
									
									$sql = "SELECT type_perso FROM perso WHERE idJoueur_perso='$id_joueur'";
									$res = $mysqli->query($sql);
									while ($tab = $res->fetch_assoc()) {
										
										$type_perso_joueur = $tab["type_perso"];
										
										$sql_u = "SELECT cout_pg FROM type_unite WHERE id_unite='$type_perso_joueur'";
										$res_u = $mysqli->query($sql_u);
										$t_u = $res_u->fetch_assoc();
										
										$pg_utilise += $t_u["cout_pg"];
										
									}
									
									// Calcul PG restant au joueur
									$pg_restant = $pg - $pg_utilise;
									
									// Récupérer coût PG unite 
									$sql = "SELECT cout_pg, nom_unite, perception_unite, protection_unite, recup_unite, pv_unite, pa_unite, pm_unite, image_unite FROM type_unite WHERE id_unite='4'";
									$res = $mysqli->query($sql);
									$tab = $res->fetch_assoc();
									
									$cout_pg_recrutement = $tab["cout_pg"];
									
									// Verifier si possibilité de recruter
									if ($pg_restant >= $cout_pg_recrutement) {
										
										// MAJ des PA du chef 
										$pa_perso = $pa_perso - 3;
										$sql = "UPDATE perso SET pa_perso=pa_perso-3 WHERE id_perso='$id'";
										$mysqli->query($sql);
										
										// Recupération caracs de base du perso 
										$nom_unite 			= $tab["nom_unite"];
										$perception_unite 	= $tab["perception_unite"];
										$protection_unite 	= $tab["protection_unite"];
										$recup_unite 		= $tab["recup_unite"];
										$pv_unite 			= $tab["pv_unite"];
										$pa_unite 			= $tab["pa_unite"];
										$pm_unite 			= $tab["pm_unite"];
										$image_unite		= $tab["image_unite"];
										
										$image_perso_cree 	= $image_unite."_".$camp.".gif";
										$nom_perso_cree		= $nom_perso."_junior";
										
										// Calcul DLA
										$date = time();
										$dla = $date + DUREE_TOUR;
										
										$bataillon = addslashes($bataillon);
										
										// Créer nouveau Perso et la placer dans ce même batiment
										$sql = "INSERT INTO perso (IDJoueur_perso, type_perso, nom_perso, x_perso, y_perso, pvMax_perso, pv_perso, pm_perso, pmMax_perso, perception_perso, recup_perso, protec_perso, pa_perso, image_perso, dateCreation_perso, DLA_perso, clan, message_perso, chef, bataillon) 
												VALUES ('$id_joueur', '4', '$nom_perso_cree', '$x_instance', '$y_instance', '$pv_unite', '$pv_unite', '$pm_unite', '$pm_unite', '$perception_unite', '$recup_unite', '$protection_unite', '$pa_unite', '$image_perso_cree', NOW(), FROM_UNIXTIME($dla), $clan, '', 0, '$bataillon')";
										$mysqli->query($sql);
										
										// Récupération de l'id du perso créé 
										$sql = "SELECT MAX(id_perso) as id_perso_cree FROM perso WHERE IDJoueur_perso='$id_joueur'";
										$res = $mysqli->query($sql);
										$tab = $res->fetch_assoc();
										
										$id_perso_cree = $tab["id_perso_cree"];
										
										// Insertion perso dans batiment 
										$sql = "INSERT INTO perso_in_batiment VALUES ('$id_perso_cree','$id_instance_bat')";
										$mysqli->query($sql);
										
										//------- Messagerie
										// dossier courant
										$sql_i = "INSERT INTO perso_as_dossiers VALUES ('$id_perso_cree','1')";
										$mysqli->query($sql_i);
										
										// dossier archives
										$sql_i = "INSERT INTO perso_as_dossiers VALUES ('$id_perso_cree','2')";
										$mysqli->query($sql_i);
										
										// grade Grouillot = 2nd classe
										$sql_i = "INSERT INTO perso_as_grade VALUES ('$id_perso_cree','1')";
										$mysqli->query($sql_i);
										
										//------- Ajout des armes au soigneur
										// Arme Cac : seringue
										$sql = "INSERT INTO perso_as_arme (id_perso, id_arme, est_portee) VALUES ('$id_perso_cree','10','1')";
										$mysqli->query($sql);
										
										// Arme distance : bandages 
										$sql = "INSERT INTO perso_as_arme (id_perso, id_arme, est_portee) VALUES ('$id_perso_cree','11','1')";
										$mysqli->query($sql);
										
										//------- Competences										
										// Insertion competence marche forcée
										$sql_c = "INSERT INTO perso_as_competence (id_perso, id_competence, nb_points) VALUES ('$id_perso_cree','6','1')";
										$mysqli->query($sql_c);
										
										// Insertion competence sieste
										$sql = "INSERT INTO perso_as_competence (id_perso, id_competence, nb_points) VALUES ('$id_perso_cree','4','1')";
										$mysqli->query($sql);
										
										// Evenement grouillot rejoint bataillon
										$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) 
												VALUES ($id_perso_cree,'<font color=$couleur_clan_perso>$nom_perso_cree</font>',' a rejoint le bataillon $bataillon',NULL,'','',NOW(),'0')";
										$mysqli->query($sql);
										
										echo "<center><font color=blue>Vous venez de recruter un $nom_unite</font></center>";
										
									} else {
										echo "<center><font color=red>Vous n'avez pas assez de point de grouillot pour pouvoir recruter cette unité. Il vous reste $pg_restant points de grouillot</font></center>";
									}
								} else {
									echo "<center><font color=red>Vous n'avez pas assez de point d'action pour recruter un grouillot, il vous reste $pa_perso points d'action</font></center>";
								}
							}
							
							// Artillerie
							if (isset($_POST["5"])) {
								
								// Besoin de 3PA pour recruter
								if ($pa_perso >= 3) {
									
									// Calculer PG déjà utilisés par le joueur
									$pg_utilise = 0;
									
									$sql = "SELECT type_perso FROM perso WHERE idJoueur_perso='$id_joueur'";
									$res = $mysqli->query($sql);
									while ($tab = $res->fetch_assoc()) {
										
										$type_perso_joueur = $tab["type_perso"];
										
										$sql_u = "SELECT cout_pg FROM type_unite WHERE id_unite='$type_perso_joueur'";
										$res_u = $mysqli->query($sql_u);
										$t_u = $res_u->fetch_assoc();
										
										$pg_utilise += $t_u["cout_pg"];
										
									}
									
									// Calcul PG restant au joueur
									$pg_restant = $pg - $pg_utilise;
									
									// Récupérer coût PG unite 
									$sql = "SELECT cout_pg, nom_unite, perception_unite, protection_unite, recup_unite, pv_unite, pa_unite, pm_unite, image_unite FROM type_unite WHERE id_unite='5'";
									$res = $mysqli->query($sql);
									$tab = $res->fetch_assoc();
									
									$cout_pg_recrutement = $tab["cout_pg"];
									
									// Verifier si possibilité de recruter
									if ($pg_restant >= $cout_pg_recrutement) {
										
										// MAJ des PA du chef 
										$pa_perso = $pa_perso - 3;
										$sql = "UPDATE perso SET pa_perso=pa_perso-3 WHERE id_perso='$id'";
										$mysqli->query($sql);
										
										// Recupération caracs de base du perso 
										$nom_unite 			= $tab["nom_unite"];
										$perception_unite 	= $tab["perception_unite"];
										$protection_unite 	= $tab["protection_unite"];
										$recup_unite 		= $tab["recup_unite"];
										$pv_unite 			= $tab["pv_unite"];
										$pa_unite 			= $tab["pa_unite"];
										$pm_unite 			= $tab["pm_unite"];
										$image_unite		= $tab["image_unite"];
										
										$image_perso_cree 	= $image_unite."_".$camp.".gif";
										$nom_perso_cree		= $nom_perso."_junior";
										
										// Calcul DLA
										$date = time();
										$dla = $date + DUREE_TOUR;
										
										$bataillon = addslashes($bataillon);
										
										// Créer nouveau Perso et la placer dans ce même batiment
										$sql = "INSERT INTO perso (IDJoueur_perso, type_perso, nom_perso, x_perso, y_perso, pvMax_perso, pv_perso, pm_perso, pmMax_perso, perception_perso, recup_perso, protec_perso, pa_perso, image_perso, dateCreation_perso, DLA_perso, clan, message_perso, chef, bataillon) 
												VALUES ('$id_joueur', '5', '$nom_perso_cree', '$x_instance', '$y_instance', '$pv_unite', '$pv_unite', '$pm_unite', '$pm_unite', '$perception_unite', '$recup_unite', '$protection_unite', '$pa_unite', '$image_perso_cree', NOW(), FROM_UNIXTIME($dla), $clan, '', 0, '$bataillon')";
										$mysqli->query($sql);
										
										// Récupération de l'id du perso créé 
										$sql = "SELECT MAX(id_perso) as id_perso_cree FROM perso WHERE IDJoueur_perso='$id_joueur'";
										$res = $mysqli->query($sql);
										$tab = $res->fetch_assoc();
										
										$id_perso_cree = $tab["id_perso_cree"];
										
										// Insertion perso dans batiment 
										$sql = "INSERT INTO perso_in_batiment VALUES ('$id_perso_cree','$id_instance_bat')";
										$mysqli->query($sql);
										
										//------- Messagerie
										// dossier courant
										$sql_i = "INSERT INTO perso_as_dossiers VALUES ('$id_perso_cree','1')";
										$mysqli->query($sql_i);
										
										// dossier archives
										$sql_i = "INSERT INTO perso_as_dossiers VALUES ('$id_perso_cree','2')";
										$mysqli->query($sql_i);
										
										// grade Grouillot = 2nd classe
										$sql_i = "INSERT INTO perso_as_grade VALUES ('$id_perso_cree','1')";
										$mysqli->query($sql_i);
										
										//------- Ajout des armes à l'artillerie
										// Arme : Canon
										$sql = "INSERT INTO perso_as_arme (id_perso, id_arme, est_portee) VALUES ('$id_perso_cree','13','1')";
										$mysqli->query($sql);
										
										// Insertion competence sieste
										$sql = "INSERT INTO perso_as_competence (id_perso, id_competence, nb_points) VALUES ('$id_perso_cree','4','1')";
										$mysqli->query($sql);
										
										// Evenement grouillot rejoint bataillon
										$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) 
												VALUES ($id_perso_cree,'<font color=$couleur_clan_perso>$nom_perso_cree</font>',' a rejoint le bataillon $bataillon',NULL,'','',NOW(),'0')";
										$mysqli->query($sql);
										
										echo "<center><font color=blue>Vous venez de recruter une $nom_unite</font></center>";
										
									} else {
										echo "<center><font color=red>Vous n'avez pas assez de point de grouillot pour pouvoir recruter cette unité. Il vous reste $pg_restant points de grouillot</font></center>";
									}
								} else {
									echo "<center><font color=red>Vous n'avez pas assez de point d'action pour recruter un grouillot, il vous reste $pa_perso points d'action</font></center>";
								}
							}
							
							// Toutou
							if (isset($_POST["6"])) {
								
								// Besoin de 3PA pour recruter
								if ($pa_perso >= 3) {
									
									// Calculer PG déjà utilisés par le joueur
									$pg_utilise = 0;
									
									$sql = "SELECT type_perso FROM perso WHERE idJoueur_perso='$id_joueur'";
									$res = $mysqli->query($sql);
									while ($tab = $res->fetch_assoc()) {
										
										$type_perso_joueur = $tab["type_perso"];
										
										$sql_u = "SELECT cout_pg FROM type_unite WHERE id_unite='$type_perso_joueur'";
										$res_u = $mysqli->query($sql_u);
										$t_u = $res_u->fetch_assoc();
										
										$pg_utilise += $t_u["cout_pg"];
										
									}
									
									// Calcul PG restant au joueur
									$pg_restant = $pg - $pg_utilise;
									
									// Récupérer coût PG unite 
									$sql = "SELECT cout_pg, nom_unite, perception_unite, protection_unite, recup_unite, pv_unite, pa_unite, pm_unite, image_unite FROM type_unite WHERE id_unite='6'";
									$res = $mysqli->query($sql);
									$tab = $res->fetch_assoc();
									
									$cout_pg_recrutement = $tab["cout_pg"];
									
									// Verifier si possibilité de recruter
									if ($pg_restant >= $cout_pg_recrutement) {
										
										// MAJ des PA du chef 
										$pa_perso = $pa_perso - 3;
										$sql = "UPDATE perso SET pa_perso=pa_perso-3 WHERE id_perso='$id'";
										$mysqli->query($sql);
										
										// Recupération caracs de base du perso 
										$nom_unite 			= $tab["nom_unite"];
										$perception_unite 	= $tab["perception_unite"];
										$protection_unite 	= $tab["protection_unite"];
										$recup_unite 		= $tab["recup_unite"];
										$pv_unite 			= $tab["pv_unite"];
										$pa_unite 			= $tab["pa_unite"];
										$pm_unite 			= $tab["pm_unite"];
										$image_unite		= $tab["image_unite"];
										
										$image_perso_cree 	= $image_unite."_".$camp.".gif";
										$nom_perso_cree		= $nom_perso."_junior";
										
										// Calcul DLA
										$date = time();
										$dla = $date + DUREE_TOUR;
										
										$bataillon = addslashes($bataillon);
										
										// Créer nouveau Perso et la placer dans ce même batiment
										$sql = "INSERT INTO perso (IDJoueur_perso, type_perso, nom_perso, x_perso, y_perso, pvMax_perso, pv_perso, pm_perso, pmMax_perso, perception_perso, recup_perso, protec_perso, pa_perso, image_perso, dateCreation_perso, DLA_perso, clan, message_perso, chef, bataillon) 
												VALUES ('$id_joueur', '6', '$nom_perso_cree', '$x_instance', '$y_instance', '$pv_unite', '$pv_unite', '$pm_unite', '$pm_unite', '$perception_unite', '$recup_unite', '$protection_unite', '$pa_unite', '$image_perso_cree', NOW(), FROM_UNIXTIME($dla), $clan, '', 0, '$bataillon')";
										$mysqli->query($sql);
										
										// Récupération de l'id du perso créé 
										$sql = "SELECT MAX(id_perso) as id_perso_cree FROM perso WHERE IDJoueur_perso='$id_joueur'";
										$res = $mysqli->query($sql);
										$tab = $res->fetch_assoc();
										
										$id_perso_cree = $tab["id_perso_cree"];
										
										// Insertion perso dans batiment 
										$sql = "INSERT INTO perso_in_batiment VALUES ('$id_perso_cree','$id_instance_bat')";
										$mysqli->query($sql);
										
										//------- Messagerie
										// dossier courant
										$sql_i = "INSERT INTO perso_as_dossiers VALUES ('$id_perso_cree','1')";
										$mysqli->query($sql_i);
										
										// dossier archives
										$sql_i = "INSERT INTO perso_as_dossiers VALUES ('$id_perso_cree','2')";
										$mysqli->query($sql_i);
										
										// grade Grouillot = 2nd classe
										$sql_i = "INSERT INTO perso_as_grade VALUES ('$id_perso_cree','1')";
										$mysqli->query($sql_i);
										
										//------- Ajout des armes au toutou
										// canines
										$sql = "INSERT INTO perso_as_arme (id_perso, id_arme, est_portee) VALUES ('$id_perso_cree','9','1')";
										$mysqli->query($sql);
										// griffes
										$sql = "INSERT INTO perso_as_arme (id_perso, id_arme, est_portee) VALUES ('$id_perso_cree','12','1')";
										$mysqli->query($sql);
										
										// Insertion competence sieste
										$sql = "INSERT INTO perso_as_competence (id_perso, id_competence, nb_points) VALUES ('$id_perso_cree','4','1')";
										$mysqli->query($sql);
										
										// Evenement grouillot rejoint bataillon
										$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) 
												VALUES ($id_perso_cree,'<font color=$couleur_clan_perso>$nom_perso_cree</font>',' a rejoint le bataillon $bataillon',NULL,'','',NOW(),'0')";
										$mysqli->query($sql);
										
										echo "<center><font color=blue>Vous venez de recruter un $nom_unite</font></center>";
										
									} else {
										echo "<center><font color=red>Vous n'avez pas assez de point de grouillot pour pouvoir recruter cette unité. Il vous reste $pg_restant points de grouillot</font></center>";
									}
								} else {
									echo "<center><font color=red>Vous n'avez pas assez de point d'action pour recruter un grouillot, il vous reste $pa_perso points d'action</font></center>";
								}
							}
							
							
							// Calculer PG déjà utilisés par le joueur
							$pg_utilise = 0;
							
							$sql = "SELECT type_perso FROM perso WHERE idJoueur_perso='$id_joueur'";
							$res = $mysqli->query($sql);
							while ($tab = $res->fetch_assoc()) {
								
								$type_perso_joueur = $tab["type_perso"];
								
								$sql_u = "SELECT cout_pg FROM type_unite WHERE id_unite='$type_perso_joueur'";
								$res_u = $mysqli->query($sql_u);
								$t_u = $res_u->fetch_assoc();
								
								$pg_utilise += $t_u["cout_pg"];
								
							}
							
							// Calcul PG restant au joueur
							$pg_restant = $pg - $pg_utilise;
							
							?>
							
							<center>Vous avez utilisé <b><?php echo $pg_utilise; ?> PG</b> sur un total de <b><?php echo $pg; ?>PG (PG restant : <?php echo $pg_restant; ?>)</b></center>
							<center>Le recrutement d'un grouillot coute <b>3PA</b>, il vous reste <b><?php echo $pa_perso; ?> PA</b></center>
							
							<?php

							// Récupération des grouillots recrutable
							$sql = "SELECT * FROM type_unite WHERE id_unite != '1'";
							$res = $mysqli->query($sql);
							
							echo "<form method=\"post\" action=\"recrutement.php\">";
							
							echo "<table align='center' border='1' width='70%'>";
							echo "	<tr>";
							echo "		<th></th><th>Unité</th><th>PA</th><th>PV</th><th>PM</th><th>Recupération</th><th>Perception</th><th>Protection</th><th>Description</th><th>Cout PG</th><th>Action</th>";
							echo "	</tr>";
							
							while ($tab = $res->fetch_assoc()) {
								
								$id_unite			= $tab["id_unite"];
								$nom_unite 			= $tab["nom_unite"];
								$description_unite 	= $tab["description_unite"];
								$perception_unite 	= $tab["perception_unite"];
								$protection_unite 	= $tab["protection_unite"];
								$recup_unite 		= $tab["recup_unite"];
								$pv_unite 			= $tab["pv_unite"];
								$pa_unite 			= $tab["pa_unite"];
								$pm_unite 			= $tab["pm_unite"];
								$image_unite 		= $tab["image_unite"];
								$cout_pg_unite 		= $tab["cout_pg"];
								
								$image_affiche = $image_unite."_".$camp.".gif";
								
								if ($id_grade < 6 && $id_unite == 6) {
									// Chien non recrutable si grade inférieur à Sergent chef
									echo "<center>Le chien n'est recrutable qu'à partir du grade de Sergent Chef</center>";
								} else {
								
									echo "	<tr>";
									echo "		<td align='center'><img src='../images_perso/".$image_affiche."' alt='".$nom_unite."'/></td>";
									echo "		<td align='center'>$nom_unite</td>";
									echo "		<td align='center'>$pa_unite</td>";
									echo "		<td align='center'>$pv_unite</td>";
									echo "		<td align='center'>$pm_unite</td>";
									echo "		<td align='center'>$recup_unite</td>";
									echo "		<td align='center'>$perception_unite</td>";
									echo "		<td align='center'>$protection_unite</td>";
									echo "		<td align='center'>$description_unite</td>";
									echo "		<td align='center'>$cout_pg_unite PG</td>";
									
									// TODO - Condition si Possibilité de recruter
									echo "		<td align='center'><input type=\"submit\" name=\"".$id_unite."\" value=\">> Recruter !\"></td>";
									echo "	</tr>";
								}
								
							}
							
							echo "</table>";
							
							echo "</form>";
						}
					}
				}
			}
			else {
				echo "<font color=red>Seul le chef de bataillon peut accéder à cette page.</font>";
			}
		}
	}
	else{
		echo "<font color=red>Vous ne pouvez pas accéder à cette page, veuillez vous loguer.</font>";
	}
	?>
	</body>
</html>
<?php
}
else {
	// logout
	$_SESSION = array(); // On écrase le tableau de session
	session_destroy(); // On détruit la session
	
	header("Location: index2.php");
}
?>