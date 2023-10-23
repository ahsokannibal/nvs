<?php
session_start();
require_once("../fonctions.php");
require_once("f_combat.php");

$mysqli = db_connexion();

// Récupération de la clef secrete
$sql = "SELECT valeur_config FROM config_jeu WHERE code_config='clef_secrete'";
$res = $mysqli->query($sql);
$t = $res->fetch_assoc();

$clef_secrete = $t['valeur_config'];

if (isset($_GET['clef']) && $_GET['clef'] == $clef_secrete) {
	// Récupération des barricades et tours de guet qui ne sont pas a proximité des forts, fortins et gares, et qui ne contiennent pas de persos.
	$sql = "SELECT id_instanceBat, nom_instance, camp_instance, x_instance, y_instance, pv_instance, nom_batiment FROM instance_batiment as b0 LEFT JOIN batiment ON b0.id_batiment = batiment.id_batiment WHERE (b0.id_batiment=1 OR b0.id_batiment=2) AND id_instanceBat NOT IN (SELECT b2.id_instanceBat from instance_batiment as b1 JOIN instance_batiment as b2 WHERE (b1.id_batiment=8 OR b1.id_batiment=9 OR b1.id_batiment=11) AND (b2.id_batiment=1 OR b2.id_batiment=2) AND (b2.x_instance BETWEEN b1.x_instance - 8 AND b1.x_instance + 8) AND (b2.y_instance BETWEEN b1.y_instance - 8 AND b1.y_instance + 8)) AND id_instanceBat NOT IN (SELECT id_instanceBat FROM perso_in_batiment)";
	$res = $mysqli->query($sql);

	while ($t_b = $res->fetch_assoc()) {
		$id_instance_bat 	= $t_b['id_instanceBat'];
		$nom_instance		= $t_b['nom_instance'];
		$camp_instance		= $t_b['camp_instance'];
		$x		= $t_b['x_instance'];
		$y		= $t_b['y_instance'];
		$pv_instance		= $t_b['pv_instance'];
		$nom_batiment		= $t_b['nom_batiment'];

		$couleur_clan_batiment = couleur_clan($camp_instance);

		echo "bat ".$id_instance_bat." ".$nom_instance." ".$x." ".$y."\n";

		$degats = min($pv_instance, 6);

		// mise à jour des pv du batiment
		$sql = "UPDATE instance_batiment SET pv_instance=pv_instance-$degats WHERE id_instanceBat='$id_instance_bat'";
		$mysqli->query($sql);

		// maj evenement
		$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) VALUES (0,'','se dégrade','$id_instance_bat','<font color=$couleur_clan_batiment><b>$nom_batiment</b></font>',' ',NOW(),'0')";
		$mysqli->query($sql);					

		if ($pv_instance - $degats <= 0) {
			// on efface le batiment de la carte
			$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE x_carte='$x' AND y_carte='$y'";
			$mysqli->query($sql);

			// on delete le bâtiment
			$sql = "DELETE FROM instance_batiment WHERE id_instanceBat='$id_instance_bat'";
			$mysqli->query($sql);
		}

	}
}
?>
