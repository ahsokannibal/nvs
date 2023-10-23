<?php
session_start();
require_once("../fonctions.php");
require_once("f_entete.php");

$mysqli = db_connexion();

include ('../nb_online.php');

date_default_timezone_set('Europe/Paris');

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
		
		<p align="center"><a href="jouer.php"> <input type="button" value="Retour au jeu"> </a></p>
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
	}
	else {
		entete_mort($mysqli, $id);
	}
		
	// CV spéciaux
	$sql = "SELECT * FROM cv WHERE IDActeur_cv='$id' AND special='1' ORDER BY date_cv DESC";
	$res = $mysqli->query($sql);
	$nb_event = $res->num_rows;
	
	if ($nb_event) {
		
		echo "<center><font color=red><b>Évènements spéciaux</b></font></center>";
		echo "<center><table border=1 class='table'><tr><th style='text-align:center' width=25%>date</th><th style='text-align:center'>Évènement</th></tr>";
	
		while ($t = $res->fetch_assoc()){
			echo "<tr>";
			echo "<td align='center'>".$t['date_cv']."</td><td align='center'>".$t['nomActeur_cv']." ";
			echo "</td></tr>";
		}
		echo "</table></center><br />";
	}
	
	// Décorations
	$sql = "SELECT UNIX_TIMESTAMP(date_decoration) as date_decoration, raison_decoration, image_decoration FROM perso_as_decoration, decorations 
			WHERE perso_as_decoration.id_decoration = decorations.id_decoration
			AND id_perso='$id'
			ORDER BY date_decoration";
	$res = $mysqli->query($sql);
	$nb_event = $res->num_rows;
	
	if ($nb_event) {
		
		echo "<center><font color=red><b>Décorations</b></font></center>";
		echo "<center>";
		echo "<table border=1 class='table'>";
		echo "	<tr>";
		echo "		<th style='text-align:center' width=25%>date</th>";
		echo "		<th style='text-align:center' width=25%>décoration</th>";
		echo "		<th style='text-align:center'>Raison</th>";
		echo "	</tr>";
	
		while ($t = $res->fetch_assoc()){
			
			$date_deco		= $t['date_decoration'];
			$date_deco 		= date('Y-m-d H:i:s', $date_deco);
			$raison_deco	= htmlspecialchars($t['raison_decoration']);
			$image_deco 	= $t['image_decoration'];
			
			echo "	<tr>";
			echo "		<td align='center'>".$date_deco."</td>";
			echo "		<td align='center'><img src='../images/medailles/".$image_deco."' width='20' height='40'/></td>";
			if (trim($raison_deco) != "") {
				echo "		<td align='center'>".$raison_deco."</td>";
			}
			else {
				echo "		<td align='center'>Pour son engagement et son courage</td>";
			}
			echo "	</tr>";
		}
		echo "</table></center><br />";
	}
	
	// Citations
	$sql = "SELECT date_decoration, raison_decoration FROM perso_as_decoration
			WHERE id_decoration is NULL
			AND id_perso='$id'
			ORDER BY date_decoration";
	$res = $mysqli->query($sql);
	$nb_event = $res->num_rows;
	
	if ($nb_event) {
		
		echo "<center><font color=red><b>Citations</b></font></center>";
		echo "<center>";
		echo "<table border=1 class='table'>";
		echo "	<tr>";
		echo "		<th style='text-align:center' width=25%>date</th>";
		echo "		<th style='text-align:center'>Raison</th>";
		echo "	</tr>";
	
		while ($t = $res->fetch_assoc()){
			
			$date_deco		= $t['date_decoration'];
			$raison_deco	= htmlspecialchars($t['raison_decoration']);
			
			echo "	<tr>";
			echo "		<td align='center'>".$date_deco."</td>";
			echo "		<td align='center'>Citation : ".$raison_deco."</td>";
			echo "	</tr>";
		}
		echo "</table></center><br />";
	}
	
	
	// Missions
	$count = 0;
	$sql = "SELECT UNIX_TIMESTAMP(date_cv) as date_cv, nomActeur_cv, nomCible_cv FROM cv WHERE IDActeur_cv='$id' AND special='2' ORDER BY date_cv DESC";
	$res = $mysqli->query($sql);
	$nb_mission = $res->num_rows;
	
	if ($nb_mission) {
		
		echo "<center><font color=red><b>Missions</b></font></center>";
		echo "<center><table border=1 class='table'><tr><th style='text-align:center' width=25%>date</th><th style='text-align:center'>Évènement</th></tr>";
		
		while ($t = $res->fetch_assoc()){
			$count++;
			
			$date_cv = $t['date_cv'];
			$date_cv = date('Y-m-d H:i:s', $date_cv);
			
			echo "<tr>";
			echo "<td align='center'>".$date_cv."</td><td align='center'>".$t['nomActeur_cv']." a réussi la mission ".$t['nomCible_cv'];
			echo "</td></tr>";
		}
		echo "<tr><td align='center'><font color = red>total</font></td><td align='center'>$count</td></tr>";
		echo "</table></center><br />";
		
	}
	
	// nombre de kills
	$count_capture = 0;
	$count_promotion = 0;
	$sql = "SELECT UNIX_TIMESTAMP(date_cv) as date_cv, nomActeur_cv, IDActeur_cv, IDCible_cv, nomCible_cv, gradeActeur_cv, gradeCible_cv, special 
			FROM cv WHERE (special IS NULL OR special = '8' OR special = '9' OR special = '10') AND (IDActeur_cv='$id' OR IDCible_cv='$id') ORDER BY date_cv DESC";
	$res = $mysqli->query($sql);
	
	echo "<center><font color=red><b>Le bon...</b></font></center>";
	echo "<center><table border=1 class='table'><tr><th style='text-align:center' width=25%>date</th><th style='text-align:center'>Évènement</th></tr>";
	
	while ($t = $res->fetch_assoc()) {
		
		$date_cv			= $t['date_cv'];
		$id_acteur_cv 		= $t['IDActeur_cv'];
		$id_cible_cv		= $t['IDCible_cv'];
		$nom_acteur_cv		= $t['nomActeur_cv'];
		$grade_acteur_cv	= $t['gradeActeur_cv'];
		$special			= $t['special'];
		
		$date_cv = date('Y-m-d H:i:s', $date_cv);
		
		if ($id_acteur_cv == $id && $id_cible_cv < 50000 && $id_cible_cv != NULL) {			
			
			$nom_cible_cv		= $t['nomCible_cv'];
			$grade_cible_cv		= $t['gradeCible_cv'];
			
			$count_capture++;
			
			echo "<tr>";
			echo "	<td align='center'>".$date_cv."</td><td>".$nom_acteur_cv." [<a href=\"evenement.php?infoid=".$id_acteur_cv."\">".$id_acteur_cv."</a>]";
			if ($special == '10') {
				echo " a négocié la capture de ";
			}
			else {
				echo " a capturé ";
			}
			echo $nom_cible_cv." [<a href=\"evenement.php?infoid=".$id_cible_cv."\">".$id_cible_cv."</a>]";
			if ($grade_cible_cv	 != null && $grade_cible_cv	 != "") {
				echo " (".$grade_cible_cv.")";
			}
			echo "	</td>";
			echo "</tr>";
		}
		else if ($id_acteur_cv == $id && $special == '8') {
			
			$nom_cible_cv		= $t['nomCible_cv'];
			
			$count_capture++;
			
			echo "<tr>";
			echo "	<td align='center'>".$date_cv."</td><td>".$nom_acteur_cv." [<a href=\"evenement.php?infoid=".$id_acteur_cv."\">".$id_acteur_cv."</a>] a capturé le bâtiment ";
			echo $nom_cible_cv." [<a href=\"evenement.php?infoid=".$id_cible_cv."\">".$id_cible_cv."</a>]";
			echo "	</td>";
			echo "</tr>";
		}
		else if ($id_acteur_cv == $id && $special == '9') {
			
			$count_promotion++;
			
			echo "<tr>";
			echo "	<td align='center'>".$date_cv."</td><td>".$nom_acteur_cv." [<a href=\"evenement.php?infoid=".$id_acteur_cv."\">".$id_acteur_cv."</a>] a été promu <b>".$grade_acteur_cv."</b></td>";
			echo "</tr>";
		}
	}
	
	$cout_total = $count_capture + $count_promotion;
	echo "<tr><td align='center'><font color = red>total captures</font></td><td align='center'>$count_capture</td></tr>";
	echo "<tr><td align='center'><font color = red>total</font></td><td align='center'>$cout_total</td></tr>";
	echo "</table></center><br />";
	
	// nombre de morts
	$count = 0;
	$sql = "SELECT UNIX_TIMESTAMP(date_cv) as date_cv, nomActeur_cv, IDActeur_cv, IDCible_cv, nomCible_cv, gradeActeur_cv, gradeCible_cv, special 
			FROM cv WHERE (special IS NULL OR special = '10' OR special = '11') AND (IDActeur_cv='$id' OR IDCible_cv='$id') ORDER BY date_cv DESC";
	$res = $mysqli->query($sql);
	
	echo "</table></center><br><center><font color=red><b>... et le moins bon</b></font></center>";
	echo "<center><table border=1 class='table'><tr><th style='text-align:center' width=25%>date</th><th style='text-align:center'>Évènement</th></tr>";
	
	while ($t = $res->fetch_assoc()) {
		
		$date_cv 			= $t['date_cv'];
		$date_cv 			= date('Y-m-d H:i:s', $date_cv);
		
		$id_acteur_cv 		= $t['IDActeur_cv'];
		$nom_acteur_cv		= $t['nomActeur_cv'];
		$grade_acteur_cv	= $t['gradeActeur_cv'];
		
		$id_cible_cv		= $t['IDCible_cv'];
		$nom_cible_cv		= $t['nomCible_cv'];
		$grade_cible_cv		= $t['gradeCible_cv'];
		
		$special			= $t['special'];
		
		if ($id_cible_cv == $id) {
			
			$count++;
			echo "<tr>";
			echo "	<td align='center'>".$date_cv."</td><td>".$nom_cible_cv." [<a href=\"evenement.php?infoid=".$id_cible_cv."\">".$id_cible_cv."</a>]";
			if ($id_acteur_cv != 0) {
				if ($special == '10') {
				echo " a accepté de se rendre face à ";
				}
				else {
					echo " a été capturé par ";
				}
				echo $nom_acteur_cv." [<a href=\"evenement.php?infoid=".$id_acteur_cv."\">".$id_acteur_cv."</a>]";
				if ($grade_acteur_cv	 != null && $grade_acteur_cv	 != "") {
					echo " (".$grade_acteur_cv.")";
				}
			}
			else {
				echo " s'est effondré tout seul à cause de : ";
				echo $nom_acteur_cv;
			}
			
			echo "	</td>";
			echo "</tr>";
		}
		else if ($id_acteur_cv == $id && $special == 11) {
			
			$count++;
			echo "<tr>";
			echo "	<td align='center'>".$date_cv."</td><td>".$nom_acteur_cv." [<a href=\"evenement.php?infoid=".$id_acteur_cv."\">".$id_acteur_cv."</a>] a été capturé de force par encerclement !";			
			echo "	</td>";
			echo "</tr>";
			
		}
	}
	echo "<tr><td align='center'><font color = red>total</font></td><td align='center'>$count</td></tr>";
	echo "</table></center><br />";
	
	// nombre de pnj tués
	$count = 0;
	$sql = "SELECT UNIX_TIMESTAMP(date_cv) as date_cv, nomActeur_cv, IDActeur_cv, IDCible_cv, nomCible_cv FROM cv WHERE special IS NULL AND (IDActeur_cv='$id' OR IDCible_cv='$id') ORDER BY date_cv DESC";
	$res = $mysqli->query($sql);
	
	echo "<center><font color=red><b>PNJ</b></font></center>";
	echo "<center><table border=1 class='table'><tr><th style='text-align:center' width=25%>date</th><th style='text-align:center' >Évènement</th></tr>";
	
	while ($t = $res->fetch_assoc()) {
		
		$nomActeur_cv 	= $t['nomActeur_cv'];
		$idActeur_cv	= $t['IDActeur_cv'];
		$nomCible_cv	= $t['nomCible_cv'];
		$idCible_cv		= $t['IDCible_cv'];
		
		$date_cv = $t['date_cv'];
		$date_cv = date('Y-m-d H:i:s', $date_cv);
		
		if ($idActeur_cv == $id && $idCible_cv >= 200000) {
			$count++;
			
			echo "<tr><td align='center'>".$date_cv."</td><td>".$nomActeur_cv." [<a href=\"evenement.php?infoid=".$idActeur_cv."\">".$idActeur_cv."</a>] a tué ";
			echo $nomCible_cv." [<a href=\"evenement.php?infoid=".$idCible_cv."\">".$idCible_cv."</a>]</td>";
		}
	}
	echo "<tr><td align='center'><font color = red>total</font></td><td align='center'>$count</td></tr>";
	echo "</table></center><br />";
	
	// nombre de batiments détruits
	$count = 0;
	$sql = "SELECT UNIX_TIMESTAMP(date_cv) as date_cv, nomActeur_cv, IDActeur_cv, IDCible_cv, nomCible_cv FROM cv WHERE special IS NULL AND (IDActeur_cv='$id' OR IDCible_cv='$id') ORDER BY date_cv DESC";
	$res = $mysqli->query($sql);
	
	echo "<center><font color=red><b>Batiments</b></font></center>";
	echo "<center>";
	echo "	<table border=1 class='table'>";
	echo "		<tr>";
	echo "			<th style='text-align:center' width=25%>date</th><th style='text-align:center'>Évènement</th>";
	echo "		</tr>";
	
	while ($t = $res->fetch_assoc()) {
		
		$date_cv = $t['date_cv'];
		$date_cv = date('Y-m-d H:i:s', $date_cv);
		
		$nomActeur_cv 	= $t['nomActeur_cv'];
		$idActeur_cv	= $t['IDActeur_cv'];
		$nomCible_cv	= $t['nomCible_cv'];
		$idCible_cv		= $t['IDCible_cv'];
		
		if ($idActeur_cv == $id && $idCible_cv >= 50000 && $idCible_cv < 200000) {
			$count++;
			
			echo "		<tr><td align='center'>".$date_cv."</td><td>".$nomActeur_cv." [<a href=\"evenement.php?infoid=".$idActeur_cv."\">".$idActeur_cv."</a>] a détruit ";
			echo $nomCible_cv." [<a href=\"evenement.php?infoid=".$idCible_cv."\">".$idCible_cv."</a>]</td>";
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