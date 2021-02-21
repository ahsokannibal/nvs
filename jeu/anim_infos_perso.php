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
			
			if (isset($_POST['liste_perso_info'])) {
						
				$id_perso_info = $_POST['liste_perso_info'];
				
			}
			
			if (isset($_GET['consulter_mp'])) {
				$id_perso_info = $_GET['consulter_mp'];
			}
			
			if (isset($_GET['voir_respawn'])) {
				$id_perso_info = $_GET['voir_respawn'];
			}
			
			if (isset($_GET['logs_respawn'])) {
				$id_perso_info = $_GET['logs_respawn'];
			}
			
			if (isset($_GET['verifier_charge'])) {
				$id_perso_info = $_GET['verifier_charge'];
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
						<h2>Animation - Informations des persos</h2>
						
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
					<form method='POST' action='anim_infos_perso.php'>
						<div class="form-row">
							<div class="form-group col-md-12">
								<label for="formSelectPerso">Infos sur le perso : </label>
							</div>
						</div>
						<div class="form-row">
							<div class="form-group col-md-8">
								<select class="form-control" name='liste_perso_info' id="formSelectPerso" onchange="this.form.submit()">
								<?php
								// récuopération de tous les persos de son camp 
								$sql = "SELECT id_perso, nom_perso FROM perso WHERE clan='$camp' ORDER BY id_perso ASC";
								$res = $mysqli->query($sql);
								
								while ($t = $res->fetch_assoc()) {
									
									$id_perso_list 	= $t["id_perso"];
									$nom_perso_list	= $t["nom_perso"];
									
									echo "<option value='".$id_perso_list."' ";
									if (isset($id_perso_info) && $id_perso_info == $id_perso_list) {
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
					if (isset($id_perso_info)) {
						
						$sql = "SELECT * FROM perso WHERE id_perso='$id_perso_info'";
						$res = $mysqli->query($sql);
						$t = $res->fetch_assoc();
						
						$nom_perso 	= $t['nom_perso'];
						$xp_perso 	= $t['xp_perso'];
						$pc_perso 	= $t['pc_perso'];
						$pi_perso	= $t['pi_perso'];
						$pv_perso 	= $t['pv_perso'];
						$pm_perso 	= $t['pm_perso'];
						$pa_perso	= $t['pa_perso'];
						$or_perso 	= $t['or_perso'];
						$ch_perso	= $t['charge_perso'];
						$type_p 	= $t['type_perso'];
						$test_b 	= $t['bourre_perso'];
						$camp_perso	= $t['clan'];
						$bat_perso	= $t['bataillon'];
						
						if ($camp_perso == 1) {
							$nom_camp_perso 	= "Nord";
							$couleur_camp_perso	= "blue";
						}
						else if ($camp_perso == 2) {
							$nom_camp_perso 	= "Sud";
							$couleur_camp_perso	= "red";
						}
						else if ($camp_perso == 2) {
							$nom_camp_perso 	= "Indiens";
							$couleur_camp_perso	= "green";
						}
						else {
							$nom_camp_perso 	= "Outlaw";
							$couleur_camp_perso	= "black";
						}
						
						$im_camp_perso = $nom_camp_perso.".gif";
						$im_type_perso = get_image_type_perso($type_p, $camp_perso);
						
						echo "<table class='table'>";
						echo "	<tr>";
						echo "		<td align='center'><img src='../images_perso/".$im_type_perso."'></td>";
						echo "		<td align='center'><b>Nom : </b>".$nom_perso."</td>";
						echo "		<td align='center'><b>Bataillon : </b>".$bat_perso."</td>";
						echo "	</tr>";
						echo "</table>";
						
						if (isset($_GET['consulter_mp'])) {
							echo "<a href='anim_infos_perso.php?consulter_mp=".$id_perso_info."' class='btn btn-secondary'>Consulter les MP</a> ";
						}
						else {
							echo "<a href='anim_infos_perso.php?consulter_mp=".$id_perso_info."' class='btn btn-warning'>Consulter les MP</a> ";
						}
						
						if (isset($_GET['voir_respawn'])) {
							echo "<a href='anim_infos_perso.php?voir_respawn=".$id_perso_info."' class='btn btn-secondary'>Voir ses respawns</a> ";
						}
						else {
							echo "<a href='anim_infos_perso.php?voir_respawn=".$id_perso_info."' class='btn btn-warning'>Voir ses respawns</a> ";
						}
						
						if (isset($_GET['logs_respawn'])) {
							echo "<a href='anim_infos_perso.php?logs_respawn=".$id_perso_info."' class='btn btn-secondary'>Logs respawns</a> ";
						}
						else {
							echo "<a href='anim_infos_perso.php?logs_respawn=".$id_perso_info."' class='btn btn-warning'>Logs respawns</a> ";
						}
						
						if (isset($_GET['verifier_charge'])) {
							echo "<a href='anim_infos_perso.php?verifier_charge=".$id_perso_info."' class='btn btn-secondary'>Vérifier la charge du perso</a> ";
						}
						else {
							echo "<a href='anim_infos_perso.php?verifier_charge=".$id_perso_info."' class='btn btn-warning'>Vérifier la charge du perso</a> ";
						}
					}
					?>
				</div>
			</div>
			
			<div class='row'>
				<?php
				if (isset($_GET['consulter_mp'])) {
					
					$sql_mp = "SELECT * FROM message WHERE id_expediteur='".$id_perso_info."' ORDER BY id_message DESC";
					$res_mp = $mysqli->query($sql_mp);
					$nb_mp_e = $res_mp->num_rows;
					
					echo "	<div class='col-6'>";
					echo "		<h2>MP envoyés par le perso (".$nb_mp_e.")</h2>";
					echo "		<table class='table'>";
					echo "			<tr>";
					echo "				<th style='text-align:center'>Date</th><th style='text-align:center'>Objet</th>";
					echo "			</tr>";
					
					while ($t_mp = $res_mp->fetch_assoc()) {
						
						$date_mp 	= $t_mp['date_message'];
						$contenu_mp = $t_mp['contenu_message'];
						$objet_mp 	= $t_mp['objet_message'];
						$id_mp		= $t_mp['id_message'];
						
						echo "			<tr>";
						echo "				<td align='center'>".$date_mp."</td>";
						echo "				<td>".$objet_mp."</td>";
						echo "			</tr>";
					}
					
					echo "			</tr>";
					echo "		</table>";
					echo "	</div>";
					
					$sql_mp = "SELECT date_message, objet_message, contenu_message, message.id_message FROM message, message_perso 
								WHERE message.id_message = message_perso.id_message
								AND message_perso.id_perso='".$id_perso_info."' ORDER BY message.id_message DESC";
					$res_mp = $mysqli->query($sql_mp);
					$nb_mp_r = $res_mp->num_rows;
					
					echo "	<div class='col-6'>";
					echo "		<h2>MP reçues par le perso (".$nb_mp_r.")</h2>";
					echo "		<table class='table'>";
					echo "			<tr>";
					echo "				<th style='text-align:center'>Date</th><th style='text-align:center'>Objet</th>";
					echo "			</tr>";
					while ($t_mp = $res_mp->fetch_assoc()) {
						
						$date_mp 	= $t_mp['date_message'];
						$contenu_mp = $t_mp['contenu_message'];
						$objet_mp 	= $t_mp['objet_message'];
						$id_mp		= $t_mp['id_message'];
						
						echo "			<tr>";
						echo "				<td align='center'>".$date_mp."</td>";
						echo "				<td>".$objet_mp."</td>";
						echo "			</tr>";
					}
					
					echo "			</tr>";
					echo "		</table>";
					echo "	</div>";
				}
				
				if (isset($_GET['voir_respawn'])) {
					
					echo "	<div class='col-12'>";
					
					$sql = "SELECT * FROM perso_as_respawn WHERE id_perso='$id_perso_info' ORDER BY id_bat ASC";
					$res = $mysqli->query($sql);
					
					echo "<table class='table'>";
					echo "	<thead>";
					echo "		<tr>";
					echo "			<th>Batiment</th><th>Etat</th><th>Position</th>";
					echo "		</tr>";
					echo "	</thead>";
					echo "	<tbody>";
					
					while ($t = $res->fetch_assoc()) {
						
						$id_i_bat_respawn = $t['id_instance_bat'];
						
						$sql_b = "SELECT nom_batiment, nom_instance, pv_instance, pvMax_instance, x_instance, y_instance, contenance_instance FROM instance_batiment, batiment 
									WHERE instance_batiment.id_batiment = batiment.id_batiment AND id_instanceBat='$id_i_bat_respawn'";
						$res_b = $mysqli->query($sql_b);
						$t_b = $res_b->fetch_assoc();
						
						$nom_bat	= $t_b['nom_batiment'];
						$nom_i_bat	= $t_b['nom_instance'];
						$pv_i_bat	= $t_b['pv_instance'];
						$pvM_i_bat	= $t_b['pvMax_instance'];
						$x_i_bat	= $t_b['x_instance'];
						$y_i_bat	= $t_b['y_instance'];
						$cont_i_bat	= $t_b['contenance_instance'];
						
						echo "		<tr>";
						echo "			<td>".$nom_bat." ".$nom_i_bat." [".$id_i_bat_respawn."]</td>";
						echo "			<td>".$pv_i_bat."/".$pvM_i_bat."</td>";
						echo "			<td>".$x_i_bat."/".$y_i_bat."</td>";
						echo "		</tr>";
						
					}
					echo "	</tbody>";
					echo "</table>";
					
					echo "</div>";
				}
				
				if (isset($_GET['logs_respawn'])) {
					
					echo "	<div class='col-12'>";
					
					$sql = "SELECT * FROM log_respawn WHERE id_perso='$id_perso_info'";
					$res = $mysqli->query($sql);
					
					echo "<table class='table'>";
					echo "	<thead>";
					echo "		<tr>";
					echo "			<th>Date respawn</th><th>Infos respawn</th>";
					echo "		</tr>";
					echo "	</thead>";
					echo "	<tbody>";
					
					while ($t = $res->fetch_assoc()) {
						
						$date_respawn = $t['date_respawn'];
						$info_respawn = $t['texte_respawn'];
						
						echo "		<tr>";
						echo "			<td>".$date_respawn."</td>";
						echo "			<td>".$info_respawn."</td>";
						echo "		</tr>";
						
					}
					echo "	</tbody>";
					echo "</table>";
					
					echo "</div>";
				}
				
				if (isset($_GET['verifier_charge'])) {
					
					echo "	<div class='col-12'>";
					
					$sql = "SELECT SUM(poids_objet) as somme_poids_objets FROM perso_as_objet, objet WHERE perso_as_objet.id_objet = objet.id_objet AND id_perso='$id_perso_info'";
					$res = $mysqli->query($sql);
					$t_o = $res->fetch_assoc();
					
					$poids_objets = $t_o['somme_poids_objets'];
					if ($poids_objets == null) {
						$poids_objets = 0;
					}
					
					$sql = "SELECT SUM(poids_arme) as somme_poids_armes FROM perso_as_arme, arme WHERE perso_as_arme.id_arme = arme.id_arme AND id_perso='$id_perso_info' AND est_portee='0'";
					$res = $mysqli->query($sql);
					$t_a = $res->fetch_assoc();
					
					$poids_armes = $t_a['somme_poids_armes'];
					if ($poids_armes == null) {
						$poids_armes = 0;
					}
					
					echo "Somme total du poids des objets dans le sac du perso : <b>".$poids_objets."</b><br />";
					echo "Somme total du poids des armes dans le sac du perso : <b>".$poids_armes."</b><br />";
					
					echo "	</div>";
				}
				?>
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
	