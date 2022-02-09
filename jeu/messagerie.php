<?php
session_start();
require_once("../fonctions.php");

$mysqli = db_connexion();

include ('../nb_online.php');

date_default_timezone_set('Europe/Paris');

if(isset($_GET["envoi"]) && $_GET["envoi"] == 'ok'){
	unset($_SESSION['destinataires']);
	unset($_SESSION['objet']);
	unset($_SESSION['message']);
}

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
		<div class="container-fluid">
		
			<p align="center"><input type="button" value="Fermer la messagerie" onclick="window.close()"></p>
<?php
	$id = $_SESSION["id_perso"];
	
	$sql = "SELECT type_perso FROM perso WHERE id_perso='$id'";
	$res = $mysqli->query($sql);
	$t_p = $res->fetch_assoc();
	
	$type_p = $t_p['type_perso'];
	
	if ($type_p != 6) {

		// recuperation des message dont il est le destinataire
		$sql_a_lire = "SELECT id_message FROM message_perso WHERE lu_message='0' AND supprime_message='0' AND id_perso='".$id."' AND id_dossier='1'";
		$res_a_lire = $mysqli->query($sql_a_lire);
		$a_lire = $res_a_lire->num_rows;
		
		// Requête redondante. Pourquoi ? A vérifier (Magnus)
		$sql_dossier = "SELECT id_message FROM message_perso WHERE lu_message='0' AND supprime_message='0' AND id_perso='".$id."' AND id_dossier!='1'";
		$res_dossier = $mysqli->query($sql_dossier);
		$a_lire_dossier = $res_dossier->num_rows;
?>
			<div class="row justify-content-center">
				<div class="col-12">
					<table border=1 align="center" cellpadding=2 cellspacing=1 width=100%>
						<tr align="center" bgcolor="#EEEEDD">
							<td width="33%">Messages reçus<font color="red"> <?php if($a_lire) echo" (".$a_lire." new)"; ?></font></td>
							<td width="33%"><a href="message_envoye.php">Messages envoyés</a></td>
							<td><a href="nouveau_message.php">Nouveau message</a></td>
						</tr>
						<tr align="center" bgcolor="#EEEEDD">
							<td><a href="messagerie_contacts.php">Contacts</a></td>
							<td><a href="messagerie_dossiers.php">Dossiers<font color="red"> <?php if($a_lire_dossier) echo" (".$a_lire_dossier." new)"; ?></font></a></td>
							<td></td>
						</tr>
					</table>
				</div>
			</div>
			
			<br />

			<div class="row justify-content-center">
				<div class="col-12">
					<form name= "chk" method="post" action="traitement/t_messagerie.php">
						<table border=1 align="center" cellpadding=2 cellspacing=1 width=100%>
<?php
		$sql = "SELECT message.id_message, expediteur_message, UNIX_TIMESTAMP(date_message) as date_message, objet_message, lu_message 
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
			echo '<tr>';
			echo "	<th><input type='checkbox' name='test' onclick='test_chk()'></th>";
			echo "	<th style='text-align:center' width='30%'>Expediteur</th>";
			echo "	<th style='text-align:center' width='33%'>Date</th>";
			echo "	<th style='text-align:center' width='34%' colspan=2>Objet</th>";
			echo "</tr>";
			$i = 0;
			
			while($row = $resultat->fetch_assoc()) {
				
				$id_message		= $row["id_message"];
				$expediteur 	= addslashes($row["expediteur_message"]);
				$date_message	= $row["date_message"];
				$objet_message	= $row["objet_message"];
				
				$date_message = date('Y-m-d H:i:s', $date_message);
				
				$sql_camp = "SELECT id_perso, clan FROM perso WHERE nom_perso='$expediteur'";
				$res_camp = $mysqli->query($sql_camp);
				$t_camp = $res_camp->fetch_assoc();
				
				$camp_perso = $t_camp['clan'];
				$mat_perso	= $t_camp['id_perso'];
				
				if ($camp_perso != null) {
					if ($camp_perso == 1) {
						$color_camp = 'blue';
					}
					else if ($camp_perso == 2) {
						$color_camp = 'red';
					}
					else {
						$color_camp = 'black';
					}
				}
				else {
					$color_camp = 'black';
				}
				
				echo '<tr>';
				echo '	<td><input type="checkbox" id='."'check".$i."'". 'name="id_message[]" value="'.$id_message.'"></td>';
				
				if ($row["lu_message"]){
					echo "	<td><font color='".$color_camp."'>" . stripslashes($expediteur);
					if ($mat_perso != null && $mat_perso != 0) {
						echo " [".$mat_perso."]";
					}
					echo "</font></td>";
					echo "	<td align='center'>" . stripslashes($date_message) . "</td>";
					echo "	<td colspan=2><a href=message_lire.php?id=" . $id_message . "&methode=r>" . stripslashes($objet_message) . "</a></td>";
				}
				else {
					echo "	<td><font color='".$color_camp."'>" . stripslashes($expediteur);
					if ($mat_perso != null && $mat_perso != 0) {
						echo " [".$mat_perso."]";
					}
					echo "</font></td>";
					echo '	<td align="center"><div>' . stripslashes($date_message) . '</div></td>';
					echo '	<td colspan=2><a href=message_lire.php?id=' . $id_message . "&methode=r>" . stripslashes($objet_message) . "</a><b> (non lu)</b></td>";
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
							<td>Que voulez-vous faire des messages sélectionnés ?&nbsp;</td>
							<td><input type="submit" name="submit" value="Effacer" class='btn btn-danger'>&nbsp;</td>
							<td><input type="submit" name="submit" value="Archiver" class='btn btn-warning'></td>
						</tr>
					</table>
				</form>
			</div>
		</div>
<?php
	}
	else {
		echo "<center><font color='red'>Les chiens ne peuvent pas accèder à cette page.</font></center>";
	}
?>
		<br>
		
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
