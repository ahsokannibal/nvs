<?php
session_start();
require_once("../fonctions.php");

$mysqli = db_connexion();

include ('../nb_online.php');

if(@$_SESSION["id_perso"]){
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Messagerie</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../style.css" rel="stylesheet" type="text/css">
</head>

<body>
<?php
$id = $_SESSION["id_perso"];

// recuperation du nom du perso
$sql = "SELECT nom_perso FROM perso WHERE id_perso='$id'";
$res = $mysqli->query($sql);
$t = $res->fetch_assoc();
$pseudo = $t["nom_perso"];

$sql_a_lire = "SELECT id_message FROM message_perso WHERE lu_message='0' AND supprime_message='0' AND id_perso='".$id."'";
$res_a_lire = $mysqli->query($sql_a_lire);
$a_lire = $res_a_lire->num_rows;
?>

<table border=1 align="center" cellpadding=2 cellspacing=1 width=550>
<tr align="center" bgcolor="#EEEEDD">
<p align="center"><input type="button" value="Fermer la messagerie" onclick="window.close()"></p>
	<td><a href="messagerie.php">Messages reçus</a><font color="red"> <?php if($a_lire) echo" (".$a_lire." new)"; ?></font></td>
	<td>Messages envoyés</td>
	<td><a href="nouveau_message.php">Nouveau message</a></td>
</tr>
<tr align="center" bgcolor="#EEEEDD">
	<td><a href="messagerie_contacts.php">Contacts</a></td>
	<td><a href="messagerie_dossiers.php">Dossiers</a></td>
	<td></td>
</tr>
</table>
<br />

<table border=1 align="center" cellpadding=2 cellspacing=1 width=550>

<?php
// recupération des infos sur le message
$sql = "SELECT message.id_message as id_mes, expediteur_message, date_message, objet_message, lu_message 
		FROM message, message_perso 
		WHERE expediteur_message='".$pseudo."'
		AND message_perso.id_message = message.id_message
		ORDER BY date_message DESC";
$resultat = $mysqli->query($sql);

if ($resultat->num_rows == 0){
    echo "<tr align=center><td colspan=4>Aucun message envoyé</td></tr>";
}
else {	
	echo '<tr class="titre">';
	echo '<th width=120>Destinataire</th><th width=150>Date</th><th colspan=2>Objet</th>';
	echo '</tr>';
	while($row = $resultat->fetch_assoc()) {
		
		$destinataires = "";
		
		// recupération des destinataires
		$sql_dest = "SELECT id_perso FROM message_perso WHERE id_message=".$row["id_mes"]."";
		$res_dest = $mysqli->query($sql_dest);
		while($t_dest = $res_dest->fetch_assoc()){
			
			$id_dest = $t_dest["id_perso"];
			
			// recup du nom
			$sql_n = "SELECT nom_perso FROM perso WHERE id_perso='$id_dest'";
			$res_n = $mysqli->query($sql_n);
			$t_n = $res_n->fetch_assoc();
			
			if(isset($destinataires) && $destinataires != ""){
				$destinataires .= " ; ".$t_n["nom_perso"];
			}
			else {
				$destinataires = $t_n["nom_perso"];
			}
		}
	
		if ($row["lu_message"]){
			echo '<tr class="mess"><td>' . $destinataires . "</td><td>" . $row["date_message"] . "</td><td colspan=2><a href=message_lire.php?id=" . $row["id_mes"] . "&methode=e>" . stripslashes($row["objet_message"]) . "</a></td></tr>";
		}
		else{
			echo '<tr class="mess"><td><div class="info">' . $destinataires . '</div></td><td><div class="info">' . $row["date_message"] . '</div></td><td colspan=2><a href=message_lire.php?id=' . $row["id_mes"] . "&methode=e>" . stripslashes($row["objet_message"]) . "</a></td></tr>";
		}
	}	
}
?>
</table>
</body>
</html>
<?php
}
else{
	echo "<font color=red>Vous ne pouvez pas accéder à cette page, veuillez vous loguer.</font>";
}
?>
