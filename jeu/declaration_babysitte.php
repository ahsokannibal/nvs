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
		
		$sql = "SELECT id_perso, clan FROM perso WHERE perso.idJoueur_perso = (SELECT idJoueur_perso FROM perso WHERE id_perso='$id') AND chef='1'";
		$res =  $mysqli->query($sql);
		$t = $res->fetch_assoc();
		
		$id_chef = $t['id_perso'];
		$id_camp = $t['clan'];
		
		$mess = "";
		$mess_erreur = "";
		
		if (isset($_POST['nomBaby']) && $_POST['nomBaby'] != "" && isset($_POST['idBaby']) && $_POST['idBaby'] != "" 
			&& isset($_POST['dateDebut']) && $_POST['dateDebut'] != "" && isset($_POST['dateFin']) && $_POST['dateFin'] != "") {
			
			$nomBaby 		= $_POST['nomBaby'];
			$idBaby			= $_POST['idBaby'];
			$dateDebutBaby	= $_POST['dateDebut'];
			$dateFinBaby	= $_POST['dateFin'];
			
			$tabDateDebut 	= explode("/", $dateDebutBaby);
			$tabDateFin 	= explode("/", $dateFinBaby);
			
			$verifId = preg_match("#^[0-9]*[0-9]$#i","$idBaby");
			
			if (!filtre($nomBaby,1,20) || ctype_digit($nomBaby) || strpos($nomBaby,'--') !== false){
				$mess_erreur .= "Le nom du perso renseigné n'est pas conforme";
			}
			else {
				if ($verifId) {
					
					if ($idBaby != $id_chef) {
					
						if (count($tabDateDebut) == 3 && count($tabDateFin) == 3) {
							
							$jourDateDebut 	= $tabDateDebut[0];
							$moisDateDebut 	= $tabDateDebut[1];
							$anneeDateDebut = $tabDateDebut[2];
							
							$jourDateFin 	= $tabDateFin[0];
							$moisDateFin 	= $tabDateFin[1];
							$anneeDateFin 	= $tabDateFin[2];
						
							if (checkdate($moisDateDebut, $jourDateDebut, $anneeDateDebut) && checkdate($moisDateFin, $jourDateFin, $anneeDateFin)) {
								
								// On verifie que l'id du perso existe bien
								$sql = "SELECT clan FROM perso WHERE id_perso='$idBaby' AND chef='1'";
								$res = $mysqli->query($sql);
								$nb = $res->num_rows;
								
								if ($nb == 1) {
									$t = $res->fetch_assoc();
									
									$campBaby = $t['clan'];
									
									if ($id_camp == $campBaby) {
										
										// On vérifie s'il n'est pas déjà déclaré pour cette période
										$sql = "SELECT * FROM declaration_babysitte 
												WHERE id_perso='$id_chef' AND id_baby='$idBaby' AND date_debut = STR_TO_DATE(\"$dateDebutBaby\", '%d/%m/%Y') AND date_fin=STR_TO_DATE(\"$dateFinBaby\", '%d/%m/%Y')";
										$res = $mysqli->query($sql);
										$verif = $res->num_rows;
										
										if ($verif == 0) {
											$sql = "INSERT INTO declaration_babysitte (id_perso, id_baby, date_debut, date_fin) 
													VALUES ('$id_chef', '$idBaby', STR_TO_DATE(\"$dateDebutBaby\", '%d/%m/%Y'), STR_TO_DATE(\"$dateFinBaby\", '%d/%m/%Y'))";
											$mysqli->query($sql);
											
											$mess .= "Déclaration de babysitte du perso ".$nomBaby."[".$idBaby."] du ".$dateDebutBaby." au ".$dateFinBaby." bien enregistré";
										}
										else {
											$mess_erreur .= "Vous avez déjà déclaré un babysitte pour ce perso sur cette période";
										}
									}
									else {
										$mess_erreur .= "Vous n'avez pas le droit de babysitter un perso d'un autre camp !";
									}
								}
								else {
									$mess_erreur .= "L'id du perso renseigné n'existe pas";
								}
							}
							else {
								$mess_erreur .= "Les dates renseignées ne sont pas conforme";
							}
						}
						else {
							$mess_erreur .= "Les dates renseignées ne sont pas conforme";
						}
					}
					else {
						$mess_erreur .= "Vous ne pouvez pas déclarer un babysitte de votre propre perso...";
					}
				}
				else {
					$mess_erreur .= "L'id renseigné n'est pas conforme";
				}
			}
		}
		
		if (isset($_GET['fin_babysitte']) && $_GET['fin_babysitte'] != "") {
			
			$id_declaration_fin = $_GET['fin_babysitte'];
			
			$verifId = preg_match("#^[0-9]*[0-9]$#i","$id_declaration_fin");
			
			if ($verifId) {
				
				// récupération des infos de la déclaration pour verif
				$sql = "SELECT id_perso, id_baby FROM declaration_babysitte WHERE id_declaration='$id_declaration_fin'";
				$res = $mysqli->query($sql);
				$nb = $res->num_rows;
				
				if ($nb) {
					$t = $res->fetch_assoc();
				
					$id_perso_decla = $t['id_perso'];
					
					if ($id_perso_decla == $id_chef) {
						
						$id_baby = $t['id_baby'];
						
						$sql = "UPDATE declaration_babysitte SET date_fin=NOW() WHERE id_declaration='$id_declaration_fin'";
						$mysqli->query($sql);
						
						$mess .= "Déclaration de babysitting pour le perso matricule $id_baby a bien pris fin";
					}
					else {
						$mess_erreur .= "Vous n'avez pas le droit de modifier cette déclaration !";
						
						// Tentative de triche !
						$text_triche = "Tentative de mettre fin au babysitte id $id_declaration_fin qui ne lui appartient pas";
						
						$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id_chef', '$text_triche')";
						$mysqli->query($sql);
					}
				}
				else {
					$mess_erreur .= "L'id de declaration de babysitte n'existe pas";
					
					// Tentative de triche !
					$text_triche = "Tentative accès déclaration babysitte qui n'existe pas : id $id_declaration_fin";
					
					$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id_chef', '$text_triche')";
					$mysqli->query($sql);
				}
			}
			else {
				$mess_erreur .= "L'id de declaration de babysitte n'est pas conforme";
				
				// Tentative de triche !
				$text_triche = "Tentative changement paramètre fin_babysitte avec valeur incorrecte";
				
				$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id_chef', '$text_triche')";
				$mysqli->query($sql);
			}
		}
		
		// Récupération des babysittes actifs
		$sql_baby_courant = "SELECT * FROM declaration_babysitte WHERE (date_debut >= CURTIME() OR date_fin >= CURTIME()) AND id_perso='$id_chef'";
		$res_baby_courant = $mysqli->query($sql_baby_courant);
		$nb_baby_courant = $res_baby_courant->num_rows;
		
		// Récupération des babysittes passés
		$sql_baby_passe = "SELECT * FROM declaration_babysitte WHERE id_perso='$id_chef' AND date_fin < CURTIME()";
		$res_baby_passe = $mysqli->query($sql_baby_passe);
		$nb_baby_passe = $res_baby_passe->num_rows;
		
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
		<div class="container">
			
			<div class="row">
				<div class="col-12">
					<div align="center">
						<h2>Déclaration de Babysitte</h2>
					</div>
				</div>
			</div>
			
			<p align="center"><input type="button" value="Fermer la fenêtre" onclick="window.close()"></p>
			
			<div class="row">
				<div class="col-12">
					<div align="center">
						<?php
						echo "<font color='blue'>".$mess."</font><br />";
						echo "<font color='red'><b>".$mess_erreur."</b></font><br />";
						?>
					</div>
				</div>
			</div>
			
			<div class="row">
				<div class="col-12">
					<div align="center">
						<?php
						if (isset($_GET['voir']) && $_GET['voir'] == 'ok') {
							
							echo "<a href='declaration_babysitte.php?voir_archive=ok' class='btn btn-success'>Voir mes babysittings passés ";
							if ($nb_baby_passe) {
								echo "<span class='badge badge-pill badge-danger'>".$nb_baby_passe."</span>";
							}
							echo "</a><br /><br />";
							echo "<a href='declaration_babysitte.php' class='btn btn-danger'>Fermer le tableau</a>";
							
							echo "<div id='table_baby' class='table-responsive'>";
							echo "	<table class='table' width='80%'>";
							echo "		<thead>";
							echo "			<tr>";
							echo "				<th>Perso babysitté</th><th>Date de début de babysitte</th><th>Date de fin</th><th>Action</th>";
							echo "			</tr>";
							echo "		</thead>";
							echo "		<tbody>";
						
							$sql = "SELECT * FROM declaration_babysitte WHERE (date_debut >= CURTIME() OR date_fin >= CURTIME()) AND id_perso='$id_chef' ORDER BY date_debut";	
							$res = $mysqli->query($sql);
							
							while ($t = $res->fetch_assoc()) {
								
								$id_decla_baby		= $t['id_declaration'];
								$id_baby 			= $t['id_baby'];
								$date_debut_baby 	= $t['date_debut'];
								$date_fin_baby 		= $t['date_fin'];
								
								$tab_dateDebut 	= explode(" ",$date_debut_baby);
								$tab_dateFin 	= explode(" ",$date_fin_baby);
								
								$date_debut_baby 	= $tab_dateDebut[0];
								$date_fin_baby		= $tab_dateFin[0];
								
								$tab_dateDebut 	= explode("-", $date_debut_baby);
								$tab_dateFin 	= explode("-",$date_fin_baby);
								
								$date_debut_baby 	= $tab_dateDebut[2]."/".$tab_dateDebut[1]."/".$tab_dateDebut[0];
								$date_fin_baby 		= $tab_dateFin[2]."/".$tab_dateFin[1]."/".$tab_dateFin[0];
								
								$sql_p = "SELECT nom_perso FROM perso WHERE id_perso='$id_baby'";
								$res_p = $mysqli->query($sql_p);
								$t_p = $res_p->fetch_assoc();
								
								$nom_perso_baby = $t_p['nom_perso'];
								
								echo "			<tr>";
								echo "				<td>".$nom_perso_baby." [".$id_baby."]</td>";
								echo "				<td>".$date_debut_baby."</td>";
								echo "				<td>".$date_fin_baby."</td>";
								echo "				<td><a href='declaration_babysitte.php?fin_babysitte=".$id_decla_baby."' class='btn btn-danger'>Fin du babysitte</a></td>";
								echo "			</tr>";
							}
							
							echo "		</tbody>";
							echo "	</table>";
							echo "</div>";
						}
						else if (isset($_GET['voir_archive']) && $_GET['voir_archive'] == 'ok') {
							
							echo "<a href='declaration_babysitte.php?voir=ok' class='btn btn-success'>Voir mes babysittings actifs ";
							if ($nb_baby_courant) {
								echo "<span class='badge badge-pill badge-danger'>".$nb_baby_courant."</span>";
							}
							echo "</a><br /><br />";
							echo "<a href='declaration_babysitte.php' class='btn btn-danger'>Fermer le tableau</a>";
							
							echo "<div id='table_baby' class='table-responsive'>";
							echo "	<table class='table' width='80%'>";
							echo "		<thead>";
							echo "			<tr>";
							echo "				<th>Perso babysitté</th><th>Date de début de babysitte</th><th>Date de fin</th>";
							echo "			</tr>";
							echo "		</thead>";
							echo "		<tbody>";
						
							$sql = "SELECT * FROM declaration_babysitte WHERE date_fin < CURTIME() AND id_perso='$id_chef' ORDER BY date_debut";	
							$res = $mysqli->query($sql);
							
							while ($t = $res->fetch_assoc()) {
								
								$id_baby 			= $t['id_baby'];
								$date_debut_baby 	= $t['date_debut'];
								$date_fin_baby 		= $t['date_fin'];
								
								$tab_dateDebut 	= explode(" ",$date_debut_baby);
								$tab_dateFin 	= explode(" ",$date_fin_baby);
								
								$date_debut_baby 	= $tab_dateDebut[0];
								$date_fin_baby		= $tab_dateFin[0];
								
								$tab_dateDebut 	= explode("-", $date_debut_baby);
								$tab_dateFin 	= explode("-",$date_fin_baby);
								
								$date_debut_baby 	= $tab_dateDebut[2]."/".$tab_dateDebut[1]."/".$tab_dateDebut[0];
								$date_fin_baby 		= $tab_dateFin[2]."/".$tab_dateFin[1]."/".$tab_dateFin[0];
								
								$sql_p = "SELECT nom_perso FROM perso WHERE id_perso='$id_baby'";
								$res_p = $mysqli->query($sql_p);
								$t_p = $res_p->fetch_assoc();
								
								$nom_perso_baby = $t_p['nom_perso'];
								
								echo "			<tr>";
								echo "				<td>".$nom_perso_baby." [".$id_baby."]</td>";
								echo "				<td>".$date_debut_baby."</td>";
								echo "				<td>".$date_fin_baby."</td>";
								echo "			</tr>";
							}
							
							echo "		</tbody>";
							echo "	</table>";
							echo "</div>";
						}
						else {
							echo "<a href='declaration_babysitte.php?voir=ok' class='btn btn-success'>Voir mes babysittings actifs ";
							if ($nb_baby_courant) {
								echo "<span class='badge badge-pill badge-danger'>".$nb_baby_courant."</span>";
							}
							echo "</a> ";
							echo "<a href='declaration_babysitte.php?voir_archive=ok' class='btn btn-success'>Voir mes babysittings passés ";
							if ($nb_baby_passe) {
								echo "<span class='badge badge-pill badge-danger'>".$nb_baby_passe."</span>";
							}
							echo "</a>";
						}
						?>
					</div>
				</div>
			</div>
			
			<br />
			
			<div class="row">
				<div class="col-12">
					<div align="center">
						<hr>
						<h1>Déclaration d'un nouveau Babysitte</h1>
						<form method='post' action='declaration_babysitte.php'>
							<div class="form-group col-md-6">
								<label for="nomBaby">Nom du chef du compte babysitté <font color='red'>*</font></label>
								<input type="text" class="form-control" id="nomBaby" name="nomBaby" maxlength="20">
							</div>
							<div class="form-group col-md-6">
								<label for="idBaby">Id du chef du compte babysitté <font color='red'>*</font></label>
								<input type="text" class="form-control" id="idBaby" name="idBaby" maxlength="10">
							</div>
							<div class="form-group col-md-8">
								<div class="input-group input-daterange">
									<div class="input-group-addon">Du&nbsp;&nbsp;&nbsp;&nbsp;</div>
									<input type="text" class="form-control" id="dateDebut" name="dateDebut" placeholder="DD/MM/YYYY">
									<div class="input-group-addon">
										<span class="glyphicon glyphicon-th"></span>
									</div>
									<div class="input-group-addon">&nbsp;&nbsp;&nbsp;&nbsp;jusqu'au&nbsp;&nbsp;&nbsp;&nbsp;</div>
									<input type="text" class="form-control" id="dateFin" name="dateFin" placeholder="DD/MM/YYYY">
								</div>
							</div>
							<div class="form-group col-md-6">
								<input type="submit" name="envoyer" value="envoyer" class='btn btn-primary'>
							</div>
						</form>
					</div>
				</div>
			</div>
			
		</div>
		
		<!-- Optional JavaScript -->
		<!-- jQuery first, then Popper.js, then Bootstrap JS -->
		<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
		<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
	
		<!-- Bootstrap date picker -->
		<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css"/>
		
		<script>
			;(function($){
				$.fn.datepicker.dates['fr'] = {
					days: ["dimanche", "lundi", "mardi", "mercredi", "jeudi", "vendredi", "samedi"],
					daysShort: ["dim.", "lun.", "mar.", "mer.", "jeu.", "ven.", "sam."],
					daysMin: ["d", "l", "ma", "me", "j", "v", "s"],
					months: ["janvier", "février", "mars", "avril", "mai", "juin", "juillet", "août", "septembre", "octobre", "novembre", "décembre"],
					monthsShort: ["janv.", "févr.", "mars", "avril", "mai", "juin", "juil.", "août", "sept.", "oct.", "nov.", "déc."],
					today: "Aujourd'hui",
					monthsTitle: "Mois",
					clear: "Effacer",
					weekStart: 1,
					format: "dd/mm/yyyy"
				};
			}(jQuery));
		
			$(document).ready(function(){
				var options={
					autoclose: true,
					startDate: '-0d',
					language: 'fr',
					todayHighlight: true
				};
				
				$('.input-daterange input').each(function() {
					$(this).datepicker(options);
				});
			})			
		</script>
	</body>
<?php
	}
	else{
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
</html>