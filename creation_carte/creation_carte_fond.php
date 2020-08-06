<?php
header("Content-type: image/png");

require_once "../fonctions.php";

$mysqli = db_connexion();

$sql = "SELECT MAX(x_carte) as x_max, MAX(y_carte) as y_max FROM carte";
$res = $mysqli->query($sql);
$t = $res->fetch_assoc();

$X_MAX 	= $t['x_max'];
$Y_MAX  = $t['y_max'];

//ensuite on defini la taille de l'image
$image_carte = imagecreate(($X_MAX + 1)*3, ($Y_MAX + 1)*3) or die ("Cannot Initialize new GD image stream");

//maintenant on donne une couleur a notre image (ici un fond noir)
$font_carte = Imagecolorallocate($image_carte, 250, 250, 250);

// allocation des diferentes couleurs dont on aura besoin
$nean 		= Imagecolorallocate($image_carte, 0, 0, 0); // noir
$plaine 	= Imagecolorallocate($image_carte, 129, 156, 84); // vert clair
$colline 	= Imagecolorallocate($image_carte, 96, 110, 70); // 
$montagne 	= Imagecolorallocate($image_carte, 134, 118, 89); // marron foncé
$desert 	= Imagecolorallocate($image_carte, 215, 197, 101); // jaune foncé (penchant vers le marron)
$neige 		= Imagecolorallocate($image_carte, 232, 248, 248); // blanc
$marecage 	= Imagecolorallocate($image_carte, 169, 177, 166); // gris
$foret 		= Imagecolorallocate($image_carte, 60, 86, 33); // vert foncé
$eau 		= Imagecolorallocate($image_carte, 92, 191, 207); // bleu clair
$eau_p 		= Imagecolorallocate($image_carte, 39, 141, 227); // bleu foncé

// je vais chercher les terrains dans ma table
$sql = "SELECT x_carte, y_carte, fond_carte, occupee_carte, image_carte FROM carte";
$res = $mysqli->query($sql);

while ($t = $res->fetch_assoc()){
	
	$im = $t["image_carte"];
	$x 	= $t["x_carte"];
	$y 	= $t["y_carte"];
	
	$fond_carte = explode(".",$t["fond_carte"]);
	$fond = $fond_carte[0];

	switch($fond){
		case "1" :
			$color = $plaine;
			break;
		case "2" :
			$color = $colline;
			break;
		case "3" :
			$color = $montagne;
			break;
		case "4" :
			$color = $desert;
			break;
		case "5" :
			$color = $neige;
			break;
		case "6" :
			$color = $marecage;
			break;
		case "7" :
			$color = $foret;
			break;
		case "8" :
			$color = $eau;
			break;
		case "9" :
			$color = $eau_p;
			break;
		default :
			$color = $nean;
			break;
	}
		
	imagefilledrectangle ($image_carte, ((3*$x)-1), (((($Y_MAX * 3)-(3*$y)))-1), ((3*$x)+1), (((($Y_MAX * 3)-(3*$y)))+1), $color);
}

// on affiche l'image
imagepng($image_carte, "../jeu/carte/carte.png");
ImageDestroy ($image_carte);
?>
