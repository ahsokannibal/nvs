<?php
session_start();
require_once("../fonctions.php");

$mysqli = db_connexion();

include ('../nb_online.php');

if(@$_SESSION["id_perso"]){

	$id = $_SESSION["id_perso"];
	
	if(isset($_POST["changer"])) {
		
		$desc = addslashes($_POST["description"]);
		
		$sql = "UPDATE perso SET description_perso='$desc' WHERE ID_perso='$id'";
		$mysqli->query($sql);
		
		header("Location:profil.php");
	}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<title>Nord VS Sud</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	</head>

	<body>
	<?php

		$sql = "SELECT description_perso FROM perso WHERE ID_perso ='$id'";
		$res = $mysqli->query($sql);
		$tab = $res->fetch_row();
		
		$desc = stripslashes($tab[0]);
	?>
		<div align="center">Sur cette page vous avez la possibilité de changer la description de votre perso :<br>
			<a href="profil.php">Retour</a></center><br><br> 

			<form method="post" action="">
			
<TEXTAREA cols="100" rows="20" name="description">
<?php 
	if($desc == "") {
		echo "Aucune description";
	}
	else {
		echo br2nl2($desc);
	}
?>
</TEXTAREA><br>

				<input type="submit" name="changer" value="changer">
			</form>
		</div>
	</body>
</html>
<?php
}
else{
	echo "<font color=red>Vous ne pouvez pas accéder à cette page, veuillez vous loguer.</font>";
}
?>