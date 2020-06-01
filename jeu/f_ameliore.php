<?php

/**
 * Fonction permettant de calculer le cout d'amélioration des PA
 */
function ameliore_pa($mysqli, $pa_courant, $type_perso){
	
	$sql = "SELECT pa_unite FROM type_unite WHERE id_unite='$type_perso'";
	$res = $mysqli->query($sql);
	$tab = $res->fetch_assoc();
	
	$pa_base_unite = $tab["pa_unite"];
	
	$pa_ameliore_cible = $pa_courant - $pa_base_unite;
	$base_cout_pa 	= 200;
	$cout_achat_pa 	= 60;
	
	$resultat = $base_cout_pa + $pa_ameliore_cible * $cout_achat_pa;
	
	return $resultat." pi";
}

/**
 * Fonction permettant de calculer le cout d'amélioration des PM
 */
function ameliore_pm($mysqli, $pm_courant, $type_perso){
	
	$sql = "SELECT pm_unite FROM type_unite WHERE id_unite='$type_perso'";
	$res = $mysqli->query($sql);
	$tab = $res->fetch_assoc();
	
	$pm_base_unite = $tab["pm_unite"];
	
	$pm_ameliore_cible = $pm_courant - $pm_base_unite;
	$base_cout_pm 	= 175;
	$cout_achat_pm 	= 53;
	
	$resultat = $base_cout_pm + $pm_ameliore_cible * $cout_achat_pm;
	
	return $resultat." pi";
}

/**
 * Fonction permettant de calculer le cout d'amélioration des PV
 */
function ameliore_pv($mysqli, $pv_courant, $type_perso){
	
	$sql = "SELECT pv_unite FROM type_unite WHERE id_unite='$type_perso'";
	$res = $mysqli->query($sql);
	$tab = $res->fetch_assoc();
	
	$pv_base_unite = $tab["pv_unite"];
	
	$pv_ameliore_cible = $pv_courant - $pv_base_unite;
	$base_cout_pv 	= 1.2;
	$cout_achat_pv 	= 0.4;
	
	$resultat = floor($base_cout_pv + $pv_ameliore_cible * $cout_achat_pv);
	
	return $resultat." pi";
}

/**
 * Fonction permettant de calculer le cout d'amélioration de la Perception
 */
function ameliore_perc($mysqli, $per_courant, $type_perso){
	
	$sql = "SELECT perception_unite FROM type_unite WHERE id_unite='$type_perso'";
	$res = $mysqli->query($sql);
	$tab = $res->fetch_assoc();
	
	$per_base_unite = $tab["perception_unite"];
	
	$per_ameliore_cible = $per_courant - $per_base_unite;
	$base_cout_per 	= 150;
	$cout_achat_per = 45;
	
	$resultat = $base_cout_per + $per_ameliore_cible * $cout_achat_per;
	
	return $resultat." pi";
}

/**
 * Fonction permettant de calculer le cout d'amélioration de la Recup
 */
function ameliore_recup($mysqli, $rec_courant, $type_perso){
	
	$sql = "SELECT recup_unite FROM type_unite WHERE id_unite='$type_perso'";
	$res = $mysqli->query($sql);
	$tab = $res->fetch_assoc();
	
	$rec_base_unite = $tab["recup_unite"];
	
	$rec_ameliore_cible = $rec_courant - $rec_base_unite;
	$base_cout_rec 	= 10;
	$cout_achat_rec = 3;
	
	$resultat = $base_cout_rec + $rec_ameliore_cible * $cout_achat_rec;
	
	return $resultat." pi";
}
?>