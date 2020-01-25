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
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	</head>

	<body>
<?php
$id = $_SESSION["id_perso"];

// recuperation du nom du perso
$sql = "SELECT nom_perso FROM perso WHERE id_perso='$id'";
$res = $mysqli->query($sql);
$t = $res->fetch_assoc();

$pseudo = $t["nom_perso"];

// nombre de message à lire
$sql_a_lire = "SELECT id_message FROM message_perso WHERE lu_message='0' AND supprime_message='0' AND id_perso='".$id."'";
$res_a_lire = $mysqli->query($sql_a_lire);
$a_lire = $res_a_lire->num_rows;
?>
<table border=1 align="center" cellpadding=2 cellspacing=1 width=550>
<tr align="center" bgcolor="#EEEEDD">
	<td><a href="messagerie.php">Messages reçus</a><font color="red"> <?php if($a_lire) echo" (".$a_lire." new)"; ?></font></td>
	<td><a href="message_envoye.php">Messages envoyés</a></td>
	<td><a href="nouveau_message.php">Nouveau message</a></td>
</tr>
<tr align="center" bgcolor="#EEEEDD">
	<td><a href="messagerie_contacts.php">Contacts</a></td>
	<td><a href="messagerie_dossiers.php">Dossiers</a></td>
	<td></td>
</tr>
</table>
<br>
<table border=1 align="center" cellpadding=2 cellspacing=1 width=550>
<?php
$id_message = $_GET["id"];
$verif = preg_match("#^[0-9]+$#i",$id_message);
	
if($verif){
	if(isset($_GET["methode"])){
		$methode = $_GET["methode"];
		
		// verif identité joueur qui veut lire le message
		// si il est destinataire
		$sql = "SELECT id_perso FROM message_perso WHERE id_perso='$id' AND id_message='$id_message'";
		$res = $mysqli->query($sql);
		$verif_id = $res->num_rows;
		
		// si il est expediteur
		$sql = "SELECT id_message FROM message WHERE expediteur_message='$pseudo' AND id_message='$id_message'";
		$res = $mysqli->query($sql);
		$verif_id2 = $res->num_rows;
		
		if($verif_id || $verif_id2){
		
			//si on consulte un message reçu, le message est marqué "lu"
			if ($methode == "r") { 
				$sql_lu = "UPDATE message_perso SET lu_message='1' WHERE id_perso='$id' AND id_message='" . $id_message . "'";
				$res_lu = $mysqli->query($sql_lu);
			}
			
			// recupération des données du message
			$sql = "SELECT expediteur_message, date_message, objet_message, contenu_message FROM message, message_perso 
					WHERE message.id_message='" . $id_message . "' 
					AND (id_perso='".$id."' OR expediteur_message='".$pseudo."')";
			$resultat = $mysqli->query($sql);
			$tab = $resultat->fetch_assoc();
			
			// recupération des destinataires
			$sql_dest = "SELECT id_perso FROM message_perso WHERE id_message='$id_message'";
			$res_dest = $mysqli->query($sql_dest);
			
			while($t_dest = $res_dest->fetch_assoc()){
				
				$id_dest = $t_dest["id_perso"];
				
				// recup du nom
				$sql_n = "SELECT nom_perso FROM perso WHERE id_perso='$id_dest'";
				$res_n = $mysqli->query($sql_n);
				$t_n = $res_n->fetch_assoc();
				
				if(isset($destinataires) && $destinataires != ""){
					$destinataires .= ";".$t_n["nom_perso"];
				}
				else {
					$destinataires = $t_n["nom_perso"];
				}
			}
			
			echo '<tr class="exp"><td><b>Expediteur :</b> ' . $tab["expediteur_message"] . "</td><td><b>Date de l'envoi :</b> " . $tab["date_message"] . "</td></tr>";
			echo '<tr class="exp"><td colspan=2><b>Destinataires :</b> '.$destinataires.'</td></tr>';
			echo '<tr class="titrel"><td colspan=2><center><b>Objet :</b> ' . stripslashes($tab["objet_message"]) . "</center></td></tr>";
			echo '<tr class="messl"><td colspan=2>';
			
			if ($tab["contenu_message"] == ""){ 
				echo "<i>Message vide</i>"; 
			}
			else{ 
				echo bbcode(htmlentities(stripslashes($tab["contenu_message"])));
			}
			
			echo "</td></tr></table>";
			
			if ($methode == "r"){
				echo '<form method="post" action="traitement/t_lire.php">';
				echo '<input type="hidden" name="id_message" value="' . $id_message . '">';
				echo '<div align="center"> 
						<input type="submit" name="submit" value="Repondre"> &nbsp&nbsp 
						<input type="submit" name="submit" value="Repondre à tous"> &nbsp&nbsp 
						<input type="submit" name="submit" value="Effacer"> </div>';
				echo "</form>";
			}
		}
		else {
			echo "<center>Ce message ne vous est pas destiné !</center>";
		}
	}
	else {
		echo "<center>Méthode non spécifiée !</center>";
	}
}
else {
	echo "<center>Données incorrectes</center>";
}
?>
	</body>
</html>
<?php
}
else{
	echo "<font color=red>Vous ne pouvez pas accéder à cette page, veuillez vous loguer.</font>";
}
?>
