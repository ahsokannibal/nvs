<?php 
@session_start();  
require_once("fonctions.php");
require_once("jeu/f_carte.php");
	
$mysqli = db_connexion();

include ('nb_online.php');

if(isset($_SESSION["id_perso"])){
	
	$id_perso = $_SESSION["id_perso"];

	// recuperation des infos sur le perso
	$sql = "SELECT x_perso, y_perso, nom_perso, pv_perso, pvMax_perso, recup_perso, bonusRecup_perso, bonus_perso, pa_perso, paMax_perso, pm_perso, image_perso, pmMax_perso, bourre_perso, clan, est_gele, UNIX_TIMESTAMP(DLA_perso) as DLA, UNIX_TIMESTAMP(date_gele) as DG FROM perso WHERE ID_perso='$id_perso'";
	$res = $mysqli->query($sql);
	$t_perso = $res->fetch_assoc();
	$x_perso = $t_perso["x_perso"];
	$y_perso = $t_perso["y_perso"];
	$pv = $t_perso["pv_perso"];
	$pv_max = $t_perso["pvMax_perso"];
	$recup = $t_perso["recup_perso"] + $t_perso["bonusRecup_perso"];
	$dla = $t_perso["DLA"];
	$pa = $t_perso["pa_perso"];
	$pa_max = $t_perso["paMax_perso"];
	$pm = $t_perso["pm_perso"];
	$pm_max = $t_perso["pmMax_perso"];
	$bourre = $t_perso["bourre_perso"];
	$bonus_recup = $t_perso["bonusRecup_perso"];
	$bonus = $t_perso["bonus_perso"];
	$image = $t_perso["image_perso"];
	$clan = $t_perso["clan"];
	$est_gele = $t_perso["est_gele"];
	$date_gele = $t_perso["DG"];
	$_SESSION["nom_perso"] = $pseudo = $t_perso["nom_perso"];

	$date = time();
	
	if ($pv > 0) {
		//il est encore en vie
			// redirection
			header("location:jeu/jouer.php");
	} else {

		//c'est un nouveau tour et le perso n'est pas gele
		if (!$est_gele && nouveau_tour($date, $dla)) {
						
			//    RESPAWN BATIMENT    //
							
			// Récupération du batiment de rappatriement le plus proche du perso
			$id_bat = selection_bat_rapat($mysqli, $id_perso, $x_perso, $y_perso, $clan);
							
			// récupération coordonnées batiment
			$sql_b = "SELECT x_instance, y_instance FROM instance_batiment WHERE id_instanceBat='$id_bat'";
			$res_b = $mysqli->query($sql_b);
			$t_b = $res_b->fetch_assoc();
			$x = $t_b['x_instance'];
			$y = $t_b['y_instance'];
							
			// On met le perso dans le batiment
			$sql = "INSERT INTO perso_in_batiment VALUES('$id_perso','$id_bat')";
			$mysqli->query($sql);
							
			// MAJ perso
			$sql = "UPDATE perso SET x_perso='$x', y_perso='$y', pm_perso=pmMax_perso, pa_perso=paMax_perso, pv_perso=pvMax_perso, bonusPerception_perso=0, bourre_perso=0, bonus_perso=0, DLA_perso=FROM_UNIXTIME($new_dla) WHERE ID_perso='$id_perso'";
			$mysqli->query($sql);
				
			//redirection
			header("location:jeu/jouer.php");
			
		}
		else {
			echo "<div class=\"infoi\" align=\"center\">Vous êtes mort !</div><br />";
			echo "<center><img src=\"images/mort.gif\" alt='mort'/></center><br /><br />";
			echo "<center>Vous devez attendre votre prochain tour (";
			echo get_date($dla);
			echo ").</center>";		
			echo "<br /><div align=\"center\"><a href=\"jeu/jouer.php\">retour page de jeu</a></div>";
			
			
			
			echo "<br /><div align=\"center\"><a href=\"logout.php\">logout</a></div>";
		}
	}
}
else{
	echo "<font color=red>Vous ne pouvez pas acceder a cette page, veuillez vous logguer.</font>";
}
?>
