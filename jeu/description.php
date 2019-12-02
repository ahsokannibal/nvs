<?php
session_start();
require_once("../fonctions.php");
require_once("f_entete.php");

$mysqli = db_connexion();

include ('../nb_online.php');

if(@$_SESSION["id_perso"]){
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<title>Description</title>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		<link href="../style.css" rel="stylesheet" type="text/css">
	</head>
	
	<body>
		<div align="center"><h2>Description</h2></div>
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
	?>
		<p align="center"><input type="button" value="Fermer cette fenêtre" onclick="window.close()"></p>
	<?php
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
			
			if ($id < 50000) {
				echo "<table align=\"center\" width=\"80%\" border=1><tr><th background='../forum2/img/Chronicles/background.jpg'>description</th></tr>";
				$sql = "SELECT description_perso FROM perso WHERE id_perso = $id";
				$result = $mysqli->query($sql);
				$tabAttr = $result->fetch_assoc();
				$description = stripslashes($tabAttr["description_perso"]);
				
				echo "<tr><td background='../images/texture_parchemin.gif'>".bbcode(htmlentities(stripslashes($description)))."</td></tr></table>";
			}
			else {
				if($id >= 200000) {
					
					// recuperation du type de pnj
					$sql = "SELECT id_pnj FROM instance_pnj WHERE idInstance_pnj='$id'";
					$res = $mysqli->query($sql);
					$t = $res->fetch_assoc();
					$type_pnj = $t["id_pnj"];
				
					echo "<table align=\"center\" width=\"80%\" border=1><tr><th >description</th></tr>";
					$sql = "SELECT description_pnj FROM pnj WHERE id_pnj = '$type_pnj'";
					$result = $mysqli->query($sql);
					$tabAttr = $result->fetch_assoc();
					$description = stripslashes($tabAttr["description_pnj"]);
				
					echo "<tr><td>".$description."</td></tr></table>";
				}
				else {
					// recuperation du type de batiment
					$sql = "SELECT id_batiment FROM instance_batiment WHERE id_instanceBat='$id'";
					$res = $mysqli->query($sql);
					$t = $res->fetch_assoc();
					$type_bat = $t["id_batiment"];
					
					echo "<table align=\"center\" width=\"80%\" border=1><tr><th >description</th></tr>";
					$sql = "SELECT description FROM batiment WHERE id_batiment = '$type_bat'";
					$result = $mysqli->query($sql);
					$tabAttr = $result->fetch_assoc();
					$description = stripslashes($tabAttr["description"]);
				
					echo "<tr><td>".$description."</td></tr></table>";
				}
			}
		}
		else {
			// le perso n'existe pas
			echo "<br/><center><b>Erreur :</b> Ce perso n'existe pas !</center>";
		}
	}
	else {
		// rien ^^
	}
	?>
	</body>
</html>
<?php
}
else {
	echo "<font color=red>Vous ne pouvez pas accéder à cette page, veuillez vous loguer.</font>";
}
?>