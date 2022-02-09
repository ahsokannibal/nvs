<?php
session_start();
require_once("fonctions.php");
require_once("jeu/f_carte.php");

$mysqli = db_connexion();

include ('nb_online.php');

define('IN_PHPBB', true);
$phpEx = 'php';

$phpbb_root_path = './forum/';
if (is_dir($phpbb_root_path))
{
	require_once($phpbb_root_path ."common.php");
	require_once($phpbb_root_path ."includes/functions_user.php");

	$request->enable_super_globals();
}

if(config_dispo_jeu($mysqli) == '1'){

	if(isset($_POST['creer'])) {
		$_SESSION['old_input'] = $_POST;
		
		if (isset($_POST['cgu']) && $_POST['cgu']=="on" && isset($_POST['charte']) && $_POST['charte']=="on") {
			
			if ($_POST['camp_perso'] != '0') {
		
				$nom_perso 	= trim(filter_input(INPUT_POST, "nom_perso", FILTER_SANITIZE_STRING));
				$email_joueur 	= $_POST['email_joueur'];
				$nom_bataillon	= $_POST['nom_bataillon'];
				$mdp_joueur 	= $_POST['mdp_joueur'];
				$camp 			= $_POST['camp_perso'];
			
				if (strlen($nom_perso) < 2 || strlen($nom_perso) > 25 ||ctype_digit($nom_perso) || strpos($nom_perso,'--') !== false){
					$error = "Erreur : Le Pseudo est incorrect ! Veuillez en choisir un autre (taille entre 1 et 25, pas de quote, pas que des chiffres, pas la chaine --, etc..)";
				}
				else {
					$nom_perso = $mysqli->real_escape_string($nom_perso);
					$user_found_in_forum = false;
					if (is_dir($phpbb_root_path))
					{
						$sql = "SELECT username FROM ".$table_prefix."users WHERE username='".$nom_perso."'";
						$resultat_user_forum = $mysqli->query($sql);
						if( $resultat_user_forum->num_rows != 0) {
							$user_found_in_forum = true;
						}
					}
					
					$sql = "SELECT nom_perso FROM perso WHERE nom_perso='".$nom_perso."'";
					$resultat_user = $mysqli->query($sql);
					
					$sql2 = "SELECT email_joueur FROM joueur WHERE email_joueur='".$email_joueur."'";
					$resultat_user2 = $mysqli->query($sql2);
						
					if( $resultat_user->num_rows != 0 || $user_found_in_forum) {
						$error = "Erreur : Le pseudo est déjà choisi ou interdit ! Veuillez en choisir un autre";
					}
					elseif ($resultat_user2->num_rows != 0) {
						$error = "Erreur : Vous avez déjà creé un perso avec cet email, un seul perso par joueur";
					}
					elseif (!filtremail($email_joueur)) {
						$error = "Erreur : Email incorrect";
					}
					elseif ($mdp_joueur == "") {
						$error = "Erreur : Veuillez entrer un mot de passe";
					}
					else {
						if($_POST['creation']=="ok") {
							unset($_SESSION['old_input']);
							
							// sécurité camp
							if($camp == "1" || $camp == "2"){
								
								$old_mdp_joueur = $mdp_joueur;
								$mdp_joueur = md5($mdp_joueur);
								
								$sql = "SELECT MAX(x_carte) as x_max, MAX(y_carte) as y_max FROM carte";
								$res = $mysqli->query($sql);
								$t = $res->fetch_assoc();
								
								$X_MAX = $t['x_max'];
								$Y_MAX = $t['y_max'];
								
								if($camp == 1){
									$x_min_spawn 		= $X_MAX - 40;
									$x_max_spawn 		= $X_MAX;
									$y_min_spawn 		= $Y_MAX - 40;
									$y_max_spawn 		= $Y_MAX;
									$image_chef 		= "cavalerie_nord.gif";
									$image_g 			= "infanterie_nord.gif";
									$group_id 			= 8;
									$nom_camp 			= 'Nordistes';
									$ncamp 				= "Nord";
									$couleur_clan_perso = "blue";
								}
								
								if($camp == 2){
									$x_min_spawn 		= 0;
									$x_max_spawn 		= 40;
									$y_min_spawn 		= 0;
									$y_max_spawn 		= 40;
									$image_chef 		= "cavalerie_sud.gif";
									$image_g 			= "infanterie_sud.gif";
									$group_id 			= 9;
									$nom_camp 			= 'Sudistes';
									$ncamp 				= "Sud";
									$couleur_clan_perso = "red";
								}
							
								$date = time();
								$dla = $date + DUREE_TOUR; // calcul dla
						
								// Caracs Chef
								$pvMax_chef 	= 850;
								$pmMax_chef 	= 10;
								$pamax_chef 	= 10;
								$recup_chef 	= 40;
								$perc_chef 		= 5;
								$protec_chef 	= 20;
								
								// Carac grouillot
								$pvMax_g 	= 500;
								$pmMax_g 	= 5;
								$pamax_g 	= 10;
								$recup_g 	= 30;
								$perc_g 	= 4;
								$protec_g 	= 10;
								$nom_g 		= $nom_perso."_junior";
								
								// securité
								$sql = "select email_joueur from joueur,perso where nom_perso='$nom_perso'";
								$res = $mysqli->query($sql);
								$num = $res->num_rows;
								if($num != 0){
									echo "Evitez de bourriner sur l'image ^^ Votre perso est tout de même créé : <a href=\"index.php?creation=ok\">jouer</a>";
								}
								else {
											
									// insertion du nouveau joueur
									$lock = "LOCK TABLE joueur WRITE";
									$mysqli->query($lock);
									
									$insert_j = "INSERT INTO joueur (email_joueur, mdp_joueur) VALUES ('$email_joueur', '$mdp_joueur')";
									$result_j = $mysqli->query($insert_j);
									$IDJoueur_perso = $mysqli->insert_id;
									
									$unlock = "UNLOCK TABLES";
									$mysqli->query($unlock);
									
									$bat_spawn_dispo = false;
									
									// verification si fort pas en siége présent pour spawn
									$sql = "SELECT x_instance, y_instance, id_instanceBat, contenance_instance, pv_instance, pvMax_instance FROM instance_batiment WHERE camp_instance='$camp' AND id_batiment='9'";
									$res = $mysqli->query($sql);
									
									while($t = $res->fetch_assoc()) {
									
										$x 				= $t['x_instance'];
										$y 				= $t['y_instance'];
										$id_bat 		= $t['id_instanceBat'];
										$contenance_bat = $t['contenance_instance'];
										$pv_bat 		= $t['pv_instance'];
										$pvMax_bat 		= $t['pvMax_instance'];
										
										// calcul pourcentage pv bat 
										$pourcentage_pv_bat = ceil(($pv_bat * 100) / $pvMax_bat);
										
										$nb_ennemis_siege = nb_ennemis_siege_batiment($mysqli, $x, $y, $camp);
										
										// Récupération du nombre de perso dans ce batiment
										$sql_n = "SELECT count(id_perso) as nb_perso_bat FROM perso_in_batiment WHERE id_instanceBat='$id_bat'";
										$res_n = $mysqli->query($sql_n);
										$t_n = $res_n->fetch_assoc();
										$nb_perso_bat = $t_n['nb_perso_bat'];
										
										if($contenance_bat > $nb_perso_bat + 1 && $pourcentage_pv_bat >= 90 && $nb_ennemis_siege < 10){
											// Le perso peut spawn dans le fort avec son grouillot
											$bat_spawn_dispo = true;
											
											break;
										}
									}
									
									// Le perso ne peut pas respawn dans les forts
									if (!$bat_spawn_dispo) {
										
										// Verification des fortins
										$sql = "SELECT x_instance, y_instance, id_instanceBat, contenance_instance, pv_instance, pvMax_instance FROM instance_batiment WHERE camp_instance='$camp' AND id_batiment='8'";
										$res = $mysqli->query($sql);
										
										while($t = $res->fetch_assoc()) {
										
											$x 				= $t['x_instance'];
											$y 				= $t['y_instance'];
											$id_bat 		= $t['id_instanceBat'];
											$contenance_bat = $t['contenance_instance'];
											$pv_bat 		= $t['pv_instance'];
											$pvMax_bat 		= $t['pvMax_instance'];
											
											// calcul pourcentage pv bat 
											$pourcentage_pv_bat = ceil(($pv_bat * 100) / $pvMax_bat);
											
											$nb_ennemis_siege = nb_ennemis_siege_batiment($mysqli, $x, $y, $camp);
											
											// Récupération du nombre de perso dans ce batiment
											$sql_n = "SELECT count(id_perso) as nb_perso_bat FROM perso_in_batiment WHERE id_instanceBat='$id_bat'";
											$res_n = $mysqli->query($sql_n);
											$t_n = $res_n->fetch_assoc();
											$nb_perso_bat = $t_n['nb_perso_bat'];
											
											if($contenance_bat > $nb_perso_bat + 1 && $pourcentage_pv_bat >= 90 && $nb_ennemis_siege < 10){
												// Le perso peut spawn dans le fortin avec son grouillot
												$bat_spawn_dispo = true;
												
												break;
											}
											
										}
									}
									
									// Impossible de spawn dans un fort ou fortin => spawn aléatoire sur la carte
									if (!$bat_spawn_dispo) {
										
										$x = pos_zone_rand_x($x_min_spawn, $x_max_spawn);
										$y = pos_zone_rand_y($y_min_spawn, $y_max_spawn);
									
										// verification si la position est libre
										$libre = verif_pos_libre($mysqli, $x, $y); 
										
										while ($libre == 1) {
											
											// position pas libre => on rechoisit de nouvelles coordonnées
											$x = pos_zone_rand_x($x_min_spawn, $x_max_spawn); 
											$y = pos_zone_rand_y($y_min_spawn, $y_max_spawn);
											$libre = verif_pos_libre($mysqli, $x, $y);
										}
									}					
									
									// insertion nouveau perso / Chef
									$lock = "LOCK TABLE perso WRITE";
									$mysqli->query($lock);
									
									if (trim($nom_bataillon) == "") {
										$nom_bataillon = $nom_perso." family";
									}
									
									$nom_bataillon = addslashes($nom_bataillon);
									
									$insert_sql = "	INSERT INTO perso (IDJoueur_perso, nom_perso, x_perso, y_perso, pvMax_perso, pv_perso, pm_perso, pmMax_perso, perception_perso, recup_perso, protec_perso, pa_perso, image_perso, dateCreation_perso, DLA_perso, or_perso, clan, message_perso, chef, bataillon) 
													VALUES ('$IDJoueur_perso','$nom_perso','$x','$y','$pvMax_chef','$pvMax_chef','$pmMax_chef','$pmMax_chef','$perc_chef','$recup_chef','$protec_chef','$pamax_chef','$image_chef',NOW(),FROM_UNIXTIME($dla), '20', $camp, '', 1, '$nom_bataillon')";
									
									if (!$mysqli->query($insert_sql)) {
										printf("Erreur : %s\n", $mysqli->error);
									}
									$id = $mysqli->insert_id;
									
									$unlock = "UNLOCK TABLES";
									$mysqli->query($unlock);
									
									if (is_dir($phpbb_root_path))
									{
										// Creation compte forum 
										$user_row = array(
											'id_perso'				=> $id,
											'username'				=> $nom_perso,
											'user_password'			=> phpbb_hash($old_mdp_joueur),
											'user_email'			=> $email_joueur,
											'group_id'				=> $group_id,
											'user_timezone'			=> 'Europe/Paris',
											'user_lang'				=> 'fr',
											'user_type'				=> USER_NORMAL,
											'user_actkey'			=> '',
											'user_ip'				=> realip(),
											'user_regdate'			=> time(),
											'user_inactive_reason'	=> 0,
											'user_inactive_time'	=> 0,
										);
										user_add($user_row);
									}
									
									if ($bat_spawn_dispo) {
										// On met le perso dans le batiment
										$sql = "INSERT INTO perso_in_batiment VALUES('$id','$id_bat')";
										$mysqli->query($sql);
									} else {
										// insertion du Chef sur la carte
										$sql = "UPDATE carte SET occupee_carte='1' , idPerso_carte='$id', image_carte='$image_chef' WHERE x_carte=$x AND y_carte=$y";
										$mysqli->query($sql);
									}
									
									// dossier courant
									$sql_i = "INSERT INTO perso_as_dossiers VALUES ('$id','1')";
									$mysqli->query($sql_i);
									
									// dossier archives
									$sql_i = "INSERT INTO perso_as_dossiers VALUES ('$id','2')";
									$mysqli->query($sql_i);
									
									// grade Chef = Caporal
									$sql_i = "INSERT INTO perso_as_grade VALUES ('$id','2')";
									$mysqli->query($sql_i);
									
									// Arme Cac : sabre
									$sql = "INSERT INTO perso_as_arme (id_perso, id_arme, est_portee) VALUES ('$id','1','1')";
									$mysqli->query($sql);
									
									// Arme distance : pistolet 
									$sql = "INSERT INTO perso_as_arme (id_perso, id_arme, est_portee) VALUES ('$id','4','1')";
									$mysqli->query($sql);
									
									// Insertion competence sieste
									$sql_c = "INSERT INTO perso_as_competence (id_perso, id_competence, nb_points) VALUES ('$id','4','1')";
									$mysqli->query($sql_c);
									
									if (!$bat_spawn_dispo) {
										
										// Positionnement grouillot
										$x_g = pos_zone_rand_x($x_min_spawn, $x_max_spawn); //placement du perso position x
										$y_g = pos_zone_rand_y($y_min_spawn, $y_max_spawn); //placement du perso position y
									
										// verification si la position est libre
										$libre = verif_pos_libre($mysqli, $x_g, $y_g); 
										
										while ($libre == 1) {
											
											// position pas libre => on rechoisit de nouvelles coordonnées
											$x_g = pos_zone_rand_x($x_min_spawn, $x_max_spawn); 
											$y_g = pos_zone_rand_y($y_min_spawn, $y_max_spawn);
											$libre = verif_pos_libre($x_g, $y_g);
										}
									} else {
										// Les coordonnées du grouillot sont les même que le chef
										// Coordonnées du batiment de spawn
										$x_g = $x;
										$y_g = $y;
									}
									
									// Insertion grouillot
									$lock = "LOCK TABLE perso WRITE";
									$mysqli->query($lock);
									
									$insert_sql = "INSERT INTO perso (IDJoueur_perso, nom_perso, type_perso, x_perso, y_perso, pvMax_perso, pv_perso, pm_perso, pmMax_perso, perception_perso, recup_perso, protec_perso, pa_perso, image_perso, dateCreation_perso, DLA_perso, clan, message_perso, bataillon) 
												VALUES ('$IDJoueur_perso','$nom_g','3','$x_g','$y_g','$pvMax_g','$pvMax_g','$pmMax_g','$pmMax_g','$perc_g','$recup_g','$protec_g','$pamax_g','$image_g',NOW(),FROM_UNIXTIME($dla), $camp, '', '$nom_bataillon')";

									if (!$mysqli->query($insert_sql)) {
										printf("Erreur : %s\n", $mysqli->error);
									}
									$id_g = $mysqli->insert_id;
									
									$unlock = "UNLOCK TABLES";
									$mysqli->query($unlock);
									
									if ($bat_spawn_dispo) {
										// On met le perso dans le batiment
										$sql = "INSERT INTO perso_in_batiment VALUES('$id_g','$id_bat')";
										$mysqli->query($sql);
									} else {
										// insertion du Grouillot sur la carte
										$sql = "UPDATE carte SET occupee_carte='1' , idPerso_carte='$id_g', image_carte='$image_g' WHERE x_carte=$x_g AND y_carte=$y_g";
										$mysqli->query($sql);
									}
									
									// dossier courant
									$sql_i = "INSERT INTO perso_as_dossiers VALUES ('$id_g','1')";
									$mysqli->query($sql_i);
									
									// dossier archives
									$sql_i = "INSERT INTO perso_as_dossiers VALUES ('$id_g','2')";
									$mysqli->query($sql_i);
									
									// grade Grouillot = 2nd classe
									$sql_i = "INSERT INTO perso_as_grade VALUES ('$id_g','1')";
									$mysqli->query($sql_i);
									
									// Arme Cac : baillonette
									$sql = "INSERT INTO perso_as_arme (id_perso, id_arme, est_portee) VALUES ('$id_g','6','1')";
									$mysqli->query($sql);
									
									// Arme distance : fusil 
									$sql = "INSERT INTO perso_as_arme (id_perso, id_arme, est_portee) VALUES ('$id_g','7','1')";
									$mysqli->query($sql);
									
									// Insertion competence construction barricades
									$sql_c = "INSERT INTO perso_as_competence (id_perso, id_competence, nb_points) VALUES ('$id_g','22','1')";
									$mysqli->query($sql_c);
									
									// Insertion competence marche forcée
									$sql_c = "INSERT INTO perso_as_competence (id_perso, id_competence, nb_points) VALUES ('$id_g','6','1')";
									$mysqli->query($sql_c);
									
									// Insertion competence sieste
									$sql_c = "INSERT INTO perso_as_competence (id_perso, id_competence, nb_points) VALUES ('$id_g','4','1')";
									$mysqli->query($sql_c);
							
									$_SESSION["ID_joueur"] = $IDJoueur_perso;
									
									$nom_img = "msg_".$ncamp.".jpg";
									
									// message de bienvenue personnalisé selon le camp
									switch($camp){
										case 1:
											$expediteur = "Abraham Lincoln";
											$nom_camp = "Au nom de l\'armée de l\'Union";
											break;
										case 2:
											$expediteur = "Jefferson Davis";
											$nom_camp = "Au nom de l\'armée des Etats confédérés";
											break;
										default:
											$expediteur = "admin";
											$nom_camp = "";
									}

									$objet = "Lettre prioritaire !";
									$message = "[center][b]bienvenue dans cette nouvelle version de NvS ".$nom_perso.".
									".$nom_camp.", je suis fier de t\'accueillir dans nos rangs. [/b][/center]";
									$message .= "[center][img]http://nord-vs-sud.com/images/".$nom_img."[/img][/center]";
									$message .= "
									
									Bon Jeu !
									
									L\'admin et l\'équipe d\'animation de Nord VS Sud";
									
									// création du message
									$lock = "LOCK TABLE message WRITE";
									$mysqli->query($lock);
									
									$sql = "INSERT INTO message (expediteur_message, date_message, contenu_message , objet_message ) VALUES ('" . $expediteur . "', NOW(), '" . $message . "', '" . $objet . "')";
									$mysqli->query($sql);
									$id_message = $mysqli->insert_id;
									
									$unlock = "UNLOCK TABLES";
									$mysqli->query($unlock);
									
									// assignation du message au perso
									$sql = "INSERT INTO message_perso VALUES ('$id_message', '$id', '1', '0', '1', '0')";
									$mysqli->query($sql);
									
									// Evenement spécial inscription
									$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) 
											VALUES ($id,'<font color=$couleur_clan_perso>$nom_perso</font>',' vient tout juste de sortir des jupons de sa maman pour venir grossir les rangs de l\'armée du $ncamp ',NULL,'','',NOW(),'0')";
									$mysqli->query($sql);
									
									// Evenement grouillot 
									$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) 
											VALUES ($id_g,'<font color=$couleur_clan_perso>$nom_g</font>',' a rejoint le bataillon $nom_bataillon',NULL,'','',NOW(),'0')";
									$mysqli->query($sql);
									
									header("location:index.php?creation=ok");
								}
								
							}
							else {
								echo "<center>Erreur: Camp invalide !</center><br /><br />";
							}
						}
					}
				}
			}
			else {
				echo "<center>Erreur: Veuillez choisir un camp !</center><br /><br />";
			}
		}
		else {
			echo "<center>Erreur: Veuillez valider les CGU et la charte !</center><br /><br />";
		}
	}
	$sql_nbb = "SELECT id_perso FROM perso WHERE clan='1' AND chef='1'";
	$res_nbb = $mysqli->query($sql_nbb);
	$nbb = $res_nbb->num_rows;
	
	$sql_nbr = "SELECT id_perso FROM perso WHERE clan='2' AND chef='1'";
	$res_nbr = $mysqli->query($sql_nbr);
	$nbr = $res_nbr->num_rows;
	
	require_once('mvc/view/register.php');
}
else {
	// logout
	$_SESSION = array(); // On écrase le tableau de session
	session_destroy(); // On détruit la session
	
	header("Location:index2.php");
}
?>
