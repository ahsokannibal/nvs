<?php
session_start();
require_once("../fonctions.php");
require_once("f_combat.php");
require_once("f_carte.php");

$mysqli = db_connexion();

include ('../nb_online.php');

// recuperation config jeu
$sql = "SELECT disponible FROM config_jeu";
$res = $mysqli->query($sql);
$t_dispo = $res->fetch_assoc();

$dispo = $t_dispo["disponible"];

if($dispo){
	if(isset($_SESSION["id_perso"])){
		
		$id_perso = $_SESSION['id_perso'];
		$date = time();
	
		$sql = "SELECT pv_perso, pm_perso, bonusPM_perso, or_perso, UNIX_TIMESTAMP(DLA_perso) as DLA, est_gele FROM perso WHERE id_perso='$id_perso'";
		$res = $mysqli->query($sql);
		$tpv = $res->fetch_assoc();
		
		$testpv 	= $tpv['pv_perso'];
		$pm_perso	= $tpv['pm_perso'];
		$or 		= $tpv["or_perso"];
		$dla 		= $tpv["DLA"];
		$est_gele 	= $tpv["est_gele"];
		
		$config = '1';
		
		// verification si le perso est encore en vie
		if ($testpv <= 0) { 
			// le perso est mort
			session_unregister('deDefense');
			session_unregister('deAttaque');
			
			//tour.php se charge de verifier si nouveau tour
			header("Location:../tour.php"); 
		}
		else { 
			// le perso est vivant

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
	
			<div align="center"><h2>Vos Armes</h2></div>

<?php
			$mess = "";
			$mess_erreur = "";
			
			// On veut equiper ou desequiper une arme
			if (isset($_POST["equiper"]) || isset($_POST["desequiper"])) {
			
				$sql = "SELECT pa_perso, type_perso FROM perso WHERE id_perso='$id_perso'";
				$res = $mysqli->query($sql);
				$tab = $res->fetch_assoc();
				
				$pa_perso 		= $tab["pa_perso"];
				$type_perso 	= $tab["type_perso"];
				
				if ($pa_perso > 0) {
				
					// equiper arme
					if (isset($_POST["equiper"])&& isset($_POST["id_equip"])) {
					
						$id_arme = $_POST["id_equip"];
						
						// verification si perso peut équiper cette arme 
						$sql = "SELECT count(id_arme) as verif FROM arme_as_type_unite WHERE arme_as_type_unite.id_type_unite = '$type_perso' AND id_arme='$id_arme'";
						$res = $mysqli->query ($sql);
						$tab_v = $res->fetch_assoc();
						
						$verif = $tab_v['verif'];
						
						if ($verif) {
							
							$sql = "SELECT porteeMax_arme, nom_arme, main, bonusPM_arme, poids_arme FROM arme WHERE id_arme='$id_arme'";
							$res = $mysqli->query ($sql);
							$tab1 = $res->fetch_assoc();
							
							$nom_arme 		= $tab1["nom_arme"];
							$main_arme 		= $tab1["main"];
							$bonusPM_arme 	= $tab1["bonusPM_arme"];
							$poids_arme		= $tab1["poids_arme"];
							
							// CaC ?
							($tab1["porteeMax_arme"] == 1)? $cac=1 : $cac=0;
							
							// Verification si le perso est deja equipee d'une arme
							$sql2 = "SELECT nom_arme, main 
								FROM perso_as_arme, arme 
								WHERE id_perso='$id_perso'
								AND est_portee='1' AND arme.id_arme=perso_as_arme.id_arme";
							
							$res2 = $mysqli->query($sql2);
							
							// deja equipee
							if ($res2->num_rows) {
								
								$tab2 = $res2->fetch_assoc();
								
								$nom 	= $tab2["nom_arme"];
								$main 	= $tab2["main"];
								
								// Il est equipe d'une arme a une main, il peut porter une deuxieme arme a une main
								if($main == 1 && $res2->num_rows < 2){
								
									// Verification que l'arme qu'on veut equiper est une arme a une main
									if($main_arme == 1){
										
										$ok_equip = 0;
										
										// On veut s'equiper
										if(isset($_POST["equiper"])){
												
											$ok_equip = 1;
											// mise a jour des pa du perso
												
											$sql = "UPDATE perso SET pa_perso=pa_perso-1, charge_perso=charge_perso-$poids_arme WHERE id_perso='$id_perso'";
											$mysqli->query($sql);
												
											// mise a jour equipe perso
											$sql = "UPDATE perso_as_arme SET est_portee='1' WHERE id_perso='$id_perso' AND id_arme='$id_arme' LIMIT 1";
											$mysqli->query($sql);
											
											$mess = "Vous venez de vous equiper de : ".$nom_arme.".";
										}
									}
									else {
										$mess_erreur = "Vous êtes déjà equipé d'une arme à 1 main, vous ne pouvez pas porter une arme à 2 main";
									}
								}
								else {
									if($res2->num_rows == 2){
										// Il est equipee de deux armes
										$mess_erreur = "Vous êtes déjà equipé de 2 armes, veuillez déséquiper une arme";
									}
									else {
										($cac)? $meth = "corps à corps : " : $meth = "distance : ";
										$mess_erreur = "Vous êtes déjà equipé de l'arme de " . $meth . $nom . "";
									}
								}
							}
							else {
								// mise a jour des pa du perso
								$sql = "UPDATE perso SET pa_perso=pa_perso-1, charge_perso=charge_perso-$poids_arme WHERE id_perso='$id_perso'";
								$mysqli->query($sql);
								
								if($main_arme == 2){
									// mise a jour equipe perso
									$sql = "UPDATE perso_as_arme SET est_portee='1' WHERE id_perso='$id_perso' AND id_arme='$id_arme' LIMIT 1";
									$mysqli->query($sql);
								}
								else {
									if(isset($_POST["equiper"])){
										// mise a jour equipe perso
										$sql = "UPDATE perso_as_arme SET est_portee='1' WHERE id_perso='$id_perso' AND id_arme='$id_arme' LIMIT 1";
										$mysqli->query($sql);
									}
								}
								
								if($bonusPM_arme != 0){
									$sql_u = "UPDATE perso SET bonusPM_perso=bonusPM_perso+$bonusPM_arme WHERE id_perso='$id_perso'";
									$mysqli->query($sql_u);
								}
								
								$mess = "Vous venez de vous equiper de : ".$nom_arme."";
							}
						} else {
							$mess_erreur = "Vous n'avez pas le droit de vous équiper de cette arme !";
						}
					}
					
					// desequiper arme
					if (isset($_POST["desequiper"]) && isset($_POST["id_desequip"])) {
					
						$id_arme = $_POST["id_desequip"];
						
						$sql = "SELECT bonusPM_arme, poids_arme FROM arme WHERE id_arme='$id_arme'";
						$res = $mysqli->query ($sql);
						$tab1 = $res->fetch_assoc();
						
						$bonusPM_arme 	= $tab1["bonusPM_arme"];
						$poids_arme		= $tab1["poids_arme"];
						
						if($bonusPM_arme >= 0 && $bonusPM_arme <= $pm_perso) {
							
							// mise a jour pa perso
							$sql = "UPDATE perso SET pa_perso=pa_perso-1, charge_perso=charge_perso+$poids_arme WHERE id_perso='$id_perso'";
							$mysqli->query($sql);
							
							// mise a jour equipe perso
							$sql = "UPDATE perso_as_arme SET est_portee='0' WHERE id_arme='$id_arme' AND id_perso='$id_perso' AND est_portee='1' LIMIT 1";
							$mysqli->query($sql);
							
							$mess = "Vous venez de vous desequiper d'une arme.";
							
							if($bonusPM_arme != 0){
								$sql_u = "UPDATE perso SET bonusPM_perso=bonusPM_perso-$bonusPM_arme WHERE id_perso='$id_perso'";
								$mysqli->query($sql_u);
							}
						}
						else {
							$mess_erreur = "Vous devez posseder au moins ".$bonusPM_arme." PM pour rengainer cette arme !";
						}
					}
				}
				else {
					$mess_erreur = "Vous n'avez pas assez de pa pour effectuer cette action !";
				}
			}
			
			// recuperation des donnees des armes que possede le perso et qui peuvent être équipées
			$sql = "SELECT * FROM perso_as_arme, arme, arme_as_type_unite, perso 
					WHERE perso_as_arme.id_perso='$id_perso' 
					AND arme.id_arme = perso_as_arme.id_arme
					AND perso_as_arme.id_perso = perso.id_perso 
					AND perso.type_perso = arme_as_type_unite.id_type_unite
					AND arme_as_type_unite.id_arme = arme.id_arme";
			$res = $mysqli->query($sql);
			$i = 0;
			$j = 0;
			$nb_champ = $res->field_count;
			
			while ($tab = $res->fetch_assoc()) {
				if ($tab["est_portee"]) {
					for ($k = 0; $k < $nb_champ; $k++) {
						$nom = $res->fetch_field_direct($k);
						$t_porte[$nom->name][$i] = $tab[$nom->name];
					}
					$i++;
				}
				else {
					for ($k = 0; $k < $nb_champ; $k++) {
						$nom = $res->fetch_field_direct($k);
						$t_equip[$nom->name][$j] = $tab[$nom->name];
					}
					$j++;
				}
			}
			
			?>
			<table border=0 width='100%'>
				<tr>
					<td align='center'><font color='red'><?php echo $mess_erreur; ?></font><font color='blue'><?php echo $mess; ?></font></td>
				</tr>
			</table>
			
			<div align='center'><a href="equipement.php" class='btn btn-primary'>Page Equipement</a></div>
			
			<center><h3>Les armes que vous avez dans votre sac :</h3></center>
			<table border='1' align='center'>
			<?php 
			$poids_final = 0.0;
			$poids_total = 0.0;
			
			echo "<tr><th>nom</th><th>portee</th><th>cout en pa</th><th>degats</th><th>degats de zone ?</th><th>port</th><th>poids</th><th>description</th></tr>"; 
			if ($j == 0){
				echo "<tr><td colspan=8><i>Vous n'avez aucune arme dans votre sac.</i></td></tr>";
			}
			else {
				for ($l = 0; $l < $j; $l++) {
					
					echo "<tr>";
					// Nom de l'arme
					echo "<td align=\"center\">".stripslashes($t_equip["nom_arme"][$l])."</td>";
					// Portee de l'arme
					if ($t_equip["porteeMax_arme"][$l] == 1){
						echo "<td align=\"center\">".$t_equip["porteeMax_arme"][$l]." (CàC)</td>";
					}
					else {
						echo "<td align=\"center\">".$t_equip["porteeMin_arme"][$l]." - ".$t_equip["porteeMax_arme"][$l]."</td>";
					}
					// Cout en pa
					echo "<td align=\"center\">".$t_equip["coutPa_arme"][$l]."</td>";
					
					// Degats de l'arme
					if($t_equip["degatMin_arme"][$l] && $t_equip["degatMax_arme"][$l]){
						
						echo "<td align=\"center\">";
						
						if ($t_equip["valeur_des_arme"][$l]) {
							echo $t_equip["degatMin_arme"][$l] . "D".$t_equip["valeur_des_arme"][$l];
						} else {
							echo $t_equip["degatMin_arme"][$l]." - ".$t_equip["degatMax_arme"][$l];
						}
						
						echo "</td>";
						
					}
					else {
						echo "<td align=\"center\">D";
						
						if($t_equip["multiplicateurMin_degats"][$l] != 1) {
							echo "*".$t_equip["multiplicateurMin_degats"][$l];
						}
						
						echo " + ".$t_equip["additionMin_degats"][$l]." -- D";
						
						if($t_equip["multiplicateurMax_degats"][$l] != 1) {
							echo "*".$t_equip["multiplicateurMin_degats"][$l];
						}
						
						echo " + ".$t_equip["additionMax_degats"][$l];
						
						echo "</td>";
					}
					
					// Degats de zone ?
					echo "<td align=\"center\">";
					echo $t_equip["degatZone_arme"][$l]?"oui":"non";
					echo "</td>";
					// mains ?
					echo "<td align=\"center\">".$t_equip["main"][$l]." main";
					if($t_equip["main"][$l] > 1)
						echo "s";
					echo "</td>";
					// Poid de l'arme
					echo "<td align=\"center\">".$t_equip["poids_arme"][$l]."</td>";
					// Description de l'arme
					echo "<td align=\"center\">".stripslashes($t_equip["description_arme"][$l])."</td>";
					echo "</tr>";
					$poids_total = $poids_total + $t_equip["poids_arme"][$l];
					
				}
				echo "<tr><td align=\"center\">total</td><td colspan='5'>&nbsp;</td><td align=\"center\">$poids_total</td><td>&nbsp;</td></tr>";
			}
			?>
			</table>
			
			<center><h3>Les armes dont vous êtes equipé :</h3></center>
			<table border='1' align='center'>
			<?php
			$poids_final += $poids_total;
			$poids_total = 0.0;
			echo "<tr><th>nom</th><th>portee</th><th>cout en pa</th><th>degats</th><th>degats de zone ?</th><th>poids</th><th>description</th></tr>"; 
			if ($i == 0){
				echo "<tr><td colspan=8><i>Vous n'étes pas equipé.</i></td></tr>";
			}
			else {
				for ($l = 0; $l < $i; $l++) {
					echo "<tr>";
					// Nom de l'arme
					echo "<td align=\"center\">".stripslashes($t_porte["nom_arme"][$l])."</td>";
					// Portee de l'arme
					if ($t_porte["porteeMax_arme"][$l] == 1){
						echo "<td align=\"center\">".$t_porte["porteeMax_arme"][$l]." (CàC)</td>";
					}
					else {
						echo "<td align=\"center\">".$t_porte["porteeMin_arme"][$l]." - ".$t_porte["porteeMax_arme"][$l]."</td>";
					}
					// Cout en pa
					echo "<td align=\"center\">".$t_porte["coutPa_arme"][$l]."</td>";
					
					// Degats de l'arme
					if($t_porte["degatMin_arme"][$l] && $t_porte["degatMax_arme"][$l]){
						
						echo "<td align=\"center\">";
						
						if ($t_porte["valeur_des_arme"][$l]) {
							echo $t_porte["degatMin_arme"][$l] . "D".$t_porte["valeur_des_arme"][$l];
						} else {
							echo $t_porte["degatMin_arme"][$l]." - ".$t_porte["degatMax_arme"][$l];
						}
						
						echo "</td>";
					}
					else {
						
						echo "<td align=\"center\">D";
						
						if($t_porte["multiplicateurMin_degats"][$l] != 1) {
							echo "*".$t_porte["multiplicateurMin_degats"][$l];
						}
						
						echo " + ".$t_porte["additionMin_degats"][$l]." -- D";
						
						if($t_porte["multiplicateurMax_degats"][$l] != 1) {
							echo "*".$t_porte["multiplicateurMin_degats"][$l];
						}
						
						echo " + ".$t_porte["additionMax_degats"][$l];
						
						echo "</td>";
					}
					
					// Degats de zone ?
					echo "<td align=\"center\">";
					echo $t_porte["degatZone_arme"][$l]?"oui":"non";
					echo "</td>";
					// Poid de l'arme
					echo "<td align=\"center\">".$t_porte["poids_arme"][$l]."</td>";
					// Description de l'arme
					echo "<td align=\"center\">".stripslashes($t_porte["description_arme"][$l])."</td>";
					echo "</tr>";
					$poids_total = $poids_total + $t_porte["poids_arme"][$l];
				}
				echo "<tr><td align=\"center\">total</td><td colspan='4'>&nbsp;</td><td align=\"center\">$poids_total</td><td>&nbsp;</td></tr>";
			}
			$poids_final += $poids_total;
			?>
			</table>
			<br />
			<?php echo "<center>Votre charge totale est de : <b>$poids_final</b></center>"; ?>
			<center><h3>Changer votre equipement :</h3></center>
			<table align='center'>
				<form action="equipement_armes.php" method="post">
				<tr>
					<td>&nbsp;&nbsp;&nbsp;&nbsp; M'equiper de : <select name="id_equip"><?php for ($l = 0; $l < $j; $l++) echo "<option value=\"".$t_equip["id_arme"][$l]."\">".$t_equip["nom_arme"][$l]."</option>"; ?> </select>&nbsp;<input type="submit" name="equiper" value="ok">&nbsp;(1pa)</td>
				</tr>
				<tr>
					<td>Me desequiper de : <select name="id_desequip"><?php for ($l = 0; $l < $i; $l++) echo "<option value=\"".$t_porte["id_arme"][$l]."\">".$t_porte["nom_arme"][$l]."</option>"; ?> </select>&nbsp;<input type="submit" name="desequiper" value="ok">&nbsp;(1pa)</td>
				</tr>
				</form>
			</table>
	
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
	}
}
?>
