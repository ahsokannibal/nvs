<?php
session_start();
require_once("../fonctions.php");

$mysqli = db_connexion();

include ('../nb_online.php');
include ('../forum/config.php');

if (@$_SESSION["id_perso"]) {
	
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
	
	$verif1 = preg_match("#^[0-9]+$#i",$id_compagnie);
	
	if($verif1){
	
		// verification que le perso est bien le chef de la compagnie (anti-triche)
		$sql = "SELECT poste_compagnie FROM perso_in_compagnie WHERE id_perso=$id AND id_compagnie=$id_compagnie";
		$res = $mysqli->query($sql);
		$ch = $res->fetch_assoc();
		
		$ok_chef = $ch["poste_compagnie"];
		
		// Chef ou sous-chef
		if($ok_chef == 1 || $ok_chef == 2) {
			
			$mess_err 	= "";
			$mess		= "";
		
			// Récupération infos de la compagnie
			$sql = "SELECT nom_compagnie, image_compagnie, genie_civil, id_parent FROM compagnies WHERE id_compagnie='$id_compagnie'";
			$res = $mysqli->query($sql);
			$t = $res->fetch_assoc();
			
			$nom_compagnie 		= $t['nom_compagnie'];
			$image_compagnie	= $t['image_compagnie'];
			$genie_compagnie	= $t['genie_civil'];
			$id_parent			= $t['id_parent'];
			
			// Vérification si il y a une demande de suppresion en attente
			$sql = "SELECT * FROM compagnie_demande_anim WHERE id_compagnie='$id_compagnie' AND type_demande='2'";
			$res = $mysqli->query($sql);
			$demande_suppression = $res->num_rows;
			
			// Demande de suppression de la compagnie
			if (isset($_POST['delete_compagnie_hidden'])) {
				
				if (!$demande_suppression && !$genie_compagnie) {
				
					$id_compagnie_to_delete = $_POST['delete_compagnie_hidden'];
					
					// Vérification qu'on demande bien à supprimer sa propre compagnie...
					if ($id_compagnie_to_delete == $id_compagnie) {
						
						// Verification que la compagnie ne possède pas de sections
						$sql = "SELECT count(*) as nb_sections FROM compagnies WHERE id_parent='$id_compagnie_to_delete'";
						$res = $mysqli->query($sql);
						$t = $res->fetch_assoc();
						
						$nb_sections = $t['nb_sections'];
						
						if ($nb_sections == 0) {
						
							$sql = "INSERT INTO compagnie_demande_anim (id_compagnie, type_demande, info_demande) VALUES ('$id_compagnie', '2', '')";
							$mysqli->query($sql);
							
							$demande_suppression = 1;
							
							$mess .= "Demande envoyée avec succée";
						}
						else {
							$mess_err .= "Vous ne pouvez pas demander la suppression de votre compagnie car elle possède une ou plusieurs sections";
						}
					}
					else {
						// Tentative de triche 
						$text_triche = "Tentative de demande de suppression de la compagnie ".$id_compagnie_to_delete." qui n'est pas la sienne (".$id_compagnie.") !";
				
						$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id', '$text_triche')";
						$mysqli->query($sql);
					}
				}
			}
		
			// Changement de l'image
			if(isset($_POST["image"])){
				
				if($_POST["image"] != "") {
					
					$image = addslashes($_POST["image"]);
					
					$sql = "UPDATE compagnies SET image_compagnie='$image' WHERE id_compagnie=$id_compagnie";
					$mysqli->query($sql);
					
					$mess .= "Changement de l'image effectué";
				}
				else {
					$mess_err .= "Veuillez bien remplir le champ pour le changement d'image";
				}
			}
			
			// Virer un perso
			if(isset($_POST["virer"])) {
				
				if($_POST["virer"] != "") {
					
					$perso_a_virer = $_POST["virer"];
					
					// verification que le membre appartienne bien a la compagnie
					$sql = "SELECT perso.id_perso FROM perso, perso_in_compagnie 
							WHERE perso.id_perso=perso_in_compagnie.id_perso 
							AND id_compagnie=$id_compagnie 
							AND nom_perso='$perso_a_virer' 
							AND poste_compagnie!=1";
					$res = $mysqli->query($sql);
					$t_v = $res->fetch_assoc();
					
					$id_perso_a_virer = $t_v["id_perso"];
					
					// le perso existe et appartient bien a la compagnie
					if ($id_perso_a_virer != 0) {
						
						// recuperation des information sur la compagnie
						$sql = "SELECT genie_civil, nom_compagnie FROM compagnies WHERE id_compagnie=$id_compagnie";
						$res = $mysqli->query($sql);
						$sec = $res->fetch_assoc();
						
						$genie_compagnie 	= $sec["genie_civil"];
						$nom_compagnie		= addslashes($sec["nom_compagnie"]);
					
						// on vire le perso de la compagnie
						$sql = "DELETE FROM perso_in_compagnie WHERE id_perso=$id_perso_a_virer AND id_compagnie=$id_compagnie";
						$mysqli->query($sql);
						
						// on enleve le perso de la banque
						$sql = "DELETE FROM banque_compagnie WHERE id_perso=$id_perso_a_virer";
						$mysqli->query($sql);
						
						if ($genie_compagnie) {
							// On suprime les competences de construction
							
							// Construire pont
							$sql = "DELETE FROM perso_as_competence WHERE id_perso='$id_perso_a_virer' AND id_competence='23'";
							$mysqli->query($sql);
							
							// Construire tour de visu
							$sql = "DELETE FROM perso_as_competence WHERE id_perso='$id_perso_a_virer' AND id_competence='24'";
							$mysqli->query($sql);
							
							// Construire Hopital
							$sql = "DELETE FROM perso_as_competence WHERE id_perso='$id_perso_a_virer' AND id_competence='27'";
							$mysqli->query($sql);
							
							// Construire Fortin
							$sql = "DELETE FROM perso_as_competence WHERE id_perso='$id_perso_a_virer' AND id_competence='28'";
							$mysqli->query($sql);
							
							// Construire Gare
							$sql = "DELETE FROM perso_as_competence WHERE id_perso='$id_perso_a_virer' AND id_competence='63'";
							$mysqli->query($sql);
							
							// Construire Rails
							$sql = "DELETE FROM perso_as_competence WHERE id_perso='$id_perso_a_virer' AND id_competence='64'";
							$mysqli->query($sql);
						}
						
						$mess .= "Vous venez de virer $perso_a_virer [".$id_perso_a_virer."] de votre compagnie";
					}
					else {
						$mess_err .= "Ce perso n'existe pas ou ne fait pas parti de votre compagnie ou est un chef de la compagnie";
					}
				}
				else {
					$mess_err .= "Veuillez bien remplir le champ pour virer un membre";
				}
			}
		
			echo "<h3>";
			if (isset($id_parent)) {
				$titre_compagnie = "section";
			}
			else {
				$titre_compagnie = "compagnie";
			}
			
			echo "	<center>Page d'administration de la ".$titre_compagnie." ".$nom_compagnie." ";
			if (!$genie_compagnie && !isset($id_parent) && $ok_chef == 1) {
				echo "	<a class='btn btn-primary' title=\"Demander à l'animation à changer de nom de compagnie\" href='nom_compagnie_change.php?id_compagnie=$id_compagnie'>Changer le nom</a>";
			}
			if (!$demande_suppression && !$genie_compagnie && !isset($id_parent) && $ok_chef == 1) {
				echo "	<button type='button' class='btn btn-danger' data-toggle='modal' data-target=\"#modalConfirm\">Supprimer la compagnie</button>";
			}
			echo "	</center>";
			echo "</h3>";
			echo "<center>";
			if ($ok_chef == 1) {
				echo "	<a class='btn btn-danger' href='chef_compagnie.php?id_compagnie=$id_compagnie'>changer de chef</a>";
			}
			echo " 	<a class='btn btn-info' href='resume_compagnie.php?id_compagnie=$id_compagnie'>changer le resume de la ".$titre_compagnie."</a>";
			echo " 	<a class='btn btn-info' href='description_compagnie.php?id_compagnie=$id_compagnie'>changer la description de la ".$titre_compagnie."</a>";
			echo " 	<a class='btn btn-warning' href='grade_compagnie.php?id_compagnie=$id_compagnie'>donner des postes aux membres de sa ".$titre_compagnie."</a>";
			if (!isset($id_parent) && !$genie_compagnie) {
				echo " 	<a class='btn btn-warning' href='section_compagnie.php?id_compagnie=$id_compagnie'>Gérer les sections</a>";
			}
			echo "</center>";
			
			if ($demande_suppression) {
				echo "<center><font color='red'><b>Cette compagnie est en attente de suppression par l'animation</b></font></center>";
			}
			
			echo "<hr>";
			
			if (trim($mess) != "") {
				echo "<center><font color='blue'><b>".$mess."</b></font></center><br />";
			}
			if (trim($mess_err) != "") {
				echo "<center><font color='red'><b>".$mess_err."</b></font></center><br />";
			}
			
			// Affichage de l'image de la compagnie
			if (trim($image_compagnie) != "" && $image_compagnie != "0") {
				echo "<center><img src='".htmlspecialchars($image_compagnie)."' width='40' height='40' alt='image de la compagnie' title='image de ma compagnie'></center>";
			}
			else {
				echo "<center><font color='red'>Aucune image définie pour la compagnie</font></center>";
			}
			
			echo "<form action=\"admin_compagnie.php?id_compagnie=$id_compagnie\" method=\"post\" name=\"image\">";
			echo "<div align=\"center\"><br>";
			echo "changer l'image de la compagnie (url vers l'image) :<br>";
			echo "<input name=\"image\" type=\"text\" value=\"\" onFocus=\"this.value=''\" style=\"width: 400px;\" maxlength=\"200\">";
			echo "<input type=\"submit\" name=\"Submit\" value=\"ok\">";
			echo "</div>";
			echo "</form>";
			
			echo "<br /><br />";
			
			echo "<form action=\"admin_compagnie.php?id_compagnie=$id_compagnie\" method=\"post\" name=\"virer\">";
			echo "<div align=\"center\">";
			echo "Virer un membre de sa compagnie (taper le pseudo) : ";
			echo "<input name=\"virer\" type=\"text\" value=\"\" onFocus=\"this.value=''\" maxlength=\"100\">";
			echo "<input type=\"submit\" name=\"Submit\" value=\"virer!\">";
			echo "</div>";
			echo "</form>";
			
			echo "<br /><br />";
			
			echo "<form action=\"nouveau_message.php?id_compagnie=$id_compagnie\" method=\"post\" name=\"mail\">";
			echo "<div align=\"center\"><br>";
			echo "<center>envoyer un MP a tout les membres de sa compagnie :</center>";
			echo "<TEXTAREA cols=\"50\" rows=\"5\" name=\"contenu\">";
			echo "</TEXTAREA><br><input type=\"submit\" name=\"envoi\" value=\"envoyer\">";
			echo "</div>";
			echo "</form>";
			
			echo "<br /><center><a class='btn btn-primary' href='compagnie.php'>retour a la page ".$titre_compagnie."</a></center>";
			?>
			<!-- Modal -->
			<form method="post" action="admin_compagnie.php?id_compagnie=<?php echo $id_compagnie; ?>">
				<div class="modal fade" id="modalConfirm" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
					<div class="modal-dialog modal-dialog-centered" role="document">
						<div class="modal-content">
							<div class="modal-header">
								<h5 class="modal-title" id="exampleModalCenterTitle">Demande de suppression de compagnie</h5>
								<button type="button" class="close" data-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body">
								Êtes-vous sûr de vouloir demander la suppression de votre compagnie ?
								<input type='hidden' name='delete_compagnie_hidden' value='<?php echo $id_compagnie; ?>'>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
								<button type="button" onclick="this.form.submit()" class="btn btn-danger">Supprimer</button>
							</div>
						</div>
					</div>
				</div>
			</form>
			<?php
				
		}
		else {
			echo "<font color = red>Vous n'avez pas le droit d'acceder à cette page !</font>";
			
			$text_triche = "Tentative accés page admin compagnie [$id_compagnie] sans y avoir les droits";
			
			$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id', '$text_triche')";
			$mysqli->query($sql);
		}
	}
	else {
		echo "<center>La compagnie demandé n'existe pas</center>";
		
		$param_test 	= addslashes($id_compagnie);
		$text_triche 	= "Test parametre sur page admin compagnie, parametre id_compagnie invalide tenté : $param_test";
			
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
	echo "<font color=red>Vous ne pouvez pas acceder a cette page, veuillez vous logguer.</font>";
}?>
