<?php
session_start();
require_once("../fonctions.php");
require_once("f_entete.php");

$mysqli = db_connexion();

include ('../nb_online.php');

if(@$_SESSION["id_perso"]){
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>CV</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../style.css" rel="stylesheet" type="text/css">
</head>
<body>
<div align="center"><h2>CV</h2></div>
<?php 
if(isset($_POST["id_info"])){
	// verifier que la valeur est valide
	$id_tmp = $_POST["id_info"];
	$verif = preg_match("#^[0-9]*[0-9]$#i","$id_tmp");
	if($verif){
		$id = $_POST["id_info"];
	}
	else {
		echo "<center><b>Erreur :</b> La valeur entrée n'est pas correcte !</center>";
	}
}
else {
	if(isset($_GET["infoid"])){
		// verifier que la valeur est valide
		$id_tmp = $_GET["infoid"];
		$verif = preg_match("#^[0-9]*[0-9]$#i","$id_tmp");
		if($verif){
			$id = $_GET["infoid"];
		}
		else {
			echo "<center><b>Erreur :</b> La valeur entrée n'est pas correcte !</center>";
		}		
	}	
	else{
		$id = $_SESSION["id_perso"];
	}
}
?>
<p align="center"><input type="button" value="Fermer cette fenêtre" onclick="window.close()"></p>
<?php
if(isset($id)){
	
	if($id < 10000){
		// verifier que le perso existe
		$sql = "SELECT id_perso FROM perso WHERE id_perso='$id'";
		$res = $mysqli->query($sql);
		$nb_p = $res->num_rows;
	}
	else {
		if($id < 50000){
			// verifier que le pnj existe
			$sql = "SELECT idInstance_pnj FROM instance_pnj WHERE idInstance_pnj='$id'";
			$res = $mysqli->query($sql);
			$nb_p = $res->num_rows;
		}
		else {
			// verifier que le batiment existe
			$sql = "SELECT id_instanceBat FROM instance_batiment WHERE id_instanceBat='$id'";
			$res = $mysqli->query($sql);
			$nb_p = $res->num_rows;
		}
	}

	if($nb_p == '1'){
		
		entete($mysqli, $id); 
		
		//Évènements spéciaux
		$sql = "SELECT * FROM evenement WHERE IDActeur_evenement='$id' AND special='1'";
		$res = $mysqli->query($sql);
		$nb_event = $res->num_rows;
		
		if ($nb_event) {
			
			echo "<center><font color=red><b>Évènements spéciaux</b></font></center>";
			echo "<center><table border=1 width=80%><tr><th width=25%>date</th><th>Évènement</th></tr>";
		
			while ($t = $res->fetch_assoc()){
				echo "<tr>";
				echo "<td>".$t['date_evenement']."</td><td align='center'>".$t['nomActeur_evenement']." ".stripslashes($t['phrase_evenement'])." ";
				if($_SESSION["id_perso"] == $id){
					echo stripslashes($t['effet_evenement'])."";
				}
				echo "</td></tr>";
			}
			echo "</table></center><br />";
		}
		
		
		//missions
		$count = 0;
		$sql = "SELECT * FROM evenement WHERE IDActeur_evenement='$id' AND special='2'";
		$res = $mysqli->query($sql);
		$nb_mission = $res->num_rows;
		
		if ($nb_mission) {
			
			echo "<center><font color=red><b>Missions</b></font></center>";
			echo "<center><table border=1 width=80%><tr><th width=25%>date</th><th>Évènement</th></tr>";
			
			while ($t = $res->fetch_assoc()){
				$count++;
				echo "<tr>";
				echo "<td>".$t['date_evenement']."</td><td align='center'>".$t['nomActeur_evenement']." ".stripslashes($t['phrase_evenement'])." ";
				if($_SESSION["id_perso"] == $id){
					echo stripslashes($t['effet_evenement'])."";
				}
				echo "</td></tr>";
			}
			echo "<tr><td><font color = red>total</font></td><td>$count</td></tr>";
			echo "</table></center><br />";
			
		}
		
		// nombre de kills
		$count = 0;
		$sql = "SELECT * FROM cv WHERE IDActeur_cv='$id' OR IDCible_cv='$id' ORDER BY date_cv DESC";
		$res = $mysqli->query($sql);
		
		echo "<center><font color=red><b>Le bon...</b></font></center>";
		echo "<center><table border=1 width=60%><tr><th width=25%>date</th><th>Évènement</th></tr>";
		
		while ($t = $res->fetch_assoc()) {
			if ($t['IDActeur_cv'] == $id && $t['IDCible_cv'] < 10000) {
				$count++;
				echo "<tr><td>".$t['date_cv']."</td><td>".$t['nomActeur_cv']." [<a href=\"evenement.php?infoid=".$t['IDActeur_cv']."\">".$t['IDActeur_cv']."</a>] a capturé ";
				echo $t['nomCible_cv']." [<a href=\"evenement.php?infoid=".$t['IDCible_cv']."\">".$t['IDCible_cv']."</a>]</td>";
			}
		}
		echo "<tr><td><font color = red>total</font></td><td>$count</td></tr>";
		echo "</table></center><br />";
		
		// nombre de morts
		$count = 0;
		$sql = "SELECT * FROM cv WHERE IDActeur_cv='$id' OR IDCible_cv='$id' ORDER BY date_cv DESC";
		$res = $mysqli->query($sql);
		
		echo "</table></center><br><center><font color=red><b>... et le moins bon</b></font></center>";
		echo "<center><table border=1 width=60%><tr><th width=25%>date</th><th>Évènement</th></tr>";
		
		while ($t = $res->fetch_assoc()) {
			if ($t['IDCible_cv'] == $id) {
				$count++;
				echo "<tr><td>".$t['date_cv']."</td><td>".$t['nomCible_cv']." [<a href=\"evenement.php?infoid=".$t['IDCible_cv']."\">".$t['IDCible_cv']."</a>] a été capturé par ";
				echo $t['nomActeur_cv']." [<a href=\"evenement.php?infoid=".$t['IDActeur_cv']."\">".$t['IDActeur_cv']."</a>]</td>";
			}
		}
		echo "<tr><td><font color = red>total</font></td><td>$count</td></tr>";
		echo "</table></center><br />";
		
		// nombre de pnj tu"s
		$count = 0;
		$sql = "SELECT * FROM cv WHERE IDActeur_cv='$id' OR IDCible_cv='$id' ORDER BY date_cv DESC";
		$res = $mysqli->query($sql);
		
		echo "<center><font color=red><b>PNJ</b></font></center>";
		echo "<center><table border=1 width=60%><tr><th width=25%>date</th><th>Évènement</th></tr>";
		
		while ($t = $res->fetch_assoc()) {
			if ($t['IDActeur_cv'] == $id && $t['IDCible_cv'] >= 10000 && $t['IDCible_cv'] < 50000) {
				$count++;
				echo "<tr><td>".$t['date_cv']."</td><td>".$t['nomActeur_cv']." [<a href=\"evenement.php?infoid=".$t['IDActeur_cv']."\">".$t['IDActeur_cv']."</a>] a tué ";
				echo $t['nomCible_cv']." [<a href=\"evenement.php?infoid=".$t['IDCible_cv']."\">".$t['IDCible_cv']."</a>]</td>";
			}
		}
		echo "<tr><td><font color = red>total</font></td><td>$count</td></tr>";
		echo "</table></center><br />";
		
		// nombre de batiments d"truits
		$count = 0;
		$sql = "SELECT * FROM cv WHERE IDActeur_cv='$id' OR IDCible_cv='$id' ORDER BY date_cv DESC";
		$res = $mysqli->query($sql);
		
		echo "<center><font color=red><b>Batiments</b></font></center>";
		echo "<center><table border=1 width=60%><tr><th width=25%>date</th><th>Évènement</th></tr>";
		
		while ($t = $res->fetch_assoc()) {
			if ($t['IDActeur_cv'] == $id && $t['IDCible_cv'] >= 50000) {
				$count++;
				echo "<tr><td>".$t['date_cv']."</td><td>".$t['nomActeur_cv']." [<a href=\"evenement.php?infoid=".$t['IDActeur_cv']."\">".$t['IDActeur_cv']."</a>] a détruit ";
				echo $t['nomCible_cv']." [<a href=\"evenement.php?infoid=".$t['IDCible_cv']."\">".$t['IDCible_cv']."</a>]</td>";
			}
		}
		echo "<tr><td><font color = red>total</font></td><td>$count</td></tr>";
		echo "</table></center><br />";
	}
	else {
		// le perso n'existe pas
		echo "<br /><center><b>Erreur :</b> Ce perso n'existe pas ou plus !</center>";
	}
}
else {
	// rien ^^
}

?>
</body>
</html>
<?php
}
else{
	echo "<font color=red>Vous ne pouvez pas accéder à cette page, veuillez vous loguer.</font>";
}
?>