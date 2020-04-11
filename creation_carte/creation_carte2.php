<?php
require_once("../fonctions.php");

$mysqli = db_connexion();

$X_MAX = 250;
$Y_MAX = 200;

$sql = "DELETE FROM carte2";
$mysqli->query($sql);

for ($x = 0; $x <= $X_MAX; $x++)
{
	for ($y = 0; $y <= $Y_MAX; $y++)
	{
		$sql2 = "INSERT INTO `carte2` VALUES ($x, $y, '0', '1.gif', NULL, NULL)";
		$mysqli->query($sql2);
	}
}

// Coordonnées carte
$sql = "UPDATE carte2 SET coordonnees = CONCAT (x_carte, ';', y_carte)";
$mysqli->query($sql);

echo "c'est bon : la carte est créée";
?>