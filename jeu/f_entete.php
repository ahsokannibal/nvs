<?php

function entete($mysqli, $id) {
		
	if ($id) {
		// Perso
		if($id < 10000){
			$sql = "SELECT nom_perso, xp_perso, image_perso, clan FROM perso WHERE id_perso ='$id'";
			$result = $mysqli->query($sql);
			$tabAttr = $result->fetch_assoc();
			$nom_perso = $tabAttr['nom_perso'];
			$xp = $tabAttr['xp_perso'];
			$image_perso = $tabAttr['image_perso'];
			$clan_perso = $tabAttr["clan"];
			if($clan_perso == '1'){
				$couleur_clan_perso = 'blue';
				$nom_clan = 'Nord';
			}
			if($clan_perso == '2'){
				$couleur_clan_perso = 'red';
				$nom_clan = 'Sud';
			}
			
			// recuperation de l'id de la section 
			$sql_groupe = "SELECT id_section from perso_in_section where id_perso='$id' and attenteValidation_section='0'";
			$res_groupe = $mysqli->query($sql_groupe);
			$t_groupe = $res_groupe->fetch_assoc();
			$id_groupe = $t_groupe['id_section'];
												
			if(isset($id_groupe) && $id_groupe != ''){
				// recuperation des infos sur la section (dont le nom)
				$sql_groupe2 = "SELECT * FROM sections WHERE id_section='$id_groupe'";
				$res_groupe2 = $mysqli->query($sql_groupe2);
				$t_groupe2 = $res_groupe2->fetch_assoc();
				$groupe = addslashes($t_groupe2['nom_section']);
			}
			
			echo "<center>
					<img src=\"../images_perso/$image_perso\" width=\"40\" height=\"40\">
					<table border=\"1\">
						<tr>
							<td width=\"60%\"><b>Pseudo :</b> <font color=\"$couleur_clan_perso\">$nom_perso</font> [$id]</td>
						</tr>";
			echo "<tr><td><b>Xp :</b> $xp</td></tr>";
			echo "<tr><td><b>Camp :</b> <font color=\"$couleur_clan_perso\">$nom_clan</font></td></tr>";
			if(isset($groupe) && $groupe != ''){
				echo "<tr><td><b>Groupe :</b> <a href=\"section.php?id_section=$id_groupe&voir_groupe=ok\">". stripslashes($groupe) ."</a></td></tr>";
			}
			echo "</table></center>";
	
			echo "<center><table border=0>";
			echo "<tr><td><a href='evenement.php?infoid=$id'>Évènement</a>&nbsp;&nbsp;</td>";
			echo "<td><a href='cv.php?infoid=$id'>CV</a>&nbsp;&nbsp;</td>";
			echo "<td><a href='description.php?infoid=$id'>Description</a></td></tr></table></center><br>";
		}
		else {
			// PNJ
			if($id<50000) {
				$sql = "SELECT pnj.id_pnj,nom_pnj FROM pnj, instance_pnj WHERE instance_pnj.id_pnj=pnj.id_pnj AND idInstance_pnj=$id";
				$res = $mysqli->query($sql);
				$pnj = $res->fetch_assoc();
				$nom_pnj = $pnj["nom_pnj"];
				$id_pnj = $pnj["id_pnj"];
				$image_pnj = "monstre".$id_pnj."t.png";
				
				echo "<center><img src=\"../images_perso/$image_pnj\" width=\"40\" height=\"40\"><table border=\"1\"><tr><td width=\"60%\"><center>pnj : $nom_pnj [$id] </center></td></tr>";
				
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
				$id_batiment = $bat['id_batiment'];
				$nom_batiment = $bat['nom_batiment'];
				$description_batiment = $bat['description'];
				$nom_instance_batiment = $bat['nom_instance'];
				$pv_instance = $bat['pv_instance'];
				$pvMax_instance = $bat['pvMax_instance'];
				$camp_instance = $bat['camp_instance'];
				$contenance_instance = $bat['contenance_instance'];
				
				if($camp_instance == '1'){
					$couleur_camp_instance = 'blue';
					$nom_clan = 'Bleus';
				}
				if($camp_instance == '2'){
					$couleur_camp_instance = 'red';
					$nom_clan = 'Rouges';
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
	
	else 
		echo "<center>aucun perso selectionné</center>";
}
?>
