<?php
session_start();
require_once("../fonctions.php");
require_once("f_combat.php");
require_once("f_carte.php");
require_once("f_recrutement.php");

$mysqli = db_connexion();

include ('../nb_online.php');

// recupération config jeu
$dispo = config_dispo_jeu($mysqli);
$admin = admin_perso($mysqli, $_SESSION["id_perso"]);

if($dispo == '1' || $admin){
	
	if(isset($_SESSION["id_perso"])){
		
		//recuperation des varaibles de sessions
		$id = $_SESSION["id_perso"];
		
		$verif_id_perso_session = preg_match("#^[0-9]*[0-9]$#i","$id");
		
		if ($verif_id_perso_session) {
		
			$sql = "SELECT idJoueur_perso, chef, pv_perso, nom_perso, clan, point_armee_grade FROM perso, perso_as_grade, grades
					WHERE perso.id_perso = perso_as_grade.id_perso
					AND perso_as_grade.id_grade = grades.id_grade 
					AND perso.id_perso='$id'";
			$res = $mysqli->query($sql);
			$tab = $res->fetch_assoc();
			
			$testpv 	= $tab['pv_perso'];
			$id_joueur	= $tab["idJoueur_perso"];
			$chef 		= $tab["chef"];
			$nom_chef	= $tab["nom_perso"];
			$clan		= $tab["clan"];
			$pg		= $tab["point_armee_grade"];
			
			$couleur_camp_chef = couleur_clan($clan);
			
			if ($testpv <= 0) {
				echo "<font color=red>Vous êtes mort...</font>";
			}
			else {
				
				// Seul le chef peut gérer ses grouillots
				if ($chef) {
					
				?>
<html>
	<head>
		<title>Nord VS Sud</title>
		
		<!-- Required meta tags -->
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		
		<!-- Bootstrap CSS -->
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
	</head>
	
	<body>
		<div class="container-fluid">
			<nav class="navbar navbar-expand-lg navbar-light">
				<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
					<span class="navbar-toggler-icon"></span>
				</button>
				<div class="collapse navbar-collapse" id="navbarSupportedContent">
					<ul class="navbar-nav mr-auto nav-pills">
						<li class="nav-item">
							<a class="nav-link" href="profil.php">Profil</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" href="ameliorer.php">Améliorer son perso</a>
						</li>
						<?php
						if($chef) {
							echo "<li class='nav-item'><a class='nav-link' href=\"recrutement.php\">Recruter des grouillots</a></li>";
							echo "<li class='nav-item'><a class='nav-link active' href=\"#\">Gérer ses grouillots</a></li>";
						}
						?>
						<li class="nav-item">
							<a class="nav-link" href="equipement.php">Equiper son perso</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" href="compte.php">Gérer son Compte</a>
						</li>
					</ul>
				</div>
			</nav>
			
			<hr>
	
			<br /><br /><center><h1>Gestion des grouillots</h1></center>
			
			<div align=center><a href="jouer.php"> <input type="button" value="Retour au jeu"> </a></div>
			<br />
					<?php
				
					// On souhaite renommer un grouillot
					if (isset($_POST["renommer"]) && isset($_POST["nom_grouillot"]) && isset($_POST["matricule_hidden"])) {
						
						$nouveau_nom_grouillot = filter_input(INPUT_POST, "nom_grouillot", FILTER_SANITIZE_STRING);
						$matricule_grouillot = $_POST["matricule_hidden"];
						
						// controle matricule perso
						$verif_matricule = preg_match("#^[0-9]*[0-9]$#i","$matricule_grouillot");
						
						if ($verif_matricule) {
							
							// On vérifie que le grouillot lui appartient bien
							$sql = "SELECT count(id_perso) as nb_perso FROM perso WHERE id_perso='$matricule_grouillot' AND idJoueur_perso='$id_joueur'";
							
							if($res = $mysqli->query($sql)) {
								
								$tab = $res->fetch_assoc();
								
								$nb = $tab["nb_perso"];
								
								if ($nb == 1) {
						
									$nouveau_nom_grouillot = trim($nouveau_nom_grouillot);
									if (strlen($nouveau_nom_grouillot) >= 2 && strlen($nouveau_nom_grouillot) <= 25 && !ctype_digit($nouveau_nom_grouillot) && strpos($nouveau_nom_grouillot,'--') === false) {
										
										$nouveau_nom_grouillot = $mysqli->real_escape_string($nouveau_nom_grouillot);
										
										// On vérifie si ce nom est déjà utilisé
										$sql = "SELECT id_perso FROM perso WHERE nom_perso='$nouveau_nom_grouillot'";
										$res = $mysqli->query($sql);
										$verif = $res->num_rows;
										
										if (!$verif) {
										
											$sql = "UPDATE perso SET nom_perso = '$nouveau_nom_grouillot' WHERE id_perso = '$matricule_grouillot'";
											$mysqli->query($sql);
											
											echo "<center><font color='blue'>Vous avez renommé un de vos grouillots en $nouveau_nom_grouillot</font></center>";
										}
										else {
											echo "<center><b><font color='red'>Ce nom est déjà utilisé, veuillez en choisir un autre</font></b></center>";
										}
										
									} else {
										echo "<center><b><font color='red'>Veuillez rentrer une valeur correcte sans caractères spéciaux comprise entre 1 et 25 caractères pour le nom de votre grouillot</font></b></center>";
									}
								}
								else {
									// Tentative de triche ?!
									echo "<center><font color='red'>Le perso n'a pas pu être renommé, si le problème persiste, veuillez contacter l'administrateur.</font></center><br/>";
								}
							}
							else {
								// Tentative de triche ?!
								echo "<center><font color='red'>Le perso n'a pas pu être renommé, si le problème persiste, veuillez contacter l'administrateur.</font></center><br/>";
							}
						}
						else {
							// Tentative de triche ?!
							echo "<center><font color='red'>Le matricule du perso à renommer est mal renseigné, si le problème persiste, veuillez contacter l'administrateur.</font></center><br/>";
						}
					}
					
					// On souhaite renvoyer un grouillot
					if (isset($_POST["matricule_renvoi_hidden"])) {
						
						$matricule_grouillot_renvoi = $_POST["matricule_renvoi_hidden"];
						
						// controle matricule perso
						$verif_matricule = preg_match("#^[0-9]*[0-9]$#i","$matricule_grouillot_renvoi");
						
						if ($verif_matricule) {
							
							// On vérifie que le grouillot lui appartient bien
							$sql = "SELECT id_perso FROM perso WHERE id_perso='$matricule_grouillot_renvoi' AND idJoueur_perso='$id_joueur'";
							$res = $mysqli->query($sql);
							$nb = $res->num_rows;

							if ($nb) {

								if (verif_perso_est_dans_fort_ou_fortin($mysqli, $id) && verif_perso_est_dans_fort_ou_fortin($mysqli, $matricule_grouillot_renvoi)) {
									
									// On regarde si le perso n'est pas chef d'une compagnie 
									$sql = "SELECT count(id_perso) as is_chef FROM perso_in_compagnie WHERE id_perso='$matricule_grouillot_renvoi' AND poste_compagnie='1'";
									$res = $mysqli->query($sql);
									$tab = $res->fetch_assoc();
								
									$is_chef = $tab["is_chef"];
									
									if (!$is_chef) {
									
										$sql = "SELECT id_compagnie FROM perso_in_compagnie WHERE id_perso='$matricule_grouillot_renvoi'";
										$res = $mysqli->query($sql);
										$nb = $res->num_rows;

										if ($nb) {
											$tab = $res->fetch_assoc();

											$id_compagnie = $tab['id_compagnie'];

											// On regarde si le perso n'a pas de dette dans une banque de compagnie
											$sql = "SELECT SUM(montant) as thune_en_banque FROM histobanque_compagnie 
												WHERE id_perso='$matricule_grouillot_renvoi' 
												AND id_compagnie='$id_compagnie'";
											$res = $mysqli->query($sql);
											$tab = $res->fetch_assoc();

											$thune_en_banque = $tab["thune_en_banque"];
										} else {
											$thune_en_banque = 0;
										}
										
										if ($thune_en_banque >= 0) {
											// Recup info perso
											$sql = "SELECT nom_perso, clan FROM perso WHERE id_perso='$matricule_grouillot_renvoi'";
											$res = $mysqli->query($sql);
											$t = $res->fetch_assoc();
											$nom_perso =$t['nom_perso'];
											$clan =$t['clan'];
											$couleur_camp = couleur_clan($clan);
										
											// Ok - renvoi du perso						
											$sql = "UPDATE perso SET est_renvoye=1 WHERE id_perso='$matricule_grouillot_renvoi'";
											$mysqli->query($sql);

											// maj evenements
											$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) 
												VALUES ($matricule_grouillot_renvoi,'<font color=$couleur_camp><b>$nom_perso</b></font>','<b>a été renvoyé dans ses foyers</b>',NULL,'','',NOW(),'0')";
											$mysqli->query($sql);

											$sql = "INSERT INTO `cv` (IDActeur_cv, nomActeur_cv, gradeActeur_cv, IDCible_cv, nomCible_cv, gradeCible_cv, date_cv, special) VALUES ('$matricule_grouillot_renvoi','A été renvoyé dans ses foyers','', '$matricule_grouillot_renvoi','<font color=$couleur_camp>$nom_perso</font>', '', NOW(), 1)";
											$mysqli->query($sql);
											
											$sql = "DELETE FROM perso_in_batiment WHERE id_perso='$matricule_grouillot_renvoi'";
											$mysqli->query($sql);
											
											$sql = "DELETE FROM banque_compagnie WHERE id_perso='$matricule_grouillot_renvoi'";
											$mysqli->query($sql);
											
											if ($thune_en_banque > 0) {
												$sql = "UPDATE banque_as_compagnie SET montant = montant - $thune_en_banque 
														WHERE id_compagnie= ( SELECT id_compagnie FROM perso_in_compagnie WHERE id_perso='$matricule_grouillot_renvoi')";
												$mysqli->query($sql);
												
												$sql = "SELECT montant FROM banque_as_compagnie WHERE id_compagnie='$id_compagnie'";
												$res = $mysqli->query($sql);
												$t = $res->fetch_assoc();
												
												$montant_final_banque = $t['montant'];
												
												$date = time();
												
												// banque log
												$sql = "INSERT INTO banque_log (date_log, id_compagnie, id_perso, montant_transfert, montant_final) VALUES (FROM_UNIXTIME($date), '$id_compagnie', '$matricule_grouillot_renvoi', '-$thune_en_banque', '$montant_final_banque')";
												$mysqli->query($sql);
											}
											
											$sql = "DELETE FROM perso_in_compagnie WHERE id_perso='$matricule_grouillot_renvoi'";
											$mysqli->query($sql);
											
											if (in_bat($mysqli, $matricule_grouillot_renvoi)) {		
												$sql = "DELETE FROM perso_in_batiment WHERE id_perso='$matricule_grouillot_renvoi'";
											}
											else if (in_train($mysqli, $matricule_grouillot_renvoi)) {
												$sql = "DELETE FROM perso_in_train WHERE id_perso='$matricule_grouillot_renvoi'";
											}
											else {
												$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE idPerso_carte='$matricule_grouillot_renvoi'";
											}
											$mysqli->query($sql);

											// On téléporte le perso hors carte
											$sql = "UPDATE perso SET x_perso='1000', y_perso='1000' WHERE id_perso='$matricule_grouillot_renvoi'";
											$mysqli->query($sql);
											
											$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ('$id','<font color=$couleur_camp_chef><b>$nom_chef</b></font>','a viré le grouillot matricule $matricule_grouillot_renvoi',NULL,'','',NOW(),'0')";
											$mysqli->query($sql);
											
											echo "<center><font color='blue'>Le grouillot avec la matricule $matricule_grouillot_renvoi a bien été renvoyé de votre bataillon.</font></center><br/>";
										}
										else {
											echo "<center><font color='red'>Impossible de renvoyer un grouillot qui possède des dettes dans une compagnie, merci de rembourser vos dettes avant de virer votre grouillot.</font></center><br/>";
										}
									}
									else {
										echo "<center><font color='red'>Impossible de renvoyer un grouillot qui est chef d'une compagnie, merci de passer son rôle de chef à un autre avant de le virer.</font></center><br/>";
									}
								} else {
									echo "<center><font color='red'>Le chef et le perso à renvoyer doivent être dans un fort ou fortin.</font></center><br/>";
								}
							} else {
								// Tentative de triche ?!
								echo "<center><font color='red'>Le perso n'a pas pu être renvoyé, si le problème persiste, veuillez contacter l'administrateur.</font></center><br/>";
							}
						} else {
							// Tentative de triche ?!
							echo "<center><font color='red'>Le matricule du perso à renvoyer est mal renseigné, si le problème persiste, veuillez contacter l'administrateur.</font></center><br/>";
						}
					}

					if (isset($_POST["reactiver"]) && isset($_POST["matricule_hidden"])) {
						$matricule = $_POST["matricule_hidden"];
						// controle matricule perso
						$verif_matricule = preg_match("#^[0-9]*[0-9]$#i","$matricule");
						if ($verif_matricule) {
							// On vérifie que le grouillot lui appartient bien
							$sql = "SELECT perso.id_perso FROM perso WHERE id_perso='$matricule' AND idJoueur_perso='$id_joueur'";
							$res = $mysqli->query($sql);
							$nb = $res->num_rows;
							if ($nb && verif_perso_est_dans_fort_ou_fortin($mysqli, $id)) {
								// Recup batiment
								$sql = "SELECT instance_batiment.id_instanceBat, x_instance, y_instance, pv_instance, pvMax_instance FROM perso_in_batiment JOIN instance_batiment ON perso_in_batiment.id_instanceBat = instance_batiment.id_instanceBat WHERE id_perso='$id'";
								$res = $mysqli->query($sql);
								$t = $res->fetch_assoc();
								$id_instance_bat =$t['id_instanceBat'];
								$x_instance =$t['x_instance'];
								$y_instance =$t['y_instance'];
								$pv_instance =$t['pv_instance'];
								$pv_max_instance =$t['pvMax_instance'];

								// Calcul pourcentage pv du batiment 
								$pourc_pv_instance = ($pv_instance / $pv_max_instance) * 100;

								// Verification si 10 persos ennemis à moins de 15 cases
								$sql = "SELECT count(id_perso) as nb_ennemi FROM perso, carte 
									WHERE perso.id_perso = carte.idPerso_carte 
									AND x_carte <= $x_instance + 15
									AND x_carte >= $x_instance - 15
									AND y_carte <= $y_instance + 15
									AND y_carte >= $y_instance - 15
									AND perso.clan != '$clan'";
								$res = $mysqli->query($sql);
								$t_e = $res->fetch_assoc();
								$nb_ennemis_siege = $t_e['nb_ennemi'];

								if ($pourc_pv_instance < 90 || $nb_ennemis_siege >= 10) {
									// Il reste moins de 90% des pv du batiment => siege
									echo "<center><font color='red'>Ce batiment est considéré en état de siege, il ne sera pas possible de recruter des grouillots tant que ses PV ne seront pas suffisamment remontés ou que la zone ne sera pas nettoyée des ennemis</font></center><br />";
									echo "<center>PV actuel : $pv_instance / $pv_max_instance</center>";
								} else {
									// Calculer PG déjà utilisés par le joueur
									$pg_utilise = calcul_pg($mysqli, $id_joueur);

									// Calcul PG restant au joueur
									$pg_restant = $pg - $pg_utilise;

									// Recup info perso
									$sql = "SELECT nom_perso, clan, cout_pg FROM perso JOIN type_unite ON id_unite=type_perso WHERE id_perso='$matricule'";
									$res = $mysqli->query($sql);
									$t = $res->fetch_assoc();
									$nom_perso =$t['nom_perso'];
									$clan =$t['clan'];
									$cout_pg =$t['cout_pg'];
									$couleur_camp = couleur_clan($clan);

									if ($cout_pg > $pg_restant) {
										echo "<center><font color='red'>Pas suffisament de PG restants pour réactiver cette unité.</font></center><br />";
									} else {
										// Ok - reactivation du perso						
										$sql = "UPDATE perso SET est_renvoye=0 WHERE id_perso='$matricule'";
										$mysqli->query($sql);

										// maj evenements
										$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) 
											VALUES ($matricule,'<font color=$couleur_camp><b>$nom_perso</b></font>','<b>a été réactivé</b>',NULL,'','',NOW(),'0')";
										$mysqli->query($sql);

										// Suppression renvoi du cv
										$sql = "DELETE FROM `cv` WHERE IDActeur_cv=$matricule AND IDCible_cv=$matricule AND nomActeur_cv LIKE '%renvoyé%'";
										$mysqli->query($sql);

										// Insertion perso dans batiment 
										$sql = "INSERT INTO perso_in_batiment VALUES ('$matricule','$id_instance_bat')";
										$mysqli->query($sql);

										// On téléporte le perso
										$sql = "UPDATE perso SET x_perso=$x_instance, y_perso=$y_instance WHERE id_perso='$matricule'";
										$mysqli->query($sql);
									}
								}
							}
						}
					}
				
					echo "<table class='table'>";
					echo "	<thead>";
					echo "		<tr>";
					echo "			<th style='text-align:center'>Type de grouillot</th><th style='text-align:center'>Matricule</th><th style='text-align:center'>Nom</th><th style='text-align:center'>Action</th>";
					echo "		</tr>";
					echo "	</thead>";
					echo "	<tbody>";
				
					// Affichage des grouillots
					echo "";
				
					// Récupération des persos du joueur
					$sql = "SELECT id_perso, nom_perso, type_perso, image_perso, est_renvoye FROM perso WHERE idJoueur_perso = '$id_joueur' AND chef = '0' ORDER BY id_perso";
					$res = $mysqli->query($sql);
					while ($tab = $res->fetch_assoc()) {
						
						$matricule_grouillot 	= $tab["id_perso"];
						$nom_grouillot			= $tab["nom_perso"];
						$image_grouillot		= $tab["image_perso"];
						$type_grouillot			= $tab["type_perso"];
						$est_renvoye			= $tab["est_renvoye"];
						
						$sql_u = "SELECT nom_unite FROM type_unite WHERE id_unite='$type_grouillot'";
						$res_u = $mysqli->query($sql_u);
						$t_u = $res_u->fetch_assoc();
						
						$nom_unite_grouillot = $t_u["nom_unite"];
						
						echo "<tr>";
						echo "	<td align='center'><img src='../images_perso/".$image_grouillot."' alt='".$nom_unite_grouillot."'/><br />" . $nom_unite_grouillot . "</td>";
						echo "	<td align='center'><a href='evenement.php?infoid=".$matricule_grouillot."'>" . $matricule_grouillot . "</a></td>";
						echo "<form method=\"post\" action=\"gestion_grouillot.php\">";
						echo "	<td align='center'>";
						echo "		<input type='text' maxlength='25' name='nom_grouillot' value='". $nom_grouillot ."'>";
						echo "		<input type='hidden' name='matricule_hidden' value='$matricule_grouillot'>";
						echo "		<input type='submit' name='renommer' value='renommer' class='btn btn-warning'>";
						echo "	</td>";
						echo "</form>";
						if (!$est_renvoye) {
							echo "<form method=\"post\" action=\"gestion_grouillot.php\">";					
							echo "	<td align='center'><button type=\"button\" class=\"btn btn-danger\" data-toggle=\"modal\" data-target=\"#modalConfirm$matricule_grouillot\">renvoyer</button></td>";
							echo "</form>";
						} else {
							echo "<form method=\"post\" action=\"gestion_grouillot.php\">";					
							echo "	<td align='center'><input type=\"submit\" class=\"btn btn-success\" name=\"reactiver\" value=\"réactiver\"/></td>";
							echo "	<input type='hidden' name='matricule_hidden' value='$matricule_grouillot'>";
							echo "</form>";
						}
						echo "</tr>";
						?>
						<!-- Modal -->
						<form method="post" action="gestion_grouillot.php">
							<div class="modal fade" id="modalConfirm<?php echo $matricule_grouillot; ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
								<div class="modal-dialog modal-dialog-centered" role="document">
									<div class="modal-content">
										<div class="modal-header">
											<h5 class="modal-title" id="exampleModalCenterTitle">Renvoyer le grouillot <?php echo $nom_unite_grouillot." ".$nom_grouillot." [".$matricule_grouillot."]"; ?> ?</h5>
											<button type="button" class="close" data-dismiss="modal" aria-label="Close">
												<span aria-hidden="true">&times;</span>
											</button>
										</div>
										<div class="modal-body">
											Êtes-vous sûr de vouloir renvoyer le grouillot <?php echo $nom_unite_grouillot." ".$nom_grouillot." [".$matricule_grouillot."]"; ?> ?
											<input type='hidden' name='matricule_renvoi_hidden' value='<?php echo $matricule_grouillot; ?>'>
										</div>
										<div class="modal-footer">
											<button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
											<button type="button" onclick="this.form.submit()" class="btn btn-primary">Renvoyer</button>
										</div>
									</div>
								</div>
							</div>
						</form>
						<?php
					}
					echo "	</tbody>";
					echo "</table>";
				}
				else {
					echo "<font color=red>Seul le chef de bataillon peut accéder à cette page.</font>";
				}
			}
		}
		else {
			// logout
			$_SESSION = array(); // On écrase le tableau de session
			session_destroy(); // On détruit la session
			
			header("Location:../index2.php");
		}
	}
	else{
		echo "<font color=red>Vous ne pouvez pas accéder à cette page, veuillez vous loguer.</font>";
	}
	?>
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
	// logout
	$_SESSION = array(); // On écrase le tableau de session
	session_destroy(); // On détruit la session
	
	header("Location:../index2.php");
}
?>
