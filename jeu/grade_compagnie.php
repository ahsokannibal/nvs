<?php
session_start();
require_once("../fonctions.php");

$mysqli = db_connexion();

include ('../nb_online.php');

// recupération config jeu
$dispo = config_dispo_jeu($mysqli);
$admin = admin_perso($mysqli, $_SESSION["id_perso"]);

if($dispo || $admin){
	
	if(isset($_SESSION["id_perso"])){
		
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
			
			$verif = preg_match("#^[0-9]+$#i",$_GET["id_compagnie"]);
			
			if($verif){
		
				$id_compagnie = $_GET["id_compagnie"];
				
				// verification que le perso est bien le chef de la compagnie (anti-triche)
				$sql = "SELECT poste_compagnie FROM perso_in_compagnie WHERE id_perso=$id AND id_compagnie=$id_compagnie";
				$res = $mysqli->query($sql);
				$ch = $res->fetch_assoc();
				
				$ok_chef = $ch["poste_compagnie"];
				
				if($ok_chef == 1) {
					
					echo "<h3><center>Donner des postes aux membres de sa compagnie</center></h3>";
					
					echo "<center><a href='admin_compagnie.php?id_compagnie=".$id_compagnie."' class='btn btn-info'>retour a la page d'administration de compagnie</a></center>";
					
					if (isset($_POST["poste"])) {
						
						$m = $_POST["membre"];
						$p = $_POST["poste"];
						$t_membre = explode(",",$m);
						$t_poste = explode(",",$p);
						$id_membre = $t_membre[0];
						$nom_membre = $t_membre[1];
						$id_poste = $t_poste[0];
						$nom_poste = $t_poste[1];
						
						//on regarde si il n'existe pas deja un membre qui a ce poste
						$sql = "SELECT id_perso FROM perso_in_compagnie, poste WHERE poste_compagnie=id_poste AND id_compagnie=$id_compagnie AND id_poste='$id_poste' AND id_poste!='10'"; 
						$res = $mysqli->query($sql);
						$ch = $res->fetch_assoc();
						
						$nb_poste = $ch["id_perso"];
						
						if ($nb_poste){
							echo "<span class=\"erreur\">Un membre à déjà été promu à ce poste !</span>";
						}
						else {
							// on verifie que le perso qu'on veut grader n'est pas le chef ^^
							$sql = "SELECT id_perso FROM perso_in_compagnie WHERE poste_compagnie=1";
							$res = $mysqli->query($sql);
							$t_chef = $res->fetch_assoc();
							
							$id_chef = $t_chef["id_perso"];
							
							if($id_chef == $id_membre) {
								echo "<span class=\"erreur\">Le chef ne peut pas être promu a un autre poste !</span>";
							}
							else {
								
								// On vérifie que le perso choisi fais bien partie de la compagnie
								$sql = "SELECT id_perso FROM perso_in_compagnie WHERE id_perso='$id_membre' AND id_compagnie='$id_compagnie'";
								$res = $mysqli->query($sql);
								$v = $res->num_rows;
								
								if($v){
									
									$sql = "UPDATE perso_in_compagnie SET poste_compagnie=$id_poste WHERE id_perso=$id_membre AND id_compagnie=$id_compagnie";
									$mysqli->query($sql);
									
									echo "<center><font color='blue'>Vous venez de promouvoir $nom_membre au poste de $nom_poste</font></center><br>";
								}
								else {
									echo "<center><font color='red'>Le membre selectionné n'appartient pas à cette compagnie</font></center>";
								}
							}
						}
					}	
					
					echo "<form action=\"grade_compagnie.php?id_compagnie=$id_compagnie\" method=\"post\" name=\"poste\">";
					echo "<div align=\"center\"><br>";
					echo "<table><tr><td>";
					echo "<select name=\"membre\">";
					
					// recuperation de la liste des membres de la compagnie
					$sql = "SELECT perso_in_compagnie.id_perso, nom_perso, poste_compagnie FROM perso, perso_in_compagnie 
							WHERE perso_in_compagnie.id_perso=perso.ID_perso 
							AND id_compagnie=$id_compagnie 
							AND (attenteValidation_compagnie='0' OR attenteValidation_compagnie='2')
							AND poste_compagnie!='1' 
							ORDER BY poste_compagnie";
					$res = $mysqli->query($sql);
					
					while ($membre = $res->fetch_assoc()) {
					
						$nom_membre 		= $membre["nom_perso"];
						$poste_compagnie 	= $membre["poste_compagnie"];
			
						if($poste_compagnie != 10){
							
							// recuperation du nom de poste
							$sql2 = "SELECT nom_poste FROM poste WHERE id_poste=$poste_compagnie";
							$res2 = $mysqli->query($sql2);
							$t_p = $res2->fetch_assoc();
							
							$nom_poste = $t_p["nom_poste"];
							
							echo "<OPTION value=".$membre["id_perso"].",".$membre["nom_perso"].">".$membre["nom_perso"];
							echo "  (".$nom_poste.")";
			
						}
						else
							echo "<OPTION value=".$membre["id_perso"].",".$membre["nom_perso"].">".$membre["nom_perso"];
						echo "</option>";
					}
			
					echo "</select>";
					echo "&nbsp;&nbsp;&nbsp;</td><td>";
					echo "<select name=\"poste\">";
					
					//on recupére la liste des postes disponibles (sauf simple membre et chef)
					$sql = "SELECT nom_poste, id_poste FROM poste WHERE id_poste!=1 AND id_poste!=0"; 
					$res = $mysqli->query($sql);
					
					while ($poste = $res->fetch_assoc()) {
						echo "<OPTION value=".$poste["id_poste"].",".$poste["nom_poste"].">".$poste["nom_poste"]."</option>"; 
					}
					
					echo "</select>";
					echo "&nbsp;&nbsp;&nbsp;</td><td>";
					echo "<input type=\"submit\" name=\"Submit\" class='btn btn-warning' value=\"grader\">";
					echo "</td></tr></table>";
					echo "</form><br><br>";
					
					echo "<b><u>Liste des membres actuel :</u></b>";
					echo "<table class='table' border=1>";
					echo "	<tr>";
					echo "		<th style='text-align:center'>Membre</th><th style='text-align:center'>Poste</th>";
					echo "	</tr>";
			
					// recuperation de la liste des membres de la compagnie
					$sql = "SELECT perso.id_perso, nom_perso, poste_compagnie FROM perso, perso_in_compagnie 
							WHERE perso_in_compagnie.id_perso=perso.id_perso AND id_compagnie=$id_compagnie AND (attenteValidation_compagnie='0' OR attenteValidation_compagnie='2') 
							ORDER BY poste_compagnie";
					$res = $mysqli->query($sql);
					
					while ($membre = $res->fetch_assoc()) {
						
						$id_membre			= $membre["id_perso"];
						$nom_membre 		= $membre["nom_perso"];
						$poste_compagnie 	= $membre["poste_compagnie"];
						
						echo "	<tr>";
						echo "		<td>".$nom_membre." [".$id_membre."]</td>";
						echo "		<td align='center'>";
							
						// recuperation du nom de poste
						$sql2 = "SELECT nom_poste FROM poste WHERE id_poste=$poste_compagnie";
						$res2 = $mysqli->query($sql2);
						$t_p = $res2->fetch_assoc();
						
						$nom_poste = $t_p["nom_poste"];
					
						echo "$nom_poste";
						
						echo "		</td>";
						echo "	</tr>";
					}
			
					echo "</table>";
					echo "</div>";
					echo "<br /><center><a href='compagnie.php' class='btn btn-primary'>retour a la page compagnie</a></center>";
				}
				else {
					echo "<center><font color='red'>Vous n'avez pas le droit d'acceder à cette page !</font></center>";
					
					$text_triche = "Tentative accés page grade compagnie [$id_compagnie] sans y avoir les droits !";
				
					$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id', '$text_triche')";
					$mysqli->query($sql);
				}
			}
			else {
				echo "<center><font color='red'>Le groupe demandé n'existe pas</font></center>";
				
				$param_test 	= addslashes($id_compagnie);
				$text_triche 	= "Test parametre sur page grade compagnie, parametre id_compagnie invalide tenté : $param_test";
					
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
		echo "<center><font color='red'>Vous ne pouvez pas accéder à cette page, veuillez vous loguer.</font></center>";
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