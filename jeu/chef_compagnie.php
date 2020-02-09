<?php
session_start();
require_once("../fonctions.php");

$mysqli = db_connexion();

include ('../nb_online.php');

// recupération config jeu
$dispo = config_dispo_jeu($mysqli);
$admin = admin_perso($mysqli, $_SESSION["id_perso"]);

if($dispo || $admin){

	if (isset($_SESSION["id_perso"])) {
		
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
		
		$verif = preg_match("#^[0-9]+$#i",$_GET["id_compagnie"]);
		
		if($verif){
			
			// on a choisi un nouveau chef
			if(isset($_POST["chef"])) { 
			
				$ok = 0;
				
				if($_POST["chef"] != "") {
					
					$nouveau_chef = $_POST["chef"];
					
					// recuperation des noms des persos dans la compagnie
					$sql = "SELECT nom_perso, perso_in_compagnie.id_perso FROM perso, perso_in_compagnie WHERE perso_in_compagnie.id_perso=perso.id_perso AND id_compagnie=$id_compagnie AND perso_in_compagnie.id_perso!=$id";
					$res = $mysqli->query($sql);
					
					while ($noms = $res->fetch_assoc()) {
						$nom_p = $noms["nom_perso"];
						$id_p = $noms["id_perso"];
						if($nouveau_chef == $nom_p) {
							$ok = 1;
							break;
						}
					}
					
					if($ok) {
						// maj du chef
						$sql = "UPDATE perso_in_compagnie SET poste_compagnie=1 WHERE id_perso=$id_p";
						$mysqli->query($sql);
						
						$sql = "UPDATE perso_in_compagnie SET poste_compagnie=0 WHERE id_perso=$id";
						$mysqli->query($sql);
						
						echo "<br><center><font color='blue'>$nom_p devient le nouveau chef de votre compagnie</font></center><br>";
						
						echo "<center><a href='compagnie.php'> [retour a la page de compagnie] </a></center>";
					}
					else {
						echo "<center><font color='red'>Ce perso n'existe pas ou n'appartient pas a votre compagnie.</font></center>";
					}
				}
				else {
					echo "<center><font color='red'>Veuillez remplir le champ pour designer un nouveau chef</font></center>";
				}
			}
			else {
				
				// verification que le perso est bien le chef de la compagnie (ou que la compagnie existe toujours)
				$sql = "SELECT poste_compagnie FROM perso_in_compagnie WHERE id_perso='$id' AND id_compagnie='$id_compagnie'";
				$res = $mysqli->query($sql);
				$ch = $res->fetch_assoc();
				
				$ok_chef = $ch["poste_compagnie"];
				
				if($ok_chef == 1) {
				
					echo "<center><h4>Changement de chef</h4></center><br>";
					echo "<form action=\"chef_compagnie.php?id_compagnie=$id_compagnie\" method=\"post\" name=\"chef\">";
					echo "<div align=\"center\"><br>";
					echo "Nom du chef : ";
					echo "<input name=\"chef\" type=\"text\" value=\"\" onFocus=\"this.value=''\" maxlength=\"50\">";
					echo "<input type=\"submit\" name=\"Submit\" value=\"ok\">";
					echo "</div>";
					echo "</form>";
					
					echo "<a href='compagnie.php'> [retour a la page de compagnie] </a>";
				}
				else {
					echo "<center><font color='red'>Vous n'avez pas le droit d'acceder à cette page !</font></center>";
					
					$text_triche = "Tentative accés page chef compagnie [$id_compagnie] sans y avoir les droits";
			
					$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id', '$text_triche')";
					$mysqli->query($sql);
				}
			}
		}
		else {
			echo "<center><font color='red'>La compagnie demandé n'existe pas</font></center>";
			
			$param_test 	= addslashes($id_compagnie);
			$text_triche 	= "Test parametre sur page chef compagnie, parametre id_compagnie invalide tenté : $param_test";
				
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
	$_SESSION = array(); // On écrase le tableau de session
	session_destroy(); // On détruit la session
	
	header("Location:../index2.php");
}
?>