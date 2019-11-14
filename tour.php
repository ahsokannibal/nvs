<?php 
@session_start();  
require_once("fonctions.php");
require_once("jeu/f_carte.php");
	
$mysqli = db_connexion();

include ('nb_online.php');

if(isset($_SESSION["ID_joueur"])){
	$id_joueur = $_SESSION['ID_joueur']; 
	
	// recuperation de l'id et du nom du perso
	$sql = "SELECT id_perso, nom_perso FROM perso WHERE idJoueur_perso=$id_joueur";
	$res = $mysqli->query($sql);
	$t_id = $res->fetch_assoc();
	$_SESSION["id_perso"] = $id = $t_id["id_perso"];
	$_SESSION["nom_perso"] = $pseudo = $t_id["nom_perso"];

	// recuperation des infos sur le perso
	$sql = "SELECT x_perso, y_perso, pv_perso, pvMax_perso, recup_perso, bonusRecup_perso, bonus_perso, pa_perso, paMax_perso, pm_perso, image_perso, pmMax_perso, bourre_perso, clan, est_gele, UNIX_TIMESTAMP(DLA_perso) as DLA, UNIX_TIMESTAMP(date_gele) as DG FROM perso WHERE ID_perso='$id'";
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

	$date = time();
	
	// Perso gele et il peut se degeler
	if($est_gele && temp_degele($date, $date_gele)){
		
		// degele du perso
		$sql = "UPDATE perso SET est_gele='0', date_gele=NULL, a_gele='0' WHERE id_perso='$id'";
		$mysqli->query($sql);
		
		// reapparition sur la carte
		$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte='$id', image_carte='$image' WHERE x_carte=$x_perso AND y_carte=$y_perso";
		$mysqli->query($sql);
		
		/*
		// On degele le perso
		if($clan == 1){ // bleu
			$x_min_respawn = 0;
			$x_max_respawn = 40;
			$y_min_respawn = 0;
			$y_max_respawn = 40;
		}
		if($clan == 2){ // rouge
			$x_min_respawn = 160;
			$x_max_respawn = 200;
			$y_min_respawn = 160;
			$y_max_respawn = 200;
		}
				
		// on le replace aleatoirement sur la carte
		$occup = 1;
		while ($occup == 1)
		{
			$x = pos_zone_rand_x($x_min_respawn,$x_max_respawn); 
			$y = pos_zone_rand_y($y_min_respawn,$y_max_respawn);
			$occup = verif_pos_libre($x,$y);
		}
		$sql = "UPDATE carte SET occupee_carte = '1', image_carte='$image', idPerso_carte='$id' WHERE x_carte='$x' AND y_carte='$y'";
		exec_requete($sql);
			
		$sql = "UPDATE perso SET x_perso='$x', y_perso='$y', arene='0', changementDe_perso='0' ,pm_perso=pmMax_perso, pa_perso=paMax_perso, pv_perso=pvMax_perso, bonusPerception_perso=0, bourre_perso=0, bonus_perso=0, DLA_perso=FROM_UNIXTIME($new_dla) WHERE ID_perso='$id'";
		exec_requete($sql, __LINE__, __FILE__);
		*/
		
		//redirection
		header("location:jeu/jouer.php"); 
	}
	else {
		if($est_gele && !temp_degele($date, $date_gele)){
			$tr = temp_restant($date, $date_gele);
			$jours = floor ($tr/(3600*24));
			$heures = floor (($tr%(3600*24))/3600);
			$min = floor ((($tr%(3600*24))%3600)/60);
			$sec = (((($tr%(3600*24))%3600)%60));
			
			echo "Vous devez attendre $jours jours, $heures heures, $min minutes et $sec secondes encore avant de pouvoir vous degeler<br /><br />";
			echo "<a href=\"logout.php\">[ retour ]</a>";
		}
		else {
			//c'est un nouveau tour et le perso n'est pas gele
			if (!$est_gele && nouveau_tour($date, $dla)) { 
			
				// calcul du prochain tour
				$new_dla = get_new_dla($date, $dla);
				$new_dla = $new_dla + DUREE_TOUR;
				
				if ($pv > 0) { //il est encore en vie
					
					if ($pv + $recup >= $pv_max) { //il aurait recup plus de pv que son maximum
						if($bonus < -1){
							$sql = "UPDATE perso SET changementDe_perso='0' ,pm_perso=pmMax_perso, pa_perso=paMax_perso, pv_perso=pvMax_perso, bonusRecup_perso=0, bonusPerception_perso=0, bonus_perso=bonus_perso+2, xp_perso=xp_perso+1, pi_perso=pi_perso+1, or_perso=or_perso+1, bourre_perso=0, DLA_perso=FROM_UNIXTIME($new_dla) WHERE ID_perso='$id'";
							$mysqli->query($sql);
							$recup = $pv_max - $pv;
			
							// redirection
							header("location:jeu/jouer.php");
						}
						else {
							$sql = "UPDATE perso SET changementDe_perso='0' ,pm_perso=pmMax_perso, pa_perso=paMax_perso, pv_perso=pvMax_perso, bonusRecup_perso=0, bonusPerception_perso=0, bonus_perso=0, xp_perso=xp_perso+1, pi_perso=pi_perso+1, or_perso=or_perso+1, bourre_perso=0, DLA_perso=FROM_UNIXTIME($new_dla) WHERE ID_perso='$id'";
							$mysqli->query($sql);
							$recup = $pv_max - $pv;
			
							// redirection
							header("location:jeu/jouer.php");
						}
					}
					else {
						if($bonus < -1){
							$sql = "UPDATE perso SET changementDe_perso='0' ,pm_perso=pmMax_perso, pa_perso=paMax_perso, pv_perso=pv_perso+recup_perso+$bonus_recup, xp_perso=xp_perso+1, bonusRecup_perso=0, bonusPerception_perso=0, bonus_perso=bonus_perso+2, pi_perso=pi_perso+1, bourre_perso=0, or_perso=or_perso+1, DLA_perso=FROM_UNIXTIME($new_dla) WHERE ID_perso='$id'";
							$mysqli->query($sql);
			
							// redirection
							header("location:jeu/jouer.php");
						}
						else {
							$sql = "UPDATE perso SET changementDe_perso='0' ,pm_perso=pmMax_perso, pa_perso=paMax_perso, pv_perso=pv_perso+recup_perso+$bonus_recup, xp_perso=xp_perso+1, bonusRecup_perso=0, bonusPerception_perso=0, bonus_perso=0, pi_perso=pi_perso+1, bourre_perso=0, or_perso=or_perso+1, DLA_perso=FROM_UNIXTIME($new_dla) WHERE ID_perso='$id'";
							$mysqli->query($sql);
			
							// redirection
							header("location:jeu/jouer.php");
						}
					}				
				}
				else { //il est mort
					
					// NOUVEAU FONCTIONNEMENT //
					//    RESPAWN BATIMENT    //
					
					// Récupération du batiment de rappatriement le plus proche du perso
					$id_bat = selection_bat_rapat($x_perso, $y_perso, $clan);
					
					// récupération coordonnées batiment
					$sql_b = "SELECT x_instance, y_instance FROM instance_batiment WHERE id_instanceBat='$id_bat'";
					$res_b = $mysqli->query($sql_b);
					$t_b = $res_b->fetch_assoc();
					$x = $t_b['x_instance'];
					$y = $t_b['y_instance'];
					
					// On met le perso dans le batiment
					$sql = "INSERT INTO perso_in_batiment VALUES('$id','$id_bat')";
					$mysqli->query($sql);
					
					// MAJ perso
					$sql = "UPDATE perso SET x_perso='$x', y_perso='$y', arene='0', changementDe_perso='0' ,pm_perso=pmMax_perso, pa_perso=paMax_perso, pv_perso=pvMax_perso, bonusPerception_perso=0, bourre_perso=0, bonus_perso=0, DLA_perso=FROM_UNIXTIME($new_dla) WHERE ID_perso='$id'";
					$mysqli->query($sql);
		
					//redirection
					header("location:jeu/jouer.php");
				}
			}
			else {
				if ($pv > 0) { //il est encore en vie
					// redirection
					header("location:jeu/jouer.php");
				}
				else { //il est mort
					echo "<div class=\"infoi\" align=\"center\">Vous êtes mort !</div><br />";
					echo "<center><img src=\"images/mort.gif\" alt='mort'/></center><br /><br />";
					echo "Vous devez attendre votre prochain tour (";
					echo get_date($dla);
					echo ").";
					echo "<br /><br /><div align=\"center\"><a href=\"forum2/index.php\">acceder au forum</a></div>";
					echo "<br /><div align=\"center\"><a href=\"logout.php\">retour</a></div>";
				}
			}
		}
	}
}
else{
	echo "<font color=red>Vous ne pouvez pas acceder a cette page, veuillez vous logguer.</font>";
}
?>
