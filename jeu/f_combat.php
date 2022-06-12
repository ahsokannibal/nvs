<?php

require_once("config.php");

/**
  * Fonction de combat d'un pnj
  * @param $precision_pnj	: La precision d'attaque du pnj
  * @param $bonus_pj		: Les bonus / malus de defense du pj
  * @return bool			: Si le pnj a touche le pj
  */
function combat_pnj($precision_pnj, $bonus_pj){
	
	$touche = mt_rand(0,100);
	$precision_final = $precision_pnj - $bonus_pj;
	
	return ($precision_final <= $precision_pnj);
}

/**
  * Fonction qui verifie si le perso a deja tue le type de pnj passe en parametre et qui en retourne le nombre
  * @param $id_perso	: L'identifiant du perso
  * @param $id_pnj	: L'identifiant du pnj
  * @return int		: Le nombre de pnj du type demande deja tue
  */
function is_deja_tue_pnj($mysqli, $id_perso, $id_pnj){
	
	$sql = "SELECT nb_pnj FROM perso_as_killpnj WHERE id_perso='$id_perso' and id_pnj='$id_pnj'";
	$res = $mysqli->query($sql);
	$t_verif_t = $res->fetch_assoc();
	
	return $t_verif_t["nb_pnj"];
}

/**
 * Fonction qui recupere la couleur associee au clan du perso
 * @param $clan_perso	: L'identifiant du clan du perso
 * @return String		: La couleur associee au clan du perso
 */
function couleur_clan($clan_perso){
	if($clan_perso == '1'){
		$couleur_clan_perso = 'blue';
	}
	else if($clan_perso == '2'){
		$couleur_clan_perso = 'red';
	}
	else if($clan_perso == '3'){
		$couleur_clan_perso = 'green';
	}
	else {
		$couleur_clan_perso = 'black';
	}
	return $couleur_clan_perso;
}

/**
 * Fonction qui recupere l'identifiant de l'arme equipee sur la main principale perso
 * @param $id_perso	: L'identifiant du perso
 * @return int		: L'identifiant de l'arme equipee, 0 si pas d'arme equipee
 */
function id_arme_equipee($mysqli, $id_perso){

	$sql_arme_equipee = "SELECT perso_as_arme.id_arme FROM perso_as_arme WHERE id_perso='$id_perso' AND est_portee='1'";
	$res_a = $mysqli->query($sql_arme_equipee);
	$num_a = $res_a->num_rows;
	
	if($num_a){
		$t_a = $res_a->fetch_assoc();
		return $t_a["id_arme"];
	}
	
	return 0;
	
}

/**
 * Fonction permettant de retourner l'identifiant d'une arme non équipée sur le perso
 */
function id_arme_non_equipee($mysqli, $id_perso) {
	
	$sql = "SELECT perso_as_arme.id_arme FROM perso_as_arme WHERE id_perso='$id_perso' AND est_portee='0'";
	$res = $mysqli->query($sql);
	$num = $res->num_rows;
	
	if($num){
		$t = $res->fetch_assoc();
		return $t["id_arme"];
	}
	
	return 0;
}

/**
 * Function permettant de vérifier si un perso a une lunette de visée équipée
 */
function possede_lunette_visee($mysqli, $id_perso) {
	
	$sql = "SELECT * FROM perso_as_objet WHERE id_perso='$id_perso' AND id_objet='7' AND equip_objet='1'";
	$res = $mysqli->query($sql);
	
	return $res->num_rows;
}

/**
  * Fonction qui retourne la distance du perso à une cible.
  * @param $id_perso	: L'identifiant du perso qui attaque
  * @param $id_cible	: L'identifiant du pj ou pnj cible (attaque)
  * @return int		: distance
  */
function get_distance($mysqli, $id_perso, $id_cible){
	$sql = "SELECT x_carte,y_carte from carte WHERE idPerso_carte='$id_perso'";
	$res = $mysqli->query($sql);
	$t = $res->fetch_assoc();
	$x_perso = $t['x_carte'];
	$y_perso = $t['y_carte'];

	$sql = "SELECT x_carte,y_carte from carte WHERE idPerso_carte='$id_cible'";
	$res = $mysqli->query($sql);
	$t = $res->fetch_assoc();
	$x_cible = $t['x_carte'];
	$y_cible = $t['y_carte'];

	return max(abs($x_perso - $x_cible), abs($y_perso - $y_cible));
}

/**
  * Fonction qui verifie si un pnj ou un pj est bien a portee d'attaque sur la carte
  * @param $carte	: La carte sur laquelle se trouve le pj qui attaque
  * @param $id_perso	: L'identifiant du perso qui attaque
  * @param $id_cible	: L'identifiant du pj ou pnj cible (attaque)
  * @param $portee_min	: La portee minimale de l'attaquant
  * @param $portee_max: La portee maximale de l'attaquant
  * @param $per_perso	: La perception du perso qui attaque
  * @return bool		: Si le pj ou le pnj est bien a portee d'attaque
  */
function is_a_portee_attaque($mysqli, $carte, $id_perso, $id_cible, $portee_min, $portee_max, $per_perso){

	// Pas d'attaque au CaC depuis un batiment
	// Portée max ne peut pas être inférieur à portée min
	if(($portee_max == 1 && in_bat($mysqli, $id_perso)) || $portee_max < $portee_min){
		return 0;
	}

	// On réduit la portée max à la perception du perso
	if($per_perso < $portee_max){
		$portee_max = $per_perso;
	}

	// Requete qui recupere les persos a portee d'attaque
	$sql = "(
			SELECT idPerso_carte
			FROM $carte, perso 
			WHERE id_perso='$id_perso'
			AND ((x_carte>=x_perso+$portee_min AND x_carte<=x_perso+$portee_max AND y_carte>=y_perso-$portee_max AND y_carte<=y_perso+$portee_max)
					OR 	(x_carte>=x_perso-$portee_max AND x_carte<=x_perso-$portee_min AND y_carte>=y_perso-$portee_max AND y_carte<=y_perso+$portee_max)
					OR	(y_carte>=y_perso+$portee_min AND y_carte<=y_perso+$portee_max AND x_carte>=x_perso-$portee_max AND x_carte<=x_perso+$portee_max)
					OR	(y_carte>=y_perso-$portee_max AND y_carte<=y_perso-$portee_min AND x_carte>=x_perso-$portee_max AND x_carte<=x_perso+$portee_max)
				)
			AND idPerso_carte='$id_cible'
			)
			";
			
	// Si attaque à distance
	// => On peut attaquer les persos dans les train et les batiments
	if ($portee_max >= 2) {
		
		$sql .= "
			UNION (
				SELECT perso_in_train.id_perso
				FROM instance_batiment, perso_in_train, perso
				WHERE instance_batiment.id_instanceBat = perso_in_train.id_train
				AND perso.id_perso = '$id_perso'
				AND id_batiment='12' 
				AND ((x_instance>=x_perso+$portee_min AND x_instance<=x_perso+$portee_max AND y_instance>=y_perso-$portee_max AND y_instance<=y_perso+$portee_max)
					OR 	(x_instance>=x_perso-$portee_max AND x_instance<=x_perso-$portee_min AND y_instance>=y_perso-$portee_max AND y_instance<=y_perso+$portee_max)
					OR	(y_instance>=y_perso+$portee_min AND y_instance<=y_perso+$portee_max AND x_instance>=x_perso-$portee_max AND x_instance<=x_perso+$portee_max)
					OR	(y_instance>=y_perso-$portee_max AND y_instance<=y_perso-$portee_min AND x_instance>=x_perso-$portee_max AND x_instance<=x_perso+$portee_max)
				)
				AND perso_in_train.id_perso='$id_cible'
			)
			UNION (
				SELECT perso_in_batiment.id_perso
				FROM instance_batiment, perso_in_batiment, perso
				WHERE instance_batiment.id_instanceBat = perso_in_batiment.id_instanceBat
				AND perso.id_perso = '$id_perso'
				AND id_batiment!='12' 
				AND ((x_instance>=x_perso+$portee_min AND x_instance<=x_perso+$portee_max AND y_instance>=y_perso-$portee_max AND y_instance<=y_perso+$portee_max)
					OR 	(x_instance>=x_perso-$portee_max AND x_instance<=x_perso-$portee_min AND y_instance>=y_perso-$portee_max AND y_instance<=y_perso+$portee_max)
					OR	(y_instance>=y_perso+$portee_min AND y_instance<=y_perso+$portee_max AND x_instance>=x_perso-$portee_max AND x_instance<=x_perso+$portee_max)
					OR	(y_instance>=y_perso-$portee_max AND y_instance<=y_perso-$portee_min AND x_instance>=x_perso-$portee_max AND x_instance<=x_perso+$portee_max)
				)
				AND perso_in_batiment.id_perso='$id_cible'
			)";
	}
	
	$res = $mysqli->query($sql);
	
	return $res->num_rows;
}

/**
 *
 */
function resource_liste_cibles_a_portee_attaque($mysqli, $carte, $id_perso, $portee_min, $portee_max, $per_perso, $type_attaque) {
	
	// Pas d'attaque au CaC depuis un batiment
	// Portée max ne peut pas être inférieur à portée min
	if (($type_attaque == 'cac' && in_bat($mysqli, $id_perso)) || $portee_max < $portee_min) {
		$sql = "SELECT id_perso FROM perso WHERE 1=2";
	}
	else {
	
		// On réduit la portée max à la perception du perso
		if($per_perso < $portee_max){
			$portee_max = $per_perso;
		}

		// Requete qui recupere les cases a portee d'attaque
		$sql = "(
				SELECT DISTINCT(idPerso_carte)
				FROM $carte, perso 
				WHERE id_perso='$id_perso'
				AND occupee_carte = '1'
				AND ((x_carte>=x_perso+$portee_min AND x_carte<=x_perso+$portee_max AND y_carte>=y_perso-$portee_max AND y_carte<=y_perso+$portee_max)
						OR 	(x_carte>=x_perso-$portee_max AND x_carte<=x_perso-$portee_min AND y_carte>=y_perso-$portee_max AND y_carte<=y_perso+$portee_max)
						OR	(y_carte>=y_perso+$portee_min AND y_carte<=y_perso+$portee_max AND x_carte>=x_perso-$portee_max AND x_carte<=x_perso+$portee_max)
						OR	(y_carte>=y_perso-$portee_max AND y_carte<=y_perso-$portee_min AND x_carte>=x_perso-$portee_max AND x_carte<=x_perso+$portee_max)
					)
				)
				";
		
		// Si attaque à distance
		// => On peut attaquer les persos dans les train et les batiments
		if ($type_attaque == 'dist') {
			
			$sql .= "
				UNION (
					SELECT perso_in_train.id_perso
					FROM instance_batiment, perso_in_train, perso
					WHERE instance_batiment.id_instanceBat = perso_in_train.id_train
					AND perso.id_perso = '$id_perso'
					AND id_batiment='12' 
					AND ((x_instance>=x_perso+$portee_min AND x_instance<=x_perso+$portee_max AND y_instance>=y_perso-$portee_max AND y_instance<=y_perso+$portee_max)
						OR 	(x_instance>=x_perso-$portee_max AND x_instance<=x_perso-$portee_min AND y_instance>=y_perso-$portee_max AND y_instance<=y_perso+$portee_max)
						OR	(y_instance>=y_perso+$portee_min AND y_instance<=y_perso+$portee_max AND x_instance>=x_perso-$portee_max AND x_instance<=x_perso+$portee_max)
						OR	(y_instance>=y_perso-$portee_max AND y_instance<=y_perso-$portee_min AND x_instance>=x_perso-$portee_max AND x_instance<=x_perso+$portee_max)
					)
				)
				UNION (
					SELECT perso_in_batiment.id_perso
					FROM instance_batiment, perso_in_batiment, perso
					WHERE instance_batiment.id_instanceBat = perso_in_batiment.id_instanceBat
					AND perso.id_perso = '$id_perso'
					AND id_batiment!='12' AND id_batiment != '10'
					AND ((x_instance>=x_perso+$portee_min AND x_instance<=x_perso+$portee_max AND y_instance>=y_perso-$portee_max AND y_instance<=y_perso+$portee_max)
						OR 	(x_instance>=x_perso-$portee_max AND x_instance<=x_perso-$portee_min AND y_instance>=y_perso-$portee_max AND y_instance<=y_perso+$portee_max)
						OR	(y_instance>=y_perso+$portee_min AND y_instance<=y_perso+$portee_max AND x_instance>=x_perso-$portee_max AND x_instance<=x_perso+$portee_max)
						OR	(y_instance>=y_perso-$portee_max AND y_instance<=y_perso-$portee_min AND x_instance>=x_perso-$portee_max AND x_instance<=x_perso+$portee_max)
					)
				)";
		}
	}
			
	$res = $mysqli->query($sql);
	
	return $res;
}

/**
  * Fonction qui renvoie le gain d'xp lors d'une attaque en fonction des levels des deux protagonistes (attaquant et cible)
  * @param $clan_perso	: Le clan du perso
  * @param $clan_cible	: Le clan de la cible
  * @return int		: Le nombre d'xp gagne par l'attaquant
  */
function gain_xp($clan_perso, $clan_cible){

	if($clan_cible != $clan_perso){
		$gain_xp = 2;
	}
	else {
		$gain_xp = 1;
	}
	
	return $gain_xp;
}

/** 
  * Fonction qui verifie si un perso est chanceux et retourne le nombre de points de chance
  * @param $id_perso	: L'identifiant du personnage
  * @return Int		: Le nombre de points dans la competence chance, 0 si non chanceux
  */
function est_chanceux($mysqli, $id_perso){
	$sql = "SELECT nb_points FROM perso_as_competence WHERE id_perso='$id_perso' AND id_competence='31'";
	$res = $mysqli->query($sql);
	$t = $res->fetch_assoc();
	
	return $t['nb_points'];
}

/**
 * Fonction permettant de calculer le resultat d'un lancé de dès
 */
function calcul_des_attaque($nbDes, $valeurDes) {
	
	$total = 0;
	
	for ($i = 0; $i < $nbDes; $i++) {
		$resultat_des = mt_rand(1, $valeurDes);
		
		$total += $resultat_des;
	}
	
	return $total;
}

/**
 * Fonction permettant de calculer le gain de PC après une attaque sur un perso
 */
function calcul_gain_pc_attaque_perso($grade_perso, $grade_cible, $clan_perso, $clan_cible, $type_perso, $id_j_perso, $id_joueur_cible) {
	
	$gain_pc = 0;
	
	if ((( $grade_perso <= $grade_cible + 1 && $grade_cible != 101 && $grade_cible != 102 )
			|| $grade_perso == 1 || $grade_perso == 101 || $grade_perso == 102 
			|| (($grade_cible == 1 || $grade_cible == 101 || $grade_cible == 102) && $grade_perso == 2)) && ($clan_cible != $clan_perso || $type_perso == 4)) {
		$gain_pc = 1;
	}
	
	return $gain_pc;
}

/**
 * Fonction permettant de verifier et de faire le passage de grade des grouillots
 */
function passage_grade_grouillot($mysqli, $id, $grade_perso, $xp_perso, $gain_xp) {
	
	if ($grade_perso == 1) {
													
		if ($xp_perso + $gain_xp >= 500) {
			// On le passage 1ere classe
			$sql = "UPDATE perso_as_grade SET id_grade='101' WHERE id_perso='$id'";
			$mysqli->query($sql);
			
			echo "<br /><b>Vous êtes passé au grade de Grouillot 1ere classe</b><br />";
		}
	}
	
	if ($grade_perso == 101) {
		
		if ($xp_perso + $gain_xp >= 1500) {
			// On le passe élite
			$sql = "UPDATE perso_as_grade SET id_grade='102' WHERE id_perso='$id'";
			$mysqli->query($sql);
			
			echo "<br /><b>Vous êtes passé au grade de Grouillot d'élite</b><br />";
		}
	}
	
}

/**
 * Fonction permettant de gérer la loi anti-zerk
 */
function gestion_anti_zerk($mysqli, $id_perso) {
	
	$verif_anti_zerk = 1;
						
	// Verification si enregistrement d'attaque existant
	$sql = "SELECT *, TIME_TO_SEC(TIMEDIFF(NOW(), date_derniere_attaque)) as diff FROM anti_zerk WHERE id_perso='$id_perso'";
	$res = $mysqli->query($sql);
	$nb_enr_anti_zerk = $res->num_rows;
	
	// recupération dla perso
	$sql_p = "SELECT DLA_perso FROM perso WHERE id_perso='$id_perso'";
	$res_p = $mysqli->query($sql_p);
	$t_p = $res_p->fetch_assoc();
	
	$dla_perso = $t_p['DLA_perso'];

	if ($nb_enr_anti_zerk > 0) {
		
		$t_zerk = $res->fetch_assoc();
		
		$date_derniere_attaque 		= $t_zerk['date_derniere_attaque'];
		$date_nouveau_tour			= $t_zerk['date_nouveau_tour'];
		$diff				= $t_zerk['diff'];
		
		if ($dla_perso != $date_nouveau_tour) {
			// Un nouveau tour a été enclenché depuis la dernière attaque
			// L'attaque respecte t-elle les 8 heures ?
			if ($diff < DUREE_ANTI_ZERK_S) {
				// Loi anti-zerk non respectée
				$verif_anti_zerk = 0;
			}
			else {
				// Loi anti-zerk respectée
				// On met à jour la table anti-zerk
				$sql = "UPDATE anti_zerk SET date_derniere_attaque=NOW(), date_nouveau_tour='$dla_perso' WHERE id_perso='$id_perso'";
				$mysqli->query($sql);
			}
		}
		else {
			// attaque normal dans son tour
			// On met à jour la table anti-zerk
			$sql = "UPDATE anti_zerk SET date_derniere_attaque=NOW() WHERE id_perso='$id_perso'";
			$mysqli->query($sql);
		}
	}
	else {
		// Faire le premier enregistrement pour la vérification de loi anti-zerk
		$sql = "INSERT INTO anti_zerk(id_perso, date_derniere_attaque, date_nouveau_tour) VALUES ('$id_perso', NOW(), '$dla_perso')";
		$mysqli->query($sql);
	}
	
	return $verif_anti_zerk;
}

/**
  * Fonction qui verifie si le joueur a coche l'envoi de mail lors d'une attaque
  * @param $id_joueur	: L'identifiant du joueur
  * @return bool		: Si le joueur e coche ou non l'envoi de mail
  */
function verif_coche_mail($mysqli, $id_joueur){
	$sql_i = "select mail_info from joueur WHERE id_joueur ='".$id_joueur."'";
	$res_i = $mysqli->query($sql_i);
	$tabAttr_i = $res_i->fetch_assoc();
	return $tabAttr_i["mail_info"];
}

/**
  * Fonction qui envoi un mail au perso qui est attaque
  * @param $nom_attaquant	: Nom du pj ou pnj attaquant
  * @param $id_cible		: identifiant du pj cible de l'attaque
  * @ return void
  */
function mail_attaque($mysqli, $nom_attaquant, $id_cible){
	
	// Recuperation du mail de la cible
	$sql = "SELECT email_joueur, nom_perso FROM joueur, perso WHERE id_perso='$id_cible' AND id_joueur=idJoueur_perso";
	$res = $mysqli->query($sql);
	$t = $res->fetch_assoc();

	// Headers mail
	$headers ='From: "Nord VS Sud"<nordvssud@no-reply.fr>'."\n";
	$headers .='Reply-To: nordvssud@no-reply.fr'."\n";
	$headers .='Content-Type: text/plain; charset="utf-8"'."\n";
	$headers .='Content-Transfer-Encoding: 8bit';
	
	// Destinataire du mail
	$destinataire = $t['email_joueur'];
	$nom_cible = $t['nom_perso'];
	
	// Titre du mail
	$titre = 'Attaque reçue';
	
	// Contenu du mail
	$message = "Votre personnage $nom_cible a reçu une attaque de $nom_attaquant";
	
	// Envoie du mail
	mail($destinataire, $titre, $message, $headers);
}

function log_attaque($mysqli, $id, $id_cible, $id_arme_attaque, $degats, $touche){
	// Insertion log attaque
	$message_log = $id.' a attaqué '.$id_cible;
	$type_action = "Attaque ".$id_arme_attaque;
	$sql = "INSERT INTO log (date_log, id_perso, type_action, id_arme, degats, pourcentage, message_log) VALUES (NOW(), '$id', '$type_action', '$id_arme_attaque', '$degats', '$touche', '$message_log')";
	$mysqli->query($sql);
}

function get_cible($mysqli, $id_cible){
	// recuperation des données du perso cible
	$sql = "SELECT idJoueur_perso, nom_perso, type_perso, image_perso, xp_perso, x_perso, y_perso, pm_perso, pi_perso, pv_perso, pvMax_perso, pmMax_perso, pa_perso, paMax_perso, recup_perso, bonusRecup_perso, bonus_perso, perception_perso, protec_perso, bonusPerception_perso, dateCreation_perso, or_perso, clan, perso_as_grade.id_grade, nom_grade
		FROM perso, perso_as_grade, grades
		WHERE perso_as_grade.id_perso = perso.id_perso
		AND perso_as_grade.id_grade = grades.id_grade
		AND perso.id_perso='$id_cible'";								
	$res = $mysqli->query($sql);
	return $res->fetch_assoc();
}

function calcul_degats($id_arme_attaque, $degatMin_arme_attaque, $valeur_des_arme_attaque, $protec_cible, $type_perso_cible){
	$degats_tmp = calcul_des_attaque($degatMin_arme_attaque, $valeur_des_arme_attaque);
	// calcul degats arme
	if ($id_arme_attaque != 10 && $id_arme_attaque != 11) {
		$degats_final = $degats_tmp - $protec_cible;
	}
	else {
		$degats_final = $degats_tmp;
	}

	// Canon d'artillerie et cible autre artillerie
	if (($id_arme_attaque == 13 || $id_arme_attaque == 22) && $type_perso_cible == 5) {
		// Bonus dégats 13D10
		$bonus_degats_canon = calcul_des_attaque(13, 10);
		$degats_final = $degats_final + $bonus_degats_canon;
	}

	if($degats_final < 0) {
		$degats_final = 0;
	}

	return $degats_final;
}

function calcul_gain_xp($xp_perso, $xp_cible, $id_arme_attaque, $coutPa_arme_attaque, $degats_final){
	$calcul_dif_xp = ($xp_cible - $xp_perso) / 10;

	if ($calcul_dif_xp < 0 || $id_arme_attaque == 10 || $id_arme_attaque == 11) {
		$valeur_des_xp = 0;
	} else {
		$valeur_des_xp = mt_rand(0, $calcul_dif_xp);
	}

	$gain_xp = ceil(($degats_final / 20) + $valeur_des_xp);

	// Limit le nombre d'xp gagné par attaque
	$max_xp_par_attaque = ceil(20 / floor(10 / $coutPa_arme_attaque));
	// Pour le canon et gatling, reserve respectivement 4 et 2 xp pour collats
	if ($id_arme_attaque == 13 || $id_arme_attaque == 22) {
		$max_xp_par_attaque = max(0, $max_xp_par_attaque-4);
	} else if ($id_arme_attaque == 14) {
		$max_xp_par_attaque = max(0, $max_xp_par_attaque-2);
	}
	if ($gain_xp > $max_xp_par_attaque) {
		$gain_xp = $max_xp_par_attaque;
	}

	return $gain_xp;
}

function gain_pc_chef($mysqli, $id, $gain_pc){
	// recup id perso chef
	$sql = "SELECT id_perso FROM `perso` WHERE idJoueur_perso=(SELECT idJoueur_perso FROM perso WHERE id_perso='$id') AND chef='1' ";
	$res = $mysqli->query($sql);
	$t_chef = $res->fetch_assoc();

	$id_perso_chef = $t_chef["id_perso"];

	// MAJ PC Chef
	$sql = "UPDATE perso SET pc_perso = pc_perso + $gain_pc WHERE id_perso='$id_perso_chef'";
	$mysqli->query($sql);
}

function check_cible_capturee($mysqli, $carte, $id, $clan_perso, $couleur_clan_perso, $nom_perso, $nom_grade_perso, $id_cible, $clan_cible, $couleur_clan_cible, $nom_cible, $nom_grade_cible, $pi_cible, $or_cible){
	$sql = "SELECT pv_perso, x_perso, y_perso, xp_perso, pi_perso, pc_perso, type_perso FROM perso WHERE id_perso='$id_cible'";
	$res = $mysqli->query($sql);
	$tab = $res->fetch_assoc();

	$pv_cible 	= $tab["pv_perso"];
	$x_cible 	= $tab["x_perso"];
	$y_cible 	= $tab["y_perso"];
	$xp_cible 	= $tab["xp_perso"];
	$pi_cible 	= $tab["pi_perso"];
	$pc_perso	= $tab["pc_perso"];
	$tp_perso	= $tab["type_perso"];

	// il est mort
	if ($pv_cible <= 0) {

		if (in_bat($mysqli, $id_cible)) {

			// on le supprime du batiment
			$sql = "DELETE FROM perso_in_batiment WHERE id_perso='$id_cible'";
			$mysqli->query($sql);
		}
		else {

			// on l'efface de la carte
			$sql = "UPDATE $carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_cible' AND y_carte='$y_cible'";
			$mysqli->query($sql);
		}

		// Calcul gains (po et xp)
		$perte_po = gain_po_mort($or_cible);

		// Chef
		if ($tp_perso == 1) {
			// Quand un chef meurt, il perd 5% de ses XPi et de ses PC
			// Calcul PI
			$pi_perdu 		= floor(($pi_cible * 5) / 100);
			$pi_perso_fin 	= $pi_cible - $pi_perdu;

			// Calcul PC
			$pc_perdu		= floor(($pc_perso * 5) / 100);
			$pc_perso_fin	= $pc_perso - $pc_perdu;
		}
		else {
			$pi_perso_fin = floor(($pi_cible * 60) / 100);
			$pc_perso_fin = $pc_perso;
		}

		// MAJ perte xp/po/stat cible
		$sql = "UPDATE perso SET or_perso=or_perso-$perte_po, pi_perso=$pi_perso_fin, pc_perso=$pc_perso_fin, nb_mort=nb_mort+1 WHERE id_perso='$id_cible'";
		$mysqli->query($sql);

		echo "<div class=\"infoi\">Vous avez capturé votre cible ! <font color=red>Félicitations.</font></div>";

		$id_arme_non_equipee = id_arme_non_equipee($mysqli, $id_cible);

		$test_perte = mt_rand(0,100);

		if ($id_arme_non_equipee > 0) {

			// 40% de chance de perdre une arme non équipée
			if ($test_perte <= 40) {

				// Suppression de l'arme de l'inventaire du perso
				$sql = "DELETE FROM perso_as_arme WHERE id_perso='$id_cible' AND id_arme='$id_arme_non_equipee' AND est_portee='0' LIMIT 1";
				$mysqli->query($sql);

				// Maj charge perso suite perte de l'arme
				$sql = "UPDATE perso SET charge_perso = charge_perso - (SELECT poids_arme FROM arme WHERE id_arme='$id_arme_non_equipee') WHERE id_perso='$id_cible'";
				$mysqli->query($sql);
			}
		}

		if (!in_bat($mysqli, $id_cible)) {

			if ($perte_po > 0) {
				// On dépose la perte de thune par terre
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
					$sql = "UPDATE objet_in_carte SET nb_objet = nb_objet + $perte_po 
						WHERE type_objet='1' AND id_objet='0'
						AND x_carte='$x_cible' AND y_carte='$y_cible'";
					$mysqli->query($sql);
				}
				else {
					// Insertion dans la table objet_in_carte : On cree le premier enregistrement
					$sql = "INSERT INTO objet_in_carte (type_objet, id_objet, nb_objet, x_carte, y_carte) VALUES ('1','0','$perte_po','$x_cible','$y_cible')";
					$mysqli->query($sql);
				}
			}

			if ($id_arme_non_equipee > 0 && $test_perte <= 40) {

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
		}

		// maj evenements
		$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>','<b>a capturé</b>','$id_cible','<font color=$couleur_clan_cible><b>$nom_cible</b></font>','',NOW(),'0')";
		$mysqli->query($sql);

		// maj cv
		$sql = "INSERT INTO `cv` (IDActeur_cv, nomActeur_cv, gradeActeur_cv, IDCible_cv, nomCible_cv, gradeCible_cv, date_cv) VALUES ($id,'<font color=$couleur_clan_perso>$nom_perso</font>', '$nom_grade_perso', '$id_cible','<font color=$couleur_clan_cible>$nom_cible</font>', '$nom_grade_cible', NOW())";
		$mysqli->query($sql);

		// maj stats du perso
		$sql = "UPDATE perso SET nb_kill=nb_kill+1 WHERE id_perso=$id";
		$mysqli->query($sql);

		// maj stats camp
		if($clan_cible != $clan_perso){
			$sql = "UPDATE stats_camp_kill SET nb_kill=nb_kill+1 WHERE id_camp=$clan_perso";
			$mysqli->query($sql);
		}

		// maj dernier tombé
		$sql = "INSERT INTO dernier_tombe (date_capture, id_perso_capture) VALUES (NOW(), '$id_cible')";
		$mysqli->query($sql);
	}
}

function check_degats_zone($mysqli, $carte, $id, $nom_perso, $grade_perso, $type_perso, $id_j_perso, $clan_perso, $couleur_clan_perso, $xp_perso, $id_cible, $x_cible, $y_cible, $degats_collat, $gain_xp, $gain_pc, $gain_xp_tour_perso, $max_xp_tour_atteint, $id_arme_attaque){
	// Récupération des cibles potentielles autour de la cible principale
	$model_carte = new Carte();
	$res_recherche_collat = $model_carte->recupereVoisins($id_cible, $x_cible, $y_cible)->fetchAll(PDO::FETCH_CLASS,'Carte');;

	$gain_xp_collat_cumul = 0;
	$max_gain_xp_collat_cumul = 4;
	$gain_pc_collat_cumul = 0;
	$model_perso = new Perso();

	// Limite a 2xp le gain max pour collats
	if ($id_arme_attaque == 14)
		$max_gain_xp_collat_cumul = 2;

	// On parcours les cibles pour degats collateraux
	foreach($res_recherche_collat as $t_recherche_collat) {

		$id_cible_collat = $t_recherche_collat->idPerso_carte;

		if ($id_cible_collat < 50000) {

			// Perso
			// Récupération des infos du perso
			$sql = "SELECT idJoueur_perso, nom_perso, image_perso, xp_perso, x_perso, y_perso, pv_perso, pvMax_perso, or_perso, clan, perso_as_grade.id_grade, nom_grade
				FROM perso, perso_as_grade, grades
				WHERE perso_as_grade.id_perso = perso.id_perso
				AND perso_as_grade.id_grade = grades.id_grade
				AND perso.id_perso='$id_cible_collat'";
			$res = $mysqli->query($sql);
			$t_collat = $res->fetch_assoc();

			$id_joueur_collat 	= $t_collat["idJoueur_perso"];
			$nom_collat		= $t_collat["nom_perso"];
			$xp_collat 		= $t_collat["xp_perso"];
			$x_collat 		= $t_collat["x_perso"];
			$y_collat 		= $t_collat["y_perso"];
			$pv_collat 		= $t_collat["pv_perso"];
			$pvM_collat 		= $t_collat["pvMax_perso"];
			$or_collat 		= $t_collat["or_perso"];
			$image_perso_collat 	= $t_collat["image_perso"];
			$clan_collat 		= $t_collat["clan"];
			$grade_collat		= $t_collat['id_grade'];
			$nom_grade_collat	= $t_collat['nom_grade'];

			// Récupération de la couleur associée au clan de la cible
			$couleur_clan_collat = couleur_clan($clan_collat);

			$gain_xp_collat = 1;
			if ($gain_xp_tour_perso + $gain_xp_collat > 20) {
				$gain_xp_collat = 20 - $gain_xp_tour_perso;
				$max_xp_tour_atteint = true;
			}

			$gain_xp_collat_cumul += $gain_xp_collat;
			$gain_pc_collat_cumul += 1;

			$gain_pc_collat = calcul_gain_pc_attaque_perso($grade_perso, $grade_collat, $clan_perso, $clan_collat, $type_perso, $id_j_perso, $id_joueur_collat);

			// Limite 2 PC par attaque de Gatling
			if ($id_arme_attaque == 14 && $gain_pc + $gain_pc_collat_cumul > 2) {
				$gain_pc_collat = 0;
			}

			// mise a jour des pv et des malus de la cible
			$sql = "UPDATE perso SET pv_perso=pv_perso-$degats_collat, bonus_perso=bonus_perso-2 WHERE id_perso='$id_cible_collat'";
			$mysqli->query($sql);

			echo "<br>Vous avez infligé $degats_collat dégâts collatéraux à <font color='$couleur_clan_collat'>$nom_collat</font> - Matricule $id_cible_collat.<br>";

			if ($gain_xp_collat_cumul <= $max_gain_xp_collat_cumul && !$max_xp_tour_atteint) {
				echo "Vous avez gagné $gain_xp_collat xp.<br><br>";

				$model_perso->perso_gain_xp($id, $gain_xp_collat);
				$gain_xp_tour_perso += $gain_xp_collat;

				// Passage grade grouillot
				passage_grade_grouillot($mysqli, $id, $grade_perso, $xp_perso, $gain_xp_collat);

			} else {
				$gain_xp_collat = 0;
				echo "Vous avez gagné 0 xp";
				if ($max_xp_tour_atteint) {
					echo " (maximum de gain d'xp par tour atteint)";
				}
				else {
					echo " (maximum de gain d'xp par attaque atteint)";
				}

				echo ".<br><br>";
			}

			// recup id perso chef
			$sql = "SELECT id_perso FROM `perso` WHERE idJoueur_perso=(SELECT idJoueur_perso FROM perso WHERE id_perso='$id') AND chef='1' ";
			$res = $mysqli->query($sql);
			$t_chef = $res->fetch_assoc();

			$id_perso_chef = $t_chef["id_perso"];

			// mise à jour des PC du chef									
			$sql = "UPDATE perso SET pc_perso = pc_perso + $gain_pc_collat WHERE id_perso='$id_perso_chef'";
			$mysqli->query($sql);

			// mise a jour de la table evenement
			$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>','a infligé des dégâts collatéraux ','$id_cible_collat','<font color=$couleur_clan_collat><b>$nom_collat</b></font>',' ( Dégâts : $degats_collat ; Gain XP : $gain_xp_collat ; Gain PC : $gain_pc_collat )',NOW(),'0')";
			$mysqli->query($sql);

			$sql = "SELECT pv_perso, x_perso, y_perso, xp_perso, pi_perso, type_perso, pc_perso FROM perso WHERE id_perso='$id_cible_collat'";
			$res = $mysqli->query($sql);
			$tab = $res->fetch_assoc();

			$pv_collat_fin 	= $tab["pv_perso"];
			$x_collat_fin 	= $tab["x_perso"];
			$y_collat_fin 	= $tab["y_perso"];
			$xp_collat_fin 	= $tab["xp_perso"];
			$pi_collat_fin 	= $tab["pi_perso"];
			$tp_collat_fin	= $tab["type_perso"];
			$pc_collat_fin  = $tab["pc_perso"];

			// il est mort
			if ($pv_collat_fin <= 0) {

				// on l'efface de la carte
				$sql = "UPDATE $carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_collat_fin' AND y_carte='$y_collat_fin'";
				$mysqli->query($sql);

				// Calcul gains (po et xp)
				$perte_po = gain_po_mort($or_collat);

				// Chef
				if ($tp_collat_fin == 1) {
					// Quand un chef meurt, il perd 5% de ses XPi et de ses PC
					// Calcul PI
					$pi_perdu 		= floor(($pi_collat_fin * 5) / 100);
					$pi_perso_fin 	= $pi_collat_fin - $pi_perdu;

					// Calcul PC
					$pc_perdu		= floor(($pc_collat_fin * 5) / 100);
					$pc_perso_fin	= $pc_collat_fin - $pc_perdu;
				}
				else {
					$pi_perso_fin = floor(($pi_collat_fin * 60) / 100);
					$pc_perso_fin = $pc_collat_fin;
				}

				// MAJ perte xp/po/stat cible
				$sql = "UPDATE perso SET or_perso=or_perso-$perte_po, pi_perso=$pi_perso_fin, pc_perso=$pc_perso_fin, nb_mort=nb_mort+1 WHERE id_perso='$id_cible_collat'";
				$mysqli->query($sql);

				if ($perte_po > 0) {
					// On dépose la perte de PO par terre
					// Verification si l'objet existe deja sur cette case
					$sql = "SELECT nb_objet FROM objet_in_carte 
						WHERE objet_in_carte.x_carte = $x_collat_fin 
						AND objet_in_carte.y_carte = $y_collat_fin 
						AND type_objet = '1' AND id_objet = '0'";
					$res = $mysqli->query($sql);
					$to = $res->fetch_assoc();

					$nb_o = $to["nb_objet"];

					if($nb_o){
						// On met a jour le nombre
						$sql = "UPDATE objet_in_carte SET nb_objet = nb_objet + $perte_po 
							WHERE type_objet='1' AND id_objet='0'
							AND x_carte='$x_collat_fin' AND y_carte='$y_collat_fin'";
						$mysqli->query($sql);
					}
					else {
						// Insertion dans la table objet_in_carte : On cree le premier enregistrement
						$sql = "INSERT INTO objet_in_carte (type_objet, id_objet, nb_objet, x_carte, y_carte) VALUES ('1','0','$perte_po','$x_collat_fin','$y_collat_fin')";
						$mysqli->query($sql);
					}
				}

				$id_arme_non_equipee = id_arme_non_equipee($mysqli, $id_cible_collat);

				if ($id_arme_non_equipee > 0) {

					$test_perte = mt_rand(0,100);

					// 40% de chance de perdre une arme non équipée
					if ($test_perte <= 40) {

						// Suppression de l'arme de l'inventaire du perso
						$sql = "DELETE FROM perso_as_arme WHERE id_perso='$id_cible_collat' AND id_arme='$id_arme_non_equipee' AND est_portee='0' LIMIT 1";
						$mysqli->query($sql);

						// Maj charge perso suite perte de l'arme
						$sql = "UPDATE perso SET charge_perso = charge_perso - (SELECT poids_arme FROM arme WHERE id_arme='$id_arme_non_equipee') WHERE id_perso='$id_cible_collat'";
						$mysqli->query($sql);

						// On dépose la perte de l'arme par terre
						// Verification si l'objet existe deja sur cette case
						$sql = "SELECT nb_objet FROM objet_in_carte 
							WHERE objet_in_carte.x_carte = $x_collat_fin 
							AND objet_in_carte.y_carte = $y_collat_fin 
							AND type_objet = '3' AND id_objet = '$id_arme_non_equipee'";
						$res = $mysqli->query($sql);
						$to = $res->fetch_assoc();

						$nb_o = $to["nb_objet"];

						if($nb_o){
							// On met a jour le nombre
							$sql = "UPDATE objet_in_carte SET nb_objet = nb_objet + 1 
								WHERE type_objet='3' AND id_objet='$id_arme_non_equipee'
								AND x_carte='$x_collat_fin' AND y_carte='$y_collat_fin'";
							$mysqli->query($sql);
						}
						else {
							// Insertion dans la table objet_in_carte : On cree le premier enregistrement
							$sql = "INSERT INTO objet_in_carte (type_objet, id_objet, nb_objet, x_carte, y_carte) VALUES ('3','$id_arme_non_equipee','1','$x_collat_fin','$y_collat_fin')";
							$mysqli->query($sql);
						}
					}
				}

				echo "<div class=\"infoi\">Vous avez capturé <font color='$couleur_clan_collat'>$nom_collat</font> - Matricule $id_cible_collat ! <font color=red>Félicitations.</font></div>";

				// maj evenements
				$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>','<b>a capturé</b>','$id_cible_collat','<font color=$couleur_clan_collat><b>$nom_collat</b></font>','',NOW(),'0')";
				$mysqli->query($sql);

				// maj cv
				$sql = "INSERT INTO `cv` (IDActeur_cv, nomActeur_cv, gradeActeur_cv, IDCible_cv, nomCible_cv, gradeCible_cv, date_cv) VALUES ($id,'<font color=$couleur_clan_perso>$nom_perso</font>', '$nom_grade_perso', '$id_cible_collat','<font color=$couleur_clan_collat>$nom_collat</font>', '$nom_grade_collat', NOW())";
				$mysqli->query($sql);

				// maj stats de la cible
				$sql = "UPDATE perso SET nb_kill=nb_kill+1 WHERE id_perso=$id";
				$mysqli->query($sql);

				// maj stats camp
				if($clan_collat != $clan_perso){
					$sql = "UPDATE stats_camp_kill SET nb_kill=nb_kill+1 WHERE id_camp=$clan_perso";
					$mysqli->query($sql);
				}

				// maj dernier tombé
				$sql = "INSERT INTO dernier_tombe (date_capture, id_perso_capture) VALUES (NOW(), '$id_cible_collat')";
				$mysqli->query($sql);
			}

		} else if ($id_cible_collat >= 200000) {

			// PNJ
			// Récupération des infos du PNJ	
			$sql = "SELECT pnj.id_pnj, nom_pnj, pv_i, x_i, y_i, pv_i, pvMax_pnj, protec_pnj 
				FROM pnj, instance_pnj 
				WHERE pnj.id_pnj=instance_pnj.id_pnj AND idInstance_pnj='$id_cible_collat'";
			$res = $mysqli->query($sql);
			$t_cible = $res->fetch_assoc();

			$id_pnj_collat 			= $t_cible["id_pnj"];
			$nom_cible_collat 		= $t_cible["nom_pnj"];
			$pv_cible_collat 		= $t_cible["pv_i"];
			$x_cible_collat 		= $t_cible["x_i"];
			$y_cible_collat 		= $t_cible["y_i"];
			$pv_cible_collat 		= $t_cible["pv_i"];
			$pvMax_cible_collat 	= $t_cible["pvMax_pnj"];
			$protec_cible_collat	= $t_cible["protec_pnj"];
			$image_pnj_collat 		= "pnj".$t_cible["id_pnj"]."t.png";

			$gain_xp_collat = 1;
			if ($gain_xp_tour_perso + $gain_xp_collat > 20) {
				$gain_xp_collat = 20 - $gain_xp_tour_perso;
				$max_xp_tour_atteint = true;
			}

			$gain_xp_collat_cumul += $gain_xp_collat;

			// mise a jour des pv de la cible
			$sql = "UPDATE instance_pnj SET pv_i=pv_i-$degats_collat, dernierAttaquant_i=$id WHERE idInstance_pnj='$id_cible_collat'";
			$mysqli->query($sql);

			echo "<br>Vous avez infligé $degats_collat dégâts collatéraux à $nom_cible_collat<br>";

			if ($gain_xp_collat_cumul <= $max_gain_xp_collat_cumul && !$max_xp_tour_atteint) {
				echo "Vous avez gagné $gain_xp_collat xp.<br><br>";

				$model_perso->perso_gain_xp($id, $gain_xp_collat);
				$gain_xp_tour_perso += $gain_xp_collat;

				// Passage grade grouillot
				passage_grade_grouillot($mysqli, $id, $grade_perso, $xp_perso, $gain_xp_collat);

			} else {
				$gain_xp_collat = 0;
				echo "Vous avez gagné 0 xp";
				if ($max_xp_tour_atteint) {
					echo " (maximum de gain d'xp par tour atteint)";
				}
				else {
					echo " (maximum de gain d'xp par attaque atteint)";
				}

				echo ".<br><br>";
			}

			// maj evenement
			$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>','a infligé des dégâts collatéraux ','$id_cible_collat','<b>$nom_cible_collat</b>',' ( Dégâts : $degats_collat ; Gain XP : $gain_xp_collat ; Gain PC : 0 )',NOW(),'0')";
			$mysqli->query($sql);

			// recuperation des données du pnj aprés attaque
			$sql = "SELECT pv_i, x_i, y_i FROM instance_pnj WHERE idInstance_pnj='$id_cible_collat'";
			$res = $mysqli->query($sql);
			$tab = $res->fetch_assoc();

			$pv_cible_collat 	= $tab["pv_i"];
			$x_cible_collat 	= $tab["x_i"];
			$y_cible_collat 	= $tab["y_i"];

			// il est mort
			if ($pv_cible_collat <= 0) {

				echo "Vous avez tué $nom_cible_collat avec des dégâts collatéraux ! <font color=red>Félicitations.</font>";

				// on l'efface de la carte
				$sql = "UPDATE $carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x_cible_collat' AND y_carte='$y_cible_collat'";
				$mysqli->query($sql);

				// on le delete
				$sql = "DELETE FROM instance_pnj WHERE idInstance_pnj='$id_cible_collat'";
				$mysqli->query($sql);

				// verification que le perso n'a pas déjà tué ce type de pnj
				$sql_v = "SELECT id_pnj FROM perso_as_killpnj WHERE id_pnj='$id_pnj_collat' AND id_perso='$id'";
				$res_v = $mysqli->query($sql_v);
				$verif_pnj = $res_v->num_rows;

				// nb_pnj 
				$sql = "UPDATE perso SET nb_pnj=nb_pnj+1 WHERE id_perso='$id'";
				$mysqli->query($sql);

				if($verif_pnj == 0){
					// il n'a jamais tué de pnj de ce type => insert
					$sql = "INSERT INTO perso_as_killpnj VALUES('$id','$id_pnj_collat','1')";
					$mysqli->query($sql);
				}
				else { 
					// il en a déjà tué => update
					$sql = "UPDATE perso_as_killpnj SET nb_pnj=nb_pnj+1 WHERE id_perso='$id' AND id_pnj='$id_pnj_collat'";
					$mysqli->query($sql);
				}

				// maj evenement
				$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>','a tué','$id_cible_collat','<b>$nom_cible_collat</b>','',NOW(),'0')";
				$mysqli->query($sql);

				// maj cv
				$sql = "INSERT INTO `cv` (IDActeur_cv, nomActeur_cv, gradeActeur_cv, IDCible_cv, nomCible_cv, date_cv) VALUES ($id,'<font color=$couleur_clan_perso>$nom_perso</font>', '$nom_grade_perso', '$id_cible_collat','$nom_cible_collat',NOW())";
				$mysqli->query($sql);

				echo "<br><center><a href=\"jouer.php\">retour</a></center>";
			}
		} else {
			// Batiment => pas de collat sur batiment
		}
	}
}

function verif_charge_pm($type_perso, $pm_perso) {
	if (!($type_perso == 1 || $type_perso == 2 || $type_perso == 7 || $type_perso == 3))
		return false;
	return $pm_perso >= distance_min_charge_pm($type_perso);
}

function distance_min_charge_pm($type_perso) {
	if ($type_perso == 3)
		return 2;
	return 4;
}

?>
