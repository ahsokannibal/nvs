<?php
session_start();
require_once("../fonctions.php");
require_once("f_carte.php");
require_once("f_combat.php");

$mysqli = db_connexion();

include ('../nb_online.php');

$id = $_SESSION["id_perso"];

$verif_id_perso_session = preg_match("#^[0-9]*[0-9]$#i","$id");
		
if ($verif_id_perso_session) {
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
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
<?php

	$carte = "carte";
	$verif = false;

	if(isset($_POST["re_attaque"]) && isset($_POST["re_attaque_hid"])){
		
		$t_attaque_re 		= explode(",",$_POST["re_attaque_hid"]);
		$id_attaque 		= $t_attaque_re[0];
		$id_arme_attaque 	= $t_attaque_re[1];
		
		$verif = preg_match("#^[0-9]*[0-9]$#i","$id_attaque");
	}

	if (isset($_POST["id_attaque_cac"]) && $_POST["id_attaque_cac"] != "personne") {
		
		$t_attaque_cac 		= explode(",", $_POST["id_attaque_cac"]);
		$id_attaque 		= $t_attaque_cac[0];
		$id_arme_attaque 	= $t_attaque_cac[1];
		
		$verif = preg_match("#^[0-9]*[0-9]$#i","$id_attaque");
	}

	if (isset($_POST["id_attaque_cac2"]) && $_POST["id_attaque_cac2"] != "personne") {
		
		$t_attaque_cac 		= explode(",", $_POST["id_attaque_cac2"]);
		$id_attaque 		= $t_attaque_cac[0];
		$id_arme_attaque 	= $t_attaque_cac[1];
		
		$verif = preg_match("#^[0-9]*[0-9]$#i","$id_attaque");
	}

	if (isset($_POST["id_attaque_dist"]) && $_POST["id_attaque_dist"] != "personne") {
		
		$t_attaque_dist 	= explode(",", $_POST["id_attaque_dist"]);
		$id_attaque 		= $t_attaque_dist[0];
		$id_arme_attaque 	= $t_attaque_dist[1];
		
		$verif = preg_match("#^[0-9]*[0-9]$#i","$id_attaque");
	}

	if($verif){
		
		//traitement de l'attaque sur un perso
		if ((isset($id_attaque) && $id_attaque != "" && $id_attaque != "personne" && $id_attaque < 50000)) {
		
			$id_cible = $id_attaque;
			$verif_arme = 0;
			
			// arme bien passée
			if(isset($id_arme_attaque) && $id_arme_attaque != 1000 && $id_arme_attaque != 2000) {
				
				// Est-ce que le perso possède bien l'arme ?
				$sql = "SELECT * FROM perso_as_arme WHERE id_perso='$id' AND id_arme='$id_arme_attaque' AND est_portee='1'";
				$res = $mysqli->query($sql);
				$verif_arme = $res->num_rows;
				
				if ($verif_arme) {
					// Recupération des caracs de l'arme
					$sql = "SELECT nom_arme, coutPa_arme, porteeMin_arme, porteeMax_arme, degatMin_arme, degatMax_arme, degatZone_arme, precision_arme, valeur_des_arme
							FROM arme WHERE id_arme='$id_arme_attaque'";
					$res = $mysqli->query($sql);
					$t_a = $res->fetch_assoc();
					
					$nom_arme_attaque 				= $t_a["nom_arme"];
					$coutPa_arme_attaque 			= $t_a["coutPa_arme"];
					$porteeMin_arme_attaque 		= $t_a["porteeMin_arme"];
					$porteeMax_arme_attaque 		= $t_a["porteeMax_arme"];
					$valeur_des_arme_attaque		= $t_a["valeur_des_arme"];
					$degatMin_arme_attaque 			= $t_a["degatMin_arme"];
					$degatMax_arme_attaque 			= $t_a["degatMax_arme"];
					$precision_arme_attaque 		= $t_a["precision_arme"];
					$degatZone_arme_attaque 		= $t_a["degatZone_arme"];
				}
			}
			else {
				if ($id_arme_attaque == 1000) {
					
					// Poings = arme de Corps a corps					
					$nom_arme_attaque 				= "Poings";
					$coutPa_arme_attaque 			= 3;
					$porteeMin_arme_attaque 		= 1;
					$porteeMax_arme_attaque 		= 1;
					$valeur_des_arme_attaque		= 6;
					$degatMin_arme_attaque 			= 4;
					$degatMax_arme_attaque 			= 4;
					$precision_arme_attaque			= 30;
					$degatZone_arme_attaque 		= 0;
					
					$verif_arme = 1;
					
				} else if ($id_arme_attaque == 2000) {
					
					// Cailloux
					$nom_arme_attaque 				= "Cailloux";
					$coutPa_arme_attaque 			= 3;
					$porteeMin_arme_attaque 		= 1;
					$porteeMax_arme_attaque 		= 2;
					$valeur_des_arme_attaque		= 6;
					$degatMin_arme_attaque 			= 5;
					$degatMax_arme_attaque 			= 5;
					$precision_arme_attaque			= 25;
					$degatZone_arme_attaque 		= 0;
					
					$verif_arme = 1;
					
				}
			}
			
			if ($porteeMax_arme_attaque > 1 && possede_lunette_visee($mysqli, $id)) {
				$coutPa_arme_attaque = $coutPa_arme_attaque + 1;
			}
			
			if ($verif_arme) {
			
				if(!in_bat($mysqli, $id) || (in_bat($mysqli, $id) && $porteeMax_arme_attaque > 1)){
					
					// recup des données du perso
					$sql = "SELECT nom_perso, idJoueur_perso, type_perso, image_perso, xp_perso, x_perso, y_perso, pm_perso, pi_perso, pv_perso, pvMax_perso, pmMax_perso, pa_perso, paMax_perso, recup_perso, bonusRecup_perso, bonusPM_perso, perception_perso, bonusPerception_perso, dateCreation_perso, clan, gain_xp_tour, DLA_perso, perso_as_grade.id_grade, nom_grade
							FROM perso, perso_as_grade, grades
							WHERE perso_as_grade.id_perso = perso.id_perso
							AND perso_as_grade.id_grade = grades.id_grade
							AND perso.id_perso='$id'";
					$res = $mysqli->query($sql);
					$t_perso = $res->fetch_assoc();
					
					$nom_perso 		= $t_perso["nom_perso"];
					$image_perso 	= $t_perso["image_perso"];
					$xp_perso 		= $t_perso["xp_perso"];
					$x_perso 		= $t_perso["x_perso"];
					$y_perso 		= $t_perso["y_perso"];
					$pm_perso 		= $t_perso["pm_perso"];
					$pmM_perso 		= $t_perso["pmMax_perso"];
					$pi_perso 		= $t_perso["pi_perso"];
					$pv_perso 		= $t_perso["pv_perso"];
					$pvM_perso 		= $t_perso["pvMax_perso"];
					$pa_perso 		= $t_perso["pa_perso"];
					$paM_perso 		= $t_perso["paMax_perso"];
					$rec_perso 		= $t_perso["recup_perso"];
					$br_perso 		= $t_perso["bonusRecup_perso"];
					$bPM_perso		= $t_perso["bonusPM_perso"];
					$per_perso 		= $t_perso["perception_perso"];
					$bp_perso 		= $t_perso["bonusPerception_perso"];
					$dc_perso 		= $t_perso["dateCreation_perso"];
					$id_j_perso		= $t_perso["idJoueur_perso"];
					$clan_perso 	= $t_perso["clan"];
					$dla_perso		= $t_perso["DLA_perso"];
					$grade_perso 	= $t_perso["id_grade"];
					$type_perso		= $t_perso["type_perso"];
					$nom_grade_perso= $t_perso["nom_grade"];
					$gain_xp_tour_perso	= $t_perso["gain_xp_tour"];
					
					if ($pv_perso > 0) {
					
						if ($gain_xp_tour_perso >= 20) {
							$max_xp_tour_atteint = true;
						}
						else {
							$max_xp_tour_atteint = false;
						}
						
						// Récupération de la couleur associée au clan du perso
						$couleur_clan_perso = couleur_clan($clan_perso);
						
						// verification si le perso est bien a portée d'attaque			
						if(is_a_portee_attaque($mysqli, $carte, $id, $id_cible, $porteeMin_arme_attaque, $porteeMax_arme_attaque, $per_perso)) {
							
							// recuperation des données du perso cible
							$sql = "SELECT idJoueur_perso, nom_perso, type_perso, image_perso, xp_perso, x_perso, y_perso, pm_perso, pi_perso, pv_perso, pvMax_perso, pmMax_perso, pa_perso, paMax_perso, recup_perso, bonusRecup_perso, bonus_perso, perception_perso, protec_perso, bonusPerception_perso, dateCreation_perso, or_perso, clan, perso_as_grade.id_grade, nom_grade
									FROM perso, perso_as_grade, grades
									WHERE perso_as_grade.id_perso = perso.id_perso
									AND perso_as_grade.id_grade = grades.id_grade
									AND perso.id_perso='$id_cible'";								
							$res = $mysqli->query($sql);
							$t_cible = $res->fetch_assoc();
							
							$id_joueur_cible 	= $t_cible["idJoueur_perso"];
							$nom_cible 			= $t_cible["nom_perso"];
							$type_perso_cible	= $t_cible["type_perso"];
							$xp_cible 			= $t_cible["xp_perso"];
							$x_cible 			= $t_cible["x_perso"];
							$y_cible 			= $t_cible["y_perso"];
							$pm_cible 			= $t_cible["pm_perso"];
							$pmM_cible 			= $t_cible["pmMax_perso"];
							$pi_cible 			= $t_cible["pi_perso"];
							$pv_cible 			= $t_cible["pv_perso"];
							$pvM_cible 			= $t_cible["pvMax_perso"];
							$pa_cible 			= $t_cible["pa_perso"];
							$paM_cible 			= $t_cible["paMax_perso"];
							$rec_cible 			= $t_cible["recup_perso"];
							$protec_cible		= $t_cible["protec_perso"];
							$br_cible 			= $t_cible["bonusRecup_perso"];
							$bonusBase_cible	= $t_cible["bonus_perso"];
							$bonus_cible 		= $t_cible["bonus_perso"];
							$per_cible 			= $t_cible["perception_perso"];
							$bp_cible 			= $t_cible["bonusPerception_perso"];
							$dc_cible 			= $t_cible["dateCreation_perso"];
							$or_cible 			= $t_cible["or_perso"];
							$image_perso_cible 	= $t_cible["image_perso"];
							$clan_cible 		= $t_cible["clan"];
							$grade_cible		= $t_cible['id_grade'];
							$nom_grade_cible	= $t_cible['nom_grade'];
							
							// Récupération de la couleur associée au clan de la cible
							$couleur_clan_cible = couleur_clan($clan_cible);
							
							if ($clan_cible == '1') {
								$camp = 'Nord';
							}
							else if ($clan_cible == '2') {
								$camp = 'Sud';
							}
							else {
								$camp = '';
							}
							
							$pa_restant = $pa_perso - $coutPa_arme_attaque;
							
							if($pa_restant <= 0){
								$pa_restant = 0;
							}				
												
							// le perso n'a pas assez de pa pour faire cette attaque
							if ($pa_perso < $coutPa_arme_attaque) {
								echo "<div class=\"erreur\" align=\"center\">Vous n'avez pas assez de pa pour effectuer cette action !</div>";
								echo "<a class='btn btn-primary' href=\"jouer.php\">retour</a>";
							}	
							else {
								// le perso a assez de pa
								
								// Cas particulier soins
								if (($pv_cible >= $pvM_cible -30 && $id_arme_attaque == 10) || ($bonusBase_cible == 0 && $id_arme_attaque == 11)) {
									echo "<div class=\"erreur\" align=\"center\">La cible n'a pas besoin de soins !</div>";
									echo "<a class='btn btn-primary' href=\"jouer.php\">retour</a>";
								}
								else if ($pv_cible > 0) {
									
									$id_inst_bat_cible = 0;
									$id_inst_batiment 	= in_bat($mysqli, $id_cible);
									$id_inst_train		= in_train($mysqli, $id_cible);
									
									if ($id_inst_batiment != null && $id_inst_batiment != 0) {
										$id_inst_bat_cible = $id_inst_batiment;
									}
									else if ($id_inst_train != null && $id_inst_train != 0) {
										$id_inst_bat_cible = $id_inst_train;
									}
									
									// la cible est encore en vie
									?>
									<table border=0 width=100%>
										<tr height=50%>
											<td width=50%>
												<table border=1 height=100% width=100%>		
													<tr>
														<td width=25%>	
															<table border=0 width=100%>
																<tr>
																	<td align="center">
																		<div width=40 height=40 style="position: relative;">
																			<div style="position: absolute;bottom: 0;text-align: center; width: 100%;font-weight: bold;"><?php echo $id; ?></div>
																			<img class="" border=0 src="../images_perso/<?php echo $image_perso; ?>" width=40 height=40 />
																		</div>
																	</td>
																</tr>
															</table>
														</td>
														<td width=75%>
															<table border=0 width=100%>
																<tr>
																	<td><?php echo "<u><b>Pseudo :</b></u> ".$nom_perso; ?></td>
																</tr>
																<tr>
																	<td><?php echo "<u><b>Xp :</b></u> ".$xp_perso." - <u><b>Pi :</b></u> ".$pi_perso.""; ?></td>
																</tr>
																<tr>
																	<td><?php echo "<u><b>Position sur la carte :</b></u> ".$x_perso."/".$y_perso; ?></td>
																</tr>
																<tr>
																	<td><?php echo "<u><b>Mouvements restants :</b></u> ".$pm_perso."/".($pmM_perso + $bPM_perso); ?><?php echo " - <u><b>Points de vie :</b></u> ".$pv_perso."/".$pvM_perso; ?></td>
																</tr>
																<tr>
																	<td><?php echo "<u><b>Points d'action :</b></u> ".$pa_restant."/".$paM_perso; ?></td>
																</tr>
																<tr>
																	<td><?php echo "<u><b>Recup :</b></u> ".$rec_perso; if($br_perso) echo " (+".$br_perso.")"; ?><?php echo " - <u><b>Perception :</b></u> ".$per_perso; if($bp_perso) {if($bp_perso>0) echo " (+".$bp_perso.")"; else echo " (".$bp_perso.")";} ?></td>
																</tr>
															</table>
														</td>
													</tr>
												</table>
											</td>
											<td width=50%>	
												<table border=1 height=100% width=100%>		
													<tr>
														<td width=25%>	
															<table border=0 width=100%>
																<tr>												
																	<td align="center">
																		<div width=40 height=40 style="position: relative;">
																			<div style="position: absolute;bottom: 0;text-align: center; width: 100%;font-weight: bold;"><?php echo $id_cible; ?></div>
																			<img class="" border=0 src="../images_perso/<?php echo $image_perso_cible; ?>" width=40 height=40 />
																		</div>
																	</td>
																</tr>
															</table>
														</td>
														<td width=75%>
															<table border=0 width=100%>
																<tr>
																	<td><?php echo "<u><b>Pseudo :</b></u> ".$nom_cible; ?></td>
																</tr>
																<tr>
																	<td><?php echo "<u><b>Xp :</b></u> ".$xp_cible.""; ?></td>
																</tr>
																<tr>
																	<td><?php echo "<u><b>Position sur la carte :</b></u> ".$x_cible."/".$y_cible; ?></td>
																</tr>
																<tr>
																	<td><?php echo "<u><b>Camp :</b></u> <font color='".$couleur_clan_cible."'>".$camp."</font>"; ?></td>
																</tr>
															</table>
														</td>
													</tr>
												</table>
											</td>
										</tr>
							
										<tr height=50%>
											<td></td>
										</tr>	
									</table>		
									<?php
									
									// -------------
									// - ANTI ZERK -
									// -------------
									$verif_anti_zerk = gestion_anti_zerk($mysqli, $id);
									
									if ($verif_anti_zerk) {
										
										// Vérifie si le joueur attaqué a coché l'envoi de mail
										$mail_info_joueur = verif_coche_mail($mysqli, $id_joueur_cible);
										
										// Envoi mail uniquement si attaque n'est pas un soin
										if ($id_arme_attaque != 10 && $id_arme_attaque != 11) {
											if($mail_info_joueur){
												// Envoi du mail
												mail_attaque($mysqli, $nom_perso, $id_cible);
											}
										}
										
										$soin_termine = false;
									
										// Si perso ou cible est une infanterie 
										// ou si grade perso >= grade cible - 1
										$gain_pc = calcul_gain_pc_attaque_perso($grade_perso, $grade_cible, $clan_perso, $clan_cible, $type_perso, $id_j_perso, $id_joueur_cible);
										
										// Seringue ou bandage
										if ($id_arme_attaque == 10 || $id_arme_attaque == 11) {
											echo "Vous avez lancé un soin sur <b>$nom_cible [$id_cible]</b> avec $nom_arme_attaque<br/>";
										} else {
											echo "Vous avez lancé une attaque sur <b>$nom_cible [$id_cible]</b> avec $nom_arme_attaque<br/>";
										}
										
										if ($id_inst_bat_cible != 0) {
											$sql = "SELECT id_batiment FROM instance_batiment WHERE id_instanceBat='$id_inst_bat_cible'";
											$res = $mysqli->query($sql);
											$t = $res->fetch_assoc();
											
											$id_bat = $t['id_batiment'];
											
											$bonus_defense_terrain = get_bonus_defense_batiment($id_bat);
										}
										else {
											// Où se trouve la cible ?
											$sql = "SELECT fond_carte FROM carte WHERE x_carte='$x_cible' AND y_carte='$y_cible'";
											$res = $mysqli->query($sql);
											$t = $res->fetch_assoc();
											
											$fond_carte_cible = $t['fond_carte'];
											
											$bonus_defense_terrain = get_bonus_defense_terrain($fond_carte_cible, $porteeMax_arme_attaque);
										}
										
										// Bonus Précision batiment
										$bonus_precision_bat = 0;
										
										if (in_bat($mysqli, $id)) {
											
											$id_inst_bat_perso = in_bat($mysqli, $id);
											
											$sql = "SELECT id_batiment FROM instance_batiment WHERE id_instanceBat='$id_inst_bat_perso'";
											$res = $mysqli->query($sql);
											$t = $res->fetch_assoc();
											
											$id_bat_perso = $t['id_batiment'];
											
											$bonus_precision_bat = get_bonus_attaque_from_batiment($id_bat_perso);
										}
										else if (in_train($mysqli, $id)) {
											$bonus_precision_bat = -30;
										}
										
										// Calcul touche
										$touche = mt_rand(0,100);
										
										// Bonus defense objets cible 
										$bonus_defense_objet = get_bonus_defense_objet($mysqli, $id_cible);

										$bonus_precision_distance = 0;
										if ($porteeMax_arme_attaque > 3) {
											$distance = get_distance($mysqli, $id, $id_cible);
											if ($distance == $porteeMax_arme_attaque)
												$bonus_precision_distance = -15;
										}
										
										$precision_final = $precision_arme_attaque - $bonus_cible - $bonus_defense_terrain - $bonus_defense_objet + $bonus_precision_bat + $bonus_precision_distance;
										
										// Bonus Precision Objets
										$bonus_precision_objet = 0;
										if ($porteeMax_arme_attaque == 1) {
											$bonus_precision_objet = getBonusPrecisionCacObjet($mysqli, $id);
										}
										else {
											$bonus_precision_objet = getBonusPrecisionDistObjet($mysqli, $id);
										}

										$precision_final += $bonus_precision_objet;
										
										echo "Votre score de touche : ".$touche."<br>";
										echo "Précision : ".$precision_final. " (Base arme : ".$precision_arme_attaque."  -- Bonus Précision objet : ".$bonus_precision_objet."  -- Bonus precision distance : ".$bonus_precision_distance;
										if ($bonus_precision_bat != 0) {
											echo " -- Bonus du batiment : ".$bonus_precision_bat;
										}
										echo " -- Defense cible : ".$bonus_cible." -- Bonus Defense objets cible : ".$bonus_defense_objet." -- Defense terrain cible : ".$bonus_defense_terrain.")<br>";
										
										// Score touche <= precision arme utilisée - bonus cible pour l'attaque = La cible est touchée
										if ($touche <= $precision_final && $touche < 98) {
											
											$degats_tmp = calcul_des_attaque($degatMin_arme_attaque, $valeur_des_arme_attaque);
											
											// Insertion log attaque
											$message_log = $id.' a attaqué '.$id_cible;
											$type_action = "Attaque ".$id_arme_attaque;
											$sql = "INSERT INTO log (date_log, id_perso, type_action, id_arme, degats, pourcentage, message_log) VALUES (NOW(), '$id', '$type_action', '$id_arme_attaque', '$degats_tmp', '$touche', '$message_log')";
											$mysqli->query($sql);
							
											// calcul degats arme
											if ($id_arme_attaque != 10 && $id_arme_attaque != 11) {
												$degats_final = $degats_tmp - $protec_cible;
											}
											else {
												$degats_final = $degats_tmp;
											}
											
											// Canon d'artillerie et cible autre artillerie
											if (($id_arme_attaque == 13 || $id_arme_attaque == 22) && $type_perso_cible == 5) {
												// Bonus dégats 13D10
												$bonus_degats_canon = calcul_des_attaque(13, 10);
												$degats_final = $degats_final + $bonus_degats_canon;
											}
											
											if($degats_final < 0) {
												$degats_final = 0;
											}
											
											if ($touche <= 2) {
												// Coup critique ! Dégats et Gains PC X 2
												$degats_final = $degats_final * 2;
												$gain_pc = $gain_pc * 2;
											}
											
											$calcul_dif_xp = ($xp_cible - $xp_perso) / 10;
											
											if ($calcul_dif_xp < 0 || $id_arme_attaque == 10 || $id_arme_attaque == 11) {
												$valeur_des_xp = 0;
											} else {
												$valeur_des_xp = mt_rand(0, $calcul_dif_xp);
											}
											
											$gain_xp = ceil(($degats_final / 20) + $valeur_des_xp);
											
											// Limit le nombre d'xp gagné par attaque
											$max_xp_par_attaque = ceil(20 / floor(10 / $coutPa_arme_attaque));
											if ($gain_xp > $max_xp_par_attaque) {
												$gain_xp = $max_xp_par_attaque;
											}
											
											if ($gain_xp_tour_perso + $gain_xp > 20) {
												$gain_xp = 20 - $gain_xp_tour_perso;
												$max_xp_tour_atteint = true;
											}
											
											if ($id_arme_attaque == 10) {
												
												// Seringue
												if ($pv_cible + $degats_final >= $pvM_cible) {
													$degats_final = $pvM_cible - $pv_cible;
													
													$soin_termine = true;
												}
												
												// mise a jour des pv
												$sql = "UPDATE perso SET pv_perso=pv_perso+$degats_final WHERE id_perso='$id_cible'";
												$mysqli->query($sql);
												
												echo "<br>Vous avez soigné $degats_final dégâts à la cible.<br>";
												
												if ($soin_termine) {
													echo "La cible a récupérée tous ses PV<br>";
												}
												
											} else if ($id_arme_attaque == 11) {
												
												// Bandage
												if ($bonus_cible + $degats_final >= 0) {
													$sql = "UPDATE perso SET bonus_perso=0 WHERE id_perso='$id_cible'";
													echo "<br>Vous avez soigné tous les malus de la cible.<br><br>";
													
													$soin_termine = true;
												} else {
													$sql = "UPDATE perso SET bonus_perso=bonus_perso+$degats_final WHERE id_perso='$id_cible'";
													echo "<br>Vous avez soigné $degats_final malus à la cible.<br><br>";
												}
												
												$mysqli->query($sql);
												
											} else {
												// mise a jour des pv et des malus de la cible
												$sql = "UPDATE perso SET pv_perso=pv_perso-$degats_final, bonus_perso=bonus_perso-2 WHERE id_perso='$id_cible'";
												$mysqli->query($sql);
												echo "<br>Vous avez infligé $degats_final dégâts à la cible.<br>";
											}
											
											echo "Vous avez gagné $gain_xp xp.";
											if ($max_xp_tour_atteint) {
												echo " (maximum de gain d'xp par tour atteint)";
											}
											echo "<br />";
											
											// mise a jour des xp/pi
											$sql = "UPDATE perso SET xp_perso=xp_perso+$gain_xp, pi_perso=pi_perso+$gain_xp, gain_xp_tour=gain_xp_tour+$gain_xp WHERE id_perso='$id'"; 
											$mysqli->query($sql);
											
											// Passage grade grouillot
											passage_grade_grouillot($mysqli, $id, $grade_perso, $xp_perso, $gain_xp);
											
											// recup id perso chef
											$sql = "SELECT id_perso FROM `perso` WHERE idJoueur_perso=(SELECT idJoueur_perso FROM perso WHERE id_perso='$id') AND chef='1' ";
											$res = $mysqli->query($sql);
											$t_chef = $res->fetch_assoc();
											
											$id_perso_chef = $t_chef["id_perso"];
											
											// MAJ PC Chef
											$sql = "UPDATE perso SET pc_perso = pc_perso + $gain_pc WHERE id_perso='$id_perso_chef'";
											$mysqli->query($sql);
											
											if ($id_arme_attaque == 10 || $id_arme_attaque == 11) {
												
												// mise a jour de la table evenement
												$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>','a soigné ','$id_cible','<font color=$couleur_clan_cible><b>$nom_cible</b></font>',' ( Précision : $touche / $precision_final ; Soins : $degats_final ; Gain XP : $gain_xp ; Gain PC : $gain_pc )',NOW(),'0')";
												$mysqli->query($sql);
												
											} else {
											
												// mise a jour de la table evenement
												$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>','a attaqué ','$id_cible','<font color=$couleur_clan_cible><b>$nom_cible</b></font>',' ( Précision : $touche / $precision_final ; Dégâts : $degats_final ; Gain XP : $gain_xp ; Gain PC : $gain_pc )',NOW(),'0')";
												$mysqli->query($sql);
											
											}
											
											$sql = "SELECT pv_perso, x_perso, y_perso, xp_perso, pi_perso, pc_perso, type_perso FROM perso WHERE id_perso='$id_cible'";
											$res = $mysqli->query($sql);
											$tab = $res->fetch_assoc();
											
											$pv_cible 	= $tab["pv_perso"];
											$x_cible 	= $tab["x_perso"];
											$y_cible 	= $tab["y_perso"];
											$xp_cible 	= $tab["xp_perso"];
											$pi_cible 	= $tab["pi_perso"];
											$pc_perso	= $tab["pc_perso"];
											$tp_perso	= $tab["type_perso"];
												
											// il est mort
											if ($pv_cible <= 0) {
												
												if (in_bat($mysqli, $id_cible)) {
													
													// on le supprime du batiment
													$sql = "DELETE FROM perso_in_batiment WHERE id_perso='$id_cible'";
													$mysqli->query($sql);
												}
												else {
											
													// on l'efface de la carte
													$sql = "UPDATE $carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_cible' AND y_carte='$y_cible'";
													$mysqli->query($sql);
												}
							
												// Calcul gains (po et xp)
												$perte_po = gain_po_mort($or_cible);
												
												// Chef
												if ($tp_perso == 1) {
													// Quand un chef meurt, il perd 5% de ses XPi et de ses PC
													// Calcul PI
													$pi_perdu 		= floor(($pi_cible * 5) / 100);
													$pi_perso_fin 	= $pi_cible - $pi_perdu;
													
													// Calcul PC
													$pc_perdu		= floor(($pc_perso * 5) / 100);
													$pc_perso_fin	= $pc_perso - $pc_perdu;
												}
												else {
													// Quand un grouillot meurt, il perd tout ses Pi
													$pi_perso_fin = 0;
													$pc_perso_fin = $pc_perso;
												}
							
												// MAJ perte xp/po/stat cible
												$sql = "UPDATE perso SET or_perso=or_perso-$perte_po, pi_perso=$pi_perso_fin, pc_perso=$pc_perso_fin, nb_mort=nb_mort+1 WHERE id_perso='$id_cible'";
												$mysqli->query($sql);
							
												echo "<div class=\"infoi\">Vous avez capturé votre cible ! <font color=red>Félicitations.</font></div>";
												
												$id_arme_non_equipee = id_arme_non_equipee($mysqli, $id_cible);
												
												$test_perte = mt_rand(0,100);
												
												if ($id_arme_non_equipee > 0) {
													
													// 40% de chance de perdre une arme non équipée
													if ($test_perte <= 40) {
														
														// Suppression de l'arme de l'inventaire du perso
														$sql = "DELETE FROM perso_as_arme WHERE id_perso='$id_cible' AND id_arme='$id_arme_non_equipee' AND est_portee='0' LIMIT 1";
														$mysqli->query($sql);
														
														// Maj charge perso suite perte de l'arme
														$sql = "UPDATE perso SET charge_perso = charge_perso - (SELECT poids_arme FROM arme WHERE id_arme='$id_arme_non_equipee') WHERE id_perso='$id_cible'";
														$mysqli->query($sql);
													}
												}
												
												if (!in_bat($mysqli, $id_cible)) {
													
													if ($perte_po > 0) {
														// On dépose la perte de thune par terre
														// Verification si l'objet existe deja sur cette case
														$sql = "SELECT nb_objet FROM objet_in_carte 
																WHERE objet_in_carte.x_carte = $x_cible 
																AND objet_in_carte.y_carte = $y_cible 
																AND type_objet = '1' AND id_objet = '0'";
														$res = $mysqli->query($sql);
														$to = $res->fetch_assoc();
														
														$nb_o = $to["nb_objet"];
														
														if($nb_o){
															// On met a jour le nombre
															$sql = "UPDATE objet_in_carte SET nb_objet = nb_objet + $perte_po 
																	WHERE type_objet='1' AND id_objet='0'
																	AND x_carte='$x_cible' AND y_carte='$y_cible'";
															$mysqli->query($sql);
														}
														else {
															// Insertion dans la table objet_in_carte : On cree le premier enregistrement
															$sql = "INSERT INTO objet_in_carte (type_objet, id_objet, nb_objet, x_carte, y_carte) VALUES ('1','0','$perte_po','$x_cible','$y_cible')";
															$mysqli->query($sql);
														}
													}
													
													if ($id_arme_non_equipee > 0 && $test_perte <= 40) {
															
														// On dépose la perte de l'arme par terre
														// Verification si l'objet existe deja sur cette case
														$sql = "SELECT nb_objet FROM objet_in_carte 
																WHERE objet_in_carte.x_carte = $x_cible 
																AND objet_in_carte.y_carte = $y_cible 
																AND type_objet = '3' AND id_objet = '$id_arme_non_equipee'";
														$res = $mysqli->query($sql);
														$to = $res->fetch_assoc();
														
														$nb_o = $to["nb_objet"];
														
														if($nb_o){
															// On met a jour le nombre
															$sql = "UPDATE objet_in_carte SET nb_objet = nb_objet + 1 
																	WHERE type_objet='3' AND id_objet='$id_arme_non_equipee'
																	AND x_carte='$x_cible' AND y_carte='$y_cible'";
															$mysqli->query($sql);
														}
														else {
															// Insertion dans la table objet_in_carte : On cree le premier enregistrement
															$sql = "INSERT INTO objet_in_carte (type_objet, id_objet, nb_objet, x_carte, y_carte) VALUES ('3','$id_arme_non_equipee','1','$x_cible','$y_cible')";
															$mysqli->query($sql);
														}
													}
												}
												
												// maj evenements
												$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>','<b>a capturé</b>','$id_cible','<font color=$couleur_clan_cible><b>$nom_cible</b></font>','',NOW(),'0')";
												$mysqli->query($sql);
												
												// maj cv
												$sql = "INSERT INTO `cv` (IDActeur_cv, nomActeur_cv, gradeActeur_cv, IDCible_cv, nomCible_cv, gradeCible_cv, date_cv) VALUES ($id,'<font color=$couleur_clan_perso>$nom_perso</font>', '$nom_grade_perso', '$id_cible','<font color=$couleur_clan_cible>$nom_cible</font>', '$nom_grade_cible', NOW())";
												$mysqli->query($sql);
							
												// maj stats du perso
												$sql = "UPDATE perso SET nb_kill=nb_kill+1 WHERE id_perso=$id";
												$mysqli->query($sql);
												
												// maj stats camp
												if($clan_cible != $clan_perso){
													$sql = "UPDATE stats_camp_kill SET nb_kill=nb_kill+1 WHERE id_camp=$clan_perso";
													$mysqli->query($sql);
												}
												
												// maj dernier tombé
												$sql = "INSERT INTO dernier_tombe (date_capture, id_perso_capture) VALUES (NOW(), '$id_cible')";
												$mysqli->query($sql);
											}
											
											// L'arme fait des dégats de zone
											if ($degatZone_arme_attaque) {
												
												$degats_collat = floor($degats_final / 2);
												
												// Récupération des cibles potentielles autour de la cible principale
												$sql = "SELECT idPerso_carte FROM carte 
														WHERE x_carte >= $x_cible - 1 AND x_carte <= $x_cible + 1 AND y_carte >= $y_cible - 1 AND y_carte <= $y_cible + 1
														AND occupee_carte = '1'
														AND idPerso_carte != '$id_cible'";
												$res_recherche_collat = $mysqli->query($sql);
												
												$gain_xp_collat_cumul = 0;
												$gain_pc_collat_cumul = 0;
												
												// On parcours les cibles pour degats collateraux
												while ($t_recherche_collat = $res_recherche_collat->fetch_assoc()) {
													
													$id_cible_collat = $t_recherche_collat["idPerso_carte"];
													
													if ($id_cible_collat < 50000) {
														
														// Perso
														// Récupération des infos du perso
														$sql = "SELECT idJoueur_perso, nom_perso, image_perso, xp_perso, x_perso, y_perso, pv_perso, pvMax_perso, or_perso, clan, perso_as_grade.id_grade, nom_grade
																FROM perso, perso_as_grade, grades
																WHERE perso_as_grade.id_perso = perso.id_perso
																AND perso_as_grade.id_grade = grades.id_grade
																AND perso.id_perso='$id_cible_collat'";
														$res = $mysqli->query($sql);
														$t_collat = $res->fetch_assoc();
														
														$id_joueur_collat 	= $t_collat["idJoueur_perso"];
														$nom_collat			= $t_collat["nom_perso"];
														$xp_collat 			= $t_collat["xp_perso"];
														$x_collat 			= $t_collat["x_perso"];
														$y_collat 			= $t_collat["y_perso"];
														$pv_collat 			= $t_collat["pv_perso"];
														$pvM_collat 		= $t_collat["pvMax_perso"];
														$or_collat 			= $t_collat["or_perso"];
														$image_perso_collat = $t_collat["image_perso"];
														$clan_collat 		= $t_collat["clan"];
														$grade_collat		= $t_collat['id_grade'];
														$nom_grade_collat	= $t_collat['nom_grade'];
														
														// Récupération de la couleur associée au clan de la cible
														$couleur_clan_collat = couleur_clan($clan_collat);
														
														$gain_xp_collat = 1;
														if ($gain_xp_tour_perso + $gain_xp_collat > 20) {
															$gain_xp_collat = 0;
															$max_xp_tour_atteint = true;
														}
														
														$gain_xp_collat_cumul += $gain_xp_collat;
														$gain_pc_collat_cumul += 1;
														
														$gain_pc_collat = calcul_gain_pc_attaque_perso($grade_perso, $grade_collat, $clan_perso, $clan_collat, $type_perso, $id_j_perso, $id_joueur_collat);
														
														// Limite 3 PC par attaque de Gatling
														if ($id_arme_attaque == 14 && $gain_pc + $gain_pc_collat_cumul > 3) {
															$gain_pc_collat = 0;
														}
														
														// mise a jour des pv et des malus de la cible
														$sql = "UPDATE perso SET pv_perso=pv_perso-$degats_collat, bonus_perso=bonus_perso-2 WHERE id_perso='$id_cible_collat'";
														$mysqli->query($sql);
														
														echo "<br>Vous avez infligé $degats_collat dégâts collatéraux à <font color='$couleur_clan_collat'>$nom_collat</font> - Matricule $id_cible_collat.<br>";
														
														// Limite 3XP par attaque de Gatling
														if ($id_arme_attaque == 14 && $gain_xp_collat_cumul + $gain_xp <= 3 && !$max_xp_tour_atteint) {
															echo "Vous avez gagné $gain_xp_collat xp.<br><br>";
														
															// mise a jour des xp/pi
															$sql = "UPDATE perso SET xp_perso=xp_perso+$gain_xp_collat, pi_perso=pi_perso+$gain_xp_collat, gain_xp_tour=gain_xp_tour+$gain_xp_collat WHERE id_perso='$id'"; 
															$mysqli->query($sql);
															
															// Passage grade grouillot
															passage_grade_grouillot($mysqli, $id, $grade_perso, $xp_perso, $gain_xp_collat);
														}
														else if ($gain_xp_collat_cumul <= 4 && !$max_xp_tour_atteint) {
															echo "Vous avez gagné $gain_xp_collat xp.<br><br>";
														
															// mise a jour des xp/pi
															$sql = "UPDATE perso SET xp_perso=xp_perso+$gain_xp_collat, pi_perso=pi_perso+$gain_xp_collat, gain_xp_tour=gain_xp_tour+$gain_xp_collat WHERE id_perso='$id'"; 
															$mysqli->query($sql);
															
															// Passage grade grouillot
															passage_grade_grouillot($mysqli, $id, $grade_perso, $xp_perso, $gain_xp_collat);
															
														} else {
															echo "Vous avez gagné 0 xp";
															if ($max_xp_tour_atteint) {
																echo " (maximum de gain d'xp par tour atteint)";
															}
															else {
																echo " (maximum de gain d'xp par attaque atteint)";
															}
															
															echo ".<br><br>";
														}
														
														// recup id perso chef
														$sql = "SELECT id_perso FROM `perso` WHERE idJoueur_perso=(SELECT idJoueur_perso FROM perso WHERE id_perso='$id') AND chef='1' ";
														$res = $mysqli->query($sql);
														$t_chef = $res->fetch_assoc();
														
														$id_perso_chef = $t_chef["id_perso"];
														
														// mise à jour des PC du chef											
														$sql = "UPDATE perso SET pc_perso = pc_perso + $gain_pc_collat WHERE id_perso='$id_perso_chef'";
														$mysqli->query($sql);
														
														// mise a jour de la table evenement
														$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>','a infligé des dégâts collatéraux ','$id_cible_collat','<font color=$couleur_clan_collat><b>$nom_collat</b></font>',' ( Dégâts : $degats_collat ; Gain XP : $gain_xp_collat ; Gain PC : $gain_pc_collat )',NOW(),'0')";
														$mysqli->query($sql);
														
														$sql = "SELECT pv_perso, x_perso, y_perso, xp_perso, pi_perso, pc_perso, type_perso FROM perso WHERE id_perso='$id_cible_collat'";
														$res = $mysqli->query($sql);
														$tab = $res->fetch_assoc();
														
														$pv_collat_fin 	= $tab["pv_perso"];
														$x_collat_fin 	= $tab["x_perso"];
														$y_collat_fin 	= $tab["y_perso"];
														$xp_collat_fin 	= $tab["xp_perso"];
														$pi_collat_fin 	= $tab["pi_perso"];
														$pc_collat_fin	= $tab["pc_perso"];
														$tp_collat_fin	= $tab["type_perso"];
															
														// il est mort
														if ($pv_collat_fin <= 0) {
														
															// on l'efface de la carte
															$sql = "UPDATE $carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_collat_fin' AND y_carte='$y_collat_fin'";
															$mysqli->query($sql);
										
															// Calcul gains (po et xp)
															$perte_po = gain_po_mort($or_collat);
															
															// Chef
															if ($tp_collat_fin == 1) {
																// Quand un chef meurt, il perd 5% de ses XP,XPi et de ses PC
																// Calcul PI
																$pi_perdu 		= floor(($pi_collat_fin * 5) / 100);
																$pi_perso_fin 	= $pi_collat_fin - $pi_perdu;
																
																// Calcul PC
																$pc_perdu		= floor(($pc_collat_fin * 5) / 100);
																$pc_perso_fin	= $pc_collat_fin - $pc_perdu;
															}
															else {
																// Quand un grouillot meurt, il perd tout ses Pi
																$pi_perso_fin = 0;
																$pc_perso_fin = $pc_collat_fin;
															}
										
															// MAJ perte xp/po/stat cible
															$sql = "UPDATE perso SET or_perso=or_perso-$perte_po, pi_perso=$pi_perso_fin, pc_perso=$pc_perso_fin, nb_mort=nb_mort+1 WHERE id_perso='$id_cible_collat'";
															$mysqli->query($sql);
															
															if ($perte_po > 0) {
																// On dépose la perte de PO par terre
																// Verification si l'objet existe deja sur cette case
																$sql = "SELECT nb_objet FROM objet_in_carte 
																		WHERE objet_in_carte.x_carte = $x_collat_fin 
																		AND objet_in_carte.y_carte = $y_collat_fin 
																		AND type_objet = '1' AND id_objet = '0'";
																$res = $mysqli->query($sql);
																$to = $res->fetch_assoc();
																
																$nb_o = $to["nb_objet"];
																
																if($nb_o){
																	// On met a jour le nombre
																	$sql = "UPDATE objet_in_carte SET nb_objet = nb_objet + $perte_po 
																			WHERE type_objet='1' AND id_objet='0'
																			AND x_carte='$x_collat_fin' AND y_carte='$y_collat_fin'";
																	$mysqli->query($sql);
																}
																else {
																	// Insertion dans la table objet_in_carte : On cree le premier enregistrement
																	$sql = "INSERT INTO objet_in_carte (type_objet, id_objet, nb_objet, x_carte, y_carte) VALUES ('1','0','$perte_po','$x_collat_fin','$y_collat_fin')";
																	$mysqli->query($sql);
																}
															}
															
															$id_arme_non_equipee = id_arme_non_equipee($mysqli, $id_cible_collat);
															
															if ($id_arme_non_equipee > 0) {
														
																$test_perte = mt_rand(0,100);
																
																// 40% de chance de perdre une arme non équipée
																if ($test_perte <= 40) {
																	
																	// Suppression de l'arme de l'inventaire du perso
																	$sql = "DELETE FROM perso_as_arme WHERE id_perso='$id_cible_collat' AND id_arme='$id_arme_non_equipee' AND est_portee='0' LIMIT 1";
																	$mysqli->query($sql);
																	
																	// Maj charge perso suite perte de l'arme
																	$sql = "UPDATE perso SET charge_perso = charge_perso - (SELECT poids_arme FROM arme WHERE id_arme='$id_arme_non_equipee') WHERE id_perso='$id_cible_collat'";
																	$mysqli->query($sql);
																	
																	// On dépose la perte de thune par terre
																	// Verification si l'objet existe deja sur cette case
																	$sql = "SELECT nb_objet FROM objet_in_carte 
																			WHERE objet_in_carte.x_carte = $x_collat_fin 
																			AND objet_in_carte.y_carte = $y_collat_fin 
																			AND type_objet = '3' AND id_objet = '$id_arme_non_equipee'";
																	$res = $mysqli->query($sql);
																	$to = $res->fetch_assoc();
																	
																	$nb_o = $to["nb_objet"];
																	
																	if($nb_o){
																		// On met a jour le nombre
																		$sql = "UPDATE objet_in_carte SET nb_objet = nb_objet + 1 
																				WHERE type_objet='3' AND id_objet='$id_arme_non_equipee'
																				AND x_carte='$x_collat_fin' AND y_carte='$y_collat_fin'";
																		$mysqli->query($sql);
																	}
																	else {
																		// Insertion dans la table objet_in_carte : On cree le premier enregistrement
																		$sql = "INSERT INTO objet_in_carte (type_objet, id_objet, nb_objet, x_carte, y_carte) VALUES ('3','$id_arme_non_equipee','1','$x_collat_fin','$y_collat_fin')";
																		$mysqli->query($sql);
																	}
																}
															}
										
															echo "<div class=\"infoi\">Vous avez capturé <font color='$couleur_clan_collat'>$nom_collat</font> - Matricule $id_cible_collat ! <font color=red>Félicitations.</font></div>";
															
															// maj evenements
															$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>','<b>a capturé</b>','$id_cible_collat','<font color=$couleur_clan_collat><b>$nom_collat</b></font>','',NOW(),'0')";
															$mysqli->query($sql);
															
															// maj cv
															$sql = "INSERT INTO `cv` (IDActeur_cv, nomActeur_cv, gradeActeur_cv, IDCible_cv, nomCible_cv, gradeCible_cv, date_cv) VALUES ($id,'<font color=$couleur_clan_perso>$nom_perso</font>', '$nom_grade_perso', '$id_cible_collat','<font color=$couleur_clan_collat>$nom_collat</font>', '$nom_grade_collat', NOW())";
															$mysqli->query($sql);
										
															// maj stats du perso
															$sql = "UPDATE perso SET nb_kill=nb_kill+1 WHERE id_perso=$id";
															$mysqli->query($sql);
															
															// maj stats camp
															if($clan_collat != $clan_perso){
																$sql = "UPDATE stats_camp_kill SET nb_kill=nb_kill+1 WHERE id_camp=$clan_perso";
																$mysqli->query($sql);
															}
															
															// maj dernier tombé
															$sql = "INSERT INTO dernier_tombe (date_capture, id_perso_capture) VALUES (NOW(), '$id_cible_collat')";
															$mysqli->query($sql);
														}
														
													} else if ($id_cible_collat >= 200000) {
														
														// PNJ
														// Récupération des infos du PNJ	
														$sql = "SELECT pnj.id_pnj, nom_pnj, pv_i, x_i, y_i, pv_i, pvMax_pnj, protec_pnj 
																FROM pnj, instance_pnj 
																WHERE pnj.id_pnj=instance_pnj.id_pnj AND idInstance_pnj='$id_cible_collat'";
														$res = $mysqli->query($sql);
														$t_cible = $res->fetch_assoc();
														
														$id_pnj_collat 			= $t_cible["id_pnj"];
														$nom_cible_collat 		= $t_cible["nom_pnj"];
														$pv_cible_collat 		= $t_cible["pv_i"];
														$x_cible_collat 		= $t_cible["x_i"];
														$y_cible_collat 		= $t_cible["y_i"];
														$pv_cible_collat 		= $t_cible["pv_i"];
														$pvMax_cible_collat 	= $t_cible["pvMax_pnj"];
														$protec_cible_collat	= $t_cible["protec_pnj"];
														$image_pnj_collat 		= "pnj".$t_cible["id_pnj"]."t.png";
														
														$gain_xp_collat = 1;
														if ($gain_xp_tour_perso + $gain_xp_collat > 20) {
															$gain_xp_collat = 0;
															$max_xp_tour_atteint = true;
														}
														
														$gain_xp_collat_cumul += $gain_xp_collat;
														
														// mise a jour des pv de la cible
														$sql = "UPDATE instance_pnj SET pv_i=pv_i-$degats_collat, dernierAttaquant_i=$id WHERE idInstance_pnj='$id_cible_collat'";
														$mysqli->query($sql);
														
														echo "<br>Vous avez infligé $degats_collat dégâts collatéraux à $nom_cible_collat<br>";
														
														// Limite 3XP par attaque de Gatling
														if ($id_arme_attaque == 14 && $gain_xp_collat_cumul + $gain_xp <= 3 && !$max_xp_tour_atteint) {
															echo "Vous avez gagné $gain_xp_collat xp.<br><br>";
														
															// mise a jour des xp/pi
															$sql = "UPDATE perso SET xp_perso=xp_perso+$gain_xp_collat, pi_perso=pi_perso+$gain_xp_collat, gain_xp_tour=gain_xp_tour+$gain_xp_collat WHERE id_perso='$id'"; 
															$mysqli->query($sql);
															
															// Passage grade grouillot
															passage_grade_grouillot($mysqli, $id, $grade_perso, $xp_perso, $gain_xp_collat);
														}
														else if ($gain_xp_collat_cumul <= 4 && !$max_xp_tour_atteint) {
															echo "Vous avez gagné $gain_xp_collat xp.<br><br>";
														
															// mise a jour des xp/pi
															$sql = "UPDATE perso SET xp_perso=xp_perso+$gain_xp_collat, pi_perso=pi_perso+$gain_xp_collat, gain_xp_tour=gain_xp_tour+$gain_xp_collat WHERE id_perso='$id'"; 
															$mysqli->query($sql);
															
															// Passage grade grouillot
															passage_grade_grouillot($mysqli, $id, $grade_perso, $xp_perso, $gain_xp_collat);
															
														} else {
															echo "Vous avez gagné 0 xp";
															if ($max_xp_tour_atteint) {
																echo " (maximum de gain d'xp par tour atteint)";
															}
															else {
																echo " (maximum de gain d'xp par attaque atteint)";
															}
															
															echo ".<br><br>";
														}
														
														// maj evenement
														$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>','a infligé des dégâts collatéraux ','$id_cible_collat','<b>$nom_cible_collat</b>',' ( Dégâts : $degats_collat ; Gain XP : $gain_xp_collat ; Gain PC : 0 )',NOW(),'0')";
														$mysqli->query($sql);
														
														// recuperation des données du pnj aprés attaque
														$sql = "SELECT pv_i, x_i, y_i FROM instance_pnj WHERE idInstance_pnj='$id_cible_collat'";
														$res = $mysqli->query($sql);
														$tab = $res->fetch_assoc();
														
														$pv_cible_collat 	= $tab["pv_i"];
														$x_cible_collat 	= $tab["x_i"];
														$y_cible_collat 	= $tab["y_i"];
															
														// il est mort
														if ($pv_cible_collat <= 0) {
														
															echo "Vous avez tué $nom_cible_collat avec des dégâts collatéraux ! <font color=red>Félicitations.</font>";
														
															// on l'efface de la carte
															$sql = "UPDATE $carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_cible_collat' AND y_carte='$y_cible_collat'";
															$mysqli->query($sql);
															
															// on le delete
															$sql = "DELETE FROM instance_pnj WHERE idInstance_pnj='$id_cible_collat'";
															$mysqli->query($sql);
															
															// verification que le perso n'a pas déjà tué ce type de pnj
															$sql_v = "SELECT id_pnj FROM perso_as_killpnj WHERE id_pnj='$id_pnj_collat' AND id_perso='$id'";
															$res_v = $mysqli->query($sql_v);
															$verif_pnj = $res_v->num_rows;
															
															// nb_pnj 
															$sql = "UPDATE perso SET nb_pnj=nb_pnj+1 WHERE id_perso='$id'";
															$mysqli->query($sql);
															
															if($verif_pnj == 0){
																// il n'a jamais tué de pnj de ce type => insert
																$sql = "INSERT INTO perso_as_killpnj VALUES('$id','$id_pnj_collat','1')";
																$mysqli->query($sql);
															}
															else { 
																// il en a déjà tué => update
																$sql = "UPDATE perso_as_killpnj SET nb_pnj=nb_pnj+1 WHERE id_perso='$id' AND id_pnj='$id_pnj_collat'";
																$mysqli->query($sql);
															}
															
															// maj evenement
															$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>','a tué','$id_cible_collat','<b>$nom_cible_collat</b>','',NOW(),'0')";
															$mysqli->query($sql);
															
															// maj cv
															$sql = "INSERT INTO `cv` (IDActeur_cv, nomActeur_cv, gradeActeur_cv, IDCible_cv, nomCible_cv, date_cv) VALUES ($id,'<font color=$couleur_clan_perso>$nom_perso</font>', '$nom_grade_perso', '$id_cible_collat','$nom_cible_collat',NOW())";
															$mysqli->query($sql);
															
															echo "<br><center><a class='btn btn-primary' href=\"jouer.php\">retour</a></center>";
														}
													} else {
														// Batiment => pas de collat sur batiment
													}
												}
											}
										}
										else { // la cible a esquivé l'attaque
							
											if ($touche >= 98) {
												echo "<b>Echec critique !</b>";
											}
											echo "<br>Vous avez raté votre cible.<br><br>";
											
											if ($id_arme_attaque != 11 && $id_arme_attaque != 10) {
											
												if ($touche >= 98) {
													// Echec critique !
													// Ajout d'un malus supplémentaire à l'attaquant
													$sql = "UPDATE perso SET bonus_perso = bonus_perso - 1 WHERE id_perso='$id'";
												} else {
													// ajout malus cible
													$sql = "UPDATE perso SET bonus_perso = bonus_perso - 1 WHERE id_perso='$id_cible'";
												}
												$mysqli->query($sql);
												
												// Gain de 2 XP si esquive attaque d'un perso d'un autre camp
												if($clan_cible != $clan_perso && !$max_xp_tour_atteint){
													$sql = "UPDATE perso SET xp_perso = xp_perso + 2, pi_perso = pi_perso + 2, gain_xp_tour=gain_xp_tour + 2 WHERE id_perso='$id_cible'";
													$mysqli->query($sql);
												}
											}
											else {
												if ($touche >= 98) {
													// Echec critique !
													// Ajout d'un malus supplémentaire à l'attaquant
													$sql = "UPDATE perso SET bonus_perso = bonus_perso - 1 WHERE id_perso='$id'";
													$mysqli->query($sql);
												}
											}
											
											// Insertion log attaque
											$message_log = $id.' a raté son attaque sur '.$id_cible;
											$type_action = "Attaque ".$id_arme_attaque;
											$sql = "INSERT INTO log (date_log, id_perso, type_action, id_arme, pourcentage, message_log) VALUES (NOW(), '$id', '$type_action', '$id_arme_attaque', '$touche', '$message_log')";
											$mysqli->query($sql);
												
											// maj evenement
											$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_cible,'<font color=$couleur_clan_cible><b>$nom_cible</b></font>','a esquivé l\'attaque de','$id','<font color=$couleur_clan_perso><b>$nom_perso</b></font>',' ( Précision : $touche / $precision_final ; Gain XP : 0)',NOW(),'0')";
											$mysqli->query($sql);
							
										}
										
										//mise a jour des pa
										$sql = "UPDATE perso SET pa_perso=pa_perso-$coutPa_arme_attaque WHERE id_perso='$id'";
										$res = $mysqli->query($sql); 
										
										if ($id_arme_attaque == 10 || $id_arme_attaque == 11) {
											$texte_submit = "soigner à nouveau";
										} else {
											$texte_submit = "attaquer à nouveau";
										}
										
										if ($pv_cible > 0 && !$soin_termine) {
											?>
												<br />
												<form action="agir.php" method="post">
													<input type="hidden" name="re_attaque_hid" value="<?php echo $id_cible.",".$id_arme_attaque;?>" />
													<input type="submit" name="re_attaque" value="<?php echo $texte_submit; ?>" />
												</form> 
											<?php
										}
										?>
										<br /><br />
										<center><a class='btn btn-primary' href="jouer.php">retour</a></center>
										<?php
									}
									else {
										echo "Loi anti-zerk non respectée !";
										echo "<br><center><a class='btn btn-primary' href=\"jouer.php\">retour</a></center>";
									}
								}			
								else {
									//la cible est déjà morte
									echo "Erreur : La cible est déjà morte !";
									echo "<br><center><a class='btn btn-primary' href=\"jouer.php\">retour</a></center>";
								}
							}
						}
						else { 
							if($id_cible == $id){
								echo "Erreur : Vous ne pouvez pas vous attaquez vous même...";
								echo "<br /><center><a class='btn btn-primary' href=\"jouer.php\">retour</a></center>";
							}
							else {
								// la cible n'est pas à portée d'attaque
								echo "Erreur : La cible n'est pas à portée d'attaque (Vérifiez la portée de votre arme) ou votre état ne vous permet pas de la cibler (pas assez de perception)  !";
								echo "<br /><center><a class='btn btn-primary' href=\"jouer.php\">retour</a></center>";
							}
						}
					}
					else {
						echo "Erreur : vous avez été capturé entre temps !";
						echo "<br /><center><a class='btn btn-primary' href=\"jouer.php\">retour</a></center>";
					}
				}
				else {
					echo "Erreur : Il est impossible d'attaquer un perso depuis l'intérieur d'un batiment avec une arme de Corps à corps!";
					echo "<br /><center><a class='btn btn-primary' href=\"jouer.php\">retour</a></center>";
				}
			}
			else {
				// Tentative de triche
				echo "Pas bien d'essayer de tricher !";
				echo "<br /><center><a class='btn btn-primary' href=\"jouer.php\">retour</a></center>";
				
				$text_triche = "Tentative attaque avec arme qu'on ne possède pas";
				
				$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id', '$text_triche')";
				$mysqli->query($sql);
			}
		}
		
		//traitement de l'attaque sur un pnj
		if ((isset($id_attaque) && $id_attaque != "" && $id_attaque != "personne" && $id_attaque >= 200000) ) {
		
			$id_cible =  $id_attaque;
			$verif_arme = 0;
			
			// arme bien passée
			if(isset($id_arme_attaque) && $id_arme_attaque != 1000 && $id_arme_attaque != 2000) {
				
				// Est-ce que le perso possède bien l'arme ?
				$sql = "SELECT * FROM perso_as_arme WHERE id_perso='$id' AND id_arme='$id_arme_attaque' AND est_portee='1'";
				$res = $mysqli->query($sql);
				$verif_arme = $res->num_rows;
				
				// Recupération des caracs de l'arme
				$sql = "SELECT nom_arme, coutPa_arme, porteeMin_arme, porteeMax_arme, degatMin_arme, degatMax_arme, degatZone_arme, precision_arme, valeur_des_arme
						FROM arme WHERE id_arme='$id_arme_attaque'";
				$res = $mysqli->query($sql);
				$t_a = $res->fetch_assoc();
				
				$nom_arme_attaque 				= $t_a["nom_arme"];
				$coutPa_arme_attaque 			= $t_a["coutPa_arme"];
				$porteeMin_arme_attaque 		= $t_a["porteeMin_arme"];
				$porteeMax_arme_attaque 		= $t_a["porteeMax_arme"];
				$valeur_des_arme_attaque		= $t_a["valeur_des_arme"];
				$degatMin_arme_attaque 			= $t_a["degatMin_arme"];
				$degatMax_arme_attaque 			= $t_a["degatMax_arme"];
				$precision_arme_attaque 		= $t_a["precision_arme"];
				$degatZone_arme_attaque 		= $t_a["degatZone_arme"];
			}
			else {
				if ($id_arme_attaque == 1000) {
					
					// Poings = arme de Corps a corps					
					$nom_arme_attaque 				= "Poings";
					$coutPa_arme_attaque 			= 3;
					$porteeMin_arme_attaque 		= 1;
					$porteeMax_arme_attaque 		= 1;
					$valeur_des_arme_attaque		= 6;
					$degatMin_arme_attaque 			= 4;
					$degatMax_arme_attaque 			= 4;
					$precision_arme_attaque			= 30;
					$degatZone_arme_attaque 		= 0;
					
					$verif_arme = 1;				
					
				} else if ($id_arme_attaque == 2000) {
					
					// Cailloux
					$nom_arme_attaque 				= "Cailloux";
					$coutPa_arme_attaque 			= 3;
					$porteeMin_arme_attaque 		= 1;
					$porteeMax_arme_attaque 		= 2;
					$valeur_des_arme_attaque		= 6;
					$degatMin_arme_attaque 			= 5;
					$degatMax_arme_attaque 			= 5;
					$precision_arme_attaque			= 25;
					$degatZone_arme_attaque 		= 0;
					
					$verif_arme = 1;
				}
			}
			
			if ($porteeMax_arme_attaque > 1 && possede_lunette_visee($mysqli, $id)) {
				$coutPa_arme_attaque = $coutPa_arme_attaque + 1;
			}
			
			if ($verif_arme) {
			
				// recup des données du perso
				$sql = "SELECT type_perso, nom_perso, idJoueur_perso, image_perso, xp_perso, x_perso, y_perso, pm_perso, pi_perso, pv_perso, pvMax_perso, pmMax_perso, pa_perso, paMax_perso, recup_perso, bonusRecup_perso, bonusPM_perso, perception_perso, bonusPerception_perso, dateCreation_perso, clan, gain_xp_tour, perso_as_grade.id_grade, nom_grade
						FROM perso, perso_as_grade, grades
						WHERE perso_as_grade.id_perso = perso.id_perso
						AND perso_as_grade.id_grade = grades.id_grade
						AND perso.id_perso='$id'";
				$res = $mysqli->query($sql);
				$t_perso = $res->fetch_assoc();
				
				$nom_perso 		= $t_perso["nom_perso"];
				$image_perso 	= $t_perso["image_perso"];
				$xp_perso 		= $t_perso["xp_perso"];
				$x_perso 		= $t_perso["x_perso"];
				$y_perso 		= $t_perso["y_perso"];
				$pm_perso 		= $t_perso["pm_perso"];
				$pmM_perso 		= $t_perso["pmMax_perso"];
				$pi_perso 		= $t_perso["pi_perso"];
				$pv_perso 		= $t_perso["pv_perso"];
				$pvM_perso 		= $t_perso["pvMax_perso"];
				$pa_perso 		= $t_perso["pa_perso"];
				$paM_perso 		= $t_perso["paMax_perso"];
				$rec_perso 		= $t_perso["recup_perso"];
				$br_perso 		= $t_perso["bonusRecup_perso"];
				$bPM_perso		= $t_perso["bonusPM_perso"];
				$per_perso 		= $t_perso["perception_perso"];
				$bp_perso 		= $t_perso["bonusPerception_perso"];
				$dc_perso 		= $t_perso["dateCreation_perso"];
				$id_j_perso		= $t_perso["idJoueur_perso"];
				$clan_perso 	= $t_perso["clan"];
				$grade_perso 	= $t_perso["id_grade"];
				$type_perso		= $t_perso["type_perso"];
				$nom_grade_perso= $t_perso["nom_grade"];
				$gain_xp_tour_perso = $t_perso["gain_xp_tour"];
				
				if ($pv_perso > 0) {
				
					if ($gain_xp_tour_perso >= 20) {
						$max_xp_tour_atteint = true;
					}
					else {
						$max_xp_tour_atteint = false;
					}
					
					// Récupération de la couleur associée au clan du perso
					$couleur_clan_perso = couleur_clan($clan_perso);
					
					if(is_a_portee_attaque($mysqli, $carte, $id, $id_cible, $porteeMin_arme_attaque, $porteeMax_arme_attaque, $per_perso)) {
							
						// recuperation des données du pnj		
						$sql = "SELECT pnj.id_pnj, nom_pnj, degatMin_pnj, degatMax_pnj, pv_i, x_i, y_i, bonus_i, pm_pnj, pv_i, pvMax_pnj, protec_pnj 
								FROM pnj, instance_pnj 
								WHERE pnj.id_pnj=instance_pnj.id_pnj AND idInstance_pnj='$id_cible'";
						$res = $mysqli->query($sql);
						$t_cible = $res->fetch_assoc();
						
						$id_pnj 			= $t_cible["id_pnj"];
						$nom_cible 			= $t_cible["nom_pnj"];
						$pv_cible 			= $t_cible["pv_i"];
						$degatMin 			= $t_cible["degatMin_pnj"];
						$degatMax 			= $t_cible["degatMax_pnj"];
						$x_cible 			= $t_cible["x_i"];
						$y_cible 			= $t_cible["y_i"];
						$pm_cible 			= $t_cible["pm_pnj"];
						$pv_cible 			= $t_cible["pv_i"];
						$bonus_cible 		= $t_cible["bonus_i"];
						$pvMax_cible 		= $t_cible["pvMax_pnj"];
						$protec_cible		= $t_cible["protec_pnj"];
						$image_pnj 			= "pnj".$t_cible["id_pnj"]."t.png";
						
						// on verifie si le perso a déja tué ce type de pnj et on en récupère le nombre
						$nb_pnj_t = is_deja_tue_pnj($mysqli, $id, $id_pnj);
						
						$pa_restant = $pa_perso - $coutPa_arme_attaque;
						
						if($pa_restant <= 0){
							$pa_restant = 0;
						}
						?>
						<table border=0 width=100%>
							<tr height=50%>
								<td width=50%>	
									<table border=1 height=100% width=100%>		
										<tr>
											<td width=25%>	
												<table border=0 width=100%>
													<tr>
														<td align="center">
															<div width=40 height=40 style="position: relative;">
																<div style="position: absolute;bottom: 0;text-align: center; width: 100%;font-weight: bold;"><?php echo $id; ?></div>
																<img class="" border=0 src="../images_perso/<?php echo $image_perso; ?>" width=40 height=40 />
															</div>
														</td>
													</tr>
												</table>
											</td>
											<td width=75%>
												<table border=0 width=100%>
													<tr>
														<td><?php echo "<u><b>Pseudo :</b></u> ".$nom_perso; ?></td>
													</tr>
													<tr>
														<td><?php echo "<u><b>Xp :</b></u> ".$xp_perso." - <u><b>Pi :</b></u> ".$pi_perso.""; ?></td>
													</tr>
													<tr>
														<td><?php echo "<u><b>Position sur la carte :</b></u> ".$x_perso."/".$y_perso; ?></td>
													</tr>
													<tr>
														<td><?php echo "<u><b>Mouvements restants :</b></u> ".$pm_perso."/".($pmM_perso + $bPM_perso); ?><?php echo " - <u><b>Points de vie :</b></u> ".$pv_perso."/".$pvM_perso; ?></td>
													</tr>
													<tr>
														<td><?php echo "<u><b>Points d'action :</b></u> ".$pa_restant."/".$paM_perso; ?></td>
													</tr>
													<tr>
														<td><?php echo "<u><b>Recup :</b></u> ".$rec_perso; if($br_perso) echo " (+".$br_perso.")"; ?><?php echo " - <u><b>Perception :</b></u> ".$per_perso; if($bp_perso) {if($bp_perso>0) echo " (+".$bp_perso.")"; else echo " (".$bp_perso.")";} ?></td>
													</tr>
												</table>
											</td>
										</tr>
									</table>
								</td>
								<td width=50%>
									<table border=1 height=100% width=100%>		
										<tr>
											<td width=25%>	
												<table border=0 width=100%>
													<tr>
														<td align="center"><img src="../images/pnj/<?php echo $image_pnj; ?>"></td>
													</tr>
												</table>
											</td>
											<td width=75%>
												<table border=0 width=100%>
													<tr>
														<td><?php echo "<u><b>Cible :</b></u> ".$nom_cible.""; ?></td>
													</tr>
													<tr>
														<td><?php echo "<u><b>Position sur la carte :</b></u> ".$x_cible."/".$y_cible; ?></td>
													</tr>
												</table>
											</td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
						<?php
						
						//le perso n'a pas assez de pa pour faire cette attaque
						if ($pa_perso < $coutPa_arme_attaque) {
							echo "<div class=\"erreur\" align=\"center\">Vous n'avez pas assez de PA pour effectuer cette action !</div>";
							echo "<center><a class='btn btn-primary' href=\"jouer.php\">retour</a></center>";
							
						}	
						else { 
							//le perso a assez de pa
								
							//la cible est encore en vie
							if ($pv_cible > 0) {

								// maj dernierAttaquant_i
								$sql = "UPDATE instance_pnj SET dernierAttaquant_i = $id WHERE idInstance_pnj = '$id_cible'";
								$mysqli->query($sql);
										
								echo "Vous avez lancé une attaque sur <b>$nom_cible [$id_cible]</b> avec $nom_arme_attaque<br/>";
									
								// Calcul touche
								$touche = mt_rand(0,100);
								
								// Où se trouve la cible ?
								$sql = "SELECT fond_carte FROM carte WHERE x_carte='$x_cible' AND y_carte='$y_cible'";
								$res = $mysqli->query($sql);
								$t = $res->fetch_assoc();
								
								$fond_carte_cible = $t['fond_carte'];
								
								$bonus_defense_terrain = get_bonus_defense_terrain($fond_carte_cible, $porteeMax_arme_attaque);
								
								// Bonus Précision batiment
								$bonus_precision_bat = 0;
								
								if (in_bat($mysqli, $id)) {
									
									$id_inst_bat_perso = in_bat($mysqli, $id);
									
									$sql = "SELECT id_batiment FROM instance_batiment WHERE id_instanceBat='$id_inst_bat_perso'";
									$res = $mysqli->query($sql);
									$t = $res->fetch_assoc();
									
									$id_bat_perso = $t['id_batiment'];
									
									$bonus_precision_bat = get_bonus_attaque_from_batiment($id_bat_perso);
								}
								else if (in_train($mysqli, $id)) {
									$bonus_precision_bat = -30;
								}
								
								$precision_final = $precision_arme_attaque - $bonus_cible - $bonus_defense_terrain + $bonus_precision_bat;
								
								$bonus_precision_objet = 0;
								if ($porteeMax_arme_attaque == 1) {
									$bonus_precision_objet = getBonusPrecisionCacObjet($mysqli, $id);
								}
								else {
									$bonus_precision_objet = getBonusPrecisionDistObjet($mysqli, $id);
								}
								
								$precision_final += $bonus_precision_objet;
								
								echo "Votre score de touche : ".$touche."<br>";
								echo "Précision : ".$precision_final. " (Base arme : ".$precision_arme_attaque;
								if ($bonus_precision_bat != 0) {
									echo " -- Bonus du batiment : ".$bonus_precision_bat;
								}
								echo " -- Defense cible : ".$bonus_cible." -- Defense terrain : ".$bonus_defense_terrain." -- Bonus Précision objet : ".$bonus_precision_objet.")<br>";
								
								// Score touche <= precision arme utilisée - bonus cible pour l'attaque = La cible est touchée
								if ($touche <= $precision_final && $touche < 98) {
					
									// calcul degats arme
									$degats_tmp 	= calcul_des_attaque($degatMin_arme_attaque, $valeur_des_arme_attaque);
									$degats_final 	= $degats_tmp - $protec_cible;
									
									// Insertion log attaque
									$message_log = $id.' a attaqué '.$id_cible;
									$type_action = "Attaque ".$id_arme_attaque;
									$sql = "INSERT INTO log (date_log, id_perso, type_action, id_arme, degats, pourcentage, message_log) VALUES (NOW(), '$id', '$type_action', '$id_arme_attaque', '$degats_tmp', '$touche', '$message_log')";
									$mysqli->query($sql);
									
									if($degats_final < 0) {
										$degats_final = 0;
									}
									
									if ($touche <= 2) {
										// Coup critique ! Dégats et Gains PC X 2
										$degats_final = $degats_final * 2;
									}
									
									// TODO - calcul gain XP selon pnj
									$gain_xp = mt_rand(1, 4);
									
									// Limite 3XP par attaque de Gatling
									if ($id_arme_attaque == 14 && $gain_xp > 3) {
										$gain_xp = 3;
									}
									
									if ($gain_xp_tour_perso + $gain_xp > 20) {
										$gain_xp = 20 - $gain_xp_tour_perso;
										$max_xp_tour_atteint = true;
									}
									
									if ($id_arme_attaque == 10) {
											
										// Seringue
										if ($pv_cible + $degats_final >= $pvMax_cible) {
											$degats_final = $pvMax_cible - $pv_cible;
										}
											
										// mise a jour des pv
										$sql = "UPDATE instance_pnj SET pv_i = pv_i + $degats_final WHERE idInstance_pnj = '$id_cible'";
										$mysqli->query($sql);
											
										echo "<br>Vous avez soigné $degats_final dégâts à la cible.<br><br>";
											
									} else if ($id_arme_attaque == 11) {
											
										// Bandage
										echo "<br>Vous avez soigné $degats_final malus à la cible.<br><br>";
											
									} else {
									
										// mise a jour des pv du pnj
										$sql = "UPDATE instance_pnj SET pv_i = pv_i - $degats_final WHERE idInstance_pnj = '$id_cible'";
										$mysqli->query($sql);
										
										echo "<br>Vous avez infligé <b>$degats_final</b> dégâts à la cible.<br><br>";
									}
									
									echo "Vous avez gagné <b>$gain_xp</b> xp.";
									
									// maj gain xp / pi perso
									$sql = "UPDATE perso SET xp_perso=xp_perso+$gain_xp, pi_perso=pi_perso+$gain_xp, gain_xp_tour=gain_xp_tour+$gain_xp WHERE id_perso='$id'";
									$mysqli->query($sql);
									
									// Passage grade grouillot
									passage_grade_grouillot($mysqli, $id, $grade_perso, $xp_perso, $gain_xp);
									
									if ($id_arme_attaque == 10 || $id_arme_attaque == 11) {
										
										// maj evenement
										$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>','a soigné ','$id_cible','<b>$nom_cible</b>',' ( Précision : $touche / $precision_final ; Soins : $degats_final ; Gain XP : $gain_xp ; Gain PC : 0 )',NOW(),'0')";
										$mysqli->query($sql);
										
									} else {
										
										// maj evenement
										$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>','a attaqué ','$id_cible','<b>$nom_cible</b>',' ( Précision : $touche / $precision_final ; Dégâts : $degats_final ; Gain XP : $gain_xp ; Gain PC : 0 )',NOW(),'0')";
										$mysqli->query($sql);
										
									}
									
									// recuperation des données du pnj aprés attaque
									$sql = "SELECT id_pnj, pv_i, x_i, y_i FROM instance_pnj WHERE idInstance_pnj='$id_cible'";
									$res = $mysqli->query($sql);
									$tab = $res->fetch_assoc();
									
									$pv_cible = $tab["pv_i"];
									$x_cible = $tab["x_i"];
									$y_cible = $tab["y_i"];
									$id_pnj = $tab["id_pnj"];
										
									// il est mort
									if ($pv_cible <= 0) {
									
										echo "Vous avez tué votre cible ! <font color=red>Félicitations.</font>";
									
										// on l'efface de la carte
										$sql = "UPDATE $carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_cible' AND y_carte='$y_cible'";
										$mysqli->query($sql);
										
										// on le delete
										$sql = "DELETE FROM instance_pnj WHERE idInstance_pnj='$id_cible'";
										$mysqli->query($sql);
										
										// verification que le perso n'a pas déjà tué ce type de pnj
										$sql_v = "SELECT id_pnj FROM perso_as_killpnj WHERE id_pnj='$id_pnj' AND id_perso='$id'";
										$res_v = $mysqli->query($sql_v);
										$verif_pnj = $res_v->num_rows;
										
										// nb_pnj 
										$sql = "UPDATE perso SET nb_pnj=nb_pnj+1 WHERE id_perso='$id'";
										$mysqli->query($sql);
										
										if($verif_pnj == 0){
											// il n'a jamais tué de pnj de ce type => insert
											$sql = "INSERT INTO perso_as_killpnj VALUES('$id','$id_pnj','1')";
											$mysqli->query($sql);
										}
										else { 
											// il en a déjà tué => update
											$sql = "UPDATE perso_as_killpnj SET nb_pnj=nb_pnj+1 WHERE id_perso='$id' AND id_pnj='$id_pnj'";
											$mysqli->query($sql);
										}
										
										// maj evenement
										$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>','a tué','$id_cible','<b>$nom_cible</b>','',NOW(),'0')";
										$mysqli->query($sql);
										
										// maj cv
										$sql = "INSERT INTO `cv` (IDActeur_cv, nomActeur_cv, gradeActeur_cv, IDCible_cv, nomCible_cv, date_cv) VALUES ($id,'<font color=$couleur_clan_perso>$nom_perso</font>', '$nom_grade_perso', '$id_cible','$nom_cible',NOW())";
										$mysqli->query($sql);
										
										echo "<br><center><a class='btn btn-primary' href=\"jouer.php\">retour ]</a></center>";
									}
									
									// L'arme fait des dégats de zone
									if ($degatZone_arme_attaque) {
										
										$degats_collat = floor($degats_final / 2);
										
										// Récupération des cibles potentielles autour de la cible principale
										$sql = "SELECT idPerso_carte FROM carte 
												WHERE x_carte >= $x_cible - 1 AND x_carte <= $x_cible + 1 AND y_carte >= $y_cible - 1 AND y_carte <= $y_cible + 1
												AND occupee_carte = '1'
												AND idPerso_carte != '$id_cible'";
										$res_recherche_collat = $mysqli->query($sql);
										
										$gain_xp_collat_cumul = 0;
										$gain_pc_collat_cumul = 0;
										
										// On parcours les cibles pour degats collateraux
										while ($t_recherche_collat = $res_recherche_collat->fetch_assoc()) {
											
											$id_cible_collat = $t_recherche_collat["idPerso_carte"];
											
											if ($id_cible_collat < 50000) {
												
												// Perso
												// Récupération des infos du perso
												$sql = "SELECT idJoueur_perso, nom_perso, image_perso, xp_perso, x_perso, y_perso, pv_perso, pvMax_perso, or_perso, clan, perso_as_grade.id_grade, nom_grade
														FROM perso, perso_as_grade, grades
														WHERE perso_as_grade.id_perso = perso.id_perso
														AND perso_as_grade.id_grade = grades.id_grade
														AND perso.id_perso='$id_cible_collat'";
												$res = $mysqli->query($sql);
												$t_collat = $res->fetch_assoc();
												
												$id_joueur_collat 	= $t_collat["idJoueur_perso"];
												$nom_collat			= $t_collat["nom_perso"];
												$xp_collat 			= $t_collat["xp_perso"];
												$x_collat 			= $t_collat["x_perso"];
												$y_collat 			= $t_collat["y_perso"];
												$pv_collat 			= $t_collat["pv_perso"];
												$pvM_collat 		= $t_collat["pvMax_perso"];
												$or_collat 			= $t_collat["or_perso"];
												$image_perso_collat = $t_collat["image_perso"];
												$clan_collat 		= $t_collat["clan"];
												$grade_collat		= $t_collat['id_grade'];
												$nom_grade_collat	= $t_collat['nom_grade'];
												
												// Récupération de la couleur associée au clan de la cible
												$couleur_clan_collat = couleur_clan($clan_collat);
												
												$gain_xp_collat = 1;
												if ($gain_xp_tour_perso + $gain_xp_collat > 20) {
													$gain_xp_collat = 20 - $gain_xp_tour_perso;
													$max_xp_tour_atteint = true;
												}
												
												$gain_xp_collat_cumul += $gain_xp_collat;
												$gain_pc_collat_cumul += 1;
												
												$gain_pc_collat = calcul_gain_pc_attaque_perso($grade_perso, $grade_collat, $clan_perso, $clan_collat, $type_perso, $id_j_perso, $id_joueur_collat);
												
												// Limite 2 PC par attaque de Gatling
												if ($id_arme_attaque == 14 && $gain_pc + $gain_pc_collat_cumul > 2) {
													$gain_pc_collat = 0;
												}
												
												// mise a jour des pv et des malus de la cible
												$sql = "UPDATE perso SET pv_perso=pv_perso-$degats_collat, bonus_perso=bonus_perso-2 WHERE id_perso='$id_cible_collat'";
												$mysqli->query($sql);
												
												echo "<br>Vous avez infligé $degats_collat dégâts collatéraux à <font color='$couleur_clan_collat'>$nom_collat</font> - Matricule $id_cible_collat.<br>";
												
												// Limite 3XP par attaque de Gatling
												if ($id_arme_attaque == 14 && $gain_xp_collat_cumul + $gain_xp <= 3 && !$max_xp_tour_atteint) {
													echo "Vous avez gagné $gain_xp_collat xp.<br><br>";
												
													// mise a jour des xp/pi
													$sql = "UPDATE perso SET xp_perso=xp_perso+$gain_xp_collat, pi_perso=pi_perso+$gain_xp_collat, gain_xp_tour=gain_xp_tour+$gain_xp_collat WHERE id_perso='$id'"; 
													$mysqli->query($sql);
													
													// Passage grade grouillot
													passage_grade_grouillot($mysqli, $id, $grade_perso, $xp_perso, $gain_xp_collat);
												}
												else if ($gain_xp_collat_cumul <= 4 && !$max_xp_tour_atteint) {
													echo "Vous avez gagné $gain_xp_collat xp.<br><br>";
												
													// mise a jour des xp/pi
													$sql = "UPDATE perso SET xp_perso=xp_perso+$gain_xp_collat, pi_perso=pi_perso+$gain_xp_collat, gain_xp_tour=gain_xp_tour+$gain_xp_collat WHERE id_perso='$id'"; 
													$mysqli->query($sql);
													
													// Passage grade grouillot
													passage_grade_grouillot($mysqli, $id, $grade_perso, $xp_perso, $gain_xp_collat);
													
												} else {
													echo "Vous avez gagné 0 xp";
													if ($max_xp_tour_atteint) {
														echo " (maximum de gain d'xp par tour atteint)";
													}
													else {
														echo " (maximum de gain d'xp par attaque atteint)";
													}
													
													echo ".<br><br>";
												}
												
												// recup id perso chef
												$sql = "SELECT id_perso FROM `perso` WHERE idJoueur_perso=(SELECT idJoueur_perso FROM perso WHERE id_perso='$id') AND chef='1' ";
												$res = $mysqli->query($sql);
												$t_chef = $res->fetch_assoc();
												
												$id_perso_chef = $t_chef["id_perso"];
												
												// mise à jour des PC du chef									
												$sql = "UPDATE perso SET pc_perso = pc_perso + $gain_pc_collat WHERE id_perso='$id_perso_chef'";
												$mysqli->query($sql);
												
												// mise a jour de la table evenement
												$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>','a infligé des dégâts collatéraux ','$id_cible_collat','<font color=$couleur_clan_collat><b>$nom_collat</b></font>',' ( Dégâts : $degats_collat ; Gain XP : $gain_xp_collat ; Gain PC : $gain_pc_collat )',NOW(),'0')";
												$mysqli->query($sql);
												
												$sql = "SELECT pv_perso, x_perso, y_perso, xp_perso, pi_perso, type_perso FROM perso WHERE id_perso='$id_cible_collat'";
												$res = $mysqli->query($sql);
												$tab = $res->fetch_assoc();
												
												$pv_collat_fin 	= $tab["pv_perso"];
												$x_collat_fin 	= $tab["x_perso"];
												$y_collat_fin 	= $tab["y_perso"];
												$xp_collat_fin 	= $tab["xp_perso"];
												$pi_collat_fin 	= $tab["pi_perso"];
												$tp_collat_fin	= $tab["type_perso"];
													
												// il est mort
												if ($pv_collat_fin <= 0) {
												
													// on l'efface de la carte
													$sql = "UPDATE $carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_collat_fin' AND y_carte='$y_collat_fin'";
													$mysqli->query($sql);
								
													// Calcul gains (po et xp)
													$perte_po = gain_po_mort($or_collat);
													
													// Chef
													if ($tp_collat_fin == 1) {
														// Quand un chef meurt, il perd 5% de ses XPi et de ses PC
														// Calcul PI
														$pi_perdu 		= floor(($pi_collat_fin * 5) / 100);
														$pi_perso_fin 	= $pi_collat_fin - $pi_perdu;
														
														// Calcul PC
														$pc_perdu		= floor(($pc_collat_fin * 5) / 100);
														$pc_perso_fin	= $pc_collat_fin - $pc_perdu;
													}
													else {
														// Quand un grouillot meurt, il perd tout ses Pi
														$pi_perso_fin = 0;
														$pc_perso_fin = $pc_collat_fin;
													}
								
													// MAJ perte xp/po/stat cible
													$sql = "UPDATE perso SET or_perso=or_perso-$perte_po, pi_perso=$pi_perso_fin, pc_perso=$pc_perso_fin, nb_mort=nb_mort+1 WHERE id_perso='$id_cible_collat'";
													$mysqli->query($sql);
													
													if ($perte_po > 0) {
														// On dépose la perte de PO par terre
														// Verification si l'objet existe deja sur cette case
														$sql = "SELECT nb_objet FROM objet_in_carte 
																WHERE objet_in_carte.x_carte = $x_collat_fin 
																AND objet_in_carte.y_carte = $y_collat_fin 
																AND type_objet = '1' AND id_objet = '0'";
														$res = $mysqli->query($sql);
														$to = $res->fetch_assoc();
														
														$nb_o = $to["nb_objet"];
														
														if($nb_o){
															// On met a jour le nombre
															$sql = "UPDATE objet_in_carte SET nb_objet = nb_objet + $perte_po 
																	WHERE type_objet='1' AND id_objet='0'
																	AND x_carte='$x_collat_fin' AND y_carte='$y_collat_fin'";
															$mysqli->query($sql);
														}
														else {
															// Insertion dans la table objet_in_carte : On cree le premier enregistrement
															$sql = "INSERT INTO objet_in_carte (type_objet, id_objet, nb_objet, x_carte, y_carte) VALUES ('1','0','$perte_po','$x_collat_fin','$y_collat_fin')";
															$mysqli->query($sql);
														}
													}
													
													$id_arme_non_equipee = id_arme_non_equipee($mysqli, $id_cible_collat);
															
													if ($id_arme_non_equipee > 0) {
												
														$test_perte = mt_rand(0,100);
														
														// 40% de chance de perdre une arme non équipée
														if ($test_perte <= 40) {
															
															// Suppression de l'arme de l'inventaire du perso
															$sql = "DELETE FROM perso_as_arme WHERE id_perso='$id_cible_collat' AND id_arme='$id_arme_non_equipee' AND est_portee='0' LIMIT 1";
															$mysqli->query($sql);
															
															// Maj charge perso suite perte de l'arme
															$sql = "UPDATE perso SET charge_perso = charge_perso - (SELECT poids_arme FROM arme WHERE id_arme='$id_arme_non_equipee') WHERE id_perso='$id_cible_collat'";
															$mysqli->query($sql);
															
															// On dépose la perte de l'arme par terre
															// Verification si l'objet existe deja sur cette case
															$sql = "SELECT nb_objet FROM objet_in_carte 
																	WHERE objet_in_carte.x_carte = $x_collat_fin 
																	AND objet_in_carte.y_carte = $y_collat_fin 
																	AND type_objet = '3' AND id_objet = '$id_arme_non_equipee'";
															$res = $mysqli->query($sql);
															$to = $res->fetch_assoc();
															
															$nb_o = $to["nb_objet"];
															
															if($nb_o){
																// On met a jour le nombre
																$sql = "UPDATE objet_in_carte SET nb_objet = nb_objet + 1 
																		WHERE type_objet='3' AND id_objet='$id_arme_non_equipee'
																		AND x_carte='$x_collat_fin' AND y_carte='$y_collat_fin'";
																$mysqli->query($sql);
															}
															else {
																// Insertion dans la table objet_in_carte : On cree le premier enregistrement
																$sql = "INSERT INTO objet_in_carte (type_objet, id_objet, nb_objet, x_carte, y_carte) VALUES ('3','$id_arme_non_equipee','1','$x_collat_fin','$y_collat_fin')";
																$mysqli->query($sql);
															}
														}
													}
								
													echo "<div class=\"infoi\">Vous avez capturé <font color='$couleur_clan_collat'>$nom_collat</font> - Matricule $id_cible_collat ! <font color=red>Félicitations.</font></div>";
													
													// maj evenements
													$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>','<b>a capturé</b>','$id_cible_collat','<font color=$couleur_clan_collat><b>$nom_collat</b></font>','',NOW(),'0')";
													$mysqli->query($sql);
													
													// maj cv
													$sql = "INSERT INTO `cv` (IDActeur_cv, nomActeur_cv, gradeActeur_cv, IDCible_cv, nomCible_cv, gradeCible_cv, date_cv) VALUES ($id,'<font color=$couleur_clan_perso>$nom_perso</font>', '$nom_grade_perso', '$id_cible_collat','<font color=$couleur_clan_collat>$nom_collat</font>', '$nom_grade_collat', NOW())";
													$mysqli->query($sql);
								
													// maj stats de la cible
													$sql = "UPDATE perso SET nb_kill=nb_kill+1 WHERE id_perso=$id";
													$mysqli->query($sql);
													
													// maj stats camp
													if($clan_cible != $clan_perso){
														$sql = "UPDATE stats_camp_kill SET nb_kill=nb_kill+1 WHERE id_camp=$clan_perso";
														$mysqli->query($sql);
													}
													
													// maj dernier tombé
													$sql = "INSERT INTO dernier_tombe (date_capture, id_perso_capture) VALUES (NOW(), '$id_cible_collat')";
													$mysqli->query($sql);
												}
												
											} else if ($id_cible_collat >= 200000) {
												
												// PNJ
												// Récupération des infos du PNJ	
												$sql = "SELECT pnj.id_pnj, nom_pnj, pv_i, x_i, y_i, pv_i, pvMax_pnj, protec_pnj 
														FROM pnj, instance_pnj 
														WHERE pnj.id_pnj=instance_pnj.id_pnj AND idInstance_pnj='$id_cible_collat'";
												$res = $mysqli->query($sql);
												$t_cible = $res->fetch_assoc();
												
												$id_pnj_collat 			= $t_cible["id_pnj"];
												$nom_cible_collat 		= $t_cible["nom_pnj"];
												$pv_cible_collat 		= $t_cible["pv_i"];
												$x_cible_collat 		= $t_cible["x_i"];
												$y_cible_collat 		= $t_cible["y_i"];
												$pv_cible_collat 		= $t_cible["pv_i"];
												$pvMax_cible_collat 	= $t_cible["pvMax_pnj"];
												$protec_cible_collat	= $t_cible["protec_pnj"];
												$image_pnj_collat 		= "pnj".$t_cible["id_pnj"]."t.png";
												
												$gain_xp_collat = 1;
												if ($gain_xp_tour_perso + $gain_xp_collat > 20) {
													$gain_xp_collat = 20 - $gain_xp_tour_perso;
													$max_xp_tour_atteint = true;
												}
												
												$gain_xp_collat_cumul += gain_xp_collat;
												
												// mise a jour des pv de la cible
												$sql = "UPDATE instance_pnj SET pv_i=pv_i-$degats_collat, dernierAttaquant_i=$id WHERE idInstance_pnj='$id_cible_collat'";
												$mysqli->query($sql);
												
												echo "<br>Vous avez infligé $degats_collat dégâts collatéraux à $nom_cible_collat<br>";
												
												// Limite 3XP par attaque de Gatling
												if ($id_arme_attaque == 14 && $gain_xp_collat_cumul + $gain_xp <= 3 && !$max_xp_tour_atteint) {
													echo "Vous avez gagné $gain_xp_collat xp.<br><br>";
												
													// mise a jour des xp/pi
													$sql = "UPDATE perso SET xp_perso=xp_perso+$gain_xp_collat, pi_perso=pi_perso+$gain_xp_collat, gain_xp_tour=gain_xp_tour+$gain_xp_collat WHERE id_perso='$id'"; 
													$mysqli->query($sql);
													
													// Passage grade grouillot
													passage_grade_grouillot($mysqli, $id, $grade_perso, $xp_perso, $gain_xp_collat);
												}
												else if ($gain_xp_collat_cumul <= 4 && !$max_xp_tour_atteint) {
													echo "Vous avez gagné $gain_xp_collat xp.<br><br>";
												
													// mise a jour des xp/pi
													$sql = "UPDATE perso SET xp_perso=xp_perso+$gain_xp_collat, pi_perso=pi_perso+$gain_xp_collat, gain_xp_tour=gain_xp_tour+$gain_xp_collat WHERE id_perso='$id'"; 
													$mysqli->query($sql);
													
													// Passage grade grouillot
													passage_grade_grouillot($mysqli, $id, $grade_perso, $xp_perso, $gain_xp_collat);
													
												} else {
													echo "Vous avez gagné 0 xp";
													if ($max_xp_tour_atteint) {
														echo " (maximum de gain d'xp par tour atteint)";
													}
													else {
														echo " (maximum de gain d'xp par attaque atteint)";
													}
													
													echo ".<br><br>";
												}
												
												// maj evenement
												$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>','a infligé des dégâts collatéraux ','$id_cible_collat','<b>$nom_cible_collat</b>',' ( Dégâts : $degats_collat ; Gain XP : $gain_xp_collat ; Gain PC : 0 )',NOW(),'0')";
												$mysqli->query($sql);
												
												// recuperation des données du pnj aprés attaque
												$sql = "SELECT pv_i, x_i, y_i FROM instance_pnj WHERE idInstance_pnj='$id_cible_collat'";
												$res = $mysqli->query($sql);
												$tab = $res->fetch_assoc();
												
												$pv_cible_collat 	= $tab["pv_i"];
												$x_cible_collat 	= $tab["x_i"];
												$y_cible_collat 	= $tab["y_i"];
													
												// il est mort
												if ($pv_cible_collat <= 0) {
												
													echo "Vous avez tué $nom_cible_collat avec des dégâts collatéraux ! <font color=red>Félicitations.</font>";
												
													// on l'efface de la carte
													$sql = "UPDATE $carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_cible_collat' AND y_carte='$y_cible_collat'";
													$mysqli->query($sql);
													
													// on le delete
													$sql = "DELETE FROM instance_pnj WHERE idInstance_pnj='$id_cible_collat'";
													$mysqli->query($sql);
													
													// verification que le perso n'a pas déjà tué ce type de pnj
													$sql_v = "SELECT id_pnj FROM perso_as_killpnj WHERE id_pnj='$id_pnj_collat' AND id_perso='$id'";
													$res_v = $mysqli->query($sql_v);
													$verif_pnj = $res_v->num_rows;
													
													// nb_pnj 
													$sql = "UPDATE perso SET nb_pnj=nb_pnj+1 WHERE id_perso='$id'";
													$mysqli->query($sql);
													
													if($verif_pnj == 0){
														// il n'a jamais tué de pnj de ce type => insert
														$sql = "INSERT INTO perso_as_killpnj VALUES('$id','$id_pnj_collat','1')";
														$mysqli->query($sql);
													}
													else { 
														// il en a déjà tué => update
														$sql = "UPDATE perso_as_killpnj SET nb_pnj=nb_pnj+1 WHERE id_perso='$id' AND id_pnj='$id_pnj_collat'";
														$mysqli->query($sql);
													}
													
													// maj evenement
													$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>','a tué','$id_cible_collat','<b>$nom_cible_collat</b>','',NOW(),'0')";
													$mysqli->query($sql);
													
													// maj cv
													$sql = "INSERT INTO `cv` (IDActeur_cv, nomActeur_cv, gradeActeur_cv, IDCible_cv, nomCible_cv, date_cv) VALUES ($id,'<font color=$couleur_clan_perso>$nom_perso</font>', '$nom_grade_perso', '$id_cible_collat','$nom_cible_collat',NOW())";
													$mysqli->query($sql);
													
													echo "<br><center><a href=\"jouer.php\">retour</a></center>";
												}
											} else {
												// Batiment => pas de collat sur batiment
											}
										}
									}
									
									if ($pv_cible > 0) {
									?>
										<br />
										<form action="agir.php" method="post">
											<input type="hidden" name="re_attaque_hid" value="<?php echo $id_cible.",".$id_arme_attaque;?>" />
											<input type="submit" name="re_attaque" value="attaquer à nouveau" />
										</form> 
										
										<br />
									<?php
										echo "<br/><center><a class='btn btn-primary' href=\"jouer.php\">retour</a></center>";
									}						
								}
								else { // la cible a esquivé l'attaque
					
									echo "<br>Vous avez raté votre cible.<br><br>";
									
									if ($touche >= 98) {
										// Echec critique !
										// Ajout d'un malus supplémentaire à l'attaquant
										$sql = "UPDATE perso SET bonus_perso = bonus_perso - 1 WHERE id_perso='$id'";
									} else {
										// ajout malus cible
										$sql = "UPDATE instance_pnj SET bonus_i = bonus_i - 1 WHERE idInstance_pnj='$id_cible'";
									}
									$mysqli->query($sql);
									
									// Insertion log attaque
									$message_log = $id.' a raté son attaque sur '.$id_cible;
									$type_action = "Attaque ".$id_arme_attaque;
									$sql = "INSERT INTO log (date_log, id_perso, type_action, id_arme, pourcentage, message_log) VALUES (NOW(), '$id', '$type_action', '$id_arme_attaque', '$touche', '$message_log')";
									$mysqli->query($sql);
										
									// maj evenement
									$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_cible,'<b>$nom_cible</b>','a esquivé l\'attaque de','$id','<font color=$couleur_clan_perso><b>$nom_perso</b></font>',' ( Précision : $touche / $precision_final ; Gain XP : 0)',NOW(),'0')";
									$mysqli->query($sql);
									
									if ($pv_cible > 0) {
										
										if ($id_arme_attaque == 10 || $id_arme_attaque == 11) {
											$texte_submit = "soigner à nouveau";
										} else {
											$texte_submit = "attaquer à nouveau";
										}
									?>
										<br />
										<form action="agir.php" method="post">
											<input type="hidden" name="re_attaque_hid" value="<?php echo $id_cible.",".$id_arme_attaque;?>" />
											<input type="submit" name="re_attaque" value="<?php echo $texte_submit; ?>" />
										</form> 
										
										<br />
									<?php
										echo "<br/><center><a class='btn btn-primary' href=\"jouer.php\">retour</a></center>";
									}
					
								}
								
								//mise à jour des pa
								$sql = "UPDATE perso SET pa_perso=pa_perso-$coutPa_arme_attaque WHERE id_perso='$id'";
								$res = $mysqli->query($sql);
							}			
							else {
								
								//la cible est déjà morte
								echo "Erreur : La cible est déjà morte !";
								echo "<br/><center><a class='btn btn-primary' href=\"jouer.php\">retour</a></center>";
							}
						}
					}
					else { // la cible n'est pas à portée d'attaque
						echo "Erreur : La cible n'est pas à portée d'attaque (Vérifiez la portée de votre arme) ou votre état ne vous permet pas de la cibler (pas assez de perception) !";
						echo "<br/><center><a class='btn btn-primary' href=\"jouer.php\">retour</a></center>";
					}
				}
				else {
					echo "Erreur : vous avez été capturé entre temps !";
					echo "<br/><center><a class='btn btn-primary' href=\"jouer.php\">retour</a></center>";
				}
			}
			else {
				// Tentative de triche
				echo "Pas bien d'essayer de tricher !";
				echo "<br /><center><a class='btn btn-primary' href=\"jouer.php\">retour</a></center>";
				
				$text_triche = "Tentative attaque avec arme qu'on ne possède pas";
				
				$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id', '$text_triche')";
				$mysqli->query($sql);
			}
		}
		
		// Traitement attaque Batiment
		if ((isset($id_attaque) && $id_attaque != "" && $id_attaque != "personne" && $id_attaque >= 50000 && $id_attaque < 200000)) {
			
			$id_cible =  $id_attaque;
			$verif_arme = 0;
			
			// arme bien passée
			if(isset($id_arme_attaque) && $id_arme_attaque != 1000 && $id_arme_attaque != 2000) {
				
				// Est-ce que le perso possède bien l'arme ?
				$sql = "SELECT * FROM perso_as_arme WHERE id_perso='$id' AND id_arme='$id_arme_attaque' AND est_portee='1'";
				$res = $mysqli->query($sql);
				$verif_arme = $res->num_rows;
				
				// Recupération des caracs de l'arme
				$sql = "SELECT nom_arme, coutPa_arme, porteeMin_arme, porteeMax_arme, degatMin_arme, degatMax_arme, degatZone_arme, precision_arme, valeur_des_arme
						FROM arme WHERE id_arme='$id_arme_attaque'";
				$res = $mysqli->query($sql);
				$t_a = $res->fetch_assoc();
				
				$nom_arme_attaque 				= $t_a["nom_arme"];
				$coutPa_arme_attaque 			= $t_a["coutPa_arme"];
				$porteeMin_arme_attaque 		= $t_a["porteeMin_arme"];
				$porteeMax_arme_attaque 		= $t_a["porteeMax_arme"];
				$valeur_des_arme_attaque		= $t_a["valeur_des_arme"];
				$degatMin_arme_attaque 			= $t_a["degatMin_arme"];
				$degatMax_arme_attaque 			= $t_a["degatMax_arme"];
				$precision_arme_attaque 		= $t_a["precision_arme"];
				$degatZone_arme_attaque 		= $t_a["degatZone_arme"];
			}
			else {
				if ($id_arme_attaque == 1000) {
					
					// Poings = arme de Corps a corps					
					$nom_arme_attaque 				= "Poings";
					$coutPa_arme_attaque 			= 3;
					$porteeMin_arme_attaque 		= 1;
					$porteeMax_arme_attaque 		= 1;
					$valeur_des_arme_attaque		= 6;
					$degatMin_arme_attaque 			= 4;
					$degatMax_arme_attaque 			= 4;
					$precision_arme_attaque			= 30;
					$degatZone_arme_attaque 		= 0;
					
					$verif_arme = 1;
					
				} else if ($id_arme_attaque == 2000) {
					
					// Cailloux
					$nom_arme_attaque 				= "Cailloux";
					$coutPa_arme_attaque 			= 3;
					$porteeMin_arme_attaque 		= 1;
					$porteeMax_arme_attaque 		= 2;
					$valeur_des_arme_attaque		= 6;
					$degatMin_arme_attaque 			= 5;
					$degatMax_arme_attaque 			= 5;
					$precision_arme_attaque			= 25;
					$degatZone_arme_attaque 		= 0;
					
					$verif_arme = 1;
				}
			}
			
			if ($porteeMax_arme_attaque > 1 && possede_lunette_visee($mysqli, $id)) {
				$coutPa_arme_attaque = $coutPa_arme_attaque + 1;
			}
			
			if ($verif_arme) {
			
				// recup des données du perso
				$sql = "SELECT type_perso, nom_perso, image_perso, xp_perso, x_perso, y_perso, pm_perso, pi_perso, pv_perso, pvMax_perso, pmMax_perso, pa_perso, paMax_perso, recup_perso, bonusRecup_perso, bonusPM_perso, perception_perso, bonusPerception_perso, dateCreation_perso, clan, gain_xp_tour, perso_as_grade.id_grade, nom_grade
						FROM perso, perso_as_grade, grades
						WHERE perso_as_grade.id_perso = perso.id_perso
						AND perso_as_grade.id_grade = grades.id_grade
						AND perso.id_perso='$id'";
				$res = $mysqli->query($sql);
				$t_perso = $res->fetch_assoc();
				
				$nom_perso 		= $t_perso["nom_perso"];
				$image_perso 	= $t_perso["image_perso"];
				$xp_perso 		= $t_perso["xp_perso"];
				$x_perso 		= $t_perso["x_perso"];
				$y_perso 		= $t_perso["y_perso"];
				$pm_perso 		= $t_perso["pm_perso"];
				$pmM_perso 		= $t_perso["pmMax_perso"];
				$pi_perso 		= $t_perso["pi_perso"];
				$pv_perso 		= $t_perso["pv_perso"];
				$pvM_perso 		= $t_perso["pvMax_perso"];
				$pa_perso 		= $t_perso["pa_perso"];
				$paM_perso 		= $t_perso["paMax_perso"];
				$rec_perso 		= $t_perso["recup_perso"];
				$br_perso 		= $t_perso["bonusRecup_perso"];
				$bPM_perso		= $t_perso["bonusPM_perso"];
				$per_perso 		= $t_perso["perception_perso"];
				$bp_perso 		= $t_perso["bonusPerception_perso"];
				$dc_perso 		= $t_perso["dateCreation_perso"];
				$clan_perso 	= $t_perso["clan"];
				$grade_perso 	= $t_perso["id_grade"];
				$type_perso		= $t_perso["type_perso"];
				$nom_grade_perso= $t_perso["nom_grade"];
				$gain_xp_tour_perso	= $t_perso["gain_xp_tour"];
				
				if ($pv_perso > 0) {
				
					if ($gain_xp_tour_perso >= 20) {
						$max_xp_tour_atteint = true;
					}
					else {
						$max_xp_tour_atteint = false;
					}
					
					// Récupération de la couleur associée au clan du perso
					$couleur_clan_perso = couleur_clan($clan_perso);
					
					if(is_a_portee_attaque($mysqli, $carte, $id, $id_cible, $porteeMin_arme_attaque, $porteeMax_arme_attaque, $per_perso)) {
					
						$coutPa_attaque=$coutPa_arme_attaque;
								
						// recuperation des données du batiment	
						$sql = "SELECT batiment.id_batiment, nom_batiment, taille_batiment, description, nom_instance, pv_instance, pvMax_instance, x_instance, y_instance, camp_instance, contenance_instance 
								FROM batiment, instance_batiment
								WHERE batiment.id_batiment=instance_batiment.id_batiment
								AND id_instanceBat=$id_cible";
						$res = $mysqli->query($sql);
						$bat = $res->fetch_assoc();
						
						$id_batiment 			= $bat['id_batiment'];
						$nom_batiment 			= $bat['nom_batiment'];
						$taille_batiment		= $bat['taille_batiment'];
						$description_batiment 	= $bat['description'];
						$nom_instance_batiment 	= $bat['nom_instance'];
						$pv_instance 			= $bat['pv_instance'];
						$pvMax_instance 		= $bat['pvMax_instance'];
						$x_instance 			= $bat['x_instance'];
						$y_instance 			= $bat['y_instance'];
						$camp_instance 			= $bat['camp_instance'];
						$contenance_instance 	= $bat['contenance_instance'];
						
						if($camp_instance == '1'){
							$camp_bat 		= 'b';
							$couleur_bat 	= 'blue';
							$nom_camp_bat 	= 'Nord';
						}
						if($camp_instance == '2'){
							$camp_bat 		= 'r';
							$couleur_bat 	= 'red';
							$nom_camp_bat 	= 'Sud';
						}
						
						$image_bat = "b".$id_batiment."".$camp_bat.".png";
						
						$pa_restant = $pa_perso - $coutPa_attaque;
						
						if($pa_restant <= 0){
							$pa_restant = 0;
						}
						
						?>
						<table border=0 width=100%>
							<tr height=50%>
								<td width=50%>	
									<table border=1 height=100% width=100%>		
										<tr>
											<td width=25%>	
												<table border=0 width=100%>
													<tr>
														<td align="center">
															<div width=40 height=40 style="position: relative;">
																<div style="position: absolute;bottom: 0;text-align: center; width: 100%;font-weight: bold;"><?php echo $id; ?></div>
																<img class="" border=0 src="../images_perso/<?php echo $image_perso; ?>" width=40 height=40 />
															</div>
														</td>
													</tr>
												</table>
											</td>
											<td width=75%>
												<table border=0 width=100%>
													<tr>
														<td><?php echo "<u><b>Pseudo :</b></u> ".$nom_perso; ?></td>
													<tr>
													<tr>
														<td><?php echo "<u><b>Xp :</b></u> ".$xp_perso." - <u><b>Pi :</b></u> ".$pi_perso.""; ?></td>
													<tr>
													<tr>
														<td><?php echo "<u><b>Position sur la carte :</b></u> ".$x_perso."/".$y_perso; ?></td>
													<tr>
													<tr>
														<td><?php echo "<u><b>Mouvements restants :</b></u> ".$pm_perso."/".($pmM_perso + $bPM_perso); ?><?php echo " - <u><b>Points de vie :</b></u> ".$pv_perso."/".$pvM_perso; ?></td>
													<tr>
													<tr>
														<td><?php echo "<u><b>Points d'action :</b></u> ".$pa_restant."/".$paM_perso; ?></td>
													<tr>
													<tr>
														<td><?php echo "<u><b>Recup :</b></u> ".$rec_perso; if($br_perso) echo " (+".$br_perso.")"; ?><?php echo " - <u><b>Perception :</b></u> ".$per_perso; if($bp_perso) {if($bp_perso>0) echo " (+".$bp_perso.")"; else echo " (".$bp_perso.")";} ?></td>
													<tr>
												</table>
											</td>
										</tr>
									</table>
								</td>
								<td width=50%>
									<table border=1 height=100% width=100%>		
										<tr>
											<td width=25%>	
												<table border=0 width=100%>
													<tr>
														<td align="center"><img src="../images_perso/<?php echo $image_bat; ?>"></td>
													</tr>
												</table>
											</td>
											<td width=75%>
												<table border=0 width=100%>
													<tr>
														<td><?php echo "<u><b>Batiment :</b></u> ".$nom_batiment.""; ?></td>
													<tr>
													<tr>
														<td><?php echo "<u><b>Camp :</b></u> <font color='".$couleur_bat."'><b>".$nom_camp_bat."</b></font>"; ?></td>
													</tr>
													<tr>
														<td><?php echo "<u><b>Position sur la carte :</b></u> ".$x_instance."/".$y_instance; ?></td>
													<tr>
												</table>
											</td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
						<?php
						
						//le perso n'a pas assez de pa pour faire cette attaque
						if ($pa_perso < $coutPa_attaque) {
							echo "<div class=\"erreur\" align=\"center\">Vous n'avez pas assez de PA pour effectuer cette action !</div>";
							echo "<center><a href=\"jouer.php\" class='btn btn-primary'>retour</a></center>";
						}
						else {
							//le perso a assez de pa
								
							//la cible est encore en vie
							if ($pv_instance > 0) {
								
								$autorisation_attaque = true;
								
								// Est-ce que le bâtiment est de notre camp ?
								if ($camp_instance == $clan_perso) {
									
									// calcul pourcentage PV du bâtiment
									$pourc_pv_bat = ($pv_instance / $pvMax_instance) * 100;;
									
									if (nb_ennemis_siege_batiment($mysqli, $x_instance, $y_instance, $clan_perso) >= 10 && $pourc_pv_bat <= 25 
											&& ($id_batiment == 6 || $id_batiment == 7 || $id_batiment == 8 || $id_batiment == 9 || $id_batiment == 10 || $id_batiment == 11)) {
										$autorisation_attaque = false;
									}
								}

								$verif_anti_zerk = gestion_anti_zerk($mysqli, $id);
								
								if ($autorisation_attaque && $verif_anti_zerk) {
								
									echo "Vous avez lancé une attaque sur <b>$nom_batiment [$id_cible]</b>.<br>";
									
									$gain_xp = mt_rand(1,3);
									
									if ($gain_xp_tour_perso + $gain_xp > 20) {
										$gain_xp = 20 - $gain_xp_tour_perso;
										$max_xp_tour_atteint = true;
									}
						
									// Calcul touche
									$touche = mt_rand(0,100);
									
									// Bonus Précision batiment
									$bonus_precision_bat = 0;
									
									if (in_bat($mysqli, $id)) {
										
										$id_inst_bat_perso = in_bat($mysqli, $id);
										
										$sql = "SELECT id_batiment FROM instance_batiment WHERE id_instanceBat='$id_inst_bat_perso'";
										$res = $mysqli->query($sql);
										$t = $res->fetch_assoc();
										
										$id_bat_perso = $t['id_batiment'];
										
										$bonus_precision_bat = get_bonus_attaque_from_batiment($id_bat_perso);
									}
									else if (in_train($mysqli, $id)) {
										$bonus_precision_bat = -30;
									}							
									
									$precision_final = $precision_arme_attaque + $bonus_precision_bat;
									
									$bonus_precision_objet = 0;
									if ($porteeMax_arme_attaque == 1) {
										$bonus_precision_objet = getBonusPrecisionCacObjet($mysqli, $id);
									}
									else {
										$bonus_precision_objet = getBonusPrecisionDistObjet($mysqli, $id);
									}
									
									$precision_final += $bonus_precision_objet;
									
									echo "Votre score de touche : ".$touche."<br>";
									echo "Précision : ".$precision_final. " (Base arme : ".$precision_arme_attaque." -- Bonus Précision objet : ".$bonus_precision_objet."";
									if ($bonus_precision_bat != 0) {
										echo " -- Bonus du batiment : ".$bonus_precision_bat."";
									}
									echo ")<br>";
									
									// Score touche <= precision arme utilisée - bonus cible pour l'attaque = La cible est touchée
									if ($touche <= $precision_final && $touche < 98) {
						
										// calcul degats arme
										$degats_final = calcul_des_attaque($degatMin_arme_attaque, $valeur_des_arme_attaque);
										
										// Insertion log attaque
										$message_log = $id.' a attaqué '.$id_cible;
										$type_action = "Attaque ".$id_arme_attaque;
										$sql = "INSERT INTO log (date_log, id_perso, type_action, id_arme, degats, pourcentage, message_log) VALUES (NOW(), '$id', '$type_action', '$id_arme_attaque', '$degats_final', '$touche', '$message_log')";
										$mysqli->query($sql);
										
										if($degats_final < 0) {
											$degats_final = 0;
										}
										
										if ($touche <= 2) {
											// Coup critique ! Dégats et Gains PC X 2
											$degats_final = $degats_final * 2;
										}
										
										// Canon d'artillerie
										if ($id_arme_attaque == 13 || $id_arme_attaque == 22) {
											// Bonus dégats 20D10
											$bonus_degats_canon = calcul_des_attaque(20, 10);
											$degats_final = $degats_final + $bonus_degats_canon;
										}
										
										// mise à jour des pv du batiment
										$sql = "UPDATE instance_batiment SET pv_instance=pv_instance-$degats_final WHERE id_instanceBat='$id_cible'";
										$mysqli->query($sql);
										
										echo "<br>Vous avez infligé <b>$degats_final</b> degats à la cible.<br><br>";
										echo "Vous avez gagné <b>$gain_xp</b> xp";
										if ($max_xp_tour_atteint) {
											echo " (maximum de gain d'xp par tour atteint)";
										}
										echo "<br />";
										
										if ($gain_xp > 0) {
											// maj gain xp, pi perso
											$sql = "UPDATE perso SET xp_perso=xp_perso+$gain_xp, pi_perso=pi_perso+$gain_xp, gain_xp_tour=gain_xp_tour+$gain_xp WHERE id_perso='$id'";
											$mysqli->query($sql);
											
											// Passage grade grouillot
											passage_grade_grouillot($mysqli, $id, $grade_perso, $xp_perso, $gain_xp);
										}
											
										// maj evenement
										$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>','a attaqué ','$id_cible','<font color=$couleur_bat><b>$nom_batiment</b></font>',' ( Précision : $touche / $precision_final ; Dégâts : $degats_final ; Gain XP : $gain_xp ; Gain PC : 0 )',NOW(),'0')";
										$mysqli->query($sql);					
											
										// recuperation des données du batiment aprés attaque
										$sql = "SELECT id_batiment, pv_instance, x_instance, y_instance FROM instance_batiment WHERE id_instanceBat='$id_cible'";
										$res = $mysqli->query($sql);
										$tab = $res->fetch_assoc();
										
										$pv_cible 		= $tab["pv_instance"];
										$x_cible 		= $tab["x_instance"];
										$y_cible 		= $tab["y_instance"];
										$id_batiment 	= $tab["id_batiment"];
										
										/* Début du traitement de la destruction du batiment*/
										// il est detruit
										if ($pv_cible <= 0) {
										
											// on efface le batiment de la carte
											$sql = "UPDATE $carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_cible' AND y_carte='$y_cible'";
											$mysqli->query($sql);
											
											if ($taille_batiment > 1) {
												// Il faut supprimer les cases sup du batiment de la carte
												$taille_search = floor($taille_batiment / 2);
												
												for ($x = $x_cible - $taille_search; $x <= $x_cible + $taille_search; $x++) {
													for ($y = $y_cible - $taille_search; $y <= $y_cible + $taille_search; $y++) {
														
														// mise a jour de la carte
														$sql = "UPDATE $carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x' AND y_carte='$y'";
														$mysqli->query($sql);
														
													}
												}
											}
											
											// Mise à jour des respawn
											$sql = "DELETE FROM perso_as_respawn WHERE id_instance_bat='$id_cible'";
											$mysqli->query($sql);
											
											$sql = "SELECT MAX(x_carte) as x_max, MAX(y_carte) as y_max FROM carte";
											$res = $mysqli->query($sql);
											$t = $res->fetch_assoc();
											
											$X_MAX = $t['x_max'];
											$Y_MAX  = $t['y_max'];
											
											if (is_train($mysqli, $id_cible)) {
												// Récupération des persos dans le train
												$sql = "SELECT id_perso FROM perso_in_train WHERE id_train='$id_cible'";
											}
											else {
												// Récupération des persos dans le batiment
												$sql = "SELECT id_perso FROM perso_in_batiment WHERE id_instanceBat='$id_cible'";
											}
											
											$res = $mysqli->query($sql);
											
											while ($t = $res->fetch_assoc()){
												
												$id_p = $t['id_perso'];
												
												// perte entre 10 et 50 pv
												$perte_pv = mt_rand(10,50);
												
												// Traitement persos dans le batiment qui perdent des pv 
												$sql_p = "UPDATE perso SET pv_perso=pv_perso - $perte_pv WHERE id_perso='$id_p'";
												$mysqli->query($sql_p);
												
												// recup des infos du perso
												$sql_i = "SELECT nom_perso, pv_perso, image_perso, clan FROM perso WHERE id_perso='$id_p'";
												$res_i = $mysqli->query($sql_i);
												$t_i = $res_i->fetch_assoc();
												
												$nom_p 		= $t_i["nom_perso"];
												$pv_p 		= $t_i["pv_perso"];
												$image_p 	= $t_i["image_perso"];
												$clan_p 	= $t_i["clan"];
												
												// Récupération de la couleur associée au clan du perso
												$couleur_clan_p = couleur_clan($clan_p);
												
												// maj evenement
												$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ('$id_p','<font color=$couleur_clan_p><b>$nom_p</b></font>','a été blessé suite à la destruction du bâtiment',NULL,'',' : $perte_pv degats',NOW(),'0')";
												$mysqli->query($sql);
												
												// Le perso est encore vivant
												if($pv_p > 0){
													
													// pv/2
													$sql_p = "UPDATE perso SET pv_perso=pv_perso/2 WHERE id_perso='$id_p'";
													$mysqli->query($sql_p);
													
													// Traitement répartissement des persos sur la carte
													$verif_occ = 0;
													while (!$verif_occ)
													{
														// TODO - changer façon de faire 
														// TODO - Tourner autour de la position du batiment jusqu'à ce qu'on trouve une position libre
														$x = pos_zone_rand_x($x_cible-5, $x_cible+5); 
														$y = pos_zone_rand_y($y_cible-5, $y_cible+5);
														$verif_occ = verif_position_libre($mysqli, $x, $y, $X_MAX, $Y_MAX);
													}
													
													// maj evenement
													$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ('$id_p','<font color=$couleur_clan_p><b>$nom_p</b></font>','a été éjecté suite à la destruction du bâtiment',NULL,'',' : en $x / $y et PV divisé par 2',NOW(),'0')";
													$mysqli->query($sql);
													
													// MAJ du perso sur la carte
													$sql_u = "UPDATE carte SET occupee_carte = '1', image_carte='$image_p', idPerso_carte='$id_p' WHERE x_carte='$x' AND y_carte='$y'";
													$mysqli->query($sql_u);
													
													// MAJ des coordonnées du perso
													$sql_u2 = "UPDATE perso SET x_perso='$x', y_perso='$y' WHERE id_perso='$id_p'";
													$mysqli->query($sql_u2);
												}
												else {
													// Le perso est mort
													$sql_g = "SELECT perso_as_grade.id_grade, nom_grade 
																FROM perso_as_grade, grades 
																WHERE perso_as_grade.id_grade = grades.id_grade 
																AND perso_as_grade.id_perso = '$id_p'";
													$res_g = $mysqli->query($sql_g);
													$t_g = $res_g->fetch_assoc();
													
													$id_grade_p 	= $t_g['id_grade'];
													$nom_grade_p	= $t_g['nom_grade'];
																					
													// Ajout du kill
													$sql_u2 = "UPDATE perso SET nb_kill=nb_kill+1 WHERE id_perso='$id'";
													$mysqli->query($sql_u2);
													
													$sql_u2 = "UPDATE perso SET nb_mort=nb_mort+1 WHERE id_perso='$id_p'";
													$mysqli->query($sql_u2);
													
													// maj evenement
													$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>','<b>a capturé</b>','$id_p','<font color=$couleur_clan_p><b>$nom_p</b></font> ($nom_grade_p)',' : mort suite à l\'explosion du bâtiment $id_cible',NOW(),'0')";
													$mysqli->query($sql);
														
													// maj cv
													$sql = "INSERT INTO `cv` (IDActeur_cv, nomActeur_cv, gradeActeur_cv, IDCible_cv, nomCible_cv, gradeCible_cv, date_cv) VALUES ($id,'<font color=$couleur_clan_perso>$nom_perso</font>', '$nom_grade_perso', '$id_p','<font color=$couleur_clan_p>$nom_p</font>', '$nom_grade_p', NOW())"; 
													$mysqli->query($sql);
													
													// maj stats camp
													if($clan_p != $clan_perso){
														$sql = "UPDATE stats_camp_kill SET nb_kill=nb_kill+1 WHERE id_camp=$clan_perso";
														$mysqli->query($sql);
													}
													
													// maj dernier tombé
													$sql = "INSERT INTO dernier_tombe (date_capture, id_perso_capture) VALUES (NOW(), '$id_p')";
													$mysqli->query($sql);
													
												}
											}
											
											if (is_train($mysqli, $id_cible)) {
												
												// On supprime le train de la liaison
												$sql = "UPDATE liaisons_gare SET id_train=NULL WHERE id_train='$id_cible'";
												$mysqli->query($sql);
												
												// on supprime les persos du batiment
												$sql = "DELETE FROM perso_in_train WHERE id_instanceBat='$id_cible'";
											}
											else {
												// on supprime les persos du batiment
												$sql = "DELETE FROM perso_in_batiment WHERE id_instanceBat='$id_cible'";
											}
											$mysqli->query($sql);
											
											// TODO - Si gare -> maj liaisons_gare ?
											
											// on delete le bâtiment
											$sql = "DELETE FROM instance_batiment WHERE id_instanceBat='$id_cible'";
											$mysqli->query($sql);
											
											// on delete les canons rattachés au batiment 
											$sql = "DELETE FROM instance_batiment_canon WHERE id_instance_bat='$id_cible'";
											$mysqli->query($sql);
									
											echo "Vous avez détruit votre cible ! <font color=red>Félicitations.</font>";
												
											// maj evenement
											$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>','a détruit','$id_cible','<font color=$couleur_bat><b>$nom_batiment $nom_instance_batiment</b></font>','',NOW(),'0')";
											$mysqli->query($sql);
												
											// maj cv
											$sql = "INSERT INTO `cv` (IDActeur_cv, nomActeur_cv, gradeActeur_cv, IDCible_cv, nomCible_cv, date_cv) VALUES ($id,'<font color=$couleur_clan_perso>$nom_perso</font>', '$nom_grade_perso', '$id_cible','<font color=$couleur_bat>$nom_batiment $nom_instance_batiment</font>',NOW())"; 
											$mysqli->query($sql);
											
											// Gain points de victoire
											if ($id_batiment == 9) {
												// FORT -> 400
												$gain_pvict = 400;
											}
											else if ($id_batiment == 8) {
												// FORTIN -> 100
												$gain_pvict = 100;
											}
											else if ($id_batiment == 11) {
												// GARE -> 75
												$gain_pvict = 75;
											}
											else if ($id_batiment == 7) {
												// HOPITAL -> 10
												$gain_pvict = 10;
											}
											else {
												$gain_pvict = 0;
											}
											
											if ($gain_pvict > 0) {
												
												// MAJ stats points victoire
												if ($clan_perso != $camp_instance) {
													$sql = "UPDATE stats_camp_pv SET points_victoire = points_victoire + ".$gain_pvict." WHERE id_camp='$clan_perso'";
												}
												else {
													$sql = "UPDATE stats_camp_pv SET points_victoire = points_victoire - ".$gain_pvict." WHERE id_camp='$clan_perso'";
												}
												$mysqli->query($sql);
											
												// Ajout de l'historique
												$date = time();
												$texte = addslashes("Pour la destruction du bâtiment ".$nom_batiment." ".$nom_instance_batiment." [".$id_cible."] par ".$nom_perso." [".$id."]");
												if ($clan_perso != $camp_instance) {
													$sql = "INSERT INTO histo_stats_camp_pv (date_pvict, id_camp, gain_pvict, texte) VALUES (FROM_UNIXTIME($date), '$clan_perso', '$gain_pvict', '$texte')";
												}
												else {
													$sql = "INSERT INTO histo_stats_camp_pv (date_pvict, id_camp, gain_pvict, texte) VALUES (FROM_UNIXTIME($date), '$clan_perso', '-$gain_pvict', '$texte')";
												}
												$mysqli->query($sql);
												
											}
												
											echo "<br><center><a class='btn btn-primary' href=\"jouer.php\">retour</a></center>";
										}
										/* Fin du traitement de la destruction du bâtiment*/
										
										//mise à jour des pa
										$sql = "UPDATE perso SET pa_perso=pa_perso-$coutPa_attaque WHERE id_perso='$id'";
										$res = $mysqli->query($sql);
										
										if ($pv_cible > 0) { 
											?>
												<form action="agir.php" method="post">
													<input type="hidden" name="re_attaque_hid" value="<?php echo $id_cible.",".$id_arme_attaque;?>" />
													<input type="submit" name="re_attaque" value="attaquer à nouveau">
												</form> 
												
											<?php
											echo "<br/><center><a class='btn btn-primary' href=\"jouer.php\">retour</a></center>";
										}
									}
									else {
										
										echo "<br>Vous avez raté votre cible.<br><br>";
										
										if ($touche >= 98) {
											// Echec critique !
											// Ajout d'un malus supplémentaire à l'attaquant
											$sql = "UPDATE perso SET bonus_perso = bonus_perso - 1 WHERE id_perso='$id'";
											$mysqli->query($sql);
										}
										
										//mise à jour des pa
										$sql = "UPDATE perso SET pa_perso=pa_perso-$coutPa_attaque WHERE id_perso='$id'";
										$mysqli->query($sql);
										
										// Insertion log attaque
										$message_log = $id.' a raté son attaque sur '.$id_cible;
										$type_action = "Attaque ".$id_arme_attaque;
										$sql = "INSERT INTO log (date_log, id_perso, type_action, id_arme, pourcentage, message_log) VALUES (NOW(), '$id', '$type_action', '$id_arme_attaque', '$touche', '$message_log')";
										$mysqli->query($sql);
											
										// maj evenement
										$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>',' a raté son attaque contre','$id_cible','<font color=$couleur_bat><b>$nom_batiment</b></font>',' ( Précision : $touche / $precision_final ; Gain XP : 0)',NOW(),'0')";
										$mysqli->query($sql);
										
										?>
											<form action="agir.php" method="post">
												<input type="hidden" name="re_attaque_hid" value="<?php echo $id_cible.",".$id_arme_attaque;?>" />
												<input type="submit" name="re_attaque" value="attaquer à nouveau">
											</form> 
											
										<?php
										echo "<br/><center><a class='btn btn-primary' href=\"jouer.php\">retour</a></center>";
									}
								}
								else if (!$autorisation_attaque) {
									echo "Erreur : Vous n'avez plus le droit d'attaquer votre bâtiment car il posséde moins de 25% de ses PV et est encerclé par l'ennemi !";
									echo "<br/><center><a class='btn btn-primary' href=\"jouer.php\">retour</a></center>";
								}
								else if (!$verif_anti_zerk) {
									echo "Loi anti-zerk non respectée !";
									echo "<br/><center><a class='btn btn-primary' href=\"jouer.php\">retour</a></center>";
								}
							}
							else {
								//la cible est déjà morte
								echo "Erreur : La cible est déjà morte !";
								echo "<br/><center><a class='btn btn-primary' href=\"jouer.php\">retour</a></center>";
							}
						}
					}
					else {
						// la cible n'est pas à portée d'attaque
						echo "Erreur : La cible n'est pas à portée d'attaque (Vérifiez la portée de votre arme) ou votre état ne vous permet pas de la cibler (pas assez de perception) !";
						echo "<br/><center><a class='btn btn-primary' href=\"jouer.php\">retour</a></center>";		
					}
				}
				else {
					echo "Erreur : Vous avez été capturé entre temps !";
					echo "<br/><center><a class='btn btn-primary' href=\"jouer.php\">retour</a></center>";
				}
			}
			else {
				// Tentative de triche
				echo "Pas bien d'essayer de tricher !";
				echo "<br /><center><a class='btn btn-primary' href=\"jouer.php\">retour</a></center>";
				
				$text_triche = "Tentative attaque avec arme qu'on ne possède pas";
				
				$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id', '$text_triche')";
				$mysqli->query($sql);
			}
		}
			
		// 
		if (!isset($id_attaque) || $id_attaque == "" || $id_attaque == "personne") {
			echo "<center>vous devez choisir une cible pour attaquer</center><br>";
			echo "<br/><center><a class='btn btn-primary' href=\"jouer.php\">retour</a></center>";
		}
	}
	else {
		echo "Erreur : La valeur entrée est incorrecte !";
		echo "<br><center><a class='btn btn-primary' href=\"jouer.php\">retour</a></center>";
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
	$_SESSION = array();
	session_destroy();

	header("Location:../index2.php");
}
?>
