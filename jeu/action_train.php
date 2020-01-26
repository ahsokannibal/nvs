<?php
session_start();
require_once("../fonctions.php");

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
		$image_train = "b12b.png";
	}
	else if ($camp_train == 2) {
		// Sud
		$image_train = "b12r.png";
	}
	else {
		// Ne devrait pas arriver
		$image_train = "b12.png";
	}	
	
	// récupération de la direction de ce train
	$sql_dir = "SELECT direction FROM liaisons_gare WHERE id_train='$id_instance_train'";
	$res_dir = $mysqli->query($sql_dir);
	$t_dir = $res_dir->fetch_assoc();
	
	$gare_arrivee = $t_dir['direction'];
	
	// Récupération des coordonnées de la direction
	$sql_g = "SELECT x_instance, y_instance FROM instance_batiment WHERE id_instanceBat='$gare_arrivee'";
	$res_g = $mysqli->query($sql_g);
	$t_g = $res_g->fetch_assoc();
	
	$x_gare_arrivee = $t_g['x_instance'];
	$y_gare_arrivee = $t_g['y_instance'];
	
	echo "Déplacement du train ". $id_instance_train ." ($x_train / $y_train) vers la gare ". $gare_arrivee ." ($x_gare_arrivee / $y_gare_arrivee)<br />";
	
	// 10 PM
	$dep_restant = 10;
	
	while (!est_arrivee($mysqli, $x_train, $y_train, $gare_arrivee) && $dep_restant > 0) {
		
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
					
					// Modification coordonnées instance train
					$sql_t = "UPDATE instance_batiment set x_instance='$x_r', y_instance='$y_r' WHERE id_instanceBat='$id_instance_train'";
					$mysqli->query($sql_t);
					
					$x_train = $x_r;
					$y_train = $y_r;
					
					// Modification carte 
					$sql_c1 = "UPDATE carte SET idPerso_carte=NULL, occupee_carte='0', image_carte=NULL WHERE idPerso_carte='$id_instance_train'";
					$mysqli->query($sql_c1);
					
					$sql_c2 = "UPDATE carte SET idPerso_carte='$id_instance_train', occupee_carte='1', image_carte='$image_train' WHERE x_carte='$x_r' AND y_carte='$y_r'";
					$mysqli->query($sql_c2);
					
					// MAJ coordonnées persos dans le train
					$sql_u_perso = "UPDATE perso SET x_perso='$x_train', y_perso='$y_train' WHERE id_perso IN (SELECT id_perso FROM perso_in_train WHERE id_train='$id_instance_train')";
					$mysqli->query($sql_u_perso);
					
					$dep_restant--;
				}
			}
			else {
				// Le rail est trouvé => on se déplace dessus
				
				// Modification coordonnées instance train
				$sql_t = "UPDATE instance_batiment set x_instance='$x_r', y_instance='$y_r' WHERE id_instanceBat='$id_instance_train'";
				$mysqli->query($sql_t);
				
				$x_train = $x_r;
				$y_train = $y_r;
				
				// Modification carte 
				$sql_c1 = "UPDATE carte SET idPerso_carte=NULL, occupee_carte='0', image_carte=NULL WHERE idPerso_carte='$id_instance_train'";
				$mysqli->query($sql_c1);
				
				$sql_c2 = "UPDATE carte SET idPerso_carte='$id_instance_train', occupee_carte='1', image_carte='$image_train' WHERE x_carte='$x_r' AND y_carte='$y_r'";
				$mysqli->query($sql_c2);

				// MAJ coordonnées persos dans le train
				$sql_u_perso = "UPDATE perso SET x_perso='$x_train', y_perso='$y_train' WHERE id_perso IN (SELECT id_perso FROM perso_in_train WHERE id_train='$id_instance_train')";
				$mysqli->query($sql_u_perso);
				
				$dep_restant--;
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
					
					// Modification coordonnées instance train
					$sql_t = "UPDATE instance_batiment set x_instance='$x_r', y_instance='$y_r' WHERE id_instanceBat='$id_instance_train'";
					$mysqli->query($sql_t);
					
					$x_train = $x_r;
					$y_train = $y_r;
					
					// Modification carte 
					$sql_c1 = "UPDATE carte SET idPerso_carte=NULL, occupee_carte='0', image_carte=NULL WHERE idPerso_carte='$id_instance_train'";
					$mysqli->query($sql_c1);
					
					$sql_c2 = "UPDATE carte SET idPerso_carte='$id_instance_train', occupee_carte='1', image_carte='$image_train' WHERE x_carte='$x_r' AND y_carte='$y_r'";
					$mysqli->query($sql_c2);	
					
					// MAJ coordonnées persos dans le train
					$sql_u_perso = "UPDATE perso SET x_perso='$x_train', y_perso='$y_train' WHERE id_perso IN (SELECT id_perso FROM perso_in_train WHERE id_train='$id_instance_train')";
					$mysqli->query($sql_u_perso);
					
					$dep_restant--;
				}
			}
			else {
				// Le rail est trouvé => on se déplace dessus
				
				// Modification coordonnées instance train
				$sql_t = "UPDATE instance_batiment set x_instance='$x_r', y_instance='$y_r' WHERE id_instanceBat='$id_instance_train'";
				$mysqli->query($sql_t);
				
				$x_train = $x_r;
				$y_train = $y_r;
				
				// Modification carte 
				$sql_c1 = "UPDATE carte SET idPerso_carte=NULL, occupee_carte='0', image_carte=NULL WHERE idPerso_carte='$id_instance_train'";
				$mysqli->query($sql_c1);
				
				$sql_c2 = "UPDATE carte SET idPerso_carte='$id_instance_train', occupee_carte='1', image_carte='$image_train' WHERE x_carte='$x_r' AND y_carte='$y_r'";
				$mysqli->query($sql_c2);
				
				// MAJ coordonnées persos dans le train
				$sql_u_perso = "UPDATE perso SET x_perso='$x_train', y_perso='$y_train' WHERE id_perso IN (SELECT id_perso FROM perso_in_train WHERE id_train='$id_instance_train')";
				$mysqli->query($sql_u_perso);
				
				$dep_restant--;
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
				
				// Modification coordonnées instance train
				$sql_t = "UPDATE instance_batiment set x_instance='$x_r', y_instance='$y_r' WHERE id_instanceBat='$id_instance_train'";
				$mysqli->query($sql_t);
				
				$x_train = $x_r;
				$y_train = $y_r;
				
				// Modification carte 
				$sql_c1 = "UPDATE carte SET idPerso_carte=NULL, occupee_carte='0', image_carte=NULL WHERE idPerso_carte='$id_instance_train'";
				$mysqli->query($sql_c1);
				
				$sql_c2 = "UPDATE carte SET idPerso_carte='$id_instance_train', occupee_carte='1', image_carte='$image_train' WHERE x_carte='$x_r' AND y_carte='$y_r'";
				$mysqli->query($sql_c2);
				
				// MAJ coordonnées persos dans le train
				$sql_u_perso = "UPDATE perso SET x_perso='$x_train', y_perso='$y_train' WHERE id_perso IN (SELECT id_perso FROM perso_in_train WHERE id_train='$id_instance_train')";
				$mysqli->query($sql_u_perso);
				
				$dep_restant--;
			}
			
		}
	}
	
	if (est_arrivee($mysqli, $x_train, $y_train, $gare_arrivee)) {
		
		// On remet les PV au train 
		$sql_pv = "UPDATE instance_batiment SET pv_instance=pvMax_instance WHERE id_instanceBat='$id_instance_train'";
		$mysqli->query($sql_pv);
		
		// Récupération des persos dans le train 
		$sql_pt = "SELECT id_perso FROM perso_in_train WHERE id_train='$id_instance_train'";
		$res_pt = $mysqli->query($sql_pt);
		
		while ($t_pt = $res_pt->fetch_assoc()) {
			
			$id_perso_dechargement = $t_pt['id_perso'];
			
			// On le supprime du train
			$sql_dt = "DELETE FROM perso_in_train WHERE id_perso='$id_perso_dechargement'";
			$mysqli->query($sql_dt);
			
			// On décharge le perso dans la gare
			$sql_pg = "INSERT INTO perso_in_batiment VALUES ('$id_perso_dechargement','$gare_arrivee')";
			$mysqli->query($sql_pg);
			
			$sql_p = "UPDATE perso SET x_perso='$x_gare_arrivee', y_perso='$y_gare_arrivee' WHERE id_perso='$id_perso_dechargement'";
			$mysqli->query($sql_p);
			
		}		
		
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
		
		// récupération des persos dans cette gare ayant un ticket pour la nouvelle direction
		$sql_perso_ticket_dest = "SELECT id_perso FROM perso_as_objet 
									WHERE id_objet='1' 
									AND capacite_objet='$nouvelle_direction' 
									AND id_perso IN (SELECT perso_in_batiment.id_perso 
													FROM perso_in_batiment, perso 
													WHERE perso.id_perso = perso_in_batiment.id_perso 
													AND id_instanceBat = '$gare_arrivee' 
													AND clan=$camp_train)";
		$res_perso_ticket_dest = $mysqli->query($sql_perso_ticket_dest);
			
		while ($t_perso_ticket_dest = $res_perso_ticket_dest->fetch_assoc()) {
				
			$id_perso_chargement = $t_perso_ticket_dest['id_perso'];
			
			// On supprime le ticket de l'inventaire
			$sql_delete_ticket = "DELETE FROM perso_as_objet WHERE id_perso='$id_perso_chargement' AND id_objet='1' AND capacite_objet='$nouvelle_direction' LIMIT 1";
			$mysqli->query($sql_delete_ticket);
			
			// On supprime le perso du batiment
			$sql_delete_bat = "DELETE FROM perso_in_batiment WHERE id_perso='$id_perso_chargement'";
			$mysqli->query($sql_delete_bat);
			
			// On charge les persos dans le train
			$sql_chargement_train = "INSERT INTO perso_in_train VALUES ('$id_instance_train','$id_perso_chargement')";
			$mysqli->query($sql_chargement_train);
			
			// MAJ coordonnées perso chargés sur les coordonnées du train 
			$sql_maj_perso = "UPDATE perso SET x_perso='$x_train', y_perso='$y_train' WHERE id_perso='$id_perso_chargement'";
			$mysqli->query($sql_maj_perso);
		}	
	}
}

/**
 * Fonction permettant de determiner si un train est arrivée à destination
 *
 */
function est_arrivee($mysqli, $x_train, $y_train, $gare_arrivee) {
	
	$sql = "SELECT count(*) as cases_gare_arrivee FROM carte 
			WHERE x_carte>=$x_train-1 AND x_carte<=$x_train+1 AND y_carte>=$y_train-1 AND y_carte<=$y_train+1 
			AND idPerso_carte='$gare_arrivee'";
	$res = $mysqli->query($sql);
	$t = $res->fetch_assoc();
	
	$nb_case_gare_arrivee = $t['cases_gare_arrivee'];
	
	if ($nb_case_gare_arrivee > 0) {
		return 1;
	} else {
		return 0;
	}
	
}
?>