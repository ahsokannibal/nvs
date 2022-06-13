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
		<div class="container-fluid">
	
			<p align="center"><a href="jouer.php"> <input type="button" value="Retour au jeu"> </a></p>
<?php
	$id = $_SESSION["id_perso"];

	// recuperation du nom du perso
	$sql = "SELECT nom_perso, clan, type_perso FROM perso WHERE id_perso='$id'";
	$res = $mysqli->query($sql);
	$t = $res->fetch_assoc();

	$pseudo = $t["nom_perso"];
	$camp 	= $t["clan"];
	$type_p = $t["type_perso"];
	
	if ($type_p != 6) {

		if($camp == "1"){
			$couleur = "blue";
		}
		if($camp == "2"){
			$couleur = "red";
		}
		if($camp == "3"){
			$couleur = "purple";
		}

		function nettoyer_contact($noms_contact) {
			
			$contacts_retour = "";
			
			$t_contact = explode(";",$noms_contact);
			
			foreach($t_contact as $contact) {
				
				if (trim($contact) != "") {
					if ($contacts_retour != "") {
						$contacts_retour .= ";";
					}
					
					$contacts_retour .= $contact;
				}
			}
			
			return $contacts_retour;
			
		}

		if (isset($_GET['nettoyer']) && $_GET['nettoyer'] != "") {
			
			$id_contact = $_GET['nettoyer'];
			
			$verif1 = preg_match("#^[0-9]*[0-9]$#i","$id_contact");
			
			if ($verif1) {
			
				$sql = "SELECT * FROM perso_as_contact WHERE id_contact='$id_contact'";
				$res = $mysqli->query($sql);
				$verif2 = $res->num_rows;
				
				if ($verif2) {
					
					$sql = "SELECT contacts FROM contact WHERE id_contact='$id_contact'";
					$res = $mysqli->query($sql);
					$t = $res->fetch_assoc();
					
					$contacts = $t['contacts'];
					
					$contacts_net = nettoyer_contact($contacts);
					
					$sql = "UPDATE contact SET contacts='$contacts_net' WHERE id_contact='$id_contact'";
					$mysqli->query($sql);
					
					echo "<center><b><font color='blue'>Contacts nettoyés</font></b></center>";
					
				}
				else {
					// Tentative de triche
				
					echo "<center><b><font color='red'>Paramètre invalide</font></b></center>";
				}
			}
			else {
				// Tentative de triche
				
				echo "<center><b><font color='red'>Paramètre invalide</font></b></center>";
			}
		}

		//creation du contact
		if(isset($_POST['creation_contact2'])){
			
			if(isset($_SESSION['nom_contact'])){
				
				$nom_contact 	= addslashes($_SESSION['nom_contact']);
				$noms_contact 	= nettoyer_contact($_POST['nom_contact2']);
				
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

		$sql_dossier = "SELECT id_message FROM message_perso WHERE lu_message='0' AND supprime_message='0' AND id_perso='".$id."' AND id_dossier!='1'";
		$res_dossier = $mysqli->query($sql_dossier);
		$a_lire_dossier = $res_dossier->num_rows;
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
							<td>Contacts</td>
							<td><a href="messagerie_dossiers.php">Dossiers<font color="red"> <?php if($a_lire_dossier) echo" (".$a_lire_dossier." new)"; ?></font></a></td>
							<td></td>
						</tr>
					</table>
				</div>
			</div>
			
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
						
						echo "&nbsp;&nbsp;&nbsp;&nbsp;<b>Groupe de contacts $nom_contact</b> : <a href='messagerie_contacts.php?nettoyer=".$id_contact."' class='btn btn-primary'>Nettoyer ce groupe de contact</a><br />";
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
			
			echo "<center><a href=\"messagerie_contacts.php\" class='btn btn-primary'>retour à la liste des contacts</a></center>";
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