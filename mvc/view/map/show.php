<?php
$title = "Carte stratégique";

/* ---Header--- */
ob_start();
?>
<div class="row justify-content-center text-center">
	<div class="col col-md-6">
		<h2 class='mb-3'>Carte stratégique</h2>
		<nav>
			<a class="btn btn-primary me-2" href="?page=carte">Retour aux cartes</a>
			<a class="btn btn-primary me-2" href="admin_nvs.php">Retour à l'administration</a>
			<a class="btn btn-light" href="jouer.php">Retour au jeu</a>
		</nav>
	</div>
</div>
<?php
$header = ob_get_clean();

/* ---Content--- */
ob_start();
?>
<?php if(isset($statut)&& !empty($statut)): ?>
<div class="row">
	<div class='col'>
		<div class='alert alert-<?= $statut['class'] ?>'>
			<svg xmlns="http://www.w3.org/2000/svg" class="warning-icon-lg me-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
			  <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
			</svg>
			<span class='align-middle'><?= $statut['message'] ?></span>
		</div>
	</div>
</div>
<?php endif ?>
<?php if(isset($carte) && !empty($carte)): ?>
	<?php if(isset($map)): ?>
	<div class="row justify-content-center">
		<div class='col text-center mt-4'>
			<svg width="100%" height="500" viewBox="0 0 200 200">
			<?php $i=0; for($y = 0; $y <= $dimensions->yMax; $y++): ?>
				<?php for($x = 0; $x <= $dimensions->xMax; $x++):?>
					<?php if($map[$i]['idPerso_carte']):
						if($map[$i]['idPerso_carte'] >= 50000 AND $map[$i]['idPerso_carte']<200000):
							$imgDir = 'buildings';
						elseif($map[$i]['idPerso_carte'] > 200000):
							$imgDir = 'pnj';
						else:
							$imgDir = 'persos';
						endif;
					?>
					<rect class="ground-svg-<?= strstr($map[$i]['fond_carte'],'.',true) ?>" width="1" height="1" x="<?= $x ?>" y="<?= $y ?>" />
					<?php else: ?>
					<rect class="ground-svg-<?= strstr($map[$i]['fond_carte'],'.',true) ?>" width="1" height="1" x="<?= $x ?>" y="<?= $y ?>" />
					<?php endif ?>
				<?php $i++ ?>
				<?php endfor ?>
			<?php endfor ?>
			</svg>
		</div>
	</div>
	<!--
	<div class="row justify-content-center">
		<div class='col text-center mt-4'>
			<table class="mx-auto bg-light">
				<tbody>
				<?php /* $i=0; for($y = 0; $y <= $dimensions->yMax; $y++): ?>
					<tr>
						<?php for($x = 0; $x <= $dimensions->xMax; $x++):?>
							<td class='tile-pixel ground-pixel-<?= strstr($map[$i]['fond_carte'],'.',true) ?>'>
								<?php if($map[$i]['idPerso_carte']):
									if($map[$i]['idPerso_carte'] >= 50000 AND $map[$i]['idPerso_carte']<200000):
										$imgDir = 'buildings';
									elseif($map[$i]['idPerso_carte'] > 200000):
										$imgDir = 'pnj';
									else:
										$imgDir = 'persos';
									endif;
								?>
								<div class='tile-pixel-perso'>
								.
								</div>
								<?php endif ?>
							</td>
							<?php $i++ ?>
						<?php endfor ?>
					</tr>
				<?php endfor */?>
				</tbody>
			</table>
		</div>
	</div>
	<?php endif ?>
<?php endif ?>
<?php $content = ob_get_clean(); ?>

<?php require('../mvc/view/layouts/app.php'); ?>