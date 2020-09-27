<?php
session_start();
require_once("../fonctions.php");

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
			
			$mess = "";
			$mess_erreur = "";
			
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
			
			if (isset($_GET['id']) && $_GET['id'] != "") {
				
				$id_capture_rp = $_GET['id'];
				
				$verif_id_capture = preg_match("#^[0-9]*[0-9]$#i","$id_capture_rp");
				
				if ($verif_id_capture) {
					
					// Ob vérifie que la capture RP existe bien
					$sql = "SELECT * FROM anim_capture WHERE id='$id_capture_rp'";
					$res = $mysqli->query($sql);
					$verif_capture = $res->num_rows;
					
					if ($verif_capture) {
						
						if (isset($_GET['action']) && $_GET['action'] != "refuser") {
							
							// MAJ capture
							$sql = "UPDATE anim_capture SET statut=3 WHERE id='$id_capture_rp'";
							$mysqli->query($sql);
							
							// Envoi MP capture refusée au perso qui a fait la remontée
							
							
						}
						else if (isset($_GET['action']) && $_GET['action'] != "valider") {
							
							// MAJ capture
							$sql = "UPDATE anim_capture SET statut=1 WHERE id='$id_capture_rp'";
							$mysqli->query($sql);
							
							// Envoi MP capture validée aux 2 persos
							
							// 
						}
						else if (isset($_GET['action']) && $_GET['action'] != "valider_encerclement") {
							
							// MAJ capture
							$sql = "UPDATE anim_capture SET statut=2 WHERE id='$id_capture_rp'";
							$mysqli->query($sql);
							
							// Envoi MP capture validée aux 2 persos
						}
					}
					else {
						// parametres incorrectes / modifiés
						$text_triche = "Tentative modification parametre id capture RP - ID non existant";
						
						$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id', '$text_triche')";
						$mysqli->query($sql);
						
						header("Location:jouer.php");
					}
				}
				else {
					// parametres incorrectes / modifiés
					$text_triche = "Tentative modification parametre id capture RP";
					
					$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id', '$text_triche')";
					$mysqli->query($sql);
					
					header("Location:jouer.php");
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
						<h2>Animation - Captures RP</h2>
					</div>
				</div>
			</div>
			
			<div class="row">
				<div class="col-12">
					<div align="center">
						<a class="btn btn-primary" href="animation.php">Retour page principale d'animation</a>
						<?php
						if (isset($_GET['histo'])) {
						?>
						<a class="btn btn-success" href="anim_capture_rp.php">Retour à la liste des captures en attente</a>
						<?php
						}
						if (!isset($_GET['histo'])) {
						?>
						<a class="btn btn-success" href="anim_capture_rp.php?histo=ok">Voir l'historique des captures déjà traitées</a>
						<?php 
						}
						?>
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
			
			<?php
			if (isset($_GET['histo']) && $_GET['histo'] == "ok") {
					
				// Récupération des questions anims répondues
				$sql = "SELECT anim_capture.id, anim_capture.id_perso, anim_capture.id_perso_capture, date_capture, titre, message, statut FROM anim_capture 
						WHERE statut != '0'
						ORDER BY anim_capture.id ASC";
				$res = $mysqli->query($sql);
			?>
				<div class="row">
					<div class="col-12">
						<div class="table-responsive">
							<table class="table">
								<thead>
									<tr>
										<th style='text-align:center'>Date de remontée</th>
										<th style='text-align:center'>Perso</th>
										<th style='text-align:center'>Perso Capturé</th>
										<th style='text-align:center'>Titre</th>
										<th style='text-align:center'>Preuves</th>
										<th style='text-align:center'>Statut de la capture</th>
									</tr>
								</thead>
								<tbody>
									<?php
									while ($t = $res->fetch_assoc()) {
										
										$id_capture			= $t['id'];
										$id_perso 			= $t['id_perso'];
										$id_perso_capture	= $t['id_perso_capture'];
										$date_capture		= $t['date_capture'];
										$titre_capture		= $t['titre'];
										$message			= $t['message'];
										$statut_capture		= $t['statut'];
										
										$sql_p = "SELECT nom_perso, clan FROM perso WHERE id_perso='$id_perso '";
										$res_p = $mysqli->query($sql_p);
										$t_p = $res_p->fetch_assoc();
										
										$nom_perso 	= $t_p['nom_perso'];
										$camp_perso	= $t_p['clan'];
										
										$sql_p = "SELECT nom_perso, clan FROM perso WHERE id_perso='$id_perso_capture '";
										$res_p = $mysqli->query($sql_p);
										$t_p = $res_p->fetch_assoc();
										
										$nom_perso_capture 	= $t_p['nom_perso'];
										$camp_perso_capture	= $t_p['clan'];
										
										echo "<tr>";
										echo "	<td align='center'>".$date_capture."</td>";
										echo "	<td align='center'>".$nom_perso." [<a href='evenement.php?infoid=".$id_perso."'>".$id_perso."</a>]</td>";
										echo "	<td align='center'>".$nom_perso_capture." [<a href='evenement.php?infoid=".$id_perso_capture."'>".$id_perso_capture."</a>]</td>";
										echo "	<td align='center'>".$titre_capture."</td>";
										echo "	<td align='center'>".$message."</td>";
										echo "	<td align='center'>";
										if ($statut_capture == 1) {
											echo "<font color='green'><b>Validée - Capture par RP</b></font>";
										}
										else if ($statut_capture == 2) {
											echo "<font color='green'><b>Validée - Capture par Encerclement</b></font>";
										}
										else {
											echo "<font color='red'><b>Refusée</b></font>";
										}
										echo "	</td>";
										echo "</tr>";
									}
									?>
								</tbody>
							</table>
						</div>			
					</div>
				</div>
			<?php
			}
			else {
				
				// Récupération des capture en attente de traitement
				$sql = "SELECT anim_capture.id, anim_capture.id_perso, anim_capture.id_perso_capture, date_capture, titre, message, statut FROM anim_capture 
						WHERE statut = '0'
						ORDER BY anim_capture.id ASC";
				$res = $mysqli->query($sql);
			?>
			
				<div class="row">
					<div class="col-12">
						<div class="table-responsive">
							<table class="table">
								<thead>
									<tr>
										<th style='text-align:center'>Date de remontée</th>
										<th style='text-align:center'>Perso</th>
										<th style='text-align:center'>Perso Capturé</th>
										<th style='text-align:center'>Titre</th>
										<th style='text-align:center'>Preuves</th>
										<th style='text-align:center'>Action</th>
									</tr>
								</thead>
								<tbody>
									<?php
									while ($t = $res->fetch_assoc()) {
										
										$id_capture			= $t['id'];
										$id_perso 			= $t['id_perso'];
										$id_perso_capture	= $t['id_perso_capture'];
										$date_capture		= $t['date_capture'];
										$titre_capture		= $t['titre'];
										$message			= $t['message'];
										$statut_capture		= $t['statut'];
										
										$sql_p = "SELECT nom_perso, clan FROM perso WHERE id_perso='$id_perso '";
										$res_p = $mysqli->query($sql_p);
										$t_p = $res_p->fetch_assoc();
										
										$nom_perso 	= $t_p['nom_perso'];
										$camp_perso	= $t_p['clan'];
										
										$sql_p = "SELECT nom_perso, clan FROM perso WHERE id_perso='$id_perso_capture '";
										$res_p = $mysqli->query($sql_p);
										$t_p = $res_p->fetch_assoc();
										
										$nom_perso_capture 	= $t_p['nom_perso'];
										$camp_perso_capture	= $t_p['clan'];
										
										echo "<tr>";
										echo "	<td align='center'>".$date_capture."</td>";
										echo "	<td align='center'>".$nom_perso." [<a href='evenement.php?infoid=".$id_perso."'>".$id_perso."</a>] <a href='nouveau_message.php?pseudo=".$nom_perso."' target='_blank'><img src='../images/messagerie.png' width='30' height='30' alt='contacter' title='contacter'></a></td>";
										echo "	<td align='center'>".$nom_perso_capture." [<a href='evenement.php?infoid=".$id_perso_capture."'>".$id_perso_capture."</a>] <a href='nouveau_message.php?pseudo=".$nom_perso_capture."' target='_blank'><img src='../images/messagerie.png' width='30' height='30' alt='contacter' title='contacter'></a></td>";
										echo "	<td align='center'>".$titre_capture."</td>";
										echo "	<td align='center'>".$message."</td>";
										echo "	<td align='center'>";
										echo "		<a class='btn btn-success' href=\"anim_questions.php?id=".$id_capture."&action=valider\">Capture RP</a>";
										echo "		<a class='btn btn-success' href=\"anim_questions.php?id=".$id_capture."&action=valider_encerclement\">Capture Encerclement</a>";
										echo "		<a class='btn btn-danger' href=\"anim_questions.php?id=".$id_capture."&action=refuser\">Refuser</a>";
										echo "	</td>";
										echo "</tr>";
									}
									?>
								</tbody>
							</table>
						</div>			
					</div>
				</div>
			<?php
			}
			?>
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
	