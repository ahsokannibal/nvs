<?php
session_start();
require_once("../fonctions.php");

$mysqli = db_connexion();

// FORTINS
$sql = "SELECT id_instanceBat, camp_instance, x_instance, y_instance FROM instance_batiment WHERE id_batiment='8'";
$res = $mysqli->query($sql);

while ($t = $res->fetch_assoc()) {
	
	$id_i_bat = $t['id_instanceBat'];
	$camp_bat = $t['camp_instance'];
	
	$x_bat = $t['x_instance'];
	$y_bat = $t['y_instance'];
	
	// Canons Gauche
	$sql = "UPDATE carte SET image_carte='CanonG.jpg' WHERE (x_carte=$x_bat - 1 AND y_carte=$y_bat - 1) OR (x_carte=$x_bat - 1 AND y_carte=$y_bat + 1)";
	$mysqli->query($sql);
	
	// Canons Droit
	$sql = "UPDATE carte SET image_carte='CanonD.jpg' WHERE (x_carte=$x_bat + 1 AND y_carte=$y_bat - 1) OR (x_carte=$x_bat + 1 AND y_carte=$y_bat + 1)";
	$mysqli->query($sql);
	
	$sql = "INSERT INTO instance_batiment_canon (id_instance_bat, x_canon, y_canon, camp_canon) VALUES ('$id_i_bat', $x_bat - 1, $y_bat - 1, $camp_bat)";
	$mysqli->query($sql);
	$sql = "INSERT INTO instance_batiment_canon (id_instance_bat, x_canon, y_canon, camp_canon) VALUES ('$id_i_bat', $x_bat - 1, $y_bat + 1, $camp_bat)";
	$mysqli->query($sql);
	$sql = "INSERT INTO instance_batiment_canon (id_instance_bat, x_canon, y_canon, camp_canon) VALUES ('$id_i_bat', $x_bat + 1, $y_bat - 1, $camp_bat)";
	$mysqli->query($sql);
	$sql = "INSERT INTO instance_batiment_canon (id_instance_bat, x_canon, y_canon, camp_canon) VALUES ('$id_i_bat', $x_bat + 1, $y_bat + 1, $camp_bat)";
	$mysqli->query($sql);
}

// FORTS
$sql = "SELECT id_instanceBat, camp_instance, x_instance, y_instance FROM instance_batiment WHERE id_batiment='9'";
$res = $mysqli->query($sql);

while ($t = $res->fetch_assoc()) {
	
	$id_i_bat = $t['id_instanceBat'];
	$camp_bat = $t['camp_instance'];
	
	$x_bat = $t['x_instance'];
	$y_bat = $t['y_instance'];
	
	// Canons Gauche
	$sql = "UPDATE carte SET image_carte='CanonG.jpg' WHERE (x_carte=$x_bat - 2 AND y_carte=$y_bat + 2) OR (x_carte=$x_bat - 2 AND y_carte=$y_bat) OR (x_carte=$x_bat - 2 AND y_carte=$y_bat - 2)";
	$mysqli->query($sql);
	
	// Canons Droit
	$sql = "UPDATE carte SET image_carte='CanonD.jpg' WHERE (x_carte=$x_bat + 2 AND y_carte=$y_bat + 2) OR (x_carte=$x_bat + 2 AND y_carte=$y_bat) OR (x_carte=$x_bat + 2 AND y_carte=$y_bat - 2)";
	$mysqli->query($sql);
	
	$sql = "INSERT INTO instance_batiment_canon (id_instance_bat, x_canon, y_canon, camp_canon) VALUES ('$id_i_bat', $x_bat - 2, $y_bat + 2, $camp_bat)";
	$mysqli->query($sql);
	$sql = "INSERT INTO instance_batiment_canon (id_instance_bat, x_canon, y_canon, camp_canon) VALUES ('$id_i_bat', $x_bat - 2, $y_bat, $camp_bat)";
	$mysqli->query($sql);
	$sql = "INSERT INTO instance_batiment_canon (id_instance_bat, x_canon, y_canon, camp_canon) VALUES ('$id_i_bat', $x_bat - 2, $y_bat - 2, $camp_bat)";
	$mysqli->query($sql);
	$sql = "INSERT INTO instance_batiment_canon (id_instance_bat, x_canon, y_canon, camp_canon) VALUES ('$id_i_bat', $x_bat + 2, $y_bat + 2, $camp_bat)";
	$mysqli->query($sql);
	$sql = "INSERT INTO instance_batiment_canon (id_instance_bat, x_canon, y_canon, camp_canon) VALUES ('$id_i_bat', $x_bat + 2, $y_bat, $camp_bat)";
	$mysqli->query($sql);
	$sql = "INSERT INTO instance_batiment_canon (id_instance_bat, x_canon, y_canon, camp_canon) VALUES ('$id_i_bat', $x_bat + 2, $y_bat - 2, $camp_bat)";
	$mysqli->query($sql);
}

?>