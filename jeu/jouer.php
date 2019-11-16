<?php
@session_start();

require_once("../fonctions.php");
require_once("f_carte.php");
require_once("f_combat.php");

$mysqli = db_connexion();

include ('../nb_online.php');

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
	
		$sql = "SELECT pv_perso, UNIX_TIMESTAMP(DLA_perso) as DLA, est_gele FROM perso WHERE ID_perso='$id_perso'";
		$res = $mysqli->query($sql);
		$tpv = $res->fetch_assoc();
		$testpv = $tpv['pv_perso'];
		$dla = $tpv["DLA"];
		$est_gele = $tpv["est_gele"];
		
		$config = '1';
		
		// verification si le perso est encore en vie
		if ($testpv <= 0) { // le perso est mort
			//tour.php se charge de verifier si nouveau tour
			header("Location: ../tour.php"); 
		}
		else { // le perso est vivant	
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
	
	<SCRIPT LANGUAGE="JavaScript" SRC="javascript/infobulle.js"></script>
	<SCRIPT language="JavaScript">
	InitBulle("#000000","#f4f4f4","000000",1);
	// InitBulle(couleur de texte, couleur de fond, couleur de contour taille contour)
	</SCRIPT>
				
				<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
				<html>
				<head>
				<title>Nord VS Sud</title>
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
				<link href="../style2.css" rel="stylesheet" type="text/css">
				</head>
	
				<script src="fr.js" type=text/javascript></script>
				<body onload="start()">
				<?php
				
				// recuperation des anciennes données du perso
				$sql = "SELECT nom_perso, x_perso, y_perso, pm_perso, image_perso, pa_perso, recup_perso, bonusRecup_perso, bonusPM_perso, paMax_perso, pv_perso, deAttaque_perso, deDefense_perso, DLA_perso, clan, changementDe_perso FROM perso WHERE id_perso='$id_perso'";
				$res = $mysqli->query($sql);
				$t_perso1 = $res->fetch_assoc();
				$nom_perso = $t_perso1["nom_perso"];
				$x_persoN = $t_perso1["x_perso"];
				$y_persoN = $t_perso1["y_perso"];
				$pm_perso = $t_perso1["pm_perso"];
				$n_dla = $t_perso1["DLA_perso"];
				$image_perso = $t_perso1["image_perso"];
				$deAttaque_p = $t_perso1["deAttaque_perso"];
				$deDefense_p = $t_perso1["deDefense_perso"];
				$changementDe_p = $t_perso1["changementDe_perso"];
				$bonusPM_perso_p = $t_perso1["bonusPM_perso"];
				$clan_p = $t_perso1["clan"];
				
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
				if(isset($_GET["bat"])){
					$id_inst = $_GET["bat"];
					if(isset($_GET["out"]) && $_GET["out"] == "ok"){ // on veut sortir du batiment
					
						// verification que le perso est bien dans un batiment...
						if(in_bat($mysqli, $id_perso)){
							
							// verification des pm du perso
							if($pm_perso + $malus_pm >= 1){
								$oc = 1;
								$seek = 1;
								while ($oc != 0){ // tant que les cases sont occupees
									// recuperation des coordonnees des cases et de leur etat (occupee ou non)
									$sql = "SELECT occupee_carte, x_carte, y_carte, fond_carte FROM $carte WHERE x_carte >= $x_persoN - $seek AND x_carte <= $x_persoN + $seek AND y_carte >= $y_persoN - $seek AND y_carte <= $y_persoN + $seek";
									$res = $mysqli->query($sql);
									while($t = $res->fetch_assoc()){
										$oc = $t["occupee_carte"];
										$xs = $t["x_carte"];
										$ys = $t["y_carte"];
										$fond_c = $t["fond_carte"];
										if($oc == 0)
											break;
									}
									$seek++; // on elargie la recherche
								}
								
								// mise a jour des coordonnees du perso et de ses pm
								$sql = "UPDATE perso SET x_perso = '$xs', y_perso = '$ys', pm_perso=pm_perso-1 WHERE id_perso = '$id_perso'";
								$mysqli->query($sql);
								
								$x_persoN = $xs;
								$y_persoN = $ys;
								
								// verification si le perso a des pnj
								/*
								if (possede_animaux($id_perso)){
									// maj des coordonnées des pnj
									$sql = "UPDATE instance_pnj,perso_as_pnj SET x_pnj=$xs, y_pnj=$ys WHERE perso_as_pnj.id_instance_pnj=instance_pnj.id_instance_pnj";
									$mysqli->query($sql);
								}
								*/
								
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
								if(bourre($id_perso)){
									if(!endurance_alcool($id_perso))
										$malus_bourre = bourre($id_perso) * 2;
										$bonus_visu -= $malus_bourre;
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
					else { // on veut rentrer dans le batiment
					
						// traitement du cas tour de visu et de la tour de garde où il ne peut y avoir qu'un seul perso dedans !
						if(isset($_GET["bat2"]) && ($_GET["bat2"] == 2 || $_GET["bat2"] == 3) && isset($_GET["bat"]) && $_GET["bat"]!=""){
						
							// Vérification que le perso soit pas déjà dans un bâtiment
							if(!in_bat($mysqli, $id_perso)){
						
								// verification que l'instance du batiment existe
								if (existe_instance_bat($_GET["bat"])){
								
									if(verif_bat_instance($_GET["bat2"],$_GET["bat"])){
							
										// verification qu'on soit bien à côté du batiment
										if(prox_instance_bat($x_persoN,$y_persoN,$_GET["bat"])){
										
											// verification si il y a un perso dans la tour
											$sql = "SELECT id_perso FROM perso_in_batiment WHERE id_instanceBat=".$_GET["bat"]."";
											$res = $mysqli->query($sql);
											$nbp = $res->fetch_row();
											if($nbp[0] != 0){ // si la tour est occupee
												$erreur .= "Vous ne pouvez pas entrée, la tour est déjà occupée";
											}
											else { // la tour est vide
											
												// verification que le perso a encore des pm
												if($pm_perso + $malus_pm >= 1){
												
													// recuperation des coordonnees et infos du batiment dans lequel le perso entre
													$sql = "SELECT nom_instance, niveau_instance, x_instance, y_instance FROM instance_batiment WHERE id_instanceBat=".$_GET["bat"]."";
													$res = $mysqli->query($sql);
													$coordonnees_instance = $res->fetch_assoc();
													$x_bat = $coordonnees_instance["x_instance"];
													$y_bat = $coordonnees_instance["y_instance"];
													$nom_bat = $coordonnees_instance["nom_instance"];
													$niveau_instance = $coordonnees_instance["niveau_instance"];
													$id_inst_bat = $_GET["bat"];
													
													// verification si le perso est de la même nation ou non que le batiment
													if(!nation_perso_bat($id_perso,$id_inst_bat)) { // pas même nation
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
													$sql = "UPDATE $carte SET occupee_carte='0' WHERE x_carte='$x_persoN' AND y_carte='$y_persoN'";
													$res = $mysqli->query($sql);
														
													// verification si le perso a des pnj
													/*
													if (possede_animaux($id)){
														// maj des coordonnées des pnj
														$sql = "UPDATE instance_pnj,perso_as_pnj SET x_pnj=$x_persoN, y_pnj=$y_persoN WHERE perso_as_pnj.id_instance_pnj=instance_pnj.id_instance_pnj";
														$mysqli->query($sql);
													}
													*/
														
													// mise a jour des coordonnées du perso
													$sql = "UPDATE perso SET x_perso='$x_bat', y_perso='$y_bat', pm_perso=pm_perso-1 WHERE id_perso='$id_perso'";
													$res = $mysqli->query($sql);
														
													// insertion du perso dans la table perso_in_batiment
													$sql = "INSERT INTO `perso_in_batiment` VALUES ('$id_perso','$id_inst_bat')";
													$mysqli->query($sql);
													echo"<font color = blue>vous êtes entrée dans le batiment $id_inst_bat</font><br>";
														
													// mise a jour table evenement
													$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ('$id_perso','<font color=$couleur_clan_p>$nom_perso</font>','est entre dans le batiment $nom_bat $id_inst_bat',NULL,'','en $x_bat/$y_bat',NOW(),'0')";
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
													if(bourre($id_perso)){
														if(!endurance_alcool($id_perso))
															$malus_bourre = bourre($id_perso) * 2;
															$bonus_visu -= $malus_bourre;
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
									if (existe_instance_bat($_GET["bat"])){
										
										if(verif_bat_instance($_GET["bat2"],$_GET["bat"])){
										
											// verification qu'on soit bien à côté du batiment
											if(prox_instance_bat($x_persoN,$y_persoN,$_GET["bat"])){
												
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
													$x_bat = $coordonnees_instance["x_instance"];
													$y_bat = $coordonnees_instance["y_instance"];
													$nom_bat = $coordonnees_instance["nom_instance"];
													$id_inst_bat = $coordonnees_instance["id_instanceBat"];
													$contenance_inst_bat = $coordonnees_instance["contenance_instance"];
													
													// verification contenance batiment
													if($nb_perso_bat < $contenance_inst_bat){
													
														// verification si le perso est de la même nation que le batiment
														if(!nation_perso_bat($id_perso,$id_inst_bat)) { // pas même nation
															// verification que le batiment est vide
															if(batiment_vide($id_inst_bat)) {
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
														$sql = "UPDATE $carte SET occupee_carte='0' WHERE x_carte='$x_persoN' AND y_carte='$y_persoN'";
														$res = $mysqli->query($sql);
														
														// mise a jour des coordonnées du perso
														$sql = "UPDATE perso SET x_perso='$x_bat', y_perso='$y_bat' WHERE id_perso='$id_perso'";
														$res = $mysqli->query($sql);
														
														// verification si le perso a des pnj
														/*
														if (possede_animaux($id_perso)){
															// maj des coordonnées des pnj
															$sql = "UPDATE instance_pnj,perso_as_pnj SET x_pnj='$x_bat', y_pnj='$y_bat' WHERE perso_as_pnj.id_instance_pnj=instance_pnj.id_instance_pnj";
															$mysqli->query($sql);
														}
														*/
														
														// insertion du perso dans la table perso_in_batiment
														$sql = "INSERT INTO `perso_in_batiment` VALUES ('$id_perso','$id_inst_bat')";
														$mysqli->query($sql);
														echo"<font color = blue>vous êtes entrée dans le batiment $nom_bat</font>";
														
														// mise a jour table evenement
														$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ('$id_perso','<font color=$couleur_clan_p>$nom_perso</font>','est entre dans le batiment $nom_bat $id_inst_bat',NULL,'','en $x_bat/$y_bat',NOW(),'0')";
														$mysqli->query($sql);
														
														$bonus_perc = 0;
														
														// mise a jour du bonus de perception du perso
														$bonus_visu = $bonus_perc;
														if(bourre($id_perso)){
															if(!endurance_alcool($id_perso))
																$malus_bourre = bourre($id_perso) * 2;
																$bonus_visu -= $malus_bourre;
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
					$id_bat = $t["id_instanceBat"];
					$bat = $t["id_batiment"];
					$niveau_instance = $t["niveau_instance"];
					$nom_ibat = $t["nom_instance"];
					
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
					if(bourre($id_perso)){
						if(!endurance_alcool($id_perso))
							$malus_bourre = bourre($id_perso) * 2;
							$bonus_visu -= $malus_bourre;
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
						$nom_o_c = $info_c["nom_objet"];
						$poids_o_c = $info_c["poids_objet"];
						
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
	
				if(isset($_POST['valide_changement_de'])){
					if(!$changementDe_p){
						if(isset($_POST['valeur_attaque']) && $_POST['valeur_attaque'] != ""
							&& isset($_POST['valeur_defense']) && $_POST['valeur_defense'] != ""){
						   
							$verif_attaque = preg_match("#^[0-9]+$#i",$_POST['valeur_attaque']);
							$verif_defense = preg_match("#^[0-9]+$#i",$_POST['valeur_defense']);
							
							if($verif_attaque && $verif_defense){
								$deAttaque = $_POST['valeur_attaque'];
								$deDefense = $_POST['valeur_defense'];
								
								// On récupére les dès que possède le perso
								$sql = "SELECT deAttaque_perso, deDefense_perso FROM perso WHERE id_perso='$id_perso'";
								$res = $mysqli->query($sql);
								$t = $res->fetch_assoc();
								$attaque_perso = $t["deAttaque_perso"];
								$defense_perso = $t["deDefense_perso"];
								$total_des_perso = $attaque_perso + $defense_perso;
								
								// controles divers
								if($deAttaque + $deDefense == $total_des_perso 
									&& ($deAttaque >= 1 && $deAttaque <= $total_des_perso - 1) 
									&& ($deDefense >= 1 && $deDefense <= $total_des_perso - 1)){
									
									// on met à jour les dés du perso
									$sql = "UPDATE perso SET deAttaque_perso=$deAttaque, deDefense_perso=$deDefense, changementDe_perso='1' WHERE id_perso=$id_perso";
									$mysqli->query($sql);
								
									// on indique au joueur que la maj à été bien faite
									echo "Changement de dés effectué";
								}
								else {
									echo "<font color=red><center><b>Les valeurs des dès sont incorrectes</b></center></font>";
								}
							}
							else {
								echo "<font color=red><center><b>Les valeurs des dès sont incorrectes</b></center></font>";
							}
						}
						else {
							echo "<font color=red><center><b>Les valeurs des dès sont incorrectes</b></center></font>";
						}
					}
					else {
						echo "<font color=red><center><b>Ouh le petit tricheur !</b></center></font>";
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
								$case_occupee = $t_carte1["occupee_carte"];
								$fond = $t_carte1["fond_carte"];
								
								$cout_pm = cout_pm($fond);
								$bonus_visu = get_malus_visu($fond);
								
								if(bourre($mysqli, $id_perso)){
									if(!endurance_alcool($id_perso))
										$malus_bourre = bourre($id_perso) * 2;
										$bonus_visu -= $malus_bourre;
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
												$res_bat = id_prox_bat($x_persoN,$y_persoN); 
												while ($bat1 = $res_bat->fetch_assoc()) {
													$nom_ibat = $bat1["nom_instance"];
													$id_bat = $bat1["id_instanceBat"];
													$bat = $bat1["id_batiment"];
													
													//recuperation du nom du batiment
													$sql_n = "SELECT nom_batiment FROM batiment WHERE id_batiment = '$bat'";
													$res_n = $mysqli->query($sql_n);
													$t_n = $res_n->fetch_assoc();
													$nom_bat = $t_n["nom_batiment"];
													
													// verification si le batiment est de la même nation que le perso
													if(!nation_perso_bat($id_perso,$id_bat)) { // pas même nation
														// verification si le batiment est vide
														if(batiment_vide($id_bat) && $bat != 1){
															$mess_bat .= "<center><font color = blue>~~<a href=\"jouer.php?bat=$id_bat&bat2=$bat\" > capturer le batiment $nom_bat $nom_ibat [$id_bat]</a>~~</font></center>";
														}
													}
													else {
														if($bat != 1){
															$mess_bat .= "<center><font color = blue>~~<a href=\"jouer.php?bat=$id_bat&bat2=$bat\" > entrer dans le batiment $nom_bat $nom_ibat [$id_bat]</a>~~</font></center>";
														}
													}
												}
											}
										}
										else{
										
											$erreur .= "Vous n'avez pas assez de pm !";
											
											if(prox_bat($mysqli, $x_persoE, $y_persoE, $id_perso) && !in_bat($mysqli, $id_perso)){ // verification si il y a un batiment a proximite du perso
												// recuperation des id et noms des batiments dans lesquels le perso peut entrer
												$res_bat = id_prox_bat($x_persoE,$y_persoE); 
												while ($bat1 = $res_bat->fetch_assoc()) {
													$nom_ibat = $bat1["nom_instance"];
													$id_bat = $bat1["id_instanceBat"];
													$bat = $bat1["id_batiment"];
													
													//recuperation du nom du batiment
													$sql_n = "SELECT nom_batiment FROM batiment WHERE id_batiment = '$bat'";
													$res_n = $mysqli->query($sql_n);
													$t_n = $res_n->fetch_assoc();
													$nom_bat = $t_n["nom_batiment"];
													
													// verification si le batiment est de la même nation que le perso
													if(!nation_perso_bat($id_perso,$id_bat)) { // pas même nation
														// verification si le batiment est vide
														if(batiment_vide($id_bat) && $bat != 1){
															$mess_bat .= "<center><font color = blue>~~<a href=\"jouer.php?bat=$id_bat&bat2=$bat\" > capturer le batiment $nom_bat $nom_ibat [$id_bat]</a>~~</font></center>";
														}
													}
													else {
														if($bat != 1){
															$mess_bat .= "<center><font color = blue>~~<a href=\"jouer.php?bat=$id_bat&bat2=$bat\" > entrer dans le batiment $nom_bat $nom_ibat [$id_bat]</a>~~</font></center>";
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
											$res_bat = id_prox_bat($x_persoE,$y_persoE); 
											while ($bat1 = $res_bat->fetch_assoc()) {
												$nom_ibat = $bat1["nom_instance"];
												$id_bat = $bat1["id_instanceBat"];
												$bat = $bat1["id_batiment"];
													
												//recuperation du nom du batiment
												$sql_n = "SELECT nom_batiment FROM batiment WHERE id_batiment = '$bat'";
												$res_n = $mysqli->query($sql_n);
												$t_n = $res_n->fetch_assoc();
												$nom_bat = $t_n["nom_batiment"];
													
												// verification si le batiment est de la même nation que le perso
												if(!nation_perso_bat($id_perso,$id_bat)) { // pas même nation
													// verification si le batiment est vide
													if(batiment_vide($id_bat) && $bat != 1){
														$mess_bat .= "<center><font color = blue>~~<a href=\"jouer.php?bat=$id_bat&bat2=$bat\" > capturer le batiment $nom_bat $nom_ibat [$id_bat]</a>~~</font></center>";
													}
												}
												else {
													if($bat != 1){
														$mess_bat .= "<center><font color = blue>~~<a href=\"jouer.php?bat=$id_bat&bat2=$bat\" > entrer dans le batiment $nom_bat $nom_ibat [$id_bat]</a>~~</font></center>";
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
										$res_bat = id_prox_bat($x_persoE,$y_persoE); 
										while ($bat1 = $res_bat->fetch_assoc()) {
											$nom_ibat = $bat1["nom_instance"];
											$id_bat = $bat1["id_instanceBat"];
											$bat = $bat1["id_batiment"];
											
											//recuperation du nom du batiment
											$sql_n = "SELECT nom_batiment FROM batiment WHERE id_batiment = '$bat'";
											$res_n = $mysqli->query($sql_n);
											$t_n = $res_n->fetch_assoc();
											$nom_bat = $t_n["nom_batiment"];
											
											// verification si le batiment est de la même nation que le perso
											if(!nation_perso_bat($id_perso,$id_bat)) { // pas même nation
												// verification si le batiment est vide
												if(batiment_vide($id_bat) && $bat != 1){
													$mess_bat .= "<center><font color = blue>~~<a href=\"jouer.php?bat=$id_bat&bat2=$bat\" > capturer le batiment $nom_bat $nom_ibat [$id_bat]</a>~~</font></center>";
												}
											}
											else {
												if($bat != 1){
													$mess_bat .= "<center><font color = blue>~~<a href=\"jouer.php?bat=$id_bat&bat2=$bat\" > entrer dans le batiment $nom_bat $nom_ibat [$id_bat]</a>~~</font></center>";
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
									$res_bat = id_prox_bat($x_persoE,$y_persoE); 
									while ($bat1 = $res_bat->fetch_assoc()) {
										$nom_ibat = $bat1["nom_instance"];
										$id_bat = $bat1["id_instanceBat"];
										$bat = $bat1["id_batiment"];
											
										//recuperation du nom du batiment
										$sql_n = "SELECT nom_batiment FROM batiment WHERE id_batiment = '$bat'";
										$res_n = $mysqli->query($sql_n);
										$t_n = $res_n->fetch_assoc();
										$nom_bat = $t_n["nom_batiment"];
											
										// verification si le batiment est de la même nation que le perso
										if(!nation_perso_bat($id_perso,$id_bat)) { // pas même nation
											// verification si le batiment est vide
											if(batiment_vide($id_bat) && $bat != 1){
												$mess_bat .= "<center><font color = blue>~~<a href=\"jouer.php?bat=$id_bat&bat2=$bat\" > capturer le batiment $nom_bat $nom_ibat [$id_bat]</a>~~</font></center>";
											}
										}
										else {
											if($bat != 1){
												$mess_bat .= "<center><font color = blue>~~<a href=\"jouer.php?bat=$id_bat&bat2=$bat\" > entrer dans le batiment $nom_bat $nom_ibat [$id_bat]</a>~~</font></center>";
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
								$res_bat = id_prox_bat($x_persoE,$y_persoE); 
								while ($bat1 = $res_bat->fetch_assoc()) {
									$nom_ibat = $bat1["nom_instance"];
									$id_bat = $bat1["id_instanceBat"];
									$bat = $bat1["id_batiment"];
									
									//recuperation du nom du batiment
									$sql_n = "SELECT nom_batiment FROM batiment WHERE id_batiment = '$bat'";
									$res_n = $mysqli->query($sql_n);
									$t_n = $res_n->fetch_assoc();
									$nom_bat = $t_n["nom_batiment"];
									
									// verification si le batiment est de la même nation que le perso
									if(!nation_perso_bat($id_perso,$id_bat)) { // pas même nation
										// verification si le batiment est vide
										if(batiment_vide($id_bat) && $bat != 1){
											$mess_bat .= "<center><font color = blue>~~<a href=\"jouer.php?bat=$id_bat&bat2=$bat\" > capturer le batiment $nom_bat $nom_ibat [$id_bat]</a>~~</font></center>";
										}
									}
									else {
										if($bat != 1){
											$mess_bat .= "<center><font color = blue>~~<a href=\"jouer.php?bat=$id_bat&bat2=$bat\" > entrer dans le batiment $nom_bat $nom_ibat [$id_bat]</a>~~</font></center>";
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
						$res_bat = id_prox_bat($x_persoN,$y_persoN); 
						while ($bat1 = $res_bat->fetch_assoc()) {
							$nom_ibat = $bat1["nom_instance"];
							$id_bat = $bat1["id_instanceBat"];
							$bat = $bat1["id_batiment"];
							
							//recuperation du nom du batiment
							$sql_n = "SELECT nom_batiment FROM batiment WHERE id_batiment = '$bat'";
							$res_n = $mysqli->query($sql_n);
							$t_n = $res_n->fetch_assoc();
							$nom_bat = $t_n["nom_batiment"];
							
							// verification si le batiment est de la même nation que le perso
							if(!nation_perso_bat($id_perso,$id_bat)) { // pas même nation
								// verification si le batiment est vide
								if(batiment_vide($id_bat) && $bat != 1){
									$mess_bat .= "<center><font color = blue>~~<a href=\"jouer.php?bat=$id_bat&bat2=$bat\" > capturer le batiment $nom_bat $nom_ibat [$id_bat]</a>~~</font></center>";
								}
							}
							else {
								if($bat != 1){
									$mess_bat .= "<center><font color = blue>~~<a href=\"jouer.php?bat=$id_bat&bat2=$bat\" > entrer dans le batiment $nom_bat $nom_ibat [$id_bat] </a>~~</font></center>";
								}
							}
						}
					}
				}
				//affichage de l'heure serveur et de nouveau tour
				echo "<table width=100% bgcolor='white' border=0>";
				echo "<tr>
						<td><img src='../images/clock.png' alt='horloge' width='25' height='25'/> Heure serveur : <b><span id=tp1>".date('H:i:s ')."</span></b></td>
						<td rowspan=2><img src='../images/accueil/banniere3.jpg' alt='banniere NAOnline' width=400 height=62 /></td>
						<td align=right> <a href=\"../logout.php\"><font color=red><b>[déconnexion]</b></font></a></td>
					</tr>";
				echo "<tr>
						<td>Prochain tour :  ".$n_dla."</td>
						<td align=right> <a href=\"../forum2/index.php\"><font color=blue><b>[forum]</b></font></a></td>
					</tr>";
				echo "</table>";
	
				$sql_info = "SELECT xp_perso, pc_perso, pv_perso, changementDe_perso, pvMax_perso, pa_perso, paMax_perso, pi_perso, pmMax_perso, pm_perso, or_perso, x_perso, y_perso, perception_perso, bonusPerception_perso, bonus_perso, image_perso, deAttaque_perso, deDefense_perso, clan FROM perso WHERE ID_perso ='$id_perso'"; 
				$res_info = $mysqli->query($sql_info);
				$t_perso2 = $res_info->fetch_assoc();
				
				$x_perso = $t_perso2["x_perso"];
				$y_perso = $t_perso2["y_perso"];
				$image_perso = $t_perso2["image_perso"];
				$perc = $t_perso2["perception_perso"] + $t_perso2["bonusPerception_perso"];
				$pa_perso = $t_perso2["pa_perso"];
				$paMax_perso = $t_perso2["paMax_perso"];
				$pi_perso = $t_perso2["pi_perso"];
				$xp_perso = $t_perso2["xp_perso"];
				$pc_perso = $t_perso2["pc_perso"];
				$pv_perso = $t_perso2["pv_perso"];
				$pvMax_perso = $t_perso2["pvMax_perso"];
				$pm_perso = $t_perso2["pm_perso"] + $malus_pm;
				$pmMax_perso = $t_perso2["pmMax_perso"];
				$deAttaque_perso = $t_perso2["deAttaque_perso"];
				$deDefense_perso = $t_perso2["deDefense_perso"];
				$changementDe_perso = $t_perso2["changementDe_perso"];
				$perception_perso = $t_perso2["perception_perso"];
				$bonusPerception_perso = $t_perso2["bonusPerception_perso"];
				$bonus_perso = $t_perso2["bonus_perso"];
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
				
				// verifier que $_SESSION['deAttaque'] + $_SESSION['deDefense'] + ameliorations = deDefense_perso + deAttaque_perso
				?>
				<!-- Début du tableau d'information-->
				<table border=1 align="center" width=90%>
					<tr>
						<td width=60><center><img src="../images_perso/<?php echo "$image_perso";?>"></center></td>
						<td align=center>Pseudo: <?php echo "$nom_perso [$id_perso]";?></td><td align=center>xp: <?php echo "$xp_perso";?> / pi: <?php echo "$pi_perso";?> / pc: <?php echo "$pc_perso";?></td>
						<td align=center><?php $pourc = affiche_jauge($pv_perso, $pvMax_perso); echo "".round($pourc)."% ou $pv_perso/$pvMax_perso"; ?></td>
						<td><?php echo "<center>Attaque ".$deAttaque_perso."/".$deDefense_perso." Défense<br>"; echo"<font color=red>Pensez à cliquer sur valider pour changer les dés</font></center>";?></td>
					</tr>
					<tr>
						<td align=center>pa: <?php echo "".$pa_perso."/".$paMax_perso."";?></td>
						<td align=center>perception: <?php echo "$perception_perso"; if($bonusPerception_perso){ if($bonusPerception_perso>0) echo "(+".$bonusPerception_perso.")"; else echo "(".$bonusPerception_perso.")";}?></td>
						<td align=center>pm: <?php echo "".$pm_perso."/".$pmMax_perso.""; if($malus_pm){ echo "<font color='red'> ($malus_pm)</font>";}?></td>
						<td align=center>position : <?php echo "".$t_perso2["x_perso"]."/".$t_perso2["y_perso"]."";?></td>
						<!-- Début formulaire changement de dès -->
						<td>
							<center>
								<form method='post' name='change_des' action='jouer.php'>
									<?php if($changementDe_perso) {?>
									<a href="#" <?php if($changementDe_perso) echo "disabled";?>><</a>
									<input type="text" disabled maxlength="2" size="1" value="<?php echo $deAttaque_perso; echo "\"> / <input type=\"text\" disabled maxlength=\"2\" size=\"1\" value=\""; echo $deDefense_perso; echo "\">";?>
									<a href="#" <?php if($changementDe_perso) echo "disabled";?>>></a>
									<?php } else {?>
									<a href="#" onClick="moins()"><</a>
									<input type="text" maxlength="2" size="1" name='valeur_attaque' value="<?php echo $deAttaque_perso; echo "\"> / <input type=\"text\" maxlength=\"2\" size=\"1\" name=\"valeur_defense\" value=\""; echo $deDefense_perso; echo "\">";?>
									<a href="#" onClick="plus()">></a>
									<?php }?>
									&nbsp;
									<input type="submit" name='valide_changement_de' <?php if($changementDe_perso) echo "disabled";?> value="Valider">
								</form>
							</center>
						</td>
						<!-- Fin formulaire changement de dès -->
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
					
				// recuperation des données de la carte
				$sql = "SELECT x_carte, y_carte, fond_carte, occupee_carte, image_carte, idPerso_carte FROM $carte WHERE x_carte >= $x_perso - $perc AND x_carte <= $x_perso + $perc AND y_carte <= $y_perso + $perc AND y_carte >= $y_perso - $perc ORDER BY y_carte DESC, x_carte";
				$res = $mysqli->query($sql);
				$tab = $res->fetch_assoc();
				
				echo '<table border=0 align="center" cellspacing="0" cellpadding="0" style:no-padding>';
				echo '<tr><td valign="top">';
				
				//<!--Génération de la carte-->
				echo '<table style="border:1px solid black; border-collapse: collapse;"><tr><td>';
				
				// calcul taille table
				$taille_table = ($perception_perso + $bonusPerception_perso) * 2 + 2;
				$taille_table = $taille_table * 40;
				
				echo "<table border=0 width=\"$taille_table\" height=\"$taille_table\" align=\"center\" cellspacing=\"0\" cellpadding=\"0\" style:no-padding>";
				
				echo "<tr><td width='40' heigth='40' background=\"../images/background.jpg\" align='center'>y \ x</td>";  //affichage des abscisses
				for ($i = $x_perso - $perc; $i <= $x_perso + $perc; $i++) {
					if ($i == $x_perso)
						echo "<th width=40 height=40 background=\"../images/background3.jpg\">$i</th>";
					else
						echo "<th width=40 height=40 background=\"../images/background.jpg\">$i</th>";
				}
				echo "</tr>";
				
				for ($y = $y_perso + $perc; $y >= $y_perso - $perc; $y--) {
					echo "<tr align=\"center\" >";
					if ($y == $y_perso)
						echo "<th width=40 height=40 background=\"../images/background3.jpg\">$y</th>";
					else
						echo "<th width=40 height=40 background=\"../images/background.jpg\">$y</th>";
					for ($x = $x_perso - $perc; $x <= $x_perso + $perc; $x++) {
						if ($x >= X_MIN && $y >= Y_MIN && $x <= $X_MAX && $y <= $Y_MAX) { //les coordonnées sont dans les limites
							if ($x == $x_perso && $y == $y_perso){ //coordonnées du perso
								echo "<td width=40 height=40 background=\"../fond_carte/".$tab["fond_carte"]."\"><img class=\"imagedessous\" border=0 src=\"../images_perso/$image_perso\" width=40 height=40 /><img border=0 src=\"../images_perso/$clan\" /></td>";
							}
							else {
								if ($tab["occupee_carte"]){
									if($tab['image_carte'] == "coffre1t.png")
										echo "<td width=40 height=40 background=\"../fond_carte/".$tab["fond_carte"]."\"> <a href=\"jouer.php?coffre=ok\"><img border=0 src=\"../images_perso/".$tab["image_carte"]."\" width=40 height=40\" title=\"coffre fermé\"></a></td>";//positionement du coffre present
									else {
										if($tab['image_carte'] == "coffre2t.png")
											echo "<td width=40 height=40 background=\"../fond_carte/".$tab["fond_carte"]."\"><img border=0 src=\"../images_perso/".$tab["image_carte"]."\" width=40 height=40\" title=\"coffre ouvert\"></a></td>";//positionement du coffre present
										else{
											// recuperation de l'image du pnj
											if($tab['idPerso_carte'] >= 10000 && $tab['idPerso_carte'] < 50000){
												$idI_pnj = $tab['idPerso_carte'];
												// recuperation du type de pnj
												$sql_im = "SELECT id_pnj FROM instance_pnj WHERE idInstance_pnj='$idI_pnj'";
												$res_im = $mysqli->query($sql_im);
												$t_im = $res_im->fetch_assoc();
												$id_pnj_im = $t_im["id_pnj"];
												$im_pnj="Monstre".$id_pnj_im."t.png";
	
												echo "<td width=40 height=40 background=\"../fond_carte/".$tab["fond_carte"]."\"> <a href=\"jouer.php?infoid=".$tab["idPerso_carte"]."\"><img border=0 src=\"../images_perso/".$tab["image_carte"]."\" width=40 height=40 onMouseOver=\"AffBulle('<img src=../images/$im_pnj>')\" onMouseOut=\"HideBulle()\" title=\"pnj mat ".$tab["idPerso_carte"]."\"></a></td>";
											}
											else{
												//  traitement Batiment
												if($tab['idPerso_carte'] >= 50000){
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
														echo "<td width=40 height=40 background=\"../fond_carte/".$tab["fond_carte"]."\"> <img border=0 src=\"../images_perso/".$tab["image_carte"]."\" width=40 height=40 onMouseOver=\"AffBulle('<img src=../images/murs/mur.jpeg>')\" onMouseOut=\"HideBulle()\" title=\"mur\"></td>";//positionement du coffre
													}
													else {
														$id_perso_im = $tab['idPerso_carte'];
														//recuperation du type de perso (image)
														$sql_perso_im ="SELECT * FROM perso WHERE id_perso='$id_perso_im'";
														$res_perso_im = $mysqli->query($sql_perso_im);
														$t_perso_im = $res_perso_im->fetch_assoc();
														$im_perso = $t_perso_im["image_perso"];
														$nom_ennemi = $t_perso_im['nom_perso'];
														$id_ennemi = $t_perso_im['id_perso'];
														$clan_e = $t_perso_im['clan'];
														
														if($clan_e == 1){
															$clan_ennemi = 'rond_b.png';
															$couleur_clan_e = 'blue';
															$image_profil = "nord.gif";
														}
														if($clan_e == 2){
															$clan_ennemi = 'rond_r.png';
															$couleur_clan_e = 'red';
															$image_profil = "sud.gif";
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
															echo "<td width=40 height=40 background=\"../fond_carte/".$tab["fond_carte"]."\"><a href=\"jouer.php?infoid=".$tab["idPerso_carte"]."\"><img class=\"imagedessous\" border=0 src=\"../images_perso/".$tab["image_carte"]."\" width=40 height=40 onMouseOver=\"AffBulle('<tr><td rowspan=2><img src=../images/$image_profil></td><td>id:</td><td> $id_ennemi</td></tr><tr><td>nom:</td><td> $nom_ennemi</td></tr><tr><td>groupe:</td><td colspan=2> $groupe</td></tr>')\" onMouseOut=\"HideBulle()\" /><img border=0 src=\"../images_perso/$clan_ennemi\" /></a></td>";
														}
														else {
															echo "<td width=40 height=40 background=\"../fond_carte/".$tab["fond_carte"]."\"><a href=\"jouer.php?infoid=".$tab["idPerso_carte"]."\"><img class=\"imagedessous\" border=0 src=\"../images_perso/".$tab["image_carte"]."\" width=40 height=40 onMouseOver=\"AffBulle('<tr><td rowspan=2><img src=../images/$image_profil></td><td>id:</td><td> $id_ennemi</td></tr><tr><td>nom:</td><td> $nom_ennemi</td></tr><tr><td align=center><a href=evenement.php?infoid=$id_ennemi>Plus d\'info</a></td></tr>')\" onMouseOut=\"HideBulle()\" /><img border=0 src=\"../images_perso/$clan_ennemi\" /></a></td>";
														}
													}
												}
											}										
										}
									}
								}
								else{
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
				</td></tr></table>
				</td>
				<!--Fin de la génération de la carte-->
				
				<?php
				if($config == '2'){
					echo "</tr><tr>";
				}
				?>
				
				<td>
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
														echo "<option value=\"PA\">* Entrainement (1 point - 10 pa)</option>";
													}
													else {
														echo "<option value=\"65\">Entrainement (1 point - 10 pa)</option>";
													}
													// Action Déposer Objet
													if($pa_perso < 1){
														echo "<option value=\"PA\">* Deposer objet (1 point - 1 pa)</option>";
														echo "<option value=\"PA\">* Donner objet (1 point - 1 pa)</option>";
													}
													else {
														echo "<option value=\"110\">Deposer objet (1 point - 1 pa)</option>";
														echo "<option value=\"139\">Donner objet (1 point - 1 pa)</option>";
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
													
													$sql = "SELECT action.id_action, nom_action, portee_action, coutPa_action, perso_as_competence.nb_points 
															FROM perso_as_competence, competence_as_action, action 
															WHERE id_perso='$id_perso' 
															AND perso_as_competence.id_competence=competence_as_action.id_competence 
															AND competence_as_action.id_action=action.id_action
															AND perso_as_competence.nb_Points=action.nb_points
															AND passif_action = '0'
															ORDER BY nom_action";
													$res = $mysqli->query($sql);
													
													while ($t_ac = $res->fetch_assoc()) {
														
														$id_ac = $t_ac["id_action"];
														$portee_ac = $t_ac["portee_action"];
														$cout_PA = $t_ac["coutPa_action"];
														$nom_ac = $t_ac["nom_action"];
														$nb_points_ac = $t_ac["nb_points"];
													
														if ($cout_PA == -1){
															$cout_PA = $paMax_perso;
														}
														
														if ($cout_PA <= $pa_perso){
															echo "<option value=\"$id_ac\">".$nom_ac." (";
														}
														else {
															echo "<option value=\"PA\">* ".$nom_ac." (";
														}
														if ($nb_points_ac == 1){
															echo $nb_points_ac." point - ";
														}
														else{ 
															echo $nb_points_ac." points - ";
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
													<td valign='top'>&nbsp;</td>
													<td valign='top'>
														<form method="post" action="agir.php" target='_main'>
															<input type="text" maxlength="6" size="6" name="id_attaque" value="<?php if (isset($_GET["infoid"]) && $_GET["infoid"] < 10000) echo $infoid; elseif(isset($_GET["infoid"]) && $_GET["infoid"] >= 10000) echo $_GET["infoid"];?>"style="background-image:url('../images/background3.jpg');">
															<input type="submit" value="Attaquer">
														</form>
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
											//if (isset($_GET["infoid"]) && $_GET["infoid"] < 10000) 
												//echo "<a href=\"nouveau_message.php?pseudo=$nom_infoid\" target='_blank'><img src=\"../images/msg.gif\" border=0 width=40 height=40></a>Envoyer un message a ce perso<br />";
											echo "<a href=\"nouveau_message.php?visu=ok\" target='_blank'><img src='../images/Ecrire.png' border=0 /><img src='../images/Envoyer_message.png' border=0 />";?>
											
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
