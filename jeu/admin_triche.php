<?php
session_start();
require_once("../fonctions.php");

$mysqli = db_connexion();

include ('../nb_online.php');

function echo_chef($mysqli, $id_joueur){
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
}

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
						<h2>Administration</h2>
					</div>
				</div>
			</div>
			
			<p align="center"><a class="btn btn-primary" href="jouer.php">Retour au jeu</a></p>
			
			<div class="row">
				<div class="col-12">
					<div align="center">
						<a href='admin_triche.php?affiche=all' class='btn btn-warning'>Tout afficher</a>
						<a href='admin_triche.php?affiche=pwd' class='btn btn-warning'>Tableau mot de passe identifiques</a>
						<a href='admin_triche.php?affiche=email' class='btn btn-warning'>Tableau emails proches</a>
						<a href='admin_triche.php?affiche=ip2' class='btn btn-warning'>Tableau connexions même IP</a>
						<a href='admin_triche.php?affiche=connexions' class='btn btn-warning'>Connexions</a>
						<a href='admin_triche.php?affiche=cookie' class='btn btn-warning'>Tableau connexions même cookie</a>
						<a href='admin_triche.php?affiche=whitelist' class='btn btn-warning'>Whiteliste</a>
					</div>
				</div>
			</div>
			
			<?php
			if (isset($_GET["affiche"]) && ($_GET["affiche"] == "all" || $_GET["affiche"] == "pwd")) {
			?>
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
			
			<?php
			}
			if (isset($_GET["affiche"]) && ($_GET["affiche"] == "all" || $_GET["affiche"] == "email")) {
			?>
			<div class="row">
				<div class="col-12">
				
					<div align='center'><h3>Joueurs ayant un mail se ressemblant</h3></div>
					
					<div id="table_mdp" class="table-responsive">
						<table border="1" width='100%'>
							<tr>
								<th style='text-align:center'>Mail joueur 1</th><th style='text-align:center'>Mail joueur 2</th><th style='text-align:center'>Inférence Basique</th>
							</tr>
							<?php
							
							$sql = "SELECT j1.id_joueur as id_joueur1, j2.id_joueur as id_joueur2, j1.email_joueur as email_joueur1, j2.email_joueur as email_joueur2, distance_inference_basique(left(j1.email_joueur, length(j1.email_joueur)-INSTR(j1.email_joueur, '@')), left(j2.email_joueur, length(j2.email_joueur)-INSTR(j2.email_joueur, '@'))) as ib FROM joueur j1, joueur j2
									WHERE j1.id_joueur != j2.id_joueur
									AND distance_inference_basique(left(j1.email_joueur, length(j1.email_joueur)-INSTR(j1.email_joueur, '@')), left(j2.email_joueur, length(j2.email_joueur)-INSTR(j2.email_joueur, '@'))) > 4
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
			
			<?php
			}
			if (isset($_GET["affiche"]) && ($_GET["affiche"] == "all" || $_GET["affiche"] == "ip2")) {
				$offset = 0;
				if (isset($_GET["offset"]))
					$offset = $_GET["offset"];
			?>
			<div class="row">
				<div class="col-12">
				
					<div align='center'><h3>Joueurs ayant la même IP</h3></div>
					<?php
						$prev = max(0, $offset-100);
						$next = $offset+100;
						echo "<a href='admin_triche.php?affiche=ip2&offset=$prev'>prev</a>  ";
						echo "<a href='admin_triche.php?affiche=ip2&offset=$next'>next</a>  ";
					?>
					<div id="table_ip" class="table-responsive">
						<table border="1" width='100%'>


							<tr>
								<th style='text-align:center'>IP</th><th style='text-align:center'>Liste des joueurs se connectant le même jour sur la même IP</th>
							</tr>
							<?php

							$sql = "SELECT ip_joueur, id_joueur, time FROM user_ok_logins WHERE (id_joueur NOT IN (SELECT id_joueur FROM whitelist_triche)) ORDER BY time DESC limit 100 offset $offset";
							$res = $mysqli->query($sql);
							while ($t = $res->fetch_assoc()) {
								$id_joueur 	= $t["id_joueur"];
								$ip_joueur	= $t["ip_joueur"];
								$time		= $t["time"];

								$disp = False;
								$sql2 = "SELECT ip_joueur, id_joueur, time FROM user_ok_logins WHERE ip_joueur='$ip_joueur' AND id_joueur!=$id_joueur AND (id_joueur NOT IN (SELECT id_joueur FROM whitelist_triche)) ORDER BY time DESC limit 10";
								$res2 = $mysqli->query($sql2);
								while ($t2 = $res2->fetch_assoc()) {
									if (!$disp) {
										$disp = True;
										echo "<tr><td>$ip_joueur</td><td>";
										echo "$time - ";
										echo_chef($mysqli, $id_joueur);
										echo "<br />";
									}
									$id_joueur2 	= $t2["id_joueur"];
									$ip_joueur2	= $t2["ip_joueur"];
									$time2		= $t2["time"];
									echo "$time2 - ";
									echo_chef($mysqli, $id_joueur2);
									echo "<br />";
								}


								if ($disp) {
									echo "</td></tr>";
								}
							}

							?>
						</table>
					</div>
				</div>
			</div>
			<?php
			}
			if (isset($_GET["affiche"]) && ($_GET["affiche"] == "all" || $_GET["affiche"] == "connexions")) {
			?>
			<div class="row">
				<div class="col-12">

					<div align='center'><h3>Connexions</h3></div>

					<?php
					if(isset($_GET['select_perso']) && $_GET['select_perso'] != '') {
						$id_perso_select = $_GET['select_perso'];
					}
					?>

					<form method='GET' action='admin_triche.php'>
						<input type="hidden" id="affiche" name="affiche" value="connexions">
						<select name="select_perso">
							<?php
							$sql = "SELECT id_perso, nom_perso FROM perso WHERE chef=1 ORDER BY id_perso ASC";
							$res = $mysqli->query($sql);
							while ($t = $res->fetch_assoc()) {
								$id_perso 	= $t["id_perso"];
								$nom_perso 	= $t["nom_perso"];
								echo "<option value='".$id_perso."'";
								if (isset($id_perso_select) && $id_perso_select == $id_perso) {
									echo " selected";
								}
								echo ">".$nom_perso." [".$id_perso."] </option>";
							}
							?>
						</select>
						<button>Selectionner</button>
					</form>

					<div id="table_ip" class="table-responsive">
						<table border="1" width='100%'>
							<tr>
								<th style='text-align:center'>Connexions</th><th style='text-align:center'>VPN</th>
							</tr>
							<?php

							if (isset($id_perso_select) && $id_perso_select != 0) {
								$sql = "SELECT ip_joueur, id_joueur, time, user_agent, cookie_val, nom_perso, id_perso, clan FROM user_ok_logins JOIN perso ON id_joueur=idJoueur_perso WHERE id_perso='$id_perso_select' ORDER BY time DESC";
								$res = $mysqli->query($sql);
								while ($t = $res->fetch_assoc()) {
									echo "<tr> <td>";
									$time 		= $t["time"];
									$cookie_val 	= $t["cookie_val"];
									$nom_perso 	= $t["nom_perso"];
									$id_perso 	= $t["id_perso"];
									$clan 		= $t["clan"];
									$ip_joueur 	= $t["ip_joueur"];
									$id_joueur 	= $t["id_joueur"];
									$user_agent 		= $t["user_agent"];
									$color_p = "black";
									if ($clan == 1) {
										$color_p = "blue";
									} else if ($clan == 2) {
										$color_p = "red";
									}
									echo "".$time.", id_joueur : $id_joueur";
									echo ", <font color='$color_p'>".$nom_perso." [".$id_perso."]</font>";
									echo ", ip : ".$ip_joueur.", user-agent : '".$user_agent."' <br />";
									echo "</td><td>";
									echo "<a href='https://www.ipqualityscore.com/vpn-ip-address-check/lookup/$ip_joueur'>check</a>";
									echo "</td></tr>";
								}
							}
							?>
						</table>
					</div>
				</div>
			</div>
			<?php
			}
			if (isset($_GET["affiche"]) && ($_GET["affiche"] == "all" || $_GET["affiche"] == "cookie")) {
				if (isset($_GET['acquitter'])) {
					$acquitter = filter_input(INPUT_GET, "acquitter", FILTER_SANITIZE_STRING);
					$sql = "UPDATE user_ok_logins SET est_acquitte=1 WHERE cookie_val='$acquitter'";
					$res = $mysqli->query($sql);
				}
			?>
			<div class="row">
				<div class="col-12">

					<div align='center'><h3>Joueurs ayant la même valeur de cookie</h3></div>

					<div id="table_ip" class="table-responsive">
						<table border="1" width='100%'>
							<tr>
								<th style='text-align:center'>Cookie</th><th style='text-align:center'>Liste des joueurs se connectant avec la même valeur de cookie</th>
							</tr>
							<?php
							$ip_tmp = "";

							$sql = "SELECT cookie_val, COUNT(distinct id_joueur) as count FROM user_ok_logins WHERE est_acquitte=0 AND id_joueur NOT IN (SELECT id_joueur FROM whitelist_triche) GROUP BY cookie_val HAVING COUNT(distinct id_joueur) > 1";
							$res = $mysqli->query($sql);
							while ($t = $res->fetch_assoc()) {
								$cookie_val 	= $t["cookie_val"];
								$count 	        = $t["count"];

								if ($ip_tmp != $cookie_val) {

									if ($ip_tmp != "") {
										echo "</tr>";
									}

									echo "<tr>";
									echo "	<td align='center'>".$cookie_val."</td><td>";

									$ip_tmp = $cookie_val;
								}

								$sql = "SELECT ip_joueur, id_joueur, time, user_agent, cookie_val, nom_perso, id_perso, clan FROM user_ok_logins JOIN perso ON id_joueur=idJoueur_perso WHERE chef='1' AND cookie_val='$cookie_val' ORDER BY time DESC";
								$res2 = $mysqli->query($sql);
								while ($t = $res2->fetch_assoc()) {
									$time 		= $t["time"];
									$nom_perso 	= $t["nom_perso"];
									$id_perso 	= $t["id_perso"];
									$clan 		= $t["clan"];
									$ip_joueur 	= $t["ip_joueur"];
									$id_joueur 	= $t["id_joueur"];
									$user_agent 		= $t["user_agent"];
									$color_p = "black";
									if ($clan == 1) {
										$color_p = "blue";
									} else if ($clan == 2) {
										$color_p = "red";
									}
									echo "".$time.", id_joueur : $id_joueur";
									echo ", <font color='$color_p'>".$nom_perso." [".$id_perso."]</font>";
									echo ", ip : ".$ip_joueur.", user-agent : '".$user_agent."' <br />";
								}
								echo "<br />";
								echo "<form action='admin_triche.php' method='GET'>";
								echo "	<input type='hidden' id='affiche' name='affiche' value='cookie'>";
								echo "  <input name='acquitter' id='acquitter' type='hidden' value='$cookie_val'>";
								echo "	<td> <button>Acquitter</button> </td>";
								echo "</form>";
							}
							?>
								</td>
							</tr>
						</table>
					</div>
				</div>
			</div>
			<?php
			}
			if (isset($_GET["affiche"]) && ($_GET["affiche"] == "all" || $_GET["affiche"] == "whitelist")) {
				if (isset($_GET['del_whitelist'])) {
					$del_whitelist = filter_input(INPUT_GET, "del_whitelist", FILTER_SANITIZE_STRING);
					$sql = "DELETE FROM whitelist_triche WHERE id=$del_whitelist";
					$res = $mysqli->query($sql);
				}
				if (isset($_GET['add_whitelist'])) {
					$add_whitelist = filter_input(INPUT_GET, "add_whitelist", FILTER_SANITIZE_STRING);
					$sql = "INSERT INTO whitelist_triche VALUES (0, $add_whitelist)";
					$res = $mysqli->query($sql);
				}
			?>
			<div class="row">
				<div class="col-12">

					<div align='center'><h3>Whiteliste</h3></div>

					<div id="table_ip" class="table-responsive">
						<table border="1" width='100%'>
							<tr>
								<th style='text-align:center'>joueur 1</th>
							</tr>
							<?php
							$sql = "SELECT * FROM whitelist_triche";
							$res = $mysqli->query($sql);
							while ($t = $res->fetch_assoc()) {
								$id 	= $t["id"];
								$id_joueur1 	= $t["id_joueur"];

								$sql = "SELECT nom_perso, id_perso, clan FROM perso WHERE idJoueur_perso=$id_joueur1 AND chef='1'";
								$res2 = $mysqli->query($sql);
								$t = $res2->fetch_assoc();
								$nom_perso 	= $t["nom_perso"];
								$id_perso 	= $t["id_perso"];
								$clan 		= $t["clan"];

								$color_p = "black";
								if ($clan == 1) {
									$color_p = "blue";
								} else if ($clan == 2) {
									$color_p = "red";
								}

								echo "<tr>";
								echo "	<td align='center'>".$id_joueur1." <font color='$color_p'>".$nom_perso." [".$id_perso."]</font></td>";
								echo "<form action='admin_triche.php' method='GET'>";
								echo "	<input type='hidden' id='affiche' name='affiche' value='whitelist'>";
								echo "	<input name='del_whitelist' id='del_whitelist' type='hidden' value='$id'>";
								echo "	<td> <button>Supprimer</button> </td>";
								echo "</form>";
								echo "</tr>";
							}
							?>

							<form action="admin_triche.php" method="GET">
 							<input type="hidden" id="affiche" name="affiche" value="whitelist">
							<tr>
							<td align='center'><input name='add_whitelist' id='add_whitelist' type='number'></td>
							<td> <button>Ajouter</button> </td>
							<tr>
							</form>
						</table>
					</div>
				</div>
			</div>
			<?php
			}
			?>
			
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
