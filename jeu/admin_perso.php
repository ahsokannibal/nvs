<?php
session_start();
require_once("../fonctions.php");
require_once("f_carte.php");

$mysqli = db_connexion();

include ('../nb_online.php');

if(isset($_SESSION["id_perso"])){
	
	$id_perso = $_SESSION['id_perso'];
	
	// recupération config jeu
	$admin = admin_perso($mysqli, $id_perso);
	
	if($admin){
		
		$mess_err 	= "";
		$mess 		= "";
		
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
		
		if (isset($_GET['voir_inventaire'])) {
			
			$id_perso_select = $_GET['voir_inventaire'];
			
			if (isset($_GET['id_obj'])) {
				
				$id_o = $_GET['id_obj'];
				
				// On verifie que l'identifiant soit bien un nombre positif
				$verif = preg_match("#^[0-9]*[0-9]$#i","$id_o");
				
					if($verif && $id_o > 0) {
					
					if (isset($_GET['desequip'])) {
						// On desequip l'objet
					}
					elseif (isset($_GET['equip'])) {
						// On equip l'objet
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
				
				$sql = "UPDATE perso SET xp_perso=$new_xp_perso WHERE id_perso='$id_perso_select'";
				$mysqli->query($sql);
			}
			
			if (isset($_POST['pi_perso']) && trim($_POST['pi_perso']) != '') {
				
				$new_pi_perso = $_POST['pi_perso'];
				
				$mess = "MAJ PI perso matricule ".$id_perso_select." vers ".$new_pi_perso;
				
				$sql = "UPDATE perso SET pi_perso=$new_pi_perso WHERE id_perso='$id_perso_select'";
				$mysqli->query($sql);
			}
			
			if (isset($_POST['pc_perso']) && trim($_POST['pc_perso']) != '') {
				
				$new_pc_perso = $_POST['pc_perso'];
				
				$mess = "MAJ PC perso matricule ".$id_perso_select." vers ".$new_pc_perso;
				
				$sql = "UPDATE perso SET pc_perso=$new_pc_perso WHERE id_perso='$id_perso_select'";
				$mysqli->query($sql);
			}
			
			if (isset($_POST['or_perso']) && trim($_POST['or_perso']) != '') {
				
				$new_or_perso = $_POST['or_perso'];
				
				$mess = "MAJ THUNE perso matricule ".$id_perso_select." vers ".$new_or_perso;
				
				$sql = "UPDATE perso SET or_perso=$new_or_perso WHERE id_perso='$id_perso_select'";
				$mysqli->query($sql);
			}
			
			if (isset($_POST['pv_perso']) && trim($_POST['pv_perso']) != '') {
				
				$new_pv_perso = $_POST['pv_perso'];
				
				$mess = "MAJ PV perso matricule ".$id_perso_select." vers ".$new_pv_perso;
				
				$sql = "UPDATE perso SET pv_perso=$new_pv_perso WHERE id_perso='$id_perso_select'";
				$mysqli->query($sql);
			}
			
			if (isset($_POST['pm_perso']) && trim($_POST['pm_perso']) != '') {
				
				$new_pm_perso = $_POST['pm_perso'];
				
				$mess = "MAJ PM perso matricule ".$id_perso_select." vers ".$new_pm_perso;
				
				$sql = "UPDATE perso SET pm_perso=$new_pm_perso WHERE id_perso='$id_perso_select'";
				$mysqli->query($sql);
			}
			
			if (isset($_POST['pa_perso']) && trim($_POST['pa_perso']) != '') {
				
				$new_pa_perso = $_POST['pa_perso'];
				
				$mess = "MAJ PA perso matricule ".$id_perso_select." vers ".$new_pa_perso;
				
				$sql = "UPDATE perso SET pa_perso=$new_pa_perso WHERE id_perso='$id_perso_select'";
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
								echo ">".$nom_perso." [".$id_perso."] - ".$x_perso."/".$y_perso."</option>";
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
						
						echo "<b>Email joueur :</b> ".$email_joueur."<br />";
						echo "<table border='1' width='100%'>";
						echo "	<tr>";
						echo "		<td align='center'><img src='../images/".$im_camp_perso."'></td>";
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
						echo "		<td></td><td></td>";
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
							
							echo "<br />";
							echo "<table border='1' width='100%'>";
							echo "	<tr>";
							echo "		<th style='text-align:center'>Date</th><th style='text-align:center'>Objet</th><th style='text-align:center'>Contenu</th>";
							echo "	</tr>";
							
							$sql_mp = "SELECT * FROM message WHERE expediteur_message='".$nom_perso."' ORDER BY id_message DESC";
							$res_mp = $mysqli->query($sql_mp);
							while ($t_mp = $res_mp->fetch_assoc()) {
								
								$date_mp 	= $t_mp['date_message'];
								$contenu_mp = $t_mp['contenu_message'];
								$objet_mp 	= $t_mp['objet_message'];
								$id_mp		= $t_mp['id_message'];
								
								echo "	<tr>";
								echo "		<td>".$date_mp."</td>";
								echo "		<td>".$objet_mp."</td>";
								echo "		<td>".$contenu_mp."</td>";
								echo "	</tr>";
							}
							
							echo "	</tr>";
							echo "</table>";
							
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
							echo "<form method='POST' action='admin_perso.php'>";
							echo "	<label for='mdp_perso'>Nouveau Mot de passe : </label>";
							echo "	<input type='text' id='mdp_perso' name='mdp_perso' value='' ><input type='hidden' value='".$id_perso_select."' name='id_perso_select'>";
							echo "	<button type='submit' class='btn btn-primary'>Modifier</button>";
							echo "</form>";
						}
						else {
							echo "<a href='admin_perso.php?modifier_mdp=".$id_perso_select."' class='btn btn-danger'>Modifier Mot de passe</a>";
						}
					}
					?>
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
		// logout
		$_SESSION = array(); // On écrase le tableau de session
		session_destroy(); // On détruit la session
		
		header("Location:../index.php");
	}
}
else{
	echo "<font color=red>Vous ne pouvez pas accéder à cette page, veuillez vous loguer.</font>";
}