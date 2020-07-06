<?php
session_start();
require_once("../fonctions.php");
require_once("f_carte.php");

$mysqli = db_connexion();

include ('../nb_online.php');

if(isset($_SESSION["id_perso"])){
	
	$id_perso = $_SESSION['id_perso'];
	
	// recupération config jeu
	$admin = admin_perso($mysqli, $id_perso);
	
	if($admin){
		
		$mess_err 	= "";
		$mess 		= "";
		
		if(isset($_POST['select_perso']) && $_POST['select_perso'] != '') {
			
			$id_perso_select = $_POST['select_perso'];
			
		}
		
		if (isset($_GET['consulter_mp'])) {
			
			$id_perso_select = $_GET['consulter_mp'];
			
		}
		
		if (isset($_POST['id_perso_select']) && $_POST['id_perso_select'] != '') {
			
			$id_perso_select = $_POST['id_perso_select'];
			
			if (isset($_POST['xp_perso']) && trim($_POST['xp_perso']) != '') {
				
				$new_xp_perso = $_POST['xp_perso'];
				
				$mess = "MAJ XP perso matricule ".$id_perso_select." vers ".$new_xp_perso;
				
				$sql = "UPDATE perso SET xp_perso=$new_xp_perso WHERE id_perso='$id_perso_select'";
				$mysqli->query($sql);
			}
			
			if (isset($_POST['pi_perso']) && trim($_POST['pi_perso']) != '') {
				
				$new_pi_perso = $_POST['pi_perso'];
				
				$mess = "MAJ PI perso matricule ".$id_perso_select." vers ".$new_pi_perso;
				
				$sql = "UPDATE perso SET pi_perso=$new_pi_perso WHERE id_perso='$id_perso_select'";
				$mysqli->query($sql);
			}
			
			if (isset($_POST['pc_perso']) && trim($_POST['pc_perso']) != '') {
				
				$new_pc_perso = $_POST['pc_perso'];
				
				$mess = "MAJ PC perso matricule ".$id_perso_select." vers ".$new_pc_perso;
				
				$sql = "UPDATE perso SET pc_perso=$new_pc_perso WHERE id_perso='$id_perso_select'";
				$mysqli->query($sql);
			}
			
			if (isset($_POST['or_perso']) && trim($_POST['or_perso']) != '') {
				
				$new_or_perso = $_POST['or_perso'];
				
				$mess = "MAJ THUNE perso matricule ".$id_perso_select." vers ".$new_or_perso;
				
				$sql = "UPDATE perso SET or_perso=$new_or_perso WHERE id_perso='$id_perso_select'";
				$mysqli->query($sql);
			}
			
			if (isset($_POST['pv_perso']) && trim($_POST['pv_perso']) != '') {
				
				$new_pv_perso = $_POST['pv_perso'];
				
				$mess = "MAJ PV perso matricule ".$id_perso_select." vers ".$new_pv_perso;
				
				$sql = "UPDATE perso SET pv_perso=$new_pv_perso WHERE id_perso='$id_perso_select'";
				$mysqli->query($sql);
			}
			
			if (isset($_POST['pm_perso']) && trim($_POST['pm_perso']) != '') {
				
				$new_pm_perso = $_POST['pm_perso'];
				
				$mess = "MAJ PM perso matricule ".$id_perso_select." vers ".$new_pm_perso;
				
				$sql = "UPDATE perso SET pm_perso=$new_pm_perso WHERE id_perso='$id_perso_select'";
				$mysqli->query($sql);
			}
			
			if (isset($_POST['pa_perso']) && trim($_POST['pa_perso']) != '') {
				
				$new_pa_perso = $_POST['pa_perso'];
				
				$mess = "MAJ PA perso matricule ".$id_perso_select." vers ".$new_pv_perso;
				
				$sql = "UPDATE perso SET pa_perso=$new_pa_perso WHERE id_perso='$id_perso_select'";
				$mysqli->query($sql);
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
						<h2>Administration</h2>
					</div>
				</div>
			</div>
			
			<p align="center"><a class="btn btn-primary" href="admin_nvs.php">Retour à l'administration</a> <a class="btn btn-primary" href="jouer.php">Retour au jeu</a></p>
			
			<div class="row">
				<div class="col-12">
				
					<h3>Administration des persos</h3>
					
					<center><font color='red'><?php echo $mess_err; ?></font></center>
					<center><font color='blue'><?php echo $mess; ?></font></center>
					
					<form method='POST' action='admin_perso.php'>
					
						<select name="select_perso" onchange="this.form.submit()">
						
							<?php
							$sql = "SELECT id_perso, nom_perso, x_perso, y_perso FROM perso ORDER BY id_perso ASC";
							$res = $mysqli->query($sql);
							
							while ($t = $res->fetch_assoc()) {
								
								$id_perso 	= $t["id_perso"];
								$nom_perso 	= $t["nom_perso"];
								$x_perso	= $t["x_perso"];
								$y_perso 	= $t["y_perso"];
								
								echo "<option value='".$id_perso."'";
								if (isset($id_perso_select) && $id_perso_select == $id_perso) {
									echo " selected";
								}
								echo ">".$nom_perso." [".$id_perso."] - ".$x_perso."/".$y_perso."</option>";
							}
							?>
						
						</select>
						
						<input type="submit" value="choisir">
						
					</form>
					
					<?php
					if (isset($id_perso_select) && $id_perso_select != 0) {
						
						$sql = "SELECT * FROM perso WHERE id_perso='$id_perso_select'";
						$res = $mysqli->query($sql);
						$t = $res->fetch_assoc();
						
						$nom_perso 	= $t['nom_perso'];
						$xp_perso 	= $t['xp_perso'];
						$pc_perso 	= $t['pc_perso'];
						$pi_perso	= $t['pi_perso'];
						$pv_perso 	= $t['pv_perso'];
						$pm_perso 	= $t['pm_perso'];
						$pa_perso	= $t['pa_perso'];
						$or_perso 	= $t['or_perso'];
						$camp_perso	= $t['clan'];
						
						if ($camp_perso == 1) {
							$nom_camp_perso 	= "Nord";
							$couleur_camp_perso	= "blue";
						}
						else if ($camp_perso == 2) {
							$nom_camp_perso 	= "Sud";
							$couleur_camp_perso	= "red";
						}
						else if ($camp_perso == 2) {
							$nom_camp_perso 	= "Indiens";
							$couleur_camp_perso	= "green";
						}
						else {
							$nom_camp_perso 	= "Outlaw";
							$couleur_camp_perso	= "black";
						}
						
						$im_camp_perso = $nom_camp_perso.".gif";
						
						echo "<br />";
						echo "<table border='1' width='100%'>";
						echo "	<tr>";
						echo "		<td align='center'><img src='../images/".$im_camp_perso."'></td>";
						echo "		<td align='center'><b>Nom : </b>".$nom_perso."</td>";
						echo "<form method='POST' action='admin_perso.php'>";
						echo "		<td align='center'><b>XP : </b><input type='text' name='xp_perso' value='".$xp_perso."' ><input type='hidden' value='".$id_perso_select."' name='id_perso_select'><input type='submit' value='modifier'></td>";
						echo "</form>";
						echo "<form method='POST' action='admin_perso.php'>";
						echo "		<td align='center'><b>PI : </b><input type='text' name='pi_perso' value='".$pi_perso."' ><input type='hidden' value='".$id_perso_select."' name='id_perso_select'><input type='submit' value='modifier'></td>";
						echo "</form>";
						echo "<form method='POST' action='admin_perso.php'>";
						echo "		<td align='center'><b>PC : </b><input type='text' name='pc_perso' value='".$pc_perso."' ><input type='hidden' value='".$id_perso_select."' name='id_perso_select'><input type='submit' value='modifier'></td>";
						echo "</form>";
						echo "<form method='POST' action='admin_perso.php'>";
						echo "		<td align='center'><b>THUNE : </b><input type='text' name='or_perso' value='".$or_perso."' ><input type='hidden' value='".$id_perso_select."' name='id_perso_select'><input type='submit' value='modifier'></td>";
						echo "</form>";
						echo "	</tr>";
						echo "	<tr>";
						echo "		<td></td><td></td>";
						echo "<form method='POST' action='admin_perso.php'>";
						echo "		<td align='center'><b>PV : </b><input type='text' name='pv_perso' value='".$pv_perso."' ><input type='hidden' value='".$id_perso_select."' name='id_perso_select'><input type='submit' value='modifier'></td>";
						echo "</form>";
						echo "<form method='POST' action='admin_perso.php'>";
						echo "		<td align='center'><b>PM : </b><input type='text' name='pm_perso' value='".$pm_perso."' ><input type='hidden' value='".$id_perso_select."' name='id_perso_select'><input type='submit' value='modifier'></td>";
						echo "</form>";
						echo "<form method='POST' action='admin_perso.php'>";
						echo "		<td align='center'><b>PA : </b><input type='text' name='pa_perso' value='".$pa_perso."' ><input type='hidden' value='".$id_perso_select."' name='id_perso_select'><input type='submit' value='modifier'></td>";
						echo "</form>";
						echo "	</tr>";
						echo "</table>";
						
						if (isset($_GET['consulter_mp'])) {
							
							echo "<br />";
							echo "<table border='1' width='100%'>";
							echo "	<tr>";
							echo "		<th style='text-align:center'>Date</th><th style='text-align:center'>Objet</th><th style='text-align:center'>Contenu</th>";
							echo "	</tr>";
							
							$sql_mp = "SELECT * FROM message WHERE expediteur_message='".$nom_perso."' ORDER BY id_message DESC";
							$res_mp = $mysqli->query($sql_mp);
							while ($t_mp = $res_mp->fetch_assoc()) {
								
								$date_mp 	= $t_mp['date_message'];
								$contenu_mp = $t_mp['contenu_message'];
								$objet_mp 	= $t_mp['objet_message'];
								$id_mp		= $t_mp['id_message'];
								
								echo "	<tr>";
								echo "		<td>".$date_mp."</td>";
								echo "		<td>".$objet_mp."</td>";
								echo "		<td>".$contenu_mp."</td>";
								echo "	</tr>";
							}
							
							echo "	</tr>";
							echo "</table>";
							
						}
						else {
							echo "<br /><a href='admin_perso.php?consulter_mp=".$id_perso_select."' class='btn btn-primary'>Consulter les MP du perso</a>";
						}
					}
					?>
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
		
		header("Location:../index.php");
	}
}
else{
	echo "<font color=red>Vous ne pouvez pas accéder à cette page, veuillez vous loguer.</font>";
}