<?php 
session_start(); 
require_once("../../fonctions.php");	

$mysqli = db_connexion(); 

$id_perso = $_SESSION["id_perso"];

// recuperation du nom du perso
$sql = "SELECT nom_perso FROM perso WHERE id_perso='$id_perso'";
$res = $mysqli->query($sql);
$t = $res->fetch_assoc();

$pseudo = $t["nom_perso"];

if(isset($_GET["envoi"]) && $_GET["envoi"] == "ok"){
	echo "<div class=\"info\">Message envoyé</div>";
}

if (isset($_POST["submit"])) {
	
	$action = $_POST["submit"];
	
	//si on a clicker sur "effacer"
	if ($action == "Effacer") { 
	
		if(isset($_POST["id_message"])){
			
			$sql = "UPDATE message_perso SET supprime_message='1' WHERE id_perso='$id_perso' AND (";
			$sql .= "id_message='" . $_POST["id_message"][0] . "'";
			
			//à revoir --> je parcours le tableau mais le 1° indice est déjà rajouter à la requete (cf ligne du dessus)
			foreach ($_POST["id_message"] as $id) {                 
				$sql .= "OR id_message='" . $id . "'";
			}
			
			$sql .=")";
			$mysqli->query($sql);
		}
		
	header("Location:../messagerie.php");
	
	}

	//si on a clicker sur "archiver"
	if ($action == "Archiver") {
		
		if(isset($_POST["id_message"])){
			
			$sql = "UPDATE message_perso SET id_dossier='2' WHERE id_perso='$id_perso' AND (";
			$sql .= "id_message='" . $_POST["id_message"][0] . "'";
			
			foreach ($_POST["id_message"] as $id) {  
				$sql .= "OR id_message='" . $id . "'";
			}
			
			$sql .=")";
			$mysqli->query($sql);
		}
		
		header("Location:../messagerie.php");
	}
}
?>
