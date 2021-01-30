<?php
session_start();
require_once("../fonctions.php");
require_once("f_combat.php");
require_once("f_carte.php");

$mysqli = db_connexion();

include ('../nb_online.php');

// recuperation config jeu
$sql = "SELECT valeur_config FROM config_jeu WHERE code_config='disponible'";
$res = $mysqli->query($sql);
$t_dispo = $res->fetch_assoc();

$dispo = $t_dispo["valeur_config"];

if($dispo == '1'){
	if(isset($_SESSION["id_perso"])){
		
		$id_perso = $_SESSION['id_perso'];
		$date = time();
	
		$sql = "SELECT pv_perso, or_perso, UNIX_TIMESTAMP(DLA_perso) as DLA, est_gele FROM perso WHERE id_perso='$id_perso'";
		$res = $mysqli->query($sql);
		$tpv = $res->fetch_assoc();
		
		$testpv = $tpv['pv_perso'];
		$or = $tpv["or_perso"];
		$dla = $tpv["DLA"];
		$est_gele = $tpv["est_gele"];
		
		$config = '1';
		
		// verification si le perso est encore en vie
		if ($testpv <= 0) { // le perso est mort
			// unset($_SESSION['ma_variable']); 
			session_unregister('deDefense');
			session_unregister('deAttaque');
			header("Location:../tour.php"); //tour.php se charge de verifier si nouveau tour
		}
		else { // le perso est vivant

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Nord VS Sud</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../style.css" rel="stylesheet" type="text/css">
</head>

<body>
<div align="center"><h2>Vos Armures</h2></div>

<?php
			$mess = "";

			// On veut equiper ou desequiper une armure
			if (isset($_POST["equiper"]) || isset($_POST["desequiper"])) {
			
				$sql = "SELECT pa_perso FROM perso WHERE id_perso='$id_perso'";
				$res = $mysqli->query($sql);
				$tab = $res->fetch_assoc();
				
				$pa_perso = $tab["pa_perso"];
				
				if ($pa_perso > 0) {
				
					// equiper armure
					if (isset($_POST["equiper"]) && isset($_POST["id_equip"])) {
					
						$id_armure = $_POST["id_equip"];
						$sql = "SELECT nom_armure, corps_armure, bonusPM_armure FROM armure WHERE id_armure='$id_armure'";
						$res = $mysqli->query ($sql);
						$tab1 = $res->fetch_assoc();
						
						$nom_armure = $tab1["nom_armure"];
						$corps_armure = $tab1["corps_armure"];
						$bonusPM_armure = $tab1["bonusPM_armure"];
				
						// Verification si le perso est deja equipee d'une armure du meme type
						$sql2 = "SELECT nom_armure, armure.corps_armure 
									FROM perso_as_armure, armure 
									WHERE id_perso='$id_perso' 
									AND est_portee='1' 
									AND armure.corps_armure = '$corps_armure'
									AND armure.id_armure=perso_as_armure.id_armure";
									
						$res2 = $mysqli->query ($sql2);
						
						// deja equipee
						if ($res2->num_rows) {
							$mess = "<font color='red'>Vous êtes déjà équipé d'une armure de ce type</font>";
						}
						else {
							// On equipe
							// mise a jour pa perso
							$sql = "UPDATE perso SET pa_perso=pa_perso-1 WHERE id_perso='$id_perso'";
							$mysqli->query($sql);
							
							// mise a jour equipe perso
							$sql = "UPDATE perso_as_armure SET est_portee='1', corps_armure=$corps_armure WHERE id_armure='$id_armure' AND id_perso='$id_perso' LIMIT 1";
							$mysqli->query($sql);
							
							if($bonusPM_armure < 0){
								$sql_u = "UPDATE perso SET bonusPM_perso=bonusPM_perso+$bonusPM_armure WHERE id_perso='$id_perso'";
								$mysqli->query($sql_u);
							}
							
							$mess = "<font color='blue'>Vous venez de vous équiper d'une armure</font>";
						}
					}
					// desequiper armure
					if (isset($_POST["desequiper"]) && isset($_POST["id_desequip"])) {
					
						$id_armure = $_POST["id_desequip"];
						
						$sql = "SELECT bonusPM_armure FROM armure WHERE id_armure='$id_armure'";
						$res = $mysqli->query ($sql);
						$tab1 = $res->fetch_assoc();
						
						$bonusPM_armure = $tab1["bonusPM_armure"];
						
						// mise a jour pa perso
						$sql = "UPDATE perso SET pa_perso=pa_perso-1 WHERE id_perso='$id_perso'";
						$mysqli->query($sql);
						
						// mise a jour equipe perso
						$sql = "UPDATE perso_as_armure SET est_portee='0' WHERE id_armure='$id_armure' AND id_perso='$id_perso' and est_portee='1' LIMIT 1";
						$mysqli->query($sql);
						
						if($bonusPM_armure < 0){
							$sql_u = "UPDATE perso SET bonusPM_perso=bonusPM_perso-$bonusPM_armure WHERE id_perso='$id_perso'";
							$mysqli->query($sql_u);
						}
						
						$mess = "<font color='blue'>Vous venez de vous desequiper d'une armure</font>";
					}
				}
				else {
					$mess = "<font color='red'>Vous n'avez plus assez de PA</font>";
				}
			}
			
			// recuperation des donnees des armures que possede le perso
			$sql = "SELECT * FROM perso_as_armure, armure WHERE id_perso='$id_perso' AND armure.id_armure=perso_as_armure.id_armure";
			$res = $mysqli->query($sql);
			$i = 0;
			$j = 0;
			$nb_champ = $res->field_count;
			while ($tab = $res->fetch_assoc()) {
				if ($tab["est_portee"]) {
					for ($k = 0; $k < $nb_champ; $k++) {
						$nom = $res->field_name($k);
						$t_porte[$nom][$i] = $tab[$nom];
					}
					$i++;
				}
				else {
					for ($k = 0; $k < $nb_champ; $k++) {
						$nom = $res->field_name($k);
						$t_equip[$nom][$j] = $tab[$nom];
					}
					$j++;
				}
			}
			
			?>
			<table border=0 width='100%'><tr><td align='center'><?php echo $mess; ?></td></tr></table>
			
			<center><a href="equipement.php">[ Page Equipement ]</a></center>
			
			<center><h3>Les armures que vous avez dans votre sac :</h3></center>
			<table border='1' align='center' width='70%'>
			<?php 
			$poids_final = 0.0;
			$poids_total = 0.0;
			echo "<tr><th>nom</th><th>defense</th><th>poids</th><th>description</th></tr>"; 
			if ($j == 0){
				echo "<tr><td colspan=4><i>Vous n'avez aucune armure dans votre sac.</i></td></tr>";
			}
			else {
				for ($l = 0; $l < $j; $l++) {
					
					echo "<tr>";
					// Nom de l'armure
					echo "<td align=\"center\">".stripslashes($t_equip["nom_armure"][$l])."</td>";
					// Defense de l'armure
					echo "<td align=\"center\">".$t_equip["bonusDefense_armure"][$l]."</td>";
					// Poid de l'armure
					echo "<td align=\"center\">".$t_equip["poids_armure"][$l]."</td>";
					// Description de l'armure
					echo "<td align=\"center\">".stripslashes($t_equip["description_armure"][$l])."</td>";
					echo "</tr>";
					$poids_total = $poids_total + $t_equip["poids_armure"][$l];
					
				}
				echo "<tr><td align=\"center\">total</td><td>&nbsp;</td><td align=\"center\">$poids_total</td><td>&nbsp;</td></tr>";
			}
			?>
			</table>
			
			<?php
			// calcul du malus en defense
			$malus = 0;
			
			// On verifie s'il porte un casque
			$sql_c = "SELECT id_armure FROM perso_as_armure WHERE id_perso='$id_perso' AND corps_armure='1' AND est_portee='1'";
			$res_c = $mysqli->query($sql_c);
			$ok_c = $res_c->num_rows;
			if($ok_c == 0) {
				$malus = $malus - 1;
			}
				
			// On verifie s'il porte une armure de corps
			$sql_co = "SELECT id_armure FROM perso_as_armure WHERE id_perso='$id_perso' AND corps_armure='3' AND est_portee='1'";
			$res_co = $mysqli->query($sql_co);
			$ok_co = $res_co->num_rows;
			if($ok_co == 0) {
				$malus = $malus - 1;
			}
				
			// On verifie s'il porte un pantalon
			$sql_p = "SELECT id_armure FROM perso_as_armure WHERE id_perso='$id_perso' AND corps_armure='8' AND est_portee='1'";
			$res_p = $mysqli->query($sql_p);
			$ok_p = $res_p->num_rows;
			if($ok_p == 0) {
				$malus = $malus - 1;
			}
				
			// On verifie s'il porte des bottes
			$sql_c = "SELECT id_armure FROM perso_as_armure WHERE id_perso='$id_perso' AND corps_armure='9' AND est_portee='1'";
			$res_c = $mysqli->query($sql_c);
			$ok_c = $res_c->num_rows;
			if($ok_c == 0) {
				$malus = $malus - 1;
			}
			
			?>
			
			<center><h3>Les armures dont vous êtes equipé :</h3></center>
			<table border='1' align='center' width='70%'>
			<?php
			$poids_final += $poids_total;
			$poids_total = 0.0;
			$defense_total = 0.0;
			echo "<tr><th>Partie du corps</th><th>nom</th><th>defense</th><th>poids</th><th>description</th></tr>"; 
			if ($i == 0){
				echo "<tr><td colspan=5><i>Vous n'êtes pas equipé.</i></td></tr>";
			}
			else {
				for ($l = 0; $l < $i; $l++) {
					echo "<tr>";
					// Main
					if($t_porte["corps_armure"][$l] == "1")
						echo "<td align=\"center\">casque</td>";
					if($t_porte["corps_armure"][$l] == "2")
						echo "<td align=\"center\">collier</td>";
					if($t_porte["corps_armure"][$l] == "3")
						echo "<td align=\"center\">corps</td>";
					if($t_porte["corps_armure"][$l] == "6")
						echo "<td align=\"center\">gants</td>";
					if($t_porte["corps_armure"][$l] == "7")
						echo "<td align=\"center\">ceinture</td>";
					if($t_porte["corps_armure"][$l] == "8")
						echo "<td align=\"center\">pantalon</td>";
					if($t_porte["corps_armure"][$l] == "9")
						echo "<td align=\"center\">bottes</td>";
					if($t_porte["corps_armure"][$l] == "10")
						echo "<td align=\"center\">item</td>";
					// Nom de l'armure
					echo "<td align=\"center\">".stripslashes($t_porte["nom_armure"][$l])."</td>";
					// Defense de l'armure
					echo "<td align=\"center\">".$t_porte["bonusDefense_armure"][$l]."</td>";
					// Poid de l'armure
					echo "<td align=\"center\">".$t_porte["poids_armure"][$l]."</td>";
					// Description de l'armure
					echo "<td align=\"center\">".stripslashes($t_porte["description_armure"][$l])."</td>";
					echo "</tr>";
					$poids_total = $poids_total + $t_porte["poids_armure"][$l];
					$defense_total = $defense_total + $t_porte["bonusDefense_armure"][$l];
				}
				$defense_total = $defense_total + $malus;
				echo "<tr><td align=\"center\">total</td><td>&nbsp;</td><td align=\"center\">$defense_total</td><td align=\"center\">$poids_total</td><td>&nbsp;</td></tr>";
			}
			$poids_final += $poids_total;
			?>
			</table>
			<br />
			<?php 
			if($malus < 0)
				echo "<center><font color='red'>Vous avez un malus de defense de <b>$malus</b> car certaines parties de votre corps sont nues. Pensez à vous couvrir !</font></center>";
			echo "<center>Votre charge totale est de : <b>$poids_final</b></center>"; 
			
			?>
			<center><h3>Changer votre equipement :</h3></center>
			<table align='center'><form action="equipement_armures.php" method="post">
				<tr>
					<td>M'equiper de: <select name="id_equip"><?php for ($l = 0; $l < $j; $l++) echo "<option value=\"".$t_equip["id_armure"][$l]."\">".$t_equip["nom_armure"][$l]."</option>"; ?> </select>&nbsp;<input type="submit" name="equiper" value="ok">&nbsp;(1pa)</td>
					<td>&nbsp;&nbsp;&nbsp;&nbsp; Me desequiper de : <select name="id_desequip"><?php for ($l = 0; $l < $i; $l++) echo "<option value=\"".$t_porte["id_armure"][$l]."\">".$t_porte["nom_armure"][$l]."</option>"; ?> </select>&nbsp;<input type="submit" name="desequiper" value="ok">&nbsp;(1pa)</td>
				</tr>
			</form></table>
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