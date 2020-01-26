<?php
session_start();

if (@$_SESSION["id_perso"]) {
	
	$id = $_SESSION["id_perso"];

	require_once "../fonctions.php";
	
	$mysqli = db_connexion();
	
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
				
				echo "<center><img src=\"carte_tmp/perso$id.png\"></center>"; 
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
					echo "<center><img src=\"carte_tmp/carte_sl$id.png\"></center>"; 
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
						echo "<center><img src=\"carte_tmp/carte$id.png\"></center>"; 
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
							
							echo "<center><img src=\"carte_tmp/carte_bataillon_sl$id.png\"></center>"; 
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
							
							echo "<center><img src=\"carte_tmp/carte_compagnie_sl$id.png\"></center>"; 
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
								
							echo "<center><img src=\"carte_tmp/carte$id.png\"></center>"; 
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
							
							echo "<center><img src=\"carte_tmp/carte$id.png\"></center>"; 
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
		
			echo "<center><img src=\"carte_tmp/carte$id.png\"></center>"; 
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
else
	echo "Veuillez vous connecter";?>
