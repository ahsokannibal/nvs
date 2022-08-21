<?php
session_start();
require_once("../fonctions.php");
require_once("f_carte.php");
require_once("f_combat.php");

$mysqli = db_connexion();

function mail_gel_persos($nom_perso, $email_joueur, $titre, $message){

	// Headers mail
	$headers ='From: "Nord VS Sud"<nordvssud@no-reply.fr>'."\n";
	$headers .='Reply-To: nordvssud@no-reply.fr'."\n";
	$headers .='Content-Type: text/plain; charset="utf-8"'."\n";
	$headers .='Content-Transfer-Encoding: 8bit';
	
	// Envoie du mail
	mail($email_joueur, $titre, $message, $headers);
}

// Récupération de la clef secrete
$sql = "SELECT valeur_config FROM config_jeu WHERE code_config='clef_secrete'";
$res = $mysqli->query($sql);
$t = $res->fetch_assoc();

$clef_secrete = $t['valeur_config'];

if (isset($_GET['clef']) && $_GET['clef'] == $clef_secrete) {

	//************************************************
	// Traitement des persos a libérer du pénitencier
	//************************************************
	$sql = "SELECT perso_bagne.id_perso, perso.nom_perso, perso.clan FROM perso, perso_bagne 
			WHERE perso.id_perso = perso_bagne.id_perso
			AND perso_bagne.date_debut <= DATE_SUB(CURRENT_DATE, INTERVAL perso_bagne.duree DAY)";
	$res = $mysqli->query($sql);

	while ($t = $res->fetch_assoc()){
		
		$id_perso_relacher 	= $t['id_perso'];
		$nom_perso			= $t['nom_perso'];
		$camp_perso			= $t['clan'];
		
		// Récupération de la couleur associée au clan du perso
		$couleur_clan_perso = couleur_clan($camp_perso);

		// Verification que le perso est bien dans un pénitencier
		$sql = "SELECT * FROM perso_in_batiment, instance_batiment WHERE perso_in_batiment.id_instanceBat = instance_batiment.id_instanceBat AND id_batiment=10 AND id_perso='$id_perso_relacher'";
		$res = $mysqli->query($sql);
		$verif_peni = $res->num_rows;
		
		if ($verif_peni == 1) {

			// le supprimer du pénitencier
			$sql = "DELETE FROM perso_in_batiment WHERE id_perso='$id_perso_relacher'";
			$mysqli->query($sql);
			
			// Passer ses PV à 0 pour permettre un respawn
			$sql = "UPDATE perso SET pv_perso=0 WHERE id_perso='$id_perso_relacher'";
			$mysqli->query($sql);
			
			// Sécurité
			$sql = "UPDATE carte SET idPerso_carte=NULL, image_carte=NULL, occupee_carte='0' WHERE idPerso_carte¨='$id_perso_relacher'";
			$mysqli->query($sql);
			
			// Ajout événement
			$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso_relacher,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>',' a terminé de purger sa peine au pénitencier', NULL, NULL, '', NOW(), '0')";
			$mysqli->query($sql);

		}
	}


	//***************************************
	// Traitement des persos a mettre en gel
	//***************************************
	$sql_gel = "SELECT id_perso, nom_perso, clan FROM perso WHERE a_gele='1' AND date_gele <= DATE_SUB(CURRENT_DATE, INTERVAL 3 DAY)";
	$res_gel = $mysqli->query($sql_gel);

	while ($t_gel = $res_gel->fetch_assoc()){
		
		$id_perso 	= $t_gel["id_perso"];
		$nom_perso	= $t_gel["nom_perso"];
		$clan_perso	= $t_gel["clan"];
		
		$in_penitencier = false;
		
		$id_inst_bat = in_bat($mysqli, $id_perso);
		
		if ($id_inst_bat) {
			
			$sql = "SELECT id_batiment FROM instance_batiment WHERE id_instanceBat = '$id_inst_bat'";
			$res = $mysqli->query($sql);
			$t = $res->fetch_assoc();
			
			$id_bat = $t['id_batiment'];
			
			if ($id_bat == 10) {
				$in_penitencier = true;
			}
		}
		
		if (!$in_penitencier) {
			
			echo "gel du perso $id_perso <br />";
			
			// maj du statut du perso
			$sql = "UPDATE perso SET est_gele='1', a_gele='0', date_gele=NOW() WHERE id_perso='$id_perso'";
			$mysqli->query($sql);
			
			// maj de la carte => disparition du perso
			if (in_bat($mysqli, $id_perso)) {		
				$sql = "DELETE FROM perso_in_batiment WHERE id_perso='$id_perso'";
				$mysqli->query($sql);
			}
			else if (in_train($mysqli, $id_perso)) {
				$sql = "DELETE FROM perso_in_train WHERE id_perso='$id_perso'";
				$mysqli->query($sql);
			}
			else {
				$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE idPerso_carte='$id_perso'";
				$mysqli->query($sql);
			}
			
			// Récupération de la couleur associée au clan du perso
			$couleur_clan_perso = couleur_clan($clan_perso);
			
			// Ajout événement
			$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>',' est parti en permission', NULL, NULL,'',NOW(),'0')";
			$mysqli->query($sql);
		}
		else {
			// Perso dans pénitencier => ne peut pas être placé en gel
			$sql = "UPDATE perso SET a_gele='0', date_gele=NULL WHERE id_perso='$id_perso'";
			$mysqli->query($sql);
		}
	}

	//***********************************************
	// Traitement des persos inactifs a placer en gel
	//***********************************************
	// On place en gel les persos avec une date de DLA ancienne de plus de 10 jours (5 tours)
	$sql = "SELECT id_perso, nom_perso, clan FROM `perso` WHERE DLA_perso < DATE_SUB(CURRENT_DATE, INTERVAL 10 DAY) and est_gele='0' AND id_perso!='1' AND id_perso!='2'";
	$res_inactif = $mysqli->query($sql);

	while ($t = $res_inactif->fetch_assoc()){
		
		$id_perso 	= $t["id_perso"];
		$nom_perso	= $t["nom_perso"];
		$clan_perso	= $t["clan"];
		
		$in_penitencier = false;
		
		$id_inst_bat = in_bat($mysqli, $id_perso);
		
		if ($id_inst_bat) {
			
			$sql = "SELECT id_batiment FROM instance_batiment WHERE id_instanceBat = '$id_inst_bat'";
			$res = $mysqli->query($sql);
			$t = $res->fetch_assoc();
			
			$id_bat = $t['id_batiment'];
			
			if ($id_bat == 10) {
				$in_penitencier = true;
			}
		}
		
		if (!$in_penitencier) {
			echo "gel du perso inactif $id_perso <br />";
			
			// maj du statut du perso
			$sql = "UPDATE perso SET est_gele='1', a_gele='0', date_gele=NOW() WHERE id_perso='$id_perso'";
			$mysqli->query($sql);
			
			// maj de la carte => disparition du perso
			if (in_bat($mysqli, $id_perso)) {		
				$sql = "DELETE FROM perso_in_batiment WHERE id_perso='$id_perso'";
				$mysqli->query($sql);
			}
			else if (in_train($mysqli, $id_perso)) {
				$sql = "DELETE FROM perso_in_train WHERE id_perso='$id_perso'";
				$mysqli->query($sql);
			}
			else {
				$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE idPerso_carte='$id_perso'";
				$mysqli->query($sql);
			}
			
			// Récupération de la couleur associée au clan du perso
			$couleur_clan_perso = couleur_clan($clan_perso);
			
			// Ajout événement
			$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES ($id_perso,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>',' a été placé en permission', NULL, NULL,'',NOW(),'0')";
			$mysqli->query($sql);
			
			$sql = "SELECT email_joueur, nom_perso FROM joueur, perso WHERE perso.id_perso='$id_perso' AND perso.idJoueur_perso = joueur.id_joueur";
			$res = $mysqli->query($sql);
			$t_perso = $res->fetch_assoc();
			
			$nom_perso 		= $t_perso["nom_perso"];
			$email_joueur	= $t_perso["email_joueur"];
			
			// Titre du mail
			$titre = 'Placement de votre perso en permission';
			
			// Contenu du mail
			$message = "Votre perso ".$nom_perso." a été placé en permission pour inactivité.";
			
			// Envoi mail gel perso
			mail_gel_persos($nom_perso, $email_joueur, $titre, $message);
		}
	}

	//***********************************************
	// Envoi mail inactifs bientôt supprimés
	//***********************************************
	// On envoi un mail aux joueurs dont les persos sont gelés depuis 120 jours
	$sql = "SELECT DISTINCT(email_joueur) FROM perso, joueur WHERE perso.idJoueur_perso = joueur.id_joueur
			AND date_gele < DATE_SUB(CURRENT_DATE, INTERVAL 120 DAY) AND est_gele='1' AND id_perso!='1' AND id_perso!='2'";
	$res = $mysqli->query($sql);

	while ($t = $res->fetch_assoc()){
		
		$email_joueur = $t["email_joueur"];
		
		// Titre du mail
		$titre = 'Rappel avant suppression de votre compte';
		
		// Contenu du mail
		$message = "Votre compte sur Nord VS Sud sera supprimé pour inactivité d'ici 10 jours si vous ne vous reconnectez pas";
		
		// Envoi mail gel perso
		mail_gel_persos($nom_perso, $email_joueur, $titre, $message);
	}

	//***********************************************
	// Traitement suppression des inactifs
	//***********************************************
	// On supprime les persos et le compte des persos PNJs dont les persos sont gelés depuis 130 jours (désactivé pour les joueurs- on souhaite conserver les CVs)
	$sql = "SELECT DISTINCT(joueur.id_joueur) FROM perso, joueur WHERE perso.idJoueur_perso = joueur.id_joueur AND date_gele < DATE_SUB(CURRENT_DATE, INTERVAL 130 DAY) AND est_gele='1' AND id_perso!='1' AND id_perso!='2' AND id_perso<100";
	$res_sup = $mysqli->query($sql);

	while ($t = $res_sup->fetch_assoc()){
		
		$id_joueur = $t["id_joueur"];
		
		// Suppression persos
		$sql_p = "SELECT id_perso FROM perso WHERE idJoueur_perso='$id_joueur'";
		$res_p = $mysqli->query($sql_p);

		while ($t_p = $res_p->fetch_assoc()){
			
			$id_perso = $t_p["id_perso"];
			
			echo "Suppression du perso inactif $id_perso <br />";
			
			// maj de la carte => suppression du perso
			if (in_bat($mysqli, $id_perso)) {		
				$sql = "DELETE FROM perso_in_batiment WHERE id_perso='$id_perso'";
			}
			else if (in_train($mysqli, $id_perso)) {
				$sql = "DELETE FROM perso_in_train WHERE id_perso='$id_perso'";
			}
			else {
				$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE idPerso_carte='$id_perso'";
			}
			$mysqli->query($sql);
			
			// Suppression du perso 
			$sql = "DELETE FROM perso WHERE id_perso='$id_perso'";
			$mysqli->query($sql);
			
			$sql = "DELETE FROM perso_as_arme WHERE id_perso='$id_perso'";
			$mysqli->query($sql);
			
			$sql = "DELETE FROM perso_as_armure WHERE id_perso='$id_perso'";
			$mysqli->query($sql);
			
			$sql = "DELETE FROM perso_as_competence WHERE id_perso='$id_perso'";
			$mysqli->query($sql);
			
			$sql = "DELETE FROM perso_as_contact WHERE id_perso='$id_perso'";
			$mysqli->query($sql);
			
			$sql = "DELETE FROM perso_as_dossiers WHERE id_perso='$id_perso'";
			$mysqli->query($sql);
			
			$sql = "DELETE FROM perso_as_entrainement WHERE id_perso='$id_perso'";
			$mysqli->query($sql);
			
			$sql = "DELETE FROM perso_as_grade WHERE id_perso='$id_perso'";
			$mysqli->query($sql);
			
			$sql = "DELETE FROM perso_as_killpnj WHERE id_perso='$id_perso'";
			$mysqli->query($sql);
			
			$sql = "DELETE FROM perso_as_objet WHERE id_perso='$id_perso'";
			$mysqli->query($sql);
			
			$sql = "DELETE FROM perso_as_respawn WHERE id_perso='$id_perso'";
			$mysqli->query($sql);
			
			// Est ce que le perso était dans une compagnie ?
			$sql = "SELECT id_perso FROM perso_in_compagnie WHERE id_perso='$id_perso'";
			$res_c = $mysqli->query($sql);
			$is_in_compagnie = $res_c->num_rows;
			
			if ($is_in_compagnie) {
			
				// On regarde si le perso n'a pas de dette dans une banque de compagnie
				$sql = "SELECT SUM(montant) as thune_en_banque FROM histobanque_compagnie 
						WHERE id_perso='$id_perso' 
						AND id_compagnie=( SELECT id_compagnie FROM perso_in_compagnie WHERE id_perso='$id_perso')";
				$res_b = $mysqli->query($sql);
				$tab = $res_b->fetch_assoc();
				
				$thune_en_banque = $tab["thune_en_banque"];
				
				if ($thune_en_banque != 0) {
					// Si le montant est < 0 => On rembourse la compagnie de l'emprunt perdu
					// Si le montant est > 0 => la compagnie perd la thune déposée par ce perso
					
					$sql = "SELECT id_compagnie FROM perso_in_compagnie WHERE id_perso='$id_perso'";
					$res = $mysqli->query($sql);
					$t = $res->fetch_assoc();
					
					$id_compagnie = $t['id_compagnie'];
					
					$sql = "UPDATE banque_as_compagnie SET montant = montant - $thune_en_banque 
							WHERE id_compagnie='$id_compagnie'";
					$mysqli->query($sql);
					
					$sql = "SELECT montant FROM banque_as_compagnie WHERE id_compagnie='$id_compagnie'";
					$res = $mysqli->query($sql);
					$t = $res->fetch_assoc();
					
					$montant_final_banque = $t['montant'];
					
					$date = time();
					
					// banque log
					$sql = "INSERT INTO banque_log (date_log, id_compagnie, id_perso, montant_transfert, montant_final) VALUES (FROM_UNIXTIME($date), '$id_compagnie', '$id_perso', '-$thune_en_banque', '$montant_final_banque')";
					$mysqli->query($sql);
				}
				
				$sql = "DELETE FROM histobanque_compagnie WHERE id_perso='$id_perso'";
				$mysqli->query($sql);
				
				$sql = "DELETE FROM banque_compagnie WHERE id_perso='$id_perso'";
				$mysqli->query($sql);

				$sql = "DELETE FROM perso_in_compagnie WHERE id_perso='$id_perso'";
				$mysqli->query($sql);
			}
			
			
			$sql = "DELETE FROM perso_in_em WHERE id_perso='$id_perso'";
			$mysqli->query($sql);
		}
		
		// Suppression du joueur 
		$sql = "DELETE FROM joueur WHERE id_joueur='$id_joueur'";
		$mysqli->query($sql);
		
		$sql = "DELETE FROM joueur_as_ip WHERE id_joueur='$id_joueur'";
		$mysqli->query($sql);
	}
}
?>
