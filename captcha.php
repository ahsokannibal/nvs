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
$tab_mot			= str_split($mot);
$largeur 			= 180;
$hauteur 			= 24;
$milieuHauteur 		= ($hauteur / 2) - 8;
$_SESSION["code"]	= $mot;

$image 		= imagecreatetruecolor($largeur, $hauteur);
$background = array(
		imagecolorallocate($image, 0xD7, 0x19, 0x1C),
		imagecolorallocate($image, 0xFD, 0xAE, 0x61),
		imagecolorallocate($image, 0xFF, 0xFF, 0xBF),
		imagecolorallocate($image, 0x2C, 0x7B, 0xB6),
		imagecolorallocate($image, 0xAB, 0xD9, 0xE9));
$blanc 		= imagecolorallocate($image, 255, 255, 255); 
$noir 		= imagecolorallocate($image, 0, 0, 0);
$red		= imagecolorallocate($image, 245, 73, 73);

$position_debut_mot = mt_rand(4,15);

imagefill($image, 0, 0, $background[array_rand($background)]);

for ($i = 0; $i < strlen($mot); $i++) {
	$taille_police = mt_rand(3,5);
	$variation_hauteur = mt_rand(-4,4);
	imagestring($image, $taille_police, $position_debut_mot + 12*$i , $milieuHauteur + $variation_hauteur, $tab_mot[$i], $noir);
}

//imagestring($image, 6, $position_debut_mot , $milieuHauteur, $mot, $noir);
imagerectangle($image, 1, 1, $largeur - 1, $hauteur - 1, $noir);
imageconvolution($image, $matrix_blur, 16, 0);

$ecart_line = mt_rand(4,7);
$fin_line	= mt_rand(-10,+10);

for($x = 0; $x < $largeur + $ecart_line; $x+=$ecart_line)
{
	imageline($image, $x, 2, $x + $fin_line, $hauteur, $noir);
}

header("Cache-Control: no-cache, must-revalidate");
header('Content-type: image/png');

imagepng($image);
imagedestroy($image);

/*
session_start();

header('Content-Type: image/png');
$largeur 	= 80;
$hauteur	= 25;
$lignes		= 10;

$caracteres="ABCDEF123456789";

$image = imagecreatetruecolor($largeur, $hauteur);

imagefilledrectangle($image, 0, 0, $largeur, $hauteur, imagecolorallocate($image, 255, 255, 255));

function hexargb($hex) {
    return array("r"=>hexdec(substr($hex,0,2)),"g"=>hexdec(substr($hex,2,2)),"b"=>hexdec(substr($hex,4,2)));
}

for($i=0;$i<=$lignes;$i++){
    $rgb=hexargb(substr(str_shuffle("ABCDEF0123456789"),0,6));
    imageline($image,rand(1,$largeur-25),rand(1,$hauteur),rand(1,$largeur+25),rand(1,$hauteur),imagecolorallocate($image, $rgb['r'], $rgb['g'], $rgb['b']));
}

$code1=substr(str_shuffle($caracteres),0,4);
$_SESSION['code']=$code1;
$code="";

for($i=0;$i<=strlen($code1);$i++){
    $code .=substr($code1,$i,1)." ";
}

imagestring($image, 5, 10, 5, $code, imagecolorallocate($image, 0, 0, 0));
imagepng($image);
imagedestroy($image);
*/

?>