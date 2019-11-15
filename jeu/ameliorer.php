<?php
session_start();
require_once("../fonctions.php");
require_once("f_ameliore.php");

$mysqli = db_connexion();

include ('../nb_online.php');

// recupération config jeu
$sql = "SELECT disponible FROM config_jeu";
$res = $mysqli->query($sql);
$t_dispo = $res->fetch_assoc();
$dispo = $t_dispo["disponible"];

if($dispo){

	if (@$_SESSION["id_perso"]) {
		//recuperation des varaibles de sessions
		$id = $_SESSION["id_perso"];
		$sql = "SELECT pv_perso FROM perso WHERE ID_perso='$id'";
		$res = $mysqli->query($sql);
		$tpv = $res->fetch_assoc();
		$testpv = $tpv['pv_perso'];
		if ($testpv <= 0) {
			echo "<font color=red>Vous êtes mort...</font>";
		}
		else {
	?>
	<html>
	<head>
	  <title>Nord VS Sud</title>
	  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	   <meta http-equiv="Content-Language" content="fr" />
	  <link rel="stylesheet" type="text/css" media="screen" href="onglet.css" title="Version 1" />
	
	</head>
	<body>
	<div id="header">
	  <ul>
		<li><a href="profil.php">Profil</a></li>
		<li id="current"><a href="ameliorer.php">Améliorer son perso</a></li>
		<li><a href="equipement.php">Equiper son perso</a></li>
		<li><a href="pnjdex.php">PNJ Dex</a></li>
		<li><a href="mes_monstres.php">Mes monstres</a></li>
		<li><a href="compte.php">Gérer son Compte</a></li>
	  </ul>
	</div>
	<br /><br /><br /><br />
	<p align="center"><input type="button" value="Fermer cette fen�tre" onclick="window.close()"></p>
	<center><font color='red'>Attention, tout clic sur <b>>> monter</b> entraine une amélioration immédiate et irréversible</font></center>
	<?php
	$sql = "SELECT pi_perso, pvMax_perso, pmMax_perso, paMax_perso, perception_perso, recup_perso, deAttaque_perso, deDefense_perso, chargeMax_perso FROM perso WHERE id_perso ='$id'";
	$res = $mysqli->query($sql);
	$tab = $res->fetch_assoc();
	
	$pm = $tab["pmMax_perso"];
	$pa = $tab["paMax_perso"];
	$per = $tab["perception_perso"];
	$pv = $tab["pvMax_perso"];
	$rec = $tab["recup_perso"];
	$deAttaque_p = $tab["deAttaque_perso"];
	$deDefense_p = $tab["deDefense_perso"];
	$ch = $tab["chargeMax_perso"];
	$pi = $tab["pi_perso"];
	
	$nbd = $deAttaque_p + $deDefense_p;
	
	if (isset($_POST["pv"])) {
		// calcul du nombre de pi necessaire
		$nbpi_pv = ameliore_pv($pv);
		
		// verification que le perso a assez de pi
		if ($pi >= $nbpi_pv){
			$sql2 = "UPDATE perso SET pvMax_perso=pvMax_perso+1, pi_perso=pi_perso-$nbpi_pv WHERE id_perso ='$id'";
			$mysqli->query($sql2);
			$pv = $pv + 1;
			$pi = $pi - $nbpi_pv;
		}
		else
			echo "<center><font color=red>Vous n'avez pas assez de pi</font></center>";
	}
	if (isset($_POST["pm"])) {
		// calcul du nombre de pi necessaire
		$nbpi_pm = ameliore_pm($pm);
	
		// verification que le perso a assez de pi
		if($pi >= $nbpi_pm) {
			$sql2 = "UPDATE perso SET pmMax_perso=pmMax_perso+1, pi_perso=pi_perso-$nbpi_pm WHERE id_perso ='$id'";
			$mysqli->query($sql2);
			$pm = $pm + 1;
			$pi = $pi - $nbpi_pm;
		}
		else
			echo "<center><font color=red>Vous n'avez pas assez de pi</font></center>";
	}
	if (isset($_POST["pa"])) {
		// calcul du nombre de pi necessaire
		$nbpi_pa = ameliore_pa($pa);
	
		// verification que le perso a assez de pi	
		if($pi >= $nbpi_pa) {
			$sql2 = "UPDATE perso SET paMax_perso=paMax_perso+1, pi_perso=pi_perso-$nbpi_pa WHERE id_perso ='$id'";
			$mysqli->query($sql2);
			$pa = $pa + 1;
			$pi = $pi - $nbpi_pa;
		}
		else
			echo "<center><font color=red>Vous n'avez pas assez de pi</font></center>";
	}
	if (isset($_POST["per"])) {
		// calcul du nombre de pi necessaire
		$nbpi_perc = ameliore_perc($per);
	
		// verification que le perso a assez de pi	
		if($pi >= $nbpi_perc) {
			$sql2 = "UPDATE perso SET perception_perso=perception_perso+1, pi_perso=pi_perso-$nbpi_perc WHERE id_perso ='$id'";
			$mysqli->query($sql2);
			$per = $per + 1;
			$pi = $pi - $nbpi_perc;
		}
		else
			echo "<center><font color=red>Vous n'avez pas assez de pi</font></center>";
	}
	if (isset($_POST["rec"])) {
		// calcul du nombre de pi necessaire
		$nbpi_recup = ameliore_recup($rec);
	
		// verification que le perso a assez de pi
		if($pi >= $nbpi_recup) {
			$sql2 = "UPDATE perso SET recup_perso=recup_perso+1, pi_perso=pi_perso-$nbpi_recup WHERE id_perso ='$id'";
			$mysqli->query($sql2);
			$rec = $rec + 1;
			$pi = $pi - $nbpi_recup;
		}
		else
			echo "<center><font color=red>Vous n'avez pas assez de pi</font></center>";
	}
	if (isset($_POST["ch"])) {
		// calcul du nombre de pi necessaire
		$nbpi_ch = ameliore_charge($ch);
	
		// verification que le perso a assez de pi
		if($pi >= $nbpi_ch) {
			$sql2 = "UPDATE perso SET chargeMax_perso=chargeMax_perso+1, pi_perso=pi_perso-$nbpi_ch WHERE id_perso ='$id'";
			$mysqli->query($sql2);
			$ch = $ch + 1;
			$pi = $pi - $nbpi_ch;
		}
		else
			echo "<center><font color=red>Vous n'avez pas assez de pi</font></center>";
	}
	if (isset($_POST["nbd"])) {
		// calcul du nombre de pi necessaire
		$nbpi_des = ameliore_des($nbd);
	
		// verification que le perso a assez de pi
		if($pi >= $nbpi_des) {
			$sql2 = "UPDATE perso SET deDefense_perso=deDefense_perso+1, pi_perso=pi_perso-$nbpi_des WHERE id_perso ='$id'";
			$mysqli->query($sql2);
			$nbd = $nbd + 1;
			$pi = $pi - $nbpi_des;
			$_SESSION['deDefense'] = $_SESSION['deDefense'] + 1; //$deDefense_p+1;
		}
		else
			echo "<center><font color=red>Vous n'avez pas assez de pi</font></center>";
	}
	?>
	<form method="post" action="ameliorer.php">
	<br>
	<center>
	Points d'investissements : <input type="text" size="2" maxlength="5" value="<?php echo $pi ?>" disabled>
	</center>
	
	<table border="1" align="center"> 
	
	<tr><td>Points de vie</td><td><input type="text" size="3" maxlength="3" value="<?php echo $pv; ?>" disabled>&nbsp;<input type="submit" name="pv" value=">> monter">&nbsp;&nbsp;<?php echo "Cout : ".ameliore_pv($pv)." pi"; ?></td></tr>
	
	<tr><td>Points de mouvement</td><td><input type="text" size="3" maxlength="3" value="<?php echo $pm; ?>" disabled>&nbsp;<input type="submit" name="pm" value=">> monter">&nbsp;&nbsp;<?php echo "Cout : ".ameliore_pm($pm)." pi"; ?></td></tr>
	
	<tr><td>Points d'action</td><td><input type="text" size="3" maxlength="3" value="<?php echo $pa; ?>" disabled>&nbsp;<input type="submit" name="pa" value=">> monter">&nbsp;&nbsp;<?php echo "Cout : ".ameliore_pa($pa)." pi"; ?></td></tr>
	
	<tr><td>Perception</td><td><input type="text" size="3" maxlength="3" value="<?php echo $per; ?>" disabled>&nbsp;<input type="submit" name="per" value=">> monter">&nbsp;&nbsp;<?php echo "Cout : ".ameliore_perc($per)." pi"; ?></td></tr>
	
	<tr><td>Recuperation</td><td><input type="text" size="3" maxlength="3" value="<?php echo $rec; ?>" disabled>&nbsp;<input type="submit" name="rec" value=">> monter">&nbsp;&nbsp;<?php echo "Cout : ".ameliore_recup($rec)." pi"; ?></td></tr>
	
	<tr><td>Charge Maximum (*)</td><td><input type="text" size="3" maxlength="3" value="<?php echo $ch; ?>" disabled>&nbsp;<input type="submit" name="ch" value=">> monter">&nbsp;&nbsp;<?php echo "Cout : ".ameliore_charge($ch)." pi"; ?></td></tr>
	
	<tr><td>Degats</td><td><input type="text" size="3" maxlength="3" value="<?php echo $deg; ?>" disabled>&nbsp;<input type="submit" name="deg" value=">> monter">&nbsp;&nbsp;<?php echo "Cout : ".ameliore_deg($deg)." pi"; ?></td></tr>
	
	<tr><td>Nombre de dés</td><td><input type="text" size="3" maxlength="3" value="<?php echo $nbd; ?>" disabled>&nbsp;<input type="submit" name="nbd" value=">> monter">&nbsp;&nbsp;<?php echo "Cout :".ameliore_des($nbd)." pi"; ?></td></tr>
	
	</table>
	</form>
	<p>&nbsp;&nbsp;&nbsp;&nbsp;(*) Pour avoir la charge maximale que peut porter votre personnage, il faut multiplier le nombre de points par 4.</p>
	</body>
	</html>
	<?php
		}
	}
	else{
		echo "<font color=red>Vous ne pouvez pas accéder à cette page, veuillez vous loguer.</font>";
	}
	?>
<?php
}
else {
	// logout
	$_SESSION = array(); // On écrase le tableau de session
	session_destroy(); // On détruit la session
	
	header("Location: index2.php");
}
?>