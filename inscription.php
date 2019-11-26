<?php
session_start();
require_once("fonctions.php");

$mysqli = db_connexion();

include ('nb_online.php');

if(config_dispo_jeu($mysqli)){

	if(isSet ($_POST['creer'])){
		
		$nom_perso 		= $_POST['nom_perso'];
		$email_joueur 	= $_POST['email_joueur'];
		$nom_bataillon	= $_POST['nom_bataillon'];
		$mdp_joueur 	= $_POST['mdp_joueur'];
		$camp 			= $_POST['camp_perso'];
	
		if (!filtre($nom_perso,1,20)){
			echo "<center>Erreur: Le Pseudo est incorrect! Veuillez en choisir un autre (taille entre 1 et 20, par de quote, etc.) </center><br /><br />";
		}
		else {
			$sql = "SELECT nom_perso FROM perso WHERE nom_perso='".$nom_perso."'";
			$resultat_user = $mysqli->query($sql);
			$sql2 = "SELECT email_joueur FROM joueur WHERE email_joueur='".$email_joueur."'";
			$resultat_user2 = $mysqli->query($sql2);
				
			if( $resultat_user->num_rows != 0 ) {
				echo "<center>Erreur: Le pseudo est déjà choisi! Veuillez en choisir un autre</center><br /><br />";
			}
			elseif ($resultat_user2->num_rows != 0) {
				echo "<center>Erreur: Vous avez déjà creer un perso avec cet email, un seul perso par joueur</center><br /><br />";
			}
			elseif (!filtremail($email_joueur)) {
				echo "<center>Erreur: Email incorrect</center><br /><br />";
			}
			elseif ($mdp_joueur == "") {
				echo "<center>Erreur: Veuillez entrer un mot de passe</center><br /><br />";
			}
			else {
				if($_POST['creation']=="ok") {
					
					// sécurité camp
					if($camp == "1" || $camp == "2"){
						
						$mdp_joueur = md5($mdp_joueur);
						
						if($camp == 1){ // bleu
							$x_min_spawn = 0;
							$x_max_spawn = 50;
							$y_min_spawn = 0;
							$y_max_spawn = 50;
							$image_chef = "cavalerie_nord.gif";
							$image_g = "infanterie_nord.gif";
						}
						
						if($camp == 2){ // rouge
							$x_min_spawn = 150;
							$x_max_spawn = 200;
							$y_min_spawn = 110;
							$y_max_spawn = 160;
							$image_chef = "cavalerie_sud.gif";
							$image_g = "infanterie_sud.gif";
						}
						
						$x = pos_zone_rand_x($x_min_spawn, $x_max_spawn); //placement du perso position x
						$y = pos_zone_rand_y($y_min_spawn, $y_max_spawn); //placement du perso position y
					
						// verification si la position est libre
						$libre = verif_pos_libre($mysqli, $x, $y); 
						while ($libre == 1) {
							// position pas libre => on rechoisit de nouvelles coordonnées
							$x = pos_zone_rand_x($x_min_spawn, $x_max_spawn); 
							$y = pos_zone_rand_y($y_min_spawn, $y_max_spawn);
							$libre = verif_pos_libre($mysqli, $x, $y);
						}
					
						$date = time();
						$dla = $date + DUREE_TOUR; // calcul dla
				
						// Caracs Chef
						$pvMax_chef = 750;
						$pmMax_chef = 10;
						$pamax_chef = 10;
						$recup_chef = 40;
						$perc_chef = 5;
						$protec_chef = 20;
						
						// Carac grouillot
						$pvMax_g = 500;
						$pmMax_g = 5;
						$pamax_g = 10;
						$recup_g = 30;
						$perc_g = 4;
						$protec_g = 10;
						$nom_g = $nom_perso."_junior";
						
						// securité
						$sql = "select email_joueur from joueur,perso where nom_perso='$nom_perso'";
						$res = $mysqli->query($sql);
						$num = $res->num_rows;
						if($num != 0){
							echo "Evitez de bourriner sur l'image ^^ Votre perso est tout de même créé : <a href=\"index.php?creation=ok\">jouer</a>";
						}
						else {
									
							// insertion du nouveau joueur
							$insert_j = "INSERT INTO joueur (email_joueur, mdp_joueur) VALUES ('$email_joueur', '$mdp_joueur')";
							$result_j = $mysqli->query($insert_j);
							$IDJoueur_perso = $mysqli->insert_id;
							
							// insertion nouveau perso / Chef
							$insert_sql = "INSERT INTO perso (IDJoueur_perso, nom_perso, x_perso, y_perso, pvMax_perso, pv_perso, pm_perso, pmMax_perso, perception_perso, recup_perso, pa_perso, image_perso, dateCreation_perso, DLA_perso, clan, message_perso, chef, bataillon) VALUES ('$IDJoueur_perso','$nom_perso','$x','$y','$pvMax_chef','$pvMax_chef','$pmMax_chef','$pmMax_chef','$perc_chef','$recup_chef','$pamax_chef','$image_chef',NOW(),FROM_UNIXTIME($dla), $camp, '', 1, '$nom_bataillon')";

							if (!$mysqli->query($insert_sql)) {
								printf("Erreur : %s\n", $mysqli->error);
							}
							$id = $mysqli->insert_id;
							
							// dossier courant
							$sql_i = "INSERT INTO perso_as_dossiers VALUES ('$id','1')";
							$mysqli->query($sql_i);
							
							// dossier archives
							$sql_i = "INSERT INTO perso_as_dossiers VALUES ('$id','2')";
							$mysqli->query($sql_i);
							
							// grade Chef = Caporal
							$sql_i = "INSERT INTO perso_as_grade VALUES ('$id','2')";
							$mysqli->query($sql_i);
						
							// insertion du Chef sur la carte
							$sql = "UPDATE carte SET occupee_carte='1' , idPerso_carte='$id', image_carte='$image_chef' WHERE x_carte=$x AND y_carte=$y";
							$mysqli->query($sql);
							
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
							
							// Insertion grouillot
							$insert_sql = "INSERT INTO perso (IDJoueur_perso, nom_perso, x_perso, y_perso, pvMax_perso, pv_perso, pm_perso, pmMax_perso, perception_perso, recup_perso, pa_perso, image_perso, dateCreation_perso, DLA_perso, clan, message_perso, bataillon) VALUES ('$IDJoueur_perso','$nom_g','$x_g','$y_g','$pvMax_g','$pvMax_g','$pmMax_g','$pmMax_g','$perc_g','$recup_g','$pamax_g','$image_g',NOW(),FROM_UNIXTIME($dla), $camp, '', '$nom_bataillon')";

							if (!$mysqli->query($insert_sql)) {
								printf("Erreur : %s\n", $mysqli->error);
							}
							$id_g = $mysqli->insert_id;
							
							// grade Grouillot = 2nd classe
							$sql_i = "INSERT INTO perso_as_grade VALUES ('$id_g','1')";
							$mysqli->query($sql_i);
							
							// insertion du Grouillot sur la carte
							$sql = "UPDATE carte SET occupee_carte='1' , idPerso_carte='$id_g', image_carte='$image_g' WHERE x_carte=$x_g AND y_carte=$y_g";
							$mysqli->query($sql);
					
							$_SESSION["ID_joueur"] = $IDJoueur_perso;

							//insertion dans le forum
							if($camp == '1'){
								$group_id = '5';
								$nom_camp = 'Nordistes';
								$ncamp = "Nord";
								$couleur_clan_perso = "blue";
							}
							if($camp == '2'){
								$group_id = '6';
								$nom_camp = 'Sudistes';
								$ncamp = "Sud";
								$couleur_clan_perso = "red";
							}
							
							$now = time();
							$addr = get_remote_address();
							
							// FORUM - TODO
							//$sql = "INSERT INTO pun_users (username, group_id, password, email, email_setting, save_pass, timezone, language, style, registered, registration_ip, last_visit) VALUES('$nom_perso', '$group_id', '$mdp_joueur', '$email_joueur', '1', '1', '0', 'French', 'Chronicles', '$now', '$addr' , '$now')";
							//exec_requete($sql);
							
							// message de bienvenue
							$expediteur = "loka";
							$objet = "Bienvenue";
							$message = "[center][b]Bienvenue dans cette nouvelle version de NvS $nom_perso [/b][/center]";
							$message .= "
							Nous sommes fiers de t\'accueillir chez les $nom_camp.";
							$message .= "
							
							Bon Jeu !
							
							L\'admin de Nord VS Sud";
							
							// création du message
							$sql = "INSERT INTO message (expediteur_message, date_message, contenu_message , objet_message ) VALUES ('" . $expediteur . "', NOW(), '" . $message . "', '" . $objet . "')";
							$mysqli->query($sql);
							$id_message = $mysqli->insert_id;
							
							// assignation du message au perso
							$sql = "INSERT INTO message_perso VALUES ('$id_message', '$id', '1', '0', '0')";
							$mysqli->query($sql);
							
							// TODO - Insertion evenement suivant :
							// $nom_perso vient tout juste de sortir des jupons de sa maman pour venir grossir les rangs du $ncamp
							$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) 
									VALUES ($id,'<font color=$couleur_clan_perso>$nom_perso</font>',' vient tout juste de sortir des jupons de sa maman pour venir grossir les rangs de l\'armée du $ncamp ',NULL,'','',NOW(),'0')";
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
	?>
	<html>
	<head>
		<title>Nord VS Sud</title>
		
		<!-- Required meta tags -->
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		
		<!-- Bootstrap CSS -->
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
	</head>
	
	<body background="">
		<center>
		<font color="blue" size=5 face="Verdana, Arial, Helvetica, sans-serif"><b>INSCRIPTION</b></font><br/><br/>
		<form method="post" action="inscription.php">
			Entrez un nom pour votre personnage:<br/>
			<input type="text" name="nom_perso" value="" size="20" maxlength="30">
			<br/>
			Entrez un nom pour votre bataillon:<br/>
			<input type="text" name="nom_bataillon" value="" size="20" maxlength="100">
			<br/>
			Entrez votre email:<br/>
			<input type="text" name="email_joueur" value="" size="20" maxlength="60">
			<br/>
			Entrez votre mot de passe:<br/>
			<input type="password" name="mdp_joueur" value="" size="20" maxlength="20">
			<br/>
			Choisissez votre camp:<br/>
			<select name="camp_perso">
				<option value="1">Nord</option>
				<option value="2">Sud</option>
			</select>
			<br/><br/><input name="creation" type="hidden" value="ok">
			<input type="submit" name="creer" value="Cr&eacute;er">
			<br/><br/>
	<?php
	$sql_nbb = "SELECT id_perso FROM perso WHERE clan='1' AND chef='1'";
	$res_nbb = $mysqli->query($sql_nbb);
	$nbb = $res_nbb->num_rows;
	
	$sql_nbr = "SELECT id_perso FROM perso WHERE clan='2' AND chef='1'";
	$res_nbr = $mysqli->query($sql_nbr);
	$nbr = $res_nbr->num_rows;
	echo "<font color=blue>Nombre de persos au Nord : $nbb</font>&nbsp;&nbsp;&nbsp;&nbsp;<font color=red>Nombre de persos au Sud : $nbr</font>";
	
	if (isset ($_GET["voir"])) {
		echo "<br /><font color=\"#660000\"><b>Personnages(s) existant(s):</b><br>(choisir un nom diff&eacute;rent)<br><br>";
	
		$sql = "SELECT nom_perso FROM perso";
		$resultat = $mysqli->query($sql);
		$tab = $resultat->fetch_row();
		echo $tab[0];
		while ($tab = $resultat->fetch_row()) {
			echo " - ".$tab[0];
		}
		echo "</font><br><br>Masquer la liste :<br>";
		echo "<a href=\"inscription.php\"><img border=0 src=\"images/b_ok.gif\"></a>";
	}
	?>
			</font>
		
			<table border="0">
			  
			</table>
		</form>
		</center>
	
		<!-- Optional JavaScript -->
		<!-- jQuery first, then Popper.js, then Bootstrap JS -->
		<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
		<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
	
	</body>
</html>
<?php
}
else {
	// logout
	$_SESSION = array(); // On écrase le tableau de session
	session_destroy(); // On détruit la session
	
	header("Location: index2.php");
}
?>