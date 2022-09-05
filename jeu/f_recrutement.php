<?php

function calcul_pg($mysqli, $id) {
	// Calculer PG déjà utilisés par le joueur
	$pg_utilise = 0;

	$sql = "SELECT SUM(cout_pg) as pg_utilise FROM perso JOIN type_unite ON id_unite=type_perso WHERE idJoueur_perso='$id' and est_renvoye=0";
	$res = $mysqli->query($sql);
	$t = $res->fetch_assoc();
	$pg_utilise = $t["pg_utilise"];

	return $pg_utilise;
}

function verif_perso_est_dans_fort_ou_fortin($mysqli, $id) {
	// Récupération du batiment dans lequel se trouve le perso 
	$sql = "SELECT instance_batiment.id_instanceBat, id_batiment FROM perso_in_batiment JOIN instance_batiment ON perso_in_batiment.id_instanceBat = instance_batiment.id_instanceBat WHERE id_perso='$id'";
	$res = $mysqli->query($sql);
	$nb = $res->num_rows;
	if (!$nb)
		return false;

	$tab = $res->fetch_assoc();
	$id_instance_bat = $tab["id_instanceBat"];
	$id_batiment = $tab["id_batiment"];

	// Fort ou Fortin
	if ($id_batiment != 8 && $id_batiment != 9)
		return false;
	return true;
}

?>
