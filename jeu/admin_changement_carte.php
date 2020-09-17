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
		
		if (isset($_GET['carte_cible']) && trim($_GET['carte_cible']) != "") {
			
			$carte_cible = $_GET['carte_cible'];
			
			// Récupération des positions des Forts
			$sql = "SELECT id_camp, position_x, position_y FROM em_position_infra_carte_suivante WHERE carte='$carte_cible' AND id_batiment='9'";
			$res = $mysqli->query($sql);
			$verif_forts = $res->num_rows;
			
			if ($verif_forts == 2) {
						
				while ($t = $res->fetch_assoc()) {
					
					$
					
				}
				
				// passer jeu en mode mise à jour
				
				// Vider table instance_batiment
				
				// Vider table instance_batiment_canon
				
				// Vider table instance_pnj
				
				// Vider table liaisons_gare
				
				// Vider table histo_stats_camp_pv (après affichage sur forum)
				
				// Vider table objet_in_carte
				
				// Vider yable perso_as_respawn
				
				// Vider table perso_in_batiment
				
				// Vider table perso_in_train
				
				// Vider table zones (à redéfinir après installation carte)
				// Vider table pnj_in_zone (à redéfinir après installation carte)
				
				// Vider table carte
				
				// Inserer données carte à partir des données issues de la carte choisie
				
				// Insérer batiment Fort des 2 camps
				
				// Insérer persos dans Fort
				
				// Vider table choix_carte_suivante
				
				// Vider table em_position_infra_carte_suivante
				
				// passer jeu en mode disponible
			}
			else {
				$mess_err .= "La position des Forts n'a pas été défini pour les 2 camps";
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
						<h2>Changement de carte</h2>
						
						<font color='red'><?php echo $mess_err; ?></font>
						<font color='blue'><?php echo $mess; ?></font>
					</div>
				</div>
			</div>
		
			<p align="center"><a class="btn btn-primary" href="admin_nvs.php">Retour à l'administration</a> <a class="btn btn-primary" href="jouer.php">Retour au jeu</a></p>
			
			<div class="row">
				<div class="col-12">
					<div align="center">
						<div id="table_choix_carte" class="table-responsive">
						<?php
						echo "<table class='table'>";
						echo "	<thead>";
						echo "		<tr>";
						echo "			<th style='text-align:center'>Camp</th>";
						echo "			<th style='text-align:center'>Choix de la carte</th>";
						echo "			<th style='text-align:center'>Date de choix</th>";
						echo "			<th style='text-align:center'>Action</th>";
						echo "		</tr>";
						echo "	</thead>";
						echo "	<tbody>";
						
						$sql = "SELECT * FROM choix_carte_suivante";
						$res = $mysqli->query($sql);
						
						while ($t = $res->fetch_assoc()) {
							
							$id_camp 		= $t['id_camp'];
							$choix_carte 	= $t['carte'];
							$date_choix		= $t['date_choix'];
							
							if ($id_camp == 1) {
								$nom_camp 		= "Nord";
								$couleur_camp	= "blue";
							}
							else {
								$nom_camp 		= "Sud";
								$couleur_camp	= "red";
							}
							
							echo "		<tr>";
							echo "			<td align='center'><font color='".$couleur_camp."'>".$nom_camp."</font></td>";
							echo "			<td align='center'>".$choix_carte."</td>";
							echo "			<td align='center'>".$date_choix."</td>";
							echo "			<td align='center'><a href='admin_changement_carte.php?carte_cible=".$choix_carte."' class='btn btn-danger'>Basculer sur cette carte</a></td>";
							echo "		</tr>";
							
						}
						?>
						</div>
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