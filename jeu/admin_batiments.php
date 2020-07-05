<?php
session_start();
require_once("../fonctions.php");

$mysqli = db_connexion();

include ('../nb_online.php');
include ('../forum/config.php');

if(isset($_SESSION["id_perso"])){
	
	$id_perso = $_SESSION['id_perso'];
	
	// recupération config jeu
	$admin = admin_perso($mysqli, $id_perso);
	
	if($admin){
		
		$mess_err 	= "";
		$mess 		= "";
		
		if (isset($_POST["destruction_pont"]) && $_POST["destruction_pont"] == 'ok') {
			
			// Destruction des ponts
			$sql = "UPDATE carte SET fond_carte='8.gif', save_info_carte=NULL WHERE fond_carte='b5b.png' OR fond_carte='b5r.png'";
			$mysqli->query($sql);
			
			$sql = "DELETE FROM instance_batiment WHERE id_batiment='5'";
			$mysqli->query($sql);
			
			$sql = "UPDATE carte SET idPerso_carte=NULL WHERE idPerso_carte > 50000 AND idPerso_carte < 200000 AND idPerso_carte NOT IN (SELECT id_instanceBat FROM instance_batiment) ";
			$mysqli->query($sql);
			
			$mess .= "Tous les ponts ont été détruit avec succès";
		}
		
		if (isset($_POST["destruction_barricade"]) && $_POST["destruction_barricade"] == 'ok') {
			
			// Destruction des ponts
			$sql = "UPDATE carte SET idPerso_carte=NULL, save_info_carte=NULL, image_carte=NULL WHERE fond_carte='b1b.png' OR fond_carte='b1r.png'";
			$mysqli->query($sql);
			
			$sql = "DELETE FROM instance_batiment WHERE id_batiment='1'";
			$mysqli->query($sql);
			
			$mess .= "Toutes les barricades ont été détruites avec succès";
		}
		
		if (isset($_POST['id_instance_bat_destruction']) && $_POST['id_instance_bat_destruction'] != "") {
			
			$id_instance_bat_destruction = $_POST['id_instance_bat_destruction'];
			
			// Est ce qu'il y a des persos dans le batiment ?
			$sql = "SELECT id_perso FROM perso_in_batiment WHERE id_instanceBat='$id_instance_bat_destruction'";
			$res = $mysqli->query($sql);
			$nb_persos_bat = $res->num_rows;
			
			if ($nb_persos_bat == 0) {
				
				// recup id_batiment
				$sql = "SELECT id_batiment FROM instance_batiment WHERE id_instanceBat='$id_instance_bat_destruction'";
				$res = $mysqli->query($sql);
				$t = $res->fetch_assoc();
				
				$id_bat = $t['id_batiment'];
				
				if ($id_bat == 5) {
					// Ponts
					$sql = "UPDATE carte SET fond_carte='8.gif', save_info_carte=NULL WHERE save_info_carte='$id_instance_bat_destruction'";
					$mysqli->query($sql);
					
					$sql = "UPDATE carte SET idPerso_carte=NULL WHERE idPerso_carte=''$id_instance_bat_destruction''";
					$mysqli->query($sql);
				}
				else {
					// Autres batiments
					$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, save_info_carte=NULL, image_carte=NULL WHERE idPerso_carte='$id_instance_bat_destruction'";
					$mysqli->query($sql);
				}
			
				$sql = "DELETE FROM instance_batiment WHERE id_instanceBat='$id_instance_bat_destruction'";
				$mysqli->query($sql);
				
				$mess .= "le batiment ".$id_instance_bat_destruction." a été détruit avec succès";
			}
			else {
				$mess_err .= "Des persos se trouvent encore dans le batiment, batiment impossible à détruire";
			}
			
		}
		
		if (isset($_POST['hid_id_instance_rename']) && isset($_POST['nom_batiment']) && $_POST['nom_batiment'] != "") {
			
			$id_instance_bat_rename = $_POST['hid_id_instance_rename'];
			$nouveau_nom_bat		= addslashes($_POST['nom_batiment']);
			
			$sql = "UPDATE instance_batiment SET nom_instance='$nouveau_nom_bat' WHERE id_instanceBat='$id_instance_bat_rename'";
			$mysqli->query($sql);
			
			$mess .= "le batiment ".$id_instance_bat_rename." a été renommé avec succès en ".$nouveau_nom_bat;
			
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
						
						<center><font color='red'><?php echo $mess_err; ?></font></center>
						<center><font color='blue'><?php echo $mess; ?></font></center>
					</div>
				</div>
			</div>
		
			<p align="center"><a class="btn btn-primary" href="admin_nvs.php">Retour à l'administration</a> <a class="btn btn-primary" href="jouer.php">Retour au jeu</a></p>
		
			<div class="row">
				<div class="col-12">
				
					<h3>Administration des batiments</h3>
					
					<button type="button" class="btn btn-danger" data-toggle="modal" data-target="#modalConfirmPont">Détruire tous les ponts du jeu</button>
					<button type="button" class="btn btn-danger" data-toggle="modal" data-target="#modalConfirmBarricade">Détruire toutes les barricades du jeu</button>
					
				</div>
			</div>
			
			<br />
			
			<div class="row">
				<div class="col-12">
					<div align="center">					
						<div id="table_batiments" class="table-responsive">	
					
							<?php
							$sql = "SELECT id_instanceBat, instance_batiment.id_batiment, nom_batiment, nom_instance, pv_instance, pvMax_instance, x_instance, y_instance, camp_instance 
									FROM instance_batiment, batiment 
									WHERE instance_batiment.id_batiment = batiment.id_batiment
									ORDER BY camp_instance, instance_batiment.id_batiment, x_instance, y_instance ASC";
							$res = $mysqli->query($sql);
							
							echo "<table class='table'>";
							echo "	<thead>";
							echo "		<tr>";
							echo "			<th>Batiment</th><th>Coordonnées</th><th>PV</th><th>Action</th>";
							echo "		</tr>";
							echo "	</thead>";
							echo "	<tbody>";
							
							while ($t = $res->fetch_assoc()) {
								
								$id_instance_bat 	= $t['id_instanceBat'];
								$id_bat				= $t['id_batiment'];
								$nom_bat			= $t['nom_batiment'];
								$nom_instance_bat	= htmlentities($t['nom_instance'],ENT_QUOTES);
								$pv_instance_bat	= $t['pv_instance'];
								$pvMax_instance_bat	= $t['pvMax_instance'];
								$x_instance_bat		= $t['x_instance'];
								$y_instance_bat		= $t['y_instance'];
								$camp_instance_bat	= $t['camp_instance'];
								
								if ($camp_instance_bat == 1) {
									$color_camp = "blue";
								}
								else if ($camp_instance_bat == 2) {
									$color_camp = "red";
								}
								else if ($camp_instance_bat == 3) {
									$color_camp = "green";
								}
								else {
									$color_camp = "black";
								}
								
								echo "		<tr>";
								echo "<form method=\"post\" action=\"admin_batiments.php\">";
								echo "			<td>";
								echo "				<input type='hidden' name='hid_id_instance_rename' value='$id_instance_bat'>";
								echo "				<font color='".$color_camp."'>".$nom_bat." <input type='text' name='nom_batiment' value='".$nom_instance_bat."' ><input type='submit' name='rename_bat' value='Renommer' class='btn btn-primary'> [<a href='evenement.php?infoid=".$id_instance_bat."'>".$id_instance_bat."</a>]</font>";
								echo "			</td>";
								echo "</form>";
								echo "			<td>".$x_instance_bat."/".$y_instance_bat."</td>";
								echo "			<td>".$pv_instance_bat."/".$pvMax_instance_bat."</td>";
								echo "<form method=\"post\" action=\"admin_batiments.php\">";	
								echo "			<td>";
								echo "				<input type='hidden' name='id_instance_bat_destruction' value='".$id_instance_bat."'>";
								echo "				<input type='submit' name='destruire_bat' value='Détruire' class='btn btn-danger'>";
								echo "			</td>";
								echo "</form>";
								echo "		</tr>";
								
							}
							
							echo "	</tbody>";
							echo "</table>";
							?>
						
						</div>
					</div>
				</div>
			</div>
		
		</div>
		
		<!-- Modal -->
		<form method="post" action="admin_batiments.php">
			<div class="modal fade" id="modalConfirmPont" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
				<div class="modal-dialog modal-dialog-centered" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title" id="exampleModalCenterTitle">Détruire tous les ponts du jeu</h5>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>
						<div class="modal-body">
							Êtes-vous sûr de vouloir détruire tous les ponts du jeu ?
							<input type='hidden' name='destruction_pont' value='ok'>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
							<button type="button" onclick="this.form.submit()" class="btn btn-primary">Détruire</button>
						</div>
					</div>
				</div>
			</div>
		</form>
		
		<form method="post" action="admin_batiments.php">
			<div class="modal fade" id="modalConfirmbarricade" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
				<div class="modal-dialog modal-dialog-centered" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title" id="exampleModalCenterTitle">Détruire toutes les barricades du jeu</h5>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>
						<div class="modal-body">
							Êtes-vous sûr de vouloir détruire toutes les barricades du jeu ?
							<input type='hidden' name='destruction_barricade' value='ok'>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
							<button type="button" onclick="this.form.submit()" class="btn btn-primary">Détruire</button>
						</div>
					</div>
				</div>
			</div>
		</form>
		
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
