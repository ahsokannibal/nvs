<?php
session_start();

function motListe()
{
	$liste = file('dico.text');
	return trim($liste[array_rand($liste)]);
}

// Flou Gaussien
$matrix_blur = array(
		array(1,2,1),
		array(2,4,2),
		array(1,2,1));

$mot				= motListe();
$largeur 			= strlen($mot) * 12;
$hauteur 			= 24;
$milieuHauteur 		= ($hauteur / 2) - 8;
$_SESSION["code"]	= $mot;

$image 		= imagecreatetruecolor($largeur, $hauteur);
$background = array(
		imagecolorallocate($image, 0x99, 0x00, 0x66),
		imagecolorallocate($image, 0xCC, 0x00, 0x00),
		imagecolorallocate($image, 0x00, 0x00, 0xCC),
		imagecolorallocate($image, 0x00, 0x00, 0xCC),
		imagecolorallocate($image, 0xBB, 0x88, 0x77));
$blanc 		= imagecolorallocate($image, 255, 255, 255); 
$noir 		= imagecolorallocate($image, 0, 0, 0);
$red		= imagecolorallocate($image, 245, 73, 73);

imagefill($image, 0, 0, $background[array_rand($background)]);
imagestring($image, 6, 6 , $milieuHauteur, $mot, $noir);
imagerectangle($image, 1, 1, $largeur - 1, $hauteur - 1, $noir);
imageconvolution($image, $matrix_blur,16,0);

for($x = 5; $x < $largeur; $x+=6)
{
	imageline($image, $x,2,$x-5,$hauteur,$noir);
}

header("Cache-Control: no-cache, must-revalidate");
header('Content-type: image/png');

imagepng($image);
imagedestroy($image);


?>