<?php
session_start();
require_once("../fonctions.php");
require_once("f_train.php");

$mysqli = db_connexion();

// Récupération de tous les trains
$sql = "SELECT * FROM instance_batiment WHERE id_batiment='12'";
$res = $mysqli->query($sql);

while ($t = $res->fetch_assoc()) {

	$id_instance_train 	= $t['id_instanceBat'];
	$nom_train			= $t['nom_instance'];
	$pv_train			= $t['pv_instance'];
	$pvMax_train		= $t['pvMax_instance'];
	$x_train			= $t['x_instance'];
	$y_train			= $t['y_instance'];
	$camp_train			= $t['camp_instance'];
	$contenance_train	= $t['contenance_instance'];
	
	if ($camp_train == 1) {
		// Nord
		$image_train 		= "b12b.png";
		$couleur_camp_train	= "blue";
	}
	else if ($camp_train == 2) {
		// Sud
		$image_train 		= "b12r.png";
		$couleur_camp_train	= "red";
	}
	else {
		// Ne devrait pas arriver
		$image_train 		= "b12.png";
		$couleur_camp_train	= "black";
	}	
	
	// récupération de la direction de ce train
	$sql_dir = "SELECT direction FROM liaisons_gare WHERE id_train='$id_instance_train'";
	$res_dir = $mysqli->query($sql_dir);
	$t_dir = $res_dir->fetch_assoc();
	
	$gare_arrivee = $t_dir['direction'];
	
	// Récupération des coordonnées de la direction
	$sql_g = "SELECT x_instance, y_instance, pv_instance, pvMax_instance, camp_instance FROM instance_batiment WHERE id_instanceBat='$gare_arrivee'";
	$res_g = $mysqli->query($sql_g);
	$t_g = $res_g->fetch_assoc();
	$nb_g = $res_g->num_rows;
	
	if ($nb_g) {
	
		$x_gare_arrivee 	= $t_g['x_instance'];
		$y_gare_arrivee 	= $t_g['y_instance'];
		$pv_gare_arrivee	= $t_g['pv_instance'];
		$pvMax_gare_arrivee	= $t_g['pvMax_instance'];
		$camp_gare_arrivee	= $t_g['camp_instance'];
		
		// Calcul pourcentage pv du batiment 
		$pourc_pv_gare_arrivee = ($pv_gare_arrivee / $pvMax_gare_arrivee) * 100;
		
		echo "Déplacement du train ". $id_instance_train ." ($x_train / $y_train) vers la gare ". $gare_arrivee ." ($x_gare_arrivee / $y_gare_arrivee)<br />";
		
		// 10 PM
		$dep_restant = 10;
		
		// Une gare n'est active qu'au dessus de 50% de ses PV
		// Le train circule vers la gare que si la gare d'arrivée est du même camp que le train
		while (!est_arrivee($mysqli, $x_train, $y_train, $gare_arrivee) 
			&& $dep_restant > 0 
			&& $pourc_pv_gare_arrivee >= 50 
			&& $camp_gare_arrivee == $camp_train) {
			
			// On déplace le train
			if ($x_train > $x_gare_arrivee) {
				// Déplacement vers la gauche
				
				$rail_trouve = false;
				
				// Récupération des rails à gauche
				$sql_r = "SELECT x_carte, y_carte, fond_carte FROM carte WHERE x_carte=$x_train-1 AND (y_carte=$y_train OR y_carte=$y_train-1 OR y_carte=$y_train+1)";
				$res_r = $mysqli->query($sql_r);
				
				while ($t_r = $res_r->fetch_assoc()) {
					
					$x_r 	= $t_r['x_carte'];
					$y_r 	= $t_r['y_carte'];
					$fond_r	= $t_r['fond_carte'];
					
					// Rail trouvé
					if ($fond_r == "rail.gif") {
						$rail_trouve = true;
						break;
					}
				}
				
				if (!$rail_trouve) {
					// Le rail ne se trouvait pas à gauche => on cherche la direction nord ou sud
					if ($y_train > $y_gare_arrivee) {
						// Déplacement vers le bas
						// Récupération des rails en bas
						$sql_r2 = "SELECT x_carte, y_carte, fond_carte FROM carte WHERE y_carte=$y_train-1 AND (x_carte=$x_train OR x_carte=$x_train-1 OR x_carte=$x_train+1)";
						$res_r2 = $mysqli->query($sql_r2);
						
						while ($t_r2 = $res_r2->fetch_assoc()) {
					
							$x_r 	= $t_r2['x_carte'];
							$y_r 	= $t_r2['y_carte'];
							$fond_r	= $t_r2['fond_carte'];
							
							// Rail trouvé
							if ($fond_r == "rail.gif") {
								$rail_trouve = true;
								break;
							}
						}
					}
					else {
						// Déplacement vers le haut
						// Récupération des rails en haut
						$sql_r2 = "SELECT x_carte, y_carte, fond_carte FROM carte WHERE y_carte=$y_train+1 AND (x_carte=$x_train OR x_carte=$x_train-1 OR x_carte=$x_train+1)";
						$res_r2 = $mysqli->query($sql_r2);
						
						while ($t_r2 = $res_r2->fetch_assoc()) {
					
							$x_r 	= $t_r2['x_carte'];
							$y_r 	= $t_r2['y_carte'];
							$fond_r	= $t_r2['fond_carte'];
							
							// Rail trouvé
							if ($fond_r == "rail.gif") {
								$rail_trouve = true;
								break;
							}
						}
					}
					
					if (!$rail_trouve) {
						echo "Rail non trouvé !!! (problème à corriger)";
						
					} else {
						// Le rail est trouvé => on se déplace dessus
						
						// Y a t-il un obstacle sur les rails ?
						$sql_c = "SELECT occupee_carte, idPerso_carte FROM carte WHERE x_carte='$x_r' AND y_carte='$y_r'";
						$res_c = $mysqli->query($sql_c);
						$t_c = $res_c->fetch_assoc();
						
						$occupee_carte 	= $t_c['occupee_carte'];
						$idPerso_carte	= $t_c['idPerso_carte'];
						
						if ($occupee_carte && $idPerso_carte >= 50000 && $idPerso_carte < 200000) {
							// On sort de la boucle et on se déplace pas
							break;
						}
						else {
							// Modification coordonnées instance train
							$sql_t = "UPDATE instance_batiment set x_instance='$x_r', y_instance='$y_r' WHERE id_instanceBat='$id_instance_train'";
							$mysqli->query($sql_t);
							
							$x_train = $x_r;
							$y_train = $y_r;
							
							if (deplacement_train($mysqli, $id_instance_train, $x_train, $y_train, $image_train, $nom_train, $couleur_camp_train)) {
								$dep_restant--;
							}
						}
					}
				}
				else {
					// Le rail est trouvé => on se déplace dessus
					
					// Y a t-il un obstacle sur les rails ?
					$sql_c = "SELECT occupee_carte, idPerso_carte FROM carte WHERE x_carte='$x_r' AND y_carte='$y_r'";
					$res_c = $mysqli->query($sql_c);
					$t_c = $res_c->fetch_assoc();
					
					$occupee_carte 	= $t_c['occupee_carte'];
					$idPerso_carte	= $t_c['idPerso_carte'];
					
					if ($occupee_carte && $idPerso_carte >= 50000 && $idPerso_carte < 200000) {
						// On sort de la boucle et on se déplace pas
						break;
					}
					else {
					
						// Modification coordonnées instance train
						$sql_t = "UPDATE instance_batiment set x_instance='$x_r', y_instance='$y_r' WHERE id_instanceBat='$id_instance_train'";
						$mysqli->query($sql_t);
						
						$x_train = $x_r;
						$y_train = $y_r;
						
						if (deplacement_train($mysqli, $id_instance_train, $x_train, $y_train, $image_train, $nom_train, $couleur_camp_train)) {
							$dep_restant--;
						}
					}
				}
				
			}
			else if ($x_train < $x_gare_arrivee) {
				// Déplacement vers la droite
				
				$rail_trouve = false;
				
				// Récupération des rails à droite
				$sql_r = "SELECT x_carte, y_carte, fond_carte FROM carte WHERE x_carte=$x_train+1 AND (y_carte=$y_train OR y_carte=$y_train-1 OR y_carte=$y_train+1)";
				$res_r = $mysqli->query($sql_r);
				
				while ($t_r = $res_r->fetch_assoc()) {
					
					$x_r 	= $t_r['x_carte'];
					$y_r 	= $t_r['y_carte'];
					$fond_r	= $t_r['fond_carte'];
					
					// Rail trouvé
					if ($fond_r == "rail.gif") {
						$rail_trouve = true;
						break;
					}
				}
				
				if (!$rail_trouve) {
					// Le rail ne se trouvait pas à gauche => on cherche la direction nord ou sud
					if ($y_train > $y_gare_arrivee) {
						// Déplacement vers le bas
						
						// Récupération des rails en bas
						$sql_r2 = "SELECT x_carte, y_carte, fond_carte FROM carte WHERE y_carte=$y_train-1 AND (x_carte=$x_train OR x_carte=$x_train-1 OR x_carte=$x_train+1)";
						$res_r2 = $mysqli->query($sql_r2);
						
						while ($t_r2 = $res_r2->fetch_assoc()) {
					
							$x_r 	= $t_r2['x_carte'];
							$y_r 	= $t_r2['y_carte'];
							$fond_r	= $t_r2['fond_carte'];
							
							// Rail trouvé
							if ($fond_r == "rail.gif") {
								$rail_trouve = true;
								break;
							}
						}
					}
					else {
						// Déplacement vers le haut
						
						// Récupération des rails en haut
						$sql_r2 = "SELECT x_carte, y_carte, fond_carte FROM carte WHERE y_carte=$y_train+1 AND (x_carte=$x_train OR x_carte=$x_train-1 OR x_carte=$x_train+1)";
						$res_r2 = $mysqli->query($sql_r2);
						
						while ($t_r2 = $res_r2->fetch_assoc()) {
					
							$x_r 	= $t_r2['x_carte'];
							$y_r 	= $t_r2['y_carte'];
							$fond_r	= $t_r2['fond_carte'];
							
							// Rail trouvé
							if ($fond_r == "rail.gif") {
								$rail_trouve = true;
								break;
							}
						}
					}
					
					if (!$rail_trouve) {
						echo "Rail non trouvé !!! (problème à corriger)";
						
					} else {
						// Le rail est trouvé => on se déplace dessus
						
						// Y a t-il un obstacle sur les rails ?
						$sql_c = "SELECT occupee_carte, idPerso_carte FROM carte WHERE x_carte='$x_r' AND y_carte='$y_r'";
						$res_c = $mysqli->query($sql_c);
						$t_c = $res_c->fetch_assoc();
						
						$occupee_carte 	= $t_c['occupee_carte'];
						$idPerso_carte	= $t_c['idPerso_carte'];
						
						if ($occupee_carte && $idPerso_carte >= 50000 && $idPerso_carte < 200000) {
							// On sort de la boucle et on se déplace pas
							break;
						}
						else {
						
							// Modification coordonnées instance train
							$sql_t = "UPDATE instance_batiment set x_instance='$x_r', y_instance='$y_r' WHERE id_instanceBat='$id_instance_train'";
							$mysqli->query($sql_t);
							
							$x_train = $x_r;
							$y_train = $y_r;
							
							if (deplacement_train($mysqli, $id_instance_train, $x_train, $y_train, $image_train, $nom_train, $couleur_camp_train)) {
								$dep_restant--;
							}
						}
					}
				}
				else {
					// Le rail est trouvé => on se déplace dessus
					
					// Y a t-il un obstacle sur les rails ?
					$sql_c = "SELECT occupee_carte, idPerso_carte FROM carte WHERE x_carte='$x_r' AND y_carte='$y_r'";
					$res_c = $mysqli->query($sql_c);
					$t_c = $res_c->fetch_assoc();
					
					$occupee_carte 	= $t_c['occupee_carte'];
					$idPerso_carte	= $t_c['idPerso_carte'];
					
					if ($occupee_carte && $idPerso_carte >= 50000 && $idPerso_carte < 200000) {
						// On sort de la boucle et on se déplace pas
						break;
					}
					else {
					
						// Modification coordonnées instance train
						$sql_t = "UPDATE instance_batiment set x_instance='$x_r', y_instance='$y_r' WHERE id_instanceBat='$id_instance_train'";
						$mysqli->query($sql_t);
						
						$x_train = $x_r;
						$y_train = $y_r;
						
						if (deplacement_train($mysqli, $id_instance_train, $x_train, $y_train, $image_train, $nom_train, $couleur_camp_train)) {
							$dep_restant--;
						}
					}
				}
			}
			else {
				// $x_train == $x_gare_arrivee
				
				if ($y_train > $y_gare_arrivee) {
					// Déplacement vers le bas
					
					// Récupération des rails en bas
					$sql_r2 = "SELECT x_carte, y_carte, fond_carte FROM carte WHERE y_carte=$y_train-1 AND (x_carte=$x_train OR x_carte=$x_train-1 OR x_carte=$x_train+1)";
					$res_r2 = $mysqli->query($sql_r2);
					
					while ($t_r2 = $res_r2->fetch_assoc()) {
				
						$x_r 	= $t_r2['x_carte'];
						$y_r 	= $t_r2['y_carte'];
						$fond_r	= $t_r2['fond_carte'];
						
						// Rail trouvé
						if ($fond_r == "rail.gif") {
							$rail_trouve = true;
							break;
						}
					}
				}
				else {
					// Déplacement vers le haut
					
					// Récupération des rails en haut
					$sql_r2 = "SELECT x_carte, y_carte, fond_carte FROM carte WHERE y_carte=$y_train+1 AND (x_carte=$x_train OR x_carte=$x_train-1 OR x_carte=$x_train+1)";
					$res_r2 = $mysqli->query($sql_r2);
					
					while ($t_r2 = $res_r2->fetch_assoc()) {
				
						$x_r 	= $t_r2['x_carte'];
						$y_r 	= $t_r2['y_carte'];
						$fond_r	= $t_r2['fond_carte'];
						
						// Rail trouvé
						if ($fond_r == "rail.gif") {
							$rail_trouve = true;
							break;
						}
					}
				}
				
				if (!$rail_trouve) {
					echo "Rail non trouvé !!! (problème à corriger)";
					
				} else {
					// Le rail est trouvé => on se déplace dessus
					
					// Y a t-il un obstacle sur les rails ?
					$sql_c = "SELECT occupee_carte, idPerso_carte FROM carte WHERE x_carte='$x_r' AND y_carte='$y_r'";
					$res_c = $mysqli->query($sql_c);
					$t_c = $res_c->fetch_assoc();
					
					$occupee_carte 	= $t_c['occupee_carte'];
					$idPerso_carte	= $t_c['idPerso_carte'];
					
					if ($occupee_carte && $idPerso_carte >= 50000 && $idPerso_carte < 200000) {
						// On sort de la boucle et on se déplace pas
						break;
					}
					else {
					
						// Modification coordonnées instance train
						$sql_t = "UPDATE instance_batiment set x_instance='$x_r', y_instance='$y_r' WHERE id_instanceBat='$id_instance_train'";
						$mysqli->query($sql_t);
						
						$x_train = $x_r;
						$y_train = $y_r;
						
						if (deplacement_train($mysqli, $id_instance_train, $x_train, $y_train, $image_train, $nom_train, $couleur_camp_train)) {
							$dep_restant--;
						}
					}
				}
			}
		}
		
		if (est_arrivee($mysqli, $x_train, $y_train, $gare_arrivee)) {
			
			$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) 
					VALUES ($id_instance_train,'Train','est entré en Gare',NULL,'','[$gare_arrivee] en $x_train/$y_train',NOW(),'0')";
			$mysqli->query($sql);
			
			// On remet les PV au train 
			$sql_pv = "UPDATE instance_batiment SET pv_instance=pvMax_instance WHERE id_instanceBat='$id_instance_train'";
			$mysqli->query($sql_pv);
			
			dechargement_persos_train($mysqli, $id_instance_train, $gare_arrivee, $x_gare_arrivee, $y_gare_arrivee);
			
			// On change la destination du train
			$sql_dt = "SELECT id_gare1, id_gare2 FROM liaisons_gare WHERE id_train='$id_instance_train'";
			$res_dt = $mysqli->query($sql_dt);
			$t_dt = $res_dt->fetch_assoc();
			
			$id_gare1 = $t_dt['id_gare1'];
			$id_gare2 = $t_dt['id_gare2'];
			
			if ($gare_arrivee == $id_gare1) {
				$nouvelle_direction = $id_gare2;
			} else {
				$nouvelle_direction = $id_gare1;
			}
			
			$sql_lg = "UPDATE liaisons_gare SET direction='$nouvelle_direction' WHERE id_train='$id_instance_train'";
			$mysqli->query($sql_lg);
			
			chargement_persos_train($mysqli, $id_instance_train, $x_train, $y_train, $nouvelle_direction, $gare_arrivee, $camp_train);
		}
	}
	else {
		echo " -> Gare cible détruite !<br />";
	}
}
?>