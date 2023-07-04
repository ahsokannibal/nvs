<?php
$title = "Cartes - Administration";

/* ---Header--- */
ob_start();
?>
<div class="row justify-content-center text-center">
	<div class="col col-md-6">
		<h2 class='mb-3'>Administration - cartes</h2>
		<nav>
			<a class="btn btn-primary me-2" href="admin_nvs.php">Retour à l'administration</a>
			<a class="btn btn-primary" href="jouer.php">Retour au jeu</a>
		</nav>
	</div>
</div>
<?php
$header = ob_get_clean();

/* ---Content--- */
ob_start();
?>
<?php if(isset($error)): ?>
<p class='alert alert-danger'>
	<?= $error ?>
</p>
<?php endif; ?>
<?php if(isset($_SESSION['flash'])&& !empty($_SESSION['flash'])): ?>
<div class="row">
	<div class='col'>
		<div class='alert alert-<?= $_SESSION['flash']['class'] ?>'>
			<svg xmlns="http://www.w3.org/2000/svg" class="warning-icon-lg me-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
			  <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
			</svg>
			<span class='align-middle'><?= $_SESSION['flash']['message'] ?></span>
		</div>
	</div>
</div>
<?php endif ?>
<div class="row row-cols-1 row-cols-md-2 g-4">
	<div class="col">
		<div class="card shadow">
			<div class='card-header'>
				<h3>Gestion des cartes</h3>
			</div>
			<div class="card-body">
				<a class='btn btn-success mb-4' href='?action=create' >Créer une carte</a>
				<h5 class="card-title">Liste des cartes créées</h5>
				<ul class='list-group'>
					<?php if(isset($maps) && !empty($maps)): ?>
					<?php foreach($maps as $id): ?>
					<li class='list-group-item d-flex flex-wrap'>
						<div class='me-4'>carte n°<?= $id ?></div>
						<!--<img class='me-4' src="" width="34" height="34" alt='miniature carte n°{{$id}}'>-->
						<a href='../creation_carte/utils_carte.php?id=<?= $id ?>' class='mx-3 btn btn-primary'>Editer</a>
						<?php if($id==1 && $dispo==1): ?>
							<button class='btn btn-danger' disabled>Supprimer</button>
							<small class='mt-2'>vous devez mettre le jeu en maintenance pour supprimer la carte principale.<br>Attention, toute suppression de la carte n°1 réinitialise le jeu (sauf les persos)</small>
						<?php else: ?>
						<form class='row' name='delete_map' method='post' action='?action=delete'>
							<input type="hidden" name="id" value="<?= $id ?>">
							<input type="hidden" name="_method" value="DELETE">
							<div class='col-12'>
								<button type='submit' class='btn btn-danger'>Supprimer</button>
							</div>
						</form>
						<?php endif; ?>
					</li>
					<?php endforeach ?>
					<?php else: ?>
					<li class='list-group-item list-group-item-secondary'>Aucune carte créée. <button class='ms-3 btn btn-success'>Créer une carte</button></li>
					<?php endif ?>
				</ul>
			</div>
		</div>
	</div>
	<div class="col">
		<div class="card h-100 shadow">
			<div class='card-header'>
			<h3>Changement de carte</h3>
			</div>
			<div class="card-body">
				<h5 class="card-title">Vote pour la nouvelle carte</h5>
				<p class='card-text'>
					Aucun vote en cours.<br/>
					Aucun changement demandé.
				</p>
			</div>
			<div class='card-footer'>
				<a href='' class='btn btn-primary me-3 disabled'>Changer de carte</a>
				<a href='' class='btn btn-secondary disabled'>Réinitialiser les votes</a>
			</div>
		</div>
	</div>
</div>

<?php $content = ob_get_clean(); ?>

<?php require('../mvc/view/layouts/app.php'); ?>