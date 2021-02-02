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
	
	if($admin) {
		
		$mess_err 	= "";
		$mess 		= "";
		
		if(isset($_POST['select_perso']) && $_POST['select_perso'] != '') {
			$id_perso_select = $_POST['select_perso'];
		}
		
		if (isset($_GET['id_perso']) && $_GET['id_perso'] != '') {
			$id_perso_select = $_GET['id_perso'];
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
						<h2>Top Logs d'accès</h2>
						
						<font color='red'><?php echo $mess_err; ?></font>
						<font color='blue'><?php echo $mess; ?></font>
					</div>
				</div>
			</div>
		
			<p align="center"><a class="btn btn-primary" href="admin_nvs.php">Retour à l'administration</a> <a class="btn btn-primary" href="jouer.php">Retour au jeu</a></p>
			
			
			<div class="row">
				<div class="col-12">
					<div align="center">
						<?php
						if (!isset($_GET['top_all'])) {
							echo "<a href='admin_top_logs_acces.php?top_all=ok' class='btn btn-secondary'>Top global</a> ";
						}
						if (!isset($_GET['top_jour'])) {
							echo "<a href='admin_top_logs_acces.php?top_jour=ok' class='btn btn-warning'>Top par jour</a> ";
						}
						if (!isset($_GET['top_mois'])) {
							echo "<a href='admin_top_logs_acces.php?top_mois=ok' class='btn btn-warning'>Top par mois</a> ";
						}
						if (!isset($_GET['top_annee'])) {
							echo "<a href='admin_top_logs_acces.php?top_annee=ok' class='btn btn-warning'>Top par année</a> ";
						}
						echo "<br /><br />";
						?>
					
						<div id="table_top_logs_acces" class="table-responsive">
							<?php							
							echo "<table class='table'>";
							echo "	<thead>";
							echo "		<tr>";
							echo "			<th style='text-align:center'>Classement</th>";
							echo "			<th style='text-align:center'>Perso</th>";
							if (!isset($_GET['top_all'])) {
								echo "			<th style='text-align:center'>Date</th>";
							}
							echo "			<th style='text-align:center'>Nb Logs</th>";
							echo "			<th style='text-align:center'>Action</th>";
							echo "		</tr>";
							echo "	</thead>";
							echo "	<tbody>";
							
							if (isset($_GET['top_jour'])) {
								$sql = "SELECT id_perso, DAY(date_acces) as jour, MONTH(date_acces) as mois, YEAR(date_acces) as annee, COUNT(*) as nb_logs 
										FROM acces_log 
										GROUP BY YEAR(date_acces), MONTH(date_acces), DAY(date_acces), id_perso
										ORDER BY nb_logs DESC LIMIT 100";
							}
							elseif (isset($_GET['top_mois'])) {
								$sql = "SELECT id_perso, MONTH(date_acces) as mois, YEAR(date_acces) as annee, COUNT(*) as nb_logs 
										FROM acces_log 
										GROUP BY YEAR(date_acces), MONTH(date_acces), id_perso
										ORDER BY nb_logs DESC LIMIT 100";
							}
							elseif (isset($_GET['top_annee'])) {
								$sql = "SELECT id_perso, YEAR(date_acces) as annee, COUNT(*) as nb_logs 
										FROM acces_log 
										GROUP BY YEAR(date_acces), id_perso
										ORDER BY nb_logs DESC LIMIT 100";
							}
							elseif (isset($_GET['top_all'])) {
								$sql = "SELECT id_perso, COUNT(*) as nb_logs 
										FROM acces_log 
										GROUP BY id_perso
										ORDER BY nb_logs DESC LIMIT 100";
							}
							
							$res = $mysqli->query($sql);
							
							
							$ordre = 1;
							while ($t = $res->fetch_assoc()) {
								
								$id_perso 	= $t['id_perso'];
								$nb_logs 	= $t['nb_logs'];
								
								if (isset($_GET['top_jour'])) {
									$jour_log	= $t['jour'];
								}
								if (isset($_GET['top_jour']) || isset($_GET['top_mois'])) {
									$mois_log	= $t['mois'];
								}
								if (isset($_GET['top_jour']) || isset($_GET['top_mois']) || isset($_GET['top_annee'])) {
									$annee_log	= $t['annee'];
								}								
								
								echo "<tr>";
								echo "	<td align='center'>".$ordre."</td>";
								echo "	<td align='center'>".$id_perso."</td>";
								if (!isset($_GET['top_all'])) {
									echo "	<td align='center'>";
									if (isset($_GET['top_jour'])) {
										echo sprintf('%02d', $jour_log)."/";
									}
									if (isset($_GET['top_jour']) || isset($_GET['top_mois'])) {
										echo sprintf('%02d', $mois_log)."/";
									}
									if (isset($_GET['top_jour']) || isset($_GET['top_mois']) || isset($_GET['top_annee'])) {
										echo $annee_log;
									}
									echo "</td>";
								}
								echo "	<td align='center'>".$nb_logs."</td>";
								echo "	<td align='center'>";
								if (isset($_GET['top_all'])) {
									echo "		<a href='admin_log_access.php?id_perso=".$id_perso."&detail_complet=ok' class='btn btn-primary'>Détail logs</a> ";
								}
								if (isset($_GET['top_jour'])) {
									echo "		<a href='admin_log_access.php?id_perso=".$id_perso."&stat_jour=ok&jour=".$jour_log."&mois=".$mois_log."&annee=".$annee_log."' class='btn btn-primary'>Détail logs</a>";
								}
								if (isset($_GET['top_mois'])) {
									echo "		<a href='admin_log_access.php?id_perso=".$id_perso."&stat_mois=ok&mois=".$mois_log."&annee=".$annee_log."' class='btn btn-primary'>Détail logs</a>";
								}
								if (isset($_GET['top_annee'])) {
									echo "		<a href='admin_log_access.php?id_perso=".$id_perso."&stat_annee=ok&annee=".$annee_log."' class='btn btn-primary'>Détail logs</a>";
								}
								echo "	</td>";
								echo "</tr>";
								
								$ordre++;
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