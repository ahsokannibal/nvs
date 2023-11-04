<?php
session_start();
require_once("../fonctions.php");
require_once("../mvc/controller/itemController.php");

$mysqli = db_connexion();

if(isset($_SESSION["id_perso"])){
	
	$id_perso = $_SESSION['id_perso'];
	
	// recupération config jeu
	$admin = admin_perso($mysqli, $id_perso);
	
	if($admin){
		
		$itemController = new itemController();
		
		$action = (empty($_GET['action'])) ? '' : $_GET['action'];
		switch ($action) {
			case "create":
				$itemController->create();
			break;
			case "store":
				$itemController->store();
			break;
			case "edit":
				if(!isset($_GET['id'])){
					$itemController->index();
				}else{
					$itemController->edit($_GET['id']);
				}
			break;
			case "update":
				if(!isset($_POST['id']) AND !isset($_GET['id'])){
					$itemController->index();
				}else{
					$itemController->update($_GET['id']);
				}
				
			break;
			case "show":
			break;
			case "delete":
				if(!isset($_POST['id'])){
					$itemController->index();
				}else{
					$itemController->destroy($_POST['id']);
				}
			break;
			default:
				$itemController->index();
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