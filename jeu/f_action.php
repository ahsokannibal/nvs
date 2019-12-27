<?php
require_once("../fonctions.php");
require_once("f_carte.php");
require_once("f_combat.php");

$mysqli = db_connexion();

/**
  * Fonction qui retourne la chaine de caractere correspondant a l'image associee a une action
  * @param $id_action	: L'identifiant de l'action
  * @return String		: L'image
  */
function image_action($id_action){
	
	if($id_action == '11' || $id_action == '12' || $id_action == '13' || $id_action == '14' || $id_action == '15' || 
	   $id_action == '16' || $id_action == '17' || $id_action == '18' || $id_action == '19' || $id_action == '20' ||
	   $id_action == '21' || $id_action == '22' || $id_action == '23' || $id_action == '24' || $id_action == '25' ||
	   $id_action == '26' || $id_action == '27'){
	   // soins pv
	   return 'soin.gif';
	}
	
	if($id_action == '140' || $id_action == '141' || $id_action == '142'){
	   // soins malus
	   return 'soin.gif';
	}
	
	if($id_action == '76'){
		// Reparer bat
		return 'reparer_bat.png';
	}
	
	if($id_action == '80' || $id_action == '81' || $id_action == '82' || $id_action == '83' || 
	   $id_action == '84' || $id_action == '85'){
		// Upgrade bat
		return 'reparer_bat.png';
	}
}

function construire_rail($mysqli, $t_bat, $id_perso, $carte){
	
	if(isset($_POST['pose_rail'])){
		$t_rail = $_POST['pose_rail'];
	}
	else {
		$t_rail = $_POST['hid_pose_rail'];
	}

	$t_rail2 = explode(',',$t_rail);
	$x_rail = $t_rail2[0];
	$y_rail = $t_rail2[1];
	
	// mise a jour de la carte
	$sql = "UPDATE $carte SET fond_carte='rail.gif' WHERE x_carte='$x_rail' AND y_carte='$y_rail'";
	$mysqli->query($sql);
	
	return 1;
}

/**
  * Fonction qui permet de construire un batiment sur une case
  * @param $t_bat	: Un tableau contenant les coordonnees ou le batiement doit etre construit ainsi que l'identifiant du batiment
  * @param $id_perso	: L'identifiant du perso qui construit le batiment
  * @param $carte 	: La carte sur laquelle le batiment doit etre construit
  * @return Bool		: Si oui ou non le batiment est constructible
  */
function construire_bat($mysqli, $t_bat, $id_perso,$carte){
	
	if(isset($_POST['image_bat'])){
		$t_bat = $_POST['image_bat'];
	}
	else {
		$t_bat = $_POST['hid_image_bat'];
	}

	$t_bat2 = explode(',',$t_bat);
	$x_bat = $t_bat2[0];
	$y_bat = $t_bat2[1];
	$id_bat = $t_bat2[2];
	
	if(isset($id_bat) && $id_bat != ''){
	
		// recuperation du nom du batiment
		$sql = "SELECT nom_batiment, taille_batiment FROM batiment WHERE id_batiment='$id_bat'";
		$res = $mysqli->query($sql);
		$tb = $res->fetch_assoc();
		
		$nom_bat 	= $tb["nom_batiment"];
		$taille_bat = $tb["taille_batiment"];
		
		// recuperation des donnees necessaires pour la construction du batiment
		$sql = "SELECT clan, or_perso, pa_perso, pvMin_action, pvMax_action, coutPa_action, coutOr_action, coutBois_action, coutfer_action, contenance, action.nb_points as niveau_bat
				FROM action, action_as_batiment, perso_as_competence, competence_as_action, perso
				WHERE action.id_action = action_as_batiment.id_action
				AND perso_as_competence.nb_points = action.nb_points
				AND competence_as_action.id_action = action.id_action
				AND perso_as_competence.id_competence = competence_as_action.id_competence
				AND perso.id_perso = perso_as_competence.id_perso
				AND id_batiment = '$id_bat'
				AND perso_as_competence.id_perso = '$id_perso'";
		$res = $mysqli->query($sql);
		$t_b = $res->fetch_assoc();
		
		$pvMin 			= $t_b['pvMin_action'];
		$pvMax 			= $t_b['pvMax_action'];
		$coutPa 		= $t_b['coutPa_action'];
		$coutOr 		= $t_b['coutOr_action'];
		$coutBois 		= $t_b['coutBois_action'];
		$coutFer 		= $t_b['coutfer_action'];
		$or_perso 		= $t_b["or_perso"];
		$pa_perso 		= $t_b["pa_perso"];
		$camp_perso 	= $t_b['clan'];
		$niveau_bat 	= $t_b['niveau_bat'];
		$contenance_bat = $t_b['contenance'];
		
		if($camp_perso == '1'){
			
			$bat_camp = "b";
		}
		
		if($camp_perso == '2'){
			
			$bat_camp = "r";
		}
		
		// test pa
		if($pa_perso >= $coutPa){
		
			// recuperation nombre bois du perso
			$nb_bois = nb_bois_perso($mysqli, $id_perso);
			
			if($or_perso >= $coutOr && $nb_bois >= $coutBois){
				// -- TODO --
				// verif occupee carte ?
				
				$gain_xp = 1;
				
				// TODO - verification distance entre nouveau bat et batiments existant
				$autorisation_construction = true;
				
				$autorisation_construction_taille = true;
				
				$taille_search = floor($taille_bat / 2);
				
				if ($taille_bat > 1) {
					
					// verification carte pour construction 
					$sql = "SELECT occupee_carte, fond_carte FROM carte 
							WHERE x_carte <= $x_bat + $taille_search AND x_carte >= $x_bat - $taille_search AND y_carte <= $y_bat + $taille_search AND y_carte >= $y_bat - $taille_search";
					$res = $mysqli->query($sql);
					
					while ($t = $res->fetch_assoc()) {
						
						$occupee_carte 	= $t["occupee_carte"];
						$fond_carte 	= $t["fond_carte"];
						
						if ($occupee_carte || $fond_carte != '1.gif') {
							$autorisation_construction_taille = false;
						}
					}
				}
				
				if($autorisation_construction){
					
					if ($autorisation_construction_taille) {
					
						if($coutPa == -1){
							// mise a jour des pa, or et charge du perso + xp/pi
							$sql = "UPDATE perso SET pa_perso='0' , or_perso=or_perso-$coutOr, charge_perso=charge_perso-$coutBois, xp_perso=xp_perso+$gain_xp, pi_perso=pi_perso+$gain_xp WHERE id_perso='$id_perso'";
							$mysqli->query($sql);
						}
						else {
							// mise a jour des pa, or et charge du perso + xp/pi
							$sql = "UPDATE perso SET pa_perso=pa_perso-$coutPa , or_perso=or_perso-$coutOr, charge_perso=charge_perso-$coutBois, xp_perso=xp_perso+$gain_xp, pi_perso=pi_perso+$gain_xp WHERE id_perso='$id_perso'";
							$mysqli->query($sql);
						}
						
						// MAJ bois
						for($i=1; $i <= $coutBois; $i++){
							$sql = "DELETE FROM perso_as_objet WHERE id_perso='$id_perso' AND id_objet='7' LIMIT 1";
							$mysqli->query($sql);
						}
						
						$pv_bat = rand($pvMin, $pvMax);
						$img_bat = "b".$id_bat."".$bat_camp.".png";
						
						if ($id_bat == 4){
							// route et pont
							// mise a jour de la carte
							$sql = "UPDATE $carte SET occupee_carte='0', fond_carte='$img_bat' WHERE x_carte=$x_bat AND y_carte=$y_bat";
							$mysqli->query($sql);							
						}
						else {
							// mise a jour de la table instance_bat
							$sql = "INSERT INTO instance_batiment (niveau_instance, id_batiment, nom_instance, pv_instance, pvMax_instance, x_instance, y_instance, camp_instance, contenance_instance) 
									VALUES ('$niveau_bat', '$id_bat', '', '$pv_bat', '$pvMax', '$x_bat', '$y_bat', '$camp_perso', '$contenance_bat')";
							$mysqli->query($sql);
							$id_i_bat = $mysqli->insert_id;
							
							if ($id_bat == 5) {
								
								// mise a jour de la carte
								$sql = "UPDATE $carte SET occupee_carte='0', idPerso_carte='$id_i_bat', fond_carte='$img_bat' WHERE x_carte='$x_bat' AND y_carte='$y_bat'";
								$mysqli->query($sql);
								
							} else {
							
								$img_bat_sup = $bat_camp.".png";
								
								for ($x = $x_bat - $taille_search; $x <= $x_bat + $taille_search; $x++) {
									for ($y = $y_bat - $taille_search; $y <= $y_bat + $taille_search; $y++) {
										
										// mise a jour de la carte
										$sql = "UPDATE $carte SET occupee_carte='1', idPerso_carte='$id_i_bat', image_carte='$img_bat_sup' WHERE x_carte='$x' AND y_carte='$y'";
										$mysqli->query($sql);
										
									}
								}
							
								// mise a jour de la carte image centrale
								$sql = "UPDATE $carte SET occupee_carte='1', idPerso_carte='$id_i_bat', image_carte='$img_bat' WHERE x_carte='$x_bat' AND y_carte='$y_bat'";
								$mysqli->query($sql);
							}
						}
						
						// recuperation des infos du perso
						$sql = "SELECT nom_perso, clan FROM perso WHERE id_perso='$id_perso'";
						$res = $mysqli->query($sql);
						$t_p = $res->fetch_assoc();
						$nom_perso = $t_p["nom_perso"];
						$camp = $t_p["clan"];
						
						// recuperation de la couleur du camp du perso
						$couleur_clan_perso = couleur_clan($camp);
						
						// route et pont
						if($id_bat == 4 || $id_bat == 5){
							//mise a jour de la table evenement
							$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>','a construit $nom_bat',NULL,'','',NOW(),'0')";
							$mysqli->query($sql);
						}
						else {
							//mise a jour de la table evenement
							$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>','a construit ','$id_i_bat','<font color=$couleur_clan_perso>$nom_bat</font>','',NOW(),'0')";
							$mysqli->query($sql);
						}
						
						return 1;
					}
					else {
						echo "<center>Vous ne pouvez pas construire ce bâtiment car la carte est occupée ou le terrain n'est pas que de la plaine</center><br />";
						echo "<a href='jouer.php'>[ retour ]</a>";
						
						return 0;
					}
				}
				else {
					echo "<center>Vous ne pouvez pas construire ce bâtiment aussi loin du fort : distance = $distance ; distance max = $distance_max</center><br />";
					echo "<a href='jouer.php'>[ retour ]</a>";
					
					return 0;
				}
			}
			else {
				echo "<center>Vous n'avez pas assez d'or ou assez de bois pour construire ce batiment</center><br />";
				echo "<a href='jouer.php'>[ retour ]</a>";
				return 0;
			}
		}
		else {
			echo "<center>Vous n'avez pas assez de PA</center><br />";
			echo "<a href='jouer.php'>[ retour ]</a>";
		}
	}
	else {
		echo "<center>Vous n'avez pas choisi de batiment</center><br />";
		echo "<a href='jouer.php'>[ retour ]</a>";
	}
}

/**
  * Fonction qui permet de reparer un batiment
  * @param $id_perso	: L'identifiant du perso qui repare le batiment
  * @param $id_cible	: L'identifiant de l'instance du batiment cible de la reparation
  * @param $id_action	: L'identifiant de l'action pour recuperer le niveau de reparation
  * @return Void
  */
function action_reparer_bat($mysqli, $id_perso, $id_cible, $id_action){
	
	// recuperation des donnees correspondant a l'action
	$sql = "SELECT coutPa_action FROM action WHERE id_action=$id_action";
	$res = $mysqli->query($sql);
	$t_reparer = $res->fetch_assoc();
	$coutPa = $t_reparer["coutPa_action"];
	
	// recuperation des infos du perso
	$sql = "SELECT nom_perso, pa_perso, clan FROM perso WHERE id_perso='$id_perso'";
	$res = $mysqli->query($sql);
	$t_i_perso = $res->fetch_assoc();
	
	$nom_perso = $t_i_perso['nom_perso'];
	$pa_perso = $t_i_perso['pa_perso'];
	$camp_perso = $t_i_perso['clan'];
	
	// recuperation de la couleur du camp du perso
	$couleur_clan_perso = couleur_clan($camp_perso);
	
	// test pa
	if($pa_perso >= $coutPa){
		
		// recuperation des infos de la cible
		$sql = "SELECT nom_batiment, pv_instance, pvMax_instance, camp_instance FROM instance_batiment, batiment WHERE id_instanceBat='$id_cible' AND batiment.id_batiment = instance_batiment.id_batiment";
		$res = $mysqli->query($sql);
		$t_i_cible = $res->fetch_assoc();
		$nom_cible = $t_i_cible['nom_batiment'];
		$pv_instance_bat = $t_i_cible['pv_instance'];
		$pv_max_bat = $t_i_cible['pvMax_instance'];
		$camp_bat = $t_i_cible['camp_instance'];
		
		// recuperation de la couleur du camp du batiment
		$couleur_clan_cible = couleur_clan($camp_bat);
		
		//calcul des reparations
		$pv_reparation = calcul_pv_reparation($id_action);
		
		// traitement reparation
		if($pv_instance_bat < $pv_max_bat){
		
			// calcul gain xp
			$gain_xp = rand(2,5);
			
			if($camp_bat != $camp_perso){
				$gain_xp = floor($gain_xp / 2);
			}
		
			if($pv_instance_bat + $pv_reparation < $pv_max_bat){
				//MAJ pv cible
				$sql = "UPDATE instance_batiment SET pv_instance=pv_instance+$pv_reparation WHERE id_instanceBat='$id_cible'";
				$mysqli->query($sql);
					
				//MAJ xp/pi perso et pa
				$sql = "UPDATE perso SET xp_perso=xp_perso+$gain_xp, pi_perso=pi_perso+$gain_xp, pa_perso=pa_perso-$coutPa WHERE id_perso='$id_perso'";
				$mysqli->query($sql);
					
				//MAJ evenments perso
				$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>',' a reparé le batiment ','$id_cible','<font color=$couleur_clan_cible>$nom_cible</font>',' : reparation de $pv_reparation PV',NOW(),'0')";
				$mysqli->query($sql);
					
				echo "<center>Vous avez réparé <font color=$couleur_clan_cible>$nom_cible</font> de $pv_reparation PV</center><br />";
				echo "<center>Vous avez gagné $gain_xp XP</center>";
			}
			else {
				// on met aux pvMax de la cible
				$sql = "UPDATE instance_batiment SET pv_instance=pvMax_instance WHERE id_instanceBat='$id_cible'";
				$mysqli->query($sql);
					
				//MAJ xp/pi/pa perso
				$sql = "UPDATE perso SET xp_perso=xp_perso+$gain_xp, pi_perso=pi_perso+$gain_xp, pa_perso=pa_perso-$coutPa WHERE id_perso='$id_perso'";
				$mysqli->query($sql);
					
				//MAJ evenments perso
				$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>',' a reparé le batiment ','$id_cible','<font color=$couleur_clan_cible>$nom_cible</font>',' : reparation de $pv_reparation PV',NOW(),'0')";
				$mysqli->query($sql);
					
				echo "<center>Vous avez réparé <font color=$couleur_clan_cible>$nom_cible</font> de $pv_reparation PV</center><br />";
				echo "<center>La cible est revenu à son max de vie</center><br />";
				echo "<center>Vous avez gagné $gain_xp XP</center>";
			}
		}
		else {
			// cible deja au max
			$gain_xp = '1';
				
				//MAJ xp/pi/pa perso
			$sql = "UPDATE perso SET xp_perso=xp_perso+$gain_xp, pi_perso=pi_perso+$gain_xp, pa_perso=pa_perso-$coutPa WHERE id_perso='$id_perso'";
			$mysqli->query($sql);
				
			//MAJ evenments perso
			$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>',' a fait une révision sur le batiment ','$id_cible','<font color=$couleur_clan_cible>$nom_cible</font>',' : pv déjà au max...',NOW(),'0')";
			$mysqli->query($sql);
				
			echo "<center>La cible est était déjà à son max de vie</center><br />";
			echo "<center>Vous avez gagné $gain_xp XP</center>";
		}
	}
	else {
		echo "<center>Vous n'avez pas assez de PA</center><br />";
	}
	echo "<br /><br /><a href='jouer.php'>[ retour ]</a>";
}

/**
  * Fonction d'upgrade de batiment (passage d'une batiment vers son niveau directement superieur)
  * @param $id_perso	: L'identifiant du personnage qui va faire l'upgrade
  * @param $id_cible	: L'identifiant de l'instance du batiment cible de l'upgrade
  * @param $id_action	: L'identifiant de l'action afin de recuperer le niveau d'upgrade et donc le pourcentage de reussite
  * @return Void
  */
function action_upgrade_bat($mysqli, $id_perso, $id_cible, $id_action){

	// recuperation des donnees correspondant a l'action
	$sql = "SELECT coutPa_action FROM action WHERE id_action=$id_action";
	$res = $mysqli->query($sql);
	$t_reparer = $res->fetch_assoc();
	$coutPa = $t_reparer["coutPa_action"];
	
	// recuperation des infos du perso
	$sql = "SELECT nom_perso, pa_perso, or_perso, clan FROM perso WHERE id_perso='$id_perso'";
	$res = $mysqli->query($sql);
	$t_i_perso = $res->fetch_assoc();
	$nom_perso = $t_i_perso['nom_perso'];
	$pa_perso = $t_i_perso['pa_perso'];
	$or_perso = $t_i_perso['or_perso'];
	$camp_perso = $t_i_perso['clan'];
	
	// recuperation de la couleur du camp du perso
	$couleur_clan_perso = couleur_clan($camp_perso);
	
	// test pa
	if($pa_perso >= $coutPa){
	
		// recuperation des infos de la cible
		$sql = "SELECT nom_batiment, batiment.id_batiment, niveau_instance, camp_instance FROM instance_batiment, batiment WHERE id_instanceBat='$id_cible' AND batiment.id_batiment = instance_batiment.id_batiment";
		$res = $mysqli->query($sql);
		$t_i_cible = $res->fetch_assoc();
		$nom_cible = $t_i_cible['nom_batiment'];
		$id_batiment = $t_i_cible['id_batiment'];
		$niveau_instance_bat = $t_i_cible['niveau_instance'];
		$camp_bat = $t_i_cible['camp_instance'];
		
		// recuperation de la couleur du camp du batiment
		$couleur_clan_cible = couleur_clan($camp_bat);
		
		// recuperation de l'id de la competence de construction du batiment cible
		$id_competence = recup_id_competence_bat($id_batiment);
		
		// verification si le perso possede la competence et recuperation du nombre de points
		$sql = "SELECT nb_points FROM perso_as_competence WHERE id_perso='$id_perso' AND id_competence='$id_competence'";
		$res = $mysqli->query($sql);
		$verif_comp = $res->num_rows;
		
		if($verif_comp){
			
			$t_comp = $res->fetch_assoc();
			$nb_points_comp_perso = $t_comp['nb_points'];
			
			// recuperation de l'action necessaire a la construction du niveau superieur du batiment
			$sql = "SELECT action.id_action, action.nb_points
				FROM perso_as_competence, competence, competence_as_action, action
				WHERE perso_as_competence.id_perso='$id_perso' 
				AND perso_as_competence.id_competence = competence.id_competence
				AND competence.id_competence = competence_as_action.id_competence
				AND competence_as_action.id_action = action.id_action
				AND competence.id_competence = '$id_competence'
				AND action.nb_points = $niveau_instance_bat + 1";
			$res = $mysqli->query($sql);
			$t = $res->fetch_assoc();
			
			$id_action_cible = $t["id_action"];
			$nb_points_action_cible = $t['nb_points'];
			
			// verification si le perso possede la possibilite de construire le niveau de batiment superieur
			if($nb_points_comp_perso >= $nb_points_action_cible){
				
				// calcul du cout en or
				$cout_or = 10 * $nb_points_action_cible;
				
				// verification que le perso possede l'or necessaire a l'upgrade
				if($or_perso >= $cout_or){
				
					// calcul du pourcentage de reussite de l'action
					$pourcentage_reussite = calcul_pourcentage_action($id_action);
					
					$reussite = rand(0,100);
					
					// verif chanceux et recup nb_points de chance
					if(est_chanceux($id_perso)){
						$bonus_chance = 2 * est_chanceux($id_perso);
					}
					else {
						$bonus_chance = 0;
					}
					
					if($reussite <= $pourcentage_reussite + $bonus_chance){
						// recuperation des infos du nouveau niveau de batiment
						$sql = "SELECT pvMin_action, pvMax_action, contenance FROM action, action_as_batiment WHERE action.id_action = '$id_action_cible' AND action.id_action = action_as_batiment.id_action";
						$res = $mysqli->query($sql);
						$t_action_cible = $res->fetch_assoc();
						
						$pv_min = $t_action_cible['pvMin_action'];
						$pv_max = $t_action_cible['pvMax_action'];
						$new_contenance = $t_action_cible['contenance'];
						
						$new_pv_bat = rand($pv_min, $pv_max);
					
						// mise a jour du batiment
						$sql = "UPDATE instance_batiment SET niveau_instance = niveau_instance+1, pvMax_instance=$new_pv_bat, contenance_instance=$new_contenance 
								WHERE id_instanceBat = '$id_cible'";
						$mysqli->query($sql);
						
						// gains xp
						$gain_xp = rand(2,4);
							
						//MAJ evenments perso
						$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>',' a upgradé le batiment ','$id_cible','<font color=$couleur_clan_cible>$nom_cible</font>',' : le batiment est passé niveau $nb_points_action_cible',NOW(),'0')";
						$mysqli->query($sql);
							
						echo "<center>Vous avez Upgradé le batiment $id_cible <font color=$couleur_clan_cible>$nom_cible</font> au niveau suivant : le niveau $nb_points_action_cible</center><br />";
						echo "<center>Le bâtiment posséde maintenant $new_pv_bat Points de Vie et une contenance de $new_contenance</center>";
						echo "<center>Vous avez gagné $gain_xp XP</center>";
					}
					else {
						$gain_xp = 1;
						echo "<center>Vous n'avez pas réussi à upgrader ce bâtiment ($reussite / $pourcentage_reussite)</center><br/>";
						echo "<center>Vous avez gagné $gain_xp XP</center>";
					}
					//MAJ xp perso
					$sql = "UPDATE perso SET xp_perso=xp_perso+$gain_xp, pi_perso=pi_perso+$gain_xp, pa_perso=pa_perso-$coutPa, or_perso=or_perso-$cout_or WHERE id_perso='$id_perso'";
					$mysqli->query($sql);
				}
				else {
					echo "<center>Vous ne possedez pas assez d'or pour upgrader le bâtiment (Votre or : $or_perso / cout en or : $cout_or)</center>";
				}
			}
			else {
				echo "<center>Vous ne possédez pas les connaissances necessaires pour upgrader ce batiment</center><br />";
				echo "<center>Vous devez être capable de construire le niveau supérieur de ce bâtiment afin de pouvoir l'upgrader</center><br />";
			}
		}
		else {
			echo "<center>Vous ne possédez pas les connaissances necessaires pour upgrader ce batiment</center><br />";
			echo "<center>Vous devez avoir la compétence de construction de ce bâtiment</center><br />";
		}
	}
	else {
		echo "<center>Vous n'avez pas assez de PA</center><br />";
	}
	echo "<br /><br /><a href='jouer.php'>[ retour ]</a>";
}

/**
  * Fonction d'upgrade de batiment de niveau expert (Passage du niveau d'un batiment directement vers son niveau maximum)
  * @param $id_perso	: L'identifiant du personnage qui va effetuer l'upgrade
  * @param $id_cible	: L'identifiant de l'instance du batiment cible de l'upgrade
  * @param $id_action	: L'identifiant de l'action afin de recuperer le pourcentage de reussite de l'upgrade
  * @return Void
  */
function action_upgrade_expert_bat($mysqli, $id_perso, $id_cible, $id_action){

	// recuperation des donnees correspondant a l'action
	$sql = "SELECT coutPa_action FROM action WHERE id_action=$id_action";
	$res = $mysqli->query($sql);
	$t_reparer = $res->fetch_assoc();
	$coutPa = $t_reparer["coutPa_action"];
	
	// recuperation des infos du perso
	$sql = "SELECT nom_perso, pa_perso, or_perso, clan FROM perso WHERE id_perso='$id_perso'";
	$res = $mysqli->query($sql);
	$t_i_perso = $res->fetch_assoc();
	$nom_perso = $t_i_perso['nom_perso'];
	$pa_perso = $t_i_perso['pa_perso'];
	$or_perso = $t_i_perso['or_perso'];
	$camp_perso = $t_i_perso['clan'];
	
	// recuperation de la couleur du camp du perso
	$couleur_clan_perso = couleur_clan($camp_perso);
	
	// test pa
	if($pa_perso >= $coutPa){
	
		// recuperation des infos de la cible
		$sql = "SELECT nom_batiment, batiment.id_batiment, niveau_instance, camp_instance FROM instance_batiment, batiment WHERE id_instanceBat='$id_cible' AND batiment.id_batiment = instance_batiment.id_batiment";
		$res = $mysqli->query($sql);
		$t_i_cible = $res->fetch_assoc();
		
		$nom_cible = $t_i_cible['nom_batiment'];
		$id_batiment = $t_i_cible['id_batiment'];
		$niveau_instance_bat = $t_i_cible['niveau_instance'];
		$camp_bat = $t_i_cible['camp_instance'];
		
		// recuperation de la couleur du camp du perso
		$couleur_clan_cible = couleur_clan($camp_bat);
		
		// recuperation de l'id de la competence de construction du batiment cible
		$id_competence = recup_id_competence_bat($id_batiment);
		
		// verification si le perso possede la competence et recuperation du nombre de points
		$sql = "SELECT nb_points FROM perso_as_competence WHERE id_perso='$id_perso' AND id_competence='$id_competence'";
		$res = $mysqli->query($sql);
		$verif_comp = $res->num_rows;
		
		if($verif_comp){
			$t_comp = $res->fetch_assoc();
			$nb_points_comp_perso = $t_comp['nb_points'];
			
			// recuperation du niveau max de la competence
			$sql = "SELECT nbPoints_competence FROM competence WHERE id_competence='$id_competence'";
			$res = $mysqli->query($sql);
			$t_p = $res->fetch_assoc();
			$nbPoints_max_comp = $t_p['nbPoints_competence'];
			
			// recuperation de l'action necessaire a la construction du niveau superieur du batiment
			$sql = "SELECT action.id_action, action.nb_points
				FROM perso_as_competence, competence, competence_as_action, action
				WHERE perso_as_competence.id_perso='$id_perso' 
				AND perso_as_competence.id_competence = competence.id_competence
				AND competence.id_competence = competence_as_action.id_competence
				AND competence_as_action.id_action = action.id_action
				AND competence.id_competence = '$id_competence'
				AND action.nb_points = $nbPoints_max_comp";
			$res = $mysqli->query($sql);
			$t = $res->fetch_assoc();
			
			$id_action_cible = $t["id_action"];
			$nb_points_action_cible = $t['nb_points'];
			
			// verification si le perso possede la possibilite de construire le niveau de batiment superieur
			if($nb_points_comp_perso >= $nb_points_action_cible){
				
				// calcul du cout en or
				$cout_or = 10 * $nb_points_action_cible;
				
				// verification que le perso possede l'or necessaire a l'upgrade
				if($or_perso >= $cout_or){
				
					if($niveau_instance_bat == $nb_points_action_cible){
						echo "<center>bâtiment déjà au niveau maximum</center>";
					}
					else {
				
						//recuperation du pourcentage de reussite
						$pourcentage_reussite = calcul_pourcentage_action($id_action);
						
						$reussite = rand(0,100);
						
						// verif chanceux et recup nb_points de chance
						if(est_chanceux($id_perso)){
							$bonus_chance = 2 * est_chanceux($id_perso);
						}
						else {
							$bonus_chance = 0;
						}
						
						if($reussite <= $pourcentage_reussite + $bonus_chance){
							// recuperation des infos du nouveau niveau de batiment
							$sql = "SELECT pvMin_action, pvMax_action, contenance FROM action, action_as_batiment WHERE action.id_action = '$id_action_cible' AND action.id_action = action_as_batiment.id_action";
							$res = $mysqli->query($sql);
							$t_action_cible = $res->fetch_assoc();
							
							$pv_min = $t_action_cible['pvMin_action'];
							$pv_max = $t_action_cible['pvMax_action'];
							$new_contenance = $t_action_cible['contenance'];
							
							$new_pv_bat = rand($pv_min, $pv_max);
						
							// mise a jour du batiment
							$sql = "UPDATE instance_batiment SET niveau_instance = niveau_instance+1, pvMax_instance=$new_pv_bat, contenance_instance=$new_contenance 
									WHERE id_instanceBat = '$id_cible'";
							$mysqli->query($sql);
							
							// gains xp
							$gain_xp = rand(2,4);
								
							//MAJ evenments perso
							$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>',' a upgradé le bâtiment ','$id_cible','<font color=$couleur_clan_cible>$nom_cible</font>',' : le batiment est passé niveau $nb_points_action_cible (niveau max)',NOW(),'0')";
							$mysqli->query($sql);
								
							echo "<center>Vous avez Upgradé le bâtiment $id_cible <font color=$couleur_clan_cible>$nom_cible</font> au niveau maximum : le niveau $nb_points_action_cible</center><br />";
							echo "<center>Le bâtiment posséde maintenant $new_pv_bat Points de Vie et une contenance de $new_contenance</center>";
							echo "<center>Vous avez gagné $gain_xp XP</center>";
						}
						else {
							$gain_xp = 1;
							echo "<center>Vous n'avez pas réussi à upgrader ce bâtiment ($reussite / $pourcentage_reussite)</center><br/>";
							echo "<center>Vous avez gagné $gain_xp XP</center>";
						}
						//MAJ xp perso
						$sql = "UPDATE perso SET xp_perso=xp_perso+$gain_xp, pi_perso=pi_perso+$gain_xp, pa_perso=pa_perso-$coutPa, or_perso=or_perso-$cout_or WHERE id_perso='$id_perso'";
						$mysqli->query($sql);
					}
				}
				else {
					echo "<center>Vous ne possedez pas assez d'or pour upgrader le bâtiment (Votre or : $or_perso / cout en or : $cout_or)</center>";
				}
			}
			else {
				echo "<center>Vous ne possédez pas les connaissances necessaires pour upgrader ce batiment</center><br />";
				echo "<center>Vous devez être capable de construire le niveau supérieur de ce bâtiment afin de pouvoir l'upgrader</center><br />";
			}
		}
		else {
			echo "<center>Vous ne possédez pas les connaissances necessaires pour upgrader ce batiment</center><br />";
			echo "<center>Vous devez avoir la compétence de construction de ce bâtiment</center><br />";
		}
	}
	else {
		echo "<center>Vous n'avez pas assez de PA</center><br />";
	}
	echo "<br /><br /><a href='jouer.php'>[ retour ]</a>";
}

/**
  * Fonction qui permet d'effectuer des soin de malus sur une cible
  * @param $id_perso		: l'identifiant du personnage effectuant les soins
  * @param $id_ciblea		: L'identiiant du pnj ou pj (cible) recevant les soins
  * @param $id_action		: L'identifiant de l'action de soin afin de recuperer le niveau de competence
  * @param $id_objet_soin	: L'identifiant de l'objet qu'on va utiliser pour ameliorer les soins
  * @return Void
  */
function action_soin_malus($mysqli, $id_perso, $id_cible, $id_action, $id_objet_soin){
	
	// reuperation des donnees correspondant a l'action
	$sql = "SELECT coutPa_action FROM action WHERE id_action=$id_action";
	$res = $mysqli->query($sql);
	$t_soin = $res->fetch_assoc();
	$coutPa = $t_soin["coutPa_action"];
	
	// calcul recup malus
	$recup_malus = calcul_recup_malus($id_action);
	
	//recuperation des infos sur le perso
	$sql = "SELECT nom_perso, clan, pa_perso FROM perso WHERE id_perso='$id_perso'";
	$res = $mysqli->query($sql);
	$t_p = $res->fetch_assoc();
	$nom_perso = $t_p["nom_perso"];
	$pa_perso = $t_p["pa_perso"];
	$camp = $t_p["clan"];
	
	// recupetion de la couleur du camp du perso
	$couleur_clan_perso = couleur_clan($camp);
	
	// test pa
	if($pa_perso >= $coutPa){
		// recuperation des malus du perso cible
		$sql = "SELECT nom_perso, bonus_perso, pv_perso, pvMax_perso, clan FROM perso WHERE id_perso='$id_cible'";
		$res = $mysqli->query($sql);
		$t_pv = $res->fetch_assoc();
		
		$nom_cible = $t_pv['nom_perso'];
		$pv_cible = $t_pv['pv_perso'];
		$pvMax_cible = $t_pv['pvMax_perso'];
		$bonus_cible = $t_pv['bonus_perso'];
		$camp_cible = $t_pv['clan'];
		
		// recuperation de la couleur du camp du perso
		$couleur_clan_cible = couleur_clan($camp_cible);
		
		// calcul gain xp
		$gain_xp = gain_xp($camp, $camp_cible);
		$gain_xp = min($gain_xp, 5);
		
		// Si on soigne les malus d'un perso autre que soi meme
		if($id_perso != $id_cible){
			$gain_xp = $gain_xp * 2;
		}
		
		$bonus_recup_s = 0;
		
		if($id_objet_soin > 0){
			// recuperation des avantages de l'objet de soin
			$sql_s = "SELECT nom_objet, bonusPv_objet, bonusRecup_objet FROM objet WHERE id_objet='$id_objet_soin'";
			$res_s = $mysqli->query($sql_s);
			$t_s = $res_s->fetch_assoc();
			$nom_objet = $t_s['nom_objet'];
			$bonus_recup_s = $t_s['bonusRecup_objet'];
		}
			
		// traitement soin malus
		if($id_objet_soin == 12){
			$bonus_recup_final = $bonus_recup_s + $recup_malus;		
		}
		else {
			$bonus_recup_final = $recup_malus;
		}
		
		if($bonus_cible < 0){
			if($bonus_cible + $recup_malus <= 0){
				//MAJ pv et recup cible
				$sql = "UPDATE perso SET bonus_perso=bonus_perso+$bonus_recup_final, bonusRecup_perso=bonusRecup_perso+$bonus_recup_s WHERE id_perso='$id_cible'";
				$mysqli->query($sql);
			}
			else {
				//MAJ pv et recup cible
				$sql = "UPDATE perso SET bonus_perso=0, bonusRecup_perso=bonusRecup_perso+$bonus_recup_s WHERE id_perso='$id_cible'";
				$mysqli->query($sql);
				
				echo "<center>La cible a complétement récupérée de ses malus</center><br />";
			}
			
			echo "<center>Vous avez apaisé <font color=$couleur_clan_cible>$nom_cible</font> de $bonus_recup_final</center><br />";
			
			if($bonus_recup_s){
				if($id_objet_soin != 12){
					echo "<center>Vous avez augmenté la récupération de <font color=$couleur_clan_cible>$nom_cible</font> de $bonus_recup_s</center><br />";
				}
			}
			
			//MAJ evenements perso
			$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>',' a apaisé ','$id_cible','<font color=$couleur_clan_cible>$nom_cible</font>',' : apaisement de $bonus_recup_final',NOW(),'0')";
			$mysqli->query($sql);
		}
		else {
			// cible n'ayant pas de malus
			$gain_xp = 1;
			
			echo "<center>La cible n'avait pas de malus...</center><br />";
			
			if($bonus_recup_s){
				if($id_objet_soin != 12){
					echo "<center>Vous avez augmenté la récupération de <font color=$couleur_clan_cible>$nom_cible</font> de $bonus_recup_s</center><br />";
				}
			}
			
			//MAJ evenements perso
			$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>',' a tenté d\'apaiser ','$id_cible','<font color=$couleur_clan_cible>$nom_cible</font>',' : cible sans malus',NOW(),'0')";
			$mysqli->query($sql);
		}
		
		if($id_objet_soin > 0){
			// Suppression de l'objet
			$sql_d = "DELETE FROM perso_as_objet WHERE id_objet='$id_objet_soin' AND id_perso='$id_perso' LIMIT 1";
			$mysqli->query($sql_d);
		}
		
		//MAJ xp/pi perso et pa
		$sql = "UPDATE perso SET xp_perso=xp_perso+$gain_xp, pi_perso=pi_perso+$gain_xp, pa_perso=pa_perso-$coutPa WHERE id_perso='$id_perso'";
		$mysqli->query($sql);
		
		echo "<center>Vous avez gagné $gain_xp XP</center>";
		
	}
	else {
		echo "<center>Vous n'avez pas assez de PA</center><br />";
	}
	echo "<br /><br /><a href='jouer.php'>[ retour ]</a>";
}


/**
  * Fonction qui permet d'effectuer des soin sur une cible
  * @param $id_perso	: l'identifiant du personnage effectuant les soins
  * @param $id_ciblea	: L'identiiant du pnj ou pj (cible) recevant les soins
  * @param $id_action	: L'identifiant de l'action de soin afin de recuperer le niveau de competence
  * @param $id_objet_soin	: L'identifiant de l'objet qu'on va utiliser pour ameliorer les soins
  * @return Void
  */
function action_soin($mysqli, $id_perso, $id_cible, $id_action, $id_objet_soin){
	
	// recuperation des donnees correspondant a l'action
	$sql = "SELECT pvMin_action, pvMax_action, coutPa_action FROM action WHERE id_action=$id_action";
	$res = $mysqli->query($sql);
	$t_soin = $res->fetch_assoc();
	
	$pvMin_soin = $t_soin["pvMin_action"];
	$pvMax_soin = $t_soin["pvMax_action"];
	$coutPa = $t_soin["coutPa_action"];
	
	// calcul pv
	$pv_soin = rand($pvMin_soin, $pvMax_soin);
	
	// recuperation du pourcentage
	$pourcent = calcul_pourcentage_action($id_action);
	
	//recuperation des infos sur le perso
	$sql = "SELECT nom_perso, clan, pa_perso FROM perso WHERE id_perso='$id_perso'";
	$res = $mysqli->query($sql);
	$t_p = $res->fetch_assoc();
	
	$nom_perso = $t_p["nom_perso"];
	$pa_perso = $t_p["pa_perso"];
	$camp = $t_p["clan"];
	
	// recuperation de la couleur du camp du perso
	$couleur_clan_perso = couleur_clan($camp);
	
	// test pa
	if($pa_perso >= $coutPa){
	
		// recuperation des pv du perso cible
		$sql = "SELECT nom_perso, pv_perso, pvMax_perso, clan FROM perso WHERE id_perso='$id_cible'";
		$res = $mysqli->query($sql);
		$t_pv = $res->fetch_assoc();
		
		$nom_cible = $t_pv['nom_perso'];
		$pv_cible = $t_pv['pv_perso'];
		$pvMax_cible = $t_pv['pvMax_perso'];
		$camp_cible = $t_pv['clan'];
		
		// recuperation de la couleur du camp du perso
		$couleur_clan_cible = couleur_clan($camp_cible);
		
		// calcul gain xp
		$gain_xp = gain_xp($camp, $camp_cible);
		$gain_xp = min($gain_xp, 5);
		
		// Si on soigne un perso autre que soi meme
		if($id_perso != $id_cible){
			$gain_xp = $gain_xp * 2;
		}
		
		$bonus_pv_s = 0;
		$bonus_recup_s = 0;
		$bonus_pourcent = 0;
		
		if($id_objet_soin > 0){
			// recuperation des avantages de l'objet de soin
			$sql_s = "SELECT nom_objet, bonusPv_objet, bonusRecup_objet FROM objet WHERE id_objet='$id_objet_soin'";
			$res_s = $mysqli->query($sql_s);
			$t_s = $res_s->fetch_assoc();
			$nom_objet = $t_s['nom_objet'];
			$bonus_pv_s = $t_s['bonusPv_objet'];
			$bonus_recup_s = $t_s['bonusRecup_objet'];
		}
		
		if($id_objet_soin == 13)
			$bonus_pourcent = 10;
		
		// calcul pourcentage de pv en moins du perso
		$pourcent_pv_cible = (100 / $pvMax_cible)*($pvMax_cible - $pv_cible);
		
		if($pourcent_pv_cible <= $pourcent + $bonus_pourcent){
			// traitement soin
			if($pv_cible < $pvMax_cible){
				if($pv_cible + $pv_soin + $bonus_pv_s < $pvMax_cible){
					// calcul pv soin total
					$pv_soin_final = $pv_soin + $bonus_pv_s;
					
					if($id_objet_soin == 12){
						//MAJ pv et malus cible
						$sql = "UPDATE perso SET pv_perso=pv_perso+$pv_soin_final, bonus_perso=bonus_perso+$bonus_recup_s WHERE id_perso='$id_cible'";
						$mysqli->query($sql);
					}
					else {
						//MAJ pv et recup cible
						$sql = "UPDATE perso SET pv_perso=pv_perso+$pv_soin_final, bonusRecup_perso=bonusRecup_perso+$bonus_recup_s WHERE id_perso='$id_cible'";
						$mysqli->query($sql);
					}
					
					//MAJ xp/pi perso et pa
					$sql = "UPDATE perso SET xp_perso=xp_perso+$gain_xp, pi_perso=pi_perso+$gain_xp, pa_perso=pa_perso-$coutPa WHERE id_perso='$id_perso'";
					$mysqli->query($sql);
					
					//MAJ evenments perso
					$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>',' a soigné ','$id_cible','<font color=$couleur_clan_cible>$nom_cible</font>',' : soin de $pv_soin_final PV',NOW(),'0')";
					$mysqli->query($sql);
					
					echo "<center>Vous avez soigné <font color=$couleur_clan_cible>$nom_cible</font> de $pv_soin (+$bonus_pv_s) PV</center><br />";
					if($bonus_recup_s){
						if($id_objet_soin == 12){
							echo "<center>Vous avez soigner les malus de <font color=$couleur_clan_cible>$nom_cible</font> de $bonus_recup_s</center><br />";
						}
						else {
							echo "<center>Vous avez augmenté la récupération de <font color=$couleur_clan_cible>$nom_cible</font> de $bonus_recup_s</center><br />";
						}
					}
					echo "<center>Vous avez gagné $gain_xp XP</center>";
				}
				else {
					// on met aux pvMax de la cible
					$sql = "UPDATE perso SET pv_perso=pvMax_perso WHERE id_perso='$id_cible'";
					$mysqli->query($sql);
					
					//MAJ xp perso
					$sql = "UPDATE perso SET xp_perso=xp_perso+$gain_xp, pi_perso=pi_perso+$gain_xp, pa_perso=pa_perso-$coutPa WHERE id_perso='$id_perso'";
					$mysqli->query($sql);
					
					//MAJ evenments perso
					$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>',' a soigné ','$id_cible','<font color=$couleur_clan_cible>$nom_cible</font>',' : soin de $pv_soin PV',NOW(),'0')";
					$mysqli->query($sql);
					
					echo "<center>Vous avez soigné <font color=$couleur_clan_cible>$nom_cible</font> de $pv_soin PV</center><br />";
					echo "<center>La cible est revenu à son max de vie</center><br />";
					echo "<center>Vous avez gagné $gain_xp XP</center>";
				}
				
				if($id_objet_soin > 0){
					// Suppression de l'objet
					$sql_d = "DELETE FROM perso_as_objet WHERE id_objet='$id_objet_soin' AND id_perso='$id_perso' LIMIT 1";
					$mysqli->query($sql_d);
				}
			}
			else {
				// cible deja au max
				$gain_xp = '1';
				
				//MAJ xp perso
				$sql = "UPDATE perso SET xp_perso=xp_perso+$gain_xp, pi_perso=pi_perso+$gain_xp, pa_perso=pa_perso-$coutPa WHERE id_perso='$id_perso'";
				$mysqli->query($sql);
				
				if($bonus_recup_s){
					if($id_objet_soin == 12){
						// Soin des malus
						// MAJ recup cible
						$sql = "UPDATE perso SET bonus_perso=bonus_perso+$bonus_recup_s WHERE id_perso='$id_cible'";
						$mysqli->query($sql);
						
						// MAJ evenments perso
						$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>',' a supprimé des malus de ','$id_cible','<font color=$couleur_clan_cible>$nom_cible</font>',' : (+$bonus_recup_s) $nom_objet',NOW(),'0')";
						$mysqli->query($sql);
						
						echo "<center>Vous avez soigné des malus de <font color=$couleur_clan_cible>$nom_cible</font> grâce à $nom_objet : +$bonus_recup_s </center><br/>";
					}
					else {
						// Augmentation recup
						// MAJ recup cible
						$sql = "UPDATE perso SET bonusRecup_perso=bonusRecup_perso+$bonus_recup_s WHERE id_perso='$id_cible'";
						$mysqli->query($sql);
						
						// MAJ evenments perso
						$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>',' a augmenté la récupération de ','$id_cible','<font color=$couleur_clan_cible>$nom_cible</font>',' : (+$bonus_recup_s) $nom_objet',NOW(),'0')";
						$mysqli->query($sql);
						
						echo "<center>Vous avez augmenté la récupération de <font color=$couleur_clan_cible>$nom_cible</font> grâce à $nom_objet : +$bonus_recup_s </center><br/>";
					}
					
					if($id_objet_soin > 0){
						// Suppression de l'objet
						$sql_d = "DELETE FROM perso_as_objet WHERE id_objet='$id_objet_soin' AND id_perso='$id_perso' LIMIT 1";
						$mysqli->query($sql_d);
					}
				}
				else {
					//MAJ evenments perso
					$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>',' a joué au docteur avec ','$id_cible','<font color=$couleur_clan_cible>$nom_cible</font>',' : pv déjà au max...',NOW(),'0')";
					$mysqli->query($sql);
				}
				
				echo "<center>La cible est était déjà à son max de vie</center><br />";
				echo "<center>Vous avez gagné $gain_xp XP</center>";
			}
		}
		else {
			// Competence de soin pas assez developpee pour soigner le perso cible
			$gain_xp = '1';
			//MAJ xp perso
			$sql = "UPDATE perso SET xp_perso=xp_perso+$gain_xp, pi_perso=pi_perso+$gain_xp, pa_perso=pa_perso-$coutPa WHERE id_perso='$id_perso'";
			$mysqli->query($sql);
			
			//MAJ evenments perso
			$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>',' a raté ses soins sur ','$id_cible','<font color=$couleur_clan_cible>$nom_cible</font>',' : compétence pas assez développée...',NOW(),'0')";
			$mysqli->query($sql);
			
			echo "<center>Votre niveau dans cette compétence ne vous permet pas de soigner correctement la cible</center><br />";
			echo "<center>Vous avez gagné $gain_xp XP</center>";
		}
	}
	else {
		echo "<center>Vous n'avez pas assez de PA</center><br />";
	}
	echo "<br /><br /><a href='jouer.php'>[ retour ]</a>";
}

/**
  * Fonction qui pemet d'effectuer l'action dormir
  * @param $id_perso	: L'identifiant du personnage qui veut dormir
  * @param $nb_points_action	: Le niveau de l'action dormir
  * @return Void
  */
function action_dormir($mysqli, $id_perso, $nb_points_action){

	// recuperation des infos du perso
	$sql = "SELECT nom_perso, clan, recup_perso, bonusRecup_perso, pa_perso, paMax_perso FROM perso WHERE id_perso='$id_perso'";
	$res = $mysqli->query($sql);
	$t_p = $res->fetch_assoc();
	
	$nom_perso = $t_p["nom_perso"];
	$recup_perso = $t_p["recup_perso"];
	$pa_perso = $t_p["pa_perso"];
	$paMax_perso = $t_p["paMax_perso"];
	$br_p = $t_p["bonusRecup_perso"];
	$camp = $t_p["clan"];
	
	// recuperation de la couleur du camp du perso
	$couleur_clan_perso = couleur_clan($camp);
	
	if($nb_points_action == '1'){
		$bonus_recup = '+ 1';
		$n_r = $recup_perso + $br_p + 1;
	}
	if($nb_points_action == '2'){
		$calcul = $recup_perso * 1.5;
		$bonus_r = ceil($calcul) - $recup_perso;
		$bonus_recup = '+'.$bonus_r;
		$n_r = ceil($calcul) + $br_p;
	}
	if($nb_points_action == '3'){
		$calcul = $recup_perso * 2;
		$bonus_r = ceil($calcul) - $recup_perso;
		$bonus_recup = '+'.$bonus_r;
		$n_r = ceil($calcul) + $br_p;
	}
	
	// test pa
	if($pa_perso >= $paMax_perso){
	
		$gain_xp = '1';
	
		// maj bonus recup/ pm et xp/pi
		$sql = "UPDATE perso SET bonusRecup_perso = bonusRecup_perso $bonus_recup, pm_perso=0, pa_perso=0, xp_perso=xp_perso+$gain_xp, pi_perso=pi_perso+$gain_xp WHERE id_perso='$id_perso'";
		$mysqli->query($sql);
		
		//mise a jour de la table evenement
		$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>',' ... ',NULL,'','',NOW(),'0')";
		$mysqli->query($sql);
		
		echo "<center>Vous dormez profondément, votre récupération au prochain tour sera de : $n_r</center><br />";
		echo "<center>Vous avez gagné 1xp</center><br /><br />";
		echo "<a href='jouer.php'>[ retour ]</a>";
	}
	else {
		echo "<center>Vous n'avez pas assez de PA</center><br />";
		echo "<a href='jouer.php'>[ retour ]</a>";
	}
}

/**
  * Fonction qui permet a un personnage d'effectuer une marche forcee (+1pm contre des pv)
  * @param $id_perso	: L'identifiant du personnage qui veut faire la marche forcee
  * @param $nb_points_action	: le niveau de l'action de marche forcee
  * @param $coutPa_action	: Le cout en Pa de l'action
  * @return	Void
  */
function action_marcheForcee($mysqli, $id_perso, $nb_points_action, $coutPa_action){
	
	// recuperation des infos du perso
	$sql = "SELECT nom_perso, clan, pv_perso, x_perso, y_perso, pa_perso FROM perso WHERE id_perso='$id_perso'";
	$res = $mysqli->query($sql);
	$t_p = $res->fetch_assoc();
	
	$nom_perso = $t_p["nom_perso"];
	$pv_perso = $t_p["pv_perso"];
	$pa_perso = $t_p["pa_perso"];
	$x_perso = $t_p["x_perso"];
	$y_perso = $t_p["y_perso"];
	$camp = $t_p["clan"];
	
	// recuperation de la couleur du camp du perso
	$couleur_clan_perso = couleur_clan($camp);

	$cout_pv = 10 - (2 * ($nb_points_action - 1));
	
	// test pa
	if($pa_perso >= $coutPa_action){
		
		$gain_xp = '0';
		
		// maj pm et xp/pi
		$sql = "UPDATE perso SET pm_perso=pm_perso+1, pa_perso=pa_perso-$coutPa_action, pv_perso=pv_perso-$cout_pv, xp_perso=xp_perso+$gain_xp, pi_perso=pi_perso+$gain_xp WHERE id_perso='$id_perso'";
		$mysqli->query($sql);
		
		if($pv_perso - $cout_pv <= 0){
			// Le perso s'est tue tout seul...
			// on l'efface de la carte
			$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_perso' AND y_carte='$y_perso'";
			$mysqli->query($sql);
			
			//mise a jour de la table evenement
			$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>',' s\'est tué en effectuant une marche forcée... ',NULL,'',' : Bravo !',NOW(),'0')";
			$mysqli->query($sql);
			
			echo "<br /><center>En tentant de puiser dans vos dernières resources pour continuer d'avancer, les forces vous lachent et vous vous effondrez...</center><br />";
			echo "<center>Vous êtes Mort !</center><br />";
			echo "<center><a href='jouer.php'>[retour]</a></center>";
		}
		else {	
			//mise a jour de la table evenement
			$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>',' a effectué une marche forcée ',NULL,'',' : +1 PM',NOW(),'0')";
			$mysqli->query($sql);
		
			echo "<center>Vous vous êtes dépassé et gagnez 1PM ! ($cout_pv PV perdu)</center><br />";
			echo "<a href='jouer.php'>[ retour ]</a>";
		}
	}
	else {
		echo "<center>Vous n'avez pas assez de PA</center><br />";
		echo "<a href='jouer.php'>[ retour ]</a>";
	}
}

/**
  * Fonction qui permet a un personnage d'effectuer une course
  * @param $id_perso		: L'identifiant du personnage qui veut faire une course
  * @param $direction		: Direction de la course (1 = hg, 2 = h, 3 = hd, 4 = g, 5 = d, 6 = bg, 7 = b, 8 = bd)
  * @param $nb_points_action	: le niveau de l'action de course
  * @param $coutPa_action	: Le cout en Pa de l'action
  * @return	Void
  */
function action_courir($mysqli, $id_perso, $direction, $nb_points_action, $coutPa_action){
	
	// recuperation des infos du perso
	$sql = "SELECT x_perso, y_perso, nom_perso, clan, pm_perso, pa_perso, paMax_perso FROM perso WHERE id_perso='$id_perso'";
	$res = $mysqli->query($sql);
	$t_p = $res->fetch_assoc();
	
	$nom_perso = $t_p["nom_perso"];
	$pm_perso = $t_p["pm_perso"];
	$pa_perso = $t_p["pa_perso"];
	$paMax_perso = $t_p["paMax_perso"];
	$x_perso_depart = $t_p['x_perso'];
	$y_perso_depart = $t_p['y_perso'];
	$camp = $t_p["clan"];
	
	// recuperation de la couleur du camp du perso
	$couleur_clan_perso = couleur_clan($camp);
	
	// Calcul du bonus de mouvement en course
	if($nb_points_action == '1'){
		$bonus_mouv = 1;
	}
	if($nb_points_action == '2'){
		$calcul = $pm_perso * 1.5;
		$bonus_mouv = ceil($calcul) - $pm_perso;
	}
	if($nb_points_action == '3'){
		$calcul = $pm_perso * 2;
		$bonus_mouv = ceil($calcul) - $pm_perso;
	}
	$pm_total = $pm_perso + $bonus_mouv;
	if($pm_total == 0)
		$pm_total = 1;
	
	if($pa_perso >= $paMax_perso){
		$gain_xp = '0';
		
		//mise a jour de la table evenement
		$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>',' se met à courir ',NULL,'',' : $pm_total PM',NOW(),'0')";
		$mysqli->query($sql);
		
		$obstacle = 0;
		$pm_utilise = 0;
		
		while($pm_total > 0){
			
			// Recuperation position perso
			$sql = "SELECT x_perso, y_perso, image_perso FROM perso WHERE id_perso='$id_perso'";
			$res = $mysqli->query($sql);
			$t = $res->fetch_assoc();
			
			$x_perso = $t['x_perso'];
			$y_perso = $t['y_perso'];
			$image_perso = $t['image_perso'];
			
			// On verifie que le perso peut courir vers la prochaine position
			if($direction == 1){
				$sql_v = "SELECT occupee_carte FROM carte WHERE x_carte = $x_perso - 1 AND y_carte = $y_perso + 1";
			}
			if($direction == 2){
				$sql_v = "SELECT occupee_carte FROM carte WHERE x_carte = $x_perso AND y_carte = $y_perso + 1";
			}
			if($direction == 3){
				$sql_v = "SELECT occupee_carte FROM carte WHERE x_carte = $x_perso + 1 AND y_carte = $y_perso + 1";
			}
			if($direction == 4){
				$sql_v = "SELECT occupee_carte FROM carte WHERE x_carte = $x_perso - 1 AND y_carte = $y_perso";
			}
			if($direction == 5){
				$sql_v = "SELECT occupee_carte FROM carte WHERE x_carte = $x_perso + 1 AND y_carte = $y_perso";
			}
			if($direction == 6){
				$sql_v = "SELECT occupee_carte FROM carte WHERE x_carte = $x_perso - 1 AND y_carte = $y_perso - 1";
			}
			if($direction == 7){
				$sql_v = "SELECT occupee_carte FROM carte WHERE x_carte = $x_perso AND y_carte = $y_perso - 1";
			}
			if($direction == 8){
				$sql_v = "SELECT occupee_carte FROM carte WHERE x_carte = $x_perso + 1 AND y_carte = $y_perso - 1";
			}
			
			$res_v = $mysqli->query($sql_v);
			$t_v = $res_v->fetch_assoc();
			$occupee_carte = $t_v['occupee_carte'];
			
			if(!$occupee_carte && $occupee_carte!="" && $occupee_carte!=NULL){
				// MAJ carte position courante
				$sql_u1 = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_perso' AND y_carte='$y_perso'";
				$mysqli->query($sql_u1);
				
				// MAJ carte et perso nouvelle position
				if($direction == 1){
					$sql_u2 = "UPDATE carte SET occupee_carte='1', idPerso_carte='$id_perso', image_carte='$image_perso' WHERE x_carte = $x_perso - 1 AND y_carte = $y_perso + 1";
					$sql_u3 = "UPDATE perso SET x_perso = x_perso - 1, y_perso =y_perso + 1 WHERE id_perso='$id_perso'";
					$x_perso_final = $x_perso - 1;
					$y_perso_final = $y_perso + 1;
				}
				if($direction == 2){
					$sql_u2 = "UPDATE carte SET occupee_carte='1', idPerso_carte='$id_perso', image_carte='$image_perso' WHERE x_carte = $x_perso AND y_carte = $y_perso + 1";
					$sql_u3 = "UPDATE perso SET x_perso = x_perso, y_perso = y_perso + 1 WHERE id_perso='$id_perso'";
					$x_perso_final = $x_perso;
					$y_perso_final = $y_perso + 1;
				}
				if($direction == 3){
					$sql_u2 = "UPDATE carte SET occupee_carte='1', idPerso_carte='$id_perso', image_carte='$image_perso' WHERE x_carte = $x_perso + 1 AND y_carte = $y_perso + 1";
					$sql_u3 = "UPDATE perso SET x_perso = x_perso + 1, y_perso = y_perso + 1 WHERE id_perso='$id_perso'";
					$x_perso_final = $x_perso + 1;
					$y_perso_final = $y_perso + 1;
				}
				if($direction == 4){
					$sql_u2 = "UPDATE carte SET occupee_carte='1', idPerso_carte='$id_perso', image_carte='$image_perso' WHERE x_carte = $x_perso - 1 AND y_carte = $y_perso";
					$sql_u3 = "UPDATE perso SET x_perso = x_perso - 1, y_perso = y_perso WHERE id_perso='$id_perso'";
					$x_perso_final = $x_perso - 1;
					$y_perso_final = $y_perso;
				}
				if($direction == 5){
					$sql_u2 = "UPDATE carte SET occupee_carte='1', idPerso_carte='$id_perso', image_carte='$image_perso' WHERE x_carte = $x_perso + 1 AND y_carte = $y_perso";
					$sql_u3 = "UPDATE perso SET x_perso = x_perso + 1, y_perso = y_perso WHERE id_perso='$id_perso'";
					$x_perso_final = $x_perso + 1;
					$y_perso_final = $y_perso;
				}
				if($direction == 6){
					$sql_u2 = "UPDATE carte SET occupee_carte='1', idPerso_carte='$id_perso', image_carte='$image_perso' WHERE x_carte = $x_perso - 1 AND y_carte = $y_perso - 1";
					$sql_u3 = "UPDATE perso SET x_perso = x_perso - 1, y_perso =y_perso - 1 WHERE id_perso='$id_perso'";
					$x_perso_final = $x_perso - 1;
					$y_perso_final = $y_perso - 1;
				}
				if($direction == 7){
					$sql_u2 = "UPDATE carte SET occupee_carte='1', idPerso_carte='$id_perso', image_carte='$image_perso' WHERE x_carte = $x_perso AND y_carte = $y_perso - 1";
					$sql_u3 = "UPDATE perso SET x_perso = x_perso, y_perso =y_perso - 1 WHERE id_perso='$id_perso'";
					$x_perso_final = $x_perso;
					$y_perso_final = $y_perso - 1;
				}
				if($direction == 8){
					$sql_u2 = "UPDATE carte SET occupee_carte='1', idPerso_carte='$id_perso', image_carte='$image_perso' WHERE x_carte = $x_perso + 1 AND y_carte = $y_perso - 1";
					$sql_u3 = "UPDATE perso SET x_perso = x_perso + 1, y_perso =y_perso - 1 WHERE id_perso='$id_perso'";
					$x_perso_final = $x_perso + 1;
					$y_perso_final = $y_perso - 1;
				}				
				$mysqli->query($sql_u2);
				$mysqli->query($sql_u3);				
				
				$pm_total = $pm_total - 1;
				$pm_utilise = $pm_utilise + 1;
			}
			else {
				$pm_total = 0;
				$obstacle = 1;
			}
		}
		
		// MAJ PM/PA perso
		$sql = "UPDATE perso SET pa_perso=0, pm_perso=0 WHERE id_perso='$id_perso'";
		$mysqli->query($sql);
		
		//mise a jour de la table evenement
		$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>',' a couru de $x_perso_depart / $y_perso_depart jusqu\'à $x_perso_final / $y_perso_final',NULL,'',' : $pm_utilise PM',NOW(),'0')";
		$mysqli->query($sql);
		
		echo "<center>Votre course vous fait gagner : + $bonus_mouv PM</center><br />";
		if($obstacle) {
			echo "<center>Vous avez rencontré un obstacle</center><br />";
		}
		echo "<center>Vous avez utilisé $pm_utilise PM et êtes arrivé en $x_perso_final / $y_perso_final</center><br />";
		echo "<a href='jouer.php'>[ retour ]</a>";
	}
	else {
		echo "<center>Vous n'avez pas assez de PA</center><br />";
		echo "<a href='jouer.php'>[ retour ]</a>";
	}
}

/**
  * Fonction qui permet de couper du bois
  * @param $id_perso	: L'identifiant du personnage qui va couper du bois
  * @param $id_action	: L'identifiant de l'action (A SUPPRIMER !!!)
  * @param $nb_points_action	: Le niveau de l'action, permettant de determiner le nombre de morceaux de bois recuperes
  * @return Void
  */
function action_couper_bois($mysqli, $id_perso, $id_action, $nb_points_action){
	
	// recuperation des coordonnees du perso
	$sql = "SELECT x_perso, y_perso, nom_perso, clan, pa_perso FROM perso WHERE id_perso='$id_perso'";
	$res = $mysqli->query($sql);
	$t_c = $res->fetch_assoc();
	
	$x_perso = $t_c['x_perso'];
	$y_perso = $t_c['y_perso'];
	$nom_perso = $t_c["nom_perso"];
	$pa_perso = $t_c["pa_perso"];
	$camp = $t_c["clan"];
	
	// recuperation de la couleur du camp du perso
	$couleur_clan_perso = couleur_clan($camp);
	
	// Cout de l'action en Pa
	$cout_pa = '7';
	
	if($pa_perso >= $cout_pa){
		
		// recuperation du fond de carte
		$sql = "SELECT fond_carte FROM carte WHERE x_carte='$x_perso' and y_carte='$y_perso'";
		$res = $mysqli->query($sql);
		$t_f = $res->fetch_assoc();
		$fond_carte = $t_f["fond_carte"];
		
		// verification que le perso est bien sur une case de foret
		if($fond_carte == '7.gif'){
			
			// calcul des gain de bois
			$gain_bois = rand(1, $nb_points_action);
			$charge_bois = 2 * $gain_bois;
			
			$gain_xp = '1';
			
			// MAJ xp/pi/pa/charge perso
			$sql = "UPDATE perso SET xp_perso=xp_perso+$gain_xp, pi_perso=pi_perso+$gain_xp, pa_perso=pa_perso-$cout_pa, charge_perso=charge_perso+$charge_bois WHERE id_perso='$id_perso'";
			$mysqli->query($sql);
			
			// MAJ fond carte
			$sql = "UPDATE carte SET fond_carte='1.gif' WHERE x_carte='$x_perso' AND y_carte='$y_perso'";
			$mysqli->query($sql);
			
			// MAJ objets perso
			for ($i = 1; $i <= $gain_bois; $i++){
				$sql = "INSERT INTO perso_as_objet VALUES('$id_perso', '7')";
				$mysqli->query($sql);
			}
			
			//mise a jour de la table evenement
			$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>',' a coupé des arbres ',NULL,'',' : + $gain_bois morceaux de bois',NOW(),'0')";
			$mysqli->query($sql);
			
			echo "<center><font color=red><b>Vous avez coupé une forêt, vous avez récupéré $gain_bois morceaux de bois </b></font></center><br />";
			echo "<center>Vous avez gagné 1xp</center><br /><br />";
			echo "<a href='jouer.php'>[ retour ]</a>";
		}
		else {
			echo "<center><font color=red><b>Vous devez être sur une case de forêt afin de pouvoir la couper</b></font></center><br />";
			echo "<a href='jouer.php'>[ retour ]</a>";
		}
	}
	else {
		echo "<center>Vous n'avez pas assez de PA</center><br />";
		echo "<a href='jouer.php'>[ retour ]</a>";
	}
}

/**
  * Fonction qui permet de recuperer du minerais dans la montagne
  * @param $id_perso	: L'identifiant du personnage qui va miner la montagne
  * @param $id_action	: L'identifiant de l'action
  * @param $nb_points_action	: Le niveau de l'action
  * @return Void
  */
function action_miner_montagne($mysqli, $id_perso, $id_action, $nb_points_action){
	
	// recuperation des coordonnees du perso
	$sql = "SELECT x_perso, y_perso, nom_perso, clan, pa_perso FROM perso WHERE id_perso='$id_perso'";
	$res = $mysqli->query($sql);
	$t_c = $res->fetch_assoc();
	
	$x_perso = $t_c['x_perso'];
	$y_perso = $t_c['y_perso'];
	$nom_perso = $t_c["nom_perso"];
	$pa_perso = $t_c["pa_perso"];
	$camp = $t_c["clan"];
	
	// recuperation de la couleur du camp du perso
	$couleur_clan_perso = couleur_clan($camp);
	
	// Cout en Pa de l'action
	$cout_pa = '10';
	
	if($pa_perso >= $cout_pa){
		
		// recuperation du fond de carte
		$sql = "SELECT fond_carte FROM carte WHERE x_carte='$x_perso' and y_carte='$y_perso'";
		$res = $mysqli->query($sql);
		$t_f = $res->fetch_assoc();
		$fond_carte = $t_f["fond_carte"];
		
		// verification que le perso est bien sur une case de montagne
		if($fond_carte == '3.gif'){
			
			// calcul chance de recuperer du fer
			$chance = rand(0,100);
			
			if(est_chanceux($id_perso)){
				$bonus_chance = 2 * est_chanceux($id_perso);
				echo "Bonus donné par la chance : ".$bonus_chance."<br/>";
			}
			else {
				$bonus_chance = 0;
			}
			
			if($chance + $bonus_chance >= 50){
				
				// calcul des gain de fer
				$gain_fer = rand(1, 3);
				$charge_fer = 1 * $gain_fer;
				
				$gain_xp = '1';
				
				// MAJ xp/pi/pa/charge perso
				$sql = "UPDATE perso SET xp_perso=xp_perso+$gain_xp, pi_perso=pi_perso+$gain_xp, pa_perso=pa_perso-$cout_pa, charge_perso=charge_perso+$charge_fer WHERE id_perso='$id_perso'";
				$mysqli->query($sql);
				
				// MAJ objets perso
				for ($i = 1; $i <= $gain_fer; $i++){
					$sql = "INSERT INTO perso_as_objet VALUES('$id_perso', '8')";
					$mysqli->query($sql);
				}
				
				//mise a jour de la table evenement
				$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>',' a miné la montagne ',NULL,'',' : + $gain_fer morceaux de fer',NOW(),'0')";
				$mysqli->query($sql);
				
				echo "<center><font color=red><b>Vous avez miné la montagne, vous avez récupéré $gain_fer morceaux de fer </b></font></center><br />";
				echo "<center>Vous avez gagné 2xp</center><br /><br />";
				echo "<a href='jouer.php'>[ retour ]</a>";
			}
			else {
				$gain_fer = 0;
			
				// MAJ xp/pi/pa/charge perso
				$sql = "UPDATE perso SET xp_perso=xp_perso+1, pi_perso=pi_perso+1, pa_perso=pa_perso-$cout_pa WHERE id_perso='$id_perso'";
				$mysqli->query($sql);
				
				//mise a jour de la table evenement
				$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>',' a miné la montagne ',NULL,'',' : + $gain_fer morceaux de fer, pas de chance...',NOW(),'0')";
				$mysqli->query($sql);
				
				echo "<center><font color=red><b>Vous avez miné la montagne mais vous n'avez rien trouvé </b></font></center><br />";
				echo "<center>Vous avez gagné 1xp</center><br /><br />";
				echo "<a href='jouer.php'>[ retour ]</a>";
			}
		}
		else {
			echo "<center><font color=red><b>Vous devez être sur une case de montagne afin de pouvoir la miner</b></font></center><br />";
			echo "<a href='jouer.php'>[ retour ]</a>";
		}
	}
	else {
		echo "<center>Vous n'avez pas assez de PA</center><br />";
		echo "<a href='jouer.php'>[ retour ]</a>";
	}
}

/**
  * Fonction qui permet d'utiliser l'action sauter
  * @param $id_perso	: L'identifia,t du personnage qui veut sauter
  * @param $x_cible	: La coordonnee x de la case cible
  * @param $y_cible	: La coordonnee y de la case cible
  * @param $coutpa	: Le cout en Points d'action
  * @return Void
  */
function action_sauter($mysqli, $id_perso, $x_cible, $y_cible, $coutPa, $carte){
	
	// recuperation du cout de pm de la case cible
	$sql = "SELECT fond_carte FROM $carte WHERE x_carte=$x_cible AND y_carte=$y_cible";
	$res = $mysqli->query($sql);
	$t_f = $res->fetch_assoc();
	
	$fond_carte = $t_f["fond_carte"];
	$cout_pm_cible = cout_pm($fond_carte);
	$cout_pm_total = $cout_pm_cible + 1;

	// recuperation des pm du perso
	$sql = "SELECT nom_perso, pm_perso, x_perso, y_perso, image_perso, clan, pa_perso FROM perso WHERE id_perso='$id_perso'";
	$res = $mysqli->query($sql);
	$t_pm = $res->fetch_assoc();
	
	$nom_perso = $t_pm["nom_perso"];
	$pm_perso = $t_pm["pm_perso"];
	$x_perso = $t_pm["x_perso"];
	$y_perso = $t_pm["y_perso"];
	$pa_perso = $t_pm["pa_perso"];
	$image_perso = $t_pm["image_perso"];
	$camp = $t_pm["clan"];
	
	// recuperation de la couleur du camp du perso
	$couleur_clan_perso = couleur_clan($camp);
	
	// tests pa
	if($pa_perso >= $coutPa){
		// test si assez de pm
		if($pm_perso >= $cout_pm_total){
		
			$gain_xp = '0';
			
			// MAJ xp/pi/pa/pm/x et y perso
			$sql = "UPDATE perso SET x_perso=$x_cible, y_perso=$y_cible, xp_perso=xp_perso+$gain_xp, pi_perso=pi_perso+$gain_xp, pa_perso=pa_perso-$coutPa, pm_perso=pm_perso-$cout_pm_total WHERE id_perso='$id_perso'";
			$mysqli->query($sql);
			
			// MAJ carte
			$sql = "UPDATE $carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_perso' AND y_carte='$y_perso'";
			$mysqli->query($sql);
			
			$sql = "UPDATE $carte SET occupee_carte='1', idPerso_carte='$id_perso', image_carte='$image_perso' WHERE x_carte='$x_cible' AND y_carte='$y_cible'";
			$mysqli->query($sql);
			
			//mise a jour de la table evenement
			$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>',' a sauté ',NULL,'',' en $x_cible / $y_cible',NOW(),'0')";
			$mysqli->query($sql);
			
			echo "<center><font color=red><b>Vous avez sauté en $x_cible/$y_cible, cout total en PM : $cout_pm_total PM</b></font></center><br />";
			echo "<center>Vous avez gagné $gain_xp XP</center><br /><br />";
			
		}
		else {
			// pas assez de pm
			echo "<center><font color=red><b>Vous ne possédez pas assez de pm pour sauter sur cette case, il vous faut $cout_pm_total PM</b></font></center><br />";
		}
	}
	else {
		echo "<center>Vous n'avez pas assez de PA</center><br />";
	}
	echo "<a href='jouer.php'>[ retour ]</a>";
}

/**
  * Fonction qui permet de planter un arbre
  * @param $id_perso	: L'identifiant du personnage qui veut planter un arbre
  * @param $id_action	: L'identifiant de l'action
  * @param $nb_points_action	: Le niveau de l'action
  * @return Void
  */
function action_planterArbre($mysqli, $id_perso, $id_action, $nb_points_action){

	// recuperation des donnees correspondant a l'action
	$sql = "SELECT pvMin_action, pvMax_action, coutPa_action FROM action WHERE id_action=$id_action";
	$res = $mysqli->query($sql);
	$t_action = $res->fetch_assoc();
	$coutPa = $t_action["coutPa_action"];
	
	// verification que la case soit de la plaine
	$sql = "SELECT fond_carte FROM carte,perso WHERE x_carte=x_perso AND y_carte=y_perso AND id_perso='$id_perso'";
	$res = $mysqli->query($sql);
	$t_fond = $res->fetch_assoc();
	$fond_carte = $t_fond['fond_carte'];
	
	if($fond_carte == '1.gif'){
		
		// recuperation des infos du perso
		$sql = "SELECT nom_perso, clan, x_perso, y_perso, pa_perso FROM perso WHERE id_perso='$id_perso'";
		$res = $mysqli->query($sql);
		$t_perso = $res->fetch_assoc();
		
		$nom_perso = $t_perso['nom_perso'];
		$camp_perso = $t_perso['clan'];
		$x_perso = $t_perso['x_perso'];
		$y_perso = $t_perso['y_perso'];
		$pa_perso = $t_perso['pa_perso'];
		
		// recuperation de la couleur du camp du perso
		$couleur_clan_perso = couleur_clan($camp_perso);
		
		if($pa_perso >= $coutPa){
		
			// gains xp
			$gain_xp = rand(1,3);
			
			// Insertion dans la table pousse_foret
			$sql = "INSERT INTO pousse_foret VALUES('$x_perso','$y_perso','1')";
			$mysqli->query($sql);
			
			// MAJ xp/pi/pa/pm/x et y perso
			$sql = "UPDATE perso SET xp_perso=xp_perso+$gain_xp, pi_perso=pi_perso+$gain_xp, pa_perso=pa_perso-$coutPa WHERE id_perso='$id_perso'";
			$mysqli->query($sql);
		
			//mise a jour de la table evenement
			$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>',' a planté des arbres ',NULL,'',' en $x_perso / $y_perso',NOW(),'0')";
			$mysqli->query($sql);
			
			echo "<center>Vous avez planté des arbres en $x_perso / $y_perso, ils pousseront automatiquement d'ici 5 jours environs</center>";
			echo "<center>Vous avez gagné $gain_xp XP</center><br /><br />";
		}
		else {
			echo "<center>Vous n'avez pas assez de PA</center><br />";
		}
	}
	else {
		echo "<center>Vous ne pouvez pas planter d'arbre en dehors d'une case de plaine</center>";
	} 
	echo "<a href='jouer.php'>[ retour ]</a>";
}

/**
  * Fonction qui permet d'effectuer l'action de sabotage
  * @param $id_perso	: L'identifiant du personnage qui veut saboter
  * @param $id_bat		: identifiant du batiment à saboter
  * @param $id_action	: L'identifiant de l'action
  * @return Void
  */
function action_saboter($mysqli, $id_perso, $id_bat, $id_action){
	
	// recuperation des donnees correspondant a l'action
	$sql = "SELECT pvMin_action, pvMax_action, coutPa_action FROM action WHERE id_action='$id_action'";
	$res = $mysqli->query($sql);
	$t_action = $res->fetch_assoc();
	
	$coutPa = $t_action["coutPa_action"];
	
	// verification que le perso est sur un pont ou une route
	if(prox_bat_perso($mysqli, $id_perso, $id_bat)){ 
	
		// récupération des infos du pont 
		$sql = "SELECT x_instance, y_instance, pv_instance FROM instance_batiment WHERE id_instanceBat = '$id_bat'";
		$res = $mysqli->query($sql);
		$t_bat = $res->fetch_assoc();
		
		$x_bat 	= $t_bat["x_instance"];
		$y_bat 	= $t_bat["y_instance"];
		$pv_bat = $t_bat["pv_instance"];
	   
		// recuperation des infos du perso
		$sql = "SELECT nom_perso, clan, x_perso, y_perso, pa_perso FROM perso WHERE id_perso='$id_perso'";
		$res = $mysqli->query($sql);
		$t_perso = $res->fetch_assoc();
		
		$nom_perso = $t_perso['nom_perso'];
		$camp_perso = $t_perso['clan'];
		$pa_perso = $t_perso['pa_perso'];
		
		// recuperation de la couleur du camp du perso
		$couleur_clan_perso = couleur_clan($camp_perso);
		
		if($pa_perso >= $coutPa){
		
			// gains xp
			$gain_xp = rand(1,3);
			
			// calcul pourcentage de reussite
			$pourcentage_reussite = 60;
			
			$reussite = rand(0,100);
			
			// chance
			if(est_chanceux($mysqli, $id_perso)){
				$bonus_chance = 2 * est_chanceux($id_perso);
			}
			else {
				$bonus_chance = 0;
			}
			
			if($reussite <= $pourcentage_reussite + $bonus_chance){
				
				$degats_sabotage = rand(50,200);
				
				$pv_final_bat = $pv_bat - $degats_sabotage;
				
				if ($pv_final_bat <= 0) {
				
					// MAJ carte
					$sql = "UPDATE carte SET fond_carte='8.gif', idPerso_carte=NULL WHERE x_carte=$x_bat AND y_carte=$y_bat";
					$mysqli->query($sql);
					
					// Suppression instance bat 
					$sql = "DELETE FROM instance_batiment WHERE id_instanceBat = '$id_bat'";
					$mysqli->query($sql);
					
					//mise a jour de la table evenement
					$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>',' a détruit un pont ',NULL,'',' en $x_bat / $y_bat',NOW(),'0')";
					$mysqli->query($sql);
					
					echo "<center>Vous avez détruit un pont en $x_bat / $y_bat</center>";
				} 
				else {
					
					// mise à jour pv pont
					$sql = "UPDATE instance_batiment SET pv_instance = $pv_final_bat WHERE id_instanceBat = '$id_bat'";
					$mysqli->query($sql);
					
					//mise a jour de la table evenement
					$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>',' a saboté un pont ',NULL,'',' en $x_bat / $y_bat',NOW(),'0')";
					$mysqli->query($sql);
					
					echo "<center>Vous avez saboté un pont en $x_bat / $y_bat : $degats_sabotage dégats</center>";
				}
			}
			else {
				$gain_xp = 1;
				
				//mise a jour de la table evenement
				$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>',' a raté son sabotage ',NULL,'',' en $x_bat / $y_bat',NOW(),'0')";
				$mysqli->query($sql);
				
				echo "<center>Vous avez raté votre sabotage</center>";
			}
			
			// MAJ xp/pi/pa perso
			$sql = "UPDATE perso SET xp_perso=xp_perso+$gain_xp, pi_perso=pi_perso+$gain_xp, pa_perso=pa_perso-$coutPa WHERE id_perso='$id_perso'";
			$mysqli->query($sql);
			
			echo "<center>Vous avez gagné $gain_xp XP</center><br /><br />";
		}
		else {
			echo "<center>Vous n'avez pas assez de PA</center><br />";
		}
	}
	else {
		echo "<center>Vous devez être sur une case de route ou de pont afin de pouvoir saboter</center>";
	}
	echo "<a href='jouer.php'>[ retour ]</a>";
}

/**
  * Fonction qui permet d'ajouter un evenement chant
  * @param $id_perso	: L'identifiant du perso qui veut chanter
  * @param $id_action	: L'identifiant de l'action afin de connaitre le niveau
  * @return Void
  */
function action_chanter($mysqli, $id_perso,$id_action){

	// recuperation des donnees correspondant a l'action
	$sql = "SELECT pvMin_action, pvMax_action, coutPa_action FROM action WHERE id_action='$id_action'";
	$res = $mysqli->query($sql);
	$t_action = $res->fetch_assoc();
	$coutPa = $t_action["coutPa_action"];

	// recuperation des infos du perso
	$sql = "SELECT nom_perso, clan, pa_perso FROM perso WHERE id_perso='$id_perso'";
	$res = $mysqli->query($sql);
	$t_perso = $res->fetch_assoc();
	
	$nom_perso = $t_perso['nom_perso'];
	$camp_perso = $t_perso['clan'];
	$pa_perso = $t_perso['pa_perso'];
	
	// recuperation de la couleur du camp du perso
	$couleur_clan_perso = couleur_clan($camp_perso);
	
	if($pa_perso >= $coutPa){
		//mise a jour de la table evenement
		$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>',' a chanté ',NULL,'','',NOW(),'0')";
		$mysqli->query($sql);
		
		// MAJ xp/pi/pa/pm/x et y perso
		$sql = "UPDATE perso SET pa_perso=pa_perso-$coutPa WHERE id_perso='$id_perso'";
		$mysqli->query($sql);
		
		echo "<center>Vous avez chanté</center>";
	}
	else {
		echo "<center>Vous n'avez pas assez de PA</center><br />";
	}
	echo "<a href='jouer.php'>[ retour ]</a>";
}

/**
  * Fonction qui permet d'ajouter un evenement chant
  * @param $id_perso	: L'identifiant du perso qui veut chanter
  * @param $phrase	: La phrase a mettre dans les evenements
  * @return Void
  */
function action_chanter_perso($mysqli, $id_perso,$phrase){

	// recuperation des infos du perso
	$sql = "SELECT nom_perso, clan, pa_perso FROM perso WHERE id_perso='$id_perso'";
	$res = $mysqli->query($sql);
	$t_perso = $res->fetch_assoc();
	
	$nom_perso = $t_perso['nom_perso'];
	$camp_perso = $t_perso['clan'];
	$pa_perso = $t_perso['pa_perso'];
	
	// recuperation de la couleur du camp du perso
	$couleur_clan_perso = couleur_clan($camp_perso);
	
	if($pa_perso >= 2){
		//mise a jour de la table evenement
		$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>',' $phrase ',NULL,'','',NOW(),'0')";
		$mysqli->query($sql);
		
		// MAJ xp/pi/pa/pm/x et y perso
		$sql = "UPDATE perso SET pa_perso=pa_perso-2 WHERE id_perso='$id_perso'";
		$mysqli->query($sql);
		
		echo "<center>$nom_perso ". stripslashes($phrase) ."</center>";
	}
	else {
		echo "<center>Vous n'avez pas assez de PA</center><br />";
	}
	echo "<a href='jouer.php'>[ retour ]</a>";
}

/**
  * Fonction qui permet d'ajouter un evenement danse
  * @param $id_perso	: L'identifiant du perso qui veut danser
  * @param $id_action	: l'identifiant de l'action afin de connaitre le niveau
  * @return Void
  */
function action_danser($mysqli, $id_perso,$id_action){

	// recuperation des donnees correspondant a l'action
	$sql = "SELECT pvMin_action, pvMax_action, coutPa_action FROM action WHERE id_action='$id_action'";
	$res = $mysqli->query($sql);
	$t_action = $res->fetch_assoc();
	$coutPa = $t_action["coutPa_action"];

	// recuperation des infos du perso
	$sql = "SELECT nom_perso, clan, pa_perso FROM perso WHERE id_perso='$id_perso'";
	$res = $mysqli->query($sql);
	$t_perso = $res->fetch_assoc();
	
	$nom_perso = $t_perso['nom_perso'];
	$camp_perso = $t_perso['clan'];
	$pa_perso = $t_perso['pa_perso'];
	
	// recuperation de la couleur du camp du perso
	$couleur_clan_perso = couleur_clan($camp_perso);
	
	if($pa_perso >= $coutPa){
		//mise a jour de la table evenement
		$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>',' a dansé ',NULL,'','',NOW(),'0')";
		$mysqli->query($sql);
		
		// MAJ xp/pi/pa/pm/x et y perso
		$sql = "UPDATE perso SET pa_perso=pa_perso-$coutPa WHERE id_perso='$id_perso'";
		$mysqli->query($sql);
		
		echo "<center>Vous avez dansé</center>";
	}
	else {
		echo "<center>Vous n'avez pas assez de PA</center><br />";
	}
	echo "<a href='jouer.php'>[ retour ]</a>";
}

/**
  * Fonction qui permet d'ajouter un evenement danse
  * @param $id_perso	: L'identifiant du perso qui veut danser
  * @param $phrase	: La phrase a mettre dans les evenements
  * @return Void
  */
function action_danser_perso($mysqli, $id_perso, $phrase){

	// recuperation des infos du perso
	$sql = "SELECT nom_perso, clan, pa_perso FROM perso WHERE id_perso='$id_perso'";
	$res = $mysqli->query($sql);
	$t_perso = $res->fetch_assoc();
	
	$nom_perso = $t_perso['nom_perso'];
	$camp_perso = $t_perso['clan'];
	$pa_perso = $t_perso['pa_perso'];
	
	// recuperation de la couleur du camp du perso
	$couleur_clan_perso = couleur_clan($camp_perso);
	
	if($pa_perso >= 2){
		//mise a jour de la table evenement
		$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>',' $phrase ',NULL,'','',NOW(),'0')";
		$mysqli->query($sql);
		
		// MAJ xp/pi/pa/pm/x et y perso
		$sql = "UPDATE perso SET pa_perso=pa_perso-2 WHERE id_perso='$id_perso'";
		$mysqli->query($sql);
		
		echo "<center>$nom_perso ". stripslashes($phrase) ."</center>";
	}
	else {
		echo "<center>Vous n'avez pas assez de PA</center><br />";
	}
	echo "<a href='jouer.php'>[ retour ]</a>";
}

/**
  * Fonction qui permet d'ajouter un evenement peinture
  * @param $id_perso	: L'identifiant du perso qui veut peindre
  * @param $id_action	: l'identifiant de l'action afin de connaitre le niveau
  * @return Void
  */
function action_peindre($mysqli, $id_perso, $id_action){

	// recuperation des donnees correspondant a l'action
	$sql = "SELECT pvMin_action, pvMax_action, coutPa_action FROM action WHERE id_action='$id_action'";
	$res = $mysqli->query($sql);
	$t_action = $res->fetch_assoc();
	$coutPa = $t_action["coutPa_action"];

	// recuperation des infos du perso
	$sql = "SELECT nom_perso, clan, pa_perso FROM perso WHERE id_perso='$id_perso'";
	$res = $mysqli->query($sql);
	$t_perso = $res->fetch_assoc();
	
	$nom_perso = $t_perso['nom_perso'];
	$camp_perso = $t_perso['clan'];
	$pa_perso = $t_perso['pa_perso'];
	
	// recuperation de la couleur du camp du perso
	$couleur_clan_perso = couleur_clan($camp_perso);
	
	if($pa_perso >= $coutPa){
		//mise a jour de la table evenement
		$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>',' a peind ',NULL,'','',NOW(),'0')";
		$mysqli->query($sql);
		
		// MAJ xp/pi/pa/pm/x et y perso
		$sql = "UPDATE perso SET pa_perso=pa_perso-$coutPa WHERE id_perso='$id_perso'";
		$mysqli->query($sql);
		
		echo "<center>Vous avez peind</center>";
	}
	else {
		echo "<center>Vous n'avez pas assez de PA</center><br />";
	}
	echo "<a href='jouer.php'>[ retour ]</a>";
}

/**
  * Fonction qui permet d'ajouter un evenement peinture
  * @param $id_perso	: L'identifiant du perso qui veut peindre
  * @param $phrase	: La phrase a mettre dans les evenements
  * @return Void
  */
function action_peindre_perso($mysqli, $id_perso,$phrase){

	// recuperation des infos du perso
	$sql = "SELECT nom_perso, clan, pa_perso FROM perso WHERE id_perso='$id_perso'";
	$res = $mysqli->query($sql);
	$t_perso = $res->fetch_assoc();
	
	$nom_perso = $t_perso['nom_perso'];
	$camp_perso = $t_perso['clan'];
	$pa_perso = $t_perso['pa_perso'];
	
	// recuperation de la couleur du camp du perso
	$couleur_clan_perso = couleur_clan($camp_perso);
	
	if($pa_perso >= 2){
		//mise a jour de la table evenement
		$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>',' $phrase ',NULL,'','',NOW(),'0')";
		$mysqli->query($sql);
		
		// MAJ xp/pi/pa/pm/x et y perso
		$sql = "UPDATE perso SET pa_perso=pa_perso-2 WHERE id_perso='$id_perso'";
		$mysqli->query($sql);
		
		echo "<center>$nom_perso ". stripslashes($phrase) ."</center>";
	}
	else {
		echo "<center>Vous n'avez pas assez de PA</center><br />";
	}
	echo "<a href='jouer.php'>[ retour ]</a>";
}

/**
  * Fonction qui permet d'ajouter un evenement sculpture
  * @param $id_perso	: L'identifiant du perso qui veut scuplter
  * @param $id_action	: l'identifiant de l'action afin de connaitre le niveau
  * @return Void
  */
function action_sculter($mysqli, $id_perso,$id_action){

	// recuperation des donnees correspondant a l'action
	$sql = "SELECT pvMin_action, pvMax_action, coutPa_action FROM action WHERE id_action='$id_action'";
	$res = $mysqli->query($sql);
	$t_action = $res->fetch_assoc();
	$coutPa = $t_action["coutPa_action"];

	// recuperation des infos du perso
	$sql = "SELECT nom_perso, clan, pa_perso FROM perso WHERE id_perso='$id_perso'";
	$res = $mysqli->query($sql);
	$t_perso = $res->fetch_assoc();
	
	$nom_perso = $t_perso['nom_perso'];
	$camp_perso = $t_perso['clan'];
	$pa_perso = $t_perso['pa_perso'];
	
	// recuperation de la couleur du camp du perso
	$couleur_clan_perso = couleur_clan($camp_perso);
	
	if($pa_perso >= $coutPa){
		//mise a jour de la table evenement
		$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>',' a scuplté ',NULL,'','',NOW(),'0')";
		$mysqli->query($sql);
		
		// MAJ xp/pi/pa/pm/x et y perso
		$sql = "UPDATE perso SET pa_perso=pa_perso-$coutPa WHERE id_perso='$id_perso'";
		$mysqli->query($sql);
		
		echo "<center>Vous avez sculpté</center>";
	}
	else {
		echo "<center>Vous n'avez pas assez de PA</center><br />";
	}
	echo "<a href='jouer.php'>[ retour ]</a>";
}

/**
  * Fonction qui permet d'ajouter un evenement sculpture
  * @param $id_perso	: L'identifiant du perso qui veut scuplter
  * @param $phrase	: La phrase a mettre dans les evenements
  * @return Void
  */
function action_scuplter_perso($mysqli, $id_perso, $phrase){

	// recuperation des infos du perso
	$sql = "SELECT nom_perso, clan, pa_perso FROM perso WHERE id_perso='$id_perso'";
	$res = $mysqli->query($sql);
	$t_perso = $res->fetch_assoc();
	
	$nom_perso = $t_perso['nom_perso'];
	$camp_perso = $t_perso['clan'];
	$pa_perso = $t_perso['pa_perso'];
	
	// recuperation de la couleur du camp du perso
	$couleur_clan_perso = couleur_clan($camp_perso);
	
	if($pa_perso >= 2){
		//mise a jour de la table evenement
		$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>',' $phrase ',NULL,'','',NOW(),'0')";
		$mysqli->query($sql);
		
		// MAJ xp/pi/pa/pm/x et y perso
		$sql = "UPDATE perso SET pa_perso=pa_perso-2 WHERE id_perso='$id_perso'";
		$mysqli->query($sql);
		
		echo "<center>$nom_perso ". stripslashes($phrase) ."</center>";
	}
	else {
		echo "<center>Vous n'avez pas assez de PA</center><br />";
	}
	echo "<a href='jouer.php'>[ retour ]</a>";
}

/**
  * Fonction qui permet de faire l'action entrainement a un personnage
  * @param $id_perso	: L'identifiant du perso qui veut s'entrainer
  * @return Void
  */
function action_entrainement($mysqli, $id_perso){
	
	// verification si le perso s'est deja entraine auparavent
	$sql = "SELECT niveau_entrainement, nb FROM perso_as_entrainement WHERE id_perso='$id_perso'";
	$res = $mysqli->query($sql);
	$entraine = $res->num_rows;
	
	// recuperation du nombre de PA et infos du perso
	$sql_pa = "SELECT nom_perso, pa_perso, clan FROM perso WHERE id_perso='$id_perso'";
	$res_pa = $mysqli->query($sql_pa);
	$t_pa = $res_pa->fetch_assoc();
	
	$nom_perso = $t_pa['nom_perso'];
	$pa_perso = $t_pa['pa_perso'];
	$camp = $t_pa['clan'];
	
	// recuperation de la couleur du camp du perso
	$couleur_clan_perso = couleur_clan($camp);
	
	if(est_chanceux($mysqli, $id_perso)){
		$bonus_chance = 2 * est_chanceux($id_perso);
		echo "Bonus donné par la chance : ".$bonus_chance."<br/>";
	}
	else {
		$bonus_chance = 0;
	}
	
	if($pa_perso >= 10){
		// deja en base
		if($entraine){
			
			$t_e = $res->fetch_assoc();
			$niveau_e = $t_e['niveau_entrainement'];
			$nb_e = $t_e['nb'];
			
			// calcul pourcentage de reussite
			$sup = 100-$niveau_e;
			$mil = ceil($sup/2);
			$succes = mt_rand(0,100);
			
			if($succes <= $mil + $bonus_chance){
				
				if($nb_e == $niveau_e-1){
					$new_niveau = $niveau_e+1;
					$gain_xp = $new_niveau*2;
				
					// maj xp/pi/pa
					$sql = "UPDATE perso SET xp_perso=xp_perso+$gain_xp, pi_perso=pi_perso+$gain_xp, pa_perso=pa_perso-10 WHERE id_perso='$id_perso'";
					$mysqli->query($sql);
					
					// maj perso_as_entrainement
					$sql = "UPDATE perso_as_entrainement SET niveau_entrainement=niveau_entrainement+1, nb=0 WHERE id_perso='$id_perso'";
					$mysqli->query($sql);
					
					//mise a jour de la table evenement
					$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>',' a r&eacute;ussi son entrainement ',NULL,'',' : entrainement niveau $new_niveau',NOW(),'0')";
					$mysqli->query($sql);
					
					echo "<center>Vous venez d'effectuer votre $new_niveau ème entrainement avec succès</center><br />";
					echo "<center>Vous avez gagné $gain_xp xp</center>";
					echo "<center><a href='jouer.php'>[retour]</a></center>";
				}
				else {
					// maj pa
					$sql = "UPDATE perso SET pa_perso=pa_perso-10 WHERE id_perso='$id_perso'";
					$mysqli->query($sql);
				
					// maj perso_as_entrainement
					$sql = "UPDATE perso_as_entrainement SET nb=nb+1 WHERE id_perso='$id_perso'";
					$mysqli->query($sql);
					
					$nb_entrainement_restant = $niveau_e - $nb_e - 1;
					
					//mise a jour de la table evenement
					$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>',' a r&eacute;ussi son entrainement ',NULL,'',' : bientot au prochain niveau d\'entrainement',NOW(),'0')";
					$mysqli->query($sql);
					
					echo "<center>Vous avez réussi votre entrainement, entrainez vous encore $nb_entrainement_restant fois pour passer au niveau supérieur d'entrainement</center><br />";
					echo "<center><a href='jouer.php'>[retour]</a></center>";
				}				
			}
			else {
				// maj pa
				$sql = "UPDATE perso SET pa_perso=pa_perso-10 WHERE id_perso='$id_perso'";
				$mysqli->query($sql);
			
				//mise a jour de la table evenement
				$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>',' a rat&eacute; son entrainement ',NULL,'',' : dommage',NOW(),'0')";
				$mysqli->query($sql);
			
				echo "<center>Vous avez raté votre entrainement</center><br />";
				echo "<center>Vous êtes fatigué</center>";
				echo "<center><a href='jouer.php'>[retour]</a></center>";
			}
		}
		else {
			// on le cree
			$sql = "INSERT INTO perso_as_entrainement VALUES('$id_perso','1','0')";
			$mysqli->query($sql);
			
			// maj xp/pi/pa
			$sql = "UPDATE perso SET xp_perso=xp_perso+1, pi_perso=pi_perso+1, pa_perso=pa_perso-10 WHERE id_perso='$id_perso'";
			$mysqli->query($sql);
			
			//mise a jour de la table evenement
			$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>',' a réussi son premier entrainement ',NULL,'',' : bravo !',NOW(),'0')";
			$mysqli->query($sql);
			
			echo "<center>Vous venez d'effectuer votre premier entrainement avec succès</center><br />";
			echo "<center>Vous avez gagné 1xp</center>";
			echo "<center><a href='jouer.php'>[retour]</a></center>";
		}
	}
	else {
		echo "Pas assez de PA";
		echo "<center><a href='jouer.php'>[retour]</a></center>";
	}
}

/**
 * Fonction qui permet de donner un objet a un perso
 * @param $id_perso	: L'identifiant du personnage qui veut donner un objet
 * @param $id_cible	: L'identifiant du personnage a qui on veut donner un objet
 * @param $type_objet	: La nature de l'objet (1 => Or, 2 => Objet, 3 => Arme, 4 => Armure)
 * @param $id_objet	: L'identifiant de l'objet a deposer
 * @param $quantite	: La quantite
 */
function action_don_objet($mysqli, $id_perso, $id_cible, $type_objet, $id_objet, $quantite){
	
	// On verifie que l'id du perso est correct
	$verif_idPerso = preg_match("#^[0-9]*[0-9]$#i","$id_perso");
	
	if($verif_idPerso && $id_perso != "" && $id_perso != null){
		
		// On verifie que l'id de la cible est correct
		$verif_idCible = preg_match("#^[0-9]*[0-9]$#i","$id_cible");
		
		if($verif_idCible && $id_cible != "" && $id_cible != null){
			
			// On verifie que l'id de l'objet est correct
			$verif_idObjet = preg_match("#^[0-9]*[0-9]$#i","$id_objet");
			
			if(($verif_idObjet || $id_objet=='-1') && $id_objet != "" && $id_objet != null){
					
				// On verifie que la cible est bien au CaC avec le perso
				// Recuperation coordonnees perso
				$sql_p = "SELECT x_perso, y_perso FROM perso WHERE id_perso='$id_perso'";
				$res_p = $mysqli->query($sql_p);
				$t_p = $res_p->fetch_assoc();
				$x_perso = $t_p['x_perso'];
				$y_perso = $t_p['y_perso'];
					
				$sql_v = "SELECT idPerso_carte FROM carte WHERE idPerso_carte='$id_cible' AND occupee_carte='1' AND x_carte<=$x_perso+1 AND x_carte>=$x_perso-1 AND y_carte<=$y_perso+1 AND y_carte>=$y_perso-1";
				$res_v = $mysqli->query($sql_v);
				$verif_cac = $res_v->num_rows;
				$t_v = $res_v->fetch_assoc();
					
				$verif_cac_idCible = $t_v['idPerso_carte'];
				
				if($verif_cac == 1 && $verif_cac_idCible == $id_cible){
					// On verifie que le perso possede bien l'objet qu'il souhaite donner
					// Si c'est de l'or : on verifie qu'il possede bien la bonne quantite
					if($type_objet == 1){
							
						$sql_vo = "SELECT or_perso FROM perso WHERE id_perso='$id_perso'";
						$res_vo = $mysqli->query($sql_vo);
						$t_vo = $res_vo->fetch_assoc();
							
						$or_perso = $t_vo['or_perso'];
						
						if($or_perso >= $quantite){
								
							// On met a jour l'or du perso
							$sql_u = "UPDATE perso SET or_perso=or_perso-$quantite WHERE id_perso='$id_perso'";
							$mysqli->query($sql_u);
								
							// On met a jour l'or de la cible
							$sql_u2 = "UPDATE perso SET or_perso=or_perso+$quantite WHERE id_perso='$id_cible'";
							$mysqli->query($sql_u2);
								
							// Recuperation infos cible
							$sql = "SELECT nom_perso, clan FROM perso WHERE id_perso='$id_cible'";
							$res = $mysqli->query($sql);
							$t = $res->fetch_assoc();
								
							$nom_cible = $t['nom_perso'];
							$clan_cible = $t['clan'];
							$couleur_clan_cible = couleur_clan($clan_cible);
								
							echo "Vous avez donné <b>$quantite or</b> à <font color='$couleur_clan_cible'><b>$nom_cible</b></font>";
							echo "<center><a href='jouer.php'>[ retour ]</a></center>";
						}
						else {
							echo "<font color='red'>Vous ne possédez pas assez d'or.</font>";
							echo "<center><a href='jouer.php'>[ retour ]</a></center>";
						}
					}
						
					// Si c'est un objet ou une arme ou une armure : on verifie qu'il le/la possede
					// Objet
					if($type_objet == 2){
							
						$sql_vo = "SELECT count(*) as q_obj FROM perso_as_objet WHERE id_perso='$id_perso' AND id_objet='$id_objet'";
						$res_vo = $mysqli->query($sql_vo);
						$t_vo = $res_vo->fetch_assoc();
							
						$q_obj = $t_vo['q_obj'];
						if($q_obj >= $quantite){
								
							// On supprime l'objet de l'inventaire du perso
							$sql_d = "DELETE FROM perso_as_objet WHERE id_perso='$id_perso' AND id_objet='$id_objet' LIMIT 1";
							$mysqli->query($sql_d);
								
							// Recuperation des infos de l'objet
							$sql = "SELECT poids_objet, nom_objet FROM objet WHERE id_objet='$id_objet'";
							$res = $mysqli->query($sql);
							$t = $res->fetch_assoc();
								
							$poids_objet = $t['poids_objet'];
							$nom_objet = $t['nom_objet'];
																
							// On met a jour le poids du perso
							$sql_u = "UPDATE perso SET charge_perso=charge_perso-$poids_objet WHERE id_perso='$id_perso'";
							$mysqli->query($sql_u);
								
							// On ajoute l'objet dans l'inventaire de la cible
							$sql_i = "INSERT INTO perso_as_objet VALUES('$id_cible','$id_objet')";
							$mysqli->query($sql_i);
								
							// On met a jour le poids de la cible
							$sql_u2 = "UPDATE perso SET charge_perso=charge_perso+$poids_objet WHERE id_perso='$id_cible'";
							$mysqli->query($sql_u2);
								
							// Recuperation des informations de la cible
							$sql_c = "SELECT nom_perso, clan FROM perso WHERE id_perso='$id_cible'";
							$res_c = $mysqli->query($sql_c);
							$t_c = $res_c->fetch_assoc();
								
							$nom_cible = $t_c['nom_perso'];
							$clan_cible = $t_c['clan'];
							$couleur_clan_cible = couleur_clan($clan_cible);
								
							echo "Vous avez donné <b>$nom_objet</b> à <font color='$couleur_clan_cible'><b>$nom_cible</b></font>";
							echo "<center><a href='jouer.php'>[ retour ]</a></center>";
						}
						else {
							echo "<font color='red'>Vous ne possédez pas l'objet que vous souhaitiez donner.</font>";
							echo "<center><a href='jouer.php'>[ retour ]</a></center>";
						}
					}
							
					// Arme
					if($type_objet == 3){
							
						$sql_vo = "SELECT count(*) as q_arme FROM perso_as_arme WHERE id_perso='$id_perso' AND id_arme='$id_objet'";
						$res_vo = $mysqli->query($sql_vo);
						$t_vo = $res_vo->fetch_assoc();
							
						$q_arme = $t_vo['q_arme'];
							
						if($q_arme >= $quantite){
								
							// On supprime l'arme de l'inventaire du perso
							$sql_d = "DELETE FROM perso_as_arme WHERE id_perso='$id_perso' AND id_arme='$id_objet'";
							$mysqli->query($sql_d);
								
							// recuperation des infos de l'arme
							$sql = "SELECT nom_arme, poids_arme FROM arme WHERE id_arme='$id_objet'";
							$res = $mysqli->query($sql);
							$t = $res->fetch_assoc();
								
							$nom_arme = $t['nom_arme'];
							$poids_arme = $t['poids_arme'];
								
							// On met a jour le poids du perso
							$sql_u = "UPDATE perso SET charge_perso=charge_perso-$poids_arme WHERE id_perso='$id_perso'";
							$mysqli->query($sql_u);
								
							// On ajoute l'arme a l'inventaire de la cible
							$sql_i = "INSERT INTO perso_as_arme VALUES('$id_cible','$id_objet','0')";
							$mysqli->query($sql_i);
								
							// On met a jour le poids de la cible
							$sql_u = "UPDATE perso SET charge_perso=charge_perso+$poids_arme WHERE id_perso='$id_cible'";
							$mysqli->query($sql_u);
								
							// Recuperation des informations de la cible
							$sql_c = "SELECT nom_perso, clan FROM perso WHERE id_perso='$id_cible'";
							$res_c = $mysqli->query($sql_c);
							$t_c = $res_c->fetch_assoc();
								
							$nom_cible = $t_c['nom_perso'];
							$clan_cible = $t_c['clan'];
							$couleur_clan_cible = couleur_clan($clan_cible);
								
							echo "Vous avez donné <b>$nom_arme</b> à <font color='$couleur_clan_cible'><b>$nom_cible</b></font>";
								
							echo "<center><a href='jouer.php'>[ retour ]</a></center>";
						}
						else {
							echo "<font color='red'>Vous ne possédez pas l'arme que vous souhaitiez donner.</font>";
							echo "<center><a href='jouer.php'>[ retour ]</a></center>";
						}
					}
							
					// Armure
					if($type_objet == 4){
							
						$sql_vo = "SELECT count(*) as q_armure FROM perso_as_armure WHERE id_perso='$id_perso' AND id_armure='$id_objet'";
						$res_vo = $mysqli->query($sql_vo);
						$t_vo = $res_vo->fetch_assoc();
							
						$q_armure = $t_vo['q_armure'];
							
						if($q_armure >= $quantite){
								
							// On supprime l'armure de l'inventaire du perso
							$sql_d = "DELETE FROM perso_as_armure WHERE id_perso='$id_perso' AND id_armure='$id_objet'";
							$mysqli->query($sql_d);
								
							// recuperation des infos de l'armure
							$sql = "SELECT nom_armure, poids_armure, corps_armure FROM armure WHERE id_armure='$id_objet'";
							$res = $mysqli->query($sql);
							$t = $res->fetch_assoc();
								
							$nom_armure = $t['nom_armure'];
							$poids_armure = $t['poids_armure'];
							$corps_armure = $t['corps_armure'];
								
							// On met a jour le poids du perso
							$sql_u = "UPDATE perso SET charge_perso=charge_perso-$poids_armure WHERE id_perso='$id_perso'";
							$mysqli->query($sql_u);
							
							// On ajoute l'armure a l'inventaire de la cible
							$sql_i = "INSERT INTO perso_as_armure VALUES('$id_cible','$id_objet','0')";
							$mysqli->query($sql_i);
								
							// On met a jour le poids de la cible
							$sql_u = "UPDATE perso SET charge_perso=charge_perso+$poids_armure WHERE id_perso='$id_cible'";
							$mysqli->query($sql_u);
								
							// Recuperation des informations de la cible
							$sql_c = "SELECT nom_perso, clan FROM perso WHERE id_perso='$id_cible'";
							$res_c = $mysqli->query($sql_c);
							$t_c = $res_c->fetch_assoc();
								
							$nom_cible = $t_c['nom_perso'];
							$clan_cible = $t_c['clan'];
							$couleur_clan_cible = couleur_clan($clan_cible);
								
							echo "Vous avez donné <b>$nom_armure</b> à <font color='$couleur_clan_cible'><b>$nom_cible</b></font>";
							echo "<center><a href='jouer.php'>[ retour ]</a></center>";
						}
						else {
							echo "<font color='red'>Vous ne possédez pas l'armure que vous souhaitiez donner.</font>";
							echo "<center><a href='jouer.php'>[ retour ]</a></center>";
						}
					}
				}
				else {
					echo "<font color='red'>La cible n'est pas au Corps à corps.</font>";
					echo "<center><a href='jouer.php'>[ retour ]</a></center>";
				}
			}
			else {
				echo "<font color='red'>L'objet choisi n'est pas correct, si le problème persiste, veuillez contacter l'administrateur.</font><br/>";
				echo "<center><a href='jouer.php'>[ retour ]</a></center>";
			}
		}
		else {
			echo "<font color='red'>La cible n'est pas correcte, si le problème persiste, veuillez contacter l'administrateur.</font><br/>";
			echo "<center><a href='jouer.php'>[ retour ]</a></center>";
		}
	}
	else {
		echo "<font color='red'>Votre identifiant est mal renseigné, si le problème persiste, veuillez contacter l'administrateur.</font><br/>";
		echo "<center><a href='jouer.php'>[ retour ]</a></center>";
	}
}
 

/**
  * Fonction qui permet a un perso de deposer un objet
  * @param $id_perso	: L'identifiant du personnage qui veut deposer un objet
  * @param $type_objet	: La nature de l'objet (1 => Or, 2 => Objet, 3 => Arme, 4 => Armure)
  * @param $id_objet	: L'identifiant de l'objet a deposer
  * @return Void
  */
function action_deposerObjet($mysqli, $id_perso, $type_objet, $id_objet){

	// Verification que le perso possede bien cet objet
	// Objet
	if($type_objet == 2){
		$sql = "SELECT perso_as_objet.id_objet, poids_objet FROM perso_as_objet, objet WHERE id_perso='$id_perso' 
				AND perso_as_objet.id_objet='$id_objet'
				AND perso_as_objet.id_objet = objet.id_objet";
		$res = $mysqli->query($sql);
		$nb = $res->num_rows;
		$t = $res->fetch_assoc();
		
		$poid_objet = $t["poids_objet"];
	}
	
	// Arme
	if($type_objet == 3){
		$sql = "SELECT perso_as_arme.id_arme, poids_arme FROM perso_as_arme, arme WHERE id_perso='$id_perso' 
				AND perso_as_arme.id_arme='$id_objet' AND est_portee='0'
				AND perso_as_arme.id_arme = arme.id_arme";
		$res = $mysqli->query($sql);
		$nb = $res->num_rows;
		$t = $res->fetch_assoc();
		
		$poid_objet = $t["poids_arme"];
	}
	
	// Armure
	if($type_objet == 4){
		$sql = "SELECT perso_as_armure.id_armure, poids_armure FROM perso_as_armure, armure WHERE id_perso='$id_perso' 
				AND perso_as_armure.id_armure='$id_objet' AND est_portee='0'
				AND perso_as_armure.id_armure = armure.id_armure";
		$res = $mysqli->query($sql);
		$nb = $res->num_row;
		$t = $res->fetch_assoc();
		
		$poid_objet = $t["poids_armure"];
	}
	
	if($nb){
		
		$coutPa = 1;
		
		// verification que le perso a assez de PA et recuperation de ses coordonnees
		$sql = "SELECT pa_perso, x_perso, y_perso FROM perso WHERE id_perso='$id_perso'";
		$res = $mysqli->query($sql);
		$t = $res->fetch_assoc();
		
		$x_perso = $t["x_perso"];
		$y_perso = $t["y_perso"];
		$pa_perso = $t["pa_perso"];
		
		if($pa_perso >= $coutPa){
			// On depose l'objet
			
			if($type_objet == 2){ // Objet
				// Suppression de l'inventaire du perso
				$sql = "DELETE FROM perso_as_objet WHERE id_perso='$id_perso' AND id_objet='$id_objet' LIMIT 1";
				$mysqli->query($sql);
			}
			if($type_objet == 3){ // Arme
				// Suppression de l'inventaire du perso
				$sql = "DELETE FROM perso_as_arme WHERE id_perso='$id_perso' AND id_arme='$id_objet' LIMIT 1";
				$mysqli->query($sql);
			}
			if($type_objet == 4){ // Armure
				// Suppression de l'inventaire du perso
				$sql = "DELETE FROM perso_as_armure WHERE id_perso='$id_perso' AND id_armure='$id_objet' LIMIT 1";
				$mysqli->query($sql);
			}
			
			// On met a jour le poid et les pa du perso
			$sql = "UPDATE perso SET charge_perso = charge_perso - $poid_objet, pa_perso=pa_perso-1 WHERE id_perso='$id_perso'";
			$mysqli->query($sql);
			
			
			// Verification si l'objet existe deja sur cette case
			$sql = "SELECT nb_objet FROM objet_in_carte, perso WHERE id_perso='$id_perso' 
					AND objet_in_carte.x_carte = perso.x_perso AND objet_in_carte.y_carte = perso.y_perso
					AND type_objet = '$type_objet' AND id_objet = '$id_objet'";
			$res = $mysqli->query($sql);
			$to = $res->fetch_assoc();
			
			$nb_o = $to["nb_objet"];
			
			if($nb_o){
				// On met a jour le nombre
				$sql = "UPDATE objet_in_carte SET nb_objet = nb_objet + 1 
						WHERE type_objet='$type_objet' AND id_objet='$id_objet'
						AND x_carte='$x_perso' AND y_carte='$y_perso'";
				$mysqli->query($sql);
			}
			else {
				// Insertion dans la table objet_in_carte : On cree le premier enregistrement
				$sql = "INSERT INTO objet_in_carte (type_objet, id_objet, nb_objet, x_carte, y_carte) VALUES ('$type_objet','$id_objet','1','$x_perso','$y_perso')";
				$mysqli->query($sql);
			}
			
			echo "<center>Vous venez de déposer un objet à terre</center><br />";
			echo "<center><a href='jouer.php'>[retour]</a></center>";
		}
	}
	else {
		// Triche ?
		echo "<center><a href='jouer.php'>[retour]</a></center>";
	}
}

/**
  * Fonction qui permet de ramasser un objet à terre
  * @param $id_perso	: L'identifiant du perso quiveut ramasser un objet
  * @param $type_objet	: Le type de l'objet (2 => Objet, 3 => Arme, 4 => Armure)
  * @param $id_objet	: L'identifiant de l'objet qu'on veut ramasser
  * @return Void
  */
function action_ramasserObjet($mysqli, $id_perso, $type_objet, $id_objet){
	
	// Verification que l'objet est bien toujours sur la case du perso
	$sql = "SELECT nb_objet FROM objet_in_carte,perso WHERE id_perso='$id_perso' 
			AND x_carte=x_perso AND y_carte=y_perso
			AND type_objet='$type_objet' AND id_objet='$id_objet'";
	$res = $mysqli->query($sql);
	$to = $res->fetch_assoc();
	$verif = $res->num_rows;
	
	if($verif){
		
		$coutPa = 1;
		
		// verification que le perso a assez de PA et recuperation de ses coordonnees
		$sql = "SELECT pa_perso, x_perso, y_perso FROM perso WHERE id_perso='$id_perso'";
		$res = $mysqli->query($sql);
		$t = $res->fetch_assoc();
		
		$x_perso = $t["x_perso"];
		$y_perso = $t["y_perso"];
		$pa_perso = $t["pa_perso"];
		
		if($pa_perso >= $coutPa){
			// On ramasse l'objet
			
			// Verification si l'objet est a plus de 1 exemplaire sur la case => maj du nombre plutot que suppression			
			$nb_o = $to["nb_objet"];
			if($nb_o > 1){
				
				// On met a jour le nombre d'objet sur la case
				$sql = "UPDATE objet_in_carte SET nb_objet=nb_objet-1 WHERE type_objet='$type_objet' 
						AND id_objet='$id_objet' AND x_carte='$x_perso' AND y_carte='$y_perso'";
				$mysqli->query($sql);
			}
			else {
				// Il n'est qu'en 1 exemplaire sur la case => on supprime la ligne dans la bdd
				$sql = "DELETE FROM objet_in_carte WHERE type_objet='$type_objet' 
						AND id_objet='$id_objet' AND x_carte='$x_perso' AND y_carte='$y_perso'";
				$mysqli->query($sql);
			}
			
			// Recuperation du poid et des infos de l'objet et insertion de l'objet dans l'inventaire du perso
			// Objet
			if($type_objet == 2){
				
				// recuperation des infos de l'objet
				$sql = "SELECT poids_objet FROM objet WHERE id_objet='$id_objet'";
				$res = $mysqli->query($sql);
				$t = $res->fetch_assoc();
				
				$poid_objet = $t["poids_objet"];
				
				// Insertion de l'objet dans l'inventaire du perso
				$sql = "INSERT INTO perso_as_objet VALUES ('$id_perso','$id_objet')";
				$mysqli->query($sql);
			}
			
			// Arme
			if($type_objet == 3){
				// recuperation des infos de l'objet
				$sql = "SELECT poids_arme FROM arme WHERE id_arme='$id_objet'";
				$res = $mysqli->query($sql);
				$t = $res->fetch_assoc();
				
				$poid_objet = $t["poids_arme"];
				
				// Insertion de l'objet dans l'inventaire du perso
				$sql = "INSERT INTO perso_as_arme VALUES ('$id_perso','$id_objet','0')";
				$mysqli->query($sql);
			}
			
			// Armure
			if($type_objet == 4){
				
				$sql = "SELECT poids_armure, corps_armure FROM armure WHERE id_armure='$id_objet'";
				$res = $mysqli->query($sql);
				$t = $res->fetch_assoc();
				
				$poid_objet = $t["poids_armure"];
				
				// Recuperation de la position de l'armure sur le corps
				$corps_armure = $t["corps_armure"];
				
				// Insertion de l'objet dans l'inventaire du perso
				$sql = "INSERT INTO perso_as_armure VALUES ('$id_perso','$id_objet','0')";
				$mysqli->query($sql);
			}
			
			// On met a jour le poid et les pa du perso
			$sql = "UPDATE perso SET charge_perso = charge_perso + $poid_objet, pa_perso=pa_perso-1 WHERE id_perso='$id_perso'";
			$mysqli->query($sql);
			
			echo "<center>Vous venez de ramasser un objet à terre</center><br />";
			echo "<center><a href='jouer.php'>[retour]</a></center>";
		}
	}
	else {
		echo "<br />L'objet que vous souhaitiez ramasser ne se trouve plus sur cette case<br />";
		echo "<center><a href='jouer.php'>[retour]</a></center>";
	}
}

/**
  * Fonction qui verifie si un perso est un marchand et retourne le nombre de points de marchandage
  * @param $id_perso	: L'identifiant du personnage
  * @return Int		: Le nombre de points dans la competence de marchandage
  */
function est_marchand($mysqli, $id_perso){
	
	$sql = "SELECT nb_points FROM perso_as_competence WHERE id_perso='$id_perso' AND id_competence='50'";
	$res = $mysqli->query($sql);
	$t = $res->fetch_assoc();
	
	return $t['nb_points'];
}

/** 
  * Fonction qui verifie si le perso est un ami des animaux et retourne le nombre de points
  * @param $id_perso	: L'identifiant du personnage
  * @return Int		: le nombre de points dans la competence d'amitie avec les animaux
  */
function est_ami_animaux($mysqli, $id_perso){
	
	$sql = "SELECT nb_points FROM perso_as_competence WHERE id_perso='$id_perso' AND id_competence='38'";
	$res = $mysqli->query($sql);
	$t = $res->fetch_assoc();
	
	return $t['nb_points'];
}

/**
  * Fonction qui permet de recuperer le nombre de morceaux de bois que le personnage possede
  * @param $id_perso	: L'identifiant du personnage
  * @return Int		: Le nombre de morceaux de bois que le personnage possede dans son sac
  */
function nb_bois_perso($mysqli, $id_perso){
	
	$sql = "SELECT count(*) as nb_bois FROM perso_as_objet WHERE id_perso='$id_perso' AND id_objet='7'";
	$res = $mysqli->query($sql);
	$t_nb_bois = $res->fetch_assoc();
	return $t_nb_bois['nb_bois'];
	
}

/**
  * Fonction qui verifie si une arme d'identifiant passe en parametre existe bien
  * @param $id_arme	: L'identifiant de l'arme
  * @return Bool
  */
function existe_arme($mysqli, $id_arme){
	$sql = "SELECT id_arme FROM arme WHERE id_arme='$id_arme'";
	$res = $mysqli->query($sql);
	$num = $res->num_rows;
	
	return $num == 1;
}

/**
  * Fonction qui verifie si une armure d'identifiant passe en parametre existe bien
  * @param $id_armure	: L'identifiant de l'armure
  * @return Bool
  */
function existe_armure($mysqli, $id_armure){
	$sql = "SELECT id_armure FROM armure WHERE id_armure='$id_armure'";
	$res = $mysqli->query($sql);
	$num = $res->num_rows;
	
	return $num == 1;
}

/**
  * Fonction qui verifie si un objet d'identifiant passe en parametre existe bien
  * @param $id_objet	: L'identifiant de l'objet
  * @return Bool
  */
function existe_objet($mysqli, $id_objet){
	$sql = "SELECT id_objet FROM objet WHERE id_objet='$id_objet'";
	$res = $mysqli->query($sql);
	$num = $res->num_rows;
	
	return $num == 1;
}

/**
  * Fonction qui permet de calculer la recuperation de malus selon l'action utilisee
  * @param $id_action	: L'identifiant de l'action
  * @return			: La reuperation de malus
  */
function calcul_recup_malus($id_action){
	if($id_action == '140'){
		$recup_malus = 1;
	}
	if($id_action == '141'){
		$recup_malus = rand(1,2);
	}
	if($id_action == '142'){
		$recup_malus = rand(1,4);
	}
	return $recup_malus;
}

/**
  * Fonction qui calcul le nombre de pv de reparation du batiment le personnage va effectuer
  * @param $id_action	: L'identifiant de l'action
  * @return int			: Le nombre de pv de reparation
  */
function calcul_pv_reparation($id_action){
	
	if($id_action == '76'){
		$pv_reparation = rand(20, 120);
	}
	
	return $pv_reparation;
}

/**
  * Fonction qui calcul le gain de pv sur une action de type reparation
  * @param $nb_points_action	: Le niveau de l'action
  * @return int			: Le gain en pv
  */
function calcul_gain_pv_reparer($nb_points_action){
	if($nb_points_action == 1 || $nb_points_action == 2){
		$gain_pv = rand(5,10);
	}
	if($nb_points_action == 3 || $nb_points_action == 4){
		$gain_pv = rand(8,15);
	}
	if($nb_points_action == 5 || $nb_points_action == 6){
		$gain_pv = rand(10,20);
	}
	return $gain_pv;
}

/**
  * Fonction qui recupere l'identifiant de competence correspondant a la construction d'un type de batiment
  * @param $id_batiment	: l'identifiant du batiment
  * @return  Int			: L'identifant de la competence associee a la construction du type de batiment passe en parametre
  */
function recup_id_competence_bat($id_batiment){
	if($id_batiment == 1){
		// barricade
		$id_competence = 22;
	}
	if($id_batiment == 2){
		// tour de visu
		$id_competence = 24;
	}
	if($id_batiment == 3){
		// tour de garde
		$id_competence = 25;
	}
	if($id_batiment == 4){
		// route
		$id_competence = 21;
	}
	if($id_batiment == 5){
		// pont
		$id_competence = 23;
	}
	if($id_batiment == 6){
		// entrepot
		$id_competence = 26;
	}
	if($id_batiment == 7){
		// hopital
		$id_competence = 27;
	}
	if($id_batiment == 8){
		// fortin
		$id_competence = 28;
	}
	if($id_batiment == 9){
		// fort
		$id_competence = 29;
	}
	if($id_batiment == 10){
		// prison
		//$id_competence = 0;
	}
	
	return $id_competence;
}

/**
  * Fonction qui calcul le pourcentage de reussite ou autre pourcentage d'une action donnee
  * @param $id_action	: L'identifiant de l'action
  * @return Int		: le pourcentage de reussite ou autre pourcentage recupere de l'action
  */
function calcul_pourcentage_action($id_action){
	
	//recuperation du pourcentage
	if(	/*premier soin*/ $id_action == '11' || $id_action == '12' || $id_action == '13'){
		$pourcent = 10;
	}
	
	if(	/*Upgrade batiment*/ $id_action == '80' || $id_action == '83'
		/* Soins avances */ 	|| $id_action == '14' || $id_action == '15' || $id_action == '16'){
		$pourcent = 25;
	}
	
	if(	/*Upgrade batiment*/ $id_action == '81' || $id_action == '84'
		/* Chirurgie */ 		|| $id_action == '22' || $id_action == '23' || $id_action == '24'
		/*Reparer Arme*/ 	|| $id_action == '115' 
		/*Reparer Armure*/ 	|| $id_action == '124'){
		$pourcent = 50;
	}
	
	if(	/*Reparer Arme*/	$id_action == '116' || $id_action == '117' 
		/*Reparer Armure*/	|| $id_action == '125' || $id_action == '126'){
		$pourcent = 65;
	}
	
	if(	/*Upgrade batiment*/ $id_action == '82' || $id_action == '85'
		/*Reparer Arme*/	|| $id_action == '118' || $id_action == '119'
		/*Reparer Armure*/	|| $id_action == '127' || $id_action == '128'){
		$pourcent = 75;
	}
	
	if(	/*Reparer Arme*/	$id_action == '120'
		/*Reparer Armure*/	|| $id_action == '129'){
		$pourcent = 95;
	}
	
	if(	/*chirurgie de guerre*/ $id_action == '25' || $id_action == '26' || $id_action == '27'){
		$pourcent = 100;
	}
	
	return $pourcent;
}

/**
 * Fonction permettant d'effectuer une charge vers le haut
 * y + 1
 */
function charge_haut($mysqli, $id_perso, $nom_perso, $x_perso, $y_perso, $pa_perso, $pv_perso, $xp_perso, $image_perso, $clan, $grade_perso) {
		
	$couleur_clan_perso = couleur_clan($clan);
	
	for ($i = 1; $i <= 5; $i++) {
					
		$sql = "SELECT x_carte, y_carte, fond_carte, occupee_carte, idPerso_carte FROM carte WHERE x_carte = $x_perso AND y_carte = $y_perso + $i";
		$res = $mysqli->query($sql);
		$t_charge = $res->fetch_assoc();
		
		$x_carte 		= $t_charge['x_carte'];
		$y_carte		= $t_charge['y_carte'];
		$fond_carte 	= $t_charge['fond_carte'];
		$occupee_carte	= $t_charge['occupee_carte'];
		$idPerso_carte	= $t_charge['idPerso_carte'];
		
		if ($occupee_carte || $fond_carte != '1.gif') {
			
			// Charge terminée
			
			if ($i <= 3) {
				
				// Mise à jour position perso sur carte
				$nb_deplacements = $i - 1;
				$x_perso_final = $x_perso;
				$y_perso_final = $y_perso + $nb_deplacements;
				
				// MAJ perso sur carte
				$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_perso' AND y_carte='$y_perso'";
				$mysqli->query($sql);
				
				$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte='$id_perso', image_carte='$image_perso' WHERE x_carte = $x_perso_final AND y_carte = $y_perso_final";
				$mysqli->query($sql);
					
				// Mise à jour du perso
				$sql = "UPDATE perso SET pm_perso = pm_perso - $nb_deplacements, x_perso = $x_perso_final, y_perso = $y_perso_final WHERE id_perso='$id_perso'";
				$mysqli->query($sql);
				
				// Charge incomplete => pas d'attaques
				$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>',' a rencontré un obstacle ne lui permettant pas de terminer sa charge ',NULL,'','',NOW(),'0')";
				$mysqli->query($sql);
				
				break;
				
			} else {
				
				// Mise a jour carte 
				$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_perso' AND y_carte='$y_perso'";
				$mysqli->query($sql);
					
				// Charge complète mais cible pas sur plaine => pas d'attaques
				if ($fond_carte != '1.gif') {
					
					// Mise à jour position perso sur carte
					$nb_deplacements = $i - 1;
					$x_perso_final = $x_perso;
					$y_perso_final = $y_perso + $nb_deplacements;
					$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte='$id_perso', image_carte='$image_perso' WHERE x_carte = $x_perso_final AND y_carte = $y_perso_final";
					$mysqli->query($sql);
					
					// Mise à jour du perso
					$sql = "UPDATE perso SET pm_perso = pm_perso - $nb_deplacements, x_perso = $x_perso_final, y_perso = $y_perso_final WHERE id_perso='$id_perso'";
					$mysqli->query($sql);
					
					// cible pas sur plaine => charge ratée
					$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>',' a raté sa charge, le terrain étant impraticable! ',NULL,'','',NOW(),'0')";
					$mysqli->query($sql);
					
				} else if ($idPerso_carte >= 50000 && $idPerso_carte < 200000) {
					
					// Charge complète mais cible batiment => pas d'attaque et le perso perd 40PV
					
					// Mise à jour position perso sur carte
					$nb_deplacements = $i - 1;
					$x_perso_final = $x_perso;
					$y_perso_final = $y_perso + $nb_deplacements;
					$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte='$id_perso', image_carte='$image_perso' WHERE x_carte = $x_perso_final AND y_carte = $y_perso_final";
					$mysqli->query($sql);
					
					// Mise à jour du perso
					$sql = "UPDATE perso SET pv_perso = pv_perso - 40, pm_perso = pm_perso - $nb_deplacements, x_perso = $x_perso_final, y_perso = $y_perso_final WHERE id_perso='$id_perso'";
					$mysqli->query($sql);
					
					if ($pv_perso <= 40) {
						// Perso rapatrié
						
						$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>',' s\'est écrasé contre un batiment suite a sa charge et a perdu connaissance ! ',NULL,'','',NOW(),'0')";
						$mysqli->query($sql);
						
					} else {
					
						$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>',' s\'est écrasé contre un batiment suite a sa charge ! ',NULL,'','',NOW(),'0')";
						$mysqli->query($sql);
						
					}
					
				} else {
					// Charge compléte et réussi sur un autre joueur ou PNJ sur plaine
					// On utilise tous les PA dispo pour faire les attaques
					// +30 degats première attaque, +20 seconde, etc...
					
					// Mise à jour position perso sur carte
					$nb_deplacements = $i - 1;
					$x_perso_final = $x_perso;
					$y_perso_final = $y_perso + $nb_deplacements;
					$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte='$id_perso', image_carte='$image_perso' WHERE x_carte = $x_perso_final AND y_carte = $y_perso_final";
					$mysqli->query($sql);
					
					// Mise à jour du perso
					$sql = "UPDATE perso SET pm_perso = pm_perso - $nb_deplacements, x_perso = $x_perso_final, y_perso = $y_perso_final WHERE id_perso='$id_perso'";
					$mysqli->query($sql);
					
					// Attaques arme CaC
					// Recuperation caracs de l'arme CaC equipé
					$sql = "SELECT nom_arme, coutPa_arme, degatMin_arme, valeur_des_arme, precision_arme FROM arme, perso, perso_as_arme 
							WHERE perso_as_arme.id_perso = perso.id_perso 
							AND perso_as_arme.id_arme = arme.id_arme
							AND perso_as_arme.est_portee = '1' 
							AND arme.porteeMax_arme = '1'
							AND perso.id_perso = '$id_perso'";
					$res = $mysqli->query($sql);
					$t_arme = $res->fetch_assoc();
					
					$nom_arme 			= $t_arme['nom_arme'];
					$degats_arme 		= $t_arme['degatMin_arme'];
					$valeur_des_arme	= $t_arme['valeur_des_arme'];
					$precision_arme 	= $t_arme['precision_arme'];
					$coutPa_arme		= $t_arme['coutPa_arme'];
					
					if ($idPerso_carte >= 200000) {
						// PNJ
						
						// Récupération des infos de la cible
						$sql = "SELECT nom_pnj, pv_i, bonus_i FROM perso WHERE id_perso = '$idPerso_carte'";
						$res = $mysqli->query($sql);
						$t_cible = $res->fetch_assoc();
						
						$nom_cible 		= $t_cible['nom_pnj'];
						$pv_cible		= $t_cible['pv_i'];
						$bonus_cible	= $t_cible['bonus_i'];
						$protec_cible	= 0;
						
						$gain_pc = 0;
						
					} else {
					
						// Récupération des infos de la cible
						$sql = "SELECT nom_perso, x_perso, y_perso, pv_perso, xp_perso, or_perso, protec_perso, bonus_perso, idJoueur_perso, clan, id_grade FROM perso, perso_as_grade 
								WHERE perso_as_grade.id_perso = perso.id_perso
								AND perso.id_perso = '$idPerso_carte'";
						$res = $mysqli->query($sql);
						$t_cible = $res->fetch_assoc();
						
						$nom_cible 			= $t_cible['nom_perso'];
						$x_cible			= $t_cible['x_perso'];
						$y_cible			= $t_cible['y_perso'];
						$pv_cible			= $t_cible['pv_perso'];
						$xp_cible 			= $t_cible['xp_perso'];
						$or_cible 			= $t_cible['or_perso'];
						$bonus_cible		= $t_cible['bonus_perso'];
						$protec_cible		= $t_cible['protec_perso'];
						$grade_cible		= $t_cible['id_grade'];
						$id_joueur_cible 	= $t_cible['idJoueur_perso'];
						$clan_cible 		= $t_cible['clan'];
						
						// Récupération de la couleur associée au clan de la cible
						$couleur_clan_cible = couleur_clan($clan_cible);
						
						// Vérifie si le joueur attaqué a coché l'envoi de mail
						$mail_info_joueur = verif_coche_mail($mysqli, $id_joueur_cible);
						
						if($mail_info_joueur){
							// Envoi du mail
							mail_attaque($mysqli, $nom_perso, $idPerso_carte);
						}
						
						// Si perso ou cible est une infanterie 
						// ou si grade perso >= grade cible - 1
						if ($grade_perso <= $grade_cible + 1 
								|| $grade_perso == 1 || $grade_perso == 101 || $grade_perso == 102 
								|| $grade_cible == 1 || $grade_cible == 101 || $grade_cible == 102) {
							
							$gain_pc = 2;
						} else {
							$gain_pc = 0;
						}
					
					}
					
					$nb_attaque = 0;
					$cible_alive = true;
					$cumul_degats = 0;
					
					// On attaque tant qu'il reste des PA
					while ($pa_perso >= $coutPa_arme && $cible_alive) {
						
						// MAJ des pa du perso
						$pa_perso = $pa_perso - $coutPa_arme;
						
						$sql = "UPDATE perso SET pa_perso = $pa_perso WHERE id_perso='$id_perso'";
						$mysqli->query($sql);
						
						// Est ce que le perso touche sa cible ?
						$touche = mt_rand(0, 100);
						$precision_final = $precision_arme + $bonus_cible;
						
						if ($touche <= $precision_final) {
							// Le perso touche sa cible
							
							// calcul des dégats
							$bonus_degats_charge = 30 - $nb_attaque*10;
							$degats = mt_rand($degats_arme, $degats_arme * $valeur_des_arme) - $protec_cible + $bonus_degats_charge;
							
							if ($touche <= 2) {
								// Coup critique ! Dégats et Gains PC X 2
								$degats = $degats * 2;
								$gain_pc = $gain_pc * 2;
							}
							
							$cumul_degats = $cumul_degats + $degats;
							
							// calcul gain experience
							if ($idPerso_carte >= 200000) {
								$gain_experience = mt_rand(1,4);
							} else {
							
								$calcul_des_xp = ($xp_cible - $xp_perso) / 10;
								if ($calcul_des_xp < 0) {
									$valeur_des_xp = 0;
								} else {
									$valeur_des_xp = mt_rand(1, $calcul_des_xp);
								}
								$gain_experience = ($degats / 2) + $valeur_des_xp;
								
								if ($gain_experience > 15) {
									$gain_experience = 15;
								}
							}
							
							// Maj cible
							// Ajout d'un malus de 2 au défenseur
							$sql = "UPDATE perso SET bonus_perso = bonus_perso - 2, pv_perso = pv_perso - $degats WHERE id_perso='$idPerso_carte'";
							$mysqli->query($sql);
							
							// evenement attaque
							$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>','a chargé ','$idPerso_carte','<font color=$couleur_clan_cible>$nom_cible</font>',': $degats degats',NOW(),'0')";
							$mysqli->query($sql);
							
							// Verification si cible morte							
							if ($pv_cible - $cumul_degats <= 0) {
								$cible_alive = false;
								
								// Perte or 
								$calcul_perte_or = floor(($or_cible * 30) / 100);
								$sql = "UPDATE perso SET or_perso = or_perso - $calcul_perte_or WHERE id_perso='$idPerso_carte'";
								
								// on l'efface de la carte
								$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_cible' AND y_carte='$y_cible'";
								$mysqli->query($sql);
								
								if ($calcul_perte_or > 0) {
									// TODO : On met de l'or sur la carte
									
								}
								
								// evenement perso capture
								$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>','a capturé','$idPerso_carte','<font color=$couleur_clan_cible>$nom_cible</font>','',NOW(),'0')";
								$mysqli->query($sql);
								
								// maj cv
								$sql = "INSERT INTO `cv` (IDActeur_cv, nomActeur_cv, IDCible_cv, nomCible_cv, date_cv) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>','$id_cible','<font color=$couleur_clan_cible>$nom_cible</font>',NOW())";
								$mysqli->query($sql);
								
								// maj stats du perso
								$sql = "UPDATE perso SET nb_kill=nb_kill+1 WHERE id_perso=$id_perso";
								$mysqli->query($sql);
								
								// maj stats de la cible
								$sql = "UPDATE perso SET nb_mort=nb_mort+1 WHERE id_perso='$idPerso_carte'";
								$mysqli->query($sql);
								
								// maj stats camp
								if($clan_cible != $clan_cible){
									$sql = "UPDATE stats_camp_kill SET nb_kill=nb_kill+1 WHERE id_camp=$clan_perso";
									$mysqli->query($sql);
								}
							}
							
							// MAJ xp/pi
							$sql = "UPDATE perso SET xp_perso = xp_perso + $gain_experience, pi_perso = pi_perso + $gain_experience WHERE id_perso='$id_perso'";
							$mysqli->query($sql);
							
							// recup id joueur perso
							$sql = "SELECT idJoueur_perso FROM perso WHERE id_perso='$id_perso'";
							$res = $mysqli->query($sql);
							$t_j = $res->fetch_assoc();
							
							$id_j_perso = $t_j["idJoueur_perso"];
							
							// mise à jour des PC du chef
							$sql = "SELECT perso.id_perso, pc_perso, id_grade FROM perso, perso_as_grade WHERE perso.id_perso = perso_as_grade.id_perso AND idJoueur_perso='$id_j_perso' AND chef='1'";
							$res = $mysqli->query($sql);
							$t_chef = $res->fetch_assoc();
							
							$id_perso_chef = $t_chef["id_perso"];
							$pc_perso_chef = $t_chef["pc_perso"];
							$id_grade_chef = $t_chef["id_grade"];
							
							$sql = "UPDATE perso SET pc_perso = pc_perso + $gain_pc WHERE id_perso='$id_perso_chef'";
							$mysqli->query($sql);
							
							$pc_perso_chef_final = $pc_perso_chef + $gain_pc;
							
							// Verification passage de grade 
							$sql = "SELECT id_grade, nom_grade FROM grades WHERE pc_grade <= $pc_perso_chef_final AND pc_grade != 0 ORDER BY id_grade DESC LIMIT 1";
							$res = $mysqli->query($sql);
							$t_grade = $res->fetch_assoc();
							
							$id_grade_final 	= $t_grade["id_grade"];
							$nom_grade_final	= $t_grade["nom_grade"];
							
							if ($id_grade_chef < $id_grade_final) {
								
								// Passage de grade								
								$sql = "UPDATE perso_as_grade SET id_grade='$id_grade_final' WHERE id_perso='$id_perso_chef'";
								$mysqli->query($sql);
								
								echo "<br /><b>Votre chef de bataillon est passé au grade de $nom_grade_final</b><br />";
								
							}
							
						} else {
							// Le perso rate sa cible
							
							if ($touche >= 98) {
								// Echec critique !
								// Ajout d'un malus supplémentaire à l'attaquant
								$sql = "UPDATE perso SET bonus_perso = bonus_perso - 1 WHERE id_perso='$id_perso'";
							} else {
								// Ajout d'un malus supplémentaire à la cible
								$sql = "UPDATE perso SET bonus_perso = bonus_perso - 1 WHERE id_perso='$idPerso_carte'";
							}
							$mysqli->query($sql);
							
							// evenement
							$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($idPerso_carte,'$nom_cible','a esquivé l\'attaque de','$id_perso','<font color=$couleur_clan_perso>$nom_perso</font>','',NOW(),'0')";
							$mysqli->query($sql);
						}
						
						if ($nb_attaque < 3) {
							$nb_attaque++;
						}
					}
					
				}
				
				break;
			}
		} else {
			if ($i == 5) {
				// On n'a rencontré aucune cible / obstacle 
				// Charge terminée sans effet à part le déplacement
				
				// Mise a jour carte 
				$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_perso' AND y_carte='$y_perso'";
				$mysqli->query($sql);
				
				// Mise à jour position perso sur carte
				$x_perso_final = $x_perso;
				$y_perso_final = $y_perso + 4;
				$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte='$id_perso', image_carte='$image_perso' WHERE x_carte = $x_perso_final AND y_carte = $y_perso_final";
				$mysqli->query($sql);
				
				// Mise à jour du perso
				$sql = "UPDATE perso SET pm_perso = pm_perso - 4, x_perso = $x_perso_final, y_perso = $y_perso_final WHERE id_perso='$id_perso'";
				$mysqli->query($sql);
				
				// evenement
				$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>',' a chargé face au vent... ',NULL,'','jusqu\'en $x_perso_final / $y_perso_final',NOW(),'0')";
				$mysqli->query($sql);
			}
		}
	}
}

/**
 * Fonction de charge vers la direction haut gauche
 * x - 1 et y + 1
 */
function charge_haut_gauche($mysqli, $id_perso, $nom_perso, $x_perso, $y_perso, $pa_perso, $pv_perso, $xp_perso, $image_perso, $clan, $grade_perso) {
		
	$couleur_clan_perso = couleur_clan($clan);
	
	for ($i = 1; $i <= 5; $i++) {
					
		$sql = "SELECT x_carte, y_carte, fond_carte, occupee_carte, idPerso_carte FROM carte WHERE x_carte = $x_perso - $i AND y_carte = $y_perso + $i";
		$res = $mysqli->query($sql);
		$t_charge = $res->fetch_assoc();
		
		$x_carte 		= $t_charge['x_carte'];
		$y_carte		= $t_charge['y_carte'];
		$fond_carte 	= $t_charge['fond_carte'];
		$occupee_carte	= $t_charge['occupee_carte'];
		$idPerso_carte	= $t_charge['idPerso_carte'];
		
		if ($occupee_carte || $fond_carte != '1.gif') {
			
			// Charge terminée
			
			if ($i <= 3) {
				
				// Mise à jour position perso sur carte
				$nb_deplacements = $i - 1;
				$x_perso_final = $x_perso - $nb_deplacements;
				$y_perso_final = $y_perso + $nb_deplacements;
				
				// MAJ perso sur carte
				$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_perso' AND y_carte='$y_perso'";
				$mysqli->query($sql);
				
				$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte='$id_perso', image_carte='$image_perso' WHERE x_carte = $x_perso_final AND y_carte = $y_perso_final";
				$mysqli->query($sql);
					
				// Mise à jour du perso
				$sql = "UPDATE perso SET pm_perso = pm_perso - $nb_deplacements, x_perso = $x_perso_final, y_perso = $y_perso_final WHERE id_perso='$id_perso'";
				$mysqli->query($sql);
				
				// Charge incomplete => pas d'attaques
				$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>',' a rencontré un obstacle ne lui permettant pas de terminer sa charge ',NULL,'','',NOW(),'0')";
				$mysqli->query($sql);
				
				break;
				
			} else {
				
				// Mise a jour carte 
				$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_perso' AND y_carte='$y_perso'";
				$mysqli->query($sql);
					
				// Charge complète mais cible pas sur plaine => pas d'attaques
				if ($fond_carte != '1.gif') {
					
					// Mise à jour position perso sur carte
					$nb_deplacements = $i - 1;
					$x_perso_final = $x_perso - $nb_deplacements;
					$y_perso_final = $y_perso + $nb_deplacements;
					$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte='$id_perso', image_carte='$image_perso' WHERE x_carte = $x_perso_final AND y_carte = $y_perso_final";
					$mysqli->query($sql);
					
					// Mise à jour du perso
					$sql = "UPDATE perso SET pm_perso = pm_perso - $nb_deplacements, x_perso = $x_perso_final, y_perso = $y_perso_final WHERE id_perso='$id_perso'";
					$mysqli->query($sql);
					
					// cible pas sur plaine => charge ratée
					$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>',' a raté sa charge, le terrain étant impraticable! ',NULL,'','',NOW(),'0')";
					$mysqli->query($sql);
					
				} else if ($idPerso_carte >= 50000 && $idPerso_carte < 200000) {
					
					// Charge complète mais cible batiment => pas d'attaque et le perso perd 40PV
					
					// Mise à jour position perso sur carte
					$nb_deplacements = $i - 1;
					$x_perso_final = $x_perso - $nb_deplacements;
					$y_perso_final = $y_perso + $nb_deplacements;
					$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte='$id_perso', image_carte='$image_perso' WHERE x_carte = $x_perso_final AND y_carte = $y_perso_final";
					$mysqli->query($sql);
					
					// Mise à jour du perso
					$sql = "UPDATE perso SET pv_perso = pv_perso - 40, pm_perso = pm_perso - $nb_deplacements, x_perso = $x_perso_final, y_perso = $y_perso_final WHERE id_perso='$id_perso'";
					$mysqli->query($sql);
					
					if ($pv_perso <= 40) {
						// Perso rapatrié
						
						$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>',' s\'est écrasé contre un batiment suite a sa charge et a perdu connaissance ! ',NULL,'','',NOW(),'0')";
						$mysqli->query($sql);
						
					} else {
					
						$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>',' s\'est écrasé contre un batiment suite a sa charge ! ',NULL,'','',NOW(),'0')";
						$mysqli->query($sql);
						
					}
					
				} else {
					// Charge compléte et réussi sur un autre joueur ou PNJ sur plaine
					// On utilise tous les PA dispo pour faire les attaques
					// +30 degats première attaque, +20 seconde, etc...
					
					// Mise à jour position perso sur carte
					$nb_deplacements = $i - 1;
					$x_perso_final = $x_perso - $nb_deplacements;
					$y_perso_final = $y_perso + $nb_deplacements;
					$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte='$id_perso', image_carte='$image_perso' WHERE x_carte = $x_perso_final AND y_carte = $y_perso_final";
					$mysqli->query($sql);
					
					// Mise à jour du perso
					$sql = "UPDATE perso SET pm_perso = pm_perso - $nb_deplacements, x_perso = $x_perso_final, y_perso = $y_perso_final WHERE id_perso='$id_perso'";
					$mysqli->query($sql);
					
					// Attaques arme CaC
					// Recuperation caracs de l'arme CaC equipé
					$sql = "SELECT nom_arme, coutPa_arme, degatMin_arme, valeur_des_arme, precision_arme FROM arme, perso, perso_as_arme 
							WHERE perso_as_arme.id_perso = perso.id_perso 
							AND perso_as_arme.id_arme = arme.id_arme
							AND perso_as_arme.est_portee = '1' 
							AND arme.porteeMax_arme = '1'
							AND perso.id_perso = '$id_perso'";
					$res = $mysqli->query($sql);
					$t_arme = $res->fetch_assoc();
					
					$nom_arme 			= $t_arme['nom_arme'];
					$degats_arme 		= $t_arme['degatMin_arme'];
					$valeur_des_arme	= $t_arme['valeur_des_arme'];
					$precision_arme 	= $t_arme['precision_arme'];
					$coutPa_arme		= $t_arme['coutPa_arme'];
					
					if ($idPerso_carte >= 200000) {
						// PNJ
						
						// Récupération des infos de la cible
						$sql = "SELECT nom_pnj, pv_i, bonus_i FROM perso WHERE id_perso = '$idPerso_carte'";
						$res = $mysqli->query($sql);
						$t_cible = $res->fetch_assoc();
						
						$nom_cible 		= $t_cible['nom_pnj'];
						$pv_cible		= $t_cible['pv_i'];
						$bonus_cible	= $t_cible['bonus_i'];
						$protec_cible	= 0;
						
						$gain_pc = 0;
						
					} else {
					
						// Récupération des infos de la cible
						$sql = "SELECT nom_perso, x_perso, y_perso, pv_perso, xp_perso, or_perso, protec_perso, bonus_perso, idJoueur_perso, clan, id_grade FROM perso, perso_as_grade 
								WHERE perso_as_grade.id_perso = perso.id_perso
								AND perso.id_perso = '$idPerso_carte'";
						$res = $mysqli->query($sql);
						$t_cible = $res->fetch_assoc();
						
						$nom_cible 			= $t_cible['nom_perso'];
						$x_cible			= $t_cible['x_perso'];
						$y_cible			= $t_cible['y_perso'];
						$pv_cible			= $t_cible['pv_perso'];
						$xp_cible 			= $t_cible['xp_perso'];
						$or_cible 			= $t_cible['or_perso'];
						$bonus_cible		= $t_cible['bonus_perso'];
						$protec_cible		= $t_cible['protec_perso'];
						$grade_cible		= $t_cible['id_grade'];
						$id_joueur_cible 	= $t_cible['idJoueur_perso'];
						$clan_cible 		= $t_cible['clan'];
						
						// Récupération de la couleur associée au clan de la cible
						$couleur_clan_cible = couleur_clan($clan_cible);
						
						// Vérifie si le joueur attaqué a coché l'envoi de mail
						$mail_info_joueur = verif_coche_mail($mysqli, $id_joueur_cible);
						
						if($mail_info_joueur){
							// Envoi du mail
							mail_attaque($mysqli, $nom_perso, $idPerso_carte);
						}
						
						// Si perso ou cible est une infanterie 
						// ou si grade perso >= grade cible - 1
						if ($grade_perso <= $grade_cible + 1 
								|| $grade_perso == 1 || $grade_perso == 101 || $grade_perso == 102 
								|| $grade_cible == 1 || $grade_cible == 101 || $grade_cible == 102) {
							
							$gain_pc = 2;
						} else {
							$gain_pc = 0;
						}
					
					}
					
					$nb_attaque = 0;
					$cible_alive = true;
					$cumul_degats = 0;
					
					// On attaque tant qu'il reste des PA
					while ($pa_perso >= $coutPa_arme && $cible_alive) {
						
						// MAJ des pa du perso
						$pa_perso = $pa_perso - $coutPa_arme;
						
						$sql = "UPDATE perso SET pa_perso = $pa_perso WHERE id_perso='$id_perso'";
						$mysqli->query($sql);
						
						// Est ce que le perso touche sa cible ?
						$touche = mt_rand(0, 100);
						$precision_final = $precision_arme + $bonus_cible;
						
						if ($touche <= $precision_final) {
							// Le perso touche sa cible
							
							// calcul des dégats
							$bonus_degats_charge = 30 - $nb_attaque*10;
							$degats = mt_rand($degats_arme, $degats_arme * $valeur_des_arme) - $protec_cible + $bonus_degats_charge;
							
							if ($touche <= 2) {
								// Coup critique ! Dégats et Gains PC X 2
								$degats = $degats * 2;
								$gain_pc = $gain_pc * 2;
							}
							
							$cumul_degats = $cumul_degats + $degats;
							
							// calcul gain experience
							if ($idPerso_carte >= 200000) {
								$gain_experience = mt_rand(1,4);
							} else {
							
								$calcul_des_xp = ($xp_cible - $xp_perso) / 10;
								if ($calcul_des_xp < 0) {
									$valeur_des_xp = 0;
								} else {
									$valeur_des_xp = mt_rand(1, $calcul_des_xp);
								}
								$gain_experience = ($degats / 2) + $valeur_des_xp;
								
								if ($gain_experience > 15) {
									$gain_experience = 15;
								}
							}
							
							// Maj cible
							// Ajout d'un malus de 2 au défenseur
							$sql = "UPDATE perso SET bonus_perso = bonus_perso - 2, pv_perso = pv_perso - $degats WHERE id_perso='$idPerso_carte'";
							$mysqli->query($sql);
							
							// evenement attaque
							$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>','a chargé ','$idPerso_carte','<font color=$couleur_clan_cible>$nom_cible</font>',': $degats degats',NOW(),'0')";
							$mysqli->query($sql);
							
							// Verification si cible morte							
							if ($pv_cible - $cumul_degats <= 0) {
								$cible_alive = false;
								
								// Perte or 
								$calcul_perte_or = floor(($or_cible * 30) / 100);
								$sql = "UPDATE perso SET or_perso = or_perso - $calcul_perte_or WHERE id_perso='$idPerso_carte'";
								
								// on l'efface de la carte
								$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_cible' AND y_carte='$y_cible'";
								$mysqli->query($sql);
								
								if ($calcul_perte_or > 0) {
									// TODO : On met de l'or sur la carte
									
								}
								
								// evenement perso capture
								$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>','a capturé','$idPerso_carte','<font color=$couleur_clan_cible>$nom_cible</font>','',NOW(),'0')";
								$mysqli->query($sql);
								
								// maj cv
								$sql = "INSERT INTO `cv` (IDActeur_cv, nomActeur_cv, IDCible_cv, nomCible_cv, date_cv) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>','$id_cible','<font color=$couleur_clan_cible>$nom_cible</font>',NOW())";
								$mysqli->query($sql);
								
								// maj stats du perso
								$sql = "UPDATE perso SET nb_kill=nb_kill+1 WHERE id_perso=$id_perso";
								$mysqli->query($sql);
								
								// maj stats de la cible
								$sql = "UPDATE perso SET nb_mort=nb_mort+1 WHERE id_perso='$idPerso_carte'";
								$mysqli->query($sql);
								
								// maj stats camp
								if($clan_cible != $clan_cible){
									$sql = "UPDATE stats_camp_kill SET nb_kill=nb_kill+1 WHERE id_camp=$clan_perso";
									$mysqli->query($sql);
								}
							}
							
							// MAJ xp/pi
							$sql = "UPDATE perso SET xp_perso = xp_perso + $gain_experience, pi_perso = pi_perso + $gain_experience WHERE id_perso='$id_perso'";
							$mysqli->query($sql);
							
							// recup id joueur perso
							$sql = "SELECT idJoueur_perso FROM perso WHERE id_perso='$id_perso'";
							$res = $mysqli->query($sql);
							$t_j = $res->fetch_assoc();
							
							$id_j_perso = $t_j["idJoueur_perso"];
							
							// mise à jour des PC du chef
							$sql = "SELECT perso.id_perso, pc_perso, id_grade FROM perso, perso_as_grade WHERE perso.id_perso = perso_as_grade.id_perso AND idJoueur_perso='$id_j_perso' AND chef='1'";
							$res = $mysqli->query($sql);
							$t_chef = $res->fetch_assoc();
							
							$id_perso_chef = $t_chef["id_perso"];
							$pc_perso_chef = $t_chef["pc_perso"];
							$id_grade_chef = $t_chef["id_grade"];
							
							$sql = "UPDATE perso SET pc_perso = pc_perso + $gain_pc WHERE id_perso='$id_perso_chef'";
							$mysqli->query($sql);
							
							$pc_perso_chef_final = $pc_perso_chef + $gain_pc;
							
							// Verification passage de grade 
							$sql = "SELECT id_grade, nom_grade FROM grades WHERE pc_grade <= $pc_perso_chef_final AND pc_grade != 0 ORDER BY id_grade DESC LIMIT 1";
							$res = $mysqli->query($sql);
							$t_grade = $res->fetch_assoc();
							
							$id_grade_final 	= $t_grade["id_grade"];
							$nom_grade_final	= $t_grade["nom_grade"];
							
							if ($id_grade_chef < $id_grade_final) {
								
								// Passage de grade								
								$sql = "UPDATE perso_as_grade SET id_grade='$id_grade_final' WHERE id_perso='$id_perso_chef'";
								$mysqli->query($sql);
								
								echo "<br /><b>Votre chef de bataillon est passé au grade de $nom_grade_final</b><br />";
								
							}
							
						} else {
							// Le perso rate sa cible
							
							if ($touche >= 98) {
								// Echec critique !
								// Ajout d'un malus supplémentaire à l'attaquant
								$sql = "UPDATE perso SET bonus_perso = bonus_perso - 1 WHERE id_perso='$id_perso'";
							} else {
								// Ajout d'un malus supplémentaire à la cible
								$sql = "UPDATE perso SET bonus_perso = bonus_perso - 1 WHERE id_perso='$idPerso_carte'";
							}
							$mysqli->query($sql);
							
							// evenement
							$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($idPerso_carte,'$nom_cible','a esquivé l\'attaque de','$id_perso','<font color=$couleur_clan_perso>$nom_perso</font>','',NOW(),'0')";
							$mysqli->query($sql);
						}
						
						if ($nb_attaque < 3) {
							$nb_attaque++;
						}
					}
					
				}
				
				break;
			}
		} else {
			if ($i == 5) {
				// On n'a rencontré aucune cible / obstacle 
				// Charge terminée sans effet à part le déplacement
				
				// Mise a jour carte 
				$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_perso' AND y_carte='$y_perso'";
				$mysqli->query($sql);
				
				// Mise à jour position perso sur carte
				$x_perso_final = $x_perso - 4;
				$y_perso_final = $y_perso + 4;
				$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte='$id_perso', image_carte='$image_perso' WHERE x_carte = $x_perso_final AND y_carte = $y_perso_final";
				$mysqli->query($sql);
				
				// Mise à jour du perso
				$sql = "UPDATE perso SET pm_perso = pm_perso - 4, x_perso = $x_perso_final, y_perso = $y_perso_final WHERE id_perso='$id_perso'";
				$mysqli->query($sql);
				
				// evenement
				$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>',' a chargé face au vent... ',NULL,'','jusqu\'en $x_perso_final / $y_perso_final',NOW(),'0')";
				$mysqli->query($sql);
			}
		}
	}
}

/**
 * Fonction de charge vers la direction gauche
 * x - 1
 */
function charge_gauche($mysqli, $id_perso, $nom_perso, $x_perso, $y_perso, $pa_perso, $pv_perso, $xp_perso, $image_perso, $clan, $grade_perso) {
		
	$couleur_clan_perso = couleur_clan($clan);
	
	for ($i = 1; $i <= 5; $i++) {
					
		$sql = "SELECT x_carte, y_carte, fond_carte, occupee_carte, idPerso_carte FROM carte WHERE x_carte = $x_perso - $i AND y_carte = $y_perso";
		$res = $mysqli->query($sql);
		$t_charge = $res->fetch_assoc();
		
		$x_carte 		= $t_charge['x_carte'];
		$y_carte		= $t_charge['y_carte'];
		$fond_carte 	= $t_charge['fond_carte'];
		$occupee_carte	= $t_charge['occupee_carte'];
		$idPerso_carte	= $t_charge['idPerso_carte'];
		
		if ($occupee_carte || $fond_carte != '1.gif') {
			
			// Charge terminée
			
			if ($i <= 3) {
				
				// Mise à jour position perso sur carte
				$nb_deplacements = $i - 1;
				$x_perso_final = $x_perso - $nb_deplacements;
				$y_perso_final = $y_perso;
				
				// MAJ perso sur carte
				$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_perso' AND y_carte='$y_perso'";
				$mysqli->query($sql);
				
				$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte='$id_perso', image_carte='$image_perso' WHERE x_carte = $x_perso_final AND y_carte = $y_perso_final";
				$mysqli->query($sql);
					
				// Mise à jour du perso
				$sql = "UPDATE perso SET pm_perso = pm_perso - $nb_deplacements, x_perso = $x_perso_final, y_perso = $y_perso_final WHERE id_perso='$id_perso'";
				$mysqli->query($sql);
				
				// Charge incomplete => pas d'attaques
				$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>',' a rencontré un obstacle ne lui permettant pas de terminer sa charge ',NULL,'','',NOW(),'0')";
				$mysqli->query($sql);
				
				break;
				
			} else {
				
				// Mise a jour carte 
				$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_perso' AND y_carte='$y_perso'";
				$mysqli->query($sql);
					
				// Charge complète mais cible pas sur plaine => pas d'attaques
				if ($fond_carte != '1.gif') {
					
					// Mise à jour position perso sur carte
					$nb_deplacements = $i - 1;
					$x_perso_final = $x_perso - $nb_deplacements;
					$y_perso_final = $y_perso;
					$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte='$id_perso', image_carte='$image_perso' WHERE x_carte = $x_perso_final AND y_carte = $y_perso_final";
					$mysqli->query($sql);
					
					// Mise à jour du perso
					$sql = "UPDATE perso SET pm_perso = pm_perso - $nb_deplacements, x_perso = $x_perso_final, y_perso = $y_perso_final WHERE id_perso='$id_perso'";
					$mysqli->query($sql);
					
					// cible pas sur plaine => charge ratée
					$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>',' a raté sa charge, le terrain étant impraticable! ',NULL,'','',NOW(),'0')";
					$mysqli->query($sql);
					
				} else if ($idPerso_carte >= 50000 && $idPerso_carte < 200000) {
					
					// Charge complète mais cible batiment => pas d'attaque et le perso perd 40PV
					
					// Mise à jour position perso sur carte
					$nb_deplacements = $i - 1;
					$x_perso_final = $x_perso - $nb_deplacements;
					$y_perso_final = $y_perso;
					$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte='$id_perso', image_carte='$image_perso' WHERE x_carte = $x_perso_final AND y_carte = $y_perso_final";
					$mysqli->query($sql);
					
					// Mise à jour du perso
					$sql = "UPDATE perso SET pv_perso = pv_perso - 40, pm_perso = pm_perso - $nb_deplacements, x_perso = $x_perso_final, y_perso = $y_perso_final WHERE id_perso='$id_perso'";
					$mysqli->query($sql);
					
					if ($pv_perso <= 40) {
						// Perso rapatrié
						
						$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>',' s\'est écrasé contre un batiment suite a sa charge et a perdu connaissance ! ',NULL,'','',NOW(),'0')";
						$mysqli->query($sql);
						
					} else {
					
						$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>',' s\'est écrasé contre un batiment suite a sa charge ! ',NULL,'','',NOW(),'0')";
						$mysqli->query($sql);
						
					}
					
				} else {
					// Charge compléte et réussi sur un autre joueur ou PNJ sur plaine
					// On utilise tous les PA dispo pour faire les attaques
					// +30 degats première attaque, +20 seconde, etc...
					
					// Mise à jour position perso sur carte
					$nb_deplacements = $i - 1;
					$x_perso_final = $x_perso - $nb_deplacements;
					$y_perso_final = $y_perso;
					$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte='$id_perso', image_carte='$image_perso' WHERE x_carte = $x_perso_final AND y_carte = $y_perso_final";
					$mysqli->query($sql);
					
					// Mise à jour du perso
					$sql = "UPDATE perso SET pm_perso = pm_perso - $nb_deplacements, x_perso = $x_perso_final, y_perso = $y_perso_final WHERE id_perso='$id_perso'";
					$mysqli->query($sql);
					
					// Attaques arme CaC
					// Recuperation caracs de l'arme CaC equipé
					$sql = "SELECT nom_arme, coutPa_arme, degatMin_arme, valeur_des_arme, precision_arme FROM arme, perso, perso_as_arme 
							WHERE perso_as_arme.id_perso = perso.id_perso 
							AND perso_as_arme.id_arme = arme.id_arme
							AND perso_as_arme.est_portee = '1' 
							AND arme.porteeMax_arme = '1'
							AND perso.id_perso = '$id_perso'";
					$res = $mysqli->query($sql);
					$t_arme = $res->fetch_assoc();
					
					$nom_arme 			= $t_arme['nom_arme'];
					$degats_arme 		= $t_arme['degatMin_arme'];
					$valeur_des_arme	= $t_arme['valeur_des_arme'];
					$precision_arme 	= $t_arme['precision_arme'];
					$coutPa_arme		= $t_arme['coutPa_arme'];
					
					if ($idPerso_carte >= 200000) {
						// PNJ
						
						// Récupération des infos de la cible
						$sql = "SELECT nom_pnj, pv_i, bonus_i FROM perso WHERE id_perso = '$idPerso_carte'";
						$res = $mysqli->query($sql);
						$t_cible = $res->fetch_assoc();
						
						$nom_cible 		= $t_cible['nom_pnj'];
						$pv_cible		= $t_cible['pv_i'];
						$bonus_cible	= $t_cible['bonus_i'];
						$protec_cible	= 0;
						
						$gain_pc = 0;
						
					} else {
					
						// Récupération des infos de la cible
						$sql = "SELECT nom_perso, x_perso, y_perso, pv_perso, xp_perso, or_perso, protec_perso, bonus_perso, idJoueur_perso, clan, id_grade FROM perso, perso_as_grade 
								WHERE perso_as_grade.id_perso = perso.id_perso
								AND perso.id_perso = '$idPerso_carte'";
						$res = $mysqli->query($sql);
						$t_cible = $res->fetch_assoc();
						
						$nom_cible 			= $t_cible['nom_perso'];
						$x_cible			= $t_cible['x_perso'];
						$y_cible			= $t_cible['y_perso'];
						$pv_cible			= $t_cible['pv_perso'];
						$xp_cible 			= $t_cible['xp_perso'];
						$or_cible 			= $t_cible['or_perso'];
						$bonus_cible		= $t_cible['bonus_perso'];
						$protec_cible		= $t_cible['protec_perso'];
						$grade_cible		= $t_cible['id_grade'];
						$id_joueur_cible 	= $t_cible['idJoueur_perso'];
						$clan_cible 		= $t_cible['clan'];
						
						// Récupération de la couleur associée au clan de la cible
						$couleur_clan_cible = couleur_clan($clan_cible);
						
						// Vérifie si le joueur attaqué a coché l'envoi de mail
						$mail_info_joueur = verif_coche_mail($mysqli, $id_joueur_cible);
						
						if($mail_info_joueur){
							// Envoi du mail
							mail_attaque($mysqli, $nom_perso, $idPerso_carte);
						}
						
						// Si perso ou cible est une infanterie 
						// ou si grade perso >= grade cible - 1
						if ($grade_perso <= $grade_cible + 1 
								|| $grade_perso == 1 || $grade_perso == 101 || $grade_perso == 102 
								|| $grade_cible == 1 || $grade_cible == 101 || $grade_cible == 102) {
							
							$gain_pc = 2;
						} else {
							$gain_pc = 0;
						}
					
					}
					
					$nb_attaque = 0;
					$cible_alive = true;
					$cumul_degats = 0;
					
					// On attaque tant qu'il reste des PA
					while ($pa_perso >= $coutPa_arme && $cible_alive) {
						
						// MAJ des pa du perso
						$pa_perso = $pa_perso - $coutPa_arme;
						
						$sql = "UPDATE perso SET pa_perso = $pa_perso WHERE id_perso='$id_perso'";
						$mysqli->query($sql);
						
						// Est ce que le perso touche sa cible ?
						$touche = mt_rand(0, 100);
						$precision_final = $precision_arme + $bonus_cible;
						
						if ($touche <= $precision_final) {
							// Le perso touche sa cible
							
							// calcul des dégats
							$bonus_degats_charge = 30 - $nb_attaque*10;
							$degats = mt_rand($degats_arme, $degats_arme * $valeur_des_arme) - $protec_cible + $bonus_degats_charge;
							
							if ($touche <= 2) {
								// Coup critique ! Dégats et Gains PC X 2
								$degats = $degats * 2;
								$gain_pc = $gain_pc * 2;
							}
							
							$cumul_degats = $cumul_degats + $degats;
							
							// calcul gain experience
							if ($idPerso_carte >= 200000) {
								$gain_experience = mt_rand(1,4);
							} else {
							
								$calcul_des_xp = ($xp_cible - $xp_perso) / 10;
								if ($calcul_des_xp < 0) {
									$valeur_des_xp = 0;
								} else {
									$valeur_des_xp = mt_rand(1, $calcul_des_xp);
								}
								$gain_experience = ($degats / 2) + $valeur_des_xp;
								
								if ($gain_experience > 15) {
									$gain_experience = 15;
								}
							}
							
							// Maj cible
							// Ajout d'un malus de 2 au défenseur
							$sql = "UPDATE perso SET bonus_perso = bonus_perso - 2, pv_perso = pv_perso - $degats WHERE id_perso='$idPerso_carte'";
							$mysqli->query($sql);
							
							// evenement attaque
							$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>','a chargé ','$idPerso_carte','<font color=$couleur_clan_cible>$nom_cible</font>',': $degats degats',NOW(),'0')";
							$mysqli->query($sql);
							
							// Verification si cible morte							
							if ($pv_cible - $cumul_degats <= 0) {
								$cible_alive = false;
								
								// Perte or 
								$calcul_perte_or = floor(($or_cible * 30) / 100);
								$sql = "UPDATE perso SET or_perso = or_perso - $calcul_perte_or WHERE id_perso='$idPerso_carte'";
								
								// on l'efface de la carte
								$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_cible' AND y_carte='$y_cible'";
								$mysqli->query($sql);
								
								if ($calcul_perte_or > 0) {
									// TODO : On met de l'or sur la carte
									
								}
								
								// evenement perso capture
								$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>','a capturé','$idPerso_carte','<font color=$couleur_clan_cible>$nom_cible</font>','',NOW(),'0')";
								$mysqli->query($sql);
								
								// maj cv
								$sql = "INSERT INTO `cv` (IDActeur_cv, nomActeur_cv, IDCible_cv, nomCible_cv, date_cv) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>','$id_cible','<font color=$couleur_clan_cible>$nom_cible</font>',NOW())";
								$mysqli->query($sql);
								
								// maj stats du perso
								$sql = "UPDATE perso SET nb_kill=nb_kill+1 WHERE id_perso=$id_perso";
								$mysqli->query($sql);
								
								// maj stats de la cible
								$sql = "UPDATE perso SET nb_mort=nb_mort+1 WHERE id_perso='$idPerso_carte'";
								$mysqli->query($sql);
								
								// maj stats camp
								if($clan_cible != $clan_cible){
									$sql = "UPDATE stats_camp_kill SET nb_kill=nb_kill+1 WHERE id_camp=$clan_perso";
									$mysqli->query($sql);
								}
							}
							
							// MAJ xp/pi
							$sql = "UPDATE perso SET xp_perso = xp_perso + $gain_experience, pi_perso = pi_perso + $gain_experience WHERE id_perso='$id_perso'";
							$mysqli->query($sql);
							
							// recup id joueur perso
							$sql = "SELECT idJoueur_perso FROM perso WHERE id_perso='$id_perso'";
							$res = $mysqli->query($sql);
							$t_j = $res->fetch_assoc();
							
							$id_j_perso = $t_j["idJoueur_perso"];
							
							// mise à jour des PC du chef
							$sql = "SELECT perso.id_perso, pc_perso, id_grade FROM perso, perso_as_grade WHERE perso.id_perso = perso_as_grade.id_perso AND idJoueur_perso='$id_j_perso' AND chef='1'";
							$res = $mysqli->query($sql);
							$t_chef = $res->fetch_assoc();
							
							$id_perso_chef = $t_chef["id_perso"];
							$pc_perso_chef = $t_chef["pc_perso"];
							$id_grade_chef = $t_chef["id_grade"];
							
							$sql = "UPDATE perso SET pc_perso = pc_perso + $gain_pc WHERE id_perso='$id_perso_chef'";
							$mysqli->query($sql);
							
							$pc_perso_chef_final = $pc_perso_chef + $gain_pc;
							
							// Verification passage de grade 
							$sql = "SELECT id_grade, nom_grade FROM grades WHERE pc_grade <= $pc_perso_chef_final AND pc_grade != 0 ORDER BY id_grade DESC LIMIT 1";
							$res = $mysqli->query($sql);
							$t_grade = $res->fetch_assoc();
							
							$id_grade_final 	= $t_grade["id_grade"];
							$nom_grade_final	= $t_grade["nom_grade"];
							
							if ($id_grade_chef < $id_grade_final) {
								
								// Passage de grade								
								$sql = "UPDATE perso_as_grade SET id_grade='$id_grade_final' WHERE id_perso='$id_perso_chef'";
								$mysqli->query($sql);
								
								echo "<br /><b>Votre chef de bataillon est passé au grade de $nom_grade_final</b><br />";
								
							}
							
						} else {
							// Le perso rate sa cible
							
							if ($touche >= 98) {
								// Echec critique !
								// Ajout d'un malus supplémentaire à l'attaquant
								$sql = "UPDATE perso SET bonus_perso = bonus_perso - 1 WHERE id_perso='$id_perso'";
							} else {
								// Ajout d'un malus supplémentaire à la cible
								$sql = "UPDATE perso SET bonus_perso = bonus_perso - 1 WHERE id_perso='$idPerso_carte'";
							}
							$mysqli->query($sql);
							
							// evenement
							$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($idPerso_carte,'$nom_cible','a esquivé l\'attaque de','$id_perso','<font color=$couleur_clan_perso>$nom_perso</font>','',NOW(),'0')";
							$mysqli->query($sql);
						}
						
						if ($nb_attaque < 3) {
							$nb_attaque++;
						}
					}
					
				}
				
				break;
			}
		} else {
			if ($i == 5) {
				// On n'a rencontré aucune cible / obstacle 
				// Charge terminée sans effet à part le déplacement
				
				// Mise a jour carte 
				$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_perso' AND y_carte='$y_perso'";
				$mysqli->query($sql);
				
				// Mise à jour position perso sur carte
				$x_perso_final = $x_perso - 4;
				$y_perso_final = $y_perso;
				$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte='$id_perso', image_carte='$image_perso' WHERE x_carte = $x_perso_final AND y_carte = $y_perso_final";
				$mysqli->query($sql);
				
				// Mise à jour du perso
				$sql = "UPDATE perso SET pm_perso = pm_perso - 4, x_perso = $x_perso_final, y_perso = $y_perso_final WHERE id_perso='$id_perso'";
				$mysqli->query($sql);
				
				// evenement
				$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>',' a chargé face au vent... ',NULL,'','jusqu\'en $x_perso_final / $y_perso_final',NOW(),'0')";
				$mysqli->query($sql);
			}
		}
	}
}

/**
 * Fonction de charge vers la direction bas gauche
 * x - 1 et y - 1
 */
function charge_bas_gauche($mysqli, $id_perso, $nom_perso, $x_perso, $y_perso, $pa_perso, $pv_perso, $xp_perso, $image_perso, $clan, $grade_perso) {
		
	$couleur_clan_perso = couleur_clan($clan);
	
	for ($i = 1; $i <= 5; $i++) {
					
		$sql = "SELECT x_carte, y_carte, fond_carte, occupee_carte, idPerso_carte FROM carte WHERE x_carte = $x_perso - $i AND y_carte = $y_perso - $i";
		$res = $mysqli->query($sql);
		$t_charge = $res->fetch_assoc();
		
		$x_carte 		= $t_charge['x_carte'];
		$y_carte		= $t_charge['y_carte'];
		$fond_carte 	= $t_charge['fond_carte'];
		$occupee_carte	= $t_charge['occupee_carte'];
		$idPerso_carte	= $t_charge['idPerso_carte'];
		
		if ($occupee_carte || $fond_carte != '1.gif') {
			
			// Charge terminée
			
			if ($i <= 3) {
				
				// Mise à jour position perso sur carte
				$nb_deplacements = $i - 1;
				$x_perso_final = $x_perso - $nb_deplacements;
				$y_perso_final = $y_perso - $nb_deplacements;
				
				// MAJ perso sur carte
				$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_perso' AND y_carte='$y_perso'";
				$mysqli->query($sql);
				
				$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte='$id_perso', image_carte='$image_perso' WHERE x_carte = $x_perso_final AND y_carte = $y_perso_final";
				$mysqli->query($sql);
					
				// Mise à jour du perso
				$sql = "UPDATE perso SET pm_perso = pm_perso - $nb_deplacements, x_perso = $x_perso_final, y_perso = $y_perso_final WHERE id_perso='$id_perso'";
				$mysqli->query($sql);
				
				// Charge incomplete => pas d'attaques
				$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>',' a rencontré un obstacle ne lui permettant pas de terminer sa charge ',NULL,'','',NOW(),'0')";
				$mysqli->query($sql);
				
				break;
				
			} else {
				
				// Mise a jour carte 
				$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_perso' AND y_carte='$y_perso'";
				$mysqli->query($sql);
					
				// Charge complète mais cible pas sur plaine => pas d'attaques
				if ($fond_carte != '1.gif') {
					
					// Mise à jour position perso sur carte
					$nb_deplacements = $i - 1;
					$x_perso_final = $x_perso - $nb_deplacements;
					$y_perso_final = $y_perso - $nb_deplacements;
					$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte='$id_perso', image_carte='$image_perso' WHERE x_carte = $x_perso_final AND y_carte = $y_perso_final";
					$mysqli->query($sql);
					
					// Mise à jour du perso
					$sql = "UPDATE perso SET pm_perso = pm_perso - $nb_deplacements, x_perso = $x_perso_final, y_perso = $y_perso_final WHERE id_perso='$id_perso'";
					$mysqli->query($sql);
					
					// cible pas sur plaine => charge ratée
					$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>',' a raté sa charge, le terrain étant impraticable! ',NULL,'','',NOW(),'0')";
					$mysqli->query($sql);
					
				} else if ($idPerso_carte >= 50000 && $idPerso_carte < 200000) {
					
					// Charge complète mais cible batiment => pas d'attaque et le perso perd 40PV
					
					// Mise à jour position perso sur carte
					$nb_deplacements = $i - 1;
					$x_perso_final = $x_perso - $nb_deplacements;
					$y_perso_final = $y_perso - $nb_deplacements;
					$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte='$id_perso', image_carte='$image_perso' WHERE x_carte = $x_perso_final AND y_carte = $y_perso_final";
					$mysqli->query($sql);
					
					// Mise à jour du perso
					$sql = "UPDATE perso SET pv_perso = pv_perso - 40, pm_perso = pm_perso - $nb_deplacements, x_perso = $x_perso_final, y_perso = $y_perso_final WHERE id_perso='$id_perso'";
					$mysqli->query($sql);
					
					if ($pv_perso <= 40) {
						// Perso rapatrié
						
						$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>',' s\'est écrasé contre un batiment suite a sa charge et a perdu connaissance ! ',NULL,'','',NOW(),'0')";
						$mysqli->query($sql);
						
					} else {
					
						$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>',' s\'est écrasé contre un batiment suite a sa charge ! ',NULL,'','',NOW(),'0')";
						$mysqli->query($sql);
						
					}
					
				} else {
					// Charge compléte et réussi sur un autre joueur ou PNJ sur plaine
					// On utilise tous les PA dispo pour faire les attaques
					// +30 degats première attaque, +20 seconde, etc...
					
					// Mise à jour position perso sur carte
					$nb_deplacements = $i - 1;
					$x_perso_final = $x_perso - $nb_deplacements;
					$y_perso_final = $y_perso - $nb_deplacements;
					$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte='$id_perso', image_carte='$image_perso' WHERE x_carte = $x_perso_final AND y_carte = $y_perso_final";
					$mysqli->query($sql);
					
					// Mise à jour du perso
					$sql = "UPDATE perso SET pm_perso = pm_perso - $nb_deplacements, x_perso = $x_perso_final, y_perso = $y_perso_final WHERE id_perso='$id_perso'";
					$mysqli->query($sql);
					
					// Attaques arme CaC
					// Recuperation caracs de l'arme CaC equipé
					$sql = "SELECT nom_arme, coutPa_arme, degatMin_arme, valeur_des_arme, precision_arme FROM arme, perso, perso_as_arme 
							WHERE perso_as_arme.id_perso = perso.id_perso 
							AND perso_as_arme.id_arme = arme.id_arme
							AND perso_as_arme.est_portee = '1' 
							AND arme.porteeMax_arme = '1'
							AND perso.id_perso = '$id_perso'";
					$res = $mysqli->query($sql);
					$t_arme = $res->fetch_assoc();
					
					$nom_arme 			= $t_arme['nom_arme'];
					$degats_arme 		= $t_arme['degatMin_arme'];
					$valeur_des_arme	= $t_arme['valeur_des_arme'];
					$precision_arme 	= $t_arme['precision_arme'];
					$coutPa_arme		= $t_arme['coutPa_arme'];
					
					if ($idPerso_carte >= 200000) {
						// PNJ
						
						// Récupération des infos de la cible
						$sql = "SELECT nom_pnj, pv_i, bonus_i FROM perso WHERE id_perso = '$idPerso_carte'";
						$res = $mysqli->query($sql);
						$t_cible = $res->fetch_assoc();
						
						$nom_cible 		= $t_cible['nom_pnj'];
						$pv_cible		= $t_cible['pv_i'];
						$bonus_cible	= $t_cible['bonus_i'];
						$protec_cible	= 0;
						
						$gain_pc = 0;
						
					} else {
					
						// Récupération des infos de la cible
						$sql = "SELECT nom_perso, x_perso, y_perso, pv_perso, xp_perso, or_perso, protec_perso, bonus_perso, idJoueur_perso, clan, id_grade FROM perso, perso_as_grade 
								WHERE perso_as_grade.id_perso = perso.id_perso
								AND perso.id_perso = '$idPerso_carte'";
						$res = $mysqli->query($sql);
						$t_cible = $res->fetch_assoc();
						
						$nom_cible 			= $t_cible['nom_perso'];
						$x_cible			= $t_cible['x_perso'];
						$y_cible			= $t_cible['y_perso'];
						$pv_cible			= $t_cible['pv_perso'];
						$xp_cible 			= $t_cible['xp_perso'];
						$or_cible 			= $t_cible['or_perso'];
						$bonus_cible		= $t_cible['bonus_perso'];
						$protec_cible		= $t_cible['protec_perso'];
						$grade_cible		= $t_cible['id_grade'];
						$id_joueur_cible 	= $t_cible['idJoueur_perso'];
						$clan_cible 		= $t_cible['clan'];
						
						// Récupération de la couleur associée au clan de la cible
						$couleur_clan_cible = couleur_clan($clan_cible);
						
						// Vérifie si le joueur attaqué a coché l'envoi de mail
						$mail_info_joueur = verif_coche_mail($mysqli, $id_joueur_cible);
						
						if($mail_info_joueur){
							// Envoi du mail
							mail_attaque($mysqli, $nom_perso, $idPerso_carte);
						}
						
						// Si perso ou cible est une infanterie 
						// ou si grade perso >= grade cible - 1
						if ($grade_perso <= $grade_cible + 1 
								|| $grade_perso == 1 || $grade_perso == 101 || $grade_perso == 102 
								|| $grade_cible == 1 || $grade_cible == 101 || $grade_cible == 102) {
							
							$gain_pc = 2;
						} else {
							$gain_pc = 0;
						}
					
					}
					
					$nb_attaque = 0;
					$cible_alive = true;
					$cumul_degats = 0;
					
					// On attaque tant qu'il reste des PA
					while ($pa_perso >= $coutPa_arme && $cible_alive) {
						
						// MAJ des pa du perso
						$pa_perso = $pa_perso - $coutPa_arme;
						
						$sql = "UPDATE perso SET pa_perso = $pa_perso WHERE id_perso='$id_perso'";
						$mysqli->query($sql);
						
						// Est ce que le perso touche sa cible ?
						$touche = mt_rand(0, 100);
						$precision_final = $precision_arme + $bonus_cible;
						
						if ($touche <= $precision_final) {
							// Le perso touche sa cible
							
							// calcul des dégats
							$bonus_degats_charge = 30 - $nb_attaque*10;
							$degats = mt_rand($degats_arme, $degats_arme * $valeur_des_arme) - $protec_cible + $bonus_degats_charge;
							
							if ($touche <= 2) {
								// Coup critique ! Dégats et Gains PC X 2
								$degats = $degats * 2;
								$gain_pc = $gain_pc * 2;
							}
							
							$cumul_degats = $cumul_degats + $degats;
							
							// calcul gain experience
							if ($idPerso_carte >= 200000) {
								$gain_experience = mt_rand(1,4);
							} else {
							
								$calcul_des_xp = ($xp_cible - $xp_perso) / 10;
								if ($calcul_des_xp < 0) {
									$valeur_des_xp = 0;
								} else {
									$valeur_des_xp = mt_rand(1, $calcul_des_xp);
								}
								$gain_experience = ($degats / 2) + $valeur_des_xp;
								
								if ($gain_experience > 15) {
									$gain_experience = 15;
								}
							}
							
							// Maj cible
							// Ajout d'un malus de 2 au défenseur
							$sql = "UPDATE perso SET bonus_perso = bonus_perso - 2, pv_perso = pv_perso - $degats WHERE id_perso='$idPerso_carte'";
							$mysqli->query($sql);
							
							// evenement attaque
							$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>','a chargé ','$idPerso_carte','<font color=$couleur_clan_cible>$nom_cible</font>',': $degats degats',NOW(),'0')";
							$mysqli->query($sql);
							
							// Verification si cible morte							
							if ($pv_cible - $cumul_degats <= 0) {
								$cible_alive = false;
								
								// Perte or 
								$calcul_perte_or = floor(($or_cible * 30) / 100);
								$sql = "UPDATE perso SET or_perso = or_perso - $calcul_perte_or WHERE id_perso='$idPerso_carte'";
								
								// on l'efface de la carte
								$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_cible' AND y_carte='$y_cible'";
								$mysqli->query($sql);
								
								if ($calcul_perte_or > 0) {
									// TODO : On met de l'or sur la carte
									
								}
								
								// evenement perso capture
								$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>','a capturé','$idPerso_carte','<font color=$couleur_clan_cible>$nom_cible</font>','',NOW(),'0')";
								$mysqli->query($sql);
								
								// maj cv
								$sql = "INSERT INTO `cv` (IDActeur_cv, nomActeur_cv, IDCible_cv, nomCible_cv, date_cv) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>','$id_cible','<font color=$couleur_clan_cible>$nom_cible</font>',NOW())";
								$mysqli->query($sql);
								
								// maj stats du perso
								$sql = "UPDATE perso SET nb_kill=nb_kill+1 WHERE id_perso=$id_perso";
								$mysqli->query($sql);
								
								// maj stats de la cible
								$sql = "UPDATE perso SET nb_mort=nb_mort+1 WHERE id_perso='$idPerso_carte'";
								$mysqli->query($sql);
								
								// maj stats camp
								if($clan_cible != $clan_cible){
									$sql = "UPDATE stats_camp_kill SET nb_kill=nb_kill+1 WHERE id_camp=$clan_perso";
									$mysqli->query($sql);
								}
							}
							
							// MAJ xp/pi
							$sql = "UPDATE perso SET xp_perso = xp_perso + $gain_experience, pi_perso = pi_perso + $gain_experience WHERE id_perso='$id_perso'";
							$mysqli->query($sql);
							
							// recup id joueur perso
							$sql = "SELECT idJoueur_perso FROM perso WHERE id_perso='$id_perso'";
							$res = $mysqli->query($sql);
							$t_j = $res->fetch_assoc();
							
							$id_j_perso = $t_j["idJoueur_perso"];
							
							// mise à jour des PC du chef
							$sql = "SELECT perso.id_perso, pc_perso, id_grade FROM perso, perso_as_grade WHERE perso.id_perso = perso_as_grade.id_perso AND idJoueur_perso='$id_j_perso' AND chef='1'";
							$res = $mysqli->query($sql);
							$t_chef = $res->fetch_assoc();
							
							$id_perso_chef = $t_chef["id_perso"];
							$pc_perso_chef = $t_chef["pc_perso"];
							$id_grade_chef = $t_chef["id_grade"];
							
							$sql = "UPDATE perso SET pc_perso = pc_perso + $gain_pc WHERE id_perso='$id_perso_chef'";
							$mysqli->query($sql);
							
							$pc_perso_chef_final = $pc_perso_chef + $gain_pc;
							
							// Verification passage de grade 
							$sql = "SELECT id_grade, nom_grade FROM grades WHERE pc_grade <= $pc_perso_chef_final AND pc_grade != 0 ORDER BY id_grade DESC LIMIT 1";
							$res = $mysqli->query($sql);
							$t_grade = $res->fetch_assoc();
							
							$id_grade_final 	= $t_grade["id_grade"];
							$nom_grade_final	= $t_grade["nom_grade"];
							
							if ($id_grade_chef < $id_grade_final) {
								
								// Passage de grade								
								$sql = "UPDATE perso_as_grade SET id_grade='$id_grade_final' WHERE id_perso='$id_perso_chef'";
								$mysqli->query($sql);
								
								echo "<br /><b>Votre chef de bataillon est passé au grade de $nom_grade_final</b><br />";
								
							}
							
						} else {
							// Le perso rate sa cible
							
							if ($touche >= 98) {
								// Echec critique !
								// Ajout d'un malus supplémentaire à l'attaquant
								$sql = "UPDATE perso SET bonus_perso = bonus_perso - 1 WHERE id_perso='$id_perso'";
							} else {
								// Ajout d'un malus supplémentaire à la cible
								$sql = "UPDATE perso SET bonus_perso = bonus_perso - 1 WHERE id_perso='$idPerso_carte'";
							}
							$mysqli->query($sql);
							
							// evenement
							$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($idPerso_carte,'$nom_cible','a esquivé l\'attaque de','$id_perso','<font color=$couleur_clan_perso>$nom_perso</font>','',NOW(),'0')";
							$mysqli->query($sql);
						}
						
						if ($nb_attaque < 3) {
							$nb_attaque++;
						}
					}
				}
				
				break;
			}
		} else {
			if ($i == 5) {
				// On n'a rencontré aucune cible / obstacle 
				// Charge terminée sans effet à part le déplacement
				
				// Mise a jour carte 
				$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_perso' AND y_carte='$y_perso'";
				$mysqli->query($sql);
				
				// Mise à jour position perso sur carte
				$x_perso_final = $x_perso - 4;
				$y_perso_final = $y_perso - 4;
				$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte='$id_perso', image_carte='$image_perso' WHERE x_carte = $x_perso_final AND y_carte = $y_perso_final";
				$mysqli->query($sql);
				
				// Mise à jour du perso
				$sql = "UPDATE perso SET pm_perso = pm_perso - 4, x_perso = $x_perso_final, y_perso = $y_perso_final WHERE id_perso='$id_perso'";
				$mysqli->query($sql);
				
				// evenement
				$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>',' a chargé face au vent... ',NULL,'','jusqu\'en $x_perso_final / $y_perso_final',NOW(),'0')";
				$mysqli->query($sql);
			}
		}
	}
}

/**
 * Fonction de charge vers la direction bas
 * y - 1 
 */
function charge_bas($mysqli, $id_perso, $nom_perso, $x_perso, $y_perso, $pa_perso, $pv_perso, $xp_perso, $image_perso, $clan, $grade_perso) {
		
	$couleur_clan_perso = couleur_clan($clan);
	
	for ($i = 1; $i <= 5; $i++) {
					
		$sql = "SELECT x_carte, y_carte, fond_carte, occupee_carte, idPerso_carte FROM carte WHERE x_carte = $x_perso AND y_carte = $y_perso - $i";
		$res = $mysqli->query($sql);
		$t_charge = $res->fetch_assoc();
		
		$x_carte 		= $t_charge['x_carte'];
		$y_carte		= $t_charge['y_carte'];
		$fond_carte 	= $t_charge['fond_carte'];
		$occupee_carte	= $t_charge['occupee_carte'];
		$idPerso_carte	= $t_charge['idPerso_carte'];
		
		if ($occupee_carte || $fond_carte != '1.gif') {
			
			// Charge terminée
			
			if ($i <= 3) {
				
				// Mise à jour position perso sur carte
				$nb_deplacements = $i - 1;
				$x_perso_final = $x_perso;
				$y_perso_final = $y_perso - $nb_deplacements;
				
				// MAJ perso sur carte
				$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_perso' AND y_carte='$y_perso'";
				$mysqli->query($sql);
				
				$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte='$id_perso', image_carte='$image_perso' WHERE x_carte = $x_perso_final AND y_carte = $y_perso_final";
				$mysqli->query($sql);
					
				// Mise à jour du perso
				$sql = "UPDATE perso SET pm_perso = pm_perso - $nb_deplacements, x_perso = $x_perso_final, y_perso = $y_perso_final WHERE id_perso='$id_perso'";
				$mysqli->query($sql);
				
				// Charge incomplete => pas d'attaques
				$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>',' a rencontré un obstacle ne lui permettant pas de terminer sa charge ',NULL,'','',NOW(),'0')";
				$mysqli->query($sql);
				
				break;
				
			} else {
				
				// Mise a jour carte 
				$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_perso' AND y_carte='$y_perso'";
				$mysqli->query($sql);
					
				// Charge complète mais cible pas sur plaine => pas d'attaques
				if ($fond_carte != '1.gif') {
					
					// Mise à jour position perso sur carte
					$nb_deplacements = $i - 1;
					$x_perso_final = $x_perso;
					$y_perso_final = $y_perso - $nb_deplacements;
					$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte='$id_perso', image_carte='$image_perso' WHERE x_carte = $x_perso_final AND y_carte = $y_perso_final";
					$mysqli->query($sql);
					
					// Mise à jour du perso
					$sql = "UPDATE perso SET pm_perso = pm_perso - $nb_deplacements, x_perso = $x_perso_final, y_perso = $y_perso_final WHERE id_perso='$id_perso'";
					$mysqli->query($sql);
					
					// cible pas sur plaine => charge ratée
					$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>',' a raté sa charge, le terrain étant impraticable! ',NULL,'','',NOW(),'0')";
					$mysqli->query($sql);
					
				} else if ($idPerso_carte >= 50000 && $idPerso_carte < 200000) {
					
					// Charge complète mais cible batiment => pas d'attaque et le perso perd 40PV
					
					// Mise à jour position perso sur carte
					$nb_deplacements = $i - 1;
					$x_perso_final = $x_perso;
					$y_perso_final = $y_perso - $nb_deplacements;
					$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte='$id_perso', image_carte='$image_perso' WHERE x_carte = $x_perso_final AND y_carte = $y_perso_final";
					$mysqli->query($sql);
					
					// Mise à jour du perso
					$sql = "UPDATE perso SET pv_perso = pv_perso - 40, pm_perso = pm_perso - $nb_deplacements, x_perso = $x_perso_final, y_perso = $y_perso_final WHERE id_perso='$id_perso'";
					$mysqli->query($sql);
					
					if ($pv_perso <= 40) {
						// Perso rapatrié
						
						$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>',' s\'est écrasé contre un batiment suite a sa charge et a perdu connaissance ! ',NULL,'','',NOW(),'0')";
						$mysqli->query($sql);
						
					} else {
					
						$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>',' s\'est écrasé contre un batiment suite a sa charge ! ',NULL,'','',NOW(),'0')";
						$mysqli->query($sql);
						
					}
					
				} else {
					// Charge compléte et réussi sur un autre joueur ou PNJ sur plaine
					// On utilise tous les PA dispo pour faire les attaques
					// +30 degats première attaque, +20 seconde, etc...
					
					// Mise à jour position perso sur carte
					$nb_deplacements = $i - 1;
					$x_perso_final = $x_perso;
					$y_perso_final = $y_perso - $nb_deplacements;
					$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte='$id_perso', image_carte='$image_perso' WHERE x_carte = $x_perso_final AND y_carte = $y_perso_final";
					$mysqli->query($sql);
					
					// Mise à jour du perso
					$sql = "UPDATE perso SET pm_perso = pm_perso - $nb_deplacements, x_perso = $x_perso_final, y_perso = $y_perso_final WHERE id_perso='$id_perso'";
					$mysqli->query($sql);
					
					// Attaques arme CaC
					// Recuperation caracs de l'arme CaC equipé
					$sql = "SELECT nom_arme, coutPa_arme, degatMin_arme, valeur_des_arme, precision_arme FROM arme, perso, perso_as_arme 
							WHERE perso_as_arme.id_perso = perso.id_perso 
							AND perso_as_arme.id_arme = arme.id_arme
							AND perso_as_arme.est_portee = '1' 
							AND arme.porteeMax_arme = '1'
							AND perso.id_perso = '$id_perso'";
					$res = $mysqli->query($sql);
					$t_arme = $res->fetch_assoc();
					
					$nom_arme 			= $t_arme['nom_arme'];
					$degats_arme 		= $t_arme['degatMin_arme'];
					$valeur_des_arme	= $t_arme['valeur_des_arme'];
					$precision_arme 	= $t_arme['precision_arme'];
					$coutPa_arme		= $t_arme['coutPa_arme'];
					
					if ($idPerso_carte >= 200000) {
						// PNJ
						
						// Récupération des infos de la cible
						$sql = "SELECT nom_pnj, pv_i, bonus_i FROM perso WHERE id_perso = '$idPerso_carte'";
						$res = $mysqli->query($sql);
						$t_cible = $res->fetch_assoc();
						
						$nom_cible 		= $t_cible['nom_pnj'];
						$pv_cible		= $t_cible['pv_i'];
						$bonus_cible	= $t_cible['bonus_i'];
						$protec_cible	= 0;
						
						$gain_pc = 0;
						
					} else {
					
						// Récupération des infos de la cible
						$sql = "SELECT nom_perso, x_perso, y_perso, pv_perso, xp_perso, or_perso, protec_perso, bonus_perso, idJoueur_perso, clan, id_grade FROM perso, perso_as_grade 
								WHERE perso_as_grade.id_perso = perso.id_perso
								AND perso.id_perso = '$idPerso_carte'";
						$res = $mysqli->query($sql);
						$t_cible = $res->fetch_assoc();
						
						$nom_cible 			= $t_cible['nom_perso'];
						$x_cible			= $t_cible['x_perso'];
						$y_cible			= $t_cible['y_perso'];
						$pv_cible			= $t_cible['pv_perso'];
						$xp_cible 			= $t_cible['xp_perso'];
						$or_cible 			= $t_cible['or_perso'];
						$bonus_cible		= $t_cible['bonus_perso'];
						$protec_cible		= $t_cible['protec_perso'];
						$grade_cible		= $t_cible['id_grade'];
						$id_joueur_cible 	= $t_cible['idJoueur_perso'];
						$clan_cible 		= $t_cible['clan'];
						
						// Récupération de la couleur associée au clan de la cible
						$couleur_clan_cible = couleur_clan($clan_cible);
						
						// Vérifie si le joueur attaqué a coché l'envoi de mail
						$mail_info_joueur = verif_coche_mail($mysqli, $id_joueur_cible);
						
						if($mail_info_joueur){
							// Envoi du mail
							mail_attaque($mysqli, $nom_perso, $idPerso_carte);
						}
						
						// Si perso ou cible est une infanterie 
						// ou si grade perso >= grade cible - 1
						if ($grade_perso <= $grade_cible + 1 
								|| $grade_perso == 1 || $grade_perso == 101 || $grade_perso == 102 
								|| $grade_cible == 1 || $grade_cible == 101 || $grade_cible == 102) {
							
							$gain_pc = 2;
						} else {
							$gain_pc = 0;
						}
					
					}
					
					$nb_attaque = 0;
					$cible_alive = true;
					$cumul_degats = 0;
					
					// On attaque tant qu'il reste des PA
					while ($pa_perso >= $coutPa_arme && $cible_alive) {
						
						// MAJ des pa du perso
						$pa_perso = $pa_perso - $coutPa_arme;
						
						$sql = "UPDATE perso SET pa_perso = $pa_perso WHERE id_perso='$id_perso'";
						$mysqli->query($sql);
						
						// Est ce que le perso touche sa cible ?
						$touche = mt_rand(0, 100);
						$precision_final = $precision_arme + $bonus_cible;
						
						if ($touche <= $precision_final) {
							// Le perso touche sa cible
							
							// calcul des dégats
							$bonus_degats_charge = 30 - $nb_attaque*10;
							$degats = mt_rand($degats_arme, $degats_arme * $valeur_des_arme) - $protec_cible + $bonus_degats_charge;
							
							if ($touche <= 2) {
								// Coup critique ! Dégats et Gains PC X 2
								$degats = $degats * 2;
								$gain_pc = $gain_pc * 2;
							}
							
							$cumul_degats = $cumul_degats + $degats;
							
							// calcul gain experience
							if ($idPerso_carte >= 200000) {
								$gain_experience = mt_rand(1,4);
							} else {
							
								$calcul_des_xp = ($xp_cible - $xp_perso) / 10;
								if ($calcul_des_xp < 0) {
									$valeur_des_xp = 0;
								} else {
									$valeur_des_xp = mt_rand(1, $calcul_des_xp);
								}
								$gain_experience = ($degats / 2) + $valeur_des_xp;
								
								if ($gain_experience > 15) {
									$gain_experience = 15;
								}
							}
							
							// Maj cible
							// Ajout d'un malus de 2 au défenseur
							$sql = "UPDATE perso SET bonus_perso = bonus_perso - 2, pv_perso = pv_perso - $degats WHERE id_perso='$idPerso_carte'";
							$mysqli->query($sql);
							
							// evenement attaque
							$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>','a chargé ','$idPerso_carte','<font color=$couleur_clan_cible>$nom_cible</font>',': $degats degats',NOW(),'0')";
							$mysqli->query($sql);
							
							// Verification si cible morte							
							if ($pv_cible - $cumul_degats <= 0) {
								$cible_alive = false;
								
								// Perte or 
								$calcul_perte_or = floor(($or_cible * 30) / 100);
								$sql = "UPDATE perso SET or_perso = or_perso - $calcul_perte_or WHERE id_perso='$idPerso_carte'";
								
								// on l'efface de la carte
								$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_cible' AND y_carte='$y_cible'";
								$mysqli->query($sql);
								
								if ($calcul_perte_or > 0) {
									// TODO : On met de l'or sur la carte
									
								}
								
								// evenement perso capture
								$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>','a capturé','$idPerso_carte','<font color=$couleur_clan_cible>$nom_cible</font>','',NOW(),'0')";
								$mysqli->query($sql);
								
								// maj cv
								$sql = "INSERT INTO `cv` (IDActeur_cv, nomActeur_cv, IDCible_cv, nomCible_cv, date_cv) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>','$id_cible','<font color=$couleur_clan_cible>$nom_cible</font>',NOW())";
								$mysqli->query($sql);
								
								// maj stats du perso
								$sql = "UPDATE perso SET nb_kill=nb_kill+1 WHERE id_perso=$id_perso";
								$mysqli->query($sql);
								
								// maj stats de la cible
								$sql = "UPDATE perso SET nb_mort=nb_mort+1 WHERE id_perso='$idPerso_carte'";
								$mysqli->query($sql);
								
								// maj stats camp
								if($clan_cible != $clan_cible){
									$sql = "UPDATE stats_camp_kill SET nb_kill=nb_kill+1 WHERE id_camp=$clan_perso";
									$mysqli->query($sql);
								}
							}
							
							// MAJ xp/pi
							$sql = "UPDATE perso SET xp_perso = xp_perso + $gain_experience, pi_perso = pi_perso + $gain_experience WHERE id_perso='$id_perso'";
							$mysqli->query($sql);
							
							// recup id joueur perso
							$sql = "SELECT idJoueur_perso FROM perso WHERE id_perso='$id_perso'";
							$res = $mysqli->query($sql);
							$t_j = $res->fetch_assoc();
							
							$id_j_perso = $t_j["idJoueur_perso"];
							
							// mise à jour des PC du chef
							$sql = "SELECT perso.id_perso, pc_perso, id_grade FROM perso, perso_as_grade WHERE perso.id_perso = perso_as_grade.id_perso AND idJoueur_perso='$id_j_perso' AND chef='1'";
							$res = $mysqli->query($sql);
							$t_chef = $res->fetch_assoc();
							
							$id_perso_chef = $t_chef["id_perso"];
							$pc_perso_chef = $t_chef["pc_perso"];
							$id_grade_chef = $t_chef["id_grade"];
							
							$sql = "UPDATE perso SET pc_perso = pc_perso + $gain_pc WHERE id_perso='$id_perso_chef'";
							$mysqli->query($sql);
							
							$pc_perso_chef_final = $pc_perso_chef + $gain_pc;
							
							// Verification passage de grade 
							$sql = "SELECT id_grade, nom_grade FROM grades WHERE pc_grade <= $pc_perso_chef_final AND pc_grade != 0 ORDER BY id_grade DESC LIMIT 1";
							$res = $mysqli->query($sql);
							$t_grade = $res->fetch_assoc();
							
							$id_grade_final 	= $t_grade["id_grade"];
							$nom_grade_final	= $t_grade["nom_grade"];
							
							if ($id_grade_chef < $id_grade_final) {
								
								// Passage de grade								
								$sql = "UPDATE perso_as_grade SET id_grade='$id_grade_final' WHERE id_perso='$id_perso_chef'";
								$mysqli->query($sql);
								
								echo "<br /><b>Votre chef de bataillon est passé au grade de $nom_grade_final</b><br />";
								
							}
							
						} else {
							// Le perso rate sa cible
							
							if ($touche >= 98) {
								// Echec critique !
								// Ajout d'un malus supplémentaire à l'attaquant
								$sql = "UPDATE perso SET bonus_perso = bonus_perso - 1 WHERE id_perso='$id_perso'";
							} else {
								// Ajout d'un malus supplémentaire à la cible
								$sql = "UPDATE perso SET bonus_perso = bonus_perso - 1 WHERE id_perso='$idPerso_carte'";
							}
							$mysqli->query($sql);
							
							// evenement
							$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($idPerso_carte,'$nom_cible','a esquivé l\'attaque de','$id_perso','<font color=$couleur_clan_perso>$nom_perso</font>','',NOW(),'0')";
							$mysqli->query($sql);
						}
						
						if ($nb_attaque < 3) {
							$nb_attaque++;
						}
					}
				}
				
				break;
			}
		} else {
			if ($i == 5) {
				// On n'a rencontré aucune cible / obstacle 
				// Charge terminée sans effet à part le déplacement
				
				// Mise a jour carte 
				$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_perso' AND y_carte='$y_perso'";
				$mysqli->query($sql);
				
				// Mise à jour position perso sur carte
				$x_perso_final = $x_perso;
				$y_perso_final = $y_perso - 4;
				$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte='$id_perso', image_carte='$image_perso' WHERE x_carte = $x_perso_final AND y_carte = $y_perso_final";
				$mysqli->query($sql);
				
				// Mise à jour du perso
				$sql = "UPDATE perso SET pm_perso = pm_perso - 4, x_perso = $x_perso_final, y_perso = $y_perso_final WHERE id_perso='$id_perso'";
				$mysqli->query($sql);
				
				// evenement
				$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>',' a chargé face au vent... ',NULL,'','jusqu\'en $x_perso_final / $y_perso_final',NOW(),'0')";
				$mysqli->query($sql);
			}
		}
	}
}

/**
 * Fonction de charge vers la direction bas droite
 * x + 1 et y - 1
 */
function charge_bas_droite($mysqli, $id_perso, $nom_perso, $x_perso, $y_perso, $pa_perso, $pv_perso, $xp_perso, $image_perso, $clan, $grade_perso) {
		
	$couleur_clan_perso = couleur_clan($clan);
	
	for ($i = 1; $i <= 5; $i++) {
					
		$sql = "SELECT x_carte, y_carte, fond_carte, occupee_carte, idPerso_carte FROM carte WHERE x_carte = $x_perso + $i AND y_carte = $y_perso - $i";
		$res = $mysqli->query($sql);
		$t_charge = $res->fetch_assoc();
		
		$x_carte 		= $t_charge['x_carte'];
		$y_carte		= $t_charge['y_carte'];
		$fond_carte 	= $t_charge['fond_carte'];
		$occupee_carte	= $t_charge['occupee_carte'];
		$idPerso_carte	= $t_charge['idPerso_carte'];
		
		if ($occupee_carte || $fond_carte != '1.gif') {
			
			// Charge terminée
			
			if ($i <= 3) {
				
				// Mise à jour position perso sur carte
				$nb_deplacements = $i - 1;
				$x_perso_final = $x_perso + $nb_deplacements;
				$y_perso_final = $y_perso - $nb_deplacements;
				
				// MAJ perso sur carte
				$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_perso' AND y_carte='$y_perso'";
				$mysqli->query($sql);
				
				$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte='$id_perso', image_carte='$image_perso' WHERE x_carte = $x_perso_final AND y_carte = $y_perso_final";
				$mysqli->query($sql);
					
				// Mise à jour du perso
				$sql = "UPDATE perso SET pm_perso = pm_perso - $nb_deplacements, x_perso = $x_perso_final, y_perso = $y_perso_final WHERE id_perso='$id_perso'";
				$mysqli->query($sql);
				
				// Charge incomplete => pas d'attaques
				$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>',' a rencontré un obstacle ne lui permettant pas de terminer sa charge ',NULL,'','',NOW(),'0')";
				$mysqli->query($sql);
				
				break;
				
			} else {
				
				// Mise a jour carte 
				$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_perso' AND y_carte='$y_perso'";
				$mysqli->query($sql);
					
				// Charge complète mais cible pas sur plaine => pas d'attaques
				if ($fond_carte != '1.gif') {
					
					// Mise à jour position perso sur carte
					$nb_deplacements = $i - 1;
					$x_perso_final = $x_perso + $nb_deplacements;
					$y_perso_final = $y_perso - $nb_deplacements;
					$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte='$id_perso', image_carte='$image_perso' WHERE x_carte = $x_perso_final AND y_carte = $y_perso_final";
					$mysqli->query($sql);
					
					// Mise à jour du perso
					$sql = "UPDATE perso SET pm_perso = pm_perso - $nb_deplacements, x_perso = $x_perso_final, y_perso = $y_perso_final WHERE id_perso='$id_perso'";
					$mysqli->query($sql);
					
					// cible pas sur plaine => charge ratée
					$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>',' a raté sa charge, le terrain étant impraticable! ',NULL,'','',NOW(),'0')";
					$mysqli->query($sql);
					
				} else if ($idPerso_carte >= 50000 && $idPerso_carte < 200000) {
					
					// Charge complète mais cible batiment => pas d'attaque et le perso perd 40PV
					
					// Mise à jour position perso sur carte
					$nb_deplacements = $i - 1;
					$x_perso_final = $x_perso + $nb_deplacements;
					$y_perso_final = $y_perso - $nb_deplacements;
					$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte='$id_perso', image_carte='$image_perso' WHERE x_carte = $x_perso_final AND y_carte = $y_perso_final";
					$mysqli->query($sql);
					
					// Mise à jour du perso
					$sql = "UPDATE perso SET pv_perso = pv_perso - 40, pm_perso = pm_perso - $nb_deplacements, x_perso = $x_perso_final, y_perso = $y_perso_final WHERE id_perso='$id_perso'";
					$mysqli->query($sql);
					
					if ($pv_perso <= 40) {
						// Perso rapatrié
						
						$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>',' s\'est écrasé contre un batiment suite a sa charge et a perdu connaissance ! ',NULL,'','',NOW(),'0')";
						$mysqli->query($sql);
						
					} else {
					
						$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>',' s\'est écrasé contre un batiment suite a sa charge ! ',NULL,'','',NOW(),'0')";
						$mysqli->query($sql);
						
					}
					
				} else {
					// Charge compléte et réussi sur un autre joueur ou PNJ sur plaine
					// On utilise tous les PA dispo pour faire les attaques
					// +30 degats première attaque, +20 seconde, etc...
					
					// Mise à jour position perso sur carte
					$nb_deplacements = $i - 1;
					$x_perso_final = $x_perso + $nb_deplacements;
					$y_perso_final = $y_perso - $nb_deplacements;
					$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte='$id_perso', image_carte='$image_perso' WHERE x_carte = $x_perso_final AND y_carte = $y_perso_final";
					$mysqli->query($sql);
					
					// Mise à jour du perso
					$sql = "UPDATE perso SET pm_perso = pm_perso - $nb_deplacements, x_perso = $x_perso_final, y_perso = $y_perso_final WHERE id_perso='$id_perso'";
					$mysqli->query($sql);
					
					// Attaques arme CaC
					// Recuperation caracs de l'arme CaC equipé
					$sql = "SELECT nom_arme, coutPa_arme, degatMin_arme, valeur_des_arme, precision_arme FROM arme, perso, perso_as_arme 
							WHERE perso_as_arme.id_perso = perso.id_perso 
							AND perso_as_arme.id_arme = arme.id_arme
							AND perso_as_arme.est_portee = '1' 
							AND arme.porteeMax_arme = '1'
							AND perso.id_perso = '$id_perso'";
					$res = $mysqli->query($sql);
					$t_arme = $res->fetch_assoc();
					
					$nom_arme 			= $t_arme['nom_arme'];
					$degats_arme 		= $t_arme['degatMin_arme'];
					$valeur_des_arme	= $t_arme['valeur_des_arme'];
					$precision_arme 	= $t_arme['precision_arme'];
					$coutPa_arme		= $t_arme['coutPa_arme'];
					
					if ($idPerso_carte >= 200000) {
						// PNJ
						
						// Récupération des infos de la cible
						$sql = "SELECT nom_pnj, pv_i, bonus_i FROM perso WHERE id_perso = '$idPerso_carte'";
						$res = $mysqli->query($sql);
						$t_cible = $res->fetch_assoc();
						
						$nom_cible 		= $t_cible['nom_pnj'];
						$pv_cible		= $t_cible['pv_i'];
						$bonus_cible	= $t_cible['bonus_i'];
						$protec_cible	= 0;
						
						$gain_pc = 0;
						
					} else {
					
						// Récupération des infos de la cible
						$sql = "SELECT nom_perso, x_perso, y_perso, pv_perso, xp_perso, or_perso, protec_perso, bonus_perso, idJoueur_perso, clan, id_grade FROM perso, perso_as_grade 
								WHERE perso_as_grade.id_perso = perso.id_perso
								AND perso.id_perso = '$idPerso_carte'";
						$res = $mysqli->query($sql);
						$t_cible = $res->fetch_assoc();
						
						$nom_cible 			= $t_cible['nom_perso'];
						$x_cible			= $t_cible['x_perso'];
						$y_cible			= $t_cible['y_perso'];
						$pv_cible			= $t_cible['pv_perso'];
						$xp_cible 			= $t_cible['xp_perso'];
						$or_cible 			= $t_cible['or_perso'];
						$bonus_cible		= $t_cible['bonus_perso'];
						$protec_cible		= $t_cible['protec_perso'];
						$grade_cible		= $t_cible['id_grade'];
						$id_joueur_cible 	= $t_cible['idJoueur_perso'];
						$clan_cible 		= $t_cible['clan'];
						
						// Récupération de la couleur associée au clan de la cible
						$couleur_clan_cible = couleur_clan($clan_cible);
						
						// Vérifie si le joueur attaqué a coché l'envoi de mail
						$mail_info_joueur = verif_coche_mail($mysqli, $id_joueur_cible);
						
						if($mail_info_joueur){
							// Envoi du mail
							mail_attaque($mysqli, $nom_perso, $idPerso_carte);
						}
						
						// Si perso ou cible est une infanterie 
						// ou si grade perso >= grade cible - 1
						if ($grade_perso <= $grade_cible + 1 
								|| $grade_perso == 1 || $grade_perso == 101 || $grade_perso == 102 
								|| $grade_cible == 1 || $grade_cible == 101 || $grade_cible == 102) {
							
							$gain_pc = 2;
						} else {
							$gain_pc = 0;
						}
					
					}
					
					$nb_attaque = 0;
					$cible_alive = true;
					$cumul_degats = 0;
					
					// On attaque tant qu'il reste des PA
					while ($pa_perso >= $coutPa_arme && $cible_alive) {
						
						// MAJ des pa du perso
						$pa_perso = $pa_perso - $coutPa_arme;
						
						$sql = "UPDATE perso SET pa_perso = $pa_perso WHERE id_perso='$id_perso'";
						$mysqli->query($sql);
						
						// Est ce que le perso touche sa cible ?
						$touche = mt_rand(0, 100);
						$precision_final = $precision_arme + $bonus_cible;
						
						if ($touche <= $precision_final) {
							// Le perso touche sa cible
							
							// calcul des dégats
							$bonus_degats_charge = 30 - $nb_attaque*10;
							$degats = mt_rand($degats_arme, $degats_arme * $valeur_des_arme) - $protec_cible + $bonus_degats_charge;
							
							if ($touche <= 2) {
								// Coup critique ! Dégats et Gains PC X 2
								$degats = $degats * 2;
								$gain_pc = $gain_pc * 2;
							}
							
							$cumul_degats = $cumul_degats + $degats;
							
							// calcul gain experience
							if ($idPerso_carte >= 200000) {
								$gain_experience = mt_rand(1,4);
							} else {
							
								$calcul_des_xp = ($xp_cible - $xp_perso) / 10;
								if ($calcul_des_xp < 0) {
									$valeur_des_xp = 0;
								} else {
									$valeur_des_xp = mt_rand(1, $calcul_des_xp);
								}
								$gain_experience = ($degats / 2) + $valeur_des_xp;
								
								if ($gain_experience > 15) {
									$gain_experience = 15;
								}
							}
							
							// Maj cible
							// Ajout d'un malus de 2 au défenseur
							$sql = "UPDATE perso SET bonus_perso = bonus_perso - 2, pv_perso = pv_perso - $degats WHERE id_perso='$idPerso_carte'";
							$mysqli->query($sql);
							
							// evenement attaque
							$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>','a chargé ','$idPerso_carte','<font color=$couleur_clan_cible>$nom_cible</font>',': $degats degats',NOW(),'0')";
							$mysqli->query($sql);
							
							// Verification si cible morte							
							if ($pv_cible - $cumul_degats <= 0) {
								$cible_alive = false;
								
								// Perte or 
								$calcul_perte_or = floor(($or_cible * 30) / 100);
								$sql = "UPDATE perso SET or_perso = or_perso - $calcul_perte_or WHERE id_perso='$idPerso_carte'";
								
								// on l'efface de la carte
								$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_cible' AND y_carte='$y_cible'";
								$mysqli->query($sql);
								
								if ($calcul_perte_or > 0) {
									// TODO : On met de l'or sur la carte
									
								}
								
								// evenement perso capture
								$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>','a capturé','$idPerso_carte','<font color=$couleur_clan_cible>$nom_cible</font>','',NOW(),'0')";
								$mysqli->query($sql);
								
								// maj cv
								$sql = "INSERT INTO `cv` (IDActeur_cv, nomActeur_cv, IDCible_cv, nomCible_cv, date_cv) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>','$id_cible','<font color=$couleur_clan_cible>$nom_cible</font>',NOW())";
								$mysqli->query($sql);
								
								// maj stats du perso
								$sql = "UPDATE perso SET nb_kill=nb_kill+1 WHERE id_perso=$id_perso";
								$mysqli->query($sql);
								
								// maj stats de la cible
								$sql = "UPDATE perso SET nb_mort=nb_mort+1 WHERE id_perso='$idPerso_carte'";
								$mysqli->query($sql);
								
								// maj stats camp
								if($clan_cible != $clan_cible){
									$sql = "UPDATE stats_camp_kill SET nb_kill=nb_kill+1 WHERE id_camp=$clan_perso";
									$mysqli->query($sql);
								}
							}
							
							// MAJ xp/pi
							$sql = "UPDATE perso SET xp_perso = xp_perso + $gain_experience, pi_perso = pi_perso + $gain_experience WHERE id_perso='$id_perso'";
							$mysqli->query($sql);
							
							// recup id joueur perso
							$sql = "SELECT idJoueur_perso FROM perso WHERE id_perso='$id_perso'";
							$res = $mysqli->query($sql);
							$t_j = $res->fetch_assoc();
							
							$id_j_perso = $t_j["idJoueur_perso"];
							
							// mise à jour des PC du chef
							$sql = "SELECT perso.id_perso, pc_perso, id_grade FROM perso, perso_as_grade WHERE perso.id_perso = perso_as_grade.id_perso AND idJoueur_perso='$id_j_perso' AND chef='1'";
							$res = $mysqli->query($sql);
							$t_chef = $res->fetch_assoc();
							
							$id_perso_chef = $t_chef["id_perso"];
							$pc_perso_chef = $t_chef["pc_perso"];
							$id_grade_chef = $t_chef["id_grade"];
							
							$sql = "UPDATE perso SET pc_perso = pc_perso + $gain_pc WHERE id_perso='$id_perso_chef'";
							$mysqli->query($sql);
							
							$pc_perso_chef_final = $pc_perso_chef + $gain_pc;
							
							// Verification passage de grade 
							$sql = "SELECT id_grade, nom_grade FROM grades WHERE pc_grade <= $pc_perso_chef_final AND pc_grade != 0 ORDER BY id_grade DESC LIMIT 1";
							$res = $mysqli->query($sql);
							$t_grade = $res->fetch_assoc();
							
							$id_grade_final 	= $t_grade["id_grade"];
							$nom_grade_final	= $t_grade["nom_grade"];
							
							if ($id_grade_chef < $id_grade_final) {
								
								// Passage de grade								
								$sql = "UPDATE perso_as_grade SET id_grade='$id_grade_final' WHERE id_perso='$id_perso_chef'";
								$mysqli->query($sql);
								
								echo "<br /><b>Votre chef de bataillon est passé au grade de $nom_grade_final</b><br />";
								
							}
							
						} else {
							// Le perso rate sa cible
							
							if ($touche >= 98) {
								// Echec critique !
								// Ajout d'un malus supplémentaire à l'attaquant
								$sql = "UPDATE perso SET bonus_perso = bonus_perso - 1 WHERE id_perso='$id_perso'";
							} else {
								// Ajout d'un malus supplémentaire à la cible
								$sql = "UPDATE perso SET bonus_perso = bonus_perso - 1 WHERE id_perso='$idPerso_carte'";
							}
							$mysqli->query($sql);
							
							// evenement
							$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($idPerso_carte,'$nom_cible','a esquivé l\'attaque de','$id_perso','<font color=$couleur_clan_perso>$nom_perso</font>','',NOW(),'0')";
							$mysqli->query($sql);
						}
						
						if ($nb_attaque < 3) {
							$nb_attaque++;
						}
					}
				}
				
				break;
			}
		} else {
			if ($i == 5) {
				// On n'a rencontré aucune cible / obstacle 
				// Charge terminée sans effet à part le déplacement
				
				// Mise a jour carte 
				$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_perso' AND y_carte='$y_perso'";
				$mysqli->query($sql);
				
				// Mise à jour position perso sur carte
				$x_perso_final = $x_perso + 4;
				$y_perso_final = $y_perso - 4;
				$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte='$id_perso', image_carte='$image_perso' WHERE x_carte = $x_perso_final AND y_carte = $y_perso_final";
				$mysqli->query($sql);
				
				// Mise à jour du perso
				$sql = "UPDATE perso SET pm_perso = pm_perso - 4, x_perso = $x_perso_final, y_perso = $y_perso_final WHERE id_perso='$id_perso'";
				$mysqli->query($sql);
				
				// evenement
				$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>',' a chargé face au vent... ',NULL,'','jusqu\'en $x_perso_final / $y_perso_final',NOW(),'0')";
				$mysqli->query($sql);
			}
		}
	}
}

/**
 * Fonction de charge vers la direction droite
 * x + 1
 */
function charge_droite($mysqli, $id_perso, $nom_perso, $x_perso, $y_perso, $pa_perso, $pv_perso, $xp_perso, $image_perso, $clan, $grade_perso) {
		
	$couleur_clan_perso = couleur_clan($clan);
	
	for ($i = 1; $i <= 5; $i++) {
					
		$sql = "SELECT x_carte, y_carte, fond_carte, occupee_carte, idPerso_carte FROM carte WHERE x_carte = $x_perso + $i AND y_carte = $y_perso";
		$res = $mysqli->query($sql);
		$t_charge = $res->fetch_assoc();
		
		$x_carte 		= $t_charge['x_carte'];
		$y_carte		= $t_charge['y_carte'];
		$fond_carte 	= $t_charge['fond_carte'];
		$occupee_carte	= $t_charge['occupee_carte'];
		$idPerso_carte	= $t_charge['idPerso_carte'];
		
		if ($occupee_carte || $fond_carte != '1.gif') {
			
			// Charge terminée
			
			if ($i <= 3) {
				
				// Mise à jour position perso sur carte
				$nb_deplacements = $i - 1;
				$x_perso_final = $x_perso + $nb_deplacements;
				$y_perso_final = $y_perso;
				
				// MAJ perso sur carte
				$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_perso' AND y_carte='$y_perso'";
				$mysqli->query($sql);
				
				$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte='$id_perso', image_carte='$image_perso' WHERE x_carte = $x_perso_final AND y_carte = $y_perso_final";
				$mysqli->query($sql);
					
				// Mise à jour du perso
				$sql = "UPDATE perso SET pm_perso = pm_perso - $nb_deplacements, x_perso = $x_perso_final, y_perso = $y_perso_final WHERE id_perso='$id_perso'";
				$mysqli->query($sql);
				
				// Charge incomplete => pas d'attaques
				$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>',' a rencontré un obstacle ne lui permettant pas de terminer sa charge ',NULL,'','',NOW(),'0')";
				$mysqli->query($sql);
				
				break;
				
			} else {
				
				// Mise a jour carte 
				$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_perso' AND y_carte='$y_perso'";
				$mysqli->query($sql);
					
				// Charge complète mais cible pas sur plaine => pas d'attaques
				if ($fond_carte != '1.gif') {
					
					// Mise à jour position perso sur carte
					$nb_deplacements = $i - 1;
					$x_perso_final = $x_perso + $nb_deplacements;
					$y_perso_final = $y_perso;
					$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte='$id_perso', image_carte='$image_perso' WHERE x_carte = $x_perso_final AND y_carte = $y_perso_final";
					$mysqli->query($sql);
					
					// Mise à jour du perso
					$sql = "UPDATE perso SET pm_perso = pm_perso - $nb_deplacements, x_perso = $x_perso_final, y_perso = $y_perso_final WHERE id_perso='$id_perso'";
					$mysqli->query($sql);
					
					// cible pas sur plaine => charge ratée
					$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>',' a raté sa charge, le terrain étant impraticable! ',NULL,'','',NOW(),'0')";
					$mysqli->query($sql);
					
				} else if ($idPerso_carte >= 50000 && $idPerso_carte < 200000) {
					
					// Charge complète mais cible batiment => pas d'attaque et le perso perd 40PV
					
					// Mise à jour position perso sur carte
					$nb_deplacements = $i - 1;
					$x_perso_final = $x_perso + $nb_deplacements;
					$y_perso_final = $y_perso;
					$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte='$id_perso', image_carte='$image_perso' WHERE x_carte = $x_perso_final AND y_carte = $y_perso_final";
					$mysqli->query($sql);
					
					// Mise à jour du perso
					$sql = "UPDATE perso SET pv_perso = pv_perso - 40, pm_perso = pm_perso - $nb_deplacements, x_perso = $x_perso_final, y_perso = $y_perso_final WHERE id_perso='$id_perso'";
					$mysqli->query($sql);
					
					if ($pv_perso <= 40) {
						// Perso rapatrié
						
						$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>',' s\'est écrasé contre un batiment suite a sa charge et a perdu connaissance ! ',NULL,'','',NOW(),'0')";
						$mysqli->query($sql);
						
					} else {
					
						$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>',' s\'est écrasé contre un batiment suite a sa charge ! ',NULL,'','',NOW(),'0')";
						$mysqli->query($sql);
						
					}
					
				} else {
					// Charge compléte et réussi sur un autre joueur ou PNJ sur plaine
					// On utilise tous les PA dispo pour faire les attaques
					// +30 degats première attaque, +20 seconde, etc...
					
					// Mise à jour position perso sur carte
					$nb_deplacements = $i - 1;
					$x_perso_final = $x_perso + $nb_deplacements;
					$y_perso_final = $y_perso;
					$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte='$id_perso', image_carte='$image_perso' WHERE x_carte = $x_perso_final AND y_carte = $y_perso_final";
					$mysqli->query($sql);
					
					// Mise à jour du perso
					$sql = "UPDATE perso SET pm_perso = pm_perso - $nb_deplacements, x_perso = $x_perso_final, y_perso = $y_perso_final WHERE id_perso='$id_perso'";
					$mysqli->query($sql);
					
					// Attaques arme CaC
					// Recuperation caracs de l'arme CaC equipé
					$sql = "SELECT nom_arme, coutPa_arme, degatMin_arme, valeur_des_arme, precision_arme FROM arme, perso, perso_as_arme 
							WHERE perso_as_arme.id_perso = perso.id_perso 
							AND perso_as_arme.id_arme = arme.id_arme
							AND perso_as_arme.est_portee = '1' 
							AND arme.porteeMax_arme = '1'
							AND perso.id_perso = '$id_perso'";
					$res = $mysqli->query($sql);
					$t_arme = $res->fetch_assoc();
					
					$nom_arme 			= $t_arme['nom_arme'];
					$degats_arme 		= $t_arme['degatMin_arme'];
					$valeur_des_arme	= $t_arme['valeur_des_arme'];
					$precision_arme 	= $t_arme['precision_arme'];
					$coutPa_arme		= $t_arme['coutPa_arme'];
					
					if ($idPerso_carte >= 200000) {
						// PNJ
						
						// Récupération des infos de la cible
						$sql = "SELECT nom_pnj, pv_i, bonus_i FROM perso WHERE id_perso = '$idPerso_carte'";
						$res = $mysqli->query($sql);
						$t_cible = $res->fetch_assoc();
						
						$nom_cible 		= $t_cible['nom_pnj'];
						$pv_cible		= $t_cible['pv_i'];
						$bonus_cible	= $t_cible['bonus_i'];
						$protec_cible	= 0;
						
						$gain_pc = 0;
						
					} else {
					
						// Récupération des infos de la cible
						$sql = "SELECT nom_perso, x_perso, y_perso, pv_perso, xp_perso, or_perso, protec_perso, bonus_perso, idJoueur_perso, clan, id_grade FROM perso, perso_as_grade 
								WHERE perso_as_grade.id_perso = perso.id_perso
								AND perso.id_perso = '$idPerso_carte'";
						$res = $mysqli->query($sql);
						$t_cible = $res->fetch_assoc();
						
						$nom_cible 			= $t_cible['nom_perso'];
						$x_cible			= $t_cible['x_perso'];
						$y_cible			= $t_cible['y_perso'];
						$pv_cible			= $t_cible['pv_perso'];
						$xp_cible 			= $t_cible['xp_perso'];
						$or_cible 			= $t_cible['or_perso'];
						$bonus_cible		= $t_cible['bonus_perso'];
						$protec_cible		= $t_cible['protec_perso'];
						$grade_cible		= $t_cible['id_grade'];
						$id_joueur_cible 	= $t_cible['idJoueur_perso'];
						$clan_cible 		= $t_cible['clan'];
						
						// Récupération de la couleur associée au clan de la cible
						$couleur_clan_cible = couleur_clan($clan_cible);
						
						// Vérifie si le joueur attaqué a coché l'envoi de mail
						$mail_info_joueur = verif_coche_mail($mysqli, $id_joueur_cible);
						
						if($mail_info_joueur){
							// Envoi du mail
							mail_attaque($mysqli, $nom_perso, $idPerso_carte);
						}
						
						// Si perso ou cible est une infanterie 
						// ou si grade perso >= grade cible - 1
						if ($grade_perso <= $grade_cible + 1 
								|| $grade_perso == 1 || $grade_perso == 101 || $grade_perso == 102 
								|| $grade_cible == 1 || $grade_cible == 101 || $grade_cible == 102) {
							
							$gain_pc = 2;
						} else {
							$gain_pc = 0;
						}
					
					}
					
					$nb_attaque = 0;
					$cible_alive = true;
					$cumul_degats = 0;
					
					// On attaque tant qu'il reste des PA
					while ($pa_perso >= $coutPa_arme && $cible_alive) {
						
						// MAJ des pa du perso
						$pa_perso = $pa_perso - $coutPa_arme;
						
						$sql = "UPDATE perso SET pa_perso = $pa_perso WHERE id_perso='$id_perso'";
						$mysqli->query($sql);
						
						// Est ce que le perso touche sa cible ?
						$touche = mt_rand(0, 100);
						$precision_final = $precision_arme + $bonus_cible;
						
						if ($touche <= $precision_final) {
							// Le perso touche sa cible
							
							// calcul des dégats
							$bonus_degats_charge = 30 - $nb_attaque*10;
							$degats = mt_rand($degats_arme, $degats_arme * $valeur_des_arme) - $protec_cible + $bonus_degats_charge;
							
							if ($touche <= 2) {
								// Coup critique ! Dégats et Gains PC X 2
								$degats = $degats * 2;
								$gain_pc = $gain_pc * 2;
							}
							
							$cumul_degats = $cumul_degats + $degats;
							
							// calcul gain experience
							if ($idPerso_carte >= 200000) {
								$gain_experience = mt_rand(1,4);
							} else {
							
								$calcul_des_xp = ($xp_cible - $xp_perso) / 10;
								if ($calcul_des_xp < 0) {
									$valeur_des_xp = 0;
								} else {
									$valeur_des_xp = mt_rand(1, $calcul_des_xp);
								}
								$gain_experience = ($degats / 2) + $valeur_des_xp;
								
								if ($gain_experience > 15) {
									$gain_experience = 15;
								}
							}
							
							// Maj cible
							// Ajout d'un malus de 2 au défenseur
							$sql = "UPDATE perso SET bonus_perso = bonus_perso - 2, pv_perso = pv_perso - $degats WHERE id_perso='$idPerso_carte'";
							$mysqli->query($sql);
							
							// evenement attaque
							$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>','a chargé ','$idPerso_carte','<font color=$couleur_clan_cible>$nom_cible</font>',': $degats degats',NOW(),'0')";
							$mysqli->query($sql);
							
							// Verification si cible morte							
							if ($pv_cible - $cumul_degats <= 0) {
								$cible_alive = false;
								
								// Perte or 
								$calcul_perte_or = floor(($or_cible * 30) / 100);
								$sql = "UPDATE perso SET or_perso = or_perso - $calcul_perte_or WHERE id_perso='$idPerso_carte'";
								
								// on l'efface de la carte
								$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_cible' AND y_carte='$y_cible'";
								$mysqli->query($sql);
								
								if ($calcul_perte_or > 0) {
									// TODO : On met de l'or sur la carte
									
								}
								
								// evenement perso capture
								$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>','a capturé','$idPerso_carte','<font color=$couleur_clan_cible>$nom_cible</font>','',NOW(),'0')";
								$mysqli->query($sql);
								
								// maj cv
								$sql = "INSERT INTO `cv` (IDActeur_cv, nomActeur_cv, IDCible_cv, nomCible_cv, date_cv) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>','$id_cible','<font color=$couleur_clan_cible>$nom_cible</font>',NOW())";
								$mysqli->query($sql);
								
								// maj stats du perso
								$sql = "UPDATE perso SET nb_kill=nb_kill+1 WHERE id_perso=$id_perso";
								$mysqli->query($sql);
								
								// maj stats de la cible
								$sql = "UPDATE perso SET nb_mort=nb_mort+1 WHERE id_perso='$idPerso_carte'";
								$mysqli->query($sql);
								
								// maj stats camp
								if($clan_cible != $clan_cible){
									$sql = "UPDATE stats_camp_kill SET nb_kill=nb_kill+1 WHERE id_camp=$clan_perso";
									$mysqli->query($sql);
								}
							}
							
							// MAJ xp/pi
							$sql = "UPDATE perso SET xp_perso = xp_perso + $gain_experience, pi_perso = pi_perso + $gain_experience WHERE id_perso='$id_perso'";
							$mysqli->query($sql);
							
							// recup id joueur perso
							$sql = "SELECT idJoueur_perso FROM perso WHERE id_perso='$id_perso'";
							$res = $mysqli->query($sql);
							$t_j = $res->fetch_assoc();
							
							$id_j_perso = $t_j["idJoueur_perso"];
							
							// mise à jour des PC du chef
							$sql = "SELECT perso.id_perso, pc_perso, id_grade FROM perso, perso_as_grade WHERE perso.id_perso = perso_as_grade.id_perso AND idJoueur_perso='$id_j_perso' AND chef='1'";
							$res = $mysqli->query($sql);
							$t_chef = $res->fetch_assoc();
							
							$id_perso_chef = $t_chef["id_perso"];
							$pc_perso_chef = $t_chef["pc_perso"];
							$id_grade_chef = $t_chef["id_grade"];
							
							$sql = "UPDATE perso SET pc_perso = pc_perso + $gain_pc WHERE id_perso='$id_perso_chef'";
							$mysqli->query($sql);
							
							$pc_perso_chef_final = $pc_perso_chef + $gain_pc;
							
							// Verification passage de grade 
							$sql = "SELECT id_grade, nom_grade FROM grades WHERE pc_grade <= $pc_perso_chef_final AND pc_grade != 0 ORDER BY id_grade DESC LIMIT 1";
							$res = $mysqli->query($sql);
							$t_grade = $res->fetch_assoc();
							
							$id_grade_final 	= $t_grade["id_grade"];
							$nom_grade_final	= $t_grade["nom_grade"];
							
							if ($id_grade_chef < $id_grade_final) {
								
								// Passage de grade								
								$sql = "UPDATE perso_as_grade SET id_grade='$id_grade_final' WHERE id_perso='$id_perso_chef'";
								$mysqli->query($sql);
								
								echo "<br /><b>Votre chef de bataillon est passé au grade de $nom_grade_final</b><br />";
								
							}
							
						} else {
							// Le perso rate sa cible
							
							if ($touche >= 98) {
								// Echec critique !
								// Ajout d'un malus supplémentaire à l'attaquant
								$sql = "UPDATE perso SET bonus_perso = bonus_perso - 1 WHERE id_perso='$id_perso'";
							} else {
								// Ajout d'un malus supplémentaire à la cible
								$sql = "UPDATE perso SET bonus_perso = bonus_perso - 1 WHERE id_perso='$idPerso_carte'";
							}
							$mysqli->query($sql);
							
							// evenement
							$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($idPerso_carte,'$nom_cible','a esquivé l\'attaque de','$id_perso','<font color=$couleur_clan_perso>$nom_perso</font>','',NOW(),'0')";
							$mysqli->query($sql);
						}
						
						if ($nb_attaque < 3) {
							$nb_attaque++;
						}
					}
				}
				
				break;
			}
		} else {
			if ($i == 5) {
				// On n'a rencontré aucune cible / obstacle 
				// Charge terminée sans effet à part le déplacement
				
				// Mise a jour carte 
				$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_perso' AND y_carte='$y_perso'";
				$mysqli->query($sql);
				
				// Mise à jour position perso sur carte
				$x_perso_final = $x_perso + 4;
				$y_perso_final = $y_perso;
				$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte='$id_perso', image_carte='$image_perso' WHERE x_carte = $x_perso_final AND y_carte = $y_perso_final";
				$mysqli->query($sql);
				
				// Mise à jour du perso
				$sql = "UPDATE perso SET pm_perso = pm_perso - 4, x_perso = $x_perso_final, y_perso = $y_perso_final WHERE id_perso='$id_perso'";
				$mysqli->query($sql);
				
				// evenement
				$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>',' a chargé face au vent... ',NULL,'','jusqu\'en $x_perso_final / $y_perso_final',NOW(),'0')";
				$mysqli->query($sql);
			}
		}
	}
}

/**
 * Fonction de charge vers la direction haut droite
 * x + 1 et y + 1
 */
function charge_haut_droite($mysqli, $id_perso, $nom_perso, $x_perso, $y_perso, $pa_perso, $pv_perso, $xp_perso, $image_perso, $clan, $grade_perso) {
		
	$couleur_clan_perso = couleur_clan($clan);
	
	for ($i = 1; $i <= 5; $i++) {
					
		$sql = "SELECT x_carte, y_carte, fond_carte, occupee_carte, idPerso_carte FROM carte WHERE x_carte = $x_perso + $i AND y_carte = $y_perso + $i";
		$res = $mysqli->query($sql);
		$t_charge = $res->fetch_assoc();
		
		$x_carte 		= $t_charge['x_carte'];
		$y_carte		= $t_charge['y_carte'];
		$fond_carte 	= $t_charge['fond_carte'];
		$occupee_carte	= $t_charge['occupee_carte'];
		$idPerso_carte	= $t_charge['idPerso_carte'];
		
		if ($occupee_carte || $fond_carte != '1.gif') {
			
			// Charge terminée
			
			if ($i <= 3) {
				
				// Mise à jour position perso sur carte
				$nb_deplacements = $i - 1;
				$x_perso_final = $x_perso + $nb_deplacements;
				$y_perso_final = $y_perso + $nb_deplacements;
				
				// MAJ perso sur carte
				$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_perso' AND y_carte='$y_perso'";
				$mysqli->query($sql);
				
				$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte='$id_perso', image_carte='$image_perso' WHERE x_carte = $x_perso_final AND y_carte = $y_perso_final";
				$mysqli->query($sql);
					
				// Mise à jour du perso
				$sql = "UPDATE perso SET pm_perso = pm_perso - $nb_deplacements, x_perso = $x_perso_final, y_perso = $y_perso_final WHERE id_perso='$id_perso'";
				$mysqli->query($sql);
				
				// Charge incomplete => pas d'attaques
				$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>',' a rencontré un obstacle ne lui permettant pas de terminer sa charge ',NULL,'','',NOW(),'0')";
				$mysqli->query($sql);
				
				break;
				
			} else {
				
				// Mise a jour carte 
				$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_perso' AND y_carte='$y_perso'";
				$mysqli->query($sql);
					
				// Charge complète mais cible pas sur plaine => pas d'attaques
				if ($fond_carte != '1.gif') {
					
					// Mise à jour position perso sur carte
					$nb_deplacements = $i - 1;
					$x_perso_final = $x_perso + $nb_deplacements;
					$y_perso_final = $y_perso + $nb_deplacements;
					$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte='$id_perso', image_carte='$image_perso' WHERE x_carte = $x_perso_final AND y_carte = $y_perso_final";
					$mysqli->query($sql);
					
					// Mise à jour du perso
					$sql = "UPDATE perso SET pm_perso = pm_perso - $nb_deplacements, x_perso = $x_perso_final, y_perso = $y_perso_final WHERE id_perso='$id_perso'";
					$mysqli->query($sql);
					
					// cible pas sur plaine => charge ratée
					$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>',' a raté sa charge, le terrain étant impraticable! ',NULL,'','',NOW(),'0')";
					$mysqli->query($sql);
					
				} else if ($idPerso_carte >= 50000 && $idPerso_carte < 200000) {
					
					// Charge complète mais cible batiment => pas d'attaque et le perso perd 40PV
					
					// Mise à jour position perso sur carte
					$nb_deplacements = $i - 1;
					$x_perso_final = $x_perso;
					$y_perso_final = $y_perso + $nb_deplacements;
					$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte='$id_perso', image_carte='$image_perso' WHERE x_carte = $x_perso_final AND y_carte = $y_perso_final";
					$mysqli->query($sql);
					
					// Mise à jour du perso
					$sql = "UPDATE perso SET pv_perso = pv_perso - 40, pm_perso = pm_perso - $nb_deplacements, x_perso = $x_perso_final, y_perso = $y_perso_final WHERE id_perso='$id_perso'";
					$mysqli->query($sql);
					
					if ($pv_perso <= 40) {
						// Perso rapatrié
						
						$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>',' s\'est écrasé contre un batiment suite a sa charge et a perdu connaissance ! ',NULL,'','',NOW(),'0')";
						$mysqli->query($sql);
						
					} else {
					
						$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>',' s\'est écrasé contre un batiment suite a sa charge ! ',NULL,'','',NOW(),'0')";
						$mysqli->query($sql);
						
					}
					
				} else {
					// Charge compléte et réussi sur un autre joueur ou PNJ sur plaine
					// On utilise tous les PA dispo pour faire les attaques
					// +30 degats première attaque, +20 seconde, etc...
					
					// Mise à jour position perso sur carte
					$nb_deplacements = $i - 1;
					$x_perso_final = $x_perso + $nb_deplacements;
					$y_perso_final = $y_perso + $nb_deplacements;
					$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte='$id_perso', image_carte='$image_perso' WHERE x_carte = $x_perso_final AND y_carte = $y_perso_final";
					$mysqli->query($sql);
					
					// Mise à jour du perso
					$sql = "UPDATE perso SET pm_perso = pm_perso - $nb_deplacements, x_perso = $x_perso_final, y_perso = $y_perso_final WHERE id_perso='$id_perso'";
					$mysqli->query($sql);
					
					// Attaques arme CaC
					// Recuperation caracs de l'arme CaC equipé
					$sql = "SELECT nom_arme, coutPa_arme, degatMin_arme, valeur_des_arme, precision_arme FROM arme, perso, perso_as_arme 
							WHERE perso_as_arme.id_perso = perso.id_perso 
							AND perso_as_arme.id_arme = arme.id_arme
							AND perso_as_arme.est_portee = '1' 
							AND arme.porteeMax_arme = '1'
							AND perso.id_perso = '$id_perso'";
					$res = $mysqli->query($sql);
					$t_arme = $res->fetch_assoc();
					
					$nom_arme 			= $t_arme['nom_arme'];
					$degats_arme 		= $t_arme['degatMin_arme'];
					$valeur_des_arme	= $t_arme['valeur_des_arme'];
					$precision_arme 	= $t_arme['precision_arme'];
					$coutPa_arme		= $t_arme['coutPa_arme'];
					
					if ($idPerso_carte >= 200000) {
						// PNJ
						
						// Récupération des infos de la cible
						$sql = "SELECT nom_pnj, pv_i, bonus_i FROM perso WHERE id_perso = '$idPerso_carte'";
						$res = $mysqli->query($sql);
						$t_cible = $res->fetch_assoc();
						
						$nom_cible 		= $t_cible['nom_pnj'];
						$pv_cible		= $t_cible['pv_i'];
						$bonus_cible	= $t_cible['bonus_i'];
						$protec_cible	= 0;
						
						$gain_pc = 0;
						
					} else {
					
						// Récupération des infos de la cible
						$sql = "SELECT nom_perso, x_perso, y_perso, pv_perso, xp_perso, or_perso, protec_perso, bonus_perso, idJoueur_perso, clan, id_grade FROM perso, perso_as_grade 
								WHERE perso_as_grade.id_perso = perso.id_perso
								AND perso.id_perso = '$idPerso_carte'";
						$res = $mysqli->query($sql);
						$t_cible = $res->fetch_assoc();
						
						$nom_cible 			= $t_cible['nom_perso'];
						$x_cible			= $t_cible['x_perso'];
						$y_cible			= $t_cible['y_perso'];
						$pv_cible			= $t_cible['pv_perso'];
						$xp_cible 			= $t_cible['xp_perso'];
						$or_cible 			= $t_cible['or_perso'];
						$bonus_cible		= $t_cible['bonus_perso'];
						$protec_cible		= $t_cible['protec_perso'];
						$grade_cible		= $t_cible['id_grade'];
						$id_joueur_cible 	= $t_cible['idJoueur_perso'];
						$clan_cible 		= $t_cible['clan'];
						
						// Récupération de la couleur associée au clan de la cible
						$couleur_clan_cible = couleur_clan($clan_cible);
						
						// Vérifie si le joueur attaqué a coché l'envoi de mail
						$mail_info_joueur = verif_coche_mail($mysqli, $id_joueur_cible);
						
						if($mail_info_joueur){
							// Envoi du mail
							mail_attaque($mysqli, $nom_perso, $idPerso_carte);
						}
						
						// Si perso ou cible est une infanterie 
						// ou si grade perso >= grade cible - 1
						if ($grade_perso <= $grade_cible + 1 
								|| $grade_perso == 1 || $grade_perso == 101 || $grade_perso == 102 
								|| $grade_cible == 1 || $grade_cible == 101 || $grade_cible == 102) {
							
							$gain_pc = 2;
						} else {
							$gain_pc = 0;
						}
					
					}
					
					$nb_attaque = 0;
					$cible_alive = true;
					$cumul_degats = 0;
					
					// On attaque tant qu'il reste des PA
					while ($pa_perso >= $coutPa_arme && $cible_alive) {
						
						// MAJ des pa du perso
						$pa_perso = $pa_perso - $coutPa_arme;
						
						$sql = "UPDATE perso SET pa_perso = $pa_perso WHERE id_perso='$id_perso'";
						$mysqli->query($sql);
						
						// Est ce que le perso touche sa cible ?
						$touche = mt_rand(0, 100);
						$precision_final = $precision_arme - $bonus_cible;
						
						if ($touche <= $precision_final) {
							// Le perso touche sa cible
							
							// calcul des dégats
							$bonus_degats_charge = 30 - $nb_attaque*10;
							$degats = mt_rand($degats_arme, $degats_arme * $valeur_des_arme) - $protec_cible + $bonus_degats_charge;
							
							if($degats < 0) {
								$degats = 0;
							}
							
							if ($touche <= 2) {
								// Coup critique ! Dégats et Gains PC X 2
								$degats = $degats * 2;
								$gain_pc = $gain_pc * 2;
							}
							
							$cumul_degats = $cumul_degats + $degats;
							
							// calcul gain experience
							if ($idPerso_carte >= 200000) {
								$gain_experience = mt_rand(1,4);
							} else {
							
								$calcul_des_xp = ($xp_cible - $xp_perso) / 10;
								if ($calcul_des_xp < 0) {
									$valeur_des_xp = 0;
								} else {
									$valeur_des_xp = mt_rand(0, $calcul_des_xp);
								}
								$gain_experience = ceil(($degats / 20) + $valeur_des_xp);
								
								if ($gain_experience > 15) {
									$gain_experience = 15;
								}
							}
							
							// Maj cible
							// Ajout d'un malus de 2 au défenseur
							$sql = "UPDATE perso SET bonus_perso = bonus_perso - 2, pv_perso = pv_perso - $degats WHERE id_perso='$idPerso_carte'";
							$mysqli->query($sql);
							
							// evenement attaque
							$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>','a chargé ','$idPerso_carte','<font color=$couleur_clan_cible>$nom_cible</font>',': $degats degats',NOW(),'0')";
							$mysqli->query($sql);
							
							// Verification si cible morte							
							if ($pv_cible - $cumul_degats <= 0) {
								$cible_alive = false;
								
								// Perte or 
								$calcul_perte_or = floor(($or_cible * 30) / 100);
								$sql = "UPDATE perso SET or_perso = or_perso - $calcul_perte_or WHERE id_perso='$idPerso_carte'";
								
								// on l'efface de la carte
								$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_cible' AND y_carte='$y_cible'";
								$mysqli->query($sql);
								
								if ($calcul_perte_or > 0) {
									// TODO : On met de l'or sur la carte
									
								}
								
								// evenement perso capture
								$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>','a capturé','$idPerso_carte','<font color=$couleur_clan_cible>$nom_cible</font>','',NOW(),'0')";
								$mysqli->query($sql);
								
								// maj cv
								$sql = "INSERT INTO `cv` (IDActeur_cv, nomActeur_cv, IDCible_cv, nomCible_cv, date_cv) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>','$id_cible','<font color=$couleur_clan_cible>$nom_cible</font>',NOW())";
								$mysqli->query($sql);
								
								// maj stats du perso
								$sql = "UPDATE perso SET nb_kill=nb_kill+1 WHERE id_perso=$id_perso";
								$mysqli->query($sql);
								
								// maj stats de la cible
								$sql = "UPDATE perso SET nb_mort=nb_mort+1 WHERE id_perso='$idPerso_carte'";
								$mysqli->query($sql);
								
								// maj stats camp
								if($clan_cible != $clan_cible){
									$sql = "UPDATE stats_camp_kill SET nb_kill=nb_kill+1 WHERE id_camp=$clan_perso";
									$mysqli->query($sql);
								}
							}
							
							// MAJ xp/pi
							$sql = "UPDATE perso SET xp_perso = xp_perso + $gain_experience, pi_perso = pi_perso + $gain_experience WHERE id_perso='$id_perso'";
							$mysqli->query($sql);
							
							// recup id joueur perso
							$sql = "SELECT idJoueur_perso FROM perso WHERE id_perso='$id_perso'";
							$res = $mysqli->query($sql);
							$t_j = $res->fetch_assoc();
							
							$id_j_perso = $t_j["idJoueur_perso"];
							
							// mise à jour des PC du chef
							$sql = "SELECT perso.id_perso, pc_perso, id_grade FROM perso, perso_as_grade WHERE perso.id_perso = perso_as_grade.id_perso AND idJoueur_perso='$id_j_perso' AND chef='1'";
							$res = $mysqli->query($sql);
							$t_chef = $res->fetch_assoc();
							
							$id_perso_chef = $t_chef["id_perso"];
							$pc_perso_chef = $t_chef["pc_perso"];
							$id_grade_chef = $t_chef["id_grade"];
							
							$sql = "UPDATE perso SET pc_perso = pc_perso + $gain_pc WHERE id_perso='$id_perso_chef'";
							$mysqli->query($sql);
							
							$pc_perso_chef_final = $pc_perso_chef + $gain_pc;
							
							// Verification passage de grade 
							$sql = "SELECT id_grade, nom_grade FROM grades WHERE pc_grade <= $pc_perso_chef_final AND pc_grade != 0 ORDER BY id_grade DESC LIMIT 1";
							$res = $mysqli->query($sql);
							$t_grade = $res->fetch_assoc();
							
							$id_grade_final 	= $t_grade["id_grade"];
							$nom_grade_final	= $t_grade["nom_grade"];
							
							if ($id_grade_chef < $id_grade_final) {
								
								// Passage de grade								
								$sql = "UPDATE perso_as_grade SET id_grade='$id_grade_final' WHERE id_perso='$id_perso_chef'";
								$mysqli->query($sql);
								
								echo "<br /><b>Votre chef de bataillon est passé au grade de $nom_grade_final</b><br />";
								
							}
							
						} else {
							// Le perso rate sa cible
							
							if ($touche >= 98) {
								// Echec critique !
								// Ajout d'un malus supplémentaire à l'attaquant
								$sql = "UPDATE perso SET bonus_perso = bonus_perso - 1 WHERE id_perso='$id_perso'";
							} else {
								// Ajout d'un malus supplémentaire à la cible
								$sql = "UPDATE perso SET bonus_perso = bonus_perso - 1 WHERE id_perso='$idPerso_carte'";
							}
							$mysqli->query($sql);
							
							// evenement
							$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($idPerso_carte,'$nom_cible','a esquivé l\'attaque de','$id_perso','<font color=$couleur_clan_perso>$nom_perso</font>','',NOW(),'0')";
							$mysqli->query($sql);
						}
						
						if ($nb_attaque < 3) {
							$nb_attaque++;
						}
					}
				}
				
				break;
			}
		} else {
			if ($i == 5) {
				// On n'a rencontré aucune cible / obstacle 
				// Charge terminée sans effet à part le déplacement
				
				// Mise a jour carte 
				$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_perso' AND y_carte='$y_perso'";
				$mysqli->query($sql);
				
				// Mise à jour position perso sur carte
				$x_perso_final = $x_perso + 4;
				$y_perso_final = $y_perso + 4;
				$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte='$id_perso', image_carte='$image_perso' WHERE x_carte = $x_perso_final AND y_carte = $y_perso_final";
				$mysqli->query($sql);
				
				// Mise à jour du perso
				$sql = "UPDATE perso SET pm_perso = pm_perso - 4, x_perso = $x_perso_final, y_perso = $y_perso_final WHERE id_perso='$id_perso'";
				$mysqli->query($sql);
				
				// evenement
				$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>',' a chargé face au vent... ',NULL,'','jusqu\'en $x_perso_final / $y_perso_final',NOW(),'0')";
				$mysqli->query($sql);
			}
		}
	}
}
?>