<?php
session_start();
require_once("../fonctions.php");

$mysqli = db_connexion();

include ('../nb_online.php');
$phpbb_root_path = '../forum/';
if (is_dir($phpbb_root_path))
{
	include ($phpbb_root_path .'config.php');
}

if(isset($_SESSION["id_perso"])){
	
	$id_perso = $_SESSION['id_perso'];
	
	// recupération config jeu
	$admin 	= admin_perso($mysqli, $id_perso);
	$anim 	= anim_perso($mysqli, $id_perso);
	
	if($admin || $anim) {
		
		$mess_err 	= "";
		$mess 		= "";
		
		date_default_timezone_set('Europe/Paris');
		
		// Récupération du camp de l'animateur 
		$sql = "SELECT clan FROM perso WHERE id_perso='$id_perso'";
		$res = $mysqli->query($sql);
		$t = $res->fetch_assoc();
		
		$camp_anim = $t['clan'];
		
		if(isset($_POST['select_perso']) && $_POST['select_perso'] != '') {
			$id_perso_select = $_POST['select_perso'];
		}
		
		if (isset($_GET['id_perso']) && $_GET['id_perso'] != '') {
			$id_perso_select = $_GET['id_perso'];
		}
		
		if (isset($id_perso_select)) {
		
			// Récupération du camp du perso selectionné
			$sql = "SELECT clan FROM perso WHERE id_perso='$id_perso_select'";
			$res = $mysqli->query($sql);
			$t = $res->fetch_assoc();
			
			$camp_perso_select = $t['clan'];
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
						<h2>Logs d'accès</h2>
						
						<font color='red'><?php echo $mess_err; ?></font>
						<font color='blue'><?php echo $mess; ?></font>
					</div>
				</div>
			</div>
		
			<p align="center"><a class="btn btn-primary" href="animation.php">Retour à l'animation</a> <a class="btn btn-primary" href="jouer.php">Retour au jeu</a></p>
			
			
			<div class="row">
				<div class="col-12">
					<form method='POST' action='anim_log_access.php'>
						<select name="select_perso" onchange="this.form.submit()">
							<?php
							$sql = "SELECT id_perso, nom_perso FROM perso ORDER BY id_perso ASC";
							$res = $mysqli->query($sql);
							
							while ($t = $res->fetch_assoc()) {
								
								$id_perso 	= $t["id_perso"];
								$nom_perso 	= $t["nom_perso"];
								
								echo "<option value='".$id_perso."'";
								if (isset($id_perso_select) && $id_perso_select == $id_perso) {
									echo " selected";
								}
								echo ">".$nom_perso." [".$id_perso."]</option>";
							}
							?>
						</select>
						<input type="submit" value="choisir">
					</form>
				</div>
			</div>
			
			
			<br />
			
			<?php
			if (isset($id_perso_select) && trim($id_perso_select) != "") {
			?>
			
			<div class="row">
				<div class="col-12">
					<div align="center">	
						<?php
						if (!isset($_GET['detail_complet'])) {
							echo "<a href='anim_log_access.php?id_perso=".$id_perso_select."&detail_complet=ok' class='btn btn-outline-secondary'>Détail Complet des logs</a> ";
						}
						else {
							echo "<a href='anim_log_access.php?id_perso=".$id_perso_select."&detail_complet=ok' class='btn btn-secondary'>Détail Complet des logs</a> ";
						}
						
						if (isset($_GET['jour']) || isset($_GET['mois']) || isset($_GET['annee'])) {
							echo "<a href='anim_log_access.php?id_perso=".$id_perso_select."&stat_jour=ok' class='btn btn-outline-warning'>Statistiques par jour</a> ";
							echo "<a href='anim_log_access.php?id_perso=".$id_perso_select."&stat_mois=ok' class='btn btn-outline-warning'>Statistiques par mois</a> ";
							echo "<a href='anim_log_access.php?id_perso=".$id_perso_select."&stat_annee=ok' class='btn btn-outline-warning'>Statistiques par année</a> ";
							
							if (isset($_GET['jour']) && isset($_GET['mois']) && isset($_GET['annee']) && !isset($_GET['graph_jour'])) {
								$jour	= $_GET['jour'];
								$mois	= $_GET['mois'];
								$annee	= $_GET['annee'];
								
								$affichage_date = sprintf('%02d', $jour)."/".sprintf('%02d', $mois)."/".$annee;
								
								echo "<a href='anim_log_access.php?id_perso=".$id_perso_select."&graph_jour=ok&jour=".$jour."&mois=".$mois."&annee=".$annee."' class='btn btn-primary'>Graphique</a>";
								
								echo "<br /><br /><h2>Détail logs du jour $affichage_date</h2>";
							}
							elseif (isset($_GET['mois']) && isset($_GET['annee']) && !isset($_GET['graph_mois'])) {
								$mois	= $_GET['mois'];
								$annee	= $_GET['annee'];
								
								$affichage_date = sprintf('%02d', $mois)."/".$annee;
								
								echo "<a href='anim_log_access.php?id_perso=".$id_perso_select."&graph_mois=ok&mois=".$mois."&annee=".$annee."' class='btn btn-primary'>Graphique</a>";
								
								echo "<br /><br /><h2>Détail logs du mois $affichage_date</h2>";
							}
							elseif (isset($_GET['annee']) && !isset($_GET['graph_annee'])) {
								$annee	= $_GET['annee'];
								
								echo "<a href='anim_log_access.php?id_perso=".$id_perso_select."&graph_annee=ok&annee=".$annee."' class='btn btn-primary'>Graphique</a>";
								
								echo "<br /><br /><h2>Détail logs de l'année $annee</h2>";
							}
						}
						else {
							if (isset($_GET['stat_jour'])) {
								echo "<a href='anim_log_access.php?id_perso=".$id_perso_select."&stat_jour=ok' class='btn btn-warning'>Statistiques par jour</a> ";
								echo "<a href='anim_log_access.php?id_perso=".$id_perso_select."&stat_mois=ok' class='btn btn-outline-warning'>Statistiques par mois</a> ";
								echo "<a href='anim_log_access.php?id_perso=".$id_perso_select."&stat_annee=ok' class='btn btn-outline-warning'>Statistiques par année</a>";
								echo "<br />";
							}
							elseif (isset($_GET['stat_mois'])) {
								echo "<a href='anim_log_access.php?id_perso=".$id_perso_select."&stat_jour=ok' class='btn btn-outline-warning'>Statistiques par jour</a> ";
								echo "<a href='anim_log_access.php?id_perso=".$id_perso_select."&stat_mois=ok' class='btn btn-warning'>Statistiques par mois</a> ";
								echo "<a href='anim_log_access.php?id_perso=".$id_perso_select."&stat_annee=ok' class='btn btn-outline-warning'>Statistiques par année</a>";
								echo "<br />";
							}
							elseif (isset($_GET['stat_annee'])) {
								echo "<a href='anim_log_access.php?id_perso=".$id_perso_select."&stat_jour=ok' class='btn btn-outline-warning'>Statistiques par jour</a> ";
								echo "<a href='anim_log_access.php?id_perso=".$id_perso_select."&stat_mois=ok' class='btn btn-outline-warning'>Statistiques par mois</a>";
								echo "<a href='anim_log_access.php?id_perso=".$id_perso_select."&stat_annee=ok' class='btn btn-warning'>Statistiques par année</a>";
								echo "<br />";
							}
							else {
								echo "<a href='anim_log_access.php?id_perso=".$id_perso_select."&stat_jour=ok' class='btn btn-outline-warning'>Statistiques par jour</a> ";
								echo "<a href='anim_log_access.php?id_perso=".$id_perso_select."&stat_mois=ok' class='btn btn-outline-warning'>Statistiques par mois</a> ";
								echo "<a href='anim_log_access.php?id_perso=".$id_perso_select."&stat_annee=ok' class='btn btn-outline-warning'>Statistiques par année</a>";
								echo "<br />";
							}
						}						
						echo "<br />";
						?>
						<div id="table_logs_acces" class="table-responsive">
							<?php
							if (isset($_GET['jour']) && isset($_GET['mois']) && isset($_GET['annee']) && !isset($_GET['graph_jour'])) {
								
								$jour	= $_GET['jour'];
								$mois	= $_GET['mois'];
								$annee	= $_GET['annee'];
							
								$sql = "SELECT *
										FROM acces_log
										WHERE id_perso='$id_perso_select'
										AND YEAR(date_acces) = $annee AND MONTH(date_acces) = $mois AND DAY(date_acces) = $jour";
							}
							elseif (isset($_GET['mois']) && isset($_GET['annee']) && !isset($_GET['graph_jour']) && !isset($_GET['graph_mois'])) {
								
								$mois	= $_GET['mois'];
								$annee	= $_GET['annee'];
							
								$sql = "SELECT *
										FROM acces_log
										WHERE id_perso='$id_perso_select'
										AND YEAR(date_acces) = $annee AND MONTH(date_acces) = $mois";
							}
							elseif (isset($_GET['annee']) && !isset($_GET['graph_jour']) && !isset($_GET['graph_mois']) && !isset($_GET['graph_annee'])) {
								
								$annee	= $_GET['annee'];
							
								$sql = "SELECT *
										FROM acces_log
										WHERE id_perso='$id_perso_select'
										AND YEAR(date_acces) = $annee";
							}
							else {
								if (isset($_GET['stat_jour'])) {
									$sql = "SELECT DAY(date_acces) as jour, MONTH(date_acces) as mois, YEAR(date_acces) as annee, COUNT(*) as nb_logs
										FROM acces_log
										WHERE id_perso='$id_perso_select'
										GROUP BY YEAR(date_acces), MONTH(date_acces), DAY(date_acces)
										ORDER BY YEAR(date_acces) DESC, MONTH(date_acces) DESC, DAY(date_acces) DESC";
								}
								elseif (isset($_GET['stat_mois'])) {
									$sql = "SELECT MONTH(date_acces) as mois, YEAR(date_acces) as annee, COUNT(*) as nb_logs
										FROM acces_log
										WHERE id_perso='$id_perso_select'
										GROUP BY YEAR(date_acces), MONTH(date_acces)
										ORDER BY YEAR(date_acces) DESC, MONTH(date_acces) DESC";
								}
								elseif (isset($_GET['stat_annee'])) {
									$sql = "SELECT YEAR(date_acces) as annee, COUNT(*) as nb_logs
										FROM acces_log
										WHERE id_perso='$id_perso_select'
										GROUP BY YEAR(date_acces)
										ORDER BY YEAR(date_acces) DESC";
								}
								elseif (isset($_GET['detail_complet'])) {
									$sql = "SELECT UNIX_TIMESTAMP(date_acces) as date_acces, page FROM acces_log WHERE id_perso='$id_perso_select' ORDER BY id_acces DESC";
								}
							}
							$res = $mysqli->query($sql);
							
							echo "<table class='table'>";
							echo "	<thead>";
							echo "		<tr>";
							if (((isset($_GET['jour']) || isset($_GET['mois']) || isset($_GET['annee'])) && !isset($_GET['graph_jour']) && !isset($_GET['graph_mois']) && !isset($_GET['graph_annee'])) || isset($_GET['detail_complet'])) {
								echo "			<th style='text-align:center'>Date accès</th>";
								echo "			<th style='text-align:center'>Page</th>";
							}
							else {
								if (isset($_GET['stat_jour'])) {
									echo "			<th style='text-align:center'>Jour</th>";
									echo "			<th style='text-align:center'>Nb logs Perso</th>";
									echo "			<th style='text-align:center'>Action</th>";
								}
								elseif (isset($_GET['stat_mois'])) {
									echo "			<th style='text-align:center'>Mois</th>";
									echo "			<th style='text-align:center'>Nb logs Perso</th>";
									echo "			<th style='text-align:center'>Action</th>";
								}
								elseif (isset($_GET['stat_annee'])) {
									echo "			<th style='text-align:center'>Année</th>";
									echo "			<th style='text-align:center'>Nb logs Perso</th>";
									echo "			<th style='text-align:center'>Action</th>";
								}
							}
							echo "		</tr>";
							echo "	</thead>";
							echo "	<tbody>";
							
							while ($t = $res->fetch_assoc()) {
								
								if (((isset($_GET['jour']) || isset($_GET['mois']) || isset($_GET['annee'])) && !isset($_GET['graph_jour']) && !isset($_GET['graph_mois']) && !isset($_GET['graph_annee'])) || isset($_GET['detail_complet'])) {
									$date_acces	= $t['date_acces'];
									$date_acces = date('Y-m-d H:i:s', $date_acces);
									$page_acces	= $t['page'];
									
									echo "		<tr>";
									echo "			<td align='center'>".$date_acces."</td>";
									echo "			<td align='center'>".$page_acces."</td>";
									echo "		</tr>";
								}
								else {
									if (isset($_GET['stat_jour'])) {
										$jour		= $t['jour'];
										$mois		= $t['mois'];
										$annee		= $t['annee'];
										$nb_logs	= $t['nb_logs'];
										
										/*
										$sql_logs_bataillon = "SELECT *
																FROM acces_log
																WHERE id_perso IN (SELECT id_perso FROM perso WHERE idJoueur_perso = (SELECT idJoueur_perso FROM perso WHERE id_perso='$id_perso_select'))
																AND YEAR(date_acces) = $annee AND MONTH(date_acces) = $mois AND DAY(date_acces) = $jour";
										$res_logs_bataillon = $mysqli->query($sql_logs_bataillon);
										$nb_logs_bataillon	= $res_logs_bataillon->num_rows;
										*/
										
										echo "		<tr>";
										echo "			<td align='center'>".sprintf('%02d', $jour)."/".sprintf('%02d', $mois)."/".$annee."</td>";
										echo "			<td align='center'>".$nb_logs."</td>";
										echo "			<td align='center'>";
										echo "				<a href='anim_log_access.php?id_perso=".$id_perso_select."&stat_jour=ok&jour=".$jour."&mois=".$mois."&annee=".$annee."' class='btn btn-primary'>Détail logs</a>";
										echo "				<a href='anim_log_access.php?id_perso=".$id_perso_select."&graph_jour=ok&jour=".$jour."&mois=".$mois."&annee=".$annee."' class='btn btn-primary'>Graphique</a>";
										if (isset($camp_perso_select) && $camp_anim == $camp_perso_select) {
											echo "				<a href='anim_event_perso.php?id_perso=".$id_perso_select."&jour=".$jour."&mois=".$mois."&annee=".$annee."' target='_blank' class='btn btn-secondary'>Événements détaillés</a>";
										}
										echo "			</td>";
										echo "		</tr>";
									}
									elseif (isset($_GET['stat_mois'])) {
										$mois		= $t['mois'];
										$annee		= $t['annee'];
										$nb_logs	= $t['nb_logs'];
										
										echo "		<tr>";
										echo "			<td align='center'>".sprintf('%02d', $mois)." / ".$annee."</td>";
										echo "			<td align='center'>".$nb_logs."</td>";
										echo "			<td align='center'>";
										echo "				<a href='anim_log_access.php?id_perso=".$id_perso_select."&stat_mois=ok&mois=".$mois."&annee=".$annee."' class='btn btn-primary'>Détail</a>";
										echo "				<a href='anim_log_access.php?id_perso=".$id_perso_select."&graph_mois=ok&mois=".$mois."&annee=".$annee."' class='btn btn-primary'>Graphique</a>";
										echo "			</td>";
										echo "		</tr>";
									}
									elseif (isset($_GET['stat_annee'])) {
										$annee		= $t['annee'];
										$nb_logs	= $t['nb_logs'];
										
										echo "		<tr>";
										echo "			<td align='center'>".$annee."</td>";
										echo "			<td align='center'>".$nb_logs."</td>";
										echo "			<td align='center'>";
										echo "				<a href='anim_log_access.php?id_perso=".$id_perso_select."&stat_annee=ok&annee=".$annee."' class='btn btn-primary'>Détail</a>";
										echo "				<a href='anim_log_access.php?id_perso=".$id_perso_select."&graph_annee=ok&annee=".$annee."' class='btn btn-primary'>Graphique</a>";
										echo "			</td>";
										echo "		</tr>";
									}
								}
							}
							
							echo "	</tbody>";
							echo "</table>";
							?>
						</div>
					</div>
				</div>
			</div>
			
			<?php
			}
			?>
		
		</div>
		
		<div class="row my-2">
			<div class="col-md-2"></div>
			<div class="col-md-8">
				<div class="card">
					<?php
					$data_log_jouer 	= array();
					$data_log_evenement = array();
					$data_log_carte 	= array();
					
					if (isset($_GET['graph_jour']) && isset($_GET['jour']) && isset($_GET['mois']) && isset($_GET['annee'])) {
						
						$jour 	= $_GET['jour'];
						$mois	= $_GET['mois'];
						$annee	= $_GET['annee'];
						
						// On récupère les jours qui possèdent des logs pour ce mois et cette année
						$sql = "SELECT DISTINCT DAY(date_acces) as jours FROM acces_log
								WHERE id_perso='$id_perso_select'
								AND YEAR(date_acces) = '$annee'
								AND MONTH(date_acces) = '$mois'";
						$res = $mysqli->query($sql);
						echo "<center>";
						while ($t = $res->fetch_assoc()) {
							$data_jour = $t['jours'];
							echo "<a href='anim_log_access.php?id_perso=".$id_perso_select."&graph_jour=ok&jour=".$data_jour."&mois=".$mois."&annee=".$annee."' ";
							if ($jour == $data_jour) {
								echo "class='btn btn-secondary'";
							}
							else {
								echo "class='btn btn-primary'";
							}
							echo ">".$data_jour."</a>";
						}
						echo "</center>";
						
						echo "<center>Graphique par heure du jour ".sprintf('%02d', $jour)."/".sprintf('%02d', $mois)."/".$annee."</center>";
						
						$heure_jouer_tmp 		= 0;
						$heure_evenement_tmp	= 0;
						$heure_carte_tmp		= 0;
						
						// Page jouer.php
						$sql = "SELECT COUNT(*) as nb_logs_jouer, HOUR(date_acces) as heure
									FROM acces_log
									WHERE id_perso='$id_perso_select'
									AND page LIKE 'jouer.php%'
									AND YEAR(date_acces) = '$annee'
									AND MONTH(date_acces) = '$mois'
									AND DAY(date_acces) = '$jour'
									GROUP BY HOUR(date_acces)";
						$res = $mysqli->query($sql);
						
						while ($t = $res->fetch_assoc()) {
							$nb_logs_jouer 	= $t['nb_logs_jouer'];
							$heure_jouer	= $t['heure'];
							
							if ($heure_jouer_tmp == 0) {
								for ($i = 0; $i < $heure_jouer; $i++) {
									array_push($data_log_jouer, 0);
								}
								
								$heure_jouer_tmp = $heure_jouer;
							}
							else {
								for ($i = $heure_jouer_tmp + 1; $i < $heure_jouer; $i++) {
									array_push($data_log_jouer, 0);
								}
								
								$heure_jouer_tmp = $heure_jouer;
							}
							
							array_push($data_log_jouer, $nb_logs_jouer);
						}
						
						
						// Page evenement.php
						$sql = "SELECT COUNT(*) as nb_logs_evenement, HOUR(date_acces) as heure
									FROM acces_log
									WHERE id_perso='$id_perso_select'
									AND page LIKE 'evenement.php%'
									AND YEAR(date_acces) = '$annee'
									AND MONTH(date_acces) = '$mois'
									AND DAY(date_acces) = '$jour'
									GROUP BY HOUR(date_acces)";
						$res = $mysqli->query($sql);
						
						while ($t = $res->fetch_assoc()) {
							$nb_logs_evenement 	= $t['nb_logs_evenement'];
							$heure_evenement	= $t['heure'];
							
							if ($heure_evenement_tmp == 0) {
								for ($i = 0; $i < $heure_evenement; $i++) {
									array_push($data_log_evenement, 0);
								}
								
								$heure_evenement_tmp = $heure_evenement;
							}
							else {
								for ($i = $heure_evenement_tmp + 1; $i < $heure_evenement; $i++) {
									array_push($data_log_evenement, 0);
								}
								
								$heure_evenement_tmp = $heure_evenement;
							}
							
							array_push($data_log_evenement, $nb_logs_evenement);
						}
						
						
						// page afficher_carte.php
						$sql = "SELECT COUNT(*) as nb_logs_carte, HOUR(date_acces) as heure
									FROM acces_log
									WHERE id_perso='$id_perso_select'
									AND page LIKE 'afficher_carte.php%'
									AND YEAR(date_acces) = '$annee'
									AND MONTH(date_acces) = '$mois'
									AND DAY(date_acces) = '$jour'
									GROUP BY HOUR(date_acces)";
						$res = $mysqli->query($sql);
						
						while ($t = $res->fetch_assoc()) {
							$nb_logs_carte 	= $t['nb_logs_carte'];
							$heure_carte	= $t['heure'];
							
							if ($heure_carte_tmp == 0) {
								for ($i = 0; $i < $heure_carte; $i++) {
									array_push($data_log_carte, 0);
								}
								
								$heure_carte_tmp = $heure_carte;
							}
							else {
								for ($i = $heure_carte_tmp + 1; $i < $heure_carte; $i++) {
									array_push($data_log_carte, 0);
								}
								
								$heure_carte_tmp = $heure_carte;
							}
							
							array_push($data_log_carte, $nb_logs_carte);
						}
						
						echo "<canvas id='chBarJour'></canvas>";
						
					}
					elseif (isset($_GET['graph_mois']) && isset($_GET['mois']) && isset($_GET['annee'])) {
						
						$mois	= $_GET['mois'];
						$annee	= $_GET['annee'];
						
						// On récupère les mois qui possèdent des logs pour cette année
						$sql = "SELECT DISTINCT MONTH(date_acces) as mois FROM acces_log
								WHERE id_perso='$id_perso_select'
								AND YEAR(date_acces) = '$annee'";
						$res = $mysqli->query($sql);
						echo "<center>";
						while ($t = $res->fetch_assoc()) {
							$data_mois = $t['mois'];
							echo "<a href='anim_log_access.php?id_perso=".$id_perso_select."&graph_mois=ok&mois=".$data_mois."&annee=".$annee."' ";
							if ($mois == $data_mois) {
								echo "class='btn btn-secondary'";
							}
							else {
								echo "class='btn btn-primary'";
							}
							echo ">".$data_mois."</a>";
						}
						echo "</center>";
						
						echo "<center>Graphique par jour du mois ".sprintf('%02d', $mois)."/".$annee."</center>";
						
						$jour_jouer_tmp 	= 0;
						$jour_evenement_tmp	= 0;
						$jour_carte_tmp		= 0;
						
						$data_jours_mois = array();
						$nombre_jour_mois = cal_days_in_month(CAL_GREGORIAN, $mois, $annee);
						
						for ($i = 1; $i <= $nombre_jour_mois; $i++) {
							array_push($data_jours_mois, $i);
						}
						
						// page jouer.php
						$sql = "SELECT COUNT(*) as nb_logs_jouer, DAY(date_acces) as jour
									FROM acces_log
									WHERE id_perso='$id_perso_select'
									AND page LIKE 'jouer.php%'
									AND YEAR(date_acces) = '$annee'
									AND MONTH(date_acces) = '$mois'
									GROUP BY DAY(date_acces)";
						$res = $mysqli->query($sql);
						
						while ($t = $res->fetch_assoc()) {
							$nb_logs_jouer 	= $t['nb_logs_jouer'];
							$jour_jouer		= $t['jour'];
							
							if ($jour_jouer_tmp == 0) {
								for ($i = 1; $i < $jour_jouer; $i++) {
									array_push($data_log_jouer, 0);
								}
								
								$jour_jouer_tmp = $jour_jouer;
							}
							else {
								for ($i = $jour_jouer_tmp + 1; $i < $jour_jouer; $i++) {
									array_push($data_log_jouer, 0);
								}
								
								$jour_jouer_tmp = $jour_jouer;
							}
							
							array_push($data_log_jouer, $nb_logs_jouer);
						}
						
						// page evenement.php
						$sql = "SELECT COUNT(*) as nb_logs_evenement, DAY(date_acces) as jour
									FROM acces_log
									WHERE id_perso='$id_perso_select'
									AND page LIKE 'evenement.php%'
									AND YEAR(date_acces) = '$annee'
									AND MONTH(date_acces) = '$mois'
									GROUP BY DAY(date_acces)";
						$res = $mysqli->query($sql);
						
						while ($t = $res->fetch_assoc()) {
							$nb_logs_evenement 	= $t['nb_logs_evenement'];
							$jour_evenement		= $t['jour'];
							
							if ($jour_evenement_tmp == 0) {
								for ($i = 1; $i < $jour_evenement; $i++) {
									array_push($data_log_evenement, 0);
								}
								
								$jour_evenement_tmp = $jour_evenement;
							}
							else {
								for ($i = $jour_evenement_tmp + 1; $i < $jour_evenement; $i++) {
									array_push($data_log_evenement, 0);
								}
								
								$jour_evenement_tmp = $jour_evenement;
							}
							
							array_push($data_log_evenement, $nb_logs_evenement);
						}
						
						// page afficher_carte.php
						$sql = "SELECT COUNT(*) as nb_logs_carte, DAY(date_acces) as jour
									FROM acces_log
									WHERE id_perso='$id_perso_select'
									AND page LIKE 'afficher_carte.php%'
									AND YEAR(date_acces) = '$annee'
									AND MONTH(date_acces) = '$mois'
									GROUP BY DAY(date_acces)";
						$res = $mysqli->query($sql);
						
						while ($t = $res->fetch_assoc()) {
							$nb_logs_carte 	= $t['nb_logs_carte'];
							$jour_carte		= $t['jour'];
							
							if ($jour_carte_tmp == 0) {
								for ($i = 1; $i < $jour_carte; $i++) {
									array_push($data_log_carte, 0);
								}
								
								$jour_carte_tmp = $jour_carte;
							}
							else {								
								for ($i = $jour_carte_tmp + 1; $i < $jour_carte; $i++) {
									array_push($data_log_carte, 0);
								}
								
								$jour_carte_tmp = $jour_carte;
							}
							
							array_push($data_log_carte, $nb_logs_carte);
						}
						
						echo "<canvas id='chBarMois'></canvas>";
					}
					elseif (isset($_GET['graph_annee']) && isset($_GET['annee'])) {
						
						$annee	= $_GET['annee'];
						
						// On récupère les année qui possèdent des logs
						$sql = "SELECT DISTINCT YEAR(date_acces) as annee FROM acces_log
								WHERE id_perso='$id_perso_select'";
						$res = $mysqli->query($sql);
						echo "<center>";
						while ($t = $res->fetch_assoc()) {
							$data_annee = $t['annee'];
							echo "<a href='anim_log_access.php?id_perso=".$id_perso_select."&graph_annee=ok&annee=".$data_annee."' ";
							if ($annee == $data_annee) {
								echo "class='btn btn-secondary'";
							}
							else {
								echo "class='btn btn-primary'";
							}
							echo ">".$data_annee."</a>";
						}
						echo "</center>";
						
						echo "<center>Graphique par mois de l'année ".$annee."</center>";
						
						$mois_jouer_tmp 	= 0;
						$mois_evenement_tmp	= 0;
						$mois_carte_tmp		= 0;
						
						// page jouer.php
						$sql = "SELECT COUNT(*) as nb_logs_jouer, MONTH(date_acces) as mois
									FROM acces_log
									WHERE id_perso='$id_perso_select'
									AND page LIKE 'jouer.php%'
									AND YEAR(date_acces) = '$annee'
									GROUP BY MONTH(date_acces)";
						$res = $mysqli->query($sql);
						
						while ($t = $res->fetch_assoc()) {
							$nb_logs_jouer 	= $t['nb_logs_jouer'];
							$mois_jouer		= $t['mois'];
							
							if ($mois_jouer_tmp == 0) {
								for ($i = 1; $i < $mois_jouer; $i++) {
									array_push($data_log_jouer, 0);
								}
								
								$mois_jouer_tmp = $mois_jouer;
							}
							else {
								for ($i = $mois_jouer_tmp + 1; $i < $mois_jouer; $i++) {
									array_push($data_log_jouer, 0);
								}
								
								$mois_jouer_tmp = $mois_jouer;
							}
							
							array_push($data_log_jouer, $nb_logs_jouer);
						}
						
						// page evenement.php
						$sql = "SELECT COUNT(*) as nb_logs_evenement, MONTH(date_acces) as mois
									FROM acces_log
									WHERE id_perso='$id_perso_select'
									AND page LIKE 'evenement.php%'
									AND YEAR(date_acces) = '$annee'
									GROUP BY MONTH(date_acces)";
						$res = $mysqli->query($sql);
						
						while ($t = $res->fetch_assoc()) {
							$nb_logs_evenement 	= $t['nb_logs_evenement'];
							$mois_evenement		= $t['mois'];
							
							if ($mois_evenement_tmp == 0) {
								for ($i = 1; $i < $mois_evenement; $i++) {
									array_push($data_log_evenement, 0);
								}
								
								$mois_evenement_tmp = $mois_evenement;
							}
							else {
								for ($i = $mois_evenement_tmp + 1; $i < $mois_evenement; $i++) {
									array_push($data_log_evenement, 0);
								}
								
								$mois_evenement_tmp = $mois_evenement;
							}
							
							array_push($data_log_evenement, $nb_logs_evenement);
						}
						
						// page afficher_carte.php
						$sql = "SELECT COUNT(*) as nb_logs_carte, MONTH(date_acces) as mois
									FROM acces_log
									WHERE id_perso='$id_perso_select'
									AND page LIKE 'evenement.php%'
									AND YEAR(date_acces) = '$annee'
									GROUP BY MONTH(date_acces)";
						$res = $mysqli->query($sql);
						
						while ($t = $res->fetch_assoc()) {
							$nb_logs_carte 	= $t['nb_logs_carte'];
							$mois_carte		= $t['mois'];
							
							if ($mois_carte_tmp == 0) {
								for ($i = 1; $i < $mois_carte; $i++) {
									array_push($data_log_carte, 0);
								}
								
								$mois_carte_tmp = $mois_carte;
							}
							else {
								for ($i = $mois_carte_tmp + 1; $i < $mois_carte; $i++) {
									array_push($data_log_carte, 0);
								}
								
								$mois_carte_tmp = $mois_carte;
							}
							
							array_push($data_log_carte, $nb_logs_carte);
						}
						
						echo "<canvas id='chBarAnnee'></canvas>";
					}
					?>
				</div>
			</div>
			<div class="col-md-2"></div>
		</div>
		
		<!-- Optional JavaScript -->
		<!-- jQuery first, then Popper.js, then Bootstrap JS -->
		<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
		<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.bundle.min.js "></script>
		
		<script>
		// chart colors
		var colors = ['#007bff','#28a745','#333333','#c3e6cb','#dc3545','#6c757d'];		
		
		<?php
		if (isset($_GET['graph_jour']) && isset($_GET['jour']) && isset($_GET['mois']) && isset($_GET['annee'])) {
		?>
		var chBarJour = document.getElementById("chBarJour");
		if (chBarJour) {
			new Chart(chBarJour, {
				type: 'bar',
				data: {
					labels: ["0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "10", "11", "12", "13", "14", "15", "16", "17", "18", "19", "20", "21", "22", "23"],
					datasets: [{
						label: 'jouer.php',
						data: <?php echo json_encode($data_log_jouer); ?>,
						backgroundColor: colors[0]
					},
					{
						label: 'evenement.php',
						data: <?php echo json_encode($data_log_evenement); ?>,
						backgroundColor: colors[1]
					},
					{
						label: 'afficher_carte.php',
						data: <?php echo json_encode($data_log_carte); ?>,
						backgroundColor: colors[2]
					}]
				},
				options: {
					legend: {
						display: true
					},
					scales: {
						xAxes: [{
							barPercentage: 0.4,
							categoryPercentage: 0.5
						}]
					}
				}
			});
		}
		<?php
		}
		elseif (isset($_GET['graph_mois']) && isset($_GET['mois']) && isset($_GET['annee'])) {
		?>
		var chBarMois = document.getElementById("chBarMois");
		if (chBarMois) {
			new Chart(chBarMois, {
				type: 'bar',
				data: {
					labels: <?php echo json_encode($data_jours_mois); ?>,
					datasets: [{
						label: 'jouer.php',
						data: <?php echo json_encode($data_log_jouer); ?>,
						backgroundColor: colors[0]
					},
					{
						label: 'evenement.php',
						data: <?php echo json_encode($data_log_evenement); ?>,
						backgroundColor: colors[1]
					},
					{
						label: 'afficher_carte.php',
						data: <?php echo json_encode($data_log_carte); ?>,
						backgroundColor: colors[2]
					}]
				},
				options: {
					legend: {
						display: true
					},
					scales: {
						xAxes: [{
							barPercentage: 0.4,
							categoryPercentage: 0.5
						}]
					}
				}
			});
		}
		<?php
		}
		elseif (isset($_GET['graph_annee']) && isset($_GET['annee'])) {
		?>
		var chBarAnnee = document.getElementById("chBarAnnee");
		if (chBarAnnee) {
			new Chart(chBarAnnee, {
				type: 'bar',
				data: {
					labels: ["Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Aout", "Septembre", "Octobre", "Novembre", "Décembre"],
					datasets: [{
						label: 'jouer.php',
						data: <?php echo json_encode($data_log_jouer); ?>,
						backgroundColor: colors[0]
					},
					{
						label: 'evenement.php',
						data: <?php echo json_encode($data_log_evenement); ?>,
						backgroundColor: colors[1]
					},
					{
						label: 'afficher_carte.php',
						data: <?php echo json_encode($data_log_carte); ?>,
						backgroundColor: colors[2]
					}]
				},
				options: {
					legend: {
						display: true
					},
					scales: {
						xAxes: [{
							barPercentage: 0.4,
							categoryPercentage: 0.5
						}]
					}
				}
			});
		}
		<?php
		}
		?>
		
		/*
		var ctx = document.getElementById('myChart').getContext('2d');
		var myChart = new Chart(ctx, {
			type: 'bar',
			data: {
				labels: ['Red', 'Blue', 'Yellow', 'Green', 'Purple', 'Orange'],
				datasets: [{
					label: 'nombre de logs',
					data: [12, 19, 3, 5, 2, 3],
					backgroundColor: [
						'rgba(255, 99, 132, 0.2)',
						'rgba(54, 162, 235, 0.2)',
						'rgba(255, 206, 86, 0.2)',
						'rgba(75, 192, 192, 0.2)',
						'rgba(153, 102, 255, 0.2)',
						'rgba(255, 159, 64, 0.2)'
					],
					borderColor: [
						'rgba(255, 99, 132, 1)',
						'rgba(54, 162, 235, 1)',
						'rgba(255, 206, 86, 1)',
						'rgba(75, 192, 192, 1)',
						'rgba(153, 102, 255, 1)',
						'rgba(255, 159, 64, 1)'
					],
					borderWidth: 1
				}]
			},
			options: {
				scales: {
					yAxes: [{
						ticks: {
							beginAtZero: true
						}
					}]
				}
			}
		});
		*/
		</script>
	</body>
</html>
<?php
	}
}
else{
	echo "<font color=red>Vous ne pouvez pas acceder a cette page, veuillez vous logguer.</font>";
}?>
