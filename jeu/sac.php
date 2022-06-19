<?php
session_start();
require_once("../fonctions.php");

$mysqli = db_connexion();

include ('../nb_online.php');

// recupération config jeu
$dispo = config_dispo_jeu($mysqli);
$admin = admin_perso($mysqli, $_SESSION["id_perso"]);

if($dispo == '1' || $admin){
	
	if(isset($_SESSION["id_perso"])){
		
		//recuperation des variables de sessions
		$id = $_SESSION["id_perso"];
		
		// recuperation des donnees sur le perso
		$sql = "SELECT pv_perso, pa_perso, type_perso, bourre_perso FROM perso WHERE id_perso='$id'";
		$res = $mysqli->query($sql);
		$tpv = $res->fetch_assoc();
		
		$testpv = $tpv['pv_perso'];
		$testpa = $tpv['pa_perso'];
		$type_p = $tpv['type_perso'];
		$test_b = $tpv['bourre_perso'];
		
		// On verifie que le perso soit toujours vivant
		if ($testpv <= 0) {
			echo "<font color=red>Vous avez été capturé...</font>";
		}
		else {
			
			$mess = "";
			$mess_err = "";
			
			// on souhaite supprimer un ticket
			if(isset($_POST['delete_ticket_hidden']) && $_POST['delete_ticket_hidden'] != "") {
				
				$dest_ticket_to_delete = $_POST['delete_ticket_hidden'];
				
				$verif = preg_match("#^[0-9]*[0-9]$#i","$dest_ticket_to_delete");
				
				if ($verif) {
					
					$sql = "DELETE FROM perso_as_objet WHERE id_objet='1' AND id_perso='$id' AND capacite_objet='$dest_ticket_to_delete' LIMIT 1";
					$mysqli->query($sql);
					
					$mess .= "Le ticket à destination de ".$dest_ticket_to_delete." a bien été supprimé de votre inventaire";
				}
				else {
					// triche
					$mess_err .= "Données envoyées incorrectes...";
				}
			}
			
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
								$sql = "SELECT * FROM objet WHERE id_objet='$id_o'";
								$res = $mysqli->query($sql);
								$bonus_o = $res->fetch_assoc();
								
								$nom_ob 			= $bonus_o["nom_objet"];
								$bonusPerception 	= $bonus_o["bonusPerception_objet"];
								$bonusRecup 		= $bonus_o["bonusRecup_objet"];
								$bonusPv 			= $bonus_o["bonusPv_objet"];
								$bonusPm 			= $bonus_o["bonusPm_objet"];
								$bonusPa			= $bonus_o["bonusPA_objet"];
								$coutPa 			= $bonus_o["coutPa_objet"];
								$poids 				= $bonus_o["poids_objet"];
								$type_o 			= $bonus_o["type_objet"];
								
								if ($test_b >= 2 && $nom_ob == "Whisky") {
									$mess_err .= "Vous ne pouvez pas consommer plus de Whisky ce tour ci";
								}
								else if ($type_o == 'N') {
										
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
												SET bonusRecup_perso=bonusRecup_perso+$bonusRecup
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
									
									// MAJ perso
									$sql_c = "UPDATE perso SET pa_perso = pa_perso - 1, charge_perso=charge_perso-$poids WHERE id_perso='$id'";
									$mysqli->query($sql_c);
								}
								else if ($type_o == 'E') {
									
									// On verifie si on peut équiper cet obket
									$sql = "SELECT equip_objet FROM perso_as_objet JOIN objet_as_type_unite ON perso_as_objet.id_objet = objet_as_type_unite.id_objet WHERE id_perso='$id' 
										AND perso_as_objet.id_objet='$id_o' AND id_type_unite=$type_p";
									$res = $mysqli->query($sql);
									if ($res->num_rows) {

										$t = $res->fetch_assoc();
										$is_equipe = $t["equip_objet"];

										if (isset($_GET['desequip']) && $_GET['desequip'] == "ok") {

											if ($is_equipe) {

												// On enleve l'objet
												$sql = "UPDATE perso_as_objet SET equip_objet='0' WHERE id_perso='$id' AND id_objet='$id_o' LIMIT 1";
												$mysqli->query($sql);

												// MAJ perso
												$sql_c = "UPDATE perso SET pa_perso = pa_perso - 1";

												if ($bonusPerception != 0) {
													$sql_c .= ", bonusPerception_perso=bonusPerception_perso-".$bonusPerception;
												}

												if ($bonusPa != 0) {
													$sql_c .= ", bonusPA_perso=bonusPA_perso-".$bonusPa;
												}

												if ($bonusPm != 0) {
													$sql_c .= ", bonusPM_perso=bonusPM_perso-".$bonusPm;
												}

												$sql_c .= " WHERE id_perso='$id'";
												$mysqli->query($sql_c);

											}
											else {
												// Tentative de triche
												$text_triche = "Tentative deséquiper objet non équipé !";

												$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id', '$text_triche')";
												$mysqli->query($sql);
											}
										}
										else {

											if (!$is_equipe) {

												// On équipe l'objet
												$sql = "UPDATE perso_as_objet SET equip_objet='1' WHERE id_perso='$id' AND id_objet='$id_o' LIMIT 1";
												$mysqli->query($sql);

												// MAJ perso
												$sql_c = "UPDATE perso SET pa_perso = pa_perso - 1";

												if ($bonusPerception != 0) {
													$sql_c .= ", bonusPerception_perso=bonusPerception_perso+".$bonusPerception;
												}

												if ($bonusPa != 0) {
													$sql_c .= ", bonusPA_perso=bonusPA_perso+".$bonusPa;
												}

												if ($bonusPm != 0) {
													$sql_c .= ", bonusPM_perso=bonusPM_perso+".$bonusPm;
												}

												$sql_c .= " WHERE id_perso='$id'";
												$mysqli->query($sql_c);
											}
											else {
												// Tentative de triche
												$text_triche = "Tentative équiper objet déjà équipé !";

												$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id', '$text_triche')";
												$mysqli->query($sql);

											}
										}
									}
								}
								
								header("location:sac.php");
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
						$mess_err .= "Impossible de consommer / équiper cet objet !";
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
		<link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
	</head>
	
	<body>
	<?php
			// recuperation du nombre d'objet que possede le perso
			$sql = "SELECT COUNT(id_objet) FROM perso_as_objet WHERE id_perso='$id'";
			$res = $mysqli->query($sql);
			$t_nb_objets = $res->fetch_row();
			
			$nb_objets = $t_nb_objets[0];
			
			// recuperation du nombre d'armes non équipées que possede le perso
			$sql = "SELECT COUNT(id_arme) FROM perso_as_arme WHERE id_perso='$id' AND est_portee='0'";
			$res = $mysqli->query($sql);
			$t_nb_armes_non_equip = $res->fetch_row();
			
			$nb_armes_non_equip = $t_nb_armes_non_equip[0];
			
			$nb_dans_sac = $nb_objets + $nb_armes_non_equip;
			
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
		<table border=0 class='table'>
			<tr>
				<td>
					<table border=1 class='table'>
						<tr>
							<td align=center width=25%><img src="../images/<?php echo $image_sac; ?>"><p align="center"><a href="jouer.php"> <input type="button" value="Retour au jeu"> </a></p></td>
							<td width=75%>
								<center><h2>Mon sac</h2>
								<p>Le sac vous permet de transporter des objets et de les utiliser.<br>Vous possédez <b><?php echo $nb_dans_sac; ?></b> objet<?php if($nb_dans_sac > 1){echo "s";} ?> dans votre sac.</p>
								<?php 
								echo "<p><u><b>Charge :</b></u> ";
								if($charge_perso >= $chargeMax_reel){
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
					<table border=1 class='table'>
						<tr>
							<th width='25%'><center>objet</center></th><th width='50%'><center>description</center></th><th width='25%'><center>nombre</center></th>
						</tr>
			<?php
			
			// recuperation du nombre de type d'objets que possede le perso
			$sql = "SELECT DISTINCT id_objet FROM perso_as_objet WHERE id_perso='$id' ORDER BY id_objet";
			$res = $mysqli->query($sql);
			$nb_obj = $res->num_rows;
			
			while ($t_obj = $res->fetch_assoc()){
			
				// id de l'objet
				$id_obj = $t_obj["id_objet"];
				
				// recuperation des carac de l'objet
				$sql1 = "SELECT nom_objet, poids_objet, description_objet, type_objet FROM objet WHERE id_objet='$id_obj'";
				$res1 = $mysqli->query($sql1);
				$t_o = $res1->fetch_assoc();
				
				$nom_o 			= $t_o["nom_objet"];
				$poids_o 		= $t_o["poids_objet"];
				$description_o 	= $t_o["description_objet"];
				$type_o			= $t_o["type_objet"];
				
				// recuperation du nombre d'objet de ce type que possede le perso
				$sql2 = "SELECT id_objet, capacite_objet FROM perso_as_objet WHERE id_perso='$id' AND id_objet='$id_obj'";
				$res2 = $mysqli->query($sql2);
				$nb_o = $res2->num_rows;
				
				// calcul poids
				$poids_total_o = $poids_o * $nb_o;
				
				// affichage
				echo "<tr>";
				echo "	<td align='center'><img class='img-fluid' src=\"../images/objets/objet".$id_obj.".png\"></td>";
				echo "	<td align='center'><font color=green><b>".$nom_o."</b></font><br>".stripslashes($description_o)."</td>";
				echo "	<td align='center'>Vous possédez <b>".$nb_o."</b> ".$nom_o."";
				if($nb_o > 1){ 
					echo "s";
				}
				
				// Tickets de train
				if ($type_o == 'T') {
					
					echo "<br /><b>Destinations : </b><br />";
					
					while ($t_o = $res2->fetch_assoc()) {
						
						$destination = $t_o['capacite_objet'];
						
						if (trim($destination) == "") {
							echo "- Ticket non valide - "; 
						}
						else {
							echo "<a class='btn btn-primary' style='height:38px;' href='evenement.php?infoid=".$destination."'>".$destination."</a>";
							echo "<button class='btn btn-danger' style='height:38px;' type='button' data-toggle='modal' data-target=\"#modalConfirm$destination\"><i class='fa fa-trash'></i></button><br />";
							?>
							<!-- Modal -->
							<form method="post" action="sac.php">
								<div class="modal fade" id="modalConfirm<?php echo $destination; ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
									<div class="modal-dialog modal-dialog-centered" role="document">
										<div class="modal-content">
											<div class="modal-header">
												<h5 class="modal-title" id="exampleModalCenterTitle">Supprimer le ticket à destination de <?php echo $destination; ?> ?</h5>
												<button type="button" class="close" data-dismiss="modal" aria-label="Close">
													<span aria-hidden="true">&times;</span>
												</button>
											</div>
											<div class="modal-body">
												Êtes-vous sûr de vouloir supprimer le ticket à destination de <?php echo $destination; ?> ?
												<input type='hidden' name='delete_ticket_hidden' value='<?php echo $destination; ?>'>
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
					}
				}
				
				if($type_o == 'N'){
					if ($test_b >= 2 && $id_obj == 3) {
						echo "<br /><font color='red'>Vous ne pouvez plus consommer de Whisky ce tour-ci</font>";
					}
					else {
						echo "<br /><a class='btn btn-outline-success' href=\"sac.php?id_obj=".$id_obj."\">utiliser (cout : 1 PA)</a>";
					}
				}
				
				// Est ce que le perso est déjà équipé de cet objet ?
				$sql2 = "SELECT * FROM perso_as_objet JOIN objet_as_type_unite ON perso_as_objet.id_objet = objet_as_type_unite.id_objet WHERE id_perso='$id' AND perso_as_objet.id_objet='$id_obj' AND id_type_unite=$type_p";
				$res2 = $mysqli->query($sql2);
				if ($res2->num_rows) {
					$t2 = $res2->fetch_assoc();
					$is_equipe = $t2["equip_objet"];

					if($type_o == 'E' && !$is_equipe){
						echo "<br /><a class='btn btn-outline-primary' href=\"sac.php?id_obj=".$id_obj."\">équiper (cout : 1 PA)</a>";
					}

					if ($is_equipe) {
						echo "<br /><b>Vous êtes équipé de cet objet</b>";
						echo "<br /><a class='btn btn-outline-danger' href=\"sac.php?id_obj=".$id_obj."&desequip=ok\">enlever (cout : 1 PA)</a>";
					}
				}
				
				echo "<br /><u>Poids total :</u> <b>$poids_total_o</b></td>";
				echo "</tr>";
			}
			
			// Récupération des armes non équipées
			$sql = "SELECT DISTINCT id_arme FROM perso_as_arme WHERE id_perso='$id' AND est_portee='0' ORDER BY id_arme";
			$res = $mysqli->query($sql);
			$nb_arme = $res->num_rows;
			
			while ($t_arme = $res->fetch_assoc()){
				
				// id de l'arme
				$id_arme = $t_arme["id_arme"];
				
				// recuperation des carac de l'objet
				$sql1 = "SELECT nom_arme, poids_arme, description_arme, image_arme FROM arme WHERE id_arme='$id_arme'";
				$res1 = $mysqli->query($sql1);
				$t_a = $res1->fetch_assoc();
				
				$nom_a 			= $t_a["nom_arme"];
				$poids_a 		= $t_a["poids_arme"];
				$description_a 	= $t_a["description_arme"];
				$image_a		= $t_a["image_arme"];
				
				// recuperation du nombre d'armes de ce type que possede le perso
				$sql2 = "SELECT id_arme FROM perso_as_arme WHERE id_perso='$id' AND id_arme='$id_arme' AND est_portee='0'";
				$res2 = $mysqli->query($sql2);
				$nb_a = $res2->num_rows;
				
				// calcul poids
				$poids_total_a = $poids_a * $nb_a;
				
				$sql_u = "SELECT nom_unite FROM type_unite, arme_as_type_unite
							WHERE type_unite.id_unite = arme_as_type_unite.id_type_unite
							AND arme_as_type_unite.id_arme = '$id_arme'";
				$res_u = $mysqli->query($sql_u);
				$liste_unite = "";
				while ($t_u = $res_u->fetch_assoc()) {
					$nom_unite = $t_u["nom_unite"];
					
					if ($liste_unite != "") {
						$liste_unite .= " / ";
					}
					$liste_unite .= $nom_unite;
				}
				
				// affichage
				echo "<tr>";
				echo "	<td align='center'><img class='img-fluid' src=\"../images/armes/".$image_a."\"></td>";
				echo "	<td align='center'><font color=green><b>".$nom_a."</b></font><br>Arme utilisable pour les unités suivante : <b>".$liste_unite."</b><br />".stripslashes($description_a)."</td>";
				echo "	<td align='center'>Vous possédez <b>".$nb_a."</b> ".$nom_a."";
				if($nb_a > 1){ 
					echo "s";
				}
				
				echo "<br /><u>Poids total :</u> <b>$poids_total_a</b></td>";
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
		<script>
		$(function () {
			$('[data-toggle="tooltip"]').tooltip();
		});
		</script>
	
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
	
	header("Location:../index2.php");
}
?>
