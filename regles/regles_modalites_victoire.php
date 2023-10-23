<html>
	<head>
		<title>Nord VS Sud</title>
		
		<!-- Required meta tags -->
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		
		<!-- Bootstrap CSS -->
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
	</head>

	<body style="background-color:grey;">

		<div class="container-fluid">

			<?php require 'regles_header.php' ?>		
			
			<div class="row justify-content-center">
				<div class="col-12">
	
					<div align="center"><h2>Les modalités de victoire</h2></div>
					
				</div>
			</div>
			
			<div class="row">
				<div class="col-12">
					<p>Désormais vous aurez différents types de conditions de victoire. Chaque mission ou conditions de victoire remplie et réalisée permettra d’engranger des points de victoire, PV.<br />
					Pour gagner une carte, chaque camp aura pour objectif final de gagner en premier 1200 PV.</p>

					<p><a class='btn btn-primary' href='../jeu/classement.php?stats=ok'>Les points de victoire sont visibles ici</a></p>

					<p>Les conditions de victoire classée par type sont : </p>

					<h3>CONSTRUCTION</h3>
					<p>Vous devrez en 3 mois construire 3 gares et 2 fortins. Tous les bâtiments construit en plus ne donneront pas de PV supplémentaires.<br/>
					<ul>
						<li>Gain par gare : 25PV par gare (pour max 3 gares)</li>
						<li>Gain par fortin : 50PV par camp</li>
					</ul>

					Attention après la limite de 3 mois, il ne sera plus possible de gagner de PV de construction
					</p>

					<h3>Mission Bonus CONSTRUCTION </h3>
					<p>Suivant la localisation de vos constructions, un bonus pourra être appliqué au gain de construction. Les détails des localisations vous seront données ingame.</p>
					<p>Bonus bâtiment 1 : 25PV <br />
					Bonus bâtiment 2 : 50PV </p>
					<p>Uniquement valable dans les délais des 3 premiers mois.</p>

					<h3>POINTS STRATEGIQUES</h3>
					<p>5 zones seront réparties sur la carte, elles seront matérialisées par un bâtiment neutre que vous découvrirez. Lors de la capture de ce bâtiment par un camp, elle déclenchera un compteur de gain de PV.<br /> 
					Le gain de PV sera calculé selon la différence du nombre de zones contrôlées par les camps et sera de 1 PV/jour de contrôle. Donc plus vous en contrôlerez plus vous vous assurerez un gain régulier de PV.<br />
					Les gains sont déclenchés lors de la capture du premier bâtiment.</p>

					<p>Ex : Si le nord possède 2 zones et que le Sud en possède 3, alors le gain de PV est calculé selon cette soustraction : 3-2 = 1 PV/ jour de contrôle.<br />  
					Si le sud ne possède pas de zone mais que le nord en possède 3 alors le gain sera de : 
					3-0= 3 PV/jour de contrôle.
					</p>

					<h3>DESTRUCTION / CAPTURE</h3>
					<p>La prise ou la destruction de bâtiment restera une des conditions majeures du jeu.</p>
					<ul>
						<li>Gain pour une gare : 50PV</li>
						<li>Gain pour un fortin : 100PV</li>
						<li>Gain pour le fort : 500PV</li>
					</ul>

					<h3>MISSION SPECIALE</h3>
					<p>L’équipe de Maître de jeu et d’animation, pourront selon les cartes mettre en place des objectifs supplémentaires. Dans ce cas les conditions de réussite vous seront exposées ingame et sur les Discord.</p>

					<p>Elles pourront être « de camp » mais aussi individuelles.</p>

					<p>Les gains seront également diffusés lors de la présentation de cette mission.</p>
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
