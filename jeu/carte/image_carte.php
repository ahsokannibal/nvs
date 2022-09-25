<?php
session_start();

if (isset($_SESSION["id_perso"])) {
	
	$id 		= $_SESSION["id_perso"];
	$taille_id 	= strlen($id);

	if (isset($_GET['imagename'])) {
		
		$imagename 	= $_GET['imagename'];
		$id_img 	= substr($imagename, -4 -$taille_id, $taille_id);
		
		if ($id_img == $id) {
		
			$fd = fopen ("joueur_carte/$imagename", "rb", 1);
			
			$data = fread($fd, filesize("joueur_carte/$imagename"));
			fclose ($fd);
			print $data;
			
		} else {
			header("location:../../index.php");
		}
	} else {
		header("location:../../index.php");
	}
} else {
	header("location:../../index.php");
}
?>