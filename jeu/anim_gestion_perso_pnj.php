<?php
session_start();
require_once("../fonctions.php");
require_once("f_carte.php");

$mysqli = db_connexion();

include ('../nb_online.php');

define('IN_PHPBB', true);
$phpEx = 'php';

$phpbb_root_path = '../forum/';
require_once($phpbb_root_path ."common.php");
require_once($phpbb_root_path ."includes/functions_user.php");

$request->enable_super_globals();

// recupération config jeu
$dispo = config_dispo_jeu($mysqli);
$admin = admin_perso($mysqli, $_SESSION["id_perso"]);

if($dispo || $admin){
	
	if (isset($_SESSION["id_perso"])) {
		
		//recuperation des variables de sessions
		$id = $_SESSION["id_perso"];
		
		// Récupération du camp de l'animateur 
		$sql = "SELECT clan FROM perso WHERE id_perso='$id'";
		$res = $mysqli->query($sql);
		$t = $res->fetch_assoc();
		
		$camp = $t['clan'];
		
		if (anim_perso($mysqli, $id)) {
			
			if (isset($_POST['pseudo']) && trim($_POST['pseudo']) != ""
				&& isset($_POST['bataillon']) && trim($_POST['bataillon']) != ""
				&& isset($_POST['email']) && trim($_POST['email']) != ""
				&& isset($_POST['mdp']) && trim($_POST['mdp']) != ""
				&& isset($_POST['select_matricule']) && trim($_POST['select_matricule']) != ""
				&& isset($_POST['select_type_perso']) && trim($_POST['select_type_perso']) != ""
				&& isset($_POST['select_grade_perso']) && trim($_POST['select_grade_perso']) != ""
				) {
				
				$nom_perso 		= $_POST['pseudo'];
				$nom_bataillon	= $_POST['bataillon'];
				$email_joueur	= $_POST['email'];
				$mdp_joueur		= $_POST['mdp'];
				$matricule		= $_POST['select_matricule'];
				$type_perso		= $_POST['select_type_perso'];
				$grade_perso	= $_POST['select_grade_perso'];
				
				// Récupération pc grade perso
				$sql = "SELECT pc_grade FROM grades WHERE id_grade = '$grade_perso'";
				$res = $mysqli->query($sql);
				$t = $res->fetch_assoc();
				
				$pc_grade_perso = $t['pc_grade'];
				
				$sql = "SELECT username FROM ".$table_prefix."users WHERE username='".$nom_perso."'";
				$resultat_user_forum = $mysqli->query($sql);
				
				$sql = "SELECT nom_perso FROM perso WHERE nom_perso='".$nom_perso."'";
				$resultat_user = $mysqli->query($sql);
				
				$sql2 = "SELECT email_joueur FROM joueur WHERE email_joueur='".$email_joueur."'";
				$resultat_user2 = $mysqli->query($sql2);
					
				if( $resultat_user->num_rows != 0 || $resultat_user_forum->num_rows != 0) {
					echo "<center><font color='red'>Erreur: Le pseudo est déjà choisi ou interdit ! Veuillez en choisir un autre</font></center><br /><br />";
				}
				elseif ($resultat_user2->num_rows != 0) {
					echo "<center><font color='red'>Erreur: Vous avez déjà creer un perso avec cet email, un seul perso par joueur</font></center><br /><br />";
				}
				elseif (!filtremail($email_joueur)) {
					echo "<center><font color='red'>Erreur: Email incorrect</font></center><br /><br />";
				}
				elseif ($mdp_joueur == "") {
					echo "<center><font color='red'>Erreur: Veuillez entrer un mot de passe</font></center><br /><br />";
				}
				else {
					// sécurité camp
					if($camp == "1" || $camp == "2"){
						
						$old_mdp_joueur = $mdp_joueur;
						$mdp_joueur = md5($mdp_joueur);
						
						if($camp == 1){
							$x_min_spawn 		= 160;
							$x_max_spawn 		= 200;
							$y_min_spawn 		= 160;
							$y_max_spawn 		= 200;
							$group_id 			= 8;
							$nom_camp 			= 'Nordistes';
							$ncamp 				= "Nord";
							$couleur_clan_perso = "blue";
							
							if ($type_perso == 1 || $type_perso == 2) {
								$image_chef = "cavalerie_nord.gif";
							}
							else if ($type_perso == 3) {
								$image_chef = "infanterie_nord.gif";
							}
							else if ($type_perso == 4) {
								$image_chef = "soigneur_nord.gif";
							}
							else if ($type_perso == 5) {
								$image_chef = "artillerie_nord.gif";
							}
							else if ($type_perso == 6) {
								$image_chef = "toutou_nord.gif";
							}
						}
						
						if($camp == 2){
							$x_min_spawn 		= 0;
							$x_max_spawn 		= 40;
							$y_min_spawn 		= 0;
							$y_max_spawn 		= 40;
							$group_id 			= 9;
							$nom_camp 			= 'Sudistes';
							$ncamp 				= "Sud";
							$couleur_clan_perso = "red";
							
							if ($type_perso == 1 || $type_perso == 2) {
								$image_chef = "cavalerie_sud.gif";
							}
							else if ($type_perso == 3) {
								$image_chef = "infanterie_sud.gif";
							}
							else if ($type_perso == 4) {
								$image_chef = "soigneur_sud.gif";
							}
							else if ($type_perso == 5) {
								$image_chef = "artillerie_sud.gif";
							}
							else if ($type_perso == 6) {
								$image_chef = "toutou_sud.gif";
							}
						}
					
						$date = time();
						$dla = $date + DUREE_TOUR; // calcul dla
				
						// Récuoération des caracs du perso selon le type d'unité
						$sql = "SELECT * FROM type_unite WHERE id_unite='$type_perso'";
						$res = $mysqli->query($sql);
						$t_type = $res->fetch_assoc();
						
						// Caracs Chef
						$pvMax_chef 	= $t_type['pv_unite'];
						$pmMax_chef 	= $t_type['pm_unite'];
						$pamax_chef 	= $t_type['pa_unite'];
						$recup_chef 	= $t_type['recup_unite'];
						$perc_chef 		= $t_type['perception_unite'];
						$protec_chef 	= $t_type['protection_unite'];
						
						// securité
						$sql = "select email_joueur from joueur,perso where nom_perso='$nom_perso'";
						$res = $mysqli->query($sql);
						$num = $res->num_rows;
						if($num != 0){
							echo "Evitez de bourriner sur l'image ^^ Votre perso est tout de même créé : <a href=\"index.php?creation=ok\">jouer</a>";
						}
						else {
									
							// insertion du nouveau joueur
							$lock = "LOCK TABLE (joueur) WRITE";
							$mysqli->query($lock);
							
							$insert_j = "INSERT INTO joueur (email_joueur, mdp_joueur) VALUES ('$email_joueur', '$mdp_joueur')";
							$result_j = $mysqli->query($insert_j);
							$IDJoueur_perso = $mysqli->insert_id;
							
							$unlock = "UNLOCK TABLES";
							$mysqli->query($unlock);
							
							$bat_spawn_dispo = false;
							
							// verification si fort pas en siége présent pour spawn
							$sql = "SELECT x_instance, y_instance, id_instanceBat, contenance_instance, pv_instance, pvMax_instance FROM instance_batiment WHERE camp_instance='$camp' AND id_batiment='9'";
							$res = $mysqli->query($sql);
							
							while($t = $res->fetch_assoc()) {
							
								$x 				= $t['x_instance'];
								$y 				= $t['y_instance'];
								$id_bat 		= $t['id_instanceBat'];
								$contenance_bat = $t['contenance_instance'];
								$pv_bat 		= $t['pv_instance'];
								$pvMax_bat 		= $t['pvMax_instance'];
								
								// calcul pourcentage pv bat 
								$pourcentage_pv_bat = ceil(($pv_bat * 100) / $pvMax_bat);
								
								$nb_ennemis_siege = nb_ennemis_siege_batiment($mysqli, $x, $y, $camp);
								
								// Récupération du nombre de perso dans ce batiment
								$sql_n = "SELECT count(id_perso) as nb_perso_bat FROM perso_in_batiment WHERE id_instanceBat='$id_bat'";
								$res_n = $mysqli->query($sql_n);
								$t_n = $res_n->fetch_assoc();
								$nb_perso_bat = $t_n['nb_perso_bat'];
								
								if($contenance_bat > $nb_perso_bat && $pourcentage_pv_bat >= 90 && $nb_ennemis_siege < 10){
									// Le perso peut spawn dans le fort
									$bat_spawn_dispo = true;
									
									break;
								}
							}
							
							// Le perso ne peut pas respawn dans les forts
							if (!$bat_spawn_dispo) {
								
								// Verification des fortins
								$sql = "SELECT x_instance, y_instance, id_instanceBat, contenance_instance, pv_instance, pvMax_instance FROM instance_batiment WHERE camp_instance='$camp' AND id_batiment='8'";
								$res = $mysqli->query($sql);
								
								while($t = $res->fetch_assoc()) {
								
									$x 				= $t['x_instance'];
									$y 				= $t['y_instance'];
									$id_bat 		= $t['id_instanceBat'];
									$contenance_bat = $t['contenance_instance'];
									$pv_bat 		= $t['pv_instance'];
									$pvMax_bat 		= $t['pvMax_instance'];
									
									// calcul pourcentage pv bat 
									$pourcentage_pv_bat = ceil(($pv_bat * 100) / $pvMax_bat);
									
									$nb_ennemis_siege = nb_ennemis_siege_batiment($mysqli, $x, $y, $camp);
									
									// Récupération du nombre de perso dans ce batiment
									$sql_n = "SELECT count(id_perso) as nb_perso_bat FROM perso_in_batiment WHERE id_instanceBat='$id_bat'";
									$res_n = $mysqli->query($sql_n);
									$t_n = $res_n->fetch_assoc();
									$nb_perso_bat = $t_n['nb_perso_bat'];
									
									if($contenance_bat > $nb_perso_bat && $pourcentage_pv_bat >= 90 && $nb_ennemis_siege < 10){
										// Le perso peut spawn dans le fortin
										$bat_spawn_dispo = true;
										
										break;
									}
									
								}
							}
							
							// Impossible de spawn dans un fort ou fortin => spawn aléatoire sur la carte
							if (!$bat_spawn_dispo) {
								
								$x = pos_zone_rand_x($x_min_spawn, $x_max_spawn);
								$y = pos_zone_rand_y($y_min_spawn, $y_max_spawn);
							
								// verification si la position est libre
								$libre = verif_pos_libre($mysqli, $x, $y); 
								
								while ($libre == 1) {
									
									// position pas libre => on rechoisit de nouvelles coordonnées
									$x = pos_zone_rand_x($x_min_spawn, $x_max_spawn); 
									$y = pos_zone_rand_y($y_min_spawn, $y_max_spawn);
									$libre = verif_pos_libre($mysqli, $x, $y);
								}
							}					
							
							// insertion nouveau perso / Chef
							$lock = "LOCK TABLE (perso) WRITE";
							$mysqli->query($lock);
							
							$nom_bataillon = addslashes($nom_bataillon);
							
							$insert_sql = "	INSERT INTO perso (id_perso, IDJoueur_perso, nom_perso, x_perso, y_perso, pc_perso, pvMax_perso, pv_perso, pm_perso, pmMax_perso, perception_perso, recup_perso, protec_perso, pa_perso, image_perso, dateCreation_perso, DLA_perso, or_perso, clan, message_perso, chef, bataillon) 
											VALUES ('$matricule','$IDJoueur_perso','$nom_perso','$x','$y','$pc_grade_perso','$pvMax_chef','$pvMax_chef','$pmMax_chef','$pmMax_chef','$perc_chef','$recup_chef','$protec_chef','$pamax_chef','$image_chef',NOW(),FROM_UNIXTIME($dla), '20', $camp, '', 1, '$nom_bataillon')";
							
							if (!$mysqli->query($insert_sql)) {
								printf("Erreur : %s\n", $mysqli->error);
							}
							$id = $mysqli->insert_id;
							
							$unlock = "UNLOCK TABLES";
							$mysqli->query($unlock);
							
							// Creation compte forum 
							$user_row = array(
								'id_perso'				=> $id,
								'username'				=> $nom_perso,
								'user_password'			=> phpbb_hash($old_mdp_joueur),
								'user_email'			=> $email_joueur,
								'group_id'				=> $group_id,
								'user_timezone'			=> 'Europe/Paris',
								'user_lang'				=> 'fr',
								'user_type'				=> USER_NORMAL,
								'user_actkey'			=> '',
								'user_ip'				=> realip(),
								'user_regdate'			=> time(),
								'user_inactive_reason'	=> 0,
								'user_inactive_time'	=> 0,
							);
							user_add($user_row);
							
							if ($bat_spawn_dispo) {
								// On met le perso dans le batiment
								$sql = "INSERT INTO perso_in_batiment VALUES('$id','$id_bat')";
								$mysqli->query($sql);
							} else {
								// insertion du Chef sur la carte
								$sql = "UPDATE carte SET occupee_carte='1' , idPerso_carte='$id', image_carte='$image_chef' WHERE x_carte=$x AND y_carte=$y";
								$mysqli->query($sql);
							}
							
							// dossier courant
							$sql_i = "INSERT INTO perso_as_dossiers VALUES ('$id','1')";
							$mysqli->query($sql_i);
							
							// dossier archives
							$sql_i = "INSERT INTO perso_as_dossiers VALUES ('$id','2')";
							$mysqli->query($sql_i);
							
							// grade Chef = Caporal
							$sql_i = "INSERT INTO perso_as_grade VALUES ('$id','$grade_perso')";
							$mysqli->query($sql_i);
							
							// Arme Cac : sabre
							$sql = "INSERT INTO perso_as_arme (id_perso, id_arme, est_portee) VALUES ('$id','1','1')";
							$mysqli->query($sql);
							
							// Arme distance : pistolet 
							$sql = "INSERT INTO perso_as_arme (id_perso, id_arme, est_portee) VALUES ('$id','4','1')";
							$mysqli->query($sql);
							
							// Insertion competence sieste
							$sql_c = "INSERT INTO perso_as_competence (id_perso, id_competence, nb_points) VALUES ('$id','4','1')";
							$mysqli->query($sql_c);
							
							echo "<center><b>Perso PNJ ".$nom_perso." [".$matricule."] créé avec succès</b></center>";
						}
					}
					else {
						echo "<center>Erreur: Camp invalide !</center><br /><br />";
					}
				}
			}
			
			if (isset($_GET['id_perso_pnj']) && $_GET['id_perso_pnj'] != "" && $_GET['id_perso_pnj'] < 100) {
				
				$id_perso_pnj = $_GET['id_perso_pnj'];
				
				// Vérification qu'on traite bien un perso pnj de son camp
				$sql = "SELECT nom_perso, clan, x_perso, y_perso FROM perso WHERE id_perso='$id_perso_pnj'";
				$res = $mysqli->query($sql);
				$t = $res->fetch_assoc();
				
				$camp_perso_pnj = $t['clan'];
				$x_perso_pnj	= $t['x_perso'];
				$y_perso_pnj	= $t['y_perso'];
				$nom_perso		= $t["nom_perso"];
				
				if ($camp_perso_pnj == $camp) {
					
					// On souhaite téléporter le perso hors carte
					if (isset($_GET['hors_carte']) && $_GET['hors_carte'] == 'ok') {
						
						if (in_bat($mysqli, $id_perso_pnj)) {
							// On supprime le perso du batiment
							$sql = "DELETE FROM perso_in_batiment WHERE id_perso='$id_perso_pnj'";
						}
						else {
							// On supprime le perso de la carte
							$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_perso_pnj' AND y_carte='$y_perso_pnj'";
						}
						$mysqli->query($sql);
						
						// On téléporte le perso hors carte
						$sql = "UPDATE perso SET x_perso='1000', y_perso='1000' WHERE id_perso='$id_perso_pnj'";
						$mysqli->query($sql);
						
						echo "<center><b>Perso PNJ ".$nom_perso." [".$id_perso_pnj."] téléporté hors carte avec succès</b></center>";
					}
					
				}
				else {
					// Tentative de triche de la part d'un anim !
					$text_triche = "L animateur qui possède le perso $id a essayé de faire des modifications sur un perso PNJ qui n est pas de son camp !";
			
					$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id', '$text_triche')";
					$mysqli->query($sql);
				}
			}
?>
		
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<title>Nord VS Sud - Animation</title>
		
		<!-- Required meta tags -->
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		
		<!-- Bootstrap CSS -->
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
	</head>
	<body>
		<div class="container">
		
			<div class="row">
				<div class="col-12">

					<div align="center">
						<h2>Animation - Gestion </h2>
					</div>
				</div>
			</div>
			
			<p align="center">
				<a class="btn btn-primary" href="animation.php">Retour page principale d'animation</a>
				
			</p>
			
			<div class="row">
				<div class="col-12">
					<div align="center">
						<?php
						if (!isset($_GET['creer'])) {
						?>
						<a class="btn btn-success" href="anim_gestion_perso_pnj.php?creer=ok">Créer un perso</a>
						<?php
						}
						?>
					</div>
				</div>
			</div>
			
			<div class="row">
				<div class="col-12">
					<div align="center">
					<?php
					if (isset($_GET['creer']) && $_GET['creer'] == "ok") {
					?>
						<a class="btn btn-danger" href="anim_gestion_perso_pnj.php">Annuler</a><br />
						<br />
						<h2>Création du perso</h2>
						<form method='POST'>
							<div class="form-group">
								<label for="pseudo">Nom</label>
								<input type="text" class="form-control" name="pseudo" id="pseudo" placeholder="">
							</div>
							<div class="form-group">
								<label for="bataillon">Bataillon</label>
								<input type="text" class="form-control" name="bataillon" id="bataillon" placeholder="">
							</div>
							<div class="form-group">
								<label for="select_matricule">Matricule</label>
								<select name='select_matricule' id='select_matricule' class="form-control">
								<?php
								$tab_id_existant = array();
								
								// récupération de la liste des id de perso libres du 3 au 99
								$sql = "SELECT id_perso FROM perso WHERE id_perso < 100 AND id_perso > 2";
								$res = $mysqli->query($sql);
								
								// Création tableau des id existant pour les perso PNJ
								while ($t = $res->fetch_assoc()) {
									array_push($tab_id_existant, $t['id_perso']);
								}
								
								for ($i = 3; $i < 100; $i++) {
									if (!in_array($i, $tab_id_existant)) {
										echo "<option value='".$i."'>Matricule ".$i."</option>";
									}
								}
								
								?>
								</select>
							</div>
							<div class="form-group">
								<label for="select_type_perso">Type de perso</label>
								<select name='select_type_perso' id='select_type_perso' class="form-control">
									<?php
									$sql = "SELECT id_unite, nom_unite FROM type_unite WHERE id_unite = 1";
									$res = $mysqli->query($sql);
									
									while ($t = $res->fetch_assoc()) {
										$id_unite 	= $t['id_unite'];
										$nom_unite 	= $t['nom_unite'];
										
										echo "<option value='".$id_unite."'>".$nom_unite."</option>";
									}
									?>
								</select>
							</div>
							<div class="form-group">
								<label for="select_grade_perso">Grade du perso</label>
								<select name='select_grade_perso' id='select_grade_perso' class="form-control">
									<?php
									$sql = "SELECT id_grade, nom_grade FROM grades WHERE id_grade < 100 AND id_grade != 1 AND id_grade != 22 ORDER BY id_grade";
									$res = $mysqli->query($sql);
									
									while ($t = $res->fetch_assoc()) {
										$id_grade 	= $t['id_grade'];
										$nom_grade 	= $t['nom_grade'];
										
										echo "<option value='".$id_grade."'>".$nom_grade."</option>";
									}
									?>
								</select>
							</div>
							<div class="form-group">
								<label for="email">Email</label>
								<input type="email" class="form-control" name="email" id="email" placeholder="">
							</div>
							<div class="form-group">
								<label for="mdp">Mot de passe</label>
								<input type="password" class="form-control" name="mdp" id="mdp" placeholder="">
							</div>
							<div class="form-group">
								<button type="submit" class="btn btn-primary">Créer</button>
							</div>
						</form>
					<?php
					}
					else {
					?>
						<br />
						<h2>Liste des Persos Non Joueur</h2>
						<div class='table-responsive'>
							<table class='table table-bordered table-hover sortable' style='width:100%'>
								<thead>
									<tr>
										<th style='text-align:center' data-defaultsign='_19'>Matricule</th>
										<th style='text-align:center'>Nom</th>
										<th style='text-align:center' data-defaultsign='_19'>Grade</th>
										<th style='text-align:center'>Position</th>
										<th style='text-align:center'>Action</th>
									</tr>
								</thead>
								<tbody>
								<?php
								$sql = "SELECT perso.id_perso, nom_perso, x_perso, y_perso, clan, grades.id_grade, nom_grade 
										FROM perso, perso_as_grade, grades 
										WHERE perso.id_perso = perso_as_grade.id_perso
										AND perso_as_grade.id_grade = grades.id_grade
										AND perso.id_perso > 2 AND perso.id_perso < 100 
										AND clan = '$camp'
										ORDER BY perso.id_perso";
								$res = $mysqli->query($sql);
								
								while ($t = $res->fetch_assoc()) {
									
									$matricule_perso 	= $t['id_perso'];
									$nom_perso			= $t['nom_perso'];
									$x_perso			= $t['x_perso'];
									$y_perso			= $t['y_perso'];
									$id_grade			= $t['id_grade'];
									$nom_grade			= $t['nom_grade'];
									$camp_perso			= $t['clan'];
									
									// cas particuliers grouillot
									if ($id_grade == 101) {
										$id_grade = "1.1";
									}
									if ($id_grade == 102) {
										$id_grade = "1.2";
									}
									
									echo "<tr>";
									echo "	<td align='center'>".$matricule_perso."</td>";
									echo "	<td>".$nom_perso."</td>";
									echo "	<td data-value='".$id_grade."'>".$nom_grade."</td>";
									if ($x_perso == 1000) {
										echo "	<td align='center'><i>Hors carte</i></td>";
									}
									else {
										echo "	<td align='center'>".$x_perso."/".$y_perso."</td>";
									}
									echo "	<td>";
									echo "		<a href='#' class='btn btn-danger'>Supprimer</a>";
									echo "		<a href='#' class='btn btn-warning'>Téléporter</a>";
									if ($x_perso != 1000) {
										echo "		<a href='anim_gestion_perso_pnj.php?id_perso_pnj=".$matricule_perso."&hors_carte=ok' class='btn btn-info'>Placer hors carte</a>";
									}
									echo "	</td>";
									echo "</tr>";
								}
								?>
								</tbody>
							</table>
						</div>
					<?php
					}
					?>
					</div>
				</div>
			</div>
			
		</div>
	
		<!-- Optional JavaScript -->
		<!-- jQuery first, then Popper.js, then Bootstrap JS -->
		<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
		<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
		<script src="https://drvic10k.github.io/bootstrap-sortable/Scripts/bootstrap-sortable.js"></script>
	</body>
</html>
<?php
		}
		else {
			// Un joueur essaye d'acceder à la page sans être animateur
			$text_triche = "Tentative accés page animation sans y avoir les droits";
			
			$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id', '$text_triche')";
			$mysqli->query($sql);
			
			header("Location:jouer.php");
		}
	}
	else{
		echo "<center><font color='red'>Vous ne pouvez pas accéder à cette page, veuillez vous loguer.</font></center>";
	}
}
else {
	// logout
	$_SESSION = array(); // On écrase le tableau de session
	session_destroy(); // On détruit la session
	
	header("Location:../index2.php");
}
?>		
	