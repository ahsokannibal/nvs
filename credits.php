<?php
@session_start();

require_once("fonctions.php");

$mysqli = db_connexion();

?>
<html>
	<head>
		<title>Nord VS Sud</title>
		
		<!-- Required meta tags -->
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		
		<!-- Bootstrap CSS -->
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
	</head>

	<body background='./images/background_html.jpg'>

		<div class="container">
			<div class="row">
				<div class="col-12">

					<div align="center" style="color: azure;">
						<h2>Crédits</h2>
					</div>
					
					<div style="color: azure;">
						<h3>Développeur / Animateur principal</h3><br />
						<b>Romain PERRUCHON</b> (Admin sur le forum)
					</div>
					
					<br /><br />
					
					<div style="color: azure;">
						<h3>Remerciements</h3>
						<b>$kulL</b>, <b>James Winter</b>, <b>Augustus Winter</b>, <b>Geoff McDubh</b>, <b>Charly Cœur</b>, <b>Martin Luther King</b>, <b>Jedd Elzey</b>, <b>Furie</b> (pour leur implication et leur aide durant l'Alpha)<br /><br />
						<b>Robert E. Lee</b> (pour son aide graphique)						
					</div>
					
					<br /><br />
					
					<div style="color: azure;">
						<h3>D'après une idée originale de </h3>
						<b>GrOOn</b>, <b>Keldrilh</b> (Créateurs du jeu original Nord versus Sud)					
					</div>
					
					<br /><br />
					
					<center><a class='btn btn-primary' href='index.php'>Retour</a></center>
					
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