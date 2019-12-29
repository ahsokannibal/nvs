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
			$erreur = "<div class=\"erreur\">";
	
	?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<title>Nord VS Sud</title>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		<link href="../style.css" rel="stylesheet" type="text/css">
	</head>
	<body>
		<p align="center"><input type="button" value="Fermer la fenêtre de diplomatie" onclick="window.close()"></p>
	<?php
	if (isset($_GET["id_compagnie"])){
		
		//verification que le perso appartiens bien à la compagnie
		$sql = "SELECT id_compagnie FROM perso_in_compagnie WHERE id_perso='$id'";
		$res = $mysqli->query($sql);
		$t_verif_sec = $res->fetch_assoc();
		
		$verif_sec = $t_verif_sec["id_compagnie"];
		
		if($verif_sec == $_GET["id_compagnie"]){
			
			// verification que le perso est bien recruteur
			$sql = "SELECT poste_compagnie FROM perso_in_compagnie WHERE id_perso='$id'";
			$res = $mysqli->query($sql);
			$t_verif = $res->fetch_assoc();
			
			$verif_poste = $t_verif["poste_compagnie"];
			
			// chef ou diplomate
			if($verif_poste == '1' || $verif_poste == '4'){
				
				$id_compagnie = $_SESSION["id_compagnie"] = $_GET["id_compagnie"];
		
				// recuperation de son camp
				$sql = "SELECT id_clan FROM compagnies WHERE id_compagnie='$id_compagnie'";
				$res = $mysqli->query($sql);
				$t_clan = $res->fetch_assoc();
				
				$id_clan_compagnie = $t_clan["id_clan"]; 
				
				if($id_clan_compagnie == 1){
					$color_c = "blue";
					$nom_c = "Nordistes";
					$color_nc = "red";
					$nom_nc = "Sudistes";
				}
				if($id_clan_compagnie == 2){
					$color_c = "red";
					$nom_c = "Sudistes";
					$color_nc = "blue";
					$nom_nc = "Nordistes";
				}
				
				// recuperation des compagnies de la meme couleur que son camp
				$sql = "SELECT id_compagnie FROM compagnies WHERE id_clan='$id_clan_compagnie'";
				$res = $mysqli->query($sql);
				
				echo "<center><h1>Liste des compagnies contactables</h1></center>";
				
				echo "<h2><font color=$color_c>Liste des compagnies $nom_c</font></h2>";
				
				while ($t_compagnies = $res->fetch_assoc()){
					
					$id_compagnie_g = $t_compagnies["id_compagnie"];
					
					// recuperation du diplomate ou du chef de la compagnie
					$sql2 = "SELECT nom_perso, poste_compagnie, nom_compagnie 
							 FROM perso_in_compagnie, perso, compagnies 
							 WHERE compagnies.id_compagnie=perso_in_compagnie.id_compagnie 
								AND perso_in_compagnie.id_compagnie=$id_compagnie_g 
								AND perso_in_compagnie.id_compagnie!=$id_compagnie 
								AND perso.id_perso=perso_in_compagnie.id_perso 
								AND (poste_compagnie='1' OR poste_compagnie='4')";
					$res2 = $mysqli->query($sql2);
					
					while ($t_sec = $res2->fetch_assoc()){
						
						$nom_perso 		= $t_sec["nom_perso"];
						$poste_perso 	= $t_sec["poste_compagnie"];
						$nom_compagnie 	= $t_sec["nom_compagnie"];
						
						if($poste_perso == '1'){
							echo "<u><b>Nom :</b></u> ".$nom_compagnie."<br />";
							echo "<b>Chef :</b> ".$nom_perso." <a href='nouveau_message.php?pseudo=".$nom_perso."' target='_blank'>[ contacter ]</a><br />";
						}
						if($poste_perso == '4'){
							echo "<b>Diplomate :</b> ".$nom_perso." <a href='nouveau_message.php?pseudo=".$nom_perso."' target='_blank'>[ contacter ]</a><br /><br />";
						}						
					}
				}
				
				// recuperation des compagnies de la couleur differente que son camp
				$sql_nc = "SELECT id_compagnie FROM compagnies WHERE id_clan!='$id_clan_compagnie'";
				$res_nc = $mysqli->query($sql_nc);
				
				echo "<h2><font color=$color_nc>Liste des compagnies $nom_nc</font></h2>";
				
				while ($t_compagnies_nc = $res_nc->fetch_assoc()){
					
					$id_compagnie_g_nc = $t_compagnies_nc["id_compagnie"];
					
					// recuperation du diplomate ou du chef de la compagnie
					$sql2 = "SELECT nom_perso, poste_compagnie, nom_compagnie 
							 FROM perso_in_compagnie, perso, compagnies 
							 WHERE compagnies.id_compagnie=perso_in_compagnie.id_compagnie 
								AND perso_in_compagnie.id_compagnie=$id_compagnie_g_nc 
								AND perso_in_compagnie.id_compagnie!=$id_compagnie 
								AND perso.id_perso=perso_in_compagnie.id_perso 
								AND (poste_compagnie='1' OR poste_compagnie='4')";
					$res2 = $mysqli->query($sql2);
					
					while ($t_sec = $res2->fetch_assoc()){
						
						$nom_perso 		= $t_sec["nom_perso"];
						$poste_perso 	= $t_sec["poste_compagnie"];
						$nom_compagnie 	= $t_sec["nom_compagnie"];
						
						if($poste_perso == '1'){
							echo "<u><b>Nom :</b></u> ".$nom_compagnie."<br />";
							echo "<b>Chef :</b> ".$nom_perso." <a href='nouveau_message.php?pseudo=".$nom_perso."' target='_blank'>[ contacter ]</a><br />";
						}
						if($poste_perso == '4'){
							echo "<b>Diplomate :</b> ".$nom_perso." <a href='nouveau_message.php?pseudo=".$nom_perso."' target='_blank'>[ contacter ]</a><br /><br />";
						}						
					}
				}
			}
			else {
				echo "<font color='red'><b>Vous n'avez pas les accréditations nécéssaires pour accéder à cette page !</b></font>";
				
				$text_triche = "Tentative accés page diplo compagnie [$id_compagnie] sans y avoir les droits !";
			
				$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id', '$text_triche')";
				$mysqli->query($sql);
			}		
		}
		else {
			echo "<font color='red'><b>Vous ne faite pas parti de cette compagnie !</b></font>";
			
			$text_triche = "Tentative accés page diplo compagnie [$id_compagnie] sans même faire partie de la compagnie !";
			
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