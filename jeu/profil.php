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

	if (isset($_SESSION["id_perso"])) {
		
		//recuperation des variables de sessions
		$id = $_SESSION["id_perso"];
		$sql = "SELECT pv_perso, est_gele FROM perso WHERE id_perso='$id'";
		$res = $mysqli->query($sql);
		$tpv = $res->fetch_assoc();
		
		$testpv = $tpv['pv_perso'];
		$e_g = $tpv['est_gele'];
		
		if($e_g){
			// redirection
			header("location:../tour.php");
		}
		else {
			
			if ($testpv <= 0) {
				echo "<font color=red>Vous êtes mort...</font>";
			}
			else {
			
				// recuperation des infos du perso
				$sql = "SELECT nom_perso, image_perso, xp_perso, pc_perso, x_perso, y_perso, pm_perso, bonusPM_perso, pi_perso, pv_perso, pvMax_perso, 
								pmMax_perso, pa_perso, paMax_perso, recup_perso, bonusRecup_perso, perception_perso, bonusPerception_perso, bonus_perso, bonusPA_perso,
								charge_perso, chargeMax_perso, message_perso, description_perso, dateCreation_perso, clan, chef, genie FROM perso WHERE id_perso='$id'";
				$res = $mysqli->query($sql);
				$t_i = $res->fetch_assoc();
				
				$nom_p 		= $t_i["nom_perso"];
				$image_p 	= $t_i["image_perso"];
				$xp_p 		= $t_i["xp_perso"];
				$pc_p 		= $t_i["pc_perso"];
				$x_p 		= $t_i["x_perso"];
				$y_p 		= $t_i["y_perso"];
				$pm_p 		= $t_i["pm_perso"];
				$pmM_p 		= $t_i["pmMax_perso"];
				$pi_p 		= $t_i["pi_perso"];
				$pv_p 		= $t_i["pv_perso"];
				$pvM_p 		= $t_i["pvMax_perso"];
				$pa_p 		= $t_i["pa_perso"];
				$paM_p 		= $t_i["paMax_perso"];
				$rec_p 		= $t_i["recup_perso"];
				$br_p 		= $t_i["bonusRecup_perso"];
				$per_p 		= $t_i["perception_perso"];
				$bp_p 		= $t_i["bonusPerception_perso"];
				$br_p 		= $t_i["bonusRecup_perso"];
				$bpm_p		= $t_i["bonusPM_perso"];
				$bpa_p		= $t_i["bonusPA_perso"];
				$b_p 		= $t_i["bonus_perso"];
				$ch_p 		= $t_i["charge_perso"];
				$chM_p 		= $t_i["chargeMax_perso"];
				$dc_p 		= $t_i["dateCreation_perso"];
				$clan_perso = $t_i["clan"];
				$chef		= $t_i["chef"];
				$genie		= $t_i["genie"];
				
				$sql = "SELECT fond_carte FROM carte WHERE x_carte='$x_p' AND y_carte='$y_p'";
				$res = $mysqli->query($sql);
				$t_f = $res->fetch_assoc();
				
				$fond = $t_f['fond_carte'];
				
				// Bonus / Malus defense objets 
				$bonus_def_obj = get_bonus_defense_objet($mysqli, $id);
				
				// Bonus / Malus defense batiment
				$bonus_defense_bat = get_bonus_defense_instance_bat($mysqli, $id);
				
				// Bonus / Malus defense terrain / batiment
				$bonus_def_terrain_cac = get_bonus_defense_terrain($fond, $id);
				$bonus_def_terrain_dist = get_bonus_defense_terrain($fond, $id);
				
				$bonus_def = $b_p + $bonus_def_obj;
				
				$bonus_def_final_cac = $bonus_def + $bonus_def_terrain_cac + $bonus_defense_bat;
				$bonus_def_final_dist = $bonus_def + $bonus_def_terrain_dist + $bonus_defense_bat;
				
				if($clan_perso == '1'){
					$couleur_clan_perso = 'blue';
					$nom_clan = 'Nord';
				}
				if($clan_perso == '2'){
					$couleur_clan_perso = 'red';
					$nom_clan = 'Sud';
				}
				if($clan_perso == '3'){
					$couleur_clan_perso = 'green';
					$nom_clan = 'Indiens';
				}
				
				$im_p = $nom_clan.".gif";
				
				// calcul malus pm
				$malus_pm_charge = getMalusCharge($ch_p);
				if ($malus_pm_charge == 100) {
					$malus_pm = -$pmMax_perso;
				}
				else {
					$malus_pm = $malus_pm_charge;
				}
				
				$pm_perso 		= $pm_p + $malus_pm;
				$pmMax_perso 	= $pmM_p + $bpm_p;
				
				$mes_p = $t_i["message_perso"];
				$des_p = $t_i["description_perso"];
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
							<a class="nav-link active" href="#">Profil</a>
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
							<a class="nav-link" href="equipement.php">Equiper son perso</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" href="compte.php">Gérer son Compte</a>
						</li>
					</ul>
				</div>
			</nav>
			
			<hr>
		
			<br /><br /><center><h1>Profil</h1></center><br /><br />
			
			<div align=center><input type="button" value="Fermer le profil" onclick="window.close()"></div>
			
			<table border=0 width=100%>
			
				<tr>
					<td>
						<table border=1 height=50% width=100%>
							<tr>
								<td width=25%>
						
									<table border=0 width=100%>
										<tr>
											<td align="center"><img src="../images/<?php echo $im_p; ?>"></td>
										</tr>
									</table>
							
								</td>
								<td width=75%>
						
									<table border=0 width=100%>
										<tr>
											<td>
												<?php 
												echo "<u><b>Pseudo :</b></u> <font color=\"$couleur_clan_perso\">".$nom_p."</font>";
												if ($chef) {
													echo " <a class='btn btn-warning' href='nom_perso_change.php'>Demander à changer de nom</a>";
												}
												echo " - <b><u>Camp :</u></b><font color=\"$couleur_clan_perso\"> ".$nom_clan." </font>"; 
												
												?>
											</td>
										</tr>
										<tr>
											<td><?php echo "<u><b>Xp :</b></u> ".$xp_p." - <u><b>Pi :</b></u> ".$pi_p." - <u><b>PC :</b></u> ".$pc_p.""; ?></td>
										</tr>
										<tr>
											<td><?php echo "<u><b>Position sur la carte :</b></u> ".$x_p."/".$y_p; ?></td>
										</tr>
										<tr>
											<td><?php 
											echo "<u><b>Mouvements restants :</b></u> ".$pm_perso;
											if ($malus_pm) {
												
												echo " (";
												
												if ($malus_pm > 0) {
													echo " charge : +".$malus_pm."";
												}
												else if ($malus_pm < 0) {
													echo " charge : ".$malus_pm."";
												}
												
												echo " )";
											}
											echo " / ".$pmMax_perso;
												
											if ($bpm_p) {
												
												echo " (";
												
												if ($bpm_p > 0) {
													echo " objets : +".$bpm_p."";
												}
												else if ($bpm_p < 0) {
													echo " objets : ".$bpm_p."";
												}
												
												echo " )";
											}
											
											echo " - <u><b>Points de vie :</b></u> ".$pv_p."/".$pvM_p;
											?></td>
										</tr>
										<tr>
											<td><?php
											// PA
											echo "<u><b>Points d'action :</b></u> ".$pa_p."/".$paM_p;
											if ($bpa_p) {
												if ($bpa_p > 0) {
													echo " (+".$bpa_p.")";
												}
												else {
													echo " (".$bpa_p.")";
												}
											}
											// Malus defense CaC
											echo " - <u><b>Malus de défense CàC :</b></u> "; 
											if($bonus_def_final_cac < 0) {
												echo "<font color=red>".$bonus_def_final_cac."</font>";
											}
											else {
												echo $bonus_def_final_cac;
											}
											
											echo " ( base : ".$b_p." - objets : ".$bonus_def_obj." - terrain : ".$bonus_def_terrain_cac;
											if ($bonus_defense_bat != 0) {
												echo " - batiment : ".$bonus_defense_bat;
											}
											echo " )";
											
											// Malus defense Dist
											echo " - <u><b>Malus de défense Dist :</b></u> "; 
											if($bonus_def_final_dist < 0) {
												echo "<font color=red>".$bonus_def_final_dist."</font>";
											}
											else {
												echo $bonus_def_final_dist;
											}
											
											echo " ( base : ".$b_p." - objets : ".$bonus_def_obj." - terrain : ".$bonus_def_terrain_dist;
											if ($bonus_defense_bat != 0) {
												echo " - batiment : ".$bonus_defense_bat;
											}
											echo " )";
											?></td>
										</tr>
										<tr>
											<td><?php 
											// Récupération
											echo "<u><b>Récupération :</b></u> ".$rec_p; 
											if($br_p) {
												echo " <font color='blue'>(+".$br_p.")</font>";
											}
											// Perception
											echo " - <u><b>Perception :</b></u> ".$per_p; 
											if($bp_p) {
												if($bp_p > 0) {
													echo " (+".$bp_p.")";
												}
												else {
													echo " (".$bp_p.")";
												}
											}
											?></td>
										</tr>
										<tr>
											<td>&nbsp;</td>
										</tr>
										<?php
										if ($genie > 0) {
											$nb_tour_acces_bonus_genie = $genie - 1;
										?>
										<tr>
											<td><?php echo "<b><u>Nombre de tours avant accés aux compétences et bonus du génie :</u></b> ".$nb_tour_acces_bonus_genie." tour(s) restant"; ?></td>
										</tr>
										<tr>
											<td>&nbsp;</td>
										</tr>
										<?php
										}
										?>
										<tr>
											<td><a class="btn btn-primary" href="choix_rapatriement.php">Choix des rapatriements</a></td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td>
						<table border=1 height=50% width=100%>
							<tr align=center>
								<td align=center>
									<u><b>Description</b></u> (<a href="changer_description.php">Changer</a>)<br><br><?php if($des_p == "") echo "Pas de description"; else echo bbcode(htmlentities(stripslashes($des_p))); ?>
									<br><br>
									<u><b>Message du jour</b></u> (<a href="changer_message.php">Changer</a>)<br><br><?php if($mes_p == "") echo "Pas de message du jour"; else echo stripslashes(br2nl2($mes_p)); ?></td>
							</tr>
						</table>
					</td>
				</tr>
				
			</table>
		</div>
		<?php
			}
		}
	}
	else{
		echo "<font color=red>Vous ne pouvez pas accéder à cette page, veuillez vous loguer.</font>";
	}
	?>
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
	
	header("Location:../index2.php");
}
?>