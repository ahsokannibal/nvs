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
				$nom_camp 		= 'Nord';
				$b_camp 		= 'b';
				$couleur_camp	= 'blue';
			}
			else if ($camp == '2') {
				$nom_camp 		= 'Sud';
				$b_camp 		= 'r';
				$couleur_camp	= 'red';
			}
			else if ($camp == '3') {
				$nom_camp 		= 'Indien';
				$b_camp 		= 'g';
				$couleur_camp	= 'green';
			}
			
			$mess = "";
			$mess_erreur = "";
			
			// Vérification si présence ou non d'un pénitencier
			$sql_peni = "SELECT id_instanceBat, x_instance, y_instance FROM instance_batiment WHERE id_batiment=10 AND camp_instance='$camp'";
			$res_peni = $mysqli->query($sql_peni);
			$verif_penitencier = $res_peni->num_rows;
			
			$t = $res_peni->fetch_assoc();
			
			$id_penitencier	= $t['id_instanceBat'];
			$x_penitencier 	= $t['x_instance'];
			$y_penitencier 	= $t['y_instance'];
			
			// Creation pénitencier
			if (isset($_POST['coord_x_penitencier']) && $_POST['coord_x_penitencier'] != ''
					&& isset($_POST['coord_y_penitencier']) && $_POST['coord_y_penitencier'] != '') {
				
				$x_penitencier = $_POST['coord_x_penitencier'];
				$y_penitencier = $_POST['coord_y_penitencier'];
				
				$verif_x = preg_match("#^[0-9]*[0-9]$#i","$x_penitencier");
				$verif_y = preg_match("#^[0-9]*[0-9]$#i","$y_penitencier");
				
				$sql = "SELECT MAX(x_carte) as x_max, MAX(y_carte) as y_max FROM carte";
				$res = $mysqli->query($sql);
				$t = $res->fetch_assoc();
				
				$X_MAX = $t['x_max'];
				$Y_MAX  = $t['y_max'];
				
				if ($verif_x && $verif_y && in_map($x_penitencier, $y_penitencier, $X_MAX, $Y_MAX)) {
					
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
					
						$img_bat = "b10".$b_camp.".png";
						$img_bat_sup = $b_camp.".png";
						
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
			
			if (isset($_POST['liste_perso_contact_penitencier'])) {
				
				$id_perso_envoi_penitencier = $_POST['liste_perso_contact_penitencier'];
				
				$verif_id_perso = preg_match("#^[0-9]*[0-9]$#i","$id_perso_envoi_penitencier");
				
				if ($verif_id_perso) {
					
					// perso déjà dans pénitencier ?
					$sql = "SELECT * FROM perso_in_batiment WHERE id_perso='$id_perso_envoi_penitencier' AND id_instanceBat='$id_penitencier'";
					$res = $mysqli->query($sql);
					$verif_peni = $res->num_rows;
					
					if ($verif_peni == 0) {
					
						// recuperation coordonnées perso
						$sql = "SELECT nom_perso, x_perso, y_perso, clan FROM perso WHERE id_perso='$id_perso_envoi_penitencier'";
						$res = $mysqli->query($sql);
						$t = $res->fetch_assoc();
						
						$nom_perso		= $t['nom_perso'];
						$x_perso_origin = $t['x_perso'];
						$y_perso_origin = $t['y_perso'];
						$camp_perso		= $t['clan'];
						
						if ($camp_perso == 1) {
							$couleur_clan_perso = 'blue';
						}
						else if ($camp_perso == 2) {
							$couleur_clan_perso = 'red';
						}
						else if ($camp_perso == 3) {
							$couleur_clan_perso = 'green';
						}
						
						$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_perso_origin' AND y_carte='$y_perso_origin'";
						$mysqli->query($sql);
						
						// MAJ coordonnées perso
						$sql = "UPDATE perso SET x_perso='$x_penitencier', y_perso='$y_penitencier' WHERE id_perso='$id_perso_envoi_penitencier'";
						$mysqli->query($sql);
						
						// Ajout du perso dans le batiment
						$sql = "INSERT INTO perso_in_batiment VALUES ('$id_perso_envoi_penitencier','$id_penitencier')";
						$mysqli->query($sql);
						
						// evenements perso
						$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement) VALUES ($id_perso_envoi_penitencier,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>','<b>a été envoyé au Pénitencier </b>','$id_penitencier','Pénitencier','',NOW())";
						$mysqli->query($sql);
						
						$mess = "Le perso ".$nom_perso." [".$id_perso_envoi_penitencier."] a bien été envoyé dans le Pénitencier";
					}
					else {
						$mess_erreur .= "Le perso est déjà dans le Pénitencier";
					}
				}
				else {
					$mess_erreur .= "Id perso incorrect";
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
						<h2>Animation - Gestion du pénitencier</h2>
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
						<?php
						if (!$verif_penitencier) {
							echo "<form method='POST' action='anim_batiment.php'>";
							echo "	<input type='text' value='' placeholder='coordonnée x' name='coord_x_penitencier'>";
							echo "	<input type='text' value='' placeholder='coordonnée y' name='coord_y_penitencier'>";
							echo "	<button type='submit' class='btn btn-danger'>Créer un pénitencier pour mon camp</button>";
							echo '</form>';
							
							echo "<br /><br />";
						}
						else {							
							echo "<b><u>Position du Pénitencier :</u></b> ".$x_penitencier."/".$y_penitencier;
						}
						?>
					</div>
				</div>
			</div>
			
			
			<?php
			if (isset($x_penitencier) && isset($y_penitencier)) {
			?>
			<div class="row">
				<div class="col-12">
					<form method='POST' action='anim_penitencier.php'>
						<div class="form-row">
							<div class="form-group col-md-12">
								<label for="formSelectPerso">Envoyer un perso dans le pénitencier : </label>
							</div>
						</div>
						<div class="form-row">
							<div class="form-group col-md-8">
								<select class="form-control" name='liste_perso_contact_penitencier' id="formSelectPerso">
								<?php
								// récupération de tous les persos
								$sql = "SELECT id_perso, nom_perso, x_perso, y_perso, clan FROM perso, carte 
										WHERE perso.id_perso = carte.idPerso_carte
										AND x_carte >= $x_penitencier - 2
										AND x_carte <= $x_penitencier + 2
										AND y_carte >= $y_penitencier - 2
										AND y_carte <= $y_penitencier + 2
										ORDER BY perso.id_perso ASC";
								$res = $mysqli->query($sql);
								$nb_p = $res->num_rows;
								
								if ($nb_p > 0) {
								
									while ($t = $res->fetch_assoc()) {
										
										$id_perso_list 		= $t["id_perso"];
										$nom_perso_list		= $t["nom_perso"];
										$x_perso_list		= $t["x_perso"];
										$y_perso_list		= $t["y_perso"];
										$camp_perso_list	= $t["clan"];
										
										if ($camp == '1') {
											$nom_camp_perso_list = 'Nord';
										}
										else if ($camp == '2') {
											$nom_camp_perso_list = 'Sud';
										}
										else if ($camp == '3') {
											$nom_camp_perso_list = 'Indien';
										}
										
										echo "<option value='".$id_perso_list."'>".$nom_perso_list." [".$id_perso_list."] - Camp : ".$nom_camp_perso_list." - Actuellement en ".$x_perso_list."/".$y_perso_list."</option>";
										
									}
								}
								else {
									echo "<option value=''>Aucun perso au contact du Pénitencier</option>";
								}
								?>
								</select>
							</div>
							<div class="form-group col-md-4">
								<button type="submit" class="btn btn-primary">Envoyer</button>
							</div>
						</div>
					</form>
				</div>
			</div>
			<?php
			}
			?>
			
			<br />
			
			<div class="row">
				<div class="col-12">
					<div align="center">
						<h2>Liste des persos dans le pénitencier</h2>
						<div id="table_penitencier" class="table-responsive">
						<?php 
						if (isset($id_penitencier)) {
							$sql = "SELECT perso.id_perso, nom_perso, clan FROM perso, perso_in_batiment
									WHERE perso.id_perso = perso_in_batiment.id_perso
									AND id_instanceBat = $id_penitencier
									ORDER BY clan, perso.id_perso ASC";
							$res = $mysqli->query($sql);
							$nb_perso_in_penitencier = $res->num_rows;
							
							if ($nb_perso_in_penitencier > 0) {
								
								echo "<table class='table'>";
								echo "	<thead>";
								echo "		<tr>";
								echo "			<th style='text-align:center'>Perso</th><th style='text-align:center'>Action</th>";
								echo "		</tr>";
								echo "	</thead>";
								echo "	<tbody>";
								
								while ($t = $res->fetch_assoc()) {
									
									$id_perso_peni 		= $t['id_perso'];
									$nom_perso_peni 	= $t['nom_perso'];
									$camp_perso_peni	= $t['clan'];
									
									if ($camp == '1') {
										$nom_camp_perso_peni 		= 'Nord';
										$couleur_camp_perso_peni	= 'blue';
									}
									else if ($camp == '2') {
										$nom_camp_perso_peni 		= 'Sud';
										$couleur_camp_perso_peni	= 'red';
									}
									else if ($camp == '3') {
										$nom_camp_perso_peni 		= 'Indien';
										$couleur_camp_perso_peni	= 'green';
									}
									
									echo "		<tr>";
									echo "			<td><font color='".$couleur_camp_perso_peni."'><b>".$nom_perso_peni."</b> [<a href='evenement.php?infoid=".$id_perso_peni."'' target='_blank'>".$id_perso_peni."</a>]</font></td>";
									echo "			<td align='center'><a class='btn btn-danger' href='anim_penitencier.php?relacher=".$id_perso_peni."'>Relacher</a></td>";
									echo "		</tr>";
								}
								
								echo "	</tbody>";
								echo "</table>";
							}
							else {
								echo "<i>Aucun prisonnier actuellement présent dans le pénitencier</i>";
							}
						}
						else {
							echo "<i>Votre camp ne possède pas de Pénitencier, pensez à le construire via le formulaire ci-dessus</i>";
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
?>		
	