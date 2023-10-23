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

// recupération config jeu
$dispo = config_dispo_jeu($mysqli);
$admin = admin_perso($mysqli, $_SESSION["id_perso"]);

if($dispo == '1' || $admin){
	
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
						
						// récupération infos capture
						$t = $res->fetch_assoc();
						
						$id_perso 			= $t['id_perso'];
						$id_perso_capture	= $t['id_perso_capture'];
						$date_capture		= $t['date_capture'];
						$titre_capture		= $t['titre'];
						$message			= $t['message'];
						$statut_capture		= $t['statut'];
						
						$sql_p = "SELECT nom_perso, clan, perso_as_grade.id_grade, nom_grade FROM perso, perso_as_grade, grades
								WHERE perso_as_grade.id_perso = perso.id_perso
								AND perso_as_grade.id_grade = grades.id_grade 
								AND perso.id_perso='$id_perso'";
						$res_p = $mysqli->query($sql_p);
						$t_p = $res_p->fetch_assoc();
						
						$nom_perso 			= $t_p['nom_perso'];
						$camp_perso			= $t_p['clan'];
						$id_grade_perso		= $t_p['id_grade'];
						$nom_grade_perso	= $t_p['nom_grade'];
						
						if ($camp_perso == '1') {
							$couleur_clan_perso = 'blue';
						}
						else if ($camp_perso == '2') {
							$couleur_clan_perso = 'red';
						}
						else if ($camp_perso == '3') {
							$couleur_clan_perso = 'green';
						}
						
						$sql_p = "SELECT nom_perso, type_perso, pi_perso, pc_perso, x_perso, y_perso, clan, perso_as_grade.id_grade, nom_grade FROM perso, perso_as_grade, grades
								WHERE perso_as_grade.id_perso = perso.id_perso
								AND perso_as_grade.id_grade = grades.id_grade 
								AND perso.id_perso='$id_perso_capture'";
						$res_p = $mysqli->query($sql_p);
						$t_p = $res_p->fetch_assoc();
						
						$nom_perso_capture 			= $t_p['nom_perso'];
						$type_perso_capture			= $t_p['type_perso'];
						$pi_perso_capture			= $t_p['pi_perso'];
						$pc_perso_capture			= $t_p['pc_perso'];
						$x_perso_capture			= $t_p['x_perso'];
						$y_perso_capture			= $t_p['y_perso'];
						$camp_perso_capture			= $t_p['clan'];
						$id_grade_perso_capture		= $t_p['id_grade'];
						$nom_grade_perso_capture	= $t_p['nom_grade'];
						
						if ($camp_perso_capture == '1') {
							$couleur_clan_perso_capture = 'blue';
						}
						else if ($camp_perso_capture == '2') {
							$couleur_clan_perso_capture = 'red';
						}
						else if ($camp_perso_capture == '3') {
							$couleur_clan_perso_capture = 'green';
						}
						
						if (isset($_GET['action']) && $_GET['action'] == "refuser") {
							
							// MAJ capture
							$sql = "UPDATE anim_capture SET statut=3 WHERE id='$id_capture_rp'";
							$mysqli->query($sql);
							
							// Récupération nom perso anim pour MP
							$sql = "SELECT nom_perso FROM perso WHERE id_perso='$id'";
							$res = $mysqli->query($sql);
							$t = $res->fetch_assoc();
						
							$expediteur = $t['nom_perso'];
							
							//-------------------------
							// Envoi MP au perso qui a effectué la remontée
							$objet = "[Animation] Refus demande Capture";
							$message = "Bonjour, l'animation a refusée votre demande de capture.";
				
							$lock = "LOCK TABLE message WRITE";
							$mysqli->query($lock);
							
							// creation du message
							$sql = "INSERT INTO message (id_expediteur, expediteur_message, date_message, contenu_message, objet_message) 
									VALUES ('" . $id . "', '" . $expediteur . "', NOW(), '" . addslashes($message). "', '" . $objet. "')";
							$mysqli->query($sql);
							$id_message = $mysqli->insert_id;
							
							$unlock = "UNLOCK TABLES";
							$mysqli->query($unlock);
							
							// assignation du message au perso
							$sql = "INSERT INTO message_perso VALUES ('$id_message', '$id_perso', '1', '0', '0', '0')";
							$mysqli->query($sql);
				
							$texte = addslashes("La capture du perso $nom_perso_capture [$id_perso_capture] par le perso matricule $id_perso a été refusé");
				
							// log_action_animation
							$sql = "INSERT INTO log_action_animation(date_acces, id_perso, page, action, texte) VALUES (NOW(), '$id', 'anim_capture_rp.php', 'refus capture', '$texte')";
							$mysqli->query($sql);
							
						}
						else if (isset($_GET['action']) && $_GET['action'] == "valider") {
							
							// MAJ capture
							$sql = "UPDATE anim_capture SET statut=1 WHERE id='$id_capture_rp'";
							$mysqli->query($sql);
							
							// maj evenements
							$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) 
									VALUES ($id_perso,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>','<b>a négocié la capture</b>','$id_perso_capture','<font color=$couleur_clan_perso_capture><b>$nom_perso_capture</b></font>','',NOW(),'0')";
							$mysqli->query($sql);
							
							// maj cv
							$sql = "INSERT INTO `cv` (IDActeur_cv, nomActeur_cv, gradeActeur_cv, IDCible_cv, nomCible_cv, gradeCible_cv, date_cv, special) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>', '$nom_grade_perso', '$id_perso_capture','<font color=$couleur_clan_perso_capture>$nom_perso_capture</font>', '$nom_grade_perso_capture', NOW(), 10)";
							$mysqli->query($sql);
							
							// MAJ perso capturé
							$sql = "UPDATE perso SET pv_perso=0, nb_mort=nb_mort+1 WHERE id_perso='$id_perso_capture'";
							$mysqli->query($sql);
							
							if (in_bat($mysqli, $id_perso_capture)) {
								// on le supprime du batiment
								$sql = "DELETE FROM perso_in_batiment WHERE id_perso='$id_perso_capture'";
								$mysqli->query($sql);
							}
							else {
								// on l'efface de la carte
								$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_perso_capture' AND y_carte='$y_perso_capture'";
								$mysqli->query($sql);
							}
							
							// maj stats du perso
							$sql = "UPDATE perso SET nb_kill=nb_kill+1 WHERE id_perso=$id_perso";
							$mysqli->query($sql);
							
							// maj stats camp
							if($camp_perso_capture != $camp_perso){
								$sql = "UPDATE stats_camp_kill SET nb_kill=nb_kill+1 WHERE id_camp=$camp_perso";
								$mysqli->query($sql);
							}
							
							// maj dernier tombé
							$sql = "INSERT INTO dernier_tombe (date_capture, id_perso_capture, camp_perso_capture, id_perso_captureur, camp_perso_captureur) VALUES (NOW(), '$id_perso_capture', $camp_perso_capture, $id_perso, $camp_perso)";
							$mysqli->query($sql);
							
							// Gain PC 
							$sql = "UPDATE perso SET pc_perso=pc_perso+4 WHERE id_perso='$id_perso'";
							$mysqli->query($sql);
							
							// Récupération nom perso anim pour MP
							$sql = "SELECT nom_perso FROM perso WHERE id_perso='$id'";
							$res = $mysqli->query($sql);
							$t = $res->fetch_assoc();
						
							$expediteur = $t['nom_perso'];
							
							//-------------------------
							// Envoi MP au capturé
							$objet = "[Animation] Capture de votre perso par RP";
							$message = "Bonjour, l'animation a validé la capture de votre personnage par RP.";
				
							$lock = "LOCK TABLE message WRITE";
							$mysqli->query($lock);
							
							// creation du message
							$sql = "INSERT INTO message (id_expediteur, expediteur_message, date_message, contenu_message, objet_message) 
									VALUES ('" . $id . "', '" . $expediteur . "', NOW(), '" . addslashes($message). "', '" . $objet. "')";
							$mysqli->query($sql);
							$id_message = $mysqli->insert_id;
							
							$unlock = "UNLOCK TABLES";
							$mysqli->query($unlock);
							
							// assignation du message au perso
							$sql = "INSERT INTO message_perso VALUES ('$id_message', '$id_perso_capture', '1', '0', '0', '0')";
							$mysqli->query($sql);
							
							//-------------------------
							// Envoi MP au perso qui a effectué la capture
							$objet = "[Animation] Capture par RP";
							$message = "Bonjour, l'animation a validé votre capture RP.";
				
							$lock = "LOCK TABLE message WRITE";
							$mysqli->query($lock);
							
							// creation du message
							$sql = "INSERT INTO message (id_expediteur, expediteur_message, date_message, contenu_message, objet_message) 
									VALUES ('" . $id . "', '" . $expediteur . "', NOW(), '" . addslashes($message). "', '" . $objet. "')";
							$mysqli->query($sql);
							$id_message = $mysqli->insert_id;
							
							$unlock = "UNLOCK TABLES";
							$mysqli->query($unlock);
							
							// assignation du message au perso
							$sql = "INSERT INTO message_perso VALUES ('$id_message', '$id_perso', '1', '0', '0', '0')";
							$mysqli->query($sql);
							
							$texte = addslashes("La capture RP du perso $nom_perso_capture [$id_perso_capture] par le perso matricule $id_perso a été validé");
				
							// log_action_animation
							$sql = "INSERT INTO log_action_animation(date_acces, id_perso, page, action, texte) VALUES (NOW(), '$id', 'anim_capture_rp.php', 'validation capture RP', '$texte')";
							$mysqli->query($sql);
							
						}
						else if (isset($_GET['action']) && $_GET['action'] == "valider_encerclement") {
							
							// MAJ capture
							$sql = "UPDATE anim_capture SET statut=2 WHERE id='$id_capture_rp'";
							$mysqli->query($sql);
							
							// Chef
							if ($type_perso_capture == 1) {
								perte_etendard($mysqli, $id_perso_capture, $x_perso_capture, $y_perso_capture);
								// Quand un chef meurt, il perd 5% de ses XPi et de ses PC
								// Calcul PI
								$pi_perdu 		= floor(($pi_perso_capture * 5) / 100);
								
								// Calcul PC
								$pc_perdu		= floor(($pc_perso_capture * 5) / 100);
								$pc_perso_fin	= $pc_perso_capture - $pc_perdu;
							}
							else {
								$pi_perdu 		= floor(($pi_perso_capture * 40) / 100);
								$pc_perso_fin = $pc_perso_capture;
							}
		
							// MAJ perte xp/po/stat perso capturé
							$sql = "UPDATE perso SET pv_perso=0, xp_perso=xp_perso-$pi_perdu, pi_perso=pi_perso-$pi_perdu, pc_perso=$pc_perso_fin, nb_mort=nb_mort+1 WHERE id_perso='$id_perso_capture'";
							$mysqli->query($sql);
							
							if (in_bat($mysqli, $id_perso_capture)) {
								// on le supprime du batiment
								$sql = "DELETE FROM perso_in_batiment WHERE id_perso='$id_perso_capture'";
								$mysqli->query($sql);
							}
							else {
								// on l'efface de la carte
								$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_perso_capture' AND y_carte='$y_perso_capture'";
								$mysqli->query($sql);
							}
							
							// mise a jour des evenements
							$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ('$id_perso_capture','<font color=$couleur_clan_perso_capture><b>$nom_perso_capture</b></font>','a été capturé de force par encerclement',NULL,'','',NOW(),'0')";
							$mysqli->query($sql);
							
							// maj cv
							$sql = "INSERT INTO `cv` (IDActeur_cv, nomActeur_cv, gradeActeur_cv, IDCible_cv, nomCible_cv, gradeCible_cv, date_cv, special) VALUES ($id_perso_capture,'<font color=$couleur_clan_perso_capture>$nom_perso_capture</font>', '$nom_grade_perso_capture', NULL,NULL,NULL, NOW(), 11)";
							$mysqli->query($sql);
							
							// maj stats camp
							if($camp_perso_capture != $camp_perso){
								$sql = "UPDATE stats_camp_kill SET nb_kill=nb_kill+1 WHERE id_camp=$camp_perso";
								$mysqli->query($sql);
							}
							
							// Récupération nom perso anim pour MP
							$sql = "SELECT nom_perso FROM perso WHERE id_perso='$id'";
							$res = $mysqli->query($sql);
							$t = $res->fetch_assoc();
						
							$expediteur = $t['nom_perso'];
							
							//-------------------------------
							// Envoi MP au capturé
							$objet = "[Animation] Capture de votre perso par encerclement";
							$message = "Bonjour, l'animation a validé la capture forcée de votre personnage par encerclement de l'ennemi.";
				
							$lock = "LOCK TABLE message WRITE";
							$mysqli->query($lock);
							
							// creation du message
							$sql = "INSERT INTO message (id_expediteur, expediteur_message, date_message, contenu_message, objet_message) 
									VALUES ('" . $id . "', '" . $expediteur . "', NOW(), '" . addslashes($message). "', '" . $objet. "')";
							$mysqli->query($sql);
							$id_message = $mysqli->insert_id;
							
							$unlock = "UNLOCK TABLES";
							$mysqli->query($unlock);
							
							// assignation du message au perso
							$sql = "INSERT INTO message_perso VALUES ('$id_message', '$id_perso_capture', '1', '0', '0', '0')";
							$mysqli->query($sql);

							$texte = addslashes("La capture par encerclement du perso $nom_perso_capture [$id_perso_capture] par le perso matricule $id_perso a été validé");
				
							// log_action_animation
							$sql = "INSERT INTO log_action_animation(date_acces, id_perso, page, action, texte) VALUES (NOW(), '$id', 'anim_capture_rp.php', 'validation capture par encerclement', '$texte')";
							$mysqli->query($sql);
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
				$sql = "SELECT anim_capture.id, anim_capture.id_perso, anim_capture.id_perso_capture, date_capture, titre, message, statut, extension_img1, extension_img2 FROM anim_capture 
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
									<th style='text-align:center'>Description</th>
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
									$extension_deb		= $t['extension_img1'];
									$extension_fin		= $t['extension_img2'];
									
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
				$sql = "SELECT anim_capture.id, anim_capture.id_perso, anim_capture.id_perso_capture, date_capture, titre, message, statut, type_capture, extension_img1, extension_img2 FROM anim_capture 
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
									<th style='text-align:center'>Description</th>
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
									$type_capture		= $t['type_capture'];
									$extension_deb		= $t['extension_img1'];
									$extension_fin		= $t['extension_img2'];
									
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
									
									$lien_image_debut 	= "upload/capture_debut_".$id_capture.".".$extension_deb;
									$lien_image_fin 	= "upload/capture_fin_".$id_capture.".".$extension_fin;
									
									echo "<tr>";
									echo "	<td align='center'>".$date_capture."</td>";
									echo "	<td align='center'>".$nom_perso." [<a href='evenement.php?infoid=".$id_perso."'>".$id_perso."</a>] <a href='nouveau_message.php?pseudo=".$nom_perso."' target='_blank'><img src='../images/messagerie.png' width='30' height='30' alt='contacter' title='contacter'></a></td>";
									echo "	<td align='center'>".$nom_perso_capture." [<a href='evenement.php?infoid=".$id_perso_capture."'>".$id_perso_capture."</a>] <a href='nouveau_message.php?pseudo=".$nom_perso_capture."' target='_blank'><img src='../images/messagerie.png' width='30' height='30' alt='contacter' title='contacter'></a></td>";
									echo "	<td align='center'>".$titre_capture."</td>";
									echo "	<td align='center'>".$message."</td>";
									echo "	<td align='center'><a href='".$lien_image_debut."' target='_blank'>Image 1</a><br/><a href='".$lien_image_fin."' target='_blank'>Image 2</a></td>";
									echo "	<td align='center'>";
									if ($type_capture == 2) {
										echo "		<a class='btn btn-success' href=\"anim_capture_rp.php?id=".$id_capture."&action=valider\">Valider Capture RP</a>";
									}
									if ($type_capture == 1) {
										echo "		<a class='btn btn-success' href=\"anim_capture_rp.php?id=".$id_capture."&action=valider_encerclement\">Valider Capture Encerclement</a>";
									}
									echo "		<a class='btn btn-danger' href=\"anim_capture_rp.php?id=".$id_capture."&action=refuser\">Refuser</a>";
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
	
