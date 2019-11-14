<?php
session_start();
require_once("../fonctions.php");
db_connexion();

include ('../nb_online.php');

// recupération config jeu
$sql = "SELECT disponible FROM config_jeu";
$res = exec_requete($sql, __LINE__, __FILE__);
$t_dispo = mysql_fetch_assoc($res);
$dispo = $t_dispo["disponible"];

if($dispo){

	?>
	<html>
	<head>
	  <title>NAOnline</title>
	  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	   <meta http-equiv="Content-Language" content="fr" />
	  <link rel="stylesheet" type="text/css" media="screen" href="onglet.css" title="Version 1" />
	
	</head>
	<body>
	<div id="header">
	  <ul>
		<li id="current"><a href="#">Profil</a></li>
		<li><a href="ameliorer.php">Améliorer son perso</a></li>
		<li><a href="equipement.php">Equiper son perso</a></li>
		<li><a href="pnjdex.php">PNJ Dex</a></li>
		<li><a href="mes_monstres.php">Mes monstres</a></li>
		<li><a href="compte.php">Gérer son Compte</a></li>
	  </ul>
	</div>
	<?php
	if (@$_SESSION["id_perso"]) {
		//recuperation des variables de sessions
		$id = $_SESSION["id_perso"];
		$sql = "SELECT pv_perso, est_gele FROM perso WHERE id_perso='$id'";
		$res = exec_requete($sql, __LINE__, __FILE__);
		$tpv = mysql_fetch_assoc($res);
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
				$sql = "SELECT nom_perso, image_perso, niveau_perso, xp_perso, x_perso, y_perso, deAttaque_perso, deDefense_perso, pm_perso, pi_perso, pv_perso, pvMax_perso, pmMax_perso, pa_perso, paMax_perso, degats_perso, recup_perso, bonusRecup_perso, perception_perso, bonusPerception_perso, bonus_perso, charge_perso, chargeMax_perso, message_perso, description_perso, dateCreation_perso, clan, mainPrincipale_perso FROM perso WHERE id_perso='$id'";
				$res = exec_requete($sql);
				$t_i = mysql_fetch_assoc($res);
				$nom_p = $t_i["nom_perso"];
				$image_p = $t_i["image_perso"];
				$xp_p = $t_i["xp_perso"];
				$x_p = $t_i["x_perso"];
				$y_p = $t_i["y_perso"];
				$deA_p = $t_i["deAttaque_perso"];
				$deD_p = $t_i["deDefense_perso"];
				$pm_p = $t_i["pm_perso"];
				$pmM_p = $t_i["pmMax_perso"];
				$pi_p = $t_i["pi_perso"];
				$pv_p = $t_i["pv_perso"];
				$pvM_p = $t_i["pvMax_perso"];
				$pa_p = $t_i["pa_perso"];
				$paM_p = $t_i["paMax_perso"];
				$rec_p = $t_i["recup_perso"];
				$br_p = $t_i["bonusRecup_perso"];
				$per_p = $t_i["perception_perso"];
				$bp_p = $t_i["bonusPerception_perso"];
				$br_p = $t_i["bonusRecup_perso"];
				$b_p = $t_i["bonus_perso"];
				$ch_p = $t_i["charge_perso"];
				$chM_p = $t_i["chargeMax_perso"];
				$deg_p = $t_i["degats_perso"];
				$lvl_p = $t_i["niveau_perso"];
				$dc_p = $t_i["dateCreation_perso"];
				$clan_perso = $t_i["clan"];
				if($clan_perso == '1'){
					$couleur_clan_perso = 'blue';
					$nom_clan = 'Bleus';
				}
				if($clan_perso == '2'){
					$couleur_clan_perso = 'red';
					$nom_clan = 'Rouges';
				}
				if($clan_perso == '3'){
					$couleur_clan_perso = 'green';
					$nom_clan = 'Verts';
				}
				
				$mes_p = $t_i["message_perso"];
				$des_p = $t_i["description_perso"];
				
				$mainPrincipale_p = $t_i["mainPrincipale_perso"];
				
				$t_im = explode("t",$image_p);
				$im_p = $t_im[0]."e.png";
				
				if(isset($_POST["changer_main_p"]) && $_POST["changer_main_p"] != ""){
					if($mainPrincipale_p == 1){
						$new_main_p = 0;
						$mainPrincipale_p = $new_main_p;
					}
					else {
						$new_main_p = 1;
						$mainPrincipale_p = $new_main_p;
					}
					$sql = "UPDATE perso SET mainPrincipale_perso='$new_main_p' WHERE id_perso='$id'";
					exec_requete($sql);
				}
				
		?>
		<br /><br /><br /><br />
		<div align=center><input type="button" value="Fermer le profil" onclick="window.close()"></div>
		
		<table border=0 width=100%>
		
			<tr><td>
		
			<table border=1 height=50% width=100%>
				
				<tr><td width=25%>
			
				<table border=0 width=100%>
					<tr>
						<td align="center"><img src="../images/<?php echo $im_p; ?>"></td>
					</tr>
				</table>
				
				</td><td width=75%>
			
				<table border=0 width=100%>
					<tr>
						<td><?php echo "<u><b>Pseudo :</b></u><font color=\"$couleur_clan_perso\"> ".$nom_p." </font>- <b><u>Niveau :</u></b> ".$lvl_p." - <b><u>Camp :</u></b><font color=\"$couleur_clan_perso\"> ".$nom_clan." </font>"; ?></td>
					</tr>
					<tr>
						<td><?php echo "<u><b>Xp :</b></u> ".$xp_p." - <u><b>Pi :</b></u> ".$pi_p.""; ?></td>
					</tr>
					<tr>
						<td><?php echo "<u><b>Position sur la carte :</b></u> ".$x_p."/".$y_p; ?></td>
					</tr>
					<tr>
						<td><?php echo "<u><b>Nombre de dés :</b></u> ".max($deA_p,$deD_p)." - <b><u>Dégâts :</u></b> ".$deg_p; ?></td>
					</tr>
					<tr>
						<td><?php echo "<u><b>Mouvements restants :</b></u> ".$pm_p."/".$pmM_p; ?><?php echo " - <u><b>Points de vie :</b></u> ".$pv_p."/".$pvM_p; ?></td>
					</tr>
					<tr>
						<td><?php echo "<u><b>Points d'action :</b></u> ".$pa_p."/".$paM_p." - <u><b>Malus de defense :</b></u> "; if($b_p < 0) echo "<font color=red>"; echo $b_p; ?></td>
					</tr>
					<tr>
						<td><?php echo "<u><b>Récupération :</b></u> ".$rec_p; if($br_p) echo " <font color='blue'>(+".$br_p.")</font>"; ?><?php echo " - <u><b>Perception :</b></u> ".$per_p; if($bp_p) {if($bp_p>0) echo " (+".$bp_p.")"; else echo " (".$bp_p.")";} ?></td>
					</tr>
					<tr>
						<td><?php echo "<u><b>Main principale :</b></u> "; if($mainPrincipale_p == 1){ echo "Droite";} if($mainPrincipale_p == 0){ echo "Gauche";}?>&nbsp;<form method="post" action="profil.php"><input type="submit" name="changer_main_p" value="changer de main"></form></td>
					</tr>
				</table>
				</td>
		
			</table>
			
			</tr>
			<tr>
			
			<table border=1 height=50% width=100%>
				<tr align=center>
					<td align=center><u><b>Description</b></u> (<a href="changer_description.php">Changer</a>)<br><br><?php if($des_p == "") echo "Pas de description"; else echo bbcode(htmlentities(stripslashes($des_p))); ?><br><br><u><b>Message du jour</b></u> (<a href="changer_message.php">Changer</a>)<br><br><?php if($mes_p == "") echo "Pas de message du jour"; else echo stripslashes(br2nl2($mes_p)); ?></td>
				</tr>
			</table>
			
			</td></tr>
			
		</table>
		<?php
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