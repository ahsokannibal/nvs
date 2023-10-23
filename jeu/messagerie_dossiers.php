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
	
			<p align="center"><a href="jouer.php"> <input type="button" value="Retour au jeu"> </a></p>
<?php
	$id = $_SESSION["id_perso"];
	
	$sql = "SELECT type_perso FROM perso WHERE id_perso='$id'";
	$res = $mysqli->query($sql);
	$t_p = $res->fetch_assoc();
	
	$type_p = $t_p['type_perso'];
	
	if ($type_p != 6) {

		//creation de dossier
		if(isset($_POST['creation_dossier'])){
			
			$nom_dossier = $_POST['nom_dossier'];
			
			if(filtre($nom_dossier,0,25) && $nom_dossier != ''){
				
				// creation du dossier
				$sql = "INSERT INTO dossier (nom_dossier) VALUES ('$nom_dossier')";
				$mysqli->query($sql);
				$id_dossier_cree = $mysqli->insert_id;
				
				// affectation du dossier au perso
				$sql = "INSERT INTO perso_as_dossiers VALUES ('$id','$id_dossier_cree')";
				$mysqli->query($sql);
			}
			else {
				echo "<center><font color='red'>Nom de dossier vide ou incorrect, veuillez en choisir un autre</font></center>";
			}
		}

		// recuperation du nom du perso
		$sql = "SELECT nom_perso, clan FROM perso WHERE id_perso='$id'";
		$res = $mysqli->query($sql);
		$t = $res->fetch_assoc();

		$pseudo = $t["nom_perso"];
		$camp 	= $t["clan"];

		if($camp == "1"){
			$couleur = "blue";
		}
		if($camp == "2"){
			$couleur = "red";
		}
		if($camp == "3"){
			$couleur = "purple";
		}

		$image_dossier_ouvert = "folder_ouvert_".$couleur.".png";
		$image_dossier_ferme = "folder_ferme_".$couleur.".png";
		$id_dossier = '1';

		if(isset($_GET["id_dossier"])){
			$dossier = $_GET["id_dossier"];
		}

		$sql_a_lire = "SELECT id_message FROM message_perso WHERE lu_message='0' AND supprime_message='0' AND id_perso='".$id."' AND id_dossier='1'";
		$res_a_lire = $mysqli->query($sql_a_lire);
		$a_lire = $res_a_lire->num_rows;
?>
			<div class="row justify-content-center">
				<div class="col-12">
					<table border=1 align="center" cellpadding=2 cellspacing=1 width=100%>
						<tr align="center" bgcolor="#EEEEDD">
							<td width="33%"><a href="messagerie.php">Messages reçus</a><font color="red"> <?php if($a_lire) echo" (".$a_lire." new)"; ?></font></td>
							<td width="33%"><a href="message_envoye.php">Messages envoyés</a></td>
							<td><a href="nouveau_message.php">Nouveau message</a></td>
						</tr>
						<tr align="center" bgcolor="#EEEEDD">
							<td><a href="messagerie_contacts.php">Contacts</a></td>
							<td>Dossiers</td>
							<td></td>
						</tr>
					</table>
				</div>
			</div>
			
			<br />

			<center>
				<form method='post' name='creer_dossier' action='messagerie_dossiers.php'>
					<img src='../images/folder_add.png' alt='add folder'> Créer un nouveau dossier : <input type='text' name='nom_dossier'>
					<input type='submit' name='creation_dossier' value='ok'>
				</form>
			</center>

<?php
		$sql = "SELECT dossier.id_dossier, dossier.nom_dossier FROM dossier, perso_as_dossiers WHERE dossier.id_dossier=perso_as_dossiers.id_dossier AND id_perso='$id'";
		$res = $mysqli->query($sql);

		while ($t = $res->fetch_assoc()){
			
			$id_dossier 	= $t["id_dossier"];
			$nom_dossier 	= $t["nom_dossier"];
			
			$sql_dossier = "SELECT id_message FROM message_perso WHERE lu_message='0' AND supprime_message='0' AND id_perso='".$id."' AND id_dossier='$id_dossier'";
			$res_dossier = $mysqli->query($sql_dossier);
			$a_lire_dossier = $res_dossier->num_rows;
			
			if(isset($_GET["id_dossier"])){
				
				if($dossier == $id_dossier){
					
					echo "&nbsp;&nbsp;&nbsp;&nbsp;<a href='messagerie_dossiers.php'><img src=\"../images/$image_dossier_ouvert\" alt=\"dossier\" border='0' width='32' height='32'></a><b> Dossier $nom_dossier</b>";
					
					if ($a_lire_dossier) {
						echo "<font color='red'> (".$a_lire_dossier." new)</font>";
					}
					
					echo "<form name='chk' method='post' action='traitement/t_messagerie.php'>";
					echo "<table border=1 align='center' cellpadding=2 cellspacing=1 width=100%>";
					
					$sql = "SELECT message.id_message, expediteur_message, date_message, objet_message, lu_message 
							FROM message, message_perso 
							WHERE id_perso='".$id."' 
							AND message_perso.id_message = message.id_message
							AND id_dossier='$id_dossier'
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
					echo "</table>";
					
					echo "<br>";
					echo "<table border=0 align=\"center\">";
					echo "	<tr>";
					echo "		<td>Que voulez-vous faire des messages sélectionnés?&nbsp;</td>";
					echo "		<td><input type=\"submit\" name=\"submit\" value=\"Effacer\">&nbsp;</td>";
					if ($id_dossier != 2) {
						echo "		<td><input type=\"submit\" name=\"submit\" value=\"Archiver\"></td>";
					}
					echo "	</tr>";
					echo "</table>";
					
					echo "</form>";
				}
				else {
					echo "&nbsp;&nbsp;&nbsp;&nbsp;<a href='messagerie_dossiers.php?id_dossier=$id_dossier'><img src=\"../images/$image_dossier_ferme\" alt=\"dossier\" border='0' width='32' height='32'></a><b> Dossier $nom_dossier</b>";
					if ($a_lire_dossier) {
						echo "<font color='red'> (".$a_lire_dossier." new)</font>";
					}
					echo "<br />";
				}
			}
			else {
				echo "&nbsp;&nbsp;&nbsp;&nbsp;<a href='messagerie_dossiers.php?id_dossier=$id_dossier'><img src=\"../images/$image_dossier_ferme\" alt=\"dossier\" border='0' width='32' height='32'></a><b> Dossier $nom_dossier</b>";
				if ($a_lire_dossier) {
					echo "<font color='red'> (".$a_lire_dossier." new)</font>";
				}
				echo "<br />";
			}
		}
	}
	else {
		echo "<center><font color='red'>Les chiens ne peuvent pas accèder à cette page.</font></center>";
	}
?>
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