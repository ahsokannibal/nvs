<?php
session_start();
require_once("../fonctions.php");
require_once("f_carte.php");

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

//***************************************
// Traitement des persos a mettre en gel
//***************************************
$sql = "SELECT id_perso FROM perso WHERE a_gele='1'";
$res = $mysqli->query($sql);

while ($t = $res->fetch_assoc()){
	
	$id_perso = $t["id_perso"];
	
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
}

//***********************************************
// Traitement des persos inactifs a placer en gel
//***********************************************
// On place en gel les persos avec une date de DLA ancienne de plus de 10 jours (5 tours)
$sql = "SELECT id_perso FROM `perso` WHERE DLA_perso < DATE_SUB(CURRENT_DATE, INTERVAL 10 DAY) and est_gele='0'";
$res_inactif = $mysqli->query($sql);

while ($t = $res_inactif->fetch_assoc()){
	
	$id_perso = $t["id_perso"];
	
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

//***********************************************
// Envoi mail inactifs bientôt supprimés
//***********************************************
// On envoi un mail aux joueurs dont les persos sont gelés depuis 20 jours
$sql = "SELECT DISTINCT(email_joueur) FROM perso, joueur WHERE perso.idJoueur_perso = joueur.id_joueur
		AND date_gele < DATE_SUB(CURRENT_DATE, INTERVAL 20 DAY) AND est_gele='1'";
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
// On supprime les persos et le compte du joueur dont les persos sont gelés depuis 30 jours
$sql = "SELECT DISTINCT(joueur.id_joueur) FROM perso, joueur WHERE perso.idJoueur_perso = joueur.id_joueur AND date_gele < DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY) AND est_gele='1'";
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
			$sql = "SELECT COUNT(montant) as thune_en_banque FROM histobanque_compagnie 
					WHERE id_perso='$id_perso' 
					AND id_compagnie=( SELECT id_compagnie FROM perso_in_compagnie WHERE id_perso='$id_perso')";
			$res_b = $mysqli->query($sql);
			$tab = $res_b->fetch_assoc();
			
			$thune_en_banque = $tab["thune_en_banque"];
			
			if ($thune_en_banque > 0 || $thune_en_banque < 0) {
				// Si le montant est < 0 => On rembourse la compagnie de l'emprunt perdu
				// Si le montant est > 0 => la compagnie perd la thune déposée par ce perso
				
				$sql = "UPDATE banque_as_compagnie SET montant = montant - $thune_en_banque 
						WHERE id_compagnie= ( SELECT id_compagnie FROM perso_in_compagnie WHERE id_perso='$id_perso')";
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
?>