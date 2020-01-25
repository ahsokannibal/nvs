<?php 
session_start(); 
require_once("../../fonctions.php");

$mysqli = db_connexion();

$id = $_SESSION["id_perso"];

// recuperation du nom du perso
$sql = "SELECT nom_perso FROM perso WHERE id_perso='$id'";
$res = $mysqli->query($sql);
$t = $res->fetch_assoc();

$pseudo = $t["nom_perso"];

$id_message = $_POST["id_message"];

if (isset($_POST["submit"])) {
	
	$action = $_POST["submit"];
	
	if ($action == "Effacer") {
		$sql = "DELETE FROM message_perso WHERE id_perso='$id' AND id_message='" . $id_message . "'";
		$mysqli->query($sql);
		
		header("Location:../messagerie.php");
	}
	else if ($action == "Repondre") {
		header("Location:../nouveau_message.php?id=$id_message");
	}
	else {
		header("Location:../nouveau_message.php?id=$id_message&rep=1");
	}
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<title>Messagerie</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	</head>

	<body>

	</body>
</html>
