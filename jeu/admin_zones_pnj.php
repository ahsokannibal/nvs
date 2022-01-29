<?php
session_start();
require_once("../fonctions.php");

$mysqli = db_connexion();

include ('../nb_online.php');
$phpbb_root_path = '../forum/';
if (is_dir($phpbb_root_path))
{
	include ($phpbb_root_path .'config.php');
}

if(isset($_SESSION["id_perso"])){
	
	$id_perso = $_SESSION['id_perso'];
	
	// recupération config jeu
	$admin = admin_perso($mysqli, $id_perso);
	
	if($admin){
		
		$mess_err 	= "";
		$mess 		= "";
		
		if (isset($_POST['select_zone']) && $_POST['select_zone'] != "") {
			
			$id_zone_selected = $_POST['select_zone'];
			
			if (isset($_POST['hid_id_pnj']) && $_POST['hid_id_pnj'] != ""
				&& isset($_POST['hid_old_zone']) && $_POST['hid_old_zone'] != "") {
				
				$id_pnj_maj_zone 	= $_POST['hid_id_pnj'];
				$id_old_zone		= $_POST['hid_old_zone'];
				
				$sql = "UPDATE pnj_in_zone SET id_zone='$id_zone_selected' WHERE id_zone='$id_old_zone' AND id_pnj='$id_pnj_maj_zone'";
				$mysqli->query($sql);
				
				$mess .= "Zone mise à jour pour le PNJ ".$id_pnj_maj_zone." - Ancienne zone : ".$id_old_zone." | Nouvelle zone : ".$id_zone_selected;
			}
		}
		
		if (isset($_POST['creation_zone'])) {
			
			if (isset($_POST['inputXMin']) && $_POST['inputXMin'] != ""
				&& isset($_POST['inputXMax']) && $_POST['inputXMax'] != ""
				&& isset($_POST['inputYMin']) && $_POST['inputYMin'] != ""
				&& isset($_POST['inputYMax']) && $_POST['inputYMax'] != "") {
				
				$xMin_creation_zone = $_POST['inputXMin'];
				$xMax_creation_zone	= $_POST['inputXMax'];
				$yMin_creation_zone	= $_POST['inputYMin'];
				$yMax_creation_zone	= $_POST['inputYMax'];
				
				$sql = "INSERT INTO zones (xMin_zone, xMax_zone, yMin_zone, yMax_zone) VALUES ('$xMin_creation_zone', '$xMax_creation_zone', '$yMin_creation_zone', '$yMax_creation_zone')";
				$mysqli->query($sql);
				
				$mess .= "Création de la nouvelle zone XMin = ".$xMin_creation_zone." - XMax = ".$xMax_creation_zone." - YMin : ".$yMin_creation_zone." - YMax = ".$yMax_creation_zone;
			}
		}
		
		if (isset($_POST['creation_zone_pnj'])) {
			
			if (isset($_POST['select_pnj']) && $_POST['select_pnj'] != ""
				&& isset($_POST['inputXMin_zone']) && $_POST['inputXMin_zone'] != ""
				&& isset($_POST['inputXMax_zone']) && $_POST['inputXMax_zone'] != ""
				&& isset($_POST['inputYMin_zone']) && $_POST['inputYMin_zone'] != ""
				&& isset($_POST['inputYMax_zone']) && $_POST['inputYMax_zone'] != "") {
				
				$xMin_creation_zone = $_POST['inputXMin_zone'];
				$xMax_creation_zone	= $_POST['inputXMax_zone'];
				$yMin_creation_zone	= $_POST['inputYMin_zone'];
				$yMax_creation_zone	= $_POST['inputYMax_zone'];
				
				$id_pnj_zone = $_POST['select_pnj'];
				
				$lock = "LOCK TABLE zones WRITE";
				$mysqli->query($lock);
				
				$sql = "INSERT INTO zones (xMin_zone, xMax_zone, yMin_zone, yMax_zone) VALUES ('$xMin_creation_zone', '$xMax_creation_zone', '$yMin_creation_zone', '$yMax_creation_zone')";
				$mysqli->query($sql);
				
				$id_zone_nouvelle = $mysqli->insert_id;
								
				$unlock = "UNLOCK TABLES";
				$mysqli->query($unlock);
				
				$sql = "INSERT INTO pnj_in_zone (id_pnj, id_zone) VALUES ('$id_pnj_zone', '$id_zone_nouvelle')";
				$mysqli->query($sql);
				
				$mess .= "Création d'une liaison pour le pnj ".$id_pnj_zone;
			}
		}
		
		if (isset($_GET['supprimer']) && $_GET['supprimer'] != ""
			&& isset($_GET['id_pnj']) && $_GET['id_pnj'] != "") {
			
			$id_zone_sup 	= $_GET['supprimer'];
			$id_pnj_sup		= $_GET['id_pnj'];
			
			$sql = "DELETE FROM pnj_in_zone WHERE id_pnj = '$id_pnj_sup' AND id_zone = '$id_zone_sup'";
			$mysqli->query($sql);
			
			$mess .= "Suppression de la liaison PNJ ".$id_pnj_sup." / Zone ".$id_zone_sup;
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
				<div class="col-12" >
					<div align="center">
						<h3>Administration des zones de respawn des PNJ</h3>
					</div>
				</div>
			</div>
			
			<br />
			
			<div class="row">
				<div class="col-12">
					<div align="center">
						<?php
						if (isset($_GET['creer_zone']) && $_GET['creer_zone'] == "ok") {
							
							echo "<a href='admin_zones_pnj.php' class='btn btn-success'>Retour au tableau</a><br /><br />";
							
							
							echo "<form method='POST' action='admin_zones_pnj.php'>";
							echo "	<div class='form-group'>";
							echo "		<label for='inputXMin'>X Min Zone</label>";
							echo "		<input type='text' class='form-control' id='inputXMin' name='inputXMin' value=''>";
							echo "		<label for='inputXMax'>X Max Zone</label>";
							echo "		<input type='text' class='form-control' id='inputXMax' name='inputXMax' value=''>";
							echo "		<label for='inputYMin'>Y Min Zone</label>";
							echo "		<input type='text' class='form-control' id='inputYMin' name='inputYMin' value=''>";
							echo "		<label for='inputYMax'>Y Max Zone</label>";
							echo "		<input type='text' class='form-control' id='inputYMax' name='inputYMax' value=''>";
							echo "	</div>";
							echo "	<div class='form-group'>";
							echo "		<input type='submit' class='btn btn-success' value='Créer' name='creation_zone'>";
							echo "	</div>";
							echo "</form>";
							
						}
						else {
							
							echo "<a href='admin_zones_pnj.php?creer_zone=ok' class='btn btn-success'>Créer une nouvelle Zone</a> ";
							
							if (isset($_GET['creer_affectation']) && $_GET['creer_affectation'] == "ok") {
								
								echo "<a href='admin_zones_pnj.php' class='btn btn-danger'>Annuler</a><br /><br />";
								
								$sql = "SELECT id_pnj, nom_pnj FROM pnj";
								$res = $mysqli->query($sql);
								
								echo "<form method='POST' action='admin_zones_pnj.php'>";
								echo "	<div class='form-group'>";
								echo "		<label for='select_pnj'>PNJ</label>";
								echo "		<select class='form-control' name='select_pnj' id='select_pnj'>";
								while ($t = $res->fetch_assoc()) {
									$id_pnj_zone	= $t['id_pnj'];
									$nom_pnj_zone	= $t['nom_pnj'];
									
									echo "			<option value='".$id_pnj_zone."'>".$nom_pnj_zone."</option>";
								}
								echo "		</select>";
								echo "	</div>";
								echo "	<div class='form-group'>";
								echo "		<label for='inputXMin'>X Min Zone</label>";
								echo "		<input type='text' class='form-control' id='inputXMin_zone' name='inputXMin_zone' value=''>";
								echo "	</div>";
								echo "	<div class='form-group'>";
								echo "		<label for='inputXMax'>X Max Zone</label>";
								echo "		<input type='text' class='form-control' id='inputXMax_zone' name='inputXMax_zone' value=''>";
								echo "	</div>";
								echo "	<div class='form-group'>";
								echo "		<label for='inputYMin'>Y Min Zone</label>";
								echo "		<input type='text' class='form-control' id='inputYMin_zone' name='inputYMin_zone' value=''>";
								echo "	</div>";
								echo "	<div class='form-group'>";
								echo "		<label for='inputYMax'>Y Max Zone</label>";
								echo "		<input type='text' class='form-control' id='inputYMax_zone' name='inputYMax_zone' value=''>";
								echo "	</div>";
								echo "	<div class='form-group'>";
								echo "		<input type='submit' class='btn btn-success' value='Créer' name='creation_zone_pnj'>";
								echo "	</div>";
								echo "</form>";
								
							}
							else {
								echo "<a href='admin_zones_pnj.php?creer_affectation=ok' class='btn btn-success'>Créer une nouvelle Affecation de zone</a>";
							}
						?>
						<br /><br />
						<div id="table_zones" class="table-responsive">	
							<?php
							$sql = "SELECT pnj.id_pnj, pnj.nom_pnj, pnj_in_zone.id_zone, zones.xMin_zone, zones.xMax_zone, zones.yMin_zone, zones.yMax_zone 
									FROM zones, pnj_in_zone, pnj
									WHERE pnj_in_zone.id_zone = zones.id_zone
									AND pnj_in_zone.id_pnj = pnj.id_pnj
									ORDER BY pnj.id_pnj ASC, zones.id_zone ASC";
							$res = $mysqli->query($sql);
							
							echo "<table class='table'>";
							echo "	<thead>";
							echo "		<tr>";
							echo "			<th style='text-align:center'>PNJ</th>";
							echo "			<th style='text-align:center'>Zone</th>";
							echo "			<th style='text-align:center'>Actions</th>";
							echo "		</tr>";
							echo "	</thead>";
							echo "	<tbody>";
							
							while ($t = $res->fetch_assoc()) {
								
								$id_pnj		= $t['id_pnj'];
								$nom_pnj	= $t['nom_pnj'];
								$id_zone	= $t['id_zone'];
								$xMin_zone	= $t['xMin_zone'];
								$xMax_zone	= $t['xMax_zone'];
								$yMin_zone	= $t['yMin_zone'];
								$yMax_zone	= $t['yMax_zone'];
								
								echo "		<tr>";
								echo "			<td align='center'><b>".$nom_pnj."</b></td>";
								echo "			<td align='center'>";
								if (isset($_GET['modifier_zone']) && $id_zone == $_GET['modifier_zone'] 
									&& isset($_GET['id_pnj']) && $id_pnj == $_GET['id_pnj']) {
									
									echo "<form method='POST' action='admin_zones_pnj.php'>";
									echo "	<select name='select_zone'>";
									
									$sql_zone = "SELECT * FROM zones 
													WHERE id_zone NOT IN (SELECT id_zone FROM pnj_in_zone WHERE id_pnj='$id_pnj')
													OR id_zone='$id_zone'";
									$res_zone = $mysqli->query($sql_zone);
									
									while ($tz = $res_zone->fetch_assoc()) {
										
										$id_zone_modif 		= $tz['id_zone'];
										$xMin_zone_modif	= $tz['xMin_zone'];
										$xMax_zone_modif	= $tz['xMax_zone'];
										$yMin_zone_modif	= $tz['yMin_zone'];
										$yMax_zone_modif	= $tz['yMax_zone'];
										
										$texte_zone = "Zone [".$id_zone_modif."] : <u>xMin</u> = ".$xMin_zone_modif." - <u>xMax</u> = ".$xMax_zone_modif." - <u>yMin</u> = ".$yMin_zone_modif." - <u>yMax</u> = ".$yMax_zone_modif;
										
										echo "		<option value='".$id_zone_modif."'";
										if ($id_zone_modif == $id_zone) {
											echo " selected";
										}
										echo ">".$texte_zone."</option>";
									}
									
									echo "	</select>";
									echo "	<input type='hidden' name='hid_old_zone' value='".$id_zone."'>";
									echo "	<input type='hidden' name='hid_id_pnj' value='".$id_pnj."'>";
									echo "	<input type='submit' value='Changer' class='btn btn-warning'>";
									echo "</form>";
								}
								else {
									echo "<b>Zone [".$id_zone."]</b> : <u>xMin</u> = ".$xMin_zone." - <u>xMax</u> = ".$xMax_zone." - <u>yMin</u> = ".$yMin_zone." - <u>yMax</u> = ".$yMax_zone;
								}
								echo "			</td>";
								echo "			<td align='center'>";
								echo "<a href='admin_zones_pnj.php?modifier_zone=".$id_zone."&id_pnj=".$id_pnj."' class='btn btn-warning'>Changer de zone</a> ";
								echo "<a href='admin_zones_pnj.php?supprimer=".$id_zone."&id_pnj=".$id_pnj."' class='btn btn-danger'>Supprimer la liaison</a> ";
								echo "			</td>";
								echo "		</tr>";
								
							}
							?>
						</div>
						
						<?php 
						}
						?>
						
					</div>
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
}
else{
	echo "<font color=red>Vous ne pouvez pas acceder a cette page, veuillez vous logguer.</font>";
}?>
