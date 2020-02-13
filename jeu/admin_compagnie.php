<?php
session_start();
require_once("../fonctions.php");

$mysqli = db_connexion();

include ('../nb_online.php');

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
		//$erreur = "<div class=\"erreur\">";

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<title>Nord VS Sud</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	</head>
	<body>
<?php
if(isset($_GET["id_compagnie"])) {
	
	$id_compagnie = $_GET["id_compagnie"];
	
	$verif1 = preg_match("#^[0-9]+$#i",$id_compagnie);
	
	if($verif1){
	
		// verification que le perso est bien le chef de la compagnie (anti-triche)
		$sql = "SELECT poste_compagnie FROM perso_in_compagnie WHERE id_perso=$id AND id_compagnie=$id_compagnie";
		$res = $mysqli->query($sql);
		$ch = $res->fetch_assoc();
		
		$ok_chef = $ch["poste_compagnie"];
		
		if($ok_chef == 1) {
		
			if(isset($_POST["image"])){
				
				if($_POST["image"] != "") {
					
					$image = addslashes($_POST["image"]);
					
					$sql = "UPDATE compagnies SET image_compagnie='$image' WHERE id_compagnie=$id_compagnie";
					$mysqli->query($sql);
					
					echo "<font color = green>Changement de l'image effectué</font>";
				}
				else {
					echo "<font color = red>Veuillez bien remplir le champ pour le changement d'image</font>";
				}
			}
			if(isset($_POST["virer"])) {
				
				if($_POST["virer"] != "") {
					
					$perso_a_virer = $_POST["virer"];
					
					// verification que le membre appartienne bien a la compagnie
					$sql = "SELECT perso.id_perso FROM perso, perso_in_compagnie WHERE perso.id_perso=perso_in_compagnie.id_perso AND id_compagnie=$id_compagnie AND nom_perso='$perso_a_virer' AND poste_compagnie!=1";
					$res = $mysqli->query($sql);
					$t_v = $res->fetch_assoc();
					
					$id_perso_a_virer = $t_v["id_perso"];
					
					// le perso existe et appartient bien a la compagnie
					if ($id_perso_a_virer != 0) {
						
						// recuperation des information sur la compagnie
						$sql = "SELECT genie_civil, nom_compagnie FROM compagnies WHERE id_compagnie=$id_compagnie";
						$res = $mysqli->query($sql);
						$sec = $res->fetch_assoc();
						
						$genie_compagnie 	= $sec["genie_civil"];
						$nom_compagnie		= addslashes($sec["nom_compagnie"]);
					
						// on vire le perso de la compagnie
						$sql = "DELETE FROM perso_in_compagnie WHERE id_perso=$id_perso_a_virer AND id_compagnie=$id_compagnie";
						$mysqli->query($sql);
						
						// on enleve le perso de la banque
						$sql = "DELETE FROM banque_compagnie WHERE id_perso=$id_perso_a_virer";
						$mysqli->query($sql);
						
						if ($genie_compagnie) {
							// On suprime les competences de construction
							
							// Construire pont
							$sql = "DELETE FROM perso_as_competence WHERE id_perso='$id_perso_a_virer' AND id_competence='23'";
							$mysqli->query($sql);
							
							// Construire tour de visu
							$sql = "DELETE FROM perso_as_competence WHERE id_perso='$id_perso_a_virer' AND id_competence='24'";
							$mysqli->query($sql);
							
							// Construire Hopital
							$sql = "DELETE FROM perso_as_competence WHERE id_perso='$id_perso_a_virer' AND id_competence='27'";
							$mysqli->query($sql);
							
							// Construire Fortin
							$sql = "DELETE FROM perso_as_competence WHERE id_perso='$id_perso_a_virer' AND id_competence='28'";
							$mysqli->query($sql);
							
							// Construire Gare
							$sql = "DELETE FROM perso_as_competence WHERE id_perso='$id_perso_a_virer' AND id_competence='63'";
							$mysqli->query($sql);
							
							// Construire Rails
							$sql = "DELETE FROM perso_as_competence WHERE id_perso='$id_perso_a_virer' AND id_competence='64'";
							$mysqli->query($sql);
						}
						
						// -- FORUM
						// Récupération de l'id de l'utilisateur sur le forum 
						$sql = "SELECT user_id FROM ".$table_prefix."users WHERE username IN 
									(SELECT nom_perso FROM perso WHERE idJoueur_perso IN 
										(SELECT idJoueur_perso FROM perso WHERE id_perso='$id_perso_a_virer') AND chef='1')";
						$res = $mysqli->query($sql);
						$t = $res->fetch_assoc();
						
						$id_user_forum = $t['user_id'];
						
						// Récupération de l'id du group de la compagnie sur le forum
						$sql = "SELECT group_id FROM ".$table_prefix."groups WHERE group_name='$nom_compagnie'";
						$res = $mysqli->query($sql);
						$t = $res->fetch_assoc();
						
						$id_group_forum = $t['group_id'];
						
						// Est ce qu'il a d'autres persos dans la compagnie en dehors de celui qui part
						$sql = "SELECT * FROM perso_in_compagnie WHERE id_perso IN (SELECT id_perso FROM perso WHERE idJoueur_perso IN (SELECT idJoueur_perso FROM perso WHERE id_perso='$id_perso_a_virer'))";
						$res = $mysqli->query($sql);
						$verif = $res->num_rows;
						
						if ($verif == 0) {
							// Suppression de l'utilisateur du groupe
							$sql = "DELETE FROM ".$table_prefix."user_group WHERE group_id='$id_group_forum' AND user_id='$id_user_forum'";
							$mysqli->query($sql);
						}
						
						echo "<font color = blue>Vous venez de virer $perso_a_virer [".$id_perso_a_virer."]de votre compagnie</font>";
					}
					else {
						echo "<font color = red>Ce perso n'existe pas ou ne fait pas parti de votre compagnie ou est un chef de la compagnie</font>";
					}
				}
				else {
					echo "<font color = red>Veuillez bien remplir le champ pour virer un membre</font>";
				}
			}
		
			echo "<h3><center>Page d'administration de la compagnie</center></h3>";
			echo "<center><a href='chef_compagnie.php?id_compagnie=$id_compagnie'>changer de chef</a></center>";
			echo "<center><a href='resume_compagnie.php?id_compagnie=$id_compagnie'>changer le resume de la compagnie</a></center>";
			echo "<center><a href='description_compagnie.php?id_compagnie=$id_compagnie'>changer la description de la compagnie</a></center>";
			echo "<center><a href='grade_compagnie.php?id_compagnie=$id_compagnie'>donner des postes aux membres de sa compagnie</a></center>";
			
			echo "<hr>";
			
			echo "<form action=\"admin_compagnie.php?id_compagnie=$id_compagnie\" method=\"post\" name=\"image\">";
			echo "<div align=\"center\"><br>";
			echo "changer l'image de la compagnie (url vers l'image) :<br>";
			echo "<input name=\"image\" type=\"text\" value=\"\" onFocus=\"this.value=''\" style=\"width: 400px;\" maxlength=\"200\">";
			echo "<input type=\"submit\" name=\"Submit\" value=\"ok\">";
			echo "</div>";
			echo "</form>";
			
			echo "<br /><br />";
			
			echo "<form action=\"admin_compagnie.php?id_compagnie=$id_compagnie\" method=\"post\" name=\"virer\">";
			echo "<div align=\"center\">";
			echo "Virer un membre de sa compagnie (taper le pseudo) : ";
			echo "<input name=\"virer\" type=\"text\" value=\"\" onFocus=\"this.value=''\" maxlength=\"100\">";
			echo "<input type=\"submit\" name=\"Submit\" value=\"virer!\">";
			echo "</div>";
			echo "</form>";
			
			echo "<br /><br />";
			
			echo "<form action=\"nouveau_message.php?id_compagnie=$id_compagnie\" method=\"post\" name=\"mail\">";
			echo "<div align=\"center\"><br>";
			echo "<center>envoyer un MP a tout les membres de sa compagnie :</center>";
			echo "<TEXTAREA cols=\"50\" rows=\"5\" name=\"contenu\">";
			echo "</TEXTAREA><br><input type=\"submit\" name=\"envoi\" value=\"envoyer\">";
			echo "</div>";
			echo "</form>";
			
			echo "<a href='compagnie.php'>[retour a la page compagnie]</a>";
				
		}
		else {
			echo "<font color = red>Vous n'avez pas le droit d'acceder à cette page !</font>";
			
			$text_triche = "Tentative accés page admin compagnie [$id_compagnie] sans y avoir les droits";
			
			$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id', '$text_triche')";
			$mysqli->query($sql);
		}
	}
	else {
		echo "<center>La compagnie demandé n'existe pas</center>";
		
		$param_test 	= addslashes($id_compagnie);
		$text_triche 	= "Test parametre sur page admin compagnie, parametre id_compagnie invalide tenté : $param_test";
			
		$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id', '$text_triche')";
		$mysqli->query($sql);
	}
}


?>
</body>
</html>
<?php
	}
}
else{
	echo "<font color=red>Vous ne pouvez pas acceder a cette page, veuillez vous logguer.</font>";
}?>
