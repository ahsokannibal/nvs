<?php
session_start();
require_once("../fonctions.php");

$mysqli = db_connexion();

include ('../nb_online.php');
include ('../forum/config.php');

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
						<h2>Tableau des Multis déclarés</h2>
						
						<font color='red'><?php echo $mess_err; ?></font>
						<font color='blue'><?php echo $mess; ?></font>
					</div>
				</div>
			</div>
		
			<p align="center"><a class="btn btn-primary" href="admin_nvs.php">Retour à l'administration</a> <a class="btn btn-primary" href="jouer.php">Retour au jeu</a></p>
			
			<?php
			if (isset($_GET['detail_id']) && trim($_GET['detail_id']) != "") {
				
				$id_declaration = $_GET['detail_id'];
			?>
			<div class="row">
				<div class="col-12">
					<div align="center">
					<?php
					$sql = "SELECT situation FROM declaration_multi WHERE id_declaration='$id_declaration'";
					$res = $mysqli->query($sql);
					$t = $res->fetch_assoc();
					
					$situation = stripslashes($t['situation']);
					
					echo $situation;
					?>
					</div>
				</div>
			</div>
			<?php
			}
			?>
			
			<br />
			
			<div class="row">
				<div class="col-12">
					<div align="center">					
						<div id="table_batiments" class="table-responsive">	
					
							<?php
							$sql = "SELECT * FROM declaration_multi ORDER BY id_perso ASC";
							$res = $mysqli->query($sql);
							
							echo "<table class='table'>";
							echo "	<thead>";
							echo "		<tr>";
							echo "			<th style='text-align:center'>Perso qui déclare</th>";
							echo "			<th style='text-align:center'>Multi</th>";
							echo "			<th style='text-align:center'>Action</th>";
							echo "		</tr>";
							echo "	</thead>";
							echo "	<tbody>";
							
							while ($t = $res->fetch_assoc()) {
								
								$id_decla	= $t['id_declaration'];
								$id_perso	= $t['id_perso'];
								$id_multi	= $t['id_multi'];
								
								// récup infos perso qui babysitte
								$sql_p = "SELECT nom_perso, clan FROM perso WHERE id_perso='$id_perso'";
								$res_p = $mysqli->query($sql_p);
								$t_p = $res_p->fetch_assoc();

								$nom_perso 	= $t_p['nom_perso'];
								$camp_perso	= $t_p['clan'];
								
								// récup infos perso multi
								$sql_m = "SELECT nom_perso, clan FROM perso WHERE id_perso='$id_multi'";
								$res_m = $mysqli->query($sql_m);
								$t_m = $res_m->fetch_assoc();

								$nom_multi 	= $t_m['nom_perso'];
								$camp_multi	= $t_m['clan'];
								
								echo "		<tr>";
								echo "			<td align='center'>".$nom_perso." [".$id_perso."]</td>";
								echo "			<td align='center'>".$nom_multi." [".$id_multi."]</td>";
								echo "			<td align='center'><a href='admin_multi.php?detail_id=".$id_decla."' class='btn btn-primary'>Consulter le détail</a></td>";
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