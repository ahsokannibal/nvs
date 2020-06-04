<?php 
@session_start();  
require_once("fonctions.php");
require_once("jeu/f_carte.php");
	
$mysqli = db_connexion();

include ('nb_online.php');

if(isset($_SESSION["ID_joueur"])){
	
	$id_joueur = $_SESSION['ID_joueur'];
	
	// recuperation de l'id et du nom du chef
	$sql = "SELECT id_perso, nom_perso, pv_perso, x_perso, y_perso, clan, image_perso, convalescence, est_gele, UNIX_TIMESTAMP(DLA_perso) as DLA, UNIX_TIMESTAMP(date_gele) as DG FROM perso WHERE idJoueur_perso=$id_joueur AND chef=1";
	$res = $mysqli->query($sql);
	$t_chef = $res->fetch_assoc();
	
	$id 		= $t_chef["id_perso"];
	$dla 		= $t_chef["DLA"];
	$clan 		= $t_chef["clan"];
	$est_gele 	= $t_chef["est_gele"];
	$date_gele 	= $t_chef["DG"];
	$pseudo 	= $t_chef["nom_perso"];
	$pv			= $t_chef["pv_perso"];
	$image_perso= $t_chef["image_perso"];
	$x_perso	= $t_chef["x_perso"];
	$y_perso	= $t_chef["y_perso"];
	
	// Récupération de la couleur associée au clan du perso
	if($clan == '1'){
		$couleur_clan_p = 'blue';
	}
	if($clan == '2'){
		$couleur_clan_p = 'red';
	}
	
	if (isset($_SESSION["id_perso"]) && $_SESSION["id_perso"] != $id) {
		// on a switch sur un perso mort ou nouveau tour ?
		
		$id_perso = $_SESSION["id_perso"];
		
		// Ce perso existe t-il toujours ?
		$sql = "SELECT count(id_perso) as nb_perso FROM perso WHERE id_perso = '$id_perso'";
		$res = $mysqli->query($sql);
		$tab = $res->fetch_assoc();
		
		$nb_perso = $tab["nb_perso"];
		
		if ($nb_perso == 1) {
			
			// recuperation des infos du perso
			$sql = "SELECT idJoueur_perso, nom_perso, x_perso, y_perso, pv_perso, pvMax_perso, recup_perso, bonusRecup_perso, image_perso, chef, convalescence FROM perso WHERE id_perso='$id_perso'";
			$res = $mysqli->query($sql);
			$t_perso = $res->fetch_assoc();
			
			$id_joueur_perso 	= $t_perso["idJoueur_perso"];
			$nom_perso			= $t_perso["nom_perso"];
			$x_perso			= $t_perso["x_perso"];
			$y_perso			= $t_perso["y_perso"];
			$pv_perso			= $t_perso["pv_perso"];
			$pvMax_perso		= $t_perso["pvMax_perso"];
			$recup_perso		= $t_perso["recup_perso"] + $t_perso["bonusRecup_perso"];
			$image_perso		= $t_perso["image_perso"];
			$convalescent_perso	= $t_perso["convalescence"];
			$chef_perso 		= $t_perso["chef"];
			
			// Le perso appartient-il bien au joueur ?
			if ($id_joueur_perso == $id_joueur) {
					
				$date = time();
				
				if (nouveau_tour($date, $dla)) {
					
					// calcul du prochain tour
					$new_dla = $date + DUREE_TOUR;
					
					nouveau_tour_joueur($mysqli, $id_joueur, $new_dla, $clan, $couleur_clan_p);
					
					//redirection
					header("location:jeu/jouer.php");
					
				} else {
					
					// Le perso est mort
					if ($pv_perso <= 0) {
					
						respawn_perso($mysqli, $id_perso, $nom_perso, $x_perso, $y_perso, $image_perso, $clan, $couleur_clan_p, $chef_perso);
						
						//redirection
						header("location:jeu/jouer.php");
					}
					else {
						// Le perso est vivant et ce n'est pas un nouveau tour
						
						//redirection
						header("location:jeu/jouer.php");
					}
				}
				
			} else {
				
				// Tentative de triche !
				$text_triche = "Le joueur $id_joueur a essayé de prendre controle du perso $id_perso qui ne lui appartient pas !";
			
				$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id_perso', '$text_triche')";
				$mysqli->query($sql);
				
				$_SESSION = array(); // On écrase le tableau de session
				session_destroy(); // On détruit la session
				
				//redirection
				header("location:index.php");
			}
		} else {
			// Le perso a été supprimé / renvoyé
			// On se remet sur le chef
			$_SESSION["id_perso"] = $id;
			
			//redirection
			header("location:jeu/jouer.php");
		}
	} else {
	
		$_SESSION["id_perso"] 	= $id;
		$_SESSION["nom_perso"] 	= $pseudo;

		$date = time();
		
		// Perso gele et il peut se degeler
		if($est_gele && temp_degele($date, $date_gele)){
			
			// Récupération de tous les perso du joueur
			$sql = "SELECT id_perso, nom_perso, x_perso, y_perso FROM perso WHERE idJoueur_perso='$id_joueur'";
			$res = $mysqli->query($sql);
			
			while ($t_persos = $res->fetch_assoc()) {
				
				$id_perso_degele 	= $t_persos["id_perso"];
				$nom_perso_degele	= $t_persos["nom_perso"];
				$x_perso_degele		= $t_persos["x_perso"];
				$y_perso_degele		= $t_persos["y_perso"];
				
				// degele du perso
				$sql = "UPDATE perso SET est_gele='0', date_gele=NULL, a_gele='0' WHERE id_perso='$id_perso_degele'";
				$mysqli->query($sql);
				
				// Récupération du batiment de rappatriement le plus proche du perso
				$id_instance_bat = selection_bat_rapat($mysqli, $id_perso_degele, $x_perso_degele, $y_perso_degele, $clan);
				
				if ($id_instance_bat != null && $id_instance_bat != 0) {
				
					// récupération coordonnées batiment
					$sql_b = "SELECT x_instance, y_instance, id_batiment FROM instance_batiment WHERE id_instanceBat='$id_instance_bat'";
					$res_b = $mysqli->query($sql_b);
					$t_b = $res_b->fetch_assoc();
					
					$x 		= $t_b['x_instance'];
					$y 		= $t_b['y_instance'];
					$id_bat	= $t_b['id_batiment'];
					
					// On met le perso et ses grouillots dans le batiment
					$sql = "INSERT INTO perso_in_batiment VALUES('$id_perso_degele','$id_instance_bat')";
					$mysqli->query($sql);
					
					// calcul bonus perception perso
					$bonus_visu = getBonusObjet($mysqli, $id_perso);
					
					// MAJ perso
					$sql = "UPDATE perso SET x_perso='$x', y_perso='$y', pm_perso=pmMax_perso+bonusPM_perso, pa_perso=paMax_perso+bonusPA_perso, pv_perso=pvMax_perso, bonusPerception_perso=$bonus_visu, bourre_perso=0, bonus_perso=0 WHERE id_perso='$id_perso_degele'";
					$mysqli->query($sql);
					
					// mise a jour des evenements
					$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ('$id_perso_degele','<font color=$couleur_clan_p><b>$nom_perso_degele</b></font>','est de retour de permission',NULL,'','dans le bâtiment $id_instance_bat en $x/$y',NOW(),'0')";
					$mysqli->query($sql);
					
					// Rapat Chef dans Fort ou Fortin
					if ($chef_perso && ($id_bat == 8 || $id_bat == 9)) {
						
						// recup grade / pc chef
						$sql = "SELECT perso.id_perso, pc_perso, id_grade FROM perso, perso_as_grade WHERE perso.id_perso = perso_as_grade.id_perso AND perso.id_perso='$id_perso'";
						$res = $mysqli->query($sql);
						$t_chef = $res->fetch_assoc();
						
						$id_perso_chef = $t_chef["id_perso"];
						$pc_perso_chef = $t_chef["pc_perso"];
						$id_grade_chef = $t_chef["id_grade"];
						
						// Verification passage de grade 
						$sql = "SELECT id_grade, nom_grade FROM grades WHERE pc_grade <= $pc_perso_chef AND pc_grade != 0 ORDER BY id_grade DESC LIMIT 1";
						$res = $mysqli->query($sql);
						$t_grade = $res->fetch_assoc();
						
						$id_grade_final 	= $t_grade["id_grade"];
						$nom_grade_final	= $t_grade["nom_grade"];
						
						if ($id_grade_chef < $id_grade_final) {
								
							// Passage de grade								
							$sql = "UPDATE perso_as_grade SET id_grade='$id_grade_final' WHERE id_perso='$id_perso'";
							$mysqli->query($sql);
							
							// mise a jour des evenements
							$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ('$id_perso','<font color=$couleur_clan_p><b>$nom_perso</b></font>','a été promu <b>$nom_grade_final</b> !',NULL,'','',NOW(),'0')";
							$mysqli->query($sql);
							
						}
					}
				}
				else {
					echo "<center>Aucun batiment disponible pour le dégèle de votre perso</center>";
				}
			}
			
			//redirection
			header("location:jeu/jouer.php"); 
		}
		else {
			
			if($est_gele && !temp_degele($date, $date_gele)){
				
				$tr = temp_restant($date, $date_gele);
				$jours = floor ($tr/(3600*24));
				$heures = floor (($tr%(3600*24))/3600);
				$min = floor ((($tr%(3600*24))%3600)/60);
				$sec = (((($tr%(3600*24))%3600)%60));
				
				echo "Vous devez attendre $jours jours, $heures heures, $min minutes et $sec secondes encore avant de pouvoir vous degeler<br /><br />";
				echo "<a href=\"logout.php\">[ retour ]</a>";
			}
			else {
				
				//c'est un nouveau tour et le perso n'est pas gele
				if (!$est_gele && nouveau_tour($date, $dla)) {
					
					// calcul du prochain tour
					$new_dla = $date + DUREE_TOUR;					
					
					nouveau_tour_joueur($mysqli, $id_joueur, $new_dla, $clan, $couleur_clan_p);
					
					//redirection
					header("location:jeu/jouer.php");
				}
				else {
					
					if ($pv > 0) {
						// il est encore en vie et pas nouveau tour
						// redirection
						header("location:jeu/jouer.php");
					}
					else {
						// Il est mort et ce n'est pas un nouveau tour
						
						// recuperation des infos du perso
						$sql = "SELECT nom_perso, x_perso, y_perso, image_perso, chef FROM perso WHERE id_perso='$id'";
						$res = $mysqli->query($sql);
						$t_perso = $res->fetch_assoc();
						
						$nom_perso			= $t_perso["nom_perso"];
						$x_perso			= $t_perso["x_perso"];
						$y_perso			= $t_perso["y_perso"];
						$image_perso		= $t_perso["image_perso"];
						$chef_perso 		= $t_perso["chef"];
						
						respawn_perso($mysqli, $id, $nom_perso, $x_perso, $y_perso, $image_perso, $clan, $couleur_clan_p, $chef_perso);
						
						// redirection
						header("location:jeu/jouer.php");
					}
				}
			}
		}
	}
}
else{
	echo "<font color=red>Vous ne pouvez pas acceder a cette page, veuillez vous logguer.</font>";
}

/**
 * Fonction permettant de gérer un nouveau tour
 *
 */
function nouveau_tour_joueur($mysqli, $id_joueur, $new_dla, $clan, $couleur_clan_p) {
	
	// Récupération de tous les perso du joueur
	$sql = "SELECT id_perso, nom_perso, x_perso, y_perso, pm_perso, pmMax_perso, paMax_perso, pv_perso, pvMax_perso, recup_perso, bonusRecup_perso, bonus_perso, bonusPM_perso, image_perso, type_perso, chef, genie, convalescence 
			FROM perso WHERE idJoueur_perso='$id_joueur'";
	$res = $mysqli->query($sql);
	
	while ($t_persos = $res->fetch_assoc()) {

		$id_perso_nouveau_tour 		= $t_persos["id_perso"];
		$nom_perso_nouveau_tour		= $t_persos["nom_perso"];
		$pv_perso_nouveau_tour 		= $t_persos["pv_perso"];
		$pv_max_perso_nouveau_tour	= $t_persos["pvMax_perso"];
		$recup_perso_nouveau_tour 	= $t_persos["recup_perso"] + $t_persos["bonusRecup_perso"];
		$x_perso_nouveau_tour		= $t_persos["x_perso"];
		$y_perso_nouveau_tour		= $t_persos["y_perso"];
		$chef_perso_nouveau_tour	= $t_persos["chef"];
		$image_perso_nouveau_tour	= $t_persos["image_perso"];
		$bonus_perso_nouveau_tour	= $t_persos["bonus_perso"];
		$type_perso_nouveau_tour	= $t_persos["type_perso"];
		$pm_perso_nouveau_tour		= $t_persos["pm_perso"];
		$pm_max_perso_nouveau_tour	= $t_persos["pmMax_perso"];
		$pa_max_perso_nouveau_tour	= $t_persos["paMax_perso"];
		$bonusPM_nouveau_tour 		= $t_persos["bonusPM_perso"];
		$genie_nouveau_tour 		= $t_persos["genie"];
		$convalescence_nouveau_tour	= $t_persos["convalescence"];
		
		// Calcul bonus perso
		$new_bonus_perso = 0;
		if ($bonus_perso_nouveau_tour + 5 <= 0) {
			$new_bonus_perso = $bonus_perso_nouveau_tour + 5;
		}
		
		// Calcul gains Or / PC
		if ($chef_perso_nouveau_tour == '1') {
				
			$gain_or = 3;
			$gain_pc = 1;
				
		} else {
			
			$gain_or = gain_or_grouillot($type_perso_nouveau_tour);
			$gain_pc = 0;
		}
		
		// Le perso est mort
		if ($pv_perso_nouveau_tour <= 0) {
			
			// ---------------------- //
			//    RESPAWN BATIMENT    //
			// ---------------------- //
						
			// Récupération du batiment de rappatriement le plus proche du perso
			$id_instance_bat = selection_bat_rapat($mysqli, $id_perso_nouveau_tour, $x_perso_nouveau_tour, $y_perso_nouveau_tour, $clan);
			
			// Batiment trouvé
			if ($id_instance_bat != null && $id_instance_bat != 0) {
				
				// récupération coordonnées batiment
				$sql_b = "SELECT x_instance, y_instance, id_batiment FROM instance_batiment WHERE id_instanceBat='$id_instance_bat'";
				$res_b = $mysqli->query($sql_b);
				$t_b = $res_b->fetch_assoc();
				
				$x 		= $t_b['x_instance'];
				$y 		= $t_b['y_instance'];
				$id_bat	= $t_b['id_batiment'];
				
				// On met le perso dans le batiment
				$sql = "INSERT INTO perso_in_batiment VALUES('$id_perso_nouveau_tour','$id_instance_bat')";
				$mysqli->query($sql);
				
				// mise a jour des evenements
				$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) 
						VALUES ('$id_perso_nouveau_tour','<font color=$couleur_clan_p><b>$nom_perso_nouveau_tour</b></font>','a été rapatrié',NULL,'','dans le bâtiment $id_instance_bat en $x/$y',NOW(),'0')";
				$mysqli->query($sql);
				
				// Rapat Chef dans Fort ou Fortin
				if ($chef_perso_nouveau_tour && ($id_bat == 8 || $id_bat == 9)) {
					
					// recup grade / pc chef
					$sql_chef = "SELECT perso.id_perso, pc_perso, id_grade FROM perso, perso_as_grade 
									WHERE perso.id_perso = perso_as_grade.id_perso AND perso.id_perso='$id_perso_nouveau_tour'";
					$res_chef = $mysqli->query($sql_chef);
					$t_chef = $res_chef->fetch_assoc();
					
					$id_perso_chef = $t_chef["id_perso"];
					$pc_perso_chef = $t_chef["pc_perso"];
					$id_grade_chef = $t_chef["id_grade"];
					
					// Verification passage de grade 
					$sql_grade = "SELECT id_grade, nom_grade FROM grades WHERE pc_grade <= $pc_perso_chef AND pc_grade != 0 ORDER BY id_grade DESC LIMIT 1";
					$res_grade = $mysqli->query($sql_grade);
					$t_grade = $res_grade->fetch_assoc();
					
					$id_grade_final 	= $t_grade["id_grade"];
					$nom_grade_final	= $t_grade["nom_grade"];
					
					if ($id_grade_chef < $id_grade_final) {
							
						// Passage de grade								
						$sql = "UPDATE perso_as_grade SET id_grade='$id_grade_final' WHERE id_perso='$id_perso_nouveau_tour'";
						$mysqli->query($sql);
						
						// mise a jour des evenements
						$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) 
								VALUES ('$id_perso_nouveau_tour','<font color=$couleur_clan_p><b>$nom_perso_nouveau_tour</b></font>','a été promu <b>$nom_grade_final</b> !',NULL,'','',NOW(),'0')";
						$mysqli->query($sql);
						
					}
				}
				
			} else {
				
				// Respawn aleatoire
				if ($clan == 1){
					// bleu
					$x_min_respawn = 160;
					$x_max_respawn = 200;
					$y_min_respawn = 160;
					$y_max_respawn = 200;
				}
				
				if ($clan == 2){
					// rouge
					$x_min_respawn = 0;
					$x_max_respawn = 40;
					$y_min_respawn = 0;
					$y_max_respawn = 40;
				}
						
				// on le replace aleatoirement sur la carte
				$occup = 1;
				while ($occup == 1)
				{
					$x = pos_zone_rand_x($x_min_respawn, $x_max_respawn); 
					$y = pos_zone_rand_y($y_min_respawn,$y_max_respawn);
					$occup = verif_pos_libre($mysqli, $x, $y);
				}
				
				$sql = "UPDATE carte SET occupee_carte = '1', image_carte='$image_perso_nouveau_tour', idPerso_carte='$id_perso_nouveau_tour' WHERE x_carte='$x' AND y_carte='$y'";
				$mysqli->query($sql);
				
				// mise a jour des evenements
				$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) 
						VALUES ('$id_perso_nouveau_tour','<font color=$couleur_clan_p><b>$nom_perso_nouveau_tour</b></font>','a été rapatrié',NULL,'','en $x/$y',NOW(),'0')";
				$mysqli->query($sql);
			}
			
			$sql = "SELECT fond_carte FROM carte WHERE x_carte=$x AND y_carte=$y";
			$res_map = $mysqli->query($sql);
			$t_carte1 = $res_map->fetch_assoc();
					
			$fond = $t_carte1["fond_carte"];
			
			// calcul bonus perception perso
			$bonus_visu = get_malus_visu($fond) + getBonusObjet($mysqli, $id_perso_nouveau_tour);
			
			// Calcul PM avec malus rapat
			$pm_nouveau = ($pm_max_perso_nouveau_tour / 2) + $bonusPM_nouveau_tour;
			
			// MAJ perso avec malus rapat
			$sql = "UPDATE perso SET x_perso='$x', y_perso='$y', pm_perso=$pm_nouveau, pa_perso=paMax_perso/2 + bonusPA_perso, pv_perso=pvMax_perso, bonusPerception_perso=$bonus_visu, bourre_perso=0, bonus_perso=0, convalescence=0, DLA_perso=FROM_UNIXTIME($new_dla) 
					WHERE id_perso='$id_perso_nouveau_tour'";
			$mysqli->query($sql);
		}
		else {
			
			// Gestion convalescence
			if ($convalescence_nouveau_tour) {
				$pm_nouveau = ($pm_max_perso_nouveau_tour / 2) + $bonusPM_nouveau_tour;
				$pa_nouveau	= $pa_max_perso_nouveau_tour / 2;
			}
			else {
				$pm_nouveau = $pm_max_perso_nouveau_tour + $bonusPM_nouveau_tour;
				$pa_nouveau	= $pa_max_perso_nouveau_tour;
			}
			
			// Prise en compte malus PM des bousculades (PM négatifs)
			if ($pm_perso_nouveau_tour < 0) {
				$pm_nouveau += $pm_perso_nouveau_tour;
			}
			
			// Calcul pv nouveau tour 
			$pv_nouveau = $pv_perso_nouveau_tour + $recup_perso_nouveau_tour;
			if ($pv_nouveau > $pv_max_perso_nouveau_tour) {
				$pv_nouveau = $pv_max_perso_nouveau_tour;
			}
			
			$sql = "UPDATE perso SET pm_perso=$pm_nouveau, pa_perso=$pa_nouveau+bonusPA_perso, pv_perso=$pv_nouveau, or_perso=or_perso+$gain_or, pc_perso=pc_perso+$gain_pc, bonusRecup_perso=0, bonus_perso=$new_bonus_perso, convalescence=0, bourre_perso=0, DLA_perso=FROM_UNIXTIME($new_dla) 
					WHERE id_perso='$id_perso_nouveau_tour'";
			$mysqli->query($sql);
		}
		
		// On decremente le compteur genie si il est > 1
		if ($genie_nouveau_tour > 1) {
			$sql = "UPDATE perso SET genie = genie - 1 WHERE id_perso = '$id_perso_nouveau_tour'";
			$mysqli->query($sql);
		}
	}
}

/**
 * Fonction qui gère le respawn d'un perso sans nouveau tour
 */
function respawn_perso($mysqli, $id_perso, $nom_perso, $x_perso, $y_perso, $image_perso, $clan, $couleur_clan_p, $chef_perso) {
	
	// ---------------------- //
	//    RESPAWN BATIMENT    //
	// ---------------------- //
									
	// Récupération du batiment de rappatriement le plus proche du perso
	$id_instance_bat = selection_bat_rapat($mysqli, $id_perso, $x_perso, $y_perso, $clan);
	
	if ($id_instance_bat != null && $id_instance_bat != 0) {
		
		// récupération coordonnées batiment
		$sql_b = "SELECT x_instance, y_instance, id_batiment FROM instance_batiment WHERE id_instanceBat='$id_instance_bat'";
		$res_b = $mysqli->query($sql_b);
		$t_b = $res_b->fetch_assoc();
		
		$x 		= $t_b['x_instance'];
		$y 		= $t_b['y_instance'];
		$id_bat	= $t_b['id_batiment'];
		
		// On met le perso dans le batiment
		$sql = "INSERT INTO perso_in_batiment VALUES('$id_perso','$id_instance_bat')";
		$mysqli->query($sql);
		
		// mise a jour des evenements
		$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) 
				VALUES ('$id_perso','<font color=$couleur_clan_p><b>$nom_perso</b></font>','a été rapatrié',NULL,'','dans le bâtiment $id_instance_bat en $x/$y',NOW(),'0')";
		$mysqli->query($sql);
		
		// Rapat Chef dans Fort ou Fortin
		if ($chef_perso && ($id_bat == 8 || $id_bat == 9)) {
			
			// recup grade / pc chef
			$sql = "SELECT perso.id_perso, pc_perso, id_grade FROM perso, perso_as_grade WHERE perso.id_perso = perso_as_grade.id_perso AND perso.id_perso='$id_perso'";
			$res = $mysqli->query($sql);
			$t_chef = $res->fetch_assoc();
			
			$id_perso_chef = $t_chef["id_perso"];
			$pc_perso_chef = $t_chef["pc_perso"];
			$id_grade_chef = $t_chef["id_grade"];
			
			// Verification passage de grade 
			$sql = "SELECT id_grade, nom_grade FROM grades WHERE pc_grade <= $pc_perso_chef AND pc_grade != 0 ORDER BY id_grade DESC LIMIT 1";
			$res = $mysqli->query($sql);
			$t_grade = $res->fetch_assoc();
			
			$id_grade_final 	= $t_grade["id_grade"];
			$nom_grade_final	= $t_grade["nom_grade"];
			
			if ($id_grade_chef < $id_grade_final) {
					
				// Passage de grade								
				$sql = "UPDATE perso_as_grade SET id_grade='$id_grade_final' WHERE id_perso='$id_perso'";
				$mysqli->query($sql);
				
				// mise a jour des evenements
				$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) 
						VALUES ('$id_perso','<font color=$couleur_clan_p><b>$nom_perso</b></font>','a été promu <b>$nom_grade_final</b> !',NULL,'','',NOW(),'0')";
				$mysqli->query($sql);
				
			}
		}
		
	} else {
		
		// Respawn aleatoire
		if ($clan == 1){
			// bleu
			$x_min_respawn = 160;
			$x_max_respawn = 200;
			$y_min_respawn = 160;
			$y_max_respawn = 200;
		}
		
		if ($clan == 2){
			// rouge
			$x_min_respawn = 0;
			$x_max_respawn = 40;
			$y_min_respawn = 0;
			$y_max_respawn = 40;
		}
				
		// on le replace aleatoirement sur la carte
		$occup = 1;
		while ($occup == 1)
		{
			$x = pos_zone_rand_x($x_min_respawn, $x_max_respawn); 
			$y = pos_zone_rand_y($y_min_respawn,$y_max_respawn);
			$occup = verif_pos_libre($mysqli, $x, $y);
		}
		
		$sql = "UPDATE carte SET occupee_carte = '1', image_carte='$image_perso', idPerso_carte='$id_perso' WHERE x_carte='$x' AND y_carte='$y'";
		$mysqli->query($sql);
		
		// mise a jour des evenements
		$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) 
				VALUES ('$id_perso','<font color=$couleur_clan_p><b>$nom_perso</b></font>','a été rapatrié',NULL,'','en $x/$y',NOW(),'0')";
		$mysqli->query($sql);
	}
	
	$sql = "SELECT fond_carte FROM carte WHERE x_carte=$x AND y_carte=$y";
	$res_map = $mysqli->query($sql);
	$t_carte1 = $res_map->fetch_assoc();
			
	$fond = $t_carte1["fond_carte"];
	
	// calcul bonus perception perso
	$bonus_visu = get_malus_visu($fond) + getBonusObjet($mysqli, $id_perso);
	
	// MAJ perso rapat
	$sql = "UPDATE perso SET x_perso='$x', y_perso='$y', pm_perso=0, pa_perso=0, pv_perso=pvMax_perso, bonusPerception_perso=$bonus_visu, bourre_perso=0, bonus_perso=0, convalescence=1 
			WHERE id_perso='$id_perso'";
	$mysqli->query($sql);
}
?>
