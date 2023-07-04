<?php
$title = "Créer une carte - Administration";

/* ---Header--- */
ob_start();
?>
<div class="row justify-content-center text-center">
	<div class="col col-md-6">
		<h2 class='mb-3'>Créer une carte</h2>
		<nav>
			<a class="btn btn-primary me-2" href="?">Retour aux cartes</a>
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
<?php if(isset($emptyMaps) && !empty($emptyMaps)): ?>
<div class="row row-cols-1 row-cols-md-2 g-4">
	<div class="col">
		<div class="card h-100 shadow">
			<div class="card-body">
				<h5 class="card-title mb-3">Créer une carte à partir d'une image</h5>
				<form class='row' name='fromImg' method='post' action='?action=store' enctype="multipart/form-data">
					<div class='col-12 mb-3'>
						<select class="form-select<?php if(isset($errors['fromImg_choix_carte'])):?> text-danger is-invalid<?php endif ?>" aria-label="Sélection de la carte" name="fromImg_choix_carte">
							<OPTION value="0" disabled selected>Choix de la carte</option>
								<?php foreach($emptyMaps as $map):?>
									<option value="<?= $map ?>" <?php if(isset($old_input['fromImg_choix_carte']) && $old_input['fromImg_choix_carte'] == $map):?> selected <?php endif ?>>carte <?= $map ?></option>
								<?php endforeach ?>
						</select>
						<?php if(isset($errors['fromImg_choix_carte'])):?>
						<div class='mt-2 alert alert-danger'>
							<ul class='mb-0'>
							<?php foreach($errors['fromImg_choix_carte'] as $message): ?>
								<li><?= $message ?></li>
							<?php endforeach ?>
							</ul>
						</div>
						<?php endif ?>
					</div>
					<div class="mb-3">
						<label for="fromImg_img" class="form-label">Choisir une image</label>
						<input type="hidden" name="MAX_FILE_SIZE" value="2000000" />
						<input class="form-control<?php if(isset($errors['fromImg_img'])):?> text-danger is-invalid<?php endif ?>" type="file" id="fromImg_img" name='fromImg_img' accept="image/png">
						<small class='text-muted'>Dimensions maximum: 201x201</small>,
						<small class='text-muted'>2Mo maximum</small>
						<?php if(isset($errors['fromImg_img'])):?>
						<div class='mt-2 alert alert-danger'>
							<ul class='mb-0'>
							<?php foreach($errors['fromImg_img'] as $message): ?>
								<li><?= $message ?></li>
							<?php endforeach ?>
							</ul>
						</div>
						<?php endif ?>
					</div>
					<div class='col-12'>
						<input type="hidden" id="create_map" name="create_map" value="fromImg" />
						<button type='submit' class='btn btn-primary'>Créer la carte</button>
					</div>
				</form>
			</div>
		</div>
	</div>
		<div class="col">
		<div class="card h-100 shadow">
			<div class="card-body">
				<h5 class="card-title mb-3">Créer une carte vierge</h5>
				<form class='row' name='virgin' method='post' action='?action=store'>
					<div class='col-12 mb-3'>
						<select class="form-select <?php if(isset($errors['virgin_choix_carte'])):?>text-danger is-invalid<?php endif ?>" aria-label="Sélection de la carte" name="virgin_choix_carte">
							<option value="0" disabled selected>Choix de la carte</option>
								<?php foreach($emptyMaps as $map):?>
									<option value="<?= $map ?>" <?php if(isset($old_input['virgin_choix_carte']) && $old_input['virgin_choix_carte'] == $map):?> selected <?php endif ?>>carte <?= $map ?></option>
								<?php endforeach ?>
						</select>
						<?php if(isset($errors['virgin_choix_carte'])):?>
						<div class='mt-2 alert alert-danger'>
							<ul class='mb-0'>
							<?php foreach($errors['virgin_choix_carte'] as $message): ?>
								<li><?= $message ?></li>
							<?php endforeach ?>
							</ul>
						</div>
						<?php endif ?>
					</div>
					<div class='col-md-5'>
						<div class='row'>
							<div class='col-11 mb-3'>
								<label class='form-label<?php if(isset($errors['virgin_creation_x_max'])):?> fw-bold text-danger <?php endif ?>' for='virgin_creation_x_max'>X Max : </label>
								<input class='form-control<?php if(isset($errors['virgin_creation_x_max'])):?> is-invalid<?php endif ?>' type='number' min='1' name='virgin_creation_x_max' id='virgin_creation_x_max' value='<?= $old_input['virgin_creation_x_max'] ?? ''?>'>
								<?php if(isset($errors['virgin_creation_x_max'])):?>
								<div class='mt-2 alert alert-danger'>
									<ul class='mb-0'>
									<?php foreach($errors['virgin_creation_x_max'] as $message): ?>
										<li><?= $message ?></li>
									<?php endforeach ?>
									</ul>
								</div>
								<?php endif ?>
							</div>
							<div class='col-11'>
								<label class='form-label <?php if(isset($errors['virgin_creation_y_max'])):?> fw-bold text-danger<?php endif ?>' for='virgin_creation_y_max'>Y Max : </label>
								<input class='form-control <?php if(isset($errors['virgin_creation_y_max'])):?> is-invalid<?php endif ?>' type='number' min='1' name='virgin_creation_y_max' id='virgin_creation_y_max' value='<?= $old_input['virgin_creation_y_max'] ?? '' ?>'>
								<?php if(isset($errors['virgin_creation_y_max'])):?>
								<div class='mt-2 alert alert-danger'>
									<ul class='mb-0'>
									<?php foreach($errors['virgin_creation_y_max'] as $message): ?>
										<li><?= $message ?></li>
									<?php endforeach ?>
									</ul>
								</div>
								<?php endif ?>
							</div>
						</div>
					</div>
					<div class='col'>
						<label class="form-check-label mb-2" for="virgin_terrain">
							terrain par défaut
						</label><br/>
						<?php if(isset($grounds) && !empty(isset($grounds))): ?>
							<?php foreach($grounds as $id => $composants): ?>
							<div class="form-check form-check-inline">
								<input class="form-check-input mt-2" type="radio" name="virgin_terrain" id="<?= $composants[0] ?>" value='<?= $id ?>' <?php if(isset($old_input['virgin_terrain']) && $old_input['virgin_terrain'] == $id ):?> checked <?php endif ?>>
								<label class="form-check-label" for="<?= $composants[0] ?>">
									<span class='visually-hidden'><?= $composants[0] ?></span> <img src="../fond_carte/<?= $id  ?>.gif" width="34" height="34" alt='<?= $composants[0] ?>'>
								</label>
							</div>
							<?php endforeach ?>
						<?php else: ?>
							<p class='alert alert-warning'>
								Aucun terrain à choisir. Le terrain par défaut sera la plaine.
							</p>
						<?php endif ?>
						<?php if(isset($errors['virgin_terrain'])):?>
						<div class='mt-2 alert alert-danger'>
							<ul class='mb-0'>
							<?php foreach($errors['virgin_terrain'] as $message): ?>
								<li><?= $message ?></li>
							<?php endforeach ?>
							</ul>
						</div>
						<?php endif ?>
					</div>
					<div class='col-12 mt-3'>
						<input id="create_map" name="create_map" type="hidden" value="virgin" />
						<button type='submit' class='btn btn-primary'>Créer la carte</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
<?php else: ?>
<div class="row">
	<div class='col-12'>
		<div class='alert alert-info'>
			<svg xmlns="http://www.w3.org/2000/svg" class="warning-icon-lg float-start me-2 mt-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
			  <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
			</svg>
			<p class='mb-0'>
			Toutes les cartes prévues en base ont été créées.<br/>
			Pour ajouter une nouvelle carte, contactez l'administrateur.
			</p>
		</div>
	</div>
</div>
<?php endif ?>

<?php $content = ob_get_clean(); ?>

<?php require('../mvc/view/layouts/app.php'); ?>