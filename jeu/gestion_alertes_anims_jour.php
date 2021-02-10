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
	
	$debut  = date("Y-m-d H:i:s" ,mktime(0, 0, 0, date("m")  , date("d")-1, date("Y")));
	$fin 	= date("Y-m-d H:i:s" ,mktime(23, 59, 59, date("m")  , date("d")-1, date("Y")));
	
	$nb_max_log_24h = 1000;
	
	// On analyse les logs d'accès de la journée de tous les joueurs
	$sql = "SELECT id_perso, COUNT(*) as nb_logs 
			FROM acces_log 
			WHERE date_acces >= '$debut' AND date_acces <=  '$fin'
			GROUP BY id_perso
			HAVING nb_logs >= $nb_max_log_24h";
	$res = $mysqli->query($sql);
	
	while ($t = $res->fetch_assoc()) {
		
		$id_perso 		= $t['id_perso'];
		$nb_logs_24h	= $t['nb_logs'];
		
		$raison_alerte	= "Nombres de raffraichissements >= $nb_max_log_24h : $nb_logs_24h";
		
		$sql_i = "INSERT INTO alerte_anim (type_alerte, id_perso, raison_alerte, date_alerte) VALUES ('1', '$id_perso', '$raison_alerte', NOW())";
		$mysqli->query($sql_i);
	}
}