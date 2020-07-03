<?php
session_start();
require_once("../fonctions.php");

$mysqli = db_connexion();

include ('../nb_online.php');

if(isset($_SESSION["id_perso"])){
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<title>Messagerie</title>
		
		<!-- Required meta tags -->
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		
		<!-- Bootstrap CSS -->
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
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
		<div class="container-fluid">
		
			<p align="center"><input type="button" value="Fermer la messagerie" onclick="window.close()"></p>

			<div class="row justify-content-center">
				<div class="col-12">
					<table border=1 align="center" cellpadding=2 cellspacing=1 width=100%>
						<tr align="center" bgcolor="#EEEEDD">
							<td width="33%"><a href="messagerie.php">Messages reçus</a><font color="red"> <?php if($a_lire) echo" (".$a_lire." new)"; ?></font></td>
							<td width="33%">Messages envoyés</td>
							<td><a href="nouveau_message.php">Nouveau message</a></td>
						</tr>
						<tr align="center" bgcolor="#EEEEDD">
							<td><a href="messagerie_contacts.php">Contacts</a></td>
							<td><a href="messagerie_dossiers.php">Dossiers</a></td>
							<td></td>
						</tr>
					</table>
				</div>
			</div>

			<br />

			<div class="row justify-content-center">
				<div class="col-12">
					<table border=1 align="center" cellpadding=2 cellspacing=1 width=100%>

<?php
// recupération des infos sur le message
$sql = "SELECT DISTINCT(message.id_message) as id_mes, expediteur_message, date_message, objet_message
		FROM message, message_perso 
		WHERE expediteur_message='".$pseudo."'
		AND message_perso.id_message = message.id_message
		ORDER BY date_message DESC";
$resultat = $mysqli->query($sql);

if ($resultat->num_rows == 0){
    echo "<tr align=center><td colspan=4>Aucun message envoyé</td></tr>";
}
else {
	
	echo '<tr>';
	echo '	<th style="text-align:center" width="33%">Destinataire</th><th style="text-align:center" width="33%">Date</th><th style="text-align:center" colspan=2>Objet</th>';
	echo '</tr>';
	
	while($row = $resultat->fetch_assoc()) {
		
		$destinataires = "";
		
		$id_mes 		= $row['id_mes'];
		$date_message	= $row["date_message"];
		$objet_message	= $row["objet_message"];
		
		// recupération des destinataires
		$sql_dest = "SELECT nom_perso FROM perso, message_perso WHERE perso.id_perso = message_perso.id_perso AND id_message='$id_mes'";
		$res_dest = $mysqli->query($sql_dest);
		
		while($t_dest = $res_dest->fetch_assoc()){
			
			$nom_dest = $t_dest["nom_perso"];
			
			if(isset($destinataires) && $destinataires != ""){
				$destinataires .= " ; ".$nom_dest;
			}
			else {
				$destinataires = $nom_dest;
			}
		}
	
		echo "<tr>";
		echo "	<td>" . $destinataires . "</td>";
		echo "	<td align='center'>" . $date_message . "</td>";
		echo "	<td colspan=2><a href=message_lire.php?id=" . $id_mes . "&methode=e>" . stripslashes($objet_message) . "</a></td>";
		echo "</tr>";

	}	
}
?>
				</table>
			</div>
		</div>
		
		<!-- Optional JavaScript -->
		<!-- jQuery first, then Popper.js, then Bootstrap JS -->
		<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
		<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
	</body>
</html>
<?php
}
else{
	echo "<font color=red>Vous ne pouvez pas accéder à cette page, veuillez vous loguer.</font>";
}
?>
