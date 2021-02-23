<?php
session_start();
require_once("../fonctions.php");

$mysqli = db_connexion();

include ('../nb_online.php');
include ('../forum/config.php');

if(isset($_SESSION["id_perso"])){
	
	$id_perso = $_SESSION['id_perso'];
	
	// recupération config jeu
	$admin 	= admin_perso($mysqli, $id_perso);
	
	if($admin) {
		
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
						<h2>Logs Animation</h2>
						
						<font color='red'><?php echo $mess_err; ?></font>
						<font color='blue'><?php echo $mess; ?></font>
					</div>
				</div>
			</div>
		
			<p align="center"><a class="btn btn-primary" href="admin_nvs.php">Retour à l'administration</a> <a class="btn btn-primary" href="jouer.php">Retour au jeu</a></p>
			
			
			<div class="row">
				<div class="col-12">
					<table class='table'>
						<thead>
							<tr>
								<th style='text-align:center'>Animateur</th>
								<th style='text-align:center'>Date</th>
								<th style='text-align:center'>Page</th>
								<th style='text-align:center'>Action</th>
								<th style='text-align:center'>Log</th>
							</tr>
						</thead>
						<tbody>
						<?php
						$sql = "SELECT * FROM log_action_animation ORDER BY id_acces DESC";
						$res = $mysqli->query($sql);
						
						while ($t = $res->fetch_assoc()) {
							
							$id_perso_log 	= $t['id_perso'];
							$date_log		= $t['date_acces'];
							$page_log		= $t['page'];
							$action_log		= $t['action'];
							$texte_log		= $t['texte'];
							
							$sql_p = "SELECT nom_perso, id_perso FROM perso WHERE chef='1' AND idJoueur_perso = (SELECT idJoueur_perso FROM perso WHERE id_perso='$id_perso_log')";
							$res_p = $mysqli->query($sql_p);
							$t_p = $res_p->fetch_assoc();
							
							$id_perso_anim	= $t_p['id_perso'];
							$nom_perso_anim	= $t_p['nom_perso'];
							
							echo "<tr>";
							echo "	<td align='center'>".$nom_perso_anim." [".$id_perso_anim."]</td>";
							echo "	<td align='center'>".$date_log."</td>";
							echo "	<td align='center'>".$page_log."</td>";
							echo "	<td align='center'>".$action_log."</td>";
							echo "	<td>".$texte_log."</td>";
							echo "</tr>";
						}
						?>
						</tbody>
					</table>
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