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

if($dispo == '1' || $admin){
	
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
				$b_camp = 'b';
			}
			else if ($camp == '2') {
				$nom_camp = 'Sud';
				$b_camp = 'r';
			}
			else if ($camp == '3') {
				$nom_camp = 'Indien';
				$b_camp = 'g';
			}
			
			$mess = "";
			$mess_erreur = "";
			
			if (isset($_POST['hid_id_instance_rename']) && isset($_POST['nom_batiment']) && $_POST['nom_batiment'] != "") {
			
				$id_instance_bat_rename = $_POST['hid_id_instance_rename'];
				$nouveau_nom_bat		= addslashes($_POST['nom_batiment']);
				
				$sql = "UPDATE instance_batiment SET nom_instance='$nouveau_nom_bat' WHERE id_instanceBat='$id_instance_bat_rename'";
				$mysqli->query($sql);
				
				$mess .= "le batiment ".$id_instance_bat_rename." a été renommé avec succès en ".$nouveau_nom_bat;
				
			}
			
			if (isset($_POST['id_instance_bat_destruction']) && $_POST['id_instance_bat_destruction'] != "") {
			
				$id_instance_bat_destruction = $_POST['id_instance_bat_destruction'];
			
				// Est ce qu'il y a des persos dans le batiment ?
				$sql = "SELECT id_perso FROM perso_in_batiment WHERE id_instanceBat='$id_instance_bat_destruction'";
				$res = $mysqli->query($sql);
				$nb_persos_bat = $res->num_rows;
				
				if ($nb_persos_bat == 0) {
					
					// recup id_batiment
					$sql = "SELECT id_batiment FROM instance_batiment WHERE id_instanceBat='$id_instance_bat_destruction'";
					$res = $mysqli->query($sql);
					$t = $res->fetch_assoc();
					
					$id_bat = $t['id_batiment'];
					
					if ($id_bat == 5) {
						// Ponts
						$sql = "UPDATE carte SET fond_carte='8.gif', save_info_carte=NULL WHERE save_info_carte='$id_instance_bat_destruction'";
						$mysqli->query($sql);
						
						$sql = "UPDATE carte SET idPerso_carte=NULL WHERE idPerso_carte=''$id_instance_bat_destruction''";
						$mysqli->query($sql);
					}
					else {
						// Autres batiments
						$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, save_info_carte=NULL, image_carte=NULL WHERE idPerso_carte='$id_instance_bat_destruction'";
						$mysqli->query($sql);
					}
				
					$sql = "DELETE FROM instance_batiment WHERE id_instanceBat='$id_instance_bat_destruction'";
					$mysqli->query($sql);
					
					$mess .= "le batiment ".$id_instance_bat_destruction." a été détruit avec succès";
				}
				else {
					$mess_err .= "Des persos se trouvent encore dans le batiment, batiment impossible à détruire";
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
			
			<p align="center"><a class="btn btn-primary" href="animation.php">Retour page principale d'animation</a></p>
			
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
						<h2>Bâtiments importants</h2>
						<div id="table_batiment" class="table-responsive">						
							<table border="1" class='table'>
								<tr>
									<th style='text-align:center'>Bâtiment [matricule]</th><th style='text-align:center'>Nom du bâtiment</th><th style='text-align:center'>PV</th><th style='text-align:center'>Position</th><th style='text-align:center'>État</th>
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
									$nom_instance	= htmlentities($t['nom_instance'],ENT_QUOTES);
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
									echo "<form method=\"post\" action=\"anim_batiment.php\">";
									echo "	<td>";
									echo "		<input type='hidden' name='hid_id_instance_rename' value='$id_instance'>";
									echo "		<img src='../images_perso/".$image_bat."' width='40' height='40' /> ".$nom_batiment." [<a href='evenement.php?infoid=".$id_instance."' target='_blank'>".$id_instance."</a>]";
									echo "	</td>";
									echo "	<td>";
									echo "		<input type='text' name='nom_batiment' value='".$nom_instance."' > <input type='submit' name='rename_bat' value='Renommer' class='btn btn-primary'>";
									echo "	</td>";
									echo "</form>";
									
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
			
			<br />
			
			<div class="row">
				<div class="col-12">
					<div align="center">
						<h2>Autres Bâtiments</h2>
						<div id="table_batiment_autre" class="table-responsive">						
							<table border="1" class='table'>
								<tr>
									<th style='text-align:center'>Bâtiment [matricule]</th><th style='text-align:center'>PV</th><th style='text-align:center'>Position</th><th style='text-align:center'>Action</th>
								</tr>
								
								<?php
								// Liste des batiments du camp de l'animateur Hors barricades / ponts / tour de guet / trains
								$sql = "SELECT id_instanceBat, nom_batiment, nom_instance, pv_instance, pvMax_instance, x_instance, y_instance, instance_batiment.id_batiment FROM batiment, instance_batiment
										WHERE batiment.id_batiment = instance_batiment.id_batiment
										AND camp_instance='$camp'
										AND (instance_batiment.id_batiment = '1' 
											OR instance_batiment.id_batiment = '2' 
											OR instance_batiment.id_batiment = '5')
										ORDER BY instance_batiment.id_batiment";
								$res = $mysqli->query($sql);
								
								while ($t = $res->fetch_assoc()) {
									
									$id_instance	= $t['id_instanceBat'];
									$nom_batiment	= $t['nom_batiment'];
									$nom_instance	= htmlentities($t['nom_instance'],ENT_QUOTES);
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
									
									echo "<tr>";
									echo "	<td>";
									echo "		<input type='hidden' name='hid_id_instance_rename' value='$id_instance'>";
									echo "		<img src='../images_perso/".$image_bat."' width='40' height='40' /> ".$nom_batiment." [<a href='evenement.php?infoid=".$id_instance."'>".$id_instance."</a>]";
									echo "	</td>";
									
									// PV
									echo "	<td>";
									$pourc = affiche_jauge($pv_instance, $pvMax_instance); 
									echo round($pourc,2)."% ou $pv_instance/$pvMax_instance";
									echo "	</td>";
									
									// Position
									echo "	<td>".$x_instance."/".$y_instance."</td>";
									
									// Action
									echo "<form method=\"post\" action=\"anim_batiment.php\">";	
									echo "			<td>";
									echo "				<input type='hidden' name='id_instance_bat_destruction' value='".$id_instance."'>";
									echo "				<input type='submit' name='destruire_bat' value='Détruire' class='btn btn-danger'>";
									echo "			</td>";
									echo "</form>";
									
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