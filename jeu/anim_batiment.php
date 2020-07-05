<?php
session_start();
require_once("../fonctions.php");
require_once("f_carte.php");

$mysqli = db_connexion();

include ('../nb_online.php');
include ('../forum/config.php');

// recupération config jeu
$dispo = config_dispo_jeu($mysqli);
$admin = admin_perso($mysqli, $_SESSION["id_perso"]);

if($dispo || $admin){
	
	if (isset($_SESSION["id_perso"])) {
		
		//recuperation des variables de sessions
		$id = $_SESSION["id_perso"];
		
		if (anim_perso($mysqli, $id)) {
			
			// Récupération du camp de l'animateur 
			$sql = "SELECT clan FROM perso WHERE id_perso='$id'";
			$res = $mysqli->query($sql);
			$t = $res->fetch_assoc();
			
			$camp = $t['clan'];
			
			if ($camp == '1') {
				$nom_camp = 'Nord';
			}
			else if ($camp == '2') {
				$nom_camp = 'Sud';
			}
			else if ($camp == '3') {
				$nom_camp = 'Indien';
			}
			
			$mess = "";
			$mess_erreur = "";
			
			// Creation pénitencier
			if (isset($_POST['coord_x_penitencier']) && $_POST['coord_x_penitencier'] != ''
					&& isset($_POST['coord_y_penitencier']) && $_POST['coord_y_penitencier'] != '') {
				
				$x_penitencier = $_POST['coord_x_penitencier'];
				$y_penitencier = $_POST['coord_y_penitencier'];
				
				$verif_x = preg_match("#^[0-9]*[0-9]$#i","$x_penitencier");
				$verif_y = preg_match("#^[0-9]*[0-9]$#i","$y_penitencier");
				
				if ($verif_x && $verif_y && in_map($x_penitencier, $y_penitencier)) {
					
					$autorisation_construction_taille = true;
					
					$taille_bat = 3;
					$taille_search = floor($taille_bat / 2);
					
					// Est ce que les coordonnées sont libres pour la construction ?
					$sql = "SELECT occupee_carte, fond_carte FROM carte 
							WHERE x_carte <= $x_penitencier + $taille_search AND x_carte >= $x_penitencier - $taille_search 
							AND y_carte <= $y_penitencier + $taille_search AND y_carte >= $y_penitencier - $taille_search";
					$res = $mysqli->query($sql);
					
					while ($t = $res->fetch_assoc()) {
						
						$occupee_carte 	= $t["occupee_carte"];
						$fond_carte 	= $t["fond_carte"];
						
						if ($occupee_carte || $fond_carte != '1.gif') {
							$autorisation_construction_taille = false;
						}
					}
					
					if ($autorisation_construction_taille) {
					
						$img_bat = "b10".$camp.".png";
						$img_bat_sup = $camp.".png";
						
						// mise a jour de la table instance_bat
						$sql = "INSERT INTO instance_batiment (niveau_instance, id_batiment, nom_instance, pv_instance, pvMax_instance, x_instance, y_instance, camp_instance, contenance_instance) 
								VALUES ('1', '10', '', '15000', '15000', '$x_penitencier', '$y_penitencier', '$camp', '50')";
						$mysqli->query($sql);
						$id_i_bat = $mysqli->insert_id;
						
						for ($x = $x_penitencier - $taille_search; $x <= $x_penitencier + $taille_search; $x++) {
							for ($y = $y_penitencier - $taille_search; $y <= $y_penitencier + $taille_search; $y++) {
								
								// mise a jour de la carte
								$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte='$id_i_bat', image_carte='$img_bat_sup' WHERE x_carte='$x' AND y_carte='$y'";
								$mysqli->query($sql);
								
							}
						}
					
						// mise a jour de la carte image centrale
						$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte='$id_i_bat', image_carte='$img_bat' WHERE x_carte='$x_penitencier' AND y_carte='$y_penitencier'";
						$mysqli->query($sql);
					}
					else {
						$mess_erreur .= "Impossible de construire sur ces coordonnées, les cases ne dont pas libre ou ne sont pas des plaines";
					}
				}
				else {
					$mess_erreur .= "Coordonnées du pénitencier incorrectes";
				}
			}
			
			?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<title>Nord VS Sud - Animation</title>
		
		<!-- Required meta tags -->
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		
		<!-- Bootstrap CSS -->
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
	</head>
	<body>
		<div class="container">
		
			<div class="row">
				<div class="col-12">
					<div align="center">
						<h2>Animation - Gestion des bâtiments</h2>
					</div>
				</div>
			</div>
			
			<div class="row">
				<div class="col-12">
					<div align="center">
						<?php
						echo "<font color='blue'>".$mess."</font><br />";
						echo "<font color='red'><b>".$mess_erreur."</b></font><br />";
						?>
					</div>
				</div>
			</div>
			
			<div class="row">
				<div class="col-12">
					<div align="center">
						<?php						
						// Vérification si présence ou non d'un pénitencier
						$sql = "SELECT id_instanceBat FROM instance_batiment WHERE id_batiment=10 AND camp_instance='$camp'";
						$res = $mysqli->query($sql);
						$verif_penitencier = $res->num_rows;
						
						if (!$verif_penitencier) {
							echo "<form method='POST' action='anim_batiment.php'>";
							echo "	<input type='text' value='' placeholder='coordonnée x' name='coord_x_penitencier'>";
							echo "	<input type='text' value='' placeholder='coordonnée y' name='coord_y_penitencier'>";
							echo "	<button type='submit' class='btn btn-danger'>Créer un pénitencier pour mon camp</button>";
							echo '</form>';
							
							echo "<br /><br />";
						}
						?>
					</div>
				</div>
			</div>
			
			<div class="row">
				<div class="col-12">
					<div align="center">					
						<div id="table_batiment" class="table-responsive">						
							<table border="1">
								<tr>
									<th style='text-align:center'>Bâtiment [matricule]</th><th style='text-align:center'>PV</th><th style='text-align:center'>Position</th><th style='text-align:center'>État</th>
								</tr>
								
								<?php
								// Liste des batiments du camp de l'animateur Hors barricades / ponts / tour de guet / trains
								$sql = "SELECT id_instanceBat, nom_batiment, nom_instance, pv_instance, pvMax_instance, x_instance, y_instance, instance_batiment.id_batiment FROM batiment, instance_batiment
										WHERE batiment.id_batiment = instance_batiment.id_batiment
										AND camp_instance='$camp'
										AND instance_batiment.id_batiment != '1' 
										AND instance_batiment.id_batiment != '2' 
										AND instance_batiment.id_batiment != '5' 
										AND instance_batiment.id_batiment != '12'
										ORDER BY instance_batiment.id_batiment";
								$res = $mysqli->query($sql);
								
								while ($t = $res->fetch_assoc()) {
									
									$id_instance	= $t['id_instanceBat'];
									$nom_batiment	= $t['nom_batiment'];
									$nom_instance	= $t['nom_instance'];
									$pv_instance	= $t['pv_instance'];
									$pvMax_instance	= $t['pvMax_instance'];
									$x_instance		= $t['x_instance'];
									$y_instance		= $t['y_instance'];
									$id_batiment	= $t['id_batiment'];
									
									if ($camp == 1) {
										$image_bat = "b".$id_batiment."b.png";
									}
									else if ($camp == 2) {
										$image_bat = "b".$id_batiment."r.png";
									}
									
									
									// La bâtiment est-il en état de siège ?
									// Calcul pourcentage pv du batiment 
									$pourc_pv_instance = ($pv_instance / $pvMax_instance) * 100;
									
									// Verification si 10 persos ennemis à moins de 15 cases
									$sql_e = "SELECT count(id_perso) as nb_ennemi FROM perso, carte 
											WHERE perso.id_perso = carte.idPerso_carte 
											AND x_carte <= $x_instance + 15
											AND x_carte >= $x_instance - 15
											AND y_carte <= $y_instance + 15
											AND y_carte >= $y_instance - 15
											AND perso.clan != '$camp'";
									$res_e = $mysqli->query($sql_e);
									$t_e = $res_e->fetch_assoc();
									
									$nb_ennemis_siege = $t_e['nb_ennemi'];
									
									echo "<tr>";
									echo "	<td><img src='../images_perso/".$image_bat."' width='40' height='40' /> ".$nom_batiment." ".$nom_instance."[<a href='evenement.php?infoid=".$id_instance."'>".$id_instance."</a>]</td>";
									
									// PV
									echo "	<td>";
									$pourc = affiche_jauge($pv_instance, $pvMax_instance); 
									echo round($pourc,2)."% ou $pv_instance/$pvMax_instance";
									echo "	</td>";
									
									// Position
									echo "	<td>".$x_instance."/".$y_instance."</td>";
									
									// Etat
									echo "	<td>";
									if ($pourc_pv_instance < 90 || $nb_ennemis_siege >= 10) {
										echo "<b>Bâtiment en état de siège</b><br />";
									}
									if ($id_batiment == '11' && $pourc < 50) {
										echo "<b>Gare désactivée</b>";
									}
									echo "	</td>";
									
									echo "</tr>";
								}
								?>
							</table>
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
		else {
			// Un joueur essaye d'acceder à la page sans être animateur
			$text_triche = "Tentative accés page animation sans y avoir les droits";
			
			$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id', '$text_triche')";
			$mysqli->query($sql);
			
			header("Location:jouer.php");
		}
	}
	else{
		echo "<center><font color='red'>Vous ne pouvez pas accéder à cette page, veuillez vous loguer.</font></center>";
	}
}
else {
	// logout
	$_SESSION = array(); // On écrase le tableau de session
	session_destroy(); // On détruit la session
	
	header("Location:../index2.php");
}