<?php
session_start();
require_once("../fonctions.php");

$mysqli = db_connexion();

include ('../nb_online.php');

if(isset($_SESSION["id_perso"])){
	
	$id_perso = $_SESSION['id_perso'];
	
	// recupération config jeu
	$admin = admin_perso($mysqli, $id_perso);
	
	if($admin){
		
		if (isset($_GET['mode_maj'])) {
			$sql = "UPDATE config_jeu SET disponible='0'";
			$mysqli->query($sql);
		}
		
		if (isset($_GET['mode_jeu'])) {
			$sql = "UPDATE config_jeu SET disponible='1'";
			$mysqli->query($sql);
		}
		
		$dispo = config_dispo_jeu($mysqli);
		
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
		<div class="container-fluid">
		
			<div class="row">
				<div class="col-12">

					<div align="center">
						<h2>Adminstration</h2>
					</div>
				</div>
			</div>
			
			<p align="center"><a class="btn btn-primary" href="jouer.php">Retour au jeu</a></p>
			
			<div class="row">
				<div class="col-12">
					<a class='btn btn-danger' href='admin_triche.php' target='_blank'>Vérification multi-compte</a>
					<a class='btn btn-danger' href='admin_teleporte.php'>Téléporter un perso sur la carte</a>
					<a class='btn btn-danger' href='admin_teleporte_bat.php'>Téléporter un perso dans un batiment</a>
					<a class='btn btn-danger' href='admin_acces.php'>Donner des accès à un perso</a>
					<a class='btn btn-danger' href='admin_perso.php'>Administration des perso</a>
					<a class='btn btn-danger' href='admin_compagnies.php'>Administration des compagnies</a>
					<a class='btn btn-danger' href='admin_batiments.php'>Administration des bâtiments</a>					
				</div>
			</div>
			
			<br />
			
			<div class="row">
				<div class="col-12">
					<a class='btn btn-warning' href='admin_tentative_triche.php' target='_blank'>LOGS Tentatives de triche</a>
					<a class='btn btn-warning' href='admin_multi.php' target='_blank'>Tableau des multis déclarés</a>
					<a class='btn btn-warning' href='admin_babysitte.php' target='_blank'>Tableau des babysittes déclarés</a>
					<a class='btn btn-warning' href='../creation_carte/utils_carte.php' target='_blank'>Editeur de carte</a>
				</div>
			</div>
			
			<br />
			
			<div class="row">
				<div class="col-12">
				<?php if ($dispo) { ?>
					<a class='btn btn-danger' href='admin_nvs.php?mode_maj=ok'>Passer le jeu en mode Mise à jour</a>
				<?php } else { ?>
					<a class='btn btn-success' href='admin_nvs.php?mode_jeu=ok'>Ouvrir le jeu</a>
				<?php } ?>
					<a class='btn btn-danger' href='admin_changement_carte.php' target='_blank'>Changement de carte</a>
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
		$_SESSION = array(); // On écrase le tableau de session
		session_destroy(); // On détruit la session
		
		header("Location:../index2.php");
	}
}
else{
	echo "<font color=red>Vous ne pouvez pas accéder à cette page, veuillez vous loguer.</font>";
}
?>