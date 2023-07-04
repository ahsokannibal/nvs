<?php
session_start();
require_once("../fonctions.php");
require_once("f_carte.php");
require_once("../mvc/model/Administration.php");
require_once("../mvc/model/Building.php");
require_once("../mvc/model/Perso.php");

$mysqli = db_connexion();

include ('../nb_online.php');

if(isset($_SESSION["id_perso"])){
	
	$id_perso = $_SESSION['id_perso'];
	
	// recupération config jeu
	$admin = admin_perso($mysqli, $id_perso);
	
	if($admin){
		
		$mess_err 	= "";
		$mess 		= "";
		
		$building = new Building();
		$perso = new Perso();
		
		$administration = new Administration();
		$dispo = $administration->checkMaintenanceMode();

		$forts_North = $building->getByType(9,1);
		$forts_South = $building->getByType(9,2);
		
		if(isset($_GET['action']) && $_GET['action']=='teleport') {
			if(isset($_POST['id']) && !empty($_POST['id'])){
				switch($_POST['id']){
					case 1:
						if(isset($_POST['teleport_bat_north']) && (!empty($_POST['teleport_bat_north']) OR $_POST['teleport_bat_north']==0)){
							$fort = $building->getById($_POST['teleport_bat_north']);
							$allPerso = $perso->getAllPerso(['id_perso'],$_POST['id']);
						}
					break;
					case 2:
						if(isset($_POST['teleport_bat_south']) && (!empty($_POST['teleport_bat_south']) OR $_POST['teleport_bat_south']==0)){
							$fort = $building->getById($_POST['teleport_bat_south']);
							$allPerso = $perso->getAllPerso(['id_perso'],$_POST['id']);
						}
					break;
					default:
					header('location:?');
				}
				
				$id_fort = $fort['id_instanceBat'];
				$x_fort = $fort['x_instance'];
				$y_fort = $fort['y_instance'];

				//insérer tous les persos dans le fort
				foreach($allPerso as $perso){
					$id = $perso['id_perso'];
					$query = "INSERT INTO `perso_in_batiment` VALUES ('$id','$id_fort')";
					$mysqli->query($query);
					
					// calcul bonus perception perso
					$bonus_visu = getBonusObjet($mysqli, $id);
					
					$sql = "UPDATE perso SET x_perso='$x_fort', y_perso='$y_fort', pv_perso=pvMax_perso, pa_perso=paMax_perso, pm_perso=pmMax_perso, bonusPerception_perso=$bonus_visu, bonus_perso=0, bourre_perso=0, convalescence=0 WHERE id_perso='$id'";
					$res = $mysqli->query($sql);
				}
			}else{
				header('location:?');
			}
		}
		
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
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-iYQeCzEYFbKjA/T2uDLTpkwGzCiq6soy8tYaI1GyVh/UjpbCx/TYkiZhlZB6+fzT" crossorigin="anonymous">
		<link rel="stylesheet" href="../public/css/app.css">
	</head>
	
	<body class='game'>
		<div class="container">
			<div class="row my-2">
				<div class="col-12 text-center">
					<h2>Administration</h2>
					<p>
						<a class="btn btn-primary" href="admin_nvs.php">Retour à l'administration</a>
						<a class="btn btn-primary" href="jouer.php">Retour au jeu</a>
					</p>
				</div>
			</div>
			<div class="row row-cols-1 row-cols-md-2 g-4">
				<div class='col'>
					<div class="card shadow">
						<div class='card-header'>
							<h3 class='mt-2'>Téléportation de tous les persos après réinitialisation de la carte</h3>
							<p class='fst-italic fs-5 mt-4'>
								Expérimental. A n'utiliser que si la carte a été changée et les forts de chaque camp créés.<br>
								remet au maximum les caractéristiques des persos.
							</p>
						</div>
						<div class="card-body">
							<?php if($dispo==0):?>
							<ul class='list-group'>
								<li class='list-group-item p'>
									<form class='row row-cols-md-auto g-3 align-items-center"' name='teleport_north' method='post' action='?action=teleport'>
										<input type="hidden" name="id" value="1">
										<div class='col-12'>Nord</div>
										<div class='col-12'>
											<select class="form-select" aria-label="Sélection du bâtiment Nord" name="teleport_bat_north">
												<option value="0" disabled selected>Choix du fort</option>
												<?php foreach($forts_North as $fort): ?>
												<option value="<?= $fort['id_instanceBat'] ?>">Fort <?= $fort['nom_instance']?> ['<?= $fort['id_instanceBat'] ?>'] en <?= $fort['x_instance']?>/<?= $fort['y_instance']?></option>
												<?php endforeach; ?>
											</select>
										</div>
										<div class='col-12'>
											<button type='submit' class='btn btn-success'>
											<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 me-2 align-bottom">
											  <path stroke-linecap="round" stroke-linejoin="round" d="M9 9V4.5M9 9H4.5M9 9L3.75 3.75M9 15v4.5M9 15H4.5M9 15l-5.25 5.25M15 9h4.5M15 9V4.5M15 9l5.25-5.25M15 15h4.5M15 15v4.5m0-4.5l5.25 5.25" />
											</svg>
											Téléporter
											</button>
										</div>
									</form>
								</li>
								<li class='list-group-item d-flex flex-wrap'>
									<form class='row row-cols-md-auto g-3 align-items-center"' name='teleport_south' method='post' action='?action=teleport'>
										<input type="hidden" name="id" value="2">
										<div class='col-12'>Sud</div>
										<div class='col-12'>
											<select class="form-select" aria-label="Sélection du bâtiment Sud" name="teleport_bat_south">
												<option value="0" disabled selected>Choix du fort</option>
												<?php foreach($forts_South as $fort): ?>
												<option value="<?= $fort['id_instanceBat'] ?>">Fort <?= $fort['nom_instance']?> ['<?= $fort['id_instanceBat'] ?>'] en <?= $fort['x_instance']?>/<?= $fort['y_instance']?></option>
												<?php endforeach; ?>
											</select>
										</div>
										<div class='col-12'>
											<button type='submit' class='btn btn-success'>
											<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 me-2 align-bottom">
											  <path stroke-linecap="round" stroke-linejoin="round" d="M9 9V4.5M9 9H4.5M9 9L3.75 3.75M9 15v4.5M9 15H4.5M9 15l-5.25 5.25M15 9h4.5M15 9V4.5M15 9l5.25-5.25M15 15h4.5M15 15v4.5m0-4.5l5.25 5.25" />
											</svg>
											Téléporter
											</button>
										</div>
									</form>
								</li>
							</ul>
							<?php else:?>
							<p>Pour accéder à la téléportation générale vous devez mettre le jeu en maintenance</p>
							<?php endif; ?>
						</div>
					</div>
				</div>
				<div class="col">
					<div class="card shadow">
						<div class='card-header'>
							<h3>Téléportation d'un perso dans un bâtiment</h3>
							<center><font color='red'><?php echo $mess_err; ?></font></center>
							<center><font color='blue'><?php echo $mess; ?></font></center>
						</div>
						<div class="card-body">
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
										AND ( instance_batiment.id_batiment='9' OR instance_batiment.id_batiment='8' OR instance_batiment.id_batiment='7' OR instance_batiment.id_batiment='11' OR instance_batiment.id_batiment='12')
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
			</div>
			
		</div>
		
		<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous" defer></script>
		<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/js/bootstrap.min.js" integrity="sha384-7VPbUDkoPSGFnVtYi0QogXtr74QeVeeIs99Qfg5YCF+TidwNdjvaKZX19NZ/e6oz" crossorigin="anonymous"></script>
		<script type="text/javascript" src="../public/js/app.js" defer></script>
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