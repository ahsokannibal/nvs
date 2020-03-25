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
				&& isset($_POST['coord_x_teleport']) && trim($_POST['coord_x_teleport']) != ''
				&& isset($_POST['coord_y_teleport']) && trim($_POST['coord_y_teleport']) != '') {
			
			$id_perso_teleport 	= $_POST['id_perso_teleport_hid'];
			$x_teleport			= $_POST['coord_x_teleport'];
			$y_teleport			= $_POST['coord_y_teleport'];
			
			// On verifie si les coordonnées sont dispo
			$sql = "SELECT occupee_carte FROM carte WHERE x_carte='$x_teleport' AND y_carte='$y_teleport'";
			$res = $mysqli->query($sql);
			$t = $res->fetch_assoc();
			
			$occupee = $t['occupee_carte'];
			
			if (!$occupee) {
			
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
				
				$sql = "UPDATE perso SET x_perso='$x_teleport', y_perso='$y_teleport' WHERE id_perso='$id_perso_teleport'";
				$mysqli->query($sql);
				
				$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte='$id_perso_teleport', image_carte='$image_perso' WHERE x_carte='$x_teleport' AND y_carte='$y_teleport'";
				$mysqli->query($sql);
				
				$mess = "Le perso d'id $id_perso_teleport a bien été téléporté en $x_teleport / $y_teleport";
			}
			else {
				$mess_err = "La case cible est déjà occupée";
			}
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
			
			<p align="center"><a class="btn btn-primary" href="jouer.php">Retour au jeu</a></p>
			
			<div class="row">
				<div class="col-12">
				
					<h3>Téléportation d'un perso</h3>
					
					<center><font color='red'><?php echo $mess_err; ?></font></center>
					<center><font color='blue'><?php echo $mess; ?></font></center>
					
					<form method='POST' action='admin_teleporte.php'>
					
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
						
						echo "<form method='POST' action='admin_teleporte.php'>";
						echo "	<input type='text' value='".$id_perso_a_teleporter."' name='id_perso_teleport' disabled>";
						echo "	<input type='hidden' value='".$id_perso_a_teleporter."' name='id_perso_teleport_hid'>";
						echo "	<input type='text' value='' name='coord_x_teleport'>";
						echo "	<input type='text' value='' name='coord_y_teleport'>";
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