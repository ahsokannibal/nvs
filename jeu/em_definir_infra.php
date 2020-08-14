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
			
			// Position fort déjà défini ?
			$sql = "SELECT position_x, position_y FROM em_position_infra_carte_suivante WHERE id_camp='$camp_em' AND id_batiment='9' AND carte='carte2'";
			$res = $mysqli->query($sql);
			$t = $res->fetch_assoc();
			
			$position_x_fort_carte2 = $t['position_x'];
			$position_y_fort_carte2 = $t['position_y'];
			
			$sql = "SELECT position_x, position_y FROM em_position_infra_carte_suivante WHERE id_camp='$camp_em' AND id_batiment='9' AND carte='carte3'";
			$res = $mysqli->query($sql);
			$t = $res->fetch_assoc();
			
			$position_x_fort_carte3 = $t['position_x'];
			$position_y_fort_carte3 = $t['position_y'];
			
			if (isset($_POST['placementXFortCarte2']) && trim($_POST['placementXFortCarte2']) != ""
					&& isset($_POST['placementYFortCarte2']) && trim($_POST['placementYFortCarte2']) != "") {
				
				$x_fort_carte2 = $_POST['placementXFortCarte2'];
				$y_fort_carte2 = $_POST['placementYFortCarte2'];
				
				$verif_x = preg_match("#^[0-9]*[0-9]$#i","$x_fort_carte2");
				$verif_y = preg_match("#^[0-9]*[0-9]$#i","$y_fort_carte2");
				
				if ($verif_x && $verif_y) {
					
					if (isset($position_x_fort_carte2) && $position_x_fort_carte2 != null && $position_x_fort_carte2 != "") {
						$sql = "UPDATE em_position_infra_carte_suivante SET position_x = '$x_fort_carte2', position_y='$y_fort_carte2' WHERE id_camp='$camp_em' AND carte='carte2' AND id_batiment='9'";
					}
					else {
						$sql = "INSERT INTO em_position_infra_carte_suivante (id_camp, carte, id_batiment, position_x, position_y) VALUES ('$camp_em', 'carte2', '9', '$x_fort_carte2', '$y_fort_carte2')";
					}
					$mysqli->query($sql);
					
					$position_x_fort_carte2 = $x_fort_carte2;
					$position_y_fort_carte2 = $y_fort_carte2;
				}
			}
			
			if (isset($_POST['placementXFortCarte3']) && trim($_POST['placementXFortCarte3']) != ""
					&& isset($_POST['placementYFortCarte3']) && trim($_POST['placementYFortCarte3']) != "") {
				
				$x_fort_carte3 = $_POST['placementXFortCarte3'];
				$y_fort_carte3 = $_POST['placementYFortCarte3'];
				
				$verif_x = preg_match("#^[0-9]*[0-9]$#i","$x_fort_carte3");
				$verif_y = preg_match("#^[0-9]*[0-9]$#i","$y_fort_carte3");
				
				if ($verif_x && $verif_y) {
					
					if (isset($position_x_fort_carte3) && $position_x_fort_carte3 != null && $position_x_fort_carte3 != "") {
						$sql = "UPDATE em_position_infra_carte_suivante SET position_x = '$x_fort_carte3', position_y='$y_fort_carte3' WHERE id_camp='$camp_em' AND carte='carte3' AND id_batiment='9'";
					}
					else {
						$sql = "INSERT INTO em_position_infra_carte_suivante (id_camp, carte, id_batiment, position_x, position_y) VALUES ('$camp_em', 'carte3', '9', '$x_fort_carte3', '$y_fort_carte3')";
					}
					$mysqli->query($sql);
					
					$position_x_fort_carte3 = $x_fort_carte3;
					$position_y_fort_carte3 = $y_fort_carte3;
				}
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
	<body onload="addMouseCheckerCarte2('carto2', 'idInput2', 'xy'); addMouseCheckerCarte3('carto3', 'idInput3', 'xy');">
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
			
			<h1>Placement des infrastructures sur les cartes suivante</h1>
			
			<div class="row">
				<div class="col-12">
					<div align='center'>
						<h3>Carte 2</h3>
						<form method='POST' action='em_definir_infra.php'>
							<div class="row">
								<label for="inputXFort">Placement du Fort</label>
								<div class="col">
									<input type="text" class="form-control" name='placementXFortCarte2' id="inputXFortCarte2" placeholder="position x" <?php if (isset($position_x_fort_carte2) && $position_x_fort_carte2 != null && $position_x_fort_carte2 != "") { echo "value=".$position_x_fort_carte2; }?>>
								</div>
								<div class="col">
									<input type="text" class="form-control" name='placementYFortCarte2' id="inputYFortCarte2" placeholder="position y" <?php if (isset($position_y_fort_carte2) && $position_y_fort_carte2 != null && $position_y_fort_carte2 != "") { echo "value=".$position_y_fort_carte2; }?>>
								</div>
								<div class="col">
									<input type='submit' value='ok' class='btn btn-primary'>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
			
			<div class="row">
				<div class="col-12">
					<div align='center'>
						<input type='text' id='idInput2' disabled /><br />
						<img id='carto2' src="carte/fond_carte2.png" onclick="addXYInputCarte2('carto2', event)"><br /><br />
					</div>
				</div>
			</div>
			
			<div class="row">
				<div class="col-12">
					<div align='center'>
						<h3>Carte 3</h3>
						<form method='POST' action='em_definir_infra.php'>
							<div class="row">
								<label for="inputXFort">Placement du Fort</label>
								<div class="col">
									<input type="text" class="form-control" name='placementXFortCarte3' id="inputXFortCarte3" placeholder="position x" <?php if (isset($position_x_fort_carte3) && $position_x_fort_carte3 != null && $position_x_fort_carte3 != "") { echo "value=".$position_x_fort_carte3; }?>>
								</div>
								<div class="col">
									<input type="text" class="form-control" name='placementYFortCarte3' id="inputYFortCarte3" placeholder="position y" <?php if (isset($position_y_fort_carte3) && $position_y_fort_carte3 != null && $position_y_fort_carte3 != "") { echo "value=".$position_y_fort_carte3; }?>>
								</div>
								<div class="col">
									<input type='submit' value='ok' class='btn btn-primary'>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
			
			<div class="row">
				<div class="col-12">
					<div align='center'>
						<input type='text' id='idInput3' disabled /><br />
						<img id='carto3' src="carte/fond_carte3.png" onclick="addXYInputCarte3('carto3', event)"><br /><br />
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
		function addMouseCheckerCarte2(imgId, inputId, valueToShow) {
			
			imgId 	= document.getElementById(imgId);
			inputId = document.getElementById(inputId);
			   
			if (imgId.addEventListener) {
				imgId.addEventListener('mousemove', function(e){checkMousePosCarte2(imgId, inputId, valueToShow, e);}, false);
			}
		}
		
		function addMouseCheckerCarte3(imgId, inputId, valueToShow) {
			
			imgId 	= document.getElementById(imgId);
			inputId = document.getElementById(inputId);
			   
			if (imgId.addEventListener) {
				imgId.addEventListener('mousemove', function(e){checkMousePosCarte3(imgId, inputId, valueToShow, e);}, false);
			}
		}
		
		function addXYInputCarte2(imgId, e) {
			
			imgId 	= document.getElementById(imgId);
			
			var ih=imgId.naturalHeight;
			
			var x 	= Math.floor((e.pageX - imgId.offsetLeft) / 3) - 127;
			var y 	= Math.floor((ih - (e.pageY - imgId.offsetTop)) / 3) + 89;
			
			document.getElementById('inputXFortCarte2').value = x;
			document.getElementById('inputYFortCarte2').value = y;
		}
		
		function addXYInputCarte3(imgId, e) {
			
			imgId 	= document.getElementById(imgId);
			
			var ih=imgId.naturalHeight;
			
			var x 	= Math.floor((e.pageX - imgId.offsetLeft) / 3) - 127;
			var y 	= Math.ceil((ih - (e.pageY - imgId.offsetTop)) / 3) + 334;
			
			document.getElementById('inputXFortCarte3').value = x;
			document.getElementById('inputYFortCarte3').value = y;
		}
		
		function checkMousePosCarte2(imgId, inputId, valueToShow, e) {
			
			var ih=imgId.naturalHeight;
			
			var pos = [];
			
			pos['x'] 	= Math.floor((e.pageX - imgId.offsetLeft) / 3) - 127;
			pos['y'] 	= Math.floor((ih - (e.pageY - imgId.offsetTop)) / 3) + 89;
			pos['xy'] 	= pos['x'] +','+ pos['y'];
		   
			inputId.value = pos[valueToShow];
		}
		
		function checkMousePosCarte3(imgId, inputId, valueToShow, e) {
			
			var ih=imgId.naturalHeight;
			
			var pos = [];
			
			pos['x'] 	= Math.floor((e.pageX - imgId.offsetLeft) / 3) - 127;
			pos['y'] 	= Math.ceil((ih - (e.pageY - imgId.offsetTop)) / 3) + 334;
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