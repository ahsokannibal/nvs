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
		
		$sql = "SELECT type_perso FROM perso WHERE id_perso='$id'";
		$res = $mysqli->query($sql);
		$t = $res->fetch_assoc();
		
		$type_perso = $t['type_perso'];
		
		if ($type_perso != 6) {
		
			$mess = "";
			$mess_err = "";
			
			if (isset($_POST['titreCapture']) && trim($_POST['titreCapture']) != "" 
				&& isset($_POST['typeCapture']) 
				&& isset($_POST['description']) && trim($_POST['description']) != "" 
				&& isset($_POST['idPersoCapture']) && trim($_POST['idPersoCapture']) != ""
				&& isset($_FILES['img']) && isset($_FILES['img_fin'])) {

				$titre 				= addslashes($_POST['titreCapture']);
				$description		= addslashes($_POST['description']);
				$id_perso_capture	= $_POST['idPersoCapture'];
				$type_capture		= $_POST['typeCapture'];
				
				// verification id_perso_capture
				$verif_id_perso = preg_match("#^[0-9]*[0-9]$#i","$id_perso_capture");
				
				if ($verif_id_perso && ($type_capture == 1 || $type_capture == 2)) {
					
					// On vérifie si une remontée de capture a déjà été effectuée aujourd'hui contre ce perso
					$sql = "SELECT * FROM anim_capture WHERE id_perso_capture='$id_perso_capture' AND date_capture >= CURDATE() - INTERVAL 1 DAY";
					$res = $mysqli->query($sql);
					$verif_capture_jour = $res->num_rows;
					
					if ($verif_capture_jour) {
						$mess_err .= "Une capture a déjà été remontée sur ce perso dans les dernières 24h";
					}
					else {
						
						$extensions = array('.png', '.gif', '.jpg', '.jpeg');
						$extension_deb = strrchr($_FILES['img']['name'], '.');
						$extension_fin = strrchr($_FILES['img_fin']['name'], '.');
						
						if(in_array($extension_deb, $extensions) && in_array($extension_fin, $extensions)) {
						
							$taille_maxi = 500000;
							$taille = filesize($_FILES['img']['tmp_name']);
							$taille_fin = filesize($_FILES['img_fin']['tmp_name']);
							
							if($taille <= $taille_maxi && $taille_fin <= $taille_maxi) {
								
								$dossier = 'upload/';
								$fichier = basename($_FILES['img']['name']);
								$fichier_fin = basename($_FILES['img_fin']['name']);
						
								$temp 		= explode(".", $_FILES["img"]["name"]);
								$temp_fin 	= explode(".", $_FILES["img_fin"]["name"]);
								
								$extension_deb	= end($temp);
								$extension_fin	= end($temp_fin);
						
								$lock = "LOCK TABLE (anim_capture) WRITE";
								$mysqli->query($lock);
						
								$sql = "INSERT INTO anim_capture(date_capture, id_perso, id_perso_capture, titre, message, type_capture, extension_img1, extension_img2) 
										VALUES (NOW(), '$id', '$id_perso_capture', '$titre', '$description', '$type_capture', '$extension_deb', '$extension_fin')";
								$mysqli->query($sql);
								$id_capture = $mysqli->insert_id;
								
								$unlock = "UNLOCK TABLES";
								$mysqli->query($unlock);
								
								$newfilename 		= "capture_debut_". $id_capture . '.' . end($temp);								
								$newfilename_fin 	= "capture_fin_". $id_capture . '.' . end($temp_fin);
								
								if(move_uploaded_file($_FILES['img']['tmp_name'], $dossier . $newfilename) && move_uploaded_file($_FILES['img_fin']['tmp_name'], $dossier . $newfilename_fin)) {
									$mess = "Capture remontée avec succès. Les animateurs peuvent éventuellement vous contacter par MP pour obtenir plus de précision si necessaire.";
								}
								else {
									$mess_err .= "Echec de l'upload !";
								}
							}
							else {
								$mess_err .= "Le fichier est trop gros, maximum autorisé : 500ko";
							}
						}
						else {
							$mess_err .= "Vous devez uploader un fichier de type png, gif, jpg, jpeg";
						}
					}
				}
				else {
					// parametres incorrectes / modifiés
					$text_triche = "Champ matricule perso ou type capture page capture RP incorrect";
					
					$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id', '$text_triche')";
					$mysqli->query($sql);
					
					header("Location:jouer.php");
				}
			}
			else {
				$mess_err .= "Les champs titre, matricule du perso, description et preuves sont obligatoire, pensez à les remplir avant envoi";
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
		
		<style>
		.file {
		  visibility: hidden;
		  position: absolute;
		}
		</style>
	</head>
	<body>
		<div class="container">
			
			<div class="row">
				<div class="col-12">
					<div align="center">
						<h2>Remonter une capture</h2>
						
						<font color='red'><?php echo $mess_err; ?></font>
						<font color='blue'><?php echo $mess; ?></font>
					</div>
				</div>
			</div>
			
			<p align="center"><input type="button" value="Fermer la fenêtre de question / remontée aux animateurs" onclick="window.close()"></p>
			
			<br />
			
			<div class="row">
				<div class="col-12">
					<div align="center">
						<form method='post' enctype= "multipart/form-data" action='capture.php'>
							<div class="form-group col-md-6">
								<label for="typeCapture">Type de capture <font color='red'>*</font></label>
								<select class="form-control" name='typeCapture'>
									<option value='1'>Capture par encerclement</option>
									<option value='2'>Capture RP</option>
								</select>
							</div>
							<div class="form-group col-md-6">
								<label for="titreCapture">Titre <font color='red'>*</font></label>
								<input type="text" class="form-control" id="titreCapture" name="titreCapture" maxlength="40">
							</div>
							<div class="form-group col-md-6">
								<label for="idPersoCapture">Matricule du perso capturé <font color='red'>*</font></label>
								<input type="text" class="form-control" id="idPersoCapture" name="idPersoCapture" maxlength="5">
							</div>
							<div class="form-group col-md-6">
								<label for="description">Description (vous pouvez ajouter des liens vers d'autres images) <font color='red'>*</font></label>
								<textarea class="form-control" cols="50" rows="5" id="description" name="description"></textarea >
							</div>
							<div class="form-group col-md-6">
								<input type="file" name="img" class="file file_d" accept="image/*">
								<div class="input-group my-3">
									<input type="text" class="form-control" disabled placeholder="Preuve début capture (image taille max 500ko)" id="file">
									<div class="input-group-append">
										<button type="button" class="browse btn btn-primary file_debut">Parcourir...</button>
									</div>
								</div>
							</div>
							<div class="form-group col-md-6">
								<input type="file" name="img_fin" class="file file_f" accept="image/*">
								<div class="input-group my-3">
									<input type="text" class="form-control" disabled placeholder="Preuve fin capture (image taille max 500ko)" id="file2">
									<div class="input-group-append">
										<button type="button" class="browse btn btn-primary file_fin">Parcourir...</button>
									</div>
								</div>
							</div>
							<div class='row col-sm-12'>
								<div class="col col-sm-6">
									<img src="https://placehold.it/80x80" id="preview" class="img-thumbnail">
								</div>
								<div class="col col-sm-6">
									<img src="https://placehold.it/80x80" id="preview2" class="img-thumbnail">
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
		<script>
		$(document).on("click", ".file_debut", function() {
			var file = $(this).parents().find(".file_d");
			file.trigger("click");
		});
		
		$(document).on("click", ".file_fin", function() {
			var file = $(this).parents().find(".file_f");
			file.trigger("click");
		});
		
		$('.file_d').change(function(e) {
			
			var fileName = e.target.files[0].name;
			$("#file").val(fileName);

			var reader = new FileReader();
			reader.onload = function(e) {
				// get loaded data and render thumbnail.
				document.getElementById("preview").src = e.target.result;
			};
			// read the image file as a data URL.
			reader.readAsDataURL(this.files[0]);
		});
		
		$('.file_f').change(function(e) {
			
			var fileName = e.target.files[0].name;
			$("#file2").val(fileName);

			var reader = new FileReader();
			reader.onload = function(e) {
				// get loaded data and render thumbnail.
				document.getElementById("preview2").src = e.target.result;
			};
			// read the image file as a data URL.
			reader.readAsDataURL(this.files[0]);
		});
		</script>
	</body>
<?php
		}
		else {
			echo "<center><font color='red'>Les chiens ne peuvent pas accéder à cette page.</font></center>";
		}
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