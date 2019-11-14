<?php
session_start();
if (@$_SESSION["id_perso"]) {
	$id = $_SESSION["id_perso"];

	require_once "../fonctions.php";
	
	$mysqli = db_connexion();
	
	//dans la page qui doit afficher la carte:
	$requete = $mysqli->query("SELECT * FROM carte_time") or die (mysql_error());
	$sql = $requete->fetch_array ();
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
			if($_POST['Submit'] == "enlever le fond"){
				echo "<center><img src=\"carte_tmp/perso$id.png\"></center>"; 
				echo 'Mise a jour de la carte dans '.$Tpsrestant.'mn.';
			
				echo "<div align=\"center\"><br>";
				echo "Vous pouvez remettre le fond si vous le souhaitez<br>";
				echo "<form action=\"afficher_carte.php\" method=\"post\" name=\"avec_fond\">";
				echo "<input type=\"submit\" name=\"Submit\" value=\"remettre le fond\">";
				echo "</div>";
				echo "</form>";
			}
			else {
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
					if($_POST['Submit'] == "remettre la legende"){
						echo "<center><img src=\"carte_tmp/carte$id.png\"></center>"; 
						echo 'Mise a jour de la carte dans '.$Tpsrestant.'mn.';
					
						echo "<div align=\"center\"><br>";
						echo "Vous pouvez remettre la legende si vous le souhaitez<br>";
						echo "<form action=\"afficher_carte.php\" method=\"post\" name=\"avec_fond\">";
						echo "<input type=\"submit\" name=\"Submit\" value=\"enlever le fond\"><input type=\"submit\" name=\"Submit\" value=\"enlever la legende\">";
						echo "</div>";
						echo "</form>";
					}
					else {
						echo "<center><img src=\"carte_tmp/carte$id.png\"></center>"; 
						echo 'Mise a jour de la carte dans '.$Tpsrestant.'mn.';
					
						echo "<div align=\"center\"><br>";
						echo "Vous pouvez enlever le fond si vous le souhaitez<br>";
						echo "<form action=\"afficher_carte.php\" method=\"post\" name=\"ss_fond\">";
						echo "<input type=\"submit\" name=\"Submit\" value=\"enlever le fond\"><input type=\"submit\" name=\"Submit\" value=\"enlever la legende\">";
						echo "</div>";
						echo "</form>";
					}
				}
			}
		}
		else {
		
			echo "<center><img src=\"carte_tmp/carte$id.png\"></center>"; 
			echo 'Mise a jour de la carte dans '.$Tpsrestant.'mn.';
		
			echo "<div align=\"center\"><br>";
			echo "Vous pouvez enlever le fond si vous le souhaitez<br>";
			echo "<form action=\"afficher_carte.php\" method=\"post\" name=\"ss_fond\">";
			echo "<input type=\"submit\" name=\"Submit\" value=\"enlever le fond\"><input type=\"submit\" name=\"Submit\" value=\"enlever la legende\">";
			echo "</div>";
			echo "</form>";
			
		}
	}
}
else
	echo "Veuillez vous connecter";?>
