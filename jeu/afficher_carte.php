<?php
session_start();

if (@$_SESSION["id_perso"]) {
	
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
			
			pos['x'] 	= Math.floor((e.clientX - imgId.offsetLeft) / 3);
			pos['y'] 	= Math.ceil((600 - (e.clientY - imgId.offsetTop)) / 3);
			pos['xy'] 	= pos['x'] +','+ pos['y'];
		   
			inputId.value = pos[valueToShow];
		}
		</script>
<?php	
	//dans la page qui doit afficher la carte:
	$requete = $mysqli->query("SELECT * FROM carte_time");
	$sql = $requete->fetch_array();
	$timerefresh = $sql['timerefresh'];
					  
	$Tpsrestant = Floor(($timerefresh-time())/60);
					  
	if ($Tpsrestant <=0)
	{
		$timerefresh = time()+60*5;
		$mysqli->query("UPDATE carte_time Set timerefresh='$timerefresh'") or die (mysql_error());
		echo 'Mise à jour de l\'historique de la carte, veuillez patienter. Merci....';
		echo '<meta http-equiv="refresh" content="1;URL=carte.php">';
	}
	else {
		if(isset($_POST['Submit'])){
			
			// Enlever le fond
			if($_POST['Submit'] == "Retirer la topographie"){
				
				echo "<center><h1>Carte Stratégique - sans Topographie</h1></center>";
				
				echo "<center><input type='text' id='idInput' disabled /><br /><img id='carto' src=\"carte_tmp/perso$id.png\"></center>"; 
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
					echo "<center><input type='text' id='idInput' disabled /><br /><img id='carto' src=\"carte_tmp/carte_sl$id.png\"></center>"; 
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
						echo "<center><input type='text' id='idInput' disabled /><br /><img id='carto' src=\"carte_tmp/carte$id.png\"></center>"; 
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
							
							echo "<center><input type='text' id='idInput' disabled /><br /><img id='carto' src=\"carte_tmp/carte_bataillon_sl$id.png\"></center>"; 
							echo 'Mise a jour de la carte dans '.$Tpsrestant.'mn.';
						
							echo "<div align=\"center\"><br>";
							echo "Vous pouvez enlever la topographie si vous le souhaitez<br>";
							echo "<form action=\"afficher_carte.php\" method=\"post\" name=\"ss_fond\">";
							echo "<input type=\"submit\" name=\"Submit\" value=\"Retirer la topographie\"><br />";
							echo "<input type=\"submit\" name=\"Submit\" value=\"cercles sur mon perso\">";
							echo "<input type=\"submit\" name=\"Submit\" value=\"cercles sur ma compagnie\">";
							echo "</div>";
							echo "</form>";
						}
						else if ($_POST['Submit'] == "cercles sur ma compagnie") {
							
							echo "<center><h1>Carte Stratégique - Ma compagnie</h1></center>";
							
							echo "<center><input type='text' id='idInput' disabled /><br /><img id='carto' src=\"carte_tmp/carte_compagnie_sl$id.png\"></center>"; 
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
								
							echo "<center><input type='text' id='idInput' disabled /><br /><img id='carto' src=\"carte_tmp/carte$id.png\"></center>"; 
							echo 'Mise a jour de la carte dans '.$Tpsrestant.'mn.';
						
							echo "<div align=\"center\"><br>";
							echo "Vous pouvez enlever la topographie si vous le souhaitez<br>";
							echo "<form action=\"afficher_carte.php\" method=\"post\" name=\"ss_fond\">";
							echo "<input type=\"submit\" name=\"Submit\" value=\"Retirer la topographie\"><br />";
							echo "<input type=\"submit\" name=\"Submit\" value=\"cercles sur mon bataillon\">";
							echo "<input type=\"submit\" name=\"Submit\" value=\"cercles sur ma compagnie\">";
							echo "</div>";
							echo "</form>";
						}
						else {
							echo "<center><h1>Carte Stratégique</h1></center>";
							
							echo "<center><input type='text' id='idInput' disabled /><br /><img id='carto' src=\"carte_tmp/carte$id.png\"></center>"; 
							echo 'Mise a jour de la carte dans '.$Tpsrestant.'mn.';
						
							echo "<div align=\"center\"><br>";
							echo "Vous pouvez enlever la topographie si vous le souhaitez<br>";
							echo "<form action=\"afficher_carte.php\" method=\"post\" name=\"ss_fond\">";
							echo "<input type=\"submit\" name=\"Submit\" value=\"Retirer la topographie\"><br />";
							echo "<input type=\"submit\" name=\"Submit\" value=\"cercles sur mon bataillon\">";
							echo "<input type=\"submit\" name=\"Submit\" value=\"cercles sur ma compagnie\">";
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
			echo "<img id='carto' src=\"carte_tmp/carte$id.png\"></center>"; 
			echo 'Mise a jour de la carte dans '.$Tpsrestant.'mn.';
		
			echo "<div align=\"center\"><br>";
			echo "Vous pouvez enlever la topographie si vous le souhaitez<br>";
			echo "<form action=\"afficher_carte.php\" method=\"post\" name=\"ss_fond\">";
			echo "<input type=\"submit\" name=\"Submit\" value=\"Retirer la topographie\"><br />";
			echo "<input type=\"submit\" name=\"Submit\" value=\"cercles sur mon bataillon\">";
			echo "<input type=\"submit\" name=\"Submit\" value=\"cercles sur ma compagnie\">";
			echo "</div>";
			echo "</form>";
			
		}
	}
	?>
	</body>
</html>	
	<?php
}
else {
	echo "Veuillez vous connecter";	
}
?>
