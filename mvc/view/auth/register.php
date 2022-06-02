<?php
$title = "Inscription";

/* ---Content--- */
ob_start();
?>
<?php if(isset($error)):?>
<div class='row justify-content-center'>
	<div class='col-md-4 alert alert-danger'>
		<?= $error ?>
	</div>
</div>
<?php endif; ?>
<form method="post" action="?p=register&action=store" class='row justify-content-center'>
	<input name="_token" type="hidden" value="<?= $_SESSION['_token']?>">
	<div class='col-12 col-md-4 px-5 py-4 me-4 shadow bg-gray-300 rounded-3'>
		<h3 class=''>INSCRIPTION</h3>
		<div class="mb-3">
			<label for="nom_perso" class='form-label'>Nom de votre personnage:</label>
			<input type="text" class="form-control form-control" id="nom_perso" name='nom_perso' value="<?php if(isset($_SESSION['old_input']['nom_perso'])){echo $_SESSION['old_input']['nom_perso'];}?>" placeholder="Alfred le borgne">
		</div>
		<div class="mb-3">
			<label for="nom_bataillon" class='form-label'>Nom de votre bataillon:</label>
			<input type="text" class="form-control form-control" id="nom_bataillon" name='nom_bataillon' value="<?php if(isset($_SESSION['old_input']['nom_bataillon'])){echo $_SESSION['old_input']['nom_bataillon'];}?>" placeholder="Les souris en claquette">
		</div>
		<div class="mb-3">
			<label for="email_joueur" class='form-label'>Adresse email:</label>
			<input type="email" class="form-control form-control" id="email_joueur" name='email_joueur' value="<?php if(isset($_SESSION['old_input']['email_joueur'])){echo $_SESSION['old_input']['email_joueur'];}?>" placeholder="exemple@site.fr">
		</div>
		<div class="mb-3">
			<label for="mdp_joueur" class='form-label'>Mot de passe:</label>
			<input type="password" class="form-control form-control" id="mdp_joueur" name='mdp_joueur' placeholder="****">
		</div>
		<div class="mb-3">
			<label for="camp_perso" class='form-label'>Choisissez votre camp:</label>
			<select name="camp_perso" id='camp_perso' class="form-select" aria-label="sélection du camp">
				<option value="0" selected disabled>-- Choisir un camp --</option>
				<option value="1">Nord</option>
				<option value="2">Sud</option>
			</select>
			<div class='text-center mt-1'>
				<span class='text-primary fw-bold'>Nord : <?= $nord_actifs ?> Persos</span> / <span class='text-danger fw-bold'>Sud : <?= $sud_actifs?> Persos</span>
			</div>
		</div>
		<div class="form-check mb-3">
			<input class="form-check-input" type="checkbox" id="cgu" name="cgu" <?php if(isset($old_input['cgu']) && $old_input['cgu'] == 'on'):?> checked <?php endif ?>>
			<label class="form-check-label" for="cgu">
				En cochant cette case je confirme avoir lu les <a href='CGU.pdf'>Conditions générales d'utilisation</a>
			</label>
			<?php if(isset($errors['cgu'])):?>
			<div class='mt-2 alert alert-danger'>
				<ul class='mb-0'>
				<?php foreach($errors['cgu'] as $message): ?>
					<li><?= $message ?></li>
				<?php endforeach ?>
				</ul>
			</div>
			<?php endif ?>
		</div>
	</div>
	<div class='col-6 bg-white-200 p-4 me-4 shadow rounded-3'>
		<h3>Charte des joueurs</h3>
		<div class='bg-light rounded shadow p-3 my-3'>
			<p>
				« Nord vs Sud » est un jeu.<br/>
				je m'engage à :
			</p>
			<ul>
				<li>faire preuve de fair-play,</li>
				<li>accepter d’équilibrer les équipes si nécessaire,</li>
				<li>ne pas tricher,</li>
				<li>ne pas abuser d’éventuels bugs mais à les remonter,</li>
				<li>être bienveillant envers les joueurs plus ou moins impliqués,</li>
				<li>ne pas être toxique mais à faire en sorte que l'ambiance intra et inter-camp soit bonne, dans un esprit de camaraderie</li>
				<li>respecter les règles et lieux Role Play (RP) et Hors Role Play (HRP)</li>
			</ul>
			<p>
			Tout multicompte ou usage de VPN légitime est à déclarer publiquement.<br/>
			L'équipe d'animation sera prompte à sanctionner sévèrement tout contrevenant.
			</p>
		</div>
		<div class="form-check mb-3">
			<input class="form-check-input" type="checkbox" id="charte" name="charte" <?php if(isset($old_input['charte']) && $old_input['charte'] == 'on'):?> checked <?php endif ?>>
			<label class="form-check-label" for="charte">
				En cochant cette case je confirme accepter sans réserve cette charte.
			</label>
			<?php if(isset($errors['charte'])):?>
			<div class='mt-2 alert alert-danger'>
				<ul class='mb-0'>
				<?php foreach($errors['charte'] as $message): ?>
					<li><?= $message ?></li>
				<?php endforeach ?>
				</ul>
			</div>
			<?php endif ?>
		</div>
		<input class='btn btn-light mt-3' type="submit" name="register" id="register" value="S'incrire">
	</div>
</form>	
	
<?php $content = ob_get_clean(); ?>

<?php require('mvc/view/layouts/guest.php'); ?>