<?php
session_start();
require_once("../fonctions.php");

$mysqli = db_connexion();

include ('../nb_online.php');

// recupération config jeu
$dispo = config_dispo_jeu($mysqli);
$admin = admin_perso($mysqli, $_SESSION["id_perso"]);

if($dispo || $admin){
	
	if(isset($_SESSION["id_perso"])){
		
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
			
			$erreur = "";
			
			if(isset($_GET["id_compagnie"])){
			
				$id_compagnie = $_GET["id_compagnie"];
				
				$verif1 = preg_match("#^[0-9]+$#i",$id_compagnie);
			
				if($verif1){
				
					// verification que le perso appartient bien a la compagnie
					$sql = "SELECT id_compagnie FROM perso_in_compagnie WHERE id_perso='$id' AND id_compagnie='$id_compagnie' AND poste_compagnie=1";
					$res = $mysqli->query($sql);
					
					$verif = $res->num_rows;
					
					if($verif){
					
						$sql = "SELECT resume_compagnie FROM compagnies WHERE id_compagnie=$id_compagnie";
						$res = $mysqli->query($sql);
						$t = $res->fetch_assoc();
						
						$resume = $t["resume_compagnie"];
						
						if(isset($_POST["changer"])) {
							
							$resume = addslashes($_POST["resume"]);
							$sql = "UPDATE compagnies SET resume_compagnie='$resume' WHERE id_compagnie='$id_compagnie'";
							$mysqli->query($sql);
							
							header("Location:compagnie.php");
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
		
			<?php
			echo "<center><a href='admin_compagnie.php?id_compagnie=".$id_compagnie."' class='btn btn-info'>retour a la page d'administration de compagnie</a></center>";
			?>
	
			<div align="center">Sur cette page vous avez la possibilité de changer le resume de votre compagnie :<br>
			
				<form method="post" action="">
					<TEXTAREA cols="100" rows="1" name="resume">
<?php 
					if($resume == "") {
						echo "Aucun résumé";
					}
					else {
						echo "$resume";
					}
?>
					</TEXTAREA>
					<br/><input type="submit" name="changer" value="changer" class='btn btn-primary'>
					<br /><br /><a href='compagnie.php' class='btn btn-info'>retour a la page compagnie</a>
				</form>
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
						echo "<center>Vous n'appartenez pas à cette compagnie</center>";
						
						$text_triche = "Tentative accés page resume compagnie [$id_compagnie] sans faire partie de la compagnie !";
			
						$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id', '$text_triche')";
						$mysqli->query($sql);
					}
				}
				else {
					echo "<center>La compagnie demandé n'existe pas</center>";
					
					$param_test 	= addslashes($id_compagnie);
					$text_triche 	= "Test parametre sur page resume compagnie, parametre id_compagnie invalide tenté : $param_test";
						
					$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id', '$text_triche')";
					$mysqli->query($sql);
				}
			}
			else {
				echo "<center>Aucune compagnie spécifié</center>";
				
				$text_triche = "Tentative accés page resume compagnie sans paramètre";
			
				$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id', '$text_triche')";
				$mysqli->query($sql);
			}
		}
	}
	else{
		echo "<font color=red>Vous ne pouvez pas accéder à cette page, veuillez vous loguer.</font>";
	}?>
<?php
}
else {
	// logout
	$_SESSION = array(); // On ecrase le tableau de session
	session_destroy(); // On detruit la session
	
	header("Location: ../index2.php");
}
?>