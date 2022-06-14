<?php
session_start();
require_once("../fonctions.php");

$mysqli = db_connexion();

include ('../nb_online.php');

// recupération config jeu
$dispo = config_dispo_jeu($mysqli);
$admin = admin_perso($mysqli, $_SESSION["id_perso"]);

if($dispo == '1' || $admin){
	
	if(isset($_SESSION["id_perso"])){
		
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
				$nb = $res->num_rows;
				
				$id_instance_bat = null;
				if ($nb)
					$id_instance_bat = $tab["id_instanceBat"];
			
	?>
<html>
	<head>
		<title>Nord VS Sud</title>
		
		<!-- Required meta tags -->
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		
		<!-- Bootstrap CSS -->
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
	</head>
	
	<body>
		<div class="container-fluid">
			<nav class="navbar navbar-expand-lg navbar-light">
				<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
					<span class="navbar-toggler-icon"></span>
				</button>
				<div class="collapse navbar-collapse" id="navbarSupportedContent">
					<ul class="navbar-nav mr-auto nav-pills">
						<li class="nav-item">
							<a class="nav-link" href="profil.php">Profil</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" href="ameliorer.php">Améliorer son perso</a>
						</li>
						<?php
						if($chef) {
							echo "<li class='nav-item'><a class='nav-link active' href=\"#\">Recruter des grouillots</a></li>";
							echo "<li class='nav-item'><a class='nav-link' href=\"gestion_grouillot.php\">Gérer ses grouillots</a></li>";
						}
						?>
						<li class="nav-item">
							<a class="nav-link" href="equipement.php">Equiper son perso</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" href="compte.php">Gérer son Compte</a>
						</li>
					</ul>
				</div>
			</nav>
			
			<hr>
	
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
							
							// Verification si 10 persos ennemis à moins de 15 cases
							$sql = "SELECT count(id_perso) as nb_ennemi FROM perso, carte 
									WHERE perso.id_perso = carte.idPerso_carte 
									AND x_carte <= $x_instance + 15
									AND x_carte >= $x_instance - 15
									AND y_carte <= $y_instance + 15
									AND y_carte >= $y_instance - 15
									AND perso.clan != '$clan'";
							$res = $mysqli->query($sql);
							$t_e = $res->fetch_assoc();
							
							$nb_ennemis_siege = $t_e['nb_ennemi'];
							
							if ($pourc_pv_instance < 90 || $nb_ennemis_siege >= 10) {
								
								// Il reste moins de 90% des pv du batiment => siege
								echo "<center><font color='red'>Ce batiment est considéré en état de siege, il ne sera pas possible de recruter des grouillots tant que ses PV ne seront pas suffisamment remontés ou que la zone ne sera pas nettoyée des ennemis</font></center><br />";
								echo "<center>PV actuel : $pv_instance / $pv_max_instance</center>";
								
							} else {
						
								// Cavalerie lourde et légère
								if (isset($_POST["2"]) || isset($_POST["7"])) {

									$id_unite = 2;
									if (isset($_POST["7"]))
										$id_unite = 7;
									
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
										$sql = "SELECT cout_pg, nom_unite, perception_unite, protection_unite, recup_unite, pv_unite, pa_unite, pm_unite, image_unite FROM type_unite WHERE id_unite='.$id_unite.'";
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
											
											$nom_perso_tmp = $nom_perso_cree;
											$nom_pas_trouve = true;
											$i = 2;
											
											while ($nom_pas_trouve) {
												
												$nom_perso_cherche = addslashes($nom_perso_cree);
												
												$sql = "SELECT id_perso FROM perso WHERE nom_perso='$nom_perso_cherche'";
												$res = $mysqli->query($sql);
												$nb = $res->num_rows;
												
												if ($nb == 0) {
													$nom_pas_trouve = false;
												}
												else {
													$nom_perso_cree = $nom_perso_tmp.$i;
													
													$i++;
												}
											}
											
											// Calcul DLA
											$date = time();
											$dla = $date + DUREE_TOUR;
											
											$bataillon = addslashes($bataillon);
											
											// Créer nouveau Perso et la placer dans ce même batiment
											$sql = "INSERT INTO perso (IDJoueur_perso, type_perso, nom_perso, x_perso, y_perso, pvMax_perso, pv_perso, pm_perso, pmMax_perso, perception_perso, recup_perso, protec_perso, pa_perso, paMax_perso, image_perso, dateCreation_perso, DLA_perso, clan, message_perso, chef, bataillon) 
													VALUES ('$id_joueur', $id_unite, '$nom_perso_cree', '$x_instance', '$y_instance', '$pv_unite', '$pv_unite', '0', '$pm_unite', '$perception_unite', '$recup_unite', '$protection_unite', '0', $pa_unite, '$image_perso_cree', NOW(), FROM_UNIXTIME($dla), $clan, '', 0, '$bataillon')";
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
											
											$nom_perso_tmp = $nom_perso_cree;
											$nom_pas_trouve = true;
											$i = 2;
											
											while ($nom_pas_trouve) {
												
												$nom_perso_cherche = addslashes($nom_perso_cree);
												
												$sql = "SELECT id_perso FROM perso WHERE nom_perso='$nom_perso_cherche'";
												$res = $mysqli->query($sql);
												$nb = $res->num_rows;
												
												if ($nb == 0) {
													$nom_pas_trouve = false;
												}
												else {
													$nom_perso_cree = $nom_perso_tmp.$i;
													
													$i++;
												}
											}
											
											// Calcul DLA
											$date = time();
											$dla = $date + DUREE_TOUR;
											
											$bataillon = addslashes($bataillon);
											
											// Créer nouveau Perso et la placer dans ce même batiment
											$sql = "INSERT INTO perso (IDJoueur_perso, type_perso, nom_perso, x_perso, y_perso, pvMax_perso, pv_perso, pm_perso, pmMax_perso, perception_perso, recup_perso, protec_perso, pa_perso, image_perso, dateCreation_perso, DLA_perso, clan, message_perso, chef, bataillon) 
													VALUES ('$id_joueur', '3', '$nom_perso_cree', '$x_instance', '$y_instance', '$pv_unite', '$pv_unite', '0', '$pm_unite', '$perception_unite', '$recup_unite', '$protection_unite', '0', '$image_perso_cree', NOW(), FROM_UNIXTIME($dla), $clan, '', 0, '$bataillon')";
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
											
											$nom_perso_tmp = $nom_perso_cree;
											$nom_pas_trouve = true;
											$i = 2;
											
											while ($nom_pas_trouve) {
												
												$nom_perso_cherche = addslashes($nom_perso_cree);
												
												$sql = "SELECT id_perso FROM perso WHERE nom_perso='$nom_perso_cherche'";
												$res = $mysqli->query($sql);
												$nb = $res->num_rows;
												
												if ($nb == 0) {
													$nom_pas_trouve = false;
												}
												else {
													$nom_perso_cree = $nom_perso_tmp.$i;
													
													$i++;
												}
											}
											
											// Calcul DLA
											$date = time();
											$dla = $date + DUREE_TOUR;
											
											$bataillon = addslashes($bataillon);
											
											// Créer nouveau Perso et la placer dans ce même batiment
											$sql = "INSERT INTO perso (IDJoueur_perso, type_perso, nom_perso, x_perso, y_perso, pvMax_perso, pv_perso, pm_perso, pmMax_perso, perception_perso, recup_perso, protec_perso, pa_perso, image_perso, dateCreation_perso, DLA_perso, clan, message_perso, chef, bataillon) 
													VALUES ('$id_joueur', '4', '$nom_perso_cree', '$x_instance', '$y_instance', '$pv_unite', '$pv_unite', '0', '$pm_unite', '$perception_unite', '$recup_unite', '$protection_unite', '0', '$image_perso_cree', NOW(), FROM_UNIXTIME($dla), $clan, '', 0, '$bataillon')";
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
											
											$nom_perso_tmp = $nom_perso_cree;
											$nom_pas_trouve = true;
											$i = 2;
											
											while ($nom_pas_trouve) {
												
												$nom_perso_cherche = addslashes($nom_perso_cree);
												
												$sql = "SELECT id_perso FROM perso WHERE nom_perso='$nom_perso_cherche'";
												$res = $mysqli->query($sql);
												$nb = $res->num_rows;
												
												if ($nb == 0) {
													$nom_pas_trouve = false;
												}
												else {
													$nom_perso_cree = $nom_perso_tmp.$i;
													
													$i++;
												}
											}
											
											// Calcul DLA
											$date = time();
											$dla = $date + DUREE_TOUR;
											
											$bataillon = addslashes($bataillon);
											
											// Créer nouveau Perso et la placer dans ce même batiment
											$sql = "INSERT INTO perso (IDJoueur_perso, type_perso, nom_perso, x_perso, y_perso, pvMax_perso, pv_perso, pm_perso, pmMax_perso, perception_perso, recup_perso, protec_perso, pa_perso, image_perso, dateCreation_perso, DLA_perso, clan, message_perso, chef, bataillon) 
													VALUES ('$id_joueur', '5', '$nom_perso_cree', '$x_instance', '$y_instance', '$pv_unite', '$pv_unite', '0', '$pm_unite', '$perception_unite', '$recup_unite', '$protection_unite', '0', '$image_perso_cree', NOW(), FROM_UNIXTIME($dla), $clan, '', 0, '$bataillon')";
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
										
										$possede_chien = false;
										
										$sql = "SELECT type_perso FROM perso WHERE idJoueur_perso='$id_joueur'";
										$res = $mysqli->query($sql);
										while ($tab = $res->fetch_assoc()) {
											
											$type_perso_joueur = $tab["type_perso"];
											
											if ($type_perso_joueur == 6) {
												$possede_chien = true;
											}
											
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
											
											if (!$possede_chien) {
											
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
												
												$nom_perso_tmp = $nom_perso_cree;
												$nom_pas_trouve = true;
												$i = 2;
												
												while ($nom_pas_trouve) {
													
													$nom_perso_cherche = addslashes($nom_perso_cree);
													
													$sql = "SELECT id_perso FROM perso WHERE nom_perso='$nom_perso_cherche'";
													$res = $mysqli->query($sql);
													$nb = $res->num_rows;
													
													if ($nb == 0) {
														$nom_pas_trouve = false;
													}
													else {
														$nom_perso_cree = $nom_perso_tmp.$i;
														
														$i++;
													}
												}
												
												// Calcul DLA
												$date = time();
												$dla = $date + DUREE_TOUR;
												
												$bataillon = addslashes($bataillon);
												
												// Créer nouveau Perso et la placer dans ce même batiment
												$sql = "INSERT INTO perso (IDJoueur_perso, type_perso, nom_perso, x_perso, y_perso, pvMax_perso, pv_perso, pm_perso, pmMax_perso, perception_perso, recup_perso, protec_perso, pa_perso, chargeMax_perso, image_perso, dateCreation_perso, DLA_perso, clan, message_perso, chef, bataillon) 
														VALUES ('$id_joueur', '6', '$nom_perso_cree', '$x_instance', '$y_instance', '$pv_unite', '$pv_unite', '0', '$pm_unite', '$perception_unite', '$recup_unite', '$protection_unite', '0', '2', '$image_perso_cree', NOW(), FROM_UNIXTIME($dla), $clan, '', 0, '$bataillon')";
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
											}
											else {
												// TODO - tentative de triche
												
												echo "<center><font color=red>Vous possédez déjà un chien, il n'est pas possible de posséder plus d'un chien par bataillon</font></center>";
											}
										} else {
											echo "<center><font color=red>Vous n'avez pas assez de point de grouillot pour pouvoir recruter cette unité. Il vous reste $pg_restant points de grouillot</font></center>";
										}
									} else {
										echo "<center><font color=red>Vous n'avez pas assez de point d'action pour recruter un grouillot, il vous reste $pa_perso points d'action</font></center>";
									}
								}
								
								$possede_chien = false;
								
								// Calculer PG déjà utilisés par le joueur
								$pg_utilise = 0;
								
								$sql = "SELECT type_perso FROM perso WHERE idJoueur_perso='$id_joueur'";
								$res = $mysqli->query($sql);
								
								while ($tab = $res->fetch_assoc()) {
									
									$type_perso_joueur = $tab["type_perso"];
									
									if ($type_perso_joueur == 6) {
										$possede_chien = true;
									}
									
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
									
									// Conditions si Possibilité de recruter
									if ((($id_unite == 6 && $id_grade >= 3 && !$possede_chien) || $id_unite != 6) && $pa_perso >= 3 && $cout_pg_unite <= $pg_restant) {
										echo "		<td align='center'><input type='submit' name=\"".$id_unite."\" class='btn btn-success' value=\">> Recruter !\"></td>";
									}
									else if ($id_unite == 6 && $id_grade < 6) {
										echo "<td align='center'>Grade insufisant</td>";
									}
									else if ($id_unite == 6 && $possede_chien) {
										echo "<td align='center'>Un chien par bataillon</td>";
									}
									else if ($pa_perso< 3) {
										echo "<td align='center'>PA insufisants</td>";
									}
									else if ($cout_pg_unite > $pg_restant) {
										echo "<td align='center'>PG insufisants</td>";
									}
									else {
										echo "<td align='center'>Non recrutable</td>";
									}
									echo "	</tr>";
									
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
		</div>
		
		<!-- Optional JavaScript -->
		<!-- jQuery first, then Popper.js, then Bootstrap JS -->
		<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
		<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
		
	</body>
</html>
<?php
}
else {
	// logout
	$_SESSION = array(); // On écrase le tableau de session
	session_destroy(); // On détruit la session
	
	header("Location:../index2.php");
}
?>
