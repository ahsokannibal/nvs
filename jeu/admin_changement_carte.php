<?php
session_start();
require_once("../fonctions.php");
require_once("f_carte.php");

$mysqli = db_connexion();

include ('../nb_online.php');
$phpbb_root_path = '../forum/';
if (is_dir($phpbb_root_path))
{
	include ($phpbb_root_path .'config.php');
}

if(isset($_SESSION["id_perso"])){
	
	$id_perso = $_SESSION['id_perso'];
	
	// recupération config jeu
	$admin = admin_perso($mysqli, $id_perso);
	
	if($admin){
		
		$mess_err 	= "";
		$mess 		= "";
		
		if (isset($_GET['carte_cible']) && trim($_GET['carte_cible']) != "") {
			
			$carte_cible = $_GET['carte_cible'];
			
			// Récupération des positions des Forts
			$sql_forts = "SELECT id_camp, position_x, position_y FROM em_position_infra_carte_suivante WHERE carte='$carte_cible' AND id_batiment='9'";
			$res_forts = $mysqli->query($sql_forts);
			$verif_forts = $res_forts->num_rows;
			
			if ($verif_forts == 2) {
				
				// passer jeu en mode mise à jour
				$sql = "UPDATE config_jeu SET valeur_config='0' WHERE code_config='disponible'";
				$mysqli->query($sql);
				
				// Vider table instance_batiment
				$sql = "DELETE FROM instance_batiment";
				$mysqli->query($sql);
				
				// Vider table instance_batiment_canon
				$sql = "DELETE FROM instance_batiment_canon";
				$mysqli->query($sql);
				
				// Vider table instance_pnj
				$sql = "DELETE FROM instance_pnj";
				$mysqli->query($sql);
				
				// Vider table liaisons_gare
				$sql = "DELETE FROM liaisons_gare";
				$mysqli->query($sql);
				
				// Vider table objet_in_carte
				$sql = "DELETE FROM objet_in_carte";
				$mysqli->query($sql);
				
				// Vider table perso_as_respawn
				$sql = "DELETE FROM perso_as_respawn";
				$mysqli->query($sql);
				
				// Vider table perso_in_batiment
				$sql = "DELETE FROM perso_in_batiment";
				$mysqli->query($sql);
				
				// Vider table perso_in_train
				$sql = "DELETE FROM perso_in_train";
				$mysqli->query($sql);
				
				// Vider table histo_stats_camp_pv (après affichage sur forum)
				$sql = "DELETE FROM histo_stats_camp_pv";
				$mysqli->query($sql);				
				
				// Vider table zones (à redéfinir après installation carte)
				$sql = "DELETE FROM zones";
				$mysqli->query($sql);
				
				// Vider table pnj_in_zone (à redéfinir après installation carte)
				$sql = "DELETE FROM pnj_in_zone";
				$mysqli->query($sql);
				
				// Vider table carte
				$sql = "DELETE FROM carte";
				$mysqli->query($sql);
				
				// Inserer données carte à partir des données issues de la carte choisie
				$sql_carte = "SELECT * FROM $carte_cible";
				$res_carte = $mysqli->query($sql_carte);
				
				while ($t_carte = $res_carte->fetch_assoc()) {
					
					$x_carte	= $t_carte['x_carte'];
					$y_carte	= $t_carte['y_carte'];
					$fond_carte	= $t_carte['fond_carte'];
					
					$sql = "INSERT INTO carte VALUES ($x_carte, $y_carte, '0', '$fond_carte', NULL, NULL, NULL, 0, 0, NULL)";
					$mysqli->query($sql);
				}
				
				// Coordonnées carte
				$sql = "UPDATE carte SET coordonnees = CONCAT (x_carte, ';', y_carte)";
				$mysqli->query($sql);

				// Récupération caracs Fort
				$sql = "SELECT pvMax_batiment, taille_batiment FROM batiment WHERE id_batiment='9'";
				$res = $mysqli->query($sql);
				$t = $res->fetch_assoc();
				
				$pvMax_Fort = $t['pvMax_batiment'];
				$taille_bat	= $t['taille_batiment'];
				
				$taille_search = floor($taille_bat / 2);
				
				while ($t_forts = $res_forts->fetch_assoc()) {
					
					$id_camp_fort	= $t_forts['id_camp'];
					$x_fort			= $t_forts['position_x'];
					$y_fort			= $t_forts['position_y'];
					
					if($id_camp_fort == '1'){
						$bat_camp = "b";
					}
					else if($id_camp_fort == '2'){
						$bat_camp = "r";
					}
					else if($id_camp_fort == '3'){
						$bat_camp = "g";
					}
					
					$img_bat = "b9".$bat_camp.".png";
					
					// Insérer batiment Fort
					$sql = "INSERT INTO instance_batiment (niveau_instance, id_batiment, nom_instance, pv_instance, pvMax_instance, x_instance, y_instance, camp_instance, contenance_instance) 
							VALUES ('1', '9', '', '$pvMax_Fort', '$pvMax_Fort', '$x_fort', '$y_fort', '$id_camp_fort', '100')";
					$mysqli->query($sql);
					$id_i_bat = $mysqli->insert_id;
					
					$img_bat_sup = $bat_camp.".png";
															
					for ($x = $x_fort - $taille_search; $x <= $x_fort + $taille_search; $x++) {
						for ($y = $y_fort - $taille_search; $y <= $y_fort + $taille_search; $y++) {
							
							// mise a jour de la carte
							$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte='$id_i_bat', image_carte='$img_bat_sup' WHERE x_carte='$x' AND y_carte='$y'";
							$mysqli->query($sql);
							
						}
					}
					
					// mise a jour de la carte image centrale
					$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte='$id_i_bat', image_carte='$img_bat' WHERE x_carte='$x_fort' AND y_carte='$y_fort'";
					$mysqli->query($sql);
					
					// CANONS FORT						
					if ($id_camp_fort == 1) {
						$image_canon_g = 'canonG_nord.gif';
						$image_canon_d = 'canonD_nord.gif';
					}
					
					if ($id_camp_fort == 2) {
						$image_canon_g = 'canonG_sud.gif';
						$image_canon_d = 'canonD_sud.gif';
					}
					
					// Canons Gauche
					$sql = "UPDATE carte SET image_carte='$image_canon_g' WHERE (x_carte=$x_fort - 2 AND y_carte=$y_fort + 2) OR (x_carte=$x_fort - 2 AND y_carte=$y_fort) OR (x_carte=$x_fort - 2 AND y_carte=$y_fort - 2)";
					$mysqli->query($sql);
					
					// Canons Droit
					$sql = "UPDATE carte SET image_carte='$image_canon_d' WHERE (x_carte=$x_fort + 2 AND y_carte=$y_fort + 2) OR (x_carte=$x_fort + 2 AND y_carte=$y_fort) OR (x_carte=$x_fort + 2 AND y_carte=$y_fort - 2)";
					$mysqli->query($sql);
					
					$sql = "INSERT INTO instance_batiment_canon (id_instance_bat, x_canon, y_canon, camp_canon) VALUES ('$id_i_bat', $x_fort - 2, $y_fort + 2, $id_camp_fort)";
					$mysqli->query($sql);
					$sql = "INSERT INTO instance_batiment_canon (id_instance_bat, x_canon, y_canon, camp_canon) VALUES ('$id_i_bat', $x_fort - 2, $y_fort, $id_camp_fort)";
					$mysqli->query($sql);
					$sql = "INSERT INTO instance_batiment_canon (id_instance_bat, x_canon, y_canon, camp_canon) VALUES ('$id_i_bat', $x_fort - 2, $y_fort - 2, $id_camp_fort)";
					$mysqli->query($sql);
					$sql = "INSERT INTO instance_batiment_canon (id_instance_bat, x_canon, y_canon, camp_canon) VALUES ('$id_i_bat', $x_fort + 2, $y_fort + 2, $id_camp_fort)";
					$mysqli->query($sql);
					$sql = "INSERT INTO instance_batiment_canon (id_instance_bat, x_canon, y_canon, camp_canon) VALUES ('$id_i_bat', $x_fort + 2, $y_fort, $id_camp_fort)";
					$mysqli->query($sql);
					$sql = "INSERT INTO instance_batiment_canon (id_instance_bat, x_canon, y_canon, camp_canon) VALUES ('$id_i_bat', $x_fort + 2, $y_fort - 2, $id_camp_fort)";
					$mysqli->query($sql);
					
					// Insérer persos dans Fort
					$sql_persos = "SELECT id_perso FROM perso WHERE clan='$id_camp_fort'";
					$res_persos = $mysqli->query($sql_persos);
					
					while ($t_persos = $res_persos->fetch_assoc()) {
						
						$id_perso_fort = $t_persos['id_perso'];
						
						$sql = "INSERT INTO `perso_in_batiment` VALUES ('$id_perso_fort','$id_i_bat')";
						$mysqli->query($sql);
						
						// calcul bonus perception perso
						$bonus_visu = getBonusObjet($mysqli, $id_perso_fort);
						
						$sql = "UPDATE perso SET x_perso='$x_fort', y_perso='$y_fort', pv_perso=pvMax_perso, pa_perso=paMax_perso, pm_perso=pmMax_perso, bonusPerception_perso=$bonus_visu, bonus_perso=0, bourre_perso=0, convalescence=0 WHERE id_perso='$id_perso_fort'";
						$res = $mysqli->query($sql);
					}
					
				}
				
				// Vider table choix_carte_suivante
				$sql = "DELETE FROM choix_carte_suivante";
				$mysqli->query($sql);
				
				// Vider table em_position_infra_carte_suivante
				$sql = "DELETE FROM em_position_infra_carte_suivante";
				$mysqli->query($sql);
							
				// passer jeu en mode disponible
				$sql = "UPDATE config_jeu SET valeur_config='1' WHERE code_config='disponible'";
				$mysqli->query($sql);
			}
			else {
				$mess_err .= "La position des Forts n'a pas été défini pour les 2 camps";
			}
		}
		
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<title>Nord VS Sud</title>
		
		<!-- Required meta tags -->
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		
		<!-- Bootstrap CSS -->
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
	</head>
	<body>
		<div class="container-fluid">
		
			<div class="row">
				<div class="col-12">
					<div align="center">
						<h2>Changement de carte</h2>
						
						<font color='red'><?php echo $mess_err; ?></font>
						<font color='blue'><?php echo $mess; ?></font>
					</div>
				</div>
			</div>
		
			<p align="center"><a class="btn btn-primary" href="admin_nvs.php">Retour à l'administration</a> <a class="btn btn-primary" href="jouer.php">Retour au jeu</a></p>
			
			<div class="row">
				<div class="col-12">
					<div align="center">
						<div id="table_choix_carte" class="table-responsive">
						<?php
						echo "<table class='table'>";
						echo "	<thead>";
						echo "		<tr>";
						echo "			<th style='text-align:center'>Camp</th>";
						echo "			<th style='text-align:center'>Choix de la carte</th>";
						echo "			<th style='text-align:center'>Date de choix</th>";
						echo "			<th style='text-align:center'>Action</th>";
						echo "		</tr>";
						echo "	</thead>";
						echo "	<tbody>";
						
						$sql = "SELECT * FROM choix_carte_suivante";
						$res = $mysqli->query($sql);
						
						while ($t = $res->fetch_assoc()) {
							
							$id_camp 		= $t['id_camp'];
							$choix_carte 	= $t['carte'];
							$date_choix		= $t['date_choix'];
							
							if ($id_camp == 1) {
								$nom_camp 		= "Nord";
								$couleur_camp	= "blue";
							}
							else {
								$nom_camp 		= "Sud";
								$couleur_camp	= "red";
							}
							
							echo "		<tr>";
							echo "			<td align='center'><font color='".$couleur_camp."'>".$nom_camp."</font></td>";
							echo "			<td align='center'>".$choix_carte."</td>";
							echo "			<td align='center'>".$date_choix."</td>";
							echo "			<td align='center'><a href='admin_changement_carte.php?carte_cible=".$choix_carte."' class='btn btn-danger'>Basculer sur cette carte</a></td>";
							echo "		</tr>";
							
						}
						?>
						</div>
					</div>
				</div>
			</div>
			
		</div>
		
		<!-- Optional JavaScript -->
		<!-- jQuery first, then Popper.js, then Bootstrap JS -->
		<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
		<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
	</body>
</html>
<?php
	}
}
else{
	echo "<font color=red>Vous ne pouvez pas acceder a cette page, veuillez vous logguer.</font>";
}?>
