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

//creation du contact
if(isset($_POST['creation_contact2'])){
	
	if(isset($_SESSION['nom_contact'])){
		
		$nom_contact 	= addslashes($_SESSION['nom_contact']);
		$noms_contact 	= $_POST['nom_contact2'];
		
		if(filtre_contact($nom_contact) && filtre_contact($noms_contact)){
		
			// verification que le nom n'existe pas déjà pour ce perso
			$sql = "SELECT contact.id_contact FROM contact, perso_as_contact WHERE id_perso='$id' AND contact.id_contact = perso_as_contact.id_contact AND nom_contact ='$nom_contact'";
			$res = $mysqli->query($sql);
			$nb_v = $res->num_rows;
			
			if(!$nb_v){
				
				if(strlen($nom_contact)){
				
					$sql = "INSERT INTO contact (nom_contact, contacts) VALUES ('$nom_contact', '$noms_contact')";
					$mysqli->query($sql);
					$id_c = $mysqli->insert_id;
					
					$sql = "INSERT INTO perso_as_contact VALUES ('$id','$id_c');";
					$mysqli->query($sql);
				}
			}
			else {
				echo "<center><b><font color='red'>Vous possédez déjà un groupe de contact de ce nom, veuillez en choisir un autre</font></b></center>";
			}
		}
	}
	else {
		echo "<center><font color='red'><b>Le nom du contact à été perdu, veuillez recommencer l'opération SVP</b></font></center>";
	}
}

$sql_a_lire = "SELECT id_message FROM message_perso WHERE lu_message='0' AND supprime_message='0' AND id_perso='".$id."'";
$res_a_lire = $mysqli->query($sql_a_lire);
$a_lire = $res_a_lire->num_rows;
?>
		<p align="center"><input type="button" value="Fermer la messagerie" onclick="window.close()"></p>

		<table border=1 align="center" cellpadding=2 cellspacing=1 width=550>
			<tr align="center" bgcolor="#EEEEDD">
				<td><a href="messagerie.php">Messages reçus</a><font color="red"> <?php if($a_lire) echo" (".$a_lire." new)"; ?></font></td>
				<td><a href="message_envoye.php">Messages envoyés</a></td>
				<td><a href="nouveau_message.php">Nouveau message</a></td>
			</tr>
			<tr align="center" bgcolor="#EEEEDD">
				<td>Contacts</td>
				<td><a href="messagerie_dossiers.php">Dossiers</a></td>
				<td></td>
			</tr>
		</table>
		
		<br />

		<center>
			<form method='post' name='creer_contact' action='messagerie_contacts.php'>
				<img src='../images/vcard_add.png' alt='add contact'> Créer un nouveau groupe de contacts : <input type='text' name='nom_contact'>
				<input type='submit' name='creation_contact' value='ok'>
			</form>
		</center>

<?php
//creation du contact : partie 1
if(isset($_POST['creation_contact'])){
	
	// verification que le nom n'existe pas déjà pour ce perso
	$sql = "SELECT contact.id_contact FROM contact, perso_as_contact WHERE id_perso='$id' AND contact.id_contact = perso_as_contact.id_contact AND nom_contact ='".addslashes($_POST['nom_contact'])."'";
	$res = $mysqli->query($sql);
	$nb_v = $res->num_rows;
	
	if(!$nb_v){
		
		$nom_contact = $_SESSION['nom_contact'] = $_POST['nom_contact'];
		
		if(strlen($nom_contact)){
			
			if(filtre_contact($nom_contact)){
			
				echo "<br /><center><b>Création du groupe de contact $nom_contact</b></center><br />";
				echo "<form method='post' name='creer_contact2' action='messagerie_contacts.php'>";
				echo "&nbsp;&nbsp;&nbsp;&nbsp;Liste des noms (ou id) des contacts (séparés par des ';') : <input type='text' name='nom_contact2'>";
				echo "<input type='submit' name='creation_contact2' value='ok'>";
				echo "</form>";
			}
			else {
				echo "<center><b>Nom de groupe de contact incorrect</b></center>";
			}
		}
	}
	else {
		echo "<center><b><font color='red'>Vous possédez déjà un groupe de contact de ce nom, veuillez en choisir un autre</font></b></center>";
	}
}

if(isset($_GET['id_contact']) && $_GET['id_contact'] != ""){
	
	echo "<center><h2>Modifier un contact</h2></center>";

	// vérifier que l'id du contact est un chiffre
	$verif = preg_match("#^[0-9]+$#i",$_GET["id_contact"]);
	
	if($verif){
		
		$id_contact = $_GET['id_contact'];
	
		// vérifier que l'id du contact appartient bien au perso
		$sql = "SELECT id_perso FROM perso_as_contact WHERE id_perso='$id' AND id_contact='$id_contact'";
		$res = $mysqli->query($sql);
		$verif_id = $res->num_rows;
			
		if($verif_id){
			
			if(isset($_GET['action']) && $_GET['action'] == "del"){
				// suppression du groupe de contact
				$sql = "DELETE FROM contact WHERE id_contact='$id_contact'";
				$mysqli->query($sql);
				
				echo "<center><b>Groupe de contacts bien supprimé</b></center>";
			}
			else {
				
				if(isset($_GET['supprime_contact']) && $_GET['supprime_contact'] != ""){
					
					$contact_a_supprime = $_GET['supprime_contact'];
				
					// verification que le perso à supprimé est bien dans le contact
					$sql = "SELECT contacts FROM contact WHERE id_contact='$id_contact'";
					$res = $mysqli->query($sql);
					$t = $res->fetch_assoc();
					
					$contacts = $t['contacts'];
					
					$t_contacts = explode(';',$contacts);
					$taille_contacts = sizeof($t_contacts);
					
					$supprime_ok = 0;
					$new_contacts = "";
					
					for($i = 0; $i < $taille_contacts; $i++){
						if($contact_a_supprime == $t_contacts[$i]){
							$supprime_ok = 1;
						}
						else {
							if($new_contacts == ""){
								$new_contacts .= $t_contacts[$i];
							}
							else {
								$new_contacts .= ";".$t_contacts[$i];
							}
						}
					}
					
					if($supprime_ok){
						$sql = "UPDATE contact SET contacts='$new_contacts' WHERE id_contact='$id_contact'";
						$mysqli->query($sql);
					}
				}
		
				if(isset($_POST['ajout_contacts']) && $_POST['ajout_contacts'] != ""){
					$ajout = addslashes($_POST['ajout_contact']);
					
					if(strlen($ajout)){
					
						if(filtre_contact($ajout)){
						
							$sql = "SELECT contacts FROM contact WHERE id_contact='$id_contact'";
							$res = $mysqli->query($sql);
							$t1 = $res->fetch_assoc();
							$contacts = $t1['contacts'];
							
							if($contacts == ""){
								$new_contacts  = $ajout;
							}
							else {
								$new_contacts = $contacts.";".$ajout;
							}
							
							$sql = "UPDATE contact SET contacts='$new_contacts' WHERE id_contact='$id_contact'";
							$mysqli->query($sql);
						}
						else {
							echo "<center>Un des contacts n'est pas conforme</center>";
						}
					}
				}
		
				//recupération des infos sur le contact
				$sql = "SELECT nom_contact, contacts FROM contact WHERE id_contact='$id_contact'";
				$res = $mysqli->query($sql);
				$t = $res->fetch_assoc();
				
				$nom_contact = stripslashes($t['nom_contact']);
				$contacts = $t['contacts'];
				$t_contacts = explode(';',$contacts);
				$taille_contacts = sizeof($t_contacts);
				
				echo "&nbsp;&nbsp;&nbsp;&nbsp;<b>Groupe de contacts $nom_contact</b> :<br />";
				echo "";
				for($i = 0; $i < $taille_contacts; $i++){
					echo "&nbsp;&nbsp;&nbsp;&nbsp;<a href='messagerie_contacts.php?id_contact=$id_contact&supprime_contact=$t_contacts[$i]'><img src='../images/delete_group.png' alt='supprimer_contact' border='0' /></a>$t_contacts[$i] ";
				}
				echo "<br /><br />";
				
				// faire l'ajout d'un perso dans la liste
				echo "&nbsp;&nbsp;&nbsp;&nbsp;Ajouter un perso ou une liste de persos à ce contact :<br/>";
				echo "<form method='post' name='ajouter_perso' action='messagerie_contacts.php?id_contact=$id_contact'>";
				echo "&nbsp;&nbsp;&nbsp;&nbsp;<input type='text' name='ajout_contact'>";
				echo "<input type='submit' name='ajout_contacts' value='ok'>";
				echo "</form>";
			}
		}
	}
	else {
		echo "<center><b><font color='red'>L'identifiant passé en paramètre n'est pas valide</font></b></center><br />";
	}
	// Ajouter un lien de retour vers la liste des contacts
	echo "<center><a href=\"messagerie_contacts.php\">[retour à la liste des contacts]</a></center>";
}
else {
	echo "<center><h2>Liste des groupes de contacts</h2></center>";
	
	// liste des contacts
	$sql = "SELECT contact.id_contact, nom_contact, contacts FROM contact, perso_as_contact WHERE id_perso='$id' AND contact.id_contact = perso_as_contact.id_contact";
	$res = $mysqli->query($sql);
	$nb_contact = $res->num_rows;
	if($nb_contact){
		while ($t = $res->fetch_assoc()){
			$id_contact = $t['id_contact'];
			$nom_contact = stripslashes($t['nom_contact']);
			$contacts = $t['contacts'];
			
			echo "&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"messagerie_contacts.php?id_contact=$id_contact&action=del\"><img src='../images/deletecell.png' alt='contact' border='0'></a>&nbsp;&nbsp;<a href=\"messagerie_contacts.php?id_contact=$id_contact\"><img src='../images/vcard_write.png' alt='contact' border='0'></a>&nbsp;&nbsp;&nbsp;<a href=\"nouveau_message.php?id_contact=$id_contact\">".$nom_contact."</a><br />";
		}
	}
	else {
		echo "<center><i>Vous ne possédez aucuns contacts</i></center>";
	}
}
?>

	</body>
</html>
<?php
}
else{
	echo "<font color=red>Vous ne pouvez pas accéder à cette page, veuillez vous loguer.</font>";
}

function filtre_contact($chaine)
{
	$lenghtBefore = strlen($chaine);
	
	$caracteres = array(
	"," => "error",
	":" => "error",
	"!" => "error",
	"@" => "error",
	"|" => "error",
	"=" => "error",
	"+" => "error",
	"/" => "error",
	"*" => "error",
	"#" => "error",
	"'" => "error",
	"&" => "error",
	"\\" => "error",
	);
	$chaine = strtr($chaine,$caracteres);
	$lenghtAfter = strlen($chaine);
	
	return !($lenghtBefore < $lenghtAfter);
}
?>