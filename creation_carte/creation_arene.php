<?php
require_once("../fonctions.php");
db_connexion();
$X_MAX = 20;
$Y_MAX = 20;

$sql = "DELETE FROM arene";
exec_requete($sql);
for ($x = 0; $x <= $X_MAX; $x++)
{
	for ($y = 0; $y <= $Y_MAX; $y++)
	{
		$sql2 = "INSERT INTO `arene` VALUES ($x, $y, 0, '1.gif', NULL, NULL)";
		exec_requete($sql2);
	}
}
echo "c'est bon : l'arene est crייe";
?>