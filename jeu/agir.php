<?php
session_start();
require_once("../fonctions.php");
require_once("f_carte.php");
require_once("f_combat.php");
require_once('../mvc/model/Event.php');
require_once('../mvc/model/Carte.php');
require_once('../mvc/model/Perso.php');

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

	$model_event = new Event();
	$model_carte = new Carte();
	$model_perso = new Perso();

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
			
			// arme bien pass??e
			if(isset($id_arme_attaque) && $id_arme_attaque != 1000 && $id_arme_attaque != 2000) {
				
				// Est-ce que le perso poss??de bien l'arme ?
				$sql = "SELECT * FROM perso_as_arme WHERE id_perso='$id' AND id_arme='$id_arme_attaque' AND est_portee='1'";
				$res = $mysqli->query($sql);
				$verif_arme = $res->num_rows;
				
				if ($verif_arme) {
					// Recup??ration des caracs de l'arme
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
			
			if ($verif_arme) {

				if ($porteeMax_arme_attaque > 1 && possede_lunette_visee($mysqli, $id)) {
					$coutPa_arme_attaque = $coutPa_arme_attaque + 2;
				}
			
				if(!in_bat($mysqli, $id) || (in_bat($mysqli, $id) && $porteeMax_arme_attaque > 1)){
					
					// recup des donn??es du perso
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
						
						// R??cup??ration de la couleur associ??e au clan du perso
						$couleur_clan_perso = couleur_clan($clan_perso);
						
						// verification si le perso est bien a port??e d'attaque			
						if(is_a_portee_attaque($mysqli, $carte, $id, $id_cible, $porteeMin_arme_attaque, $porteeMax_arme_attaque, $per_perso)) {
							
							// recuperation des donn??es du perso cible
							$t_cible = get_cible($mysqli, $id_cible);
							
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
							
							// R??cup??ration de la couleur associ??e au clan de la cible
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
										
										// V??rifie si le joueur attaqu?? a coch?? l'envoi de mail
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
											echo "Vous avez lanc?? un soin sur <b>$nom_cible [$id_cible]</b> avec $nom_arme_attaque<br/>";
										} else {
											echo "Vous avez lanc?? une attaque sur <b>$nom_cible [$id_cible]</b> avec $nom_arme_attaque<br/>";
										}
										
										if ($id_inst_bat_cible != 0) {
											$sql = "SELECT id_batiment FROM instance_batiment WHERE id_instanceBat='$id_inst_bat_cible'";
											$res = $mysqli->query($sql);
											$t = $res->fetch_assoc();
											
											$id_bat = $t['id_batiment'];
											
											$bonus_defense_terrain = get_bonus_defense_batiment($id_bat);
										}
										else {
											// O?? se trouve la cible ?
											$sql = "SELECT fond_carte FROM carte WHERE x_carte='$x_cible' AND y_carte='$y_cible'";
											$res = $mysqli->query($sql);
											$t = $res->fetch_assoc();
											
											$fond_carte_cible = $t['fond_carte'];
											
											$bonus_defense_terrain = get_bonus_defense_terrain($fond_carte_cible, $porteeMax_arme_attaque);
										}
										
										// Bonus Pr??cision batiment
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
										echo "Pr??cision : ".$precision_final. " (Base arme : ".$precision_arme_attaque."  -- Bonus Pr??cision objet : ".$bonus_precision_objet."  -- Bonus precision distance : ".$bonus_precision_distance;
										if ($bonus_precision_bat != 0) {
											echo " -- Bonus du batiment : ".$bonus_precision_bat;
										}
										echo " -- Defense cible : ".$bonus_cible." -- Bonus Defense objets cible : ".$bonus_defense_objet." -- Defense terrain cible : ".$bonus_defense_terrain.")<br>";
										
										// Score touche <= precision arme utilis??e - bonus cible pour l'attaque = La cible est touch??e
										if ($touche <= $precision_final && $touche < 98) {

											$degats_final = calcul_degats($id_arme_attaque, $degatMin_arme_attaque, $valeur_des_arme_attaque, $protec_cible, $type_perso_cible);

											// Insertion log attaque
											log_attaque($mysqli, $id, $id_cible, $id_arme_attaque, $degats_final, $touche);
											
											if ($touche <= 2) {
												// Coup critique ! D??gats et Gains PC X 2
												$degats_final = $degats_final * 2;
												$gain_pc = $gain_pc * 2;
											}

											$gain_xp = calcul_gain_xp($xp_perso, $xp_cible, $id_arme_attaque, $coutPa_arme_attaque, $degats_final);
											
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
												
												echo "<br>Vous avez soign?? $degats_final d??g??ts ?? la cible.<br>";
												
												if ($soin_termine) {
													echo "La cible a r??cup??r??e tous ses PV<br>";
												}
												
											} else if ($id_arme_attaque == 11) {
												
												// Bandage
												if ($bonus_cible + $degats_final >= 0) {
													$sql = "UPDATE perso SET bonus_perso=0 WHERE id_perso='$id_cible'";
													echo "<br>Vous avez soign?? tous les malus de la cible.<br><br>";
													
													$soin_termine = true;
												} else {
													$sql = "UPDATE perso SET bonus_perso=bonus_perso+$degats_final WHERE id_perso='$id_cible'";
													echo "<br>Vous avez soign?? $degats_final malus ?? la cible.<br><br>";
												}
												
												$mysqli->query($sql);
												
											} else {
												// mise a jour des pv et des malus de la cible
												$model_perso->infligeDegats($id_cible, $degats_final);
												echo "<br>Vous avez inflig?? $degats_final d??g??ts ?? la cible.<br>";
											}
											
											echo "Vous avez gagn?? $gain_xp xp.";
											if ($max_xp_tour_atteint) {
												echo " (maximum de gain d'xp par tour atteint)";
											}
											echo "<br />";
											
											// mise a jour des xp/pi
											$model_perso->perso_gain_xp($id, $gain_xp);
											$gain_xp_tour_perso += $gain_xp;
											
											// Passage grade grouillot
											passage_grade_grouillot($mysqli, $id, $grade_perso, $xp_perso, $gain_xp);
											
											gain_pc_chef($mysqli, $id, $gain_pc);
											
											if ($id_arme_attaque == 10 || $id_arme_attaque == 11) {
												$model_event->putEventAttaque($id, $couleur_clan_perso, $nom_perso, "soign??", $id_cible, $couleur_clan_cible, $nom_cible, $touche, $precision_final, $degats_final, $gain_xp, $gain_pc);
											} else if($id_arme_attaque == 13 || $id_arme_attaque == 22) {
												$model_event->putEventAttaque($id, $couleur_clan_perso, $nom_perso, "bombard??", $id_cible, $couleur_clan_cible, $nom_cible, $touche, $precision_final, $degats_final, $gain_xp, $gain_pc);
											} else {
												$model_event->putEventAttaque($id, $couleur_clan_perso, $nom_perso, "attaqu??", $id_cible, $couleur_clan_cible, $nom_cible, $touche, $precision_final, $degats_final, $gain_xp, $gain_pc);
											}
											
											check_cible_capturee($mysqli, $carte, $id, $clan_perso, $couleur_clan_perso, $nom_perso, $nom_grade_perso, $id_cible, $clan_cible, $couleur_clan_cible, $nom_cible, $nom_grade_cible, $pi_cible, $or_cible);
											
											// L'arme fait des d??gats de zone
											if ($degatZone_arme_attaque) {
												$degats_collat = floor($degats_final / 2);
												check_degats_zone($mysqli, $carte, $id, $nom_perso, $grade_perso, $type_perso, $id_j_perso, $clan_perso, $couleur_clan_perso, $xp_perso, $id_cible, $x_cible, $y_cible, $degats_collat, $gain_xp, $gain_pc, $gain_xp_tour_perso, $max_xp_tour_atteint, $id_arme_attaque);
											}
										}
										else { // la cible a esquiv?? l'attaque
							
											if ($touche >= 98) {
												echo "<b>Echec critique !</b>";
											}
											echo "<br>Vous avez rat?? votre cible.<br><br>";
											
											if ($id_arme_attaque != 11 && $id_arme_attaque != 10) {
											
												if ($touche >= 98) {
													// Echec critique !
													// Ajout d'un malus suppl??mentaire ?? l'attaquant
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
													// Ajout d'un malus suppl??mentaire ?? l'attaquant
													$sql = "UPDATE perso SET bonus_perso = bonus_perso - 1 WHERE id_perso='$id'";
													$mysqli->query($sql);
												}
											}
											
											// Insertion log attaque
											$message_log = $id.' a rat?? son attaque sur '.$id_cible;
											$type_action = "Attaque ".$id_arme_attaque;
											$sql = "INSERT INTO log (date_log, id_perso, type_action, id_arme, pourcentage, message_log) VALUES (NOW(), '$id', '$type_action', '$id_arme_attaque', '$touche', '$message_log')";
											$mysqli->query($sql);
												
											// maj evenement
											$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_cible,'<font color=$couleur_clan_cible><b>$nom_cible</b></font>','a esquiv?? l\'attaque de','$id','<font color=$couleur_clan_perso><b>$nom_perso</b></font>',' ( Pr??cision : $touche / $precision_final ; Gain XP : 0)',NOW(),'0')";
											$mysqli->query($sql);

											// Regarde si le boulet touche un voisin
											if ($id_arme_attaque == 13 || $id_arme_attaque == 22) {
												// R??cup??ration des cibles potentielles autour de la cible principale
												$res_recherche_voisins = $model_carte->recupereVoisins($id_cible, $x_cible, $y_cible)->fetchAll(PDO::FETCH_CLASS,'Carte');;
												$nb_voisins = count($res_recherche_voisins);

												// On selectionne un voisin qui va se prendre le boulet
												$id_voisin_touche = mt_rand(0,7);

												if ($id_voisin_touche < $nb_voisins){
													$tableau_cibles = array();
													foreach($res_recherche_voisins as $t_v) {
														$id_cible 		= $t_v->idPerso_carte;
														array_push($tableau_cibles, $id_cible);
													}

													$id_voisin = $tableau_cibles[array_rand($tableau_cibles)];

													// Calcul touche
													$touche = mt_rand(0,100);
													$precision_final = 100; //TODO: pour le moment touche ?? tous les coups s'il y a une unit?? sur la case, peut-??tre envisager tenir compte du bonus de l'unit??.
													if ($touche <= $precision_final && ($id_voisin < 50000 || $id_voisin >= 200000)){
														$t_cible = get_cible($mysqli, $id_voisin);
														$protec_cible		= $t_cible["protec_perso"];
														$xp_cible 		= $t_cible["xp_perso"];
														$grade_cible		= $t_cible['id_grade'];
														$clan_cible 		= $t_cible["clan"];
														$id_joueur_cible 	= $t_cible["idJoueur_perso"];
														$nom_cible 		= $t_cible["nom_perso"];
														$clan_cible 		= $t_cible["clan"];
														$type_perso_cible	= $t_cible["type_perso"];
														$x_cible 			= $t_cible["x_perso"];
														$y_cible 			= $t_cible["y_perso"];

														$couleur_clan_cible = couleur_clan($clan_cible);

														$degats_final = calcul_degats($id_arme_attaque, $degatMin_arme_attaque, $valeur_des_arme_attaque, $protec_cible, $type_perso_cible);
														$gain_xp = calcul_gain_xp($xp_perso, $xp_cible, $id_arme_attaque, $coutPa_arme_attaque, $degats_final);
														if ($gain_xp_tour_perso + $gain_xp > 20) {
															$gain_xp = 20 - $gain_xp_tour_perso;
															$max_xp_tour_atteint = true;
														}
														$model_perso->infligeDegats($id_voisin, $degats_final);
														$model_perso->perso_gain_xp($id, $gain_xp);
														$gain_xp_tour_perso += $gain_xp;
														passage_grade_grouillot($mysqli, $id, $grade_perso, $xp_perso, $gain_xp);

														$gain_pc = calcul_gain_pc_attaque_perso($grade_perso, $grade_cible, $clan_perso, $clan_cible, $type_perso, $id_j_perso, $id_joueur_cible);
														gain_pc_chef($mysqli, $id, $gain_pc);

														echo "Votre boulet a rencontr?? un obstacle ! Vous avez inflig?? $degats_final d??g??ts ?? <font color='$couleur_clan_cible'>$nom_cible</font> - Matricule $id_voisin.<br>";
														echo "Vous avez gagn?? $gain_xp xp.<br />";

														$model_event->putEventAttaque($id, $couleur_clan_perso, $nom_perso, "atteint", $id_voisin, $couleur_clan_cible, $nom_cible, $touche, $precision_final, $degats_final, $gain_xp, $gain_pc);
														check_cible_capturee($mysqli, $carte, $id, $clan_perso, $couleur_clan_perso, $nom_perso, $nom_grade_perso, $id_cible, $clan_cible, $couleur_clan_cible, $nom_cible, $nom_grade_cible, $pi_cible, $or_cible);
														// L'arme fait des d??gats de zone
														$degats_collat = floor($degats_final / 2);
														check_degats_zone($mysqli, $carte, $id, $nom_perso, $grade_perso, $type_perso, $id_j_perso, $clan_perso, $couleur_clan_perso, $xp_perso, $id_voisin, $x_cible, $y_cible, $degats_collat, $gain_xp, $gain_pc, $gain_xp_tour_perso, $max_xp_tour_atteint, $id_arme_attaque);
													}
												} else {
													echo "Fichtre ! Votre boulet atterrit violemment dans un ??pais buisson. <br>";
												}

											}
							
										}
										
										//mise a jour des pa
										$sql = "UPDATE perso SET pa_perso=pa_perso-$coutPa_arme_attaque WHERE id_perso='$id'";
										$res = $mysqli->query($sql); 
										
										if ($id_arme_attaque == 10 || $id_arme_attaque == 11) {
											$texte_submit = "soigner ?? nouveau";
										} else {
											$texte_submit = "attaquer ?? nouveau";
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
										echo "Loi anti-zerk non respect??e !";
										echo "<br><center><a class='btn btn-primary' href=\"jouer.php\">retour</a></center>";
									}
								}			
								else {
									//la cible est d??j?? morte
									echo "Erreur : La cible est d??j?? morte !";
									echo "<br><center><a class='btn btn-primary' href=\"jouer.php\">retour</a></center>";
								}
							}
						}
						else { 
							if($id_cible == $id){
								echo "Erreur : Vous ne pouvez pas vous attaquez vous m??me...";
								echo "<br /><center><a class='btn btn-primary' href=\"jouer.php\">retour</a></center>";
							}
							else {
								// la cible n'est pas ?? port??e d'attaque
								echo "Erreur : La cible n'est pas ?? port??e d'attaque (V??rifiez la port??e de votre arme) ou votre ??tat ne vous permet pas de la cibler (pas assez de perception)  !";
								echo "<br /><center><a class='btn btn-primary' href=\"jouer.php\">retour</a></center>";
							}
						}
					}
					else {
						echo "Erreur : vous avez ??t?? captur?? entre temps !";
						echo "<br /><center><a class='btn btn-primary' href=\"jouer.php\">retour</a></center>";
					}
				}
				else {
					echo "Erreur : Il est impossible d'attaquer un perso depuis l'int??rieur d'un batiment avec une arme de Corps ?? corps!";
					echo "<br /><center><a class='btn btn-primary' href=\"jouer.php\">retour</a></center>";
				}
			}
			else {
				// Tentative de triche
				echo "Pas bien d'essayer de tricher !";
				echo "<br /><center><a class='btn btn-primary' href=\"jouer.php\">retour</a></center>";
				
				$text_triche = "Tentative attaque avec arme qu'on ne poss??de pas";
				
				$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id', '$text_triche')";
				$mysqli->query($sql);
			}
		}
		
		//traitement de l'attaque sur un pnj
		if ((isset($id_attaque) && $id_attaque != "" && $id_attaque != "personne" && $id_attaque >= 200000) ) {
		
			$id_cible =  $id_attaque;
			$verif_arme = 0;
			
			// arme bien pass??e
			if(isset($id_arme_attaque) && $id_arme_attaque != 1000 && $id_arme_attaque != 2000) {
				
				// Est-ce que le perso poss??de bien l'arme ?
				$sql = "SELECT * FROM perso_as_arme WHERE id_perso='$id' AND id_arme='$id_arme_attaque' AND est_portee='1'";
				$res = $mysqli->query($sql);
				$verif_arme = $res->num_rows;
				
				// Recup??ration des caracs de l'arme
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
				$coutPa_arme_attaque = $coutPa_arme_attaque + 2;
			}
			
			if ($verif_arme) {
			
				// recup des donn??es du perso
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
					
					// R??cup??ration de la couleur associ??e au clan du perso
					$couleur_clan_perso = couleur_clan($clan_perso);
					
					if(is_a_portee_attaque($mysqli, $carte, $id, $id_cible, $porteeMin_arme_attaque, $porteeMax_arme_attaque, $per_perso)) {
							
						// recuperation des donn??es du pnj		
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
						
						// on verifie si le perso a d??ja tu?? ce type de pnj et on en r??cup??re le nombre
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

								// -------------
								// - ANTI ZERK -
								// -------------
								$verif_anti_zerk = gestion_anti_zerk($mysqli, $id);

								if ($verif_anti_zerk) {

									// maj dernierAttaquant_i
									$sql = "UPDATE instance_pnj SET dernierAttaquant_i = $id WHERE idInstance_pnj = '$id_cible'";
									$mysqli->query($sql);

									echo "Vous avez lanc?? une attaque sur <b>$nom_cible [$id_cible]</b> avec $nom_arme_attaque<br/>";

									// Calcul touche
									$touche = mt_rand(0,100);

									// O?? se trouve la cible ?
									$sql = "SELECT fond_carte FROM carte WHERE x_carte='$x_cible' AND y_carte='$y_cible'";
									$res = $mysqli->query($sql);
									$t = $res->fetch_assoc();

									$fond_carte_cible = $t['fond_carte'];

									$bonus_defense_terrain = get_bonus_defense_terrain($fond_carte_cible, $porteeMax_arme_attaque);

									// Bonus Pr??cision batiment
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
									echo "Pr??cision : ".$precision_final. " (Base arme : ".$precision_arme_attaque;
									if ($bonus_precision_bat != 0) {
										echo " -- Bonus du batiment : ".$bonus_precision_bat;
									}
									echo " -- Defense cible : ".$bonus_cible." -- Defense terrain : ".$bonus_defense_terrain." -- Bonus Pr??cision objet : ".$bonus_precision_objet.")<br>";

									// Score touche <= precision arme utilis??e - bonus cible pour l'attaque = La cible est touch??e
									if ($touche <= $precision_final && $touche < 98) {

										// calcul degats arme
										$degats_tmp 	= calcul_des_attaque($degatMin_arme_attaque, $valeur_des_arme_attaque);
										$degats_final 	= $degats_tmp - $protec_cible;

										// Insertion log attaque
										$message_log = $id.' a attaqu?? '.$id_cible;
										$type_action = "Attaque ".$id_arme_attaque;
										$sql = "INSERT INTO log (date_log, id_perso, type_action, id_arme, degats, pourcentage, message_log) VALUES (NOW(), '$id', '$type_action', '$id_arme_attaque', '$degats_tmp', '$touche', '$message_log')";
										$mysqli->query($sql);

										if($degats_final < 0) {
											$degats_final = 0;
										}

										if ($touche <= 2) {
											// Coup critique ! D??gats et Gains PC X 2
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

											echo "<br>Vous avez soign?? $degats_final d??g??ts ?? la cible.<br><br>";

										} else if ($id_arme_attaque == 11) {

											// Bandage
											echo "<br>Vous avez soign?? $degats_final malus ?? la cible.<br><br>";

										} else {

											// mise a jour des pv du pnj
											$sql = "UPDATE instance_pnj SET pv_i = pv_i - $degats_final WHERE idInstance_pnj = '$id_cible'";
											$mysqli->query($sql);

											echo "<br>Vous avez inflig?? <b>$degats_final</b> d??g??ts ?? la cible.<br><br>";
										}

										echo "Vous avez gagn?? <b>$gain_xp</b> xp.";

										// maj gain xp / pi perso
										$sql = "UPDATE perso SET xp_perso=xp_perso+$gain_xp, pi_perso=pi_perso+$gain_xp, gain_xp_tour=gain_xp_tour+$gain_xp WHERE id_perso='$id'";
										$mysqli->query($sql);

										// Passage grade grouillot
										passage_grade_grouillot($mysqli, $id, $grade_perso, $xp_perso, $gain_xp);

										if ($id_arme_attaque == 10 || $id_arme_attaque == 11) {

											// maj evenement
											$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>','a soign?? ','$id_cible','<b>$nom_cible</b>',' ( Pr??cision : $touche / $precision_final ; Soins : $degats_final ; Gain XP : $gain_xp ; Gain PC : 0 )',NOW(),'0')";
											$mysqli->query($sql);

										} else {

											// maj evenement
											$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>','a attaqu?? ','$id_cible','<b>$nom_cible</b>',' ( Pr??cision : $touche / $precision_final ; D??g??ts : $degats_final ; Gain XP : $gain_xp ; Gain PC : 0 )',NOW(),'0')";
											$mysqli->query($sql);

										}

										// recuperation des donn??es du pnj apr??s attaque
										$sql = "SELECT id_pnj, pv_i, x_i, y_i FROM instance_pnj WHERE idInstance_pnj='$id_cible'";
										$res = $mysqli->query($sql);
										$tab = $res->fetch_assoc();

										$pv_cible = $tab["pv_i"];
										$x_cible = $tab["x_i"];
										$y_cible = $tab["y_i"];
										$id_pnj = $tab["id_pnj"];

										// il est mort
										if ($pv_cible <= 0) {

											echo "Vous avez tu?? votre cible ! <font color=red>F??licitations.</font>";

											// on l'efface de la carte
											$sql = "UPDATE $carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_cible' AND y_carte='$y_cible'";
											$mysqli->query($sql);

											// on le delete
											$sql = "DELETE FROM instance_pnj WHERE idInstance_pnj='$id_cible'";
											$mysqli->query($sql);

											// verification que le perso n'a pas d??j?? tu?? ce type de pnj
											$sql_v = "SELECT id_pnj FROM perso_as_killpnj WHERE id_pnj='$id_pnj' AND id_perso='$id'";
											$res_v = $mysqli->query($sql_v);
											$verif_pnj = $res_v->num_rows;

											// nb_pnj 
											$sql = "UPDATE perso SET nb_pnj=nb_pnj+1 WHERE id_perso='$id'";
											$mysqli->query($sql);

											if($verif_pnj == 0){
												// il n'a jamais tu?? de pnj de ce type => insert
												$sql = "INSERT INTO perso_as_killpnj VALUES('$id','$id_pnj','1')";
												$mysqli->query($sql);
											}
											else { 
												// il en a d??j?? tu?? => update
												$sql = "UPDATE perso_as_killpnj SET nb_pnj=nb_pnj+1 WHERE id_perso='$id' AND id_pnj='$id_pnj'";
												$mysqli->query($sql);
											}

											// maj evenement
											$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>','a tu??','$id_cible','<b>$nom_cible</b>','',NOW(),'0')";
											$mysqli->query($sql);

											// maj cv
											$sql = "INSERT INTO `cv` (IDActeur_cv, nomActeur_cv, gradeActeur_cv, IDCible_cv, nomCible_cv, date_cv) VALUES ($id,'<font color=$couleur_clan_perso>$nom_perso</font>', '$nom_grade_perso', '$id_cible','$nom_cible',NOW())";
											$mysqli->query($sql);

											echo "<br><center><a class='btn btn-primary' href=\"jouer.php\">retour</a></center>";
										}

										// L'arme fait des d??gats de zone
										if ($degatZone_arme_attaque) {
											$degats_collat = floor($degats_final / 2);
											check_degats_zone($mysqli, $carte, $id, $nom_perso, $grade_perso, $type_perso, $id_j_perso, $clan_perso, $couleur_clan_perso, $xp_perso, $id_cible, $x_cible, $y_cible, $degats_collat, $gain_xp, 0, $gain_xp_tour_perso, $max_xp_tour_atteint, $id_arme_attaque);
										}

										if ($pv_cible > 0) {
?>
										<br />
										<form action="agir.php" method="post">
											<input type="hidden" name="re_attaque_hid" value="<?php echo $id_cible.",".$id_arme_attaque;?>" />
											<input type="submit" name="re_attaque" value="attaquer ?? nouveau" />
										</form> 

										<br />
<?php
											echo "<br/><center><a class='btn btn-primary' href=\"jouer.php\">retour</a></center>";
										}						
									}
									else { // la cible a esquiv?? l'attaque

										echo "<br>Vous avez rat?? votre cible.<br><br>";

										if ($touche >= 98) {
											// Echec critique !
											// Ajout d'un malus suppl??mentaire ?? l'attaquant
											$sql = "UPDATE perso SET bonus_perso = bonus_perso - 1 WHERE id_perso='$id'";
										} else {
											// ajout malus cible
											$sql = "UPDATE instance_pnj SET bonus_i = bonus_i - 1 WHERE idInstance_pnj='$id_cible'";
										}
										$mysqli->query($sql);

										// Insertion log attaque
										$message_log = $id.' a rat?? son attaque sur '.$id_cible;
										$type_action = "Attaque ".$id_arme_attaque;
										$sql = "INSERT INTO log (date_log, id_perso, type_action, id_arme, pourcentage, message_log) VALUES (NOW(), '$id', '$type_action', '$id_arme_attaque', '$touche', '$message_log')";
										$mysqli->query($sql);

										// maj evenement
										$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_cible,'<b>$nom_cible</b>','a esquiv?? l\'attaque de','$id','<font color=$couleur_clan_perso><b>$nom_perso</b></font>',' ( Pr??cision : $touche / $precision_final ; Gain XP : 0)',NOW(),'0')";
										$mysqli->query($sql);

										if ($pv_cible > 0) {

											if ($id_arme_attaque == 10 || $id_arme_attaque == 11) {
												$texte_submit = "soigner ?? nouveau";
											} else {
												$texte_submit = "attaquer ?? nouveau";
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

									//mise ?? jour des pa
									$sql = "UPDATE perso SET pa_perso=pa_perso-$coutPa_arme_attaque WHERE id_perso='$id'";
									$res = $mysqli->query($sql);
								}
								else {
									echo "Loi anti-zerk non respect??e !";
									echo "<br><center><a class='btn btn-primary' href=\"jouer.php\">retour</a></center>";
								}
							}
							else {
								
								//la cible est d??j?? morte
								echo "Erreur : La cible est d??j?? morte !";
								echo "<br/><center><a class='btn btn-primary' href=\"jouer.php\">retour</a></center>";
							}
						}
					}
					else { // la cible n'est pas ?? port??e d'attaque
						echo "Erreur : La cible n'est pas ?? port??e d'attaque (V??rifiez la port??e de votre arme) ou votre ??tat ne vous permet pas de la cibler (pas assez de perception) !";
						echo "<br/><center><a class='btn btn-primary' href=\"jouer.php\">retour</a></center>";
					}
				}
				else {
					echo "Erreur : vous avez ??t?? captur?? entre temps !";
					echo "<br/><center><a class='btn btn-primary' href=\"jouer.php\">retour</a></center>";
				}
			}
			else {
				// Tentative de triche
				echo "Pas bien d'essayer de tricher !";
				echo "<br /><center><a class='btn btn-primary' href=\"jouer.php\">retour</a></center>";
				
				$text_triche = "Tentative attaque avec arme qu'on ne poss??de pas";
				
				$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id', '$text_triche')";
				$mysqli->query($sql);
			}
		}
		
		// Traitement attaque Batiment
		if ((isset($id_attaque) && $id_attaque != "" && $id_attaque != "personne" && $id_attaque >= 50000 && $id_attaque < 200000)) {
			
			$id_cible =  $id_attaque;
			$verif_arme = 0;
			
			// arme bien pass??e
			if(isset($id_arme_attaque) && $id_arme_attaque != 1000 && $id_arme_attaque != 2000) {
				
				// Est-ce que le perso poss??de bien l'arme ?
				$sql = "SELECT * FROM perso_as_arme WHERE id_perso='$id' AND id_arme='$id_arme_attaque' AND est_portee='1'";
				$res = $mysqli->query($sql);
				$verif_arme = $res->num_rows;
				
				// Recup??ration des caracs de l'arme
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
				$coutPa_arme_attaque = $coutPa_arme_attaque + 2;
			}
			
			if ($verif_arme) {
			
				// recup des donn??es du perso
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
					
					// R??cup??ration de la couleur associ??e au clan du perso
					$couleur_clan_perso = couleur_clan($clan_perso);
					
					if(is_a_portee_attaque($mysqli, $carte, $id, $id_cible, $porteeMin_arme_attaque, $porteeMax_arme_attaque, $per_perso)) {
					
						$coutPa_attaque=$coutPa_arme_attaque;
								
						// recuperation des donn??es du batiment	
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
								
								// Est-ce que le b??timent est de notre camp ?
								if ($camp_instance == $clan_perso) {
									
									// calcul pourcentage PV du b??timent
									$pourc_pv_bat = ($pv_instance / $pvMax_instance) * 100;;
									
									if (nb_ennemis_siege_batiment($mysqli, $x_instance, $y_instance, $clan_perso) >= 10 && $pourc_pv_bat <= 25 
											&& ($id_batiment == 6 || $id_batiment == 7 || $id_batiment == 8 || $id_batiment == 9 || $id_batiment == 10 || $id_batiment == 11)) {
										$autorisation_attaque = false;
									}
								}

								$verif_anti_zerk = gestion_anti_zerk($mysqli, $id);
								
								if ($autorisation_attaque && $verif_anti_zerk) {
								
									echo "Vous avez lanc?? une attaque sur <b>$nom_batiment [$id_cible]</b>.<br>";
									
									$gain_xp = 1;
									if ($camp_instance != $clan_perso)
										$gain_xp = mt_rand(1,3);
									
									if ($gain_xp_tour_perso + $gain_xp > 20) {
										$gain_xp = 20 - $gain_xp_tour_perso;
										$max_xp_tour_atteint = true;
									}
						
									// Calcul touche
									$touche = mt_rand(0,100);
									
									// Bonus Pr??cision batiment
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
									echo "Pr??cision : ".$precision_final. " (Base arme : ".$precision_arme_attaque." -- Bonus Pr??cision objet : ".$bonus_precision_objet."";
									if ($bonus_precision_bat != 0) {
										echo " -- Bonus du batiment : ".$bonus_precision_bat."";
									}
									echo ")<br>";
									
									// Score touche <= precision arme utilis??e - bonus cible pour l'attaque = La cible est touch??e
									if ($touche <= $precision_final && $touche < 98) {
						
										// calcul degats arme
										$degats_final = calcul_des_attaque($degatMin_arme_attaque, $valeur_des_arme_attaque);
										
										// Insertion log attaque
										$message_log = $id.' a attaqu?? '.$id_cible;
										$type_action = "Attaque ".$id_arme_attaque;
										$sql = "INSERT INTO log (date_log, id_perso, type_action, id_arme, degats, pourcentage, message_log) VALUES (NOW(), '$id', '$type_action', '$id_arme_attaque', '$degats_final', '$touche', '$message_log')";
										$mysqli->query($sql);
										
										if($degats_final < 0) {
											$degats_final = 0;
										}
										
										if ($touche <= 2) {
											// Coup critique ! D??gats et Gains PC X 2
											$degats_final = $degats_final * 2;
										}
										
										// Canon d'artillerie
										$bonus_degats_canon = 0;
										if ($id_arme_attaque == 13 || $id_arme_attaque == 22) {
											// Bonus d??gats 20D10
											$bonus_degats_canon = calcul_des_attaque(20, 10);
											$degats_final = $degats_final + $bonus_degats_canon;
										}
										
										// mise ?? jour des pv du batiment
										$sql = "UPDATE instance_batiment SET pv_instance=pv_instance-$degats_final WHERE id_instanceBat='$id_cible'";
										$mysqli->query($sql);
										
										echo "<br>Vous avez inflig?? <b>$degats_final</b> degats ?? la cible.<br><br>";
										echo "Vous avez gagn?? <b>$gain_xp</b> xp";
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
										$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>','a attaqu?? ','$id_cible','<font color=$couleur_bat><b>$nom_batiment</b></font>',' ( Pr??cision : $touche / $precision_final ; D??g??ts : $degats_final ; Gain XP : $gain_xp ; Gain PC : 0 )',NOW(),'0')";
										$mysqli->query($sql);					
											
										// recuperation des donn??es du batiment apr??s attaque
										$sql = "SELECT id_batiment, pv_instance, x_instance, y_instance FROM instance_batiment WHERE id_instanceBat='$id_cible'";
										$res = $mysqli->query($sql);
										$tab = $res->fetch_assoc();
										
										$pv_cible 		= $tab["pv_instance"];
										$x_cible 		= $tab["x_instance"];
										$y_cible 		= $tab["y_instance"];
										$id_batiment 	= $tab["id_batiment"];
										
										/* D??but du traitement de la destruction du batiment*/
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
											
											// Mise ?? jour des respawn
											$sql = "DELETE FROM perso_as_respawn WHERE id_instance_bat='$id_cible'";
											$mysqli->query($sql);
											
											$sql = "SELECT MAX(x_carte) as x_max, MAX(y_carte) as y_max FROM carte";
											$res = $mysqli->query($sql);
											$t = $res->fetch_assoc();
											
											$X_MAX = $t['x_max'];
											$Y_MAX  = $t['y_max'];
											
											if (is_train($mysqli, $id_cible)) {
												// R??cup??ration des persos dans le train
												$sql = "SELECT id_perso FROM perso_in_train WHERE id_train='$id_cible'";
											}
											else {
												// R??cup??ration des persos dans le batiment
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
												
												// R??cup??ration de la couleur associ??e au clan du perso
												$couleur_clan_p = couleur_clan($clan_p);
												
												// maj evenement
												$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ('$id_p','<font color=$couleur_clan_p><b>$nom_p</b></font>','a ??t?? bless?? suite ?? la destruction du b??timent',NULL,'',' : $perte_pv degats',NOW(),'0')";
												$mysqli->query($sql);
												
												// Le perso est encore vivant
												if($pv_p > 0){
													
													// pv/2
													$sql_p = "UPDATE perso SET pv_perso=pv_perso/2 WHERE id_perso='$id_p'";
													$mysqli->query($sql_p);
													
													// Traitement r??partissement des persos sur la carte
													$verif_occ = 0;
													while (!$verif_occ)
													{
														// TODO - changer fa??on de faire 
														// TODO - Tourner autour de la position du batiment jusqu'?? ce qu'on trouve une position libre
														$x = pos_zone_rand_x($x_cible-5, $x_cible+5); 
														$y = pos_zone_rand_y($y_cible-5, $y_cible+5);
														$verif_occ = verif_position_libre($mysqli, $x, $y, $X_MAX, $Y_MAX);
													}
													
													// maj evenement
													$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ('$id_p','<font color=$couleur_clan_p><b>$nom_p</b></font>','a ??t?? ??ject?? suite ?? la destruction du b??timent',NULL,'',' : en $x / $y et PV divis?? par 2',NOW(),'0')";
													$mysqli->query($sql);
													
													// MAJ du perso sur la carte
													$sql_u = "UPDATE carte SET occupee_carte = '1', image_carte='$image_p', idPerso_carte='$id_p' WHERE x_carte='$x' AND y_carte='$y'";
													$mysqli->query($sql_u);
													
													// MAJ des coordonn??es du perso
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
													$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>','<b>a captur??</b>','$id_p','<font color=$couleur_clan_p><b>$nom_p</b></font> ($nom_grade_p)',' : mort suite ?? l\'explosion du b??timent $id_cible',NOW(),'0')";
													$mysqli->query($sql);
														
													// maj cv
													$sql = "INSERT INTO `cv` (IDActeur_cv, nomActeur_cv, gradeActeur_cv, IDCible_cv, nomCible_cv, gradeCible_cv, date_cv) VALUES ($id,'<font color=$couleur_clan_perso>$nom_perso</font>', '$nom_grade_perso', '$id_p','<font color=$couleur_clan_p>$nom_p</font>', '$nom_grade_p', NOW())"; 
													$mysqli->query($sql);
													
													// maj stats camp
													if($clan_p != $clan_perso){
														$sql = "UPDATE stats_camp_kill SET nb_kill=nb_kill+1 WHERE id_camp=$clan_perso";
														$mysqli->query($sql);
													}
													
													// maj dernier tomb??
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
											
											// on delete le b??timent
											$sql = "DELETE FROM instance_batiment WHERE id_instanceBat='$id_cible'";
											$mysqli->query($sql);
											
											// on delete les canons rattach??s au batiment 
											$sql = "DELETE FROM instance_batiment_canon WHERE id_instance_bat='$id_cible'";
											$mysqli->query($sql);
									
											echo "Vous avez d??truit votre cible ! <font color=red>F??licitations.</font>";
												
											// maj evenement
											$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>','a d??truit','$id_cible','<font color=$couleur_bat><b>$nom_batiment $nom_instance_batiment</b></font>','',NOW(),'0')";
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
												$texte = addslashes("Pour la destruction du b??timent ".$nom_batiment." ".$nom_instance_batiment." [".$id_cible."] par ".$nom_perso." [".$id."]");
												if ($clan_perso != $camp_instance) {
													$sql = "INSERT INTO histo_stats_camp_pv (date_pvict, id_camp, gain_pvict, texte) VALUES (FROM_UNIXTIME($date), '$clan_perso', '$gain_pvict', '$texte')";
												}
												else {
													$sql = "INSERT INTO histo_stats_camp_pv (date_pvict, id_camp, gain_pvict, texte) VALUES (FROM_UNIXTIME($date), '$clan_perso', '-$gain_pvict', '$texte')";
												}
												$mysqli->query($sql);
												
											}

											// L'arme fait des d??gats de zone
											if ($degatZone_arme_attaque) {
												$degats_collat = floor(($degats_final - $bonus_degats_canon) / 3);
												check_degats_zone($mysqli, $carte, $id, $nom_perso, $grade_perso, $type_perso, $id_j_perso, $clan_perso, $couleur_clan_perso, $xp_perso, $id_cible, $x_cible, $y_cible, $degats_collat, $gain_xp, 0, $gain_xp_tour_perso, $max_xp_tour_atteint, $id_arme_attaque);
											}

												
											echo "<br><center><a class='btn btn-primary' href=\"jouer.php\">retour</a></center>";
										}
										/* Fin du traitement de la destruction du b??timent*/
										
										//mise ?? jour des pa
										$sql = "UPDATE perso SET pa_perso=pa_perso-$coutPa_attaque WHERE id_perso='$id'";
										$res = $mysqli->query($sql);
										
										if ($pv_cible > 0) { 
											?>
												<form action="agir.php" method="post">
													<input type="hidden" name="re_attaque_hid" value="<?php echo $id_cible.",".$id_arme_attaque;?>" />
													<input type="submit" name="re_attaque" value="attaquer ?? nouveau">
												</form> 
												
											<?php
											echo "<br/><center><a class='btn btn-primary' href=\"jouer.php\">retour</a></center>";
										}
									}
									else {
										
										echo "<br>Vous avez rat?? votre cible.<br><br>";
										
										if ($touche >= 98) {
											// Echec critique !
											// Ajout d'un malus suppl??mentaire ?? l'attaquant
											$sql = "UPDATE perso SET bonus_perso = bonus_perso - 1 WHERE id_perso='$id'";
											$mysqli->query($sql);
										}
										
										//mise ?? jour des pa
										$sql = "UPDATE perso SET pa_perso=pa_perso-$coutPa_attaque WHERE id_perso='$id'";
										$mysqli->query($sql);
										
										// Insertion log attaque
										$message_log = $id.' a rat?? son attaque sur '.$id_cible;
										$type_action = "Attaque ".$id_arme_attaque;
										$sql = "INSERT INTO log (date_log, id_perso, type_action, id_arme, pourcentage, message_log) VALUES (NOW(), '$id', '$type_action', '$id_arme_attaque', '$touche', '$message_log')";
										$mysqli->query($sql);
											
										// maj evenement
										$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>',' a rat?? son attaque contre','$id_cible','<font color=$couleur_bat><b>$nom_batiment</b></font>',' ( Pr??cision : $touche / $precision_final ; Gain XP : 0)',NOW(),'0')";
										$mysqli->query($sql);
										
										?>
											<form action="agir.php" method="post">
												<input type="hidden" name="re_attaque_hid" value="<?php echo $id_cible.",".$id_arme_attaque;?>" />
												<input type="submit" name="re_attaque" value="attaquer ?? nouveau">
											</form> 
											
										<?php
										echo "<br/><center><a class='btn btn-primary' href=\"jouer.php\">retour</a></center>";
									}
								}
								else if (!$autorisation_attaque) {
									echo "Erreur : Vous n'avez plus le droit d'attaquer votre b??timent car il poss??de moins de 25% de ses PV et est encercl?? par l'ennemi !";
									echo "<br/><center><a class='btn btn-primary' href=\"jouer.php\">retour</a></center>";
								}
								else if (!$verif_anti_zerk) {
									echo "Loi anti-zerk non respect??e !";
									echo "<br/><center><a class='btn btn-primary' href=\"jouer.php\">retour</a></center>";
								}
							}
							else {
								//la cible est d??j?? morte
								echo "Erreur : La cible est d??j?? morte !";
								echo "<br/><center><a class='btn btn-primary' href=\"jouer.php\">retour</a></center>";
							}
						}
					}
					else {
						// la cible n'est pas ?? port??e d'attaque
						echo "Erreur : La cible n'est pas ?? port??e d'attaque (V??rifiez la port??e de votre arme) ou votre ??tat ne vous permet pas de la cibler (pas assez de perception) !";
						echo "<br/><center><a class='btn btn-primary' href=\"jouer.php\">retour</a></center>";		
					}
				}
				else {
					echo "Erreur : Vous avez ??t?? captur?? entre temps !";
					echo "<br/><center><a class='btn btn-primary' href=\"jouer.php\">retour</a></center>";
				}
			}
			else {
				// Tentative de triche
				echo "Pas bien d'essayer de tricher !";
				echo "<br /><center><a class='btn btn-primary' href=\"jouer.php\">retour</a></center>";
				
				$text_triche = "Tentative attaque avec arme qu'on ne poss??de pas";
				
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
		echo "Erreur : La valeur entr??e est incorrecte !";
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
