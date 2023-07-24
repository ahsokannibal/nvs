<?php
@session_start();

require_once("../fonctions.php");
require_once("f_carte.php");
require_once("f_combat.php");
require_once("f_popover.php");

$mysqli = db_connexion();

include ('../nb_online.php');

date_default_timezone_set('Europe/Paris');

$id_perso = 0;

// Traitement selection perso
if (isset($_POST["liste_perso"]) && $_POST["liste_perso"] != "") {

	if(isset($_SESSION["ID_joueur"])){

		$id_joueur 	= $_SESSION["ID_joueur"];
		$id_perso	= $_POST["liste_perso"];

		// recuperation des infos du perso
		$sql = "SELECT idJoueur_perso FROM perso WHERE id_perso='$id_perso'";
		$res = $mysqli->query($sql);
		$t_perso = $res->fetch_assoc();

		$id_joueur_perso 	= $t_perso["idJoueur_perso"];

		// Le perso appartient-il bien au joueur ?
		if ($id_joueur_perso == $id_joueur) {
			$id_perso = $_SESSION['id_perso'] = $_POST["liste_perso"];
		}
		else {
			// Tentative de triche !
			$text_triche = "Le joueur $id_joueur a essayé de prendre controle du perso $id_perso qui ne lui appartient pas !";

			$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id_perso', '$text_triche')";
			$mysqli->query($sql);

			$_SESSION = array(); // On écrase le tableau de session
			session_destroy(); // On détruit la session

			//redirection
			header("location:index.php");
		}

	} else {
		header("Location:../index.php");
	}
}

if(isset($_SESSION["id_perso"])){
	$id_perso = $_SESSION['id_perso'];
}

// recupération config jeu
$dispo = config_dispo_jeu($mysqli);
$admin = admin_perso($mysqli, $id_perso);

if($dispo == '1' || $admin){

	if(isset($_SESSION["id_perso"])){

		$id_perso = $_SESSION['id_perso'];
		$date = time();

		$page_acces = 'jouer.php';
		if ($_SERVER['QUERY_STRING'] != '') {
			$page_acces .= '?'.$_SERVER['QUERY_STRING'];
		}

		// acces_log
		$sql = "INSERT INTO acces_log (date_acces, id_perso, page) VALUES (NOW(), '$id_perso', '$page_acces')";
		$mysqli->query($sql);

		// Alerte si 10 refresh ou plus en 10 sec (déco ?)
		$sql = "SELECT COUNT(*) as count_log_10sec FROM acces_log WHERE id_perso='$id_perso' AND page = 'jouer.php' AND date_acces > (NOW() - INTERVAL 10 SECOND)";
		$res = $mysqli->query($sql);
		$t = $res->fetch_assoc();

		$count_log_10sec = $t['count_log_10sec'];

		if ($count_log_10sec >= 10) {
			// Est-ce qu'il y a déjà eu une alerte de ce type pour ce perso dans les 30 dernières secondes ?
			$sql = "SELECT COUNT(*) as nb_alerte_10sec FROM alerte_anim WHERE type_alerte='2' AND id_perso='$id_perso' AND date_alerte > (NOW() - INTERVAL 30 SECOND)";
			$res = $mysqli->query($sql);
			$t = $res->fetch_assoc();

			$nb_alerte_10sec = $t['nb_alerte_10sec'];

			if ($nb_alerte_10sec == 0) {
				$sql = "INSERT INTO alerte_anim (type_alerte, id_perso, raison_alerte, date_alerte) VALUES ('2', '$id_perso', 'Page de jeu - plus de 10 refresh en moins de 10 secondes : $count_log_10sec', NOW())";
				$mysqli->query($sql);
			}
		}

		// Alerte si 30 refresh ou plus en moins d'une minute
		$sql = "SELECT COUNT(*) as count_log_1min FROM acces_log WHERE id_perso='$id_perso' AND page = 'jouer.php' AND date_acces > (NOW() - INTERVAL 60 SECOND)";
		$res = $mysqli->query($sql);
		$t = $res->fetch_assoc();

		$count_log_1min = $t['count_log_1min'];

		if ($count_log_1min >= 30) {

			// Est-ce qu'il y a déjà eu une alerte de ce type pour ce perso dans les 3 dernière minutes ?
			$sql = "SELECT COUNT(*) as nb_alerte_1min FROM alerte_anim WHERE type_alerte='3' AND id_perso='$id_perso' AND date_alerte > (NOW() - INTERVAL 180 SECOND)";
			$res = $mysqli->query($sql);
			$t = $res->fetch_assoc();

			$nb_alerte_1min = $t['nb_alerte_1min'];

			if ($nb_alerte_1min == 0) {
				$sql = "INSERT INTO alerte_anim (type_alerte, id_perso, raison_alerte, date_alerte) VALUES ('3', '$id_perso', 'Page de jeu - plus de 30 refresh en moins de 1 minute : $count_log_1min', NOW())";
				$mysqli->query($sql);
			}
		}

		// TODO - Vérification 10 derniers logs d'accès, sont-il sur le même delta de temps ?


		$sql_joueur = "SELECT idJoueur_perso FROM perso WHERE id_perso='$id_perso'";
		$res_joueur = $mysqli->query($sql_joueur);
		$t_joueur = $res_joueur->fetch_assoc();

		$id_joueur_perso = $t_joueur["idJoueur_perso"];

		$sql_dla = "SELECT UNIX_TIMESTAMP(DLA_perso) as DLA, est_gele FROM perso WHERE idJoueur_perso='$id_joueur_perso' AND chef=1";
		$res_dla = $mysqli->query($sql_dla);
		$t_dla = $res_dla->fetch_assoc();

		$dla 		= $t_dla["DLA"];
		$est_gele 	= $t_dla["est_gele"];

		$sql = "SELECT pv_perso FROM perso WHERE id_perso='$id_perso'";
		$res = $mysqli->query($sql);
		$tpv = $res->fetch_assoc();

		$testpv = $tpv['pv_perso'];

		$config = '1';

		// verification si le perso est encore en vie
		if ($testpv <= 0) {
			// le perso est mort
			header("Location:../tour.php");
		}
		else {
			// le perso est vivant
			// verification si nouveau tour ou gele
			if(nouveau_tour($date, $dla) || $est_gele) {
				if (isset($_GET['login']) && $_GET['login'] == 'ok') {
					header("Location:../tour.php?login=ok");
				}
				else {
					header("Location:../tour.php");
				}
			}
			else {
				$erreur = "";
				$mess = "";
				$mess_bat ="";

				if(isset($_SESSION["nv_tour"]) && $_SESSION["nv_tour"] == 1){
					echo "<center><font color=red><b>Nouveau tour</b></font></center>";
					$_SESSION["nv_tour"] = 0;
				}

				// recuperation des anciennes données du perso
				$sql = "SELECT idJoueur_perso, nom_perso, x_perso, y_perso, pm_perso, pmMax_perso, image_perso, pa_perso, perception_perso, recup_perso, bonusRecup_perso, bonusPM_perso, type_perso, paMax_perso, pv_perso, charge_perso, chargeMax_perso, DLA_perso, clan, perso_as_grade.id_grade, nom_grade
						FROM perso, perso_as_grade, grades
						WHERE perso_as_grade.id_perso = perso.id_perso
						AND perso_as_grade.id_grade = grades.id_grade
						AND perso.id_perso='$id_perso'";
				$res = $mysqli->query($sql);
				$t_perso1 = $res->fetch_assoc();

				$id_joueur_perso 	= $t_perso1["idJoueur_perso"];
				$nom_perso 			= $t_perso1["nom_perso"];
				$x_persoN 			= $t_perso1["x_perso"];
				$y_persoN 			= $t_perso1["y_perso"];
				$pm_perso 			= $t_perso1["pm_perso"];
				$pmMax_perso		= $t_perso1["pmMax_perso"];
				$dla_perso			= $t_perso1["DLA_perso"];
				$image_perso 		= $t_perso1["image_perso"];
				$bonusPM_perso_p 	= $t_perso1["bonusPM_perso"];
				$clan_p 			= $t_perso1["clan"];
				$type_perso			= $t_perso1["type_perso"];
				$pa_perso			= $t_perso1["pa_perso"];
				$perception_perso	= $t_perso1["perception_perso"];
				$charge_perso		= $t_perso1["charge_perso"];
				$chargeMax_perso	= $t_perso1["chargeMax_perso"];
				$grade_perso 		= $t_perso1["id_grade"];
				$nom_grade_perso	= $t_perso1["nom_grade"];

				$sql = "SELECT UNIX_TIMESTAMP(DLA_perso) as DLA_perso FROM perso WHERE idJoueur_perso='$id_joueur_perso' AND chef=1";
				$res = $mysqli->query($sql);
				$t_c = $res->fetch_assoc();

				$n_dla 				= $t_c["DLA_perso"];

				// récupération de la couleur du camp
				$couleur_clan_p = couleur_clan($clan_p);

				$dossier_img_joueur = get_dossier_image_joueur($mysqli, $id_joueur_perso);

				// affichage rosace et bousculades
				$sql = "SELECT afficher_rosace, bousculade_deplacement FROM joueur WHERE id_joueur='$id_joueur_perso'";
				$res = $mysqli->query($sql);
				$t = $res->fetch_assoc();

				$afficher_rosace 	= $t['afficher_rosace'];
				$bousculade_dep		= $t['bousculade_deplacement'];
				$cadrillage			= 1;//$t['cadrillage'];

				$sql = "SELECT MAX(x_carte) as x_max, MAX(y_carte) as y_max FROM carte";
				$res = $mysqli->query($sql);
				$t = $res->fetch_assoc();

				$X_MAX = $t['x_max'];
				$Y_MAX  = $t['y_max'];

				$carte = "carte";

				if(isset($_GET['erreur'])){
					if ($_GET['erreur'] == 'competence') {
						$erreur .= 'competence indiponible pour le moment';
					}

					if ($_GET['erreur'] == 'prox_bat') {
						$erreur .= 'Vous devez vous trouver à proximité du bâtiment pour effectuer cette action';
					}

					if ($_GET['erreur'] == 'pa') {
						$erreur .= "Vous n'avez pas assez de PA";
					}

					if ($_GET['erreur'] == 'pm') {
						$erreur .= "Vous n'avez plus de pm !";
					}
				}

				if (isset($_GET['message'])) {
					$message = $_GET['message'];
					if ($message == 'gainPM') {
						$mess .= "Vous êtes en forme aujourd'hui, vous gagnez 1PM !";
					}
				}

				// calcul malus pm
				$malus_pm_charge = getMalusCharge($charge_perso, $chargeMax_perso);
				if ($malus_pm_charge == 100) {
					$malus_pm = -$pmMax_perso;
				}
				else {
					$malus_pm = $malus_pm_charge;
				}

				// traitement entrée dans un batiment
				if(isset($_GET["bat"])) {

					$id_inst = $_GET["bat"];

					// on veut sortir du batiment
					if(isset($_GET["out"]) && $_GET["out"] == "ok") {

						// verification que le perso est bien dans le batiment duquel il souhaite sortir...
						if($id_inst == in_bat($mysqli, $id_perso)){

							// verification des pm du perso
							if($pm_perso + $malus_pm >= 1){

								// Si on choisi de sortir avec une direction
								if (isset($_GET["direction"])) {

									if (isDirectionOK($_GET["direction"])) {

										$direction = $_GET["direction"];

										$sql_b = "SELECT batiment.id_batiment, nom_batiment, taille_batiment, nom_instance FROM batiment, instance_batiment
												WHERE instance_batiment.id_batiment = batiment.id_batiment
												AND instance_batiment.id_instanceBat = '$id_inst'";
										$res_b = $mysqli->query($sql_b);
										$t_b = $res_b->fetch_assoc();

										$type_bat			= $t_b['id_batiment'];
										$nom_bat 			= $t_b['nom_batiment'];
										$taille_bat			= $t_b['taille_batiment'];
										$nom_instance_bat	= $t_b['nom_instance'];

										if ($type_bat != 10) {

											$taille_case = ceil($taille_bat / 2);

											$oc = 1;

											switch($direction){
												case 1:
													// Haut gauche
													$sql = "SELECT occupee_carte, x_carte, y_carte, fond_carte FROM $carte
															WHERE x_carte = $x_persoN - $taille_case AND y_carte = $y_persoN + $taille_case";

													break;
												case 2:
													// Haut
													$sql = "SELECT occupee_carte, x_carte, y_carte, fond_carte FROM $carte
															WHERE x_carte = $x_persoN AND y_carte = $y_persoN + $taille_case";

													break;
												case 3:
													// Haut droite
													$sql = "SELECT occupee_carte, x_carte, y_carte, fond_carte FROM $carte
															WHERE x_carte = $x_persoN + $taille_case AND y_carte = $y_persoN + $taille_case";

													break;
												case 4:
													// Gauche
													$sql = "SELECT occupee_carte, x_carte, y_carte, fond_carte FROM $carte
															WHERE x_carte = $x_persoN - $taille_case AND y_carte = $y_persoN";

													break;
												case 5:
													// Droite
													$sql = "SELECT occupee_carte, x_carte, y_carte, fond_carte FROM $carte
															WHERE x_carte = $x_persoN + $taille_case AND y_carte = $y_persoN";

													break;
												case 6:
													// Bas gauche
													$sql = "SELECT occupee_carte, x_carte, y_carte, fond_carte FROM $carte
															WHERE x_carte = $x_persoN - $taille_case AND y_carte = $y_persoN - $taille_case";

													break;
												case 7:
													// Bas
													$sql = "SELECT occupee_carte, x_carte, y_carte, fond_carte FROM $carte
															WHERE x_carte = $x_persoN AND y_carte = $y_persoN - $taille_case";

													break;
												case 8:
													// Bas droite
													$sql = "SELECT occupee_carte, x_carte, y_carte, fond_carte FROM $carte
															WHERE x_carte = $x_persoN + $taille_case AND y_carte = $y_persoN - $taille_case";

													break;
											}

											$res = $mysqli->query($sql);
											$t = $res->fetch_assoc();

											$oc 	= $t["occupee_carte"];

											if ($oc) {
												switch($direction){
													case 1:
														// Haut gauche
														for ($i = 1; $i < $taille_case; $i++) {
															$sql = "SELECT occupee_carte, x_carte, y_carte, fond_carte FROM $carte
																		WHERE x_carte = $x_persoN - $i AND y_carte = $y_persoN + $taille_case";
															$res = $mysqli->query($sql);
															$t = $res->fetch_assoc();

															$oc = $t["occupee_carte"];

															if (!$oc) {
																break;
															}
														}

														if (!$oc) {
															break;
														}

														for ($i = 1; $i < $taille_case; $i++) {
															$sql = "SELECT occupee_carte, x_carte, y_carte, fond_carte FROM $carte
																	WHERE x_carte = $x_persoN - $taille_case AND y_carte = $y_persoN + $i";
															$res = $mysqli->query($sql);
															$t = $res->fetch_assoc();

															$oc = $t["occupee_carte"];

															if (!$oc) {
																break;
															}
														}

														break;
													case 2:
														// Haut
														for ($i = 1; $i < $taille_case; $i++) {
															$sql = "SELECT occupee_carte, x_carte, y_carte, fond_carte FROM $carte
																	WHERE x_carte = $x_persoN - $i AND y_carte = $y_persoN + $taille_case";
															$res = $mysqli->query($sql);
															$t = $res->fetch_assoc();

															$oc = $t["occupee_carte"];

															if (!$oc) {
																break;
															}
														}

														if (!$oc) {
															break;
														}

														for ($i = 1; $i < $taille_case; $i++) {
															$sql = "SELECT occupee_carte, x_carte, y_carte, fond_carte FROM $carte
																	WHERE x_carte = $x_persoN + $i AND y_carte = $y_persoN + $taille_case";
															$res = $mysqli->query($sql);
															$t = $res->fetch_assoc();

															$oc = $t["occupee_carte"];

															if (!$oc) {
																break;
															}
														}

														break;
													case 3:
														// Haut droite
														for ($i = 1; $i < $taille_case; $i++) {
															$sql = "SELECT occupee_carte, x_carte, y_carte, fond_carte FROM $carte
																	WHERE x_carte = $x_persoN + $i AND y_carte = $y_persoN + $taille_case";
															$res = $mysqli->query($sql);
															$t = $res->fetch_assoc();

															$oc = $t["occupee_carte"];

															if (!$oc) {
																break;
															}
														}

														if (!$oc) {
															break;
														}

														for ($i = 1; $i < $taille_case; $i++) {
															$sql = "SELECT occupee_carte, x_carte, y_carte, fond_carte FROM $carte
																	WHERE x_carte = $x_persoN + $taille_case AND y_carte = $y_persoN + $i";
															$res = $mysqli->query($sql);
															$t = $res->fetch_assoc();

															$oc = $t["occupee_carte"];

															if (!$oc) {
																break;
															}
														}

														break;
													case 4:
														// Gauche
														for ($i = 1; $i < $taille_case; $i++) {
															$sql = "SELECT occupee_carte, x_carte, y_carte, fond_carte FROM $carte
																	WHERE x_carte = $x_persoN - $taille_case AND y_carte = $y_persoN + $i";
															$res = $mysqli->query($sql);
															$t = $res->fetch_assoc();

															$oc = $t["occupee_carte"];

															if (!$oc) {
																break;
															}
														}

														if (!$oc) {
															break;
														}

														for ($i = 1; $i < $taille_case; $i++) {
															$sql = "SELECT occupee_carte, x_carte, y_carte, fond_carte FROM $carte
																	WHERE x_carte = $x_persoN - $taille_case AND y_carte = $y_persoN - $i";
															$res = $mysqli->query($sql);
															$t = $res->fetch_assoc();

															$oc = $t["occupee_carte"];

															if (!$oc) {
																break;
															}
														}

														break;
													case 5:
														// Droite
														for ($i = 1; $i < $taille_case; $i++) {
															$sql = "SELECT occupee_carte, x_carte, y_carte, fond_carte FROM $carte
																	WHERE x_carte = $x_persoN + $taille_case AND y_carte = $y_persoN + $i";
															$res = $mysqli->query($sql);
															$t = $res->fetch_assoc();

															$oc = $t["occupee_carte"];

															if (!$oc) {
																break;
															}
														}

														if (!$oc) {
															break;
														}

														for ($i = 1; $i < $taille_case; $i++) {
															$sql = "SELECT occupee_carte, x_carte, y_carte, fond_carte FROM $carte
																	WHERE x_carte = $x_persoN + $taille_case AND y_carte = $y_persoN - $i";
															$res = $mysqli->query($sql);
															$t = $res->fetch_assoc();

															$oc = $t["occupee_carte"];

															if (!$oc) {
																break;
															}
														}

														break;
													case 6:
														// Bas gauche
														for ($i = 1; $i < $taille_case; $i++) {
															$sql = "SELECT occupee_carte, x_carte, y_carte, fond_carte FROM $carte
																	WHERE x_carte = $x_persoN - $i AND y_carte = $y_persoN - $taille_case";
															$res = $mysqli->query($sql);
															$t = $res->fetch_assoc();

															$oc = $t["occupee_carte"];

															if (!$oc) {
																break;
															}
														}

														if (!$oc) {
															break;
														}

														for ($i = 1; $i < $taille_case; $i++) {
															$sql = "SELECT occupee_carte, x_carte, y_carte, fond_carte FROM $carte
																	WHERE x_carte = $x_persoN - $taille_case AND y_carte = $y_persoN - $i";
															$res = $mysqli->query($sql);
															$t = $res->fetch_assoc();

															$oc = $t["occupee_carte"];

															if (!$oc) {
																break;
															}
														}

														break;
													case 7:
														// Bas
														for ($i = 1; $i < $taille_case; $i++) {
															$sql = "SELECT occupee_carte, x_carte, y_carte, fond_carte FROM $carte
																	WHERE x_carte = $x_persoN + $i AND y_carte = $y_persoN - $taille_case";
															$res = $mysqli->query($sql);
															$t = $res->fetch_assoc();

															$oc = $t["occupee_carte"];

															if (!$oc) {
																break;
															}
														}

														if (!$oc) {
															break;
														}

														for ($i = 1; $i < $taille_case; $i++) {
															$sql = "SELECT occupee_carte, x_carte, y_carte, fond_carte FROM $carte
																	WHERE x_carte = $x_persoN - $i AND y_carte = $y_persoN - $taille_case";
															$res = $mysqli->query($sql);
															$t = $res->fetch_assoc();

															$oc = $t["occupee_carte"];

															if (!$oc) {
																break;
															}
														}

														break;
													case 8:
														// Bas droite
														for ($i = 1; $i < $taille_case; $i++) {
															$sql = "SELECT occupee_carte, x_carte, y_carte, fond_carte FROM $carte
																	WHERE x_carte = $x_persoN + $i AND y_carte = $y_persoN - $taille_case";
															$res = $mysqli->query($sql);
															$t = $res->fetch_assoc();

															$oc = $t["occupee_carte"];

															if (!$oc) {
																break;
															}
														}

														if (!$oc) {
															break;
														}

														for ($i = 1; $i < $taille_case; $i++) {
															$sql = "SELECT occupee_carte, x_carte, y_carte, fond_carte FROM $carte
																	WHERE x_carte = $x_persoN + $taille_case AND y_carte = $y_persoN - $i";
															$res = $mysqli->query($sql);
															$t = $res->fetch_assoc();

															$oc = $t["occupee_carte"];

															if (!$oc) {
																break;
															}
														}

														break;
												}
											}

											if (!$oc) {

												$xs 	= $t["x_carte"];
												$ys 	= $t["y_carte"];
												$fond 	= $t["fond_carte"];

												$cout_pm = cout_pm($fond, $type_perso);

												// verification des pm du perso
												if($pm_perso + $malus_pm >= $cout_pm){

													// mise a jour des coordonnees du perso et de ses pm
													$sql = "UPDATE perso SET x_perso = '$xs', y_perso = '$ys', pm_perso=pm_perso-$cout_pm WHERE id_perso = '$id_perso'";
													$mysqli->query($sql);

													$x_persoN = $xs;
													$y_persoN = $ys;

													// mise a jour des coordonnees du perso sur la carte et changement d'etat de la case
													$sql = "UPDATE $carte SET occupee_carte='1', image_carte='$image_perso' ,idPerso_carte='$id_perso' WHERE x_carte = '$xs' AND y_carte = '$ys'";
													$mysqli->query($sql);

													// mise a jour de la table perso_in_batiment
													$sql = "DELETE FROM perso_in_batiment WHERE id_perso='$id_perso'";
													$mysqli->query($sql);

													// mise a jour des evenements
													$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ('$id_perso','<font color=$couleur_clan_p><b>$nom_perso</b></font>','est sorti du batiment',NULL,'','en $xs/$ys',NOW(),'0')";
													$mysqli->query($sql);

													// mise a jour du bonus de perception
													$bonus_visu = get_malus_visu($fond) + getBonusObjet($mysqli, $id_perso);

													if(bourre($mysqli, $id_perso)){
														if(!endurance_alcool($mysqli, $id_perso)) {
															$malus_bourre = bourre($mysqli, $id_perso) * 3;
															$bonus_visu -= $malus_bourre;
														}
													}

													$sql = "UPDATE perso SET bonusPerception_perso=$bonus_visu WHERE id_perso='$id_perso'";
													$mysqli->query($sql);

													// maj carte brouillard de guerre
													$perception_final = $perception_perso + $bonus_visu;
													//maj_visu($mysqli, $clan_p, $carte, $x_persoN, $y_persoN, $perception_final, $id_perso);
												}
												else {
													$erreur .= "Il faut posséder au moins ".$cout_pm." pm pour sortir de ce bâtiment dans cette direction";
												}
											}
											else {
												$erreur .= "Impossible de sortir dans cette direction, la sortie est bloquée";
											}
										}
										else {
											$erreur .= "Impossible de sortir d'un pénitencier";
										}
									}
									else {
										$erreur .= "Direction de sorti du bâtiment incorrecte";
									}
								}
								else {
									$erreur .= "Une direction est nécessaire pour sortir du bâtiment";
								}
							}
							else {
								$erreur .= "Il faut posséder au moins 1pm pour sortir du bâtiment";
							}
						}
						else {
							$erreur .= "Vous n'êtes pas dans ce batiment donc vous ne pouvez pas essayer d'en sortir";
						}
					}
					else {
						// on veut rentrer dans le batiment

						// traitement du cas tour de visu et de la tour de garde où il ne peut y avoir qu'un seul perso dedans !
						if(isset($_GET["bat2"]) && ($_GET["bat2"] == 2 || $_GET["bat2"] == 3) && isset($_GET["bat"]) && $_GET["bat"]!="") {

							// Vérification que le perso soit pas déjà dans un bâtiment
							if(!in_bat($mysqli, $id_perso) && !in_train($mysqli, $id_perso)){

								// verification que l'instance du batiment existe
								if (existe_instance_bat($mysqli, $_GET["bat"])){

									if(verif_bat_instance($mysqli, $_GET["bat2"],$_GET["bat"])){

										// verification qu'on soit bien à côté du batiment
										if(prox_instance_bat($mysqli, $x_persoN, $y_persoN, $_GET["bat"])){

											// verification si il y a un perso dans la tour
											$sql = "SELECT id_perso FROM perso_in_batiment WHERE id_instanceBat=".$_GET["bat"]."";
											$res = $mysqli->query($sql);
											$nbp = $res->fetch_row();

											if($nbp[0] != 0){
												// si la tour est occupee
												$erreur .= "Vous ne pouvez pas entrer, la tour est déjà occupée";
											}
											else { // la tour est vide

												// verification que le perso a encore des pm
												if($pm_perso + $malus_pm >= 1){

													if ($type_perso == '6' || $type_perso == '4' || $type_perso == '3') {

														$entre_bat_ok = 1;

														// recuperation des coordonnees et infos du batiment dans lequel le perso entre
														$sql = "SELECT nom_instance, nom_instance, pv_instance, pvMax_instance, x_instance, y_instance, id_batiment, camp_instance FROM instance_batiment WHERE id_instanceBat=".$_GET["bat"]."";
														$res = $mysqli->query($sql);
														$coordonnees_instance = $res->fetch_assoc();

														$x_bat 				= $coordonnees_instance["x_instance"];
														$y_bat 				= $coordonnees_instance["y_instance"];
														$nom_bat 			= $coordonnees_instance["nom_instance"];
														$nom_instance 		= $coordonnees_instance["nom_instance"];
														$id_bat				= $coordonnees_instance["id_batiment"];
														$camp_bat			= $coordonnees_instance["camp_instance"];
														$pv_batiment		= $coordonnees_instance["pv_instance"];
														$pvMax_batiment		= $coordonnees_instance["pvMax_instance"];
														$id_inst_bat 		= $_GET["bat"];

														// Verification si le perso est de la même nation ou non que le batiment
														if(!nation_perso_bat($mysqli, $id_perso, $id_inst_bat)) {

															$pourc_pv_instance = $pvMax_batiment == 0 ? 0 : ($pv_batiment / $pvMax_batiment) * 100;

															if ($pourc_pv_instance <= 80) {

																// Les chiens et soigneurs ne peuvent pas capturer de batiment
																if ($type_perso != '6' && $type_perso != '4') {

																	// Les hopitaux ne peuvent être capturés
																	if ($id_bat != '7') {

																		// Capture du batiment, il devient de la nation du perso
																		$sql = "UPDATE instance_batiment, perso SET camp_instance=clan WHERE id_instanceBat='$id_inst_bat' AND id_perso='$id_perso'";
																		$mysqli->query($sql);

																		$sql = "select clan from perso where id_perso='$id_perso'";
																		$res = $mysqli->query($sql);
																		$t_c = $res->fetch_assoc();

																		$camp = $t_c["clan"];

																		// MAJ camp canons
																		$sql = "UPDATE instance_batiment_canon SET camp_canon='$camp' WHERE id_instance_bat='$id_inst_bat'";
																		$mysqli->query($sql);

																		if($camp == "1"){
																			$couleur_c 		= "b";
																		}
																		else if($camp == "2"){
																			$couleur_c 		= "r";
																		}
																		else if ($camp == "3") {
																			$couleur_c 		= "g";
																		}

																		// Mise à jour de l'icone centrale sur la carte
																		$icone = "b".$id_bat."$couleur_c.png";
																		$sql = "UPDATE $carte SET image_carte='$icone' WHERE x_carte=$x_bat and y_carte=$y_bat";
																		$mysqli->query($sql);

																		// mise a jour table evenement
																		$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ('$id_perso','<font color=$couleur_clan_p><b>$nom_perso</b></font>','a capturé','$id_inst_bat','le batiment $nom_bat','en $x_bat/$y_bat : Felicitation!',NOW(),'0')";
																		$mysqli->query($sql);

																		if ($camp_bat == '1') {
																			$couleur_clan_bat = 'blue';
																		}
																		else if ($camp_bat == '2') {
																			$couleur_clan_bat = 'red';
																		}
																		else if ($camp_bat == '2') {
																			$couleur_clan_bat = 'green';
																		}
																		else {
																			$couleur_clan_bat = 'black';
																		}

																		// maj CV
																		$sql = "INSERT INTO `cv` (IDActeur_cv, nomActeur_cv, gradeActeur_cv, IDCible_cv, nomCible_cv, gradeCible_cv, date_cv, special) VALUES ($id_perso,'<font color=$couleur_clan_p>$nom_perso</font>', '$nom_grade_perso', '$id_inst_bat','<font color=$couleur_clan_bat>Tour de Guêt $nom_bat</font>', NULL, NOW(), 8)";
																		$mysqli->query($sql);

																		echo "<font color = red>Felicitation, vous venez de capturer un bâtiment ennemi !</font><br>";
																	}
																	else {
																		// Tentative de triche
																		$text_triche = "Tentative capture Hopital";

																		$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id_perso', '$text_triche')";
																		$mysqli->query($sql);

																		$erreur .= "Les hopitaux ne peuvent pas être capturés !";
																	}
																}
																else {
																	$entre_bat_ok = 0;

																	// Tentative de triche
																	$text_triche = "Tentative capture batiment avec type perso non autorisé";

																	$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id_perso', '$text_triche')";
																	$mysqli->query($sql);

																	$erreur .= "Les chiens et les soigneurs ne peuvent pas capturer de bâtiments !";
																}
															}
															else {
																$entre_bat_ok = 0;

																$erreur .= "Le bâtiment n'est pas encore capturable, il faut descendre ses PV";
															}
														}

														if ($entre_bat_ok) {

															// mise a jour de la carte
															$sql = "UPDATE $carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_persoN' AND y_carte='$y_persoN'";
															$res = $mysqli->query($sql);

															// mise a jour des coordonnées du perso
															$sql = "UPDATE perso SET x_perso='$x_bat', y_perso='$y_bat', pm_perso=pm_perso-1 WHERE id_perso='$id_perso'";
															$res = $mysqli->query($sql);

															// insertion du perso dans la table perso_in_batiment
															$sql = "INSERT INTO `perso_in_batiment` VALUES ('$id_perso','$id_inst_bat')";
															$mysqli->query($sql);

															echo"<font color = blue>vous êtes entré(e) dans le bâtiment $id_inst_bat</font><br>";

															// mise a jour table evenement
															$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ('$id_perso','<font color=$couleur_clan_p><b>$nom_perso</b></font>','est entré dans le batiment $nom_bat $id_inst_bat',NULL,'','en $x_bat/$y_bat',NOW(),'0')";
															$mysqli->query($sql);

															// calcul du bonus de perception
															if($_GET["bat2"] == 2){
																$bonus_perc = 5;
															}

															// mise a jour du bonus de perception du perso
															$bonus_visu = $bonus_perc + getBonusObjet($mysqli, $id_perso);

															if(bourre($mysqli, $id_perso)){
																if(!endurance_alcool($mysqli, $id_perso)) {
																	$malus_bourre = bourre($mysqli, $id_perso) * 3;
																	$bonus_visu -= $malus_bourre;
																}
															}
															// maj bonus perception et -1 pm pour rentrer dans le batiment
															$sql = "UPDATE perso SET bonusPerception_perso=$bonus_visu WHERE id_perso='$id_perso'";
															$mysqli->query($sql);

															// mise a jour des coordonnees du perso pour les tests d'après
															$x_persoN = $x_bat;
															$y_persoN = $y_bat;
														}
													}
													else {
														// Tentative de triche
														$text_triche = "Tentative entrer batiment tour de guet avec type perso non autorisé";

														$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id_perso', '$text_triche')";
														$mysqli->query($sql);

														$erreur .= "Seul les infanteries, soigneurs et chiens peuvent monter dans la tour de guet";
													}
												}
												else {
													$erreur .= "Il faut posséder au moins 1PM pour entrer dans le bâtiment";
												}
											}
										}
										else {
											$erreur .= "Il faut être à côté du bâtiment pour y entrer";
										}
									}
									else {
										$erreur .= "Pas bien d'essayer de tricher...";
									}
								}
								else {
									$erreur .= "Le bâtiment n'existe pas";
								}
							}
							else {
								$erreur .= "Vous devez sortir du bâtiment dans lequel vous vous trouvez afin de rentrer dans un nouveau bâtiment";
							}
						}
						// traitement des autres cas
						else {
							if(isset($_GET["bat"]) && $_GET["bat"]!="" && isset($_GET["bat2"]) && $_GET["bat2"]!="" && $_GET["bat2"] != 1 && $_GET["bat2"] != 5 && $_GET["bat2"] != 10) {

								// Vérification que le perso soit pas déjà dans un bâtiment
								if(!in_bat($mysqli, $id_perso) && !in_train($mysqli, $id_perso)){

									// verification que l'instance du batiment existe
									if (existe_instance_bat($mysqli, $_GET["bat"])){

										if(verif_bat_instance($mysqli, $_GET["bat2"], $_GET["bat"])){

											// verification qu'on soit bien à côté du batiment
											if(prox_instance_bat($mysqli, $x_persoN, $y_persoN, $_GET["bat"])){

												// verification que le perso a encore des pm
												if($pm_perso + $malus_pm >= 1){

													//recuperation du nombre de persos dans le batiment
													$sql = "select id_perso from perso_in_batiment where id_instanceBat=".$_GET["bat"]."";
													$res = $mysqli->query($sql);
													$nb_perso_bat = $res->num_rows;

													// recuperation des coordonnees et des infos du batiment dans lequel le perso entre
													$sql = "SELECT nom_batiment, id_instanceBat, pv_instance, pvMax_instance, nom_instance, x_instance, y_instance, contenance_instance, instance_batiment.id_batiment, taille_batiment, camp_instance
															FROM instance_batiment, batiment
															WHERE instance_batiment.id_batiment = batiment.id_batiment
															AND id_instanceBat=".$_GET["bat"]."";
													$res = $mysqli->query($sql);
													$coordonnees_instance = $res->fetch_assoc();

													$x_bat 					= $coordonnees_instance["x_instance"];
													$y_bat 					= $coordonnees_instance["y_instance"];
													$nom_bat 				= $coordonnees_instance["nom_instance"];
													$nom_batiment			= $coordonnees_instance["nom_batiment"];
													$id_inst_bat 			= $coordonnees_instance["id_instanceBat"];
													$contenance_inst_bat 	= $coordonnees_instance["contenance_instance"];
													$camp_instance_bat		= $coordonnees_instance["camp_instance"];
													$id_bat					= $coordonnees_instance["id_batiment"];
													$taille_batiment		= $coordonnees_instance["taille_batiment"];
													$pv_batiment			= $coordonnees_instance["pv_instance"];
													$pvMax_batiment			= $coordonnees_instance["pvMax_instance"];

													// verification contenance batiment
													if($nb_perso_bat < $contenance_inst_bat){

														$entre_bat_ok = 1;

														// verification si le perso est de la même nation que le batiment
														if(!nation_perso_bat($mysqli, $id_perso, $id_inst_bat)) {

															$pourc_pv_instance = $pvMax_batiment == 0 ? 0 : ($pv_batiment / $pvMax_batiment) * 100;

															if ($pourc_pv_instance <= 80) {

																// les chiens et soigneurs ne peuvent pas capturer de batiment
																if ($type_perso != '6' && $type_perso != '4') {

																	// Les hopitaux et les gares ne peuvent être capturés
																	if ($id_bat != '7' && $id_bat != '11') {

																		// verification que le batiment est vide
																		if(batiment_vide($mysqli, $id_inst_bat)) {

																			// capture du batiment, il devient de la nation du perso
																			$sql = "UPDATE instance_batiment, perso SET camp_instance=clan WHERE id_instanceBat='$id_inst_bat' AND id_perso='$id_perso'";
																			$mysqli->query($sql);

																			$sql = "select clan from perso where id_perso='$id_perso'";
																			$res = $mysqli->query($sql);
																			$t_c = $res->fetch_assoc();

																			$camp = $t_c["clan"];

																			// MAJ camp canons
																			$sql = "UPDATE instance_batiment_canon SET camp_canon='$camp' WHERE id_instance_bat='$id_inst_bat'";
																			$mysqli->query($sql);

																			if($camp == "1"){
																				$couleur_c 		= "b";
																				$image_canon_g 	= 'canonG_nord.gif';
																				$image_canon_d 	= 'canonD_nord.gif';
																			}
																			else if($camp == "2"){
																				$couleur_c 		= "r";
																				$image_canon_g 	= 'canonG_sud.gif';
																				$image_canon_d 	= 'canonD_sud.gif';
																			}

																			$icone = "b".$id_bat."$couleur_c.png";

																			if ($taille_batiment > 1) {

																				$taille_search 	= floor($taille_batiment / 2);
																				$image_case_c	= $couleur_c.".png";

																				for ($x = $x_bat - $taille_search; $x <= $x_bat + $taille_search; $x++) {
																					for ($y = $y_bat - $taille_search; $y <= $y_bat + $taille_search; $y++) {
																						if ($x == $x_bat && $y == $y_bat) {
																							// Mise à jour de l'icone centrale
																							$sql = "UPDATE $carte SET image_carte='$icone' WHERE x_carte=$x_bat and y_carte=$y_bat";
																							$mysqli->query($sql);
																						}
																						else {
																							$sql = "UPDATE $carte SET image_carte='$image_case_c' WHERE x_carte='$x' AND y_carte='$y' AND image_carte NOT LIKE 'canon%'";
																							$mysqli->query($sql);
																						}
																					}
																				}

																				// Mise à jour des icones de canon sur la carte
																				if ($id_bat == 8) {
																					// Fortin
																					// Canons Gauche
																					$sql = "UPDATE $carte SET image_carte='$image_canon_g'
																							WHERE (x_carte=$x_bat - 1 AND y_carte=$y_bat - 1)
																							OR (x_carte=$x_bat - 1 AND y_carte=$y_bat + 1)";
																					$mysqli->query($sql);

																					// Canons Droit
																					$sql = "UPDATE $carte SET image_carte='$image_canon_d'
																							WHERE (x_carte=$x_bat + 1 AND y_carte=$y_bat - 1)
																							OR (x_carte=$x_bat + 1 AND y_carte=$y_bat + 1)";
																					$mysqli->query($sql);
																				}
																				else if ($id_bat == 9) {
																					// Fort
																					// Canons Gauche
																					$sql = "UPDATE $carte SET image_carte='$image_canon_g'
																							WHERE (x_carte=$x_bat - 2 AND y_carte=$y_bat + 2)
																							OR (x_carte=$x_bat - 2 AND y_carte=$y_bat)
																							OR (x_carte=$x_bat - 2 AND y_carte=$y_bat - 2)";
																					$mysqli->query($sql);

																					// Canons Droit
																					$sql = "UPDATE $carte SET image_carte='$image_canon_d'
																							WHERE (x_carte=$x_bat + 2 AND y_carte=$y_bat + 2)
																							OR (x_carte=$x_bat + 2 AND y_carte=$y_bat)
																							OR (x_carte=$x_bat + 2 AND y_carte=$y_bat - 2)";
																					$mysqli->query($sql);
																				}

																				// Mise à jour des respawn
																				$sql = "DELETE FROM perso_as_respawn WHERE id_instance_bat='$id_inst_bat'";
																				$mysqli->query($sql);
																			}
																			else {
																				// Mise à jour de l'icone centrale
																				$sql = "UPDATE $carte SET image_carte='$icone' WHERE x_carte=$x_bat and y_carte=$y_bat";
																				$mysqli->query($sql);
																			}

																			// mise a jour table evenement
																			$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ('$id_perso','<font color=$couleur_clan_p><b>$nom_perso</b></font>','a capturé le batiment','$id_inst_bat','$nom_bat','en $x_bat/$y_bat : Felicitation!',NOW(),'0')";
																			$mysqli->query($sql);

																			// Gain points de victoire
																			if ($id_bat == 9) {
																				// FORT -> 500
																				$gain_pvict = 500;
																				$nom_b = "FORT";
																			}
																			else if ($id_bat == 8) {
																				// FORTIN -> 100
																				$gain_pvict = 100;
																				$nom_b = "FORTIN";
																			}
																			else if ($id_bat == 11) {
																				// GARE -> 50
																				$gain_pvict = 50;
																				$nom_b = "GARE";
																			}
																			else if ($id_bat == 7) {
																				// HOPITAL -> 0
																				$gain_pvict = 0;
																				$nom_b = "HOPITAL";
																			}
																			else {
																				$gain_pvict = 0;
																			}

																			if ($gain_pvict > 0) {

																				// C'est une capture, gains X 1.5
																				$gain_pvict = floor($gain_pvict * 1.5);

																				// MAJ stats points victoire
																				$sql = "UPDATE stats_camp_pv SET points_victoire = points_victoire + ".$gain_pvict." WHERE id_camp='$clan_p'";
																				$mysqli->query($sql);

																				// Ajout de l'historique
																				$date = time();
																				$texte = addslashes("Pour la capture du bâtiment ".$nom_batiment." ".$nom_bat." [".$id_inst_bat."] par ".$nom_perso." [".$id_perso."]");
																				$sql = "INSERT INTO histo_stats_camp_pv (date_pvict, id_camp, gain_pvict, texte) VALUES (FROM_UNIXTIME($date), '$clan_p', '$gain_pvict', '$texte')";
																				$mysqli->query($sql);

																			}

																			if ($camp_instance_bat == '1') {
																				$couleur_clan_bat = 'blue';
																			}
																			else if ($camp_instance_bat == '2') {
																				$couleur_clan_bat = 'red';
																			}
																			else if ($camp_instance_bat == '2') {
																				$couleur_clan_bat = 'green';
																			}
																			else {
																				$couleur_clan_bat = 'black';
																			}

																			// maj CV
																			$sql = "INSERT INTO `cv` (IDActeur_cv, nomActeur_cv, gradeActeur_cv, IDCible_cv, nomCible_cv, gradeCible_cv, date_cv, special) VALUES ($id_perso,'<font color=$couleur_clan_p>$nom_perso</font>', '$nom_grade_perso', '$id_inst_bat','<font color=$couleur_clan_bat>$nom_b $nom_bat</font>', NULL, NOW(), 8)";
																			$mysqli->query($sql);

																			echo "<font color = red>Félicitation, vous venez de capturer un bâtiment ennemi !</font><br>";
																		}
																		else {
																			$entre_bat_ok = 0;

																			$erreur .= "Le bâtiment n'est pas vide et ne peut donc pas être capturé";
																		}
																	}
																	else {
																		$entre_bat_ok = 0;

																		// Tentative de triche
																		$text_triche = "Tentative capture Hopital ou Gare";

																		$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id_perso', '$text_triche')";
																		$mysqli->query($sql);

																		$erreur .= "Les hopitaux et les gares ne peuvent pas être capturés !";
																	}
																}
																else {
																	$entre_bat_ok = 0;

																	// Tentative de triche
																	$text_triche = "Tentative capture batiment avec type perso non autorisé";

																	$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id_perso', '$text_triche')";
																	$mysqli->query($sql);

																	$erreur .= "Les chiens et les soigneurs ne peuvent pas capturer de bâtiment";
																}
															}
															else {
																$entre_bat_ok = 0;

																$erreur .= "Le bâtiment n'est pas encore capturable, il faut descendre ses PV";
															}
														}

														if ($entre_bat_ok) {

															// mise a jour des coordonnées du perso sur la carte
															$sql = "UPDATE $carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_persoN' AND y_carte='$y_persoN'";
															$res = $mysqli->query($sql);

															// mise a jour des coordonnées du perso
															$sql = "UPDATE perso SET x_perso='$x_bat', y_perso='$y_bat' WHERE id_perso='$id_perso'";
															$res = $mysqli->query($sql);

															// insertion du perso dans la table perso_in_batiment
															$sql = "INSERT INTO `perso_in_batiment` VALUES ('$id_perso','$id_inst_bat')";
															$mysqli->query($sql);

															echo"<font color = blue>vous êtes entré(e) dans le bâtiment $nom_bat</font>";

															// mise a jour table evenement
															$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ('$id_perso','<font color=$couleur_clan_p><b>$nom_perso</b></font>','est entré dans le batiment $nom_bat $id_inst_bat',NULL,'','en $x_bat/$y_bat',NOW(),'0')";
															$mysqli->query($sql);

															// Partie Passage de grade chef
															if ($type_perso == 1 && ($id_bat == 8 || $id_bat == 9)) {

																// recup grade / pc chef
																$sql = "SELECT pc_perso, perso_as_grade.id_grade FROM perso, perso_as_grade WHERE perso.id_perso = perso_as_grade.id_perso AND perso.id_perso='$id_perso'";
																$res = $mysqli->query($sql);
																$t_chef = $res->fetch_assoc();

																$pc_perso_chef = $t_chef["pc_perso"];
																$id_grade_chef = $t_chef["id_grade"];

																// Verification passage de grade
																$sql = "SELECT id_grade, nom_grade FROM grades WHERE pc_grade <= $pc_perso_chef AND pc_grade != 0 ORDER BY id_grade DESC LIMIT 1";
																$res = $mysqli->query($sql);
																$t_grade = $res->fetch_assoc();

																$id_grade_final 	= $t_grade["id_grade"];
																$nom_grade_final	= $t_grade["nom_grade"];

																if ($id_grade_chef < $id_grade_final) {

																	// Passage de grade
																	$sql = "UPDATE perso_as_grade SET id_grade='$id_grade_final' WHERE id_perso='$id_perso'";
																	$mysqli->query($sql);

																	// mise a jour des evenements
																	$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ('$id_perso','<font color=$couleur_clan_p><b>$nom_perso</b></font>','a été promu <b>$nom_grade_final</b> !',NULL,'','',NOW(),'0')";
																	$mysqli->query($sql);

																	// maj CV
																	$sql = "INSERT INTO `cv` (IDActeur_cv, nomActeur_cv, gradeActeur_cv, IDCible_cv, nomCible_cv, gradeCible_cv, date_cv, special) VALUES ($id_perso,'<font color=$couleur_clan_p>$nom_perso</font>', '$nom_grade_final', NULL, NULL, NULL, NOW(), 9)";
																	$mysqli->query($sql);
																}
															}

															$bonus_perc = 0;

															// mise a jour du bonus de perception du perso
															$bonus_visu = $bonus_perc + getBonusObjet($mysqli, $id_perso);

															if(bourre($mysqli, $id_perso)){
																if(!endurance_alcool($mysqli, $id_perso)) {
																	$malus_bourre = bourre($mysqli, $id_perso) * 3;
																	$bonus_visu -= $malus_bourre;
																}
															}

															// maj bonus perception et -1 pm pour l'entrée dans le batiment
															$sql = "UPDATE perso SET bonusPerception_perso=$bonus_visu, pm_perso=pm_perso-1 WHERE id_perso='$id_perso'";
															$mysqli->query($sql);

															// mise a jour des coordonnees du perso pour le test d'après
															$x_persoN = $x_bat;
															$y_persoN = $y_bat;
														}
													}
													else {
														$erreur .= "Le bâtiment est déjà rempli au maximum de sa capacité";
													}
												}
												else {
													$erreur .= "Il faut posséder au moins 1PM pour entrer dans le bâtiment";
												}
											}
											else {
												// Tentative de triche
												$text_triche = "Tentative pour entrer dans un bâtiment sans être à côté de celui-ci";

												$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id_perso', '$text_triche')";
												$mysqli->query($sql);

												$erreur .= "Il faut être à côté du bâtiment pour y entrer";
											}
										}
										else {
											$erreur .= "Pas bien d'essayer de tricher...";
										}
									}
									else {
										// Tentative de triche
										$text_triche = "Tentative entrer dans un natiment qui n existe pas...";

										$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id_perso', '$text_triche')";
										$mysqli->query($sql);

										$erreur .= "Le batiment n'existe pas";
									}
								}
								else {
									$erreur .= "Vous devez sortir du bâtiment dans lequel vous vous trouvez afin de rentrer dans un nouveau bâtiment";
								}
							}
						}
					}
				}

				// Traitement sortie
				if (isset($_GET['sortie'])) {

					// verification que le perso a encore des pm
					if($pm_perso + $malus_pm >= 1){

						$instance_bat = in_bat($mysqli, $id_perso);

						if($instance_bat){

							$coord_sortie = $_GET['sortie'];

							$t_coord = explode(',',$coord_sortie);

							if (count($t_coord) == 2) {

								$x_sortie = $t_coord[0];
								$y_sortie = $t_coord[1];

								$verif_x = preg_match("#^[0-9]*[0-9]$#i","$x_sortie");
								$verif_y = preg_match("#^[0-9]*[0-9]$#i","$y_sortie");

								if ($verif_x && $verif_y) {

									if (in_map($x_sortie, $y_sortie, $X_MAX, $Y_MAX)) {

										// Récupération x, y et taille batiment
										$sql = "SELECT x_instance, y_instance, taille_batiment, batiment.id_batiment FROM instance_batiment, batiment
												WHERE instance_batiment.id_batiment = batiment.id_batiment
												AND id_instanceBat = '$instance_bat'";
										$res = $mysqli->query($sql);
										$t = $res->fetch_assoc();

										$id_bat		= $t['id_batiment'];
										$x_instance = $t['x_instance'];
										$y_instance = $t['y_instance'];
										$taille_bat = $t['taille_batiment'];

										// Cas particulier pénitencier
										if ($id_bat != 10) {

											$nb_case_bat = ceil($taille_bat / 2);

											if (($x_sortie == $x_instance + $nb_case_bat && $y_sortie >= $y_instance - $nb_case_bat && $y_sortie <= $y_instance + $nb_case_bat)
												|| ($x_sortie == $x_instance - $nb_case_bat && $y_sortie >= $y_instance - $nb_case_bat && $y_sortie <= $y_instance + $nb_case_bat)
												|| ($y_sortie == $y_instance + $nb_case_bat && $x_sortie >= $x_instance - $nb_case_bat && $x_sortie <= $x_instance + $nb_case_bat)
												|| ($y_sortie == $y_instance - $nb_case_bat && $x_sortie >= $x_instance - $nb_case_bat && $x_sortie <= $x_instance + $nb_case_bat)) {


												// recuperation des fonds
												$sql = "SELECT fond_carte, occupee_carte FROM $carte WHERE x_carte='$x_sortie' AND y_carte='$y_sortie'";
												$res_map = $mysqli->query ($sql);
												$t_carte1 = $res_map->fetch_assoc();

												$fond = $t_carte1["fond_carte"];
												$oc_c = $t_carte1["occupee_carte"];

												// On vérifie que la case n'est pas déjà occupée
												if (!$oc_c) {

													$cout_pm = cout_pm($fond, $type_perso);

													if ($pm_perso + $malus_pm >= $cout_pm) {

														// mise a jour des coordonnees du perso et de ses pm
														$sql = "UPDATE perso SET x_perso = '$x_sortie', y_perso = '$y_sortie', pm_perso=pm_perso-$cout_pm WHERE id_perso = '$id_perso'";
														$mysqli->query($sql);

														$x_persoN = $x_sortie;
														$y_persoN = $y_sortie;

														// mise a jour des coordonnees du perso sur la carte et changement d'etat de la case
														$sql = "UPDATE $carte SET occupee_carte='1', image_carte='$image_perso' ,idPerso_carte='$id_perso' WHERE x_carte = '$x_sortie' AND y_carte = '$y_sortie'";
														$mysqli->query($sql);

														// mise a jour de la table perso_in_batiment
														$sql = "DELETE FROM perso_in_batiment WHERE id_perso='$id_perso'";
														$mysqli->query($sql);

														// mise a jour des evenements
														$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ('$id_perso','<font color=$couleur_clan_p><b>$nom_perso</b></font>','est sorti du batiment',NULL,'','en $x_sortie/$y_sortie',NOW(),'0')";
														$mysqli->query($sql);

														// mise a jour du bonus de perception
														$bonus_visu = get_malus_visu($fond) + getBonusObjet($mysqli, $id_perso);

														if(bourre($mysqli, $id_perso)){
															if(!endurance_alcool($mysqli, $id_perso)) {
																$malus_bourre = bourre($mysqli, $id_perso) * 3;
																$bonus_visu -= $malus_bourre;
															}
														}

														$sql = "UPDATE perso SET bonusPerception_perso=$bonus_visu WHERE id_perso='$id_perso'";
														$mysqli->query($sql);

														// maj carte brouillard de guerre
														$perception_final = $perception_perso + $bonus_visu;
														//maj_visu($mysqli, $clan_p, $carte, $x_persoN, $y_persoN, $perception_final, $id_perso);

													}
													else {
														$erreur .= "Vous n'avez pas assez de PM pour sortir du bâtiment sur cette case !";
													}
												}
												else {
													$erreur .= "La case de sortie est déjà occupée !";
												}
											}
											else {
												// Tentative de triche
												$text_triche = "Les coordonnées de sortie en paramètre ne correspondent pas à la sortie du batiment";

												$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id_perso', '$text_triche')";
												$mysqli->query($sql);

												$erreur .= "Paramètre incorrect !";
											}
										}
										else {
											// Tentative de triche
											$text_triche = "Tentative de sortie de pénitencier";

											$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id_perso', '$text_triche')";
											$mysqli->query($sql);

											$erreur .= "Votre tentative d'évasion s'est soldée par un echec, les gardes vous ont rattrapés et remis au cachot !";
										}
									}
									else {
										$erreur .= "Les coordonnées sont en dehors de la carte !";
									}
								}
								else {
									// Tentative de triche
									$text_triche = "Tentative modification parametre sortie, paramètre x ou y incorrect";

									$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id_perso', '$text_triche')";
									$mysqli->query($sql);

									$erreur .= "Paramètre incorrect !";
								}
							}
							else {
								// Tentative de triche
								$text_triche = "Tentative modification parametre sortie, nombre paramètres incorrect";

								$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id_perso', '$text_triche')";
								$mysqli->query($sql);

								$erreur .= "Paramètre incorrect !";
							}
						}
						else {
							// Tentative de triche
							$text_triche = "Tentative utilisation sortie alors que non dans batiment";

							$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id_perso', '$text_triche')";
							$mysqli->query($sql);

							$erreur .= "Vous ne pouvez pas utiliser cette fonction si vous n'êtes pas dans un bâtiment !";
						}
					}
					else {
						$erreur .= "Vous n'avez pas assez de PM pour sortir du bâtiment !";
					}
				}

				// On se trouve dans un batiment
				if(in_bat($mysqli, $id_perso)){

					// Récupération des infos sur l'instance du batiment dans lequel le perso se trouve
					$sql = "SELECT id_instanceBat, id_batiment, nom_instance, pv_instance, pvMax_instance FROM instance_batiment WHERE x_instance='$x_persoN' AND y_instance='$y_persoN'";
					$res = $mysqli->query($sql);
					$t = $res->fetch_assoc();

					$id_bat 	= $t["id_instanceBat"];
					$bat 		= $t["id_batiment"];
					$nom_ibat 	= $t["nom_instance"];
					$pv_bat		= $t['pv_instance'];
					$pvMax_bat	= $t['pvMax_instance'];

					//recuperation du nom du batiment
					$sql_n = "SELECT nom_batiment FROM batiment WHERE id_batiment = '$bat'";
					$res_n = $mysqli->query($sql_n);
					$t_n = $res_n->fetch_assoc();

					$nom_bat = $t_n["nom_batiment"];

					// Les chiens ne peuvent pas réparer les bâtiments
					if ($pv_bat < $pvMax_bat && $type_perso != '6') {
						$mess_bat .= "<center><font color = blue>~~<a href=\"action.php?bat=$id_bat&reparer=ok\" > reparer $nom_bat $nom_ibat [$id_bat] (5 PA)</a>~~</font></center>";
					}

					$mess_bat .= "<center><font color = blue>~~<a href=\"batiment.php?bat=$id_bat\" target='_blank'> accéder à la page du bâtiment $nom_bat $nom_ibat</a>~~</font></center>";

					$bonus_perc = 0;

					// calcul du bonus/malus de perception
					if($bat == 2){
						// Tour de guet
						$bonus_perc += 5;
					}
					else if ($bat == 8 || $bat == 9 || $bat == 11) {
						// Fort / Fortin / Gare
						$bonus_perc += -1;
					}
					else if ($bat == 7 || $bat == 10) {
						// Hopital / Pénitencier
						$bonus_perc += -2;
					}

					// mise a jour du bonus de perception du perso
					$bonus_visu = $bonus_perc + getBonusObjet($mysqli, $id_perso);

				} else if (in_train($mysqli, $id_perso)) {
					$bonus_perc = -1;
					$bonus_visu = $bonus_perc + getBonusObjet($mysqli, $id_perso);
				} else {

					$sql = "SELECT fond_carte FROM $carte WHERE x_carte=$x_persoN AND y_carte=$y_persoN";
					$res_map = $mysqli->query($sql);
					$t_carte1 = $res_map->fetch_assoc();

					$fond 			= $t_carte1["fond_carte"];

					$malus_fond = get_malus_visu($fond);

					// Les chiens ne perdent pas de perception en foret
					if ($malus_fond < 0 && $type_perso == 6) {
						$malus_fond = 0;
					}

					$bonus_visu = $malus_fond + getBonusObjet($mysqli, $id_perso);
				}

				if(bourre($mysqli, $id_perso)){
					if(!endurance_alcool($mysqli, $id_perso)) {
						$malus_bourre = bourre($mysqli, $id_perso) * 3;
						$bonus_visu -= $malus_bourre;
					}
				}

				$sql = "UPDATE perso SET bonusPerception_perso=$bonus_visu WHERE id_perso='$id_perso'";
				$mysqli->query($sql);

				// On se trouve dans un train
				if (in_train($mysqli, $id_perso)) {
					$mess_bat .= "<center><font color = blue><b>Vous êtes dans un train</b></font></center>";

					if (isset($_GET['train']) && isset($_GET['direction'])) {

						// on veut sortir du batiment
						if(isset($_GET["out"]) && $_GET["out"] == "ok") {

							$id_instance_train 	= $_GET['train'];
							$direction_saut		= $_GET['direction'];

							if (isDirectionOK($direction_saut)) {

								switch($direction_saut){
									case 1:
										// Haut gauche
										$sql = "SELECT occupee_carte, x_carte, y_carte, fond_carte FROM $carte
												WHERE x_carte = $x_persoN - 2 AND y_carte >= $y_persoN + 2";
										break;
									case 2:
										// Haut
										$sql = "SELECT occupee_carte, x_carte, y_carte, fond_carte FROM $carte
												WHERE x_carte = $x_persoN AND y_carte = $y_persoN + 2";
										break;
									case 3:
										// Haut droite
										$sql = "SELECT occupee_carte, x_carte, y_carte, fond_carte FROM $carte
												WHERE x_carte = $x_persoN + 2 AND y_carte = $y_persoN + 2";
										break;
									case 4:
										// Gauche
										$sql = "SELECT occupee_carte, x_carte, y_carte, fond_carte FROM $carte
												WHERE x_carte = $x_persoN - 2 AND y_carte = $y_persoN";
										break;
									case 5:
										// Droite
										$sql = "SELECT occupee_carte, x_carte, y_carte, fond_carte FROM $carte
												WHERE x_carte = $x_persoN + 2 AND y_carte = $y_persoN";
										break;
									case 6:
										// Bas gauche
										$sql = "SELECT occupee_carte, x_carte, y_carte, fond_carte FROM $carte
												WHERE x_carte = $x_persoN - 2 AND y_carte = $y_persoN - 2";
										break;
									case 7:
										// Bas
										$sql = "SELECT occupee_carte, x_carte, y_carte, fond_carte FROM $carte
												WHERE x_carte = $x_persoN AND y_carte = $y_persoN - 2";
										break;
									case 8:
										// Bas droite
										$sql = "SELECT occupee_carte, x_carte, y_carte, fond_carte FROM $carte
												WHERE x_carte = $x_persoN + 2 AND y_carte = $y_persoN - 2";
										break;
								}

								$res = $mysqli->query($sql);
								$t = $res->fetch_assoc();

								$oc 	= $t["occupee_carte"];
								$xs 	= $t["x_carte"];
								$ys 	= $t["y_carte"];
								$fond_c = $t["fond_carte"];

								if (!$oc && in_map($xs, $ys, $X_MAX, $Y_MAX) && !is_eau_p($fond_c)) {
									// On peut sauter

									// mise a jour du bonus de perception
									$bonus_visu = get_malus_visu($fond_c) + getBonusObjet($mysqli, $id_perso);

									if(bourre($mysqli, $id_perso)){
										if(!endurance_alcool($mysqli, $id_perso)) {
											$malus_bourre = bourre($mysqli, $id_perso) * 3;
											$bonus_visu -= $malus_bourre;
										}
									}

									// On supprime le perso du train
									$sql = "DELETE FROM perso_in_train WHERE id_train='$id_instance_train' AND id_perso='$id_perso'";
									$mysqli->query($sql);

									// MAJ perso
									$sql = "UPDATE perso SET x_perso='$xs', y_perso='$ys', bonusPerception_perso=$bonus_visu, pv_perso=pv_perso/2 WHERE id_perso='$id_perso'";
									$mysqli->query($sql);

									// mise a jour des coordonnees du perso sur la carte et changement d'etat de la case
									$sql = "UPDATE $carte SET occupee_carte='1', image_carte='$image_perso' ,idPerso_carte='$id_perso' WHERE x_carte = '$xs' AND y_carte = '$ys'";
									$mysqli->query($sql);

									// mise a jour des evenements
									$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ('$id_perso','<font color=$couleur_clan_p><b>$nom_perso</b></font>','est sauté du train ',NULL,'','[<a href=\"evenement.php?infoid=$id_instance_train\">$id_instance_train</a>] en $xs/$ys | PV/2',NOW(),'0')";
									$mysqli->query($sql);

									// maj carte brouillard de guerre
									$perception_final = $perception_perso + $bonus_visu;
									//maj_visu($mysqli, $clan_p, $carte, $xs, $ys, $perception_final, $id_perso);

								}
								else {
									// On ne peux pas sauter
									$erreur .= "Impossible de sauter du train dans cette direction";
								}
							}
							else {
								// TRICHE
							}
						}
					}

				}

				// Traitement ramasser objets à terre
				if(isset($_GET['ramasser']) && $_GET['ramasser'] == "ok"){

					if ($pa_perso >= 1) {

						// MAJ pa perso
						$sql = "UPDATE perso SET pa_perso=pa_perso-1 WHERE id_perso='$id_perso'";
						$mysqli->query($sql);

						$liste_ramasse = "";

						// récupération de la liste des objets à terre
						$sql = "SELECT type_objet, id_objet, nb_objet FROM objet_in_carte WHERE x_carte='$x_persoN' AND y_carte='$y_persoN'";
						$res = $mysqli->query($sql);

						while ($t = $res->fetch_assoc()) {

							$type_objet = $t['type_objet'];
							$id_objet	= $t['id_objet'];
							$nb_objet	= $t['nb_objet'];

							// Si perso n'est pas un chef on empeche de ramasser un étendard
							if($type_perso != 1 && $type_objet == 2 && ($id_objet == 8 || $id_objet == 9)){
								$erreur .= "Vous n'avez pas le droit de ramasser un étendard.";
							} else {
								// Suppression de l'objet par terre
								$sql_d = "DELETE FROM objet_in_carte WHERE type_objet='$type_objet' AND id_objet='$id_objet' AND x_carte='$x_persoN' AND y_carte='$y_persoN'";
								$mysqli->query($sql_d);

								// Récupération poid objet
								// Thunes
								if ($type_objet == 1) {
									$poid_objet = 0;

									// Ajout de la thune au perso
									$sql_t = "UPDATE perso SET or_perso=or_perso+$nb_objet WHERE id_perso='$id_perso'";
									$mysqli->query($sql_t);

									$liste_ramasse .= $nb_objet . " Thune";
									if ($nb_objet > 1) {
										$liste_ramasse .= "s";
									}
								}

								// Objet
								if ($type_objet == 2) {
									$sql_obj = "SELECT nom_objet, poids_objet FROM objet WHERE id_objet='$id_objet'";
									$res_obj = $mysqli->query($sql_obj);
									$t_obj = $res_obj->fetch_assoc();

									$nom_objet	= $t_obj['nom_objet'];
									$poid_objet = $t_obj['poids_objet'];

									for ($i = 0; $i < $nb_objet; $i++) {
									// Ajout de l'objet dans l'inventaire du perso
									$sql_o = "INSERT INTO perso_as_objet (id_perso, id_objet) VALUES ('$id_perso', '$id_objet')";
									$mysqli->query($sql_o);
									}

									// calcul charge objets
									$charge_objets_total = $poid_objet * $nb_objet;

									// MAJ charge perso
									$sql_c = "UPDATE perso SET charge_perso = charge_perso + $charge_objets_total WHERE id_perso='$id_perso'";
									$mysqli->query($sql_c);

									$liste_ramasse .= " -- ". $nb_objet . " " . $nom_objet;
								}

								// Arme
								if ($type_objet == 3) {
									$sql_obj = "SELECT nom_arme, poids_arme FROM arme WHERE id_arme='$id_objet'";
									$res_obj = $mysqli->query($sql_obj);
									$t_obj = $res_obj->fetch_assoc();

									$nom_arme	= $t_obj['nom_arme'];
									$poid_objet = $t_obj['poids_arme'];

									for ($i = 0; $i < $nb_objet; $i++) {
										// Ajout de l'arme dans l'inventaire du perso
										$sql_a = "INSERT INTO perso_as_arme (id_perso, id_arme, est_portee) VALUES ('$id_perso', '$id_objet', '0')";
										$mysqli->query($sql_a);
									}

									// calcul charge armes
									$charge_objets_total = $poid_objet * $nb_objet;

									// MAJ charge perso
									$sql_c = "UPDATE perso SET charge_perso = charge_perso + $charge_objets_total WHERE id_perso='$id_perso'";
									$mysqli->query($sql_c);

									$liste_ramasse .= " -- ". $nb_objet . " " . $nom_arme;
								}
							}
						}

						// mise a jour des evenements
						$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ('$id_perso','<font color=$couleur_clan_p><b>$nom_perso</b></font>','a ramassé des objets par terre ',NULL,'','en $x_persoN/$y_persoN : $liste_ramasse',NOW(),'0')";
						$mysqli->query($sql);

						echo "<center><font colot='blue'>Vous avez ramassé les objets suivants : ". $liste_ramasse ."</font></center><br>";
					}
					else {
						$erreur .= "Vous n'avez pas assez de PA pour rammasser les objets à terre.";
					}
				}

				// traitement des deplacements
				if (isset($_GET["mouv"])) {

					$mouv = $_GET["mouv"];

					$x_persoE = $t_perso1["x_perso"];
					$y_persoE = $t_perso1["y_perso"];
					$pm_perso = $t_perso1["pm_perso"];

					if (!in_bat($mysqli, $id_perso) && !in_train($mysqli, $id_perso)) {

						if (reste_pm($pm_perso + $malus_pm)) {

							//on modifie les coordonnées du perso suivant le deplacement qu'il a effectué
							switch($mouv){
								case 1: $x_persoN=$x_persoE-1; $y_persoN=$y_persoE+1; break;
								case 2: $x_persoN=$x_persoE; $y_persoN=$y_persoE+1; break;
								case 3: $x_persoN=$x_persoE+1; $y_persoN=$y_persoE+1; break;
								case 4: $x_persoN=$x_persoE-1; $y_persoN=$y_persoE; break;
								case 5: $x_persoN=$x_persoE+1; $y_persoN=$y_persoE; break;
								case 6: $x_persoN=$x_persoE-1; $y_persoN=$y_persoE-1; break;
								case 7: $x_persoN=$x_persoE; $y_persoN=$y_persoE-1; break;
								case 8: $x_persoN=$x_persoE+1; $y_persoN=$y_persoE-1; break;
							}

							$in_map = in_map($x_persoN, $y_persoN, $X_MAX, $Y_MAX);

							if ($in_map) {

								$sql = "SELECT occupee_carte, fond_carte, image_carte FROM $carte WHERE x_carte=$x_persoN AND y_carte=$y_persoN";
								$res_map = $mysqli->query($sql);
								$t_carte1 = $res_map->fetch_assoc();

								$case_occupee 	= $t_carte1["occupee_carte"];
								$fond 			= $t_carte1["fond_carte"];

								$cout_pm 	= cout_pm($fond, $type_perso);

								if (!is_eau_p($fond)) {

									if (!$case_occupee){

										if($pm_perso  + $malus_pm >= $cout_pm){

											$chance = rand(1,1000);

											if ($chance == 1) {

												// échec critique, le perso trébuche, perd 1PM et reste sur place
												$sql = "UPDATE perso SET pm_perso=pm_perso-1 WHERE id_perso='$id_perso'";
												$mysqli->query($sql);

												$erreur .= "<b>Vous avez trébuché, vous perdez 1PM !</b>";
												// mise a jour des évènements
												$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'$nom_perso','a trébuché',NULL,'','en $x_persoN/$y_persoN',NOW(),'0')";
												$mysqli->query($sql);
											}
											else {

												// maj perso : mise à jour des pm et du bonus de perception
												$sql = "UPDATE perso SET pm_perso =$pm_perso-$cout_pm, bonusPerception_perso=$bonus_visu WHERE id_perso='$id_perso'";
												$mysqli->query($sql);

												//mise à jour des coordonnées du perso
												$dep = "UPDATE perso SET x_perso=$x_persoN, y_perso=$y_persoN WHERE id_perso ='$id_perso'";
												$mysqli->query($dep);

												// maj carte perso
												$sql = "UPDATE $carte SET occupee_carte='0', image_carte=NULL, idPerso_carte=save_info_carte WHERE x_carte='$x_persoE' AND y_carte='$y_persoE'";
												$mysqli->query($sql);

												$sql = "UPDATE $carte SET occupee_carte='1', image_carte='$image_perso', idPerso_carte='$id_perso' WHERE x_carte='$x_persoN' AND y_carte='$y_persoN'";
												$mysqli->query($sql);

												// maj carte brouillard de guerre
												$perception_final = $perception_perso + $bonus_visu;

												//maj_visu($mysqli, $clan_p, $carte, $x_persoN, $y_persoN, $perception_final, $id_perso);

												// maj evenement
												$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_p><b>$nom_perso</b></font>','s\'est deplacé',NULL,'','en $x_persoN/$y_persoN',NOW(),'0')";
												$mysqli->query($sql);

												if ($chance == 1000) {
													// réussite critique : gain de 1PM
													$sql = "UPDATE perso SET pm_perso=pm_perso+1 WHERE id_perso='$id_perso'";
													$mysqli->query($sql);

													// mise a jour des évènements
													$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'$nom_perso','est en forme aujourd\'hui !',NULL,'','',NOW(),'0')";
													$mysqli->query($sql);
													header("location:jouer.php?message=gainPM");
												}
												else {
													header("location:jouer.php");
												}
											}
										}
										else{

											$erreur .= "Vous n'avez pas assez de pm !";

											// verification si il y a un batiment a proximite du perso
											$mess_bat .= afficher_lien_prox_bat($mysqli, $x_persoE, $y_persoE, $id_perso, $type_perso);
										}
									}
									else {

										// Verification de qui / quoi occupe la case pour voir si on peut le bousculer
										$sql = "SELECT idPerso_carte FROM $carte WHERE x_carte='$x_persoN' AND y_carte='$y_persoN'";
										$res = $mysqli->query($sql);
										$t = $res->fetch_assoc();

										$idPerso_carte = $t['idPerso_carte'];

										// Batiment
										if ($idPerso_carte < 200000 && $idPerso_carte >= 50000) {
											$erreur .= "Cette case est déjà occupée par un batiment !";
										}
										else if ($idPerso_carte >= 200000) {
											// PNJ
											$erreur .= "Cette case est déjà occupée par un pnj !";
										} else {
											if ($bousculade_dep || isset($_GET['action_popup'])) {

												// Perso
												// Récupération des informations du perso
												$sql = "SELECT clan, pm_perso, pa_perso, type_perso, image_perso, nom_perso FROM perso WHERE id_perso='$idPerso_carte'";
												$res = $mysqli->query($sql);
												$t = $res->fetch_assoc();

												$camp_perso_b 	= $t['clan'];
												$pm_perso_b		= $t['pm_perso'];
												$pa_perso_b		= $t['pa_perso'];
												$type_perso_b	= $t['type_perso'];
												$image_perso_b	= $t['image_perso'];
												$nom_perso_b	= $t['nom_perso'];
												$id_perso_b 	= $idPerso_carte;

												$couleur_clan_p_b = couleur_clan($camp_perso_b);

												// Calcul case cible bousculade
												switch($mouv){
													case 1: $x_persoB=$x_persoE-2; $y_persoB=$y_persoE+2; break;
													case 2: $x_persoB=$x_persoE; $y_persoB=$y_persoE+2; break;
													case 3: $x_persoB=$x_persoE+2; $y_persoB=$y_persoE+2; break;
													case 4: $x_persoB=$x_persoE-2; $y_persoB=$y_persoE; break;
													case 5: $x_persoB=$x_persoE+2; $y_persoB=$y_persoE; break;
													case 6: $x_persoB=$x_persoE-2; $y_persoB=$y_persoE-2; break;
													case 7: $x_persoB=$x_persoE; $y_persoB=$y_persoE-2; break;
													case 8: $x_persoB=$x_persoE+2; $y_persoB=$y_persoE-2; break;
												}

												// Est ce que le perso peut être bousculer par mon perso

												// types perso compatible pour bousculade ?
												if (isTypePersoBousculable($type_perso, $type_perso_b)) {

													// Ai-je suffisamment de PA / PM pour effectuer la bousculade ?
													if($pm_perso  + $malus_pm >= $cout_pm && $pa_perso >= 3){

														// Case cible de la bousculade est-elle hors carte ?
														if (in_map($x_persoB, $y_persoB, $X_MAX, $Y_MAX)) {

															$sql = "SELECT occupee_carte, fond_carte, image_carte FROM $carte WHERE x_carte=$x_persoB AND y_carte=$y_persoB";
															$res_map = $mysqli->query($sql);
															$t_carteB = $res_map->fetch_assoc();

															$case_occupeeB 	= $t_carteB["occupee_carte"];
															$fondB 			= $t_carteB["fond_carte"];

															$cout_pmB 		= cout_pm($fondB, $type_perso_b);
															$bonus_visuB 	= get_malus_visu($fondB) + getBonusObjet($mysqli, $id_perso);

															// Case cible de la bousculade est-elle déjà occupée ?
															if (!$case_occupeeB) {
																// Case cible eau profonde ?
																if (!is_eau_p($fondB)) {

																	// Même camp ou non ?
																	if ($camp_perso_b == $clan_p) {
																		// Même camp
																		// Si allié, mon allié possède t-il encore 1PA ?
																		if ($pa_perso_b >= 1) {

																			// OK => On bouscule !

																			//-------------------------------------
																			// On déplace en premier le bousculé
																			$sql = "UPDATE perso SET pa_perso = $pa_perso_b-1, bonusPerception_perso=$bonus_visuB WHERE id_perso='$id_perso_b'";
																			$mysqli->query($sql);

																			//mise à jour des coordonnées du perso
																			$dep = "UPDATE perso SET x_perso=$x_persoB, y_perso=$y_persoB WHERE id_perso ='$id_perso_b'";
																			$mysqli->query($dep);

																			// maj carte
																			$sql = "UPDATE $carte SET occupee_carte='0', image_carte=NULL, idPerso_carte=save_info_carte WHERE x_carte='$x_persoN' AND y_carte='$y_persoN'";
																			$mysqli->query($sql);

																			$sql = "UPDATE $carte SET occupee_carte='1', image_carte='$image_perso_b', idPerso_carte='$id_perso_b' WHERE x_carte='$x_persoB' AND y_carte='$y_persoB'";
																			$mysqli->query($sql);

																			//-----------------------
																			// On se déplace ensuite
																			$sql = "UPDATE perso SET pm_perso =$pm_perso-$cout_pm, pa_perso = $pa_perso-3, bonusPerception_perso=$bonus_visu WHERE id_perso='$id_perso'";
																			$mysqli->query($sql);

																			//mise à jour des coordonnées du perso
																			$dep = "UPDATE perso SET x_perso=$x_persoN, y_perso=$y_persoN WHERE id_perso ='$id_perso'";
																			$mysqli->query($dep);

																			// maj carte
																			$sql = "UPDATE $carte SET occupee_carte='0', image_carte=NULL, idPerso_carte=save_info_carte WHERE x_carte='$x_persoE' AND y_carte='$y_persoE'";
																			$mysqli->query($sql);

																			$sql = "UPDATE $carte SET occupee_carte='1', image_carte='$image_perso', idPerso_carte='$id_perso' WHERE x_carte='$x_persoN' AND y_carte='$y_persoN'";
																			$mysqli->query($sql);

																			// maj carte brouillard de guerre
																			$perception_final = $perception_perso + $bonus_visu;
																			//maj_visu($mysqli, $clan_p, $carte, $x_persoN, $y_persoN, $perception_final, $id_perso);

																			// maj evenement
																			$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_p><b>$nom_perso</b></font>','a bousculé ',$id_perso_b,'<font color=$couleur_clan_p_b><b>$nom_perso_b</b></font>','en $x_persoB/$y_persoB',NOW(),'0')";
																			$mysqli->query($sql);

																			header("location:jouer.php");

																		} else {
																			$erreur .= "Votre allié ne possède plus suffisamment de PA pour être bousculer (demande 1 PA à votre allié) !";
																		}
																	} else {
																		// Camps différents

																		// -------------
																		// - ANTI ZERK -
																		// -------------
																		$verif_anti_zerk = gestion_anti_zerk($mysqli, $id_perso);

																		if ($verif_anti_zerk) {

																			$chance_bouculade = mt_rand(0,100);

																			$date_log = time();

																			$sql = "INSERT INTO log (date_log, id_perso, type_action, pourcentage, message_log)
																					VALUES (FROM_UNIXTIME($date_log), '$id_perso', 'Bousculade', '$chance_bouculade', '$id_perso a bousculé $id_perso_b')";
																			$mysqli->query($sql);

																			if ($chance_bouculade <= 66) {

																				// OK => On bouscule !

																				//-------------------------------------
																				// On déplace en premier le bousculé
																				// maj perso : mise à jour des pm et du bonus de perception
																				$sql = "UPDATE perso SET pm_perso = pm_perso-$cout_pmB, bonusPerception_perso=$bonus_visuB WHERE id_perso='$id_perso_b'";
																				$mysqli->query($sql);

																				//mise à jour des coordonnées du perso
																				$dep = "UPDATE perso SET x_perso=$x_persoB, y_perso=$y_persoB WHERE id_perso ='$id_perso_b'";
																				$mysqli->query($dep);

																				// maj carte
																				$sql = "UPDATE $carte SET occupee_carte='0', image_carte=NULL, idPerso_carte=save_info_carte WHERE x_carte='$x_persoN' AND y_carte='$y_persoN'";
																				$mysqli->query($sql);

																				$sql = "UPDATE $carte SET occupee_carte='1', image_carte='$image_perso_b', idPerso_carte='$id_perso_b' WHERE x_carte='$x_persoB' AND y_carte='$y_persoB'";
																				$mysqli->query($sql);

																				//-----------------------
																				// On se déplace ensuite
																				$sql = "UPDATE perso SET pm_perso =$pm_perso-$cout_pm, pa_perso = $pa_perso-3, bonusPerception_perso=$bonus_visu WHERE id_perso='$id_perso'";
																				$mysqli->query($sql);

																				//mise à jour des coordonnées du perso
																				$dep = "UPDATE perso SET x_perso=$x_persoN, y_perso=$y_persoN WHERE id_perso ='$id_perso'";
																				$mysqli->query($dep);

																				// maj carte
																				$sql = "UPDATE $carte SET occupee_carte='0', image_carte=NULL, idPerso_carte=save_info_carte WHERE x_carte='$x_persoE' AND y_carte='$y_persoE'";
																				$mysqli->query($sql);

																				$sql = "UPDATE $carte SET occupee_carte='1', image_carte='$image_perso', idPerso_carte='$id_perso' WHERE x_carte='$x_persoN' AND y_carte='$y_persoN'";
																				$mysqli->query($sql);

																				// maj carte brouillard de guerre
																				$perception_final = $perception_perso + $bonus_visu;
																				//maj_visu($mysqli, $clan_p, $carte, $x_persoN, $y_persoN, $perception_final, $id_perso);

																				// maj evenement
																				$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_p><b>$nom_perso</b></font>','a bousculé ',$id_perso_b,'<font color=$couleur_clan_p_b><b>$nom_perso_b</b></font>','en $x_persoB/$y_persoB',NOW(),'0')";
																				$mysqli->query($sql);

																				//header("location:jouer.php");
																			}
																			else {
																				// MAJ pa perso
																				$sql = "UPDATE perso SET pa_perso = $pa_perso-3 WHERE id_perso='$id_perso'";
																				$mysqli->query($sql);

																				// maj evenement
																				$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_p><b>$nom_perso</b></font>','a raté sa bousculade sur ',$id_perso_b,'<font color=$couleur_clan_p_b><b>$nom_perso_b</b></font>','',NOW(),'0')";
																				$mysqli->query($sql);

																				$erreur .= "Vous avez raté votre bousculade et perdez 3PA";
																			}
																		}
																		else {
																			$erreur .= "Loi anti-zerk non respectée !";
																		}
																	}
																} else {
																	$erreur .= "Impossible de bousculer un perso dans de l'eau profonde !";
																}
															} else {
																$erreur .= "La case cible de la bousculade est déjà occupée !";
															}
														} else {
															$erreur .= "Impossible de bousculer un perso hors map !";
														}
													}
													else {
														$erreur .= "Vous n'avez pas assez de PA/PM pour bousculer un perso !";
													}
												} else {
													$erreur .= "Impossible de bousculer ce type de perso !";
												}
											}
											else {
												$erreur .= "Cette case des déjà occupée par un autre perso !";
											}
										}

										// verification si il y a un batiment a proximite du perso
										$mess_bat .= afficher_lien_prox_bat($mysqli, $x_persoE, $y_persoE, $id_perso, $type_perso);
									}
								}
								else if (is_eau_p($fond)) {

									$erreur .= "Vous ne pouvez pas vous deplacer en eau profonde !";

									// verification si il y a un batiment a proximite du perso
									$mess_bat .= afficher_lien_prox_bat($mysqli, $x_persoE, $y_persoE, $id_perso, $type_perso);
								}
							}
							else if (!in_map($x_persoN, $y_persoN, $X_MAX, $Y_MAX)){

								$erreur .= "Vous ne pouvez pas vous déplacer sur cette case, elle est hors limites !";

								// verification si il y a un batiment a proximite du perso
								$mess_bat .= afficher_lien_prox_bat($mysqli, $x_persoE, $y_persoE, $id_perso, $type_perso);
							}
						}
						else if(!reste_pm($pm_perso + $malus_pm)){

							header("Location:jouer.php?erreur=pm");
						}
						else {
							// normalement impossible
							$erreur .= "Veuillez contacter l'administrateur si vous voyez ce message, merci";
						}
					}
					else {
						$erreur .= "Vous ne pouvez pas vous déplacer si vous êtes dans un bâtiment ou un train";
					}
				}
				else {
					if (!in_train($mysqli, $id_perso)) {
						// verification si il y a un batiment a proximite du perso
						$mess_bat .= afficher_lien_prox_bat($mysqli, $x_persoN, $y_persoN, $id_perso, $type_perso);
					}
				}

				?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<title>Nord VS Sud</title>

		<!-- Required meta tags -->
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=0.3, shrink-to-fit=no">

		<!-- Bootstrap CSS -->
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

		<link href="../style2.css" rel="stylesheet" type="text/css">

	</head>

	<body>
				<?php
				$date_serveur = new DateTime('now', new DateTimeZone('Europe/Paris'));

				$date_dla = date('d-m-Y H:i:s', $n_dla);

				if (anim_perso($mysqli, $id_perso)) {
					// Récupération des demandes sur la gestion des compagnies
					$sql = "SELECT * FROM compagnie_demande_anim, compagnies
							WHERE compagnie_demande_anim.id_compagnie = compagnies.id_compagnie
							AND compagnies.id_clan='$clan_p'";
					$res = $mysqli->query($sql);
					$nb_demandes_gestion_compagnie = $res->num_rows;

					// Récupération des demandes sur la gestion des persos
					$sql = "(SELECT perso_demande_anim.* FROM perso_demande_anim, perso
							WHERE perso_demande_anim.id_perso = perso.id_perso
							AND perso.clan = '$clan_p'
							AND perso_demande_anim.type_demande = 1)
							UNION ALL
							(SELECT perso_demande_anim.* FROM perso_demande_anim, perso
							WHERE perso_demande_anim.id_perso = perso.idJoueur_perso
							AND perso.clan = '$clan_p'
							AND perso.chef = '1'
							AND perso_demande_anim.type_demande > 1)
							";
					$res = $mysqli->query($sql);
					$nb_demandes_gestion_perso = $res->num_rows;

					// Récupération du nombre de questions / remontées anims en attente de réponse
					$sql = "SELECT id FROM anim_question WHERE id_camp='$clan_p' AND status='0'";
					$res = $mysqli->query($sql);
					$nb_questions_anim = $res->num_rows;

					// Récupération du nombre de remontées de capture RP non traitées
					$sql = "SELECT id FROM anim_capture WHERE statut='0'";
					$res = $mysqli->query($sql);
					$nb_captures_anim = $res->num_rows;

					$nb_demande_a_traiter = $nb_demandes_gestion_compagnie + $nb_demandes_gestion_perso + $nb_questions_anim + $nb_captures_anim;
				}

				// Récupération du nombre de missions actives
				$sql_ma = "SELECT id_mission, nom_mission, texte_mission, recompense_thune, recompense_xp, recompense_pc, nombre_participant, date_debut_mission, date_fin_mission
						FROM missions WHERE date_debut_mission IS NOT NULL AND (date_fin_mission IS NULL OR date_fin_mission >= CURDATE())
						AND camp_mission='$clan_p'";
				$res_ma = $mysqli->query($sql_ma);
				$nb_missions_actives = $res_ma->num_rows;

				//affichage de l'heure serveur et de nouveau tour
				echo "<table width=100% bgcolor='white' border=0>";
				echo "<tr>
						<td><img src='../images/clock.png' alt='horloge' width='25' height='25'/> Heure serveur : <b><span id=tp1>".$date_serveur->format('H:i:s')."</span></b></td>
						<td rowspan=2><img src='../images/accueil/banniere.jpg' alt='banniere Nord VS Sud' width=150 height=63 /></td>
						<td align=right> <a class='btn btn-danger' href=\"../logout.php\"><b>Déconnexion</b></a></td>
					</tr>";
				echo "<tr>";
				echo "	<td>Prochain tour :  ".$date_dla."</td>";
				echo "	<td align=right>";
				echo "		<a class='btn btn-info' href=\"https://encyclopedie.nord-vs-sud.fr\"><b>Règles</b></a>";
				echo "		<a class='btn btn-info' href=\"../faq.php\"><b>FAQ</b></a>";
				echo "		<a class='btn btn-primary' href=\"http://www.forum.persee.ovh/\" target='_blank'><b>Forum</b></a>";
				if ($type_perso != 6) {
					echo "		<a class='btn btn-primary' href=\"question_anim.php\"><b>Questions Anim</b></a>";
					echo "		<a class='btn btn-primary' href=\"capture.php\"><b>Déclarer une capture</b></a>";
				}
				echo "		<a class='btn btn-warning' href=\"missions.php\"><b>Missions ";
				if ($nb_missions_actives > 0) {
					echo "<span class='badge badge-success'>".$nb_missions_actives."</span>";
				}
				echo "</b></a>";

				// Redacteur
				if(redac_perso($mysqli, $id_perso)) {
					echo " <a class='btn btn-warning' href='redacteur.php'>Redaction</a>";
				}

				// Animation
				if(anim_perso($mysqli, $id_perso)) {
					echo " <a class='btn btn-warning' href='animation.php'>Animation <span class='badge badge-danger' title='".$nb_demande_a_traiter." demandes en attente'>";
					if ($nb_demande_a_traiter > 0) {
						echo $nb_demande_a_traiter;
					}
					echo "</span></a>";
				}

				// Admin
				if($admin) {
					echo " <a class='btn btn-warning' href='admin_nvs.php'>Admin</a>";
				}

				// Ajout Jacklegende du 23/04 - lien Discord et de la visu
				echo " <a class='btn btn-info' href='https://discord.gg/EMqRMzHKjZ' target='_blank'>Discord Commun</a>";

				$id_joueur_camp 	= $_SESSION["ID_joueur"];

				// recuperation des infos du perso
				$sql_camp = "SELECT clan FROM perso WHERE idJoueur_perso='$id_joueur_camp'";
				$res_camp = $mysqli->query($sql_camp);
				$t_perso_camp = $res_camp->fetch_assoc();

				$id_joueur_perso_camp 	= $t_perso_camp["clan"];

				if($id_joueur_perso_camp == 1){
						echo " <a class='btn btn-info' href='https://discord.gg/xYSyWjbsJf' target='_blank'>Discord nord </a>";
				} else if ($id_joueur_perso_camp == 2){
						echo " <a class='btn btn-info' href='https://discord.gg/68aCHYuths' target='_blank'>Discord sud</a>";
				}

				echo " <a class='btn btn-info' href='visu.php'>Visu</a>";
				//fin d'ajout lien Discord et de la visu

				echo "	</td>";
				echo "</tr>";
				echo "</table>";

				$sql_info = "SELECT xp_perso, pc_perso, pv_perso, pvMax_perso, pa_perso, paMax_perso, pi_perso, pm_perso, pmMax_perso, recup_perso, protec_perso, type_perso, x_perso, y_perso, perception_perso, bonusPerception_perso, bonusRecup_perso, bonusPA_perso, bonusPM_perso, bonus_perso, charge_perso, chargeMax_perso, image_perso, message_perso, clan, bataillon FROM perso WHERE ID_perso ='$id_perso'";
				$res_info = $mysqli->query($sql_info);
				$t_perso2 = $res_info->fetch_assoc();

				$x_perso 				= $t_perso2["x_perso"];
				$y_perso 				= $t_perso2["y_perso"];
				$image_perso 			= $t_perso2["image_perso"];
				$perc 					= $t_perso2["perception_perso"] + $t_perso2["bonusPerception_perso"];
				$pa_perso 				= $t_perso2["pa_perso"];
				$paMax_perso 			= $t_perso2["paMax_perso"];
				$pi_perso 				= $t_perso2["pi_perso"];
				$xp_perso 				= $t_perso2["xp_perso"];
				$pc_perso 				= $t_perso2["pc_perso"];
				$pv_perso 				= $t_perso2["pv_perso"];
				$pvMax_perso 			= $t_perso2["pvMax_perso"];
				$pm_perso_tmp			= $t_perso2["pm_perso"];
				$pmMax_perso_tmp 		= $t_perso2["pmMax_perso"];
				$perception_perso 		= $t_perso2["perception_perso"];
				$bonusPerception_perso 	= $t_perso2["bonusPerception_perso"];
				$bonusPA_perso			= $t_perso2["bonusPA_perso"];
				$recup_perso 			= $t_perso2["recup_perso"];
				$bonusRecup_perso		= $t_perso2["bonusRecup_perso"];
				$bonusPM_perso			= $t_perso2["bonusPM_perso"];
				$protec_perso 			= $t_perso2["protec_perso"];
				$bonus_perso 			= $t_perso2["bonus_perso"];
				$type_perso 			= $t_perso2["type_perso"];
				$bataillon_perso 		= $t_perso2["bataillon"];
				$message_perso			= $t_perso2["message_perso"];
				$charge_perso			= $t_perso2["charge_perso"];
				$chargeMax_perso		= $t_perso2["chargeMax_perso"];

				// Bonus recup batiment
				$bonus_recup_bat 		= get_bonus_recup_bat_perso($mysqli, $id_perso);
				$bonus_recup_terrain 	= get_bonus_recup_terrain_perso($mysqli, $x_perso, $y_perso);

				$bonusRecup_perso += $bonus_recup_bat;
				$bonusRecup_perso += $bonus_recup_terrain;



				if (in_bat($mysqli, $id_perso)) {

					$id_instance_bat_perso = in_bat($mysqli, $id_perso);

					$sql_b = "SELECT batiment.id_batiment, nom_batiment, taille_batiment, nom_instance FROM batiment, instance_batiment
							WHERE instance_batiment.id_batiment = batiment.id_batiment
							AND instance_batiment.id_instanceBat = '$id_instance_bat_perso'";
					$res_b = $mysqli->query($sql_b);
					$t_b = $res_b->fetch_assoc();

					$id_bat_perso 			= $t_b['id_batiment'];
					$nom_bat_perso			= $t_b['nom_batiment'];
					$taille_bat_perso		= $t_b['taille_batiment'];
					$nom_instance_bat_perso	= $t_b['nom_instance'];
				}
				// Si perso chien
				if ($type_perso == 6) {
					if(is_chien_eloigne_chef($mysqli, $id_joueur_perso, $x_perso, $y_perso )){
						$bonusPerception_perso -= 3;
						$perc -= 3;
					}
				}
				// calcul malus pm
				$malus_pm_charge = getMalusCharge($charge_perso, $chargeMax_perso);
				if ($malus_pm_charge == 100) {
					$malus_pm = -$pmMax_perso;
				}
				else {
					$malus_pm = $malus_pm_charge;
				}

				$pmMax_perso 	= $pmMax_perso_tmp + $bonusPM_perso;
				$pm_perso 		= $pm_perso_tmp + $malus_pm;

				$clan_perso = $t_perso2["clan"];

				if($clan_perso == 1){
					$clan = 'rond_b.png';
					$couleur_clan_perso = 'blue';

					$image_profil 		= "profil_nord4.png";
					$image_sac 			= "sac_nord2.png";
					$image_compagnie 	= "compagnie_nord2.png";
					$image_evenement 	= "evenement_nord.png";
					$image_messagerie 	= "messagerie_nord.png";
					$image_em 			= "em_nord2.png";

				}else if($clan_perso == 2){
					$clan = 'rond_r.png';
					$couleur_clan_perso = 'red';

					$image_profil 		= "profil_sud4.png";
					$image_sac 			= "sac_sud2.png";
					$image_compagnie 	= "compagnie_sud2.png";
					$image_evenement 	= "evenement_sud.png";
					$image_messagerie 	= "messagerie_sud.png";
					$image_em 			= "em_sud2.png";

				}else if($clan_perso == 0){
					$clan = 'rond_r.png';
					$couleur_clan_perso = 'black';

					$image_profil 		= "profil_sud4.png";
					$image_sac 			= "sac_sud2.png";
					$image_compagnie 	= "compagnie_sud2.png";
					$image_evenement 	= "evenement_sud.png";
					$image_messagerie 	= "messagerie_sud.png";
					$image_em 			= "em_sud2.png";
				}

				maj_visu($mysqli, $clan_p, $carte, $x_perso, $y_perso, $perc, $id_perso, $type_perso, $id_joueur_perso);

				// récupération du grade du perso
				$sql_grade = "SELECT perso_as_grade.id_grade, nom_grade FROM perso_as_grade, grades WHERE perso_as_grade.id_grade = grades.id_grade AND id_perso='$id_perso'";
				$res_grade = $mysqli->query($sql_grade);
				$t_grade = $res_grade->fetch_assoc();

				$id_grade_perso 	= $t_grade["id_grade"];
				$nom_grade_perso 	= $t_grade["nom_grade"];

				// cas particuliers grouillot
				if ($id_grade_perso == 101) {
					$id_grade_perso = "1.1";
				}
				if ($id_grade_perso == 102) {
					$id_grade_perso = "1.2";
				}

				$nom_compagnie_perso = "";
				$nb_demandes_adhesion_compagnie = 0;
				$nb_demandes_emprunt_compagnie	= 0;
				$nb_demandes_depart_compagnie	= 0;

				// recuperation de l'id de la compagnie du perso
				$sql_groupe = "SELECT id_compagnie from perso_in_compagnie where id_perso='$id_perso' AND (attenteValidation_compagnie='0' OR attenteValidation_compagnie='2')";
				$res_groupe = $mysqli->query($sql_groupe);
				$t_groupe = $res_groupe->fetch_assoc();
				$nb = $res_groupe->num_rows;

				$id_compagnie = $nb ? $t_groupe['id_compagnie'] : 0;
				$genie_compagnie_perso	= 0;

				if($id_compagnie){

					// Recuperation des infos sur la compagnie (dont le nom)
					$sql_groupe2 = "SELECT * FROM compagnies WHERE id_compagnie='$id_compagnie'";
					$res_groupe2 = $mysqli->query($sql_groupe2);
					$t_groupe2 = $res_groupe2->fetch_assoc();

					$nom_compagnie_perso 		= addslashes($t_groupe2['nom_compagnie']);
					$image_compagnie_perso		= $t_groupe2['image_compagnie'];
					$genie_compagnie_perso		= $t_groupe2['genie_civil'];
					$id_parent_compagnie_perso	= $t_groupe2['id_parent'];

					if (isset($id_parent_compagnie_perso)) {

						$sql_p = "SELECT nom_compagnie FROM compagnies WHERE id_compagnie='$id_parent_compagnie_perso'";
						$res_p = $mysqli->query($sql_p);
						$t_p = $res_p->fetch_assoc();

						$nom_compagnie_mere = addslashes($t_p['nom_compagnie']);

						$nom_compagnie_perso = $nom_compagnie_mere." - ".$nom_compagnie_perso;

					}

					// Quel est le poste du perso dans la compagnie ?
					$sql = "SELECT poste_compagnie FROM perso_in_compagnie WHERE id_compagnie='$id_compagnie' AND id_perso='$id_perso'";
					$res = $mysqli->query($sql);
					$t = $res->fetch_assoc();

					$poste_perso_compagnie = $t['poste_compagnie'];

					// Chef ou Recruteur
					if ($poste_perso_compagnie == 1 || $poste_perso_compagnie == 4) {

						// Vérifier nouvelles demandes d'adhésion
						$sql = "SELECT id_perso FROM perso_in_compagnie WHERE id_compagnie='$id_compagnie' AND attenteValidation_compagnie='1'";
						$res = $mysqli->query($sql);
						$nb_demandes_adhesion_compagnie = $res->num_rows;

						// Vérifier nouvelles demandes de départ
						$sql = "SELECT id_perso FROM perso_in_compagnie WHERE id_compagnie='$id_compagnie' AND attenteValidation_compagnie='2'";
						$res = $mysqli->query($sql);
						$nb_demandes_depart_compagnie = $res->num_rows;
					}

					// Chef ou Trésorier
					if ($poste_perso_compagnie == 1 || $poste_perso_compagnie == 3) {

						// Vérifier nouvelles demandes d'emprunt
						$sql = "SELECT banque_compagnie.id_perso FROM banque_compagnie, perso, perso_in_compagnie
								WHERE banque_compagnie.id_perso = perso.id_perso
								AND perso.id_perso = perso_in_compagnie.id_perso
								AND perso_in_compagnie.id_compagnie='$id_compagnie'
								AND demande_emprunt='1'";
						$res = $mysqli->query($sql);
						$nb_demandes_emprunt_compagnie = $res->num_rows;

					}
				}
				else {
					$image_compagnie_perso = "";
				}

				// Le perso est-il membre de l'etat major de son camp ?
				$sql_em = "SELECT * FROM perso_in_em WHERE id_perso='$id_perso' AND camp_em='$clan_perso'";
				$res_em = $mysqli->query($sql_em);
				$nb_em = $res_em->num_rows;

				if ($nb_em) {
					$pourc_icone = "12%";

					// Verifier nombre compagnies en attente de validation
					$sql = "SELECT * FROM em_creer_compagnie WHERE camp='$clan_perso'";
					$res = $mysqli->query($sql);
					$nb_compagnie_attente_em = $res->num_rows;

				} else if ($type_perso == 6) {
					$pourc_icone = "20%";
				} else {
					$pourc_icone = "14%";
				}

				// Récupération de tous les persos du joueur
				$sql = "SELECT id_perso, nom_perso, chef FROM perso WHERE idJoueur_perso='$id_joueur_perso' AND est_renvoye=0 ORDER BY id_perso";
				$res = $mysqli->query($sql);

				// init vide
				$nom_perso_chef = "";

				?>
				<!-- Début du tableau d'information-->
				<table border=1 align="center" width=90%>
					<tr>
						<td width=120>
							<center>
								<div width=40 height=40 style="position: relative;">
									<div style="position: absolute;bottom: 0;text-align: center; width: 100%;font-weight: bold;">
										<?php echo $id_perso; ?>
									</div>
									<img src="../images_perso/<?php echo "$image_perso";?>" width=40 height=40>
								</div>
							</center>
						</td>
						<td align=center>
							<form method='post' action='jouer.php'>
								<b>Nom : </b><select name='liste_perso' onchange="this.form.submit()">
								<?php
								while($t_liste_perso = $res->fetch_assoc()) {

									$id_perso_liste 	= $t_liste_perso["id_perso"];
									$nom_perso_liste 	= $t_liste_perso["nom_perso"];
									$chef_perso			= $t_liste_perso["chef"];

									if ($chef_perso) {
										$nom_perso_chef = $nom_perso_liste;
									}

									echo "<option value='$id_perso_liste'";
									if ($id_perso == $id_perso_liste) {
										echo " selected";
									}
									echo ">$nom_perso_liste [$id_perso_liste]</option>";
								}
								?>
								</select>
								<input type='submit' name='select_perso' value='ok' />
							</form>
						</td>
						<td align=center><b>Grade : <a href="grades.php"></b><?php echo $nom_grade_perso; ?>
							<img alt="<?php echo $nom_grade_perso; ?>" title="<?php echo $nom_grade_perso; ?>" src="../images/grades/<?php echo $id_grade_perso . ".gif";?>" width=40 height=40></a>
						</td>
					</tr>
					<tr>
						<td align=center><b>Chef : </b><?php echo $nom_perso_chef; ?></td>
						<td align=center><b>Bataillon : </b><?php echo "<a href=\"bataillon.php?id_bataillon=$id_joueur_perso\">" . $bataillon_perso . "</a>"; ?></td>
						<td align=center><b>Compagnie : </b><?php echo "<a href=\"compagnie.php\">" . stripslashes($nom_compagnie_perso) . "</a>"; ?></td>
					</tr>
				</table>
				<!--Fin du tableau d'information-->

				<center>
					<table border=0 align="center" width=100%>
						<tr>
							<td align="center" width=<?php echo $pourc_icone; ?>><a href="profil.php"><img width=88 height=92 border=0 src="../images/<?php echo $image_profil; ?>" alt="profil"></a></td>
							<td align="center" width=<?php echo $pourc_icone; ?>><a href="evenement.php"><img width=88 height=92 border=0 src="../images/<?php echo $image_evenement; ?>" alt="evenement"></a></td>
							<td align="center" width=<?php echo $pourc_icone; ?>><a href="sac.php"><img width=88 height=92 border=0 src="../images/<?php echo $image_sac; ?>" alt="sac"></a></td>
							<td align="center" width=<?php echo $pourc_icone; ?>><a href="carte/carte.php"><img width=88 height=92 border=0 src="../images/carte2.png" alt="mini map"></a></td>
							<?php
							if ($type_perso != 6) {
							?>
							<td align="center" width=<?php echo $pourc_icone; ?>><a href="messagerie.php"><img width=88 height=92 border=0 src="../images/<?php echo $image_messagerie; ?>" alt="messagerie"></a></td>
							<?php
							}
							?>
							<td align="center" width=<?php echo $pourc_icone; ?>><a href="classement.php"><img width=88 height=92 border=0 src="../images/classement2.png" alt="classement"></a></td>
							<?php
							if ($type_perso != 6) {
							?>
							<td align="center" width=<?php echo $pourc_icone; ?>>
								<a href="compagnie.php"><img width=88 height=92 border=0 src="../images/<?php echo $image_compagnie; ?>" alt="compagnie"></a>
							</td>
							<?php
							}
							if ($nb_em) {
							?>
							<td align="center" width=<?php echo $pourc_icone; ?>><a href="etat_major.php"><img width=117 height=89 border=0 src="../images/<?php echo $image_em; ?>" alt="etat major"></a></td>
							<?php
							}
							?>
						</tr>
						<tr>
							<td align="center" width=<?php echo $pourc_icone; ?>><a href="profil.php"><img width=83 height=16 border=0 src="../images/profil_titrev2.png"></a> <?php if($bonus_perso < 0){ echo "<span class='badge badge-pill badge-danger' title='malus de défense dû aux attaques'>$bonus_perso</span>";} ?></td>
							<td align="center" width=<?php echo $pourc_icone; ?>><a href="evenement.php"><img width=83 height=16 border=0 src="../images/evenement_titrev2.png"></a></td>
							<td align="center" width=<?php echo $pourc_icone; ?>><a href="sac.php"><img width=83 height=16 border=0 src="../images/sac_titrev2.png"></a></td>
							<?php
							$sql_mes = "SELECT count(id_message) as nb_mes from message_perso where id_perso='$id_perso' and lu_message='0' AND supprime_message='0'";
							$res_mes = $mysqli->query($sql_mes);
							$t_mes = $res_mes->fetch_assoc();

							$nb_nouveaux_mes = $t_mes["nb_mes"];
							?>
							<td align="center" width=<?php echo $pourc_icone; ?>><a href="carte/carte.php"><img width=83 height=16 border=0 src="../images/carte_titrev2.png"></a></td>
							<?php
							if ($type_perso != 6) {
							?>
							<td align="center" width=<?php echo $pourc_icone; ?>>
								<a href="messagerie.php"><img width=83 height=16 border=0 src="../images/messagerie_titrev2.png"></a>
								<?php
								if($nb_nouveaux_mes) {
									echo "<span class='badge badge-pill badge-danger'>$nb_nouveaux_mes</span>";
								}
								?>
							</td>
							<?php
							}
							?>
							<td align="center" width=<?php echo $pourc_icone; ?>><a href="classement.php"><img width=83 height=16 border=0 src="../images/classement_titrev2.png"></a></td>
							<?php
							if ($type_perso != 6) {
							?>
							<td align="center" width=<?php echo $pourc_icone; ?>>
								<a href="compagnie.php"><img width=83 height=16 border=0 src="../images/compagnie_titrev2.png"></a>
								<?php
								if ($nb_demandes_adhesion_compagnie) {
									echo "<span class='badge badge-pill badge-success'>$nb_demandes_adhesion_compagnie</span>";
								}

								if ($nb_demandes_depart_compagnie) {
									echo "<span class='badge badge-pill badge-danger'>$nb_demandes_depart_compagnie</span>";
								}

								if ($nb_demandes_emprunt_compagnie) {
									echo "<span class='badge badge-pill badge-warning'>$nb_demandes_emprunt_compagnie</span>";
								}
								?>
							</td>
							<?php
							}
							if ($nb_em) {
							?>
							<td align="center" width=<?php echo $pourc_icone; ?>>
								<a href="etat_major.php"><img width=83 height=16 border=0 src="../images/em_titrev2.png" alt="etat major"></a>
								<?php
								if ($nb_compagnie_attente_em) {
									echo "<br/><font color=red><b>$nb_compagnie_attente_em</b> compagnie(s) en attente de validation</font>";
								}
								?>
							</td>
							<?php
							}
							?>
						</tr>
						<tr>
							<td colspan='7' align='center'>&nbsp;</td>
						</tr>
						<tr>
							<td colspan='7' align='center'>Rafraîchir la page : <a href='jouer.php'><img border=0 src='../images/refreshv2.png' alt='refresh' /></a></td>
						</tr>
					</table>
				</center>

				<?php
				echo "<center><font color='red'>".$erreur."</font></center>";
				if (isset($mess) && trim($mess) != "") {
					echo "<center><font color='green'><b>".$mess."</b></font></center>";
				}
				echo "</div><br>";

				// Traitement voir objets à terre
				if(isset($_GET['ramasser']) && $_GET['ramasser'] == "voir"){

					$affichage_objets = true;

					if (isset($_GET['x']) && isset($_GET['y']) && trim($_GET['x']) != "" && trim($_GET['y']) != "") {

						$x = $_GET['x'];
						$y = $_GET['y'];

						$verifx = preg_match("#^[0-9]*[0-9]$#i","$x");
						$verify = preg_match("#^[0-9]*[0-9]$#i","$y");

						// verif si le perso est bien à côté
						$verif_prox = prox_coffre($mysqli, $x, $y, $x_perso, $y_perso);

						if ($verifx && $verify && $verif_prox) {

							$sql = "SELECT type_objet, id_objet, nb_objet FROM objet_in_carte WHERE x_carte='$x' AND y_carte='$y'";
							$res = $mysqli->query($sql);
						}
						else {
							$affichage_objets = false;

							// Tentative de triche !
							$text_triche = "Le perso $id_perso a essayé de jouer avec les paramètres pour voir les objets à ramasser !";

							$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id_perso', '$text_triche')";
							$mysqli->query($sql);
						}
					}
					else {
						$sql = "SELECT type_objet, id_objet, nb_objet FROM objet_in_carte WHERE x_carte='$x_perso' AND y_carte='$y_perso'";
						$res = $mysqli->query($sql);
					}

					if ($affichage_objets) {

						echo "<center>";
						echo "<b>Liste des objets à terre</b>";
						echo "	<table border='1' width='50%'>";
						echo "		<tr>";
						echo "			<th style='text-align:center'>Nom objet</th><th style='text-align:center'>Quantité</th>";
						echo "		</tr>";

						while ($t = $res->fetch_assoc()) {

							$type_objet = $t['type_objet'];
							$id_objet 	= $t['id_objet'];
							$nb_objet	= $t['nb_objet'];

							// Récupération du nom de l'objet
							// Thunes
							if ($type_objet == '1') {
								$nom_objet = "Thune";
								if ($nb_objet > 1) {
									$nom_objet = $nom_objet."s";
								}
							}

							// Objets
							if ($type_objet == '2') {
								$sql_obj = "SELECT nom_objet FROM objet WHERE id_objet='$id_objet'";
								$res_obj = $mysqli->query($sql_obj);
								$t_obj = $res_obj->fetch_assoc();

								$nom_objet = $t_obj['nom_objet'];
							}

							// Armes
							if ($type_objet == '3') {
								$sql_obj = "SELECT nom_arme FROM arme WHERE id_arme='$id_objet'";
								$res_obj = $mysqli->query($sql_obj);
								$t_obj = $res_obj->fetch_assoc();

								$nom_objet = $t_obj['nom_arme'];
							}

							echo "		<tr>";
							echo "			<td align='center'>" . $nom_objet . "</td><td align='center'>" . $nb_objet . "</td>";
							echo "		</tr>";
						}

						echo "	</table>";
						echo "</center>";
					}
				}

				// Récupération de l'arme de CaC équipé sur le perso
				$sql = "SELECT arme.id_arme, nom_arme, porteeMin_arme, porteeMax_arme, coutPa_arme, degatMin_arme, valeur_des_arme, precision_arme, degatZone_arme
						FROM arme, perso_as_arme
						WHERE arme.id_arme = perso_as_arme.id_arme
						AND porteeMax_arme = 1
						AND perso_as_arme.est_portee = '1'
						AND id_perso = '$id_perso' ORDER BY arme.id_arme DESC";
				$res = $mysqli->query($sql);
				$nb_cac = $res->num_rows;

				if ($nb_cac > 1) {
					$i = 1;

					while ($t_cac = $res->fetch_assoc()) {

						if ($i == 1) {
							$id_arme_cac			= $t_cac["id_arme"];
							$nom_arme_cac 			= $t_cac["nom_arme"];
							$porteeMin_arme_cac 	= $t_cac["porteeMin_arme"];
							$porteeMax_arme_cac 	= $t_cac["porteeMax_arme"];
							$coutPa_arme_cac 		= $t_cac["coutPa_arme"];
							$degatMin_arme_cac 		= $t_cac["degatMin_arme"];
							$valeur_des_arme_cac 	= $t_cac["valeur_des_arme"];
							$precision_arme_cac 	= $t_cac["precision_arme"];
							$degatZone_arme_cac 	= $t_cac["degatZone_arme"];

							$degats_arme_cac = $degatMin_arme_cac."D".$valeur_des_arme_cac;
						}
						else {
							$id_arme_cac2			= $t_cac["id_arme"];
							$nom_arme_cac2 			= $t_cac["nom_arme"];
							$porteeMin_arme_cac2 	= $t_cac["porteeMin_arme"];
							$porteeMax_arme_cac2 	= $t_cac["porteeMax_arme"];
							$coutPa_arme_cac2		= $t_cac["coutPa_arme"];
							$degatMin_arme_cac2 	= $t_cac["degatMin_arme"];
							$valeur_des_arme_cac2 	= $t_cac["valeur_des_arme"];
							$precision_arme_cac2 	= $t_cac["precision_arme"];
							$degatZone_arme_cac2 	= $t_cac["degatZone_arme"];

							$degats_arme_cac2 = $degatMin_arme_cac2."D".$valeur_des_arme_cac2;
						}

						$i++;
					}
				}
				else {
					$t_cac = $res->fetch_assoc();

					if ($t_cac != NULL) {
						$id_arme_cac			= $t_cac["id_arme"];
						$nom_arme_cac 			= $t_cac["nom_arme"];
						$porteeMin_arme_cac 	= $t_cac["porteeMin_arme"];
						$porteeMax_arme_cac 	= $t_cac["porteeMax_arme"];
						$coutPa_arme_cac 		= $t_cac["coutPa_arme"];
						$degatMin_arme_cac 		= $t_cac["degatMin_arme"];
						$valeur_des_arme_cac 	= $t_cac["valeur_des_arme"];
						$precision_arme_cac 	= $t_cac["precision_arme"];
						$degatZone_arme_cac 	= $t_cac["degatZone_arme"];
					} else {
						$id_arme_cac			= 1000;
						$nom_arme_cac 			= "Poings";
						$porteeMin_arme_cac 	= 1;
						$porteeMax_arme_cac 	= 1;
						$coutPa_arme_cac 		= 3;
						$degatMin_arme_cac 		= 4;
						$valeur_des_arme_cac 	= 6;
						$precision_arme_cac 	= 30;
						$degatZone_arme_cac 	= 0;
					}

					$degats_arme_cac = $degatMin_arme_cac."D".$valeur_des_arme_cac;
				}

				// Récupération de la liste des persos à portée d'attaque arme CaC
				$perc_att = $perc;
				if ($perc_att <= 0) {
					$perc_att = 1;
				}
				$res_portee_cac = resource_liste_cibles_a_portee_attaque($mysqli, 'carte', $id_perso, $porteeMin_arme_cac, $porteeMax_arme_cac, $perc_att, 'cac');

				// Récupération de l'arme à distance sur le perso
				$sql = "SELECT arme.id_arme, nom_arme, porteeMin_arme, porteeMax_arme, coutPa_arme, degatMin_arme, valeur_des_arme, precision_arme, degatZone_arme
						FROM arme, perso_as_arme
						WHERE arme.id_arme = perso_as_arme.id_arme
						AND porteeMax_arme > 1
						AND perso_as_arme.est_portee = '1'
						AND id_perso = '$id_perso'";
				$res = $mysqli->query($sql);
				$t_dist = $res->fetch_assoc();

				if ($t_dist != NULL) {
					$id_arme_dist 			= $t_dist["id_arme"];
					$nom_arme_dist 			= $t_dist["nom_arme"];
					$porteeMin_arme_dist 	= $t_dist["porteeMin_arme"];
					$porteeMax_arme_dist 	= $t_dist["porteeMax_arme"];
					$coutPa_arme_dist 		= $t_dist["coutPa_arme"];
					$degatMin_arme_dist 	= $t_dist["degatMin_arme"];
					$valeur_des_arme_dist 	= $t_dist["valeur_des_arme"];
					$precision_arme_dist 	= $t_dist["precision_arme"];
					$degatZone_arme_dist 	= $t_dist["degatZone_arme"];
				} else {
					$id_arme_dist			= 2000;
					$nom_arme_dist 			= "Cailloux";
					$porteeMin_arme_dist 	= 1;
					$porteeMax_arme_dist 	= 2;
					$coutPa_arme_dist 		= 3;
					$degatMin_arme_dist 	= 5;
					$valeur_des_arme_dist 	= 6;
					$precision_arme_dist 	= 25;
					$degatZone_arme_dist 	= 0;
				}

				$degats_arme_dist = $degatMin_arme_dist."D".$valeur_des_arme_dist;

				// Récupération de la liste des persos à portée d'attaque arme dist
				$res_portee_dist = resource_liste_cibles_a_portee_attaque($mysqli, 'carte', $id_perso, $porteeMin_arme_dist, $porteeMax_arme_dist, $perc_att, 'dist');

				// background='../images/background_html.jpg'
				?>
				<table border=0 align="center" cellspacing="0" cellpadding="10" >
					<tr>
						<td valign="top">

							<table style="border:0px; background-color: cornflowerblue; min-width: 375px;" width="100%">
								<tr>
									<td align='right'><b>PV</b></td>
									<td align='center'><?php $pourc = affiche_jauge($pv_perso, $pvMax_perso); echo "".round($pourc)."% ou $pv_perso/$pvMax_perso"; ?></td>
								</tr>
							</table>

							<table style="border:0px; background-color: cornflowerblue; min-width: 375px;" width="100%">
								<tr style="width: 100%;">
									<td style="width: 40%;">
										<table border="2" bordercolor="white" style="width: 100%;"> <!-- border-collapse:collapse -->
											<tr>
												<td><b>XP</b></td>
												<td><?php echo $xp_perso; ?>&nbsp;</td>
											</tr>
											<tr>
												<td><b>XPI</b></td>
												<td><?php echo $pi_perso; ?>&nbsp;</td>
											</tr>
											<tr>
												<td><b>PC</b></td>
												<td><?php echo $pc_perso; ?>&nbsp;</td>
											</tr>
										</table>
									</td>

									<td style="width: 30%;">
										<table border="2" bordercolor="white" style="width: 100%;">
											<tr>
												<td><b>Perception</b></td>
												<td align='center'>
												<?php

												$texte_tooltip = "Base : ".$perception_perso."";
												if($bonusPerception_perso != 0) {
													if ($bonusPerception_perso < 0) {
														$texte_tooltip .= " <b>(";
													} else {
														$texte_tooltip .= " <b>(+";
													}
													$texte_tooltip .= $bonusPerception_perso . ")</b>";
												}

												$perception_final_perso = $perception_perso + $bonusPerception_perso;

												echo '<a tabindex="0" href="#" data-toggle="popover" data-trigger="focus" data-placement="top" data-html="true" data-content="'.$texte_tooltip.'">'.$perception_final_perso.'</a>';

												?>&nbsp;</td>
											</tr>
											<tr>
												<td><b>PA</b></td>
												<td align='center' nowrap="nowrap">
												<?php

												$texte_tooltip = "Base max : ".$paMax_perso."";
												if ($bonusPA_perso != 0) {
													if ($bonusPA_perso < 0) {
														$texte_tooltip .= " <b>(";
													} else {
														$texte_tooltip .= " <b>(+";
													}
													$texte_tooltip .= $bonusPA_perso . ")</b>";
												}

												$paMax_final_perso = $paMax_perso + $bonusPA_perso;

												echo $pa_perso . ' / <a tabindex="0" href="#" data-toggle="popover" data-trigger="focus" data-placement="top" data-html="true" data-content="'.$texte_tooltip.'">'. $paMax_final_perso.'</a>';

												?>&nbsp;</td>
											</tr>
											<tr>
												<td><b>PM</b></td>
												<td align='center' nowrap="nowrap"><?php

												$texte_tooltip_pm = "Base : ".$pm_perso_tmp;

												if ($malus_pm_charge != 0) {
													$texte_tooltip_pm .= " <b>(";
													$texte_tooltip_pm .= "charge : ";
													$texte_tooltip_pm .= $malus_pm_charge;
													$texte_tooltip_pm .= ")</b>";
												}

												$texte_tooltip_pmMax = "Base max : ".$pmMax_perso_tmp."";

												if ($bonusPM_perso != 0) {
													$texte_tooltip_pmMax .= " <b>(";

													if ($bonusPM_perso != 0) {

														$texte_tooltip_pmMax .= "objets : ";

														if ($bonusPM_perso < 0) {
															$texte_tooltip_pmMax .= $bonusPM_perso;
														}
														else {
															$texte_tooltip_pmMax .= "+".$bonusPM_perso;
														}
													}

													$texte_tooltip_pmMax .= ")</b>";
												}
												echo '<a tabindex="0" href="#" data-toggle="popover" data-trigger="focus" data-placement="top" data-html="true" data-content="'.$texte_tooltip_pm.'">' . $pm_perso  . '</a> / <a tabindex="0" href="#" data-toggle="popover" data-trigger="focus" data-placement="top" data-html="true" data-content="'.$texte_tooltip_pmMax.'">' . $pmMax_perso . '</a>';
												?>&nbsp;</td>
											</tr>
										</table>
									</td>

									<td style="width: 30%;">
										<table border="2" bordercolor="white" style="width: 100%;">
											<tr>
												<td><b>Protection</b></td>
												<td align='center'><?php echo $protec_perso; ?>&nbsp;</td>
											</tr>
											<tr>
												<td><b>Récuperation</b></td>
												<td align='center' nowrap="nowrap">
												<?php
												$texte_tooltip = "Base : ".$recup_perso."";

												if($bonusRecup_perso != 0) {
													if ($bonusRecup_perso < 0) {
														$texte_tooltip .= " <b>(";
													} else {
														$texte_tooltip .= " <b>(+";
													}
													$texte_tooltip .= $bonusRecup_perso . ")</b>";
												}

												$recup_final = $recup_perso + $bonusRecup_perso;

												echo '<a tabindex="0" href="#" data-toggle="popover" data-trigger="focus" data-placement="top" data-html="true" data-content="'.$texte_tooltip.'">'.$recup_final.'</a>';

												?>&nbsp;</td>
											</tr>
											<tr>
												<td nowrap="nowrap"><b>Défense</b></td>
												<td align='center' nowrap="nowrap"><?php

												$texte_tooltip = "Base : ".$bonus_perso."";

												$bonus_defense = getBonusDefenseObjet($mysqli, $id_perso);

												$bonus_defense_bat = get_bonus_defense_instance_bat($mysqli, $id_perso);

												// recuperation des données de la carte
												$sql = "SELECT fond_carte FROM $carte
														WHERE x_carte = $x_perso
														AND y_carte = $y_perso";
												$res = $mysqli->query($sql);
												$tab = $res->fetch_assoc();

												$fond_carte_perso = $tab['fond_carte'];

												$bonus_defense_terrain_cac = get_bonus_defense_terrain($fond_carte_perso, 1);
												$bonus_defense_terrain_dist = get_bonus_defense_terrain($fond_carte_perso, 2);

												$bonus_final_cac = $bonus_perso + $bonus_defense + $bonus_defense_terrain_cac + $bonus_defense_bat;
												$bonus_final_dist = $bonus_perso + $bonus_defense + $bonus_defense_terrain_dist + $bonus_defense_bat;

												echo '<a tabindex="0" href="#" data-toggle="popover" data-trigger="focus" data-placement="top" data-html="true" data-content="'.$texte_tooltip.'">';
												if ($bonus_final_cac == $bonus_final_dist) {
													echo $bonus_final_cac;
												}
												else {
													echo 'Cac : '.$bonus_final_cac.' - Dist : '.$bonus_final_dist.'</a>';
												}
												?>&nbsp;</td>
											</tr>
										</table>
									</td>
								</tr>
							</table>

							<br />

							<table border="2" style="background-color: palevioletred;" width="100%">
								<tr>
									<td colspan='3' bgcolor="lightgrey" align='center'><b>Caractéristiques de combat</b></td>
								</tr>
								<tr>
									<td width='20%'></td>
									<?php
									if ($type_perso != 5 && $type_perso != 8) {
									?>
									<td width='40%' align='center'><b>Rapproché</b></td>
									<?php
									}

									if ($type_perso != 6 && $type_perso != 4) {
									?>
									<td width='40%' align='center' nowrap="nowrap"><b>A distance</b></td>
									<?php
									}
									else if ($type_perso == 4) {
									?>
									<td width='40%' align='center'><b>Rapproché</b></td>
									<?php
									}
									?>
								</tr>
								<tr>
									<td><b>Armes</b></td>
									<?php
									if ($type_perso != 5 && $type_perso != 8) {
									?>
									<td align='center' nowrap="nowrap"><?php echo $nom_arme_cac; ?></td>
									<?php
									}

									if ($type_perso != 6 && $type_perso != 4) {
									?>
									<td align='center' nowrap="nowrap"><?php echo $nom_arme_dist; ?></td>
									<?php
									}
									else if ($type_perso == 4) {
									?>
									<td align='center' nowrap="nowrap"><?php echo $nom_arme_cac2; ?></td>
									<?php
									}
									?>
								</tr>
								<tr>
									<td nowrap="nowrap"><b>Coût en PA</b></td>
									<?php
									if ($type_perso != 5 && $type_perso != 8) {
									?>
									<td align='center'><?php echo $coutPa_arme_cac; ?></td>
									<?php
									}

									if ($type_perso != 6 && $type_perso != 4) {
									?>
									<td align='center'><?php echo $coutPa_arme_dist; if (possede_lunette_visee($mysqli, $id_perso)) { echo " (+2)"; } ?></td>
									<?php
									}
									else if ($type_perso == 4) {
									?>
									<td align='center' nowrap="nowrap"><?php echo $coutPa_arme_cac2; ?></td>
									<?php
									}
									?>
								</tr>
								<tr>
									<?php
									if ($type_perso != 4) {
									?>
									<td><b>Dégâts</b></td>
									<?php
									} else {
									?>
									<td><b>Soins</b></td>
									<?php
									}
									?>
									<?php
									if ($type_perso != 5 && $type_perso != 8) {
									?>
									<td align='center'><?php echo $degats_arme_cac; ?></td>
									<?php
									}

									if ($type_perso != 6 && $type_perso != 4) {
									?>
									<td align='center'><?php echo $degats_arme_dist; ?></td>
									<?php
									}
									else if ($type_perso == 4) {
									?>
									<td align='center' nowrap="nowrap"><?php echo $degats_arme_cac2; ?></td>
									<?php
									}
									?>
								</tr>
								<tr>
									<td><b>Portée</b></td>
									<?php
									if ($type_perso != 5 && $type_perso != 8) {
									?>
									<td align='center'><?php echo $porteeMax_arme_cac; ?></td>
									<?php
									}

									if ($type_perso != 6 && $type_perso != 4) {
									?>
									<td align='center'><?php if(!in_bat($mysqli, $id_perso)) echo $porteeMax_arme_dist + get_bonus_portee($fond); else echo $porteeMax_arme_dist?></td>
									<?php
									}
									else if ($type_perso == 4) {
									?>
									<td align='center' nowrap="nowrap"><?php echo $porteeMax_arme_cac2; ?></td>
									<?php
									}
									?>
								</tr>
								<tr>
									<td><b>Précision</b></td>
									<?php
									if ($type_perso != 5 && $type_perso != 8) {
									?>
									<td align='center'><?php echo $precision_arme_cac . "%"; ?></td>
									<?php
									}

									if ($type_perso != 6 && $type_perso != 4) {
									?>
									<td align='center'>
										<?php
										echo $precision_arme_dist . "%";

										$bonus_precision_objet = getBonusPrecisionDistObjet($mysqli, $id_perso);

										if ($bonus_precision_objet != 0) {
											echo " (+".$bonus_precision_objet."%)";
										}

										?>
									</td>
									<?php
									}
									else if ($type_perso == 4) {
									?>
									<td align='center' nowrap="nowrap"><?php echo $precision_arme_cac2 . "%"; ?></td>
									<?php
									}
									?>
								</tr>
								<?php
								if (($type_perso == 5 || $type_perso == 8) && $degatZone_arme_dist) {
								?>
								<tr>
									<td><b>Spécial</b></td>
									<td colspan='2'>
										<center>Dégâts de zone
										<?php
										if ($id_arme_dist == 13) {
											echo "<br>Bonus de dégâts sur bâtiments";
										}
										?>
										</center>
									</td>
								</tr>
								<?php
								}
								?>
								<tr>
									<form method="post" action="agir.php" target='_main'>
									<?php
									if ($type_perso != 4) {
									?>
									<td><input type="submit" value="Attaquer"></td>
									<?php
									} else {
									?>
									<td><input type="submit" value="Soigner"></td>
									<?php
									}
									if ($type_perso != 5 && $type_perso != 8) {
									?>
									<td>
										<select name='id_attaque_cac' style="width: -moz-available;">
											<option value="personne">Personne</option>
											<?php
											// Soigneur
											if ($type_perso == 4) {

												while($t_cible_portee_cac = $res_portee_cac->fetch_assoc()) {

													$id_cible_cac = $t_cible_portee_cac["idPerso_carte"];

													if ($id_cible_cac < 50000) {

														// Un autre perso
														$sql = "SELECT nom_perso, pv_perso, pvMax_perso, bonus_perso, clan FROM perso WHERE id_perso='$id_cible_cac'";
														$res = $mysqli->query($sql);
														$tab = $res->fetch_assoc();

														$nom_cible_cac 		= $tab["nom_perso"];
														$pv_cible_cac		= $tab["pv_perso"];
														$pv_max_cible_cac	= $tab["pvMax_perso"];
														$bonus_cible_cac	= $tab["bonus_perso"];
														$camp_cible_cac		= $tab["clan"];

														$couleur_clan_cible = couleur_clan($camp_cible_cac);

														if ($id_arme_cac == 10) {
															// seringue
															// On affiche que les persos blessés
															if ($pv_cible_cac < $pv_max_cible_cac) {
																echo "<option style=\"color:". $couleur_clan_cible ."\" value='".$id_cible_cac.",".$id_arme_cac."'>".$nom_cible_cac." (mat. ".$id_cible_cac.")</option>";
															}
														} else if ($id_arme_cac == 11) {
															// bandage
															// On affiche que les persos avec malus
															if ($bonus_cible_cac < 0) {
																echo "<option style=\"color:". $couleur_clan_cible ."\" value='".$id_cible_cac.",".$id_arme_cac."'>".$nom_cible_cac." (mat. ".$id_cible_cac.")</option>";
															}
														}
													} else if ($id_cible_cac >= 200000) {

														// Un PNJ
														$sql = "SELECT nom_pnj, pv_i, pvMax_pnj FROM pnj, instance_pnj WHERE pnj.id_pnj = instance_pnj.id_pnj AND idInstance_pnj = '$id_cible_cac'";
														$res = $mysqli->query($sql);
														$tab = $res->fetch_assoc();

														$nom_cible_cac 		= $tab["nom_pnj"];
														$pv_cible_cac		= $tab["pv_i"];
														$pv_max_cible_cac	= $tab["pvMax_pnj"];

														if ($pv_cible_cac < $pv_max_cible_cac) {
															echo "<option style=\"color:grey\" value='".$id_cible_cac.",".$id_arme_cac."'>".$nom_cible_cac." (mat. ".$id_cible_cac.")</option>";
														}
													} else {
														// Un Batiment => on ne veut pas l'afficher !
													}
												}
											}
											else {
												// Impossible d'attaquer au CaC quand on est dans un train
												if (!in_train($mysqli, $id_perso)) {

													while($t_cible_portee_cac = $res_portee_cac->fetch_assoc()) {

														$id_cible_cac = $t_cible_portee_cac["idPerso_carte"];

														if ($id_cible_cac < 50000) {

															// Un autre perso
															$sql = "SELECT nom_perso, clan FROM perso WHERE id_perso='$id_cible_cac'";
															$res = $mysqli->query($sql);
															$tab = $res->fetch_assoc();

															$nom_cible_cac 	= $tab["nom_perso"];
															$camp_cible_cac	= $tab["clan"];

															$couleur_clan_cible = couleur_clan($camp_cible_cac);

														} else if ($id_cible_cac >= 200000) {

															// Un PNJ
															$sql = "SELECT nom_pnj FROM pnj, instance_pnj WHERE pnj.id_pnj = instance_pnj.id_pnj AND idInstance_pnj = '$id_cible_cac'";
															$res = $mysqli->query($sql);
															$tab = $res->fetch_assoc();

															$nom_cible_cac = $tab["nom_pnj"];

															$couleur_clan_cible = "grey";

														} else {

															// Un Batiment
															$sql = "SELECT nom_batiment, nom_instance, camp_instance, pv_instance FROM batiment, instance_batiment WHERE batiment.id_batiment = instance_batiment.id_batiment AND id_instanceBat = '$id_cible_cac'";
															$res = $mysqli->query($sql);
															$tab = $res->fetch_assoc();

															$nom_cible_cac = $tab["nom_batiment"];
															if ($tab["nom_instance"] != "") {
																$nom_cible_cac .= " ".$tab["nom_instance"];
															}

															$camp_cible_cac	= $tab["camp_instance"];

															$couleur_clan_cible = couleur_clan($camp_cible_cac);
															$pv_instance	= $tab["pv_instance"];
															if ($pv_instance <= 0)
																continue;
														}

														echo "<option style=\"color:". $couleur_clan_cible ."\" value='".$id_cible_cac.",".$id_arme_cac."'>".$nom_cible_cac." (mat. ".$id_cible_cac.")</option>";
													}
												}
											}
											?>
										</select>
									</td>
									<?php
									}

									if ($type_perso != 6 && $type_perso != 4) {
									?>
									<td>
										<select name='id_attaque_dist' style="width: -moz-available;">
											<option value="personne">Personne</option>
											<?php
											if (!isset($id_bat_perso) || (isset($id_bat_perso) && $id_bat_perso != 10)) {
												while($t_cible_portee_dist = $res_portee_dist->fetch_assoc()) {

													$id_cible_dist = $t_cible_portee_dist["idPerso_carte"];
													$id_instance_in_bat = in_bat($mysqli,$id_perso);

													if ($id_cible_dist != $id_instance_in_bat) {

														if ($id_cible_dist < 50000) {

															// Un autre perso
															$sql = "SELECT nom_perso, clan FROM perso WHERE id_perso='$id_cible_dist'";
															$res = $mysqli->query($sql);
															$tab = $res->fetch_assoc();

															$nom_cible_dist = $tab["nom_perso"];
															$camp_cible_cac	= $tab["clan"];

															$couleur_clan_cible = couleur_clan($camp_cible_cac);

														} else if ($id_cible_dist >= 200000) {

															// Un PNJ
															$sql = "SELECT nom_pnj FROM pnj, instance_pnj WHERE pnj.id_pnj = instance_pnj.id_pnj AND idInstance_pnj = '$id_cible_dist'";
															$res = $mysqli->query($sql);
															$tab = $res->fetch_assoc();

															$nom_cible_dist = $tab["nom_pnj"];

															$couleur_clan_cible = "grey";

														} else {

															// Un Batiment
															$sql = "SELECT nom_batiment, nom_instance, camp_instance, pv_instance FROM batiment, instance_batiment WHERE batiment.id_batiment = instance_batiment.id_batiment AND id_instanceBat = '$id_cible_dist'";
															$res = $mysqli->query($sql);
															$tab = $res->fetch_assoc();

															$nom_cible_dist = $tab["nom_batiment"];
															if ($tab["nom_instance"] != "") {
																$nom_cible_dist .= " ".$tab["nom_instance"];
															}

															$camp_cible_dist	= $tab["camp_instance"];

															$couleur_clan_cible = couleur_clan($camp_cible_dist);
															$pv_instance	= $tab["pv_instance"];
															if ($pv_instance <= 0)
																continue;
														}

														echo "<option style=\"color:". $couleur_clan_cible ."\" value='".$id_cible_dist.",".$id_arme_dist."'>".$nom_cible_dist." (mat. ".$id_cible_dist.")</option>";
													}
												}
											}
											?>
										</select>
									</td>
									<?php
									}
									else if ($nb_cac > 1 && $type_perso == '4') {
										$res_portee_cac2 = resource_liste_cibles_a_portee_attaque($mysqli, 'carte', $id_perso, $porteeMin_arme_cac, $porteeMax_arme_cac, $perc_att, 'cac');
									?>
									<td>
										<select name='id_attaque_cac2' style="width: -moz-available;">
											<option value="personne">Personne</option>
											<?php
											// Soigneur
											if ($type_perso == 4) {

												while($t_cible_portee_cac = $res_portee_cac2->fetch_assoc()) {

													$id_cible_cac = $t_cible_portee_cac["idPerso_carte"];

													if ($id_cible_cac < 50000) {

														// Un autre perso
														$sql = "SELECT nom_perso, pv_perso, pvMax_perso, bonus_perso, clan FROM perso WHERE id_perso='$id_cible_cac'";
														$res = $mysqli->query($sql);
														$tab = $res->fetch_assoc();

														$nom_cible_cac 		= $tab["nom_perso"];
														$pv_cible_cac		= $tab["pv_perso"];
														$pv_max_cible_cac	= $tab["pvMax_perso"];
														$bonus_cible_cac	= $tab["bonus_perso"];
														$camp_cible_cac		= $tab["clan"];

														$couleur_clan_cible = couleur_clan($camp_cible_cac);

														if ($id_arme_cac2 == 10) {
															// seringue
															// On affiche que les persos blessés
															if ($pv_cible_cac < $pv_max_cible_cac) {
																echo "<option style=\"color:". $couleur_clan_cible ."\" value='".$id_cible_cac.",".$id_arme_cac2."'>".$nom_cible_cac." (mat. ".$id_cible_cac.")</option>";
															}
														} else if ($id_arme_cac2 == 11) {
															// bandage
															// On affiche que les persos avec malus
															if ($bonus_cible_cac < 0) {
																echo "<option style=\"color:". $couleur_clan_cible ."\" value='".$id_cible_cac.",".$id_arme_cac2."'>".$nom_cible_cac." (mat. ".$id_cible_cac.")</option>";
															}
														}
													} else if ($id_cible_cac >= 200000) {

														// Un PNJ
														$sql = "SELECT nom_pnj, pv_i, pvMax_pnj FROM pnj, instance_pnj WHERE pnj.id_pnj = instance_pnj.id_pnj AND idInstance_pnj = '$id_cible_cac'";
														$res = $mysqli->query($sql);
														$tab = $res->fetch_assoc();

														$nom_cible_cac 		= $tab["nom_pnj"];
														$pv_cible_cac		= $tab["pv_i"];
														$pv_max_cible_cac	= $tab["pvMax_pnj"];

														if ($pv_cible_cac < $pv_max_cible_cac) {
															echo "<option style=\"color:grey\" value='".$id_cible_cac.",".$id_arme_cac2."'>".$nom_cible_cac." (mat. ".$id_cible_cac.")</option>";
														}
													} else {
														// Un Batiment => on ne veut pas l'afficher !
													}
												}
											}
											else {
												// Impossible d'attaquer au CaC quand on est dans un train
												if (!in_train($mysqli, $id_perso)) {

													while($t_cible_portee_cac = $res_portee_cac->fetch_assoc()) {

														$id_cible_cac = $t_cible_portee_cac["idPerso_carte"];

														if ($id_cible_cac < 50000) {

															// Un autre perso
															$sql = "SELECT nom_perso, clan FROM perso WHERE id_perso='$id_cible_cac'";
															$res = $mysqli->query($sql);
															$tab = $res->fetch_assoc();

															$nom_cible_cac 	= $tab["nom_perso"];
															$camp_cible_cac	= $tab["clan"];

															$couleur_clan_cible = couleur_clan($camp_cible_cac);

														} else if ($id_cible_cac >= 200000) {

															// Un PNJ
															$sql = "SELECT nom_pnj FROM pnj, instance_pnj WHERE pnj.id_pnj = instance_pnj.id_pnj AND idInstance_pnj = '$id_cible_cac'";
															$res = $mysqli->query($sql);
															$tab = $res->fetch_assoc();

															$nom_cible_cac = $tab["nom_pnj"];

															$couleur_clan_cible = "grey";

														} else {

															// Un Batiment
															$sql = "SELECT nom_batiment FROM batiment, instance_batiment, pv_instance WHERE batiment.id_batiment = instance_batiment.id_batiment AND id_instanceBat = '$id_cible_cac'";
															$res = $mysqli->query($sql);
															$tab = $res->fetch_assoc();

															$nom_cible_cac = $tab["nom_batiment"];

															$couleur_clan_cible = "black";
															$pv_instance	= $tab["pv_instance"];
															if ($pv_instance <= 0)
																continue;
														}

														echo "<option style=\"color:". $couleur_clan_cible ."\" value='".$id_cible_cac.",".$id_arme_cac."'>".$nom_cible_cac." (mat. ".$id_cible_cac.")</option>";
													}
												}
											}
											?>
										</select>
									</td>
									<?php
									}
									?>
									</form>
								</tr>
							</table>

							<br />

							<?php
							if ($afficher_rosace) {
							?>
							<table border='2' width="100%">
								<tr>
									<td background='../images/background.jpg'>
										<!--Création du tableau du choix du deplacement-->
										<table border=0 align='center'>
											<tr>
												<td colspan='5' align='center'>
												<img src='../images/Se_Deplacer.png' />
												</td>
											</tr>
											<form action="jouer.php" method="post">
											<tr>
												<td rowspan='3'><img src='../images/tribal1.png' /></td>
												<?php
												if (in_train($mysqli, $id_perso)) {
													$id_train = in_train($mysqli, $id_perso);
												}

												if(in_bat($mysqli, $id_perso)){
												?>
													<td><a href="jouer.php?bat=<?php echo $id_bat; ?>&bat2=<?php echo $bat; ?>&out=ok&direction=1"><img border=0 src="../fond_carte/fleche1.png"></a></td>
													<td><a href="jouer.php?bat=<?php echo $id_bat; ?>&bat2=<?php echo $bat; ?>&out=ok&direction=2"><img border=0 src="../fond_carte/fleche2.png"></a></td>
													<td><a href="jouer.php?bat=<?php echo $id_bat; ?>&bat2=<?php echo $bat; ?>&out=ok&direction=3"><img border=0 src="../fond_carte/fleche3.png"></a></td>
												<?php
												}
												else if (isset($id_train) && $id_train > 0) {
												?>
													<td><a href="jouer.php?train=<?php echo $id_train; ?>&out=ok&direction=1"><img border=0 src="../fond_carte/fleche1.png"></a></td>
													<td><a href="jouer.php?train=<?php echo $id_train; ?>&out=ok&direction=2"><img border=0 src="../fond_carte/fleche2.png"></a></td>
													<td><a href="jouer.php?train=<?php echo $id_train; ?>&out=ok&direction=3"><img border=0 src="../fond_carte/fleche3.png"></a></td>
												<?php
												}
												else {
												?>
													<td><a href="jouer.php?mouv=1"><img border=0 src="../fond_carte/fleche1.png"></a></td>
													<td><a href="jouer.php?mouv=2"><img border=0 src="../fond_carte/fleche2.png"></a></td>
													<td><a href="jouer.php?mouv=3"><img border=0 src="../fond_carte/fleche3.png"></a></td>
												<?php
												}
												?>
												<td rowspan='3'><img src='../images/tribal2.png' /></td>
											</tr>
											<tr>
												<?php
												if(in_bat($mysqli, $id_perso)){
												?>
													<td><a href="jouer.php?bat=<?php echo $id_bat; ?>&bat2=<?php echo $bat; ?>&out=ok&direction=4"><img border=0 src="../fond_carte/fleche4.png"></a></td>
													<td><center><b>Sortir</b></center></td>
													<td><a href="jouer.php?bat=<?php echo $id_bat; ?>&bat2=<?php echo $bat; ?>&out=ok&direction=5"><img border=0 src="../fond_carte/fleche5.png"></a></td>
												<?php
												}
												else if (isset($id_train) && $id_train > 0) {
												?>
													<td><a href="jouer.php?train=<?php echo $id_train; ?>&out=ok&direction=4"><img border=0 src="../fond_carte/fleche4.png"></a></td>
													<td><center><b>Sauter</b></center></td>
													<td><a href="jouer.php?train=<?php echo $id_train; ?>&out=ok&direction=5"><img border=0 src="../fond_carte/fleche5.png"></a></td>
												<?php
												}
												else {
												?>
												<td><a href="jouer.php?mouv=4"><img border=0 src="../fond_carte/fleche4.png"></a></td>
												<td>&nbsp; </td>
												<td><a href="jouer.php?mouv=5"><img border=0 src="../fond_carte/fleche5.png"></a></td>
												<?php
												}
												?>
											</tr>
											<tr>
												<?php
												if(in_bat($mysqli, $id_perso)){
												?>
													<td><a href="jouer.php?bat=<?php echo $id_bat; ?>&bat2=<?php echo $bat; ?>&out=ok&direction=6"><img border=0 src="../fond_carte/fleche6.png"></a></td>
													<td><a href="jouer.php?bat=<?php echo $id_bat; ?>&bat2=<?php echo $bat; ?>&out=ok&direction=7"><img border=0 src="../fond_carte/fleche7.png"></a></td>
													<td><a href="jouer.php?bat=<?php echo $id_bat; ?>&bat2=<?php echo $bat; ?>&out=ok&direction=8"><img border=0 src="../fond_carte/fleche8.png"></a></td>
												<?php
												}
												else if (isset($id_train) && $id_train > 0) {
												?>
													<td><a href="jouer.php?train=<?php echo $id_train; ?>&out=ok&direction=6"><img border=0 src="../fond_carte/fleche6.png"></a></td>
													<td><a href="jouer.php?train=<?php echo $id_train; ?>&out=ok&direction=7"><img border=0 src="../fond_carte/fleche7.png"></a></td>
													<td><a href="jouer.php?train=<?php echo $id_train; ?>&out=ok&direction=8"><img border=0 src="../fond_carte/fleche8.png"></a></td>
												<?php
												}
												else {
												?>
													<td><a href="jouer.php?mouv=6"><img border=0 src="../fond_carte/fleche6.png"></a></td>
													<td><a href="jouer.php?mouv=7"><img border=0 src="../fond_carte/fleche7.png"></a></td>
													<td><a href="jouer.php?mouv=8"><img border=0 src="../fond_carte/fleche8.png"></a></td>
												<?php
												}
												?>
											</tr>
											</form>
										</table>
										<!--Fin du tableau du choix du deplacement-->
									</td>
								</tr>
							</table>
							<?php
							}
							?>
						</td>

						<td valign="top">
							<table style="border:1px solid black; border-collapse: collapse;">
								<tr>
									<td>

				<?php
				//<!--Génération de la carte-->
				$perc_carte = $perc;
				if ($perc_carte < 0) {
					$perc_carte = 0;
				}

				// recuperation des données de la carte
				$sql = "SELECT x_carte, y_carte, fond_carte, occupee_carte, image_carte, idPerso_carte FROM $carte
						WHERE x_carte >= $x_perso - $perc_carte
						AND x_carte <= $x_perso + $perc_carte
						AND y_carte <= $y_perso + $perc_carte
						AND y_carte >= $y_perso - $perc_carte
						ORDER BY y_carte DESC, x_carte";
				$res = $mysqli->query($sql);
				$tab = $res->fetch_assoc();

				// calcul taille table
				$taille_table = ($perception_perso + $bonusPerception_perso) * 2 + 2;
				$taille_table = $taille_table * 40;

				echo "<table border='".$cadrillage."' width='".$taille_table."' height='".$taille_table."' align='center' cellspacing='0' cellpadding='0' style='text-align: center;' >";

				//affichage des abscisses
				echo "	<tr>
							<td width='40' heigth='40' background=\"../images/background.jpg\" align='center'>y \ x</td>";

				for ($i = $x_perso - $perc_carte; $i <= $x_perso + $perc_carte; $i++) {
					if ($i == $x_perso)
						echo "<th style='min-width:40px;' height='40' background=\"../images/background3.jpg\">$i</th>";
					else
						echo "<th style='min-width:40px;' height='40' background=\"../images/background.jpg\">$i</th>";
				}

				echo "	</tr>";

				for ($y = $y_perso + $perc_carte; $y >= $y_perso - $perc_carte; $y--) {

					echo "<tr align=\"center\" >";

					if ($y == $y_perso) {
						echo "<th style='min-width:40px;' height='40' background=\"../images/background3.jpg\">$y</th>";
					}
					else {
						echo "<th style='min-width:40px;' height='40' background=\"../images/background.jpg\">$y</th>";
					}

					for ($x = $x_perso - $perc_carte; $x <= $x_perso + $perc_carte; $x++) {

						//les coordonnées sont dans les limites
						if ($x >= X_MIN && $y >= Y_MIN && $x <= $X_MAX && $y <= $Y_MAX) {

							//--------------------------
							//coordonnées du perso
							if ($x == $x_perso && $y == $y_perso){

								// verification s'il y a un objet sur cette case
								$sql_o = "SELECT id_objet FROM objet_in_carte WHERE x_carte='$x' AND y_carte='$y'";
								$res_o = $mysqli->query($sql_o);
								$nb_o = $res_o->num_rows;

								if($clan_perso == '1'){
									$image_profil 	= "Nord.gif";
								}
								if($clan_perso == '2'){
									$image_profil 	= "Sud.gif";
								}

								$fond_im = $tab["fond_carte"];
								$nom_terrain = get_nom_terrain($fond_im);

								echo "<td width=40 height=40 background=\"../fond_carte/".$fond_im."\">";
								echo "	<div width=40 height=40 style=\"position: relative;\">";
								echo "		<div tabindex='0' style=\"position: absolute;bottom: -2px;text-align: center; width: 100%;font-weight: bold;\"
													data-toggle='popover'
													data-trigger='focus'
													data-html='true'
													data-placement='bottom' ";

								// TITLE POPOVER
								echo "			title=\"<div><img src='../images/".$image_profil."' width='20' height='20'><img alt='".$nom_grade_perso."' title='".$nom_grade_perso."' src='../images/grades/" . $id_grade_perso . ".gif' width='20' height='20'> <a href='evenement.php?infoid=".$id_perso."' target='_blank'>".$nom_perso." [".$id_perso."]</a></div> ";

								afficher_infos_compagnie($nom_compagnie_perso, $image_compagnie_perso);

								if (!in_bat($mysqli,$id_perso)) {

									if (!in_train($mysqli,$id_perso)) {
										afficher_infos_non_bat_non_train($fond_im, $nom_terrain, $nb_o);
									}
									else {
										afficher_infos_in_train($mysqli, $id_perso);
									}
								}
								else {
									afficher_infos_in_bat($mysqli, $id_perso);
								}
								echo "<div><u>Message du jour</u> :<br />".$message_perso."</div>";

								echo "\" ";

								// DATA CONTENT POPOVER
								echo "			data-content=\"";

								afficher_liens_objet($nb_o, $x, $y);
								afficher_liens_rail_genie($genie_compagnie_perso, $fond_im);

								if (in_bat($mysqli,$id_perso)) {

									afficher_liens_in_bat($mysqli, $id_perso);

								}
								else if (prox_bat($mysqli, $x_perso, $y_perso, $id_perso)) {

									afficher_liens_prox_bat($mysqli, $id_perso, $x_perso, $y_perso, $type_perso);

								}
								echo "\" >" ;

								// Affichage pastille étendard
								$id_etendard = id_etendard_joueur($mysqli, $id_perso);
								$id_etendard == 8 ? $pastille = 'rond_b.png' : $pastille = 'rond_r.png';
								if($id_etendard > 0){
									affichage_pastille_etendard($pastille);
								};

								echo  $id_perso . "</div>";

								echo "		<img tabindex='0' class=\"\" border=0 src=\"../images_perso/$dossier_img_joueur/$image_perso\" width=40 height=40
													data-toggle='popover'
													data-trigger='focus'
													data-html='true'
													data-placement='bottom' ";
								// TITLE POPOVER
								echo "			title=\"<div><img src='../images/".$image_profil."' width='20' height='20'><img alt='".$nom_grade_perso."' title='".$nom_grade_perso."' src='../images/grades/" . $id_grade_perso . ".gif' width='20' height='20'> <a href='evenement.php?infoid=".$id_perso."' target='_blank'>".$nom_perso." [".$id_perso."]</a></div>";

								afficher_infos_compagnie($nom_compagnie_perso, $image_compagnie_perso);

								if (!in_bat($mysqli,$id_perso)) {

									if (!in_train($mysqli,$id_perso)) {
										afficher_infos_non_bat_non_train($fond_im, $nom_terrain, $nb_o);
									}
									else {
										afficher_infos_in_train($mysqli, $id_perso);
									}
								}
								else {
									afficher_infos_in_bat($mysqli, $id_perso);
								}
								echo "<div><u>Message du jour</u> :<br />".$message_perso."</div>";

								echo "\" ";
								// DATA CONTENT POPOVER
								echo "			data-content=\"";

								afficher_liens_objet($nb_o, $x, $y);
								afficher_liens_rail_genie($genie_compagnie_perso, $fond_im);

								if (in_bat($mysqli,$id_perso)) {

									afficher_liens_in_bat($mysqli, $id_perso);

								}
								else if (prox_bat($mysqli, $x_perso, $y_perso, $id_perso)) {

									afficher_liens_prox_bat($mysqli, $id_perso, $x_perso, $y_perso, $type_perso);

								}
								echo "\" ";
								echo " />";
								echo "	</div>";
								echo "</td>";
							}
							else {
								if ($tab["occupee_carte"]){

									//------------------------------------
									// Traitement PNJ
									if($tab['idPerso_carte'] >= 200000){

										$idI_pnj = $tab['idPerso_carte'];
										$fond_im = $tab["fond_carte"];

										$nom_terrain = get_nom_terrain($fond_im);

										// recuperation du type de pnj
										$sql_im = "SELECT instance_pnj.id_pnj, nom_pnj FROM instance_pnj, pnj WHERE instance_pnj.id_pnj = pnj.id_pnj AND idInstance_pnj='$idI_pnj'";
										$res_im = $mysqli->query($sql_im);
										$t_im = $res_im->fetch_assoc();

										$id_pnj_im 	= $t_im["id_pnj"];
										$nom_pnj_im	= $t_im["nom_pnj"];

										$im_pnj="pnj".$id_pnj_im."t.png";

										$dossier_pnj = "images/pnj";

										echo "	<td width=40 height=40 background=\"../fond_carte/".$fond_im."\">";
										echo "		<img tabindex='0' border=0 src=\"../".$dossier_pnj."/".$im_pnj."\" width=40 height=40
															data-toggle='popover'
															data-trigger='focus'
															data-html='true'
															data-placement='bottom'
															title=\"<div><a href='evenement.php?infoid=".$idI_pnj."' target='_blank'>".$nom_pnj_im." [".$idI_pnj."]</a></div><div><img src='../fond_carte/".$fond_im."' width='20' height='20'> ".$nom_terrain."</div>\" >";
										echo "	</td>";
									}
									else {
										//-------------------------
										//  traitement Batiment
										if($tab['idPerso_carte'] >= 50000 && $tab['idPerso_carte'] < 200000){

											$idI_bat = $tab['idPerso_carte'];

											// recuperation du type de bat et du camp
											$sql_im = "SELECT instance_batiment.id_batiment, camp_instance, nom_instance, nom_batiment
														FROM instance_batiment, batiment
														WHERE instance_batiment.id_batiment = batiment.id_batiment
														AND id_instanceBat='$idI_bat'";
											$res_im = $mysqli->query($sql_im);
											$t_im = $res_im->fetch_assoc();

											$type_bat 	= $t_im["id_batiment"];
											$camp_bat 	= $t_im["camp_instance"];
											$nom_i_bat	= $t_im["nom_instance"];
											$nom_bat	= $t_im["nom_batiment"];

											switch($camp_bat){
												case '1':
													$camp_bat2 		= 'bleu';
													$image_profil 	= "Nord.gif";
													break;
												case '2':
													$camp_bat2 		= 'rouge';
													$image_profil 	= "Sud.gif";
												default:
													$camp_bat2 		= 'neutre';
													$image_profil 	= "neutre.gif";
											}

											$blason="mini_blason_".$camp_bat2.".gif";

											echo "<td width=40 height=40 background=\"../fond_carte/".$tab["fond_carte"]."\">";
											echo "	<img tabindex='0' border=0 src=\"../images_perso/".$tab["image_carte"]."\" width=40 height=40
														data-toggle='popover'
														data-trigger='focus'
														data-html='true'
														data-placement='bottom' ";
											echo "		title=\"<div><img src='../images/".$image_profil."' width='20' height='20'> <a href='evenement.php?infoid=".$idI_bat."' target='_blank'>".$nom_bat." ".$nom_i_bat." [".$idI_bat."]</a></div>\"";
											echo "		data-content=\"";
											if (in_bat($mysqli,$id_perso)) {

												$id_instance_in_bat = in_bat($mysqli,$id_perso);

												if ($idI_bat == $id_instance_in_bat) {

													echo "<div><a href='batiment.php?bat=".$id_instance_in_bat."' target='_blank'>Accéder à la page du bâtiment</a></div> ";
													echo "<div><a href='action.php?bat=".$idI_bat."&reparer=ok'>Réparer ce bâtiment (5PA)</a></div> ";
												}
											}
											else if(prox_instance_bat($mysqli, $x_perso, $y_perso, $idI_bat) && $type_bat != 12) {

												echo "<div><a href='action.php?bat=".$idI_bat."&reparer=ok'>Réparer ce bâtiment (5PA)</a></div> ";

												if (!nation_perso_bat($mysqli, $id_perso, $idI_bat)) {
													if(batiment_vide($mysqli, $idI_bat) && batiment_pv_capturable($mysqli, $idI_bat)&& $type_bat != 1 && $type_bat != 5 && $type_bat != 7 && $type_bat != 10 && $type_bat != 11 && $type_perso == 3){
														echo "<div><a href='jouer.php?bat=".$idI_bat."&bat2=".$type_bat."'>Capturer ce bâtiment</a></div>";
													}
												}
												else {
													if($type_bat != 1 && $type_bat != 5 && $type_bat != 10){
														if (($type_bat == 2 && ($type_perso == 3 || $type_perso == 4 || $type_perso == 6)) || $type_bat != 2 ) {
															echo "<div><a href='jouer.php?bat=".$idI_bat."&bat2=".$type_bat."'>Entrer dans ce bâtiment</a></div>";
														}
													}
												}
											}
											echo "\">";
											echo "</td>";
										}
										else {

											if($tab['image_carte'] == "murt.png"){
												//positionement du mur
												echo "<td width=40 height=40 background=\"../fond_carte/".$tab["fond_carte"]."\"> <img border=0 src=\"../images_perso/".$tab["image_carte"]."\" width=40 height=40 onMouseOver=\"AffBulle('<img src=../images/murs/mur.jpeg>')\" onMouseOut=\"HideBulle()\" title=\"mur\"></td>";
											}
											else {

												$id_perso_im 	= $tab['idPerso_carte'];
												$fond_im 		= $tab["fond_carte"];

												$nom_terrain 	= get_nom_terrain($fond_im);
												$cout_pm 		= cout_pm($fond_im, $type_perso);

												//recuperation du type de perso (image)
												$sql_perso_im = "SELECT * FROM perso WHERE id_perso='$id_perso_im'";
												$res_perso_im = $mysqli->query($sql_perso_im);
												$t_perso_im = $res_perso_im->fetch_assoc();

												$im_perso 	= $t_perso_im["image_perso"];
												$nom_ennemi = $t_perso_im['nom_perso'];
												$id_ennemi 	= $t_perso_im['id_perso'];
												$clan_e 	= $t_perso_im['clan'];
												$message_e	= $t_perso_im['message_perso'];

												if($clan_e == 1){
													$clan_ennemi 	= 'rond_b.png';
													$couleur_clan_e = 'blue';
													$image_profil 	= "Nord.gif";
												}
												if($clan_e == 2){
													$clan_ennemi 	= 'rond_r.png';
													$couleur_clan_e = 'red';
													$image_profil 	= "Sud.gif";
												}

												// récupération du grade du perso
												$sql_grade = "SELECT perso_as_grade.id_grade, nom_grade FROM perso_as_grade, grades WHERE perso_as_grade.id_grade = grades.id_grade AND id_perso='$id_ennemi'";
												$res_grade = $mysqli->query($sql_grade);
												$t_grade = $res_grade->fetch_assoc();

												$id_grade_ennemi 	= $t_grade["id_grade"];
												$nom_grade_ennemi 	= $t_grade["nom_grade"];

												// cas particuliers grouillot
												if ($id_grade_ennemi == 101) {
													$id_grade_ennemi = "1.1";
												}
												if ($id_grade_ennemi == 102) {
													$id_grade_ennemi = "1.2";
												}

												// recuperation de l'id de la compagnie
												$sql_groupe = "SELECT id_compagnie from perso_in_compagnie where id_perso='$id_perso_im' AND (attenteValidation_compagnie='0' OR attenteValidation_compagnie='2')";
												$res_groupe = $mysqli->query($sql_groupe);
												$t_groupe = $res_groupe->fetch_assoc();
												$nb = $res_groupe->num_rows;

												$id_groupe = $nb ? $t_groupe['id_compagnie'] : 0;

												$nom_compagnie = '';

												if($id_groupe){

													// recuperation des infos sur la compagnie (dont le nom)
													$sql_groupe2 = "SELECT * FROM compagnies WHERE id_compagnie='$id_groupe'";
													$res_groupe2 = $mysqli->query($sql_groupe2);
													$t_groupe2 = $res_groupe2->fetch_assoc();

													$nom_compagnie 		= addslashes($t_groupe2['nom_compagnie']);
													$id_compagnie 		= $t_groupe2['id_compagnie'];
													$image_compagnie	= $t_groupe2['image_compagnie'];

												}

												if(isset($nom_compagnie) && trim($nom_compagnie) != ''){

													echo "<td width=40 height=40 background=\"../fond_carte/".$fond_im."\">";
													echo "	<div width=40 height=40 style=\"position: relative;\">";

													//--- Div matricule perso
													echo "		<div tabindex='0' data-toggle='popover' data-trigger='focus' data-html='true' data-placement='bottom' style=\"position: absolute;bottom: -2px;text-align: center; width: 100%;font-weight: bold;\" ";
													// Title popover
													echo "			title=\"<div><img src='../images/".$image_profil."' width='20' height='20'><img alt='".$nom_grade_ennemi."' title='".$nom_grade_ennemi."' src='../images/grades/" . $id_grade_ennemi . ".gif' width='20' height='20'> <a href='evenement.php?infoid=".$id_ennemi."' target='_blank'>".$nom_ennemi." [".$id_ennemi."]</a></div><div><a href='compagnie.php?id_compagnie=".$id_compagnie."&voir_compagnie=ok' target='_blank'>";
													if (trim($image_compagnie) != "" && $image_compagnie != "0") {
														echo "<img src='".$image_compagnie."' width='20' height='20'>";
													}
													echo " ".stripslashes($nom_compagnie)."</a></div>";
													if ($nom_terrain == "Pont") {

														$sql_p = "SELECT id_instanceBat FROM instance_batiment WHERE x_instance='$x' AND y_instance='$y'";
														$res_p = $mysqli->query($sql_p);
														$t_p = $res_p->fetch_assoc();

														$idIBat = $t_p['id_instanceBat'];

														echo "<div><a href='evenement.php?infoid=".$idIBat."'><img src='../fond_carte/".$fond_im."' width='20' height='20'> ".$nom_terrain." [".$idIBat."]</a></div>";
													}
													else {
														echo "<div><img src='../fond_carte/".$fond_im."' width='20' height='20'> ".$nom_terrain."</div>";
													}
													echo "<div><u>Message du jour</u> :<br />".$message_e."</div>\" ";
													// data content popover
													echo "			data-content=\"<div><a href='nouveau_message.php?pseudo=".$nom_ennemi."' target='_blank'>Envoyer un message</a></div>";

													afficher_lien_bouculade($x, $x_perso, $y, $y_perso, $cout_pm);

													echo "			\" >" . $id_ennemi . "</div>";

													//--- Image perso
													echo "		<img tabindex='0' border=0 src=\"../images_perso/$dossier_img_joueur/".$tab["image_carte"]."\" width=40 height=40 data-toggle='popover' data-trigger='focus' data-html='true' data-placement='bottom' ";
													// Title popover
													echo "			title=\"<div><img src='../images/".$image_profil."' width='20' height='20'><img alt='".$nom_grade_ennemi."' title='".$nom_grade_ennemi."' src='../images/grades/" . $id_grade_ennemi . ".gif' width='20' height='20'> <a href='evenement.php?infoid=".$id_ennemi."' target='_blank'>".$nom_ennemi." [".$id_ennemi."]</a></div><div><a href='compagnie.php?id_compagnie=".$id_compagnie."&voir_compagnie=ok' target='_blank'>";
													if (trim($image_compagnie) != "" && $image_compagnie != "0") {
														echo "<img src='".$image_compagnie."' width='20' height='20'>";
													}
													echo " ".stripslashes($nom_compagnie)."</a></div>";
													if ($nom_terrain == "Pont") {

														$sql_p = "SELECT id_instanceBat FROM instance_batiment WHERE x_instance='$x' AND y_instance='$y'";
														$res_p = $mysqli->query($sql_p);
														$t_p = $res_p->fetch_assoc();

														$idIBat = $t_p['id_instanceBat'];

														echo "<div><a href='evenement.php?infoid=".$idIBat."'><img src='../fond_carte/".$fond_im."' width='20' height='20'> ".$nom_terrain." [".$idIBat."]</a></div>";
													}
													else {
														echo "<div><img src='../fond_carte/".$fond_im."' width='20' height='20'> ".$nom_terrain."</div>";
													}
													echo "<div><u>Message du jour</u> :<br />".$message_e."</div>\" ";
													// Data content popover
													echo "			data-content=\"<div><a href='nouveau_message.php?pseudo=".$nom_ennemi."' target='_blank'>Envoyer un message</a></div>";

													afficher_lien_bouculade($x, $x_perso, $y, $y_perso, $cout_pm);

													echo "			\" />";
													echo "	</div>";
													echo "</td>";
												}
												else {
													echo "<td width=40 height=40 background=\"../fond_carte/".$fond_im."\">";

													//--- Div matricule perso
													echo "	<div width=40 height=40 style=\"position: relative;\">";
													echo "		<div tabindex='0' data-toggle='popover' data-trigger='focus' data-html='true' data-placement='bottom' style=\"position: absolute;bottom: -2px;text-align: center; width: 100%;font-weight: bold;\" ";
													// Title Popover
													echo "			title=\"<div><img src='../images/".$image_profil."' width='20' height='20'><img alt='".$nom_grade_ennemi."' title='".$nom_grade_ennemi."' src='../images/grades/" . $id_grade_ennemi . ".gif' width='20' height='20'> <a href='evenement.php?infoid=".$id_ennemi."' target='_blank'>".$nom_ennemi." [".$id_ennemi."]</a></div>";
													echo "<div><img src='../fond_carte/".$fond_im."' width='20' height='20'> ".$nom_terrain."</div>";
													echo "<div><u>Message du jour</u> :<br />".$message_e."</div>\" ";

													echo "			data-content=\"<div><a href='nouveau_message.php?pseudo=".$nom_ennemi."' target='_blank'>Envoyer un message</a></div>";

													afficher_lien_bouculade($x, $x_perso, $y, $y_perso, $cout_pm);

													echo "			\" ";
													echo "		>";
													// Affichage pastille étendard
													$id_etendard = id_etendard_joueur($mysqli, $id_ennemi);
													$id_etendard == 8 ? $pastille = 'rond_b.png' : $pastille = 'rond_r.png';
													if($id_etendard > 0){
														affichage_pastille_etendard($pastille);
													}
													echo  $id_ennemi . "</div>";

													//--- Image perso
													echo "		<img tabindex='0' border=0 src=\"../images_perso/$dossier_img_joueur/".$tab["image_carte"]."\" width=40 height=40 data-toggle='popover' data-trigger='focus' data-html='true' data-placement='bottom' ";
													// Title popover
													echo "			title=\"<div><img src='../images/".$image_profil."' width='20' height='20'><img alt='".$nom_grade_ennemi."' title='".$nom_grade_ennemi."' src='../images/grades/" . $id_grade_ennemi . ".gif' width='20' height='20'> <a href='evenement.php?infoid=".$id_ennemi."' target='_blank'>".$nom_ennemi." [".$id_ennemi."]</a></div><div><img src='../fond_carte/".$fond_im."' width='20' height='20'> ".$nom_terrain."</div>";
													echo "<div><u>Message du jour</u> :<br />".$message_e."</div>\" ";
													echo "			data-content=\"<div><a href='nouveau_message.php?pseudo=".$nom_ennemi."' target='_blank'>Envoyer un message</a></div>";

													afficher_lien_bouculade($x, $x_perso, $y, $y_perso, $cout_pm);

													echo "			\" />";
													echo "	</div>";

													echo "</td>";
												}
											}
										}
									}
								}
								else {

									//------------------------------------------------------------
									//  traitement Batiment qui occupe pas une case comme le pont
									if($tab['idPerso_carte'] >= 50000 && $tab['idPerso_carte'] < 200000){

										$idI_bat = $tab['idPerso_carte'];

										// recuperation du type de bat et du camp
										$sql_im = "SELECT instance_batiment.id_batiment, camp_instance, nom_instance, nom_batiment
													FROM instance_batiment, batiment
													WHERE instance_batiment.id_batiment = batiment.id_batiment
													AND id_instanceBat='$idI_bat'";
										$res_im = $mysqli->query($sql_im);
										$t_im = $res_im->fetch_assoc();

										$type_bat 	= $t_im["id_batiment"];
										$camp_bat 	= $t_im["camp_instance"];
										$nom_i_bat	= $t_im["nom_instance"];
										$nom_bat	= $t_im["nom_batiment"];

										$fond_carte = $tab["fond_carte"];

										$cout_pm = cout_pm($fond_carte, $type_perso);

										afficher_popover_pont($x, $x_perso, $y, $y_perso, $fond_carte, $idI_bat, $nom_bat, $cout_pm, $type_perso);
									}
									else {

										$fond_im 			= $tab["fond_carte"];

										$nom_terrain 		= get_nom_terrain($fond_im);
										$cout_pm_terrain 	= cout_pm($fond_im, $type_perso);

										// verification s'il y a un objet sur cette case
										$sql_o = "SELECT type_objet, id_objet FROM objet_in_carte WHERE x_carte='$x' AND y_carte='$y' ORDER BY id_objet DESC";
										$res_o = $mysqli->query($sql_o);
										$nb_o = $res_o->num_rows;

										if($nb_o){
											$t = $res_o->fetch_assoc();
											$type_objet = $t['type_objet'];
											$objet = $t['id_objet'];

											if($type_objet == 2 && $objet == '8'){
												$image_objet = 'etendard_nord.png';
											} else if($type_objet == 2 && $objet == '9'){
												$image_objet = 'etendard_sud.png';
											} else {
												$image_objet = 'o1.gif';
											}
										} else {
											$image_objet = '';
										}

										$sql_case = "SELECT valid_case FROM joueur WHERE id_joueur='$id_joueur_perso'";
										$res_case = $mysqli->query($sql_case);
										$t = $res_case->fetch_assoc();
										$valid_case = $t['valid_case'];

										if (in_bat($mysqli, $id_perso)) {

											$taille_case = ceil($taille_bat_perso / 2);

											afficher_popover_in_bat($x, $x_perso, $y, $y_perso, $taille_case, $fond_im, $nb_o, $nom_terrain, $id_bat_perso, $image_objet);
										}
										else {

											if($y > $y_perso+1 || $y < $y_perso-1 || $x > $x_perso+1 || $x < $x_perso-1) {
												if($nb_o){
													echo "<td width=40 height=40 background=\"../fond_carte/".$tab["fond_carte"]."\">";
													echo "	<img border=0 src=\"../fond_carte/".$image_objet."\" width=40 height=40 data-toggle='tooltip' data-placement='top' title='objets à ramasser'/>";
													echo "</td>";
												}
												else {
													echo "<td width=40 height=40> <img border=0 src=\"../fond_carte/".$fond_im."\" width=40 height=40></td>";
												}
											}
											else {
												if($y == $y_perso+1 && $x == $x_perso+1){
													if($nb_o){
														echo "<td width=40 height=40 background=\"../fond_carte/".$fond_im."\">";
														echo "	<img tabindex='0' border=0 src=\"../fond_carte/".$image_objet."\" width=40 height=40 data-toggle='popover' data-trigger='focus' data-html='true' data-placement='bottom' title=\"<div>Objets à ramasser</div>\" data-content=\"<div><a href='jouer.php?mouv=3'>Se déplacer</a></div><div><a href='jouer.php?ramasser=voir&x=$x&y=$y'>Voir la liste des objets à terre</a></div>\" >";
														echo "</td>";
													}
													else {
														echo "<td width=40 height=40>";
														if ($valid_case || is_case_rail($fond_im)) {
															echo "	<img tabindex='0' border=0 src=\"../fond_carte/".$fond_im."\" width=40 height=40 data-toggle='popover' data-trigger='focus' data-html='true' data-placement='bottom' ";
															echo "			title=\"<div><img src='../fond_carte/".$fond_im."' width='20' height='20'> ".$nom_terrain." - ".$cout_pm_terrain." PM</div>\" ";
															echo "			data-content=\"<div><a href='jouer.php?mouv=3'>Se déplacer</a></div>\" >";
														}
														else {
															echo "	<a href=\"jouer.php?mouv=3\">";
															echo "		<img tabindex='0' border=0 src=\"../fond_carte/".$fond_im."\" width=40 height=40>";
															echo "	</a>";
														}
														echo "</td>";
													}
												}
												if($y == $y_perso-1 && $x == $x_perso+1){
													if($nb_o){
														echo "<td width=40 height=40 background=\"../fond_carte/".$fond_im."\">";
														echo "	<img tabindex='0' border=0 src=\"../fond_carte/".$image_objet."\" width=40 height=40 data-toggle='popover' data-trigger='focus' data-html='true' data-placement='bottom' title=\"<div>Objets à ramasser</div>\" data-content=\"<div><a href='jouer.php?mouv=8'>Se déplacer</a></div><div><a href='jouer.php?ramasser=voir&x=$x&y=$y'>Voir la liste des objets à terre</a></div>\" >";
														echo "</td>";
													}
													else {
														echo "<td width=40 height=40>";
														if ($valid_case || is_case_rail($fond_im)) {
															echo "	<img tabindex='0' border=0 src=\"../fond_carte/".$fond_im."\" width=40 height=40 data-toggle='popover' data-trigger='focus' data-html='true' data-placement='bottom' ";
															echo "			title=\"<div><img src='../fond_carte/".$fond_im."' width='20' height='20'> ".$nom_terrain." - ".$cout_pm_terrain." PM</div>\" ";
															echo "			data-content=\"<div><a href='jouer.php?mouv=8'>Se déplacer</a></div>\" >";
														}
														else {
															echo "	<a href=\"jouer.php?mouv=8\"><img border=0 src=\"../fond_carte/".$fond_im."\" width=40 height=40></a>";
														}
														echo "</td>";
													}
												}
												if($y == $y_perso && $x == $x_perso+1){
													if($nb_o){
														echo "<td width=40 height=40 background=\"../fond_carte/".$fond_im."\">";
														echo "	<img tabindex='0' border=0 src=\"../fond_carte/".$image_objet."\" width=40 height=40 data-toggle='popover' data-trigger='focus' data-html='true' data-placement='bottom' title=\"<div>Objets à ramasser</div>\" data-content=\"<div><a href='jouer.php?mouv=5'>Se déplacer</a></div><div><a href='jouer.php?ramasser=voir&x=$x&y=$y'>Voir la liste des objets à terre</a></div>\" >";
														echo "</td>";
													}
													else {
														echo "<td width=40 height=40>";
														if ($valid_case || is_case_rail($fond_im)) {
															echo "	<img tabindex='0' border=0 src=\"../fond_carte/".$fond_im."\" width=40 height=40 data-toggle='popover' data-trigger='focus' data-html='true' data-placement='bottom' ";
															echo "			title=\"<div><img src='../fond_carte/".$fond_im."' width='20' height='20'> ".$nom_terrain." - ".$cout_pm_terrain." PM</div>\" ";
															echo "			data-content=\"<div><a href='jouer.php?mouv=5'>Se déplacer</a></div>\" >";
														}
														else {
															echo "<a href=\"jouer.php?mouv=5\"><img border=0 src=\"../fond_carte/".$fond_im."\" width=40 height=40></a>";
														}
														echo "</td>";
													}
												}
												if($y == $y_perso && $x == $x_perso-1) {
													if($nb_o){
														echo "<td width=40 height=40 background=\"../fond_carte/".$fond_im."\">";
														echo "	<img tabindex='0' border=0 src=\"../fond_carte/".$image_objet."\" width=40 height=40 data-toggle='popover' data-trigger='focus' data-html='true' data-placement='bottom' title=\"<div>Objets à ramasser</div>\" data-content=\"<div><a href='jouer.php?mouv=4'>Se déplacer</a></div><div><a href='jouer.php?ramasser=voir&x=$x&y=$y'>Voir la liste des objets à terre</a></div>\" >";
														echo "</td>";
													}
													else {
														echo "<td width=40 height=40>";
														if ($valid_case || is_case_rail($fond_im)) {
															echo "	<img tabindex='0' border=0 src=\"../fond_carte/".$fond_im."\" width=40 height=40 data-toggle='popover' data-trigger='focus' data-html='true' data-placement='bottom' ";
															echo "			title=\"<div><img src='../fond_carte/".$fond_im."' width='20' height='20'> ".$nom_terrain." - ".$cout_pm_terrain." PM</div>\" ";
															echo "			data-content=\"<div><a href='jouer.php?mouv=4'>Se déplacer</a></div>\" >";
														}
														else {
															echo "<a href=\"jouer.php?mouv=4\"><img border=0 src=\"../fond_carte/".$fond_im."\" width=40 height=40></a>";
														}
														echo "</td>";
													}
												}
												if($y == $y_perso+1 && $x == $x_perso-1) {
													if($nb_o){
														echo "<td width=40 height=40 background=\"../fond_carte/".$fond_im."\">";
														echo "	<img tabindex='0' border=0 src=\"../fond_carte/".$image_objet."\" width=40 height=40 data-toggle='popover' data-trigger='focus' data-html='true' data-placement='bottom' title=\"<div>Objets à ramasser</div>\" data-content=\"<div><a href='jouer.php?mouv=1'>Se déplacer</a></div><div><a href='jouer.php?ramasser=voir&x=$x&y=$y'>Voir la liste des objets à terre</a></div>\" >";
														echo "</td>";
													}
													else {
														echo "<td width=40 height=40>";
														if ($valid_case || is_case_rail($fond_im)) {
															echo "	<img tabindex='0' border=0 src=\"../fond_carte/".$fond_im."\" width=40 height=40 data-toggle='popover' data-trigger='focus' data-html='true' data-placement='bottom' ";
															echo "			title=\"<div><img src='../fond_carte/".$fond_im."' width='20' height='20'> ".$nom_terrain." - ".$cout_pm_terrain." PM</div>\" ";
															echo "			data-content=\"<div><a href='jouer.php?mouv=1'>Se déplacer</a></div>\" >";
														}
														else {
															echo "<a href=\"jouer.php?mouv=1\"><img border=0 src=\"../fond_carte/".$fond_im."\" width=40 height=40></a>";
														}
														echo "</td>";
													}
												}
												if($y == $y_perso-1 && $x == $x_perso-1) {
													if($nb_o){
														echo "<td width=40 height=40 background=\"../fond_carte/".$fond_im."\">";
														echo "	<img tabindex='0' border=0 src=\"../fond_carte/".$image_objet."\" width=40 height=40 data-toggle='popover' data-trigger='focus' data-html='true' data-placement='bottom' title=\"<div>Objets à ramasser</div>\" data-content=\"<div><a href='jouer.php?mouv=6'>Se déplacer</a></div><div><a href='jouer.php?ramasser=voir&x=$x&y=$y'>Voir la liste des objets à terre</a></div>\" >";
														echo "</td>";
													}
													else {
														echo "<td width=40 height=40>";
														if ($valid_case || is_case_rail($fond_im)) {
															echo "	<img tabindex='0' border=0 src=\"../fond_carte/".$fond_im."\" width=40 height=40 data-toggle='popover' data-trigger='focus' data-html='true' data-placement='bottom' ";
															echo "			title=\"<div><img src='../fond_carte/".$fond_im."' width='20' height='20'> ".$nom_terrain." - ".$cout_pm_terrain." PM</div>\" ";
															echo "			data-content=\"<div><a href='jouer.php?mouv=6'>Se déplacer</a></div>\" >";
														}
														else {
															echo "<a href=\"jouer.php?mouv=6\"><img border=0 src=\"../fond_carte/".$fond_im."\" width=40 height=40></a>";
														}
														echo "</td>";
													}
												}
												if($y == $y_perso+1 && $x == $x_perso) {
													if($nb_o){
														echo "<td width=40 height=40 background=\"../fond_carte/".$fond_im."\">";
														echo "	<img tabindex='0' border=0 src=\"../fond_carte/".$image_objet."\" width=40 height=40 data-toggle='popover' data-trigger='focus' data-html='true' data-placement='bottom' title=\"<div>Objets à ramasser</div>\" data-content=\"<div><a href='jouer.php?mouv=2'>Se déplacer</a></div><div><a href='jouer.php?ramasser=voir&x=$x&y=$y'>Voir la liste des objets à terre</a></div>\" >";
														echo "</td>";
													}
													else {
														echo "<td width=40 height=40>";
														if ($valid_case || is_case_rail($fond_im)) {
															echo "	<img tabindex='0' border=0 src=\"../fond_carte/".$fond_im."\" width=40 height=40 data-toggle='popover' data-trigger='focus' data-html='true' data-placement='bottom' ";
															echo "			title=\"<div><img src='../fond_carte/".$fond_im."' width='20' height='20'> ".$nom_terrain." - ".$cout_pm_terrain." PM</div>\" ";
															echo "			data-content=\"<div><a href='jouer.php?mouv=2'>Se déplacer</a></div>\" >";
														}
														else {
															echo "<a href=\"jouer.php?mouv=2\"><img border=0 src=\"../fond_carte/".$fond_im."\" width=40 height=40></a>";
														}
														echo "</td>";
													}
												}
												if($y == $y_perso-1 && $x == $x_perso) {
													if($nb_o){
														echo "<td width=40 height=40 background=\"../fond_carte/".$fond_im."\">";
														echo "	<img tabindex='0' border=0 src=\"../fond_carte/".$image_objet."\" width=40 height=40 data-toggle='popover' data-trigger='focus' data-html='true' data-placement='bottom' title=\"<div>Objets à ramasser</div>\" data-content=\"<div><a href='jouer.php?mouv=7'>Se déplacer</a></div><div><a href='jouer.php?ramasser=voir&x=$x&y=$y'>Voir la liste des objets à terre</a></div>\" >";
														echo "</td>";
													}
													else {
														echo "<td width=40 height=40>";
														if ($valid_case || is_case_rail($fond_im)) {
															echo "	<img tabindex='0' border=0 src=\"../fond_carte/".$fond_im."\" width=40 height=40 data-toggle='popover' data-trigger='focus' data-html='true' data-placement='bottom' ";
															echo "			title=\"<div><img src='../fond_carte/".$fond_im."' width='20' height='20'> ".$nom_terrain." - ".$cout_pm_terrain." PM</div>\" ";
															echo "			data-content=\"<div><a href='jouer.php?mouv=7'>Se déplacer</a></div>\" >";
														}
														else {
															echo "<a href=\"jouer.php?mouv=7\"><img border=0 src=\"../fond_carte/".$fond_im."\" width=40 height=40></a>";
														}
														echo "</td>";
													}
												}
											}
										}
									}
								}
							}
							$tab = $res->fetch_assoc();
						}
						else //les coordonnées sont hors limites
							echo "<td width=40 height=40><img border=0 width=40 height=40 src=\"../fond_carte/decorO.jpg\"></td>";
					}
					echo "</tr>";
				}
				?>
								</table>
							</td>
						</tr>
					</table>
				</td>
				<!--Fin de la génération de la carte-->

				<?php
				if($config == '2'){
					echo "</tr><tr>";
				}
				?>

				<!--Debut tableau des actions -->
				<td valign="top">
					<table style="border:1px solid black; border-collapse: collapse;">
						<tr>
							<td>
								<table border="0" cellspacing="0" cellpadding="0" style:no-padding>
									<tr>
										<td background='../images/background.jpg' align='center' valign='top'colspan='2'>
											<img src='../images/Action.png' border='0'/>
											<form method='post' action='action.php'>
												<select name='liste_action'>
													<option value="invalide" selected>-- -- -- -- -- -- - Choisir une action - -- -- -- -- -- --</option>
													<?php

													// Action d'entrainement
													if($pa_perso >= 10){
														echo "<option value=\"65\">Entrainement (10 PA)</option>";
													}

													// Action Déposer Objet
													if($pa_perso >= 1){
														echo "<option value=\"110\">Deposer objet (1 PA)</option>";
														echo "<option value=\"139\">Donner objet (1 PA)</option>";
													}

													// Actions selon le type d'unité

													// Cavalerie et cavalerie lourde
													if (verif_charge_pm($type_perso, $pm_perso) && !in_train($mysqli, $id_perso) && !in_bat($mysqli, $id_perso)) {
														// Charge = 999
														echo '<option value="999">Charger (tous les PA)</option>';
													}

													$sql = "SELECT action.id_action, nom_action, coutPa_action, reflexive_action
															FROM perso_as_competence, competence_as_action, action
															WHERE id_perso='$id_perso'
															AND perso_as_competence.id_competence=competence_as_action.id_competence
															AND competence_as_action.id_action=action.id_action
															AND passif_action = '0'
															ORDER BY nom_action";
													$res = $mysqli->query($sql);

													while ($t_ac = $res->fetch_assoc()) {

														$id_ac 		= $t_ac["id_action"];
														$cout_PA 	= $t_ac["coutPa_action"];
														$nom_ac 	= $t_ac["nom_action"];
														$ref_ac		= $t_ac["reflexive_action"];

														if ($cout_PA == -1){
															$cout_PA = $paMax_perso;
														}

														if (!in_train($mysqli, $id_perso) && !in_bat($mysqli, $id_perso)) {
															if ($cout_PA <= $pa_perso){
																if ($id_ac == 1 && $pm_perso >= $pmMax_perso) {
																	echo "<option value=\"$id_ac\">".$nom_ac." (Tous les PA/PM)</option>";;
																}
																else if ($id_ac == 147) {
																	echo "<option value=\"$id_ac\">".$nom_ac." (". $cout_PA . "PA à 8PA)</option>";;
																}
																else {
																	echo "<option value=\"$id_ac\">".$nom_ac." (". $cout_PA . "PA)</option>";;
																}
															}
														}
														else {
															if ($ref_ac) {
																if ($cout_PA <= $pa_perso){
																	if ($id_ac == 1 && $pm_perso >= $pmMax_perso) {
																		echo "<option value=\"$id_ac\">".$nom_ac." (". $cout_PA . "pa)</option>";;
																	}
																	else if ($id_ac != 1) {
																		echo "<option value=\"$id_ac\">".$nom_ac." (". $cout_PA . "pa)</option>";;
																	}
																}
															}
														}
													}
													?>
													<option value="invalide">-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- --</option>
												</select>
												<input type='submit' name='action' value='ok' />
											</form>
											<?php
											echo $mess_bat;

											if (is_objet_a_terre($mysqli, $x_perso, $y_perso)) {
												echo "<center><font color = blue>~~<a href=\"jouer.php?ramasser=ok\">Ramasser les objets à terre (1 PA)</a>~~</font></center>";
												echo "<center><font color = blue>~~<a href=\"jouer.php?ramasser=voir\">Voir la liste des objets à terre</a>~~</font></center>";
											}

											// recuperation des données de la carte
											$sql = "SELECT fond_carte FROM $carte
													WHERE x_carte = $x_perso
													AND y_carte = $y_perso";
											$res = $mysqli->query($sql);
											$tab = $res->fetch_assoc();

											$fond_carte_perso = $tab['fond_carte'];

											afficher_liens_rail_genie($genie_compagnie_perso, $fond_carte_perso);

											?>
										</td>
									</tr>
									<tr>
										<td height='5' background='../images/background.jpg' colspan='2' align='center'>
											<img src='../images/barre.png' />
										</td>
									</tr>
									<tr>
										<td background='../images/background.jpg'>
											<table border='0'>
												<tr>
													<td>
														<img src='../images/Id.png' />
													</td>
													<td valign='top'>
														<form method="post" action="evenement.php" target='_blank'>
															<input type="text" maxlength="6" size="6" name="id_info" value="" style="background-image:url('../images/background3.jpg');">
															<input type="submit" value="Plus d'infos">
														</form>
													</td>
												</tr>
											</table>
										</td>
									</tr>
									<tr>
										<td background='../images/background.jpg' align='left' colspan='3'>
											<?php
											echo "<div><a href=\"nouveau_message.php?visu=ok&camp=".$clan_perso."\"><img class='img-fluid' src='../images/Ecrire.png' data-toggle='tooltip' data-placement='top' title='Envoyer un message aux persos de son camp dans sa visu' border=0 /><font face='Playball' size='5'><b>Envoyer un MP à sa visu</b></font></a></div>";
											echo "<div><a href=\"nouveau_message.php?visu=ok\"><img class='img-fluid' src='../images/porte_voix.png' data-toggle='tooltip' data-placement='top' title='Envoyer un message à tous les persos dans sa visu' border=0 width='100' height='80' /><font face='Playball' size='5'><b>Crier très fort</b></font></a></div>";
											?>
										</td>
									</tr>
									<tr>
										<td background='../images/background.jpg' colspan='2' align='center'>
											<img src='../images/barre.png' />
										</td>
									</tr>
								</table>
							</tr>
						</td>
					</table>
				</td>
			</tr>
		</table>
	<?php
			}
		}
	}
	else {
		header("Location:../index.php");
	}
	?>
		<!-- Optional JavaScript -->
		<!-- jQuery first, then Popper.js, then Bootstrap JS -->
		<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
		<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>

		<script>
		$(function () {
			$('[data-toggle="tooltip"]').tooltip();
			$('[data-toggle="popover"]').popover();
		})

		function openNav() {
			if(document.getElementById("mySidebar").style.width == "" || document.getElementById("mySidebar").style.width == "0px") {
				document.getElementById("mySidebar").style.width = "250px";
				document.getElementById("boutonChat").style.marginLeft = "250px";
			} else {
				document.getElementById("mySidebar").style.width = "0";
				document.getElementById("boutonChat").style.marginLeft= "0";
			}
		}

		function closeNav() {
			document.getElementById("mySidebar").style.width = "0";
			document.getElementById("boutonChat").style.marginLeft= "0";
		}
		</script>

	</body>
</html>
<?php
}
else {
	// logout
	$_SESSION = array(); // On écrase le tableau de session
	session_destroy(); // On détruit la session

	header("Location:../index.php");
}
?>
