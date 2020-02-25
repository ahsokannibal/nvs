<?php

function entete($mysqli, $id) {
		
	if ($id) {
		
		// Perso
		if($id < 50000) {
			
			$sql = "SELECT nom_perso, xp_perso, idJoueur_perso, image_perso, bataillon, clan FROM perso WHERE id_perso ='$id'";
			$result = $mysqli->query($sql);
			$tabAttr = $result->fetch_assoc();
			
			$nom_perso 		= $tabAttr['nom_perso'];
			$xp 			= $tabAttr['xp_perso'];
			$image_perso 	= $tabAttr['image_perso'];
			$clan_perso 	= $tabAttr["clan"];
			$bataillon		= $tabAttr["bataillon"];
			$id_joueur		= $tabAttr["idJoueur_perso"];
			
			if($clan_perso == '1'){
				$couleur_clan_perso = 'blue';
				$nom_clan = 'Nord';
			}
			if($clan_perso == '2'){
				$couleur_clan_perso = 'red';
				$nom_clan = 'Sud';
			}
			
			// récupération du grade du perso 
			$sql_grade = "SELECT perso_as_grade.id_grade, nom_grade FROM perso_as_grade, grades WHERE perso_as_grade.id_grade = grades.id_grade AND id_perso='$id'";
			$res_grade = $mysqli->query($sql_grade);
			$t_grade = $res_grade->fetch_assoc();
				
			$id_grade_perso = $t_grade["id_grade"];
			$nom_grade_perso = $t_grade["nom_grade"];
			
			// cas particuliers grouillot
			if ($id_grade_perso == 101) {
				$id_grade_perso = "1.1";
			}
			if ($id_grade_perso == 102) {
				$id_grade_perso = "1.2";
			}
			
			// recuperation de l'id de la compagnie 
			$sql_groupe = "SELECT id_compagnie from perso_in_compagnie where id_perso='$id' AND (attenteValidation_compagnie='0' OR attenteValidation_compagnie='2')";
			$res_groupe = $mysqli->query($sql_groupe);
			$t_groupe = $res_groupe->fetch_assoc();
			$id_groupe = $t_groupe['id_compagnie'];
												
			if(isset($id_groupe) && $id_groupe != ''){
				// recuperation des infos sur la compagnie (dont le nom)
				$sql_groupe2 = "SELECT * FROM compagnies WHERE id_compagnie='$id_groupe'";
				$res_groupe2 = $mysqli->query($sql_groupe2);
				$t_groupe2 = $res_groupe2->fetch_assoc();
				$groupe = addslashes($t_groupe2['nom_compagnie']);
			}
			
			echo "<center>
					<div width=40 height=40 style=\"position: relative;\">
						<div style=\"position: absolute;bottom: 0;text-align: center; width: 100%;font-weight: bold;\">" . $id ."</div>
						<img src=\"../images_perso/" . $image_perso . "\" width=\"40\" height=\"40\">
					</div>
					<table border=\"1\">
						<tr>
							<td width=\"60%\"><b>Pseudo :</b> <font color=\"$couleur_clan_perso\">$nom_perso</font> [$id]</td>
						</tr>
						<tr>
							<td><b>Grade : </b><img src=\"../images/grades/" . $id_grade_perso . ".gif\" width=\"40\" height=\"40\">  " . $nom_grade_perso . "</td>
						</tr>";
			echo "<tr><td><b>Camp :</b> <font color=\"$couleur_clan_perso\">$nom_clan</font></td></tr>";
			
			echo "<tr>";
			echo "	<td><b>Bataillon :</b> <a href=\"bataillon.php?id_bataillon=$id_joueur\" target='_blank'>" . $bataillon . "</a></td>";
			echo "</tr>";
			
			if(isset($groupe) && $groupe != ''){
				echo "<tr><td><b>Compagnie :</b> <a href=\"compagnie.php?id_compagnie=$id_groupe&voir_compagnie=ok\">". stripslashes($groupe) ."</a></td></tr>";
			}
			echo "</table></center>";
	
			echo "<center><table border=0>";
			echo "<tr><td><a href='evenement.php?infoid=$id'>Évènement</a>&nbsp;&nbsp;</td>";
			echo "<td><a href='cv.php?infoid=$id'>CV</a>&nbsp;&nbsp;</td>";
			echo "<td><a href='description.php?infoid=$id'>Description</a></td></tr></table></center><br>";
		}
		else {
			// PNJ
			if($id >= 200000) {
				
				$sql = "SELECT pnj.id_pnj,nom_pnj FROM pnj, instance_pnj WHERE instance_pnj.id_pnj=pnj.id_pnj AND idInstance_pnj=$id";
				$res = $mysqli->query($sql);
				$pnj = $res->fetch_assoc();
				
				$nom_pnj = $pnj["nom_pnj"];
				$id_pnj = $pnj["id_pnj"];
				$image_pnj = "pnj".$id_pnj."t.png";
				
				echo "<center><img src=\"../images/pnj/$image_pnj\" width=\"40\" height=\"40\"><table border=\"1\"><tr><td width=\"60%\"><center>pnj : $nom_pnj [$id] </center></td></tr>";
				
				echo "<center><table border=0>";
				echo "<td><a href='evenement.php?infoid=$id'>Évènement</a>&nbsp;&nbsp;</td>";
				echo "<td><a href='cv.php?infoid=$id'>CV</a>&nbsp;&nbsp;</td>";
				echo "<td><a href='description.php?infoid=$id'>Description</a></td></tr></table></center><br>";
			}
			else {
				// Batiment
				$sql = "SELECT batiment.id_batiment, nom_batiment, description, nom_instance, pv_instance, pvMax_instance, camp_instance, contenance_instance 
						FROM batiment, instance_batiment
						WHERE batiment.id_batiment=instance_batiment.id_batiment
						AND id_instanceBat=$id";
				$res = $mysqli->query($sql);
				$bat = $res->fetch_assoc();
				
				$id_batiment 			= $bat['id_batiment'];
				$nom_batiment 			= $bat['nom_batiment'];
				$description_batiment 	= $bat['description'];
				$nom_instance_batiment 	= $bat['nom_instance'];
				$pv_instance 			= $bat['pv_instance'];
				$pvMax_instance 		= $bat['pvMax_instance'];
				$camp_instance 			= $bat['camp_instance'];
				$contenance_instance 	= $bat['contenance_instance'];
				
				if($camp_instance == '1'){
					$couleur_camp_instance = 'blue';
					$nom_clan = 'Nord';
				}
				if($camp_instance == '2'){
					$couleur_camp_instance = 'red';
					$nom_clan = 'Sud';
				}
				if($camp_instance == '3'){
					$couleur_camp_instance = 'purple';
					$nom_clan = 'Violets';
				}
				
				$image_bat = "b".$id_batiment."".$couleur_camp_instance[0].".png";
				
				//recup du camp du perso
				$id_perso = $_SESSION["id_perso"];
				
				$sql = "select clan from perso where id_perso='$id_perso'";
				$res = $mysqli->query($sql);
				$cp = $res->fetch_assoc();
				
				$camp_perso = $cp["clan"];
				
				echo "<center>";
				echo "<img src=\"../images_perso/$image_bat\" width=\"40\" height=\"40\">";
				echo "<table border=\"1\">";
				echo "  <tr><td width=\"60%\" align='center'>Batiment : $nom_batiment [$id]</td></tr>";
				echo "  <tr><td align='center'>Camp : <font color=\"$couleur_camp_instance\">$nom_clan</font></td></tr>";
				
				if($camp_perso == $camp_instance){
					echo "<tr><td align='center'>";
					$pourc = affiche_jauge($pv_instance, $pvMax_instance); 
					echo "".round($pourc)."% ou $pv_instance/$pvMax_instance PV</td></tr>";
				}
				
				echo "</table>";
				echo "</center>";
				
				echo "<center><table border=0>";
				if ($contenance_instance){
					echo "<tr><td><a href='evenement.php?infoid=$id&liste=ok'>Liste des persos</a>&nbsp;&nbsp;</td>";
				}
				echo "<td><a href='evenement.php?infoid=$id'>Evènement</a>&nbsp;&nbsp;</td>";
				
				if($id < 50000){
					echo "<td><a href='cv.php?infoid=$id'>CV</a>&nbsp;&nbsp;</td>";
				}
				
				echo "<td><a href='description.php?infoid=$id'>Description</a></td></tr></table></center><br>";
			}
		}	
	}
	else {
		echo "<center>aucun perso selectionné</center>";
	}
}
?>
