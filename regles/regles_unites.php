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
	
					<div align="center"><h2>Les unités</h2></div>
			
				</div>
			</div>
			
			<div class="row">
				<div class="col-12">
					
					
					<p>Dans Nord versus Sud, vous serez amené à jouer différents types de PJs(personnes joueurs).<br />
					 Vous disposerez d'un chef et de ses grouillots. Vous pourrez également choisir les unités que vous voulez jouer en fonction de votre grade : Cavalerie, infanterie, artillerie… Chaque unité dispose de ses propres spécificités. </p>
					
					<h4>Le Chef</h4>
					
					<p>Votre chef de bataillon effectue une carrière militaire et monte progressivement en grade. Lorsqu'il change de grade, il vous permet alors d'augmenter son nombre de grouillots. Donc plus vous êtes gradé, plus vous pourrez jouer de personnages.</p>
					<p>Lors de sa capture, votre chef perd 5% de ses XPI/PC mais ne peut pas descendre de grade même si ses PC descendent en dessous du niveau requit pour le grade (un grade est acquit définitivement hors rétrogradation par l'animation).</p>
					<p>Le nom de votre chef de bataillon est défini lors de votre inscription. Cette unité est une cavalerie, boostée par rapport à un grouillot cavalerie (plus de PV, protection, etc..)</p>
					
					<h4>Les grouillots</h4>
					
					<p>Les grouillots sont des personnages supplémentaires que vous jouez. lorsque l'un d'eux est capturé, il perd 40% de ses XPI. Ils conservent cependant les améliorations acquises. Pensez donc à utiliser sciemment les XPI de vos grouillots au risque de grosses pertes.</p>
					<p>Le recrutement de nouveaux grouillots s'effectue dans les forts et les fortins, au prix de 3 PA par tête. Les grouillots nouvellement recrutés le sont avec 0PA et 0PM, ils seront donc opérationnels qu'au prochain tour.</p>
					<p>Renvoyer un grouillot ne coûte aucun PA. Son renvoie n’est plus définitif. Il entrera dans votre réserve d’unités et elle pourra selon certaines conditions être recrutée de nouveau. Elle pourra ainsi conserver ses améliorations Cependant <b>le matériel équipé sera définitivement perdu.</b></p>
					<p>Chaque grouillot vaut un certain nombre de points de grouillot (PG) :
					<ul>
						<li>Un chien vaut 1 point de grouillot. Il gagne une solde de 1 thune par tour.</li>
						<li>Une infanterie vaut 2 points de grouillot. Il gagne une solde de 1 thune par tour.</li>
						<li>Un soigneur vaut 3 points de grouillot. Il gagne une solde de 2 thunes par tour.</li>
						<li>Une cavalerie légère vaut 3 points de grouillot. Il gagne une solde de 1 thune par tour.</li>
						<li>Une cavalerie lourde vaut 4 points de grouillot. Il gagne une solde de 2 thunes par tour.</li>
						<li>Une artillerie vaut 5 points de grouillot. Il gagne une solde de 3 thunes par tour.</li>
					</ul></p>
					<p>Vous n'avez pas l'obligation d'utiliser tous vos PG, ces derniers seront alors conservés pour une utilisation postérieure. Cependant, il est vivement recommandé d'utiliser le maximum de PG afin de faire évoluer plus rapidement votre chef de bataillon (vos grouillots vous permettent de gagner des PC).</p>
					
					<h4>Caractéristiques des Unités</h4>
					
					<div id="table_terrain" class="table-responsive">
						<table border='1' align="center" width=100%>
							<tr>
								<th style="text-align:center">case</th>
								<th style="text-align:center">nom</th>
								<th style="text-align:center">PV</th>
								<th style="text-align:center">PA</th>
								<th style="text-align:center">PM</th>
								<th style="text-align:center">Perception</th>
								<th style="text-align:center">Récupération</th>
								<th style="text-align:center">Protection</th>
								<th style="text-align:center">Commentaire</th>
							</tr>
							<tr>
								<td align='center'><img src='../images_perso/v1/cavalerie_nord.gif' alt='chef'><img src='../images_perso/v1/cavalerie_sud.gif' alt='chef'></td>
								<td align='center'>Chef de bataillon</td>
								<td align='center'>850</td>
								<td align='center'>10</td>
								<td align='center'>10</td>
								<td align='center'>5</td>
								<td align='center'>40</td>
								<td align='center'>20</td>
								<td align='center'>Il s'agit de votre unité principale. Soldats montés sur de puissants chevaux, les chefs sont le fer de lance de chacune des armées. Un bataillon ou une famille sans son chef ne fait pas long feu… Les chefs sont avant tout des unités de corps-à-corps et disposent de la terrible charge.</td>
							</tr>
							<tr>
								<td align='center'><img src='../images_perso/v1/cavalerie_nord.gif' alt='cavalerie'><img src='../images_perso/v1/cavalerie_sud.gif' alt='cavalerie'></td>
								<td align='center'>Cavalerie Lourde</td>
								<td align='center'>700</td>
								<td align='center'>10</td>
								<td align='center'>10</td>
								<td align='center'>5</td>
								<td align='center'>30</td>
								<td align='center'>15</td>
								<td align='center'>La cavalerie lourde est sans doute l'unité Nordiste/Sudiste courante la plus redoutée sur les champs de bataille. Comparable aux chefs en tout point excepté sa résistance, la cavalerie peut faire de véritables saignées dans les rangs adverses notamment grâce à ses charges.</td>
							</tr>
							<tr>
								<td align='center'><img src='../images_perso/v1/cavalerie_legere_nord.gif' alt='cavalerie légère'><img src='../images_perso/v1/cavalerie_legere_sud.gif' alt='cavalerie légère'></td>
								<td align='center'>Scout ou Cavalerie Légère</td>
								<td align='center'>400</td>
								<td align='center'>10</td>
								<td align='center'>12</td>
								<td align='center'>5</td>
								<td align='center'>60</td>
								<td align='center'>10</td>
								<td align='center'>La cavalerie légère ou Scout est l’unité la plus mobile, redoutée pour sa capacité à se projeter dans les lignes arrières de l’ennemi, elle est également parfaite pour l’exploration et cartographier les différents terrains et carte. Attention cependant à sa faible endurance, une unité difficile à jouer mais qui vous permettra de varier vos gameplays.</td>
							</tr>
							<tr>
								<td align='center'><img src='../images_perso/v1/infanterie_nord.gif' alt='infanterie'><img src='../images_perso/v1/infanterie_sud.gif' alt='infanterie'></td>
								<td align='center'>Infanterie</td>
								<td align='center'>500</td>
								<td align='center'>10</td>
								<td align='center'>5</td>
								<td align='center'>4</td>
								<td align='center'>30</td>
								<td align='center'>10</td>
								<td align='center'>Il s'agit du plus courant des grouillots présents dans les armées nordistes et sudistes. Les infanteries représentent la masse de ces armées. Les infanteries, lorsqu'elles sont coordonnées et regroupées sont extrêmement redoutables et peuvent faire des ravages dans les rangs ennemis. Ce sont avant tout des unités de tir et d’attaque à distance, cependant elles peuvent désormais charger avec leur baïonnette et infliger de gros dégâts à ses adversaires. Elle a également gagné en mobilité avec la capacité de se déplacer pour 1 pm en forêt.</td>
							</tr>
							<tr>
								<td align='center'><img src='../images_perso/v1/soigneur_nord.gif' alt='soigneur'><img src='../images_perso/v1/soigneur_sud.gif' alt='soigneur'></td>
								<td align='center'>Soigneur</td>
								<td align='center'>400</td>
								<td align='center'>10</td>
								<td align='center'>6</td>
								<td align='center'>4</td>
								<td align='center'>30</td>
								<td align='center'>10</td>
								<td align='center'>Les soigneurs sont des unités Nordistes/Sudistes non combattantes dont le rôle est uniquement de soigner les troupes parties sur le front. Plus rapides que des infanteries classiques, ces unités doivent malgré tout rester prudentes sur le front.</td>
							</tr>
							<tr>
								<td align='center'><img src='../images_perso/v1/toutou_nord.gif' alt='toutou'><img src='../images_perso/v1/toutou_sud.gif' alt='toutou'></td>
								<td align='center'>Chien</td>
								<td align='center'>125</td>
								<td align='center'>10</td>
								<td align='center'>12</td>
								<td align='center'>5</td>
								<td align='center'>20</td>
								<td align='center'>0</td>
								<td align='center'>Les chiens sont des animaux domestiques entraînés à traquer l'ennemi grâce à leur flair très développé.</td>
							</tr>
							<tr>
								<td align='center'><img src='../images_perso/v1/gatling_nord.gif' alt='gatling'><img src='../images_perso/v1/gatling_sud.gif' alt='gatling'></td>
								<td align='center'>Gatling</td>
								<td align='center'>500</td>
								<td align='center'>10</td>
								<td align='center'>5</td>
								<td align='center'>5</td>
								<td align='center'>30</td>
								<td align='center'>10</td>
								<td align='center'>La Gatling est la première unité de type artillerie. Elle fait des dégâts collatéraux limités ainsi que de gros dégâts. Cependant la déperdition des balles à répétition peut parfois réduire l’impact des attaques. Cependant la gatling reste une unité puissante possédant plus de mobilité que le canon et permettant de faire des dégâts de zone.</td>
							</tr>						
							<tr>
								<td align='center'><img src='../images_perso/v1/artillerie_nord.gif' alt='artillerie'><img src='../images_perso/v1/artillerie_sud.gif' alt='artillerie'></td>
								<td align='center'>Canon</td>
								<td align='center'>500</td>
								<td align='center'>10</td>
								<td align='center'>3</td>
								<td align='center'>6</td>
								<td align='center'>30</td>
								<td align='center'>10</td>
								<td align='center'>La plus puissante de toutes les unités combattantes. L'artillerie est tout simplement extrêmement puissante, pouvant réduire en miettes tout un bataillon en très peu de temps. Mais c'est une unité extrêmement peu mobile et qui ne peut se battre au corps à corps et donc qui nécessite beaucoup d'attention et de protection.</td>
							</tr>						
							
						</table>
					</div>
					<br />
					
					<h3>Spécificités du chien</h3>
					<p>Le chien possède plusieurs spécificités : </p>
					<ul>
						<li>Le chien n'a pas de malus de perception en forêt Le chien aura un malus de perception de 3 s'il se trouve a plus de 15 cases de son maitre (le chef de bataillon)</li>
						<li>La charge maximale du chien avant d'avoir un malus de PM est de 2kg. Au dessus, il aura un malus de 4PM et ainsi de suite pour chaque kilo au dessus (de 2,1kg à 3kg de charge : il aura 4PM de malus, de 3,1kg à 4kg de charge, il aura 8PM de malus, etc...).</li>
						<li>A partir de 6kg de charge, le chien perd tous ses PM !</li>
						<li>Les chiens ne peuvent pas capturer les bâtiments ennemis Les chiens ne peuvent pas s'équiper d'objets (comme les bottes, les lunettes de visée, etc...)</li>
						<li>Les chiens ne peuvent pas réparer les bâtiments </li>
						<li>Les chiens ne peuvent pas lire ou envoyer de télégrammes</li>
						<li> Les chiens ne peuvent pas créer ou postuler dans des compagnies</li>
						<li>Les chiens ne peuvent pas acheter ou vendre des objets / armes / tickets de train</li>
						<li>Les chiens ne peuvent pas définir / modifier de message du jour</li>
					</ul>
					
					<h3>Rapatriement  & Convalescence</h3>
					<p>Une unité capturée est rapatriée dans un bâtiment (règles de rapatriement ici). Si l'unité n'a pas entamé un nouveau tour, elle se retrouve alors avec 0PA et 0PM. Si l'unité rapatriée entame un nouveau tour, elle subit les effets de la convalescence. Elle se retrouve donc avec PA/2 et PM/2 (hors bonus / malus qui s'ajoutent) uniquement pour le tour qui suit un rapatriement (le malus de convalescence disparait ensuite au tour suivant).</p>
					
					<h2>Inactivité</h2>
					<p>Les unités inactive pendant plus de 10 jours sont placée automatiquement en permission (voir les <a href="regles_action_spe.php">actions spéciales</a> pour connaitre les règles sur la permission).</p>
					
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
