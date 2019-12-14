<?php
session_start();
require_once("../fonctions.php");

$mysqli = db_connexion();

include ('../nb_online.php');

// recuperation config jeu
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
			//$erreur = "<div class=\"erreur\">";
	
	?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<title>Nord VS Sud</title>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		<link href="../style.css" rel="stylesheet" type="text/css">
	</head>
	
	<body>
		<div align="center"><h2>Recrutement</h2></div>
	<?php
	if(isset($_GET["id_compagnie"])) {
		
		$verif = preg_match("#^[0-9]+$#i",$_GET["id_compagnie"]);
		
		if($verif){
	
			$id_compagnie = $_GET["id_compagnie"];
		
			// verification que le perso appartient bien a la compagnie
			$sql = "SELECT id_compagnie FROM perso_in_compagnie WHERE id_perso='$id' AND attenteValidation_compagnie='0'";
			$res = $mysqli->query($sql);
			$verif = $res->num_rows;
				
			if($verif){
			
				if (isset($_POST["rec"]) && $_POST["rec"]=="recruter"){
					
					if(isset($_POST["recrut"])){
						
						$t_r = explode(",",$_POST["recrut"]);
						$new_recrue = $t_r[0];
						$nom_recrue = $t_r[1];
						
						// on met a jour le champ attenteValidation de la table perso_in_compagnie
						$sql = "UPDATE perso_in_compagnie SET attenteValidation_compagnie='0' WHERE id_perso=$new_recrue";
						$mysqli->query($sql);
						
						// insertion dans la table banque compagnie
						$sql = "INSERT INTO banque_compagnie VALUES ($new_recrue,'0','','')";
						$mysqli->query($sql);
						
						echo "<center>".$nom_recrue."[".$new_recrue."] vient de rentrer dans la compagnie</center>";
					}
				}
				
				if (isset($_POST["ref"]) && $_POST["ref"]=="refuser"){
					
					if(isset($_POST["recrut"])){
						
						$t_r = explode(",",$_POST["recrut"]);
						$new_recrue = $t_r[0];
						$nom_recrue = $t_r[1];
						
						// on l'enleve 
						$sql = "DELETE FROM perso_in_compagnie WHERE id_perso='$new_recrue'";
						$mysqli->query($sql);
						
						// recup nom compagnie
						$sql = "SELECT nom_compagnie FROM compagnies WHERE id_compagnie='$id_compagnie'";
						$res = $mysqli->query($sql);
						$t_s = $res->fetch_assoc();
						
						$nom_groupe = $t_s["nom_compagnie"];
						
						// on lui envoi un mp
						$message = "Bonjour $nom_recrue,<br /><br />J\'ai le regret de t\'annoncer que ton entrée dans la compagnie ". addslashes($nom_groupe) ." a été refusé.";
						$objet = "Refus d\'incorporation dans la compagnie";
						
						$lock = "LOCK TABLE (joueur) WRITE";
						$mysqli->query($lock);
						
						$sql = "INSERT INTO message (expediteur_message, date_message, contenu_message, objet_message) VALUES ( '" . addslashes($nom_groupe) . "', NOW(), '" . $message . "', '" . $objet . "')";
						$res = $mysqli->query($sql);
						$id_message = $mysqli->insert_id;
						
						$unlock = "UNLOCK TABLES";
						$mysqli->query($unlock);
						
						$sql = "INSERT INTO message_perso VALUES ('$id_message','$new_recrue','1','0','0')";
						$res = $mysqli->query($sql);
						
						echo "<center>".$nom_recrue."[".$new_recrue."] vient d'être refusé de la compagnie</center>";
					}
				}
				
				// recuperation de tout les persos qui sont en attente de validation pour entrer dans la compagnie
				$sql = "SELECT nom_perso, perso_in_compagnie.id_perso FROM perso_in_compagnie, perso WHERE perso.ID_perso=perso_in_compagnie.id_perso AND id_compagnie=$id_compagnie AND attenteValidation_compagnie='1'";
				$res = $mysqli->query($sql);
				$num_a = $res->num_rows;
				
				// il y a des persos en attente de validation
				if($num_a) { 
				
					echo "<center><form method=\"post\" action=\"recrut_compagnie.php?id_compagnie=$id_compagnie\">";
					echo "liste des persos en attente :";
					echo "<select name=\"recrut\">";
					
					while ($t_a = $res->fetch_assoc()){
						
						$id_p 	= $t_a["id_perso"];
						$nom_p 	= $t_a["nom_perso"];
						
						echo "<center><OPTION value=".$id_p.",".$nom_p.">".$nom_p."[".$id_p."]</option><br></center>";
					}
					
					echo "</select>";
					echo "&nbsp;<input type=\"submit\" name=\"rec\" value=\"recruter\">&nbsp;<input type=\"submit\" name=\"ref\" value=\"refuser\">";
					echo "</form></center>";
				}
				else {
					echo "<center><font color = blue>Il n y a aucun perso en attente de validation</font></center>";
				}
				echo "<a href='compagnie.php'> [acceder a la page de la compagnie] </a>";
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
		echo "<font color=red>Vous ne pouvez pas accéder à cette page, veuillez vous loguer.</font>";
	}?>
<?php
}
else {
	// logout
	$_SESSION = array(); // On ecrase le tableau de session
	session_destroy(); // On detruit la session
	
	header("Location: index2.php");
}
?>