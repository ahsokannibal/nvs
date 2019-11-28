<?php
session_start();
if (@$_SESSION["id_perso"]) {
	$id = $_SESSION["id_perso"];

	header("Content-type: image/png");//on va commencer par declarer que l'on veut creer une image
	
	//ensuite on defini la taille de l'image
	$perso_carte = imagecreate(603,603)  or die ("Cannot Initialize new GD image stream");
	$legende_carte = imagecreatefrompng("carte_tmp/legende.png");
	$image_carte = imagecreatefrompng("carte_tmp/carte.png");

	//maintenant on donne une couleur a notre image (ici un fond noir)
	$fond_perso=Imagecolorallocate($perso_carte, 250, 250, 250);
	
	// on definit le font de l'image perso_carte comme transparent
	imagecolortransparent($perso_carte,$fond_perso);
	
	//Commme d'ab
	require_once "../fonctions.php";
	
	$mysqli = db_connexion();
	
	$noir = Imagecolorallocate($perso_carte, 0, 0, 0); // noir
	$couleur_pnj = Imagecolorallocate($perso_carte, 10, 254, 10); // vert bien voyant
	$couleur_perso_clan1 = Imagecolorallocate($perso_carte, 10, 10, 254); // bleu bien voyant
	$couleur_perso_clan2 = Imagecolorallocate($perso_carte, 254, 10, 10); // rouge bien voyant
	$couleur_bat_clan1 = Imagecolorallocate($perso_carte, 176, 176, 254); // lightsteelblue
	$couleur_bat_clan2 = Imagecolorallocate($perso_carte, 254, 176, 176); // 
	
	// je vais chercher les pnj dans ma table
	$sql = "SELECT x_i, y_i FROM instance_pnj WHERE pv_i>0";
	$res = $mysqli->query($sql);
	while ($t = $res->fetch_assoc()){
		$x = $t["x_i"];
		$y = $t["y_i"];
		$color = $couleur_pnj;
		
		imagefilledrectangle ($perso_carte, (($x*3)-1), (((600-($y*3)))-1), (($x*3)+1), (((600-($y*3)))+1), $color);
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
		
		imagefilledrectangle ($perso_carte, (($x*3)-1), (((600-($y*3)))-1), (($x*3)+1), (((600-($y*3)))+1), $color);
	}
	
	// je vais chercher les batiments dans ma table
	$sql = "SELECT x_instance, y_instance, camp_instance FROM instance_batiment WHERE pv_instance>0";
	$res = $mysqli->query($sql);
	while ($t = $res->fetch_assoc()){
		
		$x = $t["x_instance"];
		$y = $t["y_instance"];
		$camp = $t["camp_instance"];
		
		if($camp == '1'){
			$color = $couleur_bat_clan1;
		}
		if($camp == '2'){
			$color = $couleur_bat_clan2;
		}
		
		imagefilledrectangle ($perso_carte, (($x*3)-1), (((600-($y*3)))-1), (($x*3)+1), (((600-($y*3)))+1), $color);
	}

	// je vais chercher le perso qui est connecté dans ma table
	$sql2 = "SELECT x_perso, y_perso FROM perso WHERE id_perso=$id";
	$res2 = $mysqli->query($sql2);
	$t2 = $res2->fetch_assoc();
	$x2 = $t2["x_perso"];
	$y2 = $t2["y_perso"];
	imageellipse($perso_carte, 3*$x2, 600-3*$y2, 20, 20, $noir);
	imagepng($perso_carte, "carte_tmp/perso$id.png");
	
	imagecopymerge ($image_carte, $perso_carte, 0, 0, 0, 0, 603, 603, 100);
	imagepng($image_carte, "carte_tmp/carte_sl$id.png");
	
	imagecopymerge ($image_carte, $legende_carte, 0, 0, 0, 0, 603, 603, 40);
	
	// on affiche l'image
	imagepng($image_carte, "carte_tmp/carte$id.png");
	ImageDestroy ($perso_carte);
	ImageDestroy ($image_carte);
	ImageDestroy ($legende_carte);

	header("Location: afficher_carte.php");
}
else
	echo "veuillez vous connecter";
?>