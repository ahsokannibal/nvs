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
							<a class="nav-link" href="regles_carte.php">La carte et les terrains</a>
						</li>
						<li class="nav-item">
							<a class="nav-link active" href="regles_batiments.php">Les bâtiments et trains</a>
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
	
					<div align="center"><h2>Les bâtiments et trains</h2></div>
					
				</div>
			</div>
			
			<div class="row">
				<div class="col-12">
					<h2>Les bâtiments</h2>
					
					<div id="table_terrain" class="table-responsive">
						<table border='1' align="center" width=100%>
							<tr>
								<th style="text-align:center">case</th>
								<th style="text-align:center">nom</th>
								<th style="text-align:center">taille</th>
								<th style="text-align:center">PV</th>
								<th style="text-align:center">Bonus Récupération</th>
								<th style="text-align:center">Bonus Perception</th>
								<th style="text-align:center">Bonus d'attaque Dist</th>
								<th style="text-align:center">Bonus de défense CàC</th>
								<th style="text-align:center">Bonus de défense Dist</th>
								<th style="text-align:center">Commentaire</th>
							</tr>
							<tr>
								<td align='center'><img src='../images_perso/b9b.png' alt='fort'><img src='../images_perso/b9r.png' alt='fort'></td>
								<td align='center'>Fort</td>
								<td align='center' style="white-space:nowrap">25 case<br />5X5</td>
								<td align='center'>10000</td>
								<td align='center'>+20</td>
								<td align='center'>-1</td>
								<td align='center'>-</td>
								<td align='center'>-</td>
								<td align='center'>+25%</td>
								<td>Le Fort est un bâtiment à défendre coute que coute. Il peut servir de lieu de rapatriement après capture. Il dispose de 6 canons de défense qui vont attaquer tout ennemi qui se rapproche de trop prés. Dans ces bâtiments, vous trouverez aussi des boutiques pour acheter armes et objets. Ces bâtiment permettent aussi aux chefs d'engager de nouvelles unités s'ils ont les PG (Points de Grouillot) nécessaire. Lorsqu'un Fort est construit, il possédera entre 5000 et 10000 PV.</td>
							</tr>
							<tr>
								<td align='center'><img src='../images_perso/b8b.png' alt='fortin'><img src='../images_perso/b8r.png' alt='fortin'></td>
								<td align='center'>Fortin</td>
								<td align='center' style="white-space:nowrap">9 cases<br />3X3</td>
								<td align='center'>6000</td>
								<td align='center'>+10</td>
								<td align='center'>-1</td>
								<td align='center'>-</td>
								<td align='center'>-</td>
								<td align='center'>+25%</td>
								<td>Le Fortin est le petit frère du Fort. Il est placé afin de défendre une partie de la carte. Il peut servir de lieu de rapatriement après capture. Il dispose de 4 canons de défense qui vont attaquer tout ennemi qui se rapproche de trop prés. Dans ces bâtiments, vous trouverez aussi des boutiques pour acheter armes et objets. Ces bâtiment permettent aussi aux chefs d'engager de nouvelles unités s'ils ont les PG (Points de Grouillot) nécessaire. Lorsqu'un Fortin est construit, il possédera entre 300 et 600 PV.</td>
							</tr>
							<tr>
								<td align='center'><img src='../images_perso/b11b.png' alt='gare'></td>
								<td align='center'>Gare</td>
								<td align='center' style="white-space:nowrap">9 cases<br />3X3</td>
								<td align='center'>5000</td>
								<td align='center'>-</td>
								<td align='center'>-1</td>
								<td align='center'>-25%</td>
								<td align='center'>-</td>
								<td align='center'>+25%</td>
								<td>Une Gare permet de se rendre à une autre Gare en achetant un ticket et en prenant le Train. Cela permet de se rendre plus rapidement à différents endroits de la carte. Une gare devient inactive (les trains ne vont plus vers cette gare) si elle est en dessous de 50% de ses PV. Une gare ne peut être construite qu'à proximité de rails (le centre doit être à 2 cases d'un rail pour le bâtiment soit à proximité de rails). Lorsqu'un gare est construite, elle possédera entre 250 et 500 PV.</td>
							</tr>
							<tr>
								<td align='center'><img src='../images_perso/b12b.gif' alt='pont'></td>
								<td align='center'>Train</td>
								<td align='center'>1 case</td>
								<td align='center'>2500</td>
								<td align='center'>-</td>
								<td align='center'>-1</td>
								<td align='center'>-30%</td>
								<td align='center'>+25%</td>
								<td align='center'>+25%</td>
								<td>Les trains permettent de voyager d'une gare à une autre en se déplaçant de 10PM par heure. un Train ,lorsqu'il est livré, possède la totalité de ses PV. Si un Train est abimé en arrivant dans une Gare, il sera réparé.</td>
							</tr>
							<tr>
								<td align='center'><img src='../images_perso/b7b.png' alt='hopital'><img src='../images_perso/b7r.png' alt='hopital'></td>
								<td align='center'>Hopital</td>
								<td align='center'>1 case</td>
								<td align='center'>1000</td>
								<td align='center'>-</td>
								<td align='center'>-2</td>
								<td align='center'>-25%</td>
								<td align='center'>-</td>
								<td align='center'>+15%</td>
								<td>L'Hopital de campagne est un bâtiment construit afin de revenir au front le plus rapidement possible. Il peut servir de lieu de rapatriement après capture. Aucun respawn aléatoire ne peut se faire dans ces bâtiments. Il n'est pas possible de capturer un Hopital. Dans ces bâtiments, vous trouverez aussi des boutiques pour acheter des objets de soin. lorsqu'un Hôpital est construit, il possédera entre 100 et 200 PV.</td>
							</tr>
							<tr>
								<td align='center'><img src='../images_perso/b2b.png' alt='tour de guet'></td>
								<td align='center'>Tour de guet</td>
								<td align='center'>1 case</td>
								<td align='center'>250</td>
								<td align='center'>-</td>
								<td align='center'>+5</td>
								<td align='center'>-</td>
								<td align='center'>-</td>
								<td align='center'>+10%</td>
								<td>Il s'agit d'une tour faiblement défendue, qui permet à la personne à l'intérieur d'augmenter sa perception. Elles ne peuvent être construites qu'en plaine, seulement par les troupes du Génie. Lorsqu'un Tour de guêt est construite, elle possédera entre 50 et 100 PV.</td>
							</tr>
							<tr>
								<td align='center'><img src='../images_perso/b1b.png' alt='barricade'></td>
								<td align='center'>Barricade</td>
								<td align='center'>1 case</td>
								<td align='center'>250</td>
								<td align='center'>-</td>
								<td align='center'>-</td>
								<td align='center'>-</td>
								<td align='center'>-</td>
								<td align='center'>-</td>
								<td>Il s'agit d'une espèce de mur de protection permettant de défendre une ligne de front. La barricade suit ces règles : on la construit pour 10PA, elle possède 250 PV au maximum et un personnage ne peut la franchir tant qu'elle n'est pas détruite. Elles ne peuvent être construites qu'en plaine par des infanteries. Lorsqu'une Barricade est construite, elle possédera entre 15 et 30 PV.</td>
							</tr>
							<tr>
								<td align='center'><img src='../images_perso/b5b.png' alt='pont'></td>
								<td align='center'>Pont</td>
								<td align='center'>1 case</td>
								<td align='center'>750</td>
								<td align='center'>-</td>
								<td align='center'>-</td>
								<td align='center'>-</td>
								<td align='center'>-10%</td>
								<td align='center'>-10%</td>
								<td>Le pont peut être posé sur les case d'eau et d'eau profonde. Il permet aux persos de traverser l'eau pour le cout d'un PM au lieu de 4 ainsi que de traverser l'eau profonde qui est infranchissable sans pont. C'est donc un outil stratégique très important à ne pas négliger ! Les ponts peuvent être sabotés par les persos, il suffit de se trouver à côté d'un pont afin d'avoir la possibilité de le <a href='regles_action_spe.php'>saboter</a> comme de le <a href='regles_action_spe.php'>réparer</a>. On ne peut peut pour l'instant pas saboter un pont avec un perso dessus. lorsqu'un Pont est construit, il possédera entre 50 et 100 PV.</td>
							</tr>
							<tr>
								<td align='center'><img src='../fond_carte/rail.gif' alt='pont'></td>
								<td align='center'>Rail</td>
								<td align='center'>1 case</td>
								<td align='center'>-</td>
								<td align='center'>-</td>
								<td align='center'>-</td>
								<td align='center'>-</td>
								<td align='center'>-</td>
								<td align='center'>-</td>
								<td>Il est possible pour le génie de construire des rails mais aussi de les détruire. <b><font color='red'>Attention</font> : il est interdit de détruire les rails du camp advserse, cet acte sera sévérement puni !</b></td>
							</tr>
						</table>
					</div>
					
					<br />
					
					<h4>Choix d'un bâtiment pour le rapatriement</h4>
					
					<p>Les différents camps peuvent choisir un bâtiment de rapatriement favori.<br />
					Il y a 3 types de bâtiment qui peuvent servir de respawn : Les Hôpitaux, les Fortins et les Forts.<br />
					Vous devez en choisir un seul de chaque type</p>
					
					<p>En cas de capture, vous serez rapatrié dans cet ordre :</p>
					<ul>
						<li>Hôpital choisi (si aucun choisi, la priorité ira au Fortin choisi, puis au Fort choisi)</li>
						<li>Fortin choisi</li>
						<li>Fort choisi</li>
						<li>Respawn aléatoire dans un bâtiment</li>
					</ul>
					
					<p>Un personnage ne peut pas être rapatrié dans un bâtiment si ce dernier est en état de siège (quelquesoit le bâtiment) ou s'il a été capturé à moins de 20 cases strictement (une capture à 20 cases pile du centre du bâtiment de rapatriement permet d'âtre rappatrié dans le bâtiment) de ce dernier (Hors hôpital).</p>
					
					<p>Pour définir vos choix de rapatriement, allez dans votre profil et cliquez sur le bouton "Choix des rapatriements".</p>
					
					<p><b>Attention</b>, cette manipulation devra être faite par chaque personnage et grouillot.</p>
					
					<h4>Etat de siège d'un bâtiment</h4>
					
					<p>Un bâtiment est considéré en état de siège s'il descend en dessous de 90% de ses PV maximum ou qu'il y a 10 persos ennemi à moins de 15 cases (15eme case inclus).<br />
					Cet état a pour effet de bloquer immédiatement les rapatriements, incorportations de nouveaux sur ce bâtiment.<br />
					Le recrutement de grouillot se trouve aussi bloqué pour ce bâtiment.</p>
					
					<p>La réactivation du respawn, des incorporations et recrutements est immédiat lorsque les conditions de siège ne sont plus remplies.</p>
					
					<p><b>Attention</b>, de graves sanctions pourront être prises contre les joueurs qui attaqueront leur bâtiment pour abuser de cette règle.</p>
					
					<p><b>Attention</b>, quand un bâtiment de votre camp est encerclé par au moins 10 ennemis à 15 cases et qu'il posséde moins de 25% de PV restant, il ne vous est plus possible de l'attaquer et donc de le détruire avant l'arrivée des ennemis, c'est trop tard !</p>
					
					<br />
					<h2>Les trains</h2>
					
					<p>Les trains se déplacent de 10 PM par heure et possède 2500PV.<br />
					Ils embarquent les personnages dans la gare qui disposent d'un ticket composté. Chaque train va d'une seule gare à une autre.<br />
					Quand le train arrive en gare, il décharge aussitôt les passagers et est automatiquement réparé (regagne tout ses PV).<br />
					Si la gare de destination posséde moins de 50% de ses PV maximum, le train s'arrête de circuler car la gare ne possède plus les infrastructures pour accueillir le train.<br />
					Si la gare de destination n'est pas du même camp que le train, le train s'arrête de circuler.</p>
					
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