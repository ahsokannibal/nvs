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
			echo "<font color=red>Vous avez été capturé...</font>";
		}
		else {
			
			$mess = "";
			$mess_err = "";
			
			// on souhaite utiliser un objet
			if(isset($_GET["id_obj"]) && $_GET["id_obj"] != ""){
				
				// On recupere l'identifiant de l'objet
				$id_o = $_GET["id_obj"];
				
				// On verifie que l'identifiant soit bien un nombre positif
				$verif = preg_match("#^[0-9]*[0-9]$#i","$id_o");
				
				if($verif && $id_o > 0) {
					
					// On verifie que l'objet soit bien utilisable
					if($id_o != 1){
						// ok
						//verification que le perso possede bien cet objet
						$sql = "SELECT id_objet FROM perso_as_objet WHERE id_perso='$id' AND id_objet='$id_o'";
						$res = $mysqli->query($sql);
						$ok = $res->num_rows;
						
						// possede plus de 0 objets
						if($ok) {
							
							// On verifie que le perso possede bien 1 pa pour utiliser l'objet
							if($testpa >= 1){
							
								// recuperation des effets de l'objet
								$sql = "SELECT nom_objet, bonusPerception_objet, bonusRecup_objet, bonusPv_objet, bonusPm_objet, coutPa_objet, poids_objet, type_objet FROM objet WHERE id_objet='$id_o'";
								$res = $mysqli->query($sql);
								$bonus_o = $res->fetch_assoc();
								
								$nom_ob 			= $bonus_o["nom_objet"];
								$bonusPerception 	= $bonus_o["bonusPerception_objet"];
								$bonusRecup 		= $bonus_o["bonusRecup_objet"];
								$bonusPv 			= $bonus_o["bonusPv_objet"];
								$bonusPm 			= $bonus_o["bonusPm_objet"];
								$coutPa 			= $bonus_o["coutPa_objet"];
								$poids 				= $bonus_o["poids_objet"];
								$type_o 			= $bonus_o["type_objet"];
										
								// on supprime l'objet de l'inventaire
								$sql = "DELETE FROM perso_as_objet WHERE id_perso='$id' AND id_objet='$id_o' LIMIT 1";
								$mysqli->query($sql);
										
								// on recupere les pv et autres donnees du perso
								$sql = "SELECT pv_perso, pvMax_perso, recup_perso, bonusRecup_perso FROM perso WHERE id_perso='$id'";
								$res = $mysqli->query($sql);
								$t_p = $res->fetch_assoc();
								
								$pv_p 	= $t_p["pv_perso"];
								$pvM_p 	= $t_p["pvMax_perso"];
								$rec_p 	= $t_p["recup_perso"];
								$br_p 	= $t_p["bonusRecup_perso"];
									
								// si l'objet donne des bonus
								if($bonusRecup) { 
										
									// on applique les effets de l'objet sur le perso
									$sql = "UPDATE perso 
											SET pa_perso=pa_perso-1, bonusRecup_perso=bonusRecup_perso+$bonusRecup
											WHERE id_perso='$id'";
									$mysqli->query($sql);
										
									// Affichage 
									$mess .= "Vous avez utilisé ".$nom_ob."<br>";
									
									if ($bonusRecup) {
										$mess .= "Votre recuperation passe de ".$rec_p+$br_p." à ";
										$mess .= $rec_p+$br_p+$bonusRecup."<br />";
									}										
								}
								
								if ($bonusPerception < 0) {
									// le perso est bourre
									$sql = "UPDATE perso SET bourre_perso=bourre_perso+1 WHERE id_perso='$id'";
									$mysqli->query($sql);
									
									$mess .= "Votre perception en prend un coup temporairement : Perception ".$bonusPerception;
								}
								
								// MAJ charge perso
								$sql_c = "UPDATE perso SET charge_perso=charge_perso-$poids WHERE id_perso='$id'";
								$mysqli->query($sql_c);
							}
							else {
								$mess_err .= "Vous n'avez pas assez de PA, l'utilisation d'un objet coute 1 PA.";
							}
						}
						else {
							$mess_err .= "Vous ne possédez pas/plus cet objet...";
						}
					}
					else {
						$mess_err .= "Impossible de consommer cet objet !";
					}
				}
				else {
					$mess_err .= "Il ne faut pas rentrer n'importe quoi dans la barre d'adresse...";
				}
			}
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
	<?php
			// recuperation du nombre d'objet que possede le perso
			$sql = "SELECT COUNT(id_objet) FROM perso_as_objet WHERE id_perso='$id'";
			$res = $mysqli->query($sql);
			$t_nb_objets = $res->fetch_row();
			
			$nb_objets = $t_nb_objets[0];
			
			// recuperation de la thune que possede le perso
			$sql = "SELECT or_perso, charge_perso, chargeMax_perso, clan FROM perso WHERE id_perso='$id'";
			$res = $mysqli->query($sql);
			$t_or = $res->fetch_assoc();
			
			$or_p 				= $t_or["or_perso"];
			$charge_perso 		= $t_or["charge_perso"];
			$chargeMax_perso 	= $t_or["chargeMax_perso"];
			$camp_perso			= $t_or["clan"];
			
			$chargeMax_reel 	= $chargeMax_perso;
			
			if ($camp_perso == 1) {
				$image_sac = "sac_nord.png";
			}
			else if ($camp_perso == 2) {
				$image_sac = "sac_sud.png";
			}
			else {
				$image_sac = "";
			}
			
			
	?>
		<table border=0 width=100%>
			<tr>
				<td>
					<table border=1 width=100%>
						<tr>
							<td align=center width=25%><img src="../images/<?php echo $image_sac; ?>"><p align="center"><input type="button" value="Fermer mon sac" onclick="window.close()"></p></td>
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
								<img src="../images/or.png" align="middle">Vous possédez <b><?php echo $or_p; ?></b> thune<?php if($or_p > 1){echo "s";}?><br>
								</center>
							</td>
						</tr>
					</table>
				</td>
			</tr>
			
			<tr>
				<td align='center'>
				<?php
				if (trim($mess) != "") {
					echo "<font color='blue'>" . $mess . "</font>";
				}
				
				if (trim($mess_err) != "") {
					echo "<font color='blue'>" . $mess_err . "</font>";
				}
				?>
				</td>
			</tr>
			
			<tr>
				<td>
					<table border=1 width=100%>
						<tr>
							<th width='25%'><center>objet</center></th><th width='50%'><center>description</center></th><th width='25%'><center>nombre</center></th>
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
					$t_o = $res1->fetch_assoc();
					
					$nom_o 			= $t_o["nom_objet"];
					$poids_o 		= $t_o["poids_objet"];
					$description_o 	= $t_o["description_objet"];
					
					// recuperation du nombre d'objet de ce type que possede le perso
					$sql2 = "SELECT id_objet FROM perso_as_objet WHERE id_perso='$id' AND id_objet='$id_obj'";
					$res2 = $mysqli->query($sql2);
					$nb_o = $res2->num_rows;
					
					// calcul poids
					$poids_total_o = $poids_o * $nb_o;
					
					// affichage
					echo "<tr>";
					echo "	<td align='center'><img src=\"../images/objets/objet".$id_obj.".png\"></td>";
					echo "	<td align='center'><font color=green><b>".$nom_o."</b></font><br>".stripslashes($description_o)."</td>";
					echo "	<td align='center'>Vous possédez <b>".$nb_o."</b> ".$nom_o."";
					if($nb_o > 1 && $id_obj != 6 && $id_obj != 7){ 
						echo "s";
					}
					if($id_obj != 1){
						echo "<br /><a href=\"sac.php?id_obj=".$id_obj."\">utiliser (cout : 1 PA)</a>";
					}
					echo "<br /><u>Poids total :</u> <b>$poids_total_o</b></td>";
					echo "</tr>";
			}
			?>
					</table>
				</td>
			</tr>
		</table>
	
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