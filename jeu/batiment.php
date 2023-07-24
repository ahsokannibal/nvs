<?php
@session_start();
require_once("../fonctions.php");
require_once("f_carte.php");
require_once("f_action.php");

$mysqli = db_connexion();

include ('../nb_online.php');

// recupération config jeu
$dispo = config_dispo_jeu($mysqli);
$admin = admin_perso($mysqli, $_SESSION["id_perso"]);

if($dispo == '1' || $admin){

	if(isset($_SESSION["id_perso"])){

		$id_perso = $_SESSION['id_perso'];
		$date = time();

		$sql = "SELECT pv_perso, pa_perso, type_perso, or_perso, UNIX_TIMESTAMP(DLA_perso) as DLA, est_gele, clan FROM perso WHERE id_perso='$id_perso'";
		$res = $mysqli->query($sql );
		$tpv = $res->fetch_assoc();

		$testpv 	= $tpv['pv_perso'];
		$type_perso	= $tpv['type_perso'];
		$or 		= $tpv["or_perso"];
		$dla 		= $tpv["DLA"];
		$est_gele 	= $tpv["est_gele"];
		$camp		= $tpv['clan'];
		$pa_perso	= $tpv['pa_perso'];

		$config = '1';

		// Récupération du role du joueur
		$sql = "SELECT * FROM perso WHERE id_perso='$id_perso'";
		$res = $mysqli->query($sql);
		$j = $res->fetch_assoc();

		$id_joueur = $j["idJoueur_perso"];

		$sql = "SELECT * FROM joueur WHERE id_joueur='$id_joueur'";
		$res = $mysqli->query($sql );
		$j2 = $res->fetch_assoc();

		$isAdmin = $j2['admin_perso'];
		$isAnim = $j2['animateur'];

		// verification si le perso est encore en vie
		if ($testpv <= 0) {
			// le perso est mort
			header("Location:../tour.php");
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
						$sql = "SELECT id_batiment, camp_instance, pv_instance, pvMax_instance, nom_instance, x_instance, y_instance
								FROM instance_batiment
								WHERE id_instanceBat='$id_i_bat'";
						$res = $mysqli->query($sql);
						$t = $res->fetch_assoc();

						$id_bat 	= $t["id_batiment"];
						$camp_bat 	= $t["camp_instance"];
						$pv_bat 	= $t["pv_instance"];
						$pvMax_bat 	= $t["pvMax_instance"];
						$nom_i_bat 	= $t["nom_instance"];
						$x_i_bat	= $t["x_instance"];
						$y_i_bat	= $t["y_instance"];

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

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<title>Nord VS Sud</title>

		<!-- Required meta tags -->
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=0.3, shrink-to-fit=no">

		<!-- Bootstrap CSS -->
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
	</head>

	<body>
		<div align="center"><h2><?php echo $nom_bat." ".$nom_i_bat; ?></h2></div>
		<p align="center"><input type="button" value="Fermer cette fenêtre" onclick="window.close()"></p>
		<div align='center'><input type="button" class='btn btn-primary' onclick="window.open('evenement.php?infoid=<?php echo $id_i_bat; ?>');" value="Voir les évènements du bâtiment" /></div>
<?php
						if ($type_perso != 6) {

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

										if ($pa_perso >= 2) {

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
												$sql_m = "UPDATE perso SET or_perso=or_perso-$coutOr_arme, charge_perso=charge_perso+$poids_arme, pa_perso=pa_perso-2 WHERE id_perso='$id_perso'";
												$mysqli->query($sql_m);

												// MAJ or perso pour affichage
												$or = $or - $coutOr_arme;

												// MAJ pa perso pour affichage
												$pa_perso = $pa_perso - 2;

												echo "<font color=blue>Vous venez de vous offrir l'arme $nom_arme pour $coutOr_arme Or</font>";
											}
											else {
												echo "<font color=red>Vous n'avez pas assez de thunes pour vous offrir cette arme</font>";
											}
										}
										else {
											echo "<font color=red>Vous n'avez pas assez de PA pour acheter une arme</font>";
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

							/////////////////////
							// on achete un objet
							if(isset($_POST["achat_objet"])) {

								// recuperation de l'id de l'objet
								$id_o 		= $_POST["hid_achat_objet"];
								$quantite_o = $_POST['quantite_objet'];

								// vérifier que $id_o est une valeur numérique
								$verif_id = preg_match("#^[0-9]+$#i",$id_o);

								if($verif_id) {

									// vérification que l'objet existe bien
									if(existe_objet($mysqli, $id_o)){

										if ($pa_perso >= 2) {

											// recuperation des données de l'objet
											$sql = "SELECT nom_objet, poids_objet, coutOr_objet, echangeable FROM objet WHERE id_objet='$id_o'";
											$res = $mysqli->query($sql);
											$t_o = $res->fetch_assoc();

											$nom_o 		= $t_o["nom_objet"];
											$poids_o 	= $t_o["poids_objet"] * $quantite_o;
											$coutOr_o 	= $t_o["coutOr_objet"] * $quantite_o;

											// calcul rabais
											if($nb_points_marchandage){
												$rabais = floor(($coutOr_o * $pourcentage_rabais)/100);
												$coutOr_o = $coutOr_o - $rabais;
											}

											//verification de l'or du perso
											if ($coutOr_o <= $or){

												//On met à jour le perso (or + charge)
												$sql = "UPDATE perso SET or_perso=or_perso-$coutOr_o, charge_perso=charge_perso+$poids_o, pa_perso=pa_perso-2 WHERE id_perso='$id_perso'";
												$mysqli->query($sql);

												for ($i = 0; $i < $quantite_o; $i++) {

													// On met l'objet dans le sac
													$sql = "INSERT INTO perso_as_objet (id_perso, id_objet) VALUES ('$id_perso','$id_o')";
													$mysqli->query($sql);

												}

												// MAJ or perso pour affichage
												$or = $or - $coutOr_o;

												// MAJ pa perso pour affichage
												$pa_perso = $pa_perso - 2;

												echo "<div align='center'><font color=blue>Vous avez acheté $quantite_o $nom_o pour $coutOr_o thunes</font></div>";
											}
											else {
												echo "<div align='center'><font color=red>Vous ne possédez pas assez de thunes pour acheter $quantite_o $nom_o : Besoin en or = $coutOr_o</font></div>";
											}
										}
										else {
											echo "<div align='center'><font color=red>Vous ne possédez pas assez de PA pour acheter un objet</font></div>";
										}
									}
									else {
										echo "<div align='center'><font color=red>L'objet demandée n'est plus en stock ou n'est plus vendue</font></div>";
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

								// On verifie que l'id de l'arme est bien une valeurs numeriques
								$verif_id = preg_match("#^[0-9]+$#i",$id_arme);

								if($verif_id){

									// On vérifie que le perso possede bien l'amure et qu'elle n'est pas équipée
									$sql_v = "SELECT id_arme FROM perso_as_arme WHERE id_arme='$id_arme' AND est_portee='0' AND id_perso='$id_perso'";
									$res_v = $mysqli->query($sql_v);
									$nb_res_v = $res_v->num_rows;

									if($nb_res_v > 0){
										// recuperation des infos sur l'arme
										$sql_a = "SELECT nom_arme, coutOr_arme, poids_arme FROM arme WHERE id_arme='$id_arme'";
										$res_a = $mysqli->query($sql_a);
										$t_a = $res_a->fetch_assoc();

										$nom_arme 		= $t_a["nom_arme"];
										$coutOr_arme 	= $t_a["coutOr_arme"];
										$poids_arme 	= $t_a["poids_arme"];

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
									$sql_v = "SELECT id_objet FROM perso_as_objet WHERE id_objet='$id_objet' AND equip_objet='0' AND id_perso='$id_perso'";
									$res_v = $mysqli->query($sql_v);
									$nb_res_v = $res_v->num_rows;

									if($nb_res_v > 0){

										// recuperation des infos sur l'objet
										$sql_o = "SELECT nom_objet, coutOr_objet, poids_objet, type_objet FROM objet WHERE id_objet='$id_objet' AND echangeable=1";
										$res_o = $mysqli->query($sql_o);
										$t_o = $res_o->fetch_assoc();

										$nom_objet 		= $t_o["nom_objet"];
										$coutOr_objet 	= $t_o["coutOr_objet"];
										$poids_objet 	= $t_o["poids_objet"];
										$type_objet 	= $t_o["type_objet"];

										// Calcul du prix de vente (selon pv)
										$prix_vente_final = ceil($coutOr_objet / 2);

										// Mise à jour de l'inventaire du perso
										$sql_d = "DELETE FROM perso_as_objet
												  WHERE id_perso='$id_perso'
												  AND id_objet='$id_objet' AND equip_objet=0 LIMIT 1";
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
										echo "Vous ne pouvez pas vendre ce que vous ne possédez pas ou ce qui est équipé.";
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

												$sql_i = "INSERT INTO perso_as_objet (id_perso, id_objet) VALUES ('$id_perso','$id_objet')";
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

									$tab_ticket_dest 	= explode(',', $_POST["ticket_hidden"]);
									$nb_ticket			= count($tab_ticket_dest);

									if ($nb_ticket > 1) {

										$thune_necessaire = 3 * $nb_ticket;

										if ($or >= $thune_necessaire) {

											for ($i = 0; $i < $nb_ticket; $i++) {

												$ticket_dest = $tab_ticket_dest[$i];

												$sql_dest = "SELECT nom_instance FROM instance_batiment WHERE id_instanceBat='$ticket_dest'";
												$res_dest = $mysqli->query($sql_dest);
												$t_dest = $res_dest->fetch_assoc();

												$nom_destination = "Gare " . $t_dest['nom_instance'] . "[" . $ticket_dest . "]";

												// MAJ thune perso
												$sql = "UPDATE perso SET or_perso=or_perso-3 WHERE id_perso='$id_perso'";
												$mysqli->query($sql);

												// Ajout de l'objet ticket de train dans l'inventaire du perso
												$sql = "INSERT INTO perso_as_objet (id_perso, id_objet, capacite_objet) VALUES ('$id_perso', '1', '$ticket_dest')";
												$mysqli->query($sql);

												// Maj thune pour affichage
												$or = $or - 3;

												echo "<center><font color='blue'>Vous avez acheté un ticket de train en destination de $nom_destination</font></center>";
											}
										}
										else {
											echo "<center><font color='red'>Vous n'avez pas suffisamment de thunes pour vous acheter tous les tickets de train</font></center>";
										}
									}
									else {

										$ticket_dest = $tab_ticket_dest[0];

										$sql_dest = "SELECT nom_instance FROM instance_batiment WHERE id_instanceBat='$ticket_dest'";
										$res_dest = $mysqli->query($sql_dest);
										$t_dest = $res_dest->fetch_assoc();

										$nom_destination = "Gare " . $t_dest['nom_instance'] . "[" . $ticket_dest . "]";

										// On vérifie que le perso possède bien 3 thunes
										if ($or >= 3) {

											// On vérifie si le perso n'a pas déjà un ticket pour la même destination
											$sql = "SELECT count(*) as nb_ticket FROM perso_as_objet WHERE id_perso='$id_perso' AND id_objet='1' AND capacite_objet='$ticket_dest'";
											$res = $mysqli->query($sql);
											$t = $res->fetch_assoc();

											$possede_deja_ticket = $t['nb_ticket'];

											if ($possede_deja_ticket == 0) {

												// MAJ thune perso
												$sql = "UPDATE perso SET or_perso=or_perso-3 WHERE id_perso='$id_perso'";
												$mysqli->query($sql);

												// Ajout de l'objet ticket de train dans l'inventaire du perso
												$sql = "INSERT INTO perso_as_objet (id_perso, id_objet, capacite_objet) VALUES ('$id_perso', '1', '$ticket_dest')";
												$mysqli->query($sql);

												// Maj thune pour affichage
												$or = $or - 3;

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
							}

							echo "<br /><div align=\"center\">Vous possédez <b>$or</b> thune(s)</div><br />";

							/////////////////
							// On veut vendre
							// Possible seulement dans : fort, fortins, hopitaux, entrepots
							if($id_bat == '6' || $id_bat == '7' || $id_bat == '8' || $id_bat == '9'){

								if(isset($_GET['vente']) && $_GET['vente'] == 'ok'){

									echo "<center><a class='btn btn-primary' href=\"batiment.php?bat=$id_i_bat\">Fermer la partie sur la vente de vos biens</a></center><br />";

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
												AND type_objet!='S' AND type_objet!='SP' AND type_objet!='SSP'
												AND objet.id_objet != '1'
												AND perso_as_objet.equip_objet = '0'
                        AND objet.echangeable = 1";
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
										$t_nb = $res_nb->fetch_assoc();

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

										echo "<tr><td align='center'><img src='../images/objets/objet".$id_objet.".png' /><br /><b>$nom_objet</b></td><td align='center'>$poids_objet</td><td align='center'>$nb_obj</td><td align='center'>$prix_vente_max</td>";
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
								}
								else {
									// Vos armes / Ressources à vendre
									echo "<center><a class='btn btn-primary' href=\"batiment.php?bat=$id_i_bat&vente=ok\">Vendre vos biens</a></center>";
								}
							}

							////////////////////
							// entrepot d'armes
							if($id_bat == '6'){

								///////////////////////////////////
								// On veut faire un dépot de ressources
								if(isset($_GET['depot']) && $_GET['depot'] == 'ok'){

									echo "<center><a class='btn btn-primary' href=\"batiment.php?bat=$id_i_bat\">Fermer la partie sur le dépot de ressources</a></center>";

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

									echo "<center><a class='btn btn-primary' href=\"batiment.php?bat=$id_i_bat\">Fermer la partie sur la récupération des ressources</a></center>";

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
								// achat armes en tout genre
								echo "<form method=\"post\" action=\"batiment.php?bat=$id_i_bat\">";
								echo "Choix :";
								echo "<select name=\"choix\" onchange=\"this.form.submit()\">";
								echo "<OPTION value=armes";
								if (isset($_POST["choix"])){
									if($_POST["choix"] == "armes"){
										echo " selected ";
									}
								}
								echo ">armes</option>";
								echo "</select>";
								echo "<input type=\"submit\" name=\"ch\" value=\"ok\">";
								echo "</form>";

								if (isset($_POST["choix"])){

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
													echo "<td align=\"center\"><input type='submit' class='btn btn-primary' name='achat_arme' value='Acheter' />";
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
												echo "<td align=\"center\"><input type='submit' class='btn btn-primary' name='achat_arme' value='Acheter' />";
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
								}
							}

							//////////////
							// hopital
							if($id_bat == '7'){

								echo "<center><font color='red'>Chaque achat coûte 2PA au perso (il vous reste ".$pa_perso." PA)</font></center>";

								// Objets de soin
								echo "<table width=100% border=1>";
								echo "<tr><th colspan='6' style='text-align:center'>Objets de soin</th></tr>";
								echo "<tr bgcolor=\"lightgreen\">";
								echo "<th style='text-align:center'>objet</th>";
								echo "<th style='text-align:center'>image</th>";
								echo "<th style='text-align:center'>description</th>";
								echo "<th style='text-align:center'>poids</th>";
								echo "<th style='text-align:center'>quantité</th>";
								echo "<th style='text-align:center'>coût à l'unité</th>";
								echo "<th style='text-align:center'>achat</th>";
								echo "</tr>";

								// achat potions en tout genre + alcool ^^
								// Objets de type S = Soin; SP et SSP = Soin Spécial
								$sql = "SELECT * FROM objet WHERE type_objet = 'S' OR type_objet = 'SP' OR type_objet = 'SSP' or id_objet='4' ORDER BY coutOr_objet";
								$res = $mysqli->query($sql);
								$nb = $res->num_rows;

								if($nb){
									while ($t = $res->fetch_assoc()) {

										echo "<form method=\"post\" action=\"batiment.php?bat=$id_i_bat\">";

										$id_o 			= $t["id_objet"];
										$nom_o 			= $t["nom_objet"];
										$image_o 		= "objet".$id_o.".png";
										$description_o 	= $t["description_objet"];
										$poid_o 		= $t["poids_objet"];
										$cout_o 		= $t["coutOr_objet"];

										// Calcul du rabais
										$rabais = floor(($cout_o * $pourcentage_rabais)/100);

										echo "<tr>";
										echo "	<td><center>$nom_o</center></td>";
										echo "	<td align='center'><img src=\"../images/objets/$image_o\" width=\"40\" height=\"40\"></td>";
										echo "	<td><center>$description_o</center></td>";
										echo "	<td><center>$poid_o</center></td>";
										echo "	<td align='center'>";
										echo "		<select name='quantite_objet'>";
										echo "			<option value='1'>1</option>";
										echo "			<option value='2'>2</option>";
										echo "			<option value='3'>3</option>";
										echo "			<option value='4'>4</option>";
										echo "			<option value='5'>5</option>";
										echo "			<option value='6'>6</option>";
										echo "		</select>";
										echo "	</td>";
										echo "	<td>";
										echo "		<center>".$cout_o;
										if($rabais) {
											$new_cout_o = $cout_o - $rabais;
											echo "<font color='blue'> ($new_cout_o)</font>";
										}
										echo "		</center>";
										echo "	</td>";
										echo "	<td align=\"center\"><input type='submit' class='btn btn-primary' name='achat_objet' value='Acheter' ";
										if ($pa_perso < 2) {
											echo "disabled";
										}
										echo " />";
										echo "<input type='hidden' name='hid_achat_objet' value=".$id_o." />";
										echo "	</td>";
										echo "</tr>";

										echo "</form>";
									}
								}
							}

							/////////////////////
							// forts et fortins
							if($id_bat == '8' || $id_bat == '9') {

								// Armes, Armures et Objets
								echo "<form method=\"post\" action=\"batiment.php?bat=$id_i_bat\">";
								echo "Choix :";
								echo "<select name=\"choix\" onchange=\"this.form.submit()\">";
								echo "<OPTION value=objets";
								if (isset($_POST["choix"])){
									if($_POST["choix"] == "objets"){
										echo " selected ";
									}
								}
								echo">objets</option>";
								echo "<OPTION value=armes";
								if (isset($_POST["choix"])){
									if($_POST["choix"] == "armes"){
										echo " selected ";
									}
								}
								echo ">armes</option>";
								echo "</select>";
								echo "<input type=\"submit\" name=\"ch\" value=\"ok\">";
								echo "</form>";

								if (isset($_POST["choix"])){
									$choix = $_POST["choix"];
								} else {
									$choix = "objets";
								}

								echo "<center><font color='red'>Chaque achat coûte 2PA au perso (il vous reste ".$pa_perso." PA)</font></center>";

								// Objets
								if($choix == "objets"){

									echo "<form method=\"post\" action=\"batiment.php?bat=$id_i_bat\">";

									echo "<table width=100% border=1>";
									echo "<tr><th colspan=7 style='text-align:center'>Objets</th></tr>";
									echo "<tr bgcolor=\"lightgreen\">";
									echo "	<th style='text-align:center'>Objet</th>";
									echo "	<th style='text-align:center'>Poids</th>";
									echo "	<th style='text-align:center'>Image</th>";
									echo "	<th style='text-align:center'>Description</th>";
									echo "	<th style='text-align:center'>Quantité</th>";
									echo "	<th style='text-align:center'>Coût à l'unité</th>";
									echo "	<th style='text-align:center'>Achat</th>";
									echo "</tr>";

									// possibilité achat objets de base
									// Affichage de l'étendard seulement pour les anims et admins
									if(($isAdmin || $isAnim) && $type_perso == '1'){
										$sql = "SELECT * from objet where (type_objet='N' OR type_objet='E') AND echangeable=1";
										$res = $mysqli->query($sql);
										$nb = $res->num_rows;
									} else {
										$sql = "SELECT * from objet where (type_objet='N' OR type_objet='E') AND echangeable=1 AND id_objet!='8' AND id_objet!='9'";
										$res = $mysqli->query($sql);
										$nb = $res->num_rows;
									}

									if($nb){
										while ($t = $res->fetch_assoc()) {

											echo "<form method=\"post\" action=\"batiment.php?bat=$id_i_bat\">";

											$id_objet 			= $t["id_objet"];
											$nom_objet 			= $t["nom_objet"];
											$poids_objet 		= $t["poids_objet"];
											$coutOr_objet 		= $t["coutOr_objet"];
											$description_objet 	= $t["description_objet"];

											$image_objet = "objet".$id_objet.".png";

											// rabais
											$rabais = floor(($coutOr_objet * $pourcentage_rabais)/100);

											echo "<tr>";
											echo "	<td align='center'>$nom_objet</td>";
											echo "	<td align='center'>$poids_objet</td>";
											echo "	<td align='center'><img src=\"../images/objets/$image_objet\" width=\"40\" height=\"40\" ></td>";
											echo "	<td>$description_objet</td>";
											echo "	<td align='center'>";
											echo "		<select name='quantite_objet'>";
											echo "			<option value='1'>1</option>";
											echo "			<option value='2'>2</option>";
											echo "			<option value='3'>3</option>";
											echo "			<option value='4'>4</option>";
											echo "			<option value='5'>5</option>";
											echo "			<option value='6'>6</option>";
											echo "		</select>";
											echo "	</td>";
											echo "	<td align='center'>";
											echo $coutOr_objet;
											if($rabais) {
												$new_coutOr_objet = $coutOr_objet - $rabais;
												echo "<font color='blue'> ($new_coutOr_objet)</font>";
											}
											echo "</td>";
											echo "	<td align='center'><input type='submit' class='btn btn-primary' name='achat_objet' value='Acheter' ";
											if ($pa_perso < 2) {
												echo "disabled";
											}
											echo " />";
											echo "<input type='hidden' name='hid_achat_objet' value=".$id_objet." />";
											echo "	</td>";
											echo "</tr>";

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
									echo "<tr><th colspan=10 style='text-align:center'>Armes CàC</th></tr>";
									echo "<tr bgcolor=\"lightgreen\">";
									echo "	<th style='text-align:center'>Arme</th>";
									echo "	<th style='text-align:center'>Image</th>";
									echo "	<th style='text-align:center'>Unité(s)</th>";
									echo "	<th style='text-align:center'>Coût PA</th>";
									echo "	<th style='text-align:center'>Précision</th>";
									echo "	<th style='text-align:center'>Dégats</th>";
									echo "	<th style='text-align:center'>Poids</th>";
									echo "	<th style='text-align:center'>Quantité</th>";
									echo "	<th style='text-align:center'>Coût</th>";
									echo "	<th style='text-align:center'>Achat</th>";
									echo "</tr>";

									// Récupération des données des armes de CàC de niveau égal à 6
									$sql = "SELECT arme.id_arme, nom_arme, coutPa_arme, degatMin_arme, degatMax_arme, valeur_des_arme, precision_arme, poids_arme, coutOr_arme, image_arme
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

												echo "<tr>";
												echo "	<td><center>$nom_arme</center></td>";
												echo "	<td align=\"center\"><img src=\"../images/armes/$image_arme\" width=\"40\" height=\"40\"></td>";

												echo "	<td><center>";
												$sql_u = "SELECT nom_unite FROM type_unite, arme_as_type_unite
															WHERE type_unite.id_unite = arme_as_type_unite.id_type_unite
															AND arme_as_type_unite.id_arme = '$id_arme'";
												$res_u = $mysqli->query($sql_u);
												$liste_unite = "";
												while ($t_u = $res_u->fetch_assoc()) {
													$nom_unite = $t_u["nom_unite"];

													if ($liste_unite != "") {
														$liste_unite .= " / ";
													}
													$liste_unite .= $nom_unite;
												}
												echo $liste_unite;
												echo "	</center></td>";

												echo "	<td><center>$coutPa_arme</center></td>";
												echo "	<td><center>".$precision_arme."%</center></td>";
												if($degatMin_arme && $valeur_des_arme){
													echo "	<td><center>" . $degatMin_arme . "D". $valeur_des_arme ."</center></td>";
												}
												else {
													echo "	<td><center> - </center></td>";
												}
												echo "	<td><center>$poids_arme</center></td>";
												echo "	<td align='center'>1</td>";
												echo "	<td>";
												echo "<center>".$coutOr_arme;
												if($rabais) {
													$new_coutOr_arme = $coutOr_arme - $rabais;
													echo "<font color='blue'> ($new_coutOr_arme)</font>";
												}
												echo "</center></td>";

												echo "	<td align=\"center\"><input type='submit' class='btn btn-primary' name='achat_arme' value='Acheter' ";
												if ($pa_perso < 2) {
													echo "disabled";
												}
												echo " />";
												echo "	<input type='hidden' name='hid_achat_arme' value=".$t["id_arme"]." />";
												echo "	</td>";
												echo "</tr>";
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
									echo "<tr><th colspan=12 style='text-align:center'>Armes Dist</th></tr>";
									echo "<tr bgcolor=\"lightgreen\">";
									echo "	<th style='text-align:center'>Arme</th>";
									echo "	<th style='text-align:center'>Image</th>";
									echo "	<th style='text-align:center'>Unités</th>";
									echo "	<th style='text-align:center'>Portée</th>";
									echo "	<th style='text-align:center'>Coût PA</th>";
									echo "	<th style='text-align:center'>Précision</th>";
									echo "	<th style='text-align:center'>Dégats</th>";
									echo "	<th style='text-align:center'>Dégats de zone ?</th>";
									echo "	<th style='text-align:center'>Poids</th>";
									echo "	<th style='text-align:center'>Quantité</th>";
									echo "	<th style='text-align:center'>Coût</th>";
									echo "	<th style='text-align:center'>Achat</th>";

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

											echo "<tr>";
											echo "	<td><center>$nom_arme2</center></td>";
											echo "	<td align=\"center\"><img src=\"../images/armes/$image_arme2\" width=\"40\" height=\"40\"></td>";

											echo "	<td><center>";
												$sql_u = "SELECT nom_unite FROM type_unite, arme_as_type_unite
															WHERE type_unite.id_unite = arme_as_type_unite.id_type_unite
															AND arme_as_type_unite.id_arme = '$id_arme2'";
												$res_u = $mysqli->query($sql_u);
												$liste_unite = "";
												while ($t_u = $res_u->fetch_assoc()) {
													$nom_unite = $t_u["nom_unite"];

													if ($liste_unite != "") {
														$liste_unite .= " / ";
													}
													$liste_unite .= $nom_unite;
												}
												echo $liste_unite;
												echo "	</center></td>";

											echo "	<td><center>$porteeMin_arme2 - $porteeMax_arme2</center></td>";
											echo "	<td><center>$coutPa_arme2</center></td>";
											echo "	<td><center>".$precision_arme2."%</center></td>";
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
											echo "	<td><center>$poids_arme2</center></td>";
											echo "	<td align='center'>1</td>";
											echo "	<td>";
											echo "<center>".$coutOr_arme2;
											if($rabais) {
												$new_coutOr_arme2 = $coutOr_arme2 - $rabais;
												echo "<font color='blue'> ($new_coutOr_arme2)</font>";
											}
											echo "</center></td>";
											echo "<td align=\"center\"><input type='submit' class='btn btn-primary' name='achat_arme' value='Acheter' ";
											if ($pa_perso < 2) {
												echo "disabled";
											}
											echo " />";
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
							}

							//////////////
							// Gare
							if($id_bat == '11') {

								// Plan gare
								if ($camp == 1) {
									$image_plan = "plan_gare_nord.png";
									$image_plan_sans_terrain = "gare_nord.png";
								}

								if ($camp == 2) {
									$image_plan = "plan_gare_sud.png";
									$image_plan_sans_terrain = "gare_sud.png";
								}
								echo "<center>";
								echo "<img src='../images/".$image_plan_sans_terrain."' class=\"img-fluid\" alt='blason gares' width='200' >";
								echo "</center><br />";

								echo "<center>";
								if (isset($_GET['afficher_plan'])) {
										echo "<img src='./carte/" . $image_plan . "' class=\"img-fluid\" alt='plan gares'/><br />";
									echo "<a href='batiment.php?bat=".$id_i_bat."' class='btn btn-info'>Cacher le plan du reseau ferré</a>";
								}
								else {
									echo "<a href='batiment.php?bat=".$id_i_bat."&afficher_plan=ok' class='btn btn-info'>Afficher le plan du reseau ferré</a>";
								}
								echo "</center>";

								echo "<br />";


								echo "<table width='50%' border='1' align='center'>";
								echo "	<tr bgcolor=\"lightgrey\"><th>Destination</th><th>Action</th></tr>";

								// On parcours d'un côté les liaisons via id_gare2
								$liaison_trouve 		= true;
								$base_dest				= $id_i_bat;
								$array_dest				= array($base_dest);
								$array_parcours			= array($base_dest);
								$array_parcours_tmp		= array();
								$array_parcours_value_dest	= array();
								$array_parcours_value_dest_tmp	= array();
								$nb_liaisons			= 1;
								$value_dest 			= "";

								$profondeur = 1;
								$prof_tmp	= 1;

								while (count($array_parcours) > 0) {

									$array_parcours_tmp = array();
									$array_parcours_value_dest_tmp = array();

									$taille_parcours = count($array_parcours);

									for ($i = 0; $i < $taille_parcours; $i++) {

										$base_dest = $array_parcours[$i];

										// Récupération des liaisons depuis cette gare
										$sql = "SELECT * FROM liaisons_gare
												WHERE (id_gare2='$base_dest' AND id_gare1 NOT IN('" . implode( "', '" , $array_dest ) . "'))
													OR (id_gare1='$base_dest' AND id_gare2 NOT IN('" . implode( "', '" , $array_dest ) . "'))";
										$res = $mysqli->query($sql);

										unset($array_parcours[array_search($base_dest, $array_parcours)]);

										while ($t = $res->fetch_assoc()) {

											$id_gare1 = $t['id_gare1'];
											$id_gare2 = $t['id_gare2'];

											if ($id_gare1 == $base_dest) {
												$destination = $id_gare2;
											}
											else {
												$destination = $id_gare1;
											}

											// Récupération infos destination
											$sql_dest = "SELECT nom_instance, camp_instance FROM instance_batiment WHERE id_instanceBat='$destination'";
											$res_dest = $mysqli->query($sql_dest);
											$t_dest = $res_dest->fetch_assoc();

											$camp_dest 	= $t_dest['camp_instance'];
											$nom_dest	= $t_dest['nom_instance'];

											$nom_destination = "Gare " . $nom_dest . "[<a href='evenement.php?infoid=".$destination."' target='_blank'>".$destination."</a>]";

											$cout_thune = $profondeur * 3;

											if ($profondeur == 1) {
												$value_dest = $destination;
											}
											else {
												$value_dest = $array_parcours_value_dest[$i].",".$destination;
											}

											if ($camp_dest == $camp) {

												echo "<form method=\"post\" action=\"batiment.php?bat=$id_i_bat\">";

												// Achat de tickets
												echo "<tr>";
												echo "	<td align='center'>$nom_destination - (tickets : $value_dest)</td>";
												echo "	<td align='center'><input type='hidden' name='ticket_hidden' value='$value_dest'> <input type='submit' class='btn btn-primary' name='acheter_ticket' value='Acheter un ticket (".$cout_thune." thunes)'></td>";
												echo "</tr>";

												echo "</form>";
											}
											else {
												echo "<tr>";
												echo "	<td align='center'>$nom_destination</td>";
												echo "	<td align='center'>Gare aux mains de l'ennemi, impossible d'acheter un ticket</td>";
												echo "</tr>";
											}

											array_push($array_dest, $destination);
											array_push($array_parcours_tmp, $destination);
											array_push($array_parcours_value_dest_tmp, $value_dest);
										}
									}

									$array_parcours = $array_parcours_tmp;
									$array_parcours_value_dest = $array_parcours_value_dest_tmp;

									$profondeur++;
								}

								echo "</table>";

							}
						}
					}
					else {
						echo "<center><font color='red'><b>Vous devez être dans le bâtiment pour accéder à sa page !</b></font></center><br /><br />";
						echo "<center><a href='jouer.php' class='btn btn-primary'>retour</a></center>";
					}
				}
				else {
					echo "<center><font color='red'><b>Cette page n'existe pas</b></font></center><br /><br />";
					echo "<center><a href='jouer.php' class='btn btn-primary'>retour</a></center>";
				}
			}
			else {
				echo "<center><font color='red'><b>Cette page n'existe pas</b></font></center><br /><br />";
				echo "<center><a href='jouer.php' class='btn btn-primary'>retour</a></center>";
			}
		}
	}
	?>
		<!-- Optional JavaScript -->
		<!-- jQuery first, then Popper.js, then Bootstrap JS -->
		<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
		<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>

		<script>
		$(function () {
			$('[data-toggle="tooltip"]').tooltip();
			$('[data-toggle="popover"]').popover();
		})
		</script>

	</body>
</html>
<?php
}
else {
	// logout
	$_SESSION = array(); // On ecrase le tableau de session
	session_destroy(); // On detruit la session

	header("Location:../index2.php");
}
