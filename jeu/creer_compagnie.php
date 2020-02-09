<?php
session_start();
require_once("../fonctions.php");

$mysqli = db_connexion();

include ('../nb_online.php');

// recupération config jeu
$dispo = config_dispo_jeu($mysqli);
$admin = admin_perso($mysqli, $_SESSION["id_perso"]);

if($dispo || $admin){
	
	if (isset($_SESSION["id_perso"])) {
		
		//recuperation des variables de sessions
		$id = $_SESSION["id_perso"];
		
		// récupération du camp du perso
		$sql = "SELECT clan FROM perso WHERE id_perso='$id'";
		$res = $mysqli->query($sql);
		$t_p = $res->fetch_assoc();
		
		$camp = $t_p['clan'];
		
		if (isset($_POST["enregistrer"])) {
			
			if (isset($_POST["nomCompagnie"]) && trim($_POST["nomCompagnie"]) != "") {
				
				if (isset($_POST["descCompagnie"]) && trim($_POST["descCompagnie"]) != "") {
					
					$nom_compagnie 	= addslashes($_POST["nomCompagnie"]);
					$desc_compagnie	= addslashes($_POST["descCompagnie"]);
					
					$sql = "INSERT INTO em_creer_compagnie (id_perso, nom_compagnie, description_compagnie, camp) VALUES ('$id', '$nom_compagnie', '$desc_compagnie', '$camp')";
					$mysqli->query($sql);
					
					echo "<center><font color='blue'>Votre compagnie " . $_POST["nomCompagnie"] . " a bien été soumis a l'état major, vous serez notifié de sa création ou non dans les prochains jours</font></center>";
					
				}
				else {
					echo "<center><font color='red'>Veuillez renseigner une description de compagnie</font></center>";
				}
			}
			else {
				echo "<center><font color='red'>Veuillez renseigner un nom de compagnie</font></center>";
			}
		}
		
		// A t-il demandé la création d'une compagie ?
		$sql = "SELECT count(id_em_creer_compagnie) as verif_creer_comp FROM em_creer_compagnie WHERE id_perso='$id'";
		$res = $mysqli->query($sql);
		$t = $res->fetch_assoc();
						
		$verif_creer_comp = $t["verif_creer_comp"];
		
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
		<div class="container">
		
			<p align="center"><input type="button" value="Fermer la fenêtre de création de compagnie" onclick="window.close()"></p>
			
			<?php
			if ($verif_creer_comp > 0) {
				echo "<center>Vous avez demandé la création d'un nouvelle compagnie, vous devez attendre la délibération de votre état major</a></center>";
			}
			else {
			?>
			
			<form method='post' action='creer_compagnie.php'>
				<div class="form-group col-md-6">
					<label for="nomCompagnie">Nom compagnie</label>
					<input type="text" class="form-control" id="nomCompagnie" name="nomCompagnie" maxlength="40">
				</div>
				<div class="form-group col-md-8">
					<label for="descCompagnie">Description compagnie</label>
					<textarea  class="form-control" cols="100" rows="20" id="descCompagnie" name="descCompagnie"></textarea >
				</div>
				<div class="form-group col-md-6">
					<input type="submit" name="enregistrer" value="enregistrer">
				</div>
			</form>
			
			<?php
			}
			?>
		</div>
	</body>
</html>
	<?php
	}
	else{
		echo "<center><font color='red'>Vous ne pouvez pas accéder à cette page, veuillez vous loguer.</font></center>";
	}
}
else {
	// logout
	$_SESSION = array(); // On écrase le tableau de session
	session_destroy(); // On détruit la session
	
	header("Location:../index2.php");
}
?>