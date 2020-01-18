<?php
session_start();
require_once("../fonctions.php");

$mysqli = db_connexion();

include ('../nb_online.php');

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
	
		<div class="container-fluid">
		
			<div class="row">
				<div class="col-12">
				
					<p align="center"><input type="button" value="Fermer cette fenêtre" onclick="window.close();"></p>

					<div align="center">
						<h2>Grades</h2>
					</div>
				</div>
			</div>
		
<?php
	if(@$_SESSION["id_perso"]){
		
		$id_perso = $_SESSION["id_perso"];
		
		// Récupération du grade du perso
		$sql_grade = "SELECT perso_as_grade.id_grade, nom_grade, chef FROM perso, perso_as_grade, grades 
						WHERE perso_as_grade.id_grade = grades.id_grade
						AND perso.id_perso = perso_as_grade.id_perso
						AND perso_as_grade.id_perso='$id_perso'";
		$res_grade = $mysqli->query($sql_grade);
		$t_grade = $res_grade->fetch_assoc();
				
		$id_grade_perso 	= $t_grade["id_grade"];
		$nom_grade_perso 	= $t_grade["nom_grade"];
		$chef_perso			= $t_grade["chef"];
		
		// cas particuliers grouillot
		if ($id_grade_perso == 101) {
			$id_grade_perso = "1.1";
		}
		if ($id_grade_perso == 102) {
			$id_grade_perso = "1.2";
		}
?>		
			<div class="row">
				<div class="col-12">

					<div align="center">
						<p><b><u>Votre grade :</u></b>&nbsp;&nbsp;<img alt="<?php echo $nom_grade_perso; ?>" title="<?php echo $nom_grade_perso; ?>" src="../images/grades/<?php echo $id_grade_perso . ".gif";?>" width="40" height="40" class="img-fluid"> <?php echo $nom_grade_perso; ?></p>
					</div>
				</div>
			</div>
			
<?php		
	}
	
	// grades chef
	$sql = "SELECT * FROM grades WHERE pc_grade != 0 ORDER BY id_grade";
	$res = $mysqli->query($sql);
	
	echo "<div class=\"row\">";
	echo "	<div class=\"col-12\">";
	
	echo "		<center>";
	echo "			<div id=\"table_grade_chef\" class=\"table-responsive\">";
	echo "				<table border='1'>";
	
	if ($chef_perso) {
	
		echo "					<tr>";
		echo "						<th>Image grade</th><th>Nom grade</th><th>PC requis</th><th>Points de grouillot</th>";
		echo "					</tr>";
		
		$affichage_pc_grade = "???";
		
		while ($t = $res->fetch_assoc()) {
			
			$id_grade 	= $t['id_grade'];
			$nom_grade 	= $t['nom_grade'];
			$pc_grade 	= $t['pc_grade'];
			$pg_grade 	= $t['point_armee_grade'];
			
			if ($id_grade_perso + 1 >= $id_grade) {
				$affichage_pc_grade = $pc_grade;
			} else {
				$affichage_pc_grade = "???";
			}
			
			echo "				<tr>";
			echo "					<td align='center'><img alt=\"" . $nom_grade ."\" title=\"" . $nom_grade . "\" src=\"../images/grades/" . $id_grade . ".gif\" width='40' height='40' class='img-fluid'></td>";
			echo "					<td align='center'>" . $nom_grade . "</td>";
			echo "					<td align='center'>" . $affichage_pc_grade . "</td>";
			echo "					<td align='center'>" . $pg_grade . "</td>";
			echo "				</tr>";

		}
	}
	else {
		
		echo "					<tr>";
		echo "						<th>Image grade</th><th>Nom grade</th><th>XP requis</th>";
		echo "					</tr>";
		
		// grades grouillots
		$sql = "SELECT * FROM grades WHERE pc_grade = 0 ORDER BY id_grade";
		$res = $mysqli->query($sql);
		
		$xp_grade = 0;
		
		while ($t = $res->fetch_assoc()) {
			
			$id_grade 	= $t['id_grade'];
			$nom_grade 	= $t['nom_grade'];
			
			if ($id_grade == 101) {
				$id_grade = "1.1";
				$xp_grade = 500;
			}
			
			if ($id_grade == 102) {
				$id_grade = "1.2";
				$xp_grade = 1500;
			}
			
			echo "				<tr>";
			echo "					<td align='center'><img alt=\"" . $nom_grade ."\" title=\"" . $nom_grade . "\" src=\"../images/grades/" . $id_grade . ".gif\" width='40' height='40' class='img-fluid'></td>";
			echo "					<td align='center'>" . $nom_grade . "</td>";
			echo "					<td align='center'>" . $xp_grade . "</td>";
			echo "				</tr>";
		}
		
	}
	
	echo "				</table>";
	echo "			</div>";
	echo "		</center>";
	echo "	</div>";
	echo "</div>";
	
	
	
?>
			
		</div>
		
		<!-- Optional JavaScript -->
		<!-- jQuery first, then Popper.js, then Bootstrap JS -->
		<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
		<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
	</body>
</html>