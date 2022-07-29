<?php
session_start();
require_once("../fonctions.php");

$mysqli = db_connexion();

include ('../nb_online.php');

// recupération config jeu
$dispo = config_dispo_jeu($mysqli);
$admin = admin_perso($mysqli, $_SESSION["id_perso"]);

if($dispo == '1' || $admin){
	
	if (isset($_SESSION["id_perso"])) {
		
		//recuperation des variables de sessions
		$id = $_SESSION["id_perso"];
		
		$sql = "SELECT id_perso, clan FROM perso WHERE perso.idJoueur_perso = (SELECT idJoueur_perso FROM perso WHERE id_perso='$id') AND chef='1'";
		$res =  $mysqli->query($sql);
		$t = $res->fetch_assoc();
		
		$id_chef = $t['id_perso'];
		$id_camp = $t['clan'];
		
		$mess = "";
		$mess_erreur = "";
		
		if (isset($_POST['nomMulti']) && $_POST['nomMulti'] != "" 
				&& isset($_POST['idMulti']) && $_POST['idMulti'] != "" 
				&& isset($_POST['situation']) && $_POST['situation'] != "") {
			
			$nom_multi 	= $_POST['nomMulti'];
			$id_multi	= $_POST['idMulti'];
			$situation	= htmlentities(addslashes($_POST['situation']));
			
			$verifId = preg_match("#^[0-9]*[0-9]$#i","$id_multi");
			
			if (!filtre($nom_multi,1,20) || ctype_digit($nom_multi) || strpos($nom_multi,'--') !== false){
				$mess_erreur .= "Le nom du perso renseigné n'est pas conforme";
			}
			else {
				if ($verifId) {
					
					// On verifie que l'id du perso existe bien
					$sql = "SELECT clan FROM perso WHERE id_perso='$id_multi' AND chef='1'";
					$res = $mysqli->query($sql);
					$nb = $res->num_rows;
					
					if ($nb == 1) {
						$t = $res->fetch_assoc();
						
						$campBaby = $t['clan'];
						
						if ($id_camp == $campBaby) {
							
							// On vérifie s'il n'est pas déjà déclaré
							$sql = "SELECT * FROM declaration_multi WHERE id_perso='$id_chef' AND id_multi='$id_multi'";
							$res = $mysqli->query($sql);
							$verif = $res->num_rows;
							
							if ($verif == 0) {
								$sql = "INSERT INTO declaration_multi (id_perso, id_multi, situation, date_declaration) VALUES ('$id_chef', '$id_multi', '$situation', NOW())";
								$mysqli->query($sql);
								
								$mess .= "Déclaration de multi avec le perso ".$nom_multi."[".$id_multi."] bien enregistré";
							}
							else {
								$mess_erreur .= "Vous avez déjà effectué une déclaration de multi avec ce perso !";
							}
						}
						else {
							$mess_erreur .= "Vous n'avez pas le droit d'être en multi avec un perso d'un autre camp !";
						}
					}
					else {
						$mess_erreur .= "L'id du perso renseigné n'existe pas";
					}
				}
				else {
					$mess_erreur .= "L'id renseigné n'est pas conforme";
				}
			}
		}
		
		// Récupération des multis déclarés
		$sql_multi_courant = "SELECT * FROM declaration_multi WHERE id_perso='$id_chef'";
		$res_multi_courant = $mysqli->query($sql_multi_courant);
		$nb_multi_courant = $res_multi_courant->num_rows;		
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
		<div class="container">
			
			<div class="row">
				<div class="col-12">
					<div align="center">
						<h2>Déclaration multicompte</h2>
					</div>
				</div>
			</div>
			
			<p align="center"><input type="button" value="Fermer la fenêtre" onclick="window.close()"></p>
			
			<div class="row">
				<div class="col-12">
					<div align="center">
						<a href='compte.php' class='btn btn-primary'>Retour à la page Compte</a>
						<?php
						echo "<font color='blue'>".$mess."</font><br />";
						echo "<font color='red'><b>".$mess_erreur."</b></font><br />";
						?>
					</div>
				</div>
			</div>

			<p>A des fins de transparence, les multicomptes (même famille, colocs, ...) sont à déclarer sur le salon dédié du discord commun.</p>
			<p>Les multicomptes sauvages sont formellement interdits et seront sévèrement sanctionnés.</p>
			
<!---
			<div class="row">
				<div class="col-12">
					<div align="center">
						<?php
						if ($nb_multi_courant) {
							
							echo "<font color='blue'><u>Vous êtes déclaré en multi avec les persos (chefs) suivant</u> : </font><br />";
							
							while ($t_multi_courant = $res_multi_courant->fetch_assoc()) {
								
								$id_multi_courant = $t_multi_courant['id_multi'];
								
								$sql = "SELECT nom_perso FROM perso WHERE id_perso='$id_multi_courant'";
								$res = $mysqli->query($sql);
								$t = $res->fetch_assoc();
								
								$nom_perso_multi_courant = $t['nom_perso'];
								
								echo $nom_perso_multi_courant." [".$id_multi_courant."]<br />";
							}
							
							echo "<br />";
						}
						?>
					</div>
				</div>
			</div>
			
			<br />
			
			<div class="row">
				<div class="col-12">
					<div align="center">
						<form method='post' action='declaration_multi.php'>
							<div class="form-group col-md-6">
								<label for="nomMulti">Nom du chef du perso avec lequel vous déclarez le multi <font color='red'>*</font></label>
								<input type="text" class="form-control" id="nomMulti" name="nomMulti" maxlength="40">
							</div>
							<div class="form-group col-md-6">
								<label for="idMulti">ID du chef du perso avec lequel vous déclarez le multi <font color='red'>*</font></label>
								<input type="text" class="form-control" id="idMulti" name="idMulti" maxlength="40">
							</div>
							<div class="form-group col-md-8">
								<label for="situation">Explication de votre situation <font color='red'>*</font></label>
								<textarea  class="form-control" cols="100" rows="20" id="situation" name="situation"></textarea >
							</div>
							<div class="form-group col-md-6">
								<input type="submit" name="envoyer" value="envoyer" class='btn btn-primary'>
							</div>
						</form>
					</div>
				</div>
			</div>
			
-->
		</div>
		
		<!-- Optional JavaScript -->
		<!-- jQuery first, then Popper.js, then Bootstrap JS -->
		<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
		<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
	</body>
<?php
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
</html>
