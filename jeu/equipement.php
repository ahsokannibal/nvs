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
					$portee_armes[$i] = $porteeMin_arme[$i]." - ".$porteeMax_arme[$i];
					
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
													<td colspan=3 align=center><a href="equipement_armes.php">Equiper / Desequiper une arme</a></td>
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
											AND est_portee='0' AND perso_as_arme.id_perso='2' 
											AND perso_as_arme.id_arme NOT IN 
												(
													SELECT perso_as_arme.id_arme
													FROM perso_as_arme, arme, arme_as_type_unite, perso 
													WHERE perso_as_arme.id_arme = arme.id_arme 
													AND perso_as_arme.id_perso = perso.id_perso 
													AND perso.type_perso = arme_as_type_unite.id_type_unite
													AND arme_as_type_unite.id_arme = arme.id_arme
													AND est_portee='0' AND perso_as_arme.id_perso='2'
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
								  
	$description_arme = addslashes($description_armures);							  
								  
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
	
	$description_arme = addslashes($description_arme);
	
	echo "	<td width=50 height=50 align=center>
				<img src='../images/armes/$image_arme' width=50 height=50 
					onMouseOver=\"AffBulle('<table width=250><tr><td colspan=2 align=center><b>$nom_arme</b></td></tr><tr><td rowspan=2><img src=../images/armes/$image_arme></td><td><u>Portee :</u> $portee_arme</td></tr><tr><td><u>Degats :</u> $degats_arme</td></tr>";
	echo "</td></tr><tr><td colspan=2 align=center><font color=grey><b>~~ Description ~~</b></font></td></tr><tr><td colspan=2 align=justify>$description_arme</td></tr></table>')\" 						 
			 onMouseOut=\"HideBulle()\">
		  </td>";
}
?>