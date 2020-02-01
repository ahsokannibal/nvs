<?php
session_start();
require_once("../fonctions.php");

$mysqli = db_connexion();

include ('../nb_online.php');

if(isset($_SESSION["id_perso"])){
	
	$id_perso = $_SESSION['id_perso'];
	
	// recupération config jeu
	$dispo = config_dispo_jeu($mysqli);
	$admin = admin_perso($mysqli, $id_perso);
	
	if($dispo || $admin){
		
		// Récupération du camp du perso 
		$sql = "SELECT clan FROM perso WHERE id_perso='$id_perso'";
		$res = $mysqli->query($sql);
		$t = $res->fetch_assoc();
		
		$camp_perso = $t['clan'];
		
		if (isset($_POST['selectHopital'])) {
			
			$id_respawn_hopital = $_POST['selectHopital'];
			
		}
		
		if (isset($_POST['selectFortin'])) {
			
			$id_respawn_fortin = $_POST['selectFortin'];
			
		}
		
		if (isset($_POST['selectFort'])) {
			
			$id_respawn_fort = $_POST['selectFort'];
			
		}
	
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
		
			<div class="row">
				<div class="col-12">

					<div align="center">
						<h2>Choix du bâtiment de rapatriement favori</h2>
					</div>
				</div>
			</div>
			
			<div class="row">
				<div class="col-12">
				
					<a href="../regles/regles_batiments.php" target='_blank'>Rappel des règles sur le rapatriement</a>
				
				</div>
			</div>
			
			<div class="row">
				<div class="col-12">
					
					<form method='POST' action='choix_rapatriement.php'>
						<div class="form-group">
							<label for="selectHopital">Hôpital</label>
							<select name="selectHopital" class="form-control" id="selectHopital">
							<?php					
							// Récupération de la liste des Hôpitaux 
							$sql = "SELECT id_instanceBat, nom_instance, x_instance, y_instance FROM instance_batiment WHERE id_batiment='7' AND camp_instance='$camp_perso'";
							$res = $mysqli->query($sql);
							
							while ($t = $res->fetch_assoc()) {
								
								$id_instance 	= $t["id_instanceBat"];
								$nom_instance	= $t["nom_instance"];
								$x_instance		= $t["x_instance"];
								$y_instance		= $t["y_instance"];
								
								// Récupération du respawns choisi
								$sql_c = "SELECT id_instance_bat FROM perso_as_respawn WHERE id_perso='$id_perso' AND id_bat='7'";
								$res_c = $mysqli->query($sql_c);
								$t_c = $res_c->fetch_assoc();
								
								$id_instance_choix = $t_c["id_instance_bat"];
								
								echo "<option value='".$id_instance."' ";
								if ($id_instance_choix == $id_instance) {
									echo "selected";
								}
								echo " >Hopital ".$nom_instance." [".$id_instance."] (".$x_instance."/".$y_instance.")</option>";
							}
							?>
							</select>
						</div>
						<div class="form-group">
							<label for="selectFortin">Fortin</label>
							<select name="selectFortin" class="form-control" id="selectFortin">
							<?php
							// Récupération de la liste des Fortins
							$sql = "SELECT id_instanceBat, nom_instance, x_instance, y_instance FROM instance_batiment WHERE id_batiment='8' AND camp_instance='$camp_perso'";
							$res = $mysqli->query($sql);
							
							while ($t = $res->fetch_assoc()) {
								
								$id_instance 	= $t["id_instanceBat"];
								$nom_instance	= $t["nom_instance"];
								$x_instance		= $t["x_instance"];
								$y_instance		= $t["y_instance"];
								
								// Récupération du respawns choisi
								$sql_c = "SELECT id_instance_bat FROM perso_as_respawn WHERE id_perso='$id_perso' AND id_bat='8'";
								$res_c = $mysqli->query($sql_c);
								$t_c = $res_c->fetch_assoc();
								
								$id_instance_choix = $t_c["id_instance_bat"];
								
								echo "<option value='".$id_instance."' ";
								if ($id_instance_choix == $id_instance) {
									echo "selected";
								}
								echo " >Fortin ".$nom_instance." [".$id_instance."] (".$x_instance."/".$y_instance.")</option>";
							}
							?>
							</select>
						</div>
						<div class="form-group">
							<label for="selectFort">Fort</label>
							<select name="selectFort" class="form-control" id="selectFort">
							<?php
							// récupération de la liste des Forts
							$sql = "SELECT id_instanceBat, nom_instance, x_instance, y_instance FROM instance_batiment WHERE id_batiment='9' AND camp_instance='$camp_perso'";
							$res = $mysqli->query($sql);
							
							while ($t = $res->fetch_assoc()) {
								
								$id_instance 	= $t["id_instanceBat"];
								$nom_instance	= $t["nom_instance"];
								$x_instance		= $t["x_instance"];
								$y_instance		= $t["y_instance"];
								
								// Récupération du respawns choisi
								$sql_c = "SELECT id_instance_bat FROM perso_as_respawn WHERE id_perso='$id_perso' AND id_bat='9'";
								$res_c = $mysqli->query($sql_c);
								$t_c = $res_c->fetch_assoc();
								
								$id_instance_choix = $t_c["id_instance_bat"];
								
								echo "<option value='".$id_instance."' ";
								if ($id_instance_choix == $id_instance) {
									echo "selected";
								}
								echo " >Fort ".$nom_instance." [".$id_instance."] (".$x_instance."/".$y_instance.")</option>";
							}
							?>
							</select>
						</div>
						<input type='submit' value='Enregistrer'>
					</form>
					
				</div>
			</div>
			
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
	else {
		// logout
		$_SESSION = array(); // On écrase le tableau de session
		session_destroy(); // On détruit la session
		
		header("Location: ../index2.php");
	}
}
else{
	echo "<font color=red>Vous ne pouvez pas accéder à cette page, veuillez vous loguer.</font>";
}
?>