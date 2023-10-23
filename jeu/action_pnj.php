<?php
session_start();
require_once("../fonctions.php");
require_once("f_pnj.php");
require_once("f_carte.php");
require_once("f_combat.php");

$mysqli = db_connexion();

// Récupération de la clef secrete
$sql = "SELECT valeur_config FROM config_jeu WHERE code_config='clef_secrete'";
$res = $mysqli->query($sql);
$t = $res->fetch_assoc();

$clef_secrete = $t['valeur_config'];

if (isset($_GET['clef']) && $_GET['clef'] == $clef_secrete) {

	$nb_deplacer = 0;
	$nb_a_deplacer = 0;

	// recuperation du nombre total de pnj
	$sql = "SELECT idInstance_pnj, pv_i FROM instance_pnj ORDER BY idInstance_pnj";
	$res = $mysqli->query($sql);
	$numT = $res->num_rows;

	// 1/7 pnj doit se déplacer
	$nb_a_deplacer = ceil($numT / 7);

	// Récupération du cycle actuel
	$sql = "SELECT DISTINCT cycle_mvt FROM instance_pnj";
	$res = $mysqli->query($sql);
	$c_id = $res->fetch_assoc()["cycle_mvt"];

	// MAJ du cycle
	$cycle = $c_id +1;

	$sql = "UPDATE instance_pnj SET cycle_mvt=$cycle";
	$mysqli->query($sql);

	// recuperation de pnj qui ne se sont pas encore deplacés
	$sql = "SELECT idInstance_pnj, pv_i FROM instance_pnj WHERE deplace_i='0' ORDER BY idInstance_pnj";
	$res = $mysqli->query($sql);
	$num = $res->num_rows;

	echo $num."<br>";

	// tout les pnj se sont deplacés
	if ($num == 0 && $cycle >= 8){

		echo "tout les pnj se sont deplacés<br>";
		
		// on remet les pnj a l'etat non deplacé
		$sql = "UPDATE instance_pnj SET deplace_i='0', cycle_mvt='0'";
		$mysqli->query($sql);
	}

	while ($t_id = $res->fetch_assoc()) {
		
		if($nb_deplacer == $nb_a_deplacer || $nb_deplacer == $num){
			echo "fin deplacement pnj<br>";
			break;
		}
		
		$nb_deplacer++;
		
		$id_i_pnj 	= $t_id["idInstance_pnj"];
		$pv_i 		= $t_id["pv_i"];
		
		echo "$nb_deplacer | id pnj : $id_i_pnj";
		
		// Recuperation des infos du pnj
		$sql = "SELECT pnj.id_pnj, nom_pnj, degatMin_pnj, degatMax_pnj, pvMax_pnj, perception_pnj, recup_pnj, aggressivite_pnj, degatMin_pnj, degatMax_pnj, precision_pnj 
				FROM pnj, instance_pnj 
				WHERE pnj.id_pnj=instance_pnj.id_pnj AND idInstance_pnj=$id_i_pnj";
		$res3 = $mysqli->query($sql);
		$info_pnj = $res3->fetch_assoc();
		
		$nom_pnj 		= $info_pnj["nom_pnj"];
		$degatMin 		= $info_pnj["degatMin_pnj"];
		$degatMax 		= $info_pnj["degatMax_pnj"];
		$pvMax 			= $info_pnj["pvMax_pnj"];
		$perception 	= $info_pnj["perception_pnj"];
		$recup 			= $info_pnj["recup_pnj"];
		$agressivite 	= $info_pnj["aggressivite_pnj"];
		$type_pnj 		= $info_pnj["id_pnj"];
		$degatMin_pnj 	= $info_pnj["degatMin_pnj"];
		$degatMax_pnj 	= $info_pnj["degatMax_pnj"];
		$precision_pnj	= $info_pnj["precision_pnj"];
		
		// on met a jour les pv et les pm de l'instance
		if ($pv_i+$recup < $pvMax) {
			$sql = "UPDATE instance_pnj SET pv_i=pv_i+$recup WHERE idInstance_pnj=$id_i_pnj";
			$mysqli->query($sql);
		}
		else if ($pv_i < $pvMax) {
			$sql = "UPDATE instance_pnj SET pv_i=$pvMax WHERE idInstance_pnj=$id_i_pnj";
			$mysqli->query($sql);
		}
		else {
			$sql = "UPDATE instance_pnj SET dernierAttaquant_i=0 WHERE idInstance_pnj=$id_i_pnj";
			$mysqli->query($sql);
		}

		
		// on recupere les infos de l'instance
		$sql = "SELECT pv_i, pm_i, x_i, y_i, dernierAttaquant_i FROM instance_pnj WHERE idInstance_pnj=$id_i_pnj";
		$res4 = $mysqli->query($sql);
		$pnj = $res4->fetch_assoc();
		
		$pv_i 			= $pnj["pv_i"];
		$pm_i 			= $pnj["pm_i"];
		$x_i 			= $pnj["x_i"];
		$y_i 			= $pnj["y_i"];
		$dernier_a_i 	= $pnj["dernierAttaquant_i"];
		
		echo " - Agressivite : ".$agressivite." -- PM : ".$pm_i."<br>";
		
		switch($agressivite) {
			
			// pnj fuyant / peureux
			case(0):
				
				// si il se trouve a coté d'un pj
				if ($id_cible = proxi_perso($mysqli,$x_i,$y_i)){
					
					// recuperation du nom et de la nation de la cible
					$sql = "SELECT nom_perso, x_perso, y_perso, bonus_perso, clan FROM perso WHERE id_perso=$id_cible";
					$res5 = $mysqli->query($sql);
					$t_n = $res5->fetch_assoc();
					
					$nom_cible 		= $t_n["nom_perso"];
					$x_cible 		= $t_n["x_perso"];
					$y_cible 		= $t_n["y_perso"];
					$bonus_def_pj 	= $t_n["bonus_perso"];
					$clan_cible 	= $t_n["clan"];
					
					// Récupération de la couleur associée au clan de la cible
					$couleur_clan_cible = couleur_clan($clan_cible);
				
					// on l'attaque
					if (combat_pnj($precision_pnj,	$bonus_def_pj)) {
						
						// Calcul des dégats
						$degats = mt_rand($degatMin, $degatMax);
						
						$sql = "UPDATE perso SET pv_perso=pv_perso-$degats WHERE id_perso='$id_cible'";
						$mysqli->query($sql);		
						
						// maj evenement
						$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) 
								VALUES ('$id_i_pnj','<b>$nom_pnj</b>','a attaqué ','$id_cible','<font color=$couleur_clan_cible><b>$nom_cible</b></font>',': $degats degats',NOW(),'0')";
						$mysqli->query($sql);
						
						// verification si la cible est morte ou non
						$sql = "SELECT pv_perso, x_perso, y_perso, xp_perso, pi_perso, pc_perso, or_perso, chef FROM perso WHERE ID_perso='$id_cible'";
						$res2 = $mysqli->query($sql);
						$tab = $res2->fetch_assoc();
						
						$pv_cible 	= $tab["pv_perso"];
						$x_cible 	= $tab["x_perso"];
						$y_cible 	= $tab["y_perso"];
						$xp_cible 	= $tab["xp_perso"];
						$pi_cible	= $tab['pi_perso'];
						$pc_cible	= $tab['pc_perso'];
						$or_cible	= $tab['or_perso'];
						$tp_perso	= $tab['chef'];
					
						if ($pv_cible <= 0) {
						
							// Calcul gains (po et xp)
							$perte_po = gain_po_mort($or_cible);
							
							// Chef
							if ($tp_perso == 1) {

								perte_etendard($mysqli, $id_cible, $x_cible, $y_cible);
								
								// Quand un chef meurt, il perd 5% de ses XPi et de ses PC
								// Calcul PI
								$pi_perdu 		= floor(($pi_cible * 5) / 100);
								
								// Calcul PC
								$pc_perdu		= floor(($pc_cible * 5) / 100);
								$pc_perso_fin	= $pc_cible - $pc_perdu;
							}
							else {
								$pi_perdu 		= floor(($pi_cible * 40) / 100);
								$pc_perso_fin = $pc_cible;
							}
		
							// MAJ perte xp/po/stat cible
							$sql = "UPDATE perso SET or_perso=or_perso-$perte_po, xp_perso=xp_perso-$pi_perdu, pi_perso=pi_perso-$pi_perdu, pc_perso=$pc_perso_fin, nb_mort=nb_mort+1 WHERE id_perso='$id_cible'";
							$mysqli->query($sql);
							
							// maj carte
							$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_cible' AND y_carte='$y_cible'";
							$mysqli->query($sql);
							
							// maj evenement
							$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_i_pnj,'<b>$nom_pnj</b>','<b>a capturé</b>','$id_cible','<font color=$couleur_clan_cible><b>$nom_cible</b></font>','',NOW(),'0')";
							$mysqli->query($sql);
							
							// maj cv
							$sql = "INSERT INTO `cv` (IDActeur_cv, nomActeur_cv, IDCible_cv, nomCible_cv, date_cv) VALUES ($id_i_pnj,'$nom_pnj','$id_cible','$nom_cible',NOW())";
							$mysqli->query($sql);
							
							// maj dernier tombé
							$sql = "INSERT INTO dernier_tombe (date_capture, id_perso_capture, camp_perso_capture, id_perso_captureur, camp_perso_captureur) VALUES (NOW(), '$id_cible', $clan_cible, $id_i_pnj, 0)";
							$mysqli->query($sql);
						}	
					}
					else { 
					
						// la cible a esquivé l'attaque
						$sql = "UPDATE perso SET xp_perso=xp_perso+1, pi_perso=pi_perso+1 WHERE ID_perso='$id_cible'";
						$mysqli->query($sql);
						
						// maj evenement
						$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_cible,'<font color=$couleur_clan_cible><b>$nom_cible</b></font>','a esquivé l\'attaque de','$id_i_pnj','<b>$nom_pnj</b>','',NOW(),'0')";
						$mysqli->query($sql);
					}
					
					// après l'attaque : on fuit du coté opposé à la cible
					// calcul des coordonnées de deplacement
					$x_d = calcul_vecteur_x($x_i,$x_cible);
					$y_d = calcul_vecteur_y($y_i,$y_cible);
					
					// fuite du pnj dans le vecteur de deplacement
					deplacement_fuite($mysqli, $x_d, $y_d, $x_cible, $y_cible, $pm_i, $id_i_pnj, $nom_pnj, $type_pnj);
				}
				else {
					// on se trouve pas a coté d'un pj
					// on regarde si il y a un pj dans la visu du pnj
					if ($id_pj = proche_perso($mysqli,$x_i,$y_i,$perception)){			
						// on se deplace dans le sens opposé de ce pj
						
						$sql5 = "SELECT x_perso, y_perso FROM perso WHERE id_perso='$id_pj'";
						$res5 = $mysqli->query($sql5);
						$tpj = $res5->fetch_assoc();
						
						$x_cible = $tpj["x_perso"];
						$y_cible = $tpj["y_perso"];
						
						// calcul des coordonnées de deplacement
						$x_d = calcul_vecteur_x($x_i,$x_cible);
						$y_d = calcul_vecteur_y($y_i,$y_cible);
						
						// fuite du pnj dans le vecteur de deplacement
						deplacement_fuite($mysqli, $x_d, $y_d, $x_cible, $y_cible, $pm_i, $id_i_pnj, $nom_pnj, $type_pnj);
					}
					else {
						
						while($pm_i > 0 && !proche_perso($mysqli,$x_i,$y_i,$perception)){
							
							// recuperation des nouvelles coordonées du pnj
							$sql = "SELECT x_i, y_i FROM instance_pnj WHERE idInstance_pnj=$id_i_pnj";
							$res9 = $mysqli->query($sql);
							$t_copnj = $res9->fetch_assoc();
							
							$x_i = $t_copnj["x_i"];
							$y_i = $t_copnj["y_i"];

							deplacement_hasard($mysqli, $x_i, $y_i, $id_i_pnj, $type_pnj, $nom_pnj);
							$pm_i--;
						}
					}
				}
				break;
				
			// pnj normal
			case(1):
				// on regarde si il a été attaqué au tour d'avant
				if ($dernier_a_i) {
					
					//on verifie si le perso est toujours dans la visu du pnj
					if ($id_pj = perso_visu_pnj($mysqli,$x_i,$y_i,$perception,$dernier_a_i)) {
						$id_cible = $dernier_a_i;
						
						// on regarde si le perso est au CaC
						if(proxi_perso_cible($mysqli,$x_i,$y_i,$dernier_a_i)){
							
							// on l'attaque
							// recuperation du nom de la cible
							$sql = "SELECT nom_perso, bonus_perso, x_perso, y_perso, clan FROM perso WHERE id_perso='$dernier_a_i'";
							$res5 = $mysqli->query($sql);
							$t_n = $res5->fetch_assoc();
							
							$nom_cible 		= $t_n["nom_perso"];
							$x_cible 		= $t_n["x_perso"];
							$y_cible 		= $t_n["y_perso"];
							$bonus_def_pj 	= $t_n["bonus_perso"];
							$clan_cible 	= $t_n["clan"];
					
							// Récupération de la couleur associée au clan de la cible
							$couleur_clan_cible = couleur_clan($clan_cible);
						
							// on l'attaque
							if (combat_pnj($precision_pnj,	$bonus_def_pj)) {
						
								$degats = mt_rand($degatMin, $degatMax);
								
								$sql = "UPDATE perso SET pv_perso=pv_perso-$degats WHERE ID_perso='$dernier_a_i'";
								$mysqli->query($sql);		
								
								// maj evenement
								$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_i_pnj,'<b>$nom_pnj</b>','a attaqué ','$dernier_a_i','<font color=$couleur_clan_cible><b>$nom_cible</b></font>',': $degats degats',NOW(),'0')";
								$mysqli->query($sql);
								
								// verification si la cible est morte ou non
								$sql = "SELECT pv_perso, x_perso, y_perso, xp_perso, pi_perso, pc_perso, or_perso, chef FROM perso WHERE ID_perso='$dernier_a_i'";
								$res2 = $mysqli->query($sql);
								$tab = $res2->fetch_assoc();
								
								$pv_cible 	= $tab["pv_perso"];
								$x_cible 	= $tab["x_perso"];
								$y_cible 	= $tab["y_perso"];
								$xp_cible 	= $tab["xp_perso"];
								$pi_cible	= $tab['pi_perso'];
								$pc_cible	= $tab['pc_perso'];
								$or_cible	= $tab['or_perso'];
								$tp_perso	= $tab['chef'];
							
								if ($pv_cible <= 0) {
								
									// Calcul gains (po et xp)
									$perte_po = gain_po_mort($or_cible);
									
									// Chef
									if ($tp_perso == 1) {
										perte_etendard($mysqli, $id_cible, $x_cible, $y_cible);
										// Quand un chef meurt, il perd 5% de ses XPi et de ses PC
										// Calcul PI
										$pi_perdu 		= floor(($pi_cible * 5) / 100);
										
										// Calcul PC
										$pc_perdu		= floor(($pc_cible * 5) / 100);
										$pc_perso_fin	= $pc_cible - $pc_perdu;
									}
									else {
										$pi_perdu 		= floor(($pi_cible * 40) / 100);
										$pc_perso_fin = $pc_cible;
									}
				
									// MAJ perte xp/po/stat cible
									$sql = "UPDATE perso SET or_perso=or_perso-$perte_po, xp_perso=xp_perso-$pi_perdu, pi_perso=pi_perso-$pi_perdu, pc_perso=$pc_perso_fin, nb_mort=nb_mort+1 WHERE id_perso='$id_cible'";
									$mysqli->query($sql);
									
									// maj carte
									$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_cible' AND y_carte='$y_cible'";
									$mysqli->query($sql);
									
									// maj evenement
									$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_i_pnj,'<b>$nom_pnj</b>','<b>a capturé</b>','$dernier_a_i','<font color=$couleur_clan_cible><b>$nom_cible</b></font>','',NOW(),'0')";
									$mysqli->query($sql);
									
									// maj cv
									$sql = "INSERT INTO `cv` (IDActeur_cv, nomActeur_cv, IDCible_cv, nomCible_cv, date_cv) VALUES ($id_i_pnj,'$nom_pnj','$dernier_a_i','$nom_cible',NOW())";
									$mysqli->query($sql);
									
									// maj dernier tombé
									$sql = "INSERT INTO dernier_tombe (date_capture, id_perso_capture, camp_perso_capture, id_perso_captureur, camp_perso_captureur) VALUES (NOW(), '$id_cible', $clan_cible, $id_i_pnj, 0)";
									$mysqli->query($sql);
								}	
							}
							else {
								// la cible a esquivé l'attaque
								$sql = "UPDATE perso SET xp_perso=xp_perso+1, pi_perso=pi_perso+1 WHERE ID_perso='$dernier_a_i'";
								$mysqli->query($sql);
							
								// maj evenement
								$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($dernier_a_i,'<font color=$couleur_clan_cible><b>$nom_cible</b></font>','a esquivé l\'attaque de','$id_i_pnj','<b>$nom_pnj</b>','',NOW(),'0')";
								$mysqli->query($sql);
							}
						}
						else {
							// il n'est pas au CaC
							// recuperation des coordonnées de la cible
							$sql = "SELECT x_perso, y_perso FROM perso WHERE ID_perso=$id_pj";
							$res6 = $mysqli->query($sql);
							$t_cpj = $res6->fetch_assoc();
							
							$x_cible = $t_cpj["x_perso"];
							$y_cible = $t_cpj["y_perso"];
							
							// on se deplace vers ce perso
							while($pm_i > 0){
								
								// recuperation des nouvelles coordonées du pnj
								$sql = "SELECT x_i, y_i FROM instance_pnj WHERE idInstance_pnj=$id_i_pnj";
								$res8 = $mysqli->query($sql);
								$t_copnj = $res8->fetch_assoc();
								
								$x_i = $t_copnj["x_i"];
								$y_i = $t_copnj["y_i"];
								
								// si proximité de la cible perso, plus besoin de bouger
								if(proxi_perso_cible($mysqli,$x_i,$y_i,$dernier_a_i)){
									break;
								}
								
								// calcul des coordonnées de deplacement
								$x_d = calcul_vecteur_x($x_i,$x_cible);
								$y_d = calcul_vecteur_y($y_i,$y_cible);
							
								// deplacement vers la cible
								deplacement_vers_cible($mysqli, $x_d,$y_d,$x_cible,$y_cible,$id_i_pnj,$nom_pnj,$type_pnj,$dernier_a_i);
								$pm_i--;
							}
							
							// on est arrivé a proximité de la cible
							if(proxi_perso_cible($mysqli,$x_i,$y_i,$dernier_a_i)) {
								// on l'attaque
								// recuperation du nom et de la nation de la cible
								$sql = "SELECT nom_perso, bonus_perso, x_perso, y_perso, clan FROM perso WHERE id_perso='$dernier_a_i'";
								$res5 = $mysqli->query($sql);
								$t_n = $res5->fetch_assoc();
								
								$nom_cible 		= $t_n["nom_perso"];
								$x_cible 		= $t_n["x_perso"];
								$y_cible 		= $t_n["y_perso"];
								$bonus_def_pj 	= $t_n["bonus_perso"];
								$clan_cible 	= $t_n["clan"];
					
								// Récupération de la couleur associée au clan de la cible
								$couleur_clan_cible = couleur_clan($clan_cible);
							
								if (combat_pnj($precision_pnj,	$bonus_def_pj)) {
						
									$degats = mt_rand($degatMin, $degatMax);
									
									$sql = "UPDATE perso SET pv_perso=pv_perso-$degats WHERE ID_perso='$dernier_a_i'";
									$mysqli->query($sql);		
									
									// maj evenement
									$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_i_pnj,'<b>$nom_pnj</b>','a attaqué ','$dernier_a_i','<font color=$couleur_clan_cible><b>$nom_cible</b></font>',': $degats degats',NOW(),'0')";
									$mysqli->query($sql);
									
									// verification si la cible est morte ou non
									$sql = "SELECT pv_perso, x_perso, y_perso, xp_perso, pi_perso, pc_perso, or_perso, chef FROM perso WHERE ID_perso='$dernier_a_i'";
									$res2 = $mysqli->query($sql);
									$tab = $res2->fetch_assoc();
									
									$pv_cible 	= $tab["pv_perso"];
									$x_cible 	= $tab["x_perso"];
									$y_cible 	= $tab["y_perso"];
									$xp_cible	= $tab["xp_perso"];
									$pi_cible	= $tab['pi_perso'];
									$pc_cible	= $tab['pc_perso'];
									$or_cible	= $tab['or_perso'];
									$tp_perso	= $tab['chef'];
								
									if ($pv_cible <= 0) {
									
										// Calcul gains (po et xp)
										$perte_po = gain_po_mort($or_cible);
										
										// Chef
										if ($tp_perso == 1) {
											perte_etendard($mysqli, $id_cible, $x_cible, $y_cible);
											// Quand un chef meurt, il perd 5% de ses XPi et de ses PC
											// Calcul PI
											$pi_perdu 		= floor(($pi_cible * 5) / 100);
											
											// Calcul PC
											$pc_perdu		= floor(($pc_cible * 5) / 100);
											$pc_perso_fin	= $pc_cible - $pc_perdu;
										}
										else {
											$pi_perdu 		= floor(($pi_cible * 40) / 100);
											$pc_perso_fin = $pc_cible;
										}
					
										// MAJ perte xp/po/stat cible
										$sql = "UPDATE perso SET or_perso=or_perso-$perte_po, xp_perso=xp_perso-$pi_perdu, pi_perso=pi_perso-$pi_perdu, pc_perso=$pc_perso_fin, nb_mort=nb_mort+1 WHERE id_perso='$id_cible'";
										$mysqli->query($sql);
										
										// maj carte
										$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_cible' AND y_carte='$y_cible'";
										$mysqli->query($sql);
										
										// maj evenement
										$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_i_pnj,'<b>$nom_pnj</b>','<b>a capturé</b>','$dernier_a_i','<font color=$couleur_clan_cible><b>$nom_cible</b></font>','',NOW(),'0')";
										$mysqli->query($sql);
										
										// maj cv
										$sql = "INSERT INTO `cv` (IDActeur_cv, nomActeur_cv, IDCible_cv, nomCible_cv, date_cv) VALUES ($id_i_pnj,'$nom_pnj','$dernier_a_i','$nom_cible',NOW())";
										$mysqli->query($sql);
										
										// maj dernier tombé
										$sql = "INSERT INTO dernier_tombe (date_capture, id_perso_capture, camp_perso_capture, id_perso_captureur, camp_perso_captureur) VALUES (NOW(), '$id_cible', $clan_cible, $id_i_pnj, 0)";
										$mysqli->query($sql);
									}	
								}
								else {
									// la cible a esquivé l'attaque
									$sql = "UPDATE perso SET xp_perso=xp_perso+1, pi_perso=pi_perso+1 WHERE ID_perso='$dernier_a_i'";
									$mysqli->query($sql);
									
									// maj evenement
									$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($dernier_a_i,'<font color=$couleur_clan_cible><b>$nom_cible</b></font>','a esquivé l\'attaque de','$id_i_pnj','<b>$nom_pnj</b>','',NOW(),'0')";
									$mysqli->query($sql);
								}
							}
						}
					}
					else {
						// il n'est plus dans la visu
						// on recupere le perso le plus proche du pnj
						if($id_pj = proche_perso($mysqli,$x_i,$y_i,$perception)){
							$id_cible = $id_pj;
						
							// recuperation des coordonnées de la cible
							$sql = "SELECT x_perso, y_perso FROM perso WHERE ID_perso=$id_pj";
							$res7 = $mysqli->query($sql);
							$t_cpj = $res7->fetch_assoc();
							
							$x_cible = $t_cpj["x_perso"];
							$y_cible = $t_cpj["y_perso"];
							
							// on se deplace vers ce perso
							while($pm_i > 0){
								
								// recuperation des nouvelles coordonées du pnj
								$sql = "SELECT x_i, y_i FROM instance_pnj WHERE idInstance_pnj=$id_i_pnj";
								$res8 = $mysqli->query($sql);
								$t_copnj = $res8->fetch_assoc();
								
								$x_i = $t_copnj["x_i"];
								$y_i = $t_copnj["y_i"];
								
								if(proxi_perso_cible($mysqli,$x_i,$y_i,$id_pj)){
									break;
								}
								
								// calcul des coordonnées de deplacement
								$x_d = calcul_vecteur_x($x_i,$x_cible);
								$y_d = calcul_vecteur_y($y_i,$y_cible);
								
								// deplacement vers la cible
								deplacement_vers_cible($mysqli, $x_d,$y_d,$x_cible,$y_cible,$id_i_pnj,$nom_pnj,$type_pnj,$id_pj);
								$pm_i--;
							}
							
							// on est arrivé a proximité de la cible
							if(proxi_perso_cible($mysqli,$x_i,$y_i,$id_pj)) {
								// on l'attaque
								// recuperation du nom et de la nation de la cible
								$sql = "SELECT nom_perso, bonus_perso, x_perso, y_perso, clan FROM perso WHERE id_perso='$id_pj'";
								$res5 = $mysqli->query($sql);
								$t_n = $res5->fetch_assoc();
								
								$nom_cible 		= $t_n["nom_perso"];
								$x_cible 		= $t_n["x_perso"];
								$y_cible 		= $t_n["y_perso"];
								$bonus_def_pj 	= $t_n["bonus_perso"];
								$clan_cible 	= $t_n["clan"];
					
								// Récupération de la couleur associée au clan de la cible
								$couleur_clan_cible = couleur_clan($clan_cible);
							
								if (combat_pnj($precision_pnj,	$bonus_def_pj)) {
						
									$degats = mt_rand($degatMin, $degatMax);
									
									$sql = "UPDATE perso SET pv_perso=pv_perso-$degats WHERE ID_perso='$id_pj'";
									$mysqli->query($sql);		
									
									// maj evenement
									$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_i_pnj,'<b>$nom_pnj</b>','a attaqué ','$id_pj','<font color=$couleur_clan_cible><b>$nom_cible</b></font>',': $degats degats',NOW(),'0')";
									$mysqli->query($sql);
									
									// verification si la cible est morte ou non
									$sql = "SELECT pv_perso, x_perso, y_perso, xp_perso, pi_perso, pc_perso, or_perso, chef FROM perso WHERE ID_perso='$id_pj'";
									$res2 = $mysqli->query($sql);
									$tab = $res2->fetch_assoc();
									
									$pv_cible 	= $tab["pv_perso"];
									$x_cible 	= $tab["x_perso"];
									$y_cible 	= $tab["y_perso"];
									$xp_cible 	= $tab["xp_perso"];
									$pi_cible	= $tab['pi_perso'];
									$pc_cible	= $tab['pc_perso'];
									$or_cible	= $tab['or_perso'];
									$tp_perso	= $tab['chef'];
								
									if ($pv_cible <= 0) {
									
										// Calcul gains (po et xp)
										$perte_po = gain_po_mort($or_cible);
										
										// Chef
										if ($tp_perso == 1) {
											perte_etendard($mysqli, $id_cible, $x_cible, $y_cible);
											// Quand un chef meurt, il perd 5% de ses XPi et de ses PC
											// Calcul PI
											$pi_perdu 		= floor(($pi_cible * 5) / 100);
											
											// Calcul PC
											$pc_perdu		= floor(($pc_cible * 5) / 100);
											$pc_perso_fin	= $pc_cible - $pc_perdu;
										}
										else {
											$pi_perdu 		= floor(($pi_cible * 40) / 100);
											$pc_perso_fin = $pc_cible;
										}
					
										// MAJ perte xp/po/stat cible
										$sql = "UPDATE perso SET or_perso=or_perso-$perte_po, xp_perso=xp_perso-$pi_perdu, pi_perso=pi_perso-$pi_perdu, pc_perso=$pc_perso_fin, nb_mort=nb_mort+1 WHERE id_perso='$id_cible'";
										$mysqli->query($sql);
										
										// maj carte
										$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_cible' AND y_carte='$y_cible'";
										$mysqli->query($sql);
										
										// maj evenement
										$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_i_pnj,'<b>$nom_pnj</b>','<b>a capturé</b>','$id_pj','<font color=$couleur_clan_cible><b>$nom_cible</b></font>','',NOW(),'0')";
										$mysqli->query($sql);
										
										// maj cv
										$sql = "INSERT INTO `cv` (IDActeur_cv, nomActeur_cv, IDCible_cv, nomCible_cv, date_cv) VALUES ($id_i_pnj,'$nom_pnj','$id_pj','$nom_cible',NOW())";
										$mysqli->query($sql);
										
										// maj dernier tombé
										$sql = "INSERT INTO dernier_tombe (date_capture, id_perso_capture, camp_perso_capture, id_perso_captureur, camp_perso_captureur) VALUES (NOW(), '$id_cible', $clan_cible, $id_i_pnj, 0)";
										$mysqli->query($sql);
									}	
								}
								else { // la cible a esquivé l'attaque
									$sql = "UPDATE perso SET xp_perso=xp_perso+1, pi_perso=pi_perso+1 WHERE ID_perso='$id_pj'";
									$mysqli->query($sql);
								
									// maj evenement
									$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_pj,'<font color=$couleur_clan_cible><b>$nom_cible</b></font>','a esquivé l\'attaque de','$id_i_pnj','<b>$nom_pnj</b>','',NOW(),'0')";
									$mysqli->query($sql);
								}
							}
						}
						else {
							
							// il n y a pas de perso dans sa visu
							// il bouge au hasard
							while($pm_i > 0 && !proche_perso($mysqli,$x_i,$y_i,$perception)){
								
								// recuperation des nouvelles coordonées du pnj
								$sql = "SELECT x_i, y_i FROM instance_pnj WHERE idInstance_pnj=$id_i_pnj";
								$res8 = $mysqli->query($sql);
								$t_copnj = $res8->fetch_assoc();
								
								$x_i = $t_copnj["x_i"];
								$y_i = $t_copnj["y_i"];

								deplacement_hasard($mysqli,$x_i,$y_i,$id_i_pnj,$type_pnj,$nom_pnj);
								$pm_i--;
							}
						}
					}
				}
				else {
					
					echo "il n'a pas été attaqué<br>";
					
					// il n'a pas été attaqué
					// deplacement du pnj au hasard
					while($pm_i > 0){
						
						// recuperation des nouvelles coordonées du pnj
						$sql = "SELECT x_i, y_i FROM instance_pnj WHERE idInstance_pnj=$id_i_pnj";
						$res8 = $mysqli->query($sql);
						$t_copnj = $res8->fetch_assoc();
						
						$x_i = $t_copnj["x_i"];
						$y_i = $t_copnj["y_i"];

						deplacement_hasard($mysqli, $x_i,$y_i,$id_i_pnj,$type_pnj,$nom_pnj);
						$pm_i--;
					}
				}
				break;
				
			// pnj agressif
			case(2):
				
				// on regarde si il a été attaqué au tour d'avant
				if ($dernier_a_i) {
					
					//on verifie si le perso est toujours dans la visu du pnj
					if ($id_pj = perso_visu_pnj($mysqli, $x_i,$y_i,$perception,$dernier_a_i)) {
						$id_cible = $dernier_a_i;
					
						// on regarde si le perso est au CaC
						if(proxi_perso_cible($mysqli,$x_i,$y_i,$dernier_a_i)){
							
							// on l'attaque
							// recuperation du nom et de la nation de la cible
							$sql = "SELECT nom_perso, bonus_perso, x_perso, y_perso, clan FROM perso WHERE id_perso='$dernier_a_i'";
							$res5 = $mysqli->query($sql);
							$t_n = $res5->fetch_assoc();
							
							$nom_cible 		= $t_n["nom_perso"];
							$x_cible 		= $t_n["x_perso"];
							$y_cible 		= $t_n["y_perso"];
							$bonus_def_pj 	= $t_n["bonus_perso"];
							$clan_cible 	= $t_n["clan"];
					
							// Récupération de la couleur associée au clan de la cible
							$couleur_clan_cible = couleur_clan($clan_cible);
							
							if (combat_pnj($precision_pnj,	$bonus_def_pj)) {
						
								$degats = mt_rand($degatMin, $degatMax);
								
								$sql = "UPDATE perso SET pv_perso=pv_perso-$degats WHERE id_perso='$dernier_a_i'";
								$mysqli->query($sql);		
								
								// maj evenement
								$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_i_pnj,'<b>$nom_pnj</b>','a attaqué ','$dernier_a_i','<font color=$couleur_clan_cible><b>$nom_cible</b></font>',': $degats degats',NOW(),'0')";
								$mysqli->query($sql);
								
								// verification si la cible est morte ou non
								$sql = "SELECT pv_perso, x_perso, y_perso, xp_perso, pi_perso, pc_perso, or_perso, chef FROM perso WHERE ID_perso='$dernier_a_i'";
								$res2 = $mysqli->query($sql);
								$tab = $res2->fetch_assoc();
								
								$pv_cible 	= $tab["pv_perso"];
								$x_cible 	= $tab["x_perso"];
								$y_cible 	= $tab["y_perso"];
								$xp_cible 	= $tab["xp_perso"];
								$pi_cible	= $tab['pi_perso'];
								$pc_cible	= $tab['pc_perso'];
								$or_cible	= $tab['or_perso'];
								$tp_perso	= $tab['chef'];
							
								if ($pv_cible <= 0) {
								
									// Calcul gains (po et xp)
									$perte_po = gain_po_mort($or_cible);
									
									// Chef
									if ($tp_perso == 1) {
										perte_etendard($mysqli, $id_cible, $x_cible, $y_cible);
										// Quand un chef meurt, il perd 5% de ses XPi et de ses PC
										// Calcul PI
										$pi_perdu 		= floor(($pi_cible * 5) / 100);
										
										// Calcul PC
										$pc_perdu		= floor(($pc_cible * 5) / 100);
										$pc_perso_fin	= $pc_cible - $pc_perdu;
									}
									else {
										$pi_perdu 		= floor(($pi_cible * 40) / 100);
										$pc_perso_fin = $pc_cible;
									}
				
									// MAJ perte xp/po/stat cible
									$sql = "UPDATE perso SET or_perso=or_perso-$perte_po, xp_perso=xp_perso-$pi_perdu, pi_perso=pi_perso-$pi_perdu, pc_perso=$pc_perso_fin, nb_mort=nb_mort+1 WHERE id_perso='$id_cible'";
									$mysqli->query($sql);
									
									// maj carte
									$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_cible' AND y_carte='$y_cible'";
									$mysqli->query($sql);
									
									// maj evenement
									$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_i_pnj,'<b>$nom_pnj</b>','<b>a capturé</b>','$dernier_a_i','<font color=$couleur_clan_cible><b>$nom_cible</b></font>','',NOW(),'0')";
									$mysqli->query($sql);
									
									// maj cv
									$sql = "INSERT INTO `cv` (IDActeur_cv, nomActeur_cv, IDCible_cv, nomCible_cv, date_cv) VALUES ($id_i_pnj,'$nom_pnj','$dernier_a_i','$nom_cible',NOW())";
									$mysqli->query($sql);
									
									// maj dernier tombé
									$sql = "INSERT INTO dernier_tombe (date_capture, id_perso_capture, camp_perso_capture, id_perso_captureur, camp_perso_captureur) VALUES (NOW(), '$id_cible', $clan_cible, $id_i_pnj, 0)";
									$mysqli->query($sql);
								}	
							}
							else {
								// la cible a esquivé l'attaque
								$sql = "UPDATE perso SET xp_perso=xp_perso+1, pi_perso=pi_perso+1 WHERE ID_perso='$dernier_a_i'";
								$mysqli->query($sql);
								
								// maj evenement
								$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($dernier_a_i,'<font color=$couleur_clan_cible><b>$nom_cible</b></font>','a esquivé l\'attaque de','$id_i_pnj','<b>$nom_pnj</b>','',NOW(),'0')";
								$mysqli->query($sql);
							}
						}
						else {
							// il n'est pas au CaC
							// recuperation des coordonnées de la cible
							$sql = "SELECT x_perso, y_perso FROM perso WHERE ID_perso=$id_pj";
							$res6 = $mysqli->query($sql);
							$t_cpj = $res6->fetch_assoc();
							
							$x_cible = $t_cpj["x_perso"];
							$y_cible = $t_cpj["y_perso"];
							
							// on se deplace vers ce perso
							while($pm_i > 0){
								
								// recuperation des nouvelles coordonées du pnj
								$sql = "SELECT x_i, y_i FROM instance_pnj WHERE idInstance_pnj=$id_i_pnj";
								$res8 = $mysqli->query($sql);
								$t_copnj = $res8->fetch_assoc();
								
								$x_i = $t_copnj["x_i"];
								$y_i = $t_copnj["y_i"];
								
								if(proxi_perso_cible($mysqli,$x_i,$y_i,$dernier_a_i)){
									break;
								}
								
								// calcul des coordonnées de deplacement
								$x_d = calcul_vecteur_x($x_i,$x_cible);
								$y_d = calcul_vecteur_y($y_i,$y_cible);
							
								// deplacement vers la cible
								deplacement_vers_cible($mysqli, $x_d,$y_d,$x_cible,$y_cible,$id_i_pnj,$nom_pnj,$type_pnj,$dernier_a_i);
								$pm_i--;
							}
							
							// on est arrivé a proximité de la cible
							if(proxi_perso_cible($mysqli,$x_i,$y_i,$dernier_a_i)) {
								// on l'attaque
								// recuperation du nom et de la nation de la cible
								$sql = "SELECT nom_perso, bonus_perso, x_perso, y_perso, clan FROM perso WHERE id_perso='$dernier_a_i'";
								$res5 = $mysqli->query($sql);
								$t_n = $res5->fetch_assoc();
								
								$nom_cible 		= $t_n["nom_perso"];
								$x_cible 		= $t_n["x_perso"];
								$y_cible 		= $t_n["y_perso"];
								$bonus_def_pj 	= $t_n["bonus_perso"];
								$clan_cible 	= $t_n["clan"];
					
								// Récupération de la couleur associée au clan de la cible
								$couleur_clan_cible = couleur_clan($clan_cible);
								
								if (combat_pnj($precision_pnj,	$bonus_def_pj)) {
						
									$degats = mt_rand($degatMin, $degatMax);
									
									$sql = "UPDATE perso SET pv_perso=pv_perso-$degats WHERE ID_perso='$dernier_a_i'";
									$mysqli->query($sql);		
									
									// maj evenement
									$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_i_pnj,'<b>$nom_pnj</b>','a attaqué ','$dernier_a_i','<font color=$couleur_clan_cible><b>$nom_cible</b></font>',': $degats degats',NOW(),'0')";
									$mysqli->query($sql);
									
									// verification si la cible est morte ou non
									$sql = "SELECT pv_perso, x_perso, y_perso, xp_perso, pi_perso, pc_perso, or_perso, chef FROM perso WHERE ID_perso='$dernier_a_i'";
									$res2 = $mysqli->query($sql);
									$tab = $res2->fetch_assoc();
									
									$pv_cible 	= $tab["pv_perso"];
									$x_cible 	= $tab["x_perso"];
									$y_cible 	= $tab["y_perso"];
									$xp_cible 	= $tab["xp_perso"];
									$pi_cible	= $tab['pi_perso'];
									$pc_cible	= $tab['pc_perso'];
									$or_cible	= $tab['or_perso'];
									$tp_perso	= $tab['chef'];
								
									if ($pv_cible <= 0) {
									
										// Calcul gains (po et xp)
										$perte_po = gain_po_mort($or_cible);
										
										// Chef
										if ($tp_perso == 1) {
											perte_etendard($mysqli, $id_cible, $x_cible, $y_cible);
											// Quand un chef meurt, il perd 5% de ses XPi et de ses PC
											// Calcul PI
											$pi_perdu 		= floor(($pi_cible * 5) / 100);
											
											// Calcul PC
											$pc_perdu		= floor(($pc_cible * 5) / 100);
											$pc_perso_fin	= $pc_cible - $pc_perdu;
										}
										else {
											$pi_perdu 		= floor(($pi_cible * 40) / 100);
											$pc_perso_fin = $pc_cible;
										}
					
										// MAJ perte xp/po/stat cible
										$sql = "UPDATE perso SET or_perso=or_perso-$perte_po, xp_perso=xp_perso-$pi_perdu, pi_perso=pi_perso-$pi_perdu, pc_perso=$pc_perso_fin, nb_mort=nb_mort+1 WHERE id_perso='$id_cible'";
										$mysqli->query($sql);
										
										// maj carte
										$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_cible' AND y_carte='$y_cible'";
										$mysqli->query($sql);
										
										// maj evenement
										$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_i_pnj,'<b>$nom_pnj</b>','<b>a capturé</b>','$dernier_a_i','<font color=$couleur_clan_cible><b>$nom_cible</b></font>','',NOW(),'0')";
										$mysqli->query($sql);
										
										// maj cv
										$sql = "INSERT INTO `cv` (IDActeur_cv, nomActeur_cv, IDCible_cv, nomCible_cv, date_cv) VALUES ($id_i_pnj,'$nom_pnj','$dernier_a_i','$nom_cible',NOW())";
										$mysqli->query($sql);
										
										// maj dernier tombé
										$sql = "INSERT INTO dernier_tombe (date_capture, id_perso_capture, camp_perso_capture, id_perso_captureur, camp_perso_captureur) VALUES (NOW(), '$id_cible', $clan_cible, $id_i_pnj, 0)";
										$mysqli->query($sql);
									}	
								}
								else {
									// la cible a esquivé l'attaque
									$sql = "UPDATE perso SET xp_perso=xp_perso+1, pi_perso=pi_perso+1 WHERE ID_perso='$dernier_a_i'";
									$mysqli->query($sql);
									
									// maj evenement
									$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($dernier_a_i,'<font color=$couleur_clan_cible><b>$nom_cible</b></font>','a esquivé l\'attaque de','$id_i_pnj','<b>$nom_pnj</b>','',NOW(),'0')";
									$mysqli->query($sql);
								}
							}
						}
					}
					else {
						// il n'est plus dans la visu
						// on recupere le perso le plus proche du pnj
						if($id_pj = proche_perso($mysqli,$x_i,$y_i,$perception)){
							$id_cible = $id_pj;
						
							// -- TODO -- //
							// verif ami des animaux //
							//if(est_ami_animaux($id_perso){
							//
							//}
						
							// recuperation des coordonnées de la cible
							$sql = "SELECT x_perso, y_perso FROM perso WHERE ID_perso=$id_pj";
							$res7 = $mysqli->query($sql);
							$t_cpj = $res7->fetch_assoc();
							
							$x_cible = $t_cpj["x_perso"];
							$y_cible = $t_cpj["y_perso"];
							
							// on se deplace vers ce perso
							while($pm_i > 0){
								
								// recuperation des nouvelles coordonées du pnj
								$sql = "SELECT x_i, y_i FROM instance_pnj WHERE idInstance_pnj='$id_i_pnj'";
								$res8 = $mysqli->query($sql);
								$t_copnj = $res8->fetch_assoc();
								
								$x_i = $t_copnj["x_i"];
								$y_i = $t_copnj["y_i"];
								
								if(proxi_perso_cible($mysqli, $x_i,$y_i,$id_pj)){
									break;
								}
								
								// calcul des coordonnées de deplacement
								$x_d = calcul_vecteur_x($x_i,$x_cible);
								$y_d = calcul_vecteur_y($y_i,$y_cible);
								
								// deplacement vers la cible
								deplacement_vers_cible($mysqli, $x_d,$y_d,$x_cible,$y_cible,$id_i_pnj,$nom_pnj,$type_pnj,$id_pj);
								$pm_i--;
							}
							
							// on est arrivé a proximité de la cible
							if(proxi_perso_cible($mysqli, $x_i,$y_i,$id_pj)) {
								
								// on l'attaque
								// recuperation du nom et de la nation de la cible
								$sql = "SELECT nom_perso, bonus_perso, x_perso, y_perso, clan FROM perso WHERE id_perso='$id_pj'";
								$res5 = $mysqli->query($sql);
								$t_n = $res5->fetch_assoc();
								
								$nom_cible 		= $t_n["nom_perso"];
								$x_cible 		= $t_n["x_perso"];
								$y_cible 		= $t_n["y_perso"];
								$bonus_def_pj 	= $t_n["bonus_perso"];
								$clan_cible 	= $t_n["clan"];
					
								// Récupération de la couleur associée au clan de la cible
								$couleur_clan_cible = couleur_clan($clan_cible);
								
								if (combat_pnj($precision_pnj,	$bonus_def_pj)) {
						
									$degats = mt_rand($degatMin, $degatMax);
									
									$sql = "UPDATE perso SET pv_perso=pv_perso-$degats WHERE ID_perso='$id_pj'";
									$mysqli->query($sql);		
									
									// maj evenement
									$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_i_pnj,'<b>$nom_pnj</b>','a attaqué ','$id_pj','<font color=$couleur_clan_cible><b>$nom_cible</b></font>',': $degats degats',NOW(),'0')";
									$mysqli->query($sql);
									
									// verification si la cible est morte ou non
									$sql = "SELECT pv_perso, x_perso, y_perso, xp_perso, pi_perso, pc_perso, or_perso, chef FROM perso WHERE ID_perso='$id_pj'";
									$res2 = $mysqli->query($sql);
									$tab = $res2->fetch_assoc();
									
									$pv_cible 	= $tab["pv_perso"];
									$x_cible 	= $tab["x_perso"];
									$y_cible 	= $tab["y_perso"];
									$xp_cible 	= $tab["xp_perso"];
									$pi_cible	= $tab['pi_perso'];
									$pc_cible	= $tab['pc_perso'];
									$or_cible	= $tab['or_perso'];
									$tp_perso	= $tab['chef'];
								
									if ($pv_cible <= 0) {
									
										// Calcul gains (po et xp)
										$perte_po = gain_po_mort($or_cible);
										
										// Chef
										if ($tp_perso == 1) {
											perte_etendard($mysqli, $id_cible, $x_cible, $y_cible);
											// Quand un chef meurt, il perd 5% de ses XPi et de ses PC
											// Calcul PI
											$pi_perdu 		= floor(($pi_cible * 5) / 100);
											
											// Calcul PC
											$pc_perdu		= floor(($pc_cible * 5) / 100);
											$pc_perso_fin	= $pc_cible - $pc_perdu;
										}
										else {
											$pi_perdu 		= floor(($pi_cible * 40) / 100);
											$pc_perso_fin = $pc_cible;
										}
					
										// MAJ perte xp/po/stat cible
										$sql = "UPDATE perso SET or_perso=or_perso-$perte_po, xp_perso=xp_perso-$pi_perdu, pi_perso=pi_perso-$pi_perdu, pc_perso=$pc_perso_fin, nb_mort=nb_mort+1 WHERE id_perso='$id_cible'";
										$mysqli->query($sql);
										
										// maj carte
										$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_cible' AND y_carte='$y_cible'";
										$mysqli->query($sql);
										
										// maj evenement
										$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_i_pnj,'<b>$nom_pnj</b>','<b>a capturé</b>','$id_pj','<font color=$couleur_clan_cible><b>$nom_cible</b></font>','',NOW(),'0')";
										$mysqli->query($sql);
										
										// maj cv
										$sql = "INSERT INTO `cv` (IDActeur_cv, nomActeur_cv, IDCible_cv, nomCible_cv, date_cv) VALUES ($id_i_pnj,'$nom_pnj','$id_pj','$nom_cible',NOW())";
										$mysqli->query($sql);
										
										// maj dernier tombé
										$sql = "INSERT INTO dernier_tombe (date_capture, id_perso_capture, camp_perso_capture, id_perso_captureur, camp_perso_captureur) VALUES (NOW(), '$id_cible', $clan_cible, $id_i_pnj, 0)";
										$mysqli->query($sql);
									}	
								}
								else {
									// la cible a esquivé l'attaque
									$sql = "UPDATE perso SET xp_perso=xp_perso+1, pi_perso=pi_perso+1 WHERE ID_perso='$id_pj'";
									$mysqli->query($sql);
									
									// maj evenement
									$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_pj,'<font color=$couleur_clan_cible><b>$nom_cible</b></font>','a esquivé l\'attaque de','$id_i_pnj','<b>$nom_pnj</b>','',NOW(),'0')";
									$mysqli->query($sql);
								}
							}
						}
						else {
							// il n y a pas de perso dans sa visu
							// il bouge au hasard
							while($pm_i > 0 && !proche_perso($mysqli,$x_i,$y_i,$perception)){
								// recuperation des nouvelles coordonées du pnj
								$sql = "SELECT x_i, y_i FROM instance_pnj WHERE idInstance_pnj=$id_i_pnj";
								$res8 = $mysqli->query($sql);
								$t_copnj = $res8->fetch_assoc();
								
								$x_i = $t_copnj["x_i"];
								$y_i = $t_copnj["y_i"];

								deplacement_hasard($mysqli,$x_i,$y_i,$id_i_pnj,$type_pnj,$nom_pnj);
								$pm_i--;
							}
							
							// on recupere le perso le plus proche du pnj
							if($id_pj = proche_perso($mysqli,$x_i,$y_i,$perception)){
								$id_cible = $id_pj;
							
								// recuperation des coordonnées de la cible
								$sql = "SELECT x_perso, y_perso FROM perso WHERE ID_perso=$id_pj";
								$res7 = $mysqli->query($sql);
								$t_cpj = $res7->fetch_assoc();
								
								$x_cible = $t_cpj["x_perso"];
								$y_cible = $t_cpj["y_perso"];
								
								// on se deplace vers ce perso
								while($pm_i > 0){
									
									// recuperation des nouvelles coordonées du pnj
									$sql = "SELECT x_i, y_i FROM instance_pnj WHERE idInstance_pnj=$id_i_pnj";
									$res8 = $mysqli->query($sql);
									$t_copnj = $res8->fetch_assoc();
									
									$x_i = $t_copnj["x_i"];
									$y_i = $t_copnj["y_i"];
									
									if(proxi_perso_cible($mysqli, $x_i,$y_i,$id_pj)){
										break;
									}
									
									// calcul des coordonnées de deplacement
									$x_d = calcul_vecteur_x($x_i,$x_cible);
									$y_d = calcul_vecteur_y($y_i,$y_cible);
									
									// deplacement vers la cible
									deplacement_vers_cible($mysqli, $x_d,$y_d,$x_cible,$y_cible,$id_i_pnj,$nom_pnj,$type_pnj,$id_pj);
									$pm_i--;
								}
								
								// on est arrivé a proximité de la cible
								if(proxi_perso_cible($mysqli, $x_i,$y_i,$id_pj)) {
									
									// on l'attaque
									// recuperation du nom et de la nation de la cible
									$sql = "SELECT nom_perso, bonus_perso, x_perso, y_perso, clan FROM perso WHERE id_perso='$id_pj'";
									$res5 = $mysqli->query($sql);
									$t_n = $res5->fetch_assoc();
									
									$nom_cible 		= $t_n["nom_perso"];
									$x_cible 		= $t_n["x_perso"];
									$y_cible 		= $t_n["y_perso"];
									$bonus_def_pj 	= $t_n["bonus_perso"];
									$clan_cible 	= $t_n["clan"];
					
									// Récupération de la couleur associée au clan de la cible
									$couleur_clan_cible = couleur_clan($clan_cible);
									
									if (combat_pnj($precision_pnj,	$bonus_def_pj)) {
						
										$degats = mt_rand($degatMin, $degatMax);
										
										$sql = "UPDATE perso SET pv_perso=pv_perso-$degats WHERE ID_perso='$id_pj'";
										$mysqli->query($sql);		
										
										// maj evenement
										$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_i_pnj,'<b>$nom_pnj</b>','a attaqué ','$id_pj','<font color=$couleur_clan_cible><b>$nom_cible</b></font>',': $degats degats',NOW(),'0')";
										$mysqli->query($sql);
										
										// verification si la cible est morte ou non
										$sql = "SELECT pv_perso, x_perso, y_perso, xp_perso, pi_perso, pc_perso, or_perso, chef FROM perso WHERE ID_perso='$id_pj'";
										$res2 = $mysqli->query($sql);
										$tab = $res2->fetch_assoc();
										
										$pv_cible 	= $tab["pv_perso"];
										$x_cible 	= $tab["x_perso"];
										$y_cible 	= $tab["y_perso"];
										$xp_cible 	= $tab["xp_perso"];
										$pi_cible	= $tab['pi_perso'];
										$pc_cible	= $tab['pc_perso'];
										$or_cible	= $tab['or_perso'];
										$tp_perso	= $tab['chef'];
									
										if ($pv_cible <= 0) {
										
											// Calcul gains (po et xp)
											$perte_po = gain_po_mort($or_cible);
											
											// Chef
											if ($tp_perso == 1) {
												perte_etendard($mysqli, $id_cible, $x_cible, $y_cible);
												// Quand un chef meurt, il perd 5% de ses XPi et de ses PC
												// Calcul PI
												$pi_perdu 		= floor(($pi_cible * 5) / 100);
												
												// Calcul PC
												$pc_perdu		= floor(($pc_cible * 5) / 100);
												$pc_perso_fin	= $pc_cible - $pc_perdu;
											}
											else {
												$pi_perdu 		= floor(($pi_cible * 40) / 100);
												$pc_perso_fin = $pc_cible;
											}
						
											// MAJ perte xp/po/stat cible
											$sql = "UPDATE perso SET or_perso=or_perso-$perte_po, xp_perso=xp_perso-$pi_perdu, pi_perso=pi_perso-$pi_perdu, pc_perso=$pc_perso_fin, nb_mort=nb_mort+1 WHERE id_perso='$id_cible'";
											$mysqli->query($sql);
											
											// maj carte
											$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_cible' AND y_carte='$y_cible'";
											$mysqli->query($sql);
											
											// maj evenement
											$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_i_pnj,'<b>$nom_pnj</b>','<b>a capturé</b>','$id_pj','<font color=$couleur_clan_cible><b>$nom_cible</b></font>','',NOW(),'0')";
											$mysqli->query($sql);
											
											// maj cv
											$sql = "INSERT INTO `cv` (IDActeur_cv, nomActeur_cv, IDCible_cv, nomCible_cv, date_cv) VALUES ($id_i_pnj,'$nom_pnj','$id_pj','$nom_cible',NOW())";
											$mysqli->query($sql);
											
											// maj dernier tombé
											$sql = "INSERT INTO dernier_tombe (date_capture, id_perso_capture, camp_perso_capture, id_perso_captureur, camp_perso_captureur) VALUES (NOW(), '$id_cible', $clan_cible, $id_i_pnj, 0)";
											$mysqli->query($sql);
										}	
									}
									else {
										// la cible a esquivé l'attaque
										$sql = "UPDATE perso SET xp_perso=xp_perso+1, pi_perso=pi_perso+1 WHERE ID_perso='$id_pj'";
										$mysqli->query($sql);
										
										// maj evenement
										$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_pj,'<font color=$couleur_clan_cible><b>$nom_cible</b></font>','a esquivé l\'attaque de','$id_i_pnj','<b>$nom_pnj</b>','',NOW(),'0')";
										$mysqli->query($sql);
									}
								}
							}
						}
					}
				}
				else {
					// il n'a pas été attaqué
					// on recupere le perso le plus proche du pnj
					if($id_pj = proche_perso($mysqli,$x_i,$y_i,$perception)){
						$id_cible = $id_pj;
						
						// -- TODO -- //
						// verif ami des animaux //
						//if(est_ami_animaux($id_perso){
						//
						//}
						
						// recuperation des coordonnées de la cible
						$sql = "SELECT x_perso, y_perso FROM perso WHERE ID_perso='$id_pj'";
						$res7 = $mysqli->query($sql);
						$t_cpj = $res7->fetch_assoc();
						
						$x_cible = $t_cpj["x_perso"];
						$y_cible = $t_cpj["y_perso"];
							
						// on se deplace vers ce perso
						while($pm_i > 0){
								
							// recuperation des nouvelles coordonées du pnj
							$sql = "SELECT x_i, y_i FROM instance_pnj WHERE idInstance_pnj='$id_i_pnj'";
							$res8 = $mysqli->query($sql);
							$t_copnj = $res8->fetch_assoc();
							
							$x_i = $t_copnj["x_i"];
							$y_i = $t_copnj["y_i"];
								
							if(proxi_perso_cible($mysqli, $x_i,$y_i,$id_pj)){
								break;
							}
								
							// calcul des coordonnées de deplacement
							$x_d = calcul_vecteur_x($x_i,$x_cible);
							$y_d = calcul_vecteur_y($y_i,$y_cible);
							
							// deplacement vers la cible
							deplacement_vers_cible($mysqli, $x_d,$y_d,$x_cible,$y_cible,$id_i_pnj,$nom_pnj,$type_pnj,$id_pj);
							$pm_i--;
						}
						
						// on est arrivé a proximité de la cible
						if(proxi_perso_cible($mysqli, $x_i,$y_i,$id_pj)) {
							// on l'attaque
							// recuperation du nom et de la nation de la cible
							$sql = "SELECT nom_perso, bonus_perso, x_perso, y_perso, clan FROM perso WHERE id_perso='$id_pj'";
							$res5 = $mysqli->query($sql);
							$t_n = $res5->fetch_assoc();
							
							$nom_cible 		= $t_n["nom_perso"];
							$x_cible 		= $t_n["x_perso"];
							$y_cible 		= $t_n["y_perso"];
							$bonus_def_pj 	= $t_n["bonus_perso"];
							$clan_cible 	= $t_n["clan"];
					
							// Récupération de la couleur associée au clan de la cible
							$couleur_clan_cible = couleur_clan($clan_cible);

								
							if (combat_pnj($precision_pnj,	$bonus_def_pj)) {
						
								$degats = mt_rand($degatMin, $degatMax);
								
								$sql = "UPDATE perso SET pv_perso=pv_perso-$degats WHERE ID_perso='$id_pj'";
								$mysqli->query($sql);		
								
								// maj evenement
								$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_i_pnj,'<b>$nom_pnj</b>','a attaqué ','$id_pj','<font color=$couleur_clan_cible><b>$nom_cible</b></font>',': $degats degats',NOW(),'0')";
								$mysqli->query($sql);
								
								// verification si la cible est morte ou non
								$sql = "SELECT pv_perso, x_perso, y_perso, xp_perso, pi_perso, pc_perso, or_perso, chef FROM perso WHERE ID_perso='$id_pj'";
								$res2 = $mysqli->query($sql);
								$tab = $res2->fetch_assoc();
								
								$pv_cible 	= $tab["pv_perso"];
								$x_cible 	= $tab["x_perso"];
								$y_cible 	= $tab["y_perso"];
								$xp_cible 	= $tab["xp_perso"];
								$pi_cible	= $tab['pi_perso'];
								$pc_cible	= $tab['pc_perso'];
								$or_cible	= $tab['or_perso'];
								$tp_perso	= $tab['chef'];
							
								if ($pv_cible <= 0) {
								
									// Calcul gains (po et xp)
									$perte_po = gain_po_mort($or_cible);
									
									// Chef
									if ($tp_perso == 1) {
										perte_etendard($mysqli, $id_cible, $x_cible, $y_cible);
										// Quand un chef meurt, il perd 5% de ses XPi et de ses PC
										// Calcul PI
										$pi_perdu 		= floor(($pi_cible * 5) / 100);
										
										// Calcul PC
										$pc_perdu		= floor(($pc_cible * 5) / 100);
										$pc_perso_fin	= $pc_cible - $pc_perdu;
									}
									else {
										$pi_perdu 		= floor(($pi_cible * 40) / 100);
										$pc_perso_fin = $pc_cible;
									}
				
									// MAJ perte xp/po/stat cible
									$sql = "UPDATE perso SET or_perso=or_perso-$perte_po, xp_perso=xp_perso-$pi_perdu, pi_perso=pi_perso-$pi_perdu, pc_perso=$pc_perso_fin, nb_mort=nb_mort+1 WHERE id_perso='$id_cible'";
									$mysqli->query($sql);
									
									// maj carte
									$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_cible' AND y_carte='$y_cible'";
									$mysqli->query($sql);
									
									// maj evenement
									$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_i_pnj,'<b>$nom_pnj</b>','<b>a capturé</b>','$id_pj','<font color=$couleur_clan_cible><b>$nom_cible</b></font>','',NOW(),'0')";
									$mysqli->query($sql);
									
									// maj cv
									$sql = "INSERT INTO `cv` (IDActeur_cv, nomActeur_cv, IDCible_cv, nomCible_cv, date_cv) VALUES ($id_i_pnj,'$nom_pnj','$id_pj','$nom_cible',NOW())";
									$mysqli->query($sql);
									
									// maj dernier tombé
									$sql = "INSERT INTO dernier_tombe (date_capture, id_perso_capture, camp_perso_capture, id_perso_captureur, camp_perso_captureur) VALUES (NOW(), '$id_cible', $clan_cible, $id_i_pnj, 0)";
									$mysqli->query($sql);
								}	
							}
							else {
								// la cible a esquivé l'attaque
								$sql = "UPDATE perso SET xp_perso=xp_perso+1, pi_perso=pi_perso+1 WHERE ID_perso='$id_pj'";
								$mysqli->query($sql);
							
								// maj evenement
								$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_pj,'<font color=$couleur_clan_cible><b>$nom_cible</b></font>','a esquivé l\'attaque de','$id_i_pnj','<b>$nom_pnj</b>','',NOW(),'0')";
								$mysqli->query($sql);
							}
						}
					}
					else { 
						
						// il n y a pas de perso dans sa visu
						// il bouge au hasard
						while($pm_i > 0 && !proche_perso($mysqli,$x_i,$y_i,$perception)){
							// recuperation des nouvelles coordonées du pnj
							$sql = "SELECT x_i, y_i FROM instance_pnj WHERE idInstance_pnj='$id_i_pnj'";
							$res8 = $mysqli->query($sql);
							$t_copnj = $res8->fetch_assoc();
							
							$x_i = $t_copnj["x_i"];
							$y_i = $t_copnj["y_i"];

							deplacement_hasard($mysqli,$x_i,$y_i,$id_i_pnj,$type_pnj,$nom_pnj);
							$pm_i--;
						}
						
						// on recupere le perso le plus proche du pnj
						if($id_pj = proche_perso($mysqli,$x_i,$y_i,$perception)){
							$id_cible = $id_pj;
						
							// recuperation des coordonnées de la cible
							$sql = "SELECT x_perso, y_perso FROM perso WHERE ID_perso='$id_pj'";
							$res7 = $mysqli->query($sql);
							$t_cpj = $res7->fetch_assoc();
							
							$x_cible = $t_cpj["x_perso"];
							$y_cible = $t_cpj["y_perso"];
								
							// on se deplace vers ce perso
							while($pm_i > 0){
									
								// recuperation des nouvelles coordonées du pnj
								$sql = "SELECT x_i, y_i FROM instance_pnj WHERE idInstance_pnj='$id_i_pnj'";
								$res8 = $mysqli->query($sql);
								$t_copnj = $res8->fetch_assoc();
								
								$x_i = $t_copnj["x_i"];
								$y_i = $t_copnj["y_i"];
								
								if(proxi_perso_cible($mysqli, $x_i,$y_i,$id_pj)){
									break;
								}
								
								// calcul des coordonnées de deplacement
								$x_d = calcul_vecteur_x($x_i,$x_cible);
								$y_d = calcul_vecteur_y($y_i,$y_cible);
								
								// deplacement vers la cible
								deplacement_vers_cible($mysqli, $x_d,$y_d,$x_cible,$y_cible,$id_i_pnj,$nom_pnj,$type_pnj,$id_pj);
								$pm_i--;
							}
							
							// on est arrivé a proximité de la cible
							if(proxi_perso_cible($mysqli, $x_i,$y_i,$id_pj)) {
								
								// on l'attaque
								// recuperation du nom et de la nation de la cible
								$sql = "SELECT nom_perso, bonus_perso, x_perso, y_perso, clan FROM perso WHERE id_perso='$id_pj'";
								$res5 = $mysqli->query($sql);
								$t_n = $res5->fetch_assoc();
								
								$nom_cible 		= $t_n["nom_perso"];
								$x_cible 		= $t_n["x_perso"];
								$y_cible 		= $t_n["y_perso"];
								$bonus_def_pj 	= $t_n["bonus_perso"];
								$clan_cible 	= $t_n["clan"];
					
								// Récupération de la couleur associée au clan de la cible
								$couleur_clan_cible = couleur_clan($clan_cible);
								
								if (combat_pnj($precision_pnj,	$bonus_def_pj)) {
						
									$degats = mt_rand($degatMin, $degatMax);
									
									$sql = "UPDATE perso SET pv_perso=pv_perso-$degats WHERE ID_perso='$id_pj'";
									$mysqli->query($sql);		
									
									// maj evenement									
									$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_i_pnj,'<b>$nom_pnj</b>','a attaqué ','$id_pj','<font color=$couleur_clan_cible><b>$nom_cible</b></font>',': $degats degats',NOW(),'0')";
									$mysqli->query($sql);
									
									// verification si la cible est morte ou non
									$sql = "SELECT pv_perso, x_perso, y_perso, xp_perso, pi_perso, pc_perso, or_perso, chef FROM perso WHERE ID_perso='$id_pj'";
									$res2 = $mysqli->query($sql);
									$tab = $res2->fetch_assoc();
									
									$pv_cible 	= $tab["pv_perso"];
									$x_cible 	= $tab["x_perso"];
									$y_cible 	= $tab["y_perso"];
									$xp_cible 	= $tab["xp_perso"];
									$pi_cible	= $tab['pi_perso'];
									$pc_cible	= $tab['pc_perso'];
									$or_cible	= $tab['or_perso'];
									$tp_perso	= $tab['chef'];
								
									if ($pv_cible <= 0) {
									
										// Calcul gains (po et xp)
										$perte_po = gain_po_mort($or_cible);
										
										// Chef
										if ($tp_perso == 1) {
											perte_etendard($mysqli, $id_cible, $x_cible, $y_cible);
											// Quand un chef meurt, il perd 5% de ses XPi et de ses PC
											// Calcul PI
											$pi_perdu 		= floor(($pi_cible * 5) / 100);
											
											// Calcul PC
											$pc_perdu		= floor(($pc_cible * 5) / 100);
											$pc_perso_fin	= $pc_cible - $pc_perdu;
										}
										else {
											$pi_perdu 		= floor(($pi_cible * 40) / 100);
											$pc_perso_fin = $pc_cible;
										}
					
										// MAJ perte xp/po/stat cible
										$sql = "UPDATE perso SET or_perso=or_perso-$perte_po, xp_perso=xp_perso-$pi_perdu, pi_perso=pi_perso-$pi_perdu, pc_perso=$pc_perso_fin, nb_mort=nb_mort+1 WHERE id_perso='$id_cible'";
										$mysqli->query($sql);
										
										// maj carte
										$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_cible' AND y_carte='$y_cible'";
										$mysqli->query($sql);
										
										// maj evenement
										$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_i_pnj,'<b>$nom_pnj</b>','<b>a capturé</b>','$id_pj','<font color=$couleur_clan_cible><b>$nom_cible</b></font>','',NOW(),'0')";
										$mysqli->query($sql);
										
										// maj cv
										$sql = "INSERT INTO `cv` (IDActeur_cv, nomActeur_cv, IDCible_cv, nomCible_cv, date_cv) VALUES ($id_i_pnj,'$nom_pnj','$id_pj','$nom_cible',NOW())";
										$mysqli->query($sql);
										
										// maj dernier tombé
										$sql = "INSERT INTO dernier_tombe (date_capture, id_perso_capture, camp_perso_capture, id_perso_captureur, camp_perso_captureur) VALUES (NOW(), '$id_cible', $clan_cible, $id_i_pnj, 0)";
										$mysqli->query($sql);
									}	
								}
								else {
									// la cible a esquivé l'attaque
									$sql = "UPDATE perso SET xp_perso=xp_perso+1, pi_perso=pi_perso+1 WHERE ID_perso='$id_pj'";
									$mysqli->query($sql);
								
									// maj evenement
									$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_pj,'<font color=$couleur_clan_cible><b>$nom_cible</b></font>','a esquivé l\'attaque de','$id_i_pnj','<b>$nom_pnj</b>','',NOW(),'0')";
									$mysqli->query($sql);
								}
							}
						}
					}
				}
				
			break;
		}
		
		// maj deplacement_pnj
		$sql = "UPDATE instance_pnj SET deplace_i='1' WHERE idInstance_pnj='$id_i_pnj'";
		$mysqli->query($sql);
	}
}
?>
