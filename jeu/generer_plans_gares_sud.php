<?php
session_start();

header("Content-type: image/png");//on va commencer par declarer que l'on veut creer une image

//ensuite on defini la taille de l'image
$gare_carte = imagecreate(603,603)  or die ("Cannot Initialize new GD image stream");
$image_carte = imagecreatefrompng("carte/carte.png");
$image_p = imagecreatetruecolor(603, 603);
imagecopyresampled($image_p, $image_carte, 0, 0, 0, 0, 603, 603, 603, 603);

//maintenant on donne une couleur a notre image (ici un fond noir)
$fond_perso=Imagecolorallocate($gare_carte, 250, 250, 250);

// on definit le font de l'image perso_carte comme transparent
imagecolortransparent($gare_carte,$fond_perso);

//Commme d'ab
require_once "../fonctions.php";

$mysqli = db_connexion();

// couleurs perso_carte
$noir 			= Imagecolorallocate($gare_carte, 0, 0, 0); // noir
$couleur_nord 	= Imagecolorallocate($gare_carte, 10, 10, 254); // bleu bien voyant
$couleur_sud 	= Imagecolorallocate($gare_carte, 254, 10, 10); // rouge bien voyant
$couleur_rail	= Imagecolorallocate($gare_carte, 200, 200, 200); // gris rails

// je vais chercher les rails dans ma table
// $sql = "SELECT x_carte, y_carte FROM carte 
		// WHERE (fond_carte='rail.gif' OR fond_carte='rail_1.gif' OR fond_carte='rail_2.gif' OR fond_carte='rail_3.gif' OR fond_carte='rail_4.gif' OR fond_carte='rail_5.gif' OR fond_carte='rail_7.gif' OR fond_carte='railP.gif')";
$sql =	"SELECT DISTINCT train_last_dep.x_last_dep as x_carte, train_last_dep.y_last_dep as y_carte
		FROM instance_batiment 
		inner join train_last_dep ON train_last_dep.id_train = instance_batiment.id_instanceBat
		WHERE instance_batiment.id_batiment = 12 and instance_batiment.camp_instance = 2 and train_last_dep.DeplacementDate > DATE_SUB(CURDATE(), INTERVAL 2 day)";
$res = $mysqli->query($sql);

while ($t = $res->fetch_assoc()){
	
	$x = $t["x_carte"];
	$y = $t["y_carte"];
	
	imagefilledrectangle ($gare_carte, (($x*3)-1), (((600-($y*3)))-1), (($x*3)+1), (((600-($y*3)))+1), $couleur_rail);
	
}

// je vais chercher les gares dans ma table
$sql = "SELECT x_instance, y_instance, nom_instance, taille_batiment, camp_instance FROM instance_batiment, batiment 
		WHERE batiment.id_batiment = instance_batiment.id_batiment 
		AND pv_instance>0
		AND instance_batiment.id_batiment='11' 
		and instance_batiment.camp_instance = 2 ";
$res = $mysqli->query($sql);

while ($t = $res->fetch_assoc()){
	
	$x 			= $t["x_instance"];
	$y 			= $t["y_instance"];
	$taille_bat = $t["taille_batiment"];
	$camp_bat	= $t["camp_instance"];
	$nom_bat	= "Gare ".$t["nom_instance"];
	
	$taille_text = strlen($nom_bat);
	
	if ($camp_bat == 1) {
		$color = $couleur_nord;
	}
	else {
		$color = $couleur_sud;
	}
	
	imagefilledrectangle ($gare_carte, (($x*3)-$taille_bat), (((600-($y*3)))-$taille_bat), (($x*3)+$taille_bat), (((600-($y*3)))+$taille_bat), $color);
	
	ImageString($gare_carte, 12, ($x*3)-($taille_text*3), ((600-($y*3))) + 3, $nom_bat, $noir);
}

// Cache les cases non découvertes
$sql = "SELECT x_carte, y_carte, fond_carte FROM carte WHERE vue_sud='0'";
$res = $mysqli->query($sql);

while($not_discovered = $res->fetch_assoc()){

	$x 			= $not_discovered["x_carte"];
	$y 			= $not_discovered["y_carte"];
	$fond		= $not_discovered["fond_carte"];

	imagefilledrectangle ($gare_carte, (($x*3)-1), (((600-($y*3)))-1), (($x*3)+1), (((600-($y*3)))+1), $noir);
}

imagepng($gare_carte, "carte/gare_sud.png");

imagecopymerge($image_p, $gare_carte, 0, 0, 0, 0, 603, 603, 100);

// on affiche l'image
imagepng($image_p, "carte/plan_gare_sud.png");


ImageDestroy ($gare_carte);
ImageDestroy ($image_carte);

if (isset($_GET['bat'])) {
	$bat = $_GET['bat'];
	
	header("Location:batiment.php?bat=$bat");
}
?>
