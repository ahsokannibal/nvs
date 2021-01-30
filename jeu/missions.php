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
		$id_perso = $_SESSION["id_perso"];
		
		$verif_id_perso_session = preg_match("#^[0-9]*[0-9]$#i","$id_perso");
		
		if ($verif_id_perso_session) {
		
			// Récupération du camp du perso
			$sql = "SELECT clan FROM perso WHERE id_perso='$id_perso'";
			$res = $mysqli->query($sql);
			$t = $res->fetch_assoc();
			
			$camp = $t['clan'];
			
			if ($camp == '1') {
				$nom_camp = 'Nord';
				$couleur_clan_perso = 'blue';
			}
			else if ($camp == '2') {
				$nom_camp = 'Sud';
				$couleur_clan_perso = 'red';
			}
			else if ($camp == '3') {
				$nom_camp = 'Indien';
				$couleur_clan_perso = 'green';
			}
			
			$mess = "";
			$mess_erreur = "";
			
			if (isset($_GET['id_mission']) && $_GET['id_mission'] != "") {
				
				$id_mission = $_GET['id_mission'];
				
				$verif_id_mission 	= preg_match("#^[0-9]*[0-9]$#i","$id_mission");
				
				if ($verif_id_mission) {
				
					// On verifie que la mission existe bien
					$sql = "SELECT id_mission FROM missions WHERE id_mission='$id_mission' AND camp_mission='$camp'";
					$res = $mysqli->query($sql);
					$num_m = $res->num_rows;
					
					if ($num_m == 1) {
						if (isset($_GET['affecter_perso']) && $_GET['affecter_perso'] == "ok") {
							
							// Est ce que le perso est déjà affecté à la mission ?
							$sql = "SELECT id_perso FROM perso_in_mission WHERE id_mission='$id_mission' AND id_perso='$id_perso'";
							$res = $mysqli->query($sql);
							$verif_p = $res->num_rows;
							
							if ($verif_p == 0) {
								
								// Recup nombre participant autorisé
								$sql = "SELECT nombre_participant FROM missions WHERE id_mission='$id_mission'";
								$res = $mysqli->query($sql);
								$t = $res->fetch_assoc();
								
								$nb_participant_autorise = $t['nombre_participant'];
								
								// Récup nombre participant actuel
								$sql = "SELECT id_perso FROM perso_in_mission WHERE id_mission='$id_mission'";
								$res = $mysqli->query($sql);
								$num_participant = $res->num_rows;
								
								if ($num_participant < $nb_participant_autorise) {
									$sql = "INSERT INTO perso_in_mission (id_perso, id_mission) VALUES ('$id_perso','$id_mission')";
									$mysqli->query($sql);
									
									$mess .= "Affectation du perso à la mission réussie";
								}
								else {
									$mess_erreur .= "Vous ne pouvez pas participer à cette mission : nombre de participants maximum atteint";
								}
							}
							else {
								$mess_erreur .= "Vous participez déjà à cette mission";
							}
						}
						else if (isset($_GET['desaffecter_perso']) && $_GET['desaffecter_perso'] == "ok") {
							
							$sql = "DELETE FROM perso_in_mission WHERE id_perso='$id_perso' AND id_mission='$id_mission'";
							$mysqli->query($sql);
							
						}
						else if (isset($_GET['consulter']) && $_GET['consulter'] == "ok") {
							
							$id_mission_consult = $id_mission;
							
						}
					}
					else {
						// Tentative de triche
						$text_triche = "Tentative modification id mission affectation perso - id mission : ".$id_mission;
					
						$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id_perso', '$text_triche')";
						$mysqli->query($sql);
						
						$mess_erreur .= "Merci de ne pas jouer avec les paramètres de l'url...";
					}
				}
				else {
					// Tentative de triche
					$text_triche = "Tentative modification id mission non numerique affectation perso";
				
					$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id_perso', '$text_triche')";
					$mysqli->query($sql);
					
					$mess_erreur .= "Merci de ne pas jouer avec les paramètres de l'url...";
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
		<p align="center"><input type="button" value="Fermer la fenêtre de missions" onclick="window.close()"></p>
		
		<div class="container-fluid">
			
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
					<div align="center">
						<?php
						if (isset($id_mission_consult) && $id_mission_consult != 0) {
							
							// récupération des infos de la mission
							$sql = "SELECT nom_mission, texte_mission, recompense_thune, recompense_xp, recompense_pc, nombre_participant, date_debut_mission, date_fin_mission 
									FROM missions WHERE id_mission='$id_mission_consult'";
							$res = $mysqli->query($sql);
							$t = $res->fetch_assoc();
							
							$nom_mission 	= stripslashes($t['nom_mission']);
							$desc_mission 	= stripslashes($t['texte_mission']);
							$rec_thune		= $t['recompense_thune'];
							$rec_xp			= $t['recompense_xp'];
							$rec_pc			= $t['recompense_pc'];
							$nb_participant	= $t['nombre_participant'];
							$date_debut		= $t['date_debut_mission'];
							$date_fin		= $t['date_fin_mission'];
							
							echo "<b><u>Nom de la mission : </u></b>".$nom_mission."<br />";
							echo "<b><u>Détail de la mission : </u></b><br />";
							echo $desc_mission;
						}
						?>
					</div>
				</div>
			</div>
			
			<br />
			
			<div class="row">
				<div class="col-12">
					<div align="center">
						<h2>Liste des missions actives</h2>
						<?php
						// Récupération de la liste des missions actives
						$sql = "SELECT id_mission, nom_mission, texte_mission, recompense_thune, recompense_xp, recompense_pc, nombre_participant, date_debut_mission, date_fin_mission 
								FROM missions WHERE date_debut_mission IS NOT NULL AND (date_fin_mission IS NULL OR date_fin_mission >= CURDATE())
								AND camp_mission='$camp'";
						$res = $mysqli->query($sql);
						$nb_missions_actives = $res->num_rows;
						
						if ($nb_missions_actives > 0) {
							
							echo "<div id='table_mission' class='table-responsive'>";						
							echo "	<table class='table'>";
							echo "		<thead>";
							echo "			<tr>";
							echo "				<th style='text-align:center'>Nom mission</th>";
							echo "				<th style='text-align:center'>Date d'activation de la mission</th>";
							echo "				<th style='text-align:center'>Date d'expiration de la mission</th>";
							echo "				<th style='text-align:center'>Récompense Thune</th>";
							echo "				<th style='text-align:center'>Récompense XP/XPI</th>";
							echo "				<th style='text-align:center'>Récompense PC</th>";
							echo "				<th style='text-align:center'>Nombre participant Max</th>";
							echo "				<th style='text-align:center'>Liste des participants à la mission</th>";
							echo "				<th style='text-align:center'>Actions</th>";
							echo "			</tr>";
							echo "		</thead>";
							echo "		<tbody>";
							
							while ($t = $res->fetch_assoc()) {
								
								$id_mission		= $t['id_mission'];
								$nom_mission 	= stripslashes($t['nom_mission']);
								$desc_mission 	= stripslashes($t['texte_mission']);
								$rec_thune		= $t['recompense_thune'];
								$rec_xp			= $t['recompense_xp'];
								$rec_pc			= $t['recompense_pc'];
								$nb_participant	= $t['nombre_participant'];
								$date_debut		= $t['date_debut_mission'];
								$date_fin		= $t['date_fin_mission'];
								
								$sql_p = "SELECT perso.id_perso, perso.nom_perso FROM perso, perso_in_mission
										WHERE perso.id_perso = perso_in_mission.id_perso
										AND id_mission='$id_mission'";
								$res_p = $mysqli->query($sql_p);
								$num_p = $res_p->num_rows;
								
								// Est ce que le perso est déjà affecté à la mission ?
								$sql_pp = "SELECT id_perso FROM perso_in_mission WHERE id_mission='$id_mission' AND id_perso='$id_perso'";
								$res_pp = $mysqli->query($sql_pp);
								$verif_pp = $res_pp->num_rows;
								
								echo "				<tr>";
								echo "					<td align='center'>".$nom_mission."</td>";
								echo "					<td align='center'>".$date_debut."</td>";
								echo "					<td align='center'>".$date_fin."</td>";
								echo "					<td align='center'>".$rec_thune."</td>";
								echo "					<td align='center'>".$rec_xp."</td>";
								echo "					<td align='center'>".$rec_pc."</td>";
								echo "					<td align='center'>".$nb_participant."</td>";
								echo "					<td align='center'>";
								while ($t_p = $res_p->fetch_assoc()) {
									
									$id_perso_mission 	= $t_p['id_perso'];
									$nom_perso_mission	= $t_p['nom_perso'];
									
									echo $nom_perso_mission." [<a href='evenement.php?infoid=".$id_perso_mission."'>".$id_perso_mission."</a>] <br />";
									
								}
								echo "					</td>";
								echo "					<td align='center'>";
								if ($num_p < $nb_participant && $verif_pp == 0) {
									echo "						<a href='missions.php?id_mission=".$id_mission."&affecter_perso=ok' class='btn btn-warning'>Participer à la mission</a>";
								}
								if ($verif_pp == 1) {
									echo "						<a href='missions.php?id_mission=".$id_mission."&desaffecter_perso=ok' class='btn btn-danger'>Ne plus participer à la mission</a>";
								}
								echo "						<a href='missions.php?id_mission=".$id_mission."&consulter=ok' class='btn btn-info'>Consulter le détail</a>";
								echo "					</td>";
								echo "				</tr>";
							}
							
							echo "		</tbody>";
							echo "	</table>";
							echo "</div>";
							
						}
						else {
							echo "<i>Aucune mission n'est actuellement active</i>";
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
</html>
<?php
		}
		else {
			// logout
			$_SESSION = array();
			session_destroy();
			
			header("Location:../index2.php");
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
