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
			
			<p align="center"><a class="btn btn-primary" href="index.php">Retour index</a></p>
		
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
								Je pars en vacance et je ne pourrais pas jouer, que faire ?
							</button>
						</h2>
					</div>
					<div id="collapseFive" class="collapse" aria-labelledby="headingFive" data-parent="#accordionExample">
						<div class="card-body">
							Vous avez 2 possibilités :
							<ul>
								<li>Mettre vos personnages en permission (Profil -> Gérer son compte -> Partir en permission). Vos personnages disparaitront du jeu jusqu'à ce que vous reveniez. Attention, un départ en permission n'est pas immédiat et vous ne pouvez revenir de permission que minimum 72h après le départ effectif.</li>
								<li>Mettre votre perso en babysitte par un autre joueur. L'autre joueur jouera votre perso à votre place pendant votre absence. Pour cela, ce joueur doit déclarer le Babysitte (Profil -> Gérer son compte -> Déclarer un babysitte). Le Babysitte ne peut se faire que par un joueur du même camp que vous et les interractions entre les persos des 2 joueurs sont interdits durant la période de babysitte. Le babysitte est à vos risques et périls, si vous perdez vos objets / thunes et que votre perso se fait capturer pendant cette période, ce sera de votre responsabilité.</li>
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
								<li>Pas d'interraction entre vos persos (pas d'échange de thunes / objets, pas de bousculades, pas de soins)</li>
								<li>Pas d'attaques / interractions contre la même cible sous un délai de 8h (Exemple : si Joueur 1 a attaqué Ennemi A à 14h, Joueur 2 qui est multi de Joueur 1 n'a le droit d'attaquer / bousculer Ennemi A qu'à partir de 22h)</li>
							</ul>
							Tout manquement à ces règles seront punies par les animateurs par des peines plus ou moins grande (qui peuvent aller de la simple amende à l'envoi au Pénitencier).
							<b>Si un multi est détecté sans déclaration ou qu'un multi déclaré se trouve être joué par une seule personne, les persos risquent la suppression pure et simple.</b>
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