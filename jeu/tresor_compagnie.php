<?php
session_start();
require_once("../fonctions.php");

$mysqli = db_connexion();

include ('../nb_online.php');

// recupération config jeu
$dispo = config_dispo_jeu($mysqli);
$admin = admin_perso($mysqli, $_SESSION["id_perso"]);

if($dispo || $admin){
	
	if(isset($_SESSION["id_perso"])){
		
		//recuperation des varaibles de sessions
		$id = $_SESSION["id_perso"];
		
		$sql = "SELECT pv_perso FROM perso WHERE id_perso='$id'";
		$res = $mysqli->query($sql);
		$tpv = $res->fetch_assoc();
		
		$testpv = $tpv['pv_perso'];
		
		if ($testpv <= 0) {
			echo "<font color=red>Vous êtes mort...</font>";
		}
		else {
	
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
						<h2>Trésorerie</h2>
					</div>
					
					<p align="center"><input type="button" value="Fermer cette fenêtre" onclick="window.close();"></p>
			<?php
			if(isset($_GET["id_compagnie"])) {
				
				$id_compagnie = $_GET["id_compagnie"];
			
				$verif1 = preg_match("#^[0-9]+$#i",$id_compagnie);
				
				if($verif1){
				
					// verification que le perso appartient bien a la compagnie et en est le tresorier
					$sql = "SELECT id_compagnie, poste_compagnie FROM perso_in_compagnie WHERE id_perso='$id' AND id_compagnie='$id_compagnie' AND (poste_compagnie='3' OR poste_compagnie='1')";
					$res = $mysqli->query($sql);
					$verif = $res->num_rows;
						
					if($verif){
						
						// On vérifie si la compagnie possède des sections 
						$sql = "SELECT * FROM compagnies WHERE id_parent='$id_compagnie'";
						$res = $mysqli->query($sql);
						$nb_sections = $res->num_rows;
						
						if(isset($_POST['val_emp']) && $_POST['val_emp'] == "valider emprunt") {
							
							if(isset($_POST["emprunteur"])){
								
								$t_r = explode(",",$_POST["emprunteur"]);
								
								$id_emp 		= $t_r[0];
								$nom_emp 		= $t_r[1];
								$montant_emp 	= $t_r[2];
								
								// on verifie qu'il y a assez d'argent pour valider l'emprunt
								$sql = "SELECT montant FROM banque_as_compagnie WHERE id_compagnie=$id_compagnie";
								$res = $mysqli->query($sql);
								$t_sum = $res->fetch_assoc();
								
								$sum = $t_sum["montant"];
								
								if($sum >= $montant_emp) {
								
									// on supprime la demande d'emprunt
									$sql = "UPDATE banque_compagnie SET demande_emprunt='0', montant_emprunt='0' WHERE id_perso='$id_emp'";
									$mysqli->query($sql);
									
									// on met a jour bourse
									$sql = "UPDATE perso SET or_perso=or_perso+$montant_emp WHERE id_perso='$id_emp'";
									$mysqli->query($sql);
									
									// maj banque_as_compagnie
									$sql = "UPDATE banque_as_compagnie SET montant=montant-$montant_emp WHERE id_compagnie='$id_compagnie'";
									$mysqli->query($sql);
									
									// on met a jour histoBanque_compagnie
									$sql = "INSERT INTO histobanque_compagnie (id_compagnie, id_perso, operation, montant) VALUES ('$id_compagnie','$id_emp','2','-$montant_emp')";
									$mysqli->query($sql);
									
									echo "<center><font color='blue'>Vous avez validé l'emprunt de $montant_emp po pour $nom_emp</font></center>";
								}
								else { 
									// pas assez en banque
									echo "<center><font color='red'>Il n y a pas assez en banque pour permettre cet emprunt.</font></center>";
								}
							}
						}
						
						if (isset($_POST['refus_emp']) && $_POST['refus_emp'] == "refuser emprunt") {
							
							if(isset($_POST["emprunteur"])){
								
								$t_r = explode(",",$_POST["emprunteur"]);
								
								$id_emp 		= $t_r[0];
								$nom_emp 		= $t_r[1];
								$montant_emp 	= $t_r[2];
								
								// on supprime la demande d'emprunt
								$sql = "UPDATE banque_compagnie SET demande_emprunt='0', montant_emprunt='0' WHERE id_perso='$id_emp'";
								$mysqli->query($sql);
								
								// nom tersorier
								$sql = "SELECT nom_perso FROM perso WHERE id_perso='$id'";
								$res = $mysqli->query($sql);
								$t = $res->fetch_assoc();
								
								$nom_tresorier = $t['nom_perso'];
								
								// on lui envoi un mp
								$message = "Bonjour $nom_emp,
											J\'ai le regret de t\'annoncer que ta demande d\'emprunt de $montant_emp thunes a été refusé.";
								$objet = "Refus emprunt du trésorier de la compagnie";
								
								$lock = "LOCK TABLE (joueur) WRITE";
								$mysqli->query($lock);
								
								$sql = "INSERT INTO message (id_expediteur, expediteur_message, date_message, contenu_message, objet_message) 
										VALUES ('" . $id . "', '" . addslashes($nom_tresorier) . "', NOW(), '" . $message . "', '" . $objet . "')";
								$res = $mysqli->query($sql);
								$id_message = $mysqli->insert_id;
								
								$unlock = "UNLOCK TABLES";
								$mysqli->query($unlock);
								
								$sql = "INSERT INTO message_perso VALUES ('$id_message','$id_emp','1','0','1','0')";
								$res = $mysqli->query($sql);
								
								echo "<center><font color='blue'>Vous avez refusé l'emprunt de $montant_emp po pour $nom_emp</font></center>";
							}
						}
						
						if (isset($_POST['select_perso_virement_1']) && isset($_POST['select_perso_virement_dest']) && isset($_POST['montant_virement'])) {
							
							$id_perso_thune_deduite = $_POST['select_perso_virement_1'];
							$id_perso_thune_dest	= $_POST['select_perso_virement_dest'];
							$montant_virement		= $_POST['montant_virement'];
							
							if ($id_perso_thune_deduite != $id_perso_thune_dest) {
								
								$verif = preg_match("#^[0-9]+$#i",$montant_virement);
								
								if ($verif && $montant_virement > 0) {
									
									// Est ce que le perso possède assez de thunes sur son compte
									$sql = "SELECT montant FROM banque_compagnie WHERE id_perso='$id_perso_thune_deduite'";
									$res = $mysqli->query($sql);
									$t = $res->fetch_assoc();
									
									$montant_perso = $t['montant'];
									
									if ($montant_perso >= $montant_virement) {
									
										// On effectue le virement
										$sql = "UPDATE banque_compagnie SET montant = montant - $montant_virement WHERE id_perso = '$id_perso_thune_deduite'";
										$mysqli->query($sql);
										
										$sql = "UPDATE banque_compagnie SET montant = montant + $montant_virement WHERE id_perso = '$id_perso_thune_dest'";
										$mysqli->query($sql);
										
										// Historique
										$date = time();
										$sql = "INSERT INTO histobanque_compagnie (id_compagnie, id_perso, operation, montant, date_operation, is_auteur) VALUES ('$id_compagnie','$id_perso_thune_deduite','4','-$montant_virement', FROM_UNIXTIME($date), '0')";
										$mysqli->query($sql);
										
										$sql = "INSERT INTO histobanque_compagnie (id_compagnie, id_perso, operation, montant, date_operation, is_auteur) VALUES ('$id_compagnie','$id_perso_thune_dest','4','$montant_virement', FROM_UNIXTIME($date), '0')";
										$mysqli->query($sql);
										
										echo "<center><font color='blue'>Virement de $montant_virement effectué entre les persos $id_perso_thune_deduite et $id_perso_thune_dest.</font></center>";
									}
									else {
										echo "<center><font color='red'>Le perso prélevé ne possède pas assez de thunes.</font></center>";
									}
								}
								else {
									echo "<center><font color='red'>Montant thune incorrect.</font></center>";
								}
							}
							else {
								echo "<center><font color='red'>Le perso destinataire de la thune doit être différent du perso prélevé.</font></center>";
							}
						}
						
						if (isset($_POST['select_compagnie_section_virement_1']) && isset($_POST['select_compagnie_section_virement_dest']) && isset($_POST['montant_virement_compagnie_section'])) {
							
							$id_comp_thune_deduite 	= $_POST['select_compagnie_section_virement_1'];
							$id_comp_thune_dest		= $_POST['select_compagnie_section_virement_dest'];
							$montant_virement		= $_POST['montant_virement_compagnie_section'];
							
							if ($id_comp_thune_deduite != $id_comp_thune_dest) {
								
								$verif = preg_match("#^[0-9]+$#i",$montant_virement);
								
								if ($verif && $montant_virement > 0) {
									
									// Est ce que le perso possède assez de thunes sur son compte
									$sql = "SELECT montant FROM banque_as_compagnie WHERE id_compagnie='$id_comp_thune_deduite'";
									$res = $mysqli->query($sql);
									$t = $res->fetch_assoc();
									
									$montant_comp = $t['montant'];
									
									if ($montant_comp >= $montant_virement) {
										
										// On effectue le virement
										$sql = "UPDATE banque_as_compagnie SET montant = montant - $montant_virement WHERE id_compagnie = '$id_comp_thune_deduite'";
										$mysqli->query($sql);
										
										$sql = "UPDATE banque_as_compagnie SET montant = montant + $montant_virement WHERE id_compagnie = '$id_comp_thune_dest'";
										$mysqli->query($sql);
										
										// Historique
										$sql = "INSERT INTO histobanque_compagnie (id_compagnie, id_perso, operation, montant, date_operation, is_auteur, id_dest) VALUES ('$id_comp_thune_deduite', NULL, '7', '-$montant_virement', NOW(), '0', '$id_comp_thune_dest')";
										$mysqli->query($sql);
										
										$sql = "INSERT INTO histobanque_compagnie (id_compagnie, id_perso, operation, montant, date_operation, is_auteur, id_dest) VALUES ('$id_comp_thune_dest', NULL, '7', '$montant_virement', NOW(), '0', '$id_comp_thune_deduite')";
										$mysqli->query($sql);
									}
									else {
										echo "<center><font color='red'>La compagnie/section prélevée ne possède pas assez de thunes.</font></center>";
									}
								}
								else {
									echo "<center><font color='red'>Montant thune incorrect.</font></center>";
								}
							}
							else {
								echo "<center><font color='red'>La compagnie/section destinataire de la thune doit être différent de celle prélevée.</font></center>";
							}							
						}
					
						// verification si quelqu'un a demandé un emprunt
						$sql = "SELECT banque_compagnie.id_perso, nom_perso, montant_emprunt FROM banque_compagnie, perso_in_compagnie, perso 
								WHERE demande_emprunt='1' 
								AND id_compagnie=$id_compagnie 
								AND banque_compagnie.id_perso=perso_in_compagnie.id_perso 
								AND banque_compagnie.id_perso=perso.ID_perso";
						$res = $mysqli->query($sql);
						$nb = $res->num_rows;
						
						if($nb) { 
						
							// il y a des persos en attente de validation
							echo "<center><form method=\"post\" action=\"tresor_compagnie.php?id_compagnie=$id_compagnie\">";
							echo "liste des persos en attente : ";
							echo "<select name=\"emprunteur\">";
							
							while($t_id = $res->fetch_assoc()) {
								
								$id_p 		= $t_id["id_perso"];
								$nom_p 		= $t_id["nom_perso"];
								$montant_p 	= $t_id["montant_emprunt"];
							
								echo "<center><option value='".$id_p.",".$nom_p.",".$montant_p."'>".$nom_p."[".$id_p."] montant : ".$montant_p."</option><br></center>";
							}
							
							echo "</select>";
							echo "&nbsp;<input type=\"submit\" name=\"val_emp\" value=\"valider emprunt\">";
							echo "&nbsp;<input type=\"submit\" name=\"refus_emp\" value=\"refuser emprunt\">";
							echo "</form></center>";
						}
						else {
							echo "<center><font color = blue>Il n y a aucun perso en attente d'emprunt</font></center>";
						}
						
						// recuperation des sous de la compagnie
						$sql = "SELECT montant FROM banque_as_compagnie WHERE id_compagnie=$id_compagnie";
						$res = $mysqli->query($sql);
						$t_sum = $res->fetch_assoc();
						
						$sum = $t_sum["montant"];
						
						if(isset($_GET['solde']) && $_GET['solde'] == "ok") {
							
							if(isset($_GET['detail'])) {
								
								$id_p = $_GET['detail'];
								
								$sql = "SELECT nom_perso FROM perso WHERE id_perso='$id_p'";
								$res = $mysqli->query($sql);
								$t = $res->fetch_assoc();
								
								$nom_perso = $t['nom_perso'];
								
								$fond_total = 0;
								
								$sql = "SELECT operation, montant, date_operation, is_auteur FROM histobanque_compagnie, perso 
										WHERE id_compagnie=$id_compagnie 
										AND histobanque_compagnie.id_perso=perso.ID_perso 
										AND histobanque_compagnie.id_perso=$id_p 
										ORDER BY id_histo DESC";
								$res = $mysqli->query($sql);
								
								echo "<center>";
								
								echo "<b>".$nom_perso." [".$id_p."]</b><br /><br />";
								
								echo "<div id=\"table_tresor\" class=\"table-responsive\">";
								echo "	<table class='table' border='1'>";
								echo "		<tr>";
								echo "			<th style='text-align:center'>Date opération</th><th style='text-align:center'>Type d'opération</th><th style='text-align:center'>Montant</th>";
								echo "		</tr>";
								
								while ($t_solde = $res->fetch_assoc()) {
									
									$op 		= $t_solde['operation'];
									$montant 	= $t_solde['montant'];
									$date_ope	= $t_solde['date_operation'];
									$is_auteur	= $t_solde['is_auteur'];
									
									$fond_total += $montant;
									
									if ($op == 0) {
										$type_ope 	= "Dépot";
										$color 		= "blue";
									}
									if ($op == 1) {
										$type_ope 	= "Retrait";
										$montant 	= substr($montant, 1, strlen($montant));
										$color 		= "orange";
									}
									if ($op == 2) {
										$type_ope 	= "Emprunt";
										$montant 	= -$montant;
										$color 		= "red";
									}
									if ($op == 3) {
										$type_ope 	= "Remboursement emprunt";
										$color 		= "green";
									}
									if ($op == 4 && $is_auteur) {
										$type_ope 	= "Virement";
										$color 		= "brown";
									}
									if ($op == 4 && !$is_auteur) {
										$type_ope 	= "Transfert par le trésorier";
										$color 		= "gold";
									}
									
									if (isset($type_ope)) {
										echo "		<tr>";
										echo "			<td>".$date_ope."</td><td>".$type_ope."</td><td align='center'><font color='".$color."'><b>".$montant."</b></font></td>";
										echo "		</tr>";
									}
								}
								
								echo "			<tr>";
								echo "				<td align='center'><b>TOTAL</b></td><td></td><td align='center'><b>".$fond_total."</b></td>";
								echo "			</tr>";
								
								echo "	</table>";
								echo "</div>";
								
								echo "<br />";
								echo "<center>";
								echo "	<a href='tresor_compagnie.php?id_compagnie=$id_compagnie&solde=ok' class='btn btn-primary'> Voir les soldes par perso </a><br>";
								echo "</center>";
							}
							else {
								// on recupere l'historique pour les persos de sa compagnie
								$sql = "SELECT histobanque_compagnie.id_perso, nom_perso, SUM(montant) as fond_calcul  FROM histobanque_compagnie, perso 
										WHERE id_compagnie=$id_compagnie 
										AND histobanque_compagnie.id_perso=perso.id_perso 
										GROUP BY id_perso";
								$res = $mysqli->query($sql);
								
								echo "<center>";
								
								echo "<div id='table_tresor_perso' class='table-responsive'>";
								echo "	<table border='1' class='table' width='100%'>";
								echo "		<tr>";
								echo "			<th>Nom [matricule]</th><th style='text-align: center;'>Montant en banque</th><th style='text-align: center;'>Montant calculé des transactions</th><th style='text-align: center;'>Action</th>";
								echo "		</tr>";
								
								$fond_total = 0;
								$total_banque = 0;
								
								while ($t_solde = $res->fetch_assoc()) {
									
									$id_p 			= $t_solde['id_perso'];
									$nom_p 			= $t_solde['nom_perso'];
									$fond_calcul 	= $t_solde['fond_calcul'];
									
									$fond_total += $fond_calcul;
									
									$sql_m = "SELECT montant FROM banque_compagnie WHERE id_perso='$id_p'";
									$res_m = $mysqli->query($sql_m);
									$t_m = $res_m->fetch_assoc();
									
									$montant_perso_banque = $t_m['montant'];
									
									$total_banque += $montant_perso_banque;
									
									echo "		<tr>";
									echo "			<td>".$nom_p."[<a href='evenement.php?infoid=".$id_p."'>".$id_p."</a>]</td>";
									echo "			<td align='center'>".$montant_perso_banque."</td>";
									echo "			<td align='center'>".$fond_calcul."</td>";
									echo "			<td align='center'><a href='tresor_compagnie.php?id_compagnie=$id_compagnie&solde=ok&detail=$id_p' class='btn btn-info'> Consulter détails </a> <a href='nouveau_message.php?pseudo=".$nom_p."' target='_blank'><img src='../images/messagerie.png' width='40' height='40' alt='contacter' title='contacter'></a></td>";
									echo "		</tr>";
								}
								
								echo "		<tr>";
								echo "			<td align='center'><b>TOTAL</b></td>";
								echo "			<td align='center'>";
								if ($fond_total < 0) {
									echo "<font color='red'>";
								}
								else {
									echo "<font color='green'>";
								}
								echo "<b>".$fond_total."</b></font>";
								echo "			</td>";
								
								echo "			<td align='center'>";
								if ($total_banque < 0) {
									echo "<font color='red'>";
								}
								else {
									echo "<font color='green'>";
								}
								echo "<b>".$total_banque."</b></font>";
								echo "			</td>";
								
								if ($sum > $fond_total) {
									echo "<td align='center'>Des persos ont quittés la compagnie en laissant de la thune en banque</td>";
								}
								else if ($sum < $fond_total) {
									echo "<td>Votre chef a viré des persos de la compagnie qui possédaient une dette envers la banque, cet argent est perdu à jamais</td>";
								}
								else {
									echo "<td></td>";
								}
								echo "		</tr>";
								
								echo "	</table>";
								echo "</div>";						
							}
							
							echo "</center><br>";
						}
						else {
							echo "<br />";
							echo "<center>";
							echo "	<a href='tresor_compagnie.php?id_compagnie=$id_compagnie&solde=ok' class='btn btn-primary'> Voir les soldes par perso </a><br>";
							echo "</center>";
						}
						
						if (isset($_GET['histo']) && $_GET['histo'] == "ok") {
							
							$fond_total_histo = 0;
							
							echo "<div id='table_histo' class='table-responsive'>";
							echo "	<table class='table' border='1'>";
							echo "		<tr>";
							echo "			<th style='text-align:center'>Date opération</th>";
							echo "			<th style='text-align:center'>Nom perso</th>";
							echo "			<th style='text-align:center'>Matricule</th>";
							echo "			<th style='text-align:center'>Type d'opération</th>";
							echo "			<th style='text-align:center'>Montant</th>";
							echo "		</tr>";
							
							$sql = "SELECT operation, montant, date_operation, is_auteur, id_perso, id_dest FROM histobanque_compagnie
										WHERE id_compagnie=$id_compagnie
										ORDER BY id_histo DESC";
							$res = $mysqli->query($sql);
							
							while ($t = $res->fetch_assoc()) {
								
								$id_perso_histo		= $t['id_perso'];
								$op_histo 			= $t['operation'];
								$montant_histo 		= $t['montant'];
								$date_ope_histo		= $t['date_operation'];
								$is_auteur_histo	= $t['is_auteur'];
								$id_dest_histo		= $t['id_dest'];
								
								$sql_p = "SELECT nom_perso FROM perso WHERE id_perso='$id_perso_histo'";
								$res_p = $mysqli->query($sql_p);
								$t_p = $res_p->fetch_assoc();
								
								$nom_perso_histo	= $t_p['nom_perso'];
								
								$fond_total_histo += $montant_histo;
								
								if ($op_histo == 0) {
									$type_ope 		= "Dépot";
									$color 			= "blue";
								}
								if ($op_histo == 1) {
									$type_ope 		= "Retrait";
									$montant_histo 	= substr($montant_histo, 1, strlen($montant_histo));
									$color 			= "orange";
								}
								if ($op_histo == 2) {
									$type_ope 		= "Emprunt";
									$montant_histo 	= $montant_histo;
									$color 			= "red";
								}
								if ($op_histo == 3) {
									$type_ope 		= "Remboursement emprunt";
									$color 			= "green";
								}
								if ($op_histo == 4 && $is_auteur_histo) {
									$type_ope 		= "Virement";
									$color 			= "brown";
								}
								if ($op_histo == 4 && !$is_auteur_histo) {
									$type_ope 		= "Transfert par le trésorier (perso)";
									$color 			= "gold";
								}
								if ($op_histo == 5 || $op_histo == 6) {
									if ($montant_histo > 0) {
										$type_ope 		= "Virement Section vers Compagnie";
									}
									else {
										$type_ope 		= "Virement Vers Section";
									}
									$color 			= "purple";
								}
								if ($op_histo == 7 && $montant_histo < 0 && $nb_sections) {
									$type_ope 		= "Transfert par le tresorier";
									$color 			= "gold";
									
									if (isset($id_dest_histo)) {
										$sql_dest = "SELECT nom_compagnie, id_parent FROM compagnies WHERE id_compagnie='$id_dest_histo'";
										$res_dest = $mysqli->query($sql_dest);
										$t_dest = $res_dest->fetch_assoc();
										
										$nom_compagnie_dest = $t_dest['nom_compagnie'];
										$id_parent_dest		= $t_dest['id_parent'];
										
										if ($id_parent_dest) {
											$type_ope .= " : Vers la Section ".$nom_compagnie_dest;
										}
										else {
											$type_ope .= " : Vers la compagnie mère";
										}
									}
								}
								if ($op_histo == 7) {
									$type_ope 		= "Transfert par le tresorier";
									$color 			= "gold";
									
									if (isset($id_dest_histo)) {
										$sql_dest = "SELECT nom_compagnie, id_parent FROM compagnies WHERE id_compagnie='$id_dest_histo'";
										$res_dest = $mysqli->query($sql_dest);
										$t_dest = $res_dest->fetch_assoc();
										
										$nom_compagnie_dest = $t_dest['nom_compagnie'];
										$id_parent_dest		= $t_dest['id_parent'];
										
										if ($montant_histo > 0) {
											if ($id_parent_dest) {
												$type_ope .= " : Depuis la Section ".$nom_compagnie_dest;
											}
											else {
												$type_ope .= " : Depuis la compagnie mère";
											}
										}
										else {
											if ($id_parent_dest) {
												$type_ope .= " : Vers la Section ".$nom_compagnie_dest;
											}
											else {
												$type_ope .= " : Vers la compagnie mère";
											}
										}
									}
								}
								
								echo "		<tr>";
								echo "			<td>".$date_ope_histo."</td>";
								echo "			<td>".$nom_perso_histo."</td>";
								echo "			<td>".$id_perso_histo."</td>";
								echo "			<td>".$type_ope."</td>";
								echo "			<td align='center'><font color='".$color."'><b>".$montant_histo."</b></font></td>";
								echo "		</tr>";
								
							}
							
							echo "		<tr>";
							echo "			<td align='center' colspan='4'><b>TOTAL</b></td><td align='center'><b>".$fond_total_histo."</b></td>";
							echo "		</tr>";
							
							echo "	</table>";
							echo "</div>";
						}
						else {
							echo "<br />";
							echo "<center>";
							echo "	<a href='tresor_compagnie.php?id_compagnie=$id_compagnie&histo=ok' class='btn btn-primary'> Voir l'historique complet de la trésorerie </a><br>";
							echo "</center>";
						}
						
						if(isset($_GET['transfert']) && $_GET['transfert'] == "ok") {
							
							$sql = "SELECT perso.id_perso, perso.nom_perso, banque_compagnie.montant FROM perso, perso_in_compagnie, banque_compagnie 
									WHERE perso.id_perso = perso_in_compagnie.id_perso
									AND perso.id_perso = banque_compagnie.id_perso
									AND perso_in_compagnie.id_compagnie = '$id_compagnie'
									AND banque_compagnie.montant > 0
									ORDER BY perso.nom_perso";
							$res = $mysqli->query($sql);
							
							echo "<br />";
							echo "<div align='center'>";
							echo "<h2>Transfert de thunes</h2>";
							echo "<form action=\"tresor_compagnie.php?id_compagnie=$id_compagnie\" method='post' name='virement_perso'>";
							echo "	<div class='form-group'>";
							echo "		<label for='select_perso_virement_1'>Perso chez qui faire on prélève la thune : </label>";
							echo "		<select class='form-control' name='select_perso_virement_1'>";
							
							while ($t = $res->fetch_assoc()) {
								$nom_perso	= $t["nom_perso"];
								$id_perso 	= $t["id_perso"];
								$montant	= $t["montant"];
								
								echo "			<option value=".$id_perso.">".$nom_perso." [".$id_perso."] : $montant thunes</option>";
							}
							
							echo "		</select>";
							echo "		<label for='select_perso_virement_dest'>Perso cible : </label>";
							echo "		<select class='form-control' name='select_perso_virement_dest'>";
							
							$sql = "SELECT perso.id_perso, perso.nom_perso, banque_compagnie.montant FROM perso, perso_in_compagnie, banque_compagnie 
									WHERE perso.id_perso = perso_in_compagnie.id_perso
									AND perso.id_perso = banque_compagnie.id_perso
									AND perso_in_compagnie.id_compagnie = '$id_compagnie'
									ORDER BY perso.nom_perso";
							$res = $mysqli->query($sql);
							while ($t = $res->fetch_assoc()) {
								$nom_perso	= $t["nom_perso"];
								$id_perso 	= $t["id_perso"];
								$montant	= $t["montant"];
								
								echo "			<option value=".$id_perso.">".$nom_perso." [".$id_perso."] : $montant thunes</option>";
							}
							
							echo "		</select>";
							echo "		<label for='montant_virement'>Montant : </label>";
							echo "		<input type='text' class='form-control' id='montant_virement' name='montant_virement' value='' >";
							echo "		<input type='submit' name='Submit' class='btn btn-primary' value='valider'>";
							echo "	</div>";
							echo "</form>";
							echo "</div>";
							
						}
						else {
							echo "<br />";
							echo "<center>";
							echo "	<a href='tresor_compagnie.php?id_compagnie=$id_compagnie&transfert=ok' class='btn btn-warning'> Faire un transfert de thunes entre persos de la compagnie</a><br>";
							echo "</center>";
						}
						
						if ($nb_sections) {
							if (isset($_GET['transfertSection']) && $_GET['transfertSection'] == "ok") {
								
								$sql = "SELECT compagnies.id_compagnie, compagnies.nom_compagnie, montant 
										FROM compagnies, banque_as_compagnie
										WHERE compagnies.id_compagnie = banque_as_compagnie.id_compagnie
										AND (compagnies.id_compagnie='$id_compagnie' OR compagnies.id_parent='$id_compagnie')";
								$res = $mysqli->query($sql);
								
								echo "<br />";
								echo "<div align='center'>";
								echo "<h2>Transfert de thunes</h2>";
								echo "<form action=\"tresor_compagnie.php?id_compagnie=$id_compagnie\" method='post' name='virement_compagnie_section'>";
								echo "	<div class='form-group'>";
								echo "		<label for='select_compagnie_section_virement_1'>Compagnie/Section chez qui faire on prélève la thune : </label>";
								echo "		<select class='form-control' name='select_compagnie_section_virement_1'>";
								
								while ($t = $res->fetch_assoc()) {
									$nom_compagnie_transfert	= $t["nom_compagnie"];
									$id_compagnie_transfert 	= $t["id_compagnie"];
									$montant_transfert			= $t["montant"];
									
									echo "			<option value=".$id_compagnie_transfert.">".$nom_compagnie_transfert." [".$id_compagnie_transfert."] : $montant_transfert thunes</option>";
								}
								
								echo "		</select>";
								echo "		<label for='select_compagnie_section_virement_dest'>Compagnie/Section cible : </label>";
								echo "		<select class='form-control' name='select_compagnie_section_virement_dest'>";
								
								$sql2 = "SELECT compagnies.id_compagnie, compagnies.nom_compagnie, montant 
										FROM compagnies, banque_as_compagnie
										WHERE compagnies.id_compagnie = banque_as_compagnie.id_compagnie
										AND (compagnies.id_compagnie='$id_compagnie' OR compagnies.id_parent='$id_compagnie')";
								$res2 = $mysqli->query($sql2);
								
								while ($t2 = $res2->fetch_assoc()) {
									$nom_compagnie_transfert	= $t2["nom_compagnie"];
									$id_compagnie_transfert 	= $t2["id_compagnie"];
									$montant_transfert			= $t2["montant"];
									
									echo "			<option value=".$id_compagnie_transfert.">".$nom_compagnie_transfert." [".$id_compagnie_transfert."] : $montant_transfert thunes</option>";
								}
								
								echo "		</select>";
								echo "		<label for='montant_virement_compagnie_section'>Montant : </label>";
								echo "		<input type='text' class='form-control' id='montant_virement_compagnie_section' name='montant_virement_compagnie_section' value='' >";
								echo "		<input type='submit' name='Submit' class='btn btn-primary' value='valider'>";
								echo "	</div>";
								echo "</form>";
								echo "</div>";
								
							}
							else {
								echo "<br />";
								echo "<center>";
								echo "	<a href='tresor_compagnie.php?id_compagnie=$id_compagnie&transfertSection=ok' class='btn btn-warning'> Faire un transfert de thunes compagnie <-> section</a><br>";
								echo "</center>";
							}
						}
						
						echo "<br><center><font color=green>Votre compagnie possède <b>$sum</b> thune(s)</font></center><br>";
						
						echo "<br />";
						echo "<center>";
						echo "	<a href='compagnie.php' class='btn btn-outline-secondary'>Retour a la page de compagnie</a>";
						echo "</center>";
					}
					else {
						echo "<center><font color='red'>Vous n'avez pas les habilitations pour accéder à cette page !</font></center>";
					
						$text_triche 	= "Test accès page tresor compagnie d'id : $id_compagnie";
							
						$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id', '$text_triche')";
						$mysqli->query($sql);
					}
				}
				else {
					echo "<center><font color='red'>Le groupe demandé n'existe pas</font></center>";
					
					$param_test 	= addslashes($id_compagnie);
					$text_triche 	= "Test parametre sur page tresor compagnie, parametre id_compagnie invalide tenté : $param_test";
						
					$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id', '$text_triche')";
					$mysqli->query($sql);
				}
			}
			else {
				header("Location:jouer.php");
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
	}
	else{
		echo "<font color=red>Vous ne pouvez pas accéder à cette page, veuillez vous loguer.</font>";
	}?>
<?php
}
else {
	// logout
	$_SESSION = array(); // On ecrase le tableau de session
	session_destroy(); // On detruit la session
	
	header("Location:../index2.php");
}
?>