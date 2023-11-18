<?php
$title = "Administration - Créer un objet";

/* ---Header--- */
ob_start();
?>
<div class="row justify-content-center text-center">
	<div class="col col-md-6">
		<h2 class='mb-3'>Créer un objet</h2>
		<nav>
			<a class="btn btn-primary me-2 mb-3" href="?">Retour à la gestion des objets</a>
			<a class="btn btn-primary me-2 mb-3" href="admin_nvs.php">Retour à l'administration</a>
			<a class="btn btn-light mb-3" href="jouer.php">Retour au jeu</a>
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
<div class="row">
	<div class="col">
		<div class="card h-100 shadow">
			<div class='card-header'>
				<h5 class="card-title mt-2">
					<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
						<path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75a4.5 4.5 0 01-4.884 4.484c-1.076-.091-2.264.071-2.95.904l-7.152 8.684a2.548 2.548 0 11-3.586-3.586l8.684-7.152c.833-.686.995-1.874.904-2.95a4.5 4.5 0 016.336-4.486l-3.276 3.276a3.004 3.004 0 002.25 2.25l3.276-3.276c.256.565.398 1.192.398 1.852z" />
						<path stroke-linecap="round" stroke-linejoin="round" d="M4.867 19.125h.008v.008h-.008v-.008z" />
					</svg>
					Créer un objet
				</h5>
			</div>
			<div class="card-body">
				<form method='post' action='?action=store' enctype="multipart/form-data">
					<div class='row'>
						<div class='col-12 col-sm-4 col-md mb-3 me-3 pt-2'>
							<h5 class='mb-3'>
								<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
								<path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
								</svg>
								Général
							</h5>
							<div class='row'>
								<div class='col-12 mb-3'>
									<label class='form-label<?php if(isset($_SESSION['errors']['name'])):?> fw-bold text-danger <?php endif ?>' for='name'>Nom : </label>
									<input class='form-control<?php if(isset($_SESSION['errors']['name'])):?> is-invalid<?php endif ?>' type='text' name='name' id='name' value='<?= $_SESSION['old_input']['name'] ?? ''?>'>
									<small class='text-muted'>Maximum 50 caractères, espaces compris</small>
									<?php if(isset($_SESSION['errors']['name'])):?>
									<div class='mt-2 alert alert-danger'>
										<ul class='mb-0'>
										<?php foreach($_SESSION['errors']['name'] as $message): ?>
											<li><?= $message ?></li>
										<?php endforeach ?>
										</ul>
									</div>
									<?php endif ?>
								</div>
								<div class='col-12 mb-3'>
									<label class='form-label<?php if(isset($_SESSION['errors']['category'])):?> fw-bold text-danger <?php endif ?>' for='name'>Catégorie : </label><br>
									<div class="form-check form-check-inline">
										<input class="form-check-input" type="radio" name="category" id="cat1" value='N'<?php if(isset($_SESSION['old_input']['category']) AND $_SESSION['old_input']['category']=='N'):?> checked<?php endif;?>>
										<label class="form-check-label" for="cat1">
											Consommable
										</label>
									</div>
									<div class="form-check form-check-inline">
										<input class="form-check-input" type="radio" name="category" id="cat2" value='E'<?php if(isset($_SESSION['old_input']['category']) AND $_SESSION['old_input']['category']=='E'):?> checked<?php endif;?>>
										<label class="form-check-label" for="cat2">
											Equipement
										</label>
									</div>
									<div class="form-check form-check-inline">
										<input class="form-check-input" type="radio" name="category" id="cat3" value='RP'<?php if(isset($_SESSION['old_input']['category']) AND $_SESSION['old_input']['category']=='RP'):?> checked<?php endif;?>>
										<label class="form-check-label" for="cat3">
											RP
										</label>
									</div>
									<?php if(isset($_SESSION['errors']['category'])):?>
									<div class='mt-2 alert alert-danger'>
										<ul class='mb-0'>
										<?php foreach($_SESSION['errors']['category'] as $message): ?>
											<li><?= $message ?></li>
										<?php endforeach ?>
										</ul>
									</div>
									<?php endif ?>
								</div>
								<div class='col-12 mb-3'>
									<label class='form-label<?php if(isset($_SESSION['errors']['desc'])):?> fw-bold text-danger <?php endif ?>' for='desc'>Description : </label>
									<textarea class='form-control<?php if(isset($_SESSION['errors']['desc'])):?> is-invalid<?php endif ?>' id="desc" name="desc" rows="5" cols="33" placeholder='Petit objet en cuir de fesses de buffle servant au sevrage des carottes. Ne rapporte aucun bonus, MAIS... aucun malus non plus'><?= $_SESSION['old_input']['desc'] ?? ''?></textarea>
									<small class='text-muted'>Maximum 250 caractères, espaces compris</small>
									<?php if(isset($_SESSION['errors']['desc'])):?>
									<div class='mt-2 alert alert-danger'>
										<ul class='mb-0'>
										<?php foreach($_SESSION['errors']['desc'] as $message): ?>
											<li><?= $message ?></li>
										<?php endforeach ?>
										</ul>
									</div>
									<?php endif ?>
								</div>
							</div>
						</div>
						<div class='col-12 col-sm-4 col-lg-3 px-4 pt-2 mb-3 border-end border-start bg-light'>
							<h5 class='mb-3'>
								<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
									<path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75" />
								</svg>
								Caractéristiques
							</h5>
							<div class='row'>
								<div class='col-12 mb-3'>
									<label class='form-label<?php if(isset($_SESSION['errors']['weight'])):?> fw-bold text-danger <?php endif ?>' for='weight'>Poids : </label>
									<div class="input-group">
										<input class='form-control<?php if(isset($_SESSION['errors']['weight'])):?> is-invalid<?php endif ?>' type='number' min='0' step='0.1' name='weight' id='weight' value='<?= $_SESSION['old_input']['weight'] ?? ''?>'>
										<span class="input-group-text">kg(s)</span>
									</div>
									<?php if(isset($_SESSION['errors']['weight'])):?>
									<div class='mt-2 alert alert-danger'>
										<ul class='mb-0'>
										<?php foreach($_SESSION['errors']['weight'] as $message): ?>
											<li><?= $message ?></li>
										<?php endforeach ?>
										</ul>
									</div>
									<?php endif ?>
								</div>
								<div class='col-12 mb-3'>
									<label class='form-label<?php if(isset($_SESSION['errors']['price'])):?> fw-bold text-danger <?php endif ?>' for='price'>Prix : </label>
									<div class="input-group">
										<input class='form-control<?php if(isset($_SESSION['errors']['price'])):?> is-invalid<?php endif ?>' type='number' min='0' name='price' id='price' value='<?= $_SESSION['old_input']['price'] ?? ''?>'>
										<span class="input-group-text">or(s)</span>
									</div>
									<?php if(isset($_SESSION['errors']['price'])):?>
									<div class='mt-2 alert alert-danger'>
										<ul class='mb-0'>
										<?php foreach($_SESSION['errors']['price'] as $message): ?>
											<li><?= $message ?></li>
										<?php endforeach ?>
										</ul>
									</div>
									<?php endif ?>
								</div>
								<div class='col-12 mb-3'>
									<label class='form-label<?php if(isset($_SESSION['errors']['cost_PA'])):?> fw-bold text-danger <?php endif ?>' for='cost_PA'>Coût en PA : </label>
									<div class="input-group">
										<input class='form-control<?php if(isset($_SESSION['errors']['cost_PA'])):?> is-invalid<?php endif ?>' type='number' min='0' name='cost_PA' id='cost_PA' value='<?= $_SESSION['old_input']['cost_PA'] ?? ''?>'>
										<span class="input-group-text">PA</span>
									</div>
									<?php if(isset($_SESSION['errors']['cost_PA'])):?>
									<div class='mt-2 alert alert-danger'>
										<ul class='mb-0'>
										<?php foreach($_SESSION['errors']['cost_PA'] as $message): ?>
											<li><?= $message ?></li>
										<?php endforeach ?>
										</ul>
									</div>
									<?php endif ?>
								</div>
								<div class='col-12 mb-3'>
									<label class='form-label<?php if(isset($_SESSION['errors']['range'])):?> fw-bold text-danger <?php endif ?>' for='range'>Portée : </label>
									<div class="input-group">
										<input class='form-control<?php if(isset($_SESSION['errors']['range'])):?> is-invalid<?php endif ?>' type='number' min='0' name='range' id='range' value='<?= $_SESSION['old_input']['range'] ?? ''?>'>
										<span class="input-group-text">case(s)</span>
									</div>
									<?php if(isset($_SESSION['errors']['range'])):?>
									<div class='mt-2 alert alert-danger'>
										<ul class='mb-0'>
										<?php foreach($_SESSION['errors']['range'] as $message): ?>
											<li><?= $message ?></li>
										<?php endforeach ?>
										</ul>
									</div>
									<?php endif ?>
								</div>
								<div class='col-12 mb-3'>
									<label class='form-label<?php if(isset($_SESSION['errors']['loss_chance'])):?> fw-bold text-danger <?php endif ?>' for='loss_chance'>Probabilité de perte : </label>
									<div class="input-group">
										<input class='form-control<?php if(isset($_SESSION['errors']['loss_chance'])):?> is-invalid<?php endif ?>' type='number' min='0' max='100' name='loss_chance' id='loss_chance' value='<?= $_SESSION['old_input']['loss_chance'] ?? ''?>'>
										<span class="input-group-text">%</span>
									</div>
									<small class='text-muted'>Probabilité de perdre l'objet après un RIP.<br> de 0 (pas de perte) à 100 (perte assurée)</small>
									<?php if(isset($_SESSION['errors']['loss_chance'])):?>
									<div class='mt-2 alert alert-danger'>
										<ul class='mb-0'>
										<?php foreach($_SESSION['errors']['loss_chance'] as $message): ?>
											<li><?= $message ?></li>
										<?php endforeach ?>
										</ul>
									</div>
									<?php endif ?>
								</div>
							</div>
						</div>
						<div class='col mb-3 ms-3 pt-2'>
							<h5 class='mb-5'>
							<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
								<path stroke-linecap="round" stroke-linejoin="round" d="M4.098 19.902a3.75 3.75 0 005.304 0l6.401-6.402M6.75 21A3.75 3.75 0 013 17.25V4.125C3 3.504 3.504 3 4.125 3h5.25c.621 0 1.125.504 1.125 1.125v4.072M6.75 21a3.75 3.75 0 003.75-3.75V8.197M6.75 21h13.125c.621 0 1.125-.504 1.125-1.125v-5.25c0-.621-.504-1.125-1.125-1.125h-4.072M10.5 8.197l2.88-2.88c.438-.439 1.15-.439 1.59 0l3.712 3.713c.44.44.44 1.152 0 1.59l-2.879 2.88M6.75 17.25h.008v.008H6.75v-.008z" />
							</svg>
							Options
							</h5>
							<div class='row'>
								<div class='col-12 mb-3'>
									<div class="form-check form-switch">
										<input class="form-check-input<?php if(isset($_SESSION['errors']['alcohol'])):?> is-invalid<?php endif ?>" type="checkbox" role="switch" id="alcohol" name='alcohol'<?php if(isset($_SESSION['old_input']['alcohol']) AND $_SESSION['old_input']['alcohol']=='on'):?> checked<?php endif;?>>
										<label class='form-check-label<?php if(isset($_SESSION['errors']['alcohol'])):?> fw-bold text-danger <?php endif ?>' for='alcohol'>Contient de l'alcool</label>
									</div>
									<small class='text-muted'>Si coché, le perso aura un statut bourré une fois consommé</small>
									<?php if(isset($_SESSION['errors']['alcohol'])):?>
									<div class='mt-2 alert alert-danger'>
										<ul class='mb-0'>
										<?php foreach($_SESSION['errors']['alcohol'] as $message): ?>
											<li><?= $message ?></li>
										<?php endforeach ?>
										</ul>
									</div>
									<?php endif ?>
								</div>
								<div class='col-12 mb-3'>
									<div class="form-check form-switch">
										<input class="form-check-input<?php if(isset($_SESSION['errors']['exchangeable'])):?> is-invalid<?php endif ?>" type="checkbox" role="switch" id="exchangeable" name='exchangeable'<?php if(isset($_SESSION['old_input']['exchangeable']) AND $_SESSION['old_input']['exchangeable']=='on'):?> checked<?php endif;?>>
										<label class='form-check-label<?php if(isset($_SESSION['errors']['exchangeable'])):?> fw-bold text-danger <?php endif ?>' for='exchangeable'>Peut être échangé</label>
									</div>
									<small class='text-muted'>Si coché, peut être échangé entre persos</small>
									<?php if(isset($_SESSION['errors']['exchangeable'])):?>
									<div class='mt-2 alert alert-danger'>
										<ul class='mb-0'>
										<?php foreach($_SESSION['errors']['exchangeable'] as $message): ?>
											<li><?= $message ?></li>
										<?php endforeach ?>
										</ul>
									</div>
									<?php endif ?>
								</div>
								<div class='col-12 mb-3'>
									<div class="form-check form-switch">
										<input class="form-check-input<?php if(isset($_SESSION['errors']['droppable'])):?> is-invalid<?php endif ?>" type="checkbox" role="switch" id="droppable" name='droppable'<?php if(isset($_SESSION['old_input']['droppable']) AND $_SESSION['old_input']['droppable']=='on'):?> checked<?php endif;?>>
										<label class='form-check-label<?php if(isset($_SESSION['errors']['droppable'])):?> fw-bold text-danger <?php endif ?>' for='droppable'>Peut être déposé</label>
									</div>
									<small class='text-muted'>Si coché, peut être déposé au sol</small>
									<?php if(isset($_SESSION['errors']['droppable'])):?>
									<div class='mt-2 alert alert-danger'>
										<ul class='mb-0'>
										<?php foreach($_SESSION['errors']['droppable'] as $message): ?>
											<li><?= $message ?></li>
										<?php endforeach ?>
										</ul>
									</div>
									<?php endif ?>
								</div>
								<div class='col-12 mb-3'>
									<div class="form-check form-switch">
										<input class="form-check-input<?php if(isset($_SESSION['errors']['buyable'])):?> is-invalid<?php endif ?>" type="checkbox" role="switch" id="buyable" name='buyable'<?php if(isset($_SESSION['old_input']['buyable']) AND $_SESSION['old_input']['buyable']=='on'):?> checked<?php endif;?>>
										<label class='form-check-label<?php if(isset($_SESSION['errors']['buyable'])):?> fw-bold text-danger <?php endif ?>' for='buyable'>Peut être acheté</label>
									</div>
									<small class='text-muted'>Si coché, sera disponible dans les boutiques</small>
									<?php if(isset($_SESSION['errors']['buyable'])):?>
									<div class='mt-2 alert alert-danger'>
										<ul class='mb-0'>
										<?php foreach($_SESSION['errors']['buyable'] as $message): ?>
											<li><?= $message ?></li>
										<?php endforeach ?>
										</ul>
									</div>
									<?php endif ?>
								</div>
							</div>
						</div>
					</div>
					<div class='row row-cols-1 row-cols-sm-2 row-cols-md-4 mb-3'>
						<div class='col-12 col-sm-12 col-md-12 mb-3'>
							<h5>
								<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
									<path stroke-linecap="round" stroke-linejoin="round" d="M6.633 10.5c.806 0 1.533-.446 2.031-1.08a9.041 9.041 0 012.861-2.4c.723-.384 1.35-.956 1.653-1.715a4.498 4.498 0 00.322-1.672V3a.75.75 0 01.75-.75A2.25 2.25 0 0116.5 4.5c0 1.152-.26 2.243-.723 3.218-.266.558.107 1.282.725 1.282h3.126c1.026 0 1.945.694 2.054 1.715.045.422.068.85.068 1.285a11.95 11.95 0 01-2.649 7.521c-.388.482-.987.729-1.605.729H13.48c-.483 0-.964-.078-1.423-.23l-3.114-1.04a4.501 4.501 0 00-1.423-.23H5.904M14.25 9h2.25M5.904 18.75c.083.205.173.405.27.602.197.4-.078.898-.523.898h-.908c-.889 0-1.713-.518-1.972-1.368a12 12 0 01-.521-3.507c0-1.553.295-3.036.831-4.398C3.387 10.203 4.167 9.75 5 9.75h1.053c.472 0 .745.556.5.96a8.958 8.958 0 00-1.302 4.665c0 1.194.232 2.333.654 3.375z" />
								</svg>
								<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
									<path stroke-linecap="round" stroke-linejoin="round" d="M7.5 15h2.25m8.024-9.75c.011.05.028.1.052.148.591 1.2.924 2.55.924 3.977a8.96 8.96 0 01-.999 4.125m.023-8.25c-.076-.365.183-.75.575-.75h.908c.889 0 1.713.518 1.972 1.368.339 1.11.521 2.287.521 3.507 0 1.553-.295 3.036-.831 4.398C20.613 14.547 19.833 15 19 15h-1.053c-.472 0-.745-.556-.5-.96a8.95 8.95 0 00.303-.54m.023-8.25H16.48a4.5 4.5 0 01-1.423-.23l-3.114-1.04a4.5 4.5 0 00-1.423-.23H6.504c-.618 0-1.217.247-1.605.729A11.95 11.95 0 002.25 12c0 .434.023.863.068 1.285C2.427 14.306 3.346 15 4.372 15h3.126c.618 0 .991.724.725 1.282A7.471 7.471 0 007.5 19.5a2.25 2.25 0 002.25 2.25.75.75 0 00.75-.75v-.633c0-.573.11-1.14.322-1.672.304-.76.93-1.33 1.653-1.715a9.04 9.04 0 002.86-2.4c.498-.634 1.226-1.08 2.032-1.08h.384" />
								</svg>
							Bonus/Malus
							</h5>
							<small class='text-muted'>laisser vide si non utilisé</small>
						</div>
						<div class='mb-3'>
							<label class='form-label<?php if(isset($_SESSION['errors']['perc'])):?> fw-bold text-danger <?php endif ?>' for='perc'>Perception : </label>
							<div class="input-group">
								<input class='form-control<?php if(isset($_SESSION['errors']['perc'])):?> is-invalid<?php endif ?>' type='number' name='perc' id='perc' value='<?= $_SESSION['old_input']['perc'] ?? ''?>'>
								<span class="input-group-text">Pt.</span>
							</div>
							<?php if(isset($_SESSION['errors']['perc'])):?>
							<div class='mt-2 alert alert-danger'>
								<ul class='mb-0'>
								<?php foreach($_SESSION['errors']['perc'] as $message): ?>
									<li><?= $message ?></li>
								<?php endforeach ?>
								</ul>
							</div>
							<?php endif ?>
						</div>
						<div class='mb-3'>
							<label class='form-label<?php if(isset($_SESSION['errors']['recup'])):?> fw-bold text-danger <?php endif ?>' for='recup'>Récupération : </label>
							<div class="input-group">
								<input class='form-control<?php if(isset($_SESSION['errors']['recup'])):?> is-invalid<?php endif ?>' type='number' name='recup' id='recup' value='<?= $_SESSION['old_input']['recup'] ?? ''?>'>
								<span class="input-group-text">pt.</span>
							</div>
							<?php if(isset($_SESSION['errors']['recup'])):?>
							<div class='mt-2 alert alert-danger'>
								<ul class='mb-0'>
								<?php foreach($_SESSION['errors']['recup'] as $message): ?>
									<li><?= $message ?></li>
								<?php endforeach ?>
								</ul>
							</div>
							<?php endif ?>
						</div>
						<div class='mb-3'>
							<label class='form-label<?php if(isset($_SESSION['errors']['pv'])):?> fw-bold text-danger <?php endif ?>' for='pv'>Point de vie (PV) : </label>
							<div class="input-group">
								<input class='form-control<?php if(isset($_SESSION['errors']['pv'])):?> is-invalid<?php endif ?>' type='number' name='pv' id='pv' value='<?= $_SESSION['old_input']['pv'] ?? ''?>'>
								<span class="input-group-text">Pt.</span>
							</div>
							<?php if(isset($_SESSION['errors']['pv'])):?>
							<div class='mt-2 alert alert-danger'>
								<ul class='mb-0'>
								<?php foreach($_SESSION['errors']['pv'] as $message): ?>
									<li><?= $message ?></li>
								<?php endforeach ?>
								</ul>
							</div>
							<?php endif ?>
						</div>
						<div class='mb-3'>
							<label class='form-label<?php if(isset($_SESSION['errors']['movement'])):?> fw-bold text-danger <?php endif ?>' for='movement'>Point de mouvement (PM) : </label>
							<div class="input-group">
								<input class='form-control<?php if(isset($_SESSION['errors']['movement'])):?> is-invalid<?php endif ?>' type='number' name='movement' id='movement' value='<?= $_SESSION['old_input']['movement'] ?? ''?>'>
								<span class="input-group-text">Pt.</span>
							</div>
							<?php if(isset($_SESSION['errors']['movement'])):?>
							<div class='mt-2 alert alert-danger'>
								<ul class='mb-0'>
								<?php foreach($_SESSION['errors']['movement'] as $message): ?>
									<li><?= $message ?></li>
								<?php endforeach ?>
								</ul>
							</div>
							<?php endif ?>
						</div>
						<div class='mb-3'>
							<label class='form-label<?php if(isset($_SESSION['errors']['pa'])):?> fw-bold text-danger <?php endif ?>' for='pa'>Point d'action (PA) : </label>
							<div class="input-group">
								<input class='form-control<?php if(isset($_SESSION['errors']['pa'])):?> is-invalid<?php endif ?>' type='number' name='pa' id='pa' value='<?= $_SESSION['old_input']['pa'] ?? ''?>'>
								<span class="input-group-text">Pt.</span>
							</div>
							<?php if(isset($_SESSION['errors']['pa'])):?>
							<div class='mt-2 alert alert-danger'>
								<ul class='mb-0'>
								<?php foreach($_SESSION['errors']['pa'] as $message): ?>
									<li><?= $message ?></li>
								<?php endforeach ?>
								</ul>
							</div>
							<?php endif ?>
						</div>
						<div class='mb-3'>
							<label class='form-label<?php if(isset($_SESSION['errors']['defense'])):?> fw-bold text-danger <?php endif ?>' for='defense'>Protection : </label>
							<div class="input-group">
								<input class='form-control<?php if(isset($_SESSION['errors']['defense'])):?> is-invalid<?php endif ?>' type='number' name='defense' id='defense' value='<?= $_SESSION['old_input']['defense'] ?? ''?>'>
								<span class="input-group-text">Pt.</span>
							</div>
							<?php if(isset($_SESSION['errors']['defense'])):?>
							<div class='mt-2 alert alert-danger'>
								<ul class='mb-0'>
								<?php foreach($_SESSION['errors']['defense'] as $message): ?>
									<li><?= $message ?></li>
								<?php endforeach ?>
								</ul>
							</div>
							<?php endif ?>
						</div>
						<div class='mb-3'>
							<label class='form-label<?php if(isset($_SESSION['errors']['prec_cac'])):?> fw-bold text-danger <?php endif ?>' for='prec_cac'>Précision au corps à corps : </label>
							<div class="input-group">
								<input class='form-control<?php if(isset($_SESSION['errors']['prec_cac'])):?> is-invalid<?php endif ?>' type='number' name='prec_cac' id='prec_cac' value='<?= $_SESSION['old_input']['prec_cac'] ?? ''?>'>
								<span class="input-group-text">%</span>
							</div>
							<?php if(isset($_SESSION['errors']['prec_cac'])):?>
							<div class='mt-2 alert alert-danger'>
								<ul class='mb-0'>
								<?php foreach($_SESSION['errors']['prec_cac'] as $message): ?>
									<li><?= $message ?></li>
								<?php endforeach ?>
								</ul>
							</div>
							<?php endif ?>
						</div>
						<div class='mb-3'>
							<label class='form-label<?php if(isset($_SESSION['errors']['prec_dist'])):?> fw-bold text-danger <?php endif ?>' for='prec_dist'>Précision à distance : </label>
							<div class="input-group">
								<input class='form-control<?php if(isset($_SESSION['errors']['prec_dist'])):?> is-invalid<?php endif ?>' type='number' name='prec_dist' id='prec_dist' value='<?= $_SESSION['old_input']['prec_dist'] ?? ''?>'>
								<span class="input-group-text">%</span>
							</div>
							<?php if(isset($_SESSION['errors']['prec_dist'])):?>
							<div class='mt-2 alert alert-danger'>
								<ul class='mb-0'>
								<?php foreach($_SESSION['errors']['prec_dist'] as $message): ?>
									<li><?= $message ?></li>
								<?php endforeach ?>
								</ul>
							</div>
							<?php endif ?>
						</div>
					</div>
					<div class='row'>
						<div class='col-5'>
							<h5>
								<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
									<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
								</svg>
								Icône
							</h5>
							<label for="imgUpload" class="form-label"><small>Choisir une image</small></label>
							<input class="form-control form-control-sm<?php if(isset($_SESSION['errors']['imgUpload'])):?> text-danger is-invalid<?php endif ?>" id="imgUpload" name="imgUpload" type="file" accept="image/*">
							<input type="hidden" name="MAX_FILE_SIZE" value="2000000" />
							<small class='text-muted'>png, jpeg ou gif</small>,
							<small class='text-muted'>dimensions maximum: 150x150</small>,
							<small class='text-muted'>2Mo maximum</small>
							<?php if(isset($_SESSION['errors']['imgUpload'])):?>
							<div class='mt-2 alert alert-danger'>
								<ul class='mb-0'>
								<?php foreach($_SESSION['errors']['imgUpload'] as $message): ?>
									<li><?= $message ?></li>
								<?php endforeach ?>
								</ul>
							</div>
							<?php endif ?>
						</div>
						<div class='col'>
							<h5>
								<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
									<path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
								</svg>
								Aperçus
							</h5>
							<div class='preview' class='mt-2'>
								(150x150) <span id="imgPreview150"></span>
								(40x40) <span id="imgPreview40"></span>
							</div>
							<p class='mt-3'>
								<span id='imgPreviewName' class='bg-secondary-subtle border rounded p-2'>Nom du fichier</span>
							</p>
						</div>
					</div>
					<div class='row mt-3'>
						<div class='col-12'>
							<button type='submit' class='btn btn-primary'>Créer</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>

<?php $content = ob_get_clean(); ?>

<?php require('../mvc/view/layouts/app.php'); ?>