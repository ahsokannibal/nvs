<?php
session_start();
require_once("../fonctions.php");

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
			
			date_default_timezone_set('Europe/Paris');
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
						<h2>Animation - Événements détaillées des persos</h2>
					</div>
				</div>
			</div>
			
			<p align="center"><a class="btn btn-primary" href="anim_perso.php">Retour page de gestion des persos</a>&nbsp;<a class="btn btn-primary" href="animation.php">Retour page principale d'animation</a></p>
			
			<div class="row">
				<div class="col-12">
					<?php
					if (isset($_POST['liste_perso_event']) || (isset($_GET['id_perso']) && trim($_GET['id_perso']) != "")) {
				
						if (isset($_POST['liste_perso_event'])) {
							$id_perso_event = $_POST['liste_perso_event'];
						}
						else {
							$id_perso_event = $_GET['id_perso'];
						}
						
						// Récupération des infos du perso 
						$sql = "SELECT nom_perso, clan FROM perso WHERE id_perso='$id_perso_event'";
						$res = $mysqli->query($sql);
						$t = $res->fetch_assoc();
						
						$nom_perso_event 	= $t['nom_perso'];
						$camp_perso_event	= $t['clan'];
						
						if ($camp_perso_event != $camp) {
							echo "<center><font color='red'><b>Vous n'avez pas le droit de voir les événements détaillées d'un perso qui n'est pas de votre camp !</b></font></center>";
						}
						else {
							
							$sql_b 	= "SELECT * FROM declaration_babysitte WHERE id_baby='$id_perso_event' AND date_debut <= CURDATE() AND date_fin >= CURDATE()";
							$res_b 	= $mysqli->query($sql_b);
							$nb_b	= $res_b->num_rows;
							
							if ($nb_b) {
								
								$t_b = $res_b->fetch_assoc();
								
								$id_perso_baby = $t_b['id_perso'];
								
								// récup infos perso qui babysitte
								$sql_p = "SELECT nom_perso, clan FROM perso WHERE id_perso='$id_perso_baby'";
								$res_p = $mysqli->query($sql_p);
								$t_p = $res_p->fetch_assoc();

								$nom_perso_baby 	= $t_p['nom_perso'];
								$camp_perso_baby	= $t_p['clan'];
								
								if ($camp_perso_baby == 1) {
									$couleur_baby = 'blue';
								}
								elseif ($camp_perso_baby == 2) {
									$couleur_baby = 'red';
								}
								else {
									$couleur_baby = 'black';
								}
								
								echo "<div align='center'><b>Ce perso est actuellement babysitté par <font color='".$couleur_baby."'>".$nom_perso_baby."</font> [<a href='anim_event_perso.php?id_perso_event=".$id_perso_baby."'>".$id_perso_baby."</a>]</b></div>";
							}
							
							$sql_b 	= "SELECT * FROM declaration_babysitte WHERE id_perso='$id_perso_event' AND date_debut <= CURDATE() AND date_fin >= CURDATE()";
							$res_b 	= $mysqli->query($sql_b);
							$nb_b	= $res_b->num_rows;
							
							if ($nb_b) {
								
								while ($t_b = $res_b->fetch_assoc()) {
									
									$id_perso_baby = $t_b['id_baby'];
									
									// récup infos perso qui babysitte
									$sql_p = "SELECT nom_perso, clan FROM perso WHERE id_perso='$id_perso_baby'";
									$res_p = $mysqli->query($sql_p);
									$t_p = $res_p->fetch_assoc();

									$nom_perso_baby 	= $t_p['nom_perso'];
									$camp_perso_baby	= $t_p['clan'];
									
									if ($camp_perso_baby == 1) {
										$couleur_baby = 'blue';
									}
									elseif ($camp_perso_baby == 2) {
										$couleur_baby = 'red';
									}
									else {
										$couleur_baby = 'black';
									}
									
									echo "<div align='center'><b>Ce perso est actuellement en train de babysitter le perso <font color='".$couleur_baby."'>".$nom_perso_baby."</font> [<a href='anim_event_perso.php?id_perso_event=".$id_perso_baby."'>".$id_perso_baby."</a>]</b></div>";
								}
							}
							
							if (isset($_GET['jour']) && isset($_GET['mois']) && isset($_GET['annee'])) {
								$jour 	= $_GET['jour'];
								$mois	= $_GET['mois'];
								$annee	= $_GET['annee'];
								
								$date_debut = date("Y-m-d H:i:s" ,mktime(0, 0, 0, $mois, $jour, $annee));
								$date_fin = date("Y-m-d H:i:s" ,mktime(23, 59, 59, $mois, $jour, $annee));
								
								echo "<div align='center'><h3>Les événements de ".$nom_perso_event." [".$id_perso_event."] au ".sprintf('%02d', $jour)."/".sprintf('%02d', $mois)."/".$annee."</h3></div>";
								
								$sql = "SELECT IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, UNIX_TIMESTAMP(date_evenement) as date_evenement
										FROM evenement WHERE (IDActeur_evenement='$id_perso_event' OR IDCible_evenement='$id_perso_event') 
										AND date_evenement >= '$date_debut' AND date_evenement <= '$date_fin'
										ORDER BY ID_evenement DESC";
							}
							else {
								echo "<div align='center'><h3>Les 100 derniers événements de ".$nom_perso_event." [".$id_perso_event."]</h3></div>";
								
								$sql = "SELECT IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, UNIX_TIMESTAMP(date_evenement) as date_evenement
										FROM evenement WHERE IDActeur_evenement='$id_perso_event' OR IDCible_evenement='$id_perso_event' ORDER BY ID_evenement DESC LIMIT 100";
							}
							$res = $mysqli->query($sql);
							$nb_event = $res->num_rows;
							
							echo "<div class='table-responsive'>";
							echo "	<table class='table table-striped table-dark'>";
							echo "		<thead>";
							echo "			<th>date</th>";
							echo "			<th>acteur</th>";
							echo "			<th>evenement</th>";
							echo "			<th>cible</th>";
							echo "		</thead>";
							echo "		<tbody>";
							
							if ($nb_event > 0) {
								while ($t = $res->fetch_assoc()) {
								
									$id_acteur_event	= $t['IDActeur_evenement'];
									$nom_acteur_event	= $t['nomActeur_evenement'];
									$phrase_event		= $t['phrase_evenement'];
									$id_cible_event		= $t['IDCible_evenement'];
									$nom_cible_event	= $t['nomCible_evenement'];
									$effet_event		= $t['effet_evenement'];
									$date_event			= $t['date_evenement'];
									
									$date_event = date('Y-m-d H:i:s T', $date_event);
									
									echo "			<tr>";
									echo "				<td>".$date_event."</td>";
									echo "				<td>".$nom_acteur_event." [".$id_acteur_event."]</td>";
									echo "				<td>".$phrase_event;
									if (trim($effet_event) != "") {
										echo " ".$effet_event;
									}
									echo "				</td>";
									echo "				<td>";
									if ($id_cible_event != "") {
										echo $nom_cible_event." [".$id_cible_event."]";
									}
									echo "				</td>";
									echo "			</tr>";
									
								}
							}
							else {
								echo "			<tr>";
								echo "				<td align='center' colspan='4'><i>Aucun événement à cette date</i></td>";
								echo "			</tr>";
							}
							
							echo "		</tbody>";
							echo "	</table>";
							echo "</div>";
							
							$texte = addslashes("Consultation evenements détaillées du perso $nom_perso_event [$id_perso_event]");
									
							// log_action_animation
							$sql = "INSERT INTO log_action_animation(date_acces, id_perso, page, action, texte) VALUES (NOW(), '$id', 'anim_event_perso.php', 'Consultation évenements perso', '$texte')";
							$mysqli->query($sql);
						}
					}
					else {
					?>
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
					<?php
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
	
