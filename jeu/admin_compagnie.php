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
			
			if(isset($_POST["detruire"])){
				
				if($_POST["detruire"] != ""){
					
					// verification de la phrase
					if($_POST["detruire"] == "destruction du groupe"){
						
						// on vire les membres de la compagnie
						$sql = "DELETE FROM perso_in_compagnie WHERE id_compagnie=$id_compagnie";
						$mysqli->query($sql);
						
						// on detruit la compagnie
						$sql = "DELETE FROM compagnies WHERE id_compagnie=$id_compagnie";
						$mysqli->query($sql);
						
						echo "<font color=blue>Destruction de la compagnie terminée</font>";
					}
					else {
						echo "<font color = red>La phrase entrée pour detruire votre compagnie est incorrecte</font>";
					}
				}
				else {
					echo "<font color = red>Si vous souhaitez detruire votre compagnie, rentrez la phrase approprie dans le champ correspondant</font>";
				}
			}
		
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
					
						// on vire le perso de la compagnie
						$sql = "DELETE FROM perso_in_compagnie WHERE id_perso=$id_perso_a_virer AND id_compagnie=$id_compagnie";
						$mysqli->query($sql);
						
						echo "<font color = blue>Vous venez de virer $perso_a_virer de votre compagnie</font>";
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
			echo "changer l'image de la compagnie (adresse internet) :<br>";
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
			
			echo "<form action=\"admin_compagnie.php?id_compagnie=$id_compagnie\" method=\"post\" name=\"detruire\">";
			echo "<div align=\"center\">";
			echo "<u>Pour detruire sa compagnie, tapez la phrase suivante dans le champ qui suit :</u><br>";
			echo "<font color = red>destruction du groupe</font><br>";
			echo "<input name=\"detruire\" type=\"text\" value=\"\" onFocus=\"this.value=''\" maxlength=\"100\">";
			echo "<input type=\"submit\" name=\"Submit\" value=\"detruire!\">";
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
		}
	}
	else {
		echo "<center>La compagnie demandé n'existe pas</center>";
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
