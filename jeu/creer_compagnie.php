<?php
session_start();
require_once("../fonctions.php");

$mysqli = db_connexion();

include ('../nb_online.php');

// recupération config jeu
$dispo = config_dispo_jeu($mysqli);
$admin = admin_perso($mysqli, $_SESSION["id_perso"]);

if($dispo == '1' || $admin){
	
	if (isset($_SESSION["id_perso"])) {
		
		//recuperation des variables de sessions
		$id = $_SESSION["id_perso"];
		
		// verification que le perso n'est pas deja dans une compagnie ou en attente sur une autre
		$sql = "SELECT id_perso FROM perso_in_compagnie WHERE id_perso='$id'";
		$res = $mysqli->query($sql);
		$est_deja = $res->num_rows;
		
		// récupération du camp du perso
		$sql = "SELECT clan, type_perso FROM perso WHERE id_perso='$id'";
		$res = $mysqli->query($sql);
		$t_p = $res->fetch_assoc();
		
		$camp 	= $t_p['clan'];
		$type_p	= $t_p['type_perso'];
		
		if ($type_p != 6) {
		
			if (isset($_POST["enregistrer"])) {
				
				if (!$est_deja) {
				
					if (isset($_POST["nomCompagnie"]) && trim($_POST["nomCompagnie"]) != "") {
						
						if (isset($_POST["descCompagnie"]) && trim($_POST["descCompagnie"]) != "") {
							
							$nom_compagnie 	= addslashes($_POST["nomCompagnie"]);
							$desc_compagnie	= addslashes($_POST["descCompagnie"]);
							
							// Est ce que le nom de cette compagnie est déjà pris ?
							$sql = "SELECT * FROM compagnies WHERE nom_compagnie='$nom_compagnie'";
							$res = $mysqli->query($sql);
							$verif = $res->num_rows;
							
							if ($verif == 0) {
							
								$sql = "INSERT INTO em_creer_compagnie (id_perso, nom_compagnie, description_compagnie, camp) VALUES ('$id', '$nom_compagnie', '$desc_compagnie', '$camp')";
								$mysqli->query($sql);
								
								echo "<center><font color='blue'>Votre compagnie " . $_POST["nomCompagnie"] . " a bien été soumis a l'état major, vous serez notifié de sa création ou non dans les prochains jours</font></center>";
							} else {
								
								$_SESSION['desc_compagnie'] = $_POST["descCompagnie"];
								
								echo "<center><font color='red'>Une compagnie du nom " . $_POST["nomCompagnie"] . " existe déjà, veuillez choisir un autre nom</font></center>";
							}
						}
						else {
							echo "<center><font color='red'>Veuillez renseigner une description de compagnie</font></center>";
						}
					}
					else {
						echo "<center><font color='red'>Veuillez renseigner un nom de compagnie</font></center>";
					}
				}
				else {
					echo "<center><font color='red'>Votre perso fait déjà parti d'une compagnie et ne peux donc pas demander la création d'une autre</font></center>";
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
			else if ($est_deja) {
				echo "<center>Votre perso fait déjà parti d'une compagnie et ne peux donc pas demander la création d'une autre</a></center>";
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
					<textarea  class="form-control" cols="100" rows="20" id="descCompagnie" name="descCompagnie"><?php if(isset($_SESSION['desc_compagnie'])) { echo $_SESSION['desc_compagnie']; } ?></textarea >
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
		else {
			echo "<center><font color='red'>Les chiens ne peuvent pas accèder à cette page.</font></center>";
		}
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