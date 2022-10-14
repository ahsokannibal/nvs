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
			
			if (isset($_POST['liste_perso_punition'])) {
						
				$id_perso_puni = $_POST['liste_perso_punition'];
				
			}
			
			if (isset($_GET['id_perso']) && trim($_GET['id_perso']) != "") {
				
				$id_perso_puni = $id_perso_punition = $_GET['id_perso'];
				
				$verif_id_perso = preg_match("#^[0-9]*[0-9]$#i","$id_perso_punition");
				
				if ($verif_id_perso) {
					
					// On verifie si le perso puni est du même camp que l'anim
					$sql = "SELECT nom_perso, x_perso, y_perso, clan FROM perso WHERE id_perso='$id_perso_punition'";
					$res = $mysqli->query($sql);
					$t = $res->fetch_assoc();
					
					$camp_perso_punition 	= $t['clan'];
					$nom_perso				= $t['nom_perso'];
					$x_perso_origin 		= $t['x_perso'];
					$y_perso_origin 		= $t['y_perso'];
					
					if ($camp_perso_punition == $camp) {
						
						if ($camp_perso_punition == 1) {
							$couleur_clan_perso = 'blue';
						}
						else if ($camp_perso_punition == 2) {
							$couleur_clan_perso = 'red';
						}
						else if ($camp_perso_punition == 3) {
							$couleur_clan_perso = 'green';
						}
					
						if (isset($_GET['bagne']) && trim($_GET['bagne']) == "ok" && isset($_GET['duree']) && trim($_GET['bagne']) != "") {
							
							$duree_bagne = $_GET['duree'];
							$verif_duree = preg_match("#^[0-9]*[0-9]$#i","$duree_bagne");
							
							if ($verif_duree) {
							
								// Vérification si présence ou non d'un pénitencier
								$sql_peni = "SELECT id_instanceBat, x_instance, y_instance FROM instance_batiment WHERE id_batiment=10 AND camp_instance='$camp_perso_punition'";
								$res_peni = $mysqli->query($sql_peni);
								$verif_penitencier = $res_peni->num_rows;
								
								if ($verif_penitencier) {
								
									$t = $res_peni->fetch_assoc();
								
									$id_penitencier	= $t['id_instanceBat'];
									$x_penitencier	= $t['x_instance'];
									$y_penitencier	= $t['y_instance'];
								
									// perso déjà dans pénitencier ?
									$sql = "SELECT * FROM perso_in_batiment WHERE id_perso='$id_perso_punition' AND id_instanceBat='$id_penitencier'";
									$res = $mysqli->query($sql);
									$verif_peni = $res->num_rows;
									
									if ($verif_peni == 0) {
										
										if (in_bat($mysqli, $id_perso_punition)) {
											$sql = "DELETE FROM perso_in_batiment WHERE id_perso='$id_perso_punition'";
										}
										else {
											$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_perso_origin' AND y_carte='$y_perso_origin'";
										}
										$mysqli->query($sql);
										
										// MAJ coordonnées perso
										$sql = "UPDATE perso SET x_perso='$x_penitencier', y_perso='$y_penitencier' WHERE id_perso='$id_perso_punition'";
										$mysqli->query($sql);
										
										// Ajout du perso dans le batiment
										$sql = "INSERT INTO perso_in_batiment VALUES ('$id_perso_punition','$id_penitencier')";
										$mysqli->query($sql);
										
										// Ajout durée bagne
										$sql = "INSERT INTO perso_bagne (id_perso, date_debut, duree) VALUES ('$id_perso_punition', NOW(), '$duree_bagne')";
										$mysqli->query($sql);
										
										// evenements perso
										$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement) VALUES ($id_perso_punition,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>','<b>a été envoyé au Pénitencier </b>','$id_penitencier','Pénitencier','',NOW())";
										$mysqli->query($sql);
										
										$mess = "Envoi de ".$nom_perso." [".$id_perso_punition."] au Pénitencier";
										
										$texte = addslashes($mess);
									
										// log_action_animation
										$sql = "INSERT INTO log_action_animation(date_acces, id_perso, page, action, texte) VALUES (NOW(), '$id', 'anim_punition.php', 'Envoi perso dans pénitencier', '$texte')";
										$mysqli->query($sql);
									}
									else {
										$mess_err .= "Le perso est déjà dans un pénitencier";
									}
								}
								else {
									$mess_err .= "Impossible d'envoyer le perso au pénitencier, il n'existe pas de pénitencier pour ce camp";
								}
							}
							else {
								// parametres incorrectes / modifiés
								$text_triche = "Tentative modification parametre Animation punition duree bagne";
								
								$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id', '$text_triche')";
								$mysqli->query($sql);
								
								header("Location:jouer.php");
							}
						}
						
						if (isset($_GET['amende']) && trim($_GET['amende']) != "") {
							
							$montant_amende = $_GET['amende'];
							
							if ($montant_amende == "all") {
								
								// On enlève toute sa thune au perso
								$sql = "UPDATE perso SET or_perso=0 WHERE id_perso='$id_perso_punition'";
								$mysqli->query($sql);
								
								// evenements perso
								$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement) VALUES ($id_perso_punition,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>','<b>a reçu une amende</b>',NULL,NULL,' : Perte de toutes ses économies !',NOW())";
								$mysqli->query($sql);
								
								$mess .= "Le perso a perdu <b>toute</b> sa thune !";
								
								$texte = addslashes($mess);
									
								// log_action_animation
								$sql = "INSERT INTO log_action_animation(date_acces, id_perso, page, action, texte) VALUES (NOW(), '$id', 'anim_punition.php', 'Punition thune all', '$texte')";
								$mysqli->query($sql);
							}
							else {
								$verif_montant = preg_match("#^[0-9]*[0-9]$#i","$montant_amende");
							
								if ($verif_montant) {
									
									// Récupération thunes du perso
									$sql = "SELECT or_perso FROM perso WHERE id_perso='$id_perso_punition'";
									$res = $mysqli->query($sql);
									$t = $res->fetch_assoc();
									
									$thune_perso_puni = $t['or_perso'];
									
									if ($thune_perso_puni < $montant_amende) {
										// On enlève toute sa thune au perso
										$sql = "UPDATE perso SET or_perso=0 WHERE id_perso='$id_perso_punition'";
										$mysqli->query($sql);
										
										// evenements perso
										$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement) VALUES ($id_perso_punition,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>','<b>a reçu une amende</b>',NULL,NULL,' : Perte de toutes ses économies !',NOW())";
										$mysqli->query($sql);
										
										$mess .= "Le perso a perdu <b>toute</b> sa thune !";
									}
									else {
										// On enleve le montant
										$sql = "UPDATE perso SET or_perso=or_perso - $montant_amende WHERE id_perso='$id_perso_punition'";
										$mysqli->query($sql);
										
										// evenements perso
										$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement) VALUES ($id_perso_punition,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>','<b>a reçu une amende</b>',NULL,NULL,' : Perte de $montant_amende thunes !',NOW())";
										$mysqli->query($sql);
										
										$mess .= "Le perso a payé une amende de <b>".$montant_amende."</b> thunes";
									}
									
									$texte = addslashes($mess);
									
									// log_action_animation
									$sql = "INSERT INTO log_action_animation(date_acces, id_perso, page, action, texte) VALUES (NOW(), '$id', 'anim_punition.php', 'Punition thune', '$texte')";
									$mysqli->query($sql);
								}
								else {
									// parametres incorrectes / modifiés
									$text_triche = "Tentative modification parametre Animation punition Thunes";
									
									$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id', '$text_triche')";
									$mysqli->query($sql);
									
									header("Location:jouer.php");
								}
							}
						}
						
						if (isset($_GET['pc']) && trim($_GET['pc']) != "") {
							
							$montant_pc = $_GET['pc'];
							
							if ($montant_pc == "all") {
								
								// On enlève tout ses PC
								$sql = "UPDATE perso SET pc_perso=0 WHERE id_perso='$id_perso_punition'";
								$mysqli->query($sql);
								
								// evenements perso
								$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement) VALUES ($id_perso_punition,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>','<b>a reçu une amende</b>',NULL,NULL,' : Perte de tout ses PC !',NOW())";
								$mysqli->query($sql);
								
								$mess .= "Le perso a perdu <b>tout</b> ses Points de Commandement !";
								
								$texte = addslashes($mess);
									
								// log_action_animation
								$sql = "INSERT INTO log_action_animation(date_acces, id_perso, page, action, texte) VALUES (NOW(), '$id', 'anim_punition.php', 'Punition PC All', '$texte')";
								$mysqli->query($sql);
							}
							else {
								$verif_pc = preg_match("#^[0-9]*[0-9]$#i","$montant_pc");
								
								if ($verif_pc) {
									
									// Récupération pc du perso
									$sql = "SELECT pc_perso FROM perso WHERE id_perso='$id_perso_punition'";
									$res = $mysqli->query($sql);
									$t = $res->fetch_assoc();
									
									$pc_perso_puni = $t['pc_perso'];
									
									if ($pc_perso_puni < $montant_pc) {
										
										// On enlève tout ses PC au perso
										$sql = "UPDATE perso SET pc_perso=0 WHERE id_perso='$id_perso_punition'";
										$mysqli->query($sql);
										
										// evenements perso
										$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement) VALUES ($id_perso_punition,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>','<b>a reçu une amende</b>',NULL,NULL,' : Perte de tout ses PC !',NOW())";
										$mysqli->query($sql);
										
										$mess .= "Le perso a perdu <b>tout</b> ses Points de Commandement !";
									}
									else {
										// On enleve le montant
										$sql = "UPDATE perso SET pc_perso=pc_perso - $montant_pc WHERE id_perso='$id_perso_punition'";
										$mysqli->query($sql);
										
										// evenements perso
										$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement) VALUES ($id_perso_punition,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>','<b>a reçu une amende</b>',NULL,NULL,' : Perte de $montant_pc PC !',NOW())";
										$mysqli->query($sql);
										
										$mess .= "Le perso a perdu <b>".$montant_pc."</b> Points de Commandement";
									}
									
									$texte = addslashes($mess);
									
									// log_action_animation
									$sql = "INSERT INTO log_action_animation(date_acces, id_perso, page, action, texte) VALUES (NOW(), '$id', 'anim_punition.php', 'Punition PC', '$texte')";
									$mysqli->query($sql);
								}
								else {
									// parametres incorrectes / modifiés
									$text_triche = "Tentative modification parametre Animation punition PC";
									
									$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id', '$text_triche')";
									$mysqli->query($sql);
									
									header("Location:jouer.php");
								}
							}
						}
						
						if (isset($_GET['xp']) && trim($_GET['xp']) != "") {
							
							$montant_xp = $_GET['xp'];
							
							if ($montant_xp == "all") {
								
								// On enlève tout ses XP
								$sql = "UPDATE perso SET xp_perso=0 WHERE id_perso='$id_perso_punition'";
								$mysqli->query($sql);
								
								// evenements perso
								$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement) VALUES ($id_perso_punition,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>','<b>a reçu une amende</b>',NULL,NULL,' : Perte de tout ses XP !',NOW())";
								$mysqli->query($sql);
								
								$mess .= "Le perso a perdu <b>tout</b> ses XP !";
								
								$texte = addslashes($mess);
									
								// log_action_animation
								$sql = "INSERT INTO log_action_animation(date_acces, id_perso, page, action, texte) VALUES (NOW(), '$id', 'anim_punition.php', 'Punition XP All', '$texte')";
								$mysqli->query($sql);
							}
							else {
							
								$verif_xp = preg_match("#^[0-9]*[0-9]$#i","$montant_xp");
								
								if ($verif_xp) {
									
									// Récupération xp du perso
									$sql = "SELECT xp_perso FROM perso WHERE id_perso='$id_perso_punition'";
									$res = $mysqli->query($sql);
									$t = $res->fetch_assoc();
									
									$xp_perso_puni = $t['xp_perso'];
									
									if ($xp_perso_puni < $montant_xp) {
										
										// On enlève tout ses XP au perso
										$sql = "UPDATE perso SET xp_perso=0 WHERE id_perso='$id_perso_punition'";
										$mysqli->query($sql);
										
										// evenements perso
										$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement) VALUES ($id_perso_punition,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>','<b>a reçu une amende</b>',NULL,NULL,' : Perte de tout ses XP !',NOW())";
										$mysqli->query($sql);
										
										$mess .= "Le perso a perdu <b>tout</b> ses XP !";
									}
									else {
										// On enleve le montant
										$sql = "UPDATE perso SET xp_perso=xp_perso - $montant_xp WHERE id_perso='$id_perso_punition'";
										$mysqli->query($sql);
										
										// evenements perso
										$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement) VALUES ($id_perso_punition,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>','<b>a reçu une amende</b>',NULL,NULL,' : Perte de $montant_xp XP !',NOW())";
										$mysqli->query($sql);
										
										$mess .= "Le perso a perdu <b>".$montant_xp."</b> XP";
									}
									
									$texte = addslashes($mess);
									
									// log_action_animation
									$sql = "INSERT INTO log_action_animation(date_acces, id_perso, page, action, texte) VALUES (NOW(), '$id', 'anim_punition.php', 'Punition XP', '$texte')";
									$mysqli->query($sql);
								}
								else {
									// parametres incorrectes / modifiés
									$text_triche = "Tentative modification parametre Animation punition XP";
									
									$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id', '$text_triche')";
									$mysqli->query($sql);
									
									header("Location:jouer.php");
								}
							}
						}
					}
					else {
						// parametres incorrectes / modifiés
						$text_triche = "Tentative modification parametre id perso animation punition - camp perso puni pas le même que anim";
						
						$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id', '$text_triche')";
						$mysqli->query($sql);
						
						header("Location:jouer.php");
					}
				}
				else {
					// parametres incorrectes / modifiés
					$text_triche = "Tentative modification parametre id perso animation punition";
					
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
						<h2>Animation - Gestion des punitions des persos</h2>
						
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
					<form method='POST' action='anim_punitions.php'>
						<div class="form-row">
							<div class="form-group col-md-12">
								<label for="formSelectPerso">Punir le perso : </label>
							</div>
						</div>
						<div class="form-row">
							<div class="form-group col-md-8">
								<select class="form-control" name='liste_perso_punition' id="formSelectPerso" onchange="this.form.submit()">
								<?php
								// récuopération de tous les persos de son camp 
								$sql = "SELECT id_perso, nom_perso FROM perso WHERE clan='$camp' ORDER BY id_perso ASC";
								$res = $mysqli->query($sql);
								
								while ($t = $res->fetch_assoc()) {
									
									$id_perso_list 	= $t["id_perso"];
									$nom_perso_list	= $t["nom_perso"];
									
									echo "<option value='".$id_perso_list."' ";
									if (isset($id_perso_puni) && $id_perso_puni == $id_perso_list) {
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
					if (isset($_GET['bagne']) && trim($_GET['bagne']) == "ok" && !isset($_GET['duree'])) {
						echo "	<a href='anim_punitions.php?id_perso=".$id_perso_puni."&bagne=ok&duree=1' class='btn btn-danger'>Envoyer au bagne pour 1 jour</a>";
						echo "	<a href='anim_punitions.php?id_perso=".$id_perso_puni."&bagne=ok&duree=2' class='btn btn-danger'>Envoyer au bagne pour 2 jour</a>";
						echo "	<a href='anim_punitions.php?id_perso=".$id_perso_puni."&bagne=ok&duree=3' class='btn btn-danger'>Envoyer au bagne pour 3 jour</a>";
						echo "	<a href='anim_punitions.php?id_perso=".$id_perso_puni."&bagne=ok&duree=4' class='btn btn-danger'>Envoyer au bagne pour 4 jour</a>";
						echo "	<a href='anim_punitions.php?id_perso=".$id_perso_puni."&bagne=ok&duree=5' class='btn btn-danger'>Envoyer au bagne pour 5 jour</a>";
						echo "	<a href='anim_punitions.php?id_perso=".$id_perso_puni."&bagne=ok&duree=6' class='btn btn-danger'>Envoyer au bagne pour 6 jour</a>";
						echo "	<a href='anim_punitions.php?id_perso=".$id_perso_puni."&bagne=ok&duree=7' class='btn btn-danger'>Envoyer au bagne pour 7 jour</a>";
						echo "	<a href='anim_punitions.php?id_perso=".$id_perso_puni."&bagne=ok&duree=8' class='btn btn-danger'>Envoyer au bagne pour 8 jour</a>";
					}
					else if (isset($id_perso_puni)) {
						
						$sql = "SELECT or_perso, pc_perso, xp_perso, chef FROM perso WHERE id_perso='$id_perso_puni'";
						$res = $mysqli->query($sql);
						$t = $res->fetch_assoc();
						
						$thune_perso 	= $t['or_perso'];
						$pc_perso 		= $t['pc_perso'];
						$xp_perso		= $t['xp_perso'];
						$chef_perso		= $t['chef'];
						
						echo "<center>";
						echo "	<a href='anim_punitions.php?id_perso=".$id_perso_puni."&bagne=ok' class='btn btn-danger'>Envoyer au bagne</a>";
						echo "<br /><br />Ce perso possède <b>".$thune_perso."</b> thunes sur lui<br />";
						
						$perte_thune_all = false;
						
						if ($thune_perso >= 5) {
							echo "	<a href='anim_punitions.php?id_perso=".$id_perso_puni."&amende=5' class='btn btn-warning'>Infliger une amende de 5 thunes</a>";
						}
						else {
							$perte_thune_all = true;
							echo "	<a href='anim_punitions.php?id_perso=".$id_perso_puni."&amende=all' class='btn btn-warning'>Infliger une amende de toute sa thune</a>";
						}
						
						if ($thune_perso >= 10) {
							echo "	<a href='anim_punitions.php?id_perso=".$id_perso_puni."&amende=10' class='btn btn-warning'>Infliger une amende de 10 thunes</a>";
						}
						else if (!$perte_thune_all) {
							$perte_thune_all = true;
							echo "	<a href='anim_punitions.php?id_perso=".$id_perso_puni."&amende=all' class='btn btn-warning'>Infliger une amende de toute sa thune</a>";
						}
						
						if ($thune_perso >= 20) {
							echo "	<a href='anim_punitions.php?id_perso=".$id_perso_puni."&amende=20' class='btn btn-warning'>Infliger une amende de 20 thunes</a>";
						}
						else if (!$perte_thune_all) {
							$perte_thune_all = true;
							echo "	<a href='anim_punitions.php?id_perso=".$id_perso_puni."&amende=all' class='btn btn-warning'>Infliger une amende de toute sa thune</a>";
						}
						
						if ($thune_perso >= 50) {
							echo "	<a href='anim_punitions.php?id_perso=".$id_perso_puni."&amende=50' class='btn btn-warning'>Infliger une amende de 50 thunes</a>";
						}
						else if (!$perte_thune_all) {
							$perte_thune_all = true;
							echo "	<a href='anim_punitions.php?id_perso=".$id_perso_puni."&amende=all' class='btn btn-warning'>Infliger une amende de toute sa thune</a>";
						}
						
						if ($pc_perso > 0 && $chef_perso) {
							$perte_pc_all = false;
							
							echo "<br /><br />Ce perso possède <b>".$pc_perso."</b> Points de commandement<br />";
							
							if ($pc_perso >= 5) {
								echo "	<a href='anim_punitions.php?id_perso=".$id_perso_puni."&pc=5' class='btn btn-warning'>Infliger une perte de 5 PC</a>";
							}
							else {
								$perte_pc_all = true;
								echo "	<a href='anim_punitions.php?id_perso=".$id_perso_puni."&pc=all' class='btn btn-warning'>Infliger une perte de tout ses PC</a>";
							}
							
							if ($pc_perso >= 10) {
								echo "	<a href='anim_punitions.php?id_perso=".$id_perso_puni."&pc=10' class='btn btn-warning'>Infliger une perte de 10 PC</a>";
							}
							else if (!$perte_pc_all) {
								$perte_pc_all = true;
								echo "	<a href='anim_punitions.php?id_perso=".$id_perso_puni."&pc=all' class='btn btn-warning'>Infliger une perte de tout ses PC</a>";
							}
							
							if ($pc_perso >= 20) {
								echo "	<a href='anim_punitions.php?id_perso=".$id_perso_puni."&pc=20' class='btn btn-warning'>Infliger une perte de 20 PC</a>";
							}
							else if (!$perte_pc_all){
								$perte_pc_all = true;
								echo "	<a href='anim_punitions.php?id_perso=".$id_perso_puni."&pc=all' class='btn btn-warning'>Infliger une perte de tout ses PC</a>";
							}
							
							if ($pc_perso >= 50) {
								echo "	<a href='anim_punitions.php?id_perso=".$id_perso_puni."&pc=50' class='btn btn-warning'>Infliger une perte de 50 PC</a>";
							}
							else if (!$perte_pc_all){
								$perte_pc_all = true;
								echo "	<a href='anim_punitions.php?id_perso=".$id_perso_puni."&pc=all' class='btn btn-warning'>Infliger une perte de tout ses PC</a>";
							}
						}
						else if ($chef_perso) {
							echo "<br /><br /><b>Ce perso ne possède pas encore de Points de commandement</b><br />";
						}
						
						if ($xp_perso > 0) {
							$perte_xp_all = false;
							
							echo "<br /><br />Ce perso possède <b>".$xp_perso."</b> Points d'experience<br />";
							if ($xp_perso >= 5) {
								echo "	<a href='anim_punitions.php?id_perso=".$id_perso_puni."&xp=5' class='btn btn-warning'>Infliger une perte de 5 XP</a>";
							}
							else {
								$perte_xp_all = true;
								echo "	<a href='anim_punitions.php?id_perso=".$id_perso_puni."&xp=all' class='btn btn-warning'>Infliger une perte de tous ses XP</a>";
							}
							
							if ($xp_perso >= 5) {
								echo "	<a href='anim_punitions.php?id_perso=".$id_perso_puni."&xp=10' class='btn btn-warning'>Infliger une perte de 10 XP</a>";
							}
							else if (!$perte_xp_all){
								$perte_xp_all = true;
								echo "	<a href='anim_punitions.php?id_perso=".$id_perso_puni."&xp=all' class='btn btn-warning'>Infliger une perte de tous ses XP</a>";
							}
							
							if ($xp_perso >= 5) {
								echo "	<a href='anim_punitions.php?id_perso=".$id_perso_puni."&xp=20' class='btn btn-warning'>Infliger une perte de 20 XP</a>";
							}
							else if (!$perte_xp_all){
								$perte_xp_all = true;
								echo "	<a href='anim_punitions.php?id_perso=".$id_perso_puni."&xp=all' class='btn btn-warning'>Infliger une perte de tous ses XP</a>";
							}
							
							if ($xp_perso >= 5) {
								echo "	<a href='anim_punitions.php?id_perso=".$id_perso_puni."&xp=50' class='btn btn-warning'>Infliger une perte de 50 XP</a>";
							}
							else if (!$perte_xp_all){
								$perte_xp_all = true;
								echo "	<a href='anim_punitions.php?id_perso=".$id_perso_puni."&xp=all' class='btn btn-warning'>Infliger une perte de tous ses XP</a>";
							}
						}
						else {
							echo "<br /><br /><b>Ce perso ne possède pas encore de Points d'experience</b><br />";
						}
						echo "</center>";
						
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
	
