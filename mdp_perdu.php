<?php
@session_start();

require_once("fonctions.php");

$mysqli = db_connexion();

$error = "";
$message = "";

if (isset($_GET['code']) && trim($_GET['code']) != "") {
	$code = $_GET['code'];
	
	$sql = "SELECT * FROM liens_activation WHERE id_lien='$code'";
	$res = $mysqli->query($sql);
	$verif = $res->num_rows;
	
	if ($verif) {
	
		$t = $res->fetch_assoc();
		
		$new_mdp 	= md5($t['data']);
		$mail		= $t['mail'];
		
		$sql = "UPDATE joueur SET mdp_joueur='$new_mdp' WHERE email_joueur='$mail'";
		$mysqli->query($sql);
		
		$sql = "DELETE FROM liens_activation WHERE id_lien='$code'";
		$mysqli->query($sql);
		
		$message = "Votre mot de passe a bien été changé";
	}
	else {
		$error = "Code invalide";
	}
}

if (isset($_POST["submitPwdLost"]) && isset($_POST["inputEmail"]) && trim($_POST["inputEmail"]) != "") {
	
	$mail = $_POST["inputEmail"];
	
	// Est ce que le mail existe dans notre base ?
	$sql = "SELECT id_joueur FROM joueur WHERE email_joueur='$mail'";
	$res = $mysqli->query($sql);
	$verif = $res->num_rows;
	
	if ($verif > 0) {
		$new_mdp = genererChaineAleatoire(9);
		$lien_activation = uniqid();
		
		$date_fin = time() + DUREE_TOUR;
		
		$sql = "INSERT INTO liens_activation (id_lien, mail, data, date_fin) VALUES ('$lien_activation', '$mail', '$new_mdp', FROM_UNIXTIME($date_fin))";
		$mysqli->query($sql);
		
		$message = "Un mail a été envoyé avec votre nouveau mot de passe ainsi qu'un lien d'activation de ce mot passe valide uniquement pendant 46h";
		
		envoi_mail_mdp($mysqli, $mail, $new_mdp, $lien_activation);
	}
	else {
		$error .= "Cette adresse mail n'existe pas dans notre base";
	}
}
?>
<html>
	<head>
		<title>Nord VS Sud</title>
		
		<!-- Required meta tags -->
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		
		<!-- Bootstrap CSS -->
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
	</head>

	<body style="background-color:grey;">

		<div class="container">
			<div class="row">
				<div class="col-12">
					<h1>Mot de passe perdu</h1>
					<font color='red'><b><?php echo $error; ?></b></font>
					<font color='blue'><b><?php echo $message; ?></b></font>
					<p align="center"><a class="btn btn-primary" href="./index.php">Retour</a></p>
				</div>
			</div>
			
			<div class="row">
				<div class="col-12">
					<form method='POST'>
						<div class="form-group">
							<label for="inputEmail">Votre adresse mail :</label>
							<input type="email" class="form-control" id="inputEmail" name="inputEmail">
							<input type="submit" name="submitPwdLost" value="envoyer" />
						</div>
					</form>
				</div>
			</div>
			
		</div>
	</body>
</html>
		