<?php
$title = "";

/* ---Content--- */
ob_start();
?>
<?php if($maintenance_mode['valeur_config']!=1): ?>
<div class="row justify-content-center">
	<div class='alert alert-warning fw-bold text-center col'>
		<svg xmlns="http://www.w3.org/2000/svg" class="me-2 mb-2 maintenance-icon-lg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
			<circle cx="12" cy="12" r="7.2490396"/>
			<path stroke-linecap="round" stroke-linejoin="round" d="M 11.076105,0.45483854 10.198151,2.9797418 7.4122271,4.322478 4.489785,3.182112 3.1821151,4.4897818 4.3482005,6.8963676 3.3356861,9.8157842 0.45484167,11.076102 v 1.847795 l 2.52490323,0.877955 1.3427363,2.785919 -1.1403661,2.922447 1.3076699,1.30767 2.4070084,-1.165729 2.9189942,1.012158 1.2603134,2.880845 h 1.849572 l 0.877042,-2.524829 2.785013,-1.341268 2.922487,1.138823 1.30767,-1.30767 -1.165729,-2.407009 1.012364,-2.919037 2.880638,-1.260272 V 11.076106 L 21.020266,10.198211 19.687882,7.4068314 20.817891,4.4897818 19.510221,3.182112 17.104916,4.3477781 14.188302,3.3175139 12.925676,0.45483854 Z" />
			<path stroke-linecap="round" stroke-linejoin="round" d="m 13.822851,8.942861 v 3.443154 L 12,13.246804 10.177149,12.386015 V 8.942861 c -3.6457021,0.860788 -3.6457021,6.02552 0,7.747097 v 1.721577 c 1.822851,0.86079 1.822851,0.86079 3.645702,0 v -1.721577 c 3.645702,-1.721577 3.645702,-6.886309 0,-7.747097 z" />
		</svg>
		<div class='w-75 m-auto pb-3 maintenance-msg'>
			<?= $maintenance_mode['msg'] ?>
		</div>
	</div>
</div>
<?php endif; ?>
<div class="row justify-content-center">
	<div class="col-11 col-sm-3 mb-4 mb-sm-0">
		<div class='row pt-4'>
			<h4>
				<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 align-bottom" fill="none" viewBox="0 0 24 24" stroke="currentColor">
				  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
				</svg>
				Connexion
			</h4>
			<?php
				if (isset($_GET['nouveau_tour']) && $_GET['nouveau_tour'] == 'ok'):
			?>
				<p class='alert alert-danger fw-bold' role="alert">
					<svg xmlns="http://www.w3.org/2000/svg" class="" width='26' height='26' fill="none" viewBox="0 0 24 24" stroke="currentColor">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
					</svg>
					Nouveau tour
				</p>
			<?php
				endif;
			?>
			<form class='' action="login.php" method="post" name="login" id="login">
				<div class="mb-3">
					<label for="pseudo" class='form-label visually-hidden'>Pseudo</label>
					<input type="text" class="form-control form-control-sm" id="pseudo" name='pseudo' placeholder="Pseudo">
				</div>
				<div class="mb-3">
					<label for="pseudo" class='form-label visually-hidden'>Mot de Passe</label>
					<input type="password" class="form-control form-control-sm" id="password" name='password' placeholder="Mot de Passe">
				</div>
				<div class="mb-3">
					<label for="pseudo" class='form-label'>Etes-vous un robot ?</label>
					<div class=''>
						<a href='#' id='reload_captcha' class='mx-2'><img src="captcha.php" id='captcha'/></a>
						<input id='captcha_input' name="captcha" type="text" class="form-control form-control-sm mt-2" placeholder="Entrez le texte de l'image">
					</div>
				</div>
				<div class="mb-3">
					<input class='btn btn-light btn-sm' type="submit" name="Submit" value="Se connecter">
				</div>
				<div>
					<a href="mdp_perdu.php" class='text-light ml-2'>Mot de passe perdu ?</a>
				</div>
			</form>
		</div>
	</div>
	<div class="col-12 col-sm-7">
		<div class='row justify-content-center'>
			<div class='col-11 col-sm shadow bg-gray-300 rounded-3 p-4 mx-2 mb-3 h-22'>
				<h4>
					<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 rotate-20deg align-bottom" fill="none" viewBox="0 0 24 24" stroke="currentColor">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
					</svg>
					Inscription
				</h4>
				<p>
					<?= $nb_inscrit; ?> joueur(s) inscrit(s)<br />
					Dernier inscrit :<br/> <?= couleur_nation($clan_last_inscrit, $pseudo_last_inscrit); ?>
				</p>
				<p>
					Joueurs actifs : <br/><span class='text-primary fw-bold'>nordistes : <?= $nb_joueurs_nord_actifs; ?></span> / <span class='text-danger fw-bold'>sudistes : <?= $nb_joueurs_sud_actifs; ?></span>
				</p>
				<p>
					Vous voulez en découdre ?<br/>
					Engagez-vous soldat !<br/>
					<div class='mt-1'>
					<a href="inscription.php" class="btn btn-light">S'inscrire</b></a>
					</div>
				</p>
			</div>
			<div class='col-11 col-sm shadow bg-gray-300 rounded-3 p-4 mx-2 h-22 news'>
				<h4>
					<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 rotate-20deg align-bottom" fill="none" viewBox="0 0 24 24" stroke="currentColor">
					  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
					</svg>
					Les nouvelles du front
				</h4>
				<?php if($res_news):?>
					<?php foreach($res_news as $new): ?>
						<p>
							<?php
								$d = new DateTime($new['date']);
								echo $d->format('d-m-Y');
							?>
							<br/>
							<?= $new['contenu'] ?><br/>
							-----
						</p>
					<?php endforeach ?>
				<?php else: ?>
				<p>
					Aucune nouvelle... espérons que nos soldats vont bien !
				</p>
				<?php endif; ?>
			</div>
		</div>
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
			<a href="https://encyclopedie.nord-vs-sud.fr" class='link-light'>Règles</b></a>
			<hr />
			<a href="faq.php" class='link-light'>FAQ</b></a>
			<hr />
			<a href="http://forum.persee.ovh/" class='link-light'>Le Forum</b></a>
			<hr />
			<a href="jeu/classement.php" class='link-light'>Les classements</b></a>
			<hr />
			<a href="jeu/statistiques/statistiques.php" class='link-light'>Les statistiques</b></a>
			<hr />
			<a href="credits.php" class='link-light'>Crédits</b></a>
	</aside>
</div>
		
<?php $content = ob_get_clean(); ?>

<?php require('layouts/guest.php'); ?>
