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

// recupération config jeu
$dispo = config_dispo_jeu($mysqli);
$admin = admin_perso($mysqli, $_SESSION["id_perso"]);

if($dispo == '1' || $admin){
	
	if (isset($_SESSION["id_perso"])) {
		
		//recuperation des variables de sessions
		$id = $_SESSION["id_perso"];
		
		if (anim_perso($mysqli, $id)) {
			
			$mess = "";
			$mess_erreur = "";
			
			// Récupération du camp de l'animateur 
			$sql = "SELECT clan FROM perso WHERE id_perso='$id'";
			$res = $mysqli->query($sql);
			$t = $res->fetch_assoc();
			
			$camp = $t['clan'];
			
			if ($camp == '1') {
				$nom_camp = 'Nord';
			}
			else if ($camp == '2') {
				$nom_camp = 'Sud';
			}
			else if ($camp == '3') {
				$nom_camp = 'Indien';
			}
			
			if (isset($_POST['reponse']) && trim($_POST['reponse']) != "" && isset($_POST['hid_rep_id_perso']) && isset($_POST['hid_rep_id_question'])) {
				
				$message 			= addslashes($_POST['reponse']);
				$id_perso_rep		= $_POST['hid_rep_id_perso'];
				$id_question_rep	= $_POST['hid_rep_id_question'];
				
				$sql = "SELECT nom_perso FROM perso WHERE id_perso='$id_perso_rep'";
				$res = $mysqli->query($sql);
				$t = $res->fetch_assoc();
			
				$dest = $t['nom_perso'];
				
				$sql = "SELECT nom_perso FROM perso WHERE id_perso='$id'";
				$res = $mysqli->query($sql);
				$t = $res->fetch_assoc();
			
				$expediteur = $t['nom_perso'];
				
				$objet = "[Animation] Réponse à votre question / remontée";
				
				$lock = "LOCK TABLE message WRITE";
				$mysqli->query($lock);
				
				// creation du message
				$sql = "INSERT INTO message (id_expediteur, expediteur_message, date_message, contenu_message, objet_message) 
						VALUES ('" . $id . "', '" . $expediteur . "', NOW(), '" . $message. "', '" . $objet. "')";
				$mysqli->query($sql);
				$id_message = $mysqli->insert_id;
				
				$unlock = "UNLOCK TABLES";
				$mysqli->query($unlock);
				
				// assignation du message au perso
				$sql = "INSERT INTO message_perso VALUES ('$id_message', '$id_perso_rep', '1', '0', '0', '0')";
				$mysqli->query($sql);
				
				$sql = "UPDATE anim_question SET status='1' WHERE id='$id_question_rep'";
				$mysqli->query($sql);
				
				$sql = "INSERT INTO anim_question(date_question, id_perso, titre, question, id_camp, status, id_parent) VALUES (NOW(), '$id', '$objet', '$message', '$camp', '9', '$id_question_rep')";
				$mysqli->query($sql);
				
				$mess .= "Réponse envoyée avec succés";
			}
?>
		
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<title>Nord VS Sud - Animation</title>
		
		<!-- Required meta tags -->
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		
		<!-- Bootstrap CSS -->
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
	</head>
	<body>
		<div class="container">
		
			<div class="row">
				<div class="col-12">
					<div align="center">
						<h2>Animation - Questions / remontées des joueurs</h2>
					</div>
				</div>
			</div>
			
			<div class="row">
				<div class="col-12">
					<div align="center">
						<a class="btn btn-primary" href="animation.php">Retour page principale d'animation</a>
						<a class="btn btn-success" href="anim_questions.php">Retour à la liste des questions en attente</a>
						<?php
						if (!isset($_GET['reponses'])) {
						?>
						<a class="btn btn-success" href="anim_questions.php?reponses=ok">Voir les questions déjà répondues</a>
						<?php 
						}
						?>
					</div>
				</div>
			</div>
			
			<div class="row">
				<div class="col-12">
					<div align="center">
						<?php
						echo "<font color='blue'>".$mess."</font><br />";
						echo "<font color='red'><b>".$mess_erreur."</b></font><br />";
						?>
					</div>
				</div>
			</div>
			
			<?php
			if (isset($_GET['id']) && isset($_GET['action']) && $_GET['action'] == 'repondre') {
				
				$id_question = $_GET['id'];
				
				$verif = preg_match("#^[0-9]*[0-9]$#i","$id_question");
				
				if ($verif) {
				
					$sql_q = "SELECT perso.id_perso, perso.nom_perso, date_question, titre, question 
								FROM anim_question, perso 
								WHERE anim_question.id_perso = perso.id_perso
								AND id='$id_question'";
					$res_q = $mysqli->query($sql_q);
					$t_q = $res_q->fetch_assoc();
					
					$id_perso 			= $t_q['id_perso'];
					$nom_perso 			= $t_q['nom_perso'];
					$date_question		= $t_q['date_question'];
					$titre_question		= $t_q['titre'];
					$question			= $t_q['question'];
			?>
			<div class="row">
				<div class="col-12">
					<div class="table-responsive">
						<table border='1' width='100%'>
							<tr>
								<td><b>Auteur de la question : </b></td><td><?php echo $nom_perso." [<a href='evenement.php?infoid=".$id_perso."&type=perso'>".$id_perso."</a>]"; ?></td>
								<td><b>Date d'envoi : </b></td><td><?php echo $date_question; ?></td>
							</tr>
							<tr>
								<td><b>Titre : </b></td><td colspan='3'><?php echo $titre_question; ?></td>
							</tr>
							<tr>
								<td colspan='4'><?php echo $question; ?></td>
							</tr>
						</table>
					</div>
				</div>
			</div>
			
			<div class="row">
				<div class="col-12">
					<div class="table-responsive">
						<form method='post' action='anim_questions.php'>
							<div class="form-group col-md-12">
								<label for="reponse">Réponse</label>
								<textarea  class="form-control" cols="100" rows="20" id="reponse" name="reponse"></textarea>
								<input type='hidden' name='hid_rep_id_perso' value='<?php echo $id_perso; ?>' />
								<input type='hidden' name='hid_rep_id_question' value='<?php echo $id_question; ?>' />
							</div>
							<div class="form-group col-md-6">
								<input type="submit" name="envoyer" value="envoyer" class='btn btn-primary'>
								<input type="submit" name="annuler" value="annuler" class='btn btn-warning'>
							</div>
						</form>
					</div>
				</div>
			</div>
			<?php
				}
				else {
					echo "<center><font color='red'><b>Merci de ne pas jouer avec les paramètres de l'url...</b></font></center>";
				}
			}
			else if (isset($_GET['id']) && isset($_GET['action']) && $_GET['action'] == 'voir') {
				
				$id_question = $_GET['id'];
				
				$verif = preg_match("#^[0-9]*[0-9]$#i","$id_question");
				
				if ($verif) {
				
					$sql_q = "SELECT perso.id_perso, perso.nom_perso, date_question, titre, question 
								FROM anim_question, perso 
								WHERE anim_question.id_perso = perso.id_perso
								AND id='$id_question'";
					$res_q = $mysqli->query($sql_q);
					$t_q = $res_q->fetch_assoc();
					
					$id_perso 			= $t_q['id_perso'];
					$nom_perso 			= $t_q['nom_perso'];
					$date_question		= $t_q['date_question'];
					$titre_question		= $t_q['titre'];
					$question			= $t_q['question'];
					
					// Réponse 
					$sql_a = "SELECT perso.id_perso, perso.nom_perso, date_question, question 
								FROM anim_question, perso 
								WHERE anim_question.id_perso = perso.id_perso
								AND id_parent='$id_question'";
					$res_a = $mysqli->query($sql_a);
					$t_a = $res_a->fetch_assoc();
					$nb_rep = $res_a->num_rows;
					
					$id_perso_rep 		= $t_a['id_perso'];
					$nom_perso_rep		= $t_a['nom_perso'];
					$date_reponse		= $t_a['date_question'];
					$reponse			= $t_a['question'];
					?>
					
			<div class="row">
				<div class="col-12">
					<div class="table-responsive">
						<table border='1' width='100%'>
							<tr>
								<td width='20%'><b>Auteur de la question : </b></td><td width='30%'><?php echo $nom_perso." [<a href='evenement.php?infoid=".$id_perso."&type=perso'>".$id_perso."</a>]"; ?></td>
								<td width='20%'><b>Date d'envoi : </b></td><td width='30%'><?php echo $date_question; ?></td>
							</tr>
							<tr>
								<td><b>Titre : </b></td><td colspan='3'><?php echo $titre_question; ?></td>
							</tr>
							<tr>
								<td colspan='4'><?php echo $question; ?></td>
							</tr>
						</table>
					</div>
				</div>
			</div>
			
			<?php
					if ($nb_rep > 0) {
			?>
			<br />
			<div class="row">
				<div class="col-12">
					<div class="table-responsive">
						<table border='1' width='100%'>
							<tr>
								<td width='20%'><b>Auteur de la réponse : </b></td><td width='30%'><?php echo $nom_perso_rep." [<a href='evenement.php?infoid=".$id_perso."&type=perso'>".$id_perso_rep."</a>]"; ?></td>
								<td width='20%'><b>Date de la réponse : </b></td><td width='30%'><?php echo $date_reponse; ?></td>
							</tr>
							<tr>
								<td colspan='4'><?php echo $reponse; ?></td>
							</tr>
						</table>
					</div>
				</div>
			</div>
					<?php
					}
					else {
					?>
			<div class="row">
				<div class="col-12">
					<div align="center">
						Réponse pour cette question non accessible
					</div>
				</div>
			</div>
					<?php
					}
				}				
			}
			else {
				if (isset($_GET['reponses']) && $_GET['reponses'] == "ok") {
					
					// Récupération des questions anims répondues
					$sql = "SELECT anim_question.id, perso.id_perso, perso.nom_perso, date_question, titre, question, status FROM perso, anim_question 
							WHERE anim_question.id_perso = perso.id_perso
							AND anim_question.id_camp='$camp'
							AND status = '1'
							ORDER BY anim_question.id ASC";
					$res = $mysqli->query($sql);
			?>
				<div class="row">
					<div class="col-12">
						<div class="table-responsive">
							<table class="table">
								<thead>
									<tr>
										<th style='text-align:center'>Perso</th>
										<th style='text-align:center'>Titre</th>
										<th style='text-align:center'>Question / remontée</th>
										<th style='text-align:center'>Action</th>
									</tr>
								</thead>
								<tbody>
									<?php
									while ($t = $res->fetch_assoc()) {
										
										$id_question		= $t['id'];
										$id_perso 			= $t['id_perso'];
										$nom_perso			= $t['nom_perso'];
										$date_question		= $t['date_question'];
										$titre_question		= $t['titre'];
										$question			= $t['question'];
										$status_question	= $t['status'];
										
										echo "<tr>";
										echo "	<td align='center'>".$nom_perso." [<a href='evenement.php?infoid=".$id_perso."&type=perso'>".$id_perso."</a>]</td>";
										echo "	<td align='center'>".$titre_question."</td>";
										echo "	<td align='center'>".$question."</td>";
										echo "	<td align='center'>";
										echo "		<a class='btn btn-success' href=\"anim_questions.php?id=".$id_question."&action=voir\">Voir</a>";
										echo "	</td>";
										echo "</tr>";
									}
									?>
								</tbody>
							</table>
						</div>			
					</div>
				</div>
			<?php
				}
				else {
					
					// Récupération des questions anims
					$sql = "SELECT anim_question.id, perso.id_perso, perso.nom_perso, date_question, titre, question, status FROM perso, anim_question 
							WHERE anim_question.id_perso = perso.id_perso
							AND anim_question.id_camp='$camp'
							AND status = '0'
							ORDER BY anim_question.id ASC";
					$res = $mysqli->query($sql);
			?>
			
				<div class="row">
					<div class="col-12">
						<div class="table-responsive">
							<table class="table">
								<thead>
									<tr>
										<th style='text-align:center'>Perso</th>
										<th style='text-align:center'>Titre</th>
										<th style='text-align:center'>Question / remontée</th>
										<th style='text-align:center'>Action</th>
									</tr>
								</thead>
								<tbody>
									<?php
									while ($t = $res->fetch_assoc()) {
										
										$id_question		= $t['id'];
										$id_perso 			= $t['id_perso'];
										$nom_perso			= $t['nom_perso'];
										$date_question		= $t['date_question'];
										$titre_question		= $t['titre'];
										$question			= $t['question'];
										$status_question	= $t['status'];
										
										echo "<tr>";
										echo "	<td align='center'>".$nom_perso." [<a href='evenement.php?infoid=".$id_perso."&type=perso'>".$id_perso."</a>]</td>";
										echo "	<td align='center'>".$titre_question."</td>";
										echo "	<td align='center'>".$question."</td>";
										echo "	<td align='center'>";
										echo "		<a class='btn btn-success' href=\"anim_questions.php?id=".$id_question."&action=repondre\">Répondre</a>";
										echo "	</td>";
										echo "</tr>";
									}
									?>
								</tbody>
							</table>
						</div>			
					</div>
				</div>
			<?php
				}
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
			// Un joueur essaye d'acceder à la page sans être animateur
			$text_triche = "Tentative accés page animation sans y avoir les droits";
			
			$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id', '$text_triche')";
			$mysqli->query($sql);
			
			header("Location:jouer.php");
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
	
