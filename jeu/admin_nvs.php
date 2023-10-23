<?php
session_start();
require_once("../fonctions.php");
require_once('../mvc/model/Administration.php');

$mysqli = db_connexion();

include ('../nb_online.php');

if(isset($_SESSION["id_perso"])){
	
	$id_perso = $_SESSION['id_perso'];
	
	// recupération config jeu
	$admin = admin_perso($mysqli, $id_perso);
	
	if($admin){
		
		$administration = new Administration();
		$maintenance_mode = $administration->getMaintenanceMode();
		
		if (isset($_GET['mode_jeu']) && $_GET['mode_jeu']=='close') {
			$sql = "UPDATE config_jeu SET valeur_config='0' WHERE code_config='disponible'";
			$mysqli->query($sql);
			
			header('location:../jeu/admin_nvs.php');
			die();
		}
		
		if (isset($_GET['mode_jeu']) && $_GET['mode_jeu']=='open') {
			$sql = "UPDATE config_jeu SET valeur_config='1' WHERE code_config='disponible'";
			$mysqli->query($sql);
			
			header('location:../jeu/admin_nvs.php');
			die();
		}
		
		if(isset($_POST['maintenance_msg'])){
			$msg = htmlspecialchars($_POST['maintenance_msg']);
			$result = $administration->updateMaintenanceMsg($msg);
			
			if($result){
				$_SESSION['flash'] = ['class'=>'success','message'=>'Message mis à jour'];
			}else{
				$_SESSION['flash'] = ['class'=>'danger','message'=>"Une erreur est survenue. Si le problème persiste, contacter l'administrateur"];
			}
			
			header('location:../jeu/admin_nvs.php');
			die();
		}

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