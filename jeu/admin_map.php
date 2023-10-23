<?php
session_start();
require_once("../fonctions.php");
require_once("../mvc/controller/mapController.php");

$mysqli = db_connexion();

if(isset($_SESSION["id_perso"])){
	
	$id_perso = $_SESSION['id_perso'];
	
	// recupération config jeu
	$admin = admin_perso($mysqli, $id_perso);
	
	if($admin){
		
		$mapController = new MapController();
		
		$action = (empty($_GET['action'])) ? '' : $_GET['action'];
		switch ($action) {
			case "create":
				$mapController->create();
			break;
			case "store":
				$mapController->store();
			break;
			case "edit":
				if(!isset($_GET['id'])){
					$mapController->index();
				}else{
				$mapController->edit($_GET['id']);
				}
			break;
			case "show":
			break;
			case "delete":
				if(!isset($_POST['id'])){
					$mapController->index();
				}else{
				$mapController->destroy($_POST['id']);
				}
			break;
			default:
				$mapController->index();
		}

	}
	else {
		// logout
		$_SESSION = array(); // On écrase le tableau de session
		session_destroy(); // On détruit la session
		
		header("Location:../index.php");
	}
}
else{
	echo "<font color=red>Vous ne pouvez pas accéder à cette page, veuillez vous connecter. <a href='index.php'>Accueil</a></font>";
}
?>