<?php
session_start();
require_once("../fonctions.php");
require_once("f_banque.php");

$mysqli = db_connexion();

include ('../nb_online.php');

// recupération config jeu
$dispo = config_dispo_jeu($mysqli);
if (isset($_SESSION["id_perso"])) {
	$admin = admin_perso($mysqli, $_SESSION["id_perso"]);
} else {
	header("Location:../index.php");
}

if($dispo == '1' || $admin){

	if (isset($_SESSION["id_perso"])) {
		
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
			//$erreur = "<div class=\"erreur\">";
	
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
		<p align="center"><a href="jouer.php"> <input type="button" value="Retour au jeu"> </a></p>
		<div class="container-fluid">
			
			<div class="row justify-content-center">
				<div class="col-12">
	
					<div align="center"><h2>Banque de la compagnie</h2></div>
					<div align="center"><a href="../regles/regles_banque_compagnie.php" class='btn btn-outline-primary'>Règles banque de la compagnie</a></div>
					
				</div>
			</div>
	<?php
	if(isset($_GET["id_compagnie"])) {
		
		$verif = preg_match("#^[0-9]+$#i",$_GET["id_compagnie"]);
		
		if($verif){
	
			$id_compagnie = $_GET["id_compagnie"];
			
			//verifier si le perso appartient bien a cette compagnie
			$sql = "SELECT id_compagnie FROM perso_in_compagnie WHERE id_perso=$id";
			$res = $mysqli->query($sql);
			$t = $res->fetch_assoc();
			
			$id_s = $t["id_compagnie"];
			
			if($id_compagnie == $id_s) { // ok
			
				if(isset($_POST['annuler_emp'])){
					
					$sql = "UPDATE banque_compagnie SET demande_emprunt='0', montant_emprunt=0 WHERE id_perso=$id";
					$mysqli->query($sql);
					
					echo "Vous venez d'annuler votre ancienne demande d'emprunt, vous pouvez à present formuler une nouvelle demande si vous le souhaitez.<br>";
				}
				
				// on verifie si le perso ne doit pas des sous a la compagnie
				$sql = "SELECT SUM(montant) as devoir FROM histobanque_compagnie WHERE id_perso=$id AND id_compagnie=$id_compagnie AND operation='2'";
				$res = $mysqli->query($sql);
				$t_du = $res->fetch_assoc();
				
				$du_t = -$t_du["devoir"];
				
				// on verifie si le perso a rembourser une partie de ses dettes
				$sql = "SELECT SUM(montant) as remb FROM histobanque_compagnie WHERE id_perso=$id AND id_compagnie=$id_compagnie AND operation='3'";
				$res = $mysqli->query($sql);
				$t_du = $res->fetch_assoc();
				
				$du_r = $t_du["remb"];
				
				$du = $du_t - $du_r;
				
				if(isset($_POST["deposer"])){
					
					if($_POST["deposer"] != ""){
						
						$montant = $_POST["deposer"];
						
						$verif = preg_match("#^[0-9]*[0-9]$#i","$montant");
						
						if($verif) {
							
							// Loi anti-zerk retrait -> depot => il faut 8h entre le dernier retrait et un nouveau dépot
							// -------------
							// - ANTI ZERK -
							// -------------
							$verif_anti_zerk = gestion_anti_zerk_depot($mysqli, $id);
							
							if ($verif_anti_zerk < 0) {
							
								// Il faut déposer au moins 25 thunes
								if ($montant >= 25) {
								
									// verification qu'il possede bien les sous qu'il souhaite deposer ^^
									// recuperation des sous que le perso a sur lui
									$sql = "SELECT or_perso FROM perso WHERE id_perso='$id'";
									$res = $mysqli->query($sql);
									$t_bourse = $res->fetch_assoc();
									
									$bourse = $t_bourse["or_perso"];
									
									// il possede les sous
									if($bourse >= $montant) {
									
										// il doit des sous a la banque
										if($du) {
											
											// maj bourse perso
											$sql = "UPDATE perso SET or_perso=or_perso-$montant WHERE id_perso='$id'";
											$mysqli->query($sql);
											
											// maj banque_as_compagnie
											$sql = "UPDATE banque_as_compagnie SET montant=montant+$montant WHERE id_compagnie='$id_compagnie'";
											$mysqli->query($sql);
											
											$sql = "SELECT montant FROM banque_as_compagnie WHERE id_compagnie='$id_compagnie'";
											$res = $mysqli->query($sql);
											$t = $res->fetch_assoc();
											
											$montant_final_banque = $t['montant'];
											
											$date = time();
											
											// banque log
											$sql = "INSERT INTO banque_log (date_log, id_compagnie, id_perso, montant_transfert, montant_final) VALUES (FROM_UNIXTIME($date), '$id_compagnie', '$id', '$montant', '$montant_final_banque')";
											$mysqli->query($sql);
											
											if($montant > $du) {
												// maj histoBanque_compagnie : remboursement dette (3)
												$sql = "INSERT INTO histobanque_compagnie (id_compagnie, id_perso, operation, montant, date_operation) VALUES ('$id_compagnie','$id','3','$du', FROM_UNIXTIME($date))";						
												$mysqli->query($sql);
												
												$depot = $montant - $du;
												
												// maj histoBanque_compagnie : depot
												$sql = "INSERT INTO histobanque_compagnie (id_compagnie, id_perso, operation, montant, date_operation) VALUES ('$id_compagnie','$id','0','$depot', FROM_UNIXTIME($date))";						
												$mysqli->query($sql);
												
												// on met la difference sur le compte du perso
												$montant_f = $montant-$du;
												
												// maj banque_compagnie
												$sql = "UPDATE banque_compagnie SET montant=montant+$montant_f WHERE id_perso=$id";
												$mysqli->query($sql);	
											} else {
												// maj histoBanque_compagnie : remboursement dette (3)
												$sql = "INSERT INTO histobanque_compagnie (id_compagnie, id_perso, operation, montant, date_operation) VALUES ('$id_compagnie','$id','3','$montant', FROM_UNIXTIME($date))";						
												$mysqli->query($sql);
											}
										}
										else {
											
											// maj banque_compagnie
											$sql = "UPDATE banque_compagnie SET montant=montant+$montant WHERE id_perso=$id";
											$mysqli->query($sql);
											
											// maj banque_as_compagnie
											$sql = "UPDATE banque_as_compagnie SET montant=montant+$montant WHERE id_compagnie=$id_compagnie";
											$mysqli->query($sql);
											
											$sql = "SELECT montant FROM banque_as_compagnie WHERE id_compagnie='$id_compagnie'";
											$res = $mysqli->query($sql);
											$t = $res->fetch_assoc();
											
											$montant_final_banque = $t['montant'];
											
											$date = time();
											
											// banque log
											$sql = "INSERT INTO banque_log (date_log, id_compagnie, id_perso, montant_transfert, montant_final) VALUES (FROM_UNIXTIME($date), '$id_compagnie', '$id', '$montant', '$montant_final_banque')";
											$mysqli->query($sql);
											
											// maj histoBanque_compagnie
											$sql = "INSERT INTO histobanque_compagnie (id_compagnie, id_perso, operation, montant, date_operation) VALUES ('$id_compagnie','$id','0','$montant', FROM_UNIXTIME($date))";
											$mysqli->query($sql);
											
											// maj bourse perso
											$sql = "UPDATE perso SET or_perso=or_perso-$montant WHERE id_perso=$id";
											$mysqli->query($sql);
											
											echo "<center>Vous venez de deposer <b>$montant</b> thune(s) en banque</center>";
										}
									}
									else {
										echo "<center><font color='red'>Vous ne disposez pas de la somme que vous souhaitez deposer en banque...</font></center>";
									}
								}
								else {
									echo "<center><font color='red'>Vous devez déposer au moins 25 thunes</font></center>";
								}
							}
							else {
								echo "<center><font color='red'>Vous devez respecter un temps de 8h entre votre dernier retrait et un nouveau dépot";
								$temps_restant = round($verif_anti_zerk / 3600, 1);
								echo "<br />Temps restant = environ ".$temps_restant."h</font></center>";
							}
						}
						else {
							echo "<center><font color='red'>Veuillez mettre un montant valide !</font></center>";
						}
					}
				}
				
				if(isset($_POST["retirer"])){
					
					if($_POST["retirer"] != ""){
						
						$montant = $_POST["retirer"];
						$verif = preg_match("#^[0-9]*[0-9]$#i","$montant");
						
						if($verif) {
							
							// verification qu'il y a assez de sous dans la banque pour retirer
							// recuperation des sous de la compagnie
							$sql = "SELECT montant FROM banque_as_compagnie WHERE id_compagnie=$id_compagnie";
							$res = $mysqli->query($sql);
							$t_sum2 = $res->fetch_assoc();
							
							$sum2 = $t_sum2["montant"];
						
							// verification qu'il a assez d'argent pour retirer
							$sql = "SELECT montant FROM banque_compagnie WHERE id_perso=$id";
							$res = $mysqli->query($sql);
							$t_b = $res->fetch_assoc();
							
							$sous_perso = $t_b["montant"];
							
							if($sous_perso >= $montant && $montant <= $sum2) { 
							
								// il possede assez d'argent en banque
								// maj de la banque
								$sql = "UPDATE banque_compagnie SET montant=montant-$montant WHERE id_perso=$id";
								$mysqli->query($sql);
								
								// maj banque_as_compagnie
								$sql = "UPDATE banque_as_compagnie SET montant=montant-$montant WHERE id_compagnie=$id_compagnie";
								$mysqli->query($sql);
								
								$sql = "SELECT montant FROM banque_as_compagnie WHERE id_compagnie='$id_compagnie'";
								$res = $mysqli->query($sql);
								$t = $res->fetch_assoc();
								
								$montant_final_banque = $t['montant'];
								
								$date = time();
								
								// banque log
								$sql = "INSERT INTO banque_log (date_log, id_compagnie, id_perso, montant_transfert, montant_final) VALUES (FROM_UNIXTIME($date), '$id_compagnie', '$id', '-$montant', '$montant_final_banque')";
								$mysqli->query($sql);
								
								// maj histoBanque_compagnie
								$date = time();
								$sql = "INSERT INTO histobanque_compagnie (id_compagnie, id_perso, operation, montant, date_operation) VALUES ('$id_compagnie','$id','1','-$montant', FROM_UNIXTIME($date))";
								$mysqli->query($sql);
								
								// maj bourse perso
								$sql = "UPDATE perso SET or_perso=or_perso+$montant WHERE id_perso=$id";
								$mysqli->query($sql);
								
								// MAJ date_dernier_retrait
								$sql = "SELECT * FROM anti_zerk_banque_compagnie WHERE id_perso='$id'";
								$res = $mysqli->query($sql);
								$nb = $res->num_rows;
								
								if ($nb > 0) {
									$sql = "UPDATE anti_zerk_banque_compagnie SET date_dernier_retrait=NOW() WHERE id_perso='$id'";
								}
								else {
									$sql = "INSERT INTO anti_zerk_banque_compagnie (id_perso, date_dernier_retrait) VALUES ('$id', NOW())";
								}
								$mysqli->query($sql);
								
								echo "<center>Vous venez de retirer <b>$montant</b> thune(s) de la banque</center>";
							}
							else {
								echo "<font color = red>Vous ne possedez pas assez en banque pour retirer $montant thune(s)</font>";
							}
						}
						else {
							echo "<font color = red>Veuillez mettre un montant valide !</font>";
						}
					}
				}
				
				if(isset($_POST["emprunter"])){
					
					if($_POST["emprunter"] != ""){
						
						$montant = $_POST["emprunter"];
						$verif = preg_match("#^[0-9]*[0-9]$#i","$montant");
						
						if($verif) {
							
							//verification qu'il n'a pas deja fait une demande
							$sql = "SELECT demande_emprunt, montant_emprunt FROM banque_compagnie WHERE id_perso='$id'";
							$res = $mysqli->query($sql);
							$t_emp = $res->fetch_assoc();
							
							$emp 	= $t_emp["demande_emprunt"];
							$mont 	= $t_emp["montant_emprunt"];
							
							if($emp) { 
							
								// il a deja demande un emprunt
								echo "<center>Vous avez déjà demandé un emprunt d'un montant de $mont thune(s)<br>";
								echo "Vous n'avez le droit qu'à une seule demande d'argent à la fois<br>";
								echo "Souhaitez vous annuler votre ancienne demande ?</center><br>";
								
								echo "<br>";
								echo "<form action=\"banque_compagnie.php?id_compagnie=$id_compagnie\" method=\"post\" class=\"form-group\" name=\"annuler_emp\">";
								echo "	<div align=\"center\">";
								echo "		<input type=\"submit\" name=\"annuler_emp\" value=\"Oui\"> <input type=\"submit\" name=\"non\" value=\"Non\">";
								echo "	</div>";
								echo "</form>";
							}
							else {
								
								// on verifie qu'il y a assez d'argent pour valider l'emprunt
								$sql = "SELECT montant FROM banque_as_compagnie WHERE id_compagnie=$id_compagnie";
								$res = $mysqli->query($sql);
								$t_sum = $res->fetch_assoc();
								
								$sum = $t_sum["montant"];
								
								if($sum >= $montant) {
								
									// envoi de la demande au tresorier
									$sql = "UPDATE banque_compagnie SET demande_emprunt='1', montant_emprunt='$montant' WHERE id_perso='$id'";
									$mysqli->query($sql);
									
									echo "Vous avez demandé un emprunt de $montant thune(s)<br>";
								}
								else {
									echo "Votre compagnie ne possede pas l'argent necessaire pour satisfaire votre demande.";
								}
							}				
						}
						else {
							echo "<font color = red>Veuillez mettre un montant valide !</font>";
						}
					}
				}
				
				if (isset($_POST["select_perso_virement"]) && isset($_POST["hid_montant_virement"])) {
					
					$id_perso_virement 	= $_POST["select_perso_virement"];
					$montant_virement	= $_POST["hid_montant_virement"];
					
					// On effectue le virement
					$sql = "UPDATE banque_compagnie SET montant = montant - $montant_virement WHERE id_perso = '$id'";
					$mysqli->query($sql);
					
					$sql = "UPDATE banque_compagnie SET montant = montant + $montant_virement WHERE id_perso = '$id_perso_virement'";
					$mysqli->query($sql);
					
					// Historique
					$date = time();
					$sql = "INSERT INTO histobanque_compagnie (id_compagnie, id_perso, operation, montant, date_operation) VALUES ('$id_compagnie','$id','4','-$montant_virement', FROM_UNIXTIME($date))";
					$mysqli->query($sql);
					
					$sql = "INSERT INTO histobanque_compagnie (id_compagnie, id_perso, operation, montant, date_operation) VALUES ('$id_compagnie','$id_perso_virement','4','$montant_virement', FROM_UNIXTIME($date))";
					$mysqli->query($sql);
				}
				
				// on recalcule le du
				// on verifie si le perso ne doit pas des sous a la compagnie
				$sql = "SELECT SUM(montant) as devoir FROM histobanque_compagnie WHERE id_perso=$id AND id_compagnie=$id_compagnie AND operation='2'";
				$res = $mysqli->query($sql);
				$t_du = $res->fetch_assoc();
				
				$du_t = -$t_du["devoir"];
				
				// on verifie si le perso a rembourser une partie de ses dettes
				$sql = "SELECT SUM(montant) as remb FROM histobanque_compagnie WHERE id_perso=$id AND id_compagnie=$id_compagnie AND operation='3'";
				$res = $mysqli->query($sql);
				$t_du = $res->fetch_assoc();
				
				$du_r = $t_du["remb"];
				
				$du = $du_t - $du_r;	
				
				if ($du) {
					echo "<center><font color=red>Vous devez <b>$du</b> thune(s) à votre compagnie</font></center>";
				}
				
				// recuperation des sous de la compagnie
				$sql = "SELECT montant FROM banque_as_compagnie WHERE id_compagnie=$id_compagnie";
				$res = $mysqli->query($sql);
				$t_sum = $res->fetch_assoc();
				
				$sum = $t_sum["montant"];
				
				echo "<center><font color=green>Votre compagnie possède <b>$sum</b> thune(s)</font></center><br>";
				
				// recuperation des sous que le perso a sur lui
				$sql = "SELECT or_perso FROM perso WHERE id_perso=$id";
				$res = $mysqli->query($sql);
				$t_bourse = $res->fetch_assoc();
				
				$bourse = $t_bourse["or_perso"];
				
				echo "<center><font color=blue>Vous avez <b>$bourse</b> thune(s) sur vous</font></center>";
				
				// recuperation de ce qu'il possede en banque
				$sql = "SELECT montant FROM banque_compagnie WHERE id_perso=$id";
				$res = $mysqli->query($sql);
				$t_b = $res->fetch_assoc();
				
				$banque = $t_b["montant"];
				
				echo "<center><font color=blue>Vous avez <b>$banque</b> thune(s) en banque</font></center><br>";
				
				// y a t-il une demande d'emprunt ?
				$sql = "SELECT montant_emprunt FROM banque_compagnie WHERE id_perso='$id' AND demande_emprunt='1'";
				$res = $mysqli->query($sql);
				$t_e = $res->fetch_assoc();
				
				$montant_emprunt = $t_e['montant_emprunt'];
				
				if (isset($montant_emprunt) && $montant_emprunt > 0) {
					echo "<center><font color=red>Vous avez effectué une demande d'emprunt de <b>$montant_emprunt</b> thune(s)</font>";
					echo "<form action=\"banque_compagnie.php?id_compagnie=$id_compagnie\" method=\"post\" class=\"form-group\" name=\"annuler_emp\">";
					echo "	<input class='btn btn-outline-danger' type=\"submit\" name=\"annuler_emp\" value=\"Annuler demande emprunt\">";
					echo "</form></center>";
				}
				
				echo "<br>";
				
				echo "<div class=\"row justify-content-center\">";
				echo "	<div class=\"col-lg-4 col-md-6 col-10\">";
				
				if (isset($_POST['virer'])) {
					
					if (trim($_POST['virer']) != "") {
						
						$montant_virement = $_POST['virer'];
						
						$verif = preg_match("#^[0-9]*[0-9]$#i","$montant_virement");
						
						if ($verif) {
						
							// Vérification qu'on a bien la thune en banque pour effectuer le virement
							if ($montant_virement <= $banque) {
						
								echo "<form action=\"banque_compagnie.php?id_compagnie=$id_compagnie\" method=\"post\" name=\"virement_perso\">";
								echo "	<div class=\"form-group\">";
								echo "		<label for=\"select_perso_virement\">Perso chez qui faire le virement : </label>";
								echo "		<select name='select_perso_virement'>";
								
								// liste des membres de la compagnie
								$sql = "SELECT nom_perso, perso.id_perso FROM perso, perso_in_compagnie 
										WHERE perso.id_perso = perso_in_compagnie.id_perso
										AND attenteValidation_compagnie = '0'
										AND id_compagnie='$id_compagnie'
										AND perso_in_compagnie.id_perso != '$id'";
								$res = $mysqli->query($sql);
								
								while ($t_pc = $res->fetch_assoc()) {
									$nom_perso_c	= $t_pc["nom_perso"];
									$id_perso_c 	= $t_pc["id_perso"];
									
									echo "			<option value=".$id_perso_c.">".$nom_perso_c." [".$id_perso_c."]</option>";
								}
								
								echo "";
								echo "		</select>";
								echo "		<input type=\"hidden\" name=\"hid_montant_virement\" value=\"".$montant_virement."\">";
								echo "		<input type=\"submit\" name=\"Submit\" class='btn btn-warning' value=\"valider\">";
								echo "	</div>";
								echo "</form>";
							}
							else {
								echo "<center><font color=red>Vous ne possèdez pas assez d'argent en banque pour effectuer ce virement</color><br />";
							}
							
							echo "<center><a href='banque_compagnie.php?id_compagnie=".$id_compagnie."' class='btn btn-outline-secondary'>Retour</a></center>";
						}
						else {
							echo "<center><font color=red>Montant renseigné incorrect !</color><br />";
							echo "<a href='banque_compagnie.php?id_compagnie=".$id_compagnie."' class='btn btn-outline-secondary'>Retour</a></center>";
						}
					}
					else {
						echo "<center><font color=red>Veuillez renseigner un montant pour le virement !</color><br />";
						echo "<a href='banque_compagnie.php?id_compagnie=".$id_compagnie."' class='btn btn-outline-secondary'>Retour</a></center>";
					}
				}
				else {
					echo "<form action=\"banque_compagnie.php?id_compagnie=$id_compagnie\" method=\"post\" name=\"deposer\">";
					echo "	<div class=\"form-group\">";
					echo "		<label for=\"depot\">Déposer de l'argent (25 minimum) : </label>";
					echo "		<input name=\"deposer\" class=\"form-control\" id=\"depot\" type=\"text\" value=\"\" onFocus=\"this.value=''\" maxlength=\"100\">";
					echo "		<input type=\"submit\" name=\"Submit\" class='btn btn-warning' value=\"valider\">";
					echo "	</div>";
					echo "</form>";
					
					echo "<form action=\"banque_compagnie.php?id_compagnie=$id_compagnie\" method=\"post\" name=\"retirer\">";
					echo "	<div class=\"form-group\">";
					echo "		<label for=\"retrait\">Retirer de l'argent : </label>";
					echo "		<input name=\"retirer\" class=\"form-control\" id=\"retrait\" type=\"text\" value=\"\" onFocus=\"this.value=''\" maxlength=\"100\">";
					echo "		<input type=\"submit\" name=\"Submit\" class='btn btn-warning' value=\"valider\">";
					echo "	</div>";
					echo "</form>";
					
					echo "<form action=\"banque_compagnie.php?id_compagnie=$id_compagnie\" method=\"post\" name=\"emprunter\">";
					echo "	<div class=\"form-group\">";
					echo "		<label for=\"emprunt\">Emprunter de l'argent (nécessite l'accord du tresorier) : </label>";
					echo "		<input name=\"emprunter\" class=\"form-control\" id=\"emprunt\" type=\"text\" value=\"\" onFocus=\"this.value=''\" maxlength=\"100\">";
					echo "		<input type=\"submit\" name=\"Submit\" class='btn btn-warning' value=\"valider\">";
					echo "	</div>";
					echo "</form>";
					
					echo "<form action=\"banque_compagnie.php?id_compagnie=$id_compagnie\" method=\"post\" name=\"virer\">";
					echo "	<div class=\"form-group\">";
					echo "		<label for=\"virement\">Virer de l'argent sur le compte d'un autre membre de la compagnie : </label>";
					echo "		<input name=\"virer\" class=\"form-control\" id=\"virement\" type=\"text\" value=\"\" onFocus=\"this.value=''\" maxlength=\"100\">";
					echo "		<input type=\"submit\" name=\"Submit\" class='btn btn-warning' value=\"valider\">";
					echo "	</div>";
					echo "</form>";
				}
				
				echo "<br /><br /><center><a href='compagnie.php' class='btn btn-outline-secondary'>Retour à la page de compagnie</a></center>";
				
				echo "	</div>";
				echo "</div>";
			}
			else {
				echo "<font color=red>Vous n'avez pas accès à la banque de cette compagnie !</color>";
				
				$text_triche = "Tentative accés page banque compagnie [$id_compagnie] sans y avoir les droits";
			
				$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id', '$text_triche')";
				$mysqli->query($sql);
			}
		}
		else {
			echo "<center>La compagnie demandé n'existe pas</center>";
			
			$param_test 	= addslashes($id_compagnie);
			$text_triche 	= "Test parametre sur page banque compagnie, parametre id_compagnie invalide tenté : $param_test";
				
			$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id', '$text_triche')";
			$mysqli->query($sql);
		}
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