<?php
$title = "Cartes - Administration";

/* ---Header--- */
ob_start();
?>
<div class="row justify-content-center text-center">
	<div class="col col-md-6">
		<h2 class='mb-3'>Editer la carte n°<?= $id?></h2>
		<?php if(isset($carte) && !empty($carte)): ?>
		<h3 class='mb-3'>Dimensions <?= $dimensions->xMax?>x<?= $dimensions->yMax?></h3>
		<?php endif ?>
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
	<div class="row mb-3">
		<a href='' class='btn btn-warning'>création fond de carte (A intégrer dans la création de carte ou développer une autre méthode pour le fond de carte)</a>
	</div>
	<div class="row row-cols-1 row-cols-md-2">
		<div class="col">
			<div class="card shadow">
				<div class='card-header'>
					<h3>Afficher une zone</h3>
				</div>
				<div class="card-body">
					<h5 class="card-title">Coordonnées</h5>
					<?php if(isset($errors) && isset($old_input) && $old_input['form']=='showArea'):?>
					<div class='col-12 mt-2'>
						<div class='alert alert-danger'>
							<ul class='mb-0 ps-2'>
							<?php foreach($errors as $error => $messages): ?>
								<?php foreach($messages as $message): ?>
									<li class='ms-0'><?= $message ?></li>
								<?php endforeach ?>
							<?php endforeach ?>
							</ul>
						</div>
					</div>
					<?php endif ?>
					<form class='row row-cols-auto gx-5 gy-3 align-items-center' name='showArea' method='post' action='?page=carte&action=edit&id=<?= $id?>'>
						<div class='col-3'>
							<div class='row'>
								<label class='form-label col-form-label col-4<?php if(isset($errors['x_pos'])):?> fw-bold text-danger<?php endif ?>' for='x_pos'>X :</label>
								<input class='form-control col<?php if(isset($errors['x_pos'])):?> is-invalid<?php endif ?>' type='number' name='x_pos' id='x_pos' value='<?= $old_input['x_pos'] ?? ''?>' placeholder='X'>
							</div>
						</div>
						<div class='col-3'>
							<div class='row'>
								<label class='form-label col-form-label col-4<?php if(isset($errors['y_pos'])):?> fw-bold text-danger<?php endif ?>' for='y_pos'>Y :</label>
								<input class='form-control col<?php if(isset($errors['y_pos'])):?> is-invalid<?php endif ?>' type='number' name='y_pos' id='y_pos' value='<?= $old_input['y_pos'] ?? '' ?>' placeholder='Y'>
							</div>
						</div>
						<div class='col-5'>
							<div class='row'>
								<label class='form-label col-form-label col-6<?php if(isset($errors['perc'])):?> fw-bold text-danger<?php endif ?>' for='perc'>Perception :</label>
								<input class='form-control col<?php if(isset($errors['perc'])):?> is-invalid<?php endif ?>' type='number' name='perc' id='perc' value='<?= $old_input['perc'] ?? '' ?>' placeholder='Perception'>
							</div>
						</div>
						<div class='col-4'>
							<input type="hidden" id="form" name="form" value="showArea" />
							<button type='submit' class='btn btn-primary'>Afficher</button>
						</div>
						<div class='col-7'>
							<small>Attention ! Ne pas renseigner une perception trop grande. Cela ralentirait l'affichage et le serveur</small>
						</div>
					</form>
					
				</div>
			</div>
		</div>
		<div class="col">
			<div class="card shadow">
				<div class='card-header'>
					<h3>Edition</h3>
				</div>
				<div class="card-body">
					<h5 class="card-title">Création d'une zone de terrain</h5>
					<?php if(isset($errors) && isset($old_input) && $old_input['form']=='editMap'):?>
					<div class='col-12 mt-2'>
						<div class='alert alert-danger'>
							<ul class='mb-0 ps-2'>
							<?php foreach($errors as $error => $messages): ?>
								<?php foreach($messages as $message): ?>
									<li class='ms-0'><?= $message ?></li>
								<?php endforeach ?>
							<?php endforeach ?>
							</ul>
						</div>
					</div>
					<?php endif ?>
					<form class='row row-cols-auto gx-5 gy-3 align-items-center' name='editMap' method='post' action='?page=carte&action=edit&id=<?= $id?>'>
						<div class='col-4'>
							<div class='row'>
								<label class='form-label col-form-label col-5<?php if(isset($errors['x_min'])):?> fw-bold text-danger<?php endif ?>' for='x_min'>X min :</label>
								<input class='form-control col<?php if(isset($errors['x_min'])):?> is-invalid<?php endif ?>' type='number' min="0" name='x_min' id='x_min' value='<?= $old_input['x_min'] ?? ''?>' placeholder='X min'>
							</div>
						</div>
						<div class='col-4 offset-1'>
							<div class='row'>
								<label class='form-label col-form-label col-5<?php if(isset($errors['x_max'])):?> fw-bold text-danger<?php endif ?>' for='x_max'>X max :</label>
								<input class='form-control col<?php if(isset($errors['x_max'])):?> is-invalid<?php endif ?>' type='number' min="1" name='x_max' id='x_max' value='<?= $old_input['x_max'] ?? ''?>' placeholder='X max'>
							</div>
						</div>
						<div class='col-4'>
							<div class='row'>
								<label class='form-label col-form-label col-5<?php if(isset($errors['y_min'])):?> fw-bold text-danger<?php endif ?>' for='y_min'>Y min :</label>
								<input class='form-control col<?php if(isset($errors['y_min'])):?> is-invalid<?php endif ?>' type='number' min="0" name='y_min' id='y_min' value='<?= $old_input['y_min'] ?? '' ?>' placeholder='Y min'>
							</div>
						</div>
						<div class='col-4 offset-1'>
							<div class='row'>
								<label class='form-label col-form-label col-5<?php if(isset($errors['y_max'])):?> fw-bold text-danger<?php endif ?>' for='y_max'>Y max :</label>
								<input class='form-control col<?php if(isset($errors['y_max'])):?> is-invalid<?php endif ?>' type='number' name='y_max' id='y_max' value='<?= $old_input['y_max'] ?? '' ?>' placeholder='Y max'>
							</div>
						</div>
						<div class='col-11'>
							<label class="form-check-label mb-2" for="terrain">
								terrain
							</label><br/>
							<?php if(isset($terrains) && !empty(isset($terrains))): ?>
								<?php foreach($terrains as $id => $composants): ?>
								<div class="form-check form-check-inline">
									<input class="form-check-input mt-2" type="radio" name="terrain" id="<?= $composants[0] ?>" value='<?= $id ?>' <?php if(isset($old_input['terrain']) && $old_input['terrain'] == $id ):?> checked <?php endif ?>>
									<label class="form-check-label" for="<?= $composants[0] ?>">
										<span class='visually-hidden'><?= $composants[0] ?></span> <img src="../fond_carte/<?= $id  ?>.gif" width="34" height="34" alt='<?= $composants[0] ?>'>
									</label>
								</div>
								<?php endforeach ?>
							<?php else: ?>
								<p class='alert alert-warning'>
									<svg xmlns="http://www.w3.org/2000/svg" class="warning-icon-lg me-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
									  <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
									</svg>
									Aucun terrain à choisir. Le terrain par défaut sera la plaine.
								</p>
							<?php endif ?>
							<?php if(isset($errors['terrain'])):?>
							<div class='mt-2 alert alert-danger'>
								<ul class='mb-0'>
								<?php foreach($errors['terrain'] as $message): ?>
									<li><?= $message ?></li>
								<?php endforeach ?>
								</ul>
							</div>
							<?php endif ?>
						</div>
						<div class='col'>
							<input type="hidden" id="form" name="form" value="editMap" />
							<button type='submit' class='btn btn-primary'>Créer</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
	<?php if(isset($map)): ?>
	<div class="row justify-content-center">
		<div class='col text-center mt-4'>
			<table class="mx-auto bg-light">
				<thead>
					<tr class='map_bg'>
						<th class='tile'>Y\X</th>
						<?php for($x = $x_min; $x <= $x_max; $x++): ?>
						<th class='tile <?php if($x==$x_choice):?>text-dark<?php else: ?>fw-light<?php endif ?>'><?= $x ?></th>
						<?php endfor ?>
						<th class='tile'>X/Y</th>
					</tr>
				</thead>
				<tbody>
				<?php $i=0; for($y = $y_min; $y <= $y_max; $y++): ?>
					<tr>
						<th class='map_bg tile <?= $tileClass ?> <?php if($y==$y_choice):?>text-dark<?php else: ?>fw-light<?php endif ?>'><?= $y ?></th>
						<?php for($x = $x_min; $x <= $x_max; $x++):?>
							<?php if($x >= 0 AND $x <= $dimensions->xMax AND $y >= 0 AND $y <= $dimensions->yMax): ?>
							<td class='tile ground-<?= strstr($map[$i]['fond_carte'],'.',true) ?><?php if($map[$i]['x_carte']==$x_choice):?> selected-line x<?php endif ?><?php if($map[$i]['y_carte']==$y_choice):?> selected-line y<?php endif ?>'>
								<?php if($map[$i]['idPerso_carte']):
									if($map[$i]['idPerso_carte'] >= 50000 AND $map[$i]['idPerso_carte']<200000):
										$imgDir = 'batiments';
									elseif($map[$i]['idPerso_carte'] > 200000):
										$imgDir = 'pnj';
									else:
										$imgDir = 'persos';
									endif;
								?>
								<div class='tile-perso'>
									<?php if($map[$i]['idPerso_carte'] < 50000):?>
									<span class='tile-perso-id'><?= $map[$i]['idPerso_carte'] ?></span>
									<?php endif ?>
									<img class='map-perso' src="../public/img/<?=$imgDir?>/<?= $map[$i]['image_carte'] ?>" width='40' height='40' alt='<?= $map[$i]['idPerso_carte'] ?>'>
								</div>
								<?php endif ?>
							</td>
							<?php $i++ ?>
							<?php else: ?>
							<td class='tile ground-0'></td>
							<?php endif ?>
						<?php endfor ?>
						<th class='map_bg tile <?= $tileClass ?> <?php if($y==$y_choice):?>text-dark<?php else: ?>fw-light<?php endif ?>'><?= $y ?></th>
					</tr>
				<?php endfor ?>
				</tbody>
				<tfoot>
					<tr class='map_bg'>
						<th class='tile'>Y/X</th>
						<?php for($x = $x_min; $x <= $x_max; $x++): ?>
						<th class='tile <?php if($x==$x_choice):?>text-dark<?php else: ?>fw-light<?php endif ?>'><?= $x ?></th>
						<?php endfor ?>
						<th class='tile'>X\Y</th>
					</tr>
				</tfoot>
			</table>
		</div>
	</div>
	<?php endif ?>
<?php endif ?>
<?php $content = ob_get_clean(); ?>

<?php require('../mvc/view/layouts/app.php'); ?>









<?php // ANCIEN CODE
$mysqli = db_connexion();

	if (isset ($_POST['liste_x']) && isset ($_POST['liste_y']) && isSet ($_POST['perception'])) {
		
		$x_choix = $_SESSION['x_choix'] = $_POST['liste_x'];
		$y_choix = $_SESSION['y_choix'] = $_POST['liste_y'];
		
		$perc = $_SESSION['perc'] = $_POST['perception'];
	}

	$carte = $_SESSION['choix_carte'] = 'Carte3';

	$X_MAXD = $dimensions->xMax;
	$Y_MAXD = $dimensions->yMax;
?>
<html>
<body>
	<div class="container-fluid">
		<div class="row">
			<div class="col-12">
				<div align='center'>
					<?php
					if (isset($X_MAXD) && isset($Y_MAXD)) {							
						$link_creation_carte_fond = "creation_".$carte."_fond.php";
						
						echo " <a href='".$link_creation_carte_fond."' class='btn btn-warning'>création fond $carte</a>";
					}
					?>
				</div>
			</div>
		</div>
		<br />
		<form method="post" action="utils_carte.php">
			<table width="100%" border="5">
			
				<tr>
					<td width="6%"><b><font color="#660000">Terrains</font></b></td>
					<td> 
						<input type="radio" name="terrain" value="1.gif" id="plaine">
						<img src="../fond_carte/1.gif" width="34" height="34"><br>Plaine
					</td>
					<td> 
						<input type="radio" name="terrain" value="2.gif" id="colline">
						<img src="../fond_carte/2.gif" width="34" height="34"><br>Colline
					</td>
					<td> 
						<input type="radio" name="terrain" value="3.gif" id="montagne">
						<img src="../fond_carte/3.gif" width="34" height="34"><br>Montagne
					</td>
					<td> 
						<input type="radio" name="terrain" value="4.gif" id="desert">
						<img src="../fond_carte/4.gif" width="34" height="34"><br>Desert
					</td>
					<td> 
						<input type="radio" name="terrain" value="5.gif" id="neige">
						<img src="../fond_carte/5.gif" width="34" height="34"><br>Neige
					</td>
					<td> 
						<input type="radio" name="terrain" value="6.gif" id="plaine">
						<img src="../fond_carte/6.gif" width="34" height="34"><br>Marecage
					</td>
					<td> 
						<input type="radio" name="terrain" value="7.gif" id="foret">
						<img src="../fond_carte/7.gif" width="34" height="34"><br>Foret
					</td>
					<td> 
						<input type="radio" name="terrain" value="8.gif" id="eau">
						<img src="../fond_carte/8.gif" width="34" height="34"><br>Eau
					</td>
					<td colspan='6'> 
						<input type="radio" name="terrain" value="9.gif" id="eau_p">
						<img src="../fond_carte/9.gif" width="34" height="34"><br>Eau_profonde
					</td>
				</tr>
				
				<tr>
					<td width="6%"><b><font color="#660000">Batiments</font></b></td>
					<td> 
						<input type="radio" name="batiment" value="b1b.png" id="barricade_bleu">
						<img src="../images_perso/b1b.png" width="34" height="34"><br>Barricade Nord
					</td>
					<td> 
						<input type="radio" name="batiment" value="b1r.png" id="barricade_rouge">
						<img src="../images_perso/b1r.png" width="34" height="34"><br>Barricade Sud
					</td>
					<td> 
						<input type="radio" name="batiment" value="b2b.png" id="tour_guet_bleu">
						<img src="../images_perso/b2b.png" width="34" height="34"><br>Tour de guet Nord
					</td>
					<td> 
						<input type="radio" name="batiment" value="b2r.png" id="tour_guet_rouge">
						<img src="../images_perso/b2r.png" width="34" height="34"><br>Tour de guet Sud
					</td>
					<td> 
						<input type="radio" name="batiment" value="b5b.png" id="pont_rouge">
						<img src="../images_perso/b5b.png" width="34" height="34"><br>Pont Sud
					</td>
					<td> 
						<input type="radio" name="batiment" value="b5r.png" id="pont_bleu">
						<img src="../images_perso/b5r.png" width="34" height="34"><br>Pont Nord
					</td>
					<td> 
						<input type="radio" name="batiment" value="b7b.png" id="hopital_bleu">
						<img src="../images_perso/b7b.png" width="34" height="34"><br>Hopital Nord
					</td>
					<td> 
						<input type="radio" name="batiment" value="b7r.png" id="hopital_rouge">
						<img src="../images_perso/b7r.png" width="34" height="34"><br>Hopital Sud
					</td>
					<td> 
						<input type="radio" name="batiment" value="b6b.png" id="fortin_bleu">
						<img src="../images_perso/b6b.png" width="34" height="34"><br>Fortin Nord
					</td>
					<td> 
						<input type="radio" name="batiment" value="b6r.png" id="fortin_rouge">
						<img src="../images_perso/b6r.png" width="34" height="34"><br>Fortin Sud
					</td>
					<td> 
						<input type="radio" name="batiment" value="b9b.png" id="fort_bleu">
						<img src="../images_perso/b9b.png" width="34" height="34"><br>Fort Nord
					</td>
					<td> 
						<input type="radio" name="batiment" value="b9r.png" id="fort_rouge">
						<img src="../images_perso/b9r.png" width="34" height="34"><br>Fort Sud
					</td>
					<td> 
						<input type="radio" name="batiment" value="b11b.png" id="gare_bleu">
						<img src="../images_perso/b11b.png" width="34" height="34"><br>Gare Nord
					</td>
					<td> 
						<input type="radio" name="batiment" value="b11r.png" id="gare_rouge">
						<img src="../images_perso/b11r.png" width="34" height="34"><br>Gare Sud
					</td>
				</tr>
				
				<tr>
					<td width="6%"><b><font color="#660000">PNJ</font></b></td>
					<td> 
						<input type="radio" name="pnj" value="1" id="sangsue">
						<img src="../images/pnj/pnj1t.png" width="34" height="34"><br>Sangsue
					</td>
					<td>
						<input type="radio" name="pnj" value="2" id="loup">
						<img src="../images/pnj/pnj2t.gif" width="34" height="34"><br>Loup
					</td>
					<td>
						<input type="radio" name="pnj" value="3" id="crotale">
						<img src="../images/pnj/pnj3t.gif" width="34" height="34"><br>Crotale
					</td>
					<td>
						<input type="radio" name="pnj" value="4" id="caiman">
						<img src="../images/pnj/pnj4t.png" width="34" height="34"><br>Caïman
					</td>
					<td>
						<input type="radio" name="pnj" value="5" id="bison">
						<img src="../images/pnj/pnj5t.png" width="34" height="34"><br>Bison
					</td>
					<td>
						<input type="radio" name="pnj" value="6" id="bison_blanc">
						<img src="../images/pnj/pnj6t.gif" width="34" height="34"><br>Bison blanc
					</td>
					<td>
						<input type="radio" name="pnj" value="7" id="scorpion">
						<img src="../images/pnj/pnj7t.png" width="34" height="34"><br>Scorpion
					</td>
					<td>
						<input type="radio" name="pnj" value="8" id="aigle">
						<img src="../images/pnj/pnj8t.png" width="34" height="34"><br>Aigle
					</td>
					<td colspan='6'>
						<input type="radio" name="pnj" value="9" id="ours">
						<img src="../images/pnj/pnj9t.png" width="34" height="34"><br>Ours
					</td>
				</tr>
				<tr>
					<td width="6%"><b><font color="#660000">Actions</font></b></td>
					<td colspan='14'><input type="submit" name="eval_terrain" value="appliquer" class='btn btn-primary'></td>
				</tr>
			</table>
			<?php
			if (isset ($_POST['terrain']) && isset ($_POST['case']))
			{
				$tabcase = $_POST['case'];
				$terrain = $_POST['terrain'];
				
				for ($i = 0; $i < count($tabcase); $i++) {
					
					$j = 0;
					$tabcase_x = $tabcase[$i][$j++];
					$stop = $tabcase[$i][$j];
					
					while ($stop != 's')
					{
						$tabcase_x .= $tabcase[$i][$j++];
						$stop = $tabcase[$i][$j];
					}
					
					$j++;
					$tabcase_y = $tabcase[$i][$j++];
					$stop = $tabcase[$i][$j];
					
					while ($stop != 's')
					{
						$tabcase_y .= $tabcase[$i][$j++];
						$stop = $tabcase[$i][$j];
					}
					
					if(isset($_SESSION['choix_carte'])){
						
						$carte = $_SESSION['choix_carte'];
							
						$sql = "UPDATE $carte SET fond_carte='$terrain' WHERE x_carte=$tabcase_x AND y_carte=$tabcase_y";
						$mysqli->query($sql);
					}
					else {
						$sql = "UPDATE carte SET fond_carte='$terrain' WHERE x_carte=$tabcase_x AND y_carte=$tabcase_y";
						$mysqli->query($sql);
					}
				}
			}

			if (isSet($_POST['pnj']) && isset ($_POST['case'])){
				
				$tabcase = $_POST['case'];
				$pnj = $_POST['pnj'];
				
				for ($i = 0; $i < count($tabcase); $i++) {
					
					$j = 0;
					$tabcase_x = $tabcase[$i][$j++];
					$stop = $tabcase[$i][$j];
					
					while ($stop != 's')
					{
						$tabcase_x .= $tabcase[$i][$j++];
						$stop = $tabcase[$i][$j];
					}
					
					$j++;
					$tabcase_y = $tabcase[$i][$j++];
					$stop = $tabcase[$i][$j];
					
					while ($stop != 's')
					{
						$tabcase_y .= $tabcase[$i][$j++];
						$stop = $tabcase[$i][$j];
					}
					
					if(isset($_SESSION['choix_carte'])) {
						
						$carte = $_SESSION['choix_carte'];
						
						// verification si la case est deja occupee ou non
						$sql = "SELECT occupee_carte FROM $carte WHERE x_carte='$tabcase_x' AND y_carte='$tabcase_y'";
						$res = $mysqli->query($sql);
						$t = $res->fetch_assoc();
							
						$oc = $t["occupee_carte"];
						
						if($oc) {
							echo "impossible de placer le pnj a cet endroit : la case est déjà occuppée<br>";
						}
						else {
							if ($pnj <= 9) {
								
								// recuperation des pv du pnj
								$sql = "SELECT pvMax_pnj, nom_pnj, pm_pnj FROM pnj WHERE id_pnj='$pnj'";
								$res = $mysqli->query($sql);
								$t = $res->fetch_assoc();
								
								$pvMaxPnj 	= $t["pvMax_pnj"];
								$nomPnj 	= $t["nom_pnj"];
								$pmPnj 		= $t["pm_pnj"];
								
								// creation du pnj
								$sql = "INSERT INTO instance_pnj (id_pnj, pv_i, pm_i, deplace_i, dernierAttaquant_i, x_i, y_i, bonus_i) VALUES ('$pnj','$pvMaxPnj','$pmPnj','1',0,'$tabcase_x','$tabcase_y','0')";
								$mysqli->query($sql);
								$id_instance = $mysqli->insert_id;
								
								// on met le pnj sur la carte
								$image_pnj = "pnj".$pnj."t.png";
								$sql = "UPDATE $carte SET occupee_carte = '1', idPerso_carte='$id_instance', image_carte='$image_pnj' WHERE x_carte='$tabcase_x' AND y_carte='$tabcase_y'";
								$mysqli->query($sql);
							}
							else {
								
								if($pnj == 10){
									$nomPnj = "mur";
									$image = "murt.png";
								}
								
								if($pnj == 11){
									$nomPnj = "coffre";
									$image = "coffre1t.png";
								}
								
								$sql = "UPDATE $carte SET occupee_carte='1', image_carte='$image' WHERE x_carte='$tabcase_x' AND y_carte='$tabcase_y'";
								$mysqli->query($sql);	
							}
						
							echo "Vous avez placer un $nomPnj en :<br>";
							echo "$tabcase_x/$tabcase_y<br>";
						}				
					}
					else { // par defaut : carte normale
					
						// verification si la case est deja occupee ou non
						$sql = "SELECT occupee_carte FROM carte WHERE x_carte='$tabcase_x' AND y_carte='$tabcase_y'";
						$res = $mysqli->query($sql);
						$t = $res->fetch_assoc();
						
						$oc = $t["occupee_carte"];
							
						if($oc) {
							echo "impossible de placer le pnj a cet endroit : la case est déjà occuppée<br>";
						}
						else {
							if ($pnj <= 9) {	
							
								// recuperation des pv du pnj
								$sql = "SELECT pvMax_pnj, nom_pnj, pm_pnj FROM pnj WHERE id_pnj='$pnj'";
								$res = $mysqli->query($sql);
								$t = $res->fetch_assoc();
								
								$pvMaxPnj = $t["pvMax_pnj"];
								$nomPnj = $t["nom_pnj"];
								$pmPnj = $t["pm_pnj"];
								
								// creation du pnj
								$sql = "INSERT INTO instance_pnj (id_pnj, pv_i, pm_i, deplace_i, dernierAttaquant_i, x_i, y_i, bonus_i) VALUES ('$pnj','$pvMaxPnj','$pmPnj','1','0','$tabcase_x','$tabcase_y','0')";
								$mysqli->query($sql);
								$id_instance = $mysqli->insert_id;
								
								// on met le pnj sur la carte
								$image_pnj = "pnj".$pnj."t.png";
								$sql = "UPDATE carte SET occupee_carte = '1', idPerso_carte='$id_instance', image_carte='$image_pnj' WHERE x_carte='$tabcase_x' AND y_carte='$tabcase_y'";
								$mysqli->query($sql);
							}
							else {
								
								if($pnj == 10){
									$nomPnj = "mur";
									$image = "murt.png";
								}
								
								if($pnj == 11){
									$nomPnj = "coffre";
									$image = "coffre1t.png";
								}
								
								$sql = "UPDATE carte SET occupee_carte='1', image_carte='$image' WHERE x_carte='$tabcase_x' AND y_carte='$tabcase_y'";
								$mysqli->query($sql);
							}
						
							echo "Vous avez placer un $nomPnj en :<br>";
							echo "$tabcase_x/$tabcase_y<br>";
						}
					}
				}
			}

			if (isset($_POST['eval_xy']) && $_POST['eval_xy'] == "ok") {
				
				if (isset ($_POST['liste_x']) && isset ($_POST['liste_y']) && isSet ($_POST['perception'])) {
					
					$x_choix = $_SESSION['x_choix'] = $_POST['liste_x'];
					$y_choix = $_SESSION['y_choix'] = $_POST['liste_y'];
					
					$perc = $_SESSION['perc'] = $_POST['perception'];
					
					echo '<table border=1 align="left">';
					echo "<tr><td width=40 height=40>y / x</td>";  
					
					//affichage des abscisses
					for ($i = $x_choix - $perc; $i <= $x_choix + $perc; $i++) {
						
						if ($i == $x_choix) {
							echo "<th class=\"map\" bgcolor=\"#cccccc\">$i</th>";
						}
						else {
							echo "<th class=\"map\">$i</th>";
						}
					}
					
					echo "</tr>";
					
					if(isset($_SESSION['choix_carte'])){
						
						$carte = $_SESSION['choix_carte'];
							
						$sql = "SELECT x_carte, y_carte, fond_carte, idPerso_carte, image_carte, occupee_carte FROM $carte WHERE x_carte >= $x_choix - $perc AND x_carte <= $x_choix + $perc AND y_carte <= $y_choix + $perc AND y_carte >= $y_choix - $perc ORDER BY y_carte DESC, x_carte";
						$res = $mysqli->query($sql);
						$tab = $res->fetch_assoc();
					}
					else { 
						// par defaut on met la carte normale
						$sql = "SELECT x_carte, y_carte, fond_carte, idPerso_carte, image_carte, occupee_carte FROM carte WHERE x_carte >= $x_choix - $perc AND x_carte <= $x_choix + $perc AND y_carte <= $y_choix + $perc AND y_carte >= $y_choix - $perc ORDER BY y_carte DESC, x_carte";
						$res = $mysqli->query($sql);
						$tab = $res->fetch_assoc();
					}
					
					for ($y = $y_choix + $perc; $y >= $y_choix - $perc; $y--) {
						
						echo "<tr align=\"center\">";
						
						if ($y == $y_choix) {
							echo "<th width=40 height=40 bgcolor=\"#cccccc\">$y</b></th>";
						}
						else {
							echo "<th width=40 height=40>$y</b></th>";
						}
						
						for ($x = $x_choix - $perc; $x <= $x_choix + $perc; $x++) {
							
							//les coordonnees sont dans les limites
							if ($x >= X_MIN && $y >= Y_MIN && $x <= $X_MAXD && $y <= $Y_MAXD) { 
								
								
								$dossier_image = "images_perso";
								
								if ($tab["idPerso_carte"] >= 200000) {
									// PNJ
									$dossier_image = "images/pnj";
								}
								
								if($tab["occupee_carte"]) {
									echo "<td width=40 height=40 background=\"../fond_carte/" . $tab["fond_carte"] . "\"><img border=0 src=\"../" . $dossier_image . "/" . $tab["image_carte"] . "\" width=40 height=40><input type=\"checkbox\" name=\"case[]\" value=\"".$x."s".$y."s"."\">" . $tab["x_carte"] . "/".$tab["y_carte"]."</td>";
								}
								else {
									echo "<td width=40 height=40 background=\"../fond_carte/" . $tab["fond_carte"] . "\"><input type=\"checkbox\" name=\"case[]\" value=\"".$x."s".$y."s"."\">" . $tab["x_carte"] . "/" . $tab["y_carte"] . "</td>";
								}
								
								$tab = $res->fetch_assoc();
							}
							else {
								//les coordonnees sont hors limites
								echo "<td width=40 height=40 background=\"../fond_carte/decorO.jpg\">$x/$y</td>";
							}
						}
						echo "</tr>";
					}		
				}
			}
			elseif (isSet($_SESSION['x_choix']) && isSet($_SESSION['y_choix']) && isSet($_SESSION['perc'])) {
				
				$x_choix = $_SESSION['x_choix'];
				$y_choix = $_SESSION['y_choix'];
				
				$perc = $_SESSION['perc'];

				echo '<table border=1 align="left">';
				echo "<tr><th width=40 height=40>y / x</th>";  //affichage des abscisses
				for ($i = $x_choix - $perc; $i <= $x_choix + $perc; $i++)
				{
					if ($i == $x_choix)
					{
						echo "<th class=\"map\" bgcolor=\"#cccccc\">$i</th>";
					}
					else
					{
						echo "<th class=\"map\">$i</th>";
					}
				}
				echo "</tr>";
				
				if(isset($_SESSION['choix_carte'])){
					
					$carte = $_SESSION['choix_carte'];
						
					$sql = "SELECT x_carte, y_carte, fond_carte, idPerso_carte, image_carte, occupee_carte FROM $carte WHERE x_carte >= $x_choix - $perc AND x_carte <= $x_choix + $perc AND y_carte <= $y_choix + $perc AND y_carte >= $y_choix - $perc ORDER BY y_carte DESC, x_carte";
					$res = $mysqli->query($sql);
					$tab = $res->fetch_assoc();
				}
				
				for ($y = $y_choix + $perc; $y >= $y_choix - $perc; $y--) {
					
					echo "<tr align=\"center\">";
					
					if ($y == $y_choix) {
						echo "<th width=40 height=40 bgcolor=\"#cccccc\">$y</b></th>";
					}
					else {
						echo "<th width=40 height=40>$y</b></th>";
					}
					
					for ($x = $x_choix - $perc; $x <= $x_choix + $perc; $x++) {
						
						//les coordonnees sont dans les limites
						if ($x >= X_MIN && $y >= Y_MIN && $x <= $X_MAXD && $y <= $Y_MAXD) {
						
							if($tab["occupee_carte"]) {
								
								$dossier_image = "images_perso";
								
								if ($tab["idPerso_carte"] >= 200000) {
									// PNJ
									$dossier_image = "images/pnj";
								}
								
								//positionnement du milieu
								echo "<td width=40 height=40 background=\"../fond_carte/".$tab["fond_carte"]."\"><img border=0 src=\"../". $dossier_image ."/" . $tab["image_carte"] . "\" width=40 height=40><input type=\"checkbox\" name=\"case[]\" value=\"".$x."s".$y."s"."\">" . $tab["x_carte"] . "/" . $tab["y_carte"] . "</td>";
							}
							else {
								//positionnement du fond
								echo "<td width=40 height=40 background=\"../fond_carte/".$tab["fond_carte"]."\"><input type=\"checkbox\" name=\"case[]\" value=\"".$x."s".$y."s"."\">".$tab["x_carte"]."/".$tab["y_carte"]."</td>"; 
							}
							
							$tab = $res->fetch_assoc();
						}
						else {
							//les coordonnees sont hors limites
							echo "<td width=40 height=40 background=\"../fond_carte/decorO.jpg\">$x/$y</td>";
						}
					}
					echo "</tr>";
				}
			}
			?>
		</form>
	</div>
	
	<!-- Optional JavaScript -->
	<!-- jQuery first, then Popper.js, then Bootstrap JS -->
	<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
		
</body>
</html>