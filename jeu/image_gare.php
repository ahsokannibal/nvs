<?php
session_start();

require_once("../fonctions.php");
require_once("generer_plan_gare.php");

$mysqli = db_connexion();

if (isset($_SESSION["id_perso"])) {

	$id 		= $_SESSION["id_perso"];

	// Récupération camp
	$sql = "SELECT clan FROM perso WHERE id_perso='$id'";
	$res = $mysqli->query($sql);
	$t = $res->fetch_assoc();

	$camp = (int)$t['clan'];

	if ($camp == 1 || $camp == 2) {
		header("Content-type: image/png");//on va commencer par declarer que l'on veut creer une image
		$imageOutput = new PlanGareImage($camp, $mysqli);
		imagepng($imageOutput->GetImage());
		//$imageOutput->Clear();

		/* $path = "carte";

		$fd = fopen ("$path/$imagename", "rb", 1);
		$data = fread($fd, filesize("$path/$imagename"));
		fclose ($fd);
		print $data; */

	} else {
		header("location:../index.php");
	}
} else {
	header("location:../index.php");
}
?>
