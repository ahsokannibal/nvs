<?php
session_start();
require_once("../fonctions.php");

$mysqli = db_connexion();

// Récupération de la clef secrete
$sql = "SELECT valeur_config FROM config_jeu WHERE code_config='clef_secrete'";
$res = $mysqli->query($sql);
$t = $res->fetch_assoc();

$clef_secrete = $t['valeur_config'];

if (isset($_GET['clef']) && $_GET['clef'] == $clef_secrete) {
	// Récupération des points stratégiques
	$sql_b = "SELECT id_instanceBat, nom_instance, camp_instance FROM instance_batiment WHERE id_batiment=13";
	$res_b = $mysqli->query($sql_b);

	$nb_pv_nord = 0;
	$nb_pv_sud = 0;

	while ($t_b = $res_b->fetch_assoc()) {
		$id_instance_bat 	= $t_b['id_instanceBat'];
		$nom_instance		= $t_b['nom_instance'];
		$camp_instance		= $t_b['camp_instance'];

		if ($camp_instance == 1)
			$nb_pv_nord += 1;
		else if ($camp_instance == 2)
			$nb_pv_sud += 1;
	}

	if ($nb_pv_nord != $nb_pv_sud) {
		$gain_pv = $nb_pv_nord > $nb_pv_sud ? $nb_pv_nord - $nb_pv_sud : $nb_pv_sud - $nb_pv_nord;
		$camp = $nb_pv_nord > $nb_pv_sud ? 1 : 2;
		$nom_camp = $camp == 1 ? "Nord" : "Sud";

		// Ajout des PV
		$sql = "UPDATE stats_camp_pv SET points_victoire = points_victoire + ".$gain_pv." WHERE id_camp='".$camp."'";
		$mysqli->query($sql);

		// Ajout de l'historique
		$date = time();
		$texte = addslashes("Pour le contrôle d'un plus grand nombre de points stratégiques par le ".$nom_camp);
		$sql = "INSERT INTO histo_stats_camp_pv (date_pvict, id_camp, gain_pvict, texte) VALUES (FROM_UNIXTIME($date), '$camp', '$gain_pv', '$texte')";
		$mysqli->query($sql);

	}
}
?>
