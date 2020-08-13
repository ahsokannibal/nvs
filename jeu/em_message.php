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
		
		// Le perso est-il membre de l'etat major
		$sql = "SELECT camp_em FROM perso_in_em WHERE id_perso='$id'";
		$res = $mysqli->query($sql);
		$t = $res->fetch_assoc();
		$verif = $res->num_rows;
		
		if ($verif) {
			
			$msg_erreur = "";
			$msg = "";
		
			$camp_em = $t['camp_em'];
			
			if ($camp_em == 1) {
				$image_em = "em_nord.png";
			} else {
				$image_em = "em_sud.png";
			}
			
			// Envoi message
			if(isset($_POST["envoyer"])) {
				
				if (isset($_GET['cible'])) {
					$cible = $_GET['cible'];
					$error = false;
					
					if ($cible == "camp") {
						// Récupération de tous les persos de mon camp
						$sql_m = "SELECT id_perso FROM perso WHERE clan='$camp_em'";
						$res_m = $mysqli->query($sql_m);						
					}
					else if ($cible == "compagnie") {
						// Récupération de tous les chefs de compagnie / section
						$sql_m = "SELECT perso.id_perso FROM perso, perso_in_compagnie 
								WHERE perso.id_perso = perso_in_compagnie.id_perso
								AND perso_in_compagnie.poste_compagnie='1'
								AND clan='$camp_em'";
						$res_m = $mysqli->query($sql_m);
					}
					else if ($cible == "em") {
						// Récupération de tous les membres de l'EM de mon camp
						$sql_m = "SELECT id_perso FROM perso_in_em 
								WHERE camp_em='$camp_em'";
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
							$txt_objet = "[Important] Message de l'Etat Major";
						}
						else {
							$txt_objet = $_POST["objet"];
						}
						$objet = htmlentities(addslashes($txt_objet));
						
						$lock = "LOCK TABLE (message) WRITE";
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
						
						$msg = "Messages envoyés";
						
					}
					else {
						$msg_erreur = "Cible incorrecte";
					}
				}
				else {
					$msg_erreur = "Cible non défini";
				}
				
			}
?>
		
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<title>Nord VS Sud - Etat Major</title>
		
		<!-- Required meta tags -->
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		
		<!-- Bootstrap CSS -->
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
	</head>
	<body>
		<div class="container">
			
			<nav class="navbar navbar-expand-lg navbar-light bg-light">
				<a class="navbar-brand" href="#"><img src='../images/<?php echo $image_em; ?>' width="80" height="60" alt=""></a>
				<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
					<span class="navbar-toggler-icon"></span>
				</button>
				<div class="collapse navbar-collapse" id="navbarNavAltMarkup">
					<div class="navbar-nav">
						<ul class="navbar-nav">
							<li class="nav-item">
								<a class="nav-link" href="etat_major.php">Validation compagnies</a>
							</li>
						</ul>
						<ul class="navbar-nav">
							<li class="nav-item dropdown">
								<a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLinkCarte" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
									Carte suivante
								</a>
								<div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLinkCarte">
									<a class="dropdown-item" href="em_cartes_suivante.php">Voir les carte suivante</a>
									<a class="dropdown-item" href="em_definir_infra.php">Positionner les infrastructures</a>
								</div>
							</li>
						</ul>
						<ul class="navbar-nav">
							<li class="nav-item dropdown active">
								<a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
									Messages
								</a>
								<div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
									<a class="dropdown-item" href="em_message.php?cible=camp">Message à son camp</a>
									<a class="dropdown-item" href="em_message.php?cible=compagnie">Message aux chefs de compagnie / section</a>
									<a class="dropdown-item" href="em_message.php?cible=em">Messages aux autres membres de l'EM</a>
								</div>
							</li>
						</ul>
					</div>
				</div>
			</nav>
		
			<p align="center"><input type="button" value="Fermer cette fenêtre" onclick="window.close()"></p>
			
			<?php
			if (isset($_GET['cible'])) {
				$cible = $_GET['cible'];
				
				if ($cible == "camp") {
					$titre = "Message pour tous les persos de mon camp";
				}
				else if ($cible == "compagnie") {
					$titre = "Message à destination des chefs de compagnies / sections";
				}
				else if ($cible == "em") {
					$titre = "Message à destination des membres de l'EM";
				}
				else {
					$titre = "";
				}
				
			}
			?>
			<div class="row justify-content-center">
				<div class="col-12">
				<?php 
				if (isset($msg_erreur) && trim($msg_erreur) != "") {
					echo "<font color='red'>".$msg_erreur."</font>";
				}
				if (isset($msg) && trim($msg) != "") {
					echo "<font color='blue'>".$msg."</font>";
				}
				?>
				</div>
			</div>
			
			<h1><?php echo $titre; ?></h1>
			
			<div class="row justify-content-center">
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
						<div align="center"><INPUT TYPE="SUBMIT" name="envoyer" VALUE="envoyer"></div>
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
			// Un joueur essaye d'acceder à la page sans être de l'état major
			$text_triche = "Tentative accés page etat major sans y avoir les droits";
			
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