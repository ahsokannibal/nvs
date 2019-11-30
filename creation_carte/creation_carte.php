<?php
require_once("../fonctions.php");

$mysqli = db_connexion();

$X_MAX = 200;
$Y_MAX = 200;

// On vide la table carte
$sql = "DELETE FROM carte";
exec_requete($sql);

// On insère que des plaines
for ($x = 0; $x <= $X_MAX; $x++)
{
	for ($y = 0; $y <= $Y_MAX; $y++)
	{
		$sql2 = "INSERT INTO `carte` VALUES ($x, $y, 0, '1.gif', NULL, NULL)";
		$mysqli->query($sql2);
	}
}

// creation d'un pnj
$sql = "INSERT INTO `instance_pnj` VALUES ('200000','1','8','2','0','0','0','0')";
$mysqli->query($sql);

// insertion du pnj sur la carte
$sql = "UPDATE `carte` SET occupee_carte='1', idPerso_carte='200000', image_carte='loup.gif' WHERE x_carte='0' AND y_carte='0'";
$mysqli->query($sql);

echo "c'est bon : la carte est créée";
?>