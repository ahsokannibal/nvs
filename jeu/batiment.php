<?php
@session_start();
require_once("../fonctions.php");
require_once("f_carte.php");
require_once("f_action.php");

$mysqli = db_connexion();

include ('../nb_online.php');

// recupération config jeu
$sql = "SELECT disponible FROM config_jeu";
$res = $mysqli->query($sql);
$t_dispo = $res->fetch_assoc();

$dispo = $t_dispo["disponible"];

if($dispo){
	
	if(isset($_SESSION["id_perso"])){
		
		$id_perso = $_SESSION['id_perso'];
		$date = time();
	
		$sql = "SELECT pv_perso, type_perso, or_perso, UNIX_TIMESTAMP(DLA_perso) as DLA, est_gele FROM perso WHERE id_perso='$id_perso'";
		$res = $mysqli->query($sql );
		$tpv = $res->fetch_assoc();
		
		$testpv 	= $tpv['pv_perso'];
		$type_perso	= $tpv['type_perso'];
		$or 		= $tpv["or_perso"];
		$dla 		= $tpv["DLA"];
		$est_gele 	= $tpv["est_gele"];
		
		$config = '1';
		
		// verification si le perso est encore en vie
		if ($testpv <= 0) {
			// le perso est mort
			session_unregister('deDefense');
			session_unregister('deAttaque');
			
			header("Location: ../tour.php");
		}
		else {
			
			// le perso est vivant
			if(isset($_GET['bat'])){
				
				// Recuperation de l'id du batiment
				$id_i_bat = $_GET['bat'];
				
				// On verifie que c'est bien une valeur numerique
				$verif = preg_match("#^[0-9]*[0-9]$#i","$id_i_bat");
				
				if($verif){
					
					//verification que le perso est bien dans le batiment
					if (in_instance_bat($mysqli, $id_perso, $id_i_bat)){
					
						// recupération du type de batiment
						$sql = "SELECT id_batiment, camp_instance, pv_instance, pvMax_instance, nom_instance
								FROM instance_batiment
								WHERE id_instanceBat='$id_i_bat'";
						$res = $mysqli->query($sql);
						$t = $res->fetch_assoc();
						
						$id_bat 	= $t["id_batiment"];
						$camp_bat 	= $t["camp_instance"];
						$pv_bat 	= $t["pv_instance"];
						$pvMax_bat 	= $t["pvMax_instance"];
						$nom_i_bat 	= $t["nom_instance"];
						
						$pourcentage_rabais = 0;
						
						// rabais marchandage pourcentage
						$nb_points_marchandage = est_marchand($mysqli, $id_perso);
						
						if($nb_points_marchandage){
							if($nb_points_marchandage == 1){
								$pourcentage_rabais = 2;
							}
							if($nb_points_marchandage == 2){
								$pourcentage_rabais = 4;
							}
							if($nb_points_marchandage == 3){
								$pourcentage_rabais = 5;
							}
						}
						
						//recup des infos du batiment
						$sql_i = "SELECT nom_batiment, description FROM batiment WHERE id_batiment='$id_bat'";
						$res_i = $mysqli->query($sql_i);
						$t_i = $res_i->fetch_assoc();
						
						$nom_bat = $t_i["nom_batiment"];
						$description_bat = $t_i["description"];
						
						if($camp_bat == '1'){
							$camp_bat2 = 'bleu';
						}
						if($camp_bat == '2'){
							$camp_bat2 = 'rouge';
						}
						
						$blason="blason_".$camp_bat2.".gif";
						
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
	<title>Nord VS Sud</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	</head>
	
	<body>
		<div align="center"><h2><?php echo $nom_bat." ".$nom_i_bat; ?></h2></div>
		<center><img src="../images/<?php echo $blason; ?>" alt="blason"/></center><br />
		<center><a href="evenement.php?infoid=<?php echo $id_i_bat; ?>">Voir les évènements du bâtiment</a></center>
<?php
						/////////////////////
						// on achete une arme
						if(isset($_POST["achat_arme"])) {
							
							// recuperation de l'id de l'arme
							$id_arme = $_POST["hid_achat_arme"];
							
							// vérifier que $id_arme est une valeur numérique
							$verif_id = preg_match("#^[0-9]+$#i",$id_arme);
							
							if($verif_id){
							
								// vérification que l'arme existe bien
								if(existe_arme($mysqli, $id_arme)){
								
									// recuperation des données de l'arme
									$sql_a = "SELECT nom_arme, coutOr_arme, poids_arme FROM arme WHERE id_arme='$id_arme'";
									$res_a = $mysqli->query($sql_a);
									$t_a = $res_a->fetch_assoc();
									
									$nom_arme 		= $t_a["nom_arme"];
									$coutOr_arme 	= $t_a["coutOr_arme"];
									$poids_arme 	= $t_a["poids_arme"];
									
									// calcul rabais
									if($nb_points_marchandage){
										$rabais = floor(($coutOr_arme * $pourcentage_rabais)/100);
										$coutOr_arme = $coutOr_arme - $rabais;
									}
									
									//verification de l'or du perso
									if($or >= $coutOr_arme){
										
										// insertion perso_as_arme
										$sql_i = "INSERT INTO perso_as_arme VALUES('$id_perso','$id_arme','0')";
										$mysqli->query($sql_i);
									
										// mis à jour or/charge perso
										$sql_m = "UPDATE perso SET or_perso=or_perso-$coutOr_arme, charge_perso=charge_perso+$poids_arme WHERE id_perso='$id_perso'";
										$mysqli->query($sql_m);
									
										// MAJ or perso pour affichage
										$or = $or - $coutOr_arme;
									
										echo "<font color=blue>Vous venez de vous offrir l'arme $nom_arme pour $coutOr_arme Or</font>";
									}
									else {
										echo "<font color=red>Vous n'avez pas assez de thunes pour vous offrir cette arme</font>";
									}
								}
								else {
									echo "<font color=red>L'arme demandée n'est plus en stock ou n'est plus vendue</font>";
								}
							}
							else {
								echo "Données incorrectes, veuillez contacter l'administrateur.";
							}
						}
						
						////////////////////////
						// On achete une armure
						if(isset($_POST["achat_armure"])){
							
							// recuperation de l'id de l'armure
							$id_armure = $_POST["hid_achat_armure"];
							
							// vérifier que $id_armure est une valeur numérique
							$verif_id = preg_match("#^[0-9]+$#i",$id_armure);
							
							if($verif_id){
								
								// vérification que l'armure existe bien
								if(existe_armure($mysqli, $id_armure)){
								
									// recuperation des données de l'armure
									$sql_a = "SELECT nom_armure, coutOr_armure, poids_armure FROM armure WHERE id_armure='$id_armure'";
									$res_a = $mysqli->query($sql_a);
									$t_a = $res_a->fetch_assoc();
									
									$nom_armure 	= $t_a["nom_armure"];
									$coutOr_armure 	= $t_a["coutOr_armure"];
									$pvMax_armure 	= $t_a["pvMax_armure"];
									
									// calcul rabais
									if($nb_points_marchandage){
										$rabais = floor(($coutOr_armure * $pourcentage_rabais)/100);
										$coutOr_armure = $coutOr_armure - $rabais;
									}
									
									//verification de l'or du perso
									if($or >= $coutOr_armure){
										
										// insertion perso_as_armure
										$sql_i = "INSERT INTO perso_as_armure VALUES('$id_perso','$id_armure','0')";
										$mysqli->query($sql_i);
									
										// mis à jour or et charge perso
										$sql_m = "UPDATE perso SET or_perso=or_perso-$coutOr_armure, charge_perso=charge_perso+$poids_armure WHERE id_perso='$id_perso'";
										$mysqli->query($sql_m);
									
										// MAJ or perso pour affichage
										$or = $or - $coutOr_armure;
										
										echo "<font color=blue>Vous venez de vous offrir l'armure $nom_armure pour $coutOr_armure Thunes</font>";
									}
									else {
										echo "<font color=red>Vous n'avez pas assez de thunes pour vous offrir cette arme</font>";
									}
								}
								else {
									echo "<font color=red>L'armure demandée n'est plus en stock ou n'est plus vendue</font>";
								}
							}
							else {
								echo "Données incorrectes, veuillez contacter l'administrateur.";
							}
						}
						
						/////////////////////
						// on achete un objet
						if(isset($_POST["achat_objet"])) {
						
							// recuperation de l'id de l'objet
							$id_o = $_POST["hid_achat_objet"];
							
							// vérifier que $id_o est une valeur numérique
							$verif_id = preg_match("#^[0-9]+$#i",$id_o);
							
							if($verif_if) {
								
								// vérification que l'objet existe bien
								if(existe_objet($mysqli, $id_o)){
								
									// recuperation des données de l'objet
									$sql = "SELECT nom_objet, poids_objet, coutOr_objet FROM objet WHERE id_objet='$id_o'";
									$res = $mysqli->query($sql);
									$t_o = $res->fetch_assoc();
									
									$nom_o 		= $t_o["nom_objet"];
									$poids_o 	= $t_o["poids_objet"];
									$coutOr_o 	= $t_o["coutOr_objet"];
									
									// calcul rabais
									if($nb_points_marchandage){
										$rabais = floor(($coutOr_o * $pourcentage_rabais)/100);
										$coutOr_o = $coutOr_o - $rabais;
									}
									
									//verification de l'or du perso
									if ($coutOr_o <= $or){
										
										//On met à jour le perso (or + charge)
										$sql = "UPDATE perso SET or_perso=or_perso-$coutOr_o, charge_perso=charge_perso+$poids_o WHERE id_perso='$id_perso'";
										$mysqli->query($sql);
										
										// On met l'objet dans le sac
										$sql = "INSERT INTO perso_as_objet VALUES ('$id_perso','$id_o')";
										$mysqli->query($sql);
										
										// MAJ or perso pour affichage
										$or = $or - $coutOr_o;
										
										echo "<font color=blue>Vous avez acheter l'objet $nom_o pour $coutOr_o thunes</font>";
									}
									else {
										echo "<font color=red>Vous ne possédez pas assez de thunes pour acheter l'objet $nom_o</font>";
									}
								}
								else {
									echo "<font color=red>L'objet demandée n'est plus en stock ou n'est plus vendue</font>";
								}
							}
							else {
								echo "Données incorrectes, veuillez contacter l'administrateur.";
							}
						}
						
						/////////////////////
						// on vent une armure
						if(isset($_POST["vente_armure"])) {
							
							// recupération de l'id de l'armure ainsi que de ses pv
							$t_vente_armure = $_POST["hid_vente_armure"];
							$t_vente_armure2 = explode(',',$t_vente_armure);
							$id_armure = $t_vente_armure2[0];
							
							// On verifie que l'id de l'armure et les pv de l'armure sont bien des valeurs numeriques
							$verif_id = preg_match("#^[0-9]+$#i",$id_armure);
							
							if($verif_id){
								
								// On vérifie que le perso possede bien l'amure et qu'elle n'est pas équipée
								$sql_v = "SELECT id_armure FROM perso_as_armure WHERE id_armure='$id_armure' AND est_portee='0'";
								$res_v = $mysqli->query($sql_v);
								$nb_res_v = $res_v->num_rows;
								
								if($nb_res_v > 0){
									
									// recuperation des infos sur l'armure
									$sql_a = "SELECT nom_armure, coutOr_armure, poids_armure FROM armure WHERE id_armure='$id_armure'";
									$res_a = $mysqli->query($sql_a);
									$t_a = $res_a->fetch_assoc();
									
									$nom_armure = $t_a["nom_armure"];
									$coutOr_armure = $t_a["coutOr_armure"];
									$poids_armure = $t_a["poids_armure"];
									
									// Calcul du prix de vente
									$prix_vente_final = ceil($coutOr_armure / 2);
									
									// Mise à jour de l'inventaire du perso
									$sql_d = "DELETE FROM perso_as_armure 
											  WHERE id_perso='$id_perso' AND id_armure='$id_armure' AND est_portee='0' LIMIT 1";
									$mysqli->query($sql_d);
									
									// Mise à jour or et poids perso
									$sql_u = "UPDATE perso 
											  SET or_perso=or_perso+$prix_vente_final, charge_perso=charge_perso-$poids_armure
											  WHERE id_perso='$id_perso'";
									$mysqli->query($sql_u);
									
									echo "<br /><center><font color='blue'>Vous avez vendu votre armure <b>$nom_armure</b> pour <b>$prix_vente_final</b> Thunes</font></center>";
									
									// MAJ affichage or perso
									$or = $or + $prix_vente_final;
								}
								else {
									echo "Vous ne pouvez pas enter de vendre ce que vous ne possédez pas.";
								}
							}
							else {
								echo "Données incorrectes, veuillez contacter l'administrateur.";
							}
						}
						
						/////////////////////
						// on vent une arme
						if(isset($_POST["vente_arme"])) {
							
							// recupération de l'id de l'arme ainsi que de ses pv
							$t_vente_arme = $_POST["hid_vente_arme"];
							$t_vente_arme2 = explode(',',$t_vente_arme);
							$id_arme = $t_vente_arme2[0];
							
							// On verifie que l'id de l'armure et les pv de l'armure sont bien des valeurs numeriques
							$verif_id = preg_match("#^[0-9]+$#i",$id_arme);
							
							if($verif_id){
								
								// On vérifie que le perso possede bien l'amure et qu'elle n'est pas équipée
								$sql_v = "SELECT id_arme FROM perso_as_arme WHERE id_arme='$id_arme' AND est_portee='0'";
								$res_v = $mysqli->query($sql_v);
								$nb_res_v = $res_v->num_rows;
								
								if($nb_res_v > 0){
									// recuperation des infos sur l'arme
									$sql_a = "SELECT nom_arme, coutOr_arme, poids_arme FROM arme WHERE id_arme='$id_arme'";
									$res_a = $mysqli->query($sql_a);
									$t_a = mysql_fetch_assoc($res_a);
									$nom_arme = $t_a["nom_arme"];
									$coutOr_arme = $t_a["coutOr_arme"];
									$poids_arme = $t_a["poids_arme"];
									
									// Calcul du prix de vente (selon pv)
									$prix_vente_final = ceil($coutOr_arme / 2);
									
									// Mise à jour de l'inventaire du perso
									$sql_d = "DELETE FROM perso_as_arme 
											  WHERE id_perso='$id_perso' AND id_arme='$id_arme' AND est_portee='0' LIMIT 1";
									$mysqli->query($sql_d);
									
									// Mise à jour or et poids perso
									$sql_u = "UPDATE perso 
											  SET or_perso=or_perso+$prix_vente_final, charge_perso=charge_perso-$poids_arme
											  WHERE id_perso='$id_perso'";
									$mysqli->query($sql_u);
									
									echo "<br /><center><font color='blue'>Vous avez vendu votre arme <b>$nom_arme</b> pour <b>$prix_vente_final</b> Thunes</font></center>";
									
									// MAJ affichage or perso
									$or = $or + $prix_vente_final;
								}
								else {
									echo "Vous ne pouvez pas enter de vendre ce que vous ne possédez pas.";
								}
							}
							else {
								echo "Données incorrectes, veuillez contacter l'administrateur.";
							}
						}
						
						/////////////////////
						// on vent un objet
						if(isset($_POST["vente_objet"])) {
							
							// recupération de l'id de l'objet
							$id_objet = $_POST["hid_vente_objet"];
							
							// On verifie que l'id de l'objet
							$verif_id = preg_match("#^[0-9]+$#i",$id_objet);
							
							if($verif_id ){
								
								// On vérifie que le perso possede bien l'objet
								$sql_v = "SELECT id_objet FROM perso_as_objet WHERE id_objet='$id_objet'";
								$res_v = $mysqli->query($sql_v);
								$nb_res_v = $res_v->num_rows;
								
								if($nb_res_v > 0){
									
									// recuperation des infos sur l'objet
									$sql_o = "SELECT nom_objet, coutOr_objet, poids_objet, type_objet FROM objet WHERE id_objet='$id_objet'";
									$res_o = $mysqli->query($sql_o);
									$t_o = mysql_fetch_assoc($res_o);
									$nom_objet = $t_o["nom_objet"];
									$coutOr_objet = $t_o["coutOr_objet"];
									$poids_objet = $t_o["poids_objet"];
									$type_objet = $t_o["type_objet"];
									
									// Calcul du prix de vente (selon pv)
									$prix_vente_final = ceil($coutOr_objet / 2);
									
									// Mise à jour de l'inventaire du perso
									$sql_d = "DELETE FROM perso_as_objet 
											  WHERE id_perso='$id_perso'
											  AND id_objet='$id_objet' LIMIT 1";
									$mysqli->query($sql_d);
									
									// Mise à jour or et poids perso
									$sql_u = "UPDATE perso 
											  SET or_perso=or_perso+$prix_vente_final, charge_perso=charge_perso-$poids_objet
											  WHERE id_perso='$id_perso'";
									$mysqli->query($sql_u);
									
									echo "<br /><center><font color='blue'>Vous avez vendu votre";
									if($type_objet == "M" || $type_objet == "MSP"){
										echo " ressource ";
									}
									else {
										echo " objet ";
									}
									echo "<b>$nom_objet</b> pour <b>$prix_vente_final</b> Thunes</font></center>";
									
									// MAJ affichage or perso
									$or = $or + $prix_vente_final;
								}
								else {
									echo "Vous ne pouvez pas enter de vendre ce que vous ne possédez pas.";
								}
							}
							else {
								echo "Données incorrectes, veuillez contacter l'administrateur.";
							}
						}
						
						////////////////////////////////////
						// On dépose des ressources à l'entrepot
						if(isset($_POST['depot_ressources'])){
							
							$id_objet = $_POST["hid_depot_ressources"];
							$nb_objet = $_POST["select_ressources"];
							
							// verifier batiment est bien un entrepot
							if($id_bat == '6'){
								
								// vérifier que id_objet et nb_objet est un nombre
								$verif_idObjet = preg_match("#^[0-9]*[0-9]$#i","$id_objet");
								$verif_nbObjet = preg_match("#^[0-9]*[0-9]$#i","$nb_objet");
								
								if($verif_idObjet && $verif_nbObjet){
									
									// verifier que le perso possede bien id_objet et la quantité
									$sql_v = "SELECT count(*) as nb_objet_perso FROM perso_as_objet WHERE id_perso='$id_perso' AND id_objet='$id_objet'";
									$res_v = $mysqli->query($sql_v);
									$t_v = $res_v->fetch_assoc();
									$nb_res = $res_v->num_rows;
									
									$nb_objet_perso = $t_v['nb_objet_perso'];
									
									if($nb_res && $nb_objet_perso >= $nb_objet){
										
										// On supprime les ressources de l'inventaire du perso
										$sql_d = "DELETE FROM perso_as_objet WHERE id_perso='$id_perso' AND id_objet='$id_objet' LIMIT $nb_objet";
										$mysqli->query($sql_d);
										
										// recup infos objet
										$sql = "SELECT nom_objet, poids_objet FROM objet WHERE id_objet='$id_objet'";
										$res = $mysqli->query($sql);
										$t = $res->fetch_assoc();
										
										$nom_objet = $t['nom_objet'];
										$poids_objet = $t['poids_objet'];
										$poids_total = $poids_objet * $nb_objet;
										
										// MAJ poids perso
										$sql_u = "UPDATE perso SET charge_perso = charge_perso - $poids_total WHERE id_perso='$id_perso'";
										$mysqli->query($sql_u);
										
										// On vérifie que l'entrepot est present en base dans la table ressource_entrepot pour la ressource qu'on veut déposer
										$sql_v2 = "SELECT * FROM ressources_entrepot WHERE id_entrepot='$id_i_bat' AND id_ressource='$id_objet'";
										$res_v2 = $mysqli->query($sql_v2);
										$verif_v2 = $res_v2->num_rows;
										
										if(!$verif_v2){
											// On crée la ligne en base et on ajoute directement les ressources
											$sql_i = "INSERT INTO ressources_entrepot VALUES ('$id_i_bat','$id_objet','$nb_objet')";
										}
										else {
											// On ajoute les ressources dans l'entrepot
											$sql_i = "UPDATE ressources_entrepot SET nb_ressource = nb_ressource + $nb_objet WHERE id_entrepot='$id_i_bat' AND id_ressource='$id_objet'";
										}
										$mysqli->query($sql_i);
										
										// message
										echo "<center><font color='blue'>Vous avez déposé $nb_objet $nom_objet";
										if($nb_objet > 1)
											echo "s";
										echo " dans l'entrepot</font></center>";
									}
									else {
										echo "<center><font color='red'><b>Vous ne possédez pas la resource que vous souhaitez déposer</b></font></center>";
									}
								}
								else {
									echo "<center><font color='red'><b>Données incorrectes, veuillez contacter l'administrateur</b></font></center>";
								}
							}
							else {
								echo "<center><font color='red'><b>Vous ne pouvez pas déposer des ressources dans un autre batiment que l'entrepôt</b></font></center>";
							}
						}
						
						////////////////////////////////////
						// On Récupére des ressources à l'entrepot
						if(isset($_POST['recup_ressources'])){
							
							$id_objet = $_POST["hid_recup_ressources"];
							$nb_objet = $_POST["select_recup_ressources"];
							
							// verifier batiment est bien un entrepot
							if($id_bat == '6'){
								
								// vérifier que id_objet et nb_objet est un nombre
								$verif_idObjet = preg_match("#^[0-9]*[0-9]$#i","$id_objet");
								$verif_nbObjet = preg_match("#^[0-9]*[0-9]$#i","$nb_objet");
								
								if($verif_idObjet && $verif_nbObjet){
									
									// Vérifier que l'entrepot posséde bien les ressources et la quantité
									$sql_res = "SELECT nb_ressource FROM ressources_entrepot WHERE id_entrepot='$id_i_bat' AND id_ressource='$id_objet'";
									$res_res = $mysqli->query($sql_res);
									$verif = $res_res->num_rows;
									$t_res = $res_res->fetch_assoc();
									
									$nb_ressources = $t_res['nb_ressource'];
									
									if($verif > 0 && $nb_ressources >= $nb_objet){
										
										// Suppression ressources dans table ressource_entrepot
										$sql_u = "UPDATE ressources_entrepot SET nb_ressource=nb_ressource-$nb_objet WHERE id_entrepot='$id_i_bat' AND id_ressource='$id_objet'";
										$mysqli->query($sql_u);
										
										// Recup infos ressource
										$sql = "SELECT nom_objet, poids_objet FROM objet WHERE id_objet='$id_objet'";
										$res = $mysqli->query($sql);
										$t = $res->fetch_assoc();
										
										$nom_objet = $t['nom_objet'];
										$poids_objet = $t['poids_objet'];
										$poids_total = $poids_objet * $nb_objet;
										
										$compteur_o = 0;
										
										// Ajout ressources dans inventaire perso
										while($compteur_o < $nb_objet){
											
											$sql_i = "INSERT INTO perso_as_objet VALUES ('$id_perso','$id_objet')";
											$mysqli->query($sql_i);
											
											$compteur_o++;
										}
										
										// MAJ poid perso
										$sql_u2 = "UPDATE perso SET charge_perso=charge_perso+$poids_total";
										$mysqli->query($sql_u2);
										
										// message
										echo "<center><font color='blue'>Vous avez récupéré $nb_objet $nom_objet";
										if($nb_objet > 1)
											echo "s";
										echo " dans l'entrepot</font></center>";
										
									}
									else {
										echo "<center><font color='red'><b>L'entrepôt ne posséde pas la ressource demandée en quantité suffisante</b></font></center>";
									}
								}
								else {
									echo "<center><font color='red'><b>Données incorrectes, veuillez contacter l'administrateur</b></font></center>";
								}
							}
							else {
								echo "<center><font color='red'><b>Vous ne pouvez pas récupérer des ressources dans un autre batiment que l'entrepôt</b></font></center>";
							}
						}
						
						// traitement des formulaires prèsent uniquement en gare
						if($id_bat == '11'){
							
							// Achat d'un ticket de train
							if (isset($_POST["acheter_ticket"]) && isset($_POST["ticket_hidden"]) && trim($_POST["ticket_hidden"]) != "") {
								
								$ticket_dest = $_POST["ticket_hidden"];
								
								$sql_dest = "SELECT nom_instance FROM instance_batiment WHERE id_instanceBat='$ticket_dest'";
								$res_dest = $mysqli->query($sql_dest);
								$t_dest = $res_dest->fetch_assoc();
								
								$nom_destination = "Gare " . $t_dest['nom_instance'] . "[" . $ticket_dest . "]";
								
								// On vérifie que le perso possède bien 5 thunes 
								if ($or >= 5) {
									
									// On vérifie si le perso n'a pas déjà un ticket pour la même destination
									$sql = "SELECT count(*) as nb_ticket FROM perso_as_objet WHERE id_perso='$id_perso' AND id_objet='1' AND capacite_objet='$ticket_dest'";
									$res = $mysqli->query($sql);
									$t = $res->fetch_assoc();
									
									$possede_deja_ticket = $t['nb_ticket'];
									
									if ($possede_deja_ticket == 0) {
										
										// MAJ thune perso
										$sql = "UPDATE perso SET or_perso=or_perso-5 WHERE id_perso='$id_perso'";
										$mysqli->query($sql);
										
										// Ajout de l'objet ticket de train dans l'inventaire du perso
										$sql = "INSERT INTO perso_as_objet (id_perso, id_objet, capacite_objet) VALUES ('$id_perso', '1', '$ticket_dest')";
										$mysqli->query($sql);
										
										// Maj thune pour affichage 
										$or = $or - 5;
										
										echo "<center><font color='blue'>Vous avez acheté un ticket de train en destination de $nom_destination</font></center>";
									}
									else {
										echo "<center><font color='red'>Vous possédez déjà un ticket de train pour cette destination</font></center>";
									}
								} 
								else {
									echo "<center><font color='red'>Vous n'avez pas suffisamment de thunes pour vous acheter un ticket de train</font></center>";
								}
							}
						}						
						
						echo "<br /><div align=\"center\">Vous possédez <b>$or</b> thune(s)</div><br />";
						
						/////////////////
						// On veut vendre
						// Possible seulement dans : fort, fortins, hopitaux, entrepots
						if($id_bat == '6' || $id_bat == '7' || $id_bat == '8' || $id_bat == '9'){
							
							if(isset($_GET['vente']) && $_GET['vente'] == 'ok'){
								
								echo "<center><a href=\"batiment.php?bat=$id_i_bat\">Fermer la partie sur la vente de vos biens</a></center>";
								
								if ($id_bat == '6') {
									// On ne peut vendre que des objets de type ressources dans un entrepot
									// Répération des ressources (M ou MSP) que posséde le perso
									$sql_ressources = "SELECT DISTINCT objet.id_objet FROM perso_as_objet, objet WHERE id_perso='$id_perso'
											AND perso_as_objet.id_objet=objet.id_objet
											AND (type_objet='M' OR type_objet='MSP')";
								}
								if($id_bat == '7'){
									// On ne peut vendre que des objets de type soins dans un hopital
									// Récupération des objets de soin (S ou SP ou SSP) que posséde le perso
									$sql_ressources = "SELECT DISTINCT objet.id_objet FROM perso_as_objet, objet WHERE id_perso='$id_perso'
											AND perso_as_objet.id_objet=objet.id_objet
											AND (type_objet='S' OR type_objet='SSP' OR type_objet='SP')";
								}
								if($id_bat == '8' || $id_bat == '9'){
									// On ne peut vendre que des objets de type autre que ressources et soins dans un fort / fortin
									// Récupération des objets non S et non M que posséde le perso
									$sql_ressources = "SELECT DISTINCT objet.id_objet FROM perso_as_objet, objet WHERE id_perso='$id_perso'
											AND perso_as_objet.id_objet=objet.id_objet
											AND type_objet!='M' AND type_objet!='MSP'
											AND type_objet!='S' AND type_objet!='SP' AND type_objet!='SSP'";
								}
								
								$res_resources = $mysqli->query($sql_ressources);
								
								echo "<table border=1 align=center width='70%'>";
								echo "<tr><th colspan='5'>Vos ressources</th></tr>";
								echo "<tr><th>Objet</th><th>Poids</th><th>Quantité possédée</th><th>Prix de vente (unité)</th><th>Vente</th></tr>";
									
								while($t = $res_resources->fetch_assoc()){
									
									echo "<form method=\"post\" action=\"batiment.php?bat=$id_i_bat\">";
										
									$id_objet = $t['id_objet'];
										
									// recupération du nombre d'objets de ce type que posséde le perso
									$sql_nb = "SELECT COUNT(id_objet) as nb_obj FROM perso_as_objet WHERE id_objet='$id_objet' AND id_perso='$id_perso'";
									$res_nb = $mysqli->query($sql_nb);
									$t_nb = $res_nb->fetch_assoc($res_nb);
									
									$nb_obj = $t_nb['nb_obj'];
										
									// Récupération des informations sur l'objet
									$sql_o = "SELECT nom_objet, description_objet, poids_objet, coutOr_objet FROM objet WHERE id_objet='$id_objet'";
									$res_o = $mysqli->query($sql_o);
									$t_o = $res_o->fetch_assoc();
									
									$nom_objet = $t_o["nom_objet"];
									$description_objet = $t_o["description_objet"];
									$poids_objet = $t_o["poids_objet"];
									$coutOr_objet = $t_o["coutOr_objet"];
										
									// Calcul du prix de vente
									$prix_vente_max = round ($coutOr_objet / 2);
										
									echo "<tr><td align='center'><img src='../images/objet".$id_objet.".png' /><br /><b>$nom_objet</b></td><td align='center'>$poids_objet</td><td align='center'>$nb_obj</td><td align='center'>$prix_vente_max</td>";
									echo "<td align=\"center\"><input type='submit' name='vente_objet' value='Vendre' />";
									echo "<input type='hidden' name='hid_vente_objet' value='".$id_objet."' />";
									echo "</td></tr>";
													
									echo "</form>";
								}
								echo "</table><br />";
									
								// Armes
								// Récupération des armes que posséde le perso (non porté)
								$sql_armes = "SELECT id_arme FROM perso_as_arme WHERE id_perso='$id_perso' AND est_portee='0'";
								$res_armes = $mysqli->query($sql_armes);
									
								echo "<table border=1 align=center width='70%'>";
								echo "<tr><th colspan='5'>Vos armes</th></tr>";
								echo "<tr><th>Armes</th><th>Poids</th><th>Prix de vente</th><th>Vente</th></tr>";
									
								while($t = $res_armes->fetch_assoc()){
									
									echo "<form method=\"post\" action=\"batiment.php?bat=$id_i_bat\">";
								
									$id_arme = $t['id_arme'];
										
									// Récupération des informations sur l'arme
									$sql_a = "SELECT nom_arme, description_arme, poids_arme, coutOr_arme, image_arme FROM arme WHERE id_arme='$id_arme'";
									$res_a = $mysqli->query($sql_a);
									$t_a = $res_a->fetch_assoc();
									
									$nom_arme = $t_a["nom_arme"];
									$description_arme = $t_a["description_arme"];
									$poids_arme = $t_a["poids_arme"];
									$coutOr_arme = $t_a["coutOr_arme"];
									$image_arme = $t_a["image_arme"];
										
									// Calcul du prix de vente
									$prix_vente_final = ceil($coutOr_arme / 2);
										
									echo "<tr><td align='center'><img src='../images/armes/".$image_arme."' /><br /><b>$nom_arme</b></td><td align='center'>$poids_arme</td><td align='center'>$prix_vente_final</td>";
									echo "<td align=\"center\"><input type='submit' name='vente_arme' value='Vendre' />";
									echo "<input type='hidden' name='hid_vente_arme' value='".$id_arme."' />";
									echo "</td></tr>";
													
									echo "</form>";
								}
								echo "</table><br />";
									
								// Armures
								// Récupération des armures que posséde le perso (non porté)
								$sql_armures = "SELECT id_armure FROM perso_as_armure WHERE id_perso='$id_perso' AND est_portee='0'";
								$res_armures = $mysqli->query($sql_armures);
									
								echo "<table border=1 align=center width='70%'>";
								echo "<tr><th colspan='5'>Vos armures</th></tr>";
								echo "<tr><th>Armures</th><th>Poids</th><th>Prix de vente</th><th>Vente</th></tr>";
									
								while($t = $res_armures->fetch_assoc()){
									
									echo "<form method=\"post\" action=\"batiment.php?bat=$id_i_bat\">";
									
									$id_armure = $t['id_armure'];
										
									// Récupération des informations sur l'armure
									$sql_a = "SELECT nom_armure, description_armure, poids_armure, coutOr_armure, image_armure FROM armure WHERE id_armure='$id_armure'";
									$res_a = $mysqli->query($sql_a);
									$t_a = $res_a->fetch_assoc();
									
									$nom_armure = $t_a["nom_armure"];
									$description_armure = $t_a["description_armure"];
									$poids_armure = $t_a["poids_armure"];
									$coutOr_armure = $t_a["coutOr_armure"];
									$image_armure = $t_a["image_armure"];
										
									// Calcul du prix de vente
									$prix_vente_final = ceil($coutOr_armure / 2);
										
									echo "<tr><td align='center'><img src='../images/armures/".$image_armure."' /><br /><b>$nom_armure</b></td><td align='center'>$poids_armure</td><td align='center'>$prix_vente_final</td>";
									echo "<td align=\"center\"><input type='submit' name='vente_armure' value='Vendre' />";
									echo "<input type='hidden' name='hid_vente_armure' value='".$id_armure."' />";
									echo "</td></tr>";
													
									echo "</form>";
								}
								echo "</table><br />";
							}
							else {
								// Vos armes / armures / Ressources à vendre
								echo "<center><a href=\"batiment.php?bat=$id_i_bat&vente=ok\">Vendre vos biens</a></center>";
							}
						}
						
						////////////////////
						// entrepot d'armes
						if($id_bat == '6'){
							
							///////////////////////////////////
							// On veut faire un dépot de ressources
							if(isset($_GET['depot']) && $_GET['depot'] == 'ok'){
								
								echo "<center><a href=\"batiment.php?bat=$id_i_bat\">Fermer la partie sur le dépot de ressources</a></center>";
								
								// Récupération des ressources (M ou MSP) que posséde le perso
								$sql = "SELECT DISTINCT objet.id_objet FROM perso_as_objet, objet WHERE id_perso='$id_perso'
										AND perso_as_objet.id_objet=objet.id_objet
										AND (type_objet='M' OR type_objet='MSP')";
								$res = $mysqli->query($sql);
								
								echo "<table border='1' align='center' width='70%'>";
								echo "<tr><th>Objet</th><th>Poids</th><th>Quantité possédée</th><th colspan='2'>Nombre à déposer</th></tr>";
								
								while($t = $res->fetch_assoc()){
									
									$id_objet = $t['id_objet'];
									
									// recupération du nombre d'objets de ce type que posséde le perso
									$sql_nb = "SELECT COUNT(id_objet) as nb_obj FROM perso_as_objet WHERE id_objet='$id_objet' AND id_perso='$id_perso'";
									$res_nb = $mysqli->query($sql_nb);
									$t_nb = $res_nb->fetch_assoc();
									
									$nb_obj = $t_nb['nb_obj'];
									
									// Récupération des informations sur l'objet
									$sql_o = "SELECT nom_objet, description_objet, poids_objet FROM objet WHERE id_objet='$id_objet'";
									$res_o = $mysqli->query($sql_o);
									$t_o = $res_o->fetch_assoc();
									
									$nom_objet = $t_o["nom_objet"];
									$description_objet = $t_o["description_objet"];
									$poids_objet = $t_o["poids_objet"];
									
									$compteur_obj = 1;
									
									echo "<tr><td align='center'><img src='../images/objet".$id_objet.".png' /><br /><b>$nom_objet</b></td><td align='center'>$poids_objet</td><td align='center'>$nb_obj</td>";
									echo "<form method=\"post\" action=\"batiment.php?bat=$id_i_bat\">";
									echo "<td align='center'>";
									echo "<select name=\"select_ressources\">";
									while ( $compteur_obj <= $nb_obj){
										echo "<option value=\"$compteur_obj\">$compteur_obj</option>";
										$compteur_obj++;
									}
									echo "</select>";
									echo "</td>";
									echo "<td align=\"center\"><input type='submit' name='depot_ressources' value='Deposer' />";
									echo "<input type='hidden' name='hid_depot_ressources' value=".$id_objet." />";
									echo "</form>";
									echo "</tr>";
								}
								echo "</table>";
							}
							else {
								// Dét de ressources
								echo "<center><a href=\"batiment.php?bat=$id_i_bat&depot=ok\">Déposer des ressources</a></center>";
							}
							
							///////////////////////////////////
							// On veut récupérer de ressources
							if(isset($_GET['recup']) && $_GET['recup'] == 'ok'){
								
								echo "<center><a href=\"batiment.php?bat=$id_i_bat\">Fermer la partie sur la récupération des ressources</a></center>";
								
								// récupération des ressources disponibles dans l'entrepôt
								$sql_res = "SELECT id_ressource, nb_ressource FROM ressources_entrepot WHERE id_entrepot='$id_i_bat'";
								$res_res = $mysqli->query($sql_res);
								
								echo "<table border='1' align='center' width='70%'>";
								echo "<tr><th>Objet</th><th>Poids</th><th>Quantité entreposée</th><th colspan='2'>Nombre à récupérer</th></tr>";
								
								while($t_res = $res_res->fetch_assoc()){
									
									$id_ressource = $t_res['id_ressource'];
									$nb_ressource = $t_res['nb_ressource'];
									
									// Récupération des informations sur l'objet
									$sql_o = "SELECT nom_objet, description_objet, poids_objet FROM objet WHERE id_objet='$id_ressource'";
									$res_o = $mysqli->query($sql_o);
									$t_o = $res_o->fetch_assoc();
									
									$nom_objet = $t_o["nom_objet"];
									$description_objet = $t_o["description_objet"];
									$poids_objet = $t_o["poids_objet"];
								
									$compteur_res = 1;
									
									echo "<tr><td align='center'><img src='../images/objet".$id_ressource.".png' /><br /><b>$nom_objet</b></td><td align='center'>$poids_objet</td><td align='center'>$nb_ressource</td>";
									echo "<form method=\"post\" action=\"batiment.php?bat=$id_i_bat\">";
									echo "<td align='center'>";
									echo "<select name=\"select_recup_ressources\">";
									while ( $compteur_res <= $nb_ressource){
										echo "<option value=\"$compteur_res\">$compteur_res</option>";
										$compteur_res++;
									}
									echo "</select>";
									echo "</td>";
									echo "<td align=\"center\"><input type='submit' name='recup_ressources' value='Récupérer' />";
									echo "<input type='hidden' name='hid_recup_ressources' value=".$id_ressource." />";
									echo "</form>";
									echo "</tr>";
								}
								echo "</table>";
							}
							else {
								// Dépot de ressources
								echo "<center><a href=\"batiment.php?bat=$id_i_bat&recup=ok\">Récupérer des ressources</a></center>";
							}
							
							/////////////////////////////////////
							// achat armes et armures en tout genre
							echo "<form method=\"post\" action=\"batiment.php?bat=$id_i_bat\">";
							echo "Choix :";
							echo "<select name=\"choix\">";
							echo "<OPTION value=armes";
							if (isset($_POST["ch"])){
								if($_POST["choix"] == "armes"){
									echo " selected ";
								}
							}
							echo ">armes</option>"; 
							echo "<OPTION value=armures";
							if (isset($_POST["ch"])){
								if($_POST["choix"] == "armures"){
									echo " selected ";
								}
							}
							echo ">armures</option>";
							echo "</select>";
							echo "<input type=\"submit\" name=\"ch\" value=\"ok\">";
							echo "</form>";
							
							if (isset($_POST["ch"])){
								
								$choix = $_POST["choix"];
								
								// Armes
								if($choix == "armes") {
									
									// Armes au CàC
									echo "<table width=100% border=1>";
									echo "<tr><th colspan=7>Armes CàC</th></tr>";
									echo "<tr bgcolor=\"lightgreen\">";
									echo "<th>arme</th>";
									echo "<th>coût PA</th>";
									echo "<th>dégats</th>";
									echo "<th>poids</th>";
									echo "<th>image</th>";
									echo "<th>coût</th>";
									echo "<th>achat</th>";
									echo "</tr>";
								
									// Récupération des données des armes au CàC de qualité entre 3 et 5
									$sql = "SELECT id_arme, nom_arme, coutPa_arme, additionMin_degats, additionMax_degats, multiplicateurMin_degats, multiplicateurMax_degats, degatMin_arme, degatMax_arme, poids_arme, coutOr_arme, image_arme FROM arme 
											WHERE porteeMin_arme = 1 AND porteeMax_arme = 1 AND qualite_arme < 6 AND qualite_arme > 2";
									$res = $mysqli->query($sql);
									$nb = $res->num_rows;
									
									if($nb){
										
										while ($t = $res->fetch_assoc()) {
											
											echo "<form method=\"post\" action=\"batiment.php?bat=$id_i_bat\">";
											
											$id_arme = $t["id_arme"];
											$nom_arme = $t["nom_arme"];
											$coutPa_arme = $t["coutPa_arme"];
											$additionMinDegats_arme = $t["additionMin_degats"];
											$additionMaxDegats_arme = $t["additionMax_degats"];
											$multiplicateurMinDegats_arme = $t["multiplicateurMin_degats"];
											$multiplicateurMaxDegats_arme = $t["multiplicateurMax_degats"];
											$degatMin_arme = $t["degatMin_arme"];
											$degatMax_arme = $t["degatMax_arme"];
											$poids_arme = $t["poids_arme"];
											$coutOr_arme = $t["coutOr_arme"];
											$image_arme = $t["image_arme"];
											
											// Calcul du rabais
											$rabais = floor(($coutOr_arme * $pourcentage_rabais)/100);
											
											// Affichage des données
											if($nom_arme != "poing") {
												
												echo "<tr><td><center>$nom_arme</center></td><td><center>$coutPa_arme</center></td>";
												
												if($degatMin_arme && $degatMax_arme){
													echo "<td><center>$degatMin_arme -- $degatMax_arme</center></td>";
												}
												else {
													if($multiplicateurMinDegats_arme || $multiplicateurMaxDegats_arme){
														echo "<td><center>D";
														if($multiplicateurMinDegats_arme != 1)
															echo "*".$multiplicateurMinDegats_arme;
														echo " + $additionMinDegats_arme -- D";
														if($multiplicateurMaxDegats_arme != 1)
															echo "*".$multiplicateurMaxDegats_arme;
														echo " + $additionMaxDegats_arme</center></td>";
													}
													else {
														echo "<td><center> - </center></td>";
													}
												}
												
												echo "<td><center>$poids_arme</center></td><td align=\"center\"><img src=\"../images/armes/$image_arme\" width=\"40\" height=\"40\"></td>";?>
												<td><?php echo "<center>".$coutOr_arme;
												if($rabais) {
													$new_coutOr_arme = $coutOr_arme - $rabais;
													echo "<font color='blue'> ($new_coutOr_arme)</font>";
												}
												echo "</center></td>"; 
												echo "<td align=\"center\"><input type='submit' name='achat_arme' value='Acheter' />";
												echo "<input type='hidden' name='hid_achat_arme' value=".$id_arme." />";
												echo "</td></tr>";
												
												echo "</form>";
											}
										}
									}
									else {
										echo "<tr><td align='center' colspan='7'><i>Aucunes armes au CàC disponibles pour le moment</i></td></tr>";
									}
									echo "</table>";
									echo "<br>";
									
									// Armes à Distance
									echo "<table width=100% border=1>";
									echo "<tr><th colspan=9>Armes Dist</th></tr>";
									echo "<tr bgcolor=\"lightgreen\">";
									echo "<th>arme</th>";
									echo "<th>portée</th>";
									echo "<th>coût PA</th>";
									echo "<th>dégats</th>";
									echo "<th>dégats de zone ?</th>";
									echo "<th>poids</th>";
									echo "<th>image</th>";
									echo "<th>coût</th>";
									echo "<th>achat</th>";
									
									// Récupération des données des armes à distance de qualité entre 3 et 5
									$sql2 = "SELECT id_arme, nom_arme, porteeMin_arme, porteeMax_arme, coutPa_arme, additionMin_degats, additionMax_degats, multiplicateurMin_degats, multiplicateurMax_degats, degatMin_arme, degatMax_arme, degatZone_arme, poids_arme, coutOr_arme, image_arme FROM arme 
											 WHERE porteeMax_arme > 1 AND qualite_arme < 6 AND qualite_arme > 2";
									$res2 = $mysqli->query($sql2);
									$nb2 = $res2->num_rows;
									
									if($nb2){
										while ($t2 = $res2->fetch_assoc()) {
											
											echo "<form method=\"post\" action=\"batiment.php?bat=$id_i_bat\">";
										
											$id_arme2 = $t2["id_arme"];
											$nom_arme2 = $t2["nom_arme"];
											$porteeMin_arme2 = $t2["porteeMin_arme"];
											$porteeMax_arme2 = $t2["porteeMax_arme"];
											$coutPa_arme2 = $t2["coutPa_arme"];
											$additionMinDegats_arme2 = $t2["additionMin_degats"];
											$additionMaxDegats_arme2 = $t2["additionMax_degats"];
											$multiplicateurMinDegats_arme2 = $t2["multiplicateurMin_degats"];
											$multiplicateurMaxDegats_arme2 = $t2["multiplicateurMax_degats"];
											$degatMin_arme2 = $t2["degatMin_arme"];
											$degatMax_arme2 = $t2["degatMax_arme"];
											$degatZone_arme2 = $t2["degatZone_arme"];
											$poids_arme2 = $t2["poids_arme"];
											$coutOr_arme2 = $t2["coutOr_arme"];
											$image_arme2 = $t2["image_arme"];
											
											// Calcul du rabais
											$rabais = floor(($coutOr_arme2 * $pourcentage_rabais)/100);
											
											echo "<tr><td><center>$nom_arme2</center></td><td><center>$porteeMin_arme2 - $porteeMax_arme2</center></td><td><center>$coutPa_arme2</center></td>";
											
											if($degatMin_arme2 && $degatMax_arme2){
												echo "<td><center>$degatMin_arme2 -- $degatMax_arme2</center></td>";
											}
											else {
												echo "<td><center>D";
												if($multiplicateurMinDegats_arme2 != 1)
													echo "*".$multiplicateurMinDegats_arme2;
												echo " + $additionMinDegats_arme2 -- D";
												if($multiplicateurMaxDegats_arme2 != 1)
													echo "*".$multiplicateurMaxDegats_arme2;
												echo " + $additionMaxDegats_arme2</center></td>";
											}
											echo "<td>";
											if ($degatZone_arme2){
												echo "<center>oui</center></td>";
											}
											else{
												echo "<center>non</center></td>";
											}
											echo "<td><center>$poids_arme2</center></td><td align=\"center\"><img src=\"../images/armes/$image_arme2\" width=\"40\" height=\"40\"></td>";?>
											<td><?php echo "<center>".$coutOr_arme2;
											if($rabais) {
												$new_coutOr_arme2 = $coutOr_arme2 - $rabais;
												echo "<font color='blue'> ($new_coutOr_arme2)</font>";
											}
											echo "</center></td>"; 
											echo "<td align=\"center\"><input type='submit' name='achat_arme' value='Acheter' />";
											echo "<input type='hidden' name='hid_achat_arme' value=".$id_arme2." />";
											echo "</td></tr>";
											
											echo "</form>";
										}
									}
									else {
										echo "<tr><td align='center' colspan='9'><i>Aucunes armes à distance disponibles pour le moment</i></td></tr>";
									}
									echo "</table>";
								}
								
								
								// Armures
								if($choix == "armures") {
									
									echo "<table width=100% border=1>";
									echo "<tr><th colspan=6>Armures</th></tr>";
									echo "<tr bgcolor=\"lightgreen\">";
									echo "<th>armure</th>";
									echo "<th>defense</th>";
									echo "<th>poids</th>";
									echo "<th>image</th>";
									echo "<th>coût</th>";
									echo "<th>achat</th>";
									echo "</tr>";
								
									// récupération des données des armures de qualité entre 3 et 5
									$sql = "SELECT id_armure, nom_armure, poids_armure, coutOr_armure, image_armure, corps_armure, bonusDefense_armure FROM armure 
											WHERE qualite_armure < 6 AND qualite_armure > 2
											ORDER BY corps_armure, coutOr_armure";
									$res = $mysqli->query($sql);
									$nb_armure = $res->num_rows;
									
									if($nb_armure){
										while ($t = $res->fetch_assoc()) {
											
											echo "<form method=\"post\" action=\"batiment.php?bat=$id_i_bat\">";
											
											$id_armure = $t["id_armure"];
											$nom_armure = $t["nom_armure"];
											$poids_armure = $t["poids_armure"];
											$coutOr_armure = $t["coutOr_armure"];
											$image_armure = $t["image_armure"];
											$corps_armure = $t["corps_armure"];
											$defense_armure = $t["bonusDefense_armure"];
											
											$rabais = floor(($coutOr_armure * $pourcentage_rabais)/100);
											
											echo "<tr><td align='center'>$nom_armure</td><td align='center'>$defense_armure</td><td align='center'>$poids_armure</td><td align='center'><img src=\"../images/armures/$image_armure\"</td><td align='center'>$coutOr_armure</td>";
											echo "<td align=\"center\"><input type='submit' name='achat_armure' value='Acheter' />";
											echo "<input type='hidden' name='hid_achat_armure' value=".$t["id_armure"]." />";
											echo "</tr>";
											echo "</form>";
										}
									}
									else {
										echo "<tr><td align='center' colspan='9'><i>Aucunes armures disponibles pour le moment</i></td></tr>";
									}
								}
							}							
						}
						
						//////////////
						// hopital
						if($id_bat == '7'){
							
							// Objets de soin
							echo "<table width=100% border=1>";
							echo "<tr><th colspan=6>Objets de soin</th></tr>";
							echo "<tr bgcolor=\"lightgreen\">";
							echo "<th>objet</th>";
							echo "<th>image</th>";
							echo "<th>description</th>";
							echo "<th>poids</th>";
							echo "<th>coût</th>";
							echo "<th>achat</th>";
							echo "</tr>";
							
							// achat potions en tout genre + alcool ^^
							// Objets de type S = Soin; SP et SSP = Soin Spécial
							$sql = "SELECT * FROM objet WHERE type_objet = 'S' OR type_objet = 'SP' OR type_objet = 'SSP' or id_objet='4' ORDER BY coutOr_objet";
							$res = $mysqli->query($sql);
							$nb = $res->num_rows;
							
							if($nb){
								while ($t = $res->fetch_assoc()) {
									
									echo "<form method=\"post\" action=\"batiment.php?bat=$id_i_bat\">";
								
									$id_o = $t["id_objet"];
									$nom_o = $t["nom_objet"];
									$image_o = "objet".$id_o.".png";
									$description_o = $t["description_objet"];
									$poid_o = $t["poids_objet"];
									$cout_o = $t["coutOr_objet"];
									
									// Calcul du rabais
									$rabais = floor(($cout_o * $pourcentage_rabais)/100);
									
									echo "<tr><td><center>$nom_o</center></td><td align='center'><img src=\"../images/$image_o\" width=\"40\" height=\"40\"></td><td><center>$description_o</center></td><td><center>$poid_o</center></td>";?>
									<td><?php echo "<center>".$cout_o;
									if($rabais) {
										$new_cout_o = $cout_o - $rabais;
										echo "<font color='blue'> ($new_cout_o)</font>";
									}
									echo "</center></td>";
									echo "<td align=\"center\"><input type='submit' name='achat_objet' value='Acheter' />";
									echo "<input type='hidden' name='hid_achat_objet' value=".$id_o." />";
									echo "</td></tr>";
									
									echo "</form>";
								}
							}
						}
						
						/////////////////////
						// forts et fortins
						if($id_bat == '8' || $id_bat == '9'){
						
							// Armes, Armures et Objets
							echo "<form method=\"post\" action=\"batiment.php?bat=$id_i_bat\">";
							echo "Choix :";
							echo "<select name=\"choix\">";
							echo "<OPTION value=objets"; 
							if (isset($_POST["ch"])){
								if($_POST["choix"] == "objets"){
									echo " selected ";
								}
							}
							echo">objets</option>"; 
							echo "<OPTION value=armes";
							if (isset($_POST["ch"])){
								if($_POST["choix"] == "armes"){
									echo " selected ";
								}
							}
							echo ">armes</option>"; 
							echo "<OPTION value=armures";
							if (isset($_POST["ch"])){
								if($_POST["choix"] == "armures"){
									echo " selected ";
								}
							}
							echo ">armures</option>";
							echo "</select>";
							echo "<input type=\"submit\" name=\"ch\" value=\"ok\">";
							echo "</form>";
							
							if (isset($_POST["ch"])){
								
								$choix = $_POST["choix"];
						
								// Objets
								if($choix == "objets"){
									echo "<form method=\"post\" action=\"batiment.php?bat=$id_i_bat\">";
									
									echo "<table width=100% border=1>";
									echo "<tr><th colspan=6>Objets</th></tr>";
									echo "<tr bgcolor=\"lightgreen\">";
									echo "<th>objet</th>";
									echo "<th>poids</th>";
									echo "<th>image</th>";
									echo "<th>description</th>";
									echo "<th>cout</th>";
									echo "<th>achat</th>";
									echo "</tr>";
									
									//possibilité achat objets de base
									$sql = "SELECT * from objet where type_objet='N'";
									$res = $mysqli->query($sql);
									$nb = $res->num_rows;
									
									if($nb){
										while ($t = $res->fetch_assoc()) {
											
											echo "<form method=\"post\" action=\"batiment.php?bat=$id_i_bat\">";
										
											$id_objet = $t["id_objet"];
											$nom_objet = $t["nom_objet"];
											$poids_objet = $t["poids_objet"];
											$coutOr_objet = $t["coutOr_objet"];
											$description_objet = $t["description_objet"];
											
											$image_objet = "objet".$id_objet.".png";
											
											// rabais
											$rabais = floor(($coutOr_objet * $pourcentage_rabais)/100);
											
											echo "<tr><td><center>$nom_objet</center></td><td><center>$poids_objet</center></td><td align='center'><img src=\"../images/$image_objet\" width=\"40\" height=\"40\" ></td><td>$description_objet</td>";?>
											<td><?php echo "<center>".$coutOr_objet;
											if($rabais) {
												$new_coutOr_objet = $coutOr_objet - $rabais;
												echo "<font color='blue'> ($new_coutOr_objet)</font>";
											}
											echo "</center></td>";
											echo "<td align=\"center\"><input type='submit' name='achat_objet' value='Acheter' />";
											echo "<input type='hidden' name='hid_achat_objet' value=".$id_objet." />";
											echo "</td></tr>";
											
											echo "</form>";
										}
									}
									else {
										echo "<tr><td align='center' colspan='6'><i>Aucun objet disponible pour le moment</i></td></tr>";
									}
								}
								
								// Armes
								if($choix == "armes") {
								
									// Armes au CaC
									echo "<table width=100% border=1>";
									echo "<tr><th colspan=7>Armes CàC</th></tr>";
									echo "<tr bgcolor=\"lightgreen\">";
									echo "<th>arme</th>";
									echo "<th>coût PA</th>";
									echo "<th>dégats</th>";
									echo "<th>poids</th>";
									echo "<th>image</th>";
									echo "<th>coût</th>";
									echo "<th>achat</th>";
									echo "</tr>";
								
									// Récupération des données des armes de CàC de niveau égal à 6
									$sql = "SELECT id_arme, nom_arme, coutPa_arme, degatMin_arme, degatMax_arme, valeur_des_arme, precision_arme, poids_arme, coutOr_arme, image_arme 
											FROM arme
											WHERE porteeMin_arme = 1 
											AND porteeMax_arme = 1
											AND coutOr_arme > 0";
									$res = $mysqli->query($sql);
									$nb = $res->num_rows;
									
									if($nb){
										while ($t = $res->fetch_assoc()) {
											
											echo "<form method=\"post\" action=\"batiment.php?bat=$id_i_bat\">";
										
											$id_arme 			= $t["id_arme"];
											$nom_arme 			= $t["nom_arme"];
											$coutPa_arme 		= $t["coutPa_arme"];
											$degatMin_arme 		= $t["degatMin_arme"];
											$degatMax_arme 		= $t["degatMax_arme"];
											$valeur_des_arme 	= $t["valeur_des_arme"];
											$precision_arme		= $t["precision_arme"];
											$coutOr_arme 		= $t["coutOr_arme"];
											$image_arme 		= $t["image_arme"];
											$poids_arme			= $t["poids_arme"];
											
											// rabais
											$rabais = floor(($coutOr_arme * $pourcentage_rabais)/100);
											
											if($nom_arme != "poing") {
												echo "<tr><td><center>$nom_arme</center></td><td><center>$coutPa_arme</center></td>";
												if($degatMin_arme && $valeur_des_arme){
													echo "<td><center>" . $degatMin_arme . "D". $valeur_des_arme ."</center></td>";
												}
												else {
													echo "<td><center> - </center></td>";
												}
												echo "<td><center>$poids_arme</center></td><td align=\"center\"><img src=\"../images/armes/$image_arme\" width=\"40\" height=\"40\"></td>";?>
												<td><?php echo "<center>".$coutOr_arme;
												if($rabais) {
													$new_coutOr_arme = $coutOr_arme - $rabais;
													echo "<font color='blue'> ($new_coutOr_arme)</font>";
												}
												echo "</center></td>"; 
												echo "<td align=\"center\"><input type='submit' name='achat_arme' value='Acheter' />";
												echo "<input type='hidden' name='hid_achat_arme' value=".$t["id_arme"]." />";
												echo "</td></tr>";
											}
											echo "</form>";
										}
									}
									else {
										echo "<tr><td align='center' colspan='7'><i>Aucunes armes au CàC disponibles pour le moment</i></td></tr>";
									}
									echo "</table>";
									echo "<br>";
									echo "<table width=100% border=1>";
									
									// Armes à Distance
									echo "<tr><th colspan=9>Armes Dist</th></tr>";
									echo "<tr bgcolor=\"lightgreen\">";
									echo "<th>arme</th>";
									echo "<th>portée</th>";
									echo "<th>coût PA</th>";
									echo "<th>dégats</th>";
									echo "<th>dégats de zone ?</th>";
									echo "<th>poids</th>";
									echo "<th>image</th>";
									echo "<th>coût</th>";
									echo "<th>achat</th>";
									
									// Récupération des données des armes à distance de qualité égal à 6
									$sql2 = "SELECT id_arme, nom_arme, porteeMin_arme, porteeMax_arme, coutPa_arme, degatMin_arme, degatMax_arme, valeur_des_arme, precision_arme, degatZone_arme, poids_arme, coutOr_arme, image_arme 
												FROM arme
												WHERE porteeMax_arme > 1
												AND coutOr_arme > 0";
									$res2 = $mysqli->query($sql2);
									$nb2 = $res2->num_rows;
									
									if($nb2){
										while ($t2 = $res2->fetch_assoc()) {
											
											echo "<form method=\"post\" action=\"batiment.php?bat=$id_i_bat\">";
										
											$id_arme2 			= $t2["id_arme"];
											$nom_arme2 			= $t2["nom_arme"];
											$porteeMin_arme2 	= $t2["porteeMin_arme"];
											$porteeMax_arme2 	= $t2["porteeMax_arme"];
											$coutPa_arme2 		= $t2["coutPa_arme"];
											$valeur_des_arme2 	= $t2["valeur_des_arme"];
											$precision_arme2 	= $t2["precision_arme"];
											$degatMin_arme2 	= $t2["degatMin_arme"];
											$degatMax_arme2 	= $t2["degatMax_arme"];
											$degatZone_arme2 	= $t2["degatZone_arme"];
											$poids_arme2 		= $t2["poids_arme"];
											$coutOr_arme2 		= $t2["coutOr_arme"];
											$image_arme2 		= $t2["image_arme"];
											
											// Calcul rabais
											$rabais = floor(($coutOr_arme2 * $pourcentage_rabais)/100);
											
											echo "<tr><td><center>$nom_arme2</center></td><td><center>$porteeMin_arme2 - $porteeMax_arme2</center></td><td><center>$coutPa_arme2</center></td>";
											if($degatMin_arme2 && $valeur_des_arme2){
												echo "<td><center>" . $degatMin_arme2 . "D" . $valeur_des_arme2 . "</center></td>";
											}
											else {
												echo "<td><center> - </center></td>";
											}
											echo "<td>";
											if ($degatZone_arme2){
												echo "<center>oui</center></td>";
											}
											else{
												echo "<center>non</center></td>";
											}
											echo "<td><center>$poids_arme2</center></td><td align=\"center\"><img src=\"../images/armes/$image_arme2\" width=\"40\" height=\"40\"></td>";?>
											<td><?php echo "<center>".$coutOr_arme2;
											if($rabais) {
												$new_coutOr_arme2 = $coutOr_arme2 - $rabais;
												echo "<font color='blue'> ($new_coutOr_arme2)</font>";
											}
											echo "</center></td>"; 
											echo "<td align=\"center\"><input type='submit' name='achat_arme' value='Acheter' />";
											echo "<input type='hidden' name='hid_achat_arme' value=".$t2["id_arme"]." />";
											echo "</td></tr>";
											
											echo "</form>";
										}
									}
									else {
										echo "<tr><td align='center' colspan='9'><i>Aucunes armes à distance disponibles pour le moment</i></td></tr>";
									}
									echo "</table>";
								}
								
								// Armures
								if($choix == "armures") {
									
									echo "<table width=100% border=1>";
									echo "<tr><th colspan=6>Armures</th></tr>";
									echo "<tr bgcolor=\"lightgreen\">";
									echo "<th>armure</th>";
									echo "<th>defense</th>";
									echo "<th>poids</th>";
									echo "<th>image</th>";
									echo "<th>coût</th>";
									echo "<th>achat</th>";
									echo "</tr>";
								
									// Récupération des données des armures de niveau égal à 6
									$sql = "SELECT id_armure, nom_armure, poids_armure, coutOr_armure, image_armure, corps_armure, bonusDefense_armure FROM armure 
											WHERE qualite_armure = 6
											ORDER BY corps_armure, coutOr_armure";
									$res = $mysqli->query($sql);
									$nb_armure = $res->num_rows;
									
									if($nb_armure){
										while ($t = $res->fetch_assoc()) {
											
											echo "<form method=\"post\" action=\"batiment.php?bat=$id_i_bat\">";
											
											$id_armure = $t["id_armure"];
											$nom_armure = $t["nom_armure"];
											$poids_armure = $t["poids_armure"];
											$coutOr_armure = $t["coutOr_armure"];
											$image_armure = $t["image_armure"];
											$corps_armure = $t["corps_armure"];
											$defense_armure = $t["bonusDefense_armure"];
											
											// Calcul du rabais
											$rabais = floor(($coutOr_armure * $pourcentage_rabais)/100);
											
											echo "<tr><td align='center'>$nom_armure</td><td align='center'>$defense_armure</td><td align='center'>$poids_armure</td><td align='center'><img src=\"../images/armures/$image_armure\"</td><td align='center'>$coutOr_armure</td>";
											echo "<td align=\"center\"><input type='submit' name='achat_armure' value='Acheter' />";
											echo "<input type='hidden' name='hid_achat_armure' value=".$t["id_armure"]." />";
											echo "</tr>";
											echo "</form>";
										}
									}
									else {
										echo "<tr><td align='center' colspan='9'><i>Aucunes armures disponibles pour le moment</i></td></tr>";
									}
								}
							}
						}
						
						//////////////
						// Gare
						if($id_bat == '11'){
							
							// Récupération des liaisons depuis cette gare
							$sql = "SELECT * FROM liaisons_gare WHERE id_gare1='$id_i_bat' OR id_gare2='$id_i_bat'";
							$res = $mysqli->query($sql);
							
							echo "<table width='50%' border='1' align='center'>";
							echo "<tr bgcolor=\"lightgrey\"><th>Destination</th><th>Action</th></tr>";
							
							while ($t = $res->fetch_assoc()) {
								
								$id_gare1 = $t['id_gare1'];
								$id_gare2 = $t['id_gare2'];
								
								if ($id_gare1 == $id_i_bat) {
									$destination = $id_gare2;
								} else {
									$destination = $id_gare1;
								}
								
								// Récupération infos destination
								$sql_dest = "SELECT nom_instance FROM instance_batiment WHERE id_instanceBat='$destination'";
								$res_dest = $mysqli->query($sql_dest);
								$t_dest = $res_dest->fetch_assoc();
								
								$nom_destination = "Gare " . $t_dest['nom_instance'] . "[" . $destination . "]";
								
								echo "<form method=\"post\" action=\"batiment.php?bat=$id_i_bat\">";
								
								// Achat de tickets
								echo "<tr>";
								echo "	<td align='center'>$nom_destination</td>";
								echo "	<td align='center'><input type='hidden' name='ticket_hidden' value='$destination'> <input type='submit' name='acheter_ticket' value='Acheter un ticket (5 thunes)'></td>";
								echo "</tr>";
								
								echo "</form>";
							}
							
							echo "</table>";
							
							
						}
					}
					else {
						echo "<center><font color='red'><b>Vous devez être dans le bâtiment pour accéder à sa page !</b></font></center><br /><br />";
						echo "<center><a href='jouer.php'>[ retour ]</a></center>";
					}
				}
				else {
					echo "<center><font color='red'><b>Cette page n'existe pas</b></font></center><br /><br />";
					echo "<center><a href='jouer.php'>[ retour ]</a></center>";
				}
			}
			else {
				echo "<center><font color='red'><b>Cette page n'existe pas</b></font></center><br /><br />";
				echo "<center><a href='jouer.php'>[ retour ]</a></center>";
			}
		}
	}
}