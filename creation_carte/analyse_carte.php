<?php
require_once("../fonctions.php");
require_once("f_analyse.php");

$mysqli = db_connexion();

$taille_x = 200;
$taille_y = 200;

$image_carte = imagecreatefrompng("carte410.png");

$sql = "DELETE FROM carte2";
$mysqli->query($sql);

for ($x = 0; $x < $taille_x; $x++) {
	for ($y = 0; $y < $taille_y; $y++) {
		
		$pixelrgb = imagecolorat($image_carte, $x, $y);
		
		$cols = imagecolorsforindex($image_carte, $pixelrgb);
		$r = dechex($cols['red']);
		$g = dechex($cols['green']);
		$b = dechex($cols['blue']);
		
		echo "RGB en ".$x."/".$y." : ".$r." ".$g." ".$b."<br />";
		
		if (est_couleur_colline($r, $g, $b)) {
			$image_fond = '2.gif';
		}
		else if (est_couleur_montagne($r, $g, $b)) {
			$image_fond = '3.gif';
		}
		else if (est_couleur_desert($r, $g, $b)) {
			$image_fond = '4.gif';
		}
		else if (est_couleur_marecage($r, $g, $b)) {
			$image_fond = '6.gif';
		}
		else if (est_couleur_foret($r, $g, $b)) {
			$image_fond = '7.gif';
		}
		else if (est_couleur_eau($r, $g, $b)) {
			$image_fond = '8.gif';
		}
		else if (est_couleur_eau_profonde($r, $g, $b)) {
			$image_fond = '9.gif';
		}
		else {
			$image_fond = '1.gif';
		}
		
		$sql = "INSERT INTO carte2 VALUES ($x, $y, '0', '$image_fond', NULL, NULL)";
		$mysqli->query($sql);
	}
}

?>