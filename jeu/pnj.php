<?php
session_start();
require_once("../fonctions.php");

$mysqli = db_connexion();

define ("NB_MAX_PNJ", 200); 
define ("NB_TYPE_PNJ", 9);
define ("NB_PNJ_A_CREER", 10);
define ("NB_PNJ_MAX_TYPE", 30);


//*********************************
//Traitement des persos à mettre en gel
//*********************************
$sql = "SELECT id_perso FROM perso WHERE a_gele='1'";
$res = $mysqli->query($sql);

while ($t = $res->fetch_assoc()){
	
	$id_perso = $t["id_perso"];
	
	echo "gel du perso $id_perso ";
	
	// maj du statut du perso
	$sql = "UPDATE perso SET est_gele='1', a_gele='0', date_gele=NOW() WHERE id_perso='$id_perso'";
	$mysqli->query($sql);
	
	// maj de la carte => disparition du perso
	$sql = "UPDATE carte SET occupee_carte='0' WHERE idPerso_carte='$id_perso'";
	$mysqli->query($sql);
}
// Fin traitement des persos à mettre en gel


//************************************
//Traitement de l'ajout des pnj sur la carte
//************************************

// on recupere d'abord le nombre de pnj sur la carte
$sql = "SELECT idInstance_pnj FROM instance_pnj";
$res = $mysqli->query($sql);
$t_i = $res->num_rows;

// nombre d'instance de pnj sur la carte
echo "nombre de pnj sur la carte : ".$t_i."<br/>";

// nombre de pnj par type
$sql_nb = "SELECT id_pnj, count(idInstance_pnj) as nb_pnj  FROM `instance_pnj` GROUP BY id_pnj";
$res_nb = $mysqli->query($sql_nb);

while ($t_nb = $res_nb->fetch_assoc()){
	
	$id_pnj = $t_nb['id_pnj'];
	$nb_pnj = $t_nb['nb_pnj'];
	
	echo $id_pnj." : ".$nb_pnj."<br />";
}

if ( $t_i < NB_MAX_PNJ) {
	
	// le nombre de pnj à creer + le nombre actuel de pnj est superieur au nombre max de pnj
	if($t_i + NB_PNJ_A_CREER > NB_MAX_PNJ ) {
		
		// on se limite à creer des pnj jusqu'au nombre max
		for ($i=$t_i; $i< NB_MAX_PNJ ; $i++) {
			
			// on choisit un pnj au hasard
			$nb_aleatoire =  rand(1, NB_TYPE_PNJ);
			
			//verification su nombre de pnj de ce type sur la carte
			$sql = "SELECT idInstance_pnj FROM instance_pnj WHERE id_pnj='$nb_aleatoire'";
			$res = $mysqli->query($sql);
			$t_j = $res->num_rows;
			
			if($t_j < NB_PNJ_MAX_TYPE){
			
				echo " type pnj : ".$nb_aleatoire;
				
				// recuperation des valeurs caracterisant le pnj
				$sql = "SELECT pvMax_pnj, pm_pnj FROM pnj WHERE id_pnj='$nb_aleatoire'";
				$res = $mysqli->query($sql);
				$t = $res->fetch_assoc();
				
				$pv_pnj = $t["pvMax_pnj"];
				$pm_pnj = $t["pm_pnj"];
				
				// récupération du nombre de zones 
				$sql = "SELECT count(zones.id_zone) as nb_zone FROM zones, pnj_in_zone WHERE zones.id_zone=pnj_in_zone.id_zone AND id_pnj='$nb_aleatoire'";
				$res = $mysqli->query($sql);
				$t = $res->fetch_assoc();
				
				$nb_zones = $t['nb_zone'];
				
				if ($nb_zones > 0) {
				
					// selection d'une zone au hasard 
					$select_zone = rand(1, $nb_zones);
					
					// recuperation de la zone du pnj
					$sql = "SELECT xMin_zone, xMax_zone, yMin_zone, yMax_zone FROM zones, pnj_in_zone WHERE zones.id_zone=pnj_in_zone.id_zone AND id_pnj='$nb_aleatoire'";
					$res = $mysqli->query($sql);
					
					$zone_count = 1;
					while ($t_z = $res->fetch_assoc()) {
						
						if ($select_zone == $zone_count) {
							$xMin_zone 	= $t_z["xMin_zone"];
							$xMax_zone 	= $t_z["xMax_zone"];
							$yMin_zone 	= $t_z["yMin_zone"];
							$yMax_zone 	= $t_z["yMax_zone"];
							
							break;
						}
					
						$zone_count++;
					}
					
					// creation d'une nouvelle instance de pnj
					$sql = "INSERT INTO instance_pnj (id_pnj, pv_i, pm_i, deplace_i, dernierAttaquant_i, x_i, y_i) VALUES('$nb_aleatoire','$pv_pnj','$pm_pnj','1',0,NULL,NULL)";
					$mysqli->query($sql);
					$id_i = $mysqli->insert_id;
					
					// recherche d'une case libre pour mettre le pnj sur la carte dans sa zone
					$occup = 1;
					$ok = 1;
					// nombre d'essai pour placer le pnj -- Evite les boucles infini si pnj impossible à placer
					// Evites aussi que trop de pnj se trouvent dans une zone
					$essai = 10; 
					while ($occup == 1)
					{
						$x = pos_zone_rand_x ($xMin_zone,$xMax_zone); 
						$y = pos_zone_rand_y ($yMin_zone,$yMax_zone);
						$occup = verif_pos_libre($mysqli, $x, $y);
						$essai--;
						if (!$essai){
							$ok = 0;
							break;
						}
					}
					
					if ($ok) {
						
						// insertion du pnj sur la carte
						$image_pnj = "pnj".$nb_aleatoire."t.png";
						
						$sql = "UPDATE carte SET idPerso_carte=$id_i, occupee_carte='1', image_carte='$image_pnj' WHERE x_carte='$x' AND y_carte='$y'";
						$mysqli->query($sql);
					
						// mise a jour des coordonnées de l'instance du pnj
						$sql = "UPDATE instance_pnj SET x_i='$x', y_i='$y' WHERE idInstance_pnj=$id_i";
						$mysqli->query($sql);
					}
					else {
						// on efface l'instance qu'on a essayé de placé
						$sql = "DELETE FROM instance_pnj WHERE idInstance_pnj='$id_i'";
						$mysqli->query($sql);
					}
				}
			}
			else {
				$i--;
			}
		}
	}
	else {
		for ($i=$t_i; $i<$t_i + NB_PNJ_A_CREER ; $i++) {
		
			// on choisit un pnj au hasard
			$nb_aleatoire =  rand(1, NB_TYPE_PNJ); 
			
			//verification su nombre de pnj de ce type sur la carte
			$sql = "SELECT idInstance_pnj FROM instance_pnj WHERE id_pnj='$nb_aleatoire'";
			$res = $mysqli->query($sql);
			$t_j = $res->num_rows;
			
			if($t_j < NB_PNJ_MAX_TYPE){
			
				echo " type pnj : ".$nb_aleatoire;
				
				// recuperation des valeurs caracterisant le pnj
				$sql = "SELECT pvMax_pnj, pm_pnj FROM pnj WHERE id_pnj='$nb_aleatoire'";
				$res = $mysqli->query($sql);
				$t = $res->fetch_assoc();
				
				$pv_pnj = $t["pvMax_pnj"];
				$pm_pnj = $t["pm_pnj"];
				
				// récupération du nombre de zones 
				$sql = "SELECT count(zones.id_zone) as nb_zone FROM zones, pnj_in_zone WHERE zones.id_zone=pnj_in_zone.id_zone AND id_pnj='$nb_aleatoire'";
				$res = $mysqli->query($sql);
				$t = $res->fetch_assoc();
				
				$nb_zones = $t['nb_zone'];
				
				if ($nb_zones > 0) {
				
					// selection d'une zone au hasard 
					$select_zone = rand(1, $nb_zones);
					
					// recuperation de la zone du pnj
					$sql = "SELECT xMin_zone, xMax_zone, yMin_zone, yMax_zone FROM zones, pnj_in_zone WHERE zones.id_zone=pnj_in_zone.id_zone AND id_pnj=$nb_aleatoire";
					$res = $mysqli->query($sql);
					
					$zone_count = 1;
					while ($t_z = $res->fetch_assoc()) {
						
						if ($select_zone == $zone_count) {
					
							$xMin_zone = $t_z["xMin_zone"];
							$xMax_zone = $t_z["xMax_zone"];
							$yMin_zone = $t_z["yMin_zone"];
							$yMax_zone = $t_z["yMax_zone"];
						}
						
						$zone_count++;
					}
					
					// creation d'une nouvelle instance de pnj
					$sql = "INSERT INTO instance_pnj (id_pnj, pv_i, pm_i, deplace_i, dernierAttaquant_i, x_i, y_i) VALUES('$nb_aleatoire','$pv_pnj','$pm_pnj','1',0,NULL,NULL)";
					$mysqli->query($sql);
					$id_i = $mysqli->insert_id; // recuperation de l'id de l'instance créée
					
					// recherche d'une case libre pour mettre le pnj sur la carte dans sa zone
					$occup = 1;
					$ok = 1;
					// nombre d'essai pour placer le pnj
					$essai = 10;
					while ($occup == 1)
					{
						$x = pos_zone_rand_x($xMin_zone,$xMax_zone); 
						$y = pos_zone_rand_y($yMin_zone,$yMax_zone);
						$occup = verif_pos_libre($mysqli, $x, $y);
						$essai--;
						if (!$essai){
							$ok = 0;
							break;
						}
					}
					
					if ($ok) {
						// insertion du pnj sur la carte
						$image_pnj = "pnj".$nb_aleatoire."t.png";
						
						$sql = "UPDATE carte SET idPerso_carte=$id_i, occupee_carte='1', image_carte='$image_pnj' WHERE x_carte='$x' AND y_carte='$y'";
						$mysqli->query($sql);
					
						// mise a jour des coordonnées de l'instance du pnj
						$sql = "UPDATE instance_pnj SET x_i='$x', y_i='$y' WHERE idInstance_pnj='$id_i'";
						$mysqli->query($sql);
					}
					else {
						// on efface l'instance qu'on a essayé de placé
						$sql = "DELETE FROM instance_pnj WHERE idInstance_pnj='$id_i'";
						$mysqli->query($sql);
					}
				}
			}
			else {
				$i--;
			}
		}
	}
}

?>