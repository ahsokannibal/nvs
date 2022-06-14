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
		
			<nav class="navbar navbar-expand-lg navbar-light bg-light">
				<a class="navbar-brand" href="#">Règles</a>
				<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
					<span class="navbar-toggler-icon"></span>
				</button>

				<div class="collapse navbar-collapse" id="navbarSupportedContent">
					<ul class="navbar-nav mr-auto">
						<li class="nav-item">
							<a class="nav-link" href="../index.php">Accueil</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" href="regles_introduction.php">Introduction</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" href="regles_caracs.php">Les caractéristiques</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" href="regles_armees.php">Les armées</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" href="regles_unites.php">Les unités</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" href="regles_objets.php">Les objets, armes et thunes</a>
						</li>
						<li class="nav-item">
							<a class="nav-link active" href="regles_carte.php">La carte et les terrains</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" href="regles_batiments.php">Les bâtiments et trains</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" href="regles_pnjs.php">Les PNJ</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" href="regles_action_spe.php">Les actions spéciales</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" href="regles_modalites_victoire.php">Les modalités de victoire</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" href="regles_conduite.php">Règles de conduite</a>
						</li>
					</ul>
				</div>
			</nav>
			
			<div class="row justify-content-center">
				<div class="col-12">
	
					<div align="center"><h2>La carte et les terrains</h2></div>
					
				</div>
			</div>
			
			<div class="row">
				<div class="col-12">
					<h2>La Carte</h2>
La Carte est le lieu des opérations. La carte est parsemée de couleurs qui correspondent à des terrains, mais aussi de petits carrés (les personnages, les PNJ et les barricades) et de gros carrés qui sont des bâtiments.<br /><br />
Les couleurs sont les suivantes :
					<ul>
						<li>En bleu, les Nordistes et bâtiments Nordistes</li>
						<li>En rouge, les Sudistes et bâtiments Sudistes</li>
						<li>En noir, les PNJs</li>
						<li>En gris, les rails</li>
					</ul>
Il est intéressant de noter que les unités combattantes situées en forêt sont invisibles sur la carte générale. Il s'agit là, d'un moyen d'effectuer des actions furtives. 
					
					<h2>Les Terrains</h2>
Il existe de nombreux terrains qui ont tous leurs spécificités. Certains terrains sont plus difficiles d'accès, d'autres sont particulièrement invivables ou sont limités quant aux possibilités de défenses offertes.<br />
Il y a plusieurs types de terrains différents, ayant tous : un coût de déplacement (PM ), un modificateur de récupération, un modificateur de perception, un modificateur de défense à distance, un modificateur de défense au corps à corps.<br />
<br />					
					<div id="table_terrain" class="table-responsive">
						<table border='1' align="center" width=100%>
							<tr>
								<th style="text-align:center">case</th><th style="text-align:center">nom</th><th style="text-align:center">coût PM</th><th style="text-align:center">Défense à distance (1)</th><th style="text-align:center">Défense au CàC</th><th style="text-align:center">Perception</th><th style="text-align:center">Récupération</th>
							</tr>
							<tr>
								<td align='center'><img src='../fond_carte/1.gif' /></td><td align='center'>Plaine</td><td align='center'>1</td><td align='center'> - </td><td align='center'> - </td><td align='center'> - </td><td align='center'> - </td>
							</tr>
							<tr>
								<td align='center'><img src='../fond_carte/2.gif' /></td><td align='center'>Coline</td><td align='center'>2</td><td align='center'>+10%</td><td align='center'>+10%</td><td align='center'>+1</td><td align='center'> - </td>
							</tr>
							<tr>
								<td align='center'><img src='../fond_carte/3.gif' /></td><td align='center'>Montagne</td><td align='center'>4</td><td align='center'>+20%</td><td align='center'>+20%</td><td align='center'>+2</td><td align='center'> - </td>
							</tr>
							<tr>
								<td align='center'><img src='../fond_carte/4.gif' /></td><td align='center'>Desert</td><td align='center'>1</td><td align='center'>-10%</td><td align='center'>-10%</td><td align='center'> - </td><td align='center'>-100</td>
							</tr>
							<tr>
								<td align='center'><img src='../fond_carte/6.gif' /></td><td align='center'>Marécage</td><td align='center'>2</td><td align='center'> - </td><td align='center'>-10%</td><td align='center'> - </td><td align='center'>-20</td>
							</tr>
							<tr>
								<td align='center'><img src='../fond_carte/7.gif' /></td><td align='center'>Forêt</td><td align='center'>2</td><td align='center'>+20%</td><td align='center'> - </td><td align='center'>-2</td><td align='center'> - </td>
							</tr>
							<tr>
								<td align='center'><img src='../fond_carte/8.gif' /></td><td align='center'>Eau / Rivière</td><td align='center'>4</td><td align='center'>+10%</td><td align='center'>+10%</td><td align='center'> - </td><td align='center'> - </td>
							</tr>
							<tr>
								<td align='center'><img src='../fond_carte/9.gif' /></td><td align='center'>Eau profonde</td><td align='center'>Infranchissable</td><td align='center'> - </td><td align='center'> - </td><td align='center'> - </td><td align='center'> - </td>
							</tr>
						</table>
					</div>
					
					<p><b>(1) Se référer à cette colonne lorsqu'on attaque avec une arme pour les attaques à distance (exemple : pistolet, fusil, etc..), même si l'attaque se fait au contact)</b></p>
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
