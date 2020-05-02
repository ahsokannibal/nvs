<?php
session_start();
require_once("../fonctions.php");
require_once("f_carte.php");

$mysqli = db_connexion();

include ('../nb_online.php');

if(isset($_SESSION["id_perso"])){
	
	$id_perso = $_SESSION['id_perso'];
	
	// recupération config jeu
	$admin = admin_perso($mysqli, $id_perso);
	
	if($admin){
		
		$mess_err 	= "";
		$mess 		= "";
		
		if(isset($_POST['teleporte_perso']) && $_POST['teleporte_perso'] != '') {
			
			$id_perso_a_teleporter = $_POST['teleporte_perso'];
			
		}
		
		if (isset($_POST['id_perso_teleport_hid']) 
				&& isset($_POST['bat_teleport']) && trim($_POST['bat_teleport']) != '') {
			
			$id_perso_teleport 	= $_POST['id_perso_teleport_hid'];
			$bat_teleport		= $_POST['bat_teleport'];
			
			// récupération nom et coordonnées batiment
			$sql = "SELECT nom_instance, x_instance, y_instance FROM instance_batiment WHERE id_instanceBat='$bat_teleport'";
			$res = $mysqli->query($sql);
			$t = $res->fetch_assoc();
			
			$nom_instance_bat 	= $t['nom_instance'];
			$x_instance_bat		= $t['x_instance'];
			$y_instance_bat		= $t['y_instance'];
			
			$sql = "SELECT x_perso, y_perso, image_perso FROM perso WHERE id_perso='$id_perso_teleport'";
			$res = $mysqli->query($sql);
			$t = $res->fetch_assoc();
			
			$x_perso_origin = $t['x_perso'];
			$y_perso_origin = $t['y_perso'];
			$image_perso	= $t['image_perso'];
			
			if (in_bat($mysqli, $id_perso_teleport)) {
				$sql = "DELETE FROM perso_in_batiment WHERE id_perso='$id_perso_teleport'";
			}
			else {
				$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_perso_origin' AND y_carte='$y_perso_origin'";
			}
			$mysqli->query($sql);
			
			// MAJ coordonnées perso
			$sql = "UPDATE perso SET x_perso='$x_instance_bat', y_perso='$y_instance_bat' WHERE id_perso='$id_perso_teleport'";
			$mysqli->query($sql);
			
			// Ajout du perso dans le batiment
			$sql = "INSERT INTO perso_in_batiment VALUES ('$id_perso_teleport','$bat_teleport')";
			$mysqli->query($sql);
			
			$mess = "Le perso d'id $id_perso_teleport a bien été téléporté dans le bâtiment $nom_instance_bat [".$bat_teleport."]";
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
						<h2>Administration</h2>
					</div>
				</div>
			</div>
			
			<p align="center"><a class="btn btn-primary" href="admin_nvs.php">Retour à l'administration</a> <a class="btn btn-primary" href="jouer.php">Retour au jeu</a></p>
			
			<div class="row">
				<div class="col-12">
				
					<h3>Téléportation d'un perso dans un bâtiment</h3>
					
					<center><font color='red'><?php echo $mess_err; ?></font></center>
					<center><font color='blue'><?php echo $mess; ?></font></center>
					
					<form method='POST' action='admin_teleporte_bat.php'>
					
						<select name="teleporte_perso">
						
							<?php
							$sql = "SELECT id_perso, nom_perso, x_perso, y_perso FROM perso ORDER BY id_perso ASC";
							$res = $mysqli->query($sql);
							
							while ($t = $res->fetch_assoc()) {
								
								$id_perso 	= $t["id_perso"];
								$nom_perso 	= $t["nom_perso"];
								$x_perso	= $t["x_perso"];
								$y_perso 	= $t["y_perso"];
								
								echo "<option value='".$id_perso."'";
								if (isset($id_perso_a_teleporter) && $id_perso_a_teleporter == $id_perso) {
									echo " selected";
								}
								echo ">".$nom_perso." [".$id_perso."] - ".$x_perso."/".$y_perso."</option>";
							}
							?>
						
						</select>
						
						<input type="submit" value="choisir">
						
					</form>
					
					<?php
					if (isset($id_perso_a_teleporter) && $id_perso_a_teleporter != 0) {
						
						echo "<form method='POST' action='admin_teleporte_bat.php'>";
						echo "	<input type='text' value='".$id_perso_a_teleporter."' name='id_perso_teleport' disabled>";
						echo "	<input type='hidden' value='".$id_perso_a_teleporter."' name='id_perso_teleport_hid'>";
						echo "	<select name='bat_teleport'>";
						
						$sql = "SELECT id_instanceBat, nom_instance, nom_batiment, x_instance, y_instance FROM instance_batiment, batiment, perso 
								WHERE instance_batiment.camp_instance = perso.clan
								AND instance_batiment.id_batiment = batiment.id_batiment
								AND ( instance_batiment.id_batiment='9' OR instance_batiment.id_batiment='8' OR instance_batiment.id_batiment='7' OR instance_batiment.id_batiment='11')
								AND perso.id_perso = '$id_perso_a_teleporter'
								ORDER BY id_instanceBat ASC";
						$res = $mysqli->query($sql);
							
						while ($t = $res->fetch_assoc()) {
							$id_instance_bat	= $t['id_instanceBat'];
							$nom_instance_bat	= $t['nom_instance'];
							$nom_batiment		= $t['nom_batiment'];
							$x_instance_bat		= $t['x_instance'];
							$y_instance_bat		= $t['y_instance'];
							
							echo "		<option value='".$id_instance_bat."'>".$nom_batiment." ".$nom_instance_bat."[".$id_instance_bat."] en ".$x_instance_bat."/".$y_instance_bat."</option>";
						}
						
						echo "	</select>";
						echo "	<input type='submit' value='téléporter'>";
						echo "</form>";
					}
					?>
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
		
		header("Location:../index2.php");
	}
}
else{
	echo "<font color=red>Vous ne pouvez pas accéder à cette page, veuillez vous loguer.</font>";
}
?>