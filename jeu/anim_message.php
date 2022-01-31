<?php
session_start();
require_once("../fonctions.php");
require_once("f_carte.php");

$mysqli = db_connexion();

include ('../nb_online.php');

// recupération config jeu
$dispo = config_dispo_jeu($mysqli);
$admin = admin_perso($mysqli, $_SESSION["id_perso"]);

if($dispo == '1' || $admin){
	
	if (isset($_SESSION["id_perso"])) {
		
		//recuperation des variables de sessions
		$id = $_SESSION["id_perso"];
		
		$mess			= "";
		$mess_erreur	= "";
		
		if (anim_perso($mysqli, $id)) {
			
			// Récupération du camp de l'animateur 
			$sql = "SELECT clan FROM perso WHERE id_perso='$id'";
			$res = $mysqli->query($sql);
			$t = $res->fetch_assoc();
			
			$camp = $t['clan'];
			
			if (isset($_GET['cible'])) {
				
				$cible = $_GET['cible'];
				
				if ($cible == "camp") {
					$titre = "Message à son camp";
				}
				else if ($cible == "compagnie") {
					$titre = "Message à destination des chefs de compagnies / sections";
				}
				else if ($cible == "all") {
					$titre = "Message à tous les persos";
				}
			}
			else {
				$cible = "camp";
				$titre = "Message à son camp";
			}
			
			// Envoi message
			if(isset($_POST["envoyer"])) {
				
				if (isset($cible) && trim($cible) != "") {
					
					$error = false;
					
					if ($cible == "camp") {
						// Récupération de tous les persos de mon camp
						$sql_m = "SELECT id_perso FROM perso WHERE clan='$camp' AND chef='1'";
						$res_m = $mysqli->query($sql_m);						
					}
					else if ($cible == "compagnie") {
						// Récupération de tous les chefs de compagnie / section
						$sql_m = "SELECT perso.id_perso FROM perso, perso_in_compagnie 
								WHERE perso.id_perso = perso_in_compagnie.id_perso
								AND perso_in_compagnie.poste_compagnie='1'
								AND clan='$camp'";
						$res_m = $mysqli->query($sql_m);
					}
					else if ($cible == "all") {
						// Récupération de tous les persos
						$sql_m = "SELECT id_perso FROM perso WHERE chef='1'";
						$res_m = $mysqli->query($sql_m);
					}
					else {
						$error = true;
					}
					
					if (!$error) {
						
						// Nom expediteur
						$sql = "SELECT nom_perso FROM perso WHERE id_perso='$id'";
						$res = $mysqli->query($sql);
						$t_p = $res->fetch_assoc();
						
						$expediteur = $t_p['nom_perso'];
						$message = addslashes($_POST["message"]);
						if(trim($_POST["objet"]) == ""){
							$txt_objet = "[Important] Message de l'Animation";
						}
						else {
							$txt_objet = $_POST["objet"];
						}
						$objet = htmlentities(addslashes($txt_objet));
						
						$lock = "LOCK TABLE message WRITE";
						$mysqli->query($lock);
						
						// creation du message
						$sql = "INSERT INTO message (id_expediteur, expediteur_message, date_message, contenu_message, objet_message) 
								VALUES ('" . $id . "', '" . $expediteur . "', NOW(), '" . $message. "', '" . $objet. "')";
						$mysqli->query($sql);
						$id_message = $mysqli->insert_id;
						
						$unlock = "UNLOCK TABLES";
						$mysqli->query($unlock);
						
						while ($t_m = $res_m->fetch_assoc()) {
							
							$id_p = $t_m['id_perso'];
							
							// assignation du message au perso
							$sql = "INSERT INTO message_perso VALUES ('$id_message', '$id_p', '1', '0', '1', '0')";
							$mysqli->query($sql);
						}
						
						$mess = "Messages envoyés";
						
					}
					else {
						$mess_erreur = "Cible incorrecte";
					}
				}
				else {
					$mess_erreur = "Cible non défini";
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
		<div class="container-fluid">
		
			<div class="row">
				<div class="col-12">
					<div align="center">
						<h2>Animation - <?php echo $titre; ?></h2>
					</div>
				</div>
			</div>
			
			<p align="center"><a class="btn btn-primary" href="animation.php">Retour page principale d'animation</a></p>
			
			<div class="row">
				<div class="col-12">
					<div align="center">
						<a class="btn btn-warning" href="anim_message.php?cible=camp">Message à son camp</a>
						<a class="btn btn-warning" href="anim_message.php?cible=compagnie">Message aux chefs de compagnie</a>
						<a class="btn btn-warning" href="anim_message.php?cible=all">Message à tous</a>
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
					<form method="post" action="">
						<table border=1 align="center" cellpadding=2 cellspacing=1 width=100%>
							<tr class="messl">
								<div class="form-group">
									<td><label for="destinataireInput">Destinataire : </label></td> 
									<td colspan=3><?php echo $cible?></td>
								</div>
							</tr>
							<tr class="messl">
								<div class="form-group">
									<td><label for="inputObjet">Objet : </label></td>
									<td colspan=3><input type="text" class="form-control" id="inputObjet" name="objet" size="30"></td>
								</div>
							</tr>
							<tr class="messl">
								<td><div class="form-group"><label for="textareaMessageImput">Message : </label></td> 
								<td colspan=3 align="center"><TEXTAREA class="form-control" id="textareaMessageImput" name="message" rows="15" cols="50" ></TEXTAREA></td>
							</tr>
						</table>
						<div align="center"><input type="submit" name="envoyer" value="envoyer" class='btn btn-success'></div>
					</form>
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
