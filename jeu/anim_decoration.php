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
			
			$mess_err 	= "";
			$mess 		= "";
			
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
			
			if (isset($_POST['liste_perso_deco'])) {
						
				$id_perso_decoration = $_POST['liste_perso_deco'];
				
			}
			
			if (isset($_POST['hid_id_perso_deco']) && isset($_POST['hid_id_choix_deco']) && isset($_POST['raison_deco'])) {
				
				$id_perso_deco 	= $_POST['hid_id_perso_deco'];
				$id_choix_deco 	= $_POST['hid_id_choix_deco'];
				$raison_deco	= addslashes($_POST['raison_deco']);
				
				// Ajout de la décoration
				$sql = "INSERT INTO perso_as_decoration (id_perso, id_decoration, raison_decoration, date_decoration) VALUES ('$id_perso_deco', '$id_choix_deco', '$raison_deco', NOW())";
				$mysqli->query($sql);
				
				$mess .= "Décoration attribuée";
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
						<h2>Animation - Gestion des décorations des persos</h2>
						
						<font color='red'><?php echo $mess_err; ?></font>
						<font color='blue'><?php echo $mess; ?></font>
					</div>
				</div>
			</div>
			
			<p align="center">
				<a class='btn btn-info' href='anim_perso.php'>Retour gestion des persos</a>
				<a class="btn btn-primary" href="animation.php">Retour page principale d'animation</a>
			</p>
			
			<div class="row">
				<div class="col-12">
					<form method='POST' action='anim_decoration.php'>
						<div class="form-row">
							<div class="form-group col-md-12">
								<label for="formSelectPerso">Décorer le perso : </label>
							</div>
						</div>
						<div class="form-row">
							<div class="form-group col-md-8">
								<select class="form-control" name='liste_perso_deco' id="formSelectPerso" onchange="this.form.submit()">
								<?php
								// récupération de tous les persos de son camp 
								$sql = "SELECT id_perso, nom_perso FROM perso WHERE clan='$camp' ORDER BY id_perso ASC";
								$res = $mysqli->query($sql);
								
								echo "<option value=''>-- Selectionnez un perso --</option>";
								
								while ($t = $res->fetch_assoc()) {
									
									$id_perso_list 	= $t["id_perso"];
									$nom_perso_list	= $t["nom_perso"];
									
									echo "<option value='".$id_perso_list."' ";
									if (isset($id_perso_decoration) && $id_perso_decoration == $id_perso_list) {
										echo "selected";
									}
									echo ">".$nom_perso_list." [".$id_perso_list."]</option>";
									
								}
								?>
								</select>
							</div>
							<div class="form-group col-md-4">
								<button type="submit" class="btn btn-primary">Voir</button>
							</div>
						</div>
					</form>
				</div>
			</div>
			
			<br />
			
			<div class="row">
				<div class="col-12">
					<?php
					if (isset($_POST['liste_perso_deco']) && $_POST['liste_perso_deco'] != "") {
						
						$id_perso_deco = $_POST['liste_perso_deco'];
						
						// récupération des décorations reçues par le perso
						$sql = "SELECT date_decoration, raison_decoration, image_decoration FROM perso_as_decoration, decorations 
								WHERE perso_as_decoration.id_decoration = decorations.id_decoration
								AND id_perso='$id_perso_deco'
								ORDER BY date_decoration";
						$res = $mysqli->query($sql);
						$nb_event = $res->num_rows;
						
						if ($nb_event) {
							echo "<center><font color=red><b>Décorations déjà reçues</b></font></center>";
							echo "<center>";
							echo "<table border=1 class='table'>";
							echo "	<tr>";
							echo "		<th style='text-align:center' width=25%>date</th>";
							echo "		<th style='text-align:center' width=25%>décoration</th>";
							echo "		<th style='text-align:center'>Raison</th>";
							echo "	</tr>";
						
							while ($t = $res->fetch_assoc()){
								
								$date_deco		= $t['date_decoration'];
								$raison_deco	= htmlspecialchars($t['raison_decoration']);
								$image_deco 	= $t['image_decoration'];
								
								echo "	<tr>";
								echo "		<td align='center'>".$date_deco."</td>";
								echo "		<td align='center'><img src='../images/medailles/".$image_deco."' width='20' height='40'/></td>";
								if (trim($raison_deco) != "") {
									echo "		<td align='center'>".$raison_deco."</td>";
								}
								else {
									echo "		<td align='center'>Pour son engagement et son courage</td>";
								}
								echo "	</tr>";
							}
							echo "</table></center><br />";
						}
						else {
							echo "<center><i>Aucune décoration reçues</i></center>";
						}
						
						// Récupération et affichage de la liste des décorations
						$sql = "SELECT id_decoration, description_decoration, image_decoration FROM decorations WHERE camp_decoration='$camp'";
						$res = $mysqli->query($sql);
						
						echo "<b><u>Choix de la décoration : </u></b><br />";
						
						echo "<table><tr>";
						
						while ($t = $res->fetch_assoc()) {
							
							$id_decoration		= $t['id_decoration'];
							$desc_decoration	= htmlspecialchars($t['description_decoration']);
							$img_decoration		= $t['image_decoration'];
							
							echo "<td>";
							
							echo "<table style='height:400px;'>";
							echo "	<tr>";
							echo "		<td align='center' style='height:300px;'><a href='anim_decoration.php?id_perso=".$id_perso_deco."&id_deco=".$id_decoration."'><img src='../images/medailles/".$img_decoration."'/></a></td>";
							echo "	</tr>";
							echo "	<tr>";
							echo "		<td align='center' style='height:50px;'>".$desc_decoration."</td>";
							echo "	</tr>";
							echo "	<tr>";
							echo "		<td align='center' style='height:50px;'><a href='anim_decoration.php?id_perso=".$id_perso_deco."&id_deco=".$id_decoration."' class='btn btn-primary'>Attribuer</a></td>";
							echo "	</tr>";
							echo "</table>";
							
							echo "</td>";
						}
						
						echo "</tr></table>";
					}
					
					if (isset($_GET['id_perso']) && trim($_GET['id_perso']) != "" && isset($_GET['id_deco']) && trim($_GET['id_deco']) != "") {
				
						$id_perso_deco = $_GET['id_perso'];
						$id_choix_deco = $_GET['id_deco'];
						
						$verif_id_perso = preg_match("#^[0-9]*[0-9]$#i","$id_perso_deco");
						$verif_id_deco 	= preg_match("#^[0-9]*[0-9]$#i","$id_choix_deco");
						
						if ($verif_id_perso && $verif_id_deco) {
							
							// On vérifie le camp du perso 
							$sql = "SELECT clan FROM perso WHERE id_perso = '$id_perso_deco'";
							$res = $mysqli->query($sql);
							$t_vp = $res->fetch_assoc();
							
							$camp_perso_deco = $t_vp['clan'];
							
							// et le camp de la decoration
							$sql = "SELECT camp_decoration FROM decorations WHERE id_decoration = '$id_choix_deco'";
							$res = $mysqli->query($sql);
							$t_vd = $res->fetch_assoc();
							
							$camp_choix_deco = $t_vd['camp_decoration'];
							
							if ($camp_perso_deco == $camp && $camp_choix_deco == $camp) {
								
								echo "Raison de la décoration (qui s'affichera sur le CV du perso) : <br />";
								
								echo "<form method='POST' action='anim_decoration.php'>";
								echo "	<input type='hidden' name='hid_id_perso_deco' value='".$id_perso_deco."'>";
								echo "	<input type='hidden' name='hid_id_choix_deco' value='".$id_choix_deco."'>";
								echo "	<input type='text' name='raison_deco' value=''>";
								echo "	<input type='submit' value='Valider'>";
								echo "</form>";
							}
							else {
								// parametres incorrectes / modifiés
								$text_triche = "Tentative modification parametre animation decoration - camp perso ou decoration incorrect";
								
								$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id', '$text_triche')";
								$mysqli->query($sql);
								
								header("Location:jouer.php");
							}
						}
						else {
							// parametres incorrectes / modifiés
							$text_triche = "Tentative modification parametre animation decoration";
							
							$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id', '$text_triche')";
							$mysqli->query($sql);
							
							header("Location:jouer.php");
						}
					}
					?>
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
	