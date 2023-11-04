<?php
$title = "Administration";

/* ---Header--- */
ob_start();
?>
<div class="row justify-content-center text-center">
	<div class="col col-md-6">
		<h2 class='mb-3'>Administration</h2>
		<nav>
			<a class="btn btn-primary" href="jouer.php">Retour au jeu</a>
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
<div class="row row-cols-1 row-cols-md-3 g-4">
	<div class="col">
		<div class="card h-100 shadow">
			<div class="card-body">
				<h5 class="card-title">Triche et contrôles</h5>
				<ul class="list-group list-group-flush">
					<li class="list-group-item"><a class='btn btn-warning' href='admin_triche.php'>Vérification multi-compte</a></li>
					<li class="list-group-item"><a class='btn btn-warning' href='admin_tentative_triche.php' >Logs tentatives de triche</a></li>
					<li class="list-group-item"><a class='btn btn-warning' href='admin_log_animation.php' >Logs animation</a></li>
					<li class="list-group-item"><a class='btn btn-warning' href='admin_triche.php' >Vérification triche</a></li>
					<li class="list-group-item"><a class='btn btn-warning' href='admin_multi.php' >Tableau des multis déclarés</a></li>
					<li class="list-group-item"><a class='btn btn-warning' href='admin_babysitte.php' >Tableau des babysittes déclarés</a></li>
				</ul>
			</div>
		</div>
	</div>
	<div class="col">
		<div class="card h-100 shadow">
			<div class="card-body">
				<h5 class="card-title">Personnages</h5>
				<ul class="list-group list-group-flush">
					<li class="list-group-item"><a class='btn btn-primary' href='admin_teleporte.php'>Téléporter un perso sur la carte</a></li>
					<li class="list-group-item"><a class='btn btn-primary' href='admin_teleporte_bat.php'>Téléporter un perso dans un batiment</a></li>
					<li class="list-group-item"><a class='btn btn-primary' href='admin_acces.php'>Donner des accès à un perso</a></li>
					<li class="list-group-item"><a class='btn btn-primary' href='admin_perso.php'>Administration des perso</a></li>
				</ul>
				<h5 class="card-title mt-4">Compagnies</h5>
				<ul class="list-group list-group-flush">
					<li class="list-group-item"><a class='btn btn-primary' href='admin_compagnies.php'>Administration des compagnies</a></li>
				</ul>
				<h5 class="card-title mt-4">Bâtiments</h5>
				<ul class="list-group list-group-flush">
					<li class="list-group-item"><a class='btn btn-primary' href='admin_batiments.php'>Administration des bâtiments</a></li>
				</ul>
				</ul>
				<h5 class="card-title mt-4">Objets</h5>
				<ul class="list-group list-group-flush">
					<li class="list-group-item"><a class='btn btn-primary' href='admin_items.php'>Gestion des objets</a></li>
				</ul>
			</div>
		</div>
	</div>
	<div class="col">
		<div class="card h-100 shadow">
			<div class="card-body">
				<h5 class="card-title">Jeu et carte</h5>
				<ul class="list-group list-group-flush">
					<li class="list-group-item"><a class='btn btn-warning' href='admin_map.php' >Gestion des cartes</a></li>
					<li class="list-group-item"><a class='btn btn-warning' href='admin_zones_pnj.php' >Zones respawn PNJ</a></li>
					<li class="list-group-item"><a class='btn btn-warning' href='admin_changement_carte.php' >Changement de carte</a></li>
				</ul>
				<h5 class="card-title mt-4">maintenance</h5>
				<ul class="list-group list-group-flush">
					<?php if ($maintenance_mode['valeur_config'] == '1') {
						$MaintenanceBtnMsg = 'Passer le jeu en mode Maintenance';
						$MaintenanceStyle = 'btn btn-danger';
						$mode = 'close';
					}else{
						$MaintenanceBtnMsg = 'Ouvrir le jeu';
						$MaintenanceStyle = 'btn btn-success';
						$mode = 'open';
					}
						?>
					<li class="list-group-item">
						<a class='<?= $MaintenanceStyle ?>' href='?mode_jeu=<?= $mode?>'><?= $MaintenanceBtnMsg ?></a>
						<h6 class='mt-4 fw-bold'>Modifier le message de maintenance</h6>
						<form action='../jeu/admin_nvs.php' method="post">
							<div class="mb-3">
								<label for="maintenance_msg" class="form-label">Message</label>
								<textarea class="form-control" id="maintenance_msg" name='maintenance_msg' rows="5" aria-describedby="msgHelp"><?= $maintenance_mode['msg']?></textarea>
								<div id="msgHelp" class="form-text">conseil : ne pas écrire un message trop long</div>
							</div>
							<button class='btn btn-secondary'>Mettre à jour</button>
						</form>
					</li>
				</ul>
			</div>
		</div>
	</div>
</div>

<div class="row">

</div>

<?php $content = ob_get_clean(); ?>

<?php require('../mvc/view/layouts/app.php'); ?>