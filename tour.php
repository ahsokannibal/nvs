<?php 
@session_start();  
require_once("fonctions.php");
require_once("jeu/f_carte.php");
	
$mysqli = db_connexion();

include ('nb_online.php');

if(isset($_SESSION["ID_joueur"])){
	
	$id_joueur = $_SESSION['ID_joueur'];
	
	// recuperation de l'id et du nom du chef
	$sql = "SELECT id_perso, nom_perso, pv_perso, clan, image_perso, est_gele, UNIX_TIMESTAMP(DLA_perso) as DLA, UNIX_TIMESTAMP(date_gele) as DG FROM perso WHERE idJoueur_perso=$id_joueur AND chef=1";
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
			$sql = "SELECT idJoueur_perso, x_perso, y_perso, pv_perso, pvMax_perso, recup_perso, bonusRecup_perso, image_perso FROM perso WHERE id_perso='$id_perso'";
			$res = $mysqli->query($sql);
			$t_perso = $res->fetch_assoc();
			
			$id_joueur_perso 	= $t_perso["idJoueur_perso"];
			$x_perso			= $t_perso["x_perso"];
			$y_perso			= $t_perso["y_perso"];
			$pv_perso			= $t_perso["pv_perso"];
			$pvMax_perso		= $t_perso["pvMax_perso"];
			$recup_perso		= $t_perso["recup_perso"] + $t_perso["bonusRecup_perso"];
			$image_perso		= $t_perso["image_perso"];
			
			// Le perso appartient-il bien au joueur ?
			if ($id_joueur_perso == $id_joueur) {
				
				if ($pv_perso <= 0) {
					
					$date = time();
					
					// Le perso est bien mort
					//    RESPAWN BATIMENT    //
								
					// Récupération du batiment de rappatriement le plus proche du perso
					$id_bat = selection_bat_rapat($mysqli, $id_perso, $x_perso, $y_perso, $clan);
					
					if ($id_bat != null && $id_bat != 0) {
						
						// récupération coordonnées batiment
						$sql_b = "SELECT x_instance, y_instance FROM instance_batiment WHERE id_instanceBat='$id_bat'";
						$res_b = $mysqli->query($sql_b);
						$t_b = $res_b->fetch_assoc();
						
						$x = $t_b['x_instance'];
						$y = $t_b['y_instance'];
						
						// On met le perso dans le batiment
						$sql = "INSERT INTO perso_in_batiment VALUES('$id_perso','$id_bat')";
						$mysqli->query($sql);
						
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
					}
					
					$sql = "SELECT fond_carte FROM carte WHERE x_carte=$x AND y_carte=$y";
					$res_map = $mysqli->query($sql);
					$t_carte1 = $res_map->fetch_assoc();
							
					$fond = $t_carte1["fond_carte"];
					
					// calcul bonus perception perso
					$bonus_visu = get_malus_visu($fond) + getBonusObjet($mysqli, $id_perso);
					
					if (nouveau_tour($date, $dla)) {
						
						// calcul du prochain tour
						$new_dla = $date + DUREE_TOUR;
						
						// Récupération de tous les perso du joueur
						$sql = "SELECT id_perso, x_perso, y_perso, pm_perso, pv_perso, pvMax_perso, recup_perso, bonusRecup_perso, bonus_perso, bonusPM_perso, image_perso, type_perso, chef FROM perso WHERE idJoueur_perso='$id_joueur'";
						$res = $mysqli->query($sql);
						
						while ($t_persos = $res->fetch_assoc()) {
					
							$id_perso_nouveau_tour 		= $t_persos["id_perso"];
							$pv_perso_nouveau_tour 		= $t_persos["pv_perso"];
							$pv_max_perso_nouveau_tour	= $t_persos["pvMax_perso"];
							$recup_perso_nouveau_tour 	= $t_persos["recup_perso"] + $t_persos["bonusRecup_perso"];
							$x_perso_nouveau_tour		= $t_persos["x_perso"];
							$y_perso_nouveau_tour		= $t_persos["y_perso"];
							$chef_perso_nouveau_tour	= $t_persos["chef"];
							$image_perso_nouveau_tour	= $t_persos["image_perso"];
							$bonus_perso_nouveau_tour	= $t_persos["bonus_perso"];
							$type_perso_nouveau_tour	= $t_persos["type_perso"];
							$pm_perso_nouvea_tour		= $t_persos["pm_perso"];
							$bonusPM_nouveau_tour 		= $t_persos["bonusPM_perso"];
							
							$new_bonus_perso = 0;
							
							if ($bonus_perso_nouveau_tour + 5 <= 0) {
								$new_bonus_perso = $bonus_perso_nouveau_tour + 5;
							}
							
							if ($chef_perso_nouveau_tour == '1') {
									
									$gain_or = 3;
									$gain_pc = 1;
									
							} else {
								
								$gain_or = gain_or_grouillot($type_perso_nouveau_tour);
								$gain_pc = 0;
							}
							
							if ($pm_perso_nouveau_tour < 0) {
								$malus_pm = $pm_perso_nouveau_tour + $bonusPM_nouveau_tour;
							} else {
								$malus_pm = 0;
							}
							
							if ($pv_perso_nouveau_tour <= 0) {
								// MAJ perso avec malus rapat
								$sql = "UPDATE perso SET x_perso='$x', y_perso='$y', pm_perso=pmMax_perso/2, pa_perso=paMax_perso+bonusPA_perso, pv_perso=pvMax_perso, bonusPerception_perso=$bonus_visu, bourre_perso=0, bonus_perso=0 WHERE id_perso='$id_perso'";
								$mysqli->query($sql);
							}
							else {
								$sql = "UPDATE perso SET pm_perso=pmMax_perso+$malus_pm, pa_perso=paMax_perso+bonusPA_perso, pv_perso=$pv_after_recup, or_perso=or_perso+$gain_or, pc_perso=pc_perso+$gain_pc, bonusRecup_perso=0, bonusPerception_perso=$bonus_visu, bonus_perso=$new_bonus_perso, bourre_perso=0, DLA_perso=FROM_UNIXTIME($new_dla) WHERE id_perso='$id_perso_nouveau_tour'";
								$mysqli->query($sql);
							}
						}
					} else {
						
						// MAJ perso avec malus rapat
						$sql = "UPDATE perso SET x_perso='$x', y_perso='$y', pm_perso=pm_perso/2, pv_perso=pvMax_perso, bonusPerception_perso=$bonus_visu, bourre_perso=0, bonus_perso=0 WHERE id_perso='$id_perso'";
						$mysqli->query($sql);
					}
		
					//redirection
					header("location:jeu/jouer.php");
					
				} else {
					// Le perso est vivant

					$date = time();
					
					// redirection
					if (!$est_gele && nouveau_tour($date, $dla)) {
				
						// calcul du prochain tour
						$new_dla = $date + DUREE_TOUR;
						
						// Récupération de tous les perso du joueur
						$sql = "SELECT id_perso, x_perso, y_perso, pm_perso, pv_perso, pvMax_perso, recup_perso, bonusRecup_perso, bonus_perso, bonusPM_perso, image_perso, type_perso, chef FROM perso WHERE idJoueur_perso='$id_joueur'";
						$res = $mysqli->query($sql);
						
						while ($t_persos = $res->fetch_assoc()) {
					
							$id_perso_nouveau_tour 		= $t_persos["id_perso"];
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
							$bonusPM_nouveau_tour 		= $t_persos["bonusPM_perso"];
							
							$new_bonus_perso = 0;
							
							if ($bonus_perso_nouveau_tour + 5 <= 0) {
								$new_bonus_perso = $bonus_perso_nouveau_tour + 5;
							}
							
							//il est encore en vie
							if ($pv_perso_nouveau_tour > 0) {
								
								$pv_after_recup = $pv_perso_nouveau_tour + $recup_perso_nouveau_tour;
								
								if ($pv_after_recup > $pv_max_perso_nouveau_tour) {
									$pv_after_recup = $pv_max_perso_nouveau_tour;
								}
								
								$sql = "SELECT fond_carte FROM carte WHERE x_carte=$x_perso_nouveau_tour AND y_carte=$y_perso_nouveau_tour";
								$res_map = $mysqli->query($sql);
								$t_carte1 = $res_map->fetch_assoc();
								
								$fond = $t_carte1["fond_carte"];
								
								// calcul bonus perception perso
								$bonus_visu = get_malus_visu($fond) + getBonusObjet($mysqli, $id_perso_nouveau_tour);
								
								if ($pm_perso_nouveau_tour < 0) {
									$malus_pm = $pm_perso_nouveau_tour + $bonusPM_nouveau_tour;
								} else {
									$malus_pm = 0;
								}
								
								if ($chef_perso_nouveau_tour == '1') {
									
									$gain_or = 3;
									
									// C'est le chef => gain or et PC
									$sql = "UPDATE perso SET pm_perso=pmMax_perso+$malus_pm, pa_perso=paMax_perso+bonusPA_perso, pv_perso=$pv_after_recup, or_perso=or_perso+$gain_or, pc_perso=pc_perso+1, bonusRecup_perso=0, bonusPerception_perso=$bonus_visu, bonus_perso=$new_bonus_perso, bourre_perso=0, DLA_perso=FROM_UNIXTIME($new_dla) WHERE id_perso='$id_perso_nouveau_tour'";
									$mysqli->query($sql);
									
								} else {
									
									$gain_or = gain_or_grouillot($type_perso_nouveau_tour);
									
									// C'est un grouillot
									$sql = "UPDATE perso SET pm_perso=pmMax_perso+$malus_pm, pa_perso=paMax_perso+bonusPA_perso, pv_perso=$pv_after_recup, or_perso=or_perso+$gain_or, bonusRecup_perso=0, bonusPerception_perso=$bonus_visu, bonus_perso=$new_bonus_perso, bourre_perso=0, DLA_perso=FROM_UNIXTIME($new_dla) WHERE id_perso='$id_perso_nouveau_tour'";
									$mysqli->query($sql);
									
								}
								
								// redirection
								header("location:jeu/jouer.php");			
							}
						}
					}
					else {
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
	
		$_SESSION["id_perso"] = $id;
		$_SESSION["nom_perso"] = $pseudo;

		$date = time();
		
		// Perso gele et il peut se degeler
		if($est_gele && temp_degele($date, $date_gele)){
			
			// Récupération de tous les perso du joueur
			$sql = "SELECT id_perso, x_perso, y_perso FROM perso WHERE idJoueur_perso='$id_joueur'";
			$res = $mysqli->query($sql);
			
			while ($t_persos = $res->fetch_assoc()) {
				
				$id_perso_degele 	= $t_persos["id_perso"];
				$x_perso_degele		= $t_persos["x_perso"];
				$y_perso_degele		= $t_persos["y_perso"];
				
				// degele du perso
				$sql = "UPDATE perso SET est_gele='0', date_gele=NULL, a_gele='0' WHERE id_perso='$id_perso_degele'";
				$mysqli->query($sql);
				
				// Récupération du batiment de rappatriement le plus proche du perso
				$id_bat = selection_bat_rapat($mysqli, $id_perso_degele, $x_perso_degele, $y_perso_degele, $clan);
				
				if ($id_bat != null && $id_bat != 0) {
				
					// récupération coordonnées batiment
					$sql_b = "SELECT x_instance, y_instance FROM instance_batiment WHERE id_instanceBat='$id_bat'";
					$res_b = $mysqli->query($sql_b);
					$t_b = $res_b->fetch_assoc();
					
					$x = $t_b['x_instance'];
					$y = $t_b['y_instance'];
					
					// On met le perso et ses grouillots dans le batiment
					$sql = "INSERT INTO perso_in_batiment VALUES('$id_perso_degele','$id_bat')";
					$mysqli->query($sql);
					
					// calcul bonus perception perso
					$bonus_visu = getBonusObjet($mysqli, $id_perso);
					
					// MAJ perso
					$sql = "UPDATE perso SET x_perso='$x', y_perso='$y', pm_perso=pmMax_perso, pa_perso=paMax_perso+bonusPA_perso, pv_perso=pvMax_perso, bonusPerception_perso=$bonus_visu, bourre_perso=0, bonus_perso=0 WHERE id_perso='$id_perso_degele'";
					$mysqli->query($sql);
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
					
					// Récupération de tous les perso du joueur
					$sql = "SELECT id_perso, x_perso, y_perso, pm_perso, pv_perso, pvMax_perso, recup_perso, bonusRecup_perso, bonus_perso, bonusPM_perso, image_perso, type_perso, chef FROM perso WHERE idJoueur_perso='$id_joueur'";
					$res = $mysqli->query($sql);
					
					while ($t_persos = $res->fetch_assoc()) {
				
						$id_perso_nouveau_tour 		= $t_persos["id_perso"];
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
						$bonusPM_nouveau_tour 		= $t_persos["bonusPM_perso"];
						
						$new_bonus_perso = 0;
						
						if ($bonus_perso_nouveau_tour + 5 <= 0) {
							$new_bonus_perso = $bonus_perso_nouveau_tour + 5;
						}
						
						//il est encore en vie
						if ($pv_perso_nouveau_tour > 0) {
							
							$pv_after_recup = $pv_perso_nouveau_tour + $recup_perso_nouveau_tour;
							
							if ($pv_after_recup > $pv_max_perso_nouveau_tour) {
								$pv_after_recup = $pv_max_perso_nouveau_tour;
							}
							
							$sql = "SELECT fond_carte FROM carte WHERE x_carte=$x_perso_nouveau_tour AND y_carte=$y_perso_nouveau_tour";
							$res_map = $mysqli->query($sql);
							$t_carte1 = $res_map->fetch_assoc();
							
							$fond = $t_carte1["fond_carte"];
							
							// calcul bonus perception perso
							$bonus_visu = get_malus_visu($fond) + getBonusObjet($mysqli, $id_perso_nouveau_tour);
							
							if ($pm_perso_nouveau_tour < 0) {
								$malus_pm = $pm_perso_nouveau_tour + $bonusPM_nouveau_tour;
							} else {
								$malus_pm = 0;
							}
							
							if ($chef_perso_nouveau_tour == '1') {
								
								$gain_or = 3;
								
								// C'est le chef => gain or et PC
								$sql = "UPDATE perso SET pm_perso=pmMax_perso+$malus_pm, pa_perso=paMax_perso+bonusPA_perso, pv_perso=$pv_after_recup, or_perso=or_perso+$gain_or, pc_perso=pc_perso+1, bonusRecup_perso=0, bonusPerception_perso=$bonus_visu, bonus_perso=$new_bonus_perso, bourre_perso=0, DLA_perso=FROM_UNIXTIME($new_dla) WHERE id_perso='$id_perso_nouveau_tour'";
								$mysqli->query($sql);
								
							} else {
								
								$gain_or = gain_or_grouillot($type_perso_nouveau_tour);
								
								// C'est un grouillot
								$sql = "UPDATE perso SET pm_perso=pmMax_perso+$malus_pm, pa_perso=paMax_perso+bonusPA_perso, pv_perso=$pv_after_recup, or_perso=or_perso+$gain_or, bonusRecup_perso=0, bonusPerception_perso=$bonus_visu, bonus_perso=$new_bonus_perso, bourre_perso=0, DLA_perso=FROM_UNIXTIME($new_dla) WHERE id_perso='$id_perso_nouveau_tour'";
								$mysqli->query($sql);
								
							}
							
							// redirection
							header("location:jeu/jouer.php");			
						}
						else { 							
							//    RESPAWN BATIMENT    //
							
							// Récupération du batiment de rappatriement le plus proche du perso
							$id_bat = selection_bat_rapat($mysqli, $id_perso_nouveau_tour, $x_perso_nouveau_tour, $y_perso_nouveau_tour, $clan);
							
							if ($id_bat != null && $id_bat != 0) {
							
								// récupération coordonnées batiment
								$sql_b = "SELECT x_instance, y_instance FROM instance_batiment WHERE id_instanceBat='$id_bat'";
								$res_b = $mysqli->query($sql_b);
								$t_b = $res_b->fetch_assoc();
								
								$x = $t_b['x_instance'];
								$y = $t_b['y_instance'];
								
								// On met le perso dans le batiment
								$sql = "INSERT INTO perso_in_batiment VALUES('$id_perso_nouveau_tour','$id_bat')";
								$mysqli->query($sql);
								
							} else {
								
								// Respawn aleatoire
								if($clan == 1){
									// bleu
									$x_min_respawn = 160;
									$x_max_respawn = 200;
									$y_min_respawn = 160;
									$y_max_respawn = 200;
								}
								if($clan == 2){
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
							}
							
							$sql = "SELECT fond_carte FROM carte WHERE x_carte=$x AND y_carte=$y";
							$res_map = $mysqli->query($sql);
							$t_carte1 = $res_map->fetch_assoc();
							
							$fond = $t_carte1["fond_carte"];
							
							// calcul bonus perception perso
							$bonus_visu = get_malus_visu($fond) + getBonusObjet($mysqli, $id_perso);
							
							// MAJ perso
							if ($chef_perso_nouveau_tour == '1') {
								
								$gain_or = 3;
								
								// C'est le chef => gain or et pc
								$sql = "UPDATE perso SET x_perso='$x', y_perso='$y', pm_perso=pmMax_perso/2, pa_perso=paMax_perso+bonusPA_perso, pv_perso=pvMax_perso, bonusPerception_perso=$bonus_visu, bourre_perso=0, bonus_perso=$new_bonus_perso, pc_perso=pc_perso+1, or_perso=or_perso+$gain_or, DLA_perso=FROM_UNIXTIME($new_dla) WHERE id_perso='$id_perso_nouveau_tour'";
								$mysqli->query($sql);
								
							} else {
								
								$gain_or = gain_or_grouillot($type_perso_nouveau_tour);
								
								// Grouillot
								$sql = "UPDATE perso SET x_perso='$x', y_perso='$y', pm_perso=pmMax_perso/2, pa_perso=paMax_perso+bonusPA_perso, pv_perso=pvMax_perso, bonusPerception_perso=$bonus_visu, bourre_perso=0, bonus_perso=$new_bonus_perso, or_perso=or_perso+$gain_or, DLA_perso=FROM_UNIXTIME($new_dla) WHERE id_perso='$id_perso_nouveau_tour'";
								$mysqli->query($sql);
							}
				
							//redirection
							header("location:jeu/jouer.php");
						}
					}
				}
				else {
					if ($pv > 0) {
						// il est encore en vie
						// redirection
						header("location:jeu/jouer.php");
					}
					else {
						// Il est mort et ce n'est pas un nouveau tour
						
						//    RESPAWN BATIMENT    //
						
						// Récupération du batiment de rappatriement le plus proche du perso
						$id_bat = selection_bat_rapat($mysqli, $id_perso, $x_perso, $y_perso, $clan);
						
						if ($id_bat != null && $id_bat != 0) {
						
							// récupération coordonnées batiment
							$sql_b = "SELECT x_instance, y_instance FROM instance_batiment WHERE id_instanceBat='$id_bat'";
							$res_b = $mysqli->query($sql_b);
							$t_b = $res_b->fetch_assoc();
							
							$x = $t_b['x_instance'];
							$y = $t_b['y_instance'];
							
							// On met le perso dans le batiment
							$sql = "INSERT INTO perso_in_batiment VALUES('$id','$id_bat')";
							$mysqli->query($sql);
							
							// calcul bonus perception perso
							$bonus_visu = getBonusObjet($mysqli, $id_perso);
							
							// MAJ perso
							$sql = "UPDATE perso SET x_perso='$x', y_perso='$y', pm_perso=pm_perso/2, pv_perso=pvMax_perso, bonusPerception_perso=$bonus_visu, bourre_perso=0, bonus_perso=0 WHERE id_perso='$id'";
							$mysqli->query($sql);
				
							//redirection
							header("location:jeu/jouer.php");
							
						}
						else {
							// Respawn aleatoire
							if($clan == 1){
								// bleu
								$x_min_respawn = 160;
								$x_max_respawn = 200;
								$y_min_respawn = 160;
								$y_max_respawn = 200;
							}
							if($clan == 2){
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
							
							$sql = "UPDATE carte SET occupee_carte = '1', image_carte='$image_perso', idPerso_carte='$id' WHERE x_carte='$x' AND y_carte='$y'";
							$mysqli->query($sql);
							
							header("location:jeu/jouer.php");
						}
					}
				}
			}
		}
	}
}
else{
	echo "<font color=red>Vous ne pouvez pas acceder a cette page, veuillez vous logguer.</font>";
}
?>
