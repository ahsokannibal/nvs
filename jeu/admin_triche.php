<?php
session_start();
require_once("../fonctions.php");

$mysqli = db_connexion();

include ('../nb_online.php');

if(isset($_SESSION["id_perso"])){
	
	$id_perso = $_SESSION['id_perso'];
	
	// recupération config jeu
	$admin = admin_perso($mysqli, $id_perso);
	
	if($admin){
		
		
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
						<h2>Adminstration</h2>
					</div>
				</div>
			</div>
			
			<p align="center"><a class="btn btn-primary" href="jouer.php">Retour au jeu</a></p>
			
			<div class="row">
				<div class="col-12">
				
					<div align='center'><h3>Joueurs ayant le même mot de passe</h3></div>
					
					<div id="table_mdp" class="table-responsive">
						<table border="1" width='100%'>
							<tr>
								<th style='text-align:center'>mdp (hash)</th><th style='text-align:center'>Liste des joueurs</th>
							</tr>
							<?php
							$mdp_tmp = "";
							
							$sql = "SELECT DISTINCT j1.id_joueur, j1.email_joueur, j1.mdp_joueur
									FROM joueur j1
									JOIN joueur j2 ON j1.mdp_joueur = j2.mdp_joueur AND j1.id_joueur <> j2.id_joueur
									AND j1.id_joueur > 4
									ORDER BY j1.mdp_joueur, j1.id_joueur";
							$res = $mysqli->query($sql);
							while ($t = $res->fetch_assoc()) {
								
								$id_joueur 		= $t["id_joueur"];
								$email_joueur	= $t["email_joueur"];
								$mdp_joueur		= $t["mdp_joueur"];
								
								if ($mdp_tmp != $mdp_joueur) {
									
									if ($mdp_tmp != "") {
										echo "</tr>";
									}
									
									echo "<tr>";
									echo "	<td align='center'>".$mdp_joueur."</td><td>";
									
									$mdp_tmp = $mdp_joueur;
								}
								
								echo "Joueur id : ".$id_joueur." - ";
								
								// récupération du perso chef du joueur 
								$sql_p = "SELECT id_perso, nom_perso, clan FROM perso WHERE idJoueur_perso='$id_joueur' AND chef='1'";
								$res_p = $mysqli->query($sql_p);
								$t_p = $res_p->fetch_assoc();
									
								$id_p 	= $t_p["id_perso"];
								$nom_p	= $t_p["nom_perso"];
								$camp_p	= $t_p["clan"];
								
								if ($camp_p == 1) {
									$color_p = "blue";
								} else if ($camp_p == 2) {
									$color_p = "red";
								} else {
									$color_p = "black";
								}
									
								echo "<font color='$color_p'>".$nom_p." [".$id_p."]</font> - ";
								
								echo "Email : ".$email_joueur."<br />";
							}				
									 
							?>
								</td>
							</tr>
						</table>
					</div>
				</div>
			</div>
			
			<br />
			
			<div class="row">
				<div class="col-12">
				
					<div align='center'><h3>Joueurs ayant un mail se ressemblant</h3></div>
					
					<div id="table_mdp" class="table-responsive">
						<table border="1" width='100%'>
							<tr>
								<th style='text-align:center'>Mail joueur 1</th><th style='text-align:center'>Mail joueur 2</th><th style='text-align:center'>Inférence Basique</th>
							</tr>
							<?php
							
							$sql = "SELECT j1.id_joueur as id_joueur1, j2.id_joueur as id_joueur2, j1.email_joueur as email_joueur1, j2.email_joueur as email_joueur2, distance_inference_basique(j1.email_joueur, j2.email_joueur) as ib FROM joueur j1, joueur j2
									WHERE j1.id_joueur != j2.id_joueur
									AND distance_inference_basique(j1.email_joueur, j2.email_joueur) > 15
									AND j1.id_joueur > 4 AND j2.id_joueur > 4
									ORDER BY j1.id_joueur";
							$res = $mysqli->query($sql);
							while ($t = $res->fetch_assoc()) {
								
								$id_joueur1 	= $t["id_joueur1"];
								$id_joueur2 	= $t["id_joueur2"];
								$email_joueur1 	= $t["email_joueur1"];
								$email_joueur2 	= $t["email_joueur2"];
								$inference_bas	= $t["ib"];
								
								if ($id_joueur2 > $id_joueur1) {
									echo "<tr>";
									
									echo "<td> Joueur id : ".$id_joueur1." - email : ".$email_joueur1."</td>";
									echo "<td> Joueur id : ".$id_joueur2." - email : ".$email_joueur2."</td>";
									echo "<td>".$inference_bas."</td>";
									
									echo "</tr>";
								}
							}				
									 
							?>
								</td>
							</tr>
						</table>
					</div>
				</div>
			</div>
			
			<br />
			
			<div class="row">
				<div class="col-12">
				
					<div align='center'><h3>Joueurs ayant la même IP le même jour</h3></div>
					
					<div id="table_ip" class="table-responsive">
						<table border="1" width='100%'>
							<tr>
								<th style='text-align:center'>IP</th><th style='text-align:center'>Liste des joueurs se connectant le même jour sur la même IP</th>
							</tr>
							<?php
							$ip_tmp = "";
							
							$sql = "SELECT DISTINCT j1.ip_joueur, j1.id_joueur, j1.date_premier_releve, j1.date_dernier_releve
									FROM joueur_as_ip j1
									JOIN joueur_as_ip j2 ON j1.ip_joueur = j2.ip_joueur AND j1.id_joueur <> j2.id_joueur
									AND j1.id_joueur > 4 AND j2.id_joueur > 4
									AND (DATEDIFF(j1.date_premier_releve, j2.date_premier_releve) = 0 
											OR DATEDIFF(j1.date_dernier_releve, j2.date_dernier_releve) = 0
											OR DATEDIFF(j1.date_premier_releve, j2.date_dernier_releve) = 0
											OR DATEDIFF(j1.date_dernier_releve, j2.date_premier_releve) = 0)
									ORDER BY j1.ip_joueur, j1.id_joueur";
							$res = $mysqli->query($sql);
							while ($t = $res->fetch_assoc()) {
								
								$id_joueur 	= $t["id_joueur"];
								$ip_joueur	= $t["ip_joueur"];
								$date_pr 	= $t["date_premier_releve"];
								$date_dr 	= $t["date_dernier_releve"];
								
								if ($ip_tmp != $ip_joueur) {
									
									if ($ip_tmp != "") {
										echo "</tr>";
									}
									
									echo "<tr>";
									echo "	<td align='center'>".$ip_joueur."</td><td>";
									
									$ip_tmp = $ip_joueur;
								}
								
								echo "Joueur id : ".$id_joueur." - ";
								
								// récupération du perso chef du joueur 
								$sql_p = "SELECT id_perso, nom_perso, clan FROM perso WHERE idJoueur_perso='$id_joueur' AND chef='1'";
								$res_p = $mysqli->query($sql_p);
								$t_p = $res_p->fetch_assoc();
									
								$id_p 	= $t_p["id_perso"];
								$nom_p	= $t_p["nom_perso"];
								$camp_p	= $t_p["clan"];
								
								if ($camp_p == 1) {
									$color_p = "blue";
								} else if ($camp_p == 2) {
									$color_p = "red";
								} else {
									$color_p = "black";
								}
									
								echo "<font color='$color_p'>".$nom_p." [".$id_p."]</font>";
								echo " - Date premier relevé : ".$date_pr." - Date dernier relevé : ".$date_dr; 
								echo "<br />";
							}				
									 
							?>
								</td>
							</tr>
						</table>
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
		// logout
		$_SESSION = array(); // On écrase le tableau de session
		session_destroy(); // On détruit la session
		
		header("Location:../index2.php");
	}
}
else{
	echo "<font color=red>Vous ne pouvez pas accéder à cette page, veuillez vous loguer.</font>";
}
?>