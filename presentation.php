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
			
			<div class="row">
				<div class="col-4">
					<img src="images/accueil/logo_NVS_lee.png" alt='baniere NVS' class="img-fluid" />
				</div>
			</div>
			
			<div class="row">
				<div class="col-12">
					<div align='center'>
						<a href='index.php' class='btn btn-danger'>Retour Accueil</a>
						<a href='https://encyclopedie.nord-vs-sud.fr' class='btn btn-info'>Regles</a>
						<a href='inscription.php' class='btn btn-success'>S'inscrire</a>
					</div>
				</div>
			</div>
			
			<div class="row">
				<div class="col-12">
					<p>Nord VS Sud est un <b>jeu de stratégie multijoueur au tour par tour</b> où vous dirigez un <b>Chef</b> qui commence au grade de caporal :</p>
					<center><img src='./images/presentation/chef.png' /></center><br />
					<p>accompagné de son premier <b>grouillot</b> (une infanterie) :</p>
					<center><img src='./images/presentation/grouillot.png' /></center><br />
					<p>Chaque personnage que vous controllez possède un certain nombre de <b>PA (points d'actions)</b> que vous pouvez dépenser pour différentes actions (attaques, constructions, réparations, soins, achats, consommation d'un objet, équippement d'une arme, etc...) durant votre tour de jeu.</p>
					<p>De la même façon, vos personnages possèdent un certain nombre de <b>PM (points de mouvements)</b> que vous pouvez dépenser pour... vous déplacer sur la carte de jeu. <b>Attention</b>, <a href='./regles/regles_carte.php' style="color: lightcyan;">chaque type de terrain demande plus ou moins de PM afin d'être traversé</a>.</p>
					<center><img src='./images/presentation/caracs_pa_pm.png' /></center><br />
					<p><b>Ces PA et PM vous seront restaurés à votre prochain tour de jeu</b>. Chaque tour de jeu dur <b>46h</b>. Pouyr activer votre nouveau tour, il vous suffira simplement de vous reconnecter au jeu après cette date/heure. Vous pouvez consulter la date et heure d'activation de votre prochain tour en haut à gauche de la page de jeu principale :</p>
					<center><img src='./images/presentation/tour.png' /></center><br />
					<p>Vos unités évolueront sur une carte de jeu dont la vision directe est limité par la perception de votre personnage. Par exemple, ici un personnage avec 5 de perception en plaine :</p>
					<center><img src='./images/presentation/carte_vision.png' /></center><br />
					<p>Des bonus / malus de perception peuvent être donnés en fonction du terrain sur lequel vous vous trouvez directement, par exemple, un malus de 2 points lorsqu'on se trouve en forêt (mais offre un bonus de defense aux attaques aux armes à ditance de 20) :</p>
					<center><img src='./images/presentation/carte_vision_malus_foret.png' /><img src='./images/presentation/caracs_perception_malus.png' /></center><br />
					<p>Le but principal du jeu est de faire gagner la bataille en cours à votre camp (<font color='blue'>Nord</font> ou <font color='red'>Sud</font>) en jouant de façon coordonnée avec les autres joueurs de votre camp afin de monter des opérations pour capturer / détruire les bâtiments ennemis, infiltrer / explorer une partie de la carte, participer à la construction des infrastructures de votre camp ou encore participer aux missions lancés par l'animation.</p>
					<p>Pour cela, il vous faudra dans tous les cas explorer la carte de la bataille en cours, carte de bataille que vous pouvez consulter via ce bouton <img src='./images/presentation/bouton_carte.png' /></p>
					<p>La minimap qui représente la carte de bataille se présente sous cette forme : </p>
					<center><img src='./images/presentation/minimap.png' /></center><br />
					<p>Certaines parties sont comme vous pouvez le voir sous une forme de brouillard de guerre, ce sont les parties de la carte que votre camp n'a pas encore exploré. <font color='blue'>En Bleu</font> se trouve les persos (en bleu foncé) et les infrastrctures (en bleu plus clair) du camp Nord et en <font color='red'>Rouge</font> les persos et infrastructure du camp Sud. On peut aussi apercevoir un tracé gris clair entre certains batiments, ce sont des rails entre les gares où circulent les trains.</p>
					<center><img src='./images/presentation/train.png' /></center><br />
					<p>Les trains permettent de voyager rapidement d'une gare à une autre afin de rejoindre certaines parties de la carte plus facilement et rapidement.</p>
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
