<?php
/**
  * Fonction de combat d'un pnj
  * @param $de_pnj	: Le nombre de des du pnj
  * @param $de_pj	: Le nombre de des du pj
  * @return bool	  	: Si le pnj a touche le pj
  */
function combat_pnj($de_pnj, $de_pj){
	// Score du pj
	srand((double) microtime() * 1000000);
	$score_pj = rand($de_pj, $de_pj*3);
	echo "score joueur : <b>".$score_pj."</b>";
	
	// Score du pnj
	srand((double) microtime() * 1000000);
	$score_pnj = rand($de_pnj, $de_pnj*3);
	echo "<br>score pnj : <b>".$score_pnj."</b><br>";
	
	// Si le score du pnj est superieur au score du pj
	if ($score_pnj > $score_pj) { // touche
		return 1;
	}
	else
		return 0;
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
	if($clan_perso == '2'){
		$couleur_clan_perso = 'red';
	}
	if($clan_perso == '3'){
		$couleur_clan_perso = 'green';
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

	if($per_perso < $portee_max){
		$portee_max = $per_perso;
	}
	
	if($portee_max < $portee_min){
		return 0;
	}

	// Requete qui recupere les cases a portee d'attaque
	$sql = "(SELECT idPerso_carte, occupee_carte 
			FROM $carte, perso 
			WHERE id_perso='$id_perso'
			AND x_carte>=x_perso+$portee_min AND x_carte<=x_perso+$portee_max
			AND y_carte>=y_perso-$portee_max AND y_carte<=y_perso+$portee_max)
			UNION (
				SELECT idPerso_carte, occupee_carte 
				FROM $carte, perso 
				WHERE id_perso='$id_perso'
				AND x_carte>=x_perso-$portee_max AND x_carte<=x_perso-$portee_min
				AND y_carte>=y_perso-$portee_max AND y_carte<=y_perso+$portee_max
			)
			UNION (
				SELECT idPerso_carte, occupee_carte 
				FROM $carte, perso 
				WHERE id_perso='$id_perso'
				AND y_carte>=y_perso+$portee_min AND y_carte<=y_perso+$portee_max
				AND x_carte>=x_perso-$portee_max AND x_carte<=x_perso+$portee_max
			)
			UNION (
				SELECT idPerso_carte, occupee_carte 
				FROM $carte, perso 
				WHERE id_perso='$id_perso'
				AND y_carte>=y_perso-$portee_max AND y_carte<=y_perso-$portee_min
				AND x_carte>=x_perso-$portee_max AND x_carte<=x_perso+$portee_max
			)";
	$res = $mysqli->query($sql);
	
	// On parcours ces cases
	while ($t_coor_p = $res->fetch_assoc()){
		
		$oc_t = $t_coor_p["occupee_carte"];
		$id_t = $t_coor_p["idPerso_carte"];
		
		// Si la case est occupee
		if($oc_t) {
			// Si c'est notre cible
			if($id_t == $id_cible)
				return 1;
		}
	}
	
	return 0;
}

/**
 *
 */
function resource_liste_cibles_a_portee_attaque($mysqli, $carte, $id_perso, $portee_min, $portee_max, $per_perso) {
	
	if($per_perso < $portee_max){
		$portee_max = $per_perso;
	}
	
	if($portee_max < $portee_min){
		return;
	}

	// Requete qui recupere les cases a portee d'attaque
	$sql = "(SELECT idPerso_carte
			FROM $carte, perso 
			WHERE id_perso='$id_perso'
			AND x_carte>=x_perso+$portee_min AND x_carte<=x_perso+$portee_max
			AND y_carte>=y_perso-$portee_max AND y_carte<=y_perso+$portee_max
			AND occupee_carte = '1')
			UNION (
				SELECT idPerso_carte
				FROM $carte, perso 
				WHERE id_perso='$id_perso'
				AND x_carte>=x_perso-$portee_max AND x_carte<=x_perso-$portee_min
				AND y_carte>=y_perso-$portee_max AND y_carte<=y_perso+$portee_max
				AND occupee_carte = '1'
			)
			UNION (
				SELECT idPerso_carte
				FROM $carte, perso 
				WHERE id_perso='$id_perso'
				AND y_carte>=y_perso+$portee_min AND y_carte<=y_perso+$portee_max
				AND x_carte>=x_perso-$portee_max AND x_carte<=x_perso+$portee_max
				AND occupee_carte = '1'
			)
			UNION (
				SELECT idPerso_carte
				FROM $carte, perso 
				WHERE id_perso='$id_perso'
				AND y_carte>=y_perso-$portee_max AND y_carte<=y_perso-$portee_min
				AND x_carte>=x_perso-$portee_max AND x_carte<=x_perso+$portee_max
				AND occupee_carte = '1'
			)";
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
  * Fonction qui recupere le total de defense qu'apporte les armures sur un perso
  * @param $id_perso	: L'identifiant du perso
  * @return Int		: Le nombre correspondant au total des defenses des armures que le perso a d'equipe sur lui, 0 si pas d'armure
  */
function defense_armure($mysqli, $id_perso){
	
	// malus
	$malus = 0;
	
	// On fait la somme des bonus en defense apporte par les armures que porte le perso
	$sql = "SELECT SUM(bonusDefense_armure) as sum_armure FROM armure, perso_as_armure
			WHERE armure.id_armure = perso_as_armure.id_armure
			AND est_portee='1' AND id_perso='$id_perso'";
	$res = $mysqli->query($sql);
	$t = $res->fetch_assoc();
	$total_armure = $t["sum_armure"];
	
	// On vefie s'il soufre ou non de malus s'il est nu
	if(!possede_comp_nu($mysqli, $id_perso)){
		
		// On verifie s'il porte une armure
		$sql_c = "SELECT id_armure FROM perso_as_armure WHERE id_perso='$id_perso' AND est_portee='1'";
		$res_c = $mysqli->query($sql_c);
		$ok_c = $res_c->num_rows;
		if($ok_c == 0) {
			$malus = $malus - 1;
		}
			
	}
	
	if($total_armure == null)
		$total_armure = 0;
		
	$total_armure = $total_armure - $malus;	
	return $total_armure;
}

/**
  * Fonction qui permet de verifier si un perso possede la competence defense d'armure
  * @param $id_perso			: L'identifiant du perso
  * @return $nb_point			: Le nombre de point dans la competence, 0 si pas possede
  */
function possede_defense_armure($mysqli, $id_perso){
	$sql = "SELECT nb_points FROM perso_as_competence WHERE id_perso='$id_perso' AND id_competence='57'";
	$res = $mysqli->query($sql);
	$t = $res->fetch_assoc();
	$nb = $res->num_rows;
	
	if($nb){
		return $t['nb_points'];
	}

	return 0;
}

/**
  * Fonction qui permet de verifier si un perso possede la competence nudiste invetere
  * @param $id_perso			: L'identifiant du perso
  * @return $nb_point			: Le nombre de point dans la competence, 0 si pas possede
  */
function possede_comp_nu($mysqli, $id_perso){
	$sql = "SELECT nb_points FROM perso_as_competence WHERE id_perso='$id_perso' AND id_competence='58'";
	$res = $mysqli->query($sql);
	$t = $res->fetch_assoc();
	$nb = $res->num_rows;
	
	if($nb){
		return $t['nb_points'];
	}

	return 0;
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
  * Fonction qui verifie si un perso possede la competence de port d'armes lourdes
  * @param $id_perso	: L'identifiant du personnage
  * @return Int			: 1 si possede, 0 si non
  */
function port_armes_lourdes($mysqli, $id_perso){
	$sql = "SELECT nb_points FROM perso_as_competence WHERE id_perso='$id_perso' AND id_competence='60'";
	$res = $mysqli->query($sql);
	$t = $res->fetch_assoc();
	
	return $t['nb_points'];
}

/** 
  * Fonction qui verifie si un perso possede la competence de port d'armures lourdes
  * @param $id_perso	: L'identifiant du personnage
  * @return Int			: 1 si possede, 0 si non
  */
function port_armures_lourdes($mysqli, $id_perso){
	$sql = "SELECT nb_points FROM perso_as_competence WHERE id_perso='$id_perso' AND id_competence='61'";
	$res = $mysqli->query($sql);
	$t = $res->fetch_assoc();
	
	return $t['nb_points'];
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
	$headers .='Content-Type: text/plain; charset="iso-8859-1"'."\n";
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
