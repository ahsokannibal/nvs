<?php
session_start();
require_once("../fonctions.php");

$mysqli = db_connexion();

include ('../nb_online.php');

if(@$_SESSION["id_perso"]){	
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
		
		<style>
		div#table_bataillon{
			margin:10px auto;
			width:100%;
			min-width:300px;
			max-width:700px;
		}
		
		div#table_bataillon table{
			margin:0px;
			width:100%;
		}
		</style>
	</head>
	
	<body>
<?php

	if(isset($_GET["id_bataillon"])){
		
		// verifier que la valeur est valide
		$id_joueur = $_GET["id_bataillon"];
		$verif = preg_match("#^[0-9]*[0-9]$#i","$id_joueur");
		
		if($verif){
			
			// Récupération du nom du bataillon du joueur
			$sql = "SELECT bataillon FROM perso WHERE idJoueur_perso='$id_joueur' LIMIT 1";
			$res = $mysqli->query($sql);
			$t = $res->fetch_assoc();
			
			$nom_bataillon = $t['bataillon'];
?>
		<div class="container-fluid">
		
			<div class="row">
				<div class="col-12">

					<div align="center">
						<h2>Bataillon <?php echo $nom_bataillon; ?></h2>
					</div>
					
					<p align="center"><input type="button" value="Fermer cette fenêtre" onclick="window.close();"></p>

					<center>
						<div id="table_bataillon" class="table-responsive">
							<table border="1">
								<tr>
									<th>Nom perso [matricule]</th><th>Type d'unité</th><th>Grade</th>
								</tr>
<?php		
			// Récupération de la liste des persos du joueur 
			$sql = "SELECT perso.id_perso, nom_perso, grades.id_grade, nom_grade, nom_unite FROM perso, perso_as_grade, grades, type_unite
					WHERE perso.id_perso = perso_as_grade.id_perso 
					AND perso_as_grade.id_grade = grades.id_grade
					AND perso.type_perso = type_unite.id_unite
					AND idJoueur_perso='$id_joueur'";
			$res = $mysqli->query($sql);
			
			while ($t = $res->fetch_assoc()) {
				
				$id_perso	= $t['id_perso'];
				$nom_perso 	= $t['nom_perso'];
				$nom_grade 	= $t['nom_grade'];
				$nom_unite 	= $t['nom_unite'];
				$id_grade	= $t['id_grade'];
				
				// cas particuliers grouillot
				if ($id_grade == 101) {
					$id_grade = "1.1";
				}
				if ($id_grade == 102) {
					$id_grade = "1.2";
				}
				
				echo "<tr>";
				echo "	<td>";
				echo "		<a href=\"grades.php\" target='_blank'><img alt='". $nom_grade."' title='".$nom_grade."' src=\"../images/grades/" . $id_grade . ".gif\" width='40' height='40'></a>
							<a href=\"evenement.php?infoid=" . $id_perso . "\">". $nom_perso ." [" . $id_perso . "]";
				echo "	</td>";
				echo "	<td>" . $nom_unite . "</td>";
				echo "	<td>" . $nom_grade. "</td>";
				echo "</tr>";
			}

		} else {
			echo "<center><b>Erreur :</b> La valeur entrée n'est pas correcte !</center>";
		}
	}
?>
						
							</table>
						</div>
					</center>
		
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
else{
	echo "<font color=red>Vous ne pouvez pas accéder à cette page, veuillez vous loguer.</font>";
}
?>