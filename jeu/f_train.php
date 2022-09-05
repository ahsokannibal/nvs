<?php

require_once("f_carte.php");

/**
 * Fonction permettant de déplacer le train sur une case x/y
 */
function deplacement_train($mysqli, $id_instance_train, $x_train, $y_train, $image_train, $nom_train, $couleur_camp_train) {
	
	$deplacement_possible = true;
	
	// Modification carte 
	$sql = "UPDATE carte SET idPerso_carte=NULL, occupee_carte='0', image_carte=NULL WHERE idPerso_carte='$id_instance_train'";
	$mysqli->query($sql);
	
	// La case de destination du train est-elle occupée ?
	$sql = "SELECT occupee_carte, idPerso_carte FROM carte WHERE x_carte='$x_train' AND y_carte='$y_train'";
	$res = $mysqli->query($sql);
	$t = $res->fetch_assoc();
	
	$occupee_carte 	= $t["occupee_carte"];
	$idPerso_carte	= $t["idPerso_carte"];
	
	if ($occupee_carte) {
		// Qu'est ce qui occupe la carte ?
		if ($idPerso_carte < 50000) {
			// Perso => on lui roule dessus (PV/2) et on l'ejecte
			
			// Recherche case libre pour ejection					

			$sql = "SELECT MAX(x_carte) as x_max, MAX(y_carte) as y_max FROM carte";
			$res = $mysqli->query($sql);
			$t = $res->fetch_assoc();
			$X_MAX = $t['x_max'];
			$Y_MAX  = $t['y_max'];

			// cherche case libre
			$verif_occ = 0;
			$seek = 1;
			$x_libre = $x_train;
			$y_libre = $y_train;
			while (!$verif_occ && $seek <= 5) {
				while (!$verif_occ) {
					$x = pos_zone_rand_x($x_train-$seek, $x_train+$seek); 
					$y = pos_zone_rand_y($y_train-$seek, $y_train+$seek);
					$verif_occ = verif_position_libre($mysqli, $x, $y, $X_MAX, $Y_MAX);
					if ($verif_occ) {
						$x_libre = $x;
						$y_libre = $y;
					}
				}
				$seek++;
			}
			
			// MAJ perso
			$sql = "UPDATE perso SET pv_perso = pv_perso/2, x_perso=$x_libre, y_perso=$y_libre WHERE id_perso='$idPerso_carte'";
			$mysqli->query($sql);
			
			// Récupération infos perso 
			$sql = "SELECT nom_perso, image_perso, clan, pv_perso FROM perso WHERE id_perso='$idPerso_carte'";
			$res = $mysqli->query($sql);
			$t_p = $res->fetch_assoc();
			
			$nom_perso		= $t_p["nom_perso"];
			$camp_perso		= $t_p["clan"];
			$image_perso	= $t_p["image_perso"];
			$pv_perso		= $t_p["pv_perso"];
			
			// MAJ carte
			$sql = "UPDATE carte SET occupee_carte='1', image_carte='$image_perso' ,idPerso_carte='$idPerso_carte' WHERE x_carte = '$x_libre' AND y_carte = '$y_libre'";
			$mysqli->query($sql);
			
			if($camp_perso == 1) {
				$couleur_camp_perso = "blue";
			}
			else {
				$couleur_camp_perso = "red";
			}
			
			// MAJ evenements perso
			$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) 
					VALUES ($id_instance_train,'<font color=$couleur_camp_train><b>Train $nom_train</b></font>','<b>a roulé sur </b>','$idPerso_carte','<font color=$couleur_camp_perso><b>$nom_perso</b></font>',' : pv = $pv_perso (pv/2) - case après expulsion : $x_libre / $y_libre',NOW(),'0')";
			$mysqli->query($sql);
		}
		else if ($idPerso_carte >= 50000 && $idPerso_carte < 200000) {
			// Batiment
			$deplacement_possible = false;
		}
		else {
			// PNJ => on lui roule dessus (PV/2) et on l'ejecte
			
			// Recherche case libre pour ejection					

			$sql = "SELECT MAX(x_carte) as x_max, MAX(y_carte) as y_max FROM carte";
			$res = $mysqli->query($sql);
			$t = $res->fetch_assoc();
			$X_MAX = $t['x_max'];
			$Y_MAX  = $t['y_max'];

			// cherche case libre
			$verif_occ = 0;
			$seek = 1;
			$x_libre = $x_train;
			$y_libre = $y_train;
			while (!$verif_occ && $seek <= 5) {
				while (!$verif_occ) {
					$x = pos_zone_rand_x($x_train-$seek, $x_train+$seek); 
					$y = pos_zone_rand_y($y_train-$seek, $y_train+$seek);
					$verif_occ = verif_position_libre($mysqli, $x, $y, $X_MAX, $Y_MAX);
					if ($verif_occ) {
						$x_libre = $x;
						$y_libre = $y;
					}
				}
				$seek++;
			}

			// MAJ pnj
			$sql = "UPDATE instance_pnj SET pv_i = pv_i/2, x_i=$x_libre, y_i=$y_libre WHERE idInstance_pnj='$idPerso_carte'";
			$mysqli->query($sql);
			
			// Récupération infos pnj 
			$sql = "SELECT instance_pnj.id_pnj, nom_pnj, pv_i FROM instance_pnj, pnj 
					WHERE instance_pnj.id_pnj = pnj.id_pnj
					AND idInstance_pnj='$idPerso_carte'";
			$res = $mysqli->query($sql);
			$t_p = $res->fetch_assoc();
			
			$pv_pnj		= $t_p["pv_i"];
			$nom_pnj	= $t_p["nom_pnj"];
			$id_pnj		= $t_p["id_pnj"];
			$image_pnj	= "pnj".$id_pnj."t.png";
			
			// MAJ carte
			$sql = "UPDATE carte SET occupee_carte='1', image_carte='$image_pnj' ,idPerso_carte='$idPerso_carte' WHERE x_carte = '$x_libre' AND y_carte = '$y_libre'";
			$mysqli->query($sql);
			
			// MAJ evenements pnj
			$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) 
					VALUES ($id_instance_train,'<font color=$couleur_camp_train><b>Train $nom_train</b></font>','<b>a roulé sur </b>','$idPerso_carte','<b>$nom_pnj</b>',' : pv = $pv_pnj (pv/2) - case après expulsion : $x_libre / $y_libre',NOW(),'0')";
			$mysqli->query($sql);
		}
	}
	
	if ($deplacement_possible) {
	
		$sql = "UPDATE carte SET idPerso_carte='$id_instance_train', occupee_carte='1', image_carte='$image_train' WHERE x_carte='$x_train' AND y_carte='$y_train'";
		$mysqli->query($sql);
		
		$sql_e = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) 
					VALUES ($id_instance_train,'Train','s\'est déplacé',NULL,'','en $x_train/$y_train',NOW(),'0')";
		$mysqli->query($sql_e);
			
		// MAJ coordonnées persos dans le train
		$sql = "UPDATE perso SET x_perso='$x_train', y_perso='$y_train' WHERE id_perso IN (SELECT id_perso FROM perso_in_train WHERE id_train='$id_instance_train')";
		$mysqli->query($sql);
	}
	
	return $deplacement_possible;
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

/**
 * Fonction permettant de décharger les persos d'un train dans une gare
 */
function dechargement_persos_train($mysqli, $id_instance_train, $gare_arrivee, $x_gare_arrivee, $y_gare_arrivee) {
	
	// Récupération des persos dans le train 
	$sql_pt = "SELECT DISTINCT(id_perso) FROM perso_in_train WHERE id_train='$id_instance_train'";
	$res_pt = $mysqli->query($sql_pt);
	
	while ($t_pt = $res_pt->fetch_assoc()) {
		
		$id_perso_dechargement = $t_pt['id_perso'];
		
		dechargement_perso_train($mysqli, $id_perso_dechargement, $gare_arrivee, $x_gare_arrivee, $y_gare_arrivee);
		
	}
}

/**
 * Fonction permettant de décharger un perso d'un train dans une gare
 */
function dechargement_perso_train($mysqli, $id_perso_dechargement, $gare_arrivee, $x_gare_arrivee, $y_gare_arrivee) {
	
	// On le supprime du train
	$sql_dt = "DELETE FROM perso_in_train WHERE id_perso='$id_perso_dechargement'";
	$mysqli->query($sql_dt);
	
	// On décharge le perso dans la gare
	$sql_pg = "INSERT INTO perso_in_batiment VALUES ('$id_perso_dechargement','$gare_arrivee')";
	$mysqli->query($sql_pg);
	
	$sql_p = "UPDATE perso SET x_perso='$x_gare_arrivee', y_perso='$y_gare_arrivee' WHERE id_perso='$id_perso_dechargement'";
	$mysqli->query($sql_p);
}


/**
 * Fonction permettant de charger les persos dans le train si ils ont un ticket
 */
function chargement_persos_train($mysqli, $id_instance_train, $x_train, $y_train, $nouvelle_direction, $gare_arrivee, $camp_train) {
	
	// récupération des persos dans cette gare ayant un ticket pour la nouvelle direction
	$sql_perso_ticket_dest = "SELECT DISTINCT(id_perso) FROM perso_as_objet 
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
		
		chargement_perso_train($mysqli, $id_perso_chargement, $id_instance_train, $x_train, $y_train, $nouvelle_direction);
	}	
}

/**
 * Fonction permettant de charger un perso dans le train
 */
function chargement_perso_train($mysqli, $id_perso_chargement, $id_instance_train, $x_train, $y_train, $nouvelle_direction) {
	
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

/**
 * Fonction permettant de gérer le compteur de blocage du train
 */
function gestion_blocage_train($mysqli, $id_instance_train, $idPerso_carte, $x_r, $y_r) {
	
	// Est ce qu'il y a déjà une ligne de compteur de blocage ?
	$sql = "SELECT * FROM train_compteur_blocage WHERE id_train='$id_instance_train'";
	$res = $mysqli->query($sql);
	$nb = $res->num_rows;
	
	if ($nb > 0 && $nb < 23) {
		
		$t = $res->fetch_assoc();
		$compteur = $t['compteur'];
		
		if ($compteur >= 0 && $compteur < 23) {
			// On augmente le compteur de 1
			$sql = "UPDATE train_compteur_blocage SET compteur = compteur+1 WHERE id_train = '$id_instance_train'";
			$mysqli->query($sql);
		}
		else if ($compteur >= 23) {
			// mise à jour de la carte
			$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_r' AND y_carte='$y_r'";
			$mysqli->query($sql);
			
			// On supprime la barricade qui bloque
			$sql = "DELETE FROM instance_batiment WHERE id_instanceBat='$idPerso_carte'";
			$mysqli->query($sql);
			
			// On supprime la ligne de compteur
			$sql = "DELETE FROM train_compteur_blocage WHERE id_train='$id_instance_train'";
			$mysqli->query($sql);
		}
	}
	else {
		// On créé la ligne
		$sql = "INSERT INTO train_compteur_blocage (id_train, compteur, date_debut_blocage) VALUES ('$id_instance_train', 0, NOW())";
		$mysqli->query($sql);
	}
}

/**
 * Fonction qui permet de supprimer la ligne de compteur de blocage si elle existe
 */
function suppression_compteur_blocage($mysqli, $id_instance_train) {
	
	// Est ce qu'il y a déjà une ligne de compteur de blocage ?
	$sql = "SELECT * FROM train_compteur_blocage WHERE id_train='$id_instance_train'";
	$res = $mysqli->query($sql);
	$nb = $res->num_rows;
	
	if ($nb > 0) {
		// On supprime la ligne de compteur
		$sql = "DELETE FROM train_compteur_blocage WHERE id_train='$id_instance_train'";
		$mysqli->query($sql);
	}
}
?>
