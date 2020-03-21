<?php
session_start();
require_once("../fonctions.php");

$mysqli = db_connexion();

include ('../nb_online.php');

if (isset($_POST['choix_stat']) && trim($_POST['choix_stat']) != "") {
	$stat = $_POST['choix_stat'];
	
	if ($stat == "Bousculade") {
		$nom_stat = $stat;
	}
	else if ($stat == "Attaque 1") {
		$nom_stat = "Attaques au Sabre";
	}
	else if ($stat == "Attaque 2") {
		$nom_stat = "Attaques au Sabre lourd";
	}
	else if ($stat == "Attaque 3") {
		$nom_stat = "Attaques au Cailloux";
	}
	else if ($stat == "Attaque 4") {
		$nom_stat = "Attaques au Pistolet";
	}
	else if ($stat == "Attaque 5") {
		$nom_stat = "Attaques au Pistolet canon long";
	}
	else if ($stat == "Attaque 6") {
		$nom_stat = "Attaques à la baïonnette";
	}
	else if ($stat == "Attaque 7") {
		$nom_stat = "Attaques au Fusil";
	}
	else if ($stat == "Attaque 8") {
		$nom_stat = "Attaques au Fusil précision";
	}
	else if ($stat == "Attaque 9") {
		$nom_stat = "Attaques aux caninces (chien)";
	}
	else if ($stat == "Attaque 15") {
		$nom_stat = "Attaques au Magnum";
	}
	else {
		$nom_stat = "Inconnu";
	}
}
else {
	$stat = "Bousculade";
}

// calcul moyenne
$sql = "SELECT SUM(pourcentage) as somme_pourcentage, SUM(degats) as somme_degats, count(pourcentage) as nb_pourcentage, count(degats) as nb_degats FROM log WHERE type_action = '$stat'";
$res = $mysqli->query($sql);
$t = $res->fetch_assoc();

$somme_pourcentage 	= $t['somme_pourcentage'];
$nb_pourcentage		= $t['nb_pourcentage'];
$somme_degats	 	= $t['somme_degats'];
$nb_degats			= $t['nb_degats'];

if ($nb_pourcentage > 0) {
	$moyenne_pourcentage 	= $somme_pourcentage / $nb_pourcentage;
}
else {
	$moyenne_pourcentage 	= 0;
}

if ($nb_degats > 0) {
	$moyenne_degats			= $somme_degats / $nb_degats;
}
else {
	$moyenne_degats			= 0;
}

// Calcul nombre de réussite
$nb_reussite 		= 0;
$chance_reussite 	= 0;

if ($stat == "Bousculade") {
	
	$chance_reussite = 66;

	$sql = "SELECT pourcentage FROM log WHERE type_action = '$stat'";
	$res = $mysqli->query($sql);

	while ($t = $res->fetch_assoc()) {
		
		$pourcentage 	= $t['pourcentage'];
		
		if ($pourcentage < $chance_reussite) {
			$nb_reussite++;
		}
	}

	// calcul pourcentage reussite
	$pourcentage_reussite 	= ($nb_reussite / $nb_pourcentage) * 100;
}

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<title>Nord VS Sud - Classement</title>
		
		<!-- Required meta tags -->
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
		<link rel="stylesheet" href="https://drvic10k.github.io/bootstrap-sortable/Contents/bootstrap-sortable.css" />
		<link href="//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css" rel="stylesheet">
		
	</head>

	<body background="../images/background.jpg">
	
		<div class="container">
		
			<div class="row">
				<div class="col-12">

					<div align="center">
						<h2>Statistiques</h2>
					</div>
					
					<form method="post" action="statistiques.php">
						<u><b>Choix Statistique :</u></b>
						<select name="choix_stat" onchange="this.form.submit()">
							<option value='Bousculade' <?php if(isset($_POST["choix_stat"]) && $_POST["choix_stat"] == "Bousculade") echo " selected"; ?>>Les bousculades sur ennemi</option>
							<option value='Attaque 1' <?php if(isset($_POST["choix_stat"]) && $_POST["choix_stat"] == "Attaque 1") echo " selected"; ?>>Les attaques au sabre</option>
							<option value='Attaque 2' <?php if(isset($_POST["choix_stat"]) && $_POST["choix_stat"] == "Attaque 2") echo " selected"; ?>>Les attaques au sabre lourd</option>
							<option value='Attaque 3' <?php if(isset($_POST["choix_stat"]) && $_POST["choix_stat"] == "Attaque 3") echo " selected"; ?>>Les attaques au cailloux</option>
							<option value='Attaque 4' <?php if(isset($_POST["choix_stat"]) && $_POST["choix_stat"] == "Attaque 4") echo " selected"; ?>>Les attaques au pistolet</option>
							<option value='Attaque 5' <?php if(isset($_POST["choix_stat"]) && $_POST["choix_stat"] == "Attaque 5") echo " selected"; ?>>Les attaques au pistolet canon long</option>
							<option value='Attaque 6' <?php if(isset($_POST["choix_stat"]) && $_POST["choix_stat"] == "Attaque 6") echo " selected"; ?>>Les attaques à la baïonnette</option>
							<option value='Attaque 7' <?php if(isset($_POST["choix_stat"]) && $_POST["choix_stat"] == "Attaque 7") echo " selected"; ?>>Les attaques au fusil</option>
							<option value='Attaque 8' <?php if(isset($_POST["choix_stat"]) && $_POST["choix_stat"] == "Attaque 8") echo " selected"; ?>>Les attaques au fusil précision</option>
							<option value='Attaque 9' <?php if(isset($_POST["choix_stat"]) && $_POST["choix_stat"] == "Attaque 9") echo " selected"; ?>>Les attaques aux canines (chien)</option>
							<option value='Attaque 15' <?php if(isset($_POST["choix_stat"]) && $_POST["choix_stat"] == "Attaque 15") echo " selected"; ?>>Les attaques au magnum</option>
						</select>
					</form>
					
					<br />

					<center>
						<div id="table_bataillon" class="table-responsive">
							<table border="1" width='100%'>
								<tr>
									<th style='text-align:center'>Nombre de <?php echo $nom_stat; ?></th>
									<th style='text-align:center'>Moyenne des scores de touche de <?php echo $nom_stat; ?></th>
									<?php
									if ($stat == "Bousculade") {
									?>
									<th style='text-align:center'>Nombre de <?php echo $nom_stat; ?> réussies</th>
									<th style='text-align:center'>Pourcentage de réussite</th>
									<?php 
									}
									else {
									?>
									<th style='text-align:center'>Moyenne des dégâts</th>
									<?php 
									}
									?>
								</tr>
								<tr>
									<td align='center'><?php echo $nb_pourcentage; ?></td>
									<td align='center'><?php echo $moyenne_pourcentage; ?></td>
									<?php
									if ($stat == "Bousculade") {
									?>
									<td align='center'><?php echo $nb_reussite; ?></td>
									<td align='center'><?php echo $pourcentage_reussite."%"; ?></td>
									<?php 
									}
									else {
									?>
									<td align='center'><?php echo $moyenne_degats; ?></td>
									<?php 
									}
									?>
								</tr>
							</table>
						</div>
					</center>
				
				</div>
			</div>
		
		</div>
		
		<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
		<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
		<script src="https://drvic10k.github.io/bootstrap-sortable/Scripts/bootstrap-sortable.js"></script>
	
	</body>
</html>