<?php
session_start();
require_once("../fonctions.php");

$mysqli = db_connexion();

include ('../nb_online.php');

date_default_timezone_set('Europe/Paris');

if(@$_SESSION["id_perso"]){
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
		<div class="container-fluid">
		
			<p align="center"><input type="button" value="Fermer la messagerie" onclick="window.close()"></p>
<?php
	$id = $_SESSION["id_perso"];

	// recuperation du nom du perso
	$sql = "SELECT nom_perso, type_perso FROM perso WHERE id_perso='$id'";
	$res = $mysqli->query($sql);
	$t = $res->fetch_assoc();

	$pseudo = $t["nom_perso"];
	$type_p = $t["type_perso"];
	
	if ($type_p != 6) {

		// nombre de message à lire
		$sql_a_lire = "SELECT id_message FROM message_perso WHERE lu_message='0' AND supprime_message='0' AND id_perso='".$id."'";
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
							<td><a href="messagerie_dossiers.php">Dossiers</a></td>
							<td></td>
						</tr>
					</table>
				</div>
			</div>
		
		<br>
		
		<div class="row justify-content-center">
				<div class="col-12">
					<table border=1 align="center" cellpadding=2 cellspacing=1 width=100%>
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
					$sql = "SELECT expediteur_message, UNIX_TIMESTAMP(date_message) as date_message, objet_message, contenu_message, annonce FROM message, message_perso 
							WHERE message.id_message = message_perso.id_message
							AND message.id_message='" . $id_message . "' 
							AND (id_perso='".$id."' OR expediteur_message='".$pseudo."')";
					$resultat = $mysqli->query($sql);
					$tab = $resultat->fetch_assoc();
					
					$annonce = $tab['annonce'];
					
					if (!$annonce) {
					
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
					}
					else {
						$destinataires = " -- ";
					}
					
					$date_message = $tab["date_message"];
					
					$date_message = date('Y-m-d H:i:s', $date_message);
					
					echo '	<tr class="exp"><td><b>Expediteur :</b> ' . $tab["expediteur_message"] . "</td><td><b>Date de l'envoi :</b> " . $date_message . "</td></tr>";
					echo '	<tr class="exp"><td colspan=2><b>Destinataires :</b> '.$destinataires.'</td></tr>';
					echo "</table><br />";
					echo "<table border=1 align='center' cellpadding=2 cellspacing=1 width=100%>";
					echo '	<tr class="titrel"><td colspan=2><center><b>Objet :</b> ' . stripslashes($tab["objet_message"]) . "</center></td></tr>";
					echo '	<tr class="messl"><td colspan=2>';
					
					if ($tab["contenu_message"] == ""){ 
						echo "<i>Message vide</i>"; 
					}
					else{ 
						echo bbcode(htmlentities(stripslashes($tab["contenu_message"])));
					}
					
					echo "		</td>";
					echo "	</tr>";
					echo "</table>";
					
					if ($methode == "r"){
						echo '<form method="post" action="traitement/t_lire.php">';
						echo '	<input type="hidden" name="id_message" value="' . $id_message . '">';
						
						// Message suivant
						$sql_suivant = "SELECT message.id_message
								FROM message, message_perso 
								WHERE id_perso='$id' 
								AND message_perso.id_message = message.id_message
								AND id_dossier='1'
								AND supprime_message='0'
								AND date_message > (SELECT date_message FROM message WHERE id_message='$id_message')
								ORDER BY date_message ASC LIMIT 1";
						$res_suivant = $mysqli->query($sql_suivant);
						$t_suivant = $res_suivant->fetch_assoc();
						
						$id_message_suivant = $t_suivant['id_message'];
						
						// Message precedent
						$sql_prec = "SELECT message.id_message
								FROM message, message_perso 
								WHERE id_perso='$id' 
								AND message_perso.id_message = message.id_message
								AND id_dossier='1'
								AND supprime_message='0'
								AND date_message < (SELECT date_message FROM message WHERE id_message='$id_message')
								ORDER BY date_message DESC LIMIT 1";
						$res_prec = $mysqli->query($sql_prec);
						$t_prec = $res_prec->fetch_assoc();
						
						$id_message_prec = $t_prec['id_message'];
						
						echo '	<div align="center">';
						if (isset($id_message_prec)) {
							echo "		<a href='message_lire.php?id=".$id_message_prec."&methode=r' title='message précédent' class='btn btn-info'> << </a> &nbsp&nbsp"; 
						}
						
						if (!$annonce) {
							echo '		<input type="submit" name="submit" value="Répondre" class="btn btn-primary"> &nbsp&nbsp'; 
							echo '		<input type="submit" name="submit" value="Répondre à tous" class="btn btn-primary"> &nbsp&nbsp';
							echo '		<input type="submit" name="submit" value="Transférer" class="btn btn-primary"> &nbsp&nbsp'; 
						}
						echo '		<input type="submit" name="submit" value="Effacer" class="btn btn-danger">&nbsp&nbsp';
						
						if (isset($id_message_suivant)) {
							echo "		<a href='message_lire.php?id=".$id_message_suivant."&methode=r' title='message suivant' class='btn btn-info'> >> </a>"; 
						}
						
						echo '	</div>';
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
	}
	else {
		echo "<center><font color='red'>Les chiens ne peuvent pas accèder à cette page.</font></center>";
	}
?>		
				</div>
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
