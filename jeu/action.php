<?php
session_start();
require_once("../fonctions.php");
require_once("f_carte.php");
require_once("f_combat.php");
require_once("f_action.php");

$mysqli = db_connexion();

include ('../nb_online.php');

// recupération config jeu
$dispo = config_dispo_jeu($mysqli);
$admin = admin_perso($mysqli, $_SESSION["id_perso"]);

if($dispo == '1' || $admin) {

	$id_perso = $_SESSION["id_perso"];
	
	$verif_id_perso_session = preg_match("#^[0-9]*[0-9]$#i","$id_perso");
		
	if ($verif_id_perso_session) {
	
		$sql = "SELECT idJoueur_perso FROM perso WHERE id_perso='$id_perso'";
		$res = $mysqli->query($sql);
		$t = $res->fetch_assoc();
		
		$id_joueur = $t["idJoueur_perso"];
		
		$carte = "carte";
		
		$sql = "SELECT MAX(x_carte) as x_max, MAX(y_carte) as y_max FROM carte";
		$res = $mysqli->query($sql);
		$t = $res->fetch_assoc();
		
		$X_MAX = $t['x_max'];
		$Y_MAX  = $t['y_max'];
		
		// Traitement action construction batiment
		if(isset($_POST['image_bat'])){
			
			$nom_bat = '';//addslashes($_POST['hid_nom_bat']);
			
			$ok = construire_bat($mysqli, $_POST['image_bat'], $id_perso, $carte, $nom_bat);
			
			if($ok){
				// header (retour a la page de jeu)
				header("location:jouer.php");
			}
		}
		else {
			// traitement action construction batiment
			// passage par le champ cache pour IE
			if(isset($_POST['hid_image_bat'])){
				
				$nom_bat = '';//addslashes($_POST['hid_nom_bat']);
				
				$ok = construire_bat($mysqli, $_POST['hid_image_bat'], $id_perso, $carte, $nom_bat);
				
				if($ok){
					// header (retour a la page de jeu)
					header("location:jouer.php");
				}
			}
		}
		
		// Traitement action construction rails
		if(isset($_POST['pose_rail'])){
			
			$ok = construire_rail($mysqli, $_POST['pose_rail'], $id_perso, $carte);
			
			if($ok){
				// header (retour a la page de jeu)
				header("location:jouer.php");
			}
		}
		else {
			// traitement action construction batiment
			// passage par le champ cache pour IE
			if(isset($_POST['hid_pose_rail'])){
				
				$ok = construire_rail($mysqli, $_POST['hid_pose_rail'], $id_perso, $carte);
				
				if($ok){
					// header (retour a la page de jeu)
					header("location:jouer.php");
				}
			}
		}
		
		// Traitement action charge 
		if (isset($_POST['action_charge'])) {
			
			$direction_charge = $_POST['action_charge'];
			
			// Recup infos perso
			$sql = "SELECT x_perso, y_perso, nom_perso, pa_perso, pm_perso, pv_perso, xp_perso, type_perso, paMax_perso, bonusPM_perso, bonusPA_perso, image_perso, clan, perso_as_grade.id_grade FROM perso, perso_as_grade
					WHERE perso_as_grade.id_perso = perso.id_perso
					AND perso.id_perso='$id_perso'";
			$res = $mysqli->query($sql);
			$t_perso = $res->fetch_assoc();
			
			$nom_perso 		= $t_perso['nom_perso'];
			$x_perso 		= $t_perso['x_perso'];
			$y_perso 		= $t_perso['y_perso'];
			$pa_perso 		= $t_perso['pa_perso'];
			$pm_perso 		= $t_perso['pm_perso'];
			$pv_perso		= $t_perso['pv_perso'];
			$xp_perso		= $t_perso['xp_perso'];
			$type_perso		= $t_perso['type_perso'];
			$clan			= $t_perso['clan'];
			$image_perso	= $t_perso["image_perso"];
			$grade_perso	= $t_perso["id_grade"];
			$paMax_perso	= $t_perso['paMax_perso'];
			$bonusPM_perso	= $t_perso['bonusPM_perso'];
			$bonusPA_perso	= $t_perso['bonusPA_perso'];
			
			if ($pv_perso > 0) {
			
				// Pour pouvoir charger, il faut avoir tout ses PA et XXPM
				if ($pa_perso == $paMax_perso + $bonusPA_perso && verif_charge_pm($type_perso, $pm_perso)) {
				
					// Déplacement du perso dans l'axe choisi
					if ($direction_charge == 'haut') {
						charge_haut($mysqli, $id_perso, $nom_perso, $type_perso, $x_perso, $y_perso, $pa_perso, $pv_perso, $xp_perso, $image_perso, $clan, $grade_perso);
					}
					
					if ($direction_charge == 'haut_gauche') {
						charge_haut_gauche($mysqli, $id_perso, $nom_perso, $type_perso, $x_perso, $y_perso, $pa_perso, $pv_perso, $xp_perso, $image_perso, $clan, $grade_perso);
					}
					
					if ($direction_charge == 'gauche') {
						charge_gauche($mysqli, $id_perso, $nom_perso, $type_perso, $x_perso, $y_perso, $pa_perso, $pv_perso, $xp_perso, $image_perso, $clan, $grade_perso);
					}
					
					if ($direction_charge == 'bas_gauche') {
						charge_bas_gauche($mysqli, $id_perso, $nom_perso, $type_perso, $x_perso, $y_perso, $pa_perso, $pv_perso, $xp_perso, $image_perso, $clan, $grade_perso);
					}
					
					if ($direction_charge == 'bas') {
						charge_bas($mysqli, $id_perso, $nom_perso, $type_perso, $x_perso, $y_perso, $pa_perso, $pv_perso, $xp_perso, $image_perso, $clan, $grade_perso);
					}
					
					if ($direction_charge == 'bas_droite') {
						charge_bas_droite($mysqli, $id_perso, $nom_perso, $type_perso, $x_perso, $y_perso, $pa_perso, $pv_perso, $xp_perso, $image_perso, $clan, $grade_perso);
					}
					
					if ($direction_charge == 'droite') {
						charge_droite($mysqli, $id_perso, $nom_perso, $type_perso, $x_perso, $y_perso, $pa_perso, $pv_perso, $xp_perso, $image_perso, $clan, $grade_perso);
					}
					
					if ($direction_charge == 'haut_droite') {
						charge_haut_droite($mysqli, $id_perso, $nom_perso, $type_perso, $x_perso, $y_perso, $pa_perso, $pv_perso, $xp_perso, $image_perso, $clan, $grade_perso);
					}
					
					// retour a la page de jeu
					header("location:jouer.php");
				}
				else {
					// retour a la page de jeu
					header("location:jouer.php");
				}
			}
			else {
				// retour a la page de jeu
				header("location:jouer.php");
			}
		} else {
			
			if(isset($_POST['hid_action_charge'])){
				
				$direction_charge = $_POST['hid_action_charge'];
				
				// Recup infos perso
				$sql = "SELECT x_perso, y_perso, nom_perso, pa_perso, pm_perso, pv_perso, xp_perso, type_perso, paMax_perso, bonusPM_perso, bonusPA_perso, image_perso, clan, perso_as_grade.id_grade FROM perso, perso_as_grade
						WHERE perso_as_grade.id_perso = perso.id_perso
						AND perso.id_perso='$id_perso'";
				$res = $mysqli->query($sql);
				$t_perso = $res->fetch_assoc();
				
				$nom_perso 		= $t_perso['nom_perso'];
				$x_perso 		= $t_perso['x_perso'];
				$y_perso 		= $t_perso['y_perso'];
				$pa_perso 		= $t_perso['pa_perso'];
				$pm_perso 		= $t_perso['pm_perso'];
				$pv_perso		= $t_perso['pv_perso'];
				$xp_perso		= $t_perso['xp_perso'];
				$type_perso		= $t_perso['type_perso'];
				$clan			= $t_perso['clan'];
				$image_perso	= $t_perso["image_perso"];
				$grade_perso	= $t_perso["id_grade"];
				$paMax_perso	= $t_perso['paMax_perso'];
				$bonusPM_perso	= $t_perso['bonusPM_perso'];
				$bonusPA_perso	= $t_perso['bonusPA_perso'];
				
				if ($pv_perso > 0) {
				
					// Pour pouvoir charger, il faut avoir tout ses PA et XXPM
					if ($pa_perso == $paMax_perso + $bonusPA_perso && verif_charge_pm($type_perso, $pm_perso)) {
					
						// Déplacement du perso dans l'axe choisi
						if ($direction_charge == 'haut') {
							charge_haut($mysqli, $id_perso, $nom_perso, $type_perso, $x_perso, $y_perso, $pa_perso, $pv_perso, $xp_perso, $image_perso, $clan, $grade_perso);
						}
						
						if ($direction_charge == 'haut_gauche') {
							charge_haut_gauche($mysqli, $id_perso, $nom_perso, $type_perso, $x_perso, $y_perso, $pa_perso, $pv_perso, $xp_perso, $image_perso, $clan, $grade_perso);
						}
						
						if ($direction_charge == 'gauche') {
							charge_gauche($mysqli, $id_perso, $nom_perso, $type_perso, $x_perso, $y_perso, $pa_perso, $pv_perso, $xp_perso, $image_perso, $clan, $grade_perso);
						}
						
						if ($direction_charge == 'bas_gauche') {
							charge_bas_gauche($mysqli, $id_perso, $nom_perso, $type_perso, $x_perso, $y_perso, $pa_perso, $pv_perso, $xp_perso, $image_perso, $clan, $grade_perso);
						}
						
						if ($direction_charge == 'bas') {
							charge_bas($mysqli, $id_perso, $nom_perso, $type_perso, $x_perso, $y_perso, $pa_perso, $pv_perso, $xp_perso, $image_perso, $clan, $grade_perso);
						}
						
						if ($direction_charge == 'bas_droite') {
							charge_bas_droite($mysqli, $id_perso, $nom_perso, $type_perso, $x_perso, $y_perso, $pa_perso, $pv_perso, $xp_perso, $image_perso, $clan, $grade_perso);
						}
						
						if ($direction_charge == 'droite') {
							charge_droite($mysqli, $id_perso, $nom_perso, $type_perso, $x_perso, $y_perso, $pa_perso, $pv_perso, $xp_perso, $image_perso, $clan, $grade_perso);
						}
						
						if ($direction_charge == 'haut_droite') {
							charge_haut_droite($mysqli, $id_perso, $nom_perso, $type_perso, $x_perso, $y_perso, $pa_perso, $pv_perso, $xp_perso, $image_perso, $clan, $grade_perso);
						}
						
						// retour a la page de jeu
						header("location:jouer.php");
					}
					else {
						// retour a la page de jeu
						header("location:jouer.php");
					}
				}
				else {
					// retour a la page de jeu
					header("location:jouer.php");
				}
			}
		}
		
		// Reparer batiment
		if (isset($_GET['bat']) && $_GET['bat'] != '' && isset($_GET['reparer']) && $_GET['reparer'] == 'ok') {
			
			$id_bat = $_GET['bat'];
			
			// verification bat est un id correct
			$verif_idBat = preg_match("#^[0-9]*[0-9]$#i","$id_bat");
			
			if ($verif_idBat && isset($_SESSION["id_perso"])) {
				
				$id_perso = $_SESSION["id_perso"];

				// recup coordonnées perso
				$sql = "SELECT x_perso, y_perso FROM perso WHERE id_perso = '$id_perso'";
				$res = $mysqli->query($sql);
				$t_coord = $res->fetch_assoc();
				
				$x_perso = $t_coord["x_perso"];
				$y_perso = $t_coord["y_perso"];
				
				// verifier batiment est à côté du perso ou perso dans le batiment
				if (prox_instance_bat($mysqli, $x_perso, $y_perso, $id_bat) || in_bat($mysqli, $id_perso) == $id_bat) {
					
					// Lancement de la réparation
					action_reparer_bat($mysqli, $id_perso, $id_bat, 76);
				}
				else {
					header("Location:jouer.php?erreur=prox_bat");
				}
			}
		}
		
		// Saboter rail
		if (isset($_GET['saboter_rail']) && $_GET['saboter_rail'] == 'ok') {
			
			// Récupération infos perso 
			$sql = "SELECT x_perso, y_perso, nom_perso, pa_perso, clan FROM perso WHERE id_perso='$id_perso'";
			$res = $mysqli->query($sql);
			$t = $res->fetch_assoc();
			
			$x_perso 	= $t["x_perso"];
			$y_perso 	= $t["y_perso"];
			$pa_perso	= $t['pa_perso'];
			$nom_perso	= $t['nom_perso'];
			$camp_perso	= $t['clan'];
			
			if ($pa_perso >= 10) {
				
				// recuperation de la couleur du camp du perso
				$couleur_clan_perso = couleur_clan($camp_perso);
				
				// récupération du rail à détruire
				$sql = "SELECT fond_carte FROM carte WHERE x_carte='$x_perso' AND y_carte='$y_perso'";
				$res = $mysqli->query($sql);
				$t = $res->fetch_assoc();
				
				$fond_carte_rail = $t['fond_carte'];
				
				$t_rail = explode('.', $fond_carte_rail);
				$t_rail2 = explode('_', $t_rail[0]);

				if (isset($t_rail2[0]) && ($t_rail2[0] == 'rail' || $t_rail2[0] == 'railP')) {
				
					if (count($t_rail2) == 2 || (count($t_rail2) == 1 && $t_rail2[0] == 'rail')) {
						
						if (count($t_rail2) == 2) {
							$numero_fond = $t_rail2[1];
						}
						else {
							$numero_fond = '1';
						}
						
						$fond_carte = $numero_fond.".gif";
						
						// Mise à jour des PA du perso
						$sql = "UPDATE perso SET pa_perso = pa_perso - 10 WHERE id_perso='$id_perso'";
						$mysqli->query($sql);
						
						// MAJ carte destruction rail
						$sql = "UPDATE carte SET fond_carte='$fond_carte' WHERE x_carte='$x_perso' AND y_carte='$y_perso'";
						$mysqli->query($sql);
						
						// Insertion ligne evenement du perso
						$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) 
								VALUES ($id_perso,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>','a détruit <b>rail</b>',NULL,'',' en $x_perso / $y_perso',NOW(),'0')";
						$mysqli->query($sql);
						
						header("Location:jouer.php");
					}
					else if (count($t_rail2) == 1 && $t_rail2[0] == 'railP') {
						// Mise à jour des PA du perso
						$sql = "UPDATE perso SET pa_perso = pa_perso - 10 WHERE id_perso='$id_perso'";
						$mysqli->query($sql);

						// MAJ carte destruction rail
						$sql = "UPDATE carte SET fond_carte='8.gif' WHERE x_carte='$x_perso' AND y_carte='$y_perso'";
						$mysqli->query($sql);

						// Insertion ligne evenement du perso
						$sql = "INSERT INTO `evenement` (IDActeur_evenement, nomActeur_evenement, phrase_evenement, IDCible_evenement, nomCible_evenement, effet_evenement, date_evenement, special) 
								VALUES ($id_perso,'<font color=$couleur_clan_perso><b>$nom_perso</b></font>','a détruit <b>rail</b>',NULL,'',' en $x_perso / $y_perso',NOW(),'0')";
						$mysqli->query($sql);
						
						header("Location:jouer.php");
					}
					else {
						echo "<center><font color='red'>Case non reconnue comme un rail valide</font>";
						echo "<br /><a class='btn btn-primary' href='jouer.php'>retour</a></center>";
					}
				}
				else {
					echo "<center><font color='red'>Case non reconnue comme un rail</font>";
					echo "<br /><a class='btn btn-primary' href='jouer.php'>retour</a></center>";
				}
			}
			else {
				echo "<center><font color='red'>Pas assez de PA</font>";
				echo "<br /><a class='btn btn-primary' href='jouer.php'>retour</a></center>";
			}
		}
	
	?>
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
	<?php
	
		if (isset($_GET['bat']) && $_GET['bat'] != '' && isset($_GET['saboter']) && $_GET['saboter'] == 'ok') {
		
			$id_bat = $_GET['bat'];
			
			// verification bat est un id correct
			$verif_idBat = preg_match("#^[0-9]*[0-9]$#i","$id_bat");

			
			if ($verif_idBat && isset($_SESSION["id_perso"])) {
		
				$id_perso = $_SESSION["id_perso"];

				// Recup infos perso
				$sql = "SELECT type_perso FROM perso WHERE id_perso='$id_perso'";
				$res = $mysqli->query($sql);
				$t_perso = $res->fetch_assoc();
				$type_perso		= $t_perso['type_perso'];

				// les chiens ne peuvent pas saboter
				if ($type_perso != 6) {
					action_saboter($mysqli, $id_perso, $id_bat, 104);
				} else {
					echo "<center><font color='red'>les chiens ne peuvent pas saboter.</font></center>";
				}
			}
		}
		
		// Traitement action cible perso et soi-meme
		if(isset($_POST['action_cible_ref']) || isset($_POST['select_objet_soin'])){
			
			if(isset($_POST['select_objet_soin'])){
				$t_cib_ref = $_POST['select_objet_soin'];
				$t_cib_ref2 = explode(',',$t_cib_ref);
				$id_objet_s = $t_cib_ref2[0];
				$id_cible = $t_cib_ref2[1];
				$id_action = $t_cib_ref2[2];
			}
			else {
				$t_cib_ref = $_POST['action_cible_ref'];
				$t_cib_ref2 = explode(',',$t_cib_ref);
				$id_cible = $t_cib_ref2[0];
				$id_action = $t_cib_ref2[1];
			}
			
			// Soins pv
			if($id_action == '11'){
			   
			   // Recuperation des objets que possede le perso pouvant ameliorer les soins
				$sql_s = "SELECT id_objet FROM perso_as_objet WHERE id_objet IN
						(select id_objet FROM objet WHERE type_objet ='SSP') AND id_perso='$id_perso'";
				$res_s = $mysqli->query($sql_s);
				$num_s = $res_s->num_rows;
					
				if((isset($_POST['select_objet_soin']) && $_POST['select_objet_soin'] != "" ) || $num_s == 0 || isset($id_objet_s)){
					
					if($num_s == 0 || $id_objet_s == "NO")
						$id_objet_soin = 0;
					else
						$id_objet_soin = $id_objet_s;
						
					action_soin($mysqli, $id_perso, $id_cible, $id_action,$id_objet_soin);
					
				}		
				else {
					if($num_s >= 1){
						// Affichage liste des objets qu'on peut utiliser pour ameliorer les soins
						echo "<form method='post' action='action.php'>";
						echo "<td align='center'><select name=\"select_objet_soin\">";
						echo "<option value=\"NO,$t_cib_ref\">-- AUCUN --</option>";
						while($t_s = $res_s->fetch_assoc()){
							$id_objet_s = $t_s['id_objet'];
								
							$sql_ss = "SELECT nom_objet FROM objet WHERE id_objet=$id_objet_s";
							$res_ss = $mysqli->query($sql_ss);
							$t_ss = $res_ss->fetch_assoc();
							$nom_objet = $t_ss['nom_objet'];
								
							echo "<option value=\"$id_objet_s,$t_cib_ref\">$nom_objet</option>";
						}
						echo "</select>&nbsp;<input type='submit' name='valid_objet_soin' value='valider' /><input type='hidden' name='hid_valid_objet_soin' value='valider' /></td>";
						echo "</form>";
					}
					else {
						echo "<center><font color='red'>Problème dans les données, veuillez contacter un administrateur.</font></center>";
					}
				}
			}
			
			// Soins malus
			if($id_action == '140'){
				
				// Recuperation des objets que possede le perso pouvant ameliorer les soins
				$sql_s = "SELECT id_objet FROM perso_as_objet WHERE id_objet IN
						(select id_objet FROM objet WHERE type_objet ='SSP') AND id_perso='$id_perso'";
				$res_s = $mysqli->query($sql_s);
				$num_s = $res_s->num_rows;
					
				if((isset($_POST['select_objet_soin']) && $_POST['select_objet_soin'] != "" ) || $num_s == 0 || isset($id_objet_s)){
					
					if($num_s == 0 || $id_objet_s == "NO")
						$id_objet_soin = 0;
					else
						$id_objet_soin = $id_objet_s;
						
					action_soin_malus($mysqli, $id_perso, $id_cible, $id_action, $id_objet_soin);
				}		
				else {
					if($num_s >= 1){
						// Affichage liste des objets qu'on peut utiliser pour ameliorer les soins
						echo "<form method='post' action='action.php'>";
						echo "<td align='center'><select name=\"select_objet_soin\">";
						echo "<option value=\"NO,$t_cib_ref\">-- AUCUN --</option>";
						while($t_s = $res_s->fetch_assoc()){
							$id_objet_s = $t_s['id_objet'];
								
							$sql_ss = "SELECT nom_objet FROM objet WHERE id_objet=$id_objet_s";
							$res_ss = $mysqli->query($sql_ss);
							$t_ss = $res_ss->fetch_assoc();
							$nom_objet = $t_ss['nom_objet'];
								
							echo "<option value=\"$id_objet_s,$t_cib_ref\">$nom_objet</option>";
						}
						echo "</select>&nbsp;<input type='submit' name='valid_objet_soin' value='valider' /><input type='hidden' name='hid_valid_objet_soin' value='valider' /></td>";
						echo "</form>";
					}
					else {
						echo "<center><font color='red'>Problème dans les données, veuillez contacter un administrateur.</font></center>";
					}
				}
			}
		}
		else {
			// traitement action cible perso et soi-meme
			// passage par le champ cache pour IE
			if(isset($_POST['hid_action_cible_ref']) || isset($_POST['select_objet_soin'])){
				
				if(isset($_POST['select_objet_soin'])){
					$t_cib_ref = $_POST['select_objet_soin'];
					$t_cib_ref2 = explode(',',$t_cib_ref);
					$id_objet_s = $t_cib_ref2[0];
					$id_cible = $t_cib_ref2[1];
					$id_action = $t_cib_ref2[2];
				}
				else {
					$t_cib_ref = $_POST['hid_action_cible_ref'];
					$t_cib_ref2 = explode(',',$t_cib_ref);
					$id_cible = $t_cib_ref2[0];
					$id_action = $t_cib_ref2[1];
				}
				
				// Soins pv
				if($id_action == '11'){
					
					// Reparation des objets que possede le perso pouvant ameliorer les soins
					$sql_s = "SELECT id_objet FROM perso_as_objet WHERE id_objet IN
							(select id_objet FROM objet WHERE type_objet ='SSP') AND id_perso='$id_perso'";
					$res_s = $mysqli->query($sql_s);
					$num_s = $res_s->num_rows;
						
					if((isset($_POST['select_objet_soin']) && $_POST['select_objet_soin'] != "" ) || $num_s == 0 || isset($id_objet_s)){
						if($num_s == 0 || $id_objet_s == "NO")
							$id_objet_soin = 0;
						else
							$id_objet_soin = $id_objet_s;
							
						action_soin($mysqli, $id_perso, $id_cible, $id_action,$id_objet_soin);
					}		
					else {
						if($num_s >= 1){
							// Affichage liste des objets qu'on peut utiliser pour ameliorer les soins
							echo "<form method='post' action='action.php'>";
							echo "<td align='center'><select name=\"select_objet_soin\">";
							echo "<option value=\"NO,$t_cib_ref\">-- AUCUN --</option>";
							while($t_s = $res_s->fetch_assoc()){
								$id_objet_s = $t_s['id_objet'];
									
								$sql_ss = "SELECT nom_objet FROM objet WHERE id_objet=$id_objet_s";
								$res_ss = $mysqli->query($sql_ss);
								$t_ss = $res_ss->fetch_assoc();
								$nom_objet = $t_ss['nom_objet'];
									
								echo "<option value=\"$id_objet_s,$t_cib_ref\">$nom_objet</option>";
							}
							echo "</select>&nbsp;<input type='submit' name='valid_objet_soin' value='valider' /><input type='hidden' name='hid_valid_objet_soin' value='valider' /></td>";
							echo "</form>";
						}
						else {
							echo "<center><font color='red'>Problème dans les données, veuillez contacter un administrateur.</font></center>";
						}
					}
				}
				
				// Soins malus
				if($id_action == '140'){
					
					// Recuperation des objets que possede le perso pouvant ameliorer les soins
					$sql_s = "SELECT id_objet FROM perso_as_objet WHERE id_objet IN
							(select id_objet FROM objet WHERE type_objet ='SSP') AND id_perso='$id_perso'";
					$res_s = $mysqli->query($sql_s);
					$num_s = $res_s->num_rows;
					
					if((isset($_POST['select_objet_soin']) && $_POST['select_objet_soin'] != "" ) || $num_s == 0 || isset($id_objet_s)){
						if($num_s == 0 || $id_objet_s == "NO")
							$id_objet_soin = 0;
						else
							$id_objet_soin = $id_objet_s;
							
						action_soin_malus($mysqli, $id_perso, $id_cible, $id_action, $id_objet_soin);
					}		
					else {
						if($num_s >= 1){
							// Affichage liste des objets qu'on peut utiliser pour ameliorer les soins
							echo "<form method='post' action='action.php'>";
							echo "<td align='center'><select name=\"select_objet_soin\">";
							echo "<option value=\"NO,$t_cib_ref\">-- AUCUN --</option>";
							while($t_s = $res_s->fetch_assoc()){
								$id_objet_s = $t_s['id_objet'];
									
								$sql_ss = "SELECT nom_objet FROM objet WHERE id_objet=$id_objet_s";
								$res_ss = $mysqli->query($sql_ss);
								$t_ss = $res_ss->fetch_assoc();
								$nom_objet = $t_ss['nom_objet'];
									
								echo "<option value=\"$id_objet_s,$t_cib_ref\">$nom_objet</option>";
							}
							echo "</select>&nbsp;<input type='submit' name='valid_objet_soin' value='valider' /><input type='hidden' name='hid_valid_objet_soin' value='valider' /></td>";
							echo "</form>";
						}
						else {
							echo "<center><font color='red'>Problème dans les données, veuillez contacter un administrateur.</font></center>";
						}
					}
				}
			}
		}
		
		// Traitement action cible bat
		if(isset($_POST['action_cible_bat'])){
			
			$t_cib_bat = $_POST['action_cible_bat'];
			$t_cib_bat2 = explode(',',$t_cib_bat);
			$id_cible = $t_cib_bat2[0];
			$id_action = $t_cib_bat2[1];
			
			if($id_action == '76'){
				// Reparer bat
				action_reparer_bat($mysqli, $id_perso, $id_cible, $id_action);
			}
		}
		else {
			// traitement action cible bat
			// passage par le champ cache pour IE
			if(isset($_POST['hid_action_cible_bat'])){
				
				$t_cib_bat = $_POST['hid_action_cible_bat'];
				$t_cib_bat2 = explode(',',$t_cib_bat);
				$id_cible = $t_cib_bat2[0];
				$id_action = $t_cib_bat2[1];
				
				if($id_action == '76'){
					// Reparer bat
					action_reparer_bat($mysqli, $id_perso, $id_cible, $id_action);
				}
			}
		}
		
		// Deposer objet
		if(isset($_POST['valid_objet_depo']) && isset($_POST['id_objet_depo'])){
			$t_objet = $_POST['id_objet_depo'];
			$t2 = explode(',',$t_objet);
			
			$id_objet 	= $t2[0];
			$type_objet = $t2[1];
			
			$quantite = 1;
			if(isset($_POST['select_quantite_depot'])){
				$quantite = $_POST['select_quantite_depot'];
			}
			
			action_deposerObjet($mysqli, $id_perso, $type_objet, $id_objet, $quantite);
		}
		
		// Don objet apres choix perso
		if(isset($_POST['select_perso_don']) && isset($_POST['valid_perso_don'])){
			
			$id_cible = $_POST['select_perso_don'];
			
			// verif perso chiffre et perso existe
			$verif_idPerso = preg_match("#^[0-9]*[0-9]$#i","$id_cible");
			
			if($verif_idPerso && $id_cible != "" && $id_cible != null){
			
				echo "<br /><center><a class='btn btn-primary' href='jouer.php'><b>retour</b></a></center><br />";
			
				echo "<div class='table-responsive'>";
				echo "<table border='1' class='table table-bordered'>";
				echo "	<tr>";
				echo "		<th style='text-align:center' colspan='4'>Thunes / Objets / Armes à donner</th>";
				echo "	</tr>";
				echo "	<tr>";
				echo "		<th style='text-align:center'>image</th><th style='text-align:center'>poid unitaire</th><th style='text-align:center'>nombre possédé</th><th style='text-align:center'>donner ?</th>";
				echo "	</tr>";
								
				// Recuperation des objets / armes / armures que possede le perso
				// Or
				$compteur_or = 0;
				$sql_o0 = "SELECT or_perso FROM perso WHERE id_perso='$id_perso'";
				$res_o0 = $mysqli->query($sql_o0);
				$t_o0 = $res_o0->fetch_assoc();
				
				$or_perso = $t_o0['or_perso'];
				
				echo "<tr>";
				echo "<td><img src='../images/or.png' alt='thune' height='40' width='40' /><span><b>Thune</b></span></td>";
				echo "<td align='center'>0</td>";
						
				echo "<form method='post' action='action.php'>";		
				echo "<td align='center'>";
				echo "<select name=\"select_quantite\">";
				while ($compteur_or <= $or_perso){
					echo "<option value=\"$compteur_or\">$compteur_or</option>";
					$compteur_or++;
				}
				echo "</select>";
				echo "</td>";
					
				echo "<td align='center'><input type='submit' name='valid_objet_don' value='oui' class='btn btn-warning' /><input type='hidden' name='id_objet_don' value='-1,1,$id_cible' /></td>";
				echo "</form>";
				echo "</tr>";
					
					
				// Objets
				$sql_o = "SELECT DISTINCT id_objet FROM perso_as_objet WHERE id_perso='$id_perso' AND equip_objet = '0' ORDER BY id_objet";
				$res_o = $mysqli->query($sql_o);
				
				while($t_o = $res_o->fetch_assoc()){
					
					$id_objet = $t_o["id_objet"];
						
					// recuperation des carac de l'objet
					$sql1_o = "SELECT nom_objet, poids_objet, type_objet, image_objet, echangeable FROM objet WHERE id_objet='$id_objet'";
					$res1_o = $mysqli->query($sql1_o);
					$t1_o = $res1_o->fetch_assoc();
					
					$nom_o 		= $t1_o["nom_objet"];
					$poids_o 	= $t1_o["poids_objet"];
					$type_o		= $t1_o["type_objet"];
					$image_o	= $t1_o["image_objet"];
					$echangeable = $t1_o["echangeable"];
					
					if ($echangeable != 0) {
						
						$compteur = 1;
						
						// recuperation du nombre d'objet de ce type que possede le perso
						$sql2_o = "SELECT id_objet FROM perso_as_objet WHERE id_perso='$id_perso' AND id_objet='$id_objet'  AND equip_objet = '0'";
						$res2_o = $mysqli->query($sql2_o);
						$nb_o = $res2_o->num_rows;
												
						echo "<tr>";
						echo "	<td><img src='../public/img/items/".$image_o."' alt='$nom_o' height='40' width='40'/><span><b>".stripslashes($nom_o)."</b></span></td>";
						echo "	<td align='center'>$poids_o</td>";
						echo "	<form method='post' action='action.php'>";
						echo "	<td align='center'>";
						if ($nb_o > 1) {
							echo "<select name=\"select_quantite\">";
							while ($compteur <= $nb_o){
								echo "<option value=\"$compteur\">$compteur</option>";
								$compteur++;
							}
							echo "</select>";
						}
						else {
							echo "1";
						}
						echo "</td>";
						echo "	<td align='center'><input type='submit' name='valid_objet_don' value='oui' class='btn btn-warning' /><input type='hidden' name='id_objet_don' value='$id_objet,2,$id_cible' /></td>";
						echo "	</form>";
						echo "</tr>";
					}
				}
				
				// Armes non portes
				$sql_a1 = "SELECT DISTINCT id_arme FROM perso_as_arme WHERE id_perso='$id_perso' AND est_portee='0' ORDER BY id_arme";
				$res_a1 = $mysqli->query($sql_a1);
				
				while($t_a1 = $res_a1->fetch_assoc()){
					
					$compteur = 1;
					
					$id_arme = $t_a1["id_arme"];
										
					// recuperation des carac de l'arme
					$sql1_a1 = "SELECT nom_arme, poids_arme, image_arme FROM arme WHERE id_arme='$id_arme'";
					$res1_a1 = $mysqli->query($sql1_a1);
					$t1_a1 = $res1_a1->fetch_assoc();
					
					$nom_a1 	= $t1_a1["nom_arme"];
					$poids_a1 	= $t1_a1["poids_arme"];
					$image_arme = $t1_a1["image_arme"];
										
					// recuperation du nombre d'armes non equipes de ce type que possede le perso 
					$sql2_a1 = "SELECT id_arme FROM perso_as_arme WHERE id_perso='$id_perso' AND id_arme='$id_arme' AND est_portee='0'";
					$res2_a1 = $mysqli->query($sql2_a1);
					$nb_a1 = $res2_a1->num_rows;
										
					echo "<tr>";
					echo "	<td><img src='../images/armes/$image_arme' alt='$nom_a1' height='40' width='40'/><span><b>".stripslashes($nom_a1)."</b></span></td>";
					echo "	<td align='center'>$poids_a1</td>";
					echo "	<form method='post' action='action.php'>";
					echo "	<td align='center'>";
						if ($nb_a1 > 1) {
							echo "<select name=\"select_quantite\">";
							while ($compteur <= $nb_a1){
								echo "<option value=\"$compteur\">$compteur</option>";
								$compteur++;
							}
							echo "</select>";
						}
						else {
							echo "1";
						}
					echo "	<td align='center'><input type='submit' name='valid_objet_don' value='oui' class='btn btn-warning' /><input type='hidden' name='id_objet_don' value='$id_arme,3,$id_cible' /></td>";
					echo "	</form>";
					echo "</tr>";
				}
									
				echo "</table>";
				echo "</div>";
				echo "<br /><br />";
			}
			else {
				echo "<font color='red'>La cible n'est correcte.</font><br/>";
				echo "<center><a class='btn btn-primary' href='jouer.php'>retour</a></center>";
			}
		}
		
		// Don objet apres choix objet
		if(isset($_POST['valid_objet_don']) && isset($_POST['id_objet_don']) ){
			
			$quantite = 1;
			if(isset($_POST['select_quantite'])){
				$quantite = $_POST['select_quantite'];
			}
			$t_objet = $_POST['id_objet_don'];
			$t2 = explode(',',$t_objet);
			
			$id_objet 	= $t2[0];
			$type_objet = $t2[1];
			$id_cible 	= $t2[2];

			$sql_cible = "SELECT type_perso FROM perso WHERE id_perso='$id_cible'";
			$res_cible = $mysqli->query($sql_cible);
			$t_cible = $res_cible->fetch_assoc();
			
			$type_cible = $t_cible['type_perso'];
			
			action_don_objet($mysqli, $id_perso, $id_cible, $type_cible,  $type_objet, $id_objet, $quantite);
		}
		
		/////////////////////////
		// Traitement des actions
		if(isset($_POST['action'])){
			
			if(isset($_POST['liste_action']) && $_POST['liste_action'] != 'invalide' && $_POST['liste_action'] != 'PA'){
				
				// recuperation de l'id de l'action
				$id_action = $_POST['liste_action'];
				
				// verification que le perso possede bien l'action
				$sql_v = "SELECT action.id_action
						FROM perso_as_competence, competence_as_action, action 
						WHERE id_perso='$id_perso' 
						AND perso_as_competence.id_competence=competence_as_action.id_competence 
						AND competence_as_action.id_action=action.id_action
						AND perso_as_competence.nb_Points=action.nb_points
						AND action.id_action='$id_action'";
				$res_v = $mysqli->query($sql_v);
				$verif = $res_v->num_rows;
				
				if($verif || $id_action=='65' || $id_action=='110' || $id_action=='111' || $id_action=='139' || $id_action=='999'){
				
					//-----------------
					// Action Charger
					//-----------------
					if ($id_action=='999') {
						
						// On verifie que le perso soit bien un cavalier
						$sql ="SELECT type_perso FROM perso WHERE id_perso='$id_perso' AND (type_perso='1' OR type_perso='2' OR type_perso='7' OR type_perso='3')";
						$res = $mysqli->query($sql);
						$verif_charge = $res->num_rows;
						$t_perso1 = $res->fetch_assoc();
						$type_perso = $t_perso1["type_perso"];
						
						if ($verif_charge) {
						
							echo "<center><h1>Charger !</h1></center>";
							
							// -------------
							// - ANTI ZERK -
							// -------------
							$verif_anti_zerk = gestion_anti_zerk($mysqli, $id_perso);
							
							if ($verif_anti_zerk) {
							
								//recuperation des coordonnees du perso
								$sql = "SELECT x_perso, y_perso, perception_perso, pa_perso, paMax_perso, pm_perso, bonusPM_perso, bonusPA_perso, clan FROM perso WHERE id_perso='$id_perso'";
								$res = $mysqli->query($sql);
								$t_coord = $res->fetch_assoc();
									
								$x_perso 			= $t_coord['x_perso'];
								$y_perso 			= $t_coord['y_perso'];
								$perception_perso 	= $t_coord['perception_perso'];
								$clan_perso 		= $t_coord['clan'];
								$pa_perso 			= $t_coord['pa_perso'];
								$paMax_perso		= $t_coord['paMax_perso'];
								$pm_perso			= $t_coord['pm_perso'];
								$bonusPM_perso		= $t_coord['bonusPM_perso'];
								$bonusPA_perso		= $t_coord['bonusPA_perso'];
								
								// Récupération du type de terrain sur lequel se trouve le perso
								$sql = "SELECT fond_carte FROM $carte WHERE x_carte = $x_perso AND y_carte = $y_perso";
								$res = $mysqli->query($sql);
								$tab = $res->fetch_assoc();
								
								$fond_carte_perso = $tab['fond_carte'];
								
								$bonus_visu = get_malus_visu($fond_carte_perso) + getBonusObjet($mysqli, $id_perso);
															
								if(bourre($mysqli, $id_perso)){
									if(!endurance_alcool($mysqli, $id_perso)) {
										$malus_bourre = bourre($mysqli, $id_perso) * 3;
										$bonus_visu -= $malus_bourre;
									}
								}
								
								$perception_final = $perception_perso + $bonus_visu;
								if ($perception_final <= 0) {
									$perception_final = 1;
								}

								// Pour pouvoir charger, il faut avoir tout ses PA, XXPM et être sur de la plaine
								if ($pa_perso == $paMax_perso + $bonusPA_perso && verif_charge_pm($type_perso, $pm_perso) && ($fond_carte_perso == '1.gif' || 0 === strpos($fond_carte_perso, 'rail'))) {
									
									// recuperation des donnees de la carte
									$sql = "SELECT x_carte, y_carte, fond_carte, occupee_carte, image_carte, idPerso_carte 
											FROM $carte WHERE x_carte >= $x_perso - $perception_final 
											AND x_carte <= $x_perso + $perception_final 
											AND y_carte <= $y_perso + $perception_final 
											AND y_carte >= $y_perso - $perception_final 
											ORDER BY y_carte DESC, x_carte";
									$res = $mysqli->query($sql);
									$tab = $res->fetch_assoc(); 
										
									//<!--Generation de la carte-->
									echo '<table border=0 align="center" cellspacing="0" cellpadding="0" style:no-padding>';
										
									echo "<tr><td>y \ x</td>";  //affichage des abscisses
									for ($i = $x_perso - $perception_final; $i <= $x_perso + $perception_final; $i++) {
										echo "<th width=40 height=40>$i</th>";
									}
									echo "</tr>";
										
									for ($y = $y_perso + $perception_final; $y >= $y_perso - $perception_final; $y--) {
										
										echo "<th>$y</th>";
										
										for ($x = $x_perso - $perception_final; $x <= $x_perso + $perception_final; $x++) {
											
											//les coordonnees sont dans les limites
											if ($x >= X_MIN && $y >= Y_MIN && $x <= $X_MAX && $y <= $Y_MAX) {
												
												if ($tab["occupee_carte"]){
													
													$image_carte 	= $tab["image_carte"];
													$id_perso_carte = $id_cible = $tab["idPerso_carte"];
													$fond_im		= $tab["fond_carte"];
													
													$nom_terrain = get_nom_terrain($fond_im);
													
													if ($id_perso_carte >= 200000) {
														// PNJ
														$dossier_image = "images/pnj";
														
														$sql_p = "SELECT nom_pnj FROM pnj, instance_pnj WHERE pnj.id_pnj = instance_pnj.id_pnj AND idInstance_pnj='$id_cible'";
														$res_p = $mysqli->query($sql_p);
														$tab_p = $res_p->fetch_assoc(); 
														
														$nom_cible 	= $tab_p["nom_pnj"];
													}
													else if ($id_perso_carte < 50000) {
														// PERSO
														$dossier_config = get_dossier_image_joueur($mysqli, $id_joueur);
														$dossier_image 	= "images_perso/".$dossier_config;
														
														$sql_p = "SELECT nom_perso FROM perso WHERE id_perso='$id_cible'";
														$res_p = $mysqli->query($sql_p);
														$tab_p = $res_p->fetch_assoc(); 
														
														$nom_cible 	= $tab_p["nom_perso"];
														
													}
													else {
														// BATIMENT
														$dossier_image = "images_perso";
														
														$sql_p = "SELECT nom_batiment, nom_instance FROM batiment, instance_batiment 
																	WHERE batiment.id_batiment = instance_batiment.id_batiment 
																	AND id_instanceBat='$id_cible'";
														$res_p = $mysqli->query($sql_p);
														$tab_p = $res_p->fetch_assoc(); 
														
														$nom_cible 	= $tab_p["nom_batiment"]." ".$tab_p["nom_instance"];
													}
														
													if(isset($id_perso_carte) && $id_perso_carte < 50000){
														echo "<td width=40 height=40 background=\"../fond_carte/".$fond_im."\">";
														echo "	<div width=40 height=40 style=\"position: relative;\">";
														echo "		<div data-toggle='tooltip' data-html='true' data-placement='bottom' title=\"<div>".$nom_cible." [".$id_cible."]</div><div>".$nom_terrain."</div>\" style=\"position: absolute;bottom: -2px;text-align: center; width: 100%;font-weight: bold;\">" . $id_cible . "</div>";
														echo "		<img border=0 src=\"../".$dossier_image."/".$image_carte."\" width=40 height=40 data-toggle='tooltip' data-html='true' data-placement='bottom' title=\"<div>".$nom_cible." [".$id_cible."]</div><div>".$nom_terrain."</div>\" />";
														echo "	</div>";
														echo "</td>";
													}
													else if (isset($id_perso_carte)) {
														echo "<td width=40 height=40 background=\"../fond_carte/".$fond_im."\">";
														echo "	<img border=0 src=\"../".$dossier_image."/".$image_carte."\" width=40 height=40 data-toggle='tooltip' data-html='true' data-placement='bottom' title=\"<div>".$nom_cible." [".$id_cible."]</div><div>".$nom_terrain."</div>\" />";
														echo "</td>";
													}
													else {
														echo "<td width=40 height=40 background=\"../fond_carte/".$fond_im."\">";
														echo "<img border=0 src=\"../images_perso/".$image_carte."\" width=40 height=40 data-toggle='tooltip' data-html='true' data-placement='bottom' title=\"<div>".$nom_cible." [".$id_cible."]</div><div>".$nom_terrain."</div>\" \>";
														echo "</td>";
													}
												}
												else{
													
													// autour du perso
													if ($x >= $x_perso - 1 && $x <= $x_perso + 1 && $y >= $y_perso - 1 && $y <= $y_perso + 1) {
														
														echo "<form method='POST' action='action.php'>";
														
														if ($x == $x_perso - 1 && $y == $y_perso - 1) {
														
															// fleche bas gauche
															echo "<td width=40 height=40> <input type=\"image\" name=\"action_charge\" value=\"bas_gauche\" border=0 src=\"../fond_carte/fleche6.png\" width=40 height=40><input type=\"hidden\" name=\"hid_action_charge\" value=\"bas_gauche\" ></td>";
														
														} else if ($x == $x_perso - 1 && $y == $y_perso) {
															
															// fleche gauche
															echo "<td width=40 height=40> <input type=\"image\" name=\"action_charge\" value=\"gauche\" border=0 src=\"../fond_carte/fleche4.png\" width=40 height=40><input type=\"hidden\" name=\"hid_action_charge\" value=\"gauche\" ></td>";
															
														} else if ($x == $x_perso - 1 && $y == $y_perso + 1) {
															
															// fleche haut gauche
															echo "<td width=40 height=40> <input type=\"image\" name=\"action_charge\" value=\"haut_gauche\" border=0 src=\"../fond_carte/fleche1.png\" width=40 height=40><input type=\"hidden\" name=\"hid_action_charge\" value=\"haut_gauche\" ></td>";
															
														} else if ($x == $x_perso && $y == $y_perso - 1) {
															
															// fleche bas
															echo "<td width=40 height=40> <input type=\"image\" name=\"action_charge\" value=\"bas\" border=0 src=\"../fond_carte/fleche7.png\" width=40 height=40><input type=\"hidden\" name=\"hid_action_charge\" value=\"bas\" ></td>";
															
														} else if ($x == $x_perso && $y == $y_perso + 1) {
															
															// fleche haut
															echo "<td width=40 height=40> <input type=\"image\" name=\"action_charge\" value=\"haut\" border=0 src=\"../fond_carte/fleche2.png\" width=40 height=40><input type=\"hidden\" name=\"hid_action_charge\" value=\"haut\" ></td>";
															
														} else if ($x == $x_perso + 1 && $y == $y_perso + 1) {
															
															// fleche haut droite
															echo "<td width=40 height=40> <input type=\"image\" name=\"action_charge\" value=\"haut_droite\" border=0 src=\"../fond_carte/fleche3.png\" width=40 height=40><input type=\"hidden\" name=\"hid_action_charge\" value=\"haut_droite\" ></td>";
															
														} else if ($x == $x_perso + 1 && $y == $y_perso) {
															
															// fleche droite
															echo "<td width=40 height=40> <input type=\"image\" name=\"action_charge\" value=\"droite\" border=0 src=\"../fond_carte/fleche5.png\" width=40 height=40><input type=\"hidden\" name=\"hid_action_charge\" value=\"droite\" ></td>";
															
														} else if ($x == $x_perso + 1 && $y == $y_perso - 1) {
															
															// fleche bas droite
															echo "<td width=40 height=40> <input type=\"image\" name=\"action_charge\" value=\"bas_droite\" border=0 src=\"../fond_carte/fleche8.png\" width=40 height=40><input type=\"hidden\" name=\"hid_action_charge\" value=\"bas_droite\" ></td>";
															
														}
														
														echo "</form>";
														
													} else {
													
														//positionnement du fond
														$fond_carte = $tab["fond_carte"];
															
														echo "<td width=40 height=40> <img border=0 src=\"../fond_carte/$fond_carte\" width=40 height=40 ></td>";
													}
												}
												$tab = $res->fetch_assoc();
											}
											else {
												//les coordonnees sont hors limites
												echo "<td width=40 height=40><img border=0 width=40 height=40 src=\"../fond_carte/decorO.jpg\"></td>";
											}
										}
										echo "</tr>";
									}
									echo "</table>";
									// fin de la generation de la carte
										
									// lien annuler
									echo "<br /><br /><center><a class='btn btn-primary' href='jouer.php'><b>annuler</b></a></center>";
								} else {
									
									// Besoin de XXPM, de tout ses PA et être sur de la plaine pour pouvoir charger
									echo "<br /><center>Vous avez besoin de tous vos PA, de 4PM pour un cavalier ou 2PM pour infanterie et d'être sur de la plaine afin de pouvoir charger !</center>";
									echo "<br /><br /><center><a class='btn btn-primary' href='jouer.php'><b>retour</b></a></center>";
								}
							}
							else {
								echo "<br /><center>Loi anti-zerk non respectée !</center>";
								echo "<br><center><a class='btn btn-primary' href=\"jouer.php\">retour</a></center>";
							}
						}
						else {
							// Tentative de triche
							echo "<br /><center>Pas bien d'essayer de tricher !";
							echo "<br /><a class='btn btn-primary' href=\"jouer.php\">retour</a></center>";
							
							$text_triche = "Tentative charge sans être cavalier ni infanterie";
							
							$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id_perso', '$text_triche')";
							$mysqli->query($sql);
						}
					} else {
				
						// recuperation des effet et du type d'action
						$sql = "SELECT * FROM action WHERE id_action='$id_action'";
						$res = $mysqli->query($sql);
						$t_ac = $res->fetch_assoc();
				
						$nom_action 			= $t_ac['nom_action'];
						$nb_points_action 		= $t_ac['nb_points'];
						$portee_action 			= $t_ac['portee_action'];
						$perceptionMin_action 	= $t_ac['perceptionMin_action'];
						$perceptionMax_action 	= $t_ac['perceptionMax_action'];
						$pvMin_action 			= $t_ac['pvMin_action'];
						$pvMax_action 			= $t_ac['pvMax_action'];
						$recupMin_action 		= $t_ac['recupMin_action'];
						$recupMax_action 		= $t_ac['recupMax_action'];
						$pmMin_action 			= $t_ac['pmMin_action'];
						$pmMax_action 			= $t_ac['pmMax_action'];
						$DefMin_action 			= $t_ac['DefMin_action'];
						$DefMax_action 			= $t_ac['DefMax_action'];
						$coutPa_action 			= $t_ac['coutPa_action'];
						$nbreTourMin 			= $t_ac['nbreTourMin'];
						$nbreTourMax 			= $t_ac['nbreTourMax'];
						$coutOr_action 			= $t_ac['coutOr_action'];
						$coutBois_action 		= $t_ac['coutBois_action'];
						$coutFer_action 		= $t_ac['coutFer_action'];
						$reflexive_action 		= $t_ac['reflexive_action'];
						$cible_action 			= $t_ac['cible_action'];
						$case_action 			= $t_ac['case_action'];
						$pnj_action 			= $t_ac['pnj_action'];
						
						$image_action = image_action($id_action);
						
						// action ayant pour cible juste son propre perso
						if($reflexive_action && !$cible_action){
							
							if($nom_action == 'Sieste'){
								action_dormir($mysqli, $id_perso);
							}
							
							// traitement de l'action entrainement
							if($nom_action == 'Entrainement'){
								action_entrainement($mysqli, $id_perso);
							}
							
							// traitement de l'action marche forcee
							if($id_action == 4){
								action_marcheForcee($mysqli, $id_perso, $nb_points_action,$coutPa_action);
							}
						}
						
						if($pnj_action){
							// header (retour a la page de jeu)
							header("location:jouer.php?erreur=competence");
						}
									
			
						// action ayant pour cible un perso
						if($cible_action){
							
							// action pouvant cibler son propre perso
							if($reflexive_action){
								
								//recuperation des coordonnees du perso
								$sql = "SELECT x_perso, y_perso, clan FROM perso WHERE id_perso='$id_perso'";
								$res = $mysqli->query($sql);
								$t_coord = $res->fetch_assoc();
								
								$x_perso = $t_coord['x_perso'];
								$y_perso = $t_coord['y_perso'];
								$clan_perso = $t_coord['clan'];
								
								// recuperation des donnees de la carte
								$sql = "SELECT x_carte, y_carte, fond_carte, occupee_carte, image_carte, idPerso_carte FROM $carte WHERE x_carte >= $x_perso - 1 AND x_carte <= $x_perso + 1 AND y_carte <= $y_perso + 1 AND y_carte >= $y_perso - 1 ORDER BY y_carte DESC, x_carte";
								$res = $mysqli->query($sql);
								$tab = $res->fetch_assoc(); 
								
								//<!--Generation de la carte-->
								echo '<table border=0 align="center" cellspacing="0" cellpadding="0" style:no-padding>';
								
								echo "<tr><td>y \ x</td>";  //affichage des abscisses
								for ($i = $x_perso - 1; $i <= $x_perso + 1; $i++) {
									echo "<th width=40 height=40>$i</th>";
								}
								echo "</tr>";
								
								for ($y = $y_perso + 1; $y >= $y_perso - 1; $y--) {
									echo "<th>$y</th>";
									for ($x = $x_perso - 1; $x <= $x_perso + 1; $x++) {
										
										//les coordonnees sont dans les limites
										if ($x >= X_MIN && $y >= Y_MIN && $x <= $X_MAX && $y <= $Y_MAX) {
											
											if ($tab["occupee_carte"]){
												
												$image_perso = $tab["image_carte"];
												$id_perso_carte = $tab["idPerso_carte"];
												
												if($id_perso_carte < 10000 && isset($id_perso_carte)){
													
													// recuperation des infos du perso
													$sql_perso_carte = "SELECT nom_perso, clan FROM perso WHERE id_perso=$id_perso_carte";
													$res_perso_carte = $mysqli->query($sql_perso_carte);
													$t_perso_carte = $res_perso_carte->fetch_assoc();
													
													$nom_perso_carte = $t_perso_carte["nom_perso"];
													$clan_perso_carte = $t_perso_carte["clan"];
													if($clan_perso_carte == $clan_perso){
														$clan_pc = 'Nord';
													}
													else {
														$clan_pc = 'Sud';
													}
												
													if($nom_action == "Apaiser"){
														$action = "+ Apaiser +";
													}
													else {
														$action = "+ Soigner +";
													}
												
													echo "<form method=\"post\" action=\"action.php\" >";
													echo "<td width=40 height=40 background=\"../fond_carte/".$tab["fond_carte"]."\"> <input type=\"image\" name=\"action_cible_ref\" value=\"$id_perso_carte,$id_action\" border=0 src=\"../images_perso/".$image_perso."\" width=40 height=40 onMouseOver=\"this.src='../images/$image_action';AffBulle('<tr><td>$action</td></tr><tr><td><font color=$clan_pc>$nom_perso_carte</font> [$id_perso_carte]</td></tr>');\" onMouseOut=\"this.src='../images_perso/$image_perso';HideBulle();\" ><input type=\"hidden\" name=\"hid_action_cible_ref\" value=\"$id_perso_carte,$id_action\" ></td>";
													echo "</form>";
												}
												else {
													echo "<td width=40 height=40 background=\"../fond_carte/".$tab["fond_carte"]."\"><img border=0 src=\"../images_perso/".$tab["image_carte"]."\" width=40 height=40 \></td>";
												}
											}
											else{
												//positionnement du fond
												$fond_carte = $tab["fond_carte"];
												
												echo "<td width=40 height=40> <img border=0 src=\"../fond_carte/$fond_carte\" width=40 height=40 ></td>";
											}
											$tab = $res->fetch_assoc();
										}
										else //les coordonnees sont hors limites
											echo "<td width=40 height=40><img border=0 width=40 height=40 src=\"../fond_carte/decorO.jpg\"></td>";
									}
									echo "</tr>";
								}
								echo "</table>";
								// fin de la generation de la carte
								
								// lien annuler
								echo "<br /><br /><center><a class='btn btn-primary' href='jouer.php'><b>annuler</b></a></center>";
							}
							
							// action ne pouvant pas cibler son propre perso
							else {
								//recuperation des coordonnees du perso
								$sql = "SELECT x_perso, y_perso, pa_perso, clan FROM perso WHERE id_perso='$id_perso'";
								$res = $mysqli->query($sql);
								$t_coord = $res->fetch_assoc();
								
								$x_perso 	= $t_coord['x_perso'];
								$y_perso 	= $t_coord['y_perso'];
								$clan_perso = $t_coord['clan'];
								$pa_perso	= $t_coord['pa_perso'];
								
								// Donner objet
								if($nom_action == 'Donner objet'){
									
									if ($pa_perso >= 1) {
										
										if (!in_bat($mysqli, $id_perso)) {
										
											// Recuperation des persos au CaC
											$sql_c = "SELECT idPerso_carte as id_cible
														FROM $carte WHERE x_carte<=$x_perso+1 AND x_carte>=$x_perso-1 AND y_carte>=$y_perso-1 AND y_carte<=$y_perso+1 
														AND occupee_carte='1' 
														AND idPerso_carte!='$id_perso' 
														AND idPerso_carte < 50000";
											$res_c = $mysqli->query($sql_c);
											
										}
										else {
											
											// Perso dans un batiment
											$sql = "SELECT id_instanceBat FROM perso_in_batiment WHERE id_perso='$id_perso'";
											$res = $mysqli->query($sql);
											$t = $res->fetch_assoc();
											
											$id_instance_bat = $t['id_instanceBat'];
											
											// On récupère la liste des persos dans le même batiment
											$sql_c = "SELECT id_perso as id_cible FROM perso_in_batiment WHERE id_instanceBat='$id_instance_bat' AND id_perso != '$id_perso'";
											$res_c = $mysqli->query($sql_c);
											
										}
										
										echo "<div class='row'>";
										echo "	<div class='col-12'>";
										echo "		<div align='center'>";
										
										echo "			<h1>Action de don à un perso</h1>";
										
										echo "			<form method='post' action='action.php'>";
										echo "				<select name=\"select_perso_don\" style=\"color: #000000;\" onChange=\"this.style.color=this.options[this.selectedIndex].style.color\" class='form-control'>";
										
										while($t_c = $res_c->fetch_assoc()){
											
											$id_cible = $t_c['id_cible'];
											
											// Recuperation infos cible
											$sql_cible = "SELECT nom_perso, clan FROM perso WHERE id_perso='$id_cible'";
											$res_cible = $mysqli->query($sql_cible);
											$t_cible = $res_cible->fetch_assoc();
											
											$nom_cible 	= $t_cible['nom_perso'];
											$camp_cible = $t_cible['clan'];
											
											// recuperation de la couleur du camp
											$couleur_clan_cible = couleur_clan($camp_cible);
											
											
											echo "					<option style='color:".$couleur_clan_cible.";' value=\"$id_cible\">$nom_cible [$id_cible]</option>";
											
										}
										
										echo "				</select>&nbsp;<input type='submit' name='valid_perso_don' value='valider' class='btn btn-success'/><input type='hidden' name='hid_valid_perso_don' value='valider' />";
										echo "			</form>";
										
										echo "		</div>";
										echo "	</div>";
										echo "</div>";
										
									} else {
									
										echo "<center><font color='red'>Vous n'avez pas assez de PA pour donner un objet (cout : 1 PA)</font></center>";
									
									}
								}
								else {
									// Soins							
									// recuperation des donnees de la carte
									$sql = "SELECT x_carte, y_carte, fond_carte, occupee_carte, image_carte, idPerso_carte FROM $carte WHERE x_carte >= $x_perso - 1 AND x_carte <= $x_perso + 1 AND y_carte <= $y_perso + 1 AND y_carte >= $y_perso - 1 ORDER BY y_carte DESC, x_carte";
									$res = $mysqli->query($sql);
									$tab = $res->fetch_assoc(); 
									
									//<!--Generation de la carte-->
									echo '<table border=0 align="center" cellspacing="0" cellpadding="0" style:no-padding>';
									
									echo "<tr><td>y \ x</td>";  //affichage des abscisses
									for ($i = $x_perso - 1; $i <= $x_perso + 1; $i++) {
										echo "<th width=40 height=40>$i</th>";
									}
									echo "</tr>";
									
									for ($y = $y_perso + 1; $y >= $y_perso - 1; $y--) {
										echo "<th>$y</th>";
										for ($x = $x_perso - 1; $x <= $x_perso + 1; $x++) {
											
											//les coordonnees sont dans les limites
											if ($x >= X_MIN && $y >= Y_MIN && $x <= $X_MAX && $y <= $Y_MAX) {
												
												if ($tab["occupee_carte"]){
													
													$image_perso = $tab["image_carte"];
													$id_perso_carte = $tab["idPerso_carte"];
													
													if($id_perso_carte < 10000 && isset($id_perso_carte) && $id_perso_carte != $id_perso){
														
														// recuperation des infos du perso
														$sql_perso_carte = "SELECT nom_perso, clan FROM perso WHERE id_perso=$id_perso_carte";
														$res_perso_carte = $mysqli->query($sql_perso_carte);
														$t_perso_carte = $res_perso_carte->fetch_assoc();
														
														$nom_perso_carte = $t_perso_carte["nom_perso"];
														$clan_perso_carte = $t_perso_carte["clan"];
														
														if($clan_perso_carte == $clan_perso){
															$clan_pc = 'Nord';
														}
														else {
															$clan_pc = 'Sud';
														}
													
														echo "<form method=\"post\" action=\"action.php\" >";
														echo "<td width=40 height=40 background=\"../fond_carte/".$tab["fond_carte"]."\"> 
																<input type=\"image\" name=\"action_cible\" value=\"$id_perso_carte,$id_action\" border=0 src=\"../images_perso/".$image_perso."\" width=40 height=40 onMouseOver=\"this.src='../images/$image_action';AffBulle('<tr><td>+ Soigner +</td></tr><tr><td><font color=$clan_pc>$nom_perso_carte</font> [$id_perso_carte]</td></tr>');\" onMouseOut=\"this.src='../images_perso/$image_perso';HideBulle();\" >
																<input type=\"hidden\" name=\"hid_action_cible\" value=\"$id_perso_carte,$id_action\" >
															  </td>";
														echo "</form>";
													}
													else {
														// PNJ
														if($id_perso_carte < 50000 && isset($id_perso_carte) && $id_perso_carte != $id_perso){
															echo "<form method=\"post\" action=\"action.php\" >";
															echo "<td width=40 height=40 background=\"../fond_carte/".$tab["fond_carte"]."\"> <input type=\"image\" name=\"action_cible\" value=\"$id_perso_carte,$id_action\" border=0 src=\"../images_perso/".$image_perso."\" width=40 height=40 onMouseOver=\"this.src='../images/$image_action';AffBulle('<tr><td>+ Soigner +</td></tr><tr><td>$nom_pnj [$id_perso_carte]</td></tr>');\" onMouseOut=\"this.src='../images_perso/$image_pnj';HideBulle();\" ><input type=\"hidden\" name=\"hid_action_cible\" value=\"$id_perso_carte,$id_action\" ></td>";
															echo "</form>";
														}
														else {
															echo "<td width=40 height=40 background=\"../fond_carte/".$tab["fond_carte"]."\"><img border=0 src=\"../images_perso/".$tab["image_carte"]."\" width=40 height=40 \></td>";
														}
													}
												}
												else{
													//positionnement du fond
													$fond_carte = $tab["fond_carte"];
													
													echo "<td width=40 height=40> <img border=0 src=\"../fond_carte/$fond_carte\" width=40 height=40 ></td>";
												}
												$tab = $res->fetch_assoc();
											}
											else //les coordonnees sont hors limites
												echo "<td width=40 height=40><img border=0 width=40 height=40 src=\"../fond_carte/decorO.jpg\"></td>";
										}
										echo "</tr>";
									}
									echo "</table>";
									// fin de la generation de la carte
								}
								
								// lien annuler
								echo "<br /><br /><center><a class='btn btn-danger' href='jouer.php'><b>annuler</b></a></center>";
							}
						}
					
						// action ayant pour cible une case
						if($case_action){
							
							if(!in_bat($mysqli, $id_perso)){
								
								// action pouvant affecter les cases aux alentours du perso
								if($portee_action){
									
									if ($nom_action=='Construire - Fort' || $nom_action=='Construire - Fortin' || $nom_action == 'Construire - Hopital'
										|| $nom_action == 'Construire - Tour de guet' || $nom_action == 'Construire - Gare'
										|| $nom_action == 'Construire - Pont' || $nom_action == 'Construire - Barricade'){
										
										// recuperation du batiment
										$sql = "SELECT batiment.id_batiment, batiment.nom_batiment, batiment.taille_batiment, clan 
												FROM action_as_batiment, batiment, perso
												WHERE id_action='$id_action'
												AND id_perso='$id_perso'
												AND batiment.id_batiment=action_as_batiment.id_batiment";
										$res = $mysqli->query($sql);
										$num_bat = $res->num_rows;
										
										$taille_batiment = 1;
										
										if($num_bat){
											
											$t_bat = $res->fetch_assoc();
											
											$id_bat 		= $t_bat['id_batiment'];
											$nom_batiment 	= $t_bat['nom_batiment'];
											$taille_batiment= $t_bat['taille_batiment'];
											$camp_batiment 	= $t_bat['clan'];
											
											switch($camp_batiment){
												case "1":
													$camp_b = 'b';
													break;
												case "2":
													$camp_b = 'r';
													break;
												default:
													$camp_b = 'g';
												
											}
											
											$image_bat = "b".$id_bat."".$camp_b.".png";
										}
										
										echo "<center><img src=\"../images_perso/$image_bat\" alt=\"$nom_batiment\" /></center>";
										echo "<center><b>$nom_batiment</b></center>";
									
										//recuperation des coordonnees du perso
										$sql = "SELECT x_perso, y_perso FROM perso WHERE id_perso='$id_perso'";
										$res = $mysqli->query($sql);
										$t_coord = $res->fetch_assoc();
										
										$x_perso = $t_coord['x_perso'];
										$y_perso = $t_coord['y_perso'];
										
										// recuperation des donnees de la carte
										$sql = "SELECT x_carte, y_carte, fond_carte, occupee_carte, image_carte, idPerso_carte 
												FROM $carte WHERE x_carte >= $x_perso - $taille_batiment AND x_carte <= $x_perso + $taille_batiment AND y_carte <= $y_perso + $taille_batiment AND y_carte >= $y_perso - $taille_batiment 
												ORDER BY y_carte DESC, x_carte";
										$res = $mysqli->query($sql);
										$tab = $res->fetch_assoc(); 
										
										//<!--Generation de la carte-->
										echo '<table border=0 align="center" cellspacing="0" cellpadding="0" style:no-padding>';
										
										echo "<tr><td>y \ x</td>";  //affichage des abscisses
										for ($i = $x_perso - $taille_batiment; $i <= $x_perso + $taille_batiment; $i++) {
											echo "<th width=40 height=40>$i</th>";
										}
										echo "</tr>";
										
										for ($y = $y_perso + $taille_batiment; $y >= $y_perso - $taille_batiment; $y--) {
											
											echo "<th>$y</th>";
											
											for ($x = $x_perso - $taille_batiment; $x <= $x_perso + $taille_batiment; $x++) {
												
												//les coordonnees sont dans les limites
												if ($x >= X_MIN && $y >= Y_MIN && $x <= $X_MAX && $y <= $Y_MAX) {
													
													if ($tab["occupee_carte"]){
														
														$idPerso_carte = $tab['idPerso_carte'];
														
														if ($idPerso_carte < 200000) {
															
															if ($idPerso_carte < 50000) {
																echo "<td width=40 height=40 background=\"../fond_carte/".$tab["fond_carte"]."\">";
																echo "	<div width=40 height=40 style=\"position: relative;\">";
																echo "		<div tabindex='0' style='position: absolute;bottom: -2px;text-align: center; width: 100%;font-weight: bold;'>" . $idPerso_carte . "</div>";
																echo "		<img tabindex='0' class='' border=0 src=\"../images_perso/".$tab["image_carte"]."\" width=40 height=40 >";
																echo "	</div>";
																echo "</td>";
															}
															else {
																// batiment
																echo "<td width=40 height=40 background=\"../fond_carte/".$tab["fond_carte"]."\">";
																echo "	<img border=0 src=\"../images_perso/".$tab["image_carte"]."\" width=40 height=40 \>";
																echo "</td>";
															}
														}
														else {
															// PNJ
															echo "<td width=40 height=40 background=\"../fond_carte/".$tab["fond_carte"]."\">";
															echo "	<img border=0 src=\"../images/pnj/".$tab["image_carte"]."\" width=40 height=40 \>";
															echo "</td>";
														}
													}
													else{
														
														echo "<form method=\"post\" action=\"action.php\" >";
													
														//positionnement du fond
														$fond_carte = $tab["fond_carte"];
														
														//barricade, tours, batiments => constructibles sur plaine seulement
														if($id_bat == '1' || $id_bat == '2' || $id_bat == '3' || $id_bat == '6' || $id_bat == '7' || $id_bat == '8' || $id_bat == '9' || $id_bat == '10' || $id_bat == '11'){
															
															// Possibilité de construire barricade sur rail
															if($fond_carte == 'rail.gif' OR $fond_carte=='rail_1.gif' OR $fond_carte=='rail_2.gif' OR $fond_carte=='rail_3.gif' OR $fond_carte=='rail_4.gif' OR $fond_carte=='rail_5.gif' OR $fond_carte=='rail_7.gif' OR $fond_carte=='railP.gif'){
																if($id_bat == '1') {
																	echo "
																	<td width=40 height=40> 
																		<input type=\"image\" name=\"image_bat\" value=\"$x,$y,$id_bat\" border=0 src=\"../fond_carte/$fond_carte\" width=40 height=40 
																			onMouseOver=\"this.src='../images_perso/$image_bat';\" 
																			onMouseOut=\"this.src='../fond_carte/$fond_carte';\" >
																		<input type=\"hidden\" name=\"hid_image_bat\" value=\"$x,$y,$id_bat\" >
																	</td>";
																}
																else {
																	echo "<td width=40 height=40> <img border=0 src=\"../fond_carte/$fond_carte\" width=40 height=40 ></td>";
																}
															}
															else if($fond_carte == '1.gif'){
																echo "
																	<td width=40 height=40> 
																		<input type=\"image\" name=\"image_bat\" value=\"$x,$y,$id_bat\" border=0 src=\"../fond_carte/$fond_carte\" width=40 height=40 
																			onMouseOver=\"this.src='../images_perso/$image_bat';\" 
																			onMouseOut=\"this.src='../fond_carte/$fond_carte';\" >
																		<input type=\"hidden\" name=\"hid_image_bat\" value=\"$x,$y,$id_bat\" >
																	</td>";
															}
															else {
																echo "<td width=40 height=40> <img border=0 src=\"../fond_carte/$fond_carte\" width=40 height=40 ></td>";
															}
														}
														// ponts => constructibles sur eau seulement
														else if($id_bat == '5'){
															if($fond_carte == '8.gif' || $fond_carte == '9.gif'){
																echo "
																	<td width=40 height=40> 
																		<input type=\"image\" name=\"image_bat\" value=\"$x,$y,$id_bat\" border=0 src=\"../fond_carte/$fond_carte\" width=40 height=40 
																			onMouseOver=\"this.src='../images_perso/$image_bat';\" 
																			onMouseOut=\"this.src='../fond_carte/$fond_carte';\" >
																		<input type=\"hidden\" name=\"hid_image_bat\" value=\"$x,$y,$id_bat\" >
																	</td>";
															}
															else {
																echo "<td width=40 height=40> <img border=0 src=\"../fond_carte/$fond_carte\" width=40 height=40 ></td>";
															}
														}
														// routes => constructibles sur tout terrain sauf eau
														else if($id_bat == '4'){
															if($fond_carte != '8.gif' && $fond_carte != '9.gif'){
																echo "<td width=40 height=40> <input type=\"image\" name=\"image_bat\" value=\"$x,$y,$id_bat\" border=0 src=\"../fond_carte/$fond_carte\" width=40 height=40 onMouseOver=\"this.src='../images_perso/$image_bat';\" onMouseOut=\"this.src='../fond_carte/$fond_carte';\" ><input type=\"hidden\" name=\"hid_image_bat\" value=\"$x,$y,$id_bat\" ></td>";
															}
															else {
																echo "<td width=40 height=40> <img border=0 src=\"../fond_carte/$fond_carte\" width=40 height=40 ></td>";
															}
														}
														else {
															echo "<td width=40 height=40> <img border=0 src=\"../fond_carte/$fond_carte\" width=40 height=40 ></td>";
														}
														
													}
													
													echo "</form>";
													
													$tab = $res->fetch_assoc();
												}
												else //les coordonnees sont hors limites
													echo "<td width=40 height=40><img border=0 width=40 height=40 src=\"../fond_carte/decorO.jpg\"></td>";
											}
											echo "</tr>";
										}
										
										echo "</table>";
										// fin de la generation de la carte
										
										// lien annuler
										echo "<br /><br /><center><a class='btn btn-primary' href='jouer.php'><b>annuler</b></a></center>";
									}
									else if ($nom_action == 'Construire - Rail') {
										
										$image_bat = "rail.gif";
										$taille_batiment = 1;
										
										//recuperation des coordonnees du perso
										$sql = "SELECT x_perso, y_perso, pa_perso FROM perso WHERE id_perso='$id_perso'";
										$res = $mysqli->query($sql);
										$t_coord = $res->fetch_assoc();
										
										$x_perso 	= $t_coord['x_perso'];
										$y_perso 	= $t_coord['y_perso'];
										$pa_perso	= $t_coord['pa_perso'];
										
										// Il faut minimum 4 PA pour construire un rail
										if ($pa_perso >= 4) {
											
											echo "<div align='center' id='infoCoutPA'>&nbsp;</div>";
											
											// recuperation des donnees de la carte
											$sql = "SELECT x_carte, y_carte, fond_carte, occupee_carte, image_carte, idPerso_carte 
													FROM $carte WHERE x_carte >= $x_perso - $taille_batiment AND x_carte <= $x_perso + $taille_batiment AND y_carte <= $y_perso + $taille_batiment AND y_carte >= $y_perso - $taille_batiment 
													ORDER BY y_carte DESC, x_carte";
											$res = $mysqli->query($sql);
											$tab = $res->fetch_assoc(); 
											
											//<!--Generation de la carte-->
											echo '<table border=0 align="center" cellspacing="0" cellpadding="0" style:no-padding>';
											
											echo "<tr><td>y \ x</td>";  //affichage des abscisses
											for ($i = $x_perso - $taille_batiment; $i <= $x_perso + $taille_batiment; $i++) {
												echo "<th width=40 height=40>$i</th>";
											}
											echo "</tr>";
											
											for ($y = $y_perso + $taille_batiment; $y >= $y_perso - $taille_batiment; $y--) {
												echo "<th>$y</th>";
												for ($x = $x_perso - $taille_batiment; $x <= $x_perso + $taille_batiment; $x++) {
													
													//les coordonnees sont dans les limites
													if ($x >= X_MIN && $y >= Y_MIN && $x <= $X_MAX && $y <= $Y_MAX) {
														
														if ($tab["occupee_carte"]){
															echo "<td width=40 height=40 background=\"../fond_carte/".$tab["fond_carte"]."\"><img border=0 src=\"../images_perso/".$tab["image_carte"]."\" width=40 height=40 \></td>";
														}
														else{
															echo "<form method=\"post\" action=\"action.php\" >";
														
															//positionnement du fond
															$fond_carte = $tab["fond_carte"];
															
															if($fond_carte == '1.gif') {
																echo "
																	<td width=40 height=40> 
																		<input type=\"image\" name=\"pose_rail\" value=\"$x,$y\" border=0 src=\"../fond_carte/$fond_carte\" width=40 height=40 
																			onMouseOver=\"this.src='../fond_carte/$image_bat'; document.getElementById('infoCoutPA').innerHTML = '<b><u>cout PA :</u> 4</b>'; \" 
																			onMouseOut=\"this.src='../fond_carte/$fond_carte'; document.getElementById('infoCoutPA').innerHTML = '&nbsp;'; \" >
																		<input type=\"hidden\" name=\"hid_pose_rail\" value=\"$x,$y,$fond_carte\" >
																	</td>";
															}
															else if ($fond_carte == '2.gif') {
																echo "
																	<td width=40 height=40> 
																		<input type=\"image\" name=\"pose_rail\" value=\"$x,$y\" border=0 src=\"../fond_carte/$fond_carte\" width=40 height=40 
																			onMouseOver=\"this.src='../fond_carte/$image_bat'; document.getElementById('infoCoutPA').innerHTML = '<b><u>cout PA :</u> 6</b>'; \" 
																			onMouseOut=\"this.src='../fond_carte/$fond_carte'; document.getElementById('infoCoutPA').innerHTML = '&nbsp;'; \" >
																		<input type=\"hidden\" name=\"hid_pose_rail\" value=\"$x,$y,$fond_carte\" >
																	</td>";
															}
															else if ($fond_carte == '3.gif') {
																echo "
																	<td width=40 height=40> 
																		<input type=\"image\" name=\"pose_rail\" value=\"$x,$y\" border=0 src=\"../fond_carte/$fond_carte\" width=40 height=40 
																			onMouseOver=\"this.src='../fond_carte/$image_bat'; document.getElementById('infoCoutPA').innerHTML = '<b><u>cout PA :</u> 8</b>'; \" 
																			onMouseOut=\"this.src='../fond_carte/$fond_carte'; document.getElementById('infoCoutPA').innerHTML = '&nbsp;'; \" >
																		<input type=\"hidden\" name=\"hid_pose_rail\" value=\"$x,$y,$fond_carte\" >
																	</td>";
															}
															else if ($fond_carte == '4.gif') {
																echo "
																	<td width=40 height=40> 
																		<input type=\"image\" name=\"pose_rail\" value=\"$x,$y\" border=0 src=\"../fond_carte/$fond_carte\" width=40 height=40 
																			onMouseOver=\"this.src='../fond_carte/$image_bat'; document.getElementById('infoCoutPA').innerHTML = '<b><u>cout PA :</u> 4</b> -- <b><u>cout PV :</u> 50</b>'; \" 
																			onMouseOut=\"this.src='../fond_carte/$fond_carte'; document.getElementById('infoCoutPA').innerHTML = '&nbsp;'; \" >
																		<input type=\"hidden\" name=\"hid_pose_rail\" value=\"$x,$y,$fond_carte\" >
																	</td>";
															}
															else if ($fond_carte == '5.gif') {
																echo "
																	<td width=40 height=40> 
																		<input type=\"image\" name=\"pose_rail\" value=\"$x,$y\" border=0 src=\"../fond_carte/$fond_carte\" width=40 height=40 
																			onMouseOver=\"this.src='../fond_carte/$image_bat'; document.getElementById('infoCoutPA').innerHTML = '<b><u>cout PA :</u> 5</b>'; \" 
																			onMouseOut=\"this.src='../fond_carte/$fond_carte'; document.getElementById('infoCoutPA').innerHTML = '&nbsp;'; \" >
																		<input type=\"hidden\" name=\"hid_pose_rail\" value=\"$x,$y,$fond_carte\" >
																	</td>";
															}
															else if ($fond_carte == '7.gif') {
																echo "
																	<td width=40 height=40> 
																		<input type=\"image\" name=\"pose_rail\" value=\"$x,$y\" border=0 src=\"../fond_carte/$fond_carte\" width=40 height=40 
																			onMouseOver=\"this.src='../fond_carte/$image_bat'; document.getElementById('infoCoutPA').innerHTML = '<b><u>cout PA :</u> 6</b>'; \" 
																			onMouseOut=\"this.src='../fond_carte/$fond_carte'; document.getElementById('infoCoutPA').innerHTML = '&nbsp;'; \" >
																		<input type=\"hidden\" name=\"hid_pose_rail\" value=\"$x,$y,$fond_carte\" >
																	</td>";
															}
															else if ($fond_carte == '8.gif') {
																echo "
																	<td width=40 height=40> 
																		<input type=\"image\" name=\"pose_rail\" value=\"$x,$y\" border=0 src=\"../fond_carte/$fond_carte\" width=40 height=40 
																			onMouseOver=\"this.src='../fond_carte/$image_bat'; document.getElementById('infoCoutPA').innerHTML = '<b><u>cout PA :</u> 8</b>'; \" 
																			onMouseOut=\"this.src='../fond_carte/$fond_carte'; document.getElementById('infoCoutPA').innerHTML = '&nbsp;'; \" >
																		<input type=\"hidden\" name=\"hid_pose_rail\" value=\"$x,$y,$fond_carte\" >
																	</td>";
															}
															else {
																echo "<td width=40 height=40> <img border=0 src=\"../fond_carte/$fond_carte\" width=40 height=40 ></td>";
															}
															echo "</form>";
														}
														$tab = $res->fetch_assoc();
													}
													else //les coordonnees sont hors limites
														echo "<td width=40 height=40><img border=0 width=40 height=40 src=\"../fond_carte/decorO.jpg\"></td>";
												}
												echo "</tr>";
											}
											echo "</table>";
											// fin de la generation de la carte
											
											// lien annuler
											echo "<br /><br /><center><a class='btn btn-primary' href='jouer.php'><b>annuler</b></a></center>";
										}
										else {
											echo "<center>Il vous faut minimum 4 PA pour construire un rail</center>";
											
											// lien retour
											echo "<br /><br /><center><a class='btn btn-primary' href='jouer.php'><b>retour</b></a></center>";
										}
									}
									
									// reparer batiment
									if($nom_action == 'Réparer bâtiment'){
										
										echo "<center><h2>$nom_action</h2></center>";
									
										//recuperation des coordonnees du perso
										$sql = "SELECT x_perso, y_perso, clan FROM perso WHERE id_perso='$id_perso'";
										$res = $mysqli->query($sql);
										$t_coord = $res->fetch_assoc();
										
										$x_perso = $t_coord['x_perso'];
										$y_perso = $t_coord['y_perso'];
										$clan_perso = $t_coord['clan'];
										
										// recuperation des donnees de la carte
										$sql = "SELECT x_carte, y_carte, fond_carte, occupee_carte, image_carte, idPerso_carte FROM $carte WHERE x_carte >= $x_perso - 1 AND x_carte <= $x_perso + 1 AND y_carte <= $y_perso + 1 AND y_carte >= $y_perso - 1 ORDER BY y_carte DESC, x_carte";
										$res = $mysqli->query($sql);
										$tab = $res->fetch_assoc(); 
										
										//<!--Generation de la carte-->
										echo '<table border=0 align="center" cellspacing="0" cellpadding="0" style:no-padding>';
										
										echo "<tr><td>y \ x</td>";  //affichage des abscisses
										for ($i = $x_perso - 1; $i <= $x_perso + 1; $i++) {
											echo "<th width=40 height=40>$i</th>";
										}
										echo "</tr>";
										
										for ($y = $y_perso + 1; $y >= $y_perso - 1; $y--) {
											echo "<th>$y</th>";
											for ($x = $x_perso - 1; $x <= $x_perso + 1; $x++) {
												
												//les coordonnees sont dans les limites
												if ($x >= X_MIN && $y >= Y_MIN && $x <= $X_MAX && $y <= $Y_MAX) {
													
													if ($tab["occupee_carte"]){
														
														$image_bat = $tab["image_carte"];
														$id_bat_carte = $tab["idPerso_carte"];
														if($id_bat_carte > 50000 && isset($id_bat_carte)){
															
															// recuperation des infos du batiment
															$sql_bat_carte = "SELECT nom_batiment, nom_instance, camp_instance, pv_instance, pvMax_instance FROM batiment, instance_batiment WHERE id_instanceBat=$id_bat_carte AND batiment.id_batiment = instance_batiment.id_batiment";
															$res_bat_carte = $mysqli->query($sql_bat_carte);
															$t_bat_carte = $res_bat_carte->fetch_assoc();
															
															$nom_bat_carte = $t_bat_carte["nom_batiment"];
															$clan_bat_carte = $t_bat_carte["camp_instance"];
															
															if($clan_bat_carte == $clan_perso){
																$clan_pc = 'Nord';
															}
															else {
																$clan_pc = 'Sud';
															}
														
															echo "<form method=\"post\" action=\"action.php\" >";
															echo "<td width=40 height=40 background=\"../fond_carte/".$tab["fond_carte"]."\"> <input type=\"image\" name=\"action_cible_bat\" value=\"$id_bat_carte,$id_action\" border=0 src=\"../images_perso/".$image_bat."\" width=40 height=40 onMouseOver=\"this.src='../images/$image_action';AffBulle('<tr><td>+ Reparer +</td></tr><tr><td><font color=$clan_pc>$nom_bat_carte</font> [$id_bat_carte]</td></tr>');\" onMouseOut=\"this.src='../images_perso/$image_bat';HideBulle();\" ><input type=\"hidden\" name=\"hid_action_cible_bat\" value=\"$id_bat_carte,$id_action\" ></td>";
															echo "</form>";
														}
														else {
															echo "<td width=40 height=40 background=\"../fond_carte/".$tab["fond_carte"]."\"><img border=0 src=\"../images_perso/".$tab["image_carte"]."\" width=40 height=40 \></td>";
														}
													}
													else{
														//positionnement du fond
														$fond_carte = $tab["fond_carte"];
														
														echo "<td width=40 height=40> <img border=0 src=\"../fond_carte/$fond_carte\" width=40 height=40 ></td>";
													}
													$tab = $res->fetch_assoc();
												}
												else //les coordonnees sont hors limites
													echo "<td width=40 height=40><img border=0 width=40 height=40 src=\"../fond_carte/decorO.jpg\"></td>";
											}
											echo "</tr>";
										}
										echo "</table>";
										// fin de la generation de la carte
										
										// lien annuler
										echo "<br /><br /><center><a class='btn btn-primary' href='jouer.php'><b>annuler</b></a></center>";
									}
								}
								// action a faire sur la case courante du perso
								else {
									
									//couper du bois
									if($nom_action=='Couper du bois'){
										action_couper_bois($mysqli, $id_perso, $id_action, $nb_points_action);
									}
									
									// saboter
									if($nom_action == 'Saboter'){
										action_saboter($mysqli, $id_perso, $id_action, $nb_points_action);
									}
									
									// planter arbre
									if($nom_action == 'Planter arbre'){
										action_planterArbre($mysqli, $id_perso, $id_action, $nb_points_action);
									}
									
									// deposer objet
									if($nom_action == 'Deposer objet'){
										
										// lien retour
										echo "<br /><center><a class='btn btn-primary' href='jouer.php'><b>retour</b></a></center><br />";
									
										echo "<table border='1' class='table table-bordered'>";
										echo "	<tr>";
										echo "		<th colspan='4' style='text-align:center'>Objets déposables<br /></th>";
										echo "	</tr>";
										echo "	<tr>";
										echo "		<th style='text-align:center'>image</th>";
										echo "		<th style='text-align:center'>poid unitaire</th>";
										echo "		<th style='text-align:center'>nombre</th>";
										echo "		<th style='text-align:center'>déposer à terre ?</th>";
										echo "	</tr>";
										
										// Recuperation des objets / armes / armures que possede le perso
										// Or
										$compteur_or = 0;
										$sql_o0 = "SELECT or_perso FROM perso WHERE id_perso='$id_perso'";
										$res_o0 = $mysqli->query($sql_o0);
										$t_o0 = $res_o0->fetch_assoc();
										
										$or_perso = $t_o0['or_perso'];
										
										echo "<tr>";
										echo "<td><img src='../images/or.png' alt='thune' height='40' width='40' /><span><b>Thune</b></span></td>";
										echo "<td align='center'>0</td>";
												
										echo "<form method='post' action='action.php'>";		
										echo "<td align='center'>";
										echo "<select name=\"select_quantite_depot\">";
										while ($compteur_or <= $or_perso){
											echo "<option value=\"$compteur_or\">$compteur_or</option>";
											$compteur_or++;
										}
										echo "</select>";
										echo "</td>";
										echo "<td align='center'><input type='submit' name='valid_objet_depo' value='oui' class='btn btn-warning' /><input type='hidden' name='id_objet_depo' value='0,1,0' /></td>";	
										echo "</form>";
										echo "</tr>";
										
										// Objets (sauf ticket de train qui sont nomminatifs)

										$sql_o = "SELECT DISTINCT id_objet FROM perso_as_objet WHERE id_perso='$id_perso' AND equip_objet='0' ORDER BY id_objet";

										$res_o = $mysqli->query($sql_o);
										
										while($t_o = $res_o->fetch_assoc()){
											
											$compteur = 1;
											
											$id_objet = $t_o["id_objet"];
											
											// recuperation des carac de l'objet
											$sql1_o = "SELECT nom_objet, poids_objet, image_objet, deposable FROM objet WHERE id_objet='$id_objet'";
											$res1_o = $mysqli->query($sql1_o);
											$t1_o = $res1_o->fetch_assoc();
											$nom_o = $t1_o["nom_objet"];
											$poids_o = $t1_o["poids_objet"];
											$image_o = $t1_o["image_objet"];
											$deposable = $t1_o["deposable"];

											if ($deposable != 0) {
											
											// recuperation du nombre d'objet de ce type que possede le perso
											$sql2_o = "SELECT id_objet FROM perso_as_objet WHERE id_perso='$id_perso' AND equip_objet='0' AND id_objet='$id_objet'";
											$res2_o = $mysqli->query($sql2_o);
											$nb_o = $res2_o->num_rows;
											
											echo "<tr>";
											echo "	<td><img src='../public/img/items/".$image_o."' alt='$nom_o' height='50' width='50'/><span><b>".stripslashes($nom_o)."</b></span></td>";
											echo "	<td align='center'>$poids_o</td>";
											echo "<form method='post' action='action.php'>";
											echo "	<td align='center'>";
											if ($nb_o > 1) {
												echo "<select name=\"select_quantite_depot\" class='form-control' style='text-align: center;'>";
												while ($compteur <= $nb_o){
													echo "<option value=\"$compteur\">$compteur</option>";
													$compteur++;
												}
												echo "</select>";
											}
											else {
												echo "1";
											}
											echo "</td>";
											echo "	<td align='center'><input type='submit' name='valid_objet_depo' value='oui' class='btn btn-warning' /><input type='hidden' name='id_objet_depo' value='$id_objet,2,0' /></td>";
											echo "</form>";
											echo "</tr>";
											}
										}
										
										// Armes non portes
										$sql_a1 = "SELECT DISTINCT id_arme FROM perso_as_arme WHERE id_perso='$id_perso' AND est_portee='0' ORDER BY id_arme";
										$res_a1 = $mysqli->query($sql_a1);
										
										while($t_a1 = $res_a1->fetch_assoc()){
											
											$compteur  = 1;
											
											$id_arme = $t_a1["id_arme"];
											
											// recuperation des carac de l'arme
											$sql1_a1 = "SELECT nom_arme, poids_arme, image_arme FROM arme WHERE id_arme='$id_arme'";
											$res1_a1 = $mysqli->query($sql1_a1);
											$t1_a1 = $res1_a1->fetch_assoc();
											$nom_a1 = $t1_a1["nom_arme"];
											$poids_a1 = $t1_a1["poids_arme"];
											$image_arme = $t1_a1["image_arme"];
											
											// recuperation du nombre d'armes non equipes de ce type que possede le perso 
											$sql2_a1 = "SELECT id_arme FROM perso_as_arme WHERE id_perso='$id_perso' AND id_arme='$id_arme' AND est_portee='0'";
											$res2_a1 = $mysqli->query($sql2_a1);
											$nb_a1 = $res2_a1->num_rows;
											
											echo "<tr>";
											echo "	<td><img src='../images/armes/$image_arme' alt='$nom_a1' height='50' width='50'/><span><b>".stripslashes($nom_a1)."</b></span></td>";
											echo "	<td align='center'>$poids_a1</td>";
											echo "<form method='post' action='action.php'>";
											echo "	<td align='center'>";
											if ($nb_o > 1) {
												echo "<select name=\"select_quantite_depot\">";
												while ($compteur <= $nb_a1){
													echo "<option value=\"$compteur\">$compteur</option>";
													$compteur++;
												}
												echo "</select>";
											}
											else {
												echo "1";
											}
											echo "</td>";
											echo "	<td align='center'><input type='submit' name='valid_objet_depo' value='oui' class='btn btn-warning' /><input type='hidden' name='id_objet_depo' value='$id_arme,3' /></td>";
											echo "</form>";
											echo "</tr>";
										}
										
										echo "</table><br /><br /><br /><br /><br /><br />";
									}
								}
							}
							else {
								echo "<center><font color='red'>Impossible d'effectuer cette action depuis un bâtiment, veuillez sortir pour effectuer cette action</font>";
								echo "<br /><a class='btn btn-primary' href='jouer.php'>retour</a></center>";
							}
						}
					}					
				}
				else {
					// triche
					$text_triche = "Tentative action inexistante ou qu'on ne possède pas";
				
					$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id_perso', '$text_triche')";
					$mysqli->query($sql);
					
					// redirection
					header("location:jouer.php");
				}
			}
			else {
				if(isset($_POST['liste_action']) && $_POST['liste_action'] == 'PA'){
					echo "Pas assez de PA";
					echo "<center><a class='btn btn-primary' href='jouer.php'>retour</a></center>";
				}
				if(isset($_POST['liste_action']) && $_POST['liste_action'] == 'invalide'){
					echo "Invalide";
					echo "<center><a class='btn btn-primary' href='jouer.php'>retour</a></center>";
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
		  $('[data-toggle="tooltip"]').tooltip()
		})
		</script>
	
	</body>
</html>
<?php
	}
	else {
		// logout
		$_SESSION = array();
		session_destroy();

		header("Location:../index2.php");
	}
}
else {
	// logout
	$_SESSION = array();
	session_destroy();
	
	header("Location:../index2.php");
}
?>
