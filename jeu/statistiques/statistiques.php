<?php
session_start();
require_once("../../fonctions.php");
include("functions_statistiques.php");

$mysqli = db_connexion();

include ('../../nb_online.php');

$stat=1;

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
		<title>Nord VS Sud - Statistiques</title>
		
		<!-- Required meta tags -->
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.20/css/jquery.dataTables.css">
		
		<link rel="stylesheet" href="css/style.css" />
	</head>

	
	<body>
		<div class="container">
		<div class="row my-3">
			<div class="col">
				<h4>Répartition des joueurs actifs lors des 6 derniers jours</h4>
			</div>
		</div>
		<div class="row py-2">
			<div class="col-md-6 py-1">
				<div>
					<h6>Répartition des personnages</h6>
				</div>
				<div class="card">
					<div class="card-body">
						<canvas id="playersPieChart"></canvas>
					</div>
				</div>
			</div>
			<div class="col-md-6 py-1">
				<div>
					<h6>Disparité des grouillots</h6>
				</div>
				<div class="card">
					<div class="card-body">
						<canvas id="playersGrouillot"></canvas>
					</div>
				</div>
			</div>
		</div>
		<div class="row py-2">
			<div class="col-md-6 py-1">
				<div >
					<h6>Répartition des points de grouillots</h6>
				</div>
				<div class="card">
					<div class="card-body">
						<canvas id="pgPieChart"></canvas>
					</div>
				</div>
			</div>
			<div class="col-md-6 py-1">
				<div>
					<h6>Disparité des grades</h6>
				</div>
				<div class="card">
					<div class="card-body">
						<canvas id="gradesChart"></canvas>
					</div>
				</div>
			</div>
		</div>
		<!--  <div class="row py-2">
			<div class="col-md-12 py-1">
				<div class="card">
					<div class="card-body">
						<canvas id="playersGrouillot"></canvas>
					</div>
				</div>
			</div>
		</div>-->
		<div class="row  my-5 white-bg">
			<div class="col">
				<table id="playersData" class="display nowrap" style="width:100%">
					<thead>
					<tr>
						<th>Matricule</th> 
						<th>Nom</th>
						<th>Type</th>
						<th>Grade</th>
						<th>Camp</th>
						<th>Bataillon</th>
					</tr>
					</thead>
					<tfoot>
					<tr>
						<th>Matricule</th> 
						<th>Nom</th>
						<th>Type</th>
						<th>Grade</th>
						<th>Camp</th>
						<th>Bataillon</th>
					</tr>
					</tfoot>
				</table>
			</div>
		</div>
	</div>
	<script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
	<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
	<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.js"></script>
	<script type="text/javascript" charset="utf8" src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.js"></script>
	<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/responsive/2.2.4/js/dataTables.responsive.min.js"></script>
	<script type="text/javascript" charset="utf8" src="joueurs.js"></script>

	
	</body>
</html>