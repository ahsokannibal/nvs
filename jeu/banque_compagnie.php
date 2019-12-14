<?php
session_start();
require_once("../fonctions.php");

$mysqli = db_connexion();

include ('../nb_online.php');

// recuperation config jeu
$sql = "SELECT disponible FROM config_jeu";
$res = $mysqli->query($sql);
$t_dispo = $res->fetch_assoc();

$dispo = $t_dispo["disponible"];

if($dispo){

	if (@$_SESSION["id_perso"]) {
		
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
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	</head>
	<body>
		<div align="center"><h2>Banque de la compagnie</h2></div>
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
					
					$sql = "UPDATE banque_compagnie SET demande_emprunt='0', montant_emprunt=NULL WHERE id_perso=$id";
					$mysqli->query($sql);
					
					echo "Vous venez d'annuler votre ancienne demande d'emprunt, vous pouvez à present en formuler une ouvelle si vous le souhaitez.<br>";
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
									
									// maj histoBanque_compagnie : remboursement dette (3)
									$sql = "INSERT INTO histobanque_compagnie (id_compagnie, id_perso, operation, montant) VALUES ('$id_compagnie','$id','3','$montant')";						
									$mysqli->query($sql);
									
									if($montant > $du) {
										// on met la difference sur le compte du perso
										$montant_f = $montant-$du;
										
										// maj banque_compagnie
										$sql = "UPDATE banque_compagnie SET montant=montant+$montant_f WHERE id_perso=$id";
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
									
									// maj histoBanque_compagnie
									$sql = "INSERT INTO histobanque_compagnie VALUES ('','$id_compagnie','$id','0','$montant')";
									$mysqli->query($sql);
									
									// maj bourse perso
									$sql = "UPDATE perso SET or_perso=or_perso-$montant WHERE id_perso=$id";
									$mysqli->query($sql);
									
									echo "Vous venez de deposer $montant po en banque";
								}
							}
							else {
								echo "<center><font color='red'>Vous ne disposez pas de la somme que vous souhaitez deposer en banque...</font></center>";
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
								
								// maj histoBanque_compagnie
								$sql = "INSERT INTO histobanque_compagnie VALUES ('','$id_compagnie','$id','1','-$montant')";
								$mysqli->query($sql);
								
								// maj bourse perso
								$sql = "UPDATE perso SET or_perso=or_perso+$montant WHERE id_perso=$id";
								$mysqli->query($sql);
								
								echo "Vous venez de retirer $montant de la banque";
							}
							else {
								echo "<font color = red>Vous ne possedez pas assez en banque pour retirer $montant po</font>";
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
								echo "Vous avez déjà demandé un emprunt d'un montant de $mont po<br>";
								echo "Vous n'avez le droit qu'à une seule demande d'argent à la fois<br>";
								echo "Souhaitez vous annuler votre ancienne demande ?<br>";
								
								echo "<br><form action=\"banque_compagnie.php?id_compagnie=$id_compagnie\" method=\"post\" name=\"annuler_emp\">";
								echo "<div align=\"center\">";
								echo "<input type=\"submit\" name=\"annuler_emp\" value=\"Oui\">";
								echo "</div>";
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
									
									echo "Vous avez demander un emprunt de $montant po<br>";
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
					echo "<center><font color=red>Vous devez <b>$du</b> po à votre compagnie</font></center>";
				}
				
				// recuperation des sous de la compagnie
				$sql = "SELECT montant FROM banque_as_compagnie WHERE id_compagnie=$id_compagnie";
				$res = $mysqli->query($sql);
				$t_sum = $res->fetch_assoc();
				
				$sum = $t_sum["montant"];
				
				echo "<center><font color=green>Votre compagnie possede <b>$sum</b> po</font></center><br>";
				
				// recuperation des sous que le perso a sur lui
				$sql = "SELECT or_perso FROM perso WHERE id_perso=$id";
				$res = $mysqli->query($sql);
				$t_bourse = $res->fetch_assoc();
				
				$bourse = $t_bourse["or_perso"];
				
				echo "<center><font color=blue>Vous avez <b>$bourse</b> po sur vous</font></center>";
				
				// recuperation de ce qu'il possede en banque
				$sql = "SELECT montant FROM banque_compagnie WHERE id_perso=$id";
				$res = $mysqli->query($sql);
				$t_b = $res->fetch_assoc();
				
				$banque = $t_b["montant"];
				
				echo "<center><font color=blue>Vous avez <b>$banque</b> po en banque</font></center><br>";
				
				echo "<br><form action=\"banque_compagnie.php?id_compagnie=$id_compagnie\" method=\"post\" name=\"deposer\">";
				echo "<div align=\"center\">";
				echo "Deposer de l'argent : ";
				echo "<input name=\"deposer\" type=\"text\" value=\"\" onFocus=\"this.value=''\" maxlength=\"100\">";
				echo "<input type=\"submit\" name=\"Submit\" value=\"valider\">";
				echo "</div>";
				echo "</form>";
				
				echo "<form action=\"banque_compagnie.php?id_compagnie=$id_compagnie\" method=\"post\" name=\"retirer\">";
				echo "<div align=\"center\">";
				echo "Retirer de l'argent : ";
				echo "<input name=\"retirer\" type=\"text\" value=\"\" onFocus=\"this.value=''\" maxlength=\"100\">";
				echo "<input type=\"submit\" name=\"Submit\" value=\"valider\">";
				echo "</div>";
				echo "</form>";
				
				echo "<form action=\"banque_compagnie.php?id_compagnie=$id_compagnie\" method=\"post\" name=\"emprunter\">";
				echo "<div align=\"center\">";
				echo "Emprunter de l'argent (necessite l'accord du tresorier) : ";
				echo "<input name=\"emprunter\" type=\"text\" value=\"\" onFocus=\"this.value=''\" maxlength=\"100\">";
				echo "<input type=\"submit\" name=\"Submit\" value=\"valider\">";
				echo "</div>";
				echo "</form>";
				
				echo "<a href='compagnie.php'> [acceder a la page de la compagnie] </a>";
			}
			else {
				echo "<font color=red>Vous n'avez pas accès à la banque de cette compagnie !</color>";
			}
		}
		else {
			echo "<center>La compagnie demandé n'existe pas</center>";
		}
	}
	?>
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
	
	header("Location: index2.php");
}
?>