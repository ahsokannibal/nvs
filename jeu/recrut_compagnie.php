<?php
session_start();
require_once("../fonctions.php");

$mysqli = db_connexion();

include ('../nb_online.php');
include ('../forum/config.php');

// recupération config jeu
$dispo = config_dispo_jeu($mysqli);

if(isset($_SESSION["id_perso"])){

	$admin = admin_perso($mysqli, $_SESSION["id_perso"]);

	if($dispo || $admin){
		
		//recuperation des varaibles de sessions
		$id = $_SESSION["id_perso"];
		
		$sql = "SELECT pv_perso FROM perso WHERE id_perso='$id'";
		$res = $mysqli->query($sql);
		$tpv = $res->fetch_assoc();
		
		$testpv = $tpv['pv_perso'];
		
		if ($testpv <= 0) {
			echo "<font color=red>Vous êtes mort...</font>";
		}
		else {
			//$erreur = "<div class=\"erreur\">";
	
	?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<title>Nord VS Sud</title>
		
		<!-- Required meta tags -->
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		
		<!-- Bootstrap CSS -->
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
	</head>
	
	<body>
		<div align="center"><h2>Recrutement</h2></div>
	<?php
	if(isset($_GET["id_compagnie"])) {
		
		$verif = preg_match("#^[0-9]+$#i",$_GET["id_compagnie"]);
		
		if($verif){
	
			$id_compagnie = $_GET["id_compagnie"];
		
			// verification que le perso appartient bien a la compagnie et en est le recruteur ou chef
			$sql = "SELECT id_compagnie, poste_compagnie FROM perso_in_compagnie WHERE id_perso='$id' AND id_compagnie='$id_compagnie' AND (poste_compagnie='4' OR poste_compagnie='1')";
			$res = $mysqli->query($sql);
			$verif = $res->num_rows;
				
			if($verif){
				
				// recuperation des information sur la compagnie
				$sql = "SELECT genie_civil, nom_compagnie FROM compagnies WHERE id_compagnie=$id_compagnie";
				$res = $mysqli->query($sql);
				$sec = $res->fetch_assoc();
				
				$genie_compagnie 	= $sec["genie_civil"];
				$nom_compagnie		= addslashes($sec["nom_compagnie"]);
				
				if ($genie_compagnie) {
					$nb_persos_compagnie_max = 60;
				} else {
					$nb_persos_compagnie_max = 80;
				}
			
				if (isset($_POST["rec"]) && $_POST["rec"]=="recruter"){
					
					if(isset($_POST["recrut"])){
						
						$t_r = explode(",",$_POST["recrut"]);
						$new_recrue = $t_r[0];
						$nom_recrue = $t_r[1];
						
						// on met a jour le champ attenteValidation de la table perso_in_compagnie
						$sql = "UPDATE perso_in_compagnie SET attenteValidation_compagnie='0' WHERE id_perso=$new_recrue";
						$mysqli->query($sql);
						
						// insertion dans la table banque compagnie
						$sql = "INSERT INTO banque_compagnie VALUES ($new_recrue,'0','0','0')";
						$mysqli->query($sql);
						
						if ($genie_compagnie) {
							// Nouvelles compétences de construction pour le perso
							
							// Construire pont
							$sql = "INSERT INTO perso_as_competence (id_perso, id_competence, nb_points) VALUES ('$new_recrue', '23', '1')";
							$mysqli->query($sql);
							
							// Construire tour de visu
							$sql = "INSERT INTO perso_as_competence (id_perso, id_competence, nb_points) VALUES ('$new_recrue', '24', '1')";
							$mysqli->query($sql);
							
							// Construire Hopital
							$sql = "INSERT INTO perso_as_competence (id_perso, id_competence, nb_points) VALUES ('$new_recrue', '27', '1')";
							$mysqli->query($sql);
							
							// Construire Fortin
							$sql = "INSERT INTO perso_as_competence (id_perso, id_competence, nb_points) VALUES ('$new_recrue', '28', '1')";
							$mysqli->query($sql);
							
							// Construire Gare
							$sql = "INSERT INTO perso_as_competence (id_perso, id_competence, nb_points) VALUES ('$new_recrue', '63', '1')";
							$mysqli->query($sql);
							
							// Construire Rails
							$sql = "INSERT INTO perso_as_competence (id_perso, id_competence, nb_points) VALUES ('$new_recrue', '64', '1')";
							$mysqli->query($sql);
							
							// Compteur Génie sur le perso
							$sql = "UPDATE perso SET genie='8' WHERE id_perso='$new_recrue'";
							$mysqli->query($sql);
						}
						
						// on lui envoi un mp
						$message = "Bonjour $nom_recrue,
									J\'ai le plaisir de t\'annoncer que ton entrée dans la compagnie ". $nom_compagnie ." a été acceptée.";
						$objet = "Incorporation dans la compagnie";
						
						$lock = "LOCK TABLE (joueur) WRITE";
						$mysqli->query($lock);
						
						$sql = "INSERT INTO message (expediteur_message, date_message, contenu_message, objet_message) VALUES ( '" . $nom_compagnie . "', NOW(), '" . $message . "', '" . $objet . "')";
						$res = $mysqli->query($sql);
						$id_message = $mysqli->insert_id;
						
						$unlock = "UNLOCK TABLES";
						$mysqli->query($unlock);
						
						$sql = "INSERT INTO message_perso VALUES ('$id_message','$new_recrue','1','0','1','0')";
						$res = $mysqli->query($sql);
						
						echo "<center>".$nom_recrue."[".$new_recrue."] vient de rentrer dans la compagnie</center>";
					}
				}
				
				if (isset($_POST["ref"]) && $_POST["ref"]=="refuser"){
					
					if(isset($_POST["recrut"])){
						
						$t_r = explode(",",$_POST["recrut"]);
						$new_recrue = $t_r[0];
						$nom_recrue = $t_r[1];
						
						// on l'enleve 
						$sql = "DELETE FROM perso_in_compagnie WHERE id_perso='$new_recrue'";
						$mysqli->query($sql);
						
						// on lui envoi un mp
						$message = "Bonjour $nom_recrue,
									J\'ai le regret de t\'annoncer que ton entrée dans la compagnie ". $nom_compagnie ." a été refusé.";
						$objet = "Refus d\'incorporation dans la compagnie";
						
						$lock = "LOCK TABLE (joueur) WRITE";
						$mysqli->query($lock);
						
						$sql = "INSERT INTO message (expediteur_message, date_message, contenu_message, objet_message) VALUES ( '" . $nom_compagnie . "', NOW(), '" . $message . "', '" . $objet . "')";
						$res = $mysqli->query($sql);
						$id_message = $mysqli->insert_id;
						
						$unlock = "UNLOCK TABLES";
						$mysqli->query($unlock);
						
						$sql = "INSERT INTO message_perso VALUES ('$id_message','$new_recrue','1','0','1','0')";
						$res = $mysqli->query($sql);
						
						echo "<center>".$nom_recrue."[".$new_recrue."] vient d'être refusé de la compagnie</center>";
					}
				}
				
				if (isset($_POST["quit"]) && $_POST["quit"]=="valider le départ"){
					
					if(isset($_POST["quitter"])){
						
						$t_r = explode(",",$_POST["quitter"]);
						$id_recrue 	= $t_r[0];
						$nom_recrue = $t_r[1];
						
						// On regarde si le perso n'a pas de dette dans une banque de compagnie
						$sql = "SELECT SUM(montant) as thune_en_banque FROM histobanque_compagnie 
								WHERE id_perso='$id_recrue' 
								AND id_compagnie='$id_compagnie'";
						$res = $mysqli->query($sql);
						$tab = $res->fetch_assoc();
						
						$thune_en_banque = $tab["thune_en_banque"];
						
						if ($thune_en_banque >= 0) {
						
							$sql = "DELETE FROM histobanque_compagnie WHERE id_perso='$id_recrue'";
							$mysqli->query($sql);
						
							if ($thune_en_banque > 0) {
								$sql = "UPDATE banque_as_compagnie SET montant = montant - $thune_en_banque 
										WHERE id_compagnie='$id_compagnie'";
								$mysqli->query($sql);
								
								$sql = "SELECT montant FROM banque_as_compagnie WHERE id_compagnie='$id_compagnie'";
								$res = $mysqli->query($sql);
								$t = $res->fetch_assoc();
								
								$montant_final_banque = $t['montant'];
								
								$date = time();
								
								// banque log
								$sql = "INSERT INTO banque_log (date_log, id_compagnie, id_perso, montant_transfert, montant_final) VALUES (FROM_UNIXTIME($date), '$id_compagnie', '$id_recrue', '-$thune_en_banque', '$montant_final_banque')";
								$mysqli->query($sql);
							}
						
							// On delete le perso de la compagnie
							$sql = "DELETE FROM perso_in_compagnie WHERE id_perso=$id_recrue";
							$mysqli->query($sql);
							
							// on enleve le perso de la banque
							$sql = "DELETE FROM banque_compagnie WHERE id_perso=$id_recrue";
							$mysqli->query($sql);
							
							if ($genie_compagnie) {
								// On suprime les competences de construction
								
								// Construire pont
								$sql = "DELETE FROM perso_as_competence WHERE id_perso='$id_recrue' AND id_competence='23'";
								$mysqli->query($sql);
								
								// Construire tour de visu
								$sql = "DELETE FROM perso_as_competence WHERE id_perso='$id_recrue' AND id_competence='24'";
								$mysqli->query($sql);
								
								// Construire Hopital
								$sql = "DELETE FROM perso_as_competence WHERE id_perso='$id_recrue' AND id_competence='27'";
								$mysqli->query($sql);
								
								// Construire Fortin
								$sql = "DELETE FROM perso_as_competence WHERE id_perso='$id_recrue' AND id_competence='28'";
								$mysqli->query($sql);
								
								// Construire Gare
								$sql = "DELETE FROM perso_as_competence WHERE id_perso='$id_recrue' AND id_competence='63'";
								$mysqli->query($sql);
								
								// Construire Rails
								$sql = "DELETE FROM perso_as_competence WHERE id_perso='$id_recrue' AND id_competence='64'";
								$mysqli->query($sql);
							}
							
							echo "<center><font color='red'>".$nom_recrue."[".$id_recrue."] a été viré de la compagnie</font></center>";
						}
						else {
							echo "<center><font color='red'>".$nom_recrue."[".$id_recrue."] ne peut pas être viré de la compagnie car il possède des dettes à rembourser</font></center>";
						}
					}
				}
				
				if (isset($_POST["ref_quit"]) && $_POST["ref_quit"]=="refuser"){
					
					if(isset($_POST["quitter"])){
						
						$t_r = explode(",",$_POST["quitter"]);
						$id_recrue 	= $t_r[0];
						$nom_recrue = $t_r[1];
						
						// Suppression demande de sortie de la compagnie 
						$sql = "UPDATE perso_in_compagnie SET attenteValidation_compagnie = '0' WHERE id_perso='$id_recrue'";
						$mysqli->query($sql);
						
						// recup nom compagnie
						$sql = "SELECT nom_compagnie FROM compagnies WHERE id_compagnie='$id_compagnie'";
						$res = $mysqli->query($sql);
						$t_s = $res->fetch_assoc();
						
						$nom_groupe = $t_s["nom_compagnie"];
						
						// on lui envoi un mp
						$message = "Bonjour $nom_recrue,
									J\'ai le regret de t\'annoncer que ton départ de la compagnie ". addslashes($nom_groupe) ." a été refusé.";
						$objet = "Refus de départ de la compagnie";
						
						$lock = "LOCK TABLE (joueur) WRITE";
						$mysqli->query($lock);
						
						$sql = "INSERT INTO message (expediteur_message, date_message, contenu_message, objet_message) VALUES ( '" . addslashes($nom_groupe) . "', NOW(), '" . $message . "', '" . $objet . "')";
						$res = $mysqli->query($sql);
						$id_message = $mysqli->insert_id;
						
						$unlock = "UNLOCK TABLES";
						$mysqli->query($unlock);
						
						$sql = "INSERT INTO message_perso VALUES ('$id_message','$id_recrue','1','0','1','0')";
						$res = $mysqli->query($sql);
						
						echo "<center>".$nom_recrue."[".$id_recrue."] vient d'être refusé de quitter la compagnie</center>";
					}
				}
				
				// Récupération nombre perso dans la compagnie
				$sql = "SELECT count(*) as nb_persos_compagnie FROM perso_in_compagnie WHERE id_compagnie=$id_compagnie AND (attenteValidation_compagnie='0' OR attenteValidation_compagnie='2')";
				$res = $mysqli->query($sql);
				$tab = $res->fetch_assoc();
				
				$nb_persos_compagnie = $tab["nb_persos_compagnie"];
				
				if ($nb_persos_compagnie >= $nb_persos_compagnie_max) {
					echo "<center><font color = red>Votre compagnie a déjà atteint le nombre maximum de membres</font></center>";
				}
					
				echo "<center>Votre compagnie possède ". $nb_persos_compagnie . " unités pour une capacité maximale de ". $nb_persos_compagnie_max . " unités</center><br />" ;
				
				// recuperation de tout les persos qui sont en attente de validation pour entrer dans la compagnie
				$sql = "SELECT nom_perso, perso_in_compagnie.id_perso FROM perso_in_compagnie, perso 
						WHERE perso.ID_perso=perso_in_compagnie.id_perso AND id_compagnie=$id_compagnie AND attenteValidation_compagnie='1'";
				$res = $mysqli->query($sql);
				$num_a = $res->num_rows;
				
				// il y a des persos en attente de validation
				if($num_a) { 
				
					echo "<center><form method=\"post\" action=\"recrut_compagnie.php?id_compagnie=$id_compagnie\">";
					echo "liste des persos en attente :";
					echo "<select name=\"recrut\">";
					
					while ($t_a = $res->fetch_assoc()){
						
						$id_p 	= $t_a["id_perso"];
						$nom_p 	= $t_a["nom_perso"];
						
						echo "<center><option value=".$id_p.",".$nom_p.">".$nom_p."[".$id_p."]</option><br></center>";
					}
					
					echo "</select>";
					if ($nb_persos_compagnie < $nb_persos_compagnie_max) {
						echo "&nbsp;<input type=\"submit\" name=\"rec\" class='btn btn-success' value=\"recruter\">";
					}
					echo "&nbsp;<input type=\"submit\" class='btn btn-danger' name=\"ref\" value=\"refuser\">";
					echo "</form></center>";
				}
				else {
					echo "<center><font color = blue>Il n y a aucun perso en attente de validation pour rejoindre la compagnie</font></center>";
				}
				
				// recuperation de tout les persos qui sont en attente de validation pour quitter la compagnie
				$sql = "SELECT nom_perso, perso_in_compagnie.id_perso FROM perso_in_compagnie, perso 
						WHERE perso.ID_perso=perso_in_compagnie.id_perso AND id_compagnie=$id_compagnie AND attenteValidation_compagnie='2'";
				$res = $mysqli->query($sql);
				$num_q = $res->num_rows;
				
				// il y a des persos en attente de validation pour quitter la compagnie
				if($num_q) { 
				
					echo "<center><form method=\"post\" action=\"recrut_compagnie.php?id_compagnie=$id_compagnie\">";
					echo "liste des persos en attente :";
					echo "<select name=\"quitter\">";
					
					while ($t_q = $res->fetch_assoc()){
						
						$id_p 	= $t_q["id_perso"];
						$nom_p 	= $t_q["nom_perso"];
						
						echo "<center><option value=".$id_p.",".$nom_p.">".$nom_p."[".$id_p."]</option><br></center>";
					}
					
					echo "</select>";
					echo "&nbsp;<input type=\"submit\" name=\"quit\" class='btn btn-success' value=\"valider le départ\">&nbsp;<input type=\"submit\" name=\"ref_quit\" class='btn btn-danger' value=\"refuser\">";
					echo "</form></center>";
				}
				else {
					echo "<br /><center><font color = blue>Il n y a aucun perso en attente de validation pour quitter la compagnie</font></center>";
				}
				echo "<center><a href='compagnie.php' class='btn btn-outline-secondary'>Retour à la page de compagnie</a></center>";
			}
			else {
				echo "<center><font color='red'>Vous n'avez pas les habilitations pour accéder à cette page !</font></center>";
			
				$text_triche 	= "Test accès page recrutement compagnie d'id : $id_compagnie";
					
				$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id', '$text_triche')";
				$mysqli->query($sql);
			}
		}
		else {
			echo "<center>La compagnie demandé n'existe pas</center>";
			
			$param_test 	= addslashes($id_compagnie);
			$text_triche 	= "Test parametre sur page recrut compagnie, parametre id_compagnie invalide tenté : $param_test";
				
			$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id', '$text_triche')";
			$mysqli->query($sql);
		}
	}	
	?>
		<!-- Optional JavaScript -->
		<!-- jQuery first, then Popper.js, then Bootstrap JS -->
		<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
		<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
	
	</body>
</html>
	<?php
		}
	}
	else{
		// logout
		$_SESSION = array(); // On ecrase le tableau de session
		session_destroy(); // On detruit la session
		
		header("Location:../index2.php");
	}?>
<?php
}
else {
	// logout
	$_SESSION = array(); // On ecrase le tableau de session
	session_destroy(); // On detruit la session
	
	header("Location:../index2.php");
}
?>