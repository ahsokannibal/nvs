<?php
session_start();

	header("Content-type: image/png");//on va commencer par declarer que l'on veut creer une image
	
	//ensuite on defini la taille de l'image
	$perso_carte = imagecreate(303,303)  or die ("Cannot Initialize new GD image stream");
	$image_carte = imagecreatefrompng("carte/carte.png");

	//maintenant on donne une couleur a notre image (ici un fond noir)
	$fond_perso=Imagecolorallocate($perso_carte, 250, 250, 250);
	
	// on definit le font de l'image perso_carte comme transparent
	imagecolortransparent($perso_carte,$fond_perso);
	
	//Commme d'ab
	require_once "../fonctions.php";
	
	$mysqli = db_connexion();
	
	$noir 					= Imagecolorallocate($perso_carte, 0, 0, 0); // noir
	$couleur_vert 			= Imagecolorallocate($perso_carte, 10, 254, 10); // vert bien voyant
	$couleur_perso_clan1 	= Imagecolorallocate($perso_carte, 14, 18, 254); // bleu bien voyant
	$couleur_perso_clan2 	= Imagecolorallocate($perso_carte, 254, 10, 10); // rouge bien voyant
	
	// je vais chercher les pnj dans ma table
	$sql = "SELECT x_i, y_i FROM instance_pnj WHERE pv_i>0";
	$res = $mysqli->query($sql);
	while ($t = $res->fetch_assoc()){
		$x = $t["x_i"];
		$y = $t["y_i"];
		$color = $noir;
		
		imagefilledrectangle ($perso_carte, (($x*3)-1), (((300-($y*3)))-1), (($x*3)+1), (((300-($y*3)))+1), $color);
	}
	
	// je vais chercher les perso dans ma table
	$sql = "SELECT x_perso, y_perso, clan FROM perso WHERE pv_perso>0 and est_gele='0'";
	$res = $mysqli->query($sql);
	while ($t = $res->fetch_assoc()){
		$x = $t["x_perso"];
		$y = $t["y_perso"];
		$clan = $t["clan"];
		
		if($clan == '1'){
			$color = $couleur_perso_clan1;
		}
		if($clan == '2'){
			$color = $couleur_perso_clan2;
		}
	
		imagefilledrectangle ($perso_carte, (($x*3)-1), (((300-($y*3)))-1), (($x*3)+1), (((300-($y*3)))+1), $color);
	}

	imagepng($perso_carte, "carte/perso.png");
	imagecopymerge ($image_carte, $perso_carte, 0, 0, 0, 0, 303, 303, 100);
	
	// Creation de la carte histo
	$date = date('D-d-M-Y-H-i-s');
	imagepng($image_carte, "histo_carte/carte-$date.png");
	
	ImageDestroy ($perso_carte);
	ImageDestroy ($image_carte);

	header("Location:afficher_carte.php");
?>