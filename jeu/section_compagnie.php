<?php
session_start();
require_once("../fonctions.php");

$mysqli = db_connexion();

include ('../nb_online.php');
include ('../forum/config.php');

if (@$_SESSION["id_perso"]) {
	
	//recuperation des varaibles de sessions
	$id = $_SESSION["id_perso"];
	
	$sql = "SELECT pv_perso FROM perso WHERE id_perso='$id'";
	$res = $mysqli->query($sql);
	$tpv = $res->fetch_assoc();
	
	$testpv = $tpv['pv_perso'];
	
	if ($testpv <= 0) {
		echo "<font color=red>Vous êtes mort...</font>";
	}
	else {
		
		if(isset($_GET["id_compagnie"])) {
			
			$id_compagnie = $_GET["id_compagnie"];
			
			$verif1 = preg_match("#^[0-9]+$#i",$id_compagnie);
			
			if($verif1){
			
				// verification que le perso est bien le chef de la compagnie (anti-triche)
				$sql = "SELECT poste_compagnie FROM perso_in_compagnie WHERE id_perso=$id AND id_compagnie=$id_compagnie";
				$res = $mysqli->query($sql);
				$ch = $res->fetch_assoc();
				
				$ok_chef = $ch["poste_compagnie"];
				
				if($ok_chef == 1) {
		
					$mess_err 	= "";
					$mess		= "";
					
					if (isset($_POST['nomSection']) && trim($_POST['nomSection']) != "" 
							&& isset($_POST['chefSection']) && trim($_POST['chefSection']) != "") {
						
						$nom_nouvelle_section 		= $_POST['nomSection'];
						$id_chef_nouvelle_section	= $_POST['chefSection'];
						
						$verif_id = preg_match("#^[0-9]+$#i",$id_chef_nouvelle_section);
						
						if ($verif_id) {
							
							// On vérifie que l'id du chef de section correspond bien à un membre de la compagnie mère
							
							// Création de la section
							
							// Le perso passe de la compagnie mère à chef de la nouvelle section
							
						}
						else {							
							// Tentative de triche
							$text_triche = "Tentative modification id chef section sur valeur non valide";
					
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
						<a href='section_compagnie.php?id_compagnie=<?php echo $id_compagnie; ?>&creer=ok' class='btn btn-warning'>Créer une nouvelle section</a>
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
						
						<form method='POST' action='section_compagnie.php?id_compagnie=<?php echo $id_compagnie; ?>'>
							<div class="form-row">
								<div class="form-group col-md-12">
									<label for="nomSection">Nom de la section</label>
									<input type="text" class="form-control" id="nomSection" name='nomSection' placeholder="Nom de la section">
								</div>
							</div>
							<div class="form-row">
								<div class="form-group col-md-12">
									<label for="chefSection">Chef de la section</label>
									<input type="text" class="form-control" id="chefSection" name='chefSection' placeholder="Chef de la section">
								</div>
							</div>
							<div class="form-row">
								<div class="form-group col-md-12">
									<input type="submit" class="btn btn-primary" value='Créer'>
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
										echo "	<td align='center'></td>";
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