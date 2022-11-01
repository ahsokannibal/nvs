<?php
session_start();
require_once("../fonctions.php");
require_once("f_carte.php");

$mysqli = db_connexion();

include ('../nb_online.php');

if(isset($_SESSION["id_perso"])){

	$id_perso = $_SESSION["id_perso"];

	// recuperation du nom du perso
	$sql = "SELECT nom_perso, type_perso, idJoueur_perso FROM perso WHERE id_perso='$id_perso'";
	$res = $mysqli->query($sql);
	$t = $res->fetch_assoc();
	
	$pseudo 		= $t["nom_perso"];
	$type_p 		= $t["type_perso"];
	$id_joueur_p	= $t["idJoueur_perso"];
	
	if ($type_p != 6) {

		unset($_SESSION['destinataires']);

		/**
		  * Fonction qui envoi une copie du mp par mail
		  * @return void
		  */
		function mail_mp($mysqli, $expediteur, $objet, $message, $id_perso_destinataire){
			
			// Recuperation du mail du destinataire
			$sql = "SELECT email_joueur, nom_perso FROM joueur, perso WHERE id_perso='$id_perso_destinataire' AND id_joueur=idJoueur_perso";
			$res = $mysqli->query($sql);
			$t = $res->fetch_assoc();
			
			// Destinataire du mail
			$destinataire 	= $t['email_joueur'];
			$nom_perso		= $t['nom_perso'];
			
			$headers[] = 'MIME-Version: 1.0';
			$headers[] = 'Content-type: text/html; charset=utf-8';
			$headers[] = 'To: '.$nom_perso.' <'.$destinataire.'>';
			$headers[] = 'From: Nord VS Sud"<nordvssud@no-reply.fr>';
			$headers[] = 'Reply-To: nordvssud@no-reply.fr';
			
			// Titre du mail
			$titre = "Votre personnage $nom_perso a reçu un MP de $expediteur";
			
			// Contenu du mail
			$message = "Objet du message : $objet <br>Message : ".bbcode(stripslashes($message));
			
			// Envoie du mail
			mail($destinataire, $titre, $message, implode("\r\n", $headers));
		}

		if(isset($_POST["envoyer"])) {
			if (trim($_POST["destinataire"]) == ""){
				echo "<div class=\"info\">Vous devez obligatoirement renseigner le destinataire du message</div>";
			}
			else {
				if(trim($_POST["objet"]) == ""){
					$objet = "(Sans objet)";
				}
				else {
					$objet = htmlentities(addslashes($_POST["objet"]));
				}
				
				$destinataire = $_POST["destinataire"];
				$dest = explode(";",$destinataire);
				$nbdest = count($dest);
				
				$expediteur = $pseudo;
				$message = addslashes($_POST["message"]);
				
				$lock = "LOCK TABLE message WRITE";
				$mysqli->query($lock);
				
				// creation du message
				$sql = "INSERT INTO message (id_expediteur, expediteur_message, date_message, contenu_message, objet_message) 
						VALUES ('" . $id_perso . "', '" . $expediteur . "', NOW(), '" . $message. "', '" . $objet. "')";
				$mysqli->query($sql);
				$id_message = $mysqli->insert_id;
				
				$unlock = "UNLOCK TABLES";
				$mysqli->query($unlock);

				if (!$id_message) {
					echo "<div class=\"erreur\">Une erreur s'est produite.</div>";
				} else {
				for ($i = 0; $i < $nbdest; $i++) {
					
					// recupération du nom du perso destinataire
					$nom = filter_var($dest[$i], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
					$sql_d = "SELECT nom_perso FROM perso WHERE id_perso='".$nom."' OR nom_perso='".$nom."'";
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
						$sql_p = "SELECT id_perso, idJoueur_perso FROM perso WHERE nom_perso='".$nom."' OR id_perso='".$nom."'";
						$res_p = $mysqli->query($sql_p);
						$t_p = $res_p->fetch_assoc();
						
						$id_p = $t_p['id_perso'];
						$id_j = $t_p['idJoueur_perso'];

						// assignation du message au perso
						$sql = "INSERT INTO message_perso VALUES ('$id_message', '$id_p', '1', '0', '0', '0')";
						$mysqli->query($sql);
						
						// On récupère la config envoi_mail_mp du joueur 
						$sql = "SELECT mail_mp FROM joueur WHERE id_joueur='$id_j'";
						$res = $mysqli->query($sql);
						$t_j = $res->fetch_assoc();
						
						$envoi_mail_mp = $t_j['mail_mp'];
						
						if ($envoi_mail_mp) {
							
							// Envoi d'une copie du message par mail 
							mail_mp($mysqli, $expediteur, $objet, $message, $id_p);
							
						}
						
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
			
					unset($_SESSION['message']);
					unset($message);
					unset($_SESSION['objet']);
					unset($message);
					unset($_SESSION['destinataires']);
					unset($destinataires);
			
					$sql_message = "SELECT * FROM message WHERE id_message ='" . $_GET["id"] . "'";
					$res_message = $mysqli->query($sql_message);
					$tabMess = $res_message->fetch_assoc();
					
					if (isset($_GET['transfert']) && $_GET['transfert'] == 1) {
						
					}
					else if(isset($_GET["rep"]) && $_GET["rep"] == '1'){
					
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
			
			$visu = "";
			
			$sql = "SELECT x_perso, y_perso, perception_perso FROM perso WHERE id_perso='$id_perso'";
			$res = $mysqli->query($sql);
			$t = $res->fetch_assoc();
			
			$x = $t["x_perso"];
			$y = $t["y_perso"];
			$perc = $t["perception_perso"];
			
			// recuperation des fonds
			$sql = "SELECT fond_carte, image_carte, image_carte FROM carte WHERE x_carte='$x' AND y_carte='$y'";
			$res_map = $mysqli->query ($sql);
			$t_carte = $res_map->fetch_assoc();
			
			$fond = $t_carte["fond_carte"];
			
			$bonus_visu = get_malus_visu($fond) + getBonusObjet($mysqli, $id_perso);
			
			// Bonus visu Tour de guet
			if (in_bat($mysqli, $id_perso)) {
				
				$instance_bat = in_bat($mysqli, $id_perso);
				
				$sql = "SELECT id_batiment FROM instance_batiment WHERE id_instanceBat='$instance_bat'";
				$res = $mysqli->query ($sql);
				$t = $res->fetch_assoc();
				
				$id_bat = $t['id_batiment'];
				
				if ($id_bat == 2) {
					$bonus_visu += 5;
				}
			}
			
			$perc_finale = $perc + $bonus_visu;
			
			if (isset($_GET["camp"]) && trim($_GET["camp"]) != '' && ($_GET["camp"] == "1" || $_GET["camp"] == "2")) {
				
				$camp = $_GET["camp"];
				
				$res_visu = get_persos_visu_camp($mysqli, $x, $y, $perc_finale, $id_perso, $camp, $id_joueur_p);
			}
			else {
				$res_visu = get_persos_visu($mysqli, $x, $y, $perc_finale, $id_perso, $id_joueur_p);
			}
			
			$tab_chef_visu = array();
			
			while ($tv = $res_visu->fetch_assoc()){
				
				$idJoueur_p_v	= $tv["idJoueur_perso"];
				$chef_v			= $tv["chef"];
				$nom_perso_v	= $tv["nom_perso"];
				
				if (!in_array($idJoueur_p_v, $tab_chef_visu)) {
					if (trim($visu) == "") {
						$visu .= $nom_perso_v;
					}
					else {
						$visu .=";".$nom_perso_v;
					}
				}
				
				if ($chef_v) {
					array_push($tab_chef_visu, $idJoueur_p_v);
				}
			}
		}

		if(isset($_GET["id_compagnie"])) {
			
			$id_compagnie = $_GET["id_compagnie"];
			
			$verif_id_compagnie = preg_match("#^[0-9]+$#i", $id_compagnie);
			
			if ($verif_id_compagnie) {
				
				// Recupération compagnie du perso
				$sql = "SELECT id_compagnie FROM perso_in_compagnie WHERE id_perso='$id_perso'";
				$res = $mysqli->query($sql);
				$t_c = $res->fetch_assoc();
				
				$id_compagnie_p = $t_c['id_compagnie'];
				
				if ($id_compagnie_p == $id_compagnie) {
			
					if(isset($_POST["contenu"])) {
						$contenu = $_POST["contenu"];
					}
					else {
						$contenu = "";
					}
					
					// recuperation des persos de la compagnie
					$sql = "SELECT perso.nom_perso, perso.chef, perso.idJoueur_perso FROM perso, perso_in_compagnie 
							WHERE perso.id_perso=perso_in_compagnie.id_perso 
							AND (attenteValidation_compagnie='0' OR attenteValidation_compagnie='2') AND id_compagnie='$id_compagnie'
							ORDER BY perso.id_perso ASC";
					$res = $mysqli->query($sql);
					
					$tab_chef_compagnie = array();
					
					$dest = "";
					while ($nom = $res->fetch_assoc()) {
						
						$nom_perso_c 	= $nom["nom_perso"];
						$chef_c			= $nom["chef"];
						$idJoueur_p_c	= $nom["idJoueur_perso"];
						
						if (!in_array($idJoueur_p_c, $tab_chef_compagnie)) {
							if (trim($dest) == "") {
								$dest .= $nom_perso_c;
							}
							else {
								$dest .=";".$nom_perso_c;
							}
						}
						
						if ($chef_c) {
							array_push($tab_chef_compagnie, $idJoueur_p_c);
						}
					}
				}
				else {
					// Tentative de triche
					echo "<br /><center><font color='red'><b>Pas bien d'essayer de tricher !</b></font></center>";
					
					$text_triche = "Tentative triche envoi message compagnie pas la sienne !";
					
					$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id_perso', '$text_triche')";
					$mysqli->query($sql);
					
					$dest 		= "";
					$contenu 	= "";
				}
			}
			else {
				// Tentative de triche
				echo "<br /><center><font color='red'><b>Pas bien d'essayer de tricher !</b></font></center>";
				
				$text_triche = "Tentative triche envoi message id compagnie incorrect";
				
				$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id_perso', '$text_triche')";
				$mysqli->query($sql);
				
				$dest 		= "";
				$contenu 	= "";
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

		// Nombre de messages non lus
		$sql_a_lire = "SELECT id_message FROM message_perso WHERE lu_message='0' AND supprime_message='0' AND id_perso='".$id_perso."'";
		$res_a_lire = $mysqli->query($sql_a_lire);
		$a_lire = $res_a_lire->num_rows;
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
		<link href="//cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.css" rel="stylesheet"/>
		
	</head>
	
	<body>
	
		<div class="container-fluid">

			<p align="center"><a href="jouer.php"> <input type="button" value="Retour au jeu"> </a></p>

			<div class="row justify-content-center">
				<div class="col-12">
			
					<table border=1 align="center" cellpadding=2 cellspacing=1 width=100%>
						<tr align="center" bgcolor="#EEEEDD">
							<td width="33%"><a href="messagerie.php">Messages reçus</a><font color="red"> <?php if($a_lire) echo" (".$a_lire." new)"; ?></font></td>
							<td width="33%"><a href="message_envoye.php">Messages envoyés</a></td>
							<td>Nouveau message</td>
						</tr>
						<tr align="center" bgcolor="#EEEEDD">
							<td><a href="messagerie_contacts.php">Contacts</a></td>
							<td><a href="messagerie_dossiers.php">Dossiers</a></td>
							<td></td>
						</tr>
					</table>
				<div>
			</div>
			
			<br>
			
			<div class="row justify-content-center">
				<div class="col-12">
				
					<?php
		if(!isset($_GET["id"]) || (isset($_GET["id"]) && $verif && $verif_id)){
					?>
					
					<form method="post" action="">
						<table border=1 align="center" cellpadding=2 cellspacing=1 width=100%>
							<tr class="messl">
								<td><div class="form-group"><label for="destinataireInput">Destinataire : </label></td> 
								<td colspan=3><input type="text" class="form-control autocomplete" id="destinataireInput" name="destinataire" size="30"
								<?php 
								if(isset($_SESSION['destinataires'])){
									echo 'value="'.$_SESSION['destinataires'].'"';
								} 
								if(isset($_GET["pseudo"])){ 
									echo 'value="'.$_GET["pseudo"].'"';
								}
								if(isset($_GET["id"])){
									if(isset($_GET["transfert"]) && $_GET["transfert"] == '1'){
										echo 'value=""';
									}
									else if(isset($_GET["rep"]) && $_GET["rep"] == '1'){
										echo 'value="'.$destinataires.'"';
									}
									else {	
										echo 'value="'.$tabMess["expediteur_message"].'"';
									}
								}	
								if(isset($_GET["visu"])){
									echo 'value="'.$visu.'"';
								}
								if(isset($_GET["id_compagnie"])){
									echo 'value="'.$dest.'"';
								}
								if(isset($_GET['id_contact'])){
									echo 'value="'.$dest_contact.'"';
								}?> ></div></td>
							</tr>
							<tr class="messl">
								<td><div class="form-group"><label for="inputObjet">Objet : </label></td>
								<td colspan=3><input type="text" class="form-control" id="inputObjet" name="objet" size="30"
								<?php 
								if(isset($_SESSION['objet'])){
									echo 'value="'.$_SESSION['objet'].'"';
								} 
								if(isset($_GET["id"])){
									if(isset($_GET["transfert"]) && $_GET["transfert"] == '1'){
										echo 'value="Tr: '.stripslashes($tabMess["objet_message"]).'"';
									}
									else {
										echo 'value="Re: '.stripslashes($tabMess["objet_message"]).'"';
									}
								}
								if(isset($_GET["id_compagnie"])){
									echo 'value="message du chef de la compagnie"';
								}?>></div></td>
							</tr>
							<tr class="messl">
								<td><div class="form-group"><label for="textareaMessageImput">Message : </label></td> 
								<td colspan=3 align="center">
<TEXTAREA class="form-control" id="textareaMessageImput" name="message" rows="15" cols="50" >
<?php
	if(isset($_SESSION['message'])){
		echo $_SESSION['message'];
	} 
	if(isset($_GET["id"])) {
		echo "\n\n****************************\n".stripslashes($tabMess["expediteur_message"])." wrote :\n****************************\n"; 
		echo stripslashes($tabMess["contenu_message"]);
	}
	if(isset($_GET["id_compagnie"])){
		echo "".stripslashes($contenu);
	} ?>
</TEXTAREA></div>
								</td>
							</tr>
						</table>
						<div align="center"><INPUT TYPE="SUBMIT" name="envoyer" VALUE="envoyer" class='btn btn-success'></div>
					</form>
				</div>
			</div>
<?php
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
		
		<!-- Optional JavaScript -->
		<!-- jQuery first, then Popper.js, then Bootstrap JS -->
		<script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
		<script src="//cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
		<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
		
		<script>		
		$(function() {
			
			$.widget("custom.myAutocomplete", $.ui.autocomplete, { 
				//since now is it copy of autocomplete widget just with different name
				//lets override:
				_renderItem: function (ul, item) {
					let result = $("<li>")
						.attr("data-value", item.value)
						.append(item.label)
						.append(" [")
						.append(item.value)
						.append("]")
						.appendTo(ul);

					//here comes the customization
					if ("1" === item.camp) {
						result.css('color', "blue");
					}
					else if ("2" === item.camp) {
						result.css('color', "red");
					}
					return result;
				}

			});
			
			function split(val) {
				return val.split(/;\s*/);
			}
			function extractLast(term) {
				return split(term).pop();
			}
		  
			$(".autocomplete").myAutocomplete({
				source: function (request, response) {
					$.getJSON("api/persos.php", {
						term: extractLast(request.term)
					}, response);
				},
				search: function () {
					// custom minLength
					var term = extractLast(this.value);
					if (term.length < 1) {
						return false;
					}
				},
				focus: function () {
					// prevent value inserted on focus
					return false;
				},
				select: function (event, ui) {
					var terms = split(this.value);
					// remove the current input
					terms.pop();
					// add the selected item
					var nm =  $('<textarea />').html(ui.item.label).text();
					terms.push(nm);
					// add placeholder to get the comma-and-space at the end
					terms.push("");
					this.value = terms.join(";");
					return false;
				}
			});
		});
		</script>
	</body>
</html>
<?php
}
else{
	echo "<font color=red>Vous ne pouvez pas accéder à cette page, veuillez vous loguer.</font>";
}
?>
