<?php
session_start();

//Pas de session en cours, on redirige vers l'accueil
if (!isset($_SESSION["id_perso"])) {
	header("location:../../index.php");
}
	
$id = $_SESSION["id_perso"];

require_once "../../fonctions.php";

$mysqli = db_connexion();

$page_acces = 'carte.php';
	
// acces_log
$sql = "INSERT INTO acces_log (date_acces, id_perso, page) VALUES (NOW(), '$id', '$page_acces')";
$mysqli->query($sql);
	
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<title>Nord VS Sud</title>
		
		<!-- Required meta tags -->
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		
		<!-- Bootstrap CSS -->
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

	</head>
	
	<body onload="addMouseChecker('carto', 'idInput', 'xy');">
		
		<p align="center"><a href="../jouer.php"> <input type="button" value="Retour au jeu"> </a></p>
		<div class="row">
			<div class="col-12" align='center'>
				<a href='histo_carte.php' class='btn btn-primary'>Afficher l'historique de la carte</a>
			</div>
		</div>
		<?php
			// Le perso appartient-il à une compagnie 
			$sql = "SELECT id_compagnie from perso_in_compagnie where id_perso='$id' AND (attenteValidation_compagnie='0' OR attenteValidation_compagnie='2')";
			$res = $mysqli->query($sql);
			$nb_compagnie = $res->num_rows;	
		?>
		<div class="grid">
			<div class="row">
				<div class="col d-flex justify-content-center">
					<h1>Carte Stratégique - Mon perso</h1>
				</div>
			</div>
			<div class="row">
				<div class="col d-flex justify-content-center">
					<input type='text' id='idInput' disabled />
				</div>
			</div>
			<div class="row">
				<div class="col d-flex justify-content-center">
					<img id='carto' src="<?='image_carte.php?imagename=carte'.$id.'.png';?>">
				</div>
			</div>
			<div class="row">
				<div class="col d-flex justify-content-center">
					<form action="carte.php" method="post" name="ss_fond">
						<input type="submit" name="Submit" value="Retirer la topographie"><br />
						<input type="submit" name="Submit" value="cercles sur mon bataillon">
						<?php if ($nb_compagnie) {
							echo "<input type=\"submit\" name=\"Submit\" value=\"cercles sur ma compagnie\">";
						} ?>
					</form>
				</div>
			</div>
		</div>
	<!--
	if(isset($_POST['Submit'])){
		
		// Enlever le fond
		if($_POST['Submit'] == "Retirer la topographie"){
			
			echo "<center><h1>Carte Stratégique - sans Topographie</h1></center>";
			
			echo "<center><input type='text' id='idInput' disabled /><br />";
			echo "<img id='carto' src=\"image_carte.php?imagename=perso$id.png\"></center>";
		
			echo "<div align=\"center\"><br>";
			echo "Vous pouvez remettre la topographie si vous le souhaitez<br>";
			echo "<form action=\"carte.php\" method=\"post\" name=\"avec_fond\">";
			echo "<input type=\"submit\" name=\"Submit\" value=\"Remettre la topographie\">";
			echo "</div>";
			echo "</form>";
		}
		else {
			
			// Enlever la legende
			if($_POST['Submit'] == "enlever la legende"){
				echo "<center><input type='text' id='idInput' disabled /><br />";
				echo "<img id='carto' src=\"image_carte.php?imagename=carte_sl$id.png\"></center>";
			
				echo "<div align=\"center\"><br>";
				echo "Vous pouvez remettre la legende si vous le souhaitez<br>";
				echo "<form action=\"carte.php\" method=\"post\" name=\"avec_fond\">";
				echo "<input type=\"submit\" name=\"Submit\" value=\"remettre la legende\">";
				echo "</div>";
				echo "</form>";
			}
			else {
				
				// Remettre la legende
				if($_POST['Submit'] == "remettre la legende"){
					echo "<center><input type='text' id='idInput' disabled /><br />";
					echo "<img id='carto' src=\"image_carte.php?imagename=carte$id.png\"></center>";
				
					echo "<div align=\"center\"><br>";
					echo "Vous pouvez remettre la legende si vous le souhaitez<br>";
					echo "<form action=\"carte.php\" method=\"post\" name=\"avec_fond\">";
					echo "<input type=\"submit\" name=\"Submit\" value=\"Retirer la topographie\">";
					echo "</div>";
					echo "</form>";
				}
				else {
					
					if ($_POST['Submit'] == "cercles sur mon bataillon") {
						
						echo "<center><h1>Carte Stratégique - Mon bataillon</h1></center>";
						
						echo "<center><input type='text' id='idInput' disabled /><br />";
						echo "<img id='carto' src=\"image_carte.php?imagename=carte_bataillon_sl$id.png\"></center>";
					
						echo "<div align=\"center\"><br>";
						echo "Vous pouvez enlever la topographie si vous le souhaitez<br>";
						echo "<form action=\"carte.php\" method=\"post\" name=\"ss_fond\">";
						echo "<input type=\"submit\" name=\"Submit\" value=\"Retirer la topographie\"><br />";
						echo "<input type=\"submit\" name=\"Submit\" value=\"cercles sur mon perso\">";
						if ($nb_compagnie) {
							echo "<input type=\"submit\" name=\"Submit\" value=\"cercles sur ma compagnie\">";
						}
						echo "</div>";
						echo "</form>";
					}
					else if ($_POST['Submit'] == "cercles sur ma compagnie" && $nb_compagnie) {
						
						echo "<center><h1>Carte Stratégique - Ma compagnie</h1></center>";
						
						echo "<center><input type='text' id='idInput' disabled /><br />";
						echo "<img id='carto' src=\"image_carte.php?imagename=carte_compagnie_sl$id.png\"></center>";
					
						echo "<div align=\"center\"><br>";
						echo "Vous pouvez enlever la topographie si vous le souhaitez<br>";
						echo "<form action=\"carte.php\" method=\"post\" name=\"ss_fond\">";
						echo "<input type=\"submit\" name=\"Submit\" value=\"Retirer la topographie\"><br />";
						echo "<input type=\"submit\" name=\"Submit\" value=\"cercles sur mon bataillon\">";
						echo "<input type=\"submit\" name=\"Submit\" value=\"cercles sur mon perso\">";
						echo "</div>";
						echo "</form>";
					}
					else if ($_POST['Submit'] == "cercles sur mon perso") {
						
						echo "<center><h1>Carte Stratégique - Mon perso</h1></center>";							
							
						echo "<center><input type='text' id='idInput' disabled /><br />";
						echo "<img id='carto' src=\"image_carte.php?imagename=carte$id.png\"></center>";
					
						echo "<div align=\"center\"><br>";
						echo "Vous pouvez enlever la topographie si vous le souhaitez<br>";
						echo "<form action=\"carte.php\" method=\"post\" name=\"ss_fond\">";
						echo "<input type=\"submit\" name=\"Submit\" value=\"Retirer la topographie\"><br />";
						echo "<input type=\"submit\" name=\"Submit\" value=\"cercles sur mon bataillon\">";
						if ($nb_compagnie) {
							echo "<input type=\"submit\" name=\"Submit\" value=\"cercles sur ma compagnie\">";
						}
						echo "</div>";
						echo "</form>";
					}
					else {
						echo "<center><h1>Carte Stratégique</h1></center>";
						
						echo "<center><input type='text' id='idInput' disabled /><br />";
						echo "<img id='carto' src=\"image_carte.php?imagename=carte$id.png\"></center>";
					
						echo "<div align=\"center\"><br>";
						echo "Vous pouvez enlever la topographie si vous le souhaitez<br>";
						echo "<form action=\"carte.php\" method=\"post\" name=\"ss_fond\">";
						echo "<input type=\"submit\" name=\"Submit\" value=\"Retirer la topographie\"><br />";
						echo "<input type=\"submit\" name=\"Submit\" value=\"cercles sur mon bataillon\">";
						if ($nb_compagnie) {
							echo "<input type=\"submit\" name=\"Submit\" value=\"cercles sur ma compagnie\">";
						}
						echo "</div>";
						echo "</form>";
					}
				}
			}
		}
	}
	else {			
		
		echo "<center><h1>Carte Stratégique - Mon perso</h1></center>";
	
		echo "<center><input type='text' id='idInput' disabled /><br />";
		echo "<img id='carto' src=\"image_carte.php?imagename=carte$id.png\"></center>";
	
		echo "<div align=\"center\"><br>";
		echo "Vous pouvez enlever la topographie si vous le souhaitez<br>";
		echo "<form action=\"carte.php\" method=\"post\" name=\"ss_fond\">";
		echo "<input type=\"submit\" name=\"Submit\" value=\"Retirer la topographie\"><br />";
		echo "<input type=\"submit\" name=\"Submit\" value=\"cercles sur mon bataillon\">";
		if ($nb_compagnie) {
			echo "<input type=\"submit\" name=\"Submit\" value=\"cercles sur ma compagnie\">";
		}
		echo "</div>";
		echo "</form>";
		
	}*/-->

		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
		<script src="carte.js"></script>
	</body>
</html>	
