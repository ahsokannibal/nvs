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
							<a class="nav-link" href="regles_armees.php">Les Armées</a>
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
							<a class="nav-link" href="regles_batiments.php">Les Bâtiments et trains</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" href="regles_pnjs.php">Les PNJ</a>
						</li>
						<li class="nav-item">
							<a class="nav-link active" href="regles_action_spe.php">Les actions spéciales</a>
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
	
					<div align="center"><h2>Les actions spéciales</h2></div>
					
				</div>
			</div>
			
			<div class="row">
				<div class="col-12">
					<div id="bousculade">
					<h2>La bousculade</h2>
La bousculade utilise 3 PA plus les PM de mouvement du terrain où se trouvait le personnage que vous bousculez (2 PM si celui que vous bousculez se trouvait dans une forêt, 4 PM si dans la montagne, etc..).<br />
Le bousculé recevra un malus de déplacement correspondant à la valeur du terrain sur laquel il est bousculé. S'il est à 0 PM, le malus se reportera quand même sur le tour suivant <i>(ceci ne s'applique pas pour les bousculades entre personnages du même camp)</i>.<br />
<b><u>Attention ! Les chances de réussir une bousculade sur un ennemi sont de 66% !</u></b><br /> 
<b><u>Attention ! Tout le monde ne peut pas bousculer n'importe qui :</u></b> 
					<ul>
						<li>Une infanterie peut bousculer une infanterie, un soigneur et pousser une artillerie.</li>
						<li>Un soigneur ne peut pousser qu'une infanterie ou un autre soigneur.</li>
						<li>Une cavalerie peut bousculer une infanterie ou une cavalerie.</li>
						<li>Un canon peut bousculer une infanterie ou un soigneur.</li>
					</ul>
<b>Les bousculades sur les unités alliés leur coûtent 1 PA à chaque fois mais ne provoque pas de malus en PM.</b><br />
Pour bousculer une unité, il vous suffit de vous déplacer sur elle (avec les flèches seulement pour l'instant) et si les conditions sont réuni, alors la bousculade se fera automatiquement.
<br /><br />
					</div>
					<div id="charge">
					<h2>Charger</h2>
La charge est une option de combat disponible uniquement pour les cavaleries. Celles ci s'élancent dans la plaine et vont enfoncer les lignes adverses infligeant par la même occasion de terribles dégâts.<br />
<b>Pour charger, il faut avoir tous ses PA et au moins 4 PM.</b><br />
La charge s'effectue sur une distance max de 5 cases et avec <b>3 cases d'élan minimum</b>.<br />
Une fois au contact et dans la limite de vos PA, vos attaques au corps-à-corps seront automatiquement effectuées avec un bonus de +30 dégâts pour la première attaque, de +20 dégâts pour la seconde, de +10 dégât pour la troisième et de +0 pour la quatrième et les suivantes.<br />
A la fin de la charge, les PA non utilisés pour les attaques ne sont pas supprimés (exemple : un cavalier avec 10PA qui charge au sabre, fera 2 attaques à 4PA et il lui restera 2 PA à la fin de la charge).<br />
Une cavalerie qui charge un bâtiment perd 40 PV et ne fait aucun dégât.<br />
Une cavalerie ne peut charger qu'en plaine ou dans les steppes, et AUCUN obstacle (les routes et rails ne sont pas considéré comme un obstacle) ne doit se trouver sur sa route, la cible doit aussi se trouver en plaine ou sur une steppe.<br />
Lors d'une charge complète de cavalerie, si toutes les attaques réussissent, un bonus de 1PC sera accordé.
<br /><br />
					</div>
					<div id="sieste">
					<h2>La sieste</h2>
Un personnage blessé peut décider d'aller se reposer. Cette action n'est disponible que lorsque vous avez tous vos PA et tous vos PM. En échange de toutes ces caractéristiques, votre personnage gagne immédiatement un nombre de PV égal au double de votre récupération.<br />
<b>Attention, la sieste prend en compte votre récupération, et donc ne prend pas en compte les effets des objets de soins.</b>
<br /><br />
					</div>
					<div id="barricader">
					<h2>Barricader</h2>
Les unités d'infanterie peuvent mettre en place des barricades qui sont des structures défensives puissantes et capables d'empêcher les cavaleries de charger.<br />
Pour ce faire, elles doivent disposer de tous leurs PA, car la construction d'une barricade coûte 10 PA et ne peut être effectuée que sur une case "plaine".<br />
Les barricades apparaissent sur la carte avec 25pv/250. Il est possible de les réparer pour 5PA. 
<br /><br />
					</div>
					<div id="saboter">
					<h2>Saboter</h2>
Lorsqu'on se trouve à proximité d'une case de pont, une action "Saboter" apparait (accéssible pour toutes les unités).<br />
Pour 10PA, cette action, ayant 80% de chance de réussite, sabotera le pont pour entre 50 et 200PV.<br />
Les unités du génie étant plus efficaces pour saboter un pont, elles feront le double de dégâts ! 
<br /><br />
					</div>
					<div id="reparer">
					<h2>Réparer</h2>
Lorsqu'on se trouve à proximité d'un bâtiment ou d'un pont, une action "Réparer" apparait (accéssible pour toutes les unités).<br />
Pour 5PA, cette action, ayant 100% de chance de réussite, réparera le bâtiment ou le pont pour entre 20 et 120PV.<br />
Les unités du génie étant plus efficaces pour réparer un bâtiment ou un pont, les points de réparation seront doublés ! 
<br /><br />
					</div>
					<div id="marche">
					<h2>La marche forcée</h2>
Les infanteries, guerriers et guerriers du feu peuvent à chaque tour effectuer une marche forcée, leur permettant au prix d'un certain effort de parcourir une plus grande distance.<br />
Cette action coûte 4 PA et 10 PV, et vous rajoute 1 PM.<br /><br />
<b>Il n'est pas possible de faire des marches forcées dans les bâtiments (ou grottes, ou stèles).</b>				
<br /><br />
					</div>
					<div id="don">
					<h2>Donner</h2>
Dans Nord versus Sud, il est possible de donner un objet, une arme ou des thunes a un autre personnage.<br />
Chaque type de chose donnée obéit à des règles spécifiques.<br />
On peut donner une arme ou un objet directement à un personnage de son camp.
<br /><br />
					</div>
					<div id="capture">
					<h2>Capturer un personnage</h2>	
Il est possible de capturer un perso sans pour autant baisser ses PV à 0. Il existe 2 manières de le faire :<br />
<ul>
	<li><b>De façon purement RP</b> : un des persos négocie la réddition d'un ennemi. Si durant les échanges, l'ennemi accepte la réddition, celui qui a négocié la capture doit alors avertir l'animation, preuves à l'appui, afin que l'animation valide ou non cette capture. Dans ce cas, le perso capturé n'a aucune perte d'XP/PI, uniquement une perte classique de PC et une ligne spéciale apparaitra dans le CV pour signifier la capture RP. Pour celui ayant effectué la capture, il gagnera 4PC pour sa capture ainsi qu'une ligne spéciale dans son CV pour signifier la capture RP. <b>Le perso qui accepte de se faire capturer doit arrêter toute action (attaques ou soins) hors déplacements jusqu'à ce que l'animation valide ou refuse la capture !</b></li>
	<li><b>Par un encerclement pendant 12h</b> : c'est une capture "forcée", un des persos qui effectue l'encerclement doit penser à prendre un screen de l'encerclement au début (avec l'heure bien visible) ainsi qu'un screen <b>12h</b> après (toujours avec l'heure bien visible) et envoyer ça à l'animation pour validation de la capture par encerclement. Pour que la capure soit valide, il ne faut pas que celui qui se fait encerclé ait réussi à s'échapper durant ces 12h bien évidemment. Le capturé subit les malus d'une capture classique hors Thunes (en PI/PC) ainsi qu'un rappatriement classique et une ligne spéciale dans le CV pour signifier sa capture par encerclement. Ceux qui ont participés à la capture ne gagnent pas d'XP/PI/PC ni de ligne dans le CV mais ont pu conserver leurs PA pour d'autres actions. <b><font color='red'>Attention : </font>Un perso n'a le droit de participer qu'à une seule capture par encerclement par tour de jeu !</b></li>
</ul>
<p>Les demandes de capture (RP ou par encerclement) doivent obligatoirement passer par l'outil prévu à cet effet, sans quoi les animateurs ne pourront pas valider ces captures.</p>
<br />
					</div>
					<div id="perm">
					<h2>Partir en permission</h2>	
lorsque vous êtes amenés à ne plus pouvoir vous connecter au jeu pendant un certain temps, vous pouvez partir en permission. Vos personnages seront alors retirés de la carte à minuit 3 jours après votre demande et ne pourront plus faire l'objet d'attaques d'aucune sorte.<br />
Ils seront entièrement gelés et reviendront quand vous l'aurez décidé dans un Fort, Fortin ou Gare (conditions de rapatriement identiques à un rapatriement normal). La durée minimum de la permission est de 5 jours.<br />
Les personnages en permission depuis plus de 30 jours sont définitivement effacés (vous recevrez un mail 10 jours avant que cela n'arrive afin de vous avertir).<br />
<i>Vous contrôlez normalement votre personnage avant son départ prévu en permission.</i>
<br /><br />
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
