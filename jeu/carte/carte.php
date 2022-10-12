<?php
session_start();

//Pas de session en cours, on redirige vers l'accueil
if (!isset($_SESSION["id_perso"])) {
	header("location:../../index.php");
}
	
$id = $_SESSION["id_perso"];

require_once "../../fonctions.php";

$mysqli = db_connexion();

$page_acces = 'carte.php';
	
// acces_log
$sql = "INSERT INTO acces_log (date_acces, id_perso, page) VALUES (NOW(), '$id', '$page_acces')";
$mysqli->query($sql);
	
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Nord VS Sud</title>
		
		<!-- Required meta tags -->
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		
		<!-- Bootstrap CSS -->
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css" rel="stylesheet">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
		<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.1/dist/leaflet.css" integrity="sha256-sA+zWATbFveLLNqWO2gtiw3HL/lh1giY/Inf1BJ0z14=" crossorigin="" />
		<style>
			#page {
					background-image: url("../../images/background-wood2.jpg");
					height: 100vh;
				}
			.form-check{
				color :white
			}
			h1{
				color:white
			}
			#wrapper {
				position: relative;
				border: 1px solid #9C9898;
				width: 100%;
				height: 100%;
			}
			/*@media screen and (min-width: 320px) and (max-width: 767px) and (orientation: portrait) {
				html {
					transform: rotate(-90deg);
					transform-origin: left top;
					width: 100vh;
					height: 100vw;
					overflow-x: hidden;
					position: absolute;
					top: 100%;
					left: 0;
				}
			}*/
			#map {
				width:100%;
				touch-action: none;
				/*width:690px;
				height : 690px;*/
			}
			
		</style>

	</head>
	
	<body id="page">
		
		<p align="center"><a href="../jouer.php"> <input type="button" value="Retour au jeu"> </a></p>
		<?php
			/*// Le perso appartient-il à une compagnie 
			$sql = "SELECT id_compagnie from perso_in_compagnie where id_perso='$id' AND (attenteValidation_compagnie='0' OR attenteValidation_compagnie='2')";
			$res = $mysqli->query($sql);
			$nb_compagnie = $res->num_rows;	

			//Le perso appartient-il au génie
			$sql = "SELECT genie FROM perso where id_perso='$id'";
			$result = $mysqli->query($sql);
			
			$perso = $result->fetch_array(MYSQLI_ASSOC);*/
			
		?>
		<div class="container">
			<div class="row">
				<div class="col d-flex justify-content-center">
					<h1>Carte Stratégique</h1>
				</div>
			</div>
			<div  class="row">
				<div  class="col-12 col-lg-10">
					<div id="carouselControls" class="carousel slide" data-bs-ride="carousel">
						<div class="carousel-inner">
							<button id="carousel-control-prev" class="carousel-control-prev" type="button" data-bs-target="#carouselControls" data-bs-slide="prev">
								<span class="carousel-control-prev-icon" aria-hidden="true"></span>
								<span class="visually-hidden">Previous</span>
							</button>
							<button id="carousel-control-next" class="carousel-control-next" type="button" data-bs-target="#carouselControls" data-bs-slide="next">
								<span class="carousel-control-next-icon" aria-hidden="true"></span>
								<span class="visually-hidden">Next</span>
							</button>
							<div class="carousel-item active">
								<div id="wrapper">
									<canvas id="map"></canvas>
									<!--<div id="carousel-caption" class="carousel-caption d-none d-md-block">
										<h5 id="carouselTitle"></h5>
									</div>-->
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col p-3">
					<form class="row">
						<div class="col">
							<div class="input-group date" id="datepicker">
								<input type="text" class="form-control" id="date"/>
								<span class="input-group-append">
								<span class="input-group-text bg-light d-block">
									<i class="fa fa-calendar"></i>
								</span>
								</span>
							</div>
						</div>
					</form>
					<div class="form-check">
						<input class="form-check-input" type="checkbox" value="" id="topographie" checked>
						<label class="form-check-label" for="topographie">
							Topographie
						</label>
					</div>
					<div class="form-check">
						<input class="form-check-input" type="checkbox" value="" id="brouillard" checked>
						<label class="form-check-label" for="brouillard">
							Brouillard
						</label>
					</div>
					<div class="form-check">
						<input class="form-check-input" type="checkbox" value="" id="joueurs" checked>
						<label class="form-check-label" for="joueurs">
							Joueurs
						</label>
					</div>
					<div class="form-check">
						<input class="form-check-input" type="checkbox" value="" id="batiments" checked>
						<label class="form-check-label" for="batiments">
							Batiments
						</label>
					</div>
					<?php 
						//if($perso["genie"] > 0){
							//require_once("contraintes_batiments.php");
						//}
					?>
					
					<div class="form-check">
						<input class="form-check-input" type="checkbox" value="" id="bataillon" checked>
						<label class="form-check-label" for="bataillon">
							Mon bataillon
						</label>
					</div>
					<div class="form-check">
						<input class="form-check-input" type="checkbox" value="" id="compagnie">
						<label class="form-check-label" for="compagnie">
							Ma compagnie
						</label>
					</div>

					
					<!-- TODO option réservée ? -->
					<!--<div class="form-check">
						<input class="form-check-input" type="checkbox" value="" id="zone_bloquage">
						<label class="form-check-label" for="zone_bloquage">
							Zone bloquage
						</label>
					</div>-->
				</div>
			</div>
		</div>
		<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/js/bootstrap.bundle.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
		
		<script src="carte.js"></script>
		<script src="Case.js"></script>
		
	</body>
</html>	
