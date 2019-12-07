<?php
@session_start();

require_once("../fonctions.php");
require_once("f_carte.php");
require_once("f_combat.php");

$mysqli = db_connexion();

include ('../nb_online.php');

// Traitement selection perso
if (isset($_POST["select_perso"]) && $_POST["select_perso"] == "ok" && isset($_POST["liste_perso"])) {
	$id_perso = $_SESSION['id_perso'] = $_POST["liste_perso"];
}

if(isset($_SESSION["id_perso"])){
	$id_perso = $_SESSION['id_perso'];
}

// recupération config jeu
$dispo = config_dispo_jeu($mysqli);
$admin = admin_perso($mysqli, $id_perso);

if($dispo || !$admin){
	
	if(isset($_SESSION["id_perso"])){
		
		$id_perso = $_SESSION['id_perso'];
		$date = time();
		
		$sql_joueur = "SELECT idJoueur_perso FROM perso WHERE id_perso='$id_perso'";
		$res_joueur = $mysqli->query($sql_joueur);
		$t_joueur = $res_joueur->fetch_assoc();
		
		$id_joueur_perso = $t_joueur["idJoueur_perso"];
		
		$sql_dla = "SELECT UNIX_TIMESTAMP(DLA_perso) as DLA, est_gele FROM perso WHERE idJoueur_perso='$id_joueur_perso' AND chef=1";
		$res_dla = $mysqli->query($sql_dla);
		$t_dla = $res_dla->fetch_assoc();
		
		$dla 		= $t_dla["DLA"];
		$est_gele 	= $t_dla["est_gele"];
	
		$sql = "SELECT pv_perso FROM perso WHERE id_perso='$id_perso'";
		$res = $mysqli->query($sql);
		$tpv = $res->fetch_assoc();
		
		$testpv = $tpv['pv_perso'];
		
		$config = '1';
		
		// verification si le perso est encore en vie
		if ($testpv <= 0) {
			// le perso est mort
			header("Location: ../tour.php"); 
		}
		else { 
			// le perso est vivant
			// verification si nouveau tour ou gele
			if(nouveau_tour($date, $dla) || $est_gele) {
				header("Location: ../tour.php");
			}
			else {
				$erreur = "<font color=red>";
				$mess_bat ="";
	
				if(isset($_SESSION["nv_tour"]) && $_SESSION["nv_tour"] == 1){
					echo "<center><font color=red><b>Nouveau tour</b></font></center>";
					$_SESSION["nv_tour"] = 0;
				}
				?>
				
	<SCRIPT LANGUAGE="JavaScript" type="text/javascript">
	function moins(){
		if(document.change_des.valeur_defense.value > 1){
			document.change_des.valeur_attaque.value++;
			document.change_des.valeur_defense.value--;
		}
		else {
			alert('Impossible de monter plus votre attaque');
		}
	}
	
	function plus(){
		if(document.change_des.valeur_attaque.value > 1){
			document.change_des.valeur_defense.value++;
			document.change_des.valeur_attaque.value--;
		}
		else {
			alert('Impossible de monter plus votre defense');
		}
	}
	</SCRIPT>
	
				<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
				<html>
					<head>
						<title>Nord VS Sud</title>
						<meta charset="utf-8"/>
						<link href="../style2.css" rel="stylesheet" type="text/css">
					</head>

					<body>
					
				<?php
				
				// recuperation des anciennes données du perso
				$sql = "SELECT idJoueur_perso, nom_perso, x_perso, y_perso, pm_perso, image_perso, pa_perso, recup_perso, bonusRecup_perso, bonusPM_perso, paMax_perso, pv_perso, DLA_perso, clan FROM perso WHERE id_perso='$id_perso'";
				$res = $mysqli->query($sql);
				$t_perso1 = $res->fetch_assoc();
				
				$id_joueur_perso 	= $t_perso1["idJoueur_perso"];
				$nom_perso 			= $t_perso1["nom_perso"];
				$x_persoN 			= $t_perso1["x_perso"];
				$y_persoN 			= $t_perso1["y_perso"];
				$pm_perso 			= $t_perso1["pm_perso"];
				$n_dla 				= $t_perso1["DLA_perso"];
				$image_perso 		= $t_perso1["image_perso"];
				$bonusPM_perso_p 	= $t_perso1["bonusPM_perso"];
				$clan_p 			= $t_perso1["clan"];
				
				// récupération de la couleur du camp
				$couleur_clan_p = couleur_clan($clan_p);
				
				$X_MAX = X_MAX;
				$Y_MAX = Y_MAX;
				$carte = "carte";
				
				if(isset($_GET['erreur']) && $_GET['erreur'] == 'competence'){
					$erreur = '<font color=red>competence indiponible pour le moment</font>';
				}			
				
				// calcul malus pm
				$malus_pm = $bonusPM_perso_p;
				
				// traitement entrée dans un batiment
				if(isset($_GET["bat"])) {
					
					$id_inst = $_GET["bat"];
					
					// on veut sortir du batiment
					if(isset($_GET["out"]) && $_GET["out"] == "ok") {
					
						// verification que le perso est bien dans un batiment...
						if(in_bat($mysqli, $id_perso)){
							
							// verification des pm du perso
							if($pm_perso + $malus_pm >= 1){
								
								$oc = 1;
								$seek = 1;
								
								// tant que les cases sont occupees
								while ($oc != 0){
								
									// recuperation des coordonnees des cases et de leur etat (occupee ou non)
									$sql = "SELECT occupee_carte, x_carte, y_carte, fond_carte FROM $carte WHERE x_carte >= $x_persoN - $seek AND x_carte <= $x_persoN + $seek AND y_carte >= $y_persoN - $seek AND y_carte <= $y_persoN + $seek";
									$res = $mysqli->query($sql);
									
									while($t = $res->fetch_assoc()){
										
										$oc 	= $t["occupee_carte"];
										$xs 	= $t["x_carte"];
										$ys 	= $t["y_carte"];
										$fond_c = $t["fond_carte"];
										
										if($oc == 0) {
											break;
										}
									}
									$seek++; // on elargie la recherche
								}
								
								// mise a jour des coordonnees du perso et de ses pm
								$sql = "UPDATE perso SET x_perso = '$xs', y_perso = '$ys', pm_perso=pm_perso-1 WHERE id_perso = '$id_perso'";
								$mysqli->query($sql);
								
								$x_persoN = $xs;
								$y_persoN = $ys;
								
								// mise a jour des coordonnees du perso sur la carte et changement d'etat de la case
								$sql = "UPDATE $carte SET occupee_carte='1', image_carte='$image_perso' ,idPerso_carte='$id_perso' WHERE x_carte = '$xs' AND y_carte = '$ys'";
								$mysqli->query($sql);
								
								// mise a jour de la table perso_in_batiment
								$sql = "DELETE FROM perso_in_batiment WHERE id_perso='$id_perso'";
								$mysqli->query($sql);
								
								// mise a jour des evenements
								$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ('$id_perso','<font color=$couleur_clan_p>$nom_perso</font>','est sorti du batiment',NULL,'','en $xs/$ys',NOW(),'0')";
								$mysqli->query($sql);
								
								// recuperation des fonds
								$sql = "SELECT fond_carte, image_carte, image_carte FROM $carte WHERE x_carte='$xs' AND y_carte='$ys'";
								$res_map = $mysqli->query ($sql);
								$t_carte1 = $res_map->fetch_assoc();
								
								$fond = $t_carte1["fond_carte"];
								
								// mise a jour du bonus de perception
								$bonus_visu = get_malus_visu($fond);
								if(bourre($mysqli, $id_perso)){
									if(!endurance_alcool($mysqli, $id_perso)) {
										$malus_bourre = bourre($mysqli, $id_perso) * 2;
										$bonus_visu -= $malus_bourre;
									}
								}
								
								$sql = "UPDATE perso SET bonusPerception_perso=$bonus_visu WHERE id_perso='$id_perso'";
								$mysqli->query($sql);
							}
							else {
								$erreur .= "Il faut posséder au moins 1pm pour sortir du bâtiment";
							}
						}
						else {
							$erreur .= "Vous n'êtes pas dans le batiment donc vous ne pouvez pas en sortir";
						}
					}
					else {
						// on veut rentrer dans le batiment
					
						// traitement du cas tour de visu et de la tour de garde où il ne peut y avoir qu'un seul perso dedans !
						if(isset($_GET["bat2"]) && ($_GET["bat2"] == 2 || $_GET["bat2"] == 3) && isset($_GET["bat"]) && $_GET["bat"]!="") {
						
							// Vérification que le perso soit pas déjà dans un bâtiment
							if(!in_bat($mysqli, $id_perso)){
						
								// verification que l'instance du batiment existe
								if (existe_instance_bat($mysqli, $_GET["bat"])){
								
									if(verif_bat_instance($mysqli, $_GET["bat2"],$_GET["bat"])){
							
										// verification qu'on soit bien à côté du batiment
										if(prox_instance_bat($mysqli, $x_persoN, $y_persoN, $_GET["bat"])){
										
											// verification si il y a un perso dans la tour
											$sql = "SELECT id_perso FROM perso_in_batiment WHERE id_instanceBat=".$_GET["bat"]."";
											$res = $mysqli->query($sql);
											$nbp = $res->fetch_row();
											
											if($nbp[0] != 0){
												// si la tour est occupee
												$erreur .= "Vous ne pouvez pas entrée, la tour est déjà occupée";
											}
											else { // la tour est vide
											
												// verification que le perso a encore des pm
												if($pm_perso + $malus_pm >= 1){
												
													// recuperation des coordonnees et infos du batiment dans lequel le perso entre
													$sql = "SELECT nom_instance, niveau_instance, x_instance, y_instance FROM instance_batiment WHERE id_instanceBat=".$_GET["bat"]."";
													$res = $mysqli->query($sql);
													$coordonnees_instance = $res->fetch_assoc();
													
													$x_bat 				= $coordonnees_instance["x_instance"];
													$y_bat 				= $coordonnees_instance["y_instance"];
													$nom_bat 			= $coordonnees_instance["nom_instance"];
													$niveau_instance 	= $coordonnees_instance["niveau_instance"];
													$id_inst_bat 		= $_GET["bat"];
													
													// verification si le perso est de la même nation ou non que le batiment
													if(!nation_perso_bat($mysqli, $id_perso, $id_inst_bat)) { // pas même nation
													
														// capture du batiment, il devient de la nation du perso
														$sql = "UPDATE instance_batiment, perso SET camp_instance=clan WHERE id_instanceBat='$id_inst_bat' AND id_perso='$id_perso'";
														$mysqli->query($sql);
														
														$sql = "select clan from perso where id_perso='$id_perso'";
														$res = $mysqli->query($sql);
														$t_c = $res->fetch_assoc();
														
														$camp = $t_c["clan"];
														
														if($camp == "1"){
															$couleur_c = "b";
														}
														if($camp == "2"){
															$couleur_c = "r";
														}
														if($camp == "3"){
															$couleur_c = "v";
														}
														
														//mise à jour de l'icone
														$icone = "b".$_GET["bat2"]."$couleur_c.png";
														$sql = "UPDATE $carte SET image_carte='$icone' WHERE x_carte=$x_bat and y_carte=$y_bat";
														$mysqli->query($sql);
														
														// mise a jour table evenement
														$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ('$id_perso','<font color=$couleur_clan_p>$nom_perso</font>','a capturé le batiment $nom_bat','$id_inst_bat','','en $x_bat/$y_bat : Felicitation!',NOW(),'0')";
														$mysqli->query($sql);
														
														echo "<font color = red>Felicitation, vous venez de capturer un batiment ennemi !</font><br>";
													}
													
													// mise a jour de la carte
													$sql = "UPDATE $carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_persoN' AND y_carte='$y_persoN'";
													$res = $mysqli->query($sql);
														
													// mise a jour des coordonnées du perso
													$sql = "UPDATE perso SET x_perso='$x_bat', y_perso='$y_bat', pm_perso=pm_perso-1 WHERE id_perso='$id_perso'";
													$res = $mysqli->query($sql);
														
													// insertion du perso dans la table perso_in_batiment
													$sql = "INSERT INTO `perso_in_batiment` VALUES ('$id_perso','$id_inst_bat')";
													$mysqli->query($sql);
													
													echo"<font color = blue>vous êtes entrée dans le batiment $id_inst_bat</font><br>";
														
													// mise a jour table evenement
													$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ('$id_perso','<font color=$couleur_clan_p>$nom_perso</font>','est entré dans le batiment $nom_bat $id_inst_bat',NULL,'','en $x_bat/$y_bat',NOW(),'0')";
													$mysqli->query($sql);
														
													// calcul du bonus de perception
													if($_GET["bat2"] == 2){
														$bonus_perc = $niveau_instance;
													}
													if($_GET["bat2"] == 3){
														$bonus_perc = 2;
													}	
													
													// mise a jour du bonus de perception du perso
													$bonus_visu = $bonus_perc;
													
													if(bourre($mysqli, $id_perso)){
														if(!endurance_alcool($mysqli, $id_perso)) {
															$malus_bourre = bourre($mysqli, $id_perso) * 2;
															$bonus_visu -= $malus_bourre;
														}
													}
													// maj bonus perception et -1 pm pour rentrer dans le batiment
													$sql = "UPDATE perso SET bonusPerception_perso=$bonus_visu, pm_perso=pm_perso-1 WHERE id_perso='$id_perso'";
													$mysqli->query($sql);
														
													// mise a jour des coordonnees du perso pour les tests d'après
													$x_persoN = $x_bat;
													$y_persoN = $y_bat;
												}
												else {
													$erreur .= "Il faut posséder au moins 1pm pour entrer dans le bâtiment";
												}
											}
										}
										else {
											$erreur .= "Il faut être à côté du bâtiment pour y entrer";
										}
									}
									else {
										$erreur .= "Pas bien d'essayer de tricher...";
									}
								}
								else {
									$erreur .= "Le batiment n'existe pas";
								}
							}
							else {
								$erreur .= "Vous devez sortir du bâtiment dans lequel vous vous trouvez afin de rentrer dans un nouveau bâtiment";
							}
						}
						// traitement des autres cas
						else {
							if(isset($_GET["bat"]) && $_GET["bat"]!="" && isset($_GET["bat2"]) && $_GET["bat2"]!="" && $_GET["bat2"] != 1){
								
								// Vérification que le perso soit pas déjà dans un bâtiment
								if(!in_bat($mysqli, $id_perso)){
								
									// verification que l'instance du batiment existe
									if (existe_instance_bat($mysqli, $_GET["bat"])){
										
										if(verif_bat_instance($mysqli, $_GET["bat2"], $_GET["bat"])){
										
											// verification qu'on soit bien à côté du batiment
											if(prox_instance_bat($mysqli, $x_persoN, $y_persoN, $_GET["bat"])){
												
												// verification que le perso a encore des pm
												if($pm_perso + $malus_pm >= 1){
													
													//recuperation du nombre de persos dans le batiment
													$sql = "select id_perso from perso_in_batiment where id_instanceBat=".$_GET["bat"]."";
													$res = $mysqli->query($sql);
													$nb_perso_bat = $res->num_rows;
											
													// recuperation des coordonnees et des infos du batiment dans lequel le perso entre
													$sql = "SELECT id_instanceBat, nom_instance, x_instance, y_instance, contenance_instance FROM instance_batiment WHERE id_instanceBat=".$_GET["bat"]."";
													$res = $mysqli->query($sql);
													$coordonnees_instance = $res->fetch_assoc();
													
													$x_bat 					= $coordonnees_instance["x_instance"];
													$y_bat 					= $coordonnees_instance["y_instance"];
													$nom_bat 				= $coordonnees_instance["nom_instance"];
													$id_inst_bat 			= $coordonnees_instance["id_instanceBat"];
													$contenance_inst_bat 	= $coordonnees_instance["contenance_instance"];
													
													// verification contenance batiment
													if($nb_perso_bat < $contenance_inst_bat){
													
														// verification si le perso est de la même nation que le batiment
														if(!nation_perso_bat($mysqli, $id_perso, $id_inst_bat)) { // pas même nation
														
															// verification que le batiment est vide
															if(batiment_vide($mysqli, $id_inst_bat)) {
																
																// capture du batiment, il devient de la nation du perso
																$sql = "UPDATE instance_batiment, perso SET camp_instance=clan WHERE id_instanceBat='$id_inst_bat' AND id_perso='$id_perso'";
																$mysqli->query($sql);
																	
																$sql = "select clan from perso where id_perso='$id_perso'";
																$res = $mysqli->query($sql);
																$t_c = $res->fetch_assoc();
																
																$camp = $t_c["clan"];
																
																if($camp == "1"){
																	$couleur_c = "b";
																}
																if($camp == "2"){
																	$couleur_c = "r";
																}
																if($camp == "3"){
																	$couleur_c = "v";
																}
																
																//mise à jour de l'icone
																$icone = "b".$_GET["bat2"]."$couleur_c.png";
																$sql = "UPDATE $carte SET image_carte='$icone' WHERE x_carte=$x_bat and y_carte=$y_bat";
																$mysqli->query($sql);
																	
																// mise a jour table evenement
																$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ('$id_perso','<font color=$couleur_clan_p>$nom_perso</font>','a capturé le batiment $nom_bat','$id_inst_bat','','en $x_bat/$y_bat : Felicitation!',NOW(),'0')";
																$mysqli->query($sql);
																
																echo "<font color = red>Félicitation, vous venez de capturer un batiment ennemi !</font><br>";
															}
														}
													
														// mise a jour des coordonnées du perso sur la carte
														$sql = "UPDATE $carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_persoN' AND y_carte='$y_persoN'";
														$res = $mysqli->query($sql);
														
														// mise a jour des coordonnées du perso
														$sql = "UPDATE perso SET x_perso='$x_bat', y_perso='$y_bat' WHERE id_perso='$id_perso'";
														$res = $mysqli->query($sql);
														
														// insertion du perso dans la table perso_in_batiment
														$sql = "INSERT INTO `perso_in_batiment` VALUES ('$id_perso','$id_inst_bat')";
														$mysqli->query($sql);
														
														echo"<font color = blue>vous êtes entrée dans le batiment $nom_bat</font>";
														
														// mise a jour table evenement
														$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ('$id_perso','<font color=$couleur_clan_p>$nom_perso</font>','est entré dans le batiment $nom_bat $id_inst_bat',NULL,'','en $x_bat/$y_bat',NOW(),'0')";
														$mysqli->query($sql);
														
														$bonus_perc = 0;
														
														// mise a jour du bonus de perception du perso
														$bonus_visu = $bonus_perc;
														
														if(bourre($mysqli, $id_perso)){
															
															if(!endurance_alcool($mysqli, $id_perso)) {
																
																$malus_bourre = bourre($mysqli, $id_perso) * 2;
																$bonus_visu -= $malus_bourre;
																
															}
														}
														
														// maj bonus perception et -1 pm pour l'entrée dans le batiment
														$sql = "UPDATE perso SET bonusPerception_perso=$bonus_visu, pm_perso=pm_perso-1 WHERE id_perso='$id_perso'";
														$mysqli->query($sql);
														
														// mise a jour des coordonnees du perso pour le test d'après
														$x_persoN = $x_bat;
														$y_persoN = $y_bat;
													}
													else {
														$erreur .= "Le bâtiment est déjà rempli au maximum de sa capacité";
													}
												}
												else {
													$erreur .= "Il faut posséder au moins 1pm pour entrer dans le bâtiment";
												}
											}
											else {
												$erreur .= "Il faut être à côté du batiment pour y entrer";
											}
										}
										else {
											$erreur .= "Pas bien d'essayer de tricher...";
										}
									}
									else {
										$erreur .= "Le batiment n'existe pas";
									}
								}
								else {
									$erreur .= "Vous devez sortir du bâtiment dans lequel vous vous trouvez afin de rentrer dans un nouveau bâtiment";
								}
							}
						}
					}
				}
				
				if(in_bat($mysqli, $id_perso)){
					
					// Récupération des infos sur l'instance du batiment dans lequel le perso se trouve
					$sql = "SELECT id_instanceBat, id_batiment, nom_instance, niveau_instance FROM instance_batiment WHERE x_instance='$x_persoN' AND y_instance='$y_persoN'";
					$res = $mysqli->query($sql);
					$t = $res->fetch_assoc();
					
					$id_bat 			= $t["id_instanceBat"];
					$bat 				= $t["id_batiment"];
					$niveau_instance 	= $t["niveau_instance"];
					$nom_ibat 			= $t["nom_instance"];
					
					//recuperation du nom du batiment
					$sql_n = "SELECT nom_batiment FROM batiment WHERE id_batiment = '$bat'";
					$res_n = $mysqli->query($sql_n);
					$t_n = $res_n->fetch_assoc();
					
					$nom_bat = $t_n["nom_batiment"];
					
					$mess_bat .= "<center><font color = blue>~~<a href=\"batiment.php?bat=$id_bat\" target='_blank'> acceder a la page du batiment $nom_bat $nom_ibat</a>~~</font></center>";
					$mess_bat .= "<center><font color = blue>~~<a href=\"jouer.php?bat=$id_bat&bat2=$bat&out=ok\" > sortir du batiment $nom_bat $nom_ibat</a>~~</font></center>";
					
					$bonus_perc = 0;
					
					// calcul du bonus de perception
					if($bat == 2){
						$bonus_perc += $niveau_instance;
					}
					if($bat == 3){
						$bonus_perc += 2;
					}
					
					// mise a jour du bonus de perception du perso
					$bonus_visu = $bonus_perc;
					if(bourre($mysqli, $id_perso)){
						if(!endurance_alcool($mysqli, $id_perso)) {
							$malus_bourre = bourre($mysqli, $id_perso) * 2;
							$bonus_visu -= $malus_bourre;
						}
					}
					
					$sql = "UPDATE perso SET bonusPerception_perso=$bonus_visu WHERE id_perso='$id_perso'";
					$mysqli->query($sql);
				}
				
				// traitement de l'ouverture du coffre
				if(isset($_GET['coffre']) && $_GET['coffre'] == "ok"){
					$ok_c = 0;
					
					// verification que le perso soit bien à proximité d'un coffre
					$sql = "SELECT occupee_carte, image_carte, x_carte, y_carte FROM $carte WHERE x_carte >= $x_persoN - 1 AND x_carte <= $x_persoN + 1 AND y_carte >= $y_persoN - 1 AND y_carte <= $y_persoN + 1";
					$res = $mysqli->query($sql);
						
					while ($t_c = $res->fetch_assoc()){
						
						$oc_c = $t_c['occupee_carte'];
						
						if($oc_c){
							
							$im_c = $t_c["image_carte"];
							
							if($im_c == "coffre1t.png"){
								
								$x_c = $t_c["x_carte"];
								$y_c = $t_c["y_carte"];
								$ok_c = 1;
								
								break;
							}
						}
					}
					if($ok_c){
						
						// on met à jour l'image du coffre
						$sql = "UPDATE $carte SET image_carte='coffre2t.png' WHERE x_carte='$x_c' AND y_carte='$y_c'";
						$mysqli->query($sql);
						
						// on recupere le contenu du coffre
						$contenu_c = contenu_coffre();
						
						// on met à jour le nombre d'objet
						$sql = "UPDATE contenu_coffre SET nb_objet=nb_objet-1 WHERE id_objet='$contenu_c'";
						$mysqli->query($sql);
						
						// on recupere les infos sur l'objet recupéré
						$sql = "SELECT nom_objet, poids_objet FROM objet WHERE id_objet='$contenu_c'";
						$res = $mysqli->query($sql);
						$info_c = $res->fetch_assoc();
						
						$nom_o_c 	= $info_c["nom_objet"];
						$poids_o_c 	= $info_c["poids_objet"];
						
						// MAj malus charge
						$malus_pm = $bonusPM_perso_p;
						
						// on met à jour les objets du perso
						$sql = "INSERT INTO perso_as_objet VALUES ('$id_perso','$contenu_c');";
						$mysqli->query($sql);
						
						echo "<center>Vous venez de trouver l'objet <b>$nom_o_c</b> dans un coffre</center><br>";
					}
					else {
						echo "<center><b>C'est pas bien d'essayer de tricher...</b> (il faut etre a proximité d'un coffre pour pouvoir l'ouvrir)</center>";
					}
				}
	
				// traitement des deplacements
				if (isset($_GET["mouv"])) {
					
					$mouv = $_GET["mouv"];
					$x_persoE = $t_perso1["x_perso"];
					$y_persoE = $t_perso1["y_perso"];
					$pm_perso = $t_perso1["pm_perso"];
					
					if(!in_bat($mysqli, $id_perso)){
					
						if (reste_pm($pm_perso + $malus_pm)) {
							switch($mouv){ //on modifie les coordonnées du perso suivant le deplacement qu'il a effectué
								case 1: $x_persoN=$x_persoE-1; $y_persoN=$y_persoE+1; break;
								case 2: $x_persoN=$x_persoE; $y_persoN=$y_persoE+1; break;
								case 3: $x_persoN=$x_persoE+1; $y_persoN=$y_persoE+1; break;
								case 4: $x_persoN=$x_persoE-1; $y_persoN=$y_persoE; break;
								case 5: $x_persoN=$x_persoE+1; $y_persoN=$y_persoE; break;
								case 6: $x_persoN=$x_persoE-1; $y_persoN=$y_persoE-1; break;
								case 7: $x_persoN=$x_persoE; $y_persoN=$y_persoE-1; break;
								case 8: $x_persoN=$x_persoE+1; $y_persoN=$y_persoE-1; break;
							}
								
							$in_map = in_map($x_persoN, $y_persoN);
							
							if ($in_map) {
								
								$sql = "SELECT occupee_carte, fond_carte, image_carte FROM $carte WHERE x_carte=$x_persoN AND y_carte=$y_persoN";
								$res_map = $mysqli->query($sql);
								$t_carte1 = $res_map->fetch_assoc();
								
								$case_occupee 	= $t_carte1["occupee_carte"];
								$fond 			= $t_carte1["fond_carte"];
								
								$cout_pm 	= cout_pm($fond);
								$bonus_visu = get_malus_visu($fond);
								
								if(bourre($mysqli, $id_perso)){
									if(!endurance_alcool($mysqli, $id_perso)){
										$malus_bourre = bourre($mysqli, $id_perso) * 2;
										$bonus_visu -= $malus_bourre;
									}
								}
		
								if (!is_eau_p($fond)) {
									
									if (!$case_occupee){
										
										if($pm_perso  + $malus_pm >= $cout_pm){	
										
											// maj perso : mise à jour des pm et du bonus de perception
											$sql = "UPDATE perso SET pm_perso =$pm_perso-$cout_pm, bonusPerception_perso=$bonus_visu WHERE id_perso='$id_perso'"; 
											$mysqli->query($sql);
											
											//mise à jour des coordonnées du perso 
											$dep = "UPDATE perso SET x_perso=$x_persoN, y_perso=$y_persoN WHERE id_perso ='$id_perso'"; 
											$mysqli->query($dep);
											
											// maj carte
											// on met à jour le nombre de perso sur son ancien emplacement
											$sql = "UPDATE $carte SET occupee_carte='0', image_carte=NULL, idPerso_carte=NULL WHERE x_carte='$x_persoE' AND y_carte='$y_persoE'";
											$mysqli->query($sql);
											
											// on met à jour le nombre de perso sur son nouvel emplacement
											$sql = "UPDATE $carte SET occupee_carte='1', image_carte='$image_perso', idPerso_carte='$id_perso' WHERE x_carte='$x_persoN' AND y_carte='$y_persoN'"; 
											$mysqli->query($sql);
											
											// maj evenement
											$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_p>$nom_perso</font>','s\'est deplacé',NULL,'','en $x_persoN/$y_persoN',NOW(),'0')";
											$mysqli->query($sql);
	
											if(prox_bat($mysqli, $x_persoN, $y_persoN, $id_perso) && !in_bat($mysqli, $id_perso)){ // verification si il y a un batiment a proximite du perso
											
												// recuperation des id et noms des batiments dans lesquels le perso peut entrer
												$res_bat = id_prox_bat($mysqli, $x_persoN, $y_persoN);
												
												while ($bat1 = $res_bat->fetch_assoc()) {
													
													$nom_ibat 		= $bat1["nom_instance"];
													$id_bat 		= $bat1["id_instanceBat"];
													$bat 			= $bat1["id_batiment"];
													$pv_instance 	= $bat1["pv_instance"];
													$pvMax_instance = $bat1["pvMax_instance"];
													
													//recuperation du nom du batiment
													$sql_n = "SELECT nom_batiment FROM batiment WHERE id_batiment = '$bat'";
													$res_n = $mysqli->query($sql_n);
													$t_n = $res_n->fetch_assoc();
													
													$nom_bat = $t_n["nom_batiment"];
													
													// verification si le batiment est de la même nation que le perso
													if(!nation_perso_bat($mysqli, $id_perso, $id_bat)) { // pas même nation
													
														// verification si le batiment est vide
														if(batiment_vide($mysqli, $id_bat) && $bat != 1 && $bat != 5){
															$mess_bat .= "<center><font color = blue>~~<a href=\"jouer.php?bat=$id_bat&bat2=$bat\" > capturer le batiment $nom_bat $nom_ibat [$id_bat]</a>~~</font></center>";
														}
													}
													else {
														if($bat != 1 && $bat != 5){
															$mess_bat .= "<center><font color = blue>~~<a href=\"jouer.php?bat=$id_bat&bat2=$bat\" > entrer dans le batiment $nom_bat $nom_ibat [$id_bat]</a>~~</font></center>";
														}
														
														if ($pv_instance < $pvMax_instance) {
															$mess_bat .= "<center><font color = blue>~~<a href=\"action.php?bat=$id_bat&reparer=ok\" > reparer $nom_bat $nom_ibat [$id_bat]</a>~~</font></center>";
														}
													}
												}
											}
										}
										else{
										
											$erreur .= "Vous n'avez pas assez de pm !";
											
											if(prox_bat($mysqli, $x_persoE, $y_persoE, $id_perso) && !in_bat($mysqli, $id_perso)){ // verification si il y a un batiment a proximite du perso
											
												// recuperation des id et noms des batiments dans lesquels le perso peut entrer
												$res_bat = id_prox_bat($mysqli, $x_persoE, $y_persoE); 
												
												while ($bat1 = $res_bat->fetch_assoc()) {
													
													$nom_ibat 		= $bat1["nom_instance"];
													$id_bat 		= $bat1["id_instanceBat"];
													$bat 			= $bat1["id_batiment"];
													$pv_instance 	= $bat1["pv_instance"];
													$pvMax_instance = $bat1["pvMax_instance"];
													
													//recuperation du nom du batiment
													$sql_n = "SELECT nom_batiment FROM batiment WHERE id_batiment = '$bat'";
													$res_n = $mysqli->query($sql_n);
													$t_n = $res_n->fetch_assoc();
													
													$nom_bat = $t_n["nom_batiment"];
													
													// verification si le batiment est de la même nation que le perso
													if(!nation_perso_bat($mysqli, $id_perso, $id_bat)) { // pas même nation
													
														// verification si le batiment est vide
														if(batiment_vide($mysqli, $id_bat) && $bat != 1 && $bat != 5){
															$mess_bat .= "<center><font color = blue>~~<a href=\"jouer.php?bat=$id_bat&bat2=$bat\" > capturer le batiment $nom_bat $nom_ibat [$id_bat]</a>~~</font></center>";
														}
														
													}
													else {
														if($bat != 1 && $bat != 5){
															$mess_bat .= "<center><font color = blue>~~<a href=\"jouer.php?bat=$id_bat&bat2=$bat\" > entrer dans le batiment $nom_bat $nom_ibat [$id_bat]</a>~~</font></center>";
														}
														
														if ($pv_instance < $pvMax_instance) {
															$mess_bat .= "<center><font color = blue>~~<a href=\"action.php?bat=$id_bat&reparer=ok\" > reparer $nom_bat $nom_ibat [$id_bat]</a>~~</font></center>";
														}
													}
												}
											}
										}
									}
									elseif($case_occupee){
									
										$erreur .= "Cette case est déjà occupée !";
										
										if(prox_bat($mysqli, $x_persoE, $y_persoE, $id_perso) && !in_bat($mysqli, $id_perso)){ // verification si il y a un batiment a proximite du perso
										
											// recuperation des id et noms des batiments dans lesquels le perso peut entrer
											$res_bat = id_prox_bat($mysqli, $x_persoE, $y_persoE); 
											
											while ($bat1 = $res_bat->fetch_assoc()) {
												
												$nom_ibat 		= $bat1["nom_instance"];
												$id_bat 		= $bat1["id_instanceBat"];
												$bat 			= $bat1["id_batiment"];
												$pv_instance 	= $bat1["pv_instance"];
												$pvMax_instance = $bat1["pvMax_instance"];
													
												//recuperation du nom du batiment
												$sql_n = "SELECT nom_batiment FROM batiment WHERE id_batiment = '$bat'";
												$res_n = $mysqli->query($sql_n);
												$t_n = $res_n->fetch_assoc();
												
												$nom_bat = $t_n["nom_batiment"];
													
												// verification si le batiment est de la même nation que le perso
												if(!nation_perso_bat($mysqli, $id_perso, $id_bat)) { // pas même nation
												
													// verification si le batiment est vide
													if(batiment_vide($mysqli, $id_bat) && $bat != 1 && $bat != 5){
														$mess_bat .= "<center><font color = blue>~~<a href=\"jouer.php?bat=$id_bat&bat2=$bat\" > capturer le batiment $nom_bat $nom_ibat [$id_bat]</a>~~</font></center>";
													}
												}
												else {
													if($bat != 1 && $bat != 5){
														$mess_bat .= "<center><font color = blue>~~<a href=\"jouer.php?bat=$id_bat&bat2=$bat\" > entrer dans le batiment $nom_bat $nom_ibat [$id_bat]</a>~~</font></center>";
													}
													
													if ($pv_instance < $pvMax_instance) {
														$mess_bat .= "<center><font color = blue>~~<a href=\"action.php?bat=$id_bat&reparer=ok\" > reparer $nom_bat $nom_ibat [$id_bat]</a>~~</font></center>";
													}
												}
											}
										}
									}
								}
								elseif (is_eau_p($fond)) {
								
									$erreur .= "Vous ne pouvez pas vous deplacer en eau profonde !";
									
									if(prox_bat($mysqli, $x_persoE, $y_persoE, $id_perso) && !in_bat($mysqli, $id_perso)){ // verification si il y a un batiment a proximite du perso
									
										// recuperation des id et noms des batiments dans lesquels le perso peut entrer
										$res_bat = id_prox_bat($mysqli, $x_persoE, $y_persoE); 
										
										while ($bat1 = $res_bat->fetch_assoc()) {
											
											$nom_ibat 		= $bat1["nom_instance"];
											$id_bat 		= $bat1["id_instanceBat"];
											$bat 			= $bat1["id_batiment"];
											$pv_instance 	= $bat1["pv_instance"];
											$pvMax_instance = $bat1["pvMax_instance"];
											
											//recuperation du nom du batiment
											$sql_n = "SELECT nom_batiment FROM batiment WHERE id_batiment = '$bat'";
											$res_n = $mysqli->query($sql_n);
											$t_n = $res_n->fetch_assoc();
											
											$nom_bat = $t_n["nom_batiment"];
											
											// verification si le batiment est de la même nation que le perso
											if(!nation_perso_bat($mysqli, $id_perso, $id_bat)) { // pas même nation
											
												// verification si le batiment est vide
												if(batiment_vide($mysqli, $id_bat) && $bat != 1 && $bat != 5){
													$mess_bat .= "<center><font color = blue>~~<a href=\"jouer.php?bat=$id_bat&bat2=$bat\" > capturer le batiment $nom_bat $nom_ibat [$id_bat]</a>~~</font></center>";
												}
											}
											else {
												if($bat != 1 && $bat != 5){
													$mess_bat .= "<center><font color = blue>~~<a href=\"jouer.php?bat=$id_bat&bat2=$bat\" > entrer dans le batiment $nom_bat $nom_ibat [$id_bat]</a>~~</font></center>";
												}
												
												if ($pv_instance < $pvMax_instance) {
													$mess_bat .= "<center><font color = blue>~~<a href=\"action.php?bat=$id_bat&reparer=ok\" > reparer $nom_bat $nom_ibat [$id_bat]</a>~~</font></center>";
												}
											}
										}
									}
								}
							}
							elseif (!in_map($x_persoN, $y_persoN)){
							
								$erreur .= "Vous ne pouvez pas vous déplacer sur cette case, elle est hors limites !";
								
								if(prox_bat($mysqli, $x_persoE, $y_persoE, $id_perso) && !in_bat($mysqli, $id_perso)){ // verification si il y a un batiment a proximite du perso
								
									// recuperation des id et noms des batiments dans lesquels le perso peut entrer
									$res_bat = id_prox_bat($mysqli, $x_persoE, $y_persoE); 
									
									while ($bat1 = $res_bat->fetch_assoc()) {
										
										$nom_ibat 		= $bat1["nom_instance"];
										$id_bat 		= $bat1["id_instanceBat"];
										$bat 			= $bat1["id_batiment"];
										$pv_instance 	= $bat1["pv_instance"];
										$pvMax_instance = $bat1["pvMax_instance"];
											
										//recuperation du nom du batiment
										$sql_n = "SELECT nom_batiment FROM batiment WHERE id_batiment = '$bat'";
										$res_n = $mysqli->query($sql_n);
										$t_n = $res_n->fetch_assoc();
										
										$nom_bat = $t_n["nom_batiment"];
											
										// verification si le batiment est de la même nation que le perso
										if(!nation_perso_bat($mysqli, $id_perso, $id_bat)) { // pas même nation
										
											// verification si le batiment est vide
											if(batiment_vide($mysqli, $id_bat) && $bat != 1 && $bat != 5){
												$mess_bat .= "<center><font color = blue>~~<a href=\"jouer.php?bat=$id_bat&bat2=$bat\" > capturer le batiment $nom_bat $nom_ibat [$id_bat]</a>~~</font></center>";
											}
										}
										else {
											if($bat != 1 && $bat != 5){
												$mess_bat .= "<center><font color = blue>~~<a href=\"jouer.php?bat=$id_bat&bat2=$bat\" > entrer dans le batiment $nom_bat $nom_ibat [$id_bat]</a>~~</font></center>";
											}
											
											if ($pv_instance < $pvMax_instance) {
												$mess_bat .= "<center><font color = blue>~~<a href=\"action.php?bat=$id_bat&reparer=ok\" > reparer $nom_bat $nom_ibat [$id_bat]</a>~~</font></center>";
											}
										}
									}
								}
							}
						}
						elseif(!reste_pm($pm_perso + $malus_pm)){
						
							$erreur .= "Vous n'avez plus de pm !";
							
							if(prox_bat($mysqli, $x_persoE, $y_persoE, $id_perso) && !in_bat($mysqli, $id_perso)){ // verification si il y a un batiment a proximite du perso
							
								// recuperation des id et noms des batiments dans lesquels le perso peut entrer
								$res_bat = id_prox_bat($mysqli, $x_persoE, $y_persoE); 
								
								while ($bat1 = $res_bat->fetch_assoc()) {
									
									$nom_ibat 		= $bat1["nom_instance"];
									$id_bat 		= $bat1["id_instanceBat"];
									$bat 			= $bat1["id_batiment"];
									$pv_instance 	= $bat1["pv_instance"];
									$pvMax_instance = $bat1["pvMax_instance"];
									
									//recuperation du nom du batiment
									$sql_n = "SELECT nom_batiment FROM batiment WHERE id_batiment = '$bat'";
									$res_n = $mysqli->query($sql_n);
									$t_n = $res_n->fetch_assoc();
									$nom_bat = $t_n["nom_batiment"];
									
									// verification si le batiment est de la même nation que le perso
									if(!nation_perso_bat($mysqli, $id_perso, $id_bat)) { // pas même nation
									
										// verification si le batiment est vide
										if(batiment_vide($mysqli, $id_bat) && $bat != 1 && $bat != 5){
											$mess_bat .= "<center><font color = blue>~~<a href=\"jouer.php?bat=$id_bat&bat2=$bat\" > capturer le batiment $nom_bat $nom_ibat [$id_bat]</a>~~</font></center>";
										}
									}
									else {
										if($bat != 1 && $bat != 5){
											$mess_bat .= "<center><font color = blue>~~<a href=\"jouer.php?bat=$id_bat&bat2=$bat\" > entrer dans le batiment $nom_bat $nom_ibat [$id_bat]</a>~~</font></center>";
										}
										
										if ($pv_instance < $pvMax_instance) {
											$mess_bat .= "<center><font color = blue>~~<a href=\"action.php?bat=$id_bat&reparer=ok\" > reparer $nom_bat $nom_ibat [$id_bat]</a>~~</font></center>";
										}
									}
								}
							}
						}
						else { // normalement impossible
							$erreur .= "Veuillez contacter l'administrateur si vous voyez ce message, merci";
						}
					}
					else {
						$erreur .= "Vous ne pouvez pas vous déplacer si vous êtes dans un bâtiment";
					}
				}
				else {
					// verification si il y a un batiment a proximite du perso
					if(prox_bat($mysqli, $x_persoN, $y_persoN, $id_perso) && !in_bat($mysqli, $id_perso)){ 
					
						// recuperation des id et noms des batiments dans lesquels le perso peut entrer
						$res_bat = id_prox_bat($mysqli, $x_persoN, $y_persoN);
						
						while ($bat1 = $res_bat->fetch_assoc()) {
							
							$nom_ibat 		= $bat1["nom_instance"];
							$id_bat 		= $bat1["id_instanceBat"];
							$bat 			= $bat1["id_batiment"];
							$pv_instance 	= $bat1["pv_instance"];
							$pvMax_instance = $bat1["pvMax_instance"];
							
							//recuperation du nom du batiment
							$sql_n = "SELECT nom_batiment FROM batiment WHERE id_batiment = '$bat'";
							$res_n = $mysqli->query($sql_n);
							$t_n = $res_n->fetch_assoc();
							
							$nom_bat = $t_n["nom_batiment"];
							
							// verification si le batiment est de la même nation que le perso
							if(!nation_perso_bat($mysqli, $id_perso, $id_bat)) { // pas même nation
							
								// verification si le batiment est vide
								if(batiment_vide($mysqli, $id_bat) && $bat != 1 && $bat != 5){
									$mess_bat .= "<center><font color = blue>~~<a href=\"jouer.php?bat=$id_bat&bat2=$bat\" > capturer le batiment $nom_bat $nom_ibat [$id_bat]</a>~~</font></center>";
								}
							}
							else {
								if($bat != 1 && $bat != 5){
									$mess_bat .= "<center><font color = blue>~~<a href=\"jouer.php?bat=$id_bat&bat2=$bat\" > entrer dans le batiment $nom_bat $nom_ibat [$id_bat] </a>~~</font></center>";
								}
								
								if ($pv_instance < $pvMax_instance) {
									$mess_bat .= "<center><font color = blue>~~<a href=\"action.php?bat=$id_bat&reparer=ok\" > reparer $nom_bat $nom_ibat [$id_bat]</a>~~</font></center>";
								}
							}
						}
					}
				}
				
				//affichage de l'heure serveur et de nouveau tour
				echo "<table width=100% bgcolor='white' border=0>";
				echo "<tr>
						<td><img src='../images/clock.png' alt='horloge' width='25' height='25'/> Heure serveur : <b><span id=tp1>".date('H:i:s ')."</span></b></td>
						<td rowspan=2><img src='../images/accueil/banniere.jpg' alt='banniere Nord VS Sud' width=150 height=63 /></td>
						<td align=right> <a href=\"../logout.php\"><font color=red><b>[déconnexion]</b></font></a></td>
					</tr>";
				echo "<tr>
						<td>Prochain tour :  ".$n_dla."</td>
						<td align=right> <a href=\"../forum2/index.php\"><font color=blue><b>[forum]</b></font></a></td>
					</tr>";
				echo "</table>";
	
				$sql_info = "SELECT xp_perso, pc_perso, pv_perso, pvMax_perso, pa_perso, paMax_perso, pi_perso, pm_perso, pmMax_perso, recup_perso, protec_perso, type_perso, x_perso, y_perso, perception_perso, bonusPerception_perso, bonus_perso, image_perso, clan, bataillon FROM perso WHERE ID_perso ='$id_perso'"; 
				$res_info = $mysqli->query($sql_info);
				$t_perso2 = $res_info->fetch_assoc();
				
				$x_perso 				= $t_perso2["x_perso"];
				$y_perso 				= $t_perso2["y_perso"];
				$image_perso 			= $t_perso2["image_perso"];
				$perc 					= $t_perso2["perception_perso"] + $t_perso2["bonusPerception_perso"];
				$pa_perso 				= $t_perso2["pa_perso"];
				$paMax_perso 			= $t_perso2["paMax_perso"];
				$pi_perso 				= $t_perso2["pi_perso"];
				$xp_perso 				= $t_perso2["xp_perso"];
				$pc_perso 				= $t_perso2["pc_perso"];
				$pv_perso 				= $t_perso2["pv_perso"];
				$pvMax_perso 			= $t_perso2["pvMax_perso"];
				$pm_perso 				= $t_perso2["pm_perso"] + $malus_pm;
				$pmMax_perso 			= $t_perso2["pmMax_perso"];
				$perception_perso 		= $t_perso2["perception_perso"];
				$bonusPerception_perso 	= $t_perso2["bonusPerception_perso"];
				$recup_perso 			= $t_perso2["recup_perso"];
				$protec_perso 			= $t_perso2["protec_perso"];
				$bonus_perso 			= $t_perso2["bonus_perso"];
				$type_perso 			= $t_perso2["type_perso"];
				$bataillon_perso 		= $t_perso2["bataillon"];
				
				$clan_perso = $t_perso2["clan"];
				
				if($clan_perso == 1){
					$clan = 'rond_b.png';
					$couleur_clan_perso = 'blue';
					$image_profil = "nord.gif";
				}
				if($clan_perso == 2){
					$clan = 'rond_r.png';
					$couleur_clan_perso = 'red';
					$image_profil = "sud.gif";
				}
				
				// récupération du grade du perso 
				$sql_grade = "SELECT perso_as_grade.id_grade, nom_grade FROM perso_as_grade, grades WHERE perso_as_grade.id_grade = grades.id_grade AND id_perso='$id_perso'";
				$res_grade = $mysqli->query($sql_grade);
				$t_grade = $res_grade->fetch_assoc();
				
				$id_grade_perso 	= $t_grade["id_grade"];
				$nom_grade_perso 	= $t_grade["nom_grade"];
				
				// cas particuliers grouillot
				if ($id_grade_perso == 101) {
					$id_grade_perso = "1.1";
				}
				if ($id_grade_perso == 102) {
					$id_grade_perso = "1.2";
				}
				
				// Récupération de tous les persos du joueur
				$sql = "SELECT id_perso, nom_perso, chef FROM perso WHERE idJoueur_perso='$id_joueur_perso'";
				$res = $mysqli->query($sql);
				
				// init vide
				$nom_perso_chef = "";
				
				?>
				<!-- Début du tableau d'information-->
				<table border=1 align="center" width=90%>
					<tr>
						<td width=120>
							<center>
								<div width=40 height=40 style="position: relative;">
									<div style="position: absolute;bottom: 0;text-align: center; width: 100%;font-weight: bold;">
										<?php echo $id_perso; ?>
									</div>
									<img src="../images_perso/<?php echo "$image_perso";?>" width=40 height=40>
								</div>
							</center>
						</td>
						<td align=center>
							<form method='post' action='jouer.php'>
								<b>Nom : </b><select name='liste_perso'>
						<?php 
						while($t_liste_perso = $res->fetch_assoc()) {
							
							$id_perso_liste 	= $t_liste_perso["id_perso"];
							$nom_perso_liste 	= $t_liste_perso["nom_perso"];
							$chef_perso			= $t_liste_perso["chef"];
							
							if ($chef_perso) {
								$nom_perso_chef = $nom_perso_liste;
							}
							
							echo "<option value='$id_perso_liste'";
							if ($id_perso == $id_perso_liste) {
								echo " selected";
							}
							echo ">$nom_perso_liste [$id_perso_liste]</option>";
						}
						?>
								</select>
								<input type='submit' name='select_perso' value='ok' />
							</form>
						</td>
						<td align=center><b>Grade : </b><?php echo $nom_grade_perso; ?>
							<img alt="<?php echo $nom_grade_perso; ?>" title="<?php echo $nom_grade_perso; ?>" src="../images/grades/<?php echo $id_grade_perso . ".gif";?>" width=40 height=40>
						</td>
						<td align=center><?php $pourc = affiche_jauge($pv_perso, $pvMax_perso); echo "".round($pourc)."% ou $pv_perso/$pvMax_perso"; ?></td>
					</tr>
					<tr>
						<td align=center><b>Chef : </b><?php echo $nom_perso_chef;?></td>
						<td align=center><b>Bataillon : </b><?php echo $bataillon_perso; ?></td>
						<td align=center><b>Compagnie : </b><?php echo ""; ?></td>
						<td align=center><b>Section : </b><?php echo ""; ?></td>
					</tr>
				</table>
				<!--Fin du tableau d'information-->
				
				<center>
					<table border=0 align="center" width=100%>
						<tr>
							<td align="center" width=14%><a href="profil.php" target='_blank'><img width=40 height=50 border=0 src="../images/<?php echo "$image_profil";?>"></a></td>
							<td align="center" width=14%><a href="evenement.php" target='_blank'><img width=83 height=66 border=0 src="../images/evenement2.gif"></a></td>
							<td align="center" width=14%><a href="sac.php" target='_blank'><img width=36 height=50 border=0 src="../images/sac.png"></a></td>
							<td align="center" width=14%><a href="carte2.php" target='_blank'><img width=83 height=83 border=0 src="../images/world.png"></a></td>
							<td align="center" width=14%><a href="messagerie.php" target='_blank'><img width=83 height=75 border=0 src="../images/messagerie2.gif"></a></td>
							<td align="center" width=14%><a href="classement.php" target='_blank'><img width=83 height=58 border=0 src="../images/classement2.gif"></a></td>
							<td align="center" width=14%><a href="section.php" target='_blank'><img width=83 height=83 border=0 src="../images/groupe2.gif"></a></td>
						</tr>
						<tr>
							<td align="center" width=14%><img width=83 height=16 border=0 src="../images/profil_titre.gif"> <?php if($bonus_perso < 0){ echo "<br/><font color=red>( Malus de defense : $bonus_perso )</font>";} ?></td>
							<td align="center" width=14%><img width=83 height=16 border=0 src="../images/evenement_titre.gif"></td>
							<td align="center" width=14%><img width=83 height=16 border=0 src="../images/sac_titre.gif"></td>
							<?php 
							$sql_mes = "SELECT count(id_message) as nb_mes from message_perso where id_perso='$id_perso' and lu_message='0' AND supprime_message='0'";
							$res_mes = $mysqli->query($sql_mes);
							$t_mes = $res_mes->fetch_assoc();
							$nb_nouveaux_mes = $t_mes["nb_mes"];
							?>
							<td align="center" width=14%><img width=83 height=16 border=0 src="../images/carte_titre.gif"></td>
							<td align="center" width=14%><img width=83 height=16 border=0 src="../images/messagerie_titre.gif"> <?php if($nb_nouveaux_mes) { echo "<br/><font color=red>($nb_nouveaux_mes nouveau"; if($nb_nouveaux_mes > 1) echo "x"; echo " message"; if($nb_nouveaux_mes > 1) echo "s"; echo ")</font>"; } ?></td>
							<td align="center" width=14%><img width=83 height=16 border=0 src="../images/classement_titre.gif"></td>
							<td align="center" width=14%><img width=83 height=16 border=0 src="../images/groupe_titre.gif"></td>
						</tr>
						<tr>
							<td colspan='7' align='center'>&nbsp;</td>
						</tr>
						<tr>
							<td colspan='7' align='center'>Rafraîchir la page : <a href='jouer.php'><img border=0 src='../images/refresh.png' alt='refresh' /></a></td>
						</tr>
					</table>
				</center>
				
				<?php
				$erreur .= "</font>";
				echo "<center>".$erreur."</div></center><br>";
				
				if($nb_c = prox_coffre($mysqli, $x_perso, $y_perso)){
					for ($c = 0; $c < $nb_c; $c++) {
						echo "<center><a href=\"jouer.php?coffre=ok\">Ouvrir le coffre</a></center>";
					}
				}
				
				// Récupération de l'arme de CaC équipé sur le perso
				$sql = "SELECT arme.id_arme, nom_arme, porteeMin_arme, porteeMax_arme, coutPa_arme, degatMin_arme, valeur_des_arme, precision_arme, degatZone_arme 
						FROM arme, perso_as_arme
						WHERE arme.id_arme = perso_as_arme.id_arme
						AND porteeMax_arme = 1
						AND perso_as_arme.est_portee = '1'
						AND id_perso = '$id_perso'";
				$res = $mysqli->query($sql);
				$t_cac = $res->fetch_assoc();
				
				if ($t_cac != NULL) {
					$id_arme_cac			= $t_cac["id_arme"];
					$nom_arme_cac 			= $t_cac["nom_arme"];
					$porteeMin_arme_cac 	= $t_cac["porteeMin_arme"];
					$porteeMax_arme_cac 	= $t_cac["porteeMax_arme"];
					$coutPa_arme_cac 		= $t_cac["coutPa_arme"];
					$degatMin_arme_cac 		= $t_cac["degatMin_arme"];
					$valeur_des_arme_cac 	= $t_cac["valeur_des_arme"];
					$precision_arme_cac 	= $t_cac["precision_arme"];
					$degatZone_arme_cac 	= $t_cac["degatZone_arme"];
				} else {
					$id_arme_cac			= 1000;
					$nom_arme_cac 			= "Poings";
					$porteeMin_arme_cac 	= 1;
					$porteeMax_arme_cac 	= 1;
					$coutPa_arme_cac 		= 3;
					$degatMin_arme_cac 		= 4;
					$valeur_des_arme_cac 	= 6;
					$precision_arme_cac 	= 30;
					$degatZone_arme_cac 	= 0;
				}
				
				$degats_arme_cac = $degatMin_arme_cac."D".$valeur_des_arme_cac;
				
				// Récupération de la liste des persos à portée d'attaque arme CaC
				$res_portee_cac = resource_liste_cibles_a_portee_attaque($mysqli, 'carte', $id_perso, $porteeMin_arme_cac, $porteeMax_arme_cac, $perception_perso);
				
				// Récupération de l'arme à distance sur le perso
				$sql = "SELECT arme.id_arme, nom_arme, porteeMin_arme, porteeMax_arme, coutPa_arme, degatMin_arme, valeur_des_arme, precision_arme, degatZone_arme 
						FROM arme, perso_as_arme
						WHERE arme.id_arme = perso_as_arme.id_arme
						AND porteeMax_arme > 1
						AND perso_as_arme.est_portee = '1'
						AND id_perso = '$id_perso'";
				$res = $mysqli->query($sql);
				$t_dist = $res->fetch_assoc();
				
				if ($t_dist != NULL) {
					$id_arme_dist 			= $t_dist["id_arme"];
					$nom_arme_dist 			= $t_dist["nom_arme"];
					$porteeMin_arme_dist 	= $t_dist["porteeMin_arme"];
					$porteeMax_arme_dist 	= $t_dist["porteeMax_arme"];
					$coutPa_arme_dist 		= $t_dist["coutPa_arme"];
					$degatMin_arme_dist 	= $t_dist["degatMin_arme"];
					$valeur_des_arme_dist 	= $t_dist["valeur_des_arme"];
					$precision_arme_dist 	= $t_dist["precision_arme"];
					$degatZone_arme_dist 	= $t_dist["degatZone_arme"];
				} else {
					$id_arme_dist			= 2000;
					$nom_arme_dist 			= "Cailloux";
					$porteeMin_arme_dist 	= 1;
					$porteeMax_arme_dist 	= 2;
					$coutPa_arme_dist 		= 3;
					$degatMin_arme_dist 	= 5;
					$valeur_des_arme_dist 	= 6;
					$precision_arme_dist 	= 25;
					$degatZone_arme_dist 	= 0;
				}
				
				$degats_arme_dist = $degatMin_arme_dist."D".$valeur_des_arme_dist;
				
				// Récupération de la liste des persos à portée d'attaque arme dist
				$res_portee_dist = resource_liste_cibles_a_portee_attaque($mysqli, 'carte', $id_perso, $porteeMin_arme_dist, $porteeMax_arme_dist, $perception_perso);
				
				?>
				<table border=0 align="center" cellspacing="0" cellpadding="10" style:no-padding>
					<tr>
						<td valign="top">
						
							<table style="border:0px; background-color: cornflowerblue; min-width: 375;">
								<tr>
									<td>
										<table border="2" bordercolor="white" > <!-- border-collapse:collapse -->
											<tr>
												<td><b>XP</b></td>
												<td><?php echo $xp_perso; ?>&nbsp;</td>
											</tr>
											<tr>
												<td><b>XPI</b></td>
												<td><?php echo $pi_perso; ?>&nbsp;</td>
											</tr>
											<tr>
												<td><b>PC</b></td>
												<td><?php echo $pc_perso; ?>&nbsp;</td>
											</tr>
										</table>
									</td>
									
									<td>
										<table border="2" bordercolor="white">
											<tr>
												<td><b>Perception</b></td>
												<td align='center'><?php echo $perception_perso; ?>&nbsp;</td>
											</tr>
											<tr>
												<td><b>PA</b></td>
												<td><?php echo $pa_perso . ' / ' . $paMax_perso; ?>&nbsp;</td>
											</tr>
											<tr>
												<td><b>PM</b></td>
												<td><?php echo $pm_perso . ' / ' . $pmMax_perso; ?>&nbsp;</td>
											</tr>
										</table>
									</td>
									
									<td>
										<table border="2" bordercolor="white">
											<tr>
												<td><b>Protection</b></td>
												<td><?php echo $protec_perso; ?>&nbsp;</td>
											</tr>
											<tr>
												<td><b>Recuperation</b></td>
												<td><?php echo $recup_perso; ?>&nbsp;</td>
											</tr>
											<tr>
												<td><b>Malus Defense</b></td>
												<td><?php echo $bonus_perso; ?>&nbsp;</td>
											</tr>
										</table>
									</td>
								</tr>
							</table>
							
							<br />
							
							<table border="2" style="background-color: palevioletred; min-width: 430;">
								<tr>
									<td colspan='3'bgcolor="lightgrey"><center><b>Caractèristiques de combat</b></center></td>
								</tr>
								<tr>
									<td width='20%'></td>
									<?php 
									if ($type_perso != 5) { ?>
									<td width='40%'><center><b>Rapproché</b></center></td>
									<?php } ?>
									<td width='40%'><center><b>A distance</b></center></td>
								</tr>
								<tr>
									<td><b>Armes</b></td>
									<td><center><?php echo $nom_arme_cac; ?></center></td>
									<td><center><?php echo $nom_arme_dist; ?></center></td>
								</tr>
								<tr>
									<td><b>Coût en PA</b></td>
									<td><center><?php echo $coutPa_arme_cac; ?></center></td>
									<td><center><?php echo $coutPa_arme_dist; ?></center></td>
								</tr>
								<tr>
									<td><b>Dégats</b></td>
									<td><center><?php echo $degats_arme_cac; ?></center></td>
									<td><center><?php echo $degats_arme_dist; ?></center></td>
								</tr>
								<tr>
									<td><b>Portée</b></td>
									<td><center><?php echo $porteeMax_arme_cac; ?></center></td>
									<td><center><?php echo $porteeMax_arme_dist; ?></center></td>
								</tr>
								<tr>
									<td><b>Précision</b></td>
									<td><center><?php echo $precision_arme_cac . "%"; ?></center></td>
									<td><center><?php echo $precision_arme_dist . "%"; ?></center></td>
								</tr>
								<tr>
									<form method="post" action="agir.php" target='_main'>
									<td><input type="submit" value="Attaquer"></td>
									<td>
										<select name='id_attaque_cac' style="width: -moz-available;">
											<option value="personne">Personne</option>
											<?php
											while($t_cible_portee_cac = $res_portee_cac->fetch_assoc()) {
												
												$id_cible_cac = $t_cible_portee_cac["idPerso_carte"];
												
												if ($id_cible_cac < 50000) {
													
													// Un autre perso
													$sql = "SELECT nom_perso FROM perso WHERE id_perso='$id_cible_cac'";
													$res = $mysqli->query($sql);
													$tab = $res->fetch_assoc();
													
													$nom_cible_cac = $tab["nom_perso"];
													
												} else if ($id_cible_cac >= 200000) {
													
													// Un PNJ
													$sql = "SELECT nom_pnj FROM pnj, instance_pnj WHERE pnj.id_pnj = instance_pnj.id_pnj AND idInstance_pnj = '$id_cible_cac'";
													$res = $mysqli->query($sql);
													$tab = $res->fetch_assoc();
													
													$nom_cible_cac = $tab["nom_pnj"];
													
												} else {
													
													// Un Batiment
													$sql = "SELECT nom_batiment FROM batiment, instance_batiment WHERE batiment.id_batiment = instance_batiment.id_batiment AND id_instanceBat = '$id_cible_cac'";
													$res = $mysqli->query($sql);
													$tab = $res->fetch_assoc();
													
													$nom_cible_cac = $tab["nom_batiment"];
												}
												
												echo "<option value='".$id_cible_cac.",".$id_arme_cac."'>".$nom_cible_cac." (mat. ".$id_cible_cac.")</option>";
											}
											?>
										</select>
									</td>
									<td>
										<select name='id_attaque_dist' style="width: -moz-available;">
											<option value="personne">Personne</option>
											<?php
											while($t_cible_portee_dist = $res_portee_dist->fetch_assoc()) {
												
												$id_cible_dist = $t_cible_portee_dist["idPerso_carte"];
												
												if ($id_cible_dist < 50000) {

													// Un autre perso
													$sql = "SELECT nom_perso FROM perso WHERE id_perso='$id_cible_dist'";
													$res = $mysqli->query($sql);
													$tab = $res->fetch_assoc();
													
													$nom_cible_dist = $tab["nom_perso"];
													
												} else if ($id_cible_dist >= 200000) {
													
													// Un PNJ
													$sql = "SELECT nom_pnj FROM pnj, instance_pnj WHERE pnj.id_pnj = instance_pnj.id_pnj AND idInstance_pnj = '$id_cible_dist'";
													$res = $mysqli->query($sql);
													$tab = $res->fetch_assoc();
													
													$nom_cible_dist = $tab["nom_pnj"];
												} else {
													
													// Un Batiment
													$sql = "SELECT nom_batiment FROM batiment, instance_batiment WHERE batiment.id_batiment = instance_batiment.id_batiment AND id_instanceBat = '$id_cible_dist'";
													$res = $mysqli->query($sql);
													$tab = $res->fetch_assoc();
													
													$nom_cible_dist = $tab["nom_batiment"];
												}
												
												echo "<option value='".$id_cible_dist.",".$id_arme_dist."'>".$nom_cible_dist." (mat. ".$id_cible_dist.")</option>";
											}
											?>
										</select>
									</td>
									</form>
								</tr>
							</table>
					
						</td>
						
						<td valign="top">
							<table style="border:1px solid black; border-collapse: collapse;">
								<tr>
									<td>
				
				<?php
				//<!--Génération de la carte-->
				
				// recuperation des données de la carte
				$sql = "SELECT x_carte, y_carte, fond_carte, occupee_carte, image_carte, idPerso_carte FROM $carte WHERE x_carte >= $x_perso - $perc AND x_carte <= $x_perso + $perc AND y_carte <= $y_perso + $perc AND y_carte >= $y_perso - $perc ORDER BY y_carte DESC, x_carte";
				$res = $mysqli->query($sql);
				$tab = $res->fetch_assoc();		
				
				// calcul taille table
				$taille_table = ($perception_perso + $bonusPerception_perso) * 2 + 2;
				$taille_table = $taille_table * 40;
				
				echo "<table border=0 width=\"$taille_table\" height=\"$taille_table\" align=\"center\" cellspacing=\"0\" cellpadding=\"0\" style:no-padding>";
				
				//affichage des abscisses
				echo "	<tr>
							<td width='40' heigth='40' background=\"../images/background.jpg\" align='center'>y \ x</td>";  
				
				for ($i = $x_perso - $perc; $i <= $x_perso + $perc; $i++) {
					if ($i == $x_perso)
						echo "<th width=40 height=40 background=\"../images/background3.jpg\">$i</th>";
					else
						echo "<th width=40 height=40 background=\"../images/background.jpg\">$i</th>";
				}
				
				echo "	</tr>";
				
				for ($y = $y_perso + $perc; $y >= $y_perso - $perc; $y--) {
					
					echo "<tr align=\"center\" >";
					
					if ($y == $y_perso) {
						echo "<th width=40 height=40 background=\"../images/background3.jpg\">$y</th>";
					}
					else {
						echo "<th width=40 height=40 background=\"../images/background.jpg\">$y</th>";
					}
					
					for ($x = $x_perso - $perc; $x <= $x_perso + $perc; $x++) {
						
						//les coordonnées sont dans les limites
						if ($x >= X_MIN && $y >= Y_MIN && $x <= $X_MAX && $y <= $Y_MAX) { 
						
							//coordonnées du perso
							if ($x == $x_perso && $y == $y_perso){ 
								echo "<td width=40 height=40 background=\"../fond_carte/".$tab["fond_carte"]."\">";
								echo "	<div width=40 height=40 style=\"position: relative;\">";
								echo "		<div style=\"position: absolute;bottom: 0;text-align: center; width: 100%;font-weight: bold;\">" . $id_perso . "</div>";
								echo "		<img class=\"\" border=0 src=\"../images_perso/$image_perso\" width=40 height=40 />";
								echo "	</div>";
								echo "</td>";
							}
							else {
								if ($tab["occupee_carte"]){
									if($tab['image_carte'] == "coffre1t.png") {
										// positionement du coffre present
										echo "<td width=40 height=40 background=\"../fond_carte/".$tab["fond_carte"]."\"> <a href=\"jouer.php?coffre=ok\"><img border=0 src=\"../images_perso/".$tab["image_carte"]."\" width=40 height=40\" title=\"coffre fermé\"></a></td>";
									}
									else {
										if($tab['image_carte'] == "coffre2t.png") {
											//positionement du coffre present
											echo "<td width=40 height=40 background=\"../fond_carte/".$tab["fond_carte"]."\"><img border=0 src=\"../images_perso/".$tab["image_carte"]."\" width=40 height=40\" title=\"coffre ouvert\"></a></td>";
										}
										else{
											// recuperation de l'image du pnj
											if($tab['idPerso_carte'] >= 200000){
												
												$idI_pnj = $tab['idPerso_carte'];
												
												// recuperation du type de pnj
												$sql_im = "SELECT id_pnj FROM instance_pnj WHERE idInstance_pnj='$idI_pnj'";
												$res_im = $mysqli->query($sql_im);
												$t_im = $res_im->fetch_assoc();
												
												$id_pnj_im = $t_im["id_pnj"];
												$im_pnj="pnj".$id_pnj_im."t.png";
												
												$dossier_pnj = "images/pnj";
	
												echo "	<td width=40 height=40 background=\"../fond_carte/".$tab["fond_carte"]."\"> 
															<a href=\"jouer.php?infoid=".$tab["idPerso_carte"]."\"><img border=0 src=\"../".$dossier_pnj."/".$tab["image_carte"]."\" width=40 height=40 title=\"pnj mat ".$tab["idPerso_carte"]."\"></a>
														</td>";
											}
											else{
												//  traitement Batiment
												if($tab['idPerso_carte'] >= 50000 && $tab['idPerso_carte'] < 200000){
													
													$idI_bat = $tab['idPerso_carte'];
													
													// recuperation du type de bat et du camp
													$sql_im = "SELECT id_batiment, camp_instance FROM instance_batiment WHERE id_instanceBat='$idI_bat'";
													$res_im = $mysqli->query($sql_im);
													$t_im = $res_im->fetch_assoc();
													
													$type_bat = $t_im["id_batiment"];
													$camp_bat = $t_im["camp_instance"];
													
													if($camp_bat == '1'){
														$camp_bat2 = 'bleu';
													}
													if($camp_bat == '2'){
														$camp_bat2 = 'rouge';
													}
													
													$blason="mini_blason_".$camp_bat2.".gif";
		
													echo "<td width=40 height=40 background=\"../fond_carte/".$tab["fond_carte"]."\"> <a href=\"jouer.php?infoid=".$tab["idPerso_carte"]."\"><img border=0 src=\"../images_perso/".$tab["image_carte"]."\" width=40 height=40 onMouseOver=\"AffBulle('<img src=../images/$blason>')\" onMouseOut=\"HideBulle()\" title=\"batiment mat ".$tab["idPerso_carte"]."\"></a></td>";
												}
												else {
											
													if($tab['image_carte'] == "murt.png"){
														//positionement du mur
														echo "<td width=40 height=40 background=\"../fond_carte/".$tab["fond_carte"]."\"> <img border=0 src=\"../images_perso/".$tab["image_carte"]."\" width=40 height=40 onMouseOver=\"AffBulle('<img src=../images/murs/mur.jpeg>')\" onMouseOut=\"HideBulle()\" title=\"mur\"></td>";
													}
													else {
														
														$id_perso_im = $tab['idPerso_carte'];
														
														//recuperation du type de perso (image)
														$sql_perso_im ="SELECT * FROM perso WHERE id_perso='$id_perso_im'";
														$res_perso_im = $mysqli->query($sql_perso_im);
														$t_perso_im = $res_perso_im->fetch_assoc();
														
														$im_perso 	= $t_perso_im["image_perso"];
														$nom_ennemi = $t_perso_im['nom_perso'];
														$id_ennemi 	= $t_perso_im['id_perso'];
														$clan_e 	= $t_perso_im['clan'];
														
														if($clan_e == 1){
															$clan_ennemi 	= 'rond_b.png';
															$couleur_clan_e = 'blue';
															$image_profil 	= "nord.gif";
														}
														if($clan_e == 2){
															$clan_ennemi 	= 'rond_r.png';
															$couleur_clan_e = 'red';
															$image_profil 	= "sud.gif";
														}
														
														// recuperation de l'id de la section 
														$sql_groupe = "SELECT id_section from perso_in_section where id_perso='$id_perso_im' and attenteValidation_section='0'";
														$res_groupe = $mysqli->query($sql_groupe);
														$t_groupe = $res_groupe->fetch_assoc();
														
														$id_groupe = $t_groupe['id_section'];
														
														if(isset($groupe)){
															$groupe = '';
														}
														
														if(isset($id_groupe) && $id_groupe != ''){
															// recuperation des infos sur la section (dont le nom)
															$sql_groupe2 = "SELECT * FROM sections WHERE id_section='$id_groupe'";
															$res_groupe2 = $mysqli->query($sql_groupe2);
															$t_groupe2 = $res_groupe2->fetch_assoc();
															$groupe = addslashes($t_groupe2['nom_section']);
														}
														
														if(isset($groupe) && $groupe != ''){
															echo "<td width=40 height=40 background=\"../fond_carte/".$tab["fond_carte"]."\"><a href=\"jouer.php?infoid=".$tab["idPerso_carte"]."\">";
															echo "	<div width=40 height=40 style=\"position: relative;\">";
															echo "		<div style=\"position: absolute;bottom: 0;text-align: center; width: 100%;font-weight: bold;\">" . $id_ennemi . "</div>";
															echo "		<img class=\"\" border=0 src=\"../images_perso/".$tab["image_carte"]."\" width=40 height=40 /></a>";
															echo "	</div>";
															echo "</td>";
														}
														else {
															echo "<td width=40 height=40 background=\"../fond_carte/".$tab["fond_carte"]."\"><a href=\"jouer.php?infoid=".$tab["idPerso_carte"]."\">";
															echo "	<div width=40 height=40 style=\"position: relative;\">";
															echo "		<div style=\"position: absolute;bottom: 0;text-align: center; width: 100%;font-weight: bold;\">" . $id_ennemi . "</div>";
															echo "		<img class=\"\" border=0 src=\"../images_perso/".$tab["image_carte"]."\" width=40 height=40 /></a>";
															echo "	</div>";
															echo "</td>";
														}
													}
												}
											}										
										}
									}
								}
								else {
									
									// verification s'il y a un objet sur cette case
									$sql_o = "SELECT id_objet FROM objet_in_carte WHERE x_carte='$x' AND y_carte='$y'";
									$res_o = $mysqli->query($sql_o);
									$nb_o = $res_o->num_rows;
									
									if($y > $y_perso+1 || $y < $y_perso-1 || $x > $x_perso+1 || $x < $x_perso-1) {
										if($nb_o){
											echo "<td width=40 height=40 background=\"../fond_carte/".$tab["fond_carte"]."\"><img border=0 src=\"../fond_carte/o1.gif\" width=40 height=40 title='objets à ramasser'/></td>";
										}
										else {										
											echo "<td width=40 height=40> <img border=0 src=\"../fond_carte/".$tab["fond_carte"]."\" width=40 height=40></td>";
										}
									}
									else {
										if($y == $y_perso+1 && $x == $x_perso+1){
											if($nb_o){
												echo "<td width=40 height=40 background=\"../fond_carte/".$tab["fond_carte"]."\"><a href=\"jouer.php?mouv=3\"><img border=0 src=\"../fond_carte/o1.gif\" width=40 height=40 title='objets à ramasser' /></a></td>";
											}
											else {	
												echo "<td width=40 height=40> <a href=\"jouer.php?mouv=3\"><img border=0 src=\"../fond_carte/".$tab["fond_carte"]."\" width=40 height=40></a></td>";//positionnement du fond
											}
										}
										if($y == $y_perso-1 && $x == $x_perso+1){
											if($nb_o){
												echo "<td width=40 height=40 background=\"../fond_carte/".$tab["fond_carte"]."\"><a href=\"jouer.php?mouv=8\"><img border=0 src=\"../fond_carte/o1.gif\" width=40 height=40 title='objets à ramasser' /></a></td>";
											}
											else {		
												echo "<td width=40 height=40> <a href=\"jouer.php?mouv=8\"><img border=0 src=\"../fond_carte/".$tab["fond_carte"]."\" width=40 height=40></a></td>";//positionnement du fond
											}
										}
										if($y == $y_perso && $x == $x_perso+1){
											if($nb_o){
												echo "<td width=40 height=40 background=\"../fond_carte/".$tab["fond_carte"]."\"><a href=\"jouer.php?mouv=5\"><img border=0 src=\"../fond_carte/o1.gif\" width=40 height=40 title='objets à ramasser' /></a></td>";
											}
											else {	
												echo "<td width=40 height=40> <a href=\"jouer.php?mouv=5\"><img border=0 src=\"../fond_carte/".$tab["fond_carte"]."\" width=40 height=40></a></td>";//positionnement du fond
											}
										}
										if($y == $y_perso && $x == $x_perso-1) {
											if($nb_o){
												echo "<td width=40 height=40 background=\"../fond_carte/".$tab["fond_carte"]."\"><a href=\"jouer.php?mouv=4\"><img border=0 src=\"../fond_carte/o1.gif\" width=40 height=40 title='objets à ramasser' /></a></td>";
											}
											else {	
												echo "<td width=40 height=40> <a href=\"jouer.php?mouv=4\"><img border=0 src=\"../fond_carte/".$tab["fond_carte"]."\" width=40 height=40></a></td>";//positionnement du fond
											}
										}
										if($y == $y_perso+1 && $x == $x_perso-1) {
											if($nb_o){
												echo "<td width=40 height=40 background=\"../fond_carte/".$tab["fond_carte"]."\"><a href=\"jouer.php?mouv=1\"><img border=0 src=\"../fond_carte/o1.gif\" width=40 height=40 title='objets à ramasser' /></a></td>";
											}
											else {	
												echo "<td width=40 height=40> <a href=\"jouer.php?mouv=1\"><img border=0 src=\"../fond_carte/".$tab["fond_carte"]."\" width=40 height=40></a></td>";//positionnement du fond
											}
										}
										if($y == $y_perso-1 && $x == $x_perso-1) {
											if($nb_o){
												echo "<td width=40 height=40 background=\"../fond_carte/".$tab["fond_carte"]."\"><a href=\"jouer.php?mouv=6\"><img border=0 src=\"../fond_carte/o1.gif\" width=40 height=40 title='objets à ramasser' /></a></td>";
											}
											else {	
												echo "<td width=40 height=40> <a href=\"jouer.php?mouv=6\"><img border=0 src=\"../fond_carte/".$tab["fond_carte"]."\" width=40 height=40></a></td>";//positionnement du fond
											}
										}
										if($y == $y_perso+1 && $x == $x_perso) {
											if($nb_o){
												echo "<td width=40 height=40 background=\"../fond_carte/".$tab["fond_carte"]."\"><a href=\"jouer.php?mouv=2\"><img border=0 src=\"../fond_carte/o1.gif\" width=40 height=40 title='objets à ramasser' /></a></td>";
											}
											else {	
												echo "<td width=40 height=40> <a href=\"jouer.php?mouv=2\"><img border=0 src=\"../fond_carte/".$tab["fond_carte"]."\" width=40 height=40></a></td>";//positionnement du fond
											}
										}
										if($y == $y_perso-1 && $x == $x_perso) {
											if($nb_o){
												echo "<td width=40 height=40 background=\"../fond_carte/".$tab["fond_carte"]."\"><a href=\"jouer.php?mouv=7\"><img border=0 src=\"../fond_carte/o1.gif\" width=40 height=40 title='objets à ramasser' /></a></td>";
											}
											else {	
												echo "<td width=40 height=40> <a href=\"jouer.php?mouv=7\"><img border=0 src=\"../fond_carte/".$tab["fond_carte"]."\" width=40 height=40></a></td>";//positionnement du fond
											}
										}
									}
								}
							}
							$tab = $res->fetch_assoc();
						}
						else //les coordonnées sont hors limites
							echo "<td width=40 height=40><img border=0 width=40 height=40 src=\"../fond_carte/decorO.jpg\"></td>";
					}
					echo "</tr>";
				}
				?>
								</table>
							</td>
						</tr>
					</table>
				</td>
				<!--Fin de la génération de la carte-->
				
				<?php
				if($config == '2'){
					echo "</tr><tr>";
				}
				?>
				
				<!--Debut tableau des actions -->
				<td valign="top">
					<table style="border:1px solid black; border-collapse: collapse;">
						<tr>
							<td>
								<table border="0" cellspacing="0" cellpadding="0" style:no-padding>
									<tr>
										<td background='../images/background.jpg' align='center' valign='top'colspan='2'>
											<img src='../images/Action.png' border='0'/>
											<form method='post' action='action.php'>
												<select name='liste_action'>
													<option value="invalide">-- -- -- -- -- -- - Choisir une action - -- -- -- -- -- --</option>
													<?php
													
													// Action d'entrainement
													if($pa_perso < 10){
														echo "<option value=\"PA\">* Entrainement (10 pa)</option>";
														echo "<option value=\"PA\">* Se reposer (10 pa)</option>";
													}
													else {
														echo "<option value=\"65\">Entrainement (10 pa)</option>";
														echo "<option value=\"1\">Se reposer (10 pa)</option>";
													}
													
													// Action Déposer Objet
													if($pa_perso < 1){
														echo "<option value=\"PA\">* Deposer objet (1 pa)</option>";
														echo "<option value=\"PA\">* Donner objet (1 pa)</option>";
													}
													else {
														echo "<option value=\"110\">Deposer objet (1 pa)</option>";
														echo "<option value=\"139\">Donner objet (1 pa)</option>";
													}
													
													// Actions selon le type d'unité
													// Soigneurs
													if ($type_perso == 4) {
														// Soin = 11
														if($pa_perso < 6){
															echo "<option value=\"PA\">Soin (6 pa)</option>";
														}
														else {
															echo "<option value=\"65\">Soin (6 pa)</option>";
														}
													}
													
													// Infanterie et soigneur
													if ($type_perso == 3 || $type_perso == 4) {
														// Bousculer = 145
														if($pa_perso < 3){
															echo "<option value=\"PA\">Bousculer (3 pa)</option>";
														}
														else {
															echo "<option value=\"145\">Bousculer (3 pa)</option>";
														}
													}
													
													// Cavalerie et cavalerie lourde
													if ($type_perso == 1 || $type_perso == 2) {
														// Charge = 999
														echo "<option value=\"999\">Charger (0 pa)</option>";
														
														// Bousculer = 145
														if($pa_perso < 3){
															echo "<option value=\"PA\">Bousculer (3 pa)</option>";
														}
														else {
															echo "<option value=\"145\">Bousculer (3 pa)</option>";
														}
													}
													
													// verification s'il y a un objet sur la case du perso
													$sql_op = "SELECT id_objet FROM objet_in_carte, perso WHERE x_carte=x_perso AND y_carte=y_perso";
													$res_op = $mysqli->query($sql_op);
													$nb_op = $res_op->num_rows;
													if($nb_op){
														// Action Ramasser Objet
														if($pa_perso < 1){
															echo "<option value=\"PA\">* Ramasser objet (1 point - 1 pa)</option>";
														}
														else {
															echo "<option value=\"111\">Ramasser objet (1 point - 1 pa)</option>";
														}
													}
													
													$sql = "SELECT action.id_action, nom_action, coutPa_action
															FROM perso_as_competence, competence_as_action, action 
															WHERE id_perso='$id_perso' 
															AND perso_as_competence.id_competence=competence_as_action.id_competence 
															AND competence_as_action.id_action=action.id_action
															AND passif_action = '0'
															ORDER BY nom_action";
													$res = $mysqli->query($sql);
													
													while ($t_ac = $res->fetch_assoc()) {
														
														$id_ac 		= $t_ac["id_action"];
														$cout_PA 	= $t_ac["coutPa_action"];
														$nom_ac 	= $t_ac["nom_action"];
													
														if ($cout_PA == -1){
															$cout_PA = $paMax_perso;
														}
														
														if ($cout_PA <= $pa_perso){
															echo "<option value=\"$id_ac\">".$nom_ac." (";
														}
														else {
															echo "<option value=\"PA\">* ".$nom_ac." (";
														}
														echo "". $cout_PA . "pa)</option>";
													}
													?>
													<option value="invalide">-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- --</option>
												</select>
												<input type='submit' name='action' value='ok' />
											</form>
											<?php echo $mess_bat; ?>
										</td>
									</tr>
									<tr>
										<td height='5' background='../images/background.jpg' colspan='2' align='center'>
											<img src='../images/barre.png' />
										</td>
									</tr>
									<?php // recuperation des infos du perso choisit
									if (isset($_GET["infoid"]) && $_GET["infoid"] < 10000) {
										
										$infoid = $_GET["infoid"];
										$sql = "SELECT nom_perso, message_perso FROM perso WHERE ID_perso='$infoid'";
										$res = $mysqli->query($sql);
										$tab = $res->fetch_assoc();
										
										$mess_infoid = stripslashes($tab["message_perso"]);
										$nom_infoid = $tab["nom_perso"];
									}?>
									<tr>
										<td background='../images/background.jpg'>
											<table border='0'>
												<tr>
													<td>
														<img src='../images/Pseudo.png' />
													</td>
													<td>
														<img src='../images/Id.png' />
													</td>
												</tr>
												<tr>
													<td valign='top'>
														<input type="text" value="<?php if (isset($_GET["infoid"]) && $_GET["infoid"] < 10000) echo $nom_infoid;?>" style=background-image:url('../images/background3.jpg');>
													</td>
													<td valign='top'>
														<form method="post" action="evenement.php?infoid=<?php if (isset($_GET["infoid"]) && $_GET["infoid"] < 10000) echo $infoid; elseif(isset($_GET["infoid"]) && $_GET["infoid"] >= 10000) echo $_GET["infoid"];?>" target='_blank'>
															<input type="text" maxlength="6" size="6" name="id_info" value="<?php if (isset($_GET["infoid"]) && $_GET["infoid"] < 10000) echo $infoid; elseif(isset($_GET["infoid"]) && $_GET["infoid"] >= 10000) echo $_GET["infoid"];?>" style="background-image:url('../images/background3.jpg');">
															<input type="submit" value="Plus d'infos">
														</form>
													</td>
												</tr>
												<tr>
													<td valign='top'>
													<?php
														if (isset($_GET["infoid"]) && $_GET["infoid"] < 10000) {
															echo "<a href=\"nouveau_message.php?pseudo=$nom_infoid\" target='_blank'>";
															echo "<img src=\"../images/msg.gif\" style=\"vertical-align:middle; margin-left:-5\" border='0' width='25' height='25'>";
															echo "Envoyer un message</a>";
														}
														?>
													</td>
												</tr>
												<tr>
													<td>
														<img src='../images/Message.png' />
													</td>
													<td align='center'>
														<img src='../images/Competences.png' />
													</td>
												</tr>
												<tr>
													<td>
														<TEXTAREA style=background-image:url('../images/background3.jpg');><?php if (isset($_GET["infoid"]) && $_GET["infoid"] < 10000) echo br2nl2($mess_infoid); ?></TEXTAREA>
													</td>
													<td align='center'>
														<a href='competence.php' target='_blank'><img src='../images/logo_competence.gif' alt='competence' width='75' height='75' border='0' /></a>	
													</td>
												</tr>
											</table>
										</td>
									</tr>
									<tr>
										<td background='../images/background.jpg' align='left' colspan='2'>
											<?php 
											echo "<a href=\"nouveau_message.php?visu=ok\" target='_blank'><img src='../images/Ecrire.png' border=0 /><img src='../images/Envoyer_message.png' border=0 />";
											?>
										</td>
									</tr>
									<tr>
										<td background='../images/background.jpg' colspan='2' align='center'>
											<img src='../images/barre.png' />
										</td>
									</tr>
									<tr>
										<td background='../images/background.jpg'>
										<!--Création du tableau du choix du deplacement-->
										<table border=0 align='center'>
											<tr>
												<td colspan='5' align='center'>
												<img src='../images/Se_Deplacer.png' />
												</td>
											</tr>
											<form action="jouer.php" method="get">  
											<tr>
												<td rowspan='3'><img src='../images/tribal1.png' /></td>
												<td><a href="jouer.php?mouv=1"><img border=0 src="../fond_carte/fleche1.png"></a></td>
												<td><a href="jouer.php?mouv=2"><img border=0 src="../fond_carte/fleche2.png"></a></td>
												<td><a href="jouer.php?mouv=3"><img border=0 src="../fond_carte/fleche3.png"></a></td>
												<td rowspan='3'><img src='../images/tribal2.png' /></td>
											</tr>
											<tr>
												<td><a href="jouer.php?mouv=4"><img border=0 src="../fond_carte/fleche4.png"></a></td>
												<td>&nbsp; </td>
												<td><a href="jouer.php?mouv=5"><img border=0 src="../fond_carte/fleche5.png"></a></td>
											</tr>
											<tr>
												<td><a href="jouer.php?mouv=6"><img border=0 src="../fond_carte/fleche6.png"></a></td>
												<td><a href="jouer.php?mouv=7"><img border=0 src="../fond_carte/fleche7.png"></a></td>
												<td><a href="jouer.php?mouv=8"><img border=0 src="../fond_carte/fleche8.png"></a></td>
											</tr>
											</form>
										</table>
										<!--Fin du tableau du choix du deplacement-->
										</td>
									</tr>
								</table>
							</tr>
						</td>
					</table>
				</td>
			</tr>
		</table>
	<?php
			}
		}
	}
	else {
		header("Location: ../index.php");
	}
	?>
			<table border='0'>
				<tr>
					<td height='100'>&nbsp;</td>
				</tr>
			</table>
			
		</body>
	</html>
<?php
}
else {
	// logout
	$_SESSION = array(); // On écrase le tableau de session
	session_destroy(); // On détruit la session
	
	header("Location: ../index2.php");
}
?>
