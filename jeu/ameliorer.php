<?php
session_start();
require_once("../fonctions.php");
require_once("f_ameliore.php");

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
			
			$sql = "SELECT pi_perso, pvMax_perso, pmMax_perso, paMax_perso, perception_perso, recup_perso, type_perso, chef FROM perso WHERE id_perso ='$id'";
			$res = $mysqli->query($sql);
			$tab = $res->fetch_assoc();
			
			$pm 	= $tab["pmMax_perso"];
			$pa 	= $tab["paMax_perso"];
			$per 	= $tab["perception_perso"];
			$pv 	= $tab["pvMax_perso"];
			$rec 	= $tab["recup_perso"];
			$pi 	= $tab["pi_perso"];
			$type	= $tab["type_perso"];
			$chef 	= $tab["chef"];
	
	?>
<html>
	<head>
		<title>Nord VS Sud</title>
		
		<!-- Required meta tags -->
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		
		<!-- Bootstrap CSS -->
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
		
		<link rel="stylesheet" type="text/css" media="screen" href="onglet.css" title="Version 1" />
	</head>
	
	<body>
		<div id="header">
			<ul>
				<li><a href="profil.php">Profil</a></li>
				<li id="current"><a href="#">Améliorer son perso</a></li>
				<?php
				if($chef) {
					echo "<li><a href=\"recrutement.php\">Recruter des grouillots</a></li>";
					echo "<li><a href=\"gestion_grouillot.php\">Gérer ses grouillots</a></li>";
				}
				?>
				<li><a href="equipement.php">Equiper son perso</a></li>
				<li><a href="compte.php">Gérer son Compte</a></li>
			</ul>
		</div>
		
		<br /><br /><center><h1>Améliorer ce perso</h1></center>
		
		<br /><br />
			
		<p align="center"><input type="button" value="Fermer cette fenêtre" onclick="window.close()"></p>
		<center><font color='red'>Attention, tout clic sur <b>>> monter</b> entraine une amélioration immédiate et irréversible</font></center>
	<?php
	if (isset($_POST["pv"])) {
		
		// calcul du nombre de pi necessaire
		$nbpi_pv = ameliore_pv($mysqli, $pv, $type);
		
		// verification que le perso a assez de pi
		if ($pi >= $nbpi_pv){
			
			$sql2 = "UPDATE perso SET pvMax_perso=pvMax_perso+1, pi_perso=pi_perso-$nbpi_pv WHERE id_perso ='$id'";
			$mysqli->query($sql2);
			
			$pv = $pv + 1;
			$pi = $pi - $nbpi_pv;
		}
		else {
			echo "<center><font color=red>Vous n'avez pas assez de pi</font></center>";
		}
	}
	
	if (isset($_POST["pm"])) {
		
		// calcul du nombre de pi necessaire
		$nbpi_pm = ameliore_pm($mysqli, $pm, $type);
	
		// verification que le perso a assez de pi
		if($pi >= $nbpi_pm) {
			
			$sql2 = "UPDATE perso SET pmMax_perso=pmMax_perso+1, pi_perso=pi_perso-$nbpi_pm WHERE id_perso ='$id'";
			$mysqli->query($sql2);
			
			$pm = $pm + 1;
			$pi = $pi - $nbpi_pm;
		}
		else {
			echo "<center><font color=red>Vous n'avez pas assez de pi</font></center>";
		}
	}
	
	if (isset($_POST["pa"])) {
		
		// calcul du nombre de pi necessaire
		$nbpi_pa = ameliore_pa($mysqli, $pa, $type);
	
		// verification que le perso a assez de pi	
		if($pi >= $nbpi_pa) {
			
			$sql2 = "UPDATE perso SET paMax_perso=paMax_perso+1, pi_perso=pi_perso-$nbpi_pa WHERE id_perso ='$id'";
			$mysqli->query($sql2);
			
			$pa = $pa + 1;
			$pi = $pi - $nbpi_pa;
		}
		else {
			echo "<center><font color=red>Vous n'avez pas assez de pi</font></center>";
		}
	}
	
	if (isset($_POST["per"])) {
		
		// calcul du nombre de pi necessaire
		$nbpi_perc = ameliore_perc($mysqli, $per, $type);
	
		// verification que le perso a assez de pi	
		if($pi >= $nbpi_perc) {
			
			$sql2 = "UPDATE perso SET perception_perso=perception_perso+1, pi_perso=pi_perso-$nbpi_perc WHERE id_perso ='$id'";
			$mysqli->query($sql2);
			
			$per = $per + 1;
			$pi = $pi - $nbpi_perc;
		}
		else {
			echo "<center><font color=red>Vous n'avez pas assez de pi</font></center>";
		}
	}
	
	if (isset($_POST["rec"])) {
		
		// calcul du nombre de pi necessaire
		$nbpi_recup = ameliore_recup($mysqli, $rec, $type);
	
		// verification que le perso a assez de pi
		if($pi >= $nbpi_recup) {
			
			$sql2 = "UPDATE perso SET recup_perso=recup_perso+1, pi_perso=pi_perso-$nbpi_recup WHERE id_perso ='$id'";
			$mysqli->query($sql2);
			
			$rec = $rec + 1;
			$pi = $pi - $nbpi_recup;
		}
		else {
			echo "<center><font color=red>Vous n'avez pas assez de pi</font></center>";
		}
	}
	
	?>
			<form method="post" action="ameliorer.php">
				<br>
				<center>
				Points d'investissements : <input type="text" size="2" maxlength="5" value="<?php echo $pi ?>" disabled>
				</center>
				
				<table border="1" align="center"> 
				
					<tr><td>Points de vie</td><td><input type="text" size="3" maxlength="3" value="<?php echo $pv; ?>" disabled>&nbsp;<input type="submit" name="pv" value=">> monter">&nbsp;&nbsp;<?php echo "Cout : ".ameliore_pv($mysqli, $pv, $type)." pi"; ?></td></tr>
					
					<tr><td>Points de mouvement</td><td><input type="text" size="3" maxlength="3" value="<?php echo $pm; ?>" disabled>&nbsp;<input type="submit" name="pm" value=">> monter">&nbsp;&nbsp;<?php echo "Cout : ".ameliore_pm($mysqli, $pm, $type)." pi"; ?></td></tr>
					
					<tr><td>Points d'action</td><td><input type="text" size="3" maxlength="3" value="<?php echo $pa; ?>" disabled>&nbsp;<input type="submit" name="pa" value=">> monter">&nbsp;&nbsp;<?php echo "Cout : ".ameliore_pa($mysqli, $pa, $type)." pi"; ?></td></tr>
					
					<tr><td>Perception</td><td><input type="text" size="3" maxlength="3" value="<?php echo $per; ?>" disabled>&nbsp;<input type="submit" name="per" value=">> monter">&nbsp;&nbsp;<?php echo "Cout : ".ameliore_perc($mysqli, $per, $type)." pi"; ?></td></tr>
					
					<tr><td>Recuperation</td><td><input type="text" size="3" maxlength="3" value="<?php echo $rec; ?>" disabled>&nbsp;<input type="submit" name="rec" value=">> monter">&nbsp;&nbsp;<?php echo "Cout : ".ameliore_recup($mysqli, $rec, $type)." pi"; ?></td></tr>
					
				</table>
			</form>
			
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
	
	header("Location: ../index2.php");
}
?>