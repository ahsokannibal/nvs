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
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	</head>
	<body>
		<div align="center"><h2>Tresorerie</h2></div>
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
						$sql = "SELECT nom_perso, operation, montant FROM histobanque_compagnie, perso 
								WHERE id_compagnie=$id_compagnie 
								AND histobanque_compagnie.id_perso=perso.ID_perso 
								AND histobanque_compagnie.id_perso=$id_p 
								ORDER BY id_histo DESC";
						$res = $mysqli->query($sql);
						
						echo "<center>";
						
						while ($t_solde = $res->fetch_assoc()) {
							
							$nom_p 		= $t_solde['nom_perso'];
							$op 		= $t_solde['operation'];
							$montant 	= $t_solde['montant'];
							
							if ($op == 0) {
								echo "$nom_p a retiré <b>$montant</b> po<br>";
							}
							if ($op == 1) {
								echo "$nom_p a deposé <b>$montant</b> po<br>";
							}
							if ($op == 2) {
								$mont = -$montant;
								echo "$nom_p a emprunté : <b>$mont</b> po<br>";
							}
							if ($op == 3) {
								echo "$nom_p a remboursé : <b>$montant</b> po<br>";
							}
						}
					}
					else {
						// on recupere l'historique pour les persos de sa compagnie
						$sql = "SELECT histobanque_compagnie.id_perso, nom_perso, SUM(montant) as fond FROM histobanque_compagnie, perso 
								WHERE id_compagnie=$id_compagnie 
								AND histobanque_compagnie.id_perso=perso.ID_perso 
								GROUP BY id_perso";
						$res = $mysqli->query($sql);
						
						echo "<center>";
						
						while ($t_solde = $res->fetch_assoc()) {
							
							$id_p 	= $t_solde['id_perso'];
							$nom_p 	= $t_solde['nom_perso'];
							$fond 	= $t_solde['fond'];
							
							echo "$nom_p [".$id_p."] : $fond <a href='tresor_compagnie.php?id_compagnie=$id_compagnie&solde=ok&detail=$id_p'> Details ? </a><br>";
						}			
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
					echo "</form></center>";
				}
				else {
					echo "<center><font color = blue>Il n y a aucun perso en attente d'emprunt</font></center>";
				}
				
				echo "<br /><center><a href='tresor_compagnie.php?id_compagnie=$id_compagnie&solde=ok'> Voir les soldes par perso </a></center><br>";
				echo "<a href='compagnie.php'> [acceder a la page de compagnie] </a>";
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
	
	header("Location: ../index2.php");
}
?>