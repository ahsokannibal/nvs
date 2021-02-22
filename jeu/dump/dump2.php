<?php
session_start();
require_once("../../fonctions.php");

$mysqli = db_connexion();

// Récupération de la clef secrete
$sql = "SELECT valeur_config FROM config_jeu WHERE code_config='clef_secrete'";
$res = $mysqli->query($sql);
$t = $res->fetch_assoc();

$clef_secrete = $t['valeur_config'];

if (isset($_GET['clef']) && $_GET['clef'] == $clef_secrete) {

	$nom_fichier = "dump_nvs_".date("Y-m-d").".sql";

	$database = 'nvs';
	$user = 'root';
	$pass = '';
	$host = 'localhost';
	$dir = dirname(__FILE__) . '/'.$nom_fichier;

	exec("mysqldump --user={$user} --password={$pass} --host={$host} {$database} --result-file={$dir} 2>&1", $output);
}