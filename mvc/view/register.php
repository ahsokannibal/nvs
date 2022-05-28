<?php
$title = "Inscription";

/* ---Content--- */
ob_start();
?>
<div class='row justify-content-center'>
<?php if(isset($error)):?>
	<div class='col-md-4 alert alert-danger'>
		<?= $error ?>
	</div>
<?php endif; ?>
</div>
<div class='row justify-content-center'>
	<div class='col-11 col-lg-8'>
		<form method="post" action="inscription.php" class='row justify-content-center'>
			<div class='col-12 col-md-6'>
				<h1 class=''>INSCRIPTION</h1>
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
						<span class='text-primary fw-bold'>Joueurs actifs au Nord : <?= $nbb ?></span> / <span class='text-danger fw-bold'>Joueurs actifs au Sud : <?= $nbr?> </span>
					</div>
				</div>
				<div class="form-check mb-3">
					<input class="form-check-input" type="checkbox" id="cgu" name="cgu">
					<label class="form-check-label" for="cgu">
						En cochant cette case je confirme avoir lu les <a href='CGU.pdf'>Conditions générales d'utilisation</a>
					</label>
				</div>
			</div>
			<div class='col-12'>
				<h3>Charte des joueurs</h3>
				<div class='bg-light rounded shadow p-3 mb-3'>
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
					<input class="form-check-input" type="checkbox" id="charte" name="charte">
					<label class="form-check-label" for="charte">
						En cochant cette case je confirme accepter sans réserve cette charte.
					</label>
				</div>
				<input name="creation" type="hidden" value="ok"><!-- A quoi sert cet input ? -->
				<input class='btn btn-light' type="submit" name="creer" name="id" value="S'incrire">
			</div>
		</form>
	</div>	
</div>
<div class='row justify-content-center mt-3'>
	<div class='col-6'>
	<?php // est ce que ce code est vraiment nécessaire ?
		if (isset ($_GET["voir"])){
			$i = 0;
			$sql = "SELECT nom_perso FROM perso";
			$resultat = $mysqli->query($sql);
			
			if(isset($resultat)):
			?>
			<p>
				<span class='fw-bold'>Personnages(s) existant(s):</span><br/>
				(choisir un nom différent)<br/><br/>
			<?php
				foreach($resultat as $tab){
					$i++;
					echo $tab['nom_perso'];
					if($i>=0 && $i<$resultat->num_rows){
						echo ' - ';
					};
				};
			?>
			<br/><br/>Masquer la liste :<br/>
			<a href="inscription.php"><img src="images/b_ok.gif"></a>
			</p>
			<?php
			endif;
		};
	?>
	</div>
</div>
	
<?php $content = ob_get_clean(); ?>

<?php require('layouts/guest.php'); ?>
