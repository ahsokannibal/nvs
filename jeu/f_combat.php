<?php
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
		
		// Est-ce que la cible est dans le même bataillon ?
		if ($id_j_perso == $id_joueur_cible) {
			$gain_pc = 0;
		}
		else {
			$gain_pc = 1;
		}
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
	$sql = "SELECT * FROM anti_zerk WHERE id_perso='$id_perso'";
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
		
		//date_default_timezone_set('Europe/Paris');
		$date_now = time();
		
		$diff_date = $date_now - strtotime($date_nouveau_tour);
		$diff_date_h = $diff_date / 3600;
		
		if ($diff_date >= 0) {
			// Un nouveau tour a été enclenché depuis la dernière attaque
			// L'attaque respecte t-elle les 8 heures ?
			$diff = $date_now - strtotime($date_derniere_attaque);
			
			if ($diff < 8 * 60 * 60) {
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
 * Fonction permettant de gérer la loi anti-zerk sur les bousculades
 */
function gestion_anti_zerk_bousculade($mysqli, $id_perso) {
	
	$verif_anti_zerk = 1;
						
	// Verification si enregistrement d'attaque existant
	$sql = "SELECT * FROM anti_zerk_bousculade WHERE id_perso='$id_perso'";
	$res = $mysqli->query($sql);
	$nb_enr_anti_zerk = $res->num_rows;
	
	// recupération dla perso
	$sql_p = "SELECT DLA_perso FROM perso WHERE id_perso='$id_perso'";
	$res_p = $mysqli->query($sql_p);
	$t_p = $res_p->fetch_assoc();
	
	$dla_perso = $t_p['DLA_perso'];
	
	if ($nb_enr_anti_zerk > 0) {
		
		$t_zerk = $res->fetch_assoc();
		
		$date_derniere_bousculade	= $t_zerk['date_derniere_bousculade'];
		$date_nouveau_tour			= $t_zerk['date_nouveau_tour'];
		
		//date_default_timezone_set('Europe/Paris');
		$date_now = time();
		
		$diff_date = $date_now - strtotime($date_nouveau_tour);
		$diff_date_h = $diff_date / 3600;
		
		if ($diff_date >= 0) {
			// Un nouveau tour a été enclenché depuis la dernière bousculade
			// La bousculade respecte t-elle les 8 heures ?
			$diff = $date_now - strtotime($date_derniere_bousculade);
			
			if ($diff < 8 * 60 * 60) {
				// Loi anti-zerk non respectée
				$verif_anti_zerk = 0;
			}
			else {
				// Loi anti-zerk respectée
				// On met à jour la table anti_zerk_bousculade
				$sql = "UPDATE anti_zerk_bousculade SET date_derniere_bousculade=NOW(), date_nouveau_tour='$dla_perso' WHERE id_perso='$id_perso'";
				$mysqli->query($sql);
			}
		}
		else {
			// attaque normal dans son tour
			// On met à jour la table anti_zerk_bousculade
			$sql = "UPDATE anti_zerk_bousculade SET date_derniere_bousculade=NOW() WHERE id_perso='$id_perso'";
			$mysqli->query($sql);
		}
	}
	else {
		// Faire le premier enregistrement pour la vérification de loi anti-zerk
		$sql = "INSERT INTO anti_zerk_bousculade(id_perso, date_derniere_bousculade, date_nouveau_tour) VALUES ('$id_perso', NOW(), '$dla_perso')";
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
?>
