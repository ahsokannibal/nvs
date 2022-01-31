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
	
	if (anim_perso($mysqli, $id_perso)) {
		
		$mess_err 	= "";
		$mess 		= "";
		
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
						<h2>Tableau des Babysittes déclarés</h2>
						
						<font color='red'><?php echo $mess_err; ?></font>
						<font color='blue'><?php echo $mess; ?></font>
					</div>
				</div>
			</div>
		
			<p align="center"><a class="btn btn-primary" href="animation.php">Retour à l'animation</a> <a class="btn btn-primary" href="jouer.php">Retour au jeu</a></p>
			
			<div class="row">
				<div class="col-12">
					<div align="center">
						<?php
						if (isset($_GET['voir_old']) && $_GET['voir_old'] == 'ok') {
							
							echo "<a href='anim_babysitte.php' class='btn btn-warning'>Fermer le tableau des déclarations passées</a><br /><br />";
							
							$sql = "SELECT * FROM declaration_babysitte WHERE date_fin < CURTIME() ORDER BY date_debut ASC";
							$res = $mysqli->query($sql);
							$nb = $res->num_rows;
							
							if ($nb > 0) {
						
								echo "<div id='table_baby_old' class='table-responsive'>";
								
								echo "<table class='table'>";
								echo "	<thead>";
								echo "		<tr>";
								echo "			<th style='text-align:center'>Date de début</th>";
								echo "			<th style='text-align:center'>Date de fin</th>";
								echo "			<th style='text-align:center'>Perso Babysitté</th>";
								echo "			<th style='text-align:center'>Perso qui Babysitte</th>";
								echo "		</tr>";
								echo "	</thead>";
								echo "	<tbody>";
							
								while ($t = $res->fetch_assoc()) {
									
									$id_perso 	= $t['id_perso'];
									$id_baby	= $t['id_baby'];
									$date_debut	= $t['date_debut'];
									$date_fin	= $t['date_fin'];
									
									// récup infos perso qui babysitte
									$sql_p = "SELECT nom_perso, clan FROM perso WHERE id_perso='$id_perso'";
									$res_p = $mysqli->query($sql_p);
									$t_p = $res_p->fetch_assoc();

									$nom_perso 	= $t_p['nom_perso'];
									$camp_perso	= $t_p['clan'];
									
									// récup infos perso babysitté
									$sql_b = "SELECT nom_perso, clan FROM perso WHERE id_perso='$id_baby'";
									$res_b = $mysqli->query($sql_b);
									$t_b = $res_b->fetch_assoc();

									$nom_baby 	= $t_b['nom_perso'];
									$camp_baby	= $t_b['clan'];
									
									// Traitement dates
									$tab_dateDebut 	= explode(" ",$date_debut);
									$tab_dateFin 	= explode(" ",$date_fin);
									
									$date_debut 	= $tab_dateDebut[0];
									$date_fin		= $tab_dateFin[0];
									
									$tab_dateDebut 	= explode("-", $date_debut);
									$tab_dateFin 	= explode("-",$date_fin);
									
									$date_debut 	= $tab_dateDebut[2]."/".$tab_dateDebut[1]."/".$tab_dateDebut[0];
									$date_fin 		= $tab_dateFin[2]."/".$tab_dateFin[1]."/".$tab_dateFin[0];
									
									echo "		<tr>";
									echo "			<td align='center'>".$date_debut."</td>";
									echo "			<td align='center'>".$date_fin."</td>";
									echo "			<td align='center'>".$nom_baby." [".$id_baby."]</td>";
									echo "			<td align='center'>".$nom_perso." [".$id_perso."]</td>";
									echo "		</tr>";
								}
								
								echo "	</tbody>";
								echo "</table>";
								
								echo "</div>";
							}
							else {
								echo "<br /><font color='red'><i>Aucun babysitte ancien trouvé</i></font>";
							}
						}
						else {
						?>
						<a href='anim_babysitte.php?voir_old=ok' class='btn btn-warning'>Voir le tableau des déclarations passées</a>
						<?php
						}
						?>
					</div>
				</div>
			</div>
			
			<br />
			
			<div class="row">
				<div class="col-12">
					<div align="center">					
						<div id="table_baby" class="table-responsive">	
					
							<?php
							$sql = "SELECT * FROM declaration_babysitte WHERE date_debut >= CURTIME() OR date_fin >= CURTIME() ORDER BY date_debut ASC";
							$res = $mysqli->query($sql);
							$nb_actif = $res->num_rows;
							
							echo "<table class='table'>";
							echo "	<thead>";
							echo "		<tr>";
							echo "			<th style='text-align:center'>Date de début</th>";
							echo "			<th style='text-align:center'>Date de fin</th>";
							echo "			<th style='text-align:center'>Perso Babysitté</th>";
							echo "			<th style='text-align:center'>Perso qui Babysitte</th>";
							echo "		</tr>";
							echo "	</thead>";
							echo "	<tbody>";
							
							if ($nb_actif) {
							
								while ($t = $res->fetch_assoc()) {
									
									$id_perso 	= $t['id_perso'];
									$id_baby	= $t['id_baby'];
									$date_debut	= $t['date_debut'];
									$date_fin	= $t['date_fin'];
									
									// récup infos perso qui babysitte
									$sql_p = "SELECT nom_perso, clan FROM perso WHERE id_perso='$id_perso'";
									$res_p = $mysqli->query($sql_p);
									$t_p = $res_p->fetch_assoc();

									$nom_perso 	= $t_p['nom_perso'];
									$camp_perso	= $t_p['clan'];
									
									// récup infos perso babysitté
									$sql_b = "SELECT nom_perso, clan FROM perso WHERE id_perso='$id_baby'";
									$res_b = $mysqli->query($sql_b);
									$t_b = $res_b->fetch_assoc();

									$nom_baby 	= $t_b['nom_perso'];
									$camp_baby	= $t_b['clan'];
									
									// Traitement dates
									$tab_dateDebut 	= explode(" ",$date_debut);
									$tab_dateFin 	= explode(" ",$date_fin);
									
									$date_debut 	= $tab_dateDebut[0];
									$date_fin		= $tab_dateFin[0];
									
									$tab_dateDebut 	= explode("-", $date_debut);
									$tab_dateFin 	= explode("-",$date_fin);
									
									$date_debut 	= $tab_dateDebut[2]."/".$tab_dateDebut[1]."/".$tab_dateDebut[0];
									$date_fin 		= $tab_dateFin[2]."/".$tab_dateFin[1]."/".$tab_dateFin[0];
									
									echo "		<tr>";
									echo "			<td align='center'>".$date_debut."</td>";
									echo "			<td align='center'>".$date_fin."</td>";
									echo "			<td align='center'>".$nom_baby." [".$id_baby."]</td>";
									echo "			<td align='center'>".$nom_perso." [".$id_perso."]</td>";
									echo "		</tr>";
								}
							}
							else {
								echo "<tr><td colspan='4' align='center'><i>Aucune déclaration de babysitting actif</i></td></tr>";
							}
							
							echo "	</tbody>";
							echo "</table>";
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
