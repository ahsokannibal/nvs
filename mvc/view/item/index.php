<?php
$title = "Sac";

/* ---header--- */
ob_start();
?>
<div class='background-img sac'>
</div>
<div class="row justify-content-center bg-light bg-opacity-75 py-2 rounded">
	<div class='col mx-auto'>
		<img src="../images/<?php echo $image_sac; ?>" class='mx-auto d-block'>
		<p class='mt-2 text-center'>
			<a href="jouer.php" class='btn btn-light border'>Retour au jeu</a>
		</p>
	</div>
	<div class="col-10 mb-4 mb-sm-0">
		<h1>Mon sac</h1>
		<p>
			Le sac vous permet de transporter des objets et des armes que vous pouvez ensuite utiliser ou équiper.<br>
			<?php 
			if($perso->charge_perso >= $perso->chargeMax_perso){
				$class = 'text-danger fw-bold';
			}
			else {
				$class = 'text-primary';
			}
			?>
			<span class='fw-bold'>Charge :</span> <span class='<?= $class?>'><?= $perso->charge_perso ?></span> / <?= $perso->chargeMax_perso ?> kg
		</p>
		<p>
			Vous possédez :<br>
			<span class='fw-bold'><?= $nb_dans_sac; ?></span> objet(s) dont <?= $total_weapons_quantity; ?> arme(s) non équipée(s)<br>
			<span class='fw-bold'><?php echo $perso->or_perso; ?></span> thune(s) <img src="../images/or.png" align="middle">
		</p>
	</div>
</div>
<?php $header = ob_get_clean();

/* ---Content--- */
ob_start();
?>
<?php if(!empty($_SESSION["flash"])):?>
<div class="row justify-content-center px-0">
	<div class='col'>
		<p class='alert alert-<?= $_SESSION["flash"]['status'] ?> text-center fw-bold'>
		<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
		  <path stroke-linecap="round" stroke-linejoin="round" d="<?= $_SESSION["flash"]['icon'] ?>" />
		</svg>
		<?= $_SESSION["flash"]['msg'] ?>
		</p>
	</div>
</div>
<?php endif ;?>
<div class="row justify-content-center">
	<?php if(empty($items) AND empty($weapons)): ?>
	<div class="col-12 mb-4 mb-sm-0 bg-light bg-opacity-75 rounded py-2 px-4">
		<h3>Votre sac est vide.</h3>
		<p>
			Pour acheter un objet ou une arme, dirigez vous vers un fort ou un fortin.
		</p>
	</div>
	<?php endif; ?>
	<?php if(!empty($items)): ?>
	<div class="col-12 mb-4 bg-light bg-opacity-75 rounded">
		<table class="table table-light table-striped caption-top">
			<caption>Objets dans le sac (<?= $total_items_quantity?>)</caption>
			<thead>
				<tr>
					<th scope="col">Objet</th>
					<th scope="col" class='d-none d-sm-block'>Description</th>
					<th scope="col">Quantité</th>
					<th scope="col"></th>
				</tr>
			</thead>
			<tbody>
			<?php
			foreach($items as $item):
				$id_obj			= $item["id_objet"];
				$nom_o 			= $item["nom_objet"];
				$poids_o 		= $item["poids_objet"];
				$description_o 	= $item["description_objet"];
				$image_objet	= $item["image_objet"];
				$type_o			= $item["type_objet"];
				$contient_alcool= $item["contient_alcool"];
				$quantity		= $item["quantity"];
				$equiped		= $item["equiped"];
				
				$poids_total_o = $poids_o * $quantity;
			?>
			    <tr>
					<td class='text-center py-4'>
						<span class='text-success fw-bold'><?= $nom_o ?></span><br>
						<img class='img-fluid' src="../public/img/items/<?=$image_objet?>">
					</td>
					<td class='w-25 d-none d-sm-table-cell py-4'>
						<?= stripslashes($description_o) ?>
					</td>
					<td class='py-4'>
						Vous en possédez <span class='fw-bold'><?=$quantity?></span><br>
						<span class='text-decoration-underline'>Poids total :</span> <span class='fw-bold'><?=$poids_total_o?> kg</span>
					</td>
					<td class='text-center py-4'>
						<?php
							if($type_o == 'N'):
								if ($contient_alcool && $taux_alcool >= 2):
						?>
							<p class='text-danger mt-4'>
								<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
								  <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
								</svg>
								Vous avez trop bu !<br> Vous ne pouvez plus consommer d'alcool ce tour-ci
							</p>
							<?php else:?>
							<a class='btn btn-success' href="sac.php?id_obj=<?=$id_obj?>">utiliser (cout : 1 PA)</a>
						<?php
								endif;
							endif;
						?>
						<?php // Tickets de train
						if ($type_o == 'T'){
							$destinations = explode(',',$item["destinations"]);
						?>
							<span class='fw-bold'>Destination(s) : </span><br>
						<?php
							foreach($destinations as $destination) {
								if (trim($destination) == "") {
						?>
									<span class='fw-bold text-danger'>- Ticket non valide -</span>
						<?php 
								}
								else {
						?>
									<div class='mt-3'>
										<a class='btn btn-primary me-2' href='evenement.php?infoid=<?= $destination ?>'>Gare n° <?= $destination ?></a>
										<button type='button' class='btn btn-danger btn-sm mt-2 mt-sm-0' data-bs-toggle="modal" data-bs-target="#modalConfirm<?= $destination ?>">
											supprimer
										</button>
									</div>
									<!-- Modal -->
									<div class="modal fade" id="modalConfirm<?php echo $destination; ?>" tabindex="-1" role="dialog" aria-labelledby="deleteTicketTitle" aria-hidden="true">
										<div class="modal-dialog modal-dialog-centered">
											<div class="modal-content">
												<div class="modal-header">
													<h5 class="modal-title" id="deleteTicketTitle">Supprimer le ticket à destination de : <br> gare n° <?php echo $destination; ?></h5>
													<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
												</div>
												<form method="post" action="sac.php">
													<div class="modal-body">
														Êtes-vous sûr de vouloir supprimer le ticket à destination de la gare n° <?php echo $destination; ?> ?
														<input type='hidden' name='delete_ticket_hidden' value='<?php echo $destination; ?>'>
													</div>
													<div class="modal-footer">
														<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
														<button type="submit" class="btn btn-danger">Supprimer</button>
													</div>
												</form>
											</div>
										</div>
									</div>
							<?php
								}
							}
						}
						if($type_o == "E"):
							if($equiped):?>
							<p>
								<span class='fw-bold'>Vous êtes équipé de cet objet</span><br><br>
								<a class='btn btn-danger' href="sac.php?id_obj=<?= $id_obj ?>&desequip=ok">enlever (cout : 1 PA)</a>
							</p>
						<?php else: ?>
							<a class='btn btn-primary' href="sac.php?id_obj=<?=$id_obj?>">équiper (cout : 1 PA)</a>
						<?php 
							endif;
						endif;
						?>			
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php endif; ?>
	<?php if(!empty($weapons)): ?>
	<div class="col-12 bg-light bg-opacity-75 rounded">
		<table class="table table-light table-striped caption-top">
			<caption>Armes non équipées (<?= $total_weapons_quantity?>)</caption>
			<thead>
				<tr>
					<th scope="col">Arme</th>
					<th scope="col">Description</th>
					<th scope="col">Quantité</th>
					<th scope="col"></th>
				</tr>
			</thead>
			<tbody>
			<?php
			foreach($weapons as $weapon):
				$id_arme		= $weapon["id_arme"];
				$nom_a 			= $weapon["nom_arme"];
				$poids_a 		= $weapon["poids_arme"];
				$description_a 	= $weapon["description_arme"];
				$image_a		= $weapon["image_arme"];
				$quantity		= $weapon["quantity"];
				
				$poids_total_a = $poids_a * $quantity;
				
				$sql_u = "SELECT nom_unite FROM type_unite, arme_as_type_unite
							WHERE type_unite.id_unite = arme_as_type_unite.id_type_unite
							AND arme_as_type_unite.id_arme = '$id_arme'";
				$res_u = $mysqli->query($sql_u);
				$liste_unite = "";
				while ($t_u = $res_u->fetch_assoc()) {
					$nom_unite = $t_u["nom_unite"];
					
					if ($liste_unite != "") {
						$liste_unite .= " / ";
					}
					$liste_unite .= $nom_unite;
				}
			?>
				<tr>
					<td class='text-center py-4'>
						<span class='text-success fw-bold'><?= $nom_a ?></span><br>
						<img class='img-fluid' src="../images/armes/<?=$image_a?>">
					</td>
					<td class='w-25 py-4'>
						Arme utilisable pour les unités suivante :<br>
						<span class='fw-bold'><?= $liste_unite ?></span><br>
						<?= stripslashes($description_a) ?>
					</td>
					<td class='py-4'>
						Vous en possédez <span class='fw-bold'><?=$quantity?></span><br>
						<span class='text-decoration-underline'>Poids total :</span> <b><?=$poids_total_a?> kg</b><br>
					</td>
					<td class='text-center py-4'>
						<a class='btn btn-primary' href='equipement.php'>équiper (cout : 1 PA)</a>
					</td>
				</tr>
			<?php
			endforeach;
			?>
			</tbody>
		</table>
		<?php endif; ?>
	</div>
</div>
		
<?php $content = ob_get_clean(); ?>
<?php require('../mvc/view/layouts/app.php'); ?>
