<?php
@session_start();
require_once("../fonctions.php");
require_once("f_carte.php");
require_once("f_entete.php");

$mysqli = db_connexion();

include ('../nb_online.php');

if(@$_SESSION["id_perso"]){
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Evènements</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../style.css" rel="stylesheet" type="text/css">
</head>
<body>
<div align="center"><h2>Evènements</h2></div>
<?php
$id_perso = $_SESSION["id_perso"];
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
			
			if(isset($_GET["liste"]) && $_GET["liste"] == "ok") { // on souhaite connaitre la liste des persos d'un batiment par exemple.
			
				// test si c'est bien un batiment
				if ($_GET['infoid'] >= 50000) {
					
					// test si le batiment existe
					$sql = "SELECT id_batiment FROM instance_batiment WHERE id_instanceBat='$id'";
					$res = $mysqli->query($sql);
					$nb_b = $res->fetch_row();
					
					if ($nb_b) { // il existe
					
						// recuperation de la liste des persos dans le batiment
						$sql_liste = "SELECT nom_perso, perso.id_perso FROM perso_in_batiment, perso WHERE perso.id_perso=perso_in_batiment.id_perso AND id_instanceBat='$id'";
						$res_liste = $mysqli->query($sql_liste);
						$verif_liste = '1';
						
					}
					else {
						echo "<font color = red><center>Le batiment selectionné n'existe pas</center></font>";
					}
				}
				else {
					echo "<font color = red><center>vous ne pouvez lister la liste des perso que sur un batiment</center></font>";
				}
			}
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
			$sql = "SELECT id_instanceBat, camp_instance FROM instance_batiment WHERE id_instanceBat='$id'";
			$res = $mysqli->query($sql);
			$nb_p = $res->num_rows;
			$t_ci = $res->fetch_assoc();
		}
	}

	if($nb_p == '1'){
		
		// l'entité existe bien
		entete($mysqli, $id);
	
		if(isset($verif_liste) && $verif_liste){
			// verifier camp perso
			$sql = "select clan from perso where id_perso='$id_perso'";
			$res = $mysqli->query($sql);
			$t_c = $res->fetch_assoc();
			$camp_perso = $t_c["clan"];
			$camp_bat = $t_ci["camp_instance"];
			
			if($camp_perso == $camp_bat){
				
				// si à l'interieur
				if(in_instance_bat($mysqli, $id_perso, $id)){
					
					echo "<center>";
					echo "<b>Liste des persos dans le bâtiment</b><br />";
					
					while($liste = $res_liste->fetch_assoc()) {
						
						$nom_p = $liste["nom_perso"];
						$id_p = $liste["id_perso"];
						
						echo "$nom_p [<a href=\"evenement.php?infoid=".$id_p."\">$id_p</a>]";
					}
					
					echo "</center>";
					echo "<br />";
				}
				else {
					echo "<center>";
					echo "<b>Nombre de persos dans le bâtiment</b><br />";
					$nb_l = $res_liste->num_rows;
					echo "<i>Il y a <b>$nb_l</b> persos dans ce batiment</i>";
					echo "</center>";
					echo "<br />";
				}
			}
			else {
				echo "<center>";
				echo "<b>Nombre de persos dans le bâtiment</b><br />";
				$nb_l = $res_liste->num_rows;
				echo "<i>Il y a <b>$nb_l</b> persos dans ce batiment</i>";
				echo "</center>";
				echo "<br />";
			}
		}
	
		if ($id) {
	
			?> 
			<table align="center" width="80%" border=1>
				<tr>
				<th width="25%">date</th>
				<th width="75%">Évènement</th>
				</tr>
			<?php
			
			if(isset($_GET['infoid'])) {
			
				$sql = "SELECT * FROM evenement WHERE IDActeur_evenement='$id' OR IDCible_evenement='$id' ORDER BY ID_evenement DESC, date_evenement DESC LIMIT 100";
				$res = $mysqli->query($sql);
				
				while ($t = $res->fetch_assoc()) {
					echo "<tr><td>".$t['date_evenement']."</td><td>".$t['nomActeur_evenement']." [<a href=\"evenement.php?infoid=".$t['IDActeur_evenement']."\">".$t['IDActeur_evenement']."</a>] ".stripslashes($t['phrase_evenement'])." ";
					if ($t['IDCible_evenement'] == 0) {
						if ($t['IDActeur_evenement'] == $id_perso || $t['IDCible_evenement'] == $id_perso)
							echo " ".stripslashes($t['effet_evenement']);
						echo "</td>";
					}
					else {
						echo $t['nomCible_evenement']." [<a href=\"evenement.php?infoid=".$t['IDCible_evenement']."\">".$t['IDCible_evenement']."</a>]";
						if ($t['IDActeur_evenement'] == $id_perso || $t['IDCible_evenement'] == $id_perso)
							echo " ".stripslashes($t['effet_evenement']);
						echo "</td>";
					}
				}
			}
			else {
			
				$sql = "SELECT * FROM evenement WHERE IDActeur_evenement='$id' OR IDCible_evenement='$id' ORDER BY ID_evenement DESC, date_evenement DESC LIMIT 100";
				$res = $mysqli->query($sql);
				
				while ($t = $res->fetch_assoc()) {
					
					echo "<tr><td>".$t['date_evenement']."</td><td>".$t['nomActeur_evenement']." [<a href=\"evenement.php?infoid=".$t['IDActeur_evenement']."\">".$t['IDActeur_evenement']."</a>] ".stripslashes($t['phrase_evenement'])." ";
					
					if ($t['IDCible_evenement'] == 0)
						echo stripslashes($t['effet_evenement'])."</td>";
					else {
						echo $t['nomCible_evenement']." [<a href=\"evenement.php?infoid=".$t['IDCible_evenement']."\">".$t['IDCible_evenement']."</a>] ".stripslashes($t['effet_evenement'])."</td>";
					}
				}
			}
		}	
	}
	else {
		// le perso n'existe pas
		echo "<br/><center><b>Erreur :</b>Ce perso n'existe pas !</center>";
	}
}
else {
	// rien ^^
}	
?>
</table>
</body>
</html>
<?php
}
else{
	echo "<font color=red>Vous ne pouvez pas accéder à cette page, veuillez vous loguer.</font>";
}
?>