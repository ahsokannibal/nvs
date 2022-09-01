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
							<a class="nav-link active" href="regles_caracs.php">Les caractéristiques</a>
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
							<a class="nav-link" href="regles_carte.php">La carte et les terrains</a>
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
						<li class="nav-item">
							<a href="../jeu/jouer.php"> <input type="button" value="Retour au jeu"> </a>
						</li>
					</ul>
				</div>
			</nav>
			
			<div class="row justify-content-center">
				<div class="col-12">
	
					<div align="center"><h2>Les caractéristiques</h2></div>
					
				</div>
			</div>
			
			<div class="row">
				<div class="col-12">

					<p class="mt-2">Chaque personnage a des caractéristiques propres : points de vie (PV), points de mouvements (PM), points d' actions (PA), récupération, perception, protection. <br />
					Ces caractéristiques peuvent être modifiées par des tares, des objets, ou de l'investissement d'expérience.</p>

					<p>Chaque personnage a ensuite deux compteurs d'évaluation (ou de comparaison) : points d'expérience (XP) et points de commandement (PC).<br />
					Enfin chaque personnage a trois compteurs de malus : malus de déplacement (malus_dep), malus défensif (malus_def) et malus de perception (malus_perc).</p>

					<h2>XP</h2>
					<p>L'expérience est la valeur image de votre expérience dans les combats. L'expérience est gagnée en frappant l'ennemi ou des animaux, en construisant des bâtiments, mais également en remplissant des missions et atteignant des objectifs que les officiers supérieurs vous auront assignés.</p>

					<h2>XPI</h2>
					<p>Les XPI permettent d'améliorer les caractéristiques de vos personnages. Ils sont le parallèle à votre expérience. Mais tandis que votre expérience est acquise, les points d'investissements peuvent être dépensés en améliorations. Si vous gagnez 20 XP , vous pourrez alors dépenser 20 XPI. <br />
					Lorsque vous êtes capturé, votre chef perd 5% de ses XPI tandis que vos grouillots perdent 40% de leurs XPI. Investissez les donc au bon moment !</p>
					
				
					<h2>PC</h2>
					<p>Les points de commandement sont les points qui vous permettront de monter en grade.<br />
					Plus votre grade est élevé, plus vous pouvez contrôler de troupes.<br />
					Vous gagnez au minimum 1 PC par tour.</p>
					
					<h2>PV</h2>
					<p>Les PV sont vos Points de Vie du personnage qui correspond à votre état de santé.<br />
					Les attaques de vos ennemis font baisser vos PV.<br />
					Le terrain peut également vous faire perdre des PV.<br />
					Quand vos PV tombent à zéro, le personnage n'est plus en état de combattre et être rapatrié dans un bâtiment (fort ou fortin), qu'on appelle aussi respawn.<br />
					Vous pouvez augmentez vos PV grâce à des actions comme la sieste, des soins d'un soigneur ou des objets comme du whisky ou des trousses de soins.</p>

					<h2>Récupération</h2>
					<p>Chaque tour, votre nombre de Points de Vie actuels est augmenté de cette valeur, jusqu'à ce que vous soyez à votre maximum.<br />
					Selon les tares dont le personnage souffre ou le terrain sur lequel il se trouve, sa récupération peut être négative. Il perdra alors des Points de Vie.</p>
					
					<h2>Perception</h2>
					<p>C'est la distance à laquelle vous voyez les ennemis dans le jeu.</p>	
					
					<h2>PM</h2>
					<p>Ceci représente votre capacité à vous déplacer. Selon les terrains, vos points baisseront différemment car le coût de déplacement varie selon les terrains fréquentés.</p>
					
					<h2>PA</h2>
					<p>Ceci représente votre capacité à effectuer des actions (Attaquer coûte un certain nombre de points d'actions dépendant de l'arme, tout comme construire ou réparer une barricade par exemple).</p>
					
					<h2>Attaque</h2>
					<p> Il existe deux types d'attaques, ayant chacune ses propres spécificités. L'attaque au corps à corps(att_cac) , et l'attaque à distance (att_dist). La capacité à toucher un adversaire se calcule en pourcentage. Chaque personnage dispose d'un % de chance de toucher son adversaire. Les valeurs de combat subissent des modifications suivant la technique de combat utilisée (distance ou corps à corps), mais aussi les armes que vous utilisez, et des bonus/malus de tous types. </p>

					<p>Exemple: Le défenseur aura de meilleures chances de ne pas se faire toucher en étant en forêt.</p>

					<p> Il est possible d'effectuer des coups critique selon votre score de touché :
						<ul>
							<li>Coup Critique Inversé (ou Échec Critique) : Si votre score de combat est supérieur ou égal à 98, vous venez de rater lamentablement votre attaque. Le défenseur gagne alors 2 points d'expérience, vous ne gagnez aucun point de commandement, aucun point d'expérience et vous subissez 1 malus de défense. </li>
							<li>Coup Critique : Si votre score de combat est inférieur ou égal à 2 , vous venez de réussir un coup critique. Vos dégâts seront multipliés par deux, vous gagnerez plus d'expérience et plus de points de commandement.</li>
						</ul>
					</p>

					<h2>Dégât</h2>
					<p>Il s'agit de la quantité de dommages que votre unité est capable d'infliger à son adversaire. La quantité de dégâts se compte en nombre de Dès (20D6 = 20 dès à 6 faces)</p>

					<h2>Protection</h2>
					<p>La valeur de protection est la résistance de votre unité. Plus votre protection est importante, plus les chances que votre adversaire a de vous infliger de gros dégâts est faible.<br />
					La protection effective se calcule ainsi : dégâts (en PV) = résultat du score de dégâts de l'arme - protection du défenseur.</p>
					
					<h2>Bonus et Malus</h2>
					<p>Vos personnages peuvent subir des bonus ou des malus pour diverses raisons. Ces bonus et malus proviennent d'armes, d'attaques subies (si vous êtes attaqué, vous avez des malus en défense), tares etc. etc.<br />
					Les malus non permanents se récupèrent ainsi : malus_def = -5/ tour.</p>
					
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
