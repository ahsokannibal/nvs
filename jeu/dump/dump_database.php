<?php
session_start();
require_once("../../fonctions.php");
require_once("f_dump.php");

$mysqli = db_connexion();

// Récupération de la clef secrete
$sql = "SELECT valeur_config FROM config_jeu WHERE code_config='clef_secrete'";
$res = $mysqli->query($sql);
$t = $res->fetch_assoc();

$clef_secrete = $t['valeur_config'];

if (isset($_GET['clef']) && $_GET['clef'] == $clef_secrete) {
	
	dump_mysql($mysqli, "127.0.0.1", "root", "", "nvs", 2);
	
}