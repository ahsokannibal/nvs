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
			
			// Récupération des demandes sur la gestion des compagnies
			$sql = "SELECT * FROM compagnie_demande_anim, compagnies 
					WHERE compagnie_demande_anim.id_compagnie = compagnies.id_compagnie
					AND compagnies.id_clan='$camp'";
			$res = $mysqli->query($sql);
			$nb_demandes_gestion_compagnie = $res->num_rows;
			
			// Récupération des demandes sur la gestion des persos 
			$sql = "(SELECT perso_demande_anim.* FROM perso_demande_anim, perso
					WHERE perso_demande_anim.id_perso = perso.id_perso
					AND perso.clan = '$camp'
					AND perso_demande_anim.type_demande = 1)
					UNION ALL
					(SELECT perso_demande_anim.* FROM perso_demande_anim, perso
					WHERE perso_demande_anim.id_perso = perso.idJoueur_perso
					AND perso.clan = '$camp'
					AND perso.chef = '1'
					AND perso_demande_anim.type_demande > 1)
					";
			$res = $mysqli->query($sql);
			$nb_demandes_gestion_perso = $res->num_rows;
			
			// Récupération du nombre de questions / remontées anims en attente de réponse
			$sql = "SELECT id FROM anim_question WHERE id_camp='$camp' AND status='0'";
			$res = $mysqli->query($sql);
			$nb_questions_anim = $res->num_rows;
			
			// Récupération du nombre de remontées de capture RP non traitées
			$sql = "SELECT id FROM anim_capture WHERE statut='0'";
			$res = $mysqli->query($sql);
			$nb_captures_anim = $res->num_rows;
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
						<h2>Animation du camp <?php echo $nom_camp; ?></h2>
					</div>
				</div>
			</div>
			
			<p align="center"><a class="btn btn-primary" href="jouer.php">Retour au jeu</a></p>
			
			<div class="row">
				<div class="col-12">
					<a class='btn btn-info' href='anim_compagnie.php'>Gestion des compagnies <span class="badge badge-danger" title='<?php echo $nb_demandes_gestion_compagnie." demandes en attente"; ?>'><?php if ($nb_demandes_gestion_compagnie > 0) { echo $nb_demandes_gestion_compagnie; }?></span></a>
					<a class='btn btn-info' href='anim_perso.php'>Gestion des persos <span class="badge badge-danger" title='<?php echo $nb_demandes_gestion_perso." demandes en attente"; ?>'><?php if ($nb_demandes_gestion_perso > 0) { echo $nb_demandes_gestion_perso; }?></span></a>
					<a class='btn btn-info' href='anim_batiment.php'>Gestion des bâtiments</a>
					<a class='btn btn-info' href='anim_penitencier.php'>Gestion du pénitencier</a>
					<a class='btn btn-info' href='anim_trains.php'>Gestion des trains</a>					
				</div>
			</div>
			
			<br />
			
			<div class="row">
				<div class="col-12">
					<a class='btn btn-warning' href='anim_missions.php'>Gestion des missions</a>
					<a class='btn btn-warning' href='anim_questions.php'>Les questions / remontées des joueurs <span class="badge badge-danger" title='<?php echo $nb_questions_anim." questions en attente"; ?>'><?php if ($nb_questions_anim > 0) { echo $nb_questions_anim; }?></span></a>
					<a class='btn btn-warning' href='anim_capture_rp.php'>Les captures RP <span class="badge badge-danger" title='<?php echo $nb_captures_anim." captures en attente"; ?>'><?php if ($nb_captures_anim > 0) { echo $nb_captures_anim; }?></span></a>
					<a class='btn btn-warning' href='anim_message.php'>Messages</a>
					<a class='btn btn-warning' href='anim_zone_rapat.php'>Zone de respawn</a>
				</div>
			</div>
			
			<br />
			
			<div class="row">
				<div class="col-12">
					<a class='btn btn-primary' href='anim_gestion_animaux_pnj.php'>Gestion des PNJ animaux</a>
					<a class='btn btn-primary' href='anim_gestion_perso_pnj.php'>Gestion des PNJ perso</a>

				</div>
			</div>
			
			<br />
			
			<div class="row">
				<div class="col-12">
					<a class='btn btn-secondary' href='anim_multi.php'>Tableau des multis déclarés</a>
					<a class='btn btn-secondary' href='anim_babysitte.php'>Tableau des Babysittes déclarés</a>
					<a class='btn btn-secondary' href='anim_log_pendaisons.php'>Tableau des Pendus</a>
					<a class='btn btn-secondary' href='anim_log_access.php'>LOGS accès</a>
					<a class='btn btn-secondary' href='anim_top_logs_acces.php'>TOP Logs accès</a>
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
	