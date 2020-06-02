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
		
		<!-- Required meta tags -->
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		
		<!-- Bootstrap CSS -->
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
	</head>
	
	<body>
		<div align="center">
			<h2>CV</h2>
		</div>
		
		<p align="center"><input type="button" value="Fermer cette fenêtre" onclick="window.close()"></p>
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

if(isset($id)){
	
	if($id < 50000){
		// verifier que le perso existe
		$sql = "SELECT id_perso FROM perso WHERE id_perso='$id'";
		$res = $mysqli->query($sql);
		$nb_p = $res->num_rows;
	}
	else {
		if($id >= 200000){
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
			echo "<center><table border=1 width=80%><tr><th style='text-align:center' width=25%>date</th><th style='text-align:center'>Évènement</th></tr>";
		
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
			echo "<center><table border=1 width=80%><tr><th style='text-align:center' width=25%>date</th><th style='text-align:center'>Évènement</th></tr>";
			
			while ($t = $res->fetch_assoc()){
				$count++;
				echo "<tr>";
				echo "<td>".$t['date_evenement']."</td><td align='center'>".$t['nomActeur_evenement']." ".stripslashes($t['phrase_evenement'])." ";
				if($_SESSION["id_perso"] == $id){
					echo stripslashes($t['effet_evenement'])."";
				}
				echo "</td></tr>";
			}
			echo "<tr><td align='center'><font color = red>total</font></td><td align='center'>$count</td></tr>";
			echo "</table></center><br />";
			
		}
		
		// nombre de kills
		$count = 0;
		$sql = "SELECT * FROM cv WHERE IDActeur_cv='$id' OR IDCible_cv='$id' ORDER BY date_cv DESC";
		$res = $mysqli->query($sql);
		
		echo "<center><font color=red><b>Le bon...</b></font></center>";
		echo "<center><table border=1 width=60%><tr><th style='text-align:center' width=25%>date</th><th style='text-align:center'>Évènement</th></tr>";
		
		while ($t = $res->fetch_assoc()) {
			if ($t['IDActeur_cv'] == $id && $t['IDCible_cv'] < 50000) {
				$count++;
				echo "<tr><td>".$t['date_cv']."</td><td>".$t['nomActeur_cv']." [<a href=\"evenement.php?infoid=".$t['IDActeur_cv']."\">".$t['IDActeur_cv']."</a>] a capturé ";
				echo $t['nomCible_cv']." [<a href=\"evenement.php?infoid=".$t['IDCible_cv']."\">".$t['IDCible_cv']."</a>]</td>";
			}
		}
		echo "<tr><td align='center'><font color = red>total</font></td><td align='center'>$count</td></tr>";
		echo "</table></center><br />";
		
		// nombre de morts
		$count = 0;
		$sql = "SELECT * FROM cv WHERE IDActeur_cv='$id' OR IDCible_cv='$id' ORDER BY date_cv DESC";
		$res = $mysqli->query($sql);
		
		echo "</table></center><br><center><font color=red><b>... et le moins bon</b></font></center>";
		echo "<center><table border=1 width=60%><tr><th style='text-align:center' width=25%>date</th><th style='text-align:center'>Évènement</th></tr>";
		
		while ($t = $res->fetch_assoc()) {
			if ($t['IDCible_cv'] == $id) {
				
				$id_acteur_cv = $t['IDActeur_cv'];
				
				$count++;
				echo "<tr>";
				echo "	<td>".$t['date_cv']."</td><td>".$t['nomCible_cv']." [<a href=\"evenement.php?infoid=".$t['IDCible_cv']."\">".$t['IDCible_cv']."</a>]";
				if ($id_acteur_cv != 0) {
					echo " a été capturé par ";
					echo $t['nomActeur_cv']." [<a href=\"evenement.php?infoid=".$id_acteur_cv."\">".$id_acteur_cv."</a>]";
				}
				else {
					echo " s'est effondré tout seul à cause de : ";
					echo $t['nomActeur_cv'];
				}
				
				echo "	</td>";
			}
		}
		echo "<tr><td align='center'><font color = red>total</font></td><td align='center'>$count</td></tr>";
		echo "</table></center><br />";
		
		// nombre de pnj tu"s
		$count = 0;
		$sql = "SELECT * FROM cv WHERE IDActeur_cv='$id' OR IDCible_cv='$id' ORDER BY date_cv DESC";
		$res = $mysqli->query($sql);
		
		echo "<center><font color=red><b>PNJ</b></font></center>";
		echo "<center><table border=1 width=60%><tr><th style='text-align:center' width=25%>date</th><th style='text-align:center' >Évènement</th></tr>";
		
		while ($t = $res->fetch_assoc()) {
			if ($t['IDActeur_cv'] == $id && $t['IDCible_cv'] >= 200000) {
				$count++;
				echo "<tr><td>".$t['date_cv']."</td><td>".$t['nomActeur_cv']." [<a href=\"evenement.php?infoid=".$t['IDActeur_cv']."\">".$t['IDActeur_cv']."</a>] a tué ";
				echo $t['nomCible_cv']." [<a href=\"evenement.php?infoid=".$t['IDCible_cv']."\">".$t['IDCible_cv']."</a>]</td>";
			}
		}
		echo "<tr><td align='center'><font color = red>total</font></td><td align='center'>$count</td></tr>";
		echo "</table></center><br />";
		
		// nombre de batiments d"truits
		$count = 0;
		$sql = "SELECT * FROM cv WHERE IDActeur_cv='$id' OR IDCible_cv='$id' ORDER BY date_cv DESC";
		$res = $mysqli->query($sql);
		
		echo "<center><font color=red><b>Batiments</b></font></center>";
		echo "<center>";
		echo "	<table border=1 width=60%>";
		echo "		<tr>";
		echo "			<th style='text-align:center' width=25%>date</th><th style='text-align:center'>Évènement</th>";
		echo "		</tr>";
		
		while ($t = $res->fetch_assoc()) {
			if ($t['IDActeur_cv'] == $id && $t['IDCible_cv'] >= 50000 && $t['IDCible_cv'] < 200000) {
				$count++;
				echo "		<tr><td>".$t['date_cv']."</td><td>".$t['nomActeur_cv']." [<a href=\"evenement.php?infoid=".$t['IDActeur_cv']."\">".$t['IDActeur_cv']."</a>] a détruit ";
				echo $t['nomCible_cv']." [<a href=\"evenement.php?infoid=".$t['IDCible_cv']."\">".$t['IDCible_cv']."</a>]</td>";
			}
		}
		echo "		<tr>";
		echo "			<td align='center'><font color = red>total</font></td><td align='center'>$count</td>";
		echo "		</tr>";
		echo "	</table>";
		echo "</center>";
		
		echo "<br />";
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

		<!-- Optional JavaScript -->
		<!-- jQuery first, then Popper.js, then Bootstrap JS -->
		<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
		<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
	</body>
</html>
<?php
}
else{
	echo "<font color=red>Vous ne pouvez pas accéder à cette page, veuillez vous loguer.</font>";
}
?>