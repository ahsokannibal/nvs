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
						<h2>Contraintes et rappel des règles de constructions</h2>
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
								<td>Fort</td><td align='center'>16</td><td align='center'>30</td><td>plus de 20 cases d'une gare. 40 cases d'un autre lieu de rapatriement. 2 cases d'un autre batiment.</td>
							</tr>
							<tr>
								<td>Fortin</td><td align='center'>10</td><td align='center'>30</td><td>plus de 20 cases d'une gare. 40 cases d'un autre lieu de rapatriement. 2 cases d'un autre batiment.</td>
							</tr>
							<tr>
								<td>Gare</td><td align='center'>6</td><td align='center'>30</td><td>plus de 40 cases d'une autre gare. 20 cases d'un lieu de rapatriement. 2 cases d'un autre batiment.</td>
							</tr>
							<tr>
								<td>Hôpital</td><td align='center'>3 + 1 soigneur</td><td align='center'>10</td><td>plus de 20 cases d'une gare ou d'un autre lieu de rapatriement. 2 cases d'un autre batiment. Moins de 40 cases d'une gare ou d'un autre lieu de rapatriement.</td>
							</tr>
							<tr>
								<td>Tour de visu</td><td align='center'>1</td><td align='center'>5</td><td>plus de 2 cases d'un autre batiment. 7 cases d'une autre tour.</td>
							</tr>
							<tr>
								<td>Barricades</td><td align='center'>0</td><td align='center'> - </td><td>plus de 2 cases d'une gare. 2 cases d'un lieu de rapatriement.</td>
							</tr>
							<tr>
								<td>Ponts</td><td align='center'>0</td><td align='center'> - </td><td>plus de 3 cases d'un autre batiment. 30 cases d'un autre pont. La construction doit démarrer de la terre ferme (pas de pont flottant). Les ponts ne peuvent pas faire plus de 2 cases de largeur.</td>
							</tr>
							<tr>
								<td>Rails</td><td align='center'>0</td><td align='center'> - </td><td>Il est possible pour le génie de construire des rails mais aussi de les détruire. <b><font color='red'>Attention</font> : il est interdit de détruire les rails du camp advserse, cet acte sera sévérement puni !</b></td>
							</tr>
						</table>
					</div>
				</div>
			</div>
			
			<br />
			
			<div class="row">
				<div class="col-12">
					<h2>Exemple contrainte largeur pont</h2>
					
					<img src='../images/regle_largeur_pont.png'>
					
					<p>
					P1 posséde bien une largeur de 2 cases max (2 cases en longueur : voir fleches), il est donc valide.<br />
					P2 possède une largeur supérieur à 2 cases (3 cases que ce soit en longueur ou en hauteur), il est donc invalide, il sera impossible de le construire.<br />
					P3 possède bien une largeur de 2 cases max (2 cases en hauteur : voir fleches), cependant, il sera impossible de construire les cases de pont pour traverser la rivière (car dans ce cas, il dépassera les 2 cases max).
					<br /><br />
					<b><font color='red'>Attention</font> : La construction d'un pont sur une rivière de 2 cases de hauteur ne doit pas dépasser 2 cases de largeur (le Y sur 3 cases est toléré et à l'appréciation des animateurs).
					De même pour les rivières de 2 cases de largeur, le pont ne doit pas dépasser 2 cases de hauteur. Les pontons du style P3 ne doivent pas être exagérés (3 cases de sont tolérés) et seront soumis aussi à l'appréciation des animateurs</b>
					</p>
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