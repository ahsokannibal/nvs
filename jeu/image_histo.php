<?php
session_start();

require_once "../fonctions.php";
	
$mysqli = db_connexion();

if (isset($_SESSION["id_perso"])) {
	
	$id 		= $_SESSION["id_perso"];
	$taille_id 	= strlen($id);
	
	$sql = "SELECT clan FROM perso WHERE id_perso='$id'";
	$res = $mysqli->query($sql);
	$t = $res->fetch_assoc();
	
	$camp_perso = $t['clan'];

	if (isset($_GET['imagename'])) {
		
		$imagename 	= $_GET['imagename'];
		
		$tab_nom_image = explode("_", $imagename);
		$taille_tab_nom_image = count($tab_nom_image);
		
		if ($taille_tab_nom_image == 2) {
		
			$tab_camp_image = explode('-', $tab_nom_image[1]);
			$camp_image 		= $tab_camp_image[0];
			
			if(($camp_perso == 1 && $camp_image == "nord") 
				|| ($camp_perso == 2 && $camp_image == "sud") 
				|| ($camp_perso == 3 && $camp_image == "indien")) {
			
				$path = "histo_carte";

				$fd = fopen ("$path/$imagename", "rb", 1);
				$data = fread($fd, filesize("$path/$imagename"));
				fclose ($fd);
				print $data;
				
			} else {
				header("location:../index.php");
			}
		}
		else {
			header("location:../index.php");
		}
	} else {
		header("location:../index.php");
	}
} else {
	header("location:../index.php");
}
?>