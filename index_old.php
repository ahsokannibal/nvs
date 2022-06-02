<?php
session_start();
require_once("fonctions.php");

$mysqli = db_connexion();

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

// Nombre de persos actif nordistes
$sql = "SELECT count(id_perso) as nb_persos_nord_actifs FROM perso WHERE est_gele='0' AND clan='1'";
$res = $mysqli->query($sql);
$t = $res->fetch_assoc();

$nb_persos_nord_actifs = $t['nb_persos_nord_actifs'];

// Nombre de persos actif sudistes
$sql = "SELECT count(id_perso) as nb_persos_sud_actifs FROM perso WHERE est_gele='0' AND clan='2'";
$res = $mysqli->query($sql);
$t = $res->fetch_assoc();

$nb_persos_sud_actifs = $t['nb_persos_sud_actifs'];


// on prépare une requête SQL permettant de compter le nombre de tuples (soit le nombre de clients connectés au site) contenu dans la table
$sql = 'SELECT count(*) FROM nb_online';
$res = $mysqli->query($sql);
$count_online = $res->fetch_array();

// récupération des news


include('mvc/view/home.php');
?>