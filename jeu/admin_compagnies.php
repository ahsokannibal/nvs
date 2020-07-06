<?php
session_start();
require_once("../fonctions.php");

$mysqli = db_connexion();

include ('../nb_online.php');
include ('../forum/config.php');

if(isset($_SESSION["id_perso"])){
	
	$id_perso = $_SESSION['id_perso'];
	
	// recupération config jeu
	$admin = admin_perso($mysqli, $id_perso);
	
	if($admin){
		
		$mess_err 	= "";
		$mess 		= "";
		
		if(isset($_POST['select_compagnie']) && $_POST['select_compagnie'] != '') {
			
			$id_compagnie_select = $_POST['select_compagnie'];
			
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
				
					<h3>Administration des compagnies</h3>
					
					<center><font color='red'><?php echo $mess_err; ?></font></center>
					<center><font color='blue'><?php echo $mess; ?></font></center>
					
					<form method='POST' action='admin_compagnies.php'>
					
						<select name="select_compagnie" onchange="this.form.submit()">
						
							<?php
							$sql = "SELECT id_compagnie, nom_compagnie, id_clan FROM compagnies ORDER BY id_compagnie ASC";
							$res = $mysqli->query($sql);
							
							while ($t = $res->fetch_assoc()) {
								
								$id_compagnie 	= $t["id_compagnie"];
								$nom_compagnie 	= $t["nom_compagnie"];
								$id_clan		= $t["id_clan"];
								
								echo "<option value='".$id_compagnie."'";
								if (isset($id_compagnie_select) && $id_compagnie_select == $id_compagnie) {
									echo " selected";
								}
								echo ">".$nom_compagnie." [".$id_compagnie."] - ".$id_clan."</option>";
							}
							?>
						
						</select>
						
						<input type="submit" value="choisir">
						
					</form>
				</div>
			</div>
			
			<div class="row">
				<div class="col-12">
					<div align="center">					
						<div id="table_batiments" class="table-responsive">	
					
						<?php 
						if (isset($id_compagnie_select)) {
							
							$sql = "SELECT nom_compagnie, id_clan, montant
									FROM compagnies, banque_as_compagnie
									WHERE compagnies.id_compagnie = banque_as_compagnie.id_compagnie
									AND compagnies.id_compagnie='$id_compagnie_select'";
							$res = $mysqli->query($sql);
							$t = $res->fetch_assoc();
							
							$nom_compagnie 	= $t['nom_compagnie'];
							$id_clan		= $t["id_clan"];
							$montant_banque	= $t["montant"];
							
							echo "<h3>".$nom_compagnie."</h3>";
							echo "".$montant_banque." thunes";

							$sql = "SELECT nom_perso, perso_in_compagnie.id_perso, attenteValidation_compagnie, nom_poste 
									FROM perso_in_compagnie, perso, poste
									WHERE perso_in_compagnie.id_perso = perso.id_perso
									AND perso_in_compagnie.poste_compagnie = poste.id_poste
									AND perso_in_compagnie.id_compagnie='$id_compagnie_select'";
							$res = $mysqli->query($sql);
							
							echo "<table class='table'>";
							echo "	<thead>";
							echo "		<tr>";
							echo "			<th>Perso</th><th>Poste</th><th>Action</th>";
							echo "		</tr>";
							echo "	</thead>";
							echo "	<tbody>";
							
							while ($t = $res->fetch_assoc()) {
								
								$id_perso						= $t['id_perso'];
								$nom_perso						= $t['nom_perso'];
								$poste_perso_compagnie			= $t['nom_poste'];
								$attenteValidation_compagnie	= $t['attenteValidation_compagnie'];
								
								echo "		<tr>";
								echo "			<td>".$nom_perso." [".$id_perso."]</td>";
								echo "			<td>".$poste_perso_compagnie."</td>";
								echo "			<td></td>";
								echo "		</tr>";
							}
							
							echo "	</tbody>";
						}
						?>
						
						</div>
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
}
else{
	echo "<font color=red>Vous ne pouvez pas acceder a cette page, veuillez vous logguer.</font>";
}?>
