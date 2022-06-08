<?php
session_start();
require_once("../fonctions.php");

$mysqli = db_connexion();

include ('../nb_online.php');

if(isset($_SESSION["id_perso"])){
	
	//recuperation des variables de sessions
	$id = $_SESSION["id_perso"];
	
	$mess 		= "";
	$mess_err 	= "";
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
		$id_joueur_bat = $_GET["id_bataillon"];
		$verif = preg_match("#^[0-9]*[0-9]$#i","$id_joueur_bat");
		
		if($verif){
			
			// récupération de l'id joueur du perso connecté 
			$sql = "SELECT idJoueur_perso FROM perso WHERE id_perso='$id'";
			$res = $mysqli->query($sql);
			$t = $res->fetch_assoc();
			
			$id_joueur_perso = $t['idJoueur_perso'];
			
			if (isset($_POST["enregistrer"]) && trim($_POST['nomBataillon']) != "") {
				
				if ($id_joueur_perso == $id_joueur_bat) {
				
					$nouveau_nom_bataillon = addslashes($_POST['nomBataillon']);
					
					$sql = "INSERT INTO perso_demande_anim (id_perso, type_demande, info_demande) VALUES ('$id_joueur_bat', '3', '$nouveau_nom_bataillon')";
					$mysqli->query($sql);
					
					$mess .= "Demande de changement de nom de bataillon en '".$_POST['nomBataillon']."' tranmis avec succès.";
				}
				else {
					// Tentative de triche !
					$text_triche = "Le perso $id (joueur $id_joueur_perso) a essayé de changer le nom du bataillon du joueur $id_joueur_bat !";
					
					$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id', '$text_triche')";
					$mysqli->query($sql);
				}
			}
			
			// Récupération du nom du bataillon du joueur
			$sql = "SELECT bataillon FROM perso WHERE idJoueur_perso='$id_joueur_bat' LIMIT 1";
			$res = $mysqli->query($sql);
			$t = $res->fetch_assoc();
			
			$nom_bataillon = $t['bataillon'];
?>
		<div class="container-fluid">
		
			<div class="row">
				<div class="col-12">

					<div align="center">
						<h2>Bataillon <?php echo $nom_bataillon; ?></h2><br />
						<?php
						if (trim($mess) != "") {
							echo "<font color='blue'>".$mess."</font>";
						}
						
						if (trim($mess_err) != "") {
							echo "<font color='red'>".$mess_err."</font>";
						}
						?>
					</div>
					
					<p align="center"><a href="jouer.php"> <input type="button" value="Retour au jeu"> </a></p>
					
					<div align='center'>
						
						<?php
						if ($id_joueur_perso == $id_joueur_bat) {
							if (isset($_GET['changer_nom']) && $_GET['changer_nom'] == 'ok') {
								echo "<form method='POST' action='bataillon.php?id_bataillon=".$id_joueur_bat."'>";
								echo "	<div class='form-group col-6'>";
								echo "		<label for='nomBataillon'>Nouveau nom du bataillon</label>";
								echo "		<input type='text' class='form-control' id='nomBataillon' name='nomBataillon' maxlength='100'>";
								echo "	</div>";
								echo "	<div class='form-group col-6'>";
								echo "		<input type='submit' class='btn btn-success' name='enregistrer' value='valider le changement de nom'>";
								echo "	</div>";
								echo "</form>";
							}
							else {
								echo "<a href='bataillon.php?id_bataillon=".$id_joueur_bat."&changer_nom=ok' class='btn btn-warning'>Demander à changer le nom du bataillon</a>";
							}
						}
						?>
					</div>

					<center>
						<div id="table_bataillon" class="table-responsive">
							<table border="1">
								<tr>
									<th style='text-align:center'>Nom perso [matricule]</th><th style='text-align:center'>Type d'unité</th><th style='text-align:center'>Grade</th>
								</tr>
<?php		
			// Récupération de la liste des persos du joueur 
			$sql = "SELECT perso.id_perso, nom_perso, grades.id_grade, nom_grade, nom_unite FROM perso, perso_as_grade, grades, type_unite
					WHERE perso.id_perso = perso_as_grade.id_perso 
					AND perso_as_grade.id_grade = grades.id_grade
					AND perso.type_perso = type_unite.id_unite
					AND idJoueur_perso='$id_joueur_bat'";
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