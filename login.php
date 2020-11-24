<?php
session_start();

require_once("fonctions.php");

$mysqli = db_connexion();

if(isset ($_POST['pseudo']) && isset ($_POST['password']) && isset ($_POST['captcha'])) {
	
	// recuperation des variables post
	$pseudo 	= $_POST['pseudo'];
	$mdp 		= $_POST['password'];
	$captcha	= $_POST['captcha'];
	
	// test champs vide
	if ( trim($pseudo) == "" || trim($mdp) == "" || trim($captcha) == "") { 
		echo "<center><font color='red'>Merci de remplir tous les champs</font><br />";
		echo "<a href=\"index.php\" class='btn btn-primary'>retour</a></center>";
	}
	else {
		if (/*!filtre($pseudo,1,20) ||*/ ctype_digit($pseudo) || strpos($pseudo,'--') !== false) {
			echo "<center><font color='red'>Le Pseudo est incorrect!</font><br />";
			echo "<a href=\"index.php\" class='btn btn-primary'>retour</a></center>";
		}
		else {
			
			if ($captcha == $_SESSION["code"]) {
		
				// passage du mdp en md5
				$mdp = md5($mdp); 
				
				// recuperation de l'id du joueur et log du joueur
				$sql = "SELECT id_joueur, mdp_joueur, id_perso FROM joueur,perso WHERE joueur.id_joueur=perso.idJoueur_perso and nom_perso='$pseudo' and chef='1'";		
				$res = $mysqli->query($sql);
				$t_user = $res->fetch_assoc();
				$mdp_j = $t_user["mdp_joueur"];
				
				if($mdp == $mdp_j){
					
					$id_joueur = $_SESSION["ID_joueur"] = $t_user["id_joueur"];
					
					$_SESSION["id_perso"] = $t_user["id_perso"];
					
					$date = time();
					
					// recuperation de l'ip du joueur
					$ip_joueur = realip();
					
					// Est ce que ce joueur est déjà présent avec cette IP
					$sql = "SELECT * FROM joueur_as_ip WHERE ip_joueur = '$ip_joueur' AND id_joueur='$id_joueur'";
					$res = $mysqli->query($sql);
					$nb_ip = $res->num_rows;
					
					if ($nb_ip > 0) {
						// Maj date dernier releve sur enregistrement existant
						$sql = "UPDATE joueur_as_ip SET date_dernier_releve = FROM_UNIXTIME($date) WHERE id_joueur = '$id_joueur' AND ip_joueur = '$ip_joueur'";
						$mysqli->query($sql);
					} else {
						// nouvel enregistrement
						$sql = "INSERT INTO joueur_as_ip VALUES ('$id_joueur','$ip_joueur',FROM_UNIXTIME($date),FROM_UNIXTIME($date))";
						$mysqli->query($sql);
					}
					
					header("location:jeu/jouer.php");
				}
				else {
					echo "<center><font color='red'>mot de passe incorrect<br />";
					echo "<a href=\"index.php\" class='btn btn-primary'>retour</a></center>";
				}
			}
			else {
				echo "<center><p style='color:#FFFFFF; font-size:20px'><span style='background-color:#FF0000;'>Le code captcha entré ne correspond pas! Veuillez réessayer.</span></p>";
				echo "<a href=\"index.php\" class='btn btn-primary'>retour</a></center>";
			}
		}
	}
}
?>