<?php
session_start();
require_once("../fonctions.php");

$mysqli = db_connexion();

include ('../nb_online.php');

// recupération config jeu
$dispo = config_dispo_jeu($mysqli);
$admin = admin_perso($mysqli, $_SESSION["id_perso"]);

if($dispo || $admin){

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
		<div class="container-fluid">
			
			<div class="row justify-content-center">
				<div class="col-12">
	
					<div align="center"><h2>Banque de la compagnie</h2></div>
					
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
										
										echo "Vous venez de deposer $montant thune(s) en banque";
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
								echo "		<input type=\"submit\" name=\"annuler_emp\" value=\"Oui\">";
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
									
									echo "Vous avez demander un emprunt de $montant thune(s)<br>";
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
					echo "<center><font color=red>Vous devez <b>$du</b> thune(s) à votre compagnie</font></center>";
				}
				
				// recuperation des sous de la compagnie
				$sql = "SELECT montant FROM banque_as_compagnie WHERE id_compagnie=$id_compagnie";
				$res = $mysqli->query($sql);
				$t_sum = $res->fetch_assoc();
				
				$sum = $t_sum["montant"];
				
				echo "<center><font color=green>Votre compagnie possede <b>$sum</b> thune(s)</font></center><br>";
				
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
				
				echo "<br>";
				
				echo "<div class=\"row justify-content-center\">";
				echo "	<div class=\"col-lg-4 col-md-6 col-10\">";
				
				echo "<form action=\"banque_compagnie.php?id_compagnie=$id_compagnie\" method=\"post\" name=\"deposer\">";
				echo "	<div class=\"form-group\">";
				echo "		<label for=\"depot\">Deposer de l'argent (25 minimum) : </label>";
				echo "		<input name=\"deposer\" class=\"form-control\" id=\"depot\" type=\"text\" value=\"\" onFocus=\"this.value=''\" maxlength=\"100\">";
				echo "		<input type=\"submit\" name=\"Submit\" value=\"valider\">";
				echo "	</div>";
				echo "</form>";
				
				echo "<form action=\"banque_compagnie.php?id_compagnie=$id_compagnie\" method=\"post\" name=\"retirer\">";
				echo "	<div class=\"form-group\">";
				echo "		<label for=\"retrait\">Retirer de l'argent : </label>";
				echo "		<input name=\"retirer\" class=\"form-control\" id=\"retrait\" type=\"text\" value=\"\" onFocus=\"this.value=''\" maxlength=\"100\">";
				echo "		<input type=\"submit\" name=\"Submit\" value=\"valider\">";
				echo "	</div>";
				echo "</form>";
				
				echo "<form action=\"banque_compagnie.php?id_compagnie=$id_compagnie\" method=\"post\" name=\"emprunter\">";
				echo "	<div class=\"form-group\">";
				echo "		<label for=\"emprunt\">Emprunter de l'argent (necessite l'accord du tresorier) : </label>";
				echo "		<input name=\"emprunter\" class=\"form-control\" id=\"emprunt\" type=\"text\" value=\"\" onFocus=\"this.value=''\" maxlength=\"100\">";
				echo "		<input type=\"submit\" name=\"Submit\" value=\"valider\">";
				echo "	</div>";
				echo "</form>";
				
				echo "<br /><br /><center><a href='compagnie.php'> [acceder a la page de la compagnie] </a></center>";
				
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
	
	header("Location: ../index2.php");
}
?>