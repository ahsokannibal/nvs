<?php
session_start();
require_once("../fonctions.php");
require_once("f_carte.php");
require_once("f_combat.php");

$mysqli = db_connexion();

include ('../nb_online.php');

$id = $_SESSION["id_perso"];
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Nord VS Sud</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../style.css" rel="stylesheet" type="text/css">
</head>

<body>

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

if (isset($_POST["id_attaque_dist"]) && $_POST["id_attaque_dist"] != "personne") {
	
	$t_attaque_dist 	= explode(",", $_POST["id_attaque_dist"]);
	$id_attaque 		= $t_attaque_dist[0];
	$id_arme_attaque 	= $t_attaque_dist[1];
	
	$verif = preg_match("#^[0-9]*[0-9]$#i","$id_attaque");
}

if($verif){
	
	//traitement de l'attaque sur un perso
	if ((isset($id_attaque) && $id_attaque != "" && $id_attaque != "personne" && $id_attaque < 50000)) {
	
		if(!in_bat($mysqli, $id)){
	
			$id_cible = $id_attaque;			
			
			// arme bien passée
			if(isset($id_arme_attaque) && $id_arme_attaque != 1000 && $id_arme_attaque != 2000) {
				
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
					
				} else {
					
					// On ne devrait pas arriver ici
					// Par défaut on va mettre les poings
					$nom_arme_attaque 				= "Poings";
					$coutPa_arme_attaque 			= 3;
					$porteeMin_arme_attaque 		= 1;
					$porteeMax_arme_attaque 		= 1;
					$valeur_des_arme_attaque		= 6;
					$degatMin_arme_attaque 			= 4;
					$degatMax_arme_attaque 			= 4;
					$precision_arme_attaque			= 30;
					$degatZone_arme_attaque 		= 0;
					
				}
			}
			
			// recup des données du perso
			$sql = "SELECT nom_perso, idJoueur_perso, image_perso, xp_perso, x_perso, y_perso, pm_perso, pi_perso, pv_perso, pvMax_perso, pmMax_perso, pa_perso, paMax_perso, recup_perso, bonusRecup_perso, perception_perso, bonusPerception_perso, charge_perso, chargeMax_perso, dateCreation_perso, clan, id_grade
					FROM perso, perso_as_grade
					WHERE perso_as_grade.id_perso = perso.id_perso
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
			$per_perso 		= $t_perso["perception_perso"];
			$bp_perso 		= $t_perso["bonusPerception_perso"];
			$ch_perso 		= $t_perso["charge_perso"];
			$chM_perso 		= $t_perso["chargeMax_perso"];
			$dc_perso 		= $t_perso["dateCreation_perso"];
			$id_j_perso		= $t_perso["idJoueur_perso"];
			$clan_perso 	= $t_perso["clan"];
			$grade_perso 	= $t_perso["id_grade"];
			
			// Récupération de la couleur associée au clan du perso
			$couleur_clan_perso = couleur_clan($clan_perso);
			
			// verification si le perso est bien a portée d'attaque			
			if(is_a_portee_attaque($mysqli, $carte, $id, $id_cible, $porteeMin_arme_attaque, $porteeMax_arme_attaque, $per_perso)) {
				
				// recuperation des données du perso cible
				$sql = "SELECT idJoueur_perso, nom_perso, type_perso, image_perso, xp_perso, x_perso, y_perso, pm_perso, pi_perso, pv_perso, pvMax_perso, pmMax_perso, pa_perso, paMax_perso, recup_perso, bonusRecup_perso, bonus_perso, perception_perso, protec_perso, bonusPerception_perso, charge_perso, chargeMax_perso, dateCreation_perso, or_perso, clan, id_grade
						FROM perso, perso_as_grade 
						WHERE perso_as_grade.id_perso = perso.id_perso
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
				$bonus_cible 		= $t_cible["bonus_perso"];
				$per_cible 			= $t_cible["perception_perso"];
				$bp_cible 			= $t_cible["bonusPerception_perso"];
				$ch_cible 			= $t_cible["charge_perso"];
				$chM_cible 			= $t_cible["chargeMax_perso"];
				$dc_cible 			= $t_cible["dateCreation_perso"];
				$or_cible 			= $t_cible["or_perso"];
				$image_perso_cible 	= $t_cible["image_perso"];
				$clan_cible 		= $t_cible["clan"];
				$grade_cible		= $t_cible['id_grade'];
				
				// Récupération de la couleur associée au clan de la cible
				$couleur_clan_cible = couleur_clan($clan_cible);
				
				$pa_restant = $pa_perso - $coutPa_arme_attaque;
				
				if($pa_restant <= 0){
					$pa_restant = 0;
				}				
									
				// le perso n'a pas assez de pa pour faire cette attaque
				if ($pa_perso < $coutPa_arme_attaque) {
					echo "<div class=\"erreur\" align=\"center\">Vous n'avez pas assez de pa pour effectuer cette action !</div>";
					echo "<a href=\"jouer.php\"><font color=\"#000000\" size=\"1\" face=\"Verdana, Arial, Helvetica, sans-serif\">[ retour ]</font></a>";
				}	
				else { 
					// le perso a assez de pa
					// la cible est encore en vie
					if ($pv_cible > 0) {
						
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
														<td><?php echo "<u><b>Mouvements restants :</b></u> ".$pm_perso."/".$pmM_perso; ?><?php echo " - <u><b>Points de vie :</b></u> ".$pv_perso."/".$pvM_perso; ?></td>
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
														<td><?php echo"<u><b>Camp :</b></u> ".$couleur_clan_cible; ?></td>
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
						
						// Vérifie si le joueur attaqué a coché l'envoi de mail
						$mail_info_joueur = verif_coche_mail($mysqli, $id_joueur_cible);
						
						if($mail_info_joueur){
							// Envoi du mail
							mail_attaque($mysqli, $nom_perso, $id_cible);
						}
						
						// Si perso ou cible est une infanterie 
						// ou si grade perso >= grade cible - 1
						if ($grade_perso <= $grade_cible + 1 
								|| $grade_perso == 1 || $grade_perso == 101 || $grade_perso == 102 
								|| $grade_cible == 1 || $grade_cible == 101 || $grade_cible == 102) {
							
							$gain_pc = 1;
						} else {
							$gain_pc = 0;
						}
						
						// Seringue ou bandage
						if ($id_arme_attaque == 10 || $id_arme_attaque == 11) {
							echo "Vous avez lancé un soin sur <b>$nom_cible [$id_cible]</b> avec $nom_arme_attaque<br/>";
						} else {
							echo "Vous avez lancé une attaque sur <b>$nom_cible [$id_cible]</b> avec $nom_arme_attaque<br/>";
						}						
						
						// Calcul touche
						$touche = mt_rand(0,100);
						$precision_final = $precision_arme_attaque - $bonus_cible;
						
						echo "Votre score de touche : ".$touche."<br>";
						
						// Score touche <= precision arme utilisée - bonus cible pour l'attaque = La cible est touchée
						if ($touche <= $precision_final) {
			
							// calcul degats arme
							$degats_final = mt_rand($degatMin_arme_attaque, $degatMin_arme_attaque * $valeur_des_arme_attaque) - $protec_cible;
							
							// Canon d'artillerie et cible autre artillerie
							if ($id_arme_attaque == 13 && $type_perso_cible == 5) {
								// Bonus dégats 13D10
								$bonus_degats_canon = mt_rand(13, 130);
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
							
							if ($calcul_dif_xp < 0) {
								$valeur_des_xp = 0;
							} else {
								$valeur_des_xp = mt_rand(0, $calcul_dif_xp);
							}
							
							$gain_xp = ceil(($degats_final / 20) + $valeur_des_xp);
							
							if ($gain_xp > 10) {
								$gain_xp = 10;
							}
							
							if ($id_arme_attaque == 10) {
								
								// Seringue
								if ($pv_cible + $degats_final >= $pvM_cible) {
									$degats_final = $pvM_cible - $pv_cible;
								}
								
								// mise a jour des pv
								$sql = "UPDATE perso SET pv_perso=pv_perso+$degats_final WHERE id_perso='$id_cible'";
								$mysqli->query($sql);
								
								echo "<br>Vous avez soigné $degats_final dégâts à la cible.<br><br>";
								
							} else if ($id_arme_attaque == 11) {
								
								// Bandage
								if ($bonus_perso + $degats_final > 0) {
									$sql = "UPDATE perso SET bonus_perso=0 WHERE id_perso='$id_cible'";
									echo "<br>Vous avez soigné tous les malus de la cible.<br><br>";
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
							
							echo "Vous avez gagné $gain_xp xp.<br>";
							
							// mise a jour des xp/pi
							$sql = "UPDATE perso SET xp_perso=xp_perso+$gain_xp, pi_perso=pi_perso+$gain_xp WHERE id_perso='$id'"; 
							$mysqli->query($sql);
							
							// Passage grade grouillot
							if ($grade_perso == 1) {
								
								if ($xp_perso + $gain_xp >= 500) {
									// On le passage 1ere classe
									$sql = "UPDATE perso_as_grade SET id_grade='101' WHERE id_perso='$id'";
									$mysqli->query($sql);
									
									echo "<br /><b>Vous êtes passé au grade de Grouillot 1ere classe</b><br />";
								}
							}
							
							if ($grade_perso == 101) {
								
								if ($xp_perso + $gain_xp >= 1500) {
									// On le passe élite
									$sql = "UPDATE perso_as_grade SET id_grade='102' WHERE id_perso='$id'";
									$mysqli->query($sql);
									
									echo "<br /><b>Vous êtes passé au grade de Grouillot d'élite</b><br />";
								}
							}
							
							// mise à jour des PC du chef
							$sql = "SELECT perso.id_perso, pc_perso, id_grade FROM perso, perso_as_grade WHERE perso.id_perso = perso_as_grade.id_perso AND idJoueur_perso='$id_j_perso' AND chef='1'";
							$res = $mysqli->query($sql);
							$t_chef = $res->fetch_assoc();
							
							$id_perso_chef = $t_chef["id_perso"];
							$pc_perso_chef = $t_chef["pc_perso"];
							$id_grade_chef = $t_chef["id_grade"];
							
							$sql = "UPDATE perso SET pc_perso = pc_perso + $gain_pc WHERE id_perso='$id_perso_chef'";
							$mysqli->query($sql);
							
							$pc_perso_chef_final = $pc_perso_chef + $gain_pc;
							
							// Verification passage de grade 
							$sql = "SELECT id_grade, nom_grade FROM grades WHERE pc_grade <= $pc_perso_chef_final AND pc_grade != 0 ORDER BY id_grade DESC LIMIT 1";
							$res = $mysqli->query($sql);
							$t_grade = $res->fetch_assoc();
							
							$id_grade_final 	= $t_grade["id_grade"];
							$nom_grade_final	= $t_grade["nom_grade"];
							
							if ($id_grade_chef < $id_grade_final) {
								
								// Passage de grade								
								$sql = "UPDATE perso_as_grade SET id_grade='$id_grade_final' WHERE id_perso='$id_perso_chef'";
								$mysqli->query($sql);
								
								echo "<br /><b>Votre chef de bataillon est passé au grade de $nom_grade_final</b><br />";
								
							}
							
							if ($id_arme_attaque == 10 || $id_arme_attaque == 11) {
								
								// mise a jour de la table evenement
								$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>','a soigné ','$id_cible','<font color=$couleur_clan_cible><b>$nom_cible</b></font>',': $degats_final soins',NOW(),'0')";
								$mysqli->query($sql);
								
							} else {
							
								// mise a jour de la table evenement
								$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>','a attaqué ','$id_cible','<font color=$couleur_clan_cible><b>$nom_cible</b></font>',': $degats_final degats',NOW(),'0')";
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
								
								// On parcours les cibles pour degats collateraux
								while ($t_recherche_collat = $res_recherche_collat->fetch_assoc()) {
									
									$id_cible_collat = $t_recherche_collat["idPerso_carte"];
									
									if ($id_cible_collat < 50000) {
										
										// Perso
										// Récupération des infos du perso
										$sql = "SELECT idJoueur_perso, nom_perso, image_perso, xp_perso, x_perso, y_perso, pv_perso, pvMax_perso, or_perso, clan, id_grade
												FROM perso, perso_as_grade 
												WHERE perso_as_grade.id_perso = perso.id_perso
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
										
										// Récupération de la couleur associée au clan de la cible
										$couleur_clan_collat = couleur_clan($clan_collat);
										
										$gain_xp_collat_cumul += 1;
										
										$gain_pc_collat = 1;
										$gain_xp_collat = 1;
										
										// mise a jour des pv et des malus de la cible
										$sql = "UPDATE perso SET pv_perso=pv_perso-$degats_collat, bonus_perso=bonus_perso-2 WHERE id_perso='$id_cible_collat'";
										$mysqli->query($sql);
										
										echo "<br>Vous avez infligé $degats_collat dégâts collatéraux à <font color='$couleur_clan_collat'>$nom_collat</font> - Matricule $id_cible_collat.<br>";
										
										if ($gain_xp_collat_cumul <= 4) {
											echo "Vous avez gagné $gain_xp_collat xp.<br><br>";
										
											// mise a jour des xp/pi
											$sql = "UPDATE perso SET xp_perso=xp_perso+$gain_xp_collat, pi_perso=pi_perso+$gain_xp_collat WHERE id_perso='$id'"; 
											$mysqli->query($sql);
											
											// Passage grade grouillot
											if ($grade_perso == 1) {
												
												if ($xp_perso + $gain_xp_collat >= 500) {
													// On le passage 1ere classe
													$sql = "UPDATE perso_as_grade SET id_grade='101' WHERE id_perso='$id'";
													$mysqli->query($sql);
													
													echo "<br /><b>Vous êtes passé au grade de Grouillot 1ere classe</b><br />";
												}
											}
											
											if ($grade_perso == 101) {
												
												if ($xp_perso + $gain_xp_collat >= 1500) {
													// On le passe élite
													$sql = "UPDATE perso_as_grade SET id_grade='102' WHERE id_perso='$id'";
													$mysqli->query($sql);
													
													echo "<br /><b>Vous êtes passé au grade de Grouillot d'élite</b><br />";
												}
											}
											
										} else {
											echo "Vous avez gagné 0 xp (maximum par attaque atteint).<br><br>";
										}
										
										// mise à jour des PC du chef
										$sql = "SELECT perso.id_perso, pc_perso, id_grade FROM perso, perso_as_grade WHERE perso.id_perso = perso_as_grade.id_perso AND idJoueur_perso='$id_j_perso' AND chef='1'";
										$res = $mysqli->query($sql);
										$t_chef = $res->fetch_assoc();
										
										$id_perso_chef = $t_chef["id_perso"];
										$pc_perso_chef = $t_chef["pc_perso"];
										$id_grade_chef = $t_chef["id_grade"];
										
										$sql = "UPDATE perso SET pc_perso = pc_perso + $gain_pc_collat WHERE id_perso='$id_perso_chef'";
										$mysqli->query($sql);
										
										$pc_perso_chef_final = $pc_perso_chef + $gain_pc_collat;
										
										// Verification passage de grade 
										$sql = "SELECT id_grade, nom_grade FROM grades WHERE pc_grade <= $pc_perso_chef_final AND pc_grade != 0 ORDER BY id_grade DESC LIMIT 1";
										$res = $mysqli->query($sql);
										$t_grade = $res->fetch_assoc();
										
										$id_grade_final 	= $t_grade["id_grade"];
										$nom_grade_final	= $t_grade["nom_grade"];
										
										if ($id_grade_chef < $id_grade_final) {
											
											// Passage de grade								
											$sql = "UPDATE perso_as_grade SET id_grade='$id_grade_final' WHERE id_perso='$id_perso_chef'";
											$mysqli->query($sql);
											
											echo "<br /><b>Votre chef de bataillon est passé au grade de $nom_grade_final</b><br />";
											
										}
										
										// mise a jour de la table evenement
										$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>','a infligé des dégâts collatéraux ','$id_cible_collat','<font color=$couleur_clan_collat><b>$nom_collat</b></font>',': $degats_collat degats',NOW(),'0')";
										$mysqli->query($sql);
										
										$sql = "SELECT pv_perso, x_perso, y_perso, xp_perso, pi_perso FROM perso WHERE id_perso='$id_cible_collat'";
										$res = $mysqli->query($sql);
										$tab = $res->fetch_assoc();
										
										$pv_collat_fin 	= $tab["pv_perso"];
										$x_collat_fin 	= $tab["x_perso"];
										$y_collat_fin 	= $tab["y_perso"];
										$xp_collat_fin 	= $tab["xp_perso"];
										$pi_collat_fin 	= $tab["pi_perso"];
											
										// il est mort
										if ($pv_collat_fin <= 0) {
										
											// on l'efface de la carte
											$sql = "UPDATE $carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_collat_fin' AND y_carte='$y_collat_fin'";
											$mysqli->query($sql);
						
											// Calcul gains (po et xp)
											$perte_po = gain_po_mort($or_collat);
											
											// TODO
											$perte_xp_collat = 0;
						
											// MAJ perte xp/po/stat cible
											$sql = "UPDATE perso SET or_perso=or_perso-$perte_po, xp_perso=xp_perso-$perte_xp_collat, pi_perso=0, nb_mort=nb_mort+1 WHERE id_perso='$id_cible_collat'";
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
						
											echo "<div class=\"infoi\">Vous avez capturé <font color='$couleur_clan_collat'>$nom_collat</font> - Matricule $id_cible_collat ! <font color=red>Félicitations.</font></div>";
											
											// maj evenements
											$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>','a capturé','$id_cible_collat','<font color=$couleur_clan_collat><b>$nom_collat</b></font>','',NOW(),'0')";
											$mysqli->query($sql);
											
											// maj cv
											$sql = "INSERT INTO `cv` (IDActeur_cv, nomActeur_cv, IDCible_cv, nomCible_cv, date_cv) VALUES ($id,'<font color=$couleur_clan_perso>$nom_perso</font>','$id_cible_collat','<font color=$couleur_clan_collat>$nom_collat</font>',NOW())";
											$mysqli->query($sql);
						
											// maj stats de la cible
											$sql = "UPDATE perso SET nb_kill=nb_kill+1 WHERE id_perso=$id";
											$mysqli->query($sql);
											
											// maj stats camp
											if($clan_collat != $clan_perso){
												$sql = "UPDATE stats_camp_kill SET nb_kill=nb_kill+1 WHERE id_camp=$clan_perso";
												$mysqli->query($sql);
											}
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
										
										// mise a jour des pv de la cible
										$sql = "UPDATE instance_pnj SET pv_i=pv_i-$degats_collat WHERE idInstance_pnj='$id_cible_collat'";
										$mysqli->query($sql);
										
										echo "<br>Vous avez infligé $degats_collat dégâts collatéraux à $nom_cible_collat<br>";
										
										if ($gain_xp_collat_cumul <= 4) {
											
											echo "Vous avez gagné $gain_xp_collat xp.<br><br>";
											
											// mise a jour des xp/pi
											$sql = "UPDATE perso SET xp_perso=xp_perso+$gain_xp_collat, pi_perso=pi_perso+$gain_xp_collat WHERE id_perso='$id'"; 
											$mysqli->query($sql);
											
											// Passage grade grouillot
											if ($grade_perso == 1) {
												
												if ($xp_perso + $gain_xp_collat >= 500) {
													// On le passage 1ere classe
													$sql = "UPDATE perso_as_grade SET id_grade='101' WHERE id_perso='$id'";
													$mysqli->query($sql);
													
													echo "<br /><b>Vous êtes passé au grade de Grouillot 1ere classe</b><br />";
												}
											}
											
											if ($grade_perso == 101) {
												
												if ($xp_perso + $gain_xp_collat >= 1500) {
													// On le passe élite
													$sql = "UPDATE perso_as_grade SET id_grade='102' WHERE id_perso='$id'";
													$mysqli->query($sql);
													
													echo "<br /><b>Vous êtes passé au grade de Grouillot d'élite</b><br />";
												}
											}
										} else {
											echo "Vous avez gagné 0 xp (maximum par attaque atteint).<br><br>";
										}
										
										// maj evenement
										$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>','a infligé des dégâts collatéraux ','$id_cible_collat','<b>$nom_cible_collat</b>',': $degats_collat degats',NOW(),'0')";
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
											$sql = "INSERT INTO `cv` (IDActeur_cv, nomActeur_cv, IDCible_cv, nomCible_cv, date_cv) VALUES ($id,'<font color=$couleur_clan_perso>$nom_perso</font>','$id_cible_collat','$nom_cible_collat',NOW())";
											$mysqli->query($sql);
											
											echo "<br><center><a href=\"jouer.php\"><font color=\"#000000\" size=\"1\" face=\"Verdana, Arial, Helvetica, sans-serif\">[ retour ]</font></a></center>";
										}
									} else {
										// Batiment => pas de collat sur batiment
									}
								}
							}
							
							
							$sql = "SELECT pv_perso, x_perso, y_perso, xp_perso, pi_perso FROM perso WHERE id_perso='$id_cible'";
							$res = $mysqli->query($sql);
							$tab = $res->fetch_assoc();
							
							$pv_cible 	= $tab["pv_perso"];
							$x_cible 	= $tab["x_perso"];
							$y_cible 	= $tab["y_perso"];
							$xp_cible 	= $tab["xp_perso"];
							$pi_cible 	= $tab["pi_perso"];
								
							// il est mort
							if ($pv_cible <= 0) {
							
								// on l'efface de la carte
								$sql = "UPDATE $carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_cible' AND y_carte='$y_cible'";
								$mysqli->query($sql);
			
								// Calcul gains (po et xp)
								$perte_po = gain_po_mort($or_cible);
								
								// TODO
								$perte_xp_cible = 0;
			
								// MAJ perte xp/po/stat cible
								$sql = "UPDATE perso SET or_perso=or_perso-$perte_po, xp_perso=xp_perso-$perte_xp_cible, pi_perso=0, nb_mort=nb_mort+1 WHERE id_perso='$id_cible'";
								$mysqli->query($sql);
			
								echo "<div class=\"infoi\">Vous avez capturé votre cible ! <font color=red>Félicitations.</font></div>";
								
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
								
								// maj evenements
								$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>','a capturé','$id_cible','<font color=$couleur_clan_cible><b>$nom_cible</b></font>','',NOW(),'0')";
								$mysqli->query($sql);
								
								// maj cv
								$sql = "INSERT INTO `cv` (IDActeur_cv, nomActeur_cv, IDCible_cv, nomCible_cv, date_cv) VALUES ($id,'<font color=$couleur_clan_perso>$nom_perso</font>','$id_cible','<font color=$couleur_clan_cible>$nom_cible</font>',NOW())";
								$mysqli->query($sql);
			
								// maj stats de la cible
								$sql = "UPDATE perso SET nb_kill=nb_kill+1 WHERE id_perso=$id";
								$mysqli->query($sql);
								
								// maj stats camp
								if($clan_cible != $clan_perso){
									$sql = "UPDATE stats_camp_kill SET nb_kill=nb_kill+1 WHERE id_camp=$clan_perso";
									$mysqli->query($sql);
								}
							}
							
						}
						else { // la cible a esquivé l'attaque
			
							echo "<br>Vous avez raté votre cible.<br><br>";
							
							$gain_xp = 1;
							
							// gain xp esquive et ajout malus
							$sql = "UPDATE perso SET xp_perso=xp_perso+1, pi_perso=pi_perso+1, bonus_perso=bonus_perso-1 WHERE id_perso='$id_cible'";
							$mysqli->query($sql);
								
							// maj evenement
							$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_cible,'<font color=$couleur_clan_cible><b>$nom_cible</b></font>','a esquivé l\'attaque de','$id','<font color=$couleur_clan_perso><b>$nom_perso</b></font>','',NOW(),'0')";
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
						
						if ($pv_cible > 0) {
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
							<center><a href="jouer.php"><font color="#000000" size="1" face="Verdana, Arial, Helvetica, sans-serif">[ retour ]</font></a></center>
						<?php
					}			
					else {//la cible est déjà morte
						echo "Erreur : La cible est déjà morte !";
						echo "<br><center><a href=\"jouer.php\"><font color=\"#000000\" size=\"1\" face=\"Verdana, Arial, Helvetica, sans-serif\">[ retour ]</font></a></center>";
					}
				}
			}
			else { 
				if($id_cible == $id){
					echo "Erreur : Vous ne pouvez pas vous attaquez vous même...";
					echo "<br /><center><a href=\"jouer.php\"><font color=\"#000000\" size=\"1\" face=\"Verdana, Arial, Helvetica, sans-serif\">[ retour ]</font></a></center>";
				}
				else {
					// la cible n'est pas à portée d'attaque
					echo "Erreur : La cible n'est pas à portée d'attaque (Vérifiez la portée de votre arme) ou votre état ne vous permet pas de la cibler (pas assez de perception)  !";
					echo "<br /><center><a href=\"jouer.php\"><font color=\"#000000\" size=\"1\" face=\"Verdana, Arial, Helvetica, sans-serif\">[ retour ]</font></a></center>";
				}
			}
		}
		else {
			echo "Erreur : Il est impossible d'attaquer un perso depuis l'intérieur d'un batiment!";
			echo "<br /><center><a href=\"jouer.php\"><font color=\"#000000\" size=\"1\" face=\"Verdana, Arial, Helvetica, sans-serif\">[ retour ]</font></a></center>";
		}
	}
	
	//traitement de l'attaque sur un pnj
	if ((isset($id_attaque) && $id_attaque != "" && $id_attaque != "personne" && $id_attaque >= 200000) ) {
	
		$id_cible =  $id_attaque;
		
		// arme bien passée
		if(isset($id_arme_attaque) && $id_arme_attaque != 1000 && $id_arme_attaque != 2000) {
			
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
				
			} else {
				
				// On ne devrait pas arriver ici
				// Par défaut on va mettre les poings
				$nom_arme_attaque 				= "Poings";
				$coutPa_arme_attaque 			= 3;
				$porteeMin_arme_attaque 		= 1;
				$porteeMax_arme_attaque 		= 1;
				$valeur_des_arme_attaque		= 6;
				$degatMin_arme_attaque 			= 4;
				$degatMax_arme_attaque 			= 4;
				$precision_arme_attaque			= 30;
				$degatZone_arme_attaque 		= 0;
				
			}
		}
		
		// recup des données du perso
		$sql = "SELECT nom_perso, idJoueur_perso, image_perso, xp_perso, x_perso, y_perso, pm_perso, pi_perso, pv_perso, pvMax_perso, pmMax_perso, pa_perso, paMax_perso, recup_perso, bonusRecup_perso, perception_perso, bonusPerception_perso, charge_perso, chargeMax_perso, dateCreation_perso, clan, id_grade
				FROM perso, perso_as_grade
				WHERE perso_as_grade.id_perso = perso.id_perso
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
		$per_perso 		= $t_perso["perception_perso"];
		$bp_perso 		= $t_perso["bonusPerception_perso"];
		$ch_perso 		= $t_perso["charge_perso"];
		$chM_perso 		= $t_perso["chargeMax_perso"];
		$dc_perso 		= $t_perso["dateCreation_perso"];
		$id_j_perso		= $t_perso["idJoueur_perso"];
		$clan_perso 	= $t_perso["clan"];
		$grade_perso 	= $t_perso["id_grade"];
		
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
											<td><?php echo "<u><b>Mouvements restants :</b></u> ".$pm_perso."/".$pmM_perso; ?><?php echo " - <u><b>Points de vie :</b></u> ".$pv_perso."/".$pvM_perso; ?></td>
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
			
				echo "<div class=\"erreur\" align=\"center\">Vous n'avez pas assez de pa pour effectuer cette action !</div>";
				echo "<a href=\"jouer.php\"><font color=\"#000000\" size=\"1\" face=\"Verdana, Arial, Helvetica, sans-serif\">[ retour ]</font></a>";
				
			}	
			else { 
				//le perso a assez de pa
					
				//la cible est encore en vie
				if ($pv_cible > 0) { 
							
					echo "Vous avez lancé une attaque sur <b>$nom_cible [$id_cible]</b> avec $nom_arme_attaque<br/>";
						
					// Calcul touche
					$touche = mt_rand(0,100);
					$precision_final = $precision_arme_attaque - $bonus_cible;
					
					echo "Votre score de touche : ".$touche."<br>";
					
					// Score touche <= precision arme utilisée - bonus cible pour l'attaque = La cible est touchée
					if ($touche <= $precision_final) {
		
						// calcul degats arme
						$degats_final = mt_rand($degatMin_arme_attaque, $degatMin_arme_attaque * $valeur_des_arme_attaque) - $protec_cible;
						
						if($degats_final < 0) {
							$degats_final = 0;
						}
						
						if ($touche <= 2) {
							// Coup critique ! Dégats et Gains PC X 2
							$degats_final = $degats_final * 2;
						}
						
						// TODO - calcul gain XP selon pnj
						$gain_xp = mt_rand(1, 12);
						
						if ($id_arme_attaque == 10) {
								
							// Seringue
							if ($pv_cible + $degats_final >= $pvM_cible) {
								$degats_final = $pvM_cible - $pv_cible;
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
						}
						
						echo "<br>Vous avez infligé <b>$degats_final</b> dégâts à la cible.<br><br>";
						echo "Vous avez gagné <b>$gain_xp</b> xp.";
						
						// maj gain xp / pi perso
						$sql = "UPDATE perso SET xp_perso=xp_perso+$gain_xp, pi_perso=pi_perso+$gain_xp WHERE id_perso='$id'";
						$mysqli->query($sql);
						
						// Passage grade grouillot
						if ($grade_perso == 1) {
							
							if ($xp_perso + $gain_xp >= 500) {
								// On le passage 1ere classe
								$sql = "UPDATE perso_as_grade SET id_grade='101' WHERE id_perso='$id'";
								$mysqli->query($sql);
								
								echo "<br /><b>Vous êtes passé au grade de Grouillot 1ere classe</b><br />";
							}
						}
						
						if ($grade_perso == 101) {
							
							if ($xp_perso + $gain_xp >= 1500) {
								// On le passe élite
								$sql = "UPDATE perso_as_grade SET id_grade='102' WHERE id_perso='$id'";
								$mysqli->query($sql);
								
								echo "<br /><b>Vous êtes passé au grade de Grouillot d'élite</b><br />";
							}
						}
						
						if ($id_arme_attaque == 10 || $id_arme_attaque == 11) {
							
							// maj evenement
							$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>','a soigné ','$id_cible','<b>$nom_cible</b>',': $degats_final soins',NOW(),'0')";
							$mysqli->query($sql);
							
						} else {
							
							// maj evenement
							$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>','a attaqué ','$id_cible','<b>$nom_cible</b>',': $degats_final degats',NOW(),'0')";
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
							
							// On parcours les cibles pour degats collateraux
							while ($t_recherche_collat = $res_recherche_collat->fetch_assoc()) {
								
								$id_cible_collat = $t_recherche_collat["idPerso_carte"];
								
								if ($id_cible_collat < 50000) {
									
									// Perso
									// Récupération des infos du perso
									$sql = "SELECT idJoueur_perso, nom_perso, image_perso, xp_perso, x_perso, y_perso, pv_perso, pvMax_perso, or_perso, clan, id_grade
											FROM perso, perso_as_grade 
											WHERE perso_as_grade.id_perso = perso.id_perso
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
									
									// Récupération de la couleur associée au clan de la cible
									$couleur_clan_collat = couleur_clan($clan_collat);
									
									$gain_pc_collat = 1;
									$gain_xp_collat = 1;
									
									// mise a jour des pv et des malus de la cible
									$sql = "UPDATE perso SET pv_perso=pv_perso-$degats_collat, bonus_perso=bonus_perso-2 WHERE id_perso='$id_cible_collat'";
									$mysqli->query($sql);
									
									echo "<br>Vous avez infligé $degats_collat dégâts collatéraux à <font color='$couleur_clan_collat'>$nom_collat</font> - Matricule $id_cible_collat.<br>";
									echo "Vous avez gagné $gain_xp_collat xp.<br><br>";
									
									// mise a jour des xp/pi
									$sql = "UPDATE perso SET xp_perso=xp_perso+$gain_xp_collat, pi_perso=pi_perso+$gain_xp_collat WHERE id_perso='$id'"; 
									$mysqli->query($sql);
									
									// Passage grade grouillot
									if ($grade_perso == 1) {
										
										if ($xp_perso + $gain_xp_collat >= 500) {
											// On le passage 1ere classe
											$sql = "UPDATE perso_as_grade SET id_grade='101' WHERE id_perso='$id'";
											$mysqli->query($sql);
											
											echo "<br /><b>Vous êtes passé au grade de Grouillot 1ere classe</b><br />";
										}
									}
									
									if ($grade_perso == 101) {
										
										if ($xp_perso + $gain_xp_collat >= 1500) {
											// On le passe élite
											$sql = "UPDATE perso_as_grade SET id_grade='102' WHERE id_perso='$id'";
											$mysqli->query($sql);
											
											echo "<br /><b>Vous êtes passé au grade de Grouillot d'élite</b><br />";
										}
									}
									
									// mise à jour des PC du chef
									$sql = "SELECT perso.id_perso, pc_perso, id_grade FROM perso, perso_as_grade WHERE perso.id_perso = perso_as_grade.id_perso AND idJoueur_perso='$id_j_perso' AND chef='1'";
									$res = $mysqli->query($sql);
									$t_chef = $res->fetch_assoc();
									
									$id_perso_chef = $t_chef["id_perso"];
									$pc_perso_chef = $t_chef["pc_perso"];
									$id_grade_chef = $t_chef["id_grade"];
									
									$sql = "UPDATE perso SET pc_perso = pc_perso + $gain_pc_collat WHERE id_perso='$id_perso_chef'";
									$mysqli->query($sql);
									
									$pc_perso_chef_final = $pc_perso_chef + $gain_pc_collat;
									
									// Verification passage de grade 
									$sql = "SELECT id_grade, nom_grade FROM grades WHERE pc_grade <= $pc_perso_chef_final AND pc_grade != 0 ORDER BY id_grade DESC LIMIT 1";
									$res = $mysqli->query($sql);
									$t_grade = $res->fetch_assoc();
									
									$id_grade_final 	= $t_grade["id_grade"];
									$nom_grade_final	= $t_grade["nom_grade"];
									
									if ($id_grade_chef < $id_grade_final) {
										
										// Passage de grade								
										$sql = "UPDATE perso_as_grade SET id_grade='$id_grade_final' WHERE id_perso='$id_perso_chef'";
										$mysqli->query($sql);
										
										echo "<br /><b>Votre chef de bataillon est passé au grade de $nom_grade_final</b><br />";
										
									}
									
									// mise a jour de la table evenement
									$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>','a infligé des dégâts collatéraux ','$id_cible_collat','<font color=$couleur_clan_collat><b>$nom_collat</b></font>',': $degats_collat degats',NOW(),'0')";
									$mysqli->query($sql);
									
									$sql = "SELECT pv_perso, x_perso, y_perso, xp_perso, pi_perso FROM perso WHERE id_perso='$id_cible_collat'";
									$res = $mysqli->query($sql);
									$tab = $res->fetch_assoc();
									
									$pv_collat_fin 	= $tab["pv_perso"];
									$x_collat_fin 	= $tab["x_perso"];
									$y_collat_fin 	= $tab["y_perso"];
									$xp_collat_fin 	= $tab["xp_perso"];
									$pi_collat_fin 	= $tab["pi_perso"];
										
									// il est mort
									if ($pv_collat_fin <= 0) {
									
										// on l'efface de la carte
										$sql = "UPDATE $carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_collat_fin' AND y_carte='$y_collat_fin'";
										$mysqli->query($sql);
					
										// Calcul gains (po et xp)
										$perte_po = gain_po_mort($or_collat);
										
										// TODO
										$perte_xp_collat = 0;
					
										// MAJ perte xp/po/stat cible
										$sql = "UPDATE perso SET or_perso=or_perso-$perte_po, xp_perso=xp_perso-$perte_xp_collat, pi_perso=0, nb_mort=nb_mort+1 WHERE id_perso='$id_cible_collat'";
										$mysqli->query($sql);
					
										echo "<div class=\"infoi\">Vous avez capturé <font color='$couleur_clan_collat'>$nom_collat</font> - Matricule $id_cible_collat ! <font color=red>Félicitations.</font></div>";
										
										// maj evenements
										$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>','a capturé','$id_cible_collat','<font color=$couleur_clan_collat><b>$nom_collat</b></font>','',NOW(),'0')";
										$mysqli->query($sql);
										
										// maj cv
										$sql = "INSERT INTO `cv` (IDActeur_cv, nomActeur_cv, IDCible_cv, nomCible_cv, date_cv) VALUES ($id,'<font color=$couleur_clan_perso>$nom_perso</font>','$id_cible_collat','<font color=$couleur_clan_collat>$nom_collat</font>',NOW())";
										$mysqli->query($sql);
					
										// maj stats de la cible
										$sql = "UPDATE perso SET nb_kill=nb_kill+1 WHERE id_perso=$id";
										$mysqli->query($sql);
										
										// maj stats camp
										if($clan_cible != $clan_perso){
											$sql = "UPDATE stats_camp_kill SET nb_kill=nb_kill+1 WHERE id_camp=$clan_perso";
											$mysqli->query($sql);
										}
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
									
									// mise a jour des pv de la cible
									$sql = "UPDATE instance_pnj SET pv_i=pv_i-$degats_collat WHERE idInstance_pnj='$id_cible_collat'";
									$mysqli->query($sql);
									
									echo "<br>Vous avez infligé $degats_collat dégâts collatéraux à $nom_cible_collat<br>";
									echo "Vous avez gagné $gain_xp_collat xp.<br><br>";
									
									// mise a jour des xp/pi
									$sql = "UPDATE perso SET xp_perso=xp_perso+$gain_xp_collat, pi_perso=pi_perso+$gain_xp_collat WHERE id_perso='$id'"; 
									$mysqli->query($sql);
									
									// Passage grade grouillot
									if ($grade_perso == 1) {
										
										if ($xp_perso + $gain_xp_collat >= 500) {
											// On le passage 1ere classe
											$sql = "UPDATE perso_as_grade SET id_grade='101' WHERE id_perso='$id'";
											$mysqli->query($sql);
											
											echo "<br /><b>Vous êtes passé au grade de Grouillot 1ere classe</b><br />";
										}
									}
									
									if ($grade_perso == 101) {
										
										if ($xp_perso + $gain_xp_collat >= 1500) {
											// On le passe élite
											$sql = "UPDATE perso_as_grade SET id_grade='102' WHERE id_perso='$id'";
											$mysqli->query($sql);
											
											echo "<br /><b>Vous êtes passé au grade de Grouillot d'élite</b><br />";
										}
									}
									
									// maj evenement
									$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>','a infligé des dégâts collatéraux ','$id_cible_collat','<b>$nom_cible_collat</b>',': $degats_collat degats',NOW(),'0')";
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
										$sql = "INSERT INTO `cv` (IDActeur_cv, nomActeur_cv, IDCible_cv, nomCible_cv, date_cv) VALUES ($id,'<font color=$couleur_clan_perso>$nom_perso</font>','$id_cible_collat','$nom_cible_collat',NOW())";
										$mysqli->query($sql);
										
										echo "<br><center><a href=\"jouer.php\"><font color=\"#000000\" size=\"1\" face=\"Verdana, Arial, Helvetica, sans-serif\">[ retour ]</font></a></center>";
									}
								} else {
									// Batiment => pas de collat sur batiment
								}
							}
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
							$sql = "INSERT INTO `cv` (IDActeur_cv, nomActeur_cv, IDCible_cv, nomCible_cv, date_cv) VALUES ($id,'<font color=$couleur_clan_perso>$nom_perso</font>','$id_cible','$nom_cible',NOW())";
							$mysqli->query($sql);
							
							echo "<br><center><a href=\"jouer.php\"><font color=\"#000000\" size=\"1\" face=\"Verdana, Arial, Helvetica, sans-serif\">[ retour ]</font></a></center>";
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
							echo "<br/><center><a href=\"jouer.php\"><font color=\"#000000\" size=\"1\" face=\"Verdana, Arial, Helvetica, sans-serif\">[ retour ]</font></a></center>";
						}						
					}
					else { // la cible a esquivé l'attaque
		
						echo "<br>Vous avez raté votre cible.<br><br>";
							
						// maj evenement
						$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_cible,'<b>$nom_cible</b>','a esquivé l\'attaque de','$id','<font color=$couleur_clan_perso><b>$nom_perso</b></font>','',NOW(),'0')";
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
							echo "<br/><center><a href=\"jouer.php\"><font color=\"#000000\" size=\"1\" face=\"Verdana, Arial, Helvetica, sans-serif\">[ retour ]</font></a></center>";
						}
		
					}
					
					//mise à jour des pa
					$sql = "UPDATE perso SET pa_perso=pa_perso-$coutPa_arme_attaque WHERE id_perso='$id'";
					$res = $mysqli->query($sql);
				}			
				else {
					
					//la cible est déjà morte
					echo "Erreur : La cible est déjà morte !";
					echo "<br/><center><a href=\"jouer.php\"><font color=\"#000000\" size=\"1\" face=\"Verdana, Arial, Helvetica, sans-serif\">[ retour ]</font></a></center>";
				}
			}
		}
		else { // la cible n'est pas à portée d'attaque
			echo "Erreur : La cible n'est pas à portée d'attaque (Vérifiez la portée de votre arme) ou votre état ne vous permet pas de la cibler (pas assez de perception) !";
			echo "<br/><center><a href=\"jouer.php\"><font color=\"#000000\" size=\"1\" face=\"Verdana, Arial, Helvetica, sans-serif\">[ retour ]</font></a></center>";
		}
	}
	
	// Traitement attaque Batiment
	if ((isset($id_attaque) && $id_attaque != "" && $id_attaque != "personne" && $id_attaque >= 50000 && $id_attaque < 200000)) {
		
		$id_cible =  $id_attaque;
		
		// arme bien passée
		if(isset($id_arme_attaque) && $id_arme_attaque != 1000 && $id_arme_attaque != 2000) {
			
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
				
			} else {
				
				// On ne devrait pas arriver ici
				// Par défaut on va mettre les poings
				$nom_arme_attaque 				= "Poings";
				$coutPa_arme_attaque 			= 3;
				$porteeMin_arme_attaque 		= 1;
				$porteeMax_arme_attaque 		= 1;
				$valeur_des_arme_attaque		= 6;
				$degatMin_arme_attaque 			= 4;
				$degatMax_arme_attaque 			= 4;
				$precision_arme_attaque			= 30;
				$degatZone_arme_attaque 		= 0;
				
			}
		}
		
		// recup des données du perso
		$sql = "SELECT nom_perso, image_perso, xp_perso, x_perso, y_perso, pm_perso, pi_perso, pv_perso, pvMax_perso, pmMax_perso, pa_perso, paMax_perso, recup_perso, bonusRecup_perso, perception_perso, bonusPerception_perso, charge_perso, chargeMax_perso, dateCreation_perso, clan, id_grade
				FROM perso, perso_as_grade
				WHERE perso_as_grade.id_perso = perso.id_perso
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
		$per_perso 		= $t_perso["perception_perso"];
		$bp_perso 		= $t_perso["bonusPerception_perso"];
		$ch_perso 		= $t_perso["charge_perso"];
		$chM_perso 		= $t_perso["chargeMax_perso"];
		$dc_perso 		= $t_perso["dateCreation_perso"];
		$clan_perso 	= $t_perso["clan"];
		$grade_perso 	= $t_perso["id_grade"];
		
		// Récupération de la couleur associée au clan du perso
		$couleur_clan_perso = couleur_clan($clan_perso);
		
		if(is_a_portee_attaque($mysqli, $carte, $id, $id_cible, $porteeMin_arme_attaque, $porteeMax_arme_attaque, $per_perso)) {
		
			$coutPa_attaque=$coutPa_arme_attaque;
					
			// recuperation des données du batiment	
			$sql = "SELECT batiment.id_batiment, nom_batiment, description, nom_instance, pv_instance, pvMax_instance, x_instance, y_instance, camp_instance, contenance_instance 
					FROM batiment, instance_batiment
					WHERE batiment.id_batiment=instance_batiment.id_batiment
					AND id_instanceBat=$id_cible";
			$res = $mysqli->query($sql);
			$bat = $res->fetch_assoc();
			
			$id_batiment 			= $bat['id_batiment'];
			$nom_batiment 			= $bat['nom_batiment'];
			$description_batiment 	= $bat['description'];
			$nom_instance_batiment 	= $bat['nom_instance'];
			$pv_instance 			= $bat['pv_instance'];
			$pvMax_instance 		= $bat['pvMax_instance'];
			$x_instance 			= $bat['x_instance'];
			$y_instance 			= $bat['y_instance'];
			$camp_instance 			= $bat['camp_instance'];
			$contenance_instance 	= $bat['contenance_instance'];
			
			if($camp_instance == '1'){
				$camp_bat = 'b';
				$couleur_bat = 'blue';
			}
			if($camp_instance == '2'){
				$camp_bat = 'r';
				$couleur_bat = 'red';
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
											<td><?php echo "<u><b>Mouvements restants :</b></u> ".$pm_perso."/".$pmM_perso; ?><?php echo " - <u><b>Points de vie :</b></u> ".$pv_perso."/".$pvM_perso; ?></td>
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
				echo "<div class=\"erreur\" align=\"center\">Vous n'avez pas assez de pa pour effectuer cette action !</div>";
				echo "<a href=\"jouer.php\"><font color=\"#000000\" size=\"1\" face=\"Verdana, Arial, Helvetica, sans-serif\">[ retour ]</font></a>";
			}
			else {
				//le perso a assez de pa
					
				//la cible est encore en vie
				if ($pv_instance > 0) {
							
					echo "Vous avez lancé une attaque sur <b>$nom_batiment [$id_cible]</b>.<br>";
					
					$gain_xp = mt_rand(1,3);
		
					// Calcul touche
					$touche = mt_rand(0,100);
					$precision_final = $precision_arme_attaque;
					
					echo "Votre score de touche : ".$touche."<br>";
					
					// Score touche <= precision arme utilisée - bonus cible pour l'attaque = La cible est touchée
					if ($touche <= $precision_final) {
		
						// calcul degats arme
						$degats_final = mt_rand($degatMin_arme_attaque, $degatMin_arme_attaque * $valeur_des_arme_attaque);
						
						if($degats_final < 0) {
							$degats_final = 0;
						}
						
						if ($touche <= 2) {
							// Coup critique ! Dégats et Gains PC X 2
							$degats_final = $degats_final * 2;
						}
						
						// Canon d'artillerie
						if ($id_arme_attaque == 13) {
							// Bonus dégats 20D10
							$bonus_degats_canon = mt_rand(20, 200);
							$degats_final = $degats_final + $bonus_degats_canon;
						}
						
						// mise à jour des pv du batiment
						$sql = "UPDATE instance_batiment SET pv_instance=pv_instance-$degats_final WHERE id_instanceBat='$id_cible'";
						$mysqli->query($sql);
						
						echo "<br>Vous avez infligé <b>$degats_final</b> degats à la cible.<br><br>";
						echo "Vous avez gagné <b>$gain_xp</b> xp<br>";
							
						// maj gain xp, pi perso
						$sql = "UPDATE perso SET xp_perso=xp_perso+$gain_xp, pi_perso=pi_perso+$gain_xp WHERE id_perso='$id'";
						$mysqli->query($sql);
						
						// Passage grade grouillot
						if ($grade_perso == 1) {
							
							if ($xp_perso + $gain_xp >= 500) {
								// On le passage 1ere classe
								$sql = "UPDATE perso_as_grade SET id_grade='101' WHERE id_perso='$id'";
								$mysqli->query($sql);
								
								echo "<br /><b>Vous êtes passé au grade de Grouillot 1ere classe</b><br />";
							}
						}
						
						if ($grade_perso == 101) {
							
							if ($xp_perso + $gain_xp >= 1500) {
								// On le passe élite
								$sql = "UPDATE perso_as_grade SET id_grade='102' WHERE id_perso='$id'";
								$mysqli->query($sql);
								
								echo "<br /><b>Vous êtes passé au grade de Grouillot d'élite</b><br />";
							}
						}
							
						// maj evenement
						$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>','a attaqué ','$id_cible','<font color=$couleur_bat><b>$nom_batiment</b></font>',': $degats_final degats',NOW(),'0')";
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
						
							// Récupération des persos dans le batiment
							$sql = "SELECT id_perso FROM perso_in_batiment WHERE id_instanceBat='$id_cible'";
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
								$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ('$id_p','<font color=$couleur_clan_p><b>$nom_p</b></font>','a été bléssé suite à la destruction du bâtiment',NULL,'',' : - $perte_pv',NOW(),'0')";
								$mysqli->query($sql);
								
								// Le perso est encore vivant
								if($pv_p > 0){
									
									// pv/2
									$sql_p = "UPDATE perso SET pv_perso=pv_perso/2 WHERE id_perso='$id_p'";
									$mysqli->query($sql_p);
									
									// Traitement répartissement des persos sur la carte
									$occup = 1;
									while ($occup == 1)
									{
										// TODO - changer façon de faire 
										// TODO - Tourner autour de la position du batiment jusqu'à ce qu'on trouve une position libre
										$x = pos_zone_rand_x($x_cible-5, $x_cible+5); 
										$y = pos_zone_rand_y($y_cible-5, $y_cible+5);
										$occup = verif_pos_libre($mysqli, $x, $y);
									}
									
									// MAJ du perso sur la carte
									$sql_u = "UPDATE carte SET occupee_carte = '1', image_carte='$image_p', idPerso_carte='$id_p' WHERE x_carte='$x' AND y_carte='$y'";
									$mysqli->query($sql_u);
									
									// MAJ des coordonnées du perso
									$sql_u2 = "UPDATE perso SET x_perso='$x' AND y_perso='$y' WHERE id_perso='$id_p'";
									$mysqli->query($sql_u2);
								}
								else {
									// Le perso est mort
																	
									// Ajout du kill
									$sql_u2 = "UPDATE perso SET nb_kill=nb_kill+1 WHERE id_perso='$id'";
									$mysqli->query($sql_u2);
									
									$sql_u2 = "UPDATE perso SET nb_mort=nb_mort+1 WHERE id_perso='$id_p'";
									$mysqli->query($sql_u2);
									
									// maj evenement
									$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>','a capturé','$id_p','<font color=$couleur_clan_p><b>$nom_p</b></font>',' : mort suite à l\'explosion du bâtiment $id_cible',NOW(),'0')";
									$mysqli->query($sql);
										
									// maj cv
									$sql = "INSERT INTO `cv` (IDActeur_cv, nomActeur_cv, IDCible_cv, nomCible_cv, date_cv) VALUES ($id,'<font color=$couleur_clan_perso>$nom_perso</font>','$id_p','<font color=$couleur_clan_p>$nom_p</font>',NOW())"; 
									$mysqli->query($sql);
									
								}
							}
							
							// on supprime les persos du batiment
							$sql = "DELETE FROM perso_in_batiment WHERE id_instanceBat='$id_cible'";
							$mysqli->query($sql);
							
							// on delete le bâtiment
							$sql = "DELETE FROM instance_batiment WHERE id_instanceBat='$id_cible'";
							$mysqli->query($sql);
					
							echo "Vous avez détruit votre cible ! <font color=red>Félicitations.</font>";
								
							// maj evenement
							$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>','a détruit','$id_cible','<font color=$couleur_bat><b>$nom_batiment</b></font>','',NOW(),'0')";
							$mysqli->query($sql);
								
							// maj cv
							$sql = "INSERT INTO `cv` (IDActeur_cv, nomActeur_cv, IDCible_cv, nomCible_cv, date_cv) VALUES ($id,'<font color=$couleur_clan_perso>$nom_perso</font>','$id_cible','<font color=$couleur_bat>$nom_batiment</font>',NOW())"; 
							$mysqli->query($sql);
								
							echo "<br><center><a href=\"jouer.php\"><font color=\"#000000\" size=\"1\" face=\"Verdana, Arial, Helvetica, sans-serif\">[ retour ]</font></a></center>";
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
							echo "<br/><center><a href=\"jouer.php\"><font color=\"#000000\" size=\"1\" face=\"Verdana, Arial, Helvetica, sans-serif\">[ retour ]</font></a></center>";
						}
					}
					else {
						
						echo "<br>Vous avez raté votre cible.<br><br>";
						
						//mise à jour des pa
						$sql = "UPDATE perso SET pa_perso=pa_perso-$coutPa_attaque WHERE id_perso='$id'";
						$res = $mysqli->query($sql);
							
						// maj evenement
						$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>',' a raté son attaque contre','$id_cible','<font color='$couleur_bat'><b>$nom_batiment</b></font>','',NOW(),'0')";
						$mysqli->query($sql);
						
						?>
							<form action="agir.php" method="post">
								<input type="hidden" name="re_attaque_hid" value="<?php echo $id_cible.",".$id_arme_attaque;?>" />
								<input type="submit" name="re_attaque" value="attaquer à nouveau">
							</form> 
							
						<?php
						echo "<br/><center><a href=\"jouer.php\"><font color=\"#000000\" size=\"1\" face=\"Verdana, Arial, Helvetica, sans-serif\">[ retour ]</font></a></center>";
					}					
				}
				else {
					
					//la cible est déjà morte
					echo "Erreur : La cible est déjà morte !";
					echo "<br/><center><a href=\"jouer.php\"><font color=\"#000000\" size=\"1\" face=\"Verdana, Arial, Helvetica, sans-serif\">[ retour ]</font></a></center>";
				}
			}
		}
		else {
			
			// la cible n'est pas à portée d'attaque
			echo "Erreur : La cible n'est pas à portée d'attaque (Vérifiez la portée de votre arme) ou votre état ne vous permet pas de la cibler (pas assez de perception) !";
			echo "<br/><center><a href=\"jouer.php\"><font color=\"#000000\" size=\"1\" face=\"Verdana, Arial, Helvetica, sans-serif\">[ retour ]</font></a></center>";
			
		}
	}	
		
	// 
	if ( (!isset($_POST["id_attaque_cac"]) || $_POST["id_attaque_cac"] == "" || $_POST["id_attaque_cac"] == "personne") 
		&& (!isset($_POST["id_attaque_dist"]) || $_POST["id_attaque_dist"] == "" || $_POST["id_attaque_dist"] == "personne")
		&& !isset($_POST["re_attaque"])){
			
		echo "<center>vous devez choisir une cible pour attaquer</center><br>";
		echo "<br/><center><a href=\"jouer.php\"><font color=\"#000000\" size=\"1\" face=\"Verdana, Arial, Helvetica, sans-serif\">[ retour ]</font></a></center>";
	}
}
else {
	echo "Erreur : La valeur entrée est incorrecte !";
	echo "<br><center><a href=\"jouer.php\"><font color=\"#000000\" size=\"1\" face=\"Verdana, Arial, Helvetica, sans-serif\">[ retour ]</font></a></center>";
}



?>
</body>
</html>
