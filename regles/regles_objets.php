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
				<a class="navbar-brand" href="#">Régles</a>
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
							<a class="nav-link active" href="regles_objets.php">Les objets, armes et thunes</a>
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
							<a class="nav-link" href="regles_action_spe.php">Actions spéciales</a>
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
	
					<div align="center"><h2>Les Objets, Armes et Thunes</h2></div>
			
				</div>
			</div>
			
			<div class="row">
				<div class="col-12">
					<br />
					<p>Il existe diverses armes et objets qui vous seront accessible tout au long du jeu. Ces objets vous coûteront des thunes, et vous donneront des bonus divers ou variés, voire aucun bonus.<br />
					Vous pourrez acheter les armes et objets dans les boutiques de Momo qui sont installées dans divers bâtiments (Fortin, Fort, Hopital, etc..). </p>
					
					<h2>Les Armes</h2>
					
					<p>Toutes les armes ont un coût d'attaque en PA, un % de précision, une quantité de dégâts (en D6 principalement, mais aussi D8 ou D10 pour certaines), une portée en cases, mais aussi des malus.<br />
					Chaque arme dispose de modificateurs sur ses caractéristiques : Coût d'utilisation, dégâts, portée, précision. Ces caractéristiques varieront selon votre état, le terrain sur lequel vous êtes ainsi que la portée à laquelle vous l'utilisez.<br />
					Les armes sont disponibles chez le marchand Momo (notamment dans les forts et fortins). On trouve ainsi aisément des baïonnettes, sabres, couteaux, sabres lourds, pistolets, pistolets à canon long, magnums, fusils, fusils à double canon, fusil de précision, gatlings…<br />
					Il est possible d'acheter autant d'armes que vous en pouvez transporter.</p>
					
					<p>Toutes armes nouvellement achetées sera intégrées dans votre inventaire sous le statut "rengainé" (non équipée). Vous pourrez dégainer (ou équiper) une arme, si la place correspondante dans l'inventaire "arme" est libre. Cette action vous coûtera 1 PA.<br />
					Rengainer (ou déséquiper) une arme vous coûtera aussi 1 PA.</p>
					
					<p>Vous pouvez ramasser une arme qui est à terre en passant sur sa case et en cliquant sur le lien permettant de rammasser les objets à terre.<br />
					Vous pouvez aussi abandonner une arme à terre pour 1 PA.<br />
					Donner une arme vous coûte 1 PA.<br />
					L'achat et la vente d'une arme coûte 2 PA.</p>
					
					<h2>Tableau des armes de Corps à corps</h2>
					
					<div id="table_armes_cac" class="table-responsive">
						<table border='1' align="center" width=100%>
							<tr>
								<th style="text-align:center">Image</th>
								<th style="text-align:center">Nom</th>
								<th style="text-align:center">Dégâts</th>
								<th style="text-align:center">Précision</th>
								<th style="text-align:center">Coût PA</th>
								<th style="text-align:center">Unités</th>
								<th style="text-align:center">Poids</th>
								<th style="text-align:center">Coût Or</th>
								<th style="text-align:center">Description</th>
							</tr>
							<tr>
								<td align='center'><img src='../images/armes/baionette.jpg' style='max-width: 100px; height: auto;' alt=''></td>
								<td align='center'>Baïonette</td>
								<td align='center'>16D6</td>
								<td align='center'>60%</td>
								<td align='center'>3</td>
								<td align='center'>Infanterie</td>
								<td align='center'>0.5kg</td>
								<td align='center'>50</td>
								<td align='center'>Arme de départ de Corps à corps des infanteries</td>
							</tr>
							<tr>
								<td align='center'><img src='../images/armes/sabre.jpg' style='max-width: 100px; height: auto;' alt=''></td>
								<td align='center'>Sabre</td>
								<td align='center'>20D6</td>
								<td align='center'>80%</td>
								<td align='center'>4</td>
								<td align='center'>Chef / Cavalerie</td>
								<td align='center'>2.0kg</td>
								<td align='center'>0</td>
								<td align='center'>Arme de départ de Corps à corps des chefs et cavaliers</td>
							</tr>
							<tr>
								<td align='center'><img src='../images/armes/sabre_lourd.jpg' style='max-width: 100px; height: auto;' alt=''></td>
								<td align='center'>Sabre Lourd</td>
								<td align='center'>25D6</td>
								<td align='center'>80%</td>
								<td align='center'>5</td>
								<td align='center'>Chef / Cavalerie</td>
								<td align='center'>2.5kg</td>
								<td align='center'>250</td>
								<td align='center'>Sabre lourd, plus lourd, inflige plus de dégâts mais moins maniable qu'un sabre de dotation</td>
							</tr>
							<tr>
								<td align='center'><img src='../images/armes/cannine.jpg' style='max-width: 100px; height: auto;' alt=''></td>
								<td align='center'>Cannines / Crocs / Morsure</td>
								<td align='center'>15D4</td>
								<td align='center'>90%</td>
								<td align='center'>10</td>
								<td align='center'>Chien</td>
								<td align='center'>0.0kg</td>
								<td align='center'>0</td>
								<td align='center'>Arme naturelle de Corps à corps des chiens</td>
							</tr>
							<tr>
								<td align='center'><img src='../images/armes/seringue.jpg' style='max-width: 100px; height: auto;' alt=''></td>
								<td align='center'>Seringue</td>
								<td align='center'>20D6</td>
								<td align='center'>90%</td>
								<td align='center'>5</td>
								<td align='center'>Soigneur</td>
								<td align='center'>0.1kg</td>
								<td align='center'>50</td>
								<td align='center'>Les seringues permettent de soigner directement les PV des persos / PNJ</td>
							</tr>
							<tr>
								<td align='center'><img src='../images/armes/bandage.jpg' style='max-width: 100px; height: auto;' alt=''></td>
								<td align='center'>Bandages</td>
								<td align='center'>2D10</td>
								<td align='center'>35%</td>
								<td align='center'>3</td>
								<td align='center'>Soigneur</td>
								<td align='center'>0.2kg</td>
								<td align='center'>50</td>
								<td align='center'>Les bandages permettent de soigner les malus des persos / PNJ</td>
							</tr>
						</table>
					</div>
					
					<br /><br />
					
					<h2>Tableau des armes de combat à distance</h2>
					
					<div id="table_armes_dist" class="table-responsive">
						<table border='1' align="center" width=100%>
							<tr>
								<th style="text-align:center">Image</th>
								<th style="text-align:center">Nom</th>
								<th style="text-align:center">Dégâts</th>
								<th style="text-align:center">Précision</th>
								<th style="text-align:center">Portée</th>
								<th style="text-align:center">Coût PA</th>
								<th style="text-align:center">Unités</th>
								<th style="text-align:center">Poids</th>
								<th style="text-align:center">Coût Or</th>
								<th style="text-align:center">Description</th>
							</tr>
							<tr>
								<td style="text-align:center"><img src='../images/armes/cailloux.gif' style='max-width: 100px; height: auto;' alt=''></td>
								<td style="text-align:center">Cailloux</td>
								<td style="text-align:center"></td>
								<td style="text-align:center"></td>
								<td style="text-align:center"></td>
								<td style="text-align:center"></td>
								<td style="text-align:center"></td>
								<td style="text-align:center"></td>
								<td style="text-align:center"></td>
								<td style="text-align:center"></td>
							</tr>
							<tr>
								<td style="text-align:center"><img src='../images/armes/pistolet.jpg' style='max-width: 100px; height: auto;' alt=''></td>
								<td style="text-align:center">Pistolet</td>
								<td style="text-align:center"></td>
								<td style="text-align:center"></td>
								<td style="text-align:center"></td>
								<td style="text-align:center"></td>
								<td style="text-align:center"></td>
								<td style="text-align:center"></td>
								<td style="text-align:center"></td>
								<td style="text-align:center"></td>
							</tr>
							<tr>
								<td style="text-align:center"><img src='../images/armes/pistolet_canon_long.jpg' style='max-width: 100px; height: auto;' alt=''></td>
								<td style="text-align:center">Pistolet canon long</td>
								<td style="text-align:center"></td>
								<td style="text-align:center"></td>
								<td style="text-align:center"></td>
								<td style="text-align:center"></td>
								<td style="text-align:center"></td>
								<td style="text-align:center"></td>
								<td style="text-align:center"></td>
								<td style="text-align:center"></td>
							</tr>
							<tr>
								<td style="text-align:center"><img src='../images/armes/magnum.jpg' style='max-width: 100px; height: auto;' alt=''></td>
								<td style="text-align:center">Magnum</td>
								<td style="text-align:center"></td>
								<td style="text-align:center"></td>
								<td style="text-align:center"></td>
								<td style="text-align:center"></td>
								<td style="text-align:center"></td>
								<td style="text-align:center"></td>
								<td style="text-align:center"></td>
								<td style="text-align:center"></td>
							</tr>
							<tr>
								<td style="text-align:center"><img src='../images/armes/fusil.jpg' style='max-width: 100px; height: auto;' alt=''></td>
								<td style="text-align:center">Fusil</td>
								<td style="text-align:center"></td>
								<td style="text-align:center"></td>
								<td style="text-align:center"></td>
								<td style="text-align:center"></td>
								<td style="text-align:center"></td>
								<td style="text-align:center"></td>
								<td style="text-align:center"></td>
								<td style="text-align:center"></td>
							</tr>
							<tr>
								<td style="text-align:center"><img src='../images/armes/fusil_precision.jpg' style='max-width: 100px; height: auto;' alt=''></td>
								<td style="text-align:center">Fusil Précision</td>
								<td style="text-align:center"></td>
								<td style="text-align:center"></td>
								<td style="text-align:center"></td>
								<td style="text-align:center"></td>
								<td style="text-align:center"></td>
								<td style="text-align:center"></td>
								<td style="text-align:center"></td>
								<td style="text-align:center"></td>
							</tr>
							<tr>
								<td style="text-align:center"><img src='../images/armes/canon.jpg' style='max-width: 100px; height: auto;' alt=''></td>
								<td style="text-align:center">Canon</td>
								<td style="text-align:center"></td>
								<td style="text-align:center"></td>
								<td style="text-align:center"></td>
								<td style="text-align:center"></td>
								<td style="text-align:center"></td>
								<td style="text-align:center"></td>
								<td style="text-align:center"></td>
								<td style="text-align:center"></td>
							</tr>
							<tr>
								<td style="text-align:center"><img src='../images/armes/gatling.png' style='max-width: 100px; height: auto;' alt=''></td>
								<td style="text-align:center">Gatling</td>
								<td style="text-align:center"></td>
								<td style="text-align:center"></td>
								<td style="text-align:center"></td>
								<td style="text-align:center"></td>
								<td style="text-align:center"></td>
								<td style="text-align:center"></td>
								<td style="text-align:center"></td>
								<td style="text-align:center"></td>
							</tr>
						</table>
					</div>
					
					<br /><br />
					
					<h2>Les Objets</h2>
					
					<p>Vous pouvez acheter divers objets chez Momo qui pourront vous donner (ou pas) des bonus temporaire ou définitifs si vous les utilisez.<br />
					Ainsi vous pourrez trouver des bouteilles de Whisky, trousses de soins, gourde d'eau…</p>
					
					<p>Vous pouvez aussi ramasser des objets à terre en passant simplement sur la case où est situé l'objet et en cliquant sur le lien permettant de rammasser les objets à terre.<br />
					L'utilisation d'un objet coute 1 PA.<br />
					Vous pouvez aussi abandonner un objet à terre pour 1 PA.<br />
					Donner un objet coûte 1 PA.<br />
					L'achat et la vente d'un objet coûte 2 PA.</p>
					
					<p>Vous pouvez revendre des objets chez Momo à condition que ceux ci ne soient pas déjà utilisés et Momo vous infligera alors une décote de 10%.</p>
					
					<center><b>Tableau des objets</b></center>
					
					<div id="table_objets" class="table-responsive">
						<table border='1' align="center" width=100%>
							<tr>
								<th style="text-align:center">Image</th>
								<th style="text-align:center">Nom</th>
								<th style="text-align:center">Type d'objet</th>
								<th style="text-align:center">Bonus</th>
								<th style="text-align:center">Malus</th>
								<th style="text-align:center">Poids</th>
								<th style="text-align:center">Coût</th>
								<th style="text-align:center">Lieu d'achat</th>
							</tr>
							<tr>
								<td align='center'><img src='../images/objets/objet1.png' style='max-width: 100px; height: auto;' alt=''></td>
								<td align='center'>Ticket de train</td>
								<td align='center'>Spécial</td>
								<td align='center'></td>
								<td align='center'></td>
								<td align='center'></td>
								<td align='center'></td>
								<td align='center'>Gare</td>
							</tr>
							<tr>
								<td align='center'><img src='../images/objets/objet2.png' style='max-width: 100px; height: auto;' alt=''></td>
								<td align='center'>Gourde d'eau</td>
								<td align='center'>Consommable</td>
								<td align='center'></td>
								<td align='center'></td>
								<td align='center'></td>
								<td align='center'></td>
								<td align='center'>Fort / Fortin</td>
							</tr>
							<tr>
								<td align='center'><img src='../images/objets/objet3.png' style='max-width: 100px; height: auto;' alt=''></td>
								<td align='center'>Whisky</td>
								<td align='center'>Consommable</td>
								<td align='center'></td>
								<td align='center'></td>
								<td align='center'></td>
								<td align='center'></td>
								<td align='center'>Fort / Fortin</td>
							</tr>
							<tr>
								<td align='center'><img src='../images/objets/objet4.png' style='max-width: 100px; height: auto;' alt=''></td>
								<td align='center'>Trousse de soin</td>
								<td align='center'>Consommable</td>
								<td align='center'></td>
								<td align='center'></td>
								<td align='center'></td>
								<td align='center'></td>
								<td align='center'>Fort / Fortin / Hôpital</td>
							</tr>
							<tr>
								<td align='center'><img src='../images/objets/objet5.png' style='max-width: 100px; height: auto;' alt=''></td>
								<td align='center'>Bottes légères</td>
								<td align='center'>Équipable</td>
								<td align='center'></td>
								<td align='center'></td>
								<td align='center'></td>
								<td align='center'></td>
								<td align='center'>Fort / Fortin</td>
							</tr>
							<tr>
								<td align='center'><img src='../images/objets/objet6.png' style='max-width: 100px; height: auto;' alt=''></td>
								<td align='center'>Lunnette de vue</td>
								<td align='center'>Équipable</td>
								<td align='center'></td>
								<td align='center'></td>
								<td align='center'></td>
								<td align='center'></td>
								<td align='center'>Fort / Fortin</td>
							</tr>
							<tr>
								<td align='center'><img src='../images/objets/objet7.png' style='max-width: 100px; height: auto;' alt=''></td>
								<td align='center'>Lunette de visée</td>
								<td align='center'>Équipable</td>
								<td align='center'></td>
								<td align='center'></td>
								<td align='center'></td>
								<td align='center'></td>
								<td align='center'>Fort / Fortin</td>
							</tr>
						</table>
					</div>
					
					<br />
					<p>Les objets consommable disparaissent après utilisation.<br />
					Les objets équipable peuvent être équipés/déséquipés et octroie les bonus/malus uniquement lorsqu'ils sont équipés.</p>
					
					<h2>Les Thunes</h2>
					
					<p>La Thune est l'indicateur de richesse de votre personnage. L'argent permet de se procurer armement, médicaments… mais aussi de soudoyer un ennemi.<br />
					Parce que vous êtes dans l'armée, vous percevez à chaque tour une solde qui s'élève à 3 thunes pour le chef et un nombre de thune égal à son nombre de PG divisé par 2 et arrondi au supérieur par grouillot.</p>
					
					<p>Lorsque vous tuez des adversaires vous gagnez de la thune si vous ramassez la bourse qui se trouve à la place de leur cadavre.</p>
					
					<p>Vous pouvez donner de la thune à un autre joueur ou la placer sur le compte de votre compagnie (somme minimale: 25 thunes).</p>
					
					<p>Attention, vous pouvez perdre de la thune à la capture de votre personnage.<br />
					La perte de thunes transportées à sa capture est calculée ainsi: <br />
					<ul>
						<li>30% à la capture de votre chef de bataillon</li>
						<li>10% à la capture d'un de vos grouillots</li>
					</ul>
					D'où l'avantage de faire partie d'une compagnie et de placer son argent dans la caisse de cette dernière. 
					</p>
					
					<h2>Malus à cause du poids</h2>
					
					<p>Lorsque le perso est chargé avec 10kg ou plus d'objets et d'armes, un malus de mouvement est alors appliqué : -1PM / 10kg. Ainsi, à 20kg, vous aurez un malus de 2PM et ainsi de suite.<br />
					Si la charge totale est supérieure ou égale à 70kg, le personnage est immobilisé (malus égal à la totalité de ses PM max).</p>
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