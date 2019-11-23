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

if(isset($_POST["re_attaque"])){
	$t_id_c = explode(":",$_POST["re_attaque"]);
	$id_c = $t_id_c[1];
	$verif = preg_match("#^[0-9]*[0-9]$#i","$id_c");
}
else {
	$id_attaque = $_POST["id_attaque"];
	$verif = preg_match("#^[0-9]*[0-9]$#i","$id_attaque");
}

if($verif){
	//traitement de l'attaque sur un perso
	if ((isset($_POST["id_attaque"]) && $_POST["id_attaque"]!="" && $_POST["id_attaque"] < 10000) || (isset($_POST["re_attaque"]) && $id_c < 10000) ) { 
	
		if(!in_bat($mysqli, $id)){
	
			if(isset($_POST["re_attaque"])) {
				$id_cible = $id_c;
			}
			else {
				$id_cible = $id_attaque;
			}
			
			// Recupération de l'arme équipée
			$id_arme_equipee = id_arme_equipee($mysqli, $id);
			
			// Si il est bien équipée d'une arme
			if($id_arme_equipee) {
				
				// Recupération des caracs de l'arme
				$sql = "SELECT nom_arme, coutPa_arme, porteeMin_arme, porteeMax_arme, additionMin_degats, additionMax_degats, multiplicateurMin_degats, multiplicateurMax_degats, degatMin_arme, degatMax_arme, degatZone_arme, main FROM arme WHERE id_arme='$id_arme_equipee'";
				$res = $mysqli->query($sql);
				$t_a = $res->fetch_assoc();
				
				$nom_arme_equipee = $t_a["nom_arme"];
				$coutPa_arme_equipee = $t_a["coutPa_arme"];
				$porteeMin_arme_equipee = $t_a["porteeMin_arme"];
				$porteeMax_arme_equipee = $t_a["porteeMax_arme"];
				$additionMin_degats_equipee = $t_a["additionMin_degats"];
				$additionMax_degats_equipee = $t_a["additionMax_degats"];
				$multiplicateurMin_degats_equipee = $t_a["multiplicateurMin_degats"];
				$multiplicateurMax_degats_equipee = $t_a["multiplicateurMax_degats"];
				$degatMin_arme_equipee = $t_a["degatMin_arme"];
				$degatMax_arme_equipee = $t_a["degatMax_arme"];
				$degatZone_arme_equipee = $t_a["degatZone_arme"];
				$main_arme_equipee = $t_a["main"];
			}
			else {
				// Poings = arme de Corps a corps
				$porteeMin_arme_equipee = 1;
				$porteeMax_arme_equipee = 1;
			}
			
			// recup des données du perso
			$sql = "SELECT nom_perso, image_perso, xp_perso, x_perso, y_perso, pm_perso, pi_perso, pv_perso, pvMax_perso, pmMax_perso, pa_perso, paMax_perso, recup_perso, bonusRecup_perso, perception_perso, bonusPerception_perso, charge_perso, chargeMax_perso, dateCreation_perso, clan FROM perso WHERE id_perso='$id'";
			$res = $mysqli->query($sql);
			$t_perso = $res->fetch_assoc();
			
			$nom_perso = $t_perso["nom_perso"];
			$image_perso = $t_perso["image_perso"];
			$xp_perso = $t_perso["xp_perso"];
			$x_perso = $t_perso["x_perso"];
			$y_perso = $t_perso["y_perso"];
			$pm_perso = $t_perso["pm_perso"];
			$pmM_perso = $t_perso["pmMax_perso"];
			$pi_perso = $t_perso["pi_perso"];
			$pv_perso = $t_perso["pv_perso"];
			$pvM_perso = $t_perso["pvMax_perso"];
			$pa_perso = $t_perso["pa_perso"];
			$paM_perso = $t_perso["paMax_perso"];
			$rec_perso = $t_perso["recup_perso"];
			$br_perso = $t_perso["bonusRecup_perso"];
			$per_perso = $t_perso["perception_perso"];
			$bp_perso = $t_perso["bonusPerception_perso"];
			$ch_perso = $t_perso["charge_perso"];
			$chM_perso = $t_perso["chargeMax_perso"];
			$dc_perso = $t_perso["dateCreation_perso"];
			$clan_perso = $t_perso["clan"];
			
			// verification si le perso est bien a portée d'attaque			
			if(is_a_portee_attaque($mysqli, $carte, $id, $id_cible, $porteeMin_arme_equipee, $porteeMax_arme_equipee, $per_perso)) {	
				
				if($id_arme_equipee){
					// Récupération des pa de l'attaque avec l'arme
					$coutPa_attaque = $coutPa_arme_equipee;
				}
				else {
					$coutPa_attaque='5';
				}
				
				// recuperation des données du perso cible
				$sql = "SELECT idJoueur_perso, nom_perso, image_perso, xp_perso, x_perso, y_perso, pm_perso, pi_perso, pv_perso, pvMax_perso, pmMax_perso, pa_perso, paMax_perso, recup_perso, bonusRecup_perso, bonus_perso, perception_perso, bonusPerception_perso, charge_perso, chargeMax_perso, dateCreation_perso, or_perso, clan FROM perso WHERE ID_perso='$id_cible'";
				$res = $mysqli->query($sql);
				$t_cible = $res->fetch_assoc();
				
				$id_joueur_cible = $t_cible["idJoueur_perso"];
				$nom_cible = $t_cible["nom_perso"];
				$xp_cible = $t_cible["xp_perso"];
				$x_cible = $t_cible["x_perso"];
				$y_cible = $t_cible["y_perso"];
				$pm_cible = $t_cible["pm_perso"];
				$pmM_cible = $t_cible["pmMax_perso"];
				$pi_cible = $t_cible["pi_perso"];
				$pv_cible = $t_cible["pv_perso"];
				$pvM_cible = $t_cible["pvMax_perso"];
				$pa_cible = $t_cible["pa_perso"];
				$paM_cible = $t_cible["paMax_perso"];
				$rec_cible = $t_cible["recup_perso"];
				$br_cible = $t_cible["bonusRecup_perso"];
				$bonus_cible = $t_cible["bonus_perso"];
				$per_cible = $t_cible["perception_perso"];
				$bp_cible = $t_cible["bonusPerception_perso"];
				$ch_cible = $t_cible["charge_perso"];
				$chM_cible = $t_cible["chargeMax_perso"];
				$dc_cible = $t_cible["dateCreation_perso"];
				$or_cible = $t_cible["or_perso"];
				$image_perso_cible = $t_cible["image_perso"];
				$clan_cible = $t_cible["clan"];
				
				// Récupération de la couleur associée au clan de la cible
				$couleur_clan_cible = couleur_clan($clan_cible);
				
				// verif chanceux et recup nb_points de chance
				if(est_chanceux($mysqli, $id_cible)){
					$bonus_chance = 2 * est_chanceux($id_cible);
				}
				else {
					$bonus_chance = 0;
				}
				
				// total d'armure de la cible
				$total_armure_cible = defense_armure($mysqli, $id_cible);
				
				// verification si la cible posséde defense d'armure
				$nb_def_armure = possede_defense_armure($mysqli, $id_cible);
				$bonus_pourc_def_armure = 5 * $nb_def_armure;
				
				// 10% de chance de base d'utiliser tout son bonus d'armure
				$u_armure = rand(0, 100);
				if($u_armure <= 10 + $bonus_chance + $bonus_pourc_def_armure){
					// le perso utilise la defense totale que lui procure son armure
					$armure_utilisee = $total_armure_cible;
				}
				else {
					$armure_utilisee = rand(0, $total_armure_cible - 1);
				}
				
				// Récupération de la couleur associée au clan du perso
				$couleur_clan_perso = couleur_clan($clan_perso);
				
				$pa_restant = $pa_perso - $coutPa_attaque;
				if($pa_restant <= 0){
					$pa_restant = 0;
				}				
				?>
				<table border=0 width=100%>
					<tr height=50%><td width=50%>	
					<table border=1 height=100% width=100%>		
						<tr><td width=25%>	
						<table border=0 width=100%>
							<tr>
							<td align="center"><img src="../images_perso/<?php echo $image_perso; ?>"></td>
							</tr>
						</table>
						</td><td width=75%>
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
					</table>
					</td>
					<td width=50%>	
					<table border=1 height=100% width=100%>		
						<tr><td width=25%>	
						<table border=0 width=100%>
							<tr>
							<td align="center"><img src="../images_perso/<?php echo $image_perso_cible; ?>"></td>
							</tr>
						</table>
						</td><td width=75%>
						<table border=0 width=100%>
						<tr>
							<td><?php echo "<u><b>Pseudo :</b></u> ".$nom_cible; ?></td>
						<tr>
						<tr>
							<td><?php echo "<u><b>Xp :</b></u> ".$xp_cible.""; ?></td>
						<tr>
						<tr>
							<td><?php echo "<u><b>Position sur la carte :</b></u> ".$x_cible."/".$y_cible; ?></td>
						<tr>
						<tr>
							<td><?php echo"<u><b>Camp :</b></u> ".$couleur_clan_cible; ?></td>
						</tr>
						</table>
						</td>
					</table>
					</tr>
		
					<tr height=50%>
					</td></tr>	
				</table>		
				<?php					
				// le perso n'a pas assez de pa pour faire cette attaque
				if ($pa_perso < $coutPa_attaque) {
					echo "<div class=\"erreur\" align=\"center\">Vous n'avez pas assez de pa pour effectuer cette action !</div>";
					echo "<a href=\"jouer.php\"><font color=\"#000000\" size=\"1\" face=\"Verdana, Arial, Helvetica, sans-serif\">[ retour ]</font></a>";
				}	
				else { 
					// le perso a assez de pa
					// la cible est encore en vie
					if ($pv_cible > 0) { 
						
						// Vérifie si le joueur attaqué a coché l'envoi de mail
						$mail_info_joueur = verif_coche_mail($mysqli, $id_joueur_cible);
						
						if($mail_info_joueur){
							// Envoi du mail
							mail_attaque($mysqli, $nom_perso, $id_cible);
						}
						
						echo "Vous avez lancé une attaque sur <b>$nom_cible [$id_cible]</b><br/>";
						
						// Calcul des scores d'attaque et de defense
						// TODO
						$score_perso = 0;
						$score_cible = 0;
						
						echo "Votre score d'attaque : <br>";
						echo "Son score de défense : <br>";
						
						if ($score_cible <= $score_perso) { //la cible est touchée
							
							// Récupération du gain d'xp en fonction des levels
							$gain_xp = gain_xp($clan_perso, $clan_cible);
			
							// Si le perso est équipée d'une arme => On prend les dégats de l'arme
							if($id_arme_equipee){				
								// Calcul des degats de l'arme
								if($degatMin_arme_equipee && $degatMax_arme_equipee){
									$min_degats_arme = $degatMin_arme_equipee;
									$max_degats_arme = $degatMax_arme_equipee;
								}
								else {
									$min_degats_arme = 1;
									$max_degats_arme = 5;
								}
								$degats = rand($min_degats_arme, $max_degats_arme);
							} else {
								$degats = rand(1, 5);
							}
							
							// Mise a jour des degats selon l'armure de la cible
							$degats_final = $degats - $armure_utilisee;
							if($degats_final < 0)
								$degats_final = 0;
							
							// MAJ Armure cible
							// Vérification si la cible posséde des armures
							$sql_armure_cible = "SELECT id_armure FROM perso_as_armure WHERE id_perso='$id_cible' AND est_portee='1' ORDER BY id_armure";
							$res_armure_cible = $mysqli->query($sql_armure_cible);
							$nb_armure_cible = $res_armure_cible->num_rows;
							
							if($nb_armure_cible){								
								// Calcul des pv de dommage
								$pv_endommage = rand(1,5);
								
								// selection de l'armure qui sera endommagée
								$select_armure_endommagee = rand(1, $nb_armure_cible) - 1;
								
								$res_armure_cible->mysqli_data_seek($select_armure_endommagee);
								$t_armure = $res_armure_cible->fetch_assoc();
								
								// Récupération de l'id de l'armure endommagée
								$id_armure_endommagee = $t_armure['id_armure'];
								
								// MAJ  pv armure endommagée
								$sql = "UPDATE perso_as_armure SET pv_armure=pv_armure-$pv_endommage 
										WHERE id_perso='$id_cible'
										AND id_armure='$id_armure_endommagee' AND est_portee='1'";
								$mysqli->query($sql);
										
								// Traitement si armure cassée	
								// Récupération pv armure endommagée par l'attaque
								$sql = "SELECT pv_armure FROM perso_as_armure WHERE id_perso='$id_cible'
										AND id_armure='$id_armure_endommagee' AND est_portee='1'";
								$res = $mysqli->query($sql);
								$t = $res->fetch_assoc();
								$pv_armure_endommagee = $t['pv_armure'];
								
								if($pv_armure_endommagee <= 0){
									// Armure cassée, On supprime
									$sql = "DELETE FROM perso_as_armure WHERE id_perso='$id_cible'
											AND id_armure='$id_armure_endommagee' AND est_portee='1'";
									$mysqli->query($sql);
									
									// Récupération du poids de l'armure cassée
									$sql = "SELECT poids_armure FROM armure WHERE id_armure='$id_armure_endommagee'";
									$res = $mysqli->query($sql);
									$t = $res->fetch_assoc();
									$poids_armure = $t["poids_armure"];
									
									// Mise a jour de la charge du perso dont l'armure s'est cassée
									$sql = "UPDATE perso SET charge_perso=charge_perso - $poids_armure WHERE id_perso='$id_cible'";
									$mysqli->query($sql);
									
									echo "<br />Vous avez cassé une des armures de votre adversaire !";
								}
							}
							
							// mise a jour des pv et des malus de la cible
							$sql = "UPDATE perso SET pv_perso=pv_perso-$degats_final, bonus_perso=bonus_perso-2 WHERE id_perso='$id_cible'";
							$mysqli->query($sql);
							echo "<br>Vous avez infligé $degats_final dégâts à la cible.<br><br>";
							echo "Vous avez gagné $gain_xp xp.<br>";
							
							// mise a jour des xp/pi/pc
							$sql = "UPDATE perso SET xp_perso=xp_perso+$gain_xp, pi_perso=pi_perso+$gain_xp WHERE id_perso='$id'"; 
							$mysqli->query($sql);
							
							// mise a jour de la table evenement
							$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id,'<font color=$couleur_clan_perso>$nom_perso</font>','a attaqué ','$id_cible','<font color=$couleur_clan_cible>$nom_cible</font>',': $degats_final degats (A:$score_perso,D:$score_cible,Ar:$armure_utilisee)',NOW(),'0')";
							$mysqli->query($sql);
							
							$sql = "SELECT pv_perso, x_perso, y_perso, xp_perso, pi_perso FROM perso WHERE id_perso='$id_cible'";
							$res = $mysqli->query($sql);
							$tab = $res->fetch_assoc();
							
							$pv_cible = $tab["pv_perso"];
							$x_cible = $tab["x_perso"];
							$y_cible = $tab["y_perso"];
							$xp_cible = $tab["xp_perso"];
							$pi_cible = $tab["pi_perso"];
								
							if ($pv_cible <= 0) { // il est mort
								// on l'efface de la carte
								$sql = "UPDATE $carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_cible' AND y_carte='$y_cible'";
								$mysqli->query($sql);
			
								// Calcul gains (po et xp)
								$gain_po = gain_po_mort($or_cible);
								$gain_xp = gain_xp_mort($xp_cible, $xp_perso);
								
								// perte d'xp de la cible morte
								$perte_xp_cible = floor($xp_cible / 20);
			
								// MAJ gain xp/or perso
								$sql = "UPDATE perso SET xp_perso=xp_perso+$gain_xp, pi_perso=pi_perso+$gain_xp, or_perso=or_perso+$gain_po WHERE id_perso='$id'";
								$mysqli->query($sql);
			
								// MAJ perte xp/po/stat cible
								if($pi_cible > $perte_xp_cible){
									$sql = "UPDATE perso SET or_perso=or_perso-$gain_po, xp_perso=xp_perso-$perte_xp_cible, pi_perso=pi_perso-$perte_xp_cible, nb_mort=nb_mort+1 WHERE id_perso='$id_cible'";
									$mysqli->query($sql);
								}
								else {
									$sql = "UPDATE perso SET or_perso=or_perso-$gain_po, xp_perso=xp_perso-$perte_xp_cible, pi_perso=0, nb_mort=nb_mort+1 WHERE id_perso='$id_cible'";
									$mysqli->query($sql);
								}
			
								echo "<div class=\"infoi\">Vous avez tué votre cible ! <font color=red>Félicitations.</font></div>";
								echo "<div class=\"infoi\">Vous gagnez <b>$gain_xp</b> xp et vous dépouillez son cadavre de <b>$gain_po</b> piece(s) d'or.</div>";
								
								// maj evenements
								$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id,'<font color=$couleur_clan_perso>$nom_perso</font>','a tué','$id_cible','<font color=$couleur_clan_cible>$nom_cible</font>','',NOW(),'0')";
								$mysqli->query($sql);
								
								// maj cv
								$sql = "INSERT INTO `cv` VALUES ('',$id,'<font color=$couleur_clan_perso>$nom_perso</font>','$id_cible','<font color=$couleur_clan_cible>$nom_cible</font>',NOW())";
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
							
							// gain xp esquive et ajout malus
							$sql = "UPDATE perso SET xp_perso=xp_perso+1, pi_perso=pi_perso+1, bonus_perso=bonus_perso-1 WHERE id_perso='$id_cible'";
							$mysqli->query($sql);
								
							// maj evenement
							$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_cible,'<font color=$couleur_clan_cible>$nom_cible</font>','a esquivé l\'attaque de','$id','<font color=$couleur_clan_perso>$nom_perso</font>','(A:$score_perso,D:$score_cible)',NOW(),'0')";
							$mysqli->query($sql);
			
						}
						//mise a jour des pa
						$sql = "UPDATE perso SET pa_perso=pa_perso-$coutPa_attaque WHERE id_perso='$id'";
						$res = $mysqli->query($sql); 
						?>
							<a href="jouer.php"><font color="#000000" size="1" face="Verdana, Arial, Helvetica, sans-serif">[ retour ]</font></a>
							<form action="agir.php" method="post">
							<input type="submit" name="re_attaque" value="attaquer :<?php echo $id_cible;?>">
							</form> <?php
						echo "<br><center><a href=\"jouer.php\"><font color=\"#000000\" size=\"1\" face=\"Verdana, Arial, Helvetica, sans-serif\">[ retour ]</font></a></center>";
					}
									
					else {//la cible est déjà morte
						echo "Erreur : La cible est déjà morte !";
						echo "<br><center><a href=\"jouer.php\"><font color=\"#000000\" size=\"1\" face=\"Verdana, Arial, Helvetica, sans-serif\">[ retour ]</font></a></center>";
					}
				}
			}
			else { // la cible n'est pas à portée d'attaque
				if($id_cible == $id){
					echo "Erreur : Vous ne pouvez pas vous attaquez vous même...";
					echo "<br /><center><a href=\"jouer.php\"><font color=\"#000000\" size=\"1\" face=\"Verdana, Arial, Helvetica, sans-serif\">[ retour ]</font></a></center>";
				}
				else {
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
	if ((isset($_POST["id_attaque"]) && $_POST["id_attaque"]!="" && $_POST["id_attaque"] >= 10000 && $_POST["id_attaque"] < 50000) || ( isset($_POST["re_attaque"]) && $id_c >= 10000 && $id_c < 50000) ) { 
	
		if(isset($_POST["re_attaque"]))
			$id_cible = $id_c;
		else
			$id_cible =  $_POST["id_attaque"];
		
		// Recupération de l'arme équipée
		$id_arme_equipee = id_arme_equipee($mysqli, $id);
		
		// Si il est bien équipée d'une arme
		if($id_arme_equipee) {
			
			// Recupération des caracs de l'arme
			$sql = "SELECT nom_arme, coutPa_arme, porteeMin_arme, porteeMax_arme, additionMin_degats, additionMax_degats, multiplicateurMin_degats, multiplicateurMax_degats, degatMin_arme, degatMax_arme, degatZone_arme, main FROM arme WHERE id_arme='$id_arme_equipee'";
			$res = $mysqli->query($sql);
			$t_a = $res->fetch_assoc();
			
			$nom_arme_equipee = $t_a["nom_arme"];
			$coutPa_arme_equipee = $t_a["coutPa_arme"];
			$porteeMin_arme_equipee = $t_a["porteeMin_arme"];
			$porteeMax_arme_equipee = $t_a["porteeMax_arme"];
			$additionMin_degats_equipee = $t_a["additionMin_degats"];
			$additionMax_degats_equipee = $t_a["additionMax_degats"];
			$multiplicateurMin_degats_equipee = $t_a["multiplicateurMin_degats"];
			$multiplicateurMax_degats_equipee = $t_a["multiplicateurMax_degats"];
			$degatMin_arme_equipee = $t_a["degatMin_arme"];
			$degatMax_arme_equipee = $t_a["degatMax_arme"];
			$degatZone_arme_equipee = $t_a["degatZone_arme"];
			$main_arme_equipee = $t_a["main"];
		}
		else {
			// Poings = arme de Corps a corps
			$porteeMin_arme_equipee = 1;
			$porteeMax_arme_equipee = 1;
		}
		
		// recup des données du perso
		$sql = "SELECT nom_perso, image_perso, xp_perso, x_perso, y_perso, pm_perso, pi_perso, pv_perso, pvMax_perso, pmMax_perso, pa_perso, paMax_perso, recup_perso, bonusRecup_perso, perception_perso, bonusPerception_perso, charge_perso, chargeMax_perso, dateCreation_perso, clan FROM perso WHERE id_perso='$id'";
		$res = $mysqli->query($sql);
		$t_perso = $res->fetch_assoc();
		
		$nom_perso = $t_perso["nom_perso"];
		$image_perso = $t_perso["image_perso"];
		$xp_perso = $t_perso["xp_perso"];
		$x_perso = $t_perso["x_perso"];
		$y_perso = $t_perso["y_perso"];
		$pm_perso = $t_perso["pm_perso"];
		$pmM_perso = $t_perso["pmMax_perso"];
		$pi_perso = $t_perso["pi_perso"];
		$pv_perso = $t_perso["pv_perso"];
		$pvM_perso = $t_perso["pvMax_perso"];
		$pa_perso = $t_perso["pa_perso"];
		$paM_perso = $t_perso["paMax_perso"];
		$rec_perso = $t_perso["recup_perso"];
		$br_perso = $t_perso["bonusRecup_perso"];
		$per_perso = $t_perso["perception_perso"];
		$bp_perso = $t_perso["bonusPerception_perso"];
		$ch_perso = $t_perso["charge_perso"];
		$chM_perso = $t_perso["chargeMax_perso"];
		$dc_perso = $t_perso["dateCreation_perso"];
		$clan_perso = $t_perso["clan"];
		
		if(is_a_portee_attaque($mysqli, $carte, $id, $id_cible, $porteeMin_arme_equipee, $porteeMax_arme_equipee, $per_perso)) {	
		
			if($id_arme_equipee){
				// Récupération des pa de l'attaque avec l'arme
				$coutPa_attaque = $coutPa_arme_equipee;
			}
			else {
				$coutPa_attaque='5';
			}
				
			// recuperation des données du pnj		
			$sql = "SELECT pnj.id_pnj, nom_pnj, degatMin_pnj, degatMax_pnj, pnj.id_pnj, pv_i, de_pnj, x_i, y_i, pm_pnj, pv_i, pvMax_pnj FROM pnj, instance_pnj WHERE pnj.id_pnj=instance_pnj.id_pnj AND idInstance_pnj='$id_cible'";
			$res = $mysqli->query($sql);
			$t_cible = $res->fetch_assoc();
			
			$id_pnj = $t_cible["id_pnj"];
			$nom_cible = $t_cible["nom_pnj"];
			$pv_cible = $t_cible["pv_i"];
			$deDefense_cible = $t_cible["de_pnj"];
			$degatMin = $t_cible["degatMin_pnj"];
			$degatMax = $t_cible["degatMax_pnj"];
			$x_cible = $t_cible["x_i"];
			$y_cible = $t_cible["y_i"];
			$pm_cible = $t_cible["pm_pnj"];
			$pv_cible = $t_cible["pv_i"];
			$pvMax_cible = $t_cible["pvMax_pnj"];
			$image_pnj = "Monstre".$t_cible["id_pnj"]."t.png";
			//$or_cible = $t_cible["or_perso"];
			
			// Récupération de la couleur associée au clan du perso
			$couleur_clan_perso = couleur_clan($clan_perso);
			
			// on verifie si le perso a déja tué ce type de pnj et on en récupère le nombre
			$nb_pnj_t = is_deja_tue_pnj($mysqli, $id, $id_pnj);
			
			$pa_restant = $pa_perso - $coutPa_attaque;
			if($pa_restant <= 0){
				$pa_restant = 0;
			}
			?>
			<table border=0 width=100%>
				<tr height=50%>
				<td width=50%>	
				<table border=1 height=100% width=100%>		
					<tr><td width=25%>	
					<table border=0 width=100%>
						<tr>
						<td align="center"><img src="../images_perso/<?php echo $image_perso; ?>"></td>
						</tr>
					</table>
					</td><td width=75%>
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
				</table>
				</td><td width=50%>
				<table border=1 height=100% width=100%>		
					<tr><td width=25%>	
					<table border=0 width=100%>
						<tr>
						<td align="center"><img src="../images_perso/<?php echo $image_pnj; ?>"></td>
						</tr>
					</table>
					</td><td width=75%>
					<table border=0 width=100%>
					<tr>
						<td><?php echo "<u><b>Pseudo :</b></u> ".$nom_cible.""; ?></td>
					<tr>
					<tr>
						<td><?php echo "<u><b>Position sur la carte :</b></u> ".$x_cible."/".$y_cible; ?></td>
					<tr>
					<tr>
						<td><?php echo "<u><b>Nombre de dés :</b></u> ".$deDefense_cible.""; ?></td>
					<tr>
					<tr>
						<td><?php echo "<u><b>degats min :</b></u> ".$degatMin." - <b><u>Degats max :</u></b> ".$degatMax; ?></td>
					<tr>
	
					<tr>
						<td><?php echo "<u><b>Mouvements :</b></u> ".$pm_cible; ?><?php if($nb_pnj_t){echo " - <u><b>Points de vie max :</b></u> ".$pvMax_cible;}else{echo " - <u><b>Points de vie :</b></u> ???";} ?></td>
					<tr>	
					</table>
					</td>
				</table>
				</td></tr>
			</table>
			<?php					
			if ($pa_perso < $coutPa_attaque) { //le perso n'a pas assez de pa pour faire cette attaque
				echo "<div class=\"erreur\" align=\"center\">Vous n'avez pas assez de pa pour effectuer cette action !</div>";
				echo "<a href=\"jouer.php\"><font color=\"#000000\" size=\"1\" face=\"Verdana, Arial, Helvetica, sans-serif\">[ retour ]</font></a>";
			}
					
			else { //le perso a assez de pa
					
				if ($pv_cible > 0) { //la cible est encore en vie
							
					echo "Vous avez lancé une attaque sur <b>$nom_cible [$id_cible]</b><br>";
					
					// TODO
					$score_perso = 0;
					$score_cible = 0;
					
					echo "Votre score d'attaque :<b></b><br>";
					echo "Son score de defense :<b></b><br>";
					
					// mise a jour de dernier attaquant du pnj
					$sql = "UPDATE instance_pnj SET dernierAttaquant_i='$id' WHERE idInstance_pnj='$id_cible'";
					$mysqli->query($sql);
					
					if ($score_cible <= $score_perso) { //la cible est touchée
								
						$gain_xp = 2;
		
						// Si le perso est équipée d'une arme => On prend les dégats de l'arme
						if($id_arme_equipee){						
							// Calcul des degats de l'arme
							if($degatMin_arme_equipee && $degatMax_arme_equipee){
								$min_degats_arme = $degatMin_arme_equipee;
								$max_degats_arme = $degatMax_arme_equipee;
							}
							else {
								$min_degats_arme = $degats_perso * $multiplicateurMin_degats_equipee + $additionMin_degats_equipee;
								$max_degats_arme = $degats_perso * $multiplicateurMax_degats_equipee + $additionMax_degats_equipee;
							}
							
							$degats = rand($min_degats_arme, $max_degats_arme);
							
						}
						
						// mise a jour des pv du pnj
						$sql = "UPDATE instance_pnj SET pv_i=pv_i-$degats WHERE idInstance_pnj='$id_cible'";
						$mysqli->query($sql);
						echo "<br>Vous avez infligé <b>$degats</b> dégâts à la cible.<br><br>";
						echo "Vous avez gagné <b>$gain_xp</b> xp<br>.";
						
						// maj gain xp perso
						$sql = "UPDATE perso SET xp_perso=xp_perso+$gain_xp, pi_perso=pi_perso+$gain_xp WHERE id_perso='$id'"; //mise a jour des xp/pi/pc
						$mysqli->query($sql);
						
						// maj evenement
						$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id,'<font color=$couleur_clan_perso>$nom_perso</font>','a attaqué ','$id_cible','$nom_cible',': $degats degats (A:$score_perso,D:$score_cible)',NOW(),'0')";
						$mysqli->query($sql);					
						
						// recuperation des données du pnj aprés attaque
						$sql = "SELECT id_pnj, pv_i, x_i, y_i FROM instance_pnj WHERE idInstance_pnj='$id_cible'";
						$res = $mysqli->query($sql);
						$tab = $res->fetch_assoc();
						
						$pv_cible = $tab["pv_i"];
						$x_cible = $tab["x_i"];
						$y_cible = $tab["y_i"];
						$id_pnj = $tab["id_pnj"];
							
						$gain_obj="";
							
						if ($pv_cible <= 0) { // il est mort
						
							//verifier si le pnj possede un objet
							$sql = "SELECT id_objet FROM pnj_as_objet WHERE idInstance_pnj='$id_cible'";
							$res = $mysqli->query($sql);
							$verif_o = $res->num_rows;
							
							if($verif_o) { // le pnj possede un(des) objet(s)
								$gain_obj.="Vous avez recuperé les objets suivant sur le cadavre du pnj :<br>";
							
								while ($t_obj = $res->fetch_assoc()){
									// recuperation de l'id de l'objet que possede le pnj
									$id_obj = $t_obj["id_objet"];
									
									// le perso gagne tous les objets que possedait le pnj
									$sql = "INSERT INTO perso_as_objet VALUES('$id','$id_obj')";
									$mysqli->query($sql);
									
									// recuperation du nom de l'objet acquis
									$sql1 = "SELECT nom_objet, poids_objet FROM objet WHERE id_objet='$id_obj'";
									$res1 = $mysqli->query($sql1);
									$t_nom_o = $res1->fetch_assoc();
									$nom_obj = $t_nom_o["nom_objet"];
									$poids_objet = $t_nom_o['poids_objet'];
									
									//maj charge perso
									$sql = "UPDATE perso SET charge_perso=charge_perso+$poids_objet WHERE id_perso='$id'";
									$mysqli->query($sql);
									
									$gain_obj.="* ".$nom_obj."<br>";
								}
							}
							
							// si le pnj est un slime
							if($id_pnj == 1){ 
								// on a une chance sur 4 de trouver une potion de slime
								if (chance_objet(25)){ // 25% de chance
									// gain de l'objet potion slime
									$sql = "INSERT INTO perso_as_objet VALUES ('$id','1')";
									$mysqli->query($sql);
									
									//maj charge perso
									$sql = "UPDATE perso SET charge_perso=charge_perso+0.2 WHERE id_perso='$id'";
									$mysqli->query($sql);
									
									if($verif_o)
										$gain_obj.="* potion de slime<br>";
									else
										$gain_obj.="Vous avez recuperé les objets suivant sur le cadavre du pnj :<br>* potion de slime<br>";
								}
							}
							
							// si le pnj est un cyclope
							if($id_pnj == 10){
								// on a une chance sur 10 de récupérer son oeil
								if (chance_objet(10)){
									// gain de l'objet potion slime
									$sql = "INSERT INTO perso_as_objet VALUES ('$id','10')";
									$mysqli->query($sql);
									
									//maj charge perso
									$sql = "UPDATE perso SET charge_perso=charge_perso+0.2 WHERE id_perso='$id'";
									$mysqli->query($sql);
									
									if($verif_o)
										$gain_obj.="* oeil de cyclope<br>";
									else
										$gain_obj.="Vous avez recuperé les objets suivant sur le cadavre du pnj :<br>* oeil de cyclope<br>";
								}
							}
						
							// on l'efface de la carte
							$sql = "UPDATE $carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_cible' AND y_carte='$y_cible'";
							$mysqli->query($sql);
							
							// on le delete
							$sql = "DELETE FROM instance_pnj WHERE idInstance_pnj='$id_cible'";
							$mysqli->query($sql);
							
							srand((double) microtime() * 1000000);
							$gain_po = rand(1,20);
							
							switch($id_pnj){
								case(1):
									srand((double) microtime() * 1000000);
									$gain_xp = rand(1,3); // gain xp
									if($gain_xp <= 0){
										$gain_xp = 1;
									}
			
									// gain xp/or/stat perso
									$sql = "UPDATE perso SET xp_perso=xp_perso+$gain_xp, pi_perso=pi_perso+$gain_xp, or_perso=or_perso+$gain_po, nb_pnj=nb_pnj+1 WHERE id_perso='$id'";
									$mysqli->query($sql);
				
									echo "Vous avez tué votre cible ! <font color=red>Félicitations.</font>";
									
									// verification que le perso n'a pas déjà tué ce type de pnj
									$sql_v = "SELECT id_pnj FROM perso_as_killpnj WHERE id_pnj='$id_pnj' AND id_perso='$id'";
									$res_v = $mysqli->query($sql_v);
									$verif_pnj = $res_v->num_rows;
									
									// il n'a jamais tué de pnj de ce type
									if($verif_pnj == 0){
										$sql = "INSERT INTO perso_as_killpnj VALUES('$id','$id_pnj','1')";
										$mysqli->query($sql);
									}
									else { // il en a déjà tué
										$sql = "UPDATE perso_as_killpnj SET nb_pnj=nb_pnj+1 WHERE id_perso='$id' AND id_pnj='$id_pnj'";
										$mysqli->query($sql);
									}								
									break;
								
								case(2):
									srand((double) microtime() * 1000000);
									$gain_xp = rand(2,8); // gain xp
									if($gain_xp <= 0){
										$gain_xp = 1;
									}
			
									// gain xp/or/stat perso
									$sql = "UPDATE perso SET xp_perso=xp_perso+$gain_xp, pi_perso=pi_perso+$gain_xp, or_perso=or_perso+$gain_po, nb_pnj=nb_pnj+1 WHERE id_perso='$id'";
									$mysqli->query($sql);
				
									echo "Vous avez tué votre cible ! <font color=red>Félicitations.</font>";
									
									// verification que le perso n'a pas déjà tué ce type de pnj
									$sql_v = "SELECT id_pnj FROM perso_as_killpnj WHERE id_pnj='$id_pnj' AND id_perso='$id'";
									$res_v = $mysqli->query($sql_v);
									$verif_pnj = $res_v->num_rows;
									
									// il n'a jamais tué de pnj de ce type
									if($verif_pnj == 0){
										$sql = "INSERT INTO perso_as_killpnj VALUES('$id','$id_pnj','1')";
										$mysqli->query($sql);
									}
									else { // il en a déjà tué
										$sql = "UPDATE perso_as_killpnj SET nb_pnj=nb_pnj+1 WHERE id_perso='$id' AND id_pnj='$id_pnj'";
										$mysqli->query($sql);
									}
									
									break;
									
								case(3):
									srand((double) microtime() * 1000000);
									$gain_xp = rand(10,40); // gain xp
									if($gain_xp <= 0){
										$gain_xp = 1;
									}
			
									// gain xp/or/stat perso
									$sql = "UPDATE perso SET xp_perso=xp_perso+$gain_xp, pi_perso=pi_perso+$gain_xp, or_perso=or_perso+$gain_po, nb_pnj=nb_pnj+1 WHERE id_perso='$id'";
									$mysqli->query($sql);
				
									echo "Vous avez tué votre cible ! <font color=red>Félicitations.</font>";
									
									// verification que le perso n'a pas déjà tué ce type de pnj
									$sql_v = "SELECT id_pnj FROM perso_as_killpnj WHERE id_pnj='$id_pnj' AND id_perso='$id'";
									$res_v = $mysqli->query($sql_v);
									$verif_pnj = $res_v->num_rows;
									
									// il n'a jamais tué de pnj de ce type
									if($verif_pnj == 0){
										$sql = "INSERT INTO perso_as_killpnj VALUES('$id','$id_pnj','1')";
										$mysqli->query($sql);
									}
									else { // il en a déjà tué
										$sql = "UPDATE perso_as_killpnj SET nb_pnj=nb_pnj+1 WHERE id_perso='$id' AND id_pnj='$id_pnj'";
										$mysqli->query($sql);
									}
									
									break;
								
								case(4):
									srand((double) microtime() * 1000000);
									$gain_xp = rand(8,40); // gain xp
									if($gain_xp <= 0){
										$gain_xp = 1;
									}
			
									// gain xp/or/stat perso
									$sql = "UPDATE perso SET xp_perso=xp_perso+$gain_xp, pi_perso=pi_perso+$gain_xp, or_perso=or_perso+$gain_po, nb_pnj=nb_pnj+1 WHERE id_perso='$id'";
									$mysqli->query($sql);
				
									echo "Vous avez tué votre cible ! <font color=red>Félicitations.</font>";
									
									// verification que le perso n'a pas déjà tué ce type de pnj
									$sql_v = "SELECT id_pnj FROM perso_as_killpnj WHERE id_pnj='$id_pnj' AND id_perso='$id'";
									$res_v = $mysqli->query($sql_v);
									$verif_pnj = $res_v->num_rows;
									
									// il n'a jamais tué de pnj de ce type
									if($verif_pnj == 0){
										$sql = "INSERT INTO perso_as_killpnj VALUES('$id','$id_pnj','1')";
										$mysqli->query($sql);
									}
									else { // il en a déjà tué
										$sql = "UPDATE perso_as_killpnj SET nb_pnj=nb_pnj+1 WHERE id_perso='$id' AND id_pnj='$id_pnj'";
										$mysqli->query($sql);
									}
									
									break;
									
								case(5):
									srand((double) microtime() * 1000000);
									$gain_xp = rand(1,5); // gain xp
									if($gain_xp <= 0){
										$gain_xp = 1;
									}
			
									// gain xp/or/stat perso
									$sql = "UPDATE perso SET xp_perso=xp_perso+$gain_xp, pi_perso=pi_perso+$gain_xp, or_perso=or_perso+$gain_po, nb_pnj=nb_pnj+1 WHERE id_perso='$id'";
									$mysqli->query($sql);
				
									echo "Vous avez tué votre cible ! <font color=red>Félicitations.</font>";
									
									// verification que le perso n'a pas déjà tué ce type de pnj
									$sql_v = "SELECT id_pnj FROM perso_as_killpnj WHERE id_pnj='$id_pnj' AND id_perso='$id'";
									$res_v = $mysqli->query($sql_v);
									$verif_pnj = $res_v->num_rows;
									
									// il n'a jamais tué de pnj de ce type
									if($verif_pnj == 0){
										$sql = "INSERT INTO perso_as_killpnj VALUES('$id','$id_pnj','1')";
										$mysqli->query($sql);
									}
									else { // il en a déjà tué
										$sql = "UPDATE perso_as_killpnj SET nb_pnj=nb_pnj+1 WHERE id_perso='$id' AND id_pnj='$id_pnj'";
										$mysqli->query($sql);
									}
									
									break;
									
								case(6):
									srand((double) microtime() * 1000000);
									$gain_xp = rand(5,15); // gain xp
									if($gain_xp <= 0){
										$gain_xp = 1;
									}
			
									// gain xp/or/stat perso
									$sql = "UPDATE perso SET xp_perso=xp_perso+$gain_xp, pi_perso=pi_perso+$gain_xp, or_perso=or_perso+$gain_po, nb_pnj=nb_pnj+1 WHERE id_perso='$id'";
									$mysqli->query($sql);
				
									echo "Vous avez tué votre cible ! <font color=red>Félicitations.</font>";
									
									// verification que le perso n'a pas déjà tué ce type de pnj
									$sql_v = "SELECT id_pnj FROM perso_as_killpnj WHERE id_pnj='$id_pnj' AND id_perso='$id'";
									$res_v = $mysqli->query($sql_v);
									$verif_pnj = $res_v->num_rows;
									
									// il n'a jamais tué de pnj de ce type
									if($verif_pnj == 0){
										$sql = "INSERT INTO perso_as_killpnj VALUES('$id','$id_pnj','1')";
										$mysqli->query($sql);
									}
									else { // il en a déjà tué
										$sql = "UPDATE perso_as_killpnj SET nb_pnj=nb_pnj+1 WHERE id_perso='$id' AND id_pnj='$id_pnj'";
										$mysqli->query($sql);
									}
									
									break;
									
								case(7):
									srand((double) microtime() * 1000000);
									$gain_xp = rand(4,12); // gain xp
									if($gain_xp <= 0){
										$gain_xp = 1;
									}
			
									// gain xp/or/stat perso
									$sql = "UPDATE perso SET xp_perso=xp_perso+$gain_xp, pi_perso=pi_perso+$gain_xp, or_perso=or_perso+$gain_po, nb_pnj=nb_pnj+1 WHERE id_perso='$id'";
									$mysqli->query($sql);
				
									echo "Vous avez tué votre cible ! <font color=red>Félicitations.</font>";
									
									// verification que le perso n'a pas déjà tué ce type de pnj
									$sql_v = "SELECT id_pnj FROM perso_as_killpnj WHERE id_pnj='$id_pnj' AND id_perso='$id'";
									$res_v = $mysqli->query($sql_v);
									$verif_pnj = $res_v->num_rows;
									
									// il n'a jamais tué de pnj de ce type
									if($verif_pnj == 0){
										$sql = "INSERT INTO perso_as_killpnj VALUES('$id','$id_pnj','1')";
										$mysqli->query($sql);
									}
									else { // il en a déjà tué
										$sql = "UPDATE perso_as_killpnj SET nb_pnj=nb_pnj+1 WHERE id_perso='$id' AND id_pnj='$id_pnj'";
										$mysqli->query($sql);
									}
									
									break;
									
								case(8):
									srand((double) microtime() * 1000000);
									$gain_xp = rand(3,10); // gain xp
									if($gain_xp <= 0){
										$gain_xp = 1;
									}
			
									// gain xp/or/stat perso
									$sql = "UPDATE perso SET xp_perso=xp_perso+$gain_xp, pi_perso=pi_perso+$gain_xp, or_perso=or_perso+$gain_po, nb_pnj=nb_pnj+1 WHERE id_perso='$id'";
									$mysqli->query($sql);
				
									echo "Vous avez tué votre cible ! <font color=red>Félicitations.</font>";
									
									// verification que le perso n'a pas déjà tué ce type de pnj
									$sql_v = "SELECT id_pnj FROM perso_as_killpnj WHERE id_pnj='$id_pnj' AND id_perso='$id'";
									$res_v = $mysqli->query($sql_v);
									$verif_pnj = $res_v->num_rows;
									
									// il n'a jamais tué de pnj de ce type
									if($verif_pnj == 0){
										$sql = "INSERT INTO perso_as_killpnj VALUES('$id','$id_pnj','1')";
										$mysqli->query($sql);
									}
									else { // il en a déjà tué
										$sql = "UPDATE perso_as_killpnj SET nb_pnj=nb_pnj+1 WHERE id_perso='$id' AND id_pnj='$id_pnj'";
										$mysqli->query($sql);
									}
									
									break;
									
								case(9):
									srand((double) microtime() * 1000000);
									$gain_xp = rand(1,5); // gain xp
									if($gain_xp <= 0){
										$gain_xp = 1;
									}
			
									// gain xp/or/stat perso
									$sql = "UPDATE perso SET xp_perso=xp_perso+$gain_xp, pi_perso=pi_perso+$gain_xp, or_perso=or_perso+$gain_po, nb_pnj=nb_pnj+1 WHERE id_perso='$id'";
									$mysqli->query($sql);
				
									echo "Vous avez tué votre cible ! <font color=red>Félicitations.</font>";
									
									// verification que le perso n'a pas déjà tué ce type de pnj
									$sql_v = "SELECT id_pnj FROM perso_as_killpnj WHERE id_pnj='$id_pnj' AND id_perso='$id'";
									$res_v = $mysqli->query($sql_v);
									$verif_pnj = $res_v->num_rows;
									
									// il n'a jamais tué de pnj de ce type
									if($verif_pnj == 0){
										$sql = "INSERT INTO perso_as_killpnj VALUES('$id','$id_pnj','1')";
										$mysqli->query($sql);
									}
									else { // il en a déjà tué
										$sql = "UPDATE perso_as_killpnj SET nb_pnj=nb_pnj+1 WHERE id_perso='$id' AND id_pnj='$id_pnj'";
										$mysqli->query($sql);
									}
									
									break;
								default :
									srand((double) microtime() * 1000000);
									$gain_xp = rand(1,10); // gain xp
									if($gain_xp <= 0){
										$gain_xp = 1;
									}
			
									// gain xp/or/stat perso
									$sql = "UPDATE perso SET xp_perso=xp_perso+$gain_xp, pi_perso=pi_perso+$gain_xp, or_perso=or_perso+$gain_po, nb_pnj=nb_pnj+1 WHERE id_perso='$id'";
									$mysqli->query($sql);
				
									echo "Vous avez tué votre cible ! <font color=red>Félicitations.</font>";
									
									// verification que le perso n'a pas déjà tué ce type de pnj
									$sql_v = "SELECT id_pnj FROM perso_as_killpnj WHERE id_pnj='$id_pnj' AND id_perso='$id'";
									$res_v = $mysqli->query($sql_v);
									$verif_pnj = $res_v->num_rows;
									
									// il n'a jamais tué de pnj de ce type
									if($verif_pnj == 0){
										$sql = "INSERT INTO perso_as_killpnj VALUES('$id','$id_pnj','1')";
										$mysqli->query($sql);
									}
									else { // il en a déjà tué
										$sql = "UPDATE perso_as_killpnj SET nb_pnj=nb_pnj+1 WHERE id_perso='$id' AND id_pnj='$id_pnj'";
										$mysqli->query($sql);
									}
									
									break;
							}
							
							echo "Vous gagnez <b>$gain_xp</b> xp et vous dépouillez son cadavre de <b>$gain_po</b> piece(s) d'or.<br/>";
							echo $gain_obj."<br/>";
							
							// maj evenement
							$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id,'<font color=$couleur_clan_perso>$nom_perso</font>','a tué','$id_cible','$nom_cible','',NOW(),'0')";
							$mysqli->query($sql);
							
							// maj cv
							$sql = "INSERT INTO `cv` VALUES ('',$id,'<font color=$couleur_clan_perso>$nom_perso</font>','$id_cible','$nom_cible',NOW())"; //mise a jour de la table cv
							$mysqli->query($sql);
							
							echo "<br><center><a href=\"jouer.php\"><font color=\"#000000\" size=\"1\" face=\"Verdana, Arial, Helvetica, sans-serif\">[ retour ]</font></a></center>";
						}
						
					}
					else { // la cible a esquivé l'attaque
		
						echo "<br>Vous avez raté votre cible.<br><br>";
							
						// maj evenement
						$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_cible,'$nom_cible','a esquivé l\'attaque de','$id','<font color=$couleur_clan_perso>$nom_perso</font>','(A:$score_perso,D:$score_cible)',NOW(),'0')";
						$mysqli->query($sql);
		
					}
					//mise à jour des pa
					$sql = "UPDATE perso SET pa_perso=pa_perso-$coutPa_attaque WHERE id_perso='$id'";
					$res = $mysqli->query($sql); 
					?>
						<form action="agir.php" method="post">
						<input type="submit" name="re_attaque" value="attaquer :<?php echo $id_cible;?>">
						</form> <?php
					echo "<br/><center><a href=\"jouer.php\"><font color=\"#000000\" size=\"1\" face=\"Verdana, Arial, Helvetica, sans-serif\">[ retour ]</font></a></center>";
				}
								
				else {//la cible est déjà morte
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
	if ((isset($_POST["id_attaque"]) && $_POST["id_attaque"]!="" && $_POST["id_attaque"] >= 50000) || (isset($_POST["re_attaque"]) && $id_c >= 50000) ) {
		
		if(isset($_POST["re_attaque"]))
			$id_cible = $id_c;
		else
			$id_cible =  $_POST["id_attaque"];
		
		// Recupération de l'arme équipée
		$id_arme_equipee = id_arme_equipee($mysqli, $id);
		
		// Si il est bien équipée d'une arme
		if($id_arme_equipee) {
			
			// Recupération des caracs de l'arme
			$sql = "SELECT nom_arme, coutPa_arme, porteeMin_arme, porteeMax_arme, additionMin_degats, additionMax_degats, multiplicateurMin_degats, multiplicateurMax_degats, degatMin_arme, degatMax_arme, degatZone_arme, main FROM arme WHERE id_arme='$id_arme_equipee'";
			$res = $mysqli->query($sql);
			$t_a = $res->fetch_assoc();
			
			$nom_arme_equipee = $t_a["nom_arme"];
			$coutPa_arme_equipee = $t_a["coutPa_arme"];
			$porteeMin_arme_equipee = $t_a["porteeMin_arme"];
			$porteeMax_arme_equipee = $t_a["porteeMax_arme"];
			$additionMin_degats_equipee = $t_a["additionMin_degats"];
			$additionMax_degats_equipee = $t_a["additionMax_degats"];
			$multiplicateurMin_degats_equipee = $t_a["multiplicateurMin_degats"];
			$multiplicateurMax_degats_equipee = $t_a["multiplicateurMax_degats"];
			$degatMin_arme_equipee = $t_a["degatMin_arme"];
			$degatMax_arme_equipee = $t_a["degatMax_arme"];
			$degatZone_arme_equipee = $t_a["degatZone_arme"];
			$main_arme_equipee = $t_a["main"];
		}
		else {
			// Poings = arme de Corps à corps
			$porteeMin_arme_equipee = 1;
			$porteeMax_arme_equipee = 1;
		}
		
		// recup des données du perso
		$sql = "SELECT nom_perso, image_perso, xp_perso, x_perso, y_perso, pm_perso, pi_perso, pv_perso, pvMax_perso, pmMax_perso, pa_perso, paMax_perso, recup_perso, bonusRecup_perso, perception_perso, bonusPerception_perso, charge_perso, chargeMax_perso, dateCreation_perso, clan FROM perso WHERE id_perso='$id'";
		$res = $mysqli->query($sql);
		$t_perso = $res->fetch_assoc();
		
		$nom_perso = $t_perso["nom_perso"];
		$image_perso = $t_perso["image_perso"];
		$xp_perso = $t_perso["xp_perso"];
		$x_perso = $t_perso["x_perso"];
		$y_perso = $t_perso["y_perso"];
		$pm_perso = $t_perso["pm_perso"];
		$pmM_perso = $t_perso["pmMax_perso"];
		$pi_perso = $t_perso["pi_perso"];
		$pv_perso = $t_perso["pv_perso"];
		$pvM_perso = $t_perso["pvMax_perso"];
		$pa_perso = $t_perso["pa_perso"];
		$paM_perso = $t_perso["paMax_perso"];
		$rec_perso = $t_perso["recup_perso"];
		$br_perso = $t_perso["bonusRecup_perso"];
		$per_perso = $t_perso["perception_perso"];
		$bp_perso = $t_perso["bonusPerception_perso"];
		$ch_perso = $t_perso["charge_perso"];
		$chM_perso = $t_perso["chargeMax_perso"];
		$dc_perso = $t_perso["dateCreation_perso"];
		$clan_perso = $t_perso["clan"];
		
		if(is_a_portee_attaque($mysqli, $carte, $id, $id_cible, $porteeMin_arme_equipee, $porteeMax_arme_equipee, $per_perso)) {	
		
			$coutPa_attaque='5';
					
			// recuperation des données du batiment	
			$sql = "SELECT batiment.id_batiment, nom_batiment, description, nom_instance, pv_instance, pvMax_instance, x_instance, y_instance, camp_instance, contenance_instance 
					FROM batiment, instance_batiment
					WHERE batiment.id_batiment=instance_batiment.id_batiment
					AND id_instanceBat=$id_cible";
			$res = $mysqli->query($sql);
			$bat = $res->fetch_assoc();
			
			$id_batiment = $bat['id_batiment'];
			$nom_batiment = $bat['nom_batiment'];
			$description_batiment = $bat['description'];
			$nom_instance_batiment = $bat['nom_instance'];
			$pv_instance = $bat['pv_instance'];
			$pvMax_instance = $bat['pvMax_instance'];
			$x_instance = $bat['x_instance'];
			$y_instance = $bat['y_instance'];
			$camp_instance = $bat['camp_instance'];
			$contenance_instance = $bat['contenance_instance'];
			
			if($camp_instance == '1'){
				$camp_bat = 'b';
				$couleur_bat = 'blue';
			}
			if($camp_instance == '2'){
				$camp_bat = 'r';
				$couleur_bat = 'red';
			}
			
			$image_bat = "b".$id_batiment."".$camp_bat.".png";
			
			// Récupération de la couleur associée au clan du perso
			$couleur_clan_perso = couleur_clan($clan_perso);
			
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
											<td align="center"><img src="../images_perso/<?php echo $image_perso; ?>"></td>
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
			if ($pa_perso < $coutPa_attaque) { //le perso n'a pas assez de pa pour faire cette attaque
				echo "<div class=\"erreur\" align=\"center\">Vous n'avez pas assez de pa pour effectuer cette action !</div>";
				echo "<a href=\"jouer.php\"><font color=\"#000000\" size=\"1\" face=\"Verdana, Arial, Helvetica, sans-serif\">[ retour ]</font></a>";
			}
			else { //le perso a assez de pa
					
				if ($pv_instance > 0) { //la cible est encore en vie
							
					echo "Vous avez lancé une attaque sur <b>$nom_batiment [$id_cible]</b>.<br>";
					
					$gain_xp = 2;
		
					// Si le perso est équipée d'une arme => On prend les dégats de l'arme
					if($id_arme_equipee){
						
						// Calcul des degats de l'arme
						if($degatMin_arme_equipee && $degatMax_arme_equipee){
							$min_degats_arme = $degatMin_arme_equipee;
							$max_degats_arme = $degatMax_arme_equipee;
						}
						else {
							$min_degats_arme = $degats_perso * $multiplicateurMin_degats_equipee + $additionMin_degats_equipee;
							$max_degats_arme = $degats_perso * $multiplicateurMax_degats_equipee + $additionMax_degats_equipee;
						}
						
						$degats = rand($min_degats_arme, $max_degats_arme);
						
					} else {
						$degats = 1;
					}
						
					// mise à jour des pv du pnj
					$sql = "UPDATE instance_batiment SET pv_instance=pv_instance-$degats WHERE id_instanceBat='$id_cible'";
					$mysqli->query($sql);
					echo "<br>Vous avez infligé <b>$degats</b> degats à la cible.<br><br>";
					echo "Vous avez gagné <b>$gain_xp</b> xp<br>";
						
					// maj gain xp, pi perso
					$sql = "UPDATE perso SET xp_perso=xp_perso+$gain_xp, pi_perso=pi_perso+$gain_xp WHERE id_perso='$id'";
					$mysqli->query($sql);
						
					// maj evenement
					$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id,'<font color=$couleur_clan_perso>$nom_perso</font>','a attaqué ','$id_cible','<font color=$couleur_bat>$nom_batiment</font>',': $degats degats',NOW(),'0')";
					$mysqli->query($sql);					
						
					// recuperation des données du batiment aprés attaque
					$sql = "SELECT id_batiment, pv_instance, x_instance, y_instance FROM instance_batiment WHERE id_instanceBat='$id_cible'";
					$res = $mysqli->query($sql);
					$tab = $res->fetch_assoc();
					
					$pv_cible = $tab["pv_instance"];
					$x_cible = $tab["x_instance"];
					$y_cible = $tab["y_instance"];
					$id_batiment = $tab["id_batiment"];
							
					$gain_obj="";
					
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
							$sql_p = "UPDATE perso SET pv_perso=pv_perso-$perte_pv WHERE id_perso='$id_p'";
							$mysqli->query($sql_p);
							
							// recup des infos du perso
							$sql_i = "SELECT nom_perso, pv_perso, image_perso, clan FROM perso WHERE id_perso='$id_p'";
							$res_i = $mysqli->query($sql_i);
							$t_i = $res_i->fetch_assoc();
							$nom_p = $t_i["nom_perso"];
							$pv_p = $t_i["pv_perso"];
							$image_p = $t_i["image_perso"];
							$clan_p = $t_i["clan"];
							
							// Récupération de la couleur associée au clan du perso
							$couleur_clan_p = couleur_clan($clan_p);
							
							// maj evenement
							$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ('$id_p','<font color=$couleur_clan_p>$nom_p</font>','a été bléssé suite à la destruction du bâtiment',NULL,'',' : - $perte_pv',NOW(),'0')";
							$mysqli->query($sql);
							
							// Le perso est encore vivant
							if($pv_p > 0){
								// Traitement répartissement des persos sur la carte
								$occup = 1;
								while ($occup == 1)
								{
									$x = pos_zone_rand_x($x_cible-5,$x_cible+5); 
									$y = pos_zone_rand_y($y_cible-5,$y_cible+5);
									$occup = verif_pos_libre($x,$y);
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
								$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id,'<font color=$couleur_clan_perso>$nom_perso</font>','a tué','$id_p','<font color=$couleur_clan_p>$nom_p</font>',' : mort suite à l\'explosion du bâtiment $id_cible',NOW(),'0')";
								$mysqli->query($sql);
									
								// maj cv
								$sql = "INSERT INTO `cv` VALUES ('',$id,'<font color=$couleur_clan_perso>$nom_perso</font>','$id_p','<font color=$couleur_clan_p>$nom_p</font>',NOW())"; 
								$mysqli->query($sql);
								
							}
						}
						// on supprime les persos du batiment
						$sql = "DELETE FROM perso_in_batiment WHERE id_instanceBat='$id_cible'";
						$mysqli->query($sql);
						
						// on delete le bâtiment
						$sql = "DELETE FROM instance_batiment WHERE id_instanceBat='$id_cible'";
						$mysqli->query($sql);
						
						$gain_xp = '2';
						$gain_po = '10'; //-- TODO -- Adapter gain or selon batiment
						
						// gain xp/or/stat perso
						$sql = "UPDATE perso SET xp_perso=xp_perso+$gain_xp, pi_perso=pi_perso+$gain_xp, or_perso=or_perso+$gain_po WHERE id_perso='$id'";
						$mysqli->query($sql);
				
						echo "Vous avez détruit votre cible ! <font color=red>Félicitations.</font>";
						
						echo "Vous gagnez <b>$gain_xp</b> xp et vous récupérez dans les décombres <b>$gain_po</b> piece(s) d'or.<br/>";
						echo $gain_obj."<br/>";
							
						// maj evenement
						$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id,'<font color=$couleur_clan_perso>$nom_perso</font>','a détruit','$id_cible','<font color=$couleur_bat>$nom_batiment</font>','',NOW(),'0')";
						$mysqli->query($sql);
							
						// maj cv
						$sql = "INSERT INTO `cv` VALUES ('',$id,'<font color=$couleur_clan_perso>$nom_perso</font>','$id_cible','<font color=$couleur_bat>$nom_batiment</font>',NOW())"; 
						$mysqli->query($sql);
							
						echo "<br><center><a href=\"jouer.php\"><font color=\"#000000\" size=\"1\" face=\"Verdana, Arial, Helvetica, sans-serif\">[ retour ]</font></a></center>";
					}
					/* Fin du traitement de la destruction du bâtiment*/
					
					//mise à jour des pa
					$sql = "UPDATE perso SET pa_perso=pa_perso-$coutPa_attaque WHERE id_perso='$id'";
					$res = $mysqli->query($sql); 
					?>
						<form action="agir.php" method="post">
						<input type="submit" name="re_attaque" value="attaquer :<?php echo $id_cible;?>">
						</form> <?php
					echo "<br/><center><a href=\"jouer.php\"><font color=\"#000000\" size=\"1\" face=\"Verdana, Arial, Helvetica, sans-serif\">[ retour ]</font></a></center>";
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
		
	if ((!isset($_POST["id_attaque"]) || $_POST["id_attaque"]=="") && !isset($_POST["re_attaque"])){
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
