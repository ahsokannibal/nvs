<?php
function gestion_anti_zerk_depot($mysqli, $id) {
						
	// Verification si enregistrement d'attaque existant
	$sql = "SELECT date_dernier_retrait FROM anti_zerk_banque_compagnie WHERE id_perso='$id'";
	$res = $mysqli->query($sql);
	$t = $res->fetch_assoc();
	
	$date_dernier_retrait = $t['date_dernier_retrait'];
	$date_now = time();
	
	$diff = $date_now - strtotime($date_dernier_retrait);
	$huit_heure = 8 * 60 * 60;
	
	$temps_restant = $huit_heure - $diff;
	
	return $temps_restant;
}
?>