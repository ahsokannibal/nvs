<?php
session_start();
require_once("../fonctions.php");

$mysqli = db_connexion();

include ('../nb_online.php');

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
				
					<p align="center"><input type="button" value="Fermer cette fenêtre" onclick="window.close();"></p>

					<div align="center">
						<h2>Contraintes de constructions</h2>
					</div>
				</div>
			</div>
			
			<div class="row">
				<div class="col-12" align="center">
					<div id="table_contraintes" class="table-responsive">
						<table border='1'>
							<tr>
								<th>Batiment</th><th>Nombre d'unités de génie civil (à 10 cases autour du point de construction)</th><th>Distance ennemis</th><th>Distance autre batiment</th>
							</tr>
							<tr>
								<td>Fort</td><td align='center'>16</td><td align='center'>50</td><td>20 cases d'une gare. 40 cases d'un autre lieu de rapatriement. 2 cases d'un autre batiment.</td>
							</tr>
							<tr>
								<td>Fortin</td><td align='center'>10</td><td align='center'>50</td><td>20 cases d'une gare. 40 cases d'un autre lieu de rapatriement. 2 cases d'un autre batiment.</td>
							</tr>
							<tr>
								<td>Gare</td><td align='center'>6</td><td align='center'>50</td><td>40 cases d'une autre gare. 20 cases d'un lieu de rapatriement. 2 cases d'un autre batiment.</td>
							</tr>
							<tr>
								<td>Hôpital</td><td align='center'>3</td><td align='center'>50</td><td>20 cases d'une gare. 40 cases d'un autre lieu de rapatriement. 2 cases d'un autre batiment.</td>
							</tr>
							<tr>
								<td>Tour de visu</td><td align='center'>1</td><td align='center'>20</td><td>2 cases d'un autre batiment.</td>
							</tr>
							<tr>
								<td>Barricades</td><td align='center'>1</td><td align='center'> - </td><td>2 cases d'une gare. 2 cases d'un lieu de rapatriement.</td>
							</tr>
						</table>
					</div>
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