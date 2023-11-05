<?php
session_start();
require_once("../fonctions.php");
require_once("f_carte.php");
require_once("f_combat.php");

$mysqli = db_connexion();

// Récupération de la clef secrete
$sql = "SELECT valeur_config FROM config_jeu WHERE code_config='clef_secrete'";
$res = $mysqli->query($sql);
$t = $res->fetch_assoc();

$clef_secrete = $t['valeur_config'];

if (isset($_GET['clef']) && $_GET['clef'] == $clef_secrete) {

	// Récupération de la liste des Forts et Fortins
	$sql_b = "SELECT id_instanceBat, nom_instance FROM instance_batiment WHERE id_batiment='8' OR id_batiment='9' ORDER BY id_instanceBat";
	$res_b = $mysqli->query($sql_b);

	while ($t_b = $res_b->fetch_assoc()) {
		
		$id_instance_bat 	= $t_b['id_instanceBat'];
		$nom_instance		= $t_b['nom_instance'];
		
		// Récupération des canons du batiment
		$sql_canon = "SELECT id_instance_canon, x_canon, y_canon, camp_canon, date_activation 
					FROM instance_batiment_canon
					WHERE id_instance_bat='$id_instance_bat'
					AND (date_activation is NULL OR date_activation < NOW())";
		$res_canon = $mysqli->query($sql_canon);

		while ($t_canon = $res_canon->fetch_assoc()) {
			
			$id_instance_canon 	= $t_canon['id_instance_canon'];
			$x_canon 			= $t_canon['x_canon'];
			$y_canon 			= $t_canon['y_canon'];
			$camp_canon 		= $t_canon['camp_canon'];
			$dla_canon			= $t_canon['date_activation'];
			
			$couleur_clan_canon = couleur_clan($camp_canon);
			
			echo "<br />* Canon ".$id_instance_canon." du Batiment ".$id_instance_bat." :<br />";
			
			$tableau_cibles = array();
			$perception_canon	= 5;
			
			// y a t-il un ennemi en visu
			$sql_v = "SELECT idPerso_carte, perso.type_perso FROM carte, perso  
						WHERE perso.id_perso = carte.idPerso_carte 
						AND occupee_carte='1' 
						AND x_carte >= $x_canon - $perception_canon
						AND x_carte <= $x_canon + $perception_canon
						AND y_carte >= $y_canon - $perception_canon
						AND y_carte <= $y_canon + $perception_canon
						AND idPerso_carte > 0 AND idPerso_carte < 50000
						AND perso.type_perso !=6
						AND clan != '$camp_canon'";
			$res_v = $mysqli->query($sql_v);
			
			while ($t_v = $res_v->fetch_assoc()) {
				
				$id_cible 		= $t_v['idPerso_carte'];
				
				array_push($tableau_cibles, $id_cible);
			}
			
			if (!empty($tableau_cibles)) {
				// Calcul nouvelle date activation canon
				$date = time();
				$new_dla = $date + DUREE_TOUR;
				// MAJ date_activation canon
				$sql = "UPDATE instance_batiment_canon SET date_activation=FROM_UNIXTIME($new_dla) WHERE id_instance_canon='$id_instance_canon'";
				$mysqli->query($sql);
			
				$id_perso_cible = $tableau_cibles[array_rand($tableau_cibles)];
				
				echo "<br /> Cible choisie : ".$id_perso_cible."<br />";
				
				if ($id_perso_cible != null && $id_perso_cible != 0) {
					
					$sql_c = "SELECT idJoueur_perso, nom_perso, type_perso, x_perso, y_perso, pi_perso, xp_perso, pc_perso, pv_perso, protec_perso, bonus_perso, or_perso, clan
								FROM perso WHERE id_perso = '$id_perso_cible';";
					$res_c = $mysqli->query($sql_c);
					$t_c = $res_c->fetch_assoc();
					
					$pv_cible 		= $t_c['pv_perso'];
					$bonus_cible 	= $t_c['bonus_perso'];
					$camp_cible		= $t_c['clan'];
					$idJoueur_cible	= $t_c['idJoueur_perso'];
					$protec_cible	= $t_c['protec_perso'];
					$type_cible		= $t_c['type_perso'];
					$x_cible		= $t_c['x_perso'];
					$y_cible		= $t_c['y_perso'];
					$or_cible		= $t_c['or_perso'];
					$nom_cible		= $t_c['nom_perso'];
					$pi_cible		= $t_c['pi_perso'];
					$xp_cible		= $t_c['xp_perso'];
					$pc_cible		= $t_c['pc_perso'];
					
					$couleur_clan_cible = couleur_clan($camp_cible);
					
					$precision_canon 	= 65;
					$nb_des_canon 		= 75;
					$valeur_des_canon 	= 6;
					
					// Vérifie si le joueur attaqué a coché l'envoi de mail
					$mail_info_joueur = verif_coche_mail($mysqli, $idJoueur_cible);
								
					if($mail_info_joueur){
						// Envoi du mail
						mail_attaque($mysqli, 'Canon', $id_perso_cible);
					}
					
					// -- Attaque cible
					// Calcul touche
					$touche = mt_rand(0,100);
					$precision_final = $precision_canon - $bonus_cible;
					
					if ($touche <= $precision_final) {
						
						// calcul degats arme
						$degats_final = calcul_des_attaque($nb_des_canon, $valeur_des_canon) - $protec_cible;
						
						// Cible autre artillerie
						if ($type_cible == 5 || $type_cible == 8) {
							// Bonus dégats 13D10
							$bonus_degats_canon = mt_rand(13, 130);
							$degats_final = $degats_final + $bonus_degats_canon;
						}
						
						if($degats_final < 0) {
							$degats_final = 0;
						}
						
						if ($touche <= 2) {
							// Coup critique ! Dégats X 2
							$degats_final = $degats_final * 2;
						}
						
						// mise a jour des pv et des malus de la cible
						$sql = "UPDATE perso SET pv_perso=pv_perso-$degats_final, bonus_perso=bonus_perso-2 WHERE id_perso='$id_perso_cible'";
						$mysqli->query($sql);
						
						// mise a jour de la table evenement
						$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_instance_bat,'<font color=$couleur_clan_canon><b>Canon</b></font>','a attaqué ','$id_perso_cible','<font color=$couleur_clan_cible><b>$nom_cible</b></font>',' ( Précision : $touche / $precision_final ; Dégâts : $degats_final )',NOW(),'0')";
						$mysqli->query($sql);
						
						// il est mort
						if ($pv_cible - $degats_final <= 0) {
							
							// on l'efface de la carte
							$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_cible' AND y_carte='$y_cible'";
							$mysqli->query($sql);

							// Calcul gains (po et xp)
							$perte_po = gain_po_mort($or_cible);
							
							// Chef
							if ($type_cible == 1) {
								
								perte_etendard($mysqli, $idJoueur_cible, $x_cible, $y_cible);
				
								// Quand un chef meurt, il perd 5% de ses XP,XPi et de ses PC
								// Calcul PI
								$pi_perdu 		= floor(($pi_cible * 5) / 100);
								
								// Calcul PC
								$pc_perdu		= floor(($pc_cible * 5) / 100);
								$pc_perso_fin	= $pc_cible - $pc_perdu;
							}
							else {
								$pi_perdu 		= floor(($pi_cible * 40) / 100);
								$xp_perso_fin = $xp_cible;
								$pc_perso_fin = $pc_cible;
							}

							// MAJ perte xp/po/stat cible
							$sql = "UPDATE perso SET or_perso=or_perso-$perte_po, xp_perso=xp_perso-$pi_perdu, pi_perso=pi_perso-$pi_perdu, pc_perso=$pc_perso_fin, nb_mort=nb_mort+1 WHERE id_perso='$id_perso_cible'";
							$mysqli->query($sql);
							
							if ($perte_po > 0) {
								// On dépose la perte de thune par terre
								// Verification si l'objet existe deja sur cette case
								$sql = "SELECT nb_objet FROM objet_in_carte 
										WHERE objet_in_carte.x_carte = $x_cible 
										AND objet_in_carte.y_carte = $y_cible 
										AND type_objet = '1' AND id_objet = '0'";
								$res = $mysqli->query($sql);
								$to = $res->fetch_assoc();
								
								$nb_o = $to["nb_objet"];
								
								if($nb_o){
									// On met a jour le nombre
									$sql = "UPDATE objet_in_carte SET nb_objet = nb_objet + $perte_po 
											WHERE type_objet='1' AND id_objet='0'
											AND x_carte='$x_cible' AND y_carte='$y_cible'";
									$mysqli->query($sql);
								}
								else {
									// Insertion dans la table objet_in_carte : On cree le premier enregistrement
									$sql = "INSERT INTO objet_in_carte (type_objet, id_objet, nb_objet, x_carte, y_carte) VALUES ('1','0','$perte_po','$x_cible','$y_cible')";
									$mysqli->query($sql);
								}
							}
							
							// maj evenements
							$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_instance_bat,'<font color=$couleur_clan_canon><b>Canon</b></font>','a capturé','$id_perso_cible','<font color=$couleur_clan_cible><b>$nom_cible</b></font>','',NOW(),'0')";
							$mysqli->query($sql);
							
							// maj cv
							$sql = "INSERT INTO `cv` (IDActeur_cv, nomActeur_cv, IDCible_cv, nomCible_cv, date_cv) VALUES ($id_instance_bat,'<font color=$couleur_clan_canon>Canon</font>','$id_perso_cible','<font color=$couleur_clan_cible>$nom_cible</font>',NOW())";
							$mysqli->query($sql);
							
							// maj stats camp
							if($camp_cible != $camp_canon){
								$sql = "UPDATE stats_camp_kill SET nb_kill=nb_kill+1 WHERE id_camp=$camp_canon";
								$mysqli->query($sql);
							}
							
							// maj dernier tombé
							$sql = "INSERT INTO dernier_tombe (date_capture, id_perso_capture, camp_perso_capture, id_perso_captureur, camp_perso_captureur) VALUES (NOW(), $id_perso_cible, $camp_cible, $id_instance_bat, $camp_canon)";
							$mysqli->query($sql);
						}
						
						$degats_collat = floor($degats_final / 2);
										
						// Récupération des cibles potentielles autour de la cible principale
						$sql = "SELECT idPerso_carte FROM carte 
								WHERE x_carte >= $x_cible - 1 AND x_carte <= $x_cible + 1 AND y_carte >= $y_cible - 1 AND y_carte <= $y_cible + 1
								AND occupee_carte = '1'
								AND idPerso_carte != '$id_perso_cible'";
						$res_recherche_collat = $mysqli->query($sql);
						
						while ($t_recherche_collat = $res_recherche_collat->fetch_assoc()) {
											
							$id_cible_collat = $t_recherche_collat["idPerso_carte"];
							
							if ($id_cible_collat < 50000) {
								
								// Perso
								// Récupération des infos du perso
								$sql = "SELECT idJoueur_perso, nom_perso, image_perso, xp_perso, pi_perso, pc_perso, x_perso, y_perso, pv_perso, pvMax_perso, or_perso, type_perso, clan, perso_as_grade.id_grade
										FROM perso, perso_as_grade 
										WHERE perso_as_grade.id_perso = perso.id_perso
										AND perso.id_perso='$id_cible_collat'";
								$res = $mysqli->query($sql);
								$t_collat = $res->fetch_assoc();
								
								$id_joueur_collat 	= $t_collat["idJoueur_perso"];
								$nom_collat			= $t_collat["nom_perso"];
								$xp_collat 			= $t_collat["xp_perso"];
								$pi_collat			= $t_collat["pi_perso"];
								$pc_collat			= $t_collat["pc_perso"];
								$x_collat 			= $t_collat["x_perso"];
								$y_collat 			= $t_collat["y_perso"];
								$pv_collat 			= $t_collat["pv_perso"];
								$pvM_collat 		= $t_collat["pvMax_perso"];
								$or_collat 			= $t_collat["or_perso"];
								$image_perso_collat = $t_collat["image_perso"];
								$clan_collat 		= $t_collat["clan"];
								$grade_collat		= $t_collat['id_grade'];
								$tp_collat			= $t_collat['type_perso'];
								
								// Récupération de la couleur associée au clan de la cible
								$couleur_clan_collat = couleur_clan($clan_collat);
								
								// mise a jour des pv et des malus de la cible
								$sql = "UPDATE perso SET pv_perso=pv_perso-$degats_collat, bonus_perso=bonus_perso-2 WHERE id_perso='$id_cible_collat'";
								$mysqli->query($sql);
								
								// mise a jour de la table evenement
								$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_instance_bat,'<font color=$couleur_clan_canon><b>Canon</b></font>','a infligé des dégâts collatéraux ','$id_cible_collat','<font color=$couleur_clan_collat><b>$nom_collat</b></font>',' ( Dégâts : $degats_collat )',NOW(),'0')";
								$mysqli->query($sql);
								
								// il est mort
								if ($pv_collat - $degats_collat <= 0) {
									
									// on l'efface de la carte
									$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_collat' AND y_carte='$y_collat'";
									$mysqli->query($sql);
									
									// Calcul gains (po et xp)
									$perte_po = gain_po_mort($or_collat);
									
									// Chef
									if ($type_cible == 1) {
										perte_etendard($mysqli, $id_joueur_collat, $x_collat, $y_collat);
										// Quand un chef meurt, il perd 5% de ses XP,XPi et de ses PC
										// Calcul PI
										$pi_perdu 		= floor(($pi_collat * 5) / 100);
										
										// Calcul PC
										$pc_perdu		= floor(($pc_collat * 5) / 100);
										$pc_perso_fin	= $pc_collat - $pc_perdu;
									}
									else {
										$pi_perdu 		= floor(($pi_collat * 40) / 100);
										$xp_perso_fin = $xp_collat;
										$pc_perso_fin = $pc_collat;
									}
				
									// MAJ perte xp/po/stat cible
									$sql = "UPDATE perso SET or_perso=or_perso-$perte_po, xp_perso=xp_perso-$pi_perdu, pi_perso=pi_perso-$pi_perdu, pc_perso=$pc_perso_fin, nb_mort=nb_mort+1 WHERE id_perso='$id_cible_collat'";
									$mysqli->query($sql);
									
									if ($perte_po > 0) {
										// On dépose la perte de PO par terre
										// Verification si l'objet existe deja sur cette case
										$sql = "SELECT nb_objet FROM objet_in_carte 
												WHERE objet_in_carte.x_carte = $x_collat 
												AND objet_in_carte.y_carte = $y_collat 
												AND type_objet = '1' AND id_objet = '0'";
										$res = $mysqli->query($sql);
										$to = $res->fetch_assoc();
										
										$nb_o = $to["nb_objet"];
										
										if($nb_o){
											// On met a jour le nombre
											$sql = "UPDATE objet_in_carte SET nb_objet = nb_objet + $perte_po 
													WHERE type_objet='1' AND id_objet='0'
													AND x_carte='$x_collat_fin' AND y_carte='$y_collat'";
											$mysqli->query($sql);
										}
										else {
											// Insertion dans la table objet_in_carte : On cree le premier enregistrement
											$sql = "INSERT INTO objet_in_carte (type_objet, id_objet, nb_objet, x_carte, y_carte) VALUES ('1','0','$perte_po','$x_collat','$y_collat')";
											$mysqli->query($sql);
										}
									}
									
									// maj evenements
									$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_instance_bat,'<font color=$couleur_clan_canon><b>Canon</b></font>','a capturé','$id_cible_collat','<font color=$couleur_clan_collat><b>$nom_collat</b></font>','',NOW(),'0')";
									$mysqli->query($sql);
									
									// maj cv
									$sql = "INSERT INTO `cv` (IDActeur_cv, nomActeur_cv, IDCible_cv, nomCible_cv, date_cv) VALUES ($id_instance_bat,'<font color=$couleur_clan_canon>Canon</font>','$id_cible_collat','<font color=$couleur_clan_collat>$nom_collat</font>',NOW())";
									$mysqli->query($sql);
									
									// maj stats camp
									if($clan_collat != $camp_canon){
										$sql = "UPDATE stats_camp_kill SET nb_kill=nb_kill+1 WHERE id_camp=$camp_canon";
										$mysqli->query($sql);
									}

									// maj dernier tombé
									$sql = "INSERT INTO dernier_tombe (date_capture, id_perso_capture, camp_perso_capture, id_perso_captureur, camp_perso_captureur) VALUES (NOW(), '$id_cible_collat', $clan_collat, $id_instance_bat, $camp_canon)";
									$mysqli->query($sql);
								}
							}
						}
					}
					else {
					
						// gain xp esquive et ajout malus Cible
						$sql = "UPDATE perso SET xp_perso=xp_perso+2, pi_perso=pi_perso+2, bonus_perso=bonus_perso-2 WHERE id_perso='$id_perso_cible'";
						$mysqli->query($sql);
										
						// maj evenement
						$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso_cible,'<font color=$couleur_clan_cible><b>$nom_cible</b></font>','a esquivé l\'attaque de','$id_instance_bat','<font color=$couleur_clan_canon><b>Canon</b></font>','',NOW(),'0')";
						$mysqli->query($sql);
						
					}
				}
			}
		}
	}
}
?>
