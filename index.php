<?php
session_start();
require_once("fonctions.php");
require_once('mvc/model/Administration.php');

$mysqli = db_connexion();

$administration = new Administration();
$maintenance_mode = $administration->getMaintenanceMode();

include ('nb_online.php');

// récupération nombre de joueurs inscrit
$sql = "SELECT id_joueur FROM joueur";
$res = $mysqli->query($sql);
$nb_inscrit = $res->num_rows;

// dernier inscrit
$sql = "SELECT nom_perso, clan FROM perso, joueur 
		WHERE perso.idJoueur_perso = joueur.id_joueur 
		ORDER BY id_joueur DESC, id_perso ASC LIMIT 1";
$res = $mysqli->query($sql);
$t = $res->fetch_assoc();

$pseudo_last_inscrit 	= $t['nom_perso'];
$clan_last_inscrit 		= $t['clan'];

// Nombre de persos actifs 
$sql = "SELECT count(id_perso) as nb_persos_actifs FROM perso WHERE est_gele='0'";
$res = $mysqli->query($sql);
$t = $res->fetch_assoc();

$nb_persos_actifs = $t['nb_persos_actifs'];

// Nombre de joueurs actif nordistes
$sql = "SELECT COUNT(id_perso) as nb_joueurs_nord_actifs FROM perso WHERE clan='1' AND chef='1' AND est_gele='0'";
$res = $mysqli->query($sql);
$t = $res->fetch_assoc();
$nb_joueurs_nord_actifs = $t['nb_joueurs_nord_actifs'];

// Nombre de joueurs actif sudistes
$sql = "SELECT COUNT(id_perso) as nb_joueurs_sud_actifs FROM perso WHERE clan='2' AND chef='1' AND est_gele='0'";
$res = $mysqli->query($sql);
$t = $res->fetch_assoc();
$nb_joueurs_sud_actifs = $t['nb_joueurs_sud_actifs'];


// on prépare une requête SQL permettant de compter le nombre de tuples (soit le nombre de clients connectés au site) contenu dans la table
$sql = 'SELECT count(*) FROM nb_online';
$res = $mysqli->query($sql);
$count_online = $res->fetch_array();

// récupération des news
$sql_news = "SELECT date, contenu FROM news ORDER BY date DESC LIMIT 10";
$res_news = $mysqli->query($sql_news);

include('mvc/view/home.php');
?>
