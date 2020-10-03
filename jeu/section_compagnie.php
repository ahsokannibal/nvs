<?php
session_start();
require_once("../fonctions.php");

$mysqli = db_connexion();

include ('../nb_online.php');
include ('../forum/config.php');

if (@$_SESSION["id_perso"]) {
	
	//recuperation des varaibles de sessions
	$id = $_SESSION["id_perso"];
	
	$sql = "SELECT pv_perso, clan FROM perso WHERE id_perso='$id'";
	$res = $mysqli->query($sql);
	$tpv = $res->fetch_assoc();
	
	$testpv 	= $tpv['pv_perso'];
	$id_camp 	= $tpv['clan'];
	
	if ($testpv <= 0) {
		echo "<font color=red>Vous êtes mort...</font>";
	}
	else {
		
		if(isset($_GET["id_compagnie"])) {
			
			$id_compagnie = $_GET["id_compagnie"];
			
			$verif1 = preg_match("#^[0-9]+$#i",$id_compagnie);
			
			if($verif1){
			
				// verification genie civil
				$sql = "SELECT genie_civil FROM compagnies WHERE id_compagnie='$id_compagnie'";
				$res = $mysqli->query($sql);
				$t = $res->fetch_assoc();
				
				$genie_compagnie	= $t['genie_civil'];
				
				if (!$genie_compagnie) {
			
					// verification que le perso est bien le chef de la compagnie (anti-triche)
					$sql = "SELECT poste_compagnie FROM perso_in_compagnie WHERE id_perso=$id AND id_compagnie=$id_compagnie";
					$res = $mysqli->query($sql);
					$ch = $res->fetch_assoc();
					
					$ok_chef 			= $ch["poste_compagnie"];
					
					if($ok_chef == 1) {
			
						$mess_err 	= "";
						$mess		= "";
						
						if (isset($_POST['creation_section'])) {
							if (isset($_POST['nomSection']) && trim($_POST['nomSection']) != "" 
									&& isset($_POST['liste_perso_chef_section']) && trim($_POST['liste_perso_chef_section']) != "") {
								
								$nom_nouvelle_section 		= addslashes($_POST['nomSection']);
								$id_chef_nouvelle_section	= $_POST['liste_perso_chef_section'];
								
								$verif_id = preg_match("#^[0-9]+$#i",$id_chef_nouvelle_section);
								
								if ($verif_id) {
									
									// On vérifie que l'id du chef de section correspond bien à un membre de la compagnie mère
									$sql = "SELECT * FROM perso_in_compagnie WHERE id_perso='$id_chef_nouvelle_section' AND id_compagnie='$id_compagnie'";
									$res = $mysqli->query($sql);
									$verif_appartient_compagnie = $res->num_rows;
									
									if ($verif_appartient_compagnie) {
										
										// On vérifie que le nom de la section n'est pas déjà utilisé
										$sql = "SELECT * FROM compagnies WHERE nom_compagnie='$nom_nouvelle_section'";
										$res = $mysqli->query($sql);
										$verif_nom_exist = $res->num_rows;
										
										if (!$verif_nom_exist) {
											// verification taille nom + caractères spéciaux
											if (strlen($nom_nouvelle_section) > 50 || ctype_digit($nom_nouvelle_section) || strpos($nom_nouvelle_section,'--') !== false) {
												$mess_err .= "Le nom ".$nom_nouvelle_section." est incorrect, veuillez en choisir un autre";
											}
											else {
									
												$lock = "LOCK TABLE (compagnies) WRITE";
												$mysqli->query($lock);
											
												// Création de la section
												$sql = "INSERT INTO compagnies (nom_compagnie, image_compagnie, resume_compagnie, description_compagnie, id_clan, genie_civil, id_parent) 
														VALUES ('$nom_nouvelle_section', '', '', '', '$id_camp', '0', '$id_compagnie')";
												$mysqli->query($sql);
												
												$id_new_comp = $mysqli->insert_id;
								
												$unlock = "UNLOCK TABLES";
												$mysqli->query($unlock);
												
												// Insertion compagnie_as_contraintes
												$sql = "INSERT INTO compagnie_as_contraintes VALUES ('$id_new_comp', '1')";
												$mysqli->query($sql);
												$sql = "INSERT INTO compagnie_as_contraintes VALUES ('$id_new_comp', '2')";
												$mysqli->query($sql);
												$sql = "INSERT INTO compagnie_as_contraintes VALUES ('$id_new_comp', '3')";
												$mysqli->query($sql);
												$sql = "INSERT INTO compagnie_as_contraintes VALUES ('$id_new_comp', '4')";
												$mysqli->query($sql);
												$sql = "INSERT INTO compagnie_as_contraintes VALUES ('$id_new_comp', '5')";
												$mysqli->query($sql);
												
												// Le perso passe de la compagnie mère à chef de la nouvelle section
												$sql = "UPDATE perso_in_compagnie SET id_compagnie='$id_new_comp', poste_compagnie='1' WHERE id_perso='$id_chef_nouvelle_section'";
												$mysqli->query($sql);
												
												// récupération de la thune du perso dans la banque de la compagnie
												$sql = "SELECT montant FROM banque_compagnie WHERE id_perso='$id_chef_nouvelle_section'";
												$res = $mysqli->query($sql);
												$t = $res->fetch_assoc();
												
												$montant_comp_chef_section = $t['montant'];
												
												// Creation de la banque de la section
												$sql = "INSERT INTO banque_as_compagnie (id_compagnie, montant) VALUES ('$id_new_comp', '$montant_comp_chef_section')";
												$mysqli->query($sql);
												
												// Mise à jour de la thune de la banque de la compagnie mère
												$sql = "UPDATE banque_as_compagnie montant = montant - $montant_comp_chef_section WHERE id_compagnie='$id_compagnie'";
												$mysqli->query($sql);
												
												// Insertion du perso dans la banque de la section avec transfert thunes
												$sql = "INSERT INTO `banque_compagnie` (`id_perso`, `montant`, `demande_emprunt`, `montant_emprunt`) VALUES ('$id_chef_nouvelle_section', '$montant_comp_chef_section', '0', '0')";
												$mysqli->query($sql);
												
												$mess .= "La section ".$_POST['nomSection']." a été créée";
											}
										}
										else {
											$mess_err .= "Le nom ".$nom_nouvelle_section." est déjà utilisé, veuillez en choisir un autre";
										}
									}
									else {
										// Tentative de triche
										$text_triche = "Tentative modification id chef section sur id appartenant pas à la compagnie";
								
										$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id', '$text_triche')";
										$mysqli->query($sql);
										
										header("Location:jouer.php");
									}
								}
								else {							
									// Tentative de triche
									$text_triche = "Tentative modification id chef section sur valeur non valide";
							
									$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id', '$text_triche')";
									$mysqli->query($sql);
									
									header("Location:jouer.php");
								}
							}
							else {
								$mess_err .= "Veuillez remplir tous les champs obligatoires";
							}
						}
						
						if (isset($_GET['supprimer_section']) && trim($_GET['supprimer_section']) != "") {
							
							$id_section_supression = $_GET['supprimer_section'];
							
							$verif_id = preg_match("#^[0-9]+$#i",$id_section_supression);
							
							if ($verif_id) {
							
								// Verification section appartient bien à la compagnie
								$sql = "SELECT * FROM compagnies WHERE id_compagnie='$id_section_supression' AND id_parent='$id_compagnie'";
								$res = $mysqli->query($sql);
								$verif_apparient_comp = $res->num_rows;
								
								if ($verif_apparient_comp) {
									
									// nombre de persos dans la compagnie
									$sql = "SELECT count(*) as nb_persos_compagnie FROM perso_in_compagnie WHERE id_compagnie='$id_compagnie' AND (attenteValidation_compagnie='0' OR attenteValidation_compagnie='2')";
									$res = $mysqli->query($sql);
									$tab = $res->fetch_assoc();
									
									$nb_persos_compagnie = $tab["nb_persos_compagnie"];
									
									// nombre de persos dans la section
									$sql = "SELECT count(*) as nb_persos_section FROM perso_in_compagnie WHERE id_compagnie='$id_section_supression' AND (attenteValidation_compagnie='0' OR attenteValidation_compagnie='2')";
									$res = $mysqli->query($sql);
									$tab = $res->fetch_assoc();
									
									$nb_persos_section = $tab["nb_persos_section"];
									
									// verification qu'on peut rapatrier tout le monde dans la compagnie
									if ($nb_persos_compagnie + $nb_persos_section <= 80) {
										
										$sql_pis = "SELECT id_perso FROM perso_in_compagnie WHERE id_compagnie='$id_section_supression' AND (attenteValidation_compagnie='0' OR attenteValidation_compagnie='2')";
										$res_pis = $mysqli->query($sql_pis);
										
										while ($t_pis = $res_pis->fetch_assoc()) {
											
											$id_perso_section = $t_pis['id_perso'];
											
											// récupération de la thune du perso dans la banque de la section
											$sql = "SELECT montant FROM banque_compagnie WHERE id_perso='$id_perso_section'";
											$res = $mysqli->query($sql);
											$t = $res->fetch_assoc();
											
											$montant_perso_section = $t['montant'];
											
											// MAJ du montant de la banque de la compagnie mère
											$sql = "UPDATE banque_as_compagnie montant = montant + $montant_perso_section WHERE id_compagnie='$id_compagnie'";
											$mysqli->query($sql);
											
											// On transfert le perso de la section à la compagnie en simple membre
											$sql = "UPDATE perso_in_compagnie SET id_compagnie='$id_compagnie', poste_compagnie='10' WHERE id_perso='$id_perso_section'";
											$mysqli->query($sql);
										}
										
										// Suppression de la section
										$sql = "DELETE FROM compagnies WHERE id_compagnie = '$id_section_supression'";
										$mysqli->query($sql);
										
										// Suppression de la banque de la section
										$sql = "DELETE FROM banque_as_compagnie WHERE id_compagnie = '$id_section_supression'";
										$mysqli->query($sql);
										
										// On supprime les persos restant dans la section (en attente de validation, autre ?)
										$sql = "DELETE FROM perso_in_compagnie WHERE id_compagnie='$id_section_supression'";
										$mysqli->query($sql);
									}
									else {
										$mess_err .= "Impossible de supprimer la section car il sera impossible de rapatrier tous les membres dans la compagnie (la limite sera dépassée)";
									}
								}
								else {
									// Tentative de triche
									$text_triche = "Tentative suppression section $id_section_supression qui appatient pas à la compagnie $id_compagnie";
							
									$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id', '$text_triche')";
									$mysqli->query($sql);
									
									header("Location:jouer.php");
								}
							}
							else {
								// Tentative de triche
								$text_triche = "Tentative modification id section pour action supprimer_section sur valeur non valide";
						
								$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id', '$text_triche')";
								$mysqli->query($sql);
								
								header("Location:jouer.php");
							}
						}

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
		<div class="container-fluid">
		
			<div class="row">
				<div class="col-12">

					<div align="center">
						<h2>Gestion des sections de la compagnie</h2>
						
						<center><font color='red'><?php echo $mess_err; ?></font></center>
						<center><font color='blue'><?php echo $mess; ?></font></center>
						
						<a href='admin_compagnie.php?id_compagnie=<?php echo $id_compagnie; ?>' class='btn btn-primary'>Retour à l'administration de la compagnie</a>
						<a href='compagnie.php' class='btn btn-primary'>Retour Compagnie</a>
						<?php
						if (isset($_GET['creer']) && $_GET['creer'] == "ok") {
						?>
						<a href='section_compagnie.php?id_compagnie=<?php echo $id_compagnie; ?>' class='btn btn-warning'>Liste des sections</a>
						<?php
						} else {
						?>
						<a href='section_compagnie.php?id_compagnie=<?php echo $id_compagnie; ?>&creer=ok' class='btn btn-warning'>Créer une nouvelle section</a>
						<?php
						}
						?>
					</div>
				</div>
			</div>
			
			<br />
			
			<div class="row">
				<div class="col-12">
					<div align="center">
						<?php
						if (isset($_GET['creer']) && $_GET['creer'] == "ok") {
						?>
						<h2>Création d'une nouvelle Section</h2>
						
						<form method='POST' action='section_compagnie.php?id_compagnie=<?php echo $id_compagnie; ?>&creer=ok'>
							<div class="form-row">
								<div class="form-group col-md-12">
									<label for="nomSection">Nom de la section <font color='red'>*</font></label>
									<input type="text" class="form-control" id="chefSection" name='nomSection' placeholder="Nom de la section" maxlength='50'>
								</div>
							</div>						
							<div class="form-row">
								<div class="form-group col-md-12">
									<label for="formSelectPerso">Chef de la section <font color='red'>*</font></label>
									<select class="form-control" name='liste_perso_chef_section' id="formSelectPerso">
									<?php
									$sql = "SELECT perso.id_perso, perso.nom_perso FROM perso, perso_in_compagnie 
											WHERE perso.id_perso = perso_in_compagnie.id_perso 
											AND perso_in_compagnie.id_compagnie='$id_compagnie'
											AND perso_in_compagnie.poste_compagnie != 1
											AND attenteValidation_compagnie = 0";
									$res = $mysqli->query($sql);
									
									while ($t = $res->fetch_assoc()) {
										
										$id_perso_section	= $t['id_perso'];
										$nom_perso_section	= $t['nom_perso'];
										
										// on recalcule le du
										// on verifie si le perso ne doit pas des sous a la compagnie
										$sql_du = "SELECT SUM(montant) as devoir FROM histobanque_compagnie WHERE id_perso=$id_perso_section AND id_compagnie=$id_compagnie AND operation='2'";
										$res_du = $mysqli->query($sql_du);
										$t_du = $res_du->fetch_assoc();
										
										$du_t = -$t_du["devoir"];
										
										// on verifie si le perso a rembourser une partie de ses dettes
										$sql_du = "SELECT SUM(montant) as remb FROM histobanque_compagnie WHERE id_perso=$id_perso_section AND id_compagnie=$id_compagnie AND operation='3'";
										$res_du = $mysqli->query($sql_du);
										$t_du = $res_du->fetch_assoc();
										
										$du_r = $t_du["remb"];
										
										$du = $du_t - $du_r;
										
										if ($du <= 0) {
											echo "<option value='".$id_perso_section."'>".$nom_perso_section." [".$id_perso_section."]</option>";
										}
									}
									?>
									</select>
								</div>
							</div>							
							<div class="form-row">
								<div class="form-group col-md-12">
									<input type="submit" class="btn btn-primary" name='creation_section' value='Créer'>
									<a href='section_compagnie.php?id_compagnie=<?php echo $id_compagnie; ?>' class='btn btn-danger'>Annuler</a>
								</div>
							</div>
						</form>
						
						<?php
						}
						else {
						?>
						<h2>Mes sections</h2>
						<div id="table_section" class="table-responsive">						
							<table class="table" border="1">
								<thead>
									<tr>
										<th style='text-align:center'>Nom de la section</th><th style='text-align:center'>Chef de la section</th><th style='text-align:center'>Nombre de membres</th><th style='text-align:center'>Actions</th>
									</tr>
								</thead>
								<tbody>
								<?php
								$sql = "SELECT id_compagnie, nom_compagnie, image_compagnie FROM compagnies WHERE id_parent='$id_compagnie'";
								$res = $mysqli->query($sql);
								$nb_sections = $res->num_rows;
								
								if (!$nb_sections) {
									echo "<tr><td colspan='4' align='center'><i>Aucune Section dans votre compagnie</i></td></tr>";
								}
								else {
									while ($t = $res->fetch_assoc()) {
										
										$id_section		= $t['id_compagnie'];
										$nom_section	= $t['nom_compagnie'];
										$image_section	= $t['image_compagnie'];
										
										// Nombre de persos dans la section
										$sql_nb_perso_sec = "SELECT id_perso FROM perso_in_compagnie WHERE id_compagnie='$id_section'";
										$res_nb_perso_sec = $mysqli->query($sql_nb_perso_sec);
										$nb_persos_sec = $res_nb_perso_sec->num_rows;
										
										// Chef de la section
										$sql_chef_sec = "SELECT perso.nom_perso, perso.id_perso 
															FROM perso, perso_in_compagnie 
															WHERE perso.id_perso = perso_in_compagnie.id_perso 
															AND id_compagnie='$id_section' AND poste_compagnie='1'";
										$res_chef_sec = $mysqli->query($sql_chef_sec);
										$t_chef_sec = $res_chef_sec->fetch_assoc();
										
										$nom_perso_chef_sec = $t_chef_sec['nom_perso'];
										$id_perso_chef_sec	= $t_chef_sec['id_perso'];
										
										echo "<tr>";
										echo "	<td align='center'>".$nom_section."</td>";
										echo "	<td align='center'>".$nom_perso_chef_sec." [".$id_perso_chef_sec."]</td>";
										echo "	<td align='center'>".$nb_persos_sec."</td>";
										echo "	<td align='center'><a href='section_compagnie.php?id_compagnie=".$id_compagnie."&supprimer_section=".$id_section."' class='btn btn-danger'>Supprimer Section</a></td>";
										echo "</tr>";
									}
								}
								
								?>
								</tbody>
							</table>
						</div>
						<?php
						}
						?>
					</div>
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
						// Tentative d'accès à cette page sans être le chef de la compagnie			
						$text_triche = "Tentative accés page section compagnie [$id_compagnie] sans y avoir les droits";
						
						$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id', '$text_triche')";
						$mysqli->query($sql);
						
						header("Location:jouer.php");
					}
				}
				else {
					// Tentative d'accès à cette page alors que compagnie du genie			
					$text_triche = "Tentative accés page section compagnie [$id_compagnie] alors que compagnie du genie	";
					
					$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id', '$text_triche')";
					$mysqli->query($sql);
					
					header("Location:jouer.php");
				}
			}
			else {
				// Tentative modification param id compagnie
				$text_triche = "Tentative modification param id compagnie sur la page de gestion des sections";
					
				$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id', '$text_triche')";
				$mysqli->query($sql);
				
				header("Location:jouer.php");
			}
		}
		else {
			// id compagnie obligatoire
			header("Location:jouer.php");
		}
	}
}
else{
	echo "<font color=red>Vous ne pouvez pas acceder a cette page, veuillez vous logguer.</font>";
}?>