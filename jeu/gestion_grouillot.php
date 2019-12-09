<?php
session_start();
require_once("../fonctions.php");

$mysqli = db_connexion();

include ('../nb_online.php');

// recupération config jeu
$sql = "SELECT disponible FROM config_jeu";
$res = $mysqli->query($sql);
$t_dispo = $res->fetch_assoc();

$dispo = $t_dispo["disponible"];

if($dispo){

	if (@$_SESSION["id_perso"]) {
		
		//recuperation des varaibles de sessions
		$id = $_SESSION["id_perso"];
		
		$sql = "SELECT idJoueur_perso, chef, pv_perso FROM perso WHERE id_perso='$id'";
		$res = $mysqli->query($sql);
		$tab = $res->fetch_assoc();
		
		$testpv 	= $tab['pv_perso'];
		$id_joueur	= $tab["idJoueur_perso"];
		$chef 		= $tab["chef"];
		
		if ($testpv <= 0) {
			echo "<font color=red>Vous êtes mort...</font>";
		}
		else {
			
			// Seul le chef peut gérer ses grouillots
			if ($chef) {
				
			?>
<html>
	<head>
		<title>Nord VS Sud</title>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			<meta http-equiv="Content-Language" content="fr" />
		<link rel="stylesheet" type="text/css" media="screen" href="onglet.css" title="Version 1" />
	</head>
	
	<body>
		<div id="header">
			<ul>
				<li><a href="profil.php">Profil</a></li>
				<li><a href="ameliorer.php">Améliorer son perso</a></li>
				<?php
				if($chef) {
					echo "<li><a href=\"recrutement.php\">Recruter des grouillots</a></li>";
					echo "<li id=\"current\"><a href=\"#\">Gérer ses grouillots</a></li>";
				}
				?>
				<li><a href="equipement.php">Equiper son perso</a></li>
				<li><a href="compte.php">Gérer son Compte</a></li>
			</ul>
		</div>
	
		<br /><br /><center><h1>Gestion des grouillots</h1></center>
		
		<div align=center><input type="button" value="Fermer cette fenêtre" onclick="window.close()"></div>
		<br />
				<?php
			
				// On souhaite renommer un grouillot
				if (isset($_POST["renommer"]) && isset($_POST["nom_grouillot"]) && isset($_POST["matricule_hidden"])) {
					
					$nouveau_nom_grouillot 	= $_POST["nom_grouillot"];
					$matricule_grouillot 	= $_POST["matricule_hidden"];
					
					if (trim($nouveau_nom_grouillot) != "" && filtre($nouveau_nom_grouillot,1,25)) {
						
						$sql = "UPDATE perso SET nom_perso = '$nouveau_nom_grouillot' WHERE id_perso = '$matricule_grouillot'";
						$mysqli->query($sql);
						
						echo "<center><font color='blue'>Vous avez renommé un de vos grouillots en $nouveau_nom_grouillot</font></center>";
						
					} else {
						echo "<center><b><font color='red'>Veuillez rentrer une valeur correcte sans caractères spéciaux comprise entre 1 et 25 caractères pour le nom de votre grouillot</font></b></center>";
					}
				}
				
				// On souhaite renvoyer un grouillot
				if (isset($_POST["renvoyer"]) && isset($_POST["matricule_renvoi_hidden"])) {
					
					$matricule_grouillot_renvoi = $_POST["matricule_renvoi_hidden"];
					
					// controle matricule perso
					$verif_matricule = preg_match("#^[0-9]*[0-9]$#i","$matricule_grouillot_renvoi");
					
					if ($verif_matricule) {
						
						// On vérifie que le grouillot lui appartient bien
						$sql = "SELECT count(id_perso) as nb_perso FROM perso WHERE id_perso='$matricule_grouillot_renvoi' AND idJoueur_perso='$id_joueur'";
						
						if($res = $mysqli->query($sql)) {
							
							$tab = $res->fetch_assoc();
							
							$nb = $tab["nb_perso"];
							
							if ($nb == 1) {
								
								// Ok - renvoi du perso						
								$sql = "DELETE FROM perso WHERE id_perso='$matricule_grouillot_renvoi'";
								$mysqli->query($sql);
								
								$sql = "DELETE FROM perso_as_arme WHERE id_perso='$matricule_grouillot_renvoi'";
								$mysqli->query($sql);
								
								$sql = "DELETE FROM perso_as_armure WHERE id_perso='$matricule_grouillot_renvoi'";
								$mysqli->query($sql);
								
								$sql = "DELETE FROM perso_as_competence WHERE id_perso='$matricule_grouillot_renvoi'";
								$mysqli->query($sql);
								
								$sql = "DELETE FROM perso_as_contact WHERE id_perso='$matricule_grouillot_renvoi'";
								$mysqli->query($sql);
								
								$sql = "DELETE FROM perso_as_dossiers WHERE id_perso='$matricule_grouillot_renvoi'";
								$mysqli->query($sql);
								
								$sql = "DELETE FROM perso_as_entrainement WHERE id_perso='$matricule_grouillot_renvoi'";
								$mysqli->query($sql);
								
								$sql = "DELETE FROM perso_as_grade WHERE id_perso='$matricule_grouillot_renvoi'";
								$mysqli->query($sql);
								
								$sql = "DELETE FROM perso_as_killpnj WHERE id_perso='$matricule_grouillot_renvoi'";
								$mysqli->query($sql);
								
								$sql = "DELETE FROM perso_as_objet WHERE id_perso='$matricule_grouillot_renvoi'";
								$mysqli->query($sql);
								
								$sql = "DELETE FROM perso_in_batiment WHERE id_perso='$matricule_grouillot_renvoi'";
								$mysqli->query($sql);
								
								$sql = "DELETE FROM perso_in_section WHERE id_perso='$matricule_grouillot_renvoi'";
								$mysqli->query($sql);
								
								$sql = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE idPerso_carte='$matricule_grouillot_renvoi'";
								$mysqli->query($sql);
								
								echo "<center><font color='blue'>Le grouillot avec la matricule $matricule_grouillot_renvoi a bien été renvoyé de votre bataillon.</font></center><br/>";
								
							} else {
								// Tentative de triche ?!
								echo "<font color='red'>Le perso n'a pas pu être renvoyé, si le problème persiste, veuillez contacter l'administrateur.</font><br/>";
								echo "<center><a href='jouer.php'>[ retour ]</a></center>";
							}
						} else {
							// Tentative de triche ?!
							echo "<font color='red'>Le perso n'a pas pu être renvoyé, si le problème persiste, veuillez contacter l'administrateur.</font><br/>";
							echo "<center><a href='jouer.php'>[ retour ]</a></center>";
						}
					} else {
						// Tentative de triche ?!
						echo "<font color='red'>Le matricule du perso à renvoyer est mal renseigné, si le problème persiste, veuillez contacter l'administrateur.</font><br/>";
						echo "<center><a href='jouer.php'>[ retour ]</a></center>";
					}
				}
			
				echo "<table align='center' border='1' width='70%'>";
				echo "	<tr>";
				echo "		<th>Type de grouillot</th><th>Matricule</th><th>Nom</th><th>Action</th>";
				echo "	</tr>";
			
				// Affichage des grouillots
				echo "";
			
				// Récupération des persos du joueur
				$sql = "SELECT id_perso, nom_perso, type_perso, image_perso FROM perso WHERE idJoueur_perso = '$id_joueur' AND chef = '0'";
				$res = $mysqli->query($sql);
				while ($tab = $res->fetch_assoc()) {
					
					$matricule_grouillot 	= $tab["id_perso"];
					$nom_grouillot			= $tab["nom_perso"];
					$image_grouillot		= $tab["image_perso"];
					$type_grouillot			= $tab["type_perso"];
					
					$sql_u = "SELECT nom_unite FROM type_unite WHERE id_unite='$type_grouillot'";
					$res_u = $mysqli->query($sql_u);
					$t_u = $res_u->fetch_assoc();
					
					$nom_unite_grouillot = $t_u["nom_unite"];
					
					echo "<tr>";
					echo "	<td align='center'><img src='../images_perso/".$image_grouillot."' alt='".$nom_unite_grouillot."'/><br />" . $nom_unite_grouillot . "</td>";
					echo "	<td align='center'>" . $matricule_grouillot . "</td>";
					echo "<form method=\"post\" action=\"gestion_grouillot.php\">";
					echo "	<td align='center'><input type='text' maxlength='25' name='nom_grouillot' value='". $nom_grouillot ."'><input type='hidden' name='matricule_hidden' value='$matricule_grouillot'> <input type='submit' name='renommer' value='renommer'></td>";
					echo "</form>";
					echo "<form method=\"post\" action=\"gestion_grouillot.php\">";
					echo "	<td align='center'><input type='hidden' name='matricule_renvoi_hidden' value='$matricule_grouillot'><input type='submit' name='renvoyer' value='renvoyer'></td>";
					echo "</form>";
					echo "</tr>";
				}
			}
			else {
				echo "<font color=red>Seul le chef de bataillon peut accéder à cette page.</font>";
			}
		}
	}
	else{
		echo "<font color=red>Vous ne pouvez pas accéder à cette page, veuillez vous loguer.</font>";
	}
	?>
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