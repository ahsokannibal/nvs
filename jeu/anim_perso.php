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
			
			function mail_changement_nom($nouveau_nom, $email_joueur){

				// Headers mail
				$headers ='From: "Nord VS Sud"<nordvssud@no-reply.fr>'."\n";
				$headers .='Reply-To: nordvssud@no-reply.fr'."\n";
				$headers .='Content-Type: text/plain; charset="utf-8"'."\n";
				$headers .='Content-Transfer-Encoding: 8bit';
				
				// Titre du mail
				$titre = 'Changement du nom de votre personnage principal';
				
				// Contenu du mail
				$message = "Votre nouveau nom est ".$nouveau_nom.". Veuillez utiliser votre nouveau nom pour vous connecter.";
				
				// Envoie du mail
				mail($email_joueur, $titre, $message, $headers);
			}
			
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
			
			if (isset($_GET['id_perso']) && isset($_GET['type']) && isset($_GET['valid'])) {
				
				$id_perso_maj 		= $_GET['id_perso'];
				$type_demande_maj 	= $_GET['type'];
				$valid_maj			= $_GET['valid'];
				
				$verif_id 	= preg_match("#^[0-9]*[0-9]$#i","$id_perso_maj");
				$verif_type = preg_match("#^[0-9]*[0-9]$#i","$type_demande_maj");
				
				if ($verif_id && $verif_type) {
				
					if ($_GET['valid'] == 'ok') {
						// Validation de la demande
						
						if ($type_demande_maj == 1) {
							// Demande de changement de nom
							
							// Récupération du nouveau nom
							$sql = "SELECT info_demande FROM perso_demande_anim WHERE id_perso='$id_perso_maj' AND type_demande='1'";
							$res = $mysqli->query($sql);
							$t = $res->fetch_assoc();
							
							$nouveau_nom_perso = addslashes($t['info_demande']);
							
							// Ce nom est-il déjà pris ?
							// Est ce que le nom de cette compagnie est déjà pris ?
							$sql = "SELECT * FROM perso WHERE nom_perso='$nouveau_nom_perso'";
							$res = $mysqli->query($sql);
							$verif = $res->num_rows;
							
							if ($verif == 0) {
								
								// Récupération ancien nom 
								$sql = "SELECT nom_perso, clan FROM perso WHERE id_perso='$id_perso_maj'";
								$res = $mysqli->query($sql);
								$t = $res->fetch_assoc();
								
								$ancien_nom = $t['nom_perso'];
								$camp		= $t['clan'];
								
								if ($camp == 1) {
									$couleur_clan_p = 'blue';
								}
								else if ($camp == 2) {
									$couleur_clan_p = 'red';
								}
								else if ($camp == 3) {
									$couleur_clan_p = 'green';
								}
								
								// Récupération mail joueur du perso 
								$sql = "SELECT email_joueur FROM joueur, perso WHERE perso.idJoueur_perso = joueur.id_joueur AND perso.id_perso='$id_perso_maj'";
								$res = $mysqli->query($sql);
								$t = $res->fetch_assoc();
								
								$email_joueur = $t["email_joueur"];
								
								$sql = "UPDATE perso SET nom_perso='$nouveau_nom_perso' WHERE id_perso='$id_perso_maj'";
								$mysqli->query($sql);
								
								// evenement changement de nom 
								$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso_maj,'<font color=$couleur_clan_p><b>$ancien_nom</b></font>','a été renommé en <font color=$couleur_clan_p><b>$nouveau_nom_perso</b></font>',NULL,'','',NOW(),'0')";
								$mysqli->query($sql);
								
								// Suppression de la demande 
								$sql = "DELETE FROM perso_demande_anim WHERE id_perso='$id_perso_maj' AND type_demande='$type_demande_maj'";
								$mysqli->query($sql);
								
								// Envoi d'un Mail
								mail_changement_nom($nouveau_nom_perso, $email_joueur);
								
								// -- FORUM
								$sql = "UPDATE ".$table_prefix."users SET username='$nouveau_nom_perso' WHERE user_email='$email_joueur'";
								$mysqli->query($sql);
								
							}
							else {
								echo "<center><font color='red'><b>Impossible de valider ce changement de nom car le nom est déjà pris</b></font></center>";
							}
						}
						else if ($type_demande_maj == 3) {
							// Demande de changement de nom de bataillon
							
							// Récupération du nouveau nom
							$sql = "SELECT info_demande FROM perso_demande_anim WHERE id_perso='$id_perso_maj' AND type_demande='3'";
							$res = $mysqli->query($sql);
							$t = $res->fetch_assoc();
							
							$nouveau_nom_bataillon = addslashes($t['info_demande']);
							
							$sql = "UPDATE perso SET bataillon='$nouveau_nom_bataillon' WHERE idJoueur_perso='$id_perso_maj'";
							$mysqli->query($sql);
							
							// Suppression de la demande 
							$sql = "DELETE FROM perso_demande_anim WHERE id_perso='$id_perso_maj' AND type_demande='$type_demande_maj'";
							$mysqli->query($sql);
						}
						else if ($type_demande_maj == 4) {
							// Demande de changement de camp
							
							// Récupération du camp cible
							$sql = "SELECT info_demande FROM perso_demande_anim WHERE id_perso='$id_perso_maj' AND type_demande='4'";
							$res = $mysqli->query($sql);
							$t = $res->fetch_assoc();
							
							$camp_cible = $t['info_demande'];
							
							// Récupération des grouillots du joueur 
							$sql = "SELECT id_perso FROM perso WHERE idJoueur_perso='$id_perso_maj' AND chef='0'";
							$res = $mysqli->query($sql);
							
							while ($t = $res->fetch_assoc()) {
								
								$id_grouillot = $t['id_perso'];
								
								// Ok - renvoi du perso						
								$sql = "DELETE FROM perso WHERE id_perso='$id_grouillot'";
								$mysqli->query($sql);
								
								$sql = "DELETE FROM perso_as_arme WHERE id_perso='$id_grouillot'";
								$mysqli->query($sql);
								
								$sql = "DELETE FROM perso_as_armure WHERE id_perso='$id_grouillot'";
								$mysqli->query($sql);
								
								$sql = "DELETE FROM perso_as_competence WHERE id_perso='$id_grouillot'";
								$mysqli->query($sql);
								
								$sql = "DELETE FROM perso_as_contact WHERE id_perso='$id_grouillot'";
								$mysqli->query($sql);
								
								$sql = "DELETE FROM perso_as_dossiers WHERE id_perso='$id_grouillot'";
								$mysqli->query($sql);
								
								$sql = "DELETE FROM perso_as_entrainement WHERE id_perso='$id_grouillot'";
								$mysqli->query($sql);
								
								$sql = "DELETE FROM perso_as_grade WHERE id_perso='$id_grouillot'";
								$mysqli->query($sql);
								
								$sql = "DELETE FROM perso_as_killpnj WHERE id_perso='$id_grouillot'";
								$mysqli->query($sql);
								
								$sql = "DELETE FROM perso_as_objet WHERE id_perso='$id_grouillot'";
								$mysqli->query($sql);
								
								$sql = "DELETE FROM perso_in_batiment WHERE id_perso='$id_grouillot'";
								$mysqli->query($sql);
								
								$sql = "DELETE FROM perso_in_compagnie WHERE id_perso='$id_grouillot'";
								$mysqli->query($sql);
								
								if (in_bat($mysqli, $id_perso)) {		
									$sql = "DELETE FROM perso_in_batiment WHERE id_perso='$id_grouillot'";
								}
								else if (in_train($mysqli, $id_perso)) {
									$sql = "DELETE FROM perso_in_train WHERE id_perso='$id_grouillot'";
								}
								else {
									$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE idPerso_carte='$id_grouillot'";
								}
								$mysqli->query($sql);								
							}
							
							// Récupération batiment de nouveau départ
							
							// MAJ du chef 
							$sql = "";
						}
					}
					else {
						// Suppression de la demande 
						$sql = "DELETE FROM perso_demande_anim WHERE id_perso='$id_perso_maj' AND type_demande='$type_demande_maj'";
						$mysqli->query($sql);
						
						// TODO - envoi MP
						
					}
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
						<h2>Animation - Gestion des demandes des persos</h2>
					</div>
				</div>
			</div>
			
			<p align="center"><a class="btn btn-primary" href="animation.php">Retour page principale d'animation</a></p>
			
			<div class="row">
				<div class="col-12">
					<form method='POST' action='anim_event_perso.php'>
						<div class="form-row">
							<div class="form-group col-md-12">
								<label for="formSelectPerso">Voir les événements détaillées du perso : </label>
							</div>
						</div>
						<div class="form-row">
							<div class="form-group col-md-8">
								<select class="form-control" name='liste_perso_event' id="formSelectPerso">
								<?php
								// récuopération de tous les persos de son camp 
								$sql = "SELECT id_perso, nom_perso FROM perso WHERE clan='$camp' ORDER BY id_perso ASC";
								$res = $mysqli->query($sql);
								
								while ($t = $res->fetch_assoc()) {
									
									$id_perso_list 	= $t["id_perso"];
									$nom_perso_list	= $t["nom_perso"];
									
									echo "<option value='".$id_perso_list."'>".$nom_perso_list." [".$id_perso_list."]</option>";
									
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
			
			<br /><br />
			
			<div class="row">
				<div class="col-12">
					<div class="table-responsive">
						<table class="table">
							<thead>
								<tr>
									<th style='text-align:center'>Perso</th>
									<th style='text-align:center'>Type de demande</th>
									<th style='text-align:center'>Infos Demande</th>
									<th style='text-align:center'>Action</th>
								</tr>
							</thead>
							<tbody>
							<?php
							// Récupération des demandes sur la gestion des persos 
							$sql = "SELECT * FROM perso_demande_anim, perso
									WHERE perso_demande_anim.id_perso = perso.id_perso
									AND perso.clan = '$camp'
									ORDER BY perso_demande_anim.id_perso ASC";
							$res = $mysqli->query($sql);
							
							while ($t = $res->fetch_assoc()) {
									
								$id_perso 		= $t['id_perso'];
								$type_demande	= $t['type_demande'];
								$info_demande	= $t['info_demande'];
								
								if ($type_demande == 1) {
									$nom_demande = "Changement de nom";
									$info_demande = "Nouveau nom : ".$info_demande;
								}
								else if ($type_demande == 2) {
									$nom_demande = "Demande de suppression";
								}
								else if ($type_demande == 3) {
									$nom_demande = "Demande de changement de nom de bataillon";
								}
								else if ($type_demande == 4) {
									$nom_demande = "Demande de changement de camp";
								}
								else {
									$nom_demande = "Inconnu";
								}
								
								if ($type_demande == 3 || $type_demande == 4) {
									// Récupération infos perso
									$sql_c = "SELECT id_perso, nom_perso FROM perso WHERE idJoueur_perso='$id_perso' AND chef='1'";
								}
								else {
									// Récupération infos perso
									$sql_c = "SELECT id_perso, nom_perso FROM perso WHERE id_perso='$id_perso'";
								}
								
								$res_c = $mysqli->query($sql_c);
								$t_c = $res_c->fetch_assoc();
								
								$id_perso_aff	= $t_c['id_perso'];
								$nom_perso 		= $t_c['nom_perso'];
								
								echo "<tr>";
								echo "	<td align='center'>".$nom_perso." [<a href='evenement.php?infoid=".$id_perso_aff."'>".$id_perso_aff."</a>]</td>";
								echo "	<td align='center'>".$nom_demande."</td>";
								echo "	<td align='center'>".$info_demande."</td>";
								echo "	<td align='center'>";
								echo "		<a class='btn btn-success' href=\"anim_perso.php?id_perso=".$id_perso."&type=".$type_demande."&valid=ok\">Accepter</a>";
								echo "		<a class='btn btn-danger' href=\"anim_perso.php?id_perso=".$id_perso."&type=".$type_demande."&valid=refus\">Refuser</a>";
								echo "	</td>";
								echo "</tr>";
							}
							?>
							</tbody>
						</table>
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
	