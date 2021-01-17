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
						<h2>Logs d'accès</h2>
						
						<font color='red'><?php echo $mess_err; ?></font>
						<font color='blue'><?php echo $mess; ?></font>
					</div>
				</div>
			</div>
		
			<p align="center"><a class="btn btn-primary" href="admin_nvs.php">Retour à l'administration</a> <a class="btn btn-primary" href="jouer.php">Retour au jeu</a></p>
			
			
			<div class="row">
				<div class="col-12">
					<form method='POST' action='admin_log_access.php'>
						<select name="select_perso" onchange="this.form.submit()">
							<?php
							$sql = "SELECT id_perso, nom_perso FROM perso ORDER BY id_perso ASC";
							$res = $mysqli->query($sql);
							
							while ($t = $res->fetch_assoc()) {
								
								$id_perso 	= $t["id_perso"];
								$nom_perso 	= $t["nom_perso"];
								
								echo "<option value='".$id_perso."'";
								if (isset($id_perso_select) && $id_perso_select == $id_perso) {
									echo " selected";
								}
								echo ">".$nom_perso." [".$id_perso."]</option>";
							}
							?>
						</select>
						<input type="submit" value="choisir">
					</form>
				</div>
			</div>
			
			
			<br />
			
			<?php
			if (isset($id_perso_select) && trim($id_perso_select) != "") {
			?>
			
			<div class="row">
				<div class="col-12">
					<div align="center">	
						<?php
						if (isset($_GET['stat_jour'])) {
							echo "<a href='admin_log_access.php?id_perso=".$id_perso_select."' class='btn btn-warning'>Détail des logs</a>";
						}
						else {
							echo "<a href='admin_log_access.php?id_perso=".$id_perso_select."&stat_jour=ok' class='btn btn-warning'>Statistiques par jour</a>";
						}
						echo "<br /><br />";
						?>
						<div id="table_logs_acces" class="table-responsive">
							<?php
							if (isset($_GET['stat_jour'])) {
								$sql = "SELECT DAY(date_acces) as jour, COUNT(*) as nb_logs
									FROM acces_log
									WHERE  id_perso='$id_perso_select'
									GROUP BY YEAR(date_acces), MONTH(date_acces), DAY(date_acces)";
							}
							else {
								$sql = "SELECT * FROM acces_log WHERE id_perso='$id_perso_select' ORDER BY id_acces DESC";
							}
							$res = $mysqli->query($sql);
							
							echo "<table class='table'>";
							echo "	<thead>";
							echo "		<tr>";
							if (isset($_GET['stat_jour'])) {
								echo "			<th style='text-align:center'>Jour</th>";
								echo "			<th style='text-align:center'>nb logs</th>";
							}
							else {
								echo "			<th style='text-align:center'>Date accès</th>";
								echo "			<th style='text-align:center'>Page</th>";
							}
							echo "		</tr>";
							echo "	</thead>";
							echo "	<tbody>";
							
							while ($t = $res->fetch_assoc()) {
								
								if (isset($_GET['stat_jour'])) {
									$jour		= $t['jour'];
									$nb_logs	= $t['nb_logs'];
									
									echo "		<tr>";
									echo "			<td align='center'>".$jour."</td>";
									echo "			<td align='center'>".$nb_logs."</td>";
									echo "		</tr>";
								}
								else {
									$date_acces	= $t['date_acces'];
									$page_acces	= $t['page'];
									
									echo "		<tr>";
									echo "			<td align='center'>".$date_acces."</td>";
									echo "			<td align='center'>".$page_acces."</td>";
									echo "		</tr>";
								}
							}
							
							echo "	</tbody>";
							echo "</table>";
							?>
						</div>
					</div>
				</div>
			</div>
			
			<?php
			}
			?>
		
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