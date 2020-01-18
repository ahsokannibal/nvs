<?php
session_start();
require_once("../fonctions.php");
require_once("f_carte.php");
require_once("f_combat.php");

$mysqli = db_connexion();

// Récupération de la liste des Forts et Fortins
$sql_b = "SELECT id_instanceBat, nom_instance FROM instance_batiment WHERE id_batiment='8' OR id_batiment='9' ORDER BY id_instanceBat";
$res_b = $mysqli->query($sql_b);

while ($t_b = $res_b->fetch_assoc()) {
	
	$id_instance_bat 	= $t_b['id_instanceBat'];
	$nom_instance		= $t_b['nom_instance'];
	
	// Récupération des canons du batiment
	$sql_c = "SELECT id_instance_canon, x_canon, y_canon, camp_canon, date_activation 
				FROM instance_batiment_canon
				WHERE id_instance_bat='$id_instance_bat'
				AND (date_activation is NULL OR date_activation < NOW())";
	$res_c = $mysqli->query($sql_c);
	
	while ($t_c = $res_c->fetch_assoc()) {
		
		$id_instance_canon 	= $t_c['id_instance_canon'];
		$x_canon 			= $t_c['x_canon'];
		$y_canon 			= $t_c['y_canon'];
		$camp_canon 		= $t_c['camp_canon'];
		$dla_canon			= $t_c['date_activation'];
		
		// Calcul nouvelle date activation canon
		$date = time();
		
		if ($dla_canon != NULL) {
			$new_dla = get_new_dla($date, $dla_canon);
			$new_dla = $new_dla + DUREE_TOUR;
		} else {
			$new_dla = $date + DUREE_TOUR;
		}
		
		// MAJ date_activation canon 
		$sql = "UPDATE instance_batiment_canon SET date_activation=FROM_UNIXTIME($new_dla) WHERE id_instance_canon='$id_instance_canon'";
		$mysqli->query($sql);
		
		$couleur_clan_canon = couleur_clan($camp_canon);
		
		echo "<br />* Canon ".$id_instance_canon." du Batiment ".$id_instance_bat." :<br />";
		
		$perception_canon	= 6;
		
		// y a t-il un ennemi en visu
		$sql_v = "SELECT idPerso_carte, idJoueur_perso, nom_perso, type_perso, x_perso, y_perso, pv_perso, protec_perso, bonus_perso, or_perso, clan FROM carte, perso  
					WHERE perso.id_perso = carte.idPerso_carte 
					AND occupee_carte='1' 
					AND x_carte >= $x_canon - $perception_canon
					AND x_carte <= $x_canon + $perception_canon
					AND y_carte >= $y_canon - $perception_canon
					AND y_carte <= $y_canon + $perception_canon
					AND idPerso_carte > 0 AND idPerso_carte < 50000
					AND clan != '$camp_canon' LIMIT 1";
		$res_v = $mysqli->query($sql_v);
		
		while ($t_v = $res_v->fetch_assoc()) {
			
			$id_cible 		= $t_v['idPerso_carte'];
			$pv_cible 		= $t_v['pv_perso'];
			$bonus_cible 	= $t_v['bonus_perso'];
			$camp_cible		= $t_v['clan'];
			$idJoueur_cible	= $t_v['idJoueur_perso'];
			$protec_cible	= $t_v['protec_perso'];
			$type_cible		= $t_v['type_perso'];
			$x_cible		= $t_v['x_perso'];
			$y_cible		= $t_v['y_perso'];
			$or_cible		= $t_v['or_perso'];
			$nom_cible		= $t_v['nom_perso'];
			
			$couleur_clan_cible = couleur_clan($camp_cible);
			
			$precision_canon 	= 65;
			$nb_des_canon 		= 75;
			$valeur_des_canon 	= 6;
			
			echo "-- Attaque sur ".$id_cible." : ";
			
			// Vérifie si le joueur attaqué a coché l'envoi de mail
			$mail_info_joueur = verif_coche_mail($mysqli, $idJoueur_cible);
						
			if($mail_info_joueur){
				// Envoi du mail
				mail_attaque($mysqli, 'Canon', $id_cible);
			}
			
			// -- Attaque cible
			// Calcul touche
			$touche = mt_rand(0,100);
			$precision_final = $precision_canon - $bonus_cible;
			
			echo "score de touche = ".$touche;
			
			if ($touche <= $precision_final) {
				
				// calcul degats arme
				$degats_final = mt_rand($nb_des_canon, $nb_des_canon * $valeur_des_canon) - $protec_cible;
				
				// Cible autre artillerie
				if ($type_cible == 5) {
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
				
				echo " - degats : ".$degats_final;
				
				// mise a jour des pv et des malus de la cible
				$sql = "UPDATE perso SET pv_perso=pv_perso-$degats_final, bonus_perso=bonus_perso-2 WHERE id_perso='$id_cible'";
				$mysqli->query($sql);
				
				// mise a jour de la table evenement
				$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_instance_bat,'<font color=$couleur_clan_canon><b>Canon</b></font>','a attaqué ','$id_cible','<font color=$couleur_clan_cible><b>$nom_cible</b></font>',': $degats_final degats',NOW(),'0')";
				$mysqli->query($sql);
				
				// il est mort
				if ($pv_cible - $degats_final <= 0) {
					
					echo " - Cible capturée<br />";
					
					// on l'efface de la carte
					$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_cible' AND y_carte='$y_cible'";
					$mysqli->query($sql);

					// Calcul gains (po et xp)
					$perte_po = gain_po_mort($or_cible);
					
					// TODO
					$perte_xp_cible = 0;

					// MAJ perte xp/po/stat cible
					$sql = "UPDATE perso SET or_perso=or_perso-$perte_po, xp_perso=xp_perso-$perte_xp_cible, pi_perso=0, nb_mort=nb_mort+1 WHERE id_perso='$id_cible'";
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
					$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_instance_bat,'<font color=$couleur_clan_canon><b>Canon</b></font>','a capturé','$id_cible','<font color=$couleur_clan_cible><b>$nom_cible</b></font>','',NOW(),'0')";
					$mysqli->query($sql);
					
					// maj cv
					$sql = "INSERT INTO `cv` (IDActeur_cv, nomActeur_cv, IDCible_cv, nomCible_cv, date_cv) VALUES ($id_instance_bat,'<font color=$couleur_clan_canon>Canon</font>','$id_cible','<font color=$couleur_clan_cible>$nom_cible</font>',NOW())";
					$mysqli->query($sql);
					
					// maj stats camp
					if($clan_cible != $camp_canon){
						$sql = "UPDATE stats_camp_kill SET nb_kill=nb_kill+1 WHERE id_camp=$camp_canon";
						$mysqli->query($sql);
					}
				}
				else {
					echo "<br />";
				}
				
				$degats_collat = floor($degats_final / 2);
								
				// Récupération des cibles potentielles autour de la cible principale
				$sql = "SELECT idPerso_carte FROM carte 
						WHERE x_carte >= $x_cible - 1 AND x_carte <= $x_cible + 1 AND y_carte >= $y_cible - 1 AND y_carte <= $y_cible + 1
						AND occupee_carte = '1'
						AND idPerso_carte != '$id_cible'";
				$res_recherche_collat = $mysqli->query($sql);
				
				while ($t_recherche_collat = $res_recherche_collat->fetch_assoc()) {
									
					$id_cible_collat = $t_recherche_collat["idPerso_carte"];
					
					if ($id_cible_collat < 50000) {
						
						// Perso
						// Récupération des infos du perso
						$sql = "SELECT idJoueur_perso, nom_perso, image_perso, xp_perso, x_perso, y_perso, pv_perso, pvMax_perso, or_perso, clan, id_grade
								FROM perso, perso_as_grade 
								WHERE perso_as_grade.id_perso = perso.id_perso
								AND perso.id_perso='$id_cible_collat'";
						$res = $mysqli->query($sql);
						$t_collat = $res->fetch_assoc();
						
						$id_joueur_collat 	= $t_collat["idJoueur_perso"];
						$nom_collat			= $t_collat["nom_perso"];
						$xp_collat 			= $t_collat["xp_perso"];
						$x_collat 			= $t_collat["x_perso"];
						$y_collat 			= $t_collat["y_perso"];
						$pv_collat 			= $t_collat["pv_perso"];
						$pvM_collat 		= $t_collat["pvMax_perso"];
						$or_collat 			= $t_collat["or_perso"];
						$image_perso_collat = $t_collat["image_perso"];
						$clan_collat 		= $t_collat["clan"];
						$grade_collat		= $t_collat['id_grade'];
						
						echo "---- Collat sur ".$id_cible_collat;
						
						// Récupération de la couleur associée au clan de la cible
						$couleur_clan_collat = couleur_clan($clan_collat);
						
						// mise a jour des pv et des malus de la cible
						$sql = "UPDATE perso SET pv_perso=pv_perso-$degats_collat, bonus_perso=bonus_perso-2 WHERE id_perso='$id_cible_collat'";
						$mysqli->query($sql);
						
						// mise a jour de la table evenement
						$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_instance_bat,'<font color=$couleur_clan_canon><b>Canon</b></font>','a infligé des dégâts collatéraux ','$id_cible_collat','<font color=$couleur_clan_collat><b>$nom_collat</b></font>',': $degats_collat degats',NOW(),'0')";
						$mysqli->query($sql);
						
						// il est mort
						if ($pv_collat - $degats_collat <= 0) {
							
							echo " - Cible collat capturée<br />";
							
							// on l'efface de la carte
							$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_collat' AND y_carte='$y_collat'";
							$mysqli->query($sql);
							
							// Calcul gains (po et xp)
							$perte_po = gain_po_mort($or_collat);
							
							// TODO
							$perte_xp_collat = 0;
		
							// MAJ perte xp/po/stat cible
							$sql = "UPDATE perso SET or_perso=or_perso-$perte_po, xp_perso=xp_perso-$perte_xp_collat, pi_perso=0, nb_mort=nb_mort+1 WHERE id_perso='$id_cible_collat'";
							$mysqli->query($sql);
							
							if ($perte_po > 0) {
								// On dépose la perte de PO par terre
								// Verification si l'objet existe deja sur cette case
								$sql = "SELECT nb_objet FROM objet_in_carte 
										WHERE objet_in_carte.x_carte = $x_collat_fin 
										AND objet_in_carte.y_carte = $y_collat_fin 
										AND type_objet = '1' AND id_objet = '0'";
								$res = $mysqli->query($sql);
								$to = $res->fetch_assoc();
								
								$nb_o = $to["nb_objet"];
								
								if($nb_o){
									// On met a jour le nombre
									$sql = "UPDATE objet_in_carte SET nb_objet = nb_objet + $perte_po 
											WHERE type_objet='1' AND id_objet='0'
											AND x_carte='$x_collat_fin' AND y_carte='$y_collat_fin'";
									$mysqli->query($sql);
								}
								else {
									// Insertion dans la table objet_in_carte : On cree le premier enregistrement
									$sql = "INSERT INTO objet_in_carte (type_objet, id_objet, nb_objet, x_carte, y_carte) VALUES ('1','0','$perte_po','$x_collat_fin','$y_collat_fin')";
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
						}
					}
				}
			}
			else {
				echo "<br />";
			
				// gain xp esquive et ajout malus Cible
				$sql = "UPDATE perso SET xp_perso=xp_perso+1, pi_perso=pi_perso+1, bonus_perso=bonus_perso-1 WHERE id_perso='$id_cible'";
				$mysqli->query($sql);
								
				// maj evenement
				$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_cible,'<font color=$couleur_clan_cible><b>$nom_cible</b></font>','a esquivé l\'attaque de','$id_instance_bat','<font color=$couleur_clan_canon><b>Canon</b></font>','',NOW(),'0')";
				$mysqli->query($sql);
				
			}
		}
	}
}
?>