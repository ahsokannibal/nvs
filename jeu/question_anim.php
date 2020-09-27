<?php
session_start();
require_once("../fonctions.php");

$mysqli = db_connexion();

include ('../nb_online.php');

// recupération config jeu
$dispo = config_dispo_jeu($mysqli);
$admin = admin_perso($mysqli, $_SESSION["id_perso"]);

if($dispo || $admin){
	
	if (isset($_SESSION["id_perso"])) {
		
		//recuperation des variables de sessions
		$id = $_SESSION["id_perso"];
		
		$sql = "SELECT type_perso FROM perso WHERE id_perso='$id'";
		$res = $mysqli->query($sql);
		$t = $res->fetch_assoc();
		
		$type_perso = $t['type_perso'];
		
		if ($type_perso != 6) {
		
			$mess = "";
			$mess_erreur = "";
			
			if (!isset($_GET['capture'])) {
				if (isset($_POST['titreQuestion']) && trim($_POST['titreQuestion']) != "") {
					
					if (isset($_POST['question']) && trim($_POST['question']) != "") {
						
						$titre 		= addslashes($_POST['titreQuestion']);
						$question 	= addslashes($_POST['question']);
						
						$sql = "SELECT clan FROM perso WHERE id_perso='$id'";
						$res = $mysqli->query($sql);
						$t = $res->fetch_assoc();
						
						$id_camp = $t['clan'];
						
						$sql = "INSERT INTO anim_question(date_question, id_perso, titre, question, id_camp) VALUES (NOW(), '$id', '$titre', '$question', '$id_camp')";
						$mysqli->query($sql);
						
						$mess = "Question envoyée avec succès, la réponse sera envoyée sur votre messagerie.";
						
					}
					else {
						$mess_erreur .= "La question /remontée est obligatoire";
					}
				}
				else {
					$mess_erreur .= "Les champs titre et question/remontée sont obligatoire, pensez à les remplir avant envoi";
				}
			}
			else {
				if (isset($_POST['titreCapture']) && trim($_POST['titreCapture']) != "") {
					
					if (isset($_POST['preuves']) && trim($_POST['preuves']) != "" && isset($_POST['idPersoCapture']) && trim($_POST['idPersoCapture']) != "") {
						
						$titre 				= addslashes($_POST['titreCapture']);
						$preuves 			= addslashes($_POST['preuves']);
						$id_perso_capture	= $_POST['idPersoCapture'];
						
						// verification id_perso_capture
						$verif_id_perso = preg_match("#^[0-9]*[0-9]$#i","$id_perso_capture");
						
						if ($verif_id_perso) {
							
							// On vérifie si une remontée de capture a déjà été effectuée aujourd'hui contre ce perso
							$sql = "SELECT * FROM anim_capture WHERE id_perso_capture='$id_perso_capture' AND date_capture >= CURDATE() - INTERVAL 1 DAY";
							$res = $mysqli->query($sql);
							$verif_capture_jour = $res->num_rows;
							
							if ($verif_capture_jour) {
								$mess_erreur .= "Une capture a déjà été remontée sur ce perso dasn les dernières 24h";
							}
							else {
								$sql = "INSERT INTO anim_capture(date_capture, id_perso, id_perso_capture, titre, message) VALUES (NOW(), '$id', '$id_perso_capture', '$titre', '$preuves')";
								$mysqli->query($sql);
								
								$mess = "Capture remontée avec succès. Les animateurs peuvent éventuellement vous contacter par MP pour obtenir plus de précision si necessaire.";
							}
						}
						else {
							// parametres incorrectes / modifiés
							$text_triche = "Champ matricule perso page capture RP incorrect";
							
							$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id', '$text_triche')";
							$mysqli->query($sql);
							
							header("Location:jouer.php");
						}
					}
					else {
						$mess_erreur .= "Les preuves et le matricule du perso capturé sont obligatoires";
					}
				}
				else {
					$mess_erreur .= "Les champs titre, matricule du perso et preuves sont obligatoire, pensez à les remplir avant envoi";
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
		<div class="container">
			
			<div class="row">
				<div class="col-12">
					<div align="center">
						<h2>Questions / remontées pour l'animation</h2>
					</div>
				</div>
			</div>
			
			<p align="center"><input type="button" value="Fermer la fenêtre de question / remontée aux animateurs" onclick="window.close()"></p>
			
			<div class="row">
				<div class="col-12">
					<div align="center">
						<?php
						if (!isset($_GET['capture'])) {
						?>
						<a href='question_anim.php?capture=ok' class='btn btn-primary'>Remonter une capture RP</a>
						<?php
						}
						else {
							echo "<a href='question_anim.php' class='btn btn-primary'>Question à l'animation</a>";
						}
						echo "<br /><font color='blue'>".$mess."</font><br />";
						echo "<font color='red'><b>".$mess_erreur."</b></font><br />";
						?>
					</div>
				</div>
			</div>
			
			<br />
			
			<div class="row">
				<div class="col-12">
					<div align="center">
						<?php
						if (!isset($_GET['capture'])) {
						?>
						<form method='post' action='question_anim.php'>
							<div class="form-group col-md-6">
								<label for="titreQuestion">Titre <font color='red'>*</font></label>
								<input type="text" class="form-control" id="titreQuestion" name="titreQuestion" maxlength="40">
							</div>
							<div class="form-group col-md-8">
								<label for="question">Votre question / remontée <font color='red'>*</font></label>
								<textarea  class="form-control" cols="100" rows="20" id="question" name="question"></textarea >
							</div>
							<div class="form-group col-md-6">
								<input type="submit" name="envoyer" value="envoyer" class='btn btn-primary'>
							</div>
						</form>
						<?php
						}
						else {
						?>
						<form method='post' action='question_anim.php?capture=ok'>
							<div class="form-group col-md-6">
								<label for="titreCapture">Titre <font color='red'>*</font></label>
								<input type="text" class="form-control" id="titreCapture" name="titreCapture" maxlength="40">
							</div>
							<div class="form-group col-md-6">
								<label for="idPersoCapture">Matricule du perso capturé <font color='red'>*</font></label>
								<input type="text" class="form-control" id="idPersoCapture" name="idPersoCapture" maxlength="5">
							</div>
							<div class="form-group col-md-8">
								<label for="preuves">Vos preuves de capture (ajouter les liens vers les images) <font color='red'>*</font></label>
								<textarea  class="form-control" cols="100" rows="20" id="preuves" name="preuves"></textarea >
							</div>
							<div class="form-group col-md-6">
								<input type="submit" name="envoyer" value="envoyer" class='btn btn-primary'>
							</div>
						</form>
						<?php
						}
						?>
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
<?php
		}
		else {
			echo "<center><font color='red'>Les chiens ne peuvent pas accéder à cette page.</font></center>";
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
</html>