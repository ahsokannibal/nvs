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
						
						<center><font color='red'><?php echo $mess_err; ?></font></center>
						<center><font color='blue'><?php echo $mess; ?></font></center>
					</div>
				</div>
			</div>
		
			<p align="center"><a class="btn btn-primary" href="admin_nvs.php">Retour à l'administration</a> <a class="btn btn-primary" href="jouer.php">Retour au jeu</a></p>
		
			<div class="row">
				<div class="col-12">
				
					<h3>LOGS Tentatives de triches</h3>
					
				</div>
			</div>
			
			<br />
			
			<div class="row">
				<div class="col-12">
					<div align="center">					
						<div id="table_batiments" class="table-responsive">	
					
							<?php
							$sql = "SELECT id_perso, texte_tentative FROM tentative_triche ORDER BY id_tentative";
							$res = $mysqli->query($sql);
							
							echo "<table class='table'>";
							echo "	<thead>";
							echo "		<tr>";
							echo "			<th>Perso / Joueur</th><th>Texte tentative de triche</th>";
							echo "		</tr>";
							echo "	</thead>";
							echo "	<tbody>";
							
							while ($t = $res->fetch_assoc()) {
								
								$id_tricheur 		= $t['id_perso'];
								$texte_tentative	= htmlentities($t['texte_tentative'],ENT_QUOTES);
								
								$sql_p = "SELECT nom_perso FROM perso WHERE id_perso='$id_tricheur'";
								$res_p = $mysqli->query($sql_p);
								$t_p = $res_p->fetch_assoc();
								
								$nom_perso = $t_p['nom_perso'];
								
								echo "		<tr>";
								echo "			<td>".$nom_perso." [".$id_tricheur."]</td>";
								echo "			<td>".$texte_tentative."</td>";
								echo "		</tr>";
								
							}
							
							echo "	</tbody>";
							echo "</table>";
							?>
						
						</div>
					</div>
				</div>
			</div>
		
		</div>
		
		<!-- Modal -->
		<form method="post" action="admin_batiments.php">
			<div class="modal fade" id="modalConfirmPont" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
				<div class="modal-dialog modal-dialog-centered" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title" id="exampleModalCenterTitle">Détruire tous les ponts du jeu</h5>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>
						<div class="modal-body">
							Êtes-vous sûr de vouloir détruire tous les ponts du jeu ?
							<input type='hidden' name='destruction_pont' value='ok'>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
							<button type="button" onclick="this.form.submit()" class="btn btn-primary">Détruire</button>
						</div>
					</div>
				</div>
			</div>
		</form>
		
		<form method="post" action="admin_batiments.php">
			<div class="modal fade" id="modalConfirmbarricade" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
				<div class="modal-dialog modal-dialog-centered" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title" id="exampleModalCenterTitle">Détruire toutes les barricades du jeu</h5>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>
						<div class="modal-body">
							Êtes-vous sûr de vouloir détruire toutes les barricades du jeu ?
							<input type='hidden' name='destruction_barricade' value='ok'>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
							<button type="button" onclick="this.form.submit()" class="btn btn-primary">Détruire</button>
						</div>
					</div>
				</div>
			</div>
		</form>
		
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
