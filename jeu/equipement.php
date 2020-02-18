<?php
session_start();
require_once("../fonctions.php");
require_once("f_carte.php");

$mysqli = db_connexion();

include ('../nb_online.php');

// recupération config jeu
$dispo = config_dispo_jeu($mysqli);
$admin = admin_perso($mysqli, $_SESSION["id_perso"]);

if($dispo || $admin){
	
	if(isset($_SESSION["id_perso"])){
		
		$id_perso = $_SESSION['id_perso'];
		$date = time();
	
		$sql = "SELECT pv_perso, or_perso, UNIX_TIMESTAMP(DLA_perso) as DLA, est_gele, chef FROM perso WHERE id_perso='$id_perso'";
		$res = $mysqli->query($sql);
		$tpe = $res->fetch_assoc();
		
		$testpv 	= $tpe['pv_perso'];
		$or 		= $tpe["or_perso"];
		$dla 		= $tpe["DLA"];
		$est_gele 	= $tpe["est_gele"];
		$chef 		= $tpe["chef"];
		
		$config = '1';
		
		// verification si le perso est encore en vie
		if ($testpv <= 0) {
			header("Location:../tour.php");
		}
		else {
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
	
	<body>
		<div class="container-fluid">
			<nav class="navbar navbar-expand-lg navbar-light">
				<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
					<span class="navbar-toggler-icon"></span>
				</button>
				<div class="collapse navbar-collapse" id="navbarSupportedContent">
					<ul class="navbar-nav mr-auto nav-pills">
						<li class="nav-item">
							<a class="nav-link" href="profil.php">Profil</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" href="ameliorer.php">Améliorer son perso</a>
						</li>
						<?php
						if($chef) {
							echo "<li class='nav-item'><a class='nav-link' href=\"recrutement.php\">Recruter des grouillots</a></li>";
							echo "<li class='nav-item'><a class='nav-link' href=\"gestion_grouillot.php\">Gérer ses grouillots</a></li>";
						}
						?>
						<li class="nav-item">
							<a class="nav-link active" href="#">Equiper son perso</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" href="compte.php">Gérer son Compte</a>
						</li>
					</ul>
				</div>
			</nav>
			
			<hr>
		
			<br /><br /><center><h1>Equipement</h1></center><br />
			
			<div align=center><input type="button" value="Fermer la page équipement" onclick="window.close()"></div>
			
			<br />

			<?php
			$mess = "";
			?>
				
			<table border=0 width=550 height=400 align='center' cellpadding=0 cellspacing=0>
				<tr>
					<td width=350 align='center'><b>Votre Equipement porté</b></td>
					<td width=200 align='center'><b>Votre Equipement en sac</b></td>
				</tr>
				<?php
					// recuperation des donnees des armes equipees
					$sql_e_a = "SELECT perso_as_arme.id_arme, nom_arme, description_arme, porteeMin_arme, porteeMax_arme, valeur_des_arme, degatMin_arme, degatMax_arme, pvMax_arme, image_arme 
								FROM perso_as_arme, arme 
								WHERE perso_as_arme.id_arme = arme.id_arme AND est_portee='1' AND id_perso='$id_perso'";
					$res_e_a = $mysqli->query($sql_e_a);
					
					$i = 0;
					
					while($t_e_a = $res_e_a->fetch_assoc()){
						
						$nom_arme[$i] 					= $t_e_a["nom_arme"];
						$porteeMin_arme[$i] 			= $t_e_a["porteeMin_arme"];
						$porteeMax_arme[$i] 			= $t_e_a["porteeMax_arme"];
						$degatMin_arme[$i] 				= $t_e_a["degatMin_arme"];
						$degatMax_arme[$i] 				= $t_e_a["degatMax_arme"];
						$valeur_des_arme[$i] 			= $t_e_a["valeur_des_arme"];
						$pvMax_arme[$i] 				= $t_e_a["pvMax_arme"];
						$image_arme[$i] 				= $t_e_a["image_arme"];
						$description_arme[$i] 			= $t_e_a["description_arme"];
						
						// Calcul des degats de l'arme
						if($degatMin_arme[$i] && $valeur_des_arme[$i]){
							$degats_armes[$i] = $degatMin_arme[$i]."D".$valeur_des_arme[$i];
						}
						else {
							// ???
							
						}
						
						// Affectation des variables selon la main de l'arme (gauche ou droite => 0 ou 1)
						$image_armes[$i] 		= $image_arme[$i];
						$nom_armes[$i] 			= $nom_arme[$i];
						$description_armes[$i] 	= $description_arme[$i];
						
						// Portee de l'arme
						if ($porteeMin_arme[$i] == $porteeMax_arme[$i]) {
							$portee_armes[$i] = $porteeMin_arme[$i];
						}
						else {
							$portee_armes[$i] = $porteeMin_arme[$i]." - ".$porteeMax_arme[$i];
						}
						
						$i++;
					}
												
				?>
				<tr>
					<td>
						<table border='1' width='350' height='400' align='center' cellpadding='0' cellspacing='0' background='../images/croquit2.jpg'>
							
							<tr width=350 height=50>
								<td width=50 height=50 align='center'>&nbsp;</td>
								<td width=50 height=50 align='center'>&nbsp;</td>
								<td width=50 height=50 align='center'>&nbsp;</td>
								<td width=50 height=50 align='center'>&nbsp;</td> 								
								<td width=50 height=50 align='center'>&nbsp;</td> 
								<td width=50 height=50 align='center'>&nbsp;</td>
								<td width=50 height=50 align='center'>&nbsp;</td>  
							</tr>
							
							<tr width=350 height=50>
								<td width=50 height=50 align='center'>&nbsp;</td>
								<td width=50 height=50 align='center'>&nbsp;</td>
								<td width=50 height=50 align='center'>&nbsp;</td>
								
								<!-- CASQUE -->
								<td width=50 height=50 align='center'>&nbsp;</td>
								
								<!-- COLLIER -->
								<td width=50 height=50 align='center'>&nbsp;</td>
								
								<td width=50 height=50 align='center'>&nbsp;</td>
								<td width=50 height=50 align='center'>&nbsp;</td>  
							</tr>
							
							<tr width=350 height=50>
								<!-- GANTS -->
								<td width=50 height=50 align='center'>&nbsp;</td>
							
								<!-- MAIN DROITE -->
								<?php if(isset($image_armes[1]) && $image_armes[1] != "vide"){
									affiche_image_arme($image_armes[1], $nom_armes[1], $description_armes[1], $portee_armes[1], $degats_armes[1]);
								} else {?>
									<td width=50 height=50 align='center'>&nbsp;</td>
								<?php } ?>
								
								<td width=50 height=50 align='center'>&nbsp;</td>
								
								<!-- CORPS -->
								<td width=50 height=50 align='center'>&nbsp;</td>
								
								<td width=50 height=50 align='center'>&nbsp;</td>
								
								<!-- MAIN GAUCHE -->
								<?php if(isset($image_armes[0]) && $image_armes[0] != "vide"){
									affiche_image_arme($image_armes[0], $nom_armes[0], $description_armes[0], $portee_armes[0], $degats_armes[0]);
								} else {?>
									<td width=50 height=50 align='center'>&nbsp;</td>
								<?php } ?>
							
								<!-- BAGUE -->
								<td width=50 height=50 align='center'>&nbsp;</td>
							</tr>
							
							<tr width=350 height=50>
								<td width=50 height=50 align='center'>&nbsp;</td>
								<td width=50 height=50 align='center'>&nbsp;</td>
								<td width=50 height=50 align='center'>&nbsp;</td>
								<td width=50 height=50 align='center'>&nbsp;</td>
								<td width=50 height=50 align='center'>&nbsp;</td>
								<td width=50 height=50 align='center'>&nbsp;</td>
								<td width=50 height=50 align='center'>&nbsp;</td>
							</tr>
							
							<tr width=350 height=50>
								<td width=50 height=50 align='center'>&nbsp;</td>
								<td width=50 height=50 align='center'>&nbsp;</td>
								<td width=50 height=50 align='center'>&nbsp;</td>
								
								<!-- PANTALON -->
								<td width=50 height=50 align='center'>&nbsp;</td>
								
								<td width=50 height=50 align='center'>&nbsp;</td>
								<td width=50 height=50 align='center'>&nbsp;</td>
								<td width=50 height=50 align='center'>&nbsp;</td>  
							</tr>

							<tr width=350 height=50>
								<td width=50 height=50 align='center'>&nbsp;</td> 
								<td width=50 height=50 align='center'>&nbsp;</td>
								<td width=50 height=50 align='center'>&nbsp;</td>
								<td width=50 height=50 align='center'>&nbsp;</td>
								<td width=50 height=50 align='center'>&nbsp;</td> 
								<td width=50 height=50 align='center'>&nbsp;</td>
								<td width=50 height=50 align='center'>&nbsp;</td>  
							</tr>
							
							<tr width=350 height=50>
								<td width=50 height=50 align='center'>&nbsp;</td>
								<td width=50 height=50 align='center'>&nbsp;</td>
								<td width=50 height=50 align='center'>&nbsp;</td>
								<td width=50 height=50 align='center'>&nbsp;</td>
								<td width=50 height=50 align='center'>&nbsp;</td>
								<td width=50 height=50 align='center'>&nbsp;</td>
								<td width=50 height=50 align='center'>&nbsp;</td>  
							</tr>

							<tr width=350 height=50>
								<td width=50 height=50 align='center'>&nbsp;</td>
								<td width=50 height=50 align='center'>&nbsp;</td>														
								<td width=50 height=50 align='center'>&nbsp;</td>
								
								<!-- BOTTES -->
								<td width=50 height=50 align='center'>&nbsp;</td>
								
								<td width=50 height=50 align='center'>&nbsp;</td>
								<td width=50 height=50 align='center'>&nbsp;</td>
								<td width=50 height=50 align='center'>&nbsp;</td>  
							</tr>
						</table>
					</td>
					<td>
						<table border='0' cellspacing=50>
							<tr>
								<td>
									<table border='0' width=160 height=200 align='center' cellpadding=0 cellspacing=0>
										<tr>
											<td>
												<table border='0' width=160 height=100 align='center' cellpadding=0 cellspacing=0>
													<tr>
														<td colspan=3 align=center><a class='btn btn-primary' href="equipement_armes.php">Equiper / Desequiper une arme</a></td>
													</tr>
													<tr>
														<td>&nbsp;</td>
													</tr>
													<tr>
														<td colspan=3 align=center><b>Vos Armes</b></td>
													</tr>
									<?php
									$compteur = 0;
									
									// recuperation des armes que le perso peut équiper
									$sql_a = "SELECT perso_as_arme.id_arme, nom_arme, description_arme, porteeMin_arme, porteeMax_arme, degatMin_arme, degatMax_arme, valeur_des_arme, degatZone_arme, image_arme 
												FROM perso_as_arme, arme, arme_as_type_unite, perso 
												WHERE perso_as_arme.id_arme = arme.id_arme 
												AND perso_as_arme.id_perso = perso.id_perso 
												AND perso.type_perso = arme_as_type_unite.id_type_unite
												AND arme_as_type_unite.id_arme = arme.id_arme
												AND est_portee='0' AND perso_as_arme.id_perso='$id_perso' 
												ORDER BY perso_as_arme.id_arme";
									$res_a = $mysqli->query($sql_a);
									
									while($t_a = $res_a->fetch_assoc()){
										
										$id_arme 					= $t_a["id_arme"];
										$nom_arme 					= $t_a["nom_arme"];
										$porteeMin_arme 			= $t_a["porteeMin_arme"];
										$porteeMax_arme 			= $t_a["porteeMax_arme"];
										$degatMin_arme 				= $t_a["degatMin_arme"];
										$degatMax_arme 				= $t_a["degatMax_arme"];
										$valeurDes_arme 			= $t_a["valeur_des_arme"];
										$degatZone_arme 			= $t_a["degatZone_arme"];
										$description_arme 			= $t_a["description_arme"];
										$image_arme 				= $t_a["image_arme"];
										
										// Portee de l'arme
										if ($porteeMin_arme == $porteeMax_arme) {
											$portee_arme = $porteeMin_arme;
										} else {
											$portee_arme = $porteeMin_arme." - ".$porteeMax_arme;
										}
										
										// Degats de l'arme
										$degats_arme = $degatMin_arme."D".$valeurDes_arme;
										
										if($compteur == 0){
											echo "<tr width=150>";
										}
										
										if($compteur == 3 || $compteur == 6){
											echo "</tr><tr width=150>";
										}
										
										if($compteur == 9){
											echo "</tr>";
										}
										
										affiche_image_arme($image_arme, $nom_arme, $description_arme, $portee_arme, $degats_arme);
										
										$compteur ++;
									}
									
									?>
													<tr>
														<td>&nbsp;</td>
													</tr>
													<tr>
														<td colspan=3 align=center><b>Vos armes non équipable</b></td>
													</tr>
									<?php
									$compteur = 0;
									
									$sql_a2 = "SELECT perso_as_arme.id_arme, nom_arme, description_arme, porteeMin_arme, porteeMax_arme, degatMin_arme, degatMax_arme, degatMax_arme, valeur_des_arme, degatZone_arme, image_arme 
												FROM perso_as_arme, arme
												WHERE perso_as_arme.id_arme = arme.id_arme 
												AND est_portee='0' AND perso_as_arme.id_perso='$id_perso' 
												AND perso_as_arme.id_arme NOT IN 
													(
														SELECT perso_as_arme.id_arme
														FROM perso_as_arme, arme, arme_as_type_unite, perso 
														WHERE perso_as_arme.id_arme = arme.id_arme 
														AND perso_as_arme.id_perso = perso.id_perso 
														AND perso.type_perso = arme_as_type_unite.id_type_unite
														AND arme_as_type_unite.id_arme = arme.id_arme
														AND est_portee='0' AND perso_as_arme.id_perso='$id_perso'
													)
												ORDER BY perso_as_arme.id_arme";
												
									$res_a2 = $mysqli->query($sql_a2);
									
									while($t_a = $res_a2->fetch_assoc()){
										
										$id_arme 					= $t_a["id_arme"];
										$nom_arme 					= $t_a["nom_arme"];
										$porteeMin_arme 			= $t_a["porteeMin_arme"];
										$porteeMax_arme 			= $t_a["porteeMax_arme"];
										$degatMin_arme 				= $t_a["degatMin_arme"];
										$degatMax_arme 				= $t_a["degatMax_arme"];
										$valeurDes_arme 			= $t_a["valeur_des_arme"];
										$degatZone_arme 			= $t_a["degatZone_arme"];
										$description_arme 			= $t_a["description_arme"];
										$image_arme 				= $t_a["image_arme"];
										
										// Portee de l'arme
										if ($porteeMin_arme == $porteeMax_arme) {
											$portee_arme = $porteeMin_arme;
										} else {
											$portee_arme = $porteeMin_arme." - ".$porteeMax_arme;
										}
										
										// Degats de l'arme
										$degats_arme = $degatMin_arme."D".$valeurDes_arme;
										
										if($compteur == 0){
											echo "<tr width=150>";
										}
										
										if($compteur == 3 || $compteur == 6){
											echo "</tr><tr width=150>";
										}
										
										if($compteur == 9){
											echo "</tr>";
										}
										
										affiche_image_arme($image_arme, $nom_arme, $description_arme, $portee_arme, $degats_arme);
										
										$compteur ++;
									}
									?>
												</table>
											</td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		
		</div>
		
		<?php
		}
	}
	else{
		echo "<font color=red>Vous ne pouvez pas acceder a cette page, veuillez vous logguer.</font>";
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

/**
  * Fonction qui permet d'afficher l'image d'une armure ainsi que son infobulle complete
  * @param $image_armures			: L'image de l'armure
  * @param $nom_armure			: Le nom de l'armure
  * @param $defense_armure			: Le score de defense de l'armure
  * @param $description_armures		: La description complete de l'armure
  * @param $BonusPerception_armures	: Le bonus de perception qu'apporte l'armure
  * @param $bonusPv_armure			: Le bonus de pv qu'apporte l'armure
  * @return Void
  */
function affiche_image_armure($image_armures, $nom_armure, $defense_armure, $description_armures,
							  $BonusPerception_armures, $bonusPv_armure){
								  
	$description_arme = $description_armures;							  
								  
	echo "<td width=50 height=50 align=center>
			<img tabindex='0' src='../images/armures/$image_armures' width=50 height=50 
					data-toggle='popover' data-trigger='focus' data-html='true' data-placement='bottom'
					title=\"<b>$nom_armure</b>\" ";
	echo "			data-content=\"	<div><img src=../images/armures/$image_armures></div>
									<div><u>Defense :</u> $defense_armure</div>";
			 if($BonusPerception_armures){
				echo "<div><u>Bonus Perception :</u> $BonusPerception_armures</div>";
			 }
			 if($bonusPv_armure){
				echo "<div><u>bonus Pv :</u> $bonusPv_armure</div>";
			 }
			 echo "<div><b>Description</b></div><div>$description_armures</div>\" >";
}

/**
  * Fonction qui permet d'afficher l'image d'une arme ainsi que son infobulle complete
  */
function affiche_image_arme($image_arme, $nom_arme, $description_arme, $portee_arme, $degats_arme){
	
	$description_arme = $description_arme;
	
	echo "	<td width=50 height=50 align=center>
				<img tabindex='0' src='../images/armes/$image_arme' width=50 height=50 
					data-toggle='popover' data-trigger='focus' data-html='true' data-placement='bottom'
					title=\"<b>$nom_arme</b>\" ";
	echo "			data-content=\"	<div><img src=../images/armes/$image_arme></div>
									<div><u>Portee :</u> $portee_arme</div>
									<div><u>Degats :</u> $degats_arme</div>
									<div><b>Description</b></div>
									<div>$description_arme</div>\" >";
	echo "	</td>";
}
?>