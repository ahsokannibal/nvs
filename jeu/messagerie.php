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
	
	<script language='javascript'>
	var ok2 = true;
	var ok3 = true;

	function test_chk()	{
	var test = 0;
	var i = 0;
		while(eval('document.chk.check'+i))	{
			test++;
			i++
			;
		}
		for(i=0; i<test; i++)	{
			var box = 'check' + i;
			document.getElementById(box).checked = ok2;
		}
		ok2=!ok2;
	}  
	</script>
	
	<body>
<?php
$id = $_SESSION["id_perso"];

// recuperation des message dont il est le destinataire
$sql_a_lire = "SELECT id_message FROM message_perso WHERE lu_message='0' AND supprime_message='0' AND id_perso='".$id."'";
$res_a_lire = $mysqli->query($sql_a_lire);
$a_lire = $res_a_lire->num_rows;
?>

		<table border=1 align="center" cellpadding=2 cellspacing=1 width=550>
			<tr align="center" bgcolor="#EEEEDD">
			<p align="center"><input type="button" value="Fermer la messagerie" onclick="window.close()"></p>
				<td>Messages reçus<font color="red"> <?php if($a_lire) echo" (".$a_lire." new)"; ?></font></td>
				<td><a href="message_envoye.php">Messages envoyés</a></td>
				<td><a href="nouveau_message.php">Nouveau message</a></td>
			</tr>
			<tr align="center" bgcolor="#EEEEDD">
				<td><a href="messagerie_contacts.php">Contacts</a></td>
				<td><a href="messagerie_dossiers.php">Dossiers</a></td>
				<td></td>
			</tr>
		</table>
		<br />

		<form name= "chk" method="post" action="traitement/t_messagerie.php">
			<table border=1 align="center" cellpadding=2 cellspacing=1 width=550>
<?php
$sql = "SELECT message.id_message, expediteur_message, date_message, objet_message, lu_message 
		FROM message, message_perso 
		WHERE id_perso='".$id."' 
		AND message_perso.id_message = message.id_message
		AND id_dossier='1'
		AND supprime_message='0'
		ORDER BY date_message DESC";
$resultat = $mysqli->query($sql);

if ($resultat->num_rows == 0) {
    echo "<tr align=center><td colspan=4>Aucun message</td></tr>";
}
else {
	echo '<tr class="titre">
			<th><input type="checkbox" name="test" onclick="test_chk()"></th><th>Expediteur</th><th>Date</th><th colspan=2>Objet</th>
		  </tr>';
	echo '<form method="post" action="traitement/messagerie.php">';
	$i = 0;
	
	while($row = $resultat->fetch_assoc()) {
		echo '<tr class="mess">';
		echo '<td width=20><input type="checkbox" id='."'check".$i."'". 'name="id_message[]" value="'.$row["id_message"].'"></td>';
		if ($row["lu_message"]){
			echo "<td width=120>" . $row["expediteur_message"] . "</td><td width=150>" . stripslashes($row["date_message"]) . "</td><td colspan=2><a href=message_lire.php?id=" . $row["id_message"] . "&methode=r>" . stripslashes($row["objet_message"]) . "</a></td>";
		}
		else {
			echo '<td><div class="info">' . $row["expediteur_message"] . '</div></td><td><div class="info">' . stripslashes($row["date_message"]) . '</div></td><td colspan=2><a href=message_lire.php?id=' . $row["id_message"] . "&methode=r>" . stripslashes($row["objet_message"]) . "</a><b> (non lu)</b></td>";
		}
		echo '</tr>';
		$i++;
	}
}
?>
			</table>

			<br>
			<table border=0 align="center">
				<tr>
					<td>Que voulez-vous faire des messages sélectionnés?&nbsp;</td>
					<td><input type="submit" name="submit" value="Effacer">&nbsp;</td>
					<td><input type="submit" name="submit" value="Archiver"></td>
				</tr>
			</table>
		</form>

		<br>
	</body>
</html>
<?php
}
else{
	echo "<font color=red>Vous ne pouvez pas accéder à cette page, veuillez vous loguer.</font>";
}
?>
