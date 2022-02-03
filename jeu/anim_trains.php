<?php
session_start();
require_once("../fonctions.php");
require_once("f_carte.php");

$mysqli = db_connexion();

include ('../nb_online.php');
$phpbb_root_path = '../forum/';
if (is_dir($phpbb_root_path))
{
	include ($phpbb_root_path .'config.php');
}

// recupération config jeu
$dispo = config_dispo_jeu($mysqli);
$admin = admin_perso($mysqli, $_SESSION["id_perso"]);

if($dispo == '1' || $admin){
	
	if (isset($_SESSION["id_perso"])) {
		
		//recuperation des variables de sessions
		$id = $_SESSION["id_perso"];
		
		if (anim_perso($mysqli, $id)) {

			// Récupération du camp de l'animateur 
			$sql = "SELECT clan FROM perso WHERE id_perso='$id'";
			$res = $mysqli->query($sql);
			$t = $res->fetch_assoc();
			
			$camp = $t['clan'];
			
			if ($camp == '1') {
				$nom_camp = 'Nord';
			}
			else if ($camp == '2') {
				$nom_camp = 'Sud';
			}
			else if ($camp == '3') {
				$nom_camp = 'Indien';
			}
			
			$mess = "";
			$mess_erreur = "";
			
			if (isset($_GET['creer_train_liaison']) && trim($_GET['creer_train_liaison']) != "") {
				
				$id_gares_liaison = $_GET['creer_train_liaison'];
				
				$t_gares = explode(',',$id_gares_liaison);
				$id_gare1_liaison = $t_gares[0];
				$id_gare2_liaison = $t_gares[1];
				
				// On vérifie que id_gare1_liaison et id_gare2_liaison sont bien des numeriques
				$verif_id_g1 = preg_match("#^[0-9]*[0-9]$#i","$id_gare1_liaison");
				$verif_id_g2 = preg_match("#^[0-9]*[0-9]$#i","$id_gare2_liaison");
				
				if ($verif_id_g1 && $verif_id_g2) {
				
					$sql = "SELECT x_instance, y_instance FROM instance_batiment WHERE id_instanceBat='$id_gare1_liaison'";
					$res = $mysqli->query($sql);
					$t = $res->fetch_assoc();
					
					$x_gare1 = $t['x_instance'];
					$y_gare1 = $t['y_instance'];
					
					$sql = "SELECT x_instance, y_instance FROM instance_batiment WHERE id_instanceBat='$id_gare2_liaison'";
					$res = $mysqli->query($sql);
					$t = $res->fetch_assoc();
					
					$x_gare2 = $t['x_instance'];
					$y_gare2 = $t['y_instance'];
					
					// Est ce que la gare 1 est dans une autre liaison ?
					$sql = "SELECT * FROM liaisons_gare WHERE id_gare1='$id_gare1_liaison' OR id_gare2='$id_gare1_liaison'";
					$res = $mysqli->query($sql);
					$num_liaisons_g1 = $res->num_rows;
					
					if ($num_liaisons_g1 > 1) {
						
						// Est ce que la gare 2 est dans une autre liaison ?
						$sql = "SELECT * FROM liaisons_gare WHERE id_gare1='$id_gare2_liaison' OR id_gare2='$id_gare2_liaison'";
						$res = $mysqli->query($sql);
						$num_liaisons_g2 = $res->num_rows;
						
						if ($num_liaisons_g2 > 1) {
							$x_gare_respaw_train = $x_gare1;
							$y_gare_respaw_train = $y_gare1;
						}
						else {
							$x_gare_respaw_train = $x_gare2;
							$y_gare_respaw_train = $y_gare2;
						}
					}
					else {
						$x_gare_respaw_train = $x_gare1;
						$y_gare_respaw_train = $y_gare1;
					}
					
					if (isset($x_gare_respaw_train) && $x_gare_respaw_train != NULL && $x_gare_respaw_train != 0) {
					
						// On cherches les rails autours de la gare 1
						$sql = "SELECT x_carte, y_carte FROM carte 
								WHERE (fond_carte='rail.gif' OR fond_carte='rail_1.gif' OR fond_carte='rail_2.gif' OR fond_carte='rail_3.gif' OR fond_carte='rail_4.gif' OR fond_carte='rail_5.gif' OR fond_carte='rail_7.gif' OR fond_carte='railP.gif')
								AND x_carte >= $x_gare_respaw_train - 2 AND x_carte <= $x_gare_respaw_train +2 AND y_carte >= $y_gare_respaw_train - 2 AND y_carte <= $y_gare_respaw_train + 2";
						$res = $mysqli->query($sql);
						
						if ($num_liaisons_g1 > 1 && $num_liaisons_g2 > 1) {
							// Il faut prendre le rail le plus en direction g1 -> g2
							
							$x_tmp = -1;
							$y_tmp = -1;
							
							while ($t = $res->fetch_assoc()) {
								
								$x_rail = $t['x_carte'];
								$y_rail = $t['y_carte'];
								
								echo "rail : ".$x_rail."/".$y_rail."<br />";
								
								if ($x_tmp == -1) {
									
									$x_tmp = $x_rail;
									$y_tmp = $y_rail;
									
								}
								
								if ($x_gare1 < $x_gare2 && $x_tmp <= $x_rail) {
									$x_respawn_train = $x_tmp;
									$y_respawn_train = $y_tmp;
								}
								else if ($x_gare1 > $x_gare2 && $x_tmp >= $x_rail) {
									$x_respawn_train = $x_tmp;
									$y_respawn_train = $y_tmp;
								}
								else if ($y_gare1 < $y_gare2 && $y_tmp <= $y_rail) {
									$x_respawn_train = $x_tmp;
									$y_respawn_train = $y_tmp;
								}
								else if ($y_gare1 > $y_gare2 && $y_tmp >= $y_rail) {
									$x_respawn_train = $x_tmp;
									$y_respawn_train = $y_tmp;
								}
							}
						}
						else {
							
							// un seul rail
							$t = $res->fetch_assoc();
							
							$x_respawn_train = $t['x_carte'];
							$y_respawn_train = $t['y_carte'];
							
						}
						
						// Creation train
						$lock = "LOCK TABLE instance_batiment WRITE";
						$mysqli->query($lock);
						
						$sql = "INSERT INTO instance_batiment (niveau_instance, id_batiment, nom_instance, pv_instance, pvMax_instance, x_instance, y_instance, camp_instance, contenance_instance) 
								VALUES ('1', '12', '', '2500', '2500', '".$x_respawn_train."', '".$y_respawn_train."', '".$camp."', '50')";
						$mysqli->query($sql);
						$id_new_train = $mysqli->insert_id;
						
						$unlock = "UNLOCK TABLES";
						$mysqli->query($unlock);
						
						// MAJ liaison
						$sql = "UPDATE liaisons_gare SET id_train='$id_new_train' WHERE id_gare1='$id_gare1_liaison' AND id_gare2='$id_gare2_liaison'";
						$mysqli->query($sql);
						
						$image_train = 'b12';
						if ($camp == '1') {
							$image_train .= 'b.gif';
						}
						else {
							$image_train .= 'r.gif';
						}
						
						// update carte
						$sql = "UPDATE carte SET idPerso_carte='$id_new_train', occupee_carte='1', image_carte='$image_train' WHERE x_carte='$x_respawn_train' AND y_carte='$y_respawn_train'";
						$mysqli->query($sql);
						
						$mess .= "Création du train ".$id_new_train." en position ".$x_respawn_train."/".$y_respawn_train." entre les gare $id_gare1_liaison et $id_gare2_liaison terminé";
						
						$texte = addslashes($mess);
									
						// log_action_animation
						$sql = "INSERT INTO log_action_animation(date_acces, id_perso, page, action, texte) VALUES (NOW(), '$id', 'anim_train.php', 'Création train', '$texte')";
						$mysqli->query($sql);
					}
					else {
						$mess_erreur .= "Impossible de rajouter un train pour ces liaisons";
					}
				}
				else {
					$mess_erreur .= "Merci de ne pas jouer avec les paramètres de l'url...";
				}
			}
			
			if (isset($_GET['detruire_obstacle']) && trim($_GET['detruire_obstacle']) != "") {
				
				$id_obstacle = $_GET['detruire_obstacle'];
				
				$verif_id_obstacle = preg_match("#^[0-9]*[0-9]$#i","$id_obstacle");
				
				if ($verif_id_obstacle) {
					
					// On vérifie que l'obstacle est bien une simple barricade...
					$sql = "SELECT id_batiment, x_instance, y_instance FROM instance_batiment WHERE id_instanceBat='$id_obstacle'";
					$res = $mysqli->query($sql);
					$t = $res->fetch_assoc();
					
					$id_batiment 	= $t['id_batiment'];
					$x_obstacle		= $t['x_instance'];
					$y_obstacle		= $t['y_instance'];
 					
					if ($id_batiment == 1) {
						
						$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_obstacle' AND y_carte='$y_obstacle'";
						$mysqli->query($sql);
						
						$sql = "DELETE FROM instance_batiment WHERE id_instanceBat='$id_batiment'";
						$mysqli->query($sql);
						
						$mess .= "Obstacle ".$id_batiment." en ".$x_obstacle."/".$y_obstacle." détruit !";
						
						$texte = addslashes($mess);
									
						// log_action_animation
						$sql = "INSERT INTO log_action_animation(date_acces, id_perso, page, action, texte) VALUES (NOW(), '$id', 'anim_train.php', 'Destruction obstacle', '$texte')";
						$mysqli->query($sql);
					}
					else {
						$mess_erreur .= "L'obstacle ne semble pas être une barricade, destruction impossible";
					}
				}
				else {
					$mess_erreur .= "Impossible de rajouter un train pour ces liaisons";
				}
			}
			
			if (isset($_GET['detruire_train']) && trim($_GET['detruire_train']) != "") {
				
				$id_train_detruire = $_GET['detruire_train'];
				
				$verif_id_train = preg_match("#^[0-9]*[0-9]$#i","$id_train_detruire");
				
				if ($verif_id_train) {
					
					// On vérifie que l'id correspond bien à un train existant de son camp
					$sql = "SELECT * FROM instance_batiment WHERE id_batiment='12' AND id_instanceBat='$id_train_detruire' AND camp_instance='$camp'";
					$res = $mysqli->query($sql);
					
					$verif_exist_train = $res->num_rows;
					
					if ($verif_exist_train) {
						
						$t = $res->fetch_assoc();
						
						$x_instance_train = $t['x_instance'];
						$y_instance_train = $t['y_instance'];
						
						// MAJ carte
						$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_instance_train' AND y_carte='$y_instance_train'";
						$mysqli->query($sql);
						
						$sql = "DELETE FROM instance_batiment WHERE id_instanceBat='$id_train_detruire'";
						$mysqli->query($sql);
						
						$sql = "UPDATE liaisons_gare SET id_train=NULL WHERE id_train='$id_train_detruire'";
						$mysqli->query($sql);
						
						$mess .= "Train [".$id_train_detruire."] en ".$x_instance_train."/".$y_instance_train." détruit !";
						
						$texte = addslashes($mess);
									
						// log_action_animation
						$sql = "INSERT INTO log_action_animation(date_acces, id_perso, page, action, texte) VALUES (NOW(), '$id', 'anim_train.php', 'Destruction train', '$texte')";
						$mysqli->query($sql);
					}
					else {
						$mess_erreur .= "Ce train n'existe pas ou ne fait pas parti de votre camp";
					}					
				}
				else {
					$mess_erreur .= "Id incorrect";
				}
			}
			
			if (isset($_POST['hid_gare1_liaison']) && trim($_POST['hid_gare1_liaison']) != ""
				&& isset($_POST['select_liaison_gare']) && trim($_POST['select_liaison_gare']) != "") {
				
				$gare1_liaison = $_POST['hid_gare1_liaison'];
				$gare2_liaison = $_POST['select_liaison_gare'];
				
				$verif_id_gare1 = preg_match("#^[0-9]*[0-9]$#i","$gare1_liaison");
				$verif_id_gare2 = preg_match("#^[0-9]*[0-9]$#i","$gare2_liaison");
				
				if ($verif_id_gare1 && $verif_id_gare2) {
					
					// On vérifie que les 2 gares existent et sont du camp de l'animateur
					$sql = "SELECT * FROM instance_batiment WHERE id_instanceBat='$gare1_liaison' AND camp_instance='$camp'";
					$res = $mysqli->query($sql);
					$verif_gare1 = $res->num_rows;
					
					$sql = "SELECT * FROM instance_batiment WHERE id_instanceBat='$gare2_liaison' AND camp_instance='$camp'";
					$res = $mysqli->query($sql);
					$verif_gare2 = $res->num_rows;
					
					if ($verif_gare1 && $verif_gare2) {
						
						// Création de la liaison
						$sql = "INSERT INTO liaisons_gare (id_gare1, id_gare2, id_train, direction) VALUES ('$gare1_liaison', '$gare2_liaison', NULL, '$gare2_liaison')";
						$mysqli->query($sql);
						
						$mess .= "Liaison entre la gare [".$gare1_liaison."] et la gare [".$gare2_liaison."] créée";
						
						$texte = addslashes($mess);
									
						// log_action_animation
						$sql = "INSERT INTO log_action_animation(date_acces, id_perso, page, action, texte) VALUES (NOW(), '$id', 'anim_train.php', 'Création liaison', '$texte')";
						$mysqli->query($sql);
						
					}
					else {
						$mess_erreur .= "Ces gares n'existent pas ou ne sont pas de votre camp !";
					}
				}
				else {
					$mess_erreur .= "Données incorrectes";
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
						<h2>Animation - Gestion des trains</h2>
					</div>
				</div>
			</div>
			
			<p align="center"><a class="btn btn-primary" href="animation.php">Retour page principale d'animation</a></p>
			
			<div class="row">
				<div class="col-12">
					<div align="center">
						<?php
						echo "<font color='blue'>".$mess."</font><br />";
						echo "<font color='red'><b>".$mess_erreur."</b></font><br />";
						?>
					</div>
				</div>
			</div>
			
			<div class="row">
				<div class="col-12">
					<div align="center">
						<h2>Liaisons existantes</h2>
						<div id="table_trains" class="table-responsive">						
							<table border="1" class='table'>
								<tr>
									<th style='text-align:center'>Train</th>
									<th style='text-align:center'>Gare 1</th>
									<th style='text-align:center'>Gare 2</th>
									<th style='text-align:center'>Position et direction actuelle du train</th>
									<th style='text-align:center'>Autres informations</th>
									<th style='text-align:center'>Actions</th>
								</tr>
								<?php
								$sql = "SELECT id_gare1, id_gare2, id_train, direction FROM liaisons_gare, instance_batiment 
										WHERE liaisons_gare.id_gare1 = instance_batiment.id_instanceBat
										AND instance_batiment.camp_instance='$camp'";
								$res = $mysqli->query($sql);
								
								while ($t = $res->fetch_assoc()) {
									
									$num_perso_train = 0;
									
									$id_gare1 		= $t['id_gare1'];
									$id_gare2 		= $t['id_gare2'];
									$id_train		= $t['id_train'];
									$gare_direction	= $t['direction'];
									
									$obstacle_train = false;
									
									// Récupération infos train
									$sql_t = "SELECT pv_instance, pvMax_instance, x_instance, y_instance FROM instance_batiment WHERE id_instanceBat='$id_train'";
									$res_t = $mysqli->query($sql_t);
									$t_t = $res_t->fetch_assoc();
									
									$pv_train		= $t_t['pv_instance'];
									$pvMax_train	= $t_t['pvMax_instance'];
									$x_train		= $t_t['x_instance'];
									$y_train		= $t_t['y_instance'];
									
									if ($id_train != NULL && $id_train != 0) {
										// Est ce que le train a un obstacle devant lui l'empéchant d'avancer ?
										$sql_o = "SELECT x_carte, y_carte, idPerso_carte FROM carte 
													WHERE (fond_carte='rail.gif' OR fond_carte='rail_1.gif' OR fond_carte='rail_2.gif' OR fond_carte='rail_3.gif' OR fond_carte='rail_4.gif' OR fond_carte='rail_5.gif' OR fond_carte='rail_7.gif' OR fond_carte='railP.gif')
													AND x_carte >= $x_train - 1 AND x_carte <= $x_train + 1
													AND y_carte >= $y_train - 1 AND y_carte <= $y_train + 1
													AND occupee_carte = '1'
													AND idPerso_carte != '$id_train'
													AND idPerso_carte >= 50000 AND idperso_carte < 200000";
										$res_o = $mysqli->query($sql_o);
										$t_o = $res_o->fetch_assoc();
										$nb_obstacles = $res_o->num_rows;
										
										if ($nb_obstacles)
										{
											$id_obstacle = $t_o['idPerso_carte'];
											$x_obstacle = $t_o['x_carte'];
											$y_obstacle = $t_o['y_carte'];
											$obstacle_train = true;
										}
										
										// Est ce qu'il y a des passagers dans le train ?
										$sql_p = "SELECT id_perso FROM perso_in_train WHERE id_train='$id_train'";
										$res_p = $mysqli->query($sql_p);
										$num_perso_train = $res_p->num_rows;
									}
									
									// Récupération infos gare 1
									$sql_g1 = "SELECT nom_instance, pv_instance, pvMax_instance FROM instance_batiment WHERE id_instanceBat='$id_gare1'";
									$res_g1 = $mysqli->query($sql_g1);
									$t_g1 = $res_g1->fetch_assoc();
									
									$nom_gare1		= $t_g1['nom_instance'];
									$pv_gare1		= $t_g1['pv_instance'];
									$pvMax_gare1	= $t_g1['pvMax_instance'];
									
									// Calcul pourcentage pv du batiment 
									$pourc_pv_gare1 = ($pv_gare1 / $pvMax_gare1) * 100;
									
									// Récupération infos gare 2
									$sql_g2 = "SELECT nom_instance, pv_instance, pvMax_instance FROM instance_batiment WHERE id_instanceBat='$id_gare2'";
									$res_g2 = $mysqli->query($sql_g2);
									$t_g2 = $res_g2->fetch_assoc();
									
									$nom_gare2		= $t_g2['nom_instance'];
									$pv_gare2		= $t_g2['pv_instance'];
									$pvMax_gare2	= $t_g2['pvMax_instance'];
									
									// Calcul pourcentage pv du batiment 
									$pourc_pv_gare2 = ($pv_gare2 / $pvMax_gare2) * 100;
									
									if ($gare_direction == $id_gare1) {
										$nom_gare_direction = $nom_gare1;
									}
									else {
										$nom_gare_direction = $nom_gare2;
									}
									
									echo "<tr>";
									
									echo "	<td>";
									if ($id_train != NULL && $id_train != 0) {
										echo "<img src='../images_perso/b12b.gif' width='40' height='40' />Train [<a href='evenement.php?infoid=".$id_train."' target='_blank'>".$id_train."</a>]";
										$pourc_pv_train = affiche_jauge($pv_train, $pvMax_train); 
										echo round($pourc_pv_train,2)."% ou $pv_train/$pvMax_train";
									}
									else {
										echo "<i>Aucun train sur cette liaison</i>";
									}
									echo "	</td>";
									echo "	<td>";
									echo "<img src='../images_perso/b11b.png' width='40' height='40' />Gare ".$nom_gare1." [<a href='evenement.php?infoid=".$id_gare1."' target='_blank'>".$id_gare1."</a>]";
									if ($pv_gare1 != null) {
										$pourc_pv_gare1 = affiche_jauge($pv_gare1, $pvMax_gare1); 
										echo round($pourc_pv_gare1,2)."% ou $pv_gare1/$pvMax_gare1";
									}
									else {
										echo "<br /><i>Gare détruite</i>";
									}
									echo "	</td>";
									echo "	<td>";
									echo "<img src='../images_perso/b11b.png' width='40' height='40' />Gare ".$nom_gare2." [<a href='evenement.php?infoid=".$id_gare2."' target='_blank'>".$id_gare2."</a>]";
									if ($pv_gare2 != null) {
										$pourc_pv_gare2 = affiche_jauge($pv_gare2, $pvMax_gare2); 
										echo round($pourc_pv_gare2,2)."% ou $pv_gare2/$pvMax_gare2";
									}
									else {
										echo "<br /><i>Gare détruite</i>";
									}
									echo "	</td>";
									echo "	<td>";
									if ($id_train != NULL && $id_train != 0) {
										echo "Position actuelle : ".$x_train."/".$y_train." - En direction de la Gare ".$nom_gare_direction." [<a href='evenement.php?infoid=".$gare_direction."' target='_blank'>".$gare_direction."</a>]";
									}
									echo "	</td>";
									
									echo "	<td>";
									if ($obstacle_train) {
										echo "<b>Un obstacle bloque le train en ".$x_obstacle."/".$y_obstacle."</b><br />";
									}
									if ($gare_direction == $id_gare1 && $pourc_pv_gare1 < 50) {
										echo "<b>La gare de destination est en trop mauvais état, le train ne circulera pas</b><br />";
									}
									if ($gare_direction == $id_gare2 && $pourc_pv_gare2 < 50) {
										echo "<b>La gare de destination est en trop mauvais état, le train ne circulera pas</b><br />";
									}
									if ($gare_direction == $id_gare1 && $pv_gare1 == null) {
										echo "<b>La gare de destination est détruire, le train ne circulera pas</b><br />";
									}
									if ($gare_direction == $id_gare2 && $pv_gare2 == null) {
										echo "<b>La gare de destination est détruire, le train ne circulera pas</b><br />";
									}
									if (isset($num_perso_train) && $num_perso_train > 0) {
										echo "<b>Il y a ".$num_perso_train." des persos dans le train</b><br />";
									}
									echo "	</td>";
									
									echo "	<td>";
									if ($id_train == NULL || $id_train == '' || $id_train == 0) {
										echo "<a href='anim_trains.php?creer_train_liaison=".$id_gare1.",".$id_gare2."' class='btn btn-primary'>Ajouter un train sur cette liaison</a>";
									}
									if ($obstacle_train) {
										echo "<a href='anim_trains.php?detruire_obstacle=".$id_obstacle."' class='btn btn-warning'>Détruire l'obstacle</a>";
									}
									if ($id_train != NULL && $id_train != 0 && isset($num_perso_train) && $num_perso_train == 0) {
										echo "<a href='anim_trains.php?detruire_train=".$id_train."' class='btn btn-danger'>Détruire le train</a>";
									}
									echo "	</td>";
									
									echo "</tr>";
								}
								?>
							</table>
						</div>
					</div>
				</div>
			</div>
			
			<br />
			
			<div class="row">
				<div class="col-12">
					<div align="center">
						<h2>Liaisons manquantes</h2>
						<?php
						// Liste des gares dans liaisons_gare
						$array_gares_liaison = array();
						$sql = "SELECT DISTINCT(id_gare) FROM (
								SELECT DISTINCT(id_gare1) as id_gare FROM liaisons_gare ,instance_batiment 
								WHERE liaisons_gare.id_gare1 = instance_batiment.id_instanceBat
								AND instance_batiment.camp_instance='$camp'
								UNION
								SELECT DISTINCT(id_gare2) as id_gare FROM liaisons_gare ,instance_batiment 
								WHERE liaisons_gare.id_gare1 = instance_batiment.id_instanceBat
								AND instance_batiment.camp_instance='$camp'
								) tb ORDER BY id_gare ASC";
						$res = $mysqli->query($sql);
						$nb_gares_liaisons = $res->num_rows;
						while ($t = $res->fetch_assoc()) {
							
							$id_gare_liaison = $t['id_gare'];
							
							array_push($array_gares_liaison, $id_gare_liaison);
							
						}
						
						// Liste des gares dans instance batiment
						$array_gares = array();
						$sql_i = "SELECT id_instanceBat FROM instance_batiment WHERE camp_instance='$camp' AND id_batiment='11' ORDER BY id_instanceBat ASC";
						$res_i = $mysqli->query($sql_i);
						$nb_gares = $res_i->num_rows;
						while ($t_i = $res_i->fetch_assoc()) {
							
							$id_gare = $t_i['id_instanceBat'];
							
							array_push($array_gares, $id_gare);
							
						}
						
						$diff_gares = array_diff($array_gares, $array_gares_liaison);
						
						if (empty($diff_gares)) {
							echo "<b>Toutes les gares du $nom_camp possèdent bien une liaison</b><br />";
						}
						else {							
							if (isset($_GET['creer_liaison']) && trim($_GET['creer_liaison']) != "") {
								
								$id_gare_creer_liaison = $_GET['creer_liaison'];
								
								echo "<b>Création de la liaison pour la gare [<a href='evenement.php?infoid=".$id_gare_creer_liaison."' target='_blank'>".$id_gare_creer_liaison."</a>]</b><br />";
								
								$sql_gares = "SELECT id_instanceBat, nom_instance FROM instance_batiment 
										WHERE camp_instance='$camp' AND id_batiment='11' AND id_instanceBat != '$id_gare_creer_liaison' 
										ORDER BY id_instanceBat ASC";
								$res_gares = $mysqli->query($sql_gares);
								
								echo "<form method='POST' action='anim_trains.php'>";
								echo "	<select name='select_liaison_gare'>";
								while ($t_gares = $res_gares->fetch_assoc()) {
									
									$id_gare_liaison 	= $t_gares['id_instanceBat'];
									$nom_gare_liasion	= $t_gares['nom_instance'];
									
									echo "		<option value='".$id_gare_liaison."'>Gare ".$nom_gare_liasion." [".$id_gare_liaison."]</option>";
									
								}
								echo "	</select>";
								echo "	<input type='hidden' value='".$id_gare_creer_liaison."' name='hid_gare1_liaison' class='btn btn-success'>";
								echo "	<input type='submit' value='Créer' class='btn btn-success'>";
								echo "</form>";
								
								echo "<a href='anim_trains.php' class='btn btn-danger'>Annuler</a>";
							}
							else {
								echo "<b>Les gares suivantes ne possèdent pas de liaison</b><br />";
								
								foreach ($diff_gares as $gare){								
									echo "Gare [<a href='evenement.php?infoid=".$gare."' target='_blank'>".$gare."</a>] <a href='anim_trains.php?creer_liaison=".$gare."' class='btn btn-warning'>Créer la liaison</a><br />";
								}
							}
						}
						
						$diff_gares2 = array_diff($array_gares_liaison, $array_gares);
						
						if ($nb_gares_liaisons > $nb_gares || !empty($diff_gares2)) {
							echo "<b>Des gares ont été détruites sur certaines liaisons : </b><br />";
							
							foreach ($diff_gares2 as $gare){
								echo "Gare id : ".$gare."<br />";
							}
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
