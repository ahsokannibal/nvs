<?php
session_start();
require_once("../fonctions.php");

$mysqli = db_connexion();

include ('../nb_online.php');

if(@$_SESSION["id_perso"]){

	$id = $_SESSION["id_perso"];
	
	$sql = "SELECT type_perso FROM perso WHERE id_perso='$id'";
	$res = $mysqli->query($sql);
	$t_p = $res->fetch_assoc();
	
	$type_p = $t_p['type_perso'];
	
	if ($type_p != 6) {
	
		if(isset($_POST["changer"])) {
			
			$message = htmlentities(addslashes(nl2br($_POST["message"])));
			
			$sql = "UPDATE perso SET message_perso='$message' WHERE ID_perso='$id'";
			$mysqli->query($sql);
			
			header("Location:profil.php");
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
	<?php

		$sql = "SELECT message_perso FROM perso WHERE ID_perso ='$id'";
		$res = $mysqli->query($sql);
		$tab = $res->fetch_row();
		
		$message = stripslashes($tab[0]);
	?>
		<div class="container">
			<div align="center">
				<div><b>Sur cette page vous avez la possibilité de changer votre message du jour<br>Message limité à 3 lignes et 100 caractères maximum</b></div>
				
				<a class="btn btn-primary" href="profil.php">Retour</a>
				
				<br><br> 

				<form method="post" action="">
<textarea data-limit-rows="true" cols="50" rows="3" name="message" onkeyup="javascript:maxLengthTextarea(this, 100);">
<?php 
	if($message == "") {
		echo "Aucun message"; 
	}
	else {
		echo br2nl2($message);
	}
?>
</textarea><br>
					<input type="submit" name="changer" value="changer">
				</form>
			</div>
		</div>
		
<?php
	}
	else {
		echo "<center><font color='red'>Les chiens ne peuvent pas accèder à cette page.</font></center>";
	}
?>		
		
		<!-- Optional JavaScript -->
		<!-- jQuery first, then Popper.js, then Bootstrap JS -->
		<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
		<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
		
		<script>
		$(document).ready(function () {
		  $('textarea[data-limit-rows=true]')
			.on('keypress', function (event) {
				var textarea = $(this),
					text = textarea.val(),
					numberOfLines = (text.match(/\n/g) || []).length + 1,
					maxRows = parseInt(textarea.attr('rows'));
		 
				if (event.which === 13 && numberOfLines === maxRows ) {
				  return false;
				}
			});
		});
		
		function maxLengthTextarea(objettextarea,maxlength){
		  if (objettextarea.value.length > maxlength) {
			objettextarea.value = objettextarea.value.substring(0, maxlength);
			alert('Votre texte ne doit pas dépasser '+maxlength+' caractères!');
		   }
		}
		</script>
	</body>
</html>
<?php
}
else{
	echo "<font color=red>Vous ne pouvez pas accéder à cette page, veuillez vous loguer.</font>";
}
?>