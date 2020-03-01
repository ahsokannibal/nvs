<?php
session_start();
require_once("../fonctions.php");

$mysqli = db_connexion();

include ('../nb_online.php');

if (isset($_POST['choix_stat']) && trim($_POST['choix_stat']) != "") {
	$stat = $_POST['choix_stat'];
}
else {
	$stat = "Bousculade";
}

// calcul moyenne
$sql = "SELECT SUM(pourcentage) as somme_pourcentage, count(pourcentage) as nb_pourcentage FROM log WHERE type_action = '$stat'";
$res = $mysqli->query($sql);
$t = $res->fetch_assoc();

$somme_pourcentage 	= $t['somme_pourcentage'];
$nb_pourcentage		= $t['nb_pourcentage'];

$moyenne_pourcentage = $somme_pourcentage / $nb_pourcentage;

// Calcul nombre de réussite
$nb_reussite 		= 0;
$chance_reussite 	= 0;

$sql = "SELECT pourcentage FROM log WHERE type_action = '$stat'";
$res = $mysqli->query($sql);

while ($t = $res->fetch_assoc()) {
	
	$pourcentage = $t['pourcentage'];
	
	if ($stat == "Bousculade") {
		$chance_reussite = 66;
	}
	
	if ($pourcentage < $chance_reussite) {
		$nb_reussite++;
	}
}

// calcul pourcentage reussite
$pourcentage_reussite = ($nb_reussite / $nb_pourcentage) * 100

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
							<option value='Bousculade' <?php if(isset($_GET["choix_stat"]) && $_GET["choix_stat"] == "Bousculade") echo " selected"; ?>>Les bousculades sur ennemi</option>
						</select>
					</form>
					
					<br />

					<center>
						<div id="table_bataillon" class="table-responsive">
							<table border="1" width='100%'>
								<tr>
									<th style='text-align:center'>Nombre de <?php echo $stat; ?></th>
									<th style='text-align:center'>Moyenne des scores de <?php echo $stat; ?></th>
									<th style='text-align:center'>Nombre de <?php echo $stat; ?> réussies</th>
									<th style='text-align:center'>Pourcentage de réussite</th>
								</tr>
								<tr>
									<td align='center'><?php echo $nb_pourcentage; ?></td>
									<td align='center'><?php echo $moyenne_pourcentage; ?></td>
									<td align='center'><?php echo $nb_reussite; ?></td>
									<td align='center'><?php echo $pourcentage_reussite."%"; ?></td>
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