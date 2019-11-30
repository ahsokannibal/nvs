<?php
require_once("../fonctions.php");
db_connexion();
$X_MAX = 200;
$Y_MAX = 200;

$sql = "DELETE FROM carte3";
exec_requete($sql);
for ($x = 0; $x <= $X_MAX; $x++)
{
	for ($y = 0; $y <= $Y_MAX; $y++)
	{
		$sql2 = "INSERT INTO `carte3` VALUES ($x, $y, 0, '1.gif', NULL, NULL)";
		exec_requete($sql2);
	}
}

echo "c'est bon : la carte est crייe";
?>