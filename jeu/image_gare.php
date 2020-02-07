<?php
session_start();

require_once("../fonctions.php");

$mysqli = db_connexion();

if (isset($_SESSION["id_perso"])) {
	
	$id 		= $_SESSION["id_perso"];
	
	// Récupération camp
	$sql = "SELECT clan FROM perso WHERE id_perso='$id'";
	$res = $mysqli->query($sql);
	$t = $res->fetch_assoc();
	
	$camp = $t['clan'];
	
	if ($camp == 1) {
		$neg 			= -8;
		$taille_camp 	= 4;
		$camp_expected 	= "nord";
	} else {
		$neg 			= -7;
		$taille_camp 	= 3;
		$camp_expected 	= "sud";
	}

	if (isset($_GET['imagename'])) {
		
		$imagename 	= $_GET['imagename'];
		$camp_img 	= substr($imagename, $neg, $taille_camp);
		
		if ($camp_img == $camp_expected) {
		
			$path = "carte";

			$fd = fopen ("$path/$imagename", "rb", 1);
			$data = fread($fd, filesize("$path/$imagename"));
			fclose ($fd);
			print $data;
			
		} else {
			header("location:../index.php");
		}
	} else {
		header("location:../index.php");
	}
} else {
	header("location:../index.php");
}
?>