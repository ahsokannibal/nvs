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
		
		$sql = "SELECT pv_perso FROM perso WHERE id_perso='$id'";
		$res = $mysqli->query($sql);
		$tpv = $res->fetch_assoc();
		
		$testpv = $tpv['pv_perso'];
		
		if ($testpv <= 0) {
			echo "<font color=red>Vous êtes mort...</font>";
		}
		else {
			
			$sql = "SELECT pc_perso, chef, clan FROM perso WHERE id_perso ='$id'";
			$res = $mysqli->query($sql);
			$tab = $res->fetch_assoc();
			
			$pc 	= $tab["pc_perso"];
			$chef 	= $tab["chef"];
			$clan	= $tab["clan"];
			
			if ($clan == 1) {
				$camp = "nord";
			} else if ($clan == 2) {
				$camp = "sud";
			} else {
				// ???
				$camp = "nord";
			}
	
	?>
<html>
	<head>
		<title>Nord VS Sud</title>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			<meta http-equiv="Content-Language" content="fr" />
		<link rel="stylesheet" type="text/css" media="screen" href="onglet.css" title="Version 1" />
	</head>
	
	<body>
		<div id="header">
			<ul>
				<li><a href="profil.php">Profil</a></li>
				<li><a href="ameliorer.php">Améliorer son perso</a></li>
				<?php
				if($chef) {
					echo "<li id=\"current\"><a href=\"#\">Recruter des grouillots</a></li>";
				}
				?>
				<li><a href="equipement.php">Equiper son perso</a></li>
				<li><a href="compte.php">Gérer son Compte</a></li>
			</ul>
		</div>
	
		<br /><br /><center><h1>Recruter un grouillot</h1></center>
		
		<div align=center><input type="button" value="Fermer cette fenêtre" onclick="window.close()"></div>
		<br />
<?php
			// Récupération des grouillots recrutable
			$sql = "SELECT * FROM type_unite WHERE id_unite != '1'";
			$res = $mysqli->query($sql);
			
			echo "<table align='center' border='1'>";
			echo "	<tr>";
			echo "		<th></th><th>Unité</th><th>PA</th><th>PV</th><th>PM</th><th>Recupération</th><th>Perception</th><th>Protection</th><th>Description</th><th>Cout PG</th>";
			echo "	</tr>";
			
			while ($tab = $res->fetch_assoc()) {
				
				$nom_unite 			= $tab["nom_unite"];
				$description_unite 	= $tab["description_unite"];
				$perception_unite 	= $tab["perception_unite"];
				$protection_unite 	= $tab["protection_unite"];
				$recup_unite 		= $tab["recup_unite"];
				$pv_unite 			= $tab["pv_unite"];
				$pa_unite 			= $tab["pa_unite"];
				$pm_unite 			= $tab["pm_unite"];
				$image_unite 		= $tab["image_unite"];
				$cout_pg_unite 		= $tab["cout_pg"];
				
				$image_affiche = $image_unite."_".$camp.".gif";
				
				echo "	<tr>";
				echo "		<td align='center'><img src='../images_perso/".$image_affiche."' alt='".$nom_unite."'/></td>";
				echo "		<td align='center'>$nom_unite</td>";
				echo "		<td align='center'>$pa_unite</td>";
				echo "		<td align='center'>$pv_unite</td>";
				echo "		<td align='center'>$pm_unite</td>";
				echo "		<td align='center'>$recup_unite</td>";
				echo "		<td align='center'>$perception_unite</td>";
				echo "		<td align='center'>$protection_unite</td>";
				echo "		<td align='center'>$description_unite</td>";
				echo "		<td align='center'>$cout_pg_unite PG</td>";
				echo "	</tr>";
				
			}
			
			echo "</table>";
		}
	}
	else{
		echo "<font color=red>Vous ne pouvez pas accéder à cette page, veuillez vous loguer.</font>";
	}
	?>
	</body>
</html>
<?php
}
else {
	// logout
	$_SESSION = array(); // On écrase le tableau de session
	session_destroy(); // On détruit la session
	
	header("Location: index2.php");
}
?>