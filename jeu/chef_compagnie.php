<?php
session_start();
require_once("../fonctions.php");

$mysqli = db_connexion();

include ('../nb_online.php');
$phpbb_root_path = '../forum/';
if (is_dir($phpbb_root_path))
{
	include ($phpbb_root_path .'config.php');
}

// recupération config jeu
$dispo = config_dispo_jeu($mysqli);
$admin = admin_perso($mysqli, $_SESSION["id_perso"]);

if($dispo == '1' || $admin){

	if (isset($_SESSION["id_perso"])) {
		
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
		<div class="container-fluid">
		<?php
		if(isset($_GET["id_compagnie"])) {
			
			$id_compagnie = $_GET["id_compagnie"];
			
			$verif = preg_match("#^[0-9]+$#i",$_GET["id_compagnie"]);
			
			if($verif){
				
				// verification que le perso est bien le chef de la compagnie (ou que la compagnie existe toujours)
				$sql = "SELECT poste_compagnie FROM perso_in_compagnie WHERE id_perso='$id' AND id_compagnie='$id_compagnie'";
				$res = $mysqli->query($sql);
				$ch = $res->fetch_assoc();
				
				$ok_chef = $ch["poste_compagnie"];
				
				if($ok_chef == 1) {
				
					echo "<center><h4>Changement de chef</h4></center>";
					
					echo "<center><a href='admin_compagnie.php?id_compagnie=".$id_compagnie."' class='btn btn-info'>retour a la page d'administration de compagnie</a></center>";
					
					// on a choisi un nouveau chef
					if(isset($_POST["chef"])) {
					
						$ok = 0;
						
						if($_POST["chef"] != "") {
							
							$nouveau_chef = $_POST["chef"];
							
							// recuperation des noms des persos dans la compagnie
							$sql = "SELECT nom_perso, perso_in_compagnie.id_perso FROM perso, perso_in_compagnie WHERE perso_in_compagnie.id_perso=perso.id_perso AND id_compagnie=$id_compagnie AND perso_in_compagnie.id_perso!=$id";
							$res = $mysqli->query($sql);
							
							while ($noms = $res->fetch_assoc()) {
								$nom_p = $noms["nom_perso"];
								$id_p = $noms["id_perso"];
								if($nouveau_chef == $nom_p) {
									$ok = 1;
									break;
								}
							}
							
							if($ok) {
								// maj du chef
								$sql = "UPDATE perso_in_compagnie SET poste_compagnie=1 WHERE id_perso=$id_p";
								$mysqli->query($sql);
								
								// L'ancien chef redevient simple membre
								$sql = "UPDATE perso_in_compagnie SET poste_compagnie=5 WHERE id_perso=$id";
								$mysqli->query($sql);
								
								// recuperation des information sur la compagnie
								$sql = "SELECT nom_compagnie FROM compagnies WHERE id_compagnie=$id_compagnie";
								$res = $mysqli->query($sql);
								$sec = $res->fetch_assoc();
								
								$nom_compagnie		= addslashes($sec["nom_compagnie"]);
								
								// FORUM
								// Récupération de l'id du group de la compagnie sur le forum
								$sql = "SELECT group_id FROM ".$table_prefix."groups WHERE group_name='$nom_compagnie'";
								$res = $mysqli->query($sql);
								$t = $res->fetch_assoc();
								
								$id_group_forum = $t['group_id'];
								
								// Récupération de l'id de l'ancien chef sur le forum 
								$sql = "SELECT user_id FROM ".$table_prefix."users WHERE username IN 
											(SELECT nom_perso FROM perso WHERE idJoueur_perso IN 
												(SELECT idJoueur_perso FROM perso WHERE id_perso='$id') AND chef='1')";
								$res = $mysqli->query($sql);
								$t = $res->fetch_assoc();
								
								$id_forum_ancien_chef = $t['user_id'];
								
								// Il n'est plus chef du groupe sur le forum
								$sql = "UPDATE ".$table_prefix."user_group SET group_leader=0 WHERE group_id='$id_group_forum' AND user_id='$id_forum_ancien_chef'";
								$mysqli->query($sql);
								
								// Récupération de l'id du nouveau chef sur le forum 
								$sql = "SELECT user_id FROM ".$table_prefix."users WHERE username IN 
											(SELECT nom_perso FROM perso WHERE idJoueur_perso IN 
												(SELECT idJoueur_perso FROM perso WHERE id_perso='$id_p') AND chef='1')";
								$res = $mysqli->query($sql);
								$t = $res->fetch_assoc();
								
								$id_forum_nouveau_chef = $t['user_id'];
								
								// Il devient chef du groupe sur le forum
								$sql = "UPDATE ".$table_prefix."user_group SET group_leader=1 WHERE group_id='$id_group_forum' AND user_id='$id_forum_ancien_chef'";
								$mysqli->query($sql);
								
								echo "<br><center><font color='blue'>$nom_p devient le nouveau chef de votre compagnie</font></center><br>";
							}
							else {
								echo "<center><font color='red'>Ce perso n'existe pas ou n'appartient pas a votre compagnie.</font></center>";
							}
						}
						else {
							echo "<center><font color='red'>Veuillez remplir le champ pour designer un nouveau chef</font></center>";
						}
					}
					else {
							
						echo "<form action=\"chef_compagnie.php?id_compagnie=$id_compagnie\" method='post' name='chef'>";
						echo "<div align='center'><br>";
						echo "Nom du chef : ";
						echo "<input name='chef' type='text' value='' onFocus=\"this.value=''\" maxlength='50'>";
						echo "<input type='submit' name='Submit' value='changer' class='btn btn-primary'>";
						echo "</div>";
						echo "</form>";
						
					}
					
					echo "<br /><center><a href='compagnie.php' class='btn btn-info'>retour a la page de compagnie</a></center>";
				}
				else {
					echo "<center><font color='red'>Vous n'avez pas le droit d'acceder à cette page !</font></center>";
					
					$text_triche = "Tentative accés page chef compagnie [$id_compagnie] sans y avoir les droits";
			
					$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id', '$text_triche')";
					$mysqli->query($sql);
				}
			}
			else {
				echo "<center><font color='red'>La compagnie demandé n'existe pas</font></center>";
				
				$param_test 	= addslashes($id_compagnie);
				$text_triche 	= "Test parametre sur page chef compagnie, parametre id_compagnie invalide tenté : $param_test";
					
				$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id', '$text_triche')";
				$mysqli->query($sql);
			}
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
	}
	else{
		echo "<font color=red>Vous ne pouvez pas accéder à cette page, veuillez vous loguer.</font>";
	}?>
<?php
}
else {
	// logout
	$_SESSION = array(); // On écrase le tableau de session
	session_destroy(); // On détruit la session
	
	header("Location:../index2.php");
}
?>
