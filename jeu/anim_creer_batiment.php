<?php
session_start();
require_once("../fonctions.php");
require_once("f_carte.php");
require_once("f_combat.php");
require_once("f_action.php");

$mysqli = db_connexion();

include ('../nb_online.php');
include ('../forum/config.php');

if(isset($_SESSION["id_perso"])){
	
	$id_perso = $_SESSION['id_perso'];
	
	// recupération config jeu
	$admin = admin_perso($mysqli, $id_perso);
	$anim = anim_perso($mysqli, $id_perso);
	
	if($anim){
		
		$mess_err 	= "";
		$mess 		= "";
		
		if (isset($_POST['select_batiment']) && $_POST['select_batiment'] != ""
			&& isset($_POST['coord_x_placement']) && trim($_POST['coord_x_placement']) != ""
			&& isset($_POST['coord_y_placement']) && trim($_POST['coord_y_placement']) != "") {
			
			$id_bat		= $_POST['select_batiment'];
			$x_bat		= $_POST['coord_x_placement'];
			$y_bat		= $_POST['coord_y_placement'];
			$camp_bat	= $_POST['select_camp'];
			$verif 		= $_POST['select_verifications'];
			
			$nom_instance = '';
			
			$couleur_clan_bat = couleur_clan($camp_bat);
			
			if($camp_bat == '1'){
				$bat_camp = "b";
			}
			else if($camp_bat == '2'){
				$bat_camp = "r";
			}
			else if($camp_bat == '3'){
				$bat_camp = "g";
			}
			
			// recuperation du nom du batiment
			$sql = "SELECT nom_batiment, taille_batiment FROM batiment WHERE id_batiment='$id_bat'";
			$res = $mysqli->query($sql);
			$tb = $res->fetch_assoc();
			
			$nom_bat 	= $tb["nom_batiment"];
			$taille_bat = $tb["taille_batiment"];
			
			$sql = "SELECT MAX(x_carte) as x_max, MAX(y_carte) as y_max FROM carte";
			$res = $mysqli->query($sql);
			$t = $res->fetch_assoc();
			
			$X_MAX = $t['x_max'];
			$Y_MAX  = $t['y_max'];
			
			$verif_occ_in_map = verif_position_libre($mysqli, $x_bat, $y_bat, $X_MAX, $Y_MAX);
			
			if ($verif_occ_in_map) {
					
				$verif_fond_carte = true;

				$sql = "SELECT fond_carte FROM carte WHERE x_carte='$x_bat' AND y_carte='$y_bat'";
				$res = $mysqli->query($sql);
				$t_f = $res->fetch_assoc();
				
				$fond_carte = $t_f['fond_carte'];
				
				if ($id_bat == 1) {
					// Barricade peut être construite sur rail
					if ($fond_carte != 'rail.gif' && $fond_carte != 'rail_1.gif' && $fond_carte != 'rail_2.gif' && $fond_carte != 'rail_3.gif' && $fond_carte != 'rail_4.gif' && $fond_carte != 'rail_5.gif' && $fond_carte != 'railP.gif' 
							&& $fond_carte != '1.gif') {
						$verif_fond_carte = false;
					}
				}
				else if ($id_bat == 5) {
					// Pont sur eau ou eau profonde
					if($fond_carte != '8.gif' && $fond_carte != '9.gif') {
						$verif_fond_carte = false;
					}
				}
				else {
					if ($fond_carte != '1.gif') {
						$verif_fond_carte = false;
					}
				}
				
				if ($verif_fond_carte) {								
				
					$gain_xp = 1;
					
					if ($verif == 1) {
						// Autorisations de construction - vérification des contraintes
						$autorisation_construction_gc 		= true;
						$autorisation_construction_ennemis 	= true;
						$autorisation_construction_bats 	= true;
					}
					else {
						// Autorisations de construction - vérification des contraintes
						$autorisation_construction_gc 		= verif_contraintes_construction($mysqli, $id_bat, $camp_bat, $x_bat, $y_bat);
						$autorisation_construction_ennemis 	= verif_contraintes_construction_ennemis($mysqli, $id_bat, $camp_bat, $x_bat, $y_bat);
						$autorisation_construction_bats 	= verif_contraintes_construction_bat($mysqli, $id_bat, $camp_bat, $x_bat, $y_bat);
					}
					
					$autorisation_construction_taille = true;
					
					$taille_search = floor($taille_bat / 2);
					
					if ($taille_bat > 1) {
						
						// verification carte pour construction 
						$sql = "SELECT occupee_carte, fond_carte FROM carte 
								WHERE x_carte <= $x_bat + $taille_search AND x_carte >= $x_bat - $taille_search AND y_carte <= $y_bat + $taille_search AND y_carte >= $y_bat - $taille_search";
						$res = $mysqli->query($sql);
						
						while ($t = $res->fetch_assoc()) {
							
							$occupee_carte 	= $t["occupee_carte"];
							$fond_carte 	= $t["fond_carte"];
							
							if ($occupee_carte || $fond_carte != '1.gif') {
								$autorisation_construction_taille = false;
							}
						}
					}
					
					if($autorisation_construction_gc){
						
						if ($autorisation_construction_ennemis) {
							
							if ($autorisation_construction_bats) {
						
								if ($autorisation_construction_taille) {
									
									// recuperation des donnees necessaires pour la construction du batiment
									$sql = "SELECT pvMin_action, pvMax_action, contenance, action.nb_points as niveau_bat
											FROM action, action_as_batiment
											WHERE action.id_action = action_as_batiment.id_action
											AND id_batiment = '$id_bat'";
									$res = $mysqli->query($sql);
									$t_b = $res->fetch_assoc();
									
									$pvMin 			= $t_b['pvMin_action'];
									$pvMax 			= $t_b['pvMax_action'];
									$niveau_bat 	= $t_b["niveau_bat"];
									$contenance_bat = $t_b["contenance"];
									
									
									$pv_bat = rand($pvMin, $pvMin * 2);
									$img_bat = "b".$id_bat."".$bat_camp.".png";
									
									if ($id_bat == 4){
										// route
										// mise a jour de la carte
										$sql = "UPDATE carte SET occupee_carte='0', fond_carte='$img_bat' WHERE x_carte=$x_bat AND y_carte=$y_bat";
										$mysqli->query($sql);							
									}
									else {
										
										// mise a jour de la table instance_bat
										$sql = "INSERT INTO instance_batiment (niveau_instance, id_batiment, nom_instance, pv_instance, pvMax_instance, x_instance, y_instance, camp_instance, camp_origine_instance, contenance_instance) 
												VALUES ('$niveau_bat', '$id_bat', '$nom_instance', '$pv_bat', '$pvMax', '$x_bat', '$y_bat', '$camp_bat', '$camp_bat', '$contenance_bat')";
										$mysqli->query($sql);
										$id_i_bat = $mysqli->insert_id;
										
										// Cas particulier Ponts
										if ($id_bat == 5) {
											
											// mise a jour de la carte
											$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte='$id_i_bat', save_info_carte='$id_i_bat', fond_carte='$img_bat' WHERE x_carte='$x_bat' AND y_carte='$y_bat'";
											$mysqli->query($sql);
											
										} else {
										
											$img_bat_sup = $bat_camp.".png";
											
											for ($x = $x_bat - $taille_search; $x <= $x_bat + $taille_search; $x++) {
												for ($y = $y_bat - $taille_search; $y <= $y_bat + $taille_search; $y++) {
													
													// mise a jour de la carte
													$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte='$id_i_bat', image_carte='$img_bat_sup' WHERE x_carte='$x' AND y_carte='$y'";
													$mysqli->query($sql);
													
												}
											}
										
											// mise a jour de la carte image centrale
											$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte='$id_i_bat', image_carte='$img_bat' WHERE x_carte='$x_bat' AND y_carte='$y_bat'";
											$mysqli->query($sql);
											
											if ($id_bat == '8') {
												// CANONS FORTIN
												
												if ($camp_bat == 1) {
													$image_canon_g = 'canonG_nord.gif';
													$image_canon_d = 'canonD_nord.gif';
												}
												
												if ($camp_bat == 2) {
													$image_canon_g = 'canonG_sud.gif';
													$image_canon_d = 'canonD_sud.gif';
												}
												
												// Canons Gauche
												$sql = "UPDATE carte SET image_carte='$image_canon_g' WHERE (x_carte=$x_bat - 1 AND y_carte=$y_bat - 1) OR (x_carte=$x_bat - 1 AND y_carte=$y_bat + 1)";
												$mysqli->query($sql);
												
												// Canons Droit
												$sql = "UPDATE carte SET image_carte='$image_canon_d' WHERE (x_carte=$x_bat + 1 AND y_carte=$y_bat - 1) OR (x_carte=$x_bat + 1 AND y_carte=$y_bat + 1)";
												$mysqli->query($sql);
												
												$sql = "INSERT INTO instance_batiment_canon (id_instance_bat, x_canon, y_canon, camp_canon) VALUES ('$id_i_bat', $x_bat - 1, $y_bat - 1, $camp_bat)";
												$mysqli->query($sql);
												$sql = "INSERT INTO instance_batiment_canon (id_instance_bat, x_canon, y_canon, camp_canon) VALUES ('$id_i_bat', $x_bat - 1, $y_bat + 1, $camp_bat)";
												$mysqli->query($sql);
												$sql = "INSERT INTO instance_batiment_canon (id_instance_bat, x_canon, y_canon, camp_canon) VALUES ('$id_i_bat', $x_bat + 1, $y_bat - 1, $camp_bat)";
												$mysqli->query($sql);
												$sql = "INSERT INTO instance_batiment_canon (id_instance_bat, x_canon, y_canon, camp_canon) VALUES ('$id_i_bat', $x_bat + 1, $y_bat + 1, $camp_bat)";
												$mysqli->query($sql);
											}
											else if ($id_bat == '9') {
												// CANONS FORT
												
												if ($camp_bat == 1) {
													$image_canon_g = 'canonG_nord.gif';
													$image_canon_d = 'canonD_nord.gif';
												}
												
												if ($camp_bat == 2) {
													$image_canon_g = 'canonG_sud.gif';
													$image_canon_d = 'canonD_sud.gif';
												}
												
												// Canons Gauche
												$sql = "UPDATE carte SET image_carte='$image_canon_g' WHERE (x_carte=$x_bat - 2 AND y_carte=$y_bat + 2) OR (x_carte=$x_bat - 2 AND y_carte=$y_bat) OR (x_carte=$x_bat - 2 AND y_carte=$y_bat - 2)";
												$mysqli->query($sql);
												
												// Canons Droit
												$sql = "UPDATE carte SET image_carte='$image_canon_d' WHERE (x_carte=$x_bat + 2 AND y_carte=$y_bat + 2) OR (x_carte=$x_bat + 2 AND y_carte=$y_bat) OR (x_carte=$x_bat + 2 AND y_carte=$y_bat - 2)";
												$mysqli->query($sql);
												
												$sql = "INSERT INTO instance_batiment_canon (id_instance_bat, x_canon, y_canon, camp_canon) VALUES ('$id_i_bat', $x_bat - 2, $y_bat + 2, $camp_bat)";
												$mysqli->query($sql);
												$sql = "INSERT INTO instance_batiment_canon (id_instance_bat, x_canon, y_canon, camp_canon) VALUES ('$id_i_bat', $x_bat - 2, $y_bat, $camp_bat)";
												$mysqli->query($sql);
												$sql = "INSERT INTO instance_batiment_canon (id_instance_bat, x_canon, y_canon, camp_canon) VALUES ('$id_i_bat', $x_bat - 2, $y_bat - 2, $camp_bat)";
												$mysqli->query($sql);
												$sql = "INSERT INTO instance_batiment_canon (id_instance_bat, x_canon, y_canon, camp_canon) VALUES ('$id_i_bat', $x_bat + 2, $y_bat + 2, $camp_bat)";
												$mysqli->query($sql);
												$sql = "INSERT INTO instance_batiment_canon (id_instance_bat, x_canon, y_canon, camp_canon) VALUES ('$id_i_bat', $x_bat + 2, $y_bat, $camp_bat)";
												$mysqli->query($sql);
												$sql = "INSERT INTO instance_batiment_canon (id_instance_bat, x_canon, y_canon, camp_canon) VALUES ('$id_i_bat', $x_bat + 2, $y_bat - 2, $camp_bat)";
												$mysqli->query($sql);
											}
											else if ($id_bat == '11') {
												// Gare 
												
												// Est ce que la gare est connectée à des rails ?
												$sql = "SELECT x_carte, y_carte, occupee_carte, idPerso_carte, image_carte FROM carte 
														WHERE x_carte >= $x_bat -2 AND x_carte <= $x_bat + 2 
														AND y_carte >= $y_bat - 2 AND y_carte <= $y_bat + 2 
														AND (fond_carte='rail.gif' OR fond_carte='rail_1.gif' OR fond_carte='rail_2.gif' OR fond_carte='rail_3.gif' OR fond_carte='rail_4.gif' OR fond_carte='rail_5.gif' OR fond_carte='railP.gif')";
												$res = $mysqli->query($sql);
												$nb_connections = $res->num_rows;
												
												if ($nb_connections > 0) {
												
													$tab_rail = array();
													
													$trouve = false;
												
													while ($t = $res->fetch_assoc()) {
														
														$x_rail 		= $t["x_carte"];
														$y_rail 		= $t["y_carte"];
														$occ_rail		= $t["occupee_carte"];
														$idPerso_rail	= $t["idPerso_carte"];
														$image_on_rail	= $t["image_carte"];
														
														// Coordonnées rail
														$coord_rail = $x_rail.";".$y_rail;
														array_push($tab_rail, $coord_rail);
														
														if (($camp_bat == 1 && $image_on_rail == 'b12b.png') || ($camp_bat == 2 && $image_on_rail == 'b12r.png')) {
															
															// On a trouvé un train du même camp que la gare construite
															$trouve = true;
															
															$sql_t = "SELECT id_gare1, id_gare2, direction FROM liaisons_gare WHERE id_train='$idPerso_rail'";
															$res_t = $mysqli->query($sql_t);
															$t_t = $res_t->fetch_assoc();
															
															$id_gare1 	= $t_t['id_gare1'];
															$id_gare2 	= $t_t['id_gare2'];
															$direction 	= $t_t['direction'];
															
															// Est-ce que la gare 1 existe toujours ?
															$sql_e1 = "SELECT * FROM instance_batiment WHERE id_instanceBat = '$id_gare1'";
															$res_e1 = $mysqli->query($sql_e1);
															$existe_gare1 = $res_e1->num_rows;
															
															if (!$existe_gare1) {
																if ($direction == $id_gare1) {
																	// On met à jour gare1 ET direction
																	$sql = "UPDATE liaisons_gare SET id_gare1='$id_i_bat', direction='$id_i_bat' WHERE id_train='$idPerso_rail'";
																	$mysqli->query($sql);
																}
																else {
																	// On met à jour gare1
																	$sql = "UPDATE liaisons_gare SET id_gare1='$id_i_bat' WHERE id_train='$idPerso_rail'";
																	$mysqli->query($sql);
																}
															}
															else {
																if ($direction == $id_gare2) {
																	// On met à jour gare2 ET direction
																	$sql = "UPDATE liaisons_gare SET id_gare2='$id_i_bat', direction='$id_i_bat' WHERE id_train='$idPerso_rail'";
																	$mysqli->query($sql);
																}
																else {
																	// On met à jour gare2
																	$sql = "UPDATE liaisons_gare SET id_gare2='$id_i_bat' WHERE id_train='$idPerso_rail'";
																	$mysqli->query($sql);
																}
															}
														}
														else {												
															
															$num_res = 1;
															
															while ($image_on_rail != 'b12b.png' && $image_on_rail != 'b12r.png' && $num_res > 0) {
																
																// On cherche un train sur le chemin des rails
																$sql_r = "SELECT x_carte, y_carte, occupee_carte, idPerso_carte, image_carte FROM carte 
																		WHERE x_carte >= $x_rail - 1 AND x_carte <= $x_rail + 1 AND y_carte >= $y_rail - 1 AND y_carte <= $y_rail + 1
																		AND coordonnees NOT IN ( '" . implode( "', '" , $tab_rail ) . "' )
																		AND (fond_carte='rail.gif' OR fond_carte='rail_1.gif' OR fond_carte='rail_2.gif' OR fond_carte='rail_3.gif' OR fond_carte='rail_4.gif' OR fond_carte='rail_5.gif' OR fond_carte='railP.gif')";
																$res_r = $mysqli->query($sql_r);
																$num_res = $res_r->num_rows;
																
																$t_r = $res_r->fetch_assoc();
															
																$x_rail 		= $t_r['x_carte'];
																$y_rail 		= $t_r['y_carte'];
																$occ_rail		= $t_r["occupee_carte"];
																$idPerso_rail	= $t_r["idPerso_carte"];
																$image_on_rail	= $t_r["image_carte"];
																
																// Ajout coordonnées dans tableau des coordonnées des rails
																$coord_rail = $x_rail.";".$y_rail;
																array_push($tab_rail, $coord_rail);												
															}
															
															if (($camp_bat == 1 && $image_on_rail == 'b12b.png') || ($camp_bat == 2 && $image_on_rail == 'b12r.png')) {
															
																// On a trouvé un train du même camp que la gare construite
																$trouve = true;
																
																$sql_t = "SELECT id_gare1, id_gare2, direction FROM liaisons_gare WHERE id_train='$idPerso_rail'";
																$res_t = $mysqli->query($sql_t);
																$t_t = $res_t->fetch_assoc();
																
																$id_gare1 	= $t_t['id_gare1'];
																$id_gare2 	= $t_t['id_gare2'];
																$direction 	= $t_t['direction'];
																
																// Est-ce que la gare 1 existe toujours ?
																$sql_e1 = "SELECT * FROM instance_batiment WHERE id_instanceBat = '$id_gare1'";
																$res_e1 = $mysqli->query($sql_e1);
																$existe_gare1 = $res_e1->num_rows;
																
																if (!$existe_gare1) {
																	if ($direction == $id_gare1) {
																		// On met à jour gare1 ET direction
																		$sql = "UPDATE liaisons_gare SET id_gare1='$id_i_bat', direction='$id_i_bat' WHERE id_train='$idPerso_rail'";
																		$mysqli->query($sql);
																	}
																	else {
																		// On met à jour gare1
																		$sql = "UPDATE liaisons_gare SET id_gare1='$id_i_bat' WHERE id_train='$idPerso_rail'";
																		$mysqli->query($sql);
																	}
																}
																else {
																	if ($direction == $id_gare2) {
																		// On met à jour gare2 ET direction
																		$sql = "UPDATE liaisons_gare SET id_gare2='$id_i_bat', direction='$id_i_bat' WHERE id_train='$idPerso_rail'";
																		$mysqli->query($sql);
																	}
																	else {
																		// On met à jour gare2
																		$sql = "UPDATE liaisons_gare SET id_gare2='$id_i_bat' WHERE id_train='$idPerso_rail'";
																		$mysqli->query($sql);
																	}
																}
															}
														}
													}
													
													if (!$trouve) {
														
														// Est ce que la gare est connectée à des rails ?
														$sql = "SELECT x_carte, y_carte, occupee_carte, idPerso_carte, image_carte FROM carte 
																WHERE x_carte >= $x_bat - 2 AND x_carte <= $x_bat + 2 AND y_carte >= $y_bat - 2 AND y_carte <= $y_bat + 2 
																AND (fond_carte='rail.gif' OR fond_carte='rail_1.gif' OR fond_carte='rail_2.gif' OR fond_carte='rail_3.gif' OR fond_carte='rail_4.gif' OR fond_carte='rail_5.gif' OR fond_carte='railP.gif')";
														$res = $mysqli->query($sql);
														$nb_connections = $res->num_rows;
														
														if ($nb_connections > 0) {
															
															while ($t = $res->fetch_assoc()) {
														
																$x_rail 		= $t["x_carte"];
																$y_rail 		= $t["y_carte"];
														
																// On n'a pas trouvé de train sur les rails
																// Est ce qu'on trouve une gare liée par les rails à cette nouvelle gare ?																		
																$num_res = 1;
																
																$tab_rail2 = array();
																
																while ($image_on_rail != 'b.png' && $image_on_rail != 'r.png' && $num_res > 0) {
																		
																	// On cherche un train sur le chemin des rails
																	$sql_r = "SELECT x_carte, y_carte, occupee_carte, idPerso_carte, image_carte FROM carte 
																			WHERE x_carte >= $x_rail - 1 AND x_carte <= $x_rail + 1 AND y_carte >= $y_rail - 1 AND y_carte <= $y_rail + 1
																			AND coordonnees NOT IN ( '" . implode( "', '" , $tab_rail2 ) . "' )
																			AND (fond_carte='rail.gif' OR fond_carte='rail_1.gif' OR fond_carte='rail_2.gif' OR fond_carte='rail_3.gif' OR fond_carte='rail_4.gif' OR fond_carte='rail_5.gif' OR fond_carte='railP.gif' OR image_carte='r.png' OR image_carte='b.png')
																			AND (idPerso_carte != '$id_i_bat' OR idPerso_carte IS NULL)";
																	$res_r = $mysqli->query($sql_r);
																	$num_res = $res_r->num_rows;
																	
																	$t_r = $res_r->fetch_assoc();
																
																	$x_rail 		= $t_r['x_carte'];
																	$y_rail 		= $t_r['y_carte'];
																	$occ_rail		= $t_r["occupee_carte"];
																	$idPerso_rail	= $t_r["idPerso_carte"];
																	$image_on_rail	= $t_r["image_carte"];
																	
																	// Ajout coordonnées dans tableau des coordonnées des rails
																	$coord_rail = $x_rail.";".$y_rail;
																	array_push($tab_rail2, $coord_rail);
																}
																
																if (($camp_bat == 1 && $image_on_rail == 'b.png') || ($camp_bat == 2 && $image_on_rail == 'r.png')) {
																	
																	// On a trouvé un batiment du même camp que la gare construite
																	$trouve = true;
																	
																	// Récupération des infos du bâtiment rencontré
																	$sql_b = "SELECT * FROM instance_batiment WHERE id_instanceBat='$idPerso_rail'";
																	$res_b = $mysqli->query($sql_b);
																	$t_b = $res_b->fetch_assoc();
																	
																	$id_bat_instance	= $t_b['id_batiment'];
																	$camp_instance		= $t_b['camp_instance'];
																	
																	// La batiment rencontré est bien une gare du même camp que la gare construite
																	if ($id_bat_instance == '11' && $camp_instance == $camp_bat) {
																		
																		// Création de la liaison
																		$sql = "INSERT INTO liaisons_gare (id_gare1, id_gare2, id_train, direction) VALUES ('$id_i_bat', '$idPerso_rail', NULL, '$idPerso_rail')";
																		$mysqli->query($sql);
																		
																	}
																}
															}
														}
													}
												}
											}
										}
									}
									
									// recuperation des infos du perso
									$sql = "SELECT nom_perso, clan FROM perso WHERE id_perso='$id_perso'";
									$res = $mysqli->query($sql);
									$t_p = $res->fetch_assoc();
									$nom_perso = $t_p["nom_perso"];
									$camp = $t_p["clan"];
									
									// recuperation de la couleur du camp du perso
									$couleur_clan_perso = couleur_clan($camp);
									
									
									//mise a jour de la table evenement
									$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) 
											VALUES ($id_i_bat,'<font color=$couleur_clan_bat><b>$nom_bat</b></font>','a été construit par un animateur',NULL,'','',NOW(),'0')";
									$mysqli->query($sql);
									
									$texte = "Construction du batiment $nom_bat [$id_i_bat] - verification contraintes : $verif";
									
									// log_action_animation
									$sql = "INSERT INTO log_action_animation(date_acces, id_perso, page, action, texte) VALUES (NOW(), '$id_perso', 'anim_creer_batiment.php', 'creation batiment', '$texte')";
									$mysqli->query($sql);
								}
								else {
									echo "<center>Vous ne pouvez pas construire ce bâtiment car la carte est occupée ou le terrain n'est pas que de la plaine<br />";
									echo "<a href='anim_creer_batiment.php' class='btn btn-primary'>retour</a></center>";
								}
							}
							else {
								echo "<center>Vous ne pouvez pas construire ce bâtiment car la contrainte sur la distance avec un autre batiment n'a pas été respecté<br />";
								echo "<a href='contraintes_construction.php' target='_blank'>Voir page des contraintes de construction</a><br />";
								echo "<a href='anim_creer_batiment.php' class='btn btn-primary'>retour</a></center>";
							}
						}
						else {
							echo "<center>Vous ne pouvez pas construire ce bâtiment car la contrainte du nombre d'ennemis présent autour de la zone de construction n'a pas été respecté. Veuillez nettoyer la zone !<br />";
							echo "<a href='contraintes_construction.php' target='_blank'>Voir page des contraintes de construction</a><br />";
							echo "<a href='anim_creer_batiment.php' class='btn btn-primary'>retour</a></center>";
						}
					}
					else {
						echo "<center>Vous ne pouvez pas construire ce bâtiment car la contrainte du nombre d'unités de Génie Civil qui doit être présente n'a pas été respecté<br />";
						echo "<a href='contraintes_construction.php' target='_blank'>Voir page des contraintes de construction</a><br />";
						echo "<a href='anim_creer_batiment.php' class='btn btn-primary'>retour</a></center>";
					}
				}
				else {				
					echo "<center>Impossible de faire une construction sur cette case<br />";
					echo "<a href='anim_creer_batiment.php' class='btn btn-primary'>retour</a></center>";
				}
			}
			else {				
				echo "<center>Vous ne pouvez pas construire ce bâtiment la case cible est occupée ou hors carte<br />";
				echo "<a href='anim_creer_batiment.php' class='btn btn-primary'>retour</a></center>";
			}
			
		}
		
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
			<div class="row">
				<div class="col-12">

					<div align="center">
						<h2>Administration</h2>
						
						<center><font color='red'><?php echo $mess_err; ?></font></center>
						<center><font color='blue'><?php echo $mess; ?></font></center>
					</div>
				</div>
			</div>
		
			<p align="center">
			<?php
			if ($admin) {
				echo " <a class='btn btn-primary' href='admin_nvs.php'>Retour à l'administration</a>";
				echo " <a class='btn btn-primary' href='admin_batiments.php'>Retour à la gestion des batiments</a>";
			}
			else {
				echo " <a class='btn btn-primary' href='animation.php'>Retour à l'animation</a>";
				echo " <a class='btn btn-primary' href='anim_batiment.php'>Retour à la gestion des batiments</a>";
			}
			?>
				<a class="btn btn-primary" href="jouer.php">Retour au jeu</a>
			</p>
			 
		
			<div class="row">
				<div class="col-12">
				
					<h3>Création de batiments</h3>
					
					<form method='POST' action='anim_creer_batiment.php'>
						<select name="select_batiment">
							<option value='1'>Barricade</option>
							<option value='2'>Tour de visu</option>
							<option value='7'>Hopital</option>
							<option value='11'>Gare</option>
							<option value='8'>Fortin</option>
							<option value='9'>Fort</option>
						</select>
						<select name="select_verifications">
							<option value='1'>Aucune verification des contraintes</option>
							<option value='2'>Vérifier les contraintes</option>
						</select>
						<select name="select_camp">
							<option value='1'>Nord</option>
							<option value='2'>Sud</option>
						</select>
						<input type='text' value='' name='coord_x_placement' placeholder='x'>
						<input type='text' value='' name='coord_y_placement' placeholder='y'>
						<input type='submit' class='btn btn-success' value='créer'>
					</form>
					
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
}
else{
	echo "<font color=red>Vous ne pouvez pas acceder a cette page, veuillez vous logguer.</font>";
}?>