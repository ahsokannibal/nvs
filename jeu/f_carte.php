<?php
// définition des abscisses et ordonnées limites de la carte
define ("X_MIN", 0);
define ("Y_MIN", 0);

// définition des abscisses et ordonnées limites de la carte
define ("I_PLAINE", "1.gif");
define ("I_COLLINE", "2.gif");
define ("I_MONTAGNE", "3.gif");
define ("I_DESERT", "4.gif");
define ("I_NEIGE", "5.gif");
define ("I_MARECAGE", "6.gif");
define ("I_FORET", "7.gif");
define ("I_EAU", "8.gif");
define ("I_EAU_P", "9.gif");

define("I_VILLE","ville1t.gif");
define("I_PONT_B","b5b.png");
define("I_PONT_R","b5r.png");
define("I_ROUTE_B","b4b.png");
define("I_ROUTE_R","b4r.png");
define("I_RAIL","rail.gif");
define("I_RAIL1","rail_1.gif");
define("I_RAIL2","rail_2.gif");
define("I_RAIL3","rail_3.gif");
define("I_RAIL4","rail_4.gif");
define("I_RAIL5","rail_5.gif");
define("I_RAIL7","rail_7.gif");
define("I_RAILP","railP.gif");

//vérifie si les coordonnées passées en argument sont bien sur la carte
function in_map($x, $y, $X_MAX, $Y_MAX)
{
	return $x >= X_MIN && $y >= Y_MIN && $x <= $X_MAX && $y <= $Y_MAX;
}

//vérifie si les coordonnées passées en argument sont bien sur la carte
function in_map_arene($x, $y)
{
	return $x >= X_MIN && $y >= Y_MIN && $x <= X_MAXA && $y <= Y_MAXA;
}

//vérifie si le perso à suffisament de pm pour se deplacer
function reste_pm($pm)
{
	return $pm > 0;
}

//vérifie si le fond passé en argument est de l'eau profonde
function is_eau_p($fond)
{
	return $fond == I_EAU_P;
}

//vérifie si l'image de la carte occupée (passée en argument) est une ville
function is_ville($image)
{
	return $image == I_VILLE;
}

//vérifie si l'image de la carte occupée (passée en argument) est un pont
function is_pont($image)
{
	return $image == I_PONT;
}

function verif_position_libre($mysqli, $x, $y, $X_MAX, $Y_MAX) {
	
	$verif = true;
	
	if (in_map($x, $y, $X_MAX, $Y_MAX)) {
		
		$sql = "SELECT occupee_carte FROM carte WHERE x_carte='".$x."' AND y_carte='".$y."' AND fond_carte != '9.gif'";
		$res = $mysqli->query($sql);
		$num = $res->num_rows;
		
		if ($num == 0) {
			$verif = false;
		}
		else {
			$t = $res->fetch_assoc();
			$occ = $t['occupee_carte'];
			
			if ($occ) {
				$verif = false;
			}
		}
	}
	else {
		$verif = false;
	}
	
	return $verif;
}

function verif_position_libre_pont($mysqli, $x, $y, $X_MAX, $Y_MAX) {
	
	$verif = true;
	
	if (in_map($x, $y, $X_MAX, $Y_MAX)) {
		
		$sql = "SELECT occupee_carte FROM carte WHERE x_carte='".$x."' AND y_carte='".$y."' AND (fond_carte = '8.gif' OR fond_carte = '9.gif')";
		$res = $mysqli->query($sql);
		$num = $res->num_rows;
		
		if ($num == 0) {
			$verif = false;
		}
		else {
			$t = $res->fetch_assoc();
			$occ = $t['occupee_carte'];
			
			if ($occ) {
				$verif = false;
			}
		}
	}
	else {
		$verif = false;
	}
	
	return $verif;
}

function verif_coord_in_perception($x, $y, $x_perso, $y_perso, $perception) {
	
	$verif = true;
	
	if ($x > $x_perso + $perception || $x < $x_perso - $perception 
			|| $y > $y_perso + $perception || $y < $y_perso - $perception) {
		$verif = false;
	}
	
	return $verif;
}

// Donne le nombre de pm que coute le deplacement suivant le terrain
function cout_pm($fond, $type_perso) 
{
	switch($fond) {
		case(I_FORET): return ($type_perso == 3 || $type_perso == 7) ? 1 : 2; break; 	//foret
		case(I_EAU): return 4; break; 		//eau
		case(I_MARECAGE): return 2; break; 	//marecage
		case(I_DESERT): return 1; break; 	//desert
		case(I_COLLINE): return 2; break; 	//colline
		case(I_MONTAGNE): return 4; break; 	// montagne
		case(I_ROUTE_B): return 1; break; 	// route bleu
		case(I_ROUTE_R): return 1; break; 	// route rouge
		case(I_PONT_B): return 1; break; 	// pont bleu
		case(I_PONT_R): return 1; break; 	// pont rouge
		case(I_RAIL): return 1; break; 		// ancien rail sur plaine
		case(I_RAIL1): return 1; break; 	// nouveau rail sur plaine
		case(I_RAIL2): return 1; break; 	// rail sur coline
		case(I_RAIL3): return 2; break; 	// rail sur montagne
		case(I_RAIL4): return 1; break; 	// rail sur desert
		case(I_RAIL5): return 1; break; 	// rail sur plaine enneigée
		case(I_RAIL7): return 1; break; 	// rail sur forêt
		case(I_RAILP): return 1; break; 	// rail sur pont
		default: return 1;
	}
}

/**
 * Donne les bonus en portée selon le terrain
 */
function get_bonus_portee($fond){
	switch($fond) {
		case(I_COLLINE): return 1; break; //colline
		case(I_MONTAGNE): return 2; break; //montagne
		default: return 0;
	}
}

// Donne les malus en visu suivant le terrain
function get_malus_visu($fond) 
{
	switch($fond) {
		case(I_FORET): return -2; break; //foret
		case(I_COLLINE): return 1; break; //colline
		case(I_MONTAGNE): return 2; break; //montagne
		default: return 0;
	}
}

// Donne les malus en recup suivant le terrain
function get_malus_recup($fond) 
{
	switch($fond) {
		case(I_MARECAGE): return -20; break; 
		case(I_DESERT): return -100; break;
		default: return 0;
	}
}

// Donne le nom du terrain
function get_nom_terrain($fond) {
	switch($fond) {
		case(I_PLAINE): return "Plaine"; break;
		case(I_FORET): return "Forêt"; break;
		case(I_EAU): return "Eau"; break;
		case(I_EAU_P): return "Eau Profonde"; break;
		case(I_MARECAGE): return "Marécage"; break;
		case(I_DESERT): return "Désert"; break;
		case(I_NEIGE): return "Neige"; break;
		case(I_COLLINE): return "Colline"; break;
		case(I_MONTAGNE): return "Montagne"; break;
		case(I_ROUTE_B): return "Route"; break;
		case(I_ROUTE_R): return "Route"; break;
		case(I_PONT_B): return "Pont"; break;
		case(I_PONT_R): return "Pont"; break;
		case(I_RAIL): return "Rail"; break;
		case(I_RAIL1): return "Rail sur plaine"; break;
		case(I_RAIL2): return "Rail sur colline"; break;
		case(I_RAIL3): return "Rail sur montagne"; break;
		case(I_RAIL4): return "Rail sur desert"; break;
		case(I_RAIL5): return "Rail sur plaine enneigée"; break;
		case(I_RAIL7): return "Rail dans forêt"; break;
		case(I_RAILP): return "Rail sur pont"; break;
		default: return "Inconnu";
	}
}

function is_case_rail($fond) {
	switch($fond) {
		case(I_RAIL): return true; break;
		case(I_RAIL1): return true; break;
		case(I_RAIL2): return true; break;
		case(I_RAIL3): return true; break;
		case(I_RAIL4): return true; break;
		case(I_RAIL5): return true; break;
		case(I_RAIL7): return true; break;
		case(I_RAILP): return true; break;
		default: return false;
	}
}

function get_bonus_defense_instance_bat($mysqli, $id_perso) {
	
	$defense_bat = 0;
	
	if (in_bat($mysqli, $id_perso)) {
		
		$id_inst_bat = in_bat($mysqli, $id_perso);
		
		$sql = "SELECT id_batiment FROM instance_batiment WHERE id_instanceBat='$id_inst_bat'";
		$res = $mysqli->query($sql);
		$t = $res->fetch_assoc();
		
		$id_bat = $t['id_batiment'];
		
		$defense_bat = get_bonus_defense_batiment($id_bat);
		
	}
	
	return $defense_bat;
}

function get_bonus_defense_batiment($id_bat) {
	switch($id_bat) {
		case(2): return +10; break;
		case(5): return -10; break;
		case(7): return +15; break;
		case(8): return +25; break;
		case(9): return +25; break;
		case(11): return +25; break;
		case(12): return +25; break;
		default: return 0;
	}
}

function get_bonus_attaque_from_batiment($id_bat) {
	switch($id_bat) {
		case(7): return -25; break;
		case(11): return -25; break;
		case(12): return -30; break;
		default: return 0;
	}
}

function get_bonus_defense_terrain($fond, $porteeMax_arme_attaque) {
	
	if ($porteeMax_arme_attaque > 1) {
		// Attaque avec arme à distance 
		switch($fond) {
			case(I_FORET): return 20; break;
			case(I_EAU): return 10; break;
			case(I_DESERT): return -10; break;
			case(I_COLLINE): return 10; break;
			case(I_MONTAGNE): return 20; break;
			case(I_PONT_B): return -10; break;
			case(I_PONT_R): return -10; break;
			default: return 0;
		}
	}
	else {
		// Attaque avec arme au CaC 
		switch($fond) {
			case(I_MARECAGE): return -10; break;
			case(I_DESERT): return -10; break;
			case(I_EAU): return 10; break;
			case(I_COLLINE): return 10; break;
			case(I_MONTAGNE): return 20; break;
			case(I_PONT_B): return -10; break;
			case(I_PONT_R): return -10; break;
			default: return 0;
		}
	}
}

function get_bonus_recup_bat_perso($mysqli, $id_perso) {
	
	$bonus_recup_bat = 0;
	
	if (in_bat($mysqli, $id_perso)) {
		
		$id_inst_bat = in_bat($mysqli, $id_perso);
		
		$sql = "SELECT id_batiment FROM instance_batiment WHERE id_instanceBat='$id_inst_bat'";
		$res = $mysqli->query($sql);
		$t = $res->fetch_assoc();
		
		$id_bat = $t['id_batiment'];
		
		$bonus_recup_bat = get_bonus_recup_bat($id_bat);
	}
	
	return $bonus_recup_bat;	
}

function get_bonus_recup_terrain_perso($mysqli, $x_perso, $y_perso) {
	
	$bonus_recup_terrain = 0;
		
	$sql = "SELECT fond_carte FROM carte WHERE x_carte=$x_perso AND y_carte=$y_perso";
	$res = $mysqli->query($sql);
	$t = $res->fetch_assoc();
		
	$fond = $t['fond_carte'];
		
	$bonus_recup_terrain = get_malus_recup($fond);
	
	return $bonus_recup_terrain;
}

function get_bonus_recup_bat($id_bat) {
	switch($id_bat) {
		case(8): return 10; break;
		case(9): return 20; break;
		default: return 0;
	}
}

function isDirectionOK($direction) {
	return $direction == 1 || $direction == 2 || $direction == 3
		|| $direction == 4 || $direction == 5 || $direction == 6
		|| $direction == 7 || $direction == 8;
}

// fonction qui verifie si le perso est bourre ou non
function bourre($mysqli, $id_perso) {
	$sql = "SELECT bourre_perso FROM perso WHERE id_perso='$id_perso'";
	$res = $mysqli->query($sql);
	$t_bourre = $res->fetch_assoc();
	$bourre = $t_bourre["bourre_perso"];
	return $bourre;
}

// fonction qui virifie si le perso est endurant a l'alcool ou non
function endurance_alcool($mysqli, $id_perso){
	$sql = "SELECT id_perso FROM perso_as_competence WHERE id_competence='2' AND id_perso='$id_perso'";
	$res = $mysqli->query($sql);
	$num = $res->num_rows;
	return $num==1;
}

// fonction qui vérifie si on est pas trop chargé et retourne le malus de pm
function get_malus_charge($charge, $chargeMax){
	$chargeMax_reel = $chargeMax * 4;
	$malus = ceil($chargeMax_reel - $charge);
	if($malus > 0){
		$malus = 0;
	}
	return $malus;
}

//donne les cibles humaines potentielles d'une attaque en fonction de la portée de l'arme
function get_cibles($mysqli, $x_perso, $y_perso, $id_perso, $perc, $portee) {	
	if ($perc < $portee) $portee=$perc;
	$sql = "SELECT DISTINCT nom_perso, ID_perso FROM carte, perso 
			WHERE occupee_carte='1' 
			AND x_carte >= $x_perso - $portee 
			AND x_carte <= $x_perso + $portee 
			AND y_carte <= $y_perso + $portee 
			AND y_carte >= $y_perso - $portee 
			AND idPerso_carte!='$id_perso' 
			AND idPerso_carte=ID_perso 
			AND x_perso >= $x_perso - $portee 
			AND x_perso <= $x_perso + $portee 
			AND y_perso <= $y_perso + $portee 
			AND y_perso >= $y_perso - $portee 
			AND pv_perso>0 
			ORDER BY ID_perso";
	return $res = $mysqli->query($sql);
}

//donne les cibles pnj potentielles d'une attaque en fonction de la portée de l'arme
function get_cibles_pnj($mysqli, $x_perso, $y_perso, $perc, $portee) {	
	echo $portee;
	echo " ".$perc;
	if ($perc < $portee) $portee=$perc;
	$sql = "SELECT DISTINCT nom_pnj, id_instance_pnj 
			FROM carte, instance_pnj, pnj 
			WHERE occupee_carte='1' 
			AND x_carte >= $x_perso - $portee 
			AND x_carte <= $x_perso + $portee 
			AND y_carte <= $y_perso + $portee 
			AND y_carte >= $y_perso - $portee 
			AND pnj.id_pnj=instance_pnj.id_pnj 
			AND idPerso_carte=id_instance_pnj 
			AND x_pnj >= $x_perso - $portee 
			AND x_pnj <= $x_perso + $portee 
			AND y_pnj <= $y_perso + $portee 
			AND y_pnj >= $y_perso - $portee 
			AND pv_instance>0 
			ORDER BY id_instance_pnj";
	return $res = $mysqli->query($sql);
}

/**
 * Fonction qui donne les persos dans sa visu
 */
function get_persos_visu($mysqli, $x_perso, $y_perso, $perc, $id, $id_joueur_p)
{
	$sql = "SELECT DISTINCT perso.nom_perso, perso.id_perso, perso.idJoueur_perso, perso.chef
			FROM perso 
			WHERE x_perso >= $x_perso - $perc 
			AND x_perso <= $x_perso + $perc 
			AND y_perso >= $y_perso - $perc 
			AND y_perso <= $y_perso + $perc 
			AND idJoueur_perso!='$id_joueur_p'
			AND perso.est_gele='0'
			AND type_perso != 6
			AND pv_perso > 0
			ORDER BY perso.id_perso ASC";
	return $res = $mysqli->query($sql);
}

/**
 * Fonction qui donne les persos de son camp dans sa visu
 */
function get_persos_visu_camp($mysqli, $x_perso, $y_perso, $perc, $id, $camp, $id_joueur_p)
{
	$sql = "SELECT DISTINCT perso.nom_perso, perso.id_perso, perso.idJoueur_perso, perso.chef
			FROM perso 
			WHERE x_perso >= $x_perso - $perc 
			AND x_perso <= $x_perso + $perc 
			AND y_perso >= $y_perso - $perc 
			AND y_perso <= $y_perso + $perc 
			AND perso.idJoueur_perso!='$id_joueur_p'
			AND perso.clan = $camp
			AND perso.est_gele='0'
			AND type_perso != 6
			AND pv_perso > 0
			ORDER BY perso.id_perso ASC";
	return $res = $mysqli->query($sql);
}

function calcul_de($de)
{
	srand((double) microtime() * 1000000);
	$score = rand($de,$de*3);
	return $score;
}

function touche($pourcent)
{
	srand((double) microtime() * 1000000);
	$r = rand(0,100);
	return $r;
}

function gain_po_mort($thune_cible)
{
	return floor(30*($thune_cible/100));
}

function gain_xp_mort($xp_cible, $xp)
{
	if($xp_cible >= $xp)
		return min(20, round(($xp_cible-$xp+20)/10));
	else
		return 2;
}

function chance_objet($nb){
	srand((double) microtime() * 1000000);
	$r = rand(0,100);
	if ($r < $nb)
		return 1;
	else
		return 0;
}

// fonction qui verifie si le perso est à proximité du coffre
function prox_coffre($mysqli, $x, $y, $x_perso, $y_perso){
	
	if ( $x_perso >= $x - 1 && $x_perso <= $x + 1
			&& $y_perso >= $y - 1 && $y_perso <= $y + 1) {
		return true;
	}
	else {
		return false;
	}
}

// fonction qui retourne l'id de l'objet obtenu dans le coffre
function contenu_coffre($mysqli){
	
	$ok = 0;
	
	// récupération du nombre d'objets en base
	$sql = "SELECT id_objet FROM contenu_coffre";
	$res = $mysqli->query($sql);
	$nb_o = $res->num_row;
	
	srand((double) microtime() * 1000000);
	$id_o = rand(1,$nb_o);
	
	while (!$ok){
		// verification qu'il reste de l'objet xhoisi par le rand
		$sql = "SELECT nb_objet FROM contenu_coffre WHERE id_objet='$id_o'";
		$res = $mysqli->query($sql);
		$t = $res->fetch_assoc();
		$nb = $t["nb_objet"];
		
		if($nb)
			$ok = 1;
		else {
			srand((double) microtime() * 1000000);
			$id_o = rand(1,$nb_o);
		}
	}
	return $id_o;
}

/**
 * Fonction qui permet de determiner si un perso est sur un objet déposé ou drop à terre
 *
 */
function is_objet_a_terre($mysqli, $x_perso, $y_perso) {
	 
	$sql = "SELECT count(nb_objet) as nb_objets FROM objet_in_carte WHERE x_carte='$x_perso' AND y_carte='$y_perso'";
	$res = $mysqli->query($sql);
	$t = $res->fetch_assoc();
	
	$nb_obj = $t['nb_objets'];
	
	return $nb_obj > 0;
}

// fonction qui retourne un booleen : vrai si il y a un batiment a coté de ces coordonnées, faux sinon
function prox_bat($mysqli, $x, $y, $id_perso){
	
	$sql = "SELECT idPerso_carte FROM carte,perso WHERE x_carte >= $x - 1 AND x_carte <= $x + 1 AND y_carte >= $y - 1 AND y_carte <= $y + 1 AND idPerso_carte>50000";
	$res = $mysqli->query($sql);
	$nb = $res->fetch_row();
	return $nb != 0;
	
}

// fonction qui retourne un booleen : vrai si il y a un batiment a coté de ces coordonnées, faux sinon
function prox_bat_perso($mysqli, $id_perso, $id_bat){
	
	$sql = "SELECT idPerso_carte FROM carte, perso 
			WHERE x_carte >= x_perso - 1 AND x_carte <= x_perso + 1 AND y_carte >= y_perso - 1 AND y_carte <= y_perso + 1 
			AND idPerso_carte = $id_bat
			AND perso.id_perso = $id_perso";
	$res = $mysqli->query($sql);
	$nb = $res->fetch_row();
	return $nb != 0;
	
}

// fonction qui retourne un booleen : vrai si le batiment concerné est a coté de ces coordonnées, faux sinon
function prox_instance_bat($mysqli, $x, $y, $instance){
	
	if($instance >= 50000){
		
		$sql = "SELECT image_carte 
				FROM carte
				WHERE x_carte >= $x - 1 
				AND x_carte <= $x + 1 
				AND y_carte >= $y - 1 
				AND y_carte <= $y + 1 
				AND idPerso_carte='$instance'";
		$res = $mysqli->query($sql);
		$nb = $res->fetch_row();
		
		return $nb != 0;
	}
	else {
		return 0;
	}
	
}

// fonction qui recupere les infos sur les batiments a proximité
// on ne prend pas en compte les trains
function id_prox_bat($mysqli, $x, $y){
	
	$sql = "SELECT DISTINCT(id_instanceBat), nom_instance, id_batiment, pv_instance, pvMax_instance
			FROM carte,instance_batiment 
			WHERE x_carte >= $x - 1 
			AND x_carte <= $x + 1 
			AND y_carte >= $y - 1 
			AND y_carte <= $y + 1 
			AND id_batiment != 12
			AND idPerso_carte=id_instanceBat 
			ORDER BY id_instanceBat";
			
	return $res = $mysqli->query($sql);
}

// fonction qui verifie si le perso est dans un batiment ou non
function in_bat($mysqli, $id){
	$sql = "SELECT id_instanceBat FROM perso_in_batiment WHERE id_perso='$id'";
	$res = $mysqli->query($sql);
	$t = $res->fetch_assoc();
	$nb = $res->num_rows;
	
	return $nb ? $t['id_instanceBat'] : 0;
}

// fonction qui verifie si le perso est dans un train ou non
function in_train($mysqli, $id){
	$sql = "SELECT id_train FROM perso_in_train WHERE id_perso='$id'";
	$res = $mysqli->query($sql);
	$t = $res->fetch_assoc();
	$nb = $res->num_rows;
	
	return $nb ? $t['id_train'] : 0;
}

function is_train($mysqli, $id_i_bat) {
	$sql = "SELECT id_instanceBat FROM instance_batiment WHERE id_instanceBat='$id_i_bat' AND id_batiment='12'";
	$res = $mysqli->query($sql);
	
	return $res->num_rows;
}

// fonction qui verifie si le perso est dans un batiment precis ou non
function in_instance_bat($mysqli, $id_perso, $id_i_bat){
	$sql = "SELECT id_perso FROM perso_in_batiment WHERE id_perso='$id_perso' AND id_instanceBat='$id_i_bat'";
	$res = $mysqli->query($sql);
	$nb = $res->num_rows;
	
	return $nb != 0;
}

// fonction qui verifie si le perso est dans un train precis ou non
function in_instance_train($mysqli, $id_perso, $id_i_train){
	$sql = "SELECT id_perso FROM perso_in_train WHERE id_perso='$id_perso' AND id_train='$id_i_train'";
	$res = $mysqli->query($sql);
	$nb = $res->num_rows;
	
	return $nb != 0;
}

// fonction qui verifie si le batiment est de la même nation ou non
function nation_perso_bat($mysqli, $id_perso, $id_bat){
	
	// recuperation de la nation du perso
	$sql = "SELECT clan FROM perso WHERE id_perso='$id_perso'";
	$res = $mysqli->query($sql);
	$t_np = $res->fetch_assoc();
	$nation_perso = $t_np["clan"];
	
	// recuperation de la nation du batiment
	$sql = "SELECT camp_instance FROM instance_batiment WHERE id_instanceBat='$id_bat'";
	$res = $mysqli->query($sql);
	$t_nb = $res->fetch_assoc();
	$nation_bat = $t_nb["camp_instance"];
	
	return $nation_perso==$nation_bat;
}

// fonction qui verifie si le batiment est vide ou non
function batiment_vide($mysqli, $id_bat){
	$sql = "SELECT id_perso FROM perso_in_batiment WHERE id_instanceBat='$id_bat'";
	$res = $mysqli->query($sql);
	$num = $res->num_rows;
	
	return $num == 0;
}

// fonction qui verifie si le batiment est vide ou non
function batiment_pv_capturable($mysqli, $id_bat){
	$sql = "SELECT pv_instance, pvMax_instance FROM instance_batiment WHERE id_instanceBat='$id_bat'";
	$res = $mysqli->query($sql);
	$t = $res->fetch_assoc();
	
	$pv_instance 	= $t['pv_instance'];
	$pvMax_instance	= $t['pvMax_instance'];
	
	// Calcul pourcentage pv du batiment 
	$pourc_pv_instance = $pvMax_instance == 0 ? 0 : ($pv_instance / $pvMax_instance) * 100;
	
	return $pourc_pv_instance <= 80;
}

// fonction qui verifie si l'instance du batiment existe
function existe_instance_bat($mysqli, $instance_bat){
	
	$sql = "SELECT id_batiment FROM instance_batiment WHERE id_instanceBat='$instance_bat'";
	$res = $mysqli->query($sql);
	$verif = $res->num_rows;

	return $verif != 0;
}

// fonction qui vérifie le type du batiment par rapport à son instance
function verif_bat_instance($mysqli, $id_bat, $id_instance){
	
	$sql = "SELECT id_batiment FROM instance_batiment WHERE id_instanceBat='$id_instance'";
	$res = $mysqli->query($sql);
	$nb = $res->num_rows;
	if($nb){
		$t = $res->fetch_assoc();
		$id_batiment = $t["id_batiment"];
		
		return $id_bat == $id_batiment;
	}
	else {
		return 0;
	}
	
}

// fonction qui calcul le nombre de case entre 2 positions
function calcul_nb_cases($x_depart, $y_depart, $x_arrivee, $y_arrivee){
	$x = abs($x_depart - $x_arrivee);
	$y = abs($y_depart - $y_arrivee);
	return max($x, $y);
}

/**
 * Fonction permettant de trouver le nombre d'ennemis à 15 cases du bâtiment
 */
function nb_ennemis_siege_batiment($mysqli, $x_ibat, $y_ibat, $camp) {
	
	$sql = "SELECT count(id_perso) as nb_ennemi FROM perso, carte 
			WHERE perso.id_perso = carte.idPerso_carte 
			AND x_carte <= $x_ibat + 15
			AND x_carte >= $x_ibat - 15
			AND y_carte <= $y_ibat + 15
			AND y_carte >= $y_ibat - 15
			AND perso.clan != '$camp'";
	$res = $mysqli->query($sql);
	$t_e = $res->fetch_assoc();
	
	return $t_e['nb_ennemi'];
	
}

// fonction qui récupère le batiment de rapatriement le plus proche d'un perso
function selection_bat_rapat($mysqli, $id_perso, $x_perso, $y_perso, $clan){
	
	$txt_log_respawn = "";
	
	// Verification si le perso a choisi un Hopital de respawn
	$sql = "SELECT id_instance_bat, x_instance, y_instance FROM perso_as_respawn, instance_batiment 
			WHERE perso_as_respawn.id_instance_bat = instance_batiment.id_instanceBat
			AND id_perso='$id_perso' AND id_bat='7'
			AND instance_batiment.camp_instance = '$clan'
			AND instance_batiment.pv_instance >= ((instance_batiment.pvMax_instance * 90) / 100)";
	$res = $mysqli->query($sql);
	$nb_h = $res->num_rows;
	
	$t = $res->fetch_assoc();
		
	$id_ibat7 	= $t['id_instance_bat'];
	$x_ibat		= $t['x_instance'];
	$y_ibat		= $t['y_instance'];
	
	// Verification si 10 persos ennemis à moins de 15 cases	
	$nb_ennemis_siege7 = nb_ennemis_siege_batiment($mysqli, $x_ibat, $y_ibat, $clan);
	
	if ($nb_h == 0) {
		$txt_log_respawn .= "Pas d\'hopital choisi en point de respawn ou PV Hôpital en état de siège.";
	}
	elseif ($nb_ennemis_siege7 >= 10) {
		$txt_log_respawn .= "Hopital ".$id_ibat7." en état de siège - 10 ennemis ou plus à moins de 15 cases.";
	}
	
	if ($nb_h > 0 && $nb_ennemis_siege7 < 10 
			&& $x_ibat <= $x_perso + 40 && $x_ibat >= $x_perso - 40
			&& $y_ibat <= $y_perso + 40 && $y_ibat >= $y_perso - 40) {
		
		$min_id_bat = $id_ibat7;
		
		$txt_log_respawn .= "Hopital ".$id_ibat7." choisi pour le respawn.";
	}
	else {
		
		if ($nb_h > 0 && $nb_ennemis_siege7 < 10) {
			$txt_log_respawn .= "Hopital ".$id_ibat7." (".$x_ibat."/".$y_ibat.") trop proche de la position de capture : ".$x_perso."/".$y_perso.".";
		}
		
		// Verification si le perso a choisi un Fortin de respawn
		$sql = "SELECT id_instance_bat, x_instance, y_instance FROM perso_as_respawn, instance_batiment, perso 
				WHERE perso_as_respawn.id_instance_bat = instance_batiment.id_instanceBat
				AND perso.id_perso = perso_as_respawn.id_perso
				AND perso_as_respawn.id_perso='$id_perso' AND id_bat='8'
				AND instance_batiment.camp_instance = '$clan'
				AND instance_batiment.pv_instance >= ((instance_batiment.pvMax_instance * 90) / 100)
				AND ((instance_batiment.x_instance <= (x_perso - 20) OR instance_batiment.x_instance >= (x_perso + 20))
				OR (instance_batiment.y_instance <= (y_perso - 20) OR instance_batiment.y_instance >= (y_perso + 20)))";
		$res = $mysqli->query($sql);
		$nb_f = $res->num_rows;
		
		$t = $res->fetch_assoc();
			
		$id_ibat8 	= $t['id_instance_bat'];
		$x_ibat		= $t['x_instance'];
		$y_ibat		= $t['y_instance'];
		
		// Verification si 10 persos ennemis à moins de 15 cases		
		$nb_ennemis_siege8 = nb_ennemis_siege_batiment($mysqli, $x_ibat, $y_ibat, $clan);
		
		if ($nb_f == 0) {
			$txt_log_respawn .= "Pas de Fortin choisi en point de respawn ou PV Fortin en état de siège ou Fortin trop proche du perso lors de sa capture.";
		}
		elseif ($nb_ennemis_siege8 >= 10) {
			$txt_log_respawn .= "Fortin ".$id_ibat8." en état de siège - 10 ennemis ou plus à moins de 15 cases.";
		}
		
		if ($nb_f > 0 && $nb_ennemis_siege8 < 10) {
			
			$min_id_bat = $id_ibat8;
			
			$txt_log_respawn .= "Fortin ".$id_ibat8." choisi pour le respawn.";
		}
		else {
			
			// Verification si le perso a choisi un Fort de respawn
			$sql = "SELECT id_instance_bat, x_instance, y_instance FROM perso_as_respawn, instance_batiment, perso 
					WHERE perso_as_respawn.id_instance_bat = instance_batiment.id_instanceBat
					AND perso.id_perso = perso_as_respawn.id_perso
					AND perso_as_respawn.id_perso='$id_perso' AND id_bat='9'
					AND instance_batiment.camp_instance = '$clan'
					AND instance_batiment.pv_instance >= ((instance_batiment.pvMax_instance * 90) / 100)
					AND ((instance_batiment.x_instance <= (x_perso - 20) OR instance_batiment.x_instance >= (x_perso + 20))
					OR (instance_batiment.y_instance <= (y_perso - 20) OR instance_batiment.y_instance >= (y_perso + 20)))";
			$res = $mysqli->query($sql);
			$nb_fort = $res->num_rows;
			
			$t = $res->fetch_assoc();
			
			$id_ibat9 	= $t['id_instance_bat'];
			$x_ibat		= $t['x_instance'];
			$y_ibat		= $t['y_instance'];
			
			// Verification si 10 persos ennemis à moins de 15 cases
			$nb_ennemis_siege9 = nb_ennemis_siege_batiment($mysqli, $x_ibat, $y_ibat, $clan);
			
			if ($nb_fort == 0) {
				$txt_log_respawn .= "Pas de Fort choisi en point de respawn ou PV Fort en état de siège ou Fort trop proche du perso lors de sa capture.";
			}
			elseif ($nb_ennemis_siege9 >= 10) {
				$txt_log_respawn .= "Fort ".$id_ibat9." en état de siège - 10 ennemis ou plus à moins de 15 cases.";
			}
			
			if ($nb_fort > 0 && $nb_ennemis_siege9 < 10) {
				
				$min_id_bat = $id_ibat9;
				
				$txt_log_respawn .= "Fort ".$id_ibat9." choisi pour le respawn.";
			}
			else {
				
				$txt_log_respawn .= "Choix du respawn par le système.";
				
				// Respawn choix par le système
	
				// init variables
				$min_distance = 1000;
				$min_id_bat = 0;

				// récupération des batiments de rappatriement du camp du perso 
				// Fort : 9 - Fortin : 8
				$sql_b = "SELECT * FROM instance_batiment WHERE camp_instance='$clan' AND (id_batiment='8' OR id_batiment='9')";
				$res_b = $mysqli->query($sql_b);
				
				while ($t_b = $res_b->fetch_assoc()){
					
					$x_bat 			= $t_b['x_instance'];
					$y_bat 			= $t_b['y_instance'];
					$id_ibat 		= $t_b['id_instanceBat'];
					$pv_bat			= $t_b['pv_instance'];
					$pvMax_bat		= $t_b['pvMax_instance'];
					$contenance_bat = $t_b['contenance_instance'];
					
					// calcul pourcentage pv bat 
					$pourcentage_pv_bat = ceil(($pv_bat * 100) / $pvMax_bat);
					
					// Verification si 10 persos ennemis à moins de 15 cases
					$nb_ennemis_siege = nb_ennemis_siege_batiment($mysqli, $x_bat, $y_bat, $clan);
					
					// Récupération du nombre de perso dans ce batiment
					$sql_n = "SELECT count(id_perso) as nb_perso_bat FROM perso_in_batiment WHERE id_instanceBat='$id_ibat'";
					$res_n = $mysqli->query($sql_n);
					$t_n = $res_n->fetch_assoc();
					$nb_perso_bat = $t_n['nb_perso_bat'];
					
					if ($contenance_bat > $nb_perso_bat && $pourcentage_pv_bat >= 90 && $nb_ennemis_siege < 10){
						// Le perso peut respawn dans ce batiment
						
						// Calcul de la distance entre le batiment et le perso
						$distance = calcul_nb_cases($x_perso, $y_perso, $x_bat, $y_bat);
						
						// Si la distance est moindre mais qu'on est toujours à plus de 20 cases, on selectionne ce batiment
						if ($distance < $min_distance && $distance > 20){
							$min_id_bat = $id_ibat;
						}
					}
				}
			}
		}
	}
	
	$sql = "INSERT INTO log_respawn (id_perso, date_respawn, texte_respawn) VALUES ('$id_perso', NOW(), '$txt_log_respawn')";
	$mysqli->query($sql);
	
	return $min_id_bat;
}

/**
 * selection du batiment de rapatriement le plus proche du départ de perm
 */
function selection_bat_retour_perm($mysqli, $id_perso, $x_perso, $y_perso, $clan) {
	
	// init variables
	$min_id_bat = 0;

	// récupération des batiments de retour de perm du camp du perso
	// Fort : 9 - Fortin : 8 - Gare : 11
	$sql_b = "SELECT * FROM instance_batiment WHERE camp_instance='$clan' AND (id_batiment='8' OR id_batiment='9' OR id_batiment='11')";
	$res_b = $mysqli->query($sql_b);
	
	while ($t_b = $res_b->fetch_assoc()){
		
		$x_bat 			= $t_b['x_instance'];
		$y_bat 			= $t_b['y_instance'];
		$id_bat 		= $t_b['id_instanceBat'];
		$pv_bat			= $t_b['pv_instance'];
		$pvMax_bat		= $t_b['pvMax_instance'];
		$contenance_bat = $t_b['contenance_instance'];
		
		// calcul pourcentage pv bat 
		$pourcentage_pv_bat = ceil(($pv_bat * 100) / $pvMax_bat);
		
		// Verification si 10 persos ennemis à moins de 15 cases
		$nb_ennemis_siege = nb_ennemis_siege_batiment($mysqli, $x_bat, $y_bat, $clan);
		
		// Récupération du nombre de perso dans ce batiment
		$sql_n = "SELECT count(id_perso) as nb_perso_bat FROM perso_in_batiment WHERE id_instanceBat='$id_bat'";
		$res_n = $mysqli->query($sql_n);
		$t_n = $res_n->fetch_assoc();
		$nb_perso_bat = $t_n['nb_perso_bat'];
		
		if ($contenance_bat > $nb_perso_bat && $pourcentage_pv_bat >= 90 && $nb_ennemis_siege < 10){
			// Le perso peut respawn dans ce batiment
			
			$min_id_bat = $id_bat;
		}
	}
	
	return $min_id_bat;
}

/**
 * Fonction permettant d'afficher les liens utiles lorsqu'un perso se retrouve à proximité d'un batiment
 * @return $mess_bat contenant les liens
 */
function afficher_lien_prox_bat($mysqli, $x_persoE, $y_persoE, $id_perso, $type_perso) {
	
	$new_mess_bat = "";
	
	if(prox_bat($mysqli, $x_persoE, $y_persoE, $id_perso) && !in_bat($mysqli, $id_perso)){
										
		// recuperation des id et noms des batiments dans lesquels le perso peut entrer
		$res_bat = id_prox_bat($mysqli, $x_persoE, $y_persoE); 
		
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
				if(batiment_vide($mysqli, $id_bat) && batiment_pv_capturable($mysqli, $id_bat) && $bat != 1 && $bat != 5 && $bat != 7 && $bat != 11 && $type_perso != '6' && $type_perso != '4' && $type_perso == 3){
					$new_mess_bat .= "<center><font color = blue>~~<a href=\"jouer.php?bat=$id_bat&bat2=$bat\" > capturer le batiment $nom_bat $nom_ibat [$id_bat]</a>~~</font></center>";
				}
			}
			else {
				if($bat != 1 && $bat != 5 && $bat != 10){
					// Si batiment tour de guet, seul les infanteries, soigneurs et chiens peuvent rentrer
					if (($bat == 2 && ($type_perso == 3 || $type_perso == 4 || $type_perso == 6)) || $bat != 2 ) {
						$new_mess_bat .= "<center><font color = blue>~~<a href=\"jouer.php?bat=$id_bat&bat2=$bat\" > entrer dans le batiment $nom_bat $nom_ibat [$id_bat]</a>~~</font></center>";
					}
				}
				
				// Les chiens ne peuvent pas réparer les batiments
				if ($pv_instance < $pvMax_instance && $type_perso != '6') {
					$new_mess_bat .= "<center><font color = blue>~~<a href=\"action.php?bat=$id_bat&reparer=ok\" > reparer $nom_bat $nom_ibat [$id_bat] (5 PA)</a>~~</font></center>";
				}
			}
			
			// Pont (neutres)
			// Les chiens ne peuvent pas réparer ni saboter les ponts
			if ($bat == 5 && $type_perso != '6') {
				$new_mess_bat .= "<center><font color = blue>~~<a href=\"action.php?bat=$id_bat&saboter=ok\" > saboter $nom_bat $nom_ibat [$id_bat] (10 PA)</a>~~</font></center>";
			}
		}
	}
	
	return $new_mess_bat;
}

/**
 * Fonction permettant de determiner si un type de perso peut ou non bousculer un type de perso cible
 * @return boolean
 */
function isTypePersoBousculable($type_perso, $type_perso_b) {
	
	// Cavalier
	if ($type_perso == 1 || $type_perso == 2 || $type_perso == 7) {
		// Peut bousculer infanterie et autres cavaliers
		if ($type_perso_b == 1 || $type_perso_b == 2 || $type_perso_b == 3 || $type_perso_b == 7) {
			return true;
		} else {
			return false;
		}
	}
	
	// Infanterie
	else if ($type_perso == 3) {
		// Peut bousculer infanterie, soigneur et artillerie
		if ($type_perso_b == 3 || $type_perso_b == 4 || $type_perso_b == 5) {
			return true;
		} else {
			return false;
		}
	}

	// Soigneur
	else if ($type_perso == 4) {
		// Peut bousculer infanterie et autres soigneur
		if ($type_perso_b == 3 || $type_perso_b == 4) {
			return true;
		} else {
			return false;
		}
	}
	
	// Artillerie
	else if ($type_perso == 5) {
		// Peut bousculer infanterie et soigneur
		if ($type_perso_b == 3 || $type_perso_b == 4) {
			return true;
		} else {
			return false;
		}
	}
	
	else {
		// les autres type de perso (toutou par exemple) ne peuvent pas bousculer
		return false;
	}
}

/**
 * Fonction permettant de récupérer les bonus / malus de perception du aux objets équipés
 * @return entier
 */
function getBonusObjet($mysqli, $id_perso) {
	
	$bonusPerception = 0;
	
	$sql = "SELECT bonusPerception_objet FROM perso_as_objet, objet 
			WHERE perso_as_objet.id_objet = objet.id_objet
			AND id_perso='$id_perso' 
			AND equip_objet='1'";
	$res = $mysqli->query($sql);
	
	while ($t = $res->fetch_assoc()) {
		
		$bonus_perc_objet = $t['bonusPerception_objet'];
		
		$bonusPerception += $bonus_perc_objet;
	}
	
	return $bonusPerception;
}

/**
 * Fonction permettant de récupérer les bonus / malus de defense du aux objets équipés
 */
function getBonusDefenseObjet($mysqli, $id_perso) {
	
	$bonusDefense = 0;
	
	$sql = "SELECT bonusDefense_objet  FROM perso_as_objet, objet 
			WHERE perso_as_objet.id_objet = objet.id_objet
			AND id_perso='$id_perso' 
			AND equip_objet='1'";
	$res = $mysqli->query($sql);
	
	while ($t = $res->fetch_assoc()) {
		
		$bonus_def_objet = $t['bonusDefense_objet'];
		
		$bonusDefense += $bonus_def_objet;
	}
	
	return $bonusDefense;
}

function get_bonus_defense_objet($mysqli, $id_perso) {
	
	$bonusDefenseObjet = 0;
	
	$sql = "SELECT SUM(bonusDefense_objet) as bonus_defense_objet FROM perso_as_objet, objet 
			WHERE perso_as_objet.id_objet = objet.id_objet
			AND id_perso='$id_perso' 
			AND equip_objet='1'";
	$res = $mysqli->query($sql);
	$t = $res->fetch_assoc();
	
	$bonusDefenseObjet += $t['bonus_defense_objet'];
	
	return $bonusDefenseObjet;
}

/**
 * Fonction permettant de récupérer les bonus / malus de precision au CaC du aux objets équipés
 */
function getBonusPrecisionCacObjet($mysqli, $id_perso) {
	
	$bonusPrecisionCac = 0;
	
	$sql = "SELECT bonusPrecisionCac_objet  FROM perso_as_objet, objet 
			WHERE perso_as_objet.id_objet = objet.id_objet
			AND id_perso='$id_perso' 
			AND equip_objet='1'";
	$res = $mysqli->query($sql);
	
	while ($t = $res->fetch_assoc()) {
		
		$bonus_precision_cac_objet = $t['bonusPrecisionCac_objet'];
		
		$bonusPrecisionCac += $bonus_precision_cac_objet;
	}
	
	return $bonusPrecisionCac;
}

/**
 * Fonction permettant de récupérer les bonus / malus de precision à distance du aux objets équipés
 */
function getBonusPrecisionDistObjet($mysqli, $id_perso) {
	
	$bonusPrecisionDist = 0;
	
	$sql = "SELECT bonusPrecisionDist_objet  FROM perso_as_objet, objet 
			WHERE perso_as_objet.id_objet = objet.id_objet
			AND id_perso='$id_perso' 
			AND equip_objet='1'";
	$res = $mysqli->query($sql);
	
	while ($t = $res->fetch_assoc()) {
		
		$bonus_precision_dist_objet = $t['bonusPrecisionDist_objet'];
		
		$bonusPrecisionDist += $bonus_precision_dist_objet;
	}
	
	return $bonusPrecisionDist;
}

/**
 * Fonction qui retourne le malus de PM du à la charge
 */
function getMalusCharge($charge_perso, $chargeMax_perso) {
	
	$malus = 0;
	
	if ($chargeMax_perso == 2) {
		
		if ($charge_perso >= 6) {
			$malus = 100;
		}
		else {
			$taille_malus = ceil($charge_perso - $chargeMax_perso);
			if ($taille_malus > 0) {
				$malus = -4 * $taille_malus;
			}
		}
	}
	else {
		if ($charge_perso >= 70) {
			$malus = 100;
		}
		else {
			$taille_malus = floor($charge_perso / 10);
			$malus = -$taille_malus;
		}
	}
	
	return $malus;
}

/**
 * Fonction permettant de connaitre le gain d'or selon le type de grouillot
 */
function gain_or_grouillot($type_grouillot) {
	
	if ($type_grouillot == 2) {
		$gain_or = 2;
	}
	else if ($type_grouillot == 4) {
		$gain_or = 2;
	}
	else if ($type_grouillot == 5) {
		$gain_or = 3;
	}
	else {
		$gain_or = 1;
	}
	
	return $gain_or;
}

/**
 * Fonction permettant de connaitre le gain min d'xp selon le batiment construit
 */
function min_gain_xp_construction($id_bat) {
	switch($id_bat) {
		case(1): return 1; break;
		case(2): return 1; break;
		case(5): return 3; break;
		case(7): return 3; break;
		case(8): return 8; break;
		case(9): return 10; break;
		case(11): return 5; break;
		default: return 0;
	}
}

/**
 * Fonction permettant de connaitre le gain max d'xp selon le batiment construit
 */
function max_gain_xp_construction($id_bat) {
	switch($id_bat) {
		case(1): return 2; break;
		case(2): return 3; break;
		case(5): return 5; break;
		case(7): return 5; break;
		case(8): return 10; break;
		case(9): return 15; break;
		case(11): return 7; break;
		default: return 0;
	}
}

function gain_pc_construction($id_bat) {
	switch($id_bat) {
		case(5): return 1; break;
		default: return 0;
	}
}

/**
 * Fonction qui permet de récupérer l'image du type de perso en fonction de l'id du type et de l'id du camp
 */
function get_image_type_perso($type_p, $camp_perso) {
	
	if ($camp_perso == 1) {
		$nom_camp_perso = "nord";
	}
	elseif($camp_perso == 2) {
		$nom_camp_perso = "sud";
	}
	elseif($camp_perso == 2) {
		$nom_camp_perso = "indien";
	}
	else {
		$nom_camp_perso = "outlaw";
	}
	
	$im_type_perso = "";
	
	if ($type_p == 1) {
		$im_type_perso = "cavalerie_".$nom_camp_perso.".gif";
	}
	elseif ($type_p == 2) {
		$im_type_perso = "cavalerie_".$nom_camp_perso.".gif";
	}
	elseif ($type_p == 3) {
		$im_type_perso = "infanterie_".$nom_camp_perso.".gif";
	}
	elseif ($type_p == 4) {
		$im_type_perso = "soigneur_".$nom_camp_perso.".gif";
	}
	elseif ($type_p == 5) {
		$im_type_perso = "artillerie_".$nom_camp_perso.".gif";
	}
	elseif ($type_p == 6) {
		$im_type_perso = "toutou_".$nom_camp_perso.".gif";
	}
	
	return $im_type_perso;
}

/**
 * Fonction qui récupère le fond_carte d'un perso sur base de son id
 */
function get_fond_carte_perso($mysqli,$carte, $id_perso){
	$sql = "SELECT fond_carte FROM $carte 
	WHERE idPerso_carte = $id_perso";

	$res = $mysqli->query($sql);
	$t = $res->fetch_assoc();

	//On teste que la requête retourne bien un résultat
	if($res->num_rows == 1){
		return $t["fond_carte"];
	}
	return "";
	
}
?>
