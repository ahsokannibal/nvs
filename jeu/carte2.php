<?php
session_start();

if (@$_SESSION["id_perso"]) {
	
	$id = $_SESSION["id_perso"];

	header("Content-type: image/png");//on va commencer par declarer que l'on veut creer une image
	
	//ensuite on defini la taille de l'image
	$perso_carte = imagecreate(603,603)  or die ("Cannot Initialize new GD image stream");
	//$legende_carte = imagecreatefrompng("carte/legende.png");
	$image_carte = imagecreatefrompng("carte/carte.png");
	$image_carte_bataillon = imagecreatefrompng("carte/carte.png");
	$image_carte_compagnie = imagecreatefrompng("carte/carte.png");

	//maintenant on donne une couleur a notre image (ici un fond noir)
	$fond_perso=Imagecolorallocate($perso_carte, 250, 250, 250);
	
	// on definit le font de l'image perso_carte comme transparent
	imagecolortransparent($perso_carte,$fond_perso);
	
	//Commme d'ab
	require_once "../fonctions.php";
	
	$mysqli = db_connexion();
	
	// couleurs perso_carte
	$noir 					= Imagecolorallocate($perso_carte, 0, 0, 0); // noir
	$couleur_vert 			= Imagecolorallocate($perso_carte, 10, 254, 10); // vert bien voyant
	$couleur_perso_clan1 	= Imagecolorallocate($perso_carte, 10, 10, 254); // bleu bien voyant
	$couleur_perso_clan2 	= Imagecolorallocate($perso_carte, 254, 10, 10); // rouge bien voyant
	$couleur_bat_clan1 		= Imagecolorallocate($perso_carte, 75, 75, 254); // bleu batiments
	$couleur_bat_clan2 		= Imagecolorallocate($perso_carte, 254, 75, 75); // rouge batiments
	$couleur_rail			= Imagecolorallocate($perso_carte, 200, 200, 200); // gris rails
	$couleur_brouillard1	= Imagecolorallocate($perso_carte, 222, 184, 135); // marron burlywood
	$couleur_brouillard2	= Imagecolorallocate($perso_carte, 210, 180, 140); // marron tan
	
	// couleurs image_carte
	$couleur_bataillon		= Imagecolorallocate($image_carte, 0, 0, 0); // noir
	$couleur_compagnie		= Imagecolorallocate($image_carte_compagnie, 0, 0, 0); // noir
	
	// je vais chercher le perso qui est connecté dans ma table
	$sql2 = "SELECT idJoueur_perso, clan FROM perso WHERE id_perso=$id";
	$res2 = $mysqli->query($sql2);
	$t2 = $res2->fetch_assoc();
	
	$id_joueur 	= $t2["idJoueur_perso"];
	$camp_perso	= $t2["clan"];	
	
	// je vais chercher les rails dans ma table
	$sql = "SELECT x_carte, y_carte FROM carte WHERE fond_carte='rail.gif'";
	$res = $mysqli->query($sql);
	
	while ($t = $res->fetch_assoc()){
		
		$x = $t["x_carte"];
		$y = $t["y_carte"];
		
		imagefilledrectangle ($perso_carte, (($x*3)-1), (((600-($y*3)))-1), (($x*3)+1), (((600-($y*3)))+1), $couleur_rail);
		
	}
	
	// je vais chercher les pnj dans ma table
	$sql = "SELECT x_i, y_i FROM instance_pnj WHERE pv_i>0";
	$res = $mysqli->query($sql);
	
	while ($t = $res->fetch_assoc()){
		
		$x = $t["x_i"];
		$y = $t["y_i"];
		$color = $noir;
		
		imagefilledrectangle ($perso_carte, (($x*3)-1), (((600-($y*3)))-1), (($x*3)+1), (((600-($y*3)))+1), $color);
	}
	
	// je vais chercher les perso dans ma table
	$sql = "SELECT id_perso, x_perso, y_perso, clan FROM perso WHERE pv_perso>0 and est_gele='0'";
	$res = $mysqli->query($sql);
	
	while ($t = $res->fetch_assoc()){
		
		$x 		= $t["x_perso"];
		$y 		= $t["y_perso"];
		$id_p	= $t["id_perso"];
		$clan 	= $t["clan"];
		
		if($clan == '1'){
			$color = $couleur_perso_clan1;
		}
		if($clan == '2'){
			$color = $couleur_perso_clan2;
		}
		
		if ($id_p != $id) {
			
			// On regarde si le perso est en foret
			$sql_f = "SELECT fond_carte FROM carte WHERE x_carte='$x' AND y_carte='$y'";
			$res_f = $mysqli->query($sql_f);
			$t_f = $res_f->fetch_assoc();
			
			$fond_carte = $t_f["fond_carte"];
			
			if ($fond_carte != '7.gif') {
				imagefilledrectangle ($perso_carte, (($x*3)-1), (((600-($y*3)))-1), (($x*3)+1), (((600-($y*3)))+1), $color);
			}
		} else {
			imagefilledrectangle ($perso_carte, (($x*3)-1), (((600-($y*3)))-1), (($x*3)+1), (((600-($y*3)))+1), $color);
			
			imageellipse($perso_carte, 3*$x, 600-3*$y, 20, 20, $noir);
		}
	}
	
	// je vais chercher les batiments dans ma table
	$sql = "SELECT x_instance, y_instance, camp_instance, taille_batiment FROM instance_batiment, batiment WHERE batiment.id_batiment = instance_batiment.id_batiment AND pv_instance>0";
	$res = $mysqli->query($sql);
	
	while ($t = $res->fetch_assoc()){
		
		$x 			= $t["x_instance"];
		$y 			= $t["y_instance"];
		$camp 		= $t["camp_instance"];
		$taille_bat = $t["taille_batiment"];
		
		if($camp == '1'){
			$color = $couleur_bat_clan1;
		}
		if($camp == '2'){
			$color = $couleur_bat_clan2;
		}
		
		imagefilledrectangle ($perso_carte, (($x*3)-$taille_bat), (((600-($y*3)))-$taille_bat), (($x*3)+$taille_bat), (((600-($y*3)))+$taille_bat), $color);
	}
	
	// J'ajoute le brouillard de guerre
	if ($camp_perso == '1') {
		$sql = "SELECT x_carte, y_carte FROM carte WHERE vue_nord='0'";
	}
	else if ($camp_perso == '2') {
		$sql = "SELECT x_carte, y_carte FROM carte WHERE vue_sud='0'";
	}
	$res = $mysqli->query($sql);
	
	while ($t = $res->fetch_assoc()){
		
		$x 			= $t["x_carte"];
		$y 			= $t["y_carte"];
		
		$rand = mt_rand(1,2);
		
		if ($rand == 1) {
			$couleur_brouillard = $couleur_brouillard1;
		}
		else {
			$couleur_brouillard = $couleur_brouillard2;
		}
		
		imagefilledrectangle ($perso_carte, (($x*3)-1), (((600-($y*3)))-1), (($x*3)+1), (((600-($y*3)))+1), $couleur_brouillard);
	}

	// creation de l'image perso
	imagepng($perso_carte, "carte/perso$id.png");
	
	// creation de l'image carte_sl
	imagecopymerge ($image_carte, $perso_carte, 0, 0, 0, 0, 603, 603, 100);
	imagepng($image_carte, "carte/carte_sl$id.png");
	
	imagecopymerge ($image_carte_compagnie, $perso_carte, 0, 0, 0, 0, 603, 603, 100);
	
	// creation de l'image carte
	imagepng($image_carte, "carte/carte$id.png");
	
	//**********************//
	//		BATAILLON		//
	//**********************//
	// Je vais chercher les persos du même joueur (même bataillon)
	$sql = "SELECT x_perso, y_perso FROM perso WHERE idJoueur_perso='$id_joueur'";
	$res = $mysqli->query($sql);
	
	while ($t = $res->fetch_assoc()) {
		
		$x = $t['x_perso'];
		$y = $t['y_perso'];
		
		imageellipse($image_carte, 3*$x, 600-3*$y, 20, 20, $couleur_bataillon);
		imagepng($image_carte, "carte/bataillon$id_joueur.png");
		imagepng($image_carte, "carte/carte_bataillon$id.png");
		
		imagecopymerge ($image_carte_bataillon, $image_carte, 0, 0, 0, 0, 603, 603, 100);
		imagepng($image_carte_bataillon, "carte/carte_bataillon_sl$id.png");
	}
	
	//**********************//
	//		COMPAGNIE		//
	//**********************//
	// Je vais chercher les persos dans la même compagnie
	$sql = "SELECT id_perso FROM perso_in_compagnie WHERE id_compagnie=( SELECT id_compagnie FROM perso_in_compagnie WHERE id_perso='$id' )";
	$res = $mysqli->query($sql);
	
	while ($t = $res->fetch_assoc()) {
		
		$id_perso_comp = $t["id_perso"];
		
		$sql_coord = "SELECT x_perso, y_perso FROM perso WHERE id_perso='$id_perso_comp'";
		$res_coord = $mysqli->query($sql_coord);
		$t_coord = $res_coord->fetch_assoc();
		
		$x = $t_coord['x_perso'];
		$y = $t_coord['y_perso'];
		
		imageellipse($image_carte_compagnie, 3*$x, 600-3*$y, 20, 20, $couleur_bataillon);
		imagepng($image_carte_compagnie, "carte/compagnie$id.png");
		imagepng($image_carte_compagnie, "carte/carte_compagnie$id.png");
		
		//imagecopymerge ($image_carte_compagnie, $image_carte, 0, 0, 0, 0, 603, 603, 100);
		imagepng($image_carte_compagnie, "carte/carte_compagnie_sl$id.png");
	}	
	
	ImageDestroy ($perso_carte);
	ImageDestroy ($image_carte);
	ImageDestroy ($image_carte_bataillon);
	ImageDestroy ($image_carte_compagnie);
	ImageDestroy ($legende_carte);

	header("Location:afficher_carte.php");
}
else
	echo "veuillez vous connecter";
?>