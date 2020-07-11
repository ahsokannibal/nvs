<?php
session_start();

if (isset($_SESSION["id_perso"])) {
	
	$id = $_SESSION["id_perso"];

	require_once "../fonctions.php";
	
	$mysqli = db_connexion();
	
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
	
	<body onload="addMouseChecker('carto', 'idInput', 'xy');">
		<script>		
		function addMouseChecker(imgId, inputId, valueToShow) {
			
			imgId 	= document.getElementById(imgId);
			inputId = document.getElementById(inputId);
			   
			if (imgId.addEventListener) {
				imgId.addEventListener('mousemove', function(e){checkMousePos(imgId, inputId, valueToShow, e);}, false);
			} else if (imgId.attachEvent) {
				imgId.attachEvent('onclick', function(e){checkMousePos(imgId, inputId, valueToShow, e);});
			}
		}
		
		function checkMousePos(imgId, inputId, valueToShow, e) {
			
			var pos = [];
			
			pos['x'] 	= Math.floor((e.pageX - imgId.offsetLeft) / 3);
			pos['y'] 	= Math.ceil((600 - (e.pageY - imgId.offsetTop)) / 3);
			pos['xy'] 	= pos['x'] +','+ pos['y'];
		   
			inputId.value = pos[valueToShow];
		}
		</script>
	</body>
</html>	
	<?php
		
	// Le perso appartient-il à une compagnie 
	$sql = "SELECT id_compagnie from perso_in_compagnie where id_perso='$id' AND (attenteValidation_compagnie='0' OR attenteValidation_compagnie='2')";
	$res = $mysqli->query($sql);
	$nb_compagnie = $res->num_rows;		
	
	if(isset($_POST['Submit'])){
		
		// Enlever le fond
		if($_POST['Submit'] == "Retirer la topographie"){
			
			echo "<center><h1>Carte Stratégique - sans Topographie</h1></center>";
			
			echo "<center><input type='text' id='idInput' disabled /><br />";
			echo "<img id='carto' src=\"image_carte.php?imagename=perso$id.png\"></center>"; 
			echo 'Mise a jour de la carte dans '.$Tpsrestant.'mn.';
		
			echo "<div align=\"center\"><br>";
			echo "Vous pouvez remettre la topographie si vous le souhaitez<br>";
			echo "<form action=\"afficher_carte.php\" method=\"post\" name=\"avec_fond\">";
			echo "<input type=\"submit\" name=\"Submit\" value=\"Remettre la topographie\">";
			echo "</div>";
			echo "</form>";
		}
		else {
			
			// Enlever la legende
			if($_POST['Submit'] == "enlever la legende"){
				echo "<center><input type='text' id='idInput' disabled /><br />";
				echo "<img id='carto' src=\"image_carte.php?imagename=carte_sl$id.png\"></center>"; 
				echo 'Mise a jour de la carte dans '.$Tpsrestant.'mn.';
			
				echo "<div align=\"center\"><br>";
				echo "Vous pouvez remettre la legende si vous le souhaitez<br>";
				echo "<form action=\"afficher_carte.php\" method=\"post\" name=\"avec_fond\">";
				echo "<input type=\"submit\" name=\"Submit\" value=\"remettre la legende\">";
				echo "</div>";
				echo "</form>";
			}
			else {
				
				// Remettre la legende
				if($_POST['Submit'] == "remettre la legende"){
					echo "<center><input type='text' id='idInput' disabled /><br />";
					echo "<img id='carto' src=\"image_carte.php?imagename=carte$id.png\"></center>"; 
					echo 'Mise a jour de la carte dans '.$Tpsrestant.'mn.';
				
					echo "<div align=\"center\"><br>";
					echo "Vous pouvez remettre la legende si vous le souhaitez<br>";
					echo "<form action=\"afficher_carte.php\" method=\"post\" name=\"avec_fond\">";
					echo "<input type=\"submit\" name=\"Submit\" value=\"Retirer la topographie\">";
					echo "</div>";
					echo "</form>";
				}
				else {
					
					if ($_POST['Submit'] == "cercles sur mon bataillon") {
						
						echo "<center><h1>Carte Stratégique - Mon bataillon</h1></center>";
						
						echo "<center><input type='text' id='idInput' disabled /><br />";
						echo "<img id='carto' src=\"image_carte.php?imagename=carte_bataillon_sl$id.png\"></center>"; 
						echo 'Mise a jour de la carte dans '.$Tpsrestant.'mn.';
					
						echo "<div align=\"center\"><br>";
						echo "Vous pouvez enlever la topographie si vous le souhaitez<br>";
						echo "<form action=\"afficher_carte.php\" method=\"post\" name=\"ss_fond\">";
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
						echo 'Mise a jour de la carte dans '.$Tpsrestant.'mn.';
					
						echo "<div align=\"center\"><br>";
						echo "Vous pouvez enlever la topographie si vous le souhaitez<br>";
						echo "<form action=\"afficher_carte.php\" method=\"post\" name=\"ss_fond\">";
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
						echo 'Mise a jour de la carte dans '.$Tpsrestant.'mn.';
					
						echo "<div align=\"center\"><br>";
						echo "Vous pouvez enlever la topographie si vous le souhaitez<br>";
						echo "<form action=\"afficher_carte.php\" method=\"post\" name=\"ss_fond\">";
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
						echo 'Mise a jour de la carte dans '.$Tpsrestant.'mn.';
					
						echo "<div align=\"center\"><br>";
						echo "Vous pouvez enlever la topographie si vous le souhaitez<br>";
						echo "<form action=\"afficher_carte.php\" method=\"post\" name=\"ss_fond\">";
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
		echo "<form action=\"afficher_carte.php\" method=\"post\" name=\"ss_fond\">";
		echo "<input type=\"submit\" name=\"Submit\" value=\"Retirer la topographie\"><br />";
		echo "<input type=\"submit\" name=\"Submit\" value=\"cercles sur mon bataillon\">";
		if ($nb_compagnie) {
			echo "<input type=\"submit\" name=\"Submit\" value=\"cercles sur ma compagnie\">";
		}
		echo "</div>";
		echo "</form>";
		
	}
}
else {
	echo "Veuillez vous connecter";	
}
?>
