<?php
session_start();
require_once("../fonctions.php");

$mysqli = db_connexion();

include ('../nb_online.php');

// recupération config jeu
$dispo = config_dispo_jeu($mysqli);
$admin = admin_perso($mysqli, $_SESSION["id_perso"]);

if($dispo == '1' || $admin){

	if (isset($_SESSION["id_perso"])) {
		
		//recuperation des variables de sessions
		$id = $_SESSION["id_perso"];
		
		$sql = "SELECT pv_perso, type_perso FROM perso WHERE id_perso='$id'";
		$res = $mysqli->query($sql);
		$tpv = $res->fetch_assoc();
		
		$testpv = $tpv['pv_perso'];
		$type_p = $tpv['type_perso'];
		
		if ($testpv <= 0) {
			echo "<font color=red>Vous êtes mort...</font>";
		}
		else {
			$erreur = "<div class=\"erreur\">";
	
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
		<p align="center"><a href="jouer.php"> <input type="button" value="Retour au jeu"> </a></p>
	<?php
	if ($type_p != 6) {
	
		if (isset($_GET["id_compagnie"])){
			
			$verif = preg_match("#^[0-9]+$#i",$_GET["id_compagnie"]);
			
			if($verif){
				
				$id_compagnie = $_SESSION["id_compagnie"] = $_GET["id_compagnie"];
				
				// vérification que la compagnie existe
				$sql = "SELECT id_clan, id_parent from compagnies where id_compagnie='$id_compagnie'";
				$res = $mysqli->query($sql);
				$t_c = $res->fetch_assoc();
				
				$exist = $res->num_rows;
				$clan_compagnie = $t_c["id_clan"];
				$id_parent		= $t_c['id_parent'];
				
				// récupération du clan du perso
				$sql = "SELECT clan, idJoueur_perso FROM perso WHERE id_perso='$id'";
				$res = $mysqli->query($sql);
				$t_cp = $res->fetch_assoc();
				
				$clan_perso = $t_cp["clan"];
				$idJoueur_p = $t_cp["idJoueur_perso"];
				
				if($exist){
					
						
						if (isset($_GET["rejoindre"])) {
						
							// on souhaite rejoindre une compagnie
							if($_GET["rejoindre"] == "ok") {
								
								$sql = "SELECT * FROM perso_demande_anim WHERE id_perso='$idJoueur_p' AND type_demande='4'";
								$res = $mysqli->query($sql);
								$demande_cc = $res->num_rows;
								
								if (!$demande_cc) {
								
									$ok_n = 1;
									
									// verification que le perso est bien du meme camp que la compagnie				
									if($clan_perso == $clan_compagnie){
									
										// verification que le perso n'est pas deja dans la compagnie
										$sql = "SELECT id_perso FROM perso_in_compagnie WHERE id_compagnie='$id_compagnie'";
										$res = $mysqli->query($sql);
										
										while ($n = $res->fetch_assoc()){
											$id_n = $n["id_perso"];
											if ($id_n == $id) {
												$ok_n = 0;
												break;
											}
										}
										
										// verification que le perso n'est pas deja dans une compagnie ou en attente sur une autre
										$sql = "SELECT id_perso FROM perso_in_compagnie WHERE id_perso='$id'";
										$res = $mysqli->query($sql);
										$est_deja = $res->num_rows;
										
										if($est_deja){
											$ok_n = 0;
										}
										
										// Verification nombre dans la compagnie
										// recuperation des information sur la compagnie
										$sql = "SELECT genie_civil FROM compagnies WHERE id_compagnie=$id_compagnie";
										$res = $mysqli->query($sql);
										$sec = $res->fetch_assoc();
										$genie_compagnie		= $sec["genie_civil"];
										
										if ($genie_compagnie) {
											$nb_persos_compagnie_max = 60;
										} else {
											$nb_persos_compagnie_max = 80;
										}
										
										// Récupération nombre perso dans la compagnie
										$sql = "SELECT count(*) as nb_persos_compagnie FROM perso_in_compagnie WHERE id_compagnie=$id_compagnie AND (attenteValidation_compagnie='0' OR attenteValidation_compagnie='2')";
										$res = $mysqli->query($sql);
										$tab = $res->fetch_assoc();
										
										$nb_persos_compagnie = $tab["nb_persos_compagnie"];
										
										if ($nb_persos_compagnie >= $nb_persos_compagnie_max) {
											$ok_n = 0;
										}
										
										// si il peut postuler
										if($ok_n == 1) {
											
											// Verification que le type de perso peut postuler dans cette compagnie
											$sql = "SELECT type_perso FROM perso WHERE id_perso='$id'";
											$res = $mysqli->query($sql);
											$t_type = $res->fetch_assoc();
											
											$type_perso = $t_type["type_perso"];
											
											$sql = "SELECT * FROM compagnie_as_contraintes WHERE id_compagnie='$id_compagnie' AND contrainte_type_perso='$type_perso'";
											$res = $mysqli->query($sql);
											$nb_res = $res->num_rows;
											
											if ($nb_res >= 1) {
												
												// mise a jour de la table perso_in_compagnie
												$sql = "INSERT INTO perso_in_compagnie VALUES ('$id','$id_compagnie','10','1')";
												$mysqli->query($sql);
												
												echo "<center><font color='blue'>Vous venez de poser votre candidature dans une compagnie, vous devez attendre que le chef de compagnie ou le recruteur valide votre adhésion</font></center><br>";
												
											} else {
												echo "<center><font color='red'>Vous ne pouvez pas postuler dans cette compagnie, contraintes non respectées</font></center>";
											}
							
											echo "<center><a href='compagnie.php' class='btn btn-outline-secondary'> retour </a></center>";
										}
										else {
											if ($est_deja) {
												echo "<center><font color='red'>Vous êtes déjà inscrit dans une compagnie</font></center>";
											}
											else {
												echo "<center><font color='red'>La compagnie est déjà pleine, impossible de postuler tant qu'une place ne s'est pas liberée</font></center>";
											}
										}
									}
									else {
										echo "<center><font color='red'>Vous n'avez pas le droit de postuler dans une compagnie adverse...</font></center>";
									}
								}
								else {
									echo "<center><font color='red'>Vous ne pouvez pas postuler dans une caompagnie car vous avez effectué une demande de changement de camp</font></center>";
								}
							}
							
							if($_GET["rejoindre"] == "off") {
							
								// on souhaite quitter la compagnie
								// verification si le perso est le chef
								$sql = "SELECT id_perso, poste_compagnie FROM perso_in_compagnie WHERE id_compagnie=$id_compagnie AND id_perso=$id";
								$res = $mysqli->query($sql);
								$verif = $res->fetch_assoc();
								$chef = $verif["poste_compagnie"];
								
								// si c'est le chef de la compagnie
								if ($chef == 1) { 
									echo "<center><font color = red>Vous devez d'abords choisir un nouveau chef avant de quitter la compagnie</font></center><br>";
									echo "<center><a href='chef_compagnie.php?id_compagnie=".$id_compagnie."'>changer de chef</a></center>";
									echo "<center><a href='compagnie.php' class='btn btn-outline-secondary'> retour </a></center>";
								}
								else { 
									
									// Est-ce qu'il a une dette dans la compagnie ?
									$sql = "SELECT SUM(montant) as thune_en_banque FROM histobanque_compagnie 
													WHERE id_perso='$id' 
													AND id_compagnie='$id_compagnie'";
									$res = $mysqli->query($sql);
									$t = $res->fetch_assoc();
									
									$thune_en_banque = $t["thune_en_banque"];
									
									if ($thune_en_banque >= 0) {
										
										// MAJ demande de sortie de la compagnie 
										$sql = "UPDATE perso_in_compagnie SET attenteValidation_compagnie = '2' WHERE id_perso='$id'";
										$mysqli->query($sql);
									
										echo "<center><font color='blue'>Votre demande pour quitter la compagnie a bien été effectuée</font></center>";
										echo "<center><a href='compagnie.php' class='btn btn-outline-secondary'> retour </a></center>";
									}
									else {
										echo "<center><font color = red>Vous devez d'abords vous acquitter de vos dette avant de quitter la compagnie</font></center><br>";
										echo "<center><a href='compagnie.php' class='btn btn-outline-secondary'> retour </a></center>";
									}
								}
							}
						}
						else {
							// on souhaite juste avoir des infos sur la compagnie
							// recuperation des information sur la compagnie
							$sql = "SELECT id_compagnie, nom_compagnie, image_compagnie, resume_compagnie, description_compagnie, genie_civil, id_parent FROM compagnies WHERE id_compagnie=$id_compagnie";
							$res = $mysqli->query($sql);
							$sec = $res->fetch_assoc();
							
							$id_compagnie 			= $sec["id_compagnie"];
							$nom_compagnie 			= $sec["nom_compagnie"];
							$image_compagnie 		= $sec["image_compagnie"];
							$resume_compagnie 		= $sec["resume_compagnie"];
							$description_compagnie 	= $sec["description_compagnie"];
							$genie_compagnie		= $sec["genie_civil"];
							$id_parent				= $sec['id_parent'];
							
							if ($genie_compagnie) {
								$nb_persos_compagnie_max = 60;
							} else if (isset($id_parent)) { 
								$nb_persos_compagnie_max = 40;
							}							
							else {
								$nb_persos_compagnie_max = 80;
							}
							
							// Récupération nombre perso dans la compagnie
							$sql = "SELECT count(*) as nb_persos_compagnie FROM perso_in_compagnie WHERE id_compagnie=$id_compagnie AND (attenteValidation_compagnie='0' OR attenteValidation_compagnie='2')";
							$res = $mysqli->query($sql);
							$tab = $res->fetch_assoc();
							
							$nb_persos_compagnie = $tab["nb_persos_compagnie"];
							
							// affichage des information de la compagnie
							echo "<center><b>$nom_compagnie</b></center>";
							echo "<table border='1' class='table' width = 100%>";
							echo "	<tr>";
							echo "		<td width=40 height=40>";
							if ($image_compagnie != "0" && trim($image_compagnie) != "") {
								echo "<img src=\"".htmlspecialchars($image_compagnie)."\" width=\"40\" height=\"40\">";
							}
							echo "		</td>";
							echo "		<td>".bbcode(htmlentities(stripslashes($resume_compagnie)))."</td>";
							echo "		<td width=20%><center>Liste des membres (". $nb_persos_compagnie ."/".$nb_persos_compagnie_max.")</center></td>";
							echo "	</tr>";
							echo "	<tr>";
							echo "		<td></td>";
							echo "		<td>".bbcode(htmlentities(stripslashes($description_compagnie)))."</td>";
							echo "		<td>";
							
							// recuperation de la liste des membres de la compagnie
							$sql = "SELECT perso.id_perso, nom_perso, poste_compagnie, perso_as_grade.id_grade, nom_grade FROM perso, perso_in_compagnie, perso_as_grade, grades 
									WHERE perso_in_compagnie.id_perso=perso.ID_perso
									AND perso_as_grade.id_perso = perso.id_perso
									AND perso_as_grade.id_grade = grades.id_grade
									AND id_compagnie=$id_compagnie AND (attenteValidation_compagnie='0' OR attenteValidation_compagnie='2') 
									ORDER BY poste_compagnie, perso.id_perso";
							$res = $mysqli->query($sql);
							
							while ($membre = $res->fetch_assoc()) {
								
								$poste_compagnie 	= $membre["poste_compagnie"];
								$nom_membre 		= $membre["nom_perso"];
								$id_membre			= $membre["id_perso"];
								$id_grade			= $membre["id_grade"];
								$nom_grade			= $membre["nom_grade"];
								
								// cas particuliers grouillot
								if ($id_grade == 101) {
									$id_grade = "1.1";
								}
								if ($id_grade == 102) {
									$id_grade = "1.2";
								}
								
								if($poste_compagnie != 10){
									
									// recuperation du nom de poste
									$sql2 = "SELECT nom_poste FROM poste WHERE id_poste=$poste_compagnie";
									$res2 = $mysqli->query($sql2);
									$t_p = $res2->fetch_assoc();
									$nom_poste = $t_p["nom_poste"];
									
									echo "<img alt='".$nom_grade."' title='".$nom_grade."' src=\"../images/grades/" . $id_grade . ".gif\" width=25 height=25> ".$nom_membre." [<a href='evenement.php?infoid=".$id_membre."' target='_blank'>".$id_membre."</a>] ($nom_poste)<br />";
								}
								else
									echo "<img alt='".$nom_grade."' title='".$nom_grade."' src=\"../images/grades/" . $id_grade . ".gif\" width=25 height=25> ".$nom_membre." [<a href='evenement.php?infoid=".$id_membre."' target='_blank'>".$id_membre."</a>]<br />";
							}
							
							echo "		</td>";
							echo "	</tr>";
							echo "</table><br>";
							
							// verification que le perso n'est pas deja dans une compagnie ou en attente sur une autre
							$sql = "SELECT id_perso, id_compagnie FROM perso_in_compagnie WHERE id_perso='$id'";
							$res = $mysqli->query($sql);
							$est_deja = $res->num_rows;
							
							if(isset($_GET['voir_compagnie']) && $_GET['voir_compagnie'] == 'ok'){
								echo "";
							}
							else {
								if ($nb_persos_compagnie < $nb_persos_compagnie_max && !$est_deja) {
									echo "<center><a class='btn btn-outline-success' href='compagnie.php?id_compagnie=$id_compagnie&rejoindre=ok'> >>Rejoindre</a></center>";
								}
							}
							
							echo "<br>";
							echo "<center>";
							if ($est_deja) {
								echo "	<a href=\"compagnie.php?voir_compagnie=ok\" class='btn btn-outline-secondary'> retour liste compagnie </a> <a href=\"compagnie.php\" class='btn btn-outline-secondary'> retour compagnie </a>";
							} else {
								echo "	<a href=\"compagnie.php\" class='btn btn-outline-secondary'> retour liste compagnies </a> ";
							}
							echo "</center>";
						}
					
				}
				else {
					echo "<center><center><font color = 'red'>La compagnie demandé n'existe pas</font></center>";
				}
			}
			else {
				echo "<center><center><font color = 'red'>La compagnie demandé n'existe pas</font></center>";
			}
		}
		else {
			// si le perso souhaite voir la liste des compagnies
			if(isset($_GET['voir_compagnie']) && $_GET['voir_compagnie']=='ok'){
				
				echo "<br/><center><b><u>Liste des compagnies déjà existantes</u></b></center>";
				
				echo "<center><a class='btn btn-outline-info' href='compagnie.php'>Retour</a></center><br/>";
				
				// recuperation des compagnies existantes
				$sql = "SELECT id_compagnie, nom_compagnie, image_compagnie, resume_compagnie, description_compagnie 
						FROM compagnies, perso WHERE id_perso = $id AND compagnies.id_clan = perso.clan AND id_parent is NULL";
				$res = $mysqli->query($sql);
				
				echo "<table border='1' class='table' width = 100%>";
				
				while ($sec = $res->fetch_assoc()) {
					
					$id_compagnie 			= $sec["id_compagnie"];
					$nom_compagnie 			= $sec["nom_compagnie"];
					$image_compagnie 		= $sec["image_compagnie"];
					$resume_compagnie 		= $sec["resume_compagnie"];
					$description_compagnie 	= $sec["description_compagnie"];
							
					// creation des tableau avec les compagnies existantes
					echo "	<tr>";
					echo "		<td width=40 height=40>";
					if ($image_compagnie != "0" && trim($image_compagnie) != "") {
						echo "<img src=\"".htmlspecialchars($image_compagnie)."\" width=\"40\" height=\"40\">";
					}
					echo "		</td>";
					echo "		<td width=25%>$nom_compagnie</td>";
					echo "		<td>".bbcode(htmlentities(stripslashes($resume_compagnie)))."</td>";
					echo "		<td width=80><a class='btn btn-outline-info' href='compagnie.php?id_compagnie=$id_compagnie&voir_compagnie=ok'><center>Plus d'infos</center></a></td>";
					echo "	</tr>";
				}
				
				echo "</table>";
			}
			else {
				
				echo "<div align='center'><a class='btn btn-outline-info' href='compagnie.php?voir_compagnie=ok'>Voir les autres compagnies</a></div>";
				
				// verification si le perso appartient deja a une compagnie
				$sql = "SELECT id_compagnie, poste_compagnie FROM perso_in_compagnie WHERE id_perso = '$id' AND (attenteValidation_compagnie='0' OR attenteValidation_compagnie='2')";
				$res = $mysqli->query($sql);
				$c = $res->fetch_row();
				
				// il appartient a une compagnie
				if ($c != 0) {
					
					// recuperation de la compagnie a laquelle on appartient
					$id_compagnie 		= $c[0];
					$poste_compagnie	= $c[1];
					
					// recuperation des information sur la compagnie
					$sql = "SELECT id_compagnie, nom_compagnie, image_compagnie, resume_compagnie, description_compagnie, genie_civil, id_parent FROM compagnies WHERE id_compagnie=$id_compagnie";
					$res = $mysqli->query($sql);
					$sec = $res->fetch_assoc();
					
					$id_compagnie 			= $sec["id_compagnie"];
					$nom_compagnie 			= $sec["nom_compagnie"];
					$image_compagnie 		= $sec["image_compagnie"];
					$resume_compagnie 		= $sec["resume_compagnie"];
					$description_compagnie 	= $sec["description_compagnie"];
					$genie_compagnie		= $sec["genie_civil"];
					$id_parent				= $sec["id_parent"];
					
					if (isset($id_parent) && $id_parent != 0) {
						$titre_compagnie = "section";
					}
					else {
						$titre_compagnie = "compagnie";
					}
					
					echo "<div align='center'><a class='btn btn-outline-info' href='banque_compagnie.php?id_compagnie=$id_compagnie'>Banque de la ".$titre_compagnie."</a>";
					
					// verification si le perso est le chef de la compagnie
					$sql = "SELECT poste_compagnie FROM perso_in_compagnie WHERE id_perso=$id";
					$res = $mysqli->query($sql);
					$boss = $res->fetch_assoc();
					$poste_s = $boss["poste_compagnie"];
					
					// le perso a un poste
					if($poste_s != 10) {
						
						// c'est le tresorier
						if($poste_s == 1 || $poste_s == 3){ 
						
							// verification si quelqu'un a demande un emprunt
							$sql = "SELECT banque_compagnie.id_perso FROM banque_compagnie, perso_in_compagnie WHERE demande_emprunt='1' AND id_compagnie=$id_compagnie AND banque_compagnie.id_perso=perso_in_compagnie.id_perso";
							$res = $mysqli->query($sql);
							
							$nb = $res->num_rows;
							
							echo " <a class='btn btn-outline-primary' href='tresor_compagnie.php?id_compagnie=$id_compagnie'> Page tresorerie de la ".$titre_compagnie." ";
							if ($nb > 0) {
								echo "<span class='badge badge-pill badge-warning'>$nb</span>";
							}
							echo "</a>";
						}
						
						// c'est le recruteur
						if(!isset($id_parent) && ($poste_s == 4 || $poste_s == 1)){ 
						
							// on verifie si il y a des nouveau persos qui veulent integrer la compagnie
							$sql = "SELECT nom_perso, perso_in_compagnie.id_perso FROM perso_in_compagnie, perso 
									WHERE perso.ID_perso=perso_in_compagnie.id_perso AND id_compagnie=$id_compagnie AND attenteValidation_compagnie='1'";
							$res = $mysqli->query($sql);
							
							// nombre de persos en attente de validation pour rentrer
							$num_e = $res->num_rows; 
							
							// on verifie si il y a des nouveau persos qui veulent quitter la compagnie
							$sql = "SELECT nom_perso, perso_in_compagnie.id_perso FROM perso_in_compagnie, perso 
									WHERE perso.ID_perso=perso_in_compagnie.id_perso AND id_compagnie=$id_compagnie AND attenteValidation_compagnie='2'";
							$res = $mysqli->query($sql);
							
							// nombre de persos en attente pour quitter la compagnie
							$num_q = $res->num_rows; 
							
							$num_a = $num_e + $num_q;
							
							echo " <a class='btn btn-outline-primary' href='recrut_compagnie.php?id_compagnie=$id_compagnie'> Page de recrutement de la ".$titre_compagnie." ";
							if ($num_e > 0) {
								echo "<span class='badge badge-pill badge-success'>$num_e</span>";
							}
							if ($num_q > 0) {
								echo "<span class='badge badge-pill badge-danger'>$num_q</span>";
							}
							echo "</a>";
						}
						
						// c'est le diplomate
						if($poste_s == 5){ 
							echo " <a href='diplo_compagnie.php?id_compagnie=$id_compagnie' class='btn btn-outline-primary'> Page diplomatie de la ".$titre_compagnie."</a>";
						}
						
						// c'est le chef ou le sous-chef
						if($poste_s == 1 || $poste_s == 2) { 
							echo " <a class='btn btn-outline-danger' href='admin_compagnie.php?id_compagnie=$id_compagnie'> Page d'administration de la ".$titre_compagnie."</a>";
						}
					}
					
					echo "</div>";
					
					// Ordre compagnie
					$sql = "SELECT ordre FROM compagnie_ordre WHERE id_compagnie='$id_compagnie'";
					$res = $mysqli->query($sql);
					$t = $res->fetch_assoc();
					
					$ordre_compagnie = stripslashes($t['ordre']);
					
					if ($genie_compagnie) {
						$nb_persos_compagnie_max = 60;
					} else if(isset($id_parent)) {
						$nb_persos_compagnie_max = 40;
					} else {
						$nb_persos_compagnie_max = 80;
					}
					
					// Récupération nombre perso dans la compagnie
					$sql = "SELECT count(*) as nb_persos_compagnie FROM perso_in_compagnie WHERE id_compagnie=$id_compagnie AND (attenteValidation_compagnie='0' OR attenteValidation_compagnie='2')";
					$res = $mysqli->query($sql);
					$tab = $res->fetch_assoc();
					
					$nb_persos_compagnie = $tab["nb_persos_compagnie"];
						
					// affichage des information de la compagnie
					echo "<center><h2>$nom_compagnie</h2></center>";
					
					if (isset($ordre_compagnie) && $ordre_compagnie != null && trim($ordre_compagnie) != "") {
						echo "<div align='center' style='border: 1px dashed #CCC'>";
						echo "<h5>Ordres du jour</h5>";
						echo $ordre_compagnie;
						echo "</div>";
					}
					
					echo "<table border='1' class='table' width = 100%>";
					echo "	<tr>";
					echo "		<th width=40 height=40>";
					if ($image_compagnie != "0" && trim($image_compagnie) != "") {
						echo "<img src=\"".htmlspecialchars($image_compagnie)."\" width=\"40\" height=\"40\">";
					}
					echo "		</th>";
					echo "		<th style='text-align:center'>".bbcode(htmlentities(stripslashes($resume_compagnie)))."</th>";
					echo "		<th style='text-align:center' width=30%>Liste des membres  (". $nb_persos_compagnie ."/".$nb_persos_compagnie_max.")";
					if ($poste_compagnie == 1) {
						echo "<span style='text-align:right; float:right;padding-right:5px;'>Position</span>";
					}
					echo "		</th>";
					echo "	</tr>";
					echo "	<tr>";
					echo "		<td colspan='2'>".bbcode(htmlentities(stripslashes($description_compagnie)))."</td>";
					echo "		<td>";
						
					// recuperation de la liste des membres de la compagnie
					$sql = "SELECT perso.id_perso, nom_perso, poste_compagnie, perso_as_grade.id_grade, nom_grade, x_perso, y_perso FROM perso, perso_in_compagnie, perso_as_grade, grades
							WHERE perso_in_compagnie.id_perso=perso.ID_perso 
							AND perso_as_grade.id_perso = perso.id_perso
							AND perso_as_grade.id_grade = grades.id_grade
							AND id_compagnie=$id_compagnie AND (attenteValidation_compagnie='0' OR attenteValidation_compagnie='2') 
							ORDER BY poste_compagnie, perso.id_perso";
					$res = $mysqli->query($sql);
					
					while ($membre = $res->fetch_assoc()) {
						
						$nom_membre 		= $membre["nom_perso"];
						$poste_membre 		= $membre["poste_compagnie"];
						$id_membre			= $membre["id_perso"];
						$id_grade			= $membre["id_grade"];
						$nom_grade			= $membre["nom_grade"];
						$x_membre			= $membre["x_perso"];
						$y_membre			= $membre["y_perso"];
								
						// cas particuliers grouillot
						if ($id_grade == 101) {
							$id_grade = "1.1";
						}
						if ($id_grade == 102) {
							$id_grade = "1.2";
						}
						
						echo "<img alt='".$nom_grade."' title='".$nom_grade."' src=\"../images/grades/" . $id_grade . ".gif\" width=25 height=25> ".$nom_membre." [<a href='evenement.php?infoid=".$id_membre."' target='_blank'>".$id_membre."</a>]";
						
						if($poste_membre != 10){
							
							// recuperation du nom de poste
							$sql2 = "SELECT nom_poste FROM poste WHERE id_poste=$poste_membre";
							$res2 = $mysqli->query($sql2);
							$t_p = $res2->fetch_assoc();
							
							$nom_poste = $t_p["nom_poste"];
							
							echo " ($nom_poste)";
						}
						
						if ($poste_compagnie == 1) {
							echo "<span style='text-align:right; float:right;padding-right:5px;'> ".$x_membre."/".$y_membre."</span>";
						}
						
						echo "<br />";
					}
					
					echo "		</td>";
					echo "	</tr>";
					echo "</table><br>";
					
					$sql_sec = "SELECT * FROM compagnies WHERE id_parent='$id_compagnie'";
					$res_sec = $mysqli->query($sql_sec);
					$nb_sec	= $res_sec->num_rows;
					
					if ($nb_sec && !isset($_GET['voir_section'])) {
						echo "<center><a href='compagnie.php?voir_section=ok' class='btn btn-warning'>Voir les sections ";
						echo "<span class='badge badge-pill badge-success'>$nb_sec</span>";
						echo "</a></center>";
					}
					else if (isset($_GET['voir_section'])) {
						
						echo "<center><a href='compagnie.php' class='btn btn-warning'>Cacher les sections</a></center>";
						
						while ($t_sec = $res_sec->fetch_assoc()) {
							
							$id_section		= $t_sec['id_compagnie'];
							$nom_section	= $t_sec['nom_compagnie'];
							$image_section	= $t_sec['image_compagnie'];
							$resume_section	= $t_sec['resume_compagnie'];
							$desc_section	= $t_sec['description_compagnie'];
							
							// Récupération nombre perso dans la section
							$sql = "SELECT count(*) as nb_persos_section FROM perso_in_compagnie WHERE id_compagnie=$id_section AND (attenteValidation_compagnie='0' OR attenteValidation_compagnie='2')";
							$res = $mysqli->query($sql);
							$tab = $res->fetch_assoc();
							
							$nb_persos_section = $tab["nb_persos_section"];
								
							// affichage des information de la section
							echo "<center><h2>$nom_section</h2></center>";
							echo "<table border='1' class='table' width = 100%>";
							echo "	<tr>";
							echo "		<th width=40 height=40>";
							if ($image_section != "0" && trim($image_section) != "") {
								echo "<img src=\"".htmlspecialchars($image_section)."\" width=\"40\" height=\"40\">";
							}
							echo "		</th>";
							echo "		<th style='text-align:center'>".bbcode(htmlentities(stripslashes($resume_section)))."</th>";
							echo "		<th style='text-align:center' width=30%>Liste des membres  (". $nb_persos_section ."/40)";
							if ($poste_compagnie == 1) {
								echo "<span style='text-align:right; float:right;padding-right:5px;'>Position</span>";
							}
							echo "		</th>";
							echo "	</tr>";
							echo "	<tr>";
							echo "		<td colspan='2'>".bbcode(htmlentities(stripslashes($desc_section)))."</td>";
							echo "		<td>";
								
							// recuperation de la liste des membres de la compagnie
							$sql = "SELECT perso.id_perso, nom_perso, poste_compagnie, perso_as_grade.id_grade, nom_grade, x_perso, y_perso FROM perso, perso_in_compagnie, perso_as_grade, grades
									WHERE perso_in_compagnie.id_perso=perso.ID_perso 
									AND perso_as_grade.id_perso = perso.id_perso
									AND perso_as_grade.id_grade = grades.id_grade
									AND id_compagnie=$id_section AND (attenteValidation_compagnie='0' OR attenteValidation_compagnie='2') 
									ORDER BY poste_compagnie, perso.id_perso";
							$res = $mysqli->query($sql);
							
							while ($membre = $res->fetch_assoc()) {
								
								$nom_membre 		= $membre["nom_perso"];
								$poste_membre 		= $membre["poste_compagnie"];
								$id_membre			= $membre["id_perso"];
								$id_grade			= $membre["id_grade"];
								$nom_grade			= $membre["nom_grade"];
								$x_membre			= $membre["x_perso"];
								$y_membre			= $membre["y_perso"];
										
								// cas particuliers grouillot
								if ($id_grade == 101) {
									$id_grade = "1.1";
								}
								if ($id_grade == 102) {
									$id_grade = "1.2";
								}
								
								echo "<img alt='".$nom_grade."' title='".$nom_grade."' src=\"../images/grades/" . $id_grade . ".gif\" width=25 height=25> ".$nom_membre." [<a href='evenement.php?infoid=".$id_membre."' target='_blank'>".$id_membre."</a>]";
								
								if($poste_membre != 10){
									
									// recuperation du nom de poste
									$sql2 = "SELECT nom_poste FROM poste WHERE id_poste=$poste_membre";
									$res2 = $mysqli->query($sql2);
									$t_p = $res2->fetch_assoc();
									
									$nom_poste = $t_p["nom_poste"];
									
									echo " ($nom_poste)";
								}
								
								if ($poste_compagnie == 1) {
									echo "<span style='text-align:right; float:right;padding-right:5px;'> ".$x_membre."/".$y_membre."</span>";
								}
								
								echo "<br />";
							}
							
							echo "		</td>";
							echo "	</tr>";
							echo "</table><br>";
						}
					}
					
					echo "<br/><center><a class='btn btn-danger' href='compagnie.php?id_compagnie=$id_compagnie&rejoindre=off'"?> OnClick="return(confirm('êtes vous sûr de vouloir quitter la compagnie ?'))" <?php echo"><b>Demander à quitter la ".$titre_compagnie."</b></a></center>";
				}
				else {
				
					// verification si le perso est en attente de validation
					$sql = "SELECT id_compagnie FROM perso_in_compagnie WHERE id_perso = '$id' and attenteValidation_compagnie='1'";
					$res = $mysqli->query($sql);
					$c = $res->fetch_row();
				
					if(isset($_GET['annuler']) && $_GET['annuler']=='ok'){
						
						$sql ="delete from perso_in_compagnie where id_perso='$id'";
						$mysqli->query($sql);
				
						echo "Vous venez d'annuler votre demande d'adhésion <br />";
						echo "<center><a href='compagnie.php' class='btn btn-outline-secondary'> retour </center>";
					}
					else{
						// en attente de validation
						if ($c != 0) { 
							echo "<div align='center'>Vous êtes en attente de validation pour une compagnie";
							echo "<br/><a class='btn btn-danger' href='compagnie.php?annuler=ok'>annuler sa candidature</a>";
						}
						else {
					
							// il n'appartient a aucune compagnie
							
							// A t-il demandé la création d'une compagie ?
							$sql = "SELECT count(id_em_creer_compagnie) as verif_creer_comp FROM em_creer_compagnie WHERE id_perso='$id'";
							$res = $mysqli->query($sql);
							$t = $res->fetch_assoc();
							
							$verif_creer_comp = $t["verif_creer_comp"];
							
							if ($verif_creer_comp > 0) {
								echo "<center>Vous avez demandé la création d'un nouvelle compagnie, vous devez attendre la délibération de votre état major</a></center>";
							}
							else {

								echo "<center><a class='btn btn-primary btn-lg btn-block' href='creer_compagnie.php'>Créer une nouvelle compagnie</a></center>";
								
								echo "<br/><center><b><u>Liste des compagnies déjà existants</u></b></center><br/>";
								
								// recuperation des compagnies existantes dans lesquels il peut postuler
								$sql = "SELECT compagnies.id_compagnie, nom_compagnie, image_compagnie, resume_compagnie, description_compagnie, compagnies.id_parent 
										FROM compagnies, perso, compagnie_as_contraintes
										WHERE id_perso = $id 
										AND compagnies.id_compagnie = compagnie_as_contraintes.id_compagnie
										AND compagnies.id_clan = perso.clan
										AND compagnie_as_contraintes.contrainte_type_perso = perso.type_perso
										AND compagnies.id_parent is NULL";
								$res = $mysqli->query($sql);
								
								echo "<table border='1' class='table' width = 100%>";
								
								while ($sec = $res->fetch_assoc()) {
									
									$id_compagnie 			= $sec["id_compagnie"];
									$nom_compagnie 			= $sec["nom_compagnie"];
									$image_compagnie 		= $sec["image_compagnie"];
									$resume_compagnie 		= $sec["resume_compagnie"];
									$description_compagnie 	= $sec["description_compagnie"];
									$id_parent				= $sec["id_parent"];
								
									// creation des tableau avec les compagnies existantes
									echo "	<tr>";
									echo "		<td width=40 height=40>";
									if ($image_compagnie != "0" && trim($image_compagnie) != "") {
										echo "<img src=\"".htmlspecialchars($image_compagnie)."\" width=\"40\" height=\"40\">";
									}
									echo "		</td>";
									echo "		<td width=25%>$nom_compagnie</td>";
									echo "		<td>".bbcode(htmlentities(stripslashes($resume_compagnie)))."</td>";
									echo "		<td width=80><a class='btn btn-outline-info' href='compagnie.php?id_compagnie=$id_compagnie'><center>Plus d'infos</center></a></td>";
									echo "		<td width=100><a class='btn btn-outline-success' href='compagnie.php?id_compagnie=$id_compagnie&rejoindre=ok'><center> >>Rejoindre</center></a></td>";
									echo "	</tr>";
								}
								
								echo "</table>";
							}
						}
					}
				}
			}
		}
	}
	else {
		echo "<center><font color='red'>Les chiens ne peuvent pas accèder à cette page.</font></center>";
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
	}
	else {
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