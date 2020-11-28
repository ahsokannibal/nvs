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
	$anim = anim_perso($mysqli, $id_perso);
	
	if($anim){
		
		$mess_err 	= "";
		$mess 		= "";
		
		if (isset($_POST['select_batiment']) && $_POST['select_batiment'] != ""
			&& isset($_POST['coord_x_placement']) && trim($_POST['coord_x_placement']) != ""
			&& isset($_POST['coord_y_placement']) && trim($_POST['coord_y_placement']) != "") {
			
			$id_batiment = $_POST['select_batiment'];
			$x_carte_bat = $_POST['coord_x_placement'];
			$y_carte_bat = $_POST['coord_y_placement'];
			
			
			
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
		
			<p align="center">
			<?php
			if ($admin) {
				echo " <a class='btn btn-primary' href='admin_nvs.php'>Retour à l'administration</a>";
				echo " <a class='btn btn-primary' href='admin_batiments.php'>Retour à la gestion des batiments</a>";
			}
			else {
				echo " <a class='btn btn-primary' href='animation.php'>Retour à l'animation</a>";
				echo " <a class='btn btn-primary' href='anim_batiment.php'>Retour à la gestion des batiments</a>";
			}
			?>
				<a class="btn btn-primary" href="jouer.php">Retour au jeu</a>
			</p>
			 
		
			<div class="row">
				<div class="col-12">
				
					<h3>Création de batiments</h3>
					
					<form method='POST' action='anim_creer_batiment.php'>
						<select name="select_batiment">
							<option id='1'>Barricade</option>
							<option id='2'>Tour de visu</option>
							<option id='7'>Hopital</option>
							<option id='11'>Gare</option>
							<option id='8'>Fortin</option>
							<option id='9'>Fort</option>
						</select>
						<select name="select_verifications">
							<option id='1'>Aucune verification des contraintes</option>
							<option id='2'>Vérifier les contraintes</option>
						</select>
						<input type='text' value='' name='coord_x_placement' placeholder='x'>
						<input type='text' value='' name='coord_y_placement' placeholder='y'>
						<input type='submit' class='btn btn-success' value='créer'>
					</form>
					
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