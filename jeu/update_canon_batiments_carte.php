<?php
session_start();
require_once("../fonctions.php");

$mysqli = db_connexion();

// FORTINS
$sql = "SELECT x_instance, y_instance FROM instance_batiment WHERE id_batiment='8'";
$res = $mysqli->query($sql);

while ($t = $res->fetch_assoc()) {
	
	$x_bat = $t['x_instance'];
	$y_bat = $t['y_instance'];
	
	// Canons Gauche
	$sql = "UPDATE carte SET image_carte='CanonG.jpg' WHERE (x_carte=$x_bat - 1 AND y_carte=$y_bat - 1) OR (x_carte=$x_bat - 1 AND y_carte=$y_bat + 1)";
	$mysqli->query($sql);
	
	// Canons Droit
	$sql = "UPDATE carte SET image_carte='CanonD.jpg' WHERE (x_carte=$x_bat + 1 AND y_carte=$y_bat - 1) OR (x_carte=$x_bat + 1 AND y_carte=$y_bat + 1)";
	$mysqli->query($sql);
}

// FORTS
$sql = "SELECT x_instance, y_instance FROM instance_batiment WHERE id_batiment='9'";
$res = $mysqli->query($sql);

while ($t = $res->fetch_assoc()) {
	
	$x_bat = $t['x_instance'];
	$y_bat = $t['y_instance'];
	
	// Canons Gauche
	$sql = "UPDATE carte SET image_carte='CanonG.jpg' WHERE (x_carte=$x_bat - 2 AND y_carte=$y_bat + 2) OR (x_carte=$x_bat - 2 AND y_carte=$y_bat) OR (x_carte=$x_bat - 2 AND y_carte=$y_bat - 2)";
	$mysqli->query($sql);
	
	// Canons Droit
	$sql = "UPDATE carte SET image_carte='CanonD.jpg' WHERE (x_carte=$x_bat + 2 AND y_carte=$y_bat + 2) OR (x_carte=$x_bat + 2 AND y_carte=$y_bat) OR (x_carte=$x_bat + 2 AND y_carte=$y_bat - 2)";
	$mysqli->query($sql);
}

?>