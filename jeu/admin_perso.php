<?php
session_start();
require_once("../fonctions.php");
require_once("f_carte.php");
require_once("f_combat.php");
require_once("f_action.php");

$mysqli = db_connexion();

include ('../nb_online.php');

if(isset($_SESSION["id_perso"])){
	
	$id_perso = $_SESSION['id_perso'];
	
	// recupération config jeu
	$admin = admin_perso($mysqli, $id_perso);
	
	if($admin){
		
		$mess_err 	= "";
		$mess 		= "";
		
		if (isset($_POST['matricule_pendre_hidden'])) {
			
			$id_perso_pendre = $_POST['matricule_pendre_hidden'];

			$sql = "UPDATE joueur SET pendu=1 WHERE id_joueur=(SELECT idJoueur_perso FROM perso WHERE id_perso='$id_perso_pendre')";
			$mysqli->query($sql);
			
			// Récupération de tous les persos
			$sql = "SELECT id_perso, nom_perso, type_perso, clan FROM perso WHERE perso.idJoueur_perso = (SELECT perso.idJoueur_perso FROM perso WHERE id_perso='$id_perso_pendre')";
			$res = $mysqli->query($sql);
			
			while ($t = $res->fetch_assoc()) {
				
				$id_perso_a_pendre 		= $t['id_perso'];
				$nom_perso_a_pendre		= $t['nom_perso'];
				$type_perso_a_pendre	= $t['type_perso'];
				$clan	= $t['clan'];

				$couleur_clan_perso = couleur_clan($clan);
				
				if ($type_perso_a_pendre == 1) {
					
					$raison_pendaison = "";
					
					if (isset($_POST['raison_pendaison'])) {
						$raison_pendaison = addslashes($_POST['raison_pendaison']);
					}
					
					// Insertion log pendaison
					$sql_log = "INSERT INTO log_pendaison (date_pendaison, id_perso, nom_perso, raison_pendaison) VALUES (NOW(), '$id_perso_a_pendre', '$nom_perso_a_pendre', '$raison_pendaison')";
					$mysqli->query($sql_log);
				}
				
				$sql_c = "SELECT id_compagnie FROM perso_in_compagnie WHERE id_perso='$id_perso_a_pendre'";
				$res_c = $mysqli->query($sql_c);
				$t_c = $res_c->fetch_assoc();
				
				$id_compagnie = $t_c['id_compagnie'];
				
				if ($id_compagnie != null && $id_compagnie > 0) {
				
					// On regarde si le perso n'a pas de dette dans une banque de compagnie
					$sql_b = "SELECT SUM(montant) as thune_en_banque FROM histobanque_compagnie 
							WHERE id_perso='$id_perso_a_pendre' 
							AND id_compagnie='$id_compagnie'";
					$res_b = $mysqli->query($sql_b);
					$tab = $res_b->fetch_assoc();
					
					$thune_en_banque = $tab["thune_en_banque"];
				}
				else {
					$thune_en_banque = 0;
				}
				
				// Ok - renvoi du perso						
				// maj cv
				$sql = "INSERT INTO `cv` (IDActeur_cv, nomActeur_cv, gradeActeur_cv, IDCible_cv, nomCible_cv, gradeCible_cv, date_cv, special) VALUES ('$id_perso_a_pendre','Pendaison','', '$id_perso_a_pendre','<font color=$couleur_clan_perso>$nom_perso_a_pendre</font>', '', NOW(), 1)";
				$mysqli->query($sql);
				
				$sql = "DELETE FROM perso_as_arme WHERE id_perso='$id_perso_a_pendre'";
				$mysqli->query($sql);
				
				$sql = "DELETE FROM perso_as_armure WHERE id_perso='$id_perso_a_pendre'";
				$mysqli->query($sql);
				
				$sql = "DELETE FROM perso_as_competence WHERE id_perso='$id_perso_a_pendre'";
				$mysqli->query($sql);
				
				$sql = "DELETE FROM perso_as_contact WHERE id_perso='$id_perso_a_pendre'";
				$mysqli->query($sql);
				
				$sql = "DELETE FROM perso_as_dossiers WHERE id_perso='$id_perso_a_pendre'";
				$mysqli->query($sql);
				
				$sql = "DELETE FROM perso_as_decoration WHERE id_perso='$id_perso_a_pendre'";
				$mysqli->query($sql);
				
				$sql = "DELETE FROM perso_as_entrainement WHERE id_perso='$id_perso_a_pendre'";
				$mysqli->query($sql);
				
				$sql = "DELETE FROM perso_as_killpnj WHERE id_perso='$id_perso_a_pendre'";
				$mysqli->query($sql);
				
				$sql = "DELETE FROM perso_as_objet WHERE id_perso='$id_perso_a_pendre'";
				$mysqli->query($sql);
				
				$sql = "DELETE FROM perso_as_respawn WHERE id_perso='$id_perso_a_pendre'";
				$mysqli->query($sql);
				
				$sql = "DELETE FROM perso_bagne WHERE id_perso='$id_perso_a_pendre'";
				$mysqli->query($sql);
				
				$sql = "DELETE FROM perso_in_batiment WHERE id_perso='$id_perso_a_pendre'";
				$mysqli->query($sql);
				
				$sql = "DELETE FROM histobanque_compagnie WHERE id_perso='$id_perso_a_pendre'";
				$mysqli->query($sql);
				
				$sql = "DELETE FROM banque_compagnie WHERE id_perso='$id_perso_a_pendre'";
				$mysqli->query($sql);
				
				if ($thune_en_banque > 0) {
					$sql = "UPDATE banque_as_compagnie SET montant = montant - $thune_en_banque 
							WHERE id_compagnie= ( SELECT id_compagnie FROM perso_in_compagnie WHERE id_perso='$id_perso_a_pendre')";
					$mysqli->query($sql);
					
					$sql_b = "SELECT montant FROM banque_as_compagnie WHERE id_compagnie='$id_compagnie'";
					$res_b = $mysqli->query($sql_b);
					$t_b = $res_b->fetch_assoc();
					
					$montant_final_banque = $t_b['montant'];
					
					$date = time();
					
					// banque log
					$sql = "INSERT INTO banque_log (date_log, id_compagnie, id_perso, montant_transfert, montant_final) VALUES (FROM_UNIXTIME($date), '$id_compagnie', '$id_perso_a_pendre', '-$thune_en_banque', '$montant_final_banque')";
					$mysqli->query($sql);
				}
				
				$sql = "DELETE FROM perso_in_compagnie WHERE id_perso='$id_perso_a_pendre'";
				$mysqli->query($sql);
				
				if (in_bat($mysqli, $id_perso_a_pendre)) {		
					$sql = "DELETE FROM perso_in_batiment WHERE id_perso='$id_perso_a_pendre'";
				}
				else if (in_train($mysqli, $id_perso_a_pendre)) {
					$sql = "DELETE FROM perso_in_train WHERE id_perso='$id_perso_a_pendre'";
				}
				else {
					$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE idPerso_carte='$id_perso_a_pendre'";
				}
				$mysqli->query($sql);

				// On téléporte le perso hors carte
				$sql = "UPDATE perso SET x_perso='1000', y_perso='1000' WHERE id_perso='$id_perso_a_pendre'";
				$mysqli->query($sql);
				
				$sql = "DELETE FROM perso_in_mission WHERE id_perso='$id_perso_a_pendre'";
				$mysqli->query($sql);

				echo "<center><font color='blue'>Le perso $nom_perso_a_pendre avec la matricule $id_perso_a_pendre a bien été pendu.</font></center><br/>";
			}
		}
		
		if(isset($_POST['select_perso']) && $_POST['select_perso'] != '') {
			
			$id_perso_select = $_POST['select_perso'];
			
		}
		
		if (isset($_GET['consulter_mp'])) {
			
			$id_perso_select = $_GET['consulter_mp'];
			
		}
		
		if (isset($_GET['modifier_mdp'])) {
			
			$id_perso_select = $_GET['modifier_mdp'];
			
		}
		
		if (isset($_GET['verifier_charge'])) {
			
			$id_perso_select = $_GET['verifier_charge'];
			
		}
		
		if (isset($_GET['voir_respawn'])) {
			
			$id_perso_select = $_GET['voir_respawn'];
			
		}
		
		if (isset($_GET['voir_inventaire'])) {
			
			$id_perso_select = $_GET['voir_inventaire'];
			
			if (isset($_GET['id_obj'])) {
				
				$id_o = $_GET['id_obj'];
				
				// On verifie que l'identifiant soit bien un nombre positif
				$verif = preg_match("#^[0-9]*[0-9]$#i","$id_o");
				
					if($verif && $id_o > 0) {
					
					if (isset($_GET['desequip'])) {
						// On desequip l'objet
						$sql = "UPDATE perso_as_objet SET equip_objet=0 WHERE id_perso='$id_perso_select' AND id_objet='$id_o'";
						$mysqli->query($sql);

						$sql = "INSERT INTO log_action_animation(date_acces, id_perso, page, action, texte) VALUES (NOW(), '$id_perso', 'admim_perso.php', 'Changement inventaire déséquiper', 'perso $id_perso_select objet $id_o')";
						$mysqli->query($sql);
					}
					elseif (isset($_GET['equip'])) {
						// On equip l'objet
						$sql = "UPDATE perso_as_objet SET equip_objet=1 WHERE id_perso='$id_perso_select' AND id_objet='$id_o'";
						$mysqli->query($sql);

						$sql = "INSERT INTO log_action_animation(date_acces, id_perso, page, action, texte) VALUES (NOW(), '$id_perso', 'admim_perso.php', 'Changement inventaire équiper', 'perso $id_perso_select objet $id_o')";
						$mysqli->query($sql);
					}
					elseif (isset($_GET['deposer'])) {
						// On dépose l'objet
						action_deposerObjet($mysqli, $id_perso_select, 2, $id_o, 1);

						$sql = "INSERT INTO log_action_animation(date_acces, id_perso, page, action, texte) VALUES (NOW(), '$id_perso', 'admim_perso.php', 'Changement inventaire déposer', 'perso $id_perso_select objet $id_o')";
						$mysqli->query($sql);
					}
					elseif (isset($_GET['use'])) {
						// On utilise l'objet
						
						if($id_o != 1){
							
							// recuperation des effets de l'objet
							$sql = "SELECT * FROM objet WHERE id_objet='$id_o'";
							$res = $mysqli->query($sql);
							$bonus_o = $res->fetch_assoc();
							
							$nom_ob 			= $bonus_o["nom_objet"];
							$bonusPerception 	= $bonus_o["bonusPerception_objet"];
							$bonusRecup 		= $bonus_o["bonusRecup_objet"];
							$bonusPv 			= $bonus_o["bonusPv_objet"];
							$bonusPm 			= $bonus_o["bonusPm_objet"];
							$bonusPa			= $bonus_o["bonusPA_objet"];
							$coutPa 			= $bonus_o["coutPa_objet"];
							$poids 				= $bonus_o["poids_objet"];
							$type_o 			= $bonus_o["type_objet"];
							
							if ($type_o == 'N') {
								
								// on supprime l'objet de l'inventaire
								$sql = "DELETE FROM perso_as_objet WHERE id_perso='$id_perso_select' AND id_objet='$id_o' LIMIT 1";
								$mysqli->query($sql);
										
								// on recupere les pv et autres donnees du perso
								$sql = "SELECT pv_perso, pvMax_perso, recup_perso, bonusRecup_perso FROM perso WHERE id_perso='$id_perso_select'";
								$res = $mysqli->query($sql);
								$t_p = $res->fetch_assoc();
								
								$pv_p 	= $t_p["pv_perso"];
								$pvM_p 	= $t_p["pvMax_perso"];
								$rec_p 	= $t_p["recup_perso"];
								$br_p 	= $t_p["bonusRecup_perso"];
									
								// si l'objet donne des bonus
								if($bonusRecup) {
										
									// on applique les effets de l'objet sur le perso
									$sql = "UPDATE perso 
											SET bonusRecup_perso=bonusRecup_perso+$bonusRecup
											WHERE id_perso='$id_perso_select'";
									$mysqli->query($sql);
										
									// Affichage 
									$mess .= "Vous avez utilisé ".$nom_ob." sur le perso<br>";
									
									$recup_actuel = $rec_p + $br_p;
									
									if ($bonusRecup) {
										$mess .= "Sa recuperation passe de ".$recup_actuel." à ";
										$mess .= $rec_p+$br_p+$bonusRecup."<br />";
									}										
								}
								
								if ($bonusPerception < 0) {
									// le perso est bourre
									$sql = "UPDATE perso SET bourre_perso=bourre_perso+1 WHERE id_perso='$id_perso_select'";
									$mysqli->query($sql);
									
									$mess .= "La perception du perso en prend un coup temporairement : Perception ".$bonusPerception;
								}
								
								// MAJ perso
								$sql_c = "UPDATE perso SET pa_perso = pa_perso - 1, charge_perso=charge_perso-$poids WHERE id_perso='$id_perso_select'";
								$mysqli->query($sql_c);
							}
						}						
					}
					elseif (isset($_GET['delete'])) {
						// On supprime l'objet
					}
				}
			}
			elseif (isset($_GET['dest'])) {
				// Ticket de train
				$dest_ticket_to_delete = $_GET['dest'];
				
				if (isset($_GET['delete'])) {
					// On supprime le ticket
					
					$verif = preg_match("#^[0-9]*[0-9]$#i","$dest_ticket_to_delete");
				
					if ($verif) {
						
						$sql = "DELETE FROM perso_as_objet WHERE id_objet='1' AND id_perso='$id_perso_select' AND capacite_objet='$dest_ticket_to_delete' LIMIT 1";
						$mysqli->query($sql);
						
						$mess .= "Le ticket à destination de ".$dest_ticket_to_delete." a bien été supprimé de son inventaire";
					}
					else {
						// triche
						$mess_err .= "Données envoyées incorrectes...";
					}
					
				}
			}
			elseif (isset($_GET['id_arme'])) {
				
				$id_arme = $_GET['id_arme'];
				
				if (isset($_GET['delete'])) {
					// On supprime l'arme
				}
			}
		}
		
		if (isset($_POST['id_perso_select']) && $_POST['id_perso_select'] != '') {
			
			$id_perso_select = $_POST['id_perso_select'];
			
			if (isset($_POST['xp_perso']) && trim($_POST['xp_perso']) != '') {
				
				$new_xp_perso = $_POST['xp_perso'];
				
				$mess = "MAJ XP perso matricule ".$id_perso_select." vers ".$new_xp_perso;

				$sql = "SELECT xp_perso FROM perso WHERE id_perso='$id_perso_select'";
				$res = $mysqli->query($sql);
				$t = $res->fetch_assoc();
				$old_xp_perso = $t['xp_perso'];
				
				$sql = "UPDATE perso SET xp_perso=$new_xp_perso WHERE id_perso='$id_perso_select'";
				$mysqli->query($sql);

				$sql = "INSERT INTO log_action_animation(date_acces, id_perso, page, action, texte) VALUES (NOW(), '$id_perso', 'admim_perso.php', 'Changement XP', 'perso $id_perso_select $old_xp_perso vers $new_xp_perso')";
				$mysqli->query($sql);
			}
			
			if (isset($_POST['pi_perso']) && trim($_POST['pi_perso']) != '') {
				
				$new_pi_perso = $_POST['pi_perso'];
				
				$mess = "MAJ PI perso matricule ".$id_perso_select." vers ".$new_pi_perso;

				$sql = "SELECT pi_perso FROM perso WHERE id_perso='$id_perso_select'";
				$res = $mysqli->query($sql);
				$t = $res->fetch_assoc();
				$old_pi_perso = $t['pi_perso'];
				
				$sql = "UPDATE perso SET pi_perso=$new_pi_perso WHERE id_perso='$id_perso_select'";
				$mysqli->query($sql);

				$sql = "INSERT INTO log_action_animation(date_acces, id_perso, page, action, texte) VALUES (NOW(), '$id_perso', 'admim_perso.php', 'Changement XPI', 'perso $id_perso_select $old_pi_perso vers $new_pi_perso')";
				$mysqli->query($sql);
			}
			
			if (isset($_POST['pc_perso']) && trim($_POST['pc_perso']) != '') {
				
				$new_pc_perso = $_POST['pc_perso'];
				
				$mess = "MAJ PC perso matricule ".$id_perso_select." vers ".$new_pc_perso;
				
				$sql = "SELECT pc_perso FROM perso WHERE id_perso='$id_perso_select'";
				$res = $mysqli->query($sql);
				$t = $res->fetch_assoc();
				$old_pc_perso = $t['pc_perso'];
				
				$sql = "UPDATE perso SET pc_perso=$new_pc_perso WHERE id_perso='$id_perso_select'";
				$mysqli->query($sql);

				$sql = "INSERT INTO log_action_animation(date_acces, id_perso, page, action, texte) VALUES (NOW(), '$id_perso', 'admim_perso.php', 'Changement PC', 'perso $id_perso_select $old_pc_perso vers $new_pc_perso')";
				$mysqli->query($sql);
			}
			
			if (isset($_POST['or_perso']) && trim($_POST['or_perso']) != '') {
				
				$new_or_perso = $_POST['or_perso'];
				
				$mess = "MAJ THUNE perso matricule ".$id_perso_select." vers ".$new_or_perso;
				
				$sql = "SELECT or_perso FROM perso WHERE id_perso='$id_perso_select'";
				$res = $mysqli->query($sql);
				$t = $res->fetch_assoc();
				$old_or_perso = $t['or_perso'];
				
				$sql = "UPDATE perso SET or_perso=$new_or_perso WHERE id_perso='$id_perso_select'";
				$mysqli->query($sql);

				$sql = "INSERT INTO log_action_animation(date_acces, id_perso, page, action, texte) VALUES (NOW(), '$id_perso', 'admim_perso.php', 'Changement OR', 'perso $id_perso_select $old_or_perso vers $new_or_perso')";
				$mysqli->query($sql);
			}

			if (isset($_POST['image_perso']) && trim($_POST['image_perso']) != '') {
				
				$new_image_perso = $_POST['image_perso'];
				
				$mess = "MAJ IMAGE perso matricule ".$id_perso_select." vers ".$new_image_perso;
				
				$sql = "UPDATE perso SET image_perso='$new_image_perso' WHERE id_perso='$id_perso_select'";
				$mysqli->query($sql);

				$sql = "INSERT INTO log_action_animation(date_acces, id_perso, page, action, texte) VALUES (NOW(), '$id_perso', 'admim_perso.php', 'Changement IMAGE', 'perso $id_perso_select vers $new_image_perso')";
				$mysqli->query($sql);
			}
			
			if (isset($_POST['pv_perso']) && trim($_POST['pv_perso']) != '') {
				
				$new_pv_perso = $_POST['pv_perso'];
				
				$mess = "MAJ PV perso matricule ".$id_perso_select." vers ".$new_pv_perso;

				$sql = "SELECT pv_perso FROM perso WHERE id_perso='$id_perso_select'";
				$res = $mysqli->query($sql);
				$t = $res->fetch_assoc();
				$old_pv_perso = $t['pv_perso'];
				
				$sql = "UPDATE perso SET pv_perso=$new_pv_perso WHERE id_perso='$id_perso_select'";
				$mysqli->query($sql);

				$sql = "INSERT INTO log_action_animation(date_acces, id_perso, page, action, texte) VALUES (NOW(), '$id_perso', 'admim_perso.php', 'Changement PV', 'perso $id_perso_select $old_pv_perso vers $new_pv_perso')";
				$mysqli->query($sql);
			}
			
			if (isset($_POST['pm_perso']) && trim($_POST['pm_perso']) != '') {
				
				$new_pm_perso = $_POST['pm_perso'];
				
				$mess = "MAJ PM perso matricule ".$id_perso_select." vers ".$new_pm_perso;
				
				$sql = "SELECT pm_perso FROM perso WHERE id_perso='$id_perso_select'";
				$res = $mysqli->query($sql);
				$t = $res->fetch_assoc();
				$old_pm_perso = $t['pm_perso'];
				
				$sql = "UPDATE perso SET pm_perso=$new_pm_perso WHERE id_perso='$id_perso_select'";
				$mysqli->query($sql);

				$sql = "INSERT INTO log_action_animation(date_acces, id_perso, page, action, texte) VALUES (NOW(), '$id_perso', 'admim_perso.php', 'Changement PM', 'perso $id_perso_select $old_pm_perso vers $new_pm_perso')";
				$mysqli->query($sql);
			}
			
			if (isset($_POST['pa_perso']) && trim($_POST['pa_perso']) != '') {
				
				$new_pa_perso = $_POST['pa_perso'];
				
				$mess = "MAJ PA perso matricule ".$id_perso_select." vers ".$new_pa_perso;
				
				$sql = "SELECT pa_perso FROM perso WHERE id_perso='$id_perso_select'";
				$res = $mysqli->query($sql);
				$t = $res->fetch_assoc();
				$old_pa_perso = $t['pa_perso'];
				
				$sql = "UPDATE perso SET pa_perso=$new_pa_perso WHERE id_perso='$id_perso_select'";
				$mysqli->query($sql);

				$sql = "INSERT INTO log_action_animation(date_acces, id_perso, page, action, texte) VALUES (NOW(), '$id_perso', 'admim_perso.php', 'Changement PA', 'perso $id_perso_select $old_pa_perso vers $new_pa_perso')";
				$mysqli->query($sql);
			}
			
			if (isset($_POST['ch_perso']) && trim($_POST['ch_perso']) != '') {
				
				$new_ch_perso = $_POST['ch_perso'];
				
				$mess = "MAJ Charge perso matricule ".$id_perso_select." vers ".$new_ch_perso;
				
				$sql = "UPDATE perso SET charge_perso=$new_ch_perso WHERE id_perso='$id_perso_select'";
				$mysqli->query($sql);
			}
			
			if (isset($_POST['mdp_perso']) && trim($_POST['mdp_perso']) != "") {
				
				$new_password = $_POST['mdp_perso'];
				$new_password_md5 = MD5($new_password);
				
				$sql = "UPDATE joueur SET mdp_joueur='$new_password_md5' WHERE id_joueur = (SELECT idJoueur_perso FROM perso WHERE id_perso='$id_perso_select')";
				$mysqli->query($sql);
				
				$mess = "Changement du mot de passe du perso matricule ".$id_perso_select." vers ".$new_password ;
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
		<link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
	</head>
	
	<body>
		<div class="container-fluid">
		
			<div class="row">
				<div class="col-12">

					<div align="center">
						<h2>Administration</h2>
					</div>
				</div>
			</div>
			
			<p align="center"><a class="btn btn-primary" href="admin_nvs.php">Retour à l'administration</a> <a class="btn btn-primary" href="jouer.php">Retour au jeu</a></p>
			
			<div class="row">
				<div class="col-12">
				
					<h3>Administration des persos</h3>
					
					<center><font color='red'><?php echo $mess_err; ?></font></center>
					<center><font color='blue'><?php echo $mess; ?></font></center>
					
					<form method='POST' action='admin_perso.php'>
					
						<select name="select_perso" onchange="this.form.submit()">
						
							<?php
							$sql = "SELECT id_perso, nom_perso, x_perso, y_perso FROM perso ORDER BY id_perso ASC";
							$res = $mysqli->query($sql);
							
							while ($t = $res->fetch_assoc()) {
								
								$id_perso 	= $t["id_perso"];
								$nom_perso 	= $t["nom_perso"];
								$x_perso	= $t["x_perso"];
								$y_perso 	= $t["y_perso"];
								
								echo "<option value='".$id_perso."'";
								if (isset($id_perso_select) && $id_perso_select == $id_perso) {
									echo " selected";
								}
								echo ">".$id_perso." - ".$nom_perso." - ".$x_perso."/".$y_perso."</option>";
							}
							?>
						
						</select>
						
						<input type="submit" value="choisir">
						
					</form>
					
					<?php
					if (isset($id_perso_select) && $id_perso_select != 0) {
						
						$sql = "SELECT email_joueur FROM joueur, perso WHERE id_joueur = idJoueur_perso AND id_perso='$id_perso_select'";
						$res = $mysqli->query($sql);
						$t_j = $res->fetch_assoc();
						
						$email_joueur = $t_j['email_joueur'];
						
						$sql = "SELECT * FROM perso WHERE id_perso='$id_perso_select'";
						$res = $mysqli->query($sql);
						$t = $res->fetch_assoc();
						
						$nom_perso 	= $t['nom_perso'];
						$xp_perso 	= $t['xp_perso'];
						$pc_perso 	= $t['pc_perso'];
						$pi_perso	= $t['pi_perso'];
						$pv_perso 	= $t['pv_perso'];
						$pm_perso 	= $t['pm_perso'];
						$pa_perso	= $t['pa_perso'];
						$or_perso 	= $t['or_perso'];
						$ch_perso	= $t['charge_perso'];
						$type_p 	= $t['type_perso'];
						$test_b 	= $t['bourre_perso'];
						$camp_perso	= $t['clan'];
						$bat_perso	= $t['bataillon'];
						$image_perso	= $t['image_perso'];
						
						if ($camp_perso == 1) {
							$nom_camp_perso 	= "Nord";
							$couleur_camp_perso	= "blue";
						}
						else if ($camp_perso == 2) {
							$nom_camp_perso 	= "Sud";
							$couleur_camp_perso	= "red";
						}
						else if ($camp_perso == 2) {
							$nom_camp_perso 	= "Indiens";
							$couleur_camp_perso	= "green";
						}
						else {
							$nom_camp_perso 	= "Outlaw";
							$couleur_camp_perso	= "black";
						}
						
						$im_camp_perso = $nom_camp_perso.".gif";
						$im_type_perso = get_image_type_perso($type_p, $camp_perso);
						
						echo "<b>Email joueur :</b> ".$email_joueur."<br />";
						echo "<table border='1' width='100%'>";
						echo "	<tr>";
						echo "		<td align='center'><img src='../images_perso/".$im_type_perso."'></td>";
						echo "		<td align='center'><b>Nom : </b>".$nom_perso."</td>";
						echo "<form method='POST' action='admin_perso.php'>";
						echo "		<td align='center'><b>XP : </b><input type='text' name='xp_perso' value='".$xp_perso."' ><input type='hidden' value='".$id_perso_select."' name='id_perso_select'><input type='submit' value='modifier'></td>";
						echo "</form>";
						echo "<form method='POST' action='admin_perso.php'>";
						echo "		<td align='center'><b>PI : </b><input type='text' name='pi_perso' value='".$pi_perso."' ><input type='hidden' value='".$id_perso_select."' name='id_perso_select'><input type='submit' value='modifier'></td>";
						echo "</form>";
						echo "<form method='POST' action='admin_perso.php'>";
						echo "		<td align='center'><b>PC : </b><input type='text' name='pc_perso' value='".$pc_perso."' ><input type='hidden' value='".$id_perso_select."' name='id_perso_select'><input type='submit' value='modifier'></td>";
						echo "</form>";
						echo "<form method='POST' action='admin_perso.php'>";
						echo "		<td align='center'><b>THUNE : </b><input type='text' name='or_perso' value='".$or_perso."' ><input type='hidden' value='".$id_perso_select."' name='id_perso_select'><input type='submit' value='modifier'></td>";
						echo "</form>";
						echo "	</tr>";
						echo "	<tr>";
						echo "<form method='POST' action='admin_perso.php'>";
						echo "		<td align='center'><b>Image: </b><input type='text' name='image_perso' value='".$image_perso."' ><input type='hidden' value='".$id_perso_select."' name='id_perso_select'><input type='submit' value='modifier'></td>";
						echo "</form>";
						echo "		<td><b>Bataillon : </b>".$bat_perso."</td>";
						echo "<form method='POST' action='admin_perso.php'>";
						echo "		<td align='center'><b>PV : </b><input type='text' name='pv_perso' value='".$pv_perso."' ><input type='hidden' value='".$id_perso_select."' name='id_perso_select'><input type='submit' value='modifier'></td>";
						echo "</form>";
						echo "<form method='POST' action='admin_perso.php'>";
						echo "		<td align='center'><b>PM : </b><input type='text' name='pm_perso' value='".$pm_perso."' ><input type='hidden' value='".$id_perso_select."' name='id_perso_select'><input type='submit' value='modifier'></td>";
						echo "</form>";
						echo "<form method='POST' action='admin_perso.php'>";
						echo "		<td align='center'><b>PA : </b><input type='text' name='pa_perso' value='".$pa_perso."' ><input type='hidden' value='".$id_perso_select."' name='id_perso_select'><input type='submit' value='modifier'></td>";
						echo "</form>";
						echo "<form method='POST' action='admin_perso.php'>";
						echo "		<td align='center'><b>Charge : </b><input type='text' name='ch_perso' value='".$ch_perso."' ><input type='hidden' value='".$id_perso_select."' name='id_perso_select'><input type='submit' value='modifier'></td>";
						echo "</form>";
						echo "	</tr>";
						echo "</table>";
						
						if (isset($_GET['consulter_mp'])) {
							echo "<br /><a href='admin_perso.php?consulter_mp=".$id_perso_select."' class='btn btn-secondary'>Consulter les MP du perso</a>";
						}
						else {
							echo "<br /><a href='admin_perso.php?consulter_mp=".$id_perso_select."' class='btn btn-primary'>Consulter les MP du perso</a>";
						}
						
						if (isset($_GET['verifier_charge'])) {
							
							echo "<br /><br />";
							
							$sql = "SELECT SUM(poids_objet) as somme_poids_objets FROM perso_as_objet, objet WHERE perso_as_objet.id_objet = objet.id_objet AND id_perso='$id_perso_select'";
							$res = $mysqli->query($sql);
							$t_o = $res->fetch_assoc();
							
							$poids_objets = $t_o['somme_poids_objets'];
							if ($poids_objets == null) {
								$poids_objets = 0;
							}
							
							$sql = "SELECT SUM(poids_arme) as somme_poids_armes FROM perso_as_arme, arme WHERE perso_as_arme.id_arme = arme.id_arme AND id_perso='$id_perso_select' AND est_portee='0'";
							$res = $mysqli->query($sql);
							$t_a = $res->fetch_assoc();
							
							$poids_armes = $t_a['somme_poids_armes'];
							if ($poids_armes == null) {
								$poids_armes = 0;
							}
							
							echo "Somme total du poids des objets dans le sac du perso : <b>".$poids_objets."</b><br />";
							echo "Somme total du poids des armes dans le sac du perso : <b>".$poids_armes."</b><br />";
							
						}
						else {
							echo " <a href='admin_perso.php?verifier_charge=".$id_perso_select."' class='btn btn-primary'>vérifier la charge du perso</a> ";
						}
						
						if (isset($_GET['voir_respawn'])) {
							
							$sql = "SELECT * FROM perso_as_respawn WHERE id_perso='$id_perso_select' ORDER BY id_bat ASC";
							$res = $mysqli->query($sql);
							
							echo "<table class='table'>";
							echo "	<thead>";
							echo "		<tr>";
							echo "			<th>Batiment</th><th>Etat</th><th>Position</th>";
							echo "		</tr>";
							echo "	</thead>";
							echo "	<tbody>";
							
							while ($t = $res->fetch_assoc()) {
								
								$id_i_bat_respawn = $t['id_instance_bat'];
								
								$sql_b = "SELECT nom_batiment, nom_instance, pv_instance, pvMax_instance, x_instance, y_instance, contenance_instance FROM instance_batiment, batiment 
											WHERE instance_batiment.id_batiment = batiment.id_batiment AND id_instanceBat='$id_i_bat_respawn'";
								$res_b = $mysqli->query($sql_b);
								$t_b = $res_b->fetch_assoc();
								
								$nom_bat	= $t_b['nom_batiment'];
								$nom_i_bat	= $t_b['nom_instance'];
								$pv_i_bat	= $t_b['pv_instance'];
								$pvM_i_bat	= $t_b['pvMax_instance'];
								$x_i_bat	= $t_b['x_instance'];
								$y_i_bat	= $t_b['y_instance'];
								$cont_i_bat	= $t_b['contenance_instance'];
								
								echo "		<tr>";
								echo "			<td>".$nom_bat." ".$nom_i_bat." [".$id_i_bat_respawn."]</td>";
								echo "			<td>".$pv_i_bat."/".$pvM_i_bat."</td>";
								echo "			<td>".$x_i_bat."/".$y_i_bat."</td>";
								echo "		</tr>";
								
							}
							echo "	</tbody>";
							echo "</table>";
						}
						else {
							echo " <a href='admin_perso.php?voir_respawn=".$id_perso_select."' class='btn btn-primary'>Voir ses respawns</a> ";
						}
						
						if (isset($_GET['voir_inventaire'])) {
							
							if (isset($_GET['ajout_objet'])) {
								
							}
							else {
								echo "<a href='admin_perso.php?voir_inventaire=".$id_perso_select."&ajout_objet=ok' class='btn btn-warning'>Ajouter un objet</a>";
							}
							
							echo "<br /><br />";
							
							echo "<table border=1 class='table'>";
							echo "	<tr>";
							echo "		<th width='25%'><center>objet</center></th><th width='25%'><center>nombre</center></th><th width='25%'><center>action</center></th>";
							echo "	</tr>";
							
							// recuperation du nombre de type d'objets que possede le perso
							$sql = "SELECT DISTINCT id_objet FROM perso_as_objet WHERE id_perso='$id_perso_select' ORDER BY id_objet";
							$res = $mysqli->query($sql);
							$nb_obj = $res->num_rows;
							
							while ($t_obj = $res->fetch_assoc()){
							
								// id de l'objet
								$id_obj = $t_obj["id_objet"];
								
								// recuperation des carac de l'objet
								$sql1 = "SELECT nom_objet, poids_objet, type_objet FROM objet WHERE id_objet='$id_obj'";
								$res1 = $mysqli->query($sql1);
								$t_o = $res1->fetch_assoc();
								
								$nom_o 			= $t_o["nom_objet"];
								$poids_o 		= $t_o["poids_objet"];
								$type_o			= $t_o["type_objet"];
								
								// recuperation du nombre d'objet de ce type que possede le perso
								$sql2 = "SELECT id_objet, capacite_objet FROM perso_as_objet WHERE id_perso='$id_perso_select' AND id_objet='$id_obj'";
								$res2 = $mysqli->query($sql2);
								$nb_o = $res2->num_rows;
								
								// calcul poids
								$poids_total_o = $poids_o * $nb_o;
								
								// affichage
								echo "<tr>";
								echo "	<td align='center'><img class='img-fluid' src=\"../images/objets/objet".$id_obj.".png\"><br/><font color=green><b>".$nom_o."</b></font></td>";
								echo "	<td align='center'>Ce perso possède <b>".$nb_o."</b> ".$nom_o."";
								if($nb_o > 1){ 
									echo "s";
								}
								
								// Est ce que le perso est déjà équipé de cet objet ?
								$sql3 = "SELECT * FROM perso_as_objet WHERE id_perso='$id_perso_select' AND id_objet='$id_obj' AND equip_objet='1'";
								$res3 = $mysqli->query($sql3);
								$is_equipe = $res3->num_rows;
								
								
								echo "<br /><u>Poids total :</u> <b>$poids_total_o</b></td>";
								echo "		<td align='center'>";
								if($type_o == 'N'){
									if ($test_b >= 2 && $id_obj == 3) {
										echo "<br /><font color='red'>Le perso ne peux plus consommer de Whisky ce tour-ci</font><br />";
									}
									else {
										echo "			<a class='btn btn-outline-success' href=\"admin_perso.php?voir_inventaire=".$id_perso_select."&id_obj=".$id_obj."&use=ok\">utiliser</a>";
									}
								}
								
								if($type_o == 'E' && !$is_equipe && $type_p != 6){
									echo "			<a class='btn btn-outline-primary' href=\"admin_perso.php?voir_inventaire=".$id_perso_select."&id_obj=".$id_obj."&equip=ok\">équiper</a>";
								}
								if ($is_equipe) {
									echo "			<a class='btn btn-outline-danger' href=\"admin_perso.php?voir_inventaire=".$id_perso_select."&id_obj=".$id_obj."&desequip=ok\">Déséquipper</a>";
								} else {
									echo "			<a class='btn btn-outline-danger' href=\"admin_perso.php?voir_inventaire=".$id_perso_select."&id_obj=".$id_obj."&deposer=ok\">Déposer</a>";
								}
								
								// Tickets de train
								if ($type_o == 'T') {
									
									echo "<br /><b>Destinations : </b><br />";
									
									while ($t_o = $res2->fetch_assoc()) {
										
										$destination = $t_o['capacite_objet'];
										
										if (trim($destination) == "") {
											echo "- Ticket non valide - "; 
										}
										else {
											echo "<a class='btn btn-primary' style='height:38px;' href='evenement.php?infoid=".$destination."'>".$destination."</a>";
											echo "<a class='btn btn-danger' href=\"admin_perso.php?voir_inventaire=".$id_perso_select."&dest=".$destination."&delete=ok\"><i class='fa fa-trash'></i></a><br />";
										}
									}
								}
								else {
									echo "			<a class='btn btn-danger' href=\"admin_perso.php?voir_inventaire=".$id_perso_select."&id_obj=".$id_obj."&delete=ok\">Supprimer</a>";
								}
								echo "		</td>";
								echo "	</tr>";
							}
							
							// Récupération des armes non équipées
							$sql = "SELECT DISTINCT id_arme FROM perso_as_arme WHERE id_perso='$id_perso_select' AND est_portee='0' ORDER BY id_arme";
							$res = $mysqli->query($sql);
							$nb_arme = $res->num_rows;
							
							while ($t_arme = $res->fetch_assoc()){
								
								// id de l'arme
								$id_arme = $t_arme["id_arme"];
								
								// recuperation des carac de l'objet
								$sql1 = "SELECT nom_arme, poids_arme, description_arme, image_arme FROM arme WHERE id_arme='$id_arme'";
								$res1 = $mysqli->query($sql1);
								$t_a = $res1->fetch_assoc();
								
								$nom_a 			= $t_a["nom_arme"];
								$poids_a 		= $t_a["poids_arme"];
								$description_a 	= $t_a["description_arme"];
								$image_a		= $t_a["image_arme"];
								
								// recuperation du nombre d'armes de ce type que possede le perso
								$sql2 = "SELECT id_arme FROM perso_as_arme WHERE id_perso='$id_perso_select' AND id_arme='$id_arme' AND est_portee='0'";
								$res2 = $mysqli->query($sql2);
								$nb_a = $res2->num_rows;
								
								// calcul poids
								$poids_total_a = $poids_a * $nb_a;
								
								$sql_u = "SELECT nom_unite FROM type_unite, arme_as_type_unite
											WHERE type_unite.id_unite = arme_as_type_unite.id_type_unite
											AND arme_as_type_unite.id_arme = '$id_arme'";
								$res_u = $mysqli->query($sql_u);
								$liste_unite = "";
								while ($t_u = $res_u->fetch_assoc()) {
									$nom_unite = $t_u["nom_unite"];
									
									if ($liste_unite != "") {
										$liste_unite .= " / ";
									}
									$liste_unite .= $nom_unite;
								}
								
								// affichage
								echo "<tr>";
								echo "	<td align='center'><img class='img-fluid' src=\"../images/armes/".$image_a."\"><br /><font color=green><b>".$nom_a."</b></font></td>";
								echo "	<td align='center'>Vous possédez <b>".$nb_a."</b> ".$nom_a."";
								if($nb_a > 1){ 
									echo "s";
								}
								
								echo "<br /><u>Poids total :</u> <b>$poids_total_a</b><br/>Arme utilisable pour les unités suivante : <b>".$liste_unite."</b><br /></td>";
								echo "		<td align='center'>";
								echo "			<a class='btn btn-danger' href=\"admin_perso.php?voir_inventaire=".$id_perso_select."&id_arme=".$id_arme."&delete=ok\">Supprimer</a>";
								echo "		</td>";
								echo "	</tr>";
							}
							
							echo "</table>";
						}
						else {
							echo "<a href='admin_perso.php?voir_inventaire=".$id_perso_select."' class='btn btn-primary'>Voir son inventaire</a>";
						}
						
						echo "<br /><br />";
						
						if (isset($_GET['modifier_mdp'])) {
							echo "<a href='admin_perso.php?modifier_mdp=".$id_perso_select."' class='btn btn-secondary'>Modifier Mot de passe</a> ";
						}
						else {
							echo "<a href='admin_perso.php?modifier_mdp=".$id_perso_select."' class='btn btn-danger'>Modifier Mot de passe</a> ";
						}
						
						echo "<button type=\"button\" class=\"btn btn-danger\" data-toggle=\"modal\" data-target=\"#modalConfirm$id_perso_select\">Pendre</button>";
						?>
						<!-- Modal -->
						<form method="post" action="admin_perso.php">
							<div class="modal fade" id="modalConfirm<?php echo $id_perso_select; ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
								<div class="modal-dialog modal-dialog-centered" role="document">
									<div class="modal-content">
										<div class="modal-header">
											<h5 class="modal-title" id="exampleModalCenterTitle">Pendre le perso <?php echo $nom_perso." [".$id_perso_select."]"; ?> ?</h5>
											<button type="button" class="close" data-dismiss="modal" aria-label="Close">
												<span aria-hidden="true">&times;</span>
											</button>
										</div>
										<div class="modal-body">
											Êtes-vous sûr de vouloir pendre le perso <?php echo $nom_perso." [".$id_perso_select."]"; ?> ?
											<input type='text' name='raison_pendaison' value="" placeholder="Raison pendaison">
											<input type='hidden' name='matricule_pendre_hidden' value='<?php echo $id_perso_select; ?>'>
										</div>
										<div class="modal-footer">
											<button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
											<button type="button" onclick="this.form.submit()" class="btn btn-danger">Pendre</button>
										</div>
									</div>
								</div>
							</div>
						</form>
						<?php
					}
					?>
				</div>
			</div>
			
			<div class='row'>
				<?php
				if (isset($_GET['modifier_mdp'])) {
					echo "	<div class='col-12'>";
					echo "	<form method='POST' action='admin_perso.php'>";
					echo "		<label for='mdp_perso'>Nouveau Mot de passe : </label>";
					echo "		<input type='text' id='mdp_perso' name='mdp_perso' value='' ><input type='hidden' value='".$id_perso_select."' name='id_perso_select'>";
					echo "		<button type='submit' class='btn btn-primary'>Modifier</button>";
					echo "	</form>";
					echo "	</div>";
				}
				
				if (isset($_GET['consulter_mp'])) {
					
					$sql_mp = "SELECT * FROM message WHERE id_expediteur='".$id_perso_select."' ORDER BY id_message DESC";
					$res_mp = $mysqli->query($sql_mp);
					$nb_mp_e = $res_mp->num_rows;
					
					echo "	<div class='col-12'>";
					echo "		<h2>MP envoyés par le perso (".$nb_mp_e.")</h2>";
					echo "		<table class='table'>";
					echo "			<tr>";
					echo "				<th style='text-align:center'>Date</th><th style='text-align:center'>Objet</th><th style='text-align:center'>Contenu</th>";
					echo "			</tr>";
					
					while ($t_mp = $res_mp->fetch_assoc()) {
						
						$date_mp 	= $t_mp['date_message'];
						$contenu_mp = $t_mp['contenu_message'];
						$objet_mp 	= $t_mp['objet_message'];
						$id_mp		= $t_mp['id_message'];
						
						echo "			<tr>";
						echo "				<td align='center'>".$date_mp."</td>";
						echo "				<td>".$objet_mp."</td>";
						echo "				<td>".$contenu_mp."</td>";
						echo "			</tr>";
					}
					
					echo "			</tr>";
					echo "		</table>";
					echo "	</div>";
					
					$sql_mp = "SELECT date_message, objet_message, contenu_message, message.id_message FROM message, message_perso 
								WHERE message.id_message = message_perso.id_message
								AND message_perso.id_perso='".$id_perso_select."' ORDER BY message.id_message DESC";
					$res_mp = $mysqli->query($sql_mp);
					$nb_mp_r = $res_mp->num_rows;
					
					echo "	<div class='col-12'>";
					echo "		<h2>MP reçues par le perso (".$nb_mp_r.")</h2>";
					echo "		<table class='table'>";
					echo "			<tr>";
					echo "				<th style='text-align:center'>Date</th><th style='text-align:center'>Objet</th>";
					echo "			</tr>";
					
					
					while ($t_mp = $res_mp->fetch_assoc()) {
						
						$date_mp 	= $t_mp['date_message'];
						$contenu_mp = $t_mp['contenu_message'];
						$objet_mp 	= $t_mp['objet_message'];
						$id_mp		= $t_mp['id_message'];
						
						echo "			<tr>";
						echo "				<td align='center'>".$date_mp."</td>";
						echo "				<td>".$objet_mp."</td>";
						echo "				<td>".$contenu_mp."</td>";
						echo "			</tr>";
					}
					
					echo "			</tr>";
					echo "		</table>";
					echo "	</div>";
				}
				?>
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
		// logout
		$_SESSION = array(); // On écrase le tableau de session
		session_destroy(); // On détruit la session
		
		header("Location:../index.php");
	}
}
else{
	echo "<font color=red>Vous ne pouvez pas accéder à cette page, veuillez vous loguer.</font>";
}
