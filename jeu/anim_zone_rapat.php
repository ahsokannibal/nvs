<?php
session_start();
require_once("../fonctions.php");
require_once("f_carte.php");

$mysqli = db_connexion();

include ('../nb_online.php');
$phpbb_root_path = '../forum/';
if (is_dir($phpbb_root_path))
{
	include ($phpbb_root_path .'config.php');
}

if(isset($_SESSION["id_perso"])){
	
	$id = $_SESSION['id_perso'];
	
	if (anim_perso($mysqli, $id)) {
		
		$mess_err 	= "";
		$mess 		= "";
		
		// Récupération du camp de l'animateur 
		$sql = "SELECT clan FROM perso WHERE id_perso='$id'";
		$res = $mysqli->query($sql);
		$t = $res->fetch_assoc();
		
		$camp = $t['clan'];
		
		if ($camp == '1') {
			$nom_camp = 'Nord';
			$b_camp = 'b';
		}
		else if ($camp == '2') {
			$nom_camp = 'Sud';
			$b_camp = 'r';
		}
		else if ($camp == '3') {
			$nom_camp = 'Indien';
			$b_camp = 'g';
		}
		
		$sql = "SELECT * FROM zone_respawn_camp WHERE id_camp='$camp'";
		$res = $mysqli->query($sql);
		$t = $res->fetch_assoc();
		
		$x_min_zone_def = $t['x_min_zone'];
		$x_max_zone_def = $t['x_max_zone'];
		$y_min_zone_def = $t['y_min_zone'];
		$y_max_zone_def = $t['y_max_zone'];
		
		if (isset($_POST['x_zone_min']) && trim($_POST['x_zone_min']) != ""
				&& isset($_POST['x_zone_max']) && trim($_POST['x_zone_max']) != ""
				&& isset($_POST['y_zone_min']) && trim($_POST['y_zone_min']) != ""
				&& isset($_POST['y_zone_max']) && trim($_POST['y_zone_max']) != "") {
			
			$x_zone_min = $_POST['x_zone_min'];
			$x_zone_max = $_POST['x_zone_max'];
			$y_zone_min = $_POST['y_zone_min'];
			$y_zone_max = $_POST['y_zone_max'];
			
			$verif_x_min = preg_match("#^[0-9]*[0-9]$#i","$x_zone_min");
			$verif_x_max = preg_match("#^[0-9]*[0-9]$#i","$x_zone_max");
			$verif_y_min = preg_match("#^[0-9]*[0-9]$#i","$y_zone_min");
			$verif_y_max = preg_match("#^[0-9]*[0-9]$#i","$y_zone_max");
			
			if ($verif_x_min && $verif_x_max && $verif_y_min && $verif_y_max) {
				
				$sql = "SELECT MAX(x_carte) as x_max, MAX(y_carte) as y_max FROM carte";
				$res = $mysqli->query($sql);
				$t = $res->fetch_assoc();
				
				$X_MAX 	= $t['x_max'];
				$Y_MAX  = $t['y_max'];
				
				if (in_map($x_zone_min, $y_zone_min, $X_MAX, $Y_MAX) && in_map($x_zone_max, $y_zone_max, $X_MAX, $Y_MAX)) {
					
					if (isset($x_min_zone_def)) {
						$sql = "UPDATE zone_respawn_camp SET x_min_zone='$x_zone_min', x_max_zone='$x_zone_max', y_min_zone='$y_zone_min', y_max_zone='$y_zone_max' WHERE id_camp='$camp'";
					}
					else {
						$sql = "INSERT INTO zone_respawn_camp (id_camp, x_min_zone, x_max_zone, y_min_zone, y_max_zone) VALUES ('$camp', '$x_zone_min', '$x_zone_max', '$y_zone_min', '$y_zone_max')";
					}
					$mysqli->query($sql);
					
					$x_min_zone_def = $x_zone_min;
					$x_max_zone_def = $x_zone_max;
					$y_min_zone_def = $y_zone_min;
					$y_max_zone_def = $y_zone_max;
					
					$mess .= "Zone de respawn du ".$nom_camp." mise à jour";
					
					$texte = addslashes($mess);
									
					// log_action_animation
					$sql = "INSERT INTO log_action_animation(date_acces, id_perso, page, action, texte) VALUES (NOW(), '$id', 'anim_zone_rapat.php', 'Modification zone de rapatriement', '$texte')";
					$mysqli->query($sql);
				}
				else {
					$mess_err .= "Coordonnées incorrectes : hors carte !";
				}
			}
			else {
				$mess_err .= "Coordonnées incorrectes : veuillez renseigner des entiers !";
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
						<h2>Zone de rapatriement pour le camp <?php echo $nom_camp; ?></h2>
						
						<font color='red'><?php echo $mess_err; ?></font>
						<font color='blue'><?php echo $mess; ?></font>
					</div>
				</div>
			</div>
		
			<p align="center"><a class="btn btn-primary" href="animation.php">Retour à l'animation</a> <a class="btn btn-primary" href="jouer.php">Retour au jeu</a></p>
			
			<div class="row">
				<div class="col-12">
					<div align="center">
						<font color='red'><b>La Zone de respawn correspond à la zone où les persos vont respawn si aucun bâtiment n'est disponible lors d'un rapatriement</b></font><br /><br />
						<?php						
						echo "<form method='POST' action='anim_zone_rapat.php'>";
						echo "	<input type='text' value='".$x_min_zone_def."' placeholder='x min' name='x_zone_min'>";
						echo "	<input type='text' value='".$x_max_zone_def."' placeholder='x max' name='x_zone_max'>";
						echo "	<input type='text' value='".$y_min_zone_def."' placeholder='y min' name='y_zone_min'>";
						echo "	<input type='text' value='".$y_max_zone_def."' placeholder='y max' name='y_zone_max'>";
						echo "	<input type='submit' value='Valider'>";
						echo "</form>";
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
