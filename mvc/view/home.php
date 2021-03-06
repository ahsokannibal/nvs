<?php
$title = "";

/* ---Content--- */
ob_start();
?>
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
					Persos actifs : <br/><span class='text-primary fw-bold'>nordistes : <?= $nb_persos_nord_actifs; ?></span> / <span class='text-danger fw-bold'>sudistes : <?= $nb_persos_sud_actifs; ?></span>
				</p>
				<p>
					Vous voulez en d??coudre ?<br/>
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
					Aucune nouvelle... esp??rons que nos soldats vont bien !
				</p>
				<?php endif; ?>
			</div>
		</div>
		<div class='row justify-content-center'>
			<div class='col-11 col-sm shadow bg-gray-300 rounded-3 p-4 m-2'>
				<!-- texte ?? repenser ??ventuellement -->
				<h3>Synopsis</h3>
				<p>
					Am??rique du Nord, printemps 1861. Bienvenue dans la lutte qui oppose le <span class='text-primary fw-bold'>Nord</span> et le <span class='text-danger fw-bold'>Sud</span>.<br />
					Nous sommes ?? la fin du 19??me si??cle et depuis des ann??es, les tensions montent entre l'arm??e de l'Union, command??e par <span class='text-primary fw-bold'>Abraham Lincoln</span>, et l'arm??e des Etats conf??d??r??s, command??e par <span class='text-danger fw-bold'>Jefferson Davis</span>.<br/>
					<br/>
					le 12 avril, La guerre est d??clar??e.<br/>
					Une attaque de l'arm??e des ??tats conf??d??r??s sur le Fort Sumter, ?? Charleston (Caroline du Sud), lance les hostilit??s. Vous vous retrouvez malgr?? vous dans cette tourmente et devez choisir un camp.<br/>
					<span class='text-primary fw-bold'>Unionniste</span> ou <span class='text-danger fw-bold'>conf??d??r?? ?</span> La d??cision vous appartient.<br />
				</p>
				<p>
					Vous commencerez en tant que caporal et vous aurez sous vos ordres votre 1er grouillot.<br />
					Au fur et ?? mesure de vos actions, votre capacit?? ?? commander se r??v??leront. Votre mont??e en grade vous permettra d'avoir encore plus de grouillots sous vos ordres.<br />
					Mais pour cela, il vous faudra utiliser tous les moyens disponibles : Relief du terrain, protection des b??timents, achats d'armes et d'objets ainsi que le train ?? vapeur pour survivre au milieu du camp adverse et des b??tes sauvages.<br /><br />
					Alors, quel camp allez-vous faire gagner ?
					</b>
				</p>
				<!-- texte ?? repenser et non affich??. Cat??gorie description du jeu -->
				<p class='d-none'>
					<b>Nord vs Sud</b> est un jeu de strat??gie sur Internet largement multi-joueurs.<br />
					Chaque joueur commande un bataillon de quelques unit??s : <b>cavaliers, infanteries, soigneurs, artillerie, chiens militaires</b>.<br />
					<b>Deux camps</b> s'affrontent depuis la nuit des temps pour des motifs qui ont fini par ??tre oubli??s, <span class='text-primary fw-bold'>les bleus (Nord)</span> et <span class='text-danger fw-bold'>les rouges (Sud)</span>.<br />
					Ceux-ci, pour ??tre plus efficaces, ont proc??d?? au regroupement des bataillons en <b>compagnies</b> pouvant aller jusqu'?? 80 unit??s.<br />
					Le Nord a une organisation plut??t hi??rarchique avec un Comit?? Strat??gique qui d??finit les ordres de mission, tandis que le Sud proc??de avec une plus grande autonomie des compagnies.<br />
					Certains joueurs restent ind??pendants, ne veulent pas profiter des avantages tactiques et ??conomiques (achat d'??quipements) qu'apportent l'enr??lement dans une compagnie, afin de conserver une libert?? d'action.<br />
					Car un autre des avantages des compagnies est de r??aliser des actions coordonn??es, que leurs unit??s agissent avec simultan??it??, pour attaquer les adversaires. Il faut en effet plusieurs attaques avant de r??ussir ?? ?? capturer ?? une unit??.<br />
					Cette ?? capture ?? consiste ?? renvoyer l'unit?? remont??e ?? bloc dans l'un des b??timents de son camp.<br />
					Il lui faudra donc ensuite du temps pour rejoindre le front et retrouver le reste de son bataillon.<br />
					L'objectif du jeu est donc de repousser les adversaires en ?? capturant ?? leurs unit??s et de d??truire leurs b??timents, ce qui apporte des points de victoire au camp.<br />
					Dans certains cas il est ??galement possible de capturer un b??timent ennemi pour l'inclure dans son camp.<br />
					La bataille se termine lorsque un camp est parvenu ?? <b>1000 points de victoire</b>. Une autre bataille sera donc lanc??e sur la carte suivante d??cid??e par l'??tat major du camp vainqueur.<br />
					La surface de jeu (carte) est assez vaste et chaque camp n'en conna??t que les zones qu'il a pu visiter.	
				</p>
			</div>
		</div>
	</div>
	<aside class='col-12 col-sm px-3'>
			<a href="presentation.php" class='link-light'>Pr??sentation du jeu</b></a>
			<hr />
			<a href="regles/regles.php" class='link-light'>R??gles</b></a>
			<hr />
			<a href="faq.php" class='link-light'>FAQ</b></a>
			<hr />
			<a href="http://forum.persee.ovh/" class='link-light'>Le Forum</b></a>
			<hr />
			<a href="jeu/classement.php" class='link-light'>Les classements</b></a>
			<hr />
			<a href="jeu/statistiques.php" class='link-light'>Les statistiques</b></a>
			<hr />
			<a href="credits.php" class='link-light'>Cr??dits</b></a>
	</aside>
</div>
		
<?php $content = ob_get_clean(); ?>

<?php require('layouts/guest.php'); ?>