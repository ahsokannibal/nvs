<?php
require_once("../fonctions.php");
require_once("f_analyse.php");

$mysqli = db_connexion();

$taille_x = 201;
$taille_y = 201;

$image_carte = imagecreatefrompng("carte413.png");

$sql = "DELETE FROM carte2";
$mysqli->query($sql);

for ($x_pixel = 0; $x_pixel < $taille_x; $x_pixel++) {
	for ($y_pixel = 0; $y_pixel < $taille_y; $y_pixel++) {
		
		$pixelrgb = imagecolorat($image_carte, $x_pixel, $y_pixel);
		
		$cols = imagecolorsforindex($image_carte, $pixelrgb);
		$r = dechex($cols['red']);
		$g = dechex($cols['green']);
		$b = dechex($cols['blue']);
		
		//echo "RGB en ".$x_pixel."/".$y_pixel." : ".$r." ".$g." ".$b."<br />";
		
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
		
		$x = $x_pixel;
		$y = 200 - $y_pixel;
		
		$sql = "INSERT INTO carte2 (id_carte, x_carte, y_carte, occupee_carte, fond_carte, idPerso_carte, image_carte) VALUES (0, $x, $y, '0', '$image_fond', NULL, NULL)";
		$mysqli->query($sql);
	}
}

$sql = "UPDATE carte2 SET coordonnees = CONCAT (x_carte, ';', y_carte)";
$mysqli->query($sql);

echo "Fin creation carte";

?>
