<?php
session_start();
require_once("../fonctions.php");

$mysqli = db_connexion();

include ('../nb_online.php');

if(isset($_SESSION["id_perso"])){
	
	$id_perso = $_SESSION['id_perso'];
	
	// recupération config jeu
	$admin = admin_perso($mysqli, $id_perso);
	
	if($admin){
		
		if (isset($_GET['mode_jeu']) && $_GET['mode_jeu']=='close') {
			$sql = "UPDATE config_jeu SET valeur_config='0' WHERE code_config='disponible'";
			$mysqli->query($sql);
		}
		
		if (isset($_GET['mode_jeu']) && $_GET['mode_jeu']=='open') {
			$sql = "UPDATE config_jeu SET valeur_config='1' WHERE code_config='disponible'";
			$mysqli->query($sql);
		}
		
		$dispo = config_dispo_jeu($mysqli);

	require_once('../mvc/view/admin/index.php');
	}
	else {
		// logout
		$_SESSION = array(); // On écrase le tableau de session
		session_destroy(); // On détruit la session
		
		header("Location:../index.php");
	}
}
else{
	echo "<font color=red>Vous ne pouvez pas accéder à cette page, veuillez vous loguer. <a href='index.php'>Accueil</a></font>";
}
?>