<?php
@session_start();
require_once("../fonctions.php");
require_once("f_carte.php");
require_once("f_entete.php");

$mysqli = db_connexion();

include ('../nb_online.php');

if(@$_SESSION["id_perso"]){
	
	$id_perso = $_SESSION["id_perso"];
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<title>Evènements</title>
		
		<!-- Required meta tags -->
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		
		<!-- Bootstrap CSS -->
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
	</head>
	<body>
		<div align="center">
			<h2>Evènements</h2>
		</div>
			
		<p align="center"><input type="button" value="Fermer cette fenêtre" onclick="window.close()"></p>
	<?php
	
	if(isset($_POST["id_info"])){
		
		// verifier que la valeur est valide
		$id_tmp = $_POST["id_info"];
		$verif = preg_match("#^[0-9]*[0-9]$#i","$id_tmp");
		
		if($verif){
			$id = $_POST["id_info"];
		}
		else {
			echo "<center><b>Erreur :</b> La valeur entrée n'est pas correcte !</center>";
		}
	}
	else {
		if(isset($_GET["infoid"])){
			
			// verifier que la valeur est valide
			$id_tmp = $_GET["infoid"];
			$verif = preg_match("#^[0-9]*[0-9]$#i","$id_tmp");
			
			if($verif){
				
				$id = $_GET["infoid"];
				
				// on souhaite connaitre la liste des persos d'un batiment
				if(isset($_GET["liste"]) && $_GET["liste"] == "ok") {
				
					// test si c'est bien un batiment
					if ($_GET['infoid'] >= 50000 && $_GET['infoid'] < 200000) {
						
						// test si le batiment existe
						$sql = "SELECT id_batiment FROM instance_batiment WHERE id_instanceBat='$id'";
						$res = $mysqli->query($sql);
						$t_b = $res->fetch_assoc();
						$nb_b = $res->num_rows;
						
						$id_bat = $t_b["id_batiment"];
						
						if ($nb_b == 0) { // il existe
							
							echo "<font color = red><center>Le batiment selectionné n'existe pas ou a été détruit</center></font>";
							
						}
						
						if ($id_bat == 12) {
							// recuperation de la liste des persos dans le train
							$sql_liste = "SELECT nom_perso, perso.id_perso FROM perso_in_train, perso WHERE perso.id_perso=perso_in_train.id_perso AND id_train='$id'";
						}
						else {
							// recuperation de la liste des persos dans le batiment
							$sql_liste = "SELECT nom_perso, perso.id_perso FROM perso_in_batiment, perso WHERE perso.id_perso=perso_in_batiment.id_perso AND id_instanceBat='$id'";
							
						}
						
						$res_liste = $mysqli->query($sql_liste);
						$verif_liste = '1';
					}
					else {
						echo "<font color = red><center>vous ne pouvez lister la liste des perso que sur un batiment</center></font>";
					}
				}
			}
			else {
				echo "<center><b>Erreur :</b> La valeur entrée n'est pas correcte !</center>";
			}		
		}	
		else{
			$id = $_SESSION["id_perso"];
		}
	}

	if(isset($id)){
		
		if($id < 50000){
			// verifier que le perso existe
			$sql = "SELECT id_perso FROM perso WHERE id_perso='$id'";
			$res = $mysqli->query($sql);
			$nb_p = $res->num_rows;
		}
		else {
			if($id >= 200000){
				// verifier que le pnj existe
				$sql = "SELECT idInstance_pnj FROM instance_pnj WHERE idInstance_pnj='$id'";
				$res = $mysqli->query($sql);
				$nb_p = $res->num_rows;
			}
			else {
				// verifier que le batiment existe
				$sql = "SELECT id_instanceBat, camp_instance FROM instance_batiment WHERE id_instanceBat='$id'";
				$res = $mysqli->query($sql);
				$nb_p = $res->num_rows;
				$t_ci = $res->fetch_assoc();
			}
		}

		if($nb_p == '1'){
			// l'entité existe bien
			entete($mysqli, $id);
		}
		else {
			entete_mort($mysqli, $id);
		}
	
		if(isset($verif_liste) && $verif_liste){
			
			// verifier camp perso
			$sql = "select clan from perso where id_perso='$id_perso'";
			$res = $mysqli->query($sql);
			$t_c = $res->fetch_assoc();
			
			$camp_perso = $t_c["clan"];
			$camp_bat 	= $t_ci["camp_instance"];
			
			if($camp_perso == $camp_bat){
				
				// si à l'interieur
				if(in_instance_bat($mysqli, $id_perso, $id) || in_instance_train($mysqli, $id_perso, $id)){
					
					echo "<center>";
					echo "<b>Liste des persos dans le bâtiment</b><br />";
					
					while($liste = $res_liste->fetch_assoc()) {
						
						$nom_p 	= $liste["nom_perso"];
						$id_p 	= $liste["id_perso"];
						
						echo "$nom_p [<a href=\"evenement.php?infoid=".$id_p."\">$id_p</a>]";
					}
					
					echo "</center>";
					echo "<br />";
				}
				else {
					echo "<center>";
					echo "<b>Nombre de persos dans le bâtiment</b><br />";
					$nb_l = $res_liste->num_rows;
					echo "<i>Il y a <b>$nb_l</b> persos dans ce batiment</i>";
					echo "</center>";
					echo "<br />";
				}
			}
			else {
				echo "<center>";
				echo "<b>Nombre de persos dans le bâtiment</b><br />";
				$nb_l = $res_liste->num_rows;
				echo "<i>Il y a <b>$nb_l</b> persos dans ce batiment</i>";
				echo "</center>";
				echo "<br />";
			}
		}
	
		if ($id) {
	
			?> 
		<table align="center" width="80%" border=1>
			<tr>
				<th style='text-align:center' width="25%">date</th>
				<th style='text-align:center' width="75%">Évènement</th>
			</tr>
			<?php
			
			$sql = "SELECT * FROM evenement WHERE IDActeur_evenement='$id' OR IDCible_evenement='$id' ORDER BY ID_evenement DESC, date_evenement DESC LIMIT 100";
			$res = $mysqli->query($sql);
			
			while ($t = $res->fetch_assoc()) {
				
				$date_evenement 		= $t['date_evenement'];
				$nom_acteur_evenement	= $t['nomActeur_evenement'];
				$id_acteur_evenement	= $t['IDActeur_evenement'];
				$phrase_evenement		= $t['phrase_evenement'];
				$id_cible_evenement		= $t['IDCible_evenement'];
				$nom_cible_evenement	= $t['nomCible_evenement'];
				$effet_evenement		= $t['effet_evenement'];
				$special_evenement		= $t['special'];
				
				if ($id == $id_acteur_evenement) {
					echo "<tr>";
					echo "	<td align='center'>".$date_evenement."</td>";
					
					echo "	<td>".$nom_acteur_evenement." [<a href=\"evenement.php?infoid=".$id_acteur_evenement."\">".$id_acteur_evenement."</a>] ";
					
					echo stripslashes($phrase_evenement)." ";
					
					if ($id_cible_evenement == 0) {
						
						if ($id_acteur_evenement == $id_perso || $id_cible_evenement == $id_perso) {
							echo " ".stripslashes($effet_evenement);
						}
						
						echo "</td>";
					}
					else {
						echo $nom_cible_evenement." [";
						if ($special_evenement == 2) {
							echo "".$id_cible_evenement."] ";
						}
						else {
							echo "<a href=\"evenement.php?infoid=".$id_cible_evenement."\">".$id_cible_evenement."</a>] ";
						}					
						
						if ($id_acteur_evenement == $id_perso || $id_cible_evenement == $id_perso) {
							echo " ".stripslashes($effet_evenement);
						}
						
						echo "</td>";
					}
				}
				else if ($id == $id_cible_evenement && $special_evenement == 0) {
					
					echo "<tr>";
					echo "	<td align='center'>".$date_evenement."</td>";
					
					echo "	<td>".$nom_cible_evenement." [<a href=\"evenement.php?infoid=".$id_cible_evenement."\">".$id_cible_evenement."</a>] ";
					
					if ($phrase_evenement == "a détruit") {
						$phrase_evenement = "a été détruit par";
					}
					else if ($phrase_evenement == "a attaqué ") {
						$phrase_evenement = "a été attaqué par";
					}
					else if ($phrase_evenement == " a raté son attaque contre") {
						$phrase_evenement = "a esquivé l'attaque de";
					}
					else if ($phrase_evenement == "a construit ") {
						$phrase_evenement = "a été construit par";
					}
					else if ($phrase_evenement == "a esquivé l'attaque de") {
						$phrase_evenement = "a raté son attaque contre";
					}
					else if ($phrase_evenement == "a fait un don à ") {
						$phrase_evenement = "a reçu un don de";
					}
					else if ($phrase_evenement == "a soigné ") {
						$phrase_evenement = "a été soigné par";
					}
					else if ($phrase_evenement == "<b>a capturé</b>") {
						$phrase_evenement = "<b>a été capturé par</b>";
					}
					else if ($phrase_evenement == "a capturé") {
						$phrase_evenement = "<b>a été capturé par</b>";
					}					
					else if ($phrase_evenement == " a raté son soin sur") {
						$phrase_evenement = "a trop remué pour recevoir le soin de";
					}
					else if ($phrase_evenement == "a chargé ") {
						$phrase_evenement = "a été chargé par ";
					}
					else if ($phrase_evenement == "a chargé trop tard") {
						$phrase_evenement = "a failli recevoir une charge supplémentaire de";
					}
					else if ($phrase_evenement == "a infligé des dégâts collatéraux ") {
						$phrase_evenement = "a reçu des dégâts collatéraux de";
					}
					else if ($phrase_evenement == "a bousculé ") {
						$phrase_evenement = "a été bousculé par";
					}
					else if ($phrase_evenement == " a reparé le batiment ") {
						$phrase_evenement = "a été réparé par";
					}
					else if ($phrase_evenement == "a raté sa bousculade sur ") {
						$phrase_evenement = "a resisté à la tentative de bousculade de";
					}
					else if ($phrase_evenement == " a détruit un ") {
						$phrase_evenement = "a été détruit par ";
					}
					else if ($phrase_evenement == " a saboté un ") {
						$phrase_evenement = "a été saboté par ";
					}
					else if ($phrase_evenement == " a fait une révision sur le batiment ") {
						$phrase_evenement = "a été révisé par ";
					}
					else if ($phrase_evenement == "a été envoyé au Pénitencier ") {
						$phrase_evenement = "<b>a reçu un nouveau prisonnier :</b> ";
					}
					else if ($phrase_evenement == "<b>a été envoyé au Pénitencier </b>") {
						$phrase_evenement = "<b>a reçu un nouveau prisonnier :</b> ";
					}
					else if ($phrase_evenement == "a tué") {
						$phrase_evenement = "a été tué par";
					}
					
					echo stripslashes($phrase_evenement)." ";
					
					if ($id_acteur_evenement == 0) {
						
						if ($id_acteur_evenement == $id_perso || $id_cible_evenement == $id_perso) {
							echo " ".stripslashes($effet_evenement);
						}
						
						echo "</td>";
					}
					else {
						echo $nom_acteur_evenement." [";
						if ($special_evenement == 2) {
							echo "".$id_acteur_evenement."] ";
						}
						else {
							echo "<a href=\"evenement.php?infoid=".$id_acteur_evenement."\">".$id_acteur_evenement."</a>] ";
						}					
						
						if ($id_acteur_evenement == $id_perso || $id_cible_evenement == $id_perso) {
							echo " ".stripslashes($effet_evenement);
						}
						
						echo "</td>";
					}
				}
			}
		}
	}
	else {
		// rien ^^
	}	
	?>
		</table>
		
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
?>