<?php
session_start();
include("functions_statistiques.php");

?>

<!DOCTYPE html">
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
		<div class="row py-2">
			<div class="col-md-6 py-1">
				<div >
					<h6>XP par type de perso</h6>
				</div>
				<div class="card">
					<div class="card-body">
						<canvas id="xpBarChart"></canvas>
					</div>
				</div>
			</div>
			<div class="col-md-6 py-1">
				<div>
					<h6>XP par grades</h6>
				</div>
				<div class="card">
					<div class="card-body">
						<canvas id="xpGradesChart"></canvas>
					</div>
				</div>
			</div>
		</div>
		<div class="row py-2">
			<div class="col-md-6 py-1">
				<div >
					<h6>Compagnies nord</h6>
				</div>
				<div class="card">
					<div class="card-body">
						<canvas id="nordCompaPieChart"></canvas>
					</div>
				</div>
			</div>
			<div class="col-md-6 py-1">
				<div>
					<h6>Compagnies sud</h6>
				</div>
				<div class="card">
					<div class="card-body">
						<canvas id="sudCompaPieChart"></canvas>
					</div>
				</div>
			</div>
		</div>
		<div class="row  my-5 white-bg">
			<div class="col">
				<div>
					<ul class="nav nav-tabs" role="tablist">
						<li class="nav-item">
							<a class="nav-link active" href="#tab-table1" data-toggle="tab" role="tab" aria-controls="home" aria-selected="true">Joueurs</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" href="#tab-table2" data-toggle="tab" role="tab" aria-controls="home" aria-selected="false">Armes</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" href="#tab-table3" data-toggle="tab" role="tab" aria-controls="home" aria-selected="false">Compagnies</a>
						</li>
					</ul>
					<div class="tab-content">
						<div class="tab-pane active" id="tab-table1">
						<table id="playersData" class="display nowrap" style="width:100%">
							<thead>
							<tr>
								<th>Matricule</th> 
								<th>Nom</th>
								<th>Type</th>
								<th>Grade</th>
								<th>Camp</th>
								<th>Bataillon</th>
								<th>XP</th>
								<th>Compagnie</th>
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
								<th>XP</th>
								<th>Compagnie</th>
							</tr>
							</tfoot>
						</table>
						</div>
						<div class="tab-pane" id="tab-table2">
							<table id="armesData" class="display nowrap" style="width:100%">
								<thead>
								<tr>
									<th>Arme</th> 
									<th>Nombre d'attaques</th>
									<th>Précision moyenne</th>
									<th>Dégat moyen</th>
									<th>Camp</th>
								</tr>
								</thead>
								<tfoot>
								<tr>
									<th>Arme</th> 
									<th>Nombre d'attaques</th>
									<th>Précision moyenne</th>
									<th>Dégat moyen</th>
									<th>Camp</th>
								</tr>
								</tfoot>
							</table>
						</div>
						<div class="tab-pane" id="tab-table3">
							<table id="compagniesData" class="display nowrap" style="width:100%">
								<thead>
								<tr>
									<th>Nom</th> 
									<th>Membres</th>
									<th>Camp</th>
								</tr>
								</thead>
								<tfoot>
								<tr>
									<th>Nom</th> 
									<th>Membres</th>
									<th>Camp</th>
								</tr>
								</tfoot>
							</table>
						</div>
					</div>
				</div>
				
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
	<script type="text/javascript" charset="utf8" src="graphs.js"></script>
	<script type="text/javascript" charset="utf8" src="datatables.js"></script>
	
	</body>
</html>
