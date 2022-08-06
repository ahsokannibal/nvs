<?php

function afficher_infos_compagnie($nom_compagnie_perso, $image_compagnie_perso) {
	
	if (trim($nom_compagnie_perso) != "") {
		echo "<div>";
		if (trim($image_compagnie_perso) != "" && $image_compagnie_perso != "0") {
			echo "<img src='".$image_compagnie_perso."' width='20' height='20'>";
		}
		echo " <a href='compagnie.php' target='_blank'>" . stripslashes($nom_compagnie_perso) . "</a></div>";
	}
}


/**
 * Fonction permettant d'afficher les infos de popover dans le cas où on n'est ni dans un batiment, ni dans un train
 */
function afficher_infos_non_bat_non_train($fond_im, $nom_terrain, $nb_o) {
	
	echo "<div><img src='../fond_carte/".$fond_im."' width='20' height='20'> ".$nom_terrain." ";
										
	if ($nb_o) {
		echo "<img src='../fond_carte/o1.gif' width='20' height='20'> Objets à terre";
	}
	
	echo "</div>";
	
}

/**
 * Fonction permettant d'afficher les infos de popover dans le cas où on est dans un train
 */
function afficher_infos_in_train($mysqli, $id_perso) {
	
	$id_instance_in_train = in_train($mysqli,$id_perso);
										
	$sql_im = "SELECT instance_batiment.id_batiment, camp_instance, nom_instance, nom_batiment
					FROM instance_batiment, batiment 
					WHERE instance_batiment.id_batiment = batiment.id_batiment
					AND id_instanceBat='$id_instance_in_train'";
	$res_im = $mysqli->query($sql_im);
	$t_im = $res_im->fetch_assoc();
			
	$type_bat 	= $t_im["id_batiment"];
	$camp_bat 	= $t_im["camp_instance"];
	$nom_i_bat	= $t_im["nom_instance"];
	$nom_bat	= $t_im["nom_batiment"];
	
	if ($camp_bat == 1) {
		$pre_img = "b";
	}
	else {
		$pre_img = "r";
	}
	
	echo "<div><a href='evenement.php?infoid=".$id_instance_in_train."' target='_blank'><img src='../images_perso/b".$type_bat.$pre_img.".png' width='20' height='20'> " . $nom_bat ." ". $nom_i_bat ."[".$id_instance_in_train."]</a></div>";
	
}

/**
 * Fonction permettant d'afficher les infos de popover dans le cas où on est dans un batiment
 */
function afficher_infos_in_bat($mysqli, $id_perso) {
	
	$id_instance_in_bat = in_bat($mysqli,$id_perso);
									
	$sql_im = "SELECT instance_batiment.id_batiment, camp_instance, nom_instance, nom_batiment
						FROM instance_batiment, batiment 
						WHERE instance_batiment.id_batiment = batiment.id_batiment
						AND id_instanceBat='$id_instance_in_bat'";
	$res_im = $mysqli->query($sql_im);
	$t_im = $res_im->fetch_assoc();
			
	$type_bat 	= $t_im["id_batiment"];
	$camp_bat 	= $t_im["camp_instance"];
	$nom_i_bat	= $t_im["nom_instance"];
	$nom_bat	= $t_im["nom_batiment"];
	
	if ($camp_bat == 1) {
		$pre_img = "b";
	}
	else {
		$pre_img = "r";
	}
	
	echo "<div><a href='evenement.php?infoid=".$id_instance_in_bat."' target='_blank'><img src='../images_perso/b".$type_bat.$pre_img.".png' width='20' height='20'> " . $nom_bat ." ". $nom_i_bat ."[".$id_instance_in_bat."]</a></div>";
}

/**
 * Fonction permettant d'afficher les liens si objet à terre
 */
function afficher_liens_objet($nb_o, $x, $y) {
	if ($nb_o) {
		echo "		<div><a href='jouer.php?ramasser=voir&x=".$x."&y=".$y."' >Voir la liste des objets à terre</a></div> ";
		echo "		<div><a href='jouer.php?ramasser=ok' >Ramasser les objets à terre (1 PA)</a></div> ";
	}
}

/**
 * Fonction permettant d'afficher les liens si perso du génie et sur rail
 */
function afficher_liens_rail_genie($genie_compagnie_perso, $fond_im) {
	
	if ($genie_compagnie_perso && ($fond_im == 'rail.gif' || $fond_im == 'rail_1.gif' || $fond_im == 'rail_2.gif' || $fond_im == 'rail_3.gif' || $fond_im == 'rail_4.gif' || $fond_im == 'rail_5.gif' || $fond_im == 'rail_7.gif' || $fond_im == 'railP.gif')) {
		echo "		<div><a href='action.php?saboter_rail=ok' >Détruire le rail (10 PA)</a></div> ";
	}
	
}

/**
 * Fonction permettant d'afficher les liens si dans batiment
 */
function afficher_liens_in_bat($mysqli, $id_perso) {
	
	$id_instance_in_bat = in_bat($mysqli,$id_perso);
									
	echo "		<div><a href='batiment.php?bat=".$id_instance_in_bat."' target='_blank'>Accéder à la page du bâtiment</a></div> ";
	echo "		<div><a href='action.php?bat=".$id_instance_in_bat."&reparer=ok'>Réparer ce bâtiment (5PA)</a></div> ";
}

/**
 * Fonction permettant d'afficher les liens si à proximité d'un batiment
 */
function afficher_liens_prox_bat($mysqli, $id_perso, $x_perso, $y_perso, $type_perso) {
	
	// recuperation des id et noms des batiments dans lesquels le perso peut entrer
	$res_bat = id_prox_bat($mysqli, $x_perso, $y_perso);
	
	while ($bat1 = $res_bat->fetch_assoc()) {
		
		$nom_ibat 		= $bat1["nom_instance"];
		$id_bat 		= $bat1["id_instanceBat"];
		$bat 			= $bat1["id_batiment"];
		$pv_instance 	= $bat1["pv_instance"];
		$pvMax_instance = $bat1["pvMax_instance"];
			
		//recuperation du nom du batiment
		$sql_n = "SELECT nom_batiment FROM batiment WHERE id_batiment = '$bat'";
		$res_n = $mysqli->query($sql_n);
		$t_n = $res_n->fetch_assoc();
		
		$nom_bat = $t_n["nom_batiment"];
			
		// verification si le batiment est de la même nation que le perso
		if(!nation_perso_bat($mysqli, $id_perso, $id_bat)) {
		
			// Verification si le batiment est vide
			// + Le lien est utile pour les batiments autre que barricade et pont 
			// + Le lien est utile que pour les unités autre que chien et soigneur
			// + si batiment tour de guet, seul les infanterie peuvent capturer
			if((batiment_vide($mysqli, $id_bat) && $bat != 1 && $bat != 5 && $bat != 7 && $bat != 11 && $type_perso != '6' && $type_perso != '4') || (($bat == 2 && $type_perso == 3))){
				echo "		<div><a href='jouer.php?bat=".$id_bat."&bat2=".$bat."' > Capturer ".$nom_bat." ".$nom_ibat." [".$id_bat."]</a></div>";
			}
		}
		else {
			if($bat != 1 && $bat != 5 && $bat != 10){
				// Si batiment tour de guet, seul les infanteries, soigneurs et chiens peuvent rentrer
				if (($bat == 2 && ($type_perso == 3 || $type_perso == 4 || $type_perso == 6)) || $bat != 2 ) {
					echo "		<div><a href='jouer.php?bat=".$id_bat."&bat2=".$bat."' > Entrer dans ".$nom_bat." ".$nom_ibat." [".$id_bat."]</a></div>";
				}
			}
			
			// Les chiens ne peuvent pas réparer les batiments
			if ($pv_instance < $pvMax_instance && $type_perso != '6') {
				echo "		<div><a href='action.php?bat=".$id_bat."&reparer=ok' > Reparer ".$nom_bat." ".$nom_ibat." [".$id_bat."] (5 PA)</a></div>";
			}
		}
		
		// Pont
		// Les chiens ne peuvent pas saboter les ponts
		if ($bat == 5 && $type_perso != '6') {
			if ($pv_instance < $pvMax_instance) {
				echo "		<div><a href='action.php?bat=".$id_bat."&reparer=ok' > Reparer ".$nom_bat." ".$nom_ibat." [".$id_bat."] (5 PA)</a></div>";
			}
			echo "		<div><a href='action.php?bat=".$id_bat."&saboter=ok' > Saboter ".$nom_bat." ".$nom_ibat." [".$id_bat."] (10 PA)</a></div>";
		}
	}
}

/**
 * Affichage des liens de bousculade
 */
function afficher_lien_bouculade($x, $x_perso, $y, $y_perso, $cout_pm) {
	if($y == $y_perso+1 && $x == $x_perso+1){
		echo "<div><a href='jouer.php?mouv=3&action_popup=ok'>Bousculer (".$cout_pm." PM et 3 PA)</a></div>";								
	}
	else if($y == $y_perso-1 && $x == $x_perso+1){
		echo "<div><a href='jouer.php?mouv=8&action_popup=ok'>Bousculer (".$cout_pm." PM et 3 PA)</a></div>";
	}
	else if($y == $y_perso && $x == $x_perso+1){
		echo "<div><a href='jouer.php?mouv=5&action_popup=ok'>Bousculer (".$cout_pm." PM et 3 PA)</a></div>";
	}
	else if($y == $y_perso && $x == $x_perso-1) {
		echo "<div><a href='jouer.php?mouv=4&action_popup=ok'>Bousculer (".$cout_pm." PM et 3 PA)</a></div>";
	}
	else if($y == $y_perso+1 && $x == $x_perso-1) {
		echo "<div><a href='jouer.php?mouv=1&action_popup=ok'>Bousculer (".$cout_pm." PM et 3 PA)</a></div>";
	}
	else if($y == $y_perso-1 && $x == $x_perso-1) {
		echo "<div><a href='jouer.php?mouv=6&action_popup=ok'>Bousculer (".$cout_pm." PM et 3 PA)</a></div>";
	}
	else if($y == $y_perso+1 && $x == $x_perso) {
		echo "<div><a href='jouer.php?mouv=2&action_popup=ok'>Bousculer (".$cout_pm." PM et 3 PA)</a></div>";
	}
	else if($y == $y_perso-1 && $x == $x_perso) {
		echo "<div><a href='jouer.php?mouv=7&action_popup=ok'>Bousculer (".$cout_pm." PM et 3 PA)</a></div>";
	}
}

/**
 * affichage du popover pour les cases de type pont (batiments sur lesquels on peut se déplacer)
 */
function afficher_popover_pont($x, $x_perso, $y, $y_perso, $fond_carte, $idI_bat, $nom_bat, $cout_pm, $type_perso) {
	
	if($y > $y_perso+1 || $y < $y_perso-1 || $x > $x_perso+1 || $x < $x_perso-1) {
		echo "<td width=40 height=40 background=\"../fond_carte/".$fond_carte."\">";
		echo "	<img tabindex='0' border=0 src=\"../fond_carte/".$fond_carte."\" width=40 height=40 data-toggle='popover' data-trigger='focus' data-html='true' data-placement='bottom' ";
		echo "			title=\"<div><img src='../fond_carte/".$fond_carte."' width='20' height='20'><a href='evenement.php?infoid=".$idI_bat."' target='_blank'> ".$nom_bat." [".$idI_bat."]</a></div>\" ";
		echo "			data-content=\"<div></div>\" >";
		echo "</td>";
	}
	else {
		echo "<td width=40 height=40 background=\"../fond_carte/".$fond_carte."\">";
		echo "	<img tabindex='0' border=0 src=\"../fond_carte/".$fond_carte."\" width=40 height=40 data-toggle='popover' data-trigger='focus' data-html='true' data-placement='bottom' ";
		echo "			title=\"<div><img src='../fond_carte/".$fond_carte."' width='20' height='20'><a href='evenement.php?infoid=".$idI_bat."' target='_blank'> ".$nom_bat." [".$idI_bat."]</a></div>\" ";
		if($y == $y_perso+1 && $x == $x_perso+1){
			echo "			data-content=\"<div><a href='jouer.php?mouv=3'>Se déplacer (".$cout_pm." PM)</a></div>";
		} else if($y == $y_perso-1 && $x == $x_perso+1){
			echo "			data-content=\"<div><a href='jouer.php?mouv=8'>Se déplacer (".$cout_pm." PM)</a></div>";
		} else if($y == $y_perso && $x == $x_perso+1){
			echo "			data-content=\"<div><a href='jouer.php?mouv=5'>Se déplacer (".$cout_pm." PM)</a></div>";
		} else if($y == $y_perso && $x == $x_perso-1) {
			echo "			data-content=\"<div><a href='jouer.php?mouv=4'>Se déplacer (".$cout_pm." PM)</a></div>";
		} else if($y == $y_perso+1 && $x == $x_perso-1) {
			echo "			data-content=\"<div><a href='jouer.php?mouv=1'>Se déplacer (".$cout_pm." PM)</a></div>";
		} else if($y == $y_perso-1 && $x == $x_perso-1) {
			echo "			data-content=\"<div><a href='jouer.php?mouv=6'>Se déplacer (".$cout_pm." PM)</a></div>";
		} else if($y == $y_perso+1 && $x == $x_perso) {
			echo "			data-content=\"<div><a href='jouer.php?mouv=2'>Se déplacer (".$cout_pm." PM)</a></div>";
		} else if($y == $y_perso-1 && $x == $x_perso) {
			echo "			data-content=\"<div><a href='jouer.php?mouv=7'>Se déplacer (".$cout_pm." PM)</a></div>";
		}

		echo "	<div><a href='evenement.php?infoid=".$idI_bat."' target='_blank'>Voir ses événéments</a></div>";
		// les chiens ne peuvent pas saboter
		if ($type_perso != 6)
				echo "<div><a href='action.php?bat=".$idI_bat."&saboter=ok' >Saboter (10 PA)</a></div>";
		echo "\" ></td>";
	}
}

/**
 * affichage des popover pour perso dans un batiment
 */
function afficher_popover_in_bat($x, $x_perso, $y, $y_perso, $taille_case, $fond_im, $nb_o, $nom_terrain, $id_bat_perso) {
	
	if($y > $y_perso+$taille_case || $y < $y_perso-$taille_case || $x > $x_perso+$taille_case || $x < $x_perso-$taille_case) {
		if($nb_o){
			echo "<td width=40 height=40 background=\"../fond_carte/".$fond_im."\">";
			echo "	<img border=0 src=\"../fond_carte/o1.gif\" width=40 height=40 data-toggle='tooltip' data-placement='top' title='objets à ramasser'/>";
			echo "</td>";
		}
		else {					
			echo "<td width=40 height=40> <img border=0 src=\"../fond_carte/".$fond_im."\" width=40 height=40></td>";
		}
	}
	else {
		if ($x == $x_perso + $taille_case) {
			for ($i = -$taille_case; $i <= $taille_case; $i++) {
				if ($y == $y_perso + $i) {
					afficher_popover_autour_bat($fond_im, $nom_terrain, $x, $y, $nb_o, $id_bat_perso);
				}
			}
		}
		else if ($x == $x_perso - $taille_case) {
			for ($i = -$taille_case; $i <= $taille_case; $i++) {
				if ($y == $y_perso + $i) {
					afficher_popover_autour_bat($fond_im, $nom_terrain, $x, $y, $nb_o, $id_bat_perso);
				}
			}
		}
		else if ($y == $y_perso + $taille_case) {
		
			for ($i = -$taille_case + 1; $i <= $taille_case - 1; $i++) {
				if ($x == $x_perso + $i) {
					afficher_popover_autour_bat($fond_im, $nom_terrain, $x, $y, $nb_o, $id_bat_perso);
				}
			}
		}
		else if ($y == $y_perso - $taille_case) {
		
			for ($i = -$taille_case + 1; $i <= $taille_case - 1; $i++) {
				if ($x == $x_perso + $i) {
					afficher_popover_autour_bat($fond_im, $nom_terrain, $x, $y, $nb_o, $id_bat_perso);					
				}
			}
		}
	}
}

/**
 * affichage du popover pour les cases se trouvant autour d'un batiment pour un perso se trouvant dans un batiment
 */
function afficher_popover_autour_bat($fond_im, $nom_terrain, $x, $y, $nb_o, $id_bat_perso) {
	
	$coord_sortie = $x.",".$y;
					
	echo "<td width=40 height=40 background=\"../fond_carte/".$fond_im."\">";
	if($nb_o){
		echo "	<img tabindex='0' border=0 src=\"../fond_carte/o1.gif\" width=40 height=40 ";
	}
	else {
		echo "	<img tabindex='0' border=0 src=\"../fond_carte/".$fond_im."\" width=40 height=40 ";
	}
	echo "			data-toggle='popover' data-trigger='focus' data-html='true' data-placement='bottom'";
	if ($id_bat_perso != 10) {
		echo "			title=\"<div><img src='../fond_carte/".$fond_im."' width='20' height='20'> ".$nom_terrain."</div>\" ";
		echo "			data-content=\"<div><a href='jouer.php?sortie=".$coord_sortie."'>Sortir ici</a></div>\" >";
	}
	echo "</td>";
	
}

?>
