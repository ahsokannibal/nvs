<?php
$title = "";

/* ---Header--- */
ob_start();
?>
<div class="row justify-content-center text-center">
	<div class="col col-md-6">
		<h2 class='mb-3'>LE JEU</h2>
		<nav>
			<a class="btn btn-primary" href="/jeu/jouer.php">COUCOU</a>
		</nav>
	</div>
</div>
<?php
$header = ob_get_clean();

/* ---Content--- */
ob_start();
?>
<div class="row justify-content-center">
	<div class="col-12 alert alert-success">
		<h1>Vous êtes connecté</h1>
	</div>
	<div class="col-12">
		<div class='row justify-content-center'>
			<div class='col-11 col-sm shadow bg-gray-300 rounded-3 p-4 m-2'>
				<!-- texte à repenser éventuellement -->
				<h3>Synopsis</h3>
				<p>
					Amérique du Nord, printemps 1861. Bienvenue dans la lutte qui oppose le <span class='text-primary fw-bold'>Nord</span> et le <span class='text-danger fw-bold'>Sud</span>.<br />
					Nous sommes à la fin du 19ème siècle et depuis des années, les tensions montent entre l'armée de l'Union, commandée par <span class='text-primary fw-bold'>Abraham Lincoln</span>, et l'armée des Etats confédérés, commandée par <span class='text-danger fw-bold'>Jefferson Davis</span>.<br/>
					<br/>
					le 12 avril, La guerre est déclarée.<br/>
					Une attaque de l'armée des États confédérés sur le Fort Sumter, à Charleston (Caroline du Sud), lance les hostilités. Vous vous retrouvez malgré vous dans cette tourmente et devez choisir un camp.<br/>
					<span class='text-primary fw-bold'>Unionniste</span> ou <span class='text-danger fw-bold'>confédéré ?</span> La décision vous appartient.<br />
				</p>
				<p>
					Vous commencerez en tant que caporal et vous aurez sous vos ordres votre 1er grouillot.<br />
					Au fur et à mesure de vos actions, votre capacité à commander se révéleront. Votre montée en grade vous permettra d'avoir encore plus de grouillots sous vos ordres.<br />
					Mais pour cela, il vous faudra utiliser tous les moyens disponibles : Relief du terrain, protection des bâtiments, achats d'armes et d'objets ainsi que le train à vapeur pour survivre au milieu du camp adverse et des bêtes sauvages.<br /><br />
					Alors, quel camp allez-vous faire gagner ?
					</b>
				</p>
				<!-- texte à repenser et non affiché. Catégorie description du jeu -->
				<p class='d-none'>
					<b>Nord vs Sud</b> est un jeu de stratégie sur Internet largement multi-joueurs.<br />
					Chaque joueur commande un bataillon de quelques unités : <b>cavaliers, infanteries, soigneurs, artillerie, chiens militaires</b>.<br />
					<b>Deux camps</b> s'affrontent depuis la nuit des temps pour des motifs qui ont fini par être oubliés, <span class='text-primary fw-bold'>les bleus (Nord)</span> et <span class='text-danger fw-bold'>les rouges (Sud)</span>.<br />
					Ceux-ci, pour être plus efficaces, ont procédé au regroupement des bataillons en <b>compagnies</b> pouvant aller jusqu'à 80 unités.<br />
					Le Nord a une organisation plutôt hiérarchique avec un Comité Stratégique qui définit les ordres de mission, tandis que le Sud procède avec une plus grande autonomie des compagnies.<br />
					Certains joueurs restent indépendants, ne veulent pas profiter des avantages tactiques et économiques (achat d'équipements) qu'apportent l'enrôlement dans une compagnie, afin de conserver une liberté d'action.<br />
					Car un autre des avantages des compagnies est de réaliser des actions coordonnées, que leurs unités agissent avec simultanéité, pour attaquer les adversaires. Il faut en effet plusieurs attaques avant de réussir à « capturer » une unité.<br />
					Cette « capture » consiste à renvoyer l'unité remontée à bloc dans l'un des bâtiments de son camp.<br />
					Il lui faudra donc ensuite du temps pour rejoindre le front et retrouver le reste de son bataillon.<br />
					L'objectif du jeu est donc de repousser les adversaires en « capturant » leurs unités et de détruire leurs bâtiments, ce qui apporte des points de victoire au camp.<br />
					Dans certains cas il est également possible de capturer un bâtiment ennemi pour l'inclure dans son camp.<br />
					La bataille se termine lorsque un camp est parvenu à <b>1000 points de victoire</b>. Une autre bataille sera donc lancée sur la carte suivante décidée par l'état major du camp vainqueur.<br />
					La surface de jeu (carte) est assez vaste et chaque camp n'en connaît que les zones qu'il a pu visiter.	
				</p>
			</div>
		</div>
	</div>
	<aside class='col-12 col-sm px-3'>
			<a href="presentation.php" class='link-light'>Présentation du jeu</b></a>
			<hr />
			<a href="regles/regles.php" class='link-light'>Règles</b></a>
			<hr />
			<a href="faq.php" class='link-light'>FAQ</b></a>
			<hr />
			<a href="/forum" class='link-light'>Le Forum</b></a>
			<hr />
			<a href="jeu/classement.php" class='link-light'>Les classements</b></a>
			<hr />
			<a href="jeu/statistiques.php" class='link-light'>Les statistiques</b></a>
			<hr />
			<a href="credits.php" class='link-light'>Crédits</b></a>
	</aside>
</div>
		
<?php $content = ob_get_clean(); ?>

<?php require('layouts/app.php'); ?>