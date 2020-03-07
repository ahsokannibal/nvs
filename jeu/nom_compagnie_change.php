<?php
session_start();
require_once("../fonctions.php");

$mysqli = db_connexion();

include ('../nb_online.php');
include ('../forum/config.php');

if (@$_SESSION["id_perso"]) {
	
	//recuperation des varaibles de sessions
	$id = $_SESSION["id_perso"];
	
	$sql = "SELECT pv_perso FROM perso WHERE id_perso='$id'";
	$res = $mysqli->query($sql);
	$tpv = $res->fetch_assoc();
	
	$testpv = $tpv['pv_perso'];
	
	if ($testpv <= 0) {
		echo "<font color=red>Vous êtes mort...</font>";
	}
	else {
		//$erreur = "<div class=\"erreur\">";

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<title>Nord VS Sud</title>
		
		<!-- Required meta tags -->
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		
		<!-- Bootstrap CSS -->
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
	</head>
	<body>
		<div class="container-fluid">
<?php
if(isset($_GET["id_compagnie"])) {
	
	$id_compagnie = $_GET["id_compagnie"];
	
	$verif1 = preg_match("#^[0-9]+$#i",$id_compagnie);
	
	if($verif1){
	
		// verification que le perso est bien le chef de la compagnie (anti-triche)
		$sql = "SELECT poste_compagnie FROM perso_in_compagnie WHERE id_perso=$id AND id_compagnie=$id_compagnie";
		$res = $mysqli->query($sql);
		$ch = $res->fetch_assoc();
		
		$ok_chef = $ch["poste_compagnie"];
		
		if($ok_chef == 1) {
			
			if (isset($_POST['nouveau_nom_compagnie'])) {
				
				$nouveau_nom_compagnie = addslashes($_POST['nouveau_nom_compagnie']);
				
				// Est ce que le nom de cette compagnie est déjà pris ?
				$sql = "SELECT * FROM compagnies WHERE nom_compagnie='$nouveau_nom_compagnie'";
				$res = $mysqli->query($sql);
				$verif = $res->num_rows;
				
				if ($verif == 0) {
					
					// Existe t-il déjà une demande de changement de nom ?
					$sql = "SELECT * FROM compagnie_demande_anim WHERE id_compagnie='$id_compagnie' AND type_demande='1'";
					$res = $mysqli->query($sql);
					$verif_demande = $res->num_rows;
					
					if ($verif_demande == 0) {
					
						$sql = "INSERT INTO compagnie_demande_anim (id_compagnie, type_demande, info_demande) VALUES ('$id_compagnie', '1', '$nouveau_nom_compagnie')";
						$mysqli->query($sql);
						
						echo "<center><font color='blue'>Demande envoyée avec succée</font></center>";
					}
					else {
						echo "<center><font color='red'>Une demande de changement de nom pour cette compagnie est déjà en attente</font></center>";
					}
				}
				else {
					echo "<center><font color='red'>Une compagnie du nom " . $_POST['nouveau_nom_compagnie'] . " existe déjà, veuillez choisir un autre nom</font></center>";
				}
			}
			
			// Récupération infos de la compagnie
			$sql = "SELECT nom_compagnie FROM compagnies WHERE id_compagnie='$id_compagnie'";
			$res = $mysqli->query($sql);
			$t = $res->fetch_assoc();
			
			$nom_compagnie = $t['nom_compagnie'];
			
			echo "<h3><center>Compagnie ".$nom_compagnie." - Demande de changement de nom à l'animation</h3>";
			
			// Existe t-il déjà une demande de changement de nom ?
			$sql = "SELECT * FROM compagnie_demande_anim WHERE id_compagnie='$id_compagnie' AND type_demande='1'";
			$res = $mysqli->query($sql);
			$verif_demande = $res->num_rows;
			
			if ($verif_demande == 0) {
			
				echo "<form method='post' action='nom_compagnie_change.php?id_compagnie=$id_compagnie' name='changer_nom_compagnie'>";
				echo "	<div class='form-group'>";
				echo "		<label for='inputChangeNomCompagnie'>Nouveau nom : </label>";
				echo "		<input type='text' value='' id='inputChangeNomCompagnie' name='nouveau_nom_compagnie' maxlength='40' />";
				echo "		<button type='submit' class='btn btn-primary'>Envoyer</button>";
				echo "	</div>";
				echo "</form>";
			}
			else {
				echo "<center><font color='red'>Une demande de changement de nom pour cette compagnie a déjà été effectuée, veuillez patienter</font></center>";
			}
			
			echo "<br /><center><a class='btn btn-primary' href='compagnie.php'>retour a la page compagnie</a></center>";
			
		}
		else {
			echo "<font color = red>Vous n'avez pas le droit d'acceder à cette page !</font>";
			
			$text_triche = "Tentative accés page de demande de changement de com de la compagnie [$id_compagnie] sans y avoir les droits";
			
			$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id', '$text_triche')";
			$mysqli->query($sql);
		}
	}
	else {
		echo "<center>La compagnie demandé n'existe pas</center>";
		
		$param_test 	= addslashes($id_compagnie);
		$text_triche 	= "Test parametre sur page admin compagnie, parametre id_compagnie invalide tenté : $param_test";
			
		$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id', '$text_triche')";
		$mysqli->query($sql);
	}
}


?>
		</div>
		
		<!-- Optional JavaScript -->
		<!-- jQuery first, then Popper.js, then Bootstrap JS -->
		<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
		<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
	</body>
</html>
<?php
	}
}
else{
	echo "<font color=red>Vous ne pouvez pas acceder a cette page, veuillez vous logguer.</font>";
}?>
