<?php
session_start();
require_once("../fonctions.php");

$mysqli = db_connexion();

include ('../nb_online.php');
include ('../forum/config.php');

if (@$_SESSION["id_perso"]) {
	
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
		
		if(isset($_GET["id_compagnie"])) {
			
			$id_compagnie = $_GET["id_compagnie"];
			
			$verif1 = preg_match("#^[0-9]+$#i",$id_compagnie);
			
			if($verif1){
			
				// verification que le perso est bien le chef de la compagnie (anti-triche)
				$sql = "SELECT poste_compagnie FROM perso_in_compagnie WHERE id_perso=$id AND id_compagnie=$id_compagnie";
				$res = $mysqli->query($sql);
				$ch = $res->fetch_assoc();
				
				$ok_chef = $ch["poste_compagnie"];
				
				if($ok_chef == 1) {
		
					$mess_err 	= "";
					$mess		= "";

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
						<h2>Gestion des sections de la compagnie</h2>
						
						<center><font color='red'><?php echo $mess_err; ?></font></center>
						<center><font color='blue'><?php echo $mess; ?></font></center>
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
				else {
					// Tentative d'accès à cette page sans être le chef de la compagnie
					echo "<font color = red>Vous n'avez pas le droit d'acceder à cette page !</font>";
			
					$text_triche = "Tentative accés page section compagnie [$id_compagnie] sans y avoir les droits";
					
					$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id', '$text_triche')";
					$mysqli->query($sql);
				}
			}
			else {
				// Tentative modification param id compagnie
				$text_triche = "Tentative modification param id compagnie sur la page de gestion des sections";
					
				$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id', '$text_triche')";
				$mysqli->query($sql);
			}
		}
		else {
			// id compagnie obligatoire
			
		}
	}
}
else{
	echo "<font color=red>Vous ne pouvez pas acceder a cette page, veuillez vous logguer.</font>";
}?>