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

	<body style="background-color:grey;">

		<div class="container">
			
			<div class="row">
				<div class="col-12">
					<div align="center">
						<h2>FAQ</h2>
					</div>
				</div>
			</div>
			
			<p align="center"><a class="btn btn-primary" href="index.php">Retour index</a>
			<a class="btn btn-primary" href="./jeu/jouer.php">Retour au jeu </a>
			</p>
		
			<div class="accordion" id="accordionExample">
				<div class="card">
					<div class="card-header" id="headingOne">
						<h2 class="mb-0">
							<button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
								Qu'est ce que Nord VS Sud ?
							</button>
						</h2>
					</div>

					<div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordionExample">
						<div class="card-body">
							Nord VS Sud est un jeu de stratégie sur le thême de la guerre de Sécession dans lequel vous allez incarné un gradé d'un des camps en présence (Union/Nord ou Confédération/Sud) ainsi que ses grouillots qu'il vous sera possible de recruter en fonction de votre grade. 
							Le but du jeu est d'être le premier camp à gagner 1000 points de victoire en détruisant des infrastructures ennemies afin de remporter la bataille qui se déroule sur une carte.
						</div>
					</div>
				</div>
				<div class="card">
					<div class="card-header" id="headingTwo">
						<h2 class="mb-0">
							<button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
								Qu'est ce que les PM ?
							</button>
						</h2>
					</div>
					<div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionExample">
						<div class="card-body">
							Les PM sont les Points de Mouvement de vos personnages. Vous pouvez les utiliser pour vous déplacer sur la carte de jeu. Attention, chaque terrain possède un coût de déplacement propre, voir <a href='./regles/regles_carte.php'>ce tableau</a>.
						</div>
					</div>
				</div>
				<div class="card">
					<div class="card-header" id="headingThree">
						<h2 class="mb-0">
							<button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
								Qu'est ce que les PA ?
							</button>
						</h2>
					</div>
					<div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#accordionExample">
						<div class="card-body">
							Les PA sont les Points d'Action de vos personnages. Vous pouvez les utiliser afin d'effectuer différentes actions, chaque action a un coût qui lui est propre.
						</div>
					</div>
				</div>
				<div class="card">
					<div class="card-header" id="headingFour">
						<h2 class="mb-0">
							<button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
								Je n'ai plus de PA / PM, que puis-je faire ?
							</button>
						</h2>
					</div>
					<div id="collapseFour" class="collapse" aria-labelledby="headingFour" data-parent="#accordionExample">
						<div class="card-body">
							Votre personnage va regagner ses PA / PM à son nouveau tour de jeu (affiché en haut à gauche de la page de jeu). Un tour dure 46h. 
							Si vous n'avez plus d'action possible sur le jeu, vous avez toujours la possibilité de participer à la vie de votre camp, discuter et mettre en place des stratégies, construire du Rôle Play, etc.. sur le forum du jeu ou les autres moyens de communications disponibles (discord, messenger, etc..)
						</div>
					</div>
				</div>
				<div class="card">
					<div class="card-header" id="headingFive">
						<h2 class="mb-0">
							<button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
								Je pars en vacances et je ne pourrais pas jouer, que faire ?
							</button>
						</h2>
					</div>
					<div id="collapseFive" class="collapse" aria-labelledby="headingFive" data-parent="#accordionExample">
						<div class="card-body">
							Vous avez 2 possibilités :
							<ul>
								<li>Mettre vos personnages en permission (Profil -> Gérer son compte -> Partir en permission). Vos personnages disparaitront du jeu jusqu'à ce que vous reveniez. Attention, un départ en permission n'est pas immédiat, il faut attendre 3 jours (prise en compte à minuit) afin que le départ soit effectif donc pensez à faire votre demande en avance (il vous sera possible de continuer à jouer jusqu'à votre départ) ! De plus, vous ne pouvez revenir de permission que minimum 5 jours après le départ effectif.</li>
								<li>Mettre votre perso en babysitte par un autre joueur. L'autre joueur jouera votre perso à votre place pendant votre absence. Pour cela, ce joueur doit déclarer le Babysitte (Profil -> Gérer son compte -> Déclarer un babysitte). Le Babysitte ne peut se faire que par un joueur du même camp que vous et les interactions entre les persos des 2 joueurs sont interdits durant la période de babysitte. Le babysitte est à vos risques et périls, si vous perdez vos objets / thunes et que votre perso se fait capturer pendant cette période, ce sera de votre responsabilité.</li>
							</ul>
						</div>
					</div>
				</div>
				<div class="card">
					<div class="card-header" id="headingSix">
						<h2 class="mb-0">
							<button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseSix" aria-expanded="false" aria-controls="collapseSix">
								Nous sommes plusieurs à jouer régulièrement du même lieu (maison, école, entreprise, etc..), est-ce que ça pose problème ?
							</button>
						</h2>
					</div>
					<div id="collapseSix" class="collapse" aria-labelledby="headingSix" data-parent="#accordionExample">
						<div class="card-body">
							C'est tout à fait possible mais vous devrez le déclarer (Profil -> Gérer son compte -> Déclarer un multi) ainsi que respecter certaines règles :
							<ul>
								<li>Jouer dans le même camp</li>
								<li>Pas d'interaction entre vos persos (pas d'échange de thunes / objets, pas de bousculades, pas de soins)</li>
								<li>Pas d'attaques / interactions contre la même cible sous un délai de 8h (Exemple : si Joueur 1 a attaqué Ennemi A à 14h, Joueur 2 qui est multi de Joueur 1 n'a le droit d'attaquer / bousculer Ennemi A qu'à partir de 22h)</li>
							</ul>
							Tout manquement à ces règles sera puni par les animateurs par des peines plus ou moins grande (qui peuvent aller de la simple amende à l'envoi au Pénitencier).
							<b>Si un multi est détecté sans déclaration ou qu'un multi déclaré se trouve être joué par une seule personne, les persos risquent la suppression pure et simple.</b>
						</div>
					</div>
				</div>
				<div class="card">
					<div class="card-header" id="headingSeven">
						<h2 class="mb-0">
							<button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseSeven" aria-expanded="false" aria-controls="collapseSeven">
								Comment se passe le retour de permission ?
							</button>
						</h2>
					</div>
					<div id="collapseSeven" class="collapse" aria-labelledby="headingSeven" data-parent="#accordionExample">
						<div class="card-body">
							Vous n'avez la possibilité de revenir de permission que <b>5 jours après votre départ effectif de permission</b>.
							Pour votre retour de permission, votre personnage sera envoyé au bâtiment le plus proche, permettant de vous accueillir, de votre position de départ de permission. Les bâtiments permettant de vous accueillir d'un retour de permission sont les Forts, les Fortins et les Gares si ces derniers ne sont pas en état de siège et si leur contenance max n'est pas atteinte.
						</div>
					</div>
				</div>
				<div class="card">
					<div class="card-header" id="headingEight">
						<h2 class="mb-0">
							<button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseEight" aria-expanded="false" aria-controls="collapseEight">
								Comment avoir un aperçu global/stratégique de la situation ?
							</button>
						</h2>
					</div>
					<div id="collapseEight" class="collapse" aria-labelledby="headingEight" data-parent="#accordionExample">
						<div class="card-body">
							Il existe une carte stratégique accessible depuis le menu nommé "CARTE". En cliquant dessus, vous ouvrirez la carte stratégique, vous permettant d'avoir une vision rapide de la situation actuelle du jeu.
							La carte possède des zones découvertes (zones déjà explorées par un perso de votre camp) et des zones non découvertes. Il vous est impossible de voir les mouvements ennemis sur les zones non découvertes, à vous donc de faire en sorte de découvrir rapidement le plus de zones !
						</div>
					</div>
				</div>
				<div class="card">
					<div class="card-header" id="headingNine">
						<h2 class="mb-0">
							<button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseNine" aria-expanded="false" aria-controls="collapseNine">
								Je vois un ennemi en forêt à côté de moi mais il est invisible sur la carte stratégique, pourquoi ?
							</button>
						</h2>
					</div>
					<div id="collapseNine" class="collapse" aria-labelledby="headingNine" data-parent="#accordionExample">
						<div class="card-body">
							Les zones de forêt sont dites à couvert, elles permettent aux personnages de rester invisibles de la carte stratégique. Cela permet de favoriser les infiltrations, embuscades et donc de rajouter un peu plus de piquant au jeu.
							A vous donc de prendre en compte les terrains de la carte afin d'identifier les zones à surveiller ou les zones à exploiter.
						</div>
					</div>
				</div>
				<div class="card">
					<div class="card-header" id="headingTen">
						<h2 class="mb-0">
							<button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseTen" aria-expanded="false" aria-controls="collapseTen">
								En rentrant dans une forêt, j'ai perdu de la perception, pourquoi ?
							</button>
						</h2>
					</div>
					<div id="collapseTen" class="collapse" aria-labelledby="headingTen" data-parent="#accordionExample">
						<div class="card-body">
							Chaque type de terrain possède ses spécificités et vous accordent divers bonus et/ou malus. Reportez vous à la <a href='./regles/regles_carte.php'>page de règles dédiée</a> pour tous les connaitre.
						</div>
					</div>
				</div>
				<div class="card">
					<div class="card-header" id="headingEleven">
						<h2 class="mb-0">
							<button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseEleven" aria-expanded="false" aria-controls="collapseEleven">
								Quelqu'un m'a envoyé une image sur la messagerie du jeu, comment faire pour améliorer mes messages dans le jeu ?
							</button>
						</h2>
					</div>
					<div id="collapseEleven" class="collapse" aria-labelledby="headingEleven" data-parent="#accordionExample">
						<div class="card-body">
							Vous pouvez utiliser certaines balises bbcode dans vos messages, en voici la liste :
							<ul>
								<li>[center]Mon texte centré[/center]</li>
								<li>[img*]monUrl vers l'image[/img*]</li>
								<li>[b]mon texte en gras[/b]</li>
								<li>[u]mon texte souligné[/u]</li>
								<li>[i]mon texte en italique[/i] </li>
								<li>[color=maCouleur]mon texte dans la couleur maCouleur[color=maCouleur]</li>
								<li>[table][tr*] et [td*] pour la Gestion des tableau</li>
							</ul>
						</div>
					</div>
				</div>
			</div>
			
			
			<div class="row">
				<div class="col-12">
					<div align="center">
						<h2>FAQ Train / Rails</h2>
					</div>
				</div>
			</div>
			
			<div class="accordion" id="accordionTrain">
		
				<div class="card">
					<div class="card-header" id="headingTwelve">
						<h2 class="mb-0">
							<button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseTwelve" aria-expanded="false" aria-controls="collapseTwelve">
								Comment prendre le train ?
							</button>
						</h2>
					</div>
					<div id="collapseTwelve" class="collapse" aria-labelledby="headingTwelve" data-parent="#accordionTrain">
						<div class="card-body">
							Le train sert à relier 2 gares. Il sert donc à se rendre d'une gare vers une autre.<br />
							Pour prendre le train, vous devez entrer dans la gare et acheter un ticket de train vers la gare de destination (Coût 5 Thunes par tronçon).
							Il ne vous reste plus qu'à attendre le train, qui une fois arrivé en gare, vous embarquera automatiquement et vous débarquera automatiquement dans la gare de destination.
							Si le voyage demande de passer par plusieurs tronçons, vous serez débarqué dans chaque gare intermédiaire avant d'embarquer automatiquement dans le train suivant jusqu'à votre destination.<br />
							<b>Attention : les tickets de train sont nominatifs et ne peuvent donc être cédés à un autre perso, ils ne fonctionneront pas !</b>
						</div>
					</div>
				</div>
				<div class="card">
					<div class="card-header" id="headingThirteen">
						<h2 class="mb-0">
							<button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseThirteen" aria-expanded="false" aria-controls="collapseThirteen">
								Mon train semble ne plus avancer, que se passe t-il ?
							</button>
						</h2>
					</div>
					<div id="collapseThirteen" class="collapse" aria-labelledby="headingThirteen" data-parent="#accordionTrain">
						<div class="card-body">
							Un train peut être bloqué pour plusieurs raisons :
							<ul>
								<li>La gare de destination du train n'existe plus / a été détruite</li>
								<li>La gare de destination du train n'est plus en état de fonctionner (PV en dessous de 50%)</li>
								<li>Une barricade a été placée sur la route du train (le train s'arretera alors devant la barricade)</li>
							</ul>
						</div>
					</div>
				</div>
				<div class="card">
					<div class="card-header" id="headingForteen">
						<h2 class="mb-0">
							<button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseForteen" aria-expanded="false" aria-controls="collapseForteen">
								Je souhaite détruire / saboter un rail, comment faire ?
							</button>
						</h2>
					</div>
					<div id="collapseForteen" class="collapse" aria-labelledby="headingForteen" data-parent="#accordionTrain">
						<div class="card-body">
							Un rail ne peut être détruit que par les unités du génie. Si vous faites partie du génie, il vous suffit de vous positionner sur le rail que vous souhaitez détruire et l'option apparaitra en dessous de la liste des actions et sur le popup du perso.
							L'action coûte 10PA et est immédiate.
						</div>
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