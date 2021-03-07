<?php
session_start();
require_once("../fonctions.php");

$mysqli = db_connexion();

include ('../nb_online.php');
include ('../forum/config.php');

if (@$_SESSION["id_perso"]) {
	
	//recuperation des varaibles de sessions
	$id = $_SESSION["id_perso"];
	
	$sql = "SELECT pv_perso, chef FROM perso WHERE id_perso='$id'";
	$res = $mysqli->query($sql);
	$tpv = $res->fetch_assoc();
	
	$testpv = $tpv['pv_perso'];
	$chef	= $tpv['chef'];
	
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

if ($chef) {

	if (isset($_POST['nouveau_nom_perso'])) {
					
		$nouveau_nom_perso = addslashes($_POST['nouveau_nom_perso']);
		
		if (!filtre($nouveau_nom_perso,1,20) || ctype_digit($nouveau_nom_perso) || strpos($nouveau_nom_perso,'--') !== false) {
			echo "<center><font color='red'>Le Pseudo est incorrect! Veuillez en choisir un autre (taille entre 1 et 20, pas de quote, pas que des chiffres, pas la chaine --, etc..)</font></center>";
		}
		else {
			// Est ce que le nom de ce persoe est déjà pris ?
			$sql = "SELECT * FROM perso WHERE nom_perso='$nouveau_nom_perso'";
			$res = $mysqli->query($sql);
			$verif = $res->num_rows;
			
			if ($verif == 0) {
				
				// Existe t-il déjà une demande de changement de nom ?
				$sql = "SELECT * FROM perso_demande_anim WHERE id_perso='$id' AND type_demande='1'";
				$res = $mysqli->query($sql);
				$verif_demande = $res->num_rows;
				
				if ($verif_demande == 0) {
				
					$sql = "INSERT INTO perso_demande_anim (id_perso, type_demande, info_demande) VALUES ('$id', '1', '$nouveau_nom_perso')";
					$mysqli->query($sql);
					
					echo "<center><font color='blue'>Demande envoyée avec succée</font></center>";
				}
				else {
					echo "<center><font color='red'>Une demande de changement de nom pour ce perso est déjà en attente</font></center>";
				}
			}
			else {
				echo "<center><font color='red'>Un perso du nom " . $_POST['nouveau_nom_perso'] . " existe déjà, veuillez choisir un autre nom</font></center>";
			}
		}
	}
				
	// Récupération infos du perso
	$sql = "SELECT nom_perso FROM perso WHERE id_perso='$id'";
	$res = $mysqli->query($sql);
	$t = $res->fetch_assoc();

	$nom_perso = $t['nom_perso'];

	echo "	<div class='row'>";
	echo "		<div class='col-12'>";
	echo "			<div align='center'>";
	echo "				<h3><center>Perso ".$nom_perso." - Demande de changement de nom à l'animation</h3>";
	echo "			</div>";
	echo "		</div>";
	echo "	</div>";
	
	echo "<hr>";
	
	echo "	<div class='row'>";
	echo "		<div class='col-12'>";
	echo "			<div align='center'>";
	
	// Existe t-il déjà une demande de changement de nom ?
	$sql = "SELECT * FROM perso_demande_anim WHERE id_perso='$id' AND type_demande='1'";
	$res = $mysqli->query($sql);
	$verif_demande = $res->num_rows;

	if ($verif_demande == 0) {

		echo "<form method='post' action='nom_perso_change.php' name='changer_nom_perso'>";
		echo "	<div class='form-group'>";
		echo "		<label for='inputChangeNomPerso'>Nouveau nom : </label>";
		echo "		<input type='text' value='' id='inputChangeNomPerso' name='nouveau_nom_perso' maxlength='40' />";
		echo "		<button type='submit' class='btn btn-primary'>Envoyer</button>";
		echo "	</div>";
		echo "</form>";
	}
	else {
		echo "<center><font color='red'>Une demande de changement de nom pour ce perso a déjà été effectuée, veuillez patienter</font></center>";
	}
	
	echo "			</div>";
	echo "		</div>";
	echo "	</div>";
}
else {
	echo "<center><font color='red'>La demande changement de nom ne peut se faire que pour votre chef</font></center>";
}

echo "<br />";

echo "<center>";
echo "	<a class='btn btn-primary' href='profil.php'>retour a la page de profil</a>";
echo "</center>";

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
