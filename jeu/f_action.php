<?php
require_once("../fonctions.php");
require_once("f_carte.php");
require_once("f_combat.php");

$mysqli = db_connexion();

/**
  * Fonction qui retourne la chaine de caractere correspondant a l'image associee a une action
  * @param $id_action	: L'identifiant de l'action
  * @return String		: L'image
  */
function image_action($id_action){
	
	if($id_action == '11' || $id_action == '12' || $id_action == '13' || $id_action == '14' || $id_action == '15' || 
	   $id_action == '16' || $id_action == '17' || $id_action == '18' || $id_action == '19' || $id_action == '20' ||
	   $id_action == '21' || $id_action == '22' || $id_action == '23' || $id_action == '24' || $id_action == '25' ||
	   $id_action == '26' || $id_action == '27'){
	   // soins pv
	   return 'soin.gif';
	}
	
	if($id_action == '140' || $id_action == '141' || $id_action == '142'){
	   // soins malus
	   return 'soin.gif';
	}
	
	if($id_action == '76'){
		// Reparer bat
		return 'reparer_bat.png';
	}
	
	if($id_action == '80' || $id_action == '81' || $id_action == '82' || $id_action == '83' || 
	   $id_action == '84' || $id_action == '85'){
		// Upgrade bat
		return 'reparer_bat.png';
	}
}

function construire_rail($mysqli, $t_rail, $id_perso, $carte){
	
	$sql = "SELECT pa_perso, pv_perso, nom_perso, clan FROM perso WHERE id_perso='$id_perso'";
	$res = $mysqli->query($sql);
	$t = $res->fetch_assoc();
	
	$camp		= $t['clan'];
	$nom_perso	= $t['nom_perso'];
	$pa_perso 	= $t['pa_perso'];
	$pv_perso	= $t['pv_perso'];
	
	if ($pa_perso >= 4) {
		
		// recuperation de la couleur du camp du perso
		$couleur_clan_perso = couleur_clan($camp);

		$t_rail2 = explode(',',$t_rail);
		$x_rail = $t_rail2[0];
		$y_rail = $t_rail2[1];

		// récupération du rail à détruire
		$sql = "SELECT fond_carte FROM carte WHERE x_carte='$x_rail' AND y_carte='$y_rail'";
		$res = $mysqli->query($sql);
		$t = $res->fetch_assoc();
		$fond_rail = $t['fond_carte'];
		
		// verification possibilité construction rail
		$verif_construction_rail = verification_construction_rail($mysqli, $x_rail, $y_rail);
		
		if ($verif_construction_rail) {
			
			// mettre la bonne image de rail
			$t_rail3 = explode('.', $fond_rail);
			$num_rail = $t_rail3[0];
			
			if ($num_rail == '2' || $num_rail == '7') {
				// Coline ou foret
				$cout_pa = 6;
				$cout_pv = 0;
				$image_rail = "rail_".$num_rail.".gif";
			}
			else if ($num_rail == '3') {
				// Montagne
				$cout_pa = 8;
				$cout_pv = 0;
				$image_rail = "rail_".$num_rail.".gif";
			}
			else if ($num_rail == '4') {
				// desert
				$cout_pa = 4;
				$cout_pv = 50;
				$image_rail = "rail_".$num_rail.".gif";
			}
			else if ($num_rail == '5') {
				// plaine enneigée
				$cout_pa = 5;
				$cout_pv = 0;
				$image_rail = "rail_".$num_rail.".gif";
			}
			else if ($num_rail == '8') {
				// eau peu profonde
				$cout_pa = 8;
				$cout_pv = 0;
				$image_rail = "railP.gif";
			}
			else {
				// plaine
				$cout_pa = 4;
				$cout_pv = 0;
				$image_rail = "rail_1.gif";
			}
			
			if ($pa_perso >= $cout_pa && $pv_perso >= $cout_pv) {
			
				// mise a jour de la carte
				$sql = "UPDATE $carte SET fond_carte='$image_rail' WHERE x_carte='$x_rail' AND y_carte='$y_rail'";
				$mysqli->query($sql);
				
				$gain_xp = rand(2,4);
				
				// maj pa perso 
				$sql = "UPDATE perso SET pa_perso = pa_perso - $cout_pa, pv_perso = pv_perso - $cout_pv, xp_perso = xp_perso + $gain_xp, pi_perso = pi_perso + $gain_xp WHERE id_perso='$id_perso'";
				$mysqli->query($sql);

				//mise a jour de la table evenement
				$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>','a construit <b>rail</b>',NULL,'',' - gain de $gain_xp XP/PI',NOW(),'0')";
				$mysqli->query($sql);
				
				return 1;
			}
			else {
				echo "<center>";
				echo "<b><font color='red'>Vous devez disposer de $cout_pa PA (vous possédez $pa_perso PA) ";
				if ($cout_pv > 0) {
					echo "et de $cout_pv PV (vous possédez $pv_perso PV) ";
				}
				echo "pour construire un rail sur ce terrain</font></b>";
				
				echo "<br /><br /><a href='jouer.php' class='btn btn-primary'>Retour</a>";
				echo "</center>";
			}
		}
		else {
			echo "<center>";
			echo "<b><font color='red'>Un rail ne peut se poser qu'à proximité d'une gare ou d'un autre rail</font></b>";
			echo "<br /><br /><a href='jouer.php' class='btn btn-primary'>Retour</a>";
			echo "</center>";
		}
	}
	else {
		echo "<center>";
		echo "<b><font color='red'>Vous devez posséder au minimum 4 PA pour construire un rail</font></b>";
		echo "<br /><br /><a href='jouer.php' class='btn btn-primary'>Retour</a>";
		echo "</center>";
	}
}

/**
 * Fonction permettant de vérifier si la construction du rail respecte les conditions de construction
 * @return boolean : true si contraintes respectée, false sinon
 */
function verification_construction_rail($mysqli, $x_rail, $y_rail) {
	
	// Le rail est-il collé à un autre rail ?
	$sql = "SELECT fond_carte FROM carte 
			WHERE (fond_carte='rail.gif' OR fond_carte='rail_1.gif' OR fond_carte='rail_2.gif' OR fond_carte='rail_3.gif' OR fond_carte='rail_4.gif' OR fond_carte='rail_5.gif' OR fond_carte='rail_7.gif' OR fond_carte='railP.gif')
			AND x_carte >= $x_rail - 1 AND x_carte <= $x_rail + 1 
			AND y_carte >= $y_rail - 1 AND y_carte <= $y_rail + 1";
	$res = $mysqli->query($sql);
	
	$nb_rails_prox = $res->num_rows;
	
	if ($nb_rails_prox > 0) {
		return true;
	}
	else {
		// Le rail est-il collé à une gare
		$sql = "SELECT idPerso_carte FROM carte, instance_batiment
				WHERE carte.idPerso_carte = instance_batiment.id_instanceBat
				AND idPerso_carte >= 50000
				AND instance_batiment.id_batiment = 11
				AND x_carte >= $x_rail - 1 AND x_carte <= $x_rail + 1 
				AND y_carte >= $y_rail - 1 AND y_carte <= $y_rail + 1";
		$res = $mysqli->query($sql);
	
		$nb_cases_gare_prox = $res->num_rows;
		
		return $nb_cases_gare_prox > 0;
	}
}

/**
 * Fonction permettant de verifier si les conditions de construction d'un batiment sont bien respectées
 * @return boolean : true si contraintes respectée, false sinon
 */
function verif_contraintes_construction($mysqli, $id_bat, $camp_perso, $x_bat, $y_bat) {
	
	// Conditions construction
	if ($id_bat == '9') {
		// Fort => 16 Génie civil présent à 10 cases autour du point de construction
		$nb_genie_civil 	= 16;
		$nb_soigneur		= 0;
	}
	else if ($id_bat == '8') {
		// Fortin => 10 Génie civil présent à 10 cases autour du point de construction
		$nb_genie_civil 	= 1;
		$nb_soigneur		= 0;
	}
	else if ($id_bat == '11') {
		// Gare => 6 Génie civil présent à 10 cases autour du point de construction
		$nb_genie_civil 	= 1;
		$nb_soigneur		= 0;
	}
	else if ($id_bat == '7') {
		// Hopital => 3 Génie civil présent à 10 cases autour du point de construction
		$nb_genie_civil 	= 3;
		$nb_soigneur		= 1;
	}
	else if ($id_bat == '2') {
		// tour de guêt => 1 Génie civil
		$nb_genie_civil 	= 1;
		$nb_soigneur		= 0;
	}
	else {
		$nb_genie_civil 	= 0;
		$nb_soigneur		= 0;
	}
	
	// Verification nb genie civil
	$sql = "SELECT count(perso.id_perso) as nb_gc FROM perso, perso_in_compagnie, compagnies 
							WHERE perso.id_perso = perso_in_compagnie.id_perso
							AND perso_in_compagnie.id_compagnie = compagnies.id_compagnie
							AND compagnies.genie_civil='1'
							AND compagnies.id_clan = '$camp_perso'
							AND perso.genie = 1
							AND x_perso >= $x_bat - 10
							AND x_perso <= $x_bat + 10
							AND y_perso >= $y_bat - 10
							AND y_perso <= $y_bat + 10
							AND est_gele = 0";							
	$res = $mysqli->query($sql);
	$t = $res->fetch_assoc();
	
	$verif_nb_gc = $t['nb_gc'];	
	
	// Verification nb soigneurs
	$verif_nb_soigneurs = 0;
	
	if ($nb_soigneur > 0) {
		$sql = "SELECT count(perso.id_perso) as nb_soigneur FROM perso
								WHERE clan = '$camp_perso'
								AND type_perso = 4
								AND x_perso >= $x_bat - 10
								AND x_perso <= $x_bat + 10
								AND y_perso >= $y_bat - 10
								AND y_perso <= $y_bat + 10
								AND est_gele = 0";							
		$res = $mysqli->query($sql);
		$t = $res->fetch_assoc();
		
		$verif_nb_soigneurs = $t['nb_soigneur'];	
	}
	
	return $verif_nb_gc >= $nb_genie_civil && $verif_nb_soigneurs >= $nb_soigneur;
}

/**
 * Fonction permettant de verifier si les conditions de construction d'un batiment sont bien respectées
 * @return boolean : true si contraintes respectée, false sinon
 */
function verif_contraintes_construction_ennemis($mysqli, $id_bat, $camp_perso, $x_bat, $y_bat) {
	
	// Conditions construction
	if ($id_bat == '2') {
		// Tour de guet
		$nb_cases_ennemi 	= 5;
	}
	else if ($id_bat == '5' || $id_bat == '1') {
		// Barricades et Ponts
		$nb_cases_ennemi 	= 0;
	}
	else if ($id_bat == '7') {
		// Hopital
		$nb_cases_ennemi 	= 5;
	}
	else {
		$nb_cases_ennemi 	= 5;
	}
	
	// Verification distance avec ennemis
	$sql = "SELECT count(perso.id_perso) as nb_ennemi FROM perso 
			WHERE clan != '$camp_perso'
			AND x_perso >= $x_bat - $nb_cases_ennemi
			AND x_perso <= $x_bat + $nb_cases_ennemi
			AND y_perso >= $y_bat - $nb_cases_ennemi
			AND y_perso <= $y_bat + $nb_cases_ennemi
			AND pv_perso > 0
			AND est_gele = 0";
	$res = $mysqli->query($sql);
	$t = $res->fetch_assoc();
	
	$verif_nb_ennemis = $t['nb_ennemi'];
	
	return $verif_nb_ennemis == 0;
}

/**
 * Fonction permettant de verifier si les conditions de construction d'un batiment sont bien respectées
 * @return boolean : true si contraintes respectée, false sinon
 */
function verif_contraintes_construction_bat($mysqli, $id_bat, $camp_perso, $x_bat, $y_bat) {
	
	// Conditions construction
	if ($id_bat == '2') {
		// Tour de guet
		$nb_cases_bat 	= 2;
		$nb_cases_gare	= 2;
		$nb_cases_rapat = 2;
		$nb_cases_tour	= 7;
	}
	else if ($id_bat == '5' || $id_bat == '1') {
		// Barricades et Ponts
		$nb_cases_bat 	= 0;
		$nb_cases_gare	= 2;
		$nb_cases_rapat = 2;
		$nb_cases_tour	= 0;
	}
	else if ($id_bat == '7') {
		// Hopital
		$nb_cases_bat 	= 2;
		$nb_cases_gare	= 20;
		$nb_cases_rapat = 20;
		$nb_cases_tour	= 0;
	}
	else if ($id_bat == '8') {
		// Fortin
		$nb_cases_bat 	= 2;
		$nb_cases_gare	= 2;
		$nb_cases_rapat = 4;
		$nb_cases_tour	= 0;
	}
	else if ($id_bat == '9') {
		// Fort
		$nb_cases_bat 	= 2;
		$nb_cases_gare	= 2;
		$nb_cases_rapat = 4;
		$nb_cases_tour	= 0;
	}
	else if ($id_bat == '11') {
		// Gare
		$nb_cases_bat 	= 2;
		$nb_cases_gare	= 1;
		$nb_cases_rapat = 2;
		$nb_cases_tour	= 0;
	}
	else {
		$nb_cases_bat 	= 2;
		$nb_cases_gare	= 2;
		$nb_cases_rapat = 2;
		$nb_cases_tour	= 0;
	}
	
	// Verification distance avec autre batiment
	$sql = "SELECT count(id_instanceBat) as nb_bat FROM instance_batiment 
			WHERE x_instance >= $x_bat - $nb_cases_bat
			AND x_instance <= $x_bat + $nb_cases_bat
			AND y_instance >= $y_bat - $nb_cases_bat
			AND y_instance <= $y_bat + $nb_cases_bat
			AND pv_instance > 0";
	$res = $mysqli->query($sql);
	$t = $res->fetch_assoc();
	
	$verif_nb_bats = $t['nb_bat'];
	
	// Verification distance avec gare
	$sql = "SELECT count(id_instanceBat) as nb_gare FROM instance_batiment INNER JOIN batiment ON instance_batiment.id_batiment = batiment.id_batiment
			WHERE x_instance >= $x_bat - $nb_cases_gare - taille_batiment / 2
			AND x_instance <= $x_bat + $nb_cases_gare + taille_batiment / 2
			AND y_instance >= $y_bat - $nb_cases_gare - taille_batiment / 2
			AND y_instance <= $y_bat + $nb_cases_gare + taille_batiment / 2
			AND instance_batiment.id_batiment='11' AND camp_instance='$camp_perso' AND pv_instance > 0";
	$res = $mysqli->query($sql);
	$t = $res->fetch_assoc();
	
	$verif_nb_gares = $t['nb_gare'];
	
	// Verification distance avec autres batiments de rapatriement
	$sql = "SELECT count(id_instanceBat) as nb_rapat FROM instance_batiment INNER JOIN batiment ON instance_batiment.id_batiment = batiment.id_batiment
			WHERE x_instance >= $x_bat - $nb_cases_rapat - taille_batiment / 2
			AND x_instance <= $x_bat + $nb_cases_rapat + taille_batiment / 2
			AND y_instance >= $y_bat - $nb_cases_rapat - taille_batiment / 2
			AND y_instance <= $y_bat + $nb_cases_rapat + taille_batiment / 2
			AND camp_instance='$camp_perso'
			AND (instance_batiment.id_batiment='7' OR instance_batiment.id_batiment='8' OR instance_batiment.id_batiment='9') AND pv_instance > 0";
	$res = $mysqli->query($sql);
	$t = $res->fetch_assoc();

	$verif_nb_rapats = $t['nb_rapat'];
	
	$verif_distance_tour = 0;
	
	// Verification distance tour de guet
	if ($id_bat == '2') {
		$sql = "SELECT count(id_instanceBat) as nb_tour FROM instance_batiment 
			WHERE x_instance >= $x_bat - $nb_cases_tour
			AND x_instance <= $x_bat + $nb_cases_tour
			AND y_instance >= $y_bat - $nb_cases_tour
			AND y_instance <= $y_bat + $nb_cases_tour
			AND id_batiment='2' AND camp_instance='$camp_perso' AND pv_instance > 0";
		$res = $mysqli->query($sql);
		$t = $res->fetch_assoc();
		
		$verif_distance_tour = $t['nb_tour'];
	}
	
	// Verification Hopital distance < 40 cases avec Fort / Fortin ou Gare
	// Verification Hopital distance >= 30 cases avec autre hopital
	$verif_bat_pour_construction_hopital = 1;
	$verif_hop_pour_construction_hopital = 0;
	$nb_cases_construction_hopital_bat = 40;
	$nb_cases_construction_hopital_hop = 30;
	$nb_instance_hopital = 0;
	
	if ($id_bat == '7') {
		
		$sql = "SELECT count(id_instanceBat) as nb_bat_pour_hopital FROM instance_batiment 
			WHERE x_instance >= $x_bat - $nb_cases_construction_hopital_bat
			AND x_instance <= $x_bat + $nb_cases_construction_hopital_bat
			AND y_instance >= $y_bat - $nb_cases_construction_hopital_bat
			AND y_instance <= $y_bat + $nb_cases_construction_hopital_bat
			AND (id_batiment='8' OR id_batiment='9' OR id_batiment='11') AND camp_instance='$camp_perso' AND pv_instance > 0";
		$res = $mysqli->query($sql);
		$t = $res->fetch_assoc();
		
		$verif_bat_pour_construction_hopital = $t['nb_bat_pour_hopital'];
		
		$sql = "SELECT count(id_instanceBat) as nb_hopital FROM instance_batiment 
			WHERE x_instance >= $x_bat - $nb_cases_construction_hopital_hop
			AND x_instance <= $x_bat + $nb_cases_construction_hopital_hop
			AND y_instance >= $y_bat - $nb_cases_construction_hopital_hop
			AND y_instance <= $y_bat + $nb_cases_construction_hopital_hop
			AND (id_batiment='7') AND camp_instance='$camp_perso' AND pv_instance > 0";
		$res = $mysqli->query($sql);
		$t = $res->fetch_assoc();
		
		$verif_hop_pour_construction_hopital = $t['nb_hopital'];

		$sql = "SELECT count(id_instanceBat) as nb_hopital FROM instance_batiment WHERE (id_batiment='7') AND camp_instance='$camp_perso'";
		if ($result = $mysqli->query($sql)) {
  
			// Return the number of rows in result set
			$nb_instance_hopital = mysqli_num_rows( $result );
		}

	}
	
	$verif_berge_pont			= 1;
	$verif_distance_pont 		= 0;
	$verif_distance_pont_bat 	= 0;
	$verif_largeur_pont			= 0;
	
	if ($id_bat == '5') {
		
		$ban_id_pont = array();
		
		// Récupération de tous les id des ponts rattachés au pont en construction
		$ban_id_pont = get_cases_pont($mysqli, $x_bat, $y_bat, $ban_id_pont);
		
		// Si ban vide => verifier case pont pas entouré d'eau
		if (empty($ban_id_pont)) {
			
			$sql = "SELECT fond_carte FROM carte 
						WHERE x_carte >= $x_bat - 1
						AND x_carte <= $x_bat + 1
						AND y_carte >= $y_bat - 1
						AND y_carte <= $y_bat + 1
						AND fond_carte!='8.gif'
						AND fond_carte!='9.gif'";
			$res = $mysqli->query($sql);
			$verif_berge_pont = $res->num_rows;
		}
		else {
			
			$limite_basse_x = $x_bat - 2;
			$limite_haute_x = $x_bat + 2;
			
			$limite_basse_y = $y_bat - 2;
			$limite_haute_y = $y_bat + 2;
			
			// Verification largeur pont 
			foreach ($ban_id_pont as $id_pont){
				
				$sql = "SELECT x_carte, y_carte FROM carte WHERE save_info_carte='$id_pont'";
				$res = $mysqli->query($sql);
				$t = $res->fetch_assoc();
				
				$x_pont_verif = $t['x_carte'];
				$y_pont_verif = $t['y_carte'];
				
				if (($x_pont_verif <= $limite_basse_x || $x_pont_verif >= $limite_haute_x)
						&& ($y_pont_verif <= $limite_basse_y || $y_pont_verif >= $limite_haute_y)) {
					$verif_largeur_pont = 1;
					break;
				}
			}
		}
		
		// 20 PM entre chaque pont
		$distance_min_pont = 20;
		
		$sql = "SELECT save_info_carte FROM carte 
					WHERE x_carte >= $x_bat - $distance_min_pont
					AND x_carte <= $x_bat + $distance_min_pont
					AND y_carte >= $y_bat - $distance_min_pont
					AND y_carte <= $y_bat + $distance_min_pont
					AND (fond_carte='b5b.png' OR fond_carte='b5r.png' OR fond_carte='b5g.png')
					AND save_info_carte NOT IN ( '" . implode( "', '" , $ban_id_pont ) . "' )";
		$res = $mysqli->query($sql);
		$verif_distance_pont = $res->num_rows;
		
		// 3 PM entre pont et bat 
		$distance_min_bat = 3;
		
		$sql = "SELECT idPerso_carte FROM carte 
					WHERE x_carte >= $x_bat - $distance_min_bat
					AND x_carte <= $x_bat + $distance_min_bat
					AND y_carte >= $y_bat - $distance_min_bat
					AND y_carte <= $y_bat + $distance_min_bat
					AND fond_carte!='b5b.png'
					AND fond_carte!='b5r.png'
					AND fond_carte!='b5g.png'
					AND idPerso_carte >= 50000 AND idPerso_carte < 200000";
		$res = $mysqli->query($sql);
		$verif_distance_pont_bat = $res->num_rows;
	}
	
	return $verif_nb_bats == 0 
				&& $verif_nb_gares == 0 
				&& $verif_nb_rapats == 0 
				&& $verif_bat_pour_construction_hopital > 0
				&& $verif_hop_pour_construction_hopital == 0
				&& $nb_instance_hopital <= 4
				&& $verif_distance_tour == 0 
				&& $verif_distance_pont == 0
				&& $verif_distance_pont_bat == 0
				&& $verif_largeur_pont == 0
				&& $verif_berge_pont > 0;
}

/**
 * Fonction permettant de récupérer les cases de pont connectées à celle qu'on essaye de placer
 * afin de les exclure pour la recherche de pont à moins de 30 cases
 */
function get_cases_pont($mysqli, $x_pont, $y_pont, $ban_id_pont) {
	
	$sql = "SELECT x_carte, y_carte, save_info_carte FROM carte
				WHERE x_carte >= $x_pont - 1
				AND x_carte <= $x_pont + 1
				AND y_carte >= $y_pont - 1
				AND y_carte <= $y_pont + 1
				AND (fond_carte='b5b.png' OR fond_carte='b5r.png'  OR fond_carte='b5g.png')
				AND coordonnees NOT IN (SELECT coordonnees FROM carte WHERE x_carte=$x_pont AND y_carte=$y_pont)
				AND save_info_carte NOT IN ( '" . implode( "', '" , $ban_id_pont ) . "' )";
	$res = $mysqli->query($sql);
	$nb_ponts = $res->num_rows;
	
	if ($nb_ponts > 0) {
		while ($t = $res->fetch_assoc()) {
			
			$x_pont 	= $t['x_carte'];
			$y_pont		= $t['y_carte'];
			$id_pont	= $t['save_info_carte'];
			
			array_push($ban_id_pont, $id_pont);
			
			$ban_id_pont = array_merge($ban_id_pont, get_cases_pont($mysqli, $x_pont, $y_pont, $ban_id_pont));
		}
	}
	
	return array_unique($ban_id_pont);
}

/**
  * Fonction qui permet de construire un batiment sur une case
  * @param $t_bat		: Un tableau contenant les coordonnees ou le batiement doit etre construit ainsi que l'identifiant du batiment
  * @param $id_perso	: L'identifiant du perso qui construit le batiment
  * @param $carte 		: La carte sur laquelle le batiment doit etre construit
  * @return Bool		: Si oui ou non le batiment est constructible
  */
function construire_bat($mysqli, $t_bat, $id_perso, $carte, $nom_instance){

	$t_bat2 = explode(',',$t_bat);
	$x_bat = $t_bat2[0];
	$y_bat = $t_bat2[1];
	$id_bat = $t_bat2[2];
	
	if(isset($id_bat) && $id_bat != ''){
	
		// recuperation du nom du batiment
		$sql = "SELECT nom_batiment, taille_batiment FROM batiment WHERE id_batiment='$id_bat'";
		$res = $mysqli->query($sql);
		$tb = $res->fetch_assoc();
		
		$nom_bat 	= $tb["nom_batiment"];
		$taille_bat = $tb["taille_batiment"];
		
		// recuperation des donnees necessaires pour la construction du batiment
		$sql = "SELECT clan, gain_xp_tour, or_perso, pa_perso, x_perso, y_perso, genie, pvMin_action, pvMax_action, coutPa_action, coutOr_action, coutBois_action, coutfer_action, contenance, action.nb_points as niveau_bat
				FROM action, action_as_batiment, perso_as_competence, competence_as_action, perso
				WHERE action.id_action = action_as_batiment.id_action
				AND perso_as_competence.nb_points = action.nb_points
				AND competence_as_action.id_action = action.id_action
				AND perso_as_competence.id_competence = competence_as_action.id_competence
				AND perso.id_perso = perso_as_competence.id_perso
				AND id_batiment = '$id_bat'
				AND perso_as_competence.id_perso = '$id_perso'";
		$res = $mysqli->query($sql);
		$t_b = $res->fetch_assoc();
		
		$pvMin 			= $t_b['pvMin_action'];
		$pvMax 			= $t_b['pvMax_action'];
		$coutPa 		= $t_b['coutPa_action'];
		$coutOr 		= $t_b['coutOr_action'];
		$coutBois 		= $t_b['coutBois_action'];
		$coutFer 		= $t_b['coutfer_action'];
		$or_perso 		= $t_b["or_perso"];
		$pa_perso 		= $t_b["pa_perso"];
		$x_perso		= $t_b["x_perso"];
		$y_perso		= $t_b["y_perso"];
		$camp_perso 	= $t_b["clan"];
		$genie_perso	= $t_b["genie"];
		$niveau_bat 	= $t_b["niveau_bat"];
		$contenance_bat = $t_b["contenance"];
		$gain_xp_tour_perso = $t_b["gain_xp_tour"];
		
		if ($gain_xp_tour_perso >= 20) {
			$max_xp_tour_atteint = true;
		}
		else {
			$max_xp_tour_atteint = false;
		}		
		
		if($camp_perso == '1'){
			$bat_camp = "b";
		}
		else if($camp_perso == '2'){
			$bat_camp = "r";
		}
		else if($camp_perso == '3'){
			$bat_camp = "g";
		}
		
		if($id_bat == '5'){
			$bat_camp = "g";
		}
		
		$verif_genie = false;
		if ($id_bat == 1) {
			$verif_genie = true;
		}
		else {
			if ($genie_perso > 0) {
				$verif_genie = true;
			}
		}
		
		if ($camp_perso != null && $camp_perso != 0) {
		
			// test pa
			if($pa_perso >= $coutPa){
				
				if($or_perso >= $coutOr){
					
					$sql = "SELECT MAX(x_carte) as x_max, MAX(y_carte) as y_max FROM carte";
					$res = $mysqli->query($sql);
					$t = $res->fetch_assoc();
					
					$X_MAX = $t['x_max'];
					$Y_MAX  = $t['y_max'];
					
					$verif_occ_in_map = true;
					
					if ($id_bat == 5) {
						$verif_occ_in_map = verif_position_libre_pont($mysqli, $x_bat, $y_bat, $X_MAX, $Y_MAX);
					}
					else {					
						$verif_occ_in_map = verif_position_libre($mysqli, $x_bat, $y_bat, $X_MAX, $Y_MAX);
					}
					
					if ($verif_occ_in_map) {
						
						$verif_coord_construction = verif_coord_in_perception($x_bat, $y_bat, $x_perso, $y_perso, $taille_bat);
					
						if ($verif_coord_construction) {
					
							if ($verif_genie) {
								
								$verif_fond_carte = true;
		
								$sql = "SELECT fond_carte FROM carte WHERE x_carte='$x_bat' AND y_carte='$y_bat'";
								$res = $mysqli->query($sql);
								$t_f = $res->fetch_assoc();
								
								$fond_carte = $t_f['fond_carte'];
								
								if ($id_bat == 1) {
									// Barricade peut être construite sur rail
									if ($fond_carte != 'rail.gif' && $fond_carte != 'rail_1.gif' && $fond_carte != 'rail_2.gif' && $fond_carte != 'rail_3.gif' && $fond_carte != 'rail_4.gif' && $fond_carte != 'rail_5.gif' && $fond_carte != 'rail_7.gif' && $fond_carte != 'railP.gif' 
										&& $fond_carte != '1.gif') {
										$verif_fond_carte = false;
									}
								}
								else if ($id_bat == 5) {
									// Pont sur eau ou eau profonde
									if($fond_carte != '8.gif' && $fond_carte != '9.gif') {
										$verif_fond_carte = false;
									}
								}
								else {
									if ($fond_carte != '1.gif') {
										$verif_fond_carte = false;
									}
								}
								
								if ($verif_fond_carte) {								
								
									$gain_xp = rand(min_gain_xp_construction($id_bat), max_gain_xp_construction($id_bat));
									$gain_pc = gain_pc_construction($id_bat);
									
									if ($gain_xp_tour_perso + $gain_xp > 20) {
										$gain_xp = 0;
										$max_xp_tour_atteint = true;
									}
									
									// Autorisations de construction - vérification des contraintes
									$autorisation_construction_gc 		= verif_contraintes_construction($mysqli, $id_bat, $camp_perso, $x_bat, $y_bat);
									$autorisation_construction_ennemis 	= verif_contraintes_construction_ennemis($mysqli, $id_bat, $camp_perso, $x_bat, $y_bat);
									$autorisation_construction_bats 	= verif_contraintes_construction_bat($mysqli, $id_bat, $camp_perso, $x_bat, $y_bat);
									
									$autorisation_construction_taille = true;
									
									$taille_search = floor($taille_bat / 2);
									
									if ($taille_bat > 1) {
										
										// verification carte pour construction 
										$sql = "SELECT occupee_carte, fond_carte FROM carte 
												WHERE x_carte <= $x_bat + $taille_search AND x_carte >= $x_bat - $taille_search AND y_carte <= $y_bat + $taille_search AND y_carte >= $y_bat - $taille_search";
										$res = $mysqli->query($sql);
										
										while ($t = $res->fetch_assoc()) {
											
											$occupee_carte 	= $t["occupee_carte"];
											$fond_carte 	= $t["fond_carte"];
											
											if ($occupee_carte || $fond_carte != '1.gif') {
												$autorisation_construction_taille = false;
											}
										}
									}
									
									if($autorisation_construction_gc){
										
										if ($autorisation_construction_ennemis) {
											
											if ($autorisation_construction_bats) {
										
												if ($autorisation_construction_taille) {
												
													if($coutPa == -1){
														// mise a jour des pa, or et charge du perso + xp/pi
														$sql = "UPDATE perso SET pa_perso='0', or_perso=or_perso-$coutOr, xp_perso=xp_perso+$gain_xp, pi_perso=pi_perso+$gain_xp, gain_xp_tour=gain_xp_tour+$gain_xp WHERE id_perso='$id_perso'";
														$mysqli->query($sql);
													}
													else {
														// mise a jour des pa, or et charge du perso + xp/pi
														$sql = "UPDATE perso SET pa_perso=pa_perso-$coutPa , or_perso=or_perso-$coutOr, xp_perso=xp_perso+$gain_xp, pi_perso=pi_perso+$gain_xp, gain_xp_tour=gain_xp_tour+$gain_xp WHERE id_perso='$id_perso'";
														$mysqli->query($sql);
													}

													gain_pc_chef($mysqli, $id_perso, $gain_pc);
													
													$pv_bat = rand($pvMin, $pvMin * 2);
													$img_bat = "b".$id_bat."".$bat_camp.".png";
													
													if ($id_bat == 4){
														// route
														// mise a jour de la carte
														$sql = "UPDATE $carte SET occupee_carte='0', fond_carte='$img_bat' WHERE x_carte=$x_bat AND y_carte=$y_bat";
														$mysqli->query($sql);							
													}
													else {
														// Récupération du terrain
														$sql = "SELECT fond_carte FROM $carte WHERE x_carte=$x_bat AND y_carte=$y_bat";
														$res = $mysqli->query($sql);
														$t = $res->fetch_assoc();
														
														$fond_carte_construction = $t['fond_carte'];
														
														if ($id_bat == 5) {// neutralité du pont
															$camp_perso = 0;
														}
														
														// mise a jour de la table instance_bat
														$sql = "INSERT INTO instance_batiment (niveau_instance, id_batiment, nom_instance, pv_instance, pvMax_instance, x_instance, y_instance, camp_instance, camp_origine_instance, contenance_instance, terrain_instance) 
																VALUES ('$niveau_bat', '$id_bat', '$nom_instance', '$pv_bat', '$pvMax', '$x_bat', '$y_bat', '$camp_perso', '$camp_perso', '$contenance_bat', '$fond_carte_construction')";
														$mysqli->query($sql);
														$id_i_bat = $mysqli->insert_id;
														
														// Cas particulier Ponts
														if ($id_bat == 5) {
															
															// mise a jour de la carte
															$sql = "UPDATE $carte SET occupee_carte='0', idPerso_carte='$id_i_bat', save_info_carte='$id_i_bat', fond_carte='$img_bat' WHERE x_carte='$x_bat' AND y_carte='$y_bat'";
															$mysqli->query($sql);
															
														} else {
														
															$img_bat_sup = $bat_camp.".png";
															
															for ($x = $x_bat - $taille_search; $x <= $x_bat + $taille_search; $x++) {
																for ($y = $y_bat - $taille_search; $y <= $y_bat + $taille_search; $y++) {
																	
																	// mise a jour de la carte
																	$sql = "UPDATE $carte SET occupee_carte='1', idPerso_carte='$id_i_bat', image_carte='$img_bat_sup' WHERE x_carte='$x' AND y_carte='$y'";
																	$mysqli->query($sql);
																	
																}
															}
														
															// mise a jour de la carte image centrale
															$sql = "UPDATE $carte SET occupee_carte='1', idPerso_carte='$id_i_bat', image_carte='$img_bat' WHERE x_carte='$x_bat' AND y_carte='$y_bat'";
															$mysqli->query($sql);
															
															if ($id_bat == '8') {
																// CANONS FORTIN
																
																if ($camp_perso == 1) {
																	$image_canon_g = 'canonG_nord.gif';
																	$image_canon_d = 'canonD_nord.gif';
																}
																
																if ($camp_perso == 2) {
																	$image_canon_g = 'canonG_sud.gif';
																	$image_canon_d = 'canonD_sud.gif';
																}
																
																// Canons Gauche
																$sql = "UPDATE carte SET image_carte='$image_canon_g' WHERE (x_carte=$x_bat - 1 AND y_carte=$y_bat - 1) OR (x_carte=$x_bat - 1 AND y_carte=$y_bat + 1)";
																$mysqli->query($sql);
																
																// Canons Droit
																$sql = "UPDATE carte SET image_carte='$image_canon_d' WHERE (x_carte=$x_bat + 1 AND y_carte=$y_bat - 1) OR (x_carte=$x_bat + 1 AND y_carte=$y_bat + 1)";
																$mysqli->query($sql);
																
																$sql = "INSERT INTO instance_batiment_canon (id_instance_bat, x_canon, y_canon, camp_canon) VALUES ('$id_i_bat', $x_bat - 1, $y_bat - 1, $camp_perso)";
																$mysqli->query($sql);
																$sql = "INSERT INTO instance_batiment_canon (id_instance_bat, x_canon, y_canon, camp_canon) VALUES ('$id_i_bat', $x_bat - 1, $y_bat + 1, $camp_perso)";
																$mysqli->query($sql);
																$sql = "INSERT INTO instance_batiment_canon (id_instance_bat, x_canon, y_canon, camp_canon) VALUES ('$id_i_bat', $x_bat + 1, $y_bat - 1, $camp_perso)";
																$mysqli->query($sql);
																$sql = "INSERT INTO instance_batiment_canon (id_instance_bat, x_canon, y_canon, camp_canon) VALUES ('$id_i_bat', $x_bat + 1, $y_bat + 1, $camp_perso)";
																$mysqli->query($sql);
															}
															else if ($id_bat == '9') {
																// CANONS FORT
																
																if ($camp_perso == 1) {
																	$image_canon_g = 'canonG_nord.gif';
																	$image_canon_d = 'canonD_nord.gif';
																}
																
																if ($camp_perso == 2) {
																	$image_canon_g = 'canonG_sud.gif';
																	$image_canon_d = 'canonD_sud.gif';
																}
																
																// Canons Gauche
																$sql = "UPDATE carte SET image_carte='$image_canon_g' WHERE (x_carte=$x_bat - 2 AND y_carte=$y_bat + 2) OR (x_carte=$x_bat - 2 AND y_carte=$y_bat) OR (x_carte=$x_bat - 2 AND y_carte=$y_bat - 2)";
																$mysqli->query($sql);
																
																// Canons Droit
																$sql = "UPDATE carte SET image_carte='$image_canon_d' WHERE (x_carte=$x_bat + 2 AND y_carte=$y_bat + 2) OR (x_carte=$x_bat + 2 AND y_carte=$y_bat) OR (x_carte=$x_bat + 2 AND y_carte=$y_bat - 2)";
																$mysqli->query($sql);
																
																$sql = "INSERT INTO instance_batiment_canon (id_instance_bat, x_canon, y_canon, camp_canon) VALUES ('$id_i_bat', $x_bat - 2, $y_bat + 2, $camp_perso)";
																$mysqli->query($sql);
																$sql = "INSERT INTO instance_batiment_canon (id_instance_bat, x_canon, y_canon, camp_canon) VALUES ('$id_i_bat', $x_bat - 2, $y_bat, $camp_perso)";
																$mysqli->query($sql);
																$sql = "INSERT INTO instance_batiment_canon (id_instance_bat, x_canon, y_canon, camp_canon) VALUES ('$id_i_bat', $x_bat - 2, $y_bat - 2, $camp_perso)";
																$mysqli->query($sql);
																$sql = "INSERT INTO instance_batiment_canon (id_instance_bat, x_canon, y_canon, camp_canon) VALUES ('$id_i_bat', $x_bat + 2, $y_bat + 2, $camp_perso)";
																$mysqli->query($sql);
																$sql = "INSERT INTO instance_batiment_canon (id_instance_bat, x_canon, y_canon, camp_canon) VALUES ('$id_i_bat', $x_bat + 2, $y_bat, $camp_perso)";
																$mysqli->query($sql);
																$sql = "INSERT INTO instance_batiment_canon (id_instance_bat, x_canon, y_canon, camp_canon) VALUES ('$id_i_bat', $x_bat + 2, $y_bat - 2, $camp_perso)";
																$mysqli->query($sql);
															}
															else if ($id_bat == '11') {
																// Gare 
																
																// Est ce que la gare est connectée à des rails ?
																$sql = "SELECT x_carte, y_carte, occupee_carte, idPerso_carte, image_carte 
																		FROM carte WHERE x_carte >= $x_bat -2 AND x_carte <= $x_bat + 2 AND y_carte >= $y_bat - 2 AND y_carte <= $y_bat + 2 
																		AND (fond_carte='rail.gif' OR fond_carte='rail_1.gif' OR fond_carte='rail_2.gif' OR fond_carte='rail_3.gif' OR fond_carte='rail_4.gif' OR fond_carte='rail_5.gif' OR fond_carte='rail_7.gif' OR fond_carte='railP.gif')";
																$res = $mysqli->query($sql);
																$nb_connections = $res->num_rows;
																
																if ($nb_connections > 0) {
																
																	$tab_rail = array();
																	
																	$trouve = false;
																
																	while ($t = $res->fetch_assoc()) {
																		
																		$x_rail 		= $t["x_carte"];
																		$y_rail 		= $t["y_carte"];
																		$occ_rail		= $t["occupee_carte"];
																		$idPerso_rail	= $t["idPerso_carte"];
																		$image_on_rail	= $t["image_carte"];
																		
																		// Coordonnées rail
																		$coord_rail = $x_rail.";".$y_rail;
																		array_push($tab_rail, $coord_rail);
																		
																		if (($camp_perso == 1 && $image_on_rail == 'b12b.png') || ($camp_perso == 2 && $image_on_rail == 'b12r.png')) {
																			
																			// On a trouvé un train du même camp que la gare construite
																			$trouve = true;
																			
																			$sql_t = "SELECT id_gare1, id_gare2, direction FROM liaisons_gare WHERE id_train='$idPerso_rail'";
																			$res_t = $mysqli->query($sql_t);
																			$t_t = $res_t->fetch_assoc();
																			
																			$id_gare1 	= $t_t['id_gare1'];
																			$id_gare2 	= $t_t['id_gare2'];
																			$direction 	= $t_t['direction'];
																			
																			// Est-ce que la gare 1 existe toujours ?
																			$sql_e1 = "SELECT * FROM instance_batiment WHERE id_instanceBat = '$id_gare1'";
																			$res_e1 = $mysqli->query($sql_e1);
																			$existe_gare1 = $res_e1->num_rows;
																			
																			if (!$existe_gare1) {
																				if ($direction == $id_gare1) {
																					// On met à jour gare1 ET direction
																					$sql = "UPDATE liaisons_gare SET id_gare1='$id_i_bat', direction='$id_i_bat' WHERE id_train='$idPerso_rail'";
																					$mysqli->query($sql);
																				}
																				else {
																					// On met à jour gare1
																					$sql = "UPDATE liaisons_gare SET id_gare1='$id_i_bat' WHERE id_train='$idPerso_rail'";
																					$mysqli->query($sql);
																				}
																			}
																			else {
																				if ($direction == $id_gare2) {
																					// On met à jour gare2 ET direction
																					$sql = "UPDATE liaisons_gare SET id_gare2='$id_i_bat', direction='$id_i_bat' WHERE id_train='$idPerso_rail'";
																					$mysqli->query($sql);
																				}
																				else {
																					// On met à jour gare2
																					$sql = "UPDATE liaisons_gare SET id_gare2='$id_i_bat' WHERE id_train='$idPerso_rail'";
																					$mysqli->query($sql);
																				}
																			}
																		}
																		else {												
																			
																			$num_res = 1;
																			
																			while ($image_on_rail != 'b12b.png' && $image_on_rail != 'b12r.png' && $num_res > 0) {
																				
																				// On cherche un train sur le chemin des rails
																				$sql_r = "SELECT x_carte, y_carte, occupee_carte, idPerso_carte, image_carte FROM carte 
																						WHERE x_carte >= $x_rail - 1 AND x_carte <= $x_rail + 1 AND y_carte >= $y_rail - 1 AND y_carte <= $y_rail + 1
																						AND coordonnees NOT IN ( '" . implode( "', '" , $tab_rail ) . "' )
																						AND (fond_carte='rail.gif' OR fond_carte='rail_1.gif' OR fond_carte='rail_2.gif' OR fond_carte='rail_3.gif' OR fond_carte='rail_4.gif' OR fond_carte='rail_5.gif' OR fond_carte='rail_7.gif' OR fond_carte='railP.gif')";
																				$res_r = $mysqli->query($sql_r);
																				$num_res = $res_r->num_rows;
																				
																				$t_r = $res_r->fetch_assoc();
																			
																				$x_rail 		= $t_r['x_carte'];
																				$y_rail 		= $t_r['y_carte'];
																				$occ_rail		= $t_r["occupee_carte"];
																				$idPerso_rail	= $t_r["idPerso_carte"];
																				$image_on_rail	= $t_r["image_carte"];
																				
																				// Ajout coordonnées dans tableau des coordonnées des rails
																				$coord_rail = $x_rail.";".$y_rail;
																				array_push($tab_rail, $coord_rail);												
																			}
																			
																			if (($camp_perso == 1 && $image_on_rail == 'b12b.png') || ($camp_perso == 2 && $image_on_rail == 'b12r.png')) {
																			
																				// On a trouvé un train du même camp que la gare construite
																				$trouve = true;
																				
																				$sql_t = "SELECT id_gare1, id_gare2, direction FROM liaisons_gare WHERE id_train='$idPerso_rail'";
																				$res_t = $mysqli->query($sql_t);
																				$t_t = $res_t->fetch_assoc();
																				
																				$id_gare1 	= $t_t['id_gare1'];
																				$id_gare2 	= $t_t['id_gare2'];
																				$direction 	= $t_t['direction'];
																				
																				// Est-ce que la gare 1 existe toujours ?
																				$sql_e1 = "SELECT * FROM instance_batiment WHERE id_instanceBat = '$id_gare1'";
																				$res_e1 = $mysqli->query($sql_e1);
																				$existe_gare1 = $res_e1->num_rows;
																				
																				if (!$existe_gare1) {
																					if ($direction == $id_gare1) {
																						// On met à jour gare1 ET direction
																						$sql = "UPDATE liaisons_gare SET id_gare1='$id_i_bat', direction='$id_i_bat' WHERE id_train='$idPerso_rail'";
																						$mysqli->query($sql);
																					}
																					else {
																						// On met à jour gare1
																						$sql = "UPDATE liaisons_gare SET id_gare1='$id_i_bat' WHERE id_train='$idPerso_rail'";
																						$mysqli->query($sql);
																					}
																				}
																				else {
																					if ($direction == $id_gare2) {
																						// On met à jour gare2 ET direction
																						$sql = "UPDATE liaisons_gare SET id_gare2='$id_i_bat', direction='$id_i_bat' WHERE id_train='$idPerso_rail'";
																						$mysqli->query($sql);
																					}
																					else {
																						// On met à jour gare2
																						$sql = "UPDATE liaisons_gare SET id_gare2='$id_i_bat' WHERE id_train='$idPerso_rail'";
																						$mysqli->query($sql);
																					}
																				}
																			}
																		}
																	}
																	
																	if (!$trouve) {
																		
																		// Est ce que la gare est connectée à des rails ?
																		$sql = "SELECT x_carte, y_carte, occupee_carte, idPerso_carte, image_carte FROM carte 
																				WHERE x_carte >= $x_bat - 2 AND x_carte <= $x_bat + 2 AND y_carte >= $y_bat - 2 AND y_carte <= $y_bat + 2 
																				AND (fond_carte='rail.gif' OR fond_carte='rail_1.gif' OR fond_carte='rail_2.gif' OR fond_carte='rail_3.gif' OR fond_carte='rail_4.gif' OR fond_carte='rail_5.gif' OR fond_carte='rail_7.gif' OR fond_carte='railP.gif')";
																		$res = $mysqli->query($sql);
																		$nb_connections = $res->num_rows;
																		
																		if ($nb_connections > 0) {
																			
																			while ($t = $res->fetch_assoc()) {
																		
																				$x_rail 		= $t["x_carte"];
																				$y_rail 		= $t["y_carte"];
																		
																				// On n'a pas trouvé de train sur les rails
																				// Est ce qu'on trouve une gare liée par les rails à cette nouvelle gare ?																		
																				$num_res = 1;
																				
																				$tab_rail2 = array();
																				
																				while ($image_on_rail != 'b.png' && $image_on_rail != 'r.png' && $num_res > 0) {
																						
																					// On cherche un train sur le chemin des rails
																					$sql_r = "SELECT x_carte, y_carte, occupee_carte, idPerso_carte, image_carte FROM carte 
																							WHERE x_carte >= $x_rail - 1 AND x_carte <= $x_rail + 1 AND y_carte >= $y_rail - 1 AND y_carte <= $y_rail + 1
																							AND coordonnees NOT IN ( '" . implode( "', '" , $tab_rail2 ) . "' )
																							AND (fond_carte='rail.gif'  OR fond_carte='rail_1.gif' OR fond_carte='rail_2.gif' OR fond_carte='rail_3.gif' OR fond_carte='rail_4.gif' OR fond_carte='rail_5.gif' OR fond_carte='rail_7.gif' OR fond_carte='railP.gif' OR image_carte='r.png' OR image_carte='b.png')
																							AND (idPerso_carte != '$id_i_bat' OR idPerso_carte IS NULL)";
																					$res_r = $mysqli->query($sql_r);
																					$num_res = $res_r->num_rows;
																					
																					$t_r = $res_r->fetch_assoc();
																				
																					$x_rail 		= $t_r['x_carte'];
																					$y_rail 		= $t_r['y_carte'];
																					$occ_rail		= $t_r["occupee_carte"];
																					$idPerso_rail	= $t_r["idPerso_carte"];
																					$image_on_rail	= $t_r["image_carte"];
																					
																					// Ajout coordonnées dans tableau des coordonnées des rails
																					$coord_rail = $x_rail.";".$y_rail;
																					array_push($tab_rail2, $coord_rail);
																				}
																				
																				if (($camp_perso == 1 && $image_on_rail == 'b.png') || ($camp_perso == 2 && $image_on_rail == 'r.png')) {
																					
																					// On a trouvé un batiment du même camp que la gare construite
																					$trouve = true;
																					
																					// Récupération des infos du bâtiment rencontré
																					$sql_b = "SELECT * FROM instance_batiment WHERE id_instanceBat='$idPerso_rail'";
																					$res_b = $mysqli->query($sql_b);
																					$t_b = $res_b->fetch_assoc();
																					
																					$id_bat_instance	= $t_b['id_batiment'];
																					$camp_instance		= $t_b['camp_instance'];
																					
																					// La batiment rencontré est bien une gare du même camp que la gare construite
																					if ($id_bat_instance == '11' && $camp_instance == $camp_perso) {
																						
																						// Création de la liaison
																						$sql = "INSERT INTO liaisons_gare (id_gare1, id_gare2, id_train, direction) VALUES ('$id_i_bat', '$idPerso_rail', NULL, '$idPerso_rail')";
																						$mysqli->query($sql);
																						
																					}
																				}
																			}
																		}
																	}
																}
															}
														}
													}
													
													// recuperation des infos du perso
													$sql = "SELECT nom_perso, clan FROM perso WHERE id_perso='$id_perso'";
													$res = $mysqli->query($sql);
													$t_p = $res->fetch_assoc();
													$nom_perso = $t_p["nom_perso"];
													$camp = $t_p["clan"];
													
													// recuperation de la couleur du camp du perso
													$couleur_clan_perso = couleur_clan($camp);
													
													// route et pont
													if($id_bat == 4){
														//mise a jour de la table evenement
														$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>','a construit <b>$nom_bat</b>',NULL,'','',NOW(),'0')";
														$mysqli->query($sql);
													}
													else {
														//mise a jour de la table evenement
														$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>','a construit ','$id_i_bat','<font color=$couleur_clan_perso><b>$nom_bat</b></font>',' (Gain xp : $gain_xp)',NOW(),'0')";
														$mysqli->query($sql);
													}
													
													return 1;
												}
												else {
													echo "<center>Vous ne pouvez pas construire ce bâtiment car la carte est occupée ou le terrain n'est pas que de la plaine<br />";
													echo "<a href='jouer.php' class='btn btn-primary'>retour</a></center>";
													
													return 0;
												}
											}	
											else {
												echo "<center>Vous ne pouvez pas construire ce bâtiment car la contrainte sur la distance avec un autre batiment n'a pas été respecté<br />";
												echo "<a href='jouer.php' class='btn btn-primary'>retour</a></center>";
												
												return 0;
											}
										}
										else {
											echo "<center>Vous ne pouvez pas construire ce bâtiment car la contrainte du nombre d'ennemis présent autour de la zone de construction n'a pas été respecté. Veuillez nettoyer la zone !<br />";
											echo "<a href='jouer.php' class='btn btn-primary'>retour</a></center>";
											
											return 0;
										}
									}
									else {
										echo "<center>Vous ne pouvez pas construire ce bâtiment car la contrainte du nombre d'unités de Génie Civil qui doit être présente n'a pas été respecté<br />";
										echo "<a href='jouer.php' class='btn btn-primary'>retour</a></center>";
										
										return 0;
									}
								}
								else {
									// Tentative de triche
									$text_triche = "Tentative construction sur case non permise";
				
									$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id_perso', '$text_triche')";
									$mysqli->query($sql);
								
									echo "<center>Impossible de faire une construction sur cette case<br />";
									echo "<a href='jouer.php' class='btn btn-primary'>retour</a></center>";
									
									return 0;
								}
							}
							else {
								// Tentative de triche
								$text_triche = "Tentative construction reservé génie sans être du génie";
			
								$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id_perso', '$text_triche')";
								$mysqli->query($sql);
							
								echo "<center>Vous devez appartenir au Génie pour effectuer ce type de construction<br />";
								echo "<a href='jouer.php' class='btn btn-primary'>retour</a></center>";
								
								return 0;
							}
						}
						else {
							// Tentative de triche
							$text_triche = "Tentative construction batiment sur coordonnées en dehors des cases permises";
			
							$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id_perso', '$text_triche')";
							$mysqli->query($sql);
							
							echo "<center>Vous ne pouvez pas construire ce bâtiment sur ces coodonnées<br />";
							echo "<a href='jouer.php' class='btn btn-primary'>retour</a></center>";
							
							return 0;
						}
					}
					else {
						// Tentative de triche
						$text_triche = "Tentative construction batiment sur case interdite";
			
						$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id_perso', '$text_triche')";
						$mysqli->query($sql);
						
						echo "<center>Vous ne pouvez pas construire ce bâtiment la case cible est occupée ou hors carte<br />";
						echo "<a href='jouer.php' class='btn btn-primary'>retour</a></center>";
						
						return 0;
					}
				}
				else {
					echo "<center>Vous n'avez pas assez d'or pour construire ce batiment<br />";
					echo "<a href='jouer.php' class='btn btn-primary'>retour</a></center>";
					return 0;
				}
			}
			else {
				echo "<center>Vous n'avez pas assez de PA<br />";
				echo "<a href='jouer.php' class='btn btn-primary'>retour</a></center>";
			}
		}
		else {
			echo "<center>Construction impossible<br />";
			echo "<a href='jouer.php' class='btn btn-primary'>retour</a></center>";
		}
	}
	else {
		echo "<center>Vous n'avez pas choisi de batiment<br />";
		echo "<a href='jouer.php' class='btn btn-primary'>retour</a></center>";
	}
}

/**
  * Fonction qui permet de reparer un batiment
  * @param $id_perso	: L'identifiant du perso qui repare le batiment
  * @param $id_cible	: L'identifiant de l'instance du batiment cible de la reparation
  * @param $id_action	: L'identifiant de l'action pour recuperer le niveau de reparation
  * @return Void
  */
function action_reparer_bat($mysqli, $id_perso, $id_cible, $id_action){
	
	// recuperation des donnees correspondant a l'action
	$sql = "SELECT coutPa_action FROM action WHERE id_action=$id_action";
	$res = $mysqli->query($sql);
	$t_reparer = $res->fetch_assoc();
	$coutPa = $t_reparer["coutPa_action"];
	
	// recuperation des infos du perso
	$sql = "SELECT nom_perso, pa_perso, type_perso, clan, gain_xp_tour, genie FROM perso WHERE id_perso='$id_perso'";
	$res = $mysqli->query($sql);
	$t_i_perso = $res->fetch_assoc();
	
	$nom_perso 	= $t_i_perso['nom_perso'];
	$pa_perso 	= $t_i_perso['pa_perso'];
	$type_perso	= $t_i_perso['type_perso'];
	$camp_perso = $t_i_perso['clan'];
	$genie_perso= $t_i_perso['genie'];
	$gain_xp_tour_perso	= $t_i_perso['gain_xp_tour'];
	
	if ($gain_xp_tour_perso >= 20) {
		$max_xp_tour_atteint = true;
	}
	else {
		$max_xp_tour_atteint = false;
	}
	
	// recuperation de la couleur du camp du perso
	$couleur_clan_perso = couleur_clan($camp_perso);

	$verif_anti_zerk = gestion_anti_zerk($mysqli, $id_perso);
	
	// Les chiens ne peuvent pas réparer les bâtiments
	if ($type_perso != '6' && $verif_anti_zerk) {
	
		// test pa
		if($pa_perso >= $coutPa){
			
			// recuperation des infos de la cible
			$sql = "SELECT nom_batiment, pv_instance, pvMax_instance, camp_instance FROM instance_batiment, batiment WHERE id_instanceBat='$id_cible' AND batiment.id_batiment = instance_batiment.id_batiment";
			$res = $mysqli->query($sql);
			$t_i_cible = $res->fetch_assoc();
			$nom_cible = $t_i_cible['nom_batiment'];
			$pv_instance_bat = $t_i_cible['pv_instance'];
			$pv_max_bat = $t_i_cible['pvMax_instance'];
			$camp_bat = $t_i_cible['camp_instance'];
			
			// recuperation de la couleur du camp du batiment
			$couleur_clan_cible = couleur_clan($camp_bat);
			
			//calcul des reparations
			$pv_reparation = calcul_pv_reparation($id_action);
			
			// Récupération si appartient génie civil
			$sql = "SELECT count(id_perso) as verif_gc FROM perso_in_compagnie, compagnies 
					WHERE compagnies.id_compagnie = perso_in_compagnie.id_compagnie
					AND id_perso='$id_perso'
					AND compagnies.genie_civil='1'";
			$res = $mysqli->query($sql);
			$t_gc = $res->fetch_assoc();
			
			$verif_gc = $t_gc['verif_gc'];
			
			if ($verif_gc && $genie_perso == 1) {
				$pv_reparation *= 2;
			}
			
			// traitement reparation
			if($pv_instance_bat < $pv_max_bat){
			
				// calcul gain xp
				$gain_xp = rand(1,3);

				if ($verif_gc && $genie_perso == 1) {
					$gain_xp = rand(2,4);
				}

				if($camp_bat != $camp_perso){
					$gain_xp = 1;
				}
				
				if ($gain_xp_tour_perso + $gain_xp > 20) {
					$gain_xp = 20 - $gain_xp_tour_perso;
					$max_xp_tour_atteint = true;
				}
			
				if($pv_instance_bat + $pv_reparation < $pv_max_bat){
					//MAJ pv cible
					$sql = "UPDATE instance_batiment SET pv_instance=pv_instance+$pv_reparation WHERE id_instanceBat='$id_cible'";
					$mysqli->query($sql);
					
					//MAJ xp/pi perso et pa
					$sql = "UPDATE perso SET xp_perso=xp_perso+$gain_xp, pi_perso=pi_perso+$gain_xp, gain_xp_tour=gain_xp_tour+$gain_xp, pa_perso=pa_perso-$coutPa WHERE id_perso='$id_perso'";
					$mysqli->query($sql);
						
					//MAJ evenments perso
					$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>',' a reparé le batiment ','$id_cible','<font color=$couleur_clan_cible><b>$nom_cible</b></font>',' : reparation de $pv_reparation PV',NOW(),'0')";
					$mysqli->query($sql);
						
					echo "<center>Vous avez réparé <font color=$couleur_clan_cible>$nom_cible</font> de $pv_reparation PV</center><br />";
					echo "<center>Vous avez gagné $gain_xp XP";
					if ($max_xp_tour_atteint) {
						echo " (maximum de gain d'xp par tour atteint).";
					}
					echo "</center>";
				}
				else {
					// on met aux pvMax de la cible
					$sql = "UPDATE instance_batiment SET pv_instance=pvMax_instance WHERE id_instanceBat='$id_cible'";
					$mysqli->query($sql);
						
					//MAJ xp/pi/pa perso
					$sql = "UPDATE perso SET xp_perso=xp_perso+$gain_xp, pi_perso=pi_perso+$gain_xp, gain_xp_tour=gain_xp_tour+$gain_xp, pa_perso=pa_perso-$coutPa WHERE id_perso='$id_perso'";
					$mysqli->query($sql);
						
					//MAJ evenments perso
					$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>',' a reparé le batiment ','$id_cible','<font color=$couleur_clan_cible><b>$nom_cible</b></font>',' : reparation de $pv_reparation PV',NOW(),'0')";
					$mysqli->query($sql);
						
					echo "<center>Vous avez réparé <font color=$couleur_clan_cible>$nom_cible</font> de $pv_reparation PV</center><br />";
					echo "<center>La cible est revenu à son max de vie</center><br />";
					echo "<center>Vous avez gagné $gain_xp XP";
					if ($max_xp_tour_atteint) {
						echo " (maximum de gain d'xp par tour atteint).";
					}
					echo "</center>";
				}
			}
			else {
				// cible deja au max
				$gain_xp = 1;
				
				if ($gain_xp_tour_perso + $gain_xp > 20) {
					$gain_xp = 0;
					$max_xp_tour_atteint = true;
				}
					
				//MAJ xp/pi/pa perso
				$sql = "UPDATE perso SET xp_perso=xp_perso+$gain_xp, pi_perso=pi_perso+$gain_xp, gain_xp_tour=gain_xp_tour+$gain_xp, pa_perso=pa_perso-$coutPa WHERE id_perso='$id_perso'";
				$mysqli->query($sql);
					
				//MAJ evenments perso
				$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>',' a fait une révision sur le batiment ','$id_cible','<font color=$couleur_clan_cible><b>$nom_cible</b></font>',' : pv déjà au max...',NOW(),'0')";
				$mysqli->query($sql);
					
				echo "<center>La cible est était déjà à son max de vie</center><br />";
				echo "<center>Vous avez gagné $gain_xp XP";
				if ($max_xp_tour_atteint) {
					echo " (maximum de gain d'xp par tour atteint).";
				}
				echo "</center>";
			}
		}
		else {
			header("Location:jouer.php?erreur=pa");
		}
	}
	else if ($type_perso == 6){
		$text_triche = "Tentative réparation bâtiment avec chien";
			
		$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id_perso', '$text_triche')";
		$mysqli->query($sql);
		
		echo "<center><font color='red'>Les chiens ne peuvent pas réparer les bâtiments...</font></center><br />";
	}
	else if (!$verif_anti_zerk){
		echo "<center><font color='red'>Loi anti-zerk non respectée !</font></center><br />";
	}
	echo "<br /><br /><center><a href='jouer.php' class='btn btn-primary'>retour</a></center>";
}

/**
  * Fonction qui permet d'effectuer des soin de malus sur une cible
  * @param $id_perso		: l'identifiant du personnage effectuant les soins
  * @param $id_ciblea		: L'identiiant du pnj ou pj (cible) recevant les soins
  * @param $id_action		: L'identifiant de l'action de soin afin de recuperer le niveau de competence
  * @param $id_objet_soin	: L'identifiant de l'objet qu'on va utiliser pour ameliorer les soins
  * @return Void
  */
function action_soin_malus($mysqli, $id_perso, $id_cible, $id_action, $id_objet_soin){
	
	// reuperation des donnees correspondant a l'action
	$sql = "SELECT coutPa_action FROM action WHERE id_action=$id_action";
	$res = $mysqli->query($sql);
	$t_soin = $res->fetch_assoc();
	$coutPa = $t_soin["coutPa_action"];
	
	// calcul recup malus
	$recup_malus = calcul_recup_malus($id_action);
	
	//recuperation des infos sur le perso
	$sql = "SELECT nom_perso, clan, pa_perso, gain_xp_tour FROM perso WHERE id_perso='$id_perso'";
	$res = $mysqli->query($sql);
	$t_p = $res->fetch_assoc();
	
	$nom_perso 	= $t_p["nom_perso"];
	$pa_perso 	= $t_p["pa_perso"];
	$camp 		= $t_p["clan"];
	$gain_xp_tour_perso	= $t_p["gain_xp_tour"];
	
	$max_xp_tour_atteint = false;
	
	// recupetion de la couleur du camp du perso
	$couleur_clan_perso = couleur_clan($camp);
	
	// test pa
	if($pa_perso >= $coutPa){
		// recuperation des malus du perso cible
		$sql = "SELECT nom_perso, bonus_perso, pv_perso, pvMax_perso, clan FROM perso WHERE id_perso='$id_cible'";
		$res = $mysqli->query($sql);
		$t_pv = $res->fetch_assoc();
		
		$nom_cible = $t_pv['nom_perso'];
		$pv_cible = $t_pv['pv_perso'];
		$pvMax_cible = $t_pv['pvMax_perso'];
		$bonus_cible = $t_pv['bonus_perso'];
		$camp_cible = $t_pv['clan'];
		
		// recuperation de la couleur du camp du perso
		$couleur_clan_cible = couleur_clan($camp_cible);
		
		// calcul gain xp
		$gain_xp = gain_xp($camp, $camp_cible);
		$gain_xp = min($gain_xp, 5);
		
		// Si on soigne les malus d'un perso autre que soi meme
		if($id_perso != $id_cible){
			$gain_xp = $gain_xp * 2;
		}
		
		if ($gain_xp_tour_perso + $gain_xp > 20) {
			$gain_xp = 20 - $gain_xp_tour_perso;
			$max_xp_tour_atteint = true;
		}
		
		$bonus_recup_s = 0;
		
		if($id_objet_soin > 0){
			// recuperation des avantages de l'objet de soin
			$sql_s = "SELECT nom_objet, bonusPv_objet, bonusRecup_objet FROM objet WHERE id_objet='$id_objet_soin'";
			$res_s = $mysqli->query($sql_s);
			$t_s = $res_s->fetch_assoc();
			$nom_objet = $t_s['nom_objet'];
			$bonus_recup_s = $t_s['bonusRecup_objet'];
		}
			
		// traitement soin malus
		if($id_objet_soin == 12){
			$bonus_recup_final = $bonus_recup_s + $recup_malus;		
		}
		else {
			$bonus_recup_final = $recup_malus;
		}
		
		if($bonus_cible < 0){
			if($bonus_cible + $recup_malus <= 0){
				//MAJ pv et recup cible
				$sql = "UPDATE perso SET bonus_perso=bonus_perso+$bonus_recup_final, bonusRecup_perso=bonusRecup_perso+$bonus_recup_s WHERE id_perso='$id_cible'";
				$mysqli->query($sql);
			}
			else {
				//MAJ pv et recup cible
				$sql = "UPDATE perso SET bonus_perso=0, bonusRecup_perso=bonusRecup_perso+$bonus_recup_s WHERE id_perso='$id_cible'";
				$mysqli->query($sql);
				
				echo "<center>La cible a complétement récupérée de ses malus</center><br />";
			}
			
			echo "<center>Vous avez apaisé <font color=$couleur_clan_cible>$nom_cible</font> de $bonus_recup_final</center><br />";
			
			if($bonus_recup_s){
				if($id_objet_soin != 12){
					echo "<center>Vous avez augmenté la récupération de <font color=$couleur_clan_cible>$nom_cible</font> de $bonus_recup_s</center><br />";
				}
			}
			
			//MAJ evenements perso
			$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>',' a apaisé ','$id_cible','<font color=$couleur_clan_cible><b>$nom_cible</b></font>',' : apaisement de $bonus_recup_final',NOW(),'0')";
			$mysqli->query($sql);
		}
		else {
			// cible n'ayant pas de malus
			$gain_xp = 1;
			
			if ($gain_xp_tour_perso + $gain_xp > 20) {
				$gain_xp = 0;
				$max_xp_tour_atteint = true;
			}
			
			echo "<center>La cible n'avait pas de malus...</center><br />";
			
			if($bonus_recup_s){
				if($id_objet_soin != 12){
					echo "<center>Vous avez augmenté la récupération de <font color=$couleur_clan_cible>$nom_cible</font> de $bonus_recup_s</center><br />";
				}
			}
			
			//MAJ evenements perso
			$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>',' a tenté d\'apaiser ','$id_cible','<font color=$couleur_clan_cible><b>$nom_cible</b></font>',' : cible sans malus',NOW(),'0')";
			$mysqli->query($sql);
		}
		
		if($id_objet_soin > 0){
			// Suppression de l'objet
			$sql_d = "DELETE FROM perso_as_objet WHERE id_objet='$id_objet_soin' AND id_perso='$id_perso' LIMIT 1";
			$mysqli->query($sql_d);
		}
		
		//MAJ xp/pi perso et pa
		$sql = "UPDATE perso SET xp_perso=xp_perso+$gain_xp, pi_perso=pi_perso+$gain_xp, gain_xp_tour=gain_xp_tour+$gain_xp, pa_perso=pa_perso-$coutPa WHERE id_perso='$id_perso'";
		$mysqli->query($sql);
		
		echo "<center>Vous avez gagné $gain_xp XP";
		if ($max_xp_tour_atteint) {
			echo " (maximum de gain d'xp par tour atteint)";
		}
		echo "</center>";
		
	}
	else {
		echo "<center>Vous n'avez pas assez de PA</center><br />";
	}
	echo "<br /><br /><center><a href='jouer.php' class='btn btn-primary'>retour</a></center>";
}


/**
  * Fonction qui permet d'effectuer des soin sur une cible
  * @param $id_perso	: l'identifiant du personnage effectuant les soins
  * @param $id_ciblea	: L'identiiant du pnj ou pj (cible) recevant les soins
  * @param $id_action	: L'identifiant de l'action de soin afin de recuperer le niveau de competence
  * @param $id_objet_soin	: L'identifiant de l'objet qu'on va utiliser pour ameliorer les soins
  * @return Void
  */
function action_soin($mysqli, $id_perso, $id_cible, $id_action, $id_objet_soin){
	
	// recuperation des donnees correspondant a l'action
	$sql = "SELECT pvMin_action, pvMax_action, coutPa_action FROM action WHERE id_action=$id_action";
	$res = $mysqli->query($sql);
	$t_soin = $res->fetch_assoc();
	
	$pvMin_soin = $t_soin["pvMin_action"];
	$pvMax_soin = $t_soin["pvMax_action"];
	$coutPa = $t_soin["coutPa_action"];
	
	// calcul pv
	$pv_soin = rand($pvMin_soin, $pvMax_soin);
	
	// recuperation du pourcentage
	$pourcent = calcul_pourcentage_action($id_action);
	
	//recuperation des infos sur le perso
	$sql = "SELECT nom_perso, clan, pa_perso, gain_xp_tour FROM perso WHERE id_perso='$id_perso'";
	$res = $mysqli->query($sql);
	$t_p = $res->fetch_assoc();
	
	$nom_perso 	= $t_p["nom_perso"];
	$pa_perso 	= $t_p["pa_perso"];
	$camp 		= $t_p["clan"];
	$gain_xp_tour_perso = $t_p["gain_xp_tour"];
	
	if ($gain_xp_tour_perso >= 20) {
		$max_xp_tour_atteint = true;
	}
	else {
		$max_xp_tour_atteint = false;
	}
	
	// recuperation de la couleur du camp du perso
	$couleur_clan_perso = couleur_clan($camp);
	
	// test pa
	if($pa_perso >= $coutPa){
	
		// recuperation des pv du perso cible
		$sql = "SELECT nom_perso, pv_perso, pvMax_perso, clan FROM perso WHERE id_perso='$id_cible'";
		$res = $mysqli->query($sql);
		$t_pv = $res->fetch_assoc();
		
		$nom_cible = $t_pv['nom_perso'];
		$pv_cible = $t_pv['pv_perso'];
		$pvMax_cible = $t_pv['pvMax_perso'];
		$camp_cible = $t_pv['clan'];
		
		// recuperation de la couleur du camp du perso
		$couleur_clan_cible = couleur_clan($camp_cible);
		
		// calcul gain xp
		$gain_xp = gain_xp($camp, $camp_cible);
		$gain_xp = min($gain_xp, 5);
		
		// Si on soigne un perso autre que soi meme
		if($id_perso != $id_cible){
			$gain_xp = $gain_xp * 2;
		}
		
		if ($gain_xp_tour_perso + $gain_xp > 20) {
			$gain_xp = 20 - $gain_xp_tour_perso;
			$max_xp_tour_atteint = true;
		}
		
		$bonus_pv_s = 0;
		$bonus_recup_s = 0;
		$bonus_pourcent = 0;
		
		if($id_objet_soin > 0){
			// recuperation des avantages de l'objet de soin
			$sql_s = "SELECT nom_objet, bonusPv_objet, bonusRecup_objet FROM objet WHERE id_objet='$id_objet_soin'";
			$res_s = $mysqli->query($sql_s);
			$t_s = $res_s->fetch_assoc();
			$nom_objet = $t_s['nom_objet'];
			$bonus_pv_s = $t_s['bonusPv_objet'];
			$bonus_recup_s = $t_s['bonusRecup_objet'];
		}
		
		if($id_objet_soin == 13)
			$bonus_pourcent = 10;
		
		// calcul pourcentage de pv en moins du perso
		$pourcent_pv_cible = (100 / $pvMax_cible)*($pvMax_cible - $pv_cible);
		
		if($pourcent_pv_cible <= $pourcent + $bonus_pourcent){
			// traitement soin
			if($pv_cible < $pvMax_cible){
				if($pv_cible + $pv_soin + $bonus_pv_s < $pvMax_cible){
					// calcul pv soin total
					$pv_soin_final = $pv_soin + $bonus_pv_s;
					
					if($id_objet_soin == 12){
						//MAJ pv et malus cible
						$sql = "UPDATE perso SET pv_perso=pv_perso+$pv_soin_final, bonus_perso=bonus_perso+$bonus_recup_s WHERE id_perso='$id_cible'";
						$mysqli->query($sql);
					}
					else {
						//MAJ pv et recup cible
						$sql = "UPDATE perso SET pv_perso=pv_perso+$pv_soin_final, bonusRecup_perso=bonusRecup_perso+$bonus_recup_s WHERE id_perso='$id_cible'";
						$mysqli->query($sql);
					}
					
					//MAJ xp/pi perso et pa
					$sql = "UPDATE perso SET xp_perso=xp_perso+$gain_xp, pi_perso=pi_perso+$gain_xp, gain_xp_tour=gain_xp_tour+$gain_xp, pa_perso=pa_perso-$coutPa WHERE id_perso='$id_perso'";
					$mysqli->query($sql);
					
					//MAJ evenments perso
					$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>',' a soigné ','$id_cible','<font color=$couleur_clan_cible><b>$nom_cible</b></font>',' : soin de $pv_soin_final PV',NOW(),'0')";
					$mysqli->query($sql);
					
					echo "<center>Vous avez soigné <font color=$couleur_clan_cible>$nom_cible</font> de $pv_soin (+$bonus_pv_s) PV</center><br />";
					if($bonus_recup_s){
						if($id_objet_soin == 12){
							echo "<center>Vous avez soigner les malus de <font color=$couleur_clan_cible>$nom_cible</font> de $bonus_recup_s</center><br />";
						}
						else {
							echo "<center>Vous avez augmenté la récupération de <font color=$couleur_clan_cible>$nom_cible</font> de $bonus_recup_s</center><br />";
						}
					}
					echo "<center>Vous avez gagné $gain_xp XP";
					if ($max_xp_tour_atteint) {
						echo " (maximum de gain d'xp par tour atteint)";
					}
					echo ".</center>";
				}
				else {
					// on met aux pvMax de la cible
					$sql = "UPDATE perso SET pv_perso=pvMax_perso WHERE id_perso='$id_cible'";
					$mysqli->query($sql);
					
					//MAJ xp perso
					$sql = "UPDATE perso SET xp_perso=xp_perso+$gain_xp, pi_perso=pi_perso+$gain_xp, gain_xp_tour=gain_xp_tour+$gain_xp, pa_perso=pa_perso-$coutPa WHERE id_perso='$id_perso'";
					$mysqli->query($sql);
					
					//MAJ evenments perso
					$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>',' a soigné ','$id_cible','<font color=$couleur_clan_cible><b>$nom_cible</b></font>',' : soin de $pv_soin PV',NOW(),'0')";
					$mysqli->query($sql);
					
					echo "<center>Vous avez soigné <font color=$couleur_clan_cible>$nom_cible</font> de $pv_soin PV</center><br />";
					echo "<center>La cible est revenu à son max de vie</center><br />";
					echo "<center>Vous avez gagné $gain_xp XP";
					if ($max_xp_tour_atteint) {
						echo " (maximum de gain d'xp par tour atteint)";
					}
					echo ".</center>";
				}
				
				if($id_objet_soin > 0){
					// Suppression de l'objet
					$sql_d = "DELETE FROM perso_as_objet WHERE id_objet='$id_objet_soin' AND id_perso='$id_perso' LIMIT 1";
					$mysqli->query($sql_d);
				}
			}
			else {
				// cible deja au max
				$gain_xp = 1;
				
				if ($gain_xp_tour_perso + $gain_xp > 20) {
					$gain_xp = 0;
					$max_xp_tour_atteint = true;
				}
				
				//MAJ xp perso
				$sql = "UPDATE perso SET xp_perso=xp_perso+$gain_xp, pi_perso=pi_perso+$gain_xp, gain_xp_tour=gain_xp_tour+$gain_xp, pa_perso=pa_perso-$coutPa WHERE id_perso='$id_perso'";
				$mysqli->query($sql);
				
				if($bonus_recup_s){
					if($id_objet_soin == 12){
						// Soin des malus
						// MAJ recup cible
						$sql = "UPDATE perso SET bonus_perso=bonus_perso+$bonus_recup_s WHERE id_perso='$id_cible'";
						$mysqli->query($sql);
						
						// MAJ evenments perso
						$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>',' a supprimé des malus de ','$id_cible','<font color=$couleur_clan_cible><b>$nom_cible</b></font>',' : (+$bonus_recup_s) $nom_objet',NOW(),'0')";
						$mysqli->query($sql);
						
						echo "<center>Vous avez soigné des malus de <font color=$couleur_clan_cible>$nom_cible</font> grâce à $nom_objet : +$bonus_recup_s </center><br/>";
					}
					else {
						// Augmentation recup
						// MAJ recup cible
						$sql = "UPDATE perso SET bonusRecup_perso=bonusRecup_perso+$bonus_recup_s WHERE id_perso='$id_cible'";
						$mysqli->query($sql);
						
						// MAJ evenments perso
						$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>',' a augmenté la récupération de ','$id_cible','<font color=$couleur_clan_cible><b>$nom_cible</b></font>',' : (+$bonus_recup_s) $nom_objet',NOW(),'0')";
						$mysqli->query($sql);
						
						echo "<center>Vous avez augmenté la récupération de <font color=$couleur_clan_cible>$nom_cible</font> grâce à $nom_objet : +$bonus_recup_s </center><br/>";
					}
					
					if($id_objet_soin > 0){
						// Suppression de l'objet
						$sql_d = "DELETE FROM perso_as_objet WHERE id_objet='$id_objet_soin' AND id_perso='$id_perso' LIMIT 1";
						$mysqli->query($sql_d);
					}
				}
				else {
					//MAJ evenments perso
					$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>',' a joué au docteur avec ','$id_cible','<font color=$couleur_clan_cible><b>$nom_cible</b></font>',' : pv déjà au max...',NOW(),'0')";
					$mysqli->query($sql);
				}
				
				echo "<center>La cible est était déjà à son max de vie</center><br />";
				echo "<center>Vous avez gagné $gain_xp XP";
				if ($max_xp_tour_atteint) {
					echo " (maximum de gain d'xp par tour atteint)";
				}
				echo ".</center>";
			}
		}
		else {
			// Competence de soin pas assez developpee pour soigner le perso cible
			$gain_xp = 1;
			
			if ($gain_xp_tour_perso + $gain_xp > 20) {
				$gain_xp = 0;
				$max_xp_tour_atteint = true;
			}
			
			//MAJ xp perso
			$sql = "UPDATE perso SET xp_perso=xp_perso+$gain_xp, pi_perso=pi_perso+$gain_xp, gain_xp_tour=gain_xp_tour+$gain_xp, pa_perso=pa_perso-$coutPa WHERE id_perso='$id_perso'";
			$mysqli->query($sql);
			
			//MAJ evenments perso
			$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>',' a raté ses soins sur ','$id_cible','<font color=$couleur_clan_cible><b>$nom_cible</b></font>',' : compétence pas assez développée...',NOW(),'0')";
			$mysqli->query($sql);
			
			echo "<center>Votre niveau dans cette compétence ne vous permet pas de soigner correctement la cible</center><br />";
			echo "<center>Vous avez gagné $gain_xp XP";
			if ($max_xp_tour_atteint) {
				echo " (maximum de gain d'xp par tour atteint)";
			}
			echo ".</center>";
		}
	}
	else {
		echo "<center>Vous n'avez pas assez de PA</center><br />";
	}
	echo "<br /><br /><center><a href='jouer.php' class='btn btn-primary'>retour</a></center>";
}

/**
  * Fonction qui pemet d'effectuer l'action dormir
  * @param $id_perso			: L'identifiant du personnage qui veut dormir
  * @return Void
  */
function action_dormir($mysqli, $id_perso){

	// recuperation des infos du perso
	$sql = "SELECT nom_perso, clan, recup_perso, bonusRecup_perso, pa_perso, paMax_perso, pv_perso, pvMax_perso, pm_perso, pmMax_perso, x_perso, y_perso FROM perso WHERE id_perso='$id_perso'";
	$res = $mysqli->query($sql);
	$t_p = $res->fetch_assoc();
	
	$nom_perso        = $t_p["nom_perso"];
	$recup_perso      = $t_p["recup_perso"];
	$pa_perso         = $t_p["pa_perso"];
	$paMax_perso      = $t_p["paMax_perso"];
	$pv_perso         = $t_p["pv_perso"];
	$pvMax_perso      = $t_p["pvMax_perso"];
	$pm_perso         = $t_p["pm_perso"];
	$pmMax_perso      = $t_p["pmMax_perso"];
	$bonusRecup_perso = $t_p["bonusRecup_perso"];
	$camp             = $t_p["clan"];
	$x_perso          = $t_p["x_perso"];
	$y_perso          = $t_p["y_perso"];
	
	// recuperation de la couleur du camp du perso
	$couleur_clan_perso = couleur_clan($camp);
	
	$bonus_recup_bat = get_bonus_recup_bat_perso($mysqli, $id_perso);
	$bonus_recup_terrain = 0;
	if (!in_bat($mysqli, $id_perso)) {
		$bonus_recup_terrain = get_bonus_recup_terrain_perso($mysqli, $x_perso, $y_perso);
	}
	
	$bonusRecup_perso += $bonus_recup_bat;
	$bonusRecup_perso += $bonus_recup_terrain;
	
	$gain_pv = ($recup_perso + $bonusRecup_perso)* 3;
	
	// test pa
	if($pa_perso >= $paMax_perso && $pm_perso >= $pmMax_perso){
	
		if ($pv_perso + $gain_pv >= $pvMax_perso) {
			// maj perso
			$sql = "UPDATE perso SET pv_perso=pvMax_perso, pm_perso=0, pa_perso=0 WHERE id_perso='$id_perso'";
			$mysqli->query($sql);
		}
		else {
			// maj perso
			$sql = "UPDATE perso SET pv_perso=pv_perso + $gain_pv, pm_perso=0, pa_perso=0 WHERE id_perso='$id_perso'";
			$mysqli->query($sql);
		}
		
		//mise a jour de la table evenement
		$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>',' ... ',NULL,'','',NOW(),'0')";
		$mysqli->query($sql);
		
		echo "<center>Vous dormez profondément, votre sommeil vous permet de récupérer ".$gain_pv." PV <br />";
		echo "<a href='jouer.php' class='btn btn-primary'>retour</a></center>";
	}
	else {
		echo "<center>Vous devez posséder la totalité de vos PA / PM pour effectuer cette action<br />";
		echo "<a href='jouer.php' class='btn btn-primary'>retour</a></center>";
	}
}

/**
  * Fonction qui permet a un personnage d'effectuer une marche forcee (+1pm contre des pv)
  * @param $id_perso	: L'identifiant du personnage qui veut faire la marche forcee
  * @param $nb_points_action	: le niveau de l'action de marche forcee
  * @param $coutPa_action	: Le cout en Pa de l'action
  * @return	Void
  */
function action_marcheForcee($mysqli, $id_perso, $nb_points_action, $coutPa_action){
	
	// recuperation des infos du perso
	$sql = "SELECT nom_perso, clan, pv_perso, x_perso, y_perso, pa_perso FROM perso WHERE id_perso='$id_perso'";
	$res = $mysqli->query($sql);
	$t_p = $res->fetch_assoc();
	
	$nom_perso = $t_p["nom_perso"];
	$pv_perso = $t_p["pv_perso"];
	$pa_perso = $t_p["pa_perso"];
	$x_perso = $t_p["x_perso"];
	$y_perso = $t_p["y_perso"];
	$camp = $t_p["clan"];
	
	// recuperation de la couleur du camp du perso
	$couleur_clan_perso = couleur_clan($camp);

	$cout_pv = 10 - (2 * ($nb_points_action - 1));
	
	// test pa
	if($pa_perso >= $coutPa_action){
		
		if (!in_bat($mysqli, $id_perso)) {
		
			// maj pm et xp/pi
			$sql = "UPDATE perso SET pm_perso=pm_perso+1, pa_perso=pa_perso-$coutPa_action, pv_perso=pv_perso-$cout_pv WHERE id_perso='$id_perso'";
			$mysqli->query($sql);
			
			if($pv_perso - $cout_pv <= 0){
				// Le perso s'est tue tout seul...
				// on l'efface de la carte
				$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_perso' AND y_carte='$y_perso'";
				$mysqli->query($sql);
				
				//mise a jour de la table evenement
				$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>',' s\'est tué en effectuant une marche forcée... ',NULL,'',' : Bravo !',NOW(),'0')";
				$mysqli->query($sql);
				
				// maj cv
				$sql = "INSERT INTO `cv` (IDActeur_cv, nomActeur_cv, IDCible_cv, nomCible_cv, date_cv) VALUES (0,'marche forcée','$id_perso','<font color=$couleur_clan_perso>$nom_perso</font>',NOW())";
				$mysqli->query($sql);
				
				// maj dernier tombé
				$sql = "INSERT INTO dernier_tombe (date_capture, id_perso_capture, camp_perso_capture, id_perso_captureur, camp_perso_captureur) VALUES (NOW(), '$id_perso', $camp, $id_perso, $camp)";
				$mysqli->query($sql);
				
				// Quand un grouillot meurt, il perd tout ses Pi
				$sql = "UPDATE perso SET xp_perso=xp_perso-pi_perso, pi_perso=0 WHERE id_perso='$id_perso'";
				$mysqli->query($sql);
				
				echo "<br /><center>En tentant de puiser dans vos dernières resources pour continuer d'avancer, les forces vous lachent et vous vous effondrez...</center><br />";
				echo "<center>Vous êtes Mort !</center><br />";
				echo "<center><a href='jouer.php' class='btn btn-primary'>retour</a></center>";
			}
			else {	
				//mise a jour de la table evenement
				$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>',' a effectué une marche forcée ',NULL,'',' : +1 PM',NOW(),'0')";
				$mysqli->query($sql);
			
				echo "<center>Vous vous êtes dépassé et gagnez 1PM ! ($cout_pv PV perdu)<br /><br />";
				echo "<a href='jouer.php' class='btn btn-primary'>retour</a></center>";
			}
		}
		else {
			echo "<center>Vous ne pouvez pas faire de marche forcée dans un bâtiment<br />";
			echo "<a href='jouer.php' class='btn btn-primary'>retour</a></center>";
		}
	}
	else {
		echo "<center>Vous n'avez pas assez de PA<br />";
		echo "<a href='jouer.php' class='btn btn-primary'>retour</a></center>";
	}
}

/**
  * Fonction qui permet a un personnage d'effectuer une course
  * @param $id_perso		: L'identifiant du personnage qui veut faire une course
  * @param $direction		: Direction de la course (1 = hg, 2 = h, 3 = hd, 4 = g, 5 = d, 6 = bg, 7 = b, 8 = bd)
  * @param $nb_points_action	: le niveau de l'action de course
  * @param $coutPa_action	: Le cout en Pa de l'action
  * @return	Void
  */
function action_courir($mysqli, $id_perso, $direction, $nb_points_action, $coutPa_action){
	
	// recuperation des infos du perso
	$sql = "SELECT x_perso, y_perso, nom_perso, clan, pm_perso, pa_perso, paMax_perso FROM perso WHERE id_perso='$id_perso'";
	$res = $mysqli->query($sql);
	$t_p = $res->fetch_assoc();
	
	$nom_perso = $t_p["nom_perso"];
	$pm_perso = $t_p["pm_perso"];
	$pa_perso = $t_p["pa_perso"];
	$paMax_perso = $t_p["paMax_perso"];
	$x_perso_depart = $t_p['x_perso'];
	$y_perso_depart = $t_p['y_perso'];
	$camp = $t_p["clan"];
	
	// recuperation de la couleur du camp du perso
	$couleur_clan_perso = couleur_clan($camp);
	
	// Calcul du bonus de mouvement en course
	if($nb_points_action == '1'){
		$bonus_mouv = 1;
	}
	if($nb_points_action == '2'){
		$calcul = $pm_perso * 1.5;
		$bonus_mouv = ceil($calcul) - $pm_perso;
	}
	if($nb_points_action == '3'){
		$calcul = $pm_perso * 2;
		$bonus_mouv = ceil($calcul) - $pm_perso;
	}
	$pm_total = $pm_perso + $bonus_mouv;
	if($pm_total == 0)
		$pm_total = 1;
	
	if($pa_perso >= $paMax_perso){
		$gain_xp = '0';
		
		//mise a jour de la table evenement
		$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>',' se met à courir ',NULL,'',' : $pm_total PM',NOW(),'0')";
		$mysqli->query($sql);
		
		$obstacle = 0;
		$pm_utilise = 0;
		
		while($pm_total > 0){
			
			// Recuperation position perso
			$sql = "SELECT x_perso, y_perso, image_perso FROM perso WHERE id_perso='$id_perso'";
			$res = $mysqli->query($sql);
			$t = $res->fetch_assoc();
			
			$x_perso = $t['x_perso'];
			$y_perso = $t['y_perso'];
			$image_perso = $t['image_perso'];
			
			// On verifie que le perso peut courir vers la prochaine position
			if($direction == 1){
				$sql_v = "SELECT occupee_carte FROM carte WHERE x_carte = $x_perso - 1 AND y_carte = $y_perso + 1";
			}
			if($direction == 2){
				$sql_v = "SELECT occupee_carte FROM carte WHERE x_carte = $x_perso AND y_carte = $y_perso + 1";
			}
			if($direction == 3){
				$sql_v = "SELECT occupee_carte FROM carte WHERE x_carte = $x_perso + 1 AND y_carte = $y_perso + 1";
			}
			if($direction == 4){
				$sql_v = "SELECT occupee_carte FROM carte WHERE x_carte = $x_perso - 1 AND y_carte = $y_perso";
			}
			if($direction == 5){
				$sql_v = "SELECT occupee_carte FROM carte WHERE x_carte = $x_perso + 1 AND y_carte = $y_perso";
			}
			if($direction == 6){
				$sql_v = "SELECT occupee_carte FROM carte WHERE x_carte = $x_perso - 1 AND y_carte = $y_perso - 1";
			}
			if($direction == 7){
				$sql_v = "SELECT occupee_carte FROM carte WHERE x_carte = $x_perso AND y_carte = $y_perso - 1";
			}
			if($direction == 8){
				$sql_v = "SELECT occupee_carte FROM carte WHERE x_carte = $x_perso + 1 AND y_carte = $y_perso - 1";
			}
			
			$res_v = $mysqli->query($sql_v);
			$t_v = $res_v->fetch_assoc();
			$occupee_carte = $t_v['occupee_carte'];
			
			if(!$occupee_carte && $occupee_carte!="" && $occupee_carte!=NULL){
				// MAJ carte position courante
				$sql_u1 = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_perso' AND y_carte='$y_perso'";
				$mysqli->query($sql_u1);
				
				// MAJ carte et perso nouvelle position
				if($direction == 1){
					$sql_u2 = "UPDATE carte SET occupee_carte='1', idPerso_carte='$id_perso', image_carte='$image_perso' WHERE x_carte = $x_perso - 1 AND y_carte = $y_perso + 1";
					$sql_u3 = "UPDATE perso SET x_perso = x_perso - 1, y_perso =y_perso + 1 WHERE id_perso='$id_perso'";
					$x_perso_final = $x_perso - 1;
					$y_perso_final = $y_perso + 1;
				}
				if($direction == 2){
					$sql_u2 = "UPDATE carte SET occupee_carte='1', idPerso_carte='$id_perso', image_carte='$image_perso' WHERE x_carte = $x_perso AND y_carte = $y_perso + 1";
					$sql_u3 = "UPDATE perso SET x_perso = x_perso, y_perso = y_perso + 1 WHERE id_perso='$id_perso'";
					$x_perso_final = $x_perso;
					$y_perso_final = $y_perso + 1;
				}
				if($direction == 3){
					$sql_u2 = "UPDATE carte SET occupee_carte='1', idPerso_carte='$id_perso', image_carte='$image_perso' WHERE x_carte = $x_perso + 1 AND y_carte = $y_perso + 1";
					$sql_u3 = "UPDATE perso SET x_perso = x_perso + 1, y_perso = y_perso + 1 WHERE id_perso='$id_perso'";
					$x_perso_final = $x_perso + 1;
					$y_perso_final = $y_perso + 1;
				}
				if($direction == 4){
					$sql_u2 = "UPDATE carte SET occupee_carte='1', idPerso_carte='$id_perso', image_carte='$image_perso' WHERE x_carte = $x_perso - 1 AND y_carte = $y_perso";
					$sql_u3 = "UPDATE perso SET x_perso = x_perso - 1, y_perso = y_perso WHERE id_perso='$id_perso'";
					$x_perso_final = $x_perso - 1;
					$y_perso_final = $y_perso;
				}
				if($direction == 5){
					$sql_u2 = "UPDATE carte SET occupee_carte='1', idPerso_carte='$id_perso', image_carte='$image_perso' WHERE x_carte = $x_perso + 1 AND y_carte = $y_perso";
					$sql_u3 = "UPDATE perso SET x_perso = x_perso + 1, y_perso = y_perso WHERE id_perso='$id_perso'";
					$x_perso_final = $x_perso + 1;
					$y_perso_final = $y_perso;
				}
				if($direction == 6){
					$sql_u2 = "UPDATE carte SET occupee_carte='1', idPerso_carte='$id_perso', image_carte='$image_perso' WHERE x_carte = $x_perso - 1 AND y_carte = $y_perso - 1";
					$sql_u3 = "UPDATE perso SET x_perso = x_perso - 1, y_perso =y_perso - 1 WHERE id_perso='$id_perso'";
					$x_perso_final = $x_perso - 1;
					$y_perso_final = $y_perso - 1;
				}
				if($direction == 7){
					$sql_u2 = "UPDATE carte SET occupee_carte='1', idPerso_carte='$id_perso', image_carte='$image_perso' WHERE x_carte = $x_perso AND y_carte = $y_perso - 1";
					$sql_u3 = "UPDATE perso SET x_perso = x_perso, y_perso =y_perso - 1 WHERE id_perso='$id_perso'";
					$x_perso_final = $x_perso;
					$y_perso_final = $y_perso - 1;
				}
				if($direction == 8){
					$sql_u2 = "UPDATE carte SET occupee_carte='1', idPerso_carte='$id_perso', image_carte='$image_perso' WHERE x_carte = $x_perso + 1 AND y_carte = $y_perso - 1";
					$sql_u3 = "UPDATE perso SET x_perso = x_perso + 1, y_perso =y_perso - 1 WHERE id_perso='$id_perso'";
					$x_perso_final = $x_perso + 1;
					$y_perso_final = $y_perso - 1;
				}				
				$mysqli->query($sql_u2);
				$mysqli->query($sql_u3);				
				
				$pm_total = $pm_total - 1;
				$pm_utilise = $pm_utilise + 1;
			}
			else {
				$pm_total = 0;
				$obstacle = 1;
			}
		}
		
		// MAJ PM/PA perso
		$sql = "UPDATE perso SET pa_perso=0, pm_perso=0 WHERE id_perso='$id_perso'";
		$mysqli->query($sql);
		
		//mise a jour de la table evenement
		$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>',' a couru de $x_perso_depart / $y_perso_depart jusqu\'à $x_perso_final / $y_perso_final',NULL,'',' : $pm_utilise PM',NOW(),'0')";
		$mysqli->query($sql);
		
		echo "<center>Votre course vous fait gagner : + $bonus_mouv PM</center><br />";
		if($obstacle) {
			echo "<center>Vous avez rencontré un obstacle</center><br />";
		}
		echo "<center>Vous avez utilisé $pm_utilise PM et êtes arrivé en $x_perso_final / $y_perso_final</center><br />";
		echo "<a href='jouer.php' class='btn btn-primary'>retour</a>";
	}
	else {
		echo "<center>Vous n'avez pas assez de PA</center><br />";
		echo "<a href='jouer.php' class='btn btn-primary'>retour</a>";
	}
}

/**
  * Fonction qui permet de couper du bois
  * @param $id_perso	: L'identifiant du personnage qui va couper du bois
  * @param $id_action	: L'identifiant de l'action (A SUPPRIMER !!!)
  * @param $nb_points_action	: Le niveau de l'action, permettant de determiner le nombre de morceaux de bois recuperes
  * @return Void
  */
function action_couper_bois($mysqli, $id_perso, $id_action, $nb_points_action){
	
	// recuperation des coordonnees du perso
	$sql = "SELECT x_perso, y_perso, nom_perso, clan, pa_perso FROM perso WHERE id_perso='$id_perso'";
	$res = $mysqli->query($sql);
	$t_c = $res->fetch_assoc();
	
	$x_perso = $t_c['x_perso'];
	$y_perso = $t_c['y_perso'];
	$nom_perso = $t_c["nom_perso"];
	$pa_perso = $t_c["pa_perso"];
	$camp = $t_c["clan"];
	
	// recuperation de la couleur du camp du perso
	$couleur_clan_perso = couleur_clan($camp);
	
	// Cout de l'action en Pa
	$cout_pa = '7';
	
	if($pa_perso >= $cout_pa){
		
		// recuperation du fond de carte
		$sql = "SELECT fond_carte FROM carte WHERE x_carte='$x_perso' and y_carte='$y_perso'";
		$res = $mysqli->query($sql);
		$t_f = $res->fetch_assoc();
		$fond_carte = $t_f["fond_carte"];
		
		// verification que le perso est bien sur une case de foret
		if($fond_carte == '7.gif'){
			
			// calcul des gain de bois
			$gain_bois = rand(1, $nb_points_action);
			$charge_bois = 2 * $gain_bois;
			
			$gain_xp = '1';
			
			// MAJ xp/pi/pa/charge perso
			$sql = "UPDATE perso SET xp_perso=xp_perso+$gain_xp, pi_perso=pi_perso+$gain_xp, pa_perso=pa_perso-$cout_pa, charge_perso=charge_perso+$charge_bois WHERE id_perso='$id_perso'";
			$mysqli->query($sql);
			
			// MAJ fond carte
			$sql = "UPDATE carte SET fond_carte='1.gif' WHERE x_carte='$x_perso' AND y_carte='$y_perso'";
			$mysqli->query($sql);
			
			// MAJ objets perso
			for ($i = 1; $i <= $gain_bois; $i++){
				$sql = "INSERT INTO perso_as_objet VALUES('$id_perso', '70')";
				$mysqli->query($sql);
			}
			
			//mise a jour de la table evenement
			$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) 
					VALUES ($id_perso,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>',' a coupé des arbres ',NULL,'',' : + $gain_bois morceaux de bois',NOW(),'0')";
			$mysqli->query($sql);
			
			echo "<center><font color=red><b>Vous avez coupé une forêt, vous avez récupéré $gain_bois morceaux de bois </b></font></center><br />";
			echo "<center>Vous avez gagné 1xp</center><br /><br />";
			echo "<a href='jouer.php' class='btn btn-primary'>retour</a>";
		}
		else {
			echo "<center><font color=red><b>Vous devez être sur une case de forêt afin de pouvoir la couper</b></font></center><br />";
			echo "<a href='jouer.php' class='btn btn-primary'>retour</a>";
		}
	}
	else {
		echo "<center>Vous n'avez pas assez de PA</center><br />";
		echo "<a href='jouer.php' class='btn btn-primary'>retour</a>";
	}
}

/**
  * Fonction qui permet de recuperer du minerais dans la montagne
  * @param $id_perso	: L'identifiant du personnage qui va miner la montagne
  * @param $id_action	: L'identifiant de l'action
  * @param $nb_points_action	: Le niveau de l'action
  * @return Void
  */
function action_miner_montagne($mysqli, $id_perso, $id_action, $nb_points_action){
	
	// recuperation des coordonnees du perso
	$sql = "SELECT x_perso, y_perso, nom_perso, clan, pa_perso FROM perso WHERE id_perso='$id_perso'";
	$res = $mysqli->query($sql);
	$t_c = $res->fetch_assoc();
	
	$x_perso = $t_c['x_perso'];
	$y_perso = $t_c['y_perso'];
	$nom_perso = $t_c["nom_perso"];
	$pa_perso = $t_c["pa_perso"];
	$camp = $t_c["clan"];
	
	// recuperation de la couleur du camp du perso
	$couleur_clan_perso = couleur_clan($camp);
	
	// Cout en Pa de l'action
	$cout_pa = '10';
	
	if($pa_perso >= $cout_pa){
		
		// recuperation du fond de carte
		$sql = "SELECT fond_carte FROM carte WHERE x_carte='$x_perso' and y_carte='$y_perso'";
		$res = $mysqli->query($sql);
		$t_f = $res->fetch_assoc();
		$fond_carte = $t_f["fond_carte"];
		
		// verification que le perso est bien sur une case de montagne
		if($fond_carte == '3.gif'){
			
			// calcul chance de recuperer du fer
			$chance = rand(0,100);
			
			if(est_chanceux($id_perso)){
				$bonus_chance = 2 * est_chanceux($id_perso);
				echo "Bonus donné par la chance : ".$bonus_chance."<br/>";
			}
			else {
				$bonus_chance = 0;
			}
			
			if($chance + $bonus_chance >= 50){
				
				// calcul des gain de fer
				$gain_fer = rand(1, 3);
				$charge_fer = 1 * $gain_fer;
				
				$gain_xp = '1';
				
				// MAJ xp/pi/pa/charge perso
				$sql = "UPDATE perso SET xp_perso=xp_perso+$gain_xp, pi_perso=pi_perso+$gain_xp, pa_perso=pa_perso-$cout_pa, charge_perso=charge_perso+$charge_fer WHERE id_perso='$id_perso'";
				$mysqli->query($sql);
				
				// MAJ objets perso
				for ($i = 1; $i <= $gain_fer; $i++){
					$sql = "INSERT INTO perso_as_objet VALUES('$id_perso', '8')";
					$mysqli->query($sql);
				}
				
				//mise a jour de la table evenement
				$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>',' a miné la montagne ',NULL,'',' : + $gain_fer morceaux de fer',NOW(),'0')";
				$mysqli->query($sql);
				
				echo "<center><font color=red><b>Vous avez miné la montagne, vous avez récupéré $gain_fer morceaux de fer </b></font></center><br />";
				echo "<center>Vous avez gagné 2xp</center><br /><br />";
				echo "<a href='jouer.php' class='btn btn-primary'>retour</a>";
			}
			else {
				$gain_fer = 0;
			
				// MAJ xp/pi/pa/charge perso
				$sql = "UPDATE perso SET xp_perso=xp_perso+1, pi_perso=pi_perso+1, pa_perso=pa_perso-$cout_pa WHERE id_perso='$id_perso'";
				$mysqli->query($sql);
				
				//mise a jour de la table evenement
				$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>',' a miné la montagne ',NULL,'',' : + $gain_fer morceaux de fer, pas de chance...',NOW(),'0')";
				$mysqli->query($sql);
				
				echo "<center><font color=red><b>Vous avez miné la montagne mais vous n'avez rien trouvé </b></font></center><br />";
				echo "<center>Vous avez gagné 1xp</center><br /><br />";
				echo "<a href='jouer.php' class='btn btn-primary'>retour</a>";
			}
		}
		else {
			echo "<center><font color=red><b>Vous devez être sur une case de montagne afin de pouvoir la miner</b></font></center><br />";
			echo "<a href='jouer.php' class='btn btn-primary'>retour</a>";
		}
	}
	else {
		echo "<center>Vous n'avez pas assez de PA</center><br />";
		echo "<a href='jouer.php' class='btn btn-primary'>retour</a>";
	}
}

/**
  * Fonction qui permet d'utiliser l'action sauter
  * @param $id_perso	: L'identifia,t du personnage qui veut sauter
  * @param $x_cible	: La coordonnee x de la case cible
  * @param $y_cible	: La coordonnee y de la case cible
  * @param $coutpa	: Le cout en Points d'action
  * @return Void
  */
function action_sauter($mysqli, $id_perso, $x_cible, $y_cible, $coutPa, $carte){
	
	// recuperation du cout de pm de la case cible
	$sql = "SELECT fond_carte FROM $carte WHERE x_carte=$x_cible AND y_carte=$y_cible";
	$res = $mysqli->query($sql);
	$t_f = $res->fetch_assoc();
	
	$fond_carte = $t_f["fond_carte"];
	$cout_pm_cible = cout_pm($fond_carte);
	$cout_pm_total = $cout_pm_cible + 1;

	// recuperation des pm du perso
	$sql = "SELECT nom_perso, pm_perso, x_perso, y_perso, image_perso, clan, pa_perso FROM perso WHERE id_perso='$id_perso'";
	$res = $mysqli->query($sql);
	$t_pm = $res->fetch_assoc();
	
	$nom_perso = $t_pm["nom_perso"];
	$pm_perso = $t_pm["pm_perso"];
	$x_perso = $t_pm["x_perso"];
	$y_perso = $t_pm["y_perso"];
	$pa_perso = $t_pm["pa_perso"];
	$image_perso = $t_pm["image_perso"];
	$camp = $t_pm["clan"];
	
	// recuperation de la couleur du camp du perso
	$couleur_clan_perso = couleur_clan($camp);
	
	// tests pa
	if($pa_perso >= $coutPa){
		// test si assez de pm
		if($pm_perso >= $cout_pm_total){
		
			$gain_xp = '0';
			
			// MAJ xp/pi/pa/pm/x et y perso
			$sql = "UPDATE perso SET x_perso=$x_cible, y_perso=$y_cible, xp_perso=xp_perso+$gain_xp, pi_perso=pi_perso+$gain_xp, pa_perso=pa_perso-$coutPa, pm_perso=pm_perso-$cout_pm_total WHERE id_perso='$id_perso'";
			$mysqli->query($sql);
			
			// MAJ carte
			$sql = "UPDATE $carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_perso' AND y_carte='$y_perso'";
			$mysqli->query($sql);
			
			$sql = "UPDATE $carte SET occupee_carte='1', idPerso_carte='$id_perso', image_carte='$image_perso' WHERE x_carte='$x_cible' AND y_carte='$y_cible'";
			$mysqli->query($sql);
			
			//mise a jour de la table evenement
			$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>',' a sauté ',NULL,'',' en $x_cible / $y_cible',NOW(),'0')";
			$mysqli->query($sql);
			
			echo "<center><font color=red><b>Vous avez sauté en $x_cible/$y_cible, cout total en PM : $cout_pm_total PM</b></font></center><br />";
			echo "<center>Vous avez gagné $gain_xp XP</center><br /><br />";
			
		}
		else {
			// pas assez de pm
			echo "<center><font color=red><b>Vous ne possédez pas assez de pm pour sauter sur cette case, il vous faut $cout_pm_total PM</b></font></center><br />";
		}
	}
	else {
		echo "<center>Vous n'avez pas assez de PA</center><br />";
	}
	echo "<a href='jouer.php' class='btn btn-primary'>retour</a>";
}

/**
  * Fonction qui permet de planter un arbre
  * @param $id_perso	: L'identifiant du personnage qui veut planter un arbre
  * @param $id_action	: L'identifiant de l'action
  * @param $nb_points_action	: Le niveau de l'action
  * @return Void
  */
function action_planterArbre($mysqli, $id_perso, $id_action, $nb_points_action){

	// recuperation des donnees correspondant a l'action
	$sql = "SELECT pvMin_action, pvMax_action, coutPa_action FROM action WHERE id_action=$id_action";
	$res = $mysqli->query($sql);
	$t_action = $res->fetch_assoc();
	$coutPa = $t_action["coutPa_action"];
	
	// verification que la case soit de la plaine
	$sql = "SELECT fond_carte FROM carte,perso WHERE x_carte=x_perso AND y_carte=y_perso AND id_perso='$id_perso'";
	$res = $mysqli->query($sql);
	$t_fond = $res->fetch_assoc();
	$fond_carte = $t_fond['fond_carte'];
	
	if($fond_carte == '1.gif'){
		
		// recuperation des infos du perso
		$sql = "SELECT nom_perso, clan, x_perso, y_perso, pa_perso FROM perso WHERE id_perso='$id_perso'";
		$res = $mysqli->query($sql);
		$t_perso = $res->fetch_assoc();
		
		$nom_perso = $t_perso['nom_perso'];
		$camp_perso = $t_perso['clan'];
		$x_perso = $t_perso['x_perso'];
		$y_perso = $t_perso['y_perso'];
		$pa_perso = $t_perso['pa_perso'];
		
		// recuperation de la couleur du camp du perso
		$couleur_clan_perso = couleur_clan($camp_perso);
		
		if($pa_perso >= $coutPa){
		
			// gains xp
			$gain_xp = rand(1,3);
			
			// Insertion dans la table pousse_foret
			$sql = "INSERT INTO pousse_foret VALUES('$x_perso','$y_perso','1')";
			$mysqli->query($sql);
			
			// MAJ xp/pi/pa/pm/x et y perso
			$sql = "UPDATE perso SET xp_perso=xp_perso+$gain_xp, pi_perso=pi_perso+$gain_xp, pa_perso=pa_perso-$coutPa WHERE id_perso='$id_perso'";
			$mysqli->query($sql);
		
			//mise a jour de la table evenement
			$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>',' a planté des arbres ',NULL,'',' en $x_perso / $y_perso',NOW(),'0')";
			$mysqli->query($sql);
			
			echo "<center>Vous avez planté des arbres en $x_perso / $y_perso, ils pousseront automatiquement d'ici 5 jours environs</center>";
			echo "<center>Vous avez gagné $gain_xp XP</center><br /><br />";
		}
		else {
			echo "<center>Vous n'avez pas assez de PA</center><br />";
		}
	}
	else {
		echo "<center>Vous ne pouvez pas planter d'arbre en dehors d'une case de plaine</center>";
	} 
	echo "<a href='jouer.php' class='btn btn-primary'>retour</a>";
}

/**
  * Fonction qui permet d'effectuer l'action de sabotage
  * @param $id_perso	: L'identifiant du personnage qui veut saboter
  * @param $id_bat		: identifiant du batiment à saboter
  * @param $id_action	: L'identifiant de l'action
  * @return Void
  */
function action_saboter($mysqli, $id_perso, $id_bat, $id_action){
	
	// recuperation des donnees correspondant a l'action
	$sql = "SELECT pvMin_action, pvMax_action, coutPa_action FROM action WHERE id_action='$id_action'";
	$res = $mysqli->query($sql);
	$t_action = $res->fetch_assoc();
	
	$coutPa = $t_action["coutPa_action"];
	
	// verification que le perso est sur un pont ou une route
	if(prox_bat_perso($mysqli, $id_perso, $id_bat)){ 
	
		// récupération des infos du pont 
		$sql = "SELECT x_instance, y_instance, pv_instance, camp_instance FROM instance_batiment WHERE id_instanceBat = '$id_bat'";
		$res = $mysqli->query($sql);
		$t_bat = $res->fetch_assoc();
		
		$x_bat 	= $t_bat["x_instance"];
		$y_bat 	= $t_bat["y_instance"];
		$pv_bat = $t_bat["pv_instance"];
		$c_bat	= $t_bat["camp_instance"];
		
		$couleur_bat = couleur_clan($c_bat);
	   
		// recuperation des infos du perso
		$sql = "SELECT nom_perso, clan, x_perso, y_perso, pa_perso, gain_xp_tour, genie FROM perso WHERE id_perso='$id_perso'";
		$res = $mysqli->query($sql);
		$t_perso = $res->fetch_assoc();
		
		$nom_perso 		= $t_perso['nom_perso'];
		$camp_perso 	= $t_perso['clan'];
		$pa_perso 		= $t_perso['pa_perso'];
		$genie_perso	= $t_perso['genie'];
		$gain_xp_tour_perso	= $t_perso['gain_xp_tour'];
		
		if ($gain_xp_tour_perso >= 20) {
			$max_xp_tour_atteint = true;
		}
		else {
			$max_xp_tour_atteint = false;
		}
		
		// recuperation de la couleur du camp du perso
		$couleur_clan_perso = couleur_clan($camp_perso);
		
		if($pa_perso >= $coutPa){
		
			// gains xp
			$gain_xp = rand(1,3);
			
			if ($gain_xp_tour_perso + $gain_xp > 20) {
				$gain_xp = 20 - $gain_xp_tour_perso;
				$max_xp_tour_atteint = true;
			}
			
			// calcul pourcentage de reussite
			$pourcentage_reussite = 80;
			
			$reussite = rand(0,100);
			
			// chance
			if(est_chanceux($mysqli, $id_perso)){
				$bonus_chance = 2 * est_chanceux($id_perso);
			}
			else {
				$bonus_chance = 0;
			}
			
			if($reussite <= $pourcentage_reussite + $bonus_chance){
				
				// Récupération si appartient génie civil
				$sql = "SELECT count(id_perso) as verif_gc FROM perso_in_compagnie, compagnies 
						WHERE compagnies.id_compagnie = perso_in_compagnie.id_compagnie
						AND id_perso='$id_perso'
						AND compagnies.genie_civil='1'";
				$res = $mysqli->query($sql);
				$t_gc = $res->fetch_assoc();
				
				$verif_gc = $t_gc['verif_gc'];
				
				$degats_sabotage = rand(50,200);
				
				if ($verif_gc && $genie_perso == 1) {
					$degats_sabotage *= 2;
				}
				
				$pv_final_bat = $pv_bat - $degats_sabotage;
				
				if ($pv_final_bat <= 0) {
					
					// On récupère le terrain sur lequel il était construit pour le rétablir
					$sql = "SELECT terrain_instance FROM instance_batiment WHERE id_instanceBat = '$id_bat'";
					$res = $mysqli->query($sql);
					$t = $res->fetch_assoc();
					
					$terrain_instance = $t['terrain_instance'];
					
					// MAJ carte
					$sql = "UPDATE carte SET fond_carte='$terrain_instance', idPerso_carte=NULL, save_info_carte=NULL WHERE x_carte=$x_bat AND y_carte=$y_bat";
					$mysqli->query($sql);
					
					// Suppression instance bat 
					$sql = "DELETE FROM instance_batiment WHERE id_instanceBat = '$id_bat'";
					$mysqli->query($sql);
					
					//mise a jour de la table evenement
					$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>',' a détruit un ',$id_bat,'<font color=$couleur_bat><b>Pont</b></font>',' en $x_bat / $y_bat',NOW(),'0')";
					$mysqli->query($sql);
					
					echo "<center>Vous avez détruit un pont en $x_bat / $y_bat</center>";
				} 
				else {
					
					// mise à jour pv pont
					$sql = "UPDATE instance_batiment SET pv_instance = $pv_final_bat WHERE id_instanceBat = '$id_bat'";
					$mysqli->query($sql);
					
					//mise a jour de la table evenement
					$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>',' a saboté un ',$id_bat,'<font color=$couleur_bat><b>pont</b></font>',' en $x_bat / $y_bat',NOW(),'0')";
					$mysqli->query($sql);
					
					echo "<center>Vous avez saboté un pont en $x_bat / $y_bat : $degats_sabotage dégats</center>";
				}
			}
			else {
				$gain_xp = 1;
				
				if ($gain_xp_tour_perso + $gain_xp > 20) {
					$gain_xp = 0;
					$max_xp_tour_atteint = true;
				}
				
				//mise a jour de la table evenement
				$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>',' a raté son sabotage ',NULL,'',' en $x_bat / $y_bat',NOW(),'0')";
				$mysqli->query($sql);
				
				echo "<center>Vous avez raté votre sabotage</center>";
			}
			
			// MAJ xp/pi/pa perso
			$sql = "UPDATE perso SET xp_perso=xp_perso+$gain_xp, pi_perso=pi_perso+$gain_xp, gain_xp_tour=gain_xp_tour+$gain_xp, pa_perso=pa_perso-$coutPa WHERE id_perso='$id_perso'";
			$mysqli->query($sql);
			
			echo "<center>Vous avez gagné $gain_xp XP";
			if ($max_xp_tour_atteint) {
				echo " (maximum gain d'xp par tour atteint)";
			}
			echo "</center><br /><br />";
		}
		else {
			echo "<center>Vous n'avez pas assez de PA</center><br />";
		}
	}
	else {
		echo "<center>Vous devez être sur une case de route ou de pont afin de pouvoir saboter</center>";
	}
	echo "<center><a class='btn btn-primary' href='jouer.php'>retour</a></center>";
}

/**
  * Fonction qui permet d'ajouter un evenement chant
  * @param $id_perso	: L'identifiant du perso qui veut chanter
  * @param $id_action	: L'identifiant de l'action afin de connaitre le niveau
  * @return Void
  */
function action_chanter($mysqli, $id_perso,$id_action){

	// recuperation des donnees correspondant a l'action
	$sql = "SELECT pvMin_action, pvMax_action, coutPa_action FROM action WHERE id_action='$id_action'";
	$res = $mysqli->query($sql);
	$t_action = $res->fetch_assoc();
	$coutPa = $t_action["coutPa_action"];

	// recuperation des infos du perso
	$sql = "SELECT nom_perso, clan, pa_perso FROM perso WHERE id_perso='$id_perso'";
	$res = $mysqli->query($sql);
	$t_perso = $res->fetch_assoc();
	
	$nom_perso = $t_perso['nom_perso'];
	$camp_perso = $t_perso['clan'];
	$pa_perso = $t_perso['pa_perso'];
	
	// recuperation de la couleur du camp du perso
	$couleur_clan_perso = couleur_clan($camp_perso);
	
	if($pa_perso >= $coutPa){
		//mise a jour de la table evenement
		$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>',' a chanté ',NULL,'','',NOW(),'0')";
		$mysqli->query($sql);
		
		// MAJ xp/pi/pa/pm/x et y perso
		$sql = "UPDATE perso SET pa_perso=pa_perso-$coutPa WHERE id_perso='$id_perso'";
		$mysqli->query($sql);
		
		echo "<center>Vous avez chanté</center>";
	}
	else {
		echo "<center>Vous n'avez pas assez de PA</center><br />";
	}
	echo "<a href='jouer.php' class='btn btn-primary'>retour</a>";
}

/**
  * Fonction qui permet d'ajouter un evenement chant
  * @param $id_perso	: L'identifiant du perso qui veut chanter
  * @param $phrase	: La phrase a mettre dans les evenements
  * @return Void
  */
function action_chanter_perso($mysqli, $id_perso,$phrase){

	// recuperation des infos du perso
	$sql = "SELECT nom_perso, clan, pa_perso FROM perso WHERE id_perso='$id_perso'";
	$res = $mysqli->query($sql);
	$t_perso = $res->fetch_assoc();
	
	$nom_perso = $t_perso['nom_perso'];
	$camp_perso = $t_perso['clan'];
	$pa_perso = $t_perso['pa_perso'];
	
	// recuperation de la couleur du camp du perso
	$couleur_clan_perso = couleur_clan($camp_perso);
	
	if($pa_perso >= 2){
		//mise a jour de la table evenement
		$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>',' $phrase ',NULL,'','',NOW(),'0')";
		$mysqli->query($sql);
		
		// MAJ xp/pi/pa/pm/x et y perso
		$sql = "UPDATE perso SET pa_perso=pa_perso-2 WHERE id_perso='$id_perso'";
		$mysqli->query($sql);
		
		echo "<center>$nom_perso ". stripslashes($phrase) ."</center>";
	}
	else {
		echo "<center>Vous n'avez pas assez de PA</center><br />";
	}
	echo "<a href='jouer.php' class='btn btn-primary'>retour</a>";
}

/**
  * Fonction qui permet d'ajouter un evenement danse
  * @param $id_perso	: L'identifiant du perso qui veut danser
  * @param $id_action	: l'identifiant de l'action afin de connaitre le niveau
  * @return Void
  */
function action_danser($mysqli, $id_perso,$id_action){

	// recuperation des donnees correspondant a l'action
	$sql = "SELECT pvMin_action, pvMax_action, coutPa_action FROM action WHERE id_action='$id_action'";
	$res = $mysqli->query($sql);
	$t_action = $res->fetch_assoc();
	$coutPa = $t_action["coutPa_action"];

	// recuperation des infos du perso
	$sql = "SELECT nom_perso, clan, pa_perso FROM perso WHERE id_perso='$id_perso'";
	$res = $mysqli->query($sql);
	$t_perso = $res->fetch_assoc();
	
	$nom_perso = $t_perso['nom_perso'];
	$camp_perso = $t_perso['clan'];
	$pa_perso = $t_perso['pa_perso'];
	
	// recuperation de la couleur du camp du perso
	$couleur_clan_perso = couleur_clan($camp_perso);
	
	if($pa_perso >= $coutPa){
		//mise a jour de la table evenement
		$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>',' a dansé ',NULL,'','',NOW(),'0')";
		$mysqli->query($sql);
		
		// MAJ xp/pi/pa/pm/x et y perso
		$sql = "UPDATE perso SET pa_perso=pa_perso-$coutPa WHERE id_perso='$id_perso'";
		$mysqli->query($sql);
		
		echo "<center>Vous avez dansé</center>";
	}
	else {
		echo "<center>Vous n'avez pas assez de PA</center><br />";
	}
	echo "<a href='jouer.php' class='btn btn-primary'>retour</a>";
}

/**
  * Fonction qui permet d'ajouter un evenement danse
  * @param $id_perso	: L'identifiant du perso qui veut danser
  * @param $phrase	: La phrase a mettre dans les evenements
  * @return Void
  */
function action_danser_perso($mysqli, $id_perso, $phrase){

	// recuperation des infos du perso
	$sql = "SELECT nom_perso, clan, pa_perso FROM perso WHERE id_perso='$id_perso'";
	$res = $mysqli->query($sql);
	$t_perso = $res->fetch_assoc();
	
	$nom_perso = $t_perso['nom_perso'];
	$camp_perso = $t_perso['clan'];
	$pa_perso = $t_perso['pa_perso'];
	
	// recuperation de la couleur du camp du perso
	$couleur_clan_perso = couleur_clan($camp_perso);
	
	if($pa_perso >= 2){
		//mise a jour de la table evenement
		$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>',' $phrase ',NULL,'','',NOW(),'0')";
		$mysqli->query($sql);
		
		// MAJ xp/pi/pa/pm/x et y perso
		$sql = "UPDATE perso SET pa_perso=pa_perso-2 WHERE id_perso='$id_perso'";
		$mysqli->query($sql);
		
		echo "<center>$nom_perso ". stripslashes($phrase) ."</center>";
	}
	else {
		echo "<center>Vous n'avez pas assez de PA</center><br />";
	}
	echo "<a href='jouer.php' class='btn btn-primary'>retour</a>";
}

/**
  * Fonction qui permet d'ajouter un evenement peinture
  * @param $id_perso	: L'identifiant du perso qui veut peindre
  * @param $id_action	: l'identifiant de l'action afin de connaitre le niveau
  * @return Void
  */
function action_peindre($mysqli, $id_perso, $id_action){

	// recuperation des donnees correspondant a l'action
	$sql = "SELECT pvMin_action, pvMax_action, coutPa_action FROM action WHERE id_action='$id_action'";
	$res = $mysqli->query($sql);
	$t_action = $res->fetch_assoc();
	$coutPa = $t_action["coutPa_action"];

	// recuperation des infos du perso
	$sql = "SELECT nom_perso, clan, pa_perso FROM perso WHERE id_perso='$id_perso'";
	$res = $mysqli->query($sql);
	$t_perso = $res->fetch_assoc();
	
	$nom_perso = $t_perso['nom_perso'];
	$camp_perso = $t_perso['clan'];
	$pa_perso = $t_perso['pa_perso'];
	
	// recuperation de la couleur du camp du perso
	$couleur_clan_perso = couleur_clan($camp_perso);
	
	if($pa_perso >= $coutPa){
		//mise a jour de la table evenement
		$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>',' a peind ',NULL,'','',NOW(),'0')";
		$mysqli->query($sql);
		
		// MAJ xp/pi/pa/pm/x et y perso
		$sql = "UPDATE perso SET pa_perso=pa_perso-$coutPa WHERE id_perso='$id_perso'";
		$mysqli->query($sql);
		
		echo "<center>Vous avez peind</center>";
	}
	else {
		echo "<center>Vous n'avez pas assez de PA</center><br />";
	}
	echo "<a href='jouer.php' class='btn btn-primary'>retour</a>";
}

/**
  * Fonction qui permet d'ajouter un evenement peinture
  * @param $id_perso	: L'identifiant du perso qui veut peindre
  * @param $phrase	: La phrase a mettre dans les evenements
  * @return Void
  */
function action_peindre_perso($mysqli, $id_perso,$phrase){

	// recuperation des infos du perso
	$sql = "SELECT nom_perso, clan, pa_perso FROM perso WHERE id_perso='$id_perso'";
	$res = $mysqli->query($sql);
	$t_perso = $res->fetch_assoc();
	
	$nom_perso = $t_perso['nom_perso'];
	$camp_perso = $t_perso['clan'];
	$pa_perso = $t_perso['pa_perso'];
	
	// recuperation de la couleur du camp du perso
	$couleur_clan_perso = couleur_clan($camp_perso);
	
	if($pa_perso >= 2){
		//mise a jour de la table evenement
		$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>',' $phrase ',NULL,'','',NOW(),'0')";
		$mysqli->query($sql);
		
		// MAJ xp/pi/pa/pm/x et y perso
		$sql = "UPDATE perso SET pa_perso=pa_perso-2 WHERE id_perso='$id_perso'";
		$mysqli->query($sql);
		
		echo "<center>$nom_perso ". stripslashes($phrase) ."</center>";
	}
	else {
		echo "<center>Vous n'avez pas assez de PA</center><br />";
	}
	echo "<a href='jouer.php' class='btn btn-primary'>retour</a>";
}

/**
  * Fonction qui permet d'ajouter un evenement sculpture
  * @param $id_perso	: L'identifiant du perso qui veut scuplter
  * @param $id_action	: l'identifiant de l'action afin de connaitre le niveau
  * @return Void
  */
function action_sculter($mysqli, $id_perso,$id_action){

	// recuperation des donnees correspondant a l'action
	$sql = "SELECT pvMin_action, pvMax_action, coutPa_action FROM action WHERE id_action='$id_action'";
	$res = $mysqli->query($sql);
	$t_action = $res->fetch_assoc();
	$coutPa = $t_action["coutPa_action"];

	// recuperation des infos du perso
	$sql = "SELECT nom_perso, clan, pa_perso FROM perso WHERE id_perso='$id_perso'";
	$res = $mysqli->query($sql);
	$t_perso = $res->fetch_assoc();
	
	$nom_perso = $t_perso['nom_perso'];
	$camp_perso = $t_perso['clan'];
	$pa_perso = $t_perso['pa_perso'];
	
	// recuperation de la couleur du camp du perso
	$couleur_clan_perso = couleur_clan($camp_perso);
	
	if($pa_perso >= $coutPa){
		//mise a jour de la table evenement
		$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>',' a scuplté ',NULL,'','',NOW(),'0')";
		$mysqli->query($sql);
		
		// MAJ xp/pi/pa/pm/x et y perso
		$sql = "UPDATE perso SET pa_perso=pa_perso-$coutPa WHERE id_perso='$id_perso'";
		$mysqli->query($sql);
		
		echo "<center>Vous avez sculpté</center>";
	}
	else {
		echo "<center>Vous n'avez pas assez de PA</center><br />";
	}
	echo "<a href='jouer.php' class='btn btn-primary'>retour</a>";
}

/**
  * Fonction qui permet d'ajouter un evenement sculpture
  * @param $id_perso	: L'identifiant du perso qui veut scuplter
  * @param $phrase	: La phrase a mettre dans les evenements
  * @return Void
  */
function action_scuplter_perso($mysqli, $id_perso, $phrase){

	// recuperation des infos du perso
	$sql = "SELECT nom_perso, clan, pa_perso FROM perso WHERE id_perso='$id_perso'";
	$res = $mysqli->query($sql);
	$t_perso = $res->fetch_assoc();
	
	$nom_perso = $t_perso['nom_perso'];
	$camp_perso = $t_perso['clan'];
	$pa_perso = $t_perso['pa_perso'];
	
	// recuperation de la couleur du camp du perso
	$couleur_clan_perso = couleur_clan($camp_perso);
	
	if($pa_perso >= 2){
		//mise a jour de la table evenement
		$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>',' $phrase ',NULL,'','',NOW(),'0')";
		$mysqli->query($sql);
		
		// MAJ xp/pi/pa/pm/x et y perso
		$sql = "UPDATE perso SET pa_perso=pa_perso-2 WHERE id_perso='$id_perso'";
		$mysqli->query($sql);
		
		echo "<center>$nom_perso ". stripslashes($phrase) ."</center>";
	}
	else {
		echo "<center>Vous n'avez pas assez de PA</center><br />";
	}
	echo "<a href='jouer.php' class='btn btn-primary'>retour</a>";
}

/**
  * Fonction qui permet de faire l'action entrainement a un personnage
  * @param $id_perso	: L'identifiant du perso qui veut s'entrainer
  * @return Void
  */
function action_entrainement($mysqli, $id_perso){
	
	// verification si le perso s'est deja entraine auparavent
	$sql = "SELECT niveau_entrainement, nb FROM perso_as_entrainement WHERE id_perso='$id_perso'";
	$res = $mysqli->query($sql);
	$entraine = $res->num_rows;
	
	// recuperation du nombre de PA et infos du perso
	$sql_pa = "SELECT nom_perso, pa_perso, clan FROM perso WHERE id_perso='$id_perso'";
	$res_pa = $mysqli->query($sql_pa);
	$t_pa = $res_pa->fetch_assoc();
	
	$nom_perso = $t_pa['nom_perso'];
	$pa_perso = $t_pa['pa_perso'];
	$camp = $t_pa['clan'];
	
	// recuperation de la couleur du camp du perso
	$couleur_clan_perso = couleur_clan($camp);
	
	if(est_chanceux($mysqli, $id_perso)){
		$bonus_chance = 2 * est_chanceux($id_perso);
		echo "Bonus donné par la chance : ".$bonus_chance."<br/>";
	}
	else {
		$bonus_chance = 0;
	}
	
	if($pa_perso >= 10){
		// deja en base
		if($entraine){
			
			$t_e = $res->fetch_assoc();
			$niveau_e = $t_e['niveau_entrainement'];
			$nb_e = $t_e['nb'];
			
			// calcul pourcentage de reussite
			$sup = 100-$niveau_e;
			$mil = ceil($sup/2);
			$succes = mt_rand(0,100);
			
			if($succes <= $mil + $bonus_chance){
				
				if($nb_e == $niveau_e-1){
					$new_niveau = $niveau_e+1;
					$gain_xp = $new_niveau*2;
				
					// maj xp/pi/pa
					$sql = "UPDATE perso SET xp_perso=xp_perso+$gain_xp, pi_perso=pi_perso+$gain_xp, pa_perso=pa_perso-10 WHERE id_perso='$id_perso'";
					$mysqli->query($sql);
					
					// maj perso_as_entrainement
					$sql = "UPDATE perso_as_entrainement SET niveau_entrainement=niveau_entrainement+1, nb=0 WHERE id_perso='$id_perso'";
					$mysqli->query($sql);
					
					//mise a jour de la table evenement
					$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>',' a r&eacute;ussi son entrainement ',NULL,'',' : entrainement niveau $new_niveau',NOW(),'0')";
					$mysqli->query($sql);
					
					echo "<center>Vous venez d'effectuer votre $new_niveau ème entrainement avec succès</center><br />";
					echo "<center>Vous avez gagné $gain_xp xp</center>";
					echo "<center><a href='jouer.php' class='btn btn-primary'>retour</a></center>";
				}
				else {
					// maj pa
					$sql = "UPDATE perso SET pa_perso=pa_perso-10 WHERE id_perso='$id_perso'";
					$mysqli->query($sql);
				
					// maj perso_as_entrainement
					$sql = "UPDATE perso_as_entrainement SET nb=nb+1 WHERE id_perso='$id_perso'";
					$mysqli->query($sql);
					
					$nb_entrainement_restant = $niveau_e - $nb_e - 1;
					
					//mise a jour de la table evenement
					$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>',' a r&eacute;ussi son entrainement ',NULL,'',' : bientot au prochain niveau d\'entrainement',NOW(),'0')";
					$mysqli->query($sql);
					
					echo "<center>Vous avez réussi votre entrainement, entrainez vous encore $nb_entrainement_restant fois pour passer au niveau supérieur d'entrainement</center><br />";
					echo "<center><a href='jouer.php' class='btn btn-primary'>retour</a></center>";
				}				
			}
			else {
				// maj pa
				$sql = "UPDATE perso SET pa_perso=pa_perso-10 WHERE id_perso='$id_perso'";
				$mysqli->query($sql);
			
				//mise a jour de la table evenement
				$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>',' a rat&eacute; son entrainement ',NULL,'',' : dommage',NOW(),'0')";
				$mysqli->query($sql);
			
				echo "<center>Vous avez raté votre entrainement</center><br />";
				echo "<center>Vous êtes fatigué</center>";
				echo "<center><a href='jouer.php' class='btn btn-primary'>retour</a></center>";
			}
		}
		else {
			// on le cree
			$sql = "INSERT INTO perso_as_entrainement VALUES('$id_perso','1','0')";
			$mysqli->query($sql);
			
			// maj xp/pi/pa
			$sql = "UPDATE perso SET xp_perso=xp_perso+1, pi_perso=pi_perso+1, pa_perso=pa_perso-10 WHERE id_perso='$id_perso'";
			$mysqli->query($sql);
			
			//mise a jour de la table evenement
			$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>',' a réussi son premier entrainement ',NULL,'',' : bravo !',NOW(),'0')";
			$mysqli->query($sql);
			
			echo "<center>Vous venez d'effectuer votre premier entrainement avec succès</center><br />";
			echo "<center>Vous avez gagné 1xp</center>";
			echo "<center><a href='jouer.php' class='btn btn-primary'>retour</a></center>";
		}
	}
	else {
		echo "Pas assez de PA";
		echo "<center><a href='jouer.php' class='btn btn-primary'>retour</a></center>";
	}
}

/**
 * Fonction qui permet de donner un objet a un perso
 * @param $id_perso	: L'identifiant du personnage qui veut donner un objet
 * @param $id_cible	: L'identifiant du personnage a qui on veut donner un objet
 * @param $type_objet	: La nature de l'objet (1 => Or, 2 => Objet, 3 => Arme, 4 => Armure)
 * @param $id_objet	: L'identifiant de l'objet a donner
 * @param $quantite	: La quantite
 */
function action_don_objet($mysqli, $id_perso, $id_cible, $type_cible, $type_objet, $id_objet, $quantite){
	
	// On verifie que l'id du perso est correct
	$verif_idPerso = preg_match("#^[0-9]*[0-9]$#i","$id_perso");
	
	if($verif_idPerso && $id_perso != "" && $id_perso != null){
		
		// On verifie que l'id de la cible est correct
		$verif_idCible = preg_match("#^[0-9]*[0-9]$#i","$id_cible");
		
		if($verif_idCible && $id_cible != "" && $id_cible != null){
			
			// On verifie que l'id de l'objet est correct
			$verif_idObjet = preg_match("#^[0-9]*[0-9]$#i","$id_objet");
			
			if(($verif_idObjet || $id_objet=='-1') && $id_objet != "" && $id_objet != null){
					
				// On verifie que la cible est bien au CaC avec le perso
				// Recuperation coordonnees perso
				$sql_p = "SELECT x_perso, y_perso, pa_perso, nom_perso, clan FROM perso WHERE id_perso='$id_perso'";
				$res_p = $mysqli->query($sql_p);
				$t_p = $res_p->fetch_assoc();
				
				$x_perso 	= $t_p['x_perso'];
				$y_perso 	= $t_p['y_perso'];
				$pa_perso	= $t_p['pa_perso'];
				$nom_perso	= $t_p['nom_perso'];
				$camp_perso	= $t_p['clan'];
				
				$couleur_clan_p = couleur_clan($camp_perso);
				
				// Perso dans un batiment
				if (in_bat($mysqli, $id_perso)) {
					
					$sql = "SELECT id_instanceBat FROM perso_in_batiment WHERE id_perso='$id_perso'";
					$res = $mysqli->query($sql);
					$t = $res->fetch_assoc();
					
					$id_instance_bat = $t['id_instanceBat'];
					
					// On récupère la liste des persos dans le même batiment
					$sql_v = "SELECT id_perso as id_cible FROM perso_in_batiment WHERE id_instanceBat='$id_instance_bat' AND id_perso = '$id_cible'";
				}
				else {
					// Perso sur la carte
					
					$sql_v = "SELECT idPerso_carte as id_cible FROM carte 
							WHERE idPerso_carte='$id_cible' 
							AND occupee_carte='1' 
							AND x_carte<=$x_perso+1 AND x_carte>=$x_perso-1 AND y_carte<=$y_perso+1 AND y_carte>=$y_perso-1";
				}
				
				$res_v = $mysqli->query($sql_v);
				$verif_cac = $res_v->num_rows;
				$t_v = $res_v->fetch_assoc();
					
				$verif_cac_idCible = $t_v['id_cible'];
				
				if($verif_cac == 1 && $verif_cac_idCible == $id_cible){
					
					if ($pa_perso >= 1) {
					
						// On verifie que le perso possede bien l'objet qu'il souhaite donner
						// Si c'est de l'or : on verifie qu'il possede bien la bonne quantite
						if($type_objet == 1){
								
							$sql_vo = "SELECT or_perso FROM perso WHERE id_perso='$id_perso'";
							$res_vo = $mysqli->query($sql_vo);
							$t_vo = $res_vo->fetch_assoc();
								
							$or_perso = $t_vo['or_perso'];
							
							if($or_perso >= $quantite && $quantite > 0){
									
								// On met a jour l'or et les PA du perso
								$sql_u = "UPDATE perso SET pa_perso=pa_perso-1, or_perso=or_perso-$quantite WHERE id_perso='$id_perso'";
								$mysqli->query($sql_u);
									
								// On met a jour l'or de la cible
								$sql_u2 = "UPDATE perso SET or_perso=or_perso+$quantite WHERE id_perso='$id_cible'";
								$mysqli->query($sql_u2);
									
								// Recuperation infos cible
								$sql = "SELECT nom_perso, clan FROM perso WHERE id_perso='$id_cible'";
								$res = $mysqli->query($sql);
								$t = $res->fetch_assoc();
									
								$nom_cible = $t['nom_perso'];
								$clan_cible = $t['clan'];
								$couleur_clan_cible = couleur_clan($clan_cible);
								
								// mise a jour des evenements
								$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ('$id_perso','<font color=$couleur_clan_p><b>$nom_perso</b></font>','a fait un don à ','$id_cible','<font color=$couleur_clan_cible><b>$nom_cible</b></font>',' : $quantite thunes',NOW(),'0')";
								$mysqli->query($sql);
									
								echo "<center>Vous avez donné <b>$quantite thunes</b> à <font color='$couleur_clan_cible'><b>$nom_cible</b></font></center>";
							}
							else {
								echo "<font color='red'>Vous ne possédez pas assez d'or.</font>";
							}
							echo "<center><a href='jouer.php' class='btn btn-primary'>retour</a></center>";
						}
						
						// Si c'est un objet ou une arme ou une armure : on verifie qu'il le/la possede
						// Objet
						if($type_objet == 2){
								
							$sql_vo = "SELECT count(*) as q_obj FROM perso_as_objet WHERE id_perso='$id_perso' AND id_objet='$id_objet' AND equip_objet='0'";
							$res_vo = $mysqli->query($sql_vo);
							$t_vo = $res_vo->fetch_assoc();
								
							$q_obj = $t_vo['q_obj'];

							// Si l'objet est un étendard on ne peut le donner qu'à un chef
							if ($type_cible != 1 && ($id_objet == 8 || $id_objet == 9)){
								echo "<font color='red'>Vous ne pouvez pas donner un étendard à ce type d'unité.</font>";								
							} else {
								if($q_obj >= $quantite && $quantite > 0){
									
								// On supprime l'objet de l'inventaire du perso
								$sql_d = "DELETE FROM perso_as_objet WHERE id_perso='$id_perso' AND id_objet='$id_objet' AND equip_objet='0' LIMIT $quantite";
								$mysqli->query($sql_d);
									
								// Recuperation des infos de l'objet
								$sql = "SELECT poids_objet, nom_objet FROM objet WHERE id_objet='$id_objet'";
								$res = $mysqli->query($sql);
								$t = $res->fetch_assoc();
									
								$poids_objet 	= $t['poids_objet'];
								$nom_objet 		= $t['nom_objet'];
								
								$poids_total = $poids_objet * $quantite;
																	
								// On met a jour la charge du perso et ses PA
								$sql_u = "UPDATE perso SET pa_perso=pa_perso-1, charge_perso=charge_perso-$poids_total WHERE id_perso='$id_perso'";
								$mysqli->query($sql_u);
								
								for ($i = 0; $i < $quantite; $i++) {
									// On ajoute l'objet dans l'inventaire de la cible
									$sql_i = "INSERT INTO perso_as_objet (id_perso, id_objet) VALUES('$id_cible','$id_objet')";
									$mysqli->query($sql_i);
								}
								
								// On met a jour le poids de la cible
								$sql_u2 = "UPDATE perso SET charge_perso=charge_perso+$poids_total WHERE id_perso='$id_cible'";
								$mysqli->query($sql_u2);
									
								// Recuperation des informations de la cible
								$sql_c = "SELECT nom_perso, clan FROM perso WHERE id_perso='$id_cible'";
								$res_c = $mysqli->query($sql_c);
								$t_c = $res_c->fetch_assoc();
									
								$nom_cible 			= $t_c['nom_perso'];
								$clan_cible 		= $t_c['clan'];
								$couleur_clan_cible = couleur_clan($clan_cible);
								
								// mise a jour des evenements
								$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ('$id_perso','<font color=$couleur_clan_p><b>$nom_perso</b></font>','a fait un don à ','$id_cible','<font color=$couleur_clan_cible><b>$nom_cible</b></font>',' : $quantite <b>$nom_objet</b>',NOW(),'0')";
								$mysqli->query($sql);
									
								echo "<center>Vous avez donné $quantite <b>$nom_objet</b> à <font color='$couleur_clan_cible'><b>$nom_cible</b></font></center>";
								}
								else {
									echo "<font color='red'>Vous ne possédez pas l'objet que vous souhaitiez donner.</font>";
								}
								
							}
							
							echo "<center><a href='jouer.php' class='btn btn-primary'>retour</a></center>";
						}
								
						// Arme
						if($type_objet == 3){
								
							$sql_vo = "SELECT count(*) as q_arme FROM perso_as_arme WHERE id_perso='$id_perso' AND id_arme='$id_objet' AND est_portee='0'";
							$res_vo = $mysqli->query($sql_vo);
							$t_vo = $res_vo->fetch_assoc();
								
							$q_arme = $t_vo['q_arme'];
								
							if($q_arme >= $quantite && $quantite > 0){
									
								// On supprime l'arme de l'inventaire du perso
								$sql_d = "DELETE FROM perso_as_arme WHERE id_perso='$id_perso' AND id_arme='$id_objet' AND est_portee='0' LIMIT $quantite";
								$mysqli->query($sql_d);
									
								// recuperation des infos de l'arme
								$sql = "SELECT nom_arme, poids_arme FROM arme WHERE id_arme='$id_objet'";
								$res = $mysqli->query($sql);
								$t = $res->fetch_assoc();
									
								$nom_arme 		= $t['nom_arme'];
								$poids_arme 	= $t['poids_arme'];
								
								$poids_total = $poids_arme * $quantite;
									
								// On met a jour la charge du perso et ses PA
								$sql_u = "UPDATE perso SET pa_perso=pa_perso-1, charge_perso=charge_perso-$poids_total WHERE id_perso='$id_perso'";
								$mysqli->query($sql_u);
								
								for ($i = 0; $i < $quantite; $i++) {
									// On ajoute l'arme a l'inventaire de la cible
									$sql_i = "INSERT INTO perso_as_arme (id_perso, id_arme, est_portee) VALUES('$id_cible','$id_objet','0')";
									$mysqli->query($sql_i);
								}
									
								// On met a jour le poids de la cible
								$sql_u = "UPDATE perso SET charge_perso=charge_perso+$poids_total WHERE id_perso='$id_cible'";
								$mysqli->query($sql_u);
									
								// Recuperation des informations de la cible
								$sql_c = "SELECT nom_perso, clan FROM perso WHERE id_perso='$id_cible'";
								$res_c = $mysqli->query($sql_c);
								$t_c = $res_c->fetch_assoc();
									
								$nom_cible = $t_c['nom_perso'];
								$clan_cible = $t_c['clan'];
								$couleur_clan_cible = couleur_clan($clan_cible);
								
								// mise a jour des evenements
								$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ('$id_perso','<font color=$couleur_clan_p><b>$nom_perso</b></font>','a fait un don à ','$id_cible','<font color=$couleur_clan_cible><b>$nom_cible</b></font>',' : $quantite <b>$nom_arme</b>',NOW(),'0')";
								$mysqli->query($sql);
								
								echo "<center>Vous avez donné $quantite <b>$nom_arme</b> à <font color='$couleur_clan_cible'><b>$nom_cible</b></font></center>";
							}
							else {
								echo "<font color='red'>Vous ne possédez pas l'arme que vous souhaitiez donner.</font>";
							}
							echo "<center><a href='jouer.php' class='btn btn-primary'>retour</a></center>";
						}
					}
					else {
						echo "<font color='red'>Vous ne possédez pas assez de PA pour pouvoir faire un don (cout : 1 PA)</font>";
						echo "<center><a href='jouer.php' class='btn btn-primary'>retour</a></center>";
					}
				}
				else {
					echo "<font color='red'>La cible n'est pas au Corps à corps.</font>";
					echo "<center><a href='jouer.php' class='btn btn-primary'>retour</a></center>";
				}
			}
			else {
				echo "<font color='red'>L'objet choisi n'est pas correct, si le problème persiste, veuillez contacter l'administrateur.</font><br/>";
				echo "<center><a href='jouer.php' class='btn btn-primary'>retour</a></center>";
			}
		}
		else {
			echo "<font color='red'>La cible n'est pas correcte, si le problème persiste, veuillez contacter l'administrateur.</font><br/>";
			echo "<center><a href='jouer.php' class='btn btn-primary'>retour</a></center>";
		}
	}
	else {
		echo "<font color='red'>Votre identifiant est mal renseigné, si le problème persiste, veuillez contacter l'administrateur.</font><br/>";
		echo "<center><a href='jouer.php' class='btn btn-primary'>retour</a></center>";
	}
}
 

/**
  * Fonction qui permet a un perso de deposer un objet
  * @param $id_perso	: L'identifiant du personnage qui veut deposer un objet
  * @param $type_objet	: La nature de l'objet (1 => Or, 2 => Objet, 3 => Arme, 4 => Armure)
  * @param $id_objet	: L'identifiant de l'objet a deposer
  * @param $quantite	: La quantité déposée
  * @return Void
  */
function action_deposerObjet($mysqli, $id_perso, $type_objet, $id_objet, $quantite){

	// Verification que le perso possede bien cet objet
	//Or
	if($type_objet == 1){
		$sql_vo = "SELECT or_perso FROM perso WHERE id_perso='$id_perso'";
		$res_vo = $mysqli->query($sql_vo);
		$t_vo = $res_vo->fetch_assoc();
			
		$or_perso = $t_vo['or_perso'];
		$poid_objet = 0;
		
		if($or_perso >= $quantite && $quantite > 0){
			$nb = $or_perso;
		}else{
			$nb = false;
		}
	}

	// Objet
	if($type_objet == 2){
		$sql = "SELECT perso_as_objet.id_objet, poids_objet FROM perso_as_objet, objet WHERE id_perso='$id_perso' 
				AND perso_as_objet.id_objet='$id_objet'
				AND perso_as_objet.id_objet = objet.id_objet";
		$res = $mysqli->query($sql);
		$nb = $res->num_rows;
		$t = $res->fetch_assoc();
		
		$poid_objet = $t["poids_objet"];
	}
	
	// Arme
	if($type_objet == 3){
		$sql = "SELECT perso_as_arme.id_arme, poids_arme FROM perso_as_arme, arme WHERE id_perso='$id_perso' 
				AND perso_as_arme.id_arme='$id_objet' AND est_portee='0'
				AND perso_as_arme.id_arme = arme.id_arme";
		$res = $mysqli->query($sql);
		$nb = $res->num_rows;
		$t = $res->fetch_assoc();
		
		$poid_objet = $t["poids_arme"];
	}
	
	if($nb && intval($nb)>=intval($quantite)){
		
		$coutPa = 1;
		
		// verification que le perso a assez de PA et recuperation de ses coordonnees
		$sql = "SELECT pa_perso, x_perso, y_perso FROM perso WHERE id_perso='$id_perso'";
		$res = $mysqli->query($sql);
		$t = $res->fetch_assoc();
		
		$x_perso 	= $t["x_perso"];
		$y_perso 	= $t["y_perso"];
		$pa_perso 	= $t["pa_perso"];
		
		if($pa_perso >= $coutPa){
			// On depose l'objet
			
			$poid_total = $poid_objet * $quantite;
			
			// Or
			If($type_objet == 1){
				// On met a jour l'or du perso
				$sql_u = "UPDATE perso SET or_perso=or_perso-$quantite WHERE id_perso='$id_perso'";
				$mysqli->query($sql_u);
			}
			// Objet
			if($type_objet == 2){
				// Suppression de l'inventaire du perso
				$sql = "DELETE FROM perso_as_objet WHERE id_perso='$id_perso' AND id_objet='$id_objet' AND equip_objet='0' LIMIT $quantite";
				$mysqli->query($sql);
			}
			
			// Arme
			if($type_objet == 3){
				// Suppression de l'inventaire du perso
				$sql = "DELETE FROM perso_as_arme WHERE id_perso='$id_perso' AND id_arme='$id_objet' AND est_portee='0' LIMIT $quantite";
				$mysqli->query($sql);
			}
			
			// On met a jour le poid et les pa du perso
			$sql = "UPDATE perso SET charge_perso = charge_perso - $poid_total, pa_perso=pa_perso-1 WHERE id_perso='$id_perso'";
			$mysqli->query($sql);
			
			
			// Verification si l'objet existe deja sur cette case
			$sql = "SELECT nb_objet FROM objet_in_carte, perso WHERE id_perso='$id_perso'
					AND objet_in_carte.x_carte = perso.x_perso AND objet_in_carte.y_carte = perso.y_perso
					AND type_objet = '$type_objet' AND id_objet = '$id_objet'";
			$res = $mysqli->query($sql);
			$to = $res->fetch_assoc();
			
			$nb_o = $to["nb_objet"];
			
			if($nb_o){
				// On met a jour le nombre
				$sql = "UPDATE objet_in_carte SET nb_objet = nb_objet + $quantite
						WHERE type_objet='$type_objet' AND id_objet='$id_objet'
						AND x_carte='$x_perso' AND y_carte='$y_perso'";
				$mysqli->query($sql);
			}
			else {
				// Insertion dans la table objet_in_carte : On cree le premier enregistrement
				$sql = "INSERT INTO objet_in_carte (type_objet, id_objet, nb_objet, x_carte, y_carte) VALUES ('$type_objet','$id_objet','$quantite','$x_perso','$y_perso')";
				$mysqli->query($sql);
			}
			
			echo "<center>Vous venez de déposer un objet à terre</center><br />";
			echo "<center><a class='btn btn-primary' href='jouer.php'>retour</a></center>";

			// Recup infos perso
			$sql = "SELECT type_perso, clan FROM perso WHERE perso.id_perso='$id_perso'";
			$res = $mysqli->query($sql);
			$t_perso2 = $res->fetch_assoc();
			
			$type_perso		= $t_perso2['type_perso'];
			$clan			= $t_perso2['clan'];
		}
	}
	else {
		// Triche ?
		echo "<center>Vous ne possédez pas le nombre requis</center><br>";
		echo "<center><a href='jouer.php' class='btn btn-primary'>retour</a></center>";
	}
}

/**
  * Fonction qui verifie si un perso est un marchand et retourne le nombre de points de marchandage
  * @param $id_perso	: L'identifiant du personnage
  * @return Int		: Le nombre de points dans la competence de marchandage
  */
function est_marchand($mysqli, $id_perso){
	
	$sql = "SELECT nb_points FROM perso_as_competence WHERE id_perso='$id_perso' AND id_competence='50'";
	$res = $mysqli->query($sql);
	$t = $res->fetch_assoc();
		
	return $t['nb_points'];
	
	
}

/** 
  * Fonction qui verifie si le perso est un ami des animaux et retourne le nombre de points
  * @param $id_perso	: L'identifiant du personnage
  * @return Int		: le nombre de points dans la competence d'amitie avec les animaux
  */
function est_ami_animaux($mysqli, $id_perso){
	
	$sql = "SELECT nb_points FROM perso_as_competence WHERE id_perso='$id_perso' AND id_competence='38'";
	$res = $mysqli->query($sql);
	$t = $res->fetch_assoc();
	
	return $t['nb_points'];
}

/**
  * Fonction qui permet de recuperer le nombre de morceaux de bois que le personnage possede
  * @param $id_perso	: L'identifiant du personnage
  * @return Int		: Le nombre de morceaux de bois que le personnage possede dans son sac
  */
function nb_bois_perso($mysqli, $id_perso){
	
	$sql = "SELECT count(*) as nb_bois FROM perso_as_objet WHERE id_perso='$id_perso' AND id_objet='70'";
	$res = $mysqli->query($sql);
	$t_nb_bois = $res->fetch_assoc();
	
	return $t_nb_bois['nb_bois'];
	
}

/**
  * Fonction qui verifie si une arme d'identifiant passe en parametre existe bien
  * @param $id_arme	: L'identifiant de l'arme
  * @return Bool
  */
function existe_arme($mysqli, $id_arme){
	$sql = "SELECT id_arme FROM arme WHERE id_arme='$id_arme'";
	$res = $mysqli->query($sql);
	$num = $res->num_rows;
	
	return $num == 1;
}

/**
  * Fonction qui verifie si une armure d'identifiant passe en parametre existe bien
  * @param $id_armure	: L'identifiant de l'armure
  * @return Bool
  */
function existe_armure($mysqli, $id_armure){
	$sql = "SELECT id_armure FROM armure WHERE id_armure='$id_armure'";
	$res = $mysqli->query($sql);
	$num = $res->num_rows;
	
	return $num == 1;
}

/**
  * Fonction qui verifie si un objet d'identifiant passe en parametre existe bien
  * @param $id_objet	: L'identifiant de l'objet
  * @return Bool
  */
function existe_objet($mysqli, $id_objet){
	$sql = "SELECT id_objet FROM objet WHERE id_objet='$id_objet'";
	$res = $mysqli->query($sql);
	$num = $res->num_rows;
	
	return $num == 1;
}

/**
  * Fonction qui permet de calculer la recuperation de malus selon l'action utilisee
  * @param $id_action	: L'identifiant de l'action
  * @return			: La reuperation de malus
  */
function calcul_recup_malus($id_action){
	if($id_action == '140'){
		$recup_malus = 1;
	}
	if($id_action == '141'){
		$recup_malus = rand(1,2);
	}
	if($id_action == '142'){
		$recup_malus = rand(1,4);
	}
	return $recup_malus;
}

/**
  * Fonction qui calcul le nombre de pv de reparation du batiment le personnage va effectuer
  * @param $id_action	: L'identifiant de l'action
  * @return int			: Le nombre de pv de reparation
  */
function calcul_pv_reparation($id_action){
	
	if($id_action == '76'){
		$pv_reparation = rand(20, 120);
	}
	
	return $pv_reparation;
}

/**
  * Fonction qui calcul le gain de pv sur une action de type reparation
  * @param $nb_points_action	: Le niveau de l'action
  * @return int			: Le gain en pv
  */
function calcul_gain_pv_reparer($nb_points_action){
	if($nb_points_action == 1 || $nb_points_action == 2){
		$gain_pv = rand(5,10);
	}
	if($nb_points_action == 3 || $nb_points_action == 4){
		$gain_pv = rand(8,15);
	}
	if($nb_points_action == 5 || $nb_points_action == 6){
		$gain_pv = rand(10,20);
	}
	return $gain_pv;
}

/**
  * Fonction qui recupere l'identifiant de competence correspondant a la construction d'un type de batiment
  * @param $id_batiment	: l'identifiant du batiment
  * @return  Int			: L'identifant de la competence associee a la construction du type de batiment passe en parametre
  */
function recup_id_competence_bat($id_batiment){
	if($id_batiment == 1){
		// barricade
		$id_competence = 22;
	}
	if($id_batiment == 2){
		// tour de visu
		$id_competence = 24;
	}
	if($id_batiment == 3){
		// tour de garde
		$id_competence = 25;
	}
	if($id_batiment == 4){
		// route
		$id_competence = 21;
	}
	if($id_batiment == 5){
		// pont
		$id_competence = 23;
	}
	if($id_batiment == 6){
		// entrepot
		$id_competence = 26;
	}
	if($id_batiment == 7){
		// hopital
		$id_competence = 27;
	}
	if($id_batiment == 8){
		// fortin
		$id_competence = 28;
	}
	if($id_batiment == 9){
		// fort
		$id_competence = 29;
	}
	if($id_batiment == 10){
		// prison
		//$id_competence = 0;
	}
	
	return $id_competence;
}

/**
  * Fonction qui calcul le pourcentage de reussite ou autre pourcentage d'une action donnee
  * @param $id_action	: L'identifiant de l'action
  * @return Int		: le pourcentage de reussite ou autre pourcentage recupere de l'action
  */
function calcul_pourcentage_action($id_action){
	
	//recuperation du pourcentage
	if(	/*premier soin*/ $id_action == '11' || $id_action == '12' || $id_action == '13'){
		$pourcent = 10;
	}
	
	if(	/*Upgrade batiment*/ $id_action == '80' || $id_action == '83'
		/* Soins avances */ 	|| $id_action == '14' || $id_action == '15' || $id_action == '16'){
		$pourcent = 25;
	}
	
	if(	/*Upgrade batiment*/ $id_action == '81' || $id_action == '84'
		/* Chirurgie */ 		|| $id_action == '22' || $id_action == '23' || $id_action == '24'
		/*Reparer Arme*/ 	|| $id_action == '115' 
		/*Reparer Armure*/ 	|| $id_action == '124'){
		$pourcent = 50;
	}
	
	if(	/*Reparer Arme*/	$id_action == '116' || $id_action == '117' 
		/*Reparer Armure*/	|| $id_action == '125' || $id_action == '126'){
		$pourcent = 65;
	}
	
	if(	/*Upgrade batiment*/ $id_action == '82' || $id_action == '85'
		/*Reparer Arme*/	|| $id_action == '118' || $id_action == '119'
		/*Reparer Armure*/	|| $id_action == '127' || $id_action == '128'){
		$pourcent = 75;
	}
	
	if(	/*Reparer Arme*/	$id_action == '120'
		/*Reparer Armure*/	|| $id_action == '129'){
		$pourcent = 95;
	}
	
	if(	/*chirurgie de guerre*/ $id_action == '25' || $id_action == '26' || $id_action == '27'){
		$pourcent = 100;
	}
	
	return $pourcent;
}

/**
 * Fonction appelée lorsqu'une charge se termine sur un bâtiment
 */
function charge_batiment($mysqli, $id_perso, $nom_perso, $image_perso, $pv_perso, $couleur_clan_perso, $x_perso_final, $y_perso_final, $nb_deplacements) {
	
	$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte='$id_perso', image_carte='$image_perso' WHERE x_carte = $x_perso_final AND y_carte = $y_perso_final";
	$mysqli->query($sql);
	
	// Mise à jour du perso
	$sql = "UPDATE perso SET pv_perso = pv_perso - 40, pm_perso = pm_perso - $nb_deplacements, x_perso = $x_perso_final, y_perso = $y_perso_final WHERE id_perso='$id_perso'";
	$mysqli->query($sql);
	
	if ($pv_perso <= 40) {
		// Perso rapatrié
		
		$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>',' s\'est écrasé contre un batiment suite a sa charge et a perdu connaissance ! ',NULL,'','',NOW(),'0')";
		$mysqli->query($sql);
		
	} else {
	
		$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>',' s\'est écrasé contre un batiment suite a sa charge ! ',NULL,'','',NOW(),'0')";
		$mysqli->query($sql);
		
	}
}

/**
 * Fonction appelée lorsque la charge est incomplète (obstacle rencontré avant de pouvoir terminer sa charge)
 */
function charge_incomplete($mysqli, $id_perso, $nom_perso, $image_perso, $couleur_clan_perso, $x_perso, $y_perso, $x_perso_final, $y_perso_final, $nb_deplacements) {
	
	// MAJ perso sur carte
	$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_perso' AND y_carte='$y_perso'";
	$mysqli->query($sql);
	
	$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte='$id_perso', image_carte='$image_perso' WHERE x_carte = $x_perso_final AND y_carte = $y_perso_final";
	$mysqli->query($sql);
		
	// Mise à jour du perso
	$sql = "UPDATE perso SET pm_perso = pm_perso - $nb_deplacements, x_perso = $x_perso_final, y_perso = $y_perso_final WHERE id_perso='$id_perso'";
	$mysqli->query($sql);
	
	// Charge incomplete => pas d'attaques
	$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>',' a rencontré un obstacle ne lui permettant pas de terminer sa charge ',NULL,'','',NOW(),'0')";
	$mysqli->query($sql);
}

/**
 * Fonction appelée lorsque la charge est complète mais que la cible ne se trouve pas sur une plaine
 */
function charge_cible_terrain_impraticable($mysqli, $id_perso, $nom_perso, $image_perso, $couleur_clan_perso, $x_perso_final, $y_perso_final, $nb_deplacements) {
	
	$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte='$id_perso', image_carte='$image_perso' WHERE x_carte = $x_perso_final AND y_carte = $y_perso_final";
	$mysqli->query($sql);
	
	// Mise à jour du perso
	$sql = "UPDATE perso SET pm_perso = pm_perso - $nb_deplacements, x_perso = $x_perso_final, y_perso = $y_perso_final WHERE id_perso='$id_perso'";
	$mysqli->query($sql);
	
	// cible pas sur plaine => charge ratée
	$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>',' a raté sa charge, le terrain étant impraticable! ',NULL,'','',NOW(),'0')";
	$mysqli->query($sql);
}

/**
 * Fonction appelée lorsque la charge se passe sans encombre et donc que les attaques peuvent être effectuées
 */
function charge_bonne($mysqli, $id_perso, $nom_perso, $image_perso, $clan, $couleur_clan_perso, $grade_perso, $pa_perso, $xp_perso, $x_perso_final, $y_perso_final, $nb_deplacements, $idPerso_carte) {
	
	$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte='$id_perso', image_carte='$image_perso' WHERE x_carte = $x_perso_final AND y_carte = $y_perso_final";
	$mysqli->query($sql);
	
	$sql = "SELECT gain_xp_tour, type_perso FROM perso WHERE id_perso='$id_perso'";
	$res = $mysqli->query($sql);
	$t = $res->fetch_assoc();
	
	$gain_xp_tour_perso = $t['gain_xp_tour'];
	$type_perso = $t['type_perso'];
	if ($gain_xp_tour_perso >= 20) {
		$max_xp_tour_atteint = true;
	}
	else {
		$max_xp_tour_atteint = false;
	}

	// Bonus PC cavalerie lourde
	$gain_bonus_pc = false;
	if ($type_perso == 2 && $idPerso_carte < 200000)
		$gain_bonus_pc = true;

	// Bonus charge 30 pour cav lourde, 20 pour cav légère
	$base_bonus_degats_charge = 30;
	$diminution_bonus_degats_charge = 10;
	if ($type_perso == 7)
		$base_bonus_degats_charge = 20;
	else if ($type_perso == 3) {
		$base_bonus_degats_charge = 20;
		$diminution_bonus_degats_charge = 5;
	}
	
	// Mise à jour du perso
	$sql = "UPDATE perso SET pm_perso = pm_perso - $nb_deplacements, x_perso = $x_perso_final, y_perso = $y_perso_final WHERE id_perso='$id_perso'";
	$mysqli->query($sql);
	
	// Attaques arme CaC
	// Recuperation caracs de l'arme CaC equipé
	$sql = "SELECT arme.id_arme, nom_arme, coutPa_arme, degatMin_arme, valeur_des_arme, precision_arme FROM arme, perso, perso_as_arme 
			WHERE perso_as_arme.id_perso = perso.id_perso 
			AND perso_as_arme.id_arme = arme.id_arme
			AND perso_as_arme.est_portee = '1' 
			AND arme.porteeMax_arme = '1'
			AND perso.id_perso = '$id_perso'";
	$res = $mysqli->query($sql);
	$nb = $res->num_rows;

	if ($nb) {
		$t_arme = $res->fetch_assoc();
		$id_arme_attaque	= $t_arme['id_arme'];
		$nom_arme 			= $t_arme['nom_arme'];
		$degats_arme 		= $t_arme['degatMin_arme'];
		$valeur_des_arme	= $t_arme['valeur_des_arme'];
		$precision_arme 	= $t_arme['precision_arme'];
		$coutPa_arme		= $t_arme['coutPa_arme'];
	} else {
		// Poings
		$id_arme_attaque	= 1000;
		$nom_arme 		= "Poings";
		$degats_arme 		= 4;
		$valeur_des_arme	= 6;
		$precision_arme 	= 30;
		$coutPa_arme		= 3;
	}
	
	// recup id joueur perso
	$sql = "SELECT idJoueur_perso FROM perso WHERE id_perso='$id_perso'";
	$res = $mysqli->query($sql);
	$t_j = $res->fetch_assoc();
	
	$id_j_perso = $t_j["idJoueur_perso"];
	
	$sql = "SELECT perso.id_perso, pc_perso, perso_as_grade.id_grade FROM perso, perso_as_grade WHERE perso.id_perso = perso_as_grade.id_perso AND idJoueur_perso='$id_j_perso' AND chef='1'";
	$res = $mysqli->query($sql);
	$t_chef = $res->fetch_assoc();
	
	$id_perso_chef = $t_chef["id_perso"];
	$pc_perso_chef = $t_chef["pc_perso"];
	$id_grade_chef = $t_chef["id_grade"];
	
	if ($idPerso_carte >= 200000) {
		// PNJ
		
		// Récupération des infos de la cible
		$sql = "SELECT nom_pnj, pv_i, bonus_i, x_i, y_i FROM instance_pnj, pnj WHERE pnj.id_pnj = instance_pnj.id_pnj AND idInstance_pnj = '$idPerso_carte'";
		$res = $mysqli->query($sql);
		$t_cible = $res->fetch_assoc();
		
		$nom_cible 		= $t_cible['nom_pnj'];
		$x_cible		= $t_cible['x_i'];
		$y_cible		= $t_cible['y_i'];
		$pv_cible		= $t_cible['pv_i'];
		$bonus_cible	= $t_cible['bonus_i'];
		$protec_cible	= 0;
		
		$couleur_clan_cible = 'black';
		
		$gain_pc_cible = 0;

		// maj dernierAttaquant_i
		$sql = "UPDATE instance_pnj SET dernierAttaquant_i = $id_perso WHERE idInstance_pnj = '$idPerso_carte'";
		$mysqli->query($sql);
		
	} else {
	
		// Récupération des infos de la cible
		$sql = "SELECT nom_perso, x_perso, y_perso, pv_perso, xp_perso, or_perso, protec_perso, bonus_perso, idJoueur_perso, clan, perso_as_grade.id_grade, nom_grade 
				FROM perso, perso_as_grade, grades
				WHERE perso_as_grade.id_perso = perso.id_perso
				AND perso_as_grade.id_grade = grades.id_grade
				AND perso.id_perso = '$idPerso_carte'";
		$res = $mysqli->query($sql);
		$t_cible = $res->fetch_assoc();
		
		$nom_cible 			= $t_cible['nom_perso'];
		$x_cible			= $t_cible['x_perso'];
		$y_cible			= $t_cible['y_perso'];
		$pv_cible			= $t_cible['pv_perso'];
		$xp_cible 			= $t_cible['xp_perso'];
		$or_cible 			= $t_cible['or_perso'];
		$bonus_cible		= $t_cible['bonus_perso'];
		$protec_cible		= $t_cible['protec_perso'];
		$grade_cible		= $t_cible['id_grade'];
		$nom_grade_cible	= $t_cible['nom_grade'];
		$id_joueur_cible 	= $t_cible['idJoueur_perso'];
		$clan_cible 		= $t_cible['clan'];
		
		// Récupération de la couleur associée au clan de la cible
		$couleur_clan_cible = couleur_clan($clan_cible);
		
		// Vérifie si le joueur attaqué a coché l'envoi de mail
		$mail_info_joueur = verif_coche_mail($mysqli, $id_joueur_cible);
		
		if($mail_info_joueur){
			// Envoi du mail
			mail_attaque($mysqli, $nom_perso, $idPerso_carte);
		}
		
		// Si perso ou cible est une infanterie 
		// ou si grade perso >= grade cible - 1
		if ((( $grade_perso <= $grade_cible + 1 && $grade_cible != 101 && $grade_cible != 102 )
				|| $grade_perso == 1 || $grade_perso == 101 || $grade_perso == 102 
				|| (($grade_cible == 1 || $grade_cible == 101 || $grade_cible == 102) && $grade_perso == 2)) && ($clan_cible != $clan)) {
			
			$gain_pc_cible = 1;
		} else {
			$gain_pc_cible = 0;
		}
		
		if($clan_cible==0){
			$gain_pc_cible = 0;
		}
	
	}
	
	if ($pv_cible > 0) {
	
		$nb_attaque 	= 0;
		$cible_alive 	= true;
		$gain_pc		= 0;
		$gain_total_xp	= 0;
		
		$sql = "SELECT nom_grade FROM grades WHERE id_grade='$grade_perso'";
		$res = $mysqli->query($sql);
		$tgp = $res->fetch_assoc();
		
		$nom_grade_perso = $tgp["nom_grade"];
		
		// On attaque tant qu'il reste des PA
		while ($pa_perso >= $coutPa_arme && $cible_alive) {
			
			$gain_pc = $gain_pc_cible;
			
			// MAJ des pa du perso
			$pa_perso = $pa_perso - $coutPa_arme;
			
			$sql = "UPDATE perso SET pa_perso = $pa_perso WHERE id_perso='$id_perso'";
			$mysqli->query($sql);
			
			// Est ce que le perso touche sa cible ?
			$touche = mt_rand(0, 100);
			
			// Où se trouve la cible ?
			$sql = "SELECT fond_carte FROM carte WHERE x_carte='$x_cible' AND y_carte='$y_cible'";
			$res = $mysqli->query($sql);
			$t = $res->fetch_assoc();
			
			$fond_carte_cible = $t['fond_carte'];
			
			$bonus_defense_terrain = get_bonus_defense_terrain($fond_carte_cible, 1);
			// Bonus defense objets cible 
			$bonus_defense_objet = get_bonus_defense_objet($mysqli, $idPerso_carte);
			
			$precision_final = $precision_arme - $bonus_cible - $bonus_defense_terrain - $bonus_defense_objet;
			
			$bonus_precision_objet = getBonusPrecisionCacObjet($mysqli, $id_perso);
			
			$precision_final += $bonus_precision_objet;
			
			if ($touche <= $precision_final && $touche < 98) {
				// Le perso touche sa cible
				
				// calcul des dégats
				$bonus_degats_charge = max(0, $base_bonus_degats_charge - $nb_attaque*$diminution_bonus_degats_charge);
				$degats = calcul_des_attaque($degats_arme, $valeur_des_arme) - $protec_cible + $bonus_degats_charge;
				
				$degats_tmp = calcul_des_attaque($degats_arme, $valeur_des_arme);
				$degats_tmp_bonus = $degats_tmp + $bonus_degats_charge;
				
				// Insertion log attaque
				$message_log = $id_perso.' a chargé '.$idPerso_carte.' - degats avec bonus : '.$degats_tmp_bonus;
				$type_action = "Attaque ".$id_arme_attaque;
				$sql = "INSERT INTO log (date_log, id_perso, type_action, id_arme, degats, pourcentage, message_log) VALUES (NOW(), '$id_perso', '$type_action', '$id_arme_attaque', '$degats_tmp', '$touche', '$message_log')";
				$mysqli->query($sql);
				
				if ($touche <= 2) {
					// Coup critique ! Dégats et Gains PC X 2
					$degats = $degats * 2;
					$gain_pc = $gain_pc * 2;
				}
				
				if ($gain_total_xp < 20 && !$max_xp_tour_atteint) {
				
					// calcul gain experience
					if ($idPerso_carte >= 200000) {
						$gain_experience = mt_rand(1, 4);
						if ($gain_xp_tour_perso + $gain_experience > 20) {
							$gain_experience = 20 - $gain_xp_tour_perso;
							$max_xp_tour_atteint = true;
						}
					} else {
					
						$calcul_dif_xp = ceil(($xp_cible - $xp_perso) / 10);
										
						if ($calcul_dif_xp < 0) {
							$valeur_des_xp = 0;
						} else {
							$valeur_des_xp = mt_rand(0, $calcul_dif_xp);
						}
						
						$gain_experience = ceil(($degats / 20) + $valeur_des_xp);
						
						if ($gain_experience > 10) {
							$gain_experience = 10;
						}
						
						if ($gain_xp_tour_perso + $gain_experience > 20) {
							$gain_experience = 20 - $gain_xp_tour_perso;
							$max_xp_tour_atteint = true;
						}
					}
					
					$old_gain_total_xp = $gain_total_xp;
					$gain_total_xp += $gain_experience;
					
					if ($gain_total_xp >= 20) {
						$gain_experience = 20 - $old_gain_total_xp;
					}
				}
				else {
					$gain_experience = 0;
				}
				
				// Maj cible malus et PV
				$bonus_cible -= 2;
				
				if ($idPerso_carte < 50000) {
					$sql = "UPDATE perso SET bonus_perso = bonus_perso - 2, pv_perso = pv_perso - $degats WHERE id_perso='$idPerso_carte'";
					$mysqli->query($sql);
				}
				else {
					$sql = "UPDATE instance_pnj SET bonus_i = bonus_i - 2, pv_i = pv_i - $degats WHERE idInstance_pnj='$idPerso_carte'";
					$mysqli->query($sql);
				}
				
				// evenement attaque
				$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>','a chargé ','$idPerso_carte','<font color=$couleur_clan_cible><b>$nom_cible</b></font>','( Précision : $touche / $precision_final ; Dégât : $degats ; Gain XP : $gain_experience ; Gain PC : $gain_pc )',NOW(),'0')";
				$mysqli->query($sql);
				
				
				if ($idPerso_carte >= 200000) {
					// Récupération des infos de la cible
					$sql = "SELECT pv_i as pv_perso FROM instance_pnj WHERE idInstance_pnj = '$idPerso_carte'";
				}
				else {
					// Récupération des infos de la cible
					$sql = "SELECT pv_perso FROM perso WHERE id_perso = '$idPerso_carte'";
				}
				
				$res = $mysqli->query($sql);
				$t_pv = $res->fetch_assoc();
				
				$pv_cible = $t_pv['pv_perso'];
				
				// Verification si cible morte							
				if ($pv_cible <= 0) {
					
					$cible_alive = false;
					
					// Perte or 
					$calcul_perte_or = 0;
					
					if ($idPerso_carte < 50000) {
						// Perte or 
						$calcul_perte_or = gain_po_mort($or_cible);
						
						// MAJ perte thunes cible
						$sql = "UPDATE perso SET or_perso = or_perso - $calcul_perte_or WHERE id_perso='$idPerso_carte'";
						$mysqli->query($sql);
					}
					else {
						// On efface l'instance de PNJ 
						$sql = "DELETE FROM instance_pnj WHERE idInstance_pnj='$idPerso_carte'";
						$mysqli->query($sql);
					}
					
					// on l'efface de la carte
					$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_cible' AND y_carte='$y_cible'";
					$mysqli->query($sql);
					
					if ($calcul_perte_or > 0) {
						// On dépose la perte de PO par terre
						// Verification si l'objet existe deja sur cette case
						$sql = "SELECT nb_objet FROM objet_in_carte 
								WHERE objet_in_carte.x_carte = $x_cible
								AND objet_in_carte.y_carte = $y_cible
								AND type_objet = '1' AND id_objet = '0'";
						$res = $mysqli->query($sql);
						$to = $res->fetch_assoc();
						
						$nb_o = $to["nb_objet"];
						
						if($nb_o){
							// On met a jour le nombre
							$sql = "UPDATE objet_in_carte SET nb_objet = nb_objet + $calcul_perte_or 
									WHERE type_objet='1' AND id_objet='0'
									AND x_carte='$x_cible' AND y_carte='$y_cible'";
							$mysqli->query($sql);
						}
						else {
							// Insertion dans la table objet_in_carte : On cree le premier enregistrement
							$sql = "INSERT INTO objet_in_carte (type_objet, id_objet, nb_objet, x_carte, y_carte) VALUES ('1','0','$calcul_perte_or','$x_cible','$y_cible')";
							$mysqli->query($sql);
						}
					}
					
					if ($idPerso_carte < 50000) {
						
						$id_arme_non_equipee = id_arme_non_equipee($mysqli, $idPerso_carte);
						$test_perte = mt_rand(0,100);

						
						if ($id_arme_non_equipee > 0 && $test_perte <= 40) {
														
							// Suppression de l'arme de l'inventaire du perso
							$sql = "DELETE FROM perso_as_arme WHERE id_perso='$idPerso_carte' AND id_arme='$id_arme_non_equipee' AND est_portee='0' LIMIT 1";
							$mysqli->query($sql);
							
							// Maj charge perso suite perte de l'arme
							$sql = "UPDATE perso SET charge_perso = charge_perso - (SELECT poids_arme FROM arme WHERE id_arme='$id_arme_non_equipee') WHERE id_perso='$idPerso_carte'";
							$mysqli->query($sql);
							
							// On dépose la perte de l'arme par terre
							// Verification si l'objet existe deja sur cette case
							$sql = "SELECT nb_objet FROM objet_in_carte 
									WHERE objet_in_carte.x_carte = $x_cible 
									AND objet_in_carte.y_carte = $y_cible 
									AND type_objet = '3' AND id_objet = '$id_arme_non_equipee'";
							$res = $mysqli->query($sql);
							$to = $res->fetch_assoc();
							
							$nb_o = $to["nb_objet"];
							
							if($nb_o){
								// On met a jour le nombre
								$sql = "UPDATE objet_in_carte SET nb_objet = nb_objet + 1 
										WHERE type_objet='3' AND id_objet='$id_arme_non_equipee'
										AND x_carte='$x_cible' AND y_carte='$y_cible'";
								$mysqli->query($sql);
							}
							else {
								// Insertion dans la table objet_in_carte : On cree le premier enregistrement
								$sql = "INSERT INTO objet_in_carte (type_objet, id_objet, nb_objet, x_carte, y_carte) VALUES ('3','$id_arme_non_equipee','1','$x_cible','$y_cible')";
								$mysqli->query($sql);
							}
						}
						
						// evenement perso capture
						$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>','<b>a capturé</b>','$idPerso_carte','<font color=$couleur_clan_cible><b>$nom_cible</b></font> ($nom_grade_cible)','',NOW(),'0')";
						$mysqli->query($sql);
						
						// maj cv
						$sql = "INSERT INTO `cv` (IDActeur_cv, nomActeur_cv, gradeActeur_cv, IDCible_cv, nomCible_cv, gradeCible_cv, date_cv) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>', '$nom_grade_perso', '$idPerso_carte','<font color=$couleur_clan_cible>$nom_cible</font>', '$nom_grade_cible', NOW())";
						$mysqli->query($sql);
						
						// Recup infos cible
						$sql = "SELECT type_perso, xp_perso, pi_perso, pc_perso FROM perso WHERE id_perso='$idPerso_carte'";
						$res = $mysqli->query($sql);
						$t = $res->fetch_assoc();
						
						$type_perso_cible 	= $t["type_perso"];
						$xp_perso_cible		= $t["xp_perso"];
						$pi_perso_cible		= $t["pi_perso"];
						$pc_perso_cible		= $t["pc_perso"];
						
						// Chef
						if ($type_perso_cible == 1) {
							perte_etendard($mysqli, $idPerso_carte,$x_cible, $y_cible);
							// Quand un chef meurt, il perd 5% de ses XP,XPi et de ses PC
							// Calcul PI
							$pi_perdu 		= floor(($pi_perso_cible * 5) / 100);
							
							// Calcul PC
							$pc_perdu		= floor(($pc_perso_cible * 5) / 100);
							$pc_perso_fin	= $pc_perso_cible - $pc_perdu;
						}
						else {
							$pi_perdu 		= floor(($pi_perso_cible * 40) / 100);
							$pc_perso_fin = $pc_perso_cible;
						}
						
						// maj stats / XP / PI / PC de la cible
						$sql = "UPDATE perso SET xp_perso=xp_perso-$pi_perdu, pi_perso=pi_perso-$pi_perdu, pc_perso=$pc_perso_fin, nb_mort=nb_mort+1 WHERE id_perso='$idPerso_carte'";
						$mysqli->query($sql);
						
						// maj stats du perso
						$sql = "UPDATE perso SET nb_kill=nb_kill+1 WHERE id_perso=$id_perso";
						$mysqli->query($sql);
						
						// maj stats camp
						if($clan_cible != $clan){
							$sql = "UPDATE stats_camp_kill SET nb_kill=nb_kill+1 WHERE id_camp=$clan";
							$mysqli->query($sql);
						}
						
						// maj dernier tombé
						$sql = "INSERT INTO dernier_tombe (date_capture, id_perso_capture, camp_perso_capture, id_perso_captureur, camp_perso_captureur) VALUES (NOW(), '$idPerso_carte', $clan_cible, $id_perso, $clan)";
						$mysqli->query($sql);
					}
					else {
						// evenement perso capture
						$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>','<b>a tué</b>','$idPerso_carte','<font color=$couleur_clan_cible><b>$nom_cible</b></font>','',NOW(),'0')";
						$mysqli->query($sql);
						
						// maj cv
						$sql = "INSERT INTO `cv` (IDActeur_cv, nomActeur_cv, gradeActeur_cv, IDCible_cv, nomCible_cv, date_cv) VALUES ($id_perso,'<font color=$couleur_clan_perso>$nom_perso</font>', '$nom_grade_perso', '$idPerso_carte','<font color=$couleur_clan_cible>$nom_cible</font>',NOW())";
						$mysqli->query($sql);
						
						// maj stats du perso
						$sql = "UPDATE perso SET nb_pnj=nb_pnj+1 WHERE id_perso=$id_perso";
						$mysqli->query($sql);
					}
				}
				
				// MAJ xp/pi
				$sql = "UPDATE perso SET xp_perso = xp_perso + $gain_experience, pi_perso = pi_perso + $gain_experience, gain_xp_tour = gain_xp_tour + $gain_experience WHERE id_perso='$id_perso'";
				$mysqli->query($sql);
				
				// mise à jour des PC du chef
				$sql = "UPDATE perso SET pc_perso = pc_perso + $gain_pc WHERE id_perso='$id_perso_chef'";
				$mysqli->query($sql);
				
			} else {
				// Le perso rate sa cible
				$gain_bonus_pc = false;
				
				if ($touche >= 98) {
					// Echec critique !
					// Ajout d'un malus supplémentaire à l'attaquant
					$sql = "UPDATE perso SET bonus_perso = bonus_perso - 1 WHERE id_perso='$id_perso'";
				} else {
					// Ajout d'un malus à la cible
					$bonus_cible -= 1;
					
					if ($idPerso_carte < 50000) {
						$sql = "UPDATE perso SET bonus_perso = bonus_perso - 1 WHERE id_perso='$idPerso_carte'";
					}
					else {
						$sql = "UPDATE instance_pnj SET bonus_i = bonus_i - 1 WHERE idInstance_pnj='$idPerso_carte'";
					}
				}
				$mysqli->query($sql);
				
				// Insertion log attaque
				$message_log = $id_perso.' a raté sa charge sur '.$idPerso_carte;
				$type_action = "Attaque ".$id_arme_attaque;
				$sql = "INSERT INTO log (date_log, id_perso, type_action, id_arme, pourcentage, message_log) VALUES (NOW(), '$id_perso', '$type_action', '$id_arme_attaque', '$touche', '$message_log')";
				$mysqli->query($sql);
				
				// Gain de 2 XP si esquive attaque d'un perso d'un autre camp
				if ($idPerso_carte < 50000 && $clan_cible != $clan && !$max_xp_tour_atteint) {
					$sql = "UPDATE perso SET xp_perso = xp_perso + 2, pi_perso = pi_perso + 2, gain_xp_tour = gain_xp_tour + 2 WHERE id_perso='$idPerso_carte'";
					$mysqli->query($sql);
				}
				
				// evenement
				$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($idPerso_carte,'<font color=$couleur_clan_cible><b>$nom_cible</b></font>','a esquivé l\'attaque de','$id_perso','<font color=$couleur_clan_perso><b>$nom_perso</b></font>','( Précision : $touche / $precision_final ; Gain XP : 0 )',NOW(),'0')";
				$mysqli->query($sql);
			}
			
			if ($nb_attaque < 3) {
				$nb_attaque++;
			}
		}
		
		if ($gain_bonus_pc && $nb_attaque >= 2 && $id_perso_chef != $id_perso && $clan_cible != $clan) {
			// Toutes les attaques de charge sont passée - minimum 2 attaques - pas le chef => gain +1PC bonus
			$sql = "UPDATE perso SET pc_perso=pc_perso+1 WHERE id_perso='$id_perso_chef'";
			$mysqli->query($sql);
			
			// evenement gain bonus PC
			$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>','a réussi toutes ses charges ', NULL, NULL,'Bonus Gain PC : 1',NOW(),'0')";
			$mysqli->query($sql);
		}
	}
	else {
		// evenement charge perso déjà mort
		$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>','a chargé trop tard','$idPerso_carte','<font color=$couleur_clan_cible><b>$nom_cible</b></font>','était déjà capturée',NOW(),'0')";
		$mysqli->query($sql);
	}
}

/**
 * Fonction permettant d'effectuer une charge vers le haut
 * y + 1
 */
function charge_haut($mysqli, $id_perso, $nom_perso, $type_perso, $x_perso, $y_perso, $pa_perso, $pv_perso, $xp_perso, $image_perso, $clan, $grade_perso) {
		
	$couleur_clan_perso = couleur_clan($clan);
	$distance_charge_min = distance_min_charge_pm($type_perso) +1;
	
	for ($i = 1; $i <= 5; $i++) {
					
		$sql = "SELECT x_carte, y_carte, fond_carte, occupee_carte, idPerso_carte FROM carte WHERE x_carte = $x_perso AND y_carte = $y_perso + $i";
		$res = $mysqli->query($sql);
		$t_charge = $res->fetch_assoc();
		
		$x_carte 		= $t_charge['x_carte'];
		$y_carte		= $t_charge['y_carte'];
		$fond_carte 	= $t_charge['fond_carte'];
		$occupee_carte	= $t_charge['occupee_carte'];
		$idPerso_carte	= $t_charge['idPerso_carte'];
		
		if ($occupee_carte || ($fond_carte != '1.gif' && false === strpos($fond_carte, 'rail'))) {
			
			// Charge terminée
			
			if ($i < $distance_charge_min) {
				
				// Mise à jour position perso sur carte
				$nb_deplacements = $i - 1;
				$x_perso_final = $x_perso;
				$y_perso_final = $y_perso + $nb_deplacements;
				
				charge_incomplete($mysqli, $id_perso, $nom_perso, $image_perso, $couleur_clan_perso, $x_perso, $y_perso, $x_perso_final, $y_perso_final, $nb_deplacements);
				
				break;
				
			} else {
				
				// Mise a jour carte 
				$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_perso' AND y_carte='$y_perso'";
				$mysqli->query($sql);
					
				// Charge complète mais cible pas sur plaine => pas d'attaques
				if ($fond_carte != '1.gif' && false === strpos($fond_carte, 'rail')) {
					
					// Mise à jour position perso sur carte
					$nb_deplacements = $i - 1;
					$x_perso_final = $x_perso;
					$y_perso_final = $y_perso + $nb_deplacements;
					
					charge_cible_terrain_impraticable($mysqli, $id_perso, $nom_perso, $image_perso, $couleur_clan_perso, $x_perso_final, $y_perso_final, $nb_deplacements);
					
				} else if ($idPerso_carte >= 50000 && $idPerso_carte < 200000) {
					
					// Charge complète mais cible batiment => pas d'attaque et le perso perd 40PV
					
					// Mise à jour position perso sur carte
					$nb_deplacements = $i - 1;
					$x_perso_final = $x_perso;
					$y_perso_final = $y_perso + $nb_deplacements;
					
					charge_batiment($mysqli, $id_perso, $nom_perso, $image_perso, $pv_perso, $couleur_clan_perso, $x_perso_final, $y_perso_final, $nb_deplacements);
					
				} else {
					// Charge compléte et réussi sur un autre joueur ou PNJ sur plaine
					// On utilise tous les PA dispo pour faire les attaques
					// +30 degats première attaque, +20 seconde, etc...
					
					// Mise à jour position perso sur carte
					$nb_deplacements = $i - 1;
					$x_perso_final = $x_perso;
					$y_perso_final = $y_perso + $nb_deplacements;
					
					charge_bonne($mysqli, $id_perso, $nom_perso, $image_perso, $clan, $couleur_clan_perso, $grade_perso, $pa_perso, $xp_perso, $x_perso_final, $y_perso_final, $nb_deplacements, $idPerso_carte);					
				}
				
				break;
			}
		} else {
			if ($i == 5) {
				// On n'a rencontré aucune cible / obstacle 
				// Charge terminée sans effet à part le déplacement
				
				// Mise a jour carte 
				$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_perso' AND y_carte='$y_perso'";
				$mysqli->query($sql);
				
				// Mise à jour position perso sur carte
				$x_perso_final = $x_perso;
				$y_perso_final = $y_perso + 4;
				
				$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte='$id_perso', image_carte='$image_perso' WHERE x_carte = $x_perso_final AND y_carte = $y_perso_final";
				$mysqli->query($sql);
				
				// Mise à jour du perso
				$sql = "UPDATE perso SET pm_perso = pm_perso - 4, x_perso = $x_perso_final, y_perso = $y_perso_final WHERE id_perso='$id_perso'";
				$mysqli->query($sql);
				
				// evenement
				$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>',' a chargé face au vent... ',NULL,'','jusqu\'en $x_perso_final / $y_perso_final',NOW(),'0')";
				$mysqli->query($sql);
			}
		}
	}
}

/**
 * Fonction de charge vers la direction haut gauche
 * x - 1 et y + 1
 */
function charge_haut_gauche($mysqli, $id_perso, $nom_perso, $type_perso, $x_perso, $y_perso, $pa_perso, $pv_perso, $xp_perso, $image_perso, $clan, $grade_perso) {
		
	$couleur_clan_perso = couleur_clan($clan);
	$distance_charge_min = distance_min_charge_pm($type_perso) +1;
	
	for ($i = 1; $i <= 5; $i++) {
					
		$sql = "SELECT x_carte, y_carte, fond_carte, occupee_carte, idPerso_carte FROM carte WHERE x_carte = $x_perso - $i AND y_carte = $y_perso + $i";
		$res = $mysqli->query($sql);
		$t_charge = $res->fetch_assoc();
		
		$x_carte 		= $t_charge['x_carte'];
		$y_carte		= $t_charge['y_carte'];
		$fond_carte 	= $t_charge['fond_carte'];
		$occupee_carte	= $t_charge['occupee_carte'];
		$idPerso_carte	= $t_charge['idPerso_carte'];
		
		if ($occupee_carte || ($fond_carte != '1.gif' && false === strpos($fond_carte, 'rail'))) {
			
			// Charge terminée
			
			if ($i < $distance_charge_min) {
				
				// Mise à jour position perso sur carte
				$nb_deplacements = $i - 1;
				$x_perso_final = $x_perso - $nb_deplacements;
				$y_perso_final = $y_perso + $nb_deplacements;
				
				charge_incomplete($mysqli, $id_perso, $nom_perso, $image_perso, $couleur_clan_perso, $x_perso, $y_perso, $x_perso_final, $y_perso_final, $nb_deplacements);
				
				break;
				
			} else {
				
				// Mise a jour carte 
				$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_perso' AND y_carte='$y_perso'";
				$mysqli->query($sql);
					
				// Charge complète mais cible pas sur plaine => pas d'attaques
				if ($fond_carte != '1.gif' && false === strpos($fond_carte, 'rail')) {
					
					// Mise à jour position perso sur carte
					$nb_deplacements = $i - 1;
					$x_perso_final = $x_perso - $nb_deplacements;
					$y_perso_final = $y_perso + $nb_deplacements;
					
					charge_cible_terrain_impraticable($mysqli, $id_perso, $nom_perso, $image_perso, $couleur_clan_perso, $x_perso_final, $y_perso_final, $nb_deplacements);
					
				} else if ($idPerso_carte >= 50000 && $idPerso_carte < 200000) {
					
					// Charge complète mais cible batiment => pas d'attaque et le perso perd 40PV
					
					// Mise à jour position perso sur carte
					$nb_deplacements = $i - 1;
					$x_perso_final = $x_perso - $nb_deplacements;
					$y_perso_final = $y_perso + $nb_deplacements;
					
					charge_batiment($mysqli, $id_perso, $nom_perso, $image_perso, $pv_perso, $couleur_clan_perso, $x_perso_final, $y_perso_final, $nb_deplacements);
					
				} else {
					// Charge compléte et réussi sur un autre joueur ou PNJ sur plaine
					// On utilise tous les PA dispo pour faire les attaques
					// +30 degats première attaque, +20 seconde, etc...
					
					// Mise à jour position perso sur carte
					$nb_deplacements = $i - 1;
					$x_perso_final = $x_perso - $nb_deplacements;
					$y_perso_final = $y_perso + $nb_deplacements;
					
					charge_bonne($mysqli, $id_perso, $nom_perso, $image_perso, $clan, $couleur_clan_perso, $grade_perso, $pa_perso, $xp_perso, $x_perso_final, $y_perso_final, $nb_deplacements, $idPerso_carte);
					
				}
				
				break;
			}
		} else {
			if ($i == 5) {
				// On n'a rencontré aucune cible / obstacle 
				// Charge terminée sans effet à part le déplacement
				
				// Mise a jour carte 
				$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_perso' AND y_carte='$y_perso'";
				$mysqli->query($sql);
				
				// Mise à jour position perso sur carte
				$x_perso_final = $x_perso - 4;
				$y_perso_final = $y_perso + 4;
				$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte='$id_perso', image_carte='$image_perso' WHERE x_carte = $x_perso_final AND y_carte = $y_perso_final";
				$mysqli->query($sql);
				
				// Mise à jour du perso
				$sql = "UPDATE perso SET pm_perso = pm_perso - 4, x_perso = $x_perso_final, y_perso = $y_perso_final WHERE id_perso='$id_perso'";
				$mysqli->query($sql);
				
				// evenement
				$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>',' a chargé face au vent... ',NULL,'','jusqu\'en $x_perso_final / $y_perso_final',NOW(),'0')";
				$mysqli->query($sql);
			}
		}
	}
}

/**
 * Fonction de charge vers la direction gauche
 * x - 1
 */
function charge_gauche($mysqli, $id_perso, $nom_perso, $type_perso, $x_perso, $y_perso, $pa_perso, $pv_perso, $xp_perso, $image_perso, $clan, $grade_perso) {
		
	$couleur_clan_perso = couleur_clan($clan);
	$distance_charge_min = distance_min_charge_pm($type_perso) +1;
	
	for ($i = 1; $i <= 5; $i++) {
					
		$sql = "SELECT x_carte, y_carte, fond_carte, occupee_carte, idPerso_carte FROM carte WHERE x_carte = $x_perso - $i AND y_carte = $y_perso";
		$res = $mysqli->query($sql);
		$t_charge = $res->fetch_assoc();
		
		$x_carte 		= $t_charge['x_carte'];
		$y_carte		= $t_charge['y_carte'];
		$fond_carte 	= $t_charge['fond_carte'];
		$occupee_carte	= $t_charge['occupee_carte'];
		$idPerso_carte	= $t_charge['idPerso_carte'];
		
		if ($occupee_carte || ($fond_carte != '1.gif' && false === strpos($fond_carte, 'rail'))) {
			
			// Charge terminée
			
			if ($i < $distance_charge_min) {
				
				// Mise à jour position perso sur carte
				$nb_deplacements = $i - 1;
				$x_perso_final = $x_perso - $nb_deplacements;
				$y_perso_final = $y_perso;
				
				charge_incomplete($mysqli, $id_perso, $nom_perso, $image_perso, $couleur_clan_perso, $x_perso, $y_perso, $x_perso_final, $y_perso_final, $nb_deplacements);
				
				break;
				
			} else {
				
				// Mise a jour carte 
				$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_perso' AND y_carte='$y_perso'";
				$mysqli->query($sql);
					
				// Charge complète mais cible pas sur plaine => pas d'attaques
				if ($fond_carte != '1.gif' && false === strpos($fond_carte, 'rail')) {
					
					// Mise à jour position perso sur carte
					$nb_deplacements = $i - 1;
					$x_perso_final = $x_perso - $nb_deplacements;
					$y_perso_final = $y_perso;
					
					charge_cible_terrain_impraticable($mysqli, $id_perso, $nom_perso, $image_perso, $couleur_clan_perso, $x_perso_final, $y_perso_final, $nb_deplacements);
					
				} else if ($idPerso_carte >= 50000 && $idPerso_carte < 200000) {
					
					// Charge complète mais cible batiment => pas d'attaque et le perso perd 40PV
					
					// Mise à jour position perso sur carte
					$nb_deplacements = $i - 1;
					$x_perso_final = $x_perso - $nb_deplacements;
					$y_perso_final = $y_perso;
					
					charge_batiment($mysqli, $id_perso, $nom_perso, $image_perso, $pv_perso, $couleur_clan_perso, $x_perso_final, $y_perso_final, $nb_deplacements);
					
				} else {
					// Charge compléte et réussi sur un autre joueur ou PNJ sur plaine
					// On utilise tous les PA dispo pour faire les attaques
					// +30 degats première attaque, +20 seconde, etc...
					
					// Mise à jour position perso sur carte
					$nb_deplacements = $i - 1;
					$x_perso_final = $x_perso - $nb_deplacements;
					$y_perso_final = $y_perso;
					
					charge_bonne($mysqli, $id_perso, $nom_perso, $image_perso, $clan, $couleur_clan_perso, $grade_perso, $pa_perso, $xp_perso, $x_perso_final, $y_perso_final, $nb_deplacements, $idPerso_carte);
					
				}
				
				break;
			}
		} else {
			if ($i == 5) {
				// On n'a rencontré aucune cible / obstacle 
				// Charge terminée sans effet à part le déplacement
				
				// Mise a jour carte 
				$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_perso' AND y_carte='$y_perso'";
				$mysqli->query($sql);
				
				// Mise à jour position perso sur carte
				$x_perso_final = $x_perso - 4;
				$y_perso_final = $y_perso;
				$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte='$id_perso', image_carte='$image_perso' WHERE x_carte = $x_perso_final AND y_carte = $y_perso_final";
				$mysqli->query($sql);
				
				// Mise à jour du perso
				$sql = "UPDATE perso SET pm_perso = pm_perso - 4, x_perso = $x_perso_final, y_perso = $y_perso_final WHERE id_perso='$id_perso'";
				$mysqli->query($sql);
				
				// evenement
				$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>',' a chargé face au vent... ',NULL,'','jusqu\'en $x_perso_final / $y_perso_final',NOW(),'0')";
				$mysqli->query($sql);
			}
		}
	}
}

/**
 * Fonction de charge vers la direction bas gauche
 * x - 1 et y - 1
 */
function charge_bas_gauche($mysqli, $id_perso, $nom_perso, $type_perso, $x_perso, $y_perso, $pa_perso, $pv_perso, $xp_perso, $image_perso, $clan, $grade_perso) {
		
	$couleur_clan_perso = couleur_clan($clan);
	$distance_charge_min = distance_min_charge_pm($type_perso) +1;
	
	for ($i = 1; $i <= 5; $i++) {
					
		$sql = "SELECT x_carte, y_carte, fond_carte, occupee_carte, idPerso_carte FROM carte WHERE x_carte = $x_perso - $i AND y_carte = $y_perso - $i";
		$res = $mysqli->query($sql);
		$t_charge = $res->fetch_assoc();
		
		$x_carte 		= $t_charge['x_carte'];
		$y_carte		= $t_charge['y_carte'];
		$fond_carte 	= $t_charge['fond_carte'];
		$occupee_carte	= $t_charge['occupee_carte'];
		$idPerso_carte	= $t_charge['idPerso_carte'];
		
		if ($occupee_carte || ($fond_carte != '1.gif' && false === strpos($fond_carte, 'rail'))) {
			
			// Charge terminée
			
			if ($i < $distance_charge_min) {
				
				// Mise à jour position perso sur carte
				$nb_deplacements = $i - 1;
				$x_perso_final = $x_perso - $nb_deplacements;
				$y_perso_final = $y_perso - $nb_deplacements;
				
				charge_incomplete($mysqli, $id_perso, $nom_perso, $image_perso, $couleur_clan_perso, $x_perso, $y_perso, $x_perso_final, $y_perso_final, $nb_deplacements);
				
				break;
				
			} else {
				
				// Mise a jour carte 
				$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_perso' AND y_carte='$y_perso'";
				$mysqli->query($sql);
					
				// Charge complète mais cible pas sur plaine => pas d'attaques
				if ($fond_carte != '1.gif' && false === strpos($fond_carte, 'rail')) {
					
					// Mise à jour position perso sur carte
					$nb_deplacements = $i - 1;
					$x_perso_final = $x_perso - $nb_deplacements;
					$y_perso_final = $y_perso - $nb_deplacements;
					
					charge_cible_terrain_impraticable($mysqli, $id_perso, $nom_perso, $image_perso, $couleur_clan_perso, $x_perso_final, $y_perso_final, $nb_deplacements);
					
				} else if ($idPerso_carte >= 50000 && $idPerso_carte < 200000) {
					
					// Charge complète mais cible batiment => pas d'attaque et le perso perd 40PV
					
					// Mise à jour position perso sur carte
					$nb_deplacements = $i - 1;
					$x_perso_final = $x_perso - $nb_deplacements;
					$y_perso_final = $y_perso - $nb_deplacements;
					
					charge_batiment($mysqli, $id_perso, $nom_perso, $image_perso, $pv_perso, $couleur_clan_perso, $x_perso_final, $y_perso_final, $nb_deplacements);
					
				} else {
					// Charge compléte et réussi sur un autre joueur ou PNJ sur plaine
					// On utilise tous les PA dispo pour faire les attaques
					// +30 degats première attaque, +20 seconde, etc...
					
					// Mise à jour position perso sur carte
					$nb_deplacements = $i - 1;
					$x_perso_final = $x_perso - $nb_deplacements;
					$y_perso_final = $y_perso - $nb_deplacements;
					
					charge_bonne($mysqli, $id_perso, $nom_perso, $image_perso, $clan, $couleur_clan_perso, $grade_perso, $pa_perso, $xp_perso, $x_perso_final, $y_perso_final, $nb_deplacements, $idPerso_carte);
				}
				
				break;
			}
		} else {
			if ($i == 5) {
				// On n'a rencontré aucune cible / obstacle 
				// Charge terminée sans effet à part le déplacement
				
				// Mise a jour carte 
				$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_perso' AND y_carte='$y_perso'";
				$mysqli->query($sql);
				
				// Mise à jour position perso sur carte
				$x_perso_final = $x_perso - 4;
				$y_perso_final = $y_perso - 4;
				$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte='$id_perso', image_carte='$image_perso' WHERE x_carte = $x_perso_final AND y_carte = $y_perso_final";
				$mysqli->query($sql);
				
				// Mise à jour du perso
				$sql = "UPDATE perso SET pm_perso = pm_perso - 4, x_perso = $x_perso_final, y_perso = $y_perso_final WHERE id_perso='$id_perso'";
				$mysqli->query($sql);
				
				// evenement
				$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>',' a chargé face au vent... ',NULL,'','jusqu\'en $x_perso_final / $y_perso_final',NOW(),'0')";
				$mysqli->query($sql);
			}
		}
	}
}

/**
 * Fonction de charge vers la direction bas
 * y - 1 
 */
function charge_bas($mysqli, $id_perso, $nom_perso, $type_perso, $x_perso, $y_perso, $pa_perso, $pv_perso, $xp_perso, $image_perso, $clan, $grade_perso) {
		
	$couleur_clan_perso = couleur_clan($clan);
	$distance_charge_min = distance_min_charge_pm($type_perso) +1;
	
	for ($i = 1; $i <= 5; $i++) {
					
		$sql = "SELECT x_carte, y_carte, fond_carte, occupee_carte, idPerso_carte FROM carte WHERE x_carte = $x_perso AND y_carte = $y_perso - $i";
		$res = $mysqli->query($sql);
		$t_charge = $res->fetch_assoc();
		
		$x_carte 		= $t_charge['x_carte'];
		$y_carte		= $t_charge['y_carte'];
		$fond_carte 	= $t_charge['fond_carte'];
		$occupee_carte	= $t_charge['occupee_carte'];
		$idPerso_carte	= $t_charge['idPerso_carte'];
		
		if ($occupee_carte || ($fond_carte != '1.gif' && false === strpos($fond_carte, 'rail'))) {
			
			// Charge terminée
			
			if ($i < $distance_charge_min) {
				
				// Mise à jour position perso sur carte
				$nb_deplacements = $i - 1;
				$x_perso_final = $x_perso;
				$y_perso_final = $y_perso - $nb_deplacements;
				
				charge_incomplete($mysqli, $id_perso, $nom_perso, $image_perso, $couleur_clan_perso, $x_perso, $y_perso, $x_perso_final, $y_perso_final, $nb_deplacements);
				
				break;
				
			} else {
				
				// Mise a jour carte 
				$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_perso' AND y_carte='$y_perso'";
				$mysqli->query($sql);
					
				// Charge complète mais cible pas sur plaine => pas d'attaques
				if ($fond_carte != '1.gif' && false === strpos($fond_carte, 'rail')) {
					
					// Mise à jour position perso sur carte
					$nb_deplacements = $i - 1;
					$x_perso_final = $x_perso;
					$y_perso_final = $y_perso - $nb_deplacements;
					
					charge_cible_terrain_impraticable($mysqli, $id_perso, $nom_perso, $image_perso, $couleur_clan_perso, $x_perso_final, $y_perso_final, $nb_deplacements);
					
				} else if ($idPerso_carte >= 50000 && $idPerso_carte < 200000) {
					
					// Charge complète mais cible batiment => pas d'attaque et le perso perd 40PV
					
					// Mise à jour position perso sur carte
					$nb_deplacements = $i - 1;
					$x_perso_final = $x_perso;
					$y_perso_final = $y_perso - $nb_deplacements;
					
					charge_batiment($mysqli, $id_perso, $nom_perso, $image_perso, $pv_perso, $couleur_clan_perso, $x_perso_final, $y_perso_final, $nb_deplacements);
					
				} else {
					// Charge compléte et réussi sur un autre joueur ou PNJ sur plaine
					// On utilise tous les PA dispo pour faire les attaques
					// +30 degats première attaque, +20 seconde, etc...
					
					// Mise à jour position perso sur carte
					$nb_deplacements = $i - 1;
					$x_perso_final = $x_perso;
					$y_perso_final = $y_perso - $nb_deplacements;
					
					charge_bonne($mysqli, $id_perso, $nom_perso, $image_perso, $clan, $couleur_clan_perso, $grade_perso, $pa_perso, $xp_perso, $x_perso_final, $y_perso_final, $nb_deplacements, $idPerso_carte);
				}
				
				break;
			}
		} else {
			if ($i == 5) {
				// On n'a rencontré aucune cible / obstacle 
				// Charge terminée sans effet à part le déplacement
				
				// Mise a jour carte 
				$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_perso' AND y_carte='$y_perso'";
				$mysqli->query($sql);
				
				// Mise à jour position perso sur carte
				$x_perso_final = $x_perso;
				$y_perso_final = $y_perso - 4;
				$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte='$id_perso', image_carte='$image_perso' WHERE x_carte = $x_perso_final AND y_carte = $y_perso_final";
				$mysqli->query($sql);
				
				// Mise à jour du perso
				$sql = "UPDATE perso SET pm_perso = pm_perso - 4, x_perso = $x_perso_final, y_perso = $y_perso_final WHERE id_perso='$id_perso'";
				$mysqli->query($sql);
				
				// evenement
				$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>',' a chargé face au vent... ',NULL,'','jusqu\'en $x_perso_final / $y_perso_final',NOW(),'0')";
				$mysqli->query($sql);
			}
		}
	}
}

/**
 * Fonction de charge vers la direction bas droite
 * x + 1 et y - 1
 */
function charge_bas_droite($mysqli, $id_perso, $nom_perso, $type_perso, $x_perso, $y_perso, $pa_perso, $pv_perso, $xp_perso, $image_perso, $clan, $grade_perso) {
		
	$couleur_clan_perso = couleur_clan($clan);
	$distance_charge_min = distance_min_charge_pm($type_perso) +1;
	
	for ($i = 1; $i <= 5; $i++) {
					
		$sql = "SELECT x_carte, y_carte, fond_carte, occupee_carte, idPerso_carte FROM carte WHERE x_carte = $x_perso + $i AND y_carte = $y_perso - $i";
		$res = $mysqli->query($sql);
		$t_charge = $res->fetch_assoc();
		
		$x_carte 		= $t_charge['x_carte'];
		$y_carte		= $t_charge['y_carte'];
		$fond_carte 	= $t_charge['fond_carte'];
		$occupee_carte	= $t_charge['occupee_carte'];
		$idPerso_carte	= $t_charge['idPerso_carte'];
		
		if ($occupee_carte || ($fond_carte != '1.gif' && false === strpos($fond_carte, 'rail'))) {
			
			// Charge terminée
			
			if ($i < $distance_charge_min) {
				
				// Mise à jour position perso sur carte
				$nb_deplacements = $i - 1;
				$x_perso_final = $x_perso + $nb_deplacements;
				$y_perso_final = $y_perso - $nb_deplacements;
				
				charge_incomplete($mysqli, $id_perso, $nom_perso, $image_perso, $couleur_clan_perso, $x_perso, $y_perso, $x_perso_final, $y_perso_final, $nb_deplacements);
				
				break;
				
			} else {
				
				// Mise a jour carte 
				$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_perso' AND y_carte='$y_perso'";
				$mysqli->query($sql);
					
				// Charge complète mais cible pas sur plaine => pas d'attaques
				if ($fond_carte != '1.gif' && false === strpos($fond_carte, 'rail')) {
					
					// Mise à jour position perso sur carte
					$nb_deplacements = $i - 1;
					$x_perso_final = $x_perso + $nb_deplacements;
					$y_perso_final = $y_perso - $nb_deplacements;
					
					charge_cible_terrain_impraticable($mysqli, $id_perso, $nom_perso, $image_perso, $couleur_clan_perso, $x_perso_final, $y_perso_final, $nb_deplacements);
					
				} else if ($idPerso_carte >= 50000 && $idPerso_carte < 200000) {
					
					// Charge complète mais cible batiment => pas d'attaque et le perso perd 40PV
					
					// Mise à jour position perso sur carte
					$nb_deplacements = $i - 1;
					$x_perso_final = $x_perso + $nb_deplacements;
					$y_perso_final = $y_perso - $nb_deplacements;
					
					charge_batiment($mysqli, $id_perso, $nom_perso, $image_perso, $pv_perso, $couleur_clan_perso, $x_perso_final, $y_perso_final, $nb_deplacements);
					
				} else {
					// Charge compléte et réussi sur un autre joueur ou PNJ sur plaine
					// On utilise tous les PA dispo pour faire les attaques
					// +30 degats première attaque, +20 seconde, etc...
					
					// Mise à jour position perso sur carte
					$nb_deplacements = $i - 1;
					$x_perso_final = $x_perso + $nb_deplacements;
					$y_perso_final = $y_perso - $nb_deplacements;
					
					charge_bonne($mysqli, $id_perso, $nom_perso, $image_perso, $clan, $couleur_clan_perso, $grade_perso, $pa_perso, $xp_perso, $x_perso_final, $y_perso_final, $nb_deplacements, $idPerso_carte);
				}
				
				break;
			}
		} else {
			if ($i == 5) {
				// On n'a rencontré aucune cible / obstacle 
				// Charge terminée sans effet à part le déplacement
				
				// Mise a jour carte 
				$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_perso' AND y_carte='$y_perso'";
				$mysqli->query($sql);
				
				// Mise à jour position perso sur carte
				$x_perso_final = $x_perso + 4;
				$y_perso_final = $y_perso - 4;
				$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte='$id_perso', image_carte='$image_perso' WHERE x_carte = $x_perso_final AND y_carte = $y_perso_final";
				$mysqli->query($sql);
				
				// Mise à jour du perso
				$sql = "UPDATE perso SET pm_perso = pm_perso - 4, x_perso = $x_perso_final, y_perso = $y_perso_final WHERE id_perso='$id_perso'";
				$mysqli->query($sql);
				
				// evenement
				$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>',' a chargé face au vent... ',NULL,'','jusqu\'en $x_perso_final / $y_perso_final',NOW(),'0')";
				$mysqli->query($sql);
			}
		}
	}
}

/**
 * Fonction de charge vers la direction droite
 * x + 1
 */
function charge_droite($mysqli, $id_perso, $nom_perso, $type_perso, $x_perso, $y_perso, $pa_perso, $pv_perso, $xp_perso, $image_perso, $clan, $grade_perso) {
		
	$couleur_clan_perso = couleur_clan($clan);
	$distance_charge_min = distance_min_charge_pm($type_perso) +1;
	
	for ($i = 1; $i <= 5; $i++) {
					
		$sql = "SELECT x_carte, y_carte, fond_carte, occupee_carte, idPerso_carte FROM carte WHERE x_carte = $x_perso + $i AND y_carte = $y_perso";
		$res = $mysqli->query($sql);
		$t_charge = $res->fetch_assoc();
		
		$x_carte 		= $t_charge['x_carte'];
		$y_carte		= $t_charge['y_carte'];
		$fond_carte 	= $t_charge['fond_carte'];
		$occupee_carte	= $t_charge['occupee_carte'];
		$idPerso_carte	= $t_charge['idPerso_carte'];
		
		if ($occupee_carte || ($fond_carte != '1.gif' && false === strpos($fond_carte, 'rail'))) {
			
			// Charge terminée
			
			if ($i < $distance_charge_min) {
				
				// Mise à jour position perso sur carte
				$nb_deplacements = $i - 1;
				$x_perso_final = $x_perso + $nb_deplacements;
				$y_perso_final = $y_perso;
				
				charge_incomplete($mysqli, $id_perso, $nom_perso, $image_perso, $couleur_clan_perso, $x_perso, $y_perso, $x_perso_final, $y_perso_final, $nb_deplacements);
				
				break;
				
			} else {
				
				// Mise a jour carte 
				$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_perso' AND y_carte='$y_perso'";
				$mysqli->query($sql);
					
				// Charge complète mais cible pas sur plaine => pas d'attaques
				if ($fond_carte != '1.gif' && false === strpos($fond_carte, 'rail')) {
					
					// Mise à jour position perso sur carte
					$nb_deplacements = $i - 1;
					$x_perso_final = $x_perso + $nb_deplacements;
					$y_perso_final = $y_perso;
					
					charge_cible_terrain_impraticable($mysqli, $id_perso, $nom_perso, $image_perso, $couleur_clan_perso, $x_perso_final, $y_perso_final, $nb_deplacements);
					
				} else if ($idPerso_carte >= 50000 && $idPerso_carte < 200000) {
					
					// Charge complète mais cible batiment => pas d'attaque et le perso perd 40PV
					
					// Mise à jour position perso sur carte
					$nb_deplacements = $i - 1;
					$x_perso_final = $x_perso + $nb_deplacements;
					$y_perso_final = $y_perso;
					
					charge_batiment($mysqli, $id_perso, $nom_perso, $image_perso, $pv_perso, $couleur_clan_perso, $x_perso_final, $y_perso_final, $nb_deplacements);
					
				} else {
					// Charge compléte et réussi sur un autre joueur ou PNJ sur plaine
					// On utilise tous les PA dispo pour faire les attaques
					// +30 degats première attaque, +20 seconde, etc...
					
					// Mise à jour position perso sur carte
					$nb_deplacements = $i - 1;
					$x_perso_final = $x_perso + $nb_deplacements;
					$y_perso_final = $y_perso;
					
					charge_bonne($mysqli, $id_perso, $nom_perso, $image_perso, $clan, $couleur_clan_perso, $grade_perso, $pa_perso, $xp_perso, $x_perso_final, $y_perso_final, $nb_deplacements, $idPerso_carte);
				}
				
				break;
			}
		} else {
			if ($i == 5) {
				// On n'a rencontré aucune cible / obstacle 
				// Charge terminée sans effet à part le déplacement
				
				// Mise a jour carte 
				$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_perso' AND y_carte='$y_perso'";
				$mysqli->query($sql);
				
				// Mise à jour position perso sur carte
				$x_perso_final = $x_perso + 4;
				$y_perso_final = $y_perso;
				$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte='$id_perso', image_carte='$image_perso' WHERE x_carte = $x_perso_final AND y_carte = $y_perso_final";
				$mysqli->query($sql);
				
				// Mise à jour du perso
				$sql = "UPDATE perso SET pm_perso = pm_perso - 4, x_perso = $x_perso_final, y_perso = $y_perso_final WHERE id_perso='$id_perso'";
				$mysqli->query($sql);
				
				// evenement
				$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>',' a chargé face au vent... ',NULL,'','jusqu\'en $x_perso_final / $y_perso_final',NOW(),'0')";
				$mysqli->query($sql);
			}
		}
	}
}

/**
 * Fonction de charge vers la direction haut droite
 * x + 1 et y + 1
 */
function charge_haut_droite($mysqli, $id_perso, $nom_perso, $type_perso, $x_perso, $y_perso, $pa_perso, $pv_perso, $xp_perso, $image_perso, $clan, $grade_perso) {
		
	$couleur_clan_perso = couleur_clan($clan);
	$distance_charge_min = distance_min_charge_pm($type_perso) +1;
	
	for ($i = 1; $i <= 5; $i++) {
					
		$sql = "SELECT x_carte, y_carte, fond_carte, occupee_carte, idPerso_carte FROM carte WHERE x_carte = $x_perso + $i AND y_carte = $y_perso + $i";
		$res = $mysqli->query($sql);
		$t_charge = $res->fetch_assoc();
		
		$x_carte 		= $t_charge['x_carte'];
		$y_carte		= $t_charge['y_carte'];
		$fond_carte 	= $t_charge['fond_carte'];
		$occupee_carte	= $t_charge['occupee_carte'];
		$idPerso_carte	= $t_charge['idPerso_carte'];
		
		if ($occupee_carte || ($fond_carte != '1.gif' && false === strpos($fond_carte, 'rail'))) {
			
			// Charge terminée
			
			if ($i < $distance_charge_min) {
				
				// Mise à jour position perso sur carte
				$nb_deplacements = $i - 1;
				$x_perso_final = $x_perso + $nb_deplacements;
				$y_perso_final = $y_perso + $nb_deplacements;
				
				charge_incomplete($mysqli, $id_perso, $nom_perso, $image_perso, $couleur_clan_perso, $x_perso, $y_perso, $x_perso_final, $y_perso_final, $nb_deplacements);
				
				break;
				
			} else {
				
				// Mise a jour carte 
				$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_perso' AND y_carte='$y_perso'";
				$mysqli->query($sql);
					
				// Charge complète mais cible pas sur plaine => pas d'attaques
				if ($fond_carte != '1.gif' && false === strpos($fond_carte, 'rail')) {
					
					// Mise à jour position perso sur carte
					$nb_deplacements = $i - 1;
					$x_perso_final = $x_perso + $nb_deplacements;
					$y_perso_final = $y_perso + $nb_deplacements;
					
					charge_cible_terrain_impraticable($mysqli, $id_perso, $nom_perso, $image_perso, $couleur_clan_perso, $x_perso_final, $y_perso_final, $nb_deplacements);
					
				} else if ($idPerso_carte >= 50000 && $idPerso_carte < 200000) {
					
					// Charge complète mais cible batiment => pas d'attaque et le perso perd 40PV
					
					// Mise à jour position perso sur carte
					$nb_deplacements = $i - 1;
					$x_perso_final = $x_perso;
					$y_perso_final = $y_perso + $nb_deplacements;
					
					charge_batiment($mysqli, $id_perso, $nom_perso, $image_perso, $pv_perso, $couleur_clan_perso, $x_perso_final, $y_perso_final, $nb_deplacements);
					
				} else {
					// Charge compléte et réussi sur un autre joueur ou PNJ sur plaine
					// On utilise tous les PA dispo pour faire les attaques
					// +30 degats première attaque, +20 seconde, etc...
					
					// Mise à jour position perso sur carte
					$nb_deplacements = $i - 1;
					$x_perso_final = $x_perso + $nb_deplacements;
					$y_perso_final = $y_perso + $nb_deplacements;
					
					charge_bonne($mysqli, $id_perso, $nom_perso, $image_perso, $clan, $couleur_clan_perso, $grade_perso, $pa_perso, $xp_perso, $x_perso_final, $y_perso_final, $nb_deplacements, $idPerso_carte);
				}
				
				break;
			}
		} else {
			if ($i == 5) {
				// On n'a rencontré aucune cible / obstacle 
				// Charge terminée sans effet à part le déplacement
				
				// Mise a jour carte 
				$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_perso' AND y_carte='$y_perso'";
				$mysqli->query($sql);
				
				// Mise à jour position perso sur carte
				$x_perso_final = $x_perso + 4;
				$y_perso_final = $y_perso + 4;
				$sql = "UPDATE carte SET occupee_carte='1', idPerso_carte='$id_perso', image_carte='$image_perso' WHERE x_carte = $x_perso_final AND y_carte = $y_perso_final";
				$mysqli->query($sql);
				
				// Mise à jour du perso
				$sql = "UPDATE perso SET pm_perso = pm_perso - 4, x_perso = $x_perso_final, y_perso = $y_perso_final WHERE id_perso='$id_perso'";
				$mysqli->query($sql);
				
				// evenement
				$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>',' a chargé face au vent... ',NULL,'','jusqu\'en $x_perso_final / $y_perso_final',NOW(),'0')";
				$mysqli->query($sql);
			}
		}
	}
}

?>
