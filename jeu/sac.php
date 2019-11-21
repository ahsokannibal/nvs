<?php
session_start();
require_once("../fonctions.php");

$mysqli = db_connexion();

include ('../nb_online.php');

// recuperation config jeu
$sql = "SELECT disponible FROM config_jeu";
$res = $mysqli->query($sql);
$t_dispo = $res->fetch_assoc();

$dispo = $t_dispo["disponible"];

if($dispo){

	if (@$_SESSION["id_perso"]) {
		
		//recuperation des variables de sessions
		$id = $_SESSION["id_perso"];
		
		// recuperation des donnees sur le perso
		$sql = "SELECT pv_perso, pa_perso FROM perso WHERE ID_perso='$id'";
		$res = $mysqli->query($sql);
		$tpv = $res->fetch_assoc();
		
		$testpv = $tpv['pv_perso'];
		$testpa = $tpv['pa_perso'];
		
		// On verifie que le perso soit toujours vivant
		if ($testpv <= 0) {
			echo "<font color=red>Vous êtes mort...</font>";
		}
		else {
			// on souhaite utiliser un objet
			if(isset($_GET["id_obj"]) && $_GET["id_obj"] != ""){
				
				// On recupere l'identifiant de l'objet
				$id_o = $_GET["id_obj"];
				
				// On verifie que l'identifiant soit bien un nombre positif
				$verif = preg_match("#^[0-9]*[0-9]$#i","$id_o");
				
				if($verif && $id_o > 0) {
					
					// On verifie que l'objet soit bien utilisable
					if($id_o != 6 && $id_o != 7 && $id_o != 8 && $id_o != 10){
						// ok
						//verification que le perso possede bien cet objet
						$sql = "SELECT id_objet FROM perso_as_objet WHERE id_perso='$id' AND id_objet='$id_o'";
						$res = $mysqli->query($sql);
						$ok = $res->num_rows;
						
						if($ok) { // possede plus de 0 objets
							
							// On verifie que le perso possede bien 1 pa pour utiliser l'objet
							if($testpa >= 1){
							
								// recuperation des effets de l'objet
								$sql = "SELECT nom_objet, bonusPerception_objet, bonusRecup_objet, bonusPv_objet, bonusPm_objet, coutPa_objet, poids_objet, type_objet FROM objet WHERE id_objet='$id_o'";
								$res = $mysqli->query($sql);
								$bonus_o = $res->fetch_assoc();
								
								$nom_ob = $bonus_o["nom_objet"];
								$bonusPerception = $bonus_o["bonusPerception_objet"];
								$bonusRecup = $bonus_o["bonusRecup_objet"];
								$bonusPv = $bonus_o["bonusPv_objet"];
								$bonusPm = $bonus_o["bonusPm_objet"];
								$coutPa = $bonus_o["coutPa_objet"];
								$poids = $bonus_o["poids_objet"];
								$type_o = $bonus_o["type_objet"];
										
								// on supprime l'objet de l'inventaire
								$sql = "DELETE FROM perso_as_objet WHERE id_perso='$id' AND id_objet='$id_o' LIMIT 1";
								$mysqli->query($sql);
										
								// on recupere les pv et autres donnees du perso
								$sql = "SELECT pv_perso, pvMax_perso, recup_perso, bonusRecup_perso FROM perso WHERE id_perso='$id'";
								$res = $mysqli->query($sql);
								$t_p = $res->fetch_assoc();
								
								$pv_p = $t_p["pv_perso"];
								$pvM_p = $t_p["pvMax_perso"];
								$rec_p = $t_p["recup_perso"];
								$br_p = $t_p["bonusRecup_perso"];
									
								// si l'objet donne des pv/pm/perception/recup
								if($bonusPv || $bonusPerception || $bonusPm || $bonusRecup) { 
										
									// l'objet lui fait recuperer un nombre de pv tel que ses pv apres soient inferieurs a ses pvmax 
									if($pv_p + $bonusPv < $pvM_p){
										
										// on applique les effets de l'objet sur le perso
										$sql = "UPDATE perso 
												SET pa_perso=pa_perso-1, pv_perso=pv_perso+$bonusPv, bonusPerception_perso=bonusPerception_perso+$bonusPerception, pm_perso=pm_perso+$bonusPm, bonusRecup_perso=bonusRecup_perso+$bonusRecup
												WHERE id_perso='$id'";
										$mysqli->query($sql);
										
										// Affichage 
										echo "Vous avez utilisé ".$nom_ob."<br>";
										if($type_o == 'S' || $type_o == 'SSP' || $id_o == 4) {
											echo "Vous gagnez ".$bonusPv." pv<br>";
										}
									}
									else { // l'effet de l'objet lui rend des pv tel qu'il atteind son max de pv (ou superieur mais on ne peut pas depasser le max de pv) 
									
										// on applique les effets de l'objet sur le perso
										$sql = "UPDATE perso 
												SET pv_perso=pvMax_perso, bonusPerception_perso=bonusPerception_perso+$bonusPerception, pm_perso=pm_perso+$bonusPm, bonusRecup_perso=bonusRecup_perso+$bonusRecup 
												WHERE id_perso='$id'";
										$mysqli->query($sql);
										
										// calcul du gain de pv
										$gain_pv = $pvM_p - $pv_p;
											
										// Affichage 
										echo "Vous avez utilisé ".$nom_ob."<br>";
										if($type_o == 'S' || $type_o == 'SSP' || $id_o == 4) {
											echo "Vous gagnez ".$gain_pv." pv (Vous avez atteind votre maximum de points de vie)<br>";
										}
									}
								}
								
								// Bouteille d'alcool
								if($id_o == 4){
									// le perso est bourre
									$sql = "UPDATE perso SET bourre_perso=bourre_perso+1 WHERE id_perso='$id'";
									$mysqli->query($sql);
									
									// calcul du gain de recup
									$recup_i = $rec_p+$br_p;
									
									// Affichage 
									echo "Votre recuperation passe de ".$recup_i." à ";
									echo $rec_p+$br_p+$bonusRecup." mais il parait que l'abus d'alcool est dangereux pour la santé... (perception -2)<br>";
								}
								
								// Longue vue
								if($id_o == 5){
									// Affichage
									echo "Vous obtenez un bonus de perception de +".$bonusPerception.".<br>";
								}
								
								// Fiole du berserker
								if($id_o == 9){
									// on enleve les malus de combat au perso
									$sql = "UPDATE perso SET bonus_perso='0' WHERE id_perso='$id'";
									$mysqli->query($sql);									
									
									// Affichage
									echo "Vous ne ressentez plus aucune douleurs et vous avez l'impression de pouvoir vous mouvoir comme il vous semble<br>";
								}
								
								// MAJ charge perso
								$sql_c = "UPDATE perso SET charge_perso=charge_perso-$poids WHERE id_perso='$id'";
								$mysqli->query($sql_c);
							}
							else {
								echo "Vous n'avez pas assez de PA, l'utilisation d'un objet coute 1 PA.";
							}
						}
						else {
							echo "Vous ne possédez pas/plus cet objet...";
						}
					}
					else {
						echo "Impossible de consommer cet objet !";
					}
				}
				else {
					echo "Il ne faut pas rentrer n'importe quoi dans la barre d'adresse...";
				}
			}
		?>
	<html>
	<head>
	<title>Nord VS Sud</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<link href="../style.css" rel="stylesheet" type="text/css">
	</head>
	
	<body>
	<?php
			// recuperation du nombre d'objet que possede le perso
			$sql = "SELECT COUNT(id_objet) FROM perso_as_objet WHERE id_perso='$id'";
			$res = $mysqli->query($sql);
			$t_nb_objets = $res->fetch_row();
			
			$nb_objets = $t_nb_objets[0];
			
			// recuperation de l'or que possede le perso
			$sql = "SELECT or_perso, charge_perso, chargeMax_perso FROM perso WHERE id_perso='$id'";
			$res = $mysqli->query($sql);
			$t_or = $res->fetch_assoc();
			
			$or_p = $t_or["or_perso"];
			$charge_perso = $t_or["charge_perso"];
			$chargeMax_perso = $t_or["chargeMax_perso"];
			$chargeMax_reel = $chargeMax_perso;
	?>
	<table border=0 width=100%>
		<tr><td>
		<table border=1 width=100%>
			<tr>
				<td align=center width=25%><img src="../images/sac.png"><p align="center"><input type="button" value="Fermer mon sac" onclick="window.close()"></p></td>
				<td width=75%>
					<center><h2>Mon sac</h2>
					<p>Le sac vous permet de transporter des objets et de les utiliser.<br>Vous possédez <b><?php echo $nb_objets; ?></b> objet<?php if($nb_objets > 1){echo "s";} ?> dans votre sac.</p>
					<?php 
					echo "<p><u><b>Charge :</b></u> ";
					if($charge_perso > $chargeMax_reel){
						echo "<font color='red'>";
					}
					else {
						echo "<font color='blue'>";
					}
					echo "".$charge_perso."</font> / ".$chargeMax_reel."</p>"; 
					?>
					<img src="../images/or.png" align="middle">Vous possédez <b><?php echo $or_p; ?></b> piéce<?php if($or_p > 1){echo "s";}?> d'or.<br>
					</center>
				</td>
			</tr>
		</table>
		</td></tr><tr><td>
		<table border=1 width=100%>
			<tr>
				<th width=25%>objet</th><th width=50%>description</th><th width=25%>nombre</th>
			</tr>
			<?php
			
			// recuperation du nombre de type d'objets que possede le perso
			$sql = "SELECT DISTINCT id_objet FROM perso_as_objet WHERE id_perso='$id'";
			$res = $mysqli->query($sql);
			$nb_obj = $res->num_rows;
			
			while ($t_obj = $res->fetch_assoc()){
			
					// id de l'objet
					$id_obj = $t_obj["id_objet"];
					
					// recuperation des carac de l'objet
					$sql1 = "SELECT nom_objet, poids_objet, description_objet FROM objet WHERE id_objet='$id_obj'";
					$res1 = $mysqli->query($sql1);
					$t_o = $resl->fetch_assoc();
					
					$nom_o = $t_o["nom_objet"];
					$poids_o = $t_o["poids_objet"];
					$description_o = $t_o["description_objet"];
					
					// recuperation du nombre d'objet de ce type que possede le perso
					$sql2 = "SELECT id_objet FROM perso_as_objet WHERE id_perso='$id' AND id_objet='$id_obj'";
					$res2 = $mysqli->query($sql2);
					$nb_o = $res2->num_rows;
					
					// calcul poids
					$poids_total_o = $poids_o * $nb_o;
					
					// affichage
					echo "<tr>";
					echo "<td align='center'><img src=\"../images/objet".$id_obj.".png\"></td>";
					echo "<td align='center'><font color=green><b>".$nom_o."</b></font><br>".stripslashes($description_o)."</td>";
					echo "<td align='center'>Vous possédez <b>".$nb_o."</b> ".$nom_o."";
					if($nb_o > 1 && $id_obj != 6 && $id_obj != 7){ 
						echo "s";
					}
					if($id_obj != 6 && $id_obj != 7 && $id_obj != 8 && $id_obj != 10){
						echo "<br /><a href=\"sac.php?id_obj=".$id_obj."\">utiliser</a>";
					}
					echo "<br /><u>Poids total :</u> <b>$poids_total_o</b></td>";
					echo "</tr>";
			}
			?>
		</table>
		</td></tr>
	
	</table>
	
	</body>
	</html>
	<?php
		}
	}
	else{
		echo "<font color=red>Vous ne pouvez pas accéder à cette page, veuillez vous loguer.</font>";
	}
	?>
<?php
}
else {
	// logout
	$_SESSION = array(); // On ecrase le tableau de session
	session_destroy(); // On detruit la session
	
	header("Location: index2.php");
}
?>