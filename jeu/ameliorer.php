<?php
session_start();
require_once("../fonctions.php");
require_once("f_ameliore.php");

$mysqli = db_connexion();

include ('../nb_online.php');

// recupération config jeu
$dispo = config_dispo_jeu($mysqli);
$admin = admin_perso($mysqli, $_SESSION["id_perso"]);

if($dispo == '1' || $admin){

	if (isset($_SESSION["id_perso"])) {
		
		//recuperation des varaibles de sessions
		$id = $_SESSION["id_perso"];
		
		$sql = "SELECT pv_perso FROM perso WHERE id_perso='$id'";
		$res = $mysqli->query($sql);
		$tpv = $res->fetch_assoc();
		
		$testpv = $tpv['pv_perso'];
		
		if ($testpv <= 0) {
			echo "<font color=red>Vous êtes mort...</font>";
		}
		else {
			
			$sql = "SELECT pi_perso, pvMax_perso, pmMax_perso, paMax_perso, perception_perso, recup_perso, type_perso, chef FROM perso WHERE id_perso ='$id'";
			$res = $mysqli->query($sql);
			$tab = $res->fetch_assoc();
			
			$pm 	= $tab["pmMax_perso"];
			$pa 	= $tab["paMax_perso"];
			$per 	= $tab["perception_perso"];
			$pv 	= $tab["pvMax_perso"];
			$rec 	= $tab["recup_perso"];
			$pi 	= $tab["pi_perso"];
			$type	= $tab["type_perso"];
			$chef 	= $tab["chef"];
	
	?>
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
			<nav class="navbar navbar-expand-lg navbar-light">
				<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
					<span class="navbar-toggler-icon"></span>
				</button>
				<div class="collapse navbar-collapse" id="navbarSupportedContent">
					<ul class="navbar-nav mr-auto nav-pills">
						<li class="nav-item">
							<a class="nav-link" href="profil.php">Profil</a>
						</li>
						<li class="nav-item">
							<a class="nav-link active" href="#">Améliorer son perso</a>
						</li>
						<?php
						if($chef) {
							echo "<li class='nav-item'><a class='nav-link' href=\"recrutement.php\">Recruter des grouillots</a></li>";
							echo "<li class='nav-item'><a class='nav-link' href=\"gestion_grouillot.php\">Gérer ses grouillots</a></li>";
						}
						?>
						<li class="nav-item">
							<a class="nav-link" href="equipement.php">Equiper son perso</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" href="compte.php">Gérer son Compte</a>
						</li>
					</ul>
				</div>
			</nav>
			
			<hr>
		
			<br /><br /><center><h1>Améliorer ce perso</h1></center>
			
			<br /><br />
				
			<p align="center"><input type="button" value="Fermer cette fenêtre" onclick="window.close()"></p>
			<center><font color='red'>Attention, tout clic sur <b>>> monter</b> entraine une amélioration immédiate et irréversible</font></center>
		<?php
		if (isset($_POST["pv"])) {
			
			// calcul du nombre de pi necessaire
			$nbpi_pv = ameliore_pv($mysqli, $pv, $type);
			
			// verification que le perso a assez de pi
			if ($pi >= $nbpi_pv){
				
				$sql2 = "UPDATE perso SET pvMax_perso=pvMax_perso+1, pi_perso=pi_perso-$nbpi_pv WHERE id_perso ='$id'";
				$mysqli->query($sql2);
				
				$pv = $pv + 1;
				$pi = $pi - $nbpi_pv;
			}
			else {
				echo "<center><font color=red>Vous n'avez pas assez de pi</font></center>";
			}
		}
		
		if (isset($_POST["pm"])) {
			
			// calcul du nombre de pi necessaire
			$nbpi_pm = ameliore_pm($mysqli, $pm, $type);
			
			if ($type == 5 && $pm >= 6) {
				echo "<center><font color=red>Il est impossible d'améliorer plus les PM des unités d'artillerie</font></center>";
			}
			else {
		
				// verification que le perso a assez de pi
				if($pi >= $nbpi_pm) {
					
					$sql2 = "UPDATE perso SET pmMax_perso=pmMax_perso+1, pi_perso=pi_perso-$nbpi_pm WHERE id_perso ='$id'";
					$mysqli->query($sql2);
					
					$pm = $pm + 1;
					$pi = $pi - $nbpi_pm;
				}
				else {
					echo "<center><font color=red>Vous n'avez pas assez de pi</font></center>";
				}
			}
		}
		
		if (isset($_POST["pa"])) {
			
			// calcul du nombre de pi necessaire
			$nbpi_pa = ameliore_pa($mysqli, $pa, $type);
		
			// verification que le perso a assez de pi	
			if($pi >= $nbpi_pa) {
				
				$sql2 = "UPDATE perso SET paMax_perso=paMax_perso+1, pi_perso=pi_perso-$nbpi_pa WHERE id_perso ='$id'";
				$mysqli->query($sql2);
				
				$pa = $pa + 1;
				$pi = $pi - $nbpi_pa;
			}
			else {
				echo "<center><font color=red>Vous n'avez pas assez de pi</font></center>";
			}
		}
		
		if (isset($_POST["per"])) {
			
			// calcul du nombre de pi necessaire
			$nbpi_perc = ameliore_perc($mysqli, $per, $type);
		
			// verification que le perso a assez de pi	
			if($pi >= $nbpi_perc) {
				
				$sql2 = "UPDATE perso SET perception_perso=perception_perso+1, pi_perso=pi_perso-$nbpi_perc WHERE id_perso ='$id'";
				$mysqli->query($sql2);
				
				$per = $per + 1;
				$pi = $pi - $nbpi_perc;
			}
			else {
				echo "<center><font color=red>Vous n'avez pas assez de pi</font></center>";
			}
		}
		
		if (isset($_POST["rec"])) {
			
			// calcul du nombre de pi necessaire
			$nbpi_recup = ameliore_recup($mysqli, $rec, $type);
		
			// verification que le perso a assez de pi
			if($pi >= $nbpi_recup) {
				
				$sql2 = "UPDATE perso SET recup_perso=recup_perso+1, pi_perso=pi_perso-$nbpi_recup WHERE id_perso ='$id'";
				$mysqli->query($sql2);
				
				$rec = $rec + 1;
				$pi = $pi - $nbpi_recup;
			}
			else {
				echo "<center><font color=red>Vous n'avez pas assez de pi</font></center>";
			}
		}
		
		?>
			<form method="post" action="ameliorer.php">
				<br>
				<center>
				Points d'investissements : <input type="text" size="2" maxlength="5" value="<?php echo $pi ?>" disabled>
				</center>
				
				<table border="1" align="center"> 
				
					<tr>
						<td>Points de vie</td>
						<td>
							<input type="text" size="3" maxlength="3" value="<?php echo $pv; ?>" disabled>&nbsp;
							<input type="submit" name="pv" class='btn btn-success' value=">> monter">&nbsp;&nbsp;<?php echo "Cout : ".ameliore_pv($mysqli, $pv, $type); ?>
						</td>
					</tr>
					
					<tr>
						<td>Points de mouvement</td>
						<td>
							<input type="text" size="3" maxlength="3" value="<?php echo $pm; ?>" disabled>&nbsp;
							<?php
							if ($type == 5 && $pm >= 6) {
								echo "<b>Maximum atteint</b>";
							}
							else {
							?>
							<input type="submit" name="pm" class='btn btn-success' value=">> monter">&nbsp;&nbsp;<?php echo "Cout : ".ameliore_pm($mysqli, $pm, $type); ?>
							<?php 
							}
							?>
						</td>
					</tr>
					
					<tr>
						<td>Points d'action</td>
						<td>
							<input type="text" size="3" maxlength="3" value="<?php echo $pa; ?>" disabled>&nbsp;
							<input type="submit" name="pa" class='btn btn-success' value=">> monter">&nbsp;&nbsp;<?php echo "Cout : ".ameliore_pa($mysqli, $pa, $type); ?>
						</td>
					</tr>
					
					<tr>
						<td>Perception</td>
						<td>
							<input type="text" size="3" maxlength="3" value="<?php echo $per; ?>" disabled>&nbsp;
							<input type="submit" name="per" class='btn btn-success' value=">> monter">&nbsp;&nbsp;<?php echo "Cout : ".ameliore_perc($mysqli, $per, $type); ?>
						</td>
					</tr>
					
					<tr>
						<td>Recuperation</td>
						<td>
							<input type="text" size="3" maxlength="3" value="<?php echo $rec; ?>" disabled>&nbsp;
							<input type="submit" name="rec" class='btn btn-success' value=">> monter">&nbsp;&nbsp;<?php echo "Cout : ".ameliore_recup($mysqli, $rec, $type); ?>
						</td>
					</tr>
					
				</table>
			</form>
			
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
		echo "<font color=red>Vous ne pouvez pas accéder à cette page, veuillez vous loguer.</font>";
	}
	?>
<?php
}
else {
	// logout
	$_SESSION = array(); // On écrase le tableau de session
	session_destroy(); // On détruit la session
	
	header("Location:../index2.php");
}
?>