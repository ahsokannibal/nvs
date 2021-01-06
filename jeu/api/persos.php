<?php
require_once("../../fonctions.php");

$mysqli = db_connexion();

if(isset($_GET['term'])){
	$search = $mysqli->real_escape_string($_GET['term']);

	$query = "SELECT id_perso, nom_perso FROM perso WHERE nom_perso like'%".$search."%'";
	$result = $mysqli->query($query);

	$response = array();

	while($t = $result->fetch_assoc()){
		
		$id_perso = $t['id_perso'];
		$nom_perso = $t['nom_perso'];
		
		$response[] = array("value"=>$id_perso,"label"=>$nom_perso);
	}
	
	echo json_encode($response);
}

exit;
?>