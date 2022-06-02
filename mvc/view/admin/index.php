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
<?php if(isset($error)): ?>
<p class='alert alert-danger'>
	<?= $error ?>
</p>
<?php endif; ?>
<div class="row row-cols-1 row-cols-md-3 g-4">
	<div class="col">
		<div class="card h-100 shadow">
			<div class="card-body">
				<h5 class="card-title">Triche et contrôles</h5>
				<ul class="list-group list-group-flush">
					<li class="list-group-item"><a class='btn btn-warning' href='admin_triche.php'>Vérification multi-compte</a></li>
					<li class="list-group-item"><a class='btn btn-warning' href='admin_tentative_triche.php' >LOGS Tentatives de triche</a></li>
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
			</div>
		</div>
	</div>
	<div class="col">
		<div class="card h-100 shadow">
			<div class="card-body">
				<h5 class="card-title">Jeu et carte</h5>
				<ul class="list-group list-group-flush">
					<li class="list-group-item"><a class='btn btn-warning' href='?page=carte' >Gestion des cartes</a></li>
					<li class="list-group-item"><a class='btn btn-warning' href='admin_zones_pnj.php' >Zones respawn PNJ</a></li>
					<li class="list-group-item"><a class='btn btn-warning' href='admin_changement_carte.php' >Changement de carte</a></li>
				</ul>
				<h5 class="card-title mt-4">maintenance</h5>
				<ul class="list-group list-group-flush">
					<?php if ($maintenanceMode == '1') {
						$MaintenanceMessage = 'Passer le jeu en mode Maintenance';
						$MaintenanceStyle = 'btn btn-danger';
					}else{
						$MaintenanceMessage = 'Ouvrir le jeu';
						$MaintenanceStyle = 'btn btn-success';
					}
						?>
					<li class="list-group-item"><a class='<?= $MaintenanceStyle ?>' href='?page=admin&action=maintenance_mode'><?= $MaintenanceMessage ?></a></li>
				</ul>
			</div>
		</div>
	</div>
</div>

<div class="row">

</div>

<?php $content = ob_get_clean(); ?>

<?php require('../mvc/view/layouts/app.php'); ?>