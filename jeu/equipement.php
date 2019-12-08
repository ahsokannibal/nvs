<?php
session_start();
require_once("../fonctions.php");
require_once("f_carte.php");

$mysqli = db_connexion();

include ('../nb_online.php');

// recupération config jeu
$sql = "SELECT disponible FROM config_jeu";
$res = $mysqli->query($sql);
$t_dispo = $res->fetch_assoc();

$dispo = $t_dispo["disponible"];

if($dispo){
	
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
			header("Location: ../tour.php");
		}
		else {
?>
<html>

	<head>
		<title>Nord VS Sud</title>
			<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
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
					echo "<li><a href=\"gestion_grouillot.php\">Gérer ses grouillots</a></li>";
				}
				?>
				<li id="current"><a href="#">Equiper son perso</a></li>
				<li><a href="compte.php">Gérer son Compte</a></li>
			</ul>
		</div>
		
		<SCRIPT LANGUAGE="JavaScript" SRC="javascript/infobulle.js"></script>
		<SCRIPT language="JavaScript">
			InitBulle("#000000","#f4f4f4","000000",1);
			// InitBulle(couleur de texte, couleur de fond, couleur de contour taille contour)
		</SCRIPT>
		
		<br /><br /><center><h1>Equipement</h1></center><br /><br />
		
		<div align=center><input type="button" value="Fermer la page équipement" onclick="window.close()"></div>

		<?php
		$mess = "";
		?>
			
		<table border=0 width=550 height=400 align='center' cellpadding=0 cellspacing=0>
			<tr>
				<td width=350 align='center'><b>Votre Equipement porté</b><br />&nbsp;</td>
				<td width=200 align='center'><b>Votre Equipement en sac</b><br />&nbsp;</td>
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
					$portee_armes[$i] = $porteeMin_arme[$i]." - ".$porteeMax_arme[$i];
					
					$i++;
				}
						
				// recuperation des donnees des armures equipes
				$sql_e_a2 = "SELECT perso_as_armure.id_armure, nom_armure, pvMax_armure, description_armure, BonusPerception_armure, bonusPv_armure, bonusDefense_armure, bonusRecup_armure, bonusAttaque_armure, bonusPm_armure, image_armure, armure.corps_armure FROM perso_as_armure, armure WHERE perso_as_armure.id_armure = armure.id_armure AND est_portee='1' AND id_perso='$id_perso'";
				$res_e_a2 = $mysqli->query($sql_e_a2);
				
				$j = 0;
				
				while($t_e_a2 = $res_e_a2->fetch_assoc()){
					
					$nom_armure[$j] 			= $t_e_a2["nom_armure"];
					$defense_armure[$j] 		= $t_e_a2["bonusDefense_armure"];
					$bonusRecup_armure[$j] 		= $t_e_a2["bonusRecup_armure"];
					$bonusAttaque_armure[$j] 	= $t_e_a2["bonusAttaque_armure"];
					$bonusPm_armure[$j] 		= $t_e_a2["bonusPm_armure"];
					$bonusPv_armure[$j] 		= $t_e_a2["bonusPv_armure"];
					$BonusPerception_armure[$j] = $t_e_a2["BonusPerception_armure"];
					$description_armure[$j] 	= $t_e_a2["description_armure"];
					$pvMax_armure[$j] 			= $t_e_a2["pvMax_armure"];
					$image_armure[$j] 			= $t_e_a2["image_armure"];
					
					// Affectation des variables selon la position de l'armure ( 1 => tete, 2 => Collier, 3 => Corps, 6 => Gants, 7 => ceinture, 8 => Pantalon, 9 => Bottes, 10 => Item, 11 => Bagues )
					$image_armures[$corps_armure[$j]] 			= $image_armure[$j];
					$nom_armures[$corps_armure[$j]] 			= $nom_armure[$j];
					$defense_armures[$corps_armure[$j]] 		= $defense_armure[$j];
					$description_armures[$corps_armure[$j]] 	= $description_armure[$j];
					$BonusPerception_armures[$corps_armure[$j]] = $BonusPerception_armure[$j];
					$bonusPv_armures[$corps_armure[$j]] 		= $bonusPv_armure[$j];
					$pvMax_armures[$corps_armure[$j]] 			= $pvMax_armure[$j];
					
					$j++;
				}
											
			?>
			<tr>
				<td>
					<table border='1' width='350' height='400' align='center' cellpadding='0' cellspacing='0' background='../images/croquit2.jpg'>
						
						<tr width=350 height=50>
							<td width=50 height=50 align='center'>&nbsp;</td>
							
							<!-- Item -->
							<?php 
							if(isset($image_armures[10]) && $image_armures[10] != "vide"){
								echo "<td width=50 height=50><img src='../images/armures/$image_armures[10]' width=50 height=50 onMouseOver=\"AffBulle('<img src=../images/armures/$image_armures[10]>')\" onMouseOut=\"HideBulle()\"></td>"; // Item 
							} else { ?>
								<td width=50 height=50 align='center'>&nbsp;</td>
							<?php } ?>
								
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
							<?php if(isset($image_armures[1]) && $image_armures[1] != "vide"){
								affiche_image_armure($image_armures[1],$nom_armures[1],$defense_armures[1],$description_armures[1],$BonusPerception_armures[1],$bonusPv_armures[1]);
							} else {?>
								<td width=50 height=50 align='center'>&nbsp;</td>
							<?php } ?>
							
							<!-- COLLIER -->
							<?php if(isset($image_armures[2]) && $image_armures[2] != "vide"){
								affiche_image_armure($image_armures[2],$nom_armures[2],$defense_armures[2],$description_armures[2],$BonusPerception_armures[2],$bonusPv_armures[2]);
							} else {?>
								<td width=50 height=50 align='center'>&nbsp;</td>
							<?php } ?>	
							
							<td width=50 height=50 align='center'>&nbsp;</td>
							<td width=50 height=50 align='center'>&nbsp;</td>  
						</tr>
						
						<tr width=350 height=50>
							<!-- GANTS -->
							<?php if(isset($image_armures[6]) && $image_armures[6] != "vide"){
								affiche_image_armure($image_armures[6],$nom_armures[6],$defense_armures[6],$description_armures[6],$BonusPerception_armures[6],$bonusPv_armures[6]);
							} else {?>
								<td width=50 height=50 align='center'>&nbsp;</td>
							<?php } ?>
						
							<!-- MAIN DROITE -->
							<?php if(isset($image_armes[1]) && $image_armes[1] != "vide"){
								affiche_image_arme($image_armes[1], $nom_armes[1], $description_armes[1], $portee_armes[1], $degats_armes[1]);
							} else {?>
								<td width=50 height=50 align='center'>&nbsp;</td>
							<?php } ?>
							
							<td width=50 height=50 align='center'>&nbsp;</td>
							
							<!-- CORPS -->
							<?php if(isset($image_armures[3]) && $image_armures[3] != "vide"){
								affiche_image_armure($image_armures[3],$nom_armures[3],$defense_armures[3],$description_armures[3],$BonusPerception_armures[3],$bonusPv_armures[3]);
							} else {?>
								<td width=50 height=50 align='center'>&nbsp;</td>
							<?php } ?>
							
							<td width=50 height=50 align='center'>&nbsp;</td>
							
							<!-- MAIN GAUCHE -->
							<?php if(isset($image_armes[0]) && $image_armes[0] != "vide"){
								affiche_image_arme($image_armes[0], $nom_armes[0], $description_armes[0], $portee_armes[0], $degats_armes[0]);
							} else {?>
								<td width=50 height=50 align='center'>&nbsp;</td>
							<?php } ?>
						
							<!-- BAGUE -->
							<?php if(isset($image_armures[11]) && $image_armures[11] != "vide"){
								affiche_image_armure($image_armures[11],$nom_armures[11],$defense_armures[11],$description_armures[11],$BonusPerception_armures[11],$bonusPv_armures[11]);
							} else {?>
								<td width=50 height=50 align='center'>&nbsp;</td>
							<?php } ?>  
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
							<?php if(isset($image_armures[8]) && $image_armures[8] != "vide"){
								affiche_image_armure($image_armures[8],$nom_armures[8],$defense_armures[8],$description_armures[8],$BonusPerception_armures[8],$bonusPv_armures[8]);
							} else {?>
								<td width=50 height=50 align='center'>&nbsp;</td>
							<?php } ?>
							
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
							<?php if(isset($image_armures[9]) && $image_armures[9] != "vide"){
								affiche_image_armure($image_armures[9],$nom_armures[9],$defense_armures[9],$description_armures[9],$BonusPerception_armures[9],$bonusPv_armures[9]);
							} else {?>
								<td width=50 height=50 align='center'>&nbsp;</td>
							<?php } ?>
							
							<td width=50 height=50 align='center'>&nbsp;</td>
							<td width=50 height=50 align='center'>&nbsp;</td>
							<td width=50 height=50 align='center'>&nbsp;</td>  
						</tr>
					</table>
				</td>
				<td>
					<table border='0' cellspacing=95>
						<tr>
							<td>
								<table border='0' width=160 height=200 align='center' cellpadding=0 cellspacing=0>
									<tr>
										<td>
											<table border='0' width=160 height=100 align='center' cellpadding=0 cellspacing=0>
												<tr>
													<td colspan=3 align=center><a href="equipement_armes.php">Equiper / Desequiper une arme</a></td>
												</tr>
												<tr>
													<td colspan=3 align=center><b>Vos Armes</b></td>
												</tr>
								<?php
								$compteur = 0;
								
								// recuperation des armes du perso
								$sql_a = "SELECT perso_as_arme.id_arme, nom_arme, description_arme, porteeMin_arme, porteeMax_arme, additionMin_degats, additionMax_degats, multiplicateurMin_degats, multiplicateurMax_degats, degatMin_arme, degatMax_arme, pvMax_arme, image_arme 
											FROM perso_as_arme, arme 
											WHERE perso_as_arme.id_arme = arme.id_arme AND est_portee='0' AND id_perso='$id_perso' 
											ORDER BY perso_as_arme.id_arme LIMIT 9";
								$res_a = $mysqli->query($sql_a);
								
								while($t_a = $res_a->fetch_assoc()){
									
									$id_arme = $t_a["id_arme"];
									$nom_arme = $t_a["nom_arme"];
									$porteeMin_arme = $t_a["porteeMin_arme"];
									$porteeMax_arme = $t_a["porteeMax_arme"];
									$additionMin_degats = $t_a["additionMin_degats"];
									$additionMax_degats = $t_a["additionMax_degats"];
									$multiplicateurMin_degats = $t_a["multiplicateurMin_degats"];
									$multiplicateurMax_degats = $t_a["multiplicateurMax_degats"];
									$degatMin_arme = $t_a["degatMin_arme"];
									$degatMax_arme = $t_a["degatMax_arme"];
									$pvMax_arme= $t_a["pvMax_arme"];
									$description_arme = $t_a["description_arme"];
									$image_arme = $t_a["image_arme"];
									
									// Portee de l'arme
									$portee_arme = $porteeMin_arme." - ".$porteeMax_arme;
									
									// Calcul des degats de l'arme
									if($degatMin_arme && $degatMax_arme){
										$degats_arme = $degatMin_arme." - ".$degatMax_arme;
									}
									else {
										$deg_min = $degats_perso * $multiplicateurMin_degats + $additionMin_degats;
										$deg_max = $degats_perso * $multiplicateurMax_degats + $additionMax_degats;
										$degats_arme = $deg_min." - ".$deg_max;
									}
									
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
								if($compteur < 4) {
									while ($compteur % 3 || $compteur < 9) {
										if($compteur == 3 || $compteur == 6){
											echo "</tr><tr width=150>";
										}
										if($compteur == 9){
											echo "</tr>";
										}
										echo "<td width=50 height=50 align='center'>&nbsp;</td>";
										$compteur ++;
									}
								}
								?>
											</table>
										</td>
									</tr>
									<tr>
										<td>
											<table border='0' width=160 height=100 align='center' cellpadding=0 cellspacing=0>
												<tr>
													<td colspan=3 align=center><a href="equipement_armures.php">Equiper / Deséquiper une armure</a></td>
												</tr>
												<tr>
													<td colspan=3 align=center><b>Vos Armures</b></td>
												</tr>
								<?php
								$compteur2 = 0;
								
								// recuperation des armes du perso
								$sql_a2 = "SELECT perso_as_armure.id_armure, nom_armure, image_armure, pvMax_armure, description_armure, BonusPerception_armure, bonusPv_armure, bonusDefense_armure, bonusRecup_armure, bonusAttaque_armure, bonusPm_armure FROM perso_as_armure, armure WHERE perso_as_armure.id_armure = armure.id_armure AND est_portee='0' AND id_perso='$id_perso' ORDER BY perso_as_armure.id_armure LIMIT 9";
								$res_a2 = $mysqli->query($sql_a2);
								
								while($t_a2 = $res_a2->fetch_assoc()){
									
									$id_armure = $t_a2["id_armure"];
									$nom_armure = $t_a2["nom_armure"];
									$defense_armure = $t_a2["bonusDefense_armure"];
									$description_armure = $t_a2["description_armure"];
									$pvMax_armure = $t_a2["pvMax_armure"];
									$bonusPerception_armure = $t_a2["BonusPerception_armure"];
									$bonusRecup_armure = $t_a2["bonusRecup_armure"];
									$bonusPm_armure = $t_a2["bonusPm_armure"];
									$bonusPv_armure = $t_a2["bonusPv_armure"];
									$image_armure = $t_a2["image_armure"];
									
									if($compteur2 == 0){
										echo "<tr width=150>";
									}
									
									if($compteur2 == 3 || $compteur2 == 6){
										echo "</tr><tr width=150>";
									}
									
									if($compteur2 == 9){
										echo "</tr>";
									}
									
									affiche_image_armure($image_armure,$nom_armure,$defense_armure,$description_armure,$bonusPerception_armure,$bonusPv_armure);
									
									//echo "<td width='50' height='50' align='center'><img src=\"../images/armures/$image_armure\" width=50 height=50></td>";
									$compteur2 ++;
								}
								if($compteur2 < 4) {
									while ($compteur2 % 3 || $compteur2 < 9) {
										if($compteur2 == 3 || $compteur2 == 6){
											echo "</tr><tr width=150>";
										}
										
										if($compteur2 == 9){
											echo "</tr>";
										}
										echo "<td width=50 height=50 align='center'>&nbsp;</td>";
										$compteur2++;
									}
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
		
		<?php
		}
	}
	else{
		echo "<font color=red>Vous ne pouvez pas acceder a cette page, veuillez vous logguer.</font>";
	}
	?>
	</body>
</html>
<?php
}
else {
	// logout
	$_SESSION = array(); // On ecrase le tableau de session
	session_destroy(); // On detruit la session
	
	header("Location: index2.php");
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
	echo "<td width=50 height=50 align=center>
			<img src='../images/armures/$image_armures' width=50 height=50 
			 onMouseOver=\"AffBulle('<table width=250><tr><td colspan=2 align=center><b>$nom_armure</b></td></tr><tr><td rowspan=3><img src=../images/armures/$image_armures></td><td><u>Defense :</u> $defense_armure</td></tr><tr><td>";
			 if($BonusPerception_armures){
				echo "<u>Bonus Perception :</u> $BonusPerception_armures";
			 }
			 echo "</td></tr><tr><td>";
			 if($bonusPv_armure){
				echo "<u>bonus Pv :</u> $bonusPv_armure";
			 }
			 echo "</td></tr><tr><td colspan=2 align=center><font color=grey><b>~~ Description ~~</b></font></td></tr><tr><td colspan=2 align=justify>$description_armures</td></tr></table>')\" 
										 
			 onMouseOut=\"HideBulle()\">
		  </td>";
}

/**
  * Fonction qui permet d'afficher l'image d'une arme ainsi que son infobulle complete
  */
function affiche_image_arme($image_arme, $nom_arme, $description_arme, $portee_arme, $degats_arme){
	
	echo "	<td width=50 height=50 align=center>
				<img src='../images/armes/$image_arme' width=50 height=50 
					onMouseOver=\"AffBulle('<table width=250><tr><td colspan=2 align=center><b>$nom_arme</b></td></tr><tr><td rowspan=2><img src=../images/armes/$image_arme></td><td><u>Portee :</u> $portee_arme</td></tr><tr><td><u>Degats :</u> $degats_arme</td></tr>";
	echo "</td></tr><tr><td colspan=2 align=center><font color=grey><b>~~ Description ~~</b></font></td></tr><tr><td colspan=2 align=justify>$description_arme</td></tr></table>')\" 						 
			 onMouseOut=\"HideBulle()\">
		  </td>";
}
?>