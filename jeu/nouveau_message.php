<?php
session_start();
require_once("../fonctions.php");
require_once("f_carte.php");

$mysqli = db_connexion();

include ('../nb_online.php');

if(@$_SESSION["id_perso"]){

$id_perso = $_SESSION["id_perso"];

// recuperation du nom du perso
$sql = "SELECT nom_perso FROM perso WHERE id_perso='$id_perso'";
$res = $mysqli->query($sql);
$t = $res->fetch_assoc();
$pseudo = $t["nom_perso"];

unset($_SESSION['destinataires']);

if(isset($_POST["envoyer"])) {
	if (trim($_POST["destinataire"]) == ""){
		echo "<div class=\"info\">Vous devez obligatoirement renseigner le destinataire du message</div>";
	}
	else {
		if(trim($_POST["objet"]) == ""){
			$destinataire = $_POST["destinataire"];
			$dest = explode(";",$destinataire);
			$nbdest = count($dest);
			
			$expediteur = $pseudo;
			$message = addslashes($_POST["message"]) ;
			$objet = "(Sans objet)" ;
			
			// creation du message
			$sql = "INSERT INTO message (expediteur_message, date_message, contenu_message, objet_message) VALUES ('" . $expediteur . "', NOW(), '" . $message. "', '" . $objet. "')";
			$mysqli->query($sql);
			$id_message = $mysqli->insert_id;
			
			for ($i = 0; $i < $nbdest; $i++) {
				
				// recupération du nom du perso destinataire
				$sql_d = "SELECT nom_perso FROM perso WHERE id_perso='".addslashes($dest[$i])."' OR nom_perso='".addslashes($dest[$i])."'";
				$res_d = $mysqli->query($sql_d);
				
				if( $res_d->num_rows == 0 ){ 
					echo "<div class=\"erreur\">Le destinataire n'existe pas !</div>";
					$_SESSION['destinataires'] .= $dest[$i].";";
					$_SESSION['message'] = $_POST["message"];
					$_SESSION['objet'] = "(Sans objet)";
				}
				else {
					$sql_p = "SELECT id_perso FROM perso WHERE nom_perso='".addslashes($dest[$i])."' OR id_perso='".addslashes($dest[$i])."'";
					$res_p = $mysqli->query($sql_p);
					$t_p = $res_p->fetch_assoc();
					$id_p = $t_p['id_perso'];
				
					// assignation du message au perso
					$sql = "INSERT INTO message_perso VALUES ('$id_message', '$id_p', '1', '0', '0')";
					$mysqli->query($sql);

					header("Location:messagerie.php?envoi=ok");
				}
			}
		}
		else {
			$destinataire = $_POST["destinataire"];
			$dest = explode(";",$destinataire);
			$nbdest = count($dest);
			
			$expediteur = $pseudo;
			$message = addslashes($_POST["message"]) ;
			$objet = htmlentities(addslashes($_POST["objet"])) ;
			
			// creation du message
			$sql = "INSERT INTO message (expediteur_message, date_message, contenu_message, objet_message) VALUES ('" . $expediteur . "', NOW(), '" . $message. "', '" . $objet. "')";
			$mysqli->query($sql);
			$id_message = $mysqli->insert_id;
			
			for ($i = 0; $i < $nbdest; $i++) {
				
				// recupération du nom du perso destinataire
				$sql_d = "SELECT nom_perso FROM perso WHERE id_perso='".addslashes($dest[$i])."' OR nom_perso='".addslashes($dest[$i])."'";
				$res_d = $mysqli->query($sql_d);
				
				if( $res_d->num_rows == 0 ){
					echo "<div class=\"erreur\">Le destinataire n'existe pas !</div>";
					
					if(isset($_SESSION['destinataires'])){
						$_SESSION['destinataires'] .= ";".$dest[$i];
					}
					else {
						$_SESSION['destinataires'] = $dest[$i];
					}
					$_SESSION['message'] = $_POST["message"];
					$_SESSION['objet'] = $_POST["objet"];
				}
				else {
					$sql_p = "SELECT id_perso FROM perso WHERE nom_perso='".addslashes($dest[$i])."' OR id_perso='".addslashes($dest[$i])."'";
					$res_p = $mysqli->query($sql_p);
					$t_p = $res_p->fetch_assoc();
					$id_p = $t_p['id_perso'];
				
					// assignation du message au perso
					$sql = "INSERT INTO message_perso VALUES ('$id_message', '$id_p', '1', '0', '0')";
					$mysqli->query($sql);
					
					header("Location:messagerie.php?envoi=ok");
				}
			}
		}
	}
}

if(isset($_GET["id"])) {
	
	$verif = preg_match("#^[0-9]+$#i",$_GET["id"]);
	
	if($verif){
		
		$id_message = $_GET["id"];

		// verif identité joueur qui veut lire le message
		// si il est destinataire
		$sql = "SELECT id_perso FROM message_perso WHERE id_perso='$id_perso' AND id_message='$id_message'";
		$res = $mysqli->query($sql);
		$verif_id = $res->num_rows;
		
		if($verif_id){
	
			$sql_message = "SELECT * FROM message WHERE id_message ='" . $_GET["id"] . "'";
			$res_message = $mysqli->query($sql_message);
			$tabMess = $res_message->fetch_assoc();
			
			if(isset($_GET["rep"]) && $_GET["rep"] == '1'){
			
				// recupération des destinataires en s'excluant
				$sql_dest = "SELECT id_perso FROM message_perso WHERE id_message='" . $_GET["id"] . "' AND id_perso!='$id_perso'";
				$res_dest = $mysqli->query($sql_dest);
				$num = $res_dest->num_rows;
				if($num == '0'){
					$destinataires = $tabMess["expediteur_message"];
				}
				else {
					while($t_dest = $res_dest->fetch_assoc()){
						$id_dest = $t_dest["id_perso"];
						
						// recup du nom
						$sql_n = "SELECT nom_perso FROM perso WHERE id_perso='$id_dest'";
						$res_n = $mysqli->query($sql_n);
						$t_n = $res_n->fetch_assoc();
						
						if(isset($destinataires) && $destinataires != ""){
							if ($tabMess["expediteur_message"] != $t_n["nom_perso"]){
								$destinataires .= ";".$t_n["nom_perso"];
							}
							else {
								$destinataires .= ";".$t_n["nom_perso"];
							}
						}
						else {
							if($tabMess["expediteur_message"] != $t_n["nom_perso"]){
								$destinataires = $tabMess["expediteur_message"].";".$t_n["nom_perso"];
							}
							else {
								$destinataires = $t_n["nom_perso"];
							}
						}
					}
				}
			}
		}
	}
}

if(isset($_GET["visu"]) && $_GET["visu"] == "ok"){
	
	$sql = "SELECT x_perso, y_perso, perception_perso FROM perso WHERE id_perso='$id_perso'";
	$res = $mysqli->query($sql);
	$t = $res->fetch_assoc();
	
	$x = $t["x_perso"];
	$y = $t["y_perso"];
	$perc = $t["perception_perso"];
	
	$res_visu = get_persos_visu($mysqli, $x, $y, $perc, $id_perso);
	$tv =  $res_visu->fetch_assoc();
	$visu = $tv["nom_perso"];
	
	while ($tv = $res_visu->fetch_assoc()){
			$visu .=";".$tv["nom_perso"];
	}
}

if(isset($_GET["id_section"])) {
	$id_section = $_GET["id_section"];
	//-- TODO -- //
	// Sécurité : verif $id_section correct
	//                   verif identité joueur qui veut envoyer le message => fait-il bien partie de la section ?
	// -- TODO -- //
	
	if(isset($_POST["contenu"])) {
		$contenu = $_POST["contenu"];
	}
	else {
		$contenu = "";
	}
	
	// recuperation des persos de la section
	$sql = "SELECT nom_perso FROM perso, perso_in_section WHERE perso.id_perso=perso_in_section.id_perso AND attenteValidation_section='0' AND id_section='$id_section'";
	$res = $mysqli->query($sql);
	
	$dest = "";
	while ($nom = $res->fetch_assoc()) {
		$dest .= $nom["nom_perso"].";";
	}
}

if(isset($_GET['id_contact'])){
	
	$dest_contact = "";
	$verif = preg_match("#^[0-9]+$#i",$_GET["id_contact"]);
	
	if($verif){
		
		$id_contact = $_GET["id_contact"];

		// verif identité joueur qui veut utiliser le contact
		$sql = "SELECT id_perso FROM perso_as_contact WHERE id_perso='$id_perso' AND id_contact='$id_contact'";
		$res = $mysqli->query($sql);
		$verif_id = $res->num_rows;
		
		if($verif_id){
			// récupération des contacts
			$sql = "SELECT contacts FROM contact WHERE id_contact='$id_contact'";
			$res = $mysqli->query($sql);
			$t = $res->fetch_assoc();
			$dest_contact = $t['contacts'];
		}
	}
}

$sql_a_lire = "SELECT id_message FROM message_perso WHERE lu_message='0' AND supprime_message='0' AND id_perso='".$id_perso."'";
$res_a_lire = $mysqli->query($sql_a_lire);
$a_lire = $res_a_lire->num_rows;
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Messagerie</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../style.css" rel="stylesheet" type="text/css">
</head>
<body>

<table border=1 align="center" cellpadding=2 cellspacing=1 width=550>
<tr align="center" bgcolor="#EEEEDD">
<p align="center"><input type="button" value="Fermer la messagerie" onclick="window.close()"></p>
	<td><a href="messagerie.php">Messages reçus</a><font color="red"> <?php if($a_lire) echo" (".$a_lire." new)"; ?></font></td>
	<td><a href="message_envoye.php">Messages envoyés</a></td>
	<td>Nouveau message</td>
</tr>
<tr align="center" bgcolor="#EEEEDD">
	<td><a href="messagerie_contacts.php">Contacts</a></td>
	<td><a href="messagerie_dossiers.php">Dossiers</a></td>
	<td></td>
</tr>
</table>

<br>
<?php
if(!isset($_GET["id"]) || (isset($_GET["id"]) && $verif && $verif_id)){
?>
	<form method="post" action="">
	<table border=1 align="center" cellpadding=2 cellspacing=1 width=550>
	<tr class="messl"><td>Destinataire : </td> <td colspan=3><input type="text" name="destinataire" size=30 
	<?php 
	if(isset($_SESSION['destinataires'])){
		echo 'value="'.$_SESSION['destinataires'].'"';
	} 
	if(isset($_GET["pseudo"])){ 
		echo 'value="'.$_GET["pseudo"].'"';
	}
	if(isset($_GET["id"])){
		if(isset($_GET["rep"]) && $_GET["rep"] == '1'){
			echo 'value="'.$destinataires.'"';
		}
		else {	
			echo 'value="'.$tabMess["expediteur_message"].'"';
		}
	}	
	if(isset($_GET["visu"])){
		echo 'value="'.$visu.'"';
	}
	if(isset($_GET["id_section"])){
		echo 'value="'.$dest.'"';
	}
	if(isset($_GET['id_contact'])){
		echo 'value="'.$dest_contact.'"';
	}?> ></td></tr>
	<tr class="messl"><td>Objet : </td> <td colspan=3><input type="text" name="objet" size=30
	<?php 
	if(isset($_SESSION['objet'])){
		echo 'value="'.$_SESSION['objet'].'"';
	} 
	if(isset($_GET["id"])){ 
		echo 'value="Re: '.stripslashes($tabMess["objet_message"]).'"';
	}
	if(isset($_GET["id_section"])){
		echo 'value="message du chef de la section"';
	}?>></td></tr>
	<tr class="messl"><td>Message : </td> <td colspan=3 align="center"><TEXTAREA NAME="message" rows=15 cols=50 >
<?php
	if(isset($_SESSION['message'])){
		echo $_SESSION['message'];
	} 
	if(isset($_GET["id"])) {
		echo "\n\n****************************\n".stripslashes($tabMess["expediteur_message"])." wrote :\n****************************\n"; 
		echo stripslashes($tabMess["contenu_message"]);
	}
	if(isset($_GET["id_section"])){
		echo "".stripslashes($contenu);
	} ?></TEXTAREA></td></tr>
	</table>
	<div align="center"><INPUT TYPE="SUBMIT" name="envoyer" VALUE="envoyer"></div>
	</form>
<?php
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
