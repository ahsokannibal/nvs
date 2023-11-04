<?php
$title = "Administration - Objets";

/* ---Header--- */
ob_start();
?>
<div class="row justify-content-center text-center">
	<div class="col col-md-6">
		<h2 class='mb-3'>Administration - Objets</h2>
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
<div class="row">
	<div class="col">
		<div class="card shadow">
			<div class='card-header'>
				<h4>Gestion des objets</h4>
			</div>
			<div class="card-body">
				<?php if(isset($items) && !empty($items)): ?>
				<div class='d-flex my-3'>
					<div class='me-auto'>
						<a class='btn btn-success mb-4' href='?action=create' >Créer un objet</a>
					</div>
				</div>
				<?php endif ?>
				<h5 class="card-title">Liste des objets existants</h5>
				<?php if(isset($items) && !empty($items)): ?>
				<table class="table align-middle table-striped">
					<caption>Objets créés</caption>
					<thead class='table-light'>
						<tr>
							<th scope="col">Nom</th>
							<th class='w-25' scope="col">Description</th>
							<th class='text-center' scope="col">Caractéristiques</th>
							<th class='px-4' scope="col">Bonus/Malus</th>
							<th class='text-center' scope="col">Options</th>
							<th scope="col"></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach($items as $item): ?>
						<tr>
							<th scope="row">
								<?php echo (empty($item->nom_objet))?'<span class="text-muted">"sans nom"</span>':$item->nom_objet;?><br>
								<small class='text-muted'>catégorie : <?= $item->type_objet?></small><br>
								<img class="img-fluid w-50" src="../public/img/items/<?= $item->image_objet?>">
							</th>
							<td><?= $item->description_objet?></td>
							<td>
								<ul class='list-group list-group-flush'>
									<li class='list-group-item bg-transparent'><span class='fw-semibold'>Poids : </span><?= str_replace('.',',',$item->poids_objet)?> kg(s)</li>
									<li class='list-group-item bg-transparent'><span class='fw-semibold'>Prix : </span><?=$item->coutOr_objet?> or</li>
									<li class='list-group-item bg-transparent'><span class='fw-semibold'>Coût en PA : </span><?=$item->coutPa_objet?> PA</li>
									<li class='list-group-item bg-transparent'><span class='fw-semibold'>Portée : </span><?=$item->portee_objet?> case(s)</li>
								</ul>
							</td>
							<td class=''>
								<ul class='list-group list-group-flush'>
								<?= ($item->bonusPerception_objet!='0')?'<li class="list-group-item bg-transparent"><span class="fw-semibold">Perception : </span>'.$item->bonusPerception_objet.'</li>':''?>
								<?= ($item->bonusRecup_objet!='0')?'<li class="list-group-item bg-transparent"><span class="fw-semibold">Récupération : </span>'.$item->bonusRecup_objet.'</li>':''?>
								<?= ($item->bonusPv_objet!='0')?'<li class="list-group-item bg-transparent"><span class="fw-semibold">PV : </span>'.$item->bonusPv_objet.'</li>':''?>
								<?= ($item->bonusPm_objet!='0')?'<li class="list-group-item bg-transparent"><span class="fw-semibold">PM : </span>'.$item->bonusPm_objet.'</li>':''?>
								<?= ($item->bonusDefense_objet!='0')?'<li class="list-group-item bg-transparent"><span class="fw-semibold">Défense : </span>'.$item->bonusDefense_objet.'</li>':''?>
								<?= ($item->bonusPrecisionCac_objet!='0')?'<li class="list-group-item bg-transparent"><span class="fw-semibold">Précision CaC : </span>'.$item->bonusPrecisionCac_objet.'</li>':''?>
								<?= ($item->bonusPrecisionDist_objet!='0')?'<li class="list-group-item bg-transparent"><span class="fw-semibold">Précision dist. : </span>'.$item->bonusPrecisionDist_objet.'</li>':''?>
								<?= ($item->bonusPA_objet!='0')?'<li class="list-group-item bg-transparent"><span class="fw-semibold">PA : </span>'.$item->bonusPA_objet.'</li>':''?>
							</td>
							<td class=''>
								<ul class='list-group list-group-flush'>
									<li class='list-group-item bg-transparent'><span class='fw-semibold'>Alcoolisé : </span><?= ($item->contient_alcool!='0')?'Oui':'Non'?></li>
									<li class='list-group-item bg-transparent'><span class='fw-semibold'>Echangeable : </span><?= ($item->echangeable!='0')?'Oui':'Non'?></li>
									<li class='list-group-item bg-transparent'><span class='fw-semibold'>Déposable : </span><?= ($item->deposable!='0')?'Oui':'Non'?></li>
									<li class='list-group-item bg-transparent'><span class='fw-semibold'>Achetable : </span><?= ($item->achetable!='0')?'Oui':'Non'?></li>
								</ul>
							</td>							
							<td class=''>
									<a class='w-100 btn btn-primary mb-2' href='?action=edit&id=<?= $item->id_objet?>'>Editer</a>
									<form name='delete_item' method='post' action='?action=delete' disabled>
										<input type="hidden" name="id" value="<?= $item->id_objet?>">
										<input type="hidden" name="_method" value="DELETE">
										<button type='button' class='w-100 btn btn-danger' disabled>Supprimer</button>
									</form>
							</td>
						</tr>
						<?php endforeach ?>
					</tbody>
				</table>
				<?php else: ?>
					<p class=''>Aucun objet créé. <a class='btn btn-success ms-4' href='?action=create' >Créer un objet</a></p>
				<?php endif ?>
			</div>
		</div>
	</div>
</div>

<?php $content = ob_get_clean(); ?>

<?php require('../mvc/view/layouts/app.php'); ?>