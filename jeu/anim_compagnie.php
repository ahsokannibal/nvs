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
			
			if (isset($_GET['id_compagnie']) && isset($_GET['type']) && isset($_GET['valid'])) {
				
				$id_compagnie_maj 	= $_GET['id_compagnie'];
				$type_demande_maj 	= $_GET['type'];
				$valid_maj			= $_GET['valid'];
				
				$verif_id = preg_match("#^[0-9]*[0-9]$#i","$id_compagnie_maj");
				$verif_type = preg_match("#^[0-9]*[0-9]$#i","$type_demande_maj");
				
				if ($verif_id && $verif_type) {
				
					if ($_GET['valid'] == 'ok') {
						// Validation de la demande
						
						if ($type_demande_maj == 1) {
							// Demande de changement de nom 
							// Récupération du nouveau nom
							$sql = "SELECT info_demande FROM compagnie_demande_anim WHERE id_compagnie='$id_compagnie_maj' AND type_demande='1'";
							$res = $mysqli->query($sql);
							$t = $res->fetch_assoc();
							
							$nouveau_nom_compagnie = addslashes($t['info_demande']);
							
							$sql = "UPDATE compagnies SET nom_compagnie='$nouveau_nom_compagnie' WHERE id_compagnie='$id_compagnie_maj'";
							$mysqli->query($sql);
							
							// Suppression de la demande 
							$sql = "DELETE FROM compagnie_demande_anim WHERE id_compagnie='$id_compagnie_maj' AND type_demande='$type_demande_maj'";
							$mysqli->query($sql);
							
							// TODO - Envoi d'un MP
						}
						else if ($type_demande_maj == 2) {
							// Demande de suppression de la compagnie
							
							// recuperation des information sur la compagnie
							$sql = "SELECT nom_compagnie FROM compagnies WHERE id_compagnie=$id_compagnie_maj";
							$res = $mysqli->query($sql);
							$sec = $res->fetch_assoc();
							
							$nom_compagnie		= addslashes($sec["nom_compagnie"]);
							
							// Récupération de l'id du group de la compagnie sur le forum
							$sql = "SELECT group_id FROM ".$table_prefix."groups WHERE group_name='$nom_compagnie'";
							$res = $mysqli->query($sql);
							$t = $res->fetch_assoc();
							
							$id_group_forum = $t['group_id'];
							
							// récupération des persos dans la compagnie 
							$sql = "SELECT * FROM perso_in_compagnie WHERE id_compagnie='$id_compagnie_maj'";
							$res = $mysqli->query($sql);
							
							while ($t = $res->fetch_assoc()) {
								
								$id_perso_a_virer = $t['id_perso'];
								
								// on vire le perso de la compagnie
								$sql = "DELETE FROM perso_in_compagnie WHERE id_perso=$id_perso_a_virer AND id_compagnie=$id_compagnie_maj";
								$mysqli->query($sql);
								
								// on enleve le perso de la banque
								$sql = "DELETE FROM banque_compagnie WHERE id_perso=$id_perso_a_virer";
								$mysqli->query($sql);
								
								// -- FORUM
								// Récupération de l'id de l'utilisateur sur le forum 
								$sql = "SELECT user_id FROM ".$table_prefix."users WHERE username IN 
											(SELECT nom_perso FROM perso WHERE idJoueur_perso IN 
												(SELECT idJoueur_perso FROM perso WHERE id_perso='$id_perso_a_virer') AND chef='1')";
								$res = $mysqli->query($sql);
								$t = $res->fetch_assoc();
								
								$id_user_forum = $t['user_id'];
								
								// Suppression de l'utilisateur du groupe sur le forum
								$sql = "DELETE FROM ".$table_prefix."user_group WHERE group_id='$id_group_forum' AND user_id='$id_user_forum'";
								$mysqli->query($sql);
								
							}
							
							// Suppression du groupe sur le forum 
							$sql = "DELETE FROM ".$table_prefix."groups WHERE group_name='$nom_compagnie'";
							$mysqli->query($sql);
							
							// Suppression de la compagnie sur le jeu 
							$sql = "DELETE FROM compagnies WHERE id_compagnie='$id_compagnie_maj'";
							$mysqli->query($sql);
							
							// Suppression de la banque de la compagnie
							$sql = "DELETE FROM banque_as_compagnie WHERE id_compagnie='$id_compagnie_maj'";
							$mysqli->query($sql);
							
							// Suppression de toutes le demandes liées à cette compagnie
							$sql = "DELETE FROM compagnie_demande_anim WHERE id_compagnie='$id_compagnie_maj'";
							$mysqli->query($sql);
						}						
						
						header("Location:anim_compagnie.php");
					}
					else if ($_GET['valid'] == 'refus') {
						// Refus de la demande
						
						// Suppression de la demande 
						$sql = "DELETE FROM compagnie_demande_anim WHERE id_compagnie='$id_compagnie_maj' AND type_demande='$type_demande_maj'";
						$mysqli->query($sql);
						
						// TODO MP
						
						header("Location:anim_compagnie.php");
					}
					else {
						// Tentaive de triche d'un anim
						$text_triche = "L'animateur avec le perso $id a joué avec le paramètre valid !";
			
						$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id', '$text_triche')";
						$mysqli->query($sql);
						
						echo "<center><font color='red'><b>Merci de ne pas jouer avec les paramètres de l'url...</b></font></center>";
					}
				}
				else {
					// Tentaive de triche d'un anim
					$text_triche = "L'animateur avec le perso $id a joué avec les paramètres id_compagnie et/ou type !";
			
					$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id', '$text_triche')";
					$mysqli->query($sql);
					
					echo "<center><font color='red'><b>Merci de ne pas jouer avec les paramètres de l'url...</b></font></center>";
				}
			}
			
			// Récupération des demandes sur la gestion des compagnies
			$sql = "SELECT * FROM compagnie_demande_anim, compagnies 
					WHERE compagnie_demande_anim.id_compagnie = compagnies.id_compagnie
					AND compagnies.id_clan='$camp'
					ORDER BY compagnie_demande_anim.id_compagnie ASC";
			$res = $mysqli->query($sql);
			
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
						<h2>Animation - Gestion des demandes des compagnies</h2>
					</div>
				</div>
			</div>
			
			<p align="center"><a class="btn btn-primary" href="animation.php">Retour page principale d'animation</a></p>
			
			<div class="row">
				<div class="col-12">
					<div class="table-responsive">
						<table class="table">
							<thead>
								<tr>
									<th style='text-align:center'>Compagnie</th>
									<th style='text-align:center'>Type de demande</th>
									<th style='text-align:center'>Infos Demande</th>
									<th style='text-align:center'>Action</th>
								</tr>
							</thead>
							<tbody>
								<?php
								while ($t = $res->fetch_assoc()) {
									
									$id_compagnie 	= $t['id_compagnie'];
									$type_demande	= $t['type_demande'];
									$info_demande	= $t['info_demande'];
									
									if ($type_demande == 1) {
										$nom_demande = "Changement de nom";
										$info_demande = "Nouveau nom : ".$info_demande;
									}
									else if ($type_demande == 2) {
										$nom_demande = "Demande de suppression";
									}
									else {
										$nom_demande = "Inconnu";
									}
									
									// Récupération infos compagnie
									$sql_c = "SELECT nom_compagnie FROM compagnies WHERE id_compagnie='$id_compagnie'";
									$res_c = $mysqli->query($sql_c);
									$t_c = $res_c->fetch_assoc();
									
									$nom_compagnie = $t_c['nom_compagnie'];
									
									echo "<tr>";
									echo "	<td align='center'>".$nom_compagnie." [<a href='compagnie.php?id_compagnie=".$id_compagnie."&voir_compagnie=ok'>".$id_compagnie."</a>]</td>";
									echo "	<td align='center'>".$nom_demande."</td>";
									echo "	<td align='center'>".$info_demande."</td>";
									echo "	<td align='center'>";
									echo "		<a class='btn btn-success' href=\"anim_compagnie.php?id_compagnie=".$id_compagnie."&type=".$type_demande."&valid=ok\">Accepter</a>";
									echo "		<a class='btn btn-danger' href=\"anim_compagnie.php?id_compagnie=".$id_compagnie."&type=".$type_demande."&valid=refus\">Refuser</a>";
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
	