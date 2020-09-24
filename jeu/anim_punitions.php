<?php
session_start();
require_once("../fonctions.php");
require_once("f_carte.php");

$mysqli = db_connexion();

include ('../nb_online.php');
include ('../forum/config.php');

// recupération config jeu
$dispo = config_dispo_jeu($mysqli);
$admin = admin_perso($mysqli, $_SESSION["id_perso"]);

if($dispo || $admin){
	
	if (isset($_SESSION["id_perso"])) {
		
		//recuperation des variables de sessions
		$id = $_SESSION["id_perso"];
		
		if (anim_perso($mysqli, $id)) {
			
			$mess_err 	= "";
			$mess 		= "";
			
			// Récupération du camp de l'animateur 
			$sql = "SELECT clan FROM perso WHERE id_perso='$id'";
			$res = $mysqli->query($sql);
			$t = $res->fetch_assoc();
			
			$camp = $t['clan'];
			
			if ($camp == '1') {
				$nom_camp = 'Nord';
			}
			else if ($camp == '2') {
				$nom_camp = 'Sud';
			}
			else if ($camp == '3') {
				$nom_camp = 'Indien';
			}
			
			if (isset($_POST['liste_perso_punition'])) {
						
				$id_perso_puni = $_POST['liste_perso_punition'];
				
			}
			
			if (isset($_GET['id_perso']) && trim($_GET['id_perso']) != "") {
				
				$id_perso_punition = $_GET['id_perso'];
				
				$verif_id_perso = preg_match("#^[0-9]*[0-9]$#i","$id_perso_punition");
				
				if ($verif_id_perso) {
					
					// On verifie si le perso puni est du même camp que l'anim
					$sql = "SELECT clan FROM perso WHERE id_perso='$id_perso_punition'";
					$res = $mysqli->query($sql);
					$t = $res->fetch_assoc();
					
					$camp_perso_punition = $t['clan'];
					
					if ($camp_perso_punition == $camp) {
					
						if (isset($_GET['bagne']) && trim($_GET['bagne']) != "") {
							
							$mess .= "";
						}
					
						if (isset($_GET['amende']) && trim($_GET['amende']) != "") {
							
							$mess .= "";
						}
						
						if (isset($_GET['pc']) && trim($_GET['pc']) != "") {
							
							$mess .= "";
						}
						
						if (isset($_GET['xp']) && trim($_GET['xp']) != "") {
							
							$mess .= "";
						}
					}
					else {
						// parametres incorrectes / modifiés
						$text_triche = "Tentative modification parametre id perso animation punition - camp perso puni pas le même que anim";
						
						$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id', '$text_triche')";
						$mysqli->query($sql);
						
						header("Location:jouer.php");
					}
				}
				else {
					// parametres incorrectes / modifiés
					$text_triche = "Tentative modification parametre id perso animation punition";
					
					$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id', '$text_triche')";
					$mysqli->query($sql);
					
					header("Location:jouer.php");
				}
			}
?>
		
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<title>Nord VS Sud - Animation</title>
		
		<!-- Required meta tags -->
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		
		<!-- Bootstrap CSS -->
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
	</head>
	<body>
		<div class="container">
		
			<div class="row">
				<div class="col-12">

					<div align="center">
						<h2>Animation - Gestion des punitions des persos</h2>
						
						<font color='red'><?php echo $mess_err; ?></font>
						<font color='blue'><?php echo $mess; ?></font>
					</div>
				</div>
			</div>
			
			<p align="center"><a class="btn btn-primary" href="animation.php">Retour page principale d'animation</a></p>
			
			<div class="row">
				<div class="col-12">
					<form method='POST' action='anim_punitions.php'>
						<div class="form-row">
							<div class="form-group col-md-12">
								<label for="formSelectPerso">Punir le perso : </label>
							</div>
						</div>
						<div class="form-row">
							<div class="form-group col-md-8">
								<select class="form-control" name='liste_perso_punition' id="formSelectPerso" onchange="this.form.submit()">
								<?php
								// récuopération de tous les persos de son camp 
								$sql = "SELECT id_perso, nom_perso FROM perso WHERE clan='$camp' ORDER BY id_perso ASC";
								$res = $mysqli->query($sql);
								
								while ($t = $res->fetch_assoc()) {
									
									$id_perso_list 	= $t["id_perso"];
									$nom_perso_list	= $t["nom_perso"];
									
									echo "<option value='".$id_perso_list."' ";
									if (isset($id_perso_puni) && $id_perso_puni == $id_perso_list) {
										echo "selected";
									}
									echo ">".$nom_perso_list." [".$id_perso_list."]</option>";
									
								}
								?>
								</select>
							</div>
							<div class="form-group col-md-4">
								<button type="submit" class="btn btn-primary">Voir</button>
							</div>
						</div>
					</form>
				</div>
			</div>
			
			<br />
			
			<div class="row">
				<div class="col-12">
					<?php
					if (isset($id_perso_puni)) {
						
						$sql = "SELECT or_perso, pc_perso, xp_perso, chef FROM perso WHERE id_perso='$id_perso_puni'";
						$res = $mysqli->query($sql);
						$t = $res->fetch_assoc();
						
						$thune_perso 	= $t['or_perso'];
						$pc_perso 		= $t['pc_perso'];
						$xp_perso		= $t['xp_perso'];
						$chef_perso		= $t['chef'];
						
						echo "<center>";
						echo "	<a href='anim_punitions.php?id_perso=".$id_perso_puni."&bagne=ok' class='btn btn-danger'>Envoyer au bagne !</a>";
						echo "<br /><br />Ce perso possède <b>".$thune_perso."</b> thunes sur lui<br />";
						
						$perte_thune_all = false;
						
						if ($thune_perso >= 5) {
							echo "	<a href='anim_punitions.php?id_perso=".$id_perso_puni."&amende=5' class='btn btn-warning'>Infliger une amende de 5 thunes</a>";
						}
						else {
							$perte_thune_all = true;
							echo "	<a href='anim_punitions.php?id_perso=".$id_perso_puni."&amende=all' class='btn btn-warning'>Infliger une amende de toute sa thune</a>";
						}
						
						if ($thune_perso >= 10) {
							echo "	<a href='anim_punitions.php?id_perso=".$id_perso_puni."&amende=10' class='btn btn-warning'>Infliger une amende de 10 thunes</a>";
						}
						else if (!$perte_thune_all) {
							$perte_thune_all = true;
							echo "	<a href='anim_punitions.php?id_perso=".$id_perso_puni."&amende=all' class='btn btn-warning'>Infliger une amende de toute sa thune</a>";
						}
						
						if ($thune_perso >= 20) {
							echo "	<a href='anim_punitions.php?id_perso=".$id_perso_puni."&amende=20' class='btn btn-warning'>Infliger une amende de 20 thunes</a>";
						}
						else if (!$perte_thune_all) {
							$perte_thune_all = true;
							echo "	<a href='anim_punitions.php?id_perso=".$id_perso_puni."&amende=all' class='btn btn-warning'>Infliger une amende de toute sa thune</a>";
						}
						
						if ($thune_perso >= 50) {
							echo "	<a href='anim_punitions.php?id_perso=".$id_perso_puni."&amende=50' class='btn btn-warning'>Infliger une amende de 50 thunes</a>";
						}
						else if (!$perte_thune_all) {
							$perte_thune_all = true;
							echo "	<a href='anim_punitions.php?id_perso=".$id_perso_puni."&amende=all' class='btn btn-warning'>Infliger une amende de toute sa thune</a>";
						}
						
						if ($pc_perso > 0 && $chef_perso) {
							$perte_pc_all = false;
							
							echo "<br /><br />Ce perso possède <b>".$pc_perso."</b> Points de commandement<br />";
							
							if ($pc_perso >= 5) {
								echo "	<a href='anim_punitions.php?id_perso=".$id_perso_puni."&pc=5' class='btn btn-warning'>Infliger une perte de 5 PC</a>";
							}
							else {
								$perte_pc_all = true;
								echo "	<a href='anim_punitions.php?id_perso=".$id_perso_puni."&pc=all' class='btn btn-warning'>Infliger une perte de tout ses PC</a>";
							}
							
							if ($pc_perso >= 10) {
								echo "	<a href='anim_punitions.php?id_perso=".$id_perso_puni."&pc=10' class='btn btn-warning'>Infliger une perte de 10 PC</a>";
							}
							else if (!$perte_pc_all) {
								$perte_pc_all = true;
								echo "	<a href='anim_punitions.php?id_perso=".$id_perso_puni."&pc=all' class='btn btn-warning'>Infliger une perte de tout ses PC</a>";
							}
							
							if ($pc_perso >= 20) {
								echo "	<a href='anim_punitions.php?id_perso=".$id_perso_puni."&pc=20' class='btn btn-warning'>Infliger une perte de 20 PC</a>";
							}
							else if (!$perte_pc_all){
								$perte_pc_all = true;
								echo "	<a href='anim_punitions.php?id_perso=".$id_perso_puni."&pc=all' class='btn btn-warning'>Infliger une perte de tout ses PC</a>";
							}
							
							if ($pc_perso >= 50) {
								echo "	<a href='anim_punitions.php?id_perso=".$id_perso_puni."&pc=50' class='btn btn-warning'>Infliger une perte de 50 PC</a>";
							}
							else if (!$perte_pc_all){
								$perte_pc_all = true;
								echo "	<a href='anim_punitions.php?id_perso=".$id_perso_puni."&pc=all' class='btn btn-warning'>Infliger une perte de tout ses PC</a>";
							}
						}
						else if ($chef_perso) {
							echo "<br /><br /><b>Ce perso ne possède pas encore de Points de commandement</b><br />";
						}
						
						if ($xp_perso > 0) {
							$perte_xp_all = false;
							
							echo "<br /><br />Ce perso possède <b>".$xp_perso."</b> Points d'experience<br />";
							if ($xp_perso >= 5) {
								echo "	<a href='anim_punitions.php?id_perso=".$id_perso_puni."&xp=5' class='btn btn-warning'>Infliger une perte de 5 XP</a>";
							}
							else {
								$perte_xp_all = true;
								echo "	<a href='anim_punitions.php?id_perso=".$id_perso_puni."&xp=all' class='btn btn-warning'>Infliger une perte de tous ses XP</a>";
							}
							
							if ($xp_perso >= 5) {
								echo "	<a href='anim_punitions.php?id_perso=".$id_perso_puni."&xp=10' class='btn btn-warning'>Infliger une perte de 10 XP</a>";
							}
							else if (!$perte_xp_all){
								$perte_xp_all = true;
								echo "	<a href='anim_punitions.php?id_perso=".$id_perso_puni."&xp=all' class='btn btn-warning'>Infliger une perte de tous ses XP</a>";
							}
							
							if ($xp_perso >= 5) {
								echo "	<a href='anim_punitions.php?id_perso=".$id_perso_puni."&xp=20' class='btn btn-warning'>Infliger une perte de 20 XP</a>";
							}
							else if (!$perte_xp_all){
								$perte_xp_all = true;
								echo "	<a href='anim_punitions.php?id_perso=".$id_perso_puni."&xp=all' class='btn btn-warning'>Infliger une perte de tous ses XP</a>";
							}
							
							if ($xp_perso >= 5) {
								echo "	<a href='anim_punitions.php?id_perso=".$id_perso_puni."&xp=50' class='btn btn-warning'>Infliger une perte de 50 XP</a>";
							}
							else if (!$perte_xp_all){
								$perte_xp_all = true;
								echo "	<a href='anim_punitions.php?id_perso=".$id_perso_puni."&xp=all' class='btn btn-warning'>Infliger une perte de tous ses XP</a>";
							}
						}
						else {
							echo "<br /><br /><b>Ce perso ne possède pas encore de Points d'experience</b><br />";
						}
						echo "</center>";
						
					}
					?>
				</div>
			</div>
			
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
		else {
			// Un joueur essaye d'acceder à la page sans être animateur
			$text_triche = "Tentative accés page animation sans y avoir les droits";
			
			$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id', '$text_triche')";
			$mysqli->query($sql);
			
			header("Location:jouer.php");
		}
	}
	else{
		echo "<center><font color='red'>Vous ne pouvez pas accéder à cette page, veuillez vous loguer.</font></center>";
	}
}
else {
	// logout
	$_SESSION = array(); // On écrase le tableau de session
	session_destroy(); // On détruit la session
	
	header("Location:../index2.php");
}
?>		
	