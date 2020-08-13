<?php
session_start();
require_once("../fonctions.php");

$mysqli = db_connexion();

include ('../nb_online.php');

// recupération config jeu
$dispo = config_dispo_jeu($mysqli);
$admin = admin_perso($mysqli, $_SESSION["id_perso"]);

if($dispo || $admin){
	
	if (isset($_SESSION["id_perso"])) {
		
		//recuperation des variables de sessions
		$id = $_SESSION["id_perso"];
		
		// Le perso est-il membre de l'etat major
		$sql = "SELECT camp_em FROM perso_in_em WHERE id_perso='$id'";
		$res = $mysqli->query($sql);
		$t = $res->fetch_assoc();
		$verif = $res->num_rows;
		
		if ($verif) {
			
			$msg_erreur = "";
			$msg = "";
		
			$camp_em = $t['camp_em'];
			
			if ($camp_em == 1) {
				$image_em = "em_nord.png";
			} else {
				$image_em = "em_sud.png";
			}
			
			$sql = "SELECT * FROM choix_carte_suivante WHERE id_camp='$camp_em'";
			$res = $mysqli->query($sql);
			$t = $res->fetch_assoc();
			
			$carte_suivante_choisi 		= $t['carte'];
			$date_choix_carte_suivante 	= $t['date_choix'];
			
			if (isset($_POST['choixCarteSuivante']) && trim($_POST['choixCarteSuivante']) != "") {
				
				$choixCarteSuivante = $_POST['choixCarteSuivante'];
				
				if (isset($carte_suivante_choisi) && $carte_suivante_choisi != null && $carte_suivante_choisi != "") {
					$sql = "UPDATE choix_carte_suivante SET carte='$choixCarteSuivante', date_choix=NOW() WHERE id_camp='$camp_em'";
				}
				else {
					$sql = "INSERT INTO choix_carte_suivante (id_camp, carte, date_choix) VALUES ('$camp_em', '$choixCarteSuivante', NOW())";
				}
				$mysqli->query($sql);
				
				$carte_suivante_choisi = $choixCarteSuivante;
				
				$msg .= "Carte suivante choisi";
			}
			
?>
		
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<title>Nord VS Sud - Etat Major</title>
		
		<!-- Required meta tags -->
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		
		<!-- Bootstrap CSS -->
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
	</head>
	<body onload="addMouseChecker('carto2', 'idInput2', 'xy'); addMouseChecker('carto3', 'idInput3', 'xy');">
		<div class="container">
			
			<nav class="navbar navbar-expand-lg navbar-light bg-light">
				<a class="navbar-brand" href="#"><img src='../images/<?php echo $image_em; ?>' width="80" height="60" alt=""></a>
				<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
					<span class="navbar-toggler-icon"></span>
				</button>
				<div class="collapse navbar-collapse" id="navbarNavAltMarkup">
					<div class="navbar-nav">
						<ul class="navbar-nav">
							<li class="nav-item">
								<a class="nav-link" href="etat_major.php">Validation compagnies</a>
							</li>
						</ul>
						<ul class="navbar-nav">
							<li class="nav-item dropdown active">
								<a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLinkCarte" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
									Carte suivante
								</a>
								<div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLinkCarte">
									<a class="dropdown-item" href="em_cartes_suivante.php">Voir les carte suivante</a>
									<a class="dropdown-item" href="em_definir_infra.php">Positionner les infrastructures</a>
								</div>
							</li>
						</ul>
						<ul class="navbar-nav">
							<li class="nav-item dropdown">
								<a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
									Messages
								</a>
								<div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
									<a class="dropdown-item" href="em_message.php?cible=camp">Message à son camp</a>
									<a class="dropdown-item" href="em_message.php?cible=compagnie">Message aux chefs de compagnie / section</a>
									<a class="dropdown-item" href="em_message.php?cible=em">Messages aux autres membres de l'EM</a>
								</div>
							</li>
						</ul>
					</div>
				</div>
			</nav>
		
			<p align="center"><input type="button" value="Fermer cette fenêtre" onclick="window.close()"></p>
			
			<h1>Cartes suivantes</h1>
			
			<div class="row">
				<div class="col-12">
					<div align='center'>
						<form method='POST' action='em_cartes_suivante.php'>
							<div class="form-group row">
								<div class="col-sm-6"><b><u>Choix de la carte suivante :</u></b></div>
								<div class="col-sm-6">
									<div class="custom-control custom-radio custom-control-inline">
									  <input type="radio" id="customRadioCarte2" name="choixCarteSuivante" class="custom-control-input" value="carte2" <?php if($carte_suivante_choisi == "carte2") { echo "checked"; } ?>>
									  <label class="custom-control-label" for="customRadioCarte2">Carte 2</label>
									</div>
									<div class="custom-control custom-radio custom-control-inline">
									  <input type="radio" id="customRadioCarte3" name="choixCarteSuivante" class="custom-control-input" value="carte3" <?php if($carte_suivante_choisi == "carte3") { echo "checked"; } ?>>
									  <label class="custom-control-label" for="customRadioCarte3">Carte 3</label>
									</div>
								</div>
							</div>
							<div class="form-group row">
								<div class="col-sm-12">
									<button type="submit" class="btn btn-primary">Valider</button>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
			
			<div class="row">
				<div class="col-12">
					<div align='center'>
						<h3>Carte 2</h3>
						<input type='text' id='idInput2' disabled /><br />
						<img id='carto2' src="carte/fond_carte2.png"><br /><br />
						
						<h3>Carte 3</h3>
						<input type='text' id='idInput3' disabled /><br />
						<img id='carto3' src="carte/fond_carte3.png"><br /><br />
					</div>
				</div>
			</div>
			
		</div>
			
		<!-- Optional JavaScript -->
		<!-- jQuery first, then Popper.js, then Bootstrap JS -->
		<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
		<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
		
		<script>		
		function addMouseChecker(imgId, inputId, valueToShow) {
			
			imgId 	= document.getElementById(imgId);
			inputId = document.getElementById(inputId);
			   
			if (imgId.addEventListener) {
				imgId.addEventListener('mousemove', function(e){checkMousePos(imgId, inputId, valueToShow, e);}, false);
			} else if (imgId.attachEvent) {
				imgId.attachEvent('onclick', function(e){checkMousePos(imgId, inputId, valueToShow, e);});
			}
		}
		
		function checkMousePos(imgId, inputId, valueToShow, e) {
			
			bounds=imgId.getBoundingClientRect();
			var left=bounds.left;
			var top=bounds.top;
			var x = event.pageX - left;
			var y = event.pageY - top;
			var cw=imgId.clientWidth
			var ch=imgId.clientHeight
			var iw=imgId.naturalWidth
			var ih=imgId.naturalHeight
			var px=x/cw*iw
			var py=y/ch*ih
			
			var pos = [];
			
			pos['x'] 	= Math.floor(x / 3);
			pos['y'] 	= Math.floor((ih - y) / 3);
			pos['xy'] 	= pos['x'] +','+ pos['y'];
		   
			inputId.value = pos[valueToShow];
		}
		</script>
	</body>
</html>


<?php
		}
		else {
			// Un joueur essaye d'acceder à la page sans être de l'état major
			$text_triche = "Tentative accés page etat major sans y avoir les droits";
			
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