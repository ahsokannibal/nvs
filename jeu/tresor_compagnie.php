<?php
session_start();
require_once("../fonctions.php");

$mysqli = db_connexion();

include ('../nb_online.php');

// recupération config jeu
$dispo = config_dispo_jeu($mysqli);
$admin = admin_perso($mysqli, $_SESSION["id_perso"]);

if($dispo || $admin){
	
	if(isset($_SESSION["id_perso"])){
		
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
			//$erreur = "<div class=\"erreur\">";
	
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
						<h2>Trésorerie</h2>
					</div>
					
					<p align="center"><input type="button" value="Fermer cette fenêtre" onclick="window.close();"></p>
	<?php
	if(isset($_GET["id_compagnie"])) {
		
		$id_compagnie = $_GET["id_compagnie"];
	
		$verif1 = preg_match("#^[0-9]+$#i",$id_compagnie);
		
		if($verif1){
		
			// verification que le perso appartient bien a la compagnie
			$sql = "SELECT id_compagnie FROM perso_in_compagnie WHERE id_perso='$id' AND (attenteValidation_compagnie='0' OR attenteValidation_compagnie='2')";
			$res = $mysqli->query($sql);
			$verif = $res->num_rows;
				
			if($verif){
			
				if(isset($_GET['solde']) && $_GET['solde'] == "ok") {
					
					if(isset($_GET['detail'])) {
						
						$id_p = $_GET['detail'];
						
						$sql = "SELECT nom_perso FROM perso WHERE id_perso='$id_p'";
						$res = $mysqli->query($sql);
						$t = $res->fetch_assoc();
						
						$nom_perso = $t['nom_perso'];
						
						$sql = "SELECT operation, montant, date_operation FROM histobanque_compagnie, perso 
								WHERE id_compagnie=$id_compagnie 
								AND histobanque_compagnie.id_perso=perso.ID_perso 
								AND histobanque_compagnie.id_perso=$id_p 
								ORDER BY id_histo DESC";
						$res = $mysqli->query($sql);
						
						echo "<center>";
						
						echo "<b>".$nom_perso." [".$id_p."]</b><br /><br />";
						
						echo "<div id=\"table_tresor\" class=\"table-responsive\">";
						echo "	<table border='1'>";
						echo "		<tr>";
						echo "			<th style='text-align:center'>Date opération</th><th style='text-align:center'>Type d'opération</th><th style='text-align:center'>Montant</th>";
						echo "		</tr>";
						
						while ($t_solde = $res->fetch_assoc()) {
							
							$op 		= $t_solde['operation'];
							$montant 	= $t_solde['montant'];
							$date_ope	= $t_solde['date_operation'];
							
							if ($op == 0) {
								$type_ope = "Dépot";
								$color = "blue";
							}
							if ($op == 1) {
								$type_ope = "Retrait";
								$montant = substr($montant, 1, strlen($montant));
								$color = "orange";
							}
							if ($op == 2) {
								$type_ope = "Emprunt";
								$montant = -$montant;
								$color = "red";
							}
							if ($op == 3) {
								$type_ope = "Remboursement emprunt";
								$color = "green";
							}
							if ($op == 4) {
								$type_ope = "Virement";
								$color = "brown";
							}
							
							echo "		<tr>";
							echo "			<td>".$date_ope."</td><td>".$type_ope."</td><td align='center'><font color='".$color."'><b>".$montant."</b></font></td>";
							echo "		</tr>";
						}
						
						echo "	</table>";
						echo "</div>";
					}
					else {
						// on recupere l'historique pour les persos de sa compagnie
						$sql = "SELECT histobanque_compagnie.id_perso, nom_perso, SUM(montant) as fond FROM histobanque_compagnie, perso 
								WHERE id_compagnie=$id_compagnie 
								AND histobanque_compagnie.id_perso=perso.ID_perso 
								GROUP BY id_perso";
						$res = $mysqli->query($sql);
						
						echo "<center>";
						
						echo "<div id=\"table_tresor_perso\" class=\"table-responsive\">";
						echo "	<table border='1' width='100%'>";
						echo "		<tr>";
						echo "			<th>Nom [matricule]</th><th style='text-align: center;'>Montant</th><th style='text-align: center;'>Action</th>";
						echo "		</tr>";
						
						while ($t_solde = $res->fetch_assoc()) {
							
							$id_p 	= $t_solde['id_perso'];
							$nom_p 	= $t_solde['nom_perso'];
							$fond 	= $t_solde['fond'];
							
							echo "		<tr>";
							echo "			<td>".$nom_p."[".$id_p."]</td><td align='center'>".$fond."</td><td align='center'><a href='tresor_compagnie.php?id_compagnie=$id_compagnie&solde=ok&detail=$id_p' class='btn btn-info'> Consulter détails </a></td>";
							echo "		</tr>";
						}
						
						echo "	</table>";
						echo "</div>";						
					}
					
					echo "</center><br>";
				}
				
				if(isset($_POST['val_emp']) && $_POST['val_emp'] == "valider emprunt") {
					
					if(isset($_POST["emprunteur"])){
						
						$t_r = explode(",",$_POST["emprunteur"]);
						
						$id_emp 		= $t_r[0];
						$nom_emp 		= $t_r[1];
						$montant_emp 	= $t_r[2];
						
						// on verifie qu'il y a assez d'argent pour valider l'emprunt
						$sql = "SELECT montant FROM banque_as_compagnie WHERE id_compagnie=$id_compagnie";
						$res = $mysqli->query($sql);
						$t_sum = $res->fetch_assoc();
						
						$sum = $t_sum["montant"];
						
						if($sum >= $montant_emp) {
						
							// on supprime la demande d'emprunt
							$sql = "UPDATE banque_compagnie SET demande_emprunt='0', montant_emprunt='0' WHERE id_perso='$id_emp'";
							$mysqli->query($sql);
							
							// on met a jour bourse
							$sql = "UPDATE perso SET or_perso=or_perso+$montant_emp WHERE id_perso='$id_emp'";
							$mysqli->query($sql);
							
							// maj banque_as_compagnie
							$sql = "UPDATE banque_as_compagnie SET montant=montant-$montant_emp WHERE id_compagnie='$id_compagnie'";
							$mysqli->query($sql);
							
							// on met a jour histoBanque_compagnie
							$sql = "INSERT INTO histobanque_compagnie (id_compagnie, id_perso, operation, montant) VALUES ('$id_compagnie','$id_emp','2','-$montant_emp')";
							$mysqli->query($sql);
							
							echo "<center><font color='blue'>Vous avez validé l'emprunt de $montant_emp po pour $nom_emp</font></center>";
						}
						else { 
							// pas assez en banque
							echo "<center><font color='red'>Il n y a pas assez en banque pour permettre cet emprunt.</font></center>";
						}
					}
				}
				
				if (isset($_POST['refus_emp']) && $_POST['refus_emp'] == "refuser emprunt") {
					
					if(isset($_POST["emprunteur"])){
						
						$t_r = explode(",",$_POST["emprunteur"]);
						
						$id_emp 		= $t_r[0];
						$nom_emp 		= $t_r[1];
						$montant_emp 	= $t_r[2];
						
						// on supprime la demande d'emprunt
						$sql = "UPDATE banque_compagnie SET demande_emprunt='0', montant_emprunt='0' WHERE id_perso='$id_emp'";
						$mysqli->query($sql);
						
						// nom tersorier
						$sql = "SELECT nom_perso FROM perso WHERE id_perso='$id'";
						$res = $mysqli->query($sql);
						$t = $res->fetch_assoc();
						
						$nom_tresorier = $t['nom_perso'];
						
						// on lui envoi un mp
						$message = "Bonjour $nom_emp,
									J\'ai le regret de t\'annoncer que ta demande d\'emprunt de $montant_emp thunes a été refusé.";
						$objet = "Refus emprunt du trésorier de la compagnie";
						
						$lock = "LOCK TABLE (joueur) WRITE";
						$mysqli->query($lock);
						
						$sql = "INSERT INTO message (expediteur_message, date_message, contenu_message, objet_message) VALUES ( '" . addslashes($nom_tresorier) . "', NOW(), '" . $message . "', '" . $objet . "')";
						$res = $mysqli->query($sql);
						$id_message = $mysqli->insert_id;
						
						$unlock = "UNLOCK TABLES";
						$mysqli->query($unlock);
						
						$sql = "INSERT INTO message_perso VALUES ('$id_message','$id_emp','1','0','1','0')";
						$res = $mysqli->query($sql);
						
						echo "<center><font color='blue'>Vous avez refusé l'emprunt de $montant_emp po pour $nom_emp</font></center>";
					}
				}
			
				// verification si quelqu'un a demandé un emprunt
				$sql = "SELECT banque_compagnie.id_perso, nom_perso, montant_emprunt FROM banque_compagnie, perso_in_compagnie, perso 
						WHERE demande_emprunt='1' 
						AND id_compagnie=$id_compagnie 
						AND banque_compagnie.id_perso=perso_in_compagnie.id_perso 
						AND banque_compagnie.id_perso=perso.ID_perso";
				$res = $mysqli->query($sql);
				$nb = $res->num_rows;
				
				if($nb) { 
				
					// il y a des persos en attente de validation
					echo "<center><form method=\"post\" action=\"tresor_compagnie.php?id_compagnie=$id_compagnie\">";
					echo "liste des persos en attente : ";
					echo "<select name=\"emprunteur\">";
					
					while($t_id = $res->fetch_assoc()) {
						
						$id_p 		= $t_id["id_perso"];
						$nom_p 		= $t_id["nom_perso"];
						$montant_p 	= $t_id["montant_emprunt"];
					
						echo "<center><option value='".$id_p.",".$nom_p.",".$montant_p."'>".$nom_p."[".$id_p."] montant : ".$montant_p."</option><br></center>";
					}
					
					echo "</select>";
					echo "&nbsp;<input type=\"submit\" name=\"val_emp\" value=\"valider emprunt\">";
					echo "&nbsp;<input type=\"submit\" name=\"refus_emp\" value=\"refuser emprunt\">";
					echo "</form></center>";
				}
				else {
					echo "<center><font color = blue>Il n y a aucun perso en attente d'emprunt</font></center>";
				}
				
				echo "<br /><center><a href='tresor_compagnie.php?id_compagnie=$id_compagnie&solde=ok' class='btn btn-primary'> Voir les soldes par perso </a></center><br>";
				echo "<a href='compagnie.php' class='btn btn-outline-secondary'>Retour a la page de compagnie</a>";
			}
		}
		else {
			echo "<center><font color='red'>Le groupe demandé n'existe pas</font></center>";
			
			$param_test 	= addslashes($id_compagnie);
			$text_triche 	= "Test parametre sur page tresor compagnie, parametre id_compagnie invalide tenté : $param_test";
				
			$sql = "INSERT INTO tentative_triche (id_perso, texte_tentative) VALUES ('$id', '$text_triche')";
			$mysqli->query($sql);
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
	}
	else{
		echo "<font color=red>Vous ne pouvez pas accéder à cette page, veuillez vous loguer.</font>";
	}?>
<?php
}
else {
	// logout
	$_SESSION = array(); // On ecrase le tableau de session
	session_destroy(); // On detruit la session
	
	header("Location:../index2.php");
}
?>