<?php
session_start();
require_once("../fonctions.php");

$mysqli = db_connexion();

include ('../nb_online.php');

if(@$_SESSION["id_perso"]){

	$id = $_SESSION["id_perso"];
	
	if(isset($_POST["changer"])) {
		
		$message = htmlentities(addslashes(nl2br($_POST["message"])));
		
		$sql = "UPDATE perso SET message_perso='$message' WHERE ID_perso='$id'";
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

		$sql = "SELECT message_perso FROM perso WHERE ID_perso ='$id'";
		$res = $mysqli->query($sql);
		$tab = $res->fetch_row();
		
		$message = stripslashes($tab[0]);
	?>
		<div align="center">Sur cette page vous avez la possibilité de changer votre message du jour :<br>
			<a href="profil.php">Retour</a></center><br><br> 

			<form method="post" action="">
<TEXTAREA cols="50" rows="5" name="message">
<?php 
	if($message == "") {
		echo "Aucun message"; 
	}
	else {
		echo br2nl2($message);
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