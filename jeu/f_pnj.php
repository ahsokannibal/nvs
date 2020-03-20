<?php
require_once("f_carte.php");

$mysqli = db_connexion();

// fonction qui retourne l'id du perso collé au pnj, 0 sinon
function proxi_perso($mysqli, $x_pnj, $y_pnj){
	
	$id_pj = 0;
	
	$sql = "SELECT idPerso_carte, occupee_carte FROM carte WHERE x_carte>=$x_pnj-1 AND x_carte<=$x_pnj+1 AND y_carte>=$y_pnj-1 AND y_carte<=$y_pnj+1";
	$res = $mysqli->query($sql);
	
	while ($t_pj = $res->fetch_assoc()) {
		
		$oc = $t_pj["occupee_carte"];
		
		if($oc) {
			
			// si c'est bien un perso (pas un pnj ou un bat)
			if($t_pj["idPerso_carte"] < 50000) {
				$id_pj = $t_pj["idPerso_carte"];
			}
		}
	}
	return $id_pj;
}

// fonction qui retourne l'id du perso a proximité du pnj afin de vérifier si celui ci est bien sa cible ou non
function proxi_perso_cible($mysqli, $x_pnj, $y_pnj, $id_cible){
	
	$id_pj = 0;
	
	$sql = "SELECT idPerso_carte, occupee_carte FROM carte WHERE x_carte>=$x_pnj-1 AND x_carte<=$x_pnj+1 AND y_carte>=$y_pnj-1 AND y_carte<=$y_pnj+1";
	$res = $mysqli->query($sql);
	
	while ($t_pj = $res->fetch_assoc()) {
		
		$oc = $t_pj["occupee_carte"];
		
		if($oc) {
			
			// si c'est bien la cible
			if($t_pj["idPerso_carte"] == $id_cible) {
				$id_pj = $t_pj["idPerso_carte"];
			}
		}
	}
	return $id_pj;
}

// fonction qui recupere l'id du perso le plus proche du pnj et qui est dans sa visu
function proche_perso($mysqli, $x_pnj, $y_pnj, $perception_pnj) {
	
	$id_pj = 0;
	
	$sql = "SELECT idPerso_carte, occupee_carte FROM carte 
			WHERE x_carte>=$x_pnj-$perception_pnj AND x_carte<=$x_pnj+$perception_pnj AND y_carte>=$y_pnj-$perception_pnj AND y_carte<=$y_pnj+$perception_pnj";
	$res = $mysqli->query($sql);
	
	while ($t_proche = $res->fetch_assoc()){
		
		$occupee = $t_proche["occupee_carte"];
		
		if($occupee) {
			// si c'est bien un perso (pas un pnj ou un bat)
			if($t_proche["idPerso_carte"] < 50000){ 
				$id_pj = $t_proche["idPerso_carte"];
				break;
			}
		}
	}
	return $id_pj;
}

// fonction qui regarde si un perso particulier est dans la visu du pnj
function perso_visu_pnj($mysqli, $x_pnj, $y_pnj, $perception_pnj, $id_perso) {
	
	$ok = 0;
	$id_p = 0;
	
	// on recupere tout les perso dans la visu du pnj
	$sql = "SELECT idPerso_carte, occupee_carte FROM carte WHERE x_carte>=$x_pnj-$perception_pnj AND x_carte<=$x_pnj+$perception_pnj AND y_carte>=$y_pnj-$perception_pnj AND y_carte<=$y_pnj+$perception_pnj";
	$res = $mysqli->query($sql);
	
	while ($t_perso = $res->fetch_assoc()){
		
		$occupee = $t_perso["occupee_carte"];
		
		if($occupee) {
			
			// si c'est bien un perso (pas un pnj) ou un batiment
			if($t_perso["idPerso_carte"] < 50000){
				
				$id_pj = $t_perso["idPerso_carte"];
				
				if ($id_pj == $id_perso){
					
					$id_p = $id_pj;
					$ok = 1;
					
					break;
				}
			}
		}
	}
	
	return $id_p;
}

// fonction de calcul de la coordonnée x du vecteur de depacement
function calcul_vecteur_x($x_pnj,$x_pj) {
	return $x_pj-$x_pnj;
}

// fonction de calcule de la coordonnée y du vecteur de deplacement
function calcul_vecteur_y($y_pnj,$y_pj) {
	return $y_pj-$y_pnj;
}

// fonction de deplacement d'un pnj en fuite
function deplacement_fuite($mysqli, $x_d, $y_d, $x_pj, $y_pj, $pm_pnj, $id_pnj, $nom_pnj, $type_pnj) {
	
	echo "deplacement_fuite<br>";

	$image_pnj = "pnj".$type_pnj."t.png";	
	
	// il faut se deplacer vers la gauche
	if ($x_d > 0){
		
		while ($pm_pnj) {
			
			// on recupere les coordonnées du pnj
			$sql = "SELECT x_i, y_i FROM instance_pnj WHERE idInstance_pnj=$id_pnj";
			$res = $mysqli->query($sql);
			$t_coor = $res->fetch_assoc();
			
			$x_pnj = $t_coor["x_i"];
			$y_pnj = $t_coor["y_i"];
			
			// on regarde si la case a gauche du pnj est libre
			$sql = "SELECT occupee_carte FROM carte WHERE x_carte=$x_pnj-1 AND y_carte=$y_pnj";
			$res = $mysqli->query($sql);
			$t_oc = $res->fetch_assoc();
			
			$oc = $t_oc["occupee_carte"];
			
			// la case est occupee
			if($oc || (!in_map($x_pnj-1,$y_pnj))) {
				
				// on cherche une case libre autour du pnj en partant du haut jusqu'en bas et seulement du coté de la fuite
				$sql = "SELECT occupee_carte,x_carte,y_carte FROM carte WHERE x_carte<=$x_pnj AND x_carte>=$x_pnj-1 AND y_carte>=$y_pnj-1 AND y_carte<=$y_pnj+1";
				$res = $mysqli->query($sql);
				
				while ($t_o = $res->fetch_assoc()){
					
					$occupee = $t_o["occupee_carte"];
					
					// la case est libre
					if(!$occupee && (in_map($t_o["x_carte"],$t_o["y_carte"]))) {
						$x_ok = $t_o["x_carte"];
						$y_ok = $t_o["y_carte"];
						
						break;
					}
				}
				
				// on a trouvé une case libre
				if(isset($x_ok) && isset($y_ok)) {
					
					// on s'y deplace
					$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_pnj' AND y_carte='$y_pnj'";
					$mysqli->query($sql);
						
					$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte=$id_pnj, image_carte='$image_pnj' WHERE x_carte='$x_ok' AND y_carte='$y_ok'";
					$mysqli->query($sql);
						
					$sql = "UPDATE instance_pnj SET x_i=$x_ok, y_i=$y_ok WHERE idInstance_pnj='$id_pnj'";
					$mysqli->query($sql);
					//echo " deplacement en $x_ok/$y_ok ";
						
					// MAJ des evenements
					$sql = "INSERT INTO evenement (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES('$id_pnj','<b>$nom_pnj</b>','s\'est deplacé',NULL,'','en $x_ok/$y_ok',now(),'0')";
					$mysqli->query($sql);
						
					// MAJ pm du pnj
					$pm_pnj--;
				}
				else {
					// on a pas trouvé de case libre
					//echo "Le pnj est bloqué<br>";
					$pm_pnj=0;
				}
			}
			else {
				// la case est libre
				// on s'y deplace
				if(in_map($x_pnj-1,$y_pnj)){
				
					$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_pnj' AND y_carte='$y_pnj'";
					$mysqli->query($sql);
							
					$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte=$id_pnj, image_carte='$image_pnj' WHERE x_carte=$x_pnj-1 AND y_carte='$y_pnj'";
					$mysqli->query($sql);
						
					$sql = "UPDATE instance_pnj SET x_i=$x_pnj-1, y_i=$y_pnj WHERE idInstance_pnj='$id_pnj'";
					$mysqli->query($sql);
						
					$x_pnjj = $x_pnj-1;
						
					// MAJ des evenements
					$sql = "INSERT INTO evenement (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES('$id_pnj','<b>$nom_pnj</b>','s\'est deplacé',NULL,'','en $x_pnjj/$y_pnj',now(),'0')";
					$mysqli->query($sql);
						
					// MAJ pm du pnj
					$pm_pnj--;
				}
				else {
					// on a pas trouvé de case libre
					echo "bizarre<br>";
					break;
				}
			}
		}
	}
	
	//  deplacement vers la droite
	if ($x_d < 0){
		
		while ($pm_pnj) {
			
			// on recupere les coordonnées du pnj
			$sql = "SELECT x_i, y_i FROM instance_pnj WHERE idInstance_pnj='$id_pnj'";
			$res = $mysqli->query($sql);
			$t_coor = $res->fetch_assoc();
			
			$x_pnj = $t_coor["x_i"];
			$y_pnj = $t_coor["y_i"];
			
			// on regarde si la case a droite du pnj est libre
			$sql = "SELECT occupee_carte FROM carte WHERE x_carte=$x_pnj+1 AND y_carte='$y_pnj'";
			$res = $mysqli->query($sql);
			$t_oc = $res->fetch_assoc();
			
			$oc = $t_oc["occupee_carte"];
			
			// la case est occupee
			if($oc || (!in_map($x_pnj+1,$y_pnj))) {
				
				// on cherche une case libre autour du pnj en partant du haut jusqu'en bas et seulement du coté de la fuite
				$sql = "SELECT occupee_carte,x_carte,y_carte FROM carte WHERE x_carte<=$x_pnj AND x_carte>=$x_pnj+1 AND y_carte>=$y_pnj-1 AND y_carte<=$y_pnj+1";
				$res = $mysqli->query($sql);
				
				while ($t_o = $res->fetch_assoc()){
					
					$occupee = $t_o["occupee_carte"];
					
					// la case est libre
					if(!$occupee && (in_map($t_o["x_carte"],$t_o["y_carte"]))) {
						$x_ok = $t_o["x_carte"];
						$y_ok = $t_o["y_carte"];
						
						break;
					}
				}
				
				// on a trouvé une case libre
				if(isset($x_ok) && isset($y_ok)) {
					
					// on s'y deplace
					$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_pnj' AND y_carte='$y_pnj'";
					$mysqli->query($sql);
						
					$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte=$id_pnj, image_carte='$image_pnj' WHERE x_carte='$x_ok' AND y_carte='$y_ok'";
					$mysqli->query($sql);
						
					$sql = "UPDATE instance_pnj SET x_i=$x_ok, y_ij=$y_ok WHERE idInstance_pnj='$id_pnj'";
					$mysqli->query($sql);
					
					// MAJ des evenements
					$sql = "INSERT INTO evenement (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES('$id_pnj','<b>$nom_pnj</b>','s\'est deplacé',NULL,'','en $x_ok/$y_ok',now(),'0')";
					$mysqli->query($sql);
						
					// MAJ pm du pnj
					$pm_pnj--;
				}
				else {
					// on a pas trouvé de case libre
					//echo "Le pnj est bloqué2<br>";
					$pm_pnj=0;
				}
			}
			else {
				// la case est libre
				// on s'y deplace
				if(in_map($x_pnj+1,$y_pnj)){
				
					$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_pnj' AND y_carte='$y_pnj'";
					$mysqli->query($sql);
							
					$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte=$id_pnj, image_carte='$image_pnj' WHERE x_carte=$x_pnj+1 AND y_carte='$y_pnj'";
					$mysqli->query($sql);
							
					$sql = "UPDATE instance_pnj SET x_i=$x_pnj+1, y_i=$y_pnj WHERE idInstance_pnj='$id_pnj'";
					$mysqli->query($sql);
						
					$x_pnjj = $x_pnj+1;
					//echo "deplacement en $x_pnjj/$y_pnj<br>";
							
					// MAJ des evenements
					$sql = "INSERT INTO evenement (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES('$id_pnj','<b>$nom_pnj</b>','s\'est deplacé',NULL,'','en $x_pnjj/$y_pnj',now(),'0')";
					$mysqli->query($sql);
							
					// MAJ pm du pnj
					$pm_pnj--;
				}
				else { 
					// on a pas trouvé de case libre
					echo "bizare<br>";
					break;
				}
			}
		}
	}
	
	// pnj se trouve sur le meme x que le pj
	if($x_d==0){
		
		// deplacement vers le haut
		if($y_d < 0) {
			
			while ($pm_pnj) {
				
				// on recupere les coordonnées du pnj
				$sql = "SELECT x_i, y_i FROM instance_pnj WHERE idInstance_pnj='$id_pnj'";
				$res = $mysqli->query($sql);
				$t_coor = $res->fetch_assoc();
				
				$x_pnj = $t_coor["x_i"];
				$y_pnj = $t_coor["y_i"];
			
				// on regarde si la case en haut du pnj est libre
				$sql = "SELECT occupee_carte FROM carte WHERE x_carte=$x_pnj AND y_carte=$y_pnj+1";
				$res = $mysqli->query($sql);
				$t_oc = $res->fetch_assoc();
				
				$oc = $t_oc["occupee_carte"];
				
				// la case est occupee
				if($oc || (!in_map($x_pnj,$y_pnj+1))) {
					
					// on cherche une case libre autour du pnj en partant de la gauche vers la droite et seulement du coté de la fuite
					$sql = "SELECT occupee_carte,x_carte,y_carte FROM carte WHERE x_carte>=$x_pnj-1 AND x_carte<=$x_pnj+1 AND y_carte>=$y_pnj AND y_carte<=$y_pnj+1";
					$res = $mysqli->query($sql);
					
					while ($t_o = $res->fetch_assoc()){
						
						$occupee = $t_o["occupee_carte"];
						
						// la case est libre
						if(!$occupee && (in_map($t_o["x_carte"],$t_o["y_carte"]))) {
							$x_ok = $t_o["x_carte"];
							$y_ok = $t_o["y_carte"];
							
							break;
						}
					}
					
					// on a trouvé une case libre
					if(isset($x_ok) && isset($y_ok)) {
						
						// on s'y deplace
						$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_pnj' AND y_carte='$y_pnj'";
						$mysqli->query($sql);
							
						$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte=$id_pnj, image_carte='$image_pnj' WHERE x_carte='$x_ok' AND y_carte='$y_ok'";
						$mysqli->query($sql);
						
						$sql = "UPDATE instance_pnj SET x_i=$x_ok, y_i=$y_ok WHERE idInstance_pnj='$id_pnj'";
						$mysqli->query($sql);
						
						// MAJ des evenements
						$sql = "INSERT INTO evenement (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES('$id_pnj','<b>$nom_pnj</b>','s\'est deplacé',NULL,'','en $x_ok/$y_ok',now(),'0')";
						$mysqli->query($sql);
							
						// MAJ pm du pnj
						$pm_pnj--;
					}
					else {
						// on a pas trouvé de case libre
						//echo "Le pnj est bloqué3<br>";
						$pm_pnj=0;
					}
				}
				else { 
					// la case est libre
					if(in_map($x_pnj,$y_pnj+1)){
					
						// on s'y deplace
						$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_pnj' AND y_carte='$y_pnj'";
						$mysqli->query($sql);
								
						$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte=$id_pnj, image_carte='$image_pnj' WHERE x_carte='$x_pnj' AND y_carte=$y_pnj+1";
						$mysqli->query($sql);
								
						$sql = "UPDATE instance_pnj SET x_i=$x_pnj, y_i=$y_pnj+1 WHERE idInstance_pnj='$id_pnj'";
						$mysqli->query($sql);
							
						$y_pnjj = $y_pnj+1;
						//echo "deplacement en $x_pnj/$y_pnjj<br>";	
						
						// MAJ des evenements
						$sql = "INSERT INTO evenement (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES('$id_pnj','<b>$nom_pnj</b>','s\'est deplacé',NULL,'','en $x_pnj/$y_pnjj',now(),'0')";
						$mysqli->query($sql);
								
						// MAJ pm du pnj
						$pm_pnj--;
					}
					else {
						// on a pas trouvé de case libre
						echo "Bizare<br>";
						break;
					}
				}
			}
		}
		else { 
			// deplacement vers le bas
			while ($pm_pnj) {
				// on recupere les coordonnées du pnj
				$sql = "SELECT x_i, y_i FROM instance_pnj WHERE idInstance_pnj='$id_pnj'";
				$res = $mysqli->query($sql);
				$t_coor = $res->fetch_assoc();
				
				$x_pnj = $t_coor["x_i"];
				$y_pnj = $t_coor["y_i"];
				
				// on regarde si la case en bas du pnj est libre
				$sql = "SELECT occupee_carte FROM carte WHERE x_carte='$x_pnj' AND y_carte=$y_pnj-1";
				$res = $mysqli->query($sql);
				$t_oc = $res->fetch_assoc();
				
				$oc = $t_oc["occupee_carte"];
				
				// la case est occupee
				if($oc || (!in_map($x_pnj,$y_pnj-1))) {
					
					// on cherche une case libre autour du pnj en partant de la gauche vers la droite et seulement du coté de la fuite
					$sql = "SELECT occupee_carte,x_carte,y_carte FROM carte WHERE x_carte>=$x_pnj-1 AND x_carte<=$x_pnj+1 AND y_carte<=$y_pnj AND y_carte>=$y_pnj-1";
					$res = $mysqli->query($sql);
					
					while ($t_o = $res->fetch_assoc()){
						
						$occupee = $t_o["occupee_carte"];
						
						// la case est libre
						if(!$occupee && (in_map($t_o["x_carte"],$t_o["y_carte"]))) {
							$x_ok = $t_o["x_carte"];
							$y_ok = $t_o["y_carte"];
							
							break;
						}
					}
					
					// on a trouvé une case libre
					if(isset($x_ok) && isset($y_ok)) {
						
						// on s'y deplace
						$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_pnj' AND y_carte='$y_pnj'";
						$mysqli->query($sql);
						
						$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte=$id_pnj, image_carte='$image_pnj' WHERE x_carte='$x_ok' AND y_carte='$y_ok'";
						$mysqli->query($sql);
						
						$sql = "UPDATE instance_pnj SET x_i=$x_ok, y_i=$y_ok WHERE idInstance_pnj='$id_pnj'";
						$mysqli->query($sql);
						//echo "deplacement en $x_ok/$y_ok<br>";
						
						// MAJ des evenements
						$sql = "INSERT INTO evenement (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES('$id_pnj','<b>$nom_pnj</b>','s\'est deplacé',NULL,'','en $x_ok/$y_ok',now(),'0')";
						$mysqli->query($sql);
							
						// MAJ pm du pnj
						$pm_pnj--;
					}
					else {
						// on a pas trouvé de case libre
						//echo "Le pnj est bloqué4<br>";
						$pm_pnj=0;
					}
				}
				else {
					// la case est libre
					if(in_map($x_pnj,$y_pnj-1)){
						
						// on s'y deplace
						$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_pnj' AND y_carte='$y_pnj'";
						$mysqli->query($sql);
								
						$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte=$id_pnj, image_carte='$image_pnj' WHERE x_carte='$x_pnj' AND y_carte=$y_pnj-1";
						$mysqli->query($sql);
								
						$sql = "UPDATE instance_pnj SET x_i=$x_pnj, y_i=$y_pnj-1 WHERE idInstance_pnj='$id_pnj'";
						$mysqli->query($sql);
							
						$y_pnjj = $y_pnj-1;
								
						// MAJ des evenements
						$sql = "INSERT INTO evenement (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES('$id_pnj','<b>$nom_pnj</b>','s\'est deplacé',NULL,'','en $x_pnj/$y_pnjj',now(),'0')";
						$mysqli->query($sql);
							
						// MAJ pm du pnj
						$pm_pnj--;
					}
					else {
						// on a pas trouvé de case libre
						echo "Bizare<br>";
						break;
					}		
				}
			}	
		}
	}
	
	// maj deplacement_pnj
	$sql = "UPDATE instance_pnj SET deplace_i='1' WHERE idInstance_pnj=$id_pnj";
	$mysqli->query($sql);
}

// fonction de deplacement de pnj vers sa cible
function deplacement_vers_cible($mysqli, $x_d, $y_d, $x_cible, $y_cible, $id_i_pnj, $nom_pnj, $type_pnj, $id_cible){
	
	echo "deplacement_vers_cible<br>";

	$image_pnj = "pnj".$type_pnj."t.png";	
	
	if ($x_d < 0){
		
		if($y_d > 0) {
			
			echo "deplacement vers la diagonale haut-gauche<br>";
			// on recupere les coordonnées du pnj
			$sql = "SELECT x_i, y_i FROM instance_pnj WHERE idInstance_pnj=$id_i_pnj";
			$res = $mysqli->query($sql);
			$t_coor = $res->fetch_assoc();
			
			$x_pnj = $t_coor["x_i"];
			$y_pnj = $t_coor["y_i"];
				
			// on regarde si la case a la diagonale haut-gauche du pnj est libre
			$sql = "SELECT occupee_carte FROM carte WHERE x_carte=$x_pnj-1 AND y_carte=$y_pnj+1";
			$res = $mysqli->query($sql);
			$t_oc = $res->fetch_assoc();
			
			$oc = $t_oc["occupee_carte"];
			
			// la case est occupee
			if($oc || (!in_map($x_pnj-1,$y_pnj+1))) {
				
				// on cherche une case libre autour du pnj en partant du haut jusqu'en bas et seulement du coté de la fuite
				$sql = "SELECT occupee_carte,x_carte,y_carte FROM carte WHERE x_carte<=$x_pnj AND x_carte>=$x_pnj-1 AND y_carte>=$y_pnj AND y_carte<=$y_pnj+1";
				$res = $mysqli->query($sql);
				
				while ($t_o = $res->fetch_assoc()){
					
					$occupee = $t_o["occupee_carte"];
					
					// la case est libre
					if(!$occupee && (in_map($t_o["x_carte"],$t_o["y_carte"]))) {
						
						$x_ok = $t_o["x_carte"];
						$y_ok = $t_o["y_carte"];
						
						break;
					}
				}
				
				// on a trouvé une case libre
				if(isset($x_ok) && isset($y_ok)) {
					
					// on s'y deplace
					$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte=$x_pnj AND y_carte=$y_pnj";
					$mysqli->query($sql);
							
					$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte=$id_i_pnj, image_carte='$image_pnj' WHERE x_carte=$x_ok AND y_carte=$y_ok";
					$mysqli->query($sql);
							
					$sql = "UPDATE instance_pnj SET x_i=$x_ok, y_i=$y_ok WHERE idInstance_pnj=$id_i_pnj";
					$mysqli->query($sql);
							
					// MAJ des evenements
					$sql = "INSERT INTO evenement (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES('$id_i_pnj','<b>$nom_pnj</b>','s\'est deplacé',NULL,'','en $x_ok/$y_ok',now(),'0')";
					$mysqli->query($sql);

				}
				else {
					// on a pas trouvé de case libre
					echo "Le pnj est bloqué<br>";
				}
			}
			else {
				// on s'y deplace
				if(in_map($x_pnj-1,$y_pnj+1)){
				
					$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte=$x_pnj AND y_carte=$y_pnj";
					$mysqli->query($sql);
								
					$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte=$id_i_pnj, image_carte='$image_pnj' WHERE x_carte=$x_pnj-1 AND y_carte=$y_pnj+1";
					$mysqli->query($sql);
							
					$sql = "UPDATE instance_pnj SET x_i=$x_pnj-1, y_i=$y_pnj+1 WHERE idInstance_pnj=$id_i_pnj";
					$mysqli->query($sql);
							
					$x_pnjj = $x_pnj-1;
					$y_pnjj = $y_pnj+1;
							
					// MAJ des evenements
					$sql = "INSERT INTO evenement (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES('$id_i_pnj','<b>$nom_pnj</b>','s\'est deplacé',NULL,'','en $x_pnjj/$y_pnjj',now(),'0')";
					$mysqli->query($sql);

				}
				else {
					// on a pas trouvé de case libre
					echo "Le pnj est bloqué<br>";
				}
			}
		}
		
		if ($y_d < 0) {
			
			echo "deplacement vers la diagonale bas-gauche<br>";	
			// on recupere les coordonnées du pnj
			$sql = "SELECT x_i, y_i FROM instance_pnj WHERE idInstance_pnj=$id_i_pnj";
			$res = $mysqli->query($sql);
			$t_coor = $res->fetch_assoc();
			
			$x_pnj = $t_coor["x_i"];
			$y_pnj = $t_coor["y_i"];
				
			// on regarde si la case a la diagonale bas-gauche du pnj est libre
			$sql = "SELECT occupee_carte FROM carte WHERE x_carte=$x_pnj-1 AND y_carte=$y_pnj-1";
			$res = $mysqli->query($sql);
			$t_oc = $res->fetch_assoc();
			
			$oc = $t_oc["occupee_carte"];
			
			// la case est occupee
			if($oc || (!in_map($x_pnj-1,$y_pnj-1))) {
				
				// on cherche une case libre autour du pnj en partant du haut jusqu'en bas et seulement du coté de la fuite
				$sql = "SELECT occupee_carte,x_carte,y_carte FROM carte WHERE x_carte<=$x_pnj AND x_carte>=$x_pnj-1 AND y_carte>=$y_pnj-1 AND y_carte<=$y_pnj";
				$res = $mysqli->query($sql);
				
				while ($t_o = $res->fetch_assoc()){
					
					$occupee = $t_o["occupee_carte"];
					
					// la case est libre
					if(!$occupee && (in_map($t_o["x_carte"],$t_o["y_carte"]))) {
						
						$x_ok = $t_o["x_carte"];
						$y_ok = $t_o["y_carte"];
						
						break;
					}
				}
				
				// on a trouvé une case libre
				if(isset($x_ok) && isset($y_ok)) {
					// on s'y deplace
					$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte=$x_pnj AND y_carte=$y_pnj";
					$mysqli->query($sql);
							
					$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte=$id_i_pnj, image_carte='$image_pnj' WHERE x_carte=$x_ok AND y_carte=$y_ok";
					$mysqli->query($sql);
							
					$sql = "UPDATE instance_pnj SET x_i=$x_ok, y_i=$y_ok WHERE idInstance_pnj=$id_i_pnj";
					$mysqli->query($sql);
							
					// MAJ des evenements
					$sql = "INSERT INTO evenement (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES('$id_i_pnj','<b>$nom_pnj</b>','s\'est deplacé',NULL,'','en $x_ok/$y_ok',now(),'0')";
					$mysqli->query($sql);
				}
				else {
					// on a pas trouvé de case libre
					echo "Le pnj est bloqué<br>";
				}
			}
			else {
				// on s'y deplace
				if(in_map($x_pnj-1,$y_pnj-1)){
						
					$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte=$x_pnj AND y_carte=$y_pnj";
					$mysqli->query($sql);
									
					$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte=$id_i_pnj, image_carte='$image_pnj' WHERE x_carte=$x_pnj-1 AND y_carte=$y_pnj-1";
					$mysqli->query($sql);
								
					$sql = "UPDATE instance_pnj SET x_i=$x_pnj-1, y_i=$y_pnj-1 WHERE idInstance_pnj=$id_i_pnj";
					$mysqli->query($sql);
								
					$x_pnjj = $x_pnj-1;
					$y_pnjj = $y_pnj-1;
								
					// MAJ des evenements
					$sql = "INSERT INTO evenement (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES('$id_i_pnj','<b>$nom_pnj</b>','s\'est deplacé',NULL,'','en $x_pnjj/$y_pnjj',now(),'0')";
					$mysqli->query($sql);	
				}
				else {
					// on a pas trouvé de case libre
					echo "Le pnj est bloqué<br>";
				}
			}
		}
		
		if($y_d == 0) {
			
			echo "deplacement vers la gauche<br>";
			// on recupere les coordonnées du pnj
			$sql = "SELECT x_i, y_i FROM instance_pnj WHERE idInstance_pnj=$id_i_pnj";
			$res = $mysqli->query($sql);
			$t_coor = $res->fetch_assoc();
			
			$x_pnj = $t_coor["x_i"];
			$y_pnj = $t_coor["y_i"];
			
			// on regarde si la case a gauche du pnj est libre
			$sql = "SELECT occupee_carte FROM carte WHERE x_carte=$x_pnj-1 AND y_carte=$y_pnj";
			$res = $mysqli->query($sql);
			$t_oc = $res->fetch_assoc();
			
			$oc = $t_oc["occupee_carte"];
			
			// la case est occupee
			if($oc || (!in_map($x_pnj-1,$y_pnj))) {
			
				// on cherche une case libre autour du pnj en partant du haut jusqu'en bas et seulement du coté de la fuite
				$sql = "SELECT occupee_carte,x_carte,y_carte FROM carte WHERE x_carte<=$x_pnj AND x_carte>=$x_pnj-1 AND y_carte>=$y_pnj-1 AND y_carte<=$y_pnj+1";
				$res = $mysqli->query($sql);
				
				while ($t_o = $res->fetch_assoc()){
					
					$occupee = $t_o["occupee_carte"];
					
					// la case est libre
					if(!$occupee && (in_map($t_o["x_carte"],$t_o["y_carte"]))) {
						
						$x_ok = $t_o["x_carte"];
						$y_ok = $t_o["y_carte"];
						
						break;
					}
				}
				
				// on a trouvé une case libre
				if(isset($x_ok) && isset($y_ok)) {
					// on s'y deplace
					$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte=$x_pnj AND y_carte=$y_pnj";
					$mysqli->query($sql);
						
					$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte=$id_i_pnj, image_carte='$image_pnj' WHERE x_carte=$x_ok AND y_carte=$y_ok";
					$mysqli->query($sql);
						
					$sql = "UPDATE instance_pnj SET x_i=$x_ok, y_i=$y_ok WHERE idInstance_pnj=$id_i_pnj";
					$mysqli->query($sql);
						
					// MAJ des evenements
					$sql = "INSERT INTO evenement (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES('$id_i_pnj','<b>$nom_pnj</b>','s\'est deplacé',NULL,'','en $x_ok/$y_ok',now(),'0')";
					$mysqli->query($sql);
				}
				else {
					// on a pas trouvé de case libre
					echo "Le pnj est bloqué<br>";
				}
			}
			else {
				// on s'y deplace
				if(in_map($x_pnj-1,$y_pnj)){
				
					$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte=$x_pnj AND y_carte=$y_pnj";
					$mysqli->query($sql);
							
					$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte=$id_i_pnj, image_carte='$image_pnj' WHERE x_carte=$x_pnj-1 AND y_carte=$y_pnj";
					$mysqli->query($sql);
						
					$sql = "UPDATE instance_pnj SET x_i=$x_pnj-1, y_i=$y_pnj WHERE idInstance_pnj=$id_i_pnj";
					$mysqli->query($sql);
						
					$x_pnjj = $x_pnj-1;
						
					// MAJ des evenements
					$sql = "INSERT INTO evenement (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES('$id_i_pnj','<b>$nom_pnj</b>','s\'est deplacé',NULL,'','en $x_pnjj/$y_pnj',now(),'0')";
					$mysqli->query($sql);
				}
				else {
					// on a pas trouvé de case libre
					echo "Le pnj est bloqué<br>";
				}
			}
		}
	}
	
	if ($x_d > 0){
		
		if($y_d > 0) {
			echo "deplacement vers la diagonale haut-droite<br>";
			// on recupere les coordonnées du pnj
			$sql = "SELECT x_i, y_i FROM instance_pnj WHERE idInstance_pnj=$id_i_pnj";
			$res = $mysqli->query($sql);
			$t_coor = $res->fetch_assoc();
			
			$x_pnj = $t_coor["x_i"];
			$y_pnj = $t_coor["y_i"];
				
			// on regarde si la case a la diagonale haut-droite du pnj est libre
			$sql = "SELECT occupee_carte FROM carte WHERE x_carte=$x_pnj+1 AND y_carte=$y_pnj+1";
			$res = $mysqli->query($sql);
			$t_oc = $res->fetch_assoc();
			
			$oc = $t_oc["occupee_carte"];
			
			// la case est occupee
			if($oc || (!in_map($x_pnj+1,$y_pnj+1))) {
				
				// on cherche une case libre autour du pnj en partant du haut jusqu'en bas et seulement du coté de la fuite
				$sql = "SELECT occupee_carte,x_carte,y_carte FROM carte WHERE x_carte<=$x_pnj+1 AND x_carte>=$x_pnj AND y_carte>=$y_pnj AND y_carte<=$y_pnj+1";
				$res = $mysqli->query($sql);
				
				while ($t_o = $res->fetch_assoc()){
					
					$occupee = $t_o["occupee_carte"];
					
					// la case est libre
					if(!$occupee && (in_map($t_o["x_carte"],$t_o["y_carte"]))) {
						
						$x_ok = $t_o["x_carte"];
						$y_ok = $t_o["y_carte"];
						
						break;
					}
				}
				
				// on a trouvé une case libre
				if(isset($x_ok) && isset($y_ok)) {
					
					// on s'y deplace
					$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte=$x_pnj AND y_carte=$y_pnj";
					$mysqli->query($sql);
							
					$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte=$id_i_pnj, image_carte='$image_pnj' WHERE x_carte=$x_ok AND y_carte=$y_ok";
					$mysqli->query($sql);
							
					$sql = "UPDATE instance_pnj SET x_i=$x_ok, y_i=$y_ok WHERE idInstance_pnj=$id_i_pnj";
					$mysqli->query($sql);
						
					// MAJ des evenements
					$sql = "INSERT INTO evenement (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES('$id_i_pnj','<b>$nom_pnj</b>','s\'est deplacé',NULL,'','en $x_ok/$y_ok',now(),'0')";
					$mysqli->query($sql);
				}
				else {
					// on a pas trouvé de case libre
					echo "Le pnj est bloqué<br>";
				}
			}
			else {
				// on s'y deplace
				if(in_map($x_pnj+1,$y_pnj+1)){
						
					$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte=$x_pnj AND y_carte=$y_pnj";
					$mysqli->query($sql);
								
					$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte=$id_i_pnj, image_carte='$image_pnj' WHERE x_carte=$x_pnj+1 AND y_carte=$y_pnj+1";
					$mysqli->query($sql);
								
					$sql = "UPDATE instance_pnj SET x_i=$x_pnj+1, y_i=$y_pnj+1 WHERE idInstance_pnj=$id_i_pnj";
					$mysqli->query($sql);
							
					$x_pnjj = $x_pnj+1;
					$y_pnjj = $y_pnj+1;
								
					// MAJ des evenements
					$sql = "INSERT INTO evenement (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES('$id_i_pnj','<b>$nom_pnj</b>','s\'est deplacé',NULL,'','en $x_pnjj/$y_pnjj',now(),'0')";
					$mysqli->query($sql);
				}
				else {
					// on a pas trouvé de case libre
					echo "Le pnj est bloqué<br>";
				}
			}
		}
		
		if($y_d < 0) {
			echo "deplacement vers la diagonale bas-droite<br>";
			// on recupere les coordonnées du pnj
			$sql = "SELECT x_i, y_i FROM instance_pnj WHERE idInstance_pnj=$id_i_pnj";
			$res = $mysqli->query($sql);
			$t_coor = $res->fetch_assoc();
			
			$x_pnj = $t_coor["x_i"];
			$y_pnj = $t_coor["y_i"];
				
			// on regarde si la case a la diagonale bas-droite du pnj est libre
			$sql = "SELECT occupee_carte FROM carte WHERE x_carte=$x_pnj+1 AND y_carte=$y_pnj";
			$res = $mysqli->query($sql);
			$t_oc = $res->fetch_assoc();
			
			$oc = $t_oc["occupee_carte"];
			
			// la case est occupee
			if($oc || (!in_map($x_pnj+1,$y_pnj-1))) {
				
				// on cherche une case libre autour du pnj en partant du haut jusqu'en bas et seulement du coté de la fuite
				$sql = "SELECT occupee_carte,x_carte,y_carte FROM carte WHERE x_carte<=$x_pnj+1 AND x_carte>=$x_pnj AND y_carte>=$y_pnj-1 AND y_carte<=$y_pnj";
				$res = $mysqli->query($sql);
				
				while ($t_o = $res->fetch_assoc()){
					
					$occupee = $t_o["occupee_carte"];
					
					// la case est libre
					if(!$occupee && (in_map($t_o["x_carte"],$t_o["y_carte"]))) {
						
						$x_ok = $t_o["x_carte"];
						$y_ok = $t_o["y_carte"];
						
						break;
					}
				}
				
				// on a trouvé une case libre
				if(isset($x_ok) && isset($y_ok)) {
					// on s'y deplace
					$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte=$x_pnj AND y_carte=$y_pnj";
					$mysqli->query($sql);
							
					$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte=$id_i_pnj, image_carte='$image_pnj' WHERE x_carte=$x_ok AND y_carte=$y_ok";
					$mysqli->query($sql);
							
					$sql = "UPDATE instance_pnj SET x_i=$x_ok, y_i=$y_ok WHERE idInstance_pnj=$id_i_pnj";
					$mysqli->query($sql);
						
					// MAJ des evenements
					$sql = "INSERT INTO evenement (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES('$id_i_pnj','<b>$nom_pnj</b>','s\'est deplacé',NULL,'','en $x_ok/$y_ok',now(),'0')";
					$mysqli->query($sql);
				}
				else {
					// on a pas trouvé de case libre
					echo "Le pnj est bloqué<br>";
				}
			}
			else {
				// on s'y deplace
				if(in_map($x_pnj+1,$y_pnj-1)){
						
					$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte=$x_pnj AND y_carte=$y_pnj";
					$mysqli->query($sql);
								
					$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte=$id_i_pnj, image_carte='$image_pnj' WHERE x_carte=$x_pnj+1 AND y_carte=$y_pnj-1";
					$mysqli->query($sql);
								
					$sql = "UPDATE instance_pnj SET x_i=$x_pnj+1, y_i=$y_pnj-1 WHERE idInstance_pnj=$id_i_pnj";
					$mysqli->query($sql);
							
					$x_pnjj = $x_pnj+1;
					$y_pnjj = $y_pnj-1;
								
					// MAJ des evenements
					$sql = "INSERT INTO evenement (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES('$id_i_pnj','<b>$nom_pnj</b>','s\'est deplacé',NULL,'','en $x_pnjj/$y_pnjj',now(),'0')";
					$mysqli->query($sql);
				}
				else {
					// on a pas trouvé de case libre
					echo "Le pnj est bloqué<br>";
				}
			}
		}
		
		if($y_d == 0) {
			echo "deplacement vers la droite<br>";
			// on recupere les coordonnées du pnj
			$sql = "SELECT x_i, y_i FROM instance_pnj WHERE idInstance_pnj=$id_i_pnj";
			$res = $mysqli->query($sql);
			$t_coor = $res->fetch_assoc();
			
			$x_pnj = $t_coor["x_i"];
			$y_pnj = $t_coor["y_i"];
				
			// on regarde si la case a droite du pnj est libre
			$sql = "SELECT occupee_carte FROM carte WHERE x_carte=$x_pnj+1 AND y_carte=$y_pnj";
			$res = $mysqli->query($sql);
			$t_oc = $res->fetch_assoc();
			
			$oc = $t_oc["occupee_carte"];
			
			// la case est occupee
			if($oc || (!in_map($x_pnj+1,$y_pnj))) {
				
				// on cherche une case libre autour du pnj en partant du haut jusqu'en bas et seulement du coté de la fuite
				$sql = "SELECT occupee_carte,x_carte,y_carte FROM carte WHERE x_carte<=$x_pnj AND x_carte>=$x_pnj+1 AND y_carte>=$y_pnj-1 AND y_carte<=$y_pnj+1";
				$res = $mysqli->query($sql);
				
				while ($t_o = $res->fetch_assoc()){
					
					$occupee = $t_o["occupee_carte"];
					
					// la case est libre
					if(!$occupee && (in_map($t_o["x_carte"],$t_o["y_carte"]))) {
						
						$x_ok = $t_o["x_carte"];
						$y_ok = $t_o["y_carte"];
						
						break;
					}
				}
				
				// on a trouvé une case libre
				if(isset($x_ok) && isset($y_ok)) {
					// on s'y deplace
					$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte=$x_pnj AND y_carte=$y_pnj";
					$mysqli->query($sql);
							
					$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte=$id_i_pnj, image_carte='$image_pnj' WHERE x_carte=$x_ok AND y_carte=$y_ok";
					$mysqli->query($sql);
							
					$sql = "UPDATE instance_pnj SET x_i=$x_ok, y_i=$y_ok WHERE idInstance_pnj=$id_i_pnj";
					$mysqli->query($sql);
						
					// MAJ des evenements
					$sql = "INSERT INTO evenement (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES('$id_i_pnj','<b>$nom_pnj</b>','s\'est deplacé',NULL,'','en $x_ok/$y_ok',now(),'0')";
					$mysqli->query($sql);
				}
				else {
					// on a pas trouvé de case libre
					echo "Le pnj est bloqué<br>";
				}
			}
			else {
				
				// on s'y deplace
				if(in_map($x_pnj+1,$y_pnj)){
						
					$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte=$x_pnj AND y_carte=$y_pnj";
					$mysqli->query($sql);
								
					$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte=$id_i_pnj, image_carte='$image_pnj' WHERE x_carte=$x_pnj+1 AND y_carte=$y_pnj";
					$mysqli->query($sql);
								
					$sql = "UPDATE instance_pnj SET x_i=$x_pnj+1, y_i=$y_pnj WHERE idInstance_pnj=$id_i_pnj";
					$mysqli->query($sql);
							
					$x_pnjj = $x_pnj+1;
								
					// MAJ des evenements
					$sql = "INSERT INTO evenement (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES('$id_i_pnj','<b>$nom_pnj</b>','s\'est deplacé',NULL,'','en $x_pnjj/$y_pnj',now(),'0')";
					$mysqli->query($sql);
				}
				else {
					// on a pas trouvé de case libre
					echo "Le pnj est bloqué<br>";
				}
			}
		}
	}
	
	// pnj se trouve sur le meme x que le pj
	if($x_d==0){
		
		// deplacement vers le haut
		if($y_d > 0) {
			echo "deplacement vers le haut<br>";
			// on recupere les coordonnées du pnj
			$sql = "SELECT x_i, y_i FROM instance_pnj WHERE idInstance_pnj=$id_i_pnj";
			$res = $mysqli->query($sql);
			$t_coor = $res->fetch_assoc();
			
			$x_pnj = $t_coor["x_i"];
			$y_pnj = $t_coor["y_i"];
			
			// on regarde si la case en haut du pnj est libre
			$sql = "SELECT occupee_carte FROM carte WHERE x_carte=$x_pnj AND y_carte=$y_pnj+1";
			$res = $mysqli->query($sql);
			$t_oc = $res->fetch_assoc();
			
			$oc = $t_oc["occupee_carte"];
			
			// la case est occupee
			if($oc || (!in_map($x_pnj,$y_pnj+1))) {
				
				// on cherche une case libre autour du pnj en partant de la gauche vers la droite et seulement du coté de la fuite
				$sql = "SELECT occupee_carte,x_carte,y_carte FROM carte WHERE x_carte>=$x_pnj-1 AND x_carte<=$x_pnj+1 AND y_carte>=$y_pnj AND y_carte<=$y_pnj+1";
				$res = $mysqli->query($sql);
				
				while ($t_o = $res->fetch_assoc()){
					
					$occupee = $t_o["occupee_carte"];
					
					// la case est libre
					if(!$occupee && (in_map($t_o["x_carte"],$t_o["y_carte"]))) {
						
						$x_ok = $t_o["x_carte"];
						$y_ok = $t_o["y_carte"];
						
						break;
					}
				}
				
				// on a trouvé une case libre
				if(isset($x_ok) && isset($y_ok)) {
					// on s'y deplace
					$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte=$x_pnj AND y_carte=$y_pnj";
					$mysqli->query($sql);
						
					$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte=$id_i_pnj, image_carte='$image_pnj' WHERE x_carte=$x_ok AND y_carte=$y_ok";
					$mysqli->query($sql);
						
					$sql = "UPDATE instance_pnj SET x_i=$x_ok, y_i=$y_ok WHERE idInstance_pnj=$id_i_pnj";
					$mysqli->query($sql);
						
					// MAJ des evenements
					$sql = "INSERT INTO evenement (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES('$id_i_pnj','<b>$nom_pnj</b>','s\'est deplacé',NULL,'','en $x_ok/$y_ok',now(),'0')";
					$mysqli->query($sql);
				}
				else {
					// on a pas trouvé de case libre
					echo "Le pnj est bloqué<br>";
				}
			}
			else {
				
				if(in_map($x_pnj,$y_pnj+1)){
				
					// on s'y deplace
					$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte=$x_pnj AND y_carte=$y_pnj";
					$mysqli->query($sql);
							
					$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte=$id_i_pnj, image_carte='$image_pnj' WHERE x_carte=$x_pnj AND y_carte=$y_pnj+1";
					$mysqli->query($sql);
							
					$sql = "UPDATE instance_pnj SET x_i=$x_pnj, y_i=$y_pnj+1 WHERE idInstance_pnj=$id_i_pnj";
					$mysqli->query($sql);
						
					$y_pnjj = $y_pnj+1;	
						
					// MAJ des evenements
					$sql = "INSERT INTO evenement (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES('$id_i_pnj','<b>$nom_pnj</b>','s\'est deplacé',NULL,'','en $x_pnj/$y_pnjj',now(),'0')";
					$mysqli->query($sql);
				}
				else {
					// on a pas trouvé de case libre
					echo "Le pnj est bloqué<br>";
				}
			}
		}
		
		// deplacement vers le bas
		if($y_d < 0) {
			echo "deplacement vers le bas<br>";
			// on recupere les coordonnées du pnj
			$sql = "SELECT x_i, y_i FROM instance_pnj WHERE idInstance_pnj=$id_i_pnj";
			$res = $mysqli->query($sql);
			$t_coor = $res->fetch_assoc();
			
			$x_pnj = $t_coor["x_i"];
			$y_pnj = $t_coor["y_i"];
			
			// on regarde si la case en bas du pnj est libre
			$sql = "SELECT occupee_carte FROM carte WHERE x_carte=$x_pnj AND y_carte=$y_pnj-1";
			$res = $mysqli->query($sql);
			$t_oc = $res->fetch_assoc();
			
			$oc = $t_oc["occupee_carte"];
			
			// la case est occupee
			if($oc || (!in_map($x_pnj,$y_pnj-1))) {
				
				// on cherche une case libre autour du pnj en partant de la gauche vers la droite et seulement du coté de la fuite
				$sql = "SELECT occupee_carte,x_carte,y_carte FROM carte WHERE x_carte>=$x_pnj-1 AND x_carte<=$x_pnj+1 AND y_carte<=$y_pnj AND y_carte>=$y_pnj-1";
				$res = $mysqli->query($sql);
				
				while ($t_o = $res->fetch_assoc()){
					
					$occupee = $t_o["occupee_carte"];
					
					// la case est libre
					if(!$occupee && (in_map($t_o["x_carte"],$t_o["y_carte"]))) {
						
						$x_ok = $t_o["x_carte"];
						$y_ok = $t_o["y_carte"];
						
						break;
					}
				}
				
				if(isset($x_ok) && isset($y_ok)) {
					
					// on s'y deplace
					$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte=$x_pnj AND y_carte=$y_pnj";
					$mysqli->query($sql);
						
					$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte=$id_i_pnj, image_carte='$image_pnj' WHERE x_carte=$x_ok AND y_carte=$y_ok";
					$mysqli->query($sql);
						
					$sql = "UPDATE instance_pnj SET x_i=$x_ok, y_i=$y_ok WHERE idInstance_pnj=$id_i_pnj";
					$mysqli->query($sql);
						
					// MAJ des evenements
					$sql = "INSERT INTO evenement (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES('$id_i_pnj','<b>$nom_pnj</b>','s\'est deplacé',NULL,'','en $x_ok/$y_ok',now(),'0')";
					$mysqli->query($sql);
				}
				else {
					// on a pas trouvé de case libre
					echo "Le pnj est bloqué<br>";
				}
			}
			else {
				
				if(in_map($x_pnj,$y_pnj-1)){
					
					// on s'y deplace
					$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte=$x_pnj AND y_carte=$y_pnj";
					$mysqli->query($sql);
							
					$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte=$id_i_pnj, image_carte='$image_pnj' WHERE x_carte=$x_pnj AND y_carte=$y_pnj-1";
					$mysqli->query($sql);
							
					$sql = "UPDATE instance_pnj SET x_i=$x_pnj, y_i=$y_pnj-1 WHERE idInstance_pnj=$id_i_pnj";
					$mysqli->query($sql);
					
					$y_pnjj = $y_pnj-1;
							
					// MAJ des evenements
					$sql = "INSERT INTO evenement (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES('$id_i_pnj','<b>$nom_pnj</b>','s\'est deplacé',NULL,'','en $x_pnj/$y_pnjj',now(),'0')";
					$mysqli->query($sql);
				}
				else {
					// on a pas trouvé de case libre
					echo "Le pnj est bloqué<br>";
				}
			}
		}	
	}
}

// fonction de deplacement de pnj au hasard (quand celui ci n'a pas d'objectif ou ne doit pas fuir)
function deplacement_hasard($mysqli, $x, $y, $id_i_pnj, $type,$nom_pnj){

	echo "deplacement_hasard<br>";

	$image_pnj = "pnj".$type."t.png";
	$essai = 0; 
	$ok=1;
	$x_h=$x;
	$y_h=$y;
	
	// eviter de se deplacer sur soi-meme...
	while($ok){ 
	
		if($essai >= 10){
			break;
		}
		else {
			
			if(($x_h == $x && $y_h == $y) || $essai > 0){
				$x_h = mt_rand($x-1,$x+1);
				$y_h = mt_rand($y-1,$y+1);
				$essai++;
			}
			
			if ($x_h != $x || $y_h != $y) {
				
				// on verifie que les coordonnees sont bien dans la carte
				if ($x_h < X_MAX && $x_h > X_MIN && $y_h > Y_MIN && $y_h < Y_MAX){
					
					// on verifie que la case est libre
					$sql = "SELECT occupee_carte FROM carte WHERE x_carte=$x_h AND y_carte=$y_h";
					$res = $mysqli->query($sql);
					$pnj_dep = $res->fetch_assoc();
					$occ = $pnj_dep["occupee_carte"];
					
					if(!$occ){
						$ok=0;
					}
				}
				$essai++;
			}
		}
	}
	
	if($ok) {
		echo "deplacement au hasard bloqué<br>";
	}
	else {
		// on s'y deplace
		$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte=$x AND y_carte=$y";
		$mysqli->query($sql);
									
		$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte=$id_i_pnj, image_carte='$image_pnj' WHERE x_carte=$x_h AND y_carte=$y_h";
		$mysqli->query($sql);
									
		$sql = "UPDATE instance_pnj SET x_i=$x_h, y_i=$y_h WHERE idInstance_pnj=$id_i_pnj";
		$mysqli->query($sql);
							
		// MAJ des evenements
		$sql = "INSERT INTO evenement (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES('$id_i_pnj','<b>$nom_pnj</b>','s\'est deplacé',NULL,'','en $x_h/$y_h',now(),'0')";
		$mysqli->query($sql);
	}
}

?>